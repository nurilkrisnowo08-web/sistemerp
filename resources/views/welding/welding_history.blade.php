@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

@php
    $totalTake = $historyData->sum('qty_masuk');
    $totalOk = $historyData->sum('qty_ok');
    $totalNg = $historyData->sum('qty_ng');
    $totalRet = $historyData->sum('qty_return');
    $globalPerf = $totalTake > 0 ? round(($totalOk / $totalTake) * 100, 1) : 0;

    // Persiapan Data Chart per Tanggal
    $chartData = $historyData->groupBy(function($item) {
        return date('Y-m-d', strtotime($item->updated_at));
    })->map(function($day) {
        $t = $day->sum('qty_masuk');
        $o = $day->sum('qty_ok');
        $n = $day->sum('qty_ng');
        return [
            'ok' => $o,
            'ng' => $n,
            'perf' => $t > 0 ? round(($o / $t) * 100, 1) : 0
        ];
    })->sortKeys();

    $chartDates = $chartData->keys()->map(function($d) { return date('d M', strtotime($d)); })->toJson();
    $chartOk = $chartData->pluck('ok')->toJson();
    $chartNg = $chartData->pluck('ng')->toJson();
    $chartPerf = $chartData->pluck('perf')->toJson();
@endphp

<style>
    :root {
        --c-blue: #3b82f6; --c-green: #10b981; --c-red: #ef4444;
        --c-orange: #f59e0b; --c-dark: #475569; --bg-main: #f8fafc;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #1e293b; }
    
    .sys-title { font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 28px; color: #1e293b; letter-spacing: -0.5px; }
    .sys-title span { color: var(--c-blue); }
    .sys-subtitle { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }

    /* Top Cards */
    .metric-card { background: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-left: 4px solid; height: 100%; }
    .metric-card.m-blue { border-left-color: var(--c-blue); }
    .metric-card.m-green { border-left-color: var(--c-green); }
    .metric-card.m-red { border-left-color: var(--c-red); }
    .metric-card.m-orange { border-left-color: var(--c-orange); }
    .metric-card.m-dark { background: var(--c-dark); border: none; color: #fff; }
    
    .m-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; margin-bottom: 5px; }
    .m-dark .m-label { color: #cbd5e1; }
    .m-val { font-family: 'JetBrains Mono', monospace; font-size: 32px; font-weight: 800; line-height: 1.2; }
    .m-blue .m-val { color: var(--c-blue); }
    .m-green .m-val { color: var(--c-green); }
    .m-red .m-val { color: var(--c-red); }
    .m-orange .m-val { color: var(--c-orange); }
    
    /* Layout Containers */
    .content-panel { background: #fff; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; padding: 25px; margin-bottom: 25px; }
    .panel-header { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; display: flex; align-items: center; }
    
    /* Table */
    .q-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .q-table th { color: #64748b; font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 15px 10px; border-bottom: 2px solid #e2e8f0; text-align: center; }
    .q-table td { padding: 15px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: top; text-align: center; font-weight: 600; }
    .q-table .text-left { text-align: left; }
    
    .t-mono { font-family: 'JetBrains Mono', monospace; font-weight: 700; }
    .batch-link { color: var(--c-blue); font-weight: 800; text-decoration: none; }
    
    /* Print */
    @media print {
        body { background: #fff; }
        .no-print { display: none !important; }
        .content-panel { box-shadow: none; border: 1px solid #000; margin-bottom: 15px; }
        .metric-card { border: 1px solid #000 !important; color: #000 !important; }
        .m-dark { background: #fff !important; color: #000 !important; }
        .m-dark .m-label { color: #000 !important; }
    }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 no-print">
        <div>
            <div class="sys-title">QUALITY_AUDIT <span>SYSTEM</span></div>
            <div class="sys-subtitle mt-1">PRODUCTION TRACEABILITY MATRIX // PERIOD: {{ date('d M Y', strtotime($startDate)) }} - {{ date('d M Y', strtotime($endDate)) }}</div>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
            <form action="" method="GET" class="d-flex align-items-center bg-white px-3 py-2 rounded-pill border shadow-sm mr-3">
                <input type="date" name="start_date" class="form-control form-control-sm border-0 bg-transparent font-weight-bold p-0" value="{{ $startDate }}" style="width: 110px;">
                <span class="mx-2 text-muted font-weight-bold" style="font-size: 12px;">to</span>
                <input type="date" name="end_date" class="form-control form-control-sm border-0 bg-transparent font-weight-bold p-0" value="{{ $endDate }}" style="width: 110px;">
                <button type="submit" class="btn btn-primary btn-sm rounded-circle ml-2" style="width: 28px; height: 28px; padding: 0;"><i class="fas fa-sync-alt" style="font-size: 10px;"></i></button>
            </form>
            <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 font-weight-bold shadow-sm" style="font-size: 13px; letter-spacing: 0.5px; padding: 10px 20px;">
                <i class="fas fa-print mr-2"></i> PRINT REPORT
            </button>
        </div>
    </div>

    {{-- TOP CARDS --}}
    <div class="row mb-4">
        <div class="col-md-2 mb-3 mb-md-0" style="width: 20%; flex: 0 0 20%; max-width: 20%;">
            <div class="metric-card m-blue">
                <div class="m-label">TOTAL MATERIAL TAKE</div>
                <div class="m-val">{{ number_format($totalTake) }} <span style="font-size:12px; color:#94a3b8;">pcs</span></div>
            </div>
        </div>
        <div class="col-md-2 mb-3 mb-md-0" style="width: 20%; flex: 0 0 20%; max-width: 20%;">
            <div class="metric-card m-green">
                <div class="m-label">VERIFIED OK</div>
                <div class="m-val">{{ number_format($totalOk) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3 mb-md-0" style="width: 20%; flex: 0 0 20%; max-width: 20%;">
            <div class="metric-card m-red">
                <div class="m-label">TOTAL REJECT</div>
                <div class="m-val">{{ number_format($totalNg) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3 mb-md-0" style="width: 20%; flex: 0 0 20%; max-width: 20%;">
            <div class="metric-card m-orange">
                <div class="m-label">RETURNED RM</div>
                <div class="m-val">{{ number_format($totalRet) }}</div>
            </div>
        </div>
        <div class="col-md-2" style="width: 20%; flex: 0 0 20%; max-width: 20%;">
            <div class="metric-card m-dark">
                <div class="m-label">GLOBAL PERFORMANCE</div>
                <div class="m-val" style="color:#fff;">{{ number_format($globalPerf, 1) }}%</div>
            </div>
        </div>
    </div>

    {{-- CHART --}}
    <div class="content-panel animate__animated animate__zoomIn">
        <div class="panel-header"><i class="fas fa-chart-line mr-2" style="color:var(--c-blue);"></i> DAILY PRODUCTION TREND ANALYSIS</div>
        <div id="trendChart"></div>
    </div>

    {{-- DATA TABLE --}}
    <div class="content-panel animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="q-table">
                <thead>
                    <tr>
                        <th class="text-left">TIME / MACHINE</th>
                        <th class="text-left">BATCH ID</th>
                        <th class="text-left">PART DETAILS</th>
                        <th>TAKE</th>
                        <th style="color:var(--c-green);">OK</th>
                        <th style="color:var(--c-red);">NG</th>
                        <th class="text-left">DEFECT BREAKDOWN & REMARK</th>
                        <th>YIELD</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historyData as $data)
                        @php
                            $yield = $data->qty_masuk > 0 ? round(($data->qty_ok / $data->qty_masuk) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td class="text-left">
                                <div class="font-weight-bold text-dark">{{ date('d/m/y H:i', strtotime($data->updated_at)) }}</div>
                                <div style="font-size: 10px; color:#64748b; margin-top:3px;"><i class="fas fa-industry text-primary mr-1"></i> {{ $data->kode_line }} ({{ $data->shift }})</div>
                            </td>
                            <td class="text-left">
                                <a href="#" class="batch-link t-mono">{{ $data->no_produksi_stamping }}</a>
                            </td>
                            <td class="text-left">
                                <div class="font-weight-bold text-dark">{{ $data->part_no }}</div>
                                <div style="font-size: 9px; color:#64748b; text-transform:uppercase; margin-top:2px;">{{ Str::limit($data->part_name ?? 'WELDING PART', 35) }}</div>
                            </td>
                            <td class="t-mono text-muted">{{ number_format($data->qty_masuk) }}</td>
                            <td class="t-mono" style="color:var(--c-green);">{{ number_format($data->qty_ok) }}</td>
                            <td class="t-mono" style="color:var(--c-red);">{{ number_format($data->qty_ng) }}</td>
                            <td class="text-left">
                                @if($data->ng_details && $data->ng_details->count() > 0)
                                    @foreach($data->ng_details as $ng)
                                        <div style="font-size: 10px; margin-bottom:2px;">
                                            <span style="display:inline-block; width:12px; height:12px; background:#fee2e2; color:#ef4444; border-radius:50%; text-align:center; line-height:12px; font-size:8px; font-weight:bold; margin-right:4px;">!</span>
                                            {{ strtoupper($ng->ng_type) }}: <span class="t-mono text-danger">{{ $ng->qty }}</span>
                                        </div>
                                    @endforeach
                                @endif
                                @if($data->keterangan)
                                    <div style="font-size: 10px; color:#64748b; font-style:italic; margin-top:4px;">
                                        <i class="far fa-comment-dots mr-1"></i> {{ $data->keterangan }}
                                    </div>
                                @endif
                                @if(!$data->keterangan && (!$data->ng_details || $data->ng_details->count() == 0))
                                    <span style="font-size:10px; color:#cbd5e1; font-style:italic;">Good Batch</span>
                                @endif
                            </td>
                            <td class="t-mono">
                                <span style="background: {{ $yield >= 95 ? '#dcfce7' : ($yield >= 80 ? '#fef3c7' : '#fee2e2') }}; 
                                             color: {{ $yield >= 95 ? '#16a34a' : ($yield >= 80 ? '#d97706' : '#ef4444') }}; 
                                             padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                    {{ $yield }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-5 text-muted">No production data found for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const dates = {!! $chartDates !!};
        const okData = {!! $chartOk !!};
        const ngData = {!! $chartNg !!};
        const perfData = {!! $chartPerf !!};

        const options = {
            series: [{
                name: 'Performance %',
                type: 'line',
                data: perfData
            }, {
                name: 'OK Units',
                type: 'area',
                data: okData
            }, {
                name: 'NG Units',
                type: 'line',
                data: ngData
            }],
            chart: {
                height: 350,
                type: 'line',
                fontFamily: 'Plus Jakarta Sans',
                toolbar: { show: false }
            },
            colors: ['#3b82f6', '#10b981', '#ef4444'],
            stroke: {
                width: [3, 2, 2],
                curve: 'smooth'
            },
            fill: {
                type: ['solid', 'gradient', 'solid'],
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.3,
                    opacityTo: 0.05,
                    stops: [0, 100]
                }
            },
            xaxis: {
                categories: dates,
                labels: { style: { fontWeight: 600, fontSize: '11px' } }
            },
            yaxis: [
                {
                    title: { text: 'Performance %', style: { fontWeight: 700 } },
                    labels: { style: { fontWeight: 600 }, formatter: function(val) { return val + "%" } },
                    min: 0, max: 100
                },
                {
                    opposite: true,
                    title: { text: 'Units (Pcs)', style: { fontWeight: 700 } },
                    labels: { style: { fontWeight: 600 } }
                },
                {
                    show: false,
                    opposite: true
                }
            ],
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                fontWeight: 800
            },
            grid: {
                borderColor: '#e2e8f0',
                strokeDashArray: 4
            },
            tooltip: { theme: 'dark' }
        };

        const chart = new ApexCharts(document.querySelector("#trendChart"), options);
        chart.render();
    });
</script>
@endsection