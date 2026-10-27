@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

@php
    // Kalkulasi Data untuk Kartu Ringkasan (Top Cards)
    $totalTake = $historyData->sum('qty_masuk');
    $totalOk = $historyData->sum('qty_ok');
    $totalNg = $historyData->sum('qty_ng');
    $totalRet = $historyData->sum('qty_return');
    $globalPerf = $totalTake > 0 ? round(($totalOk / $totalTake) * 100, 1) : 0;

    // Persiapan Data Chart (Trend Harian)
    $chartData = $historyData->groupBy(function($item) {
        return date('Y-m-d', strtotime($item->updated_at));
    })->map(function($day) {
        $t = $day->sum('qty_masuk');
        $o = $day->sum('qty_ok');
        $n = $day->sum('qty_ng');
        return [
            'ok' => $o, 'ng' => $n, 'perf' => $t > 0 ? round(($o / $t) * 100, 1) : 0
        ];
    })->sortKeys();

    $chartDates = $chartData->keys()->map(function($d) { return date('d M', strtotime($d)); })->toJson();
    $chartOk = $chartData->pluck('ok')->toJson();
    $chartNg = $chartData->pluck('ng')->toJson();
    $chartPerf = $chartData->pluck('perf')->toJson();

    // Mengamankan variabel filter pencarian
    $currentWosSearch = $searchWos ?? request('wos') ?? '';
@endphp

<style>
    :root {
        --c-blue: #3b82f6; --c-green: #10b981; --c-red: #ef4444;
        --c-orange: #f59e0b; --c-dark: #0f172a; --bg-main: #f8fafc;
        --glass-border: rgba(226, 232, 240, 0.8);
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #1e293b; }
    
    .sys-title { font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 26px; color: #1e293b; letter-spacing: -0.5px; }
    .sys-title span { color: var(--c-blue); }
    .sys-subtitle { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }

    /* Filter Bar Khusus Pencarian WOS */
    .filter-bar { background: #fff; border-radius: 18px; border: 1px solid var(--glass-border); padding: 12px 20px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; flex-wrap: wrap; gap: 15px; align-items: center; justify-content: space-between; }
    .search-wos { background: #eff6ff; border: 1px solid #bfdbfe; font-family: 'JetBrains Mono', monospace; font-weight: bold; padding: 10px 15px 10px 38px; border-radius: 12px; width: 280px; outline: none; font-size: 12px; color: #1d4ed8; transition: 0.3s; }
    .search-wos:focus { border-color: var(--c-blue); background: #fff; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
    .search-wos::placeholder { color: #93c5fd; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 600; }
    .search-icon-wrapper { position: relative; }
    .search-icon-wrapper i { position: absolute; left: 14px; top: 12px; color: var(--c-blue); font-size: 13px; }

    /* Metric Cards */
    .metric-card { background: #fff; border-radius: 20px; padding: 22px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border: 1px solid var(--glass-border); transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; overflow: hidden; height: 100%; border-left: 5px solid; }
    .metric-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    .metric-card.m-blue { border-left-color: var(--c-blue); }
    .metric-card.m-green { border-left-color: var(--c-green); }
    .metric-card.m-red { border-left-color: var(--c-red); }
    .metric-card.m-orange { border-left-color: var(--c-orange); }
    .metric-card.m-dark { background: var(--c-dark); border: none; color: #fff; border-left-color: #38bdf8; }
    
    .m-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 8px; }
    .m-dark .m-label { color: #94a3b8; }
    .m-val { font-family: 'Orbitron', monospace; font-size: 28px; font-weight: 900; line-height: 1.1; }
    .m-blue .m-val { color: var(--c-blue); }
    .m-green .m-val { color: var(--c-green); }
    .m-red .m-val { color: var(--c-red); }
    .m-orange .m-val { color: var(--c-orange); }

    /* Containers */
    .content-panel { background: #fff; border-radius: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); border: 1px solid var(--glass-border); padding: 25px; margin-bottom: 25px; overflow: hidden; }
    .panel-header { font-size: 11px; font-weight: 800; color: var(--c-dark); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; display: flex; align-items: center; }
    
    /* Modern Table */
    .q-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .q-table th { background: #f8fafc; color: #64748b; font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 18px 15px; border-bottom: 2px solid #e2e8f0; text-align: center; }
    .q-table td { padding: 18px 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; text-align: center; font-weight: 600; transition: 0.2s; }
    .q-table tbody tr:hover { background-color: #f8fafc; }
    .q-table .text-left { text-align: left; }
    
    .t-mono { font-family: 'JetBrains Mono', monospace; font-weight: 800; }
    
    /* WOS Badge Styling */
    .wos-badge { display: inline-flex; align-items: center; background: #eff6ff; color: var(--c-blue); border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 8px; font-family: 'JetBrains Mono', monospace; font-size: 12px; font-weight: 900; letter-spacing: 0.5px; }
    
    /* Buttons */
    .btn-cyber { padding: 10px 24px; border-radius: 12px; font-weight: 800; font-size: 12px; letter-spacing: 0.5px; transition: 0.3s; }
    .btn-cyber:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

    /* Print Setup */
    @media print {
        body { background: #fff; }
        .no-print { display: none !important; }
        .content-panel { box-shadow: none; border: 1px solid #000; margin-bottom: 15px; border-radius: 0; }
        .metric-card { border: 1px solid #000 !important; color: #000 !important; border-radius: 0; }
        .m-dark { background: #fff !important; color: #000 !important; }
        .m-dark .m-label { color: #000 !important; }
        .wos-badge { background: transparent !important; color: #000 !important; border: 1px solid #000; }
    }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn no-print">
    
    {{-- HEADER & TITLE --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <div class="sys-title">QUALITY_AUDIT <span>SYSTEM</span></div>
            <div class="sys-subtitle mt-1">WELDING PRODUCTION TRACEABILITY MATRIX</div>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0 gap-2">
            <a href="{{ route('welding.index') }}" class="btn btn-outline-secondary btn-cyber bg-white shadow-sm mr-2">
                <i class="fas fa-arrow-left mr-1"></i> KEMBALI
            </a>
            <button onclick="window.print()" class="btn btn-dark btn-cyber shadow-sm">
                <i class="fas fa-print mr-2"></i> PRINT REPORT
            </button>
        </div>
    </div>

    {{-- FILTER BAR & WOS SEARCH --}}
    <div class="filter-bar">
        <form action="" method="GET" class="d-flex w-100 justify-content-between align-items-center flex-wrap" style="gap: 15px;">
            <div class="d-flex flex-wrap align-items-center" style="gap: 15px;">
                <!-- Kotak Pencarian WOS -->
                <div class="search-icon-wrapper">
                    <i class="fas fa-qrcode"></i>
                    <input type="text" name="wos" class="search-wos" placeholder="Scan / Ketik No WOS..." value="{{ $currentWosSearch }}">
                </div>
                
                <!-- Filter Tanggal -->
                <div class="d-flex align-items-center bg-light px-3 rounded-lg border" style="height: 40px; border-radius: 12px;">
                    <input type="date" name="start_date" class="border-0 bg-transparent font-weight-bold text-secondary" style="font-size: 12px; outline:none;" value="{{ $startDate }}">
                    <span class="mx-3 text-muted" style="font-size: 10px;"><i class="fas fa-arrow-right"></i></span>
                    <input type="date" name="end_date" class="border-0 bg-transparent font-weight-bold text-secondary" style="font-size: 12px; outline:none;" value="{{ $endDate }}">
                </div>
            </div>
            <div>
                <button type="submit" class="btn btn-primary btn-cyber"><i class="fas fa-search mr-2"></i> CARI DATA</button>
                @if($currentWosSearch)
                    <a href="{{ url()->current() }}" class="btn btn-danger btn-cyber ml-2"><i class="fas fa-times"></i></a>
                @endif
            </div>
        </form>
    </div>

    {{-- METRIC CARDS --}}
    <div class="row mb-4">
        <div class="col-md-2 mb-3 mb-md-0" style="width: 20%; flex: 0 0 20%; max-width: 20%;">
            <div class="metric-card m-blue">
                <div class="m-label"><i class="fas fa-boxes mr-1"></i> MATERIAL TAKE</div>
                <div class="m-val">{{ number_format($totalTake) }} <span style="font-size:12px; color:#94a3b8; font-family: 'Plus Jakarta Sans';">pcs</span></div>
            </div>
        </div>
        <div class="col-md-2 mb-3 mb-md-0" style="width: 20%; flex: 0 0 20%; max-width: 20%;">
            <div class="metric-card m-green">
                <div class="m-label"><i class="fas fa-check-circle mr-1"></i> VERIFIED OK</div>
                <div class="m-val">{{ number_format($totalOk) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3 mb-md-0" style="width: 20%; flex: 0 0 20%; max-width: 20%;">
            <div class="metric-card m-red">
                <div class="m-label"><i class="fas fa-times-circle mr-1"></i> TOTAL REJECT</div>
                <div class="m-val">{{ number_format($totalNg) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3 mb-md-0" style="width: 20%; flex: 0 0 20%; max-width: 20%;">
            <div class="metric-card m-orange">
                <div class="m-label"><i class="fas fa-undo-alt mr-1"></i> RETURNED RM</div>
                <div class="m-val">{{ number_format($totalRet) }}</div>
            </div>
        </div>
        <div class="col-md-2" style="width: 20%; flex: 0 0 20%; max-width: 20%;">
            <div class="metric-card m-dark">
                <div class="m-label"><i class="fas fa-percentage mr-1"></i> GLOBAL YIELD</div>
                <div class="m-val" style="color:#fff;">{{ number_format($globalPerf, 1) }}%</div>
            </div>
        </div>
    </div>

    {{-- CHART AREA --}}
    @if(!$currentWosSearch)
    <div class="content-panel animate__animated animate__zoomIn">
        <div class="panel-header"><i class="fas fa-chart-area mr-2" style="color:var(--c-blue); font-size: 14px;"></i> DAILY PRODUCTION TREND ANALYSIS</div>
        <div id="trendChart"></div>
    </div>
    @endif

    {{-- DATA TABLE --}}
    <div class="content-panel animate__animated animate__fadeInUp">
        <div class="panel-header mb-3"><i class="fas fa-list-ul mr-2" style="color:var(--c-blue); font-size: 14px;"></i> PRODUCTION BATCH LOGS</div>
        <div class="table-responsive">
            <table class="q-table">
                <thead>
                    <tr>
                        <th class="text-left pl-4">TIME / MACHINE</th>
                        <th class="text-left">NO WOS / BATCH ID</th>
                        <th class="text-left">PART DETAILS</th>
                        <th>TAKE</th>
                        <th style="color:var(--c-green);">OK</th>
                        <th style="color:var(--c-red);">NG</th>
                        <th class="text-left">DEFECT BREAKDOWN & REMARK</th>
                        <th class="pr-4">YIELD</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historyData as $data)
                        @php
                            $yield = $data->qty_masuk > 0 ? round(($data->qty_ok / $data->qty_masuk) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td class="text-left pl-4">
                                <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ date('d/m/y H:i', strtotime($data->updated_at)) }}</div>
                                <div style="font-size: 10px; color:#64748b; margin-top:4px; font-weight: 700;"><i class="fas fa-industry text-primary mr-1"></i> {{ $data->kode_line }} ({{ $data->shift }})</div>
                            </td>
                            <td class="text-left">
                                <!-- ✨ WOS BADGE ✨ -->
                                <div class="wos-badge">
                                    <i class="fas fa-barcode mr-2" style="opacity: 0.5;"></i> {{ strtoupper($data->no_produksi_stamping) }}
                                </div>
                            </td>
                            <td class="text-left">
                                <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ $data->part_no }}</div>
                                <div style="font-size: 9px; color:#64748b; text-transform:uppercase; margin-top:3px; font-weight: 700;">{{ Str::limit($data->part_name ?? 'WELDING PART', 35) }}</div>
                            </td>
                            <td class="t-mono text-muted" style="font-size: 14px;">{{ number_format($data->qty_masuk) }}</td>
                            <td class="t-mono" style="color:var(--c-green); font-size: 14px;">{{ number_format($data->qty_ok) }}</td>
                            <td class="t-mono" style="color:var(--c-red); font-size: 14px;">{{ number_format($data->qty_ng) }}</td>
                            <td class="text-left">
                                @if($data->ng_details && $data->ng_details->count() > 0)
                                    @foreach($data->ng_details as $ng)
                                        <div style="font-size: 10px; margin-bottom:4px;">
                                            <span style="display:inline-block; width:14px; height:14px; background:#fee2e2; color:#dc2626; border-radius:4px; text-align:center; line-height:14px; font-size:9px; font-weight:bold; margin-right:6px;">!</span>
                                            <span style="font-weight: 700; color: #475569;">{{ strtoupper($ng->ng_type) }}:</span> 
                                            <span class="t-mono text-danger ml-1">{{ $ng->qty }}</span>
                                        </div>
                                    @endforeach
                                @endif
                                @if($data->keterangan)
                                    <div style="font-size: 10px; color:#64748b; font-style:italic; margin-top:6px; font-weight: 600;">
                                        <i class="far fa-comment-dots mr-1"></i> {{ $data->keterangan }}
                                    </div>
                                @endif
                                @if(!$data->keterangan && (!$data->ng_details || $data->ng_details->count() == 0))
                                    <span style="font-size:10px; color:#94a3b8; font-style:italic; font-weight: 600;"><i class="fas fa-check mr-1"></i> Good Batch</span>
                                @endif
                            </td>
                            <td class="t-mono pr-4">
                                <span style="background: {{ $yield >= 95 ? '#dcfce7' : ($yield >= 80 ? '#fef3c7' : '#fee2e2') }}; color: {{ $yield >= 95 ? '#16a34a' : ($yield >= 80 ? '#d97706' : '#dc2626') }}; padding: 6px 10px; border-radius: 8px; font-size: 11px;">
                                    {{ $yield }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div style="background: #f1f5f9; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                                    <i class="fas fa-search fa-2x" style="color: #94a3b8;"></i>
                                </div>
                                <h6 class="font-weight-bold text-dark mb-1">Data Tidak Ditemukan</h6>
                                <span class="text-muted" style="font-size: 12px;">Tidak ada riwayat produksi untuk No WOS atau periode tersebut.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if(!$currentWosSearch)
        const dates = {!! $chartDates !!};
        const okData = {!! $chartOk !!};
        const ngData = {!! $chartNg !!};
        const perfData = {!! $chartPerf !!};

        const options = {
            series: [{ name: 'Performance %', type: 'line', data: perfData }, { name: 'OK Units', type: 'area', data: okData }, { name: 'NG Units', type: 'line', data: ngData }],
            chart: { height: 350, type: 'line', fontFamily: 'Plus Jakarta Sans', toolbar: { show: false } },
            colors: ['#3b82f6', '#10b981', '#ef4444'],
            stroke: { width: [3, 2, 2], curve: 'smooth' },
            fill: { type: ['solid', 'gradient', 'solid'], gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05, stops: [0, 100] } },
            xaxis: { categories: dates, labels: { style: { fontWeight: 600, fontSize: '11px', fontFamily: 'JetBrains Mono' } } },
            yaxis: [
                { title: { text: 'Performance %', style: { fontWeight: 700 } }, labels: { style: { fontWeight: 700, fontFamily: 'JetBrains Mono' }, formatter: function(val) { return val + "%" } }, min: 0, max: 100 },
                { opposite: true, title: { text: 'Units (Pcs)', style: { fontWeight: 700 } }, labels: { style: { fontWeight: 700, fontFamily: 'JetBrains Mono' } } },
                { show: false, opposite: true }
            ],
            legend: { position: 'top', horizontalAlign: 'center', fontWeight: 800, markers: { radius: 12 } },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
            tooltip: { theme: 'dark' }
        };

        const chart = new ApexCharts(document.querySelector("#trendChart"), options);
        chart.render();
        @endif
    });
</script>
@endsection