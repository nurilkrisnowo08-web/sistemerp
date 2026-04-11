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
    
    /* 📈 LEDGER PLANNING STYLE */
    .ledger-container { background: #fff; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 40px; }
    .table-ledger thead th { background: #f1f5f9; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; padding: 15px 20px; border: none; }
    .table-ledger td { padding: 15px 20px; vertical-align: middle; border-bottom: 1px solid #f8fafc; font-size: 13px; font-weight: 600; }
    .col-init { color: #94a3b8; }
    .col-in { color: var(--brand-success); }
    .col-out { color: var(--brand-danger); }
    .col-live { background: #f8faff; font-weight: 800 !important; color: var(--brand-primary); border-left: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; }
    
    .pt-nav-container { background: #fff; padding: 10px; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-bottom: 30px; }
    .nav-pills .nav-link { border-radius: 14px; padding: 12px 28px; font-weight: 700; color: #64748b; text-transform: uppercase; font-size: 11px; transition: 0.3s; }
    .nav-pills .nav-link.active { background-color: var(--brand-primary); color: #fff; box-shadow: 0 10px 15px -3px rgba(67, 97, 238, 0.3); }
    
    .work-card { background: #fff; border-radius: 24px; border: 1px solid #eef2f6; padding: 24px; margin-bottom: 16px; transition: 0.3s; display: flex; align-items: center; }
    .work-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05); border-color: var(--brand-primary); }
    .qty-display { font-family: 'Orbitron'; font-weight: 700; font-size: 24px; color: var(--dark-surface); }
    .status-indicator { display: inline-flex; align-items: center; padding: 6px 16px; border-radius: 99px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
</style>

<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding Terminal <span style="-webkit-text-fill-color: var(--dark-surface);">v2.0</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0"><i class="fas fa-chart-line text-primary mr-2"></i> WIP Inventory & Planning Ledger</p>
        </div>
        <div class="d-flex align-items-center">
            <button class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-sm mr-3" data-toggle="modal" data-target="#modalDeployWelding">
                <i class="fas fa-plus-circle mr-2"></i> NEW WELDING ORDER
            </button>
            <div class="bg-white px-4 py-2 rounded-2xl shadow-sm border text-right">
                <small class="text-muted font-weight-bold d-block uppercase" style="font-size: 9px;">Operational Date</small>
                <span class="font-weight-bold text-dark" style="font-family: 'JetBrains Mono';">{{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-lg animate__animated animate__slideInDown p-3 mb-4" style="border-radius: 15px;">
            <b class="small uppercase">Success:</b> &nbsp; {{ session('success') }}
        </div>
    @endif

    {{-- ✨ SECTION 1: PLANNING LEDGER (GAYA image_265017.png) ✨ --}}
    <div class="ledger-container animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4" style="width: 30%;">Identification (Alias & Name)</th>
                        <th style="width: 10%;">Init</th>
                        <th class="text-success" style="width: 10%;">In(S)</th>
                        <th class="text-danger" style="width: 10%;">Out</th>
                        <th class="bg-light" style="width: 15%;">Live</th>
                        <th style="width: 10%;">Run</th>
                        <th class="text-right pr-4" style="width: 15%;">Act</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventoryWelding as $inv)
                    <tr>
                        <td class="text-left pl-4">
                            <div class="font-weight-bold text-dark">{{ $inv->part_no }}</div>
                            <small class="text-muted uppercase" style="font-size: 9px;">{{ $inv->part_name }}</small>
                        </td>
                        <td class="col-init">{{ number_format($inv->init) }}</td>
                        <td class="col-in font-weight-bold">+{{ number_format($inv->in_s) }}</td>
                        <td class="col-out font-weight-bold">-{{ number_format($inv->out) }}</td>
                        <td class="col-live text-primary" style="font-size: 16px;">{{ number_format($inv->live_stock) }}</td>
                        <td>
                            <span class="badge badge-light border px-2 py-1" style="font-family: 'JetBrains Mono';">{{ $inv->run }}x</span>
                        </td>
                        <td class="text-right pr-4">
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-3 font-weight-bold" onclick="quickTake('{{ $inv->part_no }}')">
                                <i class="fas fa-hand-holding mr-1"></i> TAKE
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-5 text-muted italic">-- NO PLANNING DATA AVAILABLE --</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SECTION 2: LIVE PROCESS CARDS --}}
    <div class="pt-nav-container animate__animated animate__fadeInUp">
        <ul class="nav nav-pills" id="ptTab" role="tablist">
            @foreach($availableCustomers as $index => $customer)
            <li class="nav-item">
                <a class="nav-link {{ $index == 0 ? 'active' : '' }}" data-toggle="pill" href="#pt-{{ Str::slug($customer) }}">
                    {{ $customer }}
                </a>
            </li>
            @endforeach
        </ul>
    </div>

    <div class="tab-content" id="ptTabContent">
        @foreach($availableCustomers as $index => $customer)
        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="pt-{{ Str::slug($customer) }}">
            @foreach($activeWelding->where('customer', $customer) as $aw)
            <div class="work-card animate__animated animate__fadeInUp mb-3 border shadow-sm">
                <div class="col-md-2 font-weight-bold text-primary" style="font-family: 'JetBrains Mono';">{{ $aw->no_produksi_stamping }}</div>
                <div class="col-md-4">
                    <div class="font-weight-bold h6 mb-0">{{ $aw->part_no }}</div>
                    <small class="text-muted">{{ $aw->part_name }}</small>
                </div>
                <div class="col-md-2 text-center">
                    <div class="qty-display">{{ number_format($aw->qty_masuk) }}</div>
                    <small class="text-muted font-weight-bold uppercase" style="font-size: 8px;">ORDER QTY</small>
                </div>
                <div class="col-md-2 text-center">
                    @if($aw->batch_status == 'PENDING')
                        <span class="status-indicator status-waiting">Waiting</span>
                    @else
                        <span class="status-indicator status-processing"><i class="fas fa-sync fa-spin mr-1"></i> In-Progress</span>
                    @endif
                </div>
                <div class="col-md-2 text-right">
                    @if($aw->batch_status == 'PENDING')
                        <form action="{{ route('welding.start', $aw->id) }}" method="POST">
                            @csrf @method('PUT')
                            <button class="btn btn-primary btn-block font-weight-bold py-2" style="border-radius: 12px;">START</button>
                        </form>
                    @else
                        <button class="btn btn-success btn-block font-weight-bold py-2" style="border-radius: 12px;" data-toggle="modal" data-target="#modalFinish{{ $aw->id }}">FINISH</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>

{{-- MODAL: TAKE FROM STOCK --}}
<div class="modal fade" id="modalDeployWelding" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="modal-title font-weight-bold uppercase"><i class="fas fa-dolly mr-2"></i> Take from WIP Stock</h6>
            </div>
            <form action="{{ route('welding.deploy') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <label class="small font-weight-bold text-muted">SELECT PART:</label>
                    <select name="part_no" id="part_select" class="form-control mb-3" required style="height: 45px; border-radius: 10px; font-weight: 700;">
                        <option value="" disabled selected>-- SELECT --</option>
                        @foreach($inventoryWelding as $inv)
                            <option value="{{ $inv->part_no }}">{{ $inv->part_no }} (Available: {{ $inv->live_stock }})</option>
                        @endforeach
                    </select>
                    <label class="small font-weight-bold text-muted">QTY TO WELD:</label>
                    <input type="number" name="qty_ambil" class="form-control text-center font-weight-bold" required style="font-size: 24px; height: 60px; border-radius: 12px;">
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold shadow">DEPLOY ORDER</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: FINISH GATE (START FROM 0) --}}
@foreach($activeWelding as $aw)
<div class="modal fade" id="modalFinish{{ $aw->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h6 class="modal-title font-weight-bold uppercase">Quality Gate // Batch Finish</h6>
            </div>
            <form action="{{ route('welding.finish', $aw->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="bg-light p-3 rounded mb-4 text-center border">
                        <small class="text-muted font-weight-bold uppercase">Work Target:</small>
                        <h3 class="font-weight-bold text-primary mb-0">{{ number_format($aw->qty_masuk) }} PCS</h3>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="small font-weight-bold text-success uppercase">OK Qty</label>
                            <input type="number" name="qty_ok" class="form-control text-center font-weight-bold" value="0" required style="font-size: 20px;">
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold text-danger uppercase">NG Qty</label>
                            <input type="number" name="qty_ng" class="form-control text-center font-weight-bold" value="0" required style="font-size: 20px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-success btn-block py-3 font-weight-bold">PUSH TO FINISHED GOODS</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function quickTake(partNo) {
        $('#part_select').val(partNo);
        $('#modalDeployWelding').modal('show');
    }
</script>
@endsection