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
    .ledger-container { background: #fff; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); margin-bottom: 30px; }
    .table-ledger thead th { background: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; padding: 15px; border-bottom: 2px solid #edf2f7; }
    .table-ledger td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; }
    
    /* 🏷️ PT NAVIGATION */
    .nav-section { background: #fff; padding: 18px; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 25px; border: 1px solid #e2e8f0; }
    .nav-pills .nav-link { 
        border-radius: 12px; padding: 12px 24px; font-weight: 800; 
        transition: 0.3s; margin-right: 12px;
        background: #f1f5f9; color: #475569 !important; border: 2px solid #e2e8f0;
        display: flex; align-items: center;
    }
    .nav-pills .nav-link.active { 
        background: var(--dark-surface) !important; color: #fff !important; 
        border-color: var(--brand-primary); box-shadow: 0 8px 20px rgba(15, 23, 42, 0.3);
    }

    .count-badge {
        background: var(--brand-danger); color: white; border-radius: 8px;
        padding: 2px 8px; font-size: 10px; margin-left: 10px; font-family: 'JetBrains Mono';
    }

    /* 🛠️ WORK CARDS */
    .work-card { 
        background: #fff; border-radius: 24px; border: 1px solid #eef2f6; 
        padding: 24px; margin-bottom: 16px; transition: 0.3s; 
        display: flex; align-items: center; position: relative;
    }
    .work-card:hover { transform: scale(1.005); box-shadow: 0 15px 30px rgba(0,0,0,0.08); border-color: var(--brand-primary); }
    .qty-display { font-family: 'Orbitron'; font-weight: 800; font-size: 32px; color: var(--dark-surface); line-height: 1; }

    .btn-action-custom { border-radius: 15px; font-weight: 900; letter-spacing: 0.5px; transition: 0.3s; padding: 12px 25px; border: none; }
    .tech-input { border-radius: 15px; border: 2px solid #f1f5f9; font-weight: 700; transition: 0.3s; }
    .tech-input:focus { border-color: var(--brand-primary); outline: none; background: #f8faff; }

    /* NG Rows */
    .ng-row-item { background: #fff5f5; border-radius: 12px; padding: 10px; margin-bottom: 8px; border: 1px solid #fed7d7; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    {{-- 🛸 HEADER HUB --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding Terminal <span style="-webkit-text-fill-color: var(--dark-surface);">v3.0</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0">
                <i class="fas fa-microchip text-primary mr-2"></i> WIP Control & Batch Management System
            </p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
            <a href="{{ route('welding.history') }}" class="btn btn-white rounded-pill px-4 font-weight-extrabold border mr-2 shadow-sm">
                <i class="fas fa-archive mr-2"></i> VAULT
            </a>

            <a href="{{ route('welding.history.weldig') }}" class="btn btn-white rounded-pill px-4 font-weight-extrabold border mr-2 shadow-sm text-primary">
                <i class="fas fa-clipboard-list mr-2"></i> HISTORY
            </a>

            <button class="btn btn-primary rounded-pill px-4 font-weight-extrabold shadow-lg mr-2" style="background: var(--brand-primary); border:none;" data-toggle="modal" data-target="#modalDeployWelding">
                <i class="fas fa-plus-circle mr-1"></i> DEPLOY
            </button>
            <div class="bg-white px-4 py-2 rounded-xl shadow-sm border border-primary text-center">
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
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-4 font-weight-bold" onclick="quickTake('{{ trim($inv->part_no) }}')">TAKE</button>
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
            @php 
                $count = $activeWelding->where('customer', $customer)->count(); 
                $slugPT = Str::slug($customer);
            @endphp
            <li class="nav-item">
                <a class="nav-link {{ $index == 0 ? 'active' : '' }}" data-toggle="pill" href="#pt-{{ $slugPT }}">
                    <i class="fas fa-industry mr-2"></i> {{ strtoupper($customer) }}
                    @if($count > 0) <span class="count-badge">{{ $count }}</span> @endif
                </a>
            </li>
            @endforeach
        </ul>
    </div>

    {{-- 🛠️ WORK CARDS (PROSES PRODUKSI) --}}
    <div class="tab-content" id="ptTabContent">
        @foreach($availableCustomers as $index => $customer)
        @php $slugPT = Str::slug($customer); @endphp
        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="pt-{{ $slugPT }}">
            @php $filtered = $activeWelding->where('customer', $customer); @endphp
            @forelse($filtered as $aw)
            <div class="work-card shadow-sm animate__animated animate__fadeInUp">
                <div class="col-md-2">
                    <div class="badge badge-dark mb-2 px-3 py-1" style="font-family: 'JetBrains Mono'; font-size: 10px;">{{ $aw->kode_line }}</div>
                    <div class="font-weight-extrabold text-primary" style="font-family: 'JetBrains Mono'; font-size: 14px;">
                        <i class="fas fa-barcode mr-2 opacity-50"></i>{{ $aw->no_produksi_stamping }}
                    </div>
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
                        <span class="badge badge-warning py-2 px-3 rounded-pill font-weight-bold">WAITING</span>
                    @else
                        <span class="badge badge-info py-2 px-3 rounded-pill font-weight-bold animate__animated animate__pulse animate__infinite">IN PROCESS</span>
                    @endif
                </div>
                <div class="col-md-2 text-right">
                    @if($aw->batch_status == 'PENDING')
                        <form action="{{ route('welding.start', $aw->id) }}" method="POST">
                            @csrf @method('PUT')
                            <button class="btn btn-primary btn-block btn-action-custom shadow-sm" style="background: var(--brand-primary);">START PROCESS</button>
                        </form>
                    @else
                        <button class="btn btn-success btn-block btn-action-custom shadow-sm" style="background: var(--brand-success);" data-toggle="modal" data-target="#modalFinish{{ $aw->id }}">FINISH PROCESS</button>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-5 bg-white rounded-24 border-2 border-dashed">
                <p class="text-muted font-weight-bold mb-0">No active batches for {{ $customer }}.</p>
            </div>
            @endforelse
        </div>
        @endforeach
    </div>
</div>

{{-- 🏁 MODAL FINISH (QUALITY INSPECTION) --}}
@foreach($activeWelding as $aw)
<div class="modal fade" id="modalFinish{{ $aw->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0" style="border-radius: 25px;">
            <div class="modal-header bg-success text-white p-4">
                <h5 class="modal-title font-weight-bold text-uppercase">Quality Inspection & Finishing</h5>
            </div>
            <form action="{{ route('welding.finish', $aw->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-4 text-center border-right">
                            <h2 class="font-weight-bold text-dark mb-0" style="font-family: 'Orbitron';">{{ number_format($aw->qty_masuk) }}</h2>
                            <small class="text-muted font-weight-bold uppercase">Target Qty</small>
                            
                            <hr>
                            
                            <label class="small font-weight-bold text-success uppercase">Qty OK (Good)</label>
                            <input type="number" name="qty_ok" class="form-control text-center tech-input mb-3" value="{{ $aw->qty_masuk }}" required>
                            
                            <label class="small font-weight-bold text-danger uppercase">Qty NG (Reject)</label>
                            <input type="number" name="qty_ng" id="total_ng_{{ $aw->id }}" class="form-control text-center tech-input" value="0" readonly>
                        </div>
                        
                        <div class="col-md-8 pl-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="font-weight-bold text-dark mb-0 uppercase"><i class="fas fa-bug mr-2 text-danger"></i>Reject Breakdown</h6>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill font-weight-bold" onclick="addNgRow({{ $aw->id }})">
                                    <i class="fas fa-plus mr-1"></i> ADD NG TYPE
                                </button>
                            </div>
                            
                            {{-- Container untuk baris NG dinamis --}}
                            <div id="ng_container_{{ $aw->id }}" style="max-height: 200px; overflow-y: auto; overflow-x: hidden;">
                                <p class="text-center text-muted small py-3" id="no_ng_msg_{{ $aw->id }}">No specific defects reported.</p>
                            </div>

                            <hr>
                            <label class="small font-weight-bold text-dark uppercase">Process Remark</label>
                            <textarea name="keterangan" class="form-control tech-input" rows="2" placeholder="Operator notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="submit" class="btn btn-success btn-block py-3 font-weight-bold rounded-pill shadow-lg">TRANSFER TO QUALITY GATE</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- 🚀 MODAL DEPLOY --}}
<div class="modal fade" id="modalDeployWelding" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 25px;">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title font-weight-bold text-uppercase">Batch Deployment</h5>
            </div>
            <form action="{{ route('welding.deploy') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <label class="small font-weight-bold text-muted uppercase">Select Material Part</label>
                    <select name="part_no" id="part_select" class="form-control tech-input mb-4" required>
                        <option value="" disabled selected>-- CHOOSE PART --</option>
                        @foreach($inventoryWelding as $inv)
                            <option value="{{ $inv->part_no }}">{{ $inv->part_no }} (Available: {{ $inv->live_stock }})</option>
                        @endforeach
                    </select>

                    {{-- ✨ PILIHAN LINE DARI DATABASE --}}
                    <label class="small font-weight-bold text-muted uppercase">Select Welding Station</label>
                    <select name="line_id" class="form-control tech-input mb-4" required>
                        <option value="" disabled selected>-- CHOOSE STATION --</option>
                        @foreach($weldingLines as $wl)
                            <option value="{{ $wl->id }}">{{ $wl->kode_line }} - {{ $wl->nama_line }}</option>
                        @endforeach
                    </select>

                    <label class="small font-weight-bold text-muted uppercase">Deployment Quantity</label>
                    <input type="number" name="qty_ambil" class="form-control text-center tech-input" required style="font-size: 32px; height: 80px;" placeholder="0">
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold rounded-pill shadow-lg">CONFIRM DEPLOYMENT</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Data NG dari Master NG Database
    const listPenyakit = @json($listNG->pluck('ng_name'));

    function quickTake(partNo) {
        document.getElementById('part_select').value = partNo;
        $('#modalDeployWelding').modal('show');
    }

    // Fungsi Tambah Baris NG secara dinamis
    function addNgRow(batchId) {
        $(`#no_ng_msg_${batchId}`).hide();
        const id = Date.now();
        let options = listPenyakit.map(p => `<option value="${p}">${p.toUpperCase()}</option>`).join('');
        
        const html = `
            <div class="ng-row-item animate__animated animate__fadeInDown" id="row-${id}">
                <div class="row no-gutters">
                    <div class="col-7 pr-1">
                        <select name="ng_detail_type[]" class="form-control form-control-sm font-weight-bold" required>
                            ${options}
                        </select>
                    </div>
                    <div class="col-3 pr-1">
                        <input type="number" name="ng_detail_qty[]" class="form-control form-control-sm text-center font-weight-bold ng-qty-input" 
                        placeholder="Qty" min="1" required oninput="calculateTotalNg(${batchId})">
                    </div>
                    <div class="col-2">
                        <button type="button" class="btn btn-danger btn-sm btn-block" onclick="removeNgRow(${id}, ${batchId})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $(`#ng_container_${batchId}`).append(html);
    }

    function removeNgRow(rowId, batchId) {
        $(`#row-${rowId}`).remove();
        calculateTotalNg(batchId);
        if ($(`#ng_container_${batchId}`).children('.ng-row-item').length === 0) {
            $(`#no_ng_msg_${batchId}`).show();
        }
    }

    // Hitung otomatis total NG agar operator tidak perlu menjumlah manual
    function calculateTotalNg(batchId) {
        let total = 0;
        $(`#modalFinish${batchId} .ng-qty-input`).each(function() {
            total += parseInt($(this).val()) || 0;
        });
        $(`#total_ng_${batchId}`).val(total);
    }
</script>
@endsection