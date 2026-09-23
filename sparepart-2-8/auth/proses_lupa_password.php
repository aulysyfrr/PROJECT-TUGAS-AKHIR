<?php
require_once "../config/koneksi.php";

$email = mysqli_real_escape_string($conn, $_POST['email']);

$query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
$user  = mysqli_fetch_assoc($query);

if (!$user) {
    echo "<script>alert('Email tidak terdaftar');history.back();</script>";
    exit;
}

$token   = bin2hex(random_bytes(32));
$expired = date("Y-m-d H:i:s", time() + 3600); // 1 jam

$update = mysqli_query($conn, "
    UPDATE users SET 
    reset_token='$token',
    reset_expired='$expired'
    WHERE email='$email'
");

if (!$update) {
    die("Gagal menyimpan token");
}

$link = "http://localhost/SPAREPART/auth/reset_password.php?token=$token";
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Link Reset Password</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root{
    --primary:#1e40af;
    --secondary:#3b82f6;
    --accent:#60a5fa;
}
body{
    min-height:100vh;
    background:linear-gradient(135deg,var(--primary),var(--secondary),var(--yellow));
    display:flex;
    align-items:center;
    justify-content:center;
    font-family:'Segoe UI',sans-serif;
}
.card-reset{
    background:white;
    border-radius:20px;
    box-shadow:0 20px 60px rgba(0,0,0,.3);
    max-width:450px;
    width:100%;
    overflow:hidden;
}
.header{
    background:linear-gradient(135deg,var(--primary),var(--secondary));
    color:white;
    text-align:center;
    padding:30px;
}
.header i{
    font-size:48px;
    margin-bottom:10px;
}
.body{
    padding:30px;
    text-align:center;
}
.link-box{
    background:#f1f5f9;
    border-radius:10px;
    padding:15px;
    word-break:break-all;
    font-size:14px;
    margin:20px 0;
}
.btn-copy{
    background:linear-gradient(135deg,var(--primary),var(--secondary));
    color:white;
    border:none;
    border-radius:10px;
    padding:12px;
    font-weight:600;
}
.btn-copy:hover{
    background:linear-gradient(135deg,#0f172a,#1e40af);
}
.back-link{
    margin-top:20px;
}
.back-link a{
    text-decoration:none;
    color:#64748b;
    font-size:14px;
}
.back-link a:hover{
    color:var(--secondary);
}
.note{
    font-size:13px;
    color:#64748b;
}
</style>
</head>

<body>

<div class="card-reset">
    <div class="header">
        <i class="fas fa-envelope-open-text"></i>
        <h4 class="mb-0">Link Reset Password</h4>
        <small>Gunakan link di bawah untuk reset password</small>
    </div>

    <div class="body">
        <p class="note">
            Untuk keamanan, link ini hanya berlaku <strong>1 jam</strong>.
        </p>

        <div class="link-box" id="resetLink">
            <?= htmlspecialchars($link) ?>
        </div>

        <a href="<?= $link ?>" class="btn btn-copy w-100 mb-2">
            <i class="fas fa-key me-2"></i>Buka Halaman Reset
        </a>

        <div class="back-link">
            <a href="login.php">
                <i class="fas fa-arrow-left me-1"></i>Kembali ke Login
            </a>
        </div>
    </div>
</div>

</body>
</html>
