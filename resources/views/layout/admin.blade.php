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
            --glass-white: rgba(255, 255, 255, 0.95);
        }
        
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--ind-bg); color: #1e293b; overflow-x: hidden; }

        /* ✨ SIDEBAR ULTRA-CLEAN & ANIMATED */
        #accordionSidebar { 
            background: var(--ind-navy) !important; 
            z-index: 1060;
            height: 100vh;
            position: sticky;
            top: 0;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #accordionSidebar::-webkit-scrollbar { width: 4px; }
        #accordionSidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

        .sidebar-brand-text { font-family: 'Orbitron', sans-serif; letter-spacing: 2px; font-size: 0.9rem; color: #fff; }
        
        /* Menu Animation */
        .nav-item .nav-link { 
            padding: 0.9rem 1.3rem !important; margin: 4px 12px; border-radius: 14px; 
            font-size: 0.75rem; font-weight: 700; color: rgba(255,255,255,0.6) !important; 
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); 
            display: flex; align-items: center;
        }

        .nav-item .nav-link i { font-size: 1rem; width: 1.5rem; transition: 0.3s; }

        /* Hover Effect */
        .nav-item .nav-link:hover { 
            color: #fff !important; 
            background: rgba(255,255,255,0.08); 
            transform: translateX(8px) scale(1.02);
        }

        .nav-item .nav-link:hover i { color: var(--ind-blue); transform: scale(1.2); }

        .nav-item.active .nav-link { 
            background: var(--ind-blue) !important; 
            color: #fff !important; 
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3); 
        }
        
        .sidebar-heading { 
            color: rgba(255,255,255,0.3) !important; 
            font-size: 0.65rem !important; 
            font-weight: 800; 
            letter-spacing: 3px; 
            margin-top: 1.8rem; 
            margin-left: 24px;
            text-transform: uppercase;
        }

        /* Dropdown Style */
        .collapse-inner { 
            background: rgba(255,255,255,0.03) !important; 
            border-radius: 14px !important; 
            margin: 5px 12px; padding: 0.5rem 0; 
            border: 1px solid rgba(255,255,255,0.05);
        }
        .collapse-item { 
            font-weight: 600 !important; 
            font-size: 0.7rem !important; 
            color: rgba(255,255,255,0.5) !important; 
            padding: 0.7rem 1.5rem !important; 
            border-radius: 10px; margin: 2px 8px; 
            transition: 0.3s;
        }
        .collapse-item:hover { 
            background: var(--ind-blue) !important; 
            color: #fff !important; 
            text-decoration: none; 
            padding-left: 1.8rem !important;
        }

        /* ✨ TOPBAR GLASSMORPHISM */
        .topbar { 
            background: var(--glass-white) !important; backdrop-filter: blur(15px); 
            border-bottom: 1px solid var(--ind-border); height: 4.5rem; 
            position: sticky; top: 0; z-index: 1000;
        }
        
        #clock-wrapper { 
            background: var(--ind-navy); padding: 8px 18px; border-radius: 14px; 
            border: 1.5px solid var(--ind-blue); display: flex; align-items: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        #clock { font-family: 'JetBrains Mono', monospace; color: #fff; font-weight: 800; font-size: 0.9rem; letter-spacing: 1px; }

        @media (max-width: 768px) {
            #accordionSidebar { position: fixed; height: 100vh; left: -250px; }
            #accordionSidebar.toggled { left: 0; width: 260px !important; }
            .sidebar-overlay { display: none; position: fixed; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1050; top: 0; left: 0; }
            .sidebar-overlay.active { display: block; }
        }

        .main-content-area { padding-top: 1.5rem; padding-bottom: 5rem; min-height: 80vh; }
    </style>
</head>

<body id="page-top">
    <div class="sidebar-overlay" id="overlay"></div>
    <div id="wrapper">

        {{-- 🏁 SIDEBAR --}}
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center my-4" href="{{ route('dashboard') }}">
                <div class="sidebar-brand-icon"><i class="fas fa-cube text-primary animate__animated animate__pulse animate__infinite"></i></div>
                <div class="sidebar-brand-text mx-2">ASALTA <span class="text-xs opacity-50">v2</span></div>
            </a>

            <li class="nav-item {{ Request::is('dashboard*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}"><i class="fas fa-fw fa-layer-group"></i><span>Dashboard Hub</span></a>
            </li>

            @if(in_array(Auth::user()->role, ['kepala_ppic', 'staff_ppic']))
            <div class="sidebar-heading">DATA_REGISTRY</div>
            <li class="nav-item {{ Request::is('customers*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('customers.index') }}"><i class="fas fa-fw fa-users-gear"></i><span>Customers</span></a>
            </li>
            <li class="nav-item {{ Request::is('parts*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('parts.index') }}"><i class="fas fa-fw fa-boxes-stacked"></i><span>Parts Library</span></a>
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
            @endif

            <div class="sidebar-heading">MANUFACTURING_PLAN</div>

            {{-- 🛠️ KAMAR 1: STAMPING CONTROL --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseStamping">
                    <i class="fas fa-fw fa-microchip"></i><span>Stamping Control</span>
                </a>
                <div id="collapseStamping" class="collapse {{ Request::is('ppic*') && !Request::is('ppic-welding*') ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="py-2 collapse-inner">
                        <a class="collapse-item" href="{{ route('ppic.index') }}">Intelligence Hub</a>
                        <a class="collapse-item" href="{{ route('ppic.mps.index') }}">Master Schedule (MPS)</a>
                        <a class="collapse-item" href="{{ route('ppic.quality.hub') }}">Quality Hub</a>
                    </div>
                </div>
            </li>

            {{-- ⚡ KAMAR 2: WELDING CONTROL (KAMAR BARU) --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseWeldingPPIC">
                    <i class="fas fa-fw fa-bolt-lightning"></i><span>Welding Control</span>
                </a>
                <div id="collapseWeldingPPIC" class="collapse {{ Request::is('ppic-welding*') ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="py-2 collapse-inner">
                        <a class="collapse-item text-primary font-weight-bold" href="{{ route('ppic.welding.index') }}">
                            <i class="fas fa-chart-line mr-1"></i> Welding Dashboard
                        </a>
                        <a class="collapse-item" href="{{ route('ppic.welding.mps') }}">
                            <i class="fas fa-calendar-check mr-1"></i> Welding MPS
                        </a>
                        <a class="collapse-item" href="{{ route('ppic.welding.quality') }}">
                            <i class="fas fa-microscope mr-1"></i> Quality Hub Las
                        </a>
                    </div>
                </div>
            </li>

            <div class="sidebar-heading">OPERATIONAL_TERMINAL</div>
            <li class="nav-item {{ Request::is('produksi*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('produksi.index') }}"><i class="fas fa-fw fa-desktop"></i><span>Stamping Terminal</span></a>
            </li>
            <li class="nav-item {{ Request::is('welding*') && !Request::is('welding-master*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('welding.index') }}"><i class="fas fa-fw fa-fire-burner"></i><span>Welding Terminal</span></a>
            </li>

            <div class="sidebar-heading">LOGISTICS_SYNC</div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFG">
                    <i class="fas fa-fw fa-box-open"></i><span>Finished Goods</span>
                </a>
                <div id="collapseFG" class="collapse" data-parent="#accordionSidebar">
                    <div class="py-2 collapse-inner">
                        <a class="collapse-item" href="{{ route('fg.index') }}">Live Inventory</a>
                        <a class="collapse-item" href="{{ route('fg-daily.index') }}">Mutation Logs</a>
                    </div>
                </div>
            </li>

            <hr class="sidebar-divider d-none d-md-block opacity-25">
            <div class="text-center d-none d-md-inline"><button class="rounded-circle border-0" id="sidebarToggle"></button></div>
        </ul>

        {{-- 🏁 CONTENT AREA --}}
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top px-lg-4">
                    <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3"><i class="fa fa-bars text-dark"></i></button>
                    
                    <div class="hide-mobile">
                        <h6 class="font-weight-extrabold text-dark m-0 uppercase tracking-widest" style="font-size: 0.65rem; letter-spacing: 2px;">
                            ASALTA MANDIRI AGUNG // INDUSTRIAL CORE
                        </h6>
                    </div>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item d-flex align-items-center">
                            <div class="mr-3 text-right hide-mobile">
                                <div class="text-gray-800 font-weight-bold small">{{ Auth::user()->name }}</div>
                                <div class="text-primary font-weight-bold" style="font-size: 0.6rem; text-transform: uppercase;">{{ Auth::user()->role }}</div>
                            </div>
                            <div id="clock-wrapper"><div id="clock">00:00:00</div></div>
                            
                            <form action="{{ route('logout') }}" method="POST" class="ml-3">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 font-weight-bold" style="font-size: 0.6rem;">
                                    <i class="fas fa-power-off"></i>
                                </button>
                            </form>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid main-content-area">
                    <div class="mb-4 d-md-flex align-items-center justify-content-between">
                        <h5 class="font-weight-bold text-dark mb-0">
                            <i class="far fa-calendar-check mr-2 text-primary"></i>
                            <span class="text-primary font-weight-extrabold">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                        </h5>
                    </div>
                    @yield('content')
                </div>
            </div>
            
            <footer class="sticky-footer bg-white py-4 border-top">
                <div class="container my-auto text-center font-weight-bold uppercase" style="font-size: 0.65rem; color: #94a3b8;">
                    &copy; 2026 PT. ASALTA MANDIRI AGUNG // Industrial Ecosystem Engine.
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/js/sb-admin-2.min.js"></script>

    <script>
        function updateClock() { 
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID', { hour12: false }); 
        }
        setInterval(updateClock, 1000); 
        updateClock();

        $("#sidebarToggleTop, #overlay").click(function() { 
            $("#accordionSidebar").toggleClass("toggled"); 
            $("#overlay").toggleClass("active"); 
        });
    </script>
    @yield('scripts')
</body>
</html>