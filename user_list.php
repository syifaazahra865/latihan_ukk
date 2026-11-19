
<?php
session_start();
require_once '../config.php';

// Cek login superadmin
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'super_admin') {
    header("Location: ../login.php");
    exit;
}

// Mengambil data user
$query = mysqli_query($conn, "SELECT * FROM tb_akun ORDER BY id_akun DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Menu User</title>

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
    padding: 15px;
    text-align: center;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

th {
    background: #e9e3d2;
    color: #24351f;
}

.btn-edit {
    background: #d8e8d3;
    color: #24351f;
    padding: 7px 12px;
    text-decoration: none;
    border-radius: 5px;
}

.btn-hapus {
    background: #f4d5d5;
    color: #a83232;
    padding: 7px 12px;
    text-decoration: none;
    border-radius: 5px;
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
            <a link href="menu_list.php">
                ♙ Menu User
            </a>
        </li>

        <li>
            <a href="../admin/transaksi_list.php">
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
        <h2>Menu User</h2>

        <div class="profile">
            <?= htmlspecialchars($_SESSION['username']); ?>
            <br>
            Super Admin
        </div>
    </div>

    <a href="user_form.php" class="btn-tambah">
        + Tambah User
    </a>

    <div class="table-container">

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>UID Kartu</th>
                    <th>Saldo</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                <?php if (mysqli_num_rows($query) > 0) { ?>

                    <?php while ($user = mysqli_fetch_assoc($query)) { ?>

                    <tr>
                        <td><?= $user['id_akun']; ?></td>

                        <td>
                            <?= htmlspecialchars($user['nis']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($user['nama']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($user['uid_kartu']); ?>
                        </td>

                        <td>
                            Rp <?= number_format($user['saldo'], 0, ',', '.'); ?>
                        </td>

                        <td>
                            <a
                                href="user_form.php?id=<?= $user['id_akun']; ?>"
                                class="btn-edit"
                            >
                                Edit
                            </a>

                            <a
                                href="user_delete.php?id_akun=<?= $user['id_akun']; ?>"
                                class="btn-hapus"
                                onclick="return confirm('Yakin ingin menghapus user ini?')"
                            >
                                Hapus
                            </a>
                        </td>
                    </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>
                        <td colspan="6">
                            Belum ada data user.
                        </td>
                    </tr>

                <?php } ?>

            </tbody>
        </table>

    </div>

</div>

</body>
</html>
