<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/koneksi.php';

/* ================= QUERY ================= */
$query = mysqli_query($conn, "SELECT * FROM inventaris ORDER BY id DESC");
if (!$query) {
    die(mysqli_error($conn));
}

/* ================= HEADER EXCEL ================= */
$filename = "Data_Inventaris_" . date('Y-m-d') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

/* BOM UTF-8 */
echo "\xEF\xBB\xBF";
?>

<table border="1" width="100%"
       style="border-collapse:collapse;font-family:Calibri,Arial;font-size:11pt;">

    <!-- JUDUL -->
    <tr>
        <td colspan="11"
            style="
                font-size:16pt;
                font-weight:bold;
                text-align:center;
                padding:12px;
                background:#2563eb;
                color:#ffffff;
                border:1px solid #000;
            ">
            LAPORAN DATA INVENTARIS
        </td>
    </tr>

    <!-- SUB JUDUL -->
    <tr>
        <td colspan="11"
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
        <td colspan="11"
            style="
                text-align:center;
                font-size:10pt;
                padding:6px;
                border:1px solid #000;
            ">
            Tanggal Export : <?= date('d-m-Y H:i:s') ?>
        </td>
    </tr>

    <!-- SPASI -->
    <tr>
        <td colspan="11" style="border:none;height:10px;"></td>
    </tr>

    <!-- HEADER KOLOM -->
    <tr style="
        background:#dbeafe;
        font-weight:bold;
        text-align:center;
        vertical-align:middle;
    ">
        <?php
        $headers = [
            'No',
            'Tanggal Masuk',
            'Kode Barang',
            'Nama Barang',
            'Jenis Inventaris',
            'Kategori',
            'Harga Perolehan',
            'Pengguna Saat Ini',
            'Lokasi',
            'Kondisi',
            'Keterangan'
        ];
        foreach ($headers as $h) {
            echo "<th style='border:1px solid #000;padding:8px;'>$h</th>";
        }
        ?>
    </tr>

    <!-- DATA -->
    <?php
    $no = 1;
    $total_harga = 0;
    while ($row = mysqli_fetch_assoc($query)) {
        $total_harga += $row['harga_perolehan'];
    ?>
        <tr style="vertical-align:top;">
            <td style="border:1px solid #000;text-align:center;padding:6px;"><?= $no++ ?></td>
            
            <td style="border:1px solid #000;text-align:center;padding:6px;">
                <?= !empty($row['tanggal_masuk']) ? date('d/m/Y', strtotime($row['tanggal_masuk'])) : '-' ?>
            </td>
            
            <td style="border:1px solid #000;padding:6px;font-family:'Courier New';font-weight:bold;">
                <?= htmlspecialchars($row['kode_barang']) ?>
            </td>
            
            <td style="border:1px solid #000;padding:6px;font-weight:bold;">
                <?= htmlspecialchars($row['nama_barang']) ?>
            </td>
            
            <td style="border:1px solid #000;padding:6px;">
                <?= htmlspecialchars($row['jenis_inventaris']) ?>
            </td>
            
            <td style="border:1px solid #000;padding:6px;">
                <?= htmlspecialchars($row['kategori']) ?>
            </td>
            
            <td style="border:1px solid #000;padding:6px;text-align:right;font-weight:bold;">
                Rp <?= number_format($row['harga_perolehan'], 0, ',', '.') ?>
            </td>
            
            <td style="border:1px solid #000;padding:6px;">
                <?= htmlspecialchars($row['pengguna_saat_ini']) ?: '-' ?>
            </td>
            
            <td style="border:1px solid #000;padding:6px;">
                <?= htmlspecialchars($row['lokasi']) ?: '-' ?>
            </td>
            
            <td style="border:1px solid #000;text-align:center;padding:6px;
                <?php 
                if ($row['kondisi'] == 'Baik') {
                    echo 'background:#dbeafe;color:#065f46;font-weight:bold;';
                } elseif ($row['kondisi'] == 'Rusak') {
                    echo 'background:#fef3c7;color:#92400e;font-weight:bold;';
                } elseif ($row['kondisi'] == 'Perbaikan') {
                    echo 'background:#fee2e2;color:#991b1b;font-weight:bold;';
                }
                ?>
            ">
                <?= htmlspecialchars($row['kondisi']) ?>
            </td>
            
            <td style="border:1px solid #000;padding:6px;"></td>
        </tr>
    <?php } ?>

    <!-- SPASI -->
    <tr>
        <td colspan="11" style="border:none;height:5px;"></td>
    </tr>

    <!-- TOTAL HARGA -->
    <tr style="background:#e7f3ff;">
        <td colspan="6" 
            style="
                border:1px solid #000;
                padding:8px;
                font-weight:bold;
                text-align:right;
                font-size:12pt;
            ">
            TOTAL HARGA PEROLEHAN :
        </td>
        <td style="
                border:1px solid #000;
                padding:8px;
                text-align:right;
                font-weight:bold;
                font-size:12pt;
                background:#cfe2ff;
            ">
            Rp <?= number_format($total_harga, 0, ',', '.') ?>
        </td>
        <td colspan="4" style="border:1px solid #000;"></td>
    </tr>

    <!-- SPASI -->
    <tr>
        <td colspan="11" style="border:none;height:10px;"></td>
    </tr>

    <!-- RINGKASAN KONDISI -->
    <tr>
        <td colspan="11"
            style="
                border:1px solid #000;
                padding:8px;
                font-size:11pt;
                font-weight:bold;
                background:#f8f9fa;
            ">
            RINGKASAN KONDISI INVENTARIS
        </td>
    </tr>

    <?php
    // Query ringkasan kondisi
    $kondisi_query = mysqli_query($conn, "
        SELECT 
            kondisi,
            COUNT(*) as jumlah,
            SUM(harga_perolehan) as total_nilai
        FROM inventaris 
        GROUP BY kondisi
        ORDER BY 
            CASE kondisi
                WHEN 'Baik' THEN 1
                WHEN 'Rusak' THEN 2
                WHEN 'Perbaikan' THEN 3
                ELSE 4
            END
    ");

    while ($kondisi = mysqli_fetch_assoc($kondisi_query)) {
        $bg_color = '#ffffff';
        $text_color = '#000000';
        
        if ($kondisi['kondisi'] == 'Baik') {
            $bg_color = '#d1fae5';
            $text_color = '#065f46';
        } elseif ($kondisi['kondisi'] == 'Rusak') {
            $bg_color = '#fef3c7';
            $text_color = '#92400e';
        } elseif ($kondisi['kondisi'] == 'Perbaikan') {
            $bg_color = '#fee2e2';
            $text_color = '#991b1b';
        }
    ?>
        <tr>
            <td colspan="2" 
                style="
                    border:1px solid #000;
                    padding:8px;
                    font-weight:bold;
                    background:<?= $bg_color ?>;
                    color:<?= $text_color ?>;
                ">
                <?= htmlspecialchars($kondisi['kondisi']) ?>
            </td>
            <td colspan="4" 
                style="border:1px solid #000;padding:8px;">
                <?= $kondisi['jumlah'] ?> Item
            </td>
            <td colspan="5" 
                style="border:1px solid #000;padding:8px;text-align:right;font-weight:bold;">
                Rp <?= number_format($kondisi['total_nilai'], 0, ',', '.') ?>
            </td>
        </tr>
    <?php } ?>

    <!-- SPASI -->
    <tr>
        <td colspan="11" style="border:none;height:10px;"></td>
    </tr>

    <!-- FOOTER -->
    <tr>
        <td colspan="11"
            style="
                border:1px solid #000;
                padding:8px;
                font-size:9pt;
                text-align:center;
            ">
            Generated by Sistem Manajemen Inventaris PT. Sarana Karya Dua Satu
        </td>
    </tr>

</table>

<?php
ob_end_flush();
exit;
?>