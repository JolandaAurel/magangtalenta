<?php
require_once 'includes/header.php';

// Only Super Admin can access this page
if (($_SESSION['admin_type'] ?? '') !== 'super' && ($_SESSION['admin_email'] ?? '') !== 'superadmin@talentasemarang.com') {
    echo "<div class='card'><h3 style='color:#ef4444;'><i class='fas fa-lock'></i> Akses Ditolak</h3><p>Hanya Super Administrator yang berhak mengakses Manajemen Akun & Pengaturan Global.</p><a href='index.php' class='btn btn-primary'>Kembali ke Dashboard</a></div>";
    require_once 'includes/footer.php';
    exit;
}


$activeTab = $_GET['tab'] ?? 'akun'; // 'akun', 'home', 'tentang', 'publikasi', 'kontak'
$success = '';
$error = '';

// 1. HANDLE KONTAK UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_kontak') {
    $settingsMap = [
        'contact_whatsapp' => trim($_POST['contact_whatsapp'] ?? ''),
        'contact_phone' => trim($_POST['contact_phone'] ?? ''),
        'contact_email' => trim($_POST['contact_email'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'operational_hours' => trim($_POST['operational_hours'] ?? ''),
        'instagram_url' => trim($_POST['instagram_url'] ?? ''),
        'facebook_url' => trim($_POST['facebook_url'] ?? ''),
        'youtube_url' => trim($_POST['youtube_url'] ?? '')
    ];

    foreach ($settingsMap as $key => $val) {
        $upd = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $upd->execute([$key, $val, $val]);
    }
    $success = "Informasi kontak berhasil diperbarui!";
    $activeTab = 'kontak';
}

// 2. HANDLE HOME HERO & PROGRAM UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_home_hero') {
    $heroTitle = trim($_POST['hero_title'] ?? '');
    $heroSubtitle = trim($_POST['hero_subtitle'] ?? '');

    $updHero = $pdo->prepare("UPDATE hero_section SET title = ?, subtitle = ? WHERE id = 1");
    $updHero->execute([$heroTitle, $heroSubtitle]);
    $success = "Teks banner halaman Home berhasil diperbarui!";
    $activeTab = 'home';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_program_card') {
    $progId = (int)$_POST['program_id'];
    $progTitle = trim($_POST['program_title'] ?? '');
    $progIcon = trim($_POST['program_icon'] ?? '');
    $progLink = trim($_POST['program_link'] ?? '');

    $updProg = $pdo->prepare("UPDATE programs SET title = ?, icon_class = ?, link_url = ? WHERE id = ?");
    $updProg->execute([$progTitle, $progIcon, $progLink, $progId]);
    $success = "Kotak program berhasil diperbarui!";
    $activeTab = 'home';
}

// 3. HANDLE TENTANG KAMI UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_tentang') {
    $pageId = (int)$_POST['page_id'];
    $pageTitle = trim($_POST['title'] ?? '');
    $contentHtml = $_POST['content_html'] ?? '';

    $updPage = $pdo->prepare("UPDATE pages SET title = ?, content_html = ? WHERE id = ?");
    $updPage->execute([$pageTitle, $contentHtml, $pageId]);
    $success = "Konten halaman Tentang Kami ('$pageTitle') berhasil disimpan!";
    $activeTab = 'tentang';
}

// 4. HANDLE PUBLIKASI (ARTIKEL)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_article') {
    $artTitle = trim($_POST['title'] ?? '');
    $artSummary = trim($_POST['summary'] ?? '');
    $artContent = $_POST['content'] ?? '';
    $artCategory = trim($_POST['category'] ?? 'Berita');
    $artSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $artTitle), '-'));

    if (empty($artTitle) || empty($artContent)) {
        $error = "Judul dan konten artikel wajib diisi!";
    } else {
        $insArt = $pdo->prepare("INSERT INTO articles (title, slug, summary, content, category, published_at) VALUES (?, ?, ?, ?, ?, CURDATE())");
        $insArt->execute([$artTitle, $artSlug, $artSummary, $artContent, $artCategory]);
        $success = "Artikel baru berhasil diterbitkan!";
    }
    $activeTab = 'publikasi';
}

if (isset($_GET['delete_article'])) {
    $delId = (int)$_GET['delete_article'];
    $delStmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    $delStmt->execute([$delId]);
    $success = "Artikel berhasil dihapus!";
    $activeTab = 'publikasi';
}

// 5. HANDLE AKUN ADMIN TOGGLE & CRUD
if (isset($_GET['toggle_admin_status'])) {
    $id = (int)$_GET['toggle_admin_status'];
    if ($id === 1) {
        $error = "Akun Super Admin utama tidak boleh dinonaktifkan!";
    } else {
        $stmt = $pdo->prepare("SELECT status FROM admin_users WHERE id = ?");
        $stmt->execute([$id]);
        $curr = $stmt->fetchColumn();
        $newStatus = ($curr === 'Aktif') ? 'Non-Aktif' : 'Aktif';
        $uStmt = $pdo->prepare("UPDATE admin_users SET status = ? WHERE id = ?");
        $uStmt->execute([$newStatus, $id]);
        $success = "Status admin berhasil diubah menjadi: " . $newStatus;
    }
    $activeTab = 'akun';
}

// FETCH DATA FOR ALL TABS
// Settings
$setStmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = $setStmt->fetchAll(PDO::FETCH_KEY_PAIR);


// Hero Section
$hero = $pdo->query("SELECT * FROM hero_section WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

// Programs (Home Cards)
$programs = $pdo->query("SELECT * FROM programs ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Tentang Kami Pages
$tentangPages = $pdo->query("SELECT id, slug, title, content_html FROM pages WHERE category = 'tentang-kami' OR slug IN ('profil', 'visi-misi', 'sejarah', 'nilai-pendidikan', 'struktur') ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$selectedTentangSlug = $_GET['tentang_slug'] ?? ($tentangPages[0]['slug'] ?? 'profil');
$currentTentangPage = null;
foreach ($tentangPages as $tp) {
    if ($tp['slug'] === $selectedTentangSlug) {
        $currentTentangPage = $tp;
        break;
    }
}
if (!$currentTentangPage && !empty($tentangPages)) {
    $currentTentangPage = $tentangPages[0];
}

// Articles (Publikasi)
$articles = $pdo->query("SELECT * FROM articles ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Admins
$admins = $pdo->query("SELECT * FROM admin_users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
.admin-tabs {
    display: flex;
    gap: 8px;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 25px;
    flex-wrap: wrap;
}
.tab-link {
    padding: 12px 20px;
    font-size: 0.95rem;
    font-weight: 600;
    color: #64748b;
    text-decoration: none;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 8px 8px 0 0;
}
.tab-link:hover {
    color: #0070ad;
    background: #f8fafc;
}
.tab-link.active {
    color: #0070ad;
    border-bottom-color: #0070ad;
    background: #ffffff;
}
.tab-badge {
    background: #e2e8f0;
    color: #475569;
    font-size: 0.75rem;
    padding: 2px 7px;
    border-radius: 20px;
}
.tab-link.active .tab-badge {
    background: #e0f2fe;
    color: #0369a1;
}
</style>

<div style="margin-bottom: 25px;">
    <h2 style="color: #0F3C5C; margin: 0; font-size: 1.6rem; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-sliders-h" style="color: #00A2E9;"></i> Manajemen Akun & Konten Global (Di Luar Unit)
    </h2>
    <p style="color: #64748b; margin: 5px 0 0 0; font-size: 0.95rem;">
        Pusat kendali Super Admin untuk mengedit halaman Home, Tentang Kami, Publikasi, Kontak, serta kelola akun admin.
    </p>
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

<!-- TABS NAVIGATION -->
<div class="admin-tabs">
    <a href="manajemen_akun.php?tab=akun" class="tab-link <?php echo $activeTab === 'akun' ? 'active' : ''; ?>">
        <i class="fas fa-users-cog"></i> Akun Admin & Kredensial <span class="tab-badge"><?php echo count($admins); ?></span>
    </a>
    <a href="manajemen_akun.php?tab=home" class="tab-link <?php echo $activeTab === 'home' ? 'active' : ''; ?>">
        <i class="fas fa-home"></i> Tombol & Halaman Home
    </a>
    <a href="manajemen_akun.php?tab=tentang" class="tab-link <?php echo $activeTab === 'tentang' ? 'active' : ''; ?>">
        <i class="fas fa-info-circle"></i> Tentang Kami <span class="tab-badge"><?php echo count($tentangPages); ?> Halaman</span>
    </a>
    <a href="manajemen_akun.php?tab=publikasi" class="tab-link <?php echo $activeTab === 'publikasi' ? 'active' : ''; ?>">
        <i class="fas fa-newspaper"></i> Publikasi & Berita <span class="tab-badge"><?php echo count($articles); ?></span>
    </a>
    <a href="manajemen_akun.php?tab=kontak" class="tab-link <?php echo $activeTab === 'kontak' ? 'active' : ''; ?>">
        <i class="fas fa-phone-alt"></i> Kontak & Info Sekolah
    </a>
</div>

<!-- ========================================== -->
<!-- TAB 1: AKUN ADMIN & KREDENSIAL -->
<!-- ========================================== -->
<?php if ($activeTab === 'akun'): ?>
<div class="card" style="padding: 0; overflow: hidden; border: 1px solid #e2e8f0;">
    <div style="padding: 18px 24px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="margin: 0; font-size: 1.15rem; color: #0F3C5C; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-users" style="color: #00A2E9;"></i> Daftar Kredensial Admin Seluruh Unit
            </h3>
            <small style="color: #64748b;">Password dapat dilihat dan disalin langsung untuk dibagikan ke penanggung jawab unit.</small>
        </div>
        <a href="kelola_admin.php" class="btn btn-sm btn-primary">
            <i class="fas fa-external-link-alt"></i> Buka Editor Admin Lengkap
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table style="margin-top: 0; width: 100%;">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th style="width: 50px; text-align: center;">No</th>
                    <th>Email Admin</th>
                    <th>Password</th>
                    <th>Nama</th>
                    <th>Unit</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $idx => $a): ?>
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #64748b;"><?php echo $idx + 1; ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-envelope" style="color: #ea4335;"></i>
                            <span style="font-weight: 600; color: #0F3C5C;"><?php echo htmlspecialchars($a['email']); ?></span>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="password" value="<?php echo htmlspecialchars($a['password']); ?>" id="pw-<?php echo $a['id']; ?>" readonly style="border: none; background: transparent; font-family: monospace; font-size: 0.95rem; width: 90px;">
                            <button type="button" onclick="togglePw('pw-<?php echo $a['id']; ?>', this)" class="btn btn-sm btn-outline" style="padding: 2px 6px;">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </td>
                    <td><b><?php echo htmlspecialchars($a['name']); ?></b></td>
                    <td>
                        <?php if ($a['role'] === 'super'): ?>
                            <span class="badge badge-amber"><i class="fas fa-crown"></i> Super Admin</span>
                        <?php elseif ($a['role'] === 'pendidikan'): ?>
                            <span class="badge badge-primary"><?php echo htmlspecialchars($a['unit']); ?></span>
                        <?php else: ?>
                            <span class="badge badge-purple"><?php echo htmlspecialchars($a['unit']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <?php if ($a['status'] === 'Aktif'): ?>
                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                        <?php else: ?>
                            <span class="badge badge-danger"><i class="fas fa-ban"></i> Non-Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <a href="manajemen_akun.php?tab=akun&toggle_admin_status=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline" title="Ganti Status Aktif/Non-Aktif">
                            <i class="fas fa-power-off"></i> Toggle
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- TAB 2: TOMBOL & HALAMAN HOME -->
<!-- ========================================== -->
<?php elseif ($activeTab === 'home'): ?>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
    <!-- Form Banner Hero Home -->
    <div class="card" style="border-left: 5px solid #00A2E9;">
        <h3 style="color: #0F3C5C; margin-bottom: 5px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-image" style="color: #00A2E9;"></i> Teks Banner Utama (Hero Home)
        </h3>
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">
            Sesuaikan judul besar dan deskripsi yang tampil di bagian paling atas halaman depan.
        </p>

        <form method="POST">
            <input type="hidden" name="action" value="save_home_hero">
            <div class="form-group">
                <label>Judul Utama (Headline)</label>
                <input type="text" name="hero_title" class="form-control" value="<?php echo htmlspecialchars($hero['title'] ?? 'Every Potential Matters.'); ?>" required>
                <small style="color: #64748b;">Gunakan judul singkat dan penuh inspirasi.</small>
            </div>

            <div class="form-group">
                <label>Deskripsi Subtitle</label>
                <textarea name="hero_subtitle" class="form-control" rows="3" required><?php echo htmlspecialchars($hero['subtitle'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
                <i class="fas fa-save"></i> Simpan Teks Banner Home
            </button>
        </form>
    </div>

    <!-- Edit Tombol Program Utama Home -->
    <div class="card" style="border-left: 5px solid #10b981;">
        <h3 style="color: #0F3C5C; margin-bottom: 5px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-th-large" style="color: #10b981;"></i> Kotak Program Utama di Home
        </h3>
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">
            Edit nama dan ikon 4 kotak program yang berada di bawah banner utama.
        </p>

        <div style="display: flex; flex-direction: column; gap: 15px;">
            <?php foreach ($programs as $prog): ?>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px;">
                <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: end;">
                    <input type="hidden" name="action" value="save_program_card">
                    <input type="hidden" name="program_id" value="<?php echo $prog['id']; ?>">
                    
                    <div>
                        <label style="font-size: 0.8rem; font-weight: 600; color: #475569;">Judul Kotak</label>
                        <input type="text" name="program_title" class="form-control" value="<?php echo htmlspecialchars($prog['title']); ?>" style="padding: 6px 10px; font-size: 0.9rem;">
                    </div>
                    <div>
                        <label style="font-size: 0.8rem; font-weight: 600; color: #475569;">Ikon FontAwesome</label>
                        <input type="text" name="program_icon" class="form-control" value="<?php echo htmlspecialchars($prog['icon_class']); ?>" style="padding: 6px 10px; font-size: 0.9rem;">
                    </div>
                    <div>
                        <input type="hidden" name="program_link" value="<?php echo htmlspecialchars($prog['link_url']); ?>">
                        <button type="submit" class="btn btn-sm btn-success" title="Simpan Kotak">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- TAB 3: TENTANG KAMI -->
<!-- ========================================== -->
<?php elseif ($activeTab === 'tentang'): ?>
<div style="display: grid; grid-template-columns: 280px 1fr; gap: 25px;">
    <!-- Sidebar List Sub-Halaman Tentang Kami -->
    <div class="card" style="padding: 15px;">
        <h4 style="margin: 0 0 15px 0; color: #0F3C5C; font-size: 1.05rem;">
            <i class="fas fa-folder-open" style="color: #00A2E9;"></i> Pilih Sub-Halaman:
        </h4>
        <div style="display: flex; flex-direction: column; gap: 6px;">
            <?php foreach ($tentangPages as $tp): ?>
                <a href="manajemen_akun.php?tab=tentang&tentang_slug=<?php echo $tp['slug']; ?>" 
                   style="padding: 10px 14px; border-radius: 8px; text-decoration: none; font-size: 0.92rem; font-weight: 500; display: flex; align-items: center; justify-content: space-between; <?php echo ($currentTentangPage && $currentTentangPage['slug'] === $tp['slug']) ? 'background: #0070ad; color: white;' : 'background: #f8fafc; color: #334155;'; ?>">
                    <span><?php echo htmlspecialchars($tp['title']); ?></span>
                    <i class="fas fa-chevron-right" style="font-size: 0.75rem; opacity: 0.7;"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Editor Form Halaman Terpilih -->
    <div class="card" style="border-left: 5px solid #0070ad;">
        <?php if ($currentTentangPage): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
                <div>
                    <h3 style="color: #0F3C5C; margin: 0;">Edit Halaman: <?php echo htmlspecialchars($currentTentangPage['title']); ?></h3>
                    <small style="color: #64748b;">URL: /page.php?slug=<?php echo htmlspecialchars($currentTentangPage['slug']); ?></small>
                </div>
                <a href="/talenta-semarang/page.php?slug=<?php echo htmlspecialchars($currentTentangPage['slug']); ?>" target="_blank" class="btn btn-sm btn-outline">
                    <i class="fas fa-external-link-alt"></i> Pratinjau di Website
                </a>
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="save_tentang">
                <input type="hidden" name="page_id" value="<?php echo $currentTentangPage['id']; ?>">

                <div class="form-group">
                    <label>Judul Halaman</label>
                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($currentTentangPage['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Isi Konten HTML Halaman</label>
                    <textarea name="content_html" class="form-control" rows="16" required style="font-family: monospace; font-size: 0.92rem;"><?php echo htmlspecialchars($currentTentangPage['content_html']); ?></textarea>
                    <small style="color: #64748b;">Mendukung format HTML standar (paragraf &lt;p&gt;, heading &lt;h3&gt;, list &lt;ul&gt; &lt;li&gt;, dll).</small>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 12px 24px;">
                    <i class="fas fa-save"></i> Simpan Perubahan Halaman
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================== -->
<!-- TAB 4: PUBLIKASI & BERITA -->
<!-- ========================================== -->
<?php elseif ($activeTab === 'publikasi'): ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h3 style="color: #0F3C5C; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-newspaper" style="color: #ef4444;"></i> Kelola Artikel, Berita, & Publikasi
    </h3>
    <button onclick="document.getElementById('newArticleCard').style.display='block'" class="btn btn-success">
        <i class="fas fa-plus"></i> Tulis Artikel Baru
    </button>
</div>

<!-- FORM TULIS ARTIKEL BARU (HIDDEN BY DEFAULT) -->
<div id="newArticleCard" class="card" style="display: none; border-left: 5px solid #10b981; margin-bottom: 25px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h4 style="margin: 0; color: #0F3C5C;">Formulir Publikasi Artikel Baru</h4>
        <button type="button" onclick="document.getElementById('newArticleCard').style.display='none'" class="btn btn-sm btn-secondary">&times; Tutup</button>
    </div>
    <form method="POST">
        <input type="hidden" name="action" value="add_article">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Judul Artikel <span style="color:#ef4444;">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="Contoh: Tips Mendampingi Tumbuh Kembang Anak" required>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="category" class="form-control">
                    <option value="Berita">Berita Sekolah</option>
                    <option value="Parenting">Tips Parenting</option>
                    <option value="Edukasi">Edukasi & Psikologi</option>
                    <option value="Kegiatan">Kegiatan & Acara</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Ringkasan Singkat (Summary)</label>
            <textarea name="summary" class="form-control" rows="2" placeholder="Ringkasan yang tampil di kartu cuplikan..."></textarea>
        </div>

        <div class="form-group">
            <label>Konten Lengkap Artikel</label>
            <textarea name="content" class="form-control" rows="10" placeholder="Tuliskan isi lengkap artikel..." required></textarea>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-paper-plane"></i> Terbitkan Artikel
        </button>
    </form>
</div>

<!-- DAFTAR ARTIKEL -->
<div class="card" style="padding: 0; overflow: hidden; border: 1px solid #e2e8f0;">
    <table style="margin-top: 0; width: 100%;">
        <thead>
            <tr style="background: #f1f5f9;">
                <th style="width: 50px; text-align: center;">No</th>
                <th>Judul Artikel</th>
                <th>Kategori</th>
                <th>Tanggal Rilis</th>
                <th style="width: 140px; text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($articles)): ?>
                <tr><td colspan="5" style="text-align: center; color: #94a3b8; padding: 30px;">Belum ada artikel publikasi.</td></tr>
            <?php else: ?>
                <?php foreach ($articles as $ai => $art): ?>
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #64748b;"><?php echo $ai + 1; ?></td>
                    <td>
                        <div style="font-weight: 600; color: #0F3C5C;"><?php echo htmlspecialchars($art['title']); ?></div>
                        <small style="color: #64748b;"><?php echo htmlspecialchars(substr($art['summary'] ?? '', 0, 70)); ?>...</small>
                    </td>
                    <td><span class="badge badge-primary"><?php echo htmlspecialchars($art['category'] ?? 'Berita'); ?></span></td>
                    <td style="color: #64748b; font-size: 0.9rem;"><?php echo htmlspecialchars($art['published_at'] ?? '-'); ?></td>
                    <td style="text-align: center;">
                        <a href="kelola_artikel.php?edit=<?php echo $art['id']; ?>" class="btn btn-sm btn-primary" title="Edit Artikel">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="manajemen_akun.php?tab=publikasi&delete_article=<?php echo $art['id']; ?>" onclick="return confirm('Hapus artikel ini?')" class="btn btn-sm btn-danger" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ========================================== -->
<!-- TAB 5: KONTAK & INFO SEKOLAH -->
<!-- ========================================== -->
<?php elseif ($activeTab === 'kontak'): ?>
<div class="card" style="max-width: 800px; border-left: 5px solid #10b981;">
    <h3 style="color: #0F3C5C; margin-bottom: 5px; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-address-book" style="color: #10b981;"></i> Edit Informasi Kontak & Layanan
    </h3>
    <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 25px;">
        Data yang diubah di sini akan otomatis tampil di halaman Kontak, Footer, dan tombol WhatsApp di seluruh website.
    </p>

    <form method="POST">
        <input type="hidden" name="action" value="save_kontak">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label><i class="fab fa-whatsapp" style="color: #10b981;"></i> Nomor WhatsApp Resmi</label>
                <input type="text" name="contact_whatsapp" class="form-control" value="<?php echo htmlspecialchars($settings['contact_whatsapp'] ?? '0817838939'); ?>" required>
                <small style="color: #64748b;">Digunakan untuk tombol chat WhatsApp mengambang.</small>
            </div>

            <div class="form-group">
                <label><i class="fas fa-phone-alt" style="color: #00A2E9;"></i> Telepon Kantor</label>
                <input type="text" name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? '024-760-1502'); ?>" required>
                <small style="color: #64748b;">Nomor telepon kantor operasional.</small>
            </div>
        </div>

        <div class="form-group">
            <label><i class="fas fa-envelope" style="color: #ea4335;"></i> Alamat Email Resmi</label>
            <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($settings['contact_email'] ?? 'talenta.lpt@gmail.com'); ?>" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-map-marker-alt" style="color: #ef4444;"></i> Alamat Lengkap Kampus</label>
            <textarea name="address" class="form-control" rows="2" required><?php echo htmlspecialchars($settings['address'] ?? 'Jl. Puspowarno Tengah IX No.6, Semarang Barat'); ?></textarea>
        </div>

        <div class="form-group">
            <label><i class="far fa-clock" style="color: #f59e0b;"></i> Jam Operasional Sekolah</label>
            <input type="text" name="operational_hours" class="form-control" value="<?php echo htmlspecialchars($settings['operational_hours'] ?? 'Senin - Jumat: 08.00 - 16.00 WIB | Sabtu: 08.00 - 13.00 WIB'); ?>">
        </div>

        <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-top: 20px; margin-bottom: 25px;">
            <h4 style="margin: 0 0 15px 0; color: #0F3C5C; font-size: 1rem;"><i class="fas fa-share-alt"></i> Tautan Media Sosial</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div class="form-group" style="margin: 0;">
                    <label style="font-size: 0.85rem;"><i class="fab fa-instagram" style="color: #e1306c;"></i> Instagram</label>
                    <input type="text" name="instagram_url" class="form-control" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>" placeholder="https://instagram.com/...">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label style="font-size: 0.85rem;"><i class="fab fa-facebook" style="color: #1877f2;"></i> Facebook</label>
                    <input type="text" name="facebook_url" class="form-control" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>" placeholder="https://facebook.com/...">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label style="font-size: 0.85rem;"><i class="fab fa-youtube" style="color: #ff0000;"></i> YouTube</label>
                    <input type="text" name="youtube_url" class="form-control" value="<?php echo htmlspecialchars($settings['youtube_url'] ?? ''); ?>" placeholder="https://youtube.com/...">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
            <i class="fas fa-save"></i> Simpan Informasi Kontak
        </button>
    </form>
</div>
<?php endif; ?>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'far fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'far fa-eye';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
