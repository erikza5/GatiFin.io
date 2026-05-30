<?php
session_start();
require_once "config/koneksi.php";
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

include "includes/header.php";
include "includes/sidebar.php";
?>

<div class="content-body">
    <?php
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    $is_pantau_mode = (isset($_GET['view']) && $_GET['view'] === 'pantau');
    $halaman_hanya_pengguna = ['master', 'transaksi'];

    if (in_array($page, $halaman_hanya_pengguna) && isset($_SESSION['role']) && $_SESSION['role'] === 'orang_tua' && $is_pantau_mode) {
        echo '<div class="gf-alert danger"><i class="fas fa-ban"></i> Akses Ditolak. Halaman ini tidak tersedia di mode pantau.</div>';
        include "pages/dashboard.php";
    } else {
        $file_path = "pages/" . $page . ".php";
        include file_exists($file_path) ? $file_path : "pages/dashboard.php";
    }
    ?>
</div>

<?php include "includes/footer.php"; ?>
