@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">

<style>
    :root {
        --ppic-steel: #4e73df; --ppic-success: #1cc88a; --ppic-danger: #e74a3b;
        --ppic-warning: #fd7e14; --ppic-dark: #1a1e2a; --ppic-bg: #f8f9fc;
    }
    
    .main-terminal { background-color: var(--ppic-bg); min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }

    /* ✨ ANIMASI MASUK ✨ */
    .animasi-masuk { animation: fadeInUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) both; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); filter: blur(10px); }
        to { opacity: 1; transform: translateY(0); filter: blur(0); }
    }

    .card-kpi { 
        background: #fff; border: none; border-radius: 16px; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.04); height: 100%; 
        transition: all 0.3s ease; border: 1px solid rgba(0,0,0,0.02);
    }
    .card-kpi:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(78, 115, 223, 0.1); }
    
    .card-header-kpi { 
        padding: 18px 20px; background: #fff; border-bottom: 1px solid #f1f4f8; 
        display: flex; align-items: center; justify-content: space-between;
    }
    .card-header-kpi h6 { color: var(--ppic-dark); font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 1.5px; margin: 0; }
    .card-header-kpi i { color: var(--ppic-steel); margin-right: 10px; }

    .stat-box { padding: 22px; border-bottom: 1px solid #f8f9fc; }
    .stat-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px; }
    .stat-value { font-size: 26px; font-weight: 900; color: var(--ppic-dark); font-family: 'Roboto Mono', monospace; }

    .progress-glow { height: 6px; background: #f1f5f9; border-radius: 10px; overflow: hidden; margin-top: 10px; }
    .progress-bar-glow { background: linear-gradient(90deg, var(--ppic-steel), #00d2ff); box-shadow: 0 0 10px rgba(78, 115, 223, 0.4); border-radius: 10px; }

    .table-summary thead th { 
        background: #f8fafc; color: var(--ppic-steel); font-weight: 800;
        text-transform: uppercase; border: none; padding: 15px; font-size: 10px;
    }
    .table-summary td { padding: 15px; border-top: 1px solid #f1f5f9; font-weight: 600; font-size: 12px; }
</style>

<div class="main-terminal animasi-masuk">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="font-weight-bold text-dark mb-0 uppercase" style="letter-spacing: -1px;">PPIC_PLANNING_TERMINAL</h4>
            <span class="badge badge-primary py-1 px-3 rounded-pill" style="font-size: 9px; font-weight: 800;">REALTIME_SYNC: ACTIVE</span>
        </div>
    </div>

    <div class="row">
        {{-- KARTU KIRI: OVERALL STATUS --}}
        <div class="col-lg-4 mb-4">
            <div class="card-kpi">
                <div class="card-header-kpi"><h6><i class="fas fa-tachometer-alt"></i> Overall Status</h6></div>
                <div class="card-body p-0">
                    <div class="stat-box">
                        <div class="stat-label">Achievement Rate</div>
                        <div class="stat-value text-primary">{{ $achievementRate }}%</div>
                        <div class="progress-glow"><div class="progress-bar-glow h-100" style="width: {{ min($achievementRate, 100) }}%"></div></div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Production Efficiency</div>
                        <div class="stat-value">{{ $achievementRate > 90 ? 'High' : 'Normal' }}</div>
                        <div class="progress-glow"><div class="progress-bar-glow h-100 bg-success" style="width: 100%; background: #1cc88a;"></div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU TENGAH: DONUT CHART --}}
        <div class="col-lg-4 mb-4">
            <div class="card-kpi text-center">
                <div class="card-header-kpi"><h6><i class="fas fa-chart-pie"></i> Order Status Distribution</h6></div>
                <div class="card-body p-4">
                    <div style="height: 200px;"><canvas id="statusDonut"></canvas></div>
                    <div class="mt-4 d-flex justify-content-around">
                        <div><div class="small font-weight-bold text-muted">WAITING</div><div class="h6 font-weight-bold">{{ $statusCount['waiting'] }}</div></div>
                        <div><div class="small font-weight-bold text-primary">RUNNING</div><div class="h6 font-weight-bold">{{ $statusCount['running'] }}</div></div>
                        <div><div class="small font-weight-bold text-success">DONE</div><div class="h6 font-weight-bold">{{ $statusCount['completed'] }}</div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU KANAN: MONTHLY OUTPUT --}}
        <div class="col-lg-4 mb-4">
            <div class="card-kpi">
                <div class="card-header-kpi"><h6><i class="fas fa-chart-bar"></i> Monthly Output Volume</h6></div>
                <div class="card-body p-4">
                    <div style="height: 250px;"><canvas id="monthlyBarChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- COMPARE CHART --}}
        <div class="col-lg-8 mb-4">
            <div class="card-kpi">
                <div class="card-header-kpi"><h6><i class="fas fa-balance-scale"></i> Real-time Target vs Actual Comparison</h6></div>
                <div class="card-body p-4">
                    <div style="height: 300px;"><canvas id="compareBar"></canvas></div>
                </div>
            </div>
        </div>

        {{-- RISK CHART --}}
        <div class="col-lg-4 mb-4">
            <div class="card-kpi">
                <div class="card-header-kpi"><h6><i class="fas fa-exclamation-triangle"></i> Supply Chain Risks</h6></div>
                <div class="card-body p-4">
                    <div style="height: 300px;"><canvas id="riskChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    {{-- LEDGER DATA SUMMARY --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card-kpi">
                <div class="card-header-kpi">
                    <h6><i class="fas fa-list-alt"></i> Dashboard Ledger Data Summary</h6>
                    <span class="badge badge-light border text-muted">SYNC_TIME: {{ date('H:i:s') }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-summary text-center">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th class="text-left">Part Identification</th>
                                    <th>Target (Pcs)</th>
                                    <th>Actual (Pcs)</th>
                                    <th>Variance</th>
                                    <th>Efficiency</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($plans as $plan)
                                <tr>
                                    <td>
                                        @if($plan->actual_qty <= 0)
                                            <span class="badge badge-light border text-muted px-3">Waiting</span>
                                        @elseif($plan->actual_qty < $plan->plan_qty)
                                            <span class="badge badge-primary px-3">In Progress</span>
                                        @else
                                            <span class="badge badge-success px-3">Completed</span>
                                        @endif
                                    </td>
                                    <td class="text-left font-weight-bold">{{ $plan->part_no }}</td>
                                    <td class="font-mono">{{ number_format($plan->plan_qty) }}</td>
                                    <td class="text-primary font-weight-bold font-mono">{{ number_format($plan->actual_qty) }}</td>
                                    <td class="{{ ($plan->plan_qty - $plan->actual_qty) > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($plan->actual_qty - $plan->plan_qty) }}
                                    </td>
                                    <td>
                                        @php $eff = ($plan->actual_qty / ($plan->plan_qty ?: 1)) * 100; @endphp
                                        <div class="font-weight-bold {{ $eff >= 100 ? 'text-success' : 'text-danger' }}">
                                            {{ round($eff, 1) }}%
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
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
                borderWidth: 5,
                hoverOffset: 10
            }]
        },
        options: { cutout: '85%', maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 2. MONTHLY BAR (SINKRON DENGAN JUNI)
    new Chart(document.getElementById('monthlyBarChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($monthlyData['labels']) !!},
            datasets: [{
                label: 'Actual Output',
                backgroundColor: '#4e73df',
                borderRadius: 5,
                data: {!! json_encode($monthlyData['actual']) !!}
            }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 3. RISK CHART (STOCK RM)
    new Chart(document.getElementById('riskChart'), {
        type: 'bar',
        data: {
            labels: ['Critical', 'Warning', 'Safe'],
            datasets: [{
                data: [{{ $stockRisks['critical'] }}, {{ $stockRisks['warning'] }}, {{ $stockRisks['safe'] }}],
                backgroundColor: ['#e74a3b', '#f6c23e', '#1cc88a'],
                borderRadius: 5
            }]
        },
        options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 4. COMPARE TARGET VS ACTUAL (MENGGUNAKAN DATA AGGREGAT)
    new Chart(document.getElementById('compareBar'), {
        type: 'bar',
        data: {
            labels: ['Aggregated Target', 'Real-time Production'],
            datasets: [{
                data: [
                    {{ $plans->sum('plan_qty') }}, 
                    {{ $plans->sum('actual_qty') }}
                ],
                backgroundColor: ['#f1f5f9', '#4e73df'],
                borderRadius: 10,
                barThickness: 60
            }]
        },
        options: { 
            maintainAspectRatio: false, 
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { display: false } } }
        }
    });

    // 5. TREND DUMMY (DIPERTAHANKAN)
    new Chart(document.getElementById('quarterlyChart'), {
        type: 'line',
        data: {
            labels: ['Q1', 'Q2', 'Q3', 'Q4'],
            datasets: [{
                data: [85, 92, 88, 95],
                borderColor: '#1cc88a',
                fill: true, tension: 0.4, borderWidth: 3, pointRadius: 5
            }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });
});
</script>
@endsection
@endsection