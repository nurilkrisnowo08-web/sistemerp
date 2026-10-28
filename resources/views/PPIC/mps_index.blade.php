@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Orbitron:wght@600;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --p-primary: #3b82f6; --p-dark: #0f172a; --p-success: #10b981; 
        --p-danger: #ef4444; --p-warning: #f59e0b; --p-slate: #64748b;
        --glass-border: rgba(226, 232, 240, 0.8);
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: var(--p-dark); }
    
    /* Typography & Headers */
    .hud-title { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; }
    .glass-card { background: white; border-radius: 24px; border: 1px solid var(--glass-border); box-shadow: 0 10px 30px -5px rgba(0,0,0,0.03); overflow: hidden; transition: 0.3s; }
    .glass-card:hover { box-shadow: 0 15px 35px -5px rgba(0,0,0,0.06); transform: translateY(-3px); }
    
    /* Machine Switcher (Pill Nav) */
    .machine-switcher { background: #f1f5f9; padding: 6px; border-radius: 50px; display: inline-flex; border: 1px solid #e2e8f0; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); }
    .machine-switcher .btn { border-radius: 50px; font-weight: 800; padding: 10px 28px; font-size: 12px; letter-spacing: 0.5px; transition: 0.3s; text-transform: uppercase; }
    .machine-switcher .btn.active { background: var(--p-primary); color: white; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.35); }
    .machine-switcher .btn:not(.active) { color: var(--p-slate); }
    .machine-switcher .btn:not(.active):hover { background: #e2e8f0; color: var(--p-dark); }

    /* Top Cards with Gradients */
    .card-grad-1 { background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border-left: 5px solid var(--p-primary); }
    .card-grad-2 { background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border-left: 5px solid var(--p-success); }
    .card-grad-3 { background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%); border-left: 5px solid var(--p-warning); }
    
    /* Tables */
    .table-mps { width: 100%; border-collapse: separate; border-spacing: 0; }
    .table-mps thead th { background: #f8fafc; padding: 18px 12px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--p-slate); border-bottom: 2px solid #e2e8f0; }
    .table-mps tbody td { padding: 18px 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; font-weight: 600; vertical-align: middle; transition: 0.2s; }
    .table-mps tbody tr:hover td { background-color: #f8fafc; }
    
    .part-cell { border-left: 4px solid var(--p-primary); background: #f8faff; font-weight: 800 !important; color: var(--p-primary); }
    .badge-time { background: #fff9c4; color: #856404; font-family: 'JetBrains Mono', monospace; padding: 6px 12px; border-radius: 8px; font-weight: 800; border: 1px solid #ffe58f; font-size: 11px; }
    .badge-balance { padding: 6px 14px; border-radius: 8px; font-family: 'Orbitron', sans-serif; font-size: 12px; font-weight: 900; }
    .balance-danger { background: rgba(239, 68, 68, 0.1); color: var(--p-danger); border: 1px solid var(--p-danger); }
    .balance-success { background: rgba(16, 185, 129, 0.1); color: var(--p-success); border: 1px solid var(--p-success); }
    .footer-summary { background: var(--p-dark) !important; color: white !important; font-family: 'Orbitron', sans-serif; font-weight: 800; }
    .footer-summary td { border: none !important; padding: 22px !important; }
    
    /* Revisi Badge */
    .rev-badge { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 900; margin-left: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    
    .t-mono { font-family: 'JetBrains Mono', monospace; font-weight: 800; }
    .btn-cyber { border-radius: 14px; font-weight: 800; padding: 10px 24px; font-size: 12px; letter-spacing: 0.5px; transition: 0.3s; }
    .btn-cyber:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }

    .anim-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="container-fluid mt-4 mb-5 anim-up">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-lg mb-4" style="border-left: 5px solid #10b981 !important; background-color: #d1fae5; padding: 15px 20px;">
            <h6 class="font-weight-bold mb-1"><i class="fas fa-check-circle mr-2 text-success"></i>Operation Successful</h6>
            <p class="mb-0 text-dark" style="font-size: 13px; font-weight: 600;">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-lg mb-4" style="border-left: 5px solid #ef4444 !important; background-color: #fee2e2; padding: 15px 20px;">
            <h6 class="font-weight-bold mb-1 text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>System Warning</h6>
            <p class="mb-0 text-dark" style="font-size: 13px; font-weight: 600;">{{ session('error') }}</p>
        </div>
    @endif

    {{-- 🛰️ HEADER & NAVIGATION --}}
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
        <div>
            <h2 class="hud-title m-0" style="font-size: 28px;">WOS_TERMINAL <span class="text-primary"> Smart MRP</span></h2>
            <p class="text-muted small font-weight-bold uppercase mb-0 mt-1"><i class="far fa-calendar-alt mr-2"></i> Operational Date: <span class="text-dark">{{ date('d F Y', strtotime($date)) }}</span></p>
        </div>

        {{-- 🔥 TOMBOL SWITCHER BIG / SMALL PRESS 🔥 --}}
        <div class="machine-switcher mx-xl-auto my-3 my-xl-0">
            <a href="#" class="btn active"><i class="fas fa-industry mr-2"></i>BIG PRESS</a>
            {{-- Sesuaikan URL di bawah ini dengan route/link Small Press milikmu --}}
            <a href="{{ url('/ppic/mps/small_mps.blade') }}" class="btn"><i class="fas fa-cogs mr-2"></i>SMALL PRESS</a>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form action="" method="GET" class="mr-2">
                <input type="date" name="date" class="form-control rounded-pill border-dark px-4 shadow-sm font-weight-bold" style="height: 42px;" value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <a href="{{ route('ppic.wos.print_bundle', ['date' => $date]) }}" class="btn btn-dark btn-cyber shadow-sm mr-1" target="_blank">
                <i class="fas fa-print mr-2 text-warning"></i> PRINT ALL WOS
            </a>
            <button class="btn btn-primary btn-cyber shadow-sm" data-toggle="modal" data-target="#modalAddPlan">
                <i class="fas fa-plus-circle mr-2"></i> REGISTER PLAN
            </button>
        </div>
    </div>

    {{-- 📊 4 KARTU INDIKATOR (TOP CARDS) --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="glass-card card-grad-1 p-4 text-center">
                <small class="text-muted font-weight-bold uppercase letter-spacing-1">Total Target (Qty)</small>
                <h2 class="font-weight-bold mb-0 mt-2 text-primary" style="font-family: 'Orbitron'; font-size: 38px;">{{ number_format($totalPlanQty) }}</h2>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="glass-card card-grad-2 p-4 text-center">
                <small class="text-muted font-weight-bold uppercase letter-spacing-1">Workload (Hours)</small>
                <h4 class="font-weight-bold mb-0 mt-3 text-info font-mono" style="font-size: 22px;">
                    <span class="text-success">S1: {{ round($totalWorkingHoursS1, 1) }}h</span>
                    <span class="text-muted mx-2">|</span>
                    <span class="text-dark">S2: {{ round($totalWorkingHoursS2, 1) }}h</span>
                </h4>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="glass-card card-grad-3 p-4 text-center">
                <small class="text-muted font-weight-bold uppercase letter-spacing-1">Switch Loss (Dandory)</small>
                <h2 class="font-weight-bold mb-0 mt-2 text-warning" style="font-family: 'Orbitron'; font-size: 38px;">{{ $totalDandory }}<span style="font-size: 20px;">m</span></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-4 text-center bg-dark text-white border-0" style="border-left: 5px solid #38bdf8 !important;">
                <small class="text-info font-weight-bold uppercase letter-spacing-1">Scope Data</small>
                <h2 class="font-weight-bold mb-0 mt-2" style="font-family: 'Orbitron'; font-size: 28px; padding-top: 5px;">FULL DAY</h2>
            </div>
        </div>
    </div>

    {{-- 📑 DAFTAR MPS PER MESIN --}}
    <div class="glass-card">
        <div class="table-responsive">
            <table class="table-mps text-center">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-left pl-4">Identification</th>
                        <th>MP</th>
                        <th>Proc</th>
                        <th>Target</th>
                        <th>Actual</th>
                        <th>Balance</th>
                        <th class="text-primary">Start</th>
                        <th class="text-primary">Finish</th>
                        <th>Dandory</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groupedPlans as $lineCode => $plansInLine)
                        <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; border-top: 4px solid #1e293b;">
                            <td colspan="9" class="text-left pl-4">
                                <h5 class="font-weight-bold text-dark m-0 uppercase py-2" style="letter-spacing: 1px; font-family: 'Orbitron';">
                                    <i class="fas fa-industry mr-2 text-primary"></i> M/C LINE : {{ $lineCode }}
                                </h5>
                            </td>
                            <td colspan="2" class="text-right pr-4">
                                <div class="btn-group shadow-sm">
                                    <a href="{{ route('ppic.wos.print', ['date' => $date, 'shift' => 'ALL', 'line_code' => $lineCode]) }}" class="btn btn-primary btn-sm font-weight-bold px-3" target="_blank" title="Print Kertas WOS (Full Day)">
                                        <i class="fas fa-print mr-1"></i> WOS
                                    </a>
                                    <a href="{{ route('ppic.material.print', ['date' => $date, 'shift' => 'ALL', 'line_code' => $lineCode]) }}" class="btn btn-success btn-sm font-weight-bold px-3" target="_blank" title="Print Serah Terima Material (Full Day)">
                                        <i class="fas fa-box-open mr-1"></i> MATERIAL
                                    </a>
                                </div>
                            </td>
                        </tr>

                        @php 
                            $s1Plans = $plansInLine->where('display_shift', 'S1')->values();
                            $s2Plans = $plansInLine->where('display_shift', 'S2')->values();
                        @endphp

                        {{-- SHIFT 1 --}}
                        @if ($s1Plans->count() > 0)
                            <tr style="background-color: #e2e8f0;">
                                <td colspan="11" class="text-left pl-4 font-weight-bold" style="font-size: 11px; color: #334155; padding: 12px 10px;">
                                    <i class="fas fa-sun text-warning mr-2" style="font-size: 14px;"></i> SHIFT 1 (DAY OPS)
                                </td>
                            </tr>
                            @foreach ($s1Plans as $index => $p)
                                @php 
                                    if ($p->balance < 0) {
                                        $balanceStatus = 'balance-success'; $balanceIcon = 'fa-plus-circle'; $balanceText = '+' . number_format(abs($p->balance));
                                    } elseif ($p->balance > 0) {
                                        $balanceStatus = 'balance-danger'; $balanceIcon = 'fa-exclamation-triangle'; $balanceText = number_format($p->balance);
                                    } else {
                                        $balanceStatus = 'balance-success'; $balanceIcon = 'fa-check-circle'; $balanceText = '0';
                                    }

                                    // Deteksi Jumlah Revisi
                                    $revLabel = '';
                                    if (isset($p->keterangan) && preg_match('/\(R(\d+)\)/', $p->keterangan, $matches)) {
                                        $revLabel = "<span class='rev-badge shadow-sm'><i class='fas fa-info-circle mr-1'></i>REV-{$matches[1]}</span>";
                                    }
                                @endphp
                                <tr>
                                    <td class="text-muted t-mono">{{ $index + 1 }}</td>
                                    <td class="text-left pl-4 part-cell">
                                        <div class="font-weight-bold" style="font-size: 14px;">{{ $p->part_no }} {!! $revLabel !!}</div>
                                        <small class="text-muted font-weight-bold mt-1 d-block">{{ $p->customer_code }}</small>
                                    </td>
                                    <td><span class="badge badge-light border px-2 py-1">{{ $p->manpower }}P</span></td>
                                    <td class="t-mono">{{ $p->process_qty }}</td>
                                    <td class="t-mono text-dark" style="font-size: 15px;">{{ number_format($p->total_target) }}</td>
                                    <td class="t-mono text-primary" style="font-size: 15px;">{{ number_format($p->total_actual) }}</td>
                                    <td>
                                        <span class="badge-balance {{ $balanceStatus }}">
                                            <i class="fas {{ $balanceIcon }} mr-1"></i> {{ $balanceText }}
                                        </span>
                                    </td>
                                    <td><span class="badge-time">{{ $p->start_time }}</span></td>
                                    <td><span class="badge-time">{{ $p->ahir_time }}</span></td>
                                    <td class="t-mono text-muted">{{ $p->dandory_time ?? 15 }}m</td>
                                    <td>
                                        @if($p->total_actual > 0)
                                            <button class="btn btn-secondary btn-sm shadow-sm font-weight-bold px-3" disabled title="Terkunci: Produksi Sudah Mulai (Aktual > 0)">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-warning btn-sm shadow-sm font-weight-bold text-dark px-3" data-toggle="modal" data-target="#modalEditPlan-{{ $p->id }}-{{ $p->display_shift }}" title="Revisi Target">
                                                <i class="fas fa-edit mr-1"></i> Edit
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                @if($p->total_actual == 0)
                                <div class="modal fade" id="modalEditPlan-{{ $p->id }}-{{ $p->display_shift }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                                            <div class="modal-header bg-warning text-dark p-4 border-0">
                                                <h6 class="modal-title font-weight-bold uppercase" style="letter-spacing: 1px;"><i class="fas fa-tools mr-2"></i> Revisi WOS & Sync Material</h6>
                                                <button type="button" class="close text-dark" data-dismiss="modal"><span>&times;</span></button>
                                            </div>
                                            <form action="{{ route('ppic.wos.update', $p->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body p-4 p-md-5 bg-light">
                                                    <div class="alert alert-warning border-0 shadow-sm small font-weight-bold mb-4" style="background: #fffbeb; color: #b45309;">
                                                        <i class="fas fa-exclamation-circle mr-1"></i> Ubah target menjadi 0 jika ingin membatalkan WOS. Selisih material akan dikalkulasi otomatis ke gudang.
                                                    </div>
                                                    <div class="bg-white p-4 rounded-xl shadow-sm border mb-4">
                                                        <h6 class="font-weight-bold text-primary mb-3 pb-3 border-bottom"><i class="fas fa-cube mr-2"></i>{{ $p->part_no }} <span class="text-muted mx-2">|</span> {{ $p->line_code }}</h6>
                                                        <div class="row">
                                                            <div class="col-6 mb-3">
                                                                <label class="small font-weight-bold text-dark">S1 Reguler</label>
                                                                <input type="number" name="s1_plan_reg" class="form-control font-weight-bold text-center" style="border-radius: 12px; background: #f8fafc;" value="{{ $p->s1_plan_reg }}" min="0">
                                                            </div>
                                                            <div class="col-6 mb-3">
                                                                <label class="small font-weight-bold text-warning">S1 OT</label>
                                                                <input type="number" name="s1_plan_ot" class="form-control font-weight-bold text-center" style="border-radius: 12px; background: #f8fafc;" value="{{ $p->s1_plan_ot }}" min="0">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="small font-weight-bold text-dark">S2 Reguler</label>
                                                                <input type="number" name="s2_plan_reg" class="form-control font-weight-bold text-center" style="border-radius: 12px; background: #f8fafc;" value="{{ $p->s2_plan_reg }}" min="0">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="small font-weight-bold text-warning">S2 OT</label>
                                                                <input type="number" name="s2_plan_ot" class="form-control font-weight-bold text-center" style="border-radius: 12px; background: #f8fafc;" value="{{ $p->s2_plan_ot }}" min="0">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <label class="small font-weight-bold text-dark">Cap/Hour</label>
                                                            <input type="number" name="cap_per_hour" class="form-control text-center font-weight-bold" style="border-radius: 12px;" value="{{ $p->cap_per_hour }}">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="small font-weight-bold text-dark">Dandory (m)</label>
                                                            <input type="number" name="dandory_time" class="form-control text-center font-weight-bold" style="border-radius: 12px;" value="{{ $p->dandory_time ?? 15 }}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-white border-0 p-4 justify-content-between">
                                                    <button type="button" class="btn btn-light font-weight-bold border rounded-pill px-4" data-dismiss="modal">BATAL</button>
                                                    <button type="submit" class="btn btn-warning font-weight-bold shadow-sm text-dark rounded-pill px-4"><i class="fas fa-sync-alt mr-2"></i> UPDATE & SYNC</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        @endif

                        {{-- SHIFT 2 --}}
                        @if ($s2Plans->count() > 0)
                            <tr style="background-color: #cbd5e1;">
                                <td colspan="11" class="text-left pl-4 font-weight-bold" style="font-size: 11px; color: #1e293b; padding: 12px 10px;">
                                    <i class="fas fa-moon text-dark mr-2" style="font-size: 14px;"></i> SHIFT 2 (NIGHT OPS)
                                </td>
                            </tr>
                            @foreach ($s2Plans as $index =>$p)
                                @php 
                                    if ($p->balance < 0) {
                                        $balanceStatus = 'balance-success'; $balanceIcon = 'fa-plus-circle'; $balanceText = '+' . number_format(abs($p->balance));
                                    } elseif ($p->balance > 0) {
                                        $balanceStatus = 'balance-danger'; $balanceIcon = 'fa-exclamation-triangle'; $balanceText = number_format($p->balance);
                                    } else {
                                        $balanceStatus = 'balance-success'; $balanceIcon = 'fa-check-circle'; $balanceText = '0';
                                    }

                                    // Deteksi Jumlah Revisi
                                    $revLabel = '';
                                    if (isset($p->keterangan) && preg_match('/\(R(\d+)\)/', $p->keterangan, $matches)) {
                                        $revLabel = "<span class='rev-badge shadow-sm'><i class='fas fa-info-circle mr-1'></i>REV-{$matches[1]}</span>";
                                    }
                                @endphp
                                <tr>
                                    <td class="text-muted t-mono">{{ $index + 1 }}</td>
                                    <td class="text-left pl-4 part-cell">
                                        <div class="font-weight-bold" style="font-size: 14px;">{{ $p->part_no }} {!! $revLabel !!}</div>
                                        <small class="text-muted font-weight-bold mt-1 d-block">{{ $p->customer_code }}</small>
                                    </td>
                                    <td><span class="badge badge-light border px-2 py-1">{{ $p->manpower }}P</span></td>
                                    <td class="t-mono">{{ $p->process_qty }}</td>
                                    <td class="t-mono text-dark" style="font-size: 15px;">{{ number_format($p->total_target) }}</td>
                                    <td class="t-mono text-primary" style="font-size: 15px;">{{ number_format($p->total_actual) }}</td>
                                    <td>
                                        <span class="badge-balance {{ $balanceStatus }}">
                                            <i class="fas {{ $balanceIcon }} mr-1"></i> {{ $balanceText }}
                                        </span>
                                    </td>
                                    <td><span class="badge-time">{{ $p->start_time }}</span></td>
                                    <td><span class="badge-time">{{ $p->ahir_time }}</span></td>
                                    <td class="t-mono text-muted">{{ $p->dandory_time ?? 15 }}m</td>
                                    <td>
                                        @if($p->total_actual > 0)
                                            <button class="btn btn-secondary btn-sm shadow-sm font-weight-bold px-3" disabled title="Terkunci: Produksi Sudah Mulai (Aktual > 0)">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-warning btn-sm shadow-sm font-weight-bold text-dark px-3" data-toggle="modal" data-target="#modalEditPlan-{{ $p->id }}-{{$p->display_shift }}" title="Revisi Target">
                                                <i class="fas fa-edit mr-1"></i> Edit
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                @if($p->total_actual == 0)
                                <div class="modal fade" id="modalEditPlan-{{ $p->id }}-{{$p->display_shift }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                                            <div class="modal-header bg-warning text-dark p-4 border-0">
                                                <h6 class="modal-title font-weight-bold uppercase" style="letter-spacing: 1px;"><i class="fas fa-tools mr-2"></i> Revisi WOS & Sync Material</h6>
                                                <button type="button" class="close text-dark" data-dismiss="modal"><span>&times;</span></button>
                                            </div>
                                            <form action="{{ route('ppic.wos.update', $p->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body p-4 p-md-5 bg-light">
                                                    <div class="alert alert-warning border-0 shadow-sm small font-weight-bold mb-4" style="background: #fffbeb; color: #b45309;">
                                                        <i class="fas fa-exclamation-circle mr-1"></i> Ubah target menjadi 0 jika ingin membatalkan WOS. Selisih material akan dikalkulasi otomatis ke gudang.
                                                    </div>
                                                    <div class="bg-white p-4 rounded-xl shadow-sm border mb-4">
                                                        <h6 class="font-weight-bold text-primary mb-3 pb-3 border-bottom"><i class="fas fa-cube mr-2"></i>{{ $p->part_no }} <span class="text-muted mx-2">|</span> {{$p->line_code }}</h6>
                                                        <div class="row">
                                                            <div class="col-6 mb-3">
                                                                <label class="small font-weight-bold text-dark">S1 Reguler</label>
                                                                <input type="number" name="s1_plan_reg" class="form-control font-weight-bold text-center" style="border-radius: 12px; background: #f8fafc;" value="{{ $p->s1_plan_reg }}" min="0">
                                                            </div>
                                                            <div class="col-6 mb-3">
                                                                <label class="small font-weight-bold text-warning">S1 OT</label>
                                                                <input type="number" name="s1_plan_ot" class="form-control font-weight-bold text-center" style="border-radius: 12px; background: #f8fafc;" value="{{ $p->s1_plan_ot }}" min="0">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="small font-weight-bold text-dark">S2 Reguler</label>
                                                                <input type="number" name="s2_plan_reg" class="form-control font-weight-bold text-center" style="border-radius: 12px; background: #f8fafc;" value="{{ $p->s2_plan_reg }}" min="0">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="small font-weight-bold text-warning">S2 OT</label>
                                                                <input type="number" name="s2_plan_ot" class="form-control font-weight-bold text-center" style="border-radius: 12px; background: #f8fafc;" value="{{ $p->s2_plan_ot }}" min="0">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <label class="small font-weight-bold text-dark">Cap/Hour</label>
                                                            <input type="number" name="cap_per_hour" class="form-control text-center font-weight-bold" style="border-radius: 12px;" value="{{ $p->cap_per_hour }}">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="small font-weight-bold text-dark">Dandory (m)</label>
                                                            <input type="number" name="dandory_time" class="form-control text-center font-weight-bold" style="border-radius: 12px;" value="{{ $p->dandory_time ?? 15 }}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-white border-0 p-4 justify-content-between">
                                                    <button type="button" class="btn btn-light font-weight-bold border rounded-pill px-4" data-dismiss="modal">BATAL</button>
                                                    <button type="submit" class="btn btn-warning font-weight-bold shadow-sm text-dark rounded-pill px-4"><i class="fas fa-sync-alt mr-2"></i> UPDATE & SYNC</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        @endif

                    @empty
                    <tr>
                        <td colspan="11" class="py-5 text-center text-muted">
                            <div style="background: #f1f5f9; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                                <i class="fas fa-calendar-times fa-2x" style="color: #94a3b8;"></i>
                            </div>
                            <h6 class="font-weight-bold text-dark mb-1">MPS Kosong</h6>
                            <span style="font-size: 13px;">Belum ada jadwal produksi Big Press hari ini.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if ($groupedPlans->count() > 0)
                <tfoot>
                    <tr class="footer-summary">
                        <td colspan="4" class="text-right uppercase tracking-widest" style="font-size: 13px;">Daily Summary Total :</td>
                        <td style="font-size: 16px;">{{ number_format($totalPlanQty) }}</td>
                        <td colspan="2" class="text-right tracking-widest" style="font-size: 13px;">Total Est. Work :</td>
                        <td colspan="2" class="text-center" style="font-size: 14px;">
                            <span class="text-info">S1: {{ round($totalWorkingHoursS1, 1) }}H</span> 
                            <span class="mx-2 text-muted">|</span> 
                            <span class="text-light">S2: {{ round($totalWorkingHoursS2, 1) }}H</span>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- MODAL REGISTER --}}
<div class="modal fade" id="modalAddPlan" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 25px; overflow: hidden;">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title hud-title"><i class="fas fa-plus-circle mr-2"></i> REGISTER_PLAN_SYSTEM</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('ppic.mps.store') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="row">
                        <div class="col-md-4 border-right">
                            <h6 class="font-weight-bold text-primary mb-3">01. PRODUCT_IDENTITY</h6>
                            <div class="form-group">
                                <label class="small font-weight-bold">Select Line</label>
                                <select name="line_code" id="select_line" class="form-control input-industrial" required>
                                    <option value="">-- CHOOSE LINE --</option>
                                    @foreach ($availableLines as $l)
                                        <option value="{{ $l->kode_Line }}" data-type="{{ str_contains(strtoupper($l->nama_Line), 'BIG') ? 'BIG' : 'SMALL' }}">{{ $l->kode_Line }} - {{$l->nama_Line }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Customer</label>
                                <select name="customer_code" id="select_customer" class="form-control input-industrial" required>
                                    <option value="">-- CHOOSE --</option>
                                    @foreach ($availableCustomers as $c)
                                        <option value="{{ $c->code }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Part Identification</label>
                                <select name="part_no" id="select_part" class="form-control input-industrial" required>
                                    <option value="">-- SELECT CUSTOMER FIRST --</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 border-right px-4">
                            <h6 class="font-weight-bold text-info mb-3">02. PERFORMANCE_METRICS</h6>
                            <div class="row">
                                <div class="col-6"><label class="small font-weight-bold">Manpower</label><input type="number" name="manpower" class="form-control input-industrial mb-3" value="8"></div>
                                <div class="col-6"><label class="small font-weight-bold">Process</label><input type="number" name="process_qty" class="form-control input-industrial mb-3" value="4"></div>
                                <div class="col-6"><label class="small font-weight-bold text-primary">Cap/Hour</label><input type="number" name="cap_per_hour" id="input_cap" class="form-control input-industrial mb-3 font-weight-bold border-primary" value="320" required></div>
                                <div class="col-6"><label class="small font-weight-bold text-warning">Dandory (m)</label><input type="number" name="dandory_time" id="input_dandory" class="form-control input-industrial mb-3" value="15"></div>
                            </div>
                            <div class="alert alert-info border-0 rounded-lg text-center mt-3 py-3 shadow-sm">
                                <small class="d-block font-weight-bold uppercase mb-1">Calculated M/C Load</small>
                                <h5 class="hud-title mb-0" id="live_load_label">
                                    <span class="text-primary">S1: 0.0H</span> <span class="text-muted mx-1">|</span> <span class="text-dark">S2: 0.0H</span>
                                </h5>
                            </div>
                        </div>

                        <div class="col-md-4 pl-4">
                            <h6 class="font-weight-bold text-success mb-3">03. QUANTITY_PLANNING</h6>
                            <div class="mb-3 p-2 rounded" style="background-color: #f0fdf4; border: 1px dashed #10b981;">
                                <label class="small font-weight-bold text-success"><i class="fas fa-link mr-1"></i> Separating Part (Opsional)</label>
                                <select name="part_no_separator" id="select_part_separator" class="form-control input-industrial border-success" style="font-size: 11px;">
                                    <option value="">-- TANPA SEPARATING (TIDAK ADA PASANGAN) --</option>
                                </select>
                                <div class="text-muted mt-1" style="font-size: 9.5px; line-height: 1.3;">
                                    Jika part Kiri/Kanan (cth: 35 & 36), pilih pasangannya. WOS akan otomatis <b>DIGABUNG JADI 1 BARIS</b> dan stok RM cuma dipotong sekali.
                                </div>
                            </div>

                            <div class="shift-toggle-pill mb-3 w-100">
                                <button type="button" id="m_btn_s1" class="shift-btn active-s1 w-50" style="background:#4361ee; color:white;" onclick="modalShiftSwitch(1)">SHIFT_1</button>
                                <button type="button" id="m_btn_s2" class="shift-btn w-50" style="background:transparent; color:#64748b;" onclick="modalShiftSwitch(2)">SHIFT_2</button>
                                <input type="hidden" name="active_shift_input" id="m_active_shift" value="1">
                            </div>
                            <div class="bg-light p-3 rounded-lg border">
                                <div id="m_box_s1">
                                    <label class="small font-weight-bold">S1 Reguler Plan</label>
                                    <input type="number" name="s1_plan_reg" id="s1_reg" class="form-control input-industrial mb-2 calc-trigger" value="0">
                                    <label class="small font-weight-bold text-warning">S1 Overtime Plan</label>
                                    <input type="number" name="s1_plan_ot" id="s1_ot" class="form-control input-industrial calc-trigger" value="0">
                                </div>
                                <div id="m_box_s2" style="display: none;">
                                    <label class="small font-weight-bold">S2 Reguler Plan</label>
                                    <input type="number" name="s2_plan_reg" id="s2_reg" class="form-control input-industrial mb-2 calc-trigger" value="0">
                                    <label class="small font-weight-bold text-warning">S2 Overtime Plan</label>
                                    <input type="number" name="s2_plan_ot" id="s2_ot" class="form-control input-industrial calc-trigger" value="0">
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <input type="hidden" name="plan_date" value="{{ $date }}">
                                <textarea name="remark" class="form-control" rows="2" style="border-radius:12px;" placeholder="Optional remark..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4 font-weight-bold shadow-sm" data-dismiss="modal">ABORT</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 font-weight-black shadow-lg">AUTHORIZE & DEPLOY</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function modalShiftSwitch(s) {
        const isS1 = s === 1;
        document.getElementById('m_btn_s1').style.background = isS1 ? '#4361ee' : 'transparent';
        document.getElementById('m_btn_s1').style.color = isS1 ? 'white' : '#64748b';

        document.getElementById('m_btn_s2').style.background = !isS1 ? '#0f172a' : 'transparent';
        document.getElementById('m_btn_s2').style.color = !isS1 ? 'white' : '#64748b';

        document.getElementById('m_box_s1').style.display = isS1 ? 'block' : 'none';
        document.getElementById('m_box_s2').style.display = isS1 ? 'none' : 'block';
        document.getElementById('m_active_shift').value = s;

        if (isS1) {
            document.getElementById('s2_reg').value = 0;
            document.getElementById('s2_ot').value = 0;
        } else {
            document.getElementById('s1_reg').value = 0;
            document.getElementById('s1_ot').value = 0;
        }

        calculateLiveHours();
    }

    function calculateLiveHours() {
        const cap = parseFloat(document.getElementById('input_cap').value) || 1;
        const dandory = parseFloat(document.getElementById('input_dandory').value) || 0;
        
        const s1 = (parseFloat(document.getElementById('s1_reg').value) || 0) + (parseFloat(document.getElementById('s1_ot').value) || 0);
        const s2 = (parseFloat(document.getElementById('s2_reg').value) || 0) + (parseFloat(document.getElementById('s2_ot').value) || 0);
        
        let hoursS1 = s1 > 0 ? (s1 / cap) + (dandory / 60) : 0;
        let hoursS2 = s2 > 0 ? (s2 / cap) + (dandory / 60) : 0;
        
        const label = document.getElementById('live_load_label');
        label.innerHTML = `<span class="text-primary">S1: ${hoursS1.toFixed(1)}H</span> <span class="text-muted mx-1">|</span> <span class="text-dark">S2: ${hoursS2.toFixed(1)}H</span>`;
        label.parentElement.className = (hoursS1 > 12 || hoursS2 > 12) ? "alert alert-danger border-0 rounded-lg text-center mt-3 py-3 shadow-sm" : "alert alert-info border-0 rounded-lg text-center mt-3 py-3 shadow-sm";
    }

    document.querySelectorAll('.calc-trigger, #input_cap, #input_dandory').forEach(i => i.addEventListener('input', calculateLiveHours));

    document.getElementById('select_customer').addEventListener('change', function() {
        fetch(`/get-parts-and-specs/${this.value}`).then(r => r.json()).then(data => {
            let html = '<option value="">-- SELECT PART --</option>';
            let htmlSep = '<option value="">-- TANPA SEPARATING (TIDAK ADA PASANGAN) --</option>';
            
            data.parts.forEach(p => {
                html += `<option value="${p.part_no}">${p.part_no} - ${p.part_name}</option>`;
                htmlSep += `<option value="${p.part_no}">${p.part_no} - ${p.part_name}</option>`;
            });
            
            document.getElementById('select_part').innerHTML = html;
            document.getElementById('select_part_separator').innerHTML = htmlSep;
        });
    });

    document.getElementById('select_line').addEventListener('change', function() {
        let type = this.options[this.selectedIndex].getAttribute('data-type');
        let capInput = document.getElementById('input_cap');
        let activeShift = document.getElementById('m_active_shift').value;
        
        if(type === 'BIG') { 
            capInput.value = 320; 
            if (activeShift == 1) {
                document.getElementById('s1_reg').value = 2400; document.getElementById('s2_reg').value = 0; 
            } else {
                document.getElementById('s1_reg').value = 0; document.getElementById('s2_reg').value = 2400; 
            }
        } else { 
            capInput.value = 500; 
            if (activeShift == 1) {
                document.getElementById('s1_reg').value = 4000; document.getElementById('s2_reg').value = 0;
            } else {
                document.getElementById('s1_reg').value = 0; document.getElementById('s2_reg').value = 4000;
            }
        }
        calculateLiveHours();
    });

    $(document).ready(function() { 
        $('.modal').each(function() {
            let submitBtn = $(this).find('button:contains("AUTHORIZE & DEPLOY")');
            if(submitBtn.length === 0) submitBtn = $(this).find('button[type="submit"]');
            
            // 🔥 HANYA tambah Box Peringatan RM jika modalnya adalah modal "Register Plan Baru"
            if (submitBtn.length > 0 && $(this).attr('id') === 'modalAddPlan' && $(this).find('.rmValidationMsg').length === 0) {
                submitBtn.parent().prepend('<div class="rmValidationMsg w-100 text-center mb-3" style="font-size:12px; transition: 0.3s;"></div>');
            }
        });
        
        function validateRmStock(modal) {
            let s1_reg = parseInt(modal.find('input[name="s1_plan_reg"]').val()) || 0;
            let s1_ot = parseInt(modal.find('input[name="s1_plan_ot"]').val()) || 0;
            let s2_reg = parseInt(modal.find('input[name="s2_plan_reg"]').val()) || 0;
            let s2_ot = parseInt(modal.find('input[name="s2_plan_ot"]').val()) || 0;
            
            let currentRmStock = modal.data('rm-stock') || 0;
            let currentCavity = modal.data('cavity') || 1;
            
            let totalPcs = s1_reg + s1_ot + s2_reg + s2_ot;
            let requiredSheet = Math.ceil(totalPcs / currentCavity);
            
            let submitBtn = modal.find('button:contains("AUTHORIZE & DEPLOY")');
            if(submitBtn.length === 0) submitBtn = modal.find('button[type="submit"]');
            let msgBox = modal.find('.rmValidationMsg');
            
            if (totalPcs === 0) {
                msgBox.html('<span class="text-muted font-weight-bold"><i class="fas fa-info-circle"></i> Input target qty untuk cek ketersediaan material.</span>');
                submitBtn.prop('disabled', true);
                return;
            }
            
            if (requiredSheet > currentRmStock) {
                msgBox.html(`
                    <div class="alert alert-danger p-2 m-0 border-0 shadow-sm animate__animated animate__headShake" style="border-radius:10px;">
                        <b>❌ STOK RM TIDAK CUKUP!</b><br>
                        Butuh: <b style="font-size:14px;">${requiredSheet}</b> Lembar | Tersedia: <b>${currentRmStock}</b> Lembar (Cav: ${currentCavity})
                    </div>
                `);
                submitBtn.prop('disabled', true).removeClass('btn-primary').addClass('btn-secondary');
            } else {
                msgBox.html(`
                    <div class="alert alert-success p-2 m-0 border-0 shadow-sm" style="border-radius:10px; background-color: #d1fae5; color: #065f46;">
                        <b>✅ STOK RM AMAN!</b><br>
                        Butuh: <b>${requiredSheet}</b> Lembar | Tersedia: <b>${currentRmStock}</b> Lembar (Cav: ${currentCavity})
                    </div>
                `);
                submitBtn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary');
            }
        }
        
        $('select[name="part_no"]').on('change', function() {
            let modal = $(this).closest('.modal'); 
            let partNo = $(this).val();
            if (!partNo || modal.attr('id') !== 'modalAddPlan') return; 
            
            let submitBtn = modal.find('button:contains("AUTHORIZE & DEPLOY")');
            if(submitBtn.length === 0) submitBtn = modal.find('button[type="submit"]');
            let msgBox = modal.find('.rmValidationMsg');
            
            msgBox.html('<span class="text-warning font-weight-bold"><i class="fas fa-spinner fa-spin"></i> Mengecek stok di Gudang RM...</span>');
            submitBtn.prop('disabled', true);
            
            $.get('/planning/ppic/check-rm-stock/' + encodeURIComponent(partNo), function(res) {
                if (res.status === 'success') {
                    modal.data('rm-stock', res.total_stock);
                    modal.data('cavity', res.cavity);
                    validateRmStock(modal);
                }
            }).fail(function() {
                msgBox.html('<span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle"></i> Gagal terhubung ke database gudang.</span>');
            });
        });
        
        $('input[name="s1_plan_reg"], input[name="s1_plan_ot"], input[name="s2_plan_reg"], input[name="s2_plan_ot"]').on('input', function() {
            let modal = $(this).closest('.modal');
            // 🔥 PASTIKAN JS INI HANYA MENGUNCI TOMBOL DI MODAL REGISTER (BUKAN MODAL EDIT!) 🔥
            if (modal.attr('id') === 'modalAddPlan') {
                validateRmStock(modal);
            }
        });
    });
</script>
@endsection