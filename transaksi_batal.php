<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Customer belum login!'
    ]);
    exit;
}

$id_user = $_SESSION['id_user'];
$id_transaksi = intval($_POST['id_transaksi'] ?? 0);

if ($id_transaksi <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID transaksi tidak valid!'
    ]);
    exit;
}

mysqli_begin_transaction($conn);

try {

    // Ambil transaksi dan kunci data
    $stmt = mysqli_prepare($conn, "
        SELECT *
        FROM tb_transaksi
        WHERE id_transaksi = ?
        AND id_user = ?
        FOR UPDATE
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $id_transaksi,
        $id_user
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $transaksi = mysqli_fetch_assoc($result);

    if (!$transaksi) {
        throw new Exception("Transaksi tidak ditemukan!");
    }

    if ($transaksi['status'] != 'berhasil') {
        throw new Exception("Transaksi sudah dibatalkan!");
    }

    $total = $transaksi['total_bayar'];
    $qty = $transaksi['qty'];
    $id_menu = $transaksi['id_menu'];

    // Mengembalikan saldo customer
    $stmt_saldo = mysqli_prepare($conn, "
        UPDATE tb_akun
        SET saldo = saldo + ?
        WHERE id_akun = ?
    ");

    mysqli_stmt_bind_param(
        $stmt_saldo,
        "di",
        $total,
        $id_user
    );

    mysqli_stmt_execute($stmt_saldo);

    // Mengembalikan stok menu
    $stmt_stok = mysqli_prepare($conn, "
        UPDATE tb_menu
        SET stok = stok + ?
        WHERE id_menu = ?
    ");

    mysqli_stmt_bind_param(
        $stmt_stok,
        "ii",
        $qty,
        $id_menu
    );

    mysqli_stmt_execute($stmt_stok);

    // Mengubah status transaksi
    $stmt_update = mysqli_prepare($conn, "
        UPDATE tb_transaksi
        SET status = 'dibatalkan',
            keterangan = 'Dibatalkan oleh kasir'
        WHERE id_transaksi = ?
    ");

    mysqli_stmt_bind_param(
        $stmt_update,
        "i",
        $id_transaksi
    );

    mysqli_stmt_execute($stmt_update);

    mysqli_commit($conn);

    echo json_encode([
        'status' => 'success',
        'message' => 'Transaksi berhasil dibatalkan!'
    ]);

} catch (Exception $e) {

    mysqli_rollback($conn);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>