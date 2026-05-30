<?php
$id_user_login = $_SESSION['user_id'];

// ── HAPUS ──
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['id']);
    $ok = mysqli_query($koneksi, "DELETE FROM transaksi WHERE id_transaksi='$id_hapus' AND user_id='$id_user_login'");
    echo "<script>window.location.href='index.php?page=transaksi&status=" . ($ok ? 'hapus_sukses' : 'gagal') . "';</script>"; exit;
}

// ── DATA DROPDOWN ──
$query_kat = mysqli_query($koneksi, "SELECT id_kategori, nama_kategori, jenis FROM kategori WHERE user_id='$id_user_login'");
$kategori_pemasukan = []; $kategori_pengeluaran = [];
while ($row = mysqli_fetch_assoc($query_kat)) {
    if ($row['jenis'] == 'Pemasukan') $kategori_pemasukan[] = $row;
    else $kategori_pengeluaran[] = $row;
}
$query_dompet = mysqli_query($koneksi, "SELECT id_dompet, nama_dompet FROM dompet WHERE user_id='$id_user_login'");
$data_dompet_array = [];
while ($d = mysqli_fetch_assoc($query_dompet)) $data_dompet_array[] = $d;

// ── DATA TABEL ──
$result_tabel = mysqli_query($koneksi,
    "SELECT t.*, k.nama_kategori, k.jenis, d.nama_dompet
     FROM transaksi t
     JOIN kategori k ON t.id_kategori = k.id_kategori
     JOIN dompet d ON t.id_dompet = d.id_dompet
     WHERE t.user_id='$id_user_login'
     ORDER BY t.tanggal DESC, t.id_transaksi DESC");
?>

<style>
/* ── TRANSAKSI PAGE ── */
.trx-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.trx-title h4 {
    font-size: 1.1rem;
    font-weight: 800;
    margin: 0 0 3px;
    color: var(--text-primary);
}
.trx-title p {
    font-size: .8rem;
    color: var(--text-muted);
    margin: 0;
}
.trx-actions {
    display: flex;
    gap: 10px;
    flex-shrink: 0;
}
.btn-trx {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 16px;
    border-radius: var(--radius-md);
    font-family: var(--font);
    font-size: .835rem;
    font-weight: 700;
    cursor: pointer;
    border: none;
    white-space: nowrap;
    transition: all var(--transition);
    min-height: 40px;
}
.btn-trx-scan {
    background: #FEF3C7;
    color: #92400E;
    border: 1.5px solid #FCD34D;
}
.btn-trx-scan:hover { background: #FDE68A; }
.btn-trx-add {
    background: var(--brand);
    color: #fff;
}
.btn-trx-add:hover { background: var(--brand-dark); box-shadow: 0 4px 12px rgba(0,168,123,.3); }

/* Table */
.trx-table-wrap {
    border-radius: var(--radius-lg);
    overflow: hidden;
    border: 1px solid var(--border);
    background: var(--surface);
}
.trx-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.trx-table thead th {
    background: var(--surface-2);
    color: var(--text-muted);
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .7px;
    padding: 11px 14px;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
.trx-table tbody td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    color: var(--text-primary);
}
.trx-table tbody tr:last-child td { border-bottom: none; }
.trx-table tbody tr:hover td { background: var(--surface-2); }

/* Amount cell */
.trx-amount {
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    font-size: .9rem;
    white-space: nowrap;
}
.trx-amount.income { color: var(--success); }
.trx-amount.expense { color: var(--danger); }

/* ID cell */
.trx-id {
    font-size: .72rem;
    color: var(--text-muted);
    font-family: 'Fira Code', monospace;
    white-space: nowrap;
}

/* Date cell */
.trx-date {
    font-size: .82rem;
    color: var(--text-secondary);
    white-space: nowrap;
}

/* Keterangan truncate */
.trx-keterangan {
    max-width: 160px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--text-secondary);
    font-size: .82rem;
}

/* Action buttons */
.trx-btn-edit,
.trx-btn-del {
    width: 30px;
    height: 30px;
    border-radius: var(--radius-sm);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .8rem;
    cursor: pointer;
    border: none;
    transition: background var(--transition), color var(--transition), transform var(--transition);
    text-decoration: none;
}
.trx-btn-edit {
    background: #DBEAFE;
    color: #1D4ED8;
}
.trx-btn-edit:hover { background: #BFDBFE; transform: scale(1.1); }
.trx-btn-del {
    background: #FEE2E2;
    color: #B91C1C;
}
.trx-btn-del:hover { background: #FECACA; transform: scale(1.1); }

/* Badge jenis */
.jenis-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 50px;
    font-size: .72rem;
    font-weight: 700;
    white-space: nowrap;
}
.jenis-badge.income  { background: #DCFCE7; color: #15803D; }
.jenis-badge.expense { background: #FEE2E2; color: #B91C1C; }

/* ── MODAL UPLOAD ZONA ── */
.upload-zona {
    border: 2px dashed var(--border);
    border-radius: var(--radius-lg);
    min-height: 160px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    background: var(--surface-2);
    transition: border-color var(--transition), background var(--transition);
    padding: 24px;
    text-align: center;
}
.upload-zona:hover { border-color: var(--brand); background: var(--brand-light); }
.upload-zona .upload-icon {
    font-size: 2.2rem;
    color: var(--text-muted);
    margin-bottom: 10px;
    transition: color var(--transition);
}
.upload-zona:hover .upload-icon { color: var(--brand); }

/* ── MODAL FORM FIELDS ── */
.modal-field { margin-bottom: 14px; }
.modal-field label {
    display: block;
    font-size: .74rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .4px;
    margin-bottom: 5px;
}
.modal-field .form-control,
.modal-field .form-select {
    min-height: 44px;
    font-size: .88rem;
}

/* ── MOBILE RESPONSIVE ── */
@media (max-width: 640px) {
    .trx-header {
        flex-direction: column;
        gap: 12px;
    }
    .trx-actions {
        width: 100%;
        flex-direction: column;
    }
    .btn-trx {
        width: 100%;
        justify-content: center;
    }

    /* Hide less critical columns on small screen */
    .trx-col-id,
    .trx-col-keterangan,
    .trx-col-dompet { display: none; }

    .trx-keterangan { max-width: 100px; }
    .trx-table thead th,
    .trx-table tbody td { padding: 10px 10px; }
}
</style>

<!-- ── HEADER ── -->
<div class="trx-header">
    <div class="trx-title">
        <h4>Data Transaksi</h4>
        <p>Catat dan pantau seluruh arus kas masuk dan keluar Anda.</p>
    </div>
    <div class="trx-actions">
        <button type="button" class="btn-trx btn-trx-scan"
                data-bs-toggle="modal" data-bs-target="#modalScanAI">
            <i class="fas fa-camera"></i>Scan Nota/Struk
        </button>
        <button type="button" class="btn-trx btn-trx-add"
                data-bs-toggle="modal" data-bs-target="#modalTambahTransaksi">
            <i class="fas fa-plus"></i>Tambah Transaksi
        </button>
    </div>
</div>

<!-- ── TABEL TRANSAKSI ── -->
<div class="trx-table-wrap">
    <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
        <table class="trx-table" id="tableTransaksi" style="width:100%">
            <thead>
                <tr>
                    <th class="trx-col-id">#</th>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th class="trx-col-dompet">Dompet</th>
                    <th class="trx-col-keterangan">Keterangan</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th style="text-align:center; width:80px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($t = mysqli_fetch_assoc($result_tabel)): ?>
                <tr>
                    <td class="trx-col-id">
                        <span class="trx-id">#<?= $t['id_transaksi'] ?></span>
                    </td>
                    <td>
                        <span class="trx-date"><?= date('d M Y', strtotime($t['tanggal'])) ?></span>
                    </td>
                    <td>
                        <span style="font-weight:600; font-size:.85rem;"><?= htmlspecialchars($t['nama_kategori']) ?></span>
                    </td>
                    <td class="trx-col-dompet">
                        <span style="font-size:.82rem; color:var(--text-secondary);"><?= htmlspecialchars($t['nama_dompet']) ?></span>
                    </td>
                    <td class="trx-col-keterangan">
                        <span class="trx-keterangan" title="<?= htmlspecialchars($t['keterangan']) ?>">
                            <?= htmlspecialchars($t['keterangan'] ?: '—') ?>
                        </span>
                    </td>
                    <td>
                        <span class="jenis-badge <?= $t['jenis'] == 'Pemasukan' ? 'income' : 'expense' ?>">
                            <i class="fas fa-<?= $t['jenis'] == 'Pemasukan' ? 'arrow-down' : 'arrow-up' ?>"></i>
                            <?= $t['jenis'] ?>
                        </span>
                    </td>
                    <td>
                        <span class="trx-amount <?= $t['jenis'] == 'Pemasukan' ? 'income' : 'expense' ?>">
                            <?= $t['jenis'] == 'Pemasukan' ? '+' : '−' ?> Rp <?= number_format($t['jumlah'], 0, ',', '.') ?>
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <div style="display:flex; justify-content:center; gap:6px;">
                            <a href="index.php?page=transaksi_edit&id=<?= $t['id_transaksi'] ?>"
                               class="trx-btn-edit" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            <button type="button"
                                    class="trx-btn-del btn-hapus"
                                    data-id="<?= $t['id_transaksi'] ?>"
                                    title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ══════════════════════════════════════
     MODAL: SCAN AI
════════════════════════════════════════ -->
<div class="modal fade" id="modalScanAI" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-wand-magic-sparkles me-2" style="color:var(--warning);"></i>Scan Struk dengan AI
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formScanAI" enctype="multipart/form-data">
                <div class="modal-body">
                    <p style="font-size:.82rem; color:var(--text-muted); margin-bottom:16px;">
                        Unggah foto nota fisik. AI akan mengenali tanggal, nominal, dan nama toko secara otomatis.
                    </p>
                    <label for="fileStruk" class="upload-zona w-100">
                        <input type="file" id="fileStruk" name="image_struk" accept="image/*"
                               class="d-none" onchange="previewScanImage(this)">
                        <div id="dropzoneContent">
                            <div class="upload-icon"><i class="fas fa-camera-retro"></i></div>
                            <div style="font-weight:700; font-size:.875rem; margin-bottom:4px;">Ambil Gambar / Pilih File</div>
                            <div style="font-size:.78rem; color:var(--text-muted);">JPG, JPEG, PNG</div>
                        </div>
                        <img id="imgPreviewScan" class="img-fluid d-none rounded-3"
                             style="max-height:150px; object-fit:contain;">
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnMulaiAI" class="btn btn-warning btn-sm fw-bold text-dark px-4">
                        <i class="fas fa-robot me-1"></i>Mulai Analisis AI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════
     MODAL: TAMBAH MANUAL
════════════════════════════════════════ -->
<div class="modal fade" id="modalTambahTransaksi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="color:var(--brand);">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Catat Transaksi Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses/tambah_transaksi.php" method="POST" id="formManualTransaksi">
                <div class="modal-body">

                    <div class="modal-field">
                        <label>Tanggal Transaksi</label>
                        <input type="date" class="form-control" name="tanggal"
                               id="input_tanggal" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="row g-2">
                        <div class="col-6 modal-field">
                            <label>Jenis Transaksi</label>
                            <select class="form-select" id="jenis_transaksi" name="jenis" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="Pemasukan">Pemasukan</option>
                                <option value="Pengeluaran">Pengeluaran</option>
                            </select>
                        </div>
                        <div class="col-6 modal-field">
                            <label>Sub-Kategori</label>
                            <select class="form-select" id="sub_kategori" name="id_kategori" required disabled>
                                <option value="">-- Pilih Jenis Dulu --</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-field">
                        <label>Dompet / Akun</label>
                        <select class="form-select" name="id_dompet" required>
                            <option value="">-- Pilih Dompet --</option>
                            <?php foreach($data_dompet_array as $d): ?>
                            <option value="<?= $d['id_dompet'] ?>"><?= htmlspecialchars($d['nama_dompet']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="modal-field">
                        <label>Jumlah (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold">Rp</span>
                            <input type="number" class="form-control" name="jumlah"
                                   id="input_jumlah" placeholder="Contoh: 25000" required min="1">
                        </div>
                    </div>

                    <div class="modal-field" style="margin-bottom:0;">
                        <label>Keterangan <span style="font-weight:400; text-transform:none;">(opsional)</span></label>
                        <textarea class="form-control" name="keterangan" id="input_keterangan"
                                  rows="2" placeholder="Contoh: Makan siang bareng teman"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_transaksi"
                            class="btn btn-sm fw-semibold text-white px-4"
                            style="background:var(--brand);">
                        <i class="fas fa-floppy-disk me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewScanImage(input) {
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = function(e) {
            let img = document.getElementById('imgPreviewScan');
            img.src = e.target.result;
            img.classList.remove('d-none');
            document.getElementById('dropzoneContent').classList.add('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener("DOMContentLoaded", function () {

    // ── Status Alerts ──
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    if (status === 'sukses' || status === 'hapus_sukses') {
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data transaksi berhasil diperbarui.', confirmButtonColor: 'var(--brand)' })
            .then(() => window.history.replaceState({}, '', 'index.php?page=transaksi'));
    } else if (status === 'gagal') {
        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Operasi database gagal diproses.', confirmButtonColor: 'var(--brand)' })
            .then(() => window.history.replaceState({}, '', 'index.php?page=transaksi'));
    }

    // ── Konfirmasi Hapus ──
    document.querySelectorAll('.btn-hapus').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Hapus Transaksi?',
                text: 'Data yang terhapus tidak dapat dipulihkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#718096',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(r => {
                if (r.isConfirmed) window.location.href = `index.php?page=transaksi&aksi=hapus&id=${id}`;
            });
        });
    });

    // ── Dropdown Berantai (Jenis → Sub-Kategori) ──
    const dataPemasukan   = <?= json_encode($kategori_pemasukan) ?>;
    const dataPengeluaran = <?= json_encode($kategori_pengeluaran) ?>;
    const dataDompet      = <?= json_encode($data_dompet_array) ?>;
    const selectJenis = document.getElementById('jenis_transaksi');
    const selectSub   = document.getElementById('sub_kategori');

    selectJenis.addEventListener('change', function () {
        selectSub.innerHTML = '<option value="">-- Pilih Sub-Kategori --</option>';
        const list = this.value === 'Pemasukan' ? dataPemasukan : this.value === 'Pengeluaran' ? dataPengeluaran : [];
        if (list.length) {
            selectSub.disabled = false;
            list.forEach(item => {
                const o = document.createElement('option');
                o.value = item.id_kategori;
                o.textContent = item.nama_kategori;
                selectSub.appendChild(o);
            });
        } else {
            selectSub.disabled = true;
        }
    });

    // ── DataTables ──
    if ($.fn.DataTable) {
        $('#tableTransaksi').DataTable({
            responsive: false,
            autoWidth: false,
            pageLength: 10,
            order: [],
            language: {
                search: "Cari:",
                searchPlaceholder: "Cari transaksi...",
                lengthMenu: "Tampilkan _MENU_",
                info: "_START_–_END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(dari _MAX_)",
                zeroRecords: "Tidak ditemukan",
                paginate: { first: "«", last: "»", next: "›", previous: "‹" }
            },
            dom: '<"trx-dt-top d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3"lf>rt<"trx-dt-bottom d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3"ip>'
        });
    }

    // ── SCAN AI ──
    const formScanAI = document.getElementById('formScanAI');
    if (formScanAI) {
        formScanAI.addEventListener('submit', function (e) {
            e.preventDefault();
            const fileInput = document.getElementById('fileStruk');
            if (!fileInput || fileInput.files.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Pilih File', text: 'Silakan pilih foto struk terlebih dahulu.' });
                return;
            }
            const btn = document.getElementById('btnMulaiAI');
            const origHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menganalisis...';

            const fd = new FormData();
            fd.append('image_struk', fileInput.files[0]);

            fetch('config/proses_scan.php', { method: 'POST', body: fd })
                .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = origHTML;

                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalScanAI'))?.hide();

                        const opsiKat   = dataPengeluaran.map(k => `<option value="${k.id_kategori}">${k.nama_kategori}</option>`).join('');
                        const opsiDompt = dataDompet.map(d => `<option value="${d.id_dompet}">${d.nama_dompet}</option>`).join('');

                        Swal.fire({
                            title: '<i class="fas fa-robot me-2" style="color:var(--brand)"></i>Konfirmasi Hasil AI',
                            html: `
                                <div class="text-start" style="font-size:.875rem;">
                                    <p class="text-muted small mb-3">Periksa dan koreksi hasil deteksi AI sebelum disimpan.</p>
                                    <div class="mb-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Nama Toko / Mitra</label>
                                        <input type="text" id="swal-toko" class="form-control form-control-sm" value="${data.data.toko}">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Tanggal</label>
                                        <input type="date" id="swal-tanggal" class="form-control form-control-sm" value="${data.data.tanggal}">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Nominal (Rp)</label>
                                        <input type="number" id="swal-nominal" class="form-control form-control-sm" value="${data.data.nominal}">
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-muted mb-1">Kategori</label>
                                            <select id="swal-kategori" class="form-select form-select-sm">${opsiKat}</select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-muted mb-1">Dompet</label>
                                            <select id="swal-dompet" class="form-select form-select-sm">${opsiDompt}</select>
                                        </div>
                                    </div>
                                </div>`,
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonColor: 'var(--brand)',
                            cancelButtonColor: '#718096',
                            confirmButtonText: '<i class="fas fa-floppy-disk me-1"></i>Simpan',
                            cancelButtonText: 'Batal',
                            showLoaderOnConfirm: true,
                            preConfirm: () => ({
                                action: 'simpan_database',
                                toko: document.getElementById('swal-toko').value,
                                tanggal: document.getElementById('swal-tanggal').value,
                                nominal: document.getElementById('swal-nominal').value,
                                id_kategori: document.getElementById('swal-kategori').value,
                                id_dompet: document.getElementById('swal-dompet').value
                            }),
                            allowOutsideClick: () => !Swal.isLoading()
                        }).then(result => {
                            if (result.isConfirmed) {
                                fetch('config/proses_scan.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: new URLSearchParams(result.value)
                                })
                                .then(r => r.json())
                                .then(rd => {
                                    if (rd.success) {
                                        Swal.fire({ icon: 'success', title: 'Tersimpan!', text: rd.message, confirmButtonColor: 'var(--brand)' })
                                            .then(() => location.reload());
                                    } else {
                                        Swal.fire({ icon: 'error', title: 'Gagal Simpan', text: rd.message });
                                    }
                                })
                                .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Koneksi ke database gagal.' }));
                            }
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Analisis Gagal', text: data.message });
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = origHTML;
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Koneksi AI terputus atau format file tidak didukung.' });
                });
        });
    }
});
</script>
