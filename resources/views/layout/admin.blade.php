<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MRP System - AMK</title>
    
    <!-- LOCAL ASSET -->
    <link href="{{ asset('admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f1f5f9; 
        }

        #accordionSidebar {
            background-color: #1e293b !important;
        }

        .sidebar-dark .nav-item .nav-link {
            padding: 0.8rem 1rem;
            margin: 2px 8px;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #94a3b8;
        }

        .sidebar-dark .nav-item.active .nav-link {
            background: rgba(255,255,255,0.08);
            color: #fff !important;
        }

        .sidebar-dark .nav-item .nav-link:hover {
            color: #fff;
        }

        .topbar {
            background: #fff !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
    </style>
</head>

<body id="page-top">
<div id="wrapper">

<!-- 🔥 SIDEBAR (FULL TIDAK HILANG) -->
<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
        <div class="sidebar-brand-text mx-2 font-weight-bold">MRP AMK</div>
    </a>

    <li class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-chart-line"></i><span>Analytics</span>
        </a>
    </li>

    <div class="sidebar-heading">Registry</div>

    <li class="nav-item {{ Request::is('customers*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('customers.index') }}">
            <i class="fas fa-users"></i><span>Customers</span>
        </a>
    </li>

    <li class="nav-item {{ Request::is('parts*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('parts.index') }}">
            <i class="fas fa-cubes"></i><span>Parts Library</span>
        </a>
    </li>

    <li class="nav-item {{ Request::is('line*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('line.index') }}">
            <i class="fas fa-industry"></i><span>Line Registry</span>
        </a>
    </li>

    <div class="sidebar-heading">Commerce</div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePO">
            <i class="fas fa-file-invoice"></i><span>Orders</span>
        </a>

        <div id="collapsePO" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('po-customer.index') }}">PO Customer</a>
                <a class="collapse-item" href="{{ route('rm.po_supplier') }}">PO Supplier</a>
            </div>
        </div>
    </li>

    <div class="sidebar-heading">Inventory</div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFG">
            <i class="fas fa-box"></i><span>Finished Goods</span>
        </a>

        <div id="collapseFG" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('fg.index') }}">Real-time Stock</a>
                <a class="collapse-item" href="{{ route('fg-daily.index') }}">Log Reports</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('welding.index') }}">
            <i class="fas fa-fire"></i><span>Welding Terminal</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseRM">
            <i class="fas fa-cubes"></i><span>Raw Materials</span>
        </a>

        <div id="collapseRM" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('rm.inbound') }}">Inbound</a>
                <a class="collapse-item" href="{{ route('rm.store') }}">Stockroom</a>
            </div>
        </div>
    </li>

    <div class="sidebar-heading">Manufacturing</div>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('produksi.index') }}">
            <i class="fas fa-desktop"></i><span>Live Monitoring</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('ppic.index') }}">
            <i class="fas fa-calendar"></i><span>Planning</span>
        </a>
    </li>

    <div class="sidebar-heading">Shipping</div>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('delivery.index') }}">
            <i class="fas fa-truck"></i><span>Shipping Portal</span>
        </a>
    </li>

</ul>

<!-- 🔥 CONTENT -->
<div id="content-wrapper" class="d-flex flex-column">

    <div id="content">

        <!-- TOPBAR -->
        <nav class="navbar navbar-expand navbar-light topbar mb-4 px-3">

            <h6 class="font-weight-bold text-dark">
                ASALTA MANDIRI AGUNG
            </h6>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <span class="mr-3">{{ Auth::user()->name ?? 'Admin' }}</span>
                </li>
            </ul>

        </nav>

        <!-- MAIN -->
        <div class="container-fluid">

            <div class="d-flex justify-content-between mb-3">
                <h5>
                    Operational Shift:
                    <span class="text-primary">
                        {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                    </span>
                </h5>

                <div id="clock"></div>
            </div>

            @yield('content')

        </div>

    </div>

    <!-- FOOTER -->
    <footer class="bg-white text-center py-3">
        © {{ date('Y') }} PT. ASALTA MANDIRI AGUNG
    </footer>

</div>

</div>

<!-- JS -->
<script src="{{ asset('admin/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin/js/sb-admin-2.min.js') }}"></script>

<script>
function updateClock() {
    const now = new Date();
    document.getElementById('clock').innerText =
        now.toLocaleTimeString('id-ID', { hour12: false });
}
setInterval(updateClock, 1000);
updateClock();
</script>

@yield('scripts')

</body>
</html>