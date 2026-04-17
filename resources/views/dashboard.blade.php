@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --primary: #4361ee; --secondary: #3f37c9; --accent: #4cc9f0;
        --danger: #f72585; --success: #4cc9f0; --warning: #f8961e;
        --dark-bg: #0f172a; --glass: rgba(255, 255, 255, 0.95);
    }

    body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark-bg); }

    /* ✨ UI CARD TACTICAL rill */
    .tactical-card {
        background: #ffffff; border: none; border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative; overflow: hidden; border: 1px solid #e2e8f0;
    }
    .tactical-card:hover { transform: translateY(-8px); border-color: var(--primary); box-shadow: 0 20px 40px rgba(67, 97, 238, 0.1); }

    .stat-label { font-size: 10px; font-weight: 800; text-transform: uppercase; color: #94a3b8; letter-spacing: 1.5px; }
    
    /* Font Angka Futuristik rill */
    .stat-value { font-family: 'Orbitron', sans-serif; font-size: 36px; font-weight: 900; letter-spacing: -1px; }

    /* ✨ MODE SELECTOR rill */
    .quick-nav-btn {
        background: var(--dark-bg); color: white !important; border-radius: 50px;
        padding: 12px 25px; font-weight: 800; font-family: 'Orbitron'; font-size: 12px;
        border: none; box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .dropdown-menu { border-radius: 20px; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.1); padding: 10px; }
    .dropdown-item { border-radius: 12px; padding: 12px; font-weight: 700; transition: 0.3s; }
    .dropdown-item:hover { background: var(--primary); color: white; }

    .laser-line { height: 3px; background: linear-gradient(90deg, transparent, var(--primary), transparent); width: 100%; position: absolute; top: 0; animation: laser 3s linear infinite; }
    @keyframes laser { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }

    .chart-container { background: #fff; border-radius: 24px; padding: 30px; border: 1px solid #e2e8f0; }
</style>

<div class="container-fluid py-4">
    
    {{-- 🛰️ TOP HEADER CENTER rill --}}
    <div class="tactical-card p-4 mb-4">
        <div class="laser-line"></div>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="font-weight-extrabold m-0" style="letter-spacing: -2px;">COMMAND <span class="text-primary">CENTER v2.0</span></h2>
                <p class="text-muted small font-weight-bold m-0 uppercase tracking-widest">
                    Mode: <span class="text-primary">{{ strtoupper($mode ?? 'Summary') }}</span> // pic: {{ auth()->user()->name ?? 'Admin' }}
                </p>
            </div>
            <div class="dropdown">
                <button class="quick-nav-btn dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-microchip mr-2"></i> QUICK_NAV
                </button>
                <div class="dropdown-menu dropdown-menu-right animate__animated animate__fadeIn">
                    <a class="dropdown-item" href="{{ route('dashboard', ['mode' => 'summary']) }}"><i class="fas fa-chart-line mr-2"></i> MONITORING_STOK</a>
                    <a class="dropdown-item" href="{{ route('dashboard', ['mode' => 'delivery']) }}"><i class="fas fa-truck-loading mr-2"></i> PERFORMANCE_DELIVERY</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="{{ route('fg.index') }}"><i class="fas fa-box mr-2"></i> WAREHOUSE_ACCESS</a>
                </div>
            </div>
        </div>
    </div>

    {{-- 🚀 TACTICAL METRICS rill --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="tactical-card p-4" style="border-bottom: 5px solid var(--primary);">
                <div class="stat-label">Stock_Assets</div>
                <div class="stat-value text-dark roll-number" data-target="{{ $totalParts }}">{{ $totalParts }}</div>
                <small class="text-muted font-weight-bold uppercase">Units Registered</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="tactical-card p-4" style="border-bottom: 5px solid var(--danger);">
                <div class="stat-label text-danger">Crit_Shortage</div>
                <div class="stat-value text-danger roll-number" data-target="{{ $critCount }}">{{ $critCount }}</div>
                <small class="text-danger font-weight-bold uppercase alert-pulse">Action Required</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="tactical-card p-4" style="border-bottom: 5px solid var(--success);">
                <div class="stat-label text-success">Output_Daily</div>
                <div class="stat-value text-success">+<span class="roll-number" data-target="{{ $todayProd }}">{{ $todayProd }}</span></div>
                <small class="text-muted font-weight-bold uppercase">Finished Goods</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="tactical-card p-4" style="border-bottom: 5px solid var(--warning);">
                <div class="stat-label text-warning">Dispatched</div>
                <div class="stat-value text-warning">-<span class="roll-number" data-target="{{ $todayDelv }}">{{ $todayDelv }}</span></div>
                <small class="text-muted font-weight-bold uppercase">Delivery Manifest</small>
            </div>
        </div>
    </div>

    {{-- 📦 VIEW: MODE DELIVERY rill --}}
    @if(($mode ?? 'summary') == 'delivery')
    <div class="row animate__animated animate__fadeInUp">
        <div class="col-md-4 mb-4">
            <div class="tactical-card p-5 text-center h-100">
                <h6 class="font-weight-bold uppercase text-muted mb-4">Fulfillment_Rate</h6>
                <div style="position: relative; height: 180px;">
                    <canvas id="gaugeChart"></canvas>
                    <div style="position: absolute; top: 60%; left: 50%; transform: translate(-50%, -50%);">
                        <div class="stat-value" style="font-size: 28px;">{{ $deliveryPerformance }}%</div>
                    </div>
                </div>
                <p class="text-muted small font-weight-bold mt-3 uppercase">Total Target Achieved</p>
            </div>
        </div>
        <div class="col-md-8 mb-4">
            <div class="tactical-card p-4 h-100">
                <h6 class="font-weight-bold uppercase text-primary mb-4 tracking-widest">30-Day Logistics Trend</h6>
                <div style="height: 280px;"><canvas id="deliveryTrendChart"></canvas></div>
            </div>
        </div>
    </div>
    @endif

    {{-- 📊 VIEW: MODE SUMMARY rill --}}
    @if(($mode ?? 'summary') == 'summary')
    <div class="tactical-card p-4 animate__animated animate__fadeInUp">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="font-weight-bold m-0 uppercase tracking-widest text-primary"><i class="fas fa-warehouse mr-2"></i> Inventory Analytics</h6>
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
        // --- ANIMASI ANGKA JALAN rill ---
        const rollNumbers = document.querySelectorAll('.roll-number');
        rollNumbers.forEach(el => {
            let target = parseFloat(el.getAttribute('data-target'));
            let count = 0;
            let speed = target / 30;
            let timer = setInterval(() => {
                count += speed;
                if (count >= target) { el.innerText = Math.floor(target).toLocaleString(); clearInterval(timer); } 
                else { el.innerText = Math.floor(count).toLocaleString(); }
            }, 30);
        });

        @if(($mode ?? 'summary') == 'summary')
        const ctxMain = document.getElementById('mainStockChart').getContext('2d');
        new Chart(ctxMain, {
            type: 'bar',
            data: {
                labels: {!! json_encode($labels) !!},
                datasets: [
                    { label: 'ACTUAL_STOCK', data: {!! json_encode($actStockData) !!}, backgroundColor: '#4361ee', borderRadius: 12 },
                    { label: 'MINIMUM_LIMIT', data: {!! json_encode($minStockData) !!}, borderColor: '#f72585', borderWidth: 3, type: 'line', pointRadius: 0, tension: 0.4 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } } } }
        });
        @endif

        @if(($mode ?? '') == 'delivery')
        // Gauge rill
        const ctxGauge = document.getElementById('gaugeChart').getContext('2d');
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

        // Trend rill
        const ctxTrend = document.getElementById('deliveryTrendChart').getContext('2d');
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: {!! json_encode($deliveryTrend->pluck('date') ?? []) !!},
                datasets: [{
                    label: 'Units Dispatched',
                    data: {!! json_encode($deliveryTrend->pluck('total') ?? []) !!},
                    borderColor: '#4361ee', backgroundColor: 'rgba(67, 97, 238, 0.1)', fill: true, tension: 0.4, pointRadius: 4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
        @endif
    });
</script>
@endsection