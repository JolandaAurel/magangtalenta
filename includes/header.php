<?php 
// includes/header.php
// We can include database configuration here so it's available globally on every page
require_once __DIR__ . '/../config/database.php';
?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/database.php';
// Fetch dropdown menus dynamically
$stmt = $pdo->query("SELECT slug, title, category FROM pages WHERE category IN ('pendidikan', 'psikologi', 'tentang-kami') ORDER BY id ASC");
$dynamic_pages = $stmt->fetchAll(PDO::FETCH_OBJ);
$menu_pendidikan = [];
$menu_psikologi = [];
$menu_tentang = [];
foreach ($dynamic_pages as $dp) {
    if ($dp->category === 'pendidikan') $menu_pendidikan[] = $dp;
    if ($dp->category === 'psikologi') $menu_psikologi[] = $dp;
    if ($dp->category === 'tentang-kami') $menu_tentang[] = $dp;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talenta Semarang - Every Potential Matters</title>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/outsource_talenta_landing/magangtalenta/assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

<header class="header">
    <div class="nav-container">
        <!-- Logo -->
        <a href="/outsource_talenta_landing/magangtalenta/" class="logo">
            <img src="/outsource_talenta_landing/magangtalenta/assets/images/logo.png" alt="Talenta Semarang">
            <div class="logo-text-group">
                <span class="logo-title">Talenta Semarang</span>
                <span class="logo-subtitle">Fun and Active Learning</span>
            </div>
        </a>

        <!-- Navigation Menu -->
        <nav>
            <ul class="nav-links" id="nav-menu">
                <li class="nav-item">
                    <a href="/outsource_talenta_landing/magangtalenta/" class="active">Home</a>
                </li>
                <li class="nav-item">
                      <a href="#">Tentang Kami <i class="fas fa-chevron-down" style="font-size: 0.7em; margin-left: 3px;"></i></a>
                        <ul class="dropdown">
                            <?php foreach($menu_tentang as $item): ?>
                                <li><a href="/outsource_talenta_landing/magangtalenta/page.php?slug=<?php echo htmlspecialchars($item->slug); ?>"><?php echo htmlspecialchars($item->title); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                  </li>
                <li class="nav-item">
                    <a href="#">Pendidikan <i class="fas fa-chevron-down" style="font-size: 0.7em; margin-left: 3px;"></i></a>
                        <ul class="dropdown">
                            <?php foreach($menu_pendidikan as $item): ?>
                                <li><a href="/outsource_talenta_landing/magangtalenta/page.php?slug=<?php echo htmlspecialchars($item->slug); ?>"><?php echo htmlspecialchars($item->title); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                </li>
                <li class="nav-item">
                    <a href="#">Layanan Psikologi <i class="fas fa-chevron-down" style="font-size: 0.7em; margin-left: 3px;"></i></a>
                        <ul class="dropdown">
                            <?php foreach($menu_psikologi as $item): ?>
                                <li><a href="/outsource_talenta_landing/magangtalenta/page.php?slug=<?php echo htmlspecialchars($item->slug); ?>"><?php echo htmlspecialchars($item->title); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <li class="nav-item">
                      <a href="#">Publikasi <i class="fas fa-chevron-down" style="font-size: 0.7em; margin-left: 3px;"></i></a>
                      <ul class="dropdown">
                          <li><a href="/outsource_talenta_landing/magangtalenta/galeri.php">Galeri</a></li>
                          <li><a href="/outsource_talenta_landing/magangtalenta/berita-kegiatan.php">Berita & Kegiatan</a></li>
                          <li><a href="/outsource_talenta_landing/magangtalenta/artikel.php">Artikel</a></li>
                      </ul>
                  </li>
                <li class="nav-item">
                    <a href="#">Kontak <i class="fas fa-chevron-down" style="font-size: 0.7em; margin-left: 3px;"></i></a>
                    <ul class="dropdown" style="min-width: 250px;">
                        <li><a href="tel:0247601502"><i class="fas fa-phone-alt" style="width: 25px;"></i> Telepon (024-760-1502)</a></li>
                        <li><a href="mailto:talenta.lpt@gmail.com"><i class="fas fa-envelope" style="width: 25px;"></i> Email</a></li>
                        <li><a href="https://www.talentasemarang.com" target="_blank"><i class="fas fa-globe" style="width: 25px;"></i> Website</a></li>
                        <li><a href="https://maps.google.com/?q=Jl.Puspowarno+Tengah+IX+No.6,+Semarang+Barat" target="_blank"><i class="fas fa-map-marker-alt" style="width: 25px;"></i> Alamat</a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        

        <!-- Search & CTA -->
        <div class="nav-actions">
            
            <a href="/outsource_talenta_landing/magangtalenta/pendaftaran/sekolah.php" class="btn-primary desktop-only">REGISTER <i class="fas fa-arrow-right"></i></a>
            
            <!-- Mobile Menu Toggle -->
            <div class="mobile-toggle" id="mobile-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </div>
</header>

<script>
    // Mobile Menu Toggle Logic
    document.addEventListener('DOMContentLoaded', function() {
        const mobileToggle = document.getElementById('mobile-toggle');
        const navMenu = document.getElementById('nav-menu');
        const icon = mobileToggle.querySelector('i');

        mobileToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            if(navMenu.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });

        // Mobile Dropdown Toggle
        const navItems = document.querySelectorAll('.nav-item > a');
        navItems.forEach(item => {
            item.addEventListener('click', function(e) {
                if(window.innerWidth <= 1024) {
                    const dropdown = this.nextElementSibling;
                    if(dropdown && dropdown.classList.contains('dropdown')) {
                        e.preventDefault();
                        dropdown.classList.toggle('show-mobile');
                    }
                }
            });
        });

        // Mobile Nested Submenu Toggle
        const subItems = document.querySelectorAll('.dropdown-submenu > a');
        subItems.forEach(item => {
            item.addEventListener('click', function(e) {
                if(window.innerWidth <= 1024) {
                    e.preventDefault();
                    const subMenu = this.parentElement;
                    subMenu.classList.toggle('show-mobile-sub');
                }
            });
        });
    });
</script>
