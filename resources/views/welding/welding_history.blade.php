@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-return: #6366f1; --dark-surface: #0f172a; --bg-main: #f1f5f9;
        --glass-border: rgba(255, 255, 255, 0.7);
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #1e293b; }
    
    /* 🛸 CYBER TITLES */
    .heading-vault { 
        font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase;
        background: linear-gradient(90deg, var(--dark-surface) 0%, var(--brand-primary) 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    /* 📊 STAT CARDS */
    .stat-card { 
        background: #fff; border-radius: 28px; padding: 25px; border: 1px solid #e2e8f0; 
        transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; overflow: hidden;
    }
    .stat-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.05); }
    
    .stat-label { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; display: block; }
    .stat-value { font-family: 'Orbitron'; font-size: 28px; font-weight: 900; line-height: 1; }

    /* 📈 LEDGER TABLE GLASS */
    .ledger-container { background: #fff; border-radius: 32px; border: 1px solid var(--glass-border); overflow: hidden; box-shadow: 0 15px 50px rgba(0,0,0,0.03); }
    .table-history thead th { background: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; padding: 22px 15px; border: none; font-weight: 800; }
    .table-history td { padding: 20px 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 14px; }
    
    .chart-box { background: #fff; border-radius: 32px; padding: 30px; border: 1px solid #e2e8f0; margin-bottom: 35px; }

    /* 🖨️ PRINT STYLING */
    #printArea { display: none; }
    @media print {
        body * { visibility: hidden; }
        #printArea, #printArea * { visibility: visible; }
        #printArea { display: block !important; position: absolute; left: 0; top: 0; width: 100%; background: white; color: black; padding: 0; }
        .no-print { display: none !important; }
        .print-header { border-bottom: 4px double #000; margin-bottom: 20px; padding-bottom: 15px; text-align: center; }
        .signature-grid { margin-top: 50px; display: flex; justify-content: space-around; text-align: center; }
        .sig-box { width: 200px; }
        .stempel-circle { width: 90px; height: 90px; border: 2px dashed #000; border-radius: 50%; margin: 10px auto; display: flex; align-items: center; justify-content: center; font-size: 9px; opacity: 0.5; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black !important; padding: 8px !important; text-align: center !important; font-size: 11px !important; }
        th { background-color: #f2f2f2 !important; }
    }
</style>

{{-- 🛰️ OFFIClAL PRINT TEMPLATE --}}
<div id="printArea">
    <div class="print-header">
        <h1 style="margin:0; font-size: 24px;">PT ASALTA MANDIRI AGUNG</h1>
        <h3 style="margin:5px 0; letter-spacing: 2px;">WELDING WIP</h3>
        <p style="margin:0; font-size: 12px; font-weight: bold;">Periode Laporan: {{ $startDate }} s/d {{ $endDate }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="text-align:left !important;">Material identification</th>
                <th>Opening</th>
                <th>IN (STAMP)</th>
                <th>IN (RETURN)</th>
                <th>OUT (WELD)</th>
                <th>actual stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($historyData as $h)
            <tr>
                <td style="text-align:left !important;"><b>{{ $h->part_no }}</b><br>{{ $h->part_name }}</td>
                <td>{{ number_format($h->stock_awal) }}</td>
                <td>+{{ number_format($h->in_s) }}</td>
                <td>+{{ number_format($h->in_r) }}</td>
                <td>-{{ number_format($h->total_out) }}</td>
                <td><b>{{ number_format($h->stock_akhir) }}</b></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-grid">
        <div class="sig-box">
            <p>Prepared By,</p>
            <div class="stempel-circle">LOGISTICS</div>
            <p style="margin-top:40px;">( _________________ )</p>
            <small>Production Admin</small>
        </div>
        <div class="sig-box">
            <p>Verified By,</p>
            <div class="stempel-circle">QC / QA</div>
            <p style="margin-top:40px;">( _________________ )</p>
            <small>QA Leader</small>
        </div>
        <div class="sig-box">
            <p>Authorized By,</p>
            <div class="stempel-circle" style="border-style: solid; color: red; border-color: red;">STAMP HERE</div>
            <p style="margin-top:40px;">( _________________ )</p>
            <small>Manager Production</small>
        </div>
    </div>
    <div style="margin-top: 30px; text-align: center; font-size: 10px; font-style: italic; border-top: 1px solid #ddd; padding-top: 10px;">
        Document generated by Industrial Core System - Raw Material PT Asalta Mandiri Agung
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
                <i class="fas fa-print mr-2"></i> PRINT_RECAP
             </button>
            <a href="{{ route('welding.index') }}" class="btn btn-white border rounded-pill px-4 font-weight-extrabold shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> BACK
            </a>
        </div>
    </div>

    {{-- 2. STATS --}}
    @php
        $totalInS = $historyData->sum('in_s');
        $totalInR = $historyData->sum('in_r');
        $totalOut = $historyData->sum('total_out');
        $totalAkhir = $historyData->sum('stock_akhir');
    @endphp
    <div class="row mb-5">
        <div class="col-md-3">
            <div class="stat-card border-0 shadow-sm text-success">
                <span class="stat-label">In (Stamping)</span>
                <div class="stat-value">+{{ number_format($totalInS) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-0 shadow-sm" style="color: var(--brand-return);">
                <span class="stat-label">In (Return)</span>
                <div class="stat-value">+{{ number_format($totalInR) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-0 shadow-sm text-danger">
                <span class="stat-label">Out (Welding)</span>
                <div class="stat-value">-{{ number_format($totalOut) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-0 shadow-sm text-primary">
                <span class="stat-label">Live WIP Balance</span>
                <div class="stat-value">{{ number_format($totalAkhir) }}</div>
            </div>
        </div>
    </div>

    {{-- 3. CHART --}}
    <div class="chart-box shadow-sm animate__animated animate__zoomIn">
        <h6 class="font-weight-black mb-4 text-uppercase tracking-widest text-muted" style="font-size: 11px;">
            <i class="fas fa-chart-line mr-2 text-primary"></i> Mutation Analytics
        </h6>
        <div id="movementChart"></div>
    </div>

    {{-- 4. TABLE MUTASI --}}
    <div class="ledger-container animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-history mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Part identification</th>
                        <th>Opening</th>
                        <th class="text-success">IN (STAMP)</th>
                        <th style="color: var(--brand-return);">IN (RET)</th>
                        <th class="text-danger">OUT (WELD)</th>
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
                        <td class="text-success font-weight-black">+{{ number_format($h->in_s) }}</td>
                        <td class="font-weight-black" style="color: var(--brand-return);">+{{ number_format($h->in_r) }}</td>
                        <td class="text-danger font-weight-black">-{{ number_format($h->total_out) }}</td>
                        <td class="text-primary font-weight-black bg-light pr-5" style="font-size: 18px; font-family: 'Orbitron';">
                            {{ number_format($h->stock_akhir) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-5 text-muted font-weight-bold italic uppercase">-- Zero Records in Period --</td></tr>
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
            { name: 'Stamping In', data: rawData.map(i => i.in_s) },
            { name: 'Return In', data: rawData.map(i => i.in_r) },
            { name: 'Welding Out', data: rawData.map(i => i.total_out) }
        ],
        chart: { 
            type: 'bar', height: 350, stacked: true, toolbar: {show:false},
            fontFamily: 'Plus Jakarta Sans'
        },
        colors: ['#10b981', '#6366f1', '#ef4444'],
        plotOptions: { bar: { borderRadius: 8, columnWidth: '40%' } },
        xaxis: { categories: rawData.map(i => i.part_no) },
        legend: { position: 'top', fontWeight: 800 },
        tooltip: { theme: 'dark' }
    };

    new ApexCharts(document.querySelector("#movementChart"), options).render();
</script>
@endsection