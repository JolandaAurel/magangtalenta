<?php
// layanan.php - Halaman Hub Submenu Layanan Talenta Semarang
require_once 'includes/header.php';

// Menentukan kategori aktif dari parameter URL
$kategori = isset($_GET['kategori']) ? strtolower(trim($_GET['kategori'])) : 'pendidikan';

// Konfigurasi dan data masing-masing kategori
$kategori_data = [
    'pendidikan' => [
        'title' => 'Program Pendidikan',
        'badge' => 'PENDIDIKAN FORMAL & NON-FORMAL',
        'icon' => 'fas fa-graduation-cap',
        'desc' => 'Layanan pendidikan komprehensif mulai dari usia dini hingga jenjang menengah atas dengan kurikulum unggul, ramah anak, dan berbasis active and fun learning.',
        'items' => [
            [
                'title' => 'Daycare',
                'slug' => 'daycare',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=daycare',
                'image' => 'daycare_banner.png',
                'icon' => 'fas fa-baby-carriage',
                'desc' => 'Layanan penitipan anak harian dengan pengasuhan penuh kasih, stimulasi tumbuh kembang berkala, dan nutrisi sehat terjaga.'
            ],
            [
                'title' => 'Baby Class',
                'slug' => 'baby-class',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=baby-class',
                'image' => 'baby_class_banner.png',
                'icon' => 'fas fa-baby',
                'desc' => 'Kelas stimulasi sensori, motorik, dan kelekatan emosional (bonding) antara orang tua dan anak usia 6 bulan hingga 2 tahun.'
            ],
            [
                'title' => 'Play Group',
                'slug' => 'play-group',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=play-group',
                'image' => 'play_group_banner.png',
                'icon' => 'fas fa-shapes',
                'desc' => 'Program bermain sambil belajar interaktif untuk menumbuhkan rasa ingin tahu, kemandirian, dan sosialisasi anak usia pra-sekolah.'
            ],
            [
                'title' => 'KB & TK',
                'slug' => 'kb-tk',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=kb-tk',
                'image' => 'tk_banner.png',
                'icon' => 'fas fa-school',
                'desc' => 'Pondasi pembentukan karakter, literasi dasar, numerasi kreatif, dan kemandirian melalui metode Fun and Active Learning.'
            ],
            [
                'title' => 'Sekolah Dasar (SD)',
                'slug' => 'sd',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=sd',
                'image' => 'sd_banner.png',
                'icon' => 'fas fa-book-reader',
                'desc' => 'Pendidikan dasar terakreditasi unggul dengan pendekatan tematik kontekstual untuk mengoptimalkan potensi akademis dan akhlak.'
            ],
            [
                'title' => 'Sekolah Menengah Pertama (SMP)',
                'slug' => 'smp',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=smp',
                'image' => 'smp_banner.png',
                'icon' => 'fas fa-user-graduate',
                'desc' => 'Jenjang pendidikan menengah pertama yang mengasah kepemimpinan, daya kritis, pemecahan masalah, dan bakat minat siswa.'
            ],
            [
                'title' => 'Sekolah Menengah Atas (SMA)',
                'slug' => 'sma',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=sma',
                'image' => 'sma_banner.png',
                'icon' => 'fas fa-graduation-cap',
                'desc' => 'Penyiapan kompetensi akademik tingkat lanjut, karakter mandiri, serta bimbingan karier menuju perguruan tinggi impian.'
            ],
        ]
    ],
    'psikologi' => [
        'title' => 'Layanan Psikologi',
        'badge' => 'LEMBAGA PSIKOLOGI TERAPAN (LPT)',
        'icon' => 'fas fa-brain',
        'desc' => 'Pusat layanan psikologi terpadu didukung psikolog dan terapis berpengalaman untuk mendampingi tumbuh kembang anak, remaja, dan keluarga.',
        'items' => [
            [
                'title' => 'Pusat Psikologi Terapan',
                'slug' => 'pusat-psikologi-terapan',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=pusat-psikologi-terapan',
                'image' => 'pusat_psikologi_banner.png',
                'icon' => 'fas fa-brain',
                'desc' => 'Layanan asesmen, pendampingan psikologis, dan solusi komprehensif bagi kebutuhan tumbuh kembang anak maupun lembaga.'
            ],
            [
                'title' => 'Terapi Anak Berkebutuhan Khusus',
                'slug' => 'terapi-abk',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=terapi-abk',
                'image' => 'terapi_abk_banner.png',
                'icon' => 'fas fa-hands-helping',
                'desc' => 'Program terapi terpadu (wicara, sensori, okupasi, perilaku) untuk mendukung kemandirian dan potensi optimal anak istimewa.'
            ],
            [
                'title' => 'Psikotes & Konsultasi Tumbuh Kembang',
                'slug' => 'psikotes-konsultasi',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=psikotes-konsultasi',
                'image' => 'psikotes_konsultasi_banner.png',
                'icon' => 'fas fa-stethoscope',
                'desc' => 'Pemeriksaan psikologis berkala dan sesi konsultasi mendalam bersama psikolog untuk memetakan perkembangan buah hati.'
            ],
            [
                'title' => 'Assessment',
                'slug' => 'assessment',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=assessment',
                'image' => 'assessment_banner.png',
                'icon' => 'fas fa-clipboard-check',
                'desc' => 'Evaluasi psikologis komprehensif untuk mendeteksi dini kebutuhan perkembangan, profil kecerdasan, dan gaya belajar anak.'
            ],
            [
                'title' => 'Konsultasi',
                'slug' => 'konsultasi',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=konsultasi',
                'image' => 'konsultasi_banner.png',
                'icon' => 'fas fa-comments',
                'desc' => 'Ruang konsultasi privat dan empatik bersama ahli psikologi untuk berbagai tantangan emosi, perilaku, dan relasi keluarga.'
            ],
            [
                'title' => 'Terapi',
                'slug' => 'terapi',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=terapi',
                'image' => 'pusat_terapi_banner.png',
                'icon' => 'fas fa-heartbeat',
                'desc' => 'Layanan intervensi terapi khusus dengan ruangan representatif dan fasilitas stimulasi sensori integrasi yang lengkap.'
            ],
            [
                'title' => 'Tes Psikologi',
                'slug' => 'tes-psikologi',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=tes-psikologi',
                'image' => 'tes_psikologi_banner.png',
                'icon' => 'fas fa-file-signature',
                'desc' => 'Penyelenggaraan psikotes minat bakat, IQ, kesiapan masuk sekolah, dan evaluasi kepribadian untuk perorangan dan sekolah.'
            ],
            [
                'title' => 'Parenting',
                'slug' => 'parenting',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=parenting',
                'image' => 'profil_banner.png',
                'icon' => 'fas fa-users',
                'desc' => 'Bimbingan dan sharing pola asuh positif agar orang tua lebih percaya diri dan selaras dalam mendidik buah hati.'
            ],
            [
                'title' => 'Seminar & Workshop',
                'slug' => 'seminar',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=seminar',
                'image' => 'seminar_banner.png',
                'icon' => 'fas fa-chalkboard-teacher',
                'desc' => 'Pelatihan dan seminar edukatif bertema parenting, psikologi anak, serta inklusi untuk guru, orang tua, dan masyarakat.'
            ],
            [
                'title' => 'Outbound & Karakter',
                'slug' => 'outbound',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=outbound',
                'image' => 'outbound_banner.png',
                'icon' => 'fas fa-campground',
                'desc' => 'Aktivitas luar ruangan edukatif dan menyenangkan yang dirancang khusus untuk melatih kepemimpinan dan kekompakan tim.'
            ],
        ]
    ],
    'inklusi' => [
        'title' => 'Sekolah Inklusi',
        'badge' => 'PENDIDIKAN RAMAH ANAK & INKLUSIF',
        'icon' => 'fas fa-hands-helping',
        'desc' => 'Program pendidikan yang merangkul keberagaman dengan kurikulum adaptif, guru pendamping khusus (GPK), dan lingkungan belajar yang aman dan menghargai setiap potensi anak.',
        'items' => [
            [
                'title' => 'SD (Inklusi)',
                'slug' => 'sd-inklusi',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=sd-inklusi',
                'image' => 'sd_inklusi_banner.png',
                'icon' => 'fas fa-school',
                'desc' => 'Pendidikan dasar inklusif yang memadukan kurikulum nasional dengan Program Pembelajaran Individual (PPI) sesuai kebutuhan unik anak.'
            ],
            [
                'title' => 'SMP (Inklusi)',
                'slug' => 'smp-inklusi',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=smp-inklusi',
                'image' => 'smp_inklusi_banner.png',
                'icon' => 'fas fa-user-friends',
                'desc' => 'Jenjang SMP inklusi yang membina kemandirian sosial, kecakapan hidup, dan adaptasi akademik dengan bimbingan berkelanjutan.'
            ],
            [
                'title' => 'SMA (Inklusi)',
                'slug' => 'sma-inklusi',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=sma-inklusi',
                'image' => 'sma_inklusi_banner.png',
                'icon' => 'fas fa-graduation-cap',
                'desc' => 'Pendidikan menengah atas inklusif dengan penekanan pada keterampilan vokasi, kemandirian sosial, dan persiapan hidup bermasyarakat.'
            ],
            [
                'title' => 'Terapi Anak Berkebutuhan Khusus',
                'slug' => 'terapi-abk',
                'url' => '/outsource_talenta_landing/magangtalenta/page.php?slug=terapi-abk',
                'image' => 'terapi_abk_banner.png',
                'icon' => 'fas fa-hands-helping',
                'desc' => 'Layanan terapi terintegrasi langsung dengan aktivitas belajar sekolah untuk mengoptimalkan kemampuan interaksi dan akademik siswa.'
            ],
        ]
    ],
    'publikasi' => [
        'title' => 'Publikasi & Dokumentasi',
        'badge' => 'INFORMASI & ARSIP TALENTA',
        'icon' => 'fas fa-newspaper',
        'desc' => 'Arsip dokumentasi kegiatan, galeri foto aktivitas belajar siswa, berita agenda terkini, serta artikel wawasan edukasi dan pola asuh anak.',
        'items' => [
            [
                'title' => 'Galeri Foto & Dokumentasi',
                'slug' => 'galeri',
                'url' => '/outsource_talenta_landing/magangtalenta/galeri.php',
                'image' => 'galeri_banner.png',
                'icon' => 'fas fa-images',
                'desc' => 'Kumpulan dokumentasi visual berbagai aktivitas belajar seru, karya kreatif siswa, acara kebersamaan, dan fasilitas Talenta Semarang.'
            ],
            [
                'title' => 'Berita & Kegiatan',
                'slug' => 'berita-kegiatan',
                'url' => '/outsource_talenta_landing/magangtalenta/berita-kegiatan.php',
                'image' => 'berita_banner.png',
                'icon' => 'fas fa-calendar-alt',
                'desc' => 'Informasi terupdate mengenai acara periodik sekolah, field trip, perayaan tema, lomba, serta berbagai agenda kegiatan terbaru.'
            ],
            [
                'title' => 'Artikel Edukasi & Pengasuhan',
                'slug' => 'artikel',
                'url' => '/outsource_talenta_landing/magangtalenta/artikel.php',
                'image' => 'artikel_banner.png',
                'icon' => 'fas fa-newspaper',
                'desc' => 'Artikel dan ulasan bermanfaat seputar psikologi perkembangan anak, tips pola asuh orang tua (parenting), dan metode belajar aktif.'
            ],
        ]
    ]
];

// Fallback jika kategori tidak ditemukan
if (!array_key_exists($kategori, $kategori_data)) {
    $kategori = 'pendidikan';
}

$current_cat = $kategori_data[$kategori];
?>

<style>
/* ================= HUB LAYANAN STYLES ================= */
.hub-wrapper {
    margin-top: 80px;
    background: #f8fafc;
    min-height: 80vh;
    padding-bottom: 90px;
}

/* Header Banner Section */
.hub-hero {
    background: linear-gradient(135deg, #0F3C5C 0%, #0070ad 50%, #00A2E9 100%);
    color: #ffffff;
    padding: 55px 0 65px;
    position: relative;
    overflow: hidden;
}

.hub-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.hub-hero-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 30px;
    text-align: center;
    position: relative;
    z-index: 2;
}

.hub-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(5px);
    color: #ffffff;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 2.5px;
    padding: 6px 20px;
    border-radius: 50px;
    margin-bottom: 16px;
    border: 1px solid rgba(255,255,255,0.25);
}

.hub-hero h1 {
    font-size: 2.8rem;
    font-weight: 900;
    margin-bottom: 15px;
    letter-spacing: -0.5px;
    line-height: 1.2;
}

.hub-hero p {
    font-size: 1.12rem;
    color: rgba(255, 255, 255, 0.92);
    max-width: 780px;
    margin: 0 auto 30px;
    line-height: 1.7;
}

/* Category Switch Tabs */
.hub-tabs {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 15px;
}

.hub-tab-btn {
    padding: 10px 22px;
    border-radius: 50px;
    font-size: 0.95rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.hub-tab-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    color: #ffffff;
    transform: translateY(-2px);
}

.hub-tab-btn.active {
    background: #ffffff;
    color: #0F3C5C;
    border-color: #ffffff;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
}

.hub-tab-btn.active i {
    color: #00A2E9;
}

/* Cards Grid Section */
.hub-content-section {
    max-width: 1400px;
    margin: -30px auto 0;
    padding: 0 30px;
    position: relative;
    z-index: 3;
}

.sub-services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: 28px;
}

/* Individual Service Card */
.sub-service-card {
    background: #ffffff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(15, 60, 92, 0.07);
    border: 1px solid #eef2f6;
    transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
    position: relative;
}

.sub-service-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 18px 40px rgba(0, 162, 233, 0.16);
    border-color: rgba(0, 162, 233, 0.3);
}

/* Card Thumbnail Wrapper */
.sub-card-thumb {
    width: 100%;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    position: relative;
    background: #e2e8f0;
}

.sub-card-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
    display: block;
}

.sub-service-card:hover .sub-card-thumb img {
    transform: scale(1.06);
}

.sub-card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(15,60,92,0.4) 100%);
}

.sub-card-icon-badge {
    position: absolute;
    bottom: -22px;
    right: 22px;
    width: 50px;
    height: 50px;
    border-radius: 14px;
    background: #ffffff;
    color: #00A2E9;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    transition: all 0.3s ease;
    z-index: 2;
}

.sub-service-card:hover .sub-card-icon-badge {
    background: #00A2E9;
    color: #ffffff;
    transform: scale(1.08) rotate(5deg);
}

/* Card Body */
.sub-card-body {
    padding: 30px 24px 24px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.sub-card-body h3 {
    font-size: 1.28rem;
    font-weight: 800;
    color: #0F3C5C;
    margin-bottom: 10px;
    line-height: 1.35;
    transition: color 0.2s ease;
}

.sub-service-card:hover .sub-card-body h3 {
    color: #00A2E9;
}

.sub-card-body p {
    font-size: 0.92rem;
    color: #64748b;
    line-height: 1.65;
    margin-bottom: 22px;
    flex: 1;
}

/* Card Footer / Action */
.sub-card-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #00A2E9;
    font-weight: 700;
    font-size: 0.95rem;
    transition: gap 0.25s ease;
}

.sub-service-card:hover .sub-card-action {
    gap: 12px;
    color: #0F3C5C;
}

.sub-card-action i {
    font-size: 0.85em;
    transition: transform 0.25s ease;
}

.sub-service-card:hover .sub-card-action i {
    transform: translateX(3px);
}

/* Empty fallback state */
.hub-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.05);
}

/* ================= RESPONSIVE ================= */
@media (max-width: 992px) {
    .hub-hero h1 {
        font-size: 2.3rem;
    }
    .hub-hero p {
        font-size: 1rem;
    }
    .sub-services-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .hub-wrapper {
        margin-top: 65px;
    }
    .hub-hero {
        padding: 40px 0 50px;
    }
    .hub-hero h1 {
        font-size: 1.95rem;
    }
    .hub-hero p {
        font-size: 0.95rem;
        margin-bottom: 22px;
    }
    .hub-tabs {
        gap: 8px;
    }
    .hub-tab-btn {
        padding: 8px 16px;
        font-size: 0.85rem;
    }
    .hub-content-section {
        margin-top: -20px;
        padding: 0 16px;
    }
    .sub-services-grid {
        grid-template-columns: 1fr;
        gap: 18px;
    }
    .sub-card-body {
        padding: 24px 20px 20px;
    }
    .sub-card-body h3 {
        font-size: 1.18rem;
    }
    .sub-card-body p {
        font-size: 0.88rem;
    }
}
</style>

<div class="hub-wrapper">

    <!-- Category Header Hero -->
    <div class="hub-hero">
        <div class="hub-hero-container">
            <div class="hub-badge">
                <i class="<?php echo htmlspecialchars($current_cat['icon']); ?>"></i>
                <?php echo htmlspecialchars($current_cat['badge']); ?>
            </div>
            <h1><?php echo htmlspecialchars($current_cat['title']); ?></h1>
            <p><?php echo htmlspecialchars($current_cat['desc']); ?></p>

            <!-- Category Switcher Tabs -->
            <div class="hub-tabs">
                <a href="/outsource_talenta_landing/magangtalenta/layanan.php?kategori=pendidikan" class="hub-tab-btn <?php echo $kategori === 'pendidikan' ? 'active' : ''; ?>">
                    <i class="fas fa-graduation-cap"></i> Pendidikan
                </a>
                <a href="/outsource_talenta_landing/magangtalenta/layanan.php?kategori=psikologi" class="hub-tab-btn <?php echo $kategori === 'psikologi' ? 'active' : ''; ?>">
                    <i class="fas fa-brain"></i> Layanan Psikologi
                </a>
                <a href="/outsource_talenta_landing/magangtalenta/layanan.php?kategori=inklusi" class="hub-tab-btn <?php echo $kategori === 'inklusi' ? 'active' : ''; ?>">
                    <i class="fas fa-hands-helping"></i> Sekolah Inklusi
                </a>
                <a href="/outsource_talenta_landing/magangtalenta/layanan.php?kategori=publikasi" class="hub-tab-btn <?php echo $kategori === 'publikasi' ? 'active' : ''; ?>">
                    <i class="fas fa-newspaper"></i> Publikasi
                </a>
            </div>
        </div>
    </div>

    <!-- Sub-Services Cards Grid -->
    <div class="hub-content-section">
        <div class="sub-services-grid">
            <?php foreach ($current_cat['items'] as $item): ?>
            <?php 
                $img_path = '/outsource_talenta_landing/magangtalenta/assets/images/' . $item['image'];
                $has_file = file_exists(__DIR__ . '/assets/images/' . $item['image']);
                if (!$has_file) {
                    $img_path = '/outsource_talenta_landing/magangtalenta/assets/images/header-bg.png';
                }
            ?>
            <a href="<?php echo htmlspecialchars($item['url']); ?>" class="sub-service-card">
                <div class="sub-card-thumb">
                    <img src="<?php echo htmlspecialchars($img_path); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    <div class="sub-card-overlay"></div>
                    <div class="sub-card-icon-badge">
                        <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                    </div>
                </div>
                <div class="sub-card-body">
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p><?php echo htmlspecialchars($item['desc']); ?></p>
                    <div class="sub-card-action">
                        <span>Lihat Layanan</span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
