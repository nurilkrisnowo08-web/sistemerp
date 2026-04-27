@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
    .card-custom { border-radius: 15px; border: none; transition: 0.3s; }
    .card-custom:hover { transform: translateY(-5px); }
    .font-black { font-weight: 800; letter-spacing: -0.5px; }
    .bg-gradient-dark { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }
    .table-qc { font-size: 12px; }
    .table-qc thead th { background: #f8fafc; text-transform: uppercase; font-size: 10px; color: #64748b; border-bottom: 2px solid #e2e8f0; }
    .progress-ng { height: 8px; border-radius: 10px; background: #f1f5f9; }
    
    /* Hover Effect Table */
    .row-clickable { cursor: pointer; transition: 0.2s; }
    .row-clickable:hover { background-color: #f0f7ff !important; border-left: 4px solid #2563eb; }
    .batch-id-pill { font-family: 'JetBrains Mono'; font-size: 10px; background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 4px; font-weight: 700; }
</style>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-black text-dark mb-0">QUALITY_CONTROL_HUB</h2>
            <p class="text-muted small font-weight-bold uppercase mb-0">Monitoring Live Produksi & Analisa NG // PT ASALTA MANDIRI AGUNG</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form action="" method="GET" class="mr-2">
                <input type="date" name="date" class="form-control rounded-pill border-dark px-4 shadow-sm" 
                       value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <a href="{{ route('ppic.mps.index') }}" class="btn btn-outline-dark rounded-pill px-4 font-weight-bold shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> BACK TO MPS
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-custom shadow-sm bg-white p-4 border-left border-success" style="border-left-width: 8px !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted small font-weight-bold uppercase mb-1">Passed Good (OK)</h6>
                        <h2 class="font-black mb-0 text-success">{{ number_format($summary->total_ok ?? 0) }}</h2>
                    </div>
                    <div class="p-3 rounded-circle text-success" style="background: rgba(16, 185, 129, 0.1)">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom shadow-sm bg-white p-4 border-left border-danger" style="border-left-width: 8px !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted small font-weight-bold uppercase mb-1">Total Rejected (NG)</h6>
                        <h2 class="font-black mb-0 text-danger">{{ number_format($summary->total_ng ?? 0) }}</h2>
                    </div>
                    <div class="p-3 rounded-circle text-danger" style="background: rgba(239, 68, 68, 0.1)">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom shadow-sm bg-gradient-dark text-white p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="small font-weight-bold uppercase mb-1 text-info">Overall Yield Rate</h6>
                        @php 
                            $total = ($summary->total_ok ?? 0) + ($summary->total_ng ?? 0);
                            $yield = $total > 0 ? round(($summary->total_ok / $total) * 100, 1) : 0;
                        @endphp
                        <h2 class="font-black mb-0 text-warning">{{ $yield }}%</h2>
                    </div>
                    <div class="p-3 rounded-circle" style="background: rgba(255,255,255,0.1)">
                        <i class="fas fa-chart-line fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card card-custom shadow-sm p-4 bg-white" style="min-height: 450px;">
                <h5 class="font-black mb-4"><i class="fas fa-search-minus mr-2 text-danger"></i> REJECT_BREAKDOWN</h5>
                <div class="ng-list">
                    @forelse($ngRanking as $ng)
                    <div class="mb-4">
                        @php $pct = ($summary->total_ng > 0) ? ($ng->total / $summary->total_ng) * 100 : 0; @endphp
                        <div class="d-flex justify-content-between mb-1">
                            <span class="font-weight-bold small text-dark">{{ strtoupper($ng->ng_type) }}</span>
                            <span class="font-weight-bold small text-danger">{{ number_format($ng->total) }} Pcs ({{ round($pct, 1) }}%)</span>
                        </div>
                        <div class="progress progress-ng">
                            <div class="progress-bar bg-danger shadow-sm" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <p class="text-muted mt-3 small font-weight-bold">NO REJECT DATA DETECTED</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card card-custom shadow-sm p-0 bg-white overflow-hidden">
                <div class="p-4 bg-light d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="font-black mb-0"><i class="fas fa-stream mr-2 text-primary"></i> ACTUAL_PRODUCTION_LOG</h5>
                    <span class="badge badge-dark rounded-pill px-3">{{ count($details) }} Entries</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-qc mb-0 text-center">
                        <thead>
                            <tr>
                                <th class="text-left pl-4">Part Identification</th>
                                <th>Line</th>
                                <th>Shift</th>
                                <th>Passed Good</th>
                                <th>NG Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $d)
                            <tr class="row-clickable" onclick="showBatchDetail({{ json_encode($d->part_no) }}, {{ json_encode($d->batches) }})">
                                <td class="text-left pl-4">
                                    <span class="font-weight-bold text-primary">{{ $d->part_no }}</span>
                                    <br><small class="text-muted">Click to view batches</small>
                                </td>
                                <td><span class="badge badge-outline-dark font-weight-bold">{{ $d->line_code }}</span></td>
                                <td>
                                    <span class="badge {{ $d->shift == 'Pagi' ? 'badge-warning' : 'badge-dark' }} px-3">
                                        {{ strtoupper($d->shift) }}
                                    </span>
                                </td>
                                <td class="text-success font-weight-bold" style="font-size: 14px;">{{ number_format($d->qty_ok) }}</td>
                                <td class="text-danger font-weight-bold" style="font-size: 14px;">{{ number_format($d->qty_ng) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBatchDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-primary text-white py-4" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title font-black" id="modalPartName">PART_NAME_HERE</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="bg-light">
                            <tr class="text-center">
                                <th class="text-left pl-4">No Produksi</th>
                                <th>Line</th>
                                <th>Ambil (PCS)</th>
                                <th>OK (PCS)</th>
                                <th>NG (PCS)</th>
                            </tr>
                        </thead>
                        <tbody id="batchTableBody">
                            </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light" style="border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-dark rounded-pill px-4 font-weight-bold" data-dismiss="modal">CLOSE</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showBatchDetail(partNo, batches) {
        document.getElementById('modalPartName').innerText = "BATCH_DRILLDOWN: " + partNo;
        const tbody = document.getElementById('batchTableBody');
        tbody.innerHTML = '';

        if (batches.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">No specific batch data available</td></tr>';
        } else {
            batches.forEach(b => {
                tbody.innerHTML += `
                    <tr class="text-center font-weight-bold">
                        <td class="text-left pl-4"><span class="batch-id-pill">${b.no_produksi}</span></td>
                        <td>${b.kode_Line || '-'}</td>
                        <td class="text-dark">${parseInt(b.qty_ambil_pcs).toLocaleString()}</td>
                        <td class="text-success">${parseInt(b.qty_hasil_ok).toLocaleString()}</td>
                        <td class="text-danger">${parseInt(b.qty_hasil_ng).toLocaleString()}</td>
                    </tr>
                `;
            });
        }
        $('#modalBatchDetail').modal('show');
    }
</script>

@endsection