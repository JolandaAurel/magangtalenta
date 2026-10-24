<?php
require_once 'includes/header.php';

$images_dir = '../assets/images/';

// Insert New Photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $page_slug = $_POST['page_slug'];
    
    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['new_image']['tmp_name'];
        $name = time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $_FILES['new_image']['name']);
        $dest = $images_dir . $name;
        
        if (move_uploaded_file($tmp_name, $dest)) {
            // Get current max sort_order
            $stmt = $pdo->prepare("SELECT MAX(sort_order) FROM page_galleries WHERE page_slug = ?");
            $stmt->execute([$page_slug]);
            $max_sort = (int)$stmt->fetchColumn();
            
            $stmt = $pdo->prepare("INSERT INTO page_galleries (page_slug, image_file, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$page_slug, $name, $max_sort + 1]);
            echo "<script>alert('Foto berhasil ditambahkan!'); window.location='kelola_galeri_halaman.php?slug=".$page_slug."';</script>";
            exit;
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Get file name and slug before delete
    $stmt = $pdo->prepare("SELECT image_file, page_slug FROM page_galleries WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    
    if ($row) {
        $pdo->prepare("DELETE FROM page_galleries WHERE id = ?")->execute([$id]);
        
        // Optionally delete the physical file if you don't want it lying around
        // if(file_exists($images_dir . $row['image_file'])) unlink($images_dir . $row['image_file']);
        
        echo "<script>alert('Foto dihapus!'); window.location='kelola_galeri_halaman.php?slug=".$row['page_slug']."';</script>";
        exit;
    }
}

// Fetch available pages
$stmt = $pdo->query("SELECT slug, title FROM pages ORDER BY title ASC");
$pages = $stmt->fetchAll();

$selected_slug = $_GET['slug'] ?? 'tk';
$selected_page_title = '';
foreach($pages as $p) {
    if($p['slug'] === $selected_slug) $selected_page_title = $p['title'];
}

// Fetch photos for selected page
$stmt = $pdo->prepare("SELECT * FROM page_galleries WHERE page_slug = ? ORDER BY sort_order ASC, id ASC");
$stmt->execute([$selected_slug]);
$galleries = $stmt->fetchAll();
?>

<div class="card">
    <h3>Kelola Galeri Kegiatan Spesifik (Per Unit)</h3>
    <p style="margin-top: 10px; color: #64748b;">Pilih unit sekolah atau layanan di bawah ini, lalu tambahkan foto kegiatan anak-anak. Foto akan otomatis disusun rapi di bagian bawah halaman tersebut.</p>
    
    <form method="GET" style="margin-top: 20px; display: flex; gap: 10px; max-width: 500px;">
        <select name="slug" class="form-control" onchange="this.form.submit()">
            <?php foreach($pages as $p): ?>
                <option value="<?php echo $p['slug']; ?>" <?php echo $selected_slug === $p['slug'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($p['title']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary" style="white-space: nowrap;">Pilih Halaman</button>
    </form>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h4>Foto Kegiatan: <?php echo htmlspecialchars($selected_page_title); ?></h4>
    </div>
    
    <form method="POST" enctype="multipart/form-data" style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px dashed #cbd5e1; margin-bottom: 30px; display: flex; gap: 15px; align-items: flex-end;">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="page_slug" value="<?php echo htmlspecialchars($selected_slug); ?>">
        
        <div class="form-group" style="margin-bottom: 0; flex-grow: 1;">
            <label>Upload Foto Baru</label>
            <input type="file" name="new_image" accept="image/*" class="form-control" required>
        </div>
        
        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Foto</button>
    </form>
    
    <?php if(count($galleries) == 0): ?>
        <p style="text-align: center; color: #94a3b8; padding: 20px;">Belum ada foto kegiatan di halaman ini.</p>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
            <?php foreach($galleries as $g): ?>
            <div style="position: relative; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <img src="/outsource_talenta_landing/magangtalenta/assets/images/<?php echo $g['image_file']; ?>" style="width: 100%; aspect-ratio: 1; object-fit: cover; display: block;">
                <div style="padding: 10px; background: white; text-align: center;">
                    <a href="?delete=<?php echo $g['id']; ?>&slug=<?php echo $selected_slug; ?>" class="btn btn-danger" style="font-size: 0.8rem; padding: 4px 8px; width: 100%;" onclick="return confirm('Hapus foto ini?');"><i class="fas fa-trash"></i> Hapus</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
