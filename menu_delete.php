
<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['level']) || $_SESSION['level'] != 'super_admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id_menu'])) {

    $id_menu = intval($_GET['id_menu']);

    if ($id_menu > 0) {

        $stmt = mysqli_prepare($conn,
            "DELETE FROM tb_menu WHERE id_menu = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id_menu);
        mysqli_stmt_execute($stmt);
    }
}

header("Location: menu_list.php");
exit;
?>
