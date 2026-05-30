<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../config/koneksi.php';
$id_saya = $_SESSION['user_id'] ?? null;

// ══════════════════════════════════════════════
// 1. PROSES UPDATE PROFIL
// ══════════════════════════════════════════════
if (isset($_POST['update_saya'])) {
    $username  = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $nama      = mysqli_real_escape_string($koneksi, trim($_POST['nama_lengkap']));
    $email     = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $pekerjaan = mysqli_real_escape_string($koneksi, trim($_POST['pekerjaan']));
    $usia      = (int)$_POST['usia'];
    $foto_sql  = "";

    // Simpan foto sebagai base64 data URI agar persisten di Railway (tidak bergantung filesystem)
    if (!empty($_FILES['foto_profil']['name']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $ftype = mime_content_type($_FILES['foto_profil']['tmp_name']);
        if (in_array($ftype, $allowed_types) && $_FILES['foto_profil']['size'] <= 2 * 1024 * 1024) {
            $foto_data        = base64_encode(file_get_contents($_FILES['foto_profil']['tmp_name']));
            $foto_b64         = 'data:' . $ftype . ';base64,' . $foto_data;
            $foto_b64_escaped = mysqli_real_escape_string($koneksi, $foto_b64);
            $foto_sql         = ", foto = '$foto_b64_escaped'";
        } else {
            // File tidak valid
            $_SESSION['profil_msg'] = ['type' => 'error', 'text' => 'Foto gagal diunggah. Pastikan format JPG/PNG/WEBP dan ukuran &le; 2 MB.'];
            header("Location: ?page=profil"); exit;
        }
    }

    // Cek username tidak duplikat
    $cek = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT id FROM users WHERE username='$username' AND id != '$id_saya' LIMIT 1"));
    if ($cek) {
        $_SESSION['profil_msg'] = ['type' => 'error', 'text' => 'Username sudah dipakai oleh akun lain.'];
        header("Location: ?page=profil"); exit;
    }

    mysqli_query($koneksi,
        "UPDATE users SET username='$username', `nama lengkap`='$nama', email='$email',
         pekerjaan='$pekerjaan', usia='$usia' $foto_sql WHERE id='$id_saya'");

    $_SESSION['profil_msg'] = ['type' => 'success', 'text' => 'Profil berhasil diperbarui.'];
    // Update session username jika berubah
    $_SESSION['username'] = $username;
    header("Location: ?page=profil"); exit;
}

// ══════════════════════════════════════════════
// 2. PROSES GANTI PASSWORD
// ══════════════════════════════════════════════
if (isset($_POST['ganti_password'])) {
    $pw_lama = $_POST['pw_lama'];
    $pw_baru = $_POST['pw_baru'];
    $pw_konfirmasi = $_POST['pw_konfirmasi'];

    $user_row = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT password FROM users WHERE id='$id_saya'"));

    if (!password_verify($pw_lama, $user_row['password'])) {
        $_SESSION['profil_msg'] = ['type' => 'error', 'text' => 'Password lama tidak sesuai.'];
    } elseif (strlen($pw_baru) < 6) {
        $_SESSION['profil_msg'] = ['type' => 'error', 'text' => 'Password baru minimal 6 karakter.'];
    } elseif ($pw_baru !== $pw_konfirmasi) {
        $_SESSION['profil_msg'] = ['type' => 'error', 'text' => 'Konfirmasi password tidak cocok.'];
    } else {
        $pw_hash = password_hash($pw_baru, PASSWORD_DEFAULT);
        $pw_hash_esc = mysqli_real_escape_string($koneksi, $pw_hash);
        mysqli_query($koneksi, "UPDATE users SET password='$pw_hash_esc' WHERE id='$id_saya'");
        $_SESSION['profil_msg'] = ['type' => 'success', 'text' => 'Password berhasil diubah.'];
    }
    header("Location: ?page=profil#seksi-password"); exit;
}

// ══════════════════════════════════════════════
// 3. AMBIL DATA USER
// ══════════════════════════════════════════════
$p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id='$id_saya'"));

// ── STATISTIK RINGKASAN ──
$stat_trx   = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM transaksi WHERE user_id='$id_saya'"));
$stat_masuk = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COALESCE(SUM(t.jumlah),0) as total
     FROM transaksi t JOIN kategori k ON t.id_kategori=k.id_kategori
     WHERE t.user_id='$id_saya' AND k.jenis='Pemasukan'"));
$stat_keluar = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COALESCE(SUM(t.jumlah),0) as total
     FROM transaksi t JOIN kategori k ON t.id_kategori=k.id_kategori
     WHERE t.user_id='$id_saya' AND k.jenis='Pengeluaran'"));
$stat_dompet = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM dompet WHERE user_id='$id_saya'"));
$stat_kat    = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM kategori WHERE user_id='$id_saya'"));

// ── 5 TRANSAKSI TERAKHIR ──
$q_recent = mysqli_query($koneksi,
    "SELECT t.tanggal, t.jumlah, k.nama_kategori, k.jenis
     FROM transaksi t JOIN kategori k ON t.id_kategori=k.id_kategori
     WHERE t.user_id='$id_saya'
     ORDER BY t.tanggal DESC, t.id_transaksi DESC LIMIT 5");

// ── BULAN BERGABUNG ──
$tgl_daftar = $p['created_at'] ?? null;
$bulan_bergabung = $tgl_daftar ? date('M Y', strtotime($tgl_daftar)) : '-';

// ── POP FLASH MESSAGE ──
$flash = $_SESSION['profil_msg'] ?? null;
unset($_SESSION['profil_msg']);

// ── FOTO SRC ──
$foto_val = $p['foto'] ?? '';
if (str_starts_with($foto_val, 'data:')) {
    $foto_src = $foto_val; // base64 data URI langsung
} elseif (!empty($foto_val)) {
    $foto_src = 'assets/img/' . htmlspecialchars($foto_val);
} else {
    $foto_src = 'assets/img/logo_side.png';
}
?>

<style>
/* ════════════════════════════════════════
   PROFIL PAGE — COMPLETE & MOBILE-READY
════════════════════════════════════════ */

/* ── ALERT FLASH ── */
.pf-alert {
    display: flex; align-items: center; gap: 10px;
    padding: 13px 18px; border-radius: var(--radius-md);
    font-size: .875rem; font-weight: 600; margin-bottom: 20px;
    animation: slideDown .25s ease;
}
@keyframes slideDown { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:none; } }
.pf-alert.success { background:#DCFCE7; color:#15803D; border:1px solid #BBF7D0; }
.pf-alert.error   { background:#FEE2E2; color:#B91C1C; border:1px solid #FECACA; }

/* ── STAT CARDS ── */
.pf-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 16px;
}
.pf-stat-card {
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    padding: 14px 16px;
    text-align: center;
    transition: border-color var(--transition), box-shadow var(--transition);
}
.pf-stat-card:hover { border-color: var(--brand); box-shadow: 0 2px 10px rgba(0,0,0,.06); }
.pf-stat-val {
    font-size: 1.25rem; font-weight: 800;
    color: var(--text-primary); line-height: 1.2;
    font-variant-numeric: tabular-nums;
}
.pf-stat-lbl {
    font-size: .7rem; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .5px;
    margin-top: 3px; font-weight: 600;
}

/* ── AVATAR ── */
.pf-avatar-wrap {
    position: relative; width: 110px; height: 110px;
    margin: 0 auto 16px; flex-shrink: 0;
}
.pf-avatar {
    width: 110px; height: 110px; border-radius: 50%; object-fit: cover;
    border: 3px solid var(--brand-light); display: block;
    transition: opacity .2s;
}
.pf-avatar-btn {
    position: absolute; bottom: 2px; right: 2px;
    width: 32px; height: 32px; background: var(--brand); color: #fff;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    cursor: pointer; border: 3px solid var(--surface); font-size: .75rem;
    transition: background var(--transition), transform var(--transition);
}
.pf-avatar-btn:hover { background: var(--brand-dark); transform: scale(1.1); }
.pf-avatar-overlay {
    position: absolute; inset: 0; border-radius: 50%;
    background: rgba(0,0,0,.35); display: none;
    align-items: center; justify-content: center;
    color: #fff; font-size: .7rem; font-weight: 700; cursor: pointer;
}
.pf-avatar-wrap:hover .pf-avatar-overlay { display: flex; }

/* ── INFO ROWS ── */
.pf-info-row {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 0; border-bottom: 1px solid var(--border);
    font-size: .85rem; color: var(--text-secondary);
}
.pf-info-row:last-child { border-bottom: none; padding-bottom: 0; }
.pf-info-icon {
    width: 30px; height: 30px; background: var(--brand-light);
    border-radius: var(--radius-sm); display: flex;
    align-items: center; justify-content: center;
    color: var(--brand); font-size: .75rem; flex-shrink: 0;
}

/* ── FORM FIELDS ── */
.pf-field { display: flex; flex-direction: column; gap: 5px; }
.pf-field label {
    font-size: .72rem; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .5px;
}
.pf-field input, .pf-field select, .pf-field textarea {
    width: 100%; background: var(--surface-2);
    border: 1.5px solid var(--border); border-radius: var(--radius-md);
    padding: 10px 14px; font-family: var(--font); font-size: .875rem;
    color: var(--text-primary); outline: none; min-height: 44px;
    transition: border-color var(--transition), box-shadow var(--transition);
    appearance: none;
}
.pf-field input:focus, .pf-field select:focus, .pf-field textarea:focus {
    border-color: var(--brand); background: var(--surface);
    box-shadow: 0 0 0 3px var(--brand-glow);
}
.pf-field .input-hint {
    font-size: .72rem; color: var(--text-muted); margin-top: 2px;
}

/* ── PASSWORD STRENGTH ── */
.pw-strength-bar {
    height: 4px; border-radius: 4px;
    background: var(--border); margin-top: 6px; overflow: hidden;
}
.pw-strength-fill {
    height: 100%; border-radius: 4px;
    transition: width .3s, background .3s;
    width: 0%;
}
.pw-strength-txt { font-size: .72rem; margin-top: 3px; font-weight: 600; }

/* ── QUICK LINK CARDS ── */
.pf-quick-card {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 18px; border-radius: var(--radius-md);
    border: 1.5px solid var(--border); background: var(--surface);
    text-decoration: none; color: var(--text-primary);
    font-weight: 600; font-size: .875rem;
    transition: border-color var(--transition), background var(--transition), transform var(--transition);
}
.pf-quick-card:hover {
    border-color: var(--brand); background: var(--brand-light);
    color: var(--brand); transform: translateY(-2px);
}
.pf-quick-icon {
    width: 38px; height: 38px; border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem; flex-shrink: 0;
}

/* ── RECENT TRANSAKSI ── */
.pf-recent-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 0; border-bottom: 1px solid var(--border);
}
.pf-recent-item:last-child { border-bottom: none; }
.pf-recent-icon {
    width: 36px; height: 36px; border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem; flex-shrink: 0;
}
.pf-recent-icon.income  { background: #DCFCE7; color: #15803D; }
.pf-recent-icon.expense { background: #FEE2E2; color: #B91C1C; }
.pf-recent-meta { flex: 1; min-width: 0; }
.pf-recent-kat  { font-weight: 700; font-size: .84rem; color: var(--text-primary); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.pf-recent-tgl  { font-size: .74rem; color: var(--text-muted); }
.pf-recent-amt  { font-weight: 800; font-size: .88rem; font-variant-numeric: tabular-nums; white-space: nowrap; }
.pf-recent-amt.income  { color: var(--success); }
.pf-recent-amt.expense { color: var(--danger); }

/* ── SECTION TITLE ── */
.pf-section-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--brand-light); color: var(--brand);
    border-radius: 50px; padding: 3px 12px;
    font-size: .72rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; margin-bottom: 16px;
}

/* ── TABS (mobile password section) ── */
.pf-tab-btns {
    display: flex; gap: 0;
    border: 1.5px solid var(--border); border-radius: var(--radius-md);
    overflow: hidden; margin-bottom: 20px;
}
.pf-tab-btn {
    flex: 1; padding: 10px 8px; border: none; background: var(--surface-2);
    font-family: var(--font); font-size: .8rem; font-weight: 700; cursor: pointer;
    color: var(--text-muted); transition: background var(--transition), color var(--transition);
    border-right: 1px solid var(--border);
}
.pf-tab-btn:last-child { border-right: none; }
.pf-tab-btn.active { background: var(--brand); color: #fff; }

/* ── MOBILE ── */
@media (max-width: 767px) {
    .pf-layout        { flex-direction: column !important; }
    .pf-col-side      { width: 100% !important; flex: 0 0 100% !important; max-width: 100% !important; }
    .pf-col-main      { width: 100% !important; flex: 0 0 100% !important; max-width: 100% !important; }

    /* Horizontal avatar strip on mobile */
    .pf-card-inner {
        display: flex !important; flex-direction: row !important;
        align-items: center !important; gap: 18px !important;
        text-align: left !important; padding: 16px 18px !important;
    }
    .pf-avatar-wrap   { margin: 0 !important; width: 80px !important; height: 80px !important; }
    .pf-avatar        { width: 80px !important; height: 80px !important; }
    .pf-avatar-btn    { width: 26px !important; height: 26px !important; font-size: .68rem !important; }
    .pf-text-block    { flex: 1; min-width: 0; }
    .pf-info-section  { display: none !important; }
    .pf-stats-grid    { grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .pf-stat-val      { font-size: 1rem; }

    .pf-form-row .col-sm-6 { flex: 0 0 100%; max-width: 100%; }
    .pf-form-actions  { flex-direction: column-reverse; }
    .pf-form-actions button { width: 100% !important; }
    .pf-quick-grid    { grid-template-columns: 1fr !important; }
}
@media (min-width: 768px) {
    .pf-tab-btns, .pf-tab-panel { display: none !important; }
    .pf-desktop-sections { display: block !important; }
}
@media (max-width: 767px) {
    .pf-desktop-sections { display: none !important; }
    .pf-tab-btns, .pf-tab-panel.active { display: flex !important; }
    .pf-tab-panel { display: none; flex-direction: column; }
}
</style>

<?php
// ── FLASH ALERT ──
if ($flash):
    $ic = $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation';
?>
<div class="pf-alert <?= $flash['type'] ?>">
    <i class="fas <?= $ic ?>"></i>
    <span><?= $flash['text'] ?></span>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════
     MOBILE TABS
════════════════════════════════════════ -->
<div class="pf-tab-btns">
    <button class="pf-tab-btn active" data-tab="profil">
        <i class="fas fa-user me-1"></i>Profil
    </button>
    <button class="pf-tab-btn" data-tab="password">
        <i class="fas fa-lock me-1"></i>Password
    </button>
    <button class="pf-tab-btn" data-tab="aktivitas">
        <i class="fas fa-chart-bar me-1"></i>Aktivitas
    </button>
</div>

<!-- ══════════════════════════════════════
     LAYOUT UTAMA
════════════════════════════════════════ -->
<div class="row g-4 pf-layout">

    <!-- ────────────────────────────────────
         KOLOM KIRI: Avatar Card + Stats
    ──────────────────────────────────────── -->
    <div class="col-lg-4 col-md-5 pf-col-side">

        <!-- Avatar Card -->
        <div class="gf-card">
            <div class="gf-card-body pf-card-inner" style="padding:28px 24px; text-align:center;">

                <!-- Avatar -->
                <div class="pf-avatar-wrap">
                    <img id="preview" src="<?= htmlspecialchars($foto_src) ?>"
                         class="pf-avatar" alt="Foto Profil"
                         onerror="this.src='assets/img/logo_side.png'">
                    <label for="upload_foto" class="pf-avatar-btn" title="Ganti foto profil">
                        <i class="fas fa-camera"></i>
                    </label>
                </div>

                <!-- Teks -->
                <div class="pf-text-block">
                    <h5 class="fw-bold mb-1" style="font-size:1.05rem; line-height:1.3; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        <?= htmlspecialchars($p['nama lengkap'] ?? '-') ?>
                    </h5>
                    <p class="mb-2" style="color:var(--text-muted); font-size:.82rem;">
                        @<?= htmlspecialchars($p['username'] ?? '') ?>
                    </p>
                    <span class="gf-badge <?= ($p['role'] ?? '') === 'orang_tua' ? 'info' : 'income' ?>">
                        <i class="fas fa-<?= ($p['role'] ?? '') === 'orang_tua' ? 'user-shield' : 'user' ?>"></i>
                        <?= ucfirst(str_replace('_', ' ', $p['role'] ?? 'pengguna')) ?>
                    </span>
                </div>

                <!-- Info detail (hanya desktop) -->
                <div class="pf-info-section mt-4 pt-3" style="border-top:1px solid var(--border); width:100%; text-align:left;">
                    <?php if (!empty($p['email'])): ?>
                    <div class="pf-info-row">
                        <span class="pf-info-icon"><i class="fas fa-envelope"></i></span>
                        <span style="word-break:break-all; font-size:.83rem;"><?= htmlspecialchars($p['email']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($p['pekerjaan'])): ?>
                    <div class="pf-info-row">
                        <span class="pf-info-icon"><i class="fas fa-briefcase"></i></span>
                        <span><?= htmlspecialchars($p['pekerjaan']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($p['usia'])): ?>
                    <div class="pf-info-row">
                        <span class="pf-info-icon"><i class="fas fa-cake-candles"></i></span>
                        <span><?= (int)$p['usia'] ?> tahun</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($bulan_bergabung !== '-'): ?>
                    <div class="pf-info-row">
                        <span class="pf-info-icon"><i class="fas fa-calendar-check"></i></span>
                        <span>Bergabung <?= $bulan_bergabung ?></span>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- Stats Card -->
        <div class="gf-card mt-3">
            <div class="gf-card-header">
                <span class="gf-card-title">
                    <i class="fas fa-chart-pie me-2 text-brand"></i>Ringkasan Akun
                </span>
            </div>
            <div class="gf-card-body" style="padding:16px;">
                <div class="pf-stats-grid">
                    <div class="pf-stat-card">
                        <div class="pf-stat-val"><?= number_format($stat_trx['total']) ?></div>
                        <div class="pf-stat-lbl">Transaksi</div>
                    </div>
                    <div class="pf-stat-card">
                        <div class="pf-stat-val"><?= number_format($stat_dompet['total']) ?></div>
                        <div class="pf-stat-lbl">Dompet</div>
                    </div>
                    <div class="pf-stat-card">
                        <div class="pf-stat-val"><?= number_format($stat_kat['total']) ?></div>
                        <div class="pf-stat-lbl">Kategori</div>
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:8px; margin-top:4px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:.82rem;">
                        <span style="color:var(--text-muted); display:flex; align-items:center; gap:6px;">
                            <i class="fas fa-arrow-down" style="color:var(--success); font-size:.75rem;"></i>Total Masuk
                        </span>
                        <span style="font-weight:800; color:var(--success);">
                            Rp <?= number_format($stat_masuk['total'], 0, ',', '.') ?>
                        </span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:.82rem;">
                        <span style="color:var(--text-muted); display:flex; align-items:center; gap:6px;">
                            <i class="fas fa-arrow-up" style="color:var(--danger); font-size:.75rem;"></i>Total Keluar
                        </span>
                        <span style="font-weight:800; color:var(--danger);">
                            Rp <?= number_format($stat_keluar['total'], 0, ',', '.') ?>
                        </span>
                    </div>
                    <?php
                        $saldo_bersih = $stat_masuk['total'] - $stat_keluar['total'];
                        $saldo_color  = $saldo_bersih >= 0 ? 'var(--success)' : 'var(--danger)';
                        $saldo_sign   = $saldo_bersih >= 0 ? '+' : '';
                    ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:.84rem;
                                padding-top:8px; border-top:1px solid var(--border); margin-top:2px;">
                        <span style="font-weight:700; color:var(--text-primary);">Saldo Bersih</span>
                        <span style="font-weight:800; color:<?= $saldo_color ?>;">
                            <?= $saldo_sign ?>Rp <?= number_format(abs($saldo_bersih), 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ────────────────────────────────────
         KOLOM KANAN: Form + Password + Links
    ──────────────────────────────────────── -->
    <div class="col-lg-8 col-md-7 pf-col-main">

        <!-- ════ TAB: PROFIL (mobile) / always visible desktop ════ -->
        <div class="pf-tab-panel active" data-panel="profil">

            <!-- Form Edit Profil -->
            <div class="gf-card">
                <div class="gf-card-header">
                    <span class="gf-card-title">
                        <i class="fas fa-pen-to-square me-2 text-brand"></i>Edit Informasi Profil
                    </span>
                </div>
                <div class="gf-card-body">
                    <form method="POST" enctype="multipart/form-data" id="formEditProfil">
                        <input type="file" id="upload_foto" name="foto_profil" class="d-none"
                               accept="image/jpeg,image/png,image/gif,image/webp"
                               onchange="handleFotoPreview(this)">

                        <div class="row g-3 pf-form-row">
                            <div class="col-sm-6">
                                <div class="pf-field">
                                    <label>Username <span style="color:var(--danger);">*</span></label>
                                    <input type="text" name="username" maxlength="50" required
                                           value="<?= htmlspecialchars($p['username'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="pf-field">
                                    <label>Nama Lengkap</label>
                                    <input type="text" name="nama_lengkap" maxlength="100"
                                           value="<?= htmlspecialchars($p['nama lengkap'] ?? '') ?>"
                                           placeholder="Nama lengkap Anda">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="pf-field">
                                    <label>Alamat Email</label>
                                    <input type="email" name="email" maxlength="100"
                                           placeholder="email@contoh.com"
                                           value="<?= htmlspecialchars($p['email'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="pf-field">
                                    <label>Pekerjaan</label>
                                    <input type="text" name="pekerjaan" maxlength="80"
                                           placeholder="Contoh: Mahasiswa, Pegawai..."
                                           value="<?= htmlspecialchars($p['pekerjaan'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="pf-field">
                                    <label>Usia</label>
                                    <input type="number" name="usia" min="1" max="120"
                                           placeholder="Contoh: 21"
                                           value="<?= htmlspecialchars($p['usia'] ?? '') ?>">
                                </div>
                            </div>
                            <!-- Upload foto hint -->
                            <div class="col-12">
                                <div style="display:flex; align-items:center; gap:12px;
                                            padding:12px 14px; background:var(--surface-2);
                                            border:1.5px dashed var(--border); border-radius:var(--radius-md);">
                                    <img id="preview_thumb" src="<?= htmlspecialchars($foto_src) ?>"
                                         onerror="this.src='assets/img/logo_side.png'"
                                         style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--brand-light);flex-shrink:0;">
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-size:.82rem; font-weight:700; color:var(--text-primary); margin-bottom:2px;">Foto Profil</div>
                                        <div style="font-size:.74rem; color:var(--text-muted);">JPG, PNG, WEBP &bull; Maks. 2 MB</div>
                                    </div>
                                    <label for="upload_foto"
                                           style="display:inline-flex; align-items:center; gap:6px; padding:8px 14px;
                                                  background:var(--brand-light); color:var(--brand); border-radius:var(--radius-md);
                                                  font-size:.78rem; font-weight:700; cursor:pointer; white-space:nowrap;
                                                  border:1.5px solid var(--brand); transition:background var(--transition);"
                                           onmouseover="this.style.background='#b3eed8'" onmouseout="this.style.background='var(--brand-light)'">
                                        <i class="fas fa-camera"></i> Pilih Foto
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4 pt-3 pf-form-actions"
                             style="border-top:1px solid var(--border); justify-content:flex-end;">
                            <button type="reset" class="btn-gf-secondary" onclick="resetPreview()">
                                <i class="fas fa-rotate-left"></i> Reset
                            </button>
                            <button type="submit" name="update_saya" class="btn-gf-primary" id="btnSimpanProfil">
                                <i class="fas fa-floppy-disk"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div><!-- /tab profil -->

        <!-- ════ TAB: PASSWORD ════ -->
        <div class="pf-tab-panel" data-panel="password">

            <div class="gf-card pf-desktop-sections" style="margin-top:0;" id="seksi-password">
                <div class="gf-card-header">
                    <span class="gf-card-title">
                        <i class="fas fa-lock me-2 text-brand"></i>Ubah Password
                    </span>
                </div>
                <div class="gf-card-body">
                    <form method="POST" id="formGantiPassword" autocomplete="off">
                        <div class="row g-3 pf-form-row">
                            <div class="col-12">
                                <div class="pf-field">
                                    <label>Password Lama <span style="color:var(--danger);">*</span></label>
                                    <div style="position:relative;">
                                        <input type="password" name="pw_lama" id="pw_lama" required
                                               placeholder="Masukkan password saat ini"
                                               style="padding-right:44px;">
                                        <button type="button" class="pw-toggle" data-target="pw_lama"
                                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                                       border:none;background:none;color:var(--text-muted);cursor:pointer;font-size:.85rem;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="pf-field">
                                    <label>Password Baru <span style="color:var(--danger);">*</span></label>
                                    <div style="position:relative;">
                                        <input type="password" name="pw_baru" id="pw_baru" required
                                               placeholder="Min. 6 karakter"
                                               oninput="checkPasswordStrength(this.value)"
                                               style="padding-right:44px;">
                                        <button type="button" class="pw-toggle" data-target="pw_baru"
                                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                                       border:none;background:none;color:var(--text-muted);cursor:pointer;font-size:.85rem;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="pw-strength-bar"><div class="pw-strength-fill" id="pwBar"></div></div>
                                    <div class="pw-strength-txt" id="pwTxt" style="color:var(--text-muted);"></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="pf-field">
                                    <label>Konfirmasi Password Baru <span style="color:var(--danger);">*</span></label>
                                    <div style="position:relative;">
                                        <input type="password" name="pw_konfirmasi" id="pw_konfirmasi" required
                                               placeholder="Ulangi password baru"
                                               oninput="checkConfirm()"
                                               style="padding-right:44px;">
                                        <button type="button" class="pw-toggle" data-target="pw_konfirmasi"
                                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                                       border:none;background:none;color:var(--text-muted);cursor:pointer;font-size:.85rem;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="confirmTxt" class="pw-strength-txt" style="color:var(--text-muted);"></div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4 pt-3 pf-form-actions"
                             style="border-top:1px solid var(--border); justify-content:flex-end;">
                            <button type="reset" class="btn-gf-secondary">
                                <i class="fas fa-rotate-left"></i> Reset
                            </button>
                            <button type="submit" name="ganti_password" class="btn-gf-primary"
                                    style="background:#EF4444; border-color:#EF4444;">
                                <i class="fas fa-key"></i> Perbarui Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div><!-- /tab password -->

        <!-- ════ TAB: AKTIVITAS ════ -->
        <div class="pf-tab-panel" data-panel="aktivitas">

            <!-- Transaksi Terakhir -->
            <div class="gf-card pf-desktop-sections">
                <div class="gf-card-header">
                    <span class="gf-card-title">
                        <i class="fas fa-clock-rotate-left me-2 text-brand"></i>5 Transaksi Terakhir
                    </span>
                    <a href="index.php?page=transaksi" style="font-size:.78rem; color:var(--brand); font-weight:600; text-decoration:none;">
                        Lihat Semua <i class="fas fa-arrow-right fa-xs"></i>
                    </a>
                </div>
                <div class="gf-card-body" style="padding:16px;">
                    <?php if (mysqli_num_rows($q_recent) > 0): ?>
                        <?php while ($r = mysqli_fetch_assoc($q_recent)): ?>
                        <div class="pf-recent-item">
                            <div class="pf-recent-icon <?= $r['jenis'] === 'Pemasukan' ? 'income' : 'expense' ?>">
                                <i class="fas fa-<?= $r['jenis'] === 'Pemasukan' ? 'arrow-down' : 'arrow-up' ?>"></i>
                            </div>
                            <div class="pf-recent-meta">
                                <div class="pf-recent-kat"><?= htmlspecialchars($r['nama_kategori']) ?></div>
                                <div class="pf-recent-tgl"><?= date('d M Y', strtotime($r['tanggal'])) ?></div>
                            </div>
                            <div class="pf-recent-amt <?= $r['jenis'] === 'Pemasukan' ? 'income' : 'expense' ?>">
                                <?= $r['jenis'] === 'Pemasukan' ? '+' : '−' ?> Rp <?= number_format($r['jumlah'], 0, ',', '.') ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align:center; padding:28px 0; color:var(--text-muted);">
                            <i class="fas fa-receipt" style="font-size:1.8rem; opacity:.3; margin-bottom:8px; display:block;"></i>
                            <p style="font-size:.84rem; margin:0;">Belum ada transaksi.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /tab aktivitas -->

        <!-- ════ AKSES CEPAT (selalu tampil) ════ -->
        <div class="gf-card mt-3" id="pf-quick-section">
            <div class="gf-card-header">
                <span class="gf-card-title">
                    <i class="fas fa-bolt me-2 text-brand"></i>Akses Cepat
                </span>
            </div>
            <div class="gf-card-body">
                <div style="display:grid; grid-template-columns: repeat(2,1fr); gap:12px;" class="pf-quick-grid">

                    <a href="index.php?page=transaksi" class="pf-quick-card">
                        <span class="pf-quick-icon" style="background:rgba(16,185,129,.1); color:#059669;">
                            <i class="fas fa-arrow-right-arrow-left"></i>
                        </span>
                        <div>
                            <div style="font-size:.875rem; font-weight:700;">Transaksi</div>
                            <div style="font-size:.72rem; color:var(--text-muted); font-weight:400;">Catat kas masuk / keluar</div>
                        </div>
                    </a>

                    <a href="index.php?page=laporan" class="pf-quick-card">
                        <span class="pf-quick-icon" style="background:rgba(59,130,246,.1); color:#3B82F6;">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        <div>
                            <div style="font-size:.875rem; font-weight:700;">Laporan</div>
                            <div style="font-size:.72rem; color:var(--text-muted); font-weight:400;">Lihat laporan keuangan</div>
                        </div>
                    </a>

                    <a href="index.php?page=master" class="pf-quick-card">
                        <span class="pf-quick-icon" style="background:rgba(245,158,11,.1); color:#D97706;">
                            <i class="fas fa-database"></i>
                        </span>
                        <div>
                            <div style="font-size:.875rem; font-weight:700;">Data Master</div>
                            <div style="font-size:.72rem; color:var(--text-muted); font-weight:400;">Kelola kategori & dompet</div>
                        </div>
                    </a>

                    <a href="index.php?page=pengaturan_akun" class="pf-quick-card">
                        <span class="pf-quick-icon" style="background:rgba(239,68,68,.1); color:#EF4444;">
                            <i class="fas fa-users-gear"></i>
                        </span>
                        <div>
                            <div style="font-size:.875rem; font-weight:700;">Manajemen Akun</div>
                            <div style="font-size:.72rem; color:var(--text-muted); font-weight:400;">Pengaturan akses keluarga</div>
                        </div>
                    </a>

                    <a href="logout.php" class="pf-quick-card"
                       onclick="return confirm('Yakin ingin keluar dari GATIFIN?')"
                       style="grid-column: span 2;">
                        <span class="pf-quick-icon" style="background:rgba(113,128,150,.1); color:#718096;">
                            <i class="fas fa-right-from-bracket"></i>
                        </span>
                        <div>
                            <div style="font-size:.875rem; font-weight:700;">Keluar (Logout)</div>
                            <div style="font-size:.72rem; color:var(--text-muted); font-weight:400;">Akhiri sesi dan kembali ke login</div>
                        </div>
                    </a>

                </div>
            </div>
        </div>

        <!-- DESKTOP: Password + Aktivitas selalu tampil di bawah akses cepat -->
        <div class="pf-desktop-sections">

            <!-- Password -->
            <div class="gf-card mt-3" id="seksi-password-desktop">
                <div class="gf-card-header">
                    <span class="gf-card-title">
                        <i class="fas fa-lock me-2 text-brand"></i>Ubah Password
                    </span>
                </div>
                <div class="gf-card-body">
                    <form method="POST" autocomplete="off">
                        <div class="row g-3 pf-form-row">
                            <div class="col-12">
                                <div class="pf-field">
                                    <label>Password Lama <span style="color:var(--danger);">*</span></label>
                                    <div style="position:relative;">
                                        <input type="password" name="pw_lama" id="pw_lama_d" required
                                               placeholder="Masukkan password saat ini" style="padding-right:44px;">
                                        <button type="button" class="pw-toggle" data-target="pw_lama_d"
                                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);border:none;background:none;color:var(--text-muted);cursor:pointer;font-size:.85rem;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="pf-field">
                                    <label>Password Baru <span style="color:var(--danger);">*</span></label>
                                    <div style="position:relative;">
                                        <input type="password" name="pw_baru" id="pw_baru_d" required
                                               placeholder="Min. 6 karakter"
                                               oninput="checkPasswordStrengthD(this.value)"
                                               style="padding-right:44px;">
                                        <button type="button" class="pw-toggle" data-target="pw_baru_d"
                                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);border:none;background:none;color:var(--text-muted);cursor:pointer;font-size:.85rem;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="pw-strength-bar"><div class="pw-strength-fill" id="pwBarD"></div></div>
                                    <div class="pw-strength-txt" id="pwTxtD" style="color:var(--text-muted);"></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="pf-field">
                                    <label>Konfirmasi Password Baru <span style="color:var(--danger);">*</span></label>
                                    <div style="position:relative;">
                                        <input type="password" name="pw_konfirmasi" id="pw_konfirmasi_d" required
                                               placeholder="Ulangi password baru"
                                               oninput="checkConfirmD()"
                                               style="padding-right:44px;">
                                        <button type="button" class="pw-toggle" data-target="pw_konfirmasi_d"
                                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);border:none;background:none;color:var(--text-muted);cursor:pointer;font-size:.85rem;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="confirmTxtD" class="pw-strength-txt" style="color:var(--text-muted);"></div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4 pt-3 pf-form-actions"
                             style="border-top:1px solid var(--border); justify-content:flex-end;">
                            <button type="reset" class="btn-gf-secondary"><i class="fas fa-rotate-left"></i> Reset</button>
                            <button type="submit" name="ganti_password" class="btn-gf-primary"
                                    style="background:#EF4444; border-color:#EF4444;">
                                <i class="fas fa-key"></i> Perbarui Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Aktivitas Terakhir -->
            <div class="gf-card mt-3">
                <div class="gf-card-header">
                    <span class="gf-card-title">
                        <i class="fas fa-clock-rotate-left me-2 text-brand"></i>5 Transaksi Terakhir
                    </span>
                    <a href="index.php?page=transaksi"
                       style="font-size:.78rem; color:var(--brand); font-weight:600; text-decoration:none;">
                        Lihat Semua <i class="fas fa-arrow-right fa-xs"></i>
                    </a>
                </div>
                <div class="gf-card-body" style="padding:16px;">
                    <?php
                    // Reset query pointer
                    if (isset($q_recent)) mysqli_data_seek($q_recent, 0);
                    ?>
                    <?php if (mysqli_num_rows($q_recent) > 0): ?>
                        <?php while ($r = mysqli_fetch_assoc($q_recent)): ?>
                        <div class="pf-recent-item">
                            <div class="pf-recent-icon <?= $r['jenis'] === 'Pemasukan' ? 'income' : 'expense' ?>">
                                <i class="fas fa-<?= $r['jenis'] === 'Pemasukan' ? 'arrow-down' : 'arrow-up' ?>"></i>
                            </div>
                            <div class="pf-recent-meta">
                                <div class="pf-recent-kat"><?= htmlspecialchars($r['nama_kategori']) ?></div>
                                <div class="pf-recent-tgl"><?= date('d M Y', strtotime($r['tanggal'])) ?></div>
                            </div>
                            <div class="pf-recent-amt <?= $r['jenis'] === 'Pemasukan' ? 'income' : 'expense' ?>">
                                <?= $r['jenis'] === 'Pemasukan' ? '+' : '−' ?> Rp <?= number_format($r['jumlah'], 0, ',', '.') ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align:center; padding:28px 0; color:var(--text-muted);">
                            <i class="fas fa-receipt" style="font-size:1.8rem; opacity:.3; margin-bottom:8px; display:block;"></i>
                            <p style="font-size:.84rem; margin:0;">Belum ada transaksi.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /pf-desktop-sections -->

    </div><!-- /col main -->

</div><!-- /row -->

<script>
/* ═══════════════════════════════════════
   PROFIL PAGE JS
═══════════════════════════════════════ */

// ── Foto preview ──
const originalFotoSrc = document.getElementById('preview')?.src || '';
function handleFotoPreview(input) {
    if (input.files && input.files[0]) {
        const url = window.URL.createObjectURL(input.files[0]);
        const main  = document.getElementById('preview');
        const thumb = document.getElementById('preview_thumb');
        if (main)  main.src  = url;
        if (thumb) thumb.src = url;
    }
}
function resetPreview() {
    const main  = document.getElementById('preview');
    const thumb = document.getElementById('preview_thumb');
    if (main)  main.src  = originalFotoSrc;
    if (thumb) thumb.src = originalFotoSrc;
}

// ── Toggle password visibility ──
document.querySelectorAll('.pw-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const inp = document.getElementById(btn.dataset.target);
        if (!inp) return;
        const isText = inp.type === 'text';
        inp.type = isText ? 'password' : 'text';
        btn.querySelector('i').className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
    });
});

// ── Password strength checker ──
function strengthScore(val) {
    let s = 0;
    if (val.length >= 6) s++;
    if (val.length >= 10) s++;
    if (/[A-Z]/.test(val)) s++;
    if (/[0-9]/.test(val)) s++;
    if (/[^A-Za-z0-9]/.test(val)) s++;
    return s;
}
function applyStrength(barId, txtId, val) {
    const bar = document.getElementById(barId);
    const txt = document.getElementById(txtId);
    if (!bar || !txt) return;
    const s = strengthScore(val);
    const levels = [
        { w:'0%',   c:'#EF4444', l:'' },
        { w:'20%',  c:'#EF4444', l:'Sangat Lemah' },
        { w:'40%',  c:'#F97316', l:'Lemah' },
        { w:'60%',  c:'#EAB308', l:'Cukup' },
        { w:'80%',  c:'#22C55E', l:'Kuat' },
        { w:'100%', c:'#15803D', l:'Sangat Kuat' },
    ];
    const lv = levels[s] || levels[0];
    bar.style.width = lv.w;
    bar.style.background = lv.c;
    txt.textContent = lv.l;
    txt.style.color = lv.c;
}
function checkPasswordStrength(val)  { applyStrength('pwBar',  'pwTxt',  val); }
function checkPasswordStrengthD(val) { applyStrength('pwBarD', 'pwTxtD', val); }
function checkConfirm() {
    const b = document.getElementById('pw_baru')?.value;
    const c = document.getElementById('pw_konfirmasi')?.value;
    const t = document.getElementById('confirmTxt');
    if (!t) return;
    if (!c) { t.textContent = ''; return; }
    t.textContent = b === c ? '✓ Password cocok' : '✗ Password tidak cocok';
    t.style.color = b === c ? '#15803D' : '#EF4444';
}
function checkConfirmD() {
    const b = document.getElementById('pw_baru_d')?.value;
    const c = document.getElementById('pw_konfirmasi_d')?.value;
    const t = document.getElementById('confirmTxtD');
    if (!t) return;
    if (!c) { t.textContent = ''; return; }
    t.textContent = b === c ? '✓ Password cocok' : '✗ Password tidak cocok';
    t.style.color = b === c ? '#15803D' : '#EF4444';
}

// ── Submit profil: loading state ──
document.getElementById('formEditProfil')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnSimpanProfil');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    }
});

// ── Mobile Tabs ──
const tabBtns   = document.querySelectorAll('.pf-tab-btn');
const tabPanels = document.querySelectorAll('.pf-tab-panel');
tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        tabBtns.forEach(b => b.classList.remove('active'));
        tabPanels.forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        const panel = document.querySelector(`.pf-tab-panel[data-panel="${btn.dataset.tab}"]`);
        if (panel) panel.classList.add('active');
    });
});

// ── Anchor scroll ke password jika ada hash ──
if (window.location.hash === '#seksi-password') {
    const el = document.getElementById('seksi-password-desktop') || document.getElementById('seksi-password');
    if (el) setTimeout(() => el.scrollIntoView({ behavior:'smooth', block:'start' }), 300);
}
</script>
