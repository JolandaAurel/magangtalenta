<?php
require_once 'includes/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM programs WHERE id = ?")->execute([$id]);
    echo "<script>alert('Program berhasil dihapus!'); window.location='kelola_program.php';</script>";
    exit;
}

// Handle Form Submission (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $title = $_POST['title'];
    $description = $_POST['description'];
    $icon_class = $_POST['icon_class'];
    $link_url = $_POST['link_url'];
    $sort_order = $_POST['sort_order'];
    
    if ($id) {
        $stmt = $pdo->prepare("UPDATE programs SET title=?, description=?, icon_class=?, link_url=?, sort_order=? WHERE id=?");
        $stmt->execute([$title, $description, $icon_class, $link_url, $sort_order, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO programs (title, description, icon_class, link_url, sort_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $icon_class, $link_url, $sort_order]);
    }
    echo "<script>alert('Data program berhasil disimpan!'); window.location='kelola_program.php';</script>";
    exit;
}

// Fetch Program to Edit
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editData = $stmt->fetch();
}

// Fetch All Programs
$stmt = $pdo->query("SELECT * FROM programs ORDER BY sort_order ASC");
$programs = $stmt->fetchAll();
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3><?php echo $editData ? 'Edit Program Utama' : 'Kelola Program Utama (5 Kotak di Home)'; ?></h3>
        <?php if($editData): ?>
            <a href="kelola_program.php" class="btn btn-primary">Batal Edit</a>
        <?php endif; ?>
    </div>
    
    <form method="POST" style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 30px;">
        <input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Judul Kotak</label>
                <input type="text" name="title" class="form-control" value="<?php echo $editData['title'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Icon Class (FontAwesome)</label>
                <input type="text" name="icon_class" class="form-control" value="<?php echo $editData['icon_class'] ?? 'fas fa-star'; ?>" required>
            </div>
            <div class="form-group">
                <label>URL Tujuan (Link saat diklik)</label>
                <input type="text" name="link_url" class="form-control" value="<?php echo $editData['link_url'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Urutan (Angka)</label>
                <input type="number" name="sort_order" class="form-control" value="<?php echo $editData['sort_order'] ?? '1'; ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label>Deskripsi Singkat</label>
            <input type="text" name="description" class="form-control" value="<?php echo $editData['description'] ?? ''; ?>" required>
        </div>
        
        <button type="submit" class="btn btn-primary"><?php echo $editData ? 'Update Program' : 'Tambah Program'; ?></button>
    </form>
    
    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin-bottom: 20px;">
    
    <h3>Daftar Program (Kotak Beranda)</h3>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Urutan</th>
                    <th>Icon</th>
                    <th>Judul</th>
                    <th>Deskripsi</th>
                    <th>URL Tujuan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($programs as $p): ?>
                <tr>
                    <td><?php echo $p['sort_order']; ?></td>
                    <td><i class="<?php echo $p['icon_class']; ?> text-xl" style="color: var(--primary);"></i></td>
                    <td><strong><?php echo htmlspecialchars($p['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($p['description']); ?></td>
                    <td><code style="background:#e2e8f0; padding:2px 6px; border-radius:4px;"><?php echo htmlspecialchars($p['link_url']); ?></code></td>
                    <td>
                        <a href="?edit=<?php echo $p['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem;"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
