
<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'kasir') {
    header("Location: login.php");
    exit;
}

header("Location: transaksi_kasir.php");
exit;
?>
