<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun - Sistem MRP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
            background: url("{{ asset('admin/img/pt.jpg') }}") no-repeat center center;
            background-size: cover;
        }

        .overlay {
            position: absolute;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 0;
        }

        .card {
            position: relative;
            z-index: 1;
            width: 450px;
            padding: 45px;
            border-radius: 18px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.25);
            box-shadow: 0 15px 35px rgba(0,0,0,0.6);
            color: white;
            animation: fadeUp 0.6s ease;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* HEADER */
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 95px;
            margin-right: 18px;
        }

        .title h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .title span {
            font-size: 13px;
            opacity: 0.85;
        }

        /* INPUT */
        .input-group {
            margin-bottom: 18px;
        }

        .input-group input {
            width: 100%;
            padding: 13px;
            border-radius: 10px;
            border: none;
            outline: none;
            font-size: 14px;
            box-sizing: border-box; /* Biar padding gak nambah lebar */
        }

        /* BUTTON */
        button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 10px;
            background: #007bff;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            font-size: 15px;
        }

        button:hover {
            background: #0056b3;
            transform: scale(1.02);
        }

        /* LINKS */
        .links {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
        }

        .links a {
            color: #fff;
            text-decoration: none;
        }

        .links a:hover {
            text-decoration: underline;
        }

        /* ALERT UNTUK PESAN ERROR */
        .alert {
            background: rgba(231, 74, 59, 0.8);
            color: white;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="overlay"></div>

<div class="card">

    <div class="header">
        <img src="{{ asset('admin/img/logo-asalta.png') }}" class="logo">
        <div class="title">
            <h1>SISTEM MRP</h1>
            <span>PT. ASALTA MANDIRI AGUNG</span>
        </div>
    </div>
    
    {{-- Menampilkan error jika validasi gagal --}}
    @if ($errors->any())
        <div class="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register.post') }}">
        @csrf {{-- SAKTI: Ini pelindung biar gak Error 419 Page Expired lagi --}}

        <div class="input-group">
            <input type="text" name="name" placeholder="Nama Lengkap" value="{{ old('name') }}" required>
        </div>

        <div class="input-group">
            <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
        </div>

        <div class="input-group">
            <input type="password" name="password" placeholder="Password (Min. 6 Karakter)" required>
        </div>

        <button type="submit">Daftar Sekarang</button>
    </form>

    <div class="links">
        <a href="{{ route('login') }}">Sudah punya akun? Kembali ke Login</a>
    </div>

</div>

</body>
</html>