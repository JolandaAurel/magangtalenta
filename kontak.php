<?php
// kontak.php
require_once 'includes/header.php';

// Fetch settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);


$alamat = $settings['address'] ?? 'Jl. Puspowarno Tengah IX No.6, Semarang Barat';
$telepon = $settings['contact_phone'] ?? '024-760-1502';
$whatsapp = $settings['contact_whatsapp'] ?? '0817838939';
$email = $settings['contact_email'] ?? 'talenta.lpt@gmail.com';
$jam = $settings['operational_hours'] ?? 'Senin - Jumat: 08.00 - 16.00 WIB | Sabtu: 08.00 - 13.00 WIB';
?>

<div class="page-header" style="text-align: center; padding: 60px 20px 30px;">
    <h1 style="font-size: 3rem; color: var(--primary-blue, #00A2E9); font-weight: 800;">Hubungi Kami</h1>
    <p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 10px auto 0;">Kami siap membantu dan menjawab pertanyaan Anda seputar program pendidikan dan layanan psikologi Talenta Semarang.</p>
</div>

<div style="max-width: 1000px; margin: 30px auto 80px; padding: 0 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
    
    <!-- Contact Info -->
    <div style="background: #fff; border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
        <h3 style="font-size: 1.5rem; margin-bottom: 25px; color: #0F3C5C; font-weight: 700;">Informasi Kontak</h3>
        
        <div style="display: flex; gap: 15px; align-items: flex-start; margin-bottom: 20px;">
            <div style="color: #00A2E9; font-size: 1.4rem; width: 30px; text-align: center;"><i class="fas fa-map-marker-alt"></i></div>
            <div>
                <strong style="color: #1e293b; display: block; margin-bottom: 4px;">Alamat Kampus</strong>
                <p style="color: #64748b; margin: 0; line-height: 1.6;"><?php echo htmlspecialchars($alamat); ?></p>
            </div>
        </div>

        <div style="display: flex; gap: 15px; align-items: flex-start; margin-bottom: 20px;">
            <div style="color: #10b981; font-size: 1.4rem; width: 30px; text-align: center;"><i class="fab fa-whatsapp"></i></div>
            <div>
                <strong style="color: #1e293b; display: block; margin-bottom: 4px;">WhatsApp Resmi</strong>
                <p style="color: #64748b; margin: 0;"><a href="https://wa.me/62<?php echo ltrim(preg_replace('/[^0-9]/', '', $whatsapp), '0'); ?>" target="_blank" style="color: #10b981; text-decoration: none; font-weight: 600;"><?php echo htmlspecialchars($whatsapp); ?></a></p>
            </div>
        </div>

        <div style="display: flex; gap: 15px; align-items: flex-start; margin-bottom: 20px;">
            <div style="color: #00A2E9; font-size: 1.4rem; width: 30px; text-align: center;"><i class="fas fa-phone-alt"></i></div>
            <div>
                <strong style="color: #1e293b; display: block; margin-bottom: 4px;">Telepon Kantor</strong>
                <p style="color: #64748b; margin: 0;"><?php echo htmlspecialchars($telepon); ?></p>
            </div>
        </div>

        <div style="display: flex; gap: 15px; align-items: flex-start; margin-bottom: 20px;">
            <div style="color: #ef4444; font-size: 1.4rem; width: 30px; text-align: center;"><i class="fas fa-envelope"></i></div>
            <div>
                <strong style="color: #1e293b; display: block; margin-bottom: 4px;">Email</strong>
                <p style="color: #64748b; margin: 0;"><a href="mailto:<?php echo htmlspecialchars($email); ?>" style="color: #0070ad; text-decoration: none;"><?php echo htmlspecialchars($email); ?></a></p>
            </div>
        </div>

        <div style="display: flex; gap: 15px; align-items: flex-start;">
            <div style="color: #f59e0b; font-size: 1.4rem; width: 30px; text-align: center;"><i class="far fa-clock"></i></div>
            <div>
                <strong style="color: #1e293b; display: block; margin-bottom: 4px;">Jam Operasional</strong>
                <p style="color: #64748b; margin: 0; line-height: 1.6;"><?php echo htmlspecialchars($jam); ?></p>
            </div>
        </div>
    </div>

    <!-- Contact Form -->
    <div style="background: #fff; border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
        <h3 style="font-size: 1.5rem; margin-bottom: 25px; color: #0F3C5C; font-weight: 700;">Kirim Pesan WhatsApp</h3>
        <form onsubmit="kirimPesanWa(event)" style="display: flex; flex-direction: column; gap: 16px;">
            <div>
                <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 5px; color: #334155;">Nama Lengkap</label>
                <input type="text" id="waNama" placeholder="Nama Anda" required style="width: 100%; padding: 12px 15px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-family: inherit; box-sizing: border-box;">
            </div>
            <div>
                <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 5px; color: #334155;">Unit / Layanan yang Dituju</label>
                <select id="waUnit" style="width: 100%; padding: 12px 15px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-family: inherit; box-sizing: border-box; background: white;">
                    <option value="Umum">Informasi Umum</option>
                    <option value="Pendidikan (SD, SMP, SMA, Daycare, KB-TK)">Pendidikan (SD, SMP, SMA, Daycare, KB-TK)</option>
                    <option value="Sekolah Inklusi">Sekolah Inklusi</option>
                    <option value="Layanan Psikologi & Terapi ABK">Layanan Psikologi & Terapi ABK</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 5px; color: #334155;">Pesan Anda</label>
                <textarea id="waPesan" placeholder="Tuliskan pertanyaan atau konsultasi Anda..." rows="4" required style="width: 100%; padding: 12px 15px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-family: inherit; resize: vertical; box-sizing: border-box;"></textarea>
            </div>
            <button type="submit" class="btn-primary" style="justify-content: center; padding: 14px; border-radius: 10px; font-weight: 700; background: #10b981; border: none; color: white; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                <i class="fab fa-whatsapp" style="font-size: 1.2rem;"></i> Kirim via WhatsApp
            </button>
        </form>
    </div>

</div>

<script>
function kirimPesanWa(e) {
    e.preventDefault();
    const nama = document.getElementById('waNama').value;
    const unit = document.getElementById('waUnit').value;
    const pesan = document.getElementById('waPesan').value;
    const phone = "62<?php echo ltrim(preg_replace('/[^0-9]/', '', $whatsapp), '0'); ?>";
    
    const text = `Halo Admin Talenta Semarang,%0A%0ANama: ${encodeURIComponent(nama)}%0AUnit yang dituju: ${encodeURIComponent(unit)}%0APesan:%0A${encodeURIComponent(pesan)}`;
    window.open(`https://wa.me/${phone}?text=${text}`, '_blank');
}
</script>

<?php require_once 'includes/footer.php'; ?>
