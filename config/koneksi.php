<?php
/**
 * GATIFIN - Koneksi Database Railway MySQL
 */

// Ambil variabel dari Railway
$db_host = $_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST');
$db_user = $_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER');
$db_pass = $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD');
$db_name = $_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE');
$db_port = $_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT');

// Aktifkan pelaporan error MySQLi
mysqli_report(MYSQLI_REPORT_OFF);

// Koneksi ke database Railway
$koneksi = mysqli_connect(
    $db_host,
    $db_user,
    $db_pass,
    $db_name,
    (int)$db_port
);

// Cek koneksi
if (!$koneksi) {
    die("
    <div style='font-family:sans-serif;padding:20px;background:#fff5f5;color:#c53030;border-left:5px solid #dc3545;margin:20px;border-radius:4px;'>
        <h4 style='margin-top:0;'>GATIFIN System Error: Database Connection Failed</h4>
        <p style='margin-bottom:0;font-size:14px;'>
            Tidak dapat terhubung ke database Railway.
        </p>
        <small>
            Error: " . mysqli_connect_error() . "
        </small>
    </div>
    ");
}

// Charset UTF8
mysqli_set_charset($koneksi, "utf8mb4");

// Timezone Indonesia
date_default_timezone_set('Asia/Jakarta');
?>
