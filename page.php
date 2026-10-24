<?php
// page.php
require_once 'includes/header.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

// Fetch page data from database
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$page = $stmt->fetch(PDO::FETCH_OBJ);

if (!$page) {
    echo "<div style='min-height: 60vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 40px;'>
            <div>
                <h1 style='font-size: 4rem; color: var(--primary-blue, #00A2E9);'>404</h1>
                <p>Halaman tidak ditemukan.</p>
                <a href='/outsource_talenta_landing/magangtalenta/' class='btn-primary' style='margin-top: 20px; text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 50px;'>Kembali ke Beranda</a>
            </div>
          </div>";
    require_once 'includes/footer.php';
    exit;
}

// Check if this is a Unit page (Education or Psychology)
$isUnitPage = (in_array($page->category, ['pendidikan', 'psikologi']) || in_array($slug, [
    'daycare', 'baby-class', 'play-group', 'kb-tk', 'sd', 'smp', 'sma', 'sd-inklusi', 'smp-inklusi', 'sma-inklusi',
    'pusat-psikologi-terapan', 'terapi-abk', 'psikotes-konsultasi', 'assessment', 'konsultasi', 'terapi', 'tes-psikologi', 'parenting', 'seminar', 'outbound'
]));

$isPsikologi = ($page->category === 'psikologi' || in_array($slug, [
    'pusat-psikologi-terapan', 'terapi-abk', 'psikotes-konsultasi', 'assessment', 'konsultasi', 'terapi', 'tes-psikologi', 'parenting', 'seminar', 'outbound'
]));

// Fetch Staff if Unit Page
$staffList = [];
if ($isUnitPage) {
    $sStmt = $pdo->prepare("SELECT * FROM unit_staff WHERE unit_slug = ? ORDER BY sort_order ASC, id ASC");
    $sStmt->execute([$slug]);
    $staffList = $sStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch Galleries
$galStmt = $pdo->prepare("SELECT * FROM page_galleries WHERE page_slug = ? ORDER BY sort_order ASC, id ASC");
$galStmt->execute([$slug]);
$page_galleries = $galStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Contacts if Unit Page
$contacts = null;
if ($isUnitPage) {
    $cStmt = $pdo->prepare("SELECT * FROM unit_contacts WHERE unit_slug = ?");
    $cStmt->execute([$slug]);
    $contacts = $cStmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch 3 latest Articles for Section 5
$recentArticles = [];
if ($isUnitPage) {
    $artStmt = $pdo->query("SELECT id, title, slug, content, excerpt, featured_image, published_at FROM articles ORDER BY published_at DESC LIMIT 3");
    $recentArticles = $artStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Banner Image Mapping
$expected_banner = str_replace('-', '_', $slug) . '_banner.png';
$legacy_mappings = [
    'kb-tk' => 'tk_banner.png',
    'tk' => 'tk_banner.png',
    'pusat-psikologi-terapan' => 'pusat_psikologi_banner.png',
    'terapi' => 'pusat_terapi_banner.png',
    'terapi-abk' => 'terapi_abk_banner.png',
    'psikotes-konsultasi' => 'psikotes_konsultasi_banner.png',
    'tes-psikologi' => 'tes_psikologi_banner.png'
];

if (!empty($page->banner_image) && file_exists('assets/images/' . $page->banner_image)) {
    $banner_img = $page->banner_image;
} elseif (array_key_exists($slug, $legacy_mappings) && file_exists('assets/images/' . $legacy_mappings[$slug])) {
    $banner_img = $legacy_mappings[$slug];
} elseif (file_exists('assets/images/' . $expected_banner)) {
    $banner_img = $expected_banner;
} else {
    $banner_img = $isPsikologi ? 'pusat_psikologi_banner.png' : 'hero_banner_final.png';
}
?>

<!-- BANNER -->
<style>
    .page-banner-container {
        width: 100%;
        margin-top: 80px;
        line-height: 0;
        background: #f8fafc;
    }
    .page-banner-img {
        width: 100%;
        height: auto;
        display: block;
    }

    /* UNIT SECTIONS STYLING */
    .btn-green { display: none !important; }
    .unit-section {
        padding: 55px 20px;
        max-width: 1250px;
        margin: 0 auto;
    }
    .unit-section:not(:last-child) {
        border-bottom: 1px solid #e2e8f0;
    }
    .section-header-box {
        text-align: center;
        margin-bottom: 40px;
    }
    .section-tag {
        display: inline-block;
        padding: 6px 16px;
        background: #e0f2fe;
        color: #0284c7;
        font-weight: 700;
        font-size: 0.82rem;
        border-radius: 50px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 10px;
    }
    .section-title {
        font-size: 2.1rem;
        font-weight: 800;
        color: #0F3C5C;
        margin: 0 0 10px 0;
    }
    .section-desc {
        color: #64748b;
        font-size: 1.02rem;
        max-width: 720px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* 1. TENTANG BOX */
    .about-content-box {
        background: #ffffff;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }
    @media (max-width: 768px) {
        .about-content-box {
            padding: 25px 20px;
        }
        .section-title {
            font-size: 1.7rem;
        }
    }

    /* 2. STAFF CARDS */
    .staff-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
    }
    .staff-card {
        background: #ffffff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        text-align: center;
        padding: 30px 22px 25px;
        position: relative;
    }
    .staff-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 32px rgba(0, 162, 233, 0.12);
        border-color: #bae6fd;
    }
    .staff-avatar-wrapper {
        width: 120px;
        height: 120px;
        margin: 0 auto 16px;
        border-radius: 50%;
        padding: 5px;
        background: linear-gradient(135deg, #00A2E9, #0F3C5C);
        box-shadow: 0 6px 18px rgba(0,162,233,0.22);
    }
    .staff-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        background: #fff;
    }
    .staff-name {
        font-size: 1.15rem;
        font-weight: 700;
        color: #0F3C5C;
        margin-bottom: 5px;
    }
    .staff-position {
        font-size: 0.85rem;
        font-weight: 600;
        color: #00A2E9;
        margin-bottom: 12px;
        display: inline-block;
        background: #f0f9ff;
        padding: 4px 12px;
        border-radius: 50px;
    }
    .staff-bio {
        font-size: 0.88rem;
        color: #64748b;
        line-height: 1.55;
        margin: 0;
    }

    /* 3. GALLERY WITH CATEGORIES */
    .gallery-filter-bar {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }
    .gal-filter-btn {
        padding: 8px 18px;
        border-radius: 50px;
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .gal-filter-btn:hover, .gal-filter-btn.active {
        background: #0F3C5C;
        color: #ffffff;
        border-color: #0F3C5C;
        box-shadow: 0 4px 12px rgba(15, 60, 92, 0.2);
    }
    .gallery-grid-custom {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 22px;
    }
    .gal-card-item {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        position: relative;
        cursor: pointer;
    }
    .gal-card-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 14px 28px rgba(0,0,0,0.1);
    }
    .gal-thumb-wrapper {
        position: relative;
        width: 100%;
        aspect-ratio: 4/3;
        overflow: hidden;
    }
    .gal-thumb-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .gal-card-item:hover .gal-thumb-img {
        transform: scale(1.08);
    }
    .gal-cat-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.25);
    }
    .badge-prestasi { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .badge-aktivitas { background: linear-gradient(135deg, #00A2E9, #0284c7); }
    .badge-fasilitas { background: linear-gradient(135deg, #10b981, #059669); }
    .gal-caption {
        padding: 12px 16px;
        font-size: 0.9rem;
        font-weight: 600;
        color: #1e293b;
        background: #ffffff;
        line-height: 1.4;
    }

    /* 4. REDESIGNED COMPACT CONTACT CARDS (Ukuran Kotak Lebih Kecil & Ramping) */
    .contact-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        max-width: 960px;
        margin: 0 auto;
    }
    @media (max-width: 820px) {
        .contact-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
    }
    @media (max-width: 520px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
    }
    .contact-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 16px 14px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        border: 1px solid #e2e8f0;
        text-align: center;
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .contact-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 22px rgba(0,0,0,0.08);
        border-color: #cbd5e1;
    }
    .contact-icon-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 10px;
        color: white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.12);
    }
    .bg-wa { background: #25D366; }
    .bg-ig { background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%); }
    .bg-email { background: #EA4335; }
    .bg-fb { background: #1877F2; }
    .bg-tiktok { background: #000000; }
    .bg-yt { background: #FF0000; }

    .contact-card-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #0F3C5C;
        margin-bottom: 3px;
    }
    .contact-card-val {
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 10px;
        word-break: break-all;
        max-width: 100%;
    }
    .contact-card-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 50px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #0070ad;
        background: #f0f9ff;
        text-decoration: none;
        transition: all 0.2s;
        border: 1px solid #bae6fd;
    }
    .contact-card:hover .contact-card-btn {
        background: #0070ad;
        color: #ffffff;
        border-color: #0070ad;
    }

    /* 5. ARTIKEL SECTION */
    .article-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
        max-width: 1250px;
        margin: 0 auto;
    }
    @media (max-width: 900px) {
        .article-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
    }
    @media (max-width: 600px) {
        .article-grid {
            grid-template-columns: 1fr;
        }
    }
    .article-card {
        background: #ffffff;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 24px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    .article-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 35px rgba(0,0,0,0.1);
        border-color: #bae6fd;
    }
    .article-thumb-wrapper {
        position: relative;
        width: 100%;
        aspect-ratio: 16/10;
        overflow: hidden;
        background: #f1f5f9;
    }
    .article-thumb-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .article-card:hover .article-thumb-img {
        transform: scale(1.06);
    }
    .article-date-badge {
        position: absolute;
        bottom: 12px;
        left: 12px;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(4px);
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .article-card-body {
        padding: 22px 20px 24px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .article-card-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #0F3C5C;
        margin: 0 0 10px 0;
        line-height: 1.4;
        transition: color 0.2s;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .article-card:hover .article-card-title {
        color: #00A2E9;
    }
    .article-card-excerpt {
        font-size: 0.9rem;
        color: #64748b;
        line-height: 1.6;
        margin: 0 0 18px 0;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .article-card-link {
        font-size: 0.88rem;
        font-weight: 700;
        color: #00A2E9;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: gap 0.2s ease, color 0.2s ease;
        margin-top: auto;
    }
    .article-card-link:hover {
        color: #0F3C5C;
        gap: 12px;
    }
    .btn-more-articles {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 28px;
        background: #ffffff;
        color: #0F3C5C;
        border: 2px solid #00A2E9;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.95rem;
        text-decoration: none;
        transition: all 0.25s ease;
        box-shadow: 0 4px 15px rgba(0, 162, 233, 0.1);
    }
    .btn-more-articles:hover {
        background: #00A2E9;
        color: #ffffff;
        box-shadow: 0 8px 25px rgba(0, 162, 233, 0.25);
        transform: translateY(-2px);
    }
</style>

<div class="page-banner-container">
    <img src="/outsource_talenta_landing/magangtalenta/assets/images/<?php echo $banner_img; ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($page->title); ?>" class="page-banner-img">
</div>

<?php if ($isUnitPage): ?>
<!-- ========================================== -->
<!-- 1. TENTANG SECTION -->
<!-- ========================================== -->
<section id="tentang" class="unit-section">
    <div class="section-header-box">
        <span class="section-tag" style="<?php echo $isPsikologi ? 'background:#ede9fe; color:#7c3aed;' : ''; ?>">
            <?php echo $isPsikologi ? 'Profil Layanan' : 'Profil Unit'; ?>
        </span>
        <h2 class="section-title">Tentang <?php echo htmlspecialchars($page->title); ?></h2>
        <p class="section-desc">
            <?php 
            if ($isPsikologi) {
                echo "Layanan psikologi terapan, evaluasi tumbuh kembang anak, dan stimulasi potensi terpadu bersama Lembaga Psikologi Terapan Talenta Semarang.";
            } else {
                echo "Pendidikan inklusif & holistik yang mengoptimalkan potensi unik setiap anak bersama Sekolah Talenta Semarang.";
            }
            ?>
        </p>
    </div>

    <div class="about-content-box">
        <div class="rich-text" style="font-size: 1.05rem; line-height: 1.8; color: #334155;">
            <?php 
            $clean_content = preg_replace('/<a[^>]*class=["\']?btn-green["\']?[^>]*>.*?<\/a>/is', '', $page->content_html);
            $clean_content = preg_replace('/<a[^>]*>.*?Cek Fasilitas.*?<\/a>/is', '', $clean_content);
            $clean_content = preg_replace('/<div class=["\']lpt-gallery["\']>.*?<\/div>/is', '', $clean_content);

            // Dynamically replace featured image if updated
            if (!empty($page->featured_image)) {
                $clean_content = preg_replace('/src=["\'][^"\']*psikologi_1\.png["\']/i', 'src="/outsource_talenta_landing/magangtalenta/assets/images/' . htmlspecialchars($page->featured_image) . '"', $clean_content);
                $clean_content = preg_replace('/<img([^>]*class=["\']?lpt-top-img["\']?[^>]*)src=["\'][^"\']+["\']/i', '<img$1src="/outsource_talenta_landing/magangtalenta/assets/images/' . htmlspecialchars($page->featured_image) . '"', $clean_content);
            }
            echo $clean_content; 
            ?>
        </div>
    </div>
</section>

<!-- ========================================== -->
<!-- 2. STAFF DAN TERAPIS SECTION -->
<!-- ========================================== -->
<section id="staff" class="unit-section" style="background: #f8fafc;">
    <div class="section-header-box">
        <span class="section-tag" style="background: #ede9fe; color: #7c3aed;">
            <?php echo $isPsikologi ? 'Tim Psikolog & Terapis' : 'Tenaga Pendidik'; ?>
        </span>
        <h2 class="section-title">
            <?php echo $isPsikologi ? 'Staff & Terapis ' . htmlspecialchars($page->title) : 'Staff & Pengajar ' . htmlspecialchars($page->title); ?>
        </h2>
        <p class="section-desc">
            <?php 
            if ($isPsikologi) {
                echo "Psikolog berlisensi, konselor, dan terapis berdedikasi yang siap mendampingi proses tumbuh kembang dan optimalisasi potensi ananda.";
            } else {
                echo "Guru berdedikasi, penuh kasih, dan berpengalaman yang siap membimbing dan mengawal perkembangan ananda.";
            }
            ?>
        </p>
    </div>

    <div class="staff-grid">
        <?php foreach ($staffList as $staff): ?>
        <div class="staff-card">
            <div class="staff-avatar-wrapper">
                <img src="/outsource_talenta_landing/magangtalenta/assets/images/<?php echo htmlspecialchars($staff['photo']); ?>" alt="<?php echo htmlspecialchars($staff['name']); ?>" class="staff-avatar-img">
            </div>
            <div class="staff-name"><?php echo htmlspecialchars($staff['name']); ?></div>
            <div class="staff-position"><?php echo htmlspecialchars($staff['position']); ?></div>
            <p class="staff-bio"><?php echo htmlspecialchars($staff['bio']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ========================================== -->
<!-- 3. GALLERY SECTION -->
<!-- ========================================== -->
<section id="gallery" class="unit-section">
    <div class="section-header-box">
        <span class="section-tag" style="background: #fef3c7; color: #b45309;">Dokumentasi</span>
        <h2 class="section-title">Galeri <?php echo htmlspecialchars($page->title); ?></h2>
        <p class="section-desc">
            <?php 
            if ($isPsikologi) {
                echo "Dokumentasi momen kegiatan penanganan, sesi konsultasi, dan aktivitas stimulasi tumbuh kembang anak.";
            } else {
                echo "Dokumentasi momen berharga, prestasi membanggakan, aktivitas pembelajaran ceria, dan fasilitas ruang penanganan.";
            }
            ?>
        </p>
    </div>

    <?php if (!$isPsikologi): ?>
    <!-- CATEGORY FILTER BUTTONS (HANYA UNTUK PENDIDIKAN) -->
    <div class="gallery-filter-bar">
        <button class="gal-filter-btn active" onclick="filterGalCategory('all')">
            <i class="fas fa-th"></i> Semua Foto
        </button>
        <button class="gal-filter-btn" onclick="filterGalCategory('prestasi')">
            <i class="fas fa-trophy" style="color: #f59e0b;"></i> Prestasi
        </button>
        <button class="gal-filter-btn" onclick="filterGalCategory('aktivitas')">
            <i class="fas fa-palette" style="color: #00A2E9;"></i> Aktivitas
        </button>
        <button class="gal-filter-btn" onclick="filterGalCategory('fasilitas')">
            <i class="fas fa-school" style="color: #10b981;"></i> Fasilitas
        </button>
    </div>
    <?php endif; ?>

    <!-- GALLERY GRID -->
    <div class="gallery-grid-custom" id="unitGalleryGrid">
        <?php foreach ($page_galleries as $gal): ?>
            <?php 
            $cat = strtolower($gal['category'] ?? 'aktivitas');
            $badgeLabel = ($cat === 'prestasi') ? 'Prestasi' : (($cat === 'fasilitas') ? 'Fasilitas' : 'Aktivitas');
            $badgeClass = 'badge-' . $cat;
            $caption = !empty($gal['caption']) ? $gal['caption'] : 'Dokumentasi ' . $page->title;
            ?>
            <div class="gal-card-item" data-cat="<?php echo $cat; ?>" onclick="openLightbox('/outsource_talenta_landing/magangtalenta/assets/images/<?php echo htmlspecialchars($gal['image_file']); ?>')">
                <div class="gal-thumb-wrapper">
                    <img src="/outsource_talenta_landing/magangtalenta/assets/images/<?php echo htmlspecialchars($gal['image_file']); ?>" class="gal-thumb-img" alt="<?php echo htmlspecialchars($caption); ?>">
                    <?php if (!$isPsikologi): ?>
                        <span class="gal-cat-badge <?php echo $badgeClass; ?>"><?php echo $badgeLabel; ?></span>
                    <?php endif; ?>
                </div>
                <div class="gal-caption">
                    <?php echo htmlspecialchars($caption); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ========================================== -->
<!-- 4. KONTAK SECTION (WA, IG, EMAIL, FB, TIKTOK, YOUTUBE) -->
<!-- ========================================== -->
<section id="kontak" class="unit-section" style="background: #f8fafc;">
    <div class="section-header-box" style="margin-bottom: 30px;">
        <span class="section-tag" style="background: #dcfce7; color: #15803d;">Komunikasi & Sosmed</span>
        <h2 class="section-title">Kontak & Media Sosial <?php echo htmlspecialchars($page->title); ?></h2>
        <p class="section-desc">Hubungi kami secara langsung atau ikuti media sosial resmi unit untuk mendapatkan informasi program terkini.</p>
    </div>

    <?php 
    $waNumber = $contacts['whatsapp'] ?? '0817-838-939';
    $waClean = ltrim(preg_replace('/[^0-9]/', '', $waNumber), '0');
    $igHandle = $contacts['instagram'] ?? '@talentasemarang';
    $igClean = ltrim($igHandle, '@');
    $emailAddr = $contacts['email'] ?? ($isPsikologi ? 'psikologi.terapan@talentasemarang.com' : 'talenta.lpt@gmail.com');
    $fbName = $contacts['facebook'] ?? ($isPsikologi ? 'Layanan Psikologi Talenta Semarang' : 'Sekolah Talenta Semarang');
    $tiktokHandle = $contacts['tiktok'] ?? '@talentasemarang';
    $ytName = $contacts['youtube'] ?? 'Sekolah Talenta Semarang';
    ?>

    <!-- 6 COMPACT SOCIAL / CONTACT CARDS (Tampilan Kotak Lebih Kecil & Ramping) -->
    <div class="contact-grid">
        <!-- 1. WHATSAPP -->
        <div class="contact-card">
            <div class="contact-icon-circle bg-wa">
                <i class="fab fa-whatsapp"></i>
            </div>
            <div class="contact-card-title">WhatsApp Resmi</div>
            <div class="contact-card-val"><?php echo htmlspecialchars($waNumber); ?></div>
            <a href="https://wa.me/62<?php echo $waClean; ?>" target="_blank" class="contact-card-btn">
                <i class="fab fa-whatsapp"></i> Chat WA
            </a>
        </div>

        <!-- 2. INSTAGRAM -->
        <div class="contact-card">
            <div class="contact-icon-circle bg-ig">
                <i class="fab fa-instagram"></i>
            </div>
            <div class="contact-card-title">Instagram</div>
            <div class="contact-card-val"><?php echo htmlspecialchars($igHandle); ?></div>
            <a href="https://instagram.com/<?php echo $igClean; ?>" target="_blank" class="contact-card-btn">
                <i class="fab fa-instagram"></i> Kunjungi
            </a>
        </div>

        <!-- 3. EMAIL -->
        <div class="contact-card">
            <div class="contact-icon-circle bg-email">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="contact-card-title">Email Resmi</div>
            <div class="contact-card-val"><?php echo htmlspecialchars($emailAddr); ?></div>
            <a href="mailto:<?php echo htmlspecialchars($emailAddr); ?>" class="contact-card-btn">
                <i class="fas fa-paper-plane"></i> Kirim Email
            </a>
        </div>

        <!-- 4. FACEBOOK -->
        <div class="contact-card">
            <div class="contact-icon-circle bg-fb">
                <i class="fab fa-facebook-f"></i>
            </div>
            <div class="contact-card-title">Facebook</div>
            <div class="contact-card-val"><?php echo htmlspecialchars($fbName); ?></div>
            <a href="https://facebook.com/search/top?q=<?php echo urlencode($fbName); ?>" target="_blank" class="contact-card-btn">
                <i class="fab fa-facebook-f"></i> Buka FB
            </a>
        </div>

        <!-- 5. TIKTOK -->
        <div class="contact-card">
            <div class="contact-icon-circle bg-tiktok">
                <i class="fab fa-tiktok"></i>
            </div>
            <div class="contact-card-title">TikTok</div>
            <div class="contact-card-val"><?php echo htmlspecialchars($tiktokHandle); ?></div>
            <a href="https://tiktok.com/<?php echo htmlspecialchars($tiktokHandle); ?>" target="_blank" class="contact-card-btn">
                <i class="fab fa-tiktok"></i> TikTok
            </a>
        </div>

        <!-- 6. YOUTUBE -->
        <div class="contact-card">
            <div class="contact-icon-circle bg-yt">
                <i class="fab fa-youtube"></i>
            </div>
            <div class="contact-card-title">YouTube</div>
            <div class="contact-card-val"><?php echo htmlspecialchars($ytName); ?></div>
            <a href="https://youtube.com/results?search_query=<?php echo urlencode($ytName); ?>" target="_blank" class="contact-card-btn">
                <i class="fab fa-youtube"></i> Video
            </a>
        </div>
    </div>
</section>

<!-- ========================================== -->
<!-- 5. ARTIKEL SECTION -->
<!-- ========================================== -->
<section id="artikel" class="unit-section">
    <div class="section-header-box">
        <span class="section-tag" style="background: #e0e7ff; color: #4338ca;">Edukasi & Wawasan</span>
        <h2 class="section-title">Artikel & Wawasan Terkini</h2>
        <p class="section-desc">Kumpulan artikel informatif dan panduan praktis seputar tumbuh kembang anak, psikologi belajar, dan pola asuh keluarga.</p>
    </div>

    <div class="article-grid">
        <?php foreach ($recentArticles as $art): ?>
            <?php 
            $artImg = !empty($art['featured_image']) ? $art['featured_image'] : '/outsource_talenta_landing/magangtalenta/assets/images/artikel_banner.png';
            if (strpos($artImg, '/') !== 0 && strpos($artImg, 'http') !== 0) {
                $artImg = '/outsource_talenta_landing/magangtalenta/assets/images/' . $artImg;
            }
            $publishDate = !empty($art['published_at']) ? date('d M Y', strtotime($art['published_at'])) : date('d M Y');
            $artExcerpt = !empty($art['excerpt']) ? strip_tags($art['excerpt']) : substr(strip_tags($art['content'] ?? ''), 0, 110) . '...';
            ?>
            <div class="article-card">
                <div class="article-thumb-wrapper">
                    <img src="<?php echo htmlspecialchars($artImg); ?>" alt="<?php echo htmlspecialchars($art['title']); ?>" class="article-thumb-img">
                    <span class="article-date-badge">
                        <i class="far fa-calendar-alt"></i> <?php echo $publishDate; ?>
                    </span>
                </div>
                <div class="article-card-body">
                    <h3 class="article-card-title"><?php echo htmlspecialchars($art['title']); ?></h3>
                    <p class="article-card-excerpt"><?php echo htmlspecialchars($artExcerpt); ?></p>
                    <a href="/outsource_talenta_landing/magangtalenta/artikel-detail.php?slug=<?php echo htmlspecialchars($art['slug']); ?>" class="article-card-link">
                        Baca Selengkapnya <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="text-align: center; margin-top: 45px;">
        <a href="/outsource_talenta_landing/magangtalenta/artikel.php" class="btn-more-articles">
            <i class="fas fa-book-open"></i> Lihat Semua Artikel Edukasi
        </a>
    </div>
</section>

<script>
// Filter gallery categories
function filterGalCategory(cat) {
    document.querySelectorAll('.gal-filter-btn').forEach(btn => btn.classList.remove('active'));
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    }

    const cards = document.querySelectorAll('.gal-card-item');
    cards.forEach(card => {
        const itemCat = card.getAttribute('data-cat');
        if (cat === 'all' || itemCat === cat) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php else: ?>
<!-- STANDARD PAGE LAYOUT FOR NON-UNIT PAGES (E.G. TENTANG KAMI, PROFIL, DLL) -->
<style>
    .page-container {
        max-width: 1200px !important;
        margin: 45px auto 90px !important;
        padding: 0 25px !important;
        display: block !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .page-content-box {
        background: #ffffff;
        border-radius: 24px;
        padding: 50px 60px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    @media (max-width: 768px) {
        .page-container {
            padding: 0 15px !important;
            margin: 25px auto 60px !important;
        }
        .page-content-box {
            padding: 28px 20px !important;
            border-radius: 18px !important;
        }
    }
    .page-header-header {
        margin-bottom: 35px;
        padding-bottom: 25px;
        border-bottom: 1px solid #e2e8f0;
    }
    .page-header-badge {
        display: inline-block;
        padding: 6px 16px;
        background: #ede9fe;
        color: #7c3aed;
        font-weight: 700;
        font-size: 0.82rem;
        border-radius: 50px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 12px;
    }
    .page-header-title {
        color: #0F3C5C;
        font-size: 2.3rem;
        font-weight: 800;
        margin: 0;
        line-height: 1.25;
    }
    .rich-text {
        font-size: 1.05rem;
        line-height: 1.8;
        color: #334155;
    }
</style>

<div class="page-container">
    <div class="page-content-box">
        <div class="page-header-header">
            <span class="page-header-badge">
                Halaman Informasi
            </span>
            <h1 class="page-header-title"><?php echo htmlspecialchars($page->title); ?></h1>
        </div>

        <div class="rich-text">
            <?php 
            $clean_generic = preg_replace('/<a[^>]*class=["\']?btn-green["\']?[^>]*>.*?<\/a>/is', '', $page->content_html);
            $clean_generic = preg_replace('/<a[^>]*>.*?Cek Fasilitas.*?<\/a>/is', '', $clean_generic);
            echo $clean_generic; 
            ?>
        </div>

        <?php if (!empty($page_galleries)): ?>
        <div style="margin-top: 50px; padding-top: 35px; border-top: 2px dashed #e2e8f0;">
            <h3 style="color: #0F3C5C; margin-bottom: 25px; text-align: center; font-size: 1.8rem;">Galeri Dokumentasi</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px;">
                <?php foreach($page_galleries as $gal): ?>
                    <div style="border-radius: 16px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.08); cursor: pointer;" onclick="openLightbox('/outsource_talenta_landing/magangtalenta/assets/images/<?php echo htmlspecialchars($gal['image_file']); ?>')">
                        <img src="/outsource_talenta_landing/magangtalenta/assets/images/<?php echo htmlspecialchars($gal['image_file']); ?>" style="width: 100%; aspect-ratio: 4/3; object-fit: cover; display: block;" alt="Galeri">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- LIGHTBOX MODAL -->
<div id="unitLightbox" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 999999; justify-content: center; align-items: center; padding: 20px;" onclick="closeLightbox()">
    <span style="position: absolute; top: 25px; right: 35px; color: white; font-size: 2.5rem; cursor: pointer; font-weight: 700;">&times;</span>
    <img id="lightboxImg" src="" style="max-width: 90%; max-height: 85vh; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); object-fit: contain;">
</div>

<script>
function openLightbox(src) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('unitLightbox').style.display = 'flex';
}
function closeLightbox() {
    document.getElementById('unitLightbox').style.display = 'none';
}
</script>

<?php require_once 'includes/footer.php'; ?>
