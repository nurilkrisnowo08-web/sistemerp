@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --ind-navy: #0f172a;
        --ind-blue: #3b82f6;
        --ind-slate: #64748b;
        --ind-border: #e2e8f0;
    }
    body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: var(--ind-navy); }
    .header-container { background: linear-gradient(to right, #ffffff, #f1f5f9); border-radius: 24px; padding: 30px; border: 1px solid var(--ind-border); margin-bottom: 30px; }
    .heading-title { font-family: 'Orbitron', sans-serif; font-weight: 900; letter-spacing: -0.5px; color: var(--ind-navy); text-transform: uppercase; }
    .kpi-card { background: white; border-radius: 24px; padding: 24px; border: 1px solid var(--ind-border); transition: all 0.3s ease; position: relative; overflow: hidden; }
    .kpi-card::after { content: ""; position: absolute; bottom: 0; left: 0; right: 0; height: 4px; background: var(--accent-color, var(--ind-blue)); }
    .kpi-label { font-size: 11px; font-weight: 800; color: var(--ind-slate); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
    .kpi-value { font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 1.8rem; color: var(--ind-navy); }
    .table-container { background: white; border-radius: 28px; border: 1px solid var(--ind-border); overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    .table-mps thead th { background: #f8fafc; color: var(--ind-slate); font-size: 11px; font-weight: 800; text-transform: uppercase; padding: 20px; border: none; }
    .table-mps tbody td { padding: 18px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 13.5px; }
    .shift-toggle { background: #e2e8f0; border-radius: 50px; padding: 5px; }
    .shift-btn { border-radius: 50px; padding: 8px 20px; font-weight: 800; font-size: 12px; transition: 0.3s; border: none; background: transparent; color: #64748b; cursor: pointer; outline: none !important; }
    .shift-btn.active { background: var(--ind-navy); color: white; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); }
    .progress-micro { height: 6px; background: #f1f5f9; border-radius: 10px; margin-top: 8px; width: 100px; }
    .time-chip { font-family: 'JetBrains Mono'; background: #f1f5f9; color: var(--ind-navy); padding: 4px 10px; border-radius: 6px; font-size: 12px; border: 1px solid var(--ind-border); }
    .modal-industrial { border-radius: 32px; border: none; }
    .tech-input-group { background: #f8fafc; padding: 20px; border-radius: 20px; border: 1px solid var(--ind-border); }
</style>

<div class="container-fluid py-4 px-lg-5 animate__animated animate__fadeIn">
    
    @if(session('success')) <div class="alert alert-success border-0 shadow-sm" style="border-radius: 16px;"><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 16px;"><i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}</div> @endif

    <div class="header-container d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div>
            <h2 class="heading-title mb-1">MPS_WELDING <span class="text-primary">ENGINE</span></h2>
            <div class="d-flex align-items-center">
                <span class="badge badge-dark px-3 py-2 rounded-pill mr-3">SYSTEM_ACTIVE</span>
                <span class="text-muted font-weight-bold small"><i class="far fa-calendar-alt mr-1"></i> {{ date('D, d M Y', strtotime($date)) }}</span>
            </div>
        </div>

        <div class="d-flex align-items-center mt-3 mt-md-0 flex-wrap">
            {{-- FILTER SHIFT CEPAT (ALL / S1 / S2) --}}
            <div class="shift-toggle d-flex mr-3">
                <button type="button" class="shift-btn active" onclick="filterShiftView('ALL', this)">ALL</button>
                <button type="button" class="shift-btn" onclick="filterShiftView('S1', this)">SHIFT_01</button>
                <button type="button" class="shift-btn" onclick="filterShiftView('S2', this)">SHIFT_02</button>
            </div>
            <input type="date" class="form-control border-0 shadow-sm rounded-pill font-weight-bold px-4 mr-3" value="{{ $date }}" style="width: 180px;" onchange="location.href='?date='+this.value">
            <button class="btn btn-primary rounded-pill px-4 font-weight-black shadow-lg" data-toggle="modal" data-target="#modalRegisterPlan"><i class="fas fa-plus mr-2"></i> NEW_PLAN</button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="kpi-card" style="--accent-color: var(--ind-blue)"><div class="kpi-label">Volume Target</div><div class="kpi-value text-primary">{{ number_format($totalPlanQty) }}<small class="ml-1 text-muted" style="font-size: 12px">PCS</small></div></div></div>
        <div class="col-md-3"><div class="kpi-card" style="--accent-color: #8b5cf6"><div class="kpi-label">Load Capacity</div><div class="kpi-value" style="color: #8b5cf6">{{ number_format($totalWorkingHours, 1) }}<small class="ml-1 text-muted" style="font-size: 12px">HRS</small></div></div></div>
        <div class="col-md-3"><div class="kpi-card" style="--accent-color: #f59e0b"><div class="kpi-label">Switching Loss</div><div class="kpi-value text-warning">{{ $totalDandory }}<small class="ml-1 text-muted" style="font-size: 12px">MIN</small></div></div></div>
        <div class="col-md-3"><div class="kpi-card bg-dark text-white border-0"><div class="kpi-label text-info">Operational Shift</div><div class="kpi-value text-white" id="kpiShiftLabel">ALL_OPS</div></div></div>
    </div>

    @php
        $weldingAreas = [
            'AREA WELDING ROBOT' => [
                'icon'  => 'fa-robot',
                'color' => '#4f46e5',
                'plans' => $plans->filter(fn($p) => str_contains(strtoupper($p->line_code), 'ROBOT'))
            ],
            'AREA WELDING SPOT' => [
                'icon'  => 'fa-bolt',
                'color' => '#0284c7',
                'plans' => $plans->filter(fn($p) => !str_contains(strtoupper($p->line_code), 'ROBOT'))
            ],
        ];
    @endphp

    @if($plans->isEmpty())
        <div class="text-center py-5 mt-5">
            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="opacity-25 mb-3" style="filter: grayscale(1)">
            <h5 class="text-muted font-weight-bold">NO WELDING SCHEDULE DETECTED</h5>
            <p class="text-muted">Klik tombol "New Plan" di kanan atas untuk membuat jadwal.</p>
        </div>
    @else
        @foreach($weldingAreas as $areaName => $areaData)
            @if($areaData['plans']->isNotEmpty())
                <div class="area-wrapper mb-4">
                    {{-- PEMBATAS DEPARTEMEN (ROBOT / SPOT) --}}
                    <div class="d-flex align-items-center mb-3 mt-4">
                        <span class="badge text-white px-3 py-2 rounded-pill shadow-sm" style="background: {{ $areaData['color'] }}; font-size: 12px; letter-spacing: 1px;">
                            <i class="fas {{ $areaData['icon'] }} mr-2"></i> {{ $areaName }}
                        </span>
                        <div class="flex-grow-1 ml-3" style="height: 2px; background: #e2e8f0;"></div>
                    </div>

                    {{-- GROUPING PER STATION --}}
                    @foreach($areaData['plans']->groupBy('line_code') as $lineCode => $linePlans)
                        @php
                            $s1Plans = $linePlans->filter(fn($p) => ($p->s1_plan_reg + $p->s1_plan_ot) > 0)->values();
                            $s2Plans = $linePlans->filter(fn($p) => ($p->s2_plan_reg + $p->s2_plan_ot) > 0)->values();
                        @endphp
                        <div class="table-container station-card animate__animated animate__fadeInUp mb-4" data-has-s1="{{ $s1Plans->isNotEmpty() ? '1' : '0' }}" data-has-s2="{{ $s2Plans->isNotEmpty() ? '1' : '0' }}">
                            <div class="bg-dark text-white px-4 py-3 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 font-weight-black" style="letter-spacing: 1px;">
                                    <i class="fas {{ $areaData['icon'] }} mr-2 text-info"></i> STATION: {{ $lineCode }}
                                </h6>
                                <div>
                                    <a href="{{ url('/ppic/welding/print-wos/'.$date.'/'.$lineCode) }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3 mr-2 font-weight-bold"><i class="fas fa-print mr-1"></i> PRINT WOS</a>
                                    <a href="{{ url('/ppic/welding/print-serah-terima/'.$date.'/'.$lineCode) }}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 font-weight-bold"><i class="fas fa-file-alt mr-1"></i> PRINT BPRM</a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-mps mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Pos</th><th>Part Identification</th><th class="text-center">M/P</th>
                                            <th class="text-center">PLANNING</th><th class="text-center">Actual</th><th class="text-center">Gap</th>
                                            <th class="text-center">T-Start</th><th class="text-center">T-Finish</th><th class="text-center">Status</th><th class="text-right pr-4">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- ================= SHIFT 1 (DAY) ================= --}}
                                        @if($s1Plans->isNotEmpty())
                                            <tr class="shift-row-s1" style="background: #eff6ff;">
                                                <td colspan="10" class="py-2 px-4 font-weight-bold text-primary" style="font-size: 11px; letter-spacing: 1px;">
                                                    <i class="fas fa-sun mr-1"></i> SHIFT 1 (DAY_OPS) — {{ $s1Plans->count() }} PART
                                                </td>
                                            </tr>
                                            @foreach($s1Plans as $index => $plan)
                                            @php
                                                $targetS1 = $plan->s1_plan_reg + $plan->s1_plan_ot;
                                                $percent  = ($plan->total_target > 0) ? ($plan->total_actual / $plan->total_target) * 100 : 0;
                                            @endphp
                                            <tr class="shift-row-s1">
                                                <td class="text-center text-muted font-mono">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                                <td><div class="font-weight-black text-dark">{{ $plan->part_no }}</div><div class="text-muted small font-weight-bold" style="font-size: 10px">{{ $plan->part_name }}</div></td>
                                                <td class="text-center"><span class="badge badge-light border px-2 py-1">{{ $plan->manpower }}P</span></td>
                                                <td class="text-center font-mono font-weight-bold text-primary">{{ number_format($targetS1) }}</td>
                                                <td class="text-center"><div class="d-flex flex-column align-items-center"><span class="font-mono text-success font-weight-bold">{{ number_format($plan->total_actual) }}</span><div class="progress-micro"><div class="progress-bar bg-success" style="width: {{ min($percent, 100) }}%"></div></div></div></td>
                                                <td class="text-center font-mono">
                                                    @if($plan->balance > 0) <span class="text-danger">-{{ number_format($plan->balance) }}</span>
                                                    @else <span class="text-success font-weight-bold">+{{ abs($plan->balance) }}</span> @endif
                                                </td>
                                                <td class="text-center"><span class="time-chip">{{ $plan->start_time }}</span></td>
                                                <td class="text-center"><span class="time-chip" style="background: #e0f2fe; color: #0369a1">{{ $plan->ahir_time }}</span></td>
                                                <td class="text-center">
                                                    @if($percent >= 100) <i class="fas fa-check-circle text-success" title="Completed"></i>
                                                    @elseif($percent > 0) <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                                                    @else <i class="far fa-clock text-muted"></i> @endif
                                                </td>
                                                <td class="text-right pr-4">
                                                    @if($plan->total_actual > 0)
                                                        <button class="btn btn-sm text-muted rounded-pill px-3 shadow-none border" style="background:#f1f5f9; cursor:not-allowed;" title="LOCKED: Already in Process"><i class="fas fa-lock mr-1"></i> LOCKED</button>
                                                    @else
                                                        <button class="btn btn-sm btn-warning rounded-pill px-3 font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalEditPlan{{ $plan->id }}"><i class="fas fa-edit mr-1"></i> REVISI</button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        @endif

                                        {{-- ================= SHIFT 2 (NIGHT) ================= --}}
                                        @if($s2Plans->isNotEmpty())
                                            <tr class="shift-row-s2" style="background: #fef2f2;">
                                                <td colspan="10" class="py-2 px-4 font-weight-bold text-danger" style="font-size: 11px; letter-spacing: 1px;">
                                                    <i class="fas fa-moon mr-1"></i> SHIFT 2 (NIGHT_OPS) — {{ $s2Plans->count() }} PART
                                                </td>
                                            </tr>
                                            @foreach($s2Plans as $index => $plan)
                                            @php
                                                $targetS2 = $plan->s2_plan_reg + $plan->s2_plan_ot;
                                                $percent  = ($plan->total_target > 0) ? ($plan->total_actual / $plan->total_target) * 100 : 0;
                                            @endphp
                                            <tr class="shift-row-s2">
                                                <td class="text-center text-muted font-mono">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                                <td><div class="font-weight-black text-dark">{{ $plan->part_no }}</div><div class="text-muted small font-weight-bold" style="font-size: 10px">{{ $plan->part_name }}</div></td>
                                                <td class="text-center"><span class="badge badge-light border px-2 py-1">{{ $plan->manpower }}P</span></td>
                                                <td class="text-center font-mono font-weight-bold text-danger">{{ number_format($targetS2) }}</td>
                                                <td class="text-center"><div class="d-flex flex-column align-items-center"><span class="font-mono text-success font-weight-bold">{{ number_format($plan->total_actual) }}</span><div class="progress-micro"><div class="progress-bar bg-success" style="width: {{ min($percent, 100) }}%"></div></div></div></td>
                                                <td class="text-center font-mono">
                                                    @if($plan->balance > 0) <span class="text-danger">-{{ number_format($plan->balance) }}</span>
                                                    @else <span class="text-success font-weight-bold">+{{ abs($plan->balance) }}</span> @endif
                                                </td>
                                                <td class="text-center"><span class="time-chip">{{ $plan->start_time }}</span></td>
                                                <td class="text-center"><span class="time-chip" style="background: #fee2e2; color: #b91c1c">{{ $plan->ahir_time }}</span></td>
                                                <td class="text-center">
                                                    @if($percent >= 100) <i class="fas fa-check-circle text-success" title="Completed"></i>
                                                    @elseif($percent > 0) <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                                                    @else <i class="far fa-clock text-muted"></i> @endif
                                                </td>
                                                <td class="text-right pr-4">
                                                    @if($plan->total_actual > 0)
                                                        <button class="btn btn-sm text-muted rounded-pill px-3 shadow-none border" style="background:#f1f5f9; cursor:not-allowed;" title="LOCKED: Already in Process"><i class="fas fa-lock mr-1"></i> LOCKED</button>
                                                    @else
                                                        <button class="btn btn-sm btn-warning rounded-pill px-3 font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalEditPlan{{ $plan->id }}"><i class="fas fa-edit mr-1"></i> REVISI</button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach
    @endif
</div>

{{-- MODAL REGISTER PLAN --}}
<div class="modal fade animate__animated animate__fadeIn" id="modalRegisterPlan" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content modal-industrial shadow-2xl">
            <div class="modal-header bg-dark text-white p-4 border-0"><h5 class="modal-title font-weight-black" style="font-family: 'Orbitron'">REGISTER_PRODUCTION_PLAN</h5><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div>
            <form action="{{ route('ppic.welding.mps_store') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="tech-input-group h-100">
                                <h6 class="font-weight-black mb-3"><i class="fas fa-tag mr-2 text-primary"></i>PRODUCT_ID</h6>
                                <div class="form-group"><label class="small font-weight-bold">Station Code</label><select name="line_code" class="form-control rounded-pill border-0 shadow-sm" required>@foreach($availableLines as $line)<option value="{{ $line->kode_line }}">{{ $line->kode_line }}</option>@endforeach</select></div>
                                <div class="form-group"><label class="small font-weight-bold">Part No</label><select name="part_no" class="form-control rounded-pill border-0 shadow-sm" required>@foreach($availableParts as $p)<option value="{{ $p->part_no }}">[{{ $p->customer_code }}] {{ $p->part_no }}</option>@endforeach</select></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="tech-input-group h-100">
                                <h6 class="font-weight-black mb-3"><i class="fas fa-bolt mr-2 text-warning"></i> 02. CAP_METRICS</h6>
                                <div class="row">
                                    <div class="col-6"><label class="small font-weight-bold">Manpower</label><input type="number" name="manpower" id="inp_mp_new" class="form-control rounded-pill border-0 shadow-sm" value="1" oninput="autoCalc('new')"></div>
                                    <div class="col-6"><label class="small font-weight-bold">Cap/Hr</label><input type="number" name="cap_per_hour" id="inp_cap_new" class="form-control rounded-pill border-0 shadow-sm" value="100" oninput="autoCalc('new')"></div>
                                </div>
                                <div class="mt-4 p-3 bg-white rounded-xl border text-center shadow-sm"><label class="small font-weight-black text-muted uppercase d-block">Expected Machine Load</label><h3 class="font-weight-black text-primary mb-0" id="display_hours_new">0.0H</h3></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="tech-input-group h-100">
                                <h6 class="font-weight-black mb-3"><i class="fas fa-layer-group mr-2 text-info"></i> 03. QUANTITY_ALLOC</h6>
                                <div class="form-group"><label class="small font-weight-bold text-primary">Shift 1 (Day) Target</label><input type="number" name="s1_plan_reg" id="inp_s1_new" class="form-control rounded-pill border-0 shadow-sm" value="0" oninput="autoCalc('new')"></div>
                                <div class="form-group"><label class="small font-weight-bold text-danger">Shift 2 (Night) Target</label><input type="number" name="s2_plan_reg" id="inp_s2_new" class="form-control rounded-pill border-0 shadow-sm" value="0" oninput="autoCalc('new')"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0"><input type="hidden" name="plan_date" value="{{ $date }}"><button type="submit" class="btn btn-primary btn-block py-4 font-weight-black rounded-pill shadow-xl" style="font-size: 1.1rem; letter-spacing: 1px;">EXECUTE PRODUCTION PLAN</button></div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL REVISI PLAN --}}
@foreach($plans as $plan)
    @if($plan->total_actual == 0)
    <div class="modal fade animate__animated animate__fadeIn" id="modalEditPlan{{ $plan->id }}" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content modal-industrial shadow-2xl border-warning">
                <div class="modal-header bg-warning text-dark p-4 border-0"><h5 class="modal-title font-weight-black" style="font-family: 'Orbitron'">REVISI_PRODUCTION_PLAN</h5><button type="button" class="close text-dark" data-dismiss="modal"><span>&times;</span></button></div>
                <form action="{{ route('ppic.welding.update_wos', $plan->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-5">
                        <div class="alert alert-info border-0 mb-4 rounded-lg"><i class="fas fa-info-circle mr-2"></i> <strong>Tips:</strong> Kosongkan (Isi angka 0) pada kedua target Shift untuk MENGHAPUS / MEMBATALKAN jadwal secara otomatis. WIP akan kembali utuh.</div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="tech-input-group h-100 border-warning">
                                    <h6 class="font-weight-black mb-3"><i class="fas fa-tag mr-2 text-primary"></i>PRODUCT_ID</h6>
                                    <div class="form-group"><label class="small font-weight-bold">Station Code</label><select name="line_code" class="form-control rounded-pill border-0 shadow-sm" required>@foreach($availableLines as $line)<option value="{{ $line->kode_line }}" {{ $plan->line_code == $line->kode_line ? 'selected' : '' }}>{{ $line->kode_line }}</option>@endforeach</select></div>
                                    <div class="form-group"><label class="small font-weight-bold">Part No <span class="text-danger">*Readonly</span></label><input type="text" class="form-control rounded-pill border-0 shadow-sm bg-light" value="{{ $plan->part_no }}" disabled><input type="hidden" name="part_no" value="{{ $plan->part_no }}"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="tech-input-group h-100 border-warning">
                                    <h6 class="font-weight-black mb-3"><i class="fas fa-bolt mr-2 text-warning"></i> 02. CAP_METRICS</h6>
                                    <div class="row">
                                        <div class="col-6"><label class="small font-weight-bold">Manpower</label><input type="number" name="manpower" id="inp_mp_{{ $plan->id }}" class="form-control rounded-pill border-0 shadow-sm" value="{{ $plan->manpower }}" oninput="autoCalc('{{ $plan->id }}')"></div>
                                        <div class="col-6"><label class="small font-weight-bold">Cap/Hr</label><input type="number" name="cap_per_hour" id="inp_cap_{{ $plan->id }}" class="form-control rounded-pill border-0 shadow-sm" value="{{ $plan->cap_per_hour }}" oninput="autoCalc('{{ $plan->id }}')"></div>
                                    </div>
                                    <div class="mt-4 p-3 bg-white rounded-xl border text-center shadow-sm">
                                        <label class="small font-weight-black text-muted uppercase d-block">Expected Machine Load</label>
                                        @php $loadH = ($plan->cap_per_hour > 0 && ($plan->s1_plan_reg + $plan->s2_plan_reg) > 0) ? (($plan->s1_plan_reg + $plan->s2_plan_reg) / $plan->cap_per_hour) : 0; @endphp
                                        <h3 class="font-weight-black text-warning mb-0" id="display_hours_{{ $plan->id }}">{{ number_format($loadH, 1) }}H</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="tech-input-group h-100 border-warning">
                                    <h6 class="font-weight-black mb-3"><i class="fas fa-layer-group mr-2 text-info"></i> 03. QUANTITY_ALLOC</h6>
                                    <div class="form-group"><label class="small font-weight-bold text-primary">Shift 1 (Day) Target</label><input type="number" name="s1_plan_reg" id="inp_s1_{{ $plan->id }}" class="form-control rounded-pill border-0 shadow-sm" value="{{ $plan->s1_plan_reg }}" oninput="autoCalc('{{ $plan->id }}')"></div>
                                    <div class="form-group"><label class="small font-weight-bold text-danger">Shift 2 (Night) Target</label><input type="number" name="s2_plan_reg" id="inp_s2_{{ $plan->id }}" class="form-control rounded-pill border-0 shadow-sm" value="{{ $plan->s2_plan_reg }}" oninput="autoCalc('{{ $plan->id }}')"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-5 pt-0"><input type="hidden" name="plan_date" value="{{ $date }}"><button type="submit" class="btn btn-warning btn-block py-4 font-weight-black rounded-pill shadow-xl text-dark" style="font-size: 1.1rem; letter-spacing: 1px;">UPDATE SCHEDULE</button></div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

<script>
    function autoCalc(suffix) {
        let cap = parseFloat(document.getElementById('inp_cap_' + suffix).value) || 0;
        let s1 = parseFloat(document.getElementById('inp_s1_' + suffix).value) || 0;
        let s2 = parseFloat(document.getElementById('inp_s2_' + suffix).value) || 0;
        let total = s1 + s2;
        let hours = (cap > 0 && total > 0) ? (total / cap) : 0;
        document.getElementById('display_hours_' + suffix).innerText = hours.toFixed(1) + 'H';
    }

    // Filter cepat ALL / SHIFT_01 / SHIFT_02 tanpa reload halaman
    function filterShiftView(mode, btnEl) {
        document.querySelectorAll('.shift-toggle .shift-btn').forEach(b => b.classList.remove('active'));
        btnEl.classList.add('active');

        const kpiLabel = document.getElementById('kpiShiftLabel');
        if (mode === 'S1') kpiLabel.innerText = 'DAY_OPS';
        else if (mode === 'S2') kpiLabel.innerText = 'NIGHT_OPS';
        else kpiLabel.innerText = 'ALL_OPS';

        document.querySelectorAll('.station-card').forEach(card => {
            const hasS1 = card.getAttribute('data-has-s1') === '1';
            const hasS2 = card.getAttribute('data-has-s2') === '1';

            card.querySelectorAll('.shift-row-s1').forEach(r => r.style.display = (mode === 'ALL' || mode === 'S1') ? '' : 'none');
            card.querySelectorAll('.shift-row-s2').forEach(r => r.style.display = (mode === 'ALL' || mode === 'S2') ? '' : 'none');

            if (mode === 'S1' && !hasS1) card.style.display = 'none';
            else if (mode === 'S2' && !hasS2) card.style.display = 'none';
            else card.style.display = '';
        });

        // Sembunyikan header area jika semua station di dalamnya sedang tersembunyi
        document.querySelectorAll('.area-wrapper').forEach(area => {
            const visibleCards = Array.from(area.querySelectorAll('.station-card')).some(c => c.style.display !== 'none');
            area.style.display = visibleCards ? '' : 'none';
        });
    }
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let currentWipStock = 0;
        let isBomPart = false;
        let bomDetails = [];

        const modalReg = $('#modalRegisterPlan');
        let submitBtn = modalReg.find('button:contains("EXECUTE PRODUCTION PLAN")');
        if (submitBtn.length === 0) {
            submitBtn = modalReg.find('button[type="submit"]');
        }

        if (modalReg.find('#wipValidationMsg').length === 0) {
            submitBtn.parent().prepend('<div id="wipValidationMsg" class="w-100 text-center mb-3" style="font-size:12px; transition: 0.3s;"></div>');
        }

        function validateWipStock() {
            let s1 = parseInt($('#inp_s1_new').val()) || 0;
            let s2 = parseInt($('#inp_s2_new').val()) || 0;
            let totalTarget = s1 + s2;
            let msgBox = $('#wipValidationMsg');

            // === JIKA PART PUNYA BOM (MULTI-KOMPONEN WIP & RM) ===
            if (isBomPart && bomDetails.length > 0) {
                let allSafe = true;
                let rowsHtml = '';

                bomDetails.forEach(function(comp) {
                    let needQty = totalTarget * comp.qty_per_unit;
                    let isEnough = comp.available_stock >= needQty;
                    if (totalTarget > 0 && !isEnough) {
                        allSafe = false;
                    }

                    let badgeSource = comp.source_type === 'WIP'
                        ? '<span class="badge badge-primary px-2">WIP</span>'
                        : '<span class="badge badge-warning px-2">RM</span>';

                    let statusBadge = totalTarget === 0
                        ? '<span class="badge badge-secondary">STANDBY</span>'
                        : (isEnough
                            ? '<span class="badge badge-success">✅ AMAN</span>'
                            : '<span class="badge badge-danger">❌ KURANG</span>');

                    rowsHtml += `
                        <tr>
                            <td class="text-left"><b>${comp.component_code}</b><br><small class="text-muted">${comp.component_name}</small></td>
                            <td>${badgeSource}</td>
                            <td>x${comp.qty_per_unit}</td>
                            <td><b>${needQty.toLocaleString('id-ID')}</b> Pcs</td>
                            <td><b class="${(totalTarget > 0 && !isEnough) ? 'text-danger' : 'text-success'}">${comp.available_stock.toLocaleString('id-ID')}</b> Pcs</td>
                            <td>${statusBadge}</td>
                        </tr>
                    `;
                });

                let alertClass = totalTarget === 0
                    ? 'alert-light border'
                    : (allSafe ? 'alert-success' : 'alert-danger');

                let headerTitle = totalTarget === 0
                    ? 'ℹ️ <b>RINCIAN STOK KOMPONEN BOM</b> (Input target Shift 1 / Shift 2 untuk validasi)'
                    : (allSafe
                        ? `✅ <b>SEMUA KOMPONEN BOM AMAN!</b> (Target: ${totalTarget.toLocaleString('id-ID')} Pcs)`
                        : `❌ <b>PLAN DITOLAK: ADA KOMPONEN BOM YANG KURANG!</b> (Target: ${totalTarget.toLocaleString('id-ID')} Pcs)`);

                msgBox.html(`
                    <div class="alert ${alertClass} p-3 m-0 shadow-sm text-left" style="border-radius:12px;">
                        <div class="text-center mb-2">${headerTitle}</div>
                        <div class="table-responsive bg-white rounded border">
                            <table class="table table-sm mb-0 text-center" style="font-size:11.5px;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-left">Komponen</th>
                                        <th>Sumber</th>
                                        <th>Rasio</th>
                                        <th>Butuh</th>
                                        <th>Tersedia</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>${rowsHtml}</tbody>
                            </table>
                        </div>
                    </div>
                `);

                if (totalTarget === 0 || !allSafe) {
                    submitBtn.prop('disabled', true).removeClass('btn-primary').addClass('btn-secondary');
                } else {
                    submitBtn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary');
                }
                return;
            }

            // === JIKA PART WELDING BIASA (1:1 TANPA BOM) ===
            if (totalTarget === 0) {
                msgBox.html('<span class="text-muted font-weight-bold"><i class="fas fa-info-circle"></i> Input target qty untuk cek ketersediaan WIP Stamping. (Tersedia: ' + currentWipStock + ' Pcs)</span>');
                submitBtn.prop('disabled', true).removeClass('btn-primary').addClass('btn-secondary');
                return;
            }

            if (totalTarget > currentWipStock) {
                msgBox.html(`
                    <div class="alert alert-danger p-2 m-0 border-0 shadow-sm animate__animated animate__headShake" style="border-radius:10px;">
                        <b>❌ STOK WIP WELDING TIDAK CUKUP!</b><br>
                        Butuh: <b style="font-size:14px;">${totalTarget}</b> Pcs | Tersedia: <b>${currentWipStock}</b> Pcs
                    </div>
                `);
                submitBtn.prop('disabled', true).removeClass('btn-primary').addClass('btn-secondary');
            } else {
                msgBox.html(`
                    <div class="alert alert-success p-2 m-0 border-0 shadow-sm" style="border-radius:10px; background-color: #d1fae5; color: #065f46;">
                        <b>✅ STOK WIP AMAN!</b><br>
                        Butuh: <b>${totalTarget}</b> Pcs | Tersedia: <b>${currentWipStock}</b> Pcs
                    </div>
                `);
                submitBtn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary');
            }
        }

        function fetchStock(partNo) {
            if (!partNo) return;
            $('#wipValidationMsg').html('<span class="text-warning font-weight-bold"><i class="fas fa-spinner fa-spin"></i> Mengecek stok WIP & RM di Gudang...</span>');
            submitBtn.prop('disabled', true);

            $.get('/planning/ppic/check-wip-stock/' + encodeURIComponent(partNo), function(res) {
                if (res.status === 'success') {
                    currentWipStock = res.wip_stock;
                    isBomPart = res.is_bom === true;
                    bomDetails = res.bom_details || [];
                    validateWipStock();
                }
            }).fail(function() {
                $('#wipValidationMsg').html('<span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle"></i> Gagal terhubung ke database.</span>');
            });
        }

        // Trigger saat ganti part di modal Register
        modalReg.find('select[name="part_no"]').on('change', function() {
            fetchStock($(this).val());
        });

        // Otomatis cek stok saat modal Register baru dibuka
        modalReg.on('shown.bs.modal', function() {
            let selectedPart = modalReg.find('select[name="part_no"]').val();
            if (selectedPart) fetchStock(selectedPart);
        });

        // Trigger saat ketik target Shift 1 / Shift 2 di modal Register
        $('#inp_s1_new, #inp_s2_new').on('input', function() {
            validateWipStock();
        });
    });
</script>
@endsection