<?php
include "../config.php";

$id = $_GET['id_menu'];
$query = mysqli_query($conn, "SELECT * FROM tb_menu WHERE id_menu='$id'");
$data = mysqli_fetch_assoc($query)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
        <form action="proses_edit.php" method="POST">

        <input type="hidden" name="id_menu" value="<?php echo $data['id_menu']; ?>">

    <p>nama</p>
    <input type="text" name="nama" value=<?php echo $data['nama']; ?>>

    <p>harga</p>
    <input type="number" name="harga" value=<?php echo $data['harga_jual']; ?>>

    <p>stok</p>
    <input type="number" name="stok" value=<?php echo $data['stok']; ?>>
    <br>
    <button type="submit" name="edit">Edit</button>
    </form>
    
</body>
</html>