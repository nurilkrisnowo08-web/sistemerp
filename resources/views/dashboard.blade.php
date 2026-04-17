@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --ind-navy: #0f172a; --ind-blue: #4361ee; --ind-cyan: #4cc9f0;
        --ind-danger: #f72585; --ind-success: #10b981; --ind-warning: #f8961e;
        --ind-bg: #f1f5f9;
    }
    
    body { background-color: var(--ind-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--ind-navy); }

    /* ✨ UI COMPONENTS RILL */
    .tactical-card { 
        background: #ffffff; border: none; border-radius: 24px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03); 
        transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; overflow: hidden; border: 1px solid #e2e8f0;
    }
    .tactical-card:hover { transform: translateY(-8px); border-color: var(--ind-blue); box-shadow: 0 20px 40px rgba(67, 97, 238, 0.1); }
    
    .label-tech { font-size: 10px; font-weight: 800; text-transform: uppercase; color: #94a3b8; letter-spacing: 2px; }
    .val-tech { font-family: 'Orbitron', sans-serif; font-size: 34px; font-weight: 900; letter-spacing: -1px; line-height: 1.2; }

    /* 🛰️ SYSTEM NAV rill */
    .quick-nav-btn {
        background: var(--ind-navy); color: white !important; border-radius: 50px;
        padding: 12px 28px; font-weight: 800; font-family: 'Orbitron'; font-size: 11px;
        border: none; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2); letter-spacing: 1px;
    }
    .dropdown-menu { border-radius: 20px; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.12); padding: 10px; border: 1px solid #f1f5f9; }
    .dropdown-item { border-radius: 12px; padding: 14px; font-weight: 700; transition: 0.3s; font-size: 13px; }
    .dropdown-item:hover { background: var(--ind-blue); color: white; transform: translateX(5px); }

    /* Performance Gauge rill */
    .gauge-wrapper { position: relative; width: 220px; height: 110px; margin: 0 auto; overflow: hidden; }
    .gauge-center-text { position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); font-family: 'Orbitron'; font-size: 32px; font-weight: 900; color: var(--ind-blue); }

    .laser-line { height: 2px; background: linear-gradient(90deg, transparent, var(--ind-blue), transparent); width: 100%; position: absolute; top: 0; animation: laserSweep 3s linear infinite; }
    @keyframes laserSweep { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }

    .table-modern thead th { background: #f8fafc; border: none; font-size: 10px; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px; padding: 15px; }
    .table-modern td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 13px; font-family: 'JetBrains Mono'; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
    {{-- 🛸 1. HEADER HUB CENTER rill --}}
    <div class="tactical-card p-4 mb-4">
        <div class="laser-line"></div>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h2 mb-0 font-weight-extrabold uppercase" style="letter-spacing: -2px;">COMMAND <span class="text-primary">CENTER v2.0</span></h1>
                <div class="d-flex align-items-center mt-1">
                    <span class="badge badge-primary px-3 py-1 mr-3" style="font-family: 'Orbitron'; font-size: 10px;">MODE: {{ strtoupper($mode) }}</span>
                    <p class="text-muted small font-weight-bold mb-0 uppercase tracking-widest">
                        <i class="fas fa-microchip mr-1 text-primary"></i> SYSTEM_OPS: <span class="text-success">ONLINE</span> // L-TIME: {{ date('H:i') }}
                    </p>
                </div>
            </div>
            
            <div class="dropdown">
                <button class="quick-nav-btn dropdown-toggle shadow-lg" data-toggle="dropdown">
                    <i class="fas fa-satellite-dish mr-2"></i> QUICK_NAV
                </button>
                <div class="dropdown-menu dropdown-menu-right animate__animated animate__fadeIn">
                    <h6 class="dropdown-header font-weight-bold text-muted small uppercase">Analytical Switch</h6>
                    <a class="dropdown-item {{ $mode == 'summary' ? 'bg-primary text-white' : '' }}" href="{{ route('dashboard', ['mode' => 'summary']) }}">
                        <i class="fas fa-chart-pie mr-2"></i> INVENTORY_MONITOR
                    </a>
                    <a class="dropdown-item {{ $mode == 'delivery' ? 'bg-primary text-white' : '' }}" href="{{ route('dashboard', ['mode' => 'delivery']) }}">
                        <i class="fas fa-truck-loading mr-2"></i> DELIVERY_PERFORMANCE
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('fg.index') }}"><i class="fas fa-box mr-2"></i> WAREHOUSE_FG</a>
                </div>
            </div>
        </div>
    </div>

    {{-- 📊 2. TOP METRICS (Standard) rill --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="tactical-card p-4 h-100" style="border-bottom: 5px solid var(--ind-blue);">
                <div class="label-tech">Stock_Assets</div>
                <div class="val-tech text-dark">{{ number_format($totalParts) }}</div>
                <small class="font-weight-bold text-muted">Parts Registered rill</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="tactical-card p-4 h-100" style="border-bottom: 5px solid var(--ind-danger);">
                <div class="label-tech text-danger">Crit_Shortage</div>
                <div class="val-tech text-danger">{{ number_format($critCount) }}</div>
                <small class="font-weight-bold text-danger alert-pulse">Action Required</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="tactical-card p-4 h-100" style="border-bottom: 5px solid var(--ind-success);">
                <div class="label-tech text-success">Finished_Goods</div>
                <div class="val-tech text-success">+{{ number_format($todayProd) }}</div>
                <small class="font-weight-bold text-muted">Today Output</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="tactical-card p-4 h-100" style="border-bottom: 5px solid var(--ind-warning);">
                <div class="label-tech text-warning">Dispatched</div>
                <div class="val-tech text-warning">-{{ number_format($todayDelv) }}</div>
                <small class="font-weight-bold text-muted">Manifest Released</small>
            </div>
        </div>
    </div>

    {{-- --- 📦 VIEW: MODE DELIVERY (RILL GANTI DATA) --- --}}
    @if($mode == 'delivery')
    <div class="row animate__animated animate__zoomIn">
        <div class="col-md-4 mb-4">
            <div class="tactical-card p-4 h-100 text-center">
                <h6 class="font-weight-bold uppercase mb-4 tracking-widest text-primary">Fulfillment Performance</h6>
                <div class="gauge-wrapper">
                    <canvas id="perfGauge"></canvas>
                    <div class="gauge-center-text">{{ $deliveryPerformance }}%</div>
                </div>
                <p class="text-muted small font-weight-bold mt-4">Calculated from total manifests vs PO Target rill</p>
            </div>
        </div>
        
        <div class="col-md-8 mb-4">
            <div class="tactical-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="font-weight-bold uppercase tracking-widest text-primary">30-Day Logistics Flow</h6>
                    <span class="badge badge-light border">Real-Time Data</span>
                </div>
                <div style="height: 280px;"><canvas id="deliveryTrend"></canvas></div>
            </div>
        </div>

        <div class="col-12">
            <div class="tactical-card overflow-hidden">
                <div class="p-4 bg-dark text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold uppercase tracking-widest">Customer Delivery Ledger</h6>
                    <span class="badge badge-primary px-3">{{ count($customerShipments) }} Active Entities</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-modern mb-0 text-center">
                        <thead>
                            <tr>
                                <th class="text-left pl-5">Client Entity</th>
                                <th>Shipment Volume (PCS)</th>
                                <th>Manifest Issued (SJ)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customerShipments as $cs)
                            <tr>
                                <td class="text-left pl-5">
                                    <div class="font-weight-bold text-primary">{{ $cs->customer_name }}</div>
                                    <small class="text-muted">Registered Partner</small>
                                </td>
                                <td class="h5 font-weight-bold text-danger">-{{ number_format($cs->total_qty) }}</td>
                                <td><span class="badge badge-light border px-3 py-2">{{ $cs->total_sj }} MANIFESTS</span></td>
                                <td><span class="text-success small font-weight-bold"><i class="fas fa-check-circle mr-1"></i> OPTIMIZED</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="py-5 text-muted">No analytics data available for this period rill.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- --- 📊 VIEW: MODE SUMMARY (STOCK MONITOR) --- --}}
    @if($mode == 'summary')
    <div class="row animate__animated animate__fadeInUp">
        <div class="col-lg-8 mb-4">
            <div class="tactical-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="font-weight-bold m-0 uppercase tracking-widest text-primary"><i class="fas fa-warehouse mr-2"></i> Inventory Balance Monitoring</h6>
                    <form action="{{ route('dashboard') }}" method="GET">
                        <input type="hidden" name="mode" value="summary">
                        <select name="customer" class="btn btn-light btn-sm rounded-pill font-weight-bold px-4 border shadow-sm" onchange="this.form.submit()">
                            <option value="">-- ALL CLIENTS --</option>
                            @foreach($customersList as $cust)
                                <option value="{{ $cust }}" {{ $selectedCustomer == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div style="height: 380px;"><canvas id="mainStockChart"></canvas></div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="tactical-card h-100 p-0 overflow-hidden">
                <div class="p-4 bg-danger text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold uppercase small tracking-widest">Immediate Shortage</h6>
                    <i class="fas fa-bolt animate-pulse"></i>
                </div>
                <div class="table-responsive" style="max-height: 420px;">
                    <table class="table table-modern mb-0">
                        <tbody>
                            @forelse($permintaanStok as $p)
                            <tr>
                                <td class="pl-4">
                                    <div class="font-weight-bold">{{ $p->part_no }}</div>
                                    <small class="text-muted">{{ $p->customer_code ?? 'ASALTA' }}</small>
                                </td>
                                <td class="text-right pr-4">
                                    <div class="text-danger font-weight-bold">{{ number_format($p->actual_stock) }}</div>
                                    <div class="text-muted" style="font-size: 9px;">MIN: {{ $p->min_stock_pcs }}</div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="py-5 text-center text-muted">Inventory Levels Secure rill.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- CHART: MODE SUMMARY rill ---
        @if($mode == 'summary')
            const ctxMain = document.getElementById('mainStockChart').getContext('2d');
            new Chart(ctxMain, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($labels) !!},
                    datasets: [
                        { label: 'ACTUAL', data: {!! json_encode($actStockData) !!}, backgroundColor: '#4361ee', borderRadius: 12 },
                        { label: 'MINIMUM', data: {!! json_encode($minStockData) !!}, borderColor: '#f72585', borderWidth: 3, type: 'line', pointRadius: 0, tension: 0.4 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        @endif

        // --- CHART: MODE DELIVERY rill ---
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

            // Delivery Trend Line rill
            new Chart(document.getElementById('deliveryTrend').getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($deliveryTrend->pluck('date')) !!},
                    datasets: [{
                        label: 'Units Out',
                        data: {!! json_encode($deliveryTrend->pluck('total')) !!},
                        borderColor: '#4361ee', backgroundColor: 'rgba(67, 97, 238, 0.1)', fill: true, tension: 0.4, pointRadius: 4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        @endif
    });
</script>
@endsection