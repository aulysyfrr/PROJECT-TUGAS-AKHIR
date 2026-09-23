<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Lupa Password - PT. Sarana Karya Dua Satu</title>
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
    border-radius:20px;
    box-shadow:0 20px 60px rgba(0,0,0,.3);
    overflow:hidden;
    max-width:420px;
    width:100%;
}
.header{
    background:linear-gradient(135deg,var(--primary),var(--secondary));
    color:white;
    padding:30px;
    text-align:center;
}
.header i{
    font-size:50px;
    margin-bottom:10px;
}
.body{
    padding:30px;
}
.form-control{
    border-radius:10px;
    padding:12px;
}
.btn-reset{
    background:linear-gradient(135deg,var(--primary),var(--secondary));
    color:white;
    border:none;
    padding:12px;
    border-radius:10px;
    font-weight:600;
}
.btn-reset:hover{
    background:linear-gradient(135deg,#1e293b,#1e40af);
}
.back-link{
    text-align:center;
    margin-top:15px;
}
.back-link a{
    text-decoration:none;
    color:#64748b;
    font-size:14px;
}
.back-link a:hover{
    color:var(--secondary);
}
</style>
</head>

<body>

<div class="card card-reset">
    <div class="header">
        <i class="fas fa-unlock-alt"></i>
        <h4 class="mb-0">Lupa Password</h4>
        <small>Masukkan email akun Anda</small>
    </div>

    <div class="body">
        <form method="POST" action="proses_lupa_password.php">
            <div class="mb-3">
                <label class="form-label">Alamat Email</label>
                <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
            </div>

            <button type="submit" class="btn btn-reset w-100">
                <i class="fas fa-paper-plane me-2"></i>Kirim Link Reset
            </button>
        </form>

        <div class="back-link">
            <a href="login.php"><i class="fas fa-arrow-left me-1"></i>Kembali ke Login</a>
        </div>
    </div>
</div>

</body>
</html>
