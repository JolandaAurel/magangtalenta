<?php
// search.php
require_once 'includes/header.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$results = [];

if (!empty($q)) {
    // Search in pages
    $stmt = $pdo->prepare("SELECT title, slug, content_html AS content, 'page.php?slug=' AS url_prefix FROM pages WHERE title LIKE :q OR content_html LIKE :q");
    $stmt->execute(['q' => '%' . $q . '%']);
    $pages = $stmt->fetchAll();
    
    // Search in articles
    $stmt2 = $pdo->prepare("SELECT title, slug, content, 'artikel-detail.php?slug=' AS url_prefix FROM articles WHERE title LIKE :q OR content LIKE :q");
    $stmt2->execute(['q' => '%' . $q . '%']);
    $articles = $stmt2->fetchAll();
    
    $results = array_merge($pages, $articles);
}
?>

<style>
    .search-page-container {
        max-width: 1000px;
        margin: 100px auto 100px;
        padding: 0 20px;
        min-height: 50vh;
    }
    
    .search-page-title {
        color: var(--primary-blue);
        font-size: 2.2rem;
        margin-bottom: 40px;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 15px;
    }
    
    .search-result-item {
        background: #fff;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        transition: transform 0.3s ease;
        display: block;
        text-decoration: none;
    }
    
    .search-result-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .search-result-title {
        color: var(--primary-blue);
        font-size: 1.4rem;
        margin-bottom: 10px;
        font-weight: bold;
    }
    
    .search-result-snippet {
        color: #555;
        font-size: 1rem;
        line-height: 1.6;
    }
    
    .no-results {
        text-align: center;
        padding: 50px 20px;
        background: #f8fafc;
        border-radius: 10px;
        color: #64748b;
        font-size: 1.2rem;
    }
    
    .search-bar-inline {
        display: flex;
        gap: 10px;
        margin-bottom: 40px;
    }
    
    .search-bar-inline input {
        flex: 1;
        padding: 15px 20px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1.1rem;
        outline: none;
        transition: border-color 0.3s;
    }
    
    .search-bar-inline input:focus {
        border-color: var(--primary-blue);
    }
    
    .search-bar-inline button {
        padding: 0 30px;
        background: var(--primary-blue);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 1.1rem;
        cursor: pointer;
        font-weight: bold;
    }
</style>

<div class="search-page-container">
    <h1 class="search-page-title">Hasil Pencarian untuk: "<?php echo htmlspecialchars($q); ?>"</h1>
    
    <form action="search.php" method="GET" class="search-bar-inline">
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Cari informasi, program, atau artikel...">
        <button type="submit">Cari</button>
    </form>
    
    <?php if (empty($q)): ?>
        <div class="no-results">
            <i class="fas fa-search" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;"></i>
            <p>Silakan masukkan kata kunci untuk memulai pencarian.</p>
        </div>
    <?php elseif (count($results) > 0): ?>
        <?php foreach($results as $res): 
            $url = "/outsource_talenta_landing/magangtalenta/" . $res->url_prefix . urlencode($res->slug);
            
            // Generate snippet by stripping tags and getting first 150 chars
            $snippet = strip_tags($res->content);
            if (strlen($snippet) > 200) {
                $snippet = substr($snippet, 0, 200) . '...';
            }
        ?>
            <a href="<?php echo $url; ?>" class="search-result-item">
                <div class="search-result-title"><?php echo htmlspecialchars($res->title); ?></div>
                <div class="search-result-snippet"><?php echo htmlspecialchars($snippet); ?></div>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-results">
            <i class="far fa-frown" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;"></i>
            <p>Pencarian "<strong><?php echo htmlspecialchars($q); ?></strong>" not found (tidak ditemukan).</p>
            <p style="font-size: 0.9rem; margin-top: 10px;">Coba gunakan kata kunci lain yang lebih umum.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
