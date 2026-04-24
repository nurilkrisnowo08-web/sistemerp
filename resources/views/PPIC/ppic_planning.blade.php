@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=JetBrains+Mono:wght@400;700&family=Inter:wght@300;400;700;900&display=swap" rel="stylesheet">

<style>
    :root {
        --bg-cyber: #0f172a;
        --card-bg: #1e293b;
        --accent-blue: #38bdf8;
        --accent-green: #22c55e;
        --accent-red: #ef4444;
        --text-dim: #94a3b8;
    }

    .main-terminal { 
        background-color: #f1f5f9; 
        min-height: 100vh; 
        padding: 1.5rem; 
        font-family: 'Inter', sans-serif;
    }

    .anim-slide-up { animation: slideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) both; }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .cyber-card {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
        transition: all 0.4s ease;
        overflow: hidden;
    }
    .cyber-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px -10px rgba(56, 189, 248, 0.2); }

    .metric-value { font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 2.2rem; line-height: 1; letter-spacing: -1px; }
    .metric-label { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--text-dim); letter-spacing: 1px; }

    .ticker-wrap {
        background: var(--bg-cyber);
        color: white;
        padding: 10px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        overflow: hidden;
    }
    .ticker-label { background: var(--accent-red); padding: 2px 10px; border-radius: 6px; font-size: 10px; font-weight: 900; margin-right: 15px; animation: pulse 1.5s infinite; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }

    .part-progress-container { margin-bottom: 15px; }
    .part-name-tag { font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 700; color: #1e293b; display: flex; justify-content: space-between; }
    .custom-progress { height: 10px; background: #e2e8f0; border-radius: 10px; overflow: hidden; margin-top: 5px; }
    .custom-bar { height: 100%; transition: width 1.5s ease-in-out; border-radius: 10px; }
    .status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
</style>

<div class="main-terminal">
    <div class="d-md-flex justify-content-between align-items-center mb-4 anim-slide-up">
        <div>
            <h1 class="font-weight-black text-dark mb-0" style="letter-spacing: -2px;">CORE_PRODUCTION_AI</h1>
            <div class="d-flex align-items-center">
                <span class="status-dot bg-success mr-2"></span>
                <span class="text-muted small font-weight-bold uppercase">System Status: Optimal / Last Sync: {{ date('H:i:s') }}</span>
            </div>
        </div>

        <div class="d-flex align-items-center mt-3 mt-md-0">
            <form action="{{ route('ppic.index') }}" method="GET" class="mr-3">
                <input type="date" name="date" class="form-control border-0 shadow-sm px-4 py-2" 
                       value="{{ $date }}" onchange="this.form.submit()" style="border-radius: 15px; font-weight: 800;">
            </form>
            
            <div class="cyber-card px-4 py-3 text-center bg-primary text-white border-0 shadow-lg">
                <div class="metric-label text-white-50">Global Efficiency</div>
                <div class="metric-value" style="font-size: 1.5rem;">{{ $achievementRate }}%</div>
            </div>
        </div>
    </div>

    <div class="ticker-wrap anim-slide-up" style="animation-delay: 0.1s;">
        <span class="ticker-label">ALERT</span>
        <marquee class="small font-weight-bold" scrollamount="5">
            @php $shortages = $plans->filter(function($p) { return $p->actual_qty < $p->plan_qty; }); @endphp
            @forelse($shortages as $p)
                • ATTENTION: Part [{{ $p->part_no }}] is UNDER TARGET by {{ $p->plan_qty - $p->actual_qty }} Pcs &nbsp;&nbsp;&nbsp;
            @empty
                • ALL SYSTEMS CLEAR: Production following plan targets perfectly.
            @endforelse
        </marquee>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4 anim-slide-up" style="animation-delay: 0.2s;">
            <div class="cyber-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="metric-label"><i class="fas fa-chart-bar mr-2"></i> Production Performance (Target vs Actual)</h6>
                    <span class="badge badge-light px-3 font-mono">LIVE_FEED</span>
                </div>
                <div style="height: 350px;">
                    <canvas id="mainCompareChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4 anim-slide-up" style="animation-delay: 0.3s;">
            <div class="cyber-card p-4 h-100">
                <h6 class="metric-label text-center mb-4">Job Execution Distribution</h6>
                <div style="height: 250px;">
                    <canvas id="jobStatusChart"></canvas>
                </div>
                <div class="row mt-4 text-center">
                    <div class="col-4">
                        <div class="metric-label">Wait</div>
                        <div class="h4 font-weight-bold text-dark">{{ $statusCount['waiting'] }}</div>
                    </div>
                    <div class="col-4">
                        <div class="metric-label text-primary">Running</div>
                        <div class="h4 font-weight-bold text-primary">{{ $statusCount['running'] }}</div>
                    </div>
                    <div class="col-4">
                        <div class="metric-label text-success">Done</div>
                        <div class="h4 font-weight-bold text-success">{{ $statusCount['completed'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✨ QUALITY TRENDS (OK VS NG) ✨ --}}
    <div class="row">
        <div class="col-lg-6 mb-4 anim-slide-up" style="animation-delay: 0.4s;">
            <div class="cyber-card p-4 h-100">
                <h6 class="metric-label mb-4 text-danger"><i class="fas fa-shield-virus mr-2"></i> Daily Quality Stability (OK vs NG)</h6>
                <div style="height: 300px;">
                    <canvas id="dailyQualityChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4 anim-slide-up" style="animation-delay: 0.5s;">
            <div class="cyber-card p-4 h-100">
                <h6 class="metric-label mb-4 text-warning"><i class="fas fa-chart-line mr-2"></i> Monthly Quality Stability (OK vs NG)</h6>
                <div style="height: 300px;">
                    <canvas id="monthlyQualityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4 anim-slide-up" style="animation-delay: 0.6s;">
            <div class="cyber-card p-4 h-100">
                <h6 class="metric-label mb-4">Active Production Items</h6>
                <div class="scroll-no-bar" style="max-height: 400px; overflow-y: auto;">
                    @foreach($plans as $p)
                        @php $percent = ($p->actual_qty / ($p->plan_qty ?: 1)) * 100; @endphp
                        <div class="part-progress-container">
                            <div class="part-name-tag">
                                <span>{{ $p->part_no }}</span>
                                <span class="{{ $percent >= 100 ? 'text-success' : 'text-primary' }} font-mono">
                                    {{ number_format($p->actual_qty) }} / {{ number_format($p->plan_qty) }} Pcs
                                </span>
                            </div>
                            <div class="custom-progress">
                                <div class="custom-bar {{ $percent >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                     style="width: {{ min($percent, 100) }}%; box-shadow: 0 0 10px {{ $percent >= 100 ? 'rgba(34, 197, 94, 0.4)' : 'rgba(56, 189, 248, 0.4)' }}">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4 anim-slide-up" style="animation-delay: 0.7s;">
            <div class="cyber-card p-4 h-100">
                <h6 class="metric-label mb-4">Production Output Stability Trend</h6>
                <div style="height: 350px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPTS SECTION --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    Chart.defaults.font.family = "'JetBrains Mono', monospace";
    Chart.defaults.color = '#64748b';

    // 1. Main Compare Chart
    const ctxCompare = document.getElementById('mainCompareChart').getContext('2d');
    new Chart(ctxCompare, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [
                { label: 'ACTUAL', data: {!! json_encode($chartActuals) !!}, backgroundColor: '#38bdf8', borderRadius: 8, barThickness: 15 },
                { label: 'TARGET', data: {!! json_encode($chartTargets) !!}, backgroundColor: '#f1f5f9', borderRadius: 8, barThickness: 15 }
            ]
        },
        options: {
            indexAxis: 'y',
            maintainAspectRatio: false,
            animation: { duration: 2000, easing: 'easeOutQuart' },
            plugins: { legend: { position: 'top', align: 'end' } },
            scales: { x: { grid: { display: false } }, y: { grid: { display: false } } }
        }
    });

    // 2. Job Status Donut
    const ctxStatus = document.getElementById('jobStatusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Wait', 'Run', 'Done'],
            datasets: [{
                data: [{{ $statusCount['waiting'] }}, {{ $statusCount['running'] }}, {{ $statusCount['completed'] }}],
                backgroundColor: ['#f1f5f9', '#38bdf8', '#22c55e'],
                hoverOffset: 20, borderWidth: 0, cutout: '85%'
            }]
        },
        options: { maintainAspectRatio: false, animation: { animateScale: true, duration: 2000 }, plugins: { legend: { display: false } } }
    });

    // 3. DAILY QUALITY CHART (OK VS NG)
    const ctxDailyQ = document.getElementById('dailyQualityChart').getContext('2d');
    new Chart(ctxDailyQ, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyLabels) !!},
            datasets: [
                { label: 'OK', data: {!! json_encode($dailyOk) !!}, borderColor: '#22c55e', backgroundColor: 'rgba(34, 197, 94, 0.1)', fill: true, tension: 0.4 },
                { label: 'NG', data: {!! json_encode($dailyNg) !!}, borderColor: '#ef4444', backgroundColor: 'rgba(239, 68, 68, 0.1)', fill: true, tension: 0.4 }
            ]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
    });

    // 4. MONTHLY QUALITY CHART (OK VS NG)
    const ctxMonthlyQ = document.getElementById('monthlyQualityChart').getContext('2d');
    new Chart(ctxMonthlyQ, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyLabels) !!},
            datasets: [
                { label: 'OK', data: {!! json_encode($monthlyOk) !!}, borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', fill: true, tension: 0.4 },
                { label: 'NG', data: {!! json_encode($monthlyNg) !!}, borderColor: '#f59e0b', backgroundColor: 'rgba(245, 158, 11, 0.1)', fill: true, tension: 0.4 }
            ]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
    });

    // 5. Stability Trend
    const ctxTrend = document.getElementById('trendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyLabels) !!},
            datasets: [{
                label: 'Output',
                data: {!! json_encode($monthlyOk) !!},
                borderColor: '#38bdf8', borderWidth: 4, fill: false, tension: 0.4, pointRadius: 6
            }]
        },
        options: { maintainAspectRatio: false, animation: { duration: 3000 }, plugins: { legend: { display: false } } }
    });
</script>
@endsection