<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_GET['id'])) {
    header("Location: alat_berat_history.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

/* =====================
   HAPUS DATA HISTORY
===================== */
mysqli_query($conn, "DELETE FROM alat_berat_history WHERE id='$id'");

header("Location: alat_berat_history.php?delete=success");
exit;
