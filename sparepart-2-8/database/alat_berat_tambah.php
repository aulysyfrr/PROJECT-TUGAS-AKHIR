<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . "/../config/koneksi.php";

// Matikan mode "throw exception" bawaan mysqli (PHP 8.1+),
// supaya error query tidak bikin halaman putih / fatal error.
mysqli_report(MYSQLI_REPORT_OFF);

// Nilai enum kondisi_terakhir yang valid di tabel alat_berat
$kondisi_valid = ['Baik', 'Rusak', 'Dalam Perbaikan'];

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pemilik     = trim($_POST['nama_pemilik'] ?? '');
    $jenis_kendaraan  = trim($_POST['jenis_kendaraan'] ?? '');
    $merk_type        = trim($_POST['merk_type'] ?? '');
    $fleet_number     = trim($_POST['fleet_number'] ?? '');
    $dealer           = trim($_POST['dealer'] ?? '');
    $model_mesin      = trim($_POST['model_mesin'] ?? '');
    $no_mesin         = trim($_POST['no_mesin'] ?? '');
    $no_rangka        = trim($_POST['no_rangka'] ?? '');

    // nama_alat = gabungan jenis + merk + fleet number
    $nama_alat        = trim($jenis_kendaraan . ' - ' . $merk_type . ' (' . $fleet_number . ')');
    $merek            = trim($merk_type);
    $tahun_unit       = $_POST['tahun_unit'] !== '' ? (int)$_POST['tahun_unit'] : NULL;
    if ($tahun_unit !== NULL && $tahun_unit < 2000) {
        $tahun_unit = 2000; // batas terlama Tahun Unit
    }
    // Tahun Pembelian & Tahun Terjual sudah dihapus dari skema database
    $lokasi           = trim($_POST['lokasi'] ?? '');
    $lokasi_operasi   = $lokasi;
    $jam_operasional  = (int)($_POST['jam_operasional'] ?? 0);
    $jam_operasional  = max(0, min(8000, $jam_operasional)); // batas maksimum 8.000 jam
    $kondisi_terakhir = trim($_POST['kondisi_terakhir'] ?? '') ?: 'Baik';
    $jumlah_perbaikan = (int)($_POST['jumlah_perbaikan'] ?? 0);
    $jumlah_perbaikan = max(0, min(10, $jumlah_perbaikan)); // batas maksimum 10 kali
    $riwayat_perbaikan = trim($_POST['riwayat_perbaikan'] ?? '');

    // Validasi nilai enum supaya tidak ditolak MySQL / jadi fatal error
    if (!in_array($kondisi_terakhir, $kondisi_valid, true)) {
        $error = "Nilai Kondisi Terakhir tidak valid: '{$kondisi_terakhir}'. Pilih salah satu dari: " . implode(', ', $kondisi_valid);
    }

    if (!$error) {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO alat_berat (
                nama_pemilik, nama_alat, merek,
                jenis_kendaraan, merk_type, fleet_number, dealer,
                model_mesin, no_mesin, no_rangka,
                tahun_unit,
                lokasi, lokasi_operasi,
                jam_operasional, kondisi_terakhir,
                jumlah_perbaikan, riwayat_perbaikan
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            $error = "Gagal menyiapkan query: " . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param(
                $stmt,
                "sssssssssisssisis",
                $nama_pemilik, $nama_alat, $merek,
                $jenis_kendaraan, $merk_type, $fleet_number, $dealer,
                $model_mesin, $no_mesin, $no_rangka,
                $tahun_unit,
                $lokasi, $lokasi_operasi,
                $jam_operasional, $kondisi_terakhir,
                $jumlah_perbaikan, $riwayat_perbaikan
            );

            $ok = mysqli_stmt_execute($stmt);

            if (!$ok) {
                $error = "Gagal menyimpan data: " . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);
        }
    }

    if (!$error) {
        header("Location: ../alat_berat.php?success=1");
        exit;
    }
    // Kalau ada $error, lanjut render form lagi di bawah dengan pesan error
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Alat Berat | PT. Sarana Karya Dua Satu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --green:      #2563eb;
            --dark-blue: #1e40af;
            --grad:       linear-gradient(135deg,#1e40af 0%,#2563eb 60%,#3b82f6 100%);
            --shadow-md:  0 4px 16px rgba(0,0,0,.12);
            --shadow-lg:  0 8px 32px rgba(0,0,0,.16);
        }
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            background: var(--grad);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }
        body::before {
            content:''; position:fixed; top:-50%; right:-50%;
            width:100%; height:100%;
            background:radial-gradient(circle,rgba(255,255,255,.08) 0%,transparent 70%);
            animation:pulse 15s ease-in-out infinite; z-index:0;
        }
        @keyframes pulse { 0%,100%{transform:scale(1);opacity:.5} 50%{transform:scale(1.1);opacity:.8} }
        .container-fluid { position:relative; z-index:1; }

        /* HEADER */
        .page-header {
            background:rgba(255,255,255,.98); border-radius:20px;
            padding:2rem; margin-bottom:2rem;
            box-shadow:var(--shadow-lg); position:relative; overflow:hidden;
        }
        .page-header::before {
            content:''; position:absolute; top:0; left:0; right:0;
            height:6px; background:var(--grad);
        }
        .page-title-icon {
            width:60px; height:60px; background:var(--grad);
            border-radius:16px; display:flex; align-items:center;
            justify-content:center; box-shadow:0 4px 12px rgba(37,99,235,.3);
        }
        .page-title-icon i { font-size:1.8rem; color:white; }
        .page-title h2 { color:var(--dark-blue); font-weight:800; font-size:1.8rem; margin:0; }
        .page-subtitle { color:#6c757d; font-size:.9rem; margin:0; }

        /* FORM CONTAINER */
        .form-container {
            background:rgba(255,255,255,.98); border-radius:20px;
            padding:2.5rem; box-shadow:var(--shadow-lg); margin-bottom:2rem;
        }

        /* SECTION TITLE */
        .section-title {
            color:var(--dark-blue); font-weight:700; font-size:1rem;
            margin-bottom:1.25rem; padding-bottom:.6rem;
            border-bottom:3px solid var(--green);
            display:flex; align-items:center; gap:.5rem;
        }
        .section-title-new {
            color:var(--dark-blue); font-weight:700; font-size:1rem;
            margin-bottom:1.25rem; padding:.6rem .9rem;
            border-bottom:3px solid var(--green);
            background:#f0fff4; border-radius:10px 10px 0 0;
            display:flex; align-items:center; gap:.5rem;
        }
        .section-new { background:#f0fff4; border:1px solid #bfdbfe; border-radius:0 0 12px 12px; padding:1.5rem; margin-bottom:1.5rem; }

        /* LABELS & INPUTS */
        .form-label { font-weight:600; color:#2c3e50; font-size:.9rem; display:flex; align-items:center; gap:.3rem; margin-bottom:.5rem; }
        .form-label i { color:var(--green); }
        .required-mark { color:#dc3545; font-weight:700; }
        .form-control, .form-select {
            border:2px solid #e9ecef; border-radius:10px;
            padding:.7rem 1rem; font-size:.92rem;
            transition:all .3s; background:#f8f9fa;
        }
        .form-control:focus, .form-select:focus {
            border-color:var(--green);
            box-shadow:0 0 0 .2rem rgba(37,99,235,.15);
            background:white;
        }
        .form-control::placeholder { color:#adb5bd; font-style:italic; }
        .form-text { font-size:.75rem; color:#6c757d; margin-top:.3rem; }

        /* INPUT GROUP */
        .input-group-text {
            background:var(--grad); border:none; color:white;
            border-radius:10px 0 0 10px; font-weight:600;
        }
        .input-group .form-control { border-radius:0 10px 10px 0; }

        /* INFO BOX */
        .info-box {
            background:linear-gradient(135deg,#e3f2fd,#bbdefb);
            border-left:4px solid #2196f3; padding:1rem 1.25rem;
            border-radius:10px; margin-bottom:2rem;
            display:flex; align-items:start; gap:.75rem;
        }
        .info-box i { color:#1976d2; font-size:1.3rem; margin-top:.1rem; }
        .info-box h6 { color:#1565c0; font-weight:700; margin-bottom:.3rem; font-size:.92rem; }
        .info-box p  { color:#1976d2; margin:0; font-size:.82rem; }

        /* KONDISI PREVIEW */
        .kondisi-preview { padding:.32rem .8rem; border-radius:20px; font-weight:700; font-size:.82rem; display:inline-block; margin-top:.4rem; }

        /* UMUR BOX */
        .umur-box { background:#f8fffe; border:1px solid #bfdbfe; border-radius:10px; padding:1rem; }
        .umur-value { font-size:2rem; font-weight:800; line-height:1; }
        .progress { height:10px; border-radius:10px; }

        /* RISIKO PREVIEW */
        .risiko-preview-box { background:#f8fffe; border:1px solid #bfdbfe; border-radius:12px; padding:1.25rem; margin-top:1rem; }
        .risiko-badge { padding:.4rem 1rem; border-radius:20px; font-weight:800; font-size:.88rem; display:inline-flex; align-items:center; gap:.4rem; }
        .r-rendah { background:#dbeafe; color:#1e3a8a; }
        .r-sedang  { background:#eff6ff; color:#856404; }
        .r-tinggi  { background:#f8d7da; color:#721c24; animation:pulse-r 2s infinite; }
        @keyframes pulse-r { 0%,100%{box-shadow:0 2px 8px rgba(220,53,69,.2)} 50%{box-shadow:0 4px 16px rgba(220,53,69,.5)} }
        .skor-bar  { height:8px; border-radius:8px; background:#e9ecef; overflow:hidden; margin-top:.5rem; }
        .skor-fill { height:100%; border-radius:8px; transition:.5s; }

        /* BUTTONS */
        .btn-custom {
            padding:.85rem 2rem; border-radius:12px; font-weight:600;
            border:none; transition:all .3s; display:inline-flex;
            align-items:center; gap:.5rem; text-decoration:none; font-size:1rem;
        }
        .btn-custom:hover { transform:translateY(-2px); }
        .btn-save   { background:var(--grad); color:white; box-shadow:0 4px 12px rgba(37,99,235,.3); }
        .btn-save:hover   { box-shadow:0 6px 16px rgba(37,99,235,.5); color:white; }
        .btn-cancel { background:transparent; border:2px solid #6c757d; color:#6c757d; }
        .btn-cancel:hover { background:#6c757d; color:white; }

        .form-actions { display:flex; gap:1rem; justify-content:center; margin-top:2.5rem; padding-top:2rem; border-top:2px solid #f0f0f0; }

        @keyframes fadeInUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        .anim   { animation:fadeInUp .5s ease-out forwards; }
        .d1 { animation-delay:.1s; opacity:0; }
        .d2 { animation-delay:.2s; opacity:0; }

        @media(max-width:768px) {
            .page-header   { padding:1.5rem; }
            .form-container{ padding:1.5rem; }
            .form-actions  { flex-direction:column; }
            .btn-custom    { width:100%; justify-content:center; }
        }
    </style>
</head>
<body>
<div class="container-fluid p-4">

    <!-- HEADER -->
    <div class="page-header anim">
        <div class="d-flex align-items-center gap-3">
            <div class="page-title-icon"><i class="bi bi-plus-circle-fill"></i></div>
            <div>
                <h2 class="page-title">Tambah Data Alat Berat</h2>
                <p class="page-subtitle">PT. Sarana Karya Dua Satu — Input Data Aset Baru</p>
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger anim">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- INFO BOX -->
    <div class="info-box anim d1">
        <i class="bi bi-info-circle-fill"></i>
        <div>
            <h6>Petunjuk Pengisian</h6>
            <p>Lengkapi semua data bertanda <strong class="required-mark">*</strong> (wajib). Bagian <strong>Operasional & Perbaikan</strong> digunakan untuk analisis prediksi risiko ML.</p>
        </div>
    </div>

    <!-- FORM -->
    <div class="form-container anim d2">
    <form method="POST" id="formAlatBerat">

        <!-- ===== DATA UMUM ===== -->
        <h5 class="section-title"><i class="bi bi-info-circle-fill"></i> Data Umum</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label"><i class="bi bi-building"></i> Nama Pemilik <span class="required-mark">*</span></label>
                <input type="text" name="nama_pemilik" class="form-control" required placeholder="Masukkan nama pemilik"
                       value="<?= htmlspecialchars($_POST['nama_pemilik'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label"><i class="bi bi-truck"></i> Jenis Kendaraan <span class="required-mark">*</span></label>
                <select name="jenis_kendaraan" id="selJenis" class="form-select" required>
                    <option value="">-- Pilih Jenis --</option>
                    <?php $jk = $_POST['jenis_kendaraan'] ?? ''; ?>
                    <optgroup label="Alat Gali">
                        <option value="Excavator" <?= $jk==='Excavator'?'selected':'' ?>>Excavator</option>
                        <option value="Backhoe Loader" <?= $jk==='Backhoe Loader'?'selected':'' ?>>Backhoe Loader</option>
                    </optgroup>
                    <optgroup label="Alat Dorong & Ratakan">
                        <option value="Bulldozer" <?= $jk==='Bulldozer'?'selected':'' ?>>Bulldozer</option>
                        <option value="Motor Grader" <?= $jk==='Motor Grader'?'selected':'' ?>>Motor Grader</option>
                        <option value="Compactor" <?= $jk==='Compactor'?'selected':'' ?>>Compactor</option>
                    </optgroup>
                    <optgroup label="Alat Angkat">
                        <option value="Crane" <?= $jk==='Crane'?'selected':'' ?>>Crane</option>
                        <option value="Forklift" <?= $jk==='Forklift'?'selected':'' ?>>Forklift</option>
                    </optgroup>
                    <optgroup label="Alat Angkut">
                        <option value="Wheel Loader" <?= $jk==='Wheel Loader'?'selected':'' ?>>Wheel Loader</option>
                        <option value="Dump Truck" <?= $jk==='Dump Truck'?'selected':'' ?>>Dump Truck</option>
                    </optgroup>
                    <option value="Lainnya" <?= $jk==='Lainnya'?'selected':'' ?>>Lainnya</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label"><i class="bi bi-tag"></i> Merk / Type <span class="required-mark">*</span></label>
                <input type="text" name="merk_type" class="form-control" required placeholder="Contoh: Komatsu PC200"
                       value="<?= htmlspecialchars($_POST['merk_type'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label"><i class="bi bi-hash"></i> Fleet Number <span class="required-mark">*</span></label>
                <input type="text" name="fleet_number" class="form-control" required placeholder="Contoh: SKDS-EX-13-15"
                       value="<?= htmlspecialchars($_POST['fleet_number'] ?? '') ?>">
            </div>
            <div class="col-md-12">
                <label class="form-label"><i class="bi bi-shop"></i> Dealer</label>
                <input type="text" name="dealer" class="form-control" placeholder="Nama dealer (opsional)"
                       value="<?= htmlspecialchars($_POST['dealer'] ?? '') ?>">
            </div>
        </div>

        <!-- ===== SPESIFIKASI MESIN ===== -->
        <h5 class="section-title"><i class="bi bi-gear-fill"></i> Spesifikasi Mesin</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label"><i class="bi bi-cpu"></i> Model Mesin</label>
                <input type="text" name="model_mesin" class="form-control" placeholder="Contoh: 6D107E"
                       value="<?= htmlspecialchars($_POST['model_mesin'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label"><i class="bi bi-123"></i> No Mesin</label>
                <input type="text" name="no_mesin" class="form-control" placeholder="Nomor seri mesin"
                       value="<?= htmlspecialchars($_POST['no_mesin'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label"><i class="bi bi-upc"></i> No Rangka</label>
                <input type="text" name="no_rangka" class="form-control" placeholder="Nomor rangka kendaraan"
                       value="<?= htmlspecialchars($_POST['no_rangka'] ?? '') ?>">
            </div>
        </div>

        <!-- ===== TAHUN & LOKASI ===== -->
        <h5 class="section-title"><i class="bi bi-calendar-event-fill"></i> Tahun & Lokasi</h5>
        <div class="row g-3 mb-4">

            <!-- Tahun Unit -->
            <div class="col-md-4">
                <label class="form-label"><i class="bi bi-calendar3"></i> Tahun Unit</label>
                <select name="tahun_unit" id="selTahunUnit" class="form-select" onchange="updateUmur()">
                    <option value="">-- Pilih --</option>
                    <?php
                    $ty = date('Y');
                    $tu = $_POST['tahun_unit'] ?? '';
                    $groups = [
                        '🆕 Baru (0–7 thn)'   => [$ty,    $ty-7],
                        '🟡 Normal (8–12 thn)' => [$ty-8,  $ty-12],
                        '🔴 Tua (>12 thn)'     => [$ty-13, 2000],
                    ];
                    foreach ($groups as $glabel => [$dari, $sampai]):
                    ?>
                    <optgroup label="<?= $glabel ?>">
                    <?php for ($t = $dari; $t >= $sampai; $t--): ?>
                        <option value="<?= $t ?>" <?= $tu==$t?'selected':'' ?>><?= $t ?></option>
                    <?php endfor; ?>
                    </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Umur Otomatis -->
            <div class="col-md-4">
                <label class="form-label"><i class="bi bi-hourglass-split"></i> Umur Alat <small class="text-muted">(otomatis)</small></label>
                <div class="umur-box" id="umurBox">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="umur-value" id="umurVal" style="color:#adb5bd">–</span>
                            <span class="text-muted fw-semibold"> Thn</span>
                        </div>
                        <span id="umurLabel" class="badge px-2 py-1 fs-6"></span>
                    </div>
                    <div class="progress mt-2">
                        <div class="progress-bar" id="umurBar" style="width:0%;background:#2563eb"></div>
                    </div>
                    <div class="form-text mt-1" id="umurDesc">Pilih tahun unit</div>
                </div>
            </div>

            <!-- Lokasi -->
            <div class="col-md-4">
                <label class="form-label"><i class="bi bi-pin-map-fill"></i> Lokasi Operasi</label>
                <select name="lokasi" id="selLokasi" class="form-select">
                    <option value="">-- Pilih Lokasi --</option>
                    <?php $lok = $_POST['lokasi'] ?? ''; ?>
                    <option value="Risiko Rendah" <?= $lok==='Risiko Rendah'?'selected':'' ?>>🟢 Risiko Rendah – Gudang / Workshop / Kota</option>
                    <option value="Risiko Sedang" <?= $lok==='Risiko Sedang'?'selected':'' ?>>🟡 Risiko Sedang – Perkebunan / Jalan Tanah / Perbukitan</option>
                    <option value="Risiko Tinggi" <?= $lok==='Risiko Tinggi'?'selected':'' ?>>🔴 Risiko Tinggi – Tambang / Rawa / Tambang Aktif / Suhu &gt;45°C</option>
                </select>
            </div>
        </div>

        <!-- ===== OPERASIONAL & PERBAIKAN ===== -->
        <div class="section-title-new">
            <i class="bi bi-cpu-fill"></i> Operasional & Perbaikan
        </div>
        <div class="section-new">
            <div class="row g-3">

                <!-- Jam Operasional -->
                <div class="col-md-4">
                    <label class="form-label"><i class="bi bi-speedometer2"></i> Jam Operasional</label>
                    <select id="selRangeJam" class="form-select mb-2" onchange="setJamRangeOnly(this.value)">
                        <option value="">-- Pilih Range Jam --</option>
                        <optgroup label="🟢 Normal (0–3.000 jam)">
                            <option value="250">0 – 250 jam</option>
                            <option value="500">251 – 500 jam</option>
                            <option value="1000">501 – 1.000 jam</option>
                            <option value="2000">1.001 – 2.000 jam</option>
                            <option value="3000">2.001 – 3.000 jam</option>
                        </optgroup>
                        <optgroup label="🟡 Waspada (3.001–5.000 jam)">
                            <option value="3500">3.001 – 3.500 jam</option>
                            <option value="4000">3.501 – 4.000 jam</option>
                            <option value="4500">4.001 – 4.500 jam</option>
                            <option value="5000">4.501 – 5.000 jam</option>
                        </optgroup>
                        <optgroup label="🔴 Kritis (5.001–8.000 jam)">
                            <option value="5500">5.001 – 6.000 jam</option>
                            <option value="6500">6.001 – 7.000 jam</option>
                            <option value="8000">7.001 – 8.000 jam (maksimum)</option>
                        </optgroup>
                    </select>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-speedometer2"></i></span>
                        <input type="number" name="jam_operasional" id="inputJam" class="form-control"
                               value="<?= htmlspecialchars($_POST['jam_operasional'] ?? 0) ?>" min="0" max="8000" placeholder="Maks. 8.000 jam" oninput="clampJam(this)">
                        <span class="input-group-text" style="background:var(--grad);color:white;font-weight:700;border-radius:0 10px 10px 0">Jam</span>
                    </div>
                    <div id="jamStatus" class="mt-1"></div>
                    <div class="form-text">< 3.000 = Normal &nbsp;|&nbsp; 3.000–5.000 = Waspada &nbsp;|&nbsp; > 5.000 = Kritis. Maksimum input 8.000 jam (ambang umum sebelum overhaul besar / peremajaan alat).</div>
                </div>

                <!-- Kondisi Terakhir -->
                <div class="col-md-4">
                    <label class="form-label"><i class="bi bi-activity"></i> Kondisi Terakhir</label>
                    <select name="kondisi_terakhir" id="selKondisi" class="form-select" onchange="updateKondisiPreview(this)">
                        <option value="">-- Pilih Kondisi --</option>
                        <?php
                        $kt = $_POST['kondisi_terakhir'] ?? '';
                        $icons = ['Baik'=>'✅','Rusak'=>'❌','Dalam Perbaikan'=>'🔧'];
                        foreach ($kondisi_valid as $kv):
                        ?>
                        <option value="<?= htmlspecialchars($kv) ?>" <?= $kt===$kv?'selected':'' ?>>
                            <?= $icons[$kv] . ' ' . htmlspecialchars($kv) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="kondisiPreview" class="mt-2"></div>
                </div>

                <!-- Jumlah Perbaikan -->
                <div class="col-md-4">
                    <label class="form-label"><i class="bi bi-wrench-adjustable"></i> Jumlah Perbaikan</label>
                    <div class="input-group">
                        <input type="number" name="jumlah_perbaikan" id="inputPrb" class="form-control"
                               value="<?= htmlspecialchars($_POST['jumlah_perbaikan'] ?? 0) ?>" min="0" max="10" placeholder="0" oninput="clampPrb(this)">
                        <span class="input-group-text" style="background:var(--grad);color:white;font-weight:700;border-radius:0 10px 10px 0">Kali</span>
                    </div>
                    <div id="prbStatus" class="mt-1"></div>
                    <div class="form-text">0–2 = Baik &nbsp;|&nbsp; 3–6 = Sedang &nbsp;|&nbsp; 7–10 = Danger. Maksimum input 10 kali perbaikan.</div>
                </div>

                <!-- Riwayat Perbaikan -->
                <div class="col-12">
                    <label class="form-label"><i class="bi bi-journal-text"></i> Riwayat Perbaikan</label>
                    <textarea name="riwayat_perbaikan" class="form-control" rows="4"
                        placeholder="Contoh:&#10;12/01/2024 - Ganti oli mesin&#10;05/03/2024 - Perbaikan sistem hidrolik&#10;20/06/2024 - Servis rutin 500 jam"><?= htmlspecialchars($_POST['riwayat_perbaikan'] ?? '') ?></textarea>
                    <div class="form-text">Catat riwayat perbaikan secara kronologis. Tekan Enter untuk baris baru.</div>
                    <div class="form-text"><i class="bi bi-info-circle"></i> Catatan: kolom ini saat ini belum tersimpan ke tabel alat_berat (kolomnya belum ada). Hubungi admin bila perlu diaktifkan.</div>
                </div>
            </div>

            <!-- PREDIKSI RISIKO ML (ON-DEMAND) -->
            <div class="risiko-preview-box">
                <div style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6c757d;margin-bottom:.6rem">
                    <i class="bi bi-cpu me-1"></i> Prediksi Risiko ML
                </div>
                <button type="button" class="btn-custom" style="background:var(--grad);color:#fff;border:none;margin-bottom:.9rem" onclick="predictRisiko()">
                    <i class="bi bi-magic"></i> Prediksikan Risiko
                </button>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="risiko-badge r-rendah" id="risikoResult">
                        <i class="bi bi-shield-check" id="risikoIcon"></i>
                        <span id="risikoText">Klik "Prediksikan Risiko" untuk melihat hasil</span>
                    </span>
                    <small class="text-muted">Skor: <strong id="risikoSkor">0</strong>/<span id="risikoMaks">10</span></small>
                </div>
                <div class="skor-bar">
                    <div class="skor-fill" id="risikoBar" style="width:0%;background:#2563eb"></div>
                </div>
                <div class="mt-2" style="font-size:.75rem;color:#6c757d">
                    🟢 Rendah &nbsp;|&nbsp; 🟡 Sedang &nbsp;|&nbsp; 🔴 Tinggi — otomatis Tinggi jika Tahun Unit "Tua" atau Jam Operasional "Kritis"
                </div>
            </div>
        </div>

        <!-- FORM ACTIONS -->
        <div class="form-actions">
            <button type="submit" class="btn-custom btn-save">
                <i class="bi bi-check-circle-fill"></i> Simpan Data
            </button>
            <a href="../alat_berat.php" class="btn-custom btn-cancel">
                <i class="bi bi-x-circle"></i> Batal
            </a>
        </div>

    </form>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ===== KONDISI PREVIEW =====
const kondisiColors = {
    'Baik':             {bg:'#dbeafe',color:'#1e3a8a',label:'✅ Baik'},
    'Rusak':            {bg:'#f8d7da',color:'#721c24',label:'❌ Rusak'},
    'Dalam Perbaikan':  {bg:'#d1ecf1',color:'#0c5460',label:'🔧 Dalam Perbaikan'},
};
function updateKondisiPreview(sel) {
    const el = document.getElementById('kondisiPreview');
    const c  = kondisiColors[sel.value];
    el.innerHTML = c ? `<span style="background:${c.bg};color:${c.color};padding:.3rem .8rem;border-radius:20px;font-weight:700;font-size:.82rem;display:inline-block">${c.label}</span>` : '';
}

// ===== JAM RANGE =====
function setJamRangeOnly(val) {
    if (val) {
        document.getElementById('inputJam').value = val;
        updateJamStatus(parseInt(val) || 0);
    }
}

// ===== BATASI INPUT (JAM MAKS 8.000 / PERBAIKAN MAKS 10) =====
function clampJam(el) {
    let v = parseInt(el.value) || 0;
    if (v > 8000) { v = 8000; el.value = 8000; }
    if (v < 0)    { v = 0;    el.value = 0; }
    updateJamStatus(v);
}
function clampPrb(el) {
    let v = parseInt(el.value) || 0;
    if (v > 10) { v = 10; el.value = 10; }
    if (v < 0)  { v = 0;  el.value = 0; }
    updatePrbStatus(v);
}

// ===== JAM STATUS =====
function updateJamStatus(jam) {
    const el = document.getElementById('jamStatus');
    if (!jam && jam !== 0) { el.innerHTML=''; return; }
    let bg,tx,lbl;
    if      (jam < 3000)  { bg='#e3f2fd'; tx='#1565c0'; lbl='🟢 Normal'; }
    else if (jam <= 5000) { bg='#eff6ff'; tx='#856404'; lbl='🟡 Waspada'; }
    else                  { bg='#f8d7da'; tx='#721c24'; lbl='🔴 Kritis — Overhaul Segera!'; }
    el.innerHTML = `<span style="background:${bg};color:${tx};padding:.25rem .75rem;border-radius:20px;font-weight:700;font-size:.78rem;display:inline-block">${lbl}</span>`;
}

// ===== PERBAIKAN STATUS =====
function updatePrbStatus(p) {
    const el = document.getElementById('prbStatus');
    let bg,tx,lbl;
    if      (p < 2)  { bg='#dbeafe'; tx='#1e3a8a'; lbl='✅ Baik'; }
    else if (p <= 7) { bg='#eff6ff'; tx='#856404'; lbl='⚠️ Sedang'; }
    else             { bg='#f8d7da'; tx='#721c24'; lbl='❌ Danger'; }
    el.innerHTML = `<span style="background:${bg};color:${tx};padding:.25rem .75rem;border-radius:20px;font-weight:700;font-size:.78rem;display:inline-block">${lbl}</span>`;
}

// ===== UMUR OTOMATIS =====
function updateUmur() {
    const tahun = parseInt(document.getElementById('selTahunUnit').value) || 0;
    if (!tahun) return;
    const umur     = new Date().getFullYear() - tahun;
    const umurMaks = new Date().getFullYear() - 2000; // batas terlama Tahun Unit = tahun 2000
    const persen   = Math.min(100, Math.round((umur / umurMaks) * 100));
    let color, label, desc;
    if      (umur <= 7)  { color='#2563eb'; label='🆕 Baru';   desc='Kondisi prima, risiko rendah'; }
    else if (umur <= 12) { color='#3b82f6'; label='🟡 Normal';  desc='Perlu perhatian rutin'; }
    else                  { color='#dc3545'; label='🔴 Tua';    desc='Risiko tinggi — evaluasi kelayakan operasi segera'; }

    document.getElementById('umurVal').textContent  = umur;
    document.getElementById('umurVal').style.color  = color;
    document.getElementById('umurLabel').textContent = label;
    document.getElementById('umurLabel').style.background = color;
    document.getElementById('umurLabel').style.color      = 'white';
    document.getElementById('umurBar').style.width        = persen + '%';
    document.getElementById('umurBar').style.background   = color;
    document.getElementById('umurDesc').textContent = desc + ' · Sisa maks: ' + Math.max(0, umurMaks - umur) + ' thn';
}

// ===== PREDIKSI RISIKO (ON-DEMAND, BUKAN LIVE) =====
// Bobot poin per fitur, diurutkan dari yang PALING berpengaruh ke yang PALING kecil
// (semakin tua/aus & sering rusak sebuah alat, semakin besar risikonya):
// 1) Tahun Unit / Umur Alat  -> bobot maks 6 (paling berpengaruh; kalau kategori TUA, otomatis Risiko Tinggi)
// 2) Jam Operasional         -> bobot maks 5 (jam pakai = beban kerja aktual mesin, maks 8.000 jam)
// 3) Kondisi Terakhir        -> bobot maks 4 (kondisi fisik saat ini)
// 4) Jumlah Perbaikan        -> bobot maks 3 (riwayat kerusakan berulang, maks 10 kali)
// 5) Lokasi Operasi          -> bobot maks 2 (medan kerja mempercepat/memperlambat keausan)
// 6) Jenis Kendaraan         -> bobot maks 1 (kompleksitas alat, paling kecil pengaruhnya)
const SKOR_MAKS = 21; // 6 + 5 + 4 + 3 + 2 + 1

// ===== ALGORITMA DECISION TREE (POHON KEPUTUSAN) UNTUK KLASIFIKASI RISIKO =====
// Struktur pohon: setiap node = { feature, test, yes, no } sampai mencapai leaf.
// Root -> kategoriUmur === 'tua'? -> kategoriJam === 'kritis'? -> skorTampil <= 3? -> skorTampil <= 7?
function bangunPohonKeputusanRisiko() {
    return {
        feature: 'kategoriUmur',
        test: v => v === 'tua',
        yes: { leaf: 'tinggi' },
        no: {
            feature: 'kategoriJam',
            test: v => v === 'kritis',
            yes: { leaf: 'tinggi' },
            no: {
                feature: 'skorTampil',
                test: v => v <= 3,
                yes: { leaf: 'rendah' },
                no: {
                    feature: 'skorTampil',
                    test: v => v <= 7,
                    yes: { leaf: 'sedang' },
                    no: { leaf: 'tinggi' }
                }
            }
        }
    };
}

function evaluasiPohonKeputusanRisiko(kategoriUmur, kategoriJam, skorTampil) {
    const data = { kategoriUmur, kategoriJam, skorTampil };
    let node = bangunPohonKeputusanRisiko();
    while (!('leaf' in node)) {
        node = node.test(data[node.feature]) ? node.yes : node.no;
    }
    return node.leaf;
}

function predictRisiko() {
    const tahun  = parseInt(document.getElementById('selTahunUnit').value) || new Date().getFullYear();
    const jam    = Math.min(8000, parseInt(document.getElementById('inputJam').value) || 0);
    const kondisi= document.getElementById('selKondisi').value.toLowerCase();
    const jenis  = (document.getElementById('selJenis').value || '').toLowerCase();
    const lokasi = (document.getElementById('selLokasi').value || '').toLowerCase();
    const prb    = Math.min(10, parseInt(document.getElementById('inputPrb').value) || 0);
    const umur   = new Date().getFullYear() - tahun;

    let skor = 0;
    let kategoriUmur;
    let kategoriJam;

    // 1) Tahun Unit / Umur Alat (bobot 6) — hanya 3 kategori: Baru, Normal, Tua
    if      (umur <= 7)  { skor += 0; kategoriUmur = 'baru'; }
    else if (umur <= 12) { skor += 3; kategoriUmur = 'normal'; }
    else                  { skor += 6; kategoriUmur = 'tua'; }

    // 2) Jam Operasional (bobot 5), maksimum input 8.000 jam
    if      (jam < 3000)  { skor += 0; kategoriJam = 'normal'; }
    else if (jam <= 5000) { skor += 3; kategoriJam = 'waspada'; }
    else                   { skor += 5; kategoriJam = 'kritis'; }

    // 3) Kondisi Terakhir (bobot 4)
    if      (kondisi === 'baik')            skor += 0;
    else if (kondisi === 'dalam perbaikan') skor += 2;
    else if (kondisi === 'rusak')           skor += 4;

    // 4) Jumlah Perbaikan (bobot 3), maksimum input 10 kali
    if      (prb <= 2) skor += 0;
    else if (prb <= 6) skor += 2;
    else                skor += 3;

    // 5) Lokasi Operasi (bobot 2)
    if      (lokasi === 'risiko rendah') skor += 0;
    else if (lokasi === 'risiko sedang') skor += 1;
    else if (lokasi === 'risiko tinggi') skor += 2;

    // 6) Jenis Kendaraan (bobot 1)
    if (jenis.includes('crane') || jenis.includes('bulldozer')) skor += 1;

    // Skor mentah tetap 0–21 (presisi bobot per fitur tetap terjaga),
    // tapi ditampilkan ke user dalam skala 0–10 supaya lebih mudah dibaca.
    const skorTampil = Math.round((skor / SKOR_MAKS) * 10);

    // ===== KLASIFIKASI RISIKO — ALGORITMA DECISION TREE =====
    // Setiap node menguji SATU fitur lalu bercabang ke node/leaf berikutnya,
    // mengikuti urutan bobot fitur terbesar -> terkecil.
    const leaf = evaluasiPohonKeputusanRisiko(kategoriUmur, kategoriJam, skorTampil);

    // Leaf -> tampilan (lookup table, tanpa if-else)
    const PETA_LABEL_RISIKO = {
        tinggi: { level: '🔴 RISIKO TINGGI', rclass: 'r-tinggi', icon: 'bi-shield-x',           color: '#dc3545' },
        sedang: { level: '🟡 RISIKO SEDANG', rclass: 'r-sedang', icon: 'bi-shield-exclamation',  color: '#3b82f6' },
        rendah: { level: '🟢 RISIKO RENDAH', rclass: 'r-rendah', icon: 'bi-shield-check',        color: '#2563eb' },
    };
    const { level, rclass, icon, color } = PETA_LABEL_RISIKO[leaf];

    const badge = document.getElementById('risikoResult');
    badge.className = 'risiko-badge ' + rclass;
    document.getElementById('risikoIcon').className = 'bi ' + icon;
    document.getElementById('risikoText').textContent = level;
    document.getElementById('risikoSkor').textContent = skorTampil;
    document.getElementById('risikoMaks').textContent = 10;
    document.getElementById('risikoBar').style.width      = (skorTampil * 10) + '%';
    document.getElementById('risikoBar').style.background = color;
}

// Form Validation
document.getElementById('formAlatBerat').addEventListener('submit', function(e) {
    const required = this.querySelectorAll('[required]');
    let valid = true;
    required.forEach(f => {
        if (!f.value.trim()) { valid = false; f.classList.add('is-invalid'); }
        else f.classList.remove('is-invalid');
    });
    if (!valid) { e.preventDefault(); alert('⚠️ Mohon lengkapi semua field wajib!'); }
});
document.querySelectorAll('.form-control, .form-select').forEach(el => {
    el.addEventListener('input', () => el.classList.remove('is-invalid'));
});

// Init (tidak lagi memanggil prediksi ML otomatis, hanya status field individual)
window.addEventListener('load', function () {
    updateUmur();
    updateJamStatus(parseInt(document.getElementById('inputJam').value) || 0);
    updatePrbStatus(parseInt(document.getElementById('inputPrb').value) || 0);
});
</script>
</body>
</html>