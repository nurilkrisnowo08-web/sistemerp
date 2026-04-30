@extends('layout.admin')

@section('content')
{{-- ✨ PERHITUNGAN GLOBAL --}}
@php
    $totalTake = $history->sum('qty_ambil_pcs');
    $totalOk = $history->sum('qty_hasil_ok');
    $totalNg = $history->sum('qty_hasil_ng');
    $totalRet = $history->sum('qty_return_warehouse');
    $performance = ($totalTake - $totalRet) > 0 ? ($totalOk / ($totalTake - $totalRet)) * 100 : 0;
@endphp

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;900&family=JetBrains+Mono:wght@500;800&family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root { 
        --dragon-blue: #4361ee; --dragon-green: #10b981; 
        --dragon-red: #ef4444; --dragon-gold: #f59e0b;
        --dragon-dark: #0f172a; --dragon-glass: rgba(255, 255, 255, 0.9);
    }
    
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
    .heading-cyber { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; }

    /* 📊 STAT CARDS DRAGON STYLE */
    .stat-card { background: #fff; border-radius: 24px; padding: 25px; border: 1px solid rgba(0,0,0,0.05); transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; overflow: hidden; }
    .stat-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(67, 97, 238, 0.15); }
    .stat-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 2px; }
    .stat-value { font-family: 'Orbitron'; font-size: 28px; font-weight: 900; line-height: 1.2; }
    .card-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; }

    /* 🏛️ MODAL DRAGON ENGINE DESIGN */
    .modal-dragon { border-radius: 40px !important; border: none !important; box-shadow: 0 0 50px rgba(0,0,0,0.2); }
    .modal-dragon .modal-header { 
        background: linear-gradient(135deg, var(--dragon-dark) 0%, #1e293b 100%); 
        border-radius: 40px 40px 0 0; padding: 30px 40px; border: none;
        position: relative;
    }
    .modal-dragon .modal-header::after {
        content: ""; position: absolute; bottom: 0; left: 0; width: 100%; height: 2px;
        background: linear-gradient(90deg, transparent, var(--dragon-blue), transparent);
    }
    
    .yield-display { background: #f8fafc; border-radius: 30px; padding: 25px; border: 1px solid #e2e8f0; position: relative; }
    .ng-entry-pill { 
        background: white; border-radius: 18px; padding: 15px 20px; margin-bottom: 12px;
        border: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;
        transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
    .ng-entry-pill:hover { border-color: var(--dragon-red); background: #fff1f2; transform: scale(1.02); }
    .ng-type-label { font-weight: 800; color: var(--dragon-dark); text-transform: uppercase; font-size: 12px; }
    .ng-qty-badge { background: var(--dragon-red); color: white; padding: 5px 15px; border-radius: 10px; font-family: 'JetBrains Mono'; font-weight: 800; }

    /* 📋 TABLE HUD */
    .table-hud thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; padding: 20px; border: none; font-weight: 800; }
    .row-clickable { transition: 0.2s; }
    .row-clickable:hover { background-color: #f8fafc !important; cursor: pointer; }

    /* 🖨️ PRINT UI */
    .print-template { display: none; }
    @media print {
        .no-print, .main-sidebar, .main-header, .btn, .filter-bar, .modal { display: none !important; }
        .print-template { display: block !important; }
        @page { size: A4 landscape; margin: 10mm; }
    }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">

    {{-- 🛰️ HEADER SCREEN --}}
    <div class="screen-only">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 no-print">
            <div>
                <h2 class="heading-cyber m-0">DRAGON_AUDIT <span class="text-primary">v7.2</span></h2>
                <p class="text-muted small font-weight-bold mb-0 uppercase tracking-tighter"><i class="fas fa-dragon text-primary mr-2"></i> Legacy Traceability System // PT AMA</p>
            </div>
            <form action="{{ route('produksi.history') }}" method="GET" class="filter-bar d-flex align-items-center shadow-sm p-3 bg-white rounded-2xl border">
                <i class="fas fa-calendar-alt text-primary mr-3 ml-2"></i>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm border-0 bg-light rounded-pill px-3 mr-2">
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm border-0 bg-light rounded-pill px-3">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold ml-3 mr-2">SYNC_CORE</button>
            </form>
        </div>

        {{-- STATS GRID --}}
        <div class="row mb-5">
            <div class="col-md-3 col-6 mb-3"><div class="stat-card"><div class="card-strip bg-primary"></div><div class="stat-label">Material Take</div><div class="stat-value text-primary">{{ number_format($totalTake) }}</div></div></div>
            <div class="col-md-2 col-4 mb-3"><div class="stat-card"><div class="card-strip bg-success"></div><div class="stat-label">Verified OK</div><div class="stat-value text-success">{{ number_format($totalOk) }}</div></div></div>
            <div class="col-md-2 col-4 mb-3"><div class="stat-card"><div class="card-strip bg-danger"></div><div class="stat-label">Total Reject</div><div class="stat-value text-danger">{{ number_format($totalNg) }}</div></div></div>
            <div class="col-md-2 col-4 mb-3"><div class="stat-card"><div class="card-strip bg-warning"></div><div class="stat-label">Return RM</div><div class="stat-value text-warning">{{ number_format($totalRet) }}</div></div></div>
            <div class="col-md-3 col-12 mb-3"><div class="stat-card bg-dark text-white"><div class="stat-label text-white-50">Yield Performance</div><div class="stat-value text-white">{{ number_format($performance, 1) }}%</div></div></div>
        </div>
    </div>

    {{-- 📈 SPLINE CHART --}}
    <div class="stat-card p-4 mb-5 no-print animate__animated animate__zoomIn">
        <h6 class="font-weight-black text-muted small uppercase mb-4 tracking-widest"><i class="fas fa-chart-line mr-2 text-primary"></i> Dragon Spline Performance Matrix</h6>
        <div id="trendChart"></div>
    </div>

    {{-- 📋 HISTORY TABLE --}}
    <div class="stat-card shadow-lg p-0 mb-5 animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-hud mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Timestamp</th>
                        <th>Batch_No</th>
                        <th class="text-left">Part Identification</th>
                        <th>Take</th>
                        <th class="text-success">OK</th>
                        <th class="text-danger">NG</th>
                        <th>Yield</th>
                        <th class="text-left">Cumulative NG Breakdown</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $h)
                    @php 
                        $rincian = DB::table('production_ng_logs')
                                    ->where('no_produksi', $h->no_produksi)
                                    ->select('ng_type', DB::raw('SUM(qty) as qty'))
                                    ->groupBy('ng_type')
                                    ->get();
                        $batchOk = (float)$h->qty_hasil_ok;
                        $batchNg = (float)$h->qty_hasil_ng;
                        $yield = ($batchOk + $batchNg) > 0 ? ($batchOk / ($batchOk + $batchNg)) * 100 : 0;
                        $color = ($yield >= 95) ? '#10b981' : (($yield >= 85) ? '#f59e0b' : '#ef4444');
                    @endphp
                    <tr class="row-clickable" onclick="showDeepDive({{ json_encode($h) }}, {{ json_encode($rincian) }})">
                        <td class="text-left pl-5">
                            <div class="font-weight-bold text-dark" style="font-size: 11px;">{{ date('d M Y', strtotime($h->created_at)) }}</div>
                            <div class="small text-muted font-weight-bold" style="font-family: 'JetBrains Mono';">{{ date('H:i', strtotime($h->created_at)) }}</div>
                        </td>
                        <td class="small font-weight-bold text-primary">{{ $h->no_produksi }}</td>
                        <td class="text-left font-weight-black">> {{ $h->material_code }}</td>
                        <td class="bg-light font-weight-black">{{ number_format($h->qty_ambil_pcs) }}</td>
                        <td class="text-success font-weight-black">{{ number_format($batchOk) }}</td>
                        <td class="text-danger font-weight-black">{{ number_format($batchNg) }}</td>
                        <td><b style="color: {{ $color }}; font-family: 'JetBrains Mono';">{{ number_format($yield, 1) }}%</b></td>
                        <td class="text-left">
                            @foreach($rincian as $r)
                                <span class="ng-mini-pill">{{ strtoupper($r->ng_type) }}({{ $r->qty }})</span>
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🛡️ MODAL DEEP DIVE (DRAGON ENGINE UI) --}}
<div class="modal fade" id="deepDiveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-dragon animate__animated animate__zoomIn">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="modal-title heading-cyber text-white m-0">Audit_Deep_Dive</h5>
                    <p class="text-primary small font-weight-bold mb-0 uppercase tracking-widest">Dragon Engine Traceability Report</p>
                </div>
                <button type="button" class="btn btn-outline-light rounded-pill border-2" data-dismiss="modal" style="width: 45px; height: 45px;">&times;</button>
            </div>
            <div class="modal-body p-5">
                {{-- Donut & Info Grid --}}
                <div class="row align-items-center mb-5">
                    <div class="col-md-5">
                        <div class="yield-display text-center shadow-inner">
                            <div id="donutChart" style="min-height: 200px;"></div>
                            <h2 class="heading-cyber m-0 mt-3" id="det-yield-pct" style="font-size: 32px;">0%</h2>
                            <small class="stat-label">Production Yield</small>
                        </div>
                    </div>
                    <div class="col-md-7 pl-md-5 text-left">
                        <div class="mb-4">
                            <div class="stat-label mb-1">Batch Identifier</div>
                            <h4 class="font-weight-black text-dark" id="det-batch-no" style="font-family: 'JetBrains Mono'; letter-spacing: -1px;"></h4>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3"><div class="stat-label">Part No</div><div class="font-weight-bold h5 text-primary" id="det-part-no">-</div></div>
                            <div class="col-6 mb-3"><div class="stat-label">Shift</div><div class="font-weight-bold h5" id="det-shift">-</div></div>
                            <div class="col-6"><div class="stat-label">Line Area</div><div class="font-weight-bold text-dark" id="det-line">-</div></div>
                            <div class="col-6"><div class="stat-label">Timestamp</div><div class="small font-weight-bold text-muted" id="det-timestamp">-</div></div>
                        </div>
                    </div>
                </div>

                {{-- Status Bar --}}
                <div class="row mb-5">
                    <div class="col-3"><div class="stat-card bg-light p-3 text-center border"><div class="stat-label">Take</div><div class="h5 font-weight-black m-0" id="det-qty-take">0</div></div></div>
                    <div class="col-3"><div class="stat-card bg-light p-3 text-center border"><div class="stat-label text-success">Good</div><div class="h5 font-weight-black text-success m-0" id="det-qty-ok">0</div></div></div>
                    <div class="col-3"><div class="stat-card bg-light p-3 text-center border"><div class="stat-label text-danger">Reject</div><div class="h5 font-weight-black text-danger m-0" id="det-qty-ng">0</div></div></div>
                    <div class="col-3"><div class="stat-card bg-light p-3 text-center border"><div class="stat-label text-warning">Return</div><div class="h5 font-weight-black text-warning m-0" id="det-qty-ret">0</div></div></div>
                </div>

                {{-- NG Breakdown --}}
                <div class="mb-5">
                    <div class="stat-label mb-3 ml-1 text-danger font-weight-bold"><i class="fas fa-microscope mr-2"></i>Cumulative Defect Breakdown (6 Pcs Matrix)</div>
                    <div id="det-ng-breakdown" class="text-left">
                        <!-- Looping Javascript -->
                    </div>
                </div>

                {{-- Note Section --}}
                <div class="bg-light p-4 rounded-3xl border text-left shadow-inner">
                    <div class="stat-label mb-2"><i class="fas fa-sticky-note mr-2"></i>Audit Remarks</div>
                    <p class="font-weight-bold text-muted mb-0 small italic" id="det-remark">No remarks recorded rill.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 📊 TREND CHART
    const historyData = @json($history->take(15)->reverse()->values());
    new ApexCharts(document.querySelector("#trendChart"), {
        series: [
            { name: 'Yield %', type: 'line', data: historyData.map(h => {
                let total = (parseFloat(h.qty_hasil_ok)||0) + (parseFloat(h.qty_hasil_ng)||0);
                return total > 0 ? ((h.qty_hasil_ok/total)*100).toFixed(1) : 0;
            })},
            { name: 'Take Qty', type: 'area', data: historyData.map(h => h.qty_ambil_pcs) }
        ],
        chart: { height: 350, type: 'line', toolbar: { show: false } },
        stroke: { width: [5, 2], curve: 'smooth' },
        colors: ['#4361ee', '#e2e8f0'],
        fill: { type: 'gradient', gradient: { opacityFrom: [1, 0.4], opacityTo: [1, 0.1] } },
        xaxis: { categories: historyData.map(h => h.no_produksi.substr(-6)) }
    }).render();

    // 🛡️ DEEP DIVE LOGIC
    let donutChart = null;
    let chartDataTemp = { ok: 0, ng: 0 };

    function showDeepDive(h, rincian) {
        const ok = parseInt(h.qty_hasil_ok) || 0;
        const ng = parseInt(h.qty_hasil_ng) || 0;
        const total = ok + ng;
        const yieldVal = total > 0 ? Math.round((ok / total) * 100) : 0;

        chartDataTemp = { ok: ok, ng: ng };

        document.getElementById('det-batch-no').innerText = h.no_produksi;
        document.getElementById('det-part-no').innerText = h.material_code;
        document.getElementById('det-shift').innerText = h.shift;
        document.getElementById('det-line').innerText = h.line_names || 'LINE A';
        document.getElementById('det-timestamp').innerText = h.created_at;
        document.getElementById('det-qty-take').innerText = h.qty_ambil_pcs;
        document.getElementById('det-qty-ok').innerText = ok;
        document.getElementById('det-qty-ng').innerText = ng;
        document.getElementById('det-qty-ret').innerText = h.qty_return_warehouse;
        document.getElementById('det-yield-pct').innerText = yieldVal + "%";
        document.getElementById('det-yield-pct').style.color = yieldVal >= 85 ? '#10b981' : '#ef4444';
        document.getElementById('det-remark').innerText = h.keterangan || 'AUTOMATED_SYSTEM_REPORT_GEN';

        // Render NG Breakdown
        let html = '';
        if (rincian && rincian.length > 0) {
            rincian.forEach(item => {
                html += `
                <div class="ng-entry-pill animate__animated animate__fadeInUp">
                    <span class="ng-type-label">• ${item.ng_type}</span>
                    <span class="ng-qty-badge">${item.qty} PCS</span>
                </div>`;
            });
        } else {
            html = '<div class="text-center py-4 stat-label">ZERO_DEFECT_BATCH_CONFIRMED</div>';
        }
        document.getElementById('det-ng-breakdown').innerHTML = html;

        $('#deepDiveModal').modal('show');
    }

    // 🔥 FIX DONUT: Render after modal fully visible rill
    $('#deepDiveModal').on('shown.bs.modal', function () {
        if(donutChart) donutChart.destroy();
        
        donutChart = new ApexCharts(document.querySelector("#donutChart"), {
            series: [chartDataTemp.ok, chartDataTemp.ng],
            chart: { type: 'donut', height: 250, animations: { enabled: true, speed: 800 } },
            colors: ['#10b981', '#ef4444'],
            labels: ['Good', 'Reject'],
            legend: { show: false },
            plotOptions: { pie: { donut: { size: '75%', labels: { show: false } } } },
            stroke: { show: false }
        });
        
        donutChart.render();
    });
</script>
@endsection