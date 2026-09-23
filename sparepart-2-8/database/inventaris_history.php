<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";

// PROSES RESTORE
if (isset($_GET['restore'])) {
    $id = intval($_GET['restore']);
    
    $query = "SELECT * FROM inventaris_history WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    
    if ($data) {
        // Gunakan prepared statement untuk keamanan
        $stmt = mysqli_prepare($conn, "INSERT INTO inventaris 
        (tanggal_masuk, kode_barang, nama_barang, jenis_inventaris, kategori, harga_perolehan, pengguna_saat_ini, lokasi, kondisi) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        mysqli_stmt_bind_param($stmt, "sssssdsss", 
            $data['tanggal_masuk'], 
            $data['kode_barang'], 
            $data['nama_barang'], 
            $data['jenis_inventaris'], 
            $data['kategori'], 
            $data['harga_perolehan'], 
            $data['pengguna_saat_ini'], 
            $data['lokasi'], 
            $data['kondisi']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_query($conn, "DELETE FROM inventaris_history WHERE id = $id");
            header("Location: inventaris_history.php?status=restored");
            exit;
        }
        mysqli_stmt_close($stmt);
    }
}

// PROSES HAPUS PERMANENT
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM inventaris_history WHERE id = $id");
    header("Location: inventaris_history.php?status=deleted");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM inventaris_history ORDER BY id DESC");
$total = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>History Inventaris | PT. Sarana Karya Dua Satu</title>
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
    --warning-amber: #f59e0b;
    --danger-red: #dc2626;
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

/* Alert Messages */
.alert-custom {
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
    border: none;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
}

.alert-success-custom {
    background: #dbeafe;
    color: #065f46;
}

.alert-danger-custom {
    background: #fee2e2;
    color: #991b1b;
}

.alert-custom i {
    font-size: 1.25rem;
}

/* Action Buttons */
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

.btn-back-custom {
    background: white;
    color: var(--primary-blue);
    border: 2px solid rgba(255, 255, 255, 0.4);
}

.btn-back-custom:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    color: var(--primary-blue-dark);
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
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.stats-content h3 {
    font-size: 2.5rem;
    font-weight: 800;
    color: #dc2626;
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
    color: #dc2626;
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
    background: linear-gradient(90deg, #eff6ff 0%, #dbeafe 100%);
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

/* Delete Badge */
.deleted-badge {
    background: #fee2e2;
    color: #991b1b;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Action Buttons in Table */
.btn-action {
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 0.85rem;
    border: none;
    transition: all 0.2s ease;
    margin: 0 2px;
    font-weight: 600;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-action i {
    font-size: 0.9rem;
}

.btn-restore {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-light) 100%);
    color: white;
}

.btn-restore:hover {
    background: var(--primary-blue-dark);
    color: white;
}

.btn-delete-permanent {
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    color: white;
}

.btn-delete-permanent:hover {
    background: #991b1b;
    color: white;
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

.empty-state h6 {
    font-weight: 600;
    color: #6b7280;
    margin-top: 12px;
}

/* Responsive */
@media (max-width: 768px) {
    .header-card {
        padding: 24px;
    }
    
    .header-card h1 {
        font-size: 1.5rem;
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
        <div class="col-lg-8 col-md-12">
            <div class="logo-section">
                <div class="logo-box">
                    <i class="bi bi-clock-history" style="font-size: 2rem; display: block;"></i>
                </div>
                <div>
                    <div class="company-name">PT. SARANA KARYA DUA SATU</div>
                    <h1>History Inventaris</h1>
                    <div class="subtitle">Data Inventaris yang Telah Dihapus</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 mt-3 mt-lg-0">
            <div class="d-flex justify-content-lg-end">
                <a href="../inventaris.php" class="btn-custom btn-back-custom">
                    <i class="bi bi-arrow-left-circle-fill"></i> Kembali ke Inventaris
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ALERT -->
<?php if (isset($_GET['status'])): ?>
    <?php if ($_GET['status'] == 'restored'): ?>
        <div class="alert-custom alert-success-custom">
            <i class="bi bi-check-circle-fill"></i>
            <span>Data berhasil dipulihkan ke inventaris utama!</span>
        </div>
    <?php elseif ($_GET['status'] == 'deleted'): ?>
        <div class="alert-custom alert-danger-custom">
            <i class="bi bi-trash-fill"></i>
            <span>Data berhasil dihapus secara permanen!</span>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- STATS -->
<div class="stats-card">
    <div class="stats-icon">
        <i class="bi bi-trash"></i>
    </div>
    <div class="stats-content">
        <h3><?= $total ?></h3>
        <p>Data Inventaris Terhapus</p>
    </div>
</div>

<!-- TABLE -->
<div class="table-card">
    <div class="table-header">
        <h5>
            <i class="bi bi-archive"></i>
            Daftar History Inventaris
        </h5>
        <div class="table-info">
            Menampilkan <?= $total ?> data terhapus
        </div>
    </div>

    <div class="table-responsive">
        <table class="table custom-table align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Tanggal Dihapus</th>
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
                            <h6>Tidak ada data history</h6>
                            <p class="mb-0 small">Belum ada inventaris yang dihapus</p>
                        </div>
                    </td>
                </tr>
            <?php else: 
                $no = 1; 
                while($row = mysqli_fetch_assoc($result)): 
            ?>
                <tr>
                    <td class="text-center fw-semibold"><?= $no++ ?></td>
                    <td class="text-center">
                        <?php if(isset($row['deleted_at']) && $row['deleted_at']): ?>
                            <?= date('d/m/Y', strtotime($row['deleted_at'])) ?>
                            <br>
                            <small class="text-muted"><?= date('H:i', strtotime($row['deleted_at'])) ?></small>
                        <?php elseif(isset($row['tanggal_masuk']) && $row['tanggal_masuk']): ?>
                            <?= date('d/m/Y', strtotime($row['tanggal_masuk'])) ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="kode-badge"><?= htmlspecialchars($row['kode_barang']) ?></span>
                    </td>
                    <td class="fw-semibold"><?= htmlspecialchars($row['nama_barang']) ?></td>
                    <td><?= htmlspecialchars($row['jenis_inventaris']) ?></td>
                    <td><?= htmlspecialchars($row['kategori']) ?></td>
                    <td class="text-end fw-bold" style="color: var(--primary-blue);">
                        Rp <?= number_format($row['harga_perolehan'], 0, ',', '.') ?>
                    </td>
                    <td><?= htmlspecialchars($row['pengguna_saat_ini']) ?></td>
                    <td><?= htmlspecialchars($row['lokasi']) ?></td>
                    <td class="text-center">
                        <?php
                        $k = $row['kondisi'];
                        $badgeClass = $k == 'Baik' ? 'badge-baik' : ($k == 'Rusak' ? 'badge-rusak' : 'badge-perbaikan');
                        ?>
                        <span class="kondisi-badge <?= $badgeClass ?>"><?= $k ?></span>
                    </td>
                    <td class="text-center">
                        <a href="?restore=<?= $row['id'] ?>" 
                           class="btn btn-restore btn-action" 
                           onclick="return confirm('Yakin ingin memulihkan data ini ke inventaris utama?')"
                           title="Restore">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                        <a href="?delete=<?= $row['id'] ?>"
                           onclick="return confirm('PERINGATAN! Data akan dihapus PERMANEN dan tidak dapat dipulihkan. Lanjutkan?')"
                           class="btn btn-delete-permanent btn-action"
                           title="Hapus Permanen">
                            <i class="bi bi-trash3-fill"></i>
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
    <a href="../inventaris.php" class="btn-back">
        <i class="bi bi-arrow-left-circle-fill"></i>
        Kembali ke Data Inventaris
    </a>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>