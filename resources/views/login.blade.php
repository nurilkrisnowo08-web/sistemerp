<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Terminal Access - MRP SYSTEM v2.0</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --primary: #4361ee;
            --primary-glow: rgba(67, 97, 238, 0.3);
            --dark-deep: #020617;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.08);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            height: 100vh;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--dark-deep);
            color: white;
            overflow: hidden;
            display: flex;
        }

        /* 🖼️ DYNAMIC BACKGROUND rill */
        .bg-scene {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to right, rgba(2, 6, 23, 0.9), rgba(2, 6, 23, 0.4)), 
                        url("{{ asset('admin/img/pt.jpg') }}") no-repeat center center;
            background-size: cover;
            z-index: -1;
            transform: scale(1.1);
            animation: bgZoom 20s infinite alternate ease-in-out;
        }

        @keyframes bgZoom { from { transform: scale(1); } to { transform: scale(1.1); } }

        .main-container {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        /* --- LEFT SIDE: THE POWERHOUSE rill --- */
        .intro-side {
            flex: 1.3;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 10%;
            z-index: 1;
        }

        .system-tag {
            display: inline-flex;
            align-items: center;
            background: rgba(67, 97, 238, 0.15);
            color: var(--primary);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
            margin-bottom: 25px;
            border: 1px solid rgba(67, 97, 238, 0.3);
            text-transform: uppercase;
        }

        .intro-side h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 52px;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 25px;
            letter-spacing: -2px;
            background: linear-gradient(to bottom right, #fff 50%, #64748b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .company-desc {
            max-width: 580px;
            font-size: 15px;
            line-height: 1.8;
            color: #94a3b8;
            margin-bottom: 40px;
            text-align: justify;
        }

        .iso-pills { display: flex; gap: 15px; }
        .iso-pill {
            background: var(--glass);
            border: 1px solid var(--border);
            padding: 10px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            font-size: 11px;
            font-weight: 700;
            backdrop-filter: blur(10px);
            transition: 0.3s;
        }
        .iso-pill:hover { border-color: var(--primary); transform: translateY(-3px); }
        .iso-pill i { color: #fbbf24; margin-right: 10px; font-size: 14px; }

        /* --- RIGHT SIDE: SECURE TERMINAL rill --- */
        .terminal-side {
            flex: 0.7;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
        }

        .glass-terminal {
            width: 100%;
            max-width: 420px;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-radius: 32px;
            padding: 50px 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }

        .glass-terminal::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 4px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
        }

        .terminal-header { text-align: center; margin-bottom: 45px; }
        .terminal-header img { width: 70px; filter: drop-shadow(0 0 15px var(--primary-glow)); margin-bottom: 20px; }
        .terminal-header h2 { font-family: 'Orbitron', sans-serif; font-size: 18px; letter-spacing: 5px; color: #fff; margin-bottom: 5px; }
        .terminal-header p { font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }

        .input-box { margin-bottom: 25px; position: relative; }
        .input-box i { position: absolute; left: 18px; top: 50%; transform: translateY(-5px); color: #475569; font-size: 16px; transition: 0.3s; }
        
        .input-box input {
            width: 100%;
            background: rgba(2, 6, 23, 0.5);
            border: 1px solid #1e293b;
            padding: 18px 18px 18px 50px;
            border-radius: 16px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            outline: none;
            transition: 0.3s;
        }

        .input-box input:focus {
            border-color: var(--primary);
            background: rgba(67, 97, 238, 0.05);
            box-shadow: 0 0 20px var(--primary-glow);
        }
        .input-box input:focus + i { color: var(--primary); }

        .btn-unlock {
            width: 100%;
            padding: 18px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 20px -5px var(--primary-glow);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-unlock:hover {
            background: #3756bd;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 30px var(--primary-glow);
        }

        .terminal-footer {
            margin-top: 35px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }

        .terminal-footer a { color: #fff; text-decoration: none; font-weight: 700; transition: 0.3s; }
        .terminal-footer a:hover { color: var(--primary); }

        /* MOBILE OPTIMIZATION rill */
        @media (max-width: 1100px) {
            .intro-side { display: none; }
            .terminal-side { flex: 1; }
        }
    </style>
</head>
<body>

    <div class="bg-scene"></div>

    <div class="main-container">
        <div class="intro-side animate__animated animate__fadeInLeft">
            <div class="system-tag">Intelligence Analytics v2.0</div>
            <h1>PT ASALTA<br>MANDIRI AGUNG</h1>
            <p class="company-desc">
                Pilar utama manufaktur Indonesia dengan dedikasi tinggi pada presisi. Memasok komponen otomotif berkualitas tinggi untuk Toyota, Honda, dan Daihatsu dengan teknologi Stamping, Machining, dan Welding mutakhir.
            </p>
            
            <div class="iso-pills">
                <div class="iso-pill"><i class="fas fa-shield-halved"></i> ISO 9001:2015</div>
                <div class="iso-pill"><i class="fas fa-leaf"></i> ISO 14001:2015</div>
            </div>
        </div>

        <div class="terminal-side">
            <div class="glass-terminal animate__animated animate__zoomIn">
                <div class="terminal-header">
                    <h2>MRP ACCESS</h2>
                    <p>Secure Terminal Entry</p>
                </div>

                @if(session('error'))
                    <div class="animate__animated animate__shakeX" style="background:rgba(244,63,94,0.1); color:#fb7185; padding:15px; border-radius:12px; font-size:12px; margin-bottom:25px; text-align:center; border:1px solid rgba(244,63,94,0.2); font-weight: 700;">
                        <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="input-box">
                        <i class="fas fa-id-card"></i>
                        <input type="email" name="email" placeholder="IDENTITY EMAIL" required autofocus>
                    </div>

                    <div class="input-box">
                        <i class="fas fa-key"></i>
                        <input type="password" name="password" placeholder="ACCESS KEY" required>
                    </div>

                    <button type="submit" class="btn-unlock">
                        Unlock System <i class="fas fa-shield-check"></i>
                    </button>
                </form>

                <div class="terminal-footer">
                    <a href="{{ route('register') }}">Create Account</a> 
                    <span style="margin: 0 10px; opacity: 0.3;">|</span> 
                    <a href="{{ route('forgot') }}">System Recovery</a>
                    <p style="margin-top: 25px; opacity: 0.5;">&copy; 2026 ASALTA. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>