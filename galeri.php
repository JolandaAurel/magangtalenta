<?php
// galeri.php - Galeri Terintegrasi Seluruh Unit Pendidikan & Layanan Psikologi
require_once 'includes/header.php';

// Fetch all galleries joined with pages (Pendidikan and Layanan Psikologi)
$stmt = $pdo->query("
    SELECT 
        g.id,
        g.page_slug,
        g.image_file,
        g.category as photo_category,
        g.caption,
        g.sort_order,
        p.title as unit_title,
        p.category as unit_category,
        p.slug as unit_slug
    FROM page_galleries g
    INNER JOIN pages p ON g.page_slug = p.slug
    WHERE p.category IN ('pendidikan', 'psikologi')
    ORDER BY p.category ASC, p.id ASC, g.sort_order ASC, g.id ASC
");
$galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count summary
$totalPhotos = count($galleries);
$totalEdu = 0;
$totalPsi = 0;

$unitsList = [
    'pendidikan' => [],
    'psikologi' => []
];

foreach ($galleries as $g) {
    $cat = $g['unit_category'];
    if ($cat === 'pendidikan') $totalEdu++;
    if ($cat === 'psikologi') $totalPsi++;

    if (!isset($unitsList[$cat][$g['unit_slug']])) {
        $unitsList[$cat][$g['unit_slug']] = [
            'title' => $g['unit_title'],
            'count' => 0
        ];
    }
    $unitsList[$cat][$g['unit_slug']]['count']++;
}

// Pre-selected filter if passed via GET
$initialFilter = $_GET['filter'] ?? 'all';
$initialUnit = $_GET['unit'] ?? 'all';
?>

<style>
    /* BANNER & HEADER */
    .galeri-banner-container {
        width: 100%;
        margin-top: 80px;
        line-height: 0;
        background: #f8fafc;
    }
    .galeri-banner-img {
        width: 100%;
        height: auto;
        display: block;
    }

    .galeri-header-section {
        background: #ffffff;
        padding: 50px 20px 25px;
        text-align: center;
        max-width: 1250px;
        margin: 0 auto;
    }
    .galeri-tag {
        display: inline-block;
        padding: 6px 16px;
        background: #e0f2fe;
        color: #0284c7;
        font-weight: 700;
        font-size: 0.82rem;
        border-radius: 50px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 12px;
    }
    .galeri-title {
        font-size: 2.3rem;
        font-weight: 800;
        color: #0F3C5C;
        margin: 0 0 12px 0;
    }
    .galeri-desc {
        color: #64748b;
        font-size: 1.05rem;
        max-width: 760px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* FILTER BAR & CONTROLS */
    .galeri-filter-wrapper {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        padding: 25px 20px;
        margin-bottom: 40px;
    }
    .galeri-filter-container {
        max-width: 1250px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* LEVEL 1: MAIN CATEGORY BUTTONS */
    .cat-btn-group {
        display: flex;
        justify-content: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .cat-filter-btn {
        padding: 10px 22px;
        border-radius: 50px;
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        font-weight: 700;
        font-size: 0.92rem;
        cursor: pointer;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .cat-filter-btn:hover {
        border-color: #00A2E9;
        color: #00A2E9;
        background: #f0f9ff;
    }
    .cat-filter-btn.active {
        background: #0F3C5C;
        color: #ffffff;
        border-color: #0F3C5C;
        box-shadow: 0 4px 14px rgba(15, 60, 92, 0.25);
    }
    .cat-filter-btn.active .count-pill {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }
    .count-pill {
        padding: 2px 8px;
        border-radius: 20px;
        background: #e2e8f0;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 700;
        transition: all 0.2s;
    }

    /* LEVEL 2: SPECIFIC UNIT SELECTOR & SEARCH */
    .filter-secondary-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }
    .unit-select-box {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 280px;
        max-width: 480px;
    }
    .unit-select-box label {
        font-weight: 700;
        font-size: 0.88rem;
        color: #0F3C5C;
        white-space: nowrap;
    }
    .unit-dropdown {
        width: 100%;
        padding: 10px 16px;
        border-radius: 12px;
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        font-size: 0.9rem;
        color: #334155;
        font-weight: 600;
        outline: none;
        transition: all 0.2s;
        cursor: pointer;
    }
    .unit-dropdown:focus {
        border-color: #00A2E9;
        box-shadow: 0 0 0 3px rgba(0, 162, 233, 0.15);
    }

    .search-box-wrapper {
        position: relative;
        min-width: 260px;
    }
    .search-box-wrapper input {
        width: 100%;
        padding: 10px 16px 10px 40px;
        border-radius: 50px;
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        font-size: 0.88rem;
        color: #334155;
        outline: none;
        box-sizing: border-box;
    }
    .search-box-wrapper input:focus {
        border-color: #00A2E9;
    }
    .search-box-wrapper i {
        position: absolute;
        top: 50%;
        left: 14px;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .counter-info {
        font-size: 0.9rem;
        color: #64748b;
        font-weight: 600;
    }

    /* GALLERY GRID */
    .gallery-main-section {
        max-width: 1250px;
        margin: 0 auto 80px;
        padding: 0 20px;
    }
    .gal-integrated-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
    }
    .gal-integrated-card {
        background: #ffffff;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        cursor: pointer;
        position: relative;
    }
    .gal-integrated-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 32px rgba(0,0,0,0.1);
        border-color: #bae6fd;
    }

    /* THUMBNAIL */
    .gal-integrated-thumb {
        position: relative;
        width: 100%;
        aspect-ratio: 4/3;
        overflow: hidden;
        background: #f1f5f9;
    }
    .gal-integrated-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
        display: block;
    }
    .gal-integrated-card:hover .gal-integrated-thumb img {
        transform: scale(1.08);
    }

    /* UNIT BADGE OVERLAY */
    .unit-type-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.25);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        z-index: 2;
        letter-spacing: 0.3px;
    }
    .badge-edu {
        background: linear-gradient(135deg, #0284c7, #00A2E9);
    }
    .badge-psi {
        background: linear-gradient(135deg, #7c3aed, #8b5cf6);
    }

    .gal-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 1;
        backdrop-filter: blur(2px);
    }
    .gal-integrated-card:hover .gal-hover-overlay {
        opacity: 1;
    }
    .btn-zoom-overlay {
        background: #ffffff;
        color: #0F3C5C;
        padding: 8px 18px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.85rem;
        box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* CARD BODY & FOOTER */
    .gal-integrated-body {
        padding: 16px 18px 18px;
        display: flex;
        flex-direction: column;
        flex: 1;
        justify-content: space-between;
        background: #ffffff;
    }
    .gal-integrated-caption {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.45;
        margin-bottom: 14px;
    }
    .gal-integrated-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #f1f5f9;
        padding-top: 12px;
        margin-top: auto;
    }
    .gal-unit-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .gal-integrated-link {
        font-size: 0.82rem;
        font-weight: 700;
        color: #00A2E9;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s;
    }
    .gal-integrated-link:hover {
        color: #0F3C5C;
        transform: translateX(3px);
    }

    /* EMPTY STATE */
    .empty-gal-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        background: #ffffff;
        border-radius: 18px;
        border: 2px dashed #cbd5e1;
    }
    .empty-gal-state i {
        font-size: 3.5rem;
        color: #cbd5e1;
        margin-bottom: 15px;
    }
    .empty-gal-state h3 {
        color: #0F3C5C;
        margin-bottom: 8px;
    }
    .empty-gal-state p {
        color: #64748b;
        margin-bottom: 20px;
    }

    /* LIGHTBOX MODAL */
    .gal-lightbox-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(15, 23, 42, 0.9);
        backdrop-filter: blur(8px);
        z-index: 999999;
        justify-content: center;
        align-items: center;
        padding: 25px;
        box-sizing: border-box;
    }
    .gal-lightbox-box {
        max-width: 950px;
        max-height: 92vh;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }
    .gal-lightbox-img {
        max-width: 100%;
        max-height: 72vh;
        object-fit: contain;
        border-radius: 14px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.5);
    }
    .gal-lightbox-info {
        margin-top: 15px;
        background: rgba(255, 255, 255, 0.95);
        padding: 16px 24px;
        border-radius: 14px;
        width: 100%;
        max-width: 800px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        box-sizing: border-box;
    }
    .gal-lightbox-close {
        position: absolute;
        top: -45px;
        right: 0;
        color: #ffffff;
        font-size: 2.2rem;
        cursor: pointer;
        font-weight: 700;
        line-height: 1;
        transition: transform 0.2s;
    }
    .gal-lightbox-close:hover {
        transform: scale(1.15);
        color: #ef4444;
    }

    @media (max-width: 768px) {
        .galeri-title {
            font-size: 1.8rem;
        }
        .filter-secondary-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .unit-select-box {
            max-width: 100%;
        }
        .gal-lightbox-info {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<!-- BANNER -->
<div class="galeri-banner-container">
    <img src="/outsource_talenta_landing/magangtalenta/assets/images/galeri_banner.png" alt="Galeri Talenta Semarang" class="galeri-banner-img">
</div>

<!-- HEADER INFO -->
<section class="galeri-header-section">
    <span class="galeri-tag">
        <i class="fas fa-photo-video"></i> Publikasi & Dokumentasi
    </span>
    <h1 class="galeri-title">Galeri Terintegrasi Seluruh Unit</h1>
    <p class="galeri-desc">
        Dokumentasi momen kegiatan edukatif, fasilitas unggulan, prestasi siswa, dan sesi penanganan dari seluruh <b>10 Unit Pendidikan</b> serta <b>10 Layanan Psikologi</b> Talenta Semarang.
    </p>
</section>

<!-- FILTER BAR -->
<div class="galeri-filter-wrapper">
    <div class="galeri-filter-container">
        <!-- 1. KATEGORI UTAMA: SEMUA / PENDIDIKAN / PSIKOLOGI -->
        <div class="cat-btn-group">
            <button class="cat-filter-btn active" onclick="setMainCategory('all', this)">
                <i class="fas fa-th-large"></i> Semua Unit 
                <span class="count-pill"><?php echo $totalPhotos; ?></span>
            </button>
            <button class="cat-filter-btn" onclick="setMainCategory('pendidikan', this)">
                <i class="fas fa-graduation-cap" style="color: #00A2E9;"></i> Unit Pendidikan 
                <span class="count-pill"><?php echo $totalEdu; ?></span>
            </button>
            <button class="cat-filter-btn" onclick="setMainCategory('psikologi', this)">
                <i class="fas fa-brain" style="color: #8b5cf6;"></i> Layanan Psikologi 
                <span class="count-pill"><?php echo $totalPsi; ?></span>
            </button>
        </div>

        <!-- 2. DROPDOWN UNIT SPESIFIK & SEARCH BOX -->
        <div class="filter-secondary-bar">
            <div class="unit-select-box">
                <label for="unitDropdownFilter"><i class="fas fa-filter"></i> Unit:</label>
                <select id="unitDropdownFilter" class="unit-dropdown" onchange="onUnitSelectChange()">
                    <option value="all">Semua Unit (<?php echo $totalPhotos; ?> Foto)</option>
                    
                    <optgroup label="── Unit Pendidikan ──">
                        <?php foreach ($unitsList['pendidikan'] as $slug => $data): ?>
                            <option value="<?php echo htmlspecialchars($slug); ?>">
                                <?php echo htmlspecialchars($data['title']); ?> (<?php echo $data['count']; ?> Foto)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>

                    <optgroup label="── Layanan Psikologi ──">
                        <?php foreach ($unitsList['psikologi'] as $slug => $data): ?>
                            <option value="<?php echo htmlspecialchars($slug); ?>">
                                <?php echo htmlspecialchars($data['title']); ?> (<?php echo $data['count']; ?> Foto)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>

            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <div class="search-box-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="gallerySearchInput" placeholder="Cari foto atau nama unit..." onkeyup="onSearchChange()">
                </div>
                <div class="counter-info">
                    Menampilkan <b id="visibleCount" style="color: #0F3C5C;"><?php echo $totalPhotos; ?></b> foto
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MAIN GALLERY GRID -->
<section class="gallery-main-section">
    <div class="gal-integrated-grid" id="integratedGalleryGrid">
        <?php foreach ($galleries as $g): ?>
            <?php 
            $caption = !empty($g['caption']) ? $g['caption'] : 'Dokumentasi ' . $g['unit_title'];
            $imgUrl = '/outsource_talenta_landing/magangtalenta/assets/images/' . $g['image_file'];
            $badgeClass = ($g['unit_category'] === 'psikologi') ? 'badge-psi' : 'badge-edu';
            $badgeIcon = ($g['unit_category'] === 'psikologi') ? 'fa-brain' : 'fa-graduation-cap';
            ?>
            <div class="gal-integrated-card" 
                 data-unit-cat="<?php echo htmlspecialchars($g['unit_category']); ?>" 
                 data-unit-slug="<?php echo htmlspecialchars($g['unit_slug']); ?>" 
                 data-text="<?php echo strtolower(htmlspecialchars($g['unit_title'] . ' ' . $caption)); ?>"
                 onclick="openIntegratedLightbox('<?php echo htmlspecialchars($imgUrl); ?>', '<?php echo htmlspecialchars(addslashes($caption)); ?>', '<?php echo htmlspecialchars(addslashes($g['unit_title'])); ?>', '<?php echo htmlspecialchars($g['unit_category']); ?>', '<?php echo htmlspecialchars($g['unit_slug']); ?>')">
                
                <div class="gal-integrated-thumb">
                    <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($caption); ?>" loading="lazy">
                    
                    <!-- NAMA UNIT BADGE (Keterangan unit asal foto) -->
                    <span class="unit-type-badge <?php echo $badgeClass; ?>">
                        <i class="fas <?php echo $badgeIcon; ?>"></i>
                        <?php echo htmlspecialchars($g['unit_title']); ?>
                    </span>

                    <div class="gal-hover-overlay">
                        <span class="btn-zoom-overlay">
                            <i class="fas fa-search-plus"></i> Perbesar
                        </span>
                    </div>
                </div>

                <div class="gal-integrated-body">
                    <div class="gal-integrated-caption">
                        <?php echo htmlspecialchars($caption); ?>
                    </div>
                    <div class="gal-integrated-footer">
                        <span class="gal-unit-label">
                            <i class="fas <?php echo $badgeIcon; ?>" style="color: <?php echo $g['unit_category'] === 'psikologi' ? '#7c3aed' : '#00A2E9'; ?>;"></i>
                            <?php echo htmlspecialchars($g['unit_title']); ?>
                        </span>
                        <a href="/outsource_talenta_landing/magangtalenta/page.php?slug=<?php echo htmlspecialchars($g['unit_slug']); ?>" 
                           class="gal-integrated-link" 
                           onclick="event.stopPropagation();"
                           title="Kunjungi Halaman <?php echo htmlspecialchars($g['unit_title']); ?>">
                            Halaman Unit <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- EMPTY STATE IF FILTER HAS NO RESULTS -->
        <div id="galleryEmptyState" class="empty-gal-state" style="display: none;">
            <i class="fas fa-images"></i>
            <h3>Tidak Ada Foto Ditemukan</h3>
            <p>Tidak ada dokumentasi yang sesuai dengan kata kunci atau filter yang Anda pilih.</p>
            <button onclick="resetGalleryFilters()" class="cat-filter-btn" style="margin: 0 auto;">
                <i class="fas fa-redo"></i> Reset Filter
            </button>
        </div>
    </div>
</section>

<!-- LIGHTBOX MODAL -->
<div id="galLightboxModal" class="gal-lightbox-overlay" onclick="closeIntegratedLightbox()">
    <div class="gal-lightbox-box" onclick="event.stopPropagation();">
        <span class="gal-lightbox-close" onclick="closeIntegratedLightbox()">&times;</span>
        <img id="lightboxTargetImg" src="" alt="Dokumentasi Full" class="gal-lightbox-img">
        
        <div class="gal-lightbox-info">
            <div>
                <span id="lightboxUnitBadge" class="unit-type-badge badge-edu" style="position: static; margin-bottom: 8px; display: inline-flex;">
                    <i id="lightboxUnitIcon" class="fas fa-graduation-cap"></i>
                    <span id="lightboxUnitTitle">Nama Unit</span>
                </span>
                <div id="lightboxCaptionText" style="color: #0F3C5C; font-size: 1.1rem; font-weight: 700; margin-top: 4px;">
                    Keterangan Foto
                </div>
            </div>
            <div>
                <a id="lightboxUnitLink" href="#" class="cat-filter-btn" style="background: #00A2E9; color: #fff; border-color: #00A2E9; text-decoration: none;">
                    <i class="fas fa-external-link-alt"></i> Buka Halaman Unit
                </a>
            </div>
        </div>
    </div>
</div>

<script>
let currentCategory = 'all'; // 'all', 'pendidikan', 'psikologi'
let currentUnitSlug = 'all';

function setMainCategory(cat, btn) {
    currentCategory = cat;
    document.querySelectorAll('.cat-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Also reset or adapt unit dropdown
    const unitDropdown = document.getElementById('unitDropdownFilter');
    unitDropdown.value = 'all';
    currentUnitSlug = 'all';

    applyGalleryFilters();
}

function onUnitSelectChange() {
    const val = document.getElementById('unitDropdownFilter').value;
    currentUnitSlug = val;
    applyGalleryFilters();
}

function onSearchChange() {
    applyGalleryFilters();
}

function applyGalleryFilters() {
    const q = document.getElementById('gallerySearchInput').value.trim().toLowerCase();
    const cards = document.querySelectorAll('.gal-integrated-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const itemCat = card.getAttribute('data-unit-cat');
        const itemSlug = card.getAttribute('data-unit-slug');
        const itemText = card.getAttribute('data-text');

        // Check category match
        let matchCategory = (currentCategory === 'all' || itemCat === currentCategory);

        // Check unit slug match
        let matchUnit = (currentUnitSlug === 'all' || itemSlug === currentUnitSlug);

        // Check text search
        let matchText = (q === '' || itemText.includes(q));

        if (matchCategory && matchUnit && matchText) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('visibleCount').textContent = visibleCount;

    const emptyState = document.getElementById('galleryEmptyState');
    if (visibleCount === 0) {
        emptyState.style.display = 'block';
    } else {
        emptyState.style.display = 'none';
    }
}

function resetGalleryFilters() {
    document.getElementById('gallerySearchInput').value = '';
    document.getElementById('unitDropdownFilter').value = 'all';
    currentCategory = 'all';
    currentUnitSlug = 'all';
    document.querySelectorAll('.cat-filter-btn').forEach((b, idx) => {
        if (idx === 0) b.classList.add('active');
        else b.classList.remove('active');
    });
    applyGalleryFilters();
}

// Lightbox
function openIntegratedLightbox(src, caption, unitTitle, unitCategory, unitSlug) {
    document.getElementById('lightboxTargetImg').src = src;
    document.getElementById('lightboxCaptionText').textContent = caption;
    document.getElementById('lightboxUnitTitle').textContent = unitTitle;

    const badge = document.getElementById('lightboxUnitBadge');
    const icon = document.getElementById('lightboxUnitIcon');
    if (unitCategory === 'psikologi') {
        badge.className = 'unit-type-badge badge-psi';
        icon.className = 'fas fa-brain';
    } else {
        badge.className = 'unit-type-badge badge-edu';
        icon.className = 'fas fa-graduation-cap';
    }

    const unitLink = document.getElementById('lightboxUnitLink');
    unitLink.href = '/outsource_talenta_landing/magangtalenta/page.php?slug=' + unitSlug;
    unitLink.title = 'Kunjungi Halaman ' + unitTitle;

    document.getElementById('galLightboxModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeIntegratedLightbox() {
    document.getElementById('galLightboxModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeIntegratedLightbox();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
