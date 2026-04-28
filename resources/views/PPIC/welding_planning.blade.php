@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root { 
        --ind-blue: #4361ee; 
        --ind-navy: #0f172a; 
        --ind-emerald: #10b981;
        --ind-rose: #ef4444;
        --glass: rgba(255, 255, 255, 0.96); 
    }
    
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #1e293b; }
    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; color: var(--ind-navy); }
    
    /* 🧊 Stats Card Industrial */
    .stat-card { 
        background: var(--glass); border-radius: 28px; padding: 22px; border: 1px solid #e2e8f0; 
        transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 10px 30px rgba(0,0,0,0.02);
    }
    .stat-card:hover { transform: translateY(-7px); box-shadow: 0 20px 40px rgba(67, 97, 238, 0.12); border-color: var(--ind-blue); }
    .stat-label { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; }
    .stat-value { font-family: 'Orbitron'; font-size: 24px; font-weight: 800; color: var(--ind-navy); }

    /* 🛡️ Filter Panel */
    .filter-panel { 
        background: var(--ind-navy); border-radius: 24px; padding: 15px 25px; 
        color: white; border: 2px solid var(--ind-blue); box-shadow: 0 15px 35px rgba(15, 23, 42, 0.2);
    }
    
    /* 📈 Chart Box */
    .chart-box { 
        background: #fff; border-radius: 32px; padding: 25px; 
        border: 1px solid #edf2f7; box-shadow: 0 15px 35px rgba(0,0,0,0.03); 
    }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    
    {{-- 🛰️ HEADER & RANGE SELECTION --}}
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding_Control <span class="text-primary">Intelligence</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0">
                <i class="fas fa-microchip text-primary mr-2"></i> Real-time WIP Analytics // Industrial Core
            </p>
        </div>
        
        <form action="{{ route('ppic.welding.index') }}" method="GET" class="mt-4 mt-xl-0">
            <div class="filter-panel d-flex align-items-center animate__animated animate__zoomIn">
                <div class="d-flex align-items-center mr-4">
                    <input type="date" name="start_date" class="form-control form-control-sm bg-transparent border-0 text-white font-weight-bold" value="{{ $start_date }}">
                    <i class="fas fa-arrow-right mx-3 text-primary"></i>
                    <input type="date" name="end_date" class="form-control form-control-sm bg-transparent border-0 text-white font-weight-bold" value="{{ $end_date }}">
                </div>
                <button type="submit" class="btn btn-primary rounded-xl px-4 py-2 font-weight-black shadow-lg">SYNC_DATA</button>
            </div>
        </form>
    </div>

    {{-- 📊 TOP SUMMARY CARDS (DAILY, WEEKLY, MONTHLY) --}}
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card border-left-primary" style="border-left: 6px solid var(--ind-blue) !important;">
                <div class="stat-label">Efficiency Rate</div>
                <div class="stat-value text-primary">{{ $achievementRate }}%</div>
                <div class="progress mt-3" style="height: 6px; border-radius: 10px;">
                    <div class="progress-bar bg-primary" style="width: {{ $achievementRate }}%"></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-label">Daily WIP Output</div>
                <div class="stat-value text-dark">{{ number_format($totalActual) }} <small class="h6">PCS</small></div>
                <small class="text-muted font-weight-bold">TARGET: {{ number_format($totalPlan) }}</small>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-label text-success">Weekly Throughput</div>
                <div class="stat-value text-success">{{ number_format(array_sum($dailyOk ?? [])) }} <small class="h6">PCS</small></div>
                <div id="miniWeeklyChart" style="margin-top: -10px;"></div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card bg-dark text-white border-0">
                <div class="stat-label text-info">Monthly Capacity</div>
                <div class="stat-value text-white">{{ number_format(array_sum($monthlyOk ?? [])) }} <small class="h6 text-white-50">PCS</small></div>
                <small class="text-warning font-weight-bold uppercase" style="font-size: 8px;">Cumulative Logged Data</small>
            </div>
        </div>
    </div>

    {{-- 📉 MAIN CHARTS --}}
    <div class="row">
        <div class="col-xl-8 mb-4 animate__animated animate__fadeInLeft">
            <div class="chart-box h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="stat-label text-dark mb-0"><i class="fas fa-align-left mr-2 text-primary"></i> Part Production Load (Horizontal)</h6>
                    <span class="badge badge-light border px-3">WIP_REALTIME</span>
                </div>
                <div id="horizontalLoadChart"></div>
            </div>
        </div>

        <div class="col-xl-4 mb-4 animate__animated animate__fadeInRight">
            <div class="chart-box h-100">
                <h6 class="stat-label text-dark mb-4 text-center">Quality Yield Breakdown</h6>
                <div id="qualityDonut"></div>
                <div class="mt-4 p-3 bg-light rounded-2xl text-center border">
                    <small class="d-block font-weight-bold text-muted mb-1 uppercase">Rejection Rate</small>
                    @php $rejectRate = ($totalActual + $totalNg) > 0 ? round(($totalNg / ($totalActual + $totalNg)) * 100, 1) : 0; @endphp
                    <h3 class="font-weight-black text-danger mb-0">{{ $rejectRate }}%</h3>
                </div>
            </div>
        </div>

        <div class="col-12 animate__animated animate__fadeInUp">
            <div class="chart-box">
                <h6 class="stat-label text-dark mb-4"><i class="fas fa-chart-line mr-2 text-primary"></i> 7-Day Efficiency Trend</h6>
                <div id="performanceTrendChart"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Config Default untuk Animasi Industrial
    const chartAnimations = {
        enabled: true,
        easing: 'easeinout',
        speed: 1000,
        animateGradually: { enabled: true, delay: 150 },
        dynamicAnimation: { enabled: true, speed: 450 }
    };

    // 1. HORIZONTAL BAR CHART (LOAD)
    new ApexCharts(document.querySelector("#horizontalLoadChart"), {
        series: [
            { name: 'Target', data: @json($chartTargets) },
            { name: 'Actual', data: @json($chartActuals) }
        ],
        chart: { type: 'bar', height: 400, toolbar: { show: false }, animations: chartAnimations },
        colors: ['#e2e8f0', '#4361ee'],
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 8,
                barHeight: '65%',
                dataLabels: { position: 'top' }
            }
        },
        xaxis: { categories: @json($chartLabels) },
        legend: { position: 'top', fontWeight: 700 },
        tooltip: { theme: 'dark' }
    }).render();

    // 2. QUALITY DONUT
    new ApexCharts(document.querySelector("#qualityDonut"), {
        series: [{{ $totalActual }}, {{ $totalNg }}],
        chart: { type: 'donut', height: 320, animations: chartAnimations },
        labels: ['OK Goods', 'NG Reject'],
        colors: ['#10b981', '#ef4444'],
        stroke: { width: 0 },
        plotOptions: {
            pie: {
                donut: {
                    size: '75%',
                    labels: {
                        show: true,
                        total: { show: true, label: 'TOTAL WIP', formatter: () => '{{ number_format($totalActual + $totalNg) }}' }
                    }
                }
            }
        },
        legend: { position: 'bottom', fontWeight: 700 }
    }).render();

    // 3. PERFORMANCE TREND (AREA)
    new ApexCharts(document.querySelector("#performanceTrendChart"), {
        series: [
            { name: 'Good Output', data: @json($dailyOk ?? []) },
            { name: 'Defects (NG)', data: @json($dailyNg ?? []) }
        ],
        chart: { type: 'area', height: 300, toolbar: { show: false }, animations: chartAnimations },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#4361ee', '#ef4444'],
        fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
        xaxis: { categories: @json($dailyLabels ?? []) },
        dataLabels: { enabled: false },
        tooltip: { theme: 'dark' }
    }).render();

    // 4. MINI WEEKLY SPARKLINE
    new ApexCharts(document.querySelector("#miniWeeklyChart"), {
        chart: { type: 'area', height: 60, sparkline: { enabled: true }, animations: { enabled: true } },
        series: [{ data: @json($dailyOk ?? []) }],
        stroke: { curve: 'smooth', width: 2 },
        colors: ['#10b981'],
        fill: { opacity: 0.1 }
    }).render();
</script>
@endsection