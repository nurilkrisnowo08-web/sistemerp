@extends('layout.admin')

@section('content')
{{-- ✨ PERHITUNGAN GLOBAL --}}
@php
    $totalTake = $history->sum('qty_ambil_pcs');
    $totalOk = $history->sum('qty_hasil_ok');
    $totalNg = $history->sum('qty_hasil_ng');
    $totalRet = $history->sum('qty_return_warehouse');
    $performance = ($totalTake - $totalRet) > 0 ? ($totalOk / ($totalTake - $totalRet)) * 100 : 0;
    
    // Format tanggal untuk laporan
    $periodString = ($startDate == $endDate) 
        ? date('d F Y', strtotime($startDate)) 
        : date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate));
@endphp

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;800&family=Orbitron:wght@700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root { 
        --audit-blue: #2563eb; --audit-green: #059669; 
        --audit-red: #dc2626; --audit-gold: #d97706; --audit-slate: #475569;
        --audit-dark: #0f172a; --audit-bg: #f8fafc;
    }
    
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: #1e293b; }
    .heading-audit { font-family: 'Plus Jakarta Sans'; font-weight: 800; letter-spacing: -0.5px; text-transform: uppercase; }

    /* 📊 STAT CARDS */
    .stat-card { background: #fff; border-radius: 20px; padding: 22px; border: 1px solid #e2e8f0; transition: 0.3s; position: relative; overflow: hidden; height: 100%; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    .stat-label { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
    .stat-value { font-family: 'Plus Jakarta Sans'; font-size: 28px; font-weight: 800; line-height: 1.2; }
    .card-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 4px; }

    /* 🏛️ HUD TABLE */
    .terminal-card { background: #fff; border-radius: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; overflow: hidden; }
    .table-hud thead th { background: #f8fafc; color: #475569; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px; border: none; font-weight: 800; }
    .row-clickable:hover { background: #f1f5f9 !important; cursor: pointer; }

    /* 🛡️ MODAL */
    .modal-audit { border-radius: 28px !important; border: none !important; }
    .kamar-ng { background: #f8fafc; border-radius: 20px; padding: 18px; border: 1px solid #e2e8f0; }

    /* 🖨️ PROFESSIONAL PRINT TEMPLATE */
    .print-template { display: none; }
    @media print {
        @page { size: A4 landscape; margin: 10mm; }
        .screen-only, .no-print, .main-sidebar, .main-header, .btn, .filter-bar, .modal, #sidebarToggle, .navbar { display: none !important; }
        body { background: white !important; margin: 0; padding: 0; }
        .print-template { display: block !important; width: 100% !important; color: black !important; }
        .print-header { border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        .print-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .print-table th { background: #f0f0f0 !important; border: 1px solid #000; padding: 8px; font-size: 11px; text-transform: uppercase; }
        .print-table td { border: 1px solid #000; padding: 6px; font-size: 11px; text-align: center; }
        .summary-box { border: 1px solid #000; padding: 10px; text-align: center; width: 18%; font-weight: bold; font-size: 13px; }
    }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">

    {{-- 🏛️ CLEAN PRINT TEMPLATE --}}
    <div class="print-template">
        <div class="print-header">
            <table style="width: 100%;">
                <tr>
                    <td>
                        <h2 style="margin: 0; font-weight: 800; font-size: 24px;">PT ASALTA MANDIRI AGUNG</h2>
                        <p style="margin: 0; font-size: 14px;">Production Quality Audit Report</p>
                    </td>
                    <td style="text-align: right; vertical-align: bottom;">
                        <p style="margin: 0;"><b>PERIOD:</b> {{ $periodString }}</p>
                        <p style="margin: 0; font-size: 10px;">Printed on: {{ date('d/m/Y H:i') }}</p>
                    </td>
                </tr>
            </table>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <div class="summary-box">TAKE: {{ number_format($totalTake) }}</div>
            <div class="summary-box">OK: {{ number_format($totalOk) }}</div>
            <div class="summary-box">NG: {{ number_format($totalNg) }}</div>
            <div class="summary-box">RET: {{ number_format($totalRet) }}</div>
            <div class="summary-box">PERF: {{ number_format($performance, 1) }}%</div>
        </div>

        <table class="print-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Batch Number</th>
                    <th>Part Identification</th>
                    <th>Qty Take</th>
                    <th>Qty OK</th>
                    <th>Qty NG</th>
                    <th>Return RM</th>
                </tr>
            </thead>
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
        
        <div style="margin-top: 40px; display: flex; justify-content: space-around; text-align: center;">
            <div style="width: 200px; border-top: 1px solid #000; padding-top: 5px;">Production Supervisor</div>
            <div style="width: 200px; border-top: 1px solid #000; padding-top: 5px;">Quality Control</div>
            <div style="width: 200px; border-top: 1px solid #000; padding-top: 5px;">PPIC Department</div>
        </div>
    </div>

    {{-- 🛰️ SCREEN UI HEADER --}}
    <div class="screen-only">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
            <div>
                <h2 class="heading-audit m-0">QUALITY_AUDIT <span class="text-primary">SYSTEM</span></h2>
                <p class="text-muted small font-weight-bold mb-0 uppercase tracking-widest">Production Traceability Matrix // Period: {{ $periodString }}</p>
            </div>
            <div class="d-flex mt-3 mt-md-0">
                <form action="{{ route('produksi.history') }}" method="GET" class="d-flex align-items-center shadow-sm p-2 bg-white rounded-pill border mr-2">
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm border-0 bg-transparent shadow-none font-weight-bold" style="width: 140px;">
                    <span class="mx-1 text-muted">to</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm border-0 bg-transparent shadow-none font-weight-bold" style="width: 140px;">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 ml-2"><i class="fas fa-sync-alt"></i></button>
                </form>
                <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 shadow-sm"><i class="fas fa-print mr-2"></i> PRINT REPORT</button>
            </div>
        </div>

        {{-- 📊 MAIN STATS --}}
        <div class="row mb-5">
            <div class="col-md-3 mb-3"><div class="stat-card"><div class="card-strip bg-primary"></div><div class="stat-label">Total Material Take</div><div class="stat-value text-primary">{{ number_format($totalTake) }}</div></div></div>
            <div class="col-md-2 mb-3"><div class="stat-card"><div class="card-strip bg-success"></div><div class="stat-label">Verified OK</div><div class="stat-value text-success">{{ number_format($totalOk) }}</div></div></div>
            <div class="col-md-2 mb-3"><div class="stat-card"><div class="card-strip bg-danger"></div><div class="stat-label">Total Reject</div><div class="stat-value text-danger">{{ number_format($totalNg) }}</div></div></div>
            <div class="col-md-2 mb-3"><div class="stat-card"><div class="card-strip bg-warning"></div><div class="stat-label">Returned RM</div><div class="stat-value text-warning">{{ number_format($totalRet) }}</div></div></div>
            <div class="col-md-3 mb-3"><div class="stat-card bg-dark text-white"><div class="stat-label text-white-50">Global Performance</div><div class="stat-value text-white">{{ number_format($performance, 1) }}%</div></div></div>
        </div>

        {{-- 📈 TREND CHART --}}
        <div class="terminal-card p-4 mb-5 shadow-sm">
            <h6 class="font-weight-bold text-muted small uppercase mb-4 tracking-widest"><i class="fas fa-chart-line mr-2 text-primary"></i> Production Trend Analysis</h6>
            <div id="trendChart"></div>
        </div>

        {{-- 📋 TABLE LOG --}}
        <div class="terminal-card shadow-sm mb-5">
            <div class="table-responsive">
                <table class="table table-hud mb-0 text-center">
                    <thead>
                        <tr>
                            <th class="text-left pl-4">Finish Time</th>
                            <th>Batch ID</th>
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
                            $batchOk = (float)$h->qty_hasil_ok;
                            $batchNg = (float)$h->qty_hasil_ng;
                            $yld = ($batchOk + $batchNg) > 0 ? ($batchOk / ($batchOk + $batchNg)) * 100 : 0;
                        @endphp
                        <tr class="row-clickable" onclick="showDeepDive({{ json_encode($h) }})">
                            <td class="text-left pl-4">
                                <div class="font-weight-bold text-dark">{{ date('d/m/y', strtotime($h->updated_at)) }}</div>
                                <div class="small text-muted">{{ date('H:i', strtotime($h->updated_at)) }}</div>
                            </td>
                            <td class="small font-weight-bold text-muted">{{ $h->no_produksi }}</td>
                            <td class="text-left font-weight-bold">{{ $h->material_code }}</td>
                            <td class="font-weight-bold">{{ number_format($h->qty_ambil_pcs) }}</td>
                            <td class="text-success font-weight-bold">{{ number_format($batchOk) }}</td>
                            <td class="text-danger font-weight-bold">{{ number_format($batchNg) }}</td>
                            <td><span class="badge badge-light border px-2 py-1 font-family-jetbrains">{{ number_format($yld, 1) }}%</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- 🛡️ DETAIL MODAL --}}
<div class="modal fade" id="deepDiveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-audit">
            <div class="modal-header bg-dark text-white border-0 py-3 px-4">
                <h5 class="modal-title font-weight-bold">Batch_Trace_Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="row align-items-center mb-4">
                    <div class="col-md-5 text-center border-right">
                        <div id="modal-donut-yield" style="min-height: 200px;"></div>
                        <h2 class="font-weight-bold m-0" id="det-yield-val">0%</h2>
                        <small class="stat-label">Batch Accuracy</small>
                    </div>
                    <div class="col-md-7 pl-md-4">
                        <small class="stat-label">Batch Identifier</small>
                        <h5 class="font-weight-bold text-primary mb-3" id="det-batch"></h5>
                        <div class="row">
                            <div class="col-6 mb-2"><small class="stat-label">Part No</small><div class="font-weight-bold" id="det-part">-</div></div>
                            <div class="col-6 mb-2"><small class="stat-label">Processed</small><div class="font-weight-bold small" id="det-time">-</div></div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-3"><div class="kamar-ng text-center p-2"><small class="stat-label">Take</small><div class="h5 font-weight-bold m-0" id="det-take">0</div></div></div>
                    <div class="col-3"><div class="kamar-ng text-center p-2"><small class="stat-label text-success">OK</small><div class="h5 font-weight-bold text-success m-0" id="det-ok">0</div></div></div>
                    <div class="col-3"><div class="kamar-ng text-center p-2"><small class="stat-label text-danger">NG</small><div class="h5 font-weight-bold text-danger m-0" id="det-ng">0</div></div></div>
                    <div class="col-3"><div class="kamar-ng text-center p-2"><small class="stat-label text-info">RET</small><div class="h5 font-weight-bold text-info m-0" id="det-ret">0</div></div></div>
                </div>

                <div class="p-3 bg-light rounded-lg border">
                    <small class="stat-label d-block mb-1">Traceability Note</small>
                    <p class="mb-0 small font-weight-bold" id="det-remark">-</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 📊 TREND CHART
    const historyData = @json($history->take(20)->reverse()->values());
    new ApexCharts(document.querySelector("#trendChart"), {
        series: [
            { name: 'Perf %', type: 'line', data: historyData.map(h => {
                let t = (parseFloat(h.qty_hasil_ok)||0) + (parseFloat(h.qty_hasil_ng)||0);
                return t > 0 ? ((h.qty_hasil_ok/t)*100).toFixed(1) : 0;
            })},
            { name: 'OK Units', type: 'area', data: historyData.map(h => h.qty_hasil_ok) },
            { name: 'NG Units', type: 'area', data: historyData.map(h => h.qty_hasil_ng) }
        ],
        chart: { height: 350, type: 'line', toolbar: { show: false } },
        stroke: { width: [4, 2, 2], curve: 'smooth' },
        colors: ['#2563eb', '#10b981', '#ef4444'],
        xaxis: { categories: historyData.map(h => h.no_produksi.substr(-6)) },
        yaxis: [{ title: { text: "Performance %" }, min: 0, max: 100 }, { opposite: true, title: { text: "Units" } }],
        legend: { position: 'top', fontWeight: 700 }
    }).render();

    // 🛡️ MODAL LOGIC
    let donut = null;
    let tempDonutData = { ok: 0, ng: 0 };

    function showDeepDive(h) {
        const ok = parseInt(h.qty_hasil_ok) || 0;
        const ng = parseInt(h.qty_hasil_ng) || 0;
        const total = ok + ng;
        const yld = total > 0 ? Math.round((ok/total)*100) : 0;
        
        tempDonutData = { ok: ok, ng: ng };

        document.getElementById('det-batch').innerText = h.no_produksi;
        document.getElementById('det-part').innerText = h.material_code;
        document.getElementById('det-time').innerText = h.updated_at;
        document.getElementById('det-take').innerText = h.qty_ambil_pcs;
        document.getElementById('det-ok').innerText = ok;
        document.getElementById('det-ng').innerText = ng;
        document.getElementById('det-ret').innerText = h.qty_return_warehouse;
        document.getElementById('det-yield-val').innerText = yld + "%";
        document.getElementById('det-yield-val').style.color = yld >= 90 ? '#10b981' : '#ef4444';
        document.getElementById('det-remark').innerText = h.keterangan || 'Audit record validated.';

        $('#deepDiveModal').modal('show');
    }

    $('#deepDiveModal').on('shown.bs.modal', function () {
        if(donut) donut.destroy();
        donut = new ApexCharts(document.querySelector("#modal-donut-yield"), {
            series: [tempDonutData.ok, tempDonutData.ng],
            chart: { type: 'donut', width: 200 },
            colors: ['#10b981', '#ef4444'],
            labels: ['Good', 'NG'],
            legend: { show: false },
            plotOptions: { pie: { donut: { size: '70%' } } }
        });
        donut.render();
    });
</script>
@endsection