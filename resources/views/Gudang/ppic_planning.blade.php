@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">

<style>
    :root {
        --ppic-steel: #4e73df; --ppic-success: #1cc88a; --ppic-danger: #e74a3b;
        --ppic-warning: #fd7e14; --ppic-dark: #1a1e2a; --ppic-bg: #f8f9fc;
        --glow-blue: 0 0 15px rgba(78, 115, 223, 0.2);
    }
    
    .main-terminal { background-color: var(--ppic-bg); min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }

    /* ✨ ANIMASI MASUK ✨ */
    .animasi-masuk { animation: fadeInUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) both; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); filter: blur(10px); }
        to { opacity: 1; transform: translateY(0); filter: blur(0); }
    }

    /* ✨ GLOW CARD DESIGN ✨ */
    .card-kpi { 
        background: #fff; border: none; border-radius: 16px; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.04); height: 100%; 
        transition: all 0.3s ease; overflow: hidden;
        border: 1px solid rgba(0,0,0,0.02);
    }
    .card-kpi:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(78, 115, 223, 0.1); }
    
    .card-header-kpi { 
        padding: 18px 20px; background: #fff; 
        border-bottom: 1px solid #f1f4f8; display: flex; align-items: center; 
    }
    .card-header-kpi h6 { 
        color: var(--ppic-dark); font-weight: 800; text-transform: uppercase; 
        font-size: 11px; letter-spacing: 1.5px; margin: 0; 
    }
    .card-header-kpi i { color: var(--ppic-steel); margin-right: 10px; font-size: 14px; }

    /* STATS STYLING */
    .stat-box { padding: 22px; border-bottom: 1px solid #f8f9fc; transition: 0.3s; }
    .stat-box:hover { background: #fbfcfe; }
    .stat-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
    .stat-value { font-size: 26px; font-weight: 900; color: var(--ppic-dark); font-family: 'Roboto Mono', monospace; }
    .stat-sub { font-size: 11px; color: #64748b; font-weight: 600; margin-left: 6px; }

    /* PROGRESS BAR GLOW */
    .progress-glow { height: 6px; background: #f1f5f9; border-radius: 10px; overflow: hidden; margin-top: 10px; }
    .progress-bar-glow { background: linear-gradient(90deg, var(--ppic-steel), #00d2ff); box-shadow: 0 0 10px rgba(78, 115, 223, 0.4); border-radius: 10px; }

    /* TABLE TERMINAL STYLE */
    .table-summary { font-size: 12px; }
    .table-summary thead th { 
        background: #f8fafc; color: var(--ppic-steel); font-weight: 800;
        text-transform: uppercase; border: none; padding: 15px; font-size: 10px;
    }
    .table-summary td { padding: 15px; vertical-align: middle; border-top: 1px solid #f1f5f9; font-weight: 600; color: #334155; }
</style>

<div class="main-terminal animasi-masuk">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="font-weight-bold text-dark mb-0 uppercase" style="letter-spacing: -1px;">PPIC_PLANNING_TERMINAL</h4>
            <span class="badge badge-primary py-1 px-3 rounded-pill" style="font-size: 9px; font-weight: 800;">REALTIME_SYNC: ACTIVE</span>
        </div>
        <div class="bg-white p-2 rounded-pill shadow-sm border px-4 d-none d-md-block">
            <span class="text-muted small font-weight-bold uppercase">System_Load: <span class="text-success">Optimal</span></span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card-kpi">
                <div class="card-header-kpi"><i class="fas fa-tachometer-alt"></i><h6>Overall Status</h6></div>
                <div class="card-body p-0">
                    <div class="stat-box">
                        <div class="stat-label">Achievement Rate</div>
                        <div class="stat-value">{{ $achievementRate }}%<span class="stat-sub">of target</span></div>
                        <div class="progress-glow"><div class="progress-bar-glow h-100" style="width: {{ $achievementRate }}%"></div></div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">OTD Performance</div>
                        <div class="stat-value">92<span class="stat-sub">% ontime</span></div>
                        <div class="progress-glow"><div class="progress-bar-glow h-100 bg-success" style="width: 92%; background: #1cc88a;"></div></div>
                    </div>
                    <div class="stat-box" style="border-bottom: none;">
                        <div class="stat-label">Material Availability</div>
                        <div class="stat-value">88<span class="stat-sub">% ready</span></div>
                        <div class="progress-glow"><div class="progress-bar-glow h-100 bg-warning" style="width: 88%; background: #f6c23e;"></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card-kpi">
                <div class="card-header-kpi"><i class="fas fa-chart-pie"></i><h6>Order Status Distribution</h6></div>
                <div class="card-body d-flex flex-column justify-content-center p-4">
                    <div style="height: 220px;"><canvas id="statusDonut"></canvas></div>
                    <div class="mt-4 d-flex justify-content-around">
                        <div class="text-center"><div class="small font-weight-bold" style="color: #858796;">WAITING</div><div class="h6 font-weight-bold mb-0">{{ $statusCount['waiting'] }}</div></div>
                        <div class="text-center"><div class="small font-weight-bold" style="color: #4e73df;">RUNNING</div><div class="h6 font-weight-bold mb-0">{{ $statusCount['running'] }}</div></div>
                        <div class="text-center"><div class="small font-weight-bold" style="color: #1cc88a;">COMPLETE</div><div class="h6 font-weight-bold mb-0">{{ $statusCount['completed'] }}</div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card-kpi">
                <div class="card-header-kpi"><i class="fas fa-chart-bar"></i><h6>Monthly Output Volume</h6></div>
                <div class="card-body p-4">
                    <div style="height: 250px;"><canvas id="monthlyBarChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card-kpi">
                <div class="card-header-kpi"><i class="fas fa-balance-scale"></i><h6>Target vs Actual Comparison</h6></div>
                <div class="card-body p-4">
                    <div style="height: 250px;"><canvas id="compareBar"></canvas></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card-kpi">
                <div class="card-header-kpi"><i class="fas fa-exclamation-triangle"></i><h6>Supply Chain Risks</h6></div>
                <div class="card-body p-4">
                    <div style="height: 220px;"><canvas id="riskChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card-kpi">
                <div class="card-header-kpi"><i class="fas fa-chart-line"></i><h6>Quarterly Success Trend</h6></div>
                <div class="card-body p-4">
                    <div style="height: 220px;"><canvas id="quarterlyChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card-kpi">
                <div class="card-header-kpi d-flex justify-content-between w-100">
                    <h6><i class="fas fa-list-alt"></i> Dashboard Ledger Data Summary</h6>
                    <span class="text-muted small font-weight-bold">LAST_UPDATE: {{ date('H:i:s') }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-summary text-center">
                            <thead>
                                <tr>
                                    <th>Status Entity</th><th>Orders</th><th>Target (Pcs)</th><th>Actual (Pcs)</th><th>Variance</th><th>Efficiency</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-success py-2 px-3 rounded-pill">Completed</span></td>
                                    <td class="font-weight-bold">{{ $statusCount['completed'] }}</td>
                                    <td>{{ number_format($plans->where('status','COMPLETED')->sum('plan_qty')) }}</td>
                                    <td class="text-success">{{ number_format($plans->where('status','COMPLETED')->sum('actual_qty')) }}</td>
                                    <td>0</td>
                                    <td><span class="text-success font-weight-bold">100%</span></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-primary py-2 px-3 rounded-pill">In Progress</span></td>
                                    <td class="font-weight-bold">{{ $statusCount['running'] }}</td>
                                    <td>{{ number_format($plans->where('status','RUNNING')->sum('plan_qty')) }}</td>
                                    <td class="text-primary">{{ number_format($plans->where('status','RUNNING')->sum('actual_qty')) }}</td>
                                    <td class="text-danger">-{{ number_format($plans->where('status','RUNNING')->sum('plan_qty') - $plans->where('status','RUNNING')->sum('actual_qty')) }}</td>
                                    <td><span class="text-danger font-weight-bold">{{ $achievementRate }}%</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#94a3b8';

    // 1. DONUT STATUS
    new Chart(document.getElementById('statusDonut'), {
        type: 'doughnut',
        data: {
            labels: ['Waiting', 'Running', 'Completed'],
            datasets: [{
                data: [{{ $statusCount['waiting'] }}, {{ $statusCount['running'] }}, {{ $statusCount['completed'] }}],
                backgroundColor: ['#f1f5f9', '#4e73df', '#1cc88a'],
                hoverBorderColor: "#fff",
                borderWidth: 4,
            }]
        },
        options: { cutout: '80%', maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 2. MONTHLY BAR
    new Chart(document.getElementById('monthlyBarChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($monthlyData['labels']) !!},
            datasets: [{
                label: 'Output',
                backgroundColor: '#4e73df',
                borderRadius: 8,
                data: {!! json_encode($monthlyData['actual']) !!}
            }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { display: false } }, x: { grid: { display: false } } } }
    });

    // 3. RISK CHART
    new Chart(document.getElementById('riskChart'), {
        type: 'bar',
        data: {
            labels: ['Critical', 'Warning', 'Safe'],
            datasets: [{
                data: [{{ $stockRisks['critical'] }}, {{ $stockRisks['warning'] }}, {{ $stockRisks['safe'] }}],
                backgroundColor: ['#e74a3b', '#f6c23e', '#1cc88a'],
                borderRadius: 6
            }]
        },
        options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 4. COMPARE TARGET VS ACTUAL
    new Chart(document.getElementById('compareBar'), {
        type: 'bar',
        data: {
            labels: ['Target', 'Actual'],
            datasets: [{
                data: [{{ $plans->sum('plan_qty') }}, {{ $plans->sum('actual_qty') }}],
                backgroundColor: ['#f1f5f9', '#4e73df'],
                borderRadius: 8
            }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 5. QUARTERLY TREND
    new Chart(document.getElementById('quarterlyChart'), {
        type: 'line',
        data: {
            labels: ['Q1', 'Q2', 'Q3', 'Q4'],
            datasets: [{
                data: [85, 92, 88, 95],
                borderColor: '#1cc88a',
                backgroundColor: 'rgba(28, 200, 138, 0.1)',
                fill: true, tension: 0.4, borderWidth: 3, pointRadius: 5
            }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });
});
</script>
@endsection
@endsection