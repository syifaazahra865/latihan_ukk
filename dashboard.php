<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <center><h2>SELAMAT DATANG</h2></center>

    <hr>

<nav>
    <a href="Logout"></a>
</nav>
<h4>Data Menu</h4>

    <hr>
    <a href="transaksi/transaksi.php">Transaksi</a>
    <a href="tambah.php">Tambah</a>
    <table border="1">
        <tr>
            <td>ID</td>
            <td style="text-align: center;">Nama</td>
            <td style="text-align: center;">Harga</td>
            <td style="text-align: center;">Stok</td>
            <td style="text-align: center;">Aksi</td>
        </tr>
        <tr>
          <?php
          include '../config.php';
          $query = mysqli_query($conn, "SELECT * FROM tb_menu");
          while($data = mysqli_fetch_array($query)){
          ?>
          <td><?= $data['id_menu']?></td>
          <td><?= $data['nama']?></td>
          <td><?= $data['harga_jual']?></td>
          <td><?= $data['stok']?></td>
          <td>
            <a href="edit.php?id_menu=<?= $data['id_menu'] ?>">Edit</a>
            <a href="hapus.php?id_menu=<?= $data['id_menu'] ?>">Hapus</a>
          </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>