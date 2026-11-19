
<?php
session_start();
require_once '../config.php';

// Cek login superadmin
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'super_admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id_akun'])) {

    $id_akun = intval($_GET['id_akun']);

    if ($id_akun > 0) {

        // Menghapus data user
        $query = mysqli_prepare($conn,
            "DELETE FROM tb_akun WHERE id_akun = ?"
        );

        mysqli_stmt_bind_param($query, "i", $id_akun);
        mysqli_stmt_execute($query);

    }
}

// Kembali ke halaman user list
header("Location: user_list.php");
exit;
?>
