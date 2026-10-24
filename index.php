<?php
// index.php
require_once 'includes/header.php';

// Fetch Hero Data
$heroStmt = $pdo->query("SELECT * FROM hero_section LIMIT 1");
$heroData = $heroStmt->fetch();

// Fetch Programs
$programsStmt = $pdo->query("SELECT * FROM programs ORDER BY sort_order ASC");
$programs = $programsStmt->fetchAll();
?>

<!-- HERO SECTION -->
<section class="hero-premium">
    <!-- Background Image -->
    <div class="hero-bg-wrapper">
        <img src="/outsource_talenta_landing/magangtalenta/assets/images/hero_banner_final_edited.png?v=1791337561" alt="Background" class="hero-bg-img">
    </div>

    <div class="hero-premium-container">
        <div class="hero-premium-content">
            <div class="hero-tags">
                <i class="fas fa-sparkles"></i> TALENTA SEMARANG
            </div>
            <h1 class="hero-main-title">Every Potential<br>Matters.</h1>
            <p class="hero-desc">Kami hadir untuk mendampingi setiap anak agar tumbuh, berkembang, dan meraih masa depan yang lebih cerah.</p>
            <div class="hero-buttons">
                <a href="/outsource_talenta_landing/magangtalenta/page.php?slug=fasilitas" class="btn-primary pill">Lihat Fasilitas <i class="fas fa-arrow-right"></i></a>
                <a href="#program" class="btn-outline pill">Lihat Program <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
        <div class="hero-premium-image">
            <img src="/outsource_talenta_landing/magangtalenta/assets/images/hero_kids_new.png" alt="Siswa Talenta Semarang" class="hero-mobile-img">
        </div>
    </div>
</section>

<!-- PROGRAMS / SERVICES SECTION -->
<section id="program" class="programs-premium">
    <div class="programs-grid">
        <?php foreach ($programs as $program): 
            $url = $program->link_url;
            if (strpos($url, '#modal-') === 0 || strpos($url, 'layanan.php') === false) {
                if (stripos($program->title, 'Pendidikan') !== false) $url = 'layanan.php?kategori=pendidikan';
                elseif (stripos($program->title, 'Psikologi') !== false) $url = 'layanan.php?kategori=psikologi';
                elseif (stripos($program->title, 'Inklusi') !== false) $url = 'layanan.php?kategori=inklusi';
                elseif (stripos($program->title, 'Publikasi') !== false) $url = 'layanan.php?kategori=publikasi';
            }
            $targetUrl = (strpos($url, '/') === 0 || strpos($url, 'http') === 0) ? $url : '/outsource_talenta_landing/magangtalenta/' . $url;
        ?>
        <div class="program-card" style="cursor:pointer;" onclick="window.location.href='<?php echo htmlspecialchars($targetUrl); ?>';">
            <div class="program-icon">
                <i class="<?php echo htmlspecialchars($program->icon_class); ?>"></i>
            </div>
            <h3><?php echo htmlspecialchars($program->title); ?></h3>
            <p><?php echo htmlspecialchars($program->description); ?></p>
            <a href="<?php echo htmlspecialchars($targetUrl); ?>" class="program-link">
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- KENAPA MEMILIH KAMI SECTION -->
<section class="why-us">
    <div class="why-us-header">
        <span class="why-us-badge">KEUNGGULAN KAMI</span>
        <h2 class="why-us-title">Kenapa Memilih Kami?</h2>
        <p class="why-us-subtitle">Komitmen Sekolah Talenta Semarang dalam menghadirkan pendidikan holistik, adaptif, dan penuh kasih untuk setiap potensi anak.</p>
    </div>

    <div class="why-us-stripe">
        <div class="why-us-container">
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-award"></i></div>
                <div class="why-content">
                    <h4>Terakreditasi A (Unggul)</h4>
                    <p>Memastikan standar pendidikan bermutu tinggi dengan kurikulum terstruktur dan metode pembelajaran unggulan.</p>
                </div>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-school"></i></div>
                <div class="why-content">
                    <h4>Sekolah Penggerak</h4>
                    <p>Mendorong siswa menjadi pembelajar dan calon pemimpin yang proaktif, kritis, dan berkarakter mulia.</p>
                </div>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-hands-holding-child"></i></div>
                <div class="why-content">
                    <h4>Sekolah Ramah Anak</h4>
                    <p>Menciptakan lingkungan yang aman, inklusif, dan penuh kasih di mana setiap anak merasa dihargai dan didukung.</p>
                </div>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-layer-group"></i></div>
                <div class="why-content">
                    <h4>Program Komprehensif</h4>
                    <p>Menyediakan layanan pendidikan lengkap mulai dari Daycare, KB, TK, hingga kesetaraan SD, SMP, SMA, dan Inklusi.</p>
                </div>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="why-content">
                    <h4>Pengajar Berkompeten</h4>
                    <p>Didampingi oleh tenaga pengajar dan praktisi anak yang berkualifikasi, berpengalaman, serta berdedikasi.</p>
                </div>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-lightbulb"></i></div>
                <div class="why-content">
                    <h4>Pendekatan Inovatif</h4>
                    <p>Menerapkan metode <em>Fun & Active Learning</em> yang kontekstual dan adaptif sesuai tahap perkembangan anak.</p>
                </div>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-shapes"></i></div>
                <div class="why-content">
                    <h4>Fasilitas Representatif</h4>
                    <p>Menyediakan sarana & lingkungan belajar yang representatif, nyaman, dan kondusif bagi eksplorasi anak.</p>
                </div>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-map-location-dot"></i></div>
                <div class="why-content">
                    <h4>Lokasi Strategis</h4>
                    <p>Mudah diakses di kawasan Semarang Barat dengan lingkungan yang aman, tertib, dan nyaman bagi orang tua.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
