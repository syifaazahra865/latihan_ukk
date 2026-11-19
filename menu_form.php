
<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['level']) || $_SESSION['level'] != 'super_admin') {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$nama = '';
$harga_jual = '';
$error = '';

// Mengambil data saat edit
if ($id > 0) {

    $stmt = mysqli_prepare($conn,
        "SELECT * FROM tb_menu WHERE id_menu = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        die("Data menu tidak ditemukan!");
    }

    $nama = $data['nama'];
    $harga_jual = $data['harga_jual'];
}

// Proses simpan
if (isset($_POST['simpan'])) {

    $nama = trim($_POST['nama']);
    $harga_jual = intval($_POST['harga_jual']);

    if ($nama == '') {
        $error = "Nama menu wajib diisi!";
    } elseif ($harga_jual <= 0) {
        $error = "Harga jual harus lebih dari 0!";
    } else {

        if ($id > 0) {

            // Update menu
            $stmt = mysqli_prepare($conn,
                "UPDATE tb_menu
                 SET nama = ?, harga_jual = ?
                 WHERE id_menu = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sii",
                $nama,
                $harga_jual,
                $id
            );

            mysqli_stmt_execute($stmt);

        } else {

            // Tambah menu
            $stmt = mysqli_prepare($conn,
                "INSERT INTO tb_menu (nama, harga_jual)
                 VALUES (?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $nama,
                $harga_jual
            );

            mysqli_stmt_execute($stmt);
        }

        header("Location: menu_list.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Form Menu</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #eee8d2;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.container {
    width: 90%;
    max-width: 500px;
    background: #fffaf5;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

h2 {
    margin-bottom: 25px;
    color: #24351f;
}

label {
    display: block;
    margin-bottom: 7px;
    font-size: 14px;
}

input {
    width: 100%;
    padding: 12px;
    margin-bottom: 18px;
    border: 1px solid #d5c7b6;
    border-radius: 6px;
    outline: none;
}

input:focus {
    border-color: #b87545;
}

button {
    width: 100%;
    padding: 12px;
    background: #b87545;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-bottom: 10px;
}

button:hover {
    background: #965d35;
}

.btn-kembali {
    display: block;
    text-align: center;
    padding: 12px;
    background: #ddd;
    color: #333;
    text-decoration: none;
    border-radius: 6px;
}

.error {
    background: #ffe0e0;
    color: #b00020;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
}
</style>
</head>

<body>

<div class="container">

    <h2>
        <?= $id > 0 ? 'Edit Menu' : 'Tambah Menu'; ?>
    </h2>

    <?php if ($error != '') { ?>
        <div class="error">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php } ?>

    <form method="POST">

        <label>Nama Menu</label>
        <input
            type="text"
            name="nama"
            value="<?= htmlspecialchars($nama); ?>"
            placeholder="Masukkan nama menu"
            required
        >

        <label>Harga Jual</label>
        <input
            type="number"
            name="harga_jual"
            value="<?= htmlspecialchars((string)$harga_jual); ?>"
            placeholder="Masukkan harga menu"
            min="1"
            required
        >

        <button type="submit" name="simpan">
            <?= $id > 0 ? 'Update Menu' : 'Simpan Menu'; ?>
        </button>

    </form>

    <a href="menu_list.php" class="btn-kembali">
        Kembali
    </a>

</div>

</body>
</html>
