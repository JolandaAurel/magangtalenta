<?php
require_once 'includes/header.php';

// Only Super Admin can access this page
if (($_SESSION['admin_type'] ?? '') !== 'super' && ($_SESSION['admin_email'] ?? '') !== 'superadmin@talentasemarang.com') {
    echo "<div class='card'><h3 style='color:#ef4444;'><i class='fas fa-lock'></i> Akses Ditolak</h3><p>Hanya Super Administrator yang berhak mengakses Manajemen Unit.</p><a href='index.php' class='btn btn-primary'>Kembali ke Dashboard</a></div>";
    require_once 'includes/footer.php';
    exit;
}


$success = '';
$error = '';

// Handle Add New Unit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_unit') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'pendidikan'); // 'pendidikan' or 'psikologi'
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($title)) {
        $error = "Nama unit wajib diisi!";
    } else {
        // Generate slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        
        // Check if page slug exists
        $chk = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
        $chk->execute([$slug]);
        if ($chk->fetch()) {
            $error = "Unit dengan nama/slug ini sudah ada di sistem!";
        } else {
            $initContent = "<p>Selamat datang di unit " . htmlspecialchars($title) . " Sekolah Talenta Semarang. Halaman ini sedang dalam proses penyusunan konten.</p>";
            $insPage = $pdo->prepare("INSERT INTO pages (slug, title, category, content_html) VALUES (?, ?, ?, ?)");
            $insPage->execute([$slug, $title, $category, $initContent]);

            // Create admin user for this new unit if email provided
            if (!empty($email) && !empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insAdmin = $pdo->prepare("INSERT INTO admin_users (username, email, password, password_hash, name, unit, unit_slug, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Aktif')");
                $insAdmin->execute([$slug, $email, $password, $hash, 'Admin ' . $title, $title, $slug, $category]);
            }

            $success = "Unit baru '$title' berhasil ditambahkan!";
        }
    }
}

// Handle Delete Unit
if (isset($_GET['delete_unit'])) {
    $slugToDelete = trim($_GET['delete_unit']);
    // Do not allow deleting core units if required
    $delPage = $pdo->prepare("DELETE FROM pages WHERE slug = ?");
    $delPage->execute([$slugToDelete]);

    $delAdmin = $pdo->prepare("DELETE FROM admin_users WHERE unit_slug = ? AND role != 'super'");
    $delAdmin->execute([$slugToDelete]);

    $success = "Unit '$slugToDelete' berhasil dihapus dari sistem.";
}

// Fetch all units from pages table (category IN 'pendidikan', 'psikologi')
$stmt = $pdo->query("SELECT p.id, p.slug, p.title, p.category, p.updated_at, LENGTH(p.content_html) as content_len FROM pages p WHERE p.category IN ('pendidikan', 'psikologi') ORDER BY p.category DESC, p.id ASC");
$allUnits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Enrich with admin info and photo count
$unitList = [];
$totalPendidikan = 0;
$totalPsikologi = 0;

foreach ($allUnits as $u) {
    if ($u['category'] === 'pendidikan') $totalPendidikan++;
    if ($u['category'] === 'psikologi') $totalPsikologi++;

    // Admin info
    $aStmt = $pdo->prepare("SELECT name, email, status FROM admin_users WHERE unit_slug = ?");
    $aStmt->execute([$u['slug']]);
    $admin = $aStmt->fetch(PDO::FETCH_ASSOC);

    // Gallery count
    $gStmt = $pdo->prepare("SELECT COUNT(*) FROM page_galleries WHERE page_slug = ?");
    $gStmt->execute([$u['slug']]);
    $photos = $gStmt->fetchColumn();

    $unitList[] = [
        'id' => $u['id'],
        'slug' => $u['slug'],
        'title' => $u['title'],
        'category' => $u['category'],
        'updated_at' => $u['updated_at'],
        'content_len' => (int)$u['content_len'],
        'admin_name' => $admin ? $admin['name'] : 'Belum Ditugaskan',
        'admin_email' => $admin ? $admin['email'] : '-',
        'admin_status' => $admin ? $admin['status'] : 'Non-Aktif',
        'photos' => (int)$photos
    ];
}
$totalUnits = count($unitList);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="color: #0F3C5C; margin: 0; font-size: 1.6rem; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-school" style="color: #00A2E9;"></i> Manajemen Unit Sekolah & Layanan
        </h2>
        <p style="color: #64748b; margin: 5px 0 0 0; font-size: 0.95rem;">
            Pantau dan kelola seluruh unit pendidikan, kelas inklusi, dan layanan psikologi Talenta Semarang.
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button onclick="openAddUnitModal()" class="btn btn-success" style="box-shadow: 0 4px 10px rgba(16,185,129,0.3);">
            <i class="fas fa-plus-circle"></i> Tambah Unit Baru
        </button>
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

<!-- STATS CARDS -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 25px;">
    <div class="card" style="margin: 0; border-left: 5px solid #00A2E9; display: flex; align-items: center; gap: 15px; padding: 15px 20px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2fe; color: #00A2E9; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
            <i class="fas fa-th-large"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Total Seluruh Unit</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #0F3C5C;"><?php echo $totalUnits; ?> Unit</div>
        </div>
    </div>
    
    <div class="card" style="margin: 0; border-left: 5px solid #0284c7; display: flex; align-items: center; gap: 15px; padding: 15px 20px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Unit Pendidikan & Inklusi</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #0F3C5C;"><?php echo $totalPendidikan; ?> Unit</div>
        </div>
    </div>
    
    <div class="card" style="margin: 0; border-left: 5px solid #8b5cf6; display: flex; align-items: center; gap: 15px; padding: 15px 20px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: #ede9fe; color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
            <i class="fas fa-brain"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Layanan Psikologi & Terapi</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #0F3C5C;"><?php echo $totalPsikologi; ?> Unit</div>
        </div>
    </div>
</div>

<!-- SEARCH & FILTER -->
<div class="card" style="padding: 15px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div style="display: flex; align-items: center; gap: 10px; flex-grow: 1; max-width: 400px;">
        <i class="fas fa-search" style="color: #94a3b8;"></i>
        <input type="text" id="unitSearchInput" onkeyup="filterUnits()" class="form-control" placeholder="Cari nama unit atau admin..." style="margin: 0; padding: 8px 12px;">
    </div>
    <div style="display: flex; gap: 8px;">
        <button onclick="filterCategory('all')" class="btn btn-sm btn-outline filter-btn active" id="btn-all">Semua (<?php echo $totalUnits; ?>)</button>
        <button onclick="filterCategory('pendidikan')" class="btn btn-sm btn-outline filter-btn" id="btn-pendidikan">Pendidikan (<?php echo $totalPendidikan; ?>)</button>
        <button onclick="filterCategory('psikologi')" class="btn btn-sm btn-outline filter-btn" id="btn-psikologi">Psikologi (<?php echo $totalPsikologi; ?>)</button>
    </div>
</div>

<!-- ALL UNITS TABLE -->
<div class="card" style="padding: 0; overflow: hidden; border: 1px solid #e2e8f0;">
    <div style="padding: 18px 24px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 1.15rem; color: #0F3C5C; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-list-ul" style="color: #00A2E9;"></i> Daftar Semua Unit Talenta
        </h3>
        <span style="font-size: 0.85rem; color: #64748b;">
            Super Admin memiliki hak akses penuh untuk mengedit semua unit
        </span>
    </div>

    <div style="overflow-x: auto;">
        <table style="margin-top: 0; width: 100%;">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th style="width: 50px; text-align: center;">No</th>
                    <th style="min-width: 220px;">Nama Unit</th>
                    <th style="min-width: 140px;">Kategori</th>
                    <th style="min-width: 230px;">Admin Penanggung Jawab</th>
                    <th style="min-width: 150px;">Status Konten</th>
                    <th style="width: 110px; text-align: center;">Galeri</th>
                    <th style="min-width: 160px;">Terakhir Update</th>
                    <th style="width: 160px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unitList as $i => $u): ?>
                <tr class="unit-row" data-cat="<?php echo htmlspecialchars($u['category']); ?>" data-text="<?php echo strtolower(htmlspecialchars($u['title'] . ' ' . $u['admin_name'] . ' ' . $u['admin_email'])); ?>">
                    <td style="text-align: center; font-weight: 700; color: #64748b;"><?php echo $i + 1; ?></td>
                    
                    <td>
                        <div style="font-weight: 700; color: #0F3C5C; font-size: 0.95rem;">
                            <?php echo htmlspecialchars($u['title']); ?>
                        </div>
                        <small style="color: #94a3b8; font-family: monospace;">/page.php?slug=<?php echo htmlspecialchars($u['slug']); ?></small>
                    </td>

                    <td>
                        <?php if ($u['category'] === 'pendidikan'): ?>
                            <span class="badge badge-primary"><i class="fas fa-graduation-cap"></i> Pendidikan</span>
                        <?php else: ?>
                            <span class="badge badge-purple"><i class="fas fa-brain"></i> Layanan Psikologi</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <div style="font-weight: 600; color: #334155; font-size: 0.9rem;"><?php echo htmlspecialchars($u['admin_name']); ?></div>
                        <div style="font-size: 0.8rem; color: #64748b; display: flex; align-items: center; gap: 5px;">
                            <i class="fab fa-google" style="color: #ea4335;"></i> <?php echo htmlspecialchars($u['admin_email']); ?>
                        </div>
                    </td>

                    <td>
                        <?php if ($u['content_len'] > 100): ?>
                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Terisi (<?php echo number_format($u['content_len']); ?> b)</span>
                        <?php else: ?>
                            <span class="badge badge-danger"><i class="fas fa-exclamation-circle"></i> Belum Diisi</span>
                        <?php endif; ?>
                    </td>

                    <td style="text-align: center;">
                        <span class="badge <?php echo ($u['photos'] > 0) ? 'badge-primary' : 'badge-gray'; ?>">
                            <i class="fas fa-camera"></i> <?php echo $u['photos']; ?> Foto
                        </span>
                    </td>

                    <td style="font-size: 0.85rem; color: #64748b;">
                        <?php echo htmlspecialchars($u['updated_at']); ?>
                    </td>

                    <td style="text-align: center;">
                        <div style="display: flex; gap: 5px; justify-content: center;">
                            <a href="/outsource_talenta_landing/magangtalenta/page.php?slug=<?php echo htmlspecialchars($u['slug']); ?>" target="_blank" class="btn btn-sm btn-outline" title="Lihat di Website">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <a href="<?php echo ($u['category'] === 'pendidikan') ? 'kelola_sekolah.php' : 'kelola_psikologi.php'; ?>?edit=<?php echo $u['id']; ?>" class="btn btn-sm btn-primary" title="Edit Konten Unit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="manajemen_unit.php?delete_unit=<?php echo urlencode($u['slug']); ?>" onclick="return confirm('Hapus unit <?php echo htmlspecialchars($u['title']); ?> beserta halaman dan adminnya?')" class="btn btn-sm btn-danger" title="Hapus Unit">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL TAMBAH UNIT BARU -->
<div id="addUnitModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 99999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 550px; padding: 30px; box-shadow: 0 25px 50px rgba(0,0,0,0.2); position: relative;">
        <span onclick="closeAddUnitModal()" style="position: absolute; top: 20px; right: 20px; cursor: pointer; font-size: 1.4rem; color: #94a3b8;">&times;</span>
        <h3 style="margin-top: 0; color: #0F3C5C; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-plus-circle" style="color: #10b981;"></i> Tambah Unit Baru
        </h3>
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">Tambahkan unit sekolah atau layanan psikologi baru ke website.</p>
        
        <form method="POST">
            <input type="hidden" name="action" value="add_unit">
            
            <div class="form-group">
                <label>Nama Unit Baru <span style="color:#ef4444;">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="contoh: Sekolah Vokasi / Terapi Wicara" required>
            </div>

            <div class="form-group">
                <label>Kategori Unit <span style="color:#ef4444;">*</span></label>
                <select name="category" class="form-control" required>
                    <option value="pendidikan">Pendidikan & Inklusi</option>
                    <option value="psikologi">Layanan Psikologi</option>
                </select>
            </div>

            <div style="background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 15px;">
                <div style="font-weight: 600; font-size: 0.9rem; color: #0F3C5C; margin-bottom: 8px;">
                    <i class="fas fa-user-lock"></i> Kredensial Login Admin Unit Baru (Opsional)
                </div>
                <div class="form-group" style="margin-bottom: 10px;">
                    <label style="font-size: 0.85rem;">Email Admin</label>
                    <input type="email" name="email" class="form-control" placeholder="contoh: vokasi@talentasemarang.com">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.85rem;">Kata Sandi</label>
                    <input type="text" name="password" class="form-control" placeholder="contoh: vokasi123">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeAddUnitModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Buat Unit Baru</button>
            </div>
        </form>
    </div>
</div>

<script>
function filterUnits() {
    const q = document.getElementById('unitSearchInput').value.toLowerCase();
    const rows = document.querySelectorAll('.unit-row');
    rows.forEach(r => {
        const text = r.getAttribute('data-text');
        r.style.display = text.includes(q) ? '' : 'none';
    });
}

function filterCategory(cat) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('btn-' + cat).classList.add('active');

    const rows = document.querySelectorAll('.unit-row');
    rows.forEach(r => {
        const rowCat = r.getAttribute('data-cat');
        if (cat === 'all' || rowCat === cat) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
}

function openAddUnitModal() {
    document.getElementById('addUnitModal').style.display = 'flex';
}
function closeAddUnitModal() {
    document.getElementById('addUnitModal').style.display = 'none';
}

window.onclick = function(e) {
    const m = document.getElementById('addUnitModal');
    if (e.target === m) closeAddUnitModal();
}
</script>

<?php require_once 'includes/footer.php'; ?>
