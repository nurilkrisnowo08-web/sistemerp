@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root { --excel-border: #000000; --shift1-bg: #f8fafc; --shift2-bg: #f1f5f9; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    
    /* Excel Style Table */
    .table-mps { border: 2px solid var(--excel-border); background: white; width: 100%; border-collapse: collapse; }
    .table-mps th { 
        border: 1px solid var(--excel-border); padding: 10px; font-size: 10px; font-weight: 800; 
        background-color: #e2e8f0; color: #1e293b; vertical-align: middle;
    }
    .table-mps td { border: 1px solid var(--excel-border); padding: 8px; font-size: 11px; font-weight: 700; vertical-align: middle; }

    /* Color Indicators for Load */
    .hour-indicator { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; font-family: 'JetBrains Mono', monospace; }
    .hour-safe { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; } 
    .hour-warning { background: #fef9c3; color: #a16207; border: 1px solid #fef08a; } 
    .hour-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; } 

    .input-industrial { border: 2px solid #cbd5e1; border-radius: 10px; font-weight: 700; height: 45px; }
    .input-industrial:focus { border-color: #4e73df; box-shadow: none; }
</style>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-weight-extrabold mb-0" style="letter-spacing: -1px; color: #0f172a;">PRODUCTION PLANNING (MPS)</h2>
            <p class="text-muted small font-weight-bold uppercase">Industrial Master Schedule & Machine Load Control</p>
        </div>
        
        <div class="d-flex gap-2">
            <form action="{{ route('ppic.mps.index') }}" method="GET" class="d-flex mr-2">
                <input type="date" name="date" class="form-control rounded-pill border-dark px-4" value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <button class="btn btn-primary px-4 shadow-lg font-weight-bold" data-toggle="modal" data-target="#modalAddPlan" style="border-radius: 12px;">
                <i class="fas fa-plus-circle mr-2"></i> REGISTER NEW PLAN
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-24 overflow-hidden">
        <div class="table-responsive">
            <table class="table-mps text-center">
                <thead>
                    <tr>
                        <th rowspan="2">NO</th>
                        <th rowspan="2" style="min-width: 250px;">PART NAME / IDENTIFICATION</th>
                        <th rowspan="2">M/C</th>
                        <th rowspan="2">M/P</th>
                        <th rowspan="2">CAP/H</th>
                        <th rowspan="2">DANDORY</th>
                        <th colspan="4" class="bg-primary text-white">SHIFT 1 (DAY)</th>
                        <th colspan="4" class="bg-dark text-white">SHIFT 2 (NIGHT)</th>
                        <th rowspan="2">REMARK</th>
                    </tr>
                    <tr>
                        <th class="bg-light">PLAN</th>
                        <th class="bg-light">M/C HR</th>
                        <th class="bg-light">ACT</th>
                        <th class="bg-light">BAL</th>
                        <th class="bg-light">PLAN</th>
                        <th class="bg-light">M/C HR</th>
                        <th class="bg-light">ACT</th>
                        <th class="bg-light">BAL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $index => $p)
                    <tr>
                        <td class="bg-light">{{ $index + 1 }}</td>
                        <td class="text-left pl-3">{{ $p->part_no }}</td>
                        <td><span class="badge badge-dark px-2">{{ $p->line_code }}</span></td>
                        <td>{{ $p->manpower }}</td>
                        <td class="bg-light font-mono">{{ $p->cap_per_hour }}</td>
                        <td class="text-muted">{{ $p->dandory_time }}m</td>

                        {{-- SHIFT 1 --}}
                        <td class="font-weight-bold text-dark">{{ number_format($p->s1_total_target) }}</td>
                        <td>
                            <span class="hour-indicator {{ $p->s1_hour > 8 ? 'hour-danger' : ($p->s1_hour >= 7 ? 'hour-warning' : 'hour-safe') }}">
                                {{ $p->s1_hour }}h
                            </span>
                        </td>
                        <td class="text-primary">{{ number_format($p->s1_actual) }}</td>
                        <td class="{{ $p->s1_balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($p->s1_balance) }}</td>

                        {{-- SHIFT 2 --}}
                        <td class="font-weight-bold text-dark">{{ number_format($p->s2_total_target) }}</td>
                        <td>
                            <span class="hour-indicator {{ $p->s2_hour > 8 ? 'hour-danger' : ($p->s2_hour >= 7 ? 'hour-warning' : 'hour-safe') }}">
                                {{ $p->s2_hour }}h
                            </span>
                        </td>
                        <td class="text-primary">{{ number_format($p->s2_actual) }}</td>
                        <td class="{{ $p->s2_balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($p->s2_balance) }}</td>
                        
                        <td class="small text-muted">{{ $p->remark ?? '-' }}</td>
                    </tr>
                    @empty
                        <tr><td colspan="15" class="py-5 text-muted">-- No production plan registered --</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL INPUT PLAN - LEBIH FLEKSIBEL & DETAIL --}}
<div class="modal fade" id="modalAddPlan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content" style="border-radius: 25px; overflow: hidden;">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-microchip mr-2"></i> MASTER PRODUCTION SCHEDULER</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('ppic.mps.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-white">
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

                    <div class="row">
                        <div class="col-md-3">
                            <div class="card border-0 bg-light p-3" style="border-radius: 15px;">
                                <label class="small font-weight-bold text-uppercase">Dandory (Minutes)</label>
                                <input type="number" name="dandory_time" id="input_dandory" class="form-control input-industrial text-center" value="15">
                                <small class="text-muted mt-1 italic">* Setup time per part</small>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="alert alert-info border-0 mb-0" style="border-radius: 15px;">
                                <i class="fas fa-info-circle mr-2"></i> <strong>Workload Logic:</strong> MC Hours are calculated as <code>(Total Target / Capacity) + (Dandory / 60)</code>. Keep shift load under <strong>8.0 Hours</strong> for stability.
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        {{-- SHIFT 1 --}}
                        <div class="col-md-6">
                            <div class="card p-3 border-primary" style="border-width: 2px; border-radius: 20px;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="font-weight-bold text-primary m-0"><i class="fas fa-sun mr-1"></i> SHIFT 1 (DAY)</h6>
                                    <div id="s1_live_hour" class="hour-indicator hour-safe">0.0 HOURS</div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="font-weight-bold">REGULER PLAN</small>
                                        <input type="number" name="s1_plan_reg" id="s1_reg" class="form-control input-industrial calc-trigger" value="0">
                                    </div>
                                    <div class="col-6">
                                        <small class="font-weight-bold text-warning">OVERTIME (OT)</small>
                                        <input type="number" name="s1_plan_ot" id="s1_ot" class="form-control input-industrial calc-trigger" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SHIFT 2 --}}
                        <div class="col-md-6">
                            <div class="card p-3 border-dark" style="border-width: 2px; border-radius: 20px;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="font-weight-bold text-dark m-0"><i class="fas fa-moon mr-1"></i> SHIFT 2 (NIGHT)</h6>
                                    <div id="s2_live_hour" class="hour-indicator hour-safe">0.0 HOURS</div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="font-weight-bold">REGULER PLAN</small>
                                        <input type="number" name="s2_plan_reg" id="s2_reg" class="form-control input-industrial calc-trigger" value="0">
                                    </div>
                                    <div class="col-6">
                                        <small class="font-weight-bold text-warning">OVERTIME (OT)</small>
                                        <input type="number" name="s2_plan_ot" id="s2_ot" class="form-control input-industrial calc-trigger" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="small font-weight-bold">REMARK / PLANNING NOTE</label>
                        <textarea name="remark" class="form-control input-industrial" rows="2" placeholder="Ex: Machine maintenance, material delay..."></textarea>
                        <input type="hidden" name="plan_date" value="{{ $date }}">
                        <input type="hidden" name="manpower" value="1">
                    </div>
                </div>
                <div class="modal-footer bg-light p-4">
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold shadow-lg" style="border-radius: 15px; font-size: 16px;">
                        <i class="fas fa-check-circle mr-2"></i> AUTHORIZE & REGISTER PLAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // FUNGSI HITUNG JAM OTOMATIS (Sesuai Logika Excel)
    function calculateLiveHours() {
        const cap = parseFloat(document.getElementById('input_cap').value) || 0;
        const dandory = parseFloat(document.getElementById('input_dandory').value) || 0;
        const dandoryHour = dandory / 60;
        
        // Shift 1
        const s1Total = (parseFloat(document.getElementById('s1_reg').value) || 0) + (parseFloat(document.getElementById('s1_ot').value) || 0);
        let s1Hours = cap > 0 ? (s1Total / cap) + dandoryHour : 0;
        if(s1Total === 0) s1Hours = 0; // Jika plan 0, jam harus 0
        
        const s1Indicator = document.getElementById('s1_live_hour');
        s1Indicator.innerText = s1Hours.toFixed(1) + ' HOURS';
        updateIndicatorColor(s1Indicator, s1Hours);

        // Shift 2
        const s2Total = (parseFloat(document.getElementById('s2_reg').value) || 0) + (parseFloat(document.getElementById('s2_ot').value) || 0);
        let s2Hours = cap > 0 ? (s2Total / cap) + dandoryHour : 0;
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

    // Listener ke semua input angka
    document.querySelectorAll('.calc-trigger, #input_cap, #input_dandory').forEach(input => {
        input.addEventListener('input', calculateLiveHours);
    });

    // AJAX SINKRONISASI PART
    document.getElementById('select_customer').addEventListener('change', function() {
        const customer = this.value;
        const partSelect = document.getElementById('select_part');
        partSelect.innerHTML = '<option>SYNCING DATA...</option>';
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