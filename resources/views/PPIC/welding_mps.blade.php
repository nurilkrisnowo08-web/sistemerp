@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --ind-blue: #4361ee; --ind-navy: #0f172a; --ind-success: #10b981;
        --ind-danger: #ef4444; --ind-warning: #f59e0b; --bg-soft: #f1f5f9;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-soft); color: #334155; }
    
    /* 🛰️ Header & Typography */
    .heading-tech { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; color: var(--ind-navy); }
    
    /* 📊 Stat Card Style v5.0 */
    .stat-card { 
        background: #fff; border-radius: 25px; padding: 25px; border: none; transition: 0.3s; 
        box-shadow: 0 10px 25px rgba(0,0,0,0.02); height: 100%; border-bottom: 5px solid transparent;
    }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
    .stat-value { font-family: 'Orbitron'; font-weight: 900; line-height: 1; font-size: 2.2rem; }
    .stat-label { font-weight: 800; color: #64748b; text-transform: uppercase; font-size: 11px; letter-spacing: 1px; }

    /* 📋 Main Table Styling */
    .main-ledger-card { background: #fff; border-radius: 30px; border: none; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.03); }
    .table-tech thead th { 
        background: #f8fafc; color: #64748b; font-size: 10px; font-weight: 800; 
        text-transform: uppercase; letter-spacing: 1.5px; border: none; padding: 20px;
    }
    .table-tech td { padding: 20px; vertical-align: middle; font-weight: 700; border-bottom: 1px solid #f1f5f9; font-size: 13px; }

    /* ⏱️ Time & Badges */
    .time-badge { background: #fff3cd; color: #856404; padding: 5px 12px; border-radius: 8px; font-family: 'JetBrains Mono'; font-weight: 800; font-size: 12px; border: 1px solid #ffeeba; }
    .balance-warn { background: #fee2e2; color: var(--ind-danger); padding: 6px 12px; border-radius: 10px; border: 1px solid #fecaca; display: inline-flex; align-items: center; }

    /* 🤖 Modal & Inputs */
    .modal-content { border-radius: 40px; border: none; }
    .tech-input { border-radius: 15px; border: 2px solid #f1f5f9; font-weight: 700; background: #f8fafc; padding: 12px 20px; height: auto; }
    .tech-input:focus { border-color: var(--ind-blue); background: #fff; box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1); }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    
    {{-- 🛰️ HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="heading-tech mb-1">Welding_MPS <span class="text-primary">v5.0</span></h1>
            <p class="text-muted small font-weight-bold uppercase mb-0">
                <i class="fas fa-microchip text-primary mr-2"></i> Operational Date: {{ date('d F Y', strtotime($date)) }}
            </p>
        </div>
        <div class="d-flex align-items-center">
            <div class="btn-group bg-white rounded-pill p-1 shadow-sm mr-3 border">
                <a href="?date={{$date}}&shift=S1" class="btn rounded-pill px-4 font-weight-bold {{ $shift == 'S1' ? 'btn-primary' : 'btn-light' }}">SHIFT_01</a>
                <a href="?date={{$date}}&shift=S2" class="btn rounded-pill px-4 font-weight-bold {{ $shift == 'S2' ? 'btn-primary' : 'btn-light' }}">SHIFT_02</a>
            </div>
            <input type="date" class="form-control rounded-pill border-0 shadow-sm font-weight-bold px-4 mr-3" value="{{ $date }}" onchange="location.href='?date='+this.value+'&shift={{$shift}}'">
            <button class="btn btn-primary rounded-pill px-4 font-weight-black shadow-lg" data-toggle="modal" data-target="#modalRegisterPlan">
                <i class="fas fa-plus-circle mr-2"></i> REGISTER PLAN
            </button>
        </div>
    </div>

    {{-- 📊 HUD KPI CARDS --}}
    <div class="row mb-5">
        <div class="col-md-3">
            <div class="stat-card border-primary">
                <p class="stat-label">Total Target (Qty)</p>
                <h2 class="stat-value text-primary">{{ number_format($totalPlanQty ?? 0) }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-info">
                <p class="stat-label">Workload (Hours)</p>
                <h2 class="stat-value text-info">{{ number_format($totalWorkingHours ?? 0, 1) }}<small style="font-size: 14px;">H</small></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-warning">
                <p class="stat-label">Switch Loss (Dandory)</p>
                <h2 class="stat-value text-warning">{{ $totalDandory ?? 0 }}<small style="font-size: 14px;">M</small></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-dark text-white text-center border-0">
                <p class="stat-label text-info">Current Shift</p>
                <h2 class="stat-value text-warning uppercase" style="font-size: 1.8rem;">{{ $shift == 'S1' ? 'DAY_OPS' : 'NIGHT_OPS' }}</h2>
            </div>
        </div>
    </div>

    {{-- 📋 MAIN SCHEDULE TABLE --}}
    <div class="main-ledger-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 text-center table-tech">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-left">Identification</th>
                        <th>MP</th>
                        <th>Target</th>
                        <th>Actual</th>
                        <th>Balance</th>
                        <th>Start</th>
                        <th>Finish</th>
                        <th>Dandory</th>
                        <th>Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $index => $plan)
                    <tr>
                        <td class="text-muted small">{{ $index + 1 }}</td>
                        <td class="text-left">
                            <div class="text-dark font-weight-black" style="font-size: 14px;">{{ $plan->part_no }}</div>
                            <small class="text-muted font-weight-bold uppercase">{{ $plan->part_name }}</small>
                        </td>
                        <td><span class="badge badge-light border px-2 py-1">{{ $plan->manpower }}P</span></td>
                        <td class="font-weight-black text-dark" style="font-size: 15px;">{{ number_format($plan->total_target) }}</td>
                        <td class="text-success font-weight-black" style="font-size: 15px;">{{ number_format($plan->total_actual) }}</td>
                        <td>
                            @if($plan->balance > 0)
                                <div class="balance-warn">
                                    <i class="fas fa-exclamation-triangle mr-2"></i> {{ number_format($plan->balance) }}
                                </div>
                            @else
                                <span class="badge badge-success px-3 py-2 rounded-pill"><i class="fas fa-check-circle mr-1"></i> OK</span>
                            @endif
                        </td>
                        <td><span class="time-badge">{{ $plan->start_time ?? '--:--' }}</span></td>
                        <td><span class="time-badge" style="background:#e0e7ff; color:#3730a3; border-color:#c7d2fe;">{{ $plan->ahir_time ?? '--:--' }}</span></td>
                        <td class="text-muted font-weight-bold">{{ $plan->dandory_time }}m</td>
                        <td class="text-muted small">-</td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="py-5 text-muted font-weight-bold">-- NO PRODUCTION DATA FOR {{ $shift }} --</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- GREEN FOOTER SUMMARY --}}
        <div class="bg-success p-4 text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-black uppercase" style="letter-spacing: 1px;">Daily Summary Total : {{ number_format($totalPlanQty ?? 0) }}</h5>
            <h5 class="mb-0 font-weight-black uppercase text-light" style="letter-spacing: 1px;">Total Est. Work : {{ number_format($totalWorkingHours ?? 0, 1) }} HOURS</h5>
        </div>
    </div>
</div>

{{-- 🛡️ MODAL: REGISTER PLAN (ELEGAN) --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalRegisterPlan" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow-2xl">
            <div class="modal-header bg-dark text-white p-4 border-0">
                <h5 class="modal-title heading-tech" style="color: white; font-size: 1.1rem;">
                    <i class="fas fa-plus-circle mr-2 text-primary"></i> Register_Plan_System
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('ppic.welding.mps_store') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="p-4 rounded-3xl h-100" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                <label class="small font-weight-black text-muted uppercase mb-3 d-block">01. Product_Identity</label>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold">Select Station</label>
                                    <select name="line_code" class="form-control tech-input" required>
                                        @foreach($availableLines as $line)
                                            <option value="{{ $line->kode_line }}">{{ $line->kode_line }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Part Identification</label>
                                    <select name="part_no" class="form-control tech-input" required>
                                        <option value="" disabled selected>-- SELECT PART --</option>
                                        @foreach($availableParts as $p)
                                            <option value="{{ $p->part_no }}">[{{ $p->customer_code }}] {{ $p->part_no }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-4 rounded-3xl h-100" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                <label class="small font-weight-black text-muted uppercase mb-3 d-block">02. Performance_Metrics</label>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small font-weight-bold">Manpower</label>
                                        <input type="number" name="manpower" id="inp_mp" class="form-control tech-input" value="1" oninput="autoCalc()">
                                    </div>
                                    <div class="col-6">
                                        <label class="small font-weight-bold">Cap/Hour</label>
                                        <input type="number" name="cap_per_hour" id="inp_cap" class="form-control tech-input" value="100" oninput="autoCalc()">
                                    </div>
                                </div>
                                <div class="mt-4 p-3 bg-white rounded-xl border text-center">
                                    <label class="small font-weight-black text-muted uppercase mb-1 d-block">Calculated M/C Load</label>
                                    <h3 class="font-weight-black text-primary mb-0" id="display_hours" style="font-family: 'Orbitron';">0.0H</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-4 rounded-3xl h-100" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                <label class="small font-weight-black text-muted uppercase mb-3 d-block">03. Quantity_Planning</label>
                                <div class="form-group">
                                    <label class="small font-weight-bold text-primary">Shift 1 Target</label>
                                    <input type="number" name="s1_plan_reg" id="inp_s1" class="form-control tech-input border-primary" value="0" oninput="autoCalc()">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold text-danger">Shift 2 Target</label>
                                    <input type="number" name="s2_plan_reg" id="inp_s2" class="form-control tech-input border-danger" value="0" oninput="autoCalc()">
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="plan_date" value="{{ $date }}">
                    <button type="submit" class="btn btn-primary btn-block py-4 font-weight-black rounded-2xl shadow-xl" style="font-size: 1.2rem;">
                        AUTHORIZE & DEPLOY PLAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function autoCalc() {
        let cap = parseFloat(document.getElementById('inp_cap').value) || 0;
        let s1 = parseFloat(document.getElementById('inp_s1').value) || 0;
        let s2 = parseFloat(document.getElementById('inp_s2').value) || 0;
        let total = s1 + s2;
        let hours = (cap > 0 && total > 0) ? (total / cap) : 0;
        document.getElementById('display_hours').innerText = hours.toFixed(1) + 'H';
    }
</script>
@endsection