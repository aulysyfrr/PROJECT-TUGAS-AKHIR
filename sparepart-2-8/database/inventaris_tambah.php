<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . "/../config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tanggal_masuk     = $_POST['tanggal_masuk'];
    $kode_barang       = trim($_POST['kode_barang']);
    $nama_barang       = trim($_POST['nama_barang']);
    $jenis_inventaris  = trim($_POST['jenis_inventaris']);
    $kategori          = trim($_POST['kategori']);
    $harga_perolehan   = str_replace('.', '', $_POST['harga_perolehan']);
    $pengguna_saat_ini = trim($_POST['pengguna_saat_ini']);
    $lokasi            = trim($_POST['lokasi']);
    $kondisi           = trim($_POST['kondisi']);

    // CEK DUPLIKASI KODE BARANG
    $cek = mysqli_prepare($conn, "SELECT id FROM inventaris WHERE kode_barang = ?");
    mysqli_stmt_bind_param($cek, "s", $kode_barang);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    if (mysqli_stmt_num_rows($cek) > 0) {
        echo "<script>
            alert('❌ Kode barang sudah digunakan!');
            window.history.back();
        </script>";
        exit;
    }

    // INSERT DATA INVENTARIS
    $stmt = mysqli_prepare($conn, "
        INSERT INTO inventaris (
            tanggal_masuk,
            kode_barang,
            nama_barang,
            jenis_inventaris,
            kategori,
            harga_perolehan,
            pengguna_saat_ini,
            lokasi,
            kondisi
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "sssssdsss",
        $tanggal_masuk,
        $kode_barang,
        $nama_barang,
        $jenis_inventaris,
        $kategori,
        $harga_perolehan,
        $pengguna_saat_ini,
        $lokasi,
        $kondisi
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
            alert('✅ Data inventaris berhasil ditambahkan!');
            window.location.href = '../inventaris.php';
        </script>";
    } else {
        echo "<script>
            alert('❌ Gagal menyimpan data!');
            window.history.back();
        </script>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Inventaris | PT. Sarana Karya Dua Satu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #2563eb;
            --dark-blue: #1e40af;
            --gradient-green: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.16);
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            padding: 20px 0;
        }

        .page-header {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            border-top: 6px solid var(--primary-blue);
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-title-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient-green);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(5,150,105,0.3);
        }

        .page-title-icon i {
            font-size: 1.8rem;
            color: white;
        }

        .page-title h2 {
            color: var(--dark-blue);
            font-weight: 700;
            margin: 0;
        }

        .form-container {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
        }

        .form-section-title {
            color: var(--dark-blue);
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid var(--primary-blue);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .required-mark {
            color: #dc3545;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.25rem rgba(5,150,105,0.15);
        }

        .input-group-text {
            background: var(--gradient-green);
            border: none;
            color: white;
            border-radius: 10px 0 0 10px;
            font-weight: 600;
        }

        .btn-custom {
            padding: 0.85rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-save {
            background: var(--gradient-green);
            color: white;
            box-shadow: 0 4px 12px rgba(5,150,105,0.3);
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(5,150,105,0.5);
            color: white;
        }

        .btn-cancel {
            background: transparent;
            border: 2px solid #6c757d;
            color: #6c757d;
        }

        .btn-cancel:hover {
            background: #6c757d;
            color: white;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2.5rem;
            padding-top: 2rem;
            border-top: 2px solid #f0f0f0;
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
            .btn-custom {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 900px;">

    <div class="page-header">
        <div class="page-title">
            <div class="page-title-icon">
                <i class="bi bi-plus-circle-fill"></i>
            </div>
            <div>
                <h2>Tambah Data Inventaris</h2>
                <p class="text-muted mb-0">PT. Sarana Karya Dua Satu</p>
            </div>
        </div>
    </div>

    <div class="form-container">
        <form method="POST">
            
            <div class="mb-4">
                <h5 class="form-section-title">
                    <i class="bi bi-info-circle"></i> Data Umum
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            Tanggal Masuk <span class="required-mark">*</span>
                        </label>
                        <input type="date" name="tanggal_masuk" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Kode Barang <span class="required-mark">*</span>
                        </label>
                        <input type="text" name="kode_barang" class="form-control" required placeholder="INV-2026-001">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">
                            Nama Barang <span class="required-mark">*</span>
                        </label>
                        <input type="text" name="nama_barang" class="form-control" required placeholder="Contoh: Laptop Dell Latitude 5420">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Jenis Inventaris <span class="required-mark">*</span>
                        </label>
                        <select name="jenis_inventaris" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <option>Elektronik</option>
                            <option>Furniture</option>
                            <option>Kendaraan</option>
                            <option>Peralatan Kantor</option>
                            <option>Lainnya</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Kategori <span class="required-mark">*</span>
                        </label>
                        <select name="kategori" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <option>Aset Tetap</option>
                            <option>Aset Lancar</option>
                            <option>Inventaris Kantor</option>
                            <option>Inventaris Operasional</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h5 class="form-section-title">
                    <i class="bi bi-cash-stack"></i> Harga & Pengguna
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            Harga Perolehan <span class="required-mark">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="harga_perolehan" class="form-control" required placeholder="0" id="hargaPerolehan">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Pengguna Saat Ini</label>
                        <input type="text" name="pengguna_saat_ini" class="form-control" placeholder="Nama pengguna">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h5 class="form-section-title">
                    <i class="bi bi-geo-alt-fill"></i> Lokasi & Kondisi
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Lokasi</label>
                        <input type="text" name="lokasi" class="form-control" placeholder="Kantor Pusat - Lantai 2">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Kondisi <span class="required-mark">*</span>
                        </label>
                        <select name="kondisi" class="form-select" required>
                            <option value="Baik">Baik</option>
                            <option value="Rusak">Rusak</option>
                            <option value="Perbaikan">Perbaikan</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-custom btn-save">
                    <i class="bi bi-check-circle"></i> Simpan Data
                </button>
                <a href="../inventaris.php" class="btn-custom btn-cancel">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>

        </form>
    </div>

</div>

<script>
    // Format Harga dengan pemisah ribuan
    const hargaInput = document.getElementById('hargaPerolehan');
    
    hargaInput.addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('id-ID');
        }
        this.value = value;
    });
</script>

</body>
</html>