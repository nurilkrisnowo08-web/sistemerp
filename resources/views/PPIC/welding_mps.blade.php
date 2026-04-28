@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-warning: #f59e0b; --dark-surface: #0f172a; --bg-main: #f1f5f9;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #334155; }
    
    /* Industrial Header */
    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; color: var(--dark-surface); }
    .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-radius: 24px; border: 1px solid #e2e8f0; }

    /* Table Ledger Industrial */
    .ledger-container { background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.04); }
    .table-ledger thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px; border-bottom: 2px solid #edf2f7; }
    .table-ledger td { padding: 18px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 13px; }

    /* Progress UI */
    .progress-track { height: 8px; background: #e2e8f0; border-radius: 10px; overflow: hidden; margin-top: 5px; }
    .progress-fill { height: 100%; transition: 1s cubic-bezier(0.4, 0, 0.2, 1); }

    /* Modal Styling */
    .modal-content { border-radius: 35px; border: none; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
    .cap-control-box { background: var(--dark-surface); border-radius: 24px; padding: 25px; color: white; margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.1); }
    .tech-input { border-radius: 14px; border: 2px solid #f1f5f9; font-weight: 700; transition: 0.3s; background: #f8fafc; height: 50px; }
    .tech-input:focus { border-color: var(--brand-primary); outline: none; background: #fff; box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1); }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    {{-- 🛰️ HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding_MPS <span class="text-primary">v2.0</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0"><i class="fas fa-calendar-check text-primary mr-2"></i> Master Production Schedule // Global Tracking</p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
            <form action="{{ route('ppic.welding.mps') }}" method="GET" class="mr-3 d-flex align-items-center bg-white p-2 rounded-pill shadow-sm border">
                <i class="fas fa-filter mx-3 text-muted"></i>
                <input type="date" name="date" class="border-0 font-weight-bold text-dark" value="{{ $date }}" onchange="this.form.submit()">
                <select name="shift" class="border-0 font-weight-bold text-primary ml-2 pr-3" onchange="this.form.submit()">
                    <option value="S1" {{ $shift == 'S1' ? 'selected' : '' }}>SHIFT 1</option>
                    <option value="S2" {{ $shift == 'S2' ? 'selected' : '' }}>SHIFT 2</option>
                </select>
            </form>
            <button class="btn btn-dark rounded-pill px-4 font-weight-extrabold shadow-lg" data-toggle="modal" data-target="#modalAddPlan">
                <i class="fas fa-plus-circle mr-2"></i> CREATE PLAN
            </button>
        </div>
    </div>

    {{-- 📊 SUMMARY WIDGET --}}
    @php
        $totalScheduled = $plans->sum('total_target');
        $totalDone = $plans->sum('total_actual');
        $dayProgress = $totalScheduled > 0 ? ($totalDone / $totalScheduled) * 100 : 0;
    @endphp
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="glass-card p-4">
                <small class="text-muted font-weight-bold uppercase d-block mb-1">Load Status</small>
                <h2 class="heading-tech text-primary mb-0">{{ number_format($totalScheduled) }} <small style="font-size: 12px;">PCS</small></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4">
                <small class="text-muted font-weight-bold uppercase d-block mb-1">Validated Output</small>
                <h2 class="heading-tech text-success mb-0">{{ number_format($totalDone) }} <small style="font-size: 12px;">PCS</small></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 bg-dark text-white border-0">
                <small class="text-info font-weight-bold uppercase d-block mb-1">Completion Efficiency</small>
                <h2 class="heading-tech text-warning mb-0">{{ round($dayProgress, 1) }}%</h2>
            </div>
        </div>
    </div>

    {{-- 📋 MPS TABLE --}}
    <div class="ledger-container shadow-xl">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Station</th>
                        <th class="text-left">Part Identification</th>
                        <th>MP</th>
                        <th>Plan Qty</th>
                        <th>Actual WIP</th>
                        <th>Balance</th>
                        <th style="width: 150px;">Progress</th>
                        <th class="text-right pr-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $p)
                    @php 
                        $isComplete = $p->total_actual >= $p->total_target && $p->total_target > 0;
                        $pct = $p->total_target > 0 ? ($p->total_actual / $p->total_target) * 100 : 0;
                    @endphp
                    <tr>
                        <td class="text-left pl-4">
                            <span class="badge badge-dark px-3 py-2 font-mono" style="border-radius: 8px;">{{ $p->line_code }}</span>
                        </td>
                        <td class="text-left">
                            <div class="font-weight-black text-dark" style="font-size: 14px;">{{ $p->part_no }}</div>
                            <small class="text-muted font-weight-bold uppercase d-block" style="font-size: 10px;">{{ $p->part_name }}</small>
                            <span class="badge badge-light border text-primary mt-1" style="font-size: 9px;">{{ $p->customer_code }}</span>
                        </td>
                        <td class="font-weight-black text-muted">{{ $p->manpower }}</td>
                        <td class="font-weight-black text-dark font-mono">{{ number_format($p->total_target) }}</td>
                        <td class="text-success font-weight-black font-mono">{{ number_format($p->total_actual) }}</td>
                        <td class="{{ $p->balance > 0 ? 'text-danger' : 'text-muted' }} font-weight-black font-mono">{{ number_format($p->balance) }}</td>
                        <td>
                            <div class="progress-track">
                                <div class="progress-fill {{ $isComplete ? 'bg-success' : 'bg-primary' }}" style="width: {{ min($pct, 100) }}%"></div>
                            </div>
                            <small class="font-weight-bold" style="font-size: 9px;">{{ round($pct) }}%</small>
                        </td>
                        <td class="text-right pr-4">
                            @if($isComplete)
                                <span class="badge badge-success px-3 py-2 rounded-pill font-weight-bold animate__animated animate__pulse">COMPLETED</span>
                            @else
                                <span class="badge badge-warning px-3 py-2 rounded-pill font-weight-bold">IN PRODUCTION</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="py-5 text-center text-muted italic">-- No schedule entries found for this shift --</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🛡️ MODAL: REGISTER PRODUCTION PLAN --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalAddPlan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white p-4 border-0">
                <h5 class="modal-title heading-hub" style="color: white; font-size: 1.2rem;">
                    <i class="fas fa-microchip mr-2 text-primary"></i> Deploy_New_Plan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            
            <form action="{{ route('ppic.welding.mps_store') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    
                    {{-- Capacity Calculation HUD --}}
                    <div class="cap-control-box shadow-lg">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <label class="small font-weight-black text-white-50 uppercase mb-2">Manpower</label>
                                <input type="number" name="manpower" id="inp_mp" class="form-control bg-transparent border-0 text-white font-weight-black p-0" style="font-size: 32px; outline:none;" value="1" required oninput="autoCalc()">
                            </div>
                            <div class="col-md-3 border-left border-secondary">
                                <label class="small font-weight-black text-white-50 uppercase mb-2">Cap / Hour</label>
                                <input type="number" name="cap_per_hour" id="inp_cap" class="form-control bg-transparent border-0 text-white font-weight-black p-0" style="font-size: 32px; outline:none;" value="100" required oninput="autoCalc()">
                            </div>
                            <div class="col-md-6 text-right border-left border-secondary">
                                <div class="hour-badge">
                                    <label class="small font-weight-black text-white-50 uppercase d-block mb-1">Production Lead Time</label>
                                    <div class="hour-value" id="display_hours" style="font-family: 'Orbitron';">0.0 <small style="font-size: 14px;">HRS</small></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label class="small font-weight-black text-muted uppercase mb-2">Target Date</label>
                            <input type="date" name="plan_date" class="form-control tech-input" value="{{ $date }}" required>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="small font-weight-black text-muted uppercase mb-2">Station</label>
                            <select name="line_code" class="form-control tech-input" required>
                                <option value="" disabled selected>-- CHOOSE MACHINE --</option>
                                @foreach($availableLines as $line)
                                    <option value="{{ $line->kode_line }}">{{ $line->kode_line }} ({{ $line->nama_line }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="small font-weight-black text-muted uppercase mb-2">Part Specification</label>
                            <select name="part_no" class="form-control tech-input" required>
                                <option value="" disabled selected>-- SELECT PART --</option>
                                @foreach($availableParts as $part)
                                    <option value="{{ $part->part_no }}">[{{ $part->customer_code }}] {{ $part->part_no }} - {{ $part->part_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <div class="p-4 rounded-3xl" style="background: #f0f4ff; border: 1px solid #d0dfff;">
                                <label class="small font-weight-bold text-primary uppercase mb-2 d-block">Shift 1 Output Target</label>
                                <input type="number" name="s1_plan_reg" id="inp_s1" class="form-control border-0 bg-transparent font-weight-black text-primary p-0" style="font-size: 42px; outline: none;" value="0" oninput="autoCalc()">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 rounded-3xl" style="background: #fdf2f2; border: 1px solid #fee2e2;">
                                <label class="small font-weight-bold text-danger uppercase mb-2 d-block">Shift 2 Output Target</label>
                                <input type="number" name="s2_plan_reg" id="inp_s2" class="form-control border-0 bg-transparent font-weight-black text-danger p-0" style="font-size: 42px; outline: none;" value="0" oninput="autoCalc()">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-primary btn-block py-4 font-weight-black rounded-2xl shadow-xl" style="font-size: 1.2rem; letter-spacing: 2px;">
                        CONFIRM & DEPLOY SCHEDULE
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
        document.getElementById('display_hours').innerHTML = hours.toFixed(1) + ' <small style="font-size: 14px;">HRS</small>';
    }

    $('#modalAddPlan').on('shown.bs.modal', function () { autoCalc(); });
</script>
@endsection