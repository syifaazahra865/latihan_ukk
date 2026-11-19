
<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['level']) || $_SESSION['level'] != 'super_admin') {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$id_user = '';
$id_menu = '';
$jenis_transaksi = 'pembayaran';
$qty = 1;
$harga_satuan = 0;
$total_bayar = 0;
$saldo_awal = 0;
$saldo_akhir = 0;
$status = 'berhasil';
$keterangan = '';

$error = '';

// Mengambil data user
$user_query = mysqli_query($conn,
    "SELECT * FROM tb_akun WHERE role = 'user' ORDER BY nama ASC"
);

// Mengambil data menu
$menu_query = mysqli_query($conn,
    "SELECT * FROM tb_menu ORDER BY nama ASC"
);

// Mode edit
if ($id > 0) {

    $stmt = mysqli_prepare($conn,
        "SELECT * FROM tb_transaksi WHERE id_transaksi = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        die("Data transaksi tidak ditemukan!");
    }

    $id_user = $data['id_user'];
    $id_menu = $data['id_menu'];
    $jenis_transaksi = $data['jenis_transaksi'];
    $qty = $data['qty'];
    $harga_satuan = $data['harga_satuan'];
    $total_bayar = $data['total_bayar'];
    $saldo_awal = $data['saldo_awal'];
    $saldo_akhir = $data['saldo_akhir'];
    $status = $data['status'];
    $keterangan = $data['keterangan'];
}

// Proses simpan
if (isset($_POST['simpan'])) {

    $id_user = intval($_POST['id_user']);
    $id_menu = intval($_POST['id_menu']);
    $jenis_transaksi = trim($_POST['jenis_transaksi']);
    $qty = intval($_POST['qty']);
    $harga_satuan = intval($_POST['harga_satuan']);
    $total_bayar = $qty * $harga_satuan;
    $saldo_awal = intval($_POST['saldo_awal']);
    $saldo_akhir = intval($_POST['saldo_akhir']);
    $status = $_POST['status'];
    $keterangan = trim($_POST['keterangan']);

    $id_admin = $_SESSION['id_akun'];

    if ($id_user <= 0 || $id_menu <= 0 || $qty <= 0) {
        $error = "Data transaksi tidak valid!";
    } else {

        if ($id > 0) {

            // Update transaksi
            $stmt = mysqli_prepare($conn,
                "UPDATE tb_transaksi SET
                    id_user = ?,
                    id_admin = ?,
                    id_menu = ?,
                    jenis_transaksi = ?,
                    qty = ?,
                    harga_satuan = ?,
                    total_bayar = ?,
                    saldo_awal = ?,
                    saldo_akhir = ?,
                    status = ?,
                    keterangan = ?
                WHERE id_transaksi = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "iiisiiiiissi",
                $id_user,
                $id_admin,
                $id_menu,
                $jenis_transaksi,
                $qty,
                $harga_satuan,
                $total_bayar,
                $saldo_awal,
                $saldo_akhir,
                $status,
                $keterangan,
                $id
            );

            mysqli_stmt_execute($stmt);

        } else {

            // Tambah transaksi
            $stmt = mysqli_prepare($conn,
                "INSERT INTO tb_transaksi
                (
                    id_user,
                    id_admin,
                    id_menu,
                    jenis_transaksi,
                    qty,
                    harga_satuan,
                    total_bayar,
                    saldo_awal,
                    saldo_akhir,
                    status,
                    keterangan,
                    waktu_transaksi
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "iiisiiiiiss",
                $id_user,
                $id_admin,
                $id_menu,
                $jenis_transaksi,
                $qty,
                $harga_satuan,
                $total_bayar,
                $saldo_awal,
                $saldo_akhir,
                $status,
                $keterangan
            );

            mysqli_stmt_execute($stmt);
        }

        header("Location: transaksi_list.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Form Transaksi</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #eee8d2;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.container {
    width: 90%;
    max-width: 550px;
    background: #fffaf5;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

h2 {
    margin-bottom: 25px;
    color: #24351f;
}

label {
    display: block;
    margin-bottom: 7px;
    font-size: 14px;
}

input, select, textarea {
    width: 100%;
    padding: 12px;
    margin-bottom: 18px;
    border: 1px solid #d5c7b6;
    border-radius: 6px;
    outline: none;
}

input:focus, select:focus, textarea:focus {
    border-color: #b87545;
}

button {
    width: 100%;
    padding: 12px;
    background: #b87545;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-bottom: 10px;
}

button:hover {
    background: #965d35;
}

.btn-kembali {
    display: block;
    text-align: center;
    padding: 12px;
    background: #ddd;
    color: #333;
    text-decoration: none;
    border-radius: 6px;
}

.error {
    background: #ffe0e0;
    color: #b00020;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
}
</style>
</head>

<body>

<div class="container">

    <h2>
        <?= $id > 0 ? 'Edit Transaksi' : 'Tambah Transaksi'; ?>
    </h2>

    <?php if ($error != '') { ?>
        <div class="error">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php } ?>

    <form method="POST">

        <label>Customer</label>
        <select name="id_user" required>
            <option value="">-- Pilih Customer --</option>

            <?php
            mysqli_data_seek($user_query, 0);
            while ($user = mysqli_fetch_assoc($user_query)) {
            ?>

                <option
                    value="<?= $user['id_akun']; ?>"
                    <?= $id_user == $user['id_akun'] ? 'selected' : ''; ?>
                >
                    <?= htmlspecialchars($user['nama']); ?>
                    (<?= htmlspecialchars($user['nis']); ?>)
                </option>

            <?php } ?>
        </select>

        <label>Menu</label>
        <select name="id_menu" id="id_menu" required onchange="ambilHarga()">
            <option value="">-- Pilih Menu --</option>

            <?php
            mysqli_data_seek($menu_query, 0);
            while ($menu = mysqli_fetch_assoc($menu_query)) {
            ?>

                <option
                    value="<?= $menu['id_menu']; ?>"
                    data-harga="<?= $menu['harga_jual']; ?>"
                    <?= $id_menu == $menu['id_menu'] ? 'selected' : ''; ?>
                >
                    <?= htmlspecialchars($menu['nama']); ?>
                    - Rp <?= number_format($menu['harga_jual'], 0, ',', '.'); ?>
                </option>

            <?php } ?>
        </select>

        <label>Jenis Transaksi</label>
        <select name="jenis_transaksi" required>
            <option value="pembayaran"
                <?= $jenis_transaksi == 'pembayaran' ? 'selected' : ''; ?>>
                Pembayaran
            </option>

            <option value="topup"
                <?= $jenis_transaksi == 'topup' ? 'selected' : ''; ?>>
                Top Up
            </option>
        </select>

        <label>Jumlah (Qty)</label>
        <input
            type="number"
            name="qty"
            id="qty"
            value="<?= $qty; ?>"
            min="1"
            required
            oninput="hitungTotal()"
        >

        <label>Harga Satuan</label>
        <input
            type="number"
            name="harga_satuan"
            id="harga_satuan"
            value="<?= $harga_satuan; ?>"
            min="0"
            required
            oninput="hitungTotal()"
        >

        <label>Total Bayar</label>
        <input
            type="number"
            name="total_bayar"
            id="total_bayar"
            value="<?= $total_bayar; ?>"
            readonly
        >

        <label>Saldo Awal</label>
        <input
            type="number"
            name="saldo_awal"
            value="<?= $saldo_awal; ?>"
            min="0"
            required
        >

        <label>Saldo Akhir</label>
        <input
            type="number"
            name="saldo_akhir"
            value="<?= $saldo_akhir; ?>"
            min="0"
            required
        >

        <label>Status</label>
        <select name="status" required>
            <option value="berhasil"
                <?= $status == 'berhasil' ? 'selected' : ''; ?>>
                Berhasil
            </option>

            <option value="pending"
                <?= $status == 'pending' ? 'selected' : ''; ?>>
                Pending
            </option>

            <option value="dibatalkan"
                <?= $status == 'dibatalkan' ? 'selected' : ''; ?>>
                Dibatalkan
            </option>
        </select>

        <label>Keterangan</label>
        <textarea
            name="keterangan"
            rows="3"
            placeholder="Masukkan keterangan transaksi"
        ><?= htmlspecialchars($keterangan); ?></textarea>

        <button type="submit" name="simpan">
            <?= $id > 0 ? 'Update Transaksi' : 'Simpan Transaksi'; ?>
        </button>

    </form>

    <a href="transaksi_list.php" class="btn-kembali">
        Kembali
    </a>

</div>

<script>
function ambilHarga() {
    const menu = document.getElementById("id_menu");
    const pilihan = menu.options[menu.selectedIndex];

    const harga = pilihan.getAttribute("data-harga");

    if (harga) {
        document.getElementById("harga_satuan").value = harga;
    }

    hitungTotal();
}

function hitungTotal() {
    const qty = parseInt(document.getElementById("qty").value) || 0;
    const harga = parseInt(document.getElementById("harga_satuan").value) || 0;

    document.getElementById("total_bayar").value = qty * harga;
}

hitungTotal();
</script>

</body>
</html>
