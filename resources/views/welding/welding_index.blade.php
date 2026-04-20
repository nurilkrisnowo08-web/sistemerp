@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-warning: #f59e0b; --dark-surface: #0f172a; --bg-main: #f1f5f9;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #334155; }
    
    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(135deg, var(--brand-primary), #7209b7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    /* 📈 LEDGER TABLE */
    .ledger-container { background: #fff; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); margin-bottom: 30px; }
    .table-ledger thead th { background: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; padding: 15px; border-bottom: 2px solid #edf2f7; }
    .table-ledger td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; }
    
    /* 🏷️ PT NAVIGATION SULTAN (FIX SAMAR RILL!) */
    .nav-section { background: #fff; padding: 18px; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 25px; border: 1px solid #e2e8f0; }
    
    .nav-pills .nav-link { 
        border-radius: 12px; padding: 12px 24px; font-weight: 800; 
        transition: 0.3s; margin-right: 12px;
        /* Inactive State: Biar Gak Samar rill! */
        background: #f1f5f9; 
        color: #475569 !important; 
        border: 2px solid #e2e8f0;
        display: flex; align-items: center;
    }
    
    .nav-pills .nav-link.active { 
        /* Active State: Galak rill! */
        background: var(--dark-surface) !important; 
        color: #fff !important; 
        border-color: var(--brand-primary);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.3);
    }

    .nav-pills .nav-link:hover:not(.active) { 
        background: #e2e8f0; 
        border-color: #cbd5e1; 
    }

    /* 🔴 NOTIF BADGE rill */
    .count-badge {
        background: var(--brand-danger); color: white; border-radius: 8px;
        padding: 2px 8px; font-size: 10px; margin-left: 10px; font-family: 'JetBrains Mono';
        box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
    }

    /* 🛠️ WORK CARDS */
    .work-card { 
        background: #fff; border-radius: 24px; border: 1px solid #eef2f6; 
        padding: 24px; margin-bottom: 16px; transition: 0.3s; 
        display: flex; align-items: center; position: relative;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
    .work-card:hover { transform: scale(1.01); box-shadow: 0 15px 30px rgba(0,0,0,0.08); border-color: var(--brand-primary); }
    .qty-display { font-family: 'Orbitron'; font-weight: 800; font-size: 32px; color: var(--dark-surface); line-height: 1; }

    .btn-action-rill { border-radius: 15px; font-weight: 900; letter-spacing: 0.5px; transition: 0.3s; padding: 12px 25px; }

    .sultan-input { border-radius: 15px; border: 2px solid #f1f5f9; font-weight: 700; transition: 0.3s; }
    .sultan-input:focus { border-color: var(--brand-primary); box-shadow: none; background: #f8faff; }

    @media (max-width: 768px) {
        .work-card { flex-direction: column; text-align: center; gap: 15px; }
        .col-md-2, .col-md-4 { width: 100% !important; border: none !important; }
    }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    {{-- 🛸 HEADER HUB --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding Terminal <span style="-webkit-text-fill-color: var(--dark-surface);">v3.0</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0">
                <i class="fas fa-microchip text-primary mr-2"></i> WIP Control & Batch Management rill
            </p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
            <a href="{{ route('welding.history') }}" class="btn btn-white rounded-pill px-4 font-weight-extrabold border mr-2 shadow-sm">
                <i class="fas fa-history mr-2"></i> VAULT
            </a>
            <button class="btn btn-primary rounded-pill px-4 font-weight-extrabold shadow-lg mr-2" style="background: var(--brand-primary); border:none;" data-toggle="modal" data-target="#modalDeployWelding">
                <i class="fas fa-plus-circle mr-1"></i> DEPLOY
            </button>
            <div class="bg-white px-4 py-2 rounded-2xl shadow-sm border border-primary">
                <small class="text-muted font-weight-bold d-block uppercase" style="font-size: 8px;">Shift Date</small>
                <span class="font-weight-bold text-primary" style="font-family: 'JetBrains Mono'; font-size: 14px;">{{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</span>
            </div>
        </div>
    </div>

    {{-- 📊 LEDGER TABLE --}}
    <div class="ledger-container animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Identification</th>
                        <th>START</th>
                        <th class="text-success">IN</th>
                        <th class="text-danger">OUT</th>
                        <th class="text-primary">LIVE STOCK</th>
                        <th>RUN</th>
                        <th class="text-right pr-4">COMMAND</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventoryWelding as $inv)
                    <tr>
                        <td class="text-left pl-4">
                            <div class="font-weight-extrabold text-dark" style="font-size: 14px;">{{ $inv->part_no }}</div>
                            <small class="text-muted font-weight-bold text-uppercase" style="font-size: 9px;">{{ $inv->part_name }}</small>
                        </td>
                        <td style="color: #94a3b8; font-family: 'JetBrains Mono';">{{ number_format($inv->init) }}</td>
                        <td class="text-success font-weight-bold">+{{ number_format($inv->in_s) }}</td>
                        <td class="text-danger font-weight-bold">-{{ number_format($inv->out) }}</td>
                        <td class="text-primary font-weight-extrabold" style="font-size: 16px;">{{ number_format($inv->live_stock) }}</td>
                        <td><span class="badge badge-light border px-2 py-1 font-weight-bold">{{ $inv->run }}x</span></td>
                        <td class="text-right pr-4">
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-4 font-weight-bold" onclick="quickTake('{{ trim($inv->part_no) }}')">TAKE rill</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 📑 PT NAVIGATION WITH BADGES --}}
    <div class="nav-section animate__animated animate__fadeInUp">
        <h6 class="font-weight-bold text-uppercase text-muted mb-3" style="font-size: 10px; letter-spacing: 1px; padding-left: 5px;">Select Customer Line:</h6>
        <ul class="nav nav-pills" id="ptTab">
            @foreach($availableCustomers as $index => $customer)
            @php 
                $count = $activeWelding->where('customer', $customer)->count(); 
                $slugPT = Str::slug($customer);
            @endphp
            <li class="nav-item">
                <a class="nav-link {{ $index == 0 ? 'active' : '' }}" data-toggle="pill" href="#pt-{{ $slugPT }}">
                    <i class="fas fa-industry mr-2"></i> {{ strtoupper($customer) }}
                    @if($count > 0)
                        <span class="count-badge">{{ $count }}</span>
                    @endif
                </a>
            </li>
            @endforeach
        </ul>
    </div>

    {{-- 🛠️ WORK CARDS --}}
    <div class="tab-content" id="ptTabContent">
        @foreach($availableCustomers as $index => $customer)
        @php $slugPT = Str::slug($customer); @endphp
        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="pt-{{ $slugPT }}">
            @php $filtered = $activeWelding->where('customer', $customer); @endphp
            @forelse($filtered as $aw)
            <div class="work-card animate__animated animate__fadeInUp shadow-sm">
                <div class="col-md-2 font-weight-extrabold text-primary" style="font-family: 'JetBrains Mono'; font-size: 14px;">
                    <i class="fas fa-barcode mr-2 opacity-50"></i>{{ $aw->no_produksi_stamping }}
                </div>
                <div class="col-md-4 border-left pl-4">
                    <div class="font-weight-extrabold h5 mb-0 text-dark">{{ $aw->part_no }}</div>
                    <small class="text-muted font-weight-bold text-uppercase">{{ $aw->part_name }}</small>
                </div>
                <div class="col-md-2 text-center">
                    <div class="qty-display">{{ number_format($aw->qty_masuk) }}</div>
                    <small class="text-muted font-weight-extrabold" style="font-size: 9px;">DEPLOYED QTY</small>
                </div>
                <div class="col-md-2 text-center">
                    @if($aw->batch_status == 'PENDING')
                        <span class="badge badge-warning py-2 px-3 rounded-pill font-weight-bold"><i class="fas fa-hourglass-half mr-1"></i> WAITING</span>
                    @else
                        <span class="badge badge-info py-2 px-3 rounded-pill font-weight-bold animate-pulse"><i class="fas fa-sync-alt fa-spin mr-1"></i> IN PROCESS</span>
                    @endif
                </div>
                <div class="col-md-2 text-right">
                    @if($aw->batch_status == 'PENDING')
                        <form action="{{ route('welding.start', $aw->id) }}" method="POST">
                            @csrf @method('PUT')
                            <button class="btn btn-primary btn-block btn-action-rill shadow-lg">START PROCESS</button>
                        </form>
                    @else
                        <button class="btn btn-success btn-block btn-action-rill shadow-lg" data-toggle="modal" data-target="#modalFinish{{ $aw->id }}">FINISH & TRANSFER</button>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-5 bg-white rounded-24 border-2 border-dashed">
                <i class="fas fa-box-open fa-3x text-light mb-3"></i>
                <p class="text-muted font-weight-bold mb-0">No active batches for {{ $customer }} rill.</p>
            </div>
            @endforelse
        </div>
        @endforeach
    </div>
</div>

{{-- MODALS TETEP SAMA RILL --}}
@foreach($activeWelding as $aw)
<div class="modal fade" id="modalFinish{{ $aw->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 30px;">
            <div class="modal-header bg-success text-white p-4"><h5 class="modal-title font-weight-extrabold uppercase">Quality Gate rill</h5></div>
            <form action="{{ route('welding.finish', $aw->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4 text-center">
                    <h2 class="font-weight-extrabold text-dark mb-4" style="font-family: 'Orbitron';">{{ number_format($aw->qty_masuk) }} PCS</h2>
                    <div class="row">
                        <div class="col-6">
                            <label class="small font-weight-bold text-success uppercase">Qty OK</label>
                            <input type="number" name="qty_ok" class="form-control text-center sultan-input" value="{{ $aw->qty_masuk }}" required>
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold text-danger uppercase">Qty NG</label>
                            <input type="number" name="qty_ng" class="form-control text-center sultan-input" value="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4"><button type="submit" class="btn btn-success btn-block py-3 font-weight-extrabold rounded-pill shadow-lg">TRANSFER TO FG rill</button></div>
            </form>
        </div>
    </div>
</div>
@endforeach

<div class="modal fade" id="modalDeployWelding" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 30px;">
            <div class="modal-header bg-dark text-white p-4"><h5 class="modal-title font-weight-extrabold uppercase">Batch Deployment rill</h5></div>
            <form action="{{ route('welding.deploy') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <select name="part_no" id="part_select" class="form-control sultan-input mb-4" required>
                        <option value="" disabled selected>-- CHOOSE PART --</option>
                        @foreach($inventoryWelding as $inv)
                            <option value="{{ $inv->part_no }}">{{ $inv->part_no }} (STOCK: {{ $inv->live_stock }})</option>
                        @endforeach
                    </select>
                    <input type="number" name="qty_ambil" class="form-control text-center sultan-input" required style="font-size: 32px; height: 80px;" placeholder="0">
                </div>
                <div class="modal-footer border-0 p-4"><button type="submit" class="btn btn-primary btn-block py-3 font-weight-extrabold rounded-pill shadow-lg">DEPLOY TO TERMINAL rill</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function quickTake(partNo) {
        document.getElementById('part_select').value = partNo;
        $('#modalDeployWelding').modal('show');
    }
</script>
@endsection