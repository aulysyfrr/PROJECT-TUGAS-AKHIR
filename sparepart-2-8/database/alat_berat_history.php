<?php
session_start();
include __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../config/prediksi_risiko.php"; // Algoritma Decision Tree - Prediksi Risiko

$result        = mysqli_query($conn, "SELECT * FROM alat_berat_history ORDER BY tanggal_hapus DESC");
$total_history = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>History Alat Berat | PT. Sarana Karya Dua Satu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root { --grad:linear-gradient(135deg,#1e40af,#2563eb,#3b82f6); --gradient-green:linear-gradient(135deg,#1e40af,#2563eb); --shadow:0 8px 32px rgba(0,0,0,.15); }
body { font-family:'Inter',sans-serif; background:var(--grad); min-height:100vh; }

.page-header { background:#fff; border-radius:22px; box-shadow:var(--shadow); padding:2rem; margin-bottom:2rem; position:relative; overflow:hidden; }
.page-header::before { content:''; position:absolute; top:0;left:0;right:0; height:6px; background:var(--grad); border-radius:22px 22px 0 0; }
.company-logo { width:80px; padding:8px; border-radius:14px; box-shadow:0 4px 14px rgba(0,0,0,.1); }
.pro-title { font-size:2rem; font-weight:800; margin:0; background:linear-gradient(135deg,#1e40af,#3b82f6); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
.pro-subtitle { font-size:.9rem; color:#6c757d; margin-top:4px; }
.btn-back { border:2px solid #1e40af; color:#1e40af; padding:.65rem 1.4rem; border-radius:12px; font-weight:600; text-decoration:none; transition:.3s; display:inline-flex; align-items:center; gap:.5rem; }
.btn-back:hover { background:#1e40af; color:#fff; }

.stat-card { background:#fff; border-radius:18px; padding:1.6rem; box-shadow:var(--shadow); margin-bottom:2rem; }
.stat-icon { width:52px; height:52px; background:linear-gradient(135deg,#2563eb,#3b82f6); color:#fff; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; margin-bottom:.6rem; }

.table-container { background:#fff; border-radius:22px; padding:2rem; box-shadow:var(--shadow); }
.custom-table thead th {
    background:linear-gradient(135deg,#1e40af,#2563eb) !important; color:#fff !important;
    font-size:.78rem; text-align:center; vertical-align:middle; white-space:normal;
    line-height:1.4; padding:.9rem .7rem; border:none;
    border-right:1px solid rgba(255,255,255,.25);
}
.custom-table thead th:first-child { border-radius:12px 0 0 0; }
.custom-table thead th:last-child  { border-radius:0 12px 0 0; border-right:none; }
.custom-table tbody tr:hover { background:#f0fff4; }
.custom-table tbody td { vertical-align:middle; font-size:.875rem; padding:.85rem .7rem; border-bottom:1px solid #f0f0f0; text-align:center; }
.custom-table tbody tr:last-child td { border-bottom:none; }

/* ===== Badge styles disamakan persis dengan alat_berat.php ===== */
.fleet-number {
    font-weight:700; color:white; background:var(--gradient-green);
    padding:.35rem .85rem; border-radius:8px; display:inline-block;
    font-size:.85rem; letter-spacing:.5px; box-shadow:0 2px 6px rgba(37,99,235,.3);
}
.badge-year {
    background:linear-gradient(135deg,#6c757d 0%,#495057 100%); color:white;
    padding:.3rem .8rem; border-radius:6px; font-weight:600; display:inline-block;
    font-size:.85rem; box-shadow:0 2px 4px rgba(0,0,0,.1);
}
.location-badge {
    background:linear-gradient(135deg,#eff6ff 0%,#ffe69c 100%); color:#856404;
    padding:.3rem .75rem; border-radius:6px; font-weight:600; display:inline-flex;
    align-items:center; gap:.3rem; font-size:.85rem;
}
.date-badge {
    background:linear-gradient(135deg,#e3f2fd 0%,#bbdefb 100%); color:#1565c0;
    padding:.3rem .75rem; border-radius:6px; font-weight:600; display:inline-block; font-size:.85rem;
}
.code-text {
    font-family:'Courier New',monospace; background:#f8f9fa; padding:.25rem .5rem;
    border-radius:4px; font-size:.8rem; border:1px solid #e9ecef; color:#495057;
}
.jam-badge {
    background:linear-gradient(135deg,#e8f4fd 0%,#bee3f8 100%); color:#1a6fa3;
    padding:.35rem .75rem; border-radius:8px; font-weight:700; display:inline-flex;
    align-items:center; gap:.3rem; font-size:.85rem; box-shadow:0 2px 6px rgba(26,111,163,.15);
}
.kondisi-badge {
    padding:.35rem .85rem; border-radius:20px; font-weight:700; display:inline-flex;
    align-items:center; gap:.35rem; font-size:.82rem; letter-spacing:.3px;
}
.kondisi-baik      { background:linear-gradient(135deg,#dbeafe 0%,#bfdbfe 100%); color:#1e3a8a; box-shadow:0 2px 6px rgba(21,87,36,.15); }
.kondisi-sedang    { background:linear-gradient(135deg,#eff6ff 0%,#ffe69c 100%); color:#856404; box-shadow:0 2px 6px rgba(133,100,4,.15); }
.kondisi-rusak     { background:linear-gradient(135deg,#f8d7da 0%,#f5c6cb 100%); color:#721c24; box-shadow:0 2px 6px rgba(114,28,36,.15); }
.kondisi-perbaikan { background:linear-gradient(135deg,#d1ecf1 0%,#bee5eb 100%); color:#0c5460; box-shadow:0 2px 6px rgba(12,84,96,.15); }

.perbaikan-count { width:44px; height:44px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:1rem; box-shadow:0 3px 8px rgba(0,0,0,.15); }
.count-0    { background:linear-gradient(135deg,#dbeafe,#bfdbfe); color:#1e3a8a; }
.count-low  { background:linear-gradient(135deg,#eff6ff,#ffe69c); color:#856404; }
.count-mid  { background:linear-gradient(135deg,#fde6cc,#fcd1a3); color:#7d4100; }
.count-high { background:linear-gradient(135deg,#f8d7da,#f5c6cb); color:#721c24; }

.riwayat-text   { font-size:.83rem; color:#495057; line-height:1.5; max-width:200px; word-break:break-word; text-align:left; }
.riwayat-empty  { color:#adb5bd; font-style:italic; font-size:.83rem; }
.riwayat-toggle { cursor:pointer; color:#1a6fa3; font-size:.8rem; font-weight:600; border:none; background:none; padding:0; text-decoration:underline; }
.riwayat-full   { display:none; margin-top:.3rem; }
.riwayat-full.show { display:block; }

/* Hasil Prediksi Risiko (Decision Tree) - disamakan dengan alat_berat.php */
.risiko-badge { padding:.4rem .9rem; border-radius:20px; font-weight:800; font-size:.8rem; display:inline-flex; align-items:center; gap:.4rem; white-space:nowrap; }
.risiko-badge .risiko-skor { display:block; font-weight:600; font-size:.68rem; opacity:.85; }
.r-rendah { background:#dbeafe; color:#1e3a8a; }
.r-sedang { background:#eff6ff; color:#856404; }
.r-tinggi { background:#f8d7da; color:#721c24; }

.del-badge { background:linear-gradient(135deg,#fdecea,#f8d7da); color:#c62828; padding:.3rem .75rem; border-radius:6px; font-weight:600; display:inline-flex; align-items:center; gap:.3rem; font-size:.85rem; }

.btn-action  { padding:.5rem .75rem; border-radius:8px; border:none; font-size:.9rem; transition:.3s; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; }
.btn-action:hover { transform:translateY(-2px); }
.btn-restore { background:linear-gradient(135deg,#2563eb,#3b82f6); color:#fff; box-shadow:0 2px 8px rgba(37,99,235,.3); }
.btn-restore:hover  { color:#fff; box-shadow:0 4px 12px rgba(37,99,235,.5); }
.btn-del-perm { background:linear-gradient(135deg,#dc3545,#c82333); color:#fff; box-shadow:0 2px 8px rgba(220,53,69,.3); }
.btn-del-perm:hover { color:#fff; box-shadow:0 4px 12px rgba(220,53,69,.5); }

.empty-state { text-align:center; padding:4rem 2rem; color:#6c757d; }
.empty-state i { font-size:4rem; color:#dee2e6; margin-bottom:1rem; }
.empty-state h5 { color:#495057; font-weight:600; margin-bottom:.5rem; }
</style>
</head>
<body>
<div class="container-fluid p-4">

<!-- HEADER -->
<div class="page-header">
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
        <img src="../assets/img/image.png" class="company-logo" alt="Logo">
        <div>
            <h2 class="pro-title"><i class="bi bi-archive-fill me-2"></i>History Alat Berat</h2>
            <div class="pro-subtitle">Arsip data alat berat yang telah dihapus dari sistem</div>
        </div>
    </div>
    <a href="../alat_berat.php" class="btn-back"><i class="bi bi-arrow-left-circle"></i> Kembali</a>
</div>
</div>

<!-- STAT -->
<div class="stat-card">
    <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
    <small class="text-muted fw-bold text-uppercase" style="font-size:.75rem">Total History</small>
    <h3 class="fw-800 mt-1" style="color:#1e40af;font-size:2rem"><?= $total_history ?></h3>
</div>

<!-- TABLE (kolom disamakan dengan alat_berat.php, ditambah Tanggal Hapus & Aksi) -->
<div class="table-container">
<div class="table-responsive">
<table class="table custom-table align-middle mb-0">
<thead><tr>
    <th>NO</th>
    <th>TANGGAL</th>
    <th>NAMA<br>PEMILIK</th>
    <th>JENIS<br>KENDARAAN</th>
    <th>MERK/TYPE</th>
    <th>FLEET<br>NUMBER</th>
    <th>DEALER</th>
    <th>MODEL<br>MESIN</th>
    <th>NO MESIN</th>
    <th>NO RANGKA</th>
    <th>TAHUN<br>UNIT</th>
    <th>LOKASI</th>
    <th>JAM<br>OPERASIONAL</th>
    <th>KONDISI<br>TERAKHIR</th>
    <th>JUMLAH<br>PERBAIKAN</th>
    <th>RIWAYAT<br>PERBAIKAN</th>
    <th>HASIL PREDIKSI<br>RISIKO</th>
    <th>TANGGAL<br>HAPUS</th>
    <th>AKSI</th>
</tr></thead>
<tbody>

<?php if ($total_history == 0): ?>
    <tr>
        <td colspan="19" class="p-0">
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>Belum ada history</h5>
                <p>Data alat berat yang dihapus akan muncul di sini.</p>
            </div>
        </td>
    </tr>
<?php else: $no = 1; while ($row = mysqli_fetch_assoc($result)):

    // Kondisi (sama persis dengan logika di alat_berat.php)
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

    // Jumlah perbaikan
    $jml = (int)($row['jumlah_perbaikan'] ?? 0);
    if      ($jml === 0)  $count_class = 'count-0';
    elseif  ($jml <= 3)   $count_class = 'count-low';
    elseif  ($jml <= 7)   $count_class = 'count-mid';
    else                  $count_class = 'count-high';

    // Riwayat perbaikan
    $riwayat_full  = htmlspecialchars($row['riwayat_perbaikan'] ?? '');
    $riwayat_short = mb_strlen($riwayat_full) > 80 ? mb_substr($riwayat_full, 0, 80) . '...' : $riwayat_full;
    $has_more      = mb_strlen($riwayat_full) > 80;

    // Hasil Prediksi Risiko (Algoritma Decision Tree)
    $risiko = prediksiRisikoAlat($row);
?>
<tr>
    <td><?= $no++ ?></td>
    <td>
        <?php if (!empty($row['created_at'])): ?>
            <span class="date-badge"><?= date('d/m/Y', strtotime($row['created_at'])) ?></span>
        <?php else: ?>
            <span class="text-muted">-</span>
        <?php endif; ?>
    </td>
    <td><?= htmlspecialchars($row['nama_pemilik']) ?></td>
    <td><?= htmlspecialchars($row['jenis_kendaraan']) ?></td>
    <td><strong><?= htmlspecialchars($row['merk_type']) ?></strong></td>
    <td><span class="fleet-number"><?= htmlspecialchars($row['fleet_number']) ?></span></td>
    <td><?= htmlspecialchars($row['dealer']) ?></td>
    <td><?= htmlspecialchars($row['model_mesin']) ?></td>
    <td><span class="code-text"><?= htmlspecialchars($row['no_mesin']) ?></span></td>
    <td><span class="code-text"><?= htmlspecialchars($row['no_rangka']) ?></span></td>
    <td><span class="badge-year"><?= htmlspecialchars($row['tahun_unit']) ?></span></td>
    <td>
        <span class="location-badge"><i class="bi bi-geo-alt-fill"></i><?= htmlspecialchars($row['lokasi']) ?></span>
    </td>

    <!-- Jam Operasional -->
    <td>
        <?php if (!empty($row['jam_operasional'])): ?>
            <span class="jam-badge"><i class="bi bi-speedometer2"></i><?= number_format((int)$row['jam_operasional']) ?> Jam</span>
        <?php else: ?>
            <span class="text-muted">-</span>
        <?php endif; ?>
    </td>

    <!-- Kondisi Terakhir -->
    <td>
        <?php if (!empty($row['kondisi_terakhir'])): ?>
            <span class="kondisi-badge <?= $kondisi_class ?>"><i class="bi <?= $kondisi_icon ?>"></i><?= htmlspecialchars($row['kondisi_terakhir']) ?></span>
        <?php else: ?>
            <span class="text-muted">-</span>
        <?php endif; ?>
    </td>

    <!-- Jumlah Perbaikan -->
    <td><span class="perbaikan-count <?= $count_class ?>"><?= $jml ?></span></td>

    <!-- Riwayat Perbaikan -->
    <td>
        <?php if (!empty($riwayat_full)): ?>
            <div class="riwayat-text">
                <span class="riwayat-preview"><?= $riwayat_short ?></span>
                <?php if ($has_more): ?>
                    <div class="riwayat-full" id="rh-<?= $row['id'] ?>"><?= nl2br($riwayat_full) ?></div>
                    <button class="riwayat-toggle mt-1" onclick="toggleRiwayat('rh-<?= $row['id'] ?>', this)">Lihat selengkapnya ▼</button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <span class="riwayat-empty"><i class="bi bi-dash"></i> Belum ada riwayat</span>
        <?php endif; ?>
    </td>

    <!-- Hasil Prediksi Risiko (Decision Tree) -->
    <td>
        <span class="risiko-badge <?= $risiko['rclass'] ?>">
            <i class="bi <?= $risiko['icon'] ?>"></i>
            <span>
                <?= $risiko['level'] ?>
                <span class="risiko-skor">Skor: <?= $risiko['skor'] ?>/10</span>
            </span>
        </span>
    </td>

    <!-- Tanggal Hapus -->
    <td><span class="del-badge"><i class="bi bi-calendar-x"></i><?= date('d/m/Y H:i', strtotime($row['tanggal_hapus'])) ?></span></td>

    <!-- Aksi -->
    <td>
        <div class="d-flex gap-1 justify-content-center">
            <a href="alat_berat_restore.php?id=<?= $row['id'] ?>" class="btn btn-action btn-restore" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></a>
            <a href="alat_berat_delete_permanent.php?id=<?= $row['id'] ?>" class="btn btn-action btn-del-perm"
               onclick="return confirm('Hapus permanen? Tidak dapat dibatalkan!')" title="Hapus Permanen"><i class="bi bi-trash"></i></a>
        </div>
    </td>
</tr>
<?php endwhile; endif; ?>
</tbody>
</table>
</div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleRiwayat(id, btn) {
    const el = document.getElementById(id);
    const preview = btn.previousElementSibling;
    if (el.classList.contains('show')) {
        el.classList.remove('show');
        preview.style.display = 'inline';
        btn.textContent = 'Lihat selengkapnya ▼';
    } else {
        el.classList.add('show');
        preview.style.display = 'none';
        btn.textContent = 'Sembunyikan ▲';
    }
}
</script>
</body>
</html>