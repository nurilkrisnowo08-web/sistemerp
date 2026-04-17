@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Roboto+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root {
        --ind-navy: #0f172a; --ind-blue: #3b82f6; --ind-cyan: #06b6d4;
        --ind-success: #10b981; --ind-danger: #ef4444; --ind-warning: #f59e0b;
        --ind-bg: #f8fafc;
    }
    .main-terminal { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--ind-bg); min-height: 100vh; padding: 1.5rem; }

    /* ✨ ANIMASI LASER STREAM (HEADER) */
    @keyframes laserFlow { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    .laser-line { height: 1px; background: linear-gradient(90deg, transparent, var(--ind-cyan), transparent); width: 100%; position: absolute; animation: laserFlow 2s linear infinite; }
    
    /* 🚨 CRITICAL TICKER (ALIRAN DATA KRITIS) */
    .ticker-wrap { background: #fff; border: 1px solid #fee2e2; border-radius: 12px; overflow: hidden; padding: 10px 0; margin-bottom: 2rem; box-shadow: 0 5px 15px rgba(239, 68, 68, 0.05); }
    .ticker-move { display: flex; width: max-content; animation: ticker 30s linear infinite; }
    .ticker-item { padding: 0 30px; font-weight: 800; font-size: 11px; color: var(--ind-danger); display: flex; align-items: center; text-transform: uppercase; }
    @keyframes ticker { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }

    /* ✨ SCANNING TABLE EFFECT */
    @keyframes scan { 0% { top: -100%; } 100% { top: 100%; } }
    .table-scan { position: relative; overflow: hidden; }
    .table-scan::after { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 50px; background: linear-gradient(180deg, transparent, rgba(6, 182, 212, 0.05), transparent); animation: scan 3s linear infinite; pointer-events: none; }

    /* UI CARDS & FLOW */
    .tactical-card { background: #fff; border: none; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); transition: all 0.4s; position: relative; overflow: hidden; text-decoration: none !important; }
    .tactical-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(59, 130, 246, 0.1); }
    .stat-label { font-size: 10px; font-weight: 800; text-transform: uppercase; color: #94a3b8; }
    .stat-value { font-family: 'Roboto Mono', monospace; font-size: 32px; font-weight: 800; color: var(--ind-navy); }

    .flow-path { background: var(--ind-navy); border-radius: 30px; padding: 40px; position: relative; overflow: hidden; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.3); border: 1px solid rgba(255,255,255,0.1); }
    .flow-step { text-align: center; color: #fff; z-index: 2; position: relative; flex: 1; }
    @keyframes gearRotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .gear-anim { animation: gearRotate 4s linear infinite; display: inline-block; color: var(--ind-warning); }
</style>

<div class="container-fluid main-terminal text-dark anim-fade-up">
    
    {{-- 🛸 1. HEADER HUD WITH LASER STREAM --}}
    <div class="bg-white p-4 mb-4 shadow-sm border-bottom position-relative overflow-hidden" style="border-radius: 24px; margin-top: -10px;">
        <div class="laser-line" style="top: 0; animation-delay: 0s;"></div>
        <div class="laser-line" style="bottom: 0; animation-delay: 1s; opacity: 0.5;"></div>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 text-gray-900 font-weight-extrabold uppercase" style="letter-spacing: -1px;">
                    Intelligence <span style="color: var(--ind-blue)">Command Center</span>
                </h1>
                <p class="text-muted font-weight-bold mb-0 small uppercase"><i class="fas fa-microchip mr-2 text-primary"></i> SYSTEM_OPS: <span class="text-success">ONLINE</span> // PIC: Musa Wahab</p>
            </div>
            <div class="bg-light px-4 py-2 rounded-pill font-weight-bold" style="font-family: 'Roboto Mono';">
                <i class="far fa-clock text-primary mr-2"></i>{{ date('H:i:s') }}
            </div>
        </div>
    </div>

    {{-- 🚨 2. CRITICAL LIVE TICKER (ALIRAN DATA CRITICAL) --}}
    @if(count($permintaanStok) > 0)
    <div class="ticker-wrap">
        <div class="ticker-move">
            @foreach($permintaanStok->merge($permintaanStok) as $p) {{-- Merge biar loop nyambung --}}
            <div class="ticker-item">
                <i class="fas fa-exclamation-circle mr-2"></i> 
                ALERT: STOCK [{{ $p->part_no }}] - {{ $p->part_name }} ({{ $p->customer_code ?? 'AMK' }}) IS CRITICAL! ACTUAL: {{ $p->actual_stock }}
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 🚀 3. TACTICAL SUMMARY --}}
    <div class="row mb-5">
        <a href="{{ route('parts.index') }}" class="col-md-3 tactical-card p-4 mx-2 flex-fill" style="border-left: 6px solid var(--ind-blue);">
            <div class="stat-label">Inventory Assets</div>
            <div class="stat-value roll-number" data-target="{{ $totalParts }}">0</div>
            <div class="small font-weight-bold text-muted mt-2"><i class="fas fa-database mr-1"></i> Part Registered</div>
        </a>

        <a href="{{ route('fg.index', ['status' => 'crit']) }}" class="col-md-3 tactical-card p-4 mx-2 flex-fill" style="border-left: 6px solid var(--ind-danger);">
            <div class="stat-label text-danger">Stock Critical</div>
            <div class="stat-value text-danger roll-number" data-target="{{ $critCount }}">0</div>
            <div class="small font-weight-bold text-danger mt-2 alert-pulse"><i class="fas fa-exclamation-triangle mr-1"></i> Need Production</div>
        </a>

        <a href="{{ route('fg.index') }}" class="col-md-3 tactical-card p-4 mx-2 flex-fill" style="border-left: 6px solid var(--ind-success);">
            <div class="stat-label text-success">Finished Goods In</div>
            <div class="stat-value text-success">+<span class="roll-number" data-target="{{ $todayProd }}">0</span></div>
            <div class="small font-weight-bold text-muted mt-2"><i class="fas fa-warehouse mr-1"></i> Production Today</div>
        </a>

        <a href="{{ route('delivery.index') }}" class="col-md-3 tactical-card p-4 mx-2 flex-fill" style="border-left: 6px solid var(--ind-warning);">
            <div class="stat-label text-warning">Shipment Out</div>
            <div class="stat-value text-warning">-<span class="roll-number" data-target="{{ $todayDelv }}">0</span></div>
            <div class="small font-weight-bold text-muted mt-2"><i class="fas fa-truck mr-1"></i> Units Dispatched</div>
        </a>
    </div>

    {{-- 🌊 4. PO FLOW SYSTEM --}}
    <div class="row mb-5">
        <div class="col-12">
            <div class="flow-path d-flex align-items-center justify-content-between">
                <a href="{{ route('po-customer.index') }}" class="flow-step text-decoration-none">
                    <i class="fas fa-file-invoice fa-2x mb-3 text-primary d-block"></i>
                    <div class="stat-label text-gray-400">Total PO</div>
                    <div class="h4 font-weight-extrabold text-white mb-0">{{ $totalPO }} <small style="font-size: 10px; color: var(--ind-cyan);">ACTIVE</small></div>
                </a>
                <div class="flow-line-container"><div class="flow-pulse"></div></div>
                <a href="{{ route('produksi.index') }}" class="flow-step text-decoration-none">
                    <i class="fas fa-desktop fa-2x mb-3 gear-anim d-block"></i>
                    <div class="stat-label text-gray-400">Live Monitoring</div>
                    <div class="h4 font-weight-extrabold text-warning mb-0">PRODUKSI</div>
                </a>
                <div class="flow-line-container"><div class="flow-pulse" style="animation-delay: 1.5s;"></div></div>
                <a href="{{ route('fg.index') }}" class="flow-step text-decoration-none">
                    <i class="fas fa-warehouse fa-2x mb-3 text-success d-block"></i>
                    <div class="stat-label text-gray-400">Inventory FG</div>
                    <div class="h4 font-weight-extrabold text-success mb-0">AVAILABLE</div>
                </a>
                <div class="flow-line-container"><div class="flow-pulse" style="animation-delay: 2.5s;"></div></div>
                <a href="{{ route('delivery.index') }}" class="flow-step text-decoration-none">
                    <i class="fas fa-paper-plane fa-2x mb-3 text-danger d-block"></i>
                    <div class="stat-label text-gray-400">Pending SJ</div>
                    <div class="h4 font-weight-extrabold text-danger mb-0">{{ $pendingDelvCount }} <small style="font-size: 10px;">MANIFEST</small></div>
                </a>
            </div>
        </div>
    </div>

    {{-- 📊 5. CRITICAL TABLE & CHART --}}
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="tactical-card h-100 p-0 table-scan">
                <div class="p-4 border-bottom bg-danger d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-white small uppercase tracking-widest"><i class="fas fa-bolt mr-2"></i> Critical Shortage Ledger</h6>
                    <span class="badge bg-white text-danger font-weight-bold">{{ count($permintaanStok) }} PARTS</span>
                </div>
                <div class="table-responsive" style="max-height: 380px;">
                    <table class="table table-ledger text-center mb-0">
                        <thead>
                            <tr><th>Customer</th><th class="text-left">Part Identity</th><th>Stock</th></tr>
                        </thead>
                        <tbody>
                            @foreach($permintaanStok as $p)
                            <tr>
                                <td><span class="badge badge-light border text-primary">{{ $p->customer_code ?? 'AMK' }}</span></td>
                                <td class="text-left">
                                    <div class="font-weight-bold" style="font-family: 'Roboto Mono'; font-size: 11px;">{{ $p->part_no }}</div>
                                    <div class="small text-muted" style="font-size: 9px;">{{ Str::limit($p->part_name, 25) }}</div>
                                </td>
                                <td>
                                    <div class="text-danger font-weight-extrabold">{{ number_format($p->actual_stock) }}</div>
                                    <div class="text-muted small" style="font-size: 9px;">MIN: {{ $p->min_stock_pcs }}</div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="tactical-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="font-weight-bold text-dark small uppercase tracking-widest"><i class="fas fa-chart-area mr-2 text-primary"></i> Focus Analytics</h6>
                    <form action="{{ route('dashboard') }}" method="GET" id="chartFilter">
                        <select name="customer" class="filter-select shadow-sm" onchange="document.getElementById('chartFilter').submit()">
                            <option value="">-- ALL CUSTOMERS --</option>
                            @foreach($customersList as $cust)
                                <option value="{{ $cust }}" {{ $selectedCustomer == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div style="height: 280px;"><canvas id="dbChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const rollNumbers = document.querySelectorAll('.roll-number');
        rollNumbers.forEach(el => {
            let target = parseFloat(el.getAttribute('data-target'));
            let count = 0;
            let timer = setInterval(() => {
                count += target / 40;
                if (count >= target) { el.innerText = Math.floor(target).toLocaleString(); clearInterval(timer); } 
                else { el.innerText = Math.floor(count).toLocaleString(); }
            }, 30);
        });

        const ctx = document.getElementById('dbChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($labels) !!},
                datasets: [
                    { label: 'ACTUAL', data: {!! json_encode($actStockData) !!}, backgroundColor: 'rgba(59, 130, 246, 0.85)', borderRadius: 10 },
                    { label: 'MIN', data: {!! json_encode($minStockData) !!}, backgroundColor: 'rgba(239, 68, 68, 0.2)', borderColor: '#ef4444', borderWidth: 2, type: 'line', pointRadius: 0, tension: 0.4 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { ticks: { font: { family: 'Roboto Mono', size: 8 }, maxRotation: 45, minRotation: 45 } }, y: { beginAtZero: true } } }
        });
    });
</script>
@endsection