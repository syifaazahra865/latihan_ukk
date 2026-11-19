
<?php
session_start();
require_once '../config.php';

// Cek login superadmin
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'super_admin') {
    header("Location: login.php");
    exit;
}

// Menghitung total user
$query_user = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tb_akun");
$data_user = mysqli_fetch_assoc($query_user);
$total_user = $data_user['total'];

// Menghitung transaksi hari ini
$query_transaksi = mysqli_query($conn,
    "SELECT COUNT(*) AS total
     FROM tb_transaksi
     WHERE DATE(waktu_transaksi) = CURDATE()
     AND status = 'berhasil'"
);
$data_transaksi = mysqli_fetch_assoc($query_transaksi);
$total_transaksi = $data_transaksi['total'];

// Menghitung total saldo
$query_saldo = mysqli_query($conn,
    "SELECT SUM(saldo) AS total FROM tb_akun"
);
$data_saldo = mysqli_fetch_assoc($query_saldo);
$total_saldo = $data_saldo['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Superadmin</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #eee8d2;
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 230px;
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
    padding-left: 10px;
}

.logo span {
    color: #c0804c;
}

.menu {
    list-style: none;
}

.menu li {
    margin-bottom: 10px;
}

.menu a {
    display: block;
    text-decoration: none;
    color: #ddd;
    padding: 13px;
    border-radius: 8px;
    font-size: 14px;
}

.menu a.active,
.menu a:hover {
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
    margin-bottom: 30px;
}

.header h2 {
    color: #24351f;
}

.profile {
    font-size: 13px;
    color: #555;
}

.cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.card {
    background: #fffaf5;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
}

.card h4 {
    color: #777;
    font-size: 14px;
    margin-bottom: 15px;
}

.card h2 {
    color: #24351f;
    font-size: 25px;
}

.welcome {
    margin-top: 25px;
    background: #fffaf5;
    padding: 25px;
    border-radius: 12px;
    color: #555;
    line-height: 1.7;
}

@media(max-width: 800px) {
    .sidebar {
        width: 180px;
    }

    .cards {
        grid-template-columns: 1fr;
    }
}

@media(max-width: 600px) {
    body {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
    }

    .menu {
        display: flex;
        flex-wrap: wrap;
    }

    .main {
        padding: 20px;
    }
}
</style>
</head>

<body>

<div class="sidebar">

    <div class="logo">☕ Cashless<br>
        <span>Cafe Sekolah</span>
    </div>


    <ul class="menu">

    <li>
        <a href="dashboard_superadmin.php" class="active">
            ▣ Dashboard
        </a>
    </li>

    <li>
        <a link href="menu_list.php">
            ☕ Kelola Menu
        </a>
    </li>

    <li>
        <a href="user_list.php">
            ♙ Menu User
        </a>
    </li>

    <li>
        <a href="transaksi_list.php">
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
        <h2>Dashboard</h2>

        <div class="profile">
            <?= htmlspecialchars($_SESSION['username']); ?>
            <br>
            Super Admin
        </div>
    </div>

    <div class="cards">

        <div class="card">
            <h4>Total User</h4>
            <h2><?= $total_user; ?></h2>
        </div>

        <div class="card">
            <h4>Transaksi Hari Ini</h4>
            <h2><?= $total_transaksi; ?></h2>
        </div>

        <div class="card">
            <h4>Total Saldo Beredar</h4>
            <h2>
                Rp <?= number_format($total_saldo, 0, ',', '.'); ?>
            </h2>
        </div>

    </div>

    <div class="welcome">
        <h3>Selamat datang, Superadmin!</h3>
        <p>
            Gunakan menu di sebelah kiri untuk mengelola data user
            dan transaksi pada sistem pembayaran cashless Cafe Sekolah.
        </p>
    </div>

</div>

</body>
</html>
