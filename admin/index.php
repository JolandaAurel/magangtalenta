<?php
require_once 'includes/header.php';

$isAdminSuper = (isset($_SESSION['admin_type']) && $_SESSION['admin_type'] === 'super');
$unitSlug = $_SESSION['admin_unit_slug'] ?? '';

// If Super Admin, fetch progress for all units dynamically
if ($isAdminSuper) {
    $pQuery = $pdo->query("SELECT slug, title as name, category FROM pages WHERE category IN ('pendidikan', 'psikologi') ORDER BY category DESC, id ASC");
    $units = $pQuery->fetchAll(PDO::FETCH_ASSOC);

    $countPendidikan = 0;
    $countPsikologi = 0;
    foreach ($units as $u) {
        if ($u['category'] === 'pendidikan') $countPendidikan++;
        if ($u['category'] === 'psikologi') $countPsikologi++;
    }

    $unitProgress = [];
    foreach ($units as $u) {
        $pStmt = $pdo->prepare("SELECT id, title, category, updated_at, LENGTH(content_html) as content_len FROM pages WHERE slug = ?");
        $pStmt->execute([$u['slug']]);
        $page = $pStmt->fetch(PDO::FETCH_ASSOC);

        $aStmt = $pdo->prepare("SELECT name, email, status FROM admin_users WHERE unit_slug = ?");
        $aStmt->execute([$u['slug']]);
        $admin = $aStmt->fetch(PDO::FETCH_ASSOC);

        $gStmt = $pdo->prepare("SELECT COUNT(*) as photo_count FROM page_galleries WHERE page_slug = ?");
        $gStmt->execute([$u['slug']]);
        $photos = $gStmt->fetchColumn();

        $unitProgress[] = [
            'slug' => $u['slug'],
            'name' => $u['name'],
            'category' => $u['category'],
            'page_id' => $page ? $page['id'] : null,
            'updated_at' => $page ? $page['updated_at'] : '-',
            'content_len' => $page ? (int)$page['content_len'] : 0,
            'admin_name' => $admin ? $admin['name'] : 'Belum Ditugaskan',
            'admin_email' => $admin ? $admin['email'] : '-',
            'admin_status' => $admin ? $admin['status'] : 'Non-Aktif',
            'photos' => (int)$photos
        ];
    }

    // Counts for stats
    $totalArticles = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
    $totalGalleries = $pdo->query("SELECT COUNT(*) FROM page_galleries")->fetchColumn();
    $totalAdmins = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
} else {
    // Single unit admin data
    $pStmt = $pdo->prepare("SELECT id, title, category, updated_at, LENGTH(content_html) as content_len FROM pages WHERE slug = ?");
    $pStmt->execute([$unitSlug]);
    $myPage = $pStmt->fetch(PDO::FETCH_ASSOC);

    $gStmt = $pdo->prepare("SELECT COUNT(*) as photo_count FROM page_galleries WHERE page_slug = ?");
    $gStmt->execute([$unitSlug]);
    $myPhotos = $gStmt->fetchColumn();
}
?>

<!-- WELCOME BANNER -->
<div class="card" style="background: linear-gradient(135deg, #0F3C5C 0%, #0070ad 100%); color: white; border: none; padding: 25px 30px; border-radius: 16px; margin-bottom: 25px; box-shadow: 0 10px 20px rgba(0,112,173,0.15);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 2px; opacity: 0.8; margin-bottom: 5px; font-weight: 600;">
                <i class="fas fa-shield-alt"></i> Portal Administrasi Terpadu
            </div>
            <h2 style="margin: 0; font-size: 1.8rem; font-weight: 700;">
                Selamat Datang, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>!
            </h2>
            <?php if ($isAdminSuper): ?>
                <p style="margin: 8px 0 0 0; opacity: 0.9; font-size: 1rem; max-width: 700px;">
                    Anda login sebagai <b>Super Administrator</b>. Anda memiliki hak akses penuh untuk memantau semua progress pengisian konten, galeri, dan kredensial admin di seluruh <?php echo count($units); ?> unit.
                </p>
            <?php else: ?>
                <p style="margin: 8px 0 0 0; opacity: 0.9; font-size: 1rem;">
                    Anda login sebagai pengelola unit <b><?php echo htmlspecialchars($_SESSION['admin_unit_name'] ?? $unitSlug); ?></b>. Anda dapat memperbarui informasi, gambar, dan galeri untuk unit Anda.
                </p>
            <?php endif; ?>
        </div>
        <div>
            <?php if ($isAdminSuper): ?>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="manajemen_unit.php" class="btn" style="background: #ffffff; color: #0070ad; font-weight: 700; padding: 12px 18px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                        <i class="fas fa-school"></i> Manajemen Unit
                    </a>
                    <a href="manajemen_akun.php" class="btn" style="background: #FFB800; color: #0F3C5C; font-weight: 700; padding: 12px 18px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                        <i class="fas fa-sliders-h"></i> Manajemen Akun (Di Luar Unit)
                    </a>
                </div>

            <?php else: ?>
                <a href="<?php echo ($_SESSION['admin_type'] === 'pendidikan') ? 'kelola_sekolah.php' : 'kelola_psikologi.php'; ?>?edit=<?php echo $myPage['id'] ?? ''; ?>" class="btn" style="background: #10b981; color: white; font-weight: 700; padding: 12px 20px; border-radius: 10px;">
                    <i class="fas fa-edit"></i> Edit Halaman Saya
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($isAdminSuper): ?>
<!-- STATS ROW FOR SUPER ADMIN -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px;">
    <div class="card" style="margin: 0; border-left: 5px solid #00A2E9;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Total Unit Terdaftar</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #0F3C5C; margin-top: 5px;"><?php echo count($units); ?> Unit</div>
            </div>
            <div style="width: 50px; height: 50px; border-radius: 12px; background: #e0f2fe; color: #00A2E9; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fas fa-layer-group"></i>
            </div>
        </div>
        <div style="margin-top: 10px; font-size: 0.8rem; color: #64748b;">
            <span style="color: #0284c7; font-weight: 600;"><?php echo $countPendidikan; ?> Pendidikan</span> &bull; <span style="color: #8b5cf6; font-weight: 600;"><?php echo $countPsikologi; ?> Psikologi</span>
        </div>
    </div>

    <div class="card" style="margin: 0; border-left: 5px solid #10b981;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Akun Admin Terdaftar</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #0F3C5C; margin-top: 5px;"><?php echo $totalAdmins; ?> Akun</div>
            </div>
            <div style="width: 50px; height: 50px; border-radius: 12px; background: #dcfce7; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fab fa-google"></i>
            </div>
        </div>
        <div style="margin-top: 10px; font-size: 0.8rem; color: #10b981; font-weight: 600;">
            <i class="fas fa-check-circle"></i> <?php echo ($totalAdmins - 1); ?> Akun Unit + 1 Super Admin
        </div>
    </div>

    <div class="card" style="margin: 0; border-left: 5px solid #8b5cf6;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Foto Galeri Unit</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #0F3C5C; margin-top: 5px;"><?php echo $totalGalleries; ?> Foto</div>
            </div>
            <div style="width: 50px; height: 50px; border-radius: 12px; background: #ede9fe; color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fas fa-camera"></i>
            </div>
        </div>
        <div style="margin-top: 10px; font-size: 0.8rem; color: #64748b;">
            Dokumentasi & kegiatan per unit
        </div>
    </div>

    <div class="card" style="margin: 0; border-left: 5px solid #ef4444;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Artikel & Berita</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #0F3C5C; margin-top: 5px;"><?php echo $totalArticles; ?> Post</div>
            </div>
            <div style="width: 50px; height: 50px; border-radius: 12px; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fas fa-newspaper"></i>
            </div>
        </div>
        <div style="margin-top: 10px; font-size: 0.8rem; color: #64748b;">
            Publikasi berita & edukasi publik
        </div>
    </div>
</div>

<!-- MONITORING PROGRESS SELURUH 14 UNIT -->
<div class="card" style="padding: 0; overflow: hidden; border: 1px solid #e2e8f0; margin-bottom: 25px;">
    <div style="padding: 20px 25px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="margin: 0; font-size: 1.2rem; color: #0F3C5C; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-chart-line" style="color: #00A2E9;"></i> Monitoring Progress Seluruh 14 Unit
            </h3>
            <p style="margin: 4px 0 0 0; font-size: 0.85rem; color: #64748b;">
                Pantau kelengkapan konten teks, galeri foto, status admin, dan update terkini setiap unit.
            </p>
        </div>
        <a href="kelola_admin.php" class="btn btn-sm btn-outline" style="font-weight: 600;">
            <i class="fas fa-key"></i> Lihat Password & SSO
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table style="margin-top: 0; width: 100%;">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th style="width: 50px; text-align: center;">No</th>
                    <th style="min-width: 220px;">Unit Sekolah / Layanan</th>
                    <th style="min-width: 140px;">Kategori</th>
                    <th style="min-width: 240px;">Admin & Email Google SSO</th>
                    <th style="min-width: 150px;">Status Konten</th>
                    <th style="width: 110px; text-align: center;">Galeri</th>
                    <th style="min-width: 160px;">Terakhir Update</th>
                    <th style="width: 160px; text-align: center;">Aksi Cepat</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unitProgress as $idx => $up): ?>
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #64748b;"><?php echo $idx + 1; ?></td>
                    
                    <td>
                        <div style="font-weight: 600; color: #0F3C5C; font-size: 0.95rem;">
                            <?php echo htmlspecialchars($up['name']); ?>
                        </div>
                        <small style="color: #94a3b8; font-family: monospace;">slug: /<?php echo htmlspecialchars($up['slug']); ?></small>
                    </td>

                    <td>
                        <?php if ($up['category'] === 'pendidikan'): ?>
                            <span class="badge badge-primary"><i class="fas fa-school"></i> Pendidikan</span>
                        <?php else: ?>
                            <span class="badge badge-purple"><i class="fas fa-brain"></i> Psikologi</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <div style="font-weight: 600; color: #334155; font-size: 0.9rem;"><?php echo htmlspecialchars($up['admin_name']); ?></div>
                        <div style="font-size: 0.8rem; color: #64748b; display: flex; align-items: center; gap: 5px;">
                            <i class="fab fa-google" style="color: #ea4335;"></i> <?php echo htmlspecialchars($up['admin_email']); ?>
                        </div>
                    </td>

                    <td>
                        <?php if ($up['content_len'] > 100): ?>
                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Terisi (<?php echo number_format($up['content_len']); ?> b)</span>
                        <?php else: ?>
                            <span class="badge badge-danger"><i class="fas fa-exclamation-circle"></i> Belum Diisi</span>
                        <?php endif; ?>
                    </td>

                    <td style="text-align: center;">
                        <span class="badge <?php echo ($up['photos'] > 0) ? 'badge-primary' : 'badge-gray'; ?>">
                            <i class="fas fa-image"></i> <?php echo $up['photos']; ?> Foto
                        </span>
                    </td>

                    <td style="font-size: 0.85rem; color: #64748b;">
                        <?php echo htmlspecialchars($up['updated_at']); ?>
                    </td>

                    <td style="text-align: center;">
                        <div style="display: flex; gap: 5px; justify-content: center;">
                            <a href="/talenta-semarang/page.php?slug=<?php echo htmlspecialchars($up['slug']); ?>" target="_blank" class="btn btn-sm btn-outline" title="Lihat di Website">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($up['page_id']): ?>
                                <a href="<?php echo ($up['category'] === 'pendidikan') ? 'kelola_sekolah.php' : 'kelola_psikologi.php'; ?>?edit=<?php echo $up['page_id']; ?>" class="btn btn-sm btn-primary" title="Edit Konten">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>
<!-- DASHBOARD KHUSUS ADMIN UNIT -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <div class="card" style="border-left: 5px solid #00A2E9;">
        <h3 style="color: #0F3C5C; margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-clipboard-check" style="color: #00A2E9;"></i> Status Halaman Unit Anda
        </h3>
        <p style="color: #64748b; font-size: 0.95rem;">
            Berikut adalah ringkasan progress konten halaman website untuk unit <b><?php echo htmlspecialchars($_SESSION['admin_unit_name'] ?? $unitSlug); ?></b>:
        </p>

        <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div style="background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0;">
                <div style="font-size: 0.85rem; color: #64748b;">Status Konten</div>
                <div style="font-size: 1.1rem; font-weight: 700; color: #10b981; margin-top: 5px;">
                    <i class="fas fa-check-circle"></i> Sudah Terisi (<?php echo number_format($myPage['content_len'] ?? 0); ?> bytes)
                </div>
            </div>
            <div style="background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0;">
                <div style="font-size: 0.85rem; color: #64748b;">Galeri Dokumentasi</div>
                <div style="font-size: 1.1rem; font-weight: 700; color: #0284c7; margin-top: 5px;">
                    <i class="fas fa-camera"></i> <?php echo $myPhotos; ?> Foto Terupload
                </div>
            </div>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <?php if (isset($myPage['id'])): ?>
                <a href="<?php echo ($_SESSION['admin_type'] === 'pendidikan') ? 'kelola_sekolah.php' : 'kelola_psikologi.php'; ?>?edit=<?php echo $myPage['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Teks & Informasi Halaman
                </a>
            <?php endif; ?>
            <a href="kelola_galeri_halaman.php" class="btn btn-outline">
                <i class="fas fa-images"></i> Kelola Foto Galeri
            </a>
            <a href="/talenta-semarang/page.php?slug=<?php echo htmlspecialchars($unitSlug); ?>" target="_blank" class="btn btn-outline">
                <i class="fas fa-external-link-alt"></i> Pratinjau di Website
            </a>
        </div>
    </div>

    <div class="card">
        <h4 style="color: #0F3C5C; margin-bottom: 15px;">
            <i class="fas fa-id-badge" style="color: #00A2E9;"></i> Informasi Akun SSO
        </h4>
        <div style="margin-bottom: 15px;">
            <div style="font-size: 0.85rem; color: #64748b;">Nama Petugas Admin:</div>
            <div style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
        </div>
        <div style="margin-bottom: 15px;">
            <div style="font-size: 0.85rem; color: #64748b;">Email Terdaftar:</div>
            <div style="font-weight: 600; color: #0070ad;"><i class="fab fa-google" style="color:#ea4335;"></i> <?php echo htmlspecialchars($_SESSION['admin_email'] ?? '-'); ?></div>
        </div>
        <div style="margin-bottom: 15px;">
            <div style="font-size: 0.85rem; color: #64748b;">Hak Akses Unit:</div>
            <span class="badge badge-primary"><?php echo htmlspecialchars($_SESSION['admin_unit_name'] ?? $unitSlug); ?></span>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
