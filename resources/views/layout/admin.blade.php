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
        :root { 
            --corp-navy: #0f172a; 
            --corp-blue: #1d4ed8; 
            --corp-bg: #f1f5f9; 
            --corp-border: #cbd5e1;
        }
        
        body { 
            font-family: 'Plus Jakarta Sans', Arial, sans-serif; 
            background-color: var(--corp-bg); 
            color: #0f172a; 
            font-size: 13px;
        }

        /* 🏛️ CLEAN INDUSTRIAL SIDEBAR */
        #accordionSidebar { 
            background: var(--corp-navy) !important; 
            z-index: 1050;
            width: 250px !important;
        }

        .sidebar-brand-text { 
            font-weight: 800; 
            letter-spacing: 1.5px; 
            font-size: 0.95rem; 
            color: #ffffff; 
        }
        
        .nav-item .nav-link { 
            padding: 0.75rem 1.25rem !important; 
            margin: 2px 10px; 
            border-radius: 6px; 
            font-size: 0.8rem; 
            font-weight: 700; 
            color: #94a3b8 !important; 
            display: flex; 
            align-items: center; 
            transition: background 0.15s ease, color 0.15s ease;
        }
        .nav-item .nav-link i { 
            font-size: 0.95rem; 
            margin-right: 12px; 
            width: 18px;
            text-align: center;
        }
        .nav-item .nav-link:hover { 
            color: #ffffff !important; 
            background: rgba(255, 255, 255, 0.08); 
        }
        .nav-item.active .nav-link { 
            background: var(--corp-blue) !important; 
            color: #ffffff !important; 
        }

        .sidebar-heading { 
            color: #64748b !important; 
            font-size: 0.65rem !important; 
            font-weight: 800; 
            letter-spacing: 1.8px; 
            margin: 1.2rem 0 0.3rem 20px;
        }

        /* SUBMENU */
        .collapse-inner {
            background: #1e293b !important;
            border-radius: 6px !important;
            margin: 4px 10px 8px 10px;
            padding: 6px !important;
        }
        .collapse-item {
            border-radius: 4px !important;
            padding: 7px 14px !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            color: #94a3b8 !important;
            display: flex !important;
            align-items: center;
            justify-content: space-between;
        }
        .collapse-item:hover {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
        }
        .collapse-item.active {
            background: rgba(29, 78, 216, 0.3) !important;
            color: #60a5fa !important;
        }

        /* TOPBAR */
        .topbar { 
            background: #ffffff !important; 
            border-bottom: 1px solid var(--corp-border); 
            height: 4.2rem; 
        }
        .clock-badge {
            background: #0f172a;
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        @media (max-width: 768px) {
            #accordionSidebar { 
                position: fixed; 
                height: 100vh;
                left: -250px;
                transition: left 0.3s ease;
            }
            #accordionSidebar.mobile-active { left: 0; }
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">

        {{-- SIDEBAR --}}
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center my-3" href="{{ route('dashboard') }}">
                <div class="sidebar-brand-icon"><i class="fas fa-industry text-primary"></i></div>
                <div class="sidebar-brand-text mx-2">ASALTA <span style="color:#60a5fa;">MRP</span></div>
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

            {{-- 🗓️ STAMPING CONTROL DENGAN LINK SMALL PRESS --}}
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
                        {{-- Kamar 1: Big Press --}}
                        <a class="collapse-item {{ Request::routeIs('ppic.mps.index') ? 'active' : '' }}" href="{{ route('ppic.mps.index') }}">
                            <span>MPS Big Press</span>
                            <span class="badge badge-primary px-1">BIG</span>
                        </a>
                        {{-- Kamar 2: Small Press (Langsung Terhubung) --}}
                        <a class="collapse-item {{ Request::is('*mps-small*') ? 'active' : '' }}" href="{{ url('/planning/ppic/mps-small') }}">
                            <span>MPS Small Press</span>
                            <span class="badge badge-warning px-1 text-dark">SMALL</span>
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
            <li class="nav-item {{ Request::is('delivery*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('delivery.index') }}"><i class="fas fa-fw fa-truck"></i><span>Dispatch Portal</span></a>
            </li>
            @endif

            <hr class="sidebar-divider d-none d-md-block my-3 opacity-25">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        {{-- CONTENT WRAPPER --}}
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top px-4">
                    <button id="sidebarToggleTop" class="btn btn-light rounded border d-md-none mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    
                    <div class="d-none d-sm-block">
                        <span class="font-weight-bold text-dark" style="letter-spacing: 0.5px;">PT ASALTA MANDIRI AGUNG // INDUSTRIAL CORE</span>
                    </div>

                    <ul class="navbar-nav ml-auto align-items-center">
                        <li class="nav-item mr-3">
                            <div class="clock-badge" id="clock">00:00:00</div>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm px-3 font-weight-bold" title="Log Out">
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

            <footer class="bg-white py-3 border-top mt-auto">
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
        function updateClock() { 
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID', { hour12: false }); 
        }
        setInterval(updateClock, 1000); 
        updateClock();

        $("#sidebarToggleTop").click(function() { 
            $("#accordionSidebar").toggleClass("mobile-active");
        });

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'SUKSES',
                text: "{{ session('success') }}",
                timer: 2000,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'PERHATIAN!',
                text: "{{ session('error') }}",
                confirmButtonColor: '#1d4ed8'
            });
        @endif
    </script>
    @yield('scripts')
</body>
</html>