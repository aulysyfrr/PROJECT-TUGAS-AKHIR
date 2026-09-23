<?php
session_start();
require_once "config/koneksi.php";

if (!isset($_SESSION['login'])) {
    header("Location: auth/login.php");
    exit;
}

// Ambil email dari session untuk ditampilkan
$email = $_SESSION['email'] ?? $_SESSION['username'] ?? 'User';
$nama_tampil = $email;

// Jika ingin ambil nama lengkap dari database (opsional)
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $query = "SELECT nama, email FROM users WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($koneksi, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $nama_tampil = !empty($row['nama']) ? $row['nama'] : $row['email'];
    }
}

// Hilangkan @gmail.com atau domain email lainnya
if (strpos($nama_tampil, '@') !== false) {
    $nama_tampil = strstr($nama_tampil, '@', true);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | PT. Sarana Karya Dua Satu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #2563eb;
            --dark-blue: #1e40af;
            --light-blue: #dbeafe;
            --accent: #60a5fa;
            --accent-blue-dark: #1d4ed8;
            --accent-blue-light: #eff6ff;
            --gradient-1: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            --gradient-2: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.16);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Decorative Background Elements */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(96,165,250,0.1) 0%, transparent 70%);
            animation: pulse 15s ease-in-out infinite;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            bottom: -50%;
            left: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(32,201,151,0.1) 0%, transparent 70%);
            animation: pulse 20s ease-in-out infinite reverse;
            z-index: 0;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .container {
            position: relative;
            z-index: 1;
        }

        /* Navbar/Header */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-md);
            border-radius: 16px;
            padding: 1rem 2rem;
            margin-bottom: 2rem;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .navbar-brand img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .company-info h4 {
            color: var(--dark-blue);
            font-weight: 700;
            font-size: 1.3rem;
            margin: 0;
            line-height: 1.2;
        }

        .company-info small {
            color: #6c757d;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: var(--light-blue);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            box-shadow: var(--shadow-sm);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--gradient-1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px rgba(37,99,235,0.3);
        }

        .user-name {
            font-weight: 600;
            color: var(--dark-blue);
        }

        /* Welcome Section */
        .welcome-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 3rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255,255,255,0.8);
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: var(--gradient-1);
        }

        .welcome-section h2 {
            color: var(--dark-blue);
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .welcome-section p {
            color: #6c757d;
            font-size: 1.1rem;
            margin: 0;
        }

        .welcome-icon {
            font-size: 3rem;
            color: var(--accent);
            filter: drop-shadow(0 2px 4px rgba(96,165,250,0.3));
        }

        /* Menu Cards */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .menu-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            padding: 0;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255,255,255,0.8);
            cursor: pointer;
            position: relative;
        }

        .menu-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.1) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .menu-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: var(--shadow-lg);
        }

        .menu-card:hover::before {
            opacity: 1;
        }

        .card-header-custom {
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .card-header-yellow {
            background: var(--gradient-2);
        }

        .card-header-green {
            background: var(--gradient-1);
        }

        .card-icon-wrapper {
            width: 100px;
            height: 100px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .card-icon-bg {
            position: absolute;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            animation: iconPulse 3s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .card-icon {
            font-size: 4rem;
            color: white;
            position: relative;
            z-index: 1;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        }

        /* SVG Excavator Icon */
        .excavator-icon {
            width: 80px;
            height: 80px;
            fill: white;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        }

        .card-body-custom {
            padding: 2rem;
        }

        .card-title-custom {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #2c3e50;
        }

        .card-description {
            color: #6c757d;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .btn-menu {
            width: 100%;
            padding: 1rem;
            font-weight: 600;
            font-size: 1.05rem;
            border-radius: 12px;
            border: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-menu-yellow {
            background: var(--gradient-2);
            color: white;
            box-shadow: 0 4px 12px rgba(96,165,250,0.3);
        }

        .btn-menu-yellow:hover {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(96,165,250,0.4);
            color: white;
        }

        .btn-menu-green {
            background: var(--gradient-1);
            color: white;
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
        }

        .btn-menu-green:hover {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37,99,235,0.4);
            color: white;
        }

        /* Stats Badge */
        .stats-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255,255,255,0.9);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow: var(--shadow-sm);
        }

        /* Footer Section */
        .footer-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .footer-info {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .btn-logout {
            background: transparent;
            border: 2px solid var(--primary-blue);
            color: var(--primary-blue);
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-logout:hover {
            background: var(--primary-blue);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar-custom {
                padding: 1rem;
            }

            .company-info h4 {
                font-size: 1.1rem;
            }

            .company-info small {
                font-size: 0.75rem;
            }

            .welcome-section {
                padding: 1.5rem;
            }

            .welcome-section h2 {
                font-size: 1.5rem;
            }

            .menu-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .footer-section {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .user-profile {
                padding: 0.5rem 1rem;
            }
        }

        /* Loading Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .animate-delay-1 { animation-delay: 0.1s; opacity: 0; }
        .animate-delay-2 { animation-delay: 0.2s; opacity: 0; }
        .animate-delay-3 { animation-delay: 0.3s; opacity: 0; }
    </style>
</head>

<body>

<div class="container py-4">

    <!-- NAVBAR/HEADER -->
    <nav class="navbar-custom animate-fade-in">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-3">
            <div class="navbar-brand">
                <img src="assets/img/image.png" alt="Logo PT. Sarana Karya">
                <div class="company-info">
                    <h4>PT. Sarana Karya Dua Satu</h4>
                    <small>Sistem Informasi Manajemen Aset & Inventaris</small>
                </div>
            </div>
            <div class="user-profile">
                <div class="user-avatar">
                    <?= strtoupper(substr($nama_tampil, 0, 1)) ?>
                </div>
                <span class="user-name"><?= htmlspecialchars($nama_tampil) ?></span>
            </div>
        </div>
    </nav>

    <!-- WELCOME SECTION -->
    <div class="welcome-section animate-fade-in animate-delay-1">
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <div class="welcome-icon">
                👋
            </div>
            <div class="flex-grow-1">
                <h2>Selamat Datang, <?= htmlspecialchars($nama_tampil) ?>!</h2>
                <p>Kelola aset dan inventaris perusahaan dengan mudah dan efisien</p>
            </div>
        </div>
    </div>

    <!-- MENU GRID -->
    <div class="menu-grid">
        
        <!-- DATA ALAT BERAT -->
        <div class="menu-card animate-fade-in animate-delay-2" onclick="location.href='alat_berat.php'">
            <div class="card-header-custom card-header-yellow">
                <div class="stats-badge">
                    <i class="bi bi-gear-fill"></i> Aset Berat
                </div>
                <div class="card-icon-wrapper">
                    <div class="card-icon-bg"></div>
                    <!-- SVG Excavator Icon -->
                    <svg class="excavator-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 14v-3a1 1 0 0 0-1-1h-2.586l-2.707-2.707A1 1 0 0 0 13 7h-2a1 1 0 0 0-1 1v4H4a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 0-1-1h-1zm-8-5h1.586l2 2H12V9zm6 9H4v-4h6v-1a1 1 0 0 1 1-1h7v3h-1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1h1v1z"/>
                        <circle cx="6" cy="19" r="1.5"/>
                        <circle cx="16" cy="19" r="1.5"/>
                    </svg>
                </div>
            </div>
            <div class="card-body-custom">
                <h3 class="card-title-custom">Data Alat Berat</h3>
                <p class="card-description">
                    Kelola dan monitoring semua aset alat berat perusahaan seperti excavator, bulldozer, dan crane dengan sistem terintegrasi
                </p>
                <a href="alat_berat.php" class="btn-menu btn-menu-yellow">
                    <span>Buka Menu</span>
                    <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
            </div>
        </div>

        <!-- INVENTARIS -->
        <div class="menu-card animate-fade-in animate-delay-3" onclick="location.href='inventaris.php'">
            <div class="card-header-custom card-header-green">
                <div class="stats-badge">
                    <i class="bi bi-boxes"></i> Inventaris
                </div>
                <div class="card-icon-wrapper">
                    <div class="card-icon-bg"></div>
                    <i class="bi bi-box-seam-fill card-icon"></i>
                </div>
            </div>
            <div class="card-body-custom">
                <h3 class="card-title-custom">Data Inventaris</h3>
                <p class="card-description">
                    Catat dan kelola seluruh inventaris kantor, perlengkapan operasional, dan aset perusahaan lainnya dengan rapi
                </p>
                <a href="inventaris.php" class="btn-menu btn-menu-green">
                    <span>Buka Menu</span>
                    <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- FOOTER -->
    <div class="footer-section">
        <div class="footer-info">
            <i class="bi bi-calendar3"></i>
            <strong><?= date('l, d F Y') ?></strong> | 
            <i class="bi bi-clock"></i>
            <span id="current-time"></span>
        </div>
        <a href="auth/logout.php" class="btn-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Real-time clock
    function updateTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('current-time').textContent = `${hours}:${minutes}:${seconds}`;
    }
    
    updateTime();
    setInterval(updateTime, 1000);

    // Card click handler (backup for onclick)
    document.querySelectorAll('.menu-card').forEach(card => {
        card.addEventListener('click', function() {
            const link = this.querySelector('a.btn-menu');
            if (link) {
                window.location.href = link.href;
            }
        });
    });
</script>
</body>
</html>