<?php
// galeri.php
require_once 'includes/header.php';

$galleryItems = [
    ["title" => "Bermain di halaman", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Bermain-di-halaman.jpg"],
    ["title" => "Bermain ke danau", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Bermain-ke-danau.jpg"],
    ["title" => "Cooking Session", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Cooking-Session.png"],
    ["title" => "Family Day", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Family-Day.png"],
    ["title" => "Fieldtrip ke Bandara", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Fieldtrip-ke-Bandara.png"],
    ["title" => "Fieldtrip ke Kantor Polisi", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Fieldtrip-ke-Kantor-Polisi.png"],
    ["title" => "Fieldtrip ke Kebun Binatang", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Fieldtrip-ke-Kebun-Binatang.png"],
    ["title" => "Fieldtrip ke Lanud", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Fieldtrip-ke-Lanud.jpg"],
    ["title" => "Fieldtrip ke lawang sewu", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Fieldtrip-ke-lawang-sewu.png"],
    ["title" => "Fieldtrip ke McDonald", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Fieldtrip-ke-McDonald.png"],
    ["title" => "Fieldtrip ke Museum", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Fieldtrip-ke-Museum.png"],
    ["title" => "Fieldtrip ke Pabrik Roti", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Fieldtrip-ke-Pabrik-Roti.png"],
    ["title" => "Fieldtrip ke Pulau Panjang", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Fieldtrip-ke-Pulau-Panjang.png"],
    ["title" => "Fieldtrip ke Sam Poo Kong", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Fieldtrip-ke-Sam-Poo-Kong.png"],
    ["title" => "Fieldtrip ke toko buku", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Fieldtrip-ke-toko-buku.jpg"],
    ["title" => "Gerak dan lagu", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Gerak-dan-lagu.png"],
    ["title" => "Hore", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Hore.jpg"],
    ["title" => "Kegiatan Family Day KB Talenta", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Kegiatan-Family-Day-KB-Talenta.jpg"],
    ["title" => "Lomba balap perahu", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Lomba-balap-perahu.jpg"],
    ["title" => "Lomba memindahkan bola", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Lomba-memindahkan-bola.jpg"],
    ["title" => "Lomba menggambar", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Lomba-menggambar.png"],
    ["title" => "Lomba", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Lomba.jpg"],
    ["title" => "Makan bersama", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Makan-bersama.png"],
    ["title" => "Membangun Balok", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Membangun-Balok.png"],
    ["title" => "Membuat roti", "url" => "https://www.talentasemarang.com/wp-content/uploads/2024/07/Membuat-roti.png"]
];
?>

<style>
    .galeri-banner-container {
        width: 100%;
        margin-top: 80px; /* Offset for fixed header */
        line-height: 0;
    }
    .galeri-banner-img {
        width: 100%;
        height: auto;
        display: block;
    }
    .galeri-toolbar {
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
        .galeri-toolbar {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
    }
</style>

<div class="galeri-banner-container">
    <img src="/talenta-semarang/assets/images/galeri_banner.png" alt="Banner Galeri Talenta" class="galeri-banner-img">
</div>

<div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; margin-bottom: 50px;">
    <div class="galeri-toolbar">
        <div class="toolbar-left">
            <span>Fun and Active Learning</span>
            <span style="color: #cbd5e1;">|</span>
            <span><a href="/talenta-semarang/" class="home-link"><i class="fas fa-home"></i> Home</a> / Galeri</span>
        </div>
        
    </div>
</div>

<section class="gallery-section">
    <div class="gallery-container">
        <?php foreach ($galleryItems as $item): ?>
            <div class="gallery-item" onclick="openLightbox('<?php echo htmlspecialchars($item['url']); ?>', '<?php echo htmlspecialchars($item['title']); ?>')">
                <div class="gallery-img-wrapper">
                    <img src="<?php echo htmlspecialchars($item['url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" loading="lazy">
                </div>
                <div class="gallery-caption">
                    <?php echo htmlspecialchars($item['title']); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="gallery-lightbox" class="lightbox">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <img class="lightbox-content" id="lightbox-img">
    <div id="lightbox-caption" class="lightbox-caption"></div>
</div>

<script>
    function openLightbox(url, title) {
        document.getElementById('gallery-lightbox').style.display = 'flex';
        document.getElementById('lightbox-img').src = url;
        document.getElementById('lightbox-caption').innerText = title;
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        document.getElementById('gallery-lightbox').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    document.getElementById('gallery-lightbox').addEventListener('click', function(e) {
        if(e.target !== document.getElementById('lightbox-img')) {
            closeLightbox();
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>
