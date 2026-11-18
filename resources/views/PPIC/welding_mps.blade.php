@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --app-bg: #f8fafc;
        --ind-navy: #1e293b;
        --ind-blue: #4f46e5;
        --ind-cyan: #06b6d4;
        --ind-green: #10b981;
        --ind-slate: #64748b;
        --ind-border: #e2e8f0;
    }
    body { background-color: var(--app-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--ind-navy); }
    
    .hud-title { font-family: 'Orbitron', sans-serif; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; }
    
    .glass-card { background: white; border-radius: 24px; border: 1px solid rgba(226, 232, 240, 0.8); box-shadow: 0 10px 30px -5px rgba(0,0,0,0.03); overflow: hidden; }
    
    .machine-switcher { background: #f1f5f9; padding: 6px; border-radius: 50px; display: inline-flex; border: 1px solid #e2e8f0; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); }
    .machine-switcher .btn { border-radius: 50px; font-weight: 800; padding: 10px 28px; font-size: 12px; letter-spacing: 0.5px; transition: 0.3s; text-transform: uppercase; border: none; color: #64748b; }
    .machine-switcher .btn.active { background: #4f46e5; color: white; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.35); }
    
    .table-mps { width: 100%; border-collapse: separate; border-spacing: 0; }
    .table-mps thead th { background: #f8fafc; padding: 18px 12px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--ind-slate); border-bottom: 2px solid #e2e8f0; }
    .table-mps tbody td { padding: 18px 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; font-weight: 600; vertical-align: middle; }
    .table-mps tbody tr:hover td { background-color: #f8fafc; }
    
    .badge-time { background: #fff9c4; color: #856404; font-family: 'JetBrains Mono', monospace; padding: 6px 12px; border-radius: 8px; font-weight: 800; border: 1px solid #ffe58f; font-size: 11px; }
    .font-mono { font-family: 'JetBrains Mono', monospace; font-weight: 800; }

    /* ========================================================
       ✨ STYLE MODAL PERSIS GAMBAR (3 KOLOM) ✨ 
       ======================================================== */
    .mrp-modal-content { border-radius: 16px; border: none; overflow: hidden; background: #ffffff; box-shadow: 0 25px 50px rgba(0,0,0,0.25); }
    .mrp-modal-header { background: #4f73d9; padding: 20px 30px; border-bottom: none; display: flex; align-items: center; }
    .mrp-modal-title { font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 16px; color: #ffffff; margin: 0; letter-spacing: 0.5px; text-transform: uppercase;}
    
    .mrp-card-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; height: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
    .mrp-card-title { font-size: 13px; font-weight: 800; margin-bottom: 20px; letter-spacing: 0.5px; text-transform: uppercase; }
    
    .mrp-label { font-size: 10px; font-weight: 800; color: #0f172a; margin-bottom: 8px; display: block;}
    .mrp-input { border-radius: 8px; border: 1px solid #cbd5e1; padding: 10px 15px; font-weight: 700; font-size: 12px; color: #1e293b; width: 100%; outline: none; transition: 0.2s;}
    .mrp-input:focus { border-color: #4f73d9; box-shadow: 0 0 0 3px rgba(79, 115, 217, 0.1); }
    .mrp-input[readonly] { background: #f8fafc; color: #64748b; border-color: #e2e8f0; cursor: not-allowed; }
    
    /* M/C LOAD BOX (CYAN) */
    .mc-load-box { background: #ecfeff; border: 1px solid #a5f3fc; border-radius: 12px; padding: 20px; text-align: center; margin-top: 15px; }
    .mc-load-label { font-size: 10px; font-weight: 800; color: #0369a1; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;}
    .mc-load-val { font-family: 'Orbitron', sans-serif; font-size: 26px; font-weight: 900; color: #06b6d4; letter-spacing: 1px;}
    
    .btn-abort { background: #94a3b8; color: white; border-radius: 50rem; padding: 12px 35px; font-weight: 800; font-size: 12px; border: none; cursor: pointer; transition: 0.3s;}
    .btn-auth { background: #4f73d9; color: white; border-radius: 50rem; padding: 12px 35px; font-weight: 800; font-size: 12px; border: none; cursor: pointer; transition: 0.3s;}
    .btn-auth:disabled { background: #cbd5e1; cursor: not-allowed; }

    .select2-container--default .select2-selection--single { border: 1px solid #cbd5e1; border-radius: 8px; height: 41px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 39px; font-weight: 700; font-size: 12px; color: #1e293b; padding-left: 15px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 39px; right: 10px; }
</style>

<div class="container-fluid mt-4 mb-5 animate__animated animate__fadeIn">
    
    @if(session('success')) 
    <div class="alert alert-success border-0 shadow-sm animate__animated animate__bounceIn" style="border-radius: 12px; background:#d1fae5; color:#065f46; font-weight:800; padding:15px;">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div> 
    @endif
    @if(session('error')) 
    <div class="alert alert-danger border-0 shadow-sm animate__animated animate__shakeX" style="border-radius: 12px; background:#fee2e2; color:#991b1b; font-weight:800; padding:15px;">
        <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
    </div> 
    @endif

    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
        <div>
            <h2 class="hud-title m-0" style="font-size: 28px;">MPS_WELDING <span style="color: #4f46e5;">ENGINE</span></h2>
            <div class="d-flex align-items-center mt-2">
                <span class="badge badge-dark px-3 py-1 mr-3" style="border-radius: 6px; font-weight:800; font-size:10px;">SYSTEM_ACTIVE</span>
                <span style="font-size:11px; font-weight:800; color:var(--ind-slate);"><i class="far fa-calendar-alt mr-1"></i> {{ date('D, d M Y', strtotime($date)) }}</span>
            </div>
        </div>

        <div class="machine-switcher mx-xl-auto my-3 my-xl-0">
            <a href="?date={{$date}}&shift=S1" class="btn {{ $shift == 'S1' ? 'active' : '' }}">SHIFT_01</a>
            <a href="?date={{$date}}&shift=S2" class="btn {{ $shift == 'S2' ? 'active' : '' }}">SHIFT_02</a>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form action="" method="GET" class="mr-2">
                <input type="hidden" name="shift" value="{{ $shift }}">
                <input type="date" name="date" class="form-control rounded-pill border-dark px-4 font-weight-bold" style="height: 42px;" value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <button class="btn btn-primary rounded-pill font-weight-bold px-4 shadow-sm" data-toggle="modal" data-target="#modalRegisterPlan" style="height:42px;">
                <i class="fas fa-plus-circle mr-1"></i> REGISTER PLAN
            </button>
        </div>
    </div>

    <!-- Tabel MPS -->
    @forelse($plans->groupBy('line_code') as $lineCode => $linePlans)
    <div class="glass-card mb-4 animate__animated animate__fadeInUp">
        <div class="p-3 d-flex justify-content-between align-items-center" style="background:#1e293b; color:white;">
            <h6 class="mb-0 font-weight-bold ml-3" style="font-family:'Orbitron'; letter-spacing:1px;">
                <i class="fas fa-robot text-info mr-2"></i> STATION: {{ $lineCode }}
            </h6>
            <div>
                <a href="{{ url('/ppic/welding/wos/print/'.$date.'/'.$lineCode) }}" target="_blank" class="btn btn-sm btn-light font-weight-bold px-3 mr-2 rounded-pill"><i class="fas fa-print mr-1"></i> PRINT WOS</a>
                <a href="{{ url('/ppic/welding/bprm/print/'.$date.'/'.$lineCode) }}" target="_blank" class="btn btn-sm btn-info font-weight-bold px-3 rounded-pill"><i class="fas fa-file-alt mr-1"></i> PRINT BPRM</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-mps text-center">
                <thead>
                    <tr>
                        <th class="text-center" width="50">Pos</th>
                        <th class="text-left pl-4">Part Identification</th>
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
                        <td class="text-left pl-4" style="border-left:4px solid #3b82f6;">
                            <div style="font-weight:900; color:#1e293b; font-size:13px;">{{ $plan->part_name }}</div>
                            <div class="font-mono text-muted mt-1" style="font-size:10px;">{{ $plan->part_no }}</div>
                        </td>
                        <td class="text-center"><span class="badge badge-light border px-2 py-1">{{ $plan->manpower }}P</span></td>
                        <td class="text-center font-mono text-dark" style="font-size: 14px;">{{ number_format($plan->total_target) }}</td>
                        <td class="text-center font-mono text-primary" style="font-size: 14px;">{{ number_format($plan->total_actual) }}</td>
                        <td class="text-center font-mono">
                            @if($plan->balance > 0) <span class="badge badge-danger" style="background:#fee2e2; color:#b91c1c;">-{{ number_format($plan->balance) }}</span>
                            @else <span class="badge badge-success" style="background:#d1fae5; color:#047857;">+{{ abs($plan->balance) }}</span> @endif
                        </td>
                        <td class="text-center"><span class="badge-time">{{ $plan->start_time }}</span></td>
                        <td class="text-center"><span class="badge-time">{{ $plan->ahir_time }}</span></td>
                        <td class="text-center">
                            @if($percent >= 100) <i class="fas fa-check-circle text-success" style="font-size:18px;"></i>
                            @elseif($percent > 0) <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                            @else <i class="far fa-clock text-muted" style="font-size:18px;"></i> @endif
                        </td>
                        <td class="text-center">
                            @if($plan->total_actual > 0)
                                <button class="btn btn-sm btn-light border font-weight-bold px-3 shadow-none rounded-pill" disabled><i class="fas fa-lock text-muted"></i></button>
                            @else
                                <button class="btn btn-sm btn-warning font-weight-bold text-dark px-3 rounded-pill shadow-sm" data-toggle="modal" data-target="#modalEditPlan-{{ $plan->id }}"><i class="fas fa-edit"></i> Edit</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="text-center py-5">
        <i class="fas fa-clipboard-list text-muted mb-3" style="font-size:40px; opacity:0.3;"></i>
        <h5 style="font-weight:900; color:var(--ind-slate);">TIDAK ADA JADWAL WELDING</h5>
        <p class="text-muted mb-0" style="font-weight:600;">Klik tombol <b style="color:#4f46e5;">+ REGISTER PLAN</b> untuk menyusun jadwal.</p>
    </div>
    @endforelse
</div>

{{-- ========================================================================================= --}}
{{-- ✨ 1. MODAL REGISTER NEW PLAN (3 KOLOM 100% PERSIS STAMPING + LOGIKA JS ANTI-GAGAL) ✨ --}}
{{-- ========================================================================================= --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalRegisterPlan" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1100px;">
        <div class="modal-content mrp-modal-content">
            <div class="mrp-modal-header">
                <h5 class="mrp-modal-title">
                    <i class="fas fa-plus-circle mr-2"></i> REGISTER_PLAN_SYSTEM
                </h5>
                <button type="button" class="close text-white ml-auto" data-dismiss="modal"><span>&times;</span></button>
            </div>
            
            <form action="{{ route('ppic.welding.mps_store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 p-md-5">
                    <div class="row">
                        <!-- KOLOM 1: Identifikasi -->
                        <div class="col-md-4 border-right pr-4">
                            <h6 class="mrp-card-title" style="color: #3b82f6;"><i class="fas fa-tag mr-2"></i> 01. PRODUCT_IDENTITY</h6>
                            
                            <label class="mrp-label">Tanggal Produksi</label>
                            <input type="date" name="plan_date" class="mrp-input mb-4" value="{{ $date }}" required>
                            
                            <label class="mrp-label">Select Line</label>
                            <select name="line_code" class="mrp-input mb-4" required>
                                <option value="">-- CHOOSE STATION --</option>
                                @foreach($availableLines as $line)
                                    <option value="{{ $line->kode_line }}">{{ $line->kode_line }}</option>
                                @endforeach
                            </select>
                            
                            <label class="mrp-label">Customer (Auto)</label>
                            <input type="text" id="add_cust" name="customer_code" class="mrp-input mb-4 font-mono" value="AUTO DETECT" readonly>

                            <label class="mrp-label">Part Identification</label>
                            <select name="part_no" id="add_part" class="mrp-input select2-part add-calc" required style="width:100%;">
                                <option value="">-- PILIH PART NUMBER --</option>
                                @foreach($availableParts as $p)
                                    @php
                                        // Bersihkan string dan ambil stok gudang langsung pake PHP biar JS gak bingung
                                        $cleanPart = trim(urldecode($p->part_no));
                                        if (strpos($cleanPart, ' - ') !== false) $cleanPart = trim(explode(' - ', $cleanPart)[0]);
                                        
                                        $fg = \DB::table('finished_goods')->where('part_no', $cleanPart)->first();
                                        $wip = $fg ? (int)$fg->welding_stock : 0;
                                        $cust = $p->customer_code ?? 'SAI';
                                    @endphp
                                    <option value="{{ $cleanPart }}" data-wip="{{ $wip }}" data-cust="{{ $cust }}">
                                        [{{ $cust }}] {{ $cleanPart }} - {{ $p->part_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- KOLOM 2: M/C Load (PERSIS GAMBAR STAMPING) -->
                        <div class="col-md-4 border-right px-4">
                            <h6 class="mrp-card-title" style="color: #06b6d4;"><i class="fas fa-chart-line mr-2"></i> 02. PERFORMANCE_METRICS</h6>
                            
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="mrp-label">Manpower</label>
                                    <input type="number" name="manpower" class="mrp-input font-mono add-calc" value="1" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="mrp-label">Process</label>
                                    <input type="number" name="process_qty" class="mrp-input font-mono" value="1" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="mrp-label text-primary">Cap/Hour</label>
                                    <input type="number" name="cap_per_hour" id="add_cap" class="mrp-input font-mono add-calc" style="border-color:#3b82f6;" value="400" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="mrp-label text-warning">Dandory (m)</label>
                                    <input type="number" name="dandory_time" id="add_dandory" class="mrp-input font-mono add-calc" value="15">
                                </div>
                            </div>
                            
                            <!-- KOTAK M/C LOAD -->
                            <div class="mc-load-box">
                                <label class="mc-load-label">CALCULATED M/C LOAD</label>
                                <div class="mc-load-val" id="add_load_label">
                                    <span style="font-size:22px;">S1</span> 0.0H <span style="color:#94a3b8; font-size:22px; margin:0 5px;">|</span> <span style="font-size:22px;">S2</span> 0.0H
                                </div>
                            </div>
                        </div>

                        <!-- KOLOM 3: Target Allocation -->
                        <div class="col-md-4 pl-4">
                            <h6 class="mrp-card-title" style="color: #10b981;"><i class="fas fa-crosshairs mr-2"></i> 03. QUANTITY_PLANNING</h6>
                            
                            <div class="d-flex w-100 mb-4" style="border: 2px solid #4f73d9; border-radius: 8px; overflow: hidden;">
                                <button type="button" id="add_tab_s1" class="btn w-50 font-weight-bold rounded-0" style="background:#4f73d9; color:white; border:none; font-size:12px;" onclick="switchAddTab(1)">SHIFT_1</button>
                                <button type="button" id="add_tab_s2" class="btn w-50 font-weight-bold rounded-0" style="background:white; color:#64748b; border:none; font-size:12px;" onclick="switchAddTab(2)">SHIFT_2</button>
                            </div>
                            
                            <div class="p-3 bg-light border rounded" style="border-radius:12px;">
                                <!-- Tab 1 -->
                                <div id="add_box_s1">
                                    <label class="mrp-label text-primary">S1 Reguler Plan</label>
                                    <input type="number" name="s1_plan_reg" id="add_s1_reg" class="mrp-input font-mono add-calc mb-3" value="0" required>
                                    <label class="mrp-label text-danger">S1 Overtime Plan</label>
                                    <input type="number" name="s1_plan_ot" id="add_s1_ot" class="mrp-input font-mono add-calc" value="0" required>
                                </div>
                                
                                <!-- Tab 2 -->
                                <div id="add_box_s2" style="display:none;">
                                    <label class="mrp-label text-primary">S2 Reguler Plan</label>
                                    <input type="number" name="s2_plan_reg" id="add_s2_reg" class="mrp-input font-mono add-calc mb-3" value="0" required>
                                    <label class="mrp-label text-danger">S2 Overtime Plan</label>
                                    <input type="number" name="s2_plan_ot" id="add_s2_ot" class="mrp-input font-mono add-calc" value="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer d-flex flex-column align-items-center pb-4 pt-0" style="background:#f8fafc; border-top: 1px solid #e2e8f0;">
                    <!-- TEMPAT KOTAK PERINGATAN MUNCUL LANGSUNG SAAT MENGETIK -->
                    <div id="add_msg" class="w-100 px-3 mt-3 text-center"></div>
                    
                    <div class="d-flex justify-content-center gap-3 w-100 mt-2">
                        <button type="button" class="btn-abort" data-dismiss="modal">ABORT</button>
                        <button type="submit" id="add_btn_submit" class="btn-auth disabled" disabled><i class="fas fa-rocket mr-1"></i> AUTHORIZE & DEPLOY</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✨ 2. MODAL REVISI (DESAIN 3 KOLOM) ✨ --}}
@foreach($plans as $p)
    @if($p->total_actual == 0)
    <div class="modal fade animate__animated animate__zoomIn" id="modalEditPlan-{{ $p->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1100px;">
            <div class="modal-content mrp-modal-content">
                <div class="mrp-modal-header" style="background:#f59e0b;">
                    <h5 class="mrp-modal-title"><i class="fas fa-edit mr-2"></i> REVISE_PLAN_SYSTEM</h5>
                    <button type="button" class="close text-white ml-auto" data-dismiss="modal" style="opacity:1;"><span>&times;</span></button>
                </div>
                
                <form action="{{ route('ppic.welding.update_wos', $p->id) }}" method="POST">
                    @csrf @method('PUT') 
                    
                    <div class="modal-body p-4 p-md-5">
                        <div class="row">
                            <!-- BOX 1: Identifikasi -->
                            <div class="col-md-4 border-right pr-4">
                                <h6 class="mrp-card-title" style="color:#d97706;"><i class="fas fa-tag mr-2"></i>01. PRODUCT_IDENTITY</h6>
                                
                                <label class="mrp-label">Select Line</label>
                                <select name="line_code" class="mrp-input mb-4" required>
                                    @foreach($availableLines as $line)
                                        <option value="{{ $line->kode_line }}" {{ $p->line_code == $line->kode_line ? 'selected' : '' }}>{{ $line->kode_line }}</option>
                                    @endforeach
                                </select>
                                
                                <label class="mrp-label">Part No (Locked)</label>
                                <input type="text" class="mrp-input mb-0 font-mono" value="{{ $p->part_no }}" readonly style="background:#fef3c7; color:#b45309; border-color:transparent;">
                            </div>
                            
                            <!-- BOX 2: M/C Load -->
                            <div class="col-md-4 border-right px-4">
                                <h6 class="mrp-card-title" style="color:#d97706;"><i class="fas fa-chart-line mr-2"></i>02. PERFORMANCE_METRICS</h6>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="mrp-label">Manpower</label>
                                        <input type="number" name="manpower" class="mrp-input font-mono edit-calc-{{ $p->id }}" value="{{ $p->manpower }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="mrp-label">Process</label>
                                        <input type="number" name="process_qty" class="mrp-input font-mono" value="{{ $p->process_qty ?? 1 }}" required>
                                    </div>
                                    <div class="col-6 mt-3">
                                        <label class="mrp-label text-primary">Cap/Hour</label>
                                        <input type="number" name="cap_per_hour" id="edit_cap_{{ $p->id }}" class="mrp-input font-mono edit-calc-{{ $p->id }}" value="{{ $p->cap_per_hour }}" required>
                                    </div>
                                    <div class="col-6 mt-3">
                                        <label class="mrp-label text-warning">Dandory (m)</label>
                                        <input type="number" name="dandory_time" id="edit_dandory_{{ $p->id }}" class="mrp-input font-mono edit-calc-{{ $p->id }}" value="{{ $p->dandory_time ?? 15 }}">
                                    </div>
                                </div>

                                <div class="mc-load-box mt-auto" style="background:#fffbeb; border-color:#fde68a;">
                                    <label class="mc-load-label" style="color:#b45309;">CALCULATED M/C LOAD</label>
                                    
                                    <!-- HITUNG STOK MAKSIMAL DARI PHP -->
                                    @php
                                        $cleanPartRev = trim(urldecode($p->part_no));
                                        if (strpos($cleanPartRev, ' - ') !== false) $cleanPartRev = trim(explode(' - ', $cleanPartRev)[0]);
                                        
                                        $fgRev = \DB::table('finished_goods')->where('part_no', $cleanPartRev)->first();
                                        $wipRev = $fgRev ? (int)$fgRev->welding_stock : 0;
                                        
                                        $oldTgtS1 = $p->s1_plan_reg + $p->s1_plan_ot;
                                        $oldTgtS2 = $p->s2_plan_reg + $p->s2_plan_ot;
                                        $maxRevAvailable = $wipRev + $oldTgtS1 + $oldTgtS2; 
                                    @endphp
                                    <input type="hidden" id="edit_max_wip_{{ $p->id }}" value="{{ $maxRevAvailable }}">
                                    
                                    <div class="mc-load-val" id="edit_load_label_{{ $p->id }}" style="color:#d97706;">
                                        <span style="font-size:22px;">S1</span> 0.0H <span style="color:#fcd34d; font-size:22px; margin:0 5px;">|</span> <span style="font-size:22px;">S2</span> 0.0H
                                    </div>
                                </div>
                            </div>
                            
                            <!-- BOX 3: Target -->
                            <div class="col-md-4 pl-4">
                                <h6 class="mrp-card-title" style="color:#d97706;"><i class="fas fa-crosshairs mr-1"></i> 03. QUANTITY_PLANNING</h6>
                                
                                <div class="d-flex w-100 mb-4" style="border: 2px solid #f59e0b; border-radius: 8px; overflow: hidden;">
                                    <button type="button" id="edit_tab_btn_s1_{{ $p->id }}" class="btn w-50 font-weight-bold rounded-0" style="background:#f59e0b; color:white; border:none; font-size:12px;" onclick="switchEditTab('{{ $p->id }}', 1)">SHIFT_1</button>
                                    <button type="button" id="edit_tab_btn_s2_{{ $p->id }}" class="btn w-50 font-weight-bold rounded-0" style="background:white; color:#64748b; border:none; font-size:12px;" onclick="switchEditTab('{{ $p->id }}', 2)">SHIFT_2</button>
                                </div>
                                
                                <div class="p-3 bg-light border rounded" style="border-radius:12px;">
                                    <!-- Tab 1 Rev -->
                                    <div id="edit_box_s1_{{ $p->id }}">
                                        <label class="mrp-label text-primary">S1 Reguler Plan</label>
                                        <input type="number" name="s1_plan_reg" id="edit_s1_reg_{{ $p->id }}" class="mrp-input font-mono edit-calc-{{ $p->id }} mb-3" value="{{ $p->s1_plan_reg }}" required>
                                        <label class="mrp-label text-danger">S1 Overtime Plan</label>
                                        <input type="number" name="s1_plan_ot" id="edit_s1_ot_{{ $p->id }}" class="mrp-input font-mono edit-calc-{{ $p->id }}" value="{{ $p->s1_plan_ot }}" required>
                                    </div>
                                    
                                    <!-- Tab 2 Rev -->
                                    <div id="edit_box_s2_{{ $p->id }}" style="display:none;">
                                        <label class="mrp-label text-primary">S2 Reguler Plan</label>
                                        <input type="number" name="s2_plan_reg" id="edit_s2_reg_{{ $p->id }}" class="mrp-input font-mono edit-calc-{{ $p->id }} mb-3" value="{{ $p->s2_plan_reg }}" required>
                                        <label class="mrp-label text-danger">S2 Overtime Plan</label>
                                        <input type="number" name="s2_plan_ot" id="edit_s2_ot_{{ $p->id }}" class="mrp-input font-mono edit-calc-{{ $p->id }}" value="{{ $p->s2_plan_ot }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer bg-light border-0 p-4 flex-column">
                        <div id="edit_msg_{{ $p->id }}" class="w-100 px-3 text-center"></div>
                        
                        <div class="d-flex justify-content-center gap-3 w-100 mt-2">
                            <button type="button" class="btn-abort" data-dismiss="modal">ABORT</button>
                            <button type="submit" id="edit_btn_submit_{{ $p->id }}" class="btn-auth" style="background:#f59e0b;"><i class="fas fa-check mr-2"></i> AUTHORIZE REVISION</button>
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
    // FUNGSI TABS NEW PLAN
    function switchAddTab(s) {
        if(s === 1) {
            $('#add_tab_s1').css({background: '#4f73d9', color: 'white'});
            $('#add_tab_s2').css({background: 'white', color: '#64748b'});
            $('#add_box_s1').show();
            $('#add_box_s2').hide();
        } else {
            $('#add_tab_s2').css({background: '#4f73d9', color: 'white'});
            $('#add_tab_s1').css({background: 'white', color: '#64748b'});
            $('#add_box_s1').hide();
            $('#add_box_s2').show();
        }
    }

    // FUNGSI TABS REVISI
    function switchEditTab(id, s) {
        if(s === 1) {
            $('#edit_tab_btn_s1_' + id).css({background: '#f59e0b', color: 'white'});
            $('#edit_tab_btn_s2_' + id).css({background: 'white', color: '#64748b'});
            $('#edit_box_s1_' + id).show();
            $('#edit_box_s2_' + id).hide();
        } else {
            $('#edit_tab_btn_s2_' + id).css({background: '#f59e0b', color: 'white'});
            $('#edit_tab_btn_s1_' + id).css({background: 'white', color: '#64748b'});
            $('#edit_box_s1_' + id).hide();
            $('#edit_box_s2_' + id).show();
        }
    }

    // LOGIKA PERHITUNGAN NEW PLAN (100% PASTI JALAN)
    function calculateAdd() {
        let cap = parseFloat($('#add_cap').val()) || 1;
        let dandory = parseFloat($('#add_dandory').val()) || 0;
        
        let s1 = (parseInt($('#add_s1_reg').val()) || 0) + (parseInt($('#add_s1_ot').val()) || 0);
        let s2 = (parseInt($('#add_s2_reg').val()) || 0) + (parseInt($('#add_s2_ot').val()) || 0);
        let total = s1 + s2;
        
        let h1 = s1 > 0 ? (s1 / cap) + (dandory / 60) : 0;
        let h2 = s2 > 0 ? (s2 / cap) + (dandory / 60) : 0;
        
        $('#add_load_label').html(`<span style="font-size:22px;">S1</span> ${h1.toFixed(1)}H <span style="color:#94a3b8; font-size:22px; margin:0 5px;">|</span> <span style="font-size:22px;">S2</span> ${h2.toFixed(1)}H`);
        
        let partOpt = $('#add_part').find('option:selected');
        let wip = parseInt(partOpt.attr('data-wip')) || 0;
        let cust = partOpt.attr('data-cust') || 'AUTO DETECT';
        
        $('#add_cust').val(cust);
        
        let btn = $('#add_btn_submit');
        let msg = $('#add_msg');
        
        if (!$('#add_part').val()) {
            msg.html('<div class="alert alert-secondary py-2 border-0 shadow-sm" style="font-size:13px;"><i class="fas fa-info-circle mr-1"></i> Silakan pilih Part Number terlebih dahulu.</div>');
            btn.prop('disabled', true);
        } else if (total === 0) {
            msg.html('<div class="alert alert-secondary py-2 border-0 shadow-sm" style="font-size:13px;"><i class="fas fa-info-circle mr-1"></i> Silakan isi angka target produksi di salah satu shift.</div>');
            btn.prop('disabled', true);
        } else if (total > wip) {
            msg.html(`<div class="alert alert-danger py-2 border-0 shadow-sm animate__animated animate__headShake" style="font-size:13px; background:#fee2e2; color:#991b1b;"><i class="fas fa-times-circle mr-1"></i> <b>STOK WIP KURANG!</b> Butuh ${total} Pcs | Sisa di Gudang ${wip} Pcs</div>`);
            btn.prop('disabled', true);
        } else {
            msg.html(`<div class="alert alert-success py-2 border-0 shadow-sm" style="font-size:13px; background:#d1fae5; color:#065f46;"><i class="fas fa-check-circle mr-1"></i> <b>STOK WIP AMAN!</b> Butuh ${total} Pcs | Sisa di Gudang ${wip} Pcs</div>`);
            btn.prop('disabled', false);
        }
    }

    // LOGIKA PERHITUNGAN EDIT PLAN (100% PASTI JALAN)
    function calculateEdit(id) {
        let cap = parseFloat($('#edit_cap_' + id).val()) || 1;
        let dandory = parseFloat($('#edit_dandory_' + id).val()) || 0;
        
        let s1 = (parseInt($('#edit_s1_reg_' + id).val()) || 0) + (parseInt($('#edit_s1_ot_' + id).val()) || 0);
        let s2 = (parseInt($('#edit_s2_reg_' + id).val()) || 0) + (parseInt($('#edit_s2_ot_' + id).val()) || 0);
        let total = s1 + s2;
        
        let h1 = s1 > 0 ? (s1 / cap) + (dandory / 60) : 0;
        let h2 = s2 > 0 ? (s2 / cap) + (dandory / 60) : 0;
        
        $('#edit_load_label_' + id).html(`<span style="font-size:22px;">S1</span> ${h1.toFixed(1)}H <span style="color:#fcd34d; font-size:22px; margin:0 5px;">|</span> <span style="font-size:22px;">S2</span> ${h2.toFixed(1)}H`);
        
        let maxWip = parseInt($('#edit_max_wip_' + id).val()) || 0;
        
        let btn = $('#edit_btn_submit_' + id);
        let msg = $('#edit_msg_' + id);
        
        if (total === 0) {
            msg.html('<div class="alert alert-warning py-2 border-0 shadow-sm" style="font-size:13px; background:#fffbeb; color:#b45309;"><i class="fas fa-trash mr-1"></i> <b>JADWAL DIBATALKAN.</b> Seluruh material akan dikembalikan.</div>');
            btn.prop('disabled', false);
        } else if (total > maxWip) {
            msg.html(`<div class="alert alert-danger py-2 border-0 shadow-sm animate__animated animate__headShake" style="font-size:13px; background:#fee2e2; color:#991b1b;"><i class="fas fa-times-circle mr-1"></i> <b>STOK WIP KURANG!</b> Maksimal target revisi adalah ${maxWip} Pcs.</div>`);
            btn.prop('disabled', true);
        } else {
            msg.html(`<div class="alert alert-success py-2 border-0 shadow-sm" style="font-size:13px; background:#d1fae5; color:#065f46;"><i class="fas fa-check-circle mr-1"></i> <b>STOK WIP AMAN!</b> Sisa WIP di gudang: ${maxWip - total} Pcs.</div>`);
            btn.prop('disabled', false);
        }
    }

    $(document).ready(function() {
        // Init Select2
        $('.select2-part').select2({
            dropdownParent: $('#modalRegisterPlan'),
            placeholder: "-- PILIH PART NUMBER --",
            allowClear: true,
            width: '100%'
        });

        // Trigger Logika New Plan
        $('.add-calc').on('input change', calculateAdd);

        // Trigger Logika Revisi Plan Pake PHP Loop
        @foreach($plans as $p)
            @if($p->total_actual == 0)
                $('.edit-calc-{{ $p->id }}').on('input change', function() {
                    calculateEdit('{{ $p->id }}');
                });
                // Paksa ngitung waktu modal revisi pertama dibuka
                $('#modalEditPlan-{{ $p->id }}').on('shown.bs.modal', function () {
                    calculateEdit('{{ $p->id }}');
                });
            @endif
        @endforeach
    });
</script>
@endsection