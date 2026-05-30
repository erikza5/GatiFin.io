<?php
/**
 * GATIFIN - Konfigurasi Koneksi Database Engine
 * Support environment: Lokal (XAMPP) & Railway (Cloud)
 */

// Konfigurasi Parameter Server Database
// Railway otomatis mengisi env vars berikut saat MySQL service ditambahkan
$db_host = getenv('MYSQLHOST')     ?: 'localhost';
$db_user = getenv('MYSQLUSER')     ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('MYSQLDATABASE') ?: 'gatifin';
$db_port = (int)(getenv('MYSQLPORT') ?: 3060);

// Mengaktifkan Pelaporan Eror internal MySQLi demi keamanan data
mysqli_report(MYSQLI_REPORT_OFF);

// Melakukan inisialisasi koneksi ke server MySQL
$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

// Memeriksa status keberhasilan jembatan koneksi
if (!$koneksi) {
    die("<div style='font-family:sans-serif; padding:20px; background:#fff5f5; color:#c53030; border-left:5px solid #dc3545; margin:20px; border-radius:4px;'>
            <h4 style='margin-top:0;'>GATIFIN System Error: Koneksi Gagal</h4>
            <p style='margin-bottom:0; font-size:14px;'>Sistem gagal terhubung ke database <strong>$db_name</strong>. Silakan periksa kembali konfigurasi server Anda.</p>
            <small style='color:#742a2a;'>Detail Eror: " . mysqli_connect_error() . "</small>
         </div>");
}

// Menyetel standar karakter encoding ke UTF-8 untuk mendukung simbol mata uang & teks global
mysqli_set_charset($koneksi, "utf8mb4");

// Mengatur zona waktu default sistem agar sinkron dengan waktu input keuangan lokal (WIB)
date_default_timezone_set('Asia/Jakarta');
?>
