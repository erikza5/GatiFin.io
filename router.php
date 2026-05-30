<?php
/**
 * Router untuk PHP built-in server di Railway.
 * Struktur: folder GATIFIN di dalam repo.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$docroot = __DIR__ . '/GATIFIN';

// Set working directory
chdir($docroot);

$file = $docroot . $uri;

// Serve static files langsung (CSS, JS, gambar, dll)
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

// Jika direktori, cari index.php
if (is_dir($file)) {
    $index = rtrim($file, '/') . '/index.php';
    if (file_exists($index)) {
        require $index;
        return true;
    }
}

// Semua request → index.php di root GATIFIN
$target = $docroot . '/index.php';
if (file_exists($target)) {
    require $target;
} else {
    http_response_code(404);
    echo "<h2>404 - File tidak ditemukan</h2><p>Path: $uri</p>";
}
