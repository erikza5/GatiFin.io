<?php
// 1. Hubungkan ke database dan file fungsi utilitas GATIFIN
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi: Pastikan pengguna sudah masuk ke dalam sistem
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['CONTENT_TYPE'] === 'application/json' || isset($_POST['action'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Sesi login Anda telah habis.']);
        exit;
    }
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// ========================================================
// STRATEGI 1: MENANGANI REQUEST DARI SCAN AI (AJAX JSON)
// ========================================================
// Memeriksa jika data dikirim melalui Fetch API berupa JSON mentah
$input_raw = file_get_contents('php://input');
$input_data = json_decode($input_raw, true);

if (($input_data && isset($input_data['action']) && $input_data['action'] === 'save_transaksi_ai') || 
    (isset($_POST['action']) && $_POST['action'] === 'save_transaksi_ai')) {
    
    header('Content-Type: application/json');
    
    // Ambil parameter baik dari JSON payload ataupun POST FormData fallback
    $toko       = mysqli_real_escape_string($koneksi, $input_data['toko'] ?? $_POST['toko']);
    $tanggal    = mysqli_real_escape_string($koneksi, $input_data['tanggal'] ?? $_POST['tanggal']);
    $jumlah     = (int)($input_data['nominal'] ?? $_POST['nominal'] ?? 0);
    $keterangan = "Belanja otomatis di " . $toko;

    // Cari sub-kategori pengeluaran pertama milik user sebagai fallback otomatis
    $q_kat = mysqli_query($koneksi, "SELECT id_kategori FROM kategori WHERE user_id = '$id_user' AND jenis = 'Pengeluaran' LIMIT 1");
    $d_kat = mysqli_fetch_assoc($q_kat);
    $id_kategori = isset($d_kat['id_kategori']) ? $d_kat['id_kategori'] : 1; 

    // Cari dompet/akun aktif pertama milik user sebagai tujuan pemotongan saldo otomatis
    $q_dompet = mysqli_query($koneksi, "SELECT id_dompet FROM dompet WHERE user_id = '$id_user' LIMIT 1");
    $d_dompet = mysqli_fetch_assoc($q_dompet);
    $id_dompet = isset($d_dompet['id_dompet']) ? $d_dompet['id_dompet'] : 1;

    // Set nilai ID Sub default (jika struktur tabel Anda mewajibkan relasi sub-kategori)
    $id_sub = 0; 

    // Generate Kode Transaksi Unik sesuai aturan penamaan internal GATIFIN
    $kode_transaksi = generateKodeTransaksi($id_user, $id_kategori, $id_sub, $id_dompet);

    // Eksekusi penyimpanan ke tabel transaksi
    $query = "INSERT INTO transaksi (kode_transaksi, user_id, id_kategori, id_sub, id_dompet, tanggal, jumlah, keterangan) 
              VALUES ('$kode_transaksi', '$id_user', '$id_kategori', '$id_sub', '$id_dompet', '$tanggal', '$jumlah', '$keterangan')";

    if (mysqli_query($koneksi, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Transaksi hasil scan nota berhasil dibukukan!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database: ' . mysqli_error($koneksi)]);
    }
    exit;
}

// ========================================================
// STRATEGI 2: MENANGANI REQUEST MANUAL FORM (POST STANDAR)
// ========================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data kiriman dari elemen input form
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $id_sub      = mysqli_real_escape_string($koneksi, $_POST['id_sub'] ?? 0);
    $id_dompet   = mysqli_real_escape_string($koneksi, $_POST['id_dompet']);
    $tanggal     = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $jumlah      = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $keterangan  = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    // Generate Kode Transaksi Unik
    $kode_transaksi = generateKodeTransaksi($id_user, $id_kategori, $id_sub, $id_dompet);

    // Masukkan ke dalam database pembukuan
    $query = "INSERT INTO transaksi (kode_transaksi, user_id, id_kategori, id_sub, id_dompet, tanggal, jumlah, keterangan) 
              VALUES ('$kode_transaksi', '$id_user', '$id_kategori', '$id_sub', '$id_dompet', '$tanggal', '$jumlah', '$keterangan')";

    if (mysqli_query($koneksi, $query)) {
        // Jika berhasil, arahkan kembali ke halaman utama transaksi dengan status sukses
        header("Location: ../index.php?page=transaksi&status=sukses");
        exit;
    } else {
        // Jika gagal, kembalikan dengan status parameter gagal
        header("Location: ../index.php?page=transaksi&status=gagal");
        exit;
    }
}
