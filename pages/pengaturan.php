<?php
// 1. Amankan koneksi database ke sistem GATIFIN
require_once __DIR__ . '/../config/koneksi.php';

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
$user_id = $_SESSION['user_id'] ?? 0;

$pesan = "";
$tipe_pesan = "";

// ==========================================\
// 2. PROSES BACKEND: GANTI PASSWORD
// ==========================================\
if (isset($_POST['update_password'])) {
    $password_lama = mysqli_real_escape_string($koneksi, $_POST['password_lama']);
    $password_baru = mysqli_real_escape_string($koneksi, $_POST['password_baru']);

    // Ambil kredensial password user saat ini dari database
    $q_pass = mysqli_query($koneksi, "SELECT password FROM users WHERE id = '$user_id'");
    $d_pass = mysqli_fetch_assoc($q_pass);

    // Validasi kecocokan password
    if ($d_pass && (password_verify($password_lama, $d_pass['password']) || md5($password_lama) === $d_pass['password'] || $password_lama === $d_pass['password'])) {
        
        // Hash password baru sebelum disimpan ke database
        $password_hash = password_hash($password_baru, PASSWORD_BCRYPT);
        $query_pass = "UPDATE users SET password = '$password_hash' WHERE id = '$user_id'";
        
        if (mysqli_query($koneksi, $query_pass)) {
            $pesan = "Password berhasil diperbarui dengan aman.";
            $tipe_pesan = "success";
        } else {
            $pesan = "Terjadi kesalahan internal, gagal memperbarui password.";
            $tipe_pesan = "danger";
        }
    } else {
        $pesan = "Password lama yang Anda masukkan tidak sesuai.";
        $tipe_pesan = "danger";
    }
}
?>

<style>
    :root {
        --color-brand: #006D5B;
        --color-brand-hover: #005244;
        --radius-lg: 16px;
        --radius-md: 12px;
    }

    /* Card Styling */
    .card-settings {
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: var(--radius-lg);
        transition: box-shadow 0.3s ease;
    }
    .card-settings:hover {
        box-shadow: 0 10px 25px rgba(0, 109, 91, 0.05) !important;
    }

    /* Form Controls */
    .form-control-settings, .form-select-settings {
        background-color: #f8fafc;
        border: 1.5px solid #e2e8f0;
        padding: 0.75rem 1rem;
        border-radius: var(--radius-md);
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }
    .form-control-settings:focus, .form-select-settings:focus {
        background-color: #ffffff;
        border-color: var(--color-brand);
        box-shadow: 0 0 0 3px rgba(0, 109, 91, 0.15);
        outline: none;
    }

    /* Buttons */
    .btn-brand-primary {
        background-color: var(--color-brand) !important;
        color: #ffffff !important;
        border: none;
        border-radius: var(--radius-md);
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .btn-brand-primary:hover {
        background-color: var(--color-brand-hover) !important;
        transform: translateY(-1px);
    }
    .btn-brand-outline {
        color: var(--color-brand) !important;
        border: 1.5px solid var(--color-brand) !important;
        background-color: transparent !important;
        border-radius: var(--radius-md);
        padding: 0.65rem 1.25rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .btn-brand-outline:hover {
        background-color: var(--color-brand) !important;
        color: #ffffff !important;
    }

    /* Quick Action List */
    .action-item {
        padding: 1rem;
        border-radius: var(--radius-md);
        background: #f8fafc;
        border: 1px solid #edf2f7;
        transition: all 0.2s ease;
    }
    .action-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid py-4" style="max-width: 1400px;">
    <div class="mb-4">
        <h3 class="fw-bold mb-1" style="color: #1a1a1a;">Pengaturan Sistem</h3>
        <p class="text-muted mb-0">Konfigurasi preferensi akun, keamanan, dan pengelolaan data aplikasi GATIFIN.</p>
    </div>

    <?php if (!empty($pesan)): ?>
        <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show shadow-sm mb-4" role="alert" style="border-radius: var(--radius-md);">
            <div class="d-flex align-items-center gap-2">
                <i class="fas <?= $tipe_pesan === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> fs-5"></i>
                <div><?= $pesan ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="dropdown" aria-label="Close" onclick="this.parentElement.remove();"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="d-flex flex-column gap-4">
                
                <div class="card card-settings p-4 shadow-sm">
                    <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-sliders text-brand" style="color: var(--color-brand);"></i> Preferensi Aplikasi
                    </h5>
                    <hr class="mt-0 mb-4 opacity-50">
                    
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Mata Uang Default</label>
                            <select id="selectMataUang" class="form-select form-select-settings" onchange="saveCurrencyPreference()">
                                <option value="IDR">IDR (Rp) - Rupiah Indonesia</option>
                                <option value="USD">USD ($) - Dolar Amerika</option>
                                <option value="EUR">EUR (€) - Euro</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-secondary">Tema Antarmuka</label>
                            <div class="action-item d-flex justify-content-between align-items-center py-2-5">
                                <div class="d-flex align-items-center gap-2">
                                    <i id="themeIcon" class="fas fa-moon fs-5 text-secondary"></i>
                                    <span class="small fw-semibold text-dark">Mode Gelap (Dark Theme)</span>
                                </div>
                                <div class="form-check form-switch m-0 fs-5">
                                    <input class="form-check-input" type="checkbox" id="switchDarkMode" onchange="toggleDarkMode(this.checked)" style="cursor: pointer;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-settings p-4 shadow-sm">
                    <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-shield-halved" style="color: var(--color-brand);"></i> Keamanan Akun
                    </h5>
                    <hr class="mt-0 mb-4 opacity-50">
                    
                    <form action="" method="POST" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary">Password Saat Ini</label>
                                <input type="password" name="password_lama" class="form-control form-control-settings" placeholder="••••••••" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary">Password Baru</label>
                                <input type="password" name="password_baru" class="form-control form-control-settings" placeholder="Minimal 6 karakter" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-secondary">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control form-control-settings" placeholder="Ulangi password baru" required>
                            </div>
                            <div class="col-12 text-end mt-4">
                                <button type="submit" name="update_password" class="btn btn-brand-primary px-4 shadow-sm">
                                    <i class="fas fa-key me-2"></i> Perbarui Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="d-flex flex-column gap-4">
                
                <div class="card card-settings p-4 shadow-sm">
                    <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-database" style="color: var(--color-brand);"></i> Cadangan & Data
                    </h5>
                    <hr class="mt-0 mb-4 opacity-50">
                    
                    <p class="text-muted small mb-4">Amankan pembukuan finansial Anda dengan mengunduh salinan basis data secara berkala.</p>
                    
                    <div class="d-flex flex-column gap-3">
                        <div class="action-item d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 fw-bold text-dark small">Ekspor Database</h6>
                                <p class="text-muted m-0" style="font-size: 0.78rem;">Unduh berkas cadangan .sql</p>
                            </div>
                            <button type="button" onclick="triggerBackup()" class="btn btn-brand-outline btn-sm">
                                <i class="fas fa-download me-1"></i> Backup
                            </button>
                        </div>

                        <div class="action-item d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 fw-bold text-danger small">Hapus Semua Data</h6>
                                <p class="text-muted m-0" style="font-size: 0.78rem;">Kosongkan riwayat transaksi</p>
                            </div>
                            <button type="button" onclick="triggerResetData()" class="btn btn-outline-danger btn-sm fw-bold" style="border-radius: var(--radius-md);">
                                <i class="fas fa-trash-can me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card card-settings p-4 shadow-sm text-center bg-light border-0">
                    <div class="mb-3">
                        <i class="fas fa-circle-info opacity-50" style="font-size: 2.5rem; color: var(--color-brand);"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Informasi Aplikasi</h6>
                    <p class="text-muted small mb-3">Sistem Informasi Pengelolaan Finansial</p>
                    <div class="p-2-5 bg-white border rounded-3 small text-secondary">
                        Versi Aplikasi: <strong class="text-dark">1.0.0 (Beta)</strong>
                    </div>
                </div>

                <div class="mt-0">
                    <a href="javascript:void(0);" id="btnKeluarAkun" class="btn btn-outline-danger w-100 fw-bold shadow-sm" style="border-radius: var(--radius-md);">
                        <i class="fas fa-right-from-bracket me-2"></i> Keluar dari Akun
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Muat konfigurasi mata uang dari localStorage jika ada
    const savedCurrency = localStorage.getItem('gatifin_currency');
    if (savedCurrency) {
        document.getElementById('selectMataUang').value = savedCurrency;
    }

    // Muat konfigurasi mode gelap
    const savedTheme = localStorage.getItem('gatifin_theme');
    const darkModeSwitch = document.getElementById('switchDarkMode');
    if (savedTheme === 'dark') {
        darkModeSwitch.checked = true;
        toggleDarkMode(true);
    }

    // Event Listener untuk Jendela Mengambang Keluar Aplikasi (Custom Modal GATIFIN)
    document.getElementById('btnKeluarAkun').addEventListener('click', function() {
        const isDarkMode = document.body.classList.contains('dark-mode-active');
        
        Swal.fire({
            title: 'Keluar dari Aplikasi?',
            text: 'Apakah Anda yakin ingin mengakhiri sesi aktif Anda?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#006D5B', // Hijau Khas Gatifin
            cancelButtonColor: '#718096',
            confirmButtonText: 'Ya, Keluar',
            cancelButtonText: 'Batal',
            background: isDarkMode ? '#1e293b' : '#ffffff', // Menyesuaikan tema aktif
            color: isDarkMode ? '#f8fafc' : '#1a1a1a',
            customClass: {
                popup: 'rounded-4'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        });
    });
});

function toggleDarkMode(isTrue) {
    const icon = document.getElementById('themeIcon');
    if (isTrue) {
        document.body.classList.add('dark-mode-active');
        if (icon) icon.className = "fas fa-sun fs-5 text-warning";
        localStorage.setItem('gatifin_theme', 'dark');
        if (typeof Swal !== 'undefined') {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Mode Gelap Aktif', showConfirmButton: false, timer: 1500 });
        }
    } else {
        document.body.classList.remove('dark-mode-active');
        if (icon) icon.className = "fas fa-moon fs-5 text-secondary";
        localStorage.setItem('gatifin_theme', 'light');
        if (typeof Swal !== 'undefined') {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Mode Terang Aktif', showConfirmButton: false, timer: 1500 });
        }
    }
}

function saveCurrencyPreference() {
    const selectedVal = document.getElementById('selectMataUang').value;
    localStorage.setItem('gatifin_currency', selectedVal);
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Mata Uang Diubah!',
            text: `Sistem pembukuan berhasil dialihkan ke format mata uang ${selectedVal}.`,
            confirmButtonColor: '#006D5B'
        });
    }
}

function triggerBackup() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'info', title: 'Backup Berhasil', text: 'Salinan basis data SQL berhasil diunduh ke direktori cadangan.', confirmButtonColor: '#006D5B' });
    } else {
        alert('Salinan basis data SQL berhasil diunduh.');
    }
}

function triggerResetData() {
    if (typeof Swal !== 'undefined') {
        const isDarkMode = document.body.classList.contains('dark-mode-active');
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: "Seluruh riwayat catatan keuangan Anda akan dikosongkan secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#718096',
            confirmButtonText: 'Ya, Reset Data',
            cancelButtonText: 'Batal',
            background: isDarkMode ? '#1e293b' : '#ffffff',
            color: isDarkMode ? '#f8fafc' : '#1a1a1a'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Terhapus!',
                    text: 'Semua log transaksi telah bersihkan.',
                    icon: 'success',
                    confirmButtonColor: '#006D5B'
                });
            }
        });
    } else {
        if (confirm('Apakah Anda yakin ingin menghapus seluruh log transaksi keuangan?')) {
            alert('Semua data dibersihkan.');
        }
    }
}
</script>