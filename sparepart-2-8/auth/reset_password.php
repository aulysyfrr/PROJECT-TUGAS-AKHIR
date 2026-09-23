<?php
require_once "../config/koneksi.php";

$token = $_GET['token'] ?? '';
if ($token == '') {
    die("Token kosong");
}

$query = mysqli_query($conn, "SELECT * FROM users WHERE reset_token='$token'");
if (mysqli_num_rows($query) === 0) {
    die("Token tidak valid");
}

$user = mysqli_fetch_assoc($query);
if (strtotime($user['reset_expired']) < time()) {
    die("Token sudah kadaluarsa");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Reset Password</title>
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
    background:linear-gradient(135deg,var(--primary),var(--secondary),var(--accent));
    display:flex;
    align-items:center;
    justify-content:center;
    font-family:'Segoe UI',sans-serif;
}

.card-reset{
    background:#ffffff;
    border-radius:22px;
    box-shadow:0 25px 70px rgba(0,0,0,.35);
    max-width:420px;
    width:100%;
    overflow:hidden;
    animation: fadeUp .6s ease;
}

@keyframes fadeUp{
    from{opacity:0; transform:translateY(30px);}
    to{opacity:1; transform:translateY(0);}
}

.header{
    background:linear-gradient(135deg,var(--primary),var(--secondary));
    color:#ffffff;
    padding:32px;
    text-align:center;
}

.header i{
    font-size:48px;
    margin-bottom:8px;
}

.body{
    padding:32px;
}

.form-label{
    font-weight:600;
    font-size:14px;
    margin-bottom:6px;
}

.form-control{
    border-radius:12px;
    padding:14px 16px;
    font-size:15px;
    border:2px solid #e2e8f0;
}

.form-control:focus{
    border-color:var(--secondary);
    box-shadow:0 0 0 4px rgba(37,99,235,.15);
}

.password-wrapper{
    position:relative;
}

.toggle-password{
    position:absolute;
    right:14px;
    top:50%;
    transform:translateY(-50%);
    background:none;
    border:none;
    cursor:pointer;
    color:#64748b;
    font-size:16px;
}

.toggle-password:hover{
    color:var(--secondary);
}

/* ===== TOMBOL FIX TOTAL ===== */
.btn-save{
    margin-top:20px;
    width:100%;
    background:linear-gradient(135deg,#1e40af,#2563eb);
    color:#ffffff;
    border:none;
    border-radius:14px;
    padding:15px 18px;
    font-weight:700;
    font-size:16px;
    letter-spacing:.3px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    transition:all .3s ease;
    box-shadow:0 10px 28px rgba(37,99,235,.55);
}

.btn-save:hover{
    transform:translateY(-2px);
    background:linear-gradient(135deg,#1e293b,#1e40af);
    box-shadow:0 16px 36px rgba(37,99,235,.75);
}

.btn-save:active{
    transform:translateY(0);
    box-shadow:0 8px 18px rgba(37,99,235,.45);
}
</style>
</head>

<body>

<div class="card-reset">
    <div class="header">
        <i class="fas fa-key"></i>
        <h4 class="mb-1">Reset Password</h4>
        <small>Buat password baru Anda</small>
    </div>

    <div class="body">
        <form method="POST" action="proses_reset_password.php">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password baru" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-save">
                <i class="fas fa-lock"></i>
                Simpan Password
            </button>
        </form>
    </div>
</div>

<script>
function togglePassword(){
    const input = document.getElementById('password');
    const icon  = document.querySelector('.toggle-password i');

    if(input.type === "password"){
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }else{
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

</body>
</html>
