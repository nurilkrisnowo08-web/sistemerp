<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>MRP System - AMK</title>
    
    <link href="{{ asset('admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link href="{{ asset('admin/css/sb-admin-2.min.css') }}" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-bg: #1e293b; /* Deep Industrial Navy */
            --accent-color: #3b82f6;
            --bg-body: #f1f5f9;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-body); 
            color: #334155;
        }

        /* ✨ COMPACT & NEAT SIDEBAR */
        #accordionSidebar {
            background-color: var(--sidebar-bg) !important;
            transition: width 0.2s ease;
        }

        .sidebar-dark .nav-item .nav-link {
            padding: 0.8rem 1rem;
            margin: 2px 8px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #94a3b8;
        }

        .sidebar-dark .nav-item .nav-link i {
            font-size: 0.9rem;
            width: 1.5rem;
            text-align: center;
            margin-right: 0.5rem;
        }

        .sidebar-dark .nav-item.active .nav-link {
            background-color: rgba(255, 255, 255, 0.08);
            color: #fff !important;
            font-weight: 700;
        }

        .sidebar-dark .nav-item.active .nav-link i {
            color: var(--accent-color);
        }

        .sidebar-dark .nav-item .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .sidebar-heading {
            font-size: 0.65rem !important;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1rem;
            color: #64748b !important;
            padding: 1.5rem 1rem 0.5rem 1.5rem !important;
        }

        /* ✨ TOPBAR CLEAN */
        .topbar {
            background: #fff !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
            height: 3.5rem;
        }

        .sys-status {
            font-size: 11px;
            font-weight: 700;
            color: #10b981;
            background: #ecfdf5;
            padding: 4px 12px;
            border-radius: 50px;
            border: 1px solid #d1fae5;
        }

        #liveClock {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            font-weight: 700;
            color: #475569;
        }

        /* Content Area Alignment */
        .container-fluid {
            padding: 1.5rem !important;
        }

        /* Footer Fix */
        .sticky-footer {
            padding: 1.5rem 0;
            background: #fff;
            border-top: 1px solid #e2e8f0;
        }

        /* Typography Fixes */
        h1, .h1, h2, .h2, h3, .h3, h4, .h4, h5, .h5, h6, .h6 {
            color: #0f172a;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">

        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
                <div class="sidebar-brand-icon"><i class="fas fa-microchip text-primary"></i></div>
                <div class="sidebar-brand-text mx-2" style="font-weight: 800; letter-spacing: 1px;">MRP AMK</div>
            </a>

            <li class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-chart-line"></i><span>Analytics</span>
                </a>
            </li>

            <div class="sidebar-heading">Registry</div>
            <li class="nav-item {{ Request::is('customers*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('customers.index') }}"><i class="fas fa-address-book"></i><span>Customers</span></a>
            </li>
            <li class="nav-item {{ Request::is('parts*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('parts.index') }}"><i class="fas fa-layer-group"></i><span>Parts Library</span></a>
            </li>
            <li class="nav-item {{ Request::is('line*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('line.index') }}"><i class="fas fa-industry"></i><span>Line Registry</span></a>
            </li>

            <div class="sidebar-heading">Commerce</div>
            <li class="nav-item {{ Request::is('po*', 'gudang/po-supplier*') ? 'active' : '' }}">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePO">
                    <i class="fas fa-file-invoice-dollar"></i><span>Orders</span>
                </a>
                <div id="collapsePO" class="collapse {{ Request::is('po*', 'gudang/po-supplier*') ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded shadow-sm mx-2">
                        <a class="collapse-item {{ Request::is('po-customer*') ? 'active' : '' }}" href="{{ route('po-customer.index') }}">PO Customer</a>
                        <a class="collapse-item {{ Request::is('gudang/po-supplier*') ? 'active' : '' }}" href="{{ route('rm.po_supplier') }}">PO Supplier</a>
                    </div>
                </div>
            </li>

            <div class="sidebar-heading">Inventory</div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFG">
                    <i class="fas fa-box"></i><span>Finished Goods</span>
                </a>
                <div id="collapseFG" class="collapse {{ Request::is('stock-fg*', 'fg-daily*', 'kontrol*') ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded shadow-sm mx-2">
                        <a class="collapse-item {{ Request::is('stock-fg') ? 'active' : '' }}" href="{{ route('fg.index') }}">Real-time Stock</a>
                        <a class="collapse-item {{ Request::is('fg-daily*') ? 'active' : '' }}" href="{{ route('fg-daily.index') }}">Log Reports</a>
                    </div>
                </div>
            </li>

            <li class="nav-item {{ Request::is('inventory-welding*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('welding.index') }}"><i class="fas fa-fire-alt"></i><span>Welding Terminal</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseRM">
                    <i class="fas fa-cubes"></i><span>Raw Materials</span>
                </a>
                <div id="collapseRM" class="collapse {{ Request::is('gudang/inbound*', 'rm-store*') ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded shadow-sm mx-2">
                        <a class="collapse-item {{ Request::is('gudang/inbound*') ? 'active' : '' }}" href="{{ route('rm.inbound') }}">Inbound</a>
                        <a class="collapse-item {{ Request::is('rm-store*') ? 'active' : '' }}" href="{{ route('rm.store') }}">Stockroom</a>
                    </div>
                </div>
            </li>

            <div class="sidebar-heading">Manufacturing</div>
            <li class="nav-item {{ Request::is('monitoring-produksi*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('produksi.index') }}"><i class="fas fa-desktop"></i><span>Live Monitoring</span></a>
            </li>
            <li class="nav-item {{ Request::is('ppic-planning*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('ppic.index') }}"><i class="fas fa-calendar-alt"></i><span>Planning</span></a>
            </li>

            <div class="sidebar-heading">Shipping</div>
            <li class="nav-item {{ Request::is('delivery*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('delivery.index') }}"><i class="fas fa-truck"></i><span>Shipping Portal</span></a>
            </li>
            
            <hr class="sidebar-divider d-none d-md-block mt-4">
            <div class="text-center d-none d-md-inline"><button class="rounded-circle border-0" id="sidebarToggle"></button></div>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top px-4">
                    <div class="d-flex align-items-center">
                        <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                            <i class="fa fa-bars"></i>
                        </button>
                        <h6 class="font-weight-bold text-dark my-auto">
                            ASALTA MANDIRI AGUNG
                        </h6>
                        <span class="sys-status ml-3 d-none d-md-inline-block">
                            <i class="fas fa-check-circle mr-1"></i> ONLINE
                        </span>
                    </div>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small font-weight-bold">{{ Auth::user()->name ?? 'Administrator' }}</span>
                                <img class="img-profile rounded-circle" src="{{ asset('admin/img/Logo-asalta.png') }}" width="30" height="30">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow border-0 animated--grow-in mt-3">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger font-weight-bold">
                                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2"></i> Log Out
                                    </button>
                                </form>
                            </div>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h5 class="mb-0 font-weight-bold text-dark text-uppercase">
                            Operational Shift: <span class="text-primary">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</span>
                        </h5>
                        <div id="liveClock">00:00:00</div>
                    </div>
                    
                    @yield('content')
                </div>
            </div>

            <footer class="sticky-footer">
                <div class="container my-auto">
                    <div class="copyright text-center font-weight-bold text-gray-500">
                        <span>PT. ASALTA MANDIRI AGUNG &copy; {{ date('Y') }}</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="{{ asset('admin/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin/js/sb-admin-2.min.js') }}"></script>
    
    <script>
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour12: false });
            document.getElementById('liveClock').innerText = timeStr;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>

    @yield('scripts')
</body>
</html>