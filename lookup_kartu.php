
<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

function responseJSON($status, $message, $user = null) {
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'user' => $user
    ]);
    exit;
}

// Pastikan login sebagai kasir
if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'kasir') {
    responseJSON('error', 'Akses ditolak. Silakan login sebagai kasir.');
}

// Hanya menerima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responseJSON('error', 'Metode request tidak valid.');
}

// Ambil UID kartu
$uid = trim($_POST['uid_kartu'] ?? '');

if ($uid === '') {
    responseJSON('error', 'UID kartu belum dimasukkan.');
}

try {

    $stmt = mysqli_prepare($conn, "
        SELECT
            id_akun,
            nama,
            nis,
            saldo,
            uid_kartu
        FROM tb_akun
        WHERE uid_kartu = ?
        AND role = 'user'
        LIMIT 1
    ");

    mysqli_stmt_bind_param($stmt, "s", $uid);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        throw new Exception('Gagal membaca data customer.');
    }

    if (mysqli_num_rows($result) === 0) {
        responseJSON('error', 'Kartu RFID tidak terdaftar!');
    }

    $data = mysqli_fetch_assoc($result);

    $user = [
        'id_akun' => (int)$data['id_akun'],
        'nama' => $data['nama'],
        'nis' => $data['nis'],
        'saldo' => (float)$data['saldo'],
        'uid_kartu' => $data['uid_kartu']
    ];

    mysqli_stmt_close($stmt);

    responseJSON('success', 'Kartu berhasil dibaca.', $user);

} catch (Throwable $e) {

    error_log($e->getMessage());

    responseJSON(
        'error',
        'Terjadi kesalahan saat membaca kartu. Periksa database.'
    );
}
