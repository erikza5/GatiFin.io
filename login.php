<?php
session_start();
require_once 'config/koneksi.php';

if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$pesan = "";
$tipe_pesan = "";
$role_terpilih = $_POST['role_baru'] ?? 'pengguna';
$role_diizinkan = ['pengguna', 'orang_tua'];

if (isset($_POST['register'])) {
    $username_baru = mysqli_real_escape_string($koneksi, trim($_POST['username_baru'] ?? ''));
    $password_baru = trim($_POST['password_baru'] ?? '');
    $role_baru = $_POST['role_baru'] ?? 'pengguna';
    $role_terpilih = in_array($role_baru, $role_diizinkan, true) ? $role_baru : 'pengguna';

    if (empty($username_baru) || empty($password_baru)) {
        $pesan = "Username dan password tidak boleh kosong!";
        $tipe_pesan = "danger";
    } elseif (!in_array($role_baru, $role_diizinkan, true)) {
        $pesan = "Role akun tidak valid.";
        $tipe_pesan = "danger";
    } else {
        $cek_user = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username_baru' LIMIT 1");
        if (mysqli_num_rows($cek_user) > 0) {
            $pesan = "Username sudah digunakan.";
            $tipe_pesan = "danger";
        } else {
            $password_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
            $query_daftar = "INSERT INTO users (username, password, `nama lengkap`, role) VALUES ('$username_baru', '$password_hashed', '$username_baru', '$role_baru')";

            if (mysqli_query($koneksi, $query_daftar)) {
                $pesan = "Akun berhasil dibuat! Silakan login.";
                $tipe_pesan = "success";
                $role_terpilih = 'pengguna';
            } else {
                $pesan = "Akun gagal dibuat. Silakan coba lagi.";
                $tipe_pesan = "danger";
            }
        }
    }
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username' LIMIT 1");

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['login']          = true;
            $_SESSION['user_id']        = $row['id'];
            $_SESSION['username']       = $row['username'];
            $_SESSION['role']           = $row['role'];
            $_SESSION['target_user_id'] = ($row['role'] === 'orang_tua') ? ($row['created_by'] ?? null) : $row['id'];
            header("Location: index.php");
            exit;
        }
    }

    $pesan = "Username atau password salah!";
    $tipe_pesan = "danger";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GATIFIN - Masuk ke Akun Anda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #00a87b;
            --brand-dark: #007a5a;
            --accent: #2563eb;
            --ink: #0d1f1a;
            --muted: #64756f;
            --soft: #f4f8f6;
            --surface: #ffffff;
            --line: #dfe9e4;
            --danger: #dc2626;
            --success: #16a34a;
            --shadow: 0 24px 70px rgba(13, 31, 26, .16);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: var(--ink);
            background:
                linear-gradient(135deg, rgba(0, 168, 123, .12), transparent 36%),
                linear-gradient(315deg, rgba(37, 99, 235, .10), transparent 38%),
                #eef5f2;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .auth-shell {
            width: min(1080px, 100%);
            min-height: 660px;
            display: grid;
            grid-template-columns: 1.06fr .94fr;
            background: rgba(255, 255, 255, .88);
            border: 1px solid rgba(255, 255, 255, .78);
            border-radius: 26px;
            box-shadow: var(--shadow);
            overflow: hidden;
            backdrop-filter: blur(18px);
        }

        .brand-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 44px;
            color: #fff;
            background:
                linear-gradient(145deg, rgba(13, 31, 26, .96), rgba(0, 122, 90, .90)),
                url('assets/img/logo.png') center / 62% no-repeat;
            isolation: isolate;
        }

        .brand-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,0));
            z-index: -1;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            width: fit-content;
        }

        .brand-mark img {
            width: 42px;
            height: 42px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .brand-name {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: .02em;
        }

        .hero-copy { max-width: 440px; }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            color: rgba(255,255,255,.82);
            font-size: .78rem;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .hero-copy h1 {
            font-size: clamp(2rem, 4vw, 3.35rem);
            line-height: 1.05;
            font-weight: 800;
            letter-spacing: 0;
            margin: 0 0 18px;
        }

        .hero-copy p {
            margin: 0;
            color: rgba(255,255,255,.76);
            line-height: 1.75;
            font-size: .98rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .feature-card {
            min-height: 112px;
            padding: 16px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.09);
        }

        .feature-card i {
            display: block;
            margin-bottom: 12px;
            color: #8ff0d4;
            font-size: 1rem;
        }

        .feature-card span {
            display: block;
            font-size: .78rem;
            font-weight: 700;
            line-height: 1.45;
            color: rgba(255,255,255,.82);
        }

        .auth-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px 46px;
            background: var(--surface);
        }

        .auth-header { margin-bottom: 28px; }
        .auth-header h2 { font-size: 1.7rem; font-weight: 800; margin: 0 0 8px; }
        .auth-header p { color: var(--muted); margin: 0; font-size: .92rem; }

        .gf-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 15px;
            border-radius: 14px;
            font-size: .88rem;
            font-weight: 700;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }

        .gf-alert.danger { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .gf-alert.success { background: #ecfdf5; border-color: #bbf7d0; color: #166534; }

        .field-group { margin-bottom: 18px; }
        .field-label { display: block; font-size: .78rem; font-weight: 800; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 8px; }
        .field-wrap { position: relative; }
        .field-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #8aa098; font-size: .92rem; pointer-events: none; }

        .field-input {
            width: 100%;
            height: 50px;
            background: var(--soft);
            border: 1.5px solid var(--line);
            border-radius: 14px;
            padding: 0 44px 0 43px;
            font: 600 .92rem 'Plus Jakarta Sans', sans-serif;
            color: var(--ink);
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .field-input:focus {
            border-color: var(--brand);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(0, 168, 123, .13);
        }

        .toggle-pw {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #8aa098;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-pw:hover { background: #e7f0ec; color: var(--brand-dark); }

        .btn-auth {
            width: 100%;
            height: 50px;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            color: #fff;
            font: 800 .94rem 'Plus Jakarta Sans', sans-serif;
            box-shadow: 0 12px 28px rgba(0, 168, 123, .28);
            transition: transform .16s ease, box-shadow .16s ease;
        }

        .btn-auth:hover { transform: translateY(-1px); box-shadow: 0 16px 34px rgba(0, 168, 123, .34); }
        .btn-auth:active { transform: translateY(0); }

        .auth-link {
            text-align: center;
            margin: 24px 0 0;
            color: var(--muted);
            font-size: .9rem;
        }

        .auth-link a { color: var(--brand-dark); font-weight: 800; text-decoration: none; }
        .auth-link a:hover { color: var(--brand); }

        .gf-modal .modal-content {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 28px 80px rgba(13, 31, 26, .22);
            overflow: hidden;
        }

        .gf-modal .modal-header {
            padding: 24px 28px 12px;
            border: 0;
        }

        .gf-modal .modal-title { font-weight: 800; }
        .gf-modal .modal-body { padding: 12px 28px 26px; }
        .gf-modal .modal-footer { padding: 18px 28px; border-color: var(--line); background: var(--soft); }

        .role-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .role-option input { position: absolute; opacity: 0; pointer-events: none; }

        .role-card {
            min-height: 124px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 16px;
            border: 1.5px solid var(--line);
            border-radius: 16px;
            background: #fff;
            cursor: pointer;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease, background .2s ease;
        }

        .role-card i {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: var(--brand-dark);
            background: #e8f8f3;
        }

        .role-card strong { font-size: .94rem; }
        .role-card small { color: var(--muted); line-height: 1.45; font-weight: 600; }

        .role-option input:checked + .role-card {
            border-color: var(--brand);
            background: #f2fbf8;
            box-shadow: 0 0 0 4px rgba(0, 168, 123, .12);
        }

        .role-option input:focus-visible + .role-card { outline: 3px solid rgba(37, 99, 235, .28); outline-offset: 2px; }
        .role-card:hover { transform: translateY(-1px); border-color: #a9cbc0; }

        .btn-ghost {
            height: 42px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            color: var(--muted);
            font-weight: 800;
            padding: 0 18px;
        }

        .btn-register {
            width: auto;
            height: 42px;
            padding: 0 22px;
            box-shadow: none;
        }

        @media (max-width: 900px) {
            .auth-shell { grid-template-columns: 1fr; min-height: auto; }
            .brand-panel { display: none; }
            .auth-panel { padding: 42px 30px; }
        }

        @media (max-width: 520px) {
            body { padding: 14px; }
            .auth-shell { border-radius: 20px; }
            .auth-panel { padding: 34px 20px; }
            .role-grid { grid-template-columns: 1fr; }
            .gf-modal .modal-header, .gf-modal .modal-body, .gf-modal .modal-footer { padding-left: 20px; padding-right: 20px; }
            .gf-modal .modal-footer { align-items: stretch; flex-direction: column-reverse; }
            .btn-register, .btn-ghost { width: 100%; }
        }
    </style>
</head>
<body>

<main class="auth-shell">
    <section class="brand-panel" aria-label="Ringkasan GATIFIN">
        <div class="brand-mark">
            <img src="assets/img/logo_side.png" alt="Logo GATIFIN">
            <span class="brand-name">GATIFIN</span>
        </div>

        <div class="hero-copy">
            <div class="eyebrow"><i class="fas fa-wallet"></i> Financial tracking made simple</div>
            <h1>Atur Uang Tanpa Ribet, Pantau Semua dalam Satu Tempat</h1>
            <p>Pantau transaksi, analisis pengeluaran, dan bagikan akses lewat satu aplikasi yang cepat dan mudah dipahami.</p>
        </div>

        <div class="feature-grid">
            <div class="feature-card">
                <i class="fas fa-chart-line"></i>
                <span>Analisis finansial otomatis</span>
            </div>
            <div class="feature-card">
                <i class="fas fa-receipt"></i>
                <span>Scan struk dan nota</span>
            </div>
            <div class="feature-card">
                <i class="fas fa-user-shield"></i>
                <span>Akses pantau orang tua</span>
            </div>
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-header">
            <h2>Selamat datang kembali</h2>
            <p>Masuk untuk melanjutkan ke dashboard GATIFIN.</p>
        </div>

        <?php if (!empty($pesan)): ?>
            <div class="gf-alert <?= htmlspecialchars($tipe_pesan) ?>">
                <i class="fas fa-<?= $tipe_pesan === 'success' ? 'check-circle' : 'circle-exclamation' ?>"></i>
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="field-group">
                <label class="field-label" for="username">Username</label>
                <div class="field-wrap">
                    <i class="fas fa-user field-icon"></i>
                    <input type="text" name="username" id="username" class="field-input" placeholder="Masukkan username" autocomplete="username" required>
                </div>
            </div>

            <div class="field-group">
                <label class="field-label" for="loginPw">Password</label>
                <div class="field-wrap">
                    <i class="fas fa-lock field-icon"></i>
                    <input type="password" name="password" id="loginPw" class="field-input" placeholder="Masukkan password" autocomplete="current-password" required>
                    <button type="button" class="toggle-pw" onclick="togglePw('loginPw', this)" aria-label="Tampilkan password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" name="login" class="btn-auth">
                <i class="fas fa-arrow-right-to-bracket me-2"></i>Masuk ke Sistem
            </button>
        </form>

        <p class="auth-link">
            Belum punya akun?
            <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Daftar Sekarang</a>
        </p>
    </section>
</main>

<div class="modal fade gf-modal" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Buat Akun Baru</h5>
                    <p class="text-muted small mb-0 mt-1">Pilih jenis akun yang sesuai sebelum mendaftar.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <form method="POST" action="">
                <div class="modal-body">
                    <label class="field-label">Role Akun</label>
                    <div class="role-grid" role="radiogroup" aria-label="Pilih role akun">
                        <label class="role-option">
                            <input type="radio" name="role_baru" value="pengguna" <?= $role_terpilih === 'pengguna' ? 'checked' : '' ?>>
                            <span class="role-card">
                                <i class="fas fa-user"></i>
                                <strong>Pengguna</strong>
                                <small>Untuk mencatat transaksi dan mengelola keuangan pribadi.</small>
                            </span>
                        </label>

                        <label class="role-option">
                            <input type="radio" name="role_baru" value="orang_tua" <?= $role_terpilih === 'orang_tua' ? 'checked' : '' ?>>
                            <span class="role-card">
                                <i class="fas fa-user-shield"></i>
                                <strong>Orang Tua</strong>
                                <small>Untuk mencatat transaksi pribadimu dan mengawasi keuangan keluargamu.</small>
                            </span>
                        </label>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="usernameBaru">Username</label>
                        <div class="field-wrap">
                            <i class="fas fa-user field-icon"></i>
                            <input type="text" name="username_baru" id="usernameBaru" class="field-input" placeholder="Buat username" autocomplete="username" required>
                        </div>
                    </div>

                    <div class="field-group mb-0">
                        <label class="field-label" for="regPw">Password</label>
                        <div class="field-wrap">
                            <i class="fas fa-lock field-icon"></i>
                            <input type="password" name="password_baru" id="regPw" class="field-input" placeholder="Minimal 6 karakter" autocomplete="new-password" required>
                            <button type="button" class="toggle-pw" onclick="togglePw('regPw', this)" aria-label="Tampilkan password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="register" class="btn-auth btn-register">
                        <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    const isHidden = input.type === 'password';

    input.type = isHidden ? 'text' : 'password';
    icon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
    btn.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
}
</script>
</body>
</html>
