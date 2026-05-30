<?php
/**
 * GATIFIN - Konfigurasi Koneksi Database Engine
 * Support: Lokal (XAMPP) & Railway (Cloud)
 */

// Cek apakah ada DATABASE_URL (Railway public URL format)
$database_url = getenv('DATABASE_URL') ?: 'mysql://root:HTESIDmSfHZifwXqKyDXdUnHSCokeUZm@centerbeam.proxy.rlwy.net:13066/gatifin_db';

if ($database_url) {
    $parsed = parse_url($database_url);
    $db_host = $parsed['host'];
    $db_user = $parsed['user'];
    $db_pass = $parsed['pass'];
    $db_name = ltrim($parsed['path'], '/');
    $db_port = $parsed['port'] ?? 3306;
} else {
    // Fallback ke env vars individual atau lokal
    $db_host = getenv('MYSQLHOST')     ?: 'localhost';
    $db_user = getenv('MYSQLUSER')     ?: 'root';
    $db_pass = getenv('MYSQLPASSWORD') ?: '';
    $db_name = getenv('MYSQLDATABASE') ?: 'Gatfin';
    $db_port = (int)(getenv('MYSQLPORT') ?: 3306);
}

mysqli_report(MYSQLI_REPORT_OFF);

$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$koneksi) {
    die("
    <div style='font-family:sans-serif;padding:30px;max-width:600px;margin:40px auto;background:#fff5f5;
                color:#c53030;border-left:5px solid #dc3545;border-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,.1)'>
        <h3 style='margin-top:0'>⚠️ GATIFIN: Koneksi Database Gagal</h3>
        <p><strong>Error:</strong> " . mysqli_connect_error() . "</p>
        <small style='color:#742a2a'>Host: $db_host | Port: $db_port | DB: $db_name | User: $db_user</small>
    </div>");
}

mysqli_set_charset($koneksi, "utf8mb4");
date_default_timezone_set('Asia/Jakarta');
