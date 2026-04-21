@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root {
        --ind-navy: #0f172a; --ind-blue: #2563eb; --ind-amber: #d97706;
        --ind-emerald: #059669; --ind-rose: #e11d48; --bg-main: #f8fafc;
    }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-main); color: #1e293b; }
    .industrial-header { font-family: 'Orbitron', sans-serif; letter-spacing: 1px; color: var(--ind-navy); }
    
    /* Stats Cards */
    .stat-card { background: #fff; border-radius: 20px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.05); }
    .stat-label { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
    .stat-value { font-family: 'Orbitron'; font-size: 24px; font-weight: 800; color: var(--ind-navy); }

    /* Table Design */
    .ledger-container { background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    .table-history thead th { 
        background: #f8fafc; color: #64748b; font-size: 11px; 
        text-transform: uppercase; letter-spacing: 1px; padding: 18px 15px; 
        border-bottom: 2px solid #edf2f7; font-weight: 800;
    }
    .table-history tbody tr { cursor: pointer; transition: 0.2s; }
    .table-history tbody tr:hover { background-color: #f8faff; }
    .table-history td { padding: 16px 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 13px; }

    .origin-badge { font-size: 10px; padding: 4px 10px; border-radius: 6px; font-weight: 800; }
    .yield-badge { padding: 4px 10px; border-radius: 8px; font-family: 'JetBrains Mono'; font-weight: 700; font-size: 12px; }
    
    .chart-box { background: #fff; border-radius: 24px; padding: 25px; border: 1px solid #e2e8f0; margin-bottom: 30px; }
    
    /* Modal Detail */
    .modal-content { border-radius: 24px; border: none; }
    .detail-label { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; }
    .detail-value { font-weight: 700; color: var(--ind-navy); font-size: 15px; }
</style>

<div class="container-fluid py-4">
    {{-- 1. HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-5 bg-white p-4 rounded-xl border shadow-sm">
        <div>
            <h2 class="industrial-header m-0">QUALITY_AUDIT <span class="text-primary">HISTORY</span></h2>
            <small class="text-muted font-weight-bold uppercase">Consolidated QC Verification Logs</small>
        </div>
        <div>
            <a href="{{ route('quality.index') }}" class="btn btn-outline-primary rounded-pill px-4 font-weight-bold">
                <i class="fas fa-shield-alt mr-2"></i> QC TERMINAL
            </a>
        </div>
    </div>

    {{-- 2. STATS --}}
    @php
        $totalOk = $historyData->sum('qty_ok');
        $totalNg = $historyData->sum('qty_ng');
        $grandTotal = $totalOk + $totalNg;
        $avgYield = $grandTotal > 0 ? ($totalOk / $grandTotal) * 100 : 0;
    @endphp
    <div class="row mb-4">
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Total OK Items</div><div class="stat-value text-emerald">{{ number_format($totalOk) }}</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Total NG Items</div><div class="stat-value text-rose">{{ number_format($totalNg) }}</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Success Rate</div><div class="stat-value text-primary">{{ number_format($avgYield, 1) }}%</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-label">Inspections Done</div><div class="stat-value">{{ $historyData->count() }}</div></div></div>
    </div>

    {{-- 3. CHART --}}
    <div class="chart-box shadow-sm">
        <h6 class="font-weight-bold mb-4 text-uppercase small tracking-widest text-muted">Quality Trend Analysis</h6>
        <div id="qualityChart"></div>
    </div>

    {{-- 4. TABLE --}}
    <div class="ledger-container shadow-sm">
        <div class="table-responsive">
            <table class="table table-history mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Timestamp</th>
                        <th>Origin</th>
                        <th>Batch_No</th>
                        <th class="text-left">Part_Identification</th>
                        <th class="text-success">OK</th>
                        <th class="text-danger">NG</th>
                        <th>Yield</th>
                        <th>Inspector</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($historyData as $h)
                    @php
                        $total = (float)$h->qty_ok + (float)$h->qty_ng;
                        $yield = $total > 0 ? ($h->qty_ok / $total) * 100 : 0;
                    @endphp
                    <tr onclick="showDetail({{ json_encode($h) }})">
                        <td class="text-left pl-4 text-muted small">{{ \Carbon\Carbon::parse($h->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="origin-badge {{ $h->origin == 'WELDING' ? 'bg-warning text-dark' : 'bg-info text-white' }}">
                                {{ $h->origin }}
                            </span>
                        </td>
                        <td class="font-weight-bold text-primary" style="font-family: 'JetBrains Mono';">{{ $h->batch_no }}</td>
                        <td class="text-dark font-weight-bold text-left">{{ $h->part_no }}</td>
                        <td class="text-success font-weight-bolder">{{ number_format($h->qty_ok) }}</td>
                        <td class="text-danger font-weight-bolder">{{ number_format($h->qty_ng) }}</td>
                        <td>
                            <span class="yield-badge {{ $yield < 100 ? 'bg-light text-warning border' : 'bg-light text-success border' }}">
                                {{ number_format($yield, 1) }}%
                            </span>
                        </td>
                        <td class="text-uppercase small font-weight-bold">{{ $h->inspector }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="py-5 text-muted">No audit logs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 5. DETAIL MODAL --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-dark text-white px-4 py-3 border-0">
                <h6 class="modal-title font-weight-bold text-uppercase tracking-wider">Inspection Detail Report</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="detail-label">Batch Identifier</div>
                    <div class="h4 font-weight-bold text-primary mt-1" id="det-batch" style="font-family: 'Orbitron';"></div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3 border-right"><div class="detail-label">Part No</div><div class="detail-value" id="det-part"></div></div>
                    <div class="col-6 mb-3"><div class="detail-label">Origin</div><div class="detail-value" id="det-origin"></div></div>
                    <div class="col-4 mb-3 border-right"><div class="detail-label">Qty OK</div><div class="detail-value text-success h5" id="det-ok"></div></div>
                    <div class="col-4 mb-3 border-right"><div class="detail-label">Qty NG</div><div class="detail-value text-danger h5" id="det-ng"></div></div>
                    <div class="col-4 mb-3"><div class="detail-label">Yield</div><div class="detail-value" id="det-yield"></div></div>
                    <div class="col-12 mb-3"><div class="detail-label">Inspector Name</div><div class="detail-value" id="det-inspector"></div></div>
                    <div class="col-12">
                        <div class="detail-label">Analysis / Defect Remark</div>
                        <div class="p-3 bg-light rounded-xl border mt-1 text-muted small" id="det-reason" style="min-height: 80px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. CHART LOGIC
    const historyData = @json($historyData->take(15)->reverse()->values());
    const options = {
        series: [{
            name: 'Yield %',
            data: historyData.map(item => {
                const total = parseFloat(item.qty_ok) + parseFloat(item.qty_ng);
                return total > 0 ? ((item.qty_ok / total) * 100).toFixed(1) : 0;
            })
        }],
        chart: { type: 'area', height: 300, toolbar: { show: false }, zoom: { enabled: false } },
        colors: ['#2563eb'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
        xaxis: { 
            categories: historyData.map(item => item.batch_no.substr(-6)),
            labels: { style: { fontSize: '10px', fontWeight: 600 } }
        },
        yaxis: { max: 100, min: 0, labels: { formatter: val => val + "%" } }
    };
    new ApexCharts(document.querySelector("#qualityChart"), options).render();

    // 2. MODAL DETAIL
    function showDetail(data) {
        const total = parseFloat(data.qty_ok) + parseFloat(data.qty_ng);
        const yieldVal = total > 0 ? ((data.qty_ok / total) * 100).toFixed(1) : 0;

        document.getElementById('det-batch').innerText = data.batch_no;
        document.getElementById('det-part').innerText = data.part_no;
        document.getElementById('det-origin').innerText = data.origin;
        document.getElementById('det-ok').innerText = data.qty_ok + " PCS";
        document.getElementById('det-ng').innerText = data.qty_ng + " PCS";
        document.getElementById('det-yield').innerText = yieldVal + "%";
        document.getElementById('det-inspector').innerText = data.inspector;
        document.getElementById('det-reason').innerText = data.ng_reason || 'No specific defects recorded.';
        
        $('#detailModal').modal('show');
    }
</script>
@endsection