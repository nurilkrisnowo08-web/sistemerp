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
        --dragon-red: #ef4444; --dragon-gold: #f59e0b; --dragon-sky: #3a86ff;
        --dragon-dark: #0f172a; --dragon-glass: rgba(255, 255, 255, 0.8);
    }
    
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
    .heading-cyber { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; }

    /* 📊 CYBER STAT CARDS */
    .stat-card { background: #fff; border-radius: 24px; padding: 25px; border: 1px solid rgba(67, 97, 238, 0.1); transition: 0.4s; position: relative; overflow: hidden; height: 100%; }
    .stat-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(67, 97, 238, 0.15); }
    .stat-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 2px; }
    .stat-value { font-family: 'Orbitron'; font-size: 30px; font-weight: 900; line-height: 1.2; }
    .card-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; }

    /* 🏛️ TERMINAL HUD TABLE */
    .terminal-card { background: #fff; border-radius: 32px; box-shadow: 0 20px 60px rgba(0,0,0,0.03); border: 1px solid #eef2f6; overflow: hidden; }
    .table-hud thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; padding: 22px; border: none; font-weight: 800; }
    .row-clickable:hover { background: #f8fafc !important; cursor: pointer; box-shadow: inset 8px 0 0 var(--dragon-blue); }

    /* 🛡️ DEEP DIVE MODAL ENHANCEMENT */
    .modal-dragon { border-radius: 40px !important; border: none !important; box-shadow: 0 25px 80px rgba(0,0,0,0.3) !important; }
    .kamar-ng { background: #f8fafc; border-radius: 25px; padding: 20px; border: 1px solid #e2e8f0; height: 100%; position: relative; }
    .hud-stat-box { background: white; border-radius: 18px; padding: 15px; border: 1px solid #f1f5f9; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }

    /* 🖨️ ADVANCED PRINT TEMPLATE */
    .print-template { display: none; color: #000 !important; }
    @media print {
        .screen-only, .no-print, .main-sidebar, .main-header, .btn, .filter-bar, .modal { display: none !important; }
        .print-template { display: block !important; width: 100% !important; padding: 20px; }
        .print-header { border-bottom: 4px solid #000; padding-bottom: 15px; margin-bottom: 30px; }
        .print-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .print-table th, .print-table td { border: 1px solid #000; padding: 10px; font-size: 12px; text-align: center; }
        @page { size: A4 landscape; margin: 15mm; }
    }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">

    {{-- 🏛️ PDF TEMPLATE --}}
    <div class="print-template">
        <div class="print-header">
            <table style="width: 100%;">
                <tr>
                    <td><h1 style="margin: 0; font-family: 'Orbitron'; font-weight: 900;">PT ASALTA MANDIRI AGUNG</h1><p>Dragon Audit System v9.5 Official Report</p></td>
                    <td style="text-align: right;"><b>DATE:</b> {{ date('d M Y') }}</td>
                </tr>
            </table>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
            <div style="width: 19%; border: 2px solid #000; padding: 15px; text-align: center;">TAKE: {{ number_format($totalTake) }}</div>
            <div style="width: 19%; border: 2px solid #000; padding: 15px; text-align: center;">OK: {{ number_format($totalOk) }}</div>
            <div style="width: 19%; border: 2px solid #000; padding: 15px; text-align: center;">NG: {{ number_format($totalNg) }}</div>
            <div style="width: 19%; border: 2px solid #000; padding: 15px; text-align: center;">RET: {{ number_format($totalRet) }}</div>
            <div style="width: 19%; border: 2px solid #000; padding: 15px; text-align: center;">PERF: {{ number_format($performance, 1) }}%</div>
        </div>
        <table class="print-table">
            <thead><tr><th>Timestamp</th><th>Batch No</th><th>Part ID</th><th>Take</th><th>OK</th><th>NG</th><th>RET</th></tr></thead>
            <tbody>
                @foreach($history as $h)
                <tr>
                    <td>{{ date('d/m/y H:i', strtotime($h->updated_at)) }}</td>
                    <td>{{ $h->no_produksi }}</td>
                    <td>{{ $h->material_code }}</td>
                    <td>{{ number_format($h->qty_ambil_pcs) }}</td>
                    <td>{{ number_format($h->qty_hasil_ok) }}</td>
                    <td>{{ number_format($h->qty_hasil_ng) }}</td>
                    <td>{{ number_format($h->qty_return_warehouse) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- 🛰️ SCREEN UI HEADER --}}
    <div class="screen-only">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
            <div>
                <h2 class="heading-cyber m-0">DRAGON_AUDIT <span class="text-primary">v9.5</span></h2>
                <p class="text-muted small font-weight-bold mb-0 uppercase tracking-widest"><i class="fas fa-dragon text-primary mr-2"></i> Performance Matrix // PT AMA</p>
            </div>
            <div class="d-flex">
                <form action="{{ route('produksi.history') }}" method="GET" class="filter-bar d-flex align-items-center shadow-sm p-3 bg-white rounded-2xl border mr-3">
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm border-0 mr-2 bg-light rounded-pill px-3 shadow-none">
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm border-0 mr-2 bg-light rounded-pill px-3 shadow-none">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold">SYNC</button>
                </form>
                <button onclick="window.print()" class="btn btn-dark rounded-2xl p-3 shadow-lg"><i class="fas fa-file-pdf"></i></button>
            </div>
        </div>

        {{-- 📊 MAIN STATS --}}
        <div class="row mb-5">
            <div class="col-md-3 col-6 mb-3"><div class="stat-card"><div class="card-strip bg-primary"></div><div class="stat-label">Material Take</div><div class="stat-value text-primary">{{ number_format($totalTake) }}</div></div></div>
            <div class="col-md-2 col-4 mb-3"><div class="stat-card"><div class="card-strip bg-success"></div><div class="stat-label">Verified OK</div><div class="stat-value text-success">{{ number_format($totalOk) }}</div></div></div>
            <div class="col-md-2 col-4 mb-3"><div class="stat-card"><div class="card-strip bg-danger"></div><div class="stat-label">Total Reject</div><div class="stat-value text-danger">{{ number_format($totalNg) }}</div></div></div>
            <div class="col-md-2 col-4 mb-3"><div class="stat-card"><div class="card-strip bg-info"></div><div class="stat-label">Return RM</div><div class="stat-value text-info">{{ number_format($totalRet) }}</div></div></div>
            <div class="col-md-3 col-12 mb-3"><div class="stat-card bg-dark text-white"><div class="stat-label text-white-50">Performance</div><div class="stat-value text-white">{{ number_format($performance, 1) }}%</div></div></div>
        </div>

        {{-- 📈 THE ULTIMATE 5-AXIS CHART --}}
        <div class="terminal-card p-4 mb-5 shadow-xl animate__animated animate__zoomIn">
            <h6 class="font-weight-black text-muted small uppercase mb-4 tracking-widest"><i class="fas fa-wave-square mr-2 text-primary"></i> 5-Axis Dragon Spline Matrix</h6>
            <div id="trendChart"></div>
        </div>

        {{-- 📋 TABLE LOG --}}
        <div class="terminal-card shadow-lg mb-5 animate__animated animate__fadeInUp">
            <div class="table-responsive">
                <table class="table table-hud mb-0 text-center">
                    <thead>
                        <tr>
                            <th class="text-left pl-5">Finish Time</th>
                            <th>Batch</th>
                            <th>Part Identification</th>
                            <th>Take</th>
                            <th class="text-success">OK</th>
                            <th class="text-danger">NG</th>
                            <th>Yield</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $h)
                        @php 
                            $allLogs = DB::table('production_ng_logs')->where('no_produksi', $h->no_produksi)->get();
                            $rProd = $allLogs->where('created_at', '<', $h->qc_at ?? now())->values();
                            $rQC = $allLogs->where('created_at', '>=', $h->qc_at ?? now())->values();
                            $batchOk = (float)$h->qty_hasil_ok;
                            $batchNg = (float)$h->qty_hasil_ng;
                            $yld = ($batchOk + $batchNg) > 0 ? ($batchOk / ($batchOk + $batchNg)) * 100 : 0;
                        @endphp
                        <tr class="row-clickable" onclick="showDeepDive({{ json_encode($h) }}, {{ json_encode($rProd) }}, {{ json_encode($rQC) }})">
                            <td class="text-left pl-5">
                                <div class="font-weight-black text-dark">{{ date('d/m/y', strtotime($h->updated_at)) }}</div>
                                <div class="small text-primary font-weight-bold">{{ date('H:i', strtotime($h->updated_at)) }}</div>
                            </td>
                            <td class="small font-weight-bold text-muted">{{ $h->no_produksi }}</td>
                            <td class="text-left font-weight-black">> {{ $h->material_code }}</td>
                            <td class="bg-light font-weight-black">{{ number_format($h->qty_ambil_pcs) }}</td>
                            <td class="text-success font-weight-black">{{ number_format($batchOk) }}</td>
                            <td class="text-danger font-weight-black">{{ number_format($batchNg) }}</td>
                            <td><b class="font-family-jetbrains">{{ number_format($yld, 1) }}%</b></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- 🛡️ ULTIMATE DEEP DIVE MODAL --}}
<div class="modal fade" id="deepDiveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-dragon">
            <div class="modal-header bg-dark text-white border-0 py-4 px-5" style="border-radius: 40px 40px 0 0;">
                <div>
                    <h5 class="modal-title heading-cyber m-0">Batch_Trace_Analysis</h5>
                    <small class="text-primary font-weight-bold uppercase tracking-widest">Dragon Engine Traceability Report</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-5 bg-white">
                {{-- Header Modal: Donut + Batch Info --}}
                <div class="row align-items-center mb-5">
                    <div class="col-md-5 text-center border-right">
                        <div id="modal-donut-yield" style="min-height: 220px;"></div>
                        <h2 class="heading-cyber m-0 mt-2" id="det-yield-val">0%</h2>
                        <small class="stat-label">Yield Accuracy</small>
                    </div>
                    <div class="col-md-7 pl-md-5 text-left">
                        <div class="stat-label mb-1">Batch Identifier</div>
                        <h4 class="font-weight-black text-primary mb-4" id="det-batch" style="font-family: 'JetBrains Mono';"></h4>
                        <div class="row">
                            <div class="col-6 mb-3"><small class="stat-label">Part Identification</small><div class="font-weight-bold h5 text-dark" id="det-part">-</div></div>
                            <div class="col-6 mb-3"><small class="stat-label">Process Start</small><div class="font-weight-bold h6 text-muted" id="det-time">-</div></div>
                        </div>
                    </div>
                </div>

                {{-- 📊 DATA KEMBALI DIMUNCULKAN DI SINI RILL --}}
                <div class="row mb-5 text-left">
                    <div class="col-3"><div class="hud-stat-box"><small class="stat-label">Take</small><div class="h5 font-weight-black m-0" id="det-take">0</div></div></div>
                    <div class="col-3"><div class="hud-stat-box border-success"><small class="stat-label text-success">OK</small><div class="h5 font-weight-black text-success m-0" id="det-ok">0</div></div></div>
                    <div class="col-3"><div class="hud-stat-box border-danger"><small class="stat-label text-danger">NG</small><div class="h5 font-weight-black text-danger m-0" id="det-ng">0</div></div></div>
                    <div class="col-3"><div class="hud-stat-box border-info"><small class="stat-label text-info">RET</small><div class="h5 font-weight-black text-info m-0" id="det-ret">0</div></div></div>
                </div>

                {{-- Kamar Rincian NG --}}
                <div class="row mb-4 text-left">
                    <div class="col-md-6 mb-3">
                        <div class="kamar-ng border-primary">
                            <span class="badge badge-primary px-3 py-2 rounded-pill mb-3 uppercase font-weight-black"><i class="fas fa-industry mr-2"></i> Production Logs</span>
                            <div id="det-ng-prod-list"></div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="kamar-ng border-warning">
                            <span class="badge badge-warning px-3 py-2 rounded-pill mb-3 uppercase font-weight-black"><i class="fas fa-search mr-2"></i> QC Escape</span>
                            <div id="det-ng-qc-list"></div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-dark text-white rounded-3xl text-left shadow-lg">
                    <small class="stat-label text-white-50 d-block mb-1">Audit Traceability Note</small>
                    <p class="mb-0 font-weight-bold" id="det-remark">-</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 📊 DRAGON SPLINE 5-AXIS CHART WITH AUTO-WAVE
    const historyData = @json($history->take(15)->reverse()->values());
    new ApexCharts(document.querySelector("#trendChart"), {
        series: [
            { name: 'Perf %', type: 'line', data: historyData.map(h => {
                let t = (parseFloat(h.qty_hasil_ok)||0) + (parseFloat(h.qty_hasil_ng)||0);
                return t > 0 ? ((h.qty_hasil_ok/t)*100).toFixed(1) : 0;
            })},
            { name: 'Take', type: 'area', data: historyData.map(h => h.qty_ambil_pcs) },
            { name: 'OK', type: 'area', data: historyData.map(h => h.qty_hasil_ok) },
            { name: 'NG', type: 'area', data: historyData.map(h => h.qty_hasil_ng) },
            { name: 'RET', type: 'line', data: historyData.map(h => h.qty_return_warehouse) }
        ],
        chart: { height: 380, type: 'line', toolbar: { show: false }, animations: { enabled: true, easing: 'easeinout', speed: 1200 } },
        stroke: { width: [5, 2, 2, 2, 3], curve: 'smooth' },
        colors: ['#4361ee', '#cbd5e1', '#10b981', '#ef4444', '#f59e0b'],
        xaxis: { categories: historyData.map(h => h.no_produksi.substr(-6)), labels: { style: { fontWeight: 800 } } },
        yaxis: [{ title: { text: "Performance %" }, min: 0, max: 100 }, { opposite: true, title: { text: "Units (PCS)" } }],
        legend: { position: 'top', horizontalAlign: 'right', fontWeight: 800 }
    }).render();

    // 🛡️ MODAL DETAIL LOGIC
    let donut = null;
    let tempDonutData = { ok: 0, ng: 0 };

    function showDeepDive(h, rProd, rQC) {
        const ok = parseInt(h.qty_hasil_ok) || 0;
        const ng = parseInt(h.qty_hasil_ng) || 0;
        const total = ok + ng;
        const yld = total > 0 ? Math.round((ok/total)*100) : 0;
        
        tempDonutData = { ok: ok, ng: ng };

        // Population data ke elemen
        document.getElementById('det-batch').innerText = h.no_produksi;
        document.getElementById('det-part').innerText = h.material_code;
        document.getElementById('det-time').innerText = h.created_at;
        document.getElementById('det-take').innerText = h.qty_ambil_pcs;
        document.getElementById('det-ok').innerText = ok;
        document.getElementById('det-ng').innerText = ng;
        document.getElementById('det-ret').innerText = h.qty_return_warehouse;
        document.getElementById('det-yield-val').innerText = yld + "%";
        document.getElementById('det-yield-val').style.color = yld >= 85 ? '#10b981' : '#ef4444';
        document.getElementById('det-remark').innerText = h.keterangan || 'SYSTEM_GEN_LOG';

        renderList('det-ng-prod-list', rProd, 'text-primary');
        renderList('det-ng-qc-list', rQC, 'text-warning');

        $('#deepDiveModal').modal('show');
    }

    function renderList(id, data, cls) {
        const d = document.getElementById(id);
        d.innerHTML = '';
        if (data.length > 0) {
            data.forEach(i => {
                d.innerHTML += `<div class="d-flex justify-content-between mb-2 bg-white p-3 rounded-2xl border shadow-sm"><span class="small font-weight-black ${cls}">${i.ng_type}</span><span class="badge badge-dark rounded-pill px-3">${i.qty}</span></div>`;
            });
        } else { d.innerHTML = '<div class="text-center text-muted small py-4">No Defects Found</div>'; }
    }

    $('#deepDiveModal').on('shown.bs.modal', function () {
        if(donut) donut.destroy();
        donut = new ApexCharts(document.querySelector("#modal-donut-yield"), {
            series: [tempDonutData.ok, tempDonutData.ng],
            chart: { type: 'donut', width: 220, animations: { enabled: true, speed: 1000 } },
            colors: ['#10b981', '#ef4444'],
            labels: ['Good', 'NG'],
            legend: { show: false },
            plotOptions: { pie: { donut: { size: '75%' } } }
        });
        donut.render();
    });
</script>
@endsection