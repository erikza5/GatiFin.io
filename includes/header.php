<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id_user_login = $_SESSION['user_id'] ?? 0;
$user_nama = "Pengguna Gatifin";
$user_foto = "assets/img/user.png";

if (isset($koneksi)) {
    $query = mysqli_query($koneksi, "SELECT `nama lengkap`, foto FROM users WHERE id = '$id_user_login'");
    if ($data = mysqli_fetch_assoc($query)) {
        $user_nama = $data['nama lengkap'];
        if (!empty($data['foto']) && file_exists("assets/img/" . $data['foto'])) {
            $user_foto = "assets/img/" . $data['foto'];
        }
    }
}

// Determine current page title
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$page_titles = [
    'dashboard'       => ['Dashboard Keuangan', 'Pantau kesehatan finansial Anda'],
    'profil'          => ['Profil Saya', 'Kelola data akun dan informasi pribadi'],
    'laporan'         => ['Laporan Keuangan', 'Ringkasan pemasukan & pengeluaran'],
    'analisis'        => ['Analisis Finansial', 'Wawasan mendalam performa keuangan'],
    'master'          => ['Data Master', 'Kelola dompet dan kategori'],
    'transaksi'       => ['Transaksi', 'Riwayat semua transaksi keuangan'],
    'transaksi_edit'  => ['Edit Transaksi', 'Perbarui detail transaksi'],
    'pengaturan'      => ['Pengaturan', 'Konfigurasi akun dan aplikasi'],
    'pengaturan_akun' => ['Pengaturan Akun', 'Ubah password dan keamanan akun'],
    'profil_pantau'   => ['Profil Pantau', 'Data profil yang dipantau'],
    'laporan_pantau'  => ['Laporan Pantau', 'Laporan keuangan yang dipantau'],
    'analisis_pantau' => ['Analisis Pantau', 'Analisis finansial yang dipantau'],
    'set_target'      => ['Target Keuangan', 'Tetapkan target dan tujuan finansial'],
];
$current_title    = $page_titles[$page][0] ?? 'GATIFIN';
$current_subtitle = $page_titles[$page][1] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script id="gatifin_theme_boot">
        (function () {
            try {
                var theme = localStorage.getItem('gatifin_theme') || 'light';
                document.documentElement.dataset.theme = theme;
                if (theme === 'dark') document.documentElement.classList.add('dark-mode-active');
            } catch (e) {}
        })();
    </script>
    <title>GATIFIN â€“ <?= htmlspecialchars($current_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="wrapper" class="d-flex">

    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
