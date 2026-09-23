<?php
// import.php
include 'koneksi.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Fungsi convert tanggal Excel ke MySQL
function excelDateToMysql($value)
{
    if ($value === null || $value === '') {
        return null;
    }

    // Jika numeric (tanggal Excel)
    if (is_numeric($value)) {
        return date('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value));
    }

    // Jika string tanggal
    $time = strtotime($value);
    return $time ? date('Y-m-d', $time) : null;
}

if (isset($_POST['import'])) {

    $file = $_FILES['file_excel']['tmp_name'];
    if (!$file) {
        die('File tidak ditemukan');
    }

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();

    // Kosongkan data lama
    mysqli_query($conn, "TRUNCATE TABLE sparepart");

    // LOOP DARI ATAS (BARIS 2)
    for ($row = 2; $row <= $highestRow; $row++) {

        $nama_barang = trim($sheet->getCellByColumnAndRow(8, $row)->getValue());
        if ($nama_barang === '') {
            continue;
        }

        $tgl_invoice = excelDateToMysql($sheet->getCellByColumnAndRow(2, $row)->getValue());
        $tanggal     = excelDateToMysql($sheet->getCellByColumnAndRow(18, $row)->getValue());

        $sql = "INSERT INTO sparepart (
            tgl_invoice, no_invoice, vendor, rencana_alokasi, project,
            part_number, nama_barang, qty, satuan,
            harga_satuan, harga_jumlah, harga_diskon, harga_pajak, harga_total,
            lokasi, keluar, tanggal, spb, sisa, satuan_sisa
        ) VALUES (
            ".($tgl_invoice ? "'$tgl_invoice'" : "NULL").",
            '".$sheet->getCellByColumnAndRow(3, $row)->getValue()."',
            '".$sheet->getCellByColumnAndRow(4, $row)->getValue()."',
            '".$sheet->getCellByColumnAndRow(5, $row)->getValue()."',
            '".$sheet->getCellByColumnAndRow(6, $row)->getValue()."',
            '".$sheet->getCellByColumnAndRow(7, $row)->getValue()."',
            '$nama_barang',
            '".(int)$sheet->getCellByColumnAndRow(9, $row)->getValue()."',
            '".$sheet->getCellByColumnAndRow(10, $row)->getValue()."',
            '".(float)$sheet->getCellByColumnAndRow(11, $row)->getValue()."',
            '".(float)$sheet->getCellByColumnAndRow(12, $row)->getValue()."',
            '".(float)$sheet->getCellByColumnAndRow(13, $row)->getValue()."',
            '".(float)$sheet->getCellByColumnAndRow(14, $row)->getValue()."',
            '".(float)$sheet->getCellByColumnAndRow(15, $row)->getValue()."',
            '".$sheet->getCellByColumnAndRow(16, $row)->getValue()."',
            '".(int)$sheet->getCellByColumnAndRow(17, $row)->getValue()."',
            ".($tanggal ? "'$tanggal'" : "NULL").",
            '".$sheet->getCellByColumnAndRow(19, $row)->getValue()."',
            '".(int)$sheet->getCellByColumnAndRow(20, $row)->getValue()."',
            '".$sheet->getCellByColumnAndRow(21, $row)->getValue()."'
        )";

        mysqli_query($conn, $sql);
    }

    header("Location: sparepart.php?import=success");
    exit;
}
