<?php
include_once __DIR__ . '/../config/koneksi.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { die("Silakan login terlebih dahulu."); }

$mode = $_GET['view'] ?? 'pribadi';
$user_id = ($mode === 'pantau' && ($_SESSION['role'] ?? '') === 'orang_tua' && isset($_SESSION['target_user_id']))
    ? (int)$_SESSION['target_user_id']
    : (int)$_SESSION['user_id'];

$bulan = (int)($_GET['bulan'] ?? date('m'));
$tahun = (int)($_GET['tahun'] ?? date('Y'));
$awal_bulan = sprintf('%04d-%02d-01', $tahun, $bulan);
$akhir_bulan = date('Y-m-t', strtotime($awal_bulan));
$awal_sebelum = date('Y-m-01', strtotime("$awal_bulan -1 month"));
$akhir_sebelum = date('Y-m-t', strtotime($awal_sebelum));
$hari_berjalan = max(1, (int)date('j'));
$hari_bulan = max(1, (int)date('t', strtotime($awal_bulan)));

function gf_money($value) {
    return 'Rp ' . number_format((float)$value, 0, ',', '.');
}

function gf_sum($koneksi, $user_id, $jenis, $start, $end) {
    $query = "
        SELECT COALESCE(SUM(t.jumlah), 0) total
        FROM transaksi t
        JOIN kategori k ON t.id_kategori = k.id_kategori
        WHERE t.user_id = '$user_id'
          AND k.jenis = '$jenis'
          AND t.tanggal BETWEEN '$start' AND '$end'
    ";
    $row = mysqli_fetch_assoc(mysqli_query($koneksi, $query));
    return (float)($row['total'] ?? 0);
}

$pemasukan = gf_sum($koneksi, $user_id, 'Pemasukan', $awal_bulan, $akhir_bulan);
$pengeluaran = gf_sum($koneksi, $user_id, 'Pengeluaran', $awal_bulan, $akhir_bulan);
$pengeluaran_lalu = gf_sum($koneksi, $user_id, 'Pengeluaran', $awal_sebelum, $akhir_sebelum);
$saldo = $pemasukan - $pengeluaran;
$rata_harian = $pengeluaran / $hari_berjalan;
$proyeksi = $rata_harian * $hari_bulan;
$rasio_belanja = $pemasukan > 0 ? ($pengeluaran / $pemasukan) * 100 : 0;
$perubahan = $pengeluaran_lalu > 0 ? (($pengeluaran - $pengeluaran_lalu) / $pengeluaran_lalu) * 100 : 0;

$q_kategori = mysqli_query($koneksi, "
    SELECT k.nama_kategori, SUM(t.jumlah) total, COUNT(*) jumlah_transaksi
    FROM transaksi t
    JOIN kategori k ON t.id_kategori = k.id_kategori
    WHERE t.user_id = '$user_id'
      AND k.jenis = 'Pengeluaran'
      AND t.tanggal BETWEEN '$awal_bulan' AND '$akhir_bulan'
    GROUP BY k.id_kategori, k.nama_kategori
    ORDER BY total DESC
");

$labels = [];
$values = [];
$top_kategori = 'Belum ada data';
$top_total = 0;
$jumlah_transaksi = 0;
while ($row = mysqli_fetch_assoc($q_kategori)) {
    $labels[] = $row['nama_kategori'];
    $values[] = (float)$row['total'];
    $jumlah_transaksi += (int)$row['jumlah_transaksi'];
    if ($top_total <= 0) {
        $top_kategori = $row['nama_kategori'];
        $top_total = (float)$row['total'];
    }
}

$top_share = $pengeluaran > 0 ? ($top_total / $pengeluaran) * 100 : 0;

$q_trend = mysqli_query($koneksi, "
    SELECT DATE_FORMAT(t.tanggal, '%Y-%m') bulan, k.jenis, SUM(t.jumlah) total
    FROM transaksi t
    JOIN kategori k ON t.id_kategori = k.id_kategori
    WHERE t.user_id = '$user_id'
      AND t.tanggal >= DATE_SUB('$awal_bulan', INTERVAL 5 MONTH)
    GROUP BY DATE_FORMAT(t.tanggal, '%Y-%m'), k.jenis
    ORDER BY bulan ASC
");
$trend = [];
while ($row = mysqli_fetch_assoc($q_trend)) {
    $key = $row['bulan'];
    if (!isset($trend[$key])) $trend[$key] = ['Pemasukan' => 0, 'Pengeluaran' => 0];
    $trend[$key][$row['jenis']] = (float)$row['total'];
}
$trend_labels = array_keys($trend);
$trend_income = array_map(fn($x) => $x['Pemasukan'], array_values($trend));
$trend_expense = array_map(fn($x) => $x['Pengeluaran'], array_values($trend));

$score = 100;
if ($rasio_belanja > 80) $score -= 30;
elseif ($rasio_belanja > 60) $score -= 18;
if ($top_share > 55) $score -= 18;
if ($saldo < 0) $score -= 25;
if ($perubahan > 25) $score -= 12;
$score = max(10, min(100, round($score)));

$rekomendasi = [];
if ($pengeluaran <= 0) {
    $rekomendasi[] = 'Mulai catat transaksi kecil harian agar pola finansial bisa terbaca.';
} else {
    if ($top_share > 50) $rekomendasi[] = "Kategori $top_kategori menyerap " . round($top_share) . "% pengeluaran. Pasang batas mingguan khusus untuk kategori ini.";
    if ($rasio_belanja > 75) $rekomendasi[] = 'Rasio pengeluaran terhadap pemasukan sudah tinggi. Prioritaskan kebutuhan pokok dan tahan pembelian impulsif.';
    if ($proyeksi > $pengeluaran * 1.15) $rekomendasi[] = 'Laju belanja harian mengarah naik. Targetkan pengeluaran harian maksimal ' . gf_money(max(0, ($pemasukan * 0.7) / $hari_bulan)) . '.';
    if ($saldo > 0) $rekomendasi[] = 'Ada ruang surplus. Sisihkan minimal 20% dari saldo bersih ke tabungan atau dana darurat.';
}
if (empty($rekomendasi)) $rekomendasi[] = 'Keuangan terlihat stabil. Pertahankan ritme dan cek kategori terbesar setiap akhir pekan.';

$persona = $score >= 80 ? 'Strategist Hemat' : ($score >= 60 ? 'Balanced Spender' : 'Mode Waspada');
$persona_desc = $score >= 80
    ? 'Arus kas sehat, risiko rendah, dan pengeluaran terkendali.'
    : ($score >= 60 ? 'Masih aman, tapi ada beberapa kategori yang perlu dijaga.' : 'Pengeluaran mulai menekan saldo. Butuh rem yang lebih tegas.');
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.ai-hero{border:0;border-radius:18px;background:linear-gradient(135deg,#063f35,#00806a);color:#fff;box-shadow:0 14px 34px rgba(0,109,91,.18)}
.ai-card{border:1px solid #e5e7eb;border-radius:16px;background:#fff;box-shadow:0 4px 16px rgba(15,23,42,.04)}
.ai-score{width:92px;height:92px;border-radius:50%;display:grid;place-items:center;background:#ecfdf5;color:#006D5B;font-weight:800;font-size:1.7rem}
.ai-chip{display:inline-flex;align-items:center;gap:6px;padding:7px 11px;border-radius:999px;background:#f1f5f9;color:#475569;font-size:.78rem;font-weight:700}
</style>

<div class="container-fluid px-2">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">AI Financial Intelligence</h3>
            <p class="text-muted small mb-0">Analisis kesehatan finansial, prediksi arus kas, dan rekomendasi tindakan.</p>
        </div>
        <form method="GET" action="index.php" class="d-flex gap-2">
            <input type="hidden" name="page" value="analisis">
            <select name="bulan" class="form-select form-select-sm">
                <?php for ($i=1; $i<=12; $i++): ?>
                    <option value="<?= $i ?>" <?= $i === $bulan ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$i,1)) ?></option>
                <?php endfor; ?>
            </select>
            <select name="tahun" class="form-select form-select-sm">
                <?php for ($i=(int)date('Y')-3; $i<=(int)date('Y')+1; $i++): ?>
                    <option value="<?= $i ?>" <?= $i === $tahun ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <button class="btn btn-sm text-white fw-semibold" style="background:#006D5B;">Analisis</button>
        </form>
    </div>

    <div class="card ai-hero p-4 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-md-auto"><div class="ai-score"><?= $score ?></div></div>
            <div class="col">
                <span class="ai-chip mb-2">AI Persona: <?= htmlspecialchars($persona) ?></span>
                <h4 class="fw-bold mb-2"><?= htmlspecialchars($persona_desc) ?></h4>
                <p class="mb-0 opacity-75">Bulan ini pengeluaran terbesar ada di <strong><?= htmlspecialchars($top_kategori) ?></strong> sebesar <?= gf_money($top_total) ?>. Proyeksi pengeluaran akhir bulan: <strong><?= gf_money($proyeksi) ?></strong>.</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="ai-card p-3"><small class="text-muted fw-bold">Pemasukan</small><h4 class="fw-bold text-success mb-0"><?= gf_money($pemasukan) ?></h4></div></div>
        <div class="col-md-3"><div class="ai-card p-3"><small class="text-muted fw-bold">Pengeluaran</small><h4 class="fw-bold text-danger mb-0"><?= gf_money($pengeluaran) ?></h4></div></div>
        <div class="col-md-3"><div class="ai-card p-3"><small class="text-muted fw-bold">Saldo Bersih</small><h4 class="fw-bold mb-0"><?= gf_money($saldo) ?></h4></div></div>
        <div class="col-md-3"><div class="ai-card p-3"><small class="text-muted fw-bold">Rasio Belanja</small><h4 class="fw-bold mb-0"><?= round($rasio_belanja) ?>%</h4></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="ai-card p-4 h-100">
                <h5 class="fw-bold text-dark mb-3">Tren 6 Bulan</h5>
                <div style="height:300px"><canvas id="trendChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="ai-card p-4 h-100">
                <h5 class="fw-bold text-dark mb-3">Distribusi Pengeluaran</h5>
                <div style="height:300px"><canvas id="categoryChart"></canvas></div>
            </div>
        </div>
        <div class="col-12">
            <div class="ai-card p-4">
                <h5 class="fw-bold text-dark mb-3">Rekomendasi Pintar</h5>
                <div class="row g-3">
                    <?php foreach ($rekomendasi as $item): ?>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 h-100" style="background:#f8fafc;border:1px solid #e5e7eb;">
                                <i class="fas fa-lightbulb text-warning me-2"></i><?= htmlspecialchars($item) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: { labels: <?= json_encode($trend_labels) ?>, datasets: [
        { label: 'Pemasukan', data: <?= json_encode($trend_income) ?>, borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.12)', tension: .35, fill: true },
        { label: 'Pengeluaran', data: <?= json_encode($trend_expense) ?>, borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,.10)', tension: .35, fill: true }
    ]},
    options: { responsive: true, maintainAspectRatio: false }
});
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: { labels: <?= json_encode($labels ?: ['Belum ada data']) ?>, datasets: [{ data: <?= json_encode($values ?: [1]) ?>, backgroundColor: ['#006D5B','#34d399','#f97316','#3b82f6','#a855f7','#ef4444'] }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '68%' }
});
</script>