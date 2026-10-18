<?php
require_once 'includes/header.php';

// Helper function to upload images
function handleImageUpload($fileKey, $targetDir = '../assets/images/') {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $tmpName = $_FILES[$fileKey]['tmp_name'];
    $origName = $_FILES[$fileKey]['name'];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed)) {
        return null;
    }
    $newName = 'unit_' . time() . '_' . rand(100, 999) . '.' . $ext;
    if (move_uploaded_file($tmpName, $targetDir . $newName)) {
        return $newName;
    }
    return null;
}

$success = '';
$error = '';
$unitTab = $_GET['tab'] ?? 'tentang'; // 'tentang', 'staff', 'gallery', 'kontak'

// Determine which unit to edit
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$unitSlugParam = isset($_GET['slug']) ? trim($_GET['slug']) : '';

// If unit admin (not super), force to their own unit
if ($_SESSION['admin_type'] !== 'super') {
    $unitSlugParam = $_SESSION['admin_unit_slug'];
}

$editData = null;
if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ? AND category = 'pendidikan'");
    $stmt->execute([$editId]);
    $editData = $stmt->fetch(PDO::FETCH_OBJ);
} elseif (!empty($unitSlugParam)) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND category = 'pendidikan'");
    $stmt->execute([$unitSlugParam]);
    $editData = $stmt->fetch(PDO::FETCH_OBJ);
}

// Security check: unit admin cannot edit another unit
if ($editData && $_SESSION['admin_type'] !== 'super' && $editData->slug !== $_SESSION['admin_unit_slug']) {
    echo "<div class='card'><h3 style='color:#ef4444;'><i class='fas fa-lock'></i> Akses Ditolak</h3><p>Anda hanya memiliki hak akses untuk mengelola unit Anda sendiri.</p><a href='kelola_sekolah.php' class='btn btn-primary'>Kembali</a></div>";
    require_once 'includes/footer.php';
    exit;
}

// =========================================================================
// POST ACTIONS FOR THE 4 SECTIONS: TENTANG, STAFF, GALLERY, KONTAK
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $editData) {
    $action = $_POST['action'] ?? '';
    $unitSlug = $editData->slug;

    // 1. SAVE TENTANG (WYSIWYG)
    if ($action === 'save_tentang') {
        $content_html = $_POST['content_html'] ?? '';
        $upd = $pdo->prepare("UPDATE pages SET content_html = ? WHERE id = ?");
        $upd->execute([$content_html, $editData->id]);
        $success = "Konten 'Tentang {$editData->title}' berhasil diperbarui!";
        $unitTab = 'tentang';
        $editData->content_html = $content_html;
    }

    // 2. STAFF ACTIONS
    elseif ($action === 'add_staff') {
        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        
        $photoName = handleImageUpload('photo');
        if (!$photoName) {
            $photoName = 'guru_1.png'; // default fallback
        }

        if (empty($name) || empty($position)) {
            $error = "Nama dan jabatan staff wajib diisi!";
        } else {
            $insStaff = $pdo->prepare("INSERT INTO unit_staff (unit_slug, name, position, photo, bio) VALUES (?, ?, ?, ?, ?)");
            $insStaff->execute([$unitSlug, $name, $position, $photoName, $bio]);
            $success = "Staff baru '$name' berhasil ditambahkan!";
        }
        $unitTab = 'staff';
    }
    elseif ($action === 'edit_staff') {
        $staffId = (int)$_POST['staff_id'];
        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        $newPhoto = handleImageUpload('photo');
        if ($newPhoto) {
            $upd = $pdo->prepare("UPDATE unit_staff SET name = ?, position = ?, photo = ?, bio = ? WHERE id = ? AND unit_slug = ?");
            $upd->execute([$name, $position, $newPhoto, $bio, $staffId, $unitSlug]);
        } else {
            $upd = $pdo->prepare("UPDATE unit_staff SET name = ?, position = ?, bio = ? WHERE id = ? AND unit_slug = ?");
            $upd->execute([$name, $position, $bio, $staffId, $unitSlug]);
        }
        $success = "Data staff '$name' berhasil diperbarui!";
        $unitTab = 'staff';
    }

    // 3. GALLERY ACTIONS
    elseif ($action === 'add_gallery') {
        $category = strtolower(trim($_POST['category'] ?? 'aktivitas'));
        $caption = trim($_POST['caption'] ?? '');
        $img = handleImageUpload('image_file');

        if (!$img) {
            $error = "Silakan pilih file foto yang valid (JPG, PNG, WebP)!";
        } else {
            $insGal = $pdo->prepare("INSERT INTO page_galleries (page_slug, image_file, category, caption) VALUES (?, ?, ?, ?)");
            $insGal->execute([$unitSlug, $img, $category, $caption]);
            $success = "Foto berhasil ditambahkan ke galeri kategori " . ucfirst($category) . "!";
        }
        $unitTab = 'gallery';
    }
    elseif ($action === 'edit_gallery') {
        $galId = (int)$_POST['gallery_id'];
        $category = strtolower(trim($_POST['category'] ?? 'aktivitas'));
        $caption = trim($_POST['caption'] ?? '');
        $newImg = handleImageUpload('image_file');

        if ($newImg) {
            $upd = $pdo->prepare("UPDATE page_galleries SET image_file = ?, category = ?, caption = ? WHERE id = ? AND page_slug = ?");
            $upd->execute([$newImg, $category, $caption, $galId, $unitSlug]);
        } else {
            $upd = $pdo->prepare("UPDATE page_galleries SET category = ?, caption = ? WHERE id = ? AND page_slug = ?");
            $upd->execute([$category, $caption, $galId, $unitSlug]);
        }
        $success = "Data foto galeri berhasil diperbarui!";
        $unitTab = 'gallery';
    }

    // 4. SAVE KONTAK
    elseif ($action === 'save_contacts') {
        $wa = trim($_POST['whatsapp'] ?? '');
        $ig = trim($_POST['instagram'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $fb = trim($_POST['facebook'] ?? '');
        $tiktok = trim($_POST['tiktok'] ?? '');
        $yt = trim($_POST['youtube'] ?? '');
        $addr = trim($_POST['address'] ?? '');

        $insC = $pdo->prepare("INSERT INTO unit_contacts (unit_slug, whatsapp, instagram, email, facebook, tiktok, youtube, address) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE whatsapp = VALUES(whatsapp), instagram = VALUES(instagram), email = VALUES(email), facebook = VALUES(facebook), tiktok = VALUES(tiktok), youtube = VALUES(youtube), address = VALUES(address)");
        $insC->execute([$unitSlug, $wa, $ig, $email, $fb, $tiktok, $yt, $addr]);
        $success = "Kontak resmi unit {$editData->title} berhasil diperbarui!";
        $unitTab = 'kontak';
    }
}

// GET ACTIONS (DELETE STAFF OR DELETE GALLERY)
if ($editData) {
    if (isset($_GET['delete_staff'])) {
        $delId = (int)$_GET['delete_staff'];
        $pdo->prepare("DELETE FROM unit_staff WHERE id = ? AND unit_slug = ?")->execute([$delId, $editData->slug]);
        $success = "Staff berhasil dihapus.";
        $unitTab = 'staff';
    }
    if (isset($_GET['delete_gallery'])) {
        $delId = (int)$_GET['delete_gallery'];
        $pdo->prepare("DELETE FROM page_galleries WHERE id = ? AND page_slug = ?")->execute([$delId, $editData->slug]);
        $success = "Foto galeri berhasil dihapus.";
        $unitTab = 'gallery';
    }
}

// If in Edit Mode, fetch related data for the 4 sections
$staffList = [];
$galleryList = [];
$contacts = null;

if ($editData) {
    // Staff
    $sStmt = $pdo->prepare("SELECT * FROM unit_staff WHERE unit_slug = ? ORDER BY sort_order ASC, id ASC");
    $sStmt->execute([$editData->slug]);
    $staffList = $sStmt->fetchAll(PDO::FETCH_ASSOC);

    // Gallery
    $gStmt = $pdo->prepare("SELECT * FROM page_galleries WHERE page_slug = ? ORDER BY sort_order ASC, id DESC");
    $gStmt->execute([$editData->slug]);
    $galleryList = $gStmt->fetchAll(PDO::FETCH_ASSOC);

    // Contacts
    $cStmt = $pdo->prepare("SELECT * FROM unit_contacts WHERE unit_slug = ?");
    $cStmt->execute([$editData->slug]);
    $contacts = $cStmt->fetch(PDO::FETCH_ASSOC);
} else {
    // Super Admin unit list overview (the 10 education units)
    $stmt = $pdo->query("SELECT id, slug, title, updated_at FROM pages WHERE category = 'pendidikan' ORDER BY id ASC");
    $educationPages = $stmt->fetchAll(PDO::FETCH_OBJ);
}
?>

<!-- INCLUDE SUMMERNOTE WYSIWYG ASSETS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<style>
/* UNIT EDITOR TABS */
.unit-editor-nav {
    display: flex;
    gap: 10px;
    background: #f8fafc;
    padding: 10px 15px;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    margin-bottom: 25px;
    flex-wrap: wrap;
}
.unit-tab-btn {
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    color: #64748b;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: transparent;
}
.unit-tab-btn:hover {
    color: #0070ad;
    background: #ffffff;
}
.unit-tab-btn.active {
    background: #0070ad;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(0, 112, 173, 0.25);
}
.unit-tab-count {
    background: rgba(0,0,0,0.1);
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 0.75rem;
}
.unit-tab-btn.active .unit-tab-count {
    background: rgba(255,255,255,0.25);
    color: #fff;
}

/* CARDS & GRIDS */
.editor-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}
.staff-admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}
.staff-admin-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 20px;
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}
.staff-admin-card img {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 12px;
    border: 3px solid #00A2E9;
}

.gallery-admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 20px;
}
.gallery-admin-card {
    background: #ffffff;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    position: relative;
}
.gallery-admin-card img {
    width: 100%;
    aspect-ratio: 4/3;
    object-fit: cover;
    display: block;
}

/* MODAL STYLES */
.modal-admin-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(5px);
    z-index: 99999;
    justify-content: center;
    align-items: center;
    padding: 20px;
}
.modal-admin-box {
    background: #ffffff;
    border-radius: 20px;
    width: 100%;
    max-width: 550px;
    padding: 30px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.2);
    position: relative;
}
</style>

<?php if ($editData): ?>
<!-- ========================================================================= -->
<!-- EDIT MODE: 4 SECTIONS FOR THE CHOSEN EDUCATION UNIT -->
<!-- ========================================================================= -->
<div class="editor-header-bar">
    <div>
        <div style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
            <i class="fas fa-school"></i> Unit Pendidikan
        </div>
        <h2 style="color: #0F3C5C; margin: 4px 0 0 0; font-size: 1.7rem;">
            Kelola Unit: <?php echo htmlspecialchars($editData->title); ?>
        </h2>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="/talenta-semarang/page.php?slug=<?php echo htmlspecialchars($editData->slug); ?>" target="_blank" class="btn btn-outline" style="font-weight: 600;">
            <i class="fas fa-external-link-alt"></i> Pratinjau Tampilan User
        </a>
        <?php if ($_SESSION['admin_type'] === 'super'): ?>
            <a href="kelola_sekolah.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Daftar Unit
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($success): ?>
    <div style="background: #dcfce7; color: #166534; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #16a34a; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i> <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div style="background: #fee2e2; color: #991b1b; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc2626; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-exclamation-triangle" style="font-size: 1.2rem;"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<!-- 4 TABS NAVIGATION: TENTANG, STAFF, GALLERY, KONTAK -->
<div class="unit-editor-nav">
    <a href="kelola_sekolah.php?edit=<?php echo $editData->id; ?>&tab=tentang" class="unit-tab-btn <?php echo $unitTab === 'tentang' ? 'active' : ''; ?>">
        <i class="fas fa-file-alt"></i> 1. Tentang (WYSIWYG)
    </a>
    <a href="kelola_sekolah.php?edit=<?php echo $editData->id; ?>&tab=staff" class="unit-tab-btn <?php echo $unitTab === 'staff' ? 'active' : ''; ?>">
        <i class="fas fa-user-graduate"></i> 2. Staff & Pengajar <span class="unit-tab-count"><?php echo count($staffList); ?></span>
    </a>
    <a href="kelola_sekolah.php?edit=<?php echo $editData->id; ?>&tab=gallery" class="unit-tab-btn <?php echo $unitTab === 'gallery' ? 'active' : ''; ?>">
        <i class="fas fa-images"></i> 3. Galeri Foto <span class="unit-tab-count"><?php echo count($galleryList); ?></span>
    </a>
    <a href="kelola_sekolah.php?edit=<?php echo $editData->id; ?>&tab=kontak" class="unit-tab-btn <?php echo $unitTab === 'kontak' ? 'active' : ''; ?>">
        <i class="fas fa-address-book"></i> 4. Kontak & Sosmed
    </a>
</div>

<!-- ========================================================================= -->
<!-- TAB 1: TENTANG (WYSIWYG EDITOR) -->
<!-- ========================================================================= -->
<?php if ($unitTab === 'tentang'): ?>
<div class="card" style="border-left: 5px solid #0070ad;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <div>
            <h3 style="color: #0F3C5C; margin: 0;"><i class="fas fa-edit"></i> Edit Konten 'Tentang Unit' (WYSIWYG)</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin: 4px 0 0 0;">
                Gunakan editor visual di bawah ini untuk mengatur format teks, judul, tebal miring, daftar poin, dan gambar secara fleksibel.
            </p>
        </div>
    </div>

    <form method="POST">
        <input type="hidden" name="action" value="save_tentang">
        <div class="form-group">
            <textarea id="summernote_content" name="content_html" required><?php echo htmlspecialchars($editData->content_html); ?></textarea>
        </div>
        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 25px; font-weight: 600;">
                <i class="fas fa-save"></i> Simpan Konten Tentang
            </button>
            <a href="/talenta-semarang/page.php?slug=<?php echo htmlspecialchars($editData->slug); ?>" target="_blank" class="btn btn-outline" style="padding: 12px 20px;">
                <i class="fas fa-eye"></i> Lihat Hasil di Halaman User
            </a>
        </div>
    </form>
</div>

<!-- ========================================================================= -->
<!-- TAB 2: STAFF (FOTO, NAMA, DESKRIPSI) -->
<!-- ========================================================================= -->
<?php elseif ($unitTab === 'staff'): ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h3 style="color: #0F3C5C; margin: 0;"><i class="fas fa-user-graduate"></i> Daftar Staff & Tenaga Pendidik</h3>
        <p style="color: #64748b; font-size: 0.9rem; margin: 4px 0 0 0;">Kelola foto guru, nama lengkap, jabatan, dan deskripsi/motto mengajar.</p>
    </div>
    <button onclick="openAddStaffModal()" class="btn btn-success" style="box-shadow: 0 4px 12px rgba(16,185,129,0.3);">
        <i class="fas fa-plus"></i> Tambah Staff Baru
    </button>
</div>

<div class="staff-admin-grid">
    <?php if (empty($staffList)): ?>
        <div class="card" style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 40px;">
            <i class="fas fa-user-slash" style="font-size: 3rem; margin-bottom: 10px;"></i>
            <p>Belum ada staf pengajar yang ditambahkan untuk unit ini.</p>
        </div>
    <?php else: ?>
        <?php foreach ($staffList as $st): ?>
        <div class="staff-admin-card">
            <img src="../assets/images/<?php echo htmlspecialchars($st['photo']); ?>" alt="<?php echo htmlspecialchars($st['name']); ?>">
            <h4 style="margin: 0 0 4px 0; color: #0F3C5C; font-size: 1.1rem;"><?php echo htmlspecialchars($st['name']); ?></h4>
            <span class="badge badge-primary" style="margin-bottom: 10px;"><?php echo htmlspecialchars($st['position']); ?></span>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0 0 15px 0; line-height: 1.5;">
                <?php echo htmlspecialchars($st['bio'] ?? '-'); ?>
            </p>
            <div style="display: flex; gap: 8px; width: 100%; justify-content: center; margin-top: auto;">
                <button onclick='openEditStaffModal(<?php echo json_encode($st); ?>)' class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <a href="kelola_sekolah.php?edit=<?php echo $editData->id; ?>&tab=staff&delete_staff=<?php echo $st['id']; ?>" onclick="return confirm('Hapus staf <?php echo htmlspecialchars($st['name']); ?>?')" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i> Hapus
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- TAB 3: GALLERY (PRESTASI, AKTIVITAS, FASILITAS) -->
<!-- ========================================================================= -->
<?php elseif ($unitTab === 'gallery'): ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h3 style="color: #0F3C5C; margin: 0;"><i class="fas fa-images"></i> Galeri Dokumentasi Unit</h3>
        <p style="color: #64748b; font-size: 0.9rem; margin: 4px 0 0 0;">
            Kelola foto untuk kategori: <b>Prestasi</b>, <b>Aktivitas Belajar</b>, dan <b>Fasilitas Unit</b>.
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button onclick="openAddGalModal()" class="btn btn-success" style="box-shadow: 0 4px 12px rgba(16,185,129,0.3);">
            <i class="fas fa-cloud-upload-alt"></i> + Tambah Foto Galeri
        </button>
    </div>
</div>

<!-- CATEGORY FILTER BUTTONS -->
<div style="margin-bottom: 20px; display: flex; gap: 8px; flex-wrap: wrap;">
    <button onclick="filterAdminGal('all')" class="btn btn-sm btn-outline gal-f-btn active" id="f-all">Semua Foto (<?php echo count($galleryList); ?>)</button>
    <button onclick="filterAdminGal('prestasi')" class="btn btn-sm btn-outline gal-f-btn" id="f-prestasi">🏆 Prestasi</button>
    <button onclick="filterAdminGal('aktivitas')" class="btn btn-sm btn-outline gal-f-btn" id="f-aktivitas">🎨 Aktivitas</button>
    <button onclick="filterAdminGal('fasilitas')" class="btn btn-sm btn-outline gal-f-btn" id="f-fasilitas">🏫 Fasilitas</button>
</div>

<div class="gallery-admin-grid">
    <?php if (empty($galleryList)): ?>
        <div class="card" style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 40px;">
            <i class="fas fa-images" style="font-size: 3rem; margin-bottom: 10px;"></i>
            <p>Belum ada foto galeri untuk unit ini.</p>
        </div>
    <?php else: ?>
        <?php foreach ($galleryList as $gal): ?>
            <?php 
            $cat = strtolower($gal['category'] ?? 'aktivitas');
            $badgeColor = ($cat === 'prestasi') ? 'badge-amber' : (($cat === 'fasilitas') ? 'badge-success' : 'badge-primary');
            ?>
            <div class="gallery-admin-card gal-adm-item" data-cat="<?php echo $cat; ?>">
                <img src="../assets/images/<?php echo htmlspecialchars($gal['image_file']); ?>" alt="Galeri">
                <div style="padding: 12px 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span class="badge <?php echo $badgeColor; ?>"><?php echo ucfirst($cat); ?></span>
                    </div>
                    <p style="font-size: 0.85rem; color: #334155; margin: 0 0 10px 0; font-weight: 500;">
                        <?php echo htmlspecialchars($gal['caption'] ?? 'Tanpa Keterangan'); ?>
                    </p>
                    <div style="display: flex; gap: 6px;">
                        <button onclick='openEditGalModal(<?php echo json_encode($gal); ?>)' class="btn btn-sm btn-outline" style="flex: 1;">
                            <i class="fas fa-edit"></i> Edit / Ganti
                        </button>
                        <a href="kelola_sekolah.php?edit=<?php echo $editData->id; ?>&tab=gallery&delete_gallery=<?php echo $gal['id']; ?>" onclick="return confirm('Hapus foto ini?')" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- TAB 4: KONTAK (WA, IG, EMAIL, FB, TIKTOK, YOUTUBE) -->
<!-- ========================================================================= -->
<?php elseif ($unitTab === 'kontak'): ?>
<div class="card" style="max-width: 800px; border-left: 5px solid #10b981;">
    <h3 style="color: #0F3C5C; margin-bottom: 5px;"><i class="fas fa-address-book"></i> Pengaturan Kontak & Media Sosial Unit</h3>
    <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 25px;">
        Nomor dan tautan di bawah ini fleksibel untuk diubah dan langsung tampil di bagian Kontak unit pada halaman website.
    </p>

    <form method="POST">
        <input type="hidden" name="action" value="save_contacts">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label><i class="fab fa-whatsapp" style="color:#25D366;"></i> Nomor WhatsApp Resmi</label>
                <input type="text" name="whatsapp" class="form-control" value="<?php echo htmlspecialchars($contacts['whatsapp'] ?? '0817-838-939'); ?>" required>
                <small style="color: #64748b;">Contoh: 0817-838-939</small>
            </div>
            <div class="form-group">
                <label><i class="fab fa-instagram" style="color:#e1306c;"></i> Instagram Unit</label>
                <input type="text" name="instagram" class="form-control" value="<?php echo htmlspecialchars($contacts['instagram'] ?? '@talenta.' . $editData->slug); ?>" required>
                <small style="color: #64748b;">Contoh: @sd.talentasemarang</small>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label><i class="fas fa-envelope" style="color:#EA4335;"></i> Alamat Email Resmi</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($contacts['email'] ?? $editData->slug . '@talentasemarang.com'); ?>" required>
            </div>
            <div class="form-group">
                <label><i class="fab fa-facebook-f" style="color:#1877F2;"></i> Facebook</label>
                <input type="text" name="facebook" class="form-control" value="<?php echo htmlspecialchars($contacts['facebook'] ?? 'Sekolah Talenta Semarang'); ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label><i class="fab fa-tiktok" style="color:#000000;"></i> TikTok</label>
                <input type="text" name="tiktok" class="form-control" value="<?php echo htmlspecialchars($contacts['tiktok'] ?? '@talentasemarang'); ?>">
            </div>
            <div class="form-group">
                <label><i class="fab fa-youtube" style="color:#FF0000;"></i> YouTube</label>
                <input type="text" name="youtube" class="form-control" value="<?php echo htmlspecialchars($contacts['youtube'] ?? 'Sekolah Talenta Semarang'); ?>">
            </div>
        </div>

        <div class="form-group">
            <label><i class="fas fa-map-marker-alt" style="color:#ef4444;"></i> Alamat / Lokasi Khusus Unit</label>
            <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($contacts['address'] ?? 'Jl. Puspowarno Tengah IX No.6, Semarang Barat'); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-weight: 600; margin-top: 10px;">
            <i class="fas fa-save"></i> Simpan Kontak Unit
        </button>
    </form>
</div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- MODALS FOR STAFF AND GALLERY -->
<!-- ========================================================================= -->

<!-- MODAL TAMBAH STAFF -->
<div class="modal-admin-overlay" id="addStaffModal">
    <div class="modal-admin-box">
        <span onclick="closeAddStaffModal()" style="position: absolute; top: 20px; right: 20px; cursor: pointer; font-size: 1.4rem; color: #94a3b8;">&times;</span>
        <h3 style="margin-top: 0; color: #0F3C5C;"><i class="fas fa-user-plus" style="color:#10b981;"></i> Tambah Staff Baru</h3>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_staff">
            
            <div class="form-group">
                <label>Foto Staff (JPG / PNG)</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
                <small style="color:#64748b;">Disarankan foto rasio 1:1 (persegi).</small>
            </div>
            <div class="form-group">
                <label>Nama Lengkap & Gelar <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Rahmawati, S.Pd" required>
            </div>
            <div class="form-group">
                <label>Jabatan / Peran <span style="color:#ef4444;">*</span></label>
                <input type="text" name="position" class="form-control" placeholder="Contoh: Kepala Unit / Wali Kelas" required>
            </div>
            <div class="form-group">
                <label>Deskripsi / Motto Singkat</label>
                <textarea name="bio" class="form-control" rows="2" placeholder="Contoh: Pengasuhan penuh kasih & stimulasi motorik dini."></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeAddStaffModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Staff</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT STAFF -->
<div class="modal-admin-overlay" id="editStaffModal">
    <div class="modal-admin-box">
        <span onclick="closeEditStaffModal()" style="position: absolute; top: 20px; right: 20px; cursor: pointer; font-size: 1.4rem; color: #94a3b8;">&times;</span>
        <h3 style="margin-top: 0; color: #0F3C5C;"><i class="fas fa-user-edit" style="color:#00A2E9;"></i> Edit Staff</h3>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_staff">
            <input type="hidden" name="staff_id" id="edit_staff_id">
            
            <div class="form-group">
                <label>Ganti Foto (Kosongkan jika tidak ingin ganti)</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label>Nama Lengkap & Gelar <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" id="edit_staff_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Jabatan / Peran <span style="color:#ef4444;">*</span></label>
                <input type="text" name="position" id="edit_staff_pos" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Deskripsi / Motto Singkat</label>
                <textarea name="bio" id="edit_staff_bio" class="form-control" rows="2"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeEditStaffModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH FOTO GALERI -->
<div class="modal-admin-overlay" id="addGalModal">
    <div class="modal-admin-box">
        <span onclick="closeAddGalModal()" style="position: absolute; top: 20px; right: 20px; cursor: pointer; font-size: 1.4rem; color: #94a3b8;">&times;</span>
        <h3 style="margin-top: 0; color: #0F3C5C;"><i class="fas fa-plus-circle" style="color:#10b981;"></i> Tambah Foto Galeri</h3>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_gallery">
            
            <div class="form-group">
                <label>Pilih File Foto <span style="color:#ef4444;">*</span></label>
                <input type="file" name="image_file" class="form-control" accept="image/*" required>
            </div>
            <div class="form-group">
                <label>Kategori Foto <span style="color:#ef4444;">*</span></label>
                <select name="category" class="form-control" required>
                    <option value="prestasi">🏆 Prestasi</option>
                    <option value="aktivitas" selected>🎨 Aktivitas Belajar</option>
                    <option value="fasilitas">🏫 Fasilitas Unit</option>
                </select>
            </div>
            <div class="form-group">
                <label>Keterangan / Caption Foto</label>
                <input type="text" name="caption" class="form-control" placeholder="Contoh: Juara 1 Lomba Kreativitas Anak">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeAddGalModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-upload"></i> Unggah Foto</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT FOTO GALERI -->
<div class="modal-admin-overlay" id="editGalModal">
    <div class="modal-admin-box">
        <span onclick="closeEditGalModal()" style="position: absolute; top: 20px; right: 20px; cursor: pointer; font-size: 1.4rem; color: #94a3b8;">&times;</span>
        <h3 style="margin-top: 0; color: #0F3C5C;"><i class="fas fa-edit" style="color:#00A2E9;"></i> Edit / Ganti Foto Galeri</h3>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_gallery">
            <input type="hidden" name="gallery_id" id="edit_gal_id">
            
            <div class="form-group">
                <label>Ganti File Foto (Kosongkan jika hanya ingin ganti keterangan/kategori)</label>
                <input type="file" name="image_file" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label>Kategori Foto <span style="color:#ef4444;">*</span></label>
                <select name="category" id="edit_gal_cat" class="form-control" required>
                    <option value="prestasi">🏆 Prestasi</option>
                    <option value="aktivitas">🎨 Aktivitas Belajar</option>
                    <option value="fasilitas">🏫 Fasilitas Unit</option>
                </select>
            </div>
            <div class="form-group">
                <label>Keterangan / Caption Foto</label>
                <input type="text" name="caption" id="edit_gal_caption" class="form-control">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeEditGalModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
// Initialize Summernote WYSIWYG
$(document).ready(function() {
    $('#summernote_content').summernote({
        placeholder: 'Tuliskan deskripsi lengkap unit, profil, program unggulan, dan fasilitas...',
        tabsize: 2,
        height: 380,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });
});

// Modals JS
function openAddStaffModal() { document.getElementById('addStaffModal').style.display = 'flex'; }
function closeAddStaffModal() { document.getElementById('addStaffModal').style.display = 'none'; }
function openEditStaffModal(staff) {
    document.getElementById('edit_staff_id').value = staff.id;
    document.getElementById('edit_staff_name').value = staff.name;
    document.getElementById('edit_staff_pos').value = staff.position;
    document.getElementById('edit_staff_bio').value = staff.bio || '';
    document.getElementById('editStaffModal').style.display = 'flex';
}
function closeEditStaffModal() { document.getElementById('editStaffModal').style.display = 'none'; }

function openAddGalModal() { document.getElementById('addGalModal').style.display = 'flex'; }
function closeAddGalModal() { document.getElementById('addGalModal').style.display = 'none'; }
function openEditGalModal(gal) {
    document.getElementById('edit_gal_id').value = gal.id;
    document.getElementById('edit_gal_cat').value = gal.category.toLowerCase();
    document.getElementById('edit_gal_caption').value = gal.caption || '';
    document.getElementById('editGalModal').style.display = 'flex';
}
function closeEditGalModal() { document.getElementById('editGalModal').style.display = 'none'; }

function filterAdminGal(cat) {
    document.querySelectorAll('.gal-f-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('f-' + cat).classList.add('active');

    const items = document.querySelectorAll('.gal-adm-item');
    items.forEach(it => {
        const itemCat = it.getAttribute('data-cat');
        if (cat === 'all' || itemCat === cat) {
            it.style.display = 'block';
        } else {
            it.style.display = 'none';
        }
    });
}

window.onclick = function(e) {
    if (e.target.classList.contains('modal-admin-overlay')) {
        e.target.style.display = 'none';
    }
}
</script>

<?php else: ?>
<!-- ========================================================================= -->
<!-- OVERVIEW MODE: 10 EDUCATION UNITS GRID FOR SUPER ADMIN -->
<!-- ========================================================================= -->
<div style="margin-bottom: 25px;">
    <h2 style="color: #0F3C5C; margin: 0; font-size: 1.6rem; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-graduation-cap" style="color: #00A2E9;"></i> Kelola 10 Unit Pendidikan & Inklusi
    </h2>
    <p style="color: #64748b; margin: 5px 0 0 0; font-size: 0.95rem;">
        Pilih salah satu unit di bawah ini untuk mengedit <b>Tentang (WYSIWYG)</b>, <b>Staff Pengajar</b>, <b>Galeri (3 Kategori)</b>, dan <b>Kontak Sosmed</b>.
    </p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
    <?php foreach ($educationPages as $p): ?>
        <?php 
        // Count staff
        $stfCount = $pdo->prepare("SELECT COUNT(*) FROM unit_staff WHERE unit_slug = ?");
        $stfCount->execute([$p->slug]);
        $sTotal = $stfCount->fetchColumn();

        // Count gal
        $gCount = $pdo->prepare("SELECT COUNT(*) FROM page_galleries WHERE page_slug = ?");
        $gCount->execute([$p->slug]);
        $gTotal = $gCount->fetchColumn();
        ?>
        <div class="card" style="margin: 0; border-top: 4px solid #00A2E9; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                        <i class="fas fa-school"></i>
                    </div>
                    <span class="badge badge-primary">Pendidikan</span>
                </div>
                <h3 style="color: #0F3C5C; margin: 0 0 8px 0; font-size: 1.25rem;">
                    <?php echo htmlspecialchars($p->title); ?>
                </h3>
                <small style="color: #94a3b8; font-family: monospace;">/page.php?slug=<?php echo htmlspecialchars($p->slug); ?></small>

                <div style="margin-top: 15px; display: flex; gap: 8px; flex-wrap: wrap;">
                    <span class="badge badge-gray"><i class="fas fa-users"></i> <?php echo $sTotal; ?> Staff</span>
                    <span class="badge badge-gray"><i class="fas fa-images"></i> <?php echo $gTotal; ?> Foto Galeri</span>
                </div>
            </div>

            <div style="margin-top: 25px; display: flex; gap: 8px;">
                <a href="kelola_sekolah.php?edit=<?php echo $p->id; ?>" class="btn btn-primary" style="flex: 1; text-align: center; font-weight: 600;">
                    <i class="fas fa-edit"></i> Kelola Unit
                </a>
                <a href="/talenta-semarang/page.php?slug=<?php echo htmlspecialchars($p->slug); ?>" target="_blank" class="btn btn-outline" title="Lihat Tampilan User">
                    <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
