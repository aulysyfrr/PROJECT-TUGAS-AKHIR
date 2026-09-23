<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../config/db_helper.php";

// Matikan mode "throw exception" bawaan mysqli (PHP 8.1+),
// supaya error query tidak bikin halaman putih / fatal error.
mysqli_report(MYSQLI_REPORT_OFF);

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    header("Location: alat_berat_history.php");
    exit;
}
$id = (int)$_GET['id'];

try {

    $stmt = mysqli_prepare($conn, "SELECT * FROM alat_berat_history WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data   = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$data) {
        header("Location: alat_berat_history.php");
        exit;
    }

    $nama_pemilik      = $data['nama_pemilik']      ?? '';
    $jenis_kendaraan   = $data['jenis_kendaraan']   ?? '';
    $merk_type         = $data['merk_type']         ?? '';
    $fleet_number      = $data['fleet_number']      ?? '';
    $dealer            = $data['dealer']            ?? '';
    $model_mesin       = $data['model_mesin']       ?? '';
    $no_mesin          = $data['no_mesin']          ?? '';
    $no_rangka         = $data['no_rangka']         ?? '';
    $tahun_unit        = (int)($data['tahun_unit'] ?? 0);
    $tahun_pembelian   = (int)($data['tahun_pembelian'] ?? 0);
    $tahun_terjual     = (int)($data['tahun_terjual'] ?? 0);
    $lokasi            = $data['lokasi']            ?? '';
    $jam_operasional   = (int)($data['jam_operasional'] ?? 0);
    $kondisi_terakhir  = $data['kondisi_terakhir']  ?? '';
    $jumlah_perbaikan  = (int)($data['jumlah_perbaikan'] ?? 0);
    $riwayat_perbaikan = $data['riwayat_perbaikan'] ?? '';

    // Cek dulu apakah kolom tahun_pembelian/tahun_terjual MASIH ada di
    // tabel alat_berat (belum di-drop) -> pilih salah satu dari 2 query siap pakai.
    $masihAdaKolomLama = kolomAda($conn, 'alat_berat', 'tahun_pembelian')
                       && kolomAda($conn, 'alat_berat', 'tahun_terjual');

    if ($masihAdaKolomLama) {
        $sql = "INSERT INTO alat_berat (
                    nama_pemilik, jenis_kendaraan, merk_type, fleet_number,
                    dealer, model_mesin, no_mesin, no_rangka,
                    tahun_unit, tahun_pembelian, tahun_terjual, lokasi,
                    jam_operasional, kondisi_terakhir, jumlah_perbaikan, riwayat_perbaikan
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "ssssssssiiisisis",
                $nama_pemilik, $jenis_kendaraan, $merk_type, $fleet_number,
                $dealer, $model_mesin, $no_mesin, $no_rangka,
                $tahun_unit, $tahun_pembelian, $tahun_terjual, $lokasi,
                $jam_operasional, $kondisi_terakhir, $jumlah_perbaikan, $riwayat_perbaikan
            );
        }
    } else {
        $sql = "INSERT INTO alat_berat (
                    nama_pemilik, jenis_kendaraan, merk_type, fleet_number,
                    dealer, model_mesin, no_mesin, no_rangka,
                    tahun_unit, lokasi,
                    jam_operasional, kondisi_terakhir, jumlah_perbaikan, riwayat_perbaikan
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "ssssssssisisis",
                $nama_pemilik, $jenis_kendaraan, $merk_type, $fleet_number,
                $dealer, $model_mesin, $no_mesin, $no_rangka,
                $tahun_unit, $lokasi,
                $jam_operasional, $kondisi_terakhir, $jumlah_perbaikan, $riwayat_perbaikan
            );
        }
    }

    if (!$stmt) {
        $_SESSION['error_alat_berat'] = "Gagal menyiapkan query restore: " . mysqli_error($conn);
        header("Location: alat_berat_history.php");
        exit;
    }

    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        $_SESSION['error_alat_berat'] = "Gagal restore data: " . mysqli_error($conn);
        header("Location: alat_berat_history.php");
        exit;
    }

    // Hapus dari history
    $stmt = mysqli_prepare($conn, "DELETE FROM alat_berat_history WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    $okDelete = mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if (!$okDelete || $affected === 0) {
        $_SESSION['error_alat_berat'] = "Data berhasil dipindah ke alat_berat, tapi gagal dihapus dari history (id={$id}). " . mysqli_error($conn);
        header("Location: alat_berat_history.php");
        exit;
    }

    $_SESSION['success_alat_berat'] = "Data berhasil di-restore ke daftar alat berat.";
    header("Location: ../alat_berat.php?restored=1");
    exit;

} catch (\Throwable $e) {
    $_SESSION['error_alat_berat'] = "Terjadi kesalahan saat restore: " . $e->getMessage();
    header("Location: alat_berat_history.php");
    exit;
}
