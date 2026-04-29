@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root { 
        --ind-steel: #4361ee; --ind-success: #10b981; 
        --ind-danger: #ef4444; --ind-warning: #f59e0b; --ind-info: #3a86ff;
    }
    
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: #0f172a; }
    .heading-cyber { font-family: 'Orbitron'; font-weight: 800; letter-spacing: -1px; text-transform: uppercase; }

    /* 📊 STATS CARDS */
    .stat-card { background: #fff; border-radius: 20px; padding: 22px; border: 1px solid rgba(0,0,0,0.05); transition: 0.3s; box-shadow: 0 10px 30px rgba(0,0,0,0.02); height: 100%; position: relative; overflow: hidden; }
    .stat-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }
    .stat-value { font-family: 'Orbitron'; font-size: 24px; font-weight: 900; }
    .card-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 5px; }

    /* 📋 TABLE HUD */
    .terminal-card { background: #fff; border-radius: 25px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid #eef2f6; }
    .table-hud thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px; border: none; font-weight: 800; }
    .row-clickable { cursor: pointer; transition: 0.3s; }
    .row-clickable:hover { background-color: rgba(67, 97, 238, 0.04) !important; }
    .yield-pill { padding: 6px 12px; border-radius: 10px; font-family: 'JetBrains Mono'; font-weight: 800; font-size: 13px; }
    .ng-mini-pill { background: #fff1f2; color: var(--ind-danger); font-size: 9px; padding: 2px 8px; border-radius: 6px; border: 1px solid #fecdd3; font-family: 'JetBrains Mono'; font-weight: 700; margin-right: 3px; display: inline-block; margin-top: 4px; }

    /* 🖨️ PRINT-ONLY CSS (Sakti) */
    .print-header { display: none; }
    .print-footer { display: none; }

    @media print {
        @page { size: A4 landscape; margin: 10mm; }
        body { background: white !important; color: black !important; }
        .no-print { display: none !important; }
        .print-header { display: block !important; border-bottom: 4px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .print-footer { display: block !important; margin-top: 30px; }
        .stat-card { border: 1px solid #000 !important; box-shadow: none !important; break-inside: avoid; }
        .terminal-card { border: 1px solid #000 !important; box-shadow: none !important; border-radius: 0 !important; }
        .table-hud thead th { background: #eee !important; color: black !important; border: 1px solid #000 !important; }
        .table-hud td { border: 1px solid #000 !important; }
        .yield-pill { border: 1px solid #000 !important; background: transparent !important; color: black !important; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        #trendChart { width: 100% !important; height: 250px !important; }
    }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">

    {{-- 🏛️ PRINT HEADER (Muncul cuma pas di print) --}}
    <div class="print-header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 70px;">
                    <div style="width: 60px; height: 60px; background: #000; color: #fff; text-align: center; line-height: 60px; font-weight: 900; border-radius: 8px;">AMA</div>
                </td>
                <td>
                    <h2 style="margin: 0; font-family: 'Orbitron'; font-weight: 900;">PT ASALTA MANDIRI AGUNG</h2>
                    <p style="margin: 0; font-size: 12px; font-weight: 700;">Kawasan Industri Mitrakarawang, Jl. Mitra Raya II, Karawang, Jawa Barat</p>
                    <p style="margin: 0; font-size: 11px;">Document: Production & Quality Audit Report // History v4.5</p>
                </td>
                <td style="text-align: right; vertical-align: bottom;">
                    <p style="margin: 0; font-size: 12px; font-weight: 700;">DATE RANGE: {{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }}</p>
                </td>
            </tr>
        </table>
    </div>
    
    {{-- 🛰️ 1. TOP COMMAND BAR (No-Print) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 no-print">
        <div>
            <h2 class="heading-cyber m-0 text-dark">PRODUCTION_AUDIT <span class="text-primary">v4.5</span></h2>
            <p class="text-muted small font-weight-bold mb-0 uppercase"><i class="fas fa-history text-primary mr-2"></i> PT ASALTA MANDIRI AGUNG KARAWANG</p>
        </div>
        
        <form action="{{ route('produksi.history') }}" method="GET" class="filter-bar d-flex align-items-center mt-3 mt-md-0 shadow-sm">
            <div class="d-flex align-items-center">
                <i class="fas fa-calendar-alt text-primary mr-3"></i>
                <input type="date" name="start_date" value="{{ $startDate }}" class="input-date-custom">
                <span class="mx-3 text-muted font-weight-bold">TO</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="input-date-custom">
            </div>
            <div class="ml-4 border-left pl-4">
                <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-sm">
                    <i class="fas fa-sync-alt mr-2"></i> SYNC
                </button>
            </div>
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
    <div class="row mb-5">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="card-strip bg-primary"></div>
                <div class="stat-label">Material Take-In</div>
                <div class="stat-value text-primary">{{ number_format($totalAmbil) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="stat-card">
                <div class="card-strip bg-success"></div>
                <div class="stat-label">Passed Good</div>
                <div class="stat-value text-success">{{ number_format($totalOk) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="stat-card">
                <div class="card-strip bg-danger"></div>
                <div class="stat-label">Reject Items</div>
                <div class="stat-value text-danger">{{ number_format($totalNg) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="stat-card">
                <div class="card-strip bg-info"></div>
                <div class="stat-label">Return to RM</div>
                <div class="stat-value text-info">{{ number_format($totalRet) }}</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card bg-dark">
                <div class="stat-label text-white-50">Operational Yield</div>
                <div class="stat-value text-white">{{ number_format($avgYield, 1) }}%</div>
            </div>
        </div>
    </div>

    {{-- 📈 3. CHART & ACTION --}}
    <div class="row mb-5">
        <div class="col-md-9">
            <div class="terminal-card p-4">
                <h6 class="font-weight-black text-muted small uppercase mb-4 tracking-widest"><i class="fas fa-chart-area mr-2"></i> Quality Stability Analysis</h6>
                <div id="trendChart"></div>
            </div>
        </div>
        <div class="col-md-3 no-print">
            <div class="stat-card d-flex flex-column justify-content-center text-center p-4">
                <div class="bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-print text-dark"></i>
                </div>
                <h5 class="font-weight-black uppercase small mb-3">Export System</h5>
                <button onclick="window.print()" class="btn btn-dark btn-block font-weight-black py-3 rounded-xl mb-3 shadow-lg">PRINT REPORT</button>
                <a href="{{ route('produksi.index') }}" class="btn btn-outline-primary btn-block font-weight-bold py-3 rounded-xl">TERMINAL</a>
            </div>
        </div>
    </div>

    {{-- 📋 4. TABLE LOG --}}
    <div class="terminal-card shadow-lg mb-5">
        <div class="table-responsive">
            <table class="table table-hud mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Prod Timestamp</th>
                        <th>Batch_No</th>
                        <th class="text-left">Part Identification</th>
                        <th class="bg-light">Take</th>
                        <th class="text-success">OK</th>
                        <th class="text-danger">NG</th>
                        <th class="text-info">RET</th>
                        <th>Yield</th>
                        <th class="text-left">Defect Breakdown</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $h)
                    @php 
                        $rincian = DB::table('production_ng_logs')->where('no_produksi', $h->no_produksi)->get();
                        $h->specific_ng = $rincian; 
                        $batchOk = (float)$h->qty_hasil_ok;
                        $batchNg = (float)$h->qty_hasil_ng;
                        $yield = ($batchOk + $batchNg) > 0 ? ($batchOk / ($batchOk + $batchNg)) * 100 : 0;
                        $color = ($yield >= 95) ? 'var(--ind-success)' : (($yield >= 85) ? 'var(--ind-warning)' : 'var(--ind-danger)');
                    @endphp
                    <tr class="row-clickable" onclick="showDetail({{ json_encode($h) }})">
                        <td class="text-left pl-5">
                            <div class="font-weight-black text-dark" style="font-size: 12px;">{{ date('d/m/y', strtotime($h->created_at)) }}</div>
                            <div class="small text-primary font-weight-bold" style="font-family: 'JetBrains Mono';">{{ date('H:i', strtotime($h->created_at)) }}</div>
                        </td>
                        <td class="small font-weight-bold text-muted">{{ $h->no_produksi }}</td>
                        <td class="text-left font-weight-black text-dark pl-4">> {{ $h->material_code }}</td>
                        <td class="bg-light font-weight-black font-mono">{{ number_format($h->qty_ambil_pcs) }}</td>
                        <td class="text-success font-weight-black font-mono">{{ number_format($batchOk) }}</td>
                        <td class="text-danger font-weight-black font-mono">{{ number_format($batchNg) }}</td>
                        <td class="text-info font-weight-black font-mono">{{ number_format($h->qty_return_warehouse) }}</td>
                        <td><b style="color: {{ $color }}; font-family: 'JetBrains Mono'; font-size: 13px;">{{ number_format($yield, 1) }}%</b></td>
                        <td class="text-left">
                            @if($rincian->count() > 0)
                                @foreach($rincian as $r)
                                    <span class="ng-mini-pill">{{ strtoupper($r->ng_type) }}({{ $r->qty }})</span>
                                @endforeach
                            @else
                                <small class="text-muted italic">Regular_Flow</small>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 🖋️ PRINT SIGNATURE (Cuma Muncul pas di print) --}}
    <div class="print-footer">
        <table style="width: 100%; text-align: center;">
            <tr>
                <td style="width: 33%;">
                    <p style="margin-bottom: 60px;">Prepared by (Operator),</p>
                    <p><b>____________________</b></p>
                </td>
                <td style="width: 33%;">
                    <p style="margin-bottom: 60px;">Checked by (QC),</p>
                    <p><b>____________________</b></p>
                </td>
                <td style="width: 33%;">
                    <p style="margin-bottom: 60px;">Approved by (Supervisor),</p>
                    <p><b>____________________</b></p>
                </td>
            </tr>
        </table>
        <p style="text-align: right; font-size: 10px; margin-top: 20px;">Printed by System // {{ Auth::user()->name }} // {{ now() }}</p>
    </div>
</div>

{{-- MODAL DETAIL (Disesuaikan) --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-2xl border-0" style="border-radius: 30px;">
            <div class="modal-header bg-dark text-white border-0 py-4 px-5">
                <h6 class="modal-title font-weight-black uppercase tracking-widest">Audit_Batch_Report</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-5 bg-white">
                <div id="donut-target" class="text-center"></div>
                <div class="h4 font-weight-black text-primary mt-4 text-center" id="det-batch" style="font-family: 'Orbitron';"></div>
                <hr>
                <div class="row text-center mb-4">
                    <div class="col-4 border-right"><small class="stat-label">OK Goods</small><div class="text-success font-weight-black h5" id="det-ok">0</div></div>
                    <div class="col-4 border-right"><small class="stat-label">NG Reject</small><div class="text-danger font-weight-black h5" id="det-ng">0</div></div>
                    <div class="col-4"><small class="stat-label">Return RM</small><div class="text-info font-weight-black h5" id="det-ret">0</div></div>
                </div>
                <div class="mb-4">
                    <h6 class="font-weight-black text-danger small mb-2 uppercase border-bottom pb-2">NG Breakdown:</h6>
                    <div id="det-ng-list" class="bg-light p-3 rounded-2xl border-dashed"></div>
                </div>
                <div class="p-3 bg-light rounded-xl border">
                    <small class="text-uppercase font-weight-black text-muted d-block mb-1" style="font-size: 8px;">Note:</small>
                    <p class="mb-0 font-weight-bold text-dark small" id="det-remark">-</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 📊 CHART LOGIC
    const chartData = @json($history->take(15)->reverse()->values());
    const options = {
        series: [{
            name: 'Yield %',
            data: chartData.map(h => {
                const ok = parseFloat(h.qty_hasil_ok) || 0;
                const ng = parseFloat(h.qty_hasil_ng) || 0;
                return (ok + ng) > 0 ? ((ok / (ok + ng)) * 100).toFixed(1) : 0;
            })
        }],
        chart: { type: 'area', height: 280, toolbar: { show: false }, zoom: { enabled: false } },
        colors: ['#4361ee'],
        stroke: { curve: 'smooth', width: 4 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
        xaxis: { categories: chartData.map(h => h.no_produksi.substr(-6)), labels: { style: { fontSize: '10px', fontWeight: 700 } } },
        yaxis: { max: 100, min: 0, labels: { formatter: (val) => val + "%" } },
        grid: { borderColor: '#f1f5f9' }
    };
    new ApexCharts(document.querySelector("#trendChart"), options).render();

    // 🛡️ MODAL DETAIL
    function showDetail(h) {
        document.getElementById('det-batch').innerText = h.no_produksi;
        document.getElementById('det-ok').innerText = h.qty_hasil_ok;
        document.getElementById('det-ng').innerText = h.qty_hasil_ng;
        document.getElementById('det-ret').innerText = h.qty_return_warehouse;
        document.getElementById('det-remark').innerText = h.keterangan || 'No Note';

        const listDiv = document.getElementById('det-ng-list');
        listDiv.innerHTML = '';
        if (h.specific_ng && h.specific_ng.length > 0) {
            h.specific_ng.forEach(item => {
                listDiv.innerHTML += `
                    <div class="d-flex justify-content-between mb-2">
                        <span class="font-weight-bold text-danger small">• ${item.ng_type}</span>
                        <span class="badge badge-danger">${item.qty} PCS</span>
                    </div>`;
            });
        } else {
            listDiv.innerHTML = '<div class="text-center text-muted small">-- NO DEFECTS --</div>';
        }
        $('#detailModal').modal('show');
    }
</script>
@endsection