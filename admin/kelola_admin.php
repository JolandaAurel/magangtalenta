<?php
require_once 'includes/header.php';

// Only Super Admin can access this page
if ($_SESSION['admin_type'] !== 'super') {
    echo "<div class='card'><h3 style='color:#ef4444;'><i class='fas fa-lock'></i> Akses Ditolak</h3><p>Hanya Super Administrator yang berhak mengelola akun dan tabel admin.</p><a href='index.php' class='btn btn-primary'>Kembali ke Dashboard</a></div>";
    require_once 'includes/footer.php';
    exit;
}

$success = '';
$error = '';

// Handle Toggle Status (Aktif / Non-Aktif)
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
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
}

// Handle Delete Admin
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id === 1) {
        $error = "Akun Super Admin utama tidak boleh dihapus!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Akun admin berhasil dihapus!";
    }
}

// Handle Add / Edit Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $unit = trim($_POST['unit'] ?? '');
        $unit_slug = trim($_POST['unit_slug'] ?? '');
        $role = trim($_POST['role'] ?? 'pendidikan');
        $status = trim($_POST['status'] ?? 'Aktif');
        
        if (empty($email) || empty($password) || empty($name) || empty($unit)) {
            $error = "Semua kolom wajib diisi!";
        } else {
            // Check unique email
            $chk = $pdo->prepare("SELECT id FROM admin_users WHERE email = ?");
            $chk->execute([$email]);
            if ($chk->fetch()) {
                $error = "Email sudah digunakan oleh admin lain!";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $pdo->prepare("INSERT INTO admin_users (email, password, password_hash, name, unit, unit_slug, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $ins->execute([$email, $password, $hash, $name, $unit, $unit_slug, $role, $status]);
                $success = "Akun admin baru berhasil ditambahkan!";
            }
        }
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $unit = trim($_POST['unit'] ?? '');
        $unit_slug = trim($_POST['unit_slug'] ?? '');
        $role = trim($_POST['role'] ?? 'pendidikan');
        $status = trim($_POST['status'] ?? 'Aktif');
        
        if ($id === 1) {
            $status = 'Aktif'; // Super admin cannot be deactivated
            $role = 'super';
        }
        
        // Check duplicate email
        $chk = $pdo->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
        $chk->execute([$email, $id]);
        if ($chk->fetch()) {
            $error = "Email sudah digunakan oleh admin lain!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $upd = $pdo->prepare("UPDATE admin_users SET email = ?, password = ?, password_hash = ?, name = ?, unit = ?, unit_slug = ?, role = ?, status = ? WHERE id = ?");
            $upd->execute([$email, $password, $hash, $name, $unit, $unit_slug, $role, $status, $id]);
            $success = "Data admin berhasil diperbarui!";
        }
    }
}

// Fetch all admins
$stmt = $pdo->query("SELECT * FROM admin_users ORDER BY id ASC");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count summary
$totalAdmins = count($admins);
$pendidikanCount = 0;
$psikologiCount = 0;
$superCount = 0;
$activeCount = 0;

foreach ($admins as $a) {
    if ($a['role'] === 'pendidikan') $pendidikanCount++;
    if ($a['role'] === 'psikologi') $psikologiCount++;
    if ($a['role'] === 'super') $superCount++;
    if ($a['status'] === 'Aktif') $activeCount++;
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="color: #0F3C5C; margin: 0; font-size: 1.6rem; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-users-cog" style="color: #00A2E9;"></i> Tabel & Kelola Akun Admin Unit
        </h2>
        <p style="color: #64748b; margin: 5px 0 0 0; font-size: 0.95rem;">
            Daftar kredensial & otorisasi Google SSO untuk seluruh unit Talenta Semarang.
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button onclick="openAddModal()" class="btn btn-success" style="box-shadow: 0 4px 10px rgba(16,185,129,0.3);">
            <i class="fas fa-user-plus"></i> Tambah Admin Baru
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
        <div style="width: 45px; height: 45px; border-radius: 10px; background: #e0f2fe; color: #00A2E9; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Total Akun Admin</div>
            <div style="font-size: 1.4rem; font-weight: 700; color: #0F3C5C;"><?php echo $totalAdmins; ?> Akun</div>
        </div>
    </div>
    
    <div class="card" style="margin: 0; border-left: 5px solid #0284c7; display: flex; align-items: center; gap: 15px; padding: 15px 20px;">
        <div style="width: 45px; height: 45px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Admin Pendidikan</div>
            <div style="font-size: 1.4rem; font-weight: 700; color: #0F3C5C;"><?php echo $pendidikanCount; ?> Unit</div>
        </div>
    </div>
    
    <div class="card" style="margin: 0; border-left: 5px solid #8b5cf6; display: flex; align-items: center; gap: 15px; padding: 15px 20px;">
        <div style="width: 45px; height: 45px; border-radius: 10px; background: #ede9fe; color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
            <i class="fas fa-brain"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Admin Layanan Psikologi</div>
            <div style="font-size: 1.4rem; font-weight: 700; color: #0F3C5C;"><?php echo $psikologiCount; ?> Unit</div>
        </div>
    </div>
    
    <div class="card" style="margin: 0; border-left: 5px solid #10b981; display: flex; align-items: center; gap: 15px; padding: 15px 20px;">
        <div style="width: 45px; height: 45px; border-radius: 10px; background: #dcfce7; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
            <i class="fas fa-check-double"></i>
        </div>
        <div>
            <div style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Status Aktif</div>
            <div style="font-size: 1.4rem; font-weight: 700; color: #10b981;"><?php echo $activeCount; ?> / <?php echo $totalAdmins; ?></div>
        </div>
    </div>
</div>

<!-- SEARCH & FILTER BAR -->
<div class="card" style="padding: 15px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div style="display: flex; align-items: center; gap: 10px; flex-grow: 1; max-width: 400px;">
        <i class="fas fa-search" style="color: #94a3b8;"></i>
        <input type="text" id="adminSearch" onkeyup="filterAdminTable()" class="form-control" placeholder="Cari nama, email, atau unit..." style="margin: 0; padding: 8px 12px;">
    </div>
    <div style="display: flex; gap: 8px;">
        <button onclick="filterCategory('all')" class="btn btn-sm btn-outline filter-btn active" id="btn-all">Semua</button>
        <button onclick="filterCategory('pendidikan')" class="btn btn-sm btn-outline filter-btn" id="btn-pendidikan">Pendidikan (10)</button>
        <button onclick="filterCategory('psikologi')" class="btn btn-sm btn-outline filter-btn" id="btn-psikologi">Psikologi (4)</button>
        <button onclick="filterCategory('super')" class="btn btn-sm btn-outline filter-btn" id="btn-super">Super Admin (1)</button>
    </div>
</div>

<!-- MAIN TABLE -->
<div class="card" style="padding: 0; overflow: hidden; border: 1px solid #e2e8f0;">
    <div style="padding: 18px 24px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 1.15rem; color: #0F3C5C; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-table" style="color: #00A2E9;"></i> Tabel Kredensial Admin Talenta
        </h3>
        <span style="font-size: 0.85rem; color: #64748b;">
            <i class="fab fa-google" style="color: #ea4335;"></i> Siap digunakan untuk Google SSO & Login Mandiri
        </span>
    </div>
    
    <div style="overflow-x: auto;">
        <table id="adminTable" style="margin-top: 0; width: 100%;">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th style="width: 60px; text-align: center;">Nomer</th>
                    <th style="min-width: 220px;">Email</th>
                    <th style="min-width: 170px;">Password</th>
                    <th style="min-width: 200px;">Nama</th>
                    <th style="min-width: 180px;">Unit</th>
                    <th style="width: 120px; text-align: center;">Status</th>
                    <th style="width: 130px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $index => $admin): ?>
                <tr class="admin-row" data-role="<?php echo htmlspecialchars($admin['role']); ?>" data-text="<?php echo strtolower(htmlspecialchars($admin['name'] . ' ' . $admin['email'] . ' ' . $admin['unit'])); ?>">
                    <!-- NOMER -->
                    <td style="text-align: center; font-weight: 700; color: #64748b;">
                        <?php echo $index + 1; ?>
                    </td>
                    
                    <!-- EMAIL -->
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fab fa-google" style="color: #ea4335; font-size: 1rem;"></i>
                            <span style="font-weight: 600; color: #0F3C5C; font-size: 0.95rem;"><?php echo htmlspecialchars($admin['email']); ?></span>
                            <button onclick="copyToClipboard('<?php echo htmlspecialchars($admin['email']); ?>', this)" title="Salin Email" class="btn btn-sm btn-outline" style="padding: 2px 6px; font-size: 0.75rem; border: none; background: #f1f5f9; color: #64748b;">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                    </td>
                    
                    <!-- PASSWORD -->
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="password" value="<?php echo htmlspecialchars($admin['password']); ?>" id="pwd-<?php echo $admin['id']; ?>" readonly style="border: none; background: transparent; font-family: monospace; font-size: 0.95rem; width: 100px; color: #334155;">
                            <button type="button" onclick="togglePasswordVisibility('pwd-<?php echo $admin['id']; ?>', this)" title="Lihat/Sembunyikan Password" class="btn btn-sm btn-outline" style="padding: 3px 7px; font-size: 0.8rem;">
                                <i class="far fa-eye"></i>
                            </button>
                            <button type="button" onclick="copyToClipboard('<?php echo htmlspecialchars($admin['password']); ?>', this)" title="Salin Password" class="btn btn-sm btn-outline" style="padding: 3px 7px; font-size: 0.8rem;">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                    </td>
                    
                    <!-- NAMA -->
                    <td>
                        <div style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($admin['name']); ?></div>
                        <small style="color: #94a3b8; font-size: 0.8rem;">Slug: <?php echo htmlspecialchars($admin['unit_slug']); ?></small>
                    </td>
                    
                    <!-- UNIT -->
                    <td>
                        <div style="font-weight: 500; color: #334155; margin-bottom: 4px;"><?php echo htmlspecialchars($admin['unit']); ?></div>
                        <?php if ($admin['role'] === 'super'): ?>
                            <span class="badge badge-amber"><i class="fas fa-crown"></i> Super Admin</span>
                        <?php elseif ($admin['role'] === 'pendidikan'): ?>
                            <span class="badge badge-primary"><i class="fas fa-graduation-cap"></i> Pendidikan</span>
                        <?php else: ?>
                            <span class="badge badge-purple"><i class="fas fa-brain"></i> Layanan Psikologi</span>
                        <?php endif; ?>
                    </td>
                    
                    <!-- STATUS -->
                    <td style="text-align: center;">
                        <?php if ($admin['status'] === 'Aktif'): ?>
                            <a href="kelola_admin.php?toggle_status=<?php echo $admin['id']; ?>" title="Klik untuk nonaktifkan" style="text-decoration: none;">
                                <span class="badge badge-success" style="cursor: pointer;"><i class="fas fa-check-circle"></i> Aktif</span>
                            </a>
                        <?php else: ?>
                            <a href="kelola_admin.php?toggle_status=<?php echo $admin['id']; ?>" title="Klik untuk aktifkan" style="text-decoration: none;">
                                <span class="badge badge-danger" style="cursor: pointer;"><i class="fas fa-ban"></i> Non-Aktif</span>
                            </a>
                        <?php endif; ?>
                    </td>
                    
                    <!-- AKSI -->
                    <td style="text-align: center;">
                        <button onclick='openEditModal(<?php echo json_encode($admin); ?>)' class="btn btn-sm btn-primary" title="Edit Admin">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php if ($admin['id'] !== 1): ?>
                            <a href="kelola_admin.php?delete=<?php echo $admin['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus akun admin unit <?php echo htmlspecialchars($admin['unit']); ?>?')" class="btn btn-sm btn-danger" title="Hapus Admin">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL TAMBAH ADMIN -->
<div id="addModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 16px; width: 90%; max-width: 550px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); position: relative;">
        <span onclick="closeAddModal()" style="position: absolute; top: 20px; right: 20px; cursor: pointer; font-size: 1.4rem; color: #94a3b8;">&times;</span>
        <h3 style="margin-top: 0; color: #0F3C5C; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-user-plus" style="color: #10b981;"></i> Tambah Akun Admin Baru
        </h3>
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">Daftarkan email Google dan akun otorisasi unit baru.</p>
        
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label>Email Google / Akun SSO <span style="color:#ef4444;">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="contoh: unit@talentasemarang.com" required>
            </div>
            
            <div class="form-group">
                <label>Password Akun <span style="color:#ef4444;">*</span></label>
                <input type="text" name="password" class="form-control" placeholder="contoh: unit123" required>
            </div>
            
            <div class="form-group">
                <label>Nama Admin <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="contoh: Admin Unit Baru" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Nama Unit <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="unit" class="form-control" placeholder="contoh: Unit Baru" required>
                </div>
                <div class="form-group">
                    <label>Slug URL Halaman <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="unit_slug" class="form-control" placeholder="contoh: unit-baru" required>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Kategori / Role</label>
                    <select name="role" class="form-control">
                        <option value="pendidikan">Pendidikan</option>
                        <option value="psikologi">Layanan Psikologi</option>
                        <option value="super">Super Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="Aktif">Aktif</option>
                        <option value="Non-Aktif">Non-Aktif</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeAddModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Admin</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT ADMIN -->
<div id="editModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 16px; width: 90%; max-width: 550px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); position: relative;">
        <span onclick="closeEditModal()" style="position: absolute; top: 20px; right: 20px; cursor: pointer; font-size: 1.4rem; color: #94a3b8;">&times;</span>
        <h3 style="margin-top: 0; color: #0F3C5C; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-edit" style="color: #00A2E9;"></i> Edit Akun Admin
        </h3>
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">Perbarui data kredensial atau hak akses admin unit.</p>
        
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group">
                <label>Email Google / Akun SSO <span style="color:#ef4444;">*</span></label>
                <input type="email" name="email" id="edit_email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Password Akun <span style="color:#ef4444;">*</span></label>
                <input type="text" name="password" id="edit_password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Nama Admin <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Nama Unit <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="unit" id="edit_unit" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Slug URL Halaman <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="unit_slug" id="edit_unit_slug" class="form-control" required>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Kategori / Role</label>
                    <select name="role" id="edit_role" class="form-control">
                        <option value="pendidikan">Pendidikan</option>
                        <option value="psikologi">Layanan Psikologi</option>
                        <option value="super">Super Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status" class="form-control">
                        <option value="Aktif">Aktif</option>
                        <option value="Non-Aktif">Non-Aktif</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui Admin</button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePasswordVisibility(fieldId, btn) {
    const field = document.getElementById(fieldId);
    const icon = btn.querySelector('i');
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(function() {
        const icon = btn.querySelector('i');
        const origClass = icon.className;
        icon.className = 'fas fa-check';
        setTimeout(function() {
            icon.className = origClass;
        }, 1500);
    });
}

function filterAdminTable() {
    const input = document.getElementById('adminSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.admin-row');
    rows.forEach(row => {
        const text = row.getAttribute('data-text');
        row.style.display = text.includes(input) ? '' : 'none';
    });
}

function filterCategory(category) {
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('btn-' + category).classList.add('active');
    
    const rows = document.querySelectorAll('.admin-row');
    rows.forEach(row => {
        const role = row.getAttribute('data-role');
        if (category === 'all' || role === category) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function openAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}

function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}

function openEditModal(admin) {
    document.getElementById('edit_id').value = admin.id;
    document.getElementById('edit_email').value = admin.email;
    document.getElementById('edit_password').value = admin.password;
    document.getElementById('edit_name').value = admin.name;
    document.getElementById('edit_unit').value = admin.unit;
    document.getElementById('edit_unit_slug').value = admin.unit_slug;
    document.getElementById('edit_role').value = admin.role;
    document.getElementById('edit_status').value = admin.status;
    
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

window.onclick = function(event) {
    const addM = document.getElementById('addModal');
    const editM = document.getElementById('editModal');
    if (event.target === addM) closeAddModal();
    if (event.target === editM) closeEditModal();
}
</script>

<?php require_once 'includes/footer.php'; ?>
