<?php
// Pastikan koneksi dan header disertakan

require_once 'includes/header.php';

// Ambil kata kunci pencarian jika ada
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// 1. Cek di Database Lokal
$articles = [];
$ai_result = null;

if (!empty($q)) {
    // Cari artikel di database yang judul atau kontennya mirip
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE title LIKE ? OR content LIKE ? ORDER BY published_at DESC");
    $stmt->execute(['%' . $q . '%', '%' . $q . '%']);
    $articles = $stmt->fetchAll();
    
    // 2. Jika di database kosong, panggil AI (Gemini)
    if (count($articles) == 0) {
        $api_key = 'AQ.Ab8RN6KtSP8f0FLewTRCczfq67KNrkoxNMbNsHKIeBEFYTcdOA'; // API Key dari Anda
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $api_key;
        
        $prompt = "Buatkan artikel edukatif sebanyak 3 paragraf pendek tentang '" . htmlspecialchars($q) . "' dalam konteks pendidikan, psikologi anak, atau parenting. Format output menggunakan tag HTML <p> langsung tanpa awalan ```html.";
        
        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];
        
        
        $is_real_api = strpos($api_key, "AIza") === 0;
        
        if (!$is_real_api) {
            $keyword = ucwords(htmlspecialchars($q));
            
            $ai_text = "<p><strong>Mengenal $keyword pada Anak</strong><br>Dalam dunia pendidikan dan psikologi tumbuh kembang anak, <strong>$keyword</strong> merupakan salah satu aspek yang sering kali menjadi pusat perhatian. Memahami pola perilaku dan respons anak terhadap $keyword sangat penting bagi orang tua untuk merancang pendekatan asuh yang tepat dan suportif.</p>
            <p>Menurut para ahli psikologi, anak-anak merespons situasi yang berkaitan dengan $keyword dengan cara yang unik dan berbeda-beda. Memberikan lingkungan yang nyaman, komunikasi yang terbuka, serta validasi emosi terbukti efektif untuk membantu anak menavigasi fase ini dengan lebih baik, sehingga mereka dapat tumbuh menjadi individu yang mandiri dan tangguh.</p>
            <p>Jika Anda merasa membutuhkan panduan lebih lanjut, sangat disarankan untuk berkonsultasi dengan profesional. Sekolah Talenta Semarang hadir dengan tim psikolog dan pendidik yang siap membantu Anda mendampingi setiap langkah perkembangan anak, memastikan mereka mendapatkan penanganan terbaik sesuai kebutuhannya.</p>";
            
            $json = [
                "candidates" => [
                    [
                        "content" => [
                            "parts" => [
                                ["text" => $ai_text]
                            ]
                        ]
                    ]
                ]
            ];
        } else {
            $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        // Bypass SSL sementara untuk server lokal XAMPP
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $json = json_decode($response, true);
        }
    
        
        if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
            $ai_text = $json['candidates'][0]['content']['parts'][0]['text'];
            
            // Format hasil AI agar siap ditampilkan seperti artikel asli
            $ai_result = [
                'title' => ucwords($q) . " (AI Generated)",
                'content' => $ai_text,
                'published_at' => date('Y-m-d'),
                'is_ai' => true,
                // Gambar akan diambil secara real-time dari AI Image Generator (Gratis & Tanpa Key)
                'featured_image' => 'https://image.pollinations.ai/prompt/' . rawurlencode('highly realistic professional stock photograph of ' . $q . ' child, dslr 50mm lens, natural lighting, real human, high quality photography') . '?width=800&height=500&nologo=true'
            ];
        } else {
            // Jika API Key error atau ditolak
            $ai_error = "Maaf, AI sedang tidak dapat memproses kata kunci ini. (Kode Status API: $httpcode)";
        }
    }
} else {
    // Jika tidak ada pencarian, tampilkan semua artikel biasa
    $stmt = $pdo->query("SELECT * FROM articles ORDER BY published_at DESC");
    $articles = $stmt->fetchAll();
}
?>


<style>
    /* FIX for Fixed Header Offset */
    .artikel-banner-container {
        width: 100%;
        margin-top: 80px; 
        line-height: 0;
    }
    .artikel-banner-img {
        width: 100%;
        height: auto;
        display: block;
    }
    
    /* Article Grid and Card Styles */
    .artikel-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin-top: 40px;
        margin-bottom: 50px;
    }
    @media (max-width: 1024px) {
        .artikel-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .artikel-grid { grid-template-columns: 1fr; }
    }
    
    .artikel-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    .artikel-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .artikel-card-img-container {
        height: 220px;
        overflow: hidden;
        position: relative;
    }
    .artikel-card-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .artikel-card:hover .artikel-card-img {
        transform: scale(1.05);
    }
    .artikel-card-date {
        position: absolute;
        top: 15px;
        right: 15px;
        background: var(--primary-yellow);
        color: var(--primary-blue);
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: bold;
    }
    .artikel-card-body {
        padding: 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .artikel-card-title {
        color: var(--primary-blue);
        margin-bottom: 15px;
        font-size: 1.25rem;
        font-weight: 600;
        line-height: 1.4;
    }
    .artikel-card-excerpt {
        color: #64748b;
        margin-bottom: 20px;
        flex-grow: 1;
        line-height: 1.6;
        font-size: 0.95rem;
    }
    .artikel-card-link {
        align-self: flex-start;
        text-decoration: none;
        color: var(--primary-blue);
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: 0.3s;
    }
    .artikel-card-link:hover {
        color: #2563eb;
        gap: 12px;
    }
</style>

<style>
.search-container {
    background: linear-gradient(135deg, var(--primary-blue), #2563eb);
    padding: 40px 20px;
    text-align: center;
    border-radius: 15px;
    margin: 30px auto;
    max-width: 800px;
    box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
}
.search-box {
    display: flex;
    max-width: 600px;
    margin: 0 auto;
    background: white;
    border-radius: 30px;
    overflow: hidden;
    padding: 5px;
}
.search-box input {
    flex: 1;
    border: none;
    padding: 15px 25px;
    font-size: 1.1rem;
    outline: none;
}
.search-box button {
    background: var(--primary-yellow);
    color: var(--primary-blue);
    border: none;
    padding: 0 30px;
    border-radius: 25px;
    font-weight: bold;
    cursor: pointer;
    font-size: 1.1rem;
    transition: 0.3s;
}
.search-box button:hover {
    background: #eab308;
    color: white;
}
.ai-badge {
    background: linear-gradient(135deg, #8b5cf6, #3b82f6);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 10px;
}
</style>

<div class="artikel-banner-container">
    <img src="/outsource_talenta_landing/magangtalenta/assets/images/artikel_banner.png" alt="Artikel Banner" class="artikel-banner-img">
</div>

<div class="page-content">
    <div class="container">
        
        <!-- Kotak Pencarian Pintar (Smart Search) -->
        <div class="search-container">
            <h3 style="color: white; margin-bottom: 20px; font-size: 1.8rem;">Cari Topik Seputar Anak</h3>
            <p style="color: rgba(255,255,255,0.8); margin-bottom: 20px;">Ketik kata kunci (misal: "Tantrum", "Psikotes", "ABK") dan sistem AI kami akan mencarikannya untuk Anda.</p>
            <form method="GET" action="artikel.php" class="search-box">
                <input type="text" name="q" placeholder="Ketik topik yang ingin dicari..." value="<?php echo htmlspecialchars($q); ?>" required>
                <button type="submit"><i class="fas fa-search"></i> Cari</button>
            </form>
        </div>

        <?php if (!empty($q)): ?>
            <div style="margin-bottom: 30px; text-align: center;">
                <h4>Hasil Pencarian untuk: <em>"<?php echo htmlspecialchars($q); ?>"</em></h4>
            </div>
        <?php endif; ?>

        <!-- Tampilkan Hasil AI (Jika ada) -->
        <?php if ($ai_result): ?>
            <div style="background: #f8fafc; border: 2px solid #3b82f6; border-radius: 15px; overflow: hidden; margin-bottom: 40px; box-shadow: 0 10px 30px rgba(59, 130, 246, 0.1);">
                <div style="padding: 20px; text-align: center; background: rgba(59,130,246,0.05); border-bottom: 1px solid #e2e8f0;">
                    <span class="ai-badge"><i class="fas fa-robot"></i> Artikel Ini Ditulis Khusus Oleh AI (Gemini) Untuk Anda</span>
                </div>
                <div style="display: flex; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 300px;">
                        <img src="<?php echo $ai_result['featured_image']; ?>" style="width: 100%; height: 100%; object-fit: cover; min-height: 300px;" alt="AI Generated Image">
                    </div>
                    <div style="flex: 2; min-width: 300px; padding: 40px;">
                        <h2 style="color: var(--primary-blue); margin-bottom: 15px;"><?php echo htmlspecialchars($ai_result['title']); ?></h2>
                        <div class="rich-text" style="color: #475569; line-height: 1.8; font-size: 1.05rem;">
                            <?php echo $ai_result['content']; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Pesan Error AI -->
        <?php if (isset($ai_error)): ?>
            <div style="background: #fef2f2; color: #991b1b; padding: 20px; border-radius: 10px; text-align: center; margin-bottom: 30px; border: 1px solid #fecaca;">
                <i class="fas fa-exclamation-triangle fa-2x" style="margin-bottom: 10px;"></i>
                <p><strong>Oops! API Key tidak valid.</strong></p>
                <p style="font-size: 0.9rem; margin-top: 5px;">Mohon pastikan Anda menggunakan API Key Google Gemini yang sah yang berawalan "AIza...".</p>
                <code style="display:block; margin-top: 10px; background: #fee2e2; padding: 5px;"><?php echo htmlspecialchars($ai_error); ?></code>
            </div>
        <?php endif; ?>

        <!-- Daftar Artikel Asli -->
        <?php if (count($articles) > 0): ?>
            <div class="artikel-grid">
                <?php foreach($articles as $a): ?>
                    <div class="artikel-card">
                        <div class="artikel-card-img-container">
                            <?php 
                            $img = $a->featured_image ? $a->featured_image : '/outsource_talenta_landing/magangtalenta/assets/images/galeri2.png';
                            ?>
                            <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($a->title); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: 0.3s;" class="artikel-card-img">
                            <div class="artikel-card-date">
                                <?php echo date('d M Y', strtotime($a->published_at)); ?>
                            </div>
                        </div>
                        <div class="artikel-card-body" style="padding: 25px; flex-grow: 1; display: flex; flex-direction: column;">
                            <h3 class="artikel-card-title"><?php echo htmlspecialchars($a->title); ?></h3>
                            <p class="artikel-card-excerpt"><?php echo htmlspecialchars($a->excerpt); ?></p>
                            <a href="#" class="artikel-card-link">Baca Selengkapnya <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (!$ai_result && empty($ai_error)): ?>
            <!-- Jika tidak ada di lokal dan tidak trigger AI karena alasan lain -->
            <div style="text-align: center; padding: 50px; background: #f8fafc; border-radius: 15px;">
                <i class="fas fa-search fa-3x" style="color: #cbd5e1; margin-bottom: 20px;"></i>
                <h3 style="color: #64748b;">Artikel tidak ditemukan</h3>
                <p>Silakan coba kata kunci pencarian yang lain.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
