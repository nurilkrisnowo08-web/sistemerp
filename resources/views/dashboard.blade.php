@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Roboto+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">

<style>
    :root {
        --ind-navy: #0f172a; --ind-blue: #3b82f6; --ind-cyan: #06b6d4;
        --ind-success: #10b981; --ind-danger: #ef4444; --ind-warning: #f59e0b;
        --ind-bg: #f8fafc;
    }
    .main-terminal { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--ind-bg); min-height: 100vh; padding: 1.5rem; }

    /* ✨ ANIMASI LASER */
    @keyframes laserFlow { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    .laser-line { height: 1px; background: linear-gradient(90deg, transparent, var(--ind-cyan), transparent); width: 100%; position: absolute; animation: laserFlow 2s linear infinite; }
    
    /* 🚨 TICKER */
    .ticker-wrap { background: #fff; border: 1px solid #fee2e2; border-radius: 12px; overflow: hidden; padding: 10px 0; margin-bottom: 2rem; }
    .ticker-move { display: flex; width: max-content; animation: ticker 30s linear infinite; }
    .ticker-item { padding: 0 30px; font-weight: 800; font-size: 11px; color: var(--ind-danger); display: flex; align-items: center; text-transform: uppercase; }
    @keyframes ticker { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }

    /* 📊 ANALYSIS PANEL rill */
    .analysis-panel { 
        display: none; background: #fff; border-radius: 24px; padding: 25px; 
        margin-bottom: 20px; border: 1px solid #e2e8f0; box-shadow: 0 15px 40px rgba(0,0,0,0.05); 
        animation: fadeInDown 0.4s ease-out;
    }

    .tactical-card { 
        background: #fff; border: none; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); 
        transition: all 0.4s; position: relative; overflow: hidden; cursor: pointer;
    }
    .tactical-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(59, 130, 246, 0.1); }
    
    .btn-analysis {
        font-size: 9px; font-weight: 800; padding: 4px 12px; border-radius: 50px;
        background: var(--ind-navy); color: #fff; border: none; transition: 0.3s;
    }
    .btn-analysis:hover { background: var(--ind-blue); transform: scale(1.1); }

    .stat-value { font-family: 'Roboto Mono', monospace; font-size: 32px; font-weight: 800; }
    
    .table-analysis { font-size: 11px; }
    .table-analysis th { text-transform: uppercase; color: #94a3b8; letter-spacing: 1px; border: none; }
</style>

<div class="container-fluid main-terminal text-dark">
    
    {{-- 🛸 HEADER HUD --}}
    <div class="bg-white p-4 mb-4 shadow-sm border-bottom position-relative overflow-hidden" style="border-radius: 24px;">
        <div class="laser-line" style="top: 0;"></div>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 font-weight-extrabold uppercase">Intelligence <span class="text-primary">Command Center</span></h1>
                <p class="text-muted font-weight-bold mb-0 small uppercase">L-TIME: {{ date('H:i:s') }} // ANALYTICS_READY_rill</p>
            </div>
            <div class="dropdown">
                <button class="btn btn-dark rounded-pill px-4 font-weight-bold dropdown-toggle" data-toggle="dropdown">QUICK_NAV</button>
                <div class="dropdown-menu dropdown-menu-right border-0 shadow-lg" style="border-radius: 15px;">
                    <a class="dropdown-item font-weight-bold py-2" href="{{ route('rm.store') }}">RAW_MATERIAL</a>
                    <a class="dropdown-item font-weight-bold py-2" href="{{ route('welding.index') }}">WELDING_WIP</a>
                    <a class="dropdown-item font-weight-bold py-2" href="{{ route('fg.index') }}">FINISHED_GOODS</a>
                </div>
            </div>
        </div>
    </div>

    {{-- 🚨 LIVE TICKER --}}
    @if(count($permintaanStok) > 0)
    <div class="ticker-wrap"><div class="ticker-move">
        @foreach($permintaanStok->merge($permintaanStok) as $p)
        <div class="ticker-item"><i class="fas fa-exclamation-circle mr-2"></i> CRITICAL: {{ $p->part_no }} - {{ $p->part_name }} (STOK: {{ $p->actual_stock }})</div>
        @endforeach
    </div></div>
    @endif

    {{-- 🚀 TACTICAL SUMMARY CARDS --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="tactical-card p-4 h-100" onclick="toggleAnalysis('inventory')">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-label uppercase font-weight-bold small text-muted">Assets</div>
                    <button class="btn-analysis">ANALYSIS</button>
                </div>
                <div class="stat-value text-dark roll-number" data-target="{{ $totalParts }}">0</div>
                <div class="small font-weight-bold text-muted uppercase mt-2">Parts Registered</div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="tactical-card p-4 h-100" onclick="toggleAnalysis('critical')">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-label uppercase font-weight-bold small text-danger">Shortage</div>
                    <button class="btn-analysis">ANALYSIS</button>
                </div>
                <div class="stat-value text-danger roll-number" data-target="{{ $critCount }}">0</div>
                <div class="small font-weight-bold text-danger uppercase mt-2 alert-pulse">Need Production</div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="tactical-card p-4 h-100" onclick="toggleAnalysis('production')">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-label uppercase font-weight-bold small text-success">Finished</div>
                    <button class="btn-analysis">ANALYSIS</button>
                </div>
                <div class="stat-value text-success">+<span class="roll-number" data-target="{{ $todayProd }}">0</span></div>
                <div class="small font-weight-bold text-muted uppercase mt-2">Units Today</div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="tactical-card p-4 h-100" onclick="toggleAnalysis('delivery')">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="stat-label uppercase font-weight-bold small text-warning">Dispatched</div>
                    <button class="btn-analysis">ANALYSIS</button>
                </div>
                <div class="stat-value text-warning">-<span class="roll-number" data-target="{{ $todayDelv }}">0</span></div>
                <div class="small font-weight-bold text-muted uppercase mt-2">Shipment Today</div>
            </div>
        </div>
    </div>

    {{-- 📊 ANALYSIS DROP-PANELS rill --}}
    
    <div id="panel-delivery" class="analysis-panel animate__animated">
        <div class="row">
            <div class="col-md-7 border-right">
                <h6 class="font-weight-bold mb-4 uppercase tracking-widest"><i class="fas fa-chart-line mr-2 text-warning"></i> Delivery Performance Trend</h6>
                <div style="height: 250px;"><canvas id="deliveryChart"></canvas></div>
            </div>
            <div class="col-md-5">
                <h6 class="font-weight-bold mb-3 uppercase tracking-widest"><i class="fas fa-shipping-fast mr-2 text-warning"></i> Customer Dispatch List</h6>
                <div class="table-responsive" style="max-height: 250px;">
                    <table class="table table-analysis">
                        <thead><tr><th>Client</th><th>Part</th><th class="text-right">Qty</th></tr></thead>
                        <tbody>
                            {{-- Contoh looping data dari controller rill --}}
                            @forelse($deliveryToday as $d)
                            <tr>
                                <td class="font-weight-bold">{{ $d->customer_name }}</td>
                                <td>{{ $d->part_no }}</td>
                                <td class="text-right text-danger font-weight-bold">-{{ number_format($d->qty_delivery) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">No dispatches recorded today.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="panel-inventory" class="analysis-panel animate__animated">
        <h6 class="font-weight-bold mb-4 uppercase"><i class="fas fa-boxes mr-2 text-primary"></i> Inventory Distribution by Customer</h6>
        <div style="height: 250px;"><canvas id="inventoryPieChart"></canvas></div>
    </div>

    {{-- 📊 MAIN STOCK CHART --}}
    <div class="tactical-card p-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="font-weight-bold m-0 uppercase tracking-widest"><i class="fas fa-warehouse mr-2 text-primary"></i> Live Inventory Focus</h6>
            <form action="{{ route('dashboard') }}" method="GET" id="chartFilter">
                <select name="customer" class="btn btn-light btn-sm rounded-pill font-weight-bold px-3 border shadow-sm" onchange="this.form.submit()">
                    <option value="">-- ALL CLIENTS --</option>
                    @foreach($customersList as $cust)
                        <option value="{{ $cust }}" {{ $selectedCustomer == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div style="height: 350px;"><canvas id="dbChart"></canvas></div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 💡 Toggle Analysis Function rill
    function toggleAnalysis(type) {
        // Hide all panels first
        document.querySelectorAll('.analysis-panel').forEach(p => {
            if(p.id !== 'panel-' + type) p.style.display = 'none';
        });
        
        const target = document.getElementById('panel-' + type);
        if(target.style.display === 'block') {
            target.style.display = 'none';
        } else {
            target.style.display = 'block';
            if(type === 'delivery') initDeliveryChart();
            if(type === 'inventory') initInventoryChart();
        }
    }

    // 📈 Chart Initialization functions rill
    function initDeliveryChart() {
        const ctx = document.getElementById('deliveryChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($delvDates ?? []) !!}, // Hari ke 1 - 30 rill
                datasets: [{
                    label: 'Units Dispatched',
                    data: {!! json_encode($delvQtys ?? []) !!},
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    }

    function initInventoryChart() {
        const ctx = document.getElementById('inventoryPieChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($labels ?? []) !!},
                datasets: [{
                    data: {!! json_encode($actStockData ?? []) !!},
                    backgroundColor: ['#3b82f6', '#10b981', '#f43f5e', '#f59e0b', '#8b5cf6']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%' }
        });
    }

    // Existing Scripts (Roll Numbers & Main Chart)
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

        const ctxMain = document.getElementById('dbChart').getContext('2d');
        new Chart(ctxMain, {
            type: 'bar',
            data: {
                labels: {!! json_encode($labels) !!},
                datasets: [
                    { label: 'ACTUAL', data: {!! json_encode($actStockData) !!}, backgroundColor: 'rgba(67, 97, 238, 0.8)', borderRadius: 12 },
                    { label: 'MIN', data: {!! json_encode($minStockData) !!}, borderColor: '#ef4444', type: 'line', borderWidth: 2, pointRadius: 0 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
    });
</script>
@endsection