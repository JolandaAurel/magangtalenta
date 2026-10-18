<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';
if (!isset($_SESSION['admin_type'])) { $_SESSION['admin_type'] = 'super'; }
if (!isset($_SESSION['admin_logged_in']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header("Location: login.php");
    exit;
}
$currentPage = basename($_SERVER['PHP_SELF']);
$currentTab = $_GET['tab'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Talenta Semarang</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css?v=<?php echo time(); ?>">
    <style>
        .menu-heading {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #94a3b8;
            padding: 14px 20px 6px;
            font-weight: 700;
        }
        .sidebar-submenu {
            list-style: none;
            padding: 0 0 5px 15px;
            background: rgba(0,0,0,0.15);
        }
        .sidebar-submenu li a {
            padding: 8px 15px 8px 12px !important;
            font-size: 0.85rem !important;
            color: #94a3b8 !important;
        }
        .sidebar-submenu li a:hover, .sidebar-submenu li a.active {
            color: #38bdf8 !important;
            border-left: none !important;
        }
    </style>
</head>
<body>
<?php if ($currentPage !== 'login.php'): ?>
    <div class="sidebar">
        <div class="sidebar-header" style="display: flex; align-items: center; justify-content: center; gap: 10px;">
            <i class="fas fa-tree" style="color: #38bdf8;"></i> Talenta Admin
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            
            <?php if ($_SESSION['admin_type'] === 'super'): ?>
                <!-- 1. MANAJEMEN UNIT -->
                <div class="menu-heading">Manajemen Sekolah</div>
                <li>
                    <a href="manajemen_unit.php" class="<?php echo $currentPage === 'manajemen_unit.php' ? 'active' : ''; ?>">
                        <i class="fas fa-th-large"></i> <span>Semua Unit</span>
                    </a>
                </li>
                <li>
                    <a href="kelola_sekolah.php" class="<?php echo $currentPage === 'kelola_sekolah.php' ? 'active' : ''; ?>">
                        <i class="fas fa-graduation-cap"></i> <span>Unit Pendidikan (10)</span>
                    </a>
                </li>
                <li>
                    <a href="kelola_psikologi.php" class="<?php echo $currentPage === 'kelola_psikologi.php' ? 'active' : ''; ?>">
                        <i class="fas fa-brain"></i> <span>Layanan Psikologi (10)</span>
                    </a>
                </li>

                <!-- 2. MANAJEMEN AKUN (DI LUAR UNIT) -->
                <div class="menu-heading">Konten Global & Akun</div>
                <li>
                    <a href="manajemen_akun.php" class="<?php echo $currentPage === 'manajemen_akun.php' || $currentPage === 'kelola_admin.php' ? 'active' : ''; ?>">
                        <i class="fas fa-sliders-h"></i> <span>Manajemen Akun</span>
                    </a>
                </li>
                <ul class="sidebar-submenu">
                    <li><a href="manajemen_akun.php?tab=home" class="<?php echo ($currentPage === 'manajemen_akun.php' && $currentTab === 'home') ? 'active' : ''; ?>"><i class="fas fa-home"></i> Edit Home</a></li>
                    <li><a href="manajemen_akun.php?tab=tentang" class="<?php echo ($currentPage === 'manajemen_akun.php' && $currentTab === 'tentang') ? 'active' : ''; ?>"><i class="fas fa-info-circle"></i> Tentang Kami</a></li>
                    <li><a href="manajemen_akun.php?tab=publikasi" class="<?php echo ($currentPage === 'manajemen_akun.php' && $currentTab === 'publikasi') ? 'active' : ''; ?>"><i class="fas fa-newspaper"></i> Publikasi</a></li>
                    <li><a href="manajemen_akun.php?tab=kontak" class="<?php echo ($currentPage === 'manajemen_akun.php' && $currentTab === 'kontak') ? 'active' : ''; ?>"><i class="fas fa-phone-alt"></i> Kontak</a></li>
                    <li><a href="manajemen_akun.php?tab=akun" class="<?php echo ($currentPage === 'manajemen_akun.php' && ($currentTab === 'akun' || empty($currentTab))) ? 'active' : ''; ?>"><i class="fas fa-users-cog"></i> Tabel Akun Admin</a></li>
                </ul>

                <div class="menu-heading">Media & Desain</div>
                <li>
                    <a href="kelola_foto.php" class="<?php echo $currentPage === 'kelola_foto.php' ? 'active' : ''; ?>">
                        <i class="fas fa-images"></i> Semua Banner Halaman
                    </a>
                </li>
                <li>
                    <a href="kelola_galeri_halaman.php" class="<?php echo $currentPage === 'kelola_galeri_halaman.php' ? 'active' : ''; ?>">
                        <i class="fas fa-camera"></i> Galeri Kegiatan
                    </a>
                </li>
            <?php else: ?>
                <!-- MENU KHUSUS ADMIN UNIT -->
                <div class="menu-heading">Unit Anda</div>
                <li>
                    <a href="<?php echo ($_SESSION['admin_type'] === 'pendidikan') ? 'kelola_sekolah.php' : 'kelola_psikologi.php'; ?>" class="active">
                        <i class="fas fa-edit"></i> Edit Konten Unit Saya
                    </a>
                </li>
                <li>
                    <a href="kelola_galeri_halaman.php" class="<?php echo $currentPage === 'kelola_galeri_halaman.php' ? 'active' : ''; ?>">
                        <i class="fas fa-camera"></i> Galeri Dokumentasi
                    </a>
                </li>
                <li>
                    <a href="kelola_gambar_konten.php" class="<?php echo $currentPage === 'kelola_gambar_konten.php' ? 'active' : ''; ?>">
                        <i class="fas fa-file-image"></i> Gambar Dalam Teks
                    </a>
                </li>
            <?php endif; ?>

            <li style="margin-top: 20px;">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>
    <div class="main-content">
        <div class="topbar">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <h2 style="margin: 0; font-size: 1.25rem;">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>!</h2>
                <span class="badge <?php echo (isset($_SESSION['admin_type']) && $_SESSION['admin_type'] === 'super') ? 'badge-amber' : ((isset($_SESSION['admin_type']) && $_SESSION['admin_type'] === 'pendidikan') ? 'badge-primary' : 'badge-purple'); ?>">
                    <i class="fas <?php echo (isset($_SESSION['admin_type']) && $_SESSION['admin_type'] === 'super') ? 'fa-crown' : 'fa-user-tag'; ?>"></i>
                    <?php echo htmlspecialchars($_SESSION['admin_unit_name'] ?? (isset($_SESSION['admin_type']) && $_SESSION['admin_type'] === 'super' ? 'Super Admin' : 'Admin Unit')); ?>
                </span>
                <?php if (!empty($_SESSION['admin_email'])): ?>
                    <span style="font-size: 0.85rem; color: #64748b; background: #f8fafc; padding: 4px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <i class="fas fa-envelope" style="color: #ea4335;"></i> <?php echo htmlspecialchars($_SESSION['admin_email']); ?>
                    </span>
                <?php endif; ?>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="/talenta-semarang/" target="_blank" class="btn btn-primary"><i class="fas fa-external-link-alt"></i> Lihat Website</a>
                <a href="logout.php" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="content-area">
<?php endif; ?>
