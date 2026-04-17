@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root { --primary: #4361ee; --dark: #0f172a; --bg: #f8fafc; }
    body { background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark); }

    /* Tactical Card Style rill */
    .t-card { background: #fff; border-radius: 28px; border: 1px solid #e2e8f0; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; overflow: hidden; }
    .t-card:hover { transform: translateY(-10px); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08); }
    
    .label-tech { font-size: 10px; font-weight: 800; text-transform: uppercase; color: #94a3b8; letter-spacing: 2px; }
    .val-tech { font-family: 'Orbitron', sans-serif; font-size: 38px; font-weight: 900; letter-spacing: -2px; }

    /* Button Navigation rill */
    .nav-btn { background: var(--dark); color: #fff !important; border-radius: 50px; font-family: 'Orbitron'; font-size: 11px; padding: 12px 25px; border: none; letter-spacing: 1px; }
    
    /* Gauge Style */
    .gauge-box { position: relative; height: 200px; width: 200px; margin: 0 auto; }
    .gauge-text { position: absolute; top: 55%; left: 50%; transform: translate(-50%, -50%); font-family: 'Orbitron'; font-size: 24px; font-weight: 900; }

    .laser-line { height: 2px; background: linear-gradient(90deg, transparent, var(--primary), transparent); width: 100%; position: absolute; top: 0; animation: scan 3s linear infinite; }
    @keyframes scan { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
</style>

<div class="container-fluid py-4">
    
    {{-- 🛰️ HEADER CENTER rill --}}
    <div class="t-card p-4 mb-4">
        <div class="laser-line"></div>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 font-weight-extrabold uppercase" style="letter-spacing: -1.5px;">
                    Intelligence <span class="text-primary">Command Center</span>
                </h1>
                <p class="text-muted small font-weight-bold mb-0">
                    MODE: <span class="text-primary">{{ strtoupper($mode) }}</span> // SYSTEM_STATUS: <span class="text-success">ONLINE</span>
                </p>
            </div>
            <div class="dropdown">
                <button class="nav-btn dropdown-toggle shadow-lg" data-toggle="dropdown">
                    <i class="fas fa-crosshairs mr-2"></i> QUICK_NAV
                </button>
                <div class="dropdown-menu dropdown-menu-right border-0 shadow-xl p-2" style="border-radius: 20px; min-width: 250px;">
                    <a class="dropdown-item rounded-lg py-3 font-weight-bold {{ $mode == 'summary' ? 'bg-primary text-white' : '' }}" href="{{ route('dashboard', ['mode' => 'summary']) }}">
                        <i class="fas fa-chart-pie mr-2"></i> INVENTORY_SUMMARY
                    </a>
                    <a class="dropdown-item rounded-lg py-3 font-weight-bold {{ $mode == 'delivery' ? 'bg-primary text-white' : '' }}" href="{{ route('dashboard', ['mode' => 'delivery']) }}">
                        <i class="fas fa-truck-loading mr-2"></i> DELIVERY_PERFORMANCE
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 📊 TACTICAL METRICS rill --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="t-card p-4" style="border-left: 6px solid var(--primary);">
                <div class="label-tech">Stock_Assets</div>
                <div class="val-tech text-dark">{{ $totalParts }}</div>
                <small class="font-weight-bold text-muted uppercase">Parts Registered</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="t-card p-4" style="border-left: 6px solid #f72585;">
                <div class="label-tech text-danger">Crit_Shortage</div>
                <div class="val-tech text-danger">{{ $critCount }}</div>
                <small class="font-weight-bold text-danger uppercase alert-pulse">Action Required</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="t-card p-4" style="border-left: 6px solid #4cc9f0;">
                <div class="label-tech text-info">Output_FG</div>
                <div class="val-tech text-info">+{{ $todayProd }}</div>
                <small class="font-weight-bold text-muted uppercase">Today Production</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="t-card p-4" style="border-left: 6px solid #f8961e;">
                <div class="label-tech text-warning">Dispatched</div>
                <div class="val-tech text-warning">-{{ $todayDelv }}</div>
                <small class="font-weight-bold text-muted uppercase">Units Dispatched</small>
            </div>
        </div>
    </div>

    {{-- 📦 VIEW: DELIVERY MODE rill --}}
    @if($mode == 'delivery')
    <div class="row animate__animated animate__fadeInUp">
        <div class="col-md-4 mb-4">
            <div class="t-card p-4 h-100 text-center">
                <h6 class="font-weight-bold uppercase mb-4 tracking-widest">Target Fulfillment</h6>
                <div class="gauge-box">
                    <canvas id="performanceGauge"></canvas>
                    <div class="gauge-text text-primary">{{ $deliveryPerformance }}%</div>
                </div>
                <p class="text-muted small font-weight-bold mt-4 uppercase">Overall Delivery Accuracy</p>
            </div>
        </div>
        <div class="col-md-8 mb-4">
            <div class="t-card p-4 h-100">
                <h6 class="font-weight-bold uppercase mb-4 tracking-widest text-primary">30-Day Logistic Trend</h6>
                <div style="height: 300px;"><canvas id="deliveryTrendChart"></canvas></div>
            </div>
        </div>
        <div class="col-12">
            <div class="t-card overflow-hidden">
                <div class="p-4 bg-dark text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold uppercase tracking-widest">Customer Dispatch Analytics</h6>
                    <span class="badge badge-primary px-3">{{ count($customerShipments) }} ACTIVE_CLIENTS</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 text-center">
                        <thead class="bg-light">
                            <tr><th class="text-left pl-5">CLIENT_ENTITY</th><th>TOTAL_UNIT_OUT</th><th>MANIFEST_COUNT</th><th>STATUS</th></tr>
                        </thead>
                        <tbody style="font-family: 'JetBrains Mono';">
                            @foreach($customerShipments as $cs)
                            <tr>
                                <td class="text-left pl-5 font-weight-bold">{{ $cs->customer_name }}</td>
                                <td class="font-weight-bold text-danger" style="font-size: 16px;">-{{ number_format($cs->total_qty) }}</td>
                                <td><span class="badge badge-light border px-3">{{ $cs->total_sj }} SJ</span></td>
                                <td><span class="text-success font-weight-bold">● OPTIMIZED</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- 📊 VIEW: SUMMARY MODE rill --}}
    @if($mode == 'summary')
    <div class="t-card p-4 animate__animated animate__fadeInUp">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="font-weight-bold m-0 uppercase tracking-widest text-primary"><i class="fas fa-warehouse mr-2"></i> Inventory Status Per Part</h6>
            <form action="{{ route('dashboard') }}" method="GET">
                <input type="hidden" name="mode" value="summary">
                <select name="customer" class="btn btn-light btn-sm rounded-pill font-weight-bold px-4 border" onchange="this.form.submit()">
                    <option value="">-- ALL_CLIENTS --</option>
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
                    { label: 'MIN', data: {!! json_encode($minStockData) !!}, borderColor: '#f72585', borderWidth: 3, type: 'line', pointRadius: 0 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
        @endif

        @if($mode == 'delivery')
        // Performance Gauge rill
        const ctxGauge = document.getElementById('performanceGauge').getContext('2d');
        new Chart(ctxGauge, {
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

        // Trend Chart rill
        const ctxTrend = document.getElementById('deliveryTrendChart').getContext('2d');
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: {!! json_encode($deliveryTrend->pluck('date')) !!},
                datasets: [{
                    label: 'Unit Out',
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