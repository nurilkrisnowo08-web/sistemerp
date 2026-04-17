@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --primary: #4361ee; --dark: #0f172a; --bg: #f1f5f9;
    }
    body { background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; }

    /* ✨ HEADER KHUSUS (Tanpa Overflow Hidden rill!) */
    .header-command-center {
        background: #ffffff; border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        position: relative; 
        z-index: 999; /* Biar melayang di atas card bawahnya rill */
        border: 1px solid #e2e8f0;
        margin-bottom: 2rem;
    }

    /* Laser Line rill */
    .laser-line { 
        height: 3px; background: linear-gradient(90deg, transparent, var(--primary), transparent); 
        width: 100%; position: absolute; top: 0; left: 0;
        animation: laserSweep 3s linear infinite; 
        border-radius: 24px 24px 0 0;
    }
    @keyframes laserSweep { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }

    /* 🛰️ QUICK NAV BUTTON RILL */
    .btn-quick-nav {
        background: var(--dark); color: white !important;
        border-radius: 50px; padding: 12px 30px;
        font-family: 'Orbitron', sans-serif; font-size: 11px;
        font-weight: 800; letter-spacing: 1.5px;
        border: none; transition: 0.3s;
        display: flex; align-items: center; gap: 10px;
    }
    .btn-quick-nav:hover { background: var(--primary); transform: scale(1.05); box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3); }

    /* 💎 DROPDOWN FIX rill */
    .dropdown-menu-custom {
        border-radius: 20px; border: none; 
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        padding: 12px; min-width: 280px;
        margin-top: 10px !important;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
    }
    .dropdown-item-custom {
        border-radius: 12px; padding: 15px;
        font-weight: 700; color: var(--dark);
        display: flex; align-items: center; gap: 12px;
        transition: 0.2s; font-size: 13px;
    }
    .dropdown-item-custom:hover { background: var(--primary); color: white !important; transform: translateX(5px); }
    .dropdown-item-custom i { width: 20px; text-align: center; font-size: 16px; }

    /* Stats Styling rill */
    .val-tech { font-family: 'Orbitron', sans-serif; font-size: 38px; font-weight: 900; letter-spacing: -2px; }
    .label-tech { font-size: 10px; font-weight: 800; text-transform: uppercase; color: #94a3b8; letter-spacing: 2px; }
</style>

<div class="container-fluid py-4">
    
    {{-- 🛸 1. HEADER HUD CENTER (Mode Saktirill) --}}
    <div class="header-command-center p-4">
        <div class="laser-line"></div>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 font-weight-extrabold uppercase" style="letter-spacing: -1.5px;">
                    COMMAND <span class="text-primary">CENTER v2.0</span>
                </h1>
                <div class="d-flex align-items-center mt-1">
                    <span class="badge badge-primary px-3 py-1 mr-3" style="font-family: 'Orbitron'; font-size: 9px; border-radius: 6px;">
                        SYSTEM_MODE: {{ strtoupper($mode) }}
                    </span>
                    <p class="text-muted small font-weight-bold mb-0 uppercase tracking-widest" style="font-family: 'JetBrains Mono';">
                        <i class="fas fa-satellite mr-1 text-primary"></i> L-TIME: {{ date('H:i') }} // PIC: {{ auth()->user()->name ?? 'NURIL' }}
                    </p>
                </div>
            </div>
            
            <div class="dropdown">
                <button class="btn-quick-nav dropdown-toggle shadow" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-layer-group"></i> QUICK_NAV
                </button>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-custom animate__animated animate__fadeIn">
                    <h6 class="dropdown-header text-muted font-weight-bold small mb-2">SWITCH ANALYTICS MODE</h6>
                    <a class="dropdown-item-custom {{ $mode == 'summary' ? 'bg-primary text-white' : '' }}" href="{{ route('dashboard', ['mode' => 'summary']) }}">
                        <i class="fas fa-chart-bar"></i> 
                        <div>
                            <div class="d-block">Monitoring Stok</div>
                            <small class="opacity-75" style="font-weight: 400; font-size: 10px;">Inventory & Shortage Analysis</small>
                        </div>
                    </a>
                    <a class="dropdown-item-custom {{ $mode == 'delivery' ? 'bg-primary text-white' : '' }}" href="{{ route('dashboard', ['mode' => 'delivery']) }}">
                        <i class="fas fa-truck-moving"></i> 
                        <div>
                            <div class="d-block">Performance Delivery</div>
                            <small class="opacity-75" style="font-weight: 400; font-size: 10px;">Real-time Dispatch Tracking</small>
                        </div>
                    </a>
                    <div class="dropdown-divider my-2"></div>
                    <a class="dropdown-item-custom" href="{{ route('fg.index') }}">
                        <i class="fas fa-warehouse text-primary"></i> Warehouse FG Hub
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 🚀 2. TACTICAL METRICS CARDS (Tetap di atas rill) --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 24px; border-left: 6px solid var(--primary) !important;">
                <div class="label-tech">Stock_Assets</div>
                <div class="val-tech text-dark">{{ number_format($totalParts) }}</div>
                <small class="font-weight-bold text-muted uppercase">Units Registered</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 24px; border-left: 6px solid #f72585 !important;">
                <div class="label-tech text-danger">Crit_Shortage</div>
                <div class="val-tech text-danger">{{ number_format($critCount) }}</div>
                <small class="font-weight-bold text-danger uppercase">Need Attention rill</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 24px; border-left: 6px solid #4cc9f0 !important;">
                <div class="label-tech text-info">Today_Output</div>
                <div class="val-tech text-info">+{{ number_format($todayProd) }}</div>
                <small class="font-weight-bold text-muted uppercase">Finished Goods</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 24px; border-left: 6px solid #f8961e !important;">
                <div class="label-tech text-warning">Dispatched</div>
                <div class="val-tech text-warning">-{{ number_format($todayDelv) }}</div>
                <small class="font-weight-bold text-muted uppercase">Units Out Today</small>
            </div>
        </div>
    </div>

    {{-- 📦 3. DYNAMIC CONTENT AREA rill --}}
    
    @if($mode == 'delivery')
    <div class="row animate__animated animate__fadeInUp">
        {{-- ... (Kodingan Gauge Performance & Trend 30 Hari lu masuk sini rill) ... --}}
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm p-4 text-center h-100" style="border-radius: 24px;">
                <h6 class="font-weight-bold uppercase mb-4 tracking-widest text-primary">Fulfillment Rate</h6>
                <div class="gauge-wrapper">
                    <canvas id="perfGauge"></canvas>
                    <div class="gauge-text">{{ $deliveryPerformance }}%</div>
                </div>
            </div>
        </div>
        {{-- Tabel rincian customer di bawahnya rill --}}
    </div>
    @endif

    @if($mode == 'summary')
    <div class="card border-0 shadow-sm p-4 animate__animated animate__fadeInUp" style="border-radius: 24px;">
        {{-- ... (Kodingan Main Stock Chart lu masuk sini rill) ... --}}
        <h6 class="font-weight-bold mb-4 uppercase text-primary">Live Inventory Monitoring</h6>
        <div style="height: 400px;"><canvas id="mainStockChart"></canvas></div>
    </div>
    @endif

</div>
@endsection