<?php
// includes/footer.php
?>
<style>
    .footer-premium {
        position: relative;
        background: url('/talenta-semarang/assets/images/footer_bg_clean2.png') no-repeat center bottom;
        background-size: cover;
        padding-top: 60px; /* Reduced from 130px to pull text up */
        color: #0f3c5c;
        font-family: 'Montserrat', sans-serif;
    }
    .footer-content {
        max-width: 1450px; /* Increased from 1250px */
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1.1fr 1.2fr 1.2fr 1.2fr;
        gap: 40px; /* Increased gap */
        padding: 0 40px 120px; /* Increased bottom padding to keep text away from the wave */
    }
    .footer-col-1 {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .footer-col-1 img {
        width: 180px; /* Increased from 130px */
        margin-bottom: 8px;
        mix-blend-mode: multiply;
    }
    .footer-motto {
        font-size: 1.05rem; /* Increased */
        font-weight: 700;
        color: #0070ad;
        margin-bottom: 25px;
    }
    .footer-socials {
        display: flex;
        gap: 15px;
    }
    .footer-socials a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px; /* Increased */
        height: 42px;
        background: #0070ad;
        color: #fff;
        border-radius: 50%;
        font-size: 1.3rem; /* Increased */
        transition: transform 0.2s;
        text-decoration: none;
    }
    .footer-socials a:hover {
        transform: translateY(-3px);
    }
    
    .footer-heading {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: rgba(0, 162, 233, 0.15);
        color: #004d80;
        padding: 10px 25px; /* Increased padding */
        border-radius: 25px;
        font-weight: 800;
        font-size: 1.15rem; /* Increased */
        margin-bottom: 25px;
    }
    
    .footer-col-2 p {
        font-size: 1.05rem; /* Increased */
        line-height: 1.8;
        color: #0f3c5c;
        font-weight: 600;
    }
    
    .footer-contact-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .footer-contact-list li {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 16px;
        font-size: 1.05rem; /* Increased */
        font-weight: 600;
    }
    .footer-contact-list li i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px; /* Increased */
        height: 32px;
        background: #5db2f4;
        color: #fff;
        border-radius: 50%;
        font-size: 0.9rem;
        flex-shrink: 0;
        margin-top: 2px;
    }
    
    .footer-article-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .footer-article-item {
        display: flex;
        align-items: center;
        gap: 18px;
        text-decoration: none;
    }
    .footer-article-img {
        width: 85px; /* Increased */
        height: 60px; /* Increased */
        border-radius: 8px;
        object-fit: cover;
        box-shadow: 0 4px 6px rgba(0,0,0,0.15);
    }
    .footer-article-title {
        font-size: 1.05rem; /* Increased */
        font-weight: 700;
        color: #0070ad;
        line-height: 1.4;
    }
    
    .footer-bottom {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        text-align: center;
        padding: 20px 40px;
        font-size: 0.95rem; /* Increased */
        color: #fff;
        font-weight: 500;
    }
    .footer-bottom-divider {
        display: inline-block;
        width: 50px;
        height: 1px;
        background: rgba(255,255,255,0.4);
        vertical-align: middle;
        margin: 0 15px;
    }
    
    @media (min-width: 1600px) {
        .footer-premium {
            padding-top: 80px;
        }
        .footer-content {
            padding-bottom: 180px; /* More bottom space for ultra-wide screens to clear the wave */
        }
    }
    
    @media (max-width: 992px) {
        .footer-content {
            grid-template-columns: 1fr 1fr;
            padding-bottom: 80px;
        }
        .footer-premium { padding-top: 40px; }
    }
    @media (max-width: 576px) {
        .footer-content {
            grid-template-columns: 1fr;
        }
    }
</style>

<footer class="footer-premium">
    <div class="footer-content">
        <!-- Kolom 1: Logo -->
        <div class="footer-col-1">
            <img src="/talenta-semarang/assets/images/logo.png" alt="Talenta Semarang">
            <div class="footer-motto">Murid Hebat, Masa Depan Kuat</div>
            <div class="footer-socials">
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        
        <!-- Kolom 2: Sekilas Kami -->
        <div class="footer-col-2">
            <div class="footer-heading"><i class="fas fa-sparkles" style="color: #0070ad;"></i> Sekilas Kami</div>
            <p>Sekolah Talenta Semarang hadir untuk bermitra dengan orang tua memberi layanan pendidikan yang sesuai dengan tahap perkembangan anak.</p>
        </div>
        
        <!-- Kolom 3: Kontak Kami -->
        <div class="footer-col-3">
            <div class="footer-heading"><i class="fas fa-phone-alt" style="color: #0070ad;"></i> Kontak Kami</div>
            <ul class="footer-contact-list">
                <li><i class="fas fa-phone"></i> <span>0817-838-939</span></li>
                <li><i class="fas fa-phone"></i> <span>024-760-1502</span></li>
                <li><i class="fas fa-envelope"></i> <span>talenta.lpt@gmail.com</span></li>
                <li><i class="fas fa-map-marker-alt"></i> <span>Jl. Puspowarno Tengah IX No. 6<br>Semarang Barat</span></li>
            </ul>
        </div>
        
        <!-- Kolom 4: Artikel Terbaru -->
        <div class="footer-col-4">
            <div class="footer-heading"><i class="far fa-file-alt" style="color: #0070ad;"></i> Artikel Terbaru</div>
            <div class="footer-article-list">
                <a href="#" class="footer-article-item">
                    <img src="/talenta-semarang/assets/images/hero_kids_new.png" class="footer-article-img" alt="Anak Terlambat Bicara">
                    <span class="footer-article-title">Anak Terlambat Bicara</span>
                </a>
                <a href="#" class="footer-article-item">
                    <img src="/talenta-semarang/assets/images/hero-bg-cropped.png" class="footer-article-img" alt="Anak Berkebutuhan Khusus">
                    <span class="footer-article-title">Anak Berkebutuhan Khusus</span>
                </a>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <span class="footer-bottom-divider"></span>
        &copy; 2024 Sekolah Talenta Semarang. All Rights Reserved. Published by www.talentasemarang.com
        <span class="footer-bottom-divider"></span>
    </div>
</footer>

<style>
/* Global Lightbox Styles */
.global-lightbox {
    display: none;
    position: fixed;
    z-index: 999999;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,0.85);
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
}
.global-lightbox.active {
    display: flex;
}
.global-lightbox img {
    max-width: 90%;
    max-height: 90vh;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    animation: zoomIn 0.3s ease;
    object-fit: contain;
}
.global-lightbox-close {
    position: absolute;
    top: 20px;
    right: 30px;
    color: white;
    font-size: 45px;
    cursor: pointer;
    font-weight: bold;
    user-select: none;
    transition: color 0.2s;
}
.global-lightbox-close:hover {
    color: #5db2f4;
}
@keyframes zoomIn {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>

<div class="global-lightbox" id="globalLightbox">
    <span class="global-lightbox-close" id="globalLightboxClose">&times;</span>
    <img src="" id="globalLightboxImg" alt="Popup Image">
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lightbox = document.getElementById('globalLightbox');
    const lightboxImg = document.getElementById('globalLightboxImg');
    const closeBtn = document.getElementById('globalLightboxClose');

    // Select all images on the page
    const images = document.querySelectorAll('img');
    
    images.forEach(img => {
        // Exclude header logo, footer logo, profile banner, and images inside links
        if (
            img.closest('.header') || 
            img.closest('.footer-col-1') || 
            img.closest('a') || 
            img.classList.contains('profil-banner-img') ||
            img.classList.contains('galeri-banner-img') ||
            img.classList.contains('berita-banner-img') ||
            img.classList.contains('artikel-banner-img') ||
            img.classList.contains('page-banner-img') ||
            img.classList.contains('hero-kids')
        ) {
            return;
        }
        
        // Add pointer cursor to indicate clickability
        img.style.cursor = 'pointer';
        
        img.addEventListener('click', function() {
            lightboxImg.src = this.src;
            lightbox.classList.add('active');
        });
    });

    closeBtn.addEventListener('click', function() {
        lightbox.classList.remove('active');
    });

    lightbox.addEventListener('click', function(e) {
        if (e.target !== lightboxImg) {
            lightbox.classList.remove('active');
        }
    });
    
    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && lightbox.classList.contains('active')) {
            lightbox.classList.remove('active');
        }
    });
});
</script>

<script>
    // Simple script for sticky header if needed
    window.addEventListener('scroll', function() {
        const header = document.querySelector('.header');
        if(window.scrollY > 50) {
            header.style.boxShadow = '0 5px 20px rgba(0,0,0,0.1)';
        } else {
            header.style.boxShadow = '0 2px 15px rgba(0,0,0,0.05)';
        }
    });
</script>



<!-- Floating WhatsApp Button -->
<a href="https://wa.me/62817838939" target="_blank" class="floating-wa">
    <i class="fab fa-whatsapp"></i>
</a>

<style>
    .floating-wa {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background-color: #25D366;
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 35px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        z-index: 1000;
        transition: transform 0.3s ease;
        text-decoration: none;
    }
    .floating-wa:hover {
        transform: scale(1.1);
        color: white;
    }
</style>
</body>
</html>
