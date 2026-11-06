<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - ASALTA MRP SYSTEM</title>
    
    {{-- Font & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f7fe;
        }
        
        .login-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* ✨ KIRI: BACKGROUND GAMBAR PT ASALTA ✨ */
        .login-sidebar {
            flex: 1.2; 
            /* ✨ FIX: Menggunakan file pt.jpg dari folder lokal aslimu ✨ */
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(37, 99, 235, 0.7)), 
                        url('{{ asset("public/admin/img/pt.jpg") }}') no-repeat center center;
            background-size: cover;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 5rem;
            color: white;
            position: relative;
        }
        
        .login-sidebar h1 {
            font-weight: 900;
            font-size: 3.5rem;
            letter-spacing: -1px;
            margin-bottom: 1.5rem;
            line-height: 1.1;
        }
        
        .login-sidebar p {
            font-size: 1.15rem;
            font-weight: 500;
            color: #e2e8f0;
            line-height: 1.6;
            max-width: 550px;
        }

        /* ✨ KANAN: FORM LOGIN BERSIH ✨ */
        .login-main {
            flex: 1; 
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            max-width: 550px;
            padding: 3rem;
            box-shadow: -10px 0 30px rgba(0,0,0,0.05);
            z-index: 10;
        }
        
        .form-container {
            width: 100%;
            max-width: 400px;
        }
        
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 3rem;
        }
        
        .brand-logo img {
            height: 45px;
        }
        
        .brand-logo h2 {
            font-weight: 900;
            margin: 0;
            font-size: 1.8rem;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        
        .form-group label {
            font-weight: 800;
            color: #475569;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Desain Input ala Bubble/Pill */
        .form-control {
            border-radius: 12px;
            padding: 14px 18px;
            height: auto;
            font-weight: 600;
            font-size: 0.95rem;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            color: #1e293b;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
            background: #ffffff;
        }

        .form-control::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }
        
        /* Tombol Login */
        .btn-login {
            background: #2563eb;
            color: white;
            font-weight: 800;
            border-radius: 12px;
            padding: 16px;
            width: 100%;
            border: none;
            font-size: 1.05rem;
            margin-top: 1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
        }
        
        .btn-login:hover {
            background: #1d4ed8;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
        }

        .custom-control-label::before, 
        .custom-control-label::after {
            top: .15rem;
        }

        /* ✨ RESPONSIVE UNTUK HP ✨ */
        @media (max-width: 992px) {
            .login-sidebar { display: none; } 
            .login-main { max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="login-wrapper animate__animated animate__fadeIn">
        
        {{-- KIRI: BAGIAN GAMBAR DAN TEKS SISTEM --}}
        <div class="login-sidebar">
            <div class="mb-4">
                <span class="badge badge-light px-3 py-2 text-primary shadow-sm" style="border-radius: 50rem; font-weight: 800; letter-spacing: 1px;">VERSION 2.0.26</span>
            </div>
            <h1>INDUSTRIAL<br>ECOSYSTEM<br>ENGINE.</h1>
            <p>Sistem Perencanaan Kebutuhan Material (MRP) Terpadu untuk menunjang produktivitas, pemantauan inventaris, dan efisiensi lantai produksi PT Asalta Mandiri Agung.</p>
            
            <div style="position: absolute; bottom: 40px; left: 5rem; opacity: 0.5;">
                <i class="fas fa-shield-alt mr-2"></i> <span style="font-weight:600; font-size:14px;">Secure Intranet Connection</span>
            </div>
        </div>

        {{-- KANAN: BAGIAN FORM LOGIN --}}
        <div class="login-main">
            <div class="form-container">
                <div class="brand-logo">
                    {{-- Logo Asalta --}}
                    <img src="{{ asset('public/admin/img/Logo-asalta.png') }}" alt="Logo" onerror="this.outerHTML='<i class=\'fas fa-industry text-primary\' style=\'font-size: 2.5rem;\'></i>'">
                    <h2>ASALTA <span style="color:#2563eb;">MRP</span></h2>
                </div>

                <h4 style="font-weight: 800; color: #1e293b; margin-bottom: 5px;">Welcome Back!</h4>
                <p style="color: #64748b; font-weight: 600; font-size: 0.95rem; margin-bottom: 35px;">Silakan otentikasi identitas Anda.</p>

                {{-- FORM LOGIN --}}
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label>Username / Email</label>
                        <input type="text" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="Masukkan email atau NIK...">
                        @error('email')
                            <small class="text-danger font-weight-bold mt-2 d-block"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required placeholder="Masukkan kata sandi...">
                        @error('password')
                            <small class="text-danger font-weight-bold mt-2 d-block"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="form-group d-flex align-items-center mb-4">
                        <div class="custom-control custom-checkbox">
                            {{-- ✨ name="remember" adalah kunci utama agar Laravel otomatis menyimpan sesi ✨ --}}
                            <input type="checkbox" class="custom-control-input" id="customCheck" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="customCheck" style="text-transform: none; font-weight: 600; font-size: 0.9rem; letter-spacing: 0; color: #64748b; cursor: pointer;">
                                Ingat sesi login saya
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        LOGIN KE SISTEM <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>

                <div class="text-center mt-5 pt-3 border-top">
                    <small style="color: #94a3b8; font-weight: 600; font-size: 12px;">
                        &copy; 2026 PT Asalta Mandiri Agung.<br>Strictly restricted access.
                    </small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>