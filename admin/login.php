<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$email_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $email_input = $email;
    
    if (empty($email) || empty($password)) {
        $error = "Silakan masukkan email dan password Anda!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && (password_verify($password, $user['password_hash']) || $password === $user['password'])) {
            if ($user['status'] !== 'Aktif') {
                $error = "Akun untuk unit " . htmlspecialchars($user['unit']) . " sedang dinonaktifkan oleh Super Admin!";
            } else {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_type'] = $user['role']; // 'super', 'pendidikan', 'psikologi'
                $_SESSION['admin_unit_slug'] = $user['unit_slug'];
                $_SESSION['admin_unit_name'] = $user['unit'];
                
                header("Location: index.php");
                exit;
            }
        } else {
            $error = "Email atau Password salah! Periksa kembali kredensial Anda.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Talenta Semarang</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #00A2E9;
            --secondary-blue: #0F3C5C;
            --accent-yellow: #f59e0b;
        }
        * { box-sizing: border-box; }
        body { 
            margin: 0; 
            font-family: 'Poppins', sans-serif; 
            background: url('../assets/images/admin_bg_new.png') center/cover no-repeat;
            background-attachment: fixed;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-container { 
            background: #ffffff; 
            width: 95vw; 
            max-width: 1050px; 
            min-height: 600px;
            border-radius: 30px; 
            box-shadow: 0 25px 60px rgba(0,0,0,0.12), 0 0 0 15px rgba(255,255,255,0.4); 
            overflow: hidden; 
            display: flex; 
            flex-direction: column;
            justify-content: center;
            position: relative;
            padding: 50px 60px;
            backdrop-filter: blur(20px);
        }
        
        .login-header { 
            text-align: center; 
            margin-bottom: 25px;
        }
        .login-header img {
            height: 70px;
            margin-bottom: 12px;
        }
        .login-header h2 {
            margin: 0 0 6px 0;
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-blue);
            letter-spacing: -0.5px;
        }
        .login-header p {
            margin: 0; 
            color: #64748b; 
            font-size: 0.95rem;
        }

        .login-box {
            max-width: 480px;
            margin: 0 auto;
            width: 100%;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid #fca5a5;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 20px;
            animation: shake 0.3s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }

        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 0.92rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-icon {
            position: absolute;
            left: 16px;
            color: #94a3b8;
            font-size: 1.05rem;
            pointer-events: none;
        }
        .form-control {
            width: 100%;
            padding: 14px 16px 14px 46px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 1rem;
            font-family: inherit;
            color: #1e293b;
            transition: all 0.2s ease;
            background: #f8fafc;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(0, 162, 233, 0.12);
        }
        .toggle-password-btn {
            position: absolute;
            right: 14px;
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }
        .toggle-password-btn:hover {
            color: var(--secondary-blue);
        }

        .btn-submit {
            width: 100%;
            background: var(--secondary-blue);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 14px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 25px;
            box-shadow: 0 4px 15px rgba(15, 60, 92, 0.2);
        }
        .btn-submit:hover {
            background: var(--primary-blue);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 162, 233, 0.3);
        }

        .login-footer-links {
            text-align: center;
            margin-top: 30px;
        }
        .login-footer-links a {
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }
        .login-footer-links a:hover {
            color: var(--primary-blue);
        }

        .info-pill {
            background: #f1f5f9;
            border-radius: 50px;
            padding: 8px 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            color: #475569;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <img src="../assets/images/logo.png" alt="Logo Talenta Semarang">
        <h2>Portal Keamanan Admin</h2>
        <p>Masukkan email dan password untuk masuk ke unit Anda</p>
    </div>

    <div class="login-box">
        <?php if ($error): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle" style="font-size: 1.25rem; flex-shrink: 0;"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- EMAIL INPUT -->
            <div class="form-group">
                <label for="emailInput">Alamat Email Unit</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope input-icon"></i>
                    <input 
                        type="email" 
                        name="email" 
                        id="emailInput" 
                        class="form-control" 
                        placeholder="contoh: sd@talentasemarang.com" 
                        value="<?php echo htmlspecialchars($email_input); ?>"
                        required 
                        autofocus
                    >
                </div>
            </div>

            <!-- PASSWORD INPUT -->
            <div class="form-group">
                <label for="passwordInput">Kata Sandi</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input 
                        type="password" 
                        name="password" 
                        id="passwordInput" 
                        class="form-control" 
                        placeholder="Masukkan password unit Anda" 
                        required
                    >
                    <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility()" title="Lihat/Sembunyikan Password">
                        <i class="far fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <button type="submit" class="btn-submit">
                <span>Masuk Sekarang</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="login-footer-links">
            <div>
                <a href="/outsource_talenta_landing/magangtalenta/">
                    <i class="fas fa-arrow-left"></i> Kembali ke Website Utama
                </a>
            </div>
            <div>
                <span class="info-pill">
                    <i class="fas fa-shield-alt" style="color: #00A2E9;"></i> Setiap email otomatis mengarahkan ke halaman unit masing-masing
                </span>
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('passwordInput');
    const eyeIcon = document.getElementById('eyeIcon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}
</script>

</body>
</html>