@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root { 
        --ind-steel: #4e73df; --ind-success: #1cc88a; 
        --ind-danger: #e74a3b; --ind-warning: #f6c23e; --ind-info: #36b9cc;
    }
    body { font-family: 'Inter', sans-serif; background-color: #f8f9fc; }
    .industrial-header { font-family: 'Orbitron'; letter-spacing: -1px; }

    /* 📊 STATS CARDS */
    .stat-card { background: #fff; border-radius: 15px; padding: 20px; border: 1px solid #e3e6f0; transition: 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.03); }
    .stat-label { font-size: 10px; font-weight: 800; color: #858796; text-transform: uppercase; }
    .stat-value { font-family: 'Orbitron'; font-size: 22px; font-weight: 800; }

    /* 📅 FILTER BAR */
    .filter-bar { background: #fff; border-radius: 50px; padding: 10px 25px; border: 1px solid #e3e6f0; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .input-date-custom { border: none; font-weight: 700; color: var(--ind-steel); outline: none; }

    /* 📋 TABLE HUD */
    .terminal-card { background: #fff; border: 1px solid #e3e6f0; border-radius: 12px; overflow: hidden; }
    .table-hud thead th { background: #f8f9fc; color: var(--ind-steel); font-size: 10px; text-transform: uppercase; letter-spacing: 1px; padding: 15px; }
    .row-clickable:hover { background-color: #f8faff !important; cursor: pointer; }
    
    @media print { .no-print { display: none !important; } .container-fluid { width: 100%; } }
</style>

<div class="container-fluid py-4">
    
    {{-- 🛰️ 1. TOP COMMAND BAR --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 no-print">
        <div>
            <h3 class="industrial-header font-weight-bold text-dark mb-0">PRODUCTION_AUDIT <span class="text-primary">v4.5</span></h3>
            <p class="text-muted small font-weight-bold uppercase mb-0">Filtered Intelligence Traceability</p>
        </div>
        
        {{-- 📅 DATE RANGE FILTER --}}
        {{-- Bagian baris 42-46 di file history.blade.php Bapak --}}
<form action="{{ route('produksi.history') }}" method="GET" class="filter-bar d-flex align-items-center mt-3 mt-md-0">
    <i class="fas fa-calendar-alt text-muted mr-3"></i>
    <input type="date" name="start_date" value="{{ $startDate }}" class="input-date-custom">
    <span class="mx-3 text-muted font-weight-bold">TO</span>
    <input type="date" name="end_date" value="{{ $endDate }}" class="input-date-custom mr-4">
    <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-sm">
        <i class="fas fa-sync-alt mr-2"></i> SYNC
    </button>
</form>
            <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-sm">
                <i class="fas fa-sync-alt mr-2"></i> SYNC
            </button>
        </form>
    </div>

    {{-- 🛸 2. STATS OVERVIEW --}}
    @php
        $totalAmbil = $history->sum('qty_ambil_pcs');
        $totalOk = $history->sum('qty_hasil_ok');
        $totalNg = $history->sum('qty_hasil_ng');
        $totalRet = $history->sum('qty_return_warehouse');
        $grandTotal = $totalOk + $totalNg;
        $avgYield = $grandTotal > 0 ? ($totalOk / $grandTotal) * 100 : 0;
    @endphp
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card border-left border-primary" style="border-left-width: 5px !important;">
                <div class="stat-label">Material Take</div>
                <div class="stat-value text-primary">{{ number_format($totalAmbil) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="stat-card border-left border-success" style="border-left-width: 5px !important;">
                <div class="stat-label">Passed Good</div>
                <div class="stat-value text-success">{{ number_format($totalOk) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="stat-card border-left border-danger" style="border-left-width: 5px !important;">
                <div class="stat-label">Reject</div>
                <div class="stat-value text-danger">{{ number_format($totalNg) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="stat-card border-left border-info" style="border-left-width: 5px !important;">
                <div class="stat-label">Return to RM</div>
                <div class="stat-value text-info">{{ number_format($totalRet) }}</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card bg-dark text-white">
                <div class="stat-label text-white-50">Yield Accuracy</div>
                <div class="stat-value text-white">{{ number_format($avgYield, 1) }}%</div>
            </div>
        </div>
    </div>

    {{-- 📈 3. CHART & ACTION --}}
    <div class="row mb-4 no-print">
        <div class="col-md-9">
            <div class="terminal-card p-4">
                <h6 class="font-weight-bold text-muted small uppercase mb-4 tracking-widest"><i class="fas fa-chart-line mr-2"></i> Quality Stability Trends</h6>
                <div id="trendChart"></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card d-flex flex-column justify-content-center text-center">
                <i class="fas fa-print fa-3x text-muted mb-3"></i>
                <button onclick="window.print()" class="btn btn-dark btn-block font-weight-bold py-3 rounded-xl mb-2">GENERATE PDF</button>
                <a href="{{ route('produksi.index') }}" class="btn btn-outline-primary btn-block font-weight-bold py-3 rounded-xl">BACK TO TERMINAL</a>
            </div>
        </div>
    </div>

    {{-- 📋 4. TABLE LOG --}}
    <div class="terminal-card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hud mb-0 text-center">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Batch_No</th>
                        <th class="text-left">Part_Identification</th>
                        <th class="bg-light">AMBIL</th>
                        <th>OK</th>
                        <th>NG</th>
                        <th class="text-info">RETURN</th>
                        <th>Yield</th>
                        <th class="text-left">Defect Breakdown & Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $h)
                    @php 
                        $rincian = DB::table('production_ng_logs')->where('no_produksi', $h->no_produksi)->get();
                        $h->specific_ng = $rincian; 
                        $batchOk = (float)$h->qty_hasil_ok;
                        $batchNg = (float)$h->qty_hasil_ng;
                        $batchTotal = $batchOk + $batchNg;
                        $yield = $batchTotal > 0 ? ($batchOk / $batchTotal) * 100 : 0;
                        $color = ($yield >= 95) ? 'var(--ind-success)' : (($yield >= 85) ? 'var(--ind-warning)' : 'var(--ind-danger)');
                    @endphp
                    <tr class="row-clickable" onclick="showDetail({{ json_encode($h) }})">
                        {{-- ✨ Jam Produksi asli (Created_at) --}}
                        <td class="text-dark font-weight-bold" style="font-size: 11px;">
                            {{ date('d/m/y', strtotime($h->created_at)) }}
                            <div class="small text-primary" style="font-family: 'JetBrains Mono';">{{ date('H:i', strtotime($h->created_at)) }}</div>
                        </td>
                        <td class="small font-weight-bold text-muted">{{ $h->no_produksi }}</td>
                        <td class="text-left font-weight-bold">> {{ $h->material_code }}</td>
                        <td class="bg-light font-weight-bold font-mono">{{ number_format($h->qty_ambil_pcs) }}</td>
                        <td class="text-success font-weight-bold font-mono">{{ number_format($batchOk) }}</td>
                        <td class="text-danger font-weight-bold font-mono">{{ number_format($batchNg) }}</td>
                        <td class="text-info font-weight-bold font-mono">{{ number_format($h->qty_return_warehouse) }}</td>
                        <td><b style="color: {{ $color }}; font-family: 'JetBrains Mono';">{{ number_format($yield, 1) }}%</b></td>
                        <td class="text-left">
                            <div class="small italic font-weight-bold text-dark">{{ $h->keterangan ?? '-' }}</div>
                            @if($rincian->count() > 0)
                                <div class="ng-mini-list text-danger small" style="font-family: 'JetBrains Mono';">
                                    [ @foreach($rincian as $r) {{ $r->ng_type }}({{ $r->qty }}) @endforeach ]
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL DETAIL TETAP SAMA (LOGIKANYA) --}}
@include('Produksi.history_modal_logic') 

<script>
    // 📊 CHART LOGIC
    const chartData = @json($history->take(20)->reverse()->values());
    const options = {
        series: [{
            name: 'Batch Yield %',
            data: chartData.map(h => {
                const ok = parseFloat(h.qty_hasil_ok) || 0;
                const ng = parseFloat(h.qty_hasil_ng) || 0;
                return (ok + ng) > 0 ? ((ok / (ok + ng)) * 100).toFixed(1) : 0;
            })
        }],
        chart: { type: 'area', height: 300, toolbar: { show: false } },
        colors: ['#4e73df'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
        xaxis: { categories: chartData.map(h => h.no_produksi.substr(-6)) },
        yaxis: { max: 100, min: 0 }
    };
    new ApexCharts(document.querySelector("#trendChart"), options).render();

    // MODAL DETAIL FUNCTION (Sama seperti sebelumnya)
    function showDetail(h) {
        // ... (Kode showDetail Bapak yang lama) ...
        $('#detailModal').modal('show');
    }
</script>
@endsection