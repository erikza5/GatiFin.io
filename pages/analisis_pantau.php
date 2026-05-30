<?php
// 1. Path koneksi & Session
include_once __DIR__ . '/../config/koneksi.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { die("Silakan login terlebih dahulu."); }

// --- LOGIKA PENENTUAN ID (DINAMIS) ---
$mode = $_GET['view'] ?? 'pribadi';

// Mengambil ID target jika sedang dalam mode pantau, jika tidak gunakan ID sendiri
if ($mode == 'pantau' && isset($_SESSION['target_user_id'])) {
    $user_id = $_SESSION['target_user_id']; 
} else {
    $user_id = $_SESSION['user_id']; 
}
// -------------------------------------

// --- 1.5 AMBIL DATA UMUR & GENERASI ASLI DARI DATABASE ---
$umur = 25; // Nilai cadangan/default jika data tanggal lahir kosong
$generation_type = "Gen Z";
$peer_average = 2450000;

// Menghitung umur secara real-time dari database berdasarkan tanggal_lahir
// *Catatan: Pastikan di tabel 'user' Anda terdapat kolom 'tanggal_lahir'. Jika namanya berbeda, silakan sesuaikan.
$query_user = "SELECT tanggal_lahir, TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) AS umur_asli FROM user WHERE id_user = '$user_id'";
$res_user = mysqli_query($koneksi, $query_user);

if ($res_user && mysqli_num_rows($res_user) > 0) {
    $user_data = mysqli_fetch_assoc($res_user);
    if (!empty($user_data['tanggal_lahir'])) {
        $umur = (int)$user_data['umur_asli'];
    }
}

// Peta Klasifikasi Karakteristik dan Tolok Ukur Seluruh Generasi (Berdasarkan Umur Riil)
if ($umur <= 15) {
    $generation_type = "Gen Alpha";
    $peer_average = 850000;       // Anggaran jajan sekolah / remaja awal
} elseif ($umur >= 16 && $umur <= 29) {
    $generation_type = "Gen Z";
    $peer_average = 2450000;      // Pengeluaran mahasiswa & pemuda awal
} elseif ($umur >= 30 && $umur <= 45) {
    $generation_type = "Millennials";
    $peer_average = 5800000;      // Kelas pekerja mandiri & keluarga muda (Usia 40 masuk ke sini)
} elseif ($umur >= 46 && $umur <= 61) {
    $generation_type = "Gen X";
    $peer_average = 7500000;      // Biaya operasional rumah tangga matang
} else {
    $generation_type = "Baby Boomers";
    $peer_average = 4200000;      // Masa pensiun & manajemen kesehatan
}

// 2. Query untuk mengambil data agregat pengeluaran
$query = "SELECT k.nama_kategori, SUM(t.jumlah) as total 
          FROM transaksi t 
          JOIN kategori k ON t.id_kategori = k.id_kategori
          WHERE t.user_id = '$user_id' AND k.jenis = 'Pengeluaran'
          GROUP BY k.nama_kategori 
          ORDER BY total DESC";

$result = mysqli_query($koneksi, $query);

$labels = [];
$data_nilai = [];
$total_pengeluaran = 0;
$top_kategori = "N/A";
$max_nilai = 0;

if ($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $labels[] = $row['nama_kategori'];
        $data_nilai[] = (int)$row['total'];
        $total_pengeluaran += $row['total'];
        
        if($row['total'] > $max_nilai) {
            $max_nilai = $row['total'];
            $top_kategori = $row['nama_kategori'];
        }
    }
}

// 3. ADAPTIVE FINANCIAL PERSONALITY CARD (MENYESUAIKAN SELURUH GEN)
$spirit_animal = "Bebek Bingung 🦆";
$spirit_badge_bg = "#e2e8f0";
$spirit_badge_text = "#475569";
$spirit_card_bg = "#f8fafc";
$spirit_desc = "Belum ada transaksi keluar, dompetmu masih suci tanpa dosa pengeluaran.";

if ($total_pengeluaran > 0) {
    $is_boros = ($total_pengeluaran > $peer_average);
    
    switch ($generation_type) {
        case "Gen Alpha":
            $spirit_animal = $is_boros ? "Top Up Sultan 🎮" : "Celengan Kelinci 🐰";
            $spirit_desc = $is_boros ? "Uang sakumu banyak mengalir ke hiburan digital atau game, ngerem dikit dek!" : "Keren banget! Kamu pinter nabung dan ga gampang kegoda jajan barang viral.";
            break;
            
        case "Gen Z":
            $spirit_animal = $is_boros ? "Babi Ngepet Elit 🐖💨" : "Kucing Irit Estetik 🐱🌿";
            $spirit_desc = $is_boros ? "Gokil, pengeluaran gaya hidup buat " . $top_kategori . " mendominasi. Dompetmu menangis di pojokan!" : "Pola belanjamu rapi, estetik, dan terkontrol. Kamu beneran paham cara cari promo!";
            break;
            
        case "Millennials":
            $spirit_animal = $is_boros ? "Mentri Fomo Finansial 💸" : "Manajer Investasi Bijak 💼";
            $spirit_desc = $is_boros ? "Beban cicilan atau pengeluaran konsumtif di pos " . $top_kategori . " melampaui batas wajar bulan ini." : "Struktur keuangan sehat. Alokasi dana harian dan tabungan masa depan terbagi seimbang.";
            break;
            
        case "Gen X":
            $spirit_animal = $is_boros ? "Donatur Utama Keluarga 🏛️" : "Nahkoda Finansial Kokoh ⚓";
            $spirit_desc = $is_boros ? "Arus pengeluaran besar terdeteksi pada kebutuhan domestik. Pastikan dana cadangan tetap aman." : "Sangat matang. Pengelolaan dana operasional rumah tangga terkendali dengan efisiensi tinggi.";
            break;
            
        case "Baby Boomers":
            $spirit_animal = $is_boros ? "Sultan Kebun Sejahtera 🏡" : "Sesepuh Dana Abadi 🪙";
            $spirit_desc = $is_boros ? "Pengeluaran bulan ini cukup tinggi, luangkan waktu memeriksa kembali pos tersier Anda." : "Sangat bijaksana. Menjaga kestabilan aset masa tua dengan penuh perhitungan objektif.";
            break;
    }
    
    // Warna badge kepribadian dinamis
    $spirit_badge_bg = $is_boros ? "#ffe4e6" : "#dcfce7";
    $spirit_badge_text = $is_boros ? "#e11d48" : "#15803d";
    $spirit_card_bg = $is_boros ? "#fff1f2" : "#f0fdf4";
}

// 4. OMNI-GENERATION INSIGHT ENGINE (Diksi Bahasa Menyesuaikan Profil Psikologis Setiap Gen)
function getOmniGenerationInsight($total, $top_cat, $max, $gen) {
    if ($total <= 0) return "Dompetmu masih kosong kek kuburan. Simpanan aman, tapi yuk mulai catat transaksi harianmu!";
    $persen = ($max / $total) * 100;
    
    switch ($gen) {
        case "Gen Alpha":
            if ($persen > 50) return "Waduh dek, sekitar " . round($persen) . "% uang kamu abis buat **$top_cat** aja nih. Jangan keseringan *top-up* game atau jajan mainan viral ya, mending uangnya ditabung buat beli barang idaman kamu nanti! 🚀";
            return "Wah kamu hebat banget dek! Pengeluaran di **$top_cat** masih aman. Terus pertahankan cara jajan kamu yang pinter ini ya! 🌟";
            
        case "Gen Z":
            if ($persen > 60) return "Agak kurang-kurangin *healing*-nya ya *bestie*! Pengeluaran lu buat **$top_cat** udah jebol sampai " . round($persen) . "%. Lu mau kaya beneran atau cuma mau keliatan kaya di sosmed doang nih? Tombok melulu nangis! 😭";
            return "Finansial lu *slay* banget bulan ini! Belanja di pos **$top_cat** masih batas wajar dan aman terkendali. Pertahankan *vibe* positif ini, lu beneran paham cara ngatur duit! *No cap!* 🧢🔥";
            
        case "Millennials":
            if ($total > 5000000) return "Analisis Profesional: Total pengeluaran Anda bulan ini menembus Rp " . number_format($total, 0, ',', '.') . " dengan konsentrasi utama pada **$top_cat**. Pertimbangkan untuk memangkas anggaran *lifestyle* atau kopi harian Anda sebelum arus kas akhir bulan terganggu. Let's be wiser! ☕📈";
            return "Manajemen keuangan Anda bulan ini berada di performa terbaik. Rasio pengeluaran untuk **$top_cat** masih berada di batas ideal anggaran bulanan. Manajemen risiko portofolio Anda berjalan aman.";
            
        case "Gen X":
            if ($persen > 50) return "Yth. Bapak/Ibu, evaluasi anggaran mendeteksi adanya akumulasi biaya yang cukup tinggi pada kategori **$top_cat** (mencapai " . round($persen) . "% anggaran). Disarankan untuk memeriksa kembali efisiensi biaya rumah tangga guna memperkuat pos dana darurat.";
            return "Struktur finansial Bapak/Ibu bulan ini terpantau sangat prima. Pengendalian dana bulanan di kategori **$top_cat** mencerminkan perencanaan yang matang dan proteksi aset yang berjalan stabil.";
            
        case "Baby Boomers":
            return "Laporan Keuangan Masa Tua: Total pengeluaran tercatat sebesar Rp " . number_format($total, 0, ',', '.') . " dengan alokasi terbesar pada **$top_cat**. Seluruh perputaran dana harian Anda berada dalam zona aman, stabil, dan minim risiko finansial.";
    }
}

$raw_insight = getOmniGenerationInsight($total_pengeluaran, $top_kategori, $max_nilai, $generation_type);

// FIX PERBAIKAN TEKS: Mengubah format markdown bintang dari AI menjadi tag HTML agar rapi saat tampil
$clean_insight = preg_replace('/__([^_]+)__|\*\*([^*]+)\*\*/', '<strong>$1$2</strong>', $raw_insight);
$clean_insight = preg_replace('/_([^_]+)_|\*([^*]+)\*/', '<em>$1$2</em>', $clean_insight);
?>

<style>
    /* Custom Styling yang Selaras dengan Modern Sidebar Gatifin */
    .modern-card {
        border: 1px solid #edf2f7 !important;
        border-radius: 16px;
        background-color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .modern-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.06);
    }
    .gatifin-gradient-ai {
        background: linear-gradient(135deg, #005A4B, #00806A);
        color: white;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 90, 75, 0.15);
    }
    .typing-cursor::after {
        content: ' ▍';
        animation: blink 0.6s infinite;
    }
    @keyframes blink { 50% { opacity: 0; } }
    
    .chart-container {
        position: relative; 
        height: 240px;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-dark m-0" style="letter-spacing: -0.5px;">Financial Vibe Check ✨</h2>
        <p class="text-muted small m-0">Wawasan mendalam finansial yang disesuaikan dengan profil Anda</p>
    </div>
    <span class="badge bg-dark text-white px-3 py-2 fw-medium" style="border-radius: 20px; font-size: 0.75rem;">
        🎯 Profil: <?php echo $generation_type; ?> (<?php echo $umur; ?> Tahun)
    </span>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card p-4 gatifin-gradient-ai border-0">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="fs-4">🤖</span>
                <h5 class="mb-0 fw-bold" style="letter-spacing: 0.3px;">Gatifin AI Smart Insight:</h5>
            </div>
            <p class="mb-0 fs-5 fw-light typing-cursor" id="genzInsight"></p>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-7">
        <div class="card p-4 modern-card" style="min-height: 350px;">
            <div class="d-flex align-items-center gap-2 mb-4">
                <span class="fs-5">🔮</span>
                <h5 class="fw-bold text-dark m-0">Alokasi Dana Keluar</h5>
            </div>
            <?php if ($total_pengeluaran > 0): ?>
                <div class="chart-container">
                    <canvas id="genzChart"></canvas>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <span class="fs-1">🕶️</span>
                    <p class="text-muted small mt-2 mb-0">Belum ada riwayat pengeluaran yang tercatat.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card p-4 modern-card d-flex flex-column justify-content-between" style="min-height: 350px;">
            <div>
                <h5 class="fw-bold text-dark mb-0">Financial Persona</h5>
                <p class="text-muted small">Karakter keuangan berdasarkan kohort generasi</p>
            </div>
            
            <div class="p-4 my-2 text-center" style="background-color: <?php echo $spirit_card_bg; ?>; border-radius: 12px; border: 1px solid #edf2f7;">
                <span class="badge mb-2 px-3 py-1.5 fw-bold" style="background-color: <?php echo $spirit_badge_bg; ?>; color: <?php echo $spirit_badge_text; ?>; border-radius: 30px; font-size: 0.75rem;">
                    IDENTIFIKASI AKURAT
                </span>
                <h3 class="fw-bold text-dark my-2"><?php echo $spirit_animal; ?></h3>
                <p class="small text-secondary mb-0"><?php echo $spirit_desc; ?></p>
            </div>

            <div class="p-3 bg-light" style="border-radius: 12px; border-left: 4px solid #005A4B;">
                <span class="text-muted small d-block mb-1">Beban Pengeluaran Bulan Ini</span>
                <span class="fs-4 fw-bold text-dark">Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card p-4 modern-card" style="background-color: #fafafa;">
            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <span>👥</span> Peer Comparison (Tolok Ukur Kelompok Sosial Anda)
            </h6>
            <div class="row align-items-center text-center text-md-start g-3">
                <div class="col-md-3 border-end">
                    <span class="text-muted small d-block mb-1">Pengeluaran Anda</span>
                    <h5 class="fw-bold text-dark m-0">Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></h5>
                </div>
                <div class="col-md-3 border-end">
                    <span class="text-muted small d-block mb-1">Rata-rata Standar <?php echo $generation_type; ?></span>
                    <h5 class="fw-bold text-muted m-0">Rp <?php echo number_format($peer_average, 0, ',', '.'); ?></h5>
                </div>
                <div class="col-md-6 ps-md-4">
                    <span class="badge bg-secondary mb-2" style="font-size: 11px; background-color: #475569 !important;">Analisis Komparatif</span>
                    <p class="mb-0 small text-secondary">
                        <?php if($total_pengeluaran > $peer_average): ?>
                            Pengeluaran Anda terdeteksi <strong>lebih tinggi</strong> dari standar pengeluaran rata-rata kelompok kohort <strong><?php echo $generation_type; ?></strong>. Memeriksa kembali pos sekunder/tersier Anda dapat mengamankan tabungan masa depan.
                        <?php else: ?>
                            Luar biasa! Manajemen anggaran Anda terbukti <strong>lebih hemat dan terencana</strong> dari rata-rata pengeluaran standar kelompok <strong><?php echo $generation_type; ?></strong>. Pertahankan stabilitas aset ini!
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- TEXT TYPING EFFECT & PARSING HTML ---
        const textContent = <?php echo json_encode($clean_insight); ?>;
        const container = document.getElementById('genzInsight');
        
        let currentText = "";
        let index = 0;
        let isTag = false;
        let tagBuffer = "";

        function typeWriter() {
            if (index < textContent.length) {
                let char = textContent.charAt(index);
                
                if (char === '<') { isTag = true; }
                
                if (isTag) {
                    tagBuffer += char;
                    if (char === '>') {
                        isTag = false;
                        currentText += tagBuffer;
                        tagBuffer = "";
                        container.innerHTML = currentText;
                    }
                } else {
                    currentText += char;
                    container.innerHTML = currentText;
                }
                
                index++;
                setTimeout(typeWriter, isTag ? 1 : 20);
            } else {
                container.classList.remove('typing-cursor');
            }
        }
        setTimeout(typeWriter, 400);

        // --- MODERN CHART.JS DOUGHNUT CONFIG ---
        <?php if ($total_pengeluaran > 0): ?>
        const ctx = document.getElementById('genzChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($data_nilai); ?>,
                    backgroundColor: ['#00806A', '#34d399', '#f87171', '#fbbf24', '#818cf8', '#a7f3d0'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 12
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                cutout: '75%', 
                plugins: { 
                    legend: { 
                        position: 'right',
                        labels: { usePointStyle: true, boxWidth: 8, font: { size: 12, weight: '500' }, padding: 15 }
                    }
                },
                animation: { animateScale: true, animateRotate: true }
            }
        });
        <?php endif; ?>
    });
</script>