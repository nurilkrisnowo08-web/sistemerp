@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --ind-navy: #0f172a; --ind-blue: #4361ee; --ind-amber: #f59e0b;
        --ind-emerald: #10b981; --ind-rose: #ef4444; --bg-main: #f1f5f9;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #1e293b; }
    .industrial-header { font-family: 'Orbitron', sans-serif; letter-spacing: 1px; }
    
    /* Stats Cards */
    .stat-card { background: #fff; border-radius: 24px; padding: 20px; border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 10px 30px rgba(0,0,0,0.02); transition: 0.3s; }
    .stat-label { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
    .stat-value { font-family: 'Orbitron'; font-size: 26px; font-weight: 800; }

    /* Table Modern */
    .ledger-container { background: #fff; border-radius: 30px; border: 1px solid #eef2f6; overflow: hidden; box-shadow: 0 15px 50px rgba(0,0,0,0.03); }
    .table-history thead th { 
        background: #f8fafc; color: #64748b; font-size: 10px; 
        text-transform: uppercase; letter-spacing: 1.5px; padding: 22px 15px; border: none;
    }
    .table-history tbody tr { cursor: pointer; transition: 0.3s; }
    .table-history tbody tr:hover { background-color: rgba(67, 97, 238, 0.04); }
    .table-history td { padding: 18px 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 13px; }

    .origin-badge { font-size: 9px; padding: 5px 12px; border-radius: 10px; font-weight: 800; letter-spacing: 0.5px; }
    .yield-badge { padding: 6px 12px; border-radius: 12px; font-family: 'JetBrains Mono'; font-weight: 700; font-size: 12px; }
    
    .ng-pills-container { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px; }
    .ng-pill { background: #fee2e2; color: var(--ind-rose); font-size: 9px; padding: 2px 8px; border-radius: 6px; border: 1px solid #fecdd3; font-family: 'JetBrains Mono'; font-weight: 700; }

    /* Modal Styling */
    .modal-content { border-radius: 40px; border: none; overflow: hidden; }
    .ng-detail-box { background: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 20px; padding: 20px; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    {{-- 1. HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-5 bg-white p-4 rounded-3xl border shadow-sm">
        <div>
            <h2 class="industrial-header m-0 text-primary uppercase">Quality_Audit <span class="text-dark">History</span></h2>
            <p class="text-muted small font-weight-bold mb-0 uppercase">Verified Verification Records</p>
        </div>
        <a href="{{ route('quality.index') }}" class="btn btn-dark rounded-pill px-4 font-weight-black shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> QC TERMINAL
        </a>
    </div>

    {{-- 2. STATS --}}
    @php
        $totalOk = $historyData->sum('qty_ok');
        $totalNg = $historyData->sum('qty_ng');
        $grandTotal = $totalOk + $totalNg;
        $avgYield = $grandTotal > 0 ? ($totalOk / $grandTotal) * 100 : 0;
    @endphp
    <div class="row mb-5">
        <div class="col-md-3"><div class="stat-card animate__animated animate__fadeInUp"><div class="stat-label">Verified OK</div><div class="stat-value text-emerald">{{ number_format($totalOk) }}</div></div></div>
        <div class="col-md-3"><div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s;"><div class="stat-label">Verified NG</div><div class="stat-value text-rose">{{ number_format($totalNg) }}</div></div></div>
        <div class="col-md-3"><div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s;"><div class="stat-label">Global Yield</div><div class="stat-value text-primary">{{ number_format($avgYield, 1) }}%</div></div></div>
        <div class="col-md-3"><div class="stat-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s;"><div class="stat-label">Record Count</div><div class="stat-value">{{ $historyData->count() }}</div></div></div>
    </div>

    {{-- 3. TABLE --}}
    <div class="ledger-container shadow-sm animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-history mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Inspection Timestamp</th>
                        <th>Origin</th>
                        <th>Batch Number</th>
                        <th class="text-left">Part Identification</th>
                        <th class="text-success">OK</th>
                        <th class="text-danger">NG</th>
                        <th>Yield</th>
                        <th>Inspector</th>
                        <th class="text-left">Verified Defect Summary</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($historyData as $h)
                    @php
                        $total = (float)$h->qty_ok + (float)$h->qty_ng;
                        $yield = $total > 0 ? ($h->qty_ok / $total) * 100 : 0;
                    @endphp
                    <tr onclick="showDetail({{ json_encode($h) }})">
                        <td class="text-left pl-5 text-muted small font-weight-bold">{{ date('d/m/Y H:i', strtotime($h->created_at)) }}</td>
                        <td>
                            <span class="origin-badge {{ $h->origin == 'WELDING' ? 'bg-warning text-dark' : 'bg-primary text-white' }}">
                                {{ $h->origin }}
                            </span>
                        </td>
                        <td class="text-primary font-weight-black" style="font-family: 'JetBrains Mono';">{{ $h->batch_no }}</td>
                        <td class="text-dark font-weight-black text-left">{{ $h->part_no }}</td>
                        <td class="text-success font-weight-black">{{ number_format($h->qty_ok) }}</td>
                        <td class="text-danger font-weight-black">{{ number_format($h->qty_ng) }}</td>
                        <td>
                            <span class="yield-badge {{ $yield < 95 ? 'text-danger border-danger' : 'text-success border-success' }} border">
                                {{ number_format($yield, 1) }}%
                            </span>
                        </td>
                        <td class="text-uppercase small font-weight-black">{{ $h->inspector }}</td>
                        <td class="text-left">
                            @if($h->ng_reason && $h->ng_reason != 'OK GOODS')
                                <div class="ng-pills-container">
                                    @foreach(explode(', ', $h->ng_reason) as $pill)
                                        <span class="ng-pill">{{ $pill }}</span>
                                    @endforeach
                                </div>
                            @else
                                <small class="text-muted italic font-weight-bold">ZERO_DEFECTS</small>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="py-5 text-muted">No audit records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 4. DETAIL MODAL --}}
<div class="modal fade animate__animated animate__zoomIn" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-2xl">
            <div class="modal-header bg-dark text-white px-4 py-4 border-0">
                <h6 class="modal-title font-weight-black uppercase tracking-widest"><i class="fas fa-shield-alt mr-2 text-primary"></i>Verification_Report</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-5">
                <div class="text-center mb-5">
                    <small class="stat-label">Batch Identifier</small>
                    <div class="h3 font-weight-black text-primary mt-1" id="det-batch" style="font-family: 'Orbitron';"></div>
                </div>
                
                <div class="row mb-5 text-center">
                    <div class="col-6 mb-4"><small class="stat-label">Part Identification</small><div class="font-weight-black h5" id="det-part"></div></div>
                    <div class="col-6 mb-4"><small class="stat-label">Process Origin</small><div class="font-weight-black h5" id="det-origin"></div></div>
                    <div class="col-4 border-right"><small class="stat-label">Verified OK</small><div class="text-success font-weight-black h4" id="det-ok"></div></div>
                    <div class="col-4 border-right"><small class="stat-label">Total NG</small><div class="text-danger font-weight-black h4" id="det-ng"></div></div>
                    <div class="col-4"><small class="stat-label">Efficiency</small><div class="text-primary font-weight-black h4" id="det-yield"></div></div>
                </div>

                <div class="mb-4">
                    <label class="stat-label mb-3 d-block"><i class="fas fa-list-ul mr-2"></i>Defect Breakdown (Categorized)</label>
                    <div id="det-ng-list" class="ng-detail-box">
                        <!-- JS Dynamic Content -->
                    </div>
                </div>

                <div class="bg-light p-4 rounded-3xl border">
                    <small class="stat-label d-block mb-2">Lead Inspector Remarks</small>
                    <div class="font-weight-bold text-dark" id="det-inspector" style="letter-spacing: 1px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showDetail(data) {
        const total = parseFloat(data.qty_ok) + parseFloat(data.qty_ng);
        const yieldVal = total > 0 ? ((data.qty_ok / total) * 100).toFixed(1) : 0;

        document.getElementById('det-batch').innerText = data.batch_no;
        document.getElementById('det-part').innerText = data.part_no;
        document.getElementById('det-origin').innerText = data.origin;
        document.getElementById('det-ok').innerText = data.qty_ok;
        document.getElementById('det-ng').innerText = data.qty_ng;
        document.getElementById('det-yield').innerText = yieldVal + "%";
        document.getElementById('det-inspector').innerText = data.inspector.toUpperCase();

        const ngListDiv = document.getElementById('det-ng-list');
        ngListDiv.innerHTML = ''; 

        // ✨ LOGIKA PARSING SAKTI: Menangani "Defect (Qty)" atau "Defect" saja rill!
        if (data.ng_reason && data.ng_reason !== 'OK GOODS') {
            const defects = data.ng_reason.split(', ');
            defects.forEach(defect => {
                // Regex untuk menangkap nama dan qty di dalam kurung
                const match = defect.match(/(.+?)\s*\((\d+)\)/);
                
                if (match) {
                    const name = match[1].trim();
                    const qty = match[2];
                    ngListDiv.innerHTML += `
                        <div class="d-flex justify-content-between align-items-center mb-2 bg-white p-3 rounded-xl shadow-sm border">
                            <span class="font-weight-black text-danger uppercase" style="font-size:11px;">• ${name}</span>
                            <span class="badge badge-danger rounded-pill px-3 font-weight-black">${qty} PCS</span>
                        </div>
                    `;
                } else {
                    // Fallback jika formatnya cuma nama tanpa qty
                    ngListDiv.innerHTML += `
                        <div class="d-flex justify-content-between align-items-center mb-2 bg-white p-3 rounded-xl shadow-sm border">
                            <span class="font-weight-black text-danger uppercase" style="font-size:11px;">• ${defect}</span>
                        </div>
                    `;
                }
            });
        } else {
            ngListDiv.innerHTML = '<div class="text-center py-2 text-muted font-weight-bold italic">ZERO_DEFECTS_DETECTED</div>';
        }
        
        $('#detailModal').modal('show');
    }
</script>
@endsection