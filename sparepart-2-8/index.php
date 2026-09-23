<?php
session_start();
require_once "config/koneksi.php";

if (!isset($_SESSION['login'])) {
    header("Location: auth/login.php");
    exit;
}

$email = $_SESSION['email'] ?? $_SESSION['username'] ?? 'User';
$nama_tampil = $email;

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $query = "SELECT nama, email FROM users WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $nama_tampil = !empty($row['nama']) ? $row['nama'] : $row['email'];
    }
}

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

<style>
body {
    background: #f4f7f6;
}
.navbar {
    background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
}
.navbar-brand {
    font-weight: 700;
}
.card-menu {
    border: none;
    transition: 0.3s;
}
.card-menu:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,.15);
}
.footer {
    font-size: 13px;
    color: #888;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark shadow">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
            <img src="assets/img/image.png" height="42">
            PT. Sarana Karya Dua Satu
        </a>

        <div class="text-white">
            <i class="bi bi-person-circle"></i>
            <?= htmlspecialchars($nama_tampil) ?>
        </div>
    </div>
</nav>

<!-- CONTENT -->
<div class="container mt-5">

    <!-- WELCOME -->
    <div class="alert shadow-sm" style="background-color: #dbeafe; border-color: #2563eb; color: #1e40af;">
        <h5 class="mb-1">
            👋 Selamat Datang, <strong><?= htmlspecialchars($nama_tampil) ?></strong>
        </h5>
        <small>
            Sistem Informasi Manajemen Aset & Inventaris Perusahaan
        </small>
    </div>

    <!-- MENU -->
    <div class="row g-4 mt-3">

        <!-- ALAT BERAT -->
        <div class="col-md-6">
            <div class="card card-menu shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-truck-front-fill fs-1 text-warning"></i>
                    <h5 class="mt-3 fw-bold">Data Alat Berat</h5>
                    <p class="text-muted">
                        Kelola aset alat berat perusahaan
                    </p>
                    <a href="alat_berat.php" class="btn btn-warning px-4">
                        <i class="bi bi-arrow-right-circle"></i> Masuk
                    </a>
                </div>
            </div>
        </div>

        <!-- INVENTARIS -->
        <div class="col-md-6">
            <div class="card card-menu shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam-fill fs-1" style="color: #2563eb;"></i>
                    <h5 class="mt-3 fw-bold">Inventaris</h5>
                    <p class="text-muted">
                        Kelola barang inventaris & perlengkapan
                    </p>
                    <a href="inventaris.php" class="btn px-4" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%); color: white; border: none;">
                        <i class="bi bi-arrow-right-circle"></i> Masuk
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- LOGOUT -->
    <div class="text-center mt-5">
        <a href="auth/logout.php" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>

</div>

<!-- FOOTER -->
<div class="text-center mt-5 footer mb-3">
    © <?= date('Y') ?> PT. Sarana Karya Dua Satu • All Rights Reserved
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
