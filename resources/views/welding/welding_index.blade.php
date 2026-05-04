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

    /* 📈 LEDGER TABLE (Flow Mutation) */
    .ledger-container { background: #fff; border-radius: 30px; border: 1px solid var(--ind-border); overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.04); margin-bottom: 35px; }
    .table-ledger thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 22px; border: none; }
    .table-ledger td { padding: 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; transition: 0.3s; }
    
    /* Indikator Rework di Kartu */
    .rework-indicator { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 4px 12px; border-radius: 8px; font-size: 9px; font-weight: 800; display: inline-block; margin-left: 10px; }
    .badge-rework { background: var(--brand-return); color: white; padding: 5px 10px; border-radius: 8px; font-size: 10px; margin-bottom: 8px; display: inline-block; }

    .nav-section { background: #fff; padding: 8px; border-radius: 22px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 30px; border: 1px solid #e2e8f0; display: inline-flex; }
    .nav-pills .nav-link { border-radius: 16px; padding: 12px 28px; font-weight: 800; font-size: 0.75rem; transition: 0.3s; color: #64748b !important; position: relative; }
    .nav-pills .nav-link.active { background: var(--dark-surface) !important; color: #fff !important; box-shadow: 0 10px 20px rgba(15, 23, 42, 0.2); }
    
    .work-card { background: #fff; border-radius: 28px; border: 1px solid #eef2f6; padding: 28px; margin-bottom: 20px; transition: 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); display: flex; align-items: center; position: relative; overflow: hidden; }
    .tech-input-lg { border-radius: 20px; border: 2.5px solid #eef2f6; font-weight: 800; transition: 0.3s; height: 80px; background: #f8fafc; text-align: center; font-family: 'Orbitron'; font-size: 32px; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    {{-- 🛰️ HEADER HUB --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding Terminal <span style="-webkit-text-fill-color: var(--dark-surface);">v3.2</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0"><i class="fas fa-satellite-dish text-primary mr-2"></i> Quality Assurance Command Center</p>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('welding.history') }}" class="btn btn-white rounded-pill px-4 font-weight-extrabold border mr-2 shadow-sm">VAULT</a>
            <a href="{{ route('welding.historyWelding') }}" class="btn btn-white border shadow-sm mr-2" style="width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--brand-primary); text-decoration: none;" title="History Produksi">
                <i class="fas fa-history"></i>
            </a>
            <button class="btn btn-primary rounded-pill px-4 font-weight-extrabold shadow-lg" data-toggle="modal" data-target="#modalDeployWelding"><i class="fas fa-plus-circle mr-1"></i> DEPLOY</button>
        </div>
    </div>

    {{-- 📊 LEDGER TABLE (SINKRONISASI MUTASI STOK RILL) --}}
    <div class="ledger-container">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Part Identification</th>
                        <th>Opening</th>
                        <th class="text-success">In (Stamp)</th>
                        <th style="color: var(--brand-return);">In (Ret QC)</th>
                        <th class="text-danger">Out (Deploy)</th>
                        <th class="bg-light text-primary">Live Stock</th>
                        <th class="text-right pr-5">Control</th>
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
                        <td style="color: var(--brand-return);">+{{ number_format($inv->in_r) }}</td>
                        <td class="text-danger">-{{ number_format($inv->out) }}</td>
                        <td class="bg-light text-primary font-weight-black" style="font-size: 22px;">{{ number_format($inv->live_stock) }}</td>
                        <td class="text-right pr-5">
                            <button class="btn btn-dark btn-sm rounded-pill px-4 font-weight-black" onclick="quickTake('{{ trim($inv->part_no) }}')">TAKE</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 📑 PT NAVIGATION & ACTIVE BATCHES --}}
    <div class="nav-section animate__animated animate__fadeIn">
        <ul class="nav nav-pills" id="ptTab">
            @foreach($availableCustomers as $index => $customer)
            @php $slugPT = Str::slug($customer); $count = $activeWelding->where('customer', $customer)->count(); @endphp
            <li class="nav-item">
                <a class="nav-link {{ $index == 0 ? 'active' : '' }}" data-toggle="pill" href="#pt-{{ $slugPT }}">
                    {{ strtoupper($customer) }} @if($count > 0) <span class="count-badge">{{ $count }}</span> @endif
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
                    @if($aw->qty_return > 0)
                        <span class="badge-rework"><i class="fas fa-redo-alt mr-1"></i> REWORK (QC RETURN)</span>
                    @endif
                    <span class="station-tag d-block">{{ $aw->kode_line ?? 'STANDBY' }}</span>
                    <div class="font-weight-black text-primary font-mono">{{ $aw->no_produksi_stamping }}</div>
                </div>
                <div class="col-md-4 border-left pl-4">
                    <div class="font-weight-black h5 mb-0">{{ $aw->part_no }}</div>
                    <small class="text-muted font-weight-bold uppercase">{{ $aw->part_name }}</small>
                </div>
                <div class="col-md-2 text-center">
                    {{-- ✨ Logika Target: Jika rework, targetnya adalah qty_return --}}
                    <div class="qty-display">{{ number_format($aw->qty_return > 0 ? $aw->qty_return : $aw->qty_masuk) }}</div>
                    <small class="text-muted font-weight-black uppercase" style="font-size: 9px;">Target Unit</small>
                </div>
                <div class="col-md-3 text-right">
                    @if($aw->status == 'PENDING')
                        <form action="{{ route('welding.start', $aw->id) }}" method="POST">
                            @csrf @method('PUT')
                            <button class="btn btn-primary rounded-pill py-3 px-5 font-weight-black shadow-lg">START OPERATION</button>
                        </form>
                    @else
                        <button class="btn btn-success rounded-pill py-3 px-5 font-weight-black shadow-lg" data-toggle="modal" data-target="#modalFinish{{ $aw->id }}">FINISH BATCH</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>

{{-- 🏁 MODAL FINISH (LOGIKA TARGET DISESUAIKAN RILL) --}}
@foreach($activeWelding as $aw)
@php $currentTarget = $aw->qty_return > 0 ? $aw->qty_return : $aw->qty_masuk; @endphp
<div class="modal fade" id="modalFinish{{ $aw->id }}" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 45px; overflow: hidden;">
            <div class="modal-header bg-dark text-white p-4 border-0">
                <h5 class="modal-title font-weight-black text-uppercase" style="font-family: 'Orbitron';">Quality_Gate_Auth</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('welding.finish', $aw->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-5">
                    <div class="row">
                        <div class="col-md-4 text-center border-right pr-5">
                            <div class="p-4 bg-light rounded-3xl mb-4 border">
                                <h1 class="font-weight-black text-dark mb-0" style="font-family: 'Orbitron'; font-size: 50px;">{{ number_format($currentTarget) }}</h1>
                                <small class="text-muted font-weight-black uppercase">Required Output</small>
                            </div>
                            
                            <div class="form-group mb-4">
                                <label class="small font-weight-black text-success uppercase mb-2"><i class="fas fa-check-circle mr-1"></i> Verified OK</label>
                                <input type="number" name="qty_ok" id="input_ok_{{ $aw->id }}" class="form-control tech-input-lg text-success border-success" 
                                       value="{{ $currentTarget }}" required oninput="manualOkCheck({{ $aw->id }}, {{ $currentTarget }})">
                            </div>

                            <div id="sec_msg_{{ $aw->id }}" class="security-status status-match animate__animated">
                                <i class="fas fa-shield-check mr-2"></i>INTEGRITY_VERIFIED
                            </div>
                        </div>
                        
                        <div class="col-md-8 pl-5">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="return-box">
                                        <label class="small font-weight-black text-primary uppercase d-block mb-3">
                                            <i class="fas fa-undo-alt mr-2"></i> Return_To_WIP_Rack
                                        </label>
                                        <input type="number" name="qty_return" id="input_ret_{{ $aw->id }}" class="form-control tech-input-lg border-primary" 
                                               style="color: var(--brand-primary); height: 70px;" value="0" oninput="calculateTotal({{ $aw->id }}, {{ $currentTarget }})">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="p-4 bg-white border-danger border rounded-3xl" style="border-width: 2px;">
                                        <label class="small font-weight-black text-danger uppercase d-block mb-3">Defect Accumulation (NG)</label>
                                        <input type="number" name="qty_ng" id="total_ng_{{ $aw->id }}" class="form-control tech-input-lg border-0 bg-transparent text-danger" 
                                               style="height: 70px;" value="0" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="font-weight-black text-dark mb-0 uppercase"><i class="fas fa-bug mr-2 text-danger"></i>NG_Breakdown</h6>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-4 font-weight-black" onclick="addNgRow({{ $aw->id }}, {{ $currentTarget }})">+ ADD NG</button>
                            </div>
                            
                            <div id="ng_container_{{ $aw->id }}" class="pr-2" style="max-height: 180px; overflow-y: auto;">
                                <div class="text-center text-muted py-4 border rounded-2xl border-dashed" id="no_ng_msg_{{ $aw->id }}">No defects reported.</div>
                            </div>

                            <div class="mt-4">
                                <label class="small font-weight-black text-dark uppercase mb-2">Process Notes</label>
                                <textarea name="keterangan" class="form-control" style="border-radius: 20px; border: 2px solid #f1f5f9;"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" id="btn_submit_{{ $aw->id }}" class="btn btn-success btn-block py-4 font-weight-black rounded-3xl shadow-xl uppercase" style="font-size: 1.3rem; letter-spacing: 2px;">
                        Finalize Batch Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@include('welding.welding_modals')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const listPenyakit = @json($listNG->pluck('ng_name'));

    function addNgRow(batchId, target) {
        $(`#no_ng_msg_${batchId}`).hide();
        const id = Date.now();
        let options = listPenyakit.map(p => `<option value="${p}">${p.toUpperCase()}</option>`).join('');
        const html = `
            <div class="ng-row-item d-flex align-items-center mb-2 p-3 bg-light rounded-2xl border" id="row-${id}">
                <select name="ng_detail_type[]" class="form-control border-0 bg-transparent font-weight-bold flex-grow-1">${options}</select>
                <input type="number" name="ng_detail_qty[]" class="form-control tech-input ng-qty-input mx-3 text-center font-weight-black" style="width: 100px; height: 45px; border-radius: 10px;" value="0" oninput="calculateTotal(${batchId}, ${target})">
                <button type="button" class="btn btn-link text-danger p-0" onclick="removeNgRow(${id}, ${batchId}, ${target})"><i class="fas fa-times-circle fa-lg"></i></button>
            </div>`;
        $(`#ng_container_${batchId}`).append(html);
    }

    function removeNgRow(rowId, batchId, target) {
        $(`#row-${rowId}`).remove();
        calculateTotal(batchId, target);
        if ($(`#ng_container_${batchId}`).children('.ng-row-item').length === 0) $(`#no_ng_msg_${batchId}`).show();
    }

    function calculateTotal(batchId, target) {
        let totalNg = 0;
        $(`#modalFinish${batchId} .ng-qty-input`).each(function() { 
            totalNg += parseInt($(this).val()) || 0; 
        });
        $(`#total_ng_${batchId}`).val(totalNg);

        let ret = parseInt($(`#modalFinish${batchId} #input_ret_${batchId}`).val()) || 0;
        let okAuto = target - totalNg - ret;
        $(`#input_ok_${batchId}`).val(okAuto < 0 ? 0 : okAuto);
        refreshSecurityUI(batchId, target);
    }

    function manualOkCheck(batchId, target) {
        refreshSecurityUI(batchId, target);
    }

    function refreshSecurityUI(batchId, target) {
        let ok = parseInt($(`#input_ok_${batchId}`).val()) || 0;
        let ng = parseInt($(`#total_ng_${batchId}`).val()) || 0;
        let ret = parseInt($(`#input_ret_${batchId}`).val()) || 0;
        let grandTotal = ok + ng + ret;

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