@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --app-bg: #f4f7fe;
        --ind-navy: #0f172a;
        --ind-blue: #4318FF;
        --ind-slate: #64748b;
        --ind-border: #e2e8f0;
    }
    body { background-color: var(--app-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--ind-navy); }
    
    /* MODULAR GELEMBUNG (BUBBLE) */
    .bubble-header { background: linear-gradient(135deg, #ffffff, #fdfaf6); border-radius: 30px; padding: 25px 35px; border: 1px solid rgba(226,232,240,0.8); box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 25px; }
    .heading-title { font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 24px; letter-spacing: -0.5px; color: var(--ind-navy); margin-bottom: 5px; }
    
    .kpi-card { background: white; border-radius: 24px; padding: 20px 25px; border: 1px solid var(--ind-border); transition: all 0.3s ease; position: relative; overflow: hidden; box-shadow: 0 8px 20px rgba(0,0,0,0.02); min-height: 110px;}
    .kpi-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(67, 24, 255, 0.08); }
    .kpi-card::after { content: ""; position: absolute; bottom: 0; left: 0; right: 0; height: 5px; background: var(--accent-color, var(--ind-blue)); }
    .kpi-label { font-size: 10px; font-weight: 900; color: var(--ind-slate); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
    .kpi-value { font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 1.8rem; color: var(--ind-navy); }
    
    .station-panel { background: white; border-radius: 24px; border: 1px solid var(--ind-border); overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.03); margin-bottom: 30px; }
    .station-header { background: #f8fafc; padding: 18px 25px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; }
    
    .table-mps thead th { background: #f8fafc; color: var(--ind-slate); font-size: 10.5px; font-weight: 900; text-transform: uppercase; padding: 18px; border: none; letter-spacing: 0.5px; }
    .table-mps tbody td { padding: 18px; vertical-align: middle; border-bottom: 1px dashed #e2e8f0; font-weight: 700; font-size: 13px; }
    .table-mps tbody tr:hover td { background-color: #fcfdfd; }
    
    /* BUTTON & PILL */
    .shift-toggle { background: #e2e8f0; border-radius: 50px; padding: 5px; display: inline-flex; }
    .shift-btn { border-radius: 50px; padding: 8px 24px; font-weight: 900; font-size: 11px; transition: 0.3s; border: none; color: #64748b; text-decoration: none; }
    .shift-btn.active { background: var(--ind-navy); color: white; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); }
    .shift-btn:hover { text-decoration: none; }
    
    .btn-pill { border-radius: 50rem; padding: 10px 24px; font-weight: 900; font-size: 11.5px; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: none; }
    .btn-new-plan { background: linear-gradient(135deg, #4318FF, #5c67f2); color: white; box-shadow: 0 6px 15px rgba(67, 24, 255, 0.3); }
    .btn-new-plan:hover { transform: translateY(-2px); color: white; box-shadow: 0 8px 20px rgba(67, 24, 255, 0.4); }
    .btn-revisi { background: #fffbeb; color: #f59e0b; border: 1px solid #fde68a; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.1); }
    .btn-revisi:hover { background: #f59e0b; color: white; }
    
    .time-chip { font-family: 'JetBrains Mono'; background: #f1f5f9; color: var(--ind-slate); padding: 5px 12px; border-radius: 10px; font-size: 12px; font-weight: 800; }
    .font-mono { font-family: 'JetBrains Mono', monospace; font-weight: 800; }
    
    /* MODAL GELEMBUNG */
    .modal-content.bubble-modal { border-radius: 32px; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.15); overflow: hidden; }
    .modal-header.bubble-header-modal { border-bottom: none; padding: 25px 35px 10px 35px; }
    .tech-input-group { background: #ffffff; padding: 25px; border-radius: 24px; border: 1px solid #f1f5f9; box-shadow: 0 4px 15px rgba(0,0,0,0.02); height: 100%; transition: 0.3s; }
    .tech-input-group:hover { border-color: #e2e8f0; }
    .form-control.bubble-input { border-radius: 16px; border: 2px solid #f1f5f9; padding: 12px 20px; font-weight: 800; background: #f8fafc; color: #1e293b; transition: 0.3s; }
    .form-control.bubble-input:focus { border-color: var(--ind-blue); background: #fff; box-shadow: 0 0 0 4px rgba(67, 24, 255, 0.1); }
</style>

<div class="container-fluid py-4 px-lg-5 animate__animated animate__fadeIn">
    
    @if(session('success')) 
    <div class="alert alert-success border-0 shadow-sm animate__animated animate__bounceIn" style="border-radius: 20px; background:#ecfdf5; color:#047857; font-weight:800;">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div> 
    @endif
    
    @if(session('error')) 
    <div class="alert alert-danger border-0 shadow-sm animate__animated animate__shakeX" style="border-radius: 20px; background:#fef2f2; color:#b91c1c; font-weight:800;">
        <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
    </div> 
    @endif

    <div class="bubble-header d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div>
            <h2 class="heading-title">MPS_WELDING <span style="color: var(--ind-blue);">ENGINE</span></h2>
            <div class="d-flex align-items-center mt-1">
                <span class="badge badge-dark px-3 py-1 mr-3" style="border-radius: 8px; font-weight:800;">SYSTEM_ACTIVE</span>
                <span style="font-size:12px; font-weight:800; color:var(--ind-slate);"><i class="far fa-calendar-alt mr-1"></i> {{ date('D, d M Y', strtotime($date)) }}</span>
            </div>
        </div>

        <div class="d-flex align-items-center mt-3 mt-md-0 gap-3">
            <div class="shift-toggle mr-3">
                <a href="?date={{$date}}&shift=S1" class="shift-btn {{ $shift == 'S1' ? 'active' : '' }}">SHIFT_01</a>
                <a href="?date={{$date}}&shift=S2" class="shift-btn {{ $shift == 'S2' ? 'active' : '' }}">SHIFT_02</a>
            </div>
            <input type="date" class="form-control bubble-input px-4 mr-3" value="{{ $date }}" style="width: 170px; cursor:pointer;" onchange="location.href='?date='+this.value+'&shift={{$shift}}'">
            
            <button class="btn-pill btn-new-plan" data-toggle="modal" data-target="#modalRegisterPlan">
                <i class="fas fa-plus"></i> NEW_PLAN
            </button>
            <a href="{{ route('ppic.welding.print_report', ['date' => $date]) }}" target="_blank" class="btn-pill ml-2" style="background:#05CD99; color:white; box-shadow:0 6px 15px rgba(5,205,153,0.3); text-decoration:none;">
                <i class="fas fa-print"></i> REPORT
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 mb-3"><div class="kpi-card" style="--accent-color: var(--ind-blue)"><div class="kpi-label">Volume Target</div><div class="kpi-value" style="color:var(--ind-blue);">{{ number_format($totalPlanQty) }}<small class="ml-1 text-muted" style="font-size: 12px">PCS</small></div></div></div>
        <div class="col-md-3 mb-3"><div class="kpi-card" style="--accent-color: #8b5cf6"><div class="kpi-label">Load Capacity</div><div class="kpi-value" style="color: #8b5cf6">{{ number_format($totalWorkingHours, 1) }}<small class="ml-1 text-muted" style="font-size: 12px">HRS</small></div></div></div>
        <div class="col-md-3 mb-3"><div class="kpi-card" style="--accent-color: #f59e0b"><div class="kpi-label">Switching Loss</div><div class="kpi-value" style="color: #f59e0b">{{ $totalDandory }}<small class="ml-1 text-muted" style="font-size: 12px">MIN</small></div></div></div>
        <div class="col-md-3 mb-3"><div class="kpi-card bg-dark-slate border-0" style="background:linear-gradient(135deg, #1e293b, #334155);"><div class="kpi-label" style="color:#94a3b8;">Operational Shift</div><div class="kpi-value text-white">{{ $shift == 'S1' ? 'DAY_OPS' : 'NIGHT_OPS' }}</div></div></div>
    </div>

    @forelse($plans->groupBy('line_code') as $lineCode => $linePlans)
    <div class="station-panel animate__animated animate__fadeInUp">
        <div class="station-header">
            <h6 class="mb-0" style="font-weight:900; font-size:16px; color:var(--ind-navy);">
                <div style="display:inline-flex; align-items:center; justify-content:center; background:#e0e7ff; color:var(--ind-blue); width:35px; height:35px; border-radius:12px; margin-right:10px;">
                    <i class="fas fa-robot"></i>
                </div>
                STATION: {{ $lineCode }}
            </h6>
            <div>
                <a href="{{ url('/ppic/welding/wos/print/'.$date.'/'.$lineCode) }}" target="_blank" class="btn btn-sm px-3 mr-2 font-weight-bold" style="background:#f1f5f9; color:var(--ind-slate); border-radius:50rem;"><i class="fas fa-print mr-1"></i> PRINT WOS</a>
                <a href="{{ url('/ppic/welding/bprm/print/'.$date.'/'.$lineCode) }}" target="_blank" class="btn btn-sm px-3 font-weight-bold" style="background:#fef3c7; color:#d97706; border-radius:50rem;"><i class="fas fa-file-alt mr-1"></i> PRINT BPRM</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-mps mb-0">
                <thead>
                    <tr>
                        <th class="text-center">Pos</th>
                        <th class="text-left">Part Identification</th>
                        <th class="text-center">M/P</th>
                        <th class="text-center">Quota</th>
                        <th class="text-center">Actual</th>
                        <th class="text-center">Gap</th>
                        <th class="text-center">T-Start</th>
                        <th class="text-center">T-Finish</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($linePlans as $index => $plan)
                    @php $percent = ($plan->total_target > 0) ? ($plan->total_actual / $plan->total_target) * 100 : 0; @endphp
                    <tr>
                        <td class="text-center text-muted font-mono">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="text-left">
                            <div style="font-weight:900; color:var(--ind-navy); font-size:13.5px;">{{ $plan->part_name }}</div>
                            <div class="font-mono" style="font-size:10px; color:var(--ind-slate);">{{ $plan->part_no }}</div>
                        </td>
                        <td class="text-center"><span class="badge" style="background:#f1f5f9; color:var(--ind-slate); border-radius:6px; padding:5px 8px;">{{ $plan->manpower }}P</span></td>
                        <td class="text-center font-mono">{{ number_format($plan->total_target) }}</td>
                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center">
                                <span class="font-mono text-success">{{ number_format($plan->total_actual) }}</span>
                                <div style="height:4px; width:60px; background:#f1f5f9; border-radius:10px; margin-top:5px; overflow:hidden;">
                                    <div style="height:100%; width:{{ min($percent, 100) }}%; background:#10b981;"></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center font-mono">
                            @if($plan->balance > 0) <span style="color:#ef4444; background:#fef2f2; padding:4px 8px; border-radius:6px;">-{{ number_format($plan->balance) }}</span>
                            @else <span style="color:#10b981;">+{{ abs($plan->balance) }}</span> @endif
                        </td>
                        <td class="text-center"><span class="time-chip">{{ $plan->start_time }}</span></td>
                        <td class="text-center"><span class="time-chip" style="background:#e0e7ff; color:var(--ind-blue); border-color:#c7d2fe;">{{ $plan->ahir_time }}</span></td>
                        <td class="text-center">
                            @if($percent >= 100) <i class="fas fa-check-circle" style="color:#10b981; font-size:18px;"></i>
                            @elseif($percent > 0) <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                            @else <i class="far fa-clock text-muted" style="font-size:18px;"></i> @endif
                        </td>
                        <td class="text-center">
                            @if($plan->total_actual > 0)
                                <button class="btn btn-sm text-muted rounded-pill px-3 shadow-none border" style="background:#f1f5f9; cursor:not-allowed;" title="LOCKED: Already in Process"><i class="fas fa-lock"></i></button>
                            @else
                                <button class="btn-pill btn-revisi" data-toggle="modal" data-target="#modalEditPlan{{ $plan->id }}"><i class="fas fa-edit"></i> REVISI</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="bubble-header text-center py-5" style="border: 2px dashed #cbd5e1; background: transparent; box-shadow:none;">
        <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3" style="filter: grayscale(1); opacity:0.3;">
        <h5 style="font-weight:900; color:var(--ind-slate);">TIDAK ADA JADWAL WELDING</h5>
        <p class="text-muted mb-0" style="font-weight:600;">Klik tombol <b style="color:var(--ind-blue);">+ NEW_PLAN</b> untuk menyusun jadwal.</p>
    </div>
    @endforelse
</div>


{{-- ========================================================================================= --}}
{{-- ⚠️ EVAKUASI MODAL: SEMUA MODAL WAJIB DI LUAR TABEL AGAR TIDAK MENGALAMI BUG UI & KLIK ⚠️ --}}
{{-- ========================================================================================= --}}

{{-- ✨ 1. MODAL REGISTER NEW PLAN ✨ --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalRegisterPlan" tabindex="-1" style="z-index: 9999 !important;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bubble-modal">
            <div class="modal-header bubble-header-modal" style="background:var(--ind-navy);">
                <h5 class="modal-title font-weight-black text-white" style="font-family:'Orbitron'; letter-spacing:1px;">
                    <i class="fas fa-plus-circle mr-2" style="color:var(--ind-blue);"></i> REGISTER_NEW_PLAN
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity:1;"><span>&times;</span></button>
            </div>
            
            <form action="{{ route('ppic.welding.mps_store') }}" method="POST">
                @csrf
                <div class="modal-body p-4" style="background:#f8fafc;">
                    <div class="row">
                        <!-- BOX 1: Identifikasi -->
                        <div class="col-md-4 mb-3">
                            <div class="tech-input-group">
                                <h6 class="font-weight-black mb-4"><i class="fas fa-tag mr-2" style="color:var(--ind-blue);"></i>01. IDENTIFICATION</h6>
                                <div class="form-group">
                                    <label class="small font-weight-bold text-muted">Tanggal Produksi</label>
                                    <input type="date" name="plan_date" class="form-control bubble-input" value="{{ $date }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold text-muted">Station Code</label>
                                    <select name="line_code" class="form-control bubble-input" required>
                                        <option value="">Pilih Station...</option>
                                        @foreach($availableLines as $line)
                                            <option value="{{ $line->kode_line }}">{{ $line->kode_line }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold text-muted">Customer (Auto)</label>
                                    <input type="text" id="custDisp" class="form-control bubble-input" value="AUTO DETECT" readonly style="background:#e2e8f0; color:var(--ind-slate);">
                                </div>
                            </div>
                        </div>

                        <!-- BOX 2: Pencarian Part & WIP -->
                        <div class="col-md-4 mb-3">
                            <div class="tech-input-group">
                                <h6 class="font-weight-black mb-4"><i class="fas fa-search mr-2 text-warning"></i>02. MATERIAL & WIP</h6>
                                <div class="form-group">
                                    <label class="small font-weight-bold text-muted">Pilih Part Number</label>
                                    <select name="part_no" id="partSelector" class="form-control select2-part" required style="width:100%;">
                                        <option value="">Ketik Part No / Nama...</option>
                                        @foreach($availableParts as $p)
                                            <option value="{{ $p->part_no }}">[{{ $p->customer_code }}] {{ $p->part_no }} - {{ $p->part_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="mt-4 p-4 text-center" style="background:linear-gradient(135deg, #e0e7ff, #f8fafc); border-radius:20px; border:1px solid #c7d2fe;">
                                    <i class="fas fa-pallet mb-2" style="font-size:24px; color:var(--ind-blue);"></i>
                                    <label class="small font-weight-black text-muted d-block m-0">STOK WIP GUDANG</label>
                                    <h2 class="font-weight-black mb-0 font-mono" id="displayWipStock" style="color:#0f172a;">
                                        0 <span style="font-size:12px; color:#64748b;">PCS</span>
                                    </h2>
                                </div>
                            </div>
                        </div>

                        <!-- BOX 3: Target & Kalkulasi -->
                        <div class="col-md-4 mb-3">
                            <div class="tech-input-group">
                                <h6 class="font-weight-black mb-4"><i class="fas fa-crosshairs mr-2 text-success"></i>03. TARGET_ALLOCATION</h6>
                                
                                <div class="row">
                                    <div class="col-6 form-group">
                                        <label class="small font-weight-bold" style="color:var(--ind-blue);">S1 Reguler</label>
                                        <input type="number" name="s1_plan_reg" class="form-control bubble-input text-center font-mono input-calc-new" value="0" required>
                                    </div>
                                    <div class="col-6 form-group">
                                        <label class="small font-weight-bold text-danger">S1 Lembur</label>
                                        <input type="number" name="s1_plan_ot" class="form-control bubble-input text-center font-mono input-calc-new" value="0" required>
                                    </div>
                                    <div class="col-6 form-group">
                                        <label class="small font-weight-bold" style="color:var(--ind-blue);">S2 Reguler</label>
                                        <input type="number" name="s2_plan_reg" class="form-control bubble-input text-center font-mono input-calc-new" value="0" required>
                                    </div>
                                    <div class="col-6 form-group">
                                        <label class="small font-weight-bold text-danger">S2 Lembur</label>
                                        <input type="number" name="s2_plan_ot" class="form-control bubble-input text-center font-mono input-calc-new" value="0" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6 form-group mb-0">
                                        <label class="small font-weight-bold text-muted">Cap/Hr</label>
                                        <input type="number" name="cap_per_hour" id="cap_new" class="form-control bubble-input text-center font-mono input-calc-new" value="100" required>
                                    </div>
                                    <div class="col-6 form-group mb-0">
                                        <label class="small font-weight-bold text-muted">M/P</label>
                                        <input type="number" name="manpower" class="form-control bubble-input text-center font-mono" value="1" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer pb-4 pt-3 d-flex flex-column align-items-center" style="border-top:2px dashed #e2e8f0;">
                    <div id="wipValidationMsg" class="w-100 text-center mb-3"></div>
                    <div class="d-flex w-100 justify-content-center gap-3">
                        <button type="button" class="btn-pill" data-dismiss="modal" style="background:#e2e8f0; color:var(--ind-slate); width:150px;">BATAL</button>
                        <button type="submit" id="btnSubmitNewPlan" class="btn-pill btn-new-plan" style="width:300px; font-size:14px;">🚀 DEPLOY PLAN</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✨ 2. MODAL REVISI (DILOOPING DI LUAR TABEL) ✨ --}}
@foreach($plans as $plan)
    @if($plan->total_actual == 0)
    <div class="modal fade animate__animated animate__zoomIn" id="modalEditPlan{{ $plan->id }}" tabindex="-1" style="z-index: 9999 !important;">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content bubble-modal border-0" style="background:#fffbeb;">
                <div class="modal-header bubble-header-modal" style="background:#f59e0b;">
                    <h5 class="modal-title font-weight-black text-white" style="font-family:'Orbitron'; letter-spacing:1px;">
                        <i class="fas fa-edit mr-2" style="color:#fffbeb;"></i> REVISI_PRODUCTION_PLAN
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" style="opacity:1;"><span>&times;</span></button>
                </div>
                
                {{-- ⚠️ INI YANG PALING PENTING, ACTION HARUS MENGARAH KE update_wos WELDING! ⚠️ --}}
                <form action="{{ route('ppic.welding.update_wos', $plan->id) }}" method="POST">
                    @csrf
                    @method('PUT') 
                    
                    <div class="modal-body p-4">
                        <div class="alert mb-4" style="background:#fef3c7; border:1px solid #fde68a; color:#b45309; font-size:12.5px; font-weight:800; border-radius:16px;">
                            <i class="fas fa-info-circle mr-2"></i> <strong>TIPS REVISI:</strong> Kosongkan (Isi angka 0) pada semua target form di bawah untuk <strong>MENGHAPUS</strong> jadwal ini. Stok WIP gudang akan kembali secara otomatis!
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="tech-input-group" style="background:white; border-color:#fde68a;">
                                    <h6 class="font-weight-black mb-4"><i class="fas fa-tag mr-2 text-warning"></i>PRODUCT_ID</h6>
                                    <div class="form-group">
                                        <label class="small font-weight-bold text-muted">Station Code</label>
                                        <select name="line_code" class="form-control bubble-input" required>
                                            @foreach($availableLines as $line)
                                                <option value="{{ $line->kode_line }}" {{ $plan->line_code == $line->kode_line ? 'selected' : '' }}>{{ $line->kode_line }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold text-muted">Part No</label>
                                        <input type="text" class="form-control bubble-input font-mono" value="{{ $plan->part_no }}" readonly style="background:#f1f5f9; color:#94a3b8;">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="tech-input-group" style="background:white; border-color:#fde68a;">
                                    <h6 class="font-weight-black mb-4"><i class="fas fa-bolt mr-2 text-warning"></i> 02. CAP_METRICS</h6>
                                    <div class="row">
                                        <div class="col-6 form-group">
                                            <label class="small font-weight-bold text-muted">Manpower</label>
                                            <input type="number" name="manpower" class="form-control bubble-input text-center font-mono" value="{{ $plan->manpower }}" required>
                                        </div>
                                        <div class="col-6 form-group">
                                            <label class="small font-weight-bold text-muted">Cap/Hr</label>
                                            <input type="number" name="cap_per_hour" id="cap_rev_{{ $plan->id }}" class="form-control bubble-input text-center font-mono input-calc-rev-{{ $plan->id }}" value="{{ $plan->cap_per_hour }}" required>
                                        </div>
                                    </div>
                                    <div class="mt-2 p-3 bg-light rounded-xl text-center border">
                                        <label class="small font-weight-black text-muted d-block m-0">Expected Load</label>
                                        @php $loadH = ($plan->cap_per_hour > 0) ? ($plan->total_target / $plan->cap_per_hour) : 0; @endphp
                                        <h3 class="font-weight-black text-warning mb-0 font-mono" id="display_hours_{{ $plan->id }}">{{ number_format($loadH, 1) }}H</h3>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="tech-input-group" style="background:white; border-color:#fde68a;">
                                    <h6 class="font-weight-black mb-4"><i class="fas fa-layer-group mr-2 text-warning"></i> 03. REVISI_TARGET</h6>
                                    <div class="row">
                                        <div class="col-6 form-group">
                                            <label class="small font-weight-bold" style="color:var(--ind-blue);">S1 Reguler</label>
                                            <input type="number" name="s1_plan_reg" class="form-control bubble-input text-center font-mono input-calc-rev-{{ $plan->id }}" value="{{ $plan->s1_plan_reg }}" required>
                                        </div>
                                        <div class="col-6 form-group">
                                            <label class="small font-weight-bold text-danger">S1 Lembur</label>
                                            <input type="number" name="s1_plan_ot" class="form-control bubble-input text-center font-mono input-calc-rev-{{ $plan->id }}" value="{{ $plan->s1_plan_ot }}" required>
                                        </div>
                                        <div class="col-6 form-group mb-0">
                                            <label class="small font-weight-bold" style="color:var(--ind-blue);">S2 Reguler</label>
                                            <input type="number" name="s2_plan_reg" class="form-control bubble-input text-center font-mono input-calc-rev-{{ $plan->id }}" value="{{ $plan->s2_plan_reg }}" required>
                                        </div>
                                        <div class="col-6 form-group mb-0">
                                            <label class="small font-weight-bold text-danger">S2 Lembur</label>
                                            <input type="number" name="s2_plan_ot" class="form-control bubble-input text-center font-mono input-calc-rev-{{ $plan->id }}" value="{{ $plan->s2_plan_ot }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer pb-4 pt-3 d-flex justify-content-center gap-3" style="background:white; border-top:2px dashed #fde68a;">
                        <button type="button" class="btn-pill" data-dismiss="modal" style="background:#e2e8f0; color:var(--ind-slate); width:150px;">BATAL</button>
                        <button type="submit" class="btn-pill" style="width:300px; font-size:14px; background:linear-gradient(135deg, #f59e0b, #d97706); color:white; box-shadow:0 6px 20px rgba(245,158,11,0.4);">🚀 SIMPAN REVISI</button>
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
    $(document).ready(function() {
        // Init Select2
        $('.select2-part').select2({
            dropdownParent: $('#modalRegisterPlan'),
            placeholder: "Cari Part Number...",
            allowClear: true
        });

        // ==========================================
        // 1. VALIDASI STOK WIP (HANYA UNTUK MODAL NEW PLAN)
        // ==========================================
        let currentWipStock = 0;
        let submitBtnNew = $('#btnSubmitNewPlan');
        let msgBoxNew = $('#wipValidationMsg');

        function validateWipStock() {
            // Ambil input khusus dari dalam #modalRegisterPlan agar tidak bentrok dengan modal revisi
            let s1r = parseInt($('#modalRegisterPlan input[name="s1_plan_reg"]').val()) || 0;
            let s1o = parseInt($('#modalRegisterPlan input[name="s1_plan_ot"]').val()) || 0;
            let s2r = parseInt($('#modalRegisterPlan input[name="s2_plan_reg"]').val()) || 0;
            let s2o = parseInt($('#modalRegisterPlan input[name="s2_plan_ot"]').val()) || 0;
            
            let totalTarget = s1r + s1o + s2r + s2o;
            
            if (totalTarget === 0) {
                msgBoxNew.html('<span class="text-muted font-weight-bold"><i class="fas fa-info-circle"></i> Tentukan kuantiti target untuk mengecek WIP.</span>');
                submitBtnNew.prop('disabled', true);
                return;
            }
            
            if (totalTarget > currentWipStock) {
                msgBoxNew.html(`<div class="alert alert-danger p-2 m-0 border-0 shadow-sm" style="border-radius:12px;"><b>❌ STOK WIP KURANG!</b> Butuh: <b style="font-size:14px;">${totalTarget}</b> | Tersedia: <b>${currentWipStock}</b></div>`);
                submitBtnNew.prop('disabled', true).css('opacity', '0.5');
            } else {
                msgBoxNew.html(`<div class="alert alert-success p-2 m-0 border-0 shadow-sm" style="border-radius:12px; background:#ecfdf5; color:#065f46;"><b>✅ STOK AMAN!</b> Butuh: <b>${totalTarget}</b> | Tersedia: <b>${currentWipStock}</b></div>`);
                submitBtnNew.prop('disabled', false).css('opacity', '1');
            }
            
            // Kalkulasi Cap/Hr New Plan
            let cap = parseFloat($('#cap_new').val()) || 0;
            let hours = (cap > 0) ? (totalTarget / cap) : 0;
            $('#display_hours_new').text(hours.toFixed(1) + 'H');
        }

        $('#partSelector').on('change', function() {
            let partNo = $(this).val();
            let custMatch = $(this).find('option:selected').text().match(/\[(.*?)\]/);
            if(custMatch) $('#custDisp').val(custMatch[1]); else $('#custDisp').val('-');

            if(partNo) {
                $('#displayWipStock').html('<i class="fas fa-spinner fa-spin" style="color:#94a3b8;"></i>');
                submitBtnNew.prop('disabled', true);
                $.get('/planning/ppic/check-wip-stock/' + encodeURIComponent(partNo), function(res) {
                    if(res.status === 'success') {
                        currentWipStock = res.wip_stock;
                        let color = currentWipStock <= 0 ? '#ef4444' : '#10b981';
                        $('#displayWipStock').html(`<span style="color:${color};">${currentWipStock}</span> <span style="font-size:12px; color:#64748b;">PCS</span>`);
                        validateWipStock();
                    }
                });
            } else {
                $('#displayWipStock').html('0 <span style="font-size:12px; color:#64748b;">PCS</span>');
                currentWipStock = 0; validateWipStock();
            }
        });

        // Trigger validasi saat mengetik (khusus modal NEW PLAN)
        $('.input-calc-new').on('input', function() { validateWipStock(); });

        // ==========================================
        // 2. KALKULASI CAP/HR DINAMIS (UNTUK MODAL REVISI)
        // ==========================================
        @foreach($plans as $plan)
            $('.input-calc-rev-{{ $plan->id }}').on('input', function() {
                let s1r = parseInt($('#modalEditPlan{{ $plan->id }} input[name="s1_plan_reg"]').val()) || 0;
                let s1o = parseInt($('#modalEditPlan{{ $plan->id }} input[name="s1_plan_ot"]').val()) || 0;
                let s2r = parseInt($('#modalEditPlan{{ $plan->id }} input[name="s2_plan_reg"]').val()) || 0;
                let s2o = parseInt($('#modalEditPlan{{ $plan->id }} input[name="s2_plan_ot"]').val()) || 0;
                let cap = parseFloat($('#cap_rev_{{ $plan->id }}').val()) || 0;
                
                let total = s1r + s1o + s2r + s2o;
                let hours = (cap > 0) ? (total / cap) : 0;
                $('#display_hours_{{ $plan->id }}').text(hours.toFixed(1) + 'H');
            });
        @endforeach
    });
</script>
@endsection