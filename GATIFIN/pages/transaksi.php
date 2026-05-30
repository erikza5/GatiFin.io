<?php
// 1. Ambil ID Pengguna Aktif dari Sesi Login
$id_user_login = $_SESSION['user_id'];

// ==========================================
// 2. PROSES HAPUS TRANSAKSI (AKSI DELETE)
// ==========================================
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id_transaksi_hapus = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    $query_hapus = "DELETE FROM transaksi WHERE id_transaksi = '$id_transaksi_hapus' AND user_id = '$id_user_login'";
    
    if (mysqli_query($koneksi, $query_hapus)) {
        echo "<script>window.location.href='index.php?page=transaksi&status=hapus_sukses';</script>";
        exit;
    } else {
        echo "<script>window.location.href='index.php?page=transaksi&status=gagal';</script>";
        exit;
    }
}

// ==========================================
// 3. AMBIL DATA KATEGORI & DOMPET FOR DROPDOWN
// ==========================================
$query_kat = mysqli_query($koneksi, "SELECT id_kategori, nama_kategori, jenis FROM kategori WHERE user_id = '$id_user_login'");
$kategori_pemasukan = [];
$kategori_pengeluaran = [];
while ($row = mysqli_fetch_assoc($query_kat)) {
    if ($row['jenis'] == 'Pemasukan') {
        $kategori_pemasukan[] = $row;
    } else {
        $kategori_pengeluaran[] = $row;
    }
}

$query_dompet = mysqli_query($koneksi, "SELECT id_dompet, nama_dompet FROM dompet WHERE user_id = '$id_user_login'");
$data_dompet_array = [];
while ($d = mysqli_fetch_assoc($query_dompet)) {
    $data_dompet_array[] = $d;
}

// ==========================================
// 4. AMBIL DAFTAR TRANSAKSI (UNTUK TABEL)
// ==========================================
$query_tabel = "SELECT t.*, k.nama_kategori, k.jenis, d.nama_dompet 
                FROM transaksi t
                JOIN kategori k ON t.id_kategori = k.id_kategori
                JOIN dompet d ON t.id_dompet = d.id_dompet
                WHERE t.user_id = '$id_user_login'
                ORDER BY t.tanggal DESC, t.id_transaksi DESC";
$result_tabel = mysqli_query($koneksi, $query_tabel);
?>

<div class="container-fluid px-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">Data Transaksi</h3>
            <p class="text-muted small mb-0">Catat dan pantau seluruh arus kas masuk dan keluar Anda.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-warning fw-bold btn-sm px-3 py-2 text-dark" data-bs-toggle="modal" data-bs-target="#modalScanAI">
                <i class="fas fa-camera me-2"></i>Scan Nota/Struk
            </button>
            <button type="button" class="btn text-white fw-semibold btn-sm px-3 py-2" style="background-color: #006D5B; border: none;" data-bs-toggle="modal" data-bs-target="#modalTambahTransaksi">
                <i class="fas fa-plus me-2"></i>Tambah Transaksi
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tableTransaksi" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Dompet/Akun</th>
                            <th>Keterangan</th>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                            <th class="text-center" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($t = mysqli_fetch_assoc($result_tabel)): ?>
                            <tr>
                                <td><small class="text-muted">#<?= $t['id_transaksi']; ?></small></td>
                                <td><?= date('Y-m-d', strtotime($t['tanggal'])); ?></td>
                                <td><?= htmlspecialchars($t['nama_kategori']); ?></td>
                                <td><?= htmlspecialchars($t['nama_dompet']); ?></td>
                                <td><?= htmlspecialchars($t['keterangan']); ?></td>
                                <td>
                                    <span class="badge <?= $t['jenis'] == 'Pemasukan' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?= $t['jenis']; ?>
                                    </span>
                                </td>
                                <td class="fw-bold <?= $t['jenis'] == 'Pemasukan' ? 'text-success' : 'text-danger'; ?>">
                                    Rp <?= number_format($t['jumlah'], 0, ',', '.'); ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="index.php?page=transaksi_edit&id=<?= $t['id_transaksi']; ?>" class="btn btn-sm btn-outline-primary border-0" title="Ubah Data">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-hapus" 
                                                data-id="<?= $t['id_transaksi']; ?>" 
                                                title="Hapus Data">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalScanAI" tabindex="-1" aria-labelledby="modalScanAILabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-light py-3">
                <h5 class="modal-title fw-bold text-dark" id="modalScanAILabel">
                    <i class="fas fa-expand text-warning me-2"></i>Scan Struk Menggunakan AI
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formScanAI" enctype="multipart/form-data">
                <div class="modal-body p-4 text-center">
                    <p class="text-muted small mb-4">Unggah foto nota fisik Anda. Kecerdasan Buatan (AI) akan secara otomatis mengenali data tanggal transaksi, nominal, dan nama toko.</p>
                    
                    <label for="fileStruk" class="w-100 py-5 border rounded-3 d-flex flex-column align-items-center justify-content-center" style="border-style: dashed !important; background: #f8fafc; cursor: pointer; min-height: 180px;">
                        <input type="file" id="fileStruk" name="image_struk" accept="image/*" class="d-none" onchange="previewScanImage(this)">
                        <div id="dropzoneContent">
                            <i class="fas fa-camera text-muted mb-3" style="font-size: 2.5rem;"></i>
                            <h6 class="fw-bold text-dark mb-1">Ambil Gambar / Pilih File</h6>
                            <span class="text-muted small">Mendukung JPG, JPEG, PNG</span>
                        </div>
                        <img id="imgPreviewScan" class="img-fluid d-none rounded-3 shadow-sm" style="max-height: 160px; object-fit: contain;">
                    </label>
                </div>
                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnMulaiAI" class="btn btn-sm btn-warning fw-bold px-4 text-dark">
                        <i class="fas fa-robot me-1"></i> Mulai Analisis AI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahTransaksi" tabindex="-1" aria-labelledby="modalTambahTransaksiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-light py-3">
                <h5 class="modal-title fw-bold" id="modalTambahTransaksiLabel" style="color: #006D5B;">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Catat Transaksi Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="proses/tambah_transaksi.php" method="POST" id="formManualTransaksi">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Tanggal Transaksi</label>
                        <input type="date" class="form-control" name="tanggal" id="input_tanggal" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Jenis Transaksi</label>
                        <select class="form-select" id="jenis_transaksi" name="jenis" required>
                            <option value="">-- Pilih Jenis Transaksi --</option>
                            <option value="Pemasukan">Pemasukan</option>
                            <option value="Pengeluaran">Pengeluaran</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Sub-Kategori</label>
                        <select class="form-select" id="sub_kategori" name="id_kategori" required disabled>
                            <option value="">-- Pilih Jenis Terlebih Dahulu --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Simpan / Ambil Dari Dompet</label>
                        <select class="form-select" name="id_dompet" required>
                            <option value="">-- Pilih Dompet/Akun --</option>
                            <?php foreach($data_dompet_array as $d): ?>
                                <option value="<?= $d['id_dompet']; ?>"><?= htmlspecialchars($d['nama_dompet']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Jumlah Uang</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold text-muted">Rp</span>
                            <input type="number" class="form-control" name="jumlah" id="input_jumlah" placeholder="Contoh: 25000" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Keterangan</label>
                        <textarea class="form-control" name="keterangan" id="input_keterangan" rows="3" placeholder="Contoh: Makan siang"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_transaksi" class="btn btn-sm text-white px-4 fw-semibold" style="background-color: #006D5B;">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Fungsi Pratinjau Gambar Nota Saat Dipilih
function previewScanImage(input) {
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imgPreviewScan').src = e.target.result;
            document.getElementById('imgPreviewScan').classList.remove('d-none');
            document.getElementById('dropzoneContent').classList.add('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener("DOMContentLoaded", function() {
    // Penanganan Alur Feedback URL Parameter Status
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    if (status === 'sukses' || status === 'hapus_sukses') {
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data transaksi berhasil diperbarui.', confirmButtonColor: '#006D5B' }).then(() => {
            window.history.replaceState({}, document.title, "index.php?page=transaksi");
        });
    } else if (status === 'gagal') {
        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Operasi database gagal diproses.', confirmButtonColor: '#006D5B' }).then(() => {
            window.history.replaceState({}, document.title, "index.php?page=transaksi");
        });
    }

    // Tombol Aksi Hapus Baris Transaksi
    document.querySelectorAll('.btn-hapus').forEach(button => {
        button.addEventListener('click', function() {
            const idTransaksi = this.getAttribute('data-id');
            Swal.fire({
                title: 'Hapus Transaksi?',
                text: "Data yang terhapus tidak dapat dipulihkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#718096',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `index.php?page=transaksi&aksi=hapus&id=${idTransaksi}`;
                }
            });
        });
    });

    // Inisialisasi Data Array untuk Dropdown Berantai
    const dataPemasukan = <?= json_encode($kategori_pemasukan); ?>;
    const dataPengeluaran = <?= json_encode($kategori_pengeluaran); ?>;
    const dataDompet = <?= json_encode($data_dompet_array); ?>;
    const selectJenis = document.getElementById('jenis_transaksi');
    const selectSub = document.getElementById('sub_kategori');

    function updateDropdownKategori() {
        selectSub.innerHTML = '<option value="">-- Pilih Sub-Kategori --</option>';
        let listData = (selectJenis.value === 'Pemasukan') ? dataPemasukan : ((selectJenis.value === 'Pengeluaran') ? dataPengeluaran : []);
        
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
    selectJenis.addEventListener('change', updateDropdownKategori);

    // ========================================================
    // ✨ PROSES PARSING STRUK VIA GEMINI AI (FIXED METHOD)
    // ========================================================
    const formScanAI = document.getElementById('formScanAI');
    if(formScanAI) {
        formScanAI.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let fileInput = document.getElementById('fileStruk');
            if (!fileInput || fileInput.files.length === 0) {
                Swal.fire({ icon: 'error', title: 'Berkas Kosong', text: 'Silakan pilih file atau ambil foto struk belanja Anda!' });
                return;
            }

            let btn = document.getElementById('btnMulaiAI');
            let originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menguraikan Nota...';

            // Memetakan objek file secara manual ke FormData agar konsisten terbaca oleh $_FILES['image_struk']
            let formData = new FormData();
            formData.append('image_struk', fileInput.files[0]); 

            fetch('config/proses_scan.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error("HTTP Error " + response.status);
                return response.json();
            })
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;

                if (data.success) {
                    let modalScanEl = document.getElementById('modalScanAI');
                    let modalInstance = bootstrap.Modal.getInstance(modalScanEl);
                    if (modalInstance) modalInstance.hide();

                    let opsiKategoriHtml = dataPengeluaran.map(k => `<option value="${k.id_kategori}">${k.nama_kategori}</option>`).join('');
                    let opsiDompetHtml = dataDompet.map(d => `<option value="${d.id_dompet}">${d.nama_dompet}</option>`).join('');

                    // Menampilkan Jendela Konfirmasi SweetAlert2 berisi data hasil pembacaan AI
                    Swal.fire({
                        title: 'Konfirmasi Catatan Struk (AI)',
                        html: `
                            <div class="text-start fs-6 p-1">
                                <p class="text-muted small mb-3">Silakan periksa atau ubah hasil deteksi otomatis AI sebelum dibukukan ke dalam sistem.</p>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold mb-1 text-muted">Nama Mitra / Toko</label>
                                    <input type="text" id="swal-toko" class="form-control form-control-sm" value="${data.data.toko}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold mb-1 text-muted">Tanggal Transaksi</label>
                                    <input type="date" id="swal-tanggal" class="form-control form-control-sm" value="${data.data.tanggal}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold mb-1 text-muted">Total Nominal Pengeluaran (Rp)</label>
                                    <input type="number" id="swal-nominal" class="form-control form-control-sm" value="${data.data.nominal}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold mb-1 text-muted">Pilih Kategori Kategori</label>
                                    <select id="swal-kategori" class="form-select form-select-sm">
                                        ${opsiKategoriHtml}
                                    </select>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label small fw-bold mb-1 text-muted">Metode Pembayaran (Dompet)</label>
                                    <select id="swal-dompet" class="form-select form-select-sm">
                                        ${opsiDompetHtml}
                                    </select>
                                </div>
                            </div>
                        `,
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#006D5B',
                        cancelButtonColor: '#718096',
                        confirmButtonText: '<i class="fas fa-check-circle me-1"></i> Simpan Transaksi',
                        cancelButtonText: 'Batal',
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            return {
                                action: 'simpan_database', 
                                toko: document.getElementById('swal-toko').value,
                                tanggal: document.getElementById('swal-tanggal').value,
                                nominal: document.getElementById('swal-nominal').value,
                                id_kategori: document.getElementById('swal-kategori').value,
                                id_dompet: document.getElementById('swal-dompet').value
                            }
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Mengirimkan data final hasil review ke alur penyimpanan database di proses_scan.php
                            fetch('config/proses_scan.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: new URLSearchParams(result.value)
                            })
                            .then(res => res.json())
                            .then(resData => {
                                if (resData.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: resData.message,
                                        confirmButtonColor: '#006D5B'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: resData.message });
                                }
                            })
                            .catch(() => {
                                Swal.fire({ icon: 'error', title: 'Sistem Terkendala', text: 'Gagal menghubungkan data transaksi ke database.' });
                            });
                        }
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Analisis Gagal', text: data.message });
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                Swal.fire({ icon: 'error', title: 'Sistem Error', text: 'Koneksi AI terputus atau respon file tidak didukung.' });
            });
        });
    }

    if ($.fn.DataTable) {
        $('#tableTransaksi').DataTable({ "language": { "search": "Cari:" } });
    }
});
</script>
