<?php
session_start();
require_once "../config/koneksi.php"; // Sesuaikan path menuju koneksi

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    // Menggunakan password_hash agar aman (sesuai format database Anda)
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $role = 'orang_tua'; // Role ditentukan otomatis

    // Query untuk memasukkan data
    $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Akun orang tua berhasil ditambahkan!'); window.location='../index.php?page=pengaturan_akun';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan akun.'); window.location='../index.php?page=pengaturan_akun';</script>";
    }
}
?>