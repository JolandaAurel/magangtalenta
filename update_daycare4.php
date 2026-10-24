<?php
$host = '127.0.0.1';
$db   = 'talenta_db';
$user = 'root';
$pass = '';

$html = '
<style>
    .daycare-wrapper { display: flex; flex-wrap: wrap; gap: 40px; margin-bottom: 60px; }
    .daycare-images { flex: 1; min-width: 300px; display: flex; flex-direction: column; gap: 20px; }
    .daycare-images img { width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); object-fit: cover; }
    .daycare-content { flex: 1.5; min-width: 300px; }
    h2.blue-title { color: #00A2E9; font-size: 1.8rem; font-weight: 700; margin-bottom: 20px; margin-top: 0; }
    .daycare-text p { margin-bottom: 15px; text-align: justify; line-height: 1.6; }
    .custom-list { list-style: none; padding-left: 0; }
    .custom-list li { margin-bottom: 15px; position: relative; padding-left: 20px; line-height: 1.6; }
    .custom-list li::before { content: counters(list-item, ".") ". "; counter-increment: list-item; position: absolute; left: 0; color: var(--text-dark); font-weight: bold;}
    .cards-section { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 60px; }
    .info-card { background: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; text-align: left; }
    .info-card img { width: 100%; height: 180px; object-fit: cover; }
    .info-card-body { padding: 20px; }
    .info-card-body h3 { font-size: 1.2rem; margin: 0 0 10px; color: var(--text-dark); font-weight: bold; }
    .info-card-body p { margin: 0; font-size: 0.85rem; color: var(--text-light); }
    .facilities { margin: 40px 0; }
    .btn-green { display: inline-block; background: #65a30d; color: #fff; padding: 10px 25px; border-radius: 5px; text-decoration: none; font-weight: 600; margin-top: 15px; font-size: 0.9rem;}
    .btn-green:hover { background: #4d7c0f; }
    @media (max-width: 900px) { .cards-section { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px) { .cards-section { grid-template-columns: 1fr; } .daycare-wrapper { flex-direction: column; } }
</style>

<div class="daycare-wrapper">
    <div class="daycare-images">
        <img src="/outsource_talenta_landing/magangtalenta/assets/images/daycare_img1.png" alt="Anak Bermain Balok" style="aspect-ratio: 4/3;">
        <img src="/outsource_talenta_landing/magangtalenta/assets/images/daycare_img2.png" alt="Anak Bermain Bersama Guru" style="aspect-ratio: 4/3;">
    </div>
    
    <div class="daycare-content daycare-text">
        <h2 class="blue-title">Daycare</h2>
        <p>Daycare Talenta dengan konsep Daycare and Educares, tempat pengasuhan dan pendidikan anak yang menjadikan rumah kedua bagi anak dan sahabat orang tua dalam mendampingi anak ditahun-tahun keemasan.</p>
        <p>Daycare Talenta menjadi solusi terpercaya bagi orang tua bekerja tanpa mengkhawatirkan aktivitas dan program pendidikan anak. Anak mendapatkan stimulasi yang tepat melalui kegiatan bermain bersama guru-guru yang berkualitas dan kompeten melalui pembinaan Ibu Elizabeth Indira sebagai pembina.</p>
        <p>Daycare Talenta menerima murid usia 2 sampai 7 tahun dengan biaya relatif terjangkau dengan aktifitas setiap hari senin - Jum\'at mulai Pk. 07.00 sd 16.00 WIB dan hari Sabtu mulai Pk 07.00 sd 12.00 WIB.</p>
        
        <h2 class="blue-title" style="margin-top: 30px;">Sasaran Daycare Talenta</h2>
        <ol class="custom-list" style="counter-reset: list-item;">
            <li>Memberikan ruang gerak psikologis yang nyaman bagi anak-anak sehingga mereka dapat bertumbuh kembang dengan optimal.</li>
            <li>Menemukan potensial anak sejak dini.</li>
            <li>Mencetak anak mampu bersosialisasi, bermoral, dan mencintai lingkungan dimana dia berada.</li>
        </ol>
    </div>
</div>

<div class="cards-section">
    <div class="info-card">
        <img src="/outsource_talenta_landing/magangtalenta/assets/images/card_babyclass.png" alt="Baby Class">
        <div class="info-card-body">
            <h3>Baby Class</h3>
            <p>Untuk Usia 0 - 2 th</p>
        </div>
    </div>
    <div class="info-card">
        <img src="/outsource_talenta_landing/magangtalenta/assets/images/card_playgroup.png" alt="Play Group">
        <div class="info-card-body">
            <h3>Play Group</h3>
            <p>Untuk Usia 2 - 4 th</p>
        </div>
    </div>
    <div class="info-card">
        <img src="/outsource_talenta_landing/magangtalenta/assets/images/card_tka.png" alt="TK A">
        <div class="info-card-body">
            <h3>TK A</h3>
            <p>Untuk Usia 4 - 5 th</p>
        </div>
    </div>
    <div class="info-card">
        <img src="/outsource_talenta_landing/magangtalenta/assets/images/card_tkb.png" alt="TK B">
        <div class="info-card-body">
            <h3>TK B</h3>
            <p>Untuk Usia 5 - 6 th</p>
        </div>
    </div>
</div>

<div class="daycare-text" style="margin-bottom: 50px;">
    <h2 class="blue-title">Mengapa harus di Daycare Talenta ?</h2>
    <p>Daycare Talenta hadir untuk membantu ayah dan bunda dalam mengembangkan potensi ananda tersayang sesuai dengan tahap usianya, mengambil peran sebagai partner orangtua untuk mengembangkan generasi yang cerdas dimulai dari sekarang.</p>
    
    <ol class="custom-list" style="counter-reset: list-item; margin-top: 20px;">
        <li>Pendamping yang profesional dan kompeten dibidang perkembangan dan pendidikan anak usia dini, dengan perbandingan pengasuhan 3:1 artinya 3 anak diawasi oleh satu pendamping. Dengan komposisi ini diharapkan perawatan dan pengawasan pada masing-masing anak dapat optimal.</li>
        <li>Perkembangan anak dapat dipantau oleh orangtua melalui buku laporan harian dan dialog perkembangan yang diadakan setiap 3 bulan sekali. Orangtua dan pendidik bekerjasama dalam menentukan pendidikan yang terbaik bagi anak.</li>
        <li>Tinggal di rumah bersama pembantu, anak hanya bermain dengan orang dewasa, atau bahkan hanya menghabiskan waktu menonton TV. Sedangkan di Talenta daycare anak akan belajar bersosialisasi dengan teman-temannya dengan beragam kegiatan yang menarik dan menyenangkan.</li>
        <li>Biaya Daycare Talenta yang relatif terjangkau.</li>
        <li>Selain bermain, anak juga diperkenalkan pengetahuan dasar sebagai persiapan untuk masuk kependidikan selanjutnya.</li>
    </ol>
</div>

<div class="facilities daycare-text">
    <h2 class="blue-title">Fasilitas</h2>
    <p>Tempat yang strategis, aman dan nyaman dilengkapi dengan sarana dan prasarana yang memadai, alat permainan edukatif, permainan indoor dan outdoor dan Ruang tidur ber-AC, Area berkegiatan yang aman dan nyaman selama berada di sekolah, Menyediakan perpustakaan dilengkapi dengan buku-buku pengetahuan, mainan dan buku hiburan yang berkualitas.</p>
    <p>Buku Laporan Harian (Daily Report Activity), pembekalan parenting setiap 6 bulan, Pemeriksaan kesehatan dan pemantauan tinggi/berat badan setiap bulan. Konsultasi Psikolog jika dibutuhkan.</p>
    <a href="#" class="btn-green" style="margin-top: 15px;">↗ Cek Fasilitas</a>
</div>

<div class="daycare-text" style="margin-top: 50px;">
    <h2 class="blue-title">Perencanaan Program Pembelajaran pada Taman Penitipan Anak Talenta</h2>
    <ol class="custom-list" style="counter-reset: list-item;">
        <li>
            <strong>Full day care</strong><br>
            Anak dititipkan sehari penuh dari pukul 07.00 - 16.00.
        </li>
        <li>
            <strong>Semi day care</strong><br>
            Anak dititipkan hanya setengah hari dari pukul 07.00 - 12.00 atau 12.00 - 16.00
        </li>
        <li>
            <strong>Incidental day care</strong><br>
            Anak dititipkan sewaktu-waktu sesuai kebutuhan orang tua.
        </li>
    </ol>
</div>
';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $slugs = [
        'baby-class',
        'daycare',
        'pra-play-group',
        'play-group',
        'tk',
        'pkbm',
        'terapi-abk',
        'psikotes-konsultasi'
    ];
    
    $stmtInsert = $pdo->prepare("INSERT INTO pages (slug, title, content_html) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE content_html = ?, title = ?");
    
    foreach ($slugs as $slug) {
        $title = ucwords(str_replace('-', ' ', $slug));
        if ($slug == 'tk') $title = 'TK';
        if ($slug == 'pkbm') $title = 'PKBM (SD-SMA)';
        if ($slug == 'terapi-abk') $title = 'Terapi Anak Berkebutuhan Khusus';
        if ($slug == 'psikotes-konsultasi') $title = 'Psikotes dan Konsultasi Tumbuh Kembang Anak';
        
        $stmtInsert->execute([$slug, $title, $html, $html, $title]);
    }
    
    echo "Pages updated successfully!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
