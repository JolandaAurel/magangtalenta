<?php
require_once 'includes/header.php';

$images_dir = '../assets/images/';

// Insert New Photo (Replace Inline Image)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'replace_inline') {
    $page_id = (int)$_POST['page_id'];
    $old_src = $_POST['old_src']; // e.g., /outsource_talenta_landing/magangtalenta/assets/images/daycare1.png
    
    // Fetch current html
    $stmt = $pdo->prepare("SELECT content_html, slug FROM pages WHERE id = ?");
    $stmt->execute([$page_id]);
    $page = $stmt->fetch();
    
    if ($page && isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
        $slug = $page['slug'];
        $tmp_name = $_FILES['new_image']['tmp_name'];
        // Create unique name
        $name = $slug . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $_FILES['new_image']['name']);
        $dest = $images_dir . $name;
        $new_src = '/outsource_talenta_landing/magangtalenta/assets/images/' . $name;
        
        if (move_uploaded_file($tmp_name, $dest)) {
            // Replace the exact old src with the new src in the HTML
            $new_html = str_replace($old_src, $new_src, $page['content_html']);
            
            $updateStmt = $pdo->prepare("UPDATE pages SET content_html = ? WHERE id = ?");
            $updateStmt->execute([$new_html, $page_id]);
            
            echo "<script>alert('Gambar di dalam konten berhasil diganti!'); window.location='kelola_gambar_konten.php?id=".$page_id."';</script>";
            exit;
        }
    }
}

// Fetch available pages
$stmt = $pdo->query("SELECT id, slug, title FROM pages ORDER BY title ASC");
$pages = $stmt->fetchAll();

$selected_id = isset($_GET['id']) ? (int)$_GET['id'] : 6; // Default to daycare id 6 if possible
$selected_page_title = '';
$selected_html = '';

foreach($pages as $p) {
    if($p['id'] === $selected_id) {
        $selected_page_title = $p['title'];
        
        $stmt_content = $pdo->prepare("SELECT content_html FROM pages WHERE id = ?");
        $stmt_content->execute([$selected_id]);
        $selected_html = $stmt_content->fetchColumn();
    }
}

// Extract all image SRCs using Regex
$inline_images = [];
if ($selected_html) {
    preg_match_all('/<img[^>]+src="([^">]+)"/i', $selected_html, $matches);
    if (!empty($matches[1])) {
        // Remove duplicates just in case, but keep keys to allow replacing all instances
        $inline_images = array_unique($matches[1]);
    }
}
?>

<div class="card">
    <h3>Kelola Gambar di Dalam Teks (Konten Halaman)</h3>
    <p style="margin-top: 10px; color: #64748b;">Pilih halaman di bawah ini untuk melihat semua gambar yang menempel di dalam paragraf atau kotak layanannya. Anda bisa mengganti gambar tersebut secara spesifik hanya untuk halaman ini.</p>
    
    <form method="GET" style="margin-top: 20px; display: flex; gap: 10px; max-width: 500px;">
        <select name="id" class="form-control" onchange="this.form.submit()">
            <option value="">-- Pilih Halaman --</option>
            <?php foreach($pages as $p): ?>
                <option value="<?php echo $p['id']; ?>" <?php echo $selected_id === $p['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($p['title']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary" style="white-space: nowrap;">Lihat Gambar Konten</button>
    </form>
</div>

<?php if ($selected_id): ?>
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h4>Gambar yang ditemukan di halaman: <?php echo htmlspecialchars($selected_page_title); ?></h4>
    </div>
    
    <?php if(count($inline_images) == 0): ?>
        <p style="text-align: center; color: #94a3b8; padding: 20px;">Tidak ada gambar yang disisipkan di dalam teks halaman ini.</p>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
            <?php foreach($inline_images as $index => $src): ?>
            <div style="border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; background: #f8fafc; display: flex; flex-direction: column;">
                <div style="height: 150px; display: flex; align-items: center; justify-content: center; background: #e2e8f0; padding: 10px;">
                    <img src="<?php echo htmlspecialchars($src); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                </div>
                <div style="padding: 15px; flex-grow: 1; display: flex; flex-direction: column;">
                    <p style="font-size: 0.75rem; color: #64748b; margin-bottom: 15px; word-break: break-all; text-align: center;"><code><?php echo basename($src); ?></code></p>
                    
                    <form method="POST" enctype="multipart/form-data" style="margin-top: auto;">
                        <input type="hidden" name="action" value="replace_inline">
                        <input type="hidden" name="page_id" value="<?php echo $selected_id; ?>">
                        <input type="hidden" name="old_src" value="<?php echo htmlspecialchars($src); ?>">
                        <input type="file" name="new_image" accept="image/*" class="form-control" style="font-size: 0.75rem; padding: 5px; margin-bottom: 10px;" required>
                        <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 0.8rem;"><i class="fas fa-sync-alt"></i> Ganti Gambar Ini</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
