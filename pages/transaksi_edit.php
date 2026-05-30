<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_user_login = (int)($_SESSION['user_id'] ?? 0);
if ($id_user_login <= 0) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$id_transaksi = (int)($_GET['id'] ?? 0);
if ($id_transaksi <= 0) {
    echo "<script>window.location.href='index.php?page=transaksi&status=gagal';</script>";
    exit;
}

function gf_clean($koneksi, $value) {
    return mysqli_real_escape_string($koneksi, trim((string)$value));
}

if (isset($_POST['update_transaksi'])) {
    $tanggal = gf_clean($koneksi, $_POST['tanggal'] ?? date('Y-m-d'));
    $id_kategori = (int)($_POST['id_kategori'] ?? 0);
    $id_dompet = (int)($_POST['id_dompet'] ?? 0);
    $jumlah = (float)($_POST['jumlah'] ?? 0);
    $keterangan = gf_clean($koneksi, $_POST['keterangan'] ?? '');

    $cek_kategori = mysqli_query($koneksi, "SELECT id_kategori FROM kategori WHERE id_kategori = '$id_kategori' AND user_id = '$id_user_login'");
    $cek_dompet = mysqli_query($koneksi, "SELECT id_dompet FROM dompet WHERE id_dompet = '$id_dompet' AND user_id = '$id_user_login'");

    if ($id_kategori <= 0 || $id_dompet <= 0 || $jumlah <= 0 || mysqli_num_rows($cek_kategori) === 0 || mysqli_num_rows($cek_dompet) === 0) {
        $pesan = "Data transaksi belum lengkap atau tidak valid.";
        $tipe_pesan = "danger";
    } else {
        $query_update = "
            UPDATE transaksi
            SET id_kategori = '$id_kategori',
                id_dompet = '$id_dompet',
                tanggal = '$tanggal',
                jumlah = '$jumlah',
                keterangan = '$keterangan'
            WHERE id_transaksi = '$id_transaksi'
              AND user_id = '$id_user_login'
        ";

        if (mysqli_query($koneksi, $query_update)) {
            echo "<script>window.location.href='index.php?page=transaksi&status=sukses';</script>";
            exit;
        }

        $pesan = "Gagal memperbarui transaksi: " . mysqli_error($koneksi);
        $tipe_pesan = "danger";
    }
}

$query_transaksi = mysqli_query($koneksi, "
    SELECT t.*, k.jenis
    FROM transaksi t
    LEFT JOIN kategori k ON t.id_kategori = k.id_kategori
    WHERE t.id_transaksi = '$id_transaksi'
      AND t.user_id = '$id_user_login'
    LIMIT 1
");

if (!$query_transaksi || mysqli_num_rows($query_transaksi) === 0) {
    echo "<script>window.location.href='index.php?page=transaksi&status=gagal';</script>";
    exit;
}

$transaksi = mysqli_fetch_assoc($query_transaksi);
$kategori = mysqli_query($koneksi, "SELECT id_kategori, nama_kategori, jenis FROM kategori WHERE user_id = '$id_user_login' ORDER BY jenis, nama_kategori");
$dompet = mysqli_query($koneksi, "SELECT id_dompet, nama_dompet FROM dompet WHERE user_id = '$id_user_login' ORDER BY nama_dompet");
?>

<div class="container-fluid px-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">Edit Transaksi</h3>
            <p class="text-muted small mb-0">Perbarui tanggal, kategori, dompet, nominal, dan keterangan transaksi.</p>
        </div>
        <a href="index.php?page=transaksi" class="btn btn-sm btn-outline-secondary fw-semibold">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <?php if (!empty($pesan ?? '')): ?>
        <div class="alert alert-<?= htmlspecialchars($tipe_pesan) ?> shadow-sm"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars(date('Y-m-d', strtotime($transaksi['tanggal']))) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Nominal</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="jumlah" class="form-control" value="<?= htmlspecialchars((string)(int)$transaksi['jumlah']) ?>" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Kategori</label>
                        <select name="id_kategori" class="form-select" required>
                            <?php while ($row = mysqli_fetch_assoc($kategori)): ?>
                                <option value="<?= $row['id_kategori'] ?>" <?= (int)$row['id_kategori'] === (int)$transaksi['id_kategori'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['nama_kategori']) ?> - <?= htmlspecialchars($row['jenis']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Dompet/Akun</label>
                        <select name="id_dompet" class="form-select" required>
                            <?php while ($row = mysqli_fetch_assoc($dompet)): ?>
                                <option value="<?= $row['id_dompet'] ?>" <?= (int)$row['id_dompet'] === (int)$transaksi['id_dompet'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['nama_dompet']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3"><?= htmlspecialchars($transaksi['keterangan'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <a href="index.php?page=transaksi" class="btn btn-light fw-semibold">Batal</a>
                        <button type="submit" name="update_transaksi" class="btn text-white fw-semibold" style="background:#006D5B;">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
