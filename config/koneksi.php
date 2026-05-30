<?php
/**
 * GATIFIN - Konfigurasi Koneksi Database Engine
 * Menggunakan Driver Ekstensi MySQLi (Object-Oriented/Procedural Mix)
 */

// Konfigurasi Parameter Server Database
$db_host = "localhost";     // Alamat server basis data (default: localhost)
$db_user = "root";          // Username hak akses database (default: root)
$db_pass = "";              // Password hak akses database (default: kosong)
$db_name = "Gatfin";        // Nama database sistem sesuai instruksi awal

// Mengaktifkan Pelaporan Eror internal MySQLi demi keamanan data
mysqli_report(MYSQLI_REPORT_OFF);

// Melakukan inisialisasi koneksi ke server MySQL
$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Memeriksa status keberhasilan jembatan koneksi
if (!$koneksi) {
    // Jika koneksi gagal, hentikan skrip dan tampilkan pesan eror yang rapi
    die("<div style='font-family:sans-serif; padding:20px; background:#fff5f5; color:#c53030; border-left:5px solid #dc3545; margin:20px; border-radius:4px;'>
            <h4 style='margin-top:0;'>GATIFIN System Error: Koneksi Gagal</h4>
            <p style='margin-bottom:0; font-size:14px;'>Sistem gagal terhubung ke database <strong>$db_name</strong>. Silakan periksa kembali konfigurasi server lokal Anda.</p>
            <small style='color:#742a2a;'>Detail Eror: " . mysqli_connect_error() . "</small>
         </div>");
}

// Menyetel standar karakter encoding ke UTF-8 untuk mendukung simbol mata uang & teks global
mysqli_set_charset($koneksi, "utf8mb4");

// Mengatur zona waktu default sistem agar sinkron dengan waktu input keuangan lokal (WIB)
date_default_timezone_set('Asia/Jakarta');
?>