
<?php
session_start();
require_once '../config.php';

function e($value) {
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

if (!isset($_SESSION['level']) || $_SESSION['level'] != 'super_admin') {
    header("Location: ../login.php");
    exit;
}

$query = mysqli_query($conn, "
    SELECT 
        t.*,
        u.nama AS nama_user,
        m.nama AS nama_menu
    FROM tb_transaksi t
    LEFT JOIN tb_akun u ON t.id_user = u.id_akun
    LEFT JOIN tb_menu m ON t.id_menu = m.id_menu
    ORDER BY t.id_transaksi DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Menu Transaksi</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    display: flex;
    min-height: 100vh;
    background: #eee8d2;
}

.sidebar {
    width: 220px;
    background: #142b17;
    color: white;
    padding: 25px 15px;
    display: flex;
    flex-direction: column;
}

.logo {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 35px;
}

.logo span {
    color: #b87545;
}

.menu {
    list-style: none;
}

.menu li {
    margin-bottom: 10px;
}

.menu a {
    display: block;
    padding: 13px;
    color: #ddd;
    text-decoration: none;
    border-radius: 7px;
}

.menu a:hover,
.menu a.active {
    background: #b87545;
    color: white;
}

.logout {
    margin-top: auto;
}

.logout a {
    color: white;
    text-decoration: none;
    padding: 12px;
    display: block;
}

.main {
    flex: 1;
    padding: 30px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.header h2 {
    color: #24351f;
}

.profile {
    text-align: right;
    font-size: 13px;
    color: #555;
}

.btn-tambah {
    display: inline-block;
    padding: 10px 15px;
    background: #b87545;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    margin-bottom: 20px;
}

.table-container {
    background: #fffaf5;
    border-radius: 10px;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 13px;
    text-align: center;
    border-bottom: 1px solid #eee;
    font-size: 13px;
}

th {
    background: #e9e3d2;
    color: #24351f;
}

.btn-edit {
    background: #d8e8d3;
    color: #24351f;
    padding: 7px 10px;
    text-decoration: none;
    border-radius: 5px;
}

.btn-hapus {
    background: #f4d5d5;
    color: #a83232;
    padding: 7px 10px;
    text-decoration: none;
    border-radius: 5px;
}

.status {
    padding: 5px 8px;
    border-radius: 5px;
    font-size: 12px;
}

.berhasil {
    background: #d8e8d3;
    color: #24351f;
}

.pending {
    background: #fff0c2;
    color: #806000;
}

.dibatalkan {
    background: #f4d5d5;
    color: #a83232;
}

@media(max-width: 700px) {
    body {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
    }

    .main {
        padding: 15px;
    }
}
</style>
</head>

<body>

<div class="sidebar">

    <div class="logo">
        ☕ Cashless<br>
        <span>Cafe Sekolah</span>
    </div>

    <ul class="menu">
        <li>
            <a href="dashboard_superadmin.php">
                ▣ Dashboard
            </a>
        </li>

        <li>
            <a href="user_list.php">
                ♙ Menu User
            </a>
        </li>

        <li>
            <a link href="menu_list.php">
                ▤ Menu Transaksi
            </a>
        </li>
    </ul>

    <div class="logout">
        <a href="../logout.php">↪ Keluar</a>
    </div>

</div>

<div class="main">

    <div class="header">
        <h2>Menu Transaksi</h2>

        <div class="profile">
            <?= htmlspecialchars($_SESSION['username']); ?>
            <br>
            Super Admin
        </div>
    </div>

    <a href="transaksi_form.php" class="btn-tambah">
        + Tambah Transaksi
    </a>

    <div class="table-container">

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Menu</th>
                    <th>Jenis</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Total</th>
                    <th>Saldo Awal</th>
                    <th>Saldo Akhir</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

<?php if (mysqli_num_rows($query) > 0) { ?>

    <?php while ($data = mysqli_fetch_assoc($query)) { ?>

    <tr>
        <td><?= $data['id_transaksi']; ?></td>

        <td>
            <?= e($data['nama_user'] ?? '-'); ?>
        </td>

        <td>
            <?= e($data['nama_menu'] ?? '-'); ?>
        </td>

        <td>
            <?= e($data['jenis_transaksi'] ?? '-'); ?>
        </td>

        <td>
            <?= $data['qty'] ?? 0; ?>
        </td>

        <td>
            Rp <?= number_format((float)($data['harga_satuan'] ?? 0), 0, ',', '.'); ?>
        </td>

        <td>
            Rp <?= number_format((float)($data['total_bayar'] ?? 0), 0, ',', '.'); ?>
        </td>

        <td>
            Rp <?= number_format((float)($data['saldo_awal'] ?? 0), 0, ',', '.'); ?>
        </td>

        <td>
            Rp <?= number_format((float)($data['saldo_akhir'] ?? 0), 0, ',', '.'); ?>
        </td>

        <td>
            <?php
            $status = $data['status'] ?? 'berhasil';

            $class_status = 'berhasil';

            if ($status == 'berhasil') {
                $class_status = 'berhasil';
            } elseif ($status == 'dibatalkan') {
                $class_status = 'dibatalkan';
            }
            ?>

            <span class="status <?= $class_status ?>">
                <?= e($status); ?>
            </span>
        </td>

        <td>
            <?= e($data['waktu_transaksi'] ?? '-'); ?>
        </td>

        <td>
            <a
                href="transaksi_form.php?id=<?= $data['id_transaksi']; ?>"
                class="btn-edit"
            >
                Edit
            </a>

            <a
                href="transaksi_delete.php?id_transaksi=<?= $data['id_transaksi']; ?>"
                class="btn-hapus"
                onclick="return confirm('Yakin ingin menghapus transaksi ini?')"
            >
                Hapus
            </a>
        </td>
    </tr>

    <?php } ?>

<?php } else { ?>

    <tr>
        <td colspan="12">
            Belum ada data transaksi.
        </td>
    </tr>

<?php } ?>

</tbody>
        </table>

    </div>

</div>

</body>
</html>