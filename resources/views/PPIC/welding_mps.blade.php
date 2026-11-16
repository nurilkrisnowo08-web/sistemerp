@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    :root { 
        --app-bg: #f4f7fe; --card-bg: #ffffff; --text-main: #1e293b;
        --primary: #4318FF; --success: #05CD99; --danger: #EE5D50; --warning: #FFCE20;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--app-bg); color: var(--text-main); }
    
    /* MODEL GELEMBUNG (BUBBLE UI) */
    .bubble-panel { background: var(--card-bg); border-radius: 30px; padding: 25px; border: 1px solid rgba(226, 232, 240, 0.8); box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 25px; transition: 0.3s; }
    .bubble-panel:hover { box-shadow: 0 15px 35px rgba(67, 24, 255, 0.05); transform: translateY(-3px); }
    
    /* Header Controls */
    .mps-header-box { background: linear-gradient(145deg, #ffffff, #fdfaf6); border-radius: 50rem; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; box-shadow: 0 8px 20px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; flex-wrap: wrap; gap: 15px; }
    .mps-title h2 { font-weight: 900; font-size: 22px; color: #1e293b; margin: 0; letter-spacing: -0.5px; }
    .mps-title p { font-size: 11px; font-weight: 700; color: #64748b; margin: 2px 0 0 0; display: flex; align-items: center; gap: 10px; }
    
    .filter-group { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .shift-toggle { background: #f1f5f9; border-radius: 50rem; display: flex; overflow: hidden; padding: 4px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); }
    .shift-btn { padding: 8px 20px; font-size: 11px; font-weight: 900; color: #64748b; border-radius: 50rem; cursor: pointer; text-decoration: none; transition: 0.3s; }
    .shift-btn.active { background: #1e293b; color: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    
    .date-picker-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 50rem; padding: 8px 20px; display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 12px; color: #1e293b; transition: 0.3s; }
    .date-picker-box:hover { border-color: #cbd5e1; background: #fff; }
    .date-picker-box input { border: none; outline: none; background: transparent; font-weight: 800; color: #1e293b; cursor: pointer; font-family: 'JetBrains Mono', monospace; }

    /* Summary Bubbles */
    .summary-card { background: white; border: none; border-radius: 30px; padding: 20px 25px; flex: 1; display: flex; flex-direction: column; justify-content: center; position: relative; overflow: hidden; min-width: 160px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); transition: 0.3s; }
    .summary-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(67, 24, 255, 0.08); }
    .summary-card h6 { font-size: 10px; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin: 0 0 8px 0; letter-spacing: 0.5px; }
    .summary-card h3 { font-size: 28px; font-weight: 900; color: #4318FF; margin: 0; }
    
    .summary-card.bg-dark-slate { background: linear-gradient(135deg, #1e293b, #334155); color: white; }
    .summary-card.bg-dark-slate h6 { color: #94a3b8; }
    .summary-card.bg-dark-slate h3 { color: #fff; }

    /* Table Station Bubbles */
    .station-panel { background: white; border-radius: 24px; border: 1px solid #f1f5f9; overflow: hidden; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
    .station-header { background: #f8fafc; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; flex-wrap: wrap; gap: 10px; }
    .station-title { font-weight: 900; font-size: 15px; margin: 0; display: flex; align-items: center; gap: 10px; color: #1e293b; }
    
    .trace-table { width: 100%; border-collapse: collapse; text-align: center; }
    .trace-table th { font-size: 10px; font-weight: 900; color: #64748b; padding: 15px; border-bottom: 2px solid #f1f5f9; text-transform: uppercase; letter-spacing: 0.5px; }
    .trace-table td { padding: 15px; font-size: 12px; font-weight: 700; color: #334155; border-bottom: 1px solid #f8fafc; vertical-align: middle; }
    .trace-table tr:hover td { background-color: #fcfdfd; }
    .text-num { font-family: 'JetBrains Mono', monospace; font-weight: 900; font-size: 13px; }
    
    /* Tombol Pill/Gelembung */
    .btn-pill { border-radius: 50rem; padding: 8px 24px; font-weight: 900; font-size: 11px; border: none; transition: 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; }
    
    .btn-new-plan { background: linear-gradient(135deg, #4318FF, #5c67f2); color: white; box-shadow: 0 6px 15px rgba(67, 24, 255, 0.3); }
    .btn-new-plan:hover { box-shadow: 0 8px 20px rgba(67, 24, 255, 0.4); transform: translateY(-2px); color:white; text-decoration: none; }
    
    .btn-print-report { background: linear-gradient(135deg, #05CD99, #04b083); color: white; box-shadow: 0 6px 15px rgba(5, 205, 153, 0.3); }
    .btn-print-report:hover { transform: translateY(-2px); color:white; text-decoration: none;}

    .btn-print-wos { background: #e0e7ff; color: #4318FF; border-radius: 50rem; padding: 6px 16px; font-size: 10px; font-weight: 900; transition: 0.2s; text-decoration: none; }
    .btn-print-wos:hover { background: #4318FF; color: white; text-decoration: none; }
    
    .btn-print-bprm { background: #fef3c7; color: #d97706; border-radius: 50rem; padding: 6px 16px; font-size: 10px; font-weight: 900; transition: 0.2s; text-decoration: none; }
    .btn-print-bprm:hover { background: #f59e0b; color: white; text-decoration: none; }
    
    .btn-revisi { background: #fffbeb; color: #f59e0b; border: 1px solid #fde68a; border-radius: 50rem; padding: 6px 16px; font-size: 10px; font-weight: 900; transition: 0.2s; cursor: pointer; }
    .btn-revisi:hover { background: #f59e0b; color: white; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.2); }
    
    .val-plus { color: #059669; }
    .val-minus { color: #ef4444; background: #fef2f2; padding: 4px 8px; border-radius: 8px; }

    /* Modal Gelembung */
    .modal-content { border-radius: 30px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
    .modal-header { border-bottom: none; padding: 25px 30px 10px 30px; }
    .modal-footer { border-top: none; padding: 10px 30px 25px 30px; background: white; }
    .form-control { border-radius: 15px; border: 2px solid #f1f5f9; padding: 12px 18px; font-weight: 700; color: #1e293b; background: #f8fafc; transition: 0.3s; }
    .form-control:focus { border-color: #4318FF; background: #fff; box-shadow: 0 0 0 4px rgba(67, 24, 255, 0.1); }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">

    {{-- ALERT PESAN ERROR/SUCCESS --}}
    @if(session('error'))
    <div class="alert alert-danger shadow-sm mb-4 animate__animated animate__shakeX" style="border-radius: 20px; font-weight:800; border-left: 6px solid #ef4444; background:#fef2f2; color:#b91c1c;">
        <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
    </div>
    @endif
    @if(session('success'))
    <div class="alert alert-success shadow-sm mb-4 animate__animated animate__bounceIn" style="border-radius: 20px; font-weight:800; border-left: 6px solid #05CD99; background:#ecfdf5; color:#047857;">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
    @endif

    {{-- HEADER & FILTER GELEMBUNG --}}
    <div class="mps-header-box">
        <div class="mps-title">
            <h2>MPS_WELDING <span style="color: #4318FF;">ENGINE</span></h2>
            <p>
                <span class="badge badge-dark px-2 py-1" style="background:#475569; border-radius:8px;">SYSTEM_ACTIVE</span>
                <i class="far fa-calendar-alt ml-2"></i> {{ date('D, d M Y', strtotime($date)) }}
            </p>
        </div>
        
        <form action="{{ route('ppic.welding.mps') }}" method="GET" class="filter-group">
            <div class="shift-toggle">
                <a href="{{ route('ppic.welding.mps', ['date' => $date, 'shift' => 'S1']) }}" class="shift-btn {{ $shift == 'S1' ? 'active' : '' }}">SHIFT_01</a>
                <a href="{{ route('ppic.welding.mps', ['date' => $date, 'shift' => 'S2']) }}" class="shift-btn {{ $shift == 'S2' ? 'active' : '' }}">SHIFT_02</a>
                <input type="hidden" name="shift" value="{{ $shift }}">
            </div>
            
            <div class="date-picker-box">
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()">
            </div>
            
            {{-- TOMBOL CREATE NEW PLAN --}}
            <button type="button" class="btn-pill btn-new-plan" data-toggle="modal" data-target="#newPlanModal">
                <i class="fas fa-plus"></i> NEW_PLAN
            </button>
            
            {{-- TOMBOL PRINT REPORT HARIAN WELDING --}}
            <a href="{{ route('ppic.welding.print_report', ['date' => $date]) }}" target="_blank" class="btn-pill btn-print-report">
                <i class="fas fa-print"></i> PRINT REPORT
            </a>
        </form>
    </div>

    {{-- SUMMARY CARDS GELEMBUNG --}}
    <div class="d-flex flex-wrap gap-3 mb-4">
        <div class="summary-card">
            <h6>VOLUME TARGET ({{ $shift }})</h6>
            <h3><span class="text-num">{{ number_format($totalPlanQty) }}</span> <span style="font-size:12px; color:#64748b;">PCS</span></h3>
        </div>
        <div class="summary-card">
            <h6>ESTIMASI WAKTU KERJA</h6>
            <h3 style="color:#0f172a;"><span class="text-num">{{ number_format($totalWorkingHours, 1) }}</span> <span style="font-size:12px; color:#64748b;">JAM</span></h3>
        </div>
        <div class="summary-card">
            <h6>TOTAL DANDORY ({{ $shift }})</h6>
            <h3 style="color:#f59e0b;"><span class="text-num">{{ number_format($totalDandory) }}</span> <span style="font-size:12px; color:#64748b;">MENIT</span></h3>
        </div>
        <div class="summary-card bg-dark-slate">
            <h6 style="color:#94a3b8;">OPERATIONAL SHIFT</h6>
            <h3>{{ $shift == 'S1' ? 'DAY_OPS' : 'NIGHT_OPS' }}</h3>
        </div>
    </div>

    {{-- LOOPING GROUP BY LINE / STATION --}}
    @forelse($plans->groupBy('line_code') as $lineCode => $linePlans)
    <div class="station-panel animate__animated animate__fadeInUp">
        <div class="station-header">
            <h5 class="station-title">
                <div style="background:#e0e7ff; color:#4318FF; width:35px; height:35px; border-radius:12px; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-robot"></i>
                </div>
                STATION: {{ $lineCode }}
            </h5>
            <div class="d-flex gap-2">
                <a href="{{ route('ppic.welding.print_wos', ['date' => $date, 'line_code' => $lineCode]) }}" target="_blank" class="btn-print-wos"><i class="fas fa-print"></i> WOS</a>
                <a href="{{ route('ppic.welding.print_bprm', ['date' => $date, 'line_code' => $lineCode]) }}" target="_blank" class="btn-print-bprm"><i class="fas fa-file-alt"></i> BPRM (WIP)</a>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="trace-table">
                <thead>
                    <tr>
                        <th width="5%">POS</th>
                        <th class="text-left" width="25%">PART IDENTIFICATION</th>
                        <th>TARGET {{ $shift == 'S1' ? '1P' : '2P' }}</th>
                        <th>ACTUAL</th>
                        <th>BALANCE</th>
                        <th>T-START</th>
                        <th>T-FINISH</th>
                        <th>STATUS</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($linePlans as $idx => $p)
                    <tr>
                        <td class="text-num" style="color:#94a3b8;">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="text-left">
                            <b style="font-size:13px; color:#1e293b;">{{ $p->part_name }}</b><br>
                            <span style="font-size:10px; color:#64748b; letter-spacing:0.5px; font-family: 'JetBrains Mono', monospace;">{{ $p->part_no }}</span>
                        </td>
                        
                        <td class="text-num" style="color:#1e293b;">{{ number_format($p->total_target) }}</td>
                        <td class="text-num val-plus">{{ number_format($p->total_actual) }}</td>
                        
                        <td class="text-num">
                            <span class="{{ $p->balance < 0 ? 'val-minus' : 'text-muted' }}">
                                {{ $p->balance < 0 ? $p->balance : '-'.$p->balance }}
                            </span>
                        </td>
                        
                        <td><span class="badge" style="background:#f1f5f9; color:#475569; font-size:11px; font-weight:900; padding:6px 10px; border-radius:10px;">{{ $p->start_time }}</span></td>
                        <td><span class="badge" style="background:#e0e7ff; color:#4318FF; font-size:11px; font-weight:900; padding:6px 10px; border-radius:10px;">{{ $p->ahir_time }}</span></td>
                        
                        <td><i class="far fa-clock text-muted" style="font-size:16px;"></i></td>
                        
                        <td>
                            {{-- TOMBOL BUKA MODAL REVISI --}}
                            <button type="button" class="btn-revisi" data-toggle="modal" data-target="#revisiModal-{{ $p->id }}">
                                <i class="fas fa-edit"></i> REVISI
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="bubble-panel text-center py-5">
        <div style="background:#f1f5f9; width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin: 0 auto 15px auto;">
            <i class="fas fa-clipboard-list text-muted" style="font-size:35px; opacity:0.5;"></i>
        </div>
        <h5 style="font-weight:900; color:#475569;">BELUM ADA PLAN WELDING</h5>
        <p style="font-size:12px; color:#94a3b8; margin:0;">Klik tombol <b style="color:#4318FF;">+ NEW_PLAN</b> untuk mendeploy jadwal baru.</p>
    </div>
    @endforelse

</div>


{{-- ========================================================================= --}}
{{-- ⚠️ EVAKUASI MODAL: SEMUA MODAL DILETAKKAN DI SINI, DI LUAR TABEL/CONTAINER ⚠️ --}}
{{-- ========================================================================= --}}

{{-- ✨ MODAL REVISI (DILOOP ULANG AGAR AMAN DARI BUG UI TABEL) ✨ --}}
@foreach($plans as $p)
<div class="modal fade" id="revisiModal-{{ $p->id }}" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 9999 !important;">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-weight:900; font-size:18px; color:#1e293b;">
                    <div style="display:inline-block; background:#fffbeb; color:#f59e0b; width:35px; height:35px; text-align:center; line-height:35px; border-radius:10px; margin-right:10px;">
                        <i class="fas fa-edit"></i>
                    </div>
                    REVISI TARGET WELDING
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="background:#f1f5f9; width:35px; height:35px; border-radius:50%; opacity:1; display:flex; align-items:center; justify-content:center;">
                    <span aria-hidden="true" style="color:#475569; font-weight:bold;">&times;</span>
                </button>
            </div>
            
            <form action="{{ route('ppic.welding.update_wos', $p->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="modal-body px-4 py-3">
                    <div class="alert mb-4" style="background:#fffbeb; border:1px solid #fde68a; border-radius:16px; font-size:12px; font-weight:700; color:#b45309;">
                        <i class="fas fa-info-circle mr-1"></i> Part: <b style="font-family:'JetBrains Mono';">{{ $p->part_no }}</b><br>
                        Sisa stok WIP Gudang akan divalidasi dan disesuaikan otomatis!
                    </div>

                    <div class="row">
                        @if($shift == 'S1')
                        <div class="col-6 form-group">
                            <label style="font-size:11px; font-weight:900; color:#64748b; margin-left:10px;">Target S1 (Reguler)</label>
                            <input type="number" class="form-control text-num" name="s1_plan_reg" value="{{ $p->s1_plan_reg }}" required>
                        </div>
                        <div class="col-6 form-group">
                            <label style="font-size:11px; font-weight:900; color:#64748b; margin-left:10px;">Target S1 (Lembur)</label>
                            <input type="number" class="form-control text-num" name="s1_plan_ot" value="{{ $p->s1_plan_ot }}" required>
                        </div>
                        @else
                        <div class="col-6 form-group">
                            <label style="font-size:11px; font-weight:900; color:#64748b; margin-left:10px;">Target S2 (Reguler)</label>
                            <input type="number" class="form-control text-num" name="s2_plan_reg" value="{{ $p->s2_plan_reg }}" required>
                        </div>
                        <div class="col-6 form-group">
                            <label style="font-size:11px; font-weight:900; color:#64748b; margin-left:10px;">Target S2 (Lembur)</label>
                            <input type="number" class="form-control text-num" name="s2_plan_ot" value="{{ $p->s2_plan_ot }}" required>
                        </div>
                        @endif
                    </div>
                    
                    <div class="form-group mt-2">
                        <label style="font-size:11px; font-weight:900; color:#64748b; margin-left:10px;">Kapasitas / Jam (Pcs)</label>
                        <input type="number" class="form-control text-num" name="cap_per_hour" value="{{ $p->cap_per_hour }}" required>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-light" data-dismiss="modal" style="font-weight:900; font-size:12px; border-radius:50rem; padding:10px 24px; background:#f1f5f9; color:#475569;">BATAL</button>
                    <button type="submit" class="btn btn-warning" style="font-weight:900; font-size:12px; border-radius:50rem; padding:10px 24px; background:#f59e0b; color:white; border:none; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);">
                        <i class="fas fa-save mr-1"></i> UPDATE TARGET
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- ✨ MODAL NEW PLAN WELDING ✨ --}}
<div class="modal fade" id="newPlanModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 9999 !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#f8fafc; border-bottom:1px solid #f1f5f9;">
                <h5 class="modal-title" style="font-weight:900; font-size:18px; color:#1e293b;">
                    <div style="display:inline-block; background:#e0e7ff; color:#4318FF; width:35px; height:35px; text-align:center; line-height:35px; border-radius:10px; margin-right:10px;">
                        <i class="fas fa-plus"></i>
                    </div>
                    DEPLOY NEW WELDING PLAN
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="background:#f1f5f9; width:35px; height:35px; border-radius:50%; opacity:1; display:flex; align-items:center; justify-content:center;">
                    <span aria-hidden="true" style="color:#475569; font-weight:bold;">&times;</span>
                </button>
            </div>
            
            <form action="{{ route('ppic.welding.mps_store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-4">
                    
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label style="font-size:11px; font-weight:900; color:#64748b; margin-left:10px;">TANGGAL PRODUKSI</label>
                            <input type="date" class="form-control" name="plan_date" value="{{ $date }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label style="font-size:11px; font-weight:900; color:#64748b; margin-left:10px;">STATION / MESIN LAS</label>
                            <select class="form-control" name="line_code" required>
                                <option value="">Pilih Station...</option>
                                @foreach($availableLines as $l)
                                    <option value="{{ $l->kode_line }}">{{ $l->kode_line }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label style="font-size:11px; font-weight:900; color:#64748b; margin-left:10px;">CUSTOMER</label>
                            <input type="text" class="form-control" id="custDisp" value="AUTO DETECT" readonly style="background:#f1f5f9; color:#94a3b8;">
                        </div>
                    </div>

                    <div class="form-group mt-2">
                        <label style="font-size:11px; font-weight:900; color:#64748b; margin-left:10px;">PART NUMBER (WIP) <span class="text-danger">*</span></label>
                        <select class="form-control select2-part" name="part_no" id="partSelector" required style="width:100%;">
                            <option value="">Ketik Part No / Nama Part...</option>
                            @foreach($availableParts as $p)
                                <option value="{{ $p->part_no }}">{{ $p->part_no }} - {{ $p->part_name }} ({{ $p->customer_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- INDIKATOR STOK WIP GUDANG (AJAX) BUBBLE --}}
                    <div class="alert mt-4 mb-4 d-flex align-items-center gap-3" style="background:#ffffff; border:1px solid #f1f5f9; border-radius:24px; box-shadow:0 10px 20px rgba(0,0,0,0.02); padding:20px;">
                        <div style="background:linear-gradient(135deg, #e0e7ff, #f8fafc); color:#4318FF; width:50px; height:50px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:20px; box-shadow: 0 4px 10px rgba(67, 24, 255, 0.1);">
                            <i class="fas fa-pallet"></i>
                        </div>
                        <div>
                            <h6 style="font-size:10px; font-weight:900; color:#64748b; margin:0 0 6px 0; letter-spacing:0.5px;">SISA STOK WIP (HASIL STAMPING) DI GUDANG</h6>
                            <h3 style="margin:0; font-weight:900; font-family:'JetBrains Mono'; color:#0f172a;" id="displayWipStock">
                                0 <span style="font-size:13px; font-weight:800; color:#94a3b8;">PCS</span>
                            </h3>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="p-4 mb-3" style="background:#ffffff; border:1px solid #f1f5f9; border-radius:24px; box-shadow:0 5px 15px rgba(0,0,0,0.01);">
                                <h6 style="font-size:12px; font-weight:900; color:#1e293b; border-bottom:2px dashed #f1f5f9; padding-bottom:12px; margin-bottom:15px;"><i class="fas fa-sun text-warning mr-2"></i>TARGET SHIFT 1 (DAY)</h6>
                                <div class="row">
                                    <div class="col-6 form-group mb-0">
                                        <label style="font-size:10px; font-weight:800; color:#64748b; margin-left:10px;">Reguler (Pcs)</label>
                                        <input type="number" class="form-control text-num" name="s1_plan_reg" value="0">
                                    </div>
                                    <div class="col-6 form-group mb-0">
                                        <label style="font-size:10px; font-weight:800; color:#64748b; margin-left:10px;">Lembur (Pcs)</label>
                                        <input type="number" class="form-control text-num" name="s1_plan_ot" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 mb-3" style="background:#ffffff; border:1px solid #f1f5f9; border-radius:24px; box-shadow:0 5px 15px rgba(0,0,0,0.01);">
                                <h6 style="font-size:12px; font-weight:900; color:#1e293b; border-bottom:2px dashed #f1f5f9; padding-bottom:12px; margin-bottom:15px;"><i class="fas fa-moon text-primary mr-2"></i>TARGET SHIFT 2 (NIGHT)</h6>
                                <div class="row">
                                    <div class="col-6 form-group mb-0">
                                        <label style="font-size:10px; font-weight:800; color:#64748b; margin-left:10px;">Reguler (Pcs)</label>
                                        <input type="number" class="form-control text-num" name="s2_plan_reg" value="0">
                                    </div>
                                    <div class="col-6 form-group mb-0">
                                        <label style="font-size:10px; font-weight:800; color:#64748b; margin-left:10px;">Lembur (Pcs)</label>
                                        <input type="number" class="form-control text-num" name="s2_plan_ot" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-0">
                            <label style="font-size:11px; font-weight:900; color:#64748b; margin-left:10px;">Kapasitas / Jam (Pcs)</label>
                            <input type="number" class="form-control text-num" name="cap_per_hour" value="300" required>
                        </div>
                        <div class="col-md-6 form-group mb-0">
                            <label style="font-size:11px; font-weight:900; color:#64748b; margin-left:10px;">Waktu Dandory (Menit)</label>
                            <input type="number" class="form-control text-num" name="dandory_time" value="15" required>
                        </div>
                    </div>

                </div>
                <div class="modal-footer d-flex justify-content-between" style="background:#f8fafc; border-top:1px solid #f1f5f9;">
                    <button type="button" class="btn btn-light" data-dismiss="modal" style="font-weight:900; font-size:12px; border-radius:50rem; padding:10px 24px; color:#475569;">BATAL</button>
                    <button type="submit" class="btn btn-primary" style="font-weight:900; font-size:12px; border-radius:50rem; padding:10px 30px; background:linear-gradient(135deg, #4318FF, #5c67f2); border:none; box-shadow: 0 6px 20px rgba(67,24,255,0.4);">
                        🚀 DEPLOY PLAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Init Select2 untuk Part Number
        $('.select2-part').select2({
            dropdownParent: $('#newPlanModal'),
            placeholder: "Cari Part Number...",
            allowClear: true,
            width: '100%'
        });

        // Tampilkan error Session SweetAlert
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'PERHATIAN!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#4318FF',
                customClass: { popup: 'bubble-panel' }
            });
        @endif

        // AJAX Check Sisa Stok WIP Gudang saat Part Dipilih
        $('#partSelector').on('change', function() {
            let partNo = $(this).val();
            let custMatch = $(this).find('option:selected').text().match(/\(([^)]+)\)$/);
            
            if(custMatch) { $('#custDisp').val(custMatch[1]); }
            else { $('#custDisp').val('-'); }

            if(partNo) {
                $('#displayWipStock').html('<i class="fas fa-spinner fa-spin" style="color:#94a3b8;"></i>');
                $.ajax({
                    url: "{{ url('/ppic/welding/ajax/check-wip') }}/" + encodeURIComponent(partNo),
                    type: "GET",
                    success: function(res) {
                        if(res.status === 'success') {
                            let stockStr = res.wip_stock.toLocaleString('en-US');
                            let color = res.wip_stock <= 0 ? '#ef4444' : '#05CD99';
                            $('#displayWipStock').html('<span style="color:'+color+';">' + stockStr + '</span> <span style="font-size:13px; font-weight:800; color:#94a3b8;">PCS</span>');
                        } else {
                            $('#displayWipStock').html('<span class="text-danger">ERROR</span>');
                        }
                    },
                    error: function() {
                        $('#displayWipStock').html('<span class="text-danger">GAGAL</span>');
                    }
                });
            } else {
                $('#displayWipStock').html('0 <span style="font-size:13px; font-weight:800; color:#94a3b8;">PCS</span>');
                $('#custDisp').val('AUTO DETECT');
            }
        });
    });
</script>
@endsection