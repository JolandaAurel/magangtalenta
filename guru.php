<?php
// guru.php
require_once 'includes/header.php';

// Find all guru images
$guru_images = glob("assets/images/guru_*.{jpg,jpeg,png,gif}", GLOB_BRACE);
// Sort them naturally so guru_1, guru_2 ... guru_10
natsort($guru_images);
?>

<style>
    .profil-banner {
        width: 100%;
        height: 250px;
        background: url('/talenta-semarang/assets/images/header-bg.png') center/cover;
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
    
    .guru-content-wrapper {
        max-width: 1400px;
        margin: 50px auto 100px;
        padding: 0 40px;
    }
    
    .guru-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .guru-title {
        color: #00A2E9; /* Light blue from screenshot */
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .guru-list {
        list-style: none;
        display: inline-block;
        text-align: left;
        color: var(--text-dark);
        font-size: 1.1rem;
        padding: 0;
    }

    .guru-list li {
        margin-bottom: 10px;
        position: relative;
        padding-left: 30px;
    }
    
    .guru-list li::before {
        content: '⭐';
        position: absolute;
        left: 0;
        top: 0;
        color: #f59e0b;
        font-size: 0.9rem;
    }

    /* Beautiful Grid for Teachers */
    .guru-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 20px;
    }

    .guru-card {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: #fff;
        line-height: 0; /* remove bottom gap */
    }

    .guru-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .guru-card img {
        width: 100%;
        height: auto;
        display: block;
        object-fit: cover;
    }
    
    @media (max-width: 768px) {
        .profil-toolbar {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
        .guru-content-wrapper {
            padding: 0 20px;
        }
        .guru-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }
    }
</style>

<!-- Banner Placeholder -->
<div class="profil-banner"></div>

<!-- Toolbar / Breadcrumb area -->
<div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
    <div class="profil-toolbar">
        <div class="toolbar-left">
            <span>Fun and Active Learning</span>
            <span style="color: #cbd5e1;">|</span>
            <span><a href="/talenta-semarang/" class="home-link"><i class="fas fa-home"></i> Home</a> / Guru</span>
        </div>
        
    </div>
</div>

<div class="guru-content-wrapper">
    <div class="guru-header">
        <h1 class="guru-title">Tenaga Pendidik :</h1>
        <ul class="guru-list">
            <li>Sarjana Psikologi</li>
            <li>Sarjana Pendidikan</li>
            <li>Psikolog Terapis</li>
        </ul>
    </div>
    
    <div class="guru-grid">
        <?php if(empty($guru_images)): ?>
            <p style="text-align:center; grid-column: 1/-1;">Belum ada foto guru yang diunggah.</p>
        <?php else: ?>
            <?php foreach($guru_images as $img): ?>
                <div class="guru-card">
                    <a href="/talenta-semarang/<?php echo $img; ?>" data-lightbox="guru-gallery">
                        <img src="/talenta-semarang/<?php echo $img; ?>" alt="Guru Talenta">
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
