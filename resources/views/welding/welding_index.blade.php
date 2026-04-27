@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-warning: #f59e0b; --dark-surface: #0f172a; --bg-main: #f8fafc;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #334155; }
    
    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(135deg, var(--brand-primary), #7209b7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    /* 📈 LEDGER TABLE */
    .ledger-container { background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.04); margin-bottom: 30px; }
    .table-ledger thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 18px; border-bottom: 2px solid #edf2f7; }
    .table-ledger td { padding: 18px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; }
    
    /* 🏷️ PT NAVIGATION */
    .nav-section { background: #fff; padding: 12px; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 25px; border: 1px solid #e2e8f0; display: inline-block; }
    .nav-pills .nav-link { 
        border-radius: 14px; padding: 10px 20px; font-weight: 800; font-size: 0.8rem;
        transition: 0.3s; margin-right: 8px;
        background: transparent; color: #64748b !important;
    }
    .nav-pills .nav-link.active { 
        background: var(--dark-surface) !important; color: #fff !important; 
        box-shadow: 0 8px 15px rgba(15, 23, 42, 0.2);
    }

    /* 🛠️ WORK CARDS (Glassmorphism) */
    .work-card { 
        background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px);
        border-radius: 24px; border: 1px solid #fff; 
        padding: 24px; margin-bottom: 20px; transition: 0.4s; 
        display: flex; align-items: center; box-shadow: 0 10px 20px rgba(0,0,0,0.02);
    }
    .work-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(67, 97, 238, 0.1); border-color: var(--brand-primary); }
    
    .station-badge { background: var(--dark-surface); color: var(--brand-warning); font-family: 'JetBrains Mono'; font-size: 10px; padding: 4px 12px; border-radius: 8px; display: inline-block; margin-bottom: 8px; border: 1px solid rgba(245, 158, 11, 0.3); }
    .qty-display { font-family: 'Orbitron'; font-weight: 900; font-size: 38px; color: var(--dark-surface); line-height: 1; letter-spacing: -2px; }

    .btn-action-custom { border-radius: 18px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; padding: 15px 25px; border: none; font-size: 0.8rem; }
    .tech-input { border-radius: 15px; border: 2px solid #e2e8f0; font-weight: 700; transition: 0.3s; height: 50px; }
    .tech-input:focus { border-color: var(--brand-primary); outline: none; box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1); }

    .ng-row-item { background: #fff5f5; border-radius: 16px; padding: 12px; margin-bottom: 10px; border: 1px solid #fed7d7; }
    
    /* Security Alert Styling */
    .security-status { border-radius: 12px; padding: 8px 15px; font-weight: 800; font-size: 11px; margin-top: 10px; text-align: center; }
    .status-match { background: #dcfce7; color: #166534; }
    .status-error { background: #fee2e2; color: #991b1b; animation: headShake 0.5s; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    {{-- 🛸 HEADER HUB --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding Terminal <span style="-webkit-text-fill-color: var(--dark-surface);">v3.0</span></h1>
            <div class="d-flex align-items-center">
                <span class="badge badge-success pulse mr-2" style="width:10px; height:10px; border-radius:50%;"></span>
                <p class="text-muted font-weight-bold small uppercase mb-0">System Authority: Level 3 // Data Encryption Active</p>
            </div>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
            <a href="{{ route('welding.history') }}" class="btn btn-white rounded-pill px-4 font-weight-extrabold border mr-2 shadow-sm"><i class="fas fa-microchip mr-2"></i> VAULT</a>
            <a href="{{ route('welding.history.weldig') }}" class="btn btn-white rounded-pill px-4 font-weight-extrabold border mr-2 shadow-sm text-primary"><i class="fas fa-history mr-2"></i> LOGS</a>
            <button class="btn btn-primary rounded-pill px-4 font-weight-extrabold shadow-lg mr-3" data-toggle="modal" data-target="#modalDeployWelding"><i class="fas fa-bolt mr-2"></i> NEW BATCH</button>
            
            <div class="bg-white px-4 py-2 rounded-2xl shadow-sm border border-primary text-center">
                <small class="text-muted font-weight-bold d-block uppercase" style="font-size: 8px; letter-spacing: 1px;">Execution Date</small>
                <span class="font-weight-bold text-primary" style="font-family: 'JetBrains Mono'; font-size: 14px;">{{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</span>
            </div>
        </div>
    </div>

    {{-- 📊 LEDGER TABLE --}}
    <div class="ledger-container">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">WIP IDENTIFICATION</th>
                        <th>STARTING</th>
                        <th class="text-success">IN_STAMPING</th>
                        <th class="text-danger">OUT_WELDING</th>
                        <th class="text-primary bg-light">LIVE_INVENTORY</th>
                        <th>LOAD</th>
                        <th class="text-right pr-4">COMMAND</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventoryWelding as $inv)
                    <tr>
                        <td class="text-left pl-4">
                            <div class="font-weight-extrabold text-dark" style="font-size: 15px; letter-spacing: -0.5px;">{{ $inv->part_no }}</div>
                            <small class="text-muted font-weight-bold uppercase" style="font-size: 9px; opacity: 0.7;">{{ $inv->part_name }}</small>
                        </td>
                        <td style="color: #94a3b8; font-family: 'JetBrains Mono';">{{ number_format($inv->init) }}</td>
                        <td class="text-success font-weight-bold">+{{ number_format($inv->in_s) }}</td>
                        <td class="text-danger font-weight-bold">-{{ number_format($inv->out) }}</td>
                        <td class="text-primary font-weight-black bg-light" style="font-size: 18px;">{{ number_format($inv->live_stock) }}</td>
                        <td><span class="badge badge-outline-secondary border px-3 py-1 font-weight-bold">{{ $inv->run }}x</span></td>
                        <td class="text-right pr-4">
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-4 font-weight-black" onclick="quickTake('{{ trim($inv->part_no) }}')">DEPLOY</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 📑 PT NAVIGATION --}}
    <div class="nav-section">
        <ul class="nav nav-pills" id="ptTab">
            @foreach($availableCustomers as $index => $customer)
            @php $slugPT = Str::slug($customer); $count = $activeWelding->where('customer', $customer)->count(); @endphp
            <li class="nav-item">
                <a class="nav-link {{ $index == 0 ? 'active' : '' }}" data-toggle="pill" href="#pt-{{ $slugPT }}">
                    {{ strtoupper($customer) }} @if($count > 0) <span class="count-badge animate__animated animate__pulse animate__infinite">{{ $count }}</span> @endif
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
            @php $filtered = $activeWelding->where('customer', $customer); @endphp
            @forelse($filtered as $aw)
            <div class="work-card shadow-sm animate__animated animate__fadeInUp">
                <div class="col-md-3">
                    <div class="station-badge">{{ $aw->kode_line ?? 'NO_STATION' }}</div>
                    <div class="font-weight-black text-primary" style="font-family: 'JetBrains Mono'; font-size: 14px;">
                        <i class="fas fa-qrcode mr-2 opacity-50"></i>{{ $aw->no_produksi_stamping }}
                    </div>
                </div>
                <div class="col-md-3 border-left pl-4">
                    <div class="font-weight-black h5 mb-0 text-dark">{{ $aw->part_no }}</div>
                    <small class="text-muted font-weight-bold uppercase">{{ $aw->part_name }}</small>
                </div>
                <div class="col-md-2 text-center">
                    <div class="qty-display">{{ number_format($aw->qty_masuk) }}</div>
                    <small class="text-muted font-weight-black" style="font-size: 9px; letter-spacing: 1px;">BATCH TARGET</small>
                </div>
                <div class="col-md-2 text-center">
                    @if($aw->batch_status == 'PENDING')
                        <span class="badge badge-warning py-2 px-4 rounded-pill font-weight-black shadow-sm">WAITING_AUTH</span>
                    @else
                        <span class="badge badge-info py-2 px-4 rounded-pill font-weight-black animate__animated animate__pulse animate__infinite shadow-sm">LIVE_PROCESS</span>
                    @endif
                </div>
                <div class="col-md-2 text-right">
                    @if($aw->batch_status == 'PENDING')
                        <form action="{{ route('welding.start', $aw->id) }}" method="POST">
                            @csrf @method('PUT')
                            <button class="btn btn-primary btn-block btn-action-custom shadow-lg">START OPERATION</button>
                        </form>
                    @else
                        <button class="btn btn-success btn-block btn-action-custom shadow-lg" data-toggle="modal" data-target="#modalFinish{{ $aw->id }}">FINISH BATCH</button>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-5 bg-white rounded-3xl border-2 border-dashed">
                <i class="fas fa-clipboard-check fa-3x text-light mb-3"></i>
                <p class="text-muted font-weight-bold mb-0">Operational clear. No active batches for {{ $customer }}.</p>
            </div>
            @endforelse
        </div>
        @endforeach
    </div>
</div>

{{-- 🏁 MODAL FINISH (SECURED QUALITY INSPECTION) --}}
@foreach($activeWelding as $aw)
<div class="modal fade" id="modalFinish{{ $aw->id }}" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0" style="border-radius: 30px; overflow: hidden;">
            <div class="modal-header bg-success text-white p-4 border-0">
                <h5 class="modal-title font-weight-black text-uppercase" style="font-family: 'Orbitron'; letter-spacing: 1px;">Quality Gate v3.0</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('welding.finish', $aw->id) }}" method="POST" onsubmit="return validateSecurity({{ $aw->id }}, {{ $aw->qty_masuk }})">
                @csrf @method('PUT')
                <div class="modal-body p-5">
                    <div class="row">
                        <div class="col-md-4 text-center border-right">
                            <div class="p-3 bg-light rounded-2xl mb-4">
                                <h1 class="font-weight-black text-dark mb-0" style="font-family: 'Orbitron';">{{ number_format($aw->qty_masuk) }}</h1>
                                <small class="text-muted font-weight-black uppercase">Required Output</small>
                            </div>
                            
                            <label class="small font-weight-black text-success uppercase mb-2">Quantity Passed (OK)</label>
                            <input type="number" name="qty_ok" id="input_ok_{{ $aw->id }}" class="form-control text-center tech-input mb-4 font-weight-black" 
                                   style="font-size: 24px;" value="{{ $aw->qty_masuk }}" required oninput="calculateSecurity({{ $aw->id }}, {{ $aw->qty_masuk }})">
                            
                            <label class="small font-weight-black text-danger uppercase mb-2">Total Rejected (NG)</label>
                            <input type="number" name="qty_ng" id="total_ng_{{ $aw->id }}" class="form-control text-center tech-input font-weight-black" 
                                   style="font-size: 24px; background: #f8fafc;" value="0" readonly>

                            {{-- ✨ SECURITY FEEDBACK --}}
                            <div id="sec_msg_{{ $aw->id }}" class="security-status status-match mt-3">
                                <i class="fas fa-shield-alt mr-2"></i>DATA_INTEGRITY_MATCHED
                            </div>
                        </div>
                        
                        <div class="col-md-8 pl-md-5">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="font-weight-black text-dark mb-0 uppercase"><i class="fas fa-vial mr-2 text-danger"></i>Defect Breakdown</h6>
                                <button type="button" class="btn btn-dark btn-sm rounded-pill font-weight-black px-3" onclick="addNgRow({{ $aw->id }})">
                                    <i class="fas fa-plus mr-1"></i> ADD TYPE
                                </button>
                            </div>
                            
                            <div id="ng_container_{{ $aw->id }}" style="max-height: 220px; overflow-y: auto; padding-right: 10px;">
                                <div class="text-center text-muted small py-4" id="no_ng_msg_{{ $aw->id }}">
                                    <i class="fas fa-check-circle text-success mb-2 d-block fa-2x"></i>
                                    Perfect Batch: No defects reported yet.
                                </div>
                            </div>

                            <hr class="my-4">
                            <label class="small font-weight-black text-dark uppercase mb-2">Operational Remark</label>
                            <textarea name="keterangan" class="form-control tech-input" rows="2" placeholder="Input any process abnormalities here..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5">
                    <button type="submit" id="btn_submit_{{ $aw->id }}" class="btn btn-success btn-block py-4 font-weight-black rounded-2xl shadow-lg" style="font-size: 1.1rem;">
                        AUTHORIZE & TRANSFER TO WELDING WIP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- 🚀 MODAL DEPLOY --}}
<div class="modal fade" id="modalDeployWelding" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 30px;">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title font-weight-black text-uppercase" style="font-family: 'Orbitron'; letter-spacing: 1px;">Deployment Center</h5>
            </div>
            <form action="{{ route('welding.deploy') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <label class="small font-weight-black text-muted uppercase mb-2">Target Part Identification</label>
                    <select name="part_no" id="part_select" class="form-control tech-input mb-4" required>
                        <option value="" disabled selected>-- SELECT PART --</option>
                        @foreach($inventoryWelding as $inv)
                            <option value="{{ $inv->part_no }}">{{ $inv->part_no }} (Available: {{ $inv->live_stock }})</option>
                        @endforeach
                    </select>

                    <label class="small font-weight-black text-muted uppercase mb-2">Select Target Station</label>
                    <select name="line_id" class="form-control tech-input mb-4" required>
                        <option value="" disabled selected>-- SELECT WELDING LINE --</option>
                        @foreach($weldingLines as $wl)
                            <option value="{{ $wl->id }}">{{ $wl->kode_line }} - {{ $wl->nama_line }}</option>
                        @endforeach
                    </select>

                    <label class="small font-weight-black text-muted uppercase mb-2">Deployment Quantity</label>
                    <input type="number" name="qty_ambil" class="form-control text-center tech-input font-weight-black" 
                           required style="font-size: 42px; height: 100px; color: var(--brand-primary);" placeholder="0">
                </div>
                <div class="modal-footer border-0 p-5">
                    <button type="submit" class="btn btn-primary btn-block py-4 font-weight-black rounded-2xl shadow-xl">CONFIRM DEPLOYMENT</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const listPenyakit = @json($listNG->pluck('ng_name'));

    function quickTake(partNo) {
        document.getElementById('part_select').value = partNo;
        $('#modalDeployWelding').modal('show');
    }

    function addNgRow(batchId) {
        $(`#no_ng_msg_${batchId}`).hide();
        const id = Date.now();
        let options = listPenyakit.map(p => `<option value="${p}">${p.toUpperCase()}</option>`).join('');
        
        const html = `
            <div class="ng-row-item animate__animated animate__fadeInDown" id="row-${id}">
                <div class="row no-gutters align-items-center">
                    <div class="col-7 pr-2">
                        <select name="ng_detail_type[]" class="form-control form-control-sm font-weight-bold border-0 shadow-none bg-transparent" required>
                            ${options}
                        </select>
                    </div>
                    <div class="col-3 pr-2">
                        <input type="number" name="ng_detail_qty[]" class="form-control form-control-sm text-center font-weight-black ng-qty-input tech-input h-auto py-2" 
                        placeholder="0" min="1" required oninput="calculateSecurity(${batchId}, ${$(`#input_ok_${batchId}`).attr('oninput').match(/\d+/g)[1]})">
                    </div>
                    <div class="col-2 text-right">
                        <button type="button" class="btn btn-link text-danger p-0" onclick="removeNgRow(${id}, ${batchId})">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $(`#ng_container_${batchId}`).append(html);
    }

    function removeNgRow(rowId, batchId) {
        $(`#row-${rowId}`).remove();
        const target = parseInt($(`#input_ok_${batchId}`).attr('oninput').match(/\d+/g)[1]);
        calculateSecurity(batchId, target);
        if ($(`#ng_container_${batchId}`).children('.ng-row-item').length === 0) {
            $(`#no_ng_msg_${batchId}`).show();
        }
    }

    // ✨ LOGIKA KEAMANAN DATA (Anti-Manipulasi)
    function calculateSecurity(batchId, target) {
        let ok = parseInt($(`#input_ok_${batchId}`).val()) || 0;
        let totalNg = 0;
        
        $(`#modalFinish${batchId} .ng-qty-input`).each(function() {
            totalNg += parseInt($(this).val()) || 0;
        });

        $(`#total_ng_${batchId}`).val(totalNg);
        
        let grandTotal = ok + totalNg;
        let diff = target - grandTotal;
        let msgBox = $(`#sec_msg_${batchId}`);
        let btnSubmit = $(`#btn_submit_${batchId}`);

        if (grandTotal === target) {
            msgBox.removeClass('status-error').addClass('status-match').html('<i class="fas fa-shield-alt mr-2"></i>DATA_INTEGRITY_MATCHED');
            btnSubmit.prop('disabled', false).css('opacity', '1').css('cursor', 'pointer');
        } else {
            msgBox.removeClass('status-match').addClass('status-error').html(`<i class="fas fa-exclamation-triangle mr-2"></i>ERROR: GAP DETECTED (${diff > 0 ? '+'+diff : diff} Pcs)`);
            btnSubmit.prop('disabled', true).css('opacity', '0.5').css('cursor', 'not-allowed');
        }
    }

    function validateSecurity(batchId, target) {
        let ok = parseInt($(`#input_ok_${batchId}`).val()) || 0;
        let ng = parseInt($(`#total_ng_${batchId}`).val()) || 0;
        if ((ok + ng) !== target) {
            alert("🚨 SECURITY BREACH: Total OK + NG harus sama dengan Target (" + target + "). Silakan perbaiki data!");
            return false;
        }
        return true;
    }
</script>
@endsection