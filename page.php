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
                <a href='/talenta-semarang/' class='btn-primary' style='margin-top: 20px; text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 50px;'>Kembali ke Beranda</a>
            </div>
          </div>";
    require_once 'includes/footer.php';
    exit;
}

// Check if this is an Education unit (one of the 10 units or category = 'pendidikan')
$isEducationUnit = ($page->category === 'pendidikan' || in_array($slug, [
    'daycare', 'baby-class', 'play-group', 'kb-tk', 'sd', 'smp', 'sma', 'sd-inklusi', 'smp-inklusi', 'sma-inklusi'
]));

// Fetch Staff if Education Unit
$staffList = [];
if ($isEducationUnit) {
    $sStmt = $pdo->prepare("SELECT * FROM unit_staff WHERE unit_slug = ? ORDER BY sort_order ASC, id ASC");
    $sStmt->execute([$slug]);
    $staffList = $sStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch Galleries
$galStmt = $pdo->prepare("SELECT * FROM page_galleries WHERE page_slug = ? ORDER BY sort_order ASC, id ASC");
$galStmt->execute([$slug]);
$page_galleries = $galStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Contacts if Education Unit
$contacts = null;
if ($isEducationUnit) {
    $cStmt = $pdo->prepare("SELECT * FROM unit_contacts WHERE unit_slug = ?");
    $cStmt->execute([$slug]);
    $contacts = $cStmt->fetch(PDO::FETCH_ASSOC);
}

// Banner Image Mapping
$expected_banner = str_replace('-', '_', $slug) . '_banner.png';
$legacy_mappings = [
    'kb-tk' => 'tk_banner.png',
    'tk' => 'tk_banner.png',
    'pusat-psikologi-terapan' => 'pusat_psikologi_banner.png',
    'terapi' => 'pusat_terapi_banner.png',
    'terapi-abk' => 'terapi_abk_banner.png',
    'psikotes-konsultasi' => 'psikotes_konsultasi_banner.png'
];

if (!empty($page->banner_image) && file_exists('assets/images/' . $page->banner_image)) {
    $banner_img = $page->banner_image;
} elseif (array_key_exists($slug, $legacy_mappings)) {
    $banner_img = $legacy_mappings[$slug];
} else {
    $banner_img = $expected_banner;
}

if (!file_exists('assets/images/' . $banner_img)) {
    $banner_img = 'pusat_terapi_banner.png'; // fallback
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


        padding: 60px 20px;
        max-width: 1250px;
        margin: 0 auto;
    }
    .unit-section:not(:last-child) {
        border-bottom: 1px solid #e2e8f0;
    }
    .section-header-box {
        text-align: center;
        margin-bottom: 45px;
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
        font-size: 2.2rem;
        font-weight: 800;
        color: #0F3C5C;
        margin: 0 0 12px 0;
    }
    .section-desc {
        color: #64748b;
        font-size: 1.05rem;
        max-width: 700px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* STAFF CARDS */
    .staff-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
    }
    .staff-card {
        background: #ffffff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        text-align: center;
        padding: 35px 25px 30px;
        position: relative;
    }
    .staff-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 35px rgba(0, 162, 233, 0.12);
        border-color: #bae6fd;
    }
    .staff-avatar-wrapper {
        width: 130px;
        height: 130px;
        margin: 0 auto 20px;
        border-radius: 50%;
        padding: 6px;
        background: linear-gradient(135deg, #00A2E9, #0F3C5C);
        box-shadow: 0 8px 20px rgba(0,162,233,0.25);
    }
    .staff-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        background: #fff;
    }
    .staff-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: #0F3C5C;
        margin-bottom: 6px;
    }
    .staff-position {
        font-size: 0.88rem;
        font-weight: 600;
        color: #00A2E9;
        margin-bottom: 14px;
        display: inline-block;
        background: #f0f9ff;
        padding: 4px 12px;
        border-radius: 50px;
    }
    .staff-bio {
        font-size: 0.9rem;
        color: #64748b;
        line-height: 1.6;
        margin: 0;
    }

    /* GALLERY WITH CATEGORIES */
    .gallery-filter-bar {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 35px;
        flex-wrap: wrap;
    }
    .gal-filter-btn {
        padding: 10px 22px;
        border-radius: 50px;
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        font-weight: 600;
        font-size: 0.95rem;
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
        gap: 25px;
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
        transform: translateY(-6px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.12);
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
        top: 12px;
        left: 12px;
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
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
        padding: 14px 18px;
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
        background: #ffffff;
        line-height: 1.4;
    }

    /* KONTAK CARDS */
    .contact-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
        max-width: 1050px;
        margin: 0 auto;
    }
    @media (max-width: 900px) {
        .contact-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 600px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
    }
    .contact-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 25px 20px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.04);
        border: 1px solid #e2e8f0;
        text-align: center;
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .contact-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.08);
    }
    .contact-icon-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 14px;
        color: white;
    }
    .bg-wa { background: #25D366; }
    .bg-ig { background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%); }
    .bg-email { background: #EA4335; }
    .bg-fb { background: #1877F2; }
    .bg-tiktok { background: #000000; }
    .bg-yt { background: #FF0000; }

    .contact-card-title {
        font-size: 1rem;
        font-weight: 700;
        color: #0F3C5C;
        margin-bottom: 4px;
    }
    .contact-card-val {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 15px;
        word-break: break-all;
    }
    .contact-card-btn {
        display: inline-block;
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #0070ad;
        background: #f0f9ff;
        text-decoration: none;
        transition: all 0.2s;
    }
    .contact-card:hover .contact-card-btn {
        background: #0070ad;
        color: #ffffff;
    }
</style>


<div class="page-banner-container">
    <img src="/talenta-semarang/assets/images/<?php echo $banner_img; ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($page->title); ?>" class="page-banner-img">
</div>

<?php if ($isEducationUnit): ?>
<!-- ========================================== -->
<!-- 1. TENTANG SECTION -->
<!-- ========================================== -->

<section id="tentang" class="unit-section">
    <div class="section-header-box">
        <span class="section-tag">Profil Unit</span>
        <h2 class="section-title">Tentang <?php echo htmlspecialchars($page->title); ?></h2>
        <p class="section-desc">Pendidikan inklusif & holistik yang mengoptimalkan potensi unik setiap anak bersama Sekolah Talenta Semarang.</p>
    </div>

    <div style="background: #ffffff; border-radius: 24px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
        <div class="rich-text" style="font-size: 1.05rem; line-height: 1.8; color: #334155;">
            <?php 
            $clean_content = preg_replace('/<a[^>]*class=["\']?btn-green["\']?[^>]*>.*?<\/a>/is', '', $page->content_html);
            $clean_content = preg_replace('/<a[^>]*>.*?Cek Fasilitas.*?<\/a>/is', '', $clean_content);
            echo $clean_content; 
            ?>
        </div>

    </div>
</section>

<!-- ========================================== -->
<!-- 2. STAFF SECTION -->
<!-- ========================================== -->
<section id="staff" class="unit-section" style="background: #f8fafc;">
    <div class="section-header-box">
        <span class="section-tag" style="background: #ede9fe; color: #7c3aed;">Tenaga Pendidik</span>
        <h2 class="section-title">Staff & Pengajar <?php echo htmlspecialchars($page->title); ?></h2>
        <p class="section-desc">Guru berdedikasi, penuh kasih, dan berpengalaman yang siap membimbing dan mengawal perkembangan ananda.</p>
    </div>

    <div class="staff-grid">
        <?php foreach ($staffList as $staff): ?>
        <div class="staff-card">
            <div class="staff-avatar-wrapper">
                <img src="/talenta-semarang/assets/images/<?php echo htmlspecialchars($staff['photo']); ?>" alt="<?php echo htmlspecialchars($staff['name']); ?>" class="staff-avatar-img">
            </div>
            <div class="staff-name"><?php echo htmlspecialchars($staff['name']); ?></div>
            <div class="staff-position"><?php echo htmlspecialchars($staff['position']); ?></div>
            <p class="staff-bio"><?php echo htmlspecialchars($staff['bio']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ========================================== -->
<!-- 3. GALLERY SECTION (PRESTASI, AKTIVITAS, FASILITAS) -->
<!-- ========================================== -->
<section id="gallery" class="unit-section">
    <div class="section-header-box">
        <span class="section-tag" style="background: #fef3c7; color: #b45309;">Dokumentasi</span>
        <h2 class="section-title">Galeri <?php echo htmlspecialchars($page->title); ?></h2>
        <p class="section-desc">Dokumentasi momen berharga, prestasi membanggakan, aktivitas pembelajaran ceria, dan fasilitas ruang belajar.</p>
    </div>

    <!-- CATEGORY FILTER BUTTONS -->
    <div class="gallery-filter-bar">
        <button class="gal-filter-btn active" onclick="filterGalCategory('all')">
            <i class="fas fa-th"></i> Semua Foto
        </button>
        <button class="gal-filter-btn" onclick="filterGalCategory('prestasi')">
            <i class="fas fa-trophy" style="color: #f59e0b;"></i> Prestasi
        </button>
        <button class="gal-filter-btn" onclick="filterGalCategory('aktivitas')">
            <i class="fas fa-palette" style="color: #00A2E9;"></i> Aktivitas Belajar
        </button>
        <button class="gal-filter-btn" onclick="filterGalCategory('fasilitas')">
            <i class="fas fa-school" style="color: #10b981;"></i> Fasilitas Unit
        </button>
    </div>

    <!-- GALLERY GRID -->
    <div class="gallery-grid-custom" id="unitGalleryGrid">
        <?php foreach ($page_galleries as $gal): ?>
            <?php 
            $cat = strtolower($gal['category'] ?? 'aktivitas');
            $badgeLabel = ($cat === 'prestasi') ? 'Prestasi' : (($cat === 'fasilitas') ? 'Fasilitas' : 'Aktivitas');
            $badgeClass = 'badge-' . $cat;
            $caption = !empty($gal['caption']) ? $gal['caption'] : 'Dokumentasi ' . $page->title;
            ?>
            <div class="gal-card-item" data-cat="<?php echo $cat; ?>" onclick="openLightbox('/talenta-semarang/assets/images/<?php echo htmlspecialchars($gal['image_file']); ?>')">
                <div class="gal-thumb-wrapper">
                    <img src="/talenta-semarang/assets/images/<?php echo htmlspecialchars($gal['image_file']); ?>" class="gal-thumb-img" alt="<?php echo htmlspecialchars($caption); ?>">
                    <span class="gal-cat-badge <?php echo $badgeClass; ?>"><?php echo $badgeLabel; ?></span>
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
    <div class="section-header-box">
        <span class="section-tag" style="background: #dcfce7; color: #15803d;">Komunikasi & Sosmed</span>
        <h2 class="section-title">Kontak & Media Sosial <?php echo htmlspecialchars($page->title); ?></h2>
        <p class="section-desc">Hubungi kami secara langsung atau ikuti media sosial resmi unit untuk mendapatkan informasi program terkini.</p>
    </div>

    <?php 
    $waNumber = $contacts['whatsapp'] ?? '0817838939';
    $waClean = ltrim(preg_replace('/[^0-9]/', '', $waNumber), '0');
    $igHandle = $contacts['instagram'] ?? '@talentasemarang';
    $igClean = ltrim($igHandle, '@');
    $emailAddr = $contacts['email'] ?? 'talenta.lpt@gmail.com';
    $fbName = $contacts['facebook'] ?? 'Sekolah Talenta Semarang';
    $tiktokHandle = $contacts['tiktok'] ?? '@talentasemarang';
    $ytName = $contacts['youtube'] ?? 'Sekolah Talenta Semarang';
    ?>

    <!-- 6 SOCIAL / CONTACT CARDS -->
    <div class="contact-grid">
        <!-- 1. WHATSAPP -->
        <div class="contact-card">
            <div class="contact-icon-circle bg-wa">
                <i class="fab fa-whatsapp"></i>
            </div>
            <div class="contact-card-title">WhatsApp Resmi</div>
            <div class="contact-card-val"><?php echo htmlspecialchars($waNumber); ?></div>
            <a href="https://wa.me/62<?php echo $waClean; ?>" target="_blank" class="contact-card-btn">
                <i class="fab fa-whatsapp"></i> Chat WhatsApp
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
                <i class="fab fa-instagram"></i> Kunjungi Profil
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
                <i class="fab fa-facebook-f"></i> Buka Facebook
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
                <i class="fab fa-tiktok"></i> Tonton di TikTok
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
                <i class="fab fa-youtube"></i> Tonton Video
            </a>
        </div>
    </div>
</section>

<script>
// Filter gallery categories
function filterGalCategory(cat) {
    document.querySelectorAll('.gal-filter-btn').forEach(btn => btn.classList.remove('active'));
    event.currentTarget.classList.add('active');

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
<!-- STANDARD PAGE LAYOUT FOR NON-EDUCATION PAGES (E.G. LAYANAN PSIKOLOGI, DLL) -->
<style>
    .page-container {
        max-width: 1300px !important;
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
        font-size: 2.5rem;
        font-weight: 800;
        margin: 0;
        line-height: 1.25;
    }

    /* ENHANCED TYPOGRAPHY FOR FULL-WIDTH COMFORTABLE READING */
    .rich-text {
        font-size: 1.15rem !important;
        line-height: 1.85 !important;
        color: #334155 !important;
    }
    .rich-text p {
        font-size: 1.15rem !important;
        line-height: 1.85 !important;
        color: #334155 !important;
        margin-bottom: 22px !important;
        text-align: justify;
    }
    .rich-text h2 {
        font-size: 1.95rem !important;
        color: #0070ad !important;
        font-weight: 800 !important;
        margin: 35px 0 20px !important;
        line-height: 1.3 !important;
    }
    .rich-text h3 {
        font-size: 1.45rem !important;
        color: #0F3C5C !important;
        font-weight: 700 !important;
        margin: 30px 0 15px !important;
        line-height: 1.35 !important;
    }
    .rich-text ul, .rich-text ol {
        font-size: 1.12rem !important;
        line-height: 1.85 !important;
        color: #334155 !important;
        margin-bottom: 25px !important;
    }
    .rich-text li {
        margin-bottom: 12px !important;
    }

    /* SPECIFIC RESPONSIVE & WIDE ENHANCEMENTS FOR LAYANAN PSIKOLOGI */
    .rich-text .lpt-top-grid {
        display: flex !important;
        flex-direction: row !important;
        gap: 45px !important;
        align-items: flex-start !important;
        margin-bottom: 35px !important;
    }
    .rich-text .lpt-top-grid > div:first-child {
        flex: 0 0 460px !important;
        max-width: 45% !important;
    }
    .rich-text .lpt-top-grid > div:last-child {
        flex: 1 !important;
    }
    .rich-text .lpt-top-img {
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        float: none !important;
        margin: 0 !important;
        border-radius: 16px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
        object-fit: cover !important;
    }
    .rich-text .lpt-title,
    .rich-text .st-title,
    .rich-text .layanan-title,
    .rich-text .blue-title {
        color: #0070ad !important;
        font-size: 2rem !important;
        font-weight: 800 !important;
        margin-top: 0 !important;
        margin-bottom: 20px !important;
        line-height: 1.3 !important;
    }
    .rich-text .lpt-gallery {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 25px !important;
        margin: 40px 0 !important;
    }
    .rich-text .lpt-gallery img {
        width: 100% !important;
        height: 270px !important;
        object-fit: cover !important;
        border-radius: 14px !important;
        box-shadow: 0 6px 20px rgba(0,0,0,0.08) !important;
        transition: transform 0.3s ease !important;
        cursor: pointer !important;
    }
    .rich-text .lpt-gallery img:hover {
        transform: scale(1.03) !important;
    }
    .rich-text .lpt-list h3 {
        font-size: 1.45rem !important;
        font-weight: 700 !important;
        color: #0F3C5C !important;
        margin-bottom: 20px !important;
    }
    .rich-text .lpt-list ol {
        padding-left: 25px !important;
    }
    .rich-text .lpt-list ol > li {
        font-size: 1.15rem !important;
        font-weight: 700 !important;
        color: #0F3C5C !important;
        margin-bottom: 18px !important;
        line-height: 1.6 !important;
    }
    .rich-text .lpt-list ol > li p,
    .rich-text .lpt-list ol > li ul {
        font-weight: 400 !important;
        color: #334155 !important;
        margin-top: 8px !important;
        font-size: 1.1rem !important;
        line-height: 1.85 !important;
    }

    /* SEKILAS TERAPI, LAYANAN & PROSES TERAPI ENHANCEMENTS */
    .rich-text .st-top-img,
    .rich-text .layanan-top-img {
        width: 460px !important;
        max-width: 45% !important;
        float: left !important;
        margin: 0 45px 25px 0 !important;
        border-radius: 16px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
    }
    .rich-text .st-lists-container,
    .rich-text .pt-container {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 50px !important;
        margin: 45px 0 !important;
    }
    .rich-text .layanan-cards {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 30px !important;
        margin-bottom: 50px !important;
    }
    .rich-text .layanan-card {
        padding: 30px 25px !important;
        border-radius: 16px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 8px 20px rgba(0,0,0,0.05) !important;
    }
    .rich-text .daycare-wrapper {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 45px !important;
        margin-bottom: 60px !important;
    }
    .rich-text .daycare-images {
        flex: 1 !important;
        min-width: 340px !important;
    }
    .rich-text .daycare-images img {
        border-radius: 16px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
    }
    .rich-text .daycare-content {
        flex: 1.6 !important;
        min-width: 340px !important;
    }

    @media (max-width: 900px) {
        .rich-text .lpt-top-grid {
            flex-direction: column !important;
        }
        .rich-text .lpt-top-grid > div:first-child {
            flex: none !important;
            max-width: 100% !important;
            width: 100% !important;
        }
        .rich-text .st-top-img,
        .rich-text .layanan-top-img {
            width: 100% !important;
            max-width: 100% !important;
            float: none !important;
            margin: 0 0 25px 0 !important;
        }
        .rich-text .lpt-gallery {
            grid-template-columns: 1fr !important;
        }
        .rich-text .st-lists-container,
        .rich-text .pt-container {
            grid-template-columns: 1fr !important;
            gap: 30px !important;
        }
        .rich-text .layanan-cards {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<div class="page-container">
    <div class="page-content-box">
        <div class="page-header-header">
            <span class="page-header-badge">
                <?php echo ($page->category === 'psikologi') ? 'Layanan Psikologi & Terapi' : 'Halaman Informasi'; ?>
            </span>
            <h1 class="page-header-title"><?php echo htmlspecialchars($page->title); ?></h1>
        </div>

        <div class="rich-text">
            <?php 
            $clean_psi_content = preg_replace('/<a[^>]*class=["\']?btn-green["\']?[^>]*>.*?<\/a>/is', '', $page->content_html);
            $clean_psi_content = preg_replace('/<a[^>]*>.*?Cek Fasilitas.*?<\/a>/is', '', $clean_psi_content);

            // Dynamically replace featured image if updated
            if (!empty($page->featured_image)) {
                $clean_psi_content = preg_replace('/src=["\'][^"\']*psikologi_1\.png["\']/i', 'src="/talenta-semarang/assets/images/' . htmlspecialchars($page->featured_image) . '"', $clean_psi_content);
                $clean_psi_content = preg_replace('/<img([^>]*class=["\']?lpt-top-img["\']?[^>]*)src=["\'][^"\']+["\']/i', '<img$1src="/talenta-semarang/assets/images/' . htmlspecialchars($page->featured_image) . '"', $clean_psi_content);
            }

            // Dynamically replace gallery photos from page_galleries
            $hasDynamicGalleryRendered = false;
            if (!empty($page_galleries)) {
                $galHtml = '<div class="lpt-gallery">';
                foreach ($page_galleries as $g) {
                    $captionText = !empty($g['caption']) ? htmlspecialchars($g['caption']) : 'Dokumentasi Layanan';
                    $galHtml .= '<div style="position:relative; cursor:pointer; border-radius:14px; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.08);" onclick="openLightbox(\'/talenta-semarang/assets/images/' . htmlspecialchars($g['image_file']) . '\')">';
                    $galHtml .= '<img src="/talenta-semarang/assets/images/' . htmlspecialchars($g['image_file']) . '" alt="' . $captionText . '" style="width:100%; height:270px; object-fit:cover; display:block; transition:transform 0.3s ease;">';
                    if (!empty($g['caption'])) {
                        $galHtml .= '<div style="position:absolute; bottom:0; left:0; right:0; background:rgba(15,23,42,0.75); color:#fff; font-size:0.85rem; padding:8px 12px; backdrop-filter:blur(3px); line-height:1.4;">' . $captionText . '</div>';
                    }
                    $galHtml .= '</div>';
                }
                $galHtml .= '</div>';
                
                if (strpos($clean_psi_content, 'lpt-gallery') !== false) {
                    $clean_psi_content = preg_replace('/<div class=["\']lpt-gallery["\']>.*?<\/div>/is', $galHtml, $clean_psi_content);
                    $hasDynamicGalleryRendered = true;
                }
            }
            echo $clean_psi_content; 
            ?>
        </div>

        <?php if (!empty($page_galleries) && !$hasDynamicGalleryRendered): ?>
        <div style="margin-top: 50px; padding-top: 35px; border-top: 2px dashed #e2e8f0;">
            <h3 style="color: #0F3C5C; margin-bottom: 25px; text-align: center; font-size: 1.8rem;">Galeri Dokumentasi</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px;">
                <?php foreach($page_galleries as $gal): ?>
                    <div style="border-radius: 16px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.08); cursor: pointer;" onclick="openLightbox('/talenta-semarang/assets/images/<?php echo htmlspecialchars($gal['image_file']); ?>')">
                        <img src="/talenta-semarang/assets/images/<?php echo htmlspecialchars($gal['image_file']); ?>" style="width: 100%; aspect-ratio: 4/3; object-fit: cover; display: block;" alt="Galeri">
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
