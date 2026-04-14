<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MRP System - AMK</title>

    <!-- FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- ICON -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <!-- SB ADMIN -->
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
        }

        #wrapper {
            display: flex;
        }

        #content-wrapper {
            width: 100%;
        }

        .sidebar {
            min-height: 100vh;
            background: #1e293b;
        }

        .sidebar .nav-link {
            color: #94a3b8;
        }

        .sidebar .nav-link:hover {
            color: #fff;
        }

        .topbar {
            height: 60px;
        }

        .sys-status {
            font-size: 12px;
            font-weight: bold;
            color: #10b981;
            background: #ecfdf5;
            padding: 4px 10px;
            border-radius: 20px;
        }
    </style>
</head>

<body id="page-top">

<div id="wrapper">

    <!-- 🔥 SIDEBAR -->
    <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
            <div class="sidebar-brand-text mx-2 font-weight-bold">MRP AMK</div>
        </a>

        <hr class="sidebar-divider">

        <li class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <div class="sidebar-heading">Master</div>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('customers.index') }}">
                <i class="fas fa-users"></i>
                <span>Customers</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('parts.index') }}">
                <i class="fas fa-cubes"></i>
                <span>Parts</span>
            </a>
        </li>

    </ul>

    <!-- 🔥 CONTENT -->
    <div id="content-wrapper" class="d-flex flex-column">

        <div id="content">

            <!-- 🔥 TOPBAR -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow px-3">

                <h5 class="font-weight-bold text-dark my-auto">
                    ASALTA MANDIRI AGUNG
                </h5>

                <div class="ml-3 sys-status">
                    <i class="fas fa-check-circle"></i> ONLINE
                </div>

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <span class="mr-3 font-weight-bold">
                            {{ Auth::user()->name ?? 'Admin' }}
                        </span>
                    </li>
                </ul>

            </nav>

            <!-- 🔥 MAIN -->
            <div class="container-fluid">

                <div class="d-flex justify-content-between mb-3">
                    <h5 class="font-weight-bold">
                        Operational Shift:
                        <span class="text-primary">
                            {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                        </span>
                    </h5>

                    <div id="clock" class="font-weight-bold"></div>
                </div>

                @yield('content')

            </div>

        </div>

        <!-- 🔥 FOOTER -->
        <footer class="bg-white text-center py-3">
            © {{ date('Y') }} PT. ASALTA MANDIRI AGUNG
        </footer>

    </div>

</div>

<!-- JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/js/sb-admin-2.min.js"></script>

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