<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../config/koneksi.php';
$id_saya = $_SESSION['user_id'] ?? null;

if (isset($_POST['update_saya'])) {
    $username  = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $email     = mysqli_real_escape_string($koneksi, $_POST['email']);
    $pekerjaan = mysqli_real_escape_string($koneksi, $_POST['pekerjaan']);
    $usia      = (int)$_POST['usia'];
    $foto_sql  = "";
    if (!empty($_FILES['foto_profil']['name'])) {
        $nama_file = uniqid() . '_' . basename($_FILES['foto_profil']['name']);
        if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], "assets/img/" . $nama_file)) {
            $foto_sql = ", foto = '$nama_file'";
        }
    }
    mysqli_query($koneksi, "UPDATE users SET username='$username', `nama lengkap`='$nama', email='$email', pekerjaan='$pekerjaan', usia='$usia' $foto_sql WHERE id='$id_saya'");
    echo "<script>window.location='?page=profil&status=ok';</script>"; exit;
}

$p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$id_saya'"));
?>

<?php if (isset($_GET['status']) && $_GET['status'] === 'ok'): ?>
<div class="gf-alert success mb-4"><i class="fas fa-check-circle"></i> Profil berhasil diperbarui.</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Profile Card -->
    <div class="col-lg-4">
        <div class="gf-card h-100">
            <div class="gf-card-body text-center" style="padding: 36px 24px;">
                <div style="position:relative; width:120px; height:120px; margin: 0 auto 20px;">
                    <img id="preview"
                         src="assets/img/<?= htmlspecialchars($p['foto'] ?? 'user.png') ?>"
                         style="width:120px; height:120px; border-radius:50%; object-fit:cover; border: 3px solid var(--brand-light);"
                         onerror="this.src='assets/img/logo_side.png'">
                    <label for="upload_foto"
                           style="position:absolute; bottom:2px; right:2px; width:32px; height:32px; background:var(--brand); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; border:3px solid #fff; font-size:.8rem;">
                        <i class="fas fa-camera"></i>
                    </label>
                </div>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($p['nama lengkap'] ?? '-') ?></h5>
                <p class="mb-3" style="color:var(--text-muted); font-size:.85rem;">@<?= htmlspecialchars($p['username']) ?></p>
                <span class="gf-badge <?= $p['role'] === 'orang_tua' ? 'info' : 'income' ?>">
                    <i class="fas fa-<?= $p['role'] === 'orang_tua' ? 'user-shield' : 'user' ?>"></i>
                    <?= ucfirst(str_replace('_', ' ', $p['role'] ?? 'pengguna')) ?>
                </span>

                <div class="mt-4 pt-4" style="border-top:1px solid var(--border); text-align:left;">
                    <?php if (!empty($p['email'])): ?>
                    <div class="d-flex align-items-center gap-2 mb-2" style="font-size:.875rem; color:var(--text-secondary);">
                        <i class="fas fa-envelope" style="color:var(--brand); width:18px;"></i>
                        <?= htmlspecialchars($p['email']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($p['pekerjaan'])): ?>
                    <div class="d-flex align-items-center gap-2 mb-2" style="font-size:.875rem; color:var(--text-secondary);">
                        <i class="fas fa-briefcase" style="color:var(--brand); width:18px;"></i>
                        <?= htmlspecialchars($p['pekerjaan']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($p['usia'])): ?>
                    <div class="d-flex align-items-center gap-2" style="font-size:.875rem; color:var(--text-secondary);">
                        <i class="fas fa-cake-candles" style="color:var(--brand); width:18px;"></i>
                        <?= htmlspecialchars($p['usia']) ?> tahun
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="col-lg-8">
        <div class="gf-card">
            <div class="gf-card-header">
                <span class="gf-card-title"><i class="fas fa-pen-to-square me-2 text-brand"></i>Edit Informasi Profil</span>
            </div>
            <div class="gf-card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" id="upload_foto" name="foto_profil" class="d-none"
                           onchange="document.getElementById('preview').src = window.URL.createObjectURL(this.files[0])">

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="gf-label">Username</label>
                            <input type="text" name="username" class="gf-input"
                                   value="<?= htmlspecialchars($p['username'] ?? '') ?>" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="gf-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="gf-input"
                                   value="<?= htmlspecialchars($p['nama lengkap'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="gf-label">Email</label>
                            <input type="email" name="email" class="gf-input"
                                   placeholder="email@contoh.com"
                                   value="<?= htmlspecialchars($p['email'] ?? '') ?>">
                        </div>
                        <div class="col-sm-6">
                            <label class="gf-label">Pekerjaan</label>
                            <input type="text" name="pekerjaan" class="gf-input"
                                   placeholder="Contoh: Mahasiswa"
                                   value="<?= htmlspecialchars($p['pekerjaan'] ?? '') ?>">
                        </div>
                        <div class="col-sm-6">
                            <label class="gf-label">Usia</label>
                            <input type="number" name="usia" class="gf-input" min="1" max="120"
                                   value="<?= htmlspecialchars($p['usia'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top:1px solid var(--border);">
                        <button type="reset" class="btn-gf-secondary">Reset</button>
                        <button type="submit" name="update_saya" class="btn-gf-primary">
                            <i class="fas fa-floppy-disk"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="gf-card mt-4">
            <div class="gf-card-body d-flex gap-3 flex-wrap">
                <a href="index.php?page=pengaturan_akun" class="btn-gf-ghost" style="text-decoration:none;">
                    <i class="fas fa-key"></i> Ubah Password
                </a>
                <a href="index.php?page=pengaturan" class="btn-gf-secondary" style="text-decoration:none;">
                    <i class="fas fa-gear"></i> Pengaturan Akun
                </a>
            </div>
        </div>
    </div>
</div>