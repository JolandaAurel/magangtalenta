<?php
require_once 'includes/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);
    echo "<script>alert('Artikel berhasil dihapus!'); window.location='kelola_artikel.php';</script>";
    exit;
}

// Handle Form Submission (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $title = $_POST['title'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    $excerpt = $_POST['excerpt'];
    $content = $_POST['content'];
    $published_at = $_POST['published_at'];
    
    // Default image
    $featured_image = $_POST['existing_image'] ?? 'artikel_placeholder.png';
    
    // Handle file upload
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['featured_image']['tmp_name'];
        $name = time() . '_' . $_FILES['featured_image']['name'];
        $dest = '../assets/images/' . $name;
        if (move_uploaded_file($tmp_name, $dest)) {
            $featured_image = $name;
        }
    }
    
    if ($id) {
        // Update
        $stmt = $pdo->prepare("UPDATE articles SET title=?, slug=?, excerpt=?, content=?, featured_image=?, published_at=? WHERE id=?");
        $stmt->execute([$title, $slug, $excerpt, $content, $featured_image, $published_at, $id]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO articles (title, slug, excerpt, content, featured_image, published_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $excerpt, $content, $featured_image, $published_at]);
    }
    echo "<script>alert('Data berhasil disimpan!'); window.location='kelola_artikel.php';</script>";
    exit;
}

// Fetch Article to Edit
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editData = $stmt->fetch();
}

// Fetch All Articles
$stmt = $pdo->query("SELECT * FROM articles ORDER BY published_at DESC, id DESC");
$articles = $stmt->fetchAll();
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3><?php echo $editData ? 'Edit Artikel' : 'Kelola Artikel & Berita'; ?></h3>
        <?php if($editData): ?>
            <a href="kelola_artikel.php" class="btn btn-primary">Batal Edit</a>
        <?php endif; ?>
    </div>
    
    <form method="POST" enctype="multipart/form-data" style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px;">
        <input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">
        <?php if($editData): ?>
            <input type="hidden" name="existing_image" value="<?php echo $editData['featured_image']; ?>">
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Judul Artikel</label>
                <input type="text" name="title" class="form-control" value="<?php echo $editData['title'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Tanggal Terbit</label>
                <input type="date" name="published_at" class="form-control" value="<?php echo $editData['published_at'] ?? date('Y-m-d'); ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label>Gambar Cover (Biarkan kosong jika tidak ingin mengubah)</label>
            <input type="file" name="featured_image" class="form-control" accept="image/*">
            <?php if($editData && $editData['featured_image']): ?>
                <p style="font-size: 0.8rem; margin-top: 5px; color: #64748b;">Gambar saat ini: <?php echo $editData['featured_image']; ?></p>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label>Ringkasan (Excerpt) - Tampil di halaman depan</label>
            <textarea name="excerpt" class="form-control" rows="2" required><?php echo $editData['excerpt'] ?? ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Konten Lengkap</label>
            <textarea name="content" class="form-control" rows="8" required><?php echo $editData['content'] ?? ''; ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary"><?php echo $editData ? 'Update Artikel' : 'Tambah Artikel'; ?></button>
    </form>
    
    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin-bottom: 20px;">
    
    <h3>Daftar Artikel</h3>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Judul</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($articles as $a): ?>
                <tr>
                    <td><img src="/talenta-semarang/assets/images/<?php echo $a['featured_image']; ?>" style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px;"></td>
                    <td><strong><?php echo htmlspecialchars($a['title']); ?></strong></td>
                    <td><?php echo date('d M Y', strtotime($a['published_at'])); ?></td>
                    <td>
                        <a href="?edit=<?php echo $a['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem;"><i class="fas fa-edit"></i></a>
                        <a href="?delete=<?php echo $a['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('Hapus artikel ini?');"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
