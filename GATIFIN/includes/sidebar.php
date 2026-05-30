<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$role = $_SESSION['role'] ?? 'pengguna';
?>


<div id="sidebar-wrapper">

    <div class="sidebar-brand">
        <img src="assets/img/logo_side.png" alt="GATIFIN" class="sidebar-brand-logo" id="logoTrigger">
        <span class="sidebar-brand-name">GATIFIN</span>
        <button id="sidebarToggle" title="Ciutkan sidebar"><i class="fas fa-chevron-left fa-sm"></i></button>
    </div>

    <div class="sidebar-content">

        <a href="index.php?page=dashboard" class="nav-link-custom <?= ($page === 'dashboard') ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-grip-vertical"></i></span>
            <span class="link-text">Dashboard</span>
        </a>

        <?php if ($role === 'orang_tua'): ?>
        <div class="sidebar-section-title">PANTAU</div>
        <?php $isPantau = in_array($page, ['profil_pantau', 'laporan_pantau', 'analisis_pantau']); ?>
        <a href="javascript:void(0)" class="nav-link-custom <?= $isPantau ? 'active' : '' ?>"
           data-bs-toggle="collapse" data-bs-target="#submenuPantau"
           aria-expanded="<?= $isPantau ? 'true' : 'false' ?>">
            <span class="nav-icon"><i class="fas fa-user-shield"></i></span>
            <span class="link-text">Pilih Pantau <i class="fas fa-chevron-down chevron-icon"></i></span>
        </a>
        <div class="collapse <?= $isPantau ? 'show' : '' ?>" id="submenuPantau">
            <a href="index.php?page=profil_pantau"   class="nav-link-custom <?= $page === 'profil_pantau'   ? 'active' : '' ?>"><span class="link-text">Profil Pantau</span></a>
            <a href="index.php?page=laporan_pantau"  class="nav-link-custom <?= $page === 'laporan_pantau'  ? 'active' : '' ?>"><span class="link-text">Laporan Pantau</span></a>
            <a href="index.php?page=analisis_pantau" class="nav-link-custom <?= $page === 'analisis_pantau' ? 'active' : '' ?>"><span class="link-text">Analisis Pantau</span></a>
        </div>
        <?php endif; ?>

        <div class="sidebar-section-title">MENU UTAMA</div>
        <a href="index.php?page=profil" class="nav-link-custom <?= $page === 'profil' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-user-circle"></i></span>
            <span class="link-text">Profil Saya</span>
        </a>
        <a href="index.php?page=laporan" class="nav-link-custom <?= $page === 'laporan' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
            <span class="link-text">Laporan Keuangan</span>
        </a>
        <a href="index.php?page=analisis" class="nav-link-custom <?= $page === 'analisis' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-brain"></i></span>
            <span class="link-text">Analisis Finansial</span>
        </a>

        <?php if (in_array($role, ['pengguna', 'orang_tua'])): ?>
        <div class="sidebar-section-title">MANAJEMEN</div>
        <a href="index.php?page=master" class="nav-link-custom <?= $page === 'master' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-database"></i></span>
            <span class="link-text">Data Master</span>
        </a>
        <a href="index.php?page=transaksi" class="nav-link-custom <?= $page === 'transaksi' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-arrow-right-arrow-left"></i></span>
            <span class="link-text">Transaksi</span>
        </a>
        <?php endif; ?>

    </div>

    <?php if (in_array($role, ['pengguna', 'orang_tua'])): ?>
    <div class="sidebar-footer">
        <a href="index.php?page=pengaturan" class="nav-link-custom <?= $page === 'pengaturan' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-gear"></i></span>
            <span class="link-text">Pengaturan</span>
        </a>
    </div>
    <?php endif; ?>
</div>

<div id="page-content-wrapper">
    <nav class="navbar-top">
        <div class="navbar-top-left">
            <button class="btn-mobile-toggle" id="mobileSidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <button id="sidebarExpandBtn" style="display:none; background:transparent; border:none; color:var(--text-secondary); cursor:pointer; padding:6px 10px; border-radius:var(--radius-sm); font-size:1rem;" title="Buka sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div>
                <div class="page-title-top"><?= htmlspecialchars($current_title ?? 'GATIFIN') ?></div>
                <?php if (!empty($current_subtitle)): ?>
                <div class="page-subtitle-top"><?= htmlspecialchars($current_subtitle) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="navbar-top-right">
            <div class="dropdown">
                <button class="user-pill" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?= htmlspecialchars($user_foto) ?>" alt="Foto" class="user-avatar">
                    <div class="d-none d-sm-block">
                        <div class="user-name"><?= htmlspecialchars($user_nama) ?></div>
                        <div class="user-role"><?= ucfirst(str_replace('_', ' ', $role)) ?></div>
                    </div>
                    <i class="fas fa-chevron-down fa-xs ms-1" style="color:var(--text-muted);"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="index.php?page=profil"><i class="fas fa-user-circle me-2 text-brand"></i>Profil Saya</a></li>
                    <li><a class="dropdown-item" href="index.php?page=pengaturan"><i class="fas fa-gear me-2 text-brand"></i>Pengaturan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-right-from-bracket me-2"></i>Keluar</a></li>
                </ul>
            </div>
        </div>
    </nav>

<script>
(function(){
    const wrapper       = document.getElementById('wrapper');
    const toggleBtn     = document.getElementById('sidebarToggle');
    const expandBtn     = document.getElementById('sidebarExpandBtn');
    const mobileBtn     = document.getElementById('mobileSidebarToggle');
    const overlay       = document.getElementById('sidebarOverlay');
    const logoTrigger   = document.getElementById('logoTrigger');

    // Restore desktop collapsed state
    if (localStorage.getItem('sidebarCollapsed') === '1' && window.innerWidth > 992) {
        wrapper.classList.add('toggled');
        if (expandBtn) expandBtn.style.display = 'flex';
    }

    // Desktop toggle (collapse)
    function desktopToggle() {
        wrapper.classList.toggle('toggled');
        const isCollapsed = wrapper.classList.contains('toggled');
        localStorage.setItem('sidebarCollapsed', isCollapsed ? '1' : '0');
        if (expandBtn) expandBtn.style.display = isCollapsed ? 'flex' : 'none';
    }

    // Mobile toggle (slide in/out)
    function mobileToggle() {
        wrapper.classList.toggle('mob-open');
    }

    if (toggleBtn) toggleBtn.addEventListener('click', desktopToggle);
    if (expandBtn) expandBtn.addEventListener('click', desktopToggle);
    if (logoTrigger) logoTrigger.addEventListener('click', function(){
        if (window.innerWidth > 992) desktopToggle();
        else mobileToggle();
    });
    if (mobileBtn) mobileBtn.addEventListener('click', mobileToggle);
    if (overlay) overlay.addEventListener('click', function(){
        wrapper.classList.remove('mob-open');
    });
})();
</script>
