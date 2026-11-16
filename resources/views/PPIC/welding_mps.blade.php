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
    
    .b-panel { background: var(--card-bg); border-radius: 16px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 20px; }
    
    /* Header & Filter Controls */
    .mps-header-box { background: #fdfaf6; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); flex-wrap: wrap; gap: 15px; }
    .mps-title h2 { font-weight: 900; font-size: 24px; color: #1e293b; margin: 0; letter-spacing: -0.5px; }
    .mps-title p { font-size: 11px; font-weight: 700; color: #64748b; margin: 5px 0 0 0; display: flex; align-items: center; gap: 10px; }
    
    .filter-group { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .shift-toggle { background: #e2e8f0; border-radius: 50rem; display: flex; overflow: hidden; padding: 3px; }
    .shift-btn { padding: 6px 16px; font-size: 11px; font-weight: 800; color: #64748b; border-radius: 50rem; cursor: pointer; text-decoration: none; transition: 0.3s; }
    .shift-btn.active { background: #1e293b; color: #fff; }
    
    .date-picker-box { background: white; border: 1px solid #cbd5e1; border-radius: 50rem; padding: 6px 16px; display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 12px; color: #1e293b; }
    .date-picker-box input { border: none; outline: none; background: transparent; font-weight: 700; color: #1e293b; cursor: pointer; }

    /* Summary Cards */
    .summary-card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px 20px; flex: 1; display: flex; flex-direction: column; justify-content: center; position: relative; overflow: hidden; min-width: 150px; }
    .summary-card h6 { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; margin: 0 0 5px 0; letter-spacing: 0.5px; }
    .summary-card h3 { font-size: 24px; font-weight: 900; color: #4318FF; margin: 0; }
    .summary-card.bg-dark-slate { background: #475569; border-color: #475569; }
    .summary-card.bg-dark-slate h6 { color: #cbd5e1; }
    .summary-card.bg-dark-slate h3 { color: #fff; }

    /* Table Station */
    .station-panel { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; margin-bottom: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
    .station-header { background: #475569; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; color: white; flex-wrap: wrap; gap: 10px; }
    .station-title { font-weight: 800; font-size: 14px; margin: 0; display: flex; align-items: center; gap: 10px; letter-spacing: 0.5px; }
    
    .trace-table { width: 100%; border-collapse: collapse; text-align: center; }
    .trace-table th { background: #f8fafc; font-size: 10px; font-weight: 900; color: #64748b; padding: 12px; border-bottom: 2px solid #e2e8f0; text-transform: uppercase; }
    .trace-table td { padding: 12px; font-size: 12px; font-weight: 700; color: #1e293b; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .text-num { font-family: 'JetBrains Mono', monospace; font-weight: bold; }
    
    /* Tombol & Badges */
    .btn-new-plan { background: #5c67f2; color: white; border-radius: 50rem; padding: 8px 20px; font-weight: 800; font-size: 12px; border: none; box-shadow: 0 4px 10px rgba(92, 103, 242, 0.3); transition: 0.3s; text-decoration: none; }
    .btn-new-plan:hover { background: #434ce6; color: white; transform: translateY(-2px); }
    
    .btn-print-wos { background: transparent; color: white; border: 1px solid rgba(255,255,255,0.4); border-radius: 50rem; padding: 4px 12px; font-size: 10px; font-weight: 800; transition: 0.2s; text-decoration: none; }
    .btn-print-wos:hover { background: rgba(255,255,255,0.1); color: white; text-decoration: none; }
    
    .btn-print-bprm { background: #5c67f2; color: white; border: none; border-radius: 50rem; padding: 4px 12px; font-size: 10px; font-weight: 800; box-shadow: 0 2px 5px rgba(0,0,0,0.2); text-decoration: none; }
    .btn-print-bprm:hover { background: #434ce6; color: white; text-decoration: none; }
    
    .btn-revisi { background: #f59e0b; color: white; border: none; border-radius: 50rem; padding: 6px 14px; font-size: 10px; font-weight: 800; box-shadow: 0 2px 5px rgba(245, 158, 11, 0.2); cursor: pointer; }
    
    .val-plus { color: #059669; }
    .val-minus { color: #dc2626; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">

    {{-- ALERT PESAN ERROR/SUCCESS DARI CONTROLLER --}}
    @if(session('error'))
    <div class="alert alert-danger shadow-sm mb-4" style="border-radius: 12px; font-weight:700; border-left: 5px solid #b91c1c;">
        <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
    </div>
    @endif
    @if(session('success'))
    <div class="alert alert-success shadow-sm mb-4" style="border-radius: 12px; font-weight:700; border-left: 5px solid #059669;">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
    @endif

    {{-- HEADER & FILTER --}}
    <div class="mps-header-box">
        <div class="mps-title">
            <h2>MPS_WELDING <span style="color: #5c67f2;">ENGINE</span></h2>
            <p>
                <span class="badge badge-dark px-2 py-1" style="background:#475569;">SYSTEM_ACTIVE</span>
                <i class="far fa-calendar-alt"></i> {{ date('D, d M Y', strtotime($date)) }}
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
            <button type="button" class="btn-new-plan" data-toggle="modal" data-target="#newPlanModal">
                <i class="fas fa-plus mr-1"></i> NEW_PLAN
            </button>
            
            {{-- TOMBOL PRINT REPORT HARIAN WELDING --}}
            <a href="{{ route('ppic.welding.print_report', ['date' => $date]) }}" target="_blank" class="btn btn-success shadow-sm" style="border-radius: 50rem; font-weight: 800; font-size: 12px; padding: 8px 20px;">
                <i class="fas fa-print mr-1"></i> PRINT REPORT
            </a>
        </form>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="d-flex flex-wrap gap-3 mb-4">
        <div class="summary-card shadow-sm">
            <h6>VOLUME TARGET ({{ $shift }})</h6>
            <h3><span class="text-num">{{ number_format($totalPlanQty) }}</span> <span style="font-size:12px; color:#64748b;">PCS</span></h3>
        </div>
        <div class="summary-card shadow-sm">
            <h6>ESTIMASI WAKTU KERJA ({{ $shift }})</h6>
            <h3 style="color:#0f172a;"><span class="text-num">{{ number_format($totalWorkingHours, 1) }}</span> <span style="font-size:12px; color:#64748b;">JAM</span></h3>
        </div>
        <div class="summary-card shadow-sm">
            <h6>TOTAL DANDORY ({{ $shift }})</h6>
            <h3 style="color:#f59e0b;"><span class="text-num">{{ number_format($totalDandory) }}</span> <span style="font-size:12px; color:#64748b;">MENIT</span></h3>
        </div>
        <div class="summary-card bg-dark-slate shadow-sm">
            <h6>OPERATIONAL SHIFT</h6>
            <h3>{{ $shift == 'S1' ? 'DAY_OPS' : 'NIGHT_OPS' }}</h3>
        </div>
    </div>

    {{-- LOOPING GROUP BY LINE / STATION --}}
    @forelse($plans->groupBy('line_code') as $lineCode => $linePlans)
    <div class="station-panel animate__animated animate__fadeInUp">
        <div class="station-header">
            <h5 class="station-title"><i class="fas fa-robot text-info"></i> STATION: {{ $lineCode }}</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('ppic.welding.print_wos', ['date' => $date, 'line_code' => $lineCode]) }}" target="_blank" class="btn-print-wos"><i class="fas fa-print mr-1"></i> PRINT WOS</a>
                <a href="{{ route('ppic.welding.print_bprm', ['date' => $date, 'line_code' => $lineCode]) }}" target="_blank" class="btn-print-bprm"><i class="fas fa-file-alt mr-1"></i> PRINT BPRM (WIP)</a>
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
                        <td class="text-num" style="color:#64748b;">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="text-left">
                            <b style="font-size:12px; color:#1e293b;">{{ $p->part_name }}</b><br>
                            <span style="font-size:9px; color:#64748b; letter-spacing:0.5px;">{{ $p->part_no }}</span>
                        </td>
                        
                        <td class="text-num">{{ number_format($p->total_target) }}</td>
                        <td class="text-num val-plus">{{ number_format($p->total_actual) }}</td>
                        
                        <td class="text-num {{ $p->balance < 0 ? 'val-minus' : 'text-muted' }}">
                            {{ $p->balance < 0 ? $p->balance : '-'.$p->balance }}
                        </td>
                        
                        <td><span class="badge badge-light px-2 py-1" style="font-size:11px; font-weight:800; border:1px solid #cbd5e1;">{{ $p->start_time }}</span></td>
                        <td><span class="badge px-2 py-1" style="background:#e0e7ff; color:#4318FF; font-size:11px; font-weight:800;">{{ $p->ahir_time }}</span></td>
                        
                        <td><i class="far fa-clock text-muted"></i></td>
                        
                        <td>
                            {{-- TOMBOL BUKA MODAL REVISI --}}
                            <button type="button" class="btn-revisi" data-toggle="modal" data-target="#revisiModal-{{ $p->id }}">
                                <i class="fas fa-edit mr-1"></i> REVISI
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ✨ MODAL REVISI DIPINDAHKAN KE SINI AGAR TIDAK MERUSAK TABEL ✨ --}}
    @foreach($linePlans as $p)
    <div class="modal fade" id="revisiModal-{{ $p->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 16px; border:none;">
                <div class="modal-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title" style="font-weight:900; font-size:16px; color:#1e293b;"><i class="fas fa-edit text-warning mr-2"></i>REVISI TARGET WELDING</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                
                <form action="{{ route('ppic.welding.update_wos', $p->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="modal-body p-4">
                        <div class="alert alert-warning mb-4" style="font-size:12px; font-weight:700;">
                            <i class="fas fa-info-circle mr-1"></i> Part: <b>{{ $p->part_no }}</b><br>
                            Ubah target di bawah ini. Sisa stok WIP gudang akan divalidasi dan disesuaikan otomatis!
                        </div>

                        <div class="row">
                            @if($shift == 'S1')
                            <div class="col-6 form-group">
                                <label style="font-size:11px; font-weight:800; color:#64748b;">Target Shift 1 (Reguler)</label>
                                <input type="number" class="form-control" name="s1_plan_reg" value="{{ $p->s1_plan_reg }}" style="font-weight:bold; font-family:'JetBrains Mono';">
                            </div>
                            <div class="col-6 form-group">
                                <label style="font-size:11px; font-weight:800; color:#64748b;">Target Shift 1 (Lembur/OT)</label>
                                <input type="number" class="form-control" name="s1_plan_ot" value="{{ $p->s1_plan_ot }}" style="font-weight:bold; font-family:'JetBrains Mono';">
                            </div>
                            @else
                            <div class="col-6 form-group">
                                <label style="font-size:11px; font-weight:800; color:#64748b;">Target Shift 2 (Reguler)</label>
                                <input type="number" class="form-control" name="s2_plan_reg" value="{{ $p->s2_plan_reg }}" style="font-weight:bold; font-family:'JetBrains Mono';">
                            </div>
                            <div class="col-6 form-group">
                                <label style="font-size:11px; font-weight:800; color:#64748b;">Target Shift 2 (Lembur/OT)</label>
                                <input type="number" class="form-control" name="s2_plan_ot" value="{{ $p->s2_plan_ot }}" style="font-weight:bold; font-family:'JetBrains Mono';">
                            </div>
                            @endif
                        </div>
                        
                        <div class="form-group mt-3">
                            <label style="font-size:11px; font-weight:800; color:#64748b;">Kapasitas/Jam (Pcs)</label>
                            <input type="number" class="form-control" name="cap_per_hour" value="{{ $p->cap_per_hour }}" required style="font-weight:bold;">
                        </div>
                    </div>
                    <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e2e8f0; border-radius: 0 0 16px 16px;">
                        <button type="button" class="btn btn-light" data-dismiss="modal" style="font-weight:800; font-size:12px; border-radius:50rem;">BATAL</button>
                        <button type="submit" class="btn btn-warning" style="font-weight:800; font-size:12px; border-radius:50rem; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);">UPDATE TARGET</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    @empty
    <div class="b-panel text-center py-5">
        <i class="fas fa-clipboard-list text-muted mb-3" style="font-size:40px; opacity:0.3;"></i>
        <h5 style="font-weight:800; color:#64748b;">BELUM ADA PLAN WELDING</h5>
        <p style="font-size:12px; color:#94a3b8; margin:0;">Klik tombol <b>+ NEW_PLAN</b> untuk mendeploy jadwal baru.</p>
    </div>
    @endforelse

</div>

{{-- ✨ MODAL NEW PLAN WELDING ✨ --}}
<div class="modal fade" id="newPlanModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border-radius: 16px; border:none;">
            <div class="modal-header" style="background:#1e293b; color:white; border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" style="font-weight:900; font-size:16px;"><i class="fas fa-plus-circle text-primary mr-2"></i>DEPLOY NEW WELDING PLAN</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            
            <form action="{{ route('ppic.welding.mps_store') }}" method="POST">
                @csrf
                <div class="modal-body p-4" style="background:#f8fafc;">
                    
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label style="font-size:11px; font-weight:800; color:#64748b;">TANGGAL PRODUKSI</label>
                            <input type="date" class="form-control shadow-sm" name="plan_date" value="{{ $date }}" required style="font-weight:bold; border:1px solid #cbd5e1; border-radius:8px;">
                        </div>
                        <div class="col-md-4 form-group">
                            <label style="font-size:11px; font-weight:800; color:#64748b;">STATION / MESIN LAS</label>
                            <select class="form-control shadow-sm" name="line_code" required style="font-weight:bold; border:1px solid #cbd5e1; border-radius:8px;">
                                <option value="">Pilih Station...</option>
                                @foreach($availableLines as $l)
                                    <option value="{{ $l->kode_line }}">{{ $l->kode_line }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label style="font-size:11px; font-weight:800; color:#64748b;">CUSTOMER</label>
                            <input type="text" class="form-control shadow-sm" id="custDisp" value="AUTO DETECT" readonly style="font-weight:bold; background:#e2e8f0; border:none; border-radius:8px;">
                        </div>
                    </div>

                    <div class="form-group mt-2">
                        <label style="font-size:11px; font-weight:800; color:#64748b;">PART NUMBER (WIP) <span class="text-danger">*</span></label>
                        <select class="form-control select2-part shadow-sm" name="part_no" id="partSelector" required style="width:100%;">
                            <option value="">Ketik Part No / Nama Part...</option>
                            @foreach($availableParts as $p)
                                <option value="{{ $p->part_no }}">{{ $p->part_no }} - {{ $p->part_name }} ({{ $p->customer_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- INDIKATOR STOK WIP GUDANG (AJAX) --}}
                    <div class="alert mt-3 mb-4 d-flex align-items-center gap-3" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 2px 5px rgba(0,0,0,0.02); padding:15px;">
                        <div style="background:#e0e7ff; color:#4318FF; width:45px; height:45px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:20px;">
                            <i class="fas fa-pallet"></i>
                        </div>
                        <div>
                            <h6 style="font-size:10px; font-weight:800; color:#64748b; margin:0 0 4px 0;">SISA STOK WIP (HASIL STAMPING) DI GUDANG</h6>
                            <h3 style="margin:0; font-weight:900; font-family:'JetBrains Mono'; color:#0f172a;" id="displayWipStock">
                                0 <span style="font-size:12px; font-weight:700; color:#64748b;">PCS</span>
                            </h3>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="p-3 mb-3" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px;">
                                <h6 style="font-size:11px; font-weight:900; color:#1e293b; border-bottom:1px solid #f1f5f9; padding-bottom:8px; margin-bottom:12px;">TARGET SHIFT 1 (DAY)</h6>
                                <div class="row">
                                    <div class="col-6 form-group mb-0">
                                        <label style="font-size:10px; font-weight:700; color:#64748b;">Reguler (Pcs)</label>
                                        <input type="number" class="form-control form-control-sm text-num" name="s1_plan_reg" value="0">
                                    </div>
                                    <div class="col-6 form-group mb-0">
                                        <label style="font-size:10px; font-weight:700; color:#64748b;">Lembur/OT (Pcs)</label>
                                        <input type="number" class="form-control form-control-sm text-num" name="s1_plan_ot" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 mb-3" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px;">
                                <h6 style="font-size:11px; font-weight:900; color:#1e293b; border-bottom:1px solid #f1f5f9; padding-bottom:8px; margin-bottom:12px;">TARGET SHIFT 2 (NIGHT)</h6>
                                <div class="row">
                                    <div class="col-6 form-group mb-0">
                                        <label style="font-size:10px; font-weight:700; color:#64748b;">Reguler (Pcs)</label>
                                        <input type="number" class="form-control form-control-sm text-num" name="s2_plan_reg" value="0">
                                    </div>
                                    <div class="col-6 form-group mb-0">
                                        <label style="font-size:10px; font-weight:700; color:#64748b;">Lembur/OT (Pcs)</label>
                                        <input type="number" class="form-control form-control-sm text-num" name="s2_plan_ot" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-0">
                            <label style="font-size:11px; font-weight:800; color:#64748b;">Kapasitas / Jam (Pcs)</label>
                            <input type="number" class="form-control shadow-sm text-num" name="cap_per_hour" value="300" required style="border-radius:8px;">
                        </div>
                        <div class="col-md-6 form-group mb-0">
                            <label style="font-size:11px; font-weight:800; color:#64748b;">Waktu Dandory (Menit)</label>
                            <input type="number" class="form-control shadow-sm text-num" name="dandory_time" value="15" required style="border-radius:8px;">
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="background:#ffffff; border-top:1px solid #e2e8f0; border-radius: 0 0 16px 16px;">
                    <button type="button" class="btn btn-light" data-dismiss="modal" style="font-weight:800; font-size:12px; border-radius:50rem;">BATAL</button>
                    <button type="submit" class="btn btn-primary" style="font-weight:800; font-size:12px; border-radius:50rem; background:#4318FF; border:none; box-shadow: 0 4px 10px rgba(67,24,255,0.3);">🚀 DEPLOY PLAN</button>
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
            allowClear: true
        });

        // Tampilkan error Session SweetAlert
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'PERHATIAN!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#4318FF'
            });
        @endif

        // AJAX Check Sisa Stok WIP Gudang saat Part Dipilih
        $('#partSelector').on('change', function() {
            let partNo = $(this).val();
            let custMatch = $(this).find('option:selected').text().match(/\(([^)]+)\)$/);
            
            if(custMatch) { $('#custDisp').val(custMatch[1]); }
            else { $('#custDisp').val('-'); }

            if(partNo) {
                $('#displayWipStock').html('<i class="fas fa-spinner fa-spin text-muted"></i>');
                $.ajax({
                    url: "{{ url('/ppic/welding/ajax/check-wip') }}/" + encodeURIComponent(partNo),
                    type: "GET",
                    success: function(res) {
                        if(res.status === 'success') {
                            let stockStr = res.wip_stock.toLocaleString('en-US');
                            let color = res.wip_stock <= 0 ? '#dc2626' : '#059669';
                            $('#displayWipStock').html('<span style="color:'+color+';">' + stockStr + '</span> <span style="font-size:12px; font-weight:700; color:#64748b;">PCS</span>');
                        } else {
                            $('#displayWipStock').html('<span class="text-danger">ERROR</span>');
                        }
                    },
                    error: function() {
                        $('#displayWipStock').html('<span class="text-danger">GAGAL</span>');
                    }
                });
            } else {
                $('#displayWipStock').html('0 <span style="font-size:12px; font-weight:700; color:#64748b;">PCS</span>');
                $('#custDisp').val('AUTO DETECT');
            }
        });
    });
</script>
@endsection