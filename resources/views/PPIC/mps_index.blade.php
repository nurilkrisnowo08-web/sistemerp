@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root { --excel-border: #000000; --shift1-bg: #f8fafc; --shift2-bg: #f1f5f9; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    
    .table-mps { border: 2px solid var(--excel-border); background: white; width: 100%; border-collapse: collapse; }
    .table-mps th { 
        border: 1px solid var(--excel-border); padding: 10px; font-size: 10px; font-weight: 800; 
        background-color: #e2e8f0; color: #1e293b;
    }
    .table-mps td { border: 1px solid var(--excel-border); padding: 8px; font-size: 11px; font-weight: 700; }

    /* Indicator Jam Kerja */
    .hour-indicator { padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 800; }
    .hour-safe { background: #dcfce7; color: #15803d; } /* < 7 jam */
    .hour-warning { background: #fef9c3; color: #a16207; } /* 7-8 jam */
    .hour-danger { background: #fee2e2; color: #b91c1c; } /* > 8 jam */

    .modal-industrial { border-radius: 20px; overflow: hidden; border: none; }
    .input-cyber { border: 2px solid #e2e8f0; border-radius: 12px; font-weight: 700; transition: 0.3s; }
    .input-cyber:focus { border-color: #4e73df; box-shadow: none; }
</style>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-weight-extrabold mb-0" style="letter-spacing: -1px; color: #0f172a;">PRODUCTION PLANNING (MPS)</h2>
            <p class="text-muted small font-weight-bold">Industrial Master Schedule & Workload Control</p>
        </div>
        
        <div class="d-flex gap-2">
            <form action="{{ route('ppic.mps.index') }}" method="GET" class="d-flex mr-2">
                <input type="date" name="date" class="form-control rounded-pill border-dark px-4" value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <button class="btn btn-primary btn-excel-style px-4 shadow-lg" data-toggle="modal" data-target="#modalAddPlan" style="border-radius: 12px; font-weight: 800;">
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
                        <th colspan="4" class="bg-primary text-white">SHIFT 1 (PAGI)</th>
                        <th colspan="4" class="bg-dark text-white">SHIFT 2 (MALAM)</th>
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
                        <td>{{ $index + 1 }}</td>
                        <td class="text-left pl-3">{{ $p->part_no }}</td>
                        <td><span class="badge badge-dark">{{ $p->line_code }}</span></td>
                        <td>{{ $p->manpower }}</td>
                        <td class="bg-light font-mono">{{ $p->cap_per_hour }}</td>

                        {{-- SHIFT 1 --}}
                        <td class="font-weight-bold">{{ $p->s1_total_target }}</td>
                        <td>
                            <span class="hour-indicator {{ $p->s1_hour > 8 ? 'hour-danger' : ($p->s1_hour >= 7 ? 'hour-warning' : 'hour-safe') }}">
                                {{ $p->s1_hour }}h
                            </span>
                        </td>
                        <td class="text-primary">{{ $p->s1_actual }}</td>
                        <td class="{{ $p->s1_balance > 0 ? 'text-danger' : 'text-success' }}">{{ $p->s1_balance }}</td>

                        {{-- SHIFT 2 --}}
                        <td class="font-weight-bold">{{ $p->s2_total_target }}</td>
                        <td>
                            <span class="hour-indicator {{ $p->s2_hour > 8 ? 'hour-danger' : ($p->s2_hour >= 7 ? 'hour-warning' : 'hour-safe') }}">
                                {{ $p->s2_hour }}h
                            </span>
                        </td>
                        <td class="text-primary">{{ $p->s2_actual }}</td>
                        <td class="{{ $p->s2_balance > 0 ? 'text-danger' : 'text-success' }}">{{ $p->s2_balance }}</td>
                        
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

{{-- MODAL INPUT PLAN - LEBIH FLEKSIBEL --}}
<div class="modal fade" id="modalAddPlan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content modal-industrial">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title font-weight-bold">MASTER PRODUCTION SCHEDULER</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('ppic.mps.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-white">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="small font-weight-bold">LINE SELECTION</label>
                            <select name="line_code" class="form-control input-cyber" required>
                                @foreach($availableLines as $l)
                                    <option value="{{ $l->kode_Line }}">{{ $l->kode_Line }} - {{ $l->nama_Line }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold">CUSTOMER</label>
                            <select name="customer_code" id="select_customer" class="form-control input-cyber" required>
                                <option value="">-- SELECT --</option>
                                @foreach($availableCustomers as $c)
                                    <option value="{{ $c->code }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold">PART NUMBER</label>
                            <select name="part_no" id="select_part" class="form-control input-cyber" required>
                                <option value="">-- SELECT CUSTOMER FIRST --</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold">CAP / HOUR</label>
                            <input type="number" name="cap_per_hour" id="input_cap" class="form-control input-cyber text-center" placeholder="0" required>
                        </div>
                    </div>

                    <div class="row mt-4">
                        {{-- INPUT SHIFT 1 --}}
                        <div class="col-md-6">
                            <div class="card border-primary p-3" style="border-width: 2px; border-radius: 15px;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="font-weight-bold text-primary m-0">SHIFT 1 (DAY)</h6>
                                    <div id="s1_live_hour" class="hour-indicator hour-safe">0.0 HOURS</div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="font-weight-bold">REGULER PLAN</small>
                                        <input type="number" name="s1_plan_reg" id="s1_reg" class="form-control input-cyber calc-trigger" value="0">
                                    </div>
                                    <div class="col-6">
                                        <small class="font-weight-bold text-warning">OVERTIME (OT)</small>
                                        <input type="number" name="s1_plan_ot" id="s1_ot" class="form-control input-cyber calc-trigger" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- INPUT SHIFT 2 --}}
                        <div class="col-md-6">
                            <div class="card border-dark p-3" style="border-width: 2px; border-radius: 15px;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="font-weight-bold text-dark m-0">SHIFT 2 (NIGHT)</h6>
                                    <div id="s2_live_hour" class="hour-indicator hour-safe">0.0 HOURS</div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="font-weight-bold">REGULER PLAN</small>
                                        <input type="number" name="s2_plan_reg" id="s2_reg" class="form-control input-cyber calc-trigger" value="0">
                                    </div>
                                    <div class="col-6">
                                        <small class="font-weight-bold text-warning">OVERTIME (OT)</small>
                                        <input type="number" name="s2_plan_ot" id="s2_ot" class="form-control input-cyber calc-trigger" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <textarea name="remark" class="form-control input-cyber" rows="2" placeholder="Input remark here..."></textarea>
                        <input type="hidden" name="plan_date" value="{{ $date }}">
                        <input type="hidden" name="manpower" value="1">
                    </div>
                </div>
                <div class="modal-footer bg-light p-4">
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold shadow-lg" style="border-radius: 15px;">
                        <i class="fas fa-save mr-2"></i> SAVE PRODUCTION JADWAL
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
        
        // Shift 1
        const s1Reg = parseFloat(document.getElementById('s1_reg').value) || 0;
        const s1Ot = parseFloat(document.getElementById('s1_ot').value) || 0;
        const s1Total = s1Reg + s1Ot;
        const s1Hours = cap > 0 ? (s1Total / cap).toFixed(1) : 0;
        
        const s1Indicator = document.getElementById('s1_live_hour');
        s1Indicator.innerText = s1Hours + ' HOURS';
        updateIndicatorColor(s1Indicator, s1Hours);

        // Shift 2
        const s2Reg = parseFloat(document.getElementById('s2_reg').value) || 0;
        const s2Ot = parseFloat(document.getElementById('s2_ot').value) || 0;
        const s2Total = s2Reg + s2Ot;
        const s2Hours = cap > 0 ? (s2Total / cap).toFixed(1) : 0;

        const s2Indicator = document.getElementById('s2_live_hour');
        s2Indicator.innerText = s2Hours + ' HOURS';
        updateIndicatorColor(s2Indicator, s2Hours);
    }

    function updateIndicatorColor(el, hour) {
        el.classList.remove('hour-safe', 'hour-warning', 'hour-danger');
        if (hour > 8) el.classList.add('hour-danger');
        else if (hour >= 7) el.classList.add('hour-warning');
        else el.classList.add('hour-safe');
    }

    // Event Listener untuk semua input angka
    document.querySelectorAll('.calc-trigger, #input_cap').forEach(input => {
        input.addEventListener('input', calculateLiveHours);
    });

    // AJAX CUSTOMER PART (Tetap seperti sebelumnya)
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