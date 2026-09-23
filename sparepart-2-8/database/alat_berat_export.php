<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/prediksi_risiko.php'; // Algoritma Decision Tree - Prediksi Risiko

/* ================= QUERY ================= */
$query = mysqli_query($conn, "SELECT * FROM alat_berat ORDER BY id DESC");
if (!$query) {
    die(mysqli_error($conn));
}

/* ================= HEADER EXCEL ================= */
$filename = "Data_Alat_Berat_" . date('Y-m-d') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

/* BOM UTF-8 */
echo "\xEF\xBB\xBF";
?>

<table border="1" width="100%"
       style="border-collapse:collapse;font-family:Calibri,Arial;font-size:11pt;">

    <tr>
        <td colspan="13"
            style="
                font-size:16pt;
                font-weight:bold;
                text-align:center;
                padding:12px;
                background:#2563eb;
                color:#ffffff;
                border:1px solid #000;
            ">
            LAPORAN DATA ALAT BERAT
        </td>
    </tr>

    <tr>
        <td colspan="13"
            style="
                text-align:center;
                font-size:11pt;
                padding:6px;
                border:1px solid #000;
                font-weight:bold;
            ">
            PT. SARANA KARYA DUA SATU
        </td>
    </tr>

    <tr>
        <td colspan="13"
            style="
                text-align:center;
                font-size:10pt;
                padding:6px;
                border:1px solid #000;
            ">
            Tanggal Export : <?= date('d-m-Y H:i:s') ?>
        </td>
    </tr>

    <tr>
        <td colspan="13" style="border:none;height:10px;"></td>
    </tr>

    <tr style="
        background:#dbeafe;
        font-weight:bold;
        text-align:center;
        vertical-align:middle;
    ">
        <?php
        $headers = [
            'No',
            'Nama Pemilik',
            'Jenis Kendaraan',
            'Merk / Type',
            'Fleet Number',
            'Dealer',
            'Model Mesin',
            'No Mesin',
            'No Rangka',
            'Tahun Unit',
            'Lokasi',
            'Hasil Prediksi Risiko',
            'Keterangan'
        ];
        foreach ($headers as $h) {
            echo "<th style='border:1px solid #000;padding:8px;'>$h</th>";
        }
        ?>
    </tr>

    <?php
    $no = 1;
    $total_unit = 0;
    $total_risiko_tinggi = 0;
    $total_risiko_sedang = 0;
    $total_risiko_rendah = 0;

    while ($row = mysqli_fetch_assoc($query)) {
        $total_unit++;
        $risiko = prediksiRisikoAlat($row);
        if ($risiko['rclass'] === 'r-tinggi') $total_risiko_tinggi++;
        elseif ($risiko['rclass'] === 'r-sedang') $total_risiko_sedang++;
        else $total_risiko_rendah++;
    ?>
        <tr style="vertical-align:top;">
            <td style="border:1px solid #000;text-align:center;padding:6px;"><?= $no++ ?></td>
            
            <td style="border:1px solid #000;padding:6px;font-weight:bold;">
                <?= htmlspecialchars($row['nama_pemilik'] ?? '') ?>
            </td>
            
            <td style="border:1px solid #000;padding:6px;">
                <?= htmlspecialchars($row['jenis_kendaraan'] ?? '') ?>
            </td>
            
            <td style="border:1px solid #000;padding:6px;font-weight:bold;">
                <?= htmlspecialchars($row['merk_type'] ?? '') ?>
            </td>
            
            <td style="border:1px solid #000;padding:6px;font-family:'Courier New';font-weight:bold;text-align:center;">
                <?= htmlspecialchars($row['fleet_number'] ?? '') ?>
            </td>
            
            <td style="border:1px solid #000;padding:6px;">
                <?= htmlspecialchars($row['dealer'] ?? '') ?: '-' ?>
            </td>
            
            <td style="border:1px solid #000;padding:6px;">
                <?= htmlspecialchars($row['model_mesin'] ?? '') ?: '-' ?>
            </td>
            
            <td style="border:1px solid #000;padding:6px;font-family:'Courier New';">
                <?= htmlspecialchars($row['no_mesin'] ?? '') ?: '-' ?>
            </td>
            
            <td style="border:1px solid #000;padding:6px;font-family:'Courier New';">
                <?= htmlspecialchars($row['no_rangka'] ?? '') ?: '-' ?>
            </td>

            <td style="border:1px solid #000;text-align:center;padding:6px;font-weight:bold;">
                <?= ($row['tahun_unit'] ?? '') ?: '-' ?>
            </td>

            <td style="border:1px solid #000;padding:6px;">
                <?= htmlspecialchars($row['lokasi'] ?? '') ?: '-' ?>
            </td>

            <td style="border:1px solid #000;text-align:center;padding:6px;font-weight:bold;
                <?php
                if ($risiko['rclass'] === 'r-tinggi') {
                    echo 'background:#f8d7da;color:#721c24;';
                } elseif ($risiko['rclass'] === 'r-sedang') {
                    echo 'background:#fff3cd;color:#856404;';
                } else {
                    echo 'background:#dbeafe;color:#1e3a8a;';
                }
                ?>
            ">
                <?= htmlspecialchars($risiko['level']) ?> (<?= $risiko['skor'] ?>/10)
            </td>

            <td style="border:1px solid #000;padding:6px;"></td>
        </tr>
    <?php } ?>

    <tr>
        <td colspan="13" style="border:none;height:5px;"></td>
    </tr>

    <tr>
        <td colspan="13"
            style="
                border:1px solid #000;
                padding:8px;
                font-size:11pt;
                font-weight:bold;
                background:#f8f9fa;
            ">
            RINGKASAN DATA ALAT BERAT
        </td>
    </tr>

    <tr>
        <td colspan="6" 
            style="
                border:1px solid #000;
                padding:8px;
                font-weight:bold;
            ">
            Total Seluruh Unit
        </td>
        <td colspan="7" 
            style="
                border:1px solid #000;
                padding:8px;
                text-align:center;
                font-weight:bold;
                font-size:12pt;
                background:#e7f3ff;
            ">
            <?= $total_unit ?> Unit
        </td>
    </tr>

    <tr>
        <td colspan="6" 
            style="
                border:1px solid #000;
                padding:8px;
                font-weight:bold;
            ">
            Unit Risiko Rendah
        </td>
        <td colspan="7" 
            style="
                border:1px solid #000;
                padding:8px;
                text-align:center;
                font-weight:bold;
                font-size:12pt;
                background:#dbeafe;
                color:#1e3a8a;
            ">
            <?= $total_risiko_rendah ?> Unit
        </td>
    </tr>

    <tr>
        <td colspan="6" 
            style="
                border:1px solid #000;
                padding:8px;
                font-weight:bold;
            ">
            Unit Risiko Sedang
        </td>
        <td colspan="7" 
            style="
                border:1px solid #000;
                padding:8px;
                text-align:center;
                font-weight:bold;
                font-size:12pt;
                background:#fff3cd;
                color:#856404;
            ">
            <?= $total_risiko_sedang ?> Unit
        </td>
    </tr>

    <tr>
        <td colspan="6" 
            style="
                border:1px solid #000;
                padding:8px;
                font-weight:bold;
            ">
            Unit Risiko Tinggi
        </td>
        <td colspan="7" 
            style="
                border:1px solid #000;
                padding:8px;
                text-align:center;
                font-weight:bold;
                font-size:12pt;
                background:#fee2e2;
                color:#991b1b;
            ">
            <?= $total_risiko_tinggi ?> Unit
        </td>
    </tr>

    <tr>
        <td colspan="13" style="border:none;height:10px;"></td>
    </tr>

    <tr>
        <td colspan="13"
            style="
                border:1px solid #000;
                padding:8px;
                font-size:11pt;
                font-weight:bold;
                background:#f8f9fa;
            ">
            RINGKASAN PER JENIS KENDARAAN
        </td>
    </tr>

    <?php
    // Query ringkasan per jenis
    $jenis_query = mysqli_query($conn, "
        SELECT 
            jenis_kendaraan,
            COUNT(*) as jumlah
        FROM alat_berat 
        GROUP BY jenis_kendaraan
        ORDER BY jumlah DESC
    ");

    while ($jenis = mysqli_fetch_assoc($jenis_query)) {
        // Antisipasi jika ada jenis_kendaraan yang bernilai NULL di database
        $nama_jenis = $jenis['jenis_kendaraan'] ?? 'Tidak Diketahui';
    ?>
        <tr>
            <td colspan="6" 
                style="
                    border:1px solid #000;
                    padding:8px;
                    font-weight:bold;
                    background:#e7f3ff;
                ">
                <?= htmlspecialchars($nama_jenis) ?>
            </td>
            <td colspan="7" 
                style="border:1px solid #000;padding:8px;text-align:center;font-weight:bold;">
                <?= $jenis['jumlah'] ?> Unit
            </td>
        </tr>
    <?php } ?>

    <tr>
        <td colspan="13" style="border:none;height:10px;"></td>
    </tr>

    <tr>
        <td colspan="13"
            style="
                border:1px solid #000;
                padding:8px;
                font-size:9pt;
                text-align:center;
            ">
            Generated by System Management Alat Berat PT. Sarana Karya Dua Satu
        </td>
    </tr>

</table>

<?php
ob_end_flush();
exit;
?>