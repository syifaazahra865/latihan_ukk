<?php
include "../config.php";

if(isset($_POST['tambah'])){
    $nama = $_POST['nama'];
    $harga_jual = $_POST['harga_jual'];
    $stok = $_POST['stok'];
    mysqli_query($conn, "insert into tb_menu(nama, harga_jual, stok)
    values('$nama', '$harga_jual', '$stok')");
    header("Location: dashboard.php");
}
?>