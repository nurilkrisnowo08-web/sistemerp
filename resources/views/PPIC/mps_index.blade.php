@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root { --excel-border: #000000; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
    
    .table-mps { border: 2px solid var(--excel-border); background: white; width: 100%; border-collapse: collapse; }
    .table-mps th { 
        border: 1px solid var(--excel-border); padding: 8px; font-size: 9px; font-weight: 800; 
        background-color: #d1d5db; color: #1e293b; vertical-align: middle; text-transform: uppercase;
    }
    .table-mps td { border: 1px solid var(--excel-border); padding: 6px; font-size: 11px; font-weight: 700; vertical-align: middle; }

    /* Footer Hijau ala Excel Bapak */
    .tfoot-total { background-color: #92d050 !important; color: black !important; font-weight: 900; }
    
    .hour-indicator { padding: 2px 6px; border-radius: 4px; font-size: 10px; font-family: 'JetBrains Mono', monospace; }
    .hour-safe { background: #dcfce7; color: #15803d; } 
    .hour-danger { background: #fee2e2; color: #b91c1c; } 
    
    .input-industrial { border: 2px solid #cbd5e1; border-radius: 8px; font-weight: 700; height: 40px; font-size: 13px; }
</style>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="font-weight-extrabold mb-0" style="letter-spacing: -1px;">MASTER PRODUCTION SCHEDULE (MPS)</h3>
            <p class="text-muted small font-weight-bold uppercase mb-0">TGL: {{ date('d F Y', strtotime($date)) }}</p>
        </div>
        
        <div class="d-flex gap-2">
            <form action="{{ route('ppic.mps.index') }}" method="GET" class="d-flex mr-2">
                <input type="date" name="date" class="form-control rounded-pill border-dark px-4 shadow-sm" value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <button class="btn btn-primary px-4 shadow font-weight-bold" data-toggle="modal" data-target="#modalAddPlan" style="border-radius: 10px;">
                <i class="fas fa-plus mr-2"></i> REGISTER NEW PLAN
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table-mps text-center">
                <thead>
                    <tr>
                        <th rowspan="2">NO</th>
                        <th rowspan="2" style="min-width: 220px;">PART NUMBER / IDENTIFICATION</th>
                        <th rowspan="2">CUST</th>
                        <th rowspan="2">MENPO WER</th>
                        <th rowspan="2">PRO CESS</th>
                        <th rowspan="2">QTY/LOT PRODUKSI</th>
                        <th rowspan="2">CAP/ HOUR</th>
                        <th rowspan="2">DANDORY</th>
                        <th colspan="2" class="bg-warning text-dark">PLAN HOURS</th>
                        <th colspan="3" class="bg-primary text-white">SHIFT 1 (OPERATIONAL)</th>
                        <th rowspan="2">REMARK</th>
                    </tr>
                    <tr>
                        <th class="bg-light">START</th>
                        <th class="bg-light">AHIR</th>
                        <th class="bg-light text-dark">PLAN PRODUKSI</th>
                        <th class="bg-light text-dark">ACTUAL</th>
                        <th class="bg-light text-danger">BALANCE</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totalHoursS1 = 0;
                        $runningFinishTime = "07:30"; // Waktu mulai shift 1
                    @endphp
                    @forelse($plans as $index => $p)
                    @php
                        $s1_target = $p->s1_plan_reg + $p->s1_plan_ot;
                        $dandoryHour = ($p->dandory_time ?? 0) / 60;
                        $s1_hour = ($p->cap_per_hour > 0 && $s1_target > 0) ? round(($s1_target / $p->cap_per_hour) + $dandoryHour, 1) : 0;
                        
                        $totalHoursS1 += $s1_hour;

                        // Logika Jam START & AHIR
                        $startTime = $runningFinishTime;
                        $durationMinutes = $s1_hour * 60;
                        $finishTime = date('H:i', strtotime($startTime . " + " . round($durationMinutes) . " minutes"));
                        $runningFinishTime = $finishTime;
                    @endphp
                    <tr>
                        <td class="bg-light small">{{ $index + 1 }}</td>
                        <td class="text-left pl-3 font-weight-bold">{{ $p->part_no }}</td>
                        <td>{{ $p->customer_code }}</td>
                        <td>{{ $p->manpower }}</td>
                        <td>{{ $p->process_qty ?? 4 }}</td>
                        <td>{{ $p->qty_lot ?? 200 }}</td>
                        <td class="bg-light font-mono">{{ $p->cap_per_hour }}</td>
                        <td class="text-muted">{{ $p->dandory_time }}m</td>

                        {{-- PLAN HOURS --}}
                        <td class="bg-yellow-light">{{ $startTime }}</td>
                        <td class="bg-yellow-light">{{ $finishTime }}</td>

                        {{-- SHIFT 1 --}}
                        <td class="bg-light font-weight-bold">{{ number_format($s1_target) }}</td>
                        <td class="text-primary font-weight-bold">{{ number_format($p->s1_actual) }}</td>
                        <td class="text-danger font-weight-bold">{{ number_format($s1_target - $p->s1_actual) }}</td>

                        <td class="small text-muted">{{ $p->remark ?? '-' }}</td>
                    </tr>
                    @empty
                        <tr><td colspan="15" class="py-5 text-muted bg-white font-weight-bold">NO PLAN REGISTERED FOR THIS DATE</td></tr>
                    @endforelse
                </tbody>
                @if(count($plans) > 0)
                <tfoot>
                    <tr class="tfoot-total">
                        <td colspan="8" class="text-right px-4">TOTAL WORKING HOURS / SHIFT 1</td>
                        <td colspan="2" class="text-center">{{ $totalHoursS1 }} HOURS</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- MODAL REGISTER - DENGAN KOLOM LENGKAP EXCEL --}}
<div class="modal fade" id="modalAddPlan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content rounded-24 shadow-lg border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i> REGISTER PRODUCTION TARGET</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('ppic.mps.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    {{-- Row 1: Registry --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="small font-weight-bold">LINE SELECTION</label>
                            <select name="line_code" class="form-control input-industrial" required>
                                @foreach($availableLines as $l)
                                    <option value="{{ $l->kode_Line }}">{{ $l->kode_Line }} - {{ $l->nama_Line }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold">CLIENT/CUSTOMER</label>
                            <select name="customer_code" id="select_customer" class="form-control input-industrial" required>
                                <option value="">-- SELECT --</option>
                                @foreach($availableCustomers as $c)
                                    <option value="{{ $c->code }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">PART NUMBER</label>
                            <select name="part_no" id="select_part" class="form-control input-industrial" required>
                                <option value="">-- SELECT CUSTOMER FIRST --</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold text-primary">CAP/ HOUR</label>
                            <input type="number" name="cap_per_hour" id="input_cap" class="form-control input-industrial text-center" placeholder="0" required>
                        </div>
                    </div>

                    {{-- Row 2: Excel Specific Details --}}
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <label class="small font-weight-bold">MENPOWER</label>
                            <input type="number" name="manpower" class="form-control input-industrial text-center" value="8">
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold">PRO CESS</label>
                            <input type="number" name="process_qty" class="form-control input-industrial text-center" value="4">
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold">QTY/LOT PROD</label>
                            <input type="number" name="qty_lot" class="form-control input-industrial text-center" value="200">
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold">DANDORY (MIN)</label>
                            <input type="number" name="dandory_time" id="input_dandory" class="form-control input-industrial text-center" value="15">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div id="live_status_box" class="alert alert-info w-100 mb-0 py-2 text-center font-weight-bold" style="border-radius: 8px;">
                                ESTIMATED LOAD: <span id="s1_live_hour">0.0</span> HOURS
                            </div>
                        </div>
                    </div>

                    {{-- Row 3: Target --}}
                    <div class="card p-4 border-primary" style="border-width: 2px; border-radius: 15px;">
                        <h6 class="font-weight-bold text-primary mb-3">PLAN PRODUKSI (QTY)</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="font-weight-bold text-muted">SHIFT 1 - REGULER</small>
                                <input type="number" name="s1_plan_reg" id="s1_reg" class="form-control input-industrial calc-trigger" value="0">
                            </div>
                            <div class="col-md-6">
                                <small class="font-weight-bold text-muted">SHIFT 1 - OVERTIME</small>
                                <input type="number" name="s1_plan_ot" id="s1_ot" class="form-control input-industrial calc-trigger" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <textarea name="remark" class="form-control input-industrial" rows="2" placeholder="Remark..."></textarea>
                        <input type="hidden" name="plan_date" value="{{ $date }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold shadow-lg" style="border-radius: 12px;">
                        <i class="fas fa-check-circle mr-2"></i> AUTHORIZE & SAVE MASTER SCHEDULE
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // FUNGSI HITUNG JAM LIVE SAAT INPUT
    function calculateLiveHours() {
        const cap = parseFloat(document.getElementById('input_cap').value) || 0;
        const dandory = parseFloat(document.getElementById('input_dandory').value) || 0;
        
        const s1Total = (parseFloat(document.getElementById('s1_reg').value) || 0) + (parseFloat(document.getElementById('s1_ot').value) || 0);
        let s1Hours = cap > 0 ? (s1Total / cap) + (dandory / 60) : 0;
        if(s1Total === 0) s1Hours = 0;
        
        const display = document.getElementById('s1_live_hour');
        const box = document.getElementById('live_status_box');
        display.innerText = s1Hours.toFixed(1);

        // Warnai box kalau overload > 8 jam
        if(s1Hours > 8) {
            box.className = "alert alert-danger w-100 mb-0 py-2 text-center font-weight-bold";
        } else {
            box.className = "alert alert-info w-100 mb-0 py-2 text-center font-weight-bold";
        }
    }

    document.querySelectorAll('.calc-trigger, #input_cap, #input_dandory').forEach(input => {
        input.addEventListener('input', calculateLiveHours);
    });

    // AJAX SYNC PART
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