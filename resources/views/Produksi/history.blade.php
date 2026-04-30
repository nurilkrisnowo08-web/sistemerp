@extends('layout.admin')

@section('content')
{{-- ✨ HITUNGAN GLOBAL (WAJIB DI ATAS) ✨ --}}
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
        --dragon-dark: #0f172a; --dragon-border: rgba(67, 97, 238, 0.1);
    }
    
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
    .heading-cyber { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; }

    /* 📊 STAT CARDS UI v8.0 */
    .stat-card { background: #fff; border-radius: 24px; padding: 25px; border: 1px solid var(--dragon-border); transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; overflow: hidden; height: 100%; }
    .stat-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(67, 97, 238, 0.15); }
    .stat-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 2px; }
    .stat-value { font-family: 'Orbitron'; font-size: 28px; font-weight: 900; line-height: 1.2; }
    .card-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; }

    /* 🏛️ TERMINAL TABLE STYLE */
    .terminal-card { background: #fff; border-radius: 30px; box-shadow: 0 15px 50px rgba(0,0,0,0.04); border: 1px solid #eef2f6; overflow: hidden; }
    .table-hud thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; padding: 22px; border: none; font-weight: 800; }
    .row-clickable { transition: 0.3s; }
    .row-clickable:hover { background: #f8fafc !important; cursor: pointer; box-shadow: inset 6px 0 0 var(--dragon-blue); }

    /* 🔴 NG BREAKDOWN BADGES */
    .ng-mini-pill { background: #fee2e2; color: var(--dragon-red); font-size: 9px; padding: 4px 10px; border-radius: 8px; border: 1px solid #fecdd3; font-family: 'JetBrains Mono'; font-weight: 800; display: inline-block; margin: 2px; }

    /* 🛡️ MODAL DEEP DIVE v8.0 */
    .modal-content { border-radius: 40px !important; border: none !important; box-shadow: 0 25px 80px rgba(0,0,0,0.2) !important; overflow: hidden; }
    .modal-header-cyber { background: var(--dragon-dark); color: white; padding: 30px 45px; border: none; }
    .deep-dive-box { background: #f8fafc; border-radius: 25px; padding: 20px; border: 1px solid #e2e8f0; }
    
    /* NG Compartment (The Kamar) */
    .ng-kamar { background: white; border-radius: 20px; padding: 15px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px rgba(0,0,0,0.02); height: 100%; }
    .kamar-label { font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 1.5px; padding: 5px 10px; border-radius: 6px; display: inline-block; margin-bottom: 10px; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">

    {{-- 🛰️ SCREEN UI HEADER --}}
    <div class="screen-only">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 no-print">
            <div>
                <h2 class="heading-cyber m-0">DRAGON_AUDIT <span class="text-primary">v8.0</span></h2>
                <p class="text-muted small font-weight-bold mb-0 uppercase tracking-widest"><i class="fas fa-dragon text-primary mr-2"></i> Performance Matrix // PT AMA</p>
            </div>
            <form action="{{ route('produksi.history') }}" method="GET" class="filter-bar d-flex align-items-center shadow-sm p-3 bg-white rounded-2xl border">
                <i class="fas fa-calendar-alt text-primary mr-3 ml-2"></i>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm border-0 mr-3 shadow-none bg-light rounded-pill px-3">
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm border-0 shadow-none bg-light rounded-pill px-3">
                <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold ml-4">SYNC_CORE</button>
            </form>
        </div>

        {{-- 📊 MAIN STATS --}}
        <div class="row mb-5">
            <div class="col-md-3 col-6 mb-3"><div class="stat-card"><div class="card-strip bg-primary"></div><div class="stat-label">Material Take</div><div class="stat-value text-primary">{{ number_format($totalTake) }}</div></div></div>
            <div class="col-md-2 col-4 mb-3"><div class="stat-card"><div class="card-strip bg-success"></div><div class="stat-label">Verified OK</div><div class="stat-value text-success">{{ number_format($totalOk) }}</div></div></div>
            <div class="col-md-2 col-4 mb-3"><div class="stat-card"><div class="card-strip bg-danger"></div><div class="stat-label">Total Reject</div><div class="stat-value text-danger">{{ number_format($totalNg) }}</div></div></div>
            <div class="col-md-2 col-4 mb-3"><div class="stat-card"><div class="card-strip bg-info"></div><div class="stat-label">Returned RM</div><div class="stat-value text-info">{{ number_format($totalRet) }}</div></div></div>
            <div class="col-md-3 col-12 mb-3"><div class="stat-card bg-dark text-white"><div class="stat-label text-white-50">Performance Accuracy</div><div class="stat-value text-white">{{ number_format($performance, 1) }}%</div></div></div>
        </div>
    </div>

    {{-- 📈 THE ULTIMATE 5-AXIS DRAGON CHART --}}
    <div class="terminal-card p-4 mb-5 shadow-xl no-print animate__animated animate__zoomIn">
        <h6 class="font-weight-black text-muted small uppercase mb-4 tracking-widest ml-2"><i class="fas fa-wave-square mr-2 text-primary"></i> Dragon Spline Flow (Take, OK, NG, RET, PERF)</h6>
        <div id="trendChart"></div>
    </div>

    {{-- 📋 TABLE HUD --}}
    <div class="terminal-card shadow-lg mb-5 animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-hud mb-0 text-center print-table-final">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Finish Date (QC)</th>
                        <th>Batch_No</th>
                        <th class="text-left">Part Identification</th>
                        <th>Take</th>
                        <th class="text-success">OK</th>
                        <th class="text-danger">NG</th>
                        <th class="text-info">RET</th>
                        <th>Yield</th>
                        <th class="text-left">NG Breakdown (Kumulatif)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $h)
                    @php 
                        // 🔥 Ambil Semua Log NG (Prod + QC) rill
                        $allLogs = DB::table('production_ng_logs')->where('no_produksi', $h->no_produksi)->get();
                        
                        // Kelompokkan buat rincian kamar di modal
                        $rincianProd = $allLogs->where('created_at', '<', $h->qc_at ?? now())->values();
                        $rincianQC   = $allLogs->where('created_at', '>=', $h->qc_at ?? now())->values();

                        // Ringkasan kumulatif buat tabel
                        $summary = $allLogs->groupBy('ng_type')->map(fn($group) => $group->sum('qty'));

                        $batchOk = (float)$h->qty_hasil_ok;
                        $batchNg = (float)$h->qty_hasil_ng;
                        $yield = ($batchOk + $batchNg) > 0 ? ($batchOk / ($batchOk + $batchNg)) * 100 : 0;
                        $color = ($yield >= 95) ? '#10b981' : (($yield >= 85) ? '#f59e0b' : '#ef4444');
                    @endphp
                    <tr class="row-clickable" onclick="showDeepDive({{ json_encode($h) }}, {{ json_encode($rincianProd) }}, {{ json_encode($rincianQC) }})">
                        <td class="text-left pl-5">
                            <div class="font-weight-black text-dark" style="font-size: 12px;">{{ date('d/m/y', strtotime($h->updated_at)) }}</div>
                            <div class="small text-primary font-weight-bold" style="font-family: 'JetBrains Mono';">{{ date('H:i', strtotime($h->updated_at)) }}</div>
                        </td>
                        <td class="small font-weight-bold text-muted">{{ $h->no_produksi }}</td>
                        <td class="text-left font-weight-black text-dark pl-4">> {{ $h->material_code }}</td>
                        <td class="bg-light font-weight-black">{{ number_format($h->qty_ambil_pcs) }}</td>
                        <td class="text-success font-weight-black">{{ number_format($batchOk) }}</td>
                        <td class="text-danger font-weight-black">{{ number_format($batchNg) }}</td>
                        <td class="text-info font-weight-black">{{ number_format($h->qty_return_warehouse) }}</td>
                        <td><b style="color: {{ $color }}; font-family: 'JetBrains Mono'; font-size: 13px;">{{ number_format($yield, 1) }}%</b></td>
                        <td class="text-left">
                            @foreach($summary as $type => $qty)
                                <span class="ng-mini-pill">{{ strtoupper($type) }}({{ $qty }})</span>
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🛡️ MODAL DEEP DIVE v8.0 --}}
<div class="modal fade" id="deepDiveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content animate__animated animate__zoomIn">
            <div class="modal-header-cyber d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="heading-cyber m-0">Audit_Batch_Deep_Dive</h5>
                    <p class="text-primary small font-weight-bold mb-0 uppercase tracking-widest">Cumulative Data Integrity Report</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-5 bg-white">
                {{-- Header Section: Donut + Info --}}
                <div class="row align-items-center mb-5">
                    <div class="col-md-4 text-center border-right">
                        <div id="modal-donut-yield" style="min-height: 180px;"></div>
                        <h4 class="font-weight-black mt-2 mb-0" id="det-yield-val" style="font-family: 'Orbitron';">0%</h4>
                        <small class="stat-label">Yield Accuracy</small>
                    </div>
                    <div class="col-md-8 pl-md-5 text-left">
                        <h4 class="font-weight-black text-primary mb-3" id="det-batch" style="font-family: 'JetBrains Mono'; letter-spacing: -1px;"></h4>
                        <div class="row">
                            <div class="col-6"><small class="stat-label">Part Identification</small><div class="font-weight-bold h5" id="det-part">-</div></div>
                            <div class="col-6"><small class="stat-label">Production Start</small><div class="font-weight-bold small text-muted" id="det-time">-</div></div>
                        </div>
                    </div>
                </div>

                {{-- Statistics Grid --}}
                <div class="row mb-5 text-left">
                    <div class="col-3"><div class="deep-dive-box text-center"><small class="stat-label">Take</small><div class="h5 font-weight-black m-0" id="det-take">0</div></div></div>
                    <div class="col-3"><div class="deep-dive-box text-center border-success"><small class="stat-label text-success">Passed OK</small><div class="h5 font-weight-black text-success m-0" id="det-ok">0</div></div></div>
                    <div class="col-3"><div class="deep-dive-box text-center border-danger"><small class="stat-label text-danger">Total Reject</small><div class="h5 font-weight-black text-danger m-0" id="det-ng">0</div></div></div>
                    <div class="col-3"><div class="deep-dive-box text-center border-info"><small class="stat-label text-info">Return RM</small><div class="h5 font-weight-black text-info m-0" id="det-ret">0</div></div></div>
                </div>

                {{-- ✨ NG COMPARISON (KAMAR PRODUKSI vs QC) ✨ --}}
                <div class="row mb-4 text-left">
                    <div class="col-md-6 mb-3">
                        <div class="ng-kamar border-primary">
                            <span class="kamar-label bg-primary text-white"><i class="fas fa-industry mr-2"></i>Found by Production</span>
                            <div id="det-ng-prod-list"></div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="ng-kamar border-warning">
                            <span class="kamar-label bg-warning text-dark"><i class="fas fa-search mr-2"></i>Found by Quality (Escape)</span>
                            <div id="det-ng-qc-list"></div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-light rounded-3xl border text-left">
                    <small class="stat-label d-block mb-1"><i class="fas fa-info-circle mr-2"></i>Traceability Note</small>
                    <p class="mb-0 font-weight-bold text-dark small" id="det-remark">-</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 📊 DRAGON SPLINE 5-AXIS CHART
    const chartData = @json($history->take(15)->reverse()->values());
    new ApexCharts(document.querySelector("#trendChart"), {
        series: [
            { name: 'Perf %', type: 'line', data: chartData.map(h => {
                let total = (parseFloat(h.qty_hasil_ok)||0) + (parseFloat(h.qty_hasil_ng)||0);
                return total > 0 ? ((h.qty_hasil_ok/total)*100).toFixed(1) : 0;
            })},
            { name: 'Material Take', type: 'area', data: chartData.map(h => h.qty_ambil_pcs) },
            { name: 'Passed OK', type: 'area', data: chartData.map(h => h.qty_hasil_ok) },
            { name: 'Reject NG', type: 'area', data: chartData.map(h => h.qty_hasil_ng) },
            { name: 'Return RM', type: 'line', data: chartData.map(h => h.qty_return_warehouse) }
        ],
        chart: { height: 380, type: 'line', toolbar: { show: false }, animations: { speed: 1000 } },
        stroke: { width: [5, 2, 2, 2, 3], curve: 'smooth', dashArray: [0, 0, 0, 0, 5] },
        colors: ['#4361ee', '#cbd5e1', '#10b981', '#ef4444', '#f59e0b'],
        fill: { type: 'gradient', gradient: { opacityFrom: [1, 0.4, 0.4, 0.4, 0], opacityTo: [1, 0.1, 0.1, 0.1, 0] } },
        xaxis: { categories: chartData.map(h => h.no_produksi.substr(-6)), labels: { style: { fontWeight: 800 } } },
        yaxis: [
            { title: { text: "Performance %" }, min: 0, max: 100 },
            { opposite: true, title: { text: "Units (PCS)" } }
        ],
        legend: { position: 'top', horizontalAlign: 'right', fontWeight: 800 }
    }).render();

    // 🛡️ MODAL DETAIL LOGIC v8.0
    let donut = null;
    let currentData = { ok: 0, ng: 0 };

    function showDeepDive(h, rProd, rQC) {
        const ok = parseInt(h.qty_hasil_ok) || 0;
        const ng = parseInt(h.qty_hasil_ng) || 0;
        const total = ok + ng;
        const yieldVal = total > 0 ? Math.round((ok/total)*100) : 0;
        currentData = { ok: ok, ng: ng };

        document.getElementById('det-batch').innerText = h.no_produksi;
        document.getElementById('det-part').innerText = h.material_code;
        document.getElementById('det-time').innerText = h.created_at;
        document.getElementById('det-take').innerText = h.qty_ambil_pcs;
        document.getElementById('det-ok').innerText = ok;
        document.getElementById('det-ng').innerText = ng;
        document.getElementById('det-ret').innerText = h.qty_return_warehouse;
        document.getElementById('det-yield-val').innerText = yieldVal + "%";
        document.getElementById('det-yield-val').style.color = yieldVal >= 85 ? '#10b981' : '#ef4444';
        document.getElementById('det-remark').innerText = h.keterangan || 'AUTOMATED_SYSTEM_TRACE_LOG';

        // Render Kamar Produksi
        renderNGList('det-ng-prod-list', rProd, 'text-primary');
        // Render Kamar QC
        renderNGList('det-ng-qc-list', rQC, 'text-warning');

        $('#deepDiveModal').modal('show');
    }

    function renderNGList(id, data, colorClass) {
        const div = document.getElementById(id);
        div.innerHTML = '';
        if (data.length > 0) {
            data.forEach(item => {
                div.innerHTML += `
                    <div class="d-flex justify-content-between align-items-center mb-2 bg-light p-2 rounded-lg border-0">
                        <span class="small font-weight-bold uppercase ${colorClass}">• ${item.ng_type}</span>
                        <span class="badge badge-dark rounded-pill px-3 font-weight-bold">${item.qty}</span>
                    </div>`;
            });
        } else {
            div.innerHTML = '<div class="text-center text-muted small py-3 italic">No NG Identified</div>';
        }
    }

    // ✨ FIX DONUT: Render after modal animations rill
    $('#deepDiveModal').on('shown.bs.modal', function () {
        if(donut) donut.destroy();
        donut = new ApexCharts(document.querySelector("#modal-donut-yield"), {
            series: [currentData.ok, currentData.ng],
            chart: { type: 'donut', width: 180, animations: { enabled: true } },
            colors: ['#10b981', '#ef4444'],
            labels: ['Good', 'Reject'],
            legend: { show: false },
            plotOptions: { pie: { donut: { size: '75%' } } }
        });
        donut.render();
    });
</script>
@endsection