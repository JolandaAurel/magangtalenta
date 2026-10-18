<?php
// artikel-detail.php
require_once 'includes/header.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Fetch the specific article
$stmt = $pdo->prepare("SELECT * FROM articles WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    echo "<div style='min-height: 60vh; display: flex; align-items: center; justify-content: center;'><h2>Artikel tidak ditemukan.</h2></div>";
    require_once 'includes/footer.php';
    exit;
}

// Format Date for circle: Month \n Date \n Year
$dateObj = new DateTime($article->published_at);
$months = ['JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI', 'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'];
$monthStr = $months[$dateObj->format('n') - 1];
$dayStr = $dateObj->format('d');
$yearStr = $dateObj->format('Y');

// Fetch related posts (latest 3 excluding this one)
$relStmt = $pdo->prepare("SELECT * FROM articles WHERE id != ? ORDER BY published_at DESC LIMIT 3");
$relStmt->execute([$article->id]);
$related = $relStmt->fetchAll();
?>

<style>
.article-detail-container {
    max-width: 900px;
    margin: 120px auto 50px; /* Offset for header */
    padding: 0 20px;
}

.article-hero {
    width: 100%;
    position: relative;
    margin-bottom: 60px;
}

.article-hero-img {
    width: 100%;
    height: auto;
    max-height: 500px;
    object-fit: cover;
    display: block;
}

.article-date-circle {
    position: absolute;
    bottom: -40px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 80px;
    background: #9ecc53;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: white;
    font-size: 0.75rem;
    font-weight: 700;
    line-height: 1.2;
    text-align: center;
    box-shadow: 0 5px 15px rgba(158, 204, 83, 0.4);
}
.article-date-circle .day {
    font-size: 1.1rem;
}

.article-title-center {
    text-align: center;
    font-size: 2rem;
    color: #333;
    margin-bottom: 40px;
    font-weight: 700;
}

.article-body {
    font-size: 1rem;
    color: #555;
    line-height: 1.8;
    text-align: justify;
    margin-bottom: 60px;
}
.article-body p {
    margin-bottom: 20px;
}
.article-body a {
    color: #5db2f4;
    text-decoration: none;
}

/* Pagination / Next Prev */
.article-nav {
    display: flex;
    justify-content: space-between;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
    padding: 20px 0;
    margin-bottom: 50px;
    font-weight: 600;
}
.article-nav a {
    color: #333;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
}
.article-nav a:hover {
    color: #5db2f4;
}

/* Related Posts */
.related-posts-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 30px;
    color: #333;
}
.related-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
.related-card {
    text-decoration: none;
    color: inherit;
    display: block;
}
.related-img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    margin-bottom: 15px;
}
.related-meta {
    font-size: 0.75rem;
    color: #5db2f4;
    text-transform: uppercase;
    margin-bottom: 8px;
    font-weight: 700;
}
.related-title {
    font-size: 1rem;
    font-weight: 700;
    line-height: 1.4;
    color: #333;
}
.related-card:hover .related-title {
    color: #5db2f4;
}

@media (max-width: 768px) {
    .related-grid {
        grid-template-columns: 1fr;
    }
    .article-nav {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    .article-nav a {
        justify-content: center;
    }
}
</style>

<div class="article-detail-container">
    
    <div class="article-hero">
        <img src="<?php echo htmlspecialchars($article->featured_image); ?>" alt="<?php echo htmlspecialchars($article->title); ?>" class="article-hero-img">
        <div class="article-date-circle">
            <span><?php echo $monthStr; ?></span>
            <span class="day"><?php echo $dayStr; ?></span>
            <span><?php echo $yearStr; ?></span>
        </div>
    </div>

    <h1 class="article-title-center"><?php echo htmlspecialchars($article->title); ?></h1>

    <div style="text-align: center; color: #5db2f4; font-size: 0.85rem; font-weight: bold; text-transform: uppercase; margin-top: -20px; margin-bottom: 40px;">
        ADMIN &nbsp; <span style="color: #ccc;">/</span> &nbsp; Artikel &nbsp; <span style="color: #ccc;">/</span> &nbsp; 0 <i class="fas fa-comment" style="color: #666; margin-left: 5px;"></i>
    </div>

    <div class="article-body">
        <?php 
            // If the content starts with 'Sekolah Talenta', wrap it in a blue link to match the mockup
            $content = $article->content;
            $content = str_replace('<p>Sekolah Talenta', '<p><a href="/talenta-semarang/">Sekolah Talenta</a>', $content);
            echo $content;
        ?>
    </div>

    <div class="article-nav">
        <!-- Mockup shows generic next/prev, we can leave static or pull adjacent rows -->
        <a href="/talenta-semarang/artikel.php"><i class="fas fa-angle-double-left"></i> Kembali ke Artikel</a>
        <a href="/talenta-semarang/artikel.php">Artikel Lainnya <i class="fas fa-angle-double-right"></i></a>
    </div>

    <h3 class="related-posts-title">Related Posts</h3>
    <div class="related-grid">
        <?php foreach($related as $rel): ?>
        <a href="/talenta-semarang/artikel-detail.php?slug=<?php echo $rel->slug; ?>" class="related-card">
            <img src="<?php echo htmlspecialchars($rel->featured_image); ?>" class="related-img" alt="<?php echo htmlspecialchars($rel->title); ?>">
            <div class="related-meta">ARTIKEL</div>
            <h4 class="related-title"><?php echo htmlspecialchars($rel->title); ?></h4>
        </a>
        <?php endforeach; ?>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
