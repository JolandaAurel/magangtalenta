<?php
// berita-kegiatan.php
require_once 'includes/header.php';
?>

<style>
    .berita-banner-container {
        width: 100%;
        margin-top: 80px; /* Offset for fixed header */
        line-height: 0;
    }
    .berita-banner-img {
        width: 100%;
        height: auto;
        display: block;
    }
    .berita-toolbar {
        background: #f8fafc;
        padding: 15px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e2e8f0;
        max-width: 1400px;
        margin: 0 auto;
    }
    .toolbar-left {
        display: flex;
        align-items: center;
        gap: 20px;
        font-size: 0.9rem;
        color: var(--text-light);
    }
    .toolbar-left a {
        color: var(--text-dark);
        text-decoration: none;
    }
    .toolbar-left .home-link {
        color: var(--primary-blue);
    }
    @media (max-width: 768px) {
        .berita-toolbar {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
    }
</style>

<div class="berita-banner-container">
    <img src="/talenta-semarang/assets/images/berita_banner.png" alt="Banner Berita dan Kegiatan Talenta" class="berita-banner-img">
</div>

<div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; margin-bottom: 50px;">
    <div class="berita-toolbar">
        <div class="toolbar-left">
            <span>Fun and Active Learning</span>
            <span style="color: #cbd5e1;">|</span>
            <span><a href="/talenta-semarang/" class="home-link"><i class="fas fa-home"></i> Home</a> / Berita & Kegiatan</span>
        </div>
        
    </div>
</div>

<section class="berita-section">
    <div class="berita-container">
        <div class="berita-image">
            <img src="/talenta-semarang/assets/images/berita-kegiatan-sam-poo-kong.png" alt="Acara Periodik Talenta Semarang">
        </div>
        <div class="berita-content">
            <h2 class="berita-title">Acara Periodik</h2>
            <ol class="berita-list">
                <li>Field Trip sesuai tema pelajaran, misal ke Kebun Binatang, Museum, Pabrik Roti, dll.</li>
                <li>Family Day</li>
                <li>Pemeriksaan kesehatan</li>
                <li>English Day</li>
                <li>Parenting Day</li>
                <li>Puncak Tema / Assembly</li>
                <li>Peringatan Hari Besar Keagamaan</li>
                <li>Peringatan Hari Besar Nasional</li> <!-- Typo in original fixed from Peringan to Peringatan -->
                <li>Cooking Session</li>
                <li>Lomba-lomba</li>
            </ol>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
