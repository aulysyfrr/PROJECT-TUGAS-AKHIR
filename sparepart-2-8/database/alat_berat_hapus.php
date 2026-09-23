<?php
session_start();
/*
|====================================================
| KONEKSI DATABASE
|====================================================
*/
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../config/db_helper.php";

// Matikan mode "throw exception" bawaan mysqli (PHP 8.1+),
// supaya error query tidak bikin halaman putih / fatal error.
mysqli_report(MYSQLI_REPORT_OFF);

/*
|====================================================
| VALIDASI ID
|====================================================
*/
if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    header("Location: ../alat_berat.php");
    exit;
}
$id = (int) $_GET['id'];

try {

    /*
    |====================================================
    | AMBIL DATA LAMA
    |====================================================
    */
    $stmt = mysqli_prepare($conn, "SELECT * FROM alat_berat WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data   = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$data) {
        header("Location: ../alat_berat.php");
        exit;
    }

    /*
    |====================================================
    | SIAPKAN NILAI (dengan fallback aman)
    |====================================================
    */
    $nama_pemilik      = $data['nama_pemilik']      ?? '';
    $jenis_kendaraan   = $data['jenis_kendaraan']   ?? '';
    $merk_type         = $data['merk_type']         ?? ($data['merek'] ?? '');
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
    $created_at        = $data['created_at']        ?? date('Y-m-d H:i:s');

    /*
    |====================================================
    | SIMPAN KE HISTORY + HAPUS DATA UTAMA (1 TRANSAKSI)
    |====================================================
    | Cek dulu apakah kolom tahun_pembelian/tahun_terjual MASIH
    | ada di tabel alat_berat_history (belum di-drop) -> pilih
    | salah satu dari 2 query siap pakai. Tidak ada logika
    | dinamis lagi supaya tidak ada resiko mismatch bind_param.
    */
    mysqli_begin_transaction($conn);

    $masihAdaKolomLama = kolomAda($conn, 'alat_berat_history', 'tahun_pembelian')
                       && kolomAda($conn, 'alat_berat_history', 'tahun_terjual');

    if ($masihAdaKolomLama) {
        $sql = "INSERT INTO alat_berat_history (
                    nama_pemilik, jenis_kendaraan, merk_type, fleet_number, dealer,
                    model_mesin, no_mesin, no_rangka, tahun_unit, tahun_pembelian, tahun_terjual,
                    lokasi, jam_operasional, kondisi_terakhir, jumlah_perbaikan, riwayat_perbaikan,
                    created_at, tanggal_hapus
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())";

        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "ssssssssiiisisiss",
                $nama_pemilik, $jenis_kendaraan, $merk_type, $fleet_number, $dealer,
                $model_mesin, $no_mesin, $no_rangka, $tahun_unit, $tahun_pembelian, $tahun_terjual,
                $lokasi, $jam_operasional, $kondisi_terakhir, $jumlah_perbaikan, $riwayat_perbaikan,
                $created_at
            );
        }
    } else {
        $sql = "INSERT INTO alat_berat_history (
                    nama_pemilik, jenis_kendaraan, merk_type, fleet_number, dealer,
                    model_mesin, no_mesin, no_rangka, tahun_unit,
                    lokasi, jam_operasional, kondisi_terakhir, jumlah_perbaikan, riwayat_perbaikan,
                    created_at, tanggal_hapus
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())";

        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "ssssssssisisiss",
                $nama_pemilik, $jenis_kendaraan, $merk_type, $fleet_number, $dealer,
                $model_mesin, $no_mesin, $no_rangka, $tahun_unit,
                $lokasi, $jam_operasional, $kondisi_terakhir, $jumlah_perbaikan, $riwayat_perbaikan,
                $created_at
            );
        }
    }

    if (!$stmt) {
        mysqli_rollback($conn);
        $_SESSION['error_alat_berat'] = "Gagal menyiapkan query history: " . mysqli_error($conn);
        header("Location: ../alat_berat.php");
        exit;
    }

    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        mysqli_rollback($conn);
        $_SESSION['error_alat_berat'] = "Gagal menyimpan history: " . mysqli_error($conn);
        header("Location: ../alat_berat.php");
        exit;
    }

    /*
    |====================================================
    | HAPUS DATA UTAMA
    |====================================================
    */
    $stmt = mysqli_prepare($conn, "DELETE FROM alat_berat WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    $ok       = mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok || $affected === 0) {
        // Gagal hapus (misal ditahan FOREIGN KEY dari tabel lain) -> batalkan insert history juga
        mysqli_rollback($conn);
        $pesan = mysqli_error($conn);
        if (stripos($pesan, 'foreign key') !== false || stripos($pesan, 'constraint') !== false) {
            $_SESSION['error_alat_berat'] = "Data tidak bisa dihapus karena masih dipakai/direferensikan oleh data lain (foreign key constraint). Detail: " . $pesan;
        } else {
            $_SESSION['error_alat_berat'] = "Gagal menghapus data (id={$id}, affected={$affected}). " . $pesan;
        }
        header("Location: ../alat_berat.php");
        exit;
    }

    mysqli_commit($conn);

    header("Location: ../alat_berat.php?deleted=1");
    exit;

} catch (\Throwable $e) {
    mysqli_rollback($conn);
    $_SESSION['error_alat_berat'] = "Terjadi kesalahan saat menghapus: " . $e->getMessage();
    header("Location: ../alat_berat.php");
    exit;
}
