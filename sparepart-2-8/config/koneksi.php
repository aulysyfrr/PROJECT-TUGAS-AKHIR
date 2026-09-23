<?php
// ========================================
// FILE: config/koneksi.php
// ========================================

$host = "localhost";
$user = "root";
$pass = "";
$db   = "sparepart_db"; // ✅ SESUAI DATABASE SQL

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
