<?php
require_once 'includes/header.php';

if (!function_exists('handleImageUpload')) {
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
        $newName = 'psi_' . time() . '_' . rand(100, 999) . '.' . $ext;
        if (move_uploaded_file($tmpName, $targetDir . $newName)) {
            return $newName;
        }
        return null;
    }
}

$success = '';
$error = '';
$activeTab = $_GET['tab'] ?? 'konten'; // 'konten', 'banner', 'staff', 'galeri', 'kontak'

// Determine which service to edit
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$unitSlugParam = isset($_GET['slug']) ? trim($_GET['slug']) : '';

// If unit admin (not super), force to their own unit
if ($_SESSION['admin_type'] !== 'super') {
    $unitSlugParam = $_SESSION['admin_unit_slug'];
}

$editData = null;
if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ? AND category = 'psikologi'");
    $stmt->execute([$editId]);
    $editData = $stmt->fetch(PDO::FETCH_OBJ);
} elseif (!empty($unitSlugParam)) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND category = 'psikologi'");
    $stmt->execute([$unitSlugParam]);
    $editData = $stmt->fetch(PDO::FETCH_OBJ);
}

// Security check: unit admin cannot edit another unit
if ($editData && $_SESSION['admin_type'] !== 'super' && $editData->slug !== $_SESSION['admin_unit_slug']) {
    echo "<div class='card'><h3 style='color:#ef4444;'><i class='fas fa-lock'></i> Akses Ditolak</h3><p>Anda hanya memiliki hak akses untuk mengelola unit layanan Anda sendiri.</p><a href='kelola_psikologi.php' class='btn btn-primary'>Kembali</a></div>";
    require_once 'includes/footer.php';
    exit;
}

// =========================================================================
// POST ACTIONS: EDIT KONTEN, BANNER, FOTO UTAMA, STAFF, GALERI, KONTAK
// =========================================================================
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. TAMBAH LAYANAN BARU (SUPER ADMIN ONLY)
    if ($action === 'add_service' && $_SESSION['admin_type'] === 'super') {
        $title = trim($_POST['title'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($title)) {
            $error = "Nama layanan wajib diisi!";
        } else {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
            $chk = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
            $chk->execute([$slug]);
            if ($chk->fetch()) {
                $slug .= '-' . time();
            }

            // Copy template from pusat-psikologi-terapan
            $tStmt = $pdo->prepare("SELECT content_html, featured_image, banner_image FROM pages WHERE slug = 'pusat-psikologi-terapan'");
            $tStmt->execute();
            $tpl = $tStmt->fetch(PDO::FETCH_ASSOC);

            $ins = $pdo->prepare("INSERT INTO pages (slug, title, category, content_html, featured_image, banner_image) VALUES (?, ?, 'psikologi', ?, ?, ?)");
            $ins->execute([
                $slug,
                $title,
                $tpl['content_html'] ?? '',
                $tpl['featured_image'] ?? 'psikologi_1.png',
                $tpl['banner_image'] ?? 'pusat_psikologi_banner.png'
            ]);
            $newId = $pdo->lastInsertId();

            // Seed 3 default galleries
            $pdo->prepare("INSERT INTO page_galleries (page_slug, image_file, category, caption, sort_order) VALUES 
                (?, 'psikologi_2.png', 'aktivitas', 'Seminar & Edukasi Parenting Talenta', 1),
                (?, 'psikologi_3.png', 'fasilitas', 'Peserta Konsultasi & Intervensi Tumbuh Kembang', 2),
                (?, 'psikologi_4.png', 'prestasi', 'Dokumentasi Bersama Tim Psikolog & Peserta', 3)")->execute([$slug, $slug, $slug]);

            // Seed default contacts
            $pdo->prepare("INSERT INTO unit_contacts (unit_slug, whatsapp, instagram, email, facebook, tiktok, youtube, address) VALUES 
                (?, '0817-838-939', '@talentasemarang', ?, 'Layanan Psikologi Talenta Semarang', '@talentasemarang', 'Sekolah Talenta Semarang', 'Jl. Puspowarno Tengah IX No.6, Semarang Barat')")
                ->execute([$slug, !empty($email) ? $email : $slug . '@talentasemarang.com']);

            // Add Admin User if provided
            if (!empty($email) && !empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO admin_users (username, email, password, password_hash, name, unit, unit_slug, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'psikologi', 'Aktif')")
                    ->execute([$slug, $email, $password, $hash, 'Admin ' . $title, $title, $slug]);
            }

            $success = "Layanan Psikologi baru '$title' berhasil ditambahkan!";
            header("Location: kelola_psikologi.php?edit=$newId&tab=konten");
            exit;
        }
    }

    // 2. SAVE KONTEN WYSIWYG
    elseif ($action === 'save_content' && $editData) {
        $title = trim($_POST['title'] ?? $editData->title);
        $content_html = $_POST['content_html'] ?? '';

        $upd = $pdo->prepare("UPDATE pages SET title = ?, content_html = ? WHERE id = ?");
        $upd->execute([$title, $content_html, $editData->id]);
        $success = "Konten layanan '$title' berhasil diperbarui!";
        $editData->title = $title;
        $editData->content_html = $content_html;
        $activeTab = 'konten';
    }

    // 3. GANTI BANNER HALAMAN
    elseif ($action === 'update_banner' && $editData) {
        $newBanner = handleImageUpload('banner_file');
        if ($newBanner) {
            $upd = $pdo->prepare("UPDATE pages SET banner_image = ? WHERE id = ?");
            $upd->execute([$newBanner, $editData->id]);
            $editData->banner_image = $newBanner;
            $success = "Banner halaman berhasil diganti dengan gambar baru!";
        } else {
            $error = "Pilih file gambar banner yang valid (JPG, PNG, WebP)!";
        }
        $activeTab = 'banner';
    }

    // 4. GANTI FOTO UTAMA LAYANAN (TIM / PROFIL)
    elseif ($action === 'update_featured' && $editData) {
        $newFeatured = handleImageUpload('featured_file');
        if ($newFeatured) {
            $upd = $pdo->prepare("UPDATE pages SET featured_image = ? WHERE id = ?");
            $upd->execute([$newFeatured, $editData->id]);
            $editData->featured_image = $newFeatured;
            $success = "Foto utama layanan berhasil diganti dengan foto baru!";
        } else {
            $error = "Pilih file gambar foto utama yang valid (JPG, PNG, WebP)!";
        }
        $activeTab = 'banner';
    }

    // 5. TAMBAH STAFF / TERAPIS
    elseif ($action === 'add_staff' && $editData) {
        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $photoName = handleImageUpload('photo');
        if (!$photoName) {
            $photoName = 'guru_1.png';
        }

        if (empty($name) || empty($position)) {
            $error = "Nama dan jabatan staf/terapis wajib diisi!";
        } else {
            $insStaff = $pdo->prepare("INSERT INTO unit_staff (unit_slug, name, position, photo, bio) VALUES (?, ?, ?, ?, ?)");
            $insStaff->execute([$editData->slug, $name, $position, $photoName, $bio]);
            $success = "Staff/Terapis baru '$name' berhasil ditambahkan!";
        }
        $activeTab = 'staff';
    }

    // 6. EDIT STAFF / TERAPIS
    elseif ($action === 'edit_staff' && $editData) {
        $staffId = (int)$_POST['staff_id'];
        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        $newPhoto = handleImageUpload('photo');
        if ($newPhoto) {
            $upd = $pdo->prepare("UPDATE unit_staff SET name = ?, position = ?, photo = ?, bio = ? WHERE id = ? AND unit_slug = ?");
            $upd->execute([$name, $position, $newPhoto, $bio, $staffId, $editData->slug]);
        } else {
            $upd = $pdo->prepare("UPDATE unit_staff SET name = ?, position = ?, bio = ? WHERE id = ? AND unit_slug = ?");
            $upd->execute([$name, $position, $bio, $staffId, $editData->slug]);
        }
        $success = "Data staf/terapis '$name' berhasil diperbarui!";
        $activeTab = 'staff';
    }

    // 7. TAMBAH FOTO GALERI
    elseif ($action === 'add_gallery' && $editData) {
        $caption = trim($_POST['caption'] ?? '');
        $category = strtolower(trim($_POST['category'] ?? 'aktivitas'));
        $newImg = handleImageUpload('gallery_file');
        if ($newImg) {
            $ins = $pdo->prepare("INSERT INTO page_galleries (page_slug, image_file, category, caption) VALUES (?, ?, ?, ?)");
            $ins->execute([$editData->slug, $newImg, $category, $caption]);
            $success = "Foto dokumentasi baru berhasil ditambahkan ke galeri!";
        } else {
            $error = "Pilih file foto yang valid (JPG, PNG, WebP)!";
        }
        $activeTab = 'galeri';
    }

    // 8. EDIT / GANTI FOTO GALERI
    elseif ($action === 'edit_gallery' && $editData) {
        $galId = (int)$_POST['gallery_id'];
        $caption = trim($_POST['caption'] ?? '');
        $category = strtolower(trim($_POST['category'] ?? 'aktivitas'));
        $replacementImg = handleImageUpload('gallery_file');

        if ($replacementImg) {
            $upd = $pdo->prepare("UPDATE page_galleries SET image_file = ?, category = ?, caption = ? WHERE id = ? AND page_slug = ?");
            $upd->execute([$replacementImg, $category, $caption, $galId, $editData->slug]);
        } else {
            $upd = $pdo->prepare("UPDATE page_galleries SET category = ?, caption = ? WHERE id = ? AND page_slug = ?");
            $upd->execute([$category, $caption, $galId, $editData->slug]);
        }
        $success = "Data foto galeri berhasil diperbarui!";
        $activeTab = 'galeri';
    }

    // 9. SIMPAN KONTAK & SOSMED
    elseif ($action === 'save_contacts' && $editData) {
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
        $insC->execute([$editData->slug, $wa, $ig, $email, $fb, $tiktok, $yt, $addr]);
        $success = "Kontak resmi unit {$editData->title} berhasil diperbarui!";
        $activeTab = 'kontak';
    }
}

// GET ACTIONS: DELETE SERVICE, DELETE STAFF, OR DELETE GALLERY
if (isset($_GET['delete_service']) && $_SESSION['admin_type'] === 'super') {
    $delId = (int)$_GET['delete_service'];
    $sStmt = $pdo->prepare("SELECT slug, title FROM pages WHERE id = ? AND category = 'psikologi'");
    $sStmt->execute([$delId]);
    $srv = $sStmt->fetch(PDO::FETCH_OBJ);

    if ($srv) {
        $pdo->prepare("DELETE FROM pages WHERE id = ?")->execute([$delId]);
        $pdo->prepare("DELETE FROM page_galleries WHERE page_slug = ?")->execute([$srv->slug]);
        $pdo->prepare("DELETE FROM unit_staff WHERE unit_slug = ?")->execute([$srv->slug]);
        $pdo->prepare("DELETE FROM unit_contacts WHERE unit_slug = ?")->execute([$srv->slug]);
        $pdo->prepare("DELETE FROM admin_users WHERE unit_slug = ? AND role = 'psikologi'")->execute([$srv->slug]);
        $success = "Layanan '{$srv->title}' berhasil dihapus.";
    }
}

if ($editData && isset($_GET['delete_staff'])) {
    $delId = (int)$_GET['delete_staff'];
    $pdo->prepare("DELETE FROM unit_staff WHERE id = ? AND unit_slug = ?")->execute([$delId, $editData->slug]);
    $success = "Staf/Terapis berhasil dihapus.";
    $activeTab = 'staff';
}

if ($editData && isset($_GET['delete_gallery'])) {
    $galId = (int)$_GET['delete_gallery'];
    $pdo->prepare("DELETE FROM page_galleries WHERE id = ? AND page_slug = ?")->execute([$galId, $editData->slug]);
    $success = "Foto galeri berhasil dihapus.";
    $activeTab = 'galeri';
}

// Fetch related data if in edit mode
$staffList = [];
$galleryList = [];
$contacts = null;

if ($editData) {
    $sStmt = $pdo->prepare("SELECT * FROM unit_staff WHERE unit_slug = ? ORDER BY sort_order ASC, id ASC");
    $sStmt->execute([$editData->slug]);
    $staffList = $sStmt->fetchAll(PDO::FETCH_ASSOC);

    $gStmt = $pdo->prepare("SELECT * FROM page_galleries WHERE page_slug = ? ORDER BY sort_order ASC, id DESC");
    $gStmt->execute([$editData->slug]);
    $galleryList = $gStmt->fetchAll(PDO::FETCH_ASSOC);

    $cStmt = $pdo->prepare("SELECT * FROM unit_contacts WHERE unit_slug = ?");
    $cStmt->execute([$editData->slug]);
    $contacts = $cStmt->fetch(PDO::FETCH_ASSOC);
} else {
    // Overview mode: Fetch all 10 Psikologi services
    $stmt = $pdo->query("SELECT p.*, LENGTH(p.content_html) as content_len FROM pages p WHERE p.category = 'psikologi' ORDER BY p.id ASC");
    $psiPages = $stmt->fetchAll(PDO::FETCH_OBJ);
}
?>

<!-- INCLUDE SUMMERNOTE WYSIWYG ASSETS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<style>
/* PSIKOLOGI TABS */
.psi-editor-nav {
    display: flex;
    gap: 10px;
    background: #f8fafc;
    padding: 10px 15px;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    margin-bottom: 25px;
    flex-wrap: wrap;
}
.psi-tab-btn {
    padding: 10px 18px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.92rem;
    color: #64748b;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: transparent;
}
.psi-tab-btn:hover {
    color: #7c3aed;
    background: #ffffff;
}
.psi-tab-btn.active {
    background: #7c3aed;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(124, 58, 237, 0.25);
}
.psi-tab-count {
    background: rgba(255,255,255,0.25);
    color: #fff;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 0.75rem;
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
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
}
.staff-admin-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 22px 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: transform 0.2s ease;
}
.staff-admin-card:hover {
    transform: translateY(-3px);
}
.staff-admin-card img {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 12px;
    border: 3px solid #ede9fe;
}
.gallery-admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
}
.gallery-admin-card {
    background: #ffffff;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    position: relative;
    transition: transform 0.2s ease;
}
.gallery-admin-card:hover {
    transform: translateY(-3px);
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
    max-height: 90vh;
    overflow-y: auto;
}
</style>

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

<?php if ($editData): ?>
<!-- ========================================================================= -->
<!-- EDIT MODE: 5 TABS SESUAI STRUKTUR HALAMAN USER -->
<!-- ========================================================================= -->
<div class="editor-header-bar">
    <div>
        <div style="font-size: 0.85rem; color: #7c3aed; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
            <i class="fas fa-brain"></i> Layanan Psikologi & Terapi
        </div>
        <h2 style="color: #0F3C5C; margin: 4px 0 0 0; font-size: 1.7rem;">
            Kelola Layanan: <?php echo htmlspecialchars($editData->title); ?>
        </h2>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="/outsource_talenta_landing/magangtalenta/page.php?slug=<?php echo htmlspecialchars($editData->slug); ?>" target="_blank" class="btn btn-outline" style="font-weight: 600;">
            <i class="fas fa-external-link-alt"></i> Pratinjau Tampilan User
        </a>
        <?php if ($_SESSION['admin_type'] === 'super'): ?>
            <a href="kelola_psikologi.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Semua Layanan Psikologi
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- TABS NAVIGATION: KONTEN, BANNER, STAFF, GALERI, KONTAK -->
<div class="psi-editor-nav">
    <a href="kelola_psikologi.php?edit=<?php echo $editData->id; ?>&tab=konten" class="psi-tab-btn <?php echo $activeTab === 'konten' ? 'active' : ''; ?>">
        <i class="fas fa-file-alt"></i> 1. Konten Tentang (WYSIWYG)
    </a>
    <a href="kelola_psikologi.php?edit=<?php echo $editData->id; ?>&tab=banner" class="psi-tab-btn <?php echo $activeTab === 'banner' ? 'active' : ''; ?>">
        <i class="fas fa-image"></i> 2. Banner & Foto Layanan
    </a>
    <a href="kelola_psikologi.php?edit=<?php echo $editData->id; ?>&tab=staff" class="psi-tab-btn <?php echo $activeTab === 'staff' ? 'active' : ''; ?>">
        <i class="fas fa-user-md"></i> 3. Staff & Terapis <span class="psi-tab-count"><?php echo count($staffList); ?></span>
    </a>
    <a href="kelola_psikologi.php?edit=<?php echo $editData->id; ?>&tab=galeri" class="psi-tab-btn <?php echo $activeTab === 'galeri' ? 'active' : ''; ?>">
        <i class="fas fa-images"></i> 4. Galeri Dokumentasi <span class="psi-tab-count"><?php echo count($galleryList); ?></span>
    </a>
    <a href="kelola_psikologi.php?edit=<?php echo $editData->id; ?>&tab=kontak" class="psi-tab-btn <?php echo $activeTab === 'kontak' ? 'active' : ''; ?>">
        <i class="fas fa-address-book"></i> 5. Kontak & Sosmed
    </a>
</div>

<!-- ========================================================================= -->
<!-- TAB 1: KONTEN TEKS (WYSIWYG SUMMERNOTE) -->
<!-- ========================================================================= -->
<?php if ($activeTab === 'konten'): ?>
<div class="card" style="border-left: 5px solid #7c3aed;">
    <div style="margin-bottom: 20px;">
        <h3 style="color: #0F3C5C; margin: 0;"><i class="fas fa-edit" style="color: #7c3aed;"></i> Edit Konten Tentang Layanan (WYSIWYG)</h3>
        <p style="color: #64748b; font-size: 0.9rem; margin: 4px 0 0 0;">
            Gunakan editor visual di bawah ini untuk mengubah deskripsi profil, tujuan layanan, dan poin-poin penanganan secara fleksibel.
        </p>
    </div>

    <form method="POST">
        <input type="hidden" name="action" value="save_content">
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label style="font-weight: 700; color: #0F3C5C;">Nama Layanan / Judul Halaman <span style="color:#ef4444;">*</span></label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($editData->title); ?>" required style="font-size: 1.1rem; padding: 10px 14px;">
        </div>

        <div class="form-group">
            <label style="font-weight: 700; color: #0F3C5C; margin-bottom: 8px; display: block;">Isi Informasi & Deskripsi Layanan</label>
            <textarea id="summernote_content" name="content_html" required><?php echo htmlspecialchars($editData->content_html); ?></textarea>
        </div>

        <div style="margin-top: 25px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="background: #7c3aed; border-color: #7c3aed; padding: 12px 25px; font-weight: 600;">
                <i class="fas fa-save"></i> Simpan Perubahan Teks
            </button>
            <a href="/outsource_talenta_landing/magangtalenta/page.php?slug=<?php echo htmlspecialchars($editData->slug); ?>" target="_blank" class="btn btn-outline" style="padding: 12px 20px;">
                <i class="fas fa-eye"></i> Lihat Hasil di Halaman User
            </a>
        </div>
    </form>
</div>

<!-- ========================================================================= -->
<!-- TAB 2: BANNER & FOTO UTAMA LAYANAN -->
<!-- ========================================================================= -->
<?php elseif ($activeTab === 'banner'): ?>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
    <!-- 1. GANTI BANNER HALAMAN -->
    <div class="card" style="border-top: 4px solid #7c3aed; margin: 0;">
        <h3 style="color: #0F3C5C; margin-top: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-panorama" style="color: #7c3aed;"></i> Banner Atas Halaman
        </h3>
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 15px;">
            Gambar banner lebar yang tampil di bagian paling atas halaman layanan pengguna.
        </p>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 20px;">
            <img src="../assets/images/<?php echo htmlspecialchars($editData->banner_image ?: 'pusat_psikologi_banner.png'); ?>?v=<?php echo time(); ?>" style="width: 100%; height: auto; display: block; max-height: 200px; object-fit: cover;">
            <div style="padding: 10px 14px; font-size: 0.85rem; color: #64748b; background: #fff;">
                <b>File saat ini:</b> <code><?php echo htmlspecialchars($editData->banner_image ?: 'pusat_psikologi_banner.png'); ?></code>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_banner">
            <div class="form-group">
                <label style="font-weight: 600;">Unggah Banner Baru (JPG, PNG, WebP)</label>
                <input type="file" name="banner_file" class="form-control" accept="image/*" required>
                <small style="color: #64748b;">Rekomendasi ukuran: 1920x450 piksel (landscape lebar).</small>
            </div>
            <button type="submit" class="btn btn-primary" style="background: #7c3aed; border-color: #7c3aed; margin-top: 10px; width: 100%;">
                <i class="fas fa-upload"></i> Ganti Banner Halaman
            </button>
        </form>
    </div>

    <!-- 2. GANTI FOTO UTAMA LAYANAN (TIM / PROFIL) -->
    <div class="card" style="border-top: 4px solid #00A2E9; margin: 0;">
        <h3 style="color: #0F3C5C; margin-top: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-user-friends" style="color: #00A2E9;"></i> Foto Utama Layanan
        </h3>
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 15px;">
            Foto utama tim/layanan yang tampil berdampingan dengan judul teks pada halaman user.
        </p>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 20px;">
            <img src="../assets/images/<?php echo htmlspecialchars($editData->featured_image ?: 'psikologi_1.png'); ?>?v=<?php echo time(); ?>" style="width: 100%; height: auto; display: block; max-height: 200px; object-fit: cover;">
            <div style="padding: 10px 14px; font-size: 0.85rem; color: #64748b; background: #fff;">
                <b>File saat ini:</b> <code><?php echo htmlspecialchars($editData->featured_image ?: 'psikologi_1.png'); ?></code>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_featured">
            <div class="form-group">
                <label style="font-weight: 600;">Unggah Foto Utama Baru (JPG, PNG, WebP)</label>
                <input type="file" name="featured_file" class="form-control" accept="image/*" required>
                <small style="color: #64748b;">Rekomendasi rasio 4:3 atau 16:9.</small>
            </div>
            <button type="submit" class="btn btn-primary" style="background: #0070ad; border-color: #0070ad; margin-top: 10px; width: 100%;">
                <i class="fas fa-upload"></i> Ganti Foto Utama
            </button>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TAB 3: STAFF & TERAPIS -->
<!-- ========================================================================= -->
<?php elseif ($activeTab === 'staff'): ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h3 style="color: #0F3C5C; margin: 0;"><i class="fas fa-user-md" style="color: #7c3aed;"></i> Tim Psikolog & Terapis Layanan</h3>
        <p style="color: #64748b; font-size: 0.9rem; margin: 4px 0 0 0;">
            Kelola data nama dokter, psikolog, konselor, terapis, foto profil, dan keahlian/bio singkat yang tampil pada halaman user.
        </p>
    </div>
    <button onclick="openAddStaffModal()" class="btn btn-success" style="box-shadow: 0 4px 12px rgba(16,185,129,0.3);">
        <i class="fas fa-user-plus"></i> + Tambah Staff/Terapis Baru
    </button>
</div>

<div class="staff-admin-grid">
    <?php if (empty($staffList)): ?>
        <div class="card" style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 40px;">
            <i class="fas fa-user-slash" style="font-size: 3rem; margin-bottom: 10px;"></i>
            <p>Belum ada psikolog/terapis yang ditambahkan untuk layanan ini.</p>
        </div>
    <?php else: ?>
        <?php foreach ($staffList as $st): ?>
        <div class="staff-admin-card">
            <img src="../assets/images/<?php echo htmlspecialchars($st['photo']); ?>" alt="<?php echo htmlspecialchars($st['name']); ?>">
            <h4 style="margin: 0 0 4px 0; color: #0F3C5C; font-size: 1.05rem;"><?php echo htmlspecialchars($st['name']); ?></h4>
            <span class="badge badge-purple" style="margin-bottom: 10px;"><?php echo htmlspecialchars($st['position']); ?></span>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0 0 15px 0; line-height: 1.5;">
                <?php echo htmlspecialchars($st['bio'] ?? '-'); ?>
            </p>
            <div style="display: flex; gap: 8px; width: 100%; justify-content: center; margin-top: auto;">
                <button onclick='openEditStaffModal(<?php echo json_encode($st); ?>)' class="btn btn-sm btn-primary" style="background:#7c3aed; border-color:#7c3aed;">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <a href="kelola_psikologi.php?edit=<?php echo $editData->id; ?>&tab=staff&delete_staff=<?php echo $st['id']; ?>" onclick="return confirm('Hapus staf/terapis <?php echo htmlspecialchars($st['name']); ?>?')" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i> Hapus
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- TAB 4: GALERI FOTO DOKUMENTASI -->
<!-- ========================================================================= -->
<?php elseif ($activeTab === 'galeri'): ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h3 style="color: #0F3C5C; margin: 0;"><i class="fas fa-images" style="color: #7c3aed;"></i> Galeri Dokumentasi Layanan</h3>
        <p style="color: #64748b; font-size: 0.9rem; margin: 4px 0 0 0;">
            Kelola foto-foto dokumentasi kegiatan seminar, konsultasi, terapi, atau pelatihan untuk layanan ini.
        </p>
    </div>
    <button onclick="openAddGalModal()" class="btn btn-success" style="box-shadow: 0 4px 12px rgba(16,185,129,0.3);">
        <i class="fas fa-plus-circle"></i> + Tambah Foto Galeri
    </button>
</div>

<div class="gallery-admin-grid">
    <?php if (empty($galleryList)): ?>
        <div class="card" style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 40px;">
            <i class="fas fa-images" style="font-size: 3rem; margin-bottom: 10px;"></i>
            <p>Belum ada foto galeri dokumentasi untuk layanan ini.</p>
        </div>
    <?php else: ?>
        <?php foreach ($galleryList as $gal): ?>
            <div class="gallery-admin-card">
                <img src="../assets/images/<?php echo htmlspecialchars($gal['image_file']); ?>?v=<?php echo time(); ?>" alt="Dokumentasi">
                <div style="padding: 14px;">
                    <p style="font-size: 0.95rem; color: #334155; margin: 0 0 12px 0; font-weight: 600; min-height: 40px; line-height: 1.4;">
                        <?php echo htmlspecialchars($gal['caption'] ?: 'Tanpa Keterangan'); ?>
                    </p>
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <button onclick='openEditGalModal(<?php echo json_encode($gal); ?>)' class="btn btn-sm btn-primary" style="background: #7c3aed; border-color: #7c3aed;">
                            <i class="fas fa-edit"></i> Edit / Ganti
                        </button>
                        <a href="kelola_psikologi.php?edit=<?php echo $editData->id; ?>&tab=galeri&delete_gallery=<?php echo $gal['id']; ?>" onclick="return confirm('Hapus foto ini dari galeri?')" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- TAB 5: KONTAK & MEDIA SOSIAL -->
<!-- ========================================================================= -->
<?php elseif ($activeTab === 'kontak'): ?>
<div class="card" style="border-left: 5px solid #10b981;">
    <div style="margin-bottom: 20px;">
        <h3 style="color: #0F3C5C; margin: 0;"><i class="fas fa-address-book" style="color: #10b981;"></i> Kontak Resmi & Media Sosial Layanan</h3>
        <p style="color: #64748b; font-size: 0.9rem; margin: 4px 0 0 0;">
            Data di bawah ini langsung terhubung dengan kotak informasi kontak yang tampil di halaman user.
        </p>
    </div>

    <form method="POST">
        <input type="hidden" name="action" value="save_contacts">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label><i class="fab fa-whatsapp" style="color:#25D366;"></i> Nomor WhatsApp Resmi</label>
                <input type="text" name="whatsapp" class="form-control" value="<?php echo htmlspecialchars($contacts['whatsapp'] ?? '0817-838-939'); ?>" required>
                <small style="color: #64748b;">Contoh: 0817-838-939</small>
            </div>
            <div class="form-group">
                <label><i class="fab fa-instagram" style="color:#e1306c;"></i> Instagram Layanan</label>
                <input type="text" name="instagram" class="form-control" value="<?php echo htmlspecialchars($contacts['instagram'] ?? '@talentasemarang'); ?>" required>
                <small style="color: #64748b;">Contoh: @talentasemarang</small>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label><i class="fas fa-envelope" style="color:#EA4335;"></i> Alamat Email Resmi</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($contacts['email'] ?? $editData->slug . '@talentasemarang.com'); ?>" required>
            </div>
            <div class="form-group">
                <label><i class="fab fa-facebook-f" style="color:#1877F2;"></i> Facebook</label>
                <input type="text" name="facebook" class="form-control" value="<?php echo htmlspecialchars($contacts['facebook'] ?? 'Layanan Psikologi Talenta Semarang'); ?>">
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
            <label><i class="fas fa-map-marker-alt" style="color:#ef4444;"></i> Alamat / Lokasi Pelayanan</label>
            <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($contacts['address'] ?? 'Jl. Puspowarno Tengah IX No.6, Semarang Barat'); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-weight: 600; margin-top: 10px; background: #7c3aed; border-color: #7c3aed;">
            <i class="fas fa-save"></i> Simpan Kontak Layanan
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
        <h3 style="margin-top: 0; color: #0F3C5C;"><i class="fas fa-user-plus" style="color:#10b981;"></i> Tambah Staff/Terapis Baru</h3>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_staff">
            
            <div class="form-group">
                <label>Foto Staff / Terapis (JPG / PNG)</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
                <small style="color:#64748b;">Disarankan foto rasio 1:1 (persegi).</small>
            </div>
            <div class="form-group">
                <label>Nama Lengkap & Gelar <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: dr. Maya Indrawati, Sp.KFR" required>
            </div>
            <div class="form-group">
                <label>Jabatan / Peran Klinis <span style="color:#ef4444;">*</span></label>
                <input type="text" name="position" class="form-control" placeholder="Contoh: Konsultan Medis & Terapis Anak" required>
            </div>
            <div class="form-group">
                <label>Bio / Keterangan Keahlian</label>
                <textarea name="bio" class="form-control" rows="2" placeholder="Contoh: Spesialisasi intervensi dini dan stimulasi sensori motorik."></textarea>
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
        <h3 style="margin-top: 0; color: #0F3C5C;"><i class="fas fa-user-edit" style="color:#7c3aed;"></i> Edit Data Staff/Terapis</h3>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_staff">
            <input type="hidden" name="staff_id" id="edit_staff_id">
            
            <div class="form-group">
                <label>Ganti Foto (Kosongkan bila tidak ingin mengganti)</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label>Nama Lengkap & Gelar <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" id="edit_staff_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Jabatan / Peran Klinis <span style="color:#ef4444;">*</span></label>
                <input type="text" name="position" id="edit_staff_pos" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Bio / Keterangan Keahlian</label>
                <textarea name="bio" id="edit_staff_bio" class="form-control" rows="2"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeEditStaffModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#7c3aed; border-color:#7c3aed;"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH FOTO GALERI -->
<div class="modal-admin-overlay" id="addGalModal">
    <div class="modal-admin-box">
        <span onclick="closeAddGalModal()" style="position: absolute; top: 20px; right: 20px; cursor: pointer; font-size: 1.4rem; color: #94a3b8;">&times;</span>
        <h3 style="margin-top: 0; color: #0F3C5C;"><i class="fas fa-upload" style="color:#7c3aed;"></i> Tambah Foto Galeri Dokumentasi</h3>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_gallery">
            
            <div class="form-group">
                <label>Pilih File Foto (JPG / PNG / WebP) <span style="color:#ef4444;">*</span></label>
                <input type="file" name="gallery_file" class="form-control" accept="image/*" required>
            </div>
            <input type="hidden" name="category" value="aktivitas">
            <div class="form-group">
                <label>Keterangan / Caption Foto</label>
                <input type="text" name="caption" class="form-control" placeholder="Contoh: Sesi Evaluasi & Konsultasi Perkembangan Anak">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeAddGalModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#7c3aed; border-color:#7c3aed;"><i class="fas fa-upload"></i> Unggah Foto</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT / GANTI FOTO GALERI -->
<div class="modal-admin-overlay" id="editGalModal">
    <div class="modal-admin-box">
        <span onclick="closeEditGalModal()" style="position: absolute; top: 20px; right: 20px; cursor: pointer; font-size: 1.4rem; color: #94a3b8;">&times;</span>
        <h3 style="margin-top: 0; color: #0F3C5C;"><i class="fas fa-edit" style="color:#7c3aed;"></i> Edit Foto Galeri</h3>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_gallery">
            <input type="hidden" name="gallery_id" id="edit_gal_id">
            
            <div class="form-group">
                <label>Ganti File Foto (Kosongkan jika hanya ingin ganti keterangan)</label>
                <input type="file" name="gallery_file" class="form-control" accept="image/*">
            </div>
            <input type="hidden" name="category" id="edit_gal_category" value="aktivitas">
            <div class="form-group">
                <label>Keterangan / Caption Foto</label>
                <input type="text" name="caption" id="edit_gal_caption" class="form-control">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeEditGalModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#7c3aed; border-color:#7c3aed;"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
// Initialize Summernote WYSIWYG
$(document).ready(function() {
    $('#summernote_content').summernote({
        placeholder: 'Tuliskan deskripsi lengkap profil psikologi, program unggulan, dan fasilitas...',
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
    document.getElementById('edit_gal_caption').value = gal.caption || '';
    if (document.getElementById('edit_gal_category')) {
        document.getElementById('edit_gal_category').value = gal.category || 'aktivitas';
    }
    document.getElementById('editGalModal').style.display = 'flex';
}
function closeEditGalModal() { document.getElementById('editGalModal').style.display = 'none'; }

window.onclick = function(e) {
    if (e.target.classList.contains('modal-admin-overlay')) {
        e.target.style.display = 'none';
    }
}
</script>

<?php else: ?>
<!-- ========================================================================= -->
<!-- OVERVIEW MODE: GRID 10 KARTU LAYANAN PSIKOLOGI UNTUK ADMIN -->
<!-- ========================================================================= -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="color: #0F3C5C; margin: 0; font-size: 1.6rem; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-brain" style="color: #8b5cf6;"></i> Kelola 10 Unit Layanan Psikologi & Terapi
        </h2>
        <p style="color: #64748b; margin: 5px 0 0 0; font-size: 0.95rem;">
            Pilih salah satu layanan untuk mengubah <b>Konten Tentang</b>, <b>Banner Halaman</b>, <b>Staff & Terapis</b>, <b>Galeri Dokumentasi</b>, dan <b>Kontak</b>.
        </p>
    </div>
    <?php if ($_SESSION['admin_type'] === 'super'): ?>
        <button onclick="openAddServiceModal()" class="btn btn-success" style="box-shadow: 0 4px 10px rgba(16,185,129,0.3);">
            <i class="fas fa-plus-circle"></i> Tambah Layanan Baru
        </button>
    <?php endif; ?>
</div>

<!-- SEARCH BAR -->
<div class="card" style="padding: 15px 20px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
    <i class="fas fa-search" style="color: #94a3b8;"></i>
    <input type="text" id="psiSearchInput" onkeyup="filterPsiUnits()" class="form-control" placeholder="Cari nama layanan psikologi..." style="margin: 0; padding: 8px 12px; border: 1px solid #e2e8f0;">
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
    <?php foreach ($psiPages as $p): ?>
        <?php 
        // Count gallery
        $gCount = $pdo->prepare("SELECT COUNT(*) FROM page_galleries WHERE page_slug = ?");
        $gCount->execute([$p->slug]);
        $gTotal = $gCount->fetchColumn();

        // Count staff
        $sCount = $pdo->prepare("SELECT COUNT(*) FROM unit_staff WHERE unit_slug = ?");
        $sCount->execute([$p->slug]);
        $sTotal = $sCount->fetchColumn();

        // Get admin
        $aStmt = $pdo->prepare("SELECT name, email FROM admin_users WHERE unit_slug = ?");
        $aStmt->execute([$p->slug]);
        $adm = $aStmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="card psi-card-item" data-text="<?php echo strtolower(htmlspecialchars($p->title . ' ' . $p->slug)); ?>" style="margin: 0; border-top: 4px solid #8b5cf6; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: #ede9fe; color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                        <i class="fas fa-brain"></i>
                    </div>
                    <span class="badge badge-purple">Psikologi</span>
                </div>
                <h3 style="color: #0F3C5C; margin: 0 0 6px 0; font-size: 1.25rem;">
                    <?php echo htmlspecialchars($p->title); ?>
                </h3>
                <small style="color: #94a3b8; font-family: monospace;">/page.php?slug=<?php echo htmlspecialchars($p->slug); ?></small>

                <div style="margin-top: 15px; font-size: 0.85rem; color: #475569;">
                    <div style="margin-bottom: 4px;">
                        <i class="fas fa-user-shield" style="color: #8b5cf6;"></i> <b>Admin:</b> <?php echo htmlspecialchars($adm['name'] ?? 'Belum Ditugaskan'); ?>
                    </div>
                    <div style="color: #64748b;">
                        <i class="fas fa-envelope" style="color: #ea4335;"></i> <?php echo htmlspecialchars($adm['email'] ?? '-'); ?>
                    </div>
                </div>

                <div style="margin-top: 15px; display: flex; gap: 8px; flex-wrap: wrap;">
                    <span class="badge badge-purple"><i class="fas fa-user-md"></i> <?php echo $sTotal; ?> Terapis</span>
                    <span class="badge badge-gray"><i class="fas fa-images"></i> <?php echo $gTotal; ?> Foto Galeri</span>
                    <?php if ($p->content_len > 100): ?>
                        <span class="badge badge-success"><i class="fas fa-check"></i> Konten Aktif</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Belum Diisi</span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-top: 25px; display: flex; gap: 8px;">
                <a href="kelola_psikologi.php?edit=<?php echo $p->id; ?>" class="btn btn-primary" style="flex: 1; text-align: center; font-weight: 600; background: #7c3aed; border-color: #7c3aed;">
                    <i class="fas fa-edit"></i> Kelola Layanan
                </a>
                <a href="/outsource_talenta_landing/magangtalenta/page.php?slug=<?php echo htmlspecialchars($p->slug); ?>" target="_blank" class="btn btn-outline" title="Lihat di Halaman User">
                    <i class="fas fa-external-link-alt"></i>
                </a>
                <?php if ($_SESSION['admin_type'] === 'super'): ?>
                    <a href="kelola_psikologi.php?delete_service=<?php echo $p->id; ?>" onclick="return confirm('Hapus layanan <?php echo htmlspecialchars($p->title); ?> beserta staf, kontak, galeri, dan akun adminnya?')" class="btn btn-outline" style="color: #ef4444; border-color: #fca5a5;" title="Hapus Layanan">
                        <i class="fas fa-trash"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- MODAL TAMBAH LAYANAN BARU -->
<div class="modal-admin-overlay" id="addServiceModal">
    <div class="modal-admin-box">
        <span onclick="closeAddServiceModal()" style="position: absolute; top: 20px; right: 20px; cursor: pointer; font-size: 1.4rem; color: #94a3b8;">&times;</span>
        <h3 style="margin-top: 0; color: #0F3C5C;"><i class="fas fa-plus-circle" style="color:#10b981;"></i> Tambah Layanan Psikologi Baru</h3>
        
        <form method="POST">
            <input type="hidden" name="action" value="add_service">
            
            <div class="form-group">
                <label>Nama Layanan Psikologi Baru <span style="color:#ef4444;">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="Contoh: Hipnoterapi / Bimbingan Minat Bakat" required>
            </div>

            <div style="background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 15px;">
                <div style="font-weight: 600; font-size: 0.9rem; color: #0F3C5C; margin-bottom: 8px;">
                    <i class="fas fa-user-lock"></i> Kredensial Login Admin Layanan Baru (Opsional)
                </div>
                <div class="form-group" style="margin-bottom: 10px;">
                    <label style="font-size: 0.85rem;">Email Admin</label>
                    <input type="email" name="email" class="form-control" placeholder="contoh: layanan@talentasemarang.com">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.85rem;">Kata Sandi</label>
                    <input type="text" name="password" class="form-control" placeholder="contoh: layanan123">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeAddServiceModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Buat Layanan Baru</button>
            </div>
        </form>
    </div>
</div>

<script>
function filterPsiUnits() {
    const q = document.getElementById('psiSearchInput').value.toLowerCase();
    const items = document.querySelectorAll('.psi-card-item');
    items.forEach(it => {
        const text = it.getAttribute('data-text');
        it.style.display = text.includes(q) ? 'flex' : 'none';
    });
}

function openAddServiceModal() { document.getElementById('addServiceModal').style.display = 'flex'; }
function closeAddServiceModal() { document.getElementById('addServiceModal').style.display = 'none'; }
</script>

<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
