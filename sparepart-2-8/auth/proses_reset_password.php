<?php
require_once "../config/koneksi.php";

$token = $_POST['token'] ?? '';
$passwordInput = $_POST['password'] ?? '';

if ($token == '' || $passwordInput == '') {
    die("Data tidak valid");
}

$password = password_hash($passwordInput, PASSWORD_DEFAULT);

$cek = mysqli_query($conn, "SELECT * FROM users WHERE reset_token='$token'");
if (mysqli_num_rows($cek) === 0) {
    die("Token tidak valid");
}

mysqli_query($conn, "
    UPDATE users SET
    password='$password',
    reset_token=NULL,
    reset_expired=NULL
    WHERE reset_token='$token'
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Password Berhasil Direset</title>
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

.card-success{
    background:#ffffff;
    border-radius:24px;
    box-shadow:0 25px 70px rgba(0,0,0,.35);
    max-width:420px;
    width:100%;
    text-align:center;
    padding:40px 30px;
    animation:fadeUp .6s ease;
}

@keyframes fadeUp{
    from{opacity:0; transform:translateY(30px);}
    to{opacity:1; transform:translateY(0);}
}

.icon-success{
    width:90px;
    height:90px;
    background:linear-gradient(135deg,var(--primary),var(--secondary));
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 20px;
    box-shadow:0 12px 30px rgba(22,163,74,.6);
}

.icon-success i{
    font-size:42px;
    color:white;
}

h4{
    font-weight:700;
    margin-bottom:8px;
}

p{
    color:#64748b;
    font-size:14px;
    margin-bottom:25px;
}

.btn-login{
    background:linear-gradient(135deg,#15803d,#16a34a);
    color:white;
    border:none;
    border-radius:14px;
    padding:14px;
    font-weight:700;
    font-size:16px;
    width:100%;
    transition:all .3s ease;
    box-shadow:0 10px 28px rgba(22,163,74,.55);
}

.btn-login:hover{
    transform:translateY(-2px);
    background:linear-gradient(135deg,#0f172a,#1e40af);
    box-shadow:0 16px 36px rgba(22,163,74,.75);
}
</style>
</head>

<body>

<div class="card-success">
    <div class="icon-success">
        <i class="fas fa-check"></i>
    </div>

    <h4>Password Berhasil Direset</h4>
    <p>Silakan login kembali menggunakan password baru Anda.</p>

    <a href="login.php" class="btn btn-login">
        <i class="fas fa-sign-in-alt me-2"></i>Ke Halaman Login
    </a>
</div>

</body>
</html>
