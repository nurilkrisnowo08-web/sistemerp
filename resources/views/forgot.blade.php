<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password - Sistem MRP</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
/* CSS SAMA PERSIS DENGAN LOGIN LO */
body{
    margin:0; height:100vh; display:flex; justify-content:center; align-items:center;
    font-family: 'Segoe UI', sans-serif;
    background: url("{{ asset('admin/img/pt.jpg') }}") no-repeat center center;
    background-size:cover;
}
.overlay{ position:absolute; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:0; }
.card{
    position:relative; z-index:1; width:450px; padding:45px; border-radius:18px;
    background: rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.25);
    box-shadow:0 15px 35px rgba(0,0,0,0.6); color:white; animation:fadeUp 0.6s ease;
}
@keyframes fadeUp{ from{opacity:0; transform:translateY(30px);} to{opacity:1; transform:translateY(0);} }
.header{ display:flex; align-items:center; margin-bottom:30px; }
.logo{ width:95px; margin-right:18px; }
.title h1{ margin:0; font-size:24px; font-weight:700; letter-spacing:1px; }
.title span{ font-size:12px; opacity:0.85; }
.input-group{ margin-bottom:18px; }
.input-group input{ width:100%; padding:13px; border-radius:10px; border:none; outline:none; font-size:14px; box-sizing: border-box; }
button{ width:100%; padding:13px; border:none; border-radius:10px; background:#007bff; color:white; font-weight:bold; cursor:pointer; transition:0.3s; font-size:15px; }
button:hover{ background:#0056b3; transform:scale(1.02); }
.links{ text-align:center; margin-top:18px; font-size:14px; }
.links a{ color:#fff; text-decoration:none; }
.alert{ margin-bottom:15px; font-size:14px; text-align:center; }
</style>
</head>
<body>

<div class="overlay"></div>

<div class="card">
    <div class="header">
        <img src="{{ asset('admin/img/logo-asalta.png') }}" class="logo">
        <div class="title">
            <h1>RESET PASSWORD</h1>
            <span>PT. ASALTA MANDIRI AGUNG</span>
        </div>
    </div>

    @if(session('error'))
        <div class="alert" style="color:#ffb3b3;">{{ session('error') }}</div>
    @endif

    {{-- SAKTI: Form Reset Password sesuai AuthController --}}
    <form method="POST" action="{{ route('forgot.post') }}">
        @csrf
        <div class="input-group">
            <input type="email" name="email" placeholder="Masukkan Email Terdaftar" required>
        </div>
        <div class="input-group">
            <input type="password" name="password" placeholder="Masukkan Password Baru" required>
        </div>
        <button type="submit">Update Password Sekarang</button>
    </form>

    <div class="links">
        <a href="{{ route('login') }}">Kembali ke Login</a>
    </div>
</div>

</body>
</html>