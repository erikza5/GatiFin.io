<?php
/**
 * Router untuk PHP built-in server di Railway.
 * Struktur: file PHP langsung di root repo (tanpa folder GATIFIN).
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$docroot = __DIR__;
$file = $docroot . $uri;

// Serve static files langsung (CSS, JS, gambar, dll)
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

// Jika direktori, cari index.php
if (is_dir($file)) {
    $index = rtrim($file, '/') . '/index.php';
    if (file_exists($index)) {
        chdir(dirname($index));
        require $index;
        return true;
    }
}

// Semua request → index.php di root
$target = $docroot . '/index.php';
if (file_exists($target)) {
    chdir($docroot);
    require $target;
} else {
    http_response_code(404);
    echo "<h2>404 - File tidak ditemukan</h2><p>Path: $uri</p>";
}
