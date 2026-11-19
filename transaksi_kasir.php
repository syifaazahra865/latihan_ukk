
<?php
session_start();
require_once 'config.php';

// Cek login kasir
if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'kasir') {
    header("Location: login.php");
    exit;
}

// Ambil daftar menu
$query_menu = mysqli_query($conn, "
    SELECT * FROM tb_menu
    ORDER BY id_menu DESC
");

if (!$query_menu) {
    die("Gagal mengambil menu: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Transaksi Kasir - Cafe Sekolah</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #eee8d2;
    color: #24351f;
}

/* NAVBAR */
.navbar {
    background: #142b17;
    color: white;
    padding: 13px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.brand {
    font-weight: bold;
    font-size: 17px;
    line-height: 1.4;
}

.brand span {
    color: #b87545;
    font-size: 12px;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.nav-right button {
    padding: 9px 13px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}

.btn-riwayat {
    background: #e9eee3;
    color: #24351f;
}

.btn-logout {
    background: #b87545;
    color: white;
}

.btn-logout:hover {
    background: #965d35;
}

/* CONTAINER */
.container {
    padding: 25px;
    display: none;
}

.container.active {
    display: block;
}

/* INFORMASI CUSTOMER */
.user-info {
    background: #fffaf5;
    padding: 18px 22px;
    border-radius: 10px;
    margin-bottom: 22px;

    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.user-info h3 {
    margin-bottom: 5px;
}

.user-info p {
    color: #777;
    font-size: 13px;
}

.saldo {
    font-weight: bold;
    font-size: 16px;
}

.btn-ganti {
    background: #e9eee3;
    color: #24351f;
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

/* MENU DAN KERANJANG */
.menu-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 22px;
}

.judul-menu {
    margin-bottom: 18px;
}

.menu-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(145px, 1fr));
    gap: 15px;
}

.menu-card {
    background: #fffaf5;
    padding: 17px;
    border-radius: 9px;
    transition: 0.2s;
}

.menu-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.menu-card h3 {
    margin-bottom: 10px;
    font-size: 15px;
}

.menu-card p {
    color: #777;
    font-size: 12px;
    margin-bottom: 5px;
}

.menu-card button {
    margin-top: 12px;
    width: 100%;
    padding: 10px;
    background: #b87545;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.menu-card button:hover {
    background: #965d35;
}

.menu-card button:disabled {
    background: #aaa;
    cursor: not-allowed;
}

/* KERANJANG */
.cart {
    background: #fffaf5;
    border-radius: 10px;
    padding: 20px;
    height: fit-content;
}

.cart h3 {
    margin-bottom: 18px;
}

.cart-item {
    border-bottom: 1px solid #ddd;
    padding: 12px 0;
    display: flex;
    justify-content: space-between;
    gap: 10px;
    font-size: 13px;
}

.cart-item button {
    margin-top: 5px;
    border: none;
    background: #e8c9b5;
    padding: 5px 8px;
    cursor: pointer;
    border-radius: 4px;
}

.cart-empty {
    text-align: center;
    color: #999;
    padding: 25px 5px;
    font-size: 13px;
}

.total {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #ddd;

    display: flex;
    justify-content: space-between;
    font-weight: bold;
    font-size: 17px;
}

.pay-btn {
    margin-top: 20px;
    width: 100%;
    padding: 13px;
    background: #b87545;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}

.pay-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

/* OVERLAY MODAL */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
    z-index: 1000;
    padding: 15px;
}

.modal-content {
    background: #fffaf5;
    width: 380px;
    max-width: 100%;
    padding: 30px;
    border-radius: 13px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    max-height: 85vh;
    overflow-y: auto;
}

.modal-icon {
    font-size: 45px;
    margin-bottom: 12px;
}

.modal-content h2 {
    margin-bottom: 10px;
    color: #24351f;
}

.modal-content p {
    color: #777;
    font-size: 13px;
    line-height: 1.6;
}

/* INPUT RFID */
.rfid-input {
    width: 100%;
    padding: 12px;
    margin-top: 20px;
    border: 1px solid #d5c7b6;
    border-radius: 7px;
    text-align: center;
    outline: none;
}

.rfid-input:focus {
    border-color: #b87545;
}

.btn-modal {
    padding: 11px 18px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    margin-top: 15px;
}

.btn-primary {
    background: #b87545;
    color: white;
}

.btn-secondary {
    background: #ddd;
    color: #333;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.modal-actions button {
    flex: 1;
}

/* RIWAYAT */
.riwayat-content {
    width: 500px;
    text-align: left;
}

.riwayat-content h2 {
    font-size: 19px;
}

.riwayat-item {
    background: white;
    border: 1px solid #eee;
    padding: 14px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.riwayat-header {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    align-items: center;
}

.status {
    background: #dcebd7;
    color: #285b27;
    padding: 5px 9px;
    border-radius: 5px;
    font-size: 11px;
}

.status.batal {
    background: #f5d5d5;
    color: #a00000;
}

.riwayat-item p {
    margin-top: 7px;
    font-size: 12px;
}

.btn-tutup {
    background: #777;
    color: white;
    padding: 7px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.riwayat-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

@media(max-width: 800px) {
    .menu-container {
        grid-template-columns: 1fr;
    }
}

@media(max-width: 500px) {
    .navbar {
        padding: 12px;
    }

    .nav-right {
        gap: 5px;
    }

    .nav-right button {
        padding: 8px;
        font-size: 11px;
    }

    .container {
        padding: 15px;
    }

    .brand {
        font-size: 13px;
    }

    .user-info {
        padding: 15px;
    }
}
</style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">

    <div class="brand">
        ☕ Kasir - Cafe Sekolah
        <br>
        <span>Sistem Pembayaran Cashless</span>
    </div>

    <div class="nav-right">

        <span>
            <?= htmlspecialchars($_SESSION['username'] ?? 'Kasir'); ?>
        </span>

        <button class="btn-riwayat" onclick="tampilRiwayat()">
            Riwayat
        </button>

        <button class="btn-logout"
            onclick="location.href='logout.php'">
            Keluar
        </button>

    </div>

</div>

<!-- DASHBOARD TRANSAKSI -->
<div class="container" id="dashboardTransaksi">

    <!-- INFORMASI CUSTOMER -->
    <div class="user-info">

        <div>
            <h3 id="nama_user">-</h3>
            <p id="nis_user">NIS: -</p>
        </div>

        <div>
            <div class="saldo">Saldo</div>
            <div id="saldo_user">Rp 0</div>
        </div>

        <button class="btn-ganti" onclick="tampilRFID()">
            Ganti Kartu
        </button>

    </div>

    <!-- MENU DAN KERANJANG -->
    <div class="menu-container">

        <div>

            <h2 class="judul-menu">Daftar Menu</h2>

            <div class="menu-list">

                <?php while ($menu = mysqli_fetch_assoc($query_menu)) { ?>

                    <div class="menu-card">

                        <h3>
                            <?= htmlspecialchars($menu['nama']); ?>
                        </h3>

                        <p>
                            Harga:
                            Rp <?= number_format($menu['harga_jual'], 0, ',', '.'); ?>
                        </p>

                        <p>
                            Stok: <?= (int)$menu['stok']; ?>
                        </p>

                        <button
                            onclick='tambahKeranjang(
                                <?= (int)$menu['id_menu']; ?>,
                                <?= json_encode($menu['nama']); ?>,
                                <?= (float)$menu['harga_jual']; ?>,
                                <?= (int)$menu['stok']; ?>
                            )'
                            <?= (int)$menu['stok'] <= 0 ? 'disabled' : ''; ?>
                        >
                            + Tambah
                        </button>

                    </div>

                <?php } ?>

            </div>

        </div>

        <!-- KERANJANG -->
        <div class="cart">

            <h3>Keranjang</h3>

            <div id="keranjang">
                <div class="cart-empty">
                    Belum ada item dipilih.
                </div>
            </div>

            <div class="total">
                <span>Total</span>
                <span id="total">Rp 0</span>
            </div>

            <button
                class="pay-btn"
                id="btnBayar"
                onclick="konfirmasiBayar()"
                disabled
            >
                Bayar
            </button>

        </div>

    </div>

</div>

<!-- MODAL RFID -->
<div class="modal" id="modalRFID">

    <div class="modal-content">

        <div class="modal-icon">☕</div>

        <h2>Tempelkan Kartu</h2>

        <p>
            Silakan tempelkan kartu RFID konsumen
            ke reader untuk memulai transaksi.
        </p>

        <input
            type="text"
            id="uid_kartu"
            class="rfid-input"
            placeholder="Menunggu kartu di tap..."
            autocomplete="off"
        >

        <button
            class="btn-modal btn-primary"
            style="width:100%;"
            onclick="loginKartu()"
        >
            Masuk
        </button>

    </div>

</div>

<!-- MODAL KONFIRMASI PEMBAYARAN -->
<div class="modal" id="modalKonfirmasi">

    <div class="modal-content">

        <div class="modal-icon">☕</div>

        <h2>Konfirmasi Pembayaran</h2>

        <p id="teksKonfirmasi"></p>

        <div class="modal-actions">

            <button
                class="btn-modal btn-secondary"
                onclick="tutupKonfirmasi()"
            >
                Batal
            </button>

            <button
                class="btn-modal btn-primary"
                id="btnKonfirmasi"
                onclick="bayar()"
            >
                Ya, Bayar
            </button>

        </div>

    </div>

</div>

<!-- MODAL PEMBAYARAN BERHASIL -->
<div class="modal" id="modalBerhasil">

    <div class="modal-content">

        <div class="modal-icon">✓</div>

        <h2>Transaksi Berhasil!</h2>

        <p id="detailBerhasil"></p>

        <button
            class="btn-modal btn-primary"
            style="width:100%;"
            onclick="tutupBerhasil()"
        >
            Selesai
        </button>

    </div>

</div>

<!-- MODAL RIWAYAT -->
<div class="modal" id="modalRiwayat">

    <div class="modal-content riwayat-content">

        <div class="riwayat-title">

            <h2>Riwayat Transaksi Hari Ini</h2>

            <button
                class="btn-tutup"
                onclick="tutupRiwayat()"
            >
                ✕
            </button>

        </div>

        <hr style="margin-bottom:15px;">

        <div id="isiRiwayat">
            <p>Memuat riwayat transaksi...</p>
        </div>

    </div>

</div>

<script>

let user = null;
let keranjang = [];

let sedangLogin = false;
let sedangBayar = false;

// FORMAT RUPIAH
function formatRupiah(angka) {
    return "Rp " + Number(angka || 0).toLocaleString("id-ID");
}

// MEMBUKA MODAL RFID
function tampilRFID() {

    user = null;
    keranjang = [];

    document.getElementById("nama_user").innerText = "-";
    document.getElementById("nis_user").innerText = "NIS: -";
    document.getElementById("saldo_user").innerText = "Rp 0";

    document.getElementById("dashboardTransaksi")
        .classList.remove("active");

    document.getElementById("modalRFID").style.display = "flex";

    document.getElementById("uid_kartu").value = "";

    tampilKeranjang();

    setTimeout(() => {
        document.getElementById("uid_kartu").focus();
    }, 200);
}

// MEMBACA RESPONS SERVER
async function bacaRespons(response) {

    const text = await response.text();

    let data;

    try {
        data = JSON.parse(text);
    } catch (error) {
        console.error("Respons server:", text);
        throw new Error("Respons server tidak valid. Periksa file PHP.");
    }

    if (!response.ok) {
        throw new Error(data.message || "Terjadi kesalahan server.");
    }

    return data;
}

// LOGIN KARTU
async function loginKartu() {

    if (sedangLogin) return;

    const input = document.getElementById("uid_kartu");
    const uid = input.value.trim();

    if (uid === "") {
        tampilPesan("Silakan tap kartu terlebih dahulu!");
        input.focus();
        return;
    }

    sedangLogin = true;
    input.disabled = true;

    try {

        const response = await fetch("lookup_kartu.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "uid_kartu=" + encodeURIComponent(uid)
        });

        const data = await bacaRespons(response);

        if (data.status !== "success") {
            throw new Error(data.message || "Kartu tidak terdaftar!");
        }

        if (!data.user || !data.user.id_akun) {
            throw new Error("Data pelanggan tidak lengkap!");
        }

        user = data.user;
        keranjang = [];

        document.getElementById("nama_user").innerText =
            user.nama || "-";

        document.getElementById("nis_user").innerText =
            "NIS: " + (user.nis || "-");

        document.getElementById("saldo_user").innerText =
            formatRupiah(user.saldo);

        document.getElementById("modalRFID").style.display = "none";

        document.getElementById("dashboardTransaksi")
            .classList.add("active");

        tampilKeranjang();

    } catch (error) {

        tampilPesan(error.message);

        user = null;
        input.value = "";

    } finally {

        sedangLogin = false;
        input.disabled = false;

        if (!user) {
            input.focus();
        }

    }
}

// RFID ENTER OTOMATIS
document.getElementById("uid_kartu")
.addEventListener("keydown", function(event) {

    if (event.key === "Enter") {
        event.preventDefault();
        loginKartu();
    }

});

// MENAMBAH MENU
function tambahKeranjang(id, nama, harga, stok) {

    if (!user) {
        tampilRFID();
        return;
    }

    if (stok <= 0) {
        tampilPesan("Stok menu habis!");
        return;
    }

    let item = keranjang.find(i => i.id == id);

    if (item) {

        if (item.qty >= stok) {
            tampilPesan("Stok menu tidak mencukupi!");
            return;
        }

        item.qty++;

    } else {

        keranjang.push({
            id: id,
            nama: nama,
            harga: Number(harga),
            qty: 1,
            stok: Number(stok)
        });

    }

    tampilKeranjang();
}

// MENGHAPUS MENU
function hapusItem(id) {

    keranjang = keranjang.filter(item => item.id != id);

    tampilKeranjang();
}

// MENAMPILKAN KERANJANG
function tampilKeranjang() {

    const container = document.getElementById("keranjang");

    container.innerHTML = "";

    if (keranjang.length === 0) {

        container.innerHTML =
            '<div class="cart-empty">Belum ada item dipilih.</div>';

    } else {

        keranjang.forEach(item => {

            const div = document.createElement("div");
            div.className = "cart-item";

            const info = document.createElement("div");

            const nama = document.createElement("strong");
            nama.textContent = item.nama;

            const detail = document.createElement("p");
            detail.textContent =
                item.qty + " x " + formatRupiah(item.harga);

            info.appendChild(nama);
            info.appendChild(detail);

            const aksi = document.createElement("div");

            const harga = document.createElement("strong");
            harga.textContent =
                formatRupiah(item.harga * item.qty);

            const tombol = document.createElement("button");
            tombol.textContent = "Hapus";
            tombol.onclick = () => hapusItem(item.id);

            aksi.appendChild(harga);
            aksi.appendChild(document.createElement("br"));
            aksi.appendChild(tombol);

            div.appendChild(info);
            div.appendChild(aksi);

            container.appendChild(div);

        });

    }

    document.getElementById("total").innerText =
        formatRupiah(hitungTotal());

    document.getElementById("btnBayar").disabled =
        keranjang.length === 0 || user === null || sedangBayar;
}

// HITUNG TOTAL
function hitungTotal() {

    return keranjang.reduce(
        (total, item) => total + (item.harga * item.qty),
        0
    );

}

// KONFIRMASI PEMBAYARAN
function konfirmasiBayar() {

    if (!user || keranjang.length === 0) return;

    const total = hitungTotal();

    if (total > Number(user.saldo)) {
        tampilPesan("Saldo pelanggan tidak mencukupi!");
        return;
    }

    document.getElementById("teksKonfirmasi").innerText =
        "Apakah Anda yakin ingin membayar sebesar " +
        formatRupiah(total) + " atas nama " + user.nama + "?";

    document.getElementById("modalKonfirmasi").style.display = "flex";
}

function tutupKonfirmasi() {
    document.getElementById("modalKonfirmasi").style.display = "none";
}

// PROSES PEMBAYARAN
async function bayar() {

    if (sedangBayar || !user || keranjang.length === 0) return;

    const total = hitungTotal();

    if (total > Number(user.saldo)) {
        tutupKonfirmasi();
        tampilPesan("Saldo tidak mencukupi!");
        return;
    }

    sedangBayar = true;

    const tombol = document.getElementById("btnKonfirmasi");
    tombol.disabled = true;
    tombol.innerText = "Memproses...";

    try {

        const itemsDibayar = keranjang.map(item => ({...item}));

        const response = await fetch("proses_transaksi.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id_user: user.id_akun,
                total_bayar: total,
                items: itemsDibayar
            })
        });

        const data = await bacaRespons(response);

        if (data.status !== "success") {
            throw new Error(data.message || "Transaksi gagal!");
        }

        // UPDATE SALDO
        user.saldo = data.saldo_akhir;

        document.getElementById("saldo_user").innerText =
            formatRupiah(user.saldo);

        // UPDATE STOK MENU DI TAMPILAN
        itemsDibayar.forEach(item => {

            const tombolMenu = document.querySelectorAll(".menu-card button");

            tombolMenu.forEach(btn => {

                if (btn.getAttribute("onclick") &&
                    btn.getAttribute("onclick").includes(String(item.id) + ",")) {

                    const stokElement = btn.parentElement.querySelectorAll("p")[1];

                    if (stokElement) {
                        const stokBaru = Math.max(0, item.stok - item.qty);
                        stokElement.textContent = "Stok: " + stokBaru;

                        btn.disabled = stokBaru <= 0;

                        btn.setAttribute(
                            "onclick",
                            btn.getAttribute("onclick").replace(
                                /,\s*\d+\s*\)/,
                                ", " + stokBaru + ")"
                            )
                        );
                    }

                }

            });

        });

        // KOSONGKAN KERANJANG
        keranjang = [];

        tampilKeranjang();

        tutupKonfirmasi();

        // TAMPILKAN POPUP BERHASIL
        document.getElementById("detailBerhasil").innerText =
            "Nama: " + user.nama +
            "\nTotal pembayaran: " + formatRupiah(total) +
            "\nSaldo tersisa: " + formatRupiah(user.saldo);

        document.getElementById("modalBerhasil").style.display = "flex";

    } catch (error) {

        console.error(error);

        tutupKonfirmasi();
        tampilPesan(error.message);

    } finally {

        sedangBayar = false;

        tombol.disabled = false;
        tombol.innerText = "Ya, Bayar";

        tampilKeranjang();

    }
}

// TUTUP POPUP BERHASIL
function tutupBerhasil() {

    document.getElementById("modalBerhasil").style.display = "none";

}

// MENAMPILKAN RIWAYAT TRANSAKSI
async function tampilRiwayat() {

    if (!user) {
        tampilPesan("Silakan tap kartu pelanggan terlebih dahulu!");
        return;
    }

    const modal = document.getElementById("modalRiwayat");
    const container = document.getElementById("isiRiwayat");

    modal.style.display = "flex";

    container.innerHTML = "<p>Memuat riwayat transaksi...</p>";

    try {

        const response = await fetch("riwayat_transaksi.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "id_user=" + encodeURIComponent(user.id_akun)
        });

        const data = await bacaRespons(response);

        if (data.status !== "success") {
            throw new Error(data.message || "Gagal mengambil riwayat.");
        }

        container.innerHTML = "";

        if (!data.transaksi || data.transaksi.length === 0) {

            container.innerHTML =
                '<p style="text-align:center;padding:25px;color:#777;">' +
                'Belum ada transaksi hari ini.</p>';

            return;
        }

        data.transaksi.forEach(item => {

            const batal = item.keterangan &&
                item.keterangan.toLowerCase().includes("dibatalkan");

            const card = document.createElement("div");
            card.className = "riwayat-item";

            const header = document.createElement("div");
            header.className = "riwayat-header";

            const nama = document.createElement("strong");
            nama.textContent =
                "#" + item.id_transaksi + " - " + user.nama;

            const status = document.createElement("span");
            status.className = batal ? "status batal" : "status";
            status.textContent = batal ? "Dibatalkan" : "Berhasil";

            header.appendChild(nama);
            header.appendChild(status);

            const waktu = document.createElement("p");
            waktu.textContent = item.waktu_transaksi || "-";

            const menu = document.createElement("p");
            menu.textContent =
                (item.nama_menu || "-") +
                " (" + item.qty + " x " +
                formatRupiah(item.harga_satuan) + ")";

            const total = document.createElement("strong");
            total.style.display = "block";
            total.style.marginTop = "10px";
            total.textContent =
                "Total: " + formatRupiah(item.total_bayar);

            card.appendChild(header);
            card.appendChild(waktu);
            card.appendChild(menu);
            card.appendChild(total);

            container.appendChild(card);

        });

    } catch (error) {

        console.error(error);

        container.innerHTML = "";

        const pesan = document.createElement("p");
        pesan.textContent = error.message;
        container.appendChild(pesan);

    }
}

// TUTUP RIWAYAT
function tutupRiwayat() {
    document.getElementById("modalRiwayat").style.display = "none";
}

// PESAN POPUP
function tampilPesan(pesan) {

    // Menggunakan popup halaman sendiri, bukan alert browser.
    const modal = document.createElement("div");
    modal.className = "modal";
    modal.style.display = "flex";

    const content = document.createElement("div");
    content.className = "modal-content";

    const icon = document.createElement("div");
    icon.className = "modal-icon";
    icon.textContent = "ⓘ";

    const judul = document.createElement("h2");
    judul.textContent = "Informasi";

    const teks = document.createElement("p");
    teks.textContent = pesan;

    const tombol = document.createElement("button");
    tombol.className = "btn-modal btn-primary";
    tombol.style.width = "100%";
    tombol.textContent = "OK";

    tombol.onclick = () => modal.remove();

    content.appendChild(icon);
    content.appendChild(judul);
    content.appendChild(teks);
    content.appendChild(tombol);

    modal.appendChild(content);

    document.body.appendChild(modal);
}

// HALAMAN PERTAMA DIBUKA
window.addEventListener("load", function() {

    user = null;
    keranjang = [];

    document.getElementById("dashboardTransaksi")
        .classList.remove("active");

    document.getElementById("modalRFID").style.display = "flex";

    tampilKeranjang();

    setTimeout(() => {
        document.getElementById("uid_kartu").focus();
    }, 300);

});

</script>

</body>
</html>
