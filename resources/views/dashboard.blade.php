@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --ind-navy: #0f172a; --ind-blue: #4361ee; --ind-cyan: #4cc9f0;
        --ind-danger: #f72585; --ind-success: #10b981; --ind-warning: #f8961e;
        --ind-bg: #f1f5f9;
    }
    
    body { background-color: var(--ind-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--ind-navy); overflow-x: hidden; }

    /* ✨ RESPONSIVE ARCHITECTURE rill */
    @media (max-width: 768px) {
        .stat-value { font-size: 22px !important; }
        .flow-path { flex-direction: column !important; padding: 20px !important; gap: 15px; }
        .flow-pulse { display: none; }
        .main-terminal { padding: 10px !important; }
        .hide-mobile { display: none !important; }
    }

    /* ✨ TACTICAL CARD GLASS rill */
    .tactical-card { 
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; 
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03); transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
        position: relative; overflow: hidden; height: 100%;
    }
    .tactical-card:hover { transform: translateY(-8px); border-color: var(--ind-blue); box-shadow: 0 20px 40px rgba(67, 97, 238, 0.1); }
    
    .stat-label { font-size: 10px; font-weight: 800; text-transform: uppercase; color: #94a3b8; letter-spacing: 1.5px; }
    .stat-value { font-family: 'Orbitron', sans-serif; font-size: 30px; font-weight: 900; letter-spacing: -1px; }

    /* ✨ LASER STREAM rill */
    .laser-line { height: 2px; background: linear-gradient(90deg, transparent, var(--ind-blue), transparent); width: 100%; position: absolute; top: 0; animation: laserSweep 3s linear infinite; }
    @keyframes laserSweep { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }

    /* ✨ FLOW SYSTEM rill */
    .flow-path { background: var(--ind-navy); border-radius: 30px; padding: 30px; display: flex; align-items: center; justify-content: space-between; position: relative; }
    .flow-step { flex: 1; text-align: center; z-index: 2; transition: 0.3s; color: #fff !important; text-decoration: none !important; }
    .flow-step:hover { transform: scale(1.1); }
    .flow-pulse { width: 40px; height: 2px; background: var(--ind-blue); box-shadow: 0 0 15px var(--ind-blue); animation: pulse 2s infinite; opacity: 0.4; }
    @keyframes pulse { 0% { opacity: 0.2; } 50% { opacity: 1; } 100% { opacity: 0.2; } }

    /* ✨ TICKER rill */
    .ticker-wrap { background: #fff; border-radius: 50px; border: 1px solid #fee2e2; padding: 10px 0; overflow: hidden; }
    .ticker-move { display: flex; width: max-content; animation: ticker 45s linear infinite; }
    .ticker-item { padding: 0 40px; font-weight: 800; font-size: 11px; color: var(--ind-danger); font-family: 'JetBrains Mono'; text-transform: uppercase; }
    @keyframes ticker { from { transform: translateX(0); } to { transform: translateX(-50%); } }

    .table-modern thead th { background: #f8fafc; border: none; font-size: 10px; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px; padding: 15px; }
    .table-modern td { padding: 12px 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 13px; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
    {{-- 🛸 1. HEADER CENTER rill --}}
    <div class="tactical-card p-4 mb-4 shadow-sm">
        <div class="laser-line"></div>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 font-weight-extrabold uppercase" style="letter-spacing: -1.5px;">
                    Intelligence <span class="text-primary">Command Center</span>
                </h1>
                <p class="text-muted small font-weight-bold mb-0">
                    <i class="fas fa-microchip mr-1 text-primary"></i> 
                    MODE: <span class="badge badge-primary px-3">{{ strtoupper($mode ?? 'Summary') }}</span> 
                    // L-TIME: <span id="real-clock">{{ date('H:i') }}</span> rill
                </p>
            </div>
            <div class="dropdown">
                <button class="btn btn-dark rounded-pill px-4 font-weight-bold dropdown-toggle" data-toggle="dropdown">QUICK_NAV</button>
                <div class="dropdown-menu dropdown-menu-right border-0 shadow-xl p-2" style="border-radius: 20px;">
                    <a class="dropdown-item py-2 font-weight-bold" href="{{ route('dashboard', ['mode' => 'summary']) }}">MONITOR_STOK</a>
                    <a class="dropdown-item py-2 font-weight-bold" href="{{ route('dashboard', ['mode' => 'delivery']) }}">PERF_DELIVERY</a>
                </div>
            </div>
        </div>
    </div>

    {{-- 🚨 2. CRITICAL TICKER (FIX ERROR rill) --}}
    @if(isset($permintaanStok) && count($permintaanStok) > 0)
    <div class="ticker-wrap mb-4 shadow-sm">
        <div class="ticker-move">
            @foreach($permintaanStok->merge($permintaanStok) as $p)
            <div class="ticker-item">
                <i class="fas fa-bolt mr-2"></i> CRITICAL: [{{ $p->part_no }}] IS BELOW MINIMUM (ACTUAL: {{ $p->actual_stock }}) // ACTION_REQUIRED
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 🚀 3. TACTICAL CARDS (Grid 2 Kolom di HP rill!) --}}
    <div class="row mb-4 g-3">
        <div class="col-6 col-md-3 mb-3">
            <div class="tactical-card p-4 text-center" style="border-bottom: 5px solid var(--ind-blue);">
                <div class="stat-label">Assets</div>
                <div class="stat-value text-dark roll-number" data-target="{{ $totalParts ?? 0 }}">0</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="tactical-card p-4 text-center" style="border-bottom: 5px solid var(--ind-danger);">
                <div class="stat-label text-danger">Shortage</div>
                <div class="stat-value text-danger roll-number" data-target="{{ $critCount ?? 0 }}">0</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="tactical-card p-4 text-center" style="border-bottom: 5px solid var(--ind-success);">
                <div class="stat-label text-success">Finished</div>
                <div class="stat-value text-success">+<span class="roll-number" data-target="{{ $todayProd ?? 0 }}">0</span></div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="tactical-card p-4 text-center" style="border-bottom: 5px solid var(--ind-warning);">
                <div class="stat-label text-warning">Shipment</div>
                <div class="stat-value text-warning">-<span class="roll-number" data-target="{{ $todayDelv ?? 0 }}">0</span></div>
            </div>
        </div>
    </div>

    {{-- 🌊 4. PO LOGISTICS FLOW (Responsive rill) --}}
    <div class="tactical-card p-4 mb-4 bg-dark">
        <div class="flow-path">
            <a href="{{ route('po-customer.index') }}" class="flow-step">
                <i class="fas fa-file-contract fa-2x mb-2 text-primary"></i>
                <div class="small font-weight-bold opacity-50">Active PO</div>
                <div class="h5 font-weight-bold">{{ $totalPO ?? 0 }}</div>
            </a>
            <div class="flow-pulse"></div>
            <a href="{{ route('produksi.index') }}" class="flow-step">
                <i class="fas fa-cogs fa-2x mb-2 text-warning"></i>
                <div class="small font-weight-bold opacity-50">Production</div>
                <div class="h5 font-weight-bold">MONITOR</div>
            </a>
            <div class="flow-pulse"></div>
            <a href="{{ route('fg.index') }}" class="flow-step">
                <i class="fas fa-warehouse fa-2x mb-2 text-success"></i>
                <div class="small font-weight-bold opacity-50">Stock FG</div>
                <div class="h5 font-weight-bold">READY</div>
            </a>
            <div class="flow-pulse"></div>
            <a href="{{ route('delivery.index') }}" class="flow-step">
                <i class="fas fa-truck-loading fa-2x mb-2 text-danger"></i>
                <div class="small font-weight-bold opacity-50">Pending SJ</div>
                <div class="h5 font-weight-bold">{{ $pendingDelvCount ?? 0 }}</div>
            </a>
        </div>
    </div>

    {{-- 📊 5. DATA GRID rill --}}
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="tactical-card overflow-hidden">
                <div class="p-4 bg-danger text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold uppercase small tracking-widest"><i class="fas fa-bolt mr-2"></i> Shortage Ledger</h6>
                    <span class="badge bg-white text-danger">{{ isset($permintaanStok) ? count($permintaanStok) : 0 }} PARTS</span>
                </div>
                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table table-modern mb-0">
                        <tbody>
                            @if(isset($permintaanStok))
                                @foreach($permintaanStok as $p)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold" style="font-family: 'JetBrains Mono';">{{ $p->part_no }}</div>
                                        <small class="text-muted">{{ $p->customer_code ?? 'AMK' }}</small>
                                    </td>
                                    <td class="text-right">
                                        <div class="text-danger font-weight-bold">{{ $p->actual_stock }}</div>
                                        <div class="text-muted" style="font-size: 9px;">MIN: {{ $p->min_stock_pcs }}</div>
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="tactical-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="font-weight-bold m-0 uppercase text-primary">Live Inventory Focus rill</h6>
                    <form action="{{ route('dashboard') }}" method="GET" class="hide-mobile">
                        <select name="customer" class="btn btn-light btn-sm rounded-pill border px-3" onchange="this.form.submit()">
                            <option value="">-- ALL_CLIENTS --</option>
                            @if(isset($customersList))
                                @foreach($customersList as $cust)
                                    <option value="{{ $cust }}" {{ ($selectedCustomer ?? '') == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                                @endforeach
                            @endif
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
        // --- 🕒 Clock rill ---
        setInterval(() => {
            document.getElementById('real-clock').innerText = new Date().toLocaleTimeString('id-ID', { hour12: false });
        }, 1000);

        // --- 🔢 Numbers rill ---
        const rollNumbers = document.querySelectorAll('.roll-number');
        rollNumbers.forEach(el => {
            let target = parseFloat(el.getAttribute('data-target'));
            let count = 0;
            let timer = setInterval(() => {
                count += target / 30;
                if (count >= target) { el.innerText = Math.floor(target).toLocaleString(); clearInterval(timer); } 
                else { el.innerText = Math.floor(count).toLocaleString(); }
            }, 30);
        });

        // --- 📊 Chart rill ---
        const ctx = document.getElementById('mainDashboardChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($labels ?? []) !!},
                datasets: [
                    { label: 'ACTUAL', data: {!! json_encode($actStockData ?? []) !!}, backgroundColor: '#4361ee', borderRadius: 8 },
                    { label: 'MIN', data: {!! json_encode($minStockData ?? []) !!}, borderColor: '#f72585', borderWidth: 3, type: 'line', pointRadius: 0 }
                ]
            },
            options: { 
                responsive: true, maintainAspectRatio: false,
                scales: { x: { ticks: { font: { size: 9 } } } }
            }
        });
    });
</script>
@endsection