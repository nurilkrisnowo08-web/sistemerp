@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-warning: #f59e0b; --dark-surface: #0f172a; --bg-main: #f8fafc;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #334155; }
    
    .heading-history { font-family: 'Orbitron'; font-weight: 900; color: var(--dark-surface); letter-spacing: -1px; text-transform: uppercase; }
    
    /* Stats Cards */
    .stat-card { background: #fff; border-radius: 20px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); transition: 0.3s; height: 100%; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
    .stat-label { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
    .stat-value { font-family: 'Orbitron'; font-size: 22px; font-weight: 800; color: var(--dark-surface); }

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

    .yield-badge { padding: 4px 10px; border-radius: 8px; font-family: 'JetBrains Mono'; font-weight: 700; font-size: 12px; }
    
    /* Chart Container */
    .chart-box { background: #fff; border-radius: 24px; padding: 25px; border: 1px solid #e2e8f0; margin-bottom: 30px; }

    /* Modal Detail Styling */
    .modal-content { border-radius: 30px; border: none; overflow: hidden; }
    .detail-label { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 2px; }
    .detail-value { font-weight: 700; color: var(--dark-surface); font-size: 15px; }
    .font-mono { font-family: 'JetBrains Mono', monospace; }
</style>

<div class="container-fluid py-4 px-4">
    {{-- 1. HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="heading-history mb-1">WELDING_HISTORY <span class="text-primary">AUDIT</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0">PT. ASALTA MANDIRI AGUNG // UNIT_VERIFICATION_LOGS</p>
        </div>
        <div>
            <button class="btn btn-dark rounded-pill px-4 font-weight-extrabold shadow-sm mr-2" onclick="window.print()">
                <i class="fas fa-file-pdf mr-2"></i> EXPORT_PDF
            </button>
            <a href="{{ route('welding.index') }}" class="btn btn-outline-primary rounded-pill px-4 font-weight-extrabold shadow-sm">
                <i class="fas fa-desktop mr-2"></i> MONITORING
            </a>
        </div>
    </div>

    {{-- 2. STATS CARDS --}}
    @php
        $totalBatch = $historyData->count();
        $totalOk = $historyData->sum('qty_ok');
        $totalNg = $historyData->sum('qty_ng');
        $grandTotal = $totalOk + $totalNg;
        $avgYield = $grandTotal > 0 ? ($totalOk / $grandTotal) * 100 : 0;
    @endphp
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Total Inspections</div>
                <div class="stat-value text-primary">{{ $totalBatch }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-left-success" style="border-left: 5px solid var(--brand-success) !important;">
                <div class="stat-label">Average Yield</div>
                <div class="stat-value text-success">{{ number_format($avgYield, 1) }}%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Total OK PCS</div>
                <div class="stat-value">{{ number_format($totalOk) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-left-danger" style="border-left: 5px solid var(--brand-danger) !important;">
                <div class="stat-label">Total NG PCS</div>
                <div class="stat-value text-danger">{{ number_format($totalNg) }}</div>
            </div>
        </div>
    </div>

    {{-- 3. CHART SECTION --}}
    <div class="chart-box shadow-sm">
        <h6 class="font-weight-bold mb-4 text-uppercase small tracking-widest text-muted">Quality Performance Trend (Yield %)</h6>
        <div id="yieldChart"></div>
    </div>

    {{-- 4. DATA TABLE --}}
    <div class="ledger-container shadow-sm">
        <div class="table-responsive">
            <table class="table table-history mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Time_Stamp</th>
                        <th>Batch_Identifier</th>
                        <th class="text-left">Part_No</th>
                        <th class="text-success">OK</th>
                        <th class="text-danger">NG</th>
                        <th>Yield</th>
                        <th>Inspector</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($historyData as $h)
                    @php
                        $batchTotal = (float)$h->qty_ok + (float)$h->qty_ng;
                        $yield = $batchTotal > 0 ? ($h->qty_ok / $batchTotal) * 100 : 0;
                    @endphp
                    <tr onclick="showDetail({{ json_encode($h) }})">
                        <td class="text-left pl-4 text-muted small">
                            {{ \Carbon\Carbon::parse($h->qc_at)->format('d/m/Y H:i') }}
                        </td>
                        <td class="font-weight-bold text-primary font-mono">
                            {{ $h->no_produksi_stamping }}
                        </td>
                        <td class="text-dark font-weight-bold text-left text-uppercase">
                            {{ $h->part_no }}
                        </td>
                        <td class="text-success font-weight-bolder">{{ number_format($h->qty_ok) }}</td>
                        <td class="text-danger font-weight-bolder">{{ number_format($h->qty_ng) }}</td>
                        <td>
                            <span class="yield-badge {{ $yield < 100 ? 'bg-light text-warning border' : 'bg-light text-success border' }}">
                                {{ number_format($yield, 1) }}%
                            </span>
                        </td>
                        <td class="text-uppercase small font-weight-bold text-muted">
                            <i class="fas fa-user-check mr-1"></i> {{ $h->qc_by ?: 'STATION_ADMIN' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-5 text-muted italic">No historical data available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 5. MODAL DETAIL --}}
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-dark text-white px-4 py-3 border-0">
                <h6 class="modal-title font-weight-bold text-uppercase tracking-wider">Production Batch Details</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="detail-label">Status</div>
                    <span class="badge badge-success px-4 py-2 rounded-pill font-weight-bold">COMPLETED & VERIFIED</span>
                </div>
                <div class="row">
                    <div class="col-6 mb-3 border-right">
                        <div class="detail-label">Batch No</div>
                        <div class="detail-value text-primary font-mono" id="det-batch"></div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="detail-label">Verification Date</div>
                        <div class="detail-value" id="det-date"></div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="detail-label">Part Identification</div>
                        <div class="detail-value h5 font-weight-bold" id="det-part"></div>
                    </div>
                    <div class="col-4 mb-3 border-right">
                        <div class="detail-label">Qty OK</div>
                        <div class="detail-value text-success h4" id="det-ok"></div>
                    </div>
                    <div class="col-4 mb-3 border-right">
                        <div class="detail-label">Qty NG</div>
                        <div class="detail-value text-danger h4" id="det-ng"></div>
                    </div>
                    <div class="col-4 mb-3">
                        <div class="detail-label">Inspector</div>
                        <div class="detail-value" id="det-by"></div>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="detail-label">Defect Log / Reason</div>
                        <div class="p-3 bg-light rounded-xl border text-muted small" style="min-height: 80px;" id="det-remark"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Logic Grafik (Trend 10 data terakhir)
    const rawData = @json($historyData->take(10)->reverse()->values());
    
    const yieldSeries = rawData.map(item => {
        const total = parseFloat(item.qty_ok) + parseFloat(item.qty_ng);
        return total > 0 ? ((item.qty_ok / total) * 100).toFixed(1) : 0;
    });

    const categoryLabels = rawData.map(item => {
        // Ambil 6 digit terakhir dari nomor batch untuk label axis
        return item.no_produksi_stamping.substr(-6);
    });

    const options = {
        series: [{ name: 'Production Yield', data: yieldSeries }],
        chart: { height: 300, type: 'area', toolbar: { show: false } },
        colors: ['#4361ee'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 90, 100] } },
        xaxis: { categories: categoryLabels, labels: { style: { fontSize: '10px', fontWeight: 600 } } },
        yaxis: { max: 100, min: 0, labels: { formatter: (val) => val + "%" } },
        tooltip: { y: { formatter: (val) => val + "% Success Rate" } }
    };

    const chart = new ApexCharts(document.querySelector("#yieldChart"), options);
    chart.render();

    // 2. Fungsi Show Detail Modal
    function showDetail(data) {
        document.getElementById('det-batch').innerText = data.no_produksi_stamping;
        document.getElementById('det-date').innerText = data.qc_at;
        document.getElementById('det-part').innerText = data.part_no;
        document.getElementById('det-ok').innerText = data.qty_ok + " PCS";
        document.getElementById('det-ng').innerText = data.qty_ng + " PCS";
        document.getElementById('det-by').innerText = data.qc_by || 'QC_OFFICER';
        document.getElementById('det-remark').innerText = data.keterangan || 'No remarks provided for this batch.';
        
        $('#detailModal').modal('show');
    }
</script>
@endsection