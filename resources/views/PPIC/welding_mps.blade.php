@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --app-bg: #f8fafc;
        --ind-navy: #1e293b;
        --ind-blue: #4f73d9;
        --ind-cyan: #06b6d4;
        --ind-green: #10b981;
        --ind-slate: #64748b;
        --ind-border: #e2e8f0;
    }
    body { background-color: var(--app-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--ind-navy); }
    
    /* MODULAR HEADER */
    .mrp-header-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-radius: 20px; padding: 25px 35px; border: 1px solid rgba(226,232,240,0.8); margin-bottom: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.02);}
    .heading-title { font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 26px; letter-spacing: -0.5px; color: var(--ind-navy); margin-bottom: 5px; text-transform: uppercase;}
    
    .kpi-card { background: white; border-radius: 16px; padding: 20px 25px; border: 1px solid var(--ind-border); position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02); min-height: 100px;}
    .kpi-card::after { content: ""; position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: var(--accent-color, var(--ind-blue)); }
    .kpi-label { font-size: 10.5px; font-weight: 800; color: var(--ind-slate); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
    .kpi-value { font-weight: 800; font-size: 2rem; color: var(--ind-navy); font-family: 'JetBrains Mono', monospace; }
    
    .station-panel { background: white; border-radius: 16px; border: 1px solid var(--ind-border); overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 30px; }
    .station-header { background: #475569; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; color: white;}
    
    .table-mps thead th { background: #fdfaf6; color: var(--ind-slate); font-size: 10px; font-weight: 900; text-transform: uppercase; padding: 15px; border: none; letter-spacing: 0.5px; }
    .table-mps tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px dashed #e2e8f0; font-weight: 600; font-size: 12px; }
    
    .shift-toggle { background: #e2e8f0; border-radius: 50px; padding: 4px; display: inline-flex; }
    .shift-btn { border-radius: 50px; padding: 8px 20px; font-weight: 900; font-size: 11px; transition: 0.3s; border: none; color: #64748b; text-decoration: none; }
    .shift-btn.active { background: #1e293b; color: white; }
    
    .btn-pill { border-radius: 50rem; padding: 10px 24px; font-weight: 900; font-size: 11px; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: none; cursor: pointer; text-decoration: none;}
    .btn-new-plan { background: var(--ind-blue); color: white; box-shadow: 0 4px 15px rgba(79, 115, 217, 0.3); }
    .btn-new-plan:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(79, 115, 217, 0.4); color:white; }
    
    .btn-print-wos { background: transparent; color: white; border: 1px solid rgba(255,255,255,0.4); border-radius: 50rem; padding: 6px 16px; font-size: 10px; font-weight: 800; }
    .btn-print-bprm { background: var(--ind-blue); color: white; border: none; border-radius: 50rem; padding: 6px 16px; font-size: 10px; font-weight: 800; }
    .btn-revisi { background: #f59e0b; color: white; border-radius: 50rem; padding: 6px 16px; font-size: 10px; font-weight: 800; }
    
    .time-chip { font-family: 'JetBrains Mono'; background: #f1f5f9; color: var(--ind-navy); padding: 5px 12px; border-radius: 8px; font-size: 11.5px; font-weight: 700; border: 1px solid #e2e8f0; }
    .font-mono { font-family: 'JetBrains Mono', monospace; font-weight: 800; }

    /* ========================================================
       ✨ STYLE MODAL PERSIS KAYAK STAMPING ✨ 
       ======================================================== */
    .mrp-modal-content { border-radius: 16px; border: none; overflow: hidden; background: #ffffff; box-shadow: 0 25px 50px rgba(0,0,0,0.25); }
    .mrp-modal-header { background: #4f73d9; padding: 20px 30px; border-bottom: none; display: flex; align-items: center; }
    .mrp-modal-title { font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 16px; color: #ffffff; margin: 0; letter-spacing: 0.5px; text-transform: uppercase;}
    
    .mrp-card-box { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 25px; height: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
    .mrp-card-title { font-size: 12.5px; font-weight: 800; margin-bottom: 20px; letter-spacing: 0.5px; text-transform: uppercase; display: flex; align-items: center; gap: 8px;}
    
    .col-id .mrp-card-title { color: var(--ind-blue); }
    .col-pm .mrp-card-title { color: var(--ind-cyan); }
    .col-qa .mrp-card-title { color: var(--ind-green); }
    
    .mrp-label { font-size: 10px; font-weight: 800; color: #0f172a; margin-bottom: 6px; display: block;}
    .mrp-input { border-radius: 8px; border: 1px solid #e2e8f0; padding: 10px 15px; font-weight: 700; font-size: 12px; color: #1e293b; transition: 0.3s; width: 100%; outline: none; background: #ffffff;}
    .mrp-input:focus { border-color: var(--ind-blue); box-shadow: 0 0 0 3px rgba(79, 115, 217, 0.1); }
    .mrp-input[readonly] { background: #f8fafc; color: #64748b; border-color: #f1f5f9; }
    
    .mc-load-box { background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 12px; padding: 25px; text-align: center; margin-top: 15px; }
    .mc-load-label { font-size: 9px; font-weight: 800; color: #0369a1; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;}
    .mc-load-val { font-family: 'Orbitron', sans-serif; font-size: 28px; font-weight: 900; color: var(--ind-cyan); letter-spacing: 1px;}
    
    .mrp-tabs { display: flex; border: 1px solid var(--ind-blue); border-radius: 6px; overflow: hidden; margin-bottom: 20px; }
    .mrp-tab { flex: 1; text-align: center; font-size: 11px; font-weight: 800; padding: 10px; cursor: pointer; background: #ffffff; color: var(--ind-slate); transition: 0.2s;}
    .mrp-tab.active { background: var(--ind-blue); color: white; }
    .tab-content-mrp { display: none; }
    .tab-content-mrp.active { display: block; }
    
    /* CUSTOM ALERT (SEPERTI STAMPING) */
    .alert-stok-error { background-color: #dcfce7; border-radius: 8px; padding: 12px 20px; text-align: center; border: 1px solid #86efac; width: 100%; margin-bottom: 15px; color: #166534;}
    .alert-stok-error.error { background-color: #fee2e2; border-color: #fca5a5; color: #991b1b; }
    
    .btn-abort { background: #94a3b8; color: white; border-radius: 50rem; padding: 12px 35px; font-weight: 800; font-size: 12px; border: none; cursor: pointer; transition: 0.3s;}
    .btn-abort:hover { background: #64748b; }
    .btn-auth { background: var(--ind-blue); color: white; border-radius: 50rem; padding: 12px 35px; font-weight: 800; font-size: 12px; border: none; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(79, 115, 217, 0.4);}
    .btn-auth:hover { background: #3b5cbd; transform: translateY(-2px);}
    .btn-auth:disabled { background: #94a3b8; box-shadow: none; cursor: not-allowed; transform: none; }

    .select2-container--default .select2-selection--single { background-color: #fff; border: 1px solid #e2e8f0; border-radius: 8px; height: 41px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 39px; font-weight: 700; font-size: 12px; color: #1e293b; padding-left: 15px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 39px; right: 10px; }
</style>

<div class="container-fluid py-4 px-lg-5 animate__animated animate__fadeIn">
    
    @if(session('success')) 
    <div class="alert alert-success border-0 shadow-sm animate__animated animate__bounceIn" style="border-radius: 12px; background:#ecfdf5; color:#047857; font-weight:800;">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div> 
    @endif
    @if(session('error')) 
    <div class="alert alert-danger border-0 shadow-sm animate__animated animate__shakeX" style="border-radius: 12px; background:#fef2f2; color:#b91c1c; font-weight:800;">
        <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
    </div> 
    @endif

    <div class="mrp-header-card d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div>
            <h2 class="heading-title">MPS_WELDING <span style="color: var(--ind-blue);">ENGINE</span></h2>
            <div class="d-flex align-items-center mt-2">
                <span class="badge badge-dark px-3 py-1 mr-3" style="border-radius: 6px; font-weight:800; font-size:10px;">SYSTEM_ACTIVE</span>
                <span style="font-size:11px; font-weight:800; color:var(--ind-slate);"><i class="far fa-calendar-alt mr-1"></i> {{ date('D, d M Y', strtotime($date)) }}</span>
            </div>
        </div>

        <div class="d-flex align-items-center mt-3 mt-md-0 gap-3">
            <div class="shift-toggle mr-3">
                <a href="?date={{$date}}&shift=S1" class="shift-btn {{ $shift == 'S1' ? 'active' : '' }}">SHIFT_01</a>
                <a href="?date={{$date}}&shift=S2" class="shift-btn {{ $shift == 'S2' ? 'active' : '' }}">SHIFT_02</a>
            </div>
            <input type="date" class="mrp-input px-3 mr-3" value="{{ $date }}" style="width: 160px; height: 38px; cursor:pointer;" onchange="location.href='?date='+this.value+'&shift={{$shift}}'">
            
            <button class="btn-pill btn-new-plan" data-toggle="modal" data-target="#modalRegisterPlan">
                <i class="fas fa-plus"></i> REGISTER_PLAN
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 mb-3"><div class="kpi-card"><div class="kpi-label">Volume Target ({{ $shift }})</div><div class="kpi-value" style="color:var(--ind-blue);">{{ number_format($totalPlanQty) }}<small class="ml-1 text-muted" style="font-size: 11px">PCS</small></div></div></div>
        <div class="col-md-3 mb-3"><div class="kpi-card"><div class="kpi-label">Load Capacity</div><div class="kpi-value" style="color: #8b5cf6">{{ number_format($totalWorkingHours, 1) }}<small class="ml-1 text-muted" style="font-size: 11px">HRS</small></div></div></div>
        <div class="col-md-3 mb-3"><div class="kpi-card"><div class="kpi-label">Switching Loss</div><div class="kpi-value" style="color: #f59e0b">{{ $totalDandory }}<small class="ml-1 text-muted" style="font-size: 11px">MIN</small></div></div></div>
        <div class="col-md-3 mb-3"><div class="kpi-card" style="background:#475569;"><div class="kpi-label" style="color:#cbd5e1;">Operational Shift</div><div class="kpi-value text-white">{{ $shift == 'S1' ? 'DAY_OPS' : 'NIGHT_OPS' }}</div></div></div>
    </div>

    @forelse($plans->groupBy('line_code') as $lineCode => $linePlans)
    <div class="station-panel animate__animated animate__fadeInUp">
        <div class="station-header">
            <h6 class="mb-0" style="font-weight:800; font-size:14px; letter-spacing:0.5px;">
                <i class="fas fa-robot text-info mr-2"></i> STATION: {{ $lineCode }}
            </h6>
            <div>
                <a href="{{ url('/ppic/welding/wos/print/'.$date.'/'.$lineCode) }}" target="_blank" class="btn-print-wos mr-2 text-decoration-none"><i class="fas fa-print mr-1"></i> PRINT WOS</a>
                <a href="{{ url('/ppic/welding/bprm/print/'.$date.'/'.$lineCode) }}" target="_blank" class="btn-print-bprm text-decoration-none"><i class="fas fa-file-alt mr-1"></i> PRINT BPRM</a>
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
                            <div style="font-weight:900; color:var(--ind-navy); font-size:12.5px;">{{ $plan->part_name }}</div>
                            <div class="font-mono" style="font-size:10px; color:var(--ind-slate);">{{ $plan->part_no }}</div>
                        </td>
                        <td class="text-center"><span class="badge" style="background:#f1f5f9; color:var(--ind-slate); border-radius:4px; padding:4px 6px;">{{ $plan->manpower }}P</span></td>
                        <td class="text-center font-mono">{{ number_format($plan->total_target) }}</td>
                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center">
                                <span class="font-mono text-success">{{ number_format($plan->total_actual) }}</span>
                            </div>
                        </td>
                        <td class="text-center font-mono">
                            @if($plan->balance > 0) <span style="color:#ef4444;">-{{ number_format($plan->balance) }}</span>
                            @else <span style="color:#10b981;">+{{ abs($plan->balance) }}</span> @endif
                        </td>
                        <td class="text-center"><span class="time-chip">{{ $plan->start_time }}</span></td>
                        <td class="text-center"><span class="time-chip" style="background:#e0e7ff; color:var(--ind-blue); border-color:#c7d2fe;">{{ $plan->ahir_time }}</span></td>
                        <td class="text-center">
                            @if($percent >= 100) <i class="fas fa-check-circle" style="color:#10b981; font-size:16px;"></i>
                            @elseif($percent > 0) <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                            @else <i class="far fa-clock text-muted" style="font-size:16px;"></i> @endif
                        </td>
                        <td class="text-center">
                            @if($plan->total_actual > 0)
                                <button class="btn btn-sm text-muted rounded-pill px-3 shadow-none border" style="background:#f1f5f9; cursor:not-allowed;" title="LOCKED"><i class="fas fa-lock"></i></button>
                            @else
                                <button class="btn-revisi border-0" data-toggle="modal" data-target="#modalEditPlan{{ $plan->id }}"><i class="fas fa-edit"></i> REVISI</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="text-center py-5" style="background:transparent;">
        <i class="fas fa-clipboard-list text-muted mb-3" style="font-size:40px; opacity:0.3;"></i>
        <h5 style="font-weight:900; color:var(--ind-slate);">TIDAK ADA JADWAL WELDING</h5>
        <p class="text-muted mb-0" style="font-weight:600;">Klik tombol <b style="color:var(--ind-blue);">+ REGISTER_PLAN</b> untuk menyusun jadwal.</p>
    </div>
    @endforelse
</div>

{{-- ========================================================================================= --}}
{{-- ✨ 1. MODAL REGISTER NEW PLAN (DESAIN 3 KOLOM PERSIS STAMPING) ✨ --}}
{{-- ========================================================================================= --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalRegisterPlan" tabindex="-1" style="z-index: 9999 !important;">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1050px;">
        <div class="modal-content mrp-modal-content">
            <div class="mrp-modal-header">
                <h5 class="mrp-modal-title">
                    <i class="fas fa-plus-circle mr-2"></i> REGISTER_PLAN_SYSTEM
                </h5>
                <button type="button" class="close text-white ml-auto" data-dismiss="modal" style="opacity:1;"><span>&times;</span></button>
            </div>
            
            <form action="{{ route('ppic.welding.mps_store') }}" method="POST" id="formNewPlan">
                @csrf
                <div class="modal-body p-4 p-md-5" style="background:#f8fafc;">
                    <div class="row">
                        <!-- KOLOM 1: Identifikasi (Sekarang Part Identification di sini!) -->
                        <div class="col-md-4 mb-3 col-id">
                            <div class="mrp-card-box border-0 shadow-sm">
                                <h6 class="mrp-card-title"><i class="fas fa-tag mr-2"></i> 01. PRODUCT_IDENTITY</h6>
                                
                                <label class="mrp-label">Tanggal Produksi</label>
                                <input type="date" name="plan_date" class="mrp-input mb-3" value="{{ $date }}" required>
                                
                                <label class="mrp-label">Select Line</label>
                                <select name="line_code" class="mrp-input mb-3" required>
                                    <option value="">Pilih Station...</option>
                                    @foreach($availableLines as $line)
                                        <option value="{{ $line->kode_line }}">{{ $line->kode_line }}</option>
                                    @endforeach
                                </select>
                                
                                <label class="mrp-label">Customer (Auto)</label>
                                <input type="text" id="custDispNew" name="customer_code" class="mrp-input mb-3 font-mono" value="AUTO DETECT" readonly style="background:#f1f5f9; color:#94a3b8; border-color:transparent;">

                                <label class="mrp-label">Part Identification</label>
                                <select name="part_no" id="partSelectorNew" class="mrp-input select2-part" required style="width:100%;">
                                    <option value="">Ketik Part No / Nama...</option>
                                    @foreach($availableParts as $p)
                                        @php
                                            $cleanPart = trim(urldecode($p->part_no));
                                            if (strpos($cleanPart, ' - ') !== false) {
                                                $cleanPart = trim(explode(' - ', $cleanPart)[0]);
                                            }
                                            $fg = \Illuminate\Support\Facades\DB::table('finished_goods')->where('part_no', $cleanPart)->first();
                                            $wipStock = $fg ? (int)$fg->welding_stock : 0;
                                            $custName = $p->customer_code ?? 'SAI';
                                        @endphp
                                        <option value="{{ $cleanPart }}" data-wip="{{ $wipStock }}" data-cust="{{ $custName }}">
                                            [{{ $custName }}] {{ $cleanPart }} - {{ $p->part_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- KOLOM 2: M/C Load (Tanpa kotak stok, persis Stamping!) -->
                        <div class="col-md-4 mb-3 col-pm">
                            <div class="mrp-card-box border-0 shadow-sm d-flex flex-column">
                                <h6 class="mrp-card-title"><i class="fas fa-chart-line mr-1"></i> 02. PERFORMANCE_METRICS</h6>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="mrp-label">Manpower</label>
                                        <input type="number" name="manpower" class="mrp-input font-mono" value="1" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="mrp-label">Cap/Hour</label>
                                        <input type="number" name="cap_per_hour" id="cap_new" class="mrp-input font-mono calc-new" value="320" required>
                                    </div>
                                </div>
                                
                                <div class="mc-load-box mt-auto mb-2">
                                    <label class="mc-load-label">CALCULATED M/C LOAD</label>
                                    <div class="mc-load-val" id="display_hours_new">
                                        <span style="font-size:18px;">S1</span> 0.0H <span style="color:#94a3b8; font-size:18px; margin:0 5px;">|</span> <span style="font-size:18px;">S2</span> 0.0H
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KOLOM 3: Target Allocation & Parameters -->
                        <div class="col-md-4 mb-3 col-qa">
                            <div class="mrp-card-box pb-2 border-0 shadow-sm">
                                <h6 class="mrp-card-title"><i class="fas fa-crosshairs mr-1"></i> 03. QUANTITY_PLANNING</h6>
                                
                                <div class="mrp-tabs">
                                    <div class="mrp-tab active" data-tab="shift1">SHIFT_1</div>
                                    <div class="mrp-tab" data-tab="shift2">SHIFT_2</div>
                                </div>
                                
                                <!-- Tab 1 -->
                                <div class="tab-content-mrp active" id="tab-shift1">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label class="mrp-label text-primary">S1 Reguler Plan</label>
                                            <input type="number" name="s1_plan_reg" id="new_s1_reg" class="mrp-input font-mono calc-new" value="0" required>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="mrp-label text-danger">S1 Overtime Plan</label>
                                            <input type="number" name="s1_plan_ot" id="new_s1_ot" class="mrp-input font-mono calc-new" value="0" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Tab 2 -->
                                <div class="tab-content-mrp" id="tab-shift2">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label class="mrp-label text-primary">S2 Reguler Plan</label>
                                            <input type="number" name="s2_plan_reg" id="new_s2_reg" class="mrp-input font-mono calc-new" value="0" required>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="mrp-label text-danger">S2 Overtime Plan</label>
                                            <input type="number" name="s2_plan_ot" id="new_s2_ot" class="mrp-input font-mono calc-new" value="0" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer d-flex flex-column align-items-center pb-4 pt-0" style="background:#f8fafc; border:none;">
                    <!-- TEMPAT KOTAK PERINGATAN STOK MUNCUL (LANGSUNG KETIKA DI KETIK) -->
                    <div id="wipValidationMsgNew" class="w-100 px-3"></div>
                    
                    <div class="d-flex justify-content-center gap-3 w-100 mt-2">
                        <button type="button" class="btn-abort" data-dismiss="modal">ABORT</button>
                        <button type="submit" id="btnAuthNew" class="btn-auth disabled"><i class="fas fa-rocket mr-1"></i> AUTHORIZE & DEPLOY</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✨ 2. MODAL REVISI (DESAIN 3 KOLOM PERSIS STAMPING) ✨ --}}
@foreach($plans as $plan)
    @if($plan->total_actual == 0)
    <div class="modal fade animate__animated animate__zoomIn" id="modalEditPlan{{ $plan->id }}" tabindex="-1" style="z-index: 9999 !important;">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1050px;">
            <div class="modal-content mrp-modal-content border-0">
                <div class="mrp-modal-header" style="background:#f59e0b;">
                    <h5 class="mrp-modal-title"><i class="fas fa-edit mr-2"></i> REVISE_PLAN_SYSTEM</h5>
                    <button type="button" class="close text-white ml-auto" data-dismiss="modal" style="opacity:1;"><span>&times;</span></button>
                </div>
                
                <form action="{{ route('ppic.welding.update_wos', $plan->id) }}" method="POST" id="formRev{{ $plan->id }}">
                    @csrf
                    @method('PUT') 
                    
                    <div class="modal-body p-4 p-md-5" style="background:#f8fafc;">
                        <div class="row">
                            <!-- BOX 1: Identifikasi -->
                            <div class="col-md-4 mb-3 col-id">
                                <div class="mrp-card-box shadow-sm border-0" style="border:1px solid #fde68a;">
                                    <h6 class="mrp-card-title" style="color:#d97706;"><i class="fas fa-tag mr-2"></i>01. PRODUCT_IDENTITY</h6>
                                    
                                    <label class="mrp-label">Select Line</label>
                                    <select name="line_code" class="mrp-input mb-4" required>
                                        @foreach($availableLines as $line)
                                            <option value="{{ $line->kode_line }}" {{ $plan->line_code == $line->kode_line ? 'selected' : '' }}>{{ $line->kode_line }}</option>
                                        @endforeach
                                    </select>
                                    
                                    <label class="mrp-label">Part No (Locked)</label>
                                    <input type="text" class="mrp-input mb-0 font-mono" value="{{ $plan->part_no }}" readonly style="background:#fef3c7; color:#b45309; border-color:transparent;">
                                </div>
                            </div>
                            
                            <!-- BOX 2: M/C Load -->
                            <div class="col-md-4 mb-3 col-pm">
                                <div class="mrp-card-box shadow-sm border-0 d-flex flex-column" style="border:1px solid #fde68a;">
                                    <h6 class="mrp-card-title" style="color:#d97706;"><i class="fas fa-chart-line mr-2"></i>02. PERFORMANCE_METRICS</h6>
                                    
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label class="mrp-label">Manpower</label>
                                            <input type="number" name="manpower" class="mrp-input font-mono" value="{{ $plan->manpower }}" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="mrp-label">Cap/Hour</label>
                                            <input type="number" name="cap_per_hour" id="cap_rev_{{ $plan->id }}" class="mrp-input font-mono calc-rev-{{ $plan->id }}" value="{{ $plan->cap_per_hour }}" required>
                                        </div>
                                    </div>

                                    <div class="mc-load-box mt-auto mb-2" style="background:#fffbeb; border-color:#fde68a;">
                                        <label class="mc-load-label" style="color:#b45309;">CALCULATED M/C LOAD</label>
                                        
                                        <!-- HITUNG STOK MAKSIMAL DARI PHP -->
                                        @php
                                            $cleanPartRev = trim(urldecode($plan->part_no));
                                            if (strpos($cleanPartRev, ' - ') !== false) {
                                                $cleanPartRev = trim(explode(' - ', $cleanPartRev)[0]);
                                            }
                                            $fgRev = \Illuminate\Support\Facades\DB::table('finished_goods')->where('part_no', $cleanPartRev)->first();
                                            $wipRev = $fgRev ? (int)$fgRev->welding_stock : 0;
                                            
                                            $oldTgtS1 = $plan->s1_plan_reg + $plan->s1_plan_ot;
                                            $oldTgtS2 = $plan->s2_plan_reg + $plan->s2_plan_ot;
                                            $maxRevAvailable = $wipRev + $oldTgtS1 + $oldTgtS2; 
                                            
                                            $hS1 = ($plan->cap_per_hour > 0) ? ($oldTgtS1 / $plan->cap_per_hour) : 0; 
                                            $hS2 = ($plan->cap_per_hour > 0) ? ($oldTgtS2 / $plan->cap_per_hour) : 0; 
                                        @endphp
                                        
                                        <div class="mc-load-val" id="display_hours_{{ $plan->id }}" style="color:#d97706;">
                                            <span style="font-size:18px;">S1</span> {{ number_format($hS1, 1) }}H <span style="color:#fcd34d; font-size:18px; margin:0 5px;">|</span> <span style="font-size:18px;">S2</span> {{ number_format($hS2, 1) }}H
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- BOX 3: Target -->
                            <div class="col-md-4 mb-3 col-qa">
                                <div class="mrp-card-box pb-2 shadow-sm border-0" style="border:1px solid #fde68a;">
                                    <h6 class="mrp-card-title" style="color:#d97706;"><i class="fas fa-crosshairs mr-1"></i> 03. QUANTITY_PLANNING</h6>
                                    
                                    <div class="mrp-tabs">
                                        <div class="mrp-tab rev-tab-s1-{{ $plan->id }} active" onclick="switchRevTab('{{ $plan->id }}', 1)" style="border-color:#d97706;">SHIFT_1</div>
                                        <div class="mrp-tab rev-tab-s2-{{ $plan->id }}" onclick="switchRevTab('{{ $plan->id }}', 2)" style="border-color:#d97706;">SHIFT_2</div>
                                    </div>
                                    
                                    <!-- Tab 1 Rev -->
                                    <div class="tab-content-mrp rev-cont-s1-{{ $plan->id }} active">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label class="mrp-label text-primary">S1 Reguler Plan</label>
                                                <input type="number" name="s1_plan_reg" id="rev_s1_reg_{{ $plan->id }}" class="mrp-input font-mono calc-rev-{{ $plan->id }}" value="{{ $plan->s1_plan_reg }}" required>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="mrp-label text-danger">S1 Overtime Plan</label>
                                                <input type="number" name="s1_plan_ot" id="rev_s1_ot_{{ $plan->id }}" class="mrp-input font-mono calc-rev-{{ $plan->id }}" value="{{ $plan->s1_plan_ot }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Tab 2 Rev -->
                                    <div class="tab-content-mrp rev-cont-s2-{{ $plan->id }}">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label class="mrp-label text-primary">S2 Reguler Plan</label>
                                                <input type="number" name="s2_plan_reg" id="rev_s2_reg_{{ $plan->id }}" class="mrp-input font-mono calc-rev-{{ $plan->id }}" value="{{ $plan->s2_plan_reg }}" required>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="mrp-label text-danger">S2 Overtime Plan</label>
                                                <input type="number" name="s2_plan_ot" id="rev_s2_ot_{{ $plan->id }}" class="mrp-input font-mono calc-rev-{{ $plan->id }}" value="{{ $plan->s2_plan_ot }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer d-flex flex-column align-items-center pb-4 pt-0" style="background:#f8fafc; border:none;">
                        <!-- TEMPAT ERROR REVISI DI SINI -->
                        <div id="wipValidationMsgRev{{ $plan->id }}" class="w-100 px-3" data-max-stock="{{ $maxRevAvailable }}"></div>
                        
                        <div class="d-flex justify-content-center gap-3 w-100 mt-2">
                            <button type="button" class="btn-abort" data-dismiss="modal">ABORT</button>
                            <button type="submit" id="btnAuthRev{{ $plan->id }}" class="btn-auth active" style="background:#f59e0b; box-shadow: 0 4px 15px rgba(245,158,11,0.4);"><i class="fas fa-check mr-1"></i> AUTHORIZE REVISION</button>
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
    // FUNGSI TABS
    $('.mrp-tab').on('click', function() {
        $(this).siblings().removeClass('active').css('background', 'white').css('color', '#64748b');
        $(this).addClass('active');
        if($(this).closest('.mrp-modal-header').css('background-color') != 'rgb(245, 158, 11)') {
            $(this).css('background', '#4f73d9').css('color', 'white');
        } else {
            $(this).css('background', '#f59e0b').css('color', 'white'); 
        }
        let target = $(this).data('tab');
        $(this).parent().siblings('.tab-content-mrp').removeClass('active');
        $('#tab-' + target).addClass('active');
    });

    function switchRevTab(id, shift) {
        $('.rev-tab-s1-' + id + ', .rev-tab-s2-' + id).removeClass('active').css('background', 'white').css('color', '#64748b');
        $('.rev-tab-s' + shift + '-' + id).addClass('active').css('background', '#f59e0b').css('color', 'white');
        
        $('.rev-cont-s1-' + id + ', .rev-cont-s2-' + id).removeClass('active');
        $('.rev-cont-s' + shift + '-' + id).addClass('active');
    }

    $(document).ready(function() {
        // Init Select2
        $('.select2-part').select2({
            dropdownParent: $('#modalRegisterPlan'),
            placeholder: "Ketik Part No / Nama...",
            allowClear: true,
            width: '100%'
        });

        // ==========================================
        // 1. SELECT PART -> AUTO CUSTOMER
        // ==========================================
        $('#partSelectorNew').on('change', function() {
            let selected = $(this).find('option:selected');
            let partNo = $(this).val();
            
            if(partNo) {
                let cust = selected.attr('data-cust') || 'AUTO DETECT';
                $('#custDispNew').val(cust); 
            } else {
                $('#custDispNew').val('AUTO DETECT');
            }
            // Trigger validasi biar kotak peringatan langsung muncul kalo di bawahnya udah ada angka
            $('#modalRegisterPlan .calc-new').first().trigger('input');
        });

        // ==========================================
        // 2. CEK STOK & UPDATE JAM REAL-TIME SAAT MENGETIK (NEW PLAN)
        // ==========================================
        $('#modalRegisterPlan .calc-new').on('input', function() { 
            let partNo = $('#partSelectorNew').val();
            let msgBoxNew = $('#wipValidationMsgNew');
            let submitBtn = $('#btnAuthNew');
            
            let s1r = parseInt($('#new_s1_reg').val()) || 0;
            let s1o = parseInt($('#new_s1_ot').val()) || 0;
            let s2r = parseInt($('#new_s2_reg').val()) || 0;
            let s2o = parseInt($('#new_s2_ot').val()) || 0;
            let totalTarget = s1r + s1o + s2r + s2o;
            
            let cap = parseFloat($('#cap_new').val()) || 0;
            let h1 = (cap > 0) ? ((s1r + s1o) / cap) : 0;
            let h2 = (cap > 0) ? ((s2r + s2o) / cap) : 0;
            
            // Ubah Tampilan Jam (Warna Cyan)
            $('#display_hours_new').html(`<span style="font-size:18px;">S1</span> ${h1.toFixed(1)}H <span style="color:#94a3b8; font-size:18px; margin:0 5px;">|</span> <span style="font-size:18px;">S2</span> ${h2.toFixed(1)}H`);
            
            // Logika Peringatan Stok
            if (!partNo) {
                msgBoxNew.html('');
                submitBtn.prop('disabled', true).css('background', '#cbd5e1');
                return;
            }

            let selected = $('#partSelectorNew').find('option:selected');
            let currentWipStock = parseInt(selected.attr('data-wip')) || 0;

            if (totalTarget === 0) {
                msgBoxNew.html('');
                submitBtn.prop('disabled', true).css('background', '#cbd5e1');
            } else if (totalTarget > currentWipStock) {
                // Munculin Merah, Kunci Tombol
                msgBoxNew.html(`
                    <div class="alert-stok-error error animate__animated animate__headShake">
                        <div class="title"><i class="fas fa-times-circle mr-1"></i> PLAN DITOLAK: STOK WIP KURANG!</div>
                        <div class="desc">Butuh: <b style="font-size:13px;">${totalTarget}</b> Pcs | Tersedia WIP: <b>${currentWipStock}</b> Pcs</div>
                    </div>
                `);
                submitBtn.prop('disabled', true).css('background', '#cbd5e1');
            } else {
                // Munculin Hijau, Buka Tombol
                msgBoxNew.html(`
                    <div class="alert-stok-success animate__animated animate__fadeInUp">
                        <div class="title"><i class="fas fa-check-square mr-1"></i> STOK WIP AMAN!</div>
                        <div class="desc">Butuh: <b style="font-size:13px;">${totalTarget}</b> Pcs | Tersedia WIP: <b>${currentWipStock}</b> Pcs</div>
                    </div>
                `);
                submitBtn.prop('disabled', false).css('background', '#4f73d9');
            }
        });

        // 3. EVENT LISTENER REVISI REAL-TIME
        @foreach($plans as $plan)
            @if($plan->total_actual == 0)
            
            $('.calc-rev-{{ $plan->id }}').on('input', function() { 
                let maxAvailable = parseInt($('#wipValidationMsgRev{{ $plan->id }}').attr('data-max-stock')) || 0;
                
                let s1r = parseInt($('#rev_s1_reg_{{ $plan->id }}').val()) || 0;
                let s1o = parseInt($('#rev_s1_ot_{{ $plan->id }}').val()) || 0;
                let s2r = parseInt($('#rev_s2_reg_{{ $plan->id }}').val()) || 0;
                let s2o = parseInt($('#rev_s2_ot_{{ $plan->id }}').val()) || 0;
                let totalTarget = s1r + s1o + s2r + s2o;

                let cap = parseFloat($('#cap_rev_{{ $plan->id }}').val()) || 0;
                let h1 = (cap > 0) ? ((s1r + s1o) / cap) : 0;
                let h2 = (cap > 0) ? ((s2r + s2o) / cap) : 0;
                
                $('#display_hours_{{ $plan->id }}').html(`<span style="font-size:18px;">S1</span> ${h1.toFixed(1)}H <span style="color:#fcd34d; font-size:18px; margin:0 5px;">|</span> <span style="font-size:18px;">S2</span> ${h2.toFixed(1)}H`);
                
                let msgBox = $('#wipValidationMsgRev{{ $plan->id }}');
                let btn = $('#btnAuthRev{{ $plan->id }}');

                if (totalTarget === 0) {
                    msgBox.html(`
                        <div class="alert-stok-info animate__animated animate__pulse">
                            <div class="title" style="color:#0f52ba;">⚠️ JADWAL AKAN DIBATALKAN</div>
                            <div class="desc" style="color:#1e3a8a;">Lanjutkan simpan untuk menghapus jadwal. <b style="font-size:12px;">${maxAvailable}</b> Pcs WIP akan kembali ke gudang!</div>
                        </div>
                    `);
                    btn.prop('disabled', false).css('background', '#ef4444').html('<i class="fas fa-trash"></i> HAPUS JADWAL');
                } else if (totalTarget > maxAvailable) {
                    msgBox.html(`
                        <div class="alert-stok-error error animate__animated animate__headShake">
                            <div class="title"><i class="fas fa-times-circle mr-1"></i> REVISI DITOLAK: STOK WIP KURANG!</div>
                            <div class="desc">Maksimal target revisi untuk part ini adalah <b style="font-size:13px;">${maxAvailable}</b> Pcs.</div>
                        </div>
                    `);
                    btn.prop('disabled', true).css('background', '#cbd5e1').html('<i class="fas fa-check"></i> AUTHORIZE REVISION');
                } else {
                    msgBox.html(`
                        <div class="alert-stok-success animate__animated animate__fadeInUp">
                            <div class="title"><i class="fas fa-check-square mr-1"></i> STOK AMAN!</div>
                            <div class="desc">Sisa WIP di gudang setelah revisi: <b style="font-size:13px;">${maxAvailable - totalTarget}</b> Pcs.</div>
                        </div>
                    `);
                    btn.prop('disabled', false).css('background', '#f59e0b').html('<i class="fas fa-check"></i> AUTHORIZE REVISION');
                }
            });

            // Langsung munculin status saat modal revisi dibuka
            $('#modalEditPlan{{ $plan->id }}').on('shown.bs.modal', function () {
                $('.calc-rev-{{ $plan->id }}').first().trigger('input');
            });
            @endif
        @endforeach
    });
</script>
@endsection