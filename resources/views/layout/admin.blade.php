<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>MRP SYSTEM - PT ASALTA MANDIRI AGUNG</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        :root {
            --ind-navy: #0f172a;
            --ind-blue: #4361ee;
            --ind-border: #e2e8f0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }

        /* ✨ SIDEBAR SULTAN rill */
        #accordionSidebar {
            background: var(--ind-navy) !important;
            transition: 0.3s;
        }

        .sidebar-brand-text {
            letter-spacing: 2px;
            font-family: 'JetBrains Mono';
            font-size: 1rem;
        }

        .nav-item .nav-link {
            padding: 0.8rem 1.2rem !important;
            margin: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            color: rgba(255,255,255,0.6) !important;
            transition: 0.2s;
        }

        .nav-item.active .nav-link {
            background: var(--ind-blue) !important;
            color: #fff !important;
            box-shadow: 0 10px 15px -3px rgba(67, 97, 238, 0.3);
        }

        .nav-item .nav-link:hover {
            background: rgba(255,255,255,0.05);
            color: #fff !important;
        }

        .sidebar-heading {
            color: rgba(255,255,255,0.3) !important;
            font-size: 0.65rem !important;
            font-weight: 800;
            letter-spacing: 1.5px;
            margin-top: 1.5rem;
        }

        /* ✨ TOPBAR & HEADER rill */
        .topbar {
            background: #fff !important;
            border-bottom: 1px solid var(--ind-border);
            height: 4.5rem;
        }

        .brand-pills {
            background: #f1f5f9;
            padding: 8px 16px;
            border-radius: 12px;
            border: 1px solid var(--ind-border);
        }

        #clock {
            font-family: 'JetBrains Mono';
            font-weight: 700;
            background: var(--ind-navy);
            color: #fff;
            padding: 6px 15px;
            border-radius: 8px;
            letter-spacing: 1px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* 📱 MOBILE OPTIMIZATION rill */
        @media (max-width: 768px) {
            #accordionSidebar {
                position: fixed;
                z-index: 1000;
                height: 100%;
            }
            
            .sidebar-toggled #accordionSidebar {
                display: block !important;
                width: 100% !important;
            }

            .main-content-area {
                padding: 15px !important;
            }

            .operational-shift-text {
                font-size: 0.9rem !important;
            }

            .topbar {
                padding: 0 10px;
            }
            
            #sidebarToggleTop {
                display: block !important; /* Hamburger muncul rill */
                color: var(--ind-navy);
            }
        }

        .footer {
            background: #fff;
            border-top: 1px solid var(--ind-border);
            font-weight: 700;
            color: #94a3b8;
            font-size: 0.75rem;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">

        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center my-3" href="#">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-microchip text-primary"></i>
                </div>
                <div class="sidebar-brand-text mx-2">ASALTA <span class="text-primary text-xs">v2.0</span></div>
            </a>

            <li class="nav-item {{ Request::is('dashboard*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-fw fa-chart-line"></i><span>Analytics Hub</span>
                </a>
            </li>

            <div class="sidebar-heading">DATA_REGISTRY</div>
            <li class="nav-item {{ Request::is('customers*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('customers.index') }}"><i class="fas fa-fw fa-users"></i><span>Customers</span></a>
            </li>
            <li class="nav-item {{ Request::is('parts*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('parts.index') }}"><i class="fas fa-fw fa-cubes"></i><span>Parts Library</span></a>
            </li>
            <li class="nav-item {{ Request::is('line*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('line.index') }}"><i class="fas fa-fw fa-industry"></i><span>Line Registry</span></a>
            </li>

            <div class="sidebar-heading">COMMERCE_FLOW</div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePO">
                    <i class="fas fa-fw fa-file-invoice"></i><span>Order Center</span>
                </a>
                <div id="collapsePO" class="collapse">
                    <div class="bg-white py-2 collapse-inner rounded-lg shadow-lg">
                        <a class="collapse-item font-weight-bold" href="{{ route('po-customer.index') }}">PO Customer</a>
                        <a class="collapse-item font-weight-bold" href="{{ route('rm.po_supplier') }}">PO Supplier</a>
                    </div>
                </div>
            </li>

            <div class="sidebar-heading">INVENTORY_SYNC</div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFG">
                    <i class="fas fa-fw fa-box"></i><span>Finished Goods</span>
                </a>
                <div id="collapseFG" class="collapse">
                    <div class="bg-white py-2 collapse-inner rounded-lg shadow-lg">
                        <a class="collapse-item font-weight-bold" href="{{ route('fg.index') }}">Real-time Stock</a>
                        <a class="collapse-item font-weight-bold" href="{{ route('fg-daily.index') }}">Mutation Logs</a>
                    </div>
                </div>
            </li>
            <li class="nav-item {{ Request::is('welding*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('welding.index') }}"><i class="fas fa-fw fa-fire"></i><span>Welding WIP</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseRM">
                    <i class="fas fa-fw fa-layer-group"></i><span>Raw Materials</span>
                </a>
                <div id="collapseRM" class="collapse">
                    <div class="bg-white py-2 collapse-inner rounded-lg shadow-lg">
                        <a class="collapse-item font-weight-bold" href="{{ route('rm.inbound') }}">Inbound Logic</a>
                        <a class="collapse-item font-weight-bold" href="{{ route('rm.store') }}">Historical Stock</a>
                    </div>
                </div>
            </li>

            <div class="sidebar-heading">PRODUCTION</div>
            <li class="nav-item {{ Request::is('produksi*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('produksi.index') }}"><i class="fas fa-fw fa-desktop"></i><span>Live Monitor</span></a>
            </li>

            <div class="sidebar-heading">LOGISTICS</div>
            <li class="nav-item {{ Request::is('delivery*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('delivery.index') }}"><i class="fas fa-fw fa-truck-fast"></i><span>Dispatch Portal</span></a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">

                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top shadow-sm px-4">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    
                    <div class="d-none d-sm-inline-block">
                        <h6 class="font-weight-extrabold text-dark m-0 uppercase tracking-widest" style="font-size: 0.75rem;">
                            PT ASALTA MANDIRI AGUNG // KARAWANG PLANT
                        </h6>
                    </div>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item d-flex align-items-center">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 font-weight-bold">
                                {{ Auth::user()->name ?? 'System Admin' }}
                            </span>
                            <div class="topbar-divider d-none d-sm-block"></div>
                            <i class="fas fa-user-circle fa-lg text-primary"></i>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid main-content-area">
                    {{-- HEADER PAGE INFO --}}
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                        <div class="mb-2 mb-md-0">
                            <h5 class="font-weight-bold text-dark mb-0 operational-shift-text">
                                <i class="far fa-calendar-check mr-2 text-primary"></i>Operational Shift:
                                <span class="text-primary font-weight-extrabold ml-1">
                                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                                </span>
                            </h5>
                        </div>
                        <div id="clock">00:00:00</div>
                    </div>

                    @yield('content')
                </div>
            </div>

            <footer class="footer bg-white text-center py-4">
                <div class="container my-auto">
                    <div class="copyright my-auto">
                        <span>&copy; {{ date('Y') }} PT. ASALTA MANDIRI AGUNG // Industrial Core System rill.</span>
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
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID', { hour12: false });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // 📱 Auto collapse sidebar on mobile load rill
        if ($(window).width() < 768) {
            $('body').addClass('sidebar-toggled');
            $('.sidebar').addClass('toggled');
        }
    </script>
    @yield('scripts')
</body>
</html>