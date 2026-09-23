<?php
session_start();
include "../config/koneksi.php";

$error = '';
$debug = ''; // untuk debugging

// Cek koneksi database
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Debug: cek email yang diinput
    // $debug = "Email: " . $email;

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (!$query) {
        $error = "Error query: " . mysqli_error($conn);
    } elseif (mysqli_num_rows($query) === 1) {
        $user = mysqli_fetch_assoc($query);

        // Debug: cek password hash
        // $debug .= " | Hash: " . substr($user['password'], 0, 20) . "...";

        if (password_verify($password, $user['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['email'] = $user['email'];
            
            // Simpan user_id jika ada
            if (isset($user['id'])) {
                $_SESSION['user_id'] = $user['id'];
            }

            // Redirect dengan absolute path atau coba berbagai kemungkinan
            header("Location: ../dashboard.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak terdaftar!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - PT. Sarana Karya Dua Satu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary-color: #1e40af;
        --secondary-color: #3b82f6;
        --accent-color: #60a5fa;
        --gradient-start: #1e293b;
        --gradient-end: #2563eb;
        --yellow: #60a5fa;
    }

    body {
        background: linear-gradient(135deg, var(--gradient-start) 0%, var(--secondary-color) 50%, var(--accent) 100%);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
        position: relative;
        overflow: hidden;
    }

    body::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(148, 163, 184, 0.08) 1px, transparent 1px);
        background-size: 50px 50px;
        animation: moveBackground 20s linear infinite;
    }

    @keyframes moveBackground {
        0% { transform: translate(0, 0); }
        100% { transform: translate(50px, 50px); }
    }

    .login-container {
        position: relative;
        z-index: 1;
    }

    .login-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        border: none;
        overflow: hidden;
        animation: slideUp 0.6s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .logo-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        padding: 40px 30px;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .logo-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(96, 165, 250, 0.15) 0%, transparent 70%);
        animation: rotateBg 15s linear infinite;
    }

    @keyframes rotateBg {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .logo-container {
        position: relative;
        z-index: 1;
    }

    .logo-circle {
        width: 120px;
        height: 120px;
        background: white;
        border-radius: 50%;
        margin: 0 auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        border: 4px solid var(--accent);
        animation: pulse 2s ease-in-out infinite;
        padding: 10px;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); }
        50% { transform: scale(1.05); box-shadow: 0 15px 40px rgba(96, 165, 250, 0.4); }
    }

    .logo-circle img {
        width: 90%;
        height: 90%;
        object-fit: contain;
        background: transparent;
        mix-blend-mode: multiply;
    }

    .company-name {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 5px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        position: relative;
        z-index: 1;
    }

    .company-tagline {
        font-size: 13px;
        opacity: 0.95;
        font-weight: 300;
        position: relative;
        z-index: 1;
    }

    .security-badge {
        margin-top: 25px;
        padding: 12px 20px;
        background: rgba(96, 165, 250, 0.2);
        border-radius: 10px;
        display: inline-block;
        position: relative;
        z-index: 1;
    }

    .security-badge i {
        color: var(--accent);
        font-size: 16px;
        margin-right: 8px;
    }

    .form-section {
        padding: 40px;
    }

    .form-title {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
        font-size: 28px;
        margin-bottom: 10px;
    }

    .form-subtitle {
        color: #64748b;
        font-size: 14px;
        margin-bottom: 30px;
    }

    .form-control {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .input-group {
        margin-bottom: 20px;
    }

    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        z-index: 10;
    }

    .form-control.with-icon {
        padding-left: 45px;
    }

    .btn-login {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border: none;
        border-radius: 10px;
        padding: 14px;
        font-weight: 600;
        font-size: 16px;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        position: relative;
        overflow: hidden;
    }

    .btn-login::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(96, 165, 250, 0.3), transparent);
        transition: left 0.5s ease;
    }

    .btn-login:hover::before {
        left: 100%;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
        background: linear-gradient(135deg, #1e293b 0%, #1e40af 100%);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .alert {
        border-radius: 10px;
        border: none;
        padding: 12px 15px;
    }

    .alert-danger {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .register-link {
        text-align: center;
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid #e2e8f0;
    }

    .register-link a {
        color: var(--secondary-color);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .register-link a:hover {
        color: var(--primary-color);
    }

    .forgot-password {
        text-align: right;
        margin-top: 10px;
        margin-bottom: 20px;
    }

    .forgot-password a {
        color: #64748b;
        text-decoration: none;
        font-size: 13px;
        transition: color 0.3s ease;
    }

    .forgot-password a:hover {
        color: var(--secondary-color);
    }

    @media (max-width: 768px) {
        .logo-section {
            padding: 30px 20px;
        }

        .form-section {
            padding: 30px 20px;
        }

        .company-name {
            font-size: 18px;
        }

        .form-title {
            font-size: 24px;
        }

        .logo-circle {
            width: 100px;
            height: 100px;
        }
    }
</style>
</head>
<body class="d-flex align-items-center">

<div class="container login-container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card login-card">
                <div class="row g-0">
                    <!-- Logo Section -->
                    <div class="col-md-5 d-flex align-items-center">
                        <div class="logo-section w-100">
                            <div class="logo-container">
                                <div class="logo-circle">
                                    <img src="../assets/img/image.png" alt="Logo PT. Sarana Karya Dua Satu">
                                </div>
                                <h1 class="company-name">PT. SARANA KARYA<br>DUA SATU</h1>
                                <p class="company-tagline">Sistem Manajemen Alat Berat Dan Inventaris</p>
                                <div class="security-badge">
                                    <i class="fas fa-shield-alt"></i>
                                    <span style="font-size: 13px;">Secure & Reliable</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Section -->
                    <div class="col-md-7">
                        <div class="form-section">
                            <h2 class="form-title">Selamat Datang</h2>
                            <p class="form-subtitle">Silakan masuk ke akun Anda</p>

                            <?php if($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                            </div>
                            <?php endif; ?>

                            <?php if(isset($debug) && $debug != ''): ?>
                            <div class="alert alert-info" role="alert">
                                <small>Debug: <?= $debug ?></small>
                            </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="input-group position-relative">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" name="email" class="form-control with-icon" 
                                           placeholder="Alamat Email" required>
                                </div>

                                <div class="input-group position-relative">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" name="password" class="form-control with-icon" 
                                           placeholder="Password" required>
                                </div>

                                <div class="forgot-password">
    <a href="lupa_password.php">
        <i class="fas fa-question-circle me-1"></i>Lupa Password?
    </a>
</div>


                                <button type="submit" class="btn btn-login w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Masuk
                                </button>
                            </form>

                            <div class="register-link">
                                <span style="color: #64748b;">Belum punya akun? </span>
                                <a href="register.php">Daftar Sekarang</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>