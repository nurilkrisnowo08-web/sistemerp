@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root { --ind-blue: #4361ee; --ind-navy: #0f172a; --glass: rgba(255, 255, 255, 0.9); }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
    
    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; color: var(--ind-navy); }
    
    .stat-card { background: var(--glass); border-radius: 24px; padding: 25px; border: 1px solid #e2e8f0; transition: 0.3s; box-shadow: 0 10px 25px rgba(0,0,0,0.02); }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 20px 35px rgba(67, 97, 238, 0.1); }
    
    .stat-label { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; }
    .stat-value { font-family: 'Orbitron'; font-size: 26px; font-weight: 800; color: var(--ind-navy); }
    
    .filter-box { background: var(--ind-navy); border-radius: 20px; padding: 15px 25px; color: white; margin-bottom: 30px; border: 1.5px solid var(--ind-blue); }
    .chart-container { background: #fff; border-radius: 28px; padding: 25px; border: 1px solid #edf2f7; height: 100%; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    {{-- 🛸 HEADER & RANGE FILTER --}}
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
        <div>
            <h1 class="heading-hub mb-1">Welding_Control <span class="text-primary">Intelligence</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0"><i class="fas fa-microchip text-primary mr-2"></i> Industrial Monitoring // Phase 3.0</p>
        </div>
        
        <form action="{{ route('ppic.welding.index') }}" method="GET" class="mt-3 mt-lg-0">
            <div class="filter-box d-flex align-items-center shadow-lg">
                <i class="fas fa-calendar-alt mr-3 text-primary"></i>
                <input type="date" name="start_date" class="form-control form-control-sm bg-transparent border-0 text-white font-weight-bold" value="{{ $start_date }}">
                <span class="mx-2 text-primary">➜</span>
                <input type="date" name="end_date" class="form-control form-control-sm bg-transparent border-0 text-white font-weight-bold mr-3" value="{{ $end_date }}">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold">EXECUTE_SYNC</button>
            </div>
        </form>
    </div>

    {{-- 🚨 ALERTS --}}
    @foreach($alerts as $alert)
    <div class="alert alert-danger border-0 shadow-sm rounded-2xl p-3 mb-4 animate__animated animate__headShake d-flex justify-content-between align-items-center" style="border-left: 6px solid #ef4444 !important;">
        <div class="d-flex align-items-center">
            <div class="bg-danger text-white rounded-circle p-2 mr-3"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <h6 class="font-weight-bold mb-0">{{ $alert->kode_line }} - {{ $alert->part_no }}</h6>
                <small class="uppercase font-weight-bold opacity-75">{{ $alert->keterangan }}</small>
            </div>
        </div>
        <div class="text-right">
            <small class="d-block font-weight-bold opacity-50">{{ date('H:i', strtotime($alert->jam_lapor)) }}</small>
            <a href="#" class="btn btn-dark btn-sm rounded-pill px-3 mt-1 font-weight-bold" style="font-size: 9px;">RESOLVE</a>
        </div>
    </div>
    @endforeach

    {{-- 📊 TOP SUMMARY CARDS --}}
    <div class="row mb-5">
        <div class="col-md-3">
            <div class="stat-card border-left-primary" style="border-left: 5px solid var(--ind-blue) !important;">
                <div class="stat-label">Production Efficiency</div>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="stat-value text-primary">{{ $achievementRate }}%</div>
                    <i class="fas fa-bolt-lightning text-primary fa-2x opacity-25"></i>
                </div>
                <div class="progress mt-2" style="height: 6px; border-radius: 10px; background: #e2e8f0;">
                    <div class="progress-bar bg-primary" style="width: {{ $achievementRate }}%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Scheduled Target</div>
                <div class="stat-value">{{ number_format($totalPlan) }}</div>
                <small class="text-muted font-weight-bold uppercase">Total PCS Range</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label text-success">Total OK (Passed)</div>
                <div class="stat-value text-success">{{ number_format($totalActual) }}</div>
                <small class="text-muted font-weight-bold uppercase">Output Success</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label text-danger">Total NG (Reject)</div>
                <div class="stat-value text-danger">{{ number_format($totalNg) }}</div>
                <small class="text-muted font-weight-bold uppercase">Defect Detected</small>
            </div>
        </div>
    </div>

    {{-- 📈 CHARTS SECTION --}}
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="chart-container shadow-sm">
                <h6 class="stat-label mb-4 text-dark text-center">Quality Breakdown (OK vs NG)</h6>
                <div id="qualityDonut"></div>
            </div>
        </div>
        
        <div class="col-lg-8 mb-4">
            <div class="chart-container shadow-sm">
                <h6 class="stat-label mb-4 text-dark">Production Load Analysis Per Part</h6>
                <div id="loadChart"></div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="chart-container shadow-sm">
                <h6 class="stat-label mb-4 text-dark">Daily Performance (Trend 7 Days)</h6>
                <div id="dailyTrendChart"></div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="chart-container shadow-sm">
                <h6 class="stat-label mb-4 text-dark">Monthly Capacity Stability</h6>
                <div id="monthlyChart"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Chart OK vs NG (Donut)
    new ApexCharts(document.querySelector("#qualityDonut"), {
        series: [{{ $totalActual }}, {{ $totalNg }}],
        chart: { type: 'donut', height: 320 },
        labels: ['OK', 'NG'],
        colors: ['#10b981', '#ef4444'],
        legend: { position: 'bottom', fontWeight: 700 },
        plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'TOTAL' } } } } }
    }).render();

    // 2. Chart Load Per Part (Bar)
    new ApexCharts(document.querySelector("#loadChart"), {
        series: [{ name: 'Target', data: @json($chartTargets) }, { name: 'Actual', data: @json($chartActuals) }],
        chart: { type: 'bar', height: 280, toolbar: { show: false } },
        colors: ['#e2e8f0', '#4361ee'],
        plotOptions: { bar: { borderRadius: 8, columnWidth: '45%' } },
        xaxis: { categories: @json($chartLabels) },
        dataLabels: { enabled: false }
    }).render();

    // 3. Chart Daily Performance (Line)
    new ApexCharts(document.querySelector("#dailyTrendChart"), {
        series: [{ name: 'OK', data: @json($dailyOk) }, { name: 'NG', data: @json($dailyNg) }],
        chart: { type: 'area', height: 280, toolbar: { show: false } },
        colors: ['#10b981', '#ef4444'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0 } },
        xaxis: { categories: @json($dailyLabels) },
        dataLabels: { enabled: false }
    }).render();

    // 4. Monthly Chart
    new ApexCharts(document.querySelector("#monthlyChart"), {
        series: [{ name: 'Total OK', data: @json($monthlyOk) }],
        chart: { type: 'bar', height: 280, toolbar: { show: false } },
        colors: ['#0f172a'],
        plotOptions: { bar: { borderRadius: 10, columnWidth: '50%' } },
        xaxis: { categories: @json($monthlyLabels) },
    }).render();
</script>
@endsection