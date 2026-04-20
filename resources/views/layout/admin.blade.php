<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1.0, user-scalable=no">
    <title>MRP SYSTEM - PT ASALTA MANDIRI AGUNG</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        :root {
            --ind-navy: #0f172a;
            --ind-blue: #4361ee;
            --ind-bg: #f8fafc;
            --ind-border: #e2e8f0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--ind-bg);
            color: #1e293b;
            overflow-x: hidden;
        }

        /* ✨ MODERN SIDEBAR rill */
        #accordionSidebar {
            background: var(--ind-navy) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1050;
        }

        .sidebar-brand-text {
            letter-spacing: 2px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .nav-item .nav-link {
            padding: 0.8rem 1.2rem !important;
            margin: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            color: rgba(255,255,255,0.55) !important;
            transition: 0.2s;
        }

        .nav-item.active .nav-link {
            background: var(--ind-blue) !important;
            color: #fff !important;
            box-shadow: 0 10px 15px -3px rgba(67, 97, 238, 0.3);
        }

        .nav-item .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: #fff !important;
        }

        .sidebar-heading {
            color: rgba(255,255,255,0.25) !important;
            font-size: 0.6rem !important;
            font-weight: 800;
            letter-spacing: 2px;
            margin-top: 1.5rem;
        }

        /* ✨ TOPBAR & CLOCK rill */
        .topbar {
            background: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--ind-border);
            height: 4.5rem;
        }

        #clock {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            background: var(--ind-navy);
            color: #fff;
            padding: 8px 18px;
            border-radius: 10px;
            letter-spacing: 1px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: 1px solid var(--ind-blue);
        }

        /* 📱 MOBILE OPTIMIZATION (SOLUSI GAK KEPOTONG rill) */
        @media (max-width: 768px) {
            #accordionSidebar {
                position: fixed;
                height: 100vh;
                left: -250px; /* Sembunyi total rill */
            }
            
            #accordionSidebar.toggled {
                left: 0;
                width: 250px !important;
            }

            #content-wrapper {
                margin-left: 0 !important;
                width: 100vw;
            }

            .main-content-area {
                padding: 10px !important;
            }

            .operational-shift-text {
                font-size: 0.85rem !important;
            }

            .topbar {
                padding: 0 15px;
            }

            .hide-mobile {
                display: none !important;
            }
            
            #clock {
                font-size: 0.8rem;
                padding: 5px 12px;
            }
        }

        /* Glassmorphism effect for collapse items */
        .collapse-inner {
            background: #fff !important;
            border: 1px solid var(--ind-border);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .footer {
            background: #fff;
            border-top: 1px solid var(--ind-border);
            color: #94a3b8;
            font-weight: 600;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">

        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center my-4" href="#">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-microchip text-primary fa-lg"></i>
                </div>
                <div class="sidebar-brand-text mx-2">ASALTA <span class="text-primary text-xs">v2</span></div>
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

            <div class="sidebar-heading">INVENTORY_SYNC</div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFG">
                    <i class="fas fa-fw fa-box"></i><span>Finished Goods</span>
                </a>
                <div id="collapseFG" class="collapse" data-parent="#accordionSidebar">
                    <div class="py-2 collapse-inner rounded-lg">
                        <a class="collapse-item font-weight-bold" href="{{ route('fg.index') }}">Real-time Stock</a>
                        <a class="collapse-item font-weight-bold" href="{{ route('fg-daily.index') }}">Mutation Logs</a>
                    </div>
                </div>
            </li>
            <li class="nav-item {{ Request::is('welding*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('welding.index') }}"><i class="fas fa-fw fa-fire"></i><span>Welding WIP</span></a>
            </li>

            <div class="sidebar-heading">PRODUCTION_GATE</div>
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
                    <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
                        <i class="fa fa-bars text-dark"></i>
                    </button>
                    
                    <div class="hide-mobile">
                        <h6 class="font-weight-extrabold text-dark m-0 uppercase tracking-widest" style="font-size: 0.7rem; opacity: 0.8;">
                            PT ASALTA MANDIRI AGUNG // INDUSTRIAL CORE rill
                        </h6>
                    </div>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item d-flex align-items-center">
                            <span class="mr-2 d-none d-lg-inline text-gray-700 font-weight-bold small">
                                {{ Auth::user()->name ?? 'Administrator' }}
                            </span>
                            <div class="topbar-divider d-none d-sm-block"></div>
                            <i class="fas fa-user-gear fa-lg text-primary"></i>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid main-content-area">
                    {{-- HEADER PAGE INFO --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="font-weight-bold text-dark mb-0 operational-shift-text">
                                <i class="far fa-calendar-alt mr-2 text-primary"></i><span class="hide-mobile">Shift:</span>
                                <span class="text-primary font-weight-extrabold">
                                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                                </span>
                            </h5>
                        </div>
                        <div id="clock">00:00:00</div>
                    </div>

                    @yield('content')
                </div>
            </div>

            <footer class="footer py-4">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>&copy; 2026 PT. ASALTA MANDIRI AGUNG // KARAWANG PLANT rill.</span>
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

        // 📱 Logic Mobile Sidebar rill
        $("#sidebarToggleTop").click(function() {
            $("#accordionSidebar").toggleClass("toggled");
        });

        // Close sidebar when clicking outside on mobile
        $(document).click(function(event) {
            if ($(window).width() < 768) {
                var clickover = $(event.target);
                var _opened = $("#accordionSidebar").hasClass("toggled");
                if (_opened === true && !clickover.closest('#accordionSidebar').length && !clickover.closest('#sidebarToggleTop').length) {
                    $("#accordionSidebar").removeClass("toggled");
                }
            }
        });
    </script>
    @yield('scripts')
</body>
</html>