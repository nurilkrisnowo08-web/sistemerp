@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Orbitron:wght@600;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --p-primary: #3b82f6; --p-dark: #0f172a; --p-success: #10b981; 
        --p-danger: #ef4444; --p-warning: #f59e0b; --p-slate: #64748b;
        --glass-border: rgba(226, 232, 240, 0.8);
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: var(--p-dark); }
    
    .hud-title { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; }
    .glass-card { background: white; border-radius: 24px; border: 1px solid var(--glass-border); box-shadow: 0 10px 30px -5px rgba(0,0,0,0.03); overflow: hidden; transition: 0.3s; }
    .glass-card:hover { box-shadow: 0 15px 35px -5px rgba(0,0,0,0.06); transform: translateY(-3px); }
    
    .machine-switcher { background: #f1f5f9; padding: 6px; border-radius: 50px; display: inline-flex; border: 1px solid #e2e8f0; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); }
    .machine-switcher .btn { border-radius: 50px; font-weight: 800; padding: 10px 28px; font-size: 12px; letter-spacing: 0.5px; transition: 0.3s; text-transform: uppercase; }
    .machine-switcher .btn.active { background: var(--p-primary); color: white; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.35); }
    .machine-switcher .btn:not(.active) { color: var(--p-slate); }
    .machine-switcher .btn:not(.active):hover { background: #e2e8f0; color: var(--p-dark); }

    .card-grad-1 { background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border-left: 5px solid var(--p-primary); }
    .card-grad-2 { background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border-left: 5px solid var(--p-success); }
    .card-grad-3 { background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%); border-left: 5px solid var(--p-warning); }
    
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
    
    .rev-badge { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 900; margin-left: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    
    .t-mono { font-family: 'JetBrains Mono', monospace; font-weight: 800; }
    .btn-cyber { border-radius: 14px; font-weight: 800; padding: 10px 24px; font-size: 12px; letter-spacing: 0.5px; transition: 0.3s; }
    .btn-cyber:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }

    .anim-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    
    /* MODAL UI STAMPING */
    .input-industrial { border-radius: 8px; border: 1px solid #e2e8f0; padding: 10px 15px; font-weight: 700; font-size: 12px; transition: 0.3s; }
    .input-industrial:focus { border-color: var(--p-primary); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .input-industrial[readonly] { background: #f8fafc; color: var(--p-slate); border-color: #f1f5f9; cursor: not-allowed;}
    
    .shift-toggle-pill { display: flex; border: 1px solid var(--p-primary); border-radius: 8px; overflow: hidden; }
    .shift-btn { font-size: 11px; font-weight: 800; padding: 10px; border: none; cursor: pointer; transition: 0.2s; }
    
    .select2-container--default .select2-selection--single { background-color: #fff; border: 1px solid #e2e8f0; border-radius: 8px; height: 41px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 39px; font-weight: 700; font-size: 12px; padding-left: 15px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 39px; right: 10px; }
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

    {{-- HEADER & NAVIGATION --}}
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
        <div>
            <h2 class="hud-title m-0" style="font-size: 28px;">MPS_WELDING <span class="text-primary"> ENGINE</span></h2>
            <p class="text-muted small font-weight-bold uppercase mb-0 mt-1"><i class="far fa-calendar-alt mr-2"></i> Operational Date: <span class="text-dark">{{ date('d F Y', strtotime($date)) }}</span></p>
        </div>

        <div class="machine-switcher mx-xl-auto my-3 my-xl-0">
            <a href="?date={{$date}}&shift=S1" class="btn {{ $shift == 'S1' ? 'active' : '' }}"><i class="fas fa-sun mr-2"></i>SHIFT_01</a>
            <a href="?date={{$date}}&shift=S2" class="btn {{ $shift == 'S2' ? 'active' : '' }}"><i class="fas fa-moon mr-2"></i>SHIFT_02</a>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form action="" method="GET" class="mr-2">
                <input type="hidden" name="shift" value="{{ $shift }}">
                <input type="date" name="date" class="form-control rounded-pill border-dark px-4 shadow-sm font-weight-bold" style="height: 42px;" value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <button class="btn btn-primary btn-cyber shadow-sm" data-toggle="modal" data-target="#modalAddPlan">
                <i class="fas fa-plus-circle mr-2"></i> REGISTER PLAN
            </button>
        </div>
    </div>

    {{-- TOP CARDS --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="glass-card card-grad-1 p-4 text-center">
                <small class="text-muted font-weight-bold uppercase letter-spacing-1">Volume Target ({{ $shift }})</small>
                <h2 class="font-weight-bold mb-0 mt-2 text-primary" style="font-family: 'Orbitron'; font-size: 38px;">{{ number_format($totalPlanQty) }}</h2>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="glass-card card-grad-2 p-4 text-center">
                <small class="text-muted font-weight-bold uppercase letter-spacing-1">Load Capacity</small>
                <h4 class="font-weight-bold mb-0 mt-3 text-info font-mono" style="font-size: 26px;">
                    <span class="text-success">{{ number_format($totalWorkingHours, 1) }}H</span>
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
                <small class="text-info font-weight-bold uppercase letter-spacing-1">Operational Shift</small>
                <h2 class="font-weight-bold mb-0 mt-2" style="font-family: 'Orbitron'; font-size: 28px; padding-top: 5px;">{{ $shift == 'S1' ? 'DAY_OPS' : 'NIGHT_OPS' }}</h2>
            </div>
        </div>
    </div>

    {{-- DAFTAR MPS WELDING PER STATION --}}
    <div class="glass-card">
        <div class="table-responsive">
            <table class="table-mps text-center">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-left pl-4">Identification</th>
                        <th>MP</th>
                        <th>Quota</th>
                        <th>Actual</th>
                        <th>Gap</th>
                        <th class="text-primary">Start</th>
                        <th class="text-primary">Finish</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($plans->groupBy('line_code') as $lineCode => $linePlans)
                        <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; border-top: 4px solid #1e293b;">
                            <td colspan="8" class="text-left pl-4">
                                <h5 class="font-weight-bold text-dark m-0 uppercase py-2" style="letter-spacing: 1px; font-family: 'Orbitron';">
                                    <i class="fas fa-robot mr-2 text-primary"></i> STATION : {{ $lineCode }}
                                </h5>
                            </td>
                            <td colspan="2" class="text-right pr-4">
                                <div class="btn-group shadow-sm">
                                    <a href="{{ url('/ppic/welding/wos/print/'.$date.'/'.$lineCode) }}" class="btn btn-primary btn-sm font-weight-bold px-3" target="_blank" title="Print Kertas WOS">
                                        <i class="fas fa-print mr-1"></i> WOS
                                    </a>
                                    <a href="{{ url('/ppic/welding/bprm/print/'.$date.'/'.$lineCode) }}" class="btn btn-success btn-sm font-weight-bold px-3" target="_blank" title="Print BPRM">
                                        <i class="fas fa-file-alt mr-1"></i> BPRM
                                    </a>
                                </div>
                            </td>
                        </tr>

                        @foreach ($linePlans as $index => $p)
                            @php 
                                $percent = ($p->total_target > 0) ? ($p->total_actual / $p->total_target) * 100 : 0;
                                if ($p->balance > 0) {
                                    $balanceStatus = 'balance-danger'; $balanceIcon = 'fa-exclamation-triangle'; $balanceText = '-' . number_format($p->balance);
                                } else {
                                    $balanceStatus = 'balance-success'; $balanceIcon = 'fa-check-circle'; $balanceText = '+' . number_format(abs($p->balance));
                                }
                            @endphp
                            <tr>
                                <td class="text-muted t-mono">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="text-left pl-4 part-cell">
                                    <div class="font-weight-bold" style="font-size: 14px;">{{ $p->part_name }}</div>
                                    <small class="text-muted font-weight-bold mt-1 d-block">{{ $p->part_no }}</small>
                                </td>
                                <td><span class="badge badge-light border px-2 py-1">{{ $p->manpower }}P</span></td>
                                <td class="t-mono text-dark" style="font-size: 15px;">{{ number_format($p->total_target) }}</td>
                                <td class="t-mono text-primary" style="font-size: 15px;">{{ number_format($p->total_actual) }}</td>
                                <td>
                                    <span class="badge-balance {{ $balanceStatus }}">
                                        <i class="fas {{ $balanceIcon }} mr-1"></i> {{ $balanceText }}
                                    </span>
                                </td>
                                <td><span class="badge-time">{{ $p->start_time }}</span></td>
                                <td><span class="badge-time">{{ $p->ahir_time }}</span></td>
                                <td>
                                    @if($percent >= 100) <i class="fas fa-check-circle text-success fa-lg"></i>
                                    @elseif($percent > 0) <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                                    @else <i class="far fa-clock text-muted fa-lg"></i> @endif
                                </td>
                                <td>
                                    @if($p->total_actual > 0)
                                        <button class="btn btn-secondary btn-sm shadow-sm font-weight-bold px-3" disabled title="LOCKED">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-warning btn-sm shadow-sm font-weight-bold text-dark px-3" data-toggle="modal" data-target="#modalEditPlan-{{ $p->id }}" title="Revisi">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @empty
                    <tr>
                        <td colspan="10" class="py-5 text-center text-muted">
                            <div style="background: #f1f5f9; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                                <i class="fas fa-calendar-times fa-2x" style="color: #94a3b8;"></i>
                            </div>
                            <h6 class="font-weight-bold text-dark mb-1">TIDAK ADA JADWAL WELDING</h6>
                            <span style="font-size: 13px;">Klik tombol + REGISTER PLAN untuk menyusun jadwal.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- =========================================================================
     MODAL REGISTER NEW PLAN (100% PERSIS STAMPING UI & LOGIC)
     ========================================================================= --}}
<div class="modal fade" id="modalAddPlan" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 25px; overflow: hidden;">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title hud-title"><i class="fas fa-plus-circle mr-2"></i> REGISTER_PLAN_SYSTEM</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('ppic.welding.mps_store') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="row">
                        <!-- KOLOM 1: IDENTIFICATION -->
                        <div class="col-md-4 border-right">
                            <h6 class="font-weight-bold text-primary mb-3">01. PRODUCT_IDENTITY</h6>
                            
                            <div class="form-group">
                                <label class="small font-weight-bold">Tanggal Produksi</label>
                                <input type="date" name="plan_date" class="form-control input-industrial" value="{{ $date }}" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="small font-weight-bold">Select Line</label>
                                <select name="line_code" id="select_line" class="form-control input-industrial" required>
                                    <option value="">-- CHOOSE STATION --</option>
                                    @foreach ($availableLines as $l)
                                        <option value="{{ $l->kode_line }}">{{ $l->kode_line }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="small font-weight-bold">Customer (Auto)</label>
                                <input type="text" id="input_customer_auto" name="customer_code" class="form-control input-industrial font-weight-bold" value="AUTO DETECT" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label class="small font-weight-bold">Part Identification</label>
                                <select name="part_no" id="select_part" class="form-control select2-part" required style="width:100%;">
                                    <option value="">-- SELECT PART --</option>
                                    @foreach($availableParts as $p)
                                        @php
                                            $cleanPart = trim(urldecode($p->part_no));
                                            if (strpos($cleanPart, ' - ') !== false) $cleanPart = trim(explode(' - ', $cleanPart)[0]);
                                        @endphp
                                        <option value="{{ $cleanPart }}">[{{ $p->customer_code ?? 'SAI' }}] {{ $cleanPart }} - {{ $p->part_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- KOLOM 2: PERFORMANCE METRICS & M/C LOAD -->
                        <div class="col-md-4 border-right px-4">
                            <h6 class="font-weight-bold text-info mb-3">02. PERFORMANCE_METRICS</h6>
                            <div class="row">
                                <div class="col-6"><label class="small font-weight-bold">Manpower</label><input type="number" name="manpower" class="form-control input-industrial mb-3" value="1" required></div>
                                <div class="col-6"><label class="small font-weight-bold">Process</label><input type="number" name="process_qty" class="form-control input-industrial mb-3" value="1"></div>
                                <div class="col-6"><label class="small font-weight-bold text-primary">Cap/Hour</label><input type="number" name="cap_per_hour" id="input_cap" class="form-control input-industrial mb-3 font-weight-bold border-primary calc-trigger" value="400" required></div>
                                <div class="col-6"><label class="small font-weight-bold text-warning">Dandory (m)</label><input type="number" name="dandory_time" id="input_dandory" class="form-control input-industrial mb-3 calc-trigger" value="15"></div>
                            </div>
                            <div class="alert alert-info border-0 rounded-lg text-center mt-3 py-4 shadow-sm" style="background:#e0f2fe;">
                                <small class="d-block font-weight-bold uppercase mb-2" style="color:#0369a1;">Calculated M/C Load</small>
                                <h5 class="hud-title mb-0" id="live_load_label" style="font-size: 26px;">
                                    <span class="text-info">S1: 0.0H</span> <span class="text-muted mx-1">|</span> <span class="text-info">S2: 0.0H</span>
                                </h5>
                            </div>
                        </div>

                        <!-- KOLOM 3: QUANTITY PLANNING -->
                        <div class="col-md-4 pl-4">
                            <h6 class="font-weight-bold text-success mb-3">03. QUANTITY_PLANNING</h6>

                            <div class="shift-toggle-pill mb-4 w-100">
                                <button type="button" id="m_btn_s1" class="shift-btn active-s1 w-50" style="background:#4361ee; color:white;" onclick="modalShiftSwitch(1)">SHIFT_1</button>
                                <button type="button" id="m_btn_s2" class="shift-btn w-50" style="background:transparent; color:#64748b;" onclick="modalShiftSwitch(2)">SHIFT_2</button>
                                <input type="hidden" name="active_shift_input" id="m_active_shift" value="1">
                            </div>
                            
                            <div class="bg-light p-3 rounded-lg border">
                                <div id="m_box_s1">
                                    <label class="small font-weight-bold text-primary">S1 Reguler Plan</label>
                                    <input type="number" name="s1_plan_reg" id="s1_reg" class="form-control input-industrial mb-3 calc-trigger" value="0">
                                    <label class="small font-weight-bold text-danger">S1 Overtime Plan</label>
                                    <input type="number" name="s1_plan_ot" id="s1_ot" class="form-control input-industrial calc-trigger" value="0">
                                </div>
                                <div id="m_box_s2" style="display: none;">
                                    <label class="small font-weight-bold text-primary">S2 Reguler Plan</label>
                                    <input type="number" name="s2_plan_reg" id="s2_reg" class="form-control input-industrial mb-3 calc-trigger" value="0">
                                    <label class="small font-weight-bold text-danger">S2 Overtime Plan</label>
                                    <input type="number" name="s2_plan_ot" id="s2_ot" class="form-control input-industrial calc-trigger" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light p-4 flex-column">
                    <!-- TEMPAT KOTAK PERINGATAN (AJAX/REALTIME MUNCUL DISINI) -->
                    <div class="wipValidationMsg w-100 text-center mb-3" style="font-size:12px; transition: 0.3s;"></div>
                    
                    <div class="d-flex justify-content-end w-100">
                        <button type="button" class="btn btn-secondary rounded-pill px-4 font-weight-bold shadow-sm mr-2" data-dismiss="modal">ABORT</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 font-weight-black shadow-lg">AUTHORIZE & DEPLOY</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. KUMPULAN MODAL EDIT REVISI --}}
@foreach ($plans as $p)
    @if($p->total_actual == 0)
    @php
        $oldTargetS1 = $p->s1_plan_reg + $p->s1_plan_ot;
        $oldTargetS2 = $p->s2_plan_reg + $p->s2_plan_ot;
    @endphp
    <div class="modal fade" id="modalEditPlan-{{ $p->id }}" tabindex="-1" role="dialog" data-old-target="{{ $oldTargetS1 + $oldTargetS2 }}">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header bg-warning text-dark p-4 border-0">
                    <h6 class="modal-title font-weight-bold uppercase" style="letter-spacing: 1px;"><i class="fas fa-tools mr-2"></i> Revisi WOS & Sync Material</h6>
                    <button type="button" class="close text-dark" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('ppic.welding.update_wos', $p->id) }}" method="POST">
                    @csrf @method('PUT')
                    
                    <div class="modal-body p-4 p-md-5 bg-white">
                        <div class="row">
                            <!-- KOLOM 1 -->
                            <div class="col-md-4 border-right">
                                <h6 class="font-weight-bold text-warning mb-3">01. PRODUCT_IDENTITY</h6>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Line Code</label>
                                    <select name="line_code" class="form-control input-industrial" required>
                                        @foreach ($availableLines as $l)
                                            <option value="{{ $l->kode_line }}" {{ $p->line_code == $l->kode_line ? 'selected' : '' }}>{{ $l->kode_line }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Part No (Locked)</label>
                                    <input type="text" name="part_no" class="form-control input-industrial font-weight-bold bg-light text-muted" value="{{ $p->part_no }}" readonly>
                                </div>
                            </div>
                            
                            <!-- KOLOM 2 -->
                            <div class="col-md-4 border-right px-4">
                                <h6 class="font-weight-bold text-info mb-3">02. PERFORMANCE_METRICS</h6>
                                <div class="row">
                                    <div class="col-6"><label class="small font-weight-bold">Manpower</label><input type="number" name="manpower" class="form-control input-industrial mb-3" value="{{ $p->manpower }}"></div>
                                    <div class="col-6"><label class="small font-weight-bold text-primary">Cap/Hour</label><input type="number" name="cap_per_hour" id="input_cap_{{ $p->id }}" class="form-control input-industrial mb-3 border-primary calc-trigger-{{ $p->id }}" value="{{ $p->cap_per_hour }}" required></div>
                                    <div class="col-12"><label class="small font-weight-bold text-warning">Dandory (m)</label><input type="number" name="dandory_time" id="input_dandory_{{ $p->id }}" class="form-control input-industrial mb-3 calc-trigger-{{ $p->id }}" value="{{ $p->dandory_time ?? 15 }}"></div>
                                </div>
                                <div class="alert alert-warning border-0 rounded-lg text-center mt-1 py-3 shadow-sm">
                                    <small class="d-block font-weight-bold uppercase mb-1">Calculated M/C Load</small>
                                    <h5 class="hud-title mb-0" id="live_load_label_{{ $p->id }}">
                                        <span class="text-primary">S1: 0.0H</span> <span class="text-muted mx-1">|</span> <span class="text-dark">S2: 0.0H</span>
                                    </h5>
                                </div>
                            </div>
                            
                            <!-- KOLOM 3 -->
                            <div class="col-md-4 pl-4">
                                <h6 class="font-weight-bold text-success mb-3">03. QUANTITY_REVISION</h6>
                                <div class="shift-toggle-pill mb-3 w-100">
                                    <button type="button" id="m_btn_s1_{{ $p->id }}" class="shift-btn w-50" style="background:#4361ee; color:white;" onclick="modalShiftSwitchRev({{ $p->id }}, 1)">SHIFT_1</button>
                                    <button type="button" id="m_btn_s2_{{ $p->id }}" class="shift-btn w-50" style="background:transparent; color:#64748b;" onclick="modalShiftSwitchRev({{ $p->id }}, 2)">SHIFT_2</button>
                                    <input type="hidden" id="m_active_shift_{{ $p->id }}" value="1">
                                </div>
                                
                                <div class="bg-light p-3 rounded-lg border">
                                    <div id="m_box_s1_{{ $p->id }}">
                                        <label class="small font-weight-bold">S1 Reguler Plan</label>
                                        <input type="number" name="s1_plan_reg" id="s1_reg_{{ $p->id }}" class="form-control input-industrial mb-2 calc-trigger-{{ $p->id }}" value="{{ $p->s1_plan_reg }}">
                                        <label class="small font-weight-bold text-warning">S1 Overtime Plan</label>
                                        <input type="number" name="s1_plan_ot" id="s1_ot_{{ $p->id }}" class="form-control input-industrial calc-trigger-{{ $p->id }}" value="{{ $p->s1_plan_ot }}">
                                    </div>
                                    <div id="m_box_s2_{{ $p->id }}" style="display: none;">
                                        <label class="small font-weight-bold">S2 Reguler Plan</label>
                                        <input type="number" name="s2_plan_reg" id="s2_reg_{{ $p->id }}" class="form-control input-industrial mb-2 calc-trigger-{{ $p->id }}" value="{{ $p->s2_plan_reg }}">
                                        <label class="small font-weight-bold text-warning">S2 Overtime Plan</label>
                                        <input type="number" name="s2_plan_ot" id="s2_ot_{{ $p->id }}" class="form-control input-industrial calc-trigger-{{ $p->id }}" value="{{ $p->s2_plan_ot }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer bg-light border-0 p-4 flex-column">
                        <div class="wipValidationMsg w-100 text-center mb-3" style="font-size:12px; transition: 0.3s;"></div>
                        <div class="d-flex justify-content-end w-100">
                            <button type="button" class="btn btn-secondary font-weight-bold border rounded-pill px-4 mr-2" data-dismiss="modal">BATAL</button>
                            <button type="submit" class="btn btn-warning font-weight-bold shadow-sm text-dark rounded-pill px-4"><i class="fas fa-sync-alt mr-2"></i> AUTHORIZE REVISION</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // ==========================================
    // FUNGSI TOGGLE SHIFT TABS (BARU)
    // ==========================================
    function modalShiftSwitch(s) {
        const isS1 = s === 1;
        document.getElementById('m_btn_s1').style.background = isS1 ? '#4361ee' : 'transparent';
        document.getElementById('m_btn_s1').style.color = isS1 ? 'white' : '#64748b';
        document.getElementById('m_btn_s2').style.background = !isS1 ? '#0f172a' : 'transparent';
        document.getElementById('m_btn_s2').style.color = !isS1 ? 'white' : '#64748b';
        document.getElementById('m_box_s1').style.display = isS1 ? 'block' : 'none';
        document.getElementById('m_box_s2').style.display = isS1 ? 'none' : 'block';
        document.getElementById('m_active_shift').value = s;
        // Hanya visual hide/show, tidak me-reset input agar data tidak hilang saat pindah tab
        calculateLiveHours();
    }

    function modalShiftSwitchRev(id, s) {
        const isS1 = s === 1;
        document.getElementById('m_btn_s1_' + id).style.background = isS1 ? '#4361ee' : 'transparent';
        document.getElementById('m_btn_s1_' + id).style.color = isS1 ? 'white' : '#64748b';
        document.getElementById('m_btn_s2_' + id).style.background = !isS1 ? '#0f172a' : 'transparent';
        document.getElementById('m_btn_s2_' + id).style.color = !isS1 ? 'white' : '#64748b';
        document.getElementById('m_box_s1_' + id).style.display = isS1 ? 'block' : 'none';
        document.getElementById('m_box_s2_' + id).style.display = isS1 ? 'none' : 'block';
        document.getElementById('m_active_shift_' + id).value = s;
    }

    // ==========================================
    // FUNGSI HITUNG M/C LOAD UNTUK NEW PLAN
    // ==========================================
    function calculateLiveHours() {
        const cap = parseFloat(document.getElementById('input_cap').value) || 1;
        const dandory = parseFloat(document.getElementById('input_dandory').value) || 0;
        
        const s1 = (parseFloat(document.getElementById('s1_reg').value) || 0) + (parseFloat(document.getElementById('s1_ot').value) || 0);
        const s2 = (parseFloat(document.getElementById('s2_reg').value) || 0) + (parseFloat(document.getElementById('s2_ot').value) || 0);
        
        let hoursS1 = s1 > 0 ? (s1 / cap) + (dandory / 60) : 0;
        let hoursS2 = s2 > 0 ? (s2 / cap) + (dandory / 60) : 0;
        
        const label = document.getElementById('live_load_label');
        label.innerHTML = `<span class="text-primary">S1: ${hoursS1.toFixed(1)}H</span> <span class="text-muted mx-1">|</span> <span class="text-dark">S2: ${hoursS2.toFixed(1)}H</span>`;
    }

    // ==========================================
    // MAIN SCRIPT (AJAX STOK & EVENT LISTENER)
    // ==========================================
    $(document).ready(function() { 
        // 1. SELECT2 INIT
        $('.select2-part').select2({
            dropdownParent: $('#modalAddPlan'),
            placeholder: "-- SELECT PART --",
            allowClear: true,
            width: '100%'
        });

        // 2. TRIGGER M/C LOAD SAAT NGETIK (NEW PLAN)
        $('.calc-trigger, #input_cap, #input_dandory').on('input', function() {
            calculateLiveHours();
            validateWipStock($('#modalAddPlan'));
        });

        // 3. FUNGSI UTAMA VALIDASI STOK (MUNCULIN HIJAU/MERAH SECARA REALTIME)
        function validateWipStock(modal) {
            // Cek ini modal yang mana (New Plan atau Revisi)
            let isRev = modal.attr('id').includes('modalEditPlan');
            let idSuffix = isRev ? '_' + modal.attr('id').split('-')[1] : '';
            
            let s1_reg = parseInt(modal.find('#s1_reg' + idSuffix).val()) || 0;
            let s1_ot = parseInt(modal.find('#s1_ot' + idSuffix).val()) || 0;
            let s2_reg = parseInt(modal.find('#s2_reg' + idSuffix).val()) || 0;
            let s2_ot = parseInt(modal.find('#s2_ot' + idSuffix).val()) || 0;
            
            let currentWipStock = modal.data('wip-stock') || 0;
            let totalPcs = s1_reg + s1_ot + s2_reg + s2_ot;
            
            let submitBtn = modal.find('button:contains("AUTHORIZE")');
            if(submitBtn.length === 0) submitBtn = modal.find('button[type="submit"]');
            let msgBox = modal.find('.wipValidationMsg');
            
            if (totalPcs === 0) {
                if(isRev) {
                    msgBox.html(`
                        <div class="alert alert-warning p-2 m-0 border-0 shadow-sm animate__animated animate__pulse" style="border-radius:10px; background-color: #fffbeb; color: #b45309;">
                            <b>⚠️ JADWAL DIBATALKAN</b><br>
                            Simpan untuk menghapus jadwal. <b style="font-size:12px;">${currentWipStock}</b> Pcs WIP akan kembali ke gudang.
                        </div>
                    `);
                    submitBtn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-danger').html('<i class="fas fa-trash mr-2"></i> CONFIRM CANCEL');
                } else {
                    msgBox.html('<span class="text-muted font-weight-bold"><i class="fas fa-info-circle"></i> Input target qty (Shift 1 atau Shift 2) untuk cek ketersediaan material.</span>');
                    submitBtn.prop('disabled', true);
                }
                return;
            }
            
            if (totalPcs > currentWipStock) {
                // UI MERAH KALO STOK KURANG
                msgBox.html(`
                    <div class="alert alert-danger p-2 m-0 border-0 shadow-sm animate__animated animate__headShake" style="border-radius:10px;">
                        <b>❌ PLAN DITOLAK: STOK WIP KURANG!</b><br>
                        Butuh: <b style="font-size:14px;">${totalPcs}</b> Pcs | Tersedia: <b>${currentWipStock}</b> Pcs
                    </div>
                `);
                submitBtn.prop('disabled', true).removeClass('btn-primary btn-warning btn-danger').addClass('btn-secondary');
            } else {
                // UI HIJAU KALO STOK AMAN
                msgBox.html(`
                    <div class="alert alert-success p-2 m-0 border-0 shadow-sm" style="border-radius:10px; background-color: #d1fae5; color: #065f46;">
                        <b>✅ STOK WIP AMAN!</b><br>
                        Butuh: <b>${totalPcs}</b> Pcs | Tersedia: <b>${currentWipStock}</b> Pcs
                    </div>
                `);
                if (isRev) {
                    submitBtn.prop('disabled', false).removeClass('btn-secondary btn-danger').addClass('btn-warning').html('<i class="fas fa-sync-alt mr-2"></i> AUTHORIZE REVISION');
                } else {
                    submitBtn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary');
                }
            }
        }
        
        // 4. AJAX CHECKER SAAT PILIH PART (NEW PLAN)
        $('select[name="part_no"]').on('change', function() {
            let modal = $(this).closest('.modal'); 
            let partNo = $(this).val();
            if (!partNo || modal.attr('id') !== 'modalAddPlan') return; 
            
            let submitBtn = modal.find('button:contains("AUTHORIZE & DEPLOY")');
            if(submitBtn.length === 0) submitBtn = modal.find('button[type="submit"]');
            let msgBox = modal.find('.wipValidationMsg');
            
            msgBox.html('<span class="text-primary font-weight-bold"><i class="fas fa-spinner fa-spin"></i> Mengecek ketersediaan WIP di database...</span>');
            submitBtn.prop('disabled', true);
            
            // JALUR AJAX LARAVEL
            $.get("{{ url('planning/ppic/check-wip-stock') }}/" + encodeURIComponent(partNo), function(res) {
                if (res.status === 'success') {
                    modal.data('wip-stock', res.wip_stock);
                    $('#input_customer_auto').val(res.customer_code); // Tulis nama customer
                    validateWipStock(modal); // Langsung cek angka yang ada di inputan bawah
                }
            }).fail(function() {
                msgBox.html('<span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle"></i> Gagal terhubung ke server/rute tidak ditemukan.</span>');
            });
        });

        // 5. EVENT LISTENER REVISI (NGITUNG JAM & AJAX STOK)
        @foreach($plans as $plan)
            @if($plan->total_actual == 0)
            
            // A. Update Jam M/C Load (Revisi)
            $('.calc-trigger-{{ $plan->id }}').on('input', function() { 
                let s1r = parseInt($('#modalEditPlan-{{ $plan->id }} #s1_reg_{{ $plan->id }}').val()) || 0;
                let s1o = parseInt($('#modalEditPlan-{{ $plan->id }} #s1_ot_{{ $plan->id }}').val()) || 0;
                let s2r = parseInt($('#modalEditPlan-{{ $plan->id }} #s2_reg_{{ $plan->id }}').val()) || 0;
                let s2o = parseInt($('#modalEditPlan-{{ $plan->id }} #s2_ot_{{ $plan->id }}').val()) || 0;
                let cap = parseFloat($('#modalEditPlan-{{ $plan->id }} #input_cap_{{ $plan->id }}').val()) || 1;
                let dandory = parseFloat($('#modalEditPlan-{{ $plan->id }} #input_dandory_{{ $plan->id }}').val()) || 0;
                
                let s1 = s1r + s1o;
                let s2 = s2r + s2o;
                
                let h1 = s1 > 0 ? (s1 / cap) + (dandory / 60) : 0;
                let h2 = s2 > 0 ? (s2 / cap) + (dandory / 60) : 0;
                
                $('#live_load_label_{{ $plan->id }}').html(`<span class="text-primary">S1: ${h1.toFixed(1)}H</span> <span class="text-muted mx-1">|</span> <span class="text-dark">S2: ${h2.toFixed(1)}H</span>`);
                
                validateWipStock($('#modalEditPlan-{{ $plan->id }}'));
            });

            // B. Tarik Stok Maksimal saat Modal Revisi Muncul
            $('#modalEditPlan-{{ $plan->id }}').on('shown.bs.modal', function () {
                let modal = $(this);
                let partNo = '{{ $plan->part_no }}';
                let oldTarget = modal.data('old-target') || 0;
                let msgBox = modal.find('.wipValidationMsg');
                let submitBtn = modal.find('button[type="submit"]');
                
                msgBox.html('<span class="text-warning font-weight-bold"><i class="fas fa-spinner fa-spin"></i> Sinkronisasi stok WIP...</span>');
                submitBtn.prop('disabled', true);
                
                $.get("{{ url('planning/ppic/check-wip-stock') }}/" + encodeURIComponent(partNo), function(res) {
                    if (res.status === 'success') {
                        let maxAvailable = res.wip_stock + oldTarget;
                        modal.data('wip-stock', maxAvailable);
                        validateWipStock(modal);
                        // Trigger jam biar nyesuaikan data pas modal dibuka
                        $('.calc-trigger-{{ $plan->id }}').first().trigger('input');
                    }
                }).fail(function() {
                    msgBox.html('<span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle"></i> Gagal tarik stok gudang.</span>');
                });
            });
            @endif
        @endforeach
    });
</script>
@endsection