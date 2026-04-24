@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root { 
        --ind-steel: #4e73df; --ind-success: #1cc88a; 
        --ind-danger: #e74a3b; --ind-warning: #f6c23e; 
    }
    
    body { font-family: 'Inter', sans-serif; background-color: #f8f9fc; color: #2d3436; }
    .industrial-header { font-family: 'Orbitron', sans-serif; letter-spacing: -1px; }

    /* Stats Cards */
    .stat-card { background: #fff; border-radius: 15px; padding: 20px; border: 1px solid #e3e6f0; transition: 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.03); }
    .stat-card:hover { transform: translateY(-5px); border-color: var(--ind-steel); }
    .stat-label { font-size: 10px; font-weight: 800; color: #858796; text-transform: uppercase; }
    .stat-value { font-family: 'Orbitron'; font-size: 22px; font-weight: 800; color: #2d3436; }

    /* Table HUD */
    .terminal-card { background: #fff; border: 1px solid #e3e6f0; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; }
    .table-hud thead th { background: #f8f9fc; color: var(--ind-steel); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; padding: 18px; border-bottom: 2px solid #eaecf4; }
    .row-clickable { cursor: pointer; transition: 0.2s; }
    .row-clickable:hover { background-color: #f8faff !important; }
    .id-tag { font-family: 'JetBrains Mono'; font-size: 12px; font-weight: 700; color: var(--ind-steel); }

    /* Donut Chart Style */
    .donut-container { width: 140px; height: 140px; margin: 0 auto; position: relative; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: conic-gradient(var(--c) var(--p), #eaecf4 0); }
    .donut-container::after { content: ""; position: absolute; width: 110px; height: 110px; background: #fff; border-radius: 50%; }
    .donut-text { position: relative; z-index: 2; font-family: 'Orbitron'; font-weight: 800; font-size: 20px; color: var(--c); }

    .ng-mini-list { font-size: 10px; color: var(--ind-danger); font-family: 'JetBrains Mono'; margin-top: 5px; font-weight: 700; }
    .ng-detail-box { background: #fff5f5; border: 1px solid #fed7d7; border-radius: 10px; padding: 12px; }
    .ng-detail-item { display: flex; justify-content: space-between; border-bottom: 1px dashed #feb2b2; padding: 5px 0; font-size: 12px; font-family: 'JetBrains Mono'; font-weight: 700; }
</style>

<div class="container-fluid py-4">
    {{-- 1. HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="industrial-header font-weight-bold text-dark mb-0">PRODUCTION_HISTORY <span class="text-primary">v4.0</span></h3>
            <p class="text-muted small font-weight-bold uppercase mb-0">PT. ASALTA MANDIRI AGUNG // AUDIT_FEED_ACTIVE</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 font-weight-bold shadow-sm mr-2">
                <i class="fas fa-print mr-2"></i> GENERATE_PDF
            </button>
            <a href="{{ route('produksi.index') }}" class="btn btn-outline-primary rounded-pill px-4 font-weight-bold shadow-sm border-2">
                <i class="fas fa-desktop mr-2"></i> MONITORING
            </a>
        </div>
    </div>

    {{-- 2. STATS OVERVIEW --}}
    @php
        $totalOk = $history->sum('qty_hasil_ok');
        $totalNg = $history->sum('qty_hasil_ng');
        $grandTotal = $totalOk + $totalNg;
        $avgYield = $grandTotal > 0 ? ($totalOk / $grandTotal) * 100 : 0;
    @endphp
    <div class="row mb-4">
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Total Good Items</div><div class="stat-value text-success">{{ number_format($totalOk) }}</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Total Rejected</div><div class="stat-value text-danger">{{ number_format($totalNg) }}</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Production Yield</div><div class="stat-value text-primary">{{ number_format($avgYield, 1) }}%</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Total Batches</div><div class="stat-value text-dark">{{ $history->count() }}</div></div></div>
    </div>

    {{-- 3. TREND CHART --}}
    <div class="terminal-card p-4 mb-4">
        <h6 class="font-weight-bold text-muted small uppercase mb-4 tracking-widest">Quality Stability Trend (Last 15 Batches)</h6>
        <div id="trendChart" style="min-height: 300px;"></div>
    </div>

    {{-- 4. TABLE LOG --}}
    <div class="terminal-card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hud mb-0 text-center">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Batch_No</th>
                        <th class="text-left">Part_Identification</th>
                        <th>OK (PCS)</th>
                        <th>NG (PCS)</th>
                        <th>Yield</th>
                        <th class="text-left">Specific NG & Remarks</th>
                    </tr>
                </thead>
                <tbody id="historyLogBody">
                    @foreach($history as $h)
                    @php 
                        $batchTotal = (float)$h->qty_hasil_ok + (float)$h->qty_hasil_ng;
                        $yield = $batchTotal > 0 ? ($h->qty_hasil_ok / $batchTotal) * 100 : 0;
                        $color = ($yield >= 95) ? 'var(--ind-success)' : (($yield >= 85) ? 'var(--ind-warning)' : 'var(--ind-danger)');

                        // ✨ FIX: AMBIL DATA MURNI BERDASARKAN NO_PRODUKSI (AGAR TIDAK NGACO)
                        $rincian = DB::table('production_ng_logs')
                            ->where('no_produksi', $h->no_produksi)
                            ->select('ng_type', 'qty')
                            ->get();
                        
                        $h->specific_ng = $rincian; 
                    @endphp
                    <tr class="row-clickable" onclick="showDetail({{ json_encode($h) }})">
                        <td class="text-muted small">{{ date('d/m/y H:i', strtotime($h->updated_at)) }}</td>
                        <td class="id-tag">{{ $h->no_produksi }}</td>
                        <td class="text-left font-weight-bold pl-4">> {{ $h->material_code }}</td>
                        <td class="text-success font-weight-bold">{{ number_format($h->qty_hasil_ok) }}</td>
                        <td class="text-danger font-weight-bold">{{ number_format($h->qty_hasil_ng) }}</td>
                        <td><b style="color: {{ $color }}; font-size: 14px;">{{ number_format($yield, 1) }}%</b></td>
                        <td class="text-left">
                            <div class="small italic text-dark font-weight-bold">{{ $h->keterangan ?? '-' }}</div>
                            @if($rincian->count() > 0)
                                <div class="ng-mini-list">
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

{{-- 🛡️ MODAL DETAIL --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h6 class="modal-title font-weight-bold uppercase tracking-widest">Audit Batch Detail</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4 text-left bg-white">
                <div class="text-center mb-4">
                    <div id="donut-target"></div>
                    <small class="font-weight-bold text-muted d-block mt-3 uppercase">Batch Yield Accuracy</small>
                </div>

                <div class="row mb-3 text-center small font-weight-bold">
                    <div class="col-6 pr-1"><div class="p-3 bg-light rounded-lg border-bottom border-primary"><small class="text-muted d-block uppercase" style="font-size: 8px;">Passed Good</small><b class="text-success h5" id="det-ok">0</b></div></div>
                    <div class="col-6 pl-1"><div class="p-3 bg-light rounded-lg border-bottom border-danger"><small class="text-muted d-block uppercase" style="font-size: 8px;">Total Reject</small><b class="text-danger h5" id="det-ng">0</b></div></div>
                </div>

                <div class="mb-3">
                    <h6 class="font-weight-bold text-danger small mb-2 uppercase border-bottom pb-2">Reject Breakdown (Specific Defects)</h6>
                    <div id="det-ng-list" class="ng-detail-box">
                        </div>
                </div>

                <div class="p-3 bg-light rounded border">
                    <small class="text-uppercase font-weight-bold text-muted" style="font-size: 9px;">Operator Remark :</small>
                    <p class="mb-0 font-weight-bold text-dark small" id="det-remark">-</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. TREND CHART LOGIC
    const chartData = @json($history->take(15)->reverse()->values());
    const options = {
        series: [{
            name: 'Yield %',
            data: chartData.map(h => {
                const total = parseFloat(h.qty_hasil_ok) + parseFloat(h.qty_hasil_ng);
                return total > 0 ? ((h.qty_hasil_ok / total) * 100).toFixed(1) : 0;
            })
        }],
        chart: { type: 'area', height: 300, toolbar: { show: false }, zoom: { enabled: false } },
        colors: ['#4e73df'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
        xaxis: { 
            categories: chartData.map(h => h.no_produksi.substr(-6)),
            labels: { style: { fontSize: '10px', fontWeight: 700 } }
        },
        yaxis: { max: 100, min: 0, labels: { formatter: (val) => val + "%" } }
    };
    new ApexCharts(document.querySelector("#trendChart"), options).render();

    // 2. MODAL DETAIL FUNCTION
    function showDetail(h) {
        const batchTotal = parseFloat(h.qty_hasil_ok) + parseFloat(h.qty_hasil_ng);
        const yieldVal = batchTotal > 0 ? ((h.qty_hasil_ok / batchTotal) * 100).toFixed(0) : 0;
        const color = yieldVal >= 95 ? '#1cc88a' : (yieldVal >= 85 ? '#f6c23e' : '#e74a3b');

        // Render Donut
        document.getElementById('donut-target').innerHTML = `
            <div class="donut-container" style="--p: ${yieldVal}%; --c: ${color};">
                <div class="donut-text">${yieldVal}%</div>
            </div>
        `;

        document.getElementById('det-ok').innerText = parseInt(h.qty_hasil_ok).toLocaleString() + " Pcs";
        document.getElementById('det-ng').innerText = parseInt(h.qty_hasil_ng).toLocaleString() + " Pcs";
        document.getElementById('det-remark').innerText = h.keterangan || 'No specific notes recorded.';

        // Render NG Specifics
        const listDiv = document.getElementById('det-ng-list');
        listDiv.innerHTML = '';
        
        if (h.specific_ng && h.specific_ng.length > 0) {
            h.specific_ng.forEach(item => {
                listDiv.innerHTML += `
                    <div class="ng-detail-item">
                        <span class="text-danger">• ${item.ng_type.toUpperCase()}</span>
                        <span>${item.qty} PCS</span>
                    </div>
                `;
            });
        } else {
            listDiv.innerHTML = '<div class="text-center text-muted small py-2">-- No specific defects reported --</div>';
        }

        $('#detailModal').modal('show');
    }
</script>
@endsection