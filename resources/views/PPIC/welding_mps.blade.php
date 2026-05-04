@extends('layout.admin')

@section('content')
<!-- Fonts & Animation -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --ind-navy: #0f172a;
        --ind-blue: #3b82f6;
        --ind-slate: #64748b;
        --ind-success: #10b981;
        --ind-danger: #f43f5e;
        --ind-border: #e2e8f0;
        --bg-glass: rgba(255, 255, 255, 0.9);
    }

    body { 
        background-color: #f8fafc; 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        color: var(--ind-navy);
    }

    /* 🛰️ Industrial Header */
    .header-container {
        background: linear-gradient(to right, #ffffff, #f1f5f9);
        border-radius: 24px;
        padding: 30px;
        border: 1px solid var(--ind-border);
        margin-bottom: 30px;
    }

    .heading-title {
        font-family: 'Orbitron', sans-serif;
        font-weight: 900;
        letter-spacing: -0.5px;
        color: var(--ind-navy);
        text-transform: uppercase;
    }

    /* 📊 KPI Stat Cards */
    .kpi-card {
        background: white;
        border-radius: 24px;
        padding: 24px;
        border: 1px solid var(--ind-border);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.05);
    }

    .kpi-card::after {
        content: "";
        position: absolute;
        bottom: 0; left: 0; right: 0; height: 4px;
        background: var(--accent-color, var(--ind-blue));
    }

    .kpi-label {
        font-size: 11px;
        font-weight: 800;
        color: var(--ind-slate);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }

    .kpi-value {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
        font-size: 1.8rem;
        color: var(--ind-navy);
    }

    /* 📋 Modern Table */
    .table-container {
        background: white;
        border-radius: 28px;
        border: 1px solid var(--ind-border);
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .table-mps thead th {
        background: #f8fafc;
        color: var(--ind-slate);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        padding: 20px;
        border: none;
    }

    .table-mps tbody td {
        padding: 18px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-weight: 600;
        font-size: 13.5px;
    }

    /* ⏱️ Badges & Indicators */
    .shift-toggle {
        background: #e2e8f0;
        border-radius: 50px;
        padding: 5px;
    }

    .shift-btn {
        border-radius: 50px;
        padding: 8px 24px;
        font-weight: 800;
        font-size: 12px;
        transition: 0.3s;
        border: none;
    }

    .shift-btn.active {
        background: var(--ind-navy);
        color: white;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
    }

    .progress-micro {
        height: 6px;
        background: #f1f5f9;
        border-radius: 10px;
        margin-top: 8px;
        width: 100px;
    }

    .time-chip {
        font-family: 'JetBrains Mono';
        background: #f1f5f9;
        color: var(--ind-navy);
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        border: 1px solid var(--ind-border);
    }

    /* 🤖 Modal Customization */
    .modal-industrial {
        border-radius: 32px;
        border: none;
    }

    .tech-input-group {
        background: #f8fafc;
        padding: 20px;
        border-radius: 20px;
        border: 1px solid var(--ind-border);
    }
</style>

<div class="container-fluid py-4 px-lg-5 animate__animated animate__fadeIn">
    
    {{-- 🛰️ TOP NAVIGATION & HEADER --}}
    <div class="header-container d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div>
            <h2 class="heading-title mb-1">MPS_WELDING <span class="text-primary">ENGINE</span></h2>
            <div class="d-flex align-items-center">
                <span class="badge badge-dark px-3 py-2 rounded-pill mr-3">SYSTEM_ACTIVE</span>
                <span class="text-muted font-weight-bold small"><i class="far fa-calendar-alt mr-1"></i> {{ date('D, d M Y', strtotime($date)) }}</span>
            </div>
        </div>

        <div class="d-flex align-items-center mt-3 mt-md-0">
            <!-- Shift Selector -->
            <div class="shift-toggle d-flex mr-3">
                <a href="?date={{$date}}&shift=S1" class="shift-btn {{ $shift == 'S1' ? 'active' : 'text-muted' }}">SHIFT_01</a>
                <a href="?date={{$date}}&shift=S2" class="shift-btn {{ $shift == 'S2' ? 'active' : 'text-muted' }}">SHIFT_02</a>
            </div>
            
            <input type="date" class="form-control border-0 shadow-sm rounded-pill font-weight-bold px-4 mr-3" 
                   value="{{ $date }}" style="width: 180px;"
                   onchange="location.href='?date='+this.value+'&shift={{$shift}}'">
            
            <button class="btn btn-primary rounded-pill px-4 font-weight-black shadow-lg" data-toggle="modal" data-target="#modalRegisterPlan">
                <i class="fas fa-plus mr-2"></i> NEW_PLAN
            </button>
        </div>
    </div>

    {{-- 📊 HUD KPI SECTION --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="kpi-card" style="--accent-color: var(--ind-blue)">
                <div class="kpi-label">Volume Target</div>
                <div class="kpi-value text-primary">{{ number_format($totalPlanQty) }}<small class="ml-1 text-muted" style="font-size: 12px">PCS</small></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="--accent-color: #8b5cf6">
                <div class="kpi-label">Load Capacity</div>
                <div class="kpi-value" style="color: #8b5cf6">{{ number_format($totalWorkingHours, 1) }}<small class="ml-1 text-muted" style="font-size: 12px">HRS</small></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card" style="--accent-color: var(--ind-warning)">
                <div class="kpi-label">Switching Loss</div>
                <div class="kpi-value text-warning">{{ $totalDandory }}<small class="ml-1 text-muted" style="font-size: 12px">MIN</small></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card bg-dark text-white border-0">
                <div class="kpi-label text-info">Operational Shift</div>
                <div class="kpi-value text-white">{{ $shift == 'S1' ? 'DAY_OPS' : 'NIGHT_OPS' }}</div>
            </div>
        </div>
    </div>

    {{-- 📋 MAIN DATA TABLE --}}
    <div class="table-container animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-mps mb-0">
                <thead>
                    <tr>
                        <th class="text-center">Pos</th>
                        <th>Part Identification</th>
                        <th class="text-center">M/P</th>
                        <th class="text-center">Quota</th>
                        <th class="text-center">Actual</th>
                        <th class="text-center">Gap</th>
                        <th class="text-center">T-Start</th>
                        <th class="text-center">T-Finish</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $index => $plan)
                    @php 
                        $percent = ($plan->total_target > 0) ? ($plan->total_actual / $plan->total_target) * 100 : 0;
                    @endphp
                    <tr>
                        <td class="text-center text-muted font-mono">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                        <td>
                            <div class="font-weight-black text-dark">{{ $plan->part_no }}</div>
                            <div class="text-muted small font-weight-bold" style="font-size: 10px">{{ $plan->part_name }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-light border px-2 py-1">{{ $plan->manpower }}P</span>
                        </td>
                        <td class="text-center font-mono font-weight-bold">{{ number_format($plan->total_target) }}</td>
                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center">
                                <span class="font-mono text-success font-weight-bold">{{ number_format($plan->total_actual) }}</span>
                                <div class="progress-micro">
                                    <div class="progress-bar bg-success" style="width: {{ min($percent, 100) }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center font-mono">
                            @if($plan->balance > 0)
                                <span class="text-danger">-{{ number_format($plan->balance) }}</span>
                            @else
                                <span class="text-success font-weight-bold">+{{ abs($plan->balance) }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="time-chip">{{ $plan->start_time }}</span>
                        </td>
                        <td class="text-center">
                            <span class="time-chip" style="background: #e0f2fe; color: #0369a1">{{ $plan->ahir_time }}</span>
                        </td>
                        <td class="text-center">
                            @if($percent >= 100)
                                <i class="fas fa-check-circle text-success" title="Completed"></i>
                            @elseif($percent > 0)
                                <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                            @else
                                <i class="far fa-clock text-muted"></i>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-5 text-center">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="60" class="opacity-25 mb-3" style="filter: grayscale(1)">
                            <p class="text-muted font-weight-bold">NO SCHEDULED DATA DETECTED FOR {{ $shift == 'S1' ? 'SHIFT 1' : 'SHIFT 2' }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Summary Footer -->
        <div class="bg-dark p-4 text-white d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="mr-4">
                    <span class="text-info small font-weight-black uppercase d-block">TOTAL_DAILY_TARGET</span>
                    <span class="font-mono h4 mb-0">{{ number_format($totalPlanQty) }}</span>
                </div>
                <div>
                    <span class="text-info small font-weight-black uppercase d-block">ESTIMATED_RUN_TIME</span>
                    <span class="font-mono h4 mb-0">{{ number_format($totalWorkingHours, 1) }} HRS</span>
                </div>
            </div>
            <div class="text-right">
                <span class="badge badge-primary px-4 py-2">ENGINE_V5_STABLE</span>
            </div>
        </div>
    </div>
</div>

{{-- 🛡️ MODAL: REGISTER PLAN --}}
<div class="modal fade animate__animated animate__fadeIn" id="modalRegisterPlan" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content modal-industrial shadow-2xl">
            <div class="modal-header bg-dark text-white p-4 border-0">
                <h5 class="modal-title font-weight-black" style="font-family: 'Orbitron'">REGISTER_PRODUCTION_PLAN</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('ppic.welding.mps_store') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="row">
                        <!-- Group 1: Part Info -->
                        <div class="col-md-4">
                            <div class="tech-input-group h-100">
                                <h6 class="font-weight-black mb-3"><i class="fas fa-tag mr-2 text-primary"></i> 01. PRODUCT_ID</h6>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Station Code</label>
                                    <select name="line_code" class="form-control rounded-pill border-0 shadow-sm" required>
                                        @foreach($availableLines as $line)
                                            <option value="{{ $line->kode_line }}">{{ $line->kode_line }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold">Part No</label>
                                    <select name="part_no" class="form-control rounded-pill border-0 shadow-sm" required>
                                        @foreach($availableParts as $p)
                                            <option value="{{ $p->part_no }}">[{{ $p->customer_code }}] {{ $p->part_no }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Group 2: Performance -->
                        <div class="col-md-4">
                            <div class="tech-input-group h-100">
                                <h6 class="font-weight-black mb-3"><i class="fas fa-bolt mr-2 text-warning"></i> 02. CAP_METRICS</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small font-weight-bold">Manpower</label>
                                        <input type="number" name="manpower" id="inp_mp" class="form-control rounded-pill border-0 shadow-sm" value="1" oninput="autoCalc()">
                                    </div>
                                    <div class="col-6">
                                        <label class="small font-weight-bold">Cap/Hr</label>
                                        <input type="number" name="cap_per_hour" id="inp_cap" class="form-control rounded-pill border-0 shadow-sm" value="100" oninput="autoCalc()">
                                    </div>
                                </div>
                                <div class="mt-4 p-3 bg-white rounded-xl border text-center shadow-sm">
                                    <label class="small font-weight-black text-muted uppercase d-block">Expected Machine Load</label>
                                    <h3 class="font-weight-black text-primary mb-0" id="display_hours">0.0H</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Group 3: Shift Target -->
                        <div class="col-md-4">
                            <div class="tech-input-group h-100">
                                <h6 class="font-weight-black mb-3"><i class="fas fa-layer-group mr-2 text-info"></i> 03. QUANTITY_ALLOC</h6>
                                <div class="form-group">
                                    <label class="small font-weight-bold text-primary">Shift 1 (Day) Target</label>
                                    <input type="number" name="s1_plan_reg" id="inp_s1" class="form-control rounded-pill border-0 shadow-sm" value="0" oninput="autoCalc()">
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold text-danger">Shift 2 (Night) Target</label>
                                    <input type="number" name="s2_plan_reg" id="inp_s2" class="form-control rounded-pill border-0 shadow-sm" value="0" oninput="autoCalc()">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <input type="hidden" name="plan_date" value="{{ $date }}">
                    <button type="submit" class="btn btn-primary btn-block py-4 font-weight-black rounded-pill shadow-xl" style="font-size: 1.1rem; letter-spacing: 1px;">
                        EXECUTE PRODUCTION PLAN
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