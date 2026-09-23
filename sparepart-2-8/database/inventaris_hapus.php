<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";

// Cek apakah ada parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../inventaris.php?status=not_found");
    exit;
}

$id = intval($_GET['id']); // Sanitasi input

// Ambil data sebelum dihapus untuk disimpan ke history
$query = "SELECT * FROM inventaris WHERE id = $id";
$result = mysqli_query($conn, $query);

if (!$result) {
    header("Location: ../inventaris.php?status=error");
    exit;
}

$data = mysqli_fetch_assoc($result);

if ($data) {
    // Mulai transaction untuk keamanan
    mysqli_begin_transaction($conn);
    
    try {
        // 1. Simpan ke tabel history
        $stmt = mysqli_prepare($conn, "INSERT INTO inventaris_history 
            (tanggal_masuk, kode_barang, nama_barang, jenis_inventaris, kategori, harga_perolehan, pengguna_saat_ini, lokasi, kondisi, deleted_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        if (!$stmt) {
            throw new Exception("Prepare statement gagal");
        }
        
        mysqli_stmt_bind_param($stmt, "sssssdsss", 
            $data['tanggal_masuk'], 
            $data['kode_barang'], 
            $data['nama_barang'], 
            $data['jenis_inventaris'], 
            $data['kategori'], 
            $data['harga_perolehan'], 
            $data['pengguna_saat_ini'], 
            $data['lokasi'], 
            $data['kondisi']
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Execute statement gagal");
        }
        
        mysqli_stmt_close($stmt);
        
        // 2. Hapus data dari tabel utama
        $delete_query = "DELETE FROM inventaris WHERE id = $id";
        
        if (!mysqli_query($conn, $delete_query)) {
            throw new Exception("Delete gagal");
        }
        
        // Cek apakah data benar-benar terhapus
        $affected = mysqli_affected_rows($conn);
        
        if ($affected > 0) {
            // Commit transaction jika semua berhasil
            mysqli_commit($conn);
            header("Location: ../inventaris.php?status=deleted");
            exit;
        } else {
            throw new Exception("Tidak ada data yang terhapus");
        }
        
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($conn);
        header("Location: ../inventaris.php?status=error");
        exit;
    }
    
} else {
    // Data tidak ditemukan
    header("Location: ../inventaris.php?status=not_found");
    exit;
}

// Tutup koneksi
mysqli_close($conn);
exit;
?>