<?php
// profil.php - Dialihkan ke halaman profil dinamis yang dikelola via Admin
header("Location: /outsource_talenta_landing/magangtalenta/page.php?slug=profil", true, 301);
exit;

// Fetch profile data from database
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = 'profil' LIMIT 1");
$stmt->execute();
$page = $stmt->fetch();

// Placeholder images for the grids based on screenshot
$gridImages = [
    '/outsource_talenta_landing/magangtalenta/assets/images/profil_gal_1.jpg',
    '/outsource_talenta_landing/magangtalenta/assets/images/profil_gal_6.jpg',
    '/outsource_talenta_landing/magangtalenta/assets/images/profil_gal_8.jpg',
    '/outsource_talenta_landing/magangtalenta/assets/images/profil_gal_11.jpg'
];

$bottomImages = [
    'https://picsum.photos/400/250?random=5',
    'https://picsum.photos/400/250?random=6',
    'https://picsum.photos/400/250?random=7',
    'https://picsum.photos/400/250?random=8'
];
?>

<style>
    /* Specific styles for Profil Page */
    .profil-banner-container {
        width: 100%;
        margin-top: 80px; /* Offset for fixed header */
        line-height: 0; /* Remove bottom gap */
    }
    
    .profil-banner-img {
        width: 100%;
        height: auto;
        display: block;
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
    
    .profil-content-wrapper {
        max-width: 1200px;
        margin: 50px auto;
        padding: 0 20px;
    }
    
    .profil-title {
        color: var(--primary-blue);
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 20px;
    }
    
    .image-grid-wrap {
        float: left;
        width: 45%;
        margin-right: 40px;
        margin-bottom: 20px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    
    .image-grid-wrap img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: var(--card-shadow);
    }
    
    .profil-text {
        font-size: 1rem;
        color: var(--text-dark);
        line-height: 1.8;
        text-align: justify;
    }
    
    .profil-text p {
        margin-bottom: 20px;
    }

    .profil-text h3 {
        color: red; /* Matching the red text in screenshot */
        margin-top: 30px;
        margin-bottom: 15px;
        font-size: 1.2rem;
    }
    
    .bottom-gallery {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-top: 60px;
        background: #f8fafc;
        padding: 20px;
    }
    
    .bottom-gallery img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    
    /* Clearfix for floated elements */
    .clearfix::after {
        content: "";
        clear: both;
        display: table;
    }

    @media (max-width: 768px) {
        .image-grid-wrap {
            float: none;
            width: 100%;
            margin-right: 0;
            margin-bottom: 30px;
        }
        .profil-toolbar {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
        .bottom-gallery {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 480px) {
        .bottom-gallery {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Banner Full Responsive -->
<div class="profil-banner-container">
    <img src="/outsource_talenta_landing/magangtalenta/assets/images/profil_banner.png" alt="Banner Profil Talenta" class="profil-banner-img">
</div>

<!-- Toolbar / Breadcrumb area -->
<div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
    <div class="profil-toolbar">
        <div class="toolbar-left">
            <span>Fun and Active Learning</span>
            <span style="color: #cbd5e1;">|</span>
            <span><a href="/outsource_talenta_landing/magangtalenta/" class="home-link"><i class="fas fa-home"></i> Home</a> / Profil Sekilas</span>
        </div>
        
    </div>
</div>

<div class="profil-content-wrapper clearfix">
    
    <div class="image-grid-wrap">
        <?php foreach($gridImages as $img): ?>
            <img src="<?php echo $img; ?>" alt="Kegiatan Talenta">
        <?php endforeach; ?>
    </div>
    
    <div class="profil-text">
        <h1 class="profil-title">Sekolah Talenta Semarang</h1>
        <?php 
            if($page) {
                // To match the screenshot which has a red sub-heading "KB - TK Talenta berdiri tanggal 2 Januari tahun 2010"
                // We will output the HTML content from DB.
                echo $page->content_html;
            } else {
                echo "<p>Konten profil belum tersedia.</p>";
            }
        ?>
    </div>
</div>

<!-- Bottom Gallery Row -->
<div class="bottom-gallery">
    <?php 
    for ($i = 1; $i <= 12; $i++) {
        echo "<img src=\"/outsource_talenta_landing/magangtalenta/assets/images/profil_gal_{$i}.jpg\" alt=\"Galeri Talenta {$i}\">";
    }
    ?>
</div>

<?php require_once 'includes/footer.php'; ?>
