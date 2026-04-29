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

    /* 📊 STATS CARDS CYBER */
    .stat-card { background: #fff; border-radius: 20px; padding: 22px; border: 1px solid rgba(255,255,255,0.8); transition: 0.3s; box-shadow: 0 10px 30px rgba(0,0,0,0.02); height: 100%; position: relative; overflow: hidden; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(67, 97, 238, 0.1); }
    .stat-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }
    .stat-value { font-family: 'Orbitron'; font-size: 24px; font-weight: 900; }
    .card-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 5px; }

    /* 📅 FILTER BAR */
    .filter-bar { background: #fff; border-radius: 15px; padding: 15px 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
    .input-date-custom { border: 2px solid #f1f5f9; border-radius: 10px; font-weight: 700; color: var(--ind-steel); padding: 8px 12px; transition: 0.3s; }
    .input-date-custom:focus { border-color: var(--ind-steel); outline: none; background: #f8faff; }

    /* 📋 TABLE HUD */
    .terminal-card { background: #fff; border-radius: 25px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid #eef2f6; }
    .table-hud thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px; border: none; font-weight: 800; }
    .row-clickable { cursor: pointer; transition: 0.3s; }
    .row-clickable:hover { background-color: rgba(67, 97, 238, 0.04) !important; }
    
    .yield-pill { padding: 6px 12px; border-radius: 10px; font-family: 'JetBrains Mono'; font-weight: 800; font-size: 13px; }
    .ng-mini-pill { background: #fff1f2; color: var(--ind-danger); font-size: 9px; padding: 2px 8px; border-radius: 6px; border: 1px solid #fecdd3; font-family: 'JetBrains Mono'; font-weight: 700; margin-right: 3px; display: inline-block; margin-top: 4px; }

    @media print { .no-print { display: none !important; } .container-fluid { width: 100%; } .terminal-card { border: 1px solid #000; } }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
    {{-- 🛰️ 1. TOP COMMAND BAR --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 no-print">
        <div>
            <h2 class="heading-cyber m-0 text-dark">PRODUCTION_AUDIT <span class="text-primary">v4.5</span></h2>
            <p class="text-muted small font-weight-bold mb-0 uppercase"><i class="fas fa-history text-primary mr-2"></i> Time-Locked Traceability Log</p>
        </div>
        
        <form action="{{ route('produksi.history') }}" method="GET" class="filter-bar d-flex align-items-center mt-3 mt-md-0">
            <div class="d-flex align-items-center">
                <i class="fas fa-calendar-alt text-primary mr-3"></i>
                <input type="date" name="start_date" value="{{ $startDate }}" class="input-date-custom">
                <span class="mx-3 text-muted font-weight-bold">TO</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="input-date-custom">
            </div>
            <div class="ml-4 border-left pl-4">
                <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-sm">
                    <i class="fas fa-sync-alt mr-2"></i> SYNC_DATA
                </button>
            </div>
        </form>
    </div>

    {{-- 🛸 2. STATS OVERVIEW (Dynamic) --}}
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
            <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                <div class="card-strip bg-primary"></div>
                <div class="stat-label">Material Take-In</div>
                <div class="stat-value text-primary">{{ number_format($totalAmbil) }} <small class="h6">PCS</small></div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                <div class="card-strip bg-success"></div>
                <div class="stat-label">Passed Good</div>
                <div class="stat-value text-success">{{ number_format($totalOk) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                <div class="card-strip bg-danger"></div>
                <div class="stat-label">Reject Items</div>
                <div class="stat-value text-danger">{{ number_format($totalNg) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
                <div class="card-strip bg-info"></div>
                <div class="stat-label">Return to RM</div>
                <div class="stat-value text-info">{{ number_format($totalRet) }}</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card bg-dark animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
                <div class="stat-label text-white-50">Operational Yield</div>
                <div class="stat-value text-white">{{ number_format($avgYield, 1) }}%</div>
            </div>
        </div>
    </div>

    {{-- 📈 3. TREND CHART --}}
    <div class="row mb-5 no-print">
        <div class="col-md-9">
            <div class="terminal-card p-4">
                <h6 class="font-weight-black text-muted small uppercase mb-4 tracking-widest"><i class="fas fa-chart-area mr-2"></i> Quality Stability Analysis (Filtered Range)</h6>
                <div id="trendChart"></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card d-flex flex-column justify-content-center text-center p-5">
                <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="fas fa-file-pdf fa-2x text-dark"></i>
                </div>
                <h5 class="font-weight-black uppercase small mb-4">Export Batch Report</h5>
                <button onclick="window.print()" class="btn btn-dark btn-block font-weight-black py-3 rounded-xl mb-3 shadow-lg">PRINT_RECAP</button>
                <a href="{{ route('produksi.index') }}" class="btn btn-outline-primary btn-block font-weight-bold py-3 rounded-xl">TERMINAL</a>
            </div>
        </div>
    </div>

    {{-- 📋 4. TABLE LOG --}}
    <div class="terminal-card shadow-lg animate__animated animate__fadeInUp">
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
                        {{-- ✨ Jam Produksi Asli --}}
                        <td class="text-left pl-5">
                            <div class="font-weight-black text-dark" style="font-size: 13px;">{{ date('d/m/y', strtotime($h->created_at)) }}</div>
                            <div class="small text-primary font-weight-bold" style="font-family: 'JetBrains Mono';">{{ date('H:i', strtotime($h->created_at)) }}</div>
                        </td>
                        <td class="small font-weight-bold text-muted" style="font-family: 'JetBrains Mono';">{{ $h->no_produksi }}</td>
                        <td class="text-left font-weight-black text-dark pl-4">> {{ $h->material_code }}</td>
                        <td class="bg-light font-weight-black font-mono">{{ number_format($h->qty_ambil_pcs) }}</td>
                        <td class="text-success font-weight-black font-mono">{{ number_format($batchOk) }}</td>
                        <td class="text-danger font-weight-black font-mono">{{ number_format($batchNg) }}</td>
                        <td class="text-info font-weight-black font-mono">{{ number_format($h->qty_return_warehouse) }}</td>
                        <td>
                            <span class="yield-pill border" style="color: {{ $color }}; border-color: {{ $color }}33; background: {{ $color }}08;">
                                {{ number_format($yield, 1) }}%
                            </span>
                        </td>
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
</div>

{{-- 🛡️ MODAL DETAIL (Self-Contained) --}}
<div class="modal fade animate__animated animate__zoomIn" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-2xl border-0" style="border-radius: 35px;">
            <div class="modal-header bg-dark text-white border-0 py-4 px-5">
                <h6 class="modal-title font-weight-black uppercase tracking-widest"><i class="fas fa-shield-alt mr-2 text-primary"></i>Audit_Batch_Report</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-5 bg-white">
                <div class="text-center mb-5">
                    <div id="donut-target"></div>
                    <div class="h4 font-weight-black text-primary mt-4" id="det-batch" style="font-family: 'Orbitron';"></div>
                    <small class="font-weight-black text-muted uppercase">Production Batch Identifier</small>
                </div>

                <div class="p-4 bg-dark text-white rounded-3xl mb-4 shadow-xl">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="uppercase font-weight-black text-info" style="font-size: 10px; letter-spacing: 1px;">Material Input (Take)</small>
                        <b class="h4 mb-0 font-weight-black" id="det-ambil" style="font-family: 'Orbitron';">0</b>
                    </div>
                </div>

                <div class="row mb-4 text-center">
                    <div class="col-4 border-right"><small class="stat-label">OK Goods</small><div class="text-success font-weight-black h5 mt-1" id="det-ok">0</div></div>
                    <div class="col-4 border-right"><small class="stat-label">NG Reject</small><div class="text-danger font-weight-black h5 mt-1" id="det-ng">0</div></div>
                    <div class="col-4"><small class="stat-label">Return RM</small><div class="text-info font-weight-black h5 mt-1" id="det-ret">0</div></div>
                </div>

                <div class="mb-4">
                    <h6 class="font-weight-black text-danger small mb-3 uppercase border-bottom pb-2">Reject Breakdown:</h6>
                    <div id="det-ng-list" class="bg-light p-3 rounded-2xl border-dashed">
                        </div>
                </div>

                <div class="p-4 bg-light rounded-3xl border">
                    <small class="text-uppercase font-weight-black text-muted d-block mb-2" style="font-size: 9px;">Operator Remark :</small>
                    <p class="mb-0 font-weight-bold text-dark" id="det-remark" style="font-size: 13px;">-</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 📊 CHART LOGIC (Fixed for Data Range)
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
        chart: { type: 'area', height: 300, toolbar: { show: false }, zoom: { enabled: false }, fontFaminly: 'Plus Jakarta Sans' },
        colors: ['#4361ee'],
        stroke: { curve: 'smooth', width: 4 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
        xaxis: { 
            categories: chartData.map(h => h.no_produksi.substr(-6)),
            labels: { style: { fontSize: '10px', fontWeight: 700 } }
        },
        yaxis: { max: 100, min: 0, labels: { formatter: (val) => val + "%" } },
        grid: { borderColor: '#f1f5f9' }
    };
    new ApexCharts(document.querySelector("#trendChart"), options).render();

    // 🛡️ MODAL DETAIL FUNCTION (Parsing Colleciton)
    function showDetail(h) {
        const ambil = parseInt(h.qty_ambil_pcs) || 0;
        const ok = parseInt(h.qty_hasil_ok) || 0;
        const ng = parseInt(h.qty_hasil_ng) || 0;
        const ret = parseInt(h.qty_return_warehouse) || 0;
        const yieldVal = (ok + ng) > 0 ? ((ok / (ok + ng)) * 100).toFixed(0) : 0;
        
        document.getElementById('det-batch').innerText = h.no_produksi;
        document.getElementById('det-ambil').innerText = ambil.toLocaleString() + " PCS";
        document.getElementById('det-ok').innerText = ok.toLocaleString();
        document.getElementById('det-ng').innerText = ng.toLocaleString();
        document.getElementById('det-ret').innerText = ret.toLocaleString();
        document.getElementById('det-remark').innerText = h.keterangan || 'NO_SPECIFIC_REMARK';

        const listDiv = document.getElementById('det-ng-list');
        listDiv.innerHTML = '';
        if (h.specific_ng && h.specific_ng.length > 0) {
            h.specific_ng.forEach(item => {
                listDiv.innerHTML += `
                    <div class="d-flex justify-content-between align-items-center mb-2 bg-white p-3 rounded-xl shadow-sm">
                        <span class="font-weight-black text-danger small uppercase">• ${item.ng_type}</span>
                        <span class="badge badge-danger rounded-pill px-3 font-weight-black">${item.qty} PCS</span>
                    </div>`;
            });
        } else {
            listDiv.innerHTML = '<div class="text-center text-muted small py-2 font-weight-bold">-- NO DEFECT BREAKDOWN --</div>';
        }

        $('#detailModal').modal('show');
    }
</script>
@endsection