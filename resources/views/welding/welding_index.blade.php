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
    
    /* 📊 LEDGER PLANNING rill */
    .ledger-container { background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 40px; }
    .table-ledger thead th { background: #fdfdfd; color: #94a3b8; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px; border-bottom: 2px solid #f1f5f9; }
    .table-ledger td { padding: 18px 20px; vertical-align: middle; border-bottom: 1px solid #f8fafc; font-size: 13px; font-weight: 700; }
    .col-live { background: rgba(67, 97, 238, 0.05); font-weight: 800 !important; color: var(--brand-primary); font-size: 16px !important; border-left: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; }
    
    /* 🛠️ WORK CARDS rill */
    .work-card { background: #fff; border-radius: 24px; border: 1px solid #eef2f6; padding: 24px; margin-bottom: 16px; transition: 0.3s; display: flex; align-items: center; position: relative; }
    .work-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px rgba(0,0,0,0.05); border-color: var(--brand-primary); }
    .qty-display { font-family: 'Orbitron'; font-weight: 800; font-size: 28px; color: var(--dark-surface); line-height: 1; }
    
    /* 📑 TAB NAVIGATION rill */
    .nav-pills .nav-link { border-radius: 14px; padding: 12px 25px; font-weight: 700; color: #64748b; font-size: 11px; transition: 0.3s; border: 1px solid transparent; }
    .nav-pills .nav-link.active { background-color: var(--dark-surface); color: #fff; box-shadow: 0 10px 15px rgba(15, 23, 42, 0.2); }
    .badge-count { background: var(--brand-primary); color: white; border-radius: 50px; padding: 2px 8px; font-size: 10px; margin-left: 8px; font-family: 'JetBrains Mono'; }

    .sultan-input { border-radius: 15px; border: 2px solid #f1f5f9; font-weight: 700; transition: 0.3s; height: 55px; }
    .sultan-input:focus { border-color: var(--brand-primary); box-shadow: none; background: #f8faff; }

    @media (max-width: 768px) {
        .work-card { flex-direction: column; text-align: center; gap: 15px; padding: 20px; }
        .qty-display { font-size: 32px; }
    }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    {{-- 🛸 HEADER HUB rill --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding Terminal <span style="-webkit-text-fill-color: var(--dark-surface);">v2.5</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0">
                <i class="fas fa-microchip text-primary mr-2"></i> WIP Control & Quality Verification rill
            </p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
            {{-- 🏰 ARCHIVE VAULT BALIK RILL! --}}
            <a href="{{ route('welding.history') }}" class="btn btn-light rounded-pill px-4 font-weight-extrabold border mr-2 shadow-sm">
                <i class="fas fa-history mr-2 text-muted"></i> VAULT
            </a>
            <button class="btn btn-primary rounded-pill px-4 font-weight-extrabold shadow-lg mr-2" style="background: var(--brand-primary); border:none;" data-toggle="modal" data-target="#modalDeployWelding">
                <i class="fas fa-plus-circle mr-1"></i> DEPLOY
            </button>
            <div class="bg-white px-4 py-2 rounded-2xl shadow-sm border text-right">
                <small class="text-muted font-weight-bold d-block uppercase" style="font-size: 8px;">Shift Date</small>
                <span class="font-weight-bold text-dark" style="font-family: 'JetBrains Mono'; font-size: 14px;">{{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-lg p-3 mb-4" style="border-radius: 15px; background: var(--brand-success); color: white;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }} rill!
        </div>
    @endif

    {{-- 📊 LEDGER TABLE rill --}}
    <div class="ledger-container animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Identification</th>
                        <th>START</th>
                        <th class="text-success">IN</th>
                        <th class="text-danger">OUT</th>
                        <th class="col-live">LIVE STOCK</th>
                        <th>RUN</th>
                        <th class="text-right pr-4">COMMAND</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventoryWelding as $inv)
                    <tr>
                        <td class="text-left pl-4">
                            <div class="font-weight-extrabold text-dark">{{ $inv->part_no }}</div>
                            <small class="text-muted font-weight-bold uppercase" style="font-size: 9px;">{{ $inv->part_name }}</small>
                        </td>
                        <td style="font-family: 'JetBrains Mono'; color: #94a3b8;">{{ number_format($inv->init) }}</td>
                        <td class="text-success font-weight-bold">+{{ number_format($inv->in_s) }}</td>
                        <td class="text-danger font-weight-bold">-{{ number_format($inv->out) }}</td>
                        <td class="col-live">{{ number_format($inv->live_stock) }}</td>
                        <td><span class="badge badge-light border px-2 py-1 font-weight-bold">{{ $inv->run }}x</span></td>
                        <td class="text-right pr-4">
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-4 font-weight-extrabold" onclick="quickTake('{{ trim($inv->part_no) }}')">TAKE rill</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 📑 TAB PT rill --}}
    <div class="pt-nav-container animate__animated animate__fadeInUp">
        <ul class="nav nav-pills" id="ptTab">
            @foreach($availableCustomers as $index => $customer)
            @php 
                $count = $activeWelding->where('customer', $customer)->count(); 
                $slugPT = Str::slug($customer);
            @endphp
            <li class="nav-item">
                <a class="nav-link {{ $index == 0 ? 'active' : '' }}" data-toggle="pill" href="#pt-{{ $slugPT }}">
                    {{ strtoupper($customer) }} @if($count > 0) <span class="badge-count">{{ $count }}</span> @endif
                </a>
            </li>
            @endforeach
        </ul>
    </div>

    {{-- 🛠️ WORK CARDS rill --}}
    <div class="tab-content" id="ptTabContent">
        @foreach($availableCustomers as $index => $customer)
        @php $slugPT = Str::slug($customer); @endphp
        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="pt-{{ $slugPT }}">
            @forelse($activeWelding->where('customer', $customer) as $aw)
            <div class="work-card animate__animated animate__fadeInUp shadow-sm">
                <div class="col-md-2 font-weight-extrabold text-primary" style="font-family: 'JetBrains Mono'; font-size: 15px;">
                    <i class="fas fa-qrcode mr-2 opacity-50"></i>{{ $aw->no_produksi_stamping }}
                </div>
                <div class="col-md-4 border-left pl-4">
                    <div class="font-weight-extrabold h6 mb-0 text-dark">{{ $aw->part_no }}</div>
                    <small class="text-muted font-weight-bold uppercase">{{ $aw->part_name }}</small>
                </div>
                <div class="col-md-2 text-center">
                    <div class="qty-display">{{ number_format($aw->qty_masuk) }}</div>
                    <small class="text-muted font-weight-extrabold" style="font-size: 8px;">ORDER QTY</small>
                </div>
                <div class="col-md-2 text-center">
                    @if($aw->batch_status == 'PENDING')
                        <span class="badge badge-warning py-2 px-3 rounded-pill font-weight-bold animate-pulse">WAITING</span>
                    @else
                        <span class="badge badge-info py-2 px-3 rounded-pill font-weight-bold animate-pulse"><i class="fas fa-sync-alt fa-spin mr-1"></i> WELDING...</span>
                    @endif
                </div>
                <div class="col-md-2 text-right">
                    @if($aw->batch_status == 'PENDING')
                        <form action="{{ route('welding.start', $aw->id) }}" method="POST">
                            @csrf @method('PUT')
                            <button class="btn btn-primary btn-block font-weight-extrabold py-3 shadow" style="border-radius: 16px;">START PROCESS</button>
                        </form>
                    @else
                        <button class="btn btn-success btn-block font-weight-extrabold py-3 shadow" style="border-radius: 16px;" data-toggle="modal" data-target="#modalFinish{{ $aw->id }}">FINISH & TRANSFER</button>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-5 bg-white rounded-24 border border-dashed">
                <p class="text-muted font-weight-bold mb-0">No active process for this client rill.</p>
            </div>
            @endforelse
        </div>
        @endforeach
    </div>
</div>

{{-- 🏁 MODAL FINISH rill --}}
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
                        <small class="text-muted font-weight-bold uppercase">Target Qty:</small>
                        <h2 class="font-weight-extrabold text-dark mb-0" style="font-family: 'Orbitron';">{{ number_format($aw->qty_masuk) }} PCS</h2>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="small font-weight-bold text-success uppercase">Qty OK</label>
                            <input type="number" name="qty_ok" class="form-control text-center sultan-input" value="{{ $aw->qty_masuk }}" required>
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold text-danger uppercase">Qty NG</label>
                            <input type="number" name="qty_ng" class="form-control text-center sultan-input" value="0" required>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-muted uppercase">NG Description (Reason rill)</label>
                        <textarea name="ng_description" class="form-control sultan-input" rows="2" placeholder="Sebutkan alasan jika ada reject..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="submit" class="btn btn-success btn-block py-3 font-weight-extrabold rounded-pill shadow-lg">TRANSFER TO FG rill</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- 🚀 MODAL DEPLOY rill --}}
<div class="modal fade" id="modalDeployWelding" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 32px;">
            <div class="modal-header bg-dark text-white p-4"><h5 class="modal-title font-weight-extrabold uppercase">WIP Deployment rill</h5></div>
            <form action="{{ route('welding.deploy') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-muted uppercase">Select Part</label>
                        <select name="part_no" id="part_select" class="form-control sultan-input" required>
                            <option value="" disabled selected>-- CHOOSE COMPONENT --</option>
                            @foreach($inventoryWelding as $inv)
                                <option value="{{ trim($inv->part_no) }}">{{ $inv->part_no }} (LIVE: {{ $inv->live_stock }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group text-center mb-0">
                        <label class="small font-weight-bold text-muted uppercase">Quantity to Take</label>
                        <input type="number" name="qty_ambil" class="form-control text-center sultan-input" required style="font-size: 32px; height: 80px;" placeholder="0">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4"><button type="submit" class="btn btn-primary btn-block py-3 font-weight-extrabold rounded-pill shadow-lg">DEPLOY TO TERMINAL rill</button></div>
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