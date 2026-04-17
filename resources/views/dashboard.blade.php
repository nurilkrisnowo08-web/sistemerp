@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Roboto+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --ind-navy: #0f172a; --ind-blue: #3b82f6; --ind-cyan: #06b6d4;
        --ind-success: #10b981; --ind-danger: #ef4444; --ind-warning: #f59e0b;
        --ind-bg: #f1f5f9;
    }
    .main-terminal { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--ind-bg); min-height: 100vh; padding: 1.5rem; }

    /* ✨ UI ELEMENTS RILL */
    .tactical-card { 
        background: #fff; border: none; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); 
        transition: 0.4s; position: relative; overflow: hidden; border: 1px solid transparent;
    }
    .tactical-card:hover { transform: translateY(-5px); border-color: var(--ind-blue); box-shadow: 0 20px 40px rgba(59, 130, 246, 0.1); }
    
    .glass-header { 
        background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(15px); 
        border-radius: 24px; border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .stat-label { font-size: 10px; font-weight: 800; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px; }
    .stat-value { font-family: 'Orbitron'; font-size: 32px; font-weight: 900; }

    /* 📊 MODE CONTENT RILL */
    .mode-container { display: none; }
    .active-mode { display: block; animation: fadeInUp 0.5s ease-out; }

    .gauge-container { position: relative; height: 220px; width: 220px; margin: 0 auto; }
    .gauge-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; }

    /* CUSTOM TABLE STYLE */
    .table-modern thead th { background: #f8fafc; border: none; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; padding: 15px; }
    .table-modern td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 13px; }

    .laser-line { height: 2px; background: linear-gradient(90deg, transparent, var(--ind-blue), transparent); width: 100%; position: absolute; top: 0; animation: laserFlow 3s linear infinite; }
    @keyframes laserFlow { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
</style>

<div class="container-fluid main-terminal text-dark">
    
    {{-- 🛸 1. DYNAMIC HEADER HUB rill --}}
    <div class="glass-header p-4 mb-4 shadow-sm position-relative overflow-hidden">
        <div class="laser-line"></div>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 font-weight-extrabold uppercase" style="letter-spacing: -1px;">
                    Intelligence <span class="text-primary">Command Center</span>
                </h1>
                <p class="text-muted font-weight-bold mb-0 small uppercase">
                    <i class="fas fa-microchip mr-2 text-primary"></i> 
                    OPERATIONAL_MODE: <span class="text-primary">{{ strtoupper($mode ?? 'SUMMARY') }}</span> 
                    // L-TIME: {{ date('H:i') }} rill
                </p>
            </div>
            
            <div class="dropdown">
                <button class="btn btn-dark rounded-pill px-4 font-weight-extrabold dropdown-toggle shadow-lg" data-toggle="dropdown">
                    <i class="fas fa-satellite mr-2"></i> QUICK_NAV
                </button>
                <div class="dropdown-menu dropdown-menu-right border-0 shadow-xl p-2" style="border-radius: 20px; min-width: 250px;">
                    <h6 class="dropdown-header font-weight-bold text-muted small">SWITCH DASHBOARD MODE</h6>
                    <a class="dropdown-item rounded-lg py-3 font-weight-bold {{ ($mode ?? 'summary') == 'summary' ? 'bg-primary text-white' : '' }}" href="{{ route('dashboard', ['mode' => 'summary']) }}">
                        <i class="fas fa-chart-pie mr-2"></i> SYSTEM_SUMMARY
                    </a>
                    <a class="dropdown-item rounded-lg py-3 font-weight-bold {{ ($mode ?? '') == 'delivery' ? 'bg-primary text-white' : '' }}" href="{{ route('dashboard', ['mode' => 'delivery']) }}">
                        <i class="fas fa-truck-loading mr-2"></i> DELIVERY_PERFORMANCE
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item rounded-lg py-2 font-weight-bold" href="{{ route('fg.index') }}"><i class="fas fa-box mr-2"></i> WAREHOUSE_FG</a>
                </div>
            </div>
        </div>
    </div>

    {{-- 🚨 2. CRITICAL TICKER --}}
    @if(isset($permintaanStok) && count($permintaanStok) > 0)
    <div class="ticker-wrap rounded-pill mb-4 bg-white border shadow-sm overflow-hidden" style="height: 40px;">
        <div class="ticker-move h-100 d-flex align-items-center">
            @foreach($permintaanStok->merge($permintaanStok) as $p)
            <div class="ticker-item px-4 font-weight-bold small text-danger uppercase">
                <i class="fas fa-bolt mr-2"></i> CRITICAL: {{ $p->part_no }} ({{ $p->actual_stock }} UNIT)
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 🚀 3. TACTICAL CARDS --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3"><div class="tactical-card p-4 h-100" style="border-bottom: 4px solid var(--ind-blue);"><div class="stat-label">Stock Assets</div><div class="stat-value text-dark">{{ $totalParts }}</div><div class="small font-weight-bold text-muted mt-1 uppercase">Units Registered</div></div></div>
        <div class="col-md-3 mb-3"><div class="tactical-card p-4 h-100" style="border-bottom: 4px solid var(--ind-danger);"><div class="stat-label text-danger">Crit Shortage</div><div class="stat-value text-danger">{{ $critCount }}</div><div class="small font-weight-bold text-danger mt-1 uppercase alert-pulse">Action Required</div></div></div>
        <div class="col-md-3 mb-3"><div class="tactical-card p-4 h-100" style="border-bottom: 4px solid var(--ind-success);"><div class="stat-label text-success">Finished Today</div><div class="stat-value text-success">+{{ $todayProd }}</div><div class="small font-weight-bold text-muted mt-1 uppercase">Prod Output</div></div></div>
        <div class="col-md-3 mb-3"><div class="tactical-card p-4 h-100" style="border-bottom: 4px solid var(--ind-warning);"><div class="stat-label text-warning">Dispatched</div><div class="stat-value text-warning">-{{ $todayDelv }}</div><div class="small font-weight-bold text-muted mt-1 uppercase">Deliveries</div></div></div>
    </div>

    {{-- --- 📊 MODE CONTENT: SUMMARY rill --- --}}
    @if(($mode ?? 'summary') == 'summary')
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="tactical-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="font-weight-bold m-0 uppercase tracking-widest text-primary"><i class="fas fa-warehouse mr-2"></i> Live Inventory Focus</h6>
                    <form action="{{ route('dashboard') }}" method="GET" id="chartFilter">
                        <input type="hidden" name="mode" value="summary">
                        <select name="customer" class="btn btn-light btn-sm rounded-pill font-weight-bold px-3 border shadow-sm" onchange="this.form.submit()">
                            <option value="">-- ALL CLIENTS --</option>
                            @foreach($customersList as $cust)
                                <option value="{{ $cust }}" {{ $selectedCustomer == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div style="height: 380px;"><canvas id="dbChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="tactical-card h-100 p-0 overflow-hidden">
                <div class="p-4 bg-danger text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold uppercase small tracking-widest">Urgent Shortage</h6>
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table table-modern mb-0">
                        <tbody>
                            @forelse($permintaanStok as $p)
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $p->part_no }}</div>
                                    <div class="text-muted" style="font-size: 10px;">{{ $p->customer_code ?? 'AMK' }}</div>
                                </td>
                                <td class="text-right">
                                    <div class="text-danger font-weight-bold">{{ $p->actual_stock }}</div>
                                    <div class="text-muted" style="font-size: 9px;">MIN: {{ $p->min_stock_pcs }}</div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center py-5 text-muted">Stock Secure rill.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- --- 📦 MODE CONTENT: DELIVERY rill --- --}}
    @if(($mode ?? '') == 'delivery')
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="tactical-card p-4 h-100 text-center">
                <h6 class="font-weight-bold uppercase mb-4 tracking-widest text-primary">Target Fulfillment</h6>
                <div class="gauge-container">
                    <canvas id="gaugeChart"></canvas>
                    <div class="gauge-center">
                        <div class="h3 font-weight-bold mb-0" style="font-family: 'Orbitron';">{{ $deliveryPerformance }}%</div>
                        <small class="text-muted font-weight-bold">COMPLETED</small>
                    </div>
                </div>
                <p class="text-muted small mt-4">Calculated from total PO items vs Shipments rill.</p>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="tactical-card p-4 h-100">
                <h6 class="font-weight-bold uppercase mb-4 tracking-widest text-primary">30-Day Dispatch Trend</h6>
                <div style="height: 300px;"><canvas id="trendChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="tactical-card p-0 overflow-hidden mt-2">
        <div class="p-4 bg-dark text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold uppercase small tracking-widest">Customer Delivery Performance Ledger</h6>
            <span class="badge badge-primary px-3">{{ count($customerShipments) }} ACTIVE_CLIENTS</span>
        </div>
        <div class="table-responsive">
            <table class="table table-modern table-hover mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Client Entity</th>
                        <th>Total Volume (PCS)</th>
                        <th>Manifests Issued (SJ)</th>
                        <th>Performance Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customerShipments as $cs)
                    <tr>
                        <td class="text-left pl-4">
                            <div class="font-weight-bold text-primary">{{ $cs->customer_name }}</div>
                            <small class="text-muted">Corporate Entity</small>
                        </td>
                        <td class="h5 font-weight-bold text-dark">{{ number_format($cs->total_qty) }}</td>
                        <td><span class="badge badge-light border px-3">{{ $cs->total_sj }} SJ</span></td>
                        <td><span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> OPTIMIZED</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-5 text-muted">No shipment data recorded for this mode rill.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- LOGIC SUMMARY MODE ---
        @if(($mode ?? 'summary') == 'summary')
        const ctxMain = document.getElementById('dbChart').getContext('2d');
        new Chart(ctxMain, {
            type: 'bar',
            data: {
                labels: {!! json_encode($labels) !!},
                datasets: [
                    { label: 'ACTUAL', data: {!! json_encode($actStockData) !!}, backgroundColor: '#4361ee', borderRadius: 10 },
                    { label: 'MIN', data: {!! json_encode($minStockData) !!}, borderColor: '#ef4444', borderWidth: 2, type: 'line', pointRadius: 0 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
        @endif

        // --- LOGIC DELIVERY MODE ---
        @if(($mode ?? '') == 'delivery')
        // 1. Gauge Chart rill
        const ctxGauge = document.getElementById('gaugeChart').getContext('2d');
        new Chart(ctxGauge, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [{{ $deliveryPerformance }}, {{ max(0, 100 - $deliveryPerformance) }}],
                    backgroundColor: ['#4361ee', '#f1f5f9'],
                    borderWidth: 0,
                    circumference: 180,
                    rotation: 270,
                }]
            },
            options: { cutout: '85%', plugins: { legend: { display: false } } }
        });

        // 2. Trend Chart rill
        const ctxTrend = document.getElementById('trendChart').getContext('2d');
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: {!! json_encode($deliveryTrend->pluck('date')) !!},
                datasets: [{
                    label: 'Units Dispatched',
                    data: {!! json_encode($deliveryTrend->pluck('total')) !!},
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
        @endif
    });
</script>
@endsection