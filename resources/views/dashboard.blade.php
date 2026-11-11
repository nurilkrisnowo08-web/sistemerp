@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root { 
        --app-bg: #f4f7fe; --card-bg: #ffffff; --text-main: #1e293b;
        --primary: #4318FF; --success: #05CD99; --danger: #EE5D50; --warning: #FFCE20;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--app-bg); color: var(--text-main); }
    
    .dashboard-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 25px; gap: 15px; }
    .title-area h1 { font-weight: 900; letter-spacing: -1px; color: #2b3674; margin: 0; font-size: 28px; text-transform: uppercase; }
    .title-area p { font-size: 12px; font-weight: 700; color: #a3aed1; margin: 2px 0 0 0; }
    
    /* Tombol Refresh Baru Pengganti Filter Lama */
    .btn-refresh { background: var(--primary); color: white; border: none; border-radius: 50rem; font-weight: 800; font-size: 12px; padding: 10px 20px; box-shadow: 0 4px 10px rgba(67, 24, 255, 0.2); transition: all 0.3s ease; }
    .btn-refresh:hover { background: #2b12a8; transform: translateY(-2px); }

    .alert-ticker { background: #fff5f5; border: 1px solid #fecaca; border-radius: 12px; padding: 12px 20px; display: flex; align-items: center; gap: 15px; margin-bottom: 25px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
    .alert-badge { background: #ef4444; color: #fff; font-size: 11px; font-weight: 900; padding: 6px 12px; border-radius: 6px; letter-spacing: 0.5px; white-space: nowrap; }
    .ticker-content { font-size: 13px; font-weight: 700; white-space: nowrap; overflow: hidden; width: 100%; margin-top: 2px; }

    .kpi-wrapper { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
    .kpi-card { background: var(--card-bg); border-radius: 18px; padding: 22px; box-shadow: 0 5px 14px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 18px; border: 1px solid #e2e8f0; }
    .kpi-icon { width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff; }
    .kpi-info h4 { font-size: 11px; font-weight: 800; color: #a3aed1; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-info h2 { font-size: 26px; font-weight: 900; color: #2b3674; margin: 0; font-family: 'JetBrains Mono', monospace; line-height: 1.1; }

    .b-panel { background: var(--card-bg); border-radius: 20px; padding: 25px; box-shadow: 0 5px 14px rgba(0,0,0,0.02); height: 100%; border: 1px solid #e2e8f0; display: flex; flex-direction: column; }
    .b-panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }
    .b-panel-title { font-size: 14px; font-weight: 900; color: #2b3674; margin: 0; display: flex; align-items: center; gap: 8px; text-transform: uppercase; }

    .wos-table-wrapper { max-height: 420px; overflow-y: auto; overflow-x: auto; border-radius: 8px; border: 1px solid #e2e8f0; padding-bottom: 15px; }
    .wos-table-wrapper::-webkit-scrollbar { width: 8px; height: 8px; }
    .wos-table-wrapper::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; }
    .wos-table-wrapper::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
    .wos-table-wrapper::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    .table-wos-delv { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 800px; }
    .table-wos-delv th { background: #f8fafc; font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; padding: 14px 15px; border-bottom: 2px solid #e2e8f0; position: sticky; top: 0; z-index: 10; box-shadow: 0 2px 4px -2px rgba(0,0,0,0.1); }
    .table-wos-delv td { padding: 14px 15px; font-size: 12px; font-weight: 700; color: #1e293b; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .badge-cust { background: #e2e8f0; color: #475569; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; letter-spacing: 0.5px; white-space: nowrap; }
    .judge-ok { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 900; letter-spacing: 0.5px; white-space: nowrap; }
    .judge-short { background: #fee2e2; color: #b91c1c; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 900; letter-spacing: 0.5px; white-space: nowrap; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
    {{-- 1. HEADER & ACTIONS ✨ --}}
    <div class="dashboard-header animate__animated animate__fadeInDown">
        <div class="title-area">
            <h1>OVERALL INVENTORY HUB</h1>
            <p>Sistem Pemantauan Gudang FG, RM, WIP & Delivery Terpadu</p>
        </div>
        
        {{-- FILTER LAMA DIHAPUS, DIGANTI TOMBOL REFRESH --}}
        <div>
            <button class="btn-refresh" onclick="location.reload()">
                <i class="fas fa-sync-alt mr-1"></i> REFRESH DATA
            </button>
        </div>
    </div>

    {{-- 2. SMART ALERT TICKER ✨ --}}
    @if(isset($shortageParts) && $shortageParts->count() > 0 || isset($shortageRm) && $shortageRm->count() > 0)
    <div class="alert-ticker">
        <div class="alert-badge"><i class="fas fa-exclamation-circle mr-1"></i> STOK KRITIS</div>
        <div class="ticker-content">
            <marquee behavior="scroll" direction="left" scrollamount="6">
                @if(isset($shortageParts))
                    @foreach($shortageParts as $fg)
                        <span style="margin-right: 50px; color: #b91c1c;">
                            ⚠️ <b>[FG SHORTAGE]</b> Part: {{ $fg->part_no }} ({{ $fg->customer_code }}). Aktual: {{ number_format($fg->actual_stock) }} | Min: {{ number_format($fg->min_stock_pcs) }}
                        </span>
                    @endforeach
                @endif
                @if(isset($shortageRm))
                    @foreach($shortageRm as $rm)
                        <span style="margin-right: 50px; color: #b45309;">
                            ⚠️ <b>[RM CRITICAL]</b> Material: {{ $rm->material_code }} (Coil: {{ $rm->coil_id }}). Sisa: {{ number_format($rm->stock_pcs) }} Lbr
                        </span>
                    @endforeach
                @endif
            </marquee>
        </div>
    </div>
    @endif

    {{-- 3. KPI CARDS --}}
    <div class="kpi-wrapper">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #4318FF, #868CFF);"><i class="fas fa-boxes"></i></div>
            <div class="kpi-info"><h4>Total FG Terdaftar</h4><h2>{{ number_format($totalParts ?? 0) }}</h2></div>
        </div>
        <div class="kpi-card" style="{{ ($critCount ?? 0) > 0 ? 'border-color: #fecaca; background: #fff1f2;' : '' }}">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #EE5D50, #ff8a7d);"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="kpi-info"><h4>Part FG Kritis</h4><h2 style="color: var(--danger);">{{ number_format($critCount ?? 0) }}</h2></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #05CD99, #02A87B);"><i class="fas fa-industry"></i></div>
            <div class="kpi-info"><h4>Produksi Hari Ini</h4><h2 style="color: var(--success);">+{{ number_format($todayProd ?? 0) }}</h2></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #FFCE20, #FF9B00);"><i class="fas fa-truck-loading"></i></div>
            <div class="kpi-info"><h4>Pengiriman Hari Ini</h4><h2 style="color: #d97706;">{{ number_format($todayDelv ?? 0) }}</h2></div>
        </div>
    </div>

    {{-- 4. ROW WOS PRODUKSI & WOS DELIVERY --}}
    <div class="row mb-4">
        <div class="col-xl-6 mb-4 mb-xl-0">
            <div class="b-panel" style="background: #0f172a; color: #fff; border-radius: 12px; border:none; padding: 20px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div style="background:#fff; border-radius:8px; padding:8px;"><i class="fas fa-boxes text-primary" style="font-size:24px;"></i></div>
                        <div>
                            <h3 class="m-0" style="color:#fff; font-size:20px; font-weight:900;">WOS DELIVERY LOGISTICS</h3>
                            <p class="m-0" style="color:#94a3b8; font-size:11px;">Target Pengiriman vs Surat Jalan Aktual (Hari Ini)</p>
                        </div>
                    </div>
                </div>
                
                <div class="wos-table-wrapper bg-white">
                    <table class="table-wos-delv">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>Part Name / No</th>
                                <th>Cust</th>
                                <th class="text-center">Target (Pcs)</th>
                                <th class="text-center">Actual (SJ)</th>
                                <th class="text-center">Stock FG</th>
                                <th class="text-center">Judge</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wosDeliveryData ?? [] as $idx => $wd)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td><b style="white-space: nowrap;">{{ $wd->part_name }}</b><br><span style="font-size:9px; color:#64748b;">{{ $wd->part_no }}</span></td>
                                <td><span class="badge-cust">{{ $wd->customer_code }}</span></td>
                                <td class="text-center font-weight-bold">{{ number_format($wd->total_target) }}</td>
                                <td class="text-center font-weight-bold" style="color:#4318FF;">{{ number_format($wd->actual_s1 + $wd->actual_s2) }}</td>
                                <td class="text-center font-weight-bold" style="color:#05CD99;">{{ number_format($wd->fg_rak) }}</td>
                                <td class="text-center">
                                    <span class="{{ $wd->status_judge == 'OK' ? 'judge-ok' : 'judge-short' }}"><i class="fas {{ $wd->status_judge == 'OK' ? 'fa-check' : 'fa-exclamation-triangle' }} mr-1"></i>{{ $wd->status_judge }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted font-italic py-5">Belum ada WOS Delivery di tanggal ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="b-panel">
                <div class="b-panel-header">
                    <h3 class="b-panel-title"><i class="fas fa-bullseye text-success"></i> Aktual vs WOS Plan Produksi</h3>
                    <span style="font-size:10px; font-weight:bold; background:#e2e8f0; padding:4px 8px; border-radius:6px; color:#475569;">{{ strtoupper($areaFilter ?? 'ALL') }}</span>
                </div>
                <div id="prodChart" style="flex: 1; min-height: 280px;"></div>
            </div>
        </div>
    </div>

    {{-- 5. ROW GRAFIK INVENTORY (FG & DELIVERY TREND HARI INI) --}}
    <div class="row mb-4">
        <div class="col-xl-6 mb-4 mb-xl-0">
            <div class="b-panel">
                <div class="b-panel-header">
                    <h3 class="b-panel-title"><i class="fas fa-chart-column text-primary"></i> Monitoring Stok FG</h3>
                </div>
                <div id="stockChart" style="flex: 1; min-height: 300px;"></div>
            </div>
        </div>
        
        <div class="col-xl-6">
            <div class="b-panel">
                <div class="b-panel-header">
                    <h3 class="b-panel-title"><i class="fas fa-truck-fast text-success"></i> Pengiriman Aktual vs Target WOS (Hari Ini)</h3>
                </div>
                <div id="deliveryChart" style="flex: 1; min-height: 300px;"></div>
            </div>
        </div>
    </div>

    {{-- 6. ROW GRAFIK INVENTORY (RM & WIP) --}}
    <div class="row">
        <div class="col-md-6 mb-4 mb-md-0">
            <div class="b-panel">
                <div class="b-panel-header">
                    <h3 class="b-panel-title"><i class="fas fa-pallet text-warning"></i> Grafik Raw Material (Lembar)</h3>
                </div>
                <div id="rmChart" style="min-height: 280px;"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="b-panel">
                <div class="b-panel-header">
                    <h3 class="b-panel-title"><i class="fas fa-fire text-danger"></i> Grafik WIP Welding (Pcs)</h3>
                </div>
                <div id="wipChart" style="min-height: 280px;"></div>
            </div>
        </div>
    </div>

</div>

<script>
    const commonFont = 'Plus Jakarta Sans';

    // 1. CHART PRODUKSI (WOS PLAN VS ACTUAL OK & NG)
    const prodLbl = @json($prodLabels ?? []);
    const prodAct = @json($prodActuals ?? []);
    const prodNg  = @json($prodNgs ?? []);
    const prodTar = @json($prodTargets ?? []);

    if (prodLbl.length > 0) {
        new ApexCharts(document.querySelector("#prodChart"), {
            series: [
                { name: 'Actual OK', type: 'column', data: prodAct },
                { name: 'Actual NG', type: 'column', data: prodNg },
                { name: 'Target WOS', type: 'line', data: prodTar }
            ],
            chart: { height: 320, toolbar: { show: false }, fontFamily: commonFont },
            stroke: { width: [0, 0, 4], curve: 'smooth' },
            plotOptions: { bar: { columnWidth: '60%', borderRadius: 4 } },
            colors: ['#05CD99', '#EE5D50', '#4318FF'], 
            dataLabels: { enabled: false },
            xaxis: { categories: prodLbl, labels: { style: { fontWeight: 700, fontSize: '10px' } } },
            yaxis: { labels: { style: { fontWeight: 800, fontFamily: 'JetBrains Mono' } } },
            legend: { position: 'top', fontWeight: 700 },
            grid: { strokeDashArray: 4, borderColor: '#e2e8f0' }
        }).render();
    } else {
        document.querySelector("#prodChart").innerHTML = "<div class='text-center text-muted font-italic mt-5 pt-5'>Belum ada Plan WOS di area ini.</div>";
    }

    // 2. CHART DELIVERY HARI INI (BAR CHART) ✨
    const delvLbl = @json($deliveryChartLabels ?? []);
    const delvAct = @json($deliveryChartActual ?? []);
    const delvTar = @json($deliveryChartTarget ?? []);

    if (delvLbl.length > 0) {
        new ApexCharts(document.querySelector("#deliveryChart"), {
            series: [
                { name: "Aktual Pengiriman (SJ)", type: 'column', data: delvAct },
                { name: "Target WOS Delivery", type: 'column', data: delvTar }
            ],
            chart: { height: 320, toolbar: { show: false }, fontFamily: commonFont },
            plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
            stroke: { show: true, width: 2, colors: ['transparent'] },
            colors: ['#05CD99', '#4318FF'],
            dataLabels: { enabled: false },
            xaxis: { categories: delvLbl, labels: { style: { fontWeight: 700, fontSize: '10px' } } },
            yaxis: { labels: { style: { fontWeight: 700, fontFamily: 'JetBrains Mono' } } },
            legend: { position: 'top', fontWeight: 700 },
            grid: { strokeDashArray: 4, borderColor: '#e2e8f0' }
        }).render();
    } else {
        document.querySelector("#deliveryChart").innerHTML = "<div class='text-center text-muted font-italic mt-5 pt-5'>Belum ada data WOS Delivery atau pengiriman hari ini.</div>";
    }

    // 3. CHART FG: COMBO BAR (AKTUAL) & LINE (MINIMUM)
    const lblParts = @json($labels ?? []);
    const dataActual = @json($actStockData ?? []);
    const dataMin = @json($minStockData ?? []);

    if (lblParts.length > 0) {
        new ApexCharts(document.querySelector("#stockChart"), {
            series: [
                { name: 'Stok Aktual', type: 'column', data: dataActual },
                { name: 'Batas Minimum', type: 'line', data: dataMin }
            ],
            chart: { height: 320, toolbar: { show: false }, fontFamily: commonFont },
            stroke: { width: [0, 4], curve: 'smooth' },
            plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
            colors: ['#4318FF', '#EE5D50'],
            dataLabels: { enabled: false },
            xaxis: { categories: lblParts, labels: { style: { fontWeight: 700, fontSize: '10px' } } },
            yaxis: { labels: { style: { fontWeight: 800, fontFamily: 'JetBrains Mono' } } },
            legend: { position: 'top', fontWeight: 700 },
            grid: { strokeDashArray: 4, borderColor: '#e2e8f0' }
        }).render();
    } else {
        document.querySelector("#stockChart").innerHTML = "<div class='text-center text-muted font-italic mt-5 pt-5'>Tidak ada data stok FG.</div>";
    }

    // 4. CHART RM: COMBO BAR (AKTUAL) & LINE (MINIMUM)
    const rmLabels = @json($rmLabels ?? []);
    const rmData = @json($rmStockData ?? []);
    const rmMin = @json($rmMinData ?? []);

    if (rmLabels.length > 0) {
        new ApexCharts(document.querySelector("#rmChart"), {
            series: [
                { name: 'Sisa RM', type: 'column', data: rmData },
                { name: 'Batas Kritis', type: 'line', data: rmMin }
            ],
            chart: { height: 280, toolbar: { show: false }, fontFamily: commonFont },
            stroke: { width: [0, 3], curve: 'straight', dashArray: [0, 5] },
            plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
            colors: ['#FFCE20', '#EE5D50'],
            dataLabels: { enabled: false },
            xaxis: { categories: rmLabels, labels: { style: { fontWeight: 700, fontSize: '9px' } } },
            yaxis: { labels: { style: { fontWeight: 800, fontFamily: 'JetBrains Mono' } } },
            legend: { position: 'top', fontWeight: 700 },
            grid: { strokeDashArray: 4, borderColor: '#e2e8f0' }
        }).render();
    } else {
        document.querySelector("#rmChart").innerHTML = "<div class='text-center text-muted font-italic mt-5 pt-4'>Tidak ada data stok RM.</div>";
    }

    // 5. CHART WIP: COMBO BAR (AKTUAL) & LINE (MINIMUM)
    const wipLabels = @json($wipLabels ?? []);
    const wipData = @json($wipData ?? []);
    const wipMin = @json($wipMinData ?? []);

    if (wipLabels.length > 0) {
        new ApexCharts(document.querySelector("#wipChart"), {
            series: [
                { name: 'Stok WIP', type: 'column', data: wipData },
                { name: 'Buffer Min', type: 'line', data: wipMin }
            ],
            chart: { height: 280, toolbar: { show: false }, fontFamily: commonFont },
            stroke: { width: [0, 3], curve: 'straight', dashArray: [0, 5] },
            plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
            colors: ['#EE5D50', '#a3aed1'],
            dataLabels: { enabled: false },
            xaxis: { categories: wipLabels, labels: { style: { fontWeight: 700, fontSize: '9px' } } },
            yaxis: { labels: { style: { fontWeight: 800, fontFamily: 'JetBrains Mono' } } },
            legend: { position: 'top', fontWeight: 700 },
            grid: { strokeDashArray: 4, borderColor: '#e2e8f0' }
        }).render();
    } else {
        document.querySelector("#wipChart").innerHTML = "<div class='text-center text-muted font-italic mt-5 pt-4'>Tidak ada data stok WIP.</div>";
    }
</script>
@endsection