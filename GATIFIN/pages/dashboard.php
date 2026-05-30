<?php
// 1. LOGIKA PERHITUNGAN DATA & INTEGRASI AUTO-FILL
$id_user_login = $_SESSION['user_id'];

if (isset($_POST['simpan_transaksi'])) {
    $tanggal     = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $id_dompet   = mysqli_real_escape_string($koneksi, $_POST['id_dompet']);
    $jumlah      = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $keterangan  = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    $query_simpan = "INSERT INTO transaksi (user_id, id_kategori, id_dompet, tanggal, jumlah, keterangan) 
                     VALUES ('$id_user_login', '$id_kategori', '$id_dompet', '$tanggal', '$jumlah', '$keterangan')";
    
    if (mysqli_query($koneksi, $query_simpan)) {
        echo "<script>window.location.href='index.php?page=dashboard&status=sukses';</script>";
        exit;
    } else {
        echo "<script>window.location.href='index.php?page=dashboard&status=gagal';</script>";
        exit;
    }
}

// Menangkap data hasil scan untuk dimasukkan ke variabel form (Auto-Fill)
$val_tanggal = date('Y-m-d');
$val_jumlah = '';
$val_keterangan = '';
$buka_modal_otomatis = false;

if (isset($_GET['scan']) && $_GET['scan'] == 'success' && isset($_SESSION['scan_result'])) {
    $val_tanggal    = $_SESSION['scan_result']['tanggal'];
    $val_jumlah     = $_SESSION['scan_result']['jumlah'];
    $val_keterangan = $_SESSION['scan_result']['keterangan'];
    $buka_modal_otomatis = true;
    unset($_SESSION['scan_result']); // Hapus session hantu
}

// --- INTEGRASI AMBIL DATA UMUR & GENERASI ASLI PENGGUNA DARI DATABASE ---
$umur = 25; // Default jika data tanggal lahir kosong
$generation_type = "Gen Z";

$query_user = "SELECT tanggal_lahir, TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) AS umur_asli FROM user WHERE id_user = '$id_user_login'";
$res_user = mysqli_query($koneksi, $query_user);

if ($res_user && mysqli_num_rows($res_user) > 0) {
    $user_data = mysqli_fetch_assoc($res_user);
    if (!empty($user_data['tanggal_lahir'])) {
        $umur = (int)$user_data['umur_asli'];
    }
}

// Klasifikasi Generasi Secara Akurat Berdasarkan Umur Riil
if ($umur <= 15) {
    $generation_type = "Gen Alpha";
} elseif ($umur >= 16 && $umur <= 29) {
    $generation_type = "Gen Z";
} elseif ($umur >= 30 && $umur <= 45) {
    $generation_type = "Millennials"; 
} elseif ($umur >= 46 && $umur <= 61) {
    $generation_type = "Gen X";
} else {
    $generation_type = "Baby Boomers";
}

// Hitung total saldo awal
$q_saldo_awal = mysqli_query($koneksi, "SELECT SUM(saldo_awal) as total FROM dompet WHERE user_id = '$id_user_login'");
$d_saldo_awal = mysqli_fetch_assoc($q_saldo_awal);
$total_saldo_awal = $d_saldo_awal['total'] ?? 0;

// Hitung total pemasukan
$q_pemasukan = mysqli_query($koneksi, "SELECT SUM(t.jumlah) as total FROM transaksi t JOIN kategori k ON t.id_kategori = k.id_kategori WHERE t.user_id = '$id_user_login' AND k.jenis = 'Pemasukan'");
$d_pemasukan = mysqli_fetch_assoc($q_pemasukan);
$total_pemasukan = $d_pemasukan['total'] ?? 0;

// Hitung total pengeluaran
$q_pengeluaran = mysqli_query($koneksi, "SELECT SUM(t.jumlah) as total FROM transaksi t JOIN kategori k ON t.id_kategori = k.id_kategori WHERE t.user_id = '$id_user_login' AND k.jenis = 'Pengeluaran'");
$d_pengeluaran = mysqli_fetch_assoc($q_pengeluaran);
$total_pengeluaran = $d_pengeluaran['total'] ?? 0;

$saldo_mengendap = $total_saldo_awal + $total_pemasukan - $total_pengeluaran;

// Cari Top Kategori Pengeluaran Khusus untuk Dashboard AI Insight
$q_top_kat = mysqli_query($koneksi, "SELECT k.nama_kategori, SUM(t.jumlah) as total FROM transaksi t JOIN kategori k ON t.id_kategori = k.id_kategori WHERE t.user_id = '$id_user_login' AND k.jenis = 'Pengeluaran' GROUP BY k.nama_kategori ORDER BY total DESC LIMIT 1");
$top_kategori = "N/A";
if ($q_top_kat && mysqli_num_rows($q_top_kat) > 0) {
    $d_top_kat = mysqli_fetch_assoc($q_top_kat);
    $top_kategori = $d_top_kat['nama_kategori'];
}

// Mengambil data kategori dan dompet untuk pemicu dropdown modal
$query_kat = mysqli_query($koneksi, "SELECT id_kategori, nama_kategori, jenis FROM kategori WHERE user_id = '$id_user_login'");
$kategori_pemasukan = [];
$kategori_pengeluaran = [];
if ($query_kat) {
    while ($row = mysqli_fetch_assoc($query_kat)) {
        if ($row['jenis'] == 'Pemasukan') { $kategori_pemasukan[] = $row; } else { $kategori_pengeluaran[] = $row; }
    }
}
$query_dompet = mysqli_query($koneksi, "SELECT id_dompet, nama_dompet FROM dompet WHERE user_id = '$id_user_login'");

// LOGIKA DIKSI BAHASA ADAPTIVE AI BANNER
function getDashboardInsight($total, $top_cat, $gen) {
    if ($total <= 0) return "Dompet aman tak tersentuh. Yuk, catat transaksi pengeluaran pertamamu hari ini!";
    
    switch ($gen) {
        case "Gen Alpha":
            return "Kategori <strong>$top_cat</strong> lagi banyak menyerap uang jajarmu nih dek. Tabung sebagian ya! 🌟";
        case "Gen Z":
            return "Dompet lu lagi diuji sama <strong>$top_cat</strong>, bestie. Ngerem dikit lah biar ga boncos parah akhir bulan! 😭";
        case "Millennials":
            return "Fokus alokasi pengeluaran Anda terkonsentrasi di pos <strong>$top_cat</strong>. Awasi kestabilan rasio investasi dan kebutuhan bulanan Anda.";
        case "Gen X":
            return "Pemantauan berkala: Pos <strong>$top_cat</strong> menjadi pengeluaran terbesar rumah tangga saat ini. Pastikan dana darurat tetap aman.";
        case "Baby Boomers":
            return "Evaluasi berkala aset masa tua: Anggaran mengalir stabil dengan pos pengeluaran utama pada sektor <strong>$top_cat</strong>.";
    }
}

$clean_insight = getDashboardInsight($total_pengeluaran, $top_kategori, $generation_type);
?>

<!-- Dashboard styles: see assets/css/style.css -->

<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">Dashboard Keuangan</h3>
            <p class="text-muted mb-0">Selamat datang kembali, pantau kesehatan finansial Anda hari ini.</p>
        </div>
        <div class="d-flex gap-2 dash-actions">
            <button type="button" class="btn btn-warning text-dark fw-semibold px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalScanNota">
                <i class="fas fa-camera me-2"></i>Scan Nota/Struk
            </button>
            <button type="button" class="btn text-white fw-semibold px-3 py-2" style="background-color:var(--brand);border:none;" data-bs-toggle="modal" data-bs-target="#modalTambahTransaksi">
                <i class="fas fa-plus me-2"></i>Tambah Manual
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 gradient-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-2">Sisa Saldo Mengendap</p>
                        <h2 class="fw-bold mb-0">Rp <?= number_format($saldo_mengendap, 0, ',', '.'); ?></h2>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-3 p-3">
                        <i class="fas fa-wallet fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 glass-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label text-muted mb-2">Total Pemasukan</p>
                        <h4 class="fw-bold text-success mb-0">+ Rp <?= number_format($total_pemasukan, 0, ',', '.'); ?></h4>
                    </div>
                    <div class="icon-box bg-success bg-opacity-10 text-success">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 glass-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label text-muted mb-2">Total Pengeluaran</p>
                        <h4 class="fw-bold text-danger mb-0">- Rp <?= number_format($total_pengeluaran, 0, ',', '.'); ?></h4>
                    </div>
                    <div class="icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-4 dashboard-card-custom">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2">🤖</div>
                    <div>
                        <div class="mb-1">
                            <h6 class="fw-bold text-dark m-0">Gatifin AI Spark Insight</h6>
                        </div>
                        <p class="mb-0 text-secondary small"><?= $clean_insight; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card p-3 dashboard-card-custom <?= ($saldo_mengendap >= 0) ? 'bg-white' : 'bg-danger bg-opacity-10'; ?>" style="<?= ($saldo_mengendap >= 0) ? '' : 'border-color: #fca5a5;'; ?>">
                <div class="d-flex align-items-center">
                    <div class="<?= ($saldo_mengendap >= 0) ? 'text-success bg-success' : 'text-danger bg-danger'; ?> bg-opacity-10 p-2 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                        <i class="fas <?= ($saldo_mengendap >= 0) ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Status Arus Kas</h6>
                        <p class="mb-0 text-secondary small">
                            Status Arus Kas: <strong class="<?= ($saldo_mengendap >= 0) ? 'text-success' : 'text-danger'; ?>">Arus kas Anda dalam kondisi <?= ($saldo_mengendap >= 0) ? 'sehat dan positif (surplus)' : 'defisit (lebih besar pasak daripada tiang)'; ?></strong>. <?= ($saldo_mengendap >= 0) ? 'Pertahankan stabilitas tabungan bulanan Anda!' : 'Segera lakukan peninjauan kembali pada pos pengeluaran tersier Anda.'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalScanNota" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header border-0 bg-light py-3">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-expand me-2 text-warning"></i>Scan Struk Menggunakan AI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="config/proses_scan.php" method="POST" enctype="multipart/form-data" id="formScanEngine">
                <input type="hidden" name="redirect" value="dashboard">
                <div class="modal-body p-4 text-center">
                    <p class="text-muted small mb-3">Unggah foto nota fisik Anda. Kecerdasan Buatan (AI) akan secara otomatis mengenali data tanggal transaksi, nominal, dan nama toko.</p>
                    
                    <div class="border-dashed-custom p-4 rounded-4 text-center" style="cursor: pointer;" onclick="document.getElementById('fileInputNota').click();">
                        <i class="fas fa-camera-retro fa-3x text-secondary mb-2"></i>
                        <h6 class="fw-bold text-dark mb-1">Ambil Gambar / Pilih File</h6>
                        <span class="text-muted small d-block" id="labelFileNota">Mendukung JPG, JPEG, PNG</span>
                        <input type="file" id="fileInputNota" name="foto_nota" accept="image/*" style="display:none;" required onchange="document.getElementById('labelFileNota').innerHTML = '<strong>Terpilih:</strong> ' + this.files[0].name">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="proses_ocr" id="btnSubmitScan" class="btn btn-sm text-dark btn-warning px-4 fw-semibold">
                        <i class="fas fa-robot me-1"></i> Mulai Analisis AI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahTransaksi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header border-0 bg-light py-3">
                <h5 class="modal-title fw-bold" id="modalTambahTransaksiLabel" style="color:var(--brand);">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Catat Transaksi Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Tanggal Transaksi</label>
                        <input type="date" class="form-control" name="tanggal" value="<?= $val_tanggal; ?>" style="border-radius: 10px;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Jenis Transaksi</label>
                        <select class="form-select" id="jenis_transaksi" name="jenis" style="border-radius: 10px;" required>
                            <option value="">-- Pilih Jenis Transaksi --</option>
                            <option value="Pemasukan">Pemasukan</option>
                            <option value="Pengeluaran" <?= ($buka_modal_otomatis) ? 'selected' : ''; ?>>Pengeluaran</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Sub-Kategori</label>
                        <select class="form-select" id="sub_kategori" name="id_kategori" style="border-radius: 10px;" required disabled>
                            <option value="">-- Pilih Jenis Terlebih Dahulu --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Simpan / Ambil Dari Dompet</label>
                        <select class="form-select" name="id_dompet" style="border-radius: 10px;" required>
                            <option value="">-- Pilih Dompet/Akun --</option>
                            <?php if ($query_dompet): mysqli_data_seek($query_dompet, 0); ?>
                            <?php while($d = mysqli_fetch_assoc($query_dompet)): ?>
                                <option value="<?= $d['id_dompet']; ?>"><?= htmlspecialchars($d['nama_dompet']); ?></option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Jumlah Uang</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold text-muted" style="border-top-left-radius: 10px; border-bottom-left-radius: 10px;">Rp</span>
                            <input type="number" class="form-control" name="jumlah" value="<?= $val_jumlah; ?>" placeholder="Contoh: 25000" style="border-top-right-radius: 10px; border-bottom-right-radius: 10px;" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="3" placeholder="Contoh: Belanja Bulanan" style="border-radius: 10px;"><?= htmlspecialchars($val_keterangan); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal" style="border-radius: 8px;">Batal</button>
                    <button type="submit" name="simpan_transaksi" class="btn btn-sm text-white px-4 fw-semibold" style="background:var(--brand);border-radius:var(--radius-md);">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const scanErrorMessage = <?= json_encode($_SESSION['scan_error'] ?? '') ?>;
    <?php unset($_SESSION['scan_error']); ?>

    if ((status === 'sukses' || status === 'gagal' || status === 'gagal_scan') && typeof Swal !== 'undefined') {
        let titleAlert = status === 'sukses' ? 'Berhasil!' : 'Gagal Analisis!';
        let textAlert = status === 'sukses' ? 'Transaksi dicatat ke database.' : (scanErrorMessage || 'Sistem AI gagal membaca struk dengan benar. Pastikan foto tegak, jelas, tidak buram, dan coba lagi.');
        let iconAlert = status === 'sukses' ? 'success' : 'error';

        Swal.fire({ icon: iconAlert, title: titleAlert, text: textAlert, confirmButtonColor: '#006D5B' }).then(() => {
            window.history.replaceState({}, document.title, "index.php?page=dashboard");
        });
    }

    const formScan = document.getElementById('formScanEngine');
    const btnScan = document.getElementById('btnSubmitScan');
    if (formScan && btnScan) {
        formScan.addEventListener('submit', function() {
            btnScan.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menganalisis Struk...';
            btnScan.disabled = true;
        });
    }

    const dataPemasukan = <?= json_encode($kategori_pemasukan); ?>;
    const dataPengeluaran = <?= json_encode($kategori_pengeluaran); ?>;
    const selectJenis = document.getElementById('jenis_transaksi');
    const selectSub = document.getElementById('sub_kategori');

    function populateSubKategori(jenisValue) {
        selectSub.innerHTML = '<option value="">-- Pilih Sub-Kategori --</option>';
        let listData = (jenisValue === 'Pemasukan') ? dataPemasukan : ((jenisValue === 'Pengeluaran') ? dataPengeluaran : []);
        
        if (listData.length > 0) {
            selectSub.disabled = false;
            listData.forEach(item => {
                let opt = document.createElement('option');
                opt.value = item.id_kategori;
                opt.textContent = item.nama_kategori;
                selectSub.appendChild(opt);
            });
        } else {
            selectSub.disabled = true;
        }
    }

    if (selectJenis && selectSub) {
        selectJenis.addEventListener('change', function() {
            populateSubKategori(this.value);
        });
        if (selectJenis.value !== "") {
            populateSubKategori(selectJenis.value);
        }
    }

    <?php if ($buka_modal_otomatis): ?>
        var konfirmasiModal = new bootstrap.Modal(document.getElementById('modalTambahTransaksi'));
        konfirmasiModal.show();
    <?php endif; ?>
});
</script>
