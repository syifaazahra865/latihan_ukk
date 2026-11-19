
<?php
session_start();
require_once '../config.php';

// Cek login superadmin
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'super_admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id_transaksi'])) {

    $id_transaksi = intval($_GET['id_transaksi']);

    if ($id_transaksi > 0) {

        // Menghapus data transaksi
        $query = mysqli_prepare($conn,
            "DELETE FROM tb_transaksi
             WHERE id_transaksi = ?"
        );

        mysqli_stmt_bind_param(
            $query,
            "i",
            $id_transaksi
        );

        mysqli_stmt_execute($query);
    }
}

// Kembali ke halaman transaksi
header("Location: transaksi_list.php");
exit;
?>