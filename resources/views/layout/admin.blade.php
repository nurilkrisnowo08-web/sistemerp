<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1.0, user-scalable=no">
    <title>MRP SYSTEM - PT ASALTA MANDIRI AGUNG</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        :root {
            --ind-navy: #0f172a;
            --ind-blue: #4361ee;
            --ind-bg: #f1f5f9;
            --ind-border: #e2e8f0;
            --glass: rgba(255, 255, 255, 0.9);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--ind-bg);
            color: #1e293b;
            overflow-x: hidden;
        }

        /* ✨ MODERN SIDEBAR SULTAN rill */
        #accordionSidebar {
            background: var(--ind-navy) !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1060;
        }

        .sidebar-brand-text {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 2px;
            font-size: 0.9rem;
            background: linear-gradient(to right, #fff, var(--ind-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-item .nav-link {
            padding: 0.9rem 1.3rem !important;
            margin: 5px 15px;
            border-radius: 15px;
            font-size: 0.78rem;
            font-weight: 600;
            color: rgba(255,255,255,0.5) !important;
            transition: 0.3s;
        }

        .nav-item.active .nav-link {
            background: var(--ind-blue) !important;
            color: #fff !important;
            box-shadow: 0 12px 20px -5px rgba(67, 97, 238, 0.4);
        }

        .nav-item .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: #fff !important;
            transform: translateX(5px);
        }

        /* ✨ TOPBAR GLASSMORPHISM rill */
        .topbar {
            background: var(--glass) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--ind-border);
            height: 4.5rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        /* ✨ DIGITAL INSTRUMENT CLOCK rill */
        #clock-wrapper {
            background: var(--ind-navy);
            padding: 2px 2px 2px 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            border: 1px solid rgba(67, 97, 238, 0.3);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        #clock {
            font-family: 'JetBrains Mono', monospace;
            color: #fff;
            font-weight: 800;
            font-size: 1rem;
            letter-spacing: 1px;
        }

        .clock-label {
            background: var(--ind-blue);
            color: #fff;
            padding: 5px 10px;
            border-radius: 10px;
            margin-left: 10px;
            font-size: 0.6rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        /* 📱 MOBILE ARCHITECTURE rill */
        @media (max-width: 768px) {
            #accordionSidebar {
                position: fixed;
                height: 100vh;
                left: -250px;
                box-shadow: 20px 0 50px rgba(0,0,0,0.2);
            }
            
            #accordionSidebar.toggled {
                left: 0;
                width: 260px !important;
            }

            /* Overlay pas sidebar buka rill */
            .sidebar-overlay {
                display: none;
                position: fixed;
                width: 100vw;
                height: 100vh;
                background: rgba(15, 23, 42, 0.6);
                backdrop-filter: blur(4px);
                z-index: 1050;
                top: 0; left: 0;
            }

            .sidebar-overlay.active { display: block; }

            #content-wrapper { margin-left: 0 !important; width: 100%; }
            .main-content-area { padding: 15px !important; }
            .hide-mobile { display: none !important; }
            
            .operational-shift-text { font-size: 0.8rem !important; line-height: 1.4; }
            .topbar { padding: 0 10px !important; }
        }

        /* Decoration rill */
        .btn-circle-custom {
            width: 40px; height: 40px; border-radius: 50%;
            background: #f1f5f9; display: flex; align-items: center; justify-content: center;
            color: var(--ind-navy); transition: 0.3s;
        }
        .btn-circle-custom:hover { background: var(--ind-blue); color: #fff; }
    </style>
</head>

<body id="page-top">
    <div class="sidebar-overlay" id="overlay"></div>

    <div id="wrapper">

        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center my-4" href="#">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-microchip text-primary" style="filter: drop-shadow(0 0 10px rgba(67, 97, 238, 0.8));"></i>
                </div>
                <div class="sidebar-brand-text mx-2">ASALTA <span style="font-size: 0.6rem; opacity: 0.8;">v2.0</span></div>
            </a>

            <li class="nav-item {{ Request::is('dashboard*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-fw fa-gauge-high"></i><span>Dashboard Hub</span>
                </a>
            </li>

            <div class="sidebar-heading">DATA_REGISTRY</div>
            <li class="nav-item {{ Request::is('customers*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('customers.index') }}"><i class="fas fa-fw fa-address-card"></i><span>Customers</span></a>
            </li>
            <li class="nav-item {{ Request::is('parts*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('parts.index') }}"><i class="fas fa-fw fa-microchip"></i><span>Parts Library</span></a>
            </li>

            <div class="sidebar-heading">INVENTORY_SYNC</div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFG">
                    <i class="fas fa-fw fa-box-open"></i><span>Finished Goods</span>
                </a>
                <div id="collapseFG" class="collapse" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded-lg">
                        <a class="collapse-item font-weight-bold text-dark" href="{{ route('fg.index') }}">Live Stock</a>
                        <a class="collapse-item font-weight-bold text-dark" href="{{ route('fg-daily.index') }}">Mutation Log</a>
                    </div>
                </div>
            </li>

            <div class="sidebar-heading">MANUFACTURING</div>
            <li class="nav-item {{ Request::is('produksi*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('produksi.index') }}"><i class="fas fa-fw fa-desktop"></i><span>Live Monitor</span></a>
            </li>

            <div class="sidebar-heading">LOGISTICS</div>
            <li class="nav-item {{ Request::is('delivery*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('delivery.index') }}"><i class="fas fa-fw fa-truck-ramp-box"></i><span>Dispatch Portal</span></a>
            </li>

            <hr class="sidebar-divider d-none d-md-block opacity-25">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle" style="background: rgba(255,255,255,0.1);"></button>
            </div>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">

                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top px-4">
                    <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
                        <i class="fa fa-bars text-dark"></i>
                    </button>
                    
                    <div class="hide-mobile">
                        <h6 class="font-weight-extrabold text-dark m-0 uppercase tracking-widest" style="font-size: 0.75rem;">
                            PT ASALTA MANDIRI AGUNG <span class="text-primary ml-2">// KARAWANG PLANT rill</span>
                        </h6>
                    </div>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow d-flex align-items-center">
                            <span class="mr-3 text-gray-800 font-weight-bold small hide-mobile">
                                {{ Auth::user()->name ?? 'Administrator' }}
                            </span>
                            <div class="btn-circle-custom shadow-sm">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid main-content-area">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="brand-pills shadow-sm">
                            <h5 class="font-weight-bold text-dark mb-0 operational-shift-text">
                                <i class="far fa-calendar-check mr-2 text-primary"></i>
                                <span class="text-primary font-weight-extrabold">
                                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                                </span>
                            </h5>
                        </div>
                        
                        <div id="clock-wrapper">
                            <div id="clock">00:00:00</div>
                            <div class="clock-label">Live</div>
                        </div>
                    </div>

                    @yield('content')
                </div>
            </div>

            <footer class="footer bg-white py-4 mt-5">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span class="font-weight-bold uppercase" style="letter-spacing: 1px;">&copy; 2026 ASALTA MRP SYSTEM // INDUSTRIAL CORE rill.</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/js/sb-admin-2.min.js"></script>

    <script>
        // 🕒 Clock Logic rill
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID', { hour12: false });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // 📱 Mobile Sidebar & Overlay Logic rill
        const sidebar = $("#accordionSidebar");
        const overlay = $("#overlay");

        $("#sidebarToggleTop").click(function() {
            sidebar.toggleClass("toggled");
            overlay.toggleClass("active");
        });

        // Klik overlay buat nutup sidebar rill
        overlay.click(function() {
            sidebar.removeClass("toggled");
            $(this).removeClass("active");
        });

        // Close on escape
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                sidebar.removeClass("toggled");
                overlay.removeClass("active");
            }
        });
    </script>
    @yield('scripts')
</body>
</html>