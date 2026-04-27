@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
    .card-custom { border-radius: 15px; border: none; transition: 0.3s; }
    .font-black { font-weight: 800; letter-spacing: -0.5px; }
    .bg-gradient-dark { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }
    
    /* Hover Effect Table */
    .row-clickable { cursor: pointer; transition: 0.2s; }
    .row-clickable:hover { background-color: #f0f7ff !important; box-shadow: inset 4px 0 0 #2563eb; }
    
    .batch-id-pill { font-family: 'JetBrains Mono'; font-size: 10px; background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 4px; font-weight: 700; }
    .ng-detail-row { border-bottom: 1px dashed #cbd5e1; padding: 8px 0; }
</style>

<div class="container-fluid mt-4 mb-5">
    {{-- Header & Stats (Sama seperti sebelumnya) --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-black text-dark mb-0">QUALITY_CONTROL_HUB</h2>
            <p class="text-muted small font-weight-bold uppercase mb-0">PT ASALTA MANDIRI AGUNG // BATCH_ANALYSIS_MODE</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form action="" method="GET" class="mr-2">
                <input type="date" name="date" class="form-control rounded-pill border-dark px-4 shadow-sm" value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <a href="{{ route('ppic.mps.index') }}" class="btn btn-outline-dark rounded-pill px-4 font-weight-bold shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> BACK TO MPS
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-custom shadow-sm bg-white p-4 border-left border-success" style="border-left-width: 8px !important;">
                <h6 class="text-muted small font-weight-bold uppercase mb-1">Passed Good (OK)</h6>
                <h2 class="font-black mb-0 text-success">{{ number_format($summary->total_ok ?? 0) }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom shadow-sm bg-white p-4 border-left border-danger" style="border-left-width: 8px !important;">
                <h6 class="text-muted small font-weight-bold uppercase mb-1">Total Rejected (NG)</h6>
                <h2 class="font-black mb-0 text-danger">{{ number_format($summary->total_ng ?? 0) }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom shadow-sm bg-gradient-dark text-white p-4">
                <h6 class="small font-weight-bold uppercase mb-1 text-info">Overall Yield Rate</h6>
                @php 
                    $total = ($summary->total_ok ?? 0) + ($summary->total_ng ?? 0);
                    $yield = $total > 0 ? round(($summary->total_ok / $total) * 100, 1) : 0;
                @endphp
                <h2 class="font-black mb-0 text-warning">{{ $yield }}%</h2>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Tabel Utama --}}
        <div class="col-12">
            <div class="card card-custom shadow-sm p-0 bg-white overflow-hidden">
                <div class="p-4 bg-light d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="font-black mb-0"><i class="fas fa-stream mr-2 text-primary"></i> ACTUAL_PRODUCTION_LOG</h5>
                    <span class="badge badge-dark rounded-pill px-3">{{ count($details) }} Entries</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 text-center">
                        <thead>
                            <tr class="text-uppercase small font-weight-bold text-muted">
                                <th class="text-left pl-4">Part Identification</th>
                                <th>Line</th>
                                <th>Shift</th>
                                <th>Passed Good</th>
                                <th>NG Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $d)
                            <tr class="row-clickable" onclick="showBatchList({{ json_encode($d->part_no) }}, {{ json_encode($d->batches) }})">
                                <td class="text-left pl-4">
                                    <span class="font-weight-bold text-primary">{{ $d->part_no }}</span>
                                    <br><small class="text-muted">Click to drill down batches</small>
                                </td>
                                <td><span class="badge badge-outline-dark font-weight-bold">{{ $d->line_code }}</span></td>
                                <td><span class="badge {{ $d->shift == 'Pagi' ? 'badge-warning' : 'badge-dark' }} px-3">{{ strtoupper($d->shift) }}</span></td>
                                <td class="text-success font-weight-bold">{{ number_format($d->qty_ok) }}</td>
                                <td class="text-danger font-weight-bold">{{ number_format($d->qty_ng) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBatchList" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-primary text-white py-4" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title font-black" id="modalPartName">BATCH_DRILLDOWN</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <table class="table mb-0">
                    <thead class="bg-light small font-weight-bold">
                        <tr class="text-center">
                            <th class="text-left pl-4">No Produksi</th>
                            <th>Line</th>
                            <th>Ambil</th>
                            <th>OK</th>
                            <th>NG</th>
                        </tr>
                    </thead>
                    <tbody id="batchTableBody">
                        {{-- JS Content --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSpecificNG" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 25px;">
            <div class="modal-header bg-dark text-white py-4" style="border-radius: 25px 25px 0 0;">
                <h6 class="modal-title font-black" id="ngBatchTitle">NG_ANALYSIS</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="small text-muted uppercase font-weight-bold">Final OK Release:</div>
                    <h1 class="font-black text-success" id="ngOkVal">0</h1>
                    <div class="badge badge-danger px-4 py-2" id="ngTotalBadge">TOTAL REJECT: 0 PCS</div>
                </div>
                
                <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">DEFECT_BREAKDOWN</h6>
                <div id="ngListContainer">
                    {{-- JS Content --}}
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-dark btn-block font-weight-bold py-3" style="border-radius:15px;" data-dismiss="modal">ACKNOWLEDGE</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Tampilkan Modal 1 (Daftar Batch)
    function showBatchList(partNo, batches) {
        document.getElementById('modalPartName').innerText = "BATCH_DRILLDOWN: " + partNo;
        const tbody = document.getElementById('batchTableBody');
        tbody.innerHTML = '';

        batches.forEach(b => {
            // Stringify data NG agar bisa dikirim ke fungsi berikutnya
            const ngData = JSON.stringify(b.ng_list);
            tbody.innerHTML += `
                <tr class="row-clickable text-center font-weight-bold" onclick='showNGDetail("${b.no_produksi}", ${b.qty_hasil_ok}, ${b.qty_hasil_ng}, ${ngData})'>
                    <td class="text-left pl-4">
                        <span class="batch-id-pill">${b.no_produksi}</span>
                        <br><small class="text-muted">Click for NG details</small>
                    </td>
                    <td>${b.kode_Line || '-'}</td>
                    <td class="text-dark">${parseInt(b.qty_ambil_pcs).toLocaleString()}</td>
                    <td class="text-success">${parseInt(b.qty_hasil_ok).toLocaleString()}</td>
                    <td class="text-danger">${parseInt(b.qty_hasil_ng).toLocaleString()}</td>
                </tr>
            `;
        });
        $('#modalBatchList').modal('show');
    }

    // Tampilkan Modal 2 (Rincian NG)
    function showNGDetail(noProduksi, ok, totalNg, ngList) {
        // Sembunyikan modal pertama agar tidak tumpang tindih (opsional)
        // $('#modalBatchList').modal('hide'); 

        document.getElementById('ngBatchTitle').innerText = "NG_ANALYSIS: " + noProduksi;
        document.getElementById('ngOkVal').innerText = ok.toLocaleString() + " PCS";
        document.getElementById('ngTotalBadge').innerText = "TOTAL REJECT: " + totalNg + " PCS";

        const container = document.getElementById('ngListContainer');
        container.innerHTML = '';

        if (ngList.length === 0) {
            container.innerHTML = '<div class="text-center py-4 text-muted small font-weight-bold italic">-- NO SPECIFIC DEFECTS REPORTED --</div>';
        } else {
            ngList.forEach(ng => {
                container.innerHTML += `
                    <div class="d-flex justify-content-between ng-detail-row">
                        <span class="font-weight-bold text-danger"><i class="fas fa-times-circle mr-2"></i>${ng.ng_type.toUpperCase()}</span>
                        <span class="font-weight-bold">${ng.qty} PCS</span>
                    </div>
                `;
            });
        }
        $('#modalSpecificNG').modal('show');
    }
</script>

@endsection