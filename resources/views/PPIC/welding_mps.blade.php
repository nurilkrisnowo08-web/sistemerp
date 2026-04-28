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
    
    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; color: var(--dark-surface); }

    /* 📊 SUMMARY CARDS */
    .stat-card { background: #fff; border-radius: 20px; padding: 20px; border: 1px solid #e2e8f0; transition: 0.3s; height: 100%; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(67, 97, 238, 0.08); border-color: var(--brand-primary); }
    .stat-label { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
    .stat-value { font-family: 'Orbitron'; font-size: 24px; font-weight: 800; color: var(--dark-surface); }

    /* 📈 LEDGER TABLE */
    .ledger-container { background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.04); }
    .table-ledger thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px; border-bottom: 2px solid #edf2f7; }
    .table-ledger td { padding: 18px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 13px; }
    
    /* Progress Mini */
    .progress-mini { height: 6px; border-radius: 10px; background: #e2e8f0; overflow: hidden; margin-top: 5px; }
    .progress-bar-mini { height: 100%; transition: 0.6s cubic-bezier(0.4, 0, 0.2, 1); }

    /* 🏷️ INPUT STYLE */
    .tech-input { border-radius: 14px; border: 2px solid #f1f5f9; font-weight: 700; transition: 0.3s; background: #f8fafc; }
    .tech-input:focus { border-color: var(--brand-primary); outline: none; background: #fff; box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1); }
    
    .modal-content { border-radius: 30px; border: none; overflow: hidden; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    {{-- 🛰️ HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding_MPS <span class="text-primary">v2.0</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0"><i class="fas fa-calendar-check text-primary mr-2"></i> Master Production Schedule // Robot & Manual Welding</p>
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

    {{-- 📊 SUMMARY STATS --}}
    @php
        $totalTarget = $plans->sum('total_target');
        $totalActual = $plans->sum('total_actual');
        $overallProgress = $totalTarget > 0 ? ($totalActual / $totalTarget) * 100 : 0;
    @endphp
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="stat-card border-left-primary" style="border-left-width: 5px !important;">
                <div class="stat-label">Total Scheduled Load</div>
                <div class="stat-value text-primary">{{ number_format($totalTarget) }} <small style="font-size: 12px;">PCS</small></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label text-success">Verified Output</div>
                <div class="stat-value text-success">{{ number_format($totalActual) }} <small style="font-size: 12px;">PCS</small></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-label">Production Efficiency</div>
                <div class="stat-value">{{ number_format($overallProgress, 1) }}%</div>
                <div class="progress-mini"><div class="progress-bar-mini bg-primary" style="width: {{ $overallProgress }}%"></div></div>
            </div>
        </div>
    </div>

    {{-- 📋 MPS TABLE --}}
    <div class="ledger-container animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Station</th>
                        <th class="text-left">Part Identification</th>
                        <th>Target Qty</th>
                        <th>Actual Output</th>
                        <th>Balance</th>
                        <th style="width: 200px;">Load Progress</th>
                        <th class="text-right pr-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $p)
                    @php 
                        $progress = $p->total_target > 0 ? ($p->total_actual / $p->total_target) * 100 : 0;
                        $isComplete = $p->total_actual >= $p->total_target && $p->total_target > 0;
                    @endphp
                    <tr>
                        <td class="text-left pl-4">
                            <span class="badge badge-dark px-3 py-2" style="font-family: 'JetBrains Mono'; border-radius: 8px;">{{ $p->line_code }}</span>
                        </td>
                        <td class="text-left">
                            <div class="font-weight-black text-dark" style="font-size: 14px;">{{ $p->part_no }}</div>
                            <small class="text-muted font-weight-bold uppercase">{{ $p->customer_code }}</small>
                        </td>
                        <td class="font-weight-black text-dark" style="font-family: 'JetBrains Mono'; font-size: 15px;">{{ number_format($p->total_target) }}</td>
                        <td class="text-success font-weight-black" style="font-family: 'JetBrains Mono'; font-size: 15px;">{{ number_format($p->total_actual) }}</td>
                        <td class="{{ $p->balance > 0 ? 'text-danger' : 'text-muted' }} font-weight-black" style="font-family: 'JetBrains Mono';">{{ number_format($p->balance) }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress-mini w-100 mr-2">
                                    <div class="progress-bar-mini {{ $isComplete ? 'bg-success' : 'bg-primary' }}" style="width: {{ min($progress, 100) }}%"></div>
                                </div>
                                <small class="font-weight-bold">{{ round($progress) }}%</small>
                            </div>
                        </td>
                        <td class="text-right pr-4">
                            @if($isComplete)
                                <span class="badge badge-success px-3 py-2 rounded-pill font-weight-bold"><i class="fas fa-check-circle mr-1"></i> COMPLETED</span>
                            @else
                                <span class="badge badge-warning px-3 py-2 rounded-pill font-weight-bold animate__animated animate__pulse animate__infinite">IN PRODUCTION</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-5 text-center">
                            <i class="fas fa-calendar-times fa-3x text-light mb-3"></i>
                            <p class="text-muted font-weight-bold">No welding plans scheduled for this date and shift.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🛡️ MODAL ADD WELDING PLAN --}}
<div class="modal fade animate__animated animate__fadeIn" id="modalAddPlan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-2xl">
            <div class="modal-header bg-dark text-white p-4 border-0">
                <h5 class="modal-title font-weight-black uppercase" style="font-family: 'Orbitron'; letter-spacing: 1px;">Register_Welding_Plan</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('ppic.welding.mps_store') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <label class="small font-weight-black text-muted uppercase">Deployment Date</label>
                            <input type="date" name="plan_date" class="form-control tech-input" value="{{ $date }}" required>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <label class="small font-weight-black text-muted uppercase">Authorized Station</label>
                            <select name="line_code" class="form-control tech-input" required>
                                <option value="" disabled selected>-- SELECT STATION --</option>
                                @foreach($availableLines as $line)
                                    <option value="{{ $line->kode_line }}">{{ $line->kode_line }} - {{ $line->nama_line }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="small font-weight-black text-muted uppercase">Target Part Identification</label>
                            <select name="part_no" class="form-control tech-input" required>
                                <option value="" disabled selected>-- CHOOSE PART --</option>
                                @foreach($availableParts as $part)
                                    <option value="{{ $part->part_no }}">[{{ $part->customer_code }}] {{ $part->part_no }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-2xl">
                                <label class="small font-weight-black text-primary uppercase">Shift 1 Target</label>
                                <input type="number" name="s1_plan_reg" class="form-control tech-input font-weight-black" style="font-size: 20px;" value="0">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-2xl">
                                <label class="small font-weight-black text-info uppercase">Shift 2 Target</label>
                                <input type="number" name="s2_plan_reg" class="form-control tech-input font-weight-black" style="font-size: 20px;" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-primary btn-block py-4 font-weight-black rounded-2xl shadow-xl" style="font-size: 1.1rem; letter-spacing: 1px;">
                        AUTHORIZE PRODUCTION LOAD
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection