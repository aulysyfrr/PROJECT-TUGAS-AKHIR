<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

$result = mysqli_query($conn, "SELECT * FROM inventaris ORDER BY id DESC");
$total  = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Inventaris | PT. Sarana Karya Dua Satu</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --primary-blue: #2563eb;
    --primary-blue-dark: #1e40af;
    --primary-blue-light: #3b82f6;
    --accent-emerald: #34d399;
    --bg-light: #f0fdf4;
    --bg-card: #ffffff;
    --text-dark: #064e3b;
    --text-muted: #6b7280;
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
    --shadow-md: 0 4px 12px rgba(5, 150, 105, 0.15);
    --shadow-lg: 0 10px 30px rgba(5, 150, 105, 0.2);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    min-height: 100vh;
    color: #1f2937;
    padding: 20px 0;
}

/* Header Card */
.header-card {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-light) 100%);
    border-radius: 20px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-lg);
    color: white;
    position: relative;
    overflow: hidden;
}

.header-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    pointer-events: none;
}

.header-card .logo-section {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}

.header-card .logo-box {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 12px;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.header-card h1 {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 4px;
    letter-spacing: -0.5px;
}

.header-card .subtitle {
    font-size: 0.95rem;
    opacity: 0.95;
    font-weight: 400;
}

.header-card .company-name {
    font-size: 0.85rem;
    opacity: 0.9;
    font-weight: 500;
    letter-spacing: 0.5px;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-custom {
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn-primary-custom {
    background: white;
    color: var(--primary-blue);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-primary-custom:hover {
    background: #f9fafb;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    color: var(--primary-blue-dark);
}

.btn-outline-custom {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(10px);
}

.btn-outline-custom:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.6);
    color: white;
}

/* Stats Card */
.stats-card {
    background: var(--bg-card);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-md);
    border: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.stats-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-light) 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
    box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
}

.stats-content h3 {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--primary-blue);
    margin: 0;
    line-height: 1;
}

.stats-content p {
    margin: 4px 0 0 0;
    color: var(--text-muted);
    font-size: 0.9rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Table Card */
.table-card {
    background: var(--bg-card);
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-md);
    border: 1px solid #e5e7eb;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f3f4f6;
}

.table-header h5 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

.table-header h5 i {
    color: var(--primary-blue);
}

.table-info {
    color: var(--text-muted);
    font-size: 0.875rem;
    font-weight: 500;
}

/* Table Styling */
.table-responsive {
    border-radius: 12px;
    overflow: hidden;
}

.custom-table {
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.custom-table thead th {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-light) 100%);
    color: white;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px 12px;
    border: none;
    white-space: nowrap;
}

.custom-table thead th:first-child {
    border-top-left-radius: 12px;
}

.custom-table thead th:last-child {
    border-top-right-radius: 12px;
}

.custom-table tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid #f3f4f6;
}

.custom-table tbody tr:hover {
    background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%);
    transform: scale(1.01);
}

.custom-table tbody td {
    padding: 14px 12px;
    vertical-align: middle;
    font-size: 0.875rem;
}

/* Badge Styles */
.kode-badge {
    background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
    color: white;
    padding: 6px 14px;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-weight: 700;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    display: inline-block;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.kondisi-badge {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
}

.badge-baik {
    background: #dbeafe;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.badge-rusak {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

.badge-perbaikan {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* Action Buttons in Table */
.btn-action {
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 0.85rem;
    border: none;
    transition: all 0.2s ease;
    margin: 0 2px;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-action i {
    font-size: 0.9rem;
}

/* Footer Section */
.footer-section {
    text-align: center;
    margin-top: 32px;
}

.btn-back {
    background: white;
    color: var(--primary-blue);
    border: 2px solid var(--primary-blue);
    padding: 14px 32px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-sm);
}

.btn-back:hover {
    background: var(--primary-blue);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 16px;
}

/* Responsive */
@media (max-width: 768px) {
    .header-card {
        padding: 24px;
    }
    
    .header-card h1 {
        font-size: 1.5rem;
    }
    
    .action-buttons {
        width: 100%;
    }
    
    .btn-custom {
        flex: 1;
        justify-content: center;
    }
    
    .table-card {
        padding: 16px;
    }
    
    .custom-table {
        font-size: 0.8rem;
    }
}

/* Scrollbar Styling */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: var(--primary-blue);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--primary-blue-dark);
}
</style>
</head>

<body>
<div class="container-fluid px-3 px-md-4" style="max-width: 1400px; margin: 0 auto;">

<!-- HEADER -->
<div class="header-card">
    <div class="row align-items-center">
        <div class="col-lg-6 col-md-12">
            <div class="logo-section">
                <div class="logo-box">
                    <img src="assets/img/image.png" width="60" alt="Logo" style="display: block;">
                </div>
                <div>
                    <div class="company-name">PT. SARANA KARYA DUA SATU</div>
                    <h1>Data Inventaris</h1>
                    <div class="subtitle">Sistem Manajemen Aset & Inventaris Perusahaan</div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-12 mt-3 mt-lg-0">
            <div class="action-buttons justify-content-lg-end">
                <a href="database/inventaris_history.php" class="btn-custom btn-outline-custom">
                    <i class="bi bi-clock-history"></i> Riwayat
                </a>
                <a href="database/inventaris_export.php" class="btn-custom btn-outline-custom">
                    <i class="bi bi-file-earmark-excel"></i> Export
                </a>
                <a href="database/inventaris_tambah.php" class="btn-custom btn-primary-custom">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Data
                </a>
            </div>
        </div>
    </div>
</div>

<!-- STATS -->
<div class="stats-card">
    <div class="stats-icon">
        <i class="bi bi-box-seam"></i>
    </div>
    <div class="stats-content">
        <h3><?= $total ?></h3>
        <p>Total Inventaris Terdaftar</p>
    </div>
</div>

<!-- TABLE -->
<div class="table-card">
    <div class="table-header">
        <h5>
            <i class="bi bi-table"></i>
            Daftar Inventaris
        </h5>
        <div class="table-info">
            Menampilkan <?= $total ?> data inventaris
        </div>
    </div>

    <div class="table-responsive">
        <table class="table custom-table align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Tanggal</th>
                    <th class="text-center">Kode</th>
                    <th>Nama Barang</th>
                    <th>Jenis</th>
                    <th>Kategori</th>
                    <th class="text-end">Harga</th>
                    <th>Pengguna</th>
                    <th>Lokasi</th>
                    <th class="text-center">Kondisi</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if($total == 0): ?>
                <tr>
                    <td colspan="11">
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p class="mb-0">Belum ada data inventaris</p>
                        </div>
                    </td>
                </tr>
            <?php else: 
                $no = 1; 
                while($r = mysqli_fetch_assoc($result)): 
            ?>
                <tr>
                    <td class="text-center fw-semibold"><?= $no++ ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($r['tanggal_masuk'])) ?></td>
                    <td class="text-center">
                        <span class="kode-badge"><?= $r['kode_barang'] ?></span>
                    </td>
                    <td class="fw-semibold"><?= htmlspecialchars($r['nama_barang']) ?></td>
                    <td><?= htmlspecialchars($r['jenis_inventaris']) ?></td>
                    <td><?= htmlspecialchars($r['kategori']) ?></td>
                    <td class="text-end fw-bold" style="color: var(--primary-blue);">
                        Rp <?= number_format($r['harga_perolehan'], 0, ',', '.') ?>
                    </td>
                    <td><?= htmlspecialchars($r['pengguna_saat_ini']) ?></td>
                    <td><?= htmlspecialchars($r['lokasi']) ?></td>
                    <td class="text-center">
                        <?php
                        $k = $r['kondisi'];
                        $badgeClass = $k == 'Baik' ? 'badge-baik' : ($k == 'Rusak' ? 'badge-rusak' : 'badge-perbaikan');
                        ?>
                        <span class="kondisi-badge <?= $badgeClass ?>"><?= $k ?></span>
                    </td>
                    <td class="text-center">
                        <a href="database/inventaris_edit.php?id=<?= $r['id'] ?>" 
                           class="btn btn-warning btn-action" 
                           title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a href="database/inventaris_hapus.php?id=<?= $r['id'] ?>"
                           onclick="return confirm('Hapus <?= addslashes($r['nama_barang']) ?> (<?= $r['kode_barang'] ?>)?')"
                           class="btn btn-danger btn-action"
                           title="Hapus">
                            <i class="bi bi-trash3"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- FOOTER -->
<div class="footer-section">
    <a href="dashboard.php" class="btn-back">
        <i class="bi bi-arrow-left-circle-fill"></i>
        Kembali ke Dashboard
    </a>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>