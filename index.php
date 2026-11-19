<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Data Transaksi</h1>

    <hr>

    <nav>
        <a href="">Dashboard</a>
    </nav>
    <h4>Data Menu</h4>

    <hr>

    <a href="tambah.php">Tambah</a>
    <table border="1">
        <tr>
            <td>ID</td>
            <td style="text-align: center;">Nama</td>
            <td style="text-align: center;">Jenis</td>
            <td style="text-align: center;">Qty</td>
            <td style="text-align: center;">Harga Satuan</td>
            <td style="text-align: center;">Total Bayar</td>
            <td style="text-align: center;">Saldo Awal</td>
            <td style="text-align: center;">Saldo Akhir</td>
            <td style="text-align: center;">Keterangan</td>
            <td style="text-align: center;">Waktu</td>
            <td style="text-align: center;">Aksi</td>
        </tr>
        <tr>
            <?php
            include '../config.php';
            $query = mysqli_query($conn, "SELECT * FROM  tb_transaksi 
                     JOIN tb_menu ON tb_transaksi.id_menu=tb_menu.id_menu");
            while($data = mysqli_fetch_array($query)){
            ?>
            <td><?= $data['id_transaksi']?></td>
            <td><?= $data['nama']?></td>
            <td><?= $data['jenis_transaksi']?></td>
            <td><?= $data['qty']?></td>
            <td>Rp. <?= number_format($data['harga_satuan'],0,',',',')?></td>
            <td>Rp. <?= number_format($data['total_bayar'],0,',',',')?></td>
            <td>Rp. <?= number_format($data['saldo_awal'],0,',',',')?></td>
            <td>Rp. <?= number_format($data['saldo_akhir'],0,',',',')?></td>
            <td><?= $data['keterangan']?></td>
            <td><?= $data['waktu_transaksi']?></td>
            <td>
                <a href="edit.php?id_transaksi=<?= $data['id_transaksi'] ?>">Edit</a>
                <a href="hapus.php?id_transaksi=<?= $data['id_transaksi'] ?>">Hapus</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>