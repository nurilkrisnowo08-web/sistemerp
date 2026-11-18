@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --app-bg: #f8fafc;
        --ind-navy: #1e293b;
        --ind-blue: #3b82f6;
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
    .kpi-value { font-weight: 800; font-size: 2rem; color: var(--ind-navy); }
    
    .station-panel { background: white; border-radius: 16px; border: 1px solid var(--ind-border); overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 30px; }
    .station-header { background: #475569; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; color: white;}
    
    .table-mps thead th { background: #fdfaf6; color: var(--ind-slate); font-size: 10px; font-weight: 900; text-transform: uppercase; padding: 15px; border: none; letter-spacing: 0.5px; }
    .table-mps tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px dashed #e2e8f0; font-weight: 600; font-size: 12px; }
    
    /* BUTTONS MAIN PAGE */
    .shift-toggle { background: #e2e8f0; border-radius: 50px; padding: 4px; display: inline-flex; }
    .shift-btn { border-radius: 50px; padding: 8px 20px; font-weight: 900; font-size: 11px; transition: 0.3s; border: none; color: #64748b; text-decoration: none; }
    .shift-btn.active { background: #1e293b; color: white; }
    
    .btn-pill { border-radius: 50rem; padding: 12px 24px; font-weight: 800; font-size: 12px; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: none; cursor: pointer; text-decoration: none;}
    .btn-new-plan { background: #4f46e5; color: white; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3); }
    
    .btn-print-wos { background: transparent; color: white; border: 1px solid rgba(255,255,255,0.4); border-radius: 50rem; padding: 6px 16px; font-size: 10px; font-weight: 800; }
    .btn-print-bprm { background: #3b82f6; color: white; border: none; border-radius: 50rem; padding: 6px 16px; font-size: 10px; font-weight: 800; }
    .btn-revisi { background: #f59e0b; color: white; border-radius: 50rem; padding: 6px 16px; font-size: 10px; font-weight: 800; }
    
    .time-chip { font-family: 'JetBrains Mono'; background: #f1f5f9; color: var(--ind-navy); padding: 5px 12px; border-radius: 8px; font-size: 11.5px; font-weight: 700; border: 1px solid #e2e8f0; }
    .font-mono { font-family: 'JetBrains Mono', monospace; font-weight: 800; }

    /* ========================================================
       ✨ STYLE KHUSUS MODAL REGISTER MRP (PERSIS GAMBAR) ✨ 
       ======================================================== */
    .mrp-modal-content { border-radius: 16px; border: none; overflow: hidden; background: #ffffff; box-shadow: 0 25px 50px rgba(0,0,0,0.25); }
    .mrp-modal-header { background: #3b82f6; padding: 20px 30px; border-bottom: none; display: flex; align-items: center; }
    .mrp-modal-title { font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 16px; color: #ffffff; margin: 0; letter-spacing: 0.5px; text-transform: uppercase;}
    
    .mrp-card-box { background: #ffffff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 20px; height: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
    .mrp-card-title { font-size: 12.5px; font-weight: 800; margin-bottom: 20px; letter-spacing: 0.5px; text-transform: uppercase; display: flex; align-items: center; gap: 8px;}
    
    /* Warna Header Kolom */
    .col-id .mrp-card-title { color: var(--ind-blue); }
    .col-pm .mrp-card-title { color: var(--ind-cyan); }
    .col-qa .mrp-card-title { color: var(--ind-green); }
    
    .mrp-label { font-size: 10px; font-weight: 800; color: #0f172a; margin-bottom: 6px; display: block;}
    .mrp-input { border-radius: 8px; border: 1px solid #e2e8f0; padding: 10px 15px; font-weight: 700; font-size: 12px; color: #1e293b; transition: 0.3s; width: 100%; outline: none; background: #ffffff;}
    .mrp-input:focus { border-color: var(--ind-blue); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .mrp-input[readonly] { background: #f8fafc; color: #64748b; border-color: #f1f5f9; }
    
    /* Kotak Calculated M/C Load */
    .mc-load-box { background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 12px; padding: 20px; text-align: center; margin-top: 15px; }
    .mc-load-label { font-size: 9px; font-weight: 800; color: #0369a1; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;}
    .mc-load-val { font-family: 'Orbitron', sans-serif; font-size: 24px; font-weight: 900; color: #06b6d4; letter-spacing: 1px;}
    
    /* Tabs Shift */
    .mrp-tabs { display: flex; border: 1px solid var(--ind-blue); border-radius: 6px; overflow: hidden; margin-bottom: 20px; }
    .mrp-tab { flex: 1; text-align: center; font-size: 11px; font-weight: 800; padding: 10px; cursor: pointer; background: #ffffff; color: var(--ind-slate); transition: 0.2s;}
    .mrp-tab.active { background: var(--ind-blue); color: white; }
    .tab-content-mrp { display: none; }
    .tab-content-mrp.active { display: block; }
    
    /* Custom Alert Bawah */
    .alert-stok-error { background-color: #fef2f2; border-radius: 8px; padding: 12px 20px; text-align: center; border: 1px solid #fecaca; width: 100%; margin-bottom: 20px; color: #dc2626;}
    .alert-stok-error .title { font-weight: 900; font-size: 12px; margin-bottom: 4px; letter-spacing: 0.5px;}
    .alert-stok-error .desc { font-size: 11px; font-weight: 600; }
    
    .alert-stok-success { background-color: #ecfdf5; border-radius: 8px; padding: 12px 20px; text-align: center; border: 1px solid #a7f3d0; width: 100%; margin-bottom: 20px; color: #059669;}
    .alert-stok-success .title { font-weight: 900; font-size: 12px; margin-bottom: 4px; letter-spacing: 0.5px;}
    .alert-stok-success .desc { font-size: 11px; font-weight: 600; }

    /* Tombol Aksi Bawah */
    .btn-abort { background: #94a3b8; color: white; border-radius: 50rem; padding: 12px 35px; font-weight: 800; font-size: 12px; border: none; cursor: pointer; transition: 0.3s;}
    .btn-abort:hover { background: #64748b; }
    .btn-auth { background: var(--ind-blue); color: white; border-radius: 50rem; padding: 12px 35px; font-weight: 800; font-size: 12px; border: none; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);}
    .btn-auth:hover { background: #2563eb; transform: translateY(-2px);}

    /* Select2 Khusus Modal */
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
                <i class="fas fa-plus"></i> NEW_PLAN
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
        <p class="text-muted mb-0" style="font-weight:600;">Klik tombol <b style="color:var(--ind-blue);">+ NEW_PLAN</b> untuk menyusun jadwal.</p>
    </div>
    @endforelse
</div>


{{-- ========================================================================================= --}}
{{-- ✨ 1. MODAL REGISTER NEW PLAN (DESAIN 3 KOLOM SEPERTI GAMBAR) ✨ --}}
{{-- ========================================================================================= --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalRegisterPlan" tabindex="-1" style="z-index: 9999 !important;">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1050px;">
        <div class="modal-content mrp-modal-content">
            <div class="mrp-modal-header" style="background: #1e293b;">
                <h5 class="mrp-modal-title">
                    <i class="fas fa-plus-circle mr-2" style="color: #4f73d9;"></i> REGISTER_NEW_PLAN
                </h5>
                <button type="button" class="close text-white ml-auto" data-dismiss="modal" style="opacity:1;"><span>&times;</span></button>
            </div>
            
            <form action="{{ route('ppic.welding.mps_store') }}" method="POST" id="formNewPlan">
                @csrf
                <div class="modal-body p-4 p-md-5" style="background:#f8fafc;">
                    <div class="row">
                        <!-- KOLOM 1: Identifikasi -->
                        <div class="col-md-4 mb-3 col-id">
                            <div class="mrp-card-box border-0 shadow-sm">
                                <h6 class="mrp-card-title"><i class="fas fa-tag mr-2"></i> 01. IDENTIFICATION</h6>
                                
                                <label class="mrp-label">Tanggal Produksi</label>
                                <input type="date" name="plan_date" class="mrp-input mb-4" value="{{ $date }}" required>
                                
                                <label class="mrp-label">Station Code</label>
                                <select name="line_code" class="mrp-input mb-4" required>
                                    <option value="">Pilih Station...</option>
                                    @foreach($availableLines as $line)
                                        <option value="{{ $line->kode_line }}">{{ $line->kode_line }}</option>
                                    @endforeach
                                </select>
                                
                                <label class="mrp-label">Customer (Auto)</label>
                                <input type="text" id="custDispNew" name="customer_code" class="mrp-input mb-0 font-mono" value="AUTO DETECT" readonly>
                            </div>
                        </div>

                        <!-- KOLOM 2: M/C Load (UI SEPERTI STAMPING, TANPA STOK) -->
                        <div class="col-md-4 mb-3 col-pm">
                            <div class="mrp-card-box border-0 shadow-sm d-flex flex-column">
                                <h6 class="mrp-card-title"><i class="fas fa-search mr-1"></i> 02. MATERIAL & WIP</h6>
                                
                                <label class="mrp-label">Pilih Part Number</label>
                                <select name="part_no" id="partSelectorNew" class="mrp-input select2-part" required style="width:100%;">
                                    <option value="">Ketik Part No / Nama...</option>
                                    @foreach($availableParts as $p)
                                        <option value="{{ $p->part_no }}" data-cust="{{ $p->customer_code }}">
                                            [{{ $p->customer_code }}] {{ $p->part_no }} - {{ $p->part_name }}
                                        </option>
                                    @endforeach
                                </select>
                                
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
                                <h6 class="mrp-card-title"><i class="fas fa-crosshairs mr-1"></i> 03. TARGET_ALLOCATION</h6>
                                
                                <div class="mrp-tabs">
                                    <div class="mrp-tab active" data-tab="shift1">SHIFT_1</div>
                                    <div class="mrp-tab" data-tab="shift2">SHIFT_2</div>
                                </div>
                                
                                <!-- Tab 1 -->
                                <div class="tab-content-mrp active" id="tab-shift1">
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label class="mrp-label text-primary">S1 Reguler Plan</label>
                                            <input type="number" name="s1_plan_reg" class="mrp-input font-mono calc-new" value="0" required>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="mrp-label text-danger">S1 Overtime Plan</label>
                                            <input type="number" name="s1_plan_ot" class="mrp-input font-mono calc-new" value="0" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Tab 2 -->
                                <div class="tab-content-mrp" id="tab-shift2">
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label class="mrp-label text-primary">S2 Reguler Plan</label>
                                            <input type="number" name="s2_plan_reg" class="mrp-input font-mono calc-new" value="0" required>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="mrp-label text-danger">S2 Overtime Plan</label>
                                            <input type="number" name="s2_plan_ot" class="mrp-input font-mono calc-new" value="0" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-6">
                                        <label class="mrp-label text-muted">Cap/Hr</label>
                                        <input type="number" name="cap_per_hour" id="cap_new" class="mrp-input font-mono calc-new" value="100" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="mrp-label text-muted">Manpower</label>
                                        <input type="number" name="manpower" class="mrp-input font-mono" value="1" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer d-flex flex-column align-items-center pb-4 pt-0" style="background:#f8fafc; border:none;">
                    <!-- KOTAK NOTIFIKASI ERROR/SUKSES MUNCUL DI SINI PAS KLIK DEPLOY -->
                    <div id="wipValidationMsgNew" class="w-100 px-3"></div>
                    
                    <div class="d-flex justify-content-center gap-3 w-100 mt-2">
                        <button type="button" class="btn-abort" data-dismiss="modal">ABORT</button>
                        <button type="submit" class="btn-auth"><i class="fas fa-rocket mr-1"></i> AUTHORIZE & DEPLOY</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✨ 2. MODAL REVISI (DESAIN 3 KOLOM SEPERTI GAMBAR) ✨ --}}
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
                                <div class="mrp-card-box shadow-sm border-0">
                                    <h6 class="mrp-card-title" style="color:#d97706;"><i class="fas fa-tag mr-2"></i>01. PRODUCT_IDENTITY</h6>
                                    
                                    <label class="mrp-label">Select Line</label>
                                    <select name="line_code" class="mrp-input mb-4" required>
                                        @foreach($availableLines as $line)
                                            <option value="{{ $line->kode_line }}" {{ $plan->line_code == $line->kode_line ? 'selected' : '' }}>{{ $line->kode_line }}</option>
                                        @endforeach
                                    </select>
                                    
                                    <label class="mrp-label">Part No (Locked)</label>
                                    <input type="text" class="mrp-input mb-0 font-mono" value="{{ $plan->part_no }}" readonly>
                                </div>
                            </div>
                            
                            <!-- BOX 2: M/C Load -->
                            <div class="col-md-4 mb-3 col-pm">
                                <div class="mrp-card-box shadow-sm border-0 d-flex flex-column">
                                    <h6 class="mrp-card-title" style="color:#d97706;"><i class="fas fa-search mr-2"></i>02. MATERIAL & WIP</h6>
                                    
                                    <div class="mc-load-box mt-auto mb-2" style="background:#fffbeb; border-color:#fde68a;">
                                        <label class="mc-load-label" style="color:#b45309;">CALCULATED M/C LOAD</label>
                                        @php 
                                            $hS1 = ($plan->cap_per_hour > 0) ? (($plan->s1_plan_reg + $plan->s1_plan_ot) / $plan->cap_per_hour) : 0;
                                            $hS2 = ($plan->cap_per_hour > 0) ? (($plan->s2_plan_reg + $plan->s2_plan_ot) / $plan->cap_per_hour) : 0;
                                        @endphp
                                        <div class="mc-load-val" id="display_hours_{{ $plan->id }}" style="color:#d97706;">
                                            <span style="font-size:18px;">S1</span> {{ number_format($hS1, 1) }}H <span style="color:#fcd34d; font-size:18px; margin:0 5px;">|</span> <span style="font-size:18px;">S2</span> {{ number_format($hS2, 1) }}H
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- BOX 3: Target -->
                            <div class="col-md-4 mb-3 col-qa">
                                <div class="mrp-card-box pb-2 shadow-sm border-0">
                                    <h6 class="mrp-card-title" style="color:#d97706;"><i class="fas fa-crosshairs mr-1"></i> 03. QUANTITY_REVISION</h6>
                                    
                                    <div class="mrp-tabs">
                                        <div class="mrp-tab rev-tab-s1-{{ $plan->id }} active" onclick="switchRevTab('{{ $plan->id }}', 1)" style="border-color:#d97706;">SHIFT_1</div>
                                        <div class="mrp-tab rev-tab-s2-{{ $plan->id }}" onclick="switchRevTab('{{ $plan->id }}', 2)" style="border-color:#d97706;">SHIFT_2</div>
                                    </div>
                                    
                                    <!-- Tab 1 Rev -->
                                    <div class="tab-content-mrp rev-cont-s1-{{ $plan->id }} active">
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label class="mrp-label text-primary">S1 Reguler Plan</label>
                                                <input type="number" name="s1_plan_reg" class="mrp-input font-mono calc-rev-{{ $plan->id }}" value="{{ $plan->s1_plan_reg }}" required>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="mrp-label text-danger">S1 Overtime Plan</label>
                                                <input type="number" name="s1_plan_ot" class="mrp-input font-mono calc-rev-{{ $plan->id }}" value="{{ $plan->s1_plan_ot }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Tab 2 Rev -->
                                    <div class="tab-content-mrp rev-cont-s2-{{ $plan->id }}">
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label class="mrp-label text-primary">S2 Reguler Plan</label>
                                                <input type="number" name="s2_plan_reg" class="mrp-input font-mono calc-rev-{{ $plan->id }}" value="{{ $plan->s2_plan_reg }}" required>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="mrp-label text-danger">S2 Overtime Plan</label>
                                                <input type="number" name="s2_plan_ot" class="mrp-input font-mono calc-rev-{{ $plan->id }}" value="{{ $plan->s2_plan_ot }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-2">
                                        <div class="col-6">
                                            <label class="mrp-label text-muted">Cap/Hr</label>
                                            <input type="number" name="cap_per_hour" id="cap_rev_{{ $plan->id }}" class="mrp-input font-mono calc-rev-{{ $plan->id }}" value="{{ $plan->cap_per_hour }}" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="mrp-label text-muted">Manpower</label>
                                            <input type="number" name="manpower" class="mrp-input font-mono" value="{{ $plan->manpower }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer d-flex flex-column align-items-center pb-4 pt-0" style="background:#f8fafc; border:none;">
                        <!-- KOTAK NOTIFIKASI ERROR MUNCUL DI SINI PAS KLIK DEPLOY -->
                        <div id="wipValidationMsgRev{{ $plan->id }}" class="w-100 px-3"></div>
                        
                        <div class="d-flex justify-content-center gap-3 w-100 mt-2">
                            <button type="button" class="btn-abort" data-dismiss="modal">ABORT</button>
                            <button type="submit" class="btn-auth" style="background:#f59e0b; box-shadow: 0 4px 15px rgba(245,158,11,0.4);"><i class="fas fa-check mr-1"></i> AUTHORIZE REVISION</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

<!-- JSON DATA: STOK SEMUA PART SUDAH DIMUAT SEJAK AWAL AGAR NO DELAY! -->
<script>
    const wipDataMap = {
        @foreach($availableParts as $p)
        @php
            $fg = \Illuminate\Support\Facades\DB::table('finished_goods')->where('part_no', $p->part_no)->first();
            $wipStock = $fg ? (int)$fg->welding_stock : 0;
        @endphp
        "{!! $p->part_no !!}": { 
            wip: {{ $wipStock }}, 
            cust: "{!! $p->customer_code !!}" 
        },
        @endforeach
    };
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Tab Scripting
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
        // Init Select2 khusus Modal
        $('.select2-part').select2({
            dropdownParent: $('#modalRegisterPlan'),
            placeholder: "Ketik Part No / Nama...",
            allowClear: true,
            width: '100%'
        });

        // 1. AUTO CUSTOMER NAME SAAT PART DIPILIH
        $('#partSelectorNew').on('change', function() {
            let partNo = $(this).val();
            if(partNo && wipDataMap[partNo]) {
                let cust = wipDataMap[partNo].cust || 'AUTO DETECT';
                $('#custDispNew').val(cust); 
            } else {
                $('#custDispNew').val('AUTO DETECT');
            }
            // Hapus pesan error jika ganti part
            $('#wipValidationMsgNew').html('');
        });

        // 2. KALKULASI JAM SAAT MENGETIK (NEW PLAN)
        $('.calc-new').on('input', function() { 
            $('#wipValidationMsgNew').html(''); // Hapus pesan error saat mulai ngetik lagi

            let s1r = parseInt($('#modalRegisterPlan input[name="s1_plan_reg"]').val()) || 0;
            let s1o = parseInt($('#modalRegisterPlan input[name="s1_plan_ot"]').val()) || 0;
            let s2r = parseInt($('#modalRegisterPlan input[name="s2_plan_reg"]').val()) || 0;
            let s2o = parseInt($('#modalRegisterPlan input[name="s2_plan_ot"]').val()) || 0;
            
            let cap = parseFloat($('#cap_new').val()) || 0;
            let h1 = (cap > 0) ? ((s1r + s1o) / cap) : 0;
            let h2 = (cap > 0) ? ((s2r + s2o) / cap) : 0;
            
            $('#display_hours_new').html(`<span style="font-size:18px;">S1</span> ${h1.toFixed(1)}H <span style="color:#94a3b8; font-size:18px; margin:0 5px;">|</span> <span style="font-size:18px;">S2</span> ${h2.toFixed(1)}H`);
        });

        // 3. CEK VALIDASI STOK HANYA SAAT TOMBOL DEPLOY DIKLIK
        $('#formNewPlan').on('submit', function(e) {
            let partNo = $('#partSelectorNew').val();
            if(!partNo) { e.preventDefault(); return; } 
            
            let s1r = parseInt($('#modalRegisterPlan input[name="s1_plan_reg"]').val()) || 0;
            let s1o = parseInt($('#modalRegisterPlan input[name="s1_plan_ot"]').val()) || 0;
            let s2r = parseInt($('#modalRegisterPlan input[name="s2_plan_reg"]').val()) || 0;
            let s2o = parseInt($('#modalRegisterPlan input[name="s2_plan_ot"]').val()) || 0;
            let totalTarget = s1r + s1o + s2r + s2o;
            
            let currentWipStock = wipDataMap[partNo].wip;
            let msgBoxNew = $('#wipValidationMsgNew');

            if (totalTarget === 0) {
                e.preventDefault(); 
                msgBoxNew.html(`<div class="alert-stok-error animate__animated animate__headShake"><div class="title">❌ TARGET KOSONG</div><div class="desc">Harap isi angka target di salah satu shift!</div></div>`);
            } else if (totalTarget > currentWipStock) {
                e.preventDefault(); // Stop Submit
                msgBoxNew.html(`
                    <div class="alert-stok-error animate__animated animate__headShake">
                        <div class="title"><i class="fas fa-times-circle mr-1"></i> PLAN DITOLAK: STOK WIP KURANG!</div>
                        <div class="desc">Butuh: <b style="font-size:13px;">${totalTarget}</b> Pcs | Tersedia di Gudang WIP: <b style="font-size:13px;">${currentWipStock}</b> Pcs</div>
                    </div>
                `);
            } else {
                // Aman, Biarkan form lanjut submit ke server (Tidak di e.preventDefault)
                msgBoxNew.html('');
            }
        });

        // ==============================================================
        // 4. CEK VALIDASI STOK HANYA SAAT TOMBOL REVISI DIKLIK
        // ==============================================================
        @foreach($plans as $plan)
            @if($plan->total_actual == 0)
            
            // Kalkulasi jam & Hapus Notif Error saat ngetik ulang
            $('.calc-rev-{{ $plan->id }}').on('input', function() { 
                let s1r = parseInt($('#modalEditPlan{{ $plan->id }} input[name="s1_plan_reg"]').val()) || 0;
                let s1o = parseInt($('#modalEditPlan{{ $plan->id }} input[name="s1_plan_ot"]').val()) || 0;
                let s2r = parseInt($('#modalEditPlan{{ $plan->id }} input[name="s2_plan_reg"]').val()) || 0;
                let s2o = parseInt($('#modalEditPlan{{ $plan->id }} input[name="s2_plan_ot"]').val()) || 0;
                let cap = parseFloat($('#cap_rev_{{ $plan->id }}').val()) || 0;
                
                let h1 = (cap > 0) ? ((s1r + s1o) / cap) : 0;
                let h2 = (cap > 0) ? ((s2r + s2o) / cap) : 0;
                
                $('#display_hours_{{ $plan->id }}').html(`<span style="font-size:18px;">S1</span> ${h1.toFixed(1)}H <span style="color:#fcd34d; font-size:18px; margin:0 5px;">|</span> <span style="font-size:18px;">S2</span> ${h2.toFixed(1)}H`);
                
                // Realtime Notif pembatalan kalo diisi angka 0
                let totalTgt = s1r + s1o + s2r + s2o;
                if (totalTgt === 0) {
                    $('#wipValidationMsgRev{{ $plan->id }}').html(`
                        <div class="alert-stok-info animate__animated animate__pulse">
                            <div class="title" style="color:#0f52ba;">⚠️ JADWAL AKAN DIBATALKAN</div>
                            <div class="desc" style="color:#1e3a8a;">Lanjutkan simpan untuk menghapus jadwal. WIP akan kembali ke gudang!</div>
                        </div>
                    `);
                } else {
                    $('#wipValidationMsgRev{{ $plan->id }}').html('');
                }
            });

            // Pas Klik Submit Revisi
            $('#formRev{{ $plan->id }}').on('submit', function(e) {
                let partNo = '{{ $plan->part_no }}';
                let oldTarget = {{ $plan->s1_plan_reg + $plan->s1_plan_ot + $plan->s2_plan_reg + $plan->s2_plan_ot }};
                let currentWipStock = wipDataMap[partNo].wip;
                
                let maxAvailable = currentWipStock + oldTarget;
                
                let s1r = parseInt($('#modalEditPlan{{ $plan->id }} input[name="s1_plan_reg"]').val()) || 0;
                let s1o = parseInt($('#modalEditPlan{{ $plan->id }} input[name="s1_plan_ot"]').val()) || 0;
                let s2r = parseInt($('#modalEditPlan{{ $plan->id }} input[name="s2_plan_reg"]').val()) || 0;
                let s2o = parseInt($('#modalEditPlan{{ $plan->id }} input[name="s2_plan_ot"]').val()) || 0;
                let newTarget = s1r + s1o + s2r + s2o;
                
                let msgBox = $('#wipValidationMsgRev{{ $plan->id }}');

                if (newTarget > maxAvailable) {
                    e.preventDefault(); // Stop Submit
                    msgBox.html(`
                        <div class="alert-stok-error animate__animated animate__headShake">
                            <div class="title"><i class="fas fa-times-circle mr-1"></i> REVISI DITOLAK: STOK WIP KURANG!</div>
                            <div class="desc">Maksimal target revisi untuk part ini adalah <b style="font-size:13px;">${maxAvailable}</b> Pcs.</div>
                        </div>
                    `);
                }
                // Jika <= maxAvailable, biarkan form jalan (submit)
            });
            @endif
        @endforeach
    });
</script>
@endsection