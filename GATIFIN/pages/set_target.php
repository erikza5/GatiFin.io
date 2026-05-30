<?php
// pages/set_target.php
session_start();
include_once __DIR__ . '/../config/koneksi.php';

if (isset($_GET['id'])) {
    $target_id = (int)$_GET['id'];
    $pemantau_id = $_SESSION['user_id'];

    // Validasi: Pastikan akun tersebut memang milik/dipantau oleh orang tua ini
    $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE id = '$target_id' AND created_by = '$pemantau_id'");
    
    if (mysqli_num_rows($cek) > 0) {
        // Jika valid, set session
        $_SESSION['target_user_id'] = $target_id;
        echo "<script>alert('Berhasil memilih akun untuk dipantau!'); window.location='../index.php?page=dashboard';</script>";
    } else {
        echo "<script>alert('Gagal memilih akun!'); window.location='../index.php?page=profil_pantau';</script>";
    }
}
?>