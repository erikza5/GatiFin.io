<?php
// pages/profil_pantau.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../config/koneksi.php';

// Keamanan: Hanya orang tua yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'orang_tua') {
    echo "<script>alert('Akses ditolak!'); window.location='index.php?page=dashboard';</script>";
    exit;
}

$id_pemantau = $_SESSION['user_id'];

// --- PROSES LOGIKA HAPUS HUBUNGAN PANTAU ---
if (isset($_POST['action_hapus_pantau'])) {
    $id_anak_hapus = mysqli_real_escape_string($koneksi, $_POST['id_anak']);
    $id_pemantau_clean = mysqli_real_escape_string($koneksi, $id_pemantau);

    // Memutus hubungan pemantauan dengan mengosongkan kolom 'created_by' atau mengembalikannya ke NULL
    // Sesuaikan query ini dengan struktur logika database Anda (mengubah created_by kembali menjadi NULL/0)
    $query_hapus = "UPDATE users SET created_by = NULL WHERE id = '$id_anak_hapus' AND created_by = '$id_pemantau_clean'";
    
    if (mysqli_query($koneksi, $query_hapus)) {
        echo "<script>alert('Akun berhasil dihapus dari daftar pantau.'); window.location='index.php?page=profil_pantau';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menghapus akun pantau.');</script>";
    }
}

// Query mengambil data akun anak yang dipantau
$id_pemantau_clean = mysqli_real_escape_string($koneksi, $id_pemantau);
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE created_by = '$id_pemantau_clean'");
?>

<style>
    :root {
        --color-brand: #006D5B;
        --color-brand-hover: #005244;
    }

    /* Efek Card Modern */
    .card-anak { 
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); 
        border-radius: 16px; 
        border: 1px solid rgba(0, 0, 0, 0.06);
        background: #ffffff;
        position: relative;
    }
    .card-anak:hover { 
        transform: translateY(-4px); 
        box-shadow: 0 10px 20px rgba(0, 109, 91, 0.08) !important; 
        border-color: rgba(0, 109, 91, 0.2);
    }

    /* Tombol Tambah Pantau */
    .btn-brand-primary {
        background-color: var(--color-brand) !important;
        color: #ffffff !important;
        border: none;
        border-radius: 10px;
        font-weight: 500;
        transition: background-color 0.2s ease;
    }
    .btn-brand-primary:hover {
        background-color: var(--color-brand-hover) !important;
    }

    /* Tombol Lihat Laporan */
    .btn-brand-outline {
        color: var(--color-brand) !important;
        border: 1.5px solid var(--color-brand) !important;
        background-color: transparent !important;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .btn-brand-outline:hover {
        background-color: var(--color-brand) !important;
        color: #ffffff !important;
    }
    .btn-brand-outline i {
        transition: transform 0.2s ease;
    }
    .btn-brand-outline:hover i {
        transform: translateX(4px);
    }

    /* Tombol Hapus Pantau Tersembunyi Indah di Pojok */
    .btn-delete-pantau {
        position: absolute;
        top: 15px;
        right: 15px;
        background: transparent;
        border: none;
        color: #cbd5e1;
        padding: 5px 8px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .card-anak:hover .btn-delete-pantau {
        color: #ef4444;
        background: #fef2f2;
    }
    .btn-delete-pantau:hover {
        color: #dc2626 !important;
        background: #fee2e2 !important;
    }

    .avatar-pantau {
        object-fit: cover;
        border: 2px solid rgba(0, 109, 91, 0.15);
        padding: 2px;
        background: #fff;
    }
</style>

<div class="container-fluid py-4" style="max-width: 1400px;">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1" style="color: #1a1a1a;">Daftar Akun yang Dipantau</h3>
            <p class="text-muted mb-0">Klik "Lihat Laporan" untuk memantau detail perkembangan keuangan anak.</p>
        </div>
        <a href="index.php?page=tambah_pantau" class="btn btn-brand-primary px-4 py-2-5 shadow-sm">
            <i class="fas fa-user-plus me-2"></i> Tambah Pantau
        </a>
    </div>

    <div class="row g-4">
        <?php if ($query && mysqli_num_rows($query) > 0): ?>
            <?php while ($data = mysqli_fetch_assoc($query)): ?>
                <?php 
                    $nama_tampil = '';
                    if (isset($data['nama lengkap']) && !empty(trim($data['nama lengkap']))) {
                        $nama_tampil = $data['nama lengkap'];
                    } elseif (isset($data['nama']) && !empty(trim($data['nama']))) {
                        $nama_tampil = $data['nama'];
                    } else {
                        $nama_tampil = "Anak Pemantauan";
                    }
                    
                    $foto_profil = (!empty($data['foto'])) ? $data['foto'] : 'default.png';
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <div class="card p-4 shadow-sm h-100 card-anak">
                        
                        <button type="button" class="btn-delete-pantau" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal" 
                                data-id="<?= htmlspecialchars($data['id']) ?>" 
                                data-nama="<?= htmlspecialchars($nama_tampil) ?>"
                                title="Hapus dari daftar pantau">
                            <i class="fas fa-trash-alt fa-sm"></i>
                        </button>

                        <div class="d-flex align-items-center mb-4 pe-3">
                            <img src="assets/img/<?= htmlspecialchars($foto_profil) ?>" 
                                 class="rounded-circle avatar-pantau" 
                                 width="56" height="56" 
                                 alt="Foto Profil">
                            <div class="ms-3 overflow-hidden">
                                <h6 class="mb-0 fw-bold text-truncate" style="color: #2d3748;">
                                    <?= htmlspecialchars($nama_tampil) ?>
                                </h6>
                                <p class="text-muted mb-0 text-truncate" style="font-size: 0.85rem;">
                                    @<?= htmlspecialchars($data['username'] ?? 'user') ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-auto">
                            <a href="index.php?page=laporan_pantau&view=pantau&id=<?= urlencode($data['id']) ?>" 
                               class="btn btn-brand-outline btn-sm w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                                <span>Lihat Laporan</span>
                                <i class="fas fa-arrow-right fa-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm text-center py-5 px-4" style="border-radius: 16px;">
                    <div class="mb-3">
                        <i class="fas fa-user-friends text-muted opacity-50" style="font-size: 3.5rem;"></i>
                    </div>
                    <h5 class="fw-bold text-secondary mb-2">Belum Ada Akun yang Dipantau</h5>
                    <p class="text-muted mx-auto mb-4" style="max-width: 400px;">
                        Anda belum menghubungkan akun anak ke dalam sistem pemantauan finansial ini.
                    </p>
                    <div>
                        <a href="index.php?page=tambah_pantau" class="btn btn-brand-primary px-4 py-2">
                            <i class="fas fa-user-plus me-2"></i> Mulai Tambah Akun
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header border-0 pt-4 px-4 pb-2">
                <h5 class="modal-title fw-bold text-dark" id="deleteModalLabel">Hapus dari Pantauan?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body px-4 py-2">
                    <p class="text-muted">Apakah Anda yakin ingin menghapus akun <strong id="nama_anak_modal" class="text-dark"></strong> dari daftar pemantauan orang tua Anda?</p>
                    <p class="text-danger small mb-0"><i class="fas fa-exclamation-triangle me-1"></i> Anda tidak akan bisa melihat laporan keuangan anak ini lagi kecuali ditambahkan kembali.</p>
                    
                    <input type="hidden" name="id_anak" id="id_anak_modal" value="">
                </div>
                <div class="modal-footer border-0 pt-3 pb-4 px-4 gap-2">
                    <button type="button" class="btn btn-light px-3 py-2 fw-semibold" style="border-radius: 10px; color:#718096;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="action_hapus_pantau" class="btn btn-danger px-4 py-2 fw-semibold" style="border-radius: 10px;">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            // Tombol yang memicu munculnya modal
            const button = event.relatedTarget;
            // Ambil data atribut dari tombol
            const idAnak = button.getAttribute('data-id');
            const namaAnak = button.getAttribute('data-nama');
            
            // Masukkan data ke dalam element modal
            const modalIdInput = deleteModal.querySelector('#id_anak_modal');
            const modalNamaText = deleteModal.querySelector('#nama_anak_modal');
            
            modalIdInput.value = idAnak;
            modalNamaText.textContent = namaAnak;
        });
    }
});
</script>