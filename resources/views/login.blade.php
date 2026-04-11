<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - MRP SYSTEM v2.0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --steel-blue: #4e73df;
            --glass-light: rgba(255, 255, 255, 0.05);
            --glass-dark: rgba(0, 0, 0, 0.6); /* Transparan biar gambar tembus */
        }

        /* 🖼️ FIX GAMBAR: Taruh di sini biar gak ketutup */
        html {
            background: url("{{ asset('admin/img/pt.jpg') }}") no-repeat center center fixed;
            background-size: cover;
        }

        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            font-family: 'Inter', sans-serif;
            background: rgba(0, 0, 0, 0.5); /* Overlay gelap langsung di body */
            overflow: hidden;
        }

        .main-wrapper {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        /* --- SEBELAH KIRI: BRANDING --- */
        .branding-side {
            flex: 1.2;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px;
            color: white;
            animation: slideInLeft 1s ease-out forwards;
        }

        .branding-content {
            max-width: 600px;
            background: rgba(0, 0, 0, 0.4);
            padding: 40px;
            border-radius: 24px;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: 5px solid var(--steel-blue);
        }

        .branding-side h2 {
            font-size: 38px;
            font-weight: 800;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: -1px;
        }

        .branding-side p {
            font-size: 15px;
            line-height: 1.8;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .iso-container {
            display: flex;
            gap: 15px;
        }

        .iso-item {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            font-size: 12px;
            font-weight: 700;
        }

        .iso-item i { color: #fbc02d; margin-right: 8px; }

        /* --- SEBELAH KANAN: FORM (FROSTED GLASS) --- */
        .form-side {
            flex: 0.8;
            /* ✨ KUNCINYA DI SINI: Background harus transparan biar gambar tembus */
            background: rgba(0, 0, 0, 0.3); 
            backdrop-filter: blur(20px); /* Efek kaca buram */
            -webkit-backdrop-filter: blur(20px);
            display: flex;
            justify-content: center;
            align-items: center;
            border-left: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.3);
            animation: slideInRight 1s ease-out forwards;
        }

        .login-box {
            width: 100%;
            max-width: 360px;
            padding: 20px;
            color: white;
        }

        .logo-box {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-box img { width: 80px; margin-bottom: 15px; }
        .logo-box h3 { font-size: 20px; letter-spacing: 4px; text-transform: uppercase; font-weight: 800; margin: 0; }

        .input-group { margin-bottom: 20px; }
        .input-group label {
            display: block;
            color: rgba(255, 255, 255, 0.6);
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .input-group input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 12px;
            color: white;
            font-size: 14px;
            outline: none;
            transition: 0.3s;
            box-sizing: border-box;
        }

        .input-group input:focus {
            border-color: var(--steel-blue);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 15px rgba(78, 115, 223, 0.3);
        }

        button {
            width: 100%;
            padding: 16px;
            background: var(--steel-blue);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background: #3756bd;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .footer-links {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
        }

        .footer-links a { color: rgba(255, 255, 255, 0.4); text-decoration: none; }
        .footer-links a:hover { color: white; }

        /* ANIMATIONS */
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-50px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @media (max-width: 992px) {
            .branding-side { display: none; }
            .form-side { flex: 1; border: none; }
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="branding-side">
        <div class="branding-content">
            <h2>PT ASALTA MANDIRI AGUNG</h2>
            <p>
               PT Asalta Mandiri Agung telah menjelma menjadi salah satu pilar industri manufaktur di Indonesia. Dengan hampir 1.000 karyawan yang bekerja dalam dua shift produksi, perusahaan ini tidak hanya membuktikan komitmennya terhadap produktivitas tetapi juga sebagai penyedia lapangan kerja yang signifikan. Dikenal sebagai produsen lebih dari 500 suku cadang untuk otomotif dan sepeda motor, perusahaan ini memiliki rekam jejak yang solid. Fasilitas manufaktur lengkap, mulai dari stamping logam, permesinan, hingga pengelasan, menempatkan PT Asalta Mandiri Agung di garis depan dalam memasok komponen berkualitas tinggi. Pelanggan utamanya pun bukanlah nama sembarangan, termasuk produsen mobil raksasa seperti Toyota, Honda, dan Daihatsu.
            </p>
            
            <div class="iso-container">
                <div class="iso-item"><i class="fas fa-certificate"></i> ISO 9001:2015</div>
                <div class="iso-item"><i class="fas fa-certificate"></i> ISO 14001:2015</div>
            </div>
        </div>
    </div>

    <div class="form-side">
        <div class="login-box">
            <div class="logo-box">
                <img src="{{ asset('admin/img/logo-asalta.png') }}" alt="Logo">
                <h3>MRP SYSTEM</h3>
            </div>

            @if(session('error'))
                <div style="background:rgba(231,74,59,0.2); color:#ff9999; padding:10px; border-radius:8px; font-size:12px; margin-bottom:20px; text-align:center; border:1px solid rgba(231,74,59,0.3);">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="input-group">
                    <label>Identity / Email</label>
                    <input type="email" name="email" placeholder="email@asalta.co.id" required autofocus>
                </div>

                <div class="input-group">
                    <label>Access Key</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit">Unlock System <i class="fas fa-arrow-right" style="margin-left: 8px;"></i></button>
            </form>

            <div class="footer-links">
                <a href="{{ route('register') }}">Create Account</a> | <a href="{{ route('forgot') }}">Recovery</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>