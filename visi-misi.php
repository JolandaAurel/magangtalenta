<?php
// visi-misi.php
require_once 'includes/header.php';

// Fetch visi-misi data from database
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = 'visi-misi' LIMIT 1");
$stmt->execute();
$page = $stmt->fetch();

// Visi misi images are now using a single combined image
?>

<style>
    .profil-banner {
        width: 100%;
        height: 250px;
        background: url('/outsource_talenta_landing/magangtalenta/assets/images/header-bg.png') center/cover;
        position: relative;
        margin-top: 80px; 
    }
    
    .profil-toolbar {
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
    }
    
    .toolbar-left .home-link {
        color: var(--primary-blue);
    }
    
    .visi-content-wrapper {
        max-width: 1200px;
        margin: 50px auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 40px;
    }
    
    .image-stack {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .image-stack img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: var(--card-shadow);
    }
    
    .visi-text {
        font-size: 1rem;
        color: var(--text-dark);
        line-height: 1.8;
    }
    
    .visi-title {
        color: red;
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 25px;
        border-bottom: 1px solid #eee;
        padding-bottom: 15px;
    }

    .visi-text h3 {
        color: var(--text-dark);
        font-size: 1.1rem;
        font-weight: 700;
        margin-top: 25px;
        margin-bottom: 10px;
    }

    .visi-text p {
        margin-bottom: 15px;
    }

    .visi-text ul, .visi-text ol {
        margin-bottom: 20px;
        padding-left: 20px;
    }

    .visi-text li {
        margin-bottom: 10px;
    }

    @media (max-width: 768px) {
        .visi-content-wrapper {
            grid-template-columns: 1fr;
        }
        .profil-toolbar {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
    }
</style>

<!-- Banner Placeholder -->
<div class="page-banner-container" style="width: 100%; margin-top: 80px; line-height: 0;">
    <img src="/outsource_talenta_landing/magangtalenta/assets/images/visi_misi_banner.png?v=<?php echo time(); ?>" alt="Visi & Misi" style="width: 100%; height: auto; display: block;">
</div>

<!-- Toolbar / Breadcrumb area -->
<div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
    <div class="profil-toolbar">
        <div class="toolbar-left">
            <span>Fun and Active Learning</span>
            <span style="color: #cbd5e1;">|</span>
            <span><a href="/outsource_talenta_landing/magangtalenta/" class="home-link"><i class="fas fa-home"></i> Home</a> / Visi & Misi</span>
        </div>
        
    </div>
</div>

<div class="visi-content-wrapper">
    
    <div class="image-stack">
        <img src="/outsource_talenta_landing/magangtalenta/assets/images/visi-final-1.png" alt="Kegiatan Guru Talenta 1">
        <img src="/outsource_talenta_landing/magangtalenta/assets/images/visi-final-2.png" alt="Kegiatan Guru Talenta 2">
        <img src="/outsource_talenta_landing/magangtalenta/assets/images/visi-final-3.png" alt="Kegiatan Guru Talenta 3">
    </div>
    
    <div class="visi-text">
        <h1 class="visi-title">Visi, Misi, dan Tujuan Sekolah Talenta</h1>
        <?php 
            if($page) {
                echo $page->content_html;
            } else {
                echo "<p>Konten belum tersedia.</p>";
            }
        ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
