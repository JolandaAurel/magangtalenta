<?php
require_once 'includes/header.php';

$images_dir = '../assets/images/';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_image']) && isset($_POST['target_file'])) {
    $target_file = basename($_POST['target_file']); // to prevent directory traversal
    $target_path = $images_dir . $target_file;
    
    if ($_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['new_image']['tmp_name'];
        if (move_uploaded_file($tmp_name, $target_path)) {
            $message = "<div style='background: #dcfce7; color: #166534; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Gambar <strong>$target_file</strong> berhasil diperbarui!</div>";
        } else {
            $message = "<div style='background: #fecaca; color: #991b1b; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>Gagal menyimpan gambar.</div>";
        }
    }
}

// Kategorisasi Gambar agar rapi

// Dynamic Banner Categories
$photo_categories = [
    'Banner Utama (Halaman Depan)' => [
        'hero_banner_final_edited.png' => 'Gambar Latar Atas (Hero Background)',
        'hero_kids_new.png' => 'Gambar Anak (Footer)'
    ],
    'Banner Publikasi' => [
        'galeri_banner.png' => 'Banner Galeri',
        'berita_banner.png' => 'Banner Berita & Kegiatan',
        'artikel_banner.png' => 'Banner Artikel'
    ]
];

// Fetch all pages dynamically to allow banner uploads for ALL pages
$stmt = $pdo->query("SELECT slug, title, category FROM pages ORDER BY id ASC");
$all_pages = $stmt->fetchAll(PDO::FETCH_OBJ);

$photo_categories['Banner Tentang Kami'] = [];
$photo_categories['Banner Pendidikan'] = [];
$photo_categories['Banner Layanan Psikologi'] = [];
$photo_categories['Banner Unit Lainnya (Baru)'] = [];

foreach ($all_pages as $p) {
    $img_name = str_replace('-', '_', $p->slug) . '_banner.png';
    if ($p->category === 'tentang-kami') {
        $photo_categories['Banner Tentang Kami'][$img_name] = 'Banner ' . $p->title;
    } elseif ($p->category === 'pendidikan') {
        $photo_categories['Banner Pendidikan'][$img_name] = 'Banner ' . $p->title;
    } elseif ($p->category === 'psikologi') {
        $photo_categories['Banner Layanan Psikologi'][$img_name] = 'Banner ' . $p->title;
    } else {
        $photo_categories['Banner Unit Lainnya (Baru)'][$img_name] = 'Banner ' . $p->title;
    }
}


?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Kelola Galeri & Foto (Ganti Gambar)</h3>
    </div>
    
    <?php echo $message; ?>
    
    <p style="margin-bottom: 30px; color: #64748b;">Di sini Anda dapat mengganti gambar <em>banner</em> atau gambar utama yang ada di website. <strong>Pilih kategori di bawah ini</strong>, cari gambar yang ingin diganti, lalu klik tombol "Ganti Gambar".</p>
    
    <?php foreach ($photo_categories as $cat_name => $images): ?>
        <h4 style="background: var(--primary); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 15px;"><?php echo $cat_name; ?></h4>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
            <?php foreach($images as $filename => $label): ?>
            <?php 
                // Cek apakah file ada, jika tidak, pakai placeholder kosong (meski harusnya ada)
                $file_path = $images_dir . $filename;
                $exists = file_exists($file_path);
            ?>
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #f8fafc; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <div style="height: 120px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: <?php echo $exists ? '#e2e8f0' : '#fee2e2'; ?>;">
                    <?php if ($exists): ?>
                        <img src="/talenta-semarang/assets/images/<?php echo $filename; ?>?t=<?php echo time(); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    <?php else: ?>
                        <span style="color: #ef4444; font-size: 0.8rem;">(Gambar belum ada)</span>
                    <?php endif; ?>
                </div>
                <div style="padding: 15px 10px;">
                    <p style="font-size: 0.85rem; font-weight: bold; color: var(--text); margin-bottom: 5px; line-height: 1.3; min-height: 35px;"><?php echo $label; ?></p>
                    <p style="font-size: 0.7rem; color: #64748b; margin-bottom: 10px; word-break: break-all;">(<?php echo $filename; ?>)</p>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="target_file" value="<?php echo $filename; ?>">
                        <input type="file" name="new_image" accept="image/*" required style="font-size: 0.75rem; width: 100%; margin-bottom: 10px; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px;">
                        <button type="submit" class="btn btn-primary" style="font-size: 0.8rem; width: 100%;"><i class="fas fa-upload"></i> Ganti Gambar</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
