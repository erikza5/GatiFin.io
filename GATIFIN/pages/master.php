<?php
// Ambil ID Pengguna Aktif dari Sesi Login
$id_user_login = $_SESSION['user_id'];

// ==========================================
// 1. PROSES AKSI UNTUK TABEL KATEGORI
// ==========================================
if (isset($_POST['tambah_kategori'])) {
    $nama_kategori = mysqli_real_escape_string($koneksi, trim($_POST['nama_kategori']));
    $jenis         = mysqli_real_escape_string($koneksi, $_POST['jenis']);
    if (!empty($nama_kategori) && !empty($jenis)) {
        mysqli_query($koneksi, "INSERT INTO kategori (user_id, nama_kategori, jenis) VALUES ('$id_user_login', '$nama_kategori', '$jenis')");
        echo "<script>window.location.href='index.php?page=master&status=sukses_kat';</script>"; exit;
    }
}
if (isset($_POST['edit_kategori'])) {
    $id = $_POST['id_kategori']; $nama = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']); $jenis = $_POST['jenis'];
    mysqli_query($koneksi, "UPDATE kategori SET nama_kategori='$nama', jenis='$jenis' WHERE id_kategori='$id' AND user_id='$id_user_login'");
    echo "<script>window.location.href='index.php?page=master&status=edit_kat_sukses';</script>"; exit;
}
if (isset($_GET['action']) && $_GET['action'] == 'hapus_kat') {
    $id = $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM kategori WHERE id_kategori='$id' AND user_id='$id_user_login'");
    echo "<script>window.location.href='index.php?page=master&status=hapus_kat_sukses';</script>"; exit;
}

// ==========================================
// 2. PROSES AKSI UNTUK TABEL DOMPET
// ==========================================
if (isset($_POST['tambah_dompet'])) {
    $nama_dompet = mysqli_real_escape_string($koneksi, trim($_POST['nama_dompet']));
    $saldo_awal  = mysqli_real_escape_string($koneksi, $_POST['saldo_awal']);
    if (!empty($nama_dompet)) {
        mysqli_query($koneksi, "INSERT INTO dompet (user_id, nama_dompet, saldo_awal) VALUES ('$id_user_login', '$nama_dompet', '$saldo_awal')");
        echo "<script>window.location.href='index.php?page=master&status=sukses_dompet';</script>"; exit;
    }
}
if (isset($_POST['edit_dompet'])) {
    $id = $_POST['id_dompet']; $nama = mysqli_real_escape_string($koneksi, $_POST['nama_dompet']); $saldo = mysqli_real_escape_string($koneksi, $_POST['saldo_awal']);
    mysqli_query($koneksi, "UPDATE dompet SET nama_dompet='$nama', saldo_awal='$saldo' WHERE id_dompet='$id' AND user_id='$id_user_login'");
    echo "<script>window.location.href='index.php?page=master&status=edit_dompet_sukses';</script>"; exit;
}
if (isset($_GET['action']) && $_GET['action'] == 'hapus_dompet') {
    $id = $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM dompet WHERE id_dompet='$id' AND user_id='$id_user_login'");
    echo "<script>window.location.href='index.php?page=master&status=hapus_dompet_sukses';</script>"; exit;
}

// Ambil Data dari Database untuk Ditampilkan
$query_kategori = mysqli_query($koneksi, "SELECT * FROM kategori WHERE user_id = '$id_user_login' ORDER BY id_kategori DESC");
$query_dompet   = mysqli_query($koneksi, "SELECT * FROM dompet WHERE user_id = '$id_user_login' ORDER BY id_dompet DESC");
?>

<style>
    /* Keyframes Animasi Masuk */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translate3d(0, 20px, 0); }
        to { opacity: 1; transform: translate3d(0, 0, 0); }
    }

    .animated-fade-in {
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    /* Kustomisasi Nav Pills Tab */
    .nav-pills .nav-link {
        color: #64748b;
        font-weight: 600;
        padding: 10px 24px;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    .nav-pills .nav-link.active {
        background-color: #006D5B !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(0, 109, 91, 0.25);
    }

    /* Efek Interaktif Kursor pada Baris Tabel & Tombol */
    .custom-table tbody tr {
        transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .custom-table tbody tr:hover {
        background-color: #f8fafc !important;
    }
    .btn-action-hover {
        transition: all 0.2s ease;
    }
    .btn-action-hover:hover {
        transform: translateY(-2px);
    }

    /* Desain Badge Modern */
    .badge-pemasukan { background-color: #d1e7dd; color: #0f5132; font-weight: 600; border-radius: 6px; }
    .badge-pengeluaran { background-color: #f8d7da; color: #842029; font-weight: 600; border-radius: 6px; }

    /* Custom Input Focus */
    .form-control:focus, .form-select:focus {
        border-color: #006D5B;
        box-shadow: 0 0 0 0.25rem rgba(0, 109, 91, 0.15);
    }
</style>

<div class="container-fluid px-2 animated-fade-in">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="fas fa-database text-muted me-2"></i>Pengaturan Data Master</h3>
            <p class="text-muted small mb-0">Kelola preferensi instrumen keuangan seperti kategori aktivitas dan akun dompet digital Anda.</p>
        </div>
    </div>

    <ul class="nav nav-pills mb-4 p-2 bg-white rounded-3 shadow-sm d-inline-flex" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-kategori-tab" data-bs-toggle="pill" data-bs-target="#pills-kategori" type="button" role="tab" aria-controls="pills-kategori" aria-selected="true">
                <i class="fas fa-tags me-2"></i>Kategori Transaksi
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-dompet-tab" data-bs-toggle="pill" data-bs-target="#pills-dompet" type="button" role="tab" aria-controls="pills-dompet" aria-selected="false">
                <i class="fas fa-wallet me-2"></i>Akun Dompet
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        
        <div class="tab-pane fade show active" id="pills-kategori" role="tabpanel" aria-labelledby="pills-kategori-tab">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Daftar Kategori Anda</h5>
                    <button type="button" class="btn btn-sm text-white px-3 py-2 btn-action-hover" style="background-color: #006D5B; border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                        <i class="fas fa-plus me-2"></i>Tambah Kategori
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle custom-table" id="tableKategori" style="width:100%">
                            <thead class="table-light text-muted small text-uppercase">
                                <tr>
                                    <th width="15%">ID Kategori</th>
                                    <th width="45%">Nama Kategori</th>
                                    <th width="20%">Jenis Aliran</th>
                                    <th width="20%" class="text-center">Aksi Manajerial</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($query_kategori): while($row = mysqli_fetch_assoc($query_kategori)): ?>
                                <tr>
                                    <td><small class="text-muted fw-bold">#<?= $row['id_kategori']; ?></small></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_kategori']); ?></td>
                                    <td>
                                        <span class="badge px-3 py-2 <?= $row['jenis'] == 'Pemasukan' ? 'badge-pemasukan' : 'badge-pengeluaran'; ?>">
                                            <?= $row['jenis']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary border-0 btn-action-hover btn-edit-kategori" data-id="<?= $row['id_kategori']; ?>" data-nama="<?= htmlspecialchars($row['nama_kategori']); ?>" data-jenis="<?= $row['jenis']; ?>"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-action-hover btn-hapus-kategori" data-id="<?= $row['id_kategori']; ?>"><i class="fas fa-trash-alt"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pills-dompet" role="tabpanel" aria-labelledby="pills-dompet-tab">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Daftar Akun Dompet Keuangan</h5>
                    <button type="button" class="btn btn-sm text-white px-3 py-2 btn-action-hover" style="background-color: #006D5B; border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalTambahDompet">
                        <i class="fas fa-plus me-2"></i>Tambah Akun Dompet
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle custom-table" id="tableDompet" style="width:100%">
                            <thead class="table-light text-muted small text-uppercase">
                                <tr>
                                    <th width="15%">ID Dompet</th>
                                    <th width="45%">Nama Akun / Dompet</th>
                                    <th width="20%">Saldo Awal Terdaftar</th>
                                    <th width="20%" class="text-center">Aksi Manajerial</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($query_dompet): while($row = mysqli_fetch_assoc($query_dompet)): ?>
                                <tr>
                                    <td><small class="text-muted fw-bold">#<?= $row['id_dompet']; ?></small></td>
                                    <td class="fw-semibold text-dark"><i class="fas fa-wallet text-secondary me-2"></i><?= htmlspecialchars($row['nama_dompet']); ?></td>
                                    <td class="fw-bold text-dark">Rp <?= number_format($row['saldo_awal'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary border-0 btn-action-hover btn-edit-dompet" data-id="<?= $row['id_dompet']; ?>" data-nama="<?= htmlspecialchars($row['nama_dompet']); ?>" data-saldo="<?= $row['saldo_awal']; ?>"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-action-hover btn-hapus-dompet" data-id="<?= $row['id_dompet']; ?>"><i class="fas fa-trash-alt"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 bg-light py-3">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-tags text-muted me-2"></i>Tambah Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Nama Kategori</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-tag"></i></span>
                            <input type="text" class="form-control" name="nama_kategori" placeholder="Contoh: Makanan, Investasi, Gajian" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">Jenis Aktivitas Arus Kas</label>
                        <select class="form-select" name="jenis" required>
                            <option value="">-- Pilih Aliran Jenis --</option>
                            <option value="Pemasukan">Pemasukan (Income)</option>
                            <option value="Pengeluaran">Pengeluaran (Expense)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-sm btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_kategori" class="btn btn-sm text-white px-4 fw-semibold rounded-2" style="background-color: #006D5B;">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditKategori" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 bg-light py-3">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit text-muted me-2"></i>Ubah Informasi Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <input type="hidden" id="edit_id_kategori" name="id_kategori">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Nama Kategori</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-tag"></i></span>
                            <input type="text" class="form-control" id="edit_nama_kategori" name="nama_kategori" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">Jenis Aliran Aktivitas</label>
                        <select class="form-select" id="edit_jenis_kategori" name="jenis" required>
                            <option value="Pemasukan">Pemasukan</option>
                            <option value="Pengeluaran">Pengeluaran</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-sm btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_kategori" class="btn btn-sm text-white px-4 fw-semibold rounded-2" style="background-color: #006D5B;">Terapkan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahDompet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 bg-light py-3">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-wallet text-muted me-2"></i>Registrasi Akun Dompet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Nama Akun / Dompet</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-wallet"></i></span>
                            <input type="text" class="form-control" name="nama_dompet" placeholder="Contoh: Bank Jago, E-Wallet Dana, Cash" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">Saldo Awal</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold text-muted">Rp</span>
                            <input type="number" class="form-control" name="saldo_awal" value="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-sm btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_dompet" class="btn btn-sm text-white px-4 fw-semibold rounded-2" style="background-color: #006D5B;">Daftarkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditDompet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 bg-light py-3">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit text-muted me-2"></i>Ubah Neraca Akun Dompet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <input type="hidden" id="edit_id_dompet" name="id_dompet">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Nama Akun / Dompet</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-wallet"></i></span>
                            <input type="text" class="form-control" id="edit_nama_dompet" name="nama_dompet" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">Saldo Cadangan Awal</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold text-muted">Rp</span>
                            <input type="number" class="form-control" id="edit_saldo_dompet" name="saldo_awal" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-sm btn-secondary rounded-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_dompet" class="btn btn-sm text-white px-4 fw-semibold rounded-2" style="background-color: #006D5B;">Simpan Pembaharuan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function() {
    // Inisialisasi Plugin DataTables dengan Penyesuaian Antarmuka Bahasa Ringkas
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('#tableKategori, #tableDompet').DataTable({ 
            "pageLength": 5,
            "language": {
                "search": "Cari Data:",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "paginate": { "previous": "<i class='fas fa-angle-left'></i>", "next": "<i class='fas fa-angle-right'></i>" }
            }
        });
    }

    // Pemetaan Data Dinamis ke dalam Kolom Pengeditan Form Modal Kategori
    $(document).on('click', '.btn-edit-kategori', function() {
        $('#edit_id_kategori').val($(this).data('id')); 
        $('#edit_nama_kategori').val($(this).data('nama')); 
        $('#edit_jenis_kategori').val($(this).data('jenis'));
        new bootstrap.Modal('#modalEditKategori').show();
    });

    // Pemetaan Data Dinamis ke dalam Kolom Pengeditan Form Modal Akun Dompet
    $(document).on('click', '.btn-edit-dompet', function() {
        $('#edit_id_dompet').val($(this).data('id')); 
        $('#edit_nama_dompet').val($(this).data('nama')); 
        $('#edit_saldo_dompet').val($(this).data('saldo'));
        new bootstrap.Modal('#modalEditDompet').show();
    });
    
    // Integrasi Konfirmasi Penghapusan Interaktif Kategori via SweetAlert2
    $(document).on('click', '.btn-hapus-kategori', function() {
        let id = $(this).data('id');
        Swal.fire({ 
            title: 'Hapus Kategori ini?', 
            text: 'Seluruh konfigurasi label kategori bersangkutan akan dibersihkan secara konstan.',
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((r) => { 
            if(r.isConfirmed) window.location.href = 'index.php?page=master&action=hapus_kat&id=' + id; 
        }); 
    });

    // Integrasi Konfirmasi Penghapusan Interaktif Dompet via SweetAlert2
    $(document).on('click', '.btn-hapus-dompet', function() {
        let id = $(this).data('id');
        Swal.fire({ 
            title: 'Hapus Akun Dompet ini?', 
            text: 'Penghapusan instrumen dompet akan mematikan relasi rekam jejak saldo terikat.',
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((r) => { 
            if(r.isConfirmed) window.location.href = 'index.php?page=master&action=hapus_dompet&id=' + id; 
        }); 
    });

    // Tangkap Status Parameter URL untuk Notifikasi Toast/Alert Instan Sukses Operasi
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    if (status && typeof Swal !== 'undefined') {
        let titleAlert = 'Berhasil!';
        let textAlert = 'Data master berhasil diperbarui.';
        
        if (status.includes('sukses')) textAlert = 'Instrumen data baru berhasil ditambahkan ke database.';
        if (status.includes('edit')) textAlert = 'Perubahan informasi berhasil disimpan secara aman.';
        if (status.includes('hapus')) textAlert = 'Komponen data terpilih berhasil dihapus dari sistem.';

        Swal.fire({ icon: 'success', title: titleAlert, text: textAlert, confirmButtonColor: '#006D5B' }).then(() => {
            window.history.replaceState({}, document.title, "index.php?page=master");
        });
    }
});
</script>