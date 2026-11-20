@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --brand-primary: #3b82f6; --brand-secondary: #6366f1;
        --brand-success: #10b981; --brand-danger: #ef4444; --brand-return: #8b5cf6;
        --dark-surface: #0f172a; --bg-main: #f8fafc; --ind-border: #e2e8f0;
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #1e293b; }

    /* HEADER & TYPOGRAPHY */
    .terminal-header { background: linear-gradient(to right, #ffffff, #f1f5f9); border-radius: 22px; padding: 24px 30px; border: 1px solid var(--ind-border); margin-bottom: 24px; }
    .heading-hub { font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 1.7rem; letter-spacing: -0.5px; text-transform: uppercase; background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .font-mono { font-family: 'JetBrains Mono', monospace; font-weight: 800; }

    /* QUICK ACCESS MENU */
    .menu-card { background: #fff; border-radius: 18px; border: 1px solid var(--ind-border); padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; transition: all 0.25s ease; cursor: pointer; text-decoration: none !important; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
    .menu-card:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(59, 130, 246, 0.1); border-color: var(--brand-primary); }
    .menu-card.performance:hover { border-color: var(--brand-success); box-shadow: 0 12px 25px rgba(16, 185, 129, 0.1); }
    .menu-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: 0.25s; }
    .menu-card:hover .menu-icon { transform: scale(1.08); }

    /* LEDGER TABLE WIDGET */
    .ledger-container { background: #fff; border-radius: 20px; border: 1px solid var(--ind-border); overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 28px; }
    .ledger-topbar { background: var(--dark-surface); color: #fff; padding: 14px 22px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; }
    .table-ledger { margin-bottom: 0; }
    .table-ledger thead th { background: #f8fafc; color: #475569; font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.8px; padding: 14px 16px; border-bottom: 1px solid var(--ind-border); font-weight: 800; }
    .table-ledger td { padding: 12px 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 13px; }
    .table-ledger tbody tr:hover { background-color: #f8fafc; }

    /* TOOLBAR CUSTOMER & SHIFT */
    .control-bar { background: #fff; padding: 10px 14px; border-radius: 18px; border: 1px solid var(--ind-border); box-shadow: 0 2px 10px rgba(0,0,0,0.02); margin-bottom: 22px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; }
    .nav-pills { gap: 6px; }
    .nav-pills .nav-link { border-radius: 12px; padding: 9px 18px; font-weight: 800; font-size: 12px; transition: 0.25s; color: #64748b !important; white-space: nowrap; border: 1px solid transparent; }
    .nav-pills .nav-link.active { background: var(--dark-surface) !important; color: #fff !important; box-shadow: 0 6px 14px rgba(15, 23, 42, 0.15); }
    .nav-pills .nav-link:not(.active):hover { background: #f1f5f9; border-color: #e2e8f0; }

    .shift-filter-wrap { background: #f1f5f9; padding: 4px; border-radius: 12px; display: inline-flex; gap: 4px; }
    .shift-filter-btn { border: none; background: transparent; color: #64748b; font-size: 11px; font-weight: 800; padding: 7px 14px; border-radius: 9px; cursor: pointer; transition: 0.2s; outline: none !important; }
    .shift-filter-btn.active { background: var(--brand-primary); color: #fff; box-shadow: 0 3px 8px rgba(59,130,246,0.25); }

    /* SHIFT DIVIDER */
    .shift-divider { display: flex; align-items: center; margin: 18px 0 14px 0; }
    .shift-divider-line { flex-grow: 1; height: 2px; background: #e2e8f0; margin-left: 12px; }

    /* ACTIVE PRODUCTION CARDS (COMPACT 2-COL GRID) */
    .work-card-grid { background: #fff; border-radius: 20px; border: 1px solid var(--ind-border); overflow: hidden; transition: 0.25s; box-shadow: 0 4px 12px rgba(0,0,0,0.03); height: 100%; display: flex; flex-direction: column; position: relative; }
    .work-card-grid:hover { border-color: #94a3b8; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08); transform: translateY(-2px); }
    .work-card-grid.shift-pagi { border-top: 4px solid var(--brand-primary); }
    .work-card-grid.shift-malam { border-top: 4px solid var(--dark-surface); }

    .wc-head { padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 8px; }
    .wc-body { padding: 18px; flex-grow: 1; display: flex; justify-content: space-between; align-items: center; gap: 15px; }
    .wc-foot { padding: 12px 18px 16px 18px; background: #fff; border-top: 1px dashed #e2e8f0; }

    .badge-rework { background: #ede9fe; color: var(--brand-return); padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; border: 1px solid #ddd6fe; }
    .badge-shift-pagi { background: #eff6ff; color: var(--brand-primary); padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; border: 1px solid #bfdbfe; }
    .badge-shift-malam { background: #1e293b; color: #f8fafc; padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; }
    .badge-station { background: #fff; color: #0f172a; padding: 4px 10px; border-radius: 6px; font-size: 10.5px; font-weight: 800; border: 1px solid #cbd5e1; }

    .target-pill { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 10px 16px; text-align: center; min-width: 105px; }
    .qty-display { font-family: 'Orbitron', sans-serif; font-size: 24px; font-weight: 900; color: var(--dark-surface); line-height: 1.1; }

    /* INPUTS & BUTTONS */
    .tech-input-lg { border-radius: 18px; border: 2.5px solid #e2e8f0; font-weight: 800; transition: 0.3s; height: 75px; background: #f8fafc; text-align: center; font-family: 'Orbitron'; font-size: 32px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); }
    .tech-input-lg:focus { border-color: var(--brand-primary); outline: none; background: #fff; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); }
    .btn-cyber { padding: 11px 22px; border-radius: 12px; font-weight: 800; font-size: 12.5px; letter-spacing: 0.5px; transition: all 0.25s ease; border: none; }
    .btn-cyber:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,0.1); }
</style>

<div class="container-fluid py-4 px-lg-4 animate__animated animate__fadeIn">

    {{-- HEADER HUB --}}
    <div class="terminal-header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <h1 class="heading-hub mb-1">Welding Terminal <span style="-webkit-text-fill-color: var(--dark-surface);">v3.2</span></h1>
            <p class="text-muted font-weight-bold small text-uppercase mb-0">
                <i class="fas fa-satellite-dish text-primary mr-2"></i> Quality Assurance & MPS Execution Command Center
            </p>
        </div>
        <div class="mt-3 mt-md-0">
            <button class="btn btn-primary btn-cyber shadow-sm px-4" data-toggle="modal" data-target="#modalDeployWelding">
                <i class="fas fa-plus-circle mr-2"></i> DEPLOY MATERIAL
            </button>
        </div>
    </div>

    {{-- QUICK ACCESS MENU --}}
    <div class="row mb-4">
        <div class="col-md-6 mb-3 mb-md-0">
            <a href="{{ route('welding.history') }}" class="menu-card ledger">
                <div>
                    <h5 class="font-weight-black text-dark mb-1" style="font-family: 'Orbitron'; font-size: 1rem;">VAULT LEDGER</h5>
                    <span class="text-muted font-weight-bold" style="font-size: 11.5px;">REKAPITULASI & MUTASI STOK GUDANG</span>
                </div>
                <div class="menu-icon text-primary" style="background: #eff6ff;">
                    <i class="fas fa-boxes"></i>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('welding.historyWelding') }}" class="menu-card performance">
                <div>
                    <h5 class="font-weight-black text-dark mb-1" style="font-family: 'Orbitron'; font-size: 1rem;">PERFORMANCE</h5>
                    <span class="text-muted font-weight-bold" style="font-size: 11.5px;">RIWAYAT KINERJA PRODUKSI & CACAT (NG)</span>
                </div>
                <div class="menu-icon text-success" style="background: #ecfdf5;">
                    <i class="fas fa-chart-line"></i>
                </div>
            </a>
        </div>
    </div>

    {{-- LEDGER TABLE (LIVE INVENTORY) --}}
    <div class="ledger-container">
        <div class="ledger-topbar">
            <div class="font-weight-bold" style="font-size: 12.5px; letter-spacing: 0.8px;">
                <i class="fas fa-warehouse text-info mr-2"></i> LIVE WIP INVENTORY LEDGER
            </div>
            <div style="width: 240px;">
                <input type="text" id="searchLedger" class="form-control form-control-sm rounded-pill border-0 px-3" placeholder="Cari Part No / Nama..." style="font-size: 12px;">
            </div>
        </div>
        <div class="table-responsive" style="max-height: 360px; overflow-y: auto;">
            <table class="table table-ledger mb-0 text-center" id="tableLedger">
                <thead style="position: sticky; top: 0; z-index: 2;">
                    <tr>
                        <th class="text-left pl-4">Part Identification</th>
                        <th>Stock Awal</th>
                        <th class="text-success">In (Stamp)</th>
                        <th style="color: var(--brand-return);">In (Ret QC)</th>
                        <th class="text-danger">Out (Deploy)</th>
                        <th class="text-primary">Live Stock</th>
                        <th class="text-right pr-4">Control</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventoryWelding as $inv)
                    <tr>
                        <td class="text-left pl-4">
                            <div class="font-weight-black text-dark" style="font-size: 13.5px;">{{ $inv->part_no }}</div>
                            <small class="text-muted font-weight-bold text-uppercase" style="font-size: 10px;">{{ $inv->part_name }}</small>
                        </td>
                        <td class="text-muted font-mono">{{ number_format($inv->init) }}</td>
                        <td class="text-success font-mono">+{{ number_format($inv->in_s) }}</td>
                        <td class="font-mono" style="color: var(--brand-return);">+{{ number_format($inv->in_r) }}</td>
                        <td class="text-danger font-mono">-{{ number_format($inv->out) }}</td>
                        <td class="text-primary font-weight-black" style="font-size: 18px; font-family: 'Orbitron'; background: #f8fafc;">{{ number_format($inv->live_stock) }}</td>
                        <td class="text-right pr-4">
                            <button class="btn btn-dark btn-sm rounded-pill px-3 font-weight-bold shadow-sm" onclick="quickTake('{{ trim($inv->part_no) }}')">TAKE</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- TOOLBAR: PT NAVIGATION + SHIFT FILTER --}}
    <div class="control-bar animate__animated animate__fadeIn">
        <ul class="nav nav-pills flex-nowrap overflow-auto" id="ptTab">
            @foreach($availableCustomers as $index => $customer)
            @php $slugPT = Str::slug($customer); $count = $activeWelding->where('customer', $customer)->count(); @endphp
            <li class="nav-item">
                <a class="nav-link {{ $index == 0 ? 'active' : '' }}" data-toggle="pill" href="#pt-{{ $slugPT }}">
                    {{ strtoupper($customer) }}
                    @if($count > 0) <span class="badge badge-pill badge-danger ml-1">{{ $count }}</span> @endif
                </a>
            </li>
            @endforeach
        </ul>

        <div class="shift-filter-wrap">
            <button type="button" class="shift-filter-btn active" onclick="filterTerminalShift('ALL', this)">ALL SHIFT</button>
            <button type="button" class="shift-filter-btn" onclick="filterTerminalShift('Pagi', this)"><i class="fas fa-sun mr-1"></i> SHIFT 1</button>
            <button type="button" class="shift-filter-btn" onclick="filterTerminalShift('Malam', this)"><i class="fas fa-moon mr-1"></i> SHIFT 2</button>
        </div>
    </div>

    {{-- ACTIVE PRODUCTION CARDS (DIPETAKAN PER SHIFT & GRID 2 KOLOM) --}}
    <div class="tab-content" id="ptTabContent">
        @foreach($availableCustomers as $index => $customer)
        @php
            $slugPT = Str::slug($customer);
            $custBatches = $activeWelding->where('customer', $customer);
            $s1Batches = $custBatches->filter(fn($b) => $b->shift != 'Malam');
            $s2Batches = $custBatches->filter(fn($b) => $b->shift == 'Malam');
        @endphp
        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="pt-{{ $slugPT }}">

            @if($custBatches->isEmpty())
                <div class="bg-white rounded-lg border text-center py-5 shadow-sm" style="border-radius: 20px !important;">
                    <i class="fas fa-check-circle text-muted mb-2" style="font-size: 32px; opacity: 0.35;"></i>
                    <h6 class="font-weight-bold text-muted mb-0">TIDAK ADA ANTREAN BATCH AKTIF UNTUK {{ strtoupper($customer) }}</h6>
                </div>
            @else
                {{-- ================= SHIFT 1 - PAGI ================= --}}
                @if($s1Batches->isNotEmpty())
                <div class="terminal-shift-block" data-shift="Pagi">
                    <div class="shift-divider">
                        <span class="badge badge-primary px-3 py-2 rounded-pill font-weight-bold" style="font-size: 11px; letter-spacing: 0.8px;">
                            <i class="fas fa-sun mr-1"></i> SHIFT 1 - PAGI ({{ $s1Batches->count() }} BATCH)
                        </span>
                        <div class="shift-divider-line"></div>
                    </div>

                    <div class="row">
                        @foreach($s1Batches as $aw)
                        @php $isRobot = str_contains(strtoupper($aw->kode_line ?? ''), 'ROBOT'); @endphp
                        <div class="col-lg-6 mb-3">
                            <div class="work-card-grid shift-pagi animate__animated animate__fadeInUp">
                                <div class="wc-head">
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                        <span class="badge-station">
                                            <i class="fas {{ $isRobot ? 'fa-robot text-primary' : 'fa-bolt text-info' }} mr-1"></i>
                                            {{ $aw->kode_line ?? 'STANDBY' }}
                                        </span>
                                        <span class="badge-shift-pagi"><i class="fas fa-sun mr-1"></i> S1</span>
                                        @if($aw->qty_return > 0)
                                            <span class="badge-rework"><i class="fas fa-redo-alt mr-1"></i> REWORK</span>
                                        @endif
                                    </div>
                                    <span class="font-mono text-primary" style="font-size: 11.5px;">{{ $aw->no_produksi_stamping }}</span>
                                </div>

                                <div class="wc-body">
                                    <div class="pr-2">
                                        <div class="font-weight-black text-dark" style="font-size: 18px; line-height: 1.2;">{{ $aw->part_no }}</div>
                                        <small class="text-muted font-weight-bold text-uppercase d-block mt-1" style="font-size: 11px;">{{ $aw->part_name }}</small>
                                    </div>
                                    <div class="target-pill">
                                        <div class="qty-display">{{ number_format($aw->qty_return > 0 ? $aw->qty_return : $aw->qty_masuk) }}</div>
                                        <small class="text-muted font-weight-bold text-uppercase" style="font-size: 9.5px; letter-spacing: 0.8px;">Target Pcs</small>
                                    </div>
                                </div>

                                <div class="wc-foot">
                                    @if($aw->status == 'PENDING')
                                        <form action="{{ route('welding.start', $aw->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <button class="btn btn-primary btn-cyber btn-block shadow-sm">
                                                <i class="fas fa-play mr-1"></i> START OPERATION
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-success btn-cyber btn-block shadow-sm" data-toggle="modal" data-target="#modalFinish{{ $aw->id }}">
                                            <i class="fas fa-check-double mr-1"></i> FINISH BATCH
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ================= SHIFT 2 - MALAM ================= --}}
                @if($s2Batches->isNotEmpty())
                <div class="terminal-shift-block" data-shift="Malam">
                    <div class="shift-divider">
                        <span class="badge badge-dark px-3 py-2 rounded-pill font-weight-bold" style="font-size: 11px; letter-spacing: 0.8px;">
                            <i class="fas fa-moon mr-1 text-warning"></i> SHIFT 2 - MALAM ({{ $s2Batches->count() }} BATCH)
                        </span>
                        <div class="shift-divider-line"></div>
                    </div>

                    <div class="row">
                        @foreach($s2Batches as $aw)
                        @php $isRobot = str_contains(strtoupper($aw->kode_line ?? ''), 'ROBOT'); @endphp
                        <div class="col-lg-6 mb-3">
                            <div class="work-card-grid shift-malam animate__animated animate__fadeInUp">
                                <div class="wc-head">
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                        <span class="badge-station">
                                            <i class="fas {{ $isRobot ? 'fa-robot text-primary' : 'fa-bolt text-info' }} mr-1"></i>
                                            {{ $aw->kode_line ?? 'STANDBY' }}
                                        </span>
                                        <span class="badge-shift-malam"><i class="fas fa-moon mr-1"></i> S2</span>
                                        @if($aw->qty_return > 0)
                                            <span class="badge-rework"><i class="fas fa-redo-alt mr-1"></i> REWORK</span>
                                        @endif
                                    </div>
                                    <span class="font-mono text-primary" style="font-size: 11.5px;">{{ $aw->no_produksi_stamping }}</span>
                                </div>

                                <div class="wc-body">
                                    <div class="pr-2">
                                        <div class="font-weight-black text-dark" style="font-size: 18px; line-height: 1.2;">{{ $aw->part_no }}</div>
                                        <small class="text-muted font-weight-bold text-uppercase d-block mt-1" style="font-size: 11px;">{{ $aw->part_name }}</small>
                                    </div>
                                    <div class="target-pill">
                                        <div class="qty-display">{{ number_format($aw->qty_return > 0 ? $aw->qty_return : $aw->qty_masuk) }}</div>
                                        <small class="text-muted font-weight-bold text-uppercase" style="font-size: 9.5px; letter-spacing: 0.8px;">Target Pcs</small>
                                    </div>
                                </div>

                                <div class="wc-foot">
                                    @if($aw->status == 'PENDING')
                                        <form action="{{ route('welding.start', $aw->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <button class="btn btn-primary btn-cyber btn-block shadow-sm">
                                                <i class="fas fa-play mr-1"></i> START OPERATION
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-success btn-cyber btn-block shadow-sm" data-toggle="modal" data-target="#modalFinish{{ $aw->id }}">
                                            <i class="fas fa-check-double mr-1"></i> FINISH BATCH
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- MODAL FINISH --}}
@foreach($activeWelding as $aw)
@php $currentTarget = $aw->qty_return > 0 ? $aw->qty_return : $aw->qty_masuk; @endphp
<div class="modal fade" id="modalFinish{{ $aw->id }}" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 35px; overflow: hidden;">
            <div class="modal-header bg-dark text-white p-4 border-0">
                <h5 class="modal-title font-weight-black text-uppercase" style="font-family: 'Orbitron'; letter-spacing: 1px;">
                    Quality_Gate_Auth <span class="text-warning ml-3" style="font-size: 14px;">[{{ strtoupper($aw->shift ?? 'Pagi') }}]</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 1;"><span>&times;</span></button>
            </div>
            <form action="{{ route('welding.finish', $aw->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4 p-md-5">
                    <div class="row">
                        <div class="col-md-4 text-center border-right pr-4 pr-md-5">
                            <div class="p-4 bg-light rounded-3xl mb-4 border" style="background: #f8fafc;">
                                <h1 class="font-weight-black text-dark mb-0" style="font-family: 'Orbitron'; font-size: 48px; color: var(--dark-surface);">{{ number_format($currentTarget) }}</h1>
                                <small class="text-muted font-weight-black uppercase" style="letter-spacing: 1px;">Required Output</small>
                            </div>

                            <div class="form-group mb-4">
                                <label class="small font-weight-black text-success uppercase mb-2" style="letter-spacing: 0.5px;"><i class="fas fa-check-circle mr-1"></i> Verified OK</label>
                                <input type="number" name="qty_ok" id="input_ok_{{ $aw->id }}" class="form-control tech-input-lg text-success border-success"
                                       value="{{ $currentTarget }}" required oninput="manualOkCheck({{ $aw->id }}, {{ $currentTarget }})">
                            </div>

                            <div id="sec_msg_{{ $aw->id }}" class="p-3 rounded-2xl bg-success text-white font-weight-black animate__animated shadow-sm" style="font-size: 13px;">
                                <i class="fas fa-shield-check mr-2"></i>INTEGRITY_VERIFIED
                            </div>
                        </div>

                        <div class="col-md-8 pl-4 pl-md-5 mt-4 mt-md-0">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="return-box">
                                        <label class="small font-weight-black text-primary uppercase d-block mb-2" style="letter-spacing: 0.5px;">
                                            <i class="fas fa-undo-alt mr-1"></i> Return_To_WIP_Rack
                                        </label>
                                        <input type="number" name="qty_return" id="input_ret_{{ $aw->id }}" class="form-control tech-input-lg border-primary"
                                               style="color: var(--brand-primary); height: 70px;" value="0" oninput="calculateTotal({{ $aw->id }}, {{ $currentTarget }})">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="p-3 bg-white border-danger border rounded-3xl shadow-sm" style="border-width: 2px;">
                                        <label class="small font-weight-black text-danger uppercase d-block mb-1" style="letter-spacing: 0.5px;">Defect Accumulation (NG)</label>
                                        <input type="number" name="qty_ng" id="total_ng_{{ $aw->id }}" class="form-control tech-input-lg border-0 bg-transparent text-danger"
                                               style="height: 60px; box-shadow: none;" value="0" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="font-weight-black text-dark mb-0 uppercase"><i class="fas fa-bug mr-2 text-danger"></i>NG_Breakdown</h6>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-4 font-weight-black" onclick="addNgRow({{ $aw->id }}, {{ $currentTarget }})">+ ADD NG</button>
                            </div>

                            <div id="ng_container_{{ $aw->id }}" class="pr-2" style="max-height: 180px; overflow-y: auto;">
                                <div class="text-center text-muted py-4 border rounded-2xl border-dashed bg-light" id="no_ng_msg_{{ $aw->id }}">
                                    No defects reported.
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="small font-weight-black text-dark uppercase mb-2" style="letter-spacing: 0.5px;">Process Notes (Optional)</label>
                                <textarea name="keterangan" class="form-control" rows="2" style="border-radius: 16px; border: 2px solid #e2e8f0; font-weight: 600; padding: 12px;"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 p-md-5 pt-0 bg-white">
                    <button type="submit" id="btn_submit_{{ $aw->id }}" class="btn btn-success btn-block py-4 font-weight-black rounded-pill shadow-lg uppercase" style="font-size: 1.2rem; letter-spacing: 2px; transition: 0.3s;">
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

    // Filter cepat Shift 1 / Shift 2 di Terminal
    function filterTerminalShift(mode, btnEl) {
        $('.shift-filter-btn').removeClass('active');
        $(btnEl).addClass('active');

        $('.terminal-shift-block').each(function() {
            const shiftType = $(this).data('shift');
            if (mode === 'ALL' || shiftType === mode) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // Pencarian cepat di tabel Live WIP Inventory
    $('#searchLedger').on('input', function() {
        const keyword = $(this).val().toLowerCase();
        $('#tableLedger tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(keyword) > -1);
        });
    });

    function addNgRow(batchId, target) {
        $(`#no_ng_msg_${batchId}`).hide();
        const id = Date.now();
        let options = listPenyakit.map(p => `<option value="${p}">${p.toUpperCase()}</option>`).join('');
        const html = `
            <div class="ng-row-item d-flex align-items-center mb-2 p-2 bg-white rounded-xl border shadow-sm" id="row-${id}">
                <select name="ng_detail_type[]" class="form-control border-0 bg-transparent font-weight-bold flex-grow-1 text-dark">${options}</select>
                <input type="number" name="ng_detail_qty[]" class="form-control tech-input mx-3 text-center font-weight-black text-danger bg-light" style="width: 90px; height: 45px; border-radius: 12px; border: 1px solid #e2e8f0;" value="0" oninput="calculateTotal(${batchId}, ${target})">
                <button type="button" class="btn btn-link text-danger p-0 mr-2" onclick="removeNgRow(${id}, ${batchId}, ${target})"><i class="fas fa-times-circle fa-lg"></i></button>
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
        $(`#modalFinish${batchId} input[name="ng_detail_qty[]"]`).each(function() {
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

        if (grandTotal === target && ok >= 0 && ret >= 0 && ng >= 0) {
            msgBox.removeClass('bg-danger animate__headShake').addClass('bg-success')
                  .html('<i class="fas fa-shield-check mr-2"></i>DATA_INTEGRITY_MATCHED');
            btnSubmit.prop('disabled', false).css('opacity', '1').css('cursor', 'pointer');
        } else {
            let gap = target - grandTotal;
            msgBox.removeClass('bg-success').addClass('bg-danger animate__headShake')
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