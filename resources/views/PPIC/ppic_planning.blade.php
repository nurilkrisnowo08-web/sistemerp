@extends('layout.admin')

@section('content')
@php
    $totalWos = array_sum($statusCount);
    $wosCompletionRate = $totalWos > 0 ? round(($statusCount['completed'] / $totalWos) * 100, 1) : 0;
@endphp

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root { 
        --app-bg: #f4f7fe;
        --card-bg: #ffffff;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --primary: #4318FF;
        --success: #05CD99;
        --danger: #EE5D50;
        --warning: #FFCE20;
        --info: #39B8FF;
    }
    
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--app-bg); color: var(--text-main); }
    
    /* HEADER & FILTERS */
    .dashboard-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 25px; gap: 15px; }
    .title-area h1 { font-weight: 900; letter-spacing: -1px; color: #2b3674; margin: 0; font-size: 30px; text-transform: uppercase; }
    .title-area p { font-size: 12px; font-weight: 700; color: #a3aed1; margin: 2px 0 0 0; }
    .status-pulse { display: inline-block; width: 10px; height: 10px; background-color: var(--success); border-radius: 50%; box-shadow: 0 0 0 4px rgba(5, 205, 153, 0.2); animation: pulse 2s infinite; margin-right: 6px; }

    .filter-card { background: var(--card-bg); border-radius: 16px; padding: 8px 12px; display: flex; align-items: center; gap: 12px; box-shadow: 0 5px 14px rgba(0,0,0,0.02); }
    .filter-card select, .filter-card input { border: none; outline: none; font-weight: 700; color: #2b3674; background: transparent; cursor: pointer; font-size: 13px; font-family: 'Plus Jakarta Sans'; }
    .filter-card select { padding-right: 15px; border-right: 2px solid #e9edf7; }
    
    .eff-badge { background: linear-gradient(135deg, #868CFF, #4318FF); color: #fff; padding: 12px 25px; border-radius: 16px; text-align: center; box-shadow: 0 8px 20px rgba(67, 24, 255, 0.25); }
    .eff-badge span { display: block; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; opacity: 0.85; margin-bottom: 2px; }
    .eff-badge h3 { margin: 0; font-size: 26px; font-weight: 900; font-family: 'JetBrains Mono', monospace; line-height: 1; }

    /* ALERT TICKER */
    .alert-ticker { background: #fff1f2; border: 1px solid #ffe4e6; border-radius: 14px; padding: 12px 18px; display: flex; align-items: center; gap: 15px; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(225, 29, 72, 0.05); }
    .alert-badge { background: var(--danger); color: #fff; font-size: 11px; font-weight: 800; padding: 4px 12px; border-radius: 8px; letter-spacing: 1px; }
    .ticker-content { color: #be123c; font-size: 13px; font-weight: 700; white-space: nowrap; overflow: hidden; width: 100%; position: relative; }
    .ticker-content marquee { display: flex; gap: 40px; }

    /* KPI CARDS */
    .kpi-wrapper { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
    .kpi-card { background: var(--card-bg); border-radius: 20px; padding: 22px; box-shadow: 0 5px 14px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 18px; transition: transform 0.2s; }
    .kpi-card:hover { transform: translateY(-3px); }
    .kpi-icon { width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; }
    .kpi-info h4 { font-size: 12px; font-weight: 700; color: #a3aed1; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-info h2 { font-size: 28px; font-weight: 800; color: #2b3674; margin: 0; font-family: 'JetBrains Mono', monospace; line-height: 1.1; }

    /* PANELS */
    .b-panel { background: var(--card-bg); border-radius: 24px; padding: 25px; box-shadow: 0 5px 14px rgba(0,0,0,0.02); height: 100%; display: flex; flex-direction: column; }
    .b-panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .b-panel-title { font-size: 15px; font-weight: 800; color: #2b3674; margin: 0; display: flex; align-items: center; gap: 8px; }
    .b-panel-badge { background: #f4f7fe; color: #4318FF; font-size: 10px; font-weight: 800; padding: 5px 12px; border-radius: 8px; letter-spacing: 1px; }

    /* DONUT LEGEND */
    .wos-stats { display: flex; justify-content: space-between; margin-top: 15px; background: #f8fafc; border-radius: 16px; padding: 15px; }
    .wos-stat-item { text-align: center; flex: 1; }
    .wos-stat-item span { display: block; font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }
    .wos-stat-item h5 { margin: 0; font-size: 20px; font-weight: 900; font-family: 'JetBrains Mono', monospace; }

    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(5, 205, 153, 0.4); } 70% { box-shadow: 0 0 0 6px rgba(5, 205, 153, 0); } 100% { box-shadow: 0 0 0 0 rgba(5, 205, 153, 0); } }

    /* 🖨️ PROFESSIONAL PRINT TEMPLATE */
    .print-template { display: none; }
    @media print {
        @page { size: A4 portrait; margin: 10mm; }
        .screen-only, .no-print, .main-sidebar, .main-header, .btn, .filter-card, .modal, #sidebarToggle, .navbar, .eff-badge, .kpi-wrapper, .alert-ticker { display: none !important; }
        body { background: white !important; margin: 0; padding: 0; font-size: 10px; color: #000; }
        .print-template { display: block !important; width: 100% !important; color: #000 !important; }
        
        .print-header-container { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 15px; }
        .print-logo-section { display: flex; align-items: center; gap: 15px; }
        .print-logo-section img { height: 45px; }
        .print-company-info h2 { margin: 0; font-size: 18px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; }
        .print-company-info p { margin: 2px 0 0; font-size: 11px; font-weight: 700; color: #475569; }
        
        .print-meta { text-align: right; line-height: 1.4; }
        
        .print-recap-grid { display: flex; justify-content: space-between; margin-bottom: 15px; gap: 10px; }
        .summary-box { flex: 1; border: 1px solid #000; border-radius: 4px; overflow: hidden; text-align: center; }
        .summary-title { background: #f1f5f9; padding: 6px; font-size: 9px; font-weight: 800; border-bottom: 1px solid #000; text-transform: uppercase; }
        .summary-value { padding: 8px; font-size: 16px; font-weight: 900; font-family: 'JetBrains Mono', monospace; }
        
        .print-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .print-table th { background: #f8fafc !important; border: 1px solid #000; padding: 8px 6px; font-size: 10px; text-transform: uppercase; font-weight: 800; text-align: center; }
        .print-table td { border: 1px solid #000; padding: 8px 6px; font-size: 10px; vertical-align: middle; }
    }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
    {{-- 🖨️ LEMBAR CETAK PROFESIONAL (A4 PORTRAIT) --}}
    <div class="print-template">
        <div class="print-header-container">
            <div class="print-logo-section">
                <!-- Gunakan Asset Logo Perusahaan -->
                <img src="{{ asset('public/admin/img/Logo-asalta.png') }}" alt="Logo Asalta" onerror="this.style.display='none'">
                <div class="print-company-info">
                    <h2>PT ASALTA MANDIRI AGUNG</h2>
                    <p>DAILY PRODUCTION PLAN & ACTUAL RECAP ({{ strtoupper($areaFilter ?? 'ALL MACHINES') }})</p>
                </div>
            </div>
            <div class="print-meta">
                <p style="margin: 0; font-size: 12px;"><b>PLAN DATE:</b> {{ date('d M Y', strtotime($date)) }}</p>
                <p style="margin: 2px 0 0; font-size: 9px;">Printed on: {{ date('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="print-recap-grid">
            <div class="summary-box"><div class="summary-title">Total Target Plan</div><div class="summary-value">{{ number_format($totalPlan) }}</div></div>
            <div class="summary-box"><div class="summary-title">Total Actual OK</div><div class="summary-value">{{ number_format($totalActual) }}</div></div>
            <div class="summary-box"><div class="summary-title">WOS Created</div><div class="summary-value">{{ $totalWos }}</div></div>
            <div class="summary-box"><div class="summary-title">Achievement Rate</div><div class="summary-value">{{ number_format($achievementRate, 1) }}%</div></div>
        </div>

        <table class="print-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="25%">Part Number / Name</th>
                    <th width="15%">Line</th>
                    <th width="12%">Target (Pcs)</th>
                    <th width="12%">Actual (Pcs)</th>
                    <th width="13%">Balance</th>
                    <th width="18%">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $idx => $p)
                @php
                    $balance = $p->plan_qty - $p->actual_qty;
                    $statusStr = $balance <= 0 ? 'ACHIEVED' : 'SHORTAGE / RUNNING';
                    $statusColor = $balance <= 0 ? '#15803d' : '#b91c1c';
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td><b>{{ $p->part_no }}</b></td>
                    <td style="text-align: center;">{{ $p->line_code ?? '-' }}</td>
                    <td style="text-align: right; font-family:'JetBrains Mono';">{{ number_format($p->plan_qty) }}</td>
                    <td style="text-align: right; color: #15803d; font-weight: bold; font-family:'JetBrains Mono';">{{ number_format($p->actual_qty) }}</td>
                    <td style="text-align: right; color: {{ $balance > 0 ? '#b91c1c' : '#15803d' }}; font-weight:bold; font-family:'JetBrains Mono';">{{ number_format($balance) }}</td>
                    <td style="text-align: center; color: {{ $statusColor }}; font-weight: bold; font-size:9px;">{{ $statusStr }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; font-style: italic;">Belum ada plan produksi pada area dan tanggal ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div style="margin-top: 40px; display: flex; justify-content: space-around; text-align: center;">
            <div style="width: 200px; border-top: 1px solid #000; padding-top: 5px; font-weight: 800; font-size: 11px;">Production Manager</div>
            <div style="width: 200px; border-top: 1px solid #000; padding-top: 5px; font-weight: 800; font-size: 11px;">PPIC Manager</div>
        </div>
    </div>


    {{-- 🖥️ TAMPILAN SCREEN KHUSUS --}}
    <div class="screen-only">
        {{-- 1. HEADER & FILTERS --}}
        <div class="dashboard-header">
            <div class="title-area">
                <h1>CORE_PRODUCTION_AI</h1>
                <p><span class="status-pulse"></span> SYSTEM STATUS: ONLINE & OPTIMAL // LAST SYNC: {{ date('H:i:s') }}</p>
            </div>
            
            <div class="header-controls">
                {{-- 🔥 FILTER AREA & DATE & PRINT BUTTON 🔥 --}}
                <form id="ppicFilter" action="{{ route('ppic.dashboard') }}" method="GET" class="filter-card">
                    <i class="fas fa-layer-group text-primary ml-1"></i>
                    <select name="area" onchange="document.getElementById('ppicFilter').submit();">
                        <option value="all" {{ ($areaFilter ?? 'all') == 'all' ? 'selected' : '' }}>ALL MACHINES</option>
                        <option value="big" {{ ($areaFilter ?? 'all') == 'big' ? 'selected' : '' }}>BIG PRESS ONLY</option>
                        <option value="small" {{ ($areaFilter ?? 'all') == 'small' ? 'selected' : '' }}>SMALL PRESS ONLY</option>
                    </select>
                    
                    <i class="far fa-calendar-alt text-primary ml-1"></i>
                    <input type="date" name="date" value="{{ $date }}" onchange="document.getElementById('ppicFilter').submit();">
                    
                    <button type="button" onclick="window.print()" class="btn btn-dark btn-sm ml-2 font-weight-bold" style="border-radius: 8px; padding: 4px 12px; font-size:12px;">
                        <i class="fas fa-print mr-1"></i> Print Recap
                    </button>
                </form>
                
                <div class="eff-badge">
                    <span>Achievement Rate</span>
                    <h3>{{ number_format($achievementRate, 1) }}%</h3>
                </div>
            </div>
        </div>

        {{-- 2. ALERT TICKER --}}
        @if(isset($alerts) && count($alerts) > 0)
        <div class="alert-ticker">
            <div class="alert-badge"><i class="fas fa-exclamation-triangle mr-1"></i> ALERT</div>
            <div class="ticker-content">
                <marquee behavior="scroll" direction="left" scrollamount="6">
                    @foreach($alerts as $al)
                        <span style="margin-right: 40px;">
                            [M/C: {{ $al->kode_Line }}] Part {{ $al->material_code }} melaporkan problem: {{ $al->keterangan }} ({{ date('H:i', strtotime($al->updated_at)) }})
                        </span>
                    @endforeach
                </marquee>
            </div>
        </div>
        @endif

        {{-- 3. KPI CARDS --}}
        <div class="kpi-wrapper">
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #39B8FF, #0084F4);"><i class="fas fa-bullseye"></i></div>
                <div class="kpi-info"><h4>Total Target (Pcs)</h4><h2>{{ number_format($totalPlan) }}</h2></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #05CD99, #02A87B);"><i class="fas fa-check-circle"></i></div>
                <div class="kpi-info"><h4>Verified Actual OK</h4><h2>{{ number_format($totalActual) }}</h2></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #868CFF, #4318FF);"><i class="fas fa-clipboard-list"></i></div>
                <div class="kpi-info"><h4>Total WOS Created</h4><h2>{{ $totalWos }} <span style="font-size:14px; color:#a3aed1;">Doc</span></h2></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #FFCE20, #FF9B00);"><i class="fas fa-flag-checkered"></i></div>
                <div class="kpi-info"><h4>WOS Completion</h4><h2>{{ number_format($wosCompletionRate, 1) }}%</h2></div>
            </div>
        </div>

        {{-- 4. MIDDLE ROW: TARGET VS ACTUAL & JOB EXECUTION --}}
        <div class="row mb-4">
            <div class="col-xl-8 mb-4 mb-xl-0">
                <div class="b-panel">
                    <div class="b-panel-header">
                        <h3 class="b-panel-title"><i class="fas fa-chart-bar text-primary"></i> Target vs Actual Progress</h3>
                        <div class="b-panel-badge">LIVE FEED: {{ strtoupper($areaFilter ?? 'ALL') }}</div>
                    </div>
                    <div id="targetActualChart" style="flex: 1; min-height: 350px;"></div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="b-panel">
                    <div class="b-panel-header justify-content-center">
                        <h3 class="b-panel-title">WOS Execution Status</h3>
                    </div>
                    <div id="wosStatusChart" class="d-flex justify-content-center mt-2"></div>
                    
                    <div class="wos-stats">
                        <div class="wos-stat-item" style="color: #94a3b8;">
                            <span>Wait</span><h5>{{ $statusCount['waiting'] ?? 0 }}</h5>
                        </div>
                        <div class="wos-stat-item" style="color: #39B8FF;">
                            <span>Run</span><h5>{{ $statusCount['running'] ?? 0 }}</h5>
                        </div>
                        <div class="wos-stat-item" style="color: #05CD99;">
                            <span>Done</span><h5>{{ $statusCount['completed'] ?? 0 }}</h5>
                        </div>
                        <div class="wos-stat-item" style="color: #EE5D50;">
                            <span>Short</span><h5>{{ $statusCount['shortage'] ?? 0 }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. BOTTOM ROW: DAILY & MONTHLY TRENDS --}}
        <div class="row">
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="b-panel">
                    <div class="b-panel-header">
                        <h3 class="b-panel-title"><i class="fas fa-wave-square text-success"></i> 7-Days Quality Stability</h3>
                    </div>
                    <div id="dailyStabilityChart" style="min-height: 280px;"></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="b-panel">
                    <div class="b-panel-header">
                        <h3 class="b-panel-title"><i class="fas fa-chart-area text-info"></i> 6-Months Quality Trend</h3>
                    </div>
                    <div id="monthlyStabilityChart" style="min-height: 280px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // GLOBAL CHART CONFIG
    const commonFont = 'Plus Jakarta Sans';
    
    // 1. TARGET VS ACTUAL (HORIZONTAL BAR)
    const partLabels = @json($chartLabels ?? []);
    const targetData = @json($chartTargets ?? []);
    const actualData = @json($chartActuals ?? []);

    if (partLabels.length > 0) {
        new ApexCharts(document.querySelector("#targetActualChart"), {
            series: [
                { name: 'ACTUAL OK', data: actualData },
                { name: 'TARGET PLAN', data: targetData }
            ],
            chart: { type: 'bar', height: 350, toolbar: { show: false }, fontFamily: commonFont },
            plotOptions: {
                bar: { 
                    horizontal: true, 
                    barHeight: '50%',
                    borderRadius: 4,
                    dataLabels: { position: 'top' }
                }
            },
            // ✨ WARNA TELAH DIUBAH: Biru untuk Actual, Merah Danger (#EE5D50) untuk Target ✨
            colors: ['#4318FF', '#EE5D50'], 
            dataLabels: { enabled: false },
            stroke: { show: true, width: 2, colors: ['transparent'] },
            xaxis: { 
                categories: partLabels,
                labels: { style: { fontWeight: 700, fontSize: '10px', colors: '#64748b' } }
            },
            yaxis: { labels: { style: { fontWeight: 800, fontSize: '11px', fontFamily: 'JetBrains Mono' } } },
            legend: { position: 'top', horizontalAlign: 'right', fontWeight: 700 },
            grid: { strokeDashArray: 4, borderColor: '#f1f5f9' },
            tooltip: { theme: 'light', y: { formatter: function (val) { return val + " Pcs" } } }
        }).render();
    } else {
        document.querySelector("#targetActualChart").innerHTML = "<div class='text-center text-muted font-italic mt-5 pt-5'>No production plan scheduled for this area & date.</div>";
    }

    // 2. WOS STATUS DISTRIBUTION (DONUT)
    const wait = {{ $statusCount['waiting'] ?? 0 }};
    const run = {{ $statusCount['running'] ?? 0 }};
    const done = {{ $statusCount['completed'] ?? 0 }};
    const short = {{ $statusCount['shortage'] ?? 0 }};
    
    if ((wait + run + done + short) > 0) {
        new ApexCharts(document.querySelector("#wosStatusChart"), {
            series: [done, run, wait, short],
            chart: { type: 'donut', height: 260, fontFamily: commonFont },
            labels: ['Completed', 'Running', 'Waiting', 'Shortage'],
            colors: ['#05CD99', '#39B8FF', '#cbd5e1', '#EE5D50'],
            plotOptions: {
                pie: {
                    donut: { 
                        size: '75%',
                        labels: { 
                            show: true, 
                            name: { fontSize: '12px', fontWeight: 700, color: '#64748b' },
                            value: { fontSize: '24px', fontWeight: 900, fontFamily: 'JetBrains Mono', color: '#2b3674' },
                            total: { show: true, showAlways: true, label: 'TOTAL WOS', fontSize: '10px', fontWeight: 800, color: '#a3aed1' }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: { show: false },
            stroke: { show: true, width: 4, colors: ['#fff'] }
        }).render();
    } else {
        document.querySelector("#wosStatusChart").innerHTML = "<div class='text-center text-muted font-italic mt-5'>No WOS Data.</div>";
    }

    // 3. DAILY STABILITY CHART
    const dailyLbl = @json($dailyLabels ?? []);
    const dailyOkData = @json($dailyOk ?? []);
    const dailyNgData = @json($dailyNg ?? []);

    new ApexCharts(document.querySelector("#dailyStabilityChart"), {
        series: [
            { name: "Total OK", data: dailyOkData },
            { name: "Total Defect (NG)", data: dailyNgData }
        ],
        chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: commonFont },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#05CD99', '#EE5D50'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] } },
        xaxis: { categories: dailyLbl, labels: { style: { fontWeight: 700, fontSize: '10px' } } },
        yaxis: { labels: { style: { fontWeight: 700, fontFamily: 'JetBrains Mono' } } },
        dataLabels: { enabled: false },
        legend: { position: 'top', fontWeight: 700 },
        grid: { strokeDashArray: 4, borderColor: '#f1f5f9' }
    }).render();

    // 4. MONTHLY STABILITY CHART
    const monthlyLbl = @json($monthlyLabels ?? []);
    const monthlyOkData = @json($monthlyOk ?? []);
    const monthlyNgData = @json($monthlyNg ?? []);

    new ApexCharts(document.querySelector("#monthlyStabilityChart"), {
        series: [
            { name: "Total OK", data: monthlyOkData },
            { name: "Total Defect (NG)", data: monthlyNgData }
        ],
        chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: commonFont },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['#39B8FF', '#FFCE20'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] } },
        xaxis: { categories: monthlyLbl, labels: { style: { fontWeight: 700, fontSize: '10px' } } },
        yaxis: { labels: { style: { fontWeight: 700, fontFamily: 'JetBrains Mono' } } },
        dataLabels: { enabled: false },
        legend: { position: 'top', fontWeight: 700 },
        grid: { strokeDashArray: 4, borderColor: '#f1f5f9' }
    }).render();
</script>
@endsection