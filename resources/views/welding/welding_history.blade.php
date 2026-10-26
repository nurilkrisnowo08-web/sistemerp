@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-return: #6366f1; --dark-surface: #0f172a; --bg-main: #f8fafc;
        --glass-border: rgba(226, 232, 240, 0.8);
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
        background: #fff; border-radius: 24px; padding: 25px; border: 1px solid var(--glass-border); 
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }
    .stat-card:hover { transform: translateY(-8px); box-shadow: 0 15px 35px rgba(0,0,0,0.06); border-color: var(--brand-primary); }
    
    .stat-label { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; }
    .stat-value { font-family: 'Orbitron'; font-size: 32px; font-weight: 900; line-height: 1; letter-spacing: -1px; }

    /* 📈 LEDGER TABLE GLASS */
    .ledger-container { background: #fff; border-radius: 28px; border: 1px solid var(--glass-border); overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.03); }
    .table-history { margin-bottom: 0; }
    .table-history thead th { background: #f1f5f9; color: #475569; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; padding: 20px 15px; border: none; font-weight: 800; }
    .table-history td { padding: 18px 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 13px; }
    .table-history tbody tr:hover { background-color: #f8fafc; }
    
    .chart-box { background: #fff; border-radius: 28px; padding: 25px; border: 1px solid var(--glass-border); margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
    
    .mono-num { font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 14px; }
    .text-in { color: var(--brand-success); }
    .text-ret { color: var(--brand-return); }
    .text-out { color: var(--brand-danger); }
    
    .btn-cyber { border-radius: 12px; font-weight: 800; font-size: 0.8rem; padding: 10px 20px; letter-spacing: 0.5px; transition: 0.3s; }

    /* 🖨️ PRINT STYLING */
    #printArea { display: none; }
    @media print {
        body * { visibility: hidden; }
        #printArea, #printArea * { visibility: visible; }
        #printArea { display: block !important; position: absolute; left: 0; top: 0; width: 100%; background: white; color: black; padding: 0; font-family: Arial, sans-serif; }
        .no-print { display: none !important; }
        .print-header { border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 15px; text-align: center; }
        .signature-grid { margin-top: 40px; display: flex; justify-content: space-around; text-align: center; }
        .sig-box { width: 30%; font-size: 12px; }
        .stempel-circle { width: 80px; height: 80px; border: 2px solid #000; border-radius: 50%; margin: 15px auto; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; opacity: 0.7; }
        .print-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 11px; }
        .print-table th, .print-table td { border: 1px solid #000; padding: 8px; text-align: center; }
        .print-table th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; }
    }
</style>

{{-- 🛰️ OFFIClAL PRINT TEMPLATE --}}
<div id="printArea">
    <div class="print-header">
        <h1 style="margin:0; font-size: 20px; font-weight: bold;">PT. ASALTA MANDIRI AGUNG</h1>
        <h3 style="margin:5px 0; font-size: 14px; letter-spacing: 1px;">DEPARTEMEN PPIC - WELDING VAULT LEDGER</h3>
        <p style="margin:0; font-size: 11px;">Periode Laporan Mutasi: <strong>{{ date('d/m/Y', strtotime($startDate)) }}</strong> s/d <strong>{{ date('d/m/Y', strtotime($endDate)) }}</strong></p>
    </div>
    
    <table class="print-table">
        <thead>
            <tr>
                <th style="text-align:left; width: 30%;">MATERIAL IDENTIFICATION</th>
                <th style="width: 14%;">SALDO AWAL</th>
                <th style="width: 14%;">IN (STAMPING)</th>
                <th style="width: 14%;">IN (RETURN QC)</th>
                <th style="width: 14%;">OUT (WELDING)</th>
                <th style="width: 14%;">SALDO AKHIR</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historyData as $h)
            <tr>
                <td style="text-align:left;">
                    <strong style="font-family: monospace; font-size: 12px;">{{ $h->part_no }}</strong><br>
                    <span>{{ $h->part_name }}</span>
                </td>
                <td style="font-family: monospace;">{{ number_format($h->stock_awal) }}</td>
                <td style="font-family: monospace;">+{{ number_format($h->in_s) }}</td>
                <td style="font-family: monospace;">+{{ number_format($h->in_r) }}</td>
                <td style="font-family: monospace;">-{{ number_format($h->total_out) }}</td>
                <td style="font-family: monospace; font-weight: bold;">{{ number_format($h->stock_akhir) }}</td>
            </tr>
            @empty
            <tr><td colspan="6">Tidak ada mutasi pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-grid">
        <div class="sig-box">
            <p style="margin-bottom: 5px;">Disiapkan Oleh,</p>
            <div class="stempel-circle" style="border-style: dashed;">LOGISTICS</div>
            <p style="margin-top: 10px; margin-bottom: 0;">( _________________ )</p>
            <span>Admin Gudang</span>
        </div>
        <div class="sig-box">
            <p style="margin-bottom: 5px;">Diperiksa Oleh,</p>
            <div class="stempel-circle" style="border-style: dashed;">QC / QA</div>
            <p style="margin-top: 10px; margin-bottom: 0;">( _________________ )</p>
            <span>Leader QA</span>
        </div>
        <div class="sig-box">
            <p style="margin-bottom: 5px;">Disetujui Oleh,</p>
            <div class="stempel-circle" style="color: red; border-color: red;">APPROVED</div>
            <p style="margin-top: 10px; margin-bottom: 0;">( _________________ )</p>
            <span>Manager PPIC</span>
        </div>
    </div>
    <div style="margin-top: 20px; text-align: center; font-size: 9px; font-style: italic; border-top: 1px solid #000; padding-top: 5px;">
        Document generated by Industrial Core System v3.2 - Print Date: {{ date('d M Y H:i:s') }}
    </div>
</div>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn no-print">
    {{-- 1. HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h1 class="heading-vault mb-1">WELDING_VAULT <span style="-webkit-text-fill-color: var(--dark-surface);">LEDGER</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0"><i class="fas fa-database text-primary mr-2"></i> Rekapitulasi Mutasi Raw Material</p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0 flex-wrap gap-2">
             <form action="" method="GET" class="d-flex mr-2 bg-white p-1 rounded-lg shadow-sm border align-items-center" style="border-radius: 12px;">
                <input type="date" name="start_date" class="form-control form-control-sm border-0 bg-transparent font-weight-bold" value="{{ $startDate }}">
                <span class="px-2 text-muted"><i class="fas fa-arrow-right"></i></span>
                <input type="date" name="end_date" class="form-control form-control-sm border-0 bg-transparent font-weight-bold" value="{{ $endDate }}">
                <button type="submit" class="btn btn-primary btn-sm btn-cyber ml-1"><i class="fas fa-sync-alt"></i></button>
             </form>
             <button onclick="window.print()" class="btn btn-dark btn-cyber mr-2 shadow-sm">
                <i class="fas fa-print mr-1"></i> PRINT LEDGER
             </button>
            <a href="{{ route('welding.index') }}" class="btn btn-outline-secondary btn-cyber bg-white">
                <i class="fas fa-arrow-left mr-1"></i> KEMBALI
            </a>
        </div>
    </div>

    {{-- 2. STATS OVERVIEW --}}
    @php
        $totalInS = $historyData->sum('in_s');
        $totalInR = $historyData->sum('in_r');
        $totalOut = $historyData->sum('total_out');
        $totalAkhir = $historyData->sum('stock_akhir');
    @endphp
    <div class="row mb-4">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="stat-card border-top-0 border-right-0 border-bottom-0" style="border-left: 4px solid var(--brand-success);">
                <span class="stat-label">In (Stamping) <i class="fas fa-arrow-down text-success"></i></span>
                <div class="stat-value text-in">+{{ number_format($totalInS) }}</div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="stat-card border-top-0 border-right-0 border-bottom-0" style="border-left: 4px solid var(--brand-return);">
                <span class="stat-label">In (Return) <i class="fas fa-undo text-ret"></i></span>
                <div class="stat-value text-ret">+{{ number_format($totalInR) }}</div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="stat-card border-top-0 border-right-0 border-bottom-0" style="border-left: 4px solid var(--brand-danger);">
                <span class="stat-label">Out (Welding) <i class="fas fa-fire text-danger"></i></span>
                <div class="stat-value text-out">-{{ number_format($totalOut) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-top-0 border-right-0 border-bottom-0 bg-dark text-white" style="border-left: 4px solid var(--brand-primary);">
                <span class="stat-label text-light">Live WIP Balance <i class="fas fa-boxes text-primary"></i></span>
                <div class="stat-value text-white">{{ number_format($totalAkhir) }}</div>
            </div>
        </div>
    </div>

    {{-- 3. CHART ANALYTICS --}}
    <div class="chart-box animate__animated animate__zoomIn">
        <h6 class="font-weight-black mb-3 text-uppercase text-dark" style="font-size: 12px; letter-spacing: 1px;">
            <i class="fas fa-chart-bar mr-2 text-primary"></i> Mutation Analytics Graph
        </h6>
        <div id="movementChart"></div>
    </div>

    {{-- 4. TABLE MUTASI --}}
    <div class="ledger-container animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-history text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5" style="width: 30%;">Part Identification</th>
                        <th>Opening Balance</th>
                        <th class="text-in">IN (STAMPING)</th>
                        <th class="text-ret">IN (RETURN QC)</th>
                        <th class="text-out">OUT (WELDING)</th>
                        <th class="bg-light text-primary pr-5">Live Balance</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($historyData as $h)
                    <tr>
                        <td class="text-left pl-5">
                            <div class="font-weight-black text-dark" style="font-family: 'JetBrains Mono'; font-size: 14px;">{{ $h->part_no }}</div>
                            <small class="text-muted font-weight-bold uppercase" style="font-size: 10px;">{{ $h->part_name }}</small>
                        </td>
                        <td class="mono-num text-muted">{{ number_format($h->stock_awal) }}</td>
                        <td class="mono-num text-in">+{{ number_format($h->in_s) }}</td>
                        <td class="mono-num text-ret">+{{ number_format($h->in_r) }}</td>
                        <td class="mono-num text-out">-{{ number_format($h->total_out) }}</td>
                        <td class="text-primary font-weight-black bg-light pr-5" style="font-size: 18px; font-family: 'Orbitron';">
                            {{ number_format($h->stock_akhir) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-5 text-muted font-weight-bold italic uppercase">-- Tidak ada aktivitas mutasi pada periode ini --</td></tr>
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
            type: 'bar', height: 320, stacked: true, toolbar: {show:false},
            fontFamily: 'Plus Jakarta Sans'
        },
        colors: ['#10b981', '#6366f1', '#ef4444'],
        plotOptions: { bar: { borderRadius: 6, columnWidth: '35%' } },
        xaxis: { 
            categories: rawData.map(i => i.part_no),
            labels: { style: { fontWeight: 700, fontSize: '11px', fontFamily: 'JetBrains Mono' } }
        },
        yaxis: { labels: { style: { fontWeight: 700 } } },
        legend: { position: 'top', fontWeight: 800, markers: { radius: 12 } },
        tooltip: { theme: 'dark' },
        grid: { borderColor: '#e2e8f0', strokeDashArray: 4 }
    };

    new ApexCharts(document.querySelector("#movementChart"), options).render();
</script>
@endsection