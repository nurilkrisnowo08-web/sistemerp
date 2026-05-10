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
    
    {{-- ✨ SWEETALERT2 LIBRARY --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root { 
            --ind-navy: #0f172a; 
            --ind-blue: #4361ee; 
            --ind-bg: #f8fafc; 
            --ind-border: #e2e8f0; 
            --glass-white: rgba(255, 255, 255, 0.95);
        }
        
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--ind-bg); color: #1e293b; overflow-x: hidden; }

        /* 📱 MOBILE SIDEBAR REVOLUTION */
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
            box-shadow: 15px 0 30px rgba(0,0,0,0.2);
        }

        /* Desktop Mode Sidebar */
        @media (min-width: 768px) {
            #accordionSidebar { 
                position: sticky; 
                transform: translateX(0);
                width: 260px !important;
                box-shadow: none;
            }
            #accordionSidebar.toggled { width: 100px !important; transform: translateX(0); }
        }

        /* Mobile Active State */
        #accordionSidebar.mobile-active { transform: translateX(0); }

        .sidebar-brand-text { font-family: 'Orbitron', sans-serif; letter-spacing: 2px; font-size: 1rem; color: #fff; }
        
        /* Menu Styling */
        .nav-item .nav-link { 
            padding: 1rem 1.5rem !important; margin: 4px 15px; border-radius: 14px; 
            font-size: 0.75rem; font-weight: 700; color: rgba(255,255,255,0.55) !important; 
            display: flex; align-items: center; transition: 0.3s;
        }
        .nav-item .nav-link i { font-size: 1rem; margin-right: 12px; }
        .nav-item .nav-link:hover { color: #fff !important; background: rgba(255,255,255,0.08); }
        .nav-item.active .nav-link { background: var(--ind-blue) !important; color: #fff !important; box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3); }

        .sidebar-heading { 
            color: rgba(255,255,255,0.2) !important; font-size: 0.6rem !important; 
            font-weight: 800; letter-spacing: 2.5px; margin: 1.5rem 0 0.5rem 25px;
        }

        /* ✨ TOPBAR & HEADER */
        .topbar { background: var(--glass-white) !important; backdrop-filter: blur(12px); border-bottom: 1px solid var(--ind-border); height: 4.8rem; position: sticky; top: 0; z-index: 1000; }
        #clock-wrapper { background: var(--ind-navy); padding: 8px 16px; border-radius: 12px; border: 1.5px solid rgba(67, 97, 238, 0.4); }
        #clock { font-family: 'JetBrains Mono', monospace; color: #fff; font-weight: 700; font-size: 0.85rem; }

        @media (max-width: 576px) {
            .hide-mobile { display: none !important; }
            .topbar { height: 4.5rem; }
            .main-content-area { padding: 1rem 0.5rem !important; }
        }

        .sidebar-overlay { 
            display: none; position: fixed; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.6); 
            backdrop-filter: blur(4px); z-index: 1050; top: 0; left: 0; 
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
                <div class="sidebar-brand-icon"><i class="fas fa-microchip text-primary animate__animated animate__pulse animate__infinite"></i></div>
                <div class="sidebar-brand-text mx-2">ASALTA <span class="opacity-50" style="font-size: 0.6rem;">v2</span></div>
            </a>

            @php $role = strtolower(Auth::user()->role); @endphp

            <li class="nav-item {{ Request::is('dashboard*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}"><i class="fas fa-fw fa-gauge-high"></i><span>Dashboard Hub</span></a>
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
                    <div class="py-2 collapse-inner">
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
                    <div class="py-2 collapse-inner">
                        <a class="collapse-item" href="{{ route('po-customer.index') }}">PO Customer</a>
                        <a class="collapse-item" href="{{ route('rm.po_supplier') }}">PO Supplier</a>
                    </div>
                </div>
            </li>
            @endif

            {{-- 🗓️ 3. MANUFACTURING PLAN --}}
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

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseWeldingPPIC">
                    <i class="fas fa-fw fa-bolt-lightning"></i><span>Welding Control</span>
                </a>
                <div id="collapseWeldingPPIC" class="collapse {{ Request::is('ppic-welding*') ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="py-2 collapse-inner">
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
                    <div class="py-2 collapse-inner">
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
            <div class="text-center d-none d-md-inline"><button class="rounded-circle border-0" id="sidebarToggle"></button></div>
        </ul>

        {{-- 🏁 CONTENT AREA --}}
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top px-lg-4">
                    <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3" style="background: rgba(0,0,0,0.05);">
                        <i class="fa fa-bars text-primary"></i>
                    </button>
                    <div class="hide-mobile">
                        <h6 class="font-weight-extrabold text-dark m-0 uppercase tracking-widest" style="font-size: 0.65rem; letter-spacing: 2px;">ASALTA MANDIRI AGUNG // INDUSTRIAL CORE</h6>
                    </div>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item d-flex align-items-center">
                            <div id="clock-wrapper" class="animate__animated animate__fadeIn"><div id="clock">00:00:00</div></div>
                            <form action="{{ route('logout') }}" method="POST" class="ml-3">
                                @csrf
                                <button type="submit" class="btn btn-danger rounded-circle shadow-sm" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-power-off" style="font-size: 0.75rem;"></i>
                                </button>
                            </form>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid main-content-area">
                    <div class="mb-4 d-flex align-items-center justify-content-between px-2">
                        <h5 class="font-weight-bold text-dark mb-0 animate__animated animate__fadeInLeft">
                            <i class="far fa-calendar-check mr-2 text-primary"></i>
                            <span class="text-primary font-weight-extrabold" style="font-size: 0.9rem;">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                        </h5>
                    </div>
                    @yield('content')
                </div>
            </div>
            <footer class="bg-white py-4 border-top">
                <div class="container my-auto text-center font-weight-bold" style="font-size: 0.65rem; color: #94a3b8;">
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

        // ✨ SWEETALERT2 ANIMATION LOGIC
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'DATA BERHASIL DISIMPAN!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2500,
                showClass: {
                    popup: 'animate__animated animate__zoomIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__zoomOut'
                }
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'GAGAL!',
                text: "{{ session('error') }}",
                confirmButtonColor: '#4361ee'
            });
        @endif
    </script>
    @yield('scripts')
</body>
</html>