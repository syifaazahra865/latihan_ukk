<?php
include "../config.php";

if(isset($_POST['edit'])){
    $id = $_POST['id_menu'];
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    mysqli_query($conn, "UPDATE tb_menu SET nama='$nama', harga_jual='$harga', stok='$stok'
    WHERE id_menu='$id'");
    header("Location: dashboard.php");
}
?>