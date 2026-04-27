@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-warning: #f59e0b; --dark-surface: #0f172a; --bg-main: #f8fafc;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #334155; }
    
    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(135deg, var(--brand-primary), #7209b7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    /* 📈 LEDGER CONTAINER */
    .ledger-container { background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.04); }
    .table-ledger thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 18px; border-bottom: 2px solid #edf2f7; }
    .table-ledger td { padding: 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 13px; }
    .row-clickable { cursor: pointer; transition: 0.2s; }
    .row-clickable:hover { background-color: #f8faff !important; transform: scale(1.002); }

    /* 📊 STATS CARDS */
    .stat-card { background: #fff; border-radius: 20px; padding: 20px; border: 1px solid #e2e8f0; transition: 0.3s; box-shadow: 0 4px 12px rgba(0,0,0,0.02); height: 100%; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px rgba(0,0,0,0.05); }
    .stat-label { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 5px; }
    .stat-value { font-family: 'Orbitron'; font-size: 22px; font-weight: 800; color: var(--dark-surface); }

    /* 🏷️ BADGES */
    .yield-badge { padding: 5px 12px; border-radius: 10px; font-family: 'JetBrains Mono'; font-weight: 800; font-size: 12px; }
    .station-badge { background: var(--dark-surface); color: var(--brand-warning); font-family: 'JetBrains Mono'; font-size: 10px; padding: 3px 10px; border-radius: 6px; }

    /* 🛡️ MODAL UI */
    .modal-content { border-radius: 30px; border: none; overflow: hidden; }
    .ng-detail-box { background: #fff5f5; border: 1px solid #fed7d7; border-radius: 15px; padding: 15px; }
    .ng-item { display: flex; justify-content: space-between; border-bottom: 1px dashed #feb2b2; padding: 8px 0; font-family: 'JetBrains Mono'; font-weight: 700; font-size: 13px; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    {{-- 🛰️ HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding_History <span style="-webkit-text-fill-color: var(--dark-surface);">Audit</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0"><i class="fas fa-file-invoice text-primary mr-2"></i> Final Inspection Logs // Unit Verification</p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
            <button onclick="window.print()" class="btn btn-white rounded-pill px-4 font-weight-extrabold border mr-2 shadow-sm">
                <i class="fas fa-print mr-2"></i> EXPORT_PDF
            </button>
            <a href="{{ route('welding.index') }}" class="btn btn-primary rounded-pill px-4 font-weight-extrabold shadow-lg">
                <i class="fas fa-desktop mr-2"></i> TERMINAL
            </a>
        </div>
    </div>

    {{-- 📊 SUMMARY STATS --}}
    @php
        $totalBatch = $historyData->count();
        $totalAmbil = $historyData->sum('qty_masuk');
        $totalOk = $historyData->sum('qty_ok');
        $totalNg = $historyData->sum('qty_ng');
        $yield = $totalAmbil > 0 ? ($totalOk / $totalAmbil) * 100 : 0;
    @endphp
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card border-left border-primary" style="border-left-width: 5px !important;">
                <div class="stat-label">Total Material Processed</div>
                <div class="stat-value">{{ number_format($totalAmbil) }} <small class="text-muted" style="font-size: 10px;">PCS</small></div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card border-left border-success" style="border-left-width: 5px !important;">
                <div class="stat-label">Total Passed Good</div>
                <div class="stat-value text-success">{{ number_format($totalOk) }}</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card border-left border-danger" style="border-left-width: 5px !important;">
                <div class="stat-label">Total Defect (NG)</div>
                <div class="stat-value text-danger">{{ number_format($totalNg) }}</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card bg-dark text-white shadow-xl">
                <div class="stat-label text-white-50">Global Yield Accuracy</div>
                <div class="stat-value text-white">{{ number_format($yield, 1) }}%</div>
            </div>
        </div>
    </div>

    {{-- 📉 TREND CHART --}}
    <div class="card border-0 shadow-sm mb-4 p-4" style="border-radius: 24px;">
        <h6 class="stat-label mb-4"><i class="fas fa-chart-area mr-2"></i> Quality Stability Trend (Last 15 Batches)</h6>
        <div id="yieldChart" style="min-height: 300px;"></div>
    </div>

    {{-- 📊 HISTORY TABLE --}}
    <div class="ledger-container animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Timestamp</th>
                        <th>Station</th>
                        <th class="text-left">Batch_No / Part_No</th>
                        <th>DEPLOYED</th>
                        <th class="text-success">OK</th>
                        <th class="text-danger">NG</th>
                        <th>YIELD</th>
                        <th class="text-right pr-4">Log</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($historyData as $h)
                    @php
                        $batchTotal = (float)$h->qty_ok + (float)$h->qty_ng;
                        $batchYield = $batchTotal > 0 ? ($h->qty_ok / $batchTotal) * 100 : 0;
                        
                        // ✨ AMBIL RINCIAN NG UNTUK MODAL
                        $ngDetails = DB::table('production_ng_logs')
                            ->where('no_produksi', $h->no_produksi_stamping)
                            ->get();
                        $h->ng_details = $ngDetails;
                    @endphp
                    <tr class="row-clickable" onclick="showDetail({{ json_encode($h) }})">
                        <td class="text-left pl-4 text-muted small">
                            {{ date('d/m/Y', strtotime($h->created_at)) }}<br>
                            <span class="font-weight-bold text-dark">{{ date('H:i', strtotime($h->created_at)) }}</span>
                        </td>
                        <td><span class="station-badge">{{ $h->kode_line ?? 'N/A' }}</span></td>
                        <td class="text-left">
                            <div class="font-weight-black text-primary font-mono" style="font-size: 12px;">{{ $h->no_produksi_stamping }}</div>
                            <div class="text-dark font-weight-bold uppercase">{{ $h->part_no }}</div>
                        </td>
                        <td class="font-weight-black text-muted">{{ number_format($h->qty_masuk) }}</td>
                        <td class="text-success font-weight-black">{{ number_format($h->qty_ok) }}</td>
                        <td class="text-danger font-weight-black">{{ number_format($h->qty_ng) }}</td>
                        <td>
                            <span class="yield-badge {{ $batchYield >= 98 ? 'bg-success text-white' : 'bg-warning text-dark' }}">
                                {{ number_format($batchYield, 1) }}%
                            </span>
                        </td>
                        <td class="text-right pr-4 text-muted">
                            <i class="fas fa-chevron-right"></i>
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
        <div class="modal-content shadow-2xl animate__animated animate__zoomIn">
            <div class="modal-header bg-dark text-white p-4">
                <h6 class="modal-title font-weight-bold uppercase" style="font-family: 'Orbitron'; letter-spacing: 1px;">Batch_Audit_Report</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div id="donut-yield"></div>
                    <h5 class="font-weight-black text-dark mb-0 mt-2" id="det-part"></h5>
                    <span class="badge badge-light border px-3 py-1 font-weight-bold mt-1" id="det-batch"></span>
                </div>

                <div class="row mb-4">
                    <div class="col-4 text-center border-right">
                        <small class="stat-label">Deployed</small>
                        <div class="h5 font-weight-bold mb-0" id="det-masuk">0</div>
                    </div>
                    <div class="col-4 text-center border-right">
                        <small class="stat-label text-success">Passed OK</small>
                        <div class="h5 font-weight-bold text-success mb-0" id="det-ok">0</div>
                    </div>
                    <div class="col-4 text-center">
                        <small class="stat-label text-danger">Rejected</small>
                        <div class="h5 font-weight-bold text-danger mb-0" id="det-ng">0</div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="stat-label text-danger font-weight-black border-bottom pb-2 mb-3">Specific Defect Log (NG Breakdown)</h6>
                    <div id="ng-list-container" class="ng-detail-box">
                        </div>
                </div>

                <div class="bg-light p-3 rounded-2xl">
                    <small class="stat-label d-block mb-1">Process Remarks</small>
                    <p class="mb-0 font-weight-bold text-dark small italic" id="det-remark"></p>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button class="btn btn-dark btn-block py-3 font-weight-black rounded-2xl shadow-lg" data-dismiss="modal">CLOSE AUDIT REPORT</button>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. TREND CHART (Last 15 Batches)
    const historyData = @json($historyData->take(15)->reverse()->values());
    const options = {
        series: [{
            name: 'Yield Accuracy',
            data: historyData.map(h => {
                const total = parseFloat(h.qty_ok) + parseFloat(h.qty_ng);
                return total > 0 ? ((h.qty_ok / total) * 100).toFixed(1) : 0;
            })
        }],
        chart: { type: 'area', height: 300, toolbar: { show: false }, zoom: { enabled: false } },
        colors: ['#4361ee'],
        stroke: { curve: 'smooth', width: 4 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
        xaxis: { 
            categories: historyData.map(h => h.no_produksi_stamping.substr(-6)),
            labels: { style: { fontSize: '10px', fontWeight: 700 } }
        },
        yaxis: { max: 100, min: 80, labels: { formatter: (val) => val + "%" } }
    };
    new ApexCharts(document.querySelector("#yieldChart"), options).render();

    // 2. MODAL DETAIL FUNCTION
    function showDetail(h) {
        document.getElementById('det-part').innerText = h.part_no;
        document.getElementById('det-batch').innerText = h.no_produksi_stamping;
        document.getElementById('det-masuk').innerText = h.qty_masuk.toLocaleString();
        document.getElementById('det-ok').innerText = h.qty_ok.toLocaleString();
        document.getElementById('det-ng').innerText = h.qty_ng.toLocaleString();
        document.getElementById('det-remark').innerText = h.keterangan || 'No specific abnormalities recorded.';

        // NG Breakdown List
        const container = document.getElementById('ng-list-container');
        container.innerHTML = '';
        
        if (h.ng_details && h.ng_details.length > 0) {
            h.ng_details.forEach(item => {
                container.innerHTML += `
                    <div class="ng-item">
                        <span class="text-danger">• ${item.ng_type.toUpperCase()}</span>
                        <span class="text-dark">${item.qty} PCS</span>
                    </div>
                `;
            });
        } else {
            container.innerHTML = '<div class="text-center text-muted small py-2">-- No Defect Data --</div>';
        }

        $('#detailModal').modal('show');
    }
</script>
@endsection