@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root { --excel-border: #000000; --shift1-bg: #f8fafc; --shift2-bg: #f1f5f9; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
    
    /* ✨ ANIMASI MASUK ✨ */
    .fade-in-up { animation: fadeInUp 0.5s ease both; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    /* Excel Style Table */
    .table-mps { border: 2px solid var(--excel-border); background: white; width: 100%; border-collapse: collapse; }
    .table-mps th { 
        border: 1px solid var(--excel-border); padding: 10px; font-size: 10px; font-weight: 800; 
        background-color: #e2e8f0; color: #1e293b; vertical-align: middle;
    }
    .table-mps td { border: 1px solid var(--excel-border); padding: 8px; font-size: 11px; font-weight: 700; vertical-align: middle; }

    /* Footer Hijau ala Excel */
    .tfoot-total { background-color: #92d050 !important; color: black !important; font-weight: 900; font-size: 13px; }
    .tfoot-total td { border: 1px solid var(--excel-border) !important; }

    .hour-indicator { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; font-family: 'JetBrains Mono', monospace; }
    .hour-safe { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; } 
    .hour-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; } 
</style>

<div class="container-fluid mt-4 fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-weight-extrabold mb-0" style="letter-spacing: -1.5px; color: #0f172a;">PRODUCTION PLANNING (MPS)</h2>
            <p class="text-muted small font-weight-bold uppercase mb-0">Daily Master Schedule Control Center</p>
        </div>
        
        <div class="d-flex gap-2">
            <form action="{{ route('ppic.mps.index') }}" method="GET" class="d-flex mr-2">
                <input type="date" name="date" class="form-control rounded-pill border-dark px-4 shadow-sm" value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <button class="btn btn-primary px-4 shadow-lg font-weight-bold btn-hover" data-toggle="modal" data-target="#modalAddPlan" style="border-radius: 12px;">
                <i class="fas fa-plus-circle mr-2"></i> REGISTER NEW PLAN
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-lg rounded-24 overflow-hidden">
        <div class="table-responsive">
            <table class="table-mps text-center">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 40px;">NO</th>
                        <th rowspan="2" style="min-width: 250px;">PART NUMBER / IDENTIFICATION</th>
                        <th rowspan="2" style="width: 80px;">M/C</th>
                        <th rowspan="2" style="width: 70px;">CUST</th>
                        <th rowspan="2" style="width: 60px;">M/P</th>
                        <th rowspan="2" style="width: 80px;">CAP/ HOUR</th>
                        <th rowspan="2" style="width: 80px;">DANDORY</th>
                        <th colspan="4" class="bg-primary text-white">SHIFT 1 (DAY)</th>
                        <th colspan="4" class="bg-dark text-white">SHIFT 2 (NIGHT)</th>
                    </tr>
                    <tr>
                        <th class="bg-light">PLAN PRODUKSI</th>
                        <th class="bg-light">M/C HOURS</th>
                        <th class="bg-light">ACTUAL</th>
                        <th class="bg-light text-danger">BALANCE</th>
                        <th class="bg-light text-dark">PLAN PRODUKSI</th>
                        <th class="bg-light text-dark">M/C HOURS</th>
                        <th class="bg-light text-dark">ACTUAL</th>
                        <th class="bg-light text-danger">BALANCE</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totalHoursS1 = 0; 
                        $totalHoursS2 = 0; 
                    @endphp
                    @forelse($plans as $index => $p)
                    @php
                        $s1_target = $p->s1_plan_reg + $p->s1_plan_ot;
                        $s2_target = $p->s2_plan_reg + $p->s2_plan_ot;
                        $dandoryHour = $p->dandory_time / 60;
                        
                        $s1_hour = ($p->cap_per_hour > 0 && $s1_target > 0) ? round(($s1_target / $p->cap_per_hour) + $dandoryHour, 1) : 0;
                        $s2_hour = ($p->cap_per_hour > 0 && $s2_target > 0) ? round(($s2_target / $p->cap_per_hour) + $dandoryHour, 1) : 0;
                        
                        $totalHoursS1 += $s1_hour;
                        $totalHoursS2 += $s2_hour;
                    @endphp
                    <tr>
                        <td class="bg-light">{{ $index + 1 }}</td>
                        <td class="text-left pl-3 font-weight-bold">{{ $p->part_no }}</td>
                        <td><span class="badge badge-dark px-2">{{ $p->line_code }}</span></td>
                        <td>{{ $p->customer_code }}</td>
                        <td>{{ $p->manpower }}</td>
                        <td class="bg-light font-mono">{{ $p->cap_per_hour }}</td>
                        <td class="text-muted">{{ $p->dandory_time }}m</td>

                        {{-- SHIFT 1 --}}
                        <td class="bg-light font-weight-bold">{{ number_format($s1_target) }}</td>
                        <td><span class="hour-indicator {{ $s1_hour > 8 ? 'hour-danger' : 'hour-safe' }}">{{ $s1_hour }}h</span></td>
                        <td class="text-primary font-weight-bold">{{ number_format($p->s1_actual) }}</td>
                        <td class="text-danger font-weight-bold">{{ number_format($s1_target - $p->s1_actual) }}</td>

                        {{-- SHIFT 2 --}}
                        <td class="bg-light font-weight-bold">{{ number_format($s2_target) }}</td>
                        <td><span class="hour-indicator {{ $s2_hour > 8 ? 'hour-danger' : 'hour-safe' }}">{{ $s2_hour }}h</span></td>
                        <td class="text-primary font-weight-bold">{{ number_format($p->s2_actual) }}</td>
                        <td class="text-danger font-weight-bold">{{ number_format($s2_target - $p->s2_actual) }}</td>
                    </tr>
                    @empty
                        <tr><td colspan="15" class="py-5 text-muted bg-white">-- No production plan registered for this date --</td></tr>
                    @endforelse
                </tbody>
                {{-- ✨ BARIS TOTAL WORKING HOURS (HIJAU ALA EXCEL) --}}
                @if(count($plans) > 0)
                <tfoot>
                    <tr class="tfoot-total">
                        <td colspan="8" class="text-right px-4">TOTAL WORKING HOURS / SHIFT 1</td>
                        <td class="{{ $totalHoursS1 > 8 ? 'bg-danger text-white' : '' }}">{{ $totalHoursS1 }}h</td>
                        <td colspan="3" class="text-right px-4">TOTAL WORKING HOURS / SHIFT 2</td>
                        <td class="{{ $totalHoursS2 > 8 ? 'bg-danger text-white' : '' }}">{{ $totalHoursS2 }}h</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- MODAL INPUT --}}
<div class="modal fade" id="modalAddPlan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content" style="border-radius: 25px; overflow: hidden; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.3);">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-microchip mr-2"></i> MASTER PRODUCTION SCHEDULER</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('ppic.mps.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-white">
                    {{-- Part Info --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-uppercase">Line Selection</label>
                            <select name="line_code" class="form-control input-industrial" required>
                                @foreach($availableLines as $l)
                                    <option value="{{ $l->kode_Line }}">{{ $l->kode_Line }} - {{ $l->nama_Line }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-uppercase">Customer</label>
                            <select name="customer_code" id="select_customer" class="form-control input-industrial" required>
                                <option value="">-- SELECT --</option>
                                @foreach($availableCustomers as $c)
                                    <option value="{{ $c->code }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-uppercase">Part Number Identification</label>
                            <select name="part_no" id="select_part" class="form-control input-industrial" required>
                                <option value="">-- SELECT CUSTOMER FIRST --</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold text-uppercase text-primary">Cap / Hour</label>
                            <input type="number" name="cap_per_hour" id="input_cap" class="form-control input-industrial text-center font-weight-bold" placeholder="0" required>
                        </div>
                    </div>

                    {{-- Dandory --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 bg-light p-3" style="border-radius: 15px;">
                                <label class="small font-weight-bold text-uppercase">Dandory (Minutes)</label>
                                <input type="number" name="dandory_time" id="input_dandory" class="form-control input-industrial text-center" value="15">
                            </div>
                        </div>
                        <div class="col-md-9 d-flex align-items-center">
                            <div class="alert alert-warning border-0 w-100 mb-0 py-3" style="border-radius: 15px;">
                                <i class="fas fa-exclamation-triangle mr-2"></i> PPIC must ensure total hours per shift do not exceed <strong>8.0 Hours</strong> for production stability.
                            </div>
                        </div>
                    </div>

                    {{-- Target Input --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card p-4 border-primary shadow-sm" style="border-width: 2px; border-radius: 20px;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="font-weight-bold text-primary m-0">SHIFT 1 (DAY)</h6>
                                    <div id="s1_live_hour" class="hour-indicator hour-safe">0.0 HOURS</div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="font-weight-bold text-muted uppercase">Plan Reguler</small>
                                        <input type="number" name="s1_plan_reg" id="s1_reg" class="form-control input-industrial calc-trigger" value="0">
                                    </div>
                                    <div class="col-6">
                                        <small class="font-weight-bold text-warning uppercase">Plan Overtime</small>
                                        <input type="number" name="s1_plan_ot" id="s1_ot" class="form-control input-industrial calc-trigger" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card p-4 border-dark shadow-sm" style="border-width: 2px; border-radius: 20px;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="font-weight-bold text-dark m-0">SHIFT 2 (NIGHT)</h6>
                                    <div id="s2_live_hour" class="hour-indicator hour-safe">0.0 HOURS</div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="font-weight-bold text-muted uppercase">Plan Reguler</small>
                                        <input type="number" name="s2_plan_reg" id="s2_reg" class="form-control input-industrial calc-trigger" value="0">
                                    </div>
                                    <div class="col-6">
                                        <small class="font-weight-bold text-warning uppercase">Plan Overtime</small>
                                        <input type="number" name="s2_plan_ot" id="s2_ot" class="form-control input-industrial calc-trigger" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="small font-weight-bold">REMARK / PLANNING NOTE</label>
                        <textarea name="remark" class="form-control input-industrial" rows="2" placeholder="Input specific instructions..."></textarea>
                        <input type="hidden" name="plan_date" value="{{ $date }}">
                        <input type="hidden" name="manpower" value="1">
                    </div>
                </div>
                <div class="modal-footer bg-light p-4">
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold shadow-lg" style="border-radius: 15px; font-size: 16px;">
                        <i class="fas fa-check-circle mr-2"></i> AUTHORIZE PRODUCTION JADWAL
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // FUNGSI HITUNG JAM OTOMATIS (LIVE)
    function calculateLiveHours() {
        const cap = parseFloat(document.getElementById('input_cap').value) || 0;
        const dandory = parseFloat(document.getElementById('input_dandory').value) || 0;
        const d_hour = dandory / 60;
        
        // Shift 1
        const s1Reg = parseFloat(document.getElementById('s1_reg').value) || 0;
        const s1Ot = parseFloat(document.getElementById('s1_ot').value) || 0;
        const s1Total = s1Reg + s1Ot;
        let s1Hours = cap > 0 ? (s1Total / cap) + d_hour : 0;
        if(s1Total === 0) s1Hours = 0;
        
        const s1Indicator = document.getElementById('s1_live_hour');
        s1Indicator.innerText = s1Hours.toFixed(1) + ' HOURS';
        updateIndicatorColor(s1Indicator, s1Hours);

        // Shift 2
        const s2Reg = parseFloat(document.getElementById('s2_reg').value) || 0;
        const s2Ot = parseFloat(document.getElementById('s2_ot').value) || 0;
        const s2Total = s2Reg + s2Ot;
        let s2Hours = cap > 0 ? (s2Total / cap) + d_hour : 0;
        if(s2Total === 0) s2Hours = 0;

        const s2Indicator = document.getElementById('s2_live_hour');
        s2Indicator.innerText = s2Hours.toFixed(1) + ' HOURS';
        updateIndicatorColor(s2Indicator, s2Hours);
    }

    function updateIndicatorColor(el, hour) {
        el.classList.remove('hour-safe', 'hour-warning', 'hour-danger');
        if (hour > 8) el.classList.add('hour-danger');
        else if (hour >= 7) el.classList.add('hour-warning');
        else el.classList.add('hour-safe');
    }

    document.querySelectorAll('.calc-trigger, #input_cap, #input_dandory').forEach(input => {
        input.addEventListener('input', calculateLiveHours);
    });

    // AJAX CUSTOMER PART
    document.getElementById('select_customer').addEventListener('change', function() {
        const customer = this.value;
        const partSelect = document.getElementById('select_part');
        partSelect.innerHTML = '<option>SYNCING...</option>';
        fetch(`/get-parts-and-specs/${customer}`)
            .then(res => res.json())
            .then(data => {
                partSelect.innerHTML = '<option value="">-- SELECT PART --</option>';
                data.parts.forEach(p => {
                    partSelect.innerHTML += `<option value="${p.part_no}">${p.part_no} - ${p.part_name}</option>`;
                });
            });
    });
</script>
@endsection