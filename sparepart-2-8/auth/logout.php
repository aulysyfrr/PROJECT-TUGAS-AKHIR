<?php
session_start();
$_SESSION = [];
session_destroy();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logout | PT. Sarana Karya Dua Satu</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
:root{
    --green:#2563eb;
    --dark:#1e40af;
    --yellow:#3b82f6;
}

*{
    font-family: 'Inter', sans-serif;
}

body{
    min-height:100vh;
    background: linear-gradient(135deg, #1e40af, #2563eb, #3b82f6);
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}

/* background decoration */
body::before,
body::after{
    content:'';
    position:absolute;
    width:600px;
    height:600px;
    background: radial-gradient(circle, rgba(148,163,184,0.1), transparent 70%);
    animation: pulse 12s infinite alternate;
}
body::after{
    right:-200px;
    bottom:-200px;
}
body::before{
    left:-200px;
    top:-200px;
}

@keyframes pulse{
    from{transform:scale(1);}
    to{transform:scale(1.15);}
}

.logout-card{
    position:relative;
    z-index:2;
    background:rgba(255,255,255,0.95);
    backdrop-filter: blur(12px);
    border-radius:20px;
    box-shadow:0 20px 50px rgba(0,0,0,.25);
    padding:2.5rem;
    text-align:center;
    width:100%;
    max-width:420px;
    animation: fadeUp .8s ease;
}

@keyframes fadeUp{
    from{opacity:0;transform:translateY(40px);}
    to{opacity:1;transform:translateY(0);}
}

.logo{
    height:70px;
    margin-bottom:1rem;
}

.check-icon{
    width:90px;
    height:90px;
    border-radius:50%;
    background:linear-gradient(135deg, var(--green), var(--accent-blue));
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 1rem;
    box-shadow:0 10px 25px rgba(25,135,84,.4);
}

.check-icon i{
    font-size:3rem;
    color:white;
}

.company{
    font-weight:700;
    color:var(--dark);
}

.loading-bar{
    height:6px;
    background:#e9ecef;
    border-radius:50px;
    overflow:hidden;
    margin-top:1.5rem;
}

.loading-bar span{
    display:block;
    height:100%;
    width:0;
    background:linear-gradient(135deg, var(--green), var(--accent-blue));
    animation: loading 3s forwards;
}

@keyframes loading{
    to{width:100%;}
}
</style>

<script>
setTimeout(()=>{
    window.location.href="login.php";
},3000);
</script>
</head>

<body>

<div class="logout-card">
    <img src="../assets/img/image.png" class="logo" alt="Logo">

    <div class="check-icon">
        <i class="bi bi-check-lg"></i>
    </div>

    <h5 class="fw-bold mb-1" style="color: #1e40af;">Logout Berhasil</h5>
    <p class="text-muted mb-2">
        Terima kasih telah menggunakan sistem
    </p>

    <p class="company mb-3">
        PT. Sarana Karya Dua Satu
    </p>

    <div class="loading-bar">
        <span></span>
    </div>

    <small class="text-muted d-block mt-3">
        Mengarahkan ke halaman login...
    </small>

    <a href="login.php" class="btn btn-sm mt-3 px-4" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%); color: white; border: none;">
        Login Sekarang
    </a>
</div>

</body>
</html>
