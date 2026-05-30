<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    die("Silakan login terlebih dahulu.");
}

$mode = $_GET['view'] ?? 'pribadi';
$id_user_target = ($mode === 'pantau' && ($_SESSION['role'] ?? '') === 'orang_tua' && isset($_SESSION['target_user_id']))
    ? (int)$_SESSION['target_user_id']
    : (int)$_SESSION['user_id'];

$semua_periode = isset($_GET['periode']) && $_GET['periode'] === 'all';
$bulan_pilihan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun_pilihan = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$daftar_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$filter_tanggal_sql = $semua_periode ? "" : "AND MONTH(t.tanggal) = '$bulan_pilihan' AND YEAR(t.tanggal) = '$tahun_pilihan'";
$periode_label = $semua_periode ? 'Semua Periode' : ($daftar_bulan[$bulan_pilihan] . ' ' . $tahun_pilihan);

$total_pemasukan_bulan_ini = 0;
$total_pengeluaran_bulan_ini = 0;
$data_pemasukan_mingguan = [];
$data_pengeluaran_mingguan = [];
$label_tren = [];

if ($semua_periode) {
    $q_tren = "
        SELECT DATE_FORMAT(t.tanggal, '%Y-%m') AS periode, k.jenis, SUM(t.jumlah) AS total_jumlah
        FROM transaksi t
        JOIN kategori k ON t.id_kategori = k.id_kategori
        WHERE t.user_id = '$id_user_target'
        GROUP BY periode, k.jenis
        ORDER BY periode ASC
    ";
} else {
    $q_tren = "
        SELECT FLOOR((DAY(t.tanggal) - 1) / 7) + 1 AS periode, k.jenis, SUM(t.jumlah) AS total_jumlah
        FROM transaksi t
        JOIN kategori k ON t.id_kategori = k.id_kategori
        WHERE t.user_id = '$id_user_target'
        $filter_tanggal_sql
        GROUP BY periode, k.jenis
        ORDER BY periode ASC
    ";
}

$tren_map = [];
$res_tren = mysqli_query($koneksi, $q_tren);
if ($res_tren) {
    while ($row = mysqli_fetch_assoc($res_tren)) {
        $periode = (string)$row['periode'];
        if (!$semua_periode) {
            $periode_num = min(4, max(1, (int)$periode));
            $periode = 'Minggu ' . $periode_num;
        }
        if (!isset($tren_map[$periode])) {
            $tren_map[$periode] = ['Pemasukan' => 0, 'Pengeluaran' => 0];
        }
        $tren_map[$periode][$row['jenis']] = (int)$row['total_jumlah'];
        if ($row['jenis'] === 'Pemasukan') {
            $total_pemasukan_bulan_ini += (int)$row['total_jumlah'];
        } elseif ($row['jenis'] === 'Pengeluaran') {
            $total_pengeluaran_bulan_ini += (int)$row['total_jumlah'];
        }
    }
}

if (!$semua_periode) {
    for ($i = 1; $i <= 4; $i++) {
        $key = 'Minggu ' . $i;
        if (!isset($tren_map[$key])) {
            $tren_map[$key] = ['Pemasukan' => 0, 'Pengeluaran' => 0];
        }
    }
    ksort($tren_map);
}

foreach ($tren_map as $label => $nilai) {
    $label_tren[] = $label;
    $data_pemasukan_mingguan[] = $nilai['Pemasukan'];
    $data_pengeluaran_mingguan[] = $nilai['Pengeluaran'];
}

$saldo_bersih = $total_pemasukan_bulan_ini - $total_pengeluaran_bulan_ini;

function getDistribusi($koneksi, $id_user, $jenis, $filter_tanggal_sql)
{
    $labels = [];
    $dana = [];
    $query = "
        SELECT k.nama_kategori, SUM(t.jumlah) AS total
        FROM transaksi t
        JOIN kategori k ON t.id_kategori = k.id_kategori
        WHERE t.user_id = '$id_user'
          AND k.jenis = '$jenis'
          $filter_tanggal_sql
        GROUP BY k.id_kategori, k.nama_kategori
        ORDER BY total DESC
    ";
    $result = mysqli_query($koneksi, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = $row['nama_kategori'];
            $dana[] = (int)$row['total'];
        }
    }
    return empty($labels) ? [['Belum ada data'], [1]] : [$labels, $dana];
}

[$label_pengeluaran, $dana_pengeluaran] = getDistribusi($koneksi, $id_user_target, 'Pengeluaran', $filter_tanggal_sql);
[$label_pemasukan, $dana_pemasukan] = getDistribusi($koneksi, $id_user_target, 'Pemasukan', $filter_tanggal_sql);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.main-content-report{font-family:'Plus Jakarta Sans',sans-serif;background-color:#f8f9fa}
.custom-card{border:none;border-radius:16px;transition:.3s;background:#fff}
.custom-card:hover{transform:translateY(-4px);box-shadow:0 12px 20px rgba(0,0,0,.05)}
.btn-modern{background:#006D5B;color:#fff;border:none;border-radius:12px;padding:10px 24px;font-weight:600}
.btn-modern:hover{background:#005244;color:#fff}
.icon-shape{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center}
</style>

<div class="container-fluid px-4 py-3 main-content-report">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="fas fa-chart-line me-2"></i> Laporan Keuangan</h3>
            <p class="text-muted small mb-0">Analisis pemasukan dan pengeluaran keuangan. Terakhir diperbarui: <?= date('d M Y H:i') ?></p>
        </div>
        <div class="badge bg-light text-dark border rounded-pill p-2 px-3"><?= htmlspecialchars($periode_label) ?></div>
    </div>

    <div class="card custom-card p-4 shadow-sm mb-4">
        <form method="GET" action="index.php" class="row g-3 align-items-end">
            <input type="hidden" name="page" value="laporan">
            <input type="hidden" name="view" value="<?= htmlspecialchars($mode) ?>">
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="periodeAll" name="periode" value="all" <?= $semua_periode ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold small" for="periodeAll">Tampilkan semua periode transaksi</label>
                </div>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold small">Bulan</label>
                <select class="form-select" name="bulan">
                    <?php foreach($daftar_bulan as $angka => $nama): ?>
                        <option value="<?= $angka ?>" <?= ($angka == $bulan_pilihan) ? 'selected' : '' ?>><?= $nama ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Tahun</label>
                <select class="form-select" name="tahun">
                    <?php for($i = (int)date('Y') - 5; $i <= (int)date('Y') + 2; $i++): ?>
                        <option value="<?= $i ?>" <?= ($tahun_pilihan == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-modern">Filter</button>
            </div>
        </form>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="card custom-card p-3 shadow-sm"><span class="text-muted small">Total Pemasukan</span><h4 class="fw-bold text-success">Rp <?= number_format($total_pemasukan_bulan_ini,0,',','.') ?></h4></div></div>
        <div class="col-md-4"><div class="card custom-card p-3 shadow-sm"><span class="text-muted small">Total Pengeluaran</span><h4 class="fw-bold text-danger">Rp <?= number_format($total_pengeluaran_bulan_ini,0,',','.') ?></h4></div></div>
        <div class="col-md-4"><div class="card custom-card p-3 shadow-sm"><span class="text-muted small">Saldo Bersih</span><h4 class="fw-bold" style="color:<?= $saldo_bersih >= 0 ? '#006D5B' : '#dc3545' ?>">Rp <?= number_format($saldo_bersih,0,',','.') ?></h4></div></div>
    </div>

    <div class="card custom-card shadow-sm p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Perbandingan Arus Kas</h5>
            <span class="text-muted small"><?= $semua_periode ? 'Per Bulan' : 'Per Minggu' ?></span>
        </div>
        <div style="position:relative;height:320px;width:100%;"><canvas id="chartArusKas"></canvas></div>
    </div>

    <div class="row g-4">
        <div class="col-md-6"><div class="card custom-card shadow-sm p-4"><h5 class="fw-bold mb-3">Distribusi Pemasukan</h5><div style="height:280px"><canvas id="chartDonutPemasukan"></canvas></div></div></div>
        <div class="col-md-6"><div class="card custom-card shadow-sm p-4"><h5 class="fw-bold mb-3">Distribusi Pengeluaran</h5><div style="height:280px"><canvas id="chartDonutPengeluaran"></canvas></div></div></div>
    </div>
</div>

<script>
window.addEventListener("load", function(){
    new Chart(document.getElementById('chartArusKas'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($label_tren) ?>,
            datasets: [
                { label: 'Pemasukan', data: <?= json_encode($data_pemasukan_mingguan) ?>, backgroundColor: '#006D5B', borderRadius: 8 },
                { label: 'Pengeluaran', data: <?= json_encode($data_pengeluaran_mingguan) ?>, backgroundColor: '#e1575f', borderRadius: 8 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });
    new Chart(document.getElementById('chartDonutPemasukan'), {
        type: 'doughnut',
        data: { labels: <?= json_encode($label_pemasukan) ?>, datasets: [{ data: <?= json_encode($dana_pemasukan) ?>, backgroundColor: ['#006D5B','#34d399','#059669','#6ee7b7','#a7f3d0'], borderWidth: 2 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '70%' }
    });
    new Chart(document.getElementById('chartDonutPengeluaran'), {
        type: 'doughnut',
        data: { labels: <?= json_encode($label_pengeluaran) ?>, datasets: [{ data: <?= json_encode($dana_pengeluaran) ?>, backgroundColor: ['#e1575f','#fbbf24','#3b82f6','#f97316','#8b5cf6'], borderWidth: 2 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '70%' }
    });
});
</script>
