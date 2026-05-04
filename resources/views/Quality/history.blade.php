@extends('layout.admin')

@section('content')
<!-- Core Assets -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --ind-navy: #0f172a; --ind-blue: #4361ee; --ind-amber: #f59e0b;
        --ind-emerald: #10b981; --ind-rose: #ef4444; --bg-main: #f1f5f9;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #1e293b; }
    .industrial-header { font-family: 'Orbitron', sans-serif; letter-spacing: 1px; }
    
    .progress-custom { height: 8px; border-radius: 10px; background: #e2e8f0; overflow: hidden; margin-top: 5px; }
    .progress-bar-fill { height: 100%; transition: 0.6s cubic-bezier(0.4, 0, 0.2, 1); }

    .batch-group-card { background: white; border-radius: 30px; margin-bottom: 2rem; border: 1px solid #eef2f6; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
    .batch-header { background: #f8fafc; padding: 20px 30px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
    
    .table-history td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f8fafc; font-weight: 700; font-size: 13px; }
    /* Warna Badge Phase Dinamis */
    .phase-badge { background: var(--ind-navy); color: white; padding: 4px 12px; border-radius: 8px; font-family: 'JetBrains Mono'; font-size: 10px; font-weight: 800; }
    
    .ng-pill { background: #fee2e2; color: var(--ind-rose); font-size: 9px; padding: 2px 8px; border-radius: 6px; border: 1px solid #fecdd3; font-family: 'JetBrains Mono'; font-weight: 700; margin: 2px; display: inline-block; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-5 bg-white p-4 rounded-3xl border shadow-sm">
        <div>
            <h2 class="industrial-header m-0 text-primary uppercase">Quality_Audit <span class="text-dark">Timeline</span></h2>
            <p class="text-muted small font-weight-bold mb-0 uppercase tracking-widest">Partial Inspection Tracking System</p>
        </div>
        <a href="{{ route('quality.index') }}" class="btn btn-dark rounded-pill px-4 font-weight-black shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> QC TERMINAL
        </a>
    </div>

    @php
        // Mengurutkan data berdasarkan Batch
        $groupedHistory = $historyData->groupBy('batch_no');
    @endphp

    @forelse($groupedHistory as $batchNo => $records)
        @php
            // Urutkan records secara kronologis (lama ke baru) untuk menghitung progress total
            $sortedRecords = $records->sortBy('created_at');
            
            $batchPart = $sortedRecords->first()->part_no;
            $batchOrigin = $sortedRecords->first()->origin;
            $batchTarget = $sortedRecords->first()->qty_from_prod;
            $batchChecked = $sortedRecords->sum('total_checked');
            $percent = ($batchTarget > 0) ? ($batchChecked / $batchTarget) * 100 : 0;
        @endphp

        <div class="batch-group-card shadow-sm animate__animated animate__fadeInUp">
            <div class="batch-header">
                <div class="d-flex align-items-center">
                    <div class="mr-4 text-center">
                        <small class="font-weight-bold text-muted uppercase" style="font-size: 9px;">Origin</small>
                        <div class="badge {{ $batchOrigin == 'WELDING' ? 'bg-warning text-dark' : 'bg-primary text-white' }} d-block px-3 rounded-pill">{{ $batchOrigin }}</div>
                    </div>
                    <div>
                        <h4 class="m-0 font-weight-black text-dark" style="font-family: 'JetBrains Mono';">{{ $batchNo }}</h4>
                        <small class="text-muted font-weight-bold uppercase">{{ $batchPart }}</small>
                    </div>
                </div>

                <div style="width: 250px">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="font-weight-black uppercase" style="font-size: 9px;">Progress</small>
                        <small class="font-weight-black text-primary" style="font-size: 10px;">{{ number_format($batchChecked) }} / {{ number_format($batchTarget) }} PCS</small>
                    </div>
                    <div class="progress-custom">
                        <div class="progress-bar-fill {{ $percent >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-history mb-0 text-center">
                    <thead>
                        <tr class="bg-light">
                            <th style="font-size: 10px; text-transform: uppercase;">Phase</th>
                            <th style="font-size: 10px; text-transform: uppercase;">Inspector</th>
                            <th style="font-size: 10px; text-transform: uppercase;" class="text-success">OK</th>
                            <th style="font-size: 10px; text-transform: uppercase;" class="text-danger">NG</th>
                            <th style="font-size: 10px; text-transform: uppercase;" class="text-primary">Return</th>
                            <th style="font-size: 10px; text-transform: uppercase;">Timestamp</th>
                            <th style="font-size: 10px; text-transform: uppercase;" class="text-left">Defects Summary</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Membalik urutan display: TERBARU di ATAS, tapi nomor urut tetap akurat --}}
                        @php $totalRecords = $records->count(); @endphp
                        @foreach($records->sortByDesc('created_at') as $index => $h)
                        <tr onclick="showDetail({{ json_encode($h) }})">
                            <td>
                                {{-- Logika: Jika ada 3 data, baris paling atas (terbaru) jadi CHECK #3 --}}
                                <span class="phase-badge">CHECK #{{ $totalRecords - $loop->index }}</span>
                            </td>
                            <td class="font-weight-black uppercase text-dark">{{ $h->inspector }}</td>
                            <td class="text-success font-weight-black">+{{ number_format($h->qty_ok) }}</td>
                            <td class="text-danger font-weight-black">+{{ number_format($h->qty_ng) }}</td>
                            <td class="text-primary font-weight-black">+{{ number_format($h->qty_ret ?? 0) }}</td>
                            <td class="text-muted small font-weight-bold">{{ date('d/m/Y H:i', strtotime($h->created_at)) }}</td>
                            <td class="text-left">
                                @if($h->ng_reason && $h->ng_reason != 'OK GOODS')
                                    @foreach(explode(', ', $h->ng_reason) as $pill)
                                        <span class="ng-pill">{{ $pill }}</span>
                                    @endforeach
                                @else
                                    <small class="text-muted italic font-weight-bold">ZERO_DEFECTS</small>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="text-center py-5 bg-white rounded-3xl border shadow-sm">
            <h5 class="text-muted">No inspection records found.</h5>
        </div>
    @endforelse
</div>

{{-- MODAL DETAIL (DI-UPDATE UNTUK RETURN) --}}
<div class="modal fade animate__animated animate__zoomIn" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg" style="border-radius: 35px; border: none; overflow: hidden;">
            <div class="modal-header bg-dark text-white px-4 py-4 border-0">
                <h6 class="modal-title font-weight-black uppercase tracking-widest"><i class="fas fa-shield-alt mr-2 text-primary"></i>Verification_Report</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-5">
                <div class="text-center mb-5">
                    <small class="text-muted font-weight-bold uppercase" style="font-size: 10px;">Batch Identifier</small>
                    <div class="h3 font-weight-black text-primary mt-1" id="det-batch" style="font-family: 'Orbitron';"></div>
                </div>
                
                <div class="row mb-5 text-center">
                    <div class="col-6 mb-4"><small class="text-muted font-weight-bold uppercase" style="font-size: 9px;">Part ID</small><div class="font-weight-black h6" id="det-part"></div></div>
                    <div class="col-6 mb-4"><small class="text-muted font-weight-bold uppercase" style="font-size: 9px;">Origin</small><div class="font-weight-black h6" id="det-origin"></div></div>
                    <div class="col-4 border-right"><small class="text-success font-weight-bold uppercase" style="font-size: 9px;">OK</small><div class="text-success font-weight-black h4" id="det-ok"></div></div>
                    <div class="col-4 border-right"><small class="text-danger font-weight-bold uppercase" style="font-size: 9px;">NG</small><div class="text-danger font-weight-black h4" id="det-ng"></div></div>
                    <div class="col-4"><small class="text-primary font-weight-bold uppercase" style="font-size: 9px;">Return</small><div class="text-primary font-weight-black h4" id="det-ret"></div></div>
                </div>

                <div class="mb-4 text-center">
                    <small class="text-muted font-weight-bold uppercase" style="font-size: 9px;">Batch Efficiency (Yield)</small>
                    <div class="text-dark font-weight-black h4" id="det-yield"></div>
                </div>

                <div class="mb-4">
                    <label class="font-weight-black text-muted small uppercase mb-3 d-block"><i class="fas fa-list-ul mr-2"></i>Defect Breakdown</label>
                    <div id="det-ng-list" style="background: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 20px; padding: 20px;"></div>
                </div>

                <div class="bg-light p-4 rounded-3xl border" style="border-radius: 20px;">
                    <small class="text-muted font-weight-bold uppercase d-block mb-1" style="font-size: 9px;">Assigned Inspector</small>
                    <div class="font-weight-bold text-dark text-uppercase" id="det-inspector" style="letter-spacing: 1px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showDetail(data) {
        // Yield dihitung dari OK / (OK+NG) rill
        const totalChecked = parseFloat(data.qty_ok) + parseFloat(data.qty_ng);
        const yieldVal = totalChecked > 0 ? ((data.qty_ok / totalChecked) * 100).toFixed(1) : 0;

        document.getElementById('det-batch').innerText = data.batch_no;
        document.getElementById('det-part').innerText = data.part_no;
        document.getElementById('det-origin').innerText = data.origin;
        document.getElementById('det-ok').innerText = data.qty_ok;
        document.getElementById('det-ng').innerText = data.qty_ng;
        document.getElementById('det-ret').innerText = data.qty_ret ?? 0;
        document.getElementById('det-yield').innerText = yieldVal + "%";
        document.getElementById('det-inspector').innerText = data.inspector;

        const ngListDiv = document.getElementById('det-ng-list');
        ngListDiv.innerHTML = ''; 

        if (data.ng_reason && data.ng_reason !== 'OK GOODS') {
            const defects = data.ng_reason.split(', ');
            defects.forEach(defect => {
                const match = defect.match(/(.+?)\s*\((\d+)\)/);
                if (match) {
                    ngListDiv.innerHTML += `
                        <div class="d-flex justify-content-between align-items-center mb-2 bg-white p-3 rounded-xl shadow-sm border">
                            <span class="font-weight-black text-danger uppercase" style="font-size:10px;">• ${match[1].trim()}</span>
                            <span class="badge badge-danger rounded-pill px-3 font-weight-black">${match[2]} PCS</span>
                        </div>`;
                } else {
                    ngListDiv.innerHTML += `
                        <div class="d-flex justify-content-between align-items-center mb-2 bg-white p-3 rounded-xl shadow-sm border">
                            <span class="font-weight-black text-danger uppercase" style="font-size:10px;">• ${defect}</span>
                        </div>`;
                }
            });
        } else {
            ngListDiv.innerHTML = '<div class="text-center py-2 text-muted font-weight-bold italic">ZERO_DEFECTS_DETECTED</div>';
        }
        
        $('#detailModal').modal('show');
    }
</script>
@endsection