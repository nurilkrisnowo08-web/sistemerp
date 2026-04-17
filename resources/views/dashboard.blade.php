@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root { --primary: #4361ee; --dark: #0f172a; --bg: #f8fafc; }
    body { background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark); }

    .t-card { background: #fff; border-radius: 28px; border: 1px solid #e2e8f0; transition: 0.4s; position: relative; overflow: hidden; }
    .t-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.05); }
    
    .val-tech { font-family: 'Orbitron', sans-serif; font-size: 38px; font-weight: 900; letter-spacing: -2px; }
    .nav-btn { background: var(--dark); color: #fff !important; border-radius: 50px; font-family: 'Orbitron'; font-size: 11px; padding: 12px 25px; border: none; }

    /* Performance Gauge rill */
    .gauge-wrapper { position: relative; width: 220px; height: 110px; margin: 0 auto; overflow: hidden; }
    .gauge-text { position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); font-family: 'Orbitron'; font-size: 28px; font-weight: 900; color: var(--primary); }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
    {{-- 🛸 HEADER HUB rill --}}
    <div class="t-card p-4 mb-4" style="border-top: 4px solid var(--primary);">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 font-weight-extrabold uppercase">Intelligence <span class="text-primary">Command Center</span></h1>
                <p class="text-muted small font-weight-bold mb-0">MODE: <span class="badge badge-primary">{{ strtoupper($mode) }}</span> // L-TIME: {{ date('H:i') }}</p>
            </div>
            <div class="dropdown">
                <button class="nav-btn dropdown-toggle shadow-lg" data-toggle="dropdown">
                    <i class="fas fa-microchip mr-2"></i> QUICK_NAV
                </button>
                <div class="dropdown-menu dropdown-menu-right border-0 shadow-xl p-2" style="border-radius: 20px; min-width: 260px;">
                    <a class="dropdown-item rounded-lg py-3 font-weight-bold {{ $mode == 'summary' ? 'bg-primary text-white' : '' }}" href="{{ route('dashboard', ['mode' => 'summary']) }}">
                        <i class="fas fa-chart-pie mr-2"></i> INVENTORY_MONITOR
                    </a>
                    <a class="dropdown-item rounded-lg py-3 font-weight-bold {{ $mode == 'delivery' ? 'bg-primary text-white' : '' }}" href="{{ route('dashboard', ['mode' => 'delivery']) }}">
                        <i class="fas fa-truck-loading mr-2"></i> DELIVERY_PERFORMANCE
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 🚀 3. TACTICAL CARDS (Standard) --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3"><div class="t-card p-4 h-100" style="border-left: 6px solid var(--primary);"><div class="stat-label small font-weight-bold text-muted">ASSETS</div><div class="val-tech">{{ $totalParts }}</div><small class="font-weight-bold text-muted">Parts Registered</small></div></div>
        <div class="col-md-3 mb-3"><div class="t-card p-4 h-100" style="border-left: 6px solid #f43f5e;"><div class="stat-label small font-weight-bold text-danger">CRITICAL</div><div class="val-tech text-danger">{{ $critCount }}</div><small class="font-weight-bold text-danger alert-pulse">Action Required</small></div></div>
        <div class="col-md-3 mb-3"><div class="t-card p-4 h-100" style="border-left: 6px solid #10b981;"><div class="stat-label small font-weight-bold text-success">OUTPUT</div><div class="val-tech text-success">+{{ $todayProd }}</div><small class="font-weight-bold text-muted">Today Production</small></div></div>
        <div class="col-md-3 mb-3"><div class="t-card p-4 h-100" style="border-left: 6px solid #f59e0b;"><div class="stat-label small font-weight-bold text-warning">SHIPMENT</div><div class="val-tech text-warning">-{{ $todayDelv }}</div><small class="font-weight-bold text-muted">Units Dispatched</small></div></div>
    </div>

    {{-- --- ✨ VIEW: MODE DELIVERY ✨ --- --}}
    @if($mode == 'delivery')
    <div class="row animate__animated animate__zoomIn">
        <div class="col-md-4 mb-4">
            <div class="t-card p-4 h-100 text-center">
                <h6 class="font-weight-bold uppercase mb-4 tracking-widest text-primary">Fulfillment Rate</h6>
                <div class="gauge-wrapper">
                    <canvas id="perfGauge"></canvas>
                    <div class="gauge-text">{{ $deliveryPerformance }}%</div>
                </div>
                <p class="text-muted small font-weight-bold mt-3">Target vs Actual Dispatch rill</p>
            </div>
        </div>
        <div class="col-md-8 mb-4">
            <div class="t-card p-4 h-100">
                <h6 class="font-weight-bold uppercase mb-4 tracking-widest text-primary">30-Day Logistics Flow</h6>
                <div style="height: 250px;"><canvas id="deliveryTrend"></canvas></div>
            </div>
        </div>
        <div class="col-12">
            <div class="t-card overflow-hidden">
                <div class="p-4 bg-dark text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold uppercase tracking-widest">Customer Delivery Ledger</h6>
                    <span class="badge badge-primary px-3">{{ count($customerShipments) }} CLIENTS</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 text-center">
                        <thead class="bg-light">
                            <tr><th class="text-left pl-5">Client Entity</th><th>Volume Out</th><th>Manifests</th><th>Status</th></tr>
                        </thead>
                        <tbody style="font-family: 'JetBrains Mono'; font-weight: 700;">
                            @foreach($customerShipments as $cs)
                            <tr>
                                <td class="text-left pl-5 text-primary">{{ $cs->customer_name }}</td>
                                <td class="h5 font-weight-bold text-danger">-{{ number_format($cs->total_qty) }}</td>
                                <td><span class="badge badge-light border px-3">{{ $cs->total_sj }} SJ</span></td>
                                <td><span class="text-success small"><i class="fas fa-check-circle mr-1"></i> OPTIMIZED</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- --- 📊 VIEW: MODE SUMMARY (Default) --- --}}
    @if($mode == 'summary')
    <div class="t-card p-4 animate__animated animate__fadeInUp">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="font-weight-bold m-0 uppercase tracking-widest text-primary"><i class="fas fa-warehouse mr-2"></i> Inventory Analytics</h6>
            <form action="{{ route('dashboard') }}" method="GET">
                <input type="hidden" name="mode" value="summary">
                <select name="customer" class="btn btn-light btn-sm rounded-pill font-weight-bold px-4 border" onchange="this.form.submit()">
                    <option value="">-- ALL CLIENTS --</option>
                    @foreach($customersList as $cust)
                        <option value="{{ $cust }}" {{ $selectedCustomer == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div style="height: 380px;"><canvas id="mainStockChart"></canvas></div>
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if($mode == 'summary')
            const ctxMain = document.getElementById('mainStockChart').getContext('2d');
            new Chart(ctxMain, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($labels) !!},
                    datasets: [
                        { label: 'ACTUAL', data: {!! json_encode($actStockData) !!}, backgroundColor: '#4361ee', borderRadius: 12 },
                        { label: 'MIN', data: {!! json_encode($minStockData) !!}, borderColor: '#f43f5e', borderWidth: 3, type: 'line', pointRadius: 0 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        @endif

        @if($mode == 'delivery')
            // Performance Gauge rill
            new Chart(document.getElementById('perfGauge').getContext('2d'), {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [{{ $deliveryPerformance }}, {{ max(0, 100 - $deliveryPerformance) }}],
                        backgroundColor: ['#4361ee', '#f1f5f9'],
                        borderWidth: 0, circumference: 180, rotation: 270,
                    }]
                },
                options: { cutout: '85%', plugins: { legend: { display: false } } }
            });

            // Delivery Trend rill
            new Chart(document.getElementById('deliveryTrend').getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($deliveryTrend->pluck('date')) !!},
                    datasets: [{
                        label: 'Units Out',
                        data: {!! json_encode($deliveryTrend->pluck('total')) !!},
                        borderColor: '#4361ee', backgroundColor: 'rgba(67, 97, 238, 0.1)', fill: true, tension: 0.4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        @endif
    });
</script>
@endsection