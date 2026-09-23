<?php
session_start();

/*
|====================================================
| KONEKSI DATABASE (PATH AMAN)
|====================================================
*/
require_once __DIR__ . "/config/koneksi.php";

/*
|====================================================
| AMBIL DATA ALAT BERAT
|====================================================
*/
// Cek koneksi dulu
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$result = mysqli_query($conn, "SELECT * FROM alat_berat ORDER BY id DESC");

// Cek apakah query berhasil
if ($result === false) {
    die("Query gagal: " . mysqli_error($conn));
}

$total_alat = mysqli_num_rows($result);

/*
|====================================================
| PREDIKSI RISIKO (ALGORITMA DECISION TREE)
|====================================================
| Fungsi bangunPohonKeputusanRisiko(), telusuriPohonKeputusan(),
| dan prediksiRisikoAlat() ada di file bersama supaya konsisten
| dengan halaman History (database/alat_berat_history.php).
*/
require_once __DIR__ . "/config/prediksi_risiko.php";

/*
|====================================================
| PAGINASI "SHEET" (10 DATA PER SHEET)
|====================================================
*/
const DATA_PER_SHEET = 10;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Alat Berat | PT. Sarana Karya Dua Satu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #60a5fa;
            --accent-blue-dark: #1d4ed8;
            --accent-blue-light: #eff6ff;
            --primary-blue: #2563eb;
            --dark-blue: #1e40af;
            --gradient-yellow: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            --gradient-green: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
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

        body::before {
            content: '';
            position: fixed;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 15s ease-in-out infinite;
            z-index: 0;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .container-fluid {
            position: relative;
            z-index: 1;
        }

        /* Header Section */
        .page-header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255,255,255,0.8);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: var(--gradient-green);
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .company-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 12px;
            background: white;
            padding: 0.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .title-content {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .company-name {
            color: #2c3e50;
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0;
            letter-spacing: 0.3px;
        }

        .page-title h2 {
            color: var(--dark-blue);
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0;
            line-height: 1.2;
        }

        .page-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn-custom {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
        }

        .btn-primary-custom {
            background: var(--gradient-green);
            color: white;
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
        }

        .btn-primary-custom:hover {
            box-shadow: 0 6px 16px rgba(37,99,235,0.4);
            color: white;
        }

        .btn-info-custom {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(23,162,184,0.3);
        }

        .btn-info-custom:hover {
            box-shadow: 0 6px 16px rgba(23,162,184,0.4);
            color: white;
        }

        .btn-success-custom {
            background: var(--gradient-green);
            color: white;
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
        }

        .btn-success-custom:hover {
            box-shadow: 0 6px 16px rgba(37,99,235,0.4);
            color: white;
        }

        .btn-secondary-custom {
            background: transparent;
            color: var(--dark-blue);
            border: 2px solid var(--dark-blue);
        }

        .btn-secondary-custom:hover {
            background: var(--dark-blue);
            color: white;
        }

        /* Statistics Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255,255,255,0.8);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .stat-card.yellow::before { background: var(--gradient-green); }
        .stat-card.green::before  { background: var(--gradient-green); }
        .stat-card.blue::before   { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); }
        .stat-card.red::before    { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .stat-card.yellow .stat-card-icon { background: var(--gradient-green);  box-shadow: 0 4px 12px rgba(37,99,235,0.2); }
        .stat-card.green .stat-card-icon  { background: var(--gradient-green);  box-shadow: 0 4px 12px rgba(37,99,235,0.2); }
        .stat-card.blue .stat-card-icon   { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); box-shadow: 0 4px 12px rgba(23,162,184,0.2); }
        .stat-card.red .stat-card-icon    { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); box-shadow: 0 4px 12px rgba(220,53,69,0.2); }

        .stat-card-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .stat-card h6 {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }

        .stat-card.yellow h3 { color: var(--dark-blue); }
        .stat-card.green h3  { color: var(--dark-blue); }
        .stat-card.blue h3   { color: #138496; }
        .stat-card.red h3    { color: #c82333; }

        /* Table Container */
        .table-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255,255,255,0.8);
            margin-bottom: 2rem;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .table-header h5 {
            color: var(--dark-blue);
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.3rem;
        }

        /* Table Styles */
        .table-responsive {
            border-radius: 12px;
            overflow-x: auto;
            margin: 0;
        }

        .custom-table {
            width: 100%;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.9rem;
        }

        .custom-table thead {
            background: var(--gradient-yellow) !important;
            position: sticky;
            top: 0;
            z-index: 100 !important;
        }

        .custom-table thead tr {
            background: var(--gradient-yellow) !important;
        }

        .custom-table thead th {
            color: white !important;
            font-weight: 700 !important;
            padding: 1.2rem 0.85rem !important;
            border: none !important;
            font-size: 0.85rem !important;
            white-space: normal !important;
            text-align: center !important;
            vertical-align: middle !important;
            text-transform: uppercase !important;
            letter-spacing: 0.8px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15) !important;
            border-right: 1px solid rgba(255,255,255,0.3) !important;
            line-height: 1.4 !important;
            background: var(--gradient-yellow) !important;
        }

        .custom-table thead th:last-child { border-right: none; }
        .custom-table thead th:first-child { border-radius: 12px 0 0 0; }
        .custom-table thead th:last-child  { border-radius: 0 12px 0 0; }

        .custom-table tbody tr {
            background: white;
            transition: all 0.3s ease;
            border-bottom: 1px solid #f0f0f0;
        }

        .custom-table tbody tr:hover {
            background: linear-gradient(to right, #f0fff4, #ffffff);
            transform: scale(1.005);
            box-shadow: 0 4px 12px rgba(37,99,235,0.15);
        }

        .custom-table tbody tr:last-child td:first-child { border-radius: 0 0 0 12px; }
        .custom-table tbody tr:last-child td:last-child  { border-radius: 0 0 12px 0; }

        .custom-table tbody td {
            padding: 1rem 0.85rem;
            font-size: 0.875rem;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }

        .custom-table tbody tr:last-child td { border-bottom: none; }

        /* Column Widths */
        .col-no       { width: 55px; text-align: center; font-weight: 700; color: var(--dark-blue); }
        .col-tanggal  { width: 110px; text-align: center; }
        .col-nama-pemilik { min-width: 170px; font-weight: 600; color: #2c3e50; }
        .col-jenis    { min-width: 140px; }
        .col-merk     { min-width: 140px; font-weight: 600; }
        .col-fleet    { min-width: 120px; text-align: center; }
        .col-dealer   { min-width: 140px; }
        .col-model    { min-width: 130px; }
        .col-mesin    { min-width: 155px; }
        .col-rangka   { min-width: 155px; }
        .col-tahun    { width: 100px; text-align: center; }
        .col-lokasi   { min-width: 120px; }

        /* NEW columns */
        .col-jam-operasional  { min-width: 140px; text-align: center; }
        .col-kondisi          { min-width: 130px; text-align: center; }
        .col-jumlah-perbaikan { width: 110px; text-align: center; }
        .col-riwayat          { min-width: 200px; }

        .col-aksi { width: 120px; text-align: center; }

        /* Badge Styles */
        .fleet-number {
            font-weight: 700;
            color: white;
            background: var(--gradient-green);
            padding: 0.35rem 0.85rem;
            border-radius: 8px;
            display: inline-block;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 6px rgba(37,99,235,0.3);
        }

        .badge-year {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 6px;
            font-weight: 600;
            display: inline-block;
            font-size: 0.85rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .location-badge {
            background: linear-gradient(135deg, #eff6ff 0%, #ffe69c 100%);
            color: #856404;
            padding: 0.3rem 0.75rem;
            border-radius: 6px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.85rem;
        }

        .date-badge {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1565c0;
            padding: 0.3rem 0.75rem;
            border-radius: 6px;
            font-weight: 600;
            display: inline-block;
            font-size: 0.85rem;
        }

        .code-text {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            border: 1px solid #e9ecef;
            color: #495057;
        }

        /* ===== NEW BADGES ===== */

        /* Jam Operasional */
        .jam-badge {
            background: linear-gradient(135deg, #e8f4fd 0%, #bee3f8 100%);
            color: #1a6fa3;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.85rem;
            box-shadow: 0 2px 6px rgba(26,111,163,0.15);
        }

        /* Kondisi Alat */
        .kondisi-badge {
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.82rem;
            letter-spacing: 0.3px;
        }

        .kondisi-baik {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e3a8a;
            box-shadow: 0 2px 6px rgba(21,87,36,0.15);
        }

        .kondisi-sedang {
            background: linear-gradient(135deg, #eff6ff 0%, #ffe69c 100%);
            color: #856404;
            box-shadow: 0 2px 6px rgba(133,100,4,0.15);
        }

        .kondisi-rusak {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            box-shadow: 0 2px 6px rgba(114,28,36,0.15);
        }

        .kondisi-perbaikan {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
            box-shadow: 0 2px 6px rgba(12,84,96,0.15);
        }

        /* Jumlah Perbaikan */
        .perbaikan-count {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1rem;
            box-shadow: 0 3px 8px rgba(0,0,0,0.15);
        }

        .count-0    { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1e3a8a; }
        .count-low  { background: linear-gradient(135deg, #eff6ff, #ffe69c); color: #856404; }
        .count-mid  { background: linear-gradient(135deg, #fde6cc, #fcd1a3); color: #7d4100; }
        .count-high { background: linear-gradient(135deg, #f8d7da, #f5c6cb); color: #721c24; }

        /* Hasil Prediksi Risiko (Decision Tree) */
        .risiko-badge {
            padding: 0.4rem 0.9rem;
            border-radius: 20px;
            font-weight: 800;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            white-space: nowrap;
        }
        .risiko-badge .risiko-skor {
            display: block;
            font-weight: 600;
            font-size: 0.68rem;
            opacity: 0.85;
        }
        .r-rendah { background: #dbeafe; color: #1e3a8a; }
        .r-sedang { background: #eff6ff; color: #856404; }
        .r-tinggi { background: #f8d7da; color: #721c24; }

        /* Sheet Tabs (pagination per 10 data) */
        .sheet-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1.5rem;
            justify-content: center;
        }

        .sheet-tab {
            padding: 0.5rem 1.1rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            color: var(--dark-blue);
            background: #eff6ff;
            border: 1px solid #dbeafe;
            transition: all 0.2s ease;
        }

        .sheet-tab:hover {
            background: #dbeafe;
            color: var(--dark-blue);
            transform: translateY(-2px);
        }

        .sheet-tab.active {
            background: var(--gradient-yellow);
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 10px rgba(37,99,235,0.3);
        }

        .sheet-tab.sheet-nav {
            font-weight: 700;
            padding: 0.5rem 0.85rem;
        }

        .sheet-tab.sheet-nav.disabled {
            opacity: 0.35;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Action Buttons */
        .btn-action {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            border: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-action:hover { transform: translateY(-2px); }

        .btn-edit {
            background: linear-gradient(135deg, #3b82f6 0%, #ff9800 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(96,165,250,0.3);
        }

        .btn-edit:hover { box-shadow: 0 4px 12px rgba(96,165,250,0.5); }

        .btn-delete {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(220,53,69,0.3);
        }

        .btn-delete:hover { box-shadow: 0 4px 12px rgba(220,53,69,0.5); }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }

        .empty-state i { font-size: 4rem; color: #dee2e6; margin-bottom: 1rem; }
        .empty-state h5 { color: #495057; font-weight: 600; margin-bottom: 0.5rem; }

        /* Back Button */
        .back-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            text-align: center;
            margin-bottom: 2rem;
        }

        .btn-back {
            background: transparent;
            border: 2px solid var(--dark-blue);
            color: var(--dark-blue);
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-back:hover {
            background: var(--dark-blue);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30,126,52,0.3);
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in    { animation: fadeInUp 0.6s ease-out forwards; }
        .animate-delay-1    { animation-delay: 0.1s; opacity: 0; }
        .animate-delay-2    { animation-delay: 0.2s; opacity: 0; }
        .animate-delay-3    { animation-delay: 0.3s; opacity: 0; }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header { padding: 1.5rem; }
            .page-title  { flex-direction: row; align-items: center; gap: 1rem; }
            .company-logo { width: 60px; height: 60px; }
            .company-name { font-size: 0.95rem; }
            .page-title h2 { font-size: 1.5rem; }
            .action-buttons { width: 100%; }
            .btn-custom { flex: 1; justify-content: center; }
            .table-container { padding: 1rem; }
            .custom-table { font-size: 0.8rem; }
            .custom-table thead th,
            .custom-table tbody td { padding: 0.75rem 0.5rem; }
        }
    </style>
</head>
<body>

<div class="container-fluid p-4">

    <?php if (!empty($_SESSION['error_alat_berat'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($_SESSION['error_alat_berat']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_alat_berat']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success_alat_berat'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($_SESSION['success_alat_berat']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_alat_berat']); ?>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            Data berhasil dihapus dan dipindahkan ke History.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['restored'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            Data berhasil di-restore dari History.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- PAGE HEADER -->
    <div class="page-header animate-fade-in">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="page-title">
                <img src="assets/img/image.png" alt="Logo PT. Sarana Karya Dua Satu" class="company-logo">
                <div class="title-content">
                    <p class="company-name">PT. Sarana Karya Dua Satu</p>
                    <h2>Data Alat Berat</h2>
                    <p class="page-subtitle">Rekap Aset Alat Berat</p>
                </div>
            </div>
            <div class="action-buttons">
                <a href="dashboard.php" class="btn-custom btn-secondary-custom">
                    <i class="bi bi-arrow-left-circle"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
                <a href="database/alat_berat_history.php" class="btn-custom btn-info-custom">
                    <i class="bi bi-clock-history"></i>
                    <span>History</span>
                </a>
                <a href="database/alat_berat_export.php" class="btn-custom btn-primary-custom">
                    <i class="bi bi-file-earmark-excel"></i>
                    <span>Export Excel</span>
                </a>
                <a href="database/alat_berat_tambah.php" class="btn-custom btn-success-custom">
                    <i class="bi bi-plus-circle"></i>
                    <span>Tambah Data</span>
                </a>
            </div>
        </div>
    </div>

    <!-- STATISTICS -->
    <?php
    // Hitung statistik kondisi
    $stat_baik       = 0;
    $stat_perbaikan  = 0;
    $stat_total_jam  = 0;
    $stat_total_perbaikan = 0;

    mysqli_data_seek($result, 0);
    $all_rows = [];
    while ($r = mysqli_fetch_assoc($result)) {
        $all_rows[] = $r;
        $stat_total_jam += (int)($r['jam_operasional'] ?? 0);
        $stat_total_perbaikan += (int)($r['jumlah_perbaikan'] ?? 0);
        $kondisi = strtolower(trim($r['kondisi_terakhir'] ?? ''));
        if ($kondisi === 'baik')            $stat_baik++;
        if (strpos($kondisi, 'perbaikan') !== false || strpos($kondisi, 'rusak') !== false) $stat_perbaikan++;
    }

    // ===== PAGINASI PER SHEET (10 data / sheet) =====
    $total_sheet = max(1, (int) ceil($total_alat / DATA_PER_SHEET));
    $sheet_aktif = isset($_GET['sheet']) ? (int) $_GET['sheet'] : 1;
    if ($sheet_aktif < 1) $sheet_aktif = 1;
    if ($sheet_aktif > $total_sheet) $sheet_aktif = $total_sheet;

    $offset_sheet   = ($sheet_aktif - 1) * DATA_PER_SHEET;
    $rows_sheet_ini = array_slice($all_rows, $offset_sheet, DATA_PER_SHEET);
    ?>

    <div class="stats-container animate-fade-in animate-delay-1">
        <div class="stat-card yellow">
            <div class="stat-card-icon"><i class="bi bi-truck-front-fill"></i></div>
            <h6>Total Alat Berat</h6>
            <h3><?= $total_alat ?></h3>
        </div>
        <div class="stat-card green">
            <div class="stat-card-icon"><i class="bi bi-check-circle-fill"></i></div>
            <h6>Kondisi Baik</h6>
            <h3><?= $stat_baik ?></h3>
        </div>
        <div class="stat-card blue">
            <div class="stat-card-icon"><i class="bi bi-speedometer2"></i></div>
            <h6>Total Jam Operasional</h6>
            <h3><?= number_format($stat_total_jam) ?> <small style="font-size:1rem;font-weight:500;">Jam</small></h3>
        </div>
        <div class="stat-card red">
            <div class="stat-card-icon"><i class="bi bi-tools"></i></div>
            <h6>Total Perbaikan</h6>
            <h3><?= $stat_total_perbaikan ?> <small style="font-size:1rem;font-weight:500;">Kali</small></h3>
        </div>
    </div>

    <!-- TABLE CONTAINER -->
    <div class="table-container animate-fade-in animate-delay-2">
        <div class="table-header">
            <h5>
                <i class="bi bi-table"></i>
                Daftar Alat Berat
            </h5>
            <small class="text-muted fw-bold">
                <?php if ($total_alat > 0): ?>
                    Menampilkan <?= $offset_sheet + 1 ?>–<?= min($offset_sheet + DATA_PER_SHEET, $total_alat) ?>
                    dari <?= $total_alat ?> data (Sheet <?= $sheet_aktif ?>/<?= $total_sheet ?>)
                <?php else: ?>
                    Menampilkan 0 data
                <?php endif; ?>
            </small>
        </div>

        <div class="table-responsive">
            <table class="table custom-table mb-0">
                <thead>
                    <tr>
                        <th class="col-no">NO</th>
                        <th class="col-tanggal">TANGGAL</th>
                        <th class="col-nama-pemilik">NAMA PEMILIK</th>
                        <th class="col-jenis">JENIS<br>KENDARAAN</th>
                        <th class="col-merk">MERK/TYPE</th>
                        <th class="col-fleet">FLEET<br>NUMBER</th>
                        <th class="col-dealer">DEALER</th>
                        <th class="col-model">MODEL<br>MESIN</th>
                        <th class="col-mesin">NO MESIN</th>
                        <th class="col-rangka">NO RANGKA</th>
                        <th class="col-tahun">TAHUN<br>UNIT</th>
                        <th class="col-lokasi">LOKASI</th>
                        <!-- KOLOM BARU -->
                        <th class="col-jam-operasional th-group-new">JAM<br>OPERASIONAL</th>
                        <th class="col-kondisi th-group-new">KONDISI<br>TERAKHIR</th>
                        <th class="col-jumlah-perbaikan th-group-new">JUMLAH<br>PERBAIKAN</th>
                        <th class="col-riwayat th-group-new">HASIL PREDIKSI<br>RISIKO</th>
                        <!-- END KOLOM BARU -->
                        <th class="col-aksi">AKSI</th>
                    </tr>
                </thead>
                <tbody>

                <?php if (count($rows_sheet_ini) === 0): ?>
                    <tr>
                        <td colspan="17" class="p-0">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h5>Belum Ada Data</h5>
                                <p>Data alat berat masih kosong. Silakan tambah data baru.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = $offset_sheet + 1;
                    foreach ($rows_sheet_ini as $row):
                        // Tentukan class kondisi
                        $kondisi_val = strtolower(trim($row['kondisi_terakhir'] ?? ''));
                        if ($kondisi_val === 'baik') {
                            $kondisi_class = 'kondisi-baik';
                            $kondisi_icon  = 'bi-check-circle-fill';
                        } elseif ($kondisi_val === 'sedang') {
                            $kondisi_class = 'kondisi-sedang';
                            $kondisi_icon  = 'bi-exclamation-circle-fill';
                        } elseif (strpos($kondisi_val, 'rusak') !== false) {
                            $kondisi_class = 'kondisi-rusak';
                            $kondisi_icon  = 'bi-x-circle-fill';
                        } elseif (strpos($kondisi_val, 'perbaikan') !== false) {
                            $kondisi_class = 'kondisi-perbaikan';
                            $kondisi_icon  = 'bi-wrench-adjustable-circle-fill';
                        } else {
                            $kondisi_class = 'kondisi-sedang';
                            $kondisi_icon  = 'bi-dash-circle-fill';
                        }

                        // Tentukan class jumlah perbaikan
                        $jml = (int)($row['jumlah_perbaikan'] ?? 0);
                        if ($jml === 0)       $count_class = 'count-0';
                        elseif ($jml <= 3)    $count_class = 'count-low';
                        elseif ($jml <= 7)    $count_class = 'count-mid';
                        else                  $count_class = 'count-high';

                        // Hasil Prediksi Risiko (Algoritma Decision Tree)
                        $risiko = prediksiRisikoAlat($row);
                    ?>
                    <tr>
                        <td class="col-no"><?= $no++ ?></td>
                        <td class="col-tanggal">
                            <?php if (!empty($row['created_at'])): ?>
                                <span class="date-badge"><?= date('d/m/Y', strtotime($row['created_at'])) ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="col-nama-pemilik"><?= htmlspecialchars($row['nama_pemilik']) ?></td>
                        <td class="col-jenis"><?= htmlspecialchars($row['jenis_kendaraan']) ?></td>
                        <td class="col-merk"><strong><?= htmlspecialchars($row['merk_type']) ?></strong></td>
                        <td class="col-fleet">
                            <span class="fleet-number"><?= htmlspecialchars($row['fleet_number']) ?></span>
                        </td>
                        <td class="col-dealer"><?= htmlspecialchars($row['dealer']) ?></td>
                        <td class="col-model"><?= htmlspecialchars($row['model_mesin']) ?></td>
                        <td class="col-mesin">
                            <span class="code-text"><?= htmlspecialchars($row['no_mesin']) ?></span>
                        </td>
                        <td class="col-rangka">
                            <span class="code-text"><?= htmlspecialchars($row['no_rangka']) ?></span>
                        </td>
                        <td class="col-tahun">
                            <span class="badge-year"><?= htmlspecialchars($row['tahun_unit']) ?></span>
                        </td>
                        <td class="col-lokasi">
                            <span class="location-badge">
                                <i class="bi bi-geo-alt-fill"></i>
                                <?= htmlspecialchars($row['lokasi']) ?>
                            </span>
                        </td>

                        <!-- ===== KOLOM BARU ===== -->

                        <!-- Jam Operasional -->
                        <td class="col-jam-operasional">
                            <?php if (!empty($row['jam_operasional'])): ?>
                                <span class="jam-badge">
                                    <i class="bi bi-speedometer2"></i>
                                    <?= number_format((int)$row['jam_operasional']) ?> Jam
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>

                        <!-- Kondisi Terakhir -->
                        <td class="col-kondisi">
                            <?php if (!empty($row['kondisi_terakhir'])): ?>
                                <span class="kondisi-badge <?= $kondisi_class ?>">
                                    <i class="bi <?= $kondisi_icon ?>"></i>
                                    <?= htmlspecialchars($row['kondisi_terakhir']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>

                        <!-- Jumlah Perbaikan -->
                        <td class="col-jumlah-perbaikan">
                            <span class="perbaikan-count <?= $count_class ?>"><?= $jml ?></span>
                        </td>

                        <!-- Hasil Prediksi Risiko (Decision Tree) -->
                        <td class="col-riwayat text-center">
                            <span class="risiko-badge <?= $risiko['rclass'] ?>">
                                <i class="bi <?= $risiko['icon'] ?>"></i>
                                <span>
                                    <?= $risiko['level'] ?>
                                    <span class="risiko-skor">Skor: <?= $risiko['skor'] ?>/10</span>
                                </span>
                            </span>
                        </td>

                        <!-- ===== END KOLOM BARU ===== -->

                        <td class="col-aksi">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="database/alat_berat_edit.php?id=<?= $row['id'] ?>"
                                   class="btn btn-action btn-edit"
                                   title="Edit Data">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="database/alat_berat_hapus.php?id=<?= $row['id'] ?>"
                                   class="btn btn-action btn-delete"
                                   onclick="return confirm('⚠️ Yakin ingin menghapus data alat berat ini?\n\nData: <?= htmlspecialchars($row['jenis_kendaraan']) ?> - <?= htmlspecialchars($row['fleet_number']) ?>')"
                                   title="Hapus Data">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                </tbody>
            </table>
        </div>

        <?php if ($total_sheet > 1): ?>
        <div class="sheet-tabs">
            <?php if ($sheet_aktif > 1): ?>
                <a href="?sheet=<?= $sheet_aktif - 1 ?>" class="sheet-tab sheet-nav" aria-label="Sebelumnya">&lt;</a>
            <?php else: ?>
                <span class="sheet-tab sheet-nav disabled" aria-hidden="true">&lt;</span>
            <?php endif; ?>

            <?php for ($s = 1; $s <= $total_sheet; $s++): ?>
                <a href="?sheet=<?= $s ?>" class="sheet-tab <?= $s === $sheet_aktif ? 'active' : '' ?>">
                    <?= $s ?>
                </a>
            <?php endfor; ?>

            <?php if ($sheet_aktif < $total_sheet): ?>
                <a href="?sheet=<?= $sheet_aktif + 1 ?>" class="sheet-tab sheet-nav" aria-label="Berikutnya">&gt;</a>
            <?php else: ?>
                <span class="sheet-tab sheet-nav disabled" aria-hidden="true">&gt;</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- BACK BUTTON -->
    <div class="back-section animate-fade-in animate-delay-3">
        <button type="button" class="btn-back" id="btnKembali">
            <i class="bi bi-arrow-left-circle"></i>
            <span>Kembali ke Dashboard</span>
        </button>
    </div>

</div>

<!-- SQL REFERENCE (hapus komentar ini di production) -->
<!--
ALTER TABLE alat_berat
    ADD COLUMN jam_operasional    INT          DEFAULT 0       COMMENT 'Total jam operasional mesin' AFTER lokasi,
    ADD COLUMN kondisi_terakhir   VARCHAR(50)  DEFAULT NULL    COMMENT 'Kondisi: Baik / Sedang / Rusak / Dalam Perbaikan' AFTER jam_operasional,
    ADD COLUMN jumlah_perbaikan   INT          DEFAULT 0       COMMENT 'Jumlah total kejadian perbaikan' AFTER kondisi_terakhir,
    ADD COLUMN riwayat_perbaikan  TEXT         DEFAULT NULL    COMMENT 'Catatan riwayat perbaikan alat' AFTER jumlah_perbaikan;
-->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Tombol Kembali ke Dashboard
    document.getElementById('btnKembali').addEventListener('click', function() {
        window.location.href = 'dashboard.php';
    });

    // Confirmation before delete
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('⚠️ Yakin ingin menghapus data alat berat ini?\n\nData akan dihapus secara permanen!')) {
                e.preventDefault();
            }
        });
    });
</script>

</body>
</html>