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

<style>
/* ── PROFIL PAGE ── */
.profil-avatar-wrap {
    position: relative;
    width: 108px;
    height: 108px;
    margin: 0 auto 18px;
    flex-shrink: 0;
}
.profil-avatar {
    width: 108px;
    height: 108px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--brand-light);
    display: block;
}
.profil-avatar-btn {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 30px;
    height: 30px;
    background: var(--brand);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 3px solid var(--surface);
    font-size: .75rem;
    transition: background var(--transition);
}
.profil-avatar-btn:hover { background: var(--brand-dark); }

.profil-info-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
    font-size: .875rem;
    color: var(--text-secondary);
}
.profil-info-row:last-child { border-bottom: none; padding-bottom: 0; }
.profil-info-icon {
    width: 32px;
    height: 32px;
    background: var(--brand-light);
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--brand);
    font-size: .8rem;
    flex-shrink: 0;
}

.form-field-group { display: flex; flex-direction: column; gap: 6px; }
.form-field-group label {
    font-size: .74rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .5px;
}
.form-field-group input,
.form-field-group select {
    width: 100%;
    background: var(--surface-2);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    padding: 10px 14px;
    font-family: var(--font);
    font-size: .9rem;
    color: var(--text-primary);
    transition: border-color var(--transition), box-shadow var(--transition);
    outline: none;
    appearance: none;
    min-height: 44px;
}
.form-field-group input:focus,
.form-field-group select:focus {
    border-color: var(--brand);
    background: var(--surface);
    box-shadow: 0 0 0 3px var(--brand-glow);
}

.quick-link-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    border-radius: var(--radius-md);
    border: 1.5px solid var(--border);
    background: var(--surface);
    text-decoration: none;
    color: var(--text-primary);
    font-weight: 600;
    font-size: .875rem;
    transition: border-color var(--transition), background var(--transition), transform var(--transition);
}
.quick-link-card:hover {
    border-color: var(--brand);
    background: var(--brand-light);
    color: var(--brand);
    transform: translateY(-1px);
}
.quick-link-icon {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .95rem;
    flex-shrink: 0;
}

/* Mobile stack: card goes on top, form below */
@media (max-width: 767px) {
    .profil-layout { flex-direction: column !important; }
    .profil-sidebar-col { width: 100% !important; flex: 0 0 100% !important; max-width: 100% !important; }
    .profil-main-col   { width: 100% !important; flex: 0 0 100% !important; max-width: 100% !important; }

    /* On mobile: avatar card becomes horizontal strip */
    .profil-card-inner {
        display: flex;
        align-items: center;
        gap: 18px;
        text-align: left !important;
        padding: 20px !important;
    }
    .profil-avatar-wrap { margin: 0 !important; flex-shrink: 0; }
    .profil-text-block { flex: 1; min-width: 0; }
    .profil-info-section { display: none; }
    .profil-form-cols .col-sm-6 { flex: 0 0 100%; max-width: 100%; }
    .form-actions { flex-direction: column-reverse; }
    .form-actions button { width: 100% !important; }
    .quick-links-grid { grid-template-columns: 1fr !important; }
}
</style>

<?php if (isset($_GET['status']) && $_GET['status'] === 'ok'): ?>
<div class="gf-alert success" style="margin:0 0 20px;">
    <i class="fas fa-check-circle"></i>
    <span>Profil berhasil diperbarui.</span>
</div>
<?php endif; ?>

<div class="row g-4 profil-layout">

    <!-- ── KOLOM KIRI: Avatar Card ── -->
    <div class="col-lg-4 col-md-5 profil-sidebar-col">
        <div class="gf-card h-100">
            <div class="gf-card-body profil-card-inner" style="padding:32px 24px; text-align:center;">

                <!-- Avatar -->
                <div class="profil-avatar-wrap">
                    <img id="preview"
                         src="assets/img/<?= htmlspecialchars($p['foto'] ?? 'user.png') ?>"
                         class="profil-avatar"
                         onerror="this.src='assets/img/logo_side.png'"
                         alt="Foto Profil">
                    <label for="upload_foto" class="profil-avatar-btn" title="Ganti foto">
                        <i class="fas fa-camera"></i>
                    </label>
                </div>

                <!-- Info teks -->
                <div class="profil-text-block">
                    <h5 class="fw-bold mb-1" style="font-size:1.05rem; line-height:1.3;">
                        <?= htmlspecialchars($p['nama lengkap'] ?? '-') ?>
                    </h5>
                    <p class="mb-2" style="color:var(--text-muted); font-size:.82rem;">
                        @<?= htmlspecialchars($p['username']) ?>
                    </p>
                    <span class="gf-badge <?= $p['role'] === 'orang_tua' ? 'info' : 'income' ?>">
                        <i class="fas fa-<?= $p['role'] === 'orang_tua' ? 'user-shield' : 'user' ?>"></i>
                        <?= ucfirst(str_replace('_', ' ', $p['role'] ?? 'pengguna')) ?>
                    </span>
                </div>

                <!-- Detail info (desktop only) -->
                <div class="profil-info-section mt-4 pt-3" style="border-top:1px solid var(--border); width:100%;">
                    <?php if (!empty($p['email'])): ?>
                    <div class="profil-info-row">
                        <span class="profil-info-icon"><i class="fas fa-envelope"></i></span>
                        <span style="word-break:break-all;"><?= htmlspecialchars($p['email']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($p['pekerjaan'])): ?>
                    <div class="profil-info-row">
                        <span class="profil-info-icon"><i class="fas fa-briefcase"></i></span>
                        <span><?= htmlspecialchars($p['pekerjaan']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($p['usia'])): ?>
                    <div class="profil-info-row">
                        <span class="profil-info-icon"><i class="fas fa-cake-candles"></i></span>
                        <span><?= htmlspecialchars($p['usia']) ?> tahun</span>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- ── KOLOM KANAN: Form + Quick Links ── -->
    <div class="col-lg-8 col-md-7 profil-main-col">

        <!-- Form Edit -->
        <div class="gf-card">
            <div class="gf-card-header">
                <span class="gf-card-title">
                    <i class="fas fa-pen-to-square me-2 text-brand"></i>Edit Informasi Profil
                </span>
            </div>
            <div class="gf-card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" id="upload_foto" name="foto_profil" class="d-none"
                           accept="image/*"
                           onchange="document.getElementById('preview').src = window.URL.createObjectURL(this.files[0])">

                    <div class="row g-3 profil-form-cols">
                        <div class="col-sm-6">
                            <div class="form-field-group">
                                <label>Username</label>
                                <input type="text" name="username"
                                       value="<?= htmlspecialchars($p['username'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-field-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama_lengkap"
                                       value="<?= htmlspecialchars($p['nama lengkap'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-field-group">
                                <label>Email</label>
                                <input type="email" name="email"
                                       placeholder="email@contoh.com"
                                       value="<?= htmlspecialchars($p['email'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-field-group">
                                <label>Pekerjaan</label>
                                <input type="text" name="pekerjaan"
                                       placeholder="Contoh: Mahasiswa"
                                       value="<?= htmlspecialchars($p['pekerjaan'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-field-group">
                                <label>Usia</label>
                                <input type="number" name="usia" min="1" max="120"
                                       placeholder="Contoh: 21"
                                       value="<?= htmlspecialchars($p['usia'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4 pt-3 form-actions"
                         style="border-top:1px solid var(--border); justify-content:flex-end;">
                        <button type="reset" class="btn-gf-secondary">
                            <i class="fas fa-rotate-left"></i> Reset
                        </button>
                        <button type="submit" name="update_saya" class="btn-gf-primary">
                            <i class="fas fa-floppy-disk"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="gf-card mt-4">
            <div class="gf-card-header">
                <span class="gf-card-title">
                    <i class="fas fa-bolt me-2 text-brand"></i>Akses Cepat
                </span>
            </div>
            <div class="gf-card-body">
                <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:12px;" class="quick-links-grid">
                    <a href="index.php?page=pengaturan_akun" class="quick-link-card">
                        <span class="quick-link-icon" style="background:rgba(239,68,68,.1); color:#EF4444;">
                            <i class="fas fa-key"></i>
                        </span>
                        <div>
                            <div style="font-size:.875rem; font-weight:700;">Ubah Password</div>
                            <div style="font-size:.74rem; color:var(--text-muted); font-weight:400;">Perbarui keamanan akun</div>
                        </div>
                    </a>
                    <a href="index.php?page=pengaturan" class="quick-link-card">
                        <span class="quick-link-icon" style="background:var(--brand-light); color:var(--brand);">
                            <i class="fas fa-gear"></i>
                        </span>
                        <div>
                            <div style="font-size:.875rem; font-weight:700;">Pengaturan</div>
                            <div style="font-size:.74rem; color:var(--text-muted); font-weight:400;">Konfigurasi aplikasi</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
