<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1.0, user-scalable=no">
    <title>MRP SYSTEM - PT ASALTA MANDIRI AGUNG</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css" rel="stylesheet">
    
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* 🎨 THEME VARIABLES */
        :root { 
            --corp-blue: #2563eb; /* Biru modern cerah */
            
            /* Light Theme */
            --bg-body: #f8fafc;
            --bg-surface: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --sidebar-start: #0f172a; /* Dark Navy ala referensi */
            --sidebar-end: #172554;
        }

        /* 🌙 DARK MODE OVERRIDES */
        body.dark-mode {
            --bg-body: #0f172a;
            --bg-surface: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --sidebar-start: #020617;
            --sidebar-end: #0f172a;
        }

        body { 
            font-family: 'Plus Jakarta Sans', Arial, sans-serif; 
            background-color: var(--bg-body); 
            color: var(--text-main); 
            font-size: 13px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* 🔄 BOOTSTRAP OVERRIDES UNTUK DARK MODE */
        body.dark-mode .bg-white { background-color: var(--bg-surface) !important; }
        body.dark-mode .text-dark { color: var(--text-main) !important; }
        body.dark-mode .border-bottom, body.dark-mode .border-top { border-color: var(--border-color) !important; }
        body.dark-mode .card, body.dark-mode .modal-content { background-color: var(--bg-surface); border-color: var(--border-color); }
        
        /* 🏛️ CLEAN INDUSTRIAL SIDEBAR */
        #accordionSidebar { 
            background: linear-gradient(180deg, var(--sidebar-start) 0%, var(--sidebar-end) 100%) !important; 
            z-index: 1050;
            width: 260px !important;
            position: relative;
            overflow-x: hidden;
            transition: background 0.3s ease;
        }

        /* ✨ LOGO & BRAND BUBBLE STYLE ✨ */
        .sidebar-brand { padding: 1.5rem 1rem !important; margin-bottom: 0.5rem; height: auto !important; }
        .sidebar-brand-icon {
            background: rgba(255, 255, 255, 0.05); /* Efek bubble transparan di belakang logo */
            padding: 10px;
            border-radius: 16px; /* Bentuk Bubble / Squircle */
            display: flex; align-items: center; justify-content: center;
            box-shadow: inset 0 2px 4px rgba(255, 255, 255, 0.05);
        }
        .sidebar-brand-text { 
            font-weight: 800; 
            letter-spacing: 1px; 
            font-size: 1.05rem; 
            color: #ffffff; 
        }
        
        /* ✨ BUBBLE MENU STYLE (PILL SHAPE) ✨ */
        .nav-item .nav-link { 
            position: relative;
            padding: 0.85rem 1.25rem !important; 
            margin: 4px 16px; 
            border-radius: 50rem; /* Bentuk Pill / Bubble sempurna */
            font-size: 0.85rem; 
            font-weight: 700; 
            color: #94a3b8 !important; 
            display: flex; 
            align-items: center; 
            overflow: hidden;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .nav-item .nav-link i { font-size: 1.05rem; margin-right: 14px; width: 22px; text-align: center; z-index: 2; transition: color 0.3s ease; }
        .nav-item .nav-link span { z-index: 2; }

        /* Hover Bubble Highlight */
        .nav-item .nav-link:hover { 
            color: #ffffff !important; 
            background: rgba(255, 255, 255, 0.08); 
            transform: translateX(4px); 
        }
        
        /* Active Bubble / Pill */
        .nav-item.active .nav-link { 
            background: var(--corp-blue) !important; 
            color: #ffffff !important; 
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35); /* Glow biru */
        }
        .nav-item.active .nav-link i { color: #ffffff !important; }

        .sidebar-heading { 
            color: #64748b !important; font-size: 0.7rem !important; font-weight: 800; 
            letter-spacing: 2px; margin: 1.5rem 0 0.5rem 24px;
        }

        /* ✨ ANIMASI MICRO-BUBBLE DI SUBMENU ✨ */
        .collapse-inner {
            background: rgba(0, 0, 0, 0.25) !important;
            border-radius: 16px !important; margin: 0px 16px 10px 16px; padding: 8px !important;
            border: 1px solid rgba(255,255,255,0.03);
        }
        
        .collapse-item {
            position: relative; border-radius: 50rem !important; padding: 8px 16px !important;
            font-size: 0.8rem !important; font-weight: 700 !important; color: #94a3b8 !important;
            display: flex !important; align-items: center; justify-content: space-between;
            transition: all 0.3s ease; overflow: hidden;
        }
        
        .collapse-item::before {
            content: ''; position: absolute; left: 0; top: 50%; width: 6px; height: 6px;
            background: #60a5fa; border-radius: 50%; transform: translateY(-50%) scale(0);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .collapse-item:hover { background: rgba(255, 255, 255, 0.08) !important; color: #ffffff !important; padding-left: 22px !important; }
        .collapse-item:hover::before { transform: translateY(-50%) scale(1); left: 10px; }
        .collapse-item.active { background: rgba(37, 99, 235, 0.2) !important; color: #60a5fa !important; }

        /* 🔝 TOPBAR */
        .topbar { 
            background: var(--bg-surface) !important; 
            border-bottom: 1px solid var(--border-color); 
            height: 4.5rem; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            transition: background 0.3s ease, border-color 0.3s ease;
        }
        .clock-badge {
            background: var(--corp-blue); color: #ffffff; font-family: 'JetBrains Mono', monospace; font-weight: 700;
            padding: 8px 16px; border-radius: 50rem; font-size: 0.85rem; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }
        
        /* 🌓 THEME TOGGLE BUBBLE BUTTON */
        .theme-btn {
            width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;
            border-radius: 50%; border: 1px solid var(--border-color);
            background: var(--bg-body); color: var(--text-muted);
            transition: all 0.2s ease; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .theme-btn:hover { background: var(--border-color); color: var(--text-main); transform: scale(1.05); }

        @media (max-width: 768px) {
            #accordionSidebar { position: fixed; height: 100vh; left: -260px; transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
            #accordionSidebar.mobile-active { left: 0; }
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">

        {{-- SIDEBAR --}}
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
                <div class="sidebar-brand-icon">
                    {{-- 🖼️ LOGO ASALTA BERBENTUK BUBBLE --}}
                    <img src="{{ asset('public/admin/img/Logo-asalta.png') }}" alt="Logo" style="height: 28px; object-fit: contain;" onerror="this.outerHTML='<i class=\'fas fa-industry text-primary\' style=\'font-size: 1.5rem;\'></i>'">
                </div>
                <div class="sidebar-brand-text mx-3">ASALTA <span style="color:#60a5fa;">MRP</span></div>
            </a>

            @php $role = strtolower(Auth::user()->role ?? 'admin'); @endphp

            <li class="nav-item {{ Request::is('dashboard*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard Hub</span>
                </a>
            </li>

            @if(in_array($role, ['kepala_ppic', 'staff_ppic', 'admin']))
            <div class="sidebar-heading">DATA REGISTRY</div>
            <li class="nav-item {{ Request::is('customers*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('customers.index') }}"><i class="fas fa-fw fa-users"></i><span>Customers</span></a>
            </li>
            <li class="nav-item {{ Request::is('parts*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('parts.index') }}"><i class="fas fa-fw fa-cubes"></i><span>Parts Library</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMasterLine">
                    <i class="fas fa-fw fa-cogs"></i><span>Line Registry</span>
                </a>
                <div id="collapseMasterLine" class="collapse" data-parent="#accordionSidebar">
                    <div class="collapse-inner">
                        <a class="collapse-item" href="{{ route('line.index') }}">Stamping Line</a>
                        <a class="collapse-item" href="{{ route('welding.master.lines') }}">Welding Line</a>
                        <a class="collapse-item" href="{{ route('welding.master.ng') }}">Defect (NG) Master</a>
                    </div>
                </div>
            </li>

            <div class="sidebar-heading">COMMERCE FLOW</div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePO">
                    <i class="fas fa-fw fa-file-invoice"></i><span>Order Center</span>
                </a>
                <div id="collapsePO" class="collapse" data-parent="#accordionSidebar">
                    <div class="collapse-inner">
                        <a class="collapse-item" href="{{ route('po-customer.index') }}">PO Customer</a>
                        <a class="collapse-item" href="{{ route('rm.po_supplier') }}">PO Supplier</a>
                    </div>
                </div>
            </li>
            @endif

            <div class="sidebar-heading">MANUFACTURING PLAN</div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseStamping">
                    <i class="fas fa-fw fa-calendar-alt"></i><span>Stamping Control</span>
                </a>
                <div id="collapseStamping" class="collapse {{ Request::is('ppic*') && !Request::is('ppic-welding*') || Request::is('*mps*') ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="collapse-inner">
                        <a class="collapse-item {{ Request::routeIs('ppic.index') ? 'active' : '' }}" href="{{ route('ppic.index') }}">
                            <span>Intelligence Hub</span>
                        </a>
                        <a class="collapse-item {{ Request::routeIs('ppic.mps.index') ? 'active' : '' }}" href="{{ route('ppic.mps.index') }}">
                            <span>MPS Big Press</span><span class="badge badge-primary px-1 badge-pill">BIG</span>
                        </a>
                        <a class="collapse-item {{ Request::is('*mps-small*') ? 'active' : '' }}" href="{{ url('/planning/ppic/mps-small') }}">
                            <span>MPS Small Press</span><span class="badge badge-warning px-1 text-dark badge-pill">SMALL</span>
                        </a>
                        <a class="collapse-item {{ Request::routeIs('ppic.quality.hub') ? 'active' : '' }}" href="{{ route('ppic.quality.hub') }}">
                            <span>Quality Hub</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseWeldingPPIC">
                    <i class="fas fa-fw fa-burn"></i><span>Welding Control</span>
                </a>
                <div id="collapseWeldingPPIC" class="collapse {{ Request::is('ppic-welding*') ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="collapse-inner">
                        <a class="collapse-item" href="{{ route('ppic.welding.index') }}">Welding Dashboard</a>
                        <a class="collapse-item" href="{{ route('ppic.welding.mps') }}">Welding MPS</a>
                        <a class="collapse-item" href="{{ route('ppic.welding.quality') }}">Quality Hub Las</a>
                    </div>
                </div>
            </li>

            <div class="sidebar-heading">OPERATIONAL TERMINAL</div>
            <li class="nav-item {{ Request::is('produksi*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('produksi.index') }}"><i class="fas fa-fw fa-desktop"></i><span>Stamping Terminal</span></a>
            </li>
            <li class="nav-item {{ Request::is('welding*') && !Request::is('welding-master*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('welding.index') }}"><i class="fas fa-fw fa-fire"></i><span>Welding Terminal</span></a>
            </li>

            <div class="sidebar-heading">INVENTORY SYNC</div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFG">
                    <i class="fas fa-fw fa-boxes"></i><span>Finished Goods</span>
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

            <div class="sidebar-heading">QUALITY CONTROL</div>
            <li class="nav-item {{ Request::is('quality*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('quality.index') }}"><i class="fas fa-fw fa-shield-alt"></i><span>Quality Gate</span></a>
            </li>

            @if(in_array($role, ['kepala_ppic', 'staff_ppic', 'admin']))
            <div class="sidebar-heading">LOGISTICS</div>
            
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDelivery">
                    <i class="fas fa-fw fa-truck-loading"></i><span>Delivery Hub</span>
                </a>
                <div id="collapseDelivery" class="collapse {{ Request::is('delivery*') || Request::is('wos-delivery*') ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="collapse-inner">
                        <a class="collapse-item {{ Request::is('delivery*') && !Request::is('wos-delivery*') ? 'active' : '' }}" href="{{ route('delivery.index') }}">
                            <span>Dispatch Portal (PO)</span>
                        </a>
                        <a class="collapse-item {{ Request::is('wos-delivery*') ? 'active' : '' }}" href="{{ url('/wos-delivery') }}">
                            <span>Delivery WOS</span>
                        </a>
                    </div>
                </div>
            </li>
            @endif

            <hr class="sidebar-divider d-none d-md-block my-4 opacity-25">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0 bg-secondary opacity-50" id="sidebarToggle"></button>
            </div>
        </ul>

        {{-- CONTENT WRAPPER --}}
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand topbar mb-4 static-top px-4">
                    <button id="sidebarToggleTop" class="btn btn-light rounded border d-md-none mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    
                    <div class="d-none d-sm-block">
                        <span class="font-weight-bold text-muted" style="letter-spacing: 0.5px;">PT ASALTA MANDIRI AGUNG // INDUSTRIAL CORE</span>
                    </div>

                    <ul class="navbar-nav ml-auto align-items-center">
                        
                        {{-- 🌓 TOMBOL TOGGLE DARK/LIGHT MODE (BUBBLE) --}}
                        <li class="nav-item mr-3">
                            <button id="themeToggleBtn" class="theme-btn" title="Toggle Dark/Light Mode">
                                <i class="fas fa-moon" id="themeIcon"></i>
                            </button>
                        </li>

                        <li class="nav-item mr-3">
                            <div class="clock-badge" id="clock">00:00:00</div>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm px-4 font-weight-bold rounded-pill shadow-sm" title="Log Out">
                                    <i class="fas fa-power-off mr-1"></i> LOGOUT
                                </button>
                            </form>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid px-4">
                    @yield('content')
                </div>
            </div>

            <footer class="bg-white py-3 border-top mt-auto" style="transition: background 0.3s ease;">
                <div class="container my-auto text-center font-weight-bold text-muted small">
                    &copy; 2026 PT. ASALTA MANDIRI AGUNG // Industrial Ecosystem Engine.
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/js/sb-admin-2.min.js"></script>

    <script>
        // 🕰️ Live Clock
        function updateClock() { 
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID', { hour12: false }); 
        }
        setInterval(updateClock, 1000); 
        updateClock();

        // 📱 Mobile Toggle
        $("#sidebarToggleTop").click(function() { 
            $("#accordionSidebar").toggleClass("mobile-active");
        });

        // 🌓 LOGIKA DARK MODE
        const themeBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const body = document.body;

        if (localStorage.getItem('mrp_theme') === 'dark') {
            body.classList.add('dark-mode');
            themeIcon.classList.replace('fa-moon', 'fa-sun');
            themeIcon.classList.add('text-warning');
        }

        themeBtn.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('mrp_theme', 'dark');
                themeIcon.classList.replace('fa-moon', 'fa-sun');
                themeIcon.classList.add('text-warning');
            } else {
                localStorage.setItem('mrp_theme', 'light');
                themeIcon.classList.replace('fa-sun', 'fa-moon');
                themeIcon.classList.remove('text-warning');
            }
        });

        // 🔔 SweetAlert Notif
        @if(session('success'))
            Swal.fire({
                icon: 'success', title: 'SUKSES', text: "{{ session('success') }}",
                timer: 2000, showConfirmButton: false, backdrop: `rgba(0,0,123,0.1)`
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error', title: 'PERHATIAN!', text: "{{ session('error') }}",
                confirmButtonColor: '#1d4ed8', backdrop: `rgba(255,0,0,0.1)`
            });
        @endif
    </script>
    @yield('scripts')
</body>
</html>