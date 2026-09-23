<?php
session_start();
require_once "../config/koneksi.php";

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($email == '' || $password == '' || $confirm_password == '') {
        $error = "Semua field wajib diisi!";
    } else {
        // VALIDASI PASSWORD
        $password_errors = [];
        
        if (strlen($password) < 8) {
            $password_errors[] = "Password minimal 8 karakter";
        }
        if (!preg_match("/[A-Z]/", $password)) {
            $password_errors[] = "Password harus mengandung minimal 1 huruf besar (A-Z)";
        }
        if (!preg_match("/[a-z]/", $password)) {
            $password_errors[] = "Password harus mengandung minimal 1 huruf kecil (a-z)";
        }
        if (!preg_match("/[0-9]/", $password)) {
            $password_errors[] = "Password harus mengandung minimal 1 angka (0-9)";
        }
        if (!preg_match("/[!@#$%^&*(),.?\":{}|<>_\-+=\[\]\/\\\\]/", $password)) {
            $password_errors[] = "Password harus mengandung minimal 1 karakter spesial (!@#$%^&* dll)";
        }
        
        if ($password !== $confirm_password) {
            $password_errors[] = "Password dan konfirmasi password tidak sama";
        }
        
        if (!empty($password_errors)) {
            $error = implode("<br>", $password_errors);
        } else {
            // CEK EMAIL
            $email_escaped = mysqli_real_escape_string($conn, $email);
            $cek = mysqli_query($conn, "SELECT id FROM users WHERE email='$email_escaped'");

            if (mysqli_num_rows($cek) > 0) {
                $error = "Email sudah terdaftar!";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $insert = mysqli_query($conn,
                    "INSERT INTO users (email, password)
                     VALUES ('$email_escaped','$hash')"
                );

                if ($insert) {
                    header("Location: login.php?register=success");
                    exit;
                } else {
                    $error = "Gagal register! Cek database.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - PT. Sarana Karya Dua Satu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary-color: #1e40af;
        --secondary-color: #2563eb;
        --accent-color: #60a5fa;
        --gradient-start: #1e40af;
        --gradient-end: #3b82f6;
        --yellow: #60a5fa;
    }

    body {
        background: linear-gradient(135deg, var(--gradient-start) 0%, var(--secondary-color) 50%, var(--accent-blue) 100%);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
        padding: 20px 0;
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

    .register-container {
        position: relative;
        z-index: 1;
    }

    .register-card {
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
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 50%, var(--accent-blue) 100%);
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
        border: 4px solid var(--accent-blue);
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

    .benefits-list {
        text-align: left;
        margin-top: 30px;
        padding: 0 20px;
        position: relative;
        z-index: 1;
    }

    .benefit-item {
        margin-bottom: 15px;
        font-size: 14px;
        display: flex;
        align-items: center;
        background: rgba(96, 165, 250, 0.15);
        padding: 10px 15px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .benefit-item:hover {
        background: rgba(96, 165, 250, 0.25);
        transform: translateX(5px);
    }

    .benefit-item i {
        margin-right: 10px;
        font-size: 16px;
        color: var(--accent-blue);
    }

    .form-section {
        padding: 40px;
        max-height: 90vh;
        overflow-y: auto;
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
        margin-bottom: 25px;
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

    .form-control.is-invalid {
        border-color: #ef4444;
    }

    .form-control.is-valid {
        border-color: #2563eb;
    }

    .input-group {
        margin-bottom: 15px;
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

    /* Password Strength Indicator */
    .password-strength {
        margin-top: 8px;
        margin-bottom: 15px;
    }

    .strength-bar {
        height: 5px;
        background: #e2e8f0;
        border-radius: 3px;
        overflow: hidden;
        margin-top: 8px;
    }

    .strength-fill {
        height: 100%;
        transition: all 0.3s ease;
        width: 0%;
        border-radius: 3px;
    }

    .strength-text {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    /* Password Requirements */
    .password-requirements {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .password-requirements-title {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 10px;
    }

    .requirement-item {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
    }

    .requirement-item i {
        margin-right: 8px;
        font-size: 14px;
    }

    .requirement-item.met {
        color: #2563eb;
    }

    .requirement-item.met i {
        color: #2563eb;
    }

    .btn-register {
        background: linear-gradient(135deg, var(--accent-blue) 0%, #f59e0b 100%);
        border: none;
        border-radius: 10px;
        padding: 14px;
        font-weight: 600;
        font-size: 16px;
        color: #1f2937;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(96, 165, 250, 0.4);
        position: relative;
        overflow: hidden;
    }

    .btn-register::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s ease;
    }

    .btn-register:hover::before {
        left: 100%;
    }

    .btn-register:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(96, 165, 250, 0.6);
        background: linear-gradient(135deg, #f59e0b 0%, #1d4ed8 100%);
    }

    .btn-register:active {
        transform: translateY(0);
    }

    .btn-register:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .alert {
        border-radius: 10px;
        border: none;
        padding: 12px 15px;
        font-size: 14px;
    }

    .alert-danger {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .login-link {
        text-align: center;
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid #e2e8f0;
    }

    .login-link a {
        color: var(--secondary-color);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .login-link a:hover {
        color: var(--primary-color);
    }

    @media (max-width: 768px) {
        .logo-section {
            padding: 30px 20px;
        }

        .benefits-list {
            display: none;
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

<div class="container register-container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-9">
            <div class="card register-card">
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
                                
                                <div class="benefits-list">
                                    <div class="benefit-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Akses sistem 24/7</span>
                                    </div>
                                    <div class="benefit-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Manajemen inventory real-time</span>
                                    </div>
                                    <div class="benefit-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Laporan lengkap & akurat</span>
                                    </div>
                                    <div class="benefit-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Support tim profesional</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Section -->
                    <div class="col-md-7">
                        <div class="form-section">
                            <h2 class="form-title">Daftar Akun Baru</h2>
                            <p class="form-subtitle">Buat akun untuk mengakses sistem</p>

                            <?php if($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                            </div>
                            <?php endif; ?>

                            <form method="POST" id="registerForm">
                                <div class="input-group position-relative">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" name="email" id="email" class="form-control with-icon" 
                                           placeholder="Alamat Email" required>
                                </div>

                                <div class="input-group position-relative">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" name="password" id="password" 
                                           class="form-control with-icon" 
                                           placeholder="Password" required>
                                </div>

                                <!-- Password Strength Indicator -->
                                <div class="password-strength" id="strengthIndicator" style="display: none;">
                                    <div class="strength-text">
                                        Kekuatan Password: <span id="strengthLevel">-</span>
                                    </div>
                                    <div class="strength-bar">
                                        <div class="strength-fill" id="strengthBar"></div>
                                    </div>
                                </div>

                                <!-- Password Requirements -->
                                <div class="password-requirements">
                                    <div class="password-requirements-title">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Syarat Password:
                                    </div>
                                    <div class="requirement-item" id="req-length">
                                        <i class="fas fa-circle"></i>
                                        <span>Minimal 8 karakter</span>
                                    </div>
                                    <div class="requirement-item" id="req-upper">
                                        <i class="fas fa-circle"></i>
                                        <span>Minimal 1 huruf besar (A-Z)</span>
                                    </div>
                                    <div class="requirement-item" id="req-lower">
                                        <i class="fas fa-circle"></i>
                                        <span>Minimal 1 huruf kecil (a-z)</span>
                                    </div>
                                    <div class="requirement-item" id="req-number">
                                        <i class="fas fa-circle"></i>
                                        <span>Minimal 1 angka (0-9)</span>
                                    </div>
                                    <div class="requirement-item" id="req-special">
                                        <i class="fas fa-circle"></i>
                                        <span>Minimal 1 karakter spesial (!@#$%^&*)</span>
                                    </div>
                                </div>

                                <div class="input-group position-relative">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" name="confirm_password" id="confirm_password" 
                                           class="form-control with-icon" 
                                           placeholder="Konfirmasi Password" required>
                                    <small id="confirmPasswordFeedback" class="text-danger" style="display: none; margin-top: 5px; margin-left: 5px;"></small>
                                </div>

                                <button type="submit" class="btn btn-register w-100" id="submitBtn">
                                    <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                                </button>
                            </form>

                            <div class="login-link">
                                <span style="color: #64748b;">Sudah punya akun? </span>
                                <a href="login.php">Masuk Sekarang</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const strengthIndicator = document.getElementById('strengthIndicator');
    const strengthBar = document.getElementById('strengthBar');
    const strengthLevel = document.getElementById('strengthLevel');
    const submitBtn = document.getElementById('submitBtn');
    const confirmPasswordFeedback = document.getElementById('confirmPasswordFeedback');
    
    let requirements = {
        length: false,
        upper: false,
        lower: false,
        number: false,
        special: false
    };

    // Password validation on input
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        if (password.length > 0) {
            strengthIndicator.style.display = 'block';
        } else {
            strengthIndicator.style.display = 'none';
        }
        
        // Check requirements
        requirements.length = password.length >= 8;
        requirements.upper = /[A-Z]/.test(password);
        requirements.lower = /[a-z]/.test(password);
        requirements.number = /[0-9]/.test(password);
        requirements.special = /[!@#$%^&*(),.?":{}|<>_\-+=\[\]\/\\]/.test(password);
        
        // Update requirement indicators
        updateRequirement('req-length', requirements.length);
        updateRequirement('req-upper', requirements.upper);
        updateRequirement('req-lower', requirements.lower);
        updateRequirement('req-number', requirements.number);
        updateRequirement('req-special', requirements.special);
        
        // Calculate strength
        const metCount = Object.values(requirements).filter(v => v).length;
        updateStrengthBar(metCount);
        
        // Update password field styling
        if (metCount === 5) {
            passwordInput.classList.remove('is-invalid');
            passwordInput.classList.add('is-valid');
        } else if (password.length > 0) {
            passwordInput.classList.remove('is-valid');
            passwordInput.classList.add('is-invalid');
        } else {
            passwordInput.classList.remove('is-valid', 'is-invalid');
        }
        
        // Check confirm password match
        checkPasswordMatch();
        updateSubmitButton();
    });

    // Confirm password validation
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);

    function updateRequirement(id, met) {
        const element = document.getElementById(id);
        const icon = element.querySelector('i');
        
        if (met) {
            element.classList.add('met');
            icon.className = 'fas fa-check-circle';
        } else {
            element.classList.remove('met');
            icon.className = 'fas fa-circle';
        }
    }

    function updateStrengthBar(count) {
        const percentage = (count / 5) * 100;
        strengthBar.style.width = percentage + '%';
        
        if (count <= 2) {
            strengthBar.style.background = '#ef4444';
            strengthLevel.textContent = 'Lemah';
            strengthLevel.style.color = '#ef4444';
        } else if (count <= 4) {
            strengthBar.style.background = '#f59e0b';
            strengthLevel.textContent = 'Sedang';
            strengthLevel.style.color = '#f59e0b';
        } else {
            strengthBar.style.background = '#2563eb';
            strengthLevel.textContent = 'Kuat';
            strengthLevel.style.color = '#2563eb';
        }
    }

    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword.length > 0) {
            if (password !== confirmPassword) {
                confirmPasswordInput.classList.remove('is-valid');
                confirmPasswordInput.classList.add('is-invalid');
                confirmPasswordFeedback.style.display = 'block';
                confirmPasswordFeedback.textContent = 'Password tidak sama';
            } else {
                confirmPasswordInput.classList.remove('is-invalid');
                confirmPasswordInput.classList.add('is-valid');
                confirmPasswordFeedback.style.display = 'none';
            }
        } else {
            confirmPasswordInput.classList.remove('is-valid', 'is-invalid');
            confirmPasswordFeedback.style.display = 'none';
        }
        
        updateSubmitButton();
    }

    function updateSubmitButton() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const allRequirementsMet = Object.values(requirements).every(v => v);
        const passwordsMatch = password === confirmPassword && confirmPassword.length > 0;
        
        if (allRequirementsMet && passwordsMatch) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    }

    // Initial state
    updateSubmitButton();
});
</script>
</body>
</html>