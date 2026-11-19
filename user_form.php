
<?php
session_start();
require_once '../config.php';

// Cek login superadmin
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'super_admin') {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$nis = '';
$nama = '';
$uid_kartu = '';
$saldo = '';
$shift = 'pagi';

$error = '';

// Jika mode edit, mengambil data user
if ($id > 0) {
    $query = mysqli_prepare($conn,
        "SELECT * FROM tb_akun WHERE id_akun = ?"
    );

    mysqli_stmt_bind_param($query, "i", $id);
    mysqli_stmt_execute($query);

    $result = mysqli_stmt_get_result($query);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        die("Data user tidak ditemukan!");
    }

    $nis = $data['nis'];
    $nama = $data['nama'];
    $uid_kartu = $data['uid_kartu'];
    $saldo = $data['saldo'];
    $shift = $data['shift'];
}

// Proses simpan
if (isset($_POST['simpan'])) {

    $nis = trim($_POST['nis']);
    $nama = trim($_POST['nama']);
    $uid_kartu = trim($_POST['uid_kartu']);
    $saldo = intval($_POST['saldo']);
    $shift = $_POST['shift'];

    if ($nis == '' || $nama == '' || $uid_kartu == '') {
        $error = "Semua data wajib diisi!";
    } elseif ($saldo < 0) {
        $error = "Saldo tidak boleh kurang dari 0!";
    } else {

        // Mengecek UID kartu duplikat
        $cek = mysqli_prepare($conn,
            "SELECT id_akun FROM tb_akun
             WHERE uid_kartu = ? AND id_akun != ?"
        );

        mysqli_stmt_bind_param($cek, "si", $uid_kartu, $id);
        mysqli_stmt_execute($cek);

        $cek_result = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($cek_result) > 0) {

            $error = "UID kartu sudah digunakan!";

        } else {

            if ($id > 0) {

                // Update data user
                $query = mysqli_prepare($conn,
                    "UPDATE tb_akun
                     SET nis = ?, nama = ?, uid_kartu = ?,
                         saldo = ?, shift = ?
                     WHERE id_akun = ?"
                );

                mysqli_stmt_bind_param(
                    $query,
                    "sssisi",
                    $nis,
                    $nama,
                    $uid_kartu,
                    $saldo,
                    $shift,
                    $id
                );

                mysqli_stmt_execute($query);

                header("Location: user_list.php");
                exit;

            } else {

                // Tambah data user
                $role = 'user';

                $query = mysqli_prepare($conn,
                    "INSERT INTO tb_akun
                    (role, nama, nis, uid_kartu, saldo, shift)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $query,
                    "ssssis",
                    $role,
                    $nama,
                    $nis,
                    $uid_kartu,
                    $saldo,
                    $shift
                );

                mysqli_stmt_execute($query);

                header("Location: user_list.php");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Form User</title>

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
    max-width: 500px;
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

input, select {
    width: 100%;
    padding: 12px;
    margin-bottom: 18px;
    border: 1px solid #d5c7b6;
    border-radius: 6px;
    outline: none;
}

input:focus, select:focus {
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
        <?= $id > 0 ? 'Edit User' : 'Tambah User'; ?>
    </h2>

    <?php if ($error != '') { ?>
        <div class="error">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php } ?>

    <form method="POST">

        <label>NIS</label>
        <input
            type="text"
            name="nis"
            value="<?= htmlspecialchars($nis); ?>"
            placeholder="Masukkan NIS"
            required
        >

        <label>Nama</label>
        <input
            type="text"
            name="nama"
            value="<?= htmlspecialchars($nama); ?>"
            placeholder="Masukkan nama user"
            required
        >

        <label>UID Kartu</label>
        <input
            type="text"
            name="uid_kartu"
            value="<?= htmlspecialchars($uid_kartu); ?>"
            placeholder="Masukkan UID kartu RFID"
            required
        >

        <label>Saldo</label>
        <input
            type="number"
            name="saldo"
            value="<?= htmlspecialchars((string)$saldo); ?>"
            placeholder="Masukkan saldo"
            min="0"
            required
        >

        <label>Shift</label>
        <select name="shift" required>
            <option value="pagi"
                <?= $shift == 'pagi' ? 'selected' : ''; ?>>
                Pagi
            </option>

            <option value="siang"
                <?= $shift == 'siang' ? 'selected' : ''; ?>>
                Siang
            </option>

            <option value="malam"
                <?= $shift == 'malam' ? 'selected' : ''; ?>>
                Malam
            </option>
        </select>

        <button type="submit" name="simpan">
            <?= $id > 0 ? 'Update User' : 'Simpan User'; ?>
        </button>

    </form>

    <a href="user_list.php" class="btn-kembali">
        Kembali
    </a>

</div>

</body>
</html>
