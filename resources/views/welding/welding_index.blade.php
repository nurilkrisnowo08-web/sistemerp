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
    
    /* 📈 LEDGER PLANNING STYLE rill */
    .ledger-container { background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 40px; }
    .table-ledger thead th { background: #fdfdfd; color: #94a3b8; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px; border-bottom: 2px solid #f1f5f9; }
    .table-ledger td { padding: 18px 20px; vertical-align: middle; border-bottom: 1px solid #f8fafc; font-size: 13px; font-weight: 700; }
    
    .col-init { color: #94a3b8; font-family: 'JetBrains Mono'; }
    .col-in { color: var(--brand-success); font-family: 'JetBrains Mono'; background: rgba(16, 185, 129, 0.03); }
    .col-out { color: var(--brand-danger); font-family: 'JetBrains Mono'; background: rgba(239, 68, 68, 0.03); }
    .col-live { background: rgba(67, 97, 238, 0.05); font-weight: 800 !important; color: var(--brand-primary); font-size: 15px !important; border-left: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; }
    
    /* 🛠️ WORK CARDS rill */
    .work-card { background: #fff; border-radius: 24px; border: 1px solid #eef2f6; padding: 24px; margin-bottom: 16px; transition: 0.3s; display: flex; align-items: center; }
    .work-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05); border-color: var(--brand-primary); }
    .qty-display { font-family: 'Orbitron'; font-weight: 800; font-size: 26px; color: var(--dark-surface); line-height: 1; }
    
    /* Input Style Sultan rill */
    .sultan-input { border-radius: 15px; border: 2px solid #f1f5f9; font-weight: 700; transition: 0.3s; }
    .sultan-input:focus { border-color: var(--brand-primary); box-shadow: none; background: #f8faff; }

    @keyframes gearRotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .gear-anim { animation: gearRotate 4s linear infinite; display: inline-block; color: var(--brand-warning); }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding Terminal <span style="-webkit-text-fill-color: var(--dark-surface);">v2.1</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0">
                <i class="fas fa-microchip text-primary mr-2"></i> WIP Control & Quality Verification rill
            </p>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('welding.history') }}" class="btn btn-light rounded-pill px-4 font-weight-extrabold border mr-3 shadow-sm" style="height: 48px; display: flex; align-items: center;">
                <i class="fas fa-history mr-2 text-muted"></i> ARCHIVE VAULT
            </a>

            <button class="btn btn-primary rounded-pill px-4 font-weight-extrabold shadow-lg mr-4" style="height: 48px; background: var(--brand-primary); border:none;" data-toggle="modal" data-target="#modalDeployWelding">
                <i class="fas fa-plus-circle mr-2"></i> DEPLOY ORDER
            </button>
            <div class="bg-white px-4 py-2 rounded-2xl shadow-sm border text-right">
                <small class="text-muted font-weight-bold d-block uppercase" style="font-size: 8px; letter-spacing: 1px;">Shift Date</small>
                <span class="font-weight-bold text-dark" style="font-family: 'JetBrains Mono'; font-size: 14px;">{{ \Carbon\Carbon::parse($date)->format('d . m . Y') }}</span>
            </div>
        </div>
    </div>

    {{-- SECTION 1: PLANNING LEDGER rill --}}
    <div class="ledger-container animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Part Identification</th>
                        <th>STOCK AWAL</th>
                        <th class="text-success">In (Stamping)</th>
                        <th class="text-danger">Out (Take)</th>
                        <th>Live Stock</th>
                        <th>Pallet (Run)</th>
                        <th class="text-right pr-4">Quick Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventoryWelding as $inv)
                    <tr>
                        <td class="text-left pl-4">
                            <div class="font-weight-extrabold text-dark" style="font-size: 14px;">{{ $inv->part_no }}</div>
                            <small class="text-muted uppercase font-weight-bold" style="font-size: 9px;">{{ $inv->part_name }}</small>
                        </td>
                        <td class="col-init">{{ number_format($inv->init) }}</td>
                        <td class="col-in font-weight-bold">+{{ number_format($inv->in_s) }}</td>
                        <td class="col-out font-weight-bold">-{{ number_format($inv->out) }}</td>
                        <td class="col-live">{{ number_format($inv->live_stock) }}</td>
                        <td><span class="badge badge-light border px-3 py-1 font-weight-bold" style="font-family: 'JetBrains Mono'; border-radius: 8px;">{{ $inv->run }}x</span></td>
                        <td class="text-right pr-4">
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-4 font-weight-extrabold" onclick="quickTake('{{ $inv->part_no }}')">TAKE</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-5 text-muted font-weight-bold italic">-- NO DATA FOR CURRENT SHIFT --</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SECTION 2: ACTIVE BATCHES rill --}}
    <div class="pt-nav-container animate__animated animate__fadeInUp">
        <ul class="nav nav-pills" id="ptTab">
            @foreach($availableCustomers as $index => $customer)
            <li class="nav-item">
                <a class="nav-link {{ $index == 0 ? 'active' : '' }}" data-toggle="pill" href="#pt-{{ Str::slug($customer) }}">
                    {{ strtoupper($customer) }}
                </a>
            </li>
            @endforeach
        </ul>
    </div>

    <div class="tab-content" id="ptTabContent">
        @foreach($availableCustomers as $index => $customer)
        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="pt-{{ Str::slug($customer) }}">
            @php $filteredBatches = $activeWelding->where('customer', $customer); @endphp
            @forelse($filteredBatches as $aw)
            <div class="work-card animate__animated animate__fadeInUp shadow-sm">
                <div class="col-md-2 font-weight-extrabold text-primary" style="font-family: 'JetBrains Mono';">
                    <i class="fas fa-qrcode mr-2 opacity-50"></i>{{ $aw->no_produksi_stamping }}
                </div>
                <div class="col-md-4 border-left pl-4">
                    <div class="font-weight-extrabold h6 mb-1 text-dark">{{ $aw->part_no }}</div>
                    <small class="text-muted font-weight-bold uppercase">{{ $aw->part_name }}</small>
                </div>
                <div class="col-md-2 text-center">
                    <div class="qty-display">{{ number_format($aw->qty_masuk) }}</div>
                    <small class="text-muted font-weight-extrabold uppercase" style="font-size: 8px;">Order Qty</small>
                </div>
                <div class="col-md-2 text-center">
                    @if($aw->batch_status == 'PENDING')
                        <span class="badge badge-warning py-2 px-3 rounded-pill font-weight-bold">WAITING</span>
                    @else
                        <span class="badge badge-info py-2 px-3 rounded-pill font-weight-bold animate-pulse"><i class="fas fa-sync-alt fa-spin mr-1"></i> WELDING...</span>
                    @endif
                </div>
                <div class="col-md-2 text-right">
                    @if($aw->batch_status == 'PENDING')
                        <form action="{{ route('welding.start', $aw->id) }}" method="POST">
                            @csrf @method('PUT')
                            <button class="btn btn-primary btn-block font-weight-extrabold py-3" style="border-radius: 16px;">START PROCESS</button>
                        </form>
                    @else
                        <button class="btn btn-success btn-block font-weight-extrabold py-3" style="border-radius: 16px;" data-toggle="modal" data-target="#modalFinish{{ $aw->id }}">FINISH & TRANSFER</button>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-5 bg-white rounded-24 border dashed">
                <p class="text-muted font-weight-bold mb-0">No active batches for {{ $customer }} rill.</p>
            </div>
            @endforelse
        </div>
        @endforeach
    </div>
</div>

{{-- MODAL: QUALITY GATE (FINISH) + NG DESCRIPTION rill --}}
@foreach($activeWelding as $aw)
<div class="modal fade" id="modalFinish{{ $aw->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 32px;">
            <div class="modal-header bg-success text-white p-4">
                <h5 class="modal-title font-weight-extrabold uppercase"><i class="fas fa-check-double mr-3"></i> Quality Gate rill</h5>
            </div>
            <form action="{{ route('welding.finish', $aw->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="bg-light p-4 rounded-24 mb-4 text-center border">
                        <small class="text-muted font-weight-extrabold uppercase">Target Verification:</small>
                        <h2 class="font-weight-extrabold text-dark mb-0" style="font-family: 'Orbitron';">{{ number_format($aw->qty_masuk) }} PCS</h2>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="small font-weight-extrabold text-success uppercase">Qty OK</label>
                            <input type="number" name="qty_ok" class="form-control text-center sultan-input py-4" value="{{ $aw->qty_masuk }}" required style="font-size: 20px;">
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-extrabold text-danger uppercase">Qty NG</label>
                            <input type="number" name="qty_ng" class="form-control text-center sultan-input py-4" value="0" required style="font-size: 20px;">
                        </div>
                    </div>
                    
                    {{-- ✨ NEW: NG DESCRIPTION PROCESS rill --}}
                    <div class="form-group mb-0">
                        <label class="small font-weight-extrabold text-muted uppercase ml-1">NG / Reject Description (Optional)</label>
                        <textarea name="ng_description" class="form-control sultan-input" rows="2" placeholder="Sebutkan alasan jika ada barang NG..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="submit" class="btn btn-success btn-block py-3 font-weight-extrabold rounded-pill shadow-lg">FINALIZE & TRANSFER TO FG</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- MODAL DEPLOY rill --}}
<div class="modal fade" id="modalDeployWelding" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 32px;">
            <div class="modal-header bg-dark text-white p-4"><h5 class="modal-title font-weight-extrabold uppercase">WIP Deployment rill</h5></div>
            <form action="{{ route('welding.deploy') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-4">
                        <label class="small font-weight-extrabold text-muted uppercase">Part Identification</label>
                        <select name="part_no" id="part_select" class="form-control sultan-input" style="height: 55px;">
                            @foreach($inventoryWelding as $inv)
                                <option value="{{ $inv->part_no }}">{{ $inv->part_no }} (STOCK: {{ $inv->live_stock }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0 text-center">
                        <label class="small font-weight-extrabold text-muted uppercase">Quantity to Deploy</label>
                        <input type="number" name="qty_ambil" class="form-control text-center sultan-input" required style="font-size: 32px; height: 80px;" placeholder="0">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4"><button type="submit" class="btn btn-primary btn-block py-3 font-weight-extrabold rounded-pill shadow-lg">DEPLOY TO TERMINAL</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function quickTake(partNo) {
        $('#part_select').val(partNo);
        $('#modalDeployWelding').modal('show');
    }
</script>
@endsection