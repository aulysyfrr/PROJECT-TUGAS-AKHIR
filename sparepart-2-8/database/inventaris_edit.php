<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";

// Matikan mode "throw exception" bawaan mysqli (PHP 8.1+),
// supaya error query tidak bikin halaman putih / fatal error.
mysqli_report(MYSQLI_REPORT_OFF);

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    header("Location: ../alat_berat.php"); exit;
}
$id = (int)$_GET['id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM alat_berat WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data   = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) { header("Location: ../alat_berat.php"); exit; }

// Nilai enum kondisi_terakhir yang valid di tabel alat_berat
$kondisi_valid = ['Baik', 'Rusak', 'Dalam Perbaikan'];

if (isset($_POST['simpan'])) {
  try {

    $tanggal           = trim($_POST['tanggal'] ?? '');
    $nama_pemilik      = trim($_POST['nama_pemilik'] ?? '');
    $jenis_kendaraan   = trim($_POST['jenis_kendaraan'] ?? '');
    $merk_type         = trim($_POST['merk_type'] ?? '');
    $fleet_number      = trim($_POST['fleet_number'] ?? '');
    $dealer            = trim($_POST['dealer'] ?? '');
    $model_mesin       = trim($_POST['model_mesin'] ?? '');
    $no_mesin          = trim($_POST['no_mesin'] ?? '');
    $no_rangka         = trim($_POST['no_rangka'] ?? '');
    $tahun_unit        = (int)($_POST['tahun_unit'] ?? 0);
    if ($tahun_unit !== 0 && $tahun_unit < 2000) {
        $tahun_unit = 2000; // batas terlama Tahun Unit
    }
    // tahun_pembelian & tahun_terjual sudah dihapus dari skema database
    $lokasi            = trim($_POST['lokasi'] ?? '');
    $jam_operasional   = (int)($_POST['jam_operasional'] ?? 0);
    $jam_operasional   = max(0, min(8000, $jam_operasional)); // batas maksimum 8.000 jam
    $kondisi_terakhir  = trim($_POST['kondisi_terakhir'] ?? '');
    $jumlah_perbaikan  = (int)($_POST['jumlah_perbaikan'] ?? 0);
    $jumlah_perbaikan  = max(0, min(10, $jumlah_perbaikan)); // batas maksimum 10 kali
    $riwayat_perbaikan = trim($_POST['riwayat_perbaikan'] ?? '');

    // nama_alat dipakai sebagai field utama (mengikuti tabel asli);
    // kalau form Anda tidak punya field ini, pakai jenis_kendaraan sebagai fallback
    $nama_alat = trim($_POST['nama_alat'] ?? $jenis_kendaraan);

    if ($nama_alat === '' || $nama_pemilik === '' || $jenis_kendaraan === '' ||
        $merk_type === '' || $fleet_number === '') {
        $_SESSION['error_alat_berat'] = "Mohon lengkapi semua field wajib.";
        header("Location: alat_berat_edit.php?id=" . $id);
        exit;
    }

    if ($kondisi_terakhir !== '' && !in_array($kondisi_terakhir, $kondisi_valid, true)) {
        $_SESSION['error_alat_berat'] = "Nilai Kondisi Terakhir tidak valid.";
        header("Location: alat_berat_edit.php?id=" . $id);
        exit;
    }

    $sql = "UPDATE alat_berat SET
                tanggal=?, nama_alat=?, nama_pemilik=?,
                jenis_kendaraan=?, merek=?, merk_type=?,
                fleet_number=?, dealer=?,
                model_mesin=?, no_mesin=?, no_rangka=?,
                tahun_unit=?,
                lokasi=?, jam_operasional=?,
                kondisi_terakhir=?, jumlah_perbaikan=?,
                riwayat_perbaikan=?
            WHERE id=?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        $_SESSION['error_alat_berat'] = "Gagal menyiapkan query: " . mysqli_error($conn);
        header("Location: alat_berat_edit.php?id=" . $id);
        exit;
    }

    // merek disamakan dengan merk_type supaya kedua kolom konsisten
    $merek = $merk_type;

    // Urutan tipe: s s s s s s s s s s s i s i s i s i
    // tanggal, nama_alat, nama_pemilik, jenis_kendaraan, merek, merk_type,
    // fleet_number, dealer, model_mesin, no_mesin, no_rangka,
    // tahun_unit(i),
    // lokasi, jam_operasional(i), kondisi_terakhir, jumlah_perbaikan(i),
    // riwayat_perbaikan, id(i)
    mysqli_stmt_bind_param(
        $stmt,
        "sssssssssssisisisi",
        $tanggal, $nama_alat, $nama_pemilik,
        $jenis_kendaraan, $merek, $merk_type,
        $fleet_number, $dealer,
        $model_mesin, $no_mesin, $no_rangka,
        $tahun_unit,
        $lokasi, $jam_operasional,
        $kondisi_terakhir, $jumlah_perbaikan,
        $riwayat_perbaikan, $id
    );

    $ok = mysqli_stmt_execute($stmt);

    if (!$ok) {
        $_SESSION['error_alat_berat'] = "Gagal menyimpan: " . mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        header("Location: alat_berat_edit.php?id=" . $id);
        exit;
    }

    mysqli_stmt_close($stmt);
    header("Location: ../alat_berat.php?success=1");
    exit;

  } catch (\Throwable $e) {
    $_SESSION['error_alat_berat'] = "Terjadi kesalahan saat menyimpan: " . $e->getMessage();
    header("Location: alat_berat_edit.php?id=" . $id);
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Alat Berat | PT. Sarana Karya Dua Satu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { min-height:100vh; background:linear-gradient(135deg,#0f172a,#1e293b,#334155); font-family:'Inter',sans-serif; }
        .card { border-radius:18px; border:none; box-shadow:0 8px 32px rgba(0,0,0,.15); }
        .card-header { background:linear-gradient(135deg,#1e40af,#2563eb); border-radius:18px 18px 0 0 !important; color:white; padding:1.25rem 1.75rem; font-size:1.1rem; }
        .section-title { font-size:.82rem; font-weight:700; color:#1e40af; text-transform:uppercase; letter-spacing:.8px; border-bottom:2px solid #bfdbfe; padding-bottom:.4rem; margin-bottom:1.1rem; display:flex; align-items:center; gap:.4rem; }
        .section-new { background:#f0fff4; border:1px solid #bfdbfe; border-radius:12px; padding:1.25rem 1.1rem .5rem; margin-bottom:.5rem; }
        .form-label { font-weight:600; font-size:.88rem; color:#2c3e50; }
        .form-control:focus, .form-select:focus { border-color:#2563eb; box-shadow:0 0 0 .15rem rgba(37,99,235,.2); }
        .form-text { font-size:.75rem; color:#6c757d; margin-top:.3rem; }
        .risiko-preview-box { background:#f8fffe; border:1px solid #bfdbfe; border-radius:12px; padding:1.25rem; margin-top:.5rem; }
        .risiko-label { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#6c757d; margin-bottom:.6rem; }
        .risiko-badge { padding:.45rem 1.1rem; border-radius:20px; font-weight:800; font-size:.9rem; display:inline-flex; align-items:center; gap:.4rem; }
        .r-rendah { background:#dbeafe; color:#1e3a8a; }
        .r-sedang  { background:#eff6ff; color:#856404; }
        .r-tinggi  { background:#f8d7da; color:#721c24; }
        .skor-bar  { height:8px; border-radius:8px; background:#e9ecef; overflow:hidden; margin-top:.5rem; }
        .skor-fill { height:100%; border-radius:8px; transition:.6s; }
        .umur-box { background:#f8fffe; border:1px solid #bfdbfe; border-radius:10px; padding:1rem; }
        .umur-value { font-size:2rem; font-weight:800; line-height:1; }
        .progress { height:10px; border-radius:10px; }
    </style>
</head>
<body>
<div class="container py-4" style="max-width:1000px">

    <?php if (!empty($_SESSION['error_alat_berat'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error_alat_berat']) ?>
        </div>
        <?php unset($_SESSION['error_alat_berat']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-header fw-bold">
            <i class="bi bi-pencil-square me-2"></i>Edit Data Alat Berat
            <small class="ms-2 opacity-75">Fleet: <?= htmlspecialchars($data['fleet_number'] ?? '-') ?></small>
        </div>
        <div class="card-body p-4">
        <form method="post" id="mainForm">

            <div class="section-title"><i class="bi bi-info-circle-fill"></i> Informasi Umum</div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($data['tanggal'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Pemilik</label>
                    <input type="text" name="nama_pemilik" class="form-control" value="<?= htmlspecialchars($data['nama_pemilik'] ?? '') ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Kendaraan</label>
                    <select name="jenis_kendaraan" class="form-select" id="selJenis">
                        <option value="">-- Pilih Jenis --</option>
                        <?php
                        $jenis_list = ['Excavator','Bulldozer','Crane','Wheel Loader','Motor Grader','Dump Truck','Compactor','Forklift','Backhoe Loader'];
                        $jk = $data['jenis_kendaraan'] ?? '';
                        foreach ($jenis_list as $j):
                            $sel = (strtolower($jk) == strtolower($j)) ? 'selected' : '';
                        ?>
                        <option value="<?= htmlspecialchars($j) ?>" <?= $sel ?>><?= htmlspecialchars($j) ?></option>
                        <?php endforeach; ?>
                        <?php if ($jk !== '' && !in_array($jk, $jenis_list)): ?>
                        <option value="<?= htmlspecialchars($jk) ?>" selected><?= htmlspecialchars($jk) ?> (lainnya)</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Merk / Type</label>
                    <input type="text" name="merk_type" class="form-control" value="<?= htmlspecialchars($data['merk_type'] ?? $data['merek'] ?? '') ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fleet Number</label>
                    <input type="text" name="fleet_number" class="form-control" value="<?= htmlspecialchars($data['fleet_number'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Dealer</label>
                    <input type="text" name="dealer" class="form-control" value="<?= htmlspecialchars($data['dealer'] ?? '') ?>">
                </div>
            </div>

            <div class="section-title mt-3"><i class="bi bi-gear-fill"></i> Data Mesin</div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Model Mesin</label>
                    <input type="text" name="model_mesin" class="form-control" value="<?= htmlspecialchars($data['model_mesin'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">No Mesin</label>
                    <input type="text" name="no_mesin" class="form-control" value="<?= htmlspecialchars($data['no_mesin'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">No Rangka</label>
                    <input type="text" name="no_rangka" class="form-control" value="<?= htmlspecialchars($data['no_rangka'] ?? '') ?>">
                </div>
            </div>

            <div class="section-title mt-3"><i class="bi bi-calendar-event-fill"></i> Tahun & Lokasi</div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tahun Unit</label>
                    <select name="tahun_unit" id="selTahunUnit" class="form-select" onchange="updateUmur()">
                        <option value="">-- Pilih --</option>
                        <?php
                        $ty = date('Y');
                        $groups = [
                            '🆕 Baru (0–7 thn)'   => [$ty,    $ty-7],
                            '🟡 Normal (8–12 thn)' => [$ty-8,  $ty-12],
                            '🔴 Tua (>12 thn)'     => [$ty-13, 2000],
                        ];
                        foreach ($groups as $glabel => [$dari, $sampai]):
                        ?>
                        <optgroup label="<?= $glabel ?>">
                        <?php for ($t = $dari; $t >= $sampai; $t--): ?>
                            <option value="<?= $t ?>" <?= ($data['tahun_unit']??'')==$t ? 'selected':'' ?>><?= $t ?></option>
                        <?php endfor; ?>
                        </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Umur Alat <small class="text-muted">(otomatis)</small></label>
                    <div class="umur-box" id="umurBox">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="umur-value" id="umurVal">–</span>
                                <span class="text-muted fw-semibold"> Thn</span>
                            </div>
                            <span id="umurLabel" class="badge fs-6 px-2 py-1"></span>
                        </div>
                        <div class="progress mt-2">
                            <div class="progress-bar" id="umurBar" style="width:0%;background:#2563eb"></div>
                        </div>
                        <div class="form-text mt-1" id="umurDesc">Pilih tahun unit terlebih dahulu</div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lokasi Operasi</label>
                    <select name="lokasi" id="selLokasi" class="form-select">
                        <option value="">-- Pilih Lokasi --</option>
                        <?php $lok = $data['lokasi'] ?? ''; ?>
                        <option value="Risiko Rendah" <?= $lok==='Risiko Rendah'?'selected':'' ?>>🟢 Risiko Rendah – Gudang / Workshop / Kota</option>
                        <option value="Risiko Sedang" <?= $lok==='Risiko Sedang'?'selected':'' ?>>🟡 Risiko Sedang – Perkebunan / Jalan Tanah / Perbukitan</option>
                        <option value="Risiko Tinggi" <?= $lok==='Risiko Tinggi'?'selected':'' ?>>🔴 Risiko Tinggi – Tambang / Rawa / Tambang Aktif / Suhu &gt;45°C</option>
                    </select>
                </div>
            </div>

            <div class="section-title mt-3"><i class="bi bi-tools"></i> Operasional & Perbaikan <span class="badge ms-1" style="background-color:#2563eb;color:white;font-size:.65rem">DATA ML</span></div>
            <div class="section-new">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-speedometer2" style="color: #2563eb;"></i> Jam Operasional</label>
                        <select id="selRangeJam" class="form-select mb-2" onchange="setJamRangeOnly(this.value)">
                            <option value="">-- Pilih Range --</option>
                            <optgroup label="🟢 Normal (0–3.000 jam)">
                                <option value="250"  <?= (($data['jam_operasional']??0)<=250)?'selected':'' ?>>0 – 250 jam</option>
                                <option value="500"  <?= (($data['jam_operasional']??0)>250&&($data['jam_operasional']??0)<=500)?'selected':'' ?>>251 – 500 jam</option>
                                <option value="1000" <?= (($data['jam_operasional']??0)>500&&($data['jam_operasional']??0)<=1000)?'selected':'' ?>>501 – 1.000 jam</option>
                                <option value="2000" <?= (($data['jam_operasional']??0)>1000&&($data['jam_operasional']??0)<=2000)?'selected':'' ?>>1.001 – 2.000 jam</option>
                                <option value="3000" <?= (($data['jam_operasional']??0)>2000&&($data['jam_operasional']??0)<=3000)?'selected':'' ?>>2.001 – 3.000 jam</option>
                            </optgroup>
                            <optgroup label="🟡 Waspada (3.001–5.000 jam)">
                                <option value="3500" <?= (($data['jam_operasional']??0)>3000&&($data['jam_operasional']??0)<=3500)?'selected':'' ?>>3.001 – 3.500 jam</option>
                                <option value="4000" <?= (($data['jam_operasional']??0)>3500&&($data['jam_operasional']??0)<=4000)?'selected':'' ?>>3.501 – 4.000 jam</option>
                                <option value="4500" <?= (($data['jam_operasional']??0)>4000&&($data['jam_operasional']??0)<=4500)?'selected':'' ?>>4.001 – 4.500 jam</option>
                                <option value="5000" <?= (($data['jam_operasional']??0)>4500&&($data['jam_operasional']??0)<=5000)?'selected':'' ?>>4.501 – 5.000 jam</option>
                            </optgroup>
                            <optgroup label="🔴 Kritis (5.001–8.000 jam)">
                                <option value="5500" <?= (($data['jam_operasional']??0)>5000&&($data['jam_operasional']??0)<=6000)?'selected':'' ?>>5.001 – 6.000 jam</option>
                                <option value="6500" <?= (($data['jam_operasional']??0)>6000&&($data['jam_operasional']??0)<=7000)?'selected':'' ?>>6.001 – 7.000 jam</option>
                                <option value="8000" <?= (($data['jam_operasional']??0)>7000)?'selected':'' ?>>7.001 – 8.000 jam (maksimum)</option>
                            </optgroup>
                        </select>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-speedometer2"></i></span>
                            <input type="number" name="jam_operasional" id="inputJam" class="form-control"
                                   value="<?= htmlspecialchars($data['jam_operasional'] ?? 0) ?>"
                                   min="0" max="8000" oninput="clampJam(this)">
                            <span class="input-group-text text-white fw-bold" style="background-color: #2563eb;">Jam</span>
                        </div>
                        <div id="jamStatus" class="mt-1"></div>
                        <div class="form-text">Maksimum input 8.000 jam.</div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-activity" style="color: #2563eb;"></i> Kondisi Terakhir</label>
                        <select name="kondisi_terakhir" id="selKondisi" class="form-select" onchange="updateKondisiPreview(this)">
                            <option value="">-- Pilih Kondisi --</option>
                            <?php foreach ($kondisi_valid as $kv): ?>
                                <option value="<?= htmlspecialchars($kv) ?>" <?= ($data['kondisi_terakhir']??'')===$kv?'selected':'' ?>>
                                    <?php
                                        $icons = ['Baik'=>'✅','Rusak'=>'❌','Dalam Perbaikan'=>'🔧'];
                                        echo $icons[$kv] . ' ' . htmlspecialchars($kv);
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="kondisiPreview" class="mt-1"></div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label"><i class="bi bi-wrench-adjustable" style="color: #2563eb;"></i> Jumlah Perbaikan</label>
                        <div class="input-group">
                            <input type="number" name="jumlah_perbaikan" id="inputPrb" class="form-control"
                                   value="<?= htmlspecialchars($data['jumlah_perbaikan'] ?? 0) ?>"
                                   min="0" max="10" oninput="clampPrb(this)">
                            <span class="input-group-text text-white fw-bold" style="background-color: #2563eb;">Kali</span>
                        </div>
                        <div id="prbStatus" class="mt-1"></div>
                        <div class="form-text">
                            0–2 = Baik &nbsp;|&nbsp; 3–6 = Sedang &nbsp;|&nbsp; 7–10 = Danger. Maksimum input 10 kali.
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-journal-text" style="color: #2563eb;"></i> Riwayat Perbaikan</label>
                    <textarea name="riwayat_perbaikan" class="form-control" rows="4"
                        placeholder="Contoh:&#10;12/01/2024 - Ganti oli mesin&#10;05/03/2024 - Perbaikan sistem hidrolik&#10;20/06/2024 - Servis rutin 500 jam"><?= htmlspecialchars($data['riwayat_perbaikan'] ?? '') ?></textarea>
                    <div class="form-text">Catat riwayat perbaikan secara kronologis.</div>
                </div>

                <div class="risiko-preview-box">
                    <div class="risiko-label"><i class="bi bi-cpu me-1"></i> Prediksi Risiko ML</div>
                    <button type="button" class="btn fw-bold mb-3" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;border:none;border-radius:10px;padding:.55rem 1.2rem" onclick="predictRisiko()">
                        <i class="bi bi-magic"></i> Prediksikan Risiko
                    </button>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="risiko-badge r-rendah" id="risikoResult">
                            <i class="bi bi-shield-check" id="risikoIcon"></i>
                            <span id="risikoText">Klik "Prediksikan Risiko" untuk melihat hasil</span>
                        </span>
                        <small class="text-muted">Skor: <strong id="risikoSkor">0</strong>/<span id="risikoMaks">10</span></small>
                    </div>
                    <div class="skor-bar mt-2">
                        <div class="skor-fill" id="risikoBar" style="width:0%;background:#2563eb"></div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="../alat_berat.php" class="btn btn-secondary px-4">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="submit" name="simpan" class="btn px-4 fw-bold" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%); color: white; border: none;">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
            </div>

        </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
function setJamRangeOnly(val) {
    if (val) {
        document.getElementById('inputJam').value = val;
        updateJamStatus(parseInt(val) || 0);
    }
}
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
function updateJamStatus(jam) {
    const el = document.getElementById('jamStatus');
    if (!jam) { el.innerHTML=''; return; }
    let bg,tx,lbl;
    if      (jam < 3000)  { bg='#e3f2fd'; tx='#1565c0'; lbl='🟢 Normal'; }
    else if (jam <= 5000) { bg='#eff6ff'; tx='#856404'; lbl='🟡 Waspada'; }
    else                  { bg='#f8d7da'; tx='#721c24'; lbl='🔴 Kritis — Overhaul Segera!'; }
    el.innerHTML = `<span style="background:${bg};color:${tx};padding:.25rem .75rem;border-radius:20px;font-weight:700;font-size:.78rem">${lbl}</span>`;
}
function updatePrbStatus(p) {
    const el = document.getElementById('prbStatus');
    if (p === '') { el.innerHTML=''; return; }
    let bg,tx,lbl;
    if      (p <= 2)  { bg='#dbeafe'; tx='#1e3a8a'; lbl='✅ Baik'; }
    else if (p <= 6)  { bg='#eff6ff'; tx='#856404'; lbl='⚠️ Sedang'; }
    else              { bg='#f8d7da'; tx='#721c24'; lbl='❌ Danger'; }
    el.innerHTML = `<span style="background:${bg};color:${tx};padding:.25rem .75rem;border-radius:20px;font-weight:700;font-size:.78rem">${lbl}</span>`;
}
function updateUmur() {
    const tahun = parseInt(document.getElementById('selTahunUnit').value) || 0;
    if (!tahun) return;
    const umur     = new Date().getFullYear() - tahun;
    const umurMaks = new Date().getFullYear() - 2000; // batas terlama Tahun Unit = tahun 2000
    const persen   = Math.min(100, Math.round((umur/umurMaks)*100));
    let color, label, desc;
    if      (umur <= 7)  { color='#2563eb'; label='🆕 Baru';   desc='Kondisi prima, risiko rendah'; }
    else if (umur <= 12) { color='#3b82f6'; label='🟡 Normal';  desc='Perlu perhatian rutin'; }
    else                  { color='#dc3545'; label='🔴 Tua';    desc='Risiko tinggi — evaluasi kelayakan operasi segera'; }
    document.getElementById('umurVal').textContent  = umur;
    document.getElementById('umurLabel').textContent = label;
    document.getElementById('umurLabel').style.background = color;
    document.getElementById('umurLabel').style.color      = 'white';
    document.getElementById('umurBar').style.width       = persen+'%';
    document.getElementById('umurBar').style.background  = color;
    document.getElementById('umurDesc').textContent = desc + ' · Sisa maks: '+ Math.max(0,umurMaks-umur)+' thn';
}

// ===== PREDIKSI RISIKO (ON-DEMAND, BUKAN LIVE) =====
// Bobot poin per fitur, diurutkan dari yang PALING berpengaruh ke yang PALING kecil:
// 1) Tahun Unit / Umur Alat  -> bobot maks 6 (paling berpengaruh; kalau kategori TUA, otomatis Risiko Tinggi)
// 2) Jam Operasional         -> bobot maks 5 (maks 8.000 jam)
// 3) Kondisi Terakhir        -> bobot maks 4
// 4) Jumlah Perbaikan        -> bobot maks 3 (maks 10 kali)
// 5) Lokasi Operasi          -> bobot maks 2
// 6) Jenis Kendaraan         -> bobot maks 1 (paling kecil pengaruhnya)
const SKOR_MAKS = 21; // 6 + 5 + 4 + 3 + 2 + 1

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

    // 2) Jam Operasional (bobot 5)
    if      (jam < 3000)  { skor += 0; kategoriJam = 'normal'; }
    else if (jam <= 5000) { skor += 3; kategoriJam = 'waspada'; }
    else                   { skor += 5; kategoriJam = 'kritis'; }

    // 3) Kondisi Terakhir (bobot 4)
    if      (kondisi === 'baik')            skor += 0;
    else if (kondisi === 'dalam perbaikan') skor += 2;
    else if (kondisi === 'rusak')           skor += 4;

    // 4) Jumlah Perbaikan (bobot 3)
    if      (prb <= 2) skor += 0;
    else if (prb <= 6) skor += 2;
    else                skor += 3;

    // 5) Lokasi Operasi (bobot 2)
    if      (lokasi === 'risiko rendah') skor += 0;
    else if (lokasi === 'risiko sedang') skor += 1;
    else if (lokasi === 'risiko tinggi') skor += 2;

    // 6) Jenis Kendaraan (bobot 1)
    if (jenis.includes('crane') || jenis.includes('bulldozer')) skor += 1;

    const skorTampil = Math.round((skor / SKOR_MAKS) * 10);

    let level, rclass, icon, color;
    // ATURAN KHUSUS: kalau Tahun Unit sudah kategori TUA, atau Jam Operasional sudah KRITIS
    // (2 faktor dengan bobot terbesar), paksa Risiko Tinggi apa pun faktor lainnya
    if (kategoriUmur === 'tua' || kategoriJam === 'kritis') {
        level='🔴 RISIKO TINGGI'; rclass='r-tinggi'; icon='bi-shield-x'; color='#dc3545';
    } else if (skorTampil <= 3) {
        level='🟢 RISIKO RENDAH'; rclass='r-rendah'; icon='bi-shield-check';       color='#2563eb';
    } else if (skorTampil <= 7) {
        level='🟡 RISIKO SEDANG';  rclass='r-sedang'; icon='bi-shield-exclamation'; color='#3b82f6';
    } else {
        level='🔴 RISIKO TINGGI';  rclass='r-tinggi'; icon='bi-shield-x';           color='#dc3545';
    }

    const badge = document.getElementById('risikoResult');
    badge.className = 'risiko-badge ' + rclass;
    document.getElementById('risikoIcon').className = 'bi ' + icon;
    document.getElementById('risikoText').textContent = level;
    document.getElementById('risikoSkor').textContent = skorTampil;
    document.getElementById('risikoMaks').textContent = 10;
    document.getElementById('risikoBar').style.width      = (skorTampil * 10) + '%';
    document.getElementById('risikoBar').style.background = color;
}

// Init (tidak lagi memanggil prediksi ML otomatis saat halaman dibuka)
window.addEventListener('load', () => {
    updateKondisiPreview(document.getElementById('selKondisi'));
    updateUmur();
    updateJamStatus(parseInt(document.getElementById('inputJam').value) || 0);
    updatePrbStatus(parseInt(document.getElementById('inputPrb').value) || 0);
});
</script>
</body>
</html>