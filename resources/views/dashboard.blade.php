@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --ind-navy: #0f172a; --ind-blue: #4361ee; --ind-cyan: #4cc9f0;
        --ind-success: #10b981; --ind-danger: #f72585; --ind-warning: #f8961e;
        --ind-bg: #f1f5f9;
    }
    
    body { background-color: var(--ind-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--ind-navy); overflow-x: hidden; }

    /* ✨ NAVIGATION FIX FOR MOBILE rill */
    @media (max-width: 768px) {
        .main-terminal { padding: 10px !important; }
        .stat-value { font-size: 24px !important; }
        .flow-path { flex-direction: column !important; padding: 20px !important; gap: 20px; }
        .flow-line-container { display: none; }
        .stat-label { font-size: 8px !important; }
    }

    /* ✨ TACTICAL CARD GLASS rill */
    .tactical-card { 
        background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 24px; 
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03); transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
        position: relative; overflow: hidden; height: 100%;
    }
    .tactical-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(67, 97, 238, 0.1); border-color: var(--ind-blue); }
    
    .stat-label { font-size: 10px; font-weight: 800; text-transform: uppercase; color: #94a3b8; letter-spacing: 1.5px; }
    .stat-value { font-family: 'Orbitron', sans-serif; font-size: 32px; font-weight: 900; letter-spacing: -1px; }

    /* ✨ LASER STREAM HEADER rill */
    .laser-line { height: 2px; background: linear-gradient(90deg, transparent, var(--ind-blue), transparent); width: 100%; position: absolute; top: 0; animation: laserSweep 3s linear infinite; }
    @keyframes laserSweep { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }

    /* ✨ FLOW SYSTEM rill */
    .flow-path { background: var(--ind-navy); border-radius: 30px; padding: 30px; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between; }
    .flow-step { flex: 1; text-align: center; z-index: 2; transition: 0.3s; }
    .flow-step:hover { transform: scale(1.1); }
    .flow-pulse { width: 40px; height: 2px; background: var(--ind-blue); box-shadow: 0 0 15px var(--ind-blue); animation: pulse 2s infinite; }
    @keyframes pulse { 0% { opacity: 0.2; } 50% { opacity: 1; } 100% { opacity: 0.2; } }

    /* ✨ TICKER rill */
    .ticker-wrap { background: #fff; border-radius: 50px; border: 1px solid #fee2e2; padding: 8px 0; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
    .ticker-move { display: flex; width: max-content; animation: ticker 40s linear infinite; }
    .ticker-item { padding: 0 40px; font-weight: 800; font-size: 10px; color: var(--ind-danger); font-family: 'JetBrains Mono'; text-transform: uppercase; }
    @keyframes ticker { from { transform: translateX(0); } to { transform: translateX(-50%); } }

    .table-modern thead th { background: #f8fafc; border: none; font-size: 10px; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px; padding: 15px; }
    .table-modern td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 13px; }
</style>

<div class="container-fluid py-4 main-terminal animate__animated animate__fadeIn">
    
    {{-- 🛸 1. DYNAMIC HEADER HUB rill --}}
    <div class="tactical-card p-4 mb-4 shadow-sm position-relative overflow-hidden" style="border-radius: 24px;">
        <div class="laser-line"></div>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 font-weight-extrabold uppercase" style="letter-spacing: -1.5px;">
                    Intelligence <span class="text-primary">Command Center</span>
                </h1>
                <p class="text-muted font-weight-bold mb-0 small uppercase">
                    <i class="fas fa-microchip mr-1 text-primary"></i> SYSTEM_OPS: <span class="text-success">ONLINE</span> // L-TIME: <span id="real-clock">{{ date('H:i') }}</span> rill
                </p>
            </div>
            <div class="dropdown no-print">
                <button class="btn btn-dark rounded-pill px-4 font-weight-extrabold dropdown-toggle shadow-lg" data-toggle="dropdown" style="font-family: 'Orbitron'; font-size: 10px;">
                    QUICK_NAV
                </button>
                <div class="dropdown-menu dropdown-menu-right border-0 shadow-xl p-2" style="border-radius: 20px; min-width: 200px;">
                    <a class="dropdown-item rounded-lg py-2 font-weight-bold" href="{{ route('rm.store') }}"><i class="fas fa-layer-group mr-2"></i> RAW_MATERIAL</a>
                    <a class="dropdown-item rounded-lg py-2 font-weight-bold" href="{{ route('welding.index') }}"><i class="fas fa-fire mr-2"></i> WELDING_HUB</a>
                    <a class="dropdown-item rounded-lg py-2 font-weight-bold" href="{{ route('fg.index') }}"><i class="fas fa-box mr-2"></i> FINISHED_GOODS</a>
                </div>
            </div>
        </div>
    </div>

    {{-- 🚨 2. CRITICAL ALERT STREAM rill --}}
    @if(count($permintaanStok) > 0)
    <div class="ticker-wrap mb-4 shadow-sm">
        <div class="ticker-move">
            @foreach($permintaanStok->merge($permintaanStok) as $p)
            <div class="ticker-item">
                <i class="fas fa-radiation-alt mr-2 animate-pulse"></i> ALERT: STOCK [{{ $p->part_no }}] AT {{ $p->actual_stock }} PCS // TARGET MIN: {{ $p->min_stock_pcs }}
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 🚀 3. TACTICAL CARDS (Grid 4 Kolom) rill --}}
    <div class="row mb-4 g-3">
        <div class="col-6 col-md-3">
            <a href="{{ route('parts.index') }}" class="tactical-card p-4 text-decoration-none d-block" style="border-left: 6px solid var(--ind-blue);">
                <div class="stat-label">Total Assets</div>
                <div class="stat-value text-dark roll-number" data-target="{{ $totalParts }}">0</div>
                <div class="small font-weight-bold text-muted mt-1">REGISTERED PARTS</div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('fg.index', ['status' => 'crit']) }}" class="tactical-card p-4 text-decoration-none d-block" style="border-left: 6px solid var(--ind-danger);">
                <div class="stat-label text-danger">Crit Shortage</div>
                <div class="stat-value text-danger roll-number" data-target="{{ $critCount }}">0</div>
                <div class="small font-weight-bold text-danger mt-1 animate-pulse">ACTION REQUIRED</div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <div class="tactical-card p-4" style="border-left: 6px solid var(--ind-success);">
                <div class="stat-label text-success">Finished Today</div>
                <div class="stat-value text-success">+<span class="roll-number" data-target="{{ $todayProd }}">0</span></div>
                <div class="small font-weight-bold text-muted mt-1">UNITS LOADED</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="tactical-card p-4" style="border-left: 6px solid var(--ind-warning);">
                <div class="stat-label text-warning">Shipment</div>
                <div class="stat-value text-warning">-<span class="roll-number" data-target="{{ $todayDelv }}">0</span></div>
                <div class="small font-weight-bold text-muted mt-1">UNITS DISPATCHED</div>
            </div>
        </div>
    </div>

    {{-- 🌊 4. PO LOGISTICS FLOW rill --}}
    <div class="tactical-card p-4 mb-4 bg-dark">
        <div class="flow-path">
            <a href="{{ route('po-customer.index') }}" class="flow-step text-decoration-none">
                <i class="fas fa-file-contract fa-2x mb-3 text-primary"></i>
                <div class="stat-label text-white opacity-50">Active PO</div>
                <div class="h5 font-weight-bold text-white mb-0">{{ $totalPO }}</div>
            </a>
            <div class="flow-pulse"></div>
            <a href="{{ route('produksi.index') }}" class="flow-step text-decoration-none">
                <i class="fas fa-cogs fa-2x mb-3 text-warning"></i>
                <div class="stat-label text-white opacity-50">Production</div>
                <div class="h5 font-weight-bold text-warning mb-0">MONITOR</div>
            </a>
            <div class="flow-pulse" style="animation-delay: 1s;"></div>
            <a href="{{ route('fg.index') }}" class="flow-step text-decoration-none">
                <i class="fas fa-warehouse fa-2x mb-3 text-success"></i>
                <div class="stat-label text-white opacity-50">Stock FG</div>
                <div class="h5 font-weight-bold text-success mb-0">READY</div>
            </a>
            <div class="flow-pulse" style="animation-delay: 1.5s;"></div>
            <a href="{{ route('delivery.index') }}" class="flow-step text-decoration-none">
                <i class="fas fa-truck-loading fa-2x mb-3 text-danger"></i>
                <div class="stat-label text-white opacity-50">Pending SJ</div>
                <div class="h4 font-weight-bold text-danger mb-0">{{ $pendingDelvCount }}</div>
            </a>
        </div>
    </div>

    {{-- 📊 5. DATA ANALYTICS GRID rill --}}
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="tactical-card overflow-hidden">
                <div class="p-4 bg-danger text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold uppercase tracking-widest small"><i class="fas fa-bolt mr-2"></i> Urgent Shortage Ledger</h6>
                    <span class="badge bg-white text-danger px-3 rounded-pill">{{ count($permintaanStok) }} PARTS</span>
                </div>
                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr class="text-center"><th>Entity</th><th class="text-left">Part Identification</th><th>Actual</th></tr>
                        </thead>
                        <tbody>
                            @foreach($permintaanStok as $p)
                            <tr>
                                <td class="text-center"><span class="badge badge-light border text-primary">{{ $p->customer_code ?? 'AMK' }}</span></td>
                                <td>
                                    <div class="font-weight-bold text-dark" style="font-family: 'JetBrains Mono';">{{ $p->part_no }}</div>
                                    <div class="small text-muted font-weight-bold">{{ Str::limit($p->part_name, 30) }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="text-danger font-weight-extrabold h6 mb-0">{{ number_format($p->actual_stock) }}</div>
                                    <div class="small text-muted font-weight-bold" style="font-size: 9px;">MIN: {{ $p->min_stock_pcs }}</div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="tactical-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="font-weight-bold m-0 uppercase tracking-widest text-primary"><i class="fas fa-chart-area mr-2"></i> Inventory Analytics</h6>
                    <form action="{{ route('dashboard') }}" method="GET" id="chartFilter">
                        <select name="customer" class="btn btn-light btn-sm rounded-pill px-3 border shadow-sm font-weight-bold" onchange="this.form.submit()">
                            <option value="">-- ALL CLIENTS --</option>
                            @foreach($customersList as $cust)
                                <option value="{{ $cust }}" {{ $selectedCustomer == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div style="height: 330px;"><canvas id="mainDashboardChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- LIVE CLOCK rill ---
        setInterval(() => {
            const now = new Date();
            document.getElementById('real-clock').innerText = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0') + ':' + now.getSeconds().toString().padStart(2, '0');
        }, 1000);

        // --- NUMBER ROLLING rill ---
        const rollNumbers = document.querySelectorAll('.roll-number');
        rollNumbers.forEach(el => {
            let target = parseFloat(el.getAttribute('data-target'));
            let count = 0;
            let speed = target / 40;
            let timer = setInterval(() => {
                count += speed;
                if (count >= target) { el.innerText = Math.floor(target).toLocaleString(); clearInterval(timer); } 
                else { el.innerText = Math.floor(count).toLocaleString(); }
            }, 30);
        });

        // --- ANALYTICS CHART rill ---
        const ctx = document.getElementById('mainDashboardChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($labels) !!},
                datasets: [
                    { 
                        label: 'ACTUAL', 
                        data: {!! json_encode($actStockData) !!}, 
                        backgroundColor: '#4361ee', 
                        borderRadius: 8,
                        barThickness: 25
                    },
                    { 
                        label: 'MIN', 
                        data: {!! json_encode($minStockData) !!}, 
                        borderColor: '#f72585', 
                        borderWidth: 3, 
                        type: 'line', 
                        pointRadius: 0,
                        tension: 0.4 
                    }
                ]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { display: false } },
                scales: { 
                    x: { grid: { display: false }, ticks: { font: { family: 'JetBrains Mono', size: 9 } } },
                    y: { grid: { color: '#f1f5f9' }, beginAtZero: true }
                } 
            }
        });
    });
</script>
@endsection