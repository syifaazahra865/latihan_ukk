
<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

function responseJSON($status, $message, $extra = []) {
    echo json_encode(array_merge([
        'status' => $status,
        'message' => $message
    ], $extra));
    exit;
}

// CEK LOGIN KASIR
if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'kasir') {
    responseJSON('error', 'Akses ditolak. Silakan login kembali.');
}

// HANYA MENERIMA POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responseJSON('error', 'Metode request tidak valid.');
}

try {

    // AMBIL DATA JSON
    $input = json_decode(file_get_contents("php://input"), true);

    if (!is_array($input)) {
        throw new Exception('Data transaksi tidak valid.');
    }

    $id_user = (int)($input['id_user'] ?? 0);
    $items = $input['items'] ?? [];

    if ($id_user <= 0 || !is_array($items) || count($items) === 0) {
        throw new Exception('Data customer atau keranjang kosong.');
    }

    // Mulai transaksi database
    mysqli_begin_transaction($conn);

    // LOCK DATA CUSTOMER
    $stmt_user = mysqli_prepare($conn, "
        SELECT id_akun, nama, saldo
        FROM tb_akun
        WHERE id_akun = ?
        AND role = 'user'
        FOR UPDATE
    ");

    mysqli_stmt_bind_param($stmt_user, "i", $id_user);
    mysqli_stmt_execute($stmt_user);

    $result_user = mysqli_stmt_get_result($stmt_user);
    $customer = mysqli_fetch_assoc($result_user);

    mysqli_stmt_close($stmt_user);

    if (!$customer) {
        throw new Exception('Customer tidak ditemukan.');
    }

    $saldo_awal = (float)$customer['saldo'];
    $saldo_sekarang = $saldo_awal;
    $total_transaksi = 0;

    // GABUNGKAN ITEM DENGAN ID MENU YANG SAMA
    $daftar_item = [];

    foreach ($items as $item) {

        $id_menu = (int)($item['id'] ?? 0);
        $qty = (int)($item['qty'] ?? 0);

        if ($id_menu <= 0 || $qty <= 0) {
            throw new Exception('Data menu atau jumlah tidak valid.');
        }

        if ($qty > 1000) {
            throw new Exception('Jumlah pembelian tidak valid.');
        }

        if (!isset($daftar_item[$id_menu])) {
            $daftar_item[$id_menu] = 0;
        }

        $daftar_item[$id_menu] += $qty;
    }

    // PERIKSA MENU, HARGA, DAN STOK
    $detail_transaksi = [];

    foreach ($daftar_item as $id_menu => $qty) {

        $stmt_menu = mysqli_prepare($conn, "
            SELECT id_menu, nama, harga_jual, stok, status
            FROM tb_menu
            WHERE id_menu = ?
            FOR UPDATE
        ");

        mysqli_stmt_bind_param($stmt_menu, "i", $id_menu);
        mysqli_stmt_execute($stmt_menu);

        $result_menu = mysqli_stmt_get_result($stmt_menu);
        $menu = mysqli_fetch_assoc($result_menu);

        mysqli_stmt_close($stmt_menu);

        if (!$menu) {
            throw new Exception('Menu tidak ditemukan.');
        }

        if ($menu['status'] !== 'aktif') {
            throw new Exception('Menu ' . $menu['nama'] . ' tidak aktif.');
        }

        if ((int)$menu['stok'] < $qty) {
            throw new Exception('Stok ' . $menu['nama'] . ' tidak mencukupi.');
        }

        $harga = (float)$menu['harga_jual'];
        $subtotal = $harga * $qty;

        $total_transaksi += $subtotal;

        $detail_transaksi[] = [
            'id_menu' => (int)$menu['id_menu'],
            'nama' => $menu['nama'],
            'qty' => $qty,
            'harga' => $harga,
            'subtotal' => $subtotal
        ];
    }

    // PERIKSA SALDO
    if ($total_transaksi <= 0) {
        throw new Exception('Total transaksi tidak valid.');
    }

    if ($saldo_awal < $total_transaksi) {
        throw new Exception('Saldo customer tidak mencukupi.');
    }

    // SALDO AKHIR
    $saldo_akhir = $saldo_awal - $total_transaksi;

    // UPDATE SALDO CUSTOMER
    $stmt_update_user = mysqli_prepare($conn, "
        UPDATE tb_akun
        SET saldo = ?
        WHERE id_akun = ?
    ");

    mysqli_stmt_bind_param(
        $stmt_update_user,
        "di",
        $saldo_akhir,
        $id_user
    );

    mysqli_stmt_execute($stmt_update_user);
    mysqli_stmt_close($stmt_update_user);

    // ID ADMIN / KASIR (JIKA TERSEDIA DI SESSION)
    $id_admin = isset($_SESSION['id_akun'])
        ? (int)$_SESSION['id_akun']
        : null;

    // SIMPAN SETIAP MENU SEBAGAI BARIS TRANSAKSI
    $saldo_saat_ini = $saldo_awal;

    foreach ($detail_transaksi as $detail) {

        $saldo_sebelum = $saldo_saat_ini;
        $saldo_sesudah = $saldo_sebelum - $detail['subtotal'];

        $jenis_transaksi = 'bayar';
        $status = 'berhasil';
        $keterangan = 'Pembayaran kasir';

        $stmt_insert = mysqli_prepare($conn, "
            INSERT INTO tb_transaksi (
                id_user,
                id_admin,
                id_menu,
                jenis_transaksi,
                qty,
                harga_satuan,
                total_bayar,
                saldo_awal,
                saldo_akhir,
                status,
                keterangan,
                waktu_transaksi
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        mysqli_stmt_bind_param(
            $stmt_insert,
            "iiisiddddss",
            $id_user,
            $id_admin,
            $detail['id_menu'],
            $jenis_transaksi,
            $detail['qty'],
            $detail['harga'],
            $detail['subtotal'],
            $saldo_sebelum,
            $saldo_sesudah,
            $status,
            $keterangan
        );

        mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);

        // KURANGI STOK MENU
        $stmt_stok = mysqli_prepare($conn, "
            UPDATE tb_menu
            SET stok = stok - ?
            WHERE id_menu = ?
            AND stok >= ?
        ");

        mysqli_stmt_bind_param(
            $stmt_stok,
            "iii",
            $detail['qty'],
            $detail['id_menu'],
            $detail['qty']
        );

        mysqli_stmt_execute($stmt_stok);

        if (mysqli_stmt_affected_rows($stmt_stok) !== 1) {
            mysqli_stmt_close($stmt_stok);
            throw new Exception('Gagal memperbarui stok menu.');
        }

        mysqli_stmt_close($stmt_stok);

        $saldo_saat_ini = $saldo_sesudah;
    }

    // SIMPAN SEMUA PERUBAHAN
    mysqli_commit($conn);

    responseJSON('success', 'Transaksi berhasil!', [
        'nama' => $customer['nama'],
        'total_bayar' => $total_transaksi,
        'saldo_awal' => $saldo_awal,
        'saldo_akhir' => $saldo_akhir
    ]);

} catch (Throwable $e) {

    // BATALKAN SEMUA PERUBAHAN JIKA GAGAL
    mysqli_rollback($conn);

    error_log('Transaksi kasir: ' . $e->getMessage());

    responseJSON('error', $e->getMessage());
}
