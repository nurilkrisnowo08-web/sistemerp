<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1.0, user-scalable=no">
    <title>MRP SYSTEM - PT ASALTA MANDIRI AGUNG</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@600;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css" rel="stylesheet">
    
    {{-- ✨ SWEETALERT2 LIBRARY --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root { 
            --ios-navy: #090d16; 
            --ios-card: rgba(18, 24, 38, 0.75);
            --ios-blue: #007aff; 
            --ios-accent: #38bdf8;
            --ios-bg: #f2f5f9; 
            --ios-border: rgba(255, 255, 255, 0.08); 
            --ios-glass: rgba(255, 255, 255, 0.85);
            --ios-spring: cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        body { 
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif; 
            background-color: var(--ios-bg); 
            color: #0f172a; 
            overflow-x: hidden; 
            -webkit-font-smoothing: antialiased;
        }

        /* 📱 iOS DYNAMIC SIDEBAR */
        #accordionSidebar { 
            background: var(--ios-navy) !important; 
            z-index: 1100;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 275px !important;
            transform: translateX(-100%); 
            transition: transform 0.45s cubic-bezier(0.32, 0.72, 0, 1);
            overflow-y: auto;
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
        }

        /* Desktop Mode Sidebar */
        @media (min-width: 768px) {
            #accordionSidebar { 
                position: sticky; 
                transform: translateX(0);
                width: 265px !important;
                box-shadow: none;
            }
            #accordionSidebar.toggled { 
                width: 95px !important; 
                transform: translateX(0); 
            }
        }

        #accordionSidebar.mobile-active { transform: translateX(0); }

        .sidebar-brand-text { 
            font-family: 'Orbitron', sans-serif; 
            letter-spacing: 2px; 
            font-size: 0.95rem; 
            color: #fff; 
        }
        
        /* 🫧 iOS BUBBLE / CAPSULE MENU */
        .nav-item .nav-link { 
            padding: 0.8rem 1.25rem !important; 
            margin: 5px 14px; 
            border-radius: 9999px; /* Gelembung Pill Capsule */
            font-size: 0.78rem; 
            font-weight: 700; 
            color: rgba(255, 255, 255, 0.65) !important; 
            display: flex; 
            align-items: center; 
            transition: all 0.3s var(--ios-spring);
            position: relative;
        }
        .nav-item .nav-link i { 
            font-size: 1rem; 
            margin-right: 12px; 
            transition: transform 0.3s var(--ios-spring);
        }
        .nav-item .nav-link:hover { 
            color: #fff !important; 
            background: rgba(255, 255, 255, 0.1); 
            transform: scale(1.02);
        }
        .nav-item.active .nav-link { 
            background: linear-gradient(135deg, #007aff 0%, #0051ba 100%) !important; 
            color: #fff !important; 
            box-shadow: 0 8px 24px rgba(0, 122, 255, 0.38); 
            transform: scale(1.03);
        }
        .nav-item.active .nav-link i {
            transform: scale(1.15);
        }

        .sidebar-heading { 
            color: rgba(255, 255, 255, 0.3) !important; 
            font-size: 0.58rem !important; 
            font-weight: 800; 
            letter-spacing: 2.2px; 
            margin: 1.4rem 0 0.4rem 25px;
        }

        /* 🫧 FROSTED GLASS SUBMENU CARD */
        .collapse-inner {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 20px !important;
            margin: 4px 14px 10px 14px;
            padding: 8px !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .collapse-item {
            border-radius: 9999px !important;
            padding: 8px 16px !important;
            margin: 3px 0 !important;
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            color: rgba(255, 255, 255, 0.75) !important;
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            transition: all 0.25s var(--ios-spring) !important;
        }
        .collapse-item:hover {
            background: rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
            transform: translateX(4px);
        }
        .collapse-item.active {
            background: rgba(56, 189, 248, 0.2) !important;
            color: #38bdf8 !important;
            border: 1px solid rgba(56, 189, 248, 0.3);
        }

        /* 🏝️ DYNAMIC ISLAND TOPBAR */
        .topbar { 
            background: var(--ios-glass) !important; 
            backdrop-filter: blur(20px); 
            -webkit-backdrop-filter: blur(20px); 
            border-bottom: 1px solid rgba(226, 232, 240, 0.8); 
            height: 4.8rem; 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
            transition: all 0.3s ease;
        }

        .dynamic-island-pill {
            background: #000000;
            padding: 6px 18px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: transform 0.3s var(--ios-spring);
        }
        .dynamic-island-pill:hover {
            transform: scale(1.04);
        }
        .pulse-dot {
            width: 7px;
            height: 7px;
            background: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 10px #22c55e;
            animation: pulse-green 2s infinite;
        }
        @keyframes pulse-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
        #clock { 
            font-family: 'JetBrains Mono', monospace; 
            color: #ffffff; 
            font-weight: 800; 
            font-size: 0.85rem; 
            letter-spacing: 0.5px;
        }

        /* 🫧 iOS Haptic Buttons */
        .btn-ios-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s var(--ios-spring);
            border: none;
        }
        .btn-ios-circle:active {
            transform: scale(0.9);
        }

        @media (max-width: 576px) {
            .hide-mobile { display: none !important; }
            .topbar { height: 4.4rem; }
            .main-content-area { padding: 1rem 0.6rem !important; }
        }

        .sidebar-overlay { 
            display: none; 
            position: fixed; 
            width: 100vw; 
            height: 100vh; 
            background: rgba(15, 23, 42, 0.5); 
            backdrop-filter: blur(8px); 
            -webkit-backdrop-filter: blur(8px); 
            z-index: 1050; 
            top: 0; 
            left: 0; 
            transition: opacity 0.3s ease;
        }
        .sidebar-overlay.active { display: block; }
    </style>
</head>

<body id="page-top">
    <div class="sidebar-overlay" id="overlay"></div>
    <div id="wrapper">

        {{-- 🏁 SIDEBAR START --}}
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center my-4" href="{{ route('dashboard') }}">
                <div class="sidebar-brand-icon">
                    <div style="background: linear-gradient(135deg, #007aff, #38bdf8); width: 38px; height: 38px; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0, 122, 255, 0.4);">
                        <i class="fas fa-microchip text-white" style="font-size: 1rem;"></i>
                    </div>
                </div>
                <div class="sidebar-brand-text mx-2 font-weight-extrabold">ASALTA <span style="font-size: 0.6rem; color: #38bdf8;">CORE</span></div>
            </a>

            @php $role = strtolower(Auth::user()->role); @endphp

            <li class="nav-item {{ Request::is('dashboard*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-fw fa-gauge-high"></i>
                    <span>Dashboard Hub</span>
                </a>
            </li>

            @if(in_array($role, ['kepala_ppic', 'staff_ppic', 'admin']))
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
                    <div class="collapse-inner">
                        <a class="collapse-item" href="{{ route('line.index') }}">Stamping Line</a>
                        <a class="collapse-item" href="{{ route('welding.master.lines') }}">Welding Line</a>
                        <a class="collapse-item" href="{{ route('welding.master.ng') }}">Defect (NG) Master</a>
                    </div>
                </div>
            </li>

            {{-- 💰 2. COMMERCE FLOW --}}
            <div class="sidebar-heading">COMMERCE_FLOW</div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePO">
                    <i class="fas fa-fw fa-file-invoice-dollar"></i><span>Order Center</span>
                </a>
                <div id="collapsePO" class="collapse" data-parent="#accordionSidebar">
                    <div class="collapse-inner">
                        <a class="collapse-item" href="{{ route('po-customer.index') }}">PO Customer</a>
                        <a class="collapse-item" href="{{ route('rm.po_supplier') }}">PO Supplier</a>
                    </div>
                </div>
            </li>
            @endif

            {{-- 🗓️ 3. MANUFACTURING PLAN (DENGAN KAMAR SMALL PRESS) --}}
            <div class="sidebar-heading">MANUFACTURING_PLAN</div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseStamping">
                    <i class="fas fa-fw fa-calendar-days"></i><span>Stamping Control</span>
                </a>
                <div id="collapseStamping" class="collapse {{ Request::is('ppic*') && !Request::is('ppic-welding*') ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="collapse-inner">
                        <a class="collapse-item {{ Request::routeIs('ppic.index') ? 'active' : '' }}" href="{{ route('ppic.index') }}">
                            <span>Intelligence Hub</span>
                        </a>
                        {{-- Kamar Big Press --}}
                        <a class="collapse-item {{ Request::routeIs('ppic.mps.index') ? 'active' : '' }}" href="{{ route('ppic.mps.index') }}">
                            <span>MPS Big Press</span>
                            <span class="badge badge-primary px-2 py-1" style="font-size: 8px; border-radius: 9999px;">BIG</span>
                        </a>
                        {{-- Kamar Baru: MPS Small Press --}}
                        <a class="collapse-item {{ Request::routeIs('ppic.mps.small') ? 'active' : '' }}" href="{{ route('ppic.mps.small') }}">
                            <span>MPS Small Press</span>
                            <span class="badge badge-warning px-2 py-1 font-weight-bold" style="font-size: 8px; border-radius: 9999px; color: #000;">SMALL</span>
                        </a>
                        <a class="collapse-item {{ Request::routeIs('ppic.quality.hub') ? 'active' : '' }}" href="{{ route('ppic.quality.hub') }}">
                            <span>Quality Hub</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseWeldingPPIC">
                    <i class="fas fa-fw fa-bolt-lightning"></i><span>Welding Control</span>
                </a>
                <div id="collapseWeldingPPIC" class="collapse {{ Request::is('ppic-welding*') ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="collapse-inner">
                        <a class="collapse-item" href="{{ route('ppic.welding.index') }}">Welding Dashboard</a>
                        <a class="collapse-item" href="{{ route('ppic.welding.mps') }}">Welding MPS</a>
                        <a class="collapse-item" href="{{ route('ppic.welding.quality') }}">Quality Hub Las</a>
                    </div>
                </div>
            </li>

            {{-- 🖥️ 4. OPERATIONAL TERMINAL --}}
            <div class="sidebar-heading">OPERATIONAL_TERMINAL</div>
            <li class="nav-item {{ Request::is('produksi*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('produksi.index') }}"><i class="fas fa-fw fa-desktop"></i><span>Stamping Terminal</span></a>
            </li>
            <li class="nav-item {{ Request::is('welding*') && !Request::is('welding-master*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('welding.index') }}"><i class="fas fa-fw fa-fire-burner"></i><span>Welding Terminal</span></a>
            </li>

            {{-- 🔄 5. INVENTORY SYNC --}}
            <div class="sidebar-heading">INVENTORY_SYNC</div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFG">
                    <i class="fas fa-fw fa-box-open"></i><span>Finished Goods</span>
                </a>
                <div id="collapseFG" class="collapse" data-parent="#accordionSidebar">
                    <div class="collapse-inner">
                        <a class="collapse-item" href="{{ route('fg.index') }}">Live Inventory</a>
                        <a class="collapse-item" href="{{ route('fg-daily.index') }}">Mutation Logs</a>
                    </div>
                </div>
            </li>
            <li class="nav-item {{ Request::is('rm*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('rm.store') }}"><i class="fas fa-fw fa-layer-group"></i><span>Raw Materials</span></a>
            </li>

            {{-- 🛡️ 6. QUALITY CONTROL --}}
            <div class="sidebar-heading">QUALITY_CONTROL</div>
            <li class="nav-item {{ Request::is('quality*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('quality.index') }}"><i class="fas fa-fw fa-shield-halved"></i><span>Quality Gate</span></a>
            </li>

            @if(in_array($role, ['kepala_ppic', 'staff_ppic', 'admin']))
            <div class="sidebar-heading">LOGISTICS</div>
            <li class="nav-item {{ Request::is('delivery*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('delivery.index') }}"><i class="fas fa-fw fa-truck-ramp-box"></i><span>Dispatch Portal</span></a>
            </li>
            @endif

            <hr class="sidebar-divider d-none d-md-block opacity-25">
            <div class="text-center d-none d-md-inline mb-4">
                <button class="rounded-circle border-0" id="sidebarToggle" style="background: rgba(255,255,255,0.1); color: #fff;"></button>
            </div>
        </ul>

        {{-- 🏁 CONTENT AREA --}}
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top px-lg-4">
                    <button id="sidebarToggleTop" class="btn btn-ios-circle mr-3" style="background: rgba(0,0,0,0.06);">
                        <i class="fa fa-bars text-dark"></i>
                    </button>
                    
                    <div class="hide-mobile">
                        <div class="d-flex align-items-center">
                            <span class="badge badge-dark px-3 py-1 font-weight-bold mr-2" style="border-radius: 9999px; font-size: 9px; letter-spacing: 1px;">AMA // OS</span>
                            <span class="font-weight-bold text-muted small tracking-wide" style="font-size: 0.72rem;">INDUSTRIAL CORE ECOSYSTEM</span>
                        </div>
                    </div>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item d-flex align-items-center">
                            {{-- 🏝️ iOS DYNAMIC ISLAND CAPSULE (CLOCK & SYSTEM PULSE) --}}
                            <div class="dynamic-island-pill">
                                <div class="pulse-dot"></div>
                                <div id="clock">00:00:00</div>
                            </div>

                            <form action="{{ route('logout') }}" method="POST" class="ml-3">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-ios-circle shadow-sm" title="Log Out">
                                    <i class="fas fa-power-off" style="font-size: 0.75rem;"></i>
                                </button>
                            </form>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid main-content-area">
                    <div class="mb-4 d-flex align-items-center justify-content-between px-2">
                        <div class="d-inline-flex align-items-center bg-white px-3 py-2 rounded-pill shadow-sm border border-light">
                            <i class="far fa-calendar-alt text-primary mr-2" style="font-size: 0.9rem;"></i>
                            <span class="text-dark font-weight-bold" style="font-size: 0.82rem;">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                        </div>
                    </div>
                    @yield('content')
                </div>
            </div>

            <footer class="bg-white py-4 border-top">
                <div class="container my-auto text-center font-weight-bold" style="font-size: 0.68rem; color: #94a3b8; letter-spacing: 0.5px;">
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

        // 📱 MOBILE DRAWER LOGIC
        $("#sidebarToggleTop, #overlay").click(function() { 
            if ($(window).width() < 768) {
                $("#accordionSidebar").toggleClass("mobile-active");
                $("#overlay").toggleClass("active");
            } else {
                $("#accordionSidebar").toggleClass("toggled");
            }
        });

        // ✨ SWEETALERT2 ANIMATION
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'BERHASIL DISIMPAN',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2300,
                customClass: {
                    popup: 'rounded-24'
                },
                showClass: { popup: 'animate__animated animate__zoomIn' },
                hideClass: { popup: 'animate__animated animate__zoomOut' }
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'PERHATIAN!',
                text: "{{ session('error') }}",
                confirmButtonColor: '#007aff',
                customClass: {
                    popup: 'rounded-24'
                }
            });
        @endif
    </script>
    @yield('scripts')
</body>
</html>