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
<section class="hero-premium" style="margin-top: 90px; padding: 0; min-height: auto; position: relative; overflow: hidden; background: #DAF1FE;">
    <!-- Background Image (Cleaned) -->
    <div style="position: absolute; width: 100%; height: 100%; left: 0; top: 0; z-index: 1;">
        <img src="/talenta-semarang/assets/images/hero_banner_final_edited.png?v=1791337561" alt="Background" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
    </div>

    <div class="hero-premium-container" style="position: relative; z-index: 2; padding: 100px 0 280px; width: 100%; max-width: 1800px; padding: 100px 5% 280px; margin: 0 auto; display: grid; grid-template-columns: 1.3fr 0.7fr; gap: 20px; align-items: center;">
        <div class="hero-premium-content" style="padding-left: 20px;">
            <div class="hero-tags" style="color: #00A2E9; font-weight: 800; font-size: 1.2rem; letter-spacing: 3px; margin-bottom: 15px;">
                TALENTA SEMARANG
            </div>
            <h1 style="font-size: 4.5rem; color: #0F3C5C; line-height: 1.15; margin-bottom: 20px; font-weight: 900; letter-spacing: -1px;"><span style="white-space: nowrap;">Every Potential</span><br>Matters.</h1>
            <p style="font-size: 1.25rem; color: #475569; margin-bottom: 35px; line-height: 1.7; max-width: 90%;">Kami hadir untuk mendampingi setiap anak agar tumbuh, berkembang, dan meraih masa depan yang lebih cerah.</p>
            <div class="hero-buttons" style="display: flex; gap: 20px;">
                <a href="/talenta-semarang/page.php?slug=fasilitas" class="btn-primary pill" style="box-shadow: 0 4px 6px rgba(0,162,233,0.3); padding: 15px 30px; font-size: 1.1rem; font-weight: 700; border-radius: 50px;">Lihat Fasilitas <i class="fas fa-arrow-right" style="margin-left: 5px;"></i></a>
                <a href="#program" class="btn-outline pill" style="background: #ffffff; color: #0F3C5C; border-color: #0F3C5C; padding: 15px 30px; font-size: 1.1rem; font-weight: 700; border-radius: 50px;">Lihat Program <i class="fas fa-arrow-right" style="margin-left: 5px;"></i></a>
            </div>
        </div>
        <div class="hero-premium-image">
            <!-- Transparent placeholder to keep layout structure intact for the grid -->
        </div>
    </div>
</section>

<!-- PROGRAMS / SERVICES SECTION -->
<section id="program" class="programs-premium">
    <div class="programs-grid">
        <?php foreach ($programs as $program): ?>
        <div class="program-card" style="cursor:pointer;" onclick="<?php echo strpos($program->link_url, '#modal-') === 0 ? 'openModal(\'' . substr($program->link_url, 1) . '\');' : 'window.location.href=\'' . ('/talenta-semarang/' . $program->link_url) . '\';'; ?>">
            <div class="program-icon">
                <i class="<?php echo htmlspecialchars($program->icon_class); ?>"></i>
            </div>
            <h3><?php echo htmlspecialchars($program->title); ?></h3>
            <p><?php echo htmlspecialchars($program->description); ?></p>
            <a href="<?php echo strpos($program->link_url, '#') === 0 ? $program->link_url : '/talenta-semarang/' . $program->link_url; ?>" class="program-link" onclick="<?php echo strpos($program->link_url, '#modal-') === 0 ? 'openModal(\'' . substr($program->link_url, 1) . '\'); return false;' : ''; ?>">
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- KENAPA MEMILIH KAMI SECTION -->
<section class="why-us">
    <div class="why-us-header">
        <h2 class="why-us-title">Kenapa Memilih Kami?</h2>
        <p class="why-us-subtitle">Beberapa Keunggulan Kami :</p>
    </div>

    <!-- Row 1 -->
    <div class="why-us-stripe">
        <div class="why-us-container">
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-medal"></i></div>
                <div class="why-content">
                    <h4>Terakreditasi A ( Unggul )</h4>
                    <p>Kami memastikan standar pendidikan berkualitas tinggi, dan metode pengajaran yang luar biasa.</p>
                </div>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-school"></i></div>
                <div class="why-content">
                    <h4>Sekolah Penggerak</h4>
                    <p>Kami mendorong siswa untuk menjadi pembelajar dan pemimpin yang proaktif, dibekali dengan keterampilan.</p>
                </div>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-hands-holding-child"></i></div>
                <div class="why-content">
                    <h4>Sekolah Ramah Anak</h4>
                    <p>Kami menciptakan lingkungan yang mengasuh dan inklusif di mana setiap anak merasa aman, dihargai, dan didukung.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2 -->
    <div class="why-us-stripe">
        <div class="why-us-container">
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-lightbulb"></i></div>
                <div class="why-content">
                    <h4>Komprehensif</h4>
                    <p>Sekolah Talenta Semarang menyediakan program pendidikan KB, TK, kesetaraan SD, SMP dan SMA.</p>
                </div>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-thumbs-up"></i></div>
                <div class="why-content">
                    <h4>Pengajar Berkompeten</h4>
                    <p>Tenaga pengajar kami berkualifikasi dan berpengalaman dalam bidangnya.</p>
                </div>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-smile"></i></div>
                <div class="why-content">
                    <h4>Pendekatan Inovatif</h4>
                    <p>Kami menggunakan pendekatan pembelajaran inovatif dan sesuai dengan perkembangan zaman.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3 -->
    <div class="why-us-stripe">
        <div class="why-us-container row-start">
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-desktop"></i></div>
                <div class="why-content">
                    <h4>Fasilitas</h4>
                    <p>Kami menyediakan fasilitas & lingkungan belajar yang kondusif & nyaman bagi peserta didik.</p>
                </div>
            </div>
            <div class="why-card">
                <div class="why-icon"><i class="fas fa-map-marked-alt"></i></div>
                <div class="why-content">
                    <h4>Lokasi Strategis</h4>
                    <p>Lembaga kami mudah diakses dan memiliki aksesibilitas yang baik bagi masyarakat sekitar.</p>
                </div>
            </div>
        </div>
    </div>
</section>




<!-- MODALS UNTUK MENU -->
<style>
.custom-modal-overlay {
    display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;
    opacity: 0; transition: opacity 0.3s ease;
}
.custom-modal-overlay.active {
    display: flex; opacity: 1;
}
.custom-modal-box {
    background: white; padding: 30px; border-radius: 15px; width: 90%; max-width: 500px;
    transform: translateY(20px); transition: transform 0.3s ease;
    box-shadow: 0 15px 50px rgba(0,0,0,0.2);
    position: relative;
}
.custom-modal-overlay.active .custom-modal-box {
    transform: translateY(0);
}
.custom-modal-close {
    position: absolute; top: 15px; right: 20px; font-size: 1.5rem; cursor: pointer; color: #64748b;
}
.custom-modal-close:hover { color: red; }
.custom-modal-title {
    color: #0F3C5C; font-size: 1.5rem; margin-bottom: 20px; font-weight: 700; border-bottom: 2px solid #eee; padding-bottom: 10px;
}
.modal-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
}
.modal-btn {
    display: block; padding: 12px 15px; background: #f8fafc; color: #0F3C5C;
    text-align: center; border-radius: 8px; text-decoration: none; font-weight: 600;
    border: 1px solid #e2e8f0; transition: all 0.2s ease;
}
.modal-btn:hover {
    background: #00A2E9; color: white; border-color: #00A2E9; transform: translateY(-2px);
}
</style>

<div id="modal-pendidikan" class="custom-modal-overlay" onclick="closeModal(event, 'modal-pendidikan')">
    <div class="custom-modal-box">
        <i class="fas fa-times custom-modal-close" onclick="closeModal(null, 'modal-pendidikan', true)"></i>
        <div class="custom-modal-title">Pilih Program Pendidikan</div>
          <div class="modal-grid">
              <?php foreach($modal_pendidikan as $item): ?>
                  <a href="/talenta-semarang/page.php?slug=<?php echo htmlspecialchars($item->slug); ?>" class="modal-btn"><?php echo htmlspecialchars($item->title); ?></a>
              <?php endforeach; ?>
          </div>
    </div>
</div>

<div id="modal-inklusi" class="custom-modal-overlay" onclick="closeModal(event, 'modal-inklusi')">
    <div class="custom-modal-box">
        <i class="fas fa-times custom-modal-close" onclick="closeModal(null, 'modal-inklusi', true)"></i>
        <div class="custom-modal-title">Pilih Sekolah Inklusi</div>
        <div class="modal-grid">
            <?php foreach($modal_inklusi as $item): ?>
                <a href="/talenta-semarang/page.php?slug=<?php echo htmlspecialchars($item->slug); ?>" class="modal-btn"><?php echo htmlspecialchars($item->title); ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div id="modal-psikologi" class="custom-modal-overlay" onclick="closeModal(event, 'modal-psikologi')">
    <div class="custom-modal-box">
        <i class="fas fa-times custom-modal-close" onclick="closeModal(null, 'modal-psikologi', true)"></i>
        <div class="custom-modal-title">Pilih Layanan Psikologi</div>
          <div class="modal-grid">
              <?php foreach($modal_psikologi as $item): ?>
                  <a href="/talenta-semarang/page.php?slug=<?php echo htmlspecialchars($item->slug); ?>" class="modal-btn"><?php echo htmlspecialchars($item->title); ?></a>
              <?php endforeach; ?>
          </div>
    </div>
</div>

<div id="modal-publikasi" class="custom-modal-overlay" onclick="closeModal(event, 'modal-publikasi')">
    <div class="custom-modal-box">
        <i class="fas fa-times custom-modal-close" onclick="closeModal(null, 'modal-publikasi', true)"></i>
        <div class="custom-modal-title">Pilih Publikasi</div>
        <div class="modal-grid">
            <a href="/talenta-semarang/galeri.php" class="modal-btn">Galeri</a>
            <a href="/talenta-semarang/berita-kegiatan.php" class="modal-btn">Berita & Kegiatan</a>
            <a href="/talenta-semarang/artikel.php" class="modal-btn" style="grid-column: span 2;">Artikel</a>
        </div>
    </div>
</div>

<script>
function openModal(id) {
    let el = document.getElementById(id);
    if(el) el.classList.add('active');
}
function closeModal(e, id, force = false) {
    if (force || e.target.id === id) {
        document.getElementById(id).classList.remove('active');
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
