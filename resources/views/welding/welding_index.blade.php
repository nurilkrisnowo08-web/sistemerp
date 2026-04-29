@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-return: #6366f1; --dark-surface: #0f172a; --bg-main: #f1f5f9;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #1e293b; }
    
    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(135deg, var(--brand-primary), #7209b7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    /* 📈 LEDGER TABLE - INDUSTRIAL GLASS */
    .ledger-container { background: #fff; border-radius: 30px; border: 1px solid rgba(255,255,255,0.7); overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.04); margin-bottom: 35px; }
    .table-ledger thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 22px; border: none; }
    .table-ledger td { padding: 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; }
    
    /* 🏷️ PT NAVIGATION */
    .nav-section { background: #fff; padding: 8px; border-radius: 22px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 30px; border: 1px solid #e2e8f0; display: inline-flex; }
    .nav-pills .nav-link { 
        border-radius: 16px; padding: 12px 28px; font-weight: 800; font-size: 0.75rem;
        transition: 0.3s; color: #64748b !important;
    }
    .nav-pills .nav-link.active { background: var(--dark-surface) !important; color: #fff !important; box-shadow: 0 10px 20px rgba(15, 23, 42, 0.2); }

    /* 🛠️ WORK CARDS */
    .work-card { 
        background: #fff; border-radius: 28px; border: 1px solid #eef2f6; 
        padding: 28px; margin-bottom: 20px; transition: 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); 
        display: flex; align-items: center; position: relative;
    }
    .work-card:hover { transform: scale(1.01); box-shadow: 0 20px 50px rgba(67, 97, 238, 0.1); border-color: var(--brand-primary); }
    
    .station-tag { background: #f1f5f9; color: var(--dark-surface); font-family: 'JetBrains Mono'; font-size: 10px; padding: 5px 12px; border-radius: 10px; font-weight: 800; margin-bottom: 10px; display: inline-block; }
    .qty-display { font-family: 'Orbitron'; font-weight: 900; font-size: 38px; color: var(--dark-surface); line-height: 1; }

    .tech-input-lg { border-radius: 20px; border: 2.5px solid #eef2f6; font-weight: 800; transition: 0.3s; height: 75px; background: #f8fafc; text-align: center; font-family: 'Orbitron'; }
    .tech-input-lg:focus { border-color: var(--brand-primary); outline: none; background: #fff; box-shadow: 0 0 0 6px rgba(67, 97, 238, 0.1); }

    .security-status { border-radius: 18px; padding: 15px; font-weight: 800; font-size: 12px; text-align: center; transition: 0.3s; margin-top: 20px; }
    .status-match { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .status-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    /* Return Highlight Section */
    .return-box { background: rgba(99, 102, 241, 0.05); border: 2px dashed var(--brand-return); border-radius: 24px; padding: 20px; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    {{-- 🛰️ HEADER HUB --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding Terminal <span style="-webkit-text-fill-color: var(--dark-surface);">v3.2</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0"><i class="fas fa-microchip text-primary mr-2"></i> Quality & Logistics Sync / PT Asalta Mandiri Agung</p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
            <a href="{{ route('welding.history') }}" class="btn btn-white rounded-pill px-4 font-weight-extrabold border mr-2 shadow-sm">VAULT</a>
            <button class="btn btn-primary rounded-pill px-4 font-weight-extrabold shadow-lg" data-toggle="modal" data-target="#modalDeployWelding"><i class="fas fa-plus-circle mr-1"></i> DEPLOY BATCH</button>
        </div>
    </div>

    {{-- 📊 WIP LEDGER --}}
    <div class="ledger-container">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Part Identification</th>
                        <th>Opening</th>
                        <th class="text-success">In (Stamping)</th>
                        <th class="text-return" style="color: var(--brand-return);">In (Return)</th>
                        <th class="text-danger">Out (Welding)</th>
                        <th class="bg-light text-primary">Live Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventoryWelding as $inv)
                    <tr>
                        <td class="text-left pl-5">
                            <div class="font-weight-black text-dark" style="font-size: 15px;">{{ $inv->part_no }}</div>
                            <small class="text-muted font-weight-bold uppercase" style="font-size: 9px;">{{ $inv->part_name }}</small>
                        </td>
                        <td class="text-muted font-mono">{{ number_format($inv->init) }}</td>
                        <td class="text-success">+{{ number_format($inv->in_s) }}</td>
                        <td style="color: var(--brand-return);">+{{ number_format($inv->in_r ?? 0) }}</td>
                        <td class="text-danger">-{{ number_format($inv->out) }}</td>
                        <td class="bg-light text-primary" style="font-size: 20px;">{{ number_format($inv->live_stock) }}</td>
                        <td>
                            <button class="btn btn-dark btn-sm rounded-pill px-4 font-weight-black" onclick="quickTake('{{ trim($inv->part_no) }}')">TAKE</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 📑 NAVIGATION & WORKSTREAM --}}
    <div class="nav-section">
        <ul class="nav nav-pills" id="ptTab">
            @foreach($availableCustomers as $index => $customer)
            @php $slugPT = Str::slug($customer); $count = $activeWelding->where('customer', $customer)->count(); @endphp
            <li class="nav-item">
                <a class="nav-link {{ $index == 0 ? 'active' : '' }}" data-toggle="pill" href="#pt-{{ $slugPT }}">
                    {{ strtoupper($customer) }} @if($count > 0) <span class="badge badge-danger ml-2">{{ $count }}</span> @endif
                </a>
            </li>
            @endforeach
        </ul>
    </div>

    <div class="tab-content" id="ptTabContent">
        @foreach($availableCustomers as $index => $customer)
        @php $slugPT = Str::slug($customer); @endphp
        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="pt-{{ $slugPT }}">
            @foreach($activeWelding->where('customer', $customer) as $aw)
            <div class="work-card shadow-sm animate__animated animate__fadeInUp">
                <div class="col-md-3">
                    <span class="station-tag">{{ $aw->kode_line ?? 'UNASSIGNED' }}</span>
                    <div class="font-weight-black text-primary font-mono" style="font-size: 14px;">{{ $aw->no_produksi_stamping }}</div>
                </div>
                <div class="col-md-4 border-left pl-4">
                    <div class="font-weight-black h5 mb-0">{{ $aw->part_no }}</div>
                    <small class="text-muted font-weight-bold uppercase">{{ $aw->part_name }}</small>
                </div>
                <div class="col-md-2 text-center">
                    <div class="qty-display">{{ number_format($aw->qty_masuk) }}</div>
                    <small class="text-muted font-weight-black uppercase" style="font-size: 9px;">Batch Target</small>
                </div>
                <div class="col-md-3 text-right">
                    @if($aw->batch_status == 'PENDING')
                        <form action="{{ route('welding.start', $aw->id) }}" method="POST">
                            @csrf @method('PUT')
                            <button class="btn btn-primary rounded-pill px-5 font-weight-black shadow-lg">START OPERATION</button>
                        </form>
                    @else
                        <button class="btn btn-success rounded-pill px-5 font-weight-black shadow-lg" data-toggle="modal" data-target="#modalFinish{{ $aw->id }}">FINISH BATCH</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>

{{-- 🏁 MODAL FINISH (QUALITY & RETURN GATE) --}}
@foreach($activeWelding as $aw)
<div class="modal fade" id="modalFinish{{ $aw->id }}" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0" style="border-radius: 40px; overflow: hidden;">
            <div class="modal-header bg-dark text-white p-4 border-0">
                <h5 class="modal-title font-weight-black text-uppercase" style="font-family: 'Orbitron'; letter-spacing: 1px;">Industrial Inspection Gate</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('welding.finish', $aw->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-5">
                    <div class="row">
                        <div class="col-md-4 text-center border-right pr-5">
                            <div class="p-4 bg-light rounded-3xl mb-4">
                                <h1 class="font-weight-black text-dark mb-0" style="font-family: 'Orbitron'; font-size: 50px;">{{ number_format($aw->qty_masuk) }}</h1>
                                <small class="text-muted font-weight-black uppercase">Batch Capacity</small>
                            </div>
                            
                            <div class="form-group mb-4">
                                <label class="small font-weight-black text-success uppercase mb-2">Quantity Passed (OK)</label>
                                <input type="number" name="qty_ok" id="input_ok_{{ $aw->id }}" class="form-control tech-input-lg text-success" 
                                       value="{{ $aw->qty_masuk }}" required oninput="calculateTotal({{ $aw->id }}, {{ $aw->qty_masuk }})">
                            </div>

                            <div id="sec_msg_{{ $aw->id }}" class="security-status status-match">
                                <i class="fas fa-shield-check mr-2"></i>DATA_INTEGRITY_VERIFIED
                            </div>
                        </div>
                        
                        <div class="col-md-8 pl-5">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="return-box">
                                        <label class="small font-weight-black text-primary uppercase d-block mb-2">
                                            <i class="fas fa-undo-alt mr-2"></i> WIP Return to Rack
                                        </label>
                                        <input type="number" name="qty_return" id="input_ret_{{ $aw->id }}" class="form-control tech-input-lg border-primary" 
                                               style="color: var(--brand-primary); height: 60px; font-size: 24px;" value="0" oninput="calculateTotal({{ $aw->id }}, {{ $aw->qty_masuk }})">
                                        <small class="text-muted font-weight-bold d-block mt-2">Sisa komponen balik ke rak WIP.</small>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="p-3 bg-white border-danger border rounded-2xl" style="border-width: 2px;">
                                        <label class="small font-weight-black text-danger uppercase d-block mb-2">Rejected (NG) Total</label>
                                        <input type="number" name="qty_ng" id="total_ng_{{ $aw->id }}" class="form-control tech-input-lg border-0 bg-transparent text-danger" 
                                               style="height: 60px; font-size: 24px;" value="0" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                                <h6 class="font-weight-black text-dark mb-0 uppercase"><i class="fas fa-bug mr-2 text-danger"></i>Defect Breakdown</h6>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-4 font-weight-black" onclick="addNgRow({{ $aw->id }}, {{ $aw->qty_masuk }})">+ ADD NG</button>
                            </div>
                            
                            <div id="ng_container_{{ $aw->id }}" style="max-height: 180px; overflow-y: auto;">
                                <div class="text-center text-muted py-4 border rounded-2xl border-dashed" id="no_ng_msg_{{ $aw->id }}">No defects recorded.</div>
                            </div>

                            <div class="mt-4">
                                <label class="small font-weight-black text-dark uppercase mb-2">Operator Remarks</label>
                                <textarea name="keterangan" class="form-control" style="border-radius: 15px; border: 2px solid #f1f5f9;" placeholder="Abnormalities..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" id="btn_submit_{{ $aw->id }}" class="btn btn-success btn-block py-4 font-weight-black rounded-2xl shadow-xl uppercase" style="font-size: 1.2rem; letter-spacing: 2px;">
                        Authorize & Submit to Master Vault
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- MODAL DEPLOY & SCRIPTS (SAMA SEPERTI SEBELUMNYA) --}}
@include('welding.welding_modals')

<script>
    const listPenyakit = @json($listNG->pluck('ng_name'));

    function addNgRow(batchId, target) {
        $(`#no_ng_msg_${batchId}`).hide();
        const id = Date.now();
        let options = listPenyakit.map(p => `<option value="${p}">${p.toUpperCase()}</option>`).join('');
        const html = `
            <div class="ng-row-item animate__animated animate__fadeInDown d-flex align-items-center mb-2 p-2 bg-light rounded-xl" id="row-${id}">
                <select name="ng_detail_type[]" class="form-control form-control-sm border-0 bg-transparent font-weight-bold flex-grow-1">${options}</select>
                <input type="number" name="ng_detail_qty[]" class="form-control form-control-sm tech-input ng-qty-input mx-3" style="width: 80px; height: 40px;" placeholder="0" required oninput="calculateTotal(${batchId}, ${target})">
                <button type="button" class="btn btn-link text-danger p-0" onclick="removeNgRow(${id}, ${batchId}, ${target})"><i class="fas fa-times-circle fa-lg"></i></button>
            </div>`;
        $(`#ng_container_${batchId}`).append(html);
    }

    function removeNgRow(rowId, batchId, target) {
        $(`#row-${rowId}`).remove();
        calculateTotal(batchId, target);
        if ($(`#ng_container_${batchId}`).children('.ng-row-item').length === 0) $(`#no_ng_msg_${batchId}`).show();
    }

    // ✨ LOGIKA KALKULASI SINKRON (OK + NG + RET = TARGET)
    function calculateTotal(batchId, target) {
        let totalNg = 0;
        $(`#ng_container_${batchId} .ng-qty-input`).each(function() { totalNg += parseInt($(this).val()) || 0; });
        $(`#total_ng_${batchId}`).val(totalNg);

        let ret = parseInt($(`#input_ret_{{ $aw->id ?? 0 }}`).val()) || 0; // Seharusnya dinamis ID modal
        // Karena di loop Blade, mari gunakan pencarian selector yang lebih aman:
        let currentRet = parseInt($(`#modalFinish${batchId} #input_ret_${batchId}`).val()) || 0;

        let ok = parseInt($(`#input_ok_${batchId}`).val()) || 0;
        let grandTotal = ok + totalNg + currentRet;

        let msgBox = $(`#sec_msg_${batchId}`);
        let btnSubmit = $(`#btn_submit_${batchId}`);

        if (grandTotal === target && ok >= 0) {
            msgBox.removeClass('status-error animate__headShake').addClass('status-match')
                  .html('<i class="fas fa-shield-check mr-2"></i>DATA_INTEGRITY_MATCHED');
            btnSubmit.prop('disabled', false).css('opacity', '1');
        } else {
            let gap = target - grandTotal;
            msgBox.removeClass('status-match').addClass('status-error animate__headShake')
                  .html(`<i class="fas fa-exclamation-triangle mr-2"></i>GAP: ${gap} PCS`);
            btnSubmit.prop('disabled', true).css('opacity', '0.5');
        }
    }
</script>
@endsection