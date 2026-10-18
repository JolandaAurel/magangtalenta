<?php
// metode-konsep.php
require_once 'includes/header.php';
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
    
    .metode-content-wrapper {
        max-width: 1000px;
        margin: 50px auto 100px;
        padding: 0 20px;
        color: var(--text-dark);
        font-size: 0.95rem;
        line-height: 1.8;
    }
    
    .metode-title {
        color: #00A2E9; /* Light blue from screenshot */
        font-size: 1.8rem;
        font-weight: 700;
        text-align: center;
        margin-bottom: 25px;
    }

    .metode-intro {
        text-align: center;
        margin-bottom: 50px;
    }

    .metode-intro p {
        margin-bottom: 15px;
    }

    .sentra-section {
        margin-bottom: 60px;
    }

    .sentra-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 15px;
    }

    .sentra-text {
        margin-bottom: 25px;
    }

    .img-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .img-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }

    .img-single {
        display: flex;
        justify-content: center;
    }

    .img-single img {
        max-width: 70%;
    }

    .sentra-img {
        width: 100%;
        height: auto;
        aspect-ratio: 4/3;
        object-fit: cover;
        border-radius: 5px;
        box-shadow: var(--card-shadow);
    }

    @media (max-width: 768px) {
        .img-grid-2, .img-grid-3 {
            grid-template-columns: 1fr;
        }
        .img-single img {
            max-width: 100%;
        }
        .profil-toolbar {
            flex-direction: column;
            gap: 15px;
            text-align: center;
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
            <span><a href="/talenta-semarang/" class="home-link"><i class="fas fa-home"></i> Home</a> / Metode & Konsep Pengajaran</span>
        </div>
        
    </div>
</div>

<div class="metode-content-wrapper">
    <h1 class="metode-title">Metode dan Konsep Pengajaran</h1>
    
    <div class="metode-intro">
        <p>Program kegiatan KB-TK Talenta dirancang berdasarkan pada Kurikulum PAUD dari Diknas dengan pendekatan belajar melalui bermain dan mengembangkan potensi kecerdasan anak.</p>
        <p>Mendidik anak usia dini membutuhkan banyak kegiatan pembelajaran yang kreatif dan inovatif. Salah satunya adalah kegiatan pembelajaran yang berpusat pada anak yang biasa disebut dengan sentra. Pembelajaran sentra merupakan pembelajaran yang difokuskan pada anak (student center) dan guru berperan sebagai pengawas dan fasilitator. KB-TK Talenta menyediakan pembelajaran sentra yang meliputi :</p>
    </div>

    <!-- Sentra 1 -->
    <div class="sentra-section">
        <h2 class="sentra-title">1. Sentra Keimanan dan Ketaqwaan</h2>
        <div class="sentra-text">
            <p>Sentra keimanan dan ketaqwaan merupakan pembelajaran berbagai kegiatan yang berfungsi untuk meningkatkan keimanan dan ketaqwaan peserta didik kepada Tuhan Yang Maha Esa. Dalam sentra ini anak melakukan kegiatan bermain mengenal agama yang dianutnya didampingi oleh guru yang beragama sama dengan anak. KB-TK Talenta merupakan sekolah nasional yang menerima siswa dari semua agama. Sentra ini bertujuan agar peserta didik dapat mengembangkan kemampuan beragama sejak dini dan dapat membentuk pribadi yang cerdas dan berperilaku sesuai norma agama.</p>
            <p>Setiap hari sebelum mulai pembelajaran semua anak mengikuti kegiatan morning devotion sesuai dengan agama anak.</p>
        </div>
        <div class="img-grid-2">
            <img src="/talenta-semarang/assets/images/sentra_1_a.png" alt="Sentra Keimanan 1" class="sentra-img">
            <img src="/talenta-semarang/assets/images/sentra_1_b.png" alt="Sentra Keimanan 2" class="sentra-img">
        </div>
    </div>

    <!-- Sentra 2 -->
    <div class="sentra-section">
        <h2 class="sentra-title">2. Sentra Bahan Alam</h2>
        <div class="sentra-text">
            <p>Sentra bahan alam merupakan berbagai kegiatan pembelajaran yang berfungsi untuk meningkatkan kemampuan sensorimotorik, melakukan percobaan sederhana dan meningkatkan rasa cinta kepada alam.</p>
        </div>
        <div class="img-grid-2">
            <img src="/talenta-semarang/assets/images/sentra_2_a.png" alt="Sentra Bahan Alam 1" class="sentra-img">
            <img src="/talenta-semarang/assets/images/sentra_2_b.png" alt="Sentra Bahan Alam 2" class="sentra-img">
        </div>
    </div>

    <!-- Sentra 3 -->
    <div class="sentra-section">
        <h2 class="sentra-title">3. Sentra Seni</h2>
        <div class="sentra-text">
            <p>Sentra seni bertujuan untuk memberikan kesempatan kepada peserta didik untuk mengembangkan bakat, minat dan keterampilannya. Seperti contoh keterampilan tangan, disini peserta didik diajarkan untuk melipat, menggunting, mengelem, mewarnai, membuat prakarya, dll. Di sentra ini anak meningkatkan kemampuan motorik halus, bermain sambil belajar mengasah rasa keindahan, membangun kemandirian, kerja sama dan tanggung jawab.</p>
        </div>
        <div class="img-single">
            <img src="/talenta-semarang/assets/images/sentra_3.png" alt="Sentra Seni" class="sentra-img">
        </div>
    </div>

    <!-- Sentra 4 -->
    <div class="sentra-section">
        <h2 class="sentra-title">4. Sentra bermain peran</h2>
        <div class="sentra-text">
            <p>Sentra bermain peran sangat mendukung peserta didik pada perkembangan bahasa dan interaksi sosial.</p>
        </div>
        <div class="img-grid-2">
            <img src="/talenta-semarang/assets/images/sentra_4_a.png" alt="Sentra Bermain Peran 1" class="sentra-img">
            <img src="/talenta-semarang/assets/images/sentra_4_b.png" alt="Sentra Bermain Peran 2" class="sentra-img">
        </div>
    </div>

    <!-- Sentra 5 -->
    <div class="sentra-section">
        <h2 class="sentra-title">5. Sentra balok</h2>
        <div class="sentra-text">
            <p>Sentra balok ini membantu perkembangan anak dalam mengembangkan kemampuan visual dan matematika peserta anak usia dini.</p>
        </div>
        <div class="img-single">
            <img src="/talenta-semarang/assets/images/sentra_5.png" alt="Sentra Balok" class="sentra-img">
        </div>
    </div>

    <!-- Sentra 6 -->
    <div class="sentra-section">
        <h2 class="sentra-title">6. Sentra Persiapan</h2>
        <div class="sentra-text">
            <p>Sentra persiapan menstimulasi anak untuk mengembangkan kemampuan baca, tulis dan hitung. Pembelajaran dengan pendekatan bermain keaksaraan yang menyenangkan.</p>
        </div>
        <div class="img-grid-3">
            <img src="/talenta-semarang/assets/images/sentra_6_a.png" alt="Sentra Persiapan 1" class="sentra-img">
            <img src="/talenta-semarang/assets/images/sentra_6_b.png" alt="Sentra Persiapan 2" class="sentra-img">
            <img src="/talenta-semarang/assets/images/sentra_6_c.png" alt="Sentra Persiapan 3" class="sentra-img">
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
