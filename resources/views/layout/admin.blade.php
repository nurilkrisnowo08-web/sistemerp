<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1.0, user-scalable=no">
    <title>MRP SYSTEM - PT ASALTA MANDIRI AGUNG</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        :root { 
            --ind-navy: #0f172a; 
            --ind-blue: #4361ee; 
            --ind-bg: #f8fafc; 
            --ind-border: #e2e8f0; 
            --glass-white: rgba(255, 255, 255, 0.9);
        }
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--ind-bg); 
            color: #1e293b; 
            overflow-x: hidden; 
        }

        /* 📱 MOBILE NAVIGATION ENGINE (OFF-CANVAS) */
        #accordionSidebar { 
            background: var(--ind-navy) !important; 
            z-index: 1100;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 280px !important;
            transform: translateX(-100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            box-shadow: 15px 0 30px rgba(0,0,0,0.1);
        }

        /* Desktop Mode Sidebar */
        @media (min-width: 768px) {
            #accordionSidebar { 
                position: sticky; 
                transform: translateX(0);
                width: 260px !important;
                box-shadow: none;
            }
            #accordionSidebar.toggled {
                width: 100px !important;
                transform: translateX(0);
            }
        }

        /* Mobile Active State */
        #accordionSidebar.mobile-active {
            transform: translateX(0);
        }

        .sidebar-brand-text { font-family: 'Orbitron', sans-serif; letter-spacing: 2px; font-size: 1rem; color: #fff; }
        
        /* Menu Interaction */
        .nav-item .nav-link { 
            padding: 1rem 1.5rem !important; 
            margin: 4px 15px; 
            border-radius: 14px; 
            font-size: 0.8rem; 
            font-weight: 700; 
            color: rgba(255,255,255,0.6) !important; 
            display: flex; 
            align-items: center;
        }
        
        .nav-item.active .nav-link { 
            background: var(--ind-blue) !important; 
            color: #fff !important; 
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3); 
        }

        /* ✨ MODERN TOPBAR & GLASS EFFECT */
        .topbar { 
            background: var(--glass-white) !important; 
            backdrop-filter: blur(12px); 
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--ind-border); 
            height: 5rem; 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
        }

        #clock-wrapper { 
            background: var(--ind-navy); 
            padding: 8px 18px; 
            border-radius: 12px; 
            border: 2px solid rgba(67, 97, 238, 0.4);
        }

        #clock { font-family: 'JetBrains Mono', monospace; color: #fff; font-weight: 700; font-size: 0.9rem; }

        /* Mobile Adjustments */
        @media (max-width: 576px) {
            .hide-mobile { display: none !important; }
            .topbar { height: 4.5rem; padding: 0 1rem !important; }
            #clock-wrapper { padding: 6px 12px; }
            #clock { font-size: 0.75rem; }
            .main-content-area { padding: 1rem 0.5rem !important; }
        }

        /* Overlay Background */
        .sidebar-overlay { 
            display: none; 
            position: fixed; 
            width: 100vw; 
            height: 100vh; 
            background: rgba(15, 23, 42, 0.5); 
            backdrop-filter: blur(4px); 
            z-index: 1050; 
            top: 0; 
            left: 0; 
            opacity: 0;
            transition: opacity 0.3s;
        }
        .sidebar-overlay.active { display: block; opacity: 1; }

        /* Dropdown Modern Style */
        .collapse-inner { 
            background: rgba(255,255,255,0.05) !important; 
            border-radius: 15px !important; 
            margin: 5px 15px; 
            border: 1px solid rgba(255,255,255,0.1); 
        }
    </style>
</head>

<body id="page-top">
    <!-- Overlay untuk klik di luar sidebar pada HP -->
    <div class="sidebar-overlay" id="overlay"></div>
    
    <div id="wrapper">

        {{-- 🏁 SIDEBAR --}}
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center my-4" href="{{ route('dashboard') }}">
                <div class="sidebar-brand-icon"><i class="fas fa-microchip text-primary animate__animated animate__rotateIn"></i></div>
                <div class="sidebar-brand-text mx-2">ASALTA <span class="badge badge-primary px-2" style="font-size: 0.5rem;">V2</span></div>
            </a>

            <li class="nav-item {{ Request::is('dashboard*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}"><i class="fas fa-fw fa-gauge-high"></i><span>Dashboard Hub</span></a>
            </li>

            {{-- 📦 1. DATA REGISTRY --}}
            <div class="sidebar-heading">DATA_REGISTRY</div>
            <li class="nav-item {{ Request::is('customers*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('customers.index') }}"><i class="fas fa-fw fa-users"></i><span>Customers</span></a>
            </li>
            <li class="nav-item {{ Request::is('parts*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('parts.index') }}"><i class="fas fa-fw fa-cubes"></i><span>Parts Library</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMasterLine">
                    <i class="fas fa-fw fa-industry"></i><span>Line Registry</span>
                </a>
                <div id="collapseMasterLine" class="collapse" data-parent="#accordionSidebar">
                    <div class="py-2 collapse-inner">
                        <a class="collapse-item" href="{{ route('line.index') }}">Stamping Line</a>
                        <a class="collapse-item" href="{{ route('welding.master.lines') }}">Welding Line</a>
                        <a class="collapse-item" href="{{ route('welding.master.ng') }}">Defect (NG) Master</a>
                    </div>
                </div>
            </li>

            {{-- 🗓️ 2. MANUFACTURING PLAN --}}
            <div class="sidebar-heading">MANUFACTURING_PLAN</div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseStamping">
                    <i class="fas fa-fw fa-calendar-days"></i><span>Stamping Control</span>
                </a>
                <div id="collapseStamping" class="collapse {{ Request::is('ppic*') && !Request::is('ppic-welding*') ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="py-2 collapse-inner">
                        <a class="collapse-item" href="{{ route('ppic.index') }}">Intelligence Hub</a>
                        <a class="collapse-item" href="{{ route('ppic.mps.index') }}">Master Schedule (MPS)</a>
                        <a class="collapse-item" href="{{ route('ppic.quality.hub') }}">Quality Hub</a>
                    </div>
                </div>
            </li>

            {{-- ... (lanjutkan semua menu Bapak di bawah sini sesuai kodingan lama) ... --}}
            
            <!-- Contoh tambahan menu Terminal agar terlihat di HP -->
            <div class="sidebar-heading">OPERATIONAL</div>
            <li class="nav-item {{ Request::is('produksi*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('produksi.index') }}"><i class="fas fa-fw fa-desktop"></i><span>Stamping Terminal</span></a>
            </li>
            <li class="nav-item {{ Request::is('welding*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('welding.index') }}"><i class="fas fa-fw fa-fire-burner"></i><span>Welding Terminal</span></a>
            </li>

            <hr class="sidebar-divider d-none d-md-block opacity-25">
            <div class="text-center d-none d-md-inline"><button class="rounded-circle border-0" id="sidebarToggle"></button></div>
        </ul>

        {{-- 🏁 CONTENT AREA --}}
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top px-lg-4">
                    <!-- Hamburger button yang diperbesar untuk HP -->
                    <button id="sidebarToggleTop" class="btn btn-link rounded-pill mr-3" style="font-size: 1.2rem; background: rgba(0,0,0,0.05);">
                        <i class="fa fa-bars text-primary"></i>
                    </button>
                    
                    <div class="hide-mobile">
                        <h6 class="font-weight-extrabold text-dark m-0" style="font-size: 0.65rem; letter-spacing: 2px;">ASALTA MANDIRI AGUNG // INDUSTRIAL CORE</h6>
                    </div>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item d-flex align-items-center">
                            <div id="clock-wrapper" class="animate__animated animate__fadeIn">
                                <div id="clock">00:00:00</div>
                            </div>
                            <!-- Logout Button (Icon Power saja di HP) -->
                            <form action="{{ route('logout') }}" method="POST" class="ml-2">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger rounded-circle shadow-sm" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-power-off" style="font-size: 0.7rem;"></i>
                                </button>
                            </form>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid main-content-area px-3 px-md-4">
                    <!-- Tanggal Adaptif -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold text-dark mb-0 animate__animated animate__fadeInDown">
                            <i class="far fa-calendar-check mr-2 text-primary"></i>
                            <span class="text-primary font-weight-extrabold">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                        </h6>
                    </div>
                    @yield('content')
                </div>
            </div>
            
            <footer class="bg-white py-4 border-top">
                <div class="container my-auto text-center font-weight-bold" style="font-size: 0.6rem; color: #94a3b8; letter-spacing: 1px;">
                    &copy; 2026 PT. ASALTA MANDIRI AGUNG // INDUSTRIAL ECOSYSTEM
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/js/sb-admin-2.min.js"></script>

    <script>
        // Clock Engine
        function updateClock() { 
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID', { hour12: false }); 
        }
        setInterval(updateClock, 1000); 
        updateClock();

        // 📱 MOBILE DRAWER LOGIC
        $("#sidebarToggleTop").click(function(e) {
            e.preventDefault();
            if ($(window).width() < 768) {
                $("#accordionSidebar").toggleClass("mobile-active");
                $("#overlay").toggleClass("active");
            } else {
                $("#accordionSidebar").toggleClass("toggled");
            }
        });

        // Click outside to close
        $("#overlay").click(function() {
            $("#accordionSidebar").removeClass("mobile-active");
            $(this).removeClass("active");
        });

        // Auto-close on menu click (Mobile)
        $(".nav-link").click(function() {
            if ($(window).width() < 768 && !$(this).hasClass('collapsed')) {
                $("#accordionSidebar").removeClass("mobile-active");
                $("#overlay").removeClass("active");
            }
        });
    </script>
    @yield('scripts')
</body>
</html>