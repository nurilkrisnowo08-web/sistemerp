@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #f8fafc; color: #1e293b; }
    
    .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
    .title-area h1 { font-weight: 900; letter-spacing: -0.5px; color: #475569; margin: 0; font-size: 28px; text-transform: uppercase; }
    .title-area p { font-size: 11px; font-weight: 700; color: #94a3b8; margin: 5px 0 0 0; }
    .status-dot { display: inline-block; width: 8px; height: 8px; background-color: #10b981; border-radius: 50%; margin-right: 5px; }

    .header-controls { display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
    
    /* STYLE FILTER BOX BARU */
    .filter-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 6px 10px; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .filter-box select, .filter-box input { border: none; outline: none; font-weight: 700; color: #475569; background: transparent; cursor: pointer; font-size: 12px; }
    .filter-box select { padding-right: 10px; border-right: 1px solid #e2e8f0; }
    
    .eff-badge { background: linear-gradient(135deg, #3b82f6, #4f46e5); color: #fff; padding: 10px 25px; border-radius: 16px; text-align: center; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3); }
    .eff-badge span { display: block; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; }
    .eff-badge h3 { margin: 0; font-size: 22px; font-weight: 900; font-family: 'JetBrains Mono', monospace; }

    /* ALERT TICKER */
    .alert-ticker { background: #0f172a; border-radius: 12px; padding: 10px 15px; display: flex; align-items: center; gap: 15px; margin-bottom: 25px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .alert-badge { background: #ef4444; color: #fff; font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 6px; letter-spacing: 1px; }
    .ticker-content { color: #f8fafc; font-size: 11px; font-weight: 600; white-space: nowrap; overflow: hidden; width: 100%; position: relative; }
    .ticker-content marquee { font-family: 'JetBrains Mono', monospace; letter-spacing: 0.5px; }
    
    /* PANELS */
    .panel { background: #fff; border-radius: 20px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); height: 100%; }
    .panel-title { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
    .live-feed-badge { font-size: 9px; background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px; letter-spacing: 1px; }

    /* DONUT STATS */
    .donut-stats-container { display: flex; justify-content: space-around; margin-top: 20px; text-align: center; }
    .stat-item { flex: 1; }
    .stat-item span { display: block; font-size: 10px; font-weight: 800; text-transform: uppercase; margin-bottom: 5px; }
    .stat-item h4 { margin: 0; font-size: 24px; font-weight: 900; font-family: 'JetBrains Mono', monospace; }
</style>

<div class="container-fluid py-4">
    
    {{-- HEADER --}}
    <div class="dashboard-header">
        <div class="title-area">
            <h1>CORE_PRODUCTION_AI</h1>
            <p><span class="status-dot"></span> System Status: Optimal / Last Sync: {{ date('H:i:s') }}</p>
        </div>
        
        <div class="header-controls">
            {{-- 🔥 FILTER BOX BARU: TANGGAL & AREA 🔥 --}}
            <form id="filterForm" action="{{ route('ppic.dashboard') }}" method="GET" class="filter-box">
                <select name="area" onchange="document.getElementById('filterForm').submit();">
                    <option value="all" {{ ($areaFilter ?? 'all') == 'all' ? 'selected' : '' }}>ALL LINE</option>
                    <option value="big" {{ ($areaFilter ?? 'all') == 'big' ? 'selected' : '' }}>BIG PRESS</option>
                    <option value="small" {{ ($areaFilter ?? 'all') == 'small' ? 'selected' : '' }}>SMALL PRESS</option>
                </select>
                <div class="d-flex align-items-center gap-2 pl-2">
                    <input type="date" name="date" value="{{ $date }}" onchange="document.getElementById('filterForm').submit();">
                    <i class="far fa-calendar-alt text-primary"></i>
                </div>
            </form>
            
            <div class="eff-badge">
                <span>Global Efficiency</span>
                <h3>{{ number_format($globalEfficiency, 1) }}%</h3>
            </div>
        </div>
    </div>

    {{-- ALERT TICKER --}}
    @if(count($alerts) > 0)
    <div class="alert-ticker">
        <div class="alert-badge">ALERT</div>
        <div class="ticker-content">
            <marquee behavior="scroll" direction="left" scrollamount="5">
                @foreach($alerts as $alert)
                    <span style="margin-right: 50px;">• {{ $alert }}</span>
                @endforeach
            </marquee>
        </div>
    </div>
    @endif

    {{-- TOP ROW PANELS --}}
    <div class="row mb-4">
        {{-- TARGET VS ACTUAL CHART --}}
        <div class="col-md-8 mb-4 mb-md-0">
            <div class="panel">
                <div class="panel-title">
                    <div><i class="fas fa-chart-bar mr-2 text-primary"></i> Production Performance (Target Vs Actual)</div>
                    <div class="live-feed-badge">LIVE_FEED ({{ strtoupper($areaFilter ?? 'ALL') }})</div>
                </div>
                <div id="targetActualChart" style="min-height: 300px;"></div>
            </div>
        </div>

        {{-- DONUT CHART --}}
        <div class="col-md-4">
            <div class="panel d-flex flex-column justify-content-between">
                <div class="panel-title justify-content-center text-center w-100">
                    Job Execution Distribution
                </div>
                
                <div id="jobDistributionChart" class="d-flex justify-content-center"></div>

                <div class="donut-stats-container">
                    <div class="stat-item text-secondary">
                        <span>Wait</span>
                        <h4>{{ $jobWait }}</h4>
                    </div>
                    <div class="stat-item text-primary">
                        <span>Running</span>
                        <h4>{{ $jobRunning }}</h4>
                    </div>
                    <div class="stat-item text-success">
                        <span>Done</span>
                        <h4>{{ $jobDone }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- BOTTOM ROW PANELS (STABILITY) --}}
    <div class="row">
        <div class="col-md-6">
            <div class="panel">
                <div class="panel-title text-danger"><i class="fas fa-shield-alt mr-2"></i> Daily Quality Stability (OK vs NG)</div>
                <div id="dailyStabilityChart" style="min-height: 250px;"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel">
                <div class="panel-title text-warning"><i class="fas fa-chart-line mr-2"></i> Monthly Quality Stability (OK vs NG)</div>
                <div id="monthlyStabilityChart" style="min-height: 250px;"></div>
            </div>
        </div>
    </div>

</div>

<script>
    // 1. CHART TARGET VS ACTUAL (HORIZONTAL BAR)
    const targetData = @json($chartTarget);
    const actualData = @json($chartActual);
    const partLabels = @json($chartLabels);

    const targetActualOptions = {
        series: [
            { name: 'ACTUAL', data: actualData },
            { name: 'TARGET', data: targetData }
        ],
        chart: { type: 'bar', height: 320, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
        plotOptions: {
            bar: { 
                horizontal: true, 
                barHeight: '35%',
                borderRadius: 4,
                dataLabels: { position: 'top' }
            }
        },
        colors: ['#38bdf8', '#f1f5f9'], 
        dataLabels: { enabled: false },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        xaxis: { 
            categories: partLabels,
            labels: { style: { fontWeight: 700, fontSize: '10px', colors: '#64748b' } }
        },
        yaxis: {
            labels: { style: { fontWeight: 700, fontSize: '11px', fontFamily: 'JetBrains Mono' } }
        },
        legend: { position: 'top', horizontalAlign: 'right', fontWeight: 800, markers: { radius: 12 } },
        grid: { strokeDashArray: 4, borderColor: '#f1f5f9' }
    };
    
    if (partLabels.length > 0) {
        new ApexCharts(document.querySelector("#targetActualChart"), targetActualOptions).render();
    } else {
        document.querySelector("#targetActualChart").innerHTML = "<div class='text-center text-muted font-italic mt-5 pt-5'>No data available for selected area & date.</div>";
    }

    // 2. CHART JOB DISTRIBUTION (DONUT)
    const jobWait = {{ $jobWait }};
    const jobRunning = {{ $jobRunning }};
    const jobDone = {{ $jobDone }};
    const totalJobs = jobWait + jobRunning + jobDone;

    const donutOptions = {
        series: [jobDone, jobRunning, jobWait],
        chart: { type: 'donut', height: 260, fontFamily: 'Plus Jakarta Sans' },
        labels: ['Done', 'Running', 'Wait'],
        colors: ['#10b981', '#3b82f6', '#94a3b8'],
        plotOptions: {
            pie: {
                donut: { 
                    size: '80%',
                    labels: { show: false }
                }
            }
        },
        dataLabels: { enabled: false },
        legend: { show: false },
        stroke: { show: true, width: 5, colors: ['#fff'] }
    };
    
    if (totalJobs > 0) {
        new ApexCharts(document.querySelector("#jobDistributionChart"), donutOptions).render();
    } else {
        document.querySelector("#jobDistributionChart").innerHTML = "<div class='text-center text-muted font-italic mt-5'>No jobs scheduled.</div>";
    }

    // 3. CHART DUMMY UNTUK STABILITY
    const lineOptions = {
        series: [{ name: "OK", data: [1600, 0, 0, 1500, 0] }], 
        chart: { type: 'line', height: 250, toolbar: { show: false } },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#10b981'],
        xaxis: { categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'] },
        grid: { strokeDashArray: 4 }
    };
    new ApexCharts(document.querySelector("#dailyStabilityChart"), lineOptions).render();
    new ApexCharts(document.querySelector("#monthlyStabilityChart"), { ...lineOptions, colors: ['#3b82f6'] }).render();
</script>
@endsection