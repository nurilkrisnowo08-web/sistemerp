@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-warning: #f59e0b; --dark-surface: #0f172a; --bg-main: #f1f5f9;
        --glass-border: rgba(255, 255, 255, 0.7);
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #1e293b; }
    
    /* 🛸 CYBER TITLES */
    .heading-vault { 
        font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase;
        background: linear-gradient(90deg, var(--dark-surface) 0%, var(--brand-primary) 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    /* 📊 STAT CARDS INDUSTRIAL */
    .stat-card { 
        background: #fff; border-radius: 28px; padding: 25px; border: 1px solid #e2e8f0; 
        transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; overflow: hidden;
    }
    .stat-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.05); }
    .stat-card::after { content: ""; position: absolute; top: 0; right: 0; width: 80px; height: 80px; background: currentColor; opacity: 0.03; border-radius: 0 0 0 100%; }
    
    .stat-label { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; display: block; }
    .stat-value { font-family: 'Orbitron'; font-size: 28px; font-weight: 900; line-height: 1; }

    /* 📈 LEDGER TABLE GLASS */
    .ledger-container { background: #fff; border-radius: 32px; border: 1px solid var(--glass-border); overflow: hidden; box-shadow: 0 15px 50px rgba(0,0,0,0.03); }
    .table-history thead th { background: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; padding: 22px 15px; border: none; font-weight: 800; }
    .table-history td { padding: 20px 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 14px; transition: 0.2s; }
    .table-history tbody tr:hover { background-color: #fcfcfc; }
    
    .chart-box { background: #fff; border-radius: 32px; padding: 30px; border: 1px solid #e2e8f0; margin-bottom: 35px; }

    /* 🖨️ PRINT STYLING - SECRET LAYOUT */
    #printArea { display: none; }
    @media print {
        body * { visibility: hidden; }
        #printArea, #printArea * { visibility: visible; }
        #printArea { display: block !important; position: absolute; left: 0; top: 0; width: 100%; background: white; padding: 20px; }
        .no-print { display: none !important; }
        .print-header { border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .signature-grid { margin-top: 50px; display: grid; grid-template-columns: repeat(3, 1fr); text-align: center; }
        .sig-box { height: 120px; border: 1px solid #eee; display: flex; flex-direction: column; justify-content: space-between; padding: 10px; }
        .stempel-area { border: 2px dashed #ccc; width: 80px; height: 80px; border-radius: 50%; margin: 5px auto; opacity: 0.3; display: flex; align-items: center; justify-content: center; font-size: 8px; }
    }
</style>

{{-- 🛰️ PRINT TEMPLATE (HIDDEN IN WEB) --}}
<div id="printArea">
    <div class="print-header text-center">
        <h2 style="margin:0; font-family: 'Orbitron', sans-serif;">PT ASALTA MANDIRI AGUNG</h2>
        <h4 style="margin:5px 0; color: #444;">WELDING WIP & RAW MATERIAL LEDGER REPORT</h4>
        <p style="font-size: 12px;">Periode: {{ $startDate }} s/d {{ $endDate }}</p>
    </div>
    
    <table style="width:100%; border-collapse: collapse; margin-bottom: 20px;" border="1">
        <thead>
            <tr style="background: #f0f0f0;">
                <th style="padding: 10px;">Part identification</th>
                <th>Opening</th>
                <th>IN (+)</th>
                <th>OUT (-)</th>
                <th>Closing</th>
            </tr>
        </thead>
        <tbody>
            @foreach($historyData as $h)
            <tr>
                <td style="padding: 8px;"><b>{{ $h->part_no }}</b><br><small>{{ $h->part_name }}</small></td>
                <td style="text-align:center;">{{ number_format($h->stock_awal) }}</td>
                <td style="text-align:center; color: green;">+{{ number_format($h->total_in) }}</td>
                <td style="text-align:center; color: red;">-{{ number_format($h->total_out) }}</td>
                <td style="text-align:center; background:#f9f9f9;"><b>{{ number_format($h->stock_akhir) }}</b></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-grid">
        <div class="sig-box">
            <span>Prepared By,</span>
            <div class="stempel-area">STAFF LOGISTIK</div>
            <span style="text-decoration: underline;">( ............................ )</span>
            <small>Production Admin</small>
        </div>
        <div class="sig-box">
            <span>Verified By,</span>
            <div class="stempel-area">QC PASS</div>
            <span style="text-decoration: underline;">( ............................ )</span>
            <small>QA Leader</small>
        </div>
        <div class="sig-box">
            <span>Approved By,</span>
            <div class="stempel-area">STAMP HERE</div>
            <span style="text-decoration: underline;">( ............................ )</span>
            <small>Manager / SPV</small>
        </div>
    </div>
</div>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn no-print">
    {{-- 1. HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-vault mb-1">WELDING_VAULT <span style="-webkit-text-fill-color: var(--dark-surface);">LEDGER</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0"><i class="fas fa-database text-primary mr-2"></i> RAW MATERIAL PT ASALTA MANDIRI AGUNG</p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
             <form action="" method="GET" class="d-flex mr-3 bg-white p-2 rounded-pill shadow-sm border">
                <input type="date" name="start_date" class="form-control form-control-sm border-0 bg-transparent px-3" value="{{ $startDate }}">
                <div class="px-2 text-muted">➔</div>
                <input type="date" name="end_date" class="form-control form-control-sm border-0 bg-transparent px-3" value="{{ $endDate }}">
                <button type="submit" class="btn btn-primary btn-sm rounded-circle ml-2"><i class="fas fa-sync-alt"></i></button>
             </form>
             <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 font-weight-extrabold mr-2 shadow-lg">
                <i class="fas fa-print mr-2"></i> PRINT
             </button>
            <a href="{{ route('welding.index') }}" class="btn btn-white border rounded-pill px-4 font-weight-extrabold shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> BACK
            </a>
        </div>
    </div>

    {{-- 2. STATS --}}
    @php
        $totalIn = $historyData->sum('total_in');
        $totalOut = $historyData->sum('total_out');
        $totalAkhir = $historyData->sum('stock_akhir');
    @endphp
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="stat-card border-0 shadow-sm text-success">
                <span class="stat-label">Stock Addition (IN)</span>
                <div class="stat-value">+{{ number_format($totalIn) }}</div>
                <div class="mt-2 small font-weight-bold text-muted">Items Received From Stamping</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card border-0 shadow-sm text-danger">
                <span class="stat-label">Stock Deduction (OUT)</span>
                <div class="stat-value">-{{ number_format($totalOut) }}</div>
                <div class="mt-2 small font-weight-bold text-muted">Items Processed in Welding</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card border-0 shadow-sm text-primary">
                <span class="stat-label">Current WIP Inventory</span>
                <div class="stat-value">{{ number_format($totalAkhir) }}</div>
                <div class="mt-2 small font-weight-bold text-muted">Total Available on Rack</div>
            </div>
        </div>
    </div>

    {{-- 3. CHART --}}
    <div class="chart-box shadow-sm animate__animated animate__zoomIn">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="font-weight-black mb-0 text-uppercase tracking-widest text-muted" style="font-size: 11px;">
                <i class="fas fa-chart-line mr-2 text-primary"></i> Stock Movement Analytics
            </h6>
        </div>
        <div id="movementChart"></div>
    </div>

    {{-- 4. TABLE MUTASI --}}
    <div class="ledger-container animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-history mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Material Identification</th>
                        <th>Opening</th>
                        <th class="text-success">Inbound (+)</th>
                        <th class="text-danger">Outbound (-)</th>
                        <th class="bg-light text-primary pr-5">Live Balance</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($historyData as $h)
                    <tr>
                        <td class="text-left pl-5">
                            <div class="font-weight-black text-dark" style="font-family: 'JetBrains Mono';">{{ $h->part_no }}</div>
                            <small class="text-muted font-weight-bold uppercase" style="font-size: 9px;">{{ $h->part_name }}</small>
                        </td>
                        <td class="font-mono text-muted">{{ number_format($h->stock_awal) }}</td>
                        <td class="text-success font-weight-black">
                             <div class="bg-success-soft rounded-pill px-2" style="background: rgba(16, 185, 129, 0.1);">+{{ number_format($h->total_in) }}</div>
                        </td>
                        <td class="text-danger font-weight-black">
                            <div class="bg-danger-soft rounded-pill px-2" style="background: rgba(239, 68, 68, 0.1);">-{{ number_format($h->total_out) }}</div>
                        </td>
                        <td class="text-primary font-weight-black bg-light pr-5" style="font-size: 18px; font-family: 'Orbitron';">
                            {{ number_format($h->stock_akhir) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-5 text-muted font-weight-bold italic uppercase">-- No mutation data found in this period --</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const rawData = @json($historyData->sortByDesc('stock_akhir')->take(10)->values());
    
    const options = {
        series: [
            { name: 'Stock Inbound', data: rawData.map(i => i.total_in) },
            { name: 'Stock Outbound', data: rawData.map(i => i.total_out) }
        ],
        chart: { 
            type: 'bar', 
            height: 350, 
            stacked: false, 
            toolbar: {show:false},
            fontFamily: 'Plus Jakarta Sans'
        },
        dataLabels: { enabled: false },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        colors: ['#10b981', '#ef4444'],
        plotOptions: { bar: { borderRadius: 8, columnWidth: '45%', dataLabels: { position: 'top' } } },
        xaxis: { 
            categories: rawData.map(i => i.part_no),
            labels: { style: { fontWeight: 700, fontSize: '10px' } }
        },
        yaxis: { labels: { style: { fontWeight: 700 } } },
        legend: { position: 'top', fontWeight: 800, textTransform: 'uppercase', fontSize: '11px' },
        fill: { opacity: 1 },
        tooltip: { theme: 'dark' }
    };

    new ApexCharts(document.querySelector("#movementChart"), options).render();
</script>
@endsection