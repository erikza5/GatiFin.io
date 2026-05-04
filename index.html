<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GatiFin - Solusi Keuangan Cerdas</title>
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- JS Dependencies for Snapshot -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        :root { --primary: #4e73df; --bg-light: #f8f9fc; --sidebar-bg: #2e59d9; }
        
        /* --- OPTIMASI DARK MODE --- */
        body.dark-mode { 
            --bg-light: #121212; 
            background: var(--bg-light); 
            color: #ffffff; 
        }
        body.dark-mode .sidebar { background: #000000; }
        body.dark-mode .card, body.dark-mode .modal-content { 
            background: #1e1e1e; 
            color: #ffffff; 
            border: 1px solid #333333; 
        }
        body.dark-mode .form-control, 
        body.dark-mode .form-select { 
            background: #2d2d2d; 
            border-color: #444444; 
            color: #ffffff !important; 
        }
        body.dark-mode .form-label { color: #ffffff; font-weight: 600; }
        body.dark-mode #auth-screen .card h5 { color: #ffffff !important; }
        body.dark-mode .text-muted { color: #bbbbbb !important; }
        body.dark-mode hr { border-color: #444444; }
        body.dark-mode .table { color: #ffffff; border-color: #333333; }
        body.dark-mode .table-light { background: #2d2d2d; color: #ffffff; border-color: #444444; }
        body.dark-mode .text-success { color: #2ecc71 !important; } 
        body.dark-mode .text-danger { color: #ff7675 !important; }

        /* General Styles */
        body { background: var(--bg-light); font-family: 'Segoe UI', sans-serif; overflow-x: hidden; transition: background 0.3s; }
        #auth-screen { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: var(--sidebar-bg); z-index: 9999; display: flex; align-items: center; justify-content: center; }
        .app-logo { width: 120px; height: auto; transition: 0.3s; }
        .menu-section { display: none; opacity: 0; transform: translateY(20px); transition: all 0.4s ease; }
        .menu-section.active { display: block; opacity: 1; transform: translateY(0); }
        .sidebar { min-height: 100vh; background: var(--sidebar-bg); color: white; z-index: 1000; }
        .nav-link { color: rgba(255,255,255,0.7); cursor: pointer; border-radius: 10px; margin-bottom: 5px; transition: 0.2s; }
        .nav-link.active { background: rgba(255,255,255,0.2); color: white; font-weight: bold; }
        .nav-link:hover { background: rgba(255,255,255,0.1); color: white; transform: translateX(5px); }
        .card { border: none; border-radius: 15px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
        .btn-delete { color: #e74a3b; cursor: pointer; transition: 0.2s; }
        .btn-delete:hover { transform: scale(1.2); }
        
        /* Profile Styles */
        .sidebar-profile { width: 80px; height: 80px; object-fit: cover; border: 3px solid rgba(255,255,255,0.3); }
        .profile-header { position: relative; display: inline-block; }
        #profile-img-display { width: 150px; height: 150px; object-fit: cover; border: 5px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .fingerprint-icon { font-size: 3rem; cursor: pointer; transition: 0.3s; color: #fff; }
        .fingerprint-icon:hover { transform: scale(1.1); color: #1cc88a; }
        
        /* Stats Panel */
        .stat-card { cursor: pointer; transition: transform 0.2s; }
        .stat-card:hover { transform: scale(1.03); }
        #details-panel { display: none; margin-bottom: 20px; }

        /* --- STYLING SNAPSHOT REBRANDING (GatiFin) --- */
        .modal-content-snapshot { border: none; border-radius: 30px; overflow: hidden; background: #fff !important; }
        
        #capture-area { 
            position: relative;
            padding: 50px 35px;
            color: #ffffff !important;
            text-align: center;
            overflow: hidden;
            min-height: 420px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .bg-snapshot-dark { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important; }
        .bg-snapshot-blue { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%) !important; }

        .decor-circle {
            position: absolute;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            z-index: 0;
        }
        .decor-1 { width: 220px; height: 220px; top: -60px; right: -60px; }
        .decor-2 { width: 180px; height: 180px; bottom: -40px; left: -40px; }

        .snapshot-content { position: relative; z-index: 1; }
        .snapshot-logo { 
            width: 70px; 
            margin-bottom: 15px; 
            filter: brightness(0) invert(1); 
        }
        
        .glass-card-snapshot { 
            background: rgba(255, 255, 255, 0.12) !important;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 20px;
        }

        #capture-area h2, #capture-area h4, #capture-area h5, #capture-area p, #capture-area small {
            color: #ffffff !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .text-contrast-success { color: #4ade80 !important; font-weight: 800; }
        .text-contrast-danger { color: #f87171 !important; font-weight: 800; }
    </style>
</head>
<body>

<!-- SECTION: LOGIN / AUTH -->
<div id="auth-screen">
    <div class="card p-4 shadow-lg animate__animated animate__zoomIn" style="width: 350px;">
        <div class="text-center mb-4">
            <img src="1.png" class="app-logo mb-2" alt="Logo">
            <h5 class="fw-bold text-primary">Selamat Datang di GatiFin</h5>
        </div>
        <form id="loginForm">
            <div class="mb-3">
                <input type="text" id="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="password" id="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold mb-3">Masuk</button>
        </form>
        <div class="text-center">
            <hr>
            <p class="small text-muted mb-2">Atau Login Cepat</p>
            <div id="biometric-area" onclick="authenticateBiometric()" class="fingerprint-icon">
                <i class="bi bi-fingerprint text-primary"></i>
            </div>
            <p class="mt-3 small">Belum punya akun? <a href="#" onclick="registerSimulated()">Daftar Disini</a></p>
        </div>
    </div>
</div>

<!-- MODAL PEMILIH RENTANG SNAPSHOT -->
<div class="modal fade" id="datePickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="fw-bold mb-0">Pengaturan Snapshot</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="small text-muted mb-2">Tipe Laporan:</label>
                    <select id="snap-type" class="form-select rounded-pill mb-3" onchange="toggleDateInput()">
                        <option value="daily">Harian</option>
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                    
                    <label class="small text-muted mb-2">Pilih Waktu:</label>
                    <input type="date" id="input-snap-date" class="form-control rounded-pill">
                </div>
                <button class="btn btn-primary w-100 rounded-pill fw-bold" onclick="confirmSnapshotDate()">
                    Buat Snapshot
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: SNAPSHOT REPORT -->
<div class="modal fade" id="snapshotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-snapshot shadow-lg">
            <div class="modal-body p-0">
                <div id="capture-area" class="bg-snapshot-dark">
                    <div class="decor-circle decor-1"></div>
                    <div class="decor-circle decor-2"></div>

                    <div class="snapshot-content">
                        <img src="Logo.png" class="snapshot-logo" alt="GatiFin Logo">
                        <h2 class="fw-bold mb-1" style="letter-spacing: 2px;">GatiFin</h2>
                        <div style="width: 45px; height: 4px; background: #38bdf8; margin: 5px auto 25px;"></div>
                        
                        <div class="mb-4">
                            <small class="opacity-75 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1.5px;">Ringkasan Keuangan</small>
                            <h5 id="snap-date-label" class="fw-bold mt-1">Memuat...</h5>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="glass-card-snapshot">
                                    <small class="d-block opacity-75 mb-1">Pemasukan</small>
                                    <h4 id="snap-in-val" class="fw-bold mb-0 text-contrast-success">Rp 0</h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="glass-card-snapshot">
                                    <small class="d-block opacity-75 mb-1">Pengeluaran</small>
                                    <h4 id="snap-out-val" class="fw-bold mb-0 text-contrast-danger">Rp 0</h4>
                                </div>
                            </div>
                        </div>
                        <p class="mb-0 small opacity-75 italic fw-light">"Cerdas Mengelola bersama GatiFin AI"</p>
                    </div>
                </div>
                
                <div class="p-4 bg-white">
                    <div class="mb-3 text-center">
                        <label class="small fw-bold text-muted d-block mb-2">Pilih Tema Background:</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="snapTheme" id="themeDark" value="dark" checked onchange="updateSnapTheme('dark')">
                            <label class="btn btn-outline-dark btn-sm" for="themeDark">Gelap Modern</label>
                            
                            <input type="radio" class="btn-check" name="snapTheme" id="themeBlue" value="blue" onchange="updateSnapTheme('blue')">
                            <label class="btn btn-outline-primary btn-sm" for="themeBlue">Biru Profesional</label>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <button class="btn btn-dark w-100 rounded-pill fw-bold" onclick="downloadSnapshot()">
                                <i class="bi bi-download me-2"></i>Simpan
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-primary w-100 rounded-pill fw-bold" onclick="shareSnapshot()">
                                <i class="bi bi-share me-2"></i>Bagikan
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-link btn-sm w-100 mt-2 text-muted" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid" id="main-app" style="display: none;">
    <div class="row">
        <!-- Sidebar Navigation -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar py-4 position-fixed">
            <div class="text-center mb-3 animate__animated animate__fadeIn">
                <img src="Logo.png" id="main-app-logo" class="app-logo" alt="GatiFin Logo">
            </div>
            
            <div class="text-center mb-4 animate__animated animate__fadeIn">
                <img src="https://via.placeholder.com/150" id="sidebar-img-display" class="sidebar-profile rounded-circle mb-2 shadow-sm" alt="Profile">
                <div class="fw-bold text-white mb-0" id="sidebar-name-display" style="font-size: 0.95rem;">User</div>
                <div class="text-white opacity-50 mb-2" id="sidebar-username-display" style="font-size: 0.75rem;">@username</div>
            </div>

            <ul class="nav flex-column px-2">
                <li class="nav-item"><a class="nav-link active" onclick="showMenu('beranda', this)" data-key="nav-home"><i class="bi bi-house-door me-2"></i> Beranda</a></li>
                <li class="nav-item"><a class="nav-link" onclick="showMenu('tambah', this)" data-key="nav-add"><i class="bi bi-plus-square me-2"></i> Catatan Keuangan</a></li>
                <li class="nav-item"><a class="nav-link" onclick="showMenu('riwayat', this)" data-key="nav-history"><i class="bi bi-clock-history me-2"></i> Riwayat</a></li>
                <li class="nav-item"><a class="nav-link" onclick="showMenu('analisis', this)" data-key="nav-analysis"><i class="bi bi-pie-chart me-2"></i> Analisis</a></li>
                <li class="nav-item"><a class="nav-link" onclick="showMenu('profil', this)" data-key="nav-profile"><i class="bi bi-person me-2"></i> Profil</a></li>
                <li class="nav-item mt-4"><a class="nav-link" onclick="showMenu('settings', this)" data-key="nav-settings"><i class="bi bi-gear me-2"></i> Pengaturan</a></li>
            </ul>
        </nav>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            
            <!-- SECTION: BERANDA -->
            <section id="beranda" class="menu-section active">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold mb-0" data-key="dash-title">Beranda</h2>
                    <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="triggerSnapshotFlow()">
                        <i class="bi bi-camera-fill me-2"></i> GatiFin Snapshot
                    </button>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white p-4 text-center stat-card" onclick="showDetails('all')">
                            <small class="opacity-75" data-key="card-balance">Saldo Keseluruhan</small>
                            <h2 id="disp-saldo" class="fw-bold">Rp 0</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white p-4 text-center stat-card" onclick="showDetails('masuk')">
                            <small class="opacity-75" data-key="card-income">Total Pemasukan</small>
                            <h2 id="disp-masuk" class="fw-bold">Rp 0</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white p-4 text-center stat-card" onclick="showDetails('keluar')">
                            <small class="opacity-75" data-key="card-expense">Total Pengeluaran</small>
                            <h2 id="disp-keluar" class="fw-bold">Rp 0</h2>
                        </div>
                    </div>
                </div>

                <div id="details-panel" class="animate__animated animate__fadeIn">
                    <div class="card p-3 border-0 shadow-sm mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0" id="details-title">Rincian Transaksi</h6>
                            <button class="btn-close" onclick="closeDetails()"></button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <tbody id="details-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card p-3 border-start border-primary border-4 shadow-sm">
                    <div class="d-flex align-items-center mb-2">
                        <div class="spinner-grow spinner-grow-sm text-primary me-2" role="status"></div>
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-robot me-2"></i> GatiFin AI Advisor</h6>
                    </div>
                    <p id="ai-advice" class="mb-0 small text-dark opacity-75" data-key="ai-empty" style="line-height: 1.6;">
                        Belum ada data transaksi untuk dianalisis.
                    </p>
                </div>
            </section>

            <!-- SECTION: TAMBAH CATATAN -->
            <section id="tambah" class="menu-section">
                <h2 class="fw-bold mb-4" data-key="add-title">Tambah Catatan Keuangan</h2>
                <div class="card p-4 col-lg-7">
                    <form id="financeForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold" data-key="form-type">Tipe Transaksi</label>
                                <select class="form-select" id="type" onchange="updateCategories()" required>
                                    <option value="masuk" data-key="opt-in">Pemasukan (+)</option>
                                    <option value="keluar" data-key="opt-out">Pengeluaran (-)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold" data-key="form-amount">Nominal</label>
                                <input type="number" class="form-control" id="amount" placeholder="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold" data-key="form-cat">Kategori</label>
                            <select class="form-select" id="category" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold" data-key="form-note">Keterangan</label>
                            <input type="text" class="form-control" id="note" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold" data-key="form-date">Tanggal</label>
                            <input type="date" class="form-control" id="date" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" data-key="btn-save">Simpan Transaksi</button>
                    </form>
                </div>
            </section>

            <!-- SECTION: RIWAYAT -->
            <section id="riwayat" class="menu-section">
                <h2 class="fw-bold mb-4" data-key="hist-title">Riwayat Transaksi</h2>
                <div class="card shadow-sm overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th data-key="th-date">Tanggal</th>
                                    <th data-key="th-note">Keterangan</th>
                                    <th data-key="th-cat">Kategori</th>
                                    <th data-key="th-amount">Nominal</th>
                                    <th data-key="th-act">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- SECTION: ANALISIS -->
            <section id="analisis" class="menu-section">
                <h2 class="fw-bold mb-4" data-key="analysis-title">Analisis Keuangan</h2>
                <div class="row g-4">
                    <div class="col-md-12">
                        <div class="card p-4 text-center">
                            <h6 class="fw-bold mb-4" data-key="chart-flow">Perbandingan Arus Kas (In vs Out)</h6>
                            <div style="height: 250px;"><canvas id="mainChart"></canvas></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-4">
                            <h6 class="fw-bold mb-4 text-danger"><i class="bi bi-arrow-down-circle me-2"></i> Kategori Pengeluaran</h6>
                            <div style="height: 300px;"><canvas id="categoryChartOut"></canvas></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-4">
                            <h6 class="fw-bold mb-4 text-success"><i class="bi bi-arrow-up-circle me-2"></i> Kategori Pemasukan</h6>
                            <div style="height: 300px;"><canvas id="categoryChartIn"></canvas></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: PROFIL -->
            <section id="profil" class="menu-section">
                <h2 class="fw-bold mb-4" data-key="prof-title">Profil Saya</h2>
                <div class="card p-4 shadow-sm border-0">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <div class="profile-header position-relative d-inline-block">
                                <img src="https://via.placeholder.com/150" id="profile-img-display" class="rounded-circle shadow">
                                <label for="upload-photo" class="btn btn-primary btn-sm position-absolute bottom-0 end-0 rounded-circle shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                                <input type="file" id="upload-photo" hidden accept="image/*" onchange="previewImage(event)">
                            </div>
                            <p class="small text-muted mt-3">Klik ikon kamera untuk mengubah foto</p>
                        </div>
                        
                        <div class="col-md-8">
                            <form id="profileForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold">Username</label>
                                        <input type="text" class="form-control bg-light" id="p-username" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold" data-key="prof-name">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="p-name" placeholder="Masukkan nama lengkap">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold" data-key="prof-job">Pekerjaan</label>
                                        <input type="text" class="form-control" id="p-job" placeholder="Contoh: Freelancer">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label small fw-bold" data-key="prof-age">Usia</label>
                                        <input type="number" class="form-control" id="p-age" placeholder="0">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-primary px-5 fw-bold rounded-pill" onclick="saveProfileData()" data-key="btn-save-prof">
                                        <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: PENGATURAN -->
            <section id="settings" class="menu-section">
                <h2 class="fw-bold mb-4" data-key="sett-title">Pengaturan</h2>
                <div class="row">
                    <div class="col-lg-7">
                        <div class="card p-4 mb-4">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold small text-uppercase" data-key="sett-theme">Tampilan</label>
                                    <div class="d-flex gap-2">
                                        <!-- Perbaikan tombol dengan pemanggilan fungsi setTheme -->
                                        <button class="btn btn-sm btn-outline-primary" id="btn-light" onclick="setTheme('light')">Light</button>
                                        <button class="btn btn-sm btn-outline-dark" id="btn-dark" onclick="setTheme('dark')">Dark</button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold small text-uppercase" data-key="sett-lang">Bahasa / Language</label>
                                    <select class="form-select" id="setting-lang" onchange="changeLanguage(this.value)">
                                        <option value="id">Bahasa Indonesia</option>
                                        <option value="en">English</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-uppercase" data-key="sett-curr">Mata Uang</label>
                                    <select class="form-select" id="setting-currency" onchange="updateCurrency(this.value)">
                                        <option value="Rp">IDR (Rp)</option>
                                        <option value="$">USD ($)</option>
                                        <option value="€">EUR (€)</option>
                                        <option value="¥">JPY (¥)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button class="btn btn-outline-primary btn-sm w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#modalPassword">
                                        <i class="bi bi-key me-2"></i> Ubah Kata Sandi
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card p-4">
                            <label class="form-label fw-bold small text-uppercase text-danger">Area Berbahaya</label>
                            <p class="small text-muted">Hapus seluruh catatan secara permanen.</p>
                            <button class="btn btn-outline-danger btn-sm w-100 mb-3" onclick="clearData()" data-key="btn-reset">Hapus Semua Data Transaksi</button>
                            <button class="btn btn-danger btn-sm fw-bold w-100" onclick="logout()"><i class="bi bi-box-arrow-right me-2"></i> Keluar</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<!-- MODAL: UBAH KATA SANDI -->
<div class="modal fade" id="modalPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock me-2"></i> Ubah Kata Sandi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Kata Sandi Lama</label>
                    <input type="password" id="old-pass" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Kata Sandi Baru</label>
                    <input type="password" id="new-pass" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm px-4" onclick="changeUserPassword()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const i18n = {
        id: {
            "nav-home": "Beranda", "nav-add": "Catatan Keuangan", "nav-history": "Riwayat", "nav-analysis": "Analisis", "nav-profile": "Profil", "nav-settings": "Pengaturan",
            "dash-title": "Beranda", "card-balance": "Saldo Keseluruhan", "card-income": "Total Pemasukan", "card-expense": "Total Pengeluaran", "ai-empty": "Belum ada data transaksi.",
            "add-title": "Tambah Catatan Keuangan", "form-type": "Tipe Transaksi", "form-amount": "Nominal", "form-cat": "Kategori", "form-note": "Keterangan", "form-date": "Tanggal",
            "opt-in": "Pemasukan (+)", "opt-out": "Pengeluaran (-)", "btn-save": "Simpan Transaksi", "hist-title": "Riwayat Transaksi", "th-date": "Tanggal", "th-note": "Keterangan", "th-cat": "Kategori", "th-amount": "Nominal", "th-act": "Aksi",
            "analysis-title": "Analisis Keuangan", "chart-flow": "Arus Kas", "chart-cat": "Kategori", "prof-title": "Profil Saya", "prof-name": "Nama Lengkap", "prof-job": "Pekerjaan", "prof-age": "Usia",
            "btn-save-prof": "Simpan Perubahan", "sett-title": "Pengaturan", "sett-theme": "Tampilan", "sett-lang": "Bahasa", "sett-curr": "Mata Uang", "btn-reset": "Hapus Semua Data"
        },
        en: {
            "nav-home": "Home", "nav-add": "Finance Records", "nav-history": "History", "nav-analysis": "Analysis", "nav-profile": "Profile", "nav-settings": "Settings",
            "dash-title": "Home", "card-balance": "Total Balance", "card-income": "Total Income", "card-expense": "Total Expense", "ai-empty": "No transaction data yet.",
            "add-title": "Add Record", "form-type": "Transaction Type", "form-amount": "Amount", "form-cat": "Category", "form-note": "Note", "form-date": "Date",
            "opt-in": "Income (+)", "opt-out": "Expense (-)", "btn-save": "Save Transaction", "hist-title": "Transaction History", "th-date": "Date", "th-note": "Note", "th-cat": "Category", "th-amount": "Amount", "th-act": "Action",
            "analysis-title": "Financial Analysis", "chart-flow": "Cash Flow", "chart-cat": "Category", "prof-title": "My Profile", "prof-name": "Full Name", "prof-job": "Job", "prof-age": "Age",
            "btn-save-prof": "Save Changes", "sett-title": "Settings", "sett-theme": "Theme", "sett-lang": "Language", "sett-curr": "Currency", "btn-reset": "Clear All Data"
        }
    };

    let currentUser = ""; 
    let transactions = [];
    let mainChart, categoryChartOut, categoryChartIn;
    let currentCurrency = "Rp";
    let currentLang = "id";
    let snapModal;
    let datePickerModal;

    /* --- PERBAIKAN DARK MODE & BAHASA ---[cite: 5] */
    function setTheme(theme) {
        const body = document.body;
        const btnLight = document.getElementById('btn-light');
        const btnDark = document.getElementById('btn-dark');

        if (theme === 'dark') {
            body.classList.add('dark-mode');
            localStorage.setItem('fintrack_theme', 'dark');
            btnDark?.classList.replace('btn-outline-dark', 'btn-dark');
            btnLight?.classList.replace('btn-primary', 'btn-outline-primary');
        } else {
            body.classList.remove('dark-mode');
            localStorage.setItem('fintrack_theme', 'light');
            btnLight?.classList.replace('btn-outline-primary', 'btn-primary');
            btnDark?.classList.replace('btn-dark', 'btn-outline-dark');
        }
        if (typeof mainChart !== 'undefined') refreshCharts();
    }

    function changeLanguage(lang) {
        currentLang = lang;
        localStorage.setItem('fintrack_lang', lang);
        document.querySelectorAll('[data-key]').forEach(el => {
            const key = el.getAttribute('data-key');
            if (i18n[lang][key]) el.innerText = i18n[lang][key];
        });
    }

    function updateCurrency(curr) {
        currentCurrency = curr;
        localStorage.setItem('fintrack_currency', curr);
        updateUI();
    }

    function initSettings() {
        const savedTheme = localStorage.getItem('fintrack_theme') || 'light';
        setTheme(savedTheme);
        const savedLang = localStorage.getItem('fintrack_lang') || 'id';
        document.getElementById('setting-lang').value = savedLang;
        changeLanguage(savedLang);
        const savedCurr = localStorage.getItem('fintrack_currency') || 'Rp';
        document.getElementById('setting-currency').value = savedCurr;
        currentCurrency = savedCurr;
    }

    /* --- SNAPSHOT LOGIC --- */
    function triggerSnapshotFlow() {
        if (!datePickerModal) datePickerModal = new bootstrap.Modal(document.getElementById('datePickerModal'));
        document.getElementById('snap-type').value = 'daily';
        toggleDateInput();
        datePickerModal.show();
    }

    function toggleDateInput() {
        const type = document.getElementById('snap-type').value;
        const input = document.getElementById('input-snap-date');
        if(type === 'monthly') input.type = 'month';
        else if(type === 'yearly') { input.type = 'number'; input.value = new Date().getFullYear(); }
        else { input.type = 'date'; input.valueAsDate = new Date(); }
    }

    function confirmSnapshotDate() {
        const type = document.getElementById('snap-type').value;
        const val = document.getElementById('input-snap-date').value;
        if(!val) return alert("Pilih waktu!");
        let filtered = []; let label = "";
        if(type === 'daily') {
            filtered = transactions.filter(t => t.date === val);
            label = new Date(val).toLocaleDateString('id-ID', { dateStyle: 'full' });
        } else if(type === 'monthly') {
            filtered = transactions.filter(t => t.date.startsWith(val));
            const [y, m] = val.split('-'); label = new Date(y, m-1).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        } else if(type === 'yearly') {
            filtered = transactions.filter(t => t.date.startsWith(val)); label = "Tahun " + val;
        }
        if(filtered.length === 0) return alert("Tidak ada data pada periode tersebut.");
        datePickerModal.hide();
        const inSum = filtered.filter(t => t.type === 'masuk').reduce((a, b) => a + b.amount, 0);
        const outSum = filtered.filter(t => t.type === 'keluar').reduce((a, b) => a + b.amount, 0);
        document.getElementById('snap-date-label').innerText = label;
        document.getElementById('snap-in-val').innerText = `${currentCurrency} ${inSum.toLocaleString()}`;
        document.getElementById('snap-out-val').innerText = `${currentCurrency} ${outSum.toLocaleString()}`;
        if (!snapModal) snapModal = new bootstrap.Modal(document.getElementById('snapshotModal'));
        snapModal.show();
    }

    function updateSnapTheme(theme) {
        document.getElementById('capture-area').className = (theme === 'blue') ? 'bg-snapshot-blue' : 'bg-snapshot-dark';
    }

    function downloadSnapshot() {
        html2canvas(document.getElementById('capture-area'), { scale: 2, useCORS: true }).then(canvas => {
            const link = document.createElement('a');
            link.download = `GatiFin-Snapshot-${Date.now()}.png`;
            link.href = canvas.toDataURL("image/png"); link.click();
        });
    }

    /* --- AUTH & APP LOGIC --- */
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const u = document.getElementById('username').value;
        const p = document.getElementById('password').value;
        const users = JSON.parse(localStorage.getItem('fintrack_users')) || {};
        if (users[u] && users[u].password === p) loginUser(u);
        else alert("Login Gagal!");
    });

    function loginUser(username) {
        currentUser = username;
        sessionStorage.setItem('fintrack_logged_user', username);
        document.getElementById('auth-screen').style.display = 'none';
        document.getElementById('main-app').style.display = 'block';
        initSettings(); // Panggil inisialisasi pengaturan[cite: 5]
        loadUserData();
    }

    function loadUserData() {
        const username = sessionStorage.getItem('fintrack_logged_user');
        if (!username) return;
        currentUser = username;
        transactions = JSON.parse(localStorage.getItem(`fintrack_db_${currentUser}`)) || [];
        document.getElementById('p-username').value = currentUser;
        document.getElementById('sidebar-username-display').innerText = "@" + currentUser;
        const savedProf = JSON.parse(localStorage.getItem(`fintrack_prof_${currentUser}`));
        if(savedProf) {
            document.getElementById('p-name').value = savedProf.name || "";
            document.getElementById('p-job').value = savedProf.job || "";
            document.getElementById('p-age').value = savedProf.age || "";
            document.getElementById('sidebar-name-display').innerText = savedProf.name || currentUser;
        }
        const savedImg = localStorage.getItem(`fintrack_img_${currentUser}`);
        const defaultImg = "https://via.placeholder.com/150";
        document.getElementById('profile-img-display').src = savedImg || defaultImg;
        document.getElementById('sidebar-img-display').src = savedImg || defaultImg;
        updateUI();
    }

    function saveProfileData() { 
        const name = document.getElementById('p-name').value;
        const profData = { name, job: document.getElementById('p-job').value, age: document.getElementById('p-age').value };
        localStorage.setItem(`fintrack_prof_${currentUser}`, JSON.stringify(profData));
        document.getElementById('sidebar-name-display').innerText = name || currentUser;
        alert('Profil berhasil diperbarui!'); 
    }

    function previewImage(event) { 
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader(); 
        reader.onload = () => { 
            const img = reader.result;
            document.getElementById('profile-img-display').src = img; 
            document.getElementById('sidebar-img-display').src = img; 
            localStorage.setItem(`fintrack_img_${currentUser}`, img);
        }; 
        reader.readAsDataURL(file); 
    }

    /* --- FINANCE CORE LOGIC --- */
    const categoryData = { masuk: ["Gaji", "Bonus", "Investasi", "Lainnya"], keluar: ["Makanan", "Transport", "Belanja", "Tagihan", "Lainnya"] };

    function updateCategories() {
        const type = document.getElementById('type').value;
        const select = document.getElementById('category');
        select.innerHTML = ""; 
        categoryData[type].forEach(cat => { const o = document.createElement('option'); o.value = cat; o.text = cat; select.appendChild(o); });
    }

    document.getElementById('financeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const data = { id: Date.now(), type: document.getElementById('type').value, amount: parseInt(document.getElementById('amount').value), category: document.getElementById('category').value, note: document.getElementById('note').value, date: document.getElementById('date').value };
        transactions.unshift(data);
        localStorage.setItem(`fintrack_db_${currentUser}`, JSON.stringify(transactions));
        this.reset(); document.getElementById('date').valueAsDate = new Date();
        updateCategories(); updateUI();
    });

    function updateUI() {
        const tbody = document.getElementById('historyTableBody');
        tbody.innerHTML = ''; let inSum = 0, outSum = 0;
        transactions.forEach(t => {
            if(t.type === 'masuk') inSum += t.amount; else outSum += t.amount;
            tbody.innerHTML += `<tr><td class="small text-muted">${t.date}</td><td>${t.note}</td><td><span class="badge bg-light text-dark">${t.category}</span></td><td class="${t.type === 'masuk' ? 'text-success' : 'text-danger'} fw-bold">${currentCurrency} ${t.amount.toLocaleString()}</td><td><i class="bi bi-trash btn-delete" onclick="deleteTransaction(${t.id})"></i></td></tr>`;
        });
        document.getElementById('disp-masuk').innerText = `${currentCurrency} ${inSum.toLocaleString()}`;
        document.getElementById('disp-keluar').innerText = `${currentCurrency} ${outSum.toLocaleString()}`;
        document.getElementById('disp-saldo').innerText = `${currentCurrency} ${(inSum - outSum).toLocaleString()}`;
    }

    function showMenu(menuId, element) {
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        element.classList.add('active');
        document.querySelectorAll('.menu-section').forEach(s => { s.style.display = 'none'; s.classList.remove('active'); });
        const activeSec = document.getElementById(menuId); 
        activeSec.style.display = 'block';
        setTimeout(() => activeSec.classList.add('active'), 50);
        if(menuId === 'analisis') refreshCharts();
    }

    function refreshCharts() {
        const isDark = document.body.classList.contains('dark-mode');
        const color = isDark ? '#ffffff' : '#666666';
        const inData = transactions.filter(t => t.type === 'masuk');
        const outData = transactions.filter(t => t.type === 'keluar');
        const agg = (data) => data.reduce((acc, t) => { acc[t.category] = (acc[t.category] || 0) + t.amount; return acc; }, {});
        const inAgg = agg(inData); const outAgg = agg(outData);

        if(mainChart) mainChart.destroy();
        mainChart = new Chart(document.getElementById('mainChart'), {
            type: 'bar',
            data: { labels: ['Masuk', 'Keluar'], datasets: [{ label: 'Total', data: [inData.reduce((a,b)=>a+b.amount,0), outData.reduce((a,b)=>a+b.amount,0)], backgroundColor: ['#2ecc71', '#ff7675'] }] },
            options: { maintainAspectRatio: false, plugins: { legend: { labels: { color } } } }
        });

        if(categoryChartOut) categoryChartOut.destroy();
        categoryChartOut = new Chart(document.getElementById('categoryChartOut'), {
            type: 'doughnut',
            data: { labels: Object.keys(outAgg), datasets: [{ data: Object.values(outAgg), backgroundColor: ['#ff7675', '#fdcb6e', '#00cec9', '#6c5ce7'] }] },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color } } } }
        });

        if(categoryChartIn) categoryChartIn.destroy();
        categoryChartIn = new Chart(document.getElementById('categoryChartIn'), {
            type: 'doughnut',
            data: { labels: Object.keys(inAgg), datasets: [{ data: Object.values(inAgg), backgroundColor: ['#2ecc71', '#55efc4', '#81ecec', '#74b9ff'] }] },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color } } } }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const loggedUser = sessionStorage.getItem('fintrack_logged_user');
        if(loggedUser) loginUser(loggedUser);
        document.getElementById('date').valueAsDate = new Date();
        updateCategories();
    });

    function logout() {
        sessionStorage.clear();
        location.reload();
    }
</script>
</body>
</html>