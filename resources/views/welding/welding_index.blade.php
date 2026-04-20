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
    .ledger-container { background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 40px; }
    .table-ledger td { padding: 18px 20px; vertical-align: middle; border-bottom: 1px solid #f8fafc; font-size: 13px; font-weight: 700; }
    .work-card { background: #fff; border-radius: 24px; border: 1px solid #eef2f6; padding: 24px; margin-bottom: 16px; transition: 0.3s; display: flex; align-items: center; }
    .qty-display { font-family: 'Orbitron'; font-weight: 800; font-size: 26px; color: var(--dark-surface); line-height: 1; }
    .badge-count { background: var(--brand-primary); color: white; border-radius: 50px; padding: 2px 8px; font-size: 10px; margin-left: 8px; font-family: 'JetBrains Mono'; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding Terminal <span style="-webkit-text-fill-color: var(--dark-surface);">v2.3</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0">WIP Control & Performance Hub rill</p>
        </div>
        <div class="d-flex align-items-center">
            <button class="btn btn-primary rounded-pill px-4 font-weight-extrabold shadow-lg mr-3" data-toggle="modal" data-target="#modalDeployWelding">
                <i class="fas fa-plus-circle mr-2"></i> DEPLOY ORDER
            </button>
            <div class="bg-white px-4 py-2 rounded-2xl shadow-sm border text-right">
                <small class="text-muted font-weight-bold d-block uppercase" style="font-size: 8px;">Date</small>
                <span class="font-weight-bold text-dark" style="font-family: 'JetBrains Mono'; font-size: 14px;">{{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</span>
            </div>
        </div>
    </div>

    {{-- LEDGER --}}
    <div class="ledger-container animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Part Identification</th>
                        <th>START</th>
                        <th class="text-success">IN</th>
                        <th class="text-danger">OUT</th>
                        <th>LIVE</th>
                        <th class="text-right pr-4">COMMAND</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventoryWelding as $inv)
                    <tr>
                        <td class="text-left pl-4">
                            <div class="font-weight-extrabold text-dark">{{ $inv->part_no }}</div>
                            <small class="text-muted font-weight-bold">{{ $inv->part_name }}</small>
                        </td>
                        <td>{{ number_format($inv->init) }}</td>
                        <td class="text-success">+{{ number_format($inv->in_s) }}</td>
                        <td class="text-danger">-{{ number_format($inv->out) }}</td>
                        <td class="text-primary font-weight-bold">{{ number_format($inv->live_stock) }}</td>
                        <td class="text-right pr-4">
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-4" onclick="quickTake('{{ $inv->part_no }}')">TAKE</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABS --}}
    <div class="pt-nav-container">
        <ul class="nav nav-pills" id="ptTab">
            @foreach($availableCustomers as $index => $customer)
            @php $count = $activeWelding->where('customer', $customer)->count(); @endphp
            <li class="nav-item">
                <a class="nav-link {{ $index == 0 ? 'active' : '' }}" data-toggle="pill" href="#pt-{{ Str::slug($customer) }}">
                    {{ strtoupper($customer) }} @if($count > 0) <span class="badge-count">{{ $count }}</span> @endif
                </a>
            </li>
            @endforeach
        </ul>
    </div>

    {{-- WORK LIST --}}
    <div class="tab-content" id="ptTabContent">
        @foreach($availableCustomers as $index => $customer)
        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="pt-{{ Str::slug($customer) }}">
            @foreach($activeWelding->where('customer', $customer) as $aw)
            <div class="work-card animate__animated animate__fadeInUp shadow-sm">
                <div class="col-md-2 font-weight-extrabold text-primary" style="font-family: 'JetBrains Mono';">{{ $aw->no_produksi_stamping }}</div>
                <div class="col-md-4 border-left pl-4">
                    <div class="font-weight-extrabold h6 mb-1 text-dark">{{ $aw->part_no }}</div>
                    <small class="text-muted font-weight-bold">{{ $aw->part_name }}</small>
                </div>
                <div class="col-md-2 text-center">
                    <div class="qty-display">{{ number_format($aw->qty_masuk) }}</div>
                    <small class="text-muted font-weight-extrabold">QTY TAKE</small>
                </div>
                <div class="col-md-2 text-center">
                    @if($aw->batch_status == 'PENDING')
                        <span class="badge badge-warning py-2 px-3 rounded-pill font-weight-bold">WAITING rill</span>
                    @else
                        <span class="badge badge-info py-2 px-3 rounded-pill font-weight-bold animate-pulse">WELDING...</span>
                    @endif
                </div>
                <div class="col-md-2 text-right">
                    @if($aw->batch_status == 'PENDING')
                        <form action="{{ route('welding.start', $aw->id) }}" method="POST" class="mb-2">
                            @csrf @method('PUT')
                            <button class="btn btn-primary btn-block font-weight-extrabold py-2" style="border-radius: 12px;">START</button>
                        </form>
                        
                        {{-- ✨ FIX CANCEL: Pake URL langsung & POST biar anti 404 rill! --}}
                        <form action="{{ url('/inventory-welding/cancel-deploy/' . $aw->id) }}" method="POST" onsubmit="return confirm('Batalin rill?')">
                            @csrf
                            <button type="submit" class="btn btn-link text-danger btn-sm p-0 font-weight-bold text-decoration-none">
                                <i class="fas fa-undo-alt mr-1"></i> CANCEL DEPLOY
                            </button>
                        </form>
                    @else
                        <button class="btn btn-success btn-block font-weight-extrabold py-3" style="border-radius: 16px;" data-toggle="modal" data-target="#modalFinish{{ $aw->id }}">FINISH & TRANSFER</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>

{{-- MODALS --}}
@foreach($activeWelding as $aw)
<div class="modal fade" id="modalFinish{{ $aw->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 32px;">
            <div class="modal-header bg-success text-white p-4"><h5 class="modal-title font-weight-extrabold uppercase">Quality Gate rill</h5></div>
            <form action="{{ route('welding.finish', $aw->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4 text-center">
                    <h2 class="font-weight-extrabold text-dark mb-4" style="font-family: 'Orbitron';">{{ number_format($aw->qty_masuk) }} PCS</h2>
                    <div class="row">
                        <div class="col-6">
                            <label class="small font-weight-bold text-success uppercase">Qty OK</label>
                            <input type="number" name="qty_ok" class="form-control text-center py-3" value="{{ $aw->qty_masuk }}" required style="border-radius: 15px;">
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold text-danger uppercase">Qty NG</label>
                            <input type="number" name="qty_ng" class="form-control text-center py-3" value="0" required style="border-radius: 15px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4"><button type="submit" class="btn btn-success btn-block py-3 font-weight-extrabold rounded-pill">TRANSFER TO FG rill</button></div>
            </form>
        </div>
    </div>
</div>
@endforeach

<div class="modal fade" id="modalDeployWelding" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 32px;">
            <div class="modal-header bg-dark text-white p-4"><h5 class="modal-title font-weight-extrabold uppercase">Deployment rill</h5></div>
            <form action="{{ route('welding.deploy') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <select name="part_no" id="part_select" class="form-control mb-3" required style="height: 55px; border-radius: 15px;">
                        @foreach($inventoryWelding as $inv)
                            <option value="{{ $inv->part_no }}">{{ $inv->part_no }} (STOCK: {{ $inv->live_stock }})</option>
                        @endforeach
                    </select>
                    <input type="number" name="qty_ambil" class="form-control text-center" required style="font-size: 32px; height: 80px; border-radius: 20px;" placeholder="0">
                </div>
                <div class="modal-footer border-0 p-4"><button type="submit" class="btn btn-primary btn-block py-3 font-weight-extrabold rounded-pill">DEPLOY rill</button></div>
            </form>
        </div>
    </div>
</div>

<script>
    function quickTake(partNo) {
        document.getElementById('part_select').value = partNo;
        $('#modalDeployWelding').modal('show');
    }
</script>
@endsection