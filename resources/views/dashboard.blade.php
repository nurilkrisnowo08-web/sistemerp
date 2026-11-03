@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root { 
        --app-bg: #f4f7fe;
        --card-bg: #ffffff;
        --text-main: #1e293b;
        --primary: #4318FF;
        --success: #05CD99;
        --danger: #EE5D50;
        --warning: #FFCE20;
    }
    
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--app-bg); color: var(--text-main); }
    
    /* HEADER & FILTERS */
    .dashboard-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 25px; gap: 15px; }
    .title-area h1 { font-weight: 900; letter-spacing: -1px; color: #2b3674; margin: 0; font-size: 28px; text-transform: uppercase; }
    .title-area p { font-size: 12px; font-weight: 700; color: #a3aed1; margin: 2px 0 0 0; }
    
    .filter-card { background: var(--card-bg); border-radius: 14px; padding: 6px 12px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); }
    .filter-card select, .filter-card input { border: none; outline: none; font-weight: 700; color: #2b3674; background: transparent; cursor: pointer; font-size: 13px; font-family: 'Plus Jakarta Sans'; }
    .filter-card select { padding-right: 15px; border-right: 2px solid #e9edf7; }

    /* ALERT TICKER */
    .alert-ticker { background: #fff1f2; border: 1px solid #ffe4e6; border-radius: 12px; padding: 12px 18px; display: flex; align-items: center; gap: 15px; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(225, 29, 72, 0.05); }
    .alert-badge { background: var(--danger); color: #fff; font-size: 11px; font-weight: 800; padding: 4px 12px; border-radius: 6px; letter-spacing: 1px; white-space: nowrap; }
    .ticker-content { color: #be123c; font-size: 13px; font-weight: 700; white-space: nowrap; overflow: hidden; width: 100%; }
    .ticker-content marquee { display: flex; gap: 40px; }

    /* KPI CARDS */
    .kpi-wrapper { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
    .kpi-card { background: var(--card-bg); border-radius: 18px; padding: 22px; box-shadow: 0 5px 14px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 18px; transition: transform 0.2s; border: 1px solid #e2e8f0; }
    .kpi-card:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
    .kpi-icon { width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff; }
    .kpi-info h4 { font-size: 11px; font-weight: 800; color: #a3aed1; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-info h2 { font-size: 26px; font-weight: 900; color: #2b3674; margin: 0; font-family: 'JetBrains Mono', monospace; line-height: 1.1; }

    /* PANELS */
    .b-panel { background: var(--card-bg); border-radius: 20px; padding: 25px; box-shadow: 0 5px 14px rgba(0,0,0,0.02); height: 100%; border: 1px solid #e2e8f0; display: flex; flex-direction: column; }
    .b-panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }
    .b-panel-title { font-size: 14px; font-weight: 900; color: #2b3674; margin: 0; display: flex; align-items: center; gap: 8px; text-transform: uppercase; }

    /* DATA LISTS */
    .data-list { list-style: none; padding: 0; margin: 0; overflow-y: auto; max-height: 280px; }
    .data-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px dashed #e2e8f0; }
    .data-item:last-child { border-bottom: none; }
    .data-part h6 { margin: 0; font-size: 13px; font-weight: 800; color: #1e293b; }
    .data-part small { font-size: 10px; font-weight: 700; color: #94a3b8; }
    .data-val { font-family: 'JetBrains Mono', monospace; font-size: 14px; font-weight: 800; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
    {{-- 1. HEADER & FILTERS --}}
    <div class="dashboard-header">
        <div class="title-area">
            <h1>OVERALL INVENTORY HUB</h1>
            <p>Sistem Pemantauan Gudang FG, RM, WIP & Delivery Terpadu</p>
        </div>
        
        <form id="dashFilter" action="{{ route('dashboard') }}" method="GET" class="filter-card">
            <i class="fas fa-filter text-primary ml-1"></i>
            <select name="customer" onchange="document.getElementById('dashFilter').submit();">
                <option value="">-- SEMUA CUSTOMER --</option>
                @foreach($customersList as $cust)
                    <option value="{{ $cust }}" {{ ($selectedCustomer == $cust) ? 'selected' : '' }}>{{ strtoupper($cust) }}</option>
                @endforeach
            </select>
            
            <i class="far fa-calendar-alt text-primary ml-1"></i>
            <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" onchange="document.getElementById('dashFilter').submit();">
        </form>
    </div>

    {{-- 2. SMART ALERT TICKER (SHORTAGE FG & RM) --}}
    @if(isset($shortageParts) && $shortageParts->count() > 0 || isset($shortageRm) && $shortageRm->count() > 0)
    <div class="alert-ticker">
        <div class="alert-badge"><i class="fas fa-exclamation-circle mr-1"></i> STOK KRITIS</div>
        <div class="ticker-content">
            <marquee behavior="scroll" direction="left" scrollamount="6">
                @if(isset($shortageParts))
                    @foreach($shortageParts as $fg)
                        <span style="margin-right: 50px; color: #be123c;">
                            <b>[FG SHORTAGE]</b> Part {{ $fg->part_no }} ({{ $fg->customer_code }}). Aktual: {{ number_format($fg->actual_stock) }} | Min: {{ number_format($fg->min_stock_pcs) }}
                        </span>
                    @endforeach
                @endif
                @if(isset($shortageRm))
                    @foreach($shortageRm as $rm)
                        <span style="margin-right: 50px; color: #b45309;">
                            <b>[RM CRITICAL]</b> Material {{ $rm->material_code }} (Coil: {{ $rm->coil_id }}). Sisa: {{ number_format($rm->stock_pcs) }} Lbr
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
            {{-- ✨ FIX: Tanda minus (-) pada pengiriman dihilangkan ✨ --}}
            <div class="kpi-info"><h4>Pengiriman Hari Ini</h4><h2 style="color: #d97706;">{{ number_format($todayDelv ?? 0) }}</h2></div>
        </div>
    </div>

    {{-- 4. ROW GRAFIK 1 (FG & PO SISA) --}}
    <div class="row mb-4">
        <div class="col-xl-8 mb-4 mb-xl-0">
            <div class="b-panel">
                <div class="b-panel-header">
                    <h3 class="b-panel-title"><i class="fas fa-chart-column text-primary"></i> Grafik Monitoring Stok FG</h3>
                </div>
                <div id="stockChart" style="flex: 1; min-height: 320px;"></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="b-panel">
                <div class="b-panel-header">
                    <h3 class="b-panel-title"><i class="fas fa-truck text-warning"></i> Sisa Order PO Customer</h3>
                </div>
                <ul class="data-list">
                    @forelse($poSisa ?? [] as $po)
                    <li class="data-item">
                        <div class="data-part">
                            <h6>{{ $po->part_no }}</h6>
                            <small>PO: {{ $po->po_number }} ({{ $po->customer_code }})</small>
                        </div>
                        <div class="data-val text-warning">{{ number_format($po->sisa_qty) }} <span style="font-size: 10px; color: #94a3b8;">Pcs</span></div>
                    </li>
                    @empty
                    <li class="text-center text-muted font-italic mt-4 pt-4" style="font-size: 12px;">Semua PO telah terkirim / Belum ada PO masuk.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- 5. ROW GRAFIK 2 (RM & WIP) --}}
    <div class="row mb-4">
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

    {{-- 6. ROW GRAFIK 3 (DELIVERY TREND) --}}
    <div class="row">
        <div class="col-12">
            <div class="b-panel">
                <div class="b-panel-header">
                    <h3 class="b-panel-title"><i class="fas fa-truck-fast text-success"></i> Delivery Target & Execution (30 Hari)</h3>
                </div>
                <div id="deliveryChart" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. CHART FG: COMBO BAR (AKTUAL) & LINE (MINIMUM)
    const lblParts = @json($labels ?? []);
    const dataActual = @json($actStockData ?? []);
    const dataMin = @json($minStockData ?? []);

    if (lblParts.length > 0) {
        new ApexCharts(document.querySelector("#stockChart"), {
            series: [
                { name: 'Stok Aktual', type: 'column', data: dataActual },
                { name: 'Batas Minimum', type: 'line', data: dataMin }
            ],
            chart: { height: 350, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
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

    // 2. CHART RM: COMBO BAR (AKTUAL) & LINE (MINIMUM = 100)
    const rmLabels = @json($rmLabels ?? []);
    const rmData = @json($rmStockData ?? []);
    const rmMin = @json($rmMinData ?? []);

    if (rmLabels.length > 0) {
        new ApexCharts(document.querySelector("#rmChart"), {
            series: [
                { name: 'Sisa RM', type: 'column', data: rmData },
                { name: 'Batas Kritis', type: 'line', data: rmMin }
            ],
            chart: { height: 280, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
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

    // 3. CHART WIP: COMBO BAR (AKTUAL) & LINE (MINIMUM = 50)
    const wipLabels = @json($wipLabels ?? []);
    const wipData = @json($wipData ?? []);
    const wipMin = @json($wipMinData ?? []);

    if (wipLabels.length > 0) {
        new ApexCharts(document.querySelector("#wipChart"), {
            series: [
                { name: 'Stok WIP', type: 'column', data: wipData },
                { name: 'Buffer Min', type: 'line', data: wipMin }
            ],
            chart: { height: 280, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
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

    // 4. CHART DELIVERY TREND: AREA CHART DENGAN GARIS RATA-RATA
    const delvTrend = @json($deliveryTrend ?? []);
    
    if (delvTrend.length > 0) {
        // Format data untuk Chart
        const formattedData = delvTrend.map(item => {
            return { x: item.date, y: item.total };
        });

        // Hitung nilai rata-rata pengiriman 30 hari untuk garis anotasi
        const totalDelv = formattedData.reduce((acc, curr) => acc + curr.y, 0);
        const avgDelv = (totalDelv / formattedData.length).toFixed(0);

        new ApexCharts(document.querySelector("#deliveryChart"), {
            series: [{ name: "Total Pengiriman Aktual", data: formattedData }],
            chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            stroke: { curve: 'smooth', width: 3 },
            colors: ['#05CD99'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] } },
            xaxis: { 
                type: 'datetime',
                labels: { style: { fontWeight: 700, fontSize: '10px' }, format: 'dd MMM' } 
            },
            yaxis: { labels: { style: { fontWeight: 700, fontFamily: 'JetBrains Mono' } } },
            dataLabels: { enabled: false },
            annotations: {
                yaxis: [{
                    y: avgDelv,
                    borderColor: '#4318FF',
                    strokeDashArray: 5,
                    label: {
                        borderColor: '#4318FF',
                        style: { color: '#fff', background: '#4318FF', fontWeight: 700, fontSize: '10px' },
                        text: 'Rata-rata: ' + avgDelv + ' Pcs'
                    }
                }]
            },
            grid: { strokeDashArray: 4, borderColor: '#e2e8f0' }
        }).render();
    } else {
        document.querySelector("#deliveryChart").innerHTML = "<div class='text-center text-muted font-italic mt-5 pt-4'>Belum ada data pengiriman 30 hari terakhir.</div>";
    }
</script>
@endsection