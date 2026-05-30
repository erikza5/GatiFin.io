<?php
// Pastikan sesi aktif dan user sudah login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 1. Logika Simpan Akun Baru (Hanya untuk pemilik utama/pengguna)
if (isset($_POST['buat_akun'])) {
    if ($_SESSION['role'] === 'pengguna') {
        $username = mysqli_real_escape_string($koneksi, $_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = 'orang_tua';
        $created_by = $_SESSION['user_id'];

        $sql = "INSERT INTO users (username, password, role, created_by) 
                VALUES ('$username', '$password', '$role', '$created_by')";
        
        if (mysqli_query($koneksi, $sql)) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({ title: 'Berhasil!', text: 'Akun orang tua berhasil ditambahkan!', icon: 'success', confirmButtonColor: '#006D5B' })
                    .then(() => { window.location='index.php?page=pengaturan_akun'; });
                });
            </script>";
        }
    } else {
        // Jika orang tua mencoba menambah akun, beri peringatan
        echo "<script>Swal.fire('Akses Ditolak', 'Hanya pemilik utama yang bisa menambah akun.', 'error');</script>";
    }
}
?>

<div class="container-fluid p-4">
    <h3 class="fw-bold mb-4">Pengaturan Umum</h3>
    
    <div class="row">
        <!-- Manajemen Akses (Terlihat oleh semua, tapi hanya berfungsi untuk 'pengguna') -->
        <div class="col-md-6">
            <div class="card p-4 shadow-sm border-0 mb-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-user-cog me-2"></i>Manajemen Akses Keluarga</h5>
                <?php if ($_SESSION['role'] === 'pengguna'): ?>
                    <p class="text-muted small">Kelola akun tambahan yang memiliki akses untuk melihat data Anda.</p>
                    <form method="POST">
                        <input type="text" name="username" class="form-control mb-2" placeholder="Username Baru" required>
                        <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
                        <button type="submit" name="buat_akun" class="btn btn-primary w-100">Tambah Akun</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-2"></i> Anda login sebagai akun pendamping. Fitur manajemen akun hanya tersedia untuk pemilik utama.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabel Daftar Akun -->
        <div class="col-md-6">
            <div class="card p-4 shadow-sm border-0">
                <h5 class="fw-bold mb-3">Daftar Akun Terdaftar</h5>
                <table class="table table-hover">
                    <thead><tr><th>Username</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php
                        // Memastikan owner_id diambil dengan benar untuk kedua role
                        $owner_id = ($_SESSION['role'] === 'pengguna') ? $_SESSION['user_id'] : ($_SESSION['target_user_id'] ?? 0);
                        $q = mysqli_query($koneksi, "SELECT * FROM users WHERE role = 'orang_tua' AND created_by = '$owner_id'");
                        
                        while($d = mysqli_fetch_assoc($q)) {
                            echo "<tr>
                                    <td>{$d['username']}</td>
                                    <td>";
                            if ($_SESSION['role'] === 'pengguna') {
                                echo "<a href='#' onclick=\"confirmDelete('proses/hapus_akun.php?id={$d['id']}')\" class='btn btn-outline-danger btn-sm'>Hapus</a>";
                            } else {
                                echo "<span class='text-muted small'>Read-only</span>";
                            }
                            echo "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(url) {
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: "Data akun tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) { window.location.href = url; }
    });
}
</script>