<?php
if (isset($_POST['btn_tambah'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    $id_pemantau = $_SESSION['user_id'];

    // 1. Ambil data user target berdasarkan username
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $res = mysqli_query($koneksi, $sql);
    $user_target = mysqli_fetch_assoc($res);

    // 2. Verifikasi: Apakah user ada & password cocok?
    if ($user_target && password_verify($password, $user_target['password'])) {
        
        // 3. Update 'created_by' target menjadi milik pemantau
        $update = mysqli_query($koneksi, "UPDATE users SET created_by = '$id_pemantau' WHERE id = '".$user_target['id']."'");
        
        if ($update) {
            echo "<script>alert('Berhasil! Akun @$username sekarang dalam daftar pantau Anda.'); window.location='index.php?page=profil_pantau';</script>";
        }
    } else {
        $error = "Username atau Password tidak cocok!";
    }
}
?>

<div class="container-fluid py-4">
    <div class="card p-4 shadow-sm border-0 mx-auto" style="max-width: 450px; border-radius: 20px;">
        <h4 class="fw-bold mb-4 text-center">Verifikasi Akun Pantau</h4>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username Akun Target</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password Akun Target</label>
                <input type="password" name="password" class="form-control" required>
                <small class="text-muted">*Masukkan password akun yang ingin dipantau</small>
            </div>
            <button type="submit" name="btn_tambah" class="btn btn-success w-100 py-2 mt-3 fw-bold">
                Hubungkan Akun
            </button>
            <a href="index.php?page=profil_pantau" class="btn btn-link w-100 text-secondary mt-2">Batal</a>
        </form>
    </div>
</div>