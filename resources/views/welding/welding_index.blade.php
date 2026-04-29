@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-return: #6366f1; --dark-surface: #0f172a; --bg-main: #f1f5f9;
        --ind-border: #e2e8f0;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #1e293b; }
    
    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(135deg, var(--brand-primary), #7209b7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    /* 📈 LEDGER TABLE - INDUSTRIAL GLASS */
    .ledger-container { background: #fff; border-radius: 30px; border: 1px solid var(--ind-border); overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.04); margin-bottom: 35px; }
    .table-ledger thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 22px; border: none; }
    .table-ledger td { padding: 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; transition: 0.3s; }
    .table-ledger tbody tr:hover { background-color: #fcfcfc; }
    
    /* 🏷️ PT NAVIGATION - CYBER PILLS */
    .nav-section { background: #fff; padding: 8px; border-radius: 22px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 30px; border: 1px solid #e2e8f0; display: inline-flex; }
    .nav-pills .nav-link { 
        border-radius: 16px; padding: 12px 28px; font-weight: 800; font-size: 0.75rem;
        transition: 0.3s; color: #64748b !important; position: relative;
    }
    .nav-pills .nav-link.active { background: var(--dark-surface) !important; color: #fff !important; box-shadow: 0 10px 20px rgba(15, 23, 42, 0.2); }
    .count-badge { position: absolute; top: -5px; right: -5px; background: var(--brand-danger); color: white; font-size: 9px; padding: 2px 6px; border-radius: 20px; border: 2px solid #fff; }

    /* 🛠️ WORK CARDS - HIGH VISIBILITY */
    .work-card { 
        background: #fff; border-radius: 28px; border: 1px solid #eef2f6; 
        padding: 28px; margin-bottom: 20px; transition: 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); 
        display: flex; align-items: center; position: relative; overflow: hidden;
    }
    .work-card:hover { transform: scale(1.015); box-shadow: 0 25px 60px rgba(67, 97, 238, 0.12); border-color: var(--brand-primary); }
    .work-card::before { content: ""; position: absolute; left: 0; top: 0; height: 100%; width: 6px; background: var(--brand-primary); opacity: 0; transition: 0.3s; }
    .work-card:hover::before { opacity: 1; }
    
    .station-tag { background: #f1f5f9; color: var(--dark-surface); font-family: 'JetBrains Mono'; font-size: 10px; padding: 5px 12px; border-radius: 10px; font-weight: 800; margin-bottom: 10px; display: inline-block; border: 1px solid #e2e8f0; }
    .qty-display { font-family: 'Orbitron'; font-weight: 900; font-size: 42px; color: var(--dark-surface); line-height: 1; letter-spacing: -2px; }

    /* FORM & INPUTS */
    .tech-input-lg { border-radius: 20px; border: 2.5px solid #eef2f6; font-weight: 800; transition: 0.3s; height: 80px; background: #f8fafc; text-align: center; font-family: 'Orbitron'; font-size: 32px; }
    .tech-input-lg:focus { border-color: var(--brand-primary); outline: none; background: #fff; box-shadow: 0 0 0 8px rgba(67, 97, 238, 0.1); }

    /* SECURITY GATE */
    .security-status { border-radius: 20px; padding: 18px; font-weight: 800; font-size: 12px; text-align: center; transition: 0.3s; margin-top: 25px; border-left: 8px solid transparent; }
    .status-match { background: #dcfce7; color: #166534; border-color: #bbf7d0; border-left-color: var(--brand-success); }
    .status-error { background: #fee2e2; color: #991b1b; border-color: #fecaca; border-left-color: var(--brand-danger); }

    .return-box { background: rgba(99, 102, 241, 0.05); border: 2px dashed var(--brand-return); border-radius: 26px; padding: 24px; transition: 0.3s; }
    .return-box:focus-within { background: rgba(99, 102, 241, 0.1); border-style: solid; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    {{-- 🛰️ HEADER HUB --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding Terminal <span style="-webkit-text-fill-color: var(--dark-surface);">v3.2</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0">
                <i class="fas fa-satellite-dish text-primary mr-2"></i> Quality Assurance Command Center / Industrial Core
            </p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
            <a href="{{ route('welding.history') }}" class="btn btn-white rounded-pill px-4 font-weight-extrabold border mr-2 shadow-sm"><i class="fas fa-archive mr-2"></i>VAULT</a>
            <button class="btn btn-primary rounded-pill px-4 font-weight-extrabold shadow-lg" data-toggle="modal" data-target="#modalDeployWelding">
                <i class="fas fa-plus-circle mr-2"></i>DEPLOY_BATCH
            </button>
        </div>
    </div>

    {{-- 📊 WIP LEDGER TABLE --}}
    <div class="ledger-container animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5" style="width: 25%;">Part Identification</th>
                        <th>Opening</th>
                        <th class="text-success">In (Stamp)</th>
                        <th style="color: var(--brand-return);">In (Return)</th>
                        <th class="text-danger">Out (Weld)</th>
                        <th class="bg-light text-primary">Live Stock</th>
                        <th class="text-right pr-5">Control</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventoryWelding as $inv)
                    <tr>
                        <td class="text-left pl-5">
                            <div class="font-weight-black text-dark" style="font-size: 15px; font-family: 'JetBrains Mono';">{{ $inv->part_no }}</div>
                            <small class="text-muted font-weight-bold uppercase" style="font-size: 9px; letter-spacing: 0.5px;">{{ $inv->part_name }}</small>
                        </td>
                        <td class="text-muted font-mono">{{ number_format($inv->init) }}</td>
                        <td class="text-success font-weight-black">+{{ number_format($inv->in_s) }}</td>
                        <td class="font-weight-black" style="color: var(--brand-return);">+{{ number_format($inv->in_r ?? 0) }}</td>
                        <td class="text-danger font-weight-black">-{{ number_format($inv->out) }}</td>
                        <td class="bg-light text-primary font-weight-black" style="font-size: 22px; font-family: 'Orbitron';">{{ number_format($inv->live_stock) }}</td>
                        <td class="text-right pr-5">
                            <button class="btn btn-dark btn-sm rounded-pill px-4 font-weight-black hover-lift" onclick="quickTake('{{ trim($inv->part_no) }}')">TAKE_UNIT</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 📑 PT NAVIGATION --}}
    <div class="nav-section animate__animated animate__fadeIn">
        <ul class="nav nav-pills" id="ptTab">
            @foreach($availableCustomers as $index => $customer)
            @php 
                $slugPT = Str::slug($customer); 
                $count = $activeWelding->where('customer', $customer)->count(); 
            @endphp
            <li class="nav-item">
                <a class="nav-link {{ $index == 0 ? 'active' : '' }}" data-toggle="pill" href="#pt-{{ $slugPT }}">
                    {{ strtoupper($customer) }} 
                    @if($count > 0) <span class="count-badge animate__animated animate__pulse animate__infinite">{{ $count }}</span> @endif
                </a>
            </li>
            @endforeach
        </ul>
    </div>

    {{-- 🛠️ ACTIVE BATCHES --}}
    <div class="tab-content" id="ptTabContent">
        @foreach($availableCustomers as $index => $customer)
        @php $slugPT = Str::slug($customer); @endphp
        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="pt-{{ $slugPT }}">
            @forelse($activeWelding->where('customer', $customer) as $aw)
            <div class="work-card shadow-sm animate__animated animate__fadeInUp">
                <div class="col-md-3">
                    <span class="station-tag"><i class="fas fa-microchip mr-1"></i>{{ $aw->kode_line ?? 'STANDBY' }}</span>
                    <div class="font-weight-black text-primary font-mono" style="font-size: 14px;">{{ $aw->no_produksi_stamping }}</div>
                </div>
                <div class="col-md-4 border-left pl-4">
                    <div class="font-weight-black h5 mb-0 text-dark">{{ $aw->part_no }}</div>
                    <small class="text-muted font-weight-bold uppercase">{{ $aw->part_name }}</small>
                </div>
                <div class="col-md-2 text-center">
                    <div class="qty-display">{{ number_format($aw->qty_masuk) }}</div>
                    <small class="text-muted font-weight-black uppercase" style="font-size: 9px;">Target Batch</small>
                </div>
                <div class="col-md-3 text-right">
                    @if($aw->batch_status == 'PENDING')
                        <form action="{{ route('welding.start', $aw->id) }}" method="POST">
                            @csrf @method('PUT')
                            <button class="btn btn-primary btn-block rounded-pill py-3 font-weight-black shadow-lg animate__animated animate__pulse animate__infinite">START_OPERATION</button>
                        </form>
                    @else
                        <button class="btn btn-success btn-block rounded-pill py-3 font-weight-black shadow-lg" data-toggle="modal" data-target="#modalFinish{{ $aw->id }}">FINISH_BATCH</button>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-5 bg-white rounded-3xl border-2 border-dashed">
                <i class="fas fa-check-circle fa-3x text-light mb-3"></i>
                <p class="text-muted font-weight-bold">Zero Active Workstream for {{ $customer }}</p>
            </div>
            @endforelse
        </div>
        @endforeach
    </div>
</div>

{{-- 🏁 MODAL FINISH (SMART QUALITY GATE) --}}
@foreach($activeWelding as $aw)
<div class="modal fade" id="modalFinish{{ $aw->id }}" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 45px; overflow: hidden;">
            <div class="modal-header bg-dark text-white p-4 border-0">
                <div class="d-flex align-items-center">
                    <div class="bg-success rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-shield-check text-white"></i>
                    </div>
                    <h5 class="modal-title font-weight-black text-uppercase" style="font-family: 'Orbitron';">Quality_Inspection_Gate</h5>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('welding.finish', $aw->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-5">
                    <div class="row">
                        {{-- LEFT SIDE: TARGET & OK --}}
                        <div class="col-md-4 text-center border-right pr-5">
                            <div class="p-4 bg-light rounded-3xl mb-4 border">
                                <h1 class="font-weight-black text-dark mb-0" style="font-family: 'Orbitron'; font-size: 50px;">{{ number_format($aw->qty_masuk) }}</h1>
                                <small class="text-muted font-weight-black uppercase">Batch Deployment Target</small>
                            </div>
                            
                            <div class="form-group mb-4">
                                <label class="small font-weight-black text-success uppercase mb-2"><i class="fas fa-check-circle mr-1"></i> Quality Passed (OK)</label>
                                <input type="number" name="qty_ok" id="input_ok_{{ $aw->id }}" class="form-control tech-input-lg text-success border-success" 
                                       value="{{ $aw->qty_masuk }}" required oninput="calculateTotal({{ $aw->id }}, {{ $aw->qty_masuk }})">
                            </div>

                            <div id="sec_msg_{{ $aw->id }}" class="security-status status-match animate__animated">
                                <i class="fas fa-shield-check mr-2"></i>INTEGRITY_VERIFIED
                            </div>
                        </div>
                        
                        {{-- RIGHT SIDE: RETURN & NG --}}
                        <div class="col-md-8 pl-5">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="return-box">
                                        <label class="small font-weight-black text-primary uppercase d-block mb-3">
                                            <i class="fas fa-undo-alt mr-2"></i> WIP_Return_To_Rack
                                        </label>
                                        <input type="number" name="qty_return" id="input_ret_{{ $aw->id }}" class="form-control tech-input-lg border-primary" 
                                               style="color: var(--brand-primary); height: 70px;" value="0" oninput="calculateTotal({{ $aw->id }}, {{ $aw->qty_masuk }})">
                                        <small class="text-muted font-weight-bold d-block mt-3">Sisa komponen yang tidak jadi diproses.</small>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="p-4 bg-white border-danger border rounded-3xl" style="border-width: 2px;">
                                        <label class="small font-weight-black text-danger uppercase d-block mb-3">Total Rejected (NG)</label>
                                        <input type="number" name="qty_ng" id="total_ng_{{ $aw->id }}" class="form-control tech-input-lg border-0 bg-transparent text-danger" 
                                               style="height: 70px;" value="0" readonly>
                                        <small class="text-muted font-weight-bold d-block mt-3">Akumulasi dari rincian defect di bawah.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                                <h6 class="font-weight-black text-dark mb-0 uppercase"><i class="fas fa-bug mr-2 text-danger"></i>Defect_Breakdown</h6>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-4 font-weight-black" onclick="addNgRow({{ $aw->id }}, {{ $aw->qty_masuk }})">+ ADD_NG_TYPE</button>
                            </div>
                            
                            <div id="ng_container_{{ $aw->id }}" class="pr-2" style="max-height: 200px; overflow-y: auto;">
                                <div class="text-center text-muted py-4 border rounded-2xl border-dashed" id="no_ng_msg_{{ $aw->id }}">
                                    <i class="fas fa-check-double text-success mr-2"></i>Zero Defect Production
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="small font-weight-black text-dark uppercase mb-2">Operator Notes / Abnormalities</label>
                                <textarea name="keterangan" class="form-control" style="border-radius: 20px; border: 2px solid #f1f5f9; padding: 15px;" placeholder="Input any process issues here..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <input type="hidden" name="part_no" value="{{ $aw->part_no }}">
                    <button type="submit" id="btn_submit_{{ $aw->id }}" class="btn btn-success btn-block py-4 font-weight-black rounded-3xl shadow-2xl uppercase" style="font-size: 1.3rem; letter-spacing: 2px;">
                        Authorize & Register to Master Vault
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- MODAL DEPLOY & SCRIPTS --}}
@include('welding.welding_modals')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const listPenyakit = @json($listNG->pluck('ng_name'));

    function addNgRow(batchId, target) {
        $(`#no_ng_msg_${batchId}`).hide();
        const id = Date.now();
        let options = listPenyakit.map(p => `<option value="${p}">${p.toUpperCase()}</option>`).join('');
        const html = `
            <div class="ng-row-item animate__animated animate__fadeInDown d-flex align-items-center mb-2 p-3 bg-light rounded-2xl border" id="row-${id}">
                <div class="mr-3 text-danger"><i class="fas fa-exclamation-triangle"></i></div>
                <select name="ng_detail_type[]" class="form-control border-0 bg-transparent font-weight-bold flex-grow-1" style="font-size: 13px;">${options}</select>
                <input type="number" name="ng_detail_qty[]" class="form-control tech-input ng-qty-input mx-3 text-center font-weight-black" style="width: 100px; height: 45px; border-radius: 12px;" placeholder="0" required oninput="calculateTotal(${batchId}, ${target})">
                <button type="button" class="btn btn-link text-danger p-0" onclick="removeNgRow(${id}, ${batchId}, ${target})"><i class="fas fa-times-circle fa-lg"></i></button>
            </div>`;
        $(`#ng_container_${batchId}`).append(html);
    }

    function removeNgRow(rowId, batchId, target) {
        $(`#row-${rowId}`).remove();
        calculateTotal(batchId, target);
        if ($(`#ng_container_${batchId}`).children('.ng-row-item').length === 0) $(`#no_ng_msg_${batchId}`).show();
    }

    // ✨ SMART LOGIC: TOTAL OK + TOTAL NG + TOTAL RETURN = TARGET BATCH
    function calculateTotal(batchId, target) {
        let totalNg = 0;
        $(`#modalFinish${batchId} .ng-qty-input`).each(function() { totalNg += parseInt($(this).val()) || 0; });
        $(`#total_ng_${batchId}`).val(totalNg);

        let ok = parseInt($(`#input_ok_${batchId}`).val()) || 0;
        let ret = parseInt($(`#input_ret_${batchId}`).val()) || 0;
        
        let grandTotal = ok + totalNg + ret;

        let msgBox = $(`#sec_msg_${batchId}`);
        let btnSubmit = $(`#btn_submit_${batchId}`);

        if (grandTotal === target && ok >= 0 && ret >= 0) {
            msgBox.removeClass('status-error animate__headShake').addClass('status-match')
                  .html('<i class="fas fa-shield-check mr-2"></i>DATA_INTEGRITY_MATCHED');
            btnSubmit.prop('disabled', false).css('opacity', '1').css('cursor', 'pointer');
        } else {
            let gap = target - grandTotal;
            msgBox.removeClass('status-match').addClass('status-error animate__headShake')
                  .html(`<i class="fas fa-triangle-exclamation mr-2"></i>GAP DETECTED: ${gap > 0 ? '+'+gap : gap} PCS`);
            btnSubmit.prop('disabled', true).css('opacity', '0.5').css('cursor', 'not-allowed');
        }
    }

    function quickTake(partNo) {
        $('#part_select').val(partNo);
        $('#modalDeployWelding').modal('show');
    }
</script>
@endsection