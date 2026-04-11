<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile - Sistem MRP</title>

<style>
body{
    margin:0;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    font-family:'Segoe UI',sans-serif;
    background: url("{{ asset('admin/img/pt.jpg') }}") no-repeat center center;
    background-size:cover;
}

.overlay{
    position:absolute;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.6);
}

.card{
    position:relative;
    z-index:1;
    width:450px;
    padding:40px;
    border-radius:18px;
    background:rgba(255,255,255,0.08);
    color:white;
}

input{
    width:100%;
    padding:12px;
    border-radius:8px;
    border:none;
    margin-bottom:15px;
}

button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    background:#007bff;
    color:white;
}
</style>
</head>

<body>

<div class="overlay"></div>

<div class="card">

<h2>Edit Profile</h2>

@if(session('success'))
<div style="background:green;padding:10px;margin-bottom:10px">
{{ session('success') }}
</div>
@endif

<form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
@csrf

<label>Nama</label>
<input type="text" name="name" value="{{ auth()->user()->name }}" required>

<label>Email</label>
<input type="email" value="{{ auth()->user()->email }}" disabled>

<label>Upload Foto</label>
<input type="file" name="photo">

@if(auth()->user()->photo)
<div style="text-align:center;margin-bottom:15px;">
<img src="{{ asset('storage/public/profile/'.auth()->user()->photo) }}" width="120">
</div>
@endif

<button type="submit">Update Profile</button>

</form>

<div style="text-align:center;margin-top:15px;">
<a href="/dashboard" style="color:white;">← Kembali ke Dashboard</a>
</div>

</div>
</body>
</html>
