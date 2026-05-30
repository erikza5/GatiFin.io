c:\xampp\htdocs\GATIFIN\login.php<?php
session_start();      // Memulai sesi yang sedang aktif
session_unset();      // Menghapus semua variabel sesi
session_destroy();    // Menghancurkan sesi
header("Location: login.php"); // Mengalihkan ke halaman login
exit;
?>