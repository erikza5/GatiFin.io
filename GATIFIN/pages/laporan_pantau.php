<?php
// pages/laporan_pantau.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['user_id'])) { die("Silakan login terlebih dahulu."); }

$id_orang_tua = $_SESSION['user_id'];

// 1. Jika user ingin kembali ke mode pribadi (reset session)
if (isset($_GET['view']) && $_GET['view'] == 'pribadi') {
    unset($_SESSION['target_user_id']);
}

// 2. Jika ada ID di URL (dari profil_pantau), simpan ke session agar persisten
if (isset($_GET['id'])) {
    $_SESSION['target_user_id'] = (int)$_GET['id'];
}

// 3. VALIDASI PROTEKSI OTOMATIS: Cek apakah target_user_id masih terikat dengan orang tua ini di database
if (isset($_SESSION['target_user_id'])) {
    $target_check = $_SESSION['target_user_id'];
    $cek_relasi = mysqli_query($koneksi, "SELECT id FROM users WHERE id = '$target_check' AND created_by = '$id_orang_tua'");
    
    // Jika data anak sudah dihapus (tidak ada relasi lagi), hancurkan session target secara otomatis
    if (mysqli_num_rows($cek_relasi) == 0) {
        unset($_SESSION['target_user_id']);
    }
}

// 4. Konfigurasi Target Akhir Pengambilan Data
$is_mode_pantau = isset($_SESSION['target_user_id']);

// Jika orang tua mencoba mengakses ID yang sudah dihapus, potong eksekusi dan tampilkan pesan kosong yang aman
if ($is_mode_pantau === false && isset($_GET['id'])) {
    echo "
    <div class='container-fluid py-4'>
        <div class='alert alert-warning text-center border-0 shadow-sm mx-auto my-4' style='max-width: 700px; border-radius: 12px;'>
            <div class='py-3'>
                <i class='fas fa-user-slash text-warning mb-3' style='font-size: 2.5rem;'></i>
                <h5 class='fw-bold text-dark'>Akses Data Pemantauan Terputus</h5>
                <p class='text-muted small mb-0'>Akun ini sudah dihapus dari daftar pantauan Anda atau data tidak ditemukan. <br>Silakan daftarkan kembali akun anak melalui menu <strong>Profil Pantau</strong>.</p>
            </div>
        </div>
    </div>";
    exit;
}

// Jika tidak berada dalam mode pantau yang sah, tampilkan instruksi pilih akun anak
if (!$is_mode_pantau) {
    echo "
    <div class='container-fluid py-4'>
        <div class='alert alert-info text-center border-0 shadow-sm mx-auto my-4' style='max-width: 700px; border-radius: 12px;'>
            <div class='py-3'>
                <i class='fas fa-folder-open text-info mb-3' style='font-size: 2.5rem;'></i>
                <h5 class='fw-bold text-dark'>Belum Ada Akun Anak yang Dipilih</h5>
                <p class='text-muted small mb-3'>Silakan pilih salah satu akun aktif di halaman profil pantau terlebih dahulu untuk meninjau laporan arus kas keuangan.</p>
                <a href='index.php?page=profil_pantau' class='btn btn-sm btn-primary px-3 fw-semibold' style='border-radius:8px;'>Buka Profil Pantau</a>
            </div>
        </div>
    </div>";
    exit;
}

// ID User Target yang datanya akan ditarik ke dalam grafik laporan (Dipastikan memakai session)
$id_user_target = $_SESSION['target_user_id'];

// --- PROSES AMBIL DATA USER ANAK UNTUK DESKRIPSI HEDER ---
$q_anak = mysqli_query($koneksi, "SELECT `nama lengkap`, nama, username FROM users WHERE id = '$id_user_target'");
$nama_anak_aktif = "Anak";
if ($q_anak && mysqli_num_rows($q_anak) > 0) {
    $d_anak = mysqli_fetch_assoc($q_anak);
    $nama_anak_aktif = !empty($d_anak['nama lengkap']) ? $d_anak['nama lengkap'] : ($d_anak['nama'] ?? 'Anak');
}

// FILTER TANGGAL
$bulan_pilihan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun_pilihan = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$nama_bulan = [
    1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April", 5 => "Mei", 6 => "Juni",
    7 => "Juli", 8 => "Agustus", 9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
];

// ==========================================================
// DATA ENGINE QUERY (MENGGUNAKAN KOLOM ASLI DATABASE: user_id)
// ==========================================================

// 1. Ambil Total Ringkasan Saldo Pemasukan Bulanan
$q_masuk = mysqli_query($koneksi, "SELECT IFNULL(SUM(t.jumlah), 0) as total FROM transaksi t JOIN kategori k ON t.id_kategori = k.id_kategori WHERE t.user_id = '$id_user_target' AND k.jenis = 'Pemasukan' AND MONTH(t.tanggal) = '$bulan_pilihan' AND YEAR(t.tanggal) = '$tahun_pilihan'");
$res_masuk = mysqli_fetch_assoc($q_masuk);
$total_masuk = isset($res_masuk['total']) ? (float)$res_masuk['total'] : 0.0;

// 2. Ambil Total Ringkasan Saldo Pengeluaran Bulanan
$q_keluar = mysqli_query($koneksi, "SELECT IFNULL(SUM(t.jumlah), 0) as total FROM transaksi t JOIN kategori k ON t.id_kategori = k.id_kategori WHERE t.user_id = '$id_user_target' AND k.jenis = 'Pengeluaran' AND MONTH(t.tanggal) = '$bulan_pilihan' AND YEAR(t.tanggal) = '$tahun_pilihan'");
$res_keluar = mysqli_fetch_assoc($q_keluar);
$total_keluar = isset($res_keluar['total']) ? (float)$res_keluar['total'] : 0.0;

// Hitung Bersih Sisa Saldo
$selisih_saldo = $total_masuk - $total_keluar;

// 3. Ambil Pembagian Data Mingguan (Arus Kas Mingguan)
$pemasukan_mingguan = [0, 0, 0, 0];
$pengeluaran_mingguan = [0, 0, 0, 0];

$q_mingguan = mysqli_query($koneksi, "
    SELECT 
        k.jenis,
        FLOOR((DAY(t.tanggal)-1)/7) + 1 as minggu,
        SUM(t.jumlah) as total
    FROM transaksi t
    JOIN kategori k ON t.id_kategori = k.id_kategori
    WHERE t.user_id = '$id_user_target' AND MONTH(t.tanggal) = '$bulan_pilihan' AND YEAR(t.tanggal) = '$tahun_pilihan'
    GROUP BY k.jenis, minggu
");

if ($q_mingguan) {
    while ($r = mysqli_fetch_assoc($q_mingguan)) {
        $idx = ($r['minggu'] > 4) ? 3 : (int)$r['minggu'] - 1;
        if ($idx >= 0 && $idx < 4) {
            if ($r['jenis'] == 'Pemasukan') $pemasukan_mingguan[$idx] = (int)$r['total'];
            if ($r['jenis'] == 'Pengeluaran') $pengeluaran_mingguan[$idx] = (int)$r['total'];
        }
    }
}

$json_pemasukan_mingguan = json_encode($pemasukan_mingguan);
$json_pengeluaran_mingguan = json_encode($pengeluaran_mingguan);

// 4. Distribusi Data Kategori Pemasukan (Doughnut Chart)
$labels_masuk = []; $data_masuk = [];
$q_cat_masuk = mysqli_query($koneksi, "SELECT k.nama_kategori, SUM(t.jumlah) as total FROM transaksi t JOIN kategori k ON t.id_kategori = k.id_kategori WHERE t.user_id = '$id_user_target' AND k.jenis = 'Pemasukan' AND MONTH(t.tanggal) = '$bulan_pilihan' AND YEAR(t.tanggal) = '$tahun_pilihan' GROUP BY k.nama_kategori");
if ($q_cat_masuk) {
    while($row = mysqli_fetch_assoc($q_cat_masuk)){
        $labels_masuk[] = $row['nama_kategori'];
        $data_masuk[] = (int)$row['total'];
    }
}

// 5. Distribusi Data Kategori Pengeluaran (Doughnut Chart)
$labels_keluar = []; $data_keluar = [];
$q_cat_keluar = mysqli_query($koneksi, "SELECT k.nama_kategori, SUM(t.jumlah) as total FROM transaksi t JOIN kategori k ON t.id_kategori = k.id_kategori WHERE t.user_id = '$id_user_target' AND k.jenis = 'Pengeluaran' AND MONTH(t.tanggal) = '$bulan_pilihan' AND YEAR(t.tanggal) = '$tahun_pilihan' GROUP BY k.nama_kategori");
if ($q_cat_keluar) {
    while($row = mysqli_fetch_assoc($q_cat_keluar)){
        $labels_keluar[] = $row['nama_kategori'];
        $data_keluar[] = (int)$row['total'];
    }
}
?>

<style>
    .card-hover-zoom {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        border-radius: 12px;
        border: none;
        background: #ffffff;
    }
    .card-hover-zoom:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.06) !important;
    }
    .text-brand-color { color: #006D5B; }
    .bg-brand-soft { background-color: rgba(0, 109, 91, 0.06); }
</style>

<div class="container-fluid py-4" style="max-width: 1400px;">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Laporan Keuangan Terpantau</h3>
            <p class="text-muted mb-0">Menampilkan mutasi dan visualisasi grafik milik akun: <span class="badge bg-brand-soft text-brand-color fw-bold px-2 py-1.5 fs-6">@<?= htmlspecialchars($nama_anak_aktif) ?></span></p>
        </div>
        
        <form method="GET" action="index.php" class="d-flex gap-2">
            <input type="hidden" name="page" value="laporan_pantau">
            <select name="bulan" class="form-select form-select-sm fw-semibold bg-white border shadow-sm" style="border-radius: 8px;">
                <?php foreach($nama_bulan as $m_num => $m_name): ?>
                    <option value="<?= $m_num ?>" <?= $bulan_pilihan === $m_num ? 'selected' : '' ?>><?= $m_name ?></option>
                <?php endforeach; ?>
            </select>
            <select name="tahun" class="form-select form-select-sm fw-semibold bg-white border shadow-sm" style="border-radius: 8px;">
                <?php for($t = date('Y')-2; $t <= date('Y')+1; $t++): ?>
                    <option value="<?= $t ?>" <?= $tahun_pilihan === $t ? 'selected' : '' ?>><?= $t ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary px-3 shadow-sm" style="border-radius: 8px;"><i class="fas fa-filter me-1"></i> Saring</button>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card card-hover-zoom p-4 shadow-sm border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold mb-1">TOTAL PEMASUKAN BULANAN</p>
                        <h4 class="fw-bold text-success mb-0">Rp <?= number_format((float)$total_masuk, 0, ',', '.') ?></h4>
                    </div>
                    <div class="p-3 bg-light rounded-circle text-success"><i class="fas fa-arrow-down-long fs-4"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-hover-zoom p-4 shadow-sm border-start border-4 border-danger">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold mb-1">TOTAL PENGELUARAN BULANAN</p>
                        <h4 class="fw-bold text-danger mb-0">Rp <?= number_format((float)$total_keluar, 0, ',', '.') ?></h4>
                    </div>
                    <div class="p-3 bg-light rounded-circle text-danger"><i class="fas fa-arrow-up-long fs-4"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-hover-zoom p-4 shadow-sm border-start border-4 <?= $selisih_saldo >= 0 ? 'border-primary' : 'border-warning' ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small fw-bold mb-1">SISA SALDO BERSIH</p>
                        <h4 class="fw-bold <?= $selisih_saldo >= 0 ? 'text-primary' : 'text-warning' ?> mb-0">Rp <?= number_format((float)$selisih_saldo, 0, ',', '.') ?></h4>
                    </div>
                    <div class="p-3 bg-light rounded-circle <?= $selisih_saldo >= 0 ? 'text-primary' : 'text-warning' ?>"><i class="fas fa-wallet fs-4"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card card-hover-zoom p-4 shadow-sm">
                <h5 class="fw-bold mb-3"><i class="fas fa-chart-bar text-brand-color me-2"></i>Arus Kas Mingguan (<?= $nama_bulan[$bulan_pilihan] ?>)</h5>
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="chartArusKas"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card card-hover-zoom p-4 shadow-sm">
                <h5 class="fw-bold mb-3 text-success"><i class="fas fa-circle-arrow-down me-2"></i>Proporsi Pemasukan</h5>
                <div style="position: relative; height: 260px; width: 100%;">
                    <?php if(!empty($labels_masuk)): ?>
                        <canvas id="chartMasuk"></canvas>
                    <?php else: ?>
                        <div class="text-center text-muted p-5 small">Tidak ada data rincian pemasukan bulan ini.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card card-hover-zoom p-4 shadow-sm">
                <h5 class="fw-bold mb-3 text-danger"><i class="fas fa-circle-arrow-up me-2"></i>Proporsi Pengeluaran</h5>
                <div style="position: relative; height: 260px; width: 100%;">
                    <?php if(!empty($labels_keluar)): ?>
                        <canvas id="chartKeluar"></canvas>
                    <?php else: ?>
                        <div class="text-center text-muted p-5 small">Tidak ada data rincian pengeluaran bulan ini.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // 1. Grafik Batang Arus Kas Mingguan
    const ctxArus = document.getElementById('chartArusKas').getContext('2d');
    new Chart(ctxArus, {
        type: 'bar',
        data: { 
            labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'], 
            datasets: [
                { label: 'Pemasukan', data: <?= $json_pemasukan_mingguan ?>, backgroundColor: '#006D5B', borderRadius: 5 },
                { label: 'Pengeluaran', data: <?= $json_pengeluaran_mingguan ?>, backgroundColor: '#dc3545', borderRadius: 5 }
            ]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    // 2. Grafik Doughnut Kategori Pemasukan
    <?php if(!empty($labels_masuk)): ?>
    const ctxMasuk = document.getElementById('chartMasuk').getContext('2d');
    new Chart(ctxMasuk, { 
        type: 'doughnut', 
        data: {
            labels: <?= json_encode($labels_masuk) ?>,
            datasets: [{
                data: <?= json_encode($data_masuk) ?>,
                backgroundColor: ['#28a745', '#20c997', '#17a2b8', '#007bff', '#ffc107', '#fd7e14']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
    <?php endif; ?>

    // 3. Grafik Doughnut Kategori Pengeluaran
    <?php if(!empty($labels_keluar)): ?>
    const ctxKeluar = document.getElementById('chartKeluar').getContext('2d');
    new Chart(ctxKeluar, { 
        type: 'doughnut', 
        data: {
            labels: <?= json_encode($labels_keluar) ?>,
            datasets: [{
                data: <?= json_encode($data_keluar) ?>,
                backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#6f42c1', '#e83e8c', '#6c757d']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
    <?php endif; ?>
});
</script>