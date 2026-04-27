@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root {
        --p-primary: #4361ee; --p-dark: #0f172a; --p-success: #10b981; 
        --p-danger: #ef4444; --p-warning: #f59e0b; --p-slate: #64748b;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: var(--p-dark); }

    /* Modern Card & Header */
    .glass-card { background: white; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); }
    .hud-title { font-family: 'Orbitron'; font-weight: 800; letter-spacing: -1px; }

    /* Navigation Rail / Shift Toggle */
    .shift-toggle-pill { background: #f1f5f9; padding: 5px; border-radius: 50px; display: inline-flex; gap: 5px; }
    .st-btn { border: none; padding: 8px 25px; border-radius: 50px; font-weight: 800; font-size: 11px; transition: 0.3s; color: var(--p-slate); text-decoration: none !important; }
    .st-btn.active { background: var(--p-primary); color: white; box-shadow: 0 4px 12px rgba(67, 97, 238, 0.4); }
    .st-btn-night.active { background: var(--p-dark); color: white; }

    /* Table HUD */
    .table-mps { width: 100%; border-collapse: separate; border-spacing: 0; }
    .table-mps thead th { 
        background: #f1f5f9; padding: 15px 10px; font-size: 10px; font-weight: 800; 
        text-transform: uppercase; letter-spacing: 1px; color: var(--p-slate); border-bottom: 2px solid #e2e8f0;
    }
    .table-mps tbody td { padding: 15px 10px; border-bottom: 1px solid #f1f5f9; font-size: 12px; font-weight: 600; vertical-align: middle; }
    .part-cell { border-left: 4px solid var(--p-primary); background: #f8faff; font-weight: 800 !important; color: var(--p-primary); }

    /* Badges */
    .badge-time { background: #fff9c4; color: #856404; font-family: 'JetBrains Mono'; padding: 5px 10px; border-radius: 6px; font-weight: 700; border: 1px solid #ffe58f; }
    .badge-balance { padding: 6px 12px; border-radius: 8px; font-family: 'Orbitron'; font-size: 11px; }
    .balance-danger { background: rgba(239, 68, 68, 0.1); color: var(--p-danger); border: 1px solid var(--p-danger); }
    .balance-success { background: rgba(16, 185, 129, 0.1); color: var(--p-success); border: 1px solid var(--p-success); }

    /* Footer Summary */
    .footer-summary { background: var(--p-success) !important; color: white !important; font-family: 'Orbitron'; font-weight: 800; }
    .footer-summary td { border: none !important; padding: 20px !important; }

    /* Animation */
    .anim-up { animation: fadeInUp 0.5s ease-out; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="container-fluid mt-4 mb-5 anim-up">
    {{-- 🛰️ HEADER & CONTROL SECTOR --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="hud-title m-0">MPS_TERMINAL <span class="text-primary">v5.0</span></h2>
            <p class="text-muted small font-weight-bold uppercase mb-0"><i class="fas fa-calendar-alt mr-2"></i> Operational Date: {{ date('d F Y', strtotime($date)) }}</p>
        </div>

        <div class="d-flex align-items-center gap-3">
            {{-- Shift Switcher --}}
            <div class="shift-toggle-pill shadow-sm mr-3">
                @php $currentShift = request('shift', 'S1'); @endphp
                <a href="?date={{ $date }}&shift=S1" class="st-btn {{ $currentShift == 'S1' ? 'active' : '' }}">SHIFT_01</a>
                <a href="?date={{ $date }}&shift=S2" class="st-btn st-btn-night {{ $currentShift == 'S2' ? 'active' : '' }}">SHIFT_02</a>
            </div>

            <form action="" method="GET" class="d-flex mr-2">
                <input type="hidden" name="shift" value="{{ $currentShift }}">
                <input type="date" name="date" class="form-control rounded-pill border-dark px-4 shadow-sm font-weight-bold" 
                       value="{{ $date }}" onchange="this.form.submit()">
            </form>
            
            <button class="btn btn-primary shadow font-weight-bold rounded-pill px-4" data-toggle="modal" data-target="#modalAddPlan">
                <i class="fas fa-plus-circle mr-2"></i> REGISTER PLAN
            </button>
        </div>
    </div>

    {{-- 📊 QUICK STATS --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="glass-card p-3 text-center border-left border-primary" style="border-left-width: 5px !important;">
                <small class="text-muted font-weight-bold uppercase">Total Target (Qty)</small>
                <h3 class="font-weight-bold mb-0 text-primary">{{ number_format($totalPlanQty) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-3 text-center border-left border-info" style="border-left-width: 5px !important;">
                <small class="text-muted font-weight-bold uppercase">Workload (Hours)</small>
                <h3 class="font-weight-bold mb-0 text-info">{{ round($totalWorkingHours, 1) }}h</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-3 text-center border-left border-warning" style="border-left-width: 5px !important;">
                <small class="text-muted font-weight-bold uppercase">Switch Loss (Dandory)</small>
                <h3 class="font-weight-bold mb-0 text-warning">{{ $totalDandory }}m</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card p-3 text-center bg-dark text-white">
                <small class="text-info font-weight-bold uppercase">Current Shift</small>
                <h3 class="font-weight-bold mb-0">{{ $currentShift == 'S1' ? 'DAY_OPS' : 'NIGHT_OPS' }}</h3>
            </div>
        </div>
    </div>

    {{-- 📋 MAIN DATA GRID --}}
    <div class="glass-card overflow-hidden">
        <div class="table-responsive">
            <table class="table-mps text-center">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-left pl-4">Identification</th>
                        <th>MP</th>
                        <th>Proc</th>
                        <th>Target</th>
                        <th>Actual</th>
                        <th>Balance</th>
                        <th class="text-primary">Start</th>
                        <th class="text-primary">Finish</th>
                        <th>Dandory</th>
                        <th>Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $index => $p)
                    @php 
                        $balanceStatus = ($p->balance > 0) ? 'balance-danger' : 'balance-success';
                        $balanceIcon = ($p->balance > 0) ? 'fa-exclamation-triangle' : 'fa-check-circle';
                    @endphp
                    <tr>
                        <td class="text-muted">{{ $index + 1 }}</td>
                        <td class="text-left pl-4 part-cell">{{ $p->part_no }} <br> <small class="text-muted">{{ $p->customer_code }}</small></td>
                        <td><span class="badge badge-light border">{{ $p->manpower }}P</span></td>
                        <td>{{ $p->process_qty }}</td>
                        <td class="font-weight-bold">{{ number_format($p->total_target) }}</td>
                        <td class="text-primary font-weight-bold">{{ number_format($p->total_actual) }}</td>
                        <td>
                            <span class="badge-balance {{ $balanceStatus }}">
                                <i class="fas {{ $balanceIcon }} mr-1"></i> {{ number_format($p->balance) }}
                            </span>
                        </td>
                        <td><span class="badge-time">{{ $p->start_time }}</span></td>
                        <td><span class="badge-time">{{ $p->ahir_time }}</span></td>
                        <td class="text-muted small">{{ $p->dandory_time }}m</td>
                        <td class="text-muted italic small text-left">{{ $p->remark ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="py-5 text-center text-muted font-weight-bold h5">-- NO PRODUCTION DATA FOR {{ $currentShift }} --</td></tr>
                    @endforelse
                </tbody>
                @if(count($plans) > 0)
                <tfoot>
                    <tr class="footer-summary">
                        <td colspan="4" class="text-right uppercase">Daily Summary Total :</td>
                        <td>{{ number_format($totalPlanQty) }}</td>
                        <td colspan="2" class="text-right">Total Est. Work :</td>
                        <td colspan="2" class="text-center">{{ round($totalWorkingHours, 1) }} HOURS</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- 🛠️ MODAL REGISTER - REVAMPED UI --}}
<div class="modal fade" id="modalAddPlan" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 25px; overflow: hidden;">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title hud-title"><i class="fas fa-plus-circle mr-2"></i> REGISTER_PLAN_SYSTEM</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('ppic.mps.store') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="row">
                        {{-- Left Side: Identity --}}
                        <div class="col-md-4 border-right">
                            <h6 class="font-weight-bold text-primary mb-3">01. PRODUCT_IDENTITY</h6>
                            <div class="form-group">
                                <label class="small font-weight-bold">Select Line</label>
                                <select name="line_code" class="form-control input-industrial" required>
                                    @foreach($availableLines as $l)
                                        <option value="{{ $l->kode_Line }}">{{ $l->kode_Line }} - {{ $l->nama_Line }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Customer</label>
                                <select name="customer_code" id="select_customer" class="form-control input-industrial" required>
                                    <option value="">-- CHOOSE --</option>
                                    @foreach($availableCustomers as $c)
                                        <option value="{{ $c->code }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Part Identification</label>
                                <select name="part_no" id="select_part" class="form-control input-industrial" required>
                                    <option value="">-- SELECT CUSTOMER FIRST --</option>
                                </select>
                            </div>
                        </div>

                        {{-- Middle Side: Numeric --}}
                        <div class="col-md-4 border-right px-4">
                            <h6 class="font-weight-bold text-info mb-3">02. PERFORMANCE_METRICS</h6>
                            <div class="row">
                                <div class="col-6"><label class="small font-weight-bold">Manpower</label><input type="number" name="manpower" class="form-control input-industrial mb-3" value="8"></div>
                                <div class="col-6"><label class="small font-weight-bold">Process</label><input type="number" name="process_qty" class="form-control input-industrial mb-3" value="4"></div>
                                <div class="col-6"><label class="small font-weight-bold text-primary">Cap/Hour</label><input type="number" name="cap_per_hour" id="input_cap" class="form-control input-industrial mb-3 font-weight-bold border-primary" value="320" required></div>
                                <div class="col-6"><label class="small font-weight-bold text-warning">Dandory (m)</label><input type="number" name="dandory_time" id="input_dandory" class="form-control input-industrial mb-3" value="15"></div>
                            </div>
                            <div class="alert alert-info border-0 rounded-lg text-center mt-3 py-3 shadow-sm">
                                <small class="d-block font-weight-bold uppercase mb-1">Calculated M/C Load</small>
                                <h4 class="hud-title mb-0" id="live_load_label">0.0H</h4>
                            </div>
                        </div>

                        {{-- Right Side: Planning --}}
                        <div class="col-md-4 pl-4">
                            <h6 class="font-weight-bold text-success mb-3">03. QUANTITY_PLANNING</h6>
                            <div class="shift-toggle-pill mb-3 w-100">
                                <button type="button" id="m_btn_s1" class="shift-btn active-s1 w-50" style="border:none; border-radius:50px; padding:10px;" onclick="modalShiftSwitch(1)">SHIFT_1</button>
                                <button type="button" id="m_btn_s2" class="shift-btn w-50" style="border:none; border-radius:50px; padding:10px; background:none;" onclick="modalShiftSwitch(2)">SHIFT_2</button>
                                <input type="hidden" name="active_shift_input" id="m_active_shift" value="1">
                            </div>

                            <div class="bg-light p-3 rounded-lg border">
                                <div id="m_box_s1">
                                    <label class="small font-weight-bold">S1 Reguler Plan</label>
                                    <input type="number" name="s1_plan_reg" id="s1_reg" class="form-control input-industrial mb-2 calc-trigger" value="0">
                                    <label class="small font-weight-bold text-warning">S1 Overtime Plan</label>
                                    <input type="number" name="s1_plan_ot" id="s1_ot" class="form-control input-industrial calc-trigger" value="0">
                                </div>
                                <div id="m_box_s2" style="display: none;">
                                    <label class="small font-weight-bold">S2 Reguler Plan</label>
                                    <input type="number" name="s2_plan_reg" id="s2_reg" class="form-control input-industrial mb-2 calc-trigger" value="0">
                                    <label class="small font-weight-bold text-warning">S2 Overtime Plan</label>
                                    <input type="number" name="s2_plan_ot" id="s2_ot" class="form-control input-industrial calc-trigger" value="0">
                                </div>
                            </div>
                            <div class="mt-3">
                                <input type="hidden" name="plan_date" value="{{ $date }}">
                                <textarea name="remark" class="form-control" rows="2" style="border-radius:12px;" placeholder="Optional remark..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">ABORT</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 font-weight-black shadow-lg">AUTHORIZE & DEPLOY</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function modalShiftSwitch(s) {
        const isS1 = s === 1;
        document.getElementById('m_btn_s1').className = isS1 ? 'shift-btn active-s1 w-50' : 'shift-btn w-50';
        document.getElementById('m_btn_s1').style.background = isS1 ? '#4361ee' : 'none';
        document.getElementById('m_btn_s1').style.color = isS1 ? 'white' : '#64748b';

        document.getElementById('m_btn_s2').className = !isS1 ? 'shift-btn active-s2 w-50' : 'shift-btn w-50';
        document.getElementById('m_btn_s2').style.background = !isS1 ? '#0f172a' : 'none';
        document.getElementById('m_btn_s2').style.color = !isS1 ? 'white' : '#64748b';

        document.getElementById('m_box_s1').style.display = isS1 ? 'block' : 'none';
        document.getElementById('m_box_s2').style.display = isS1 ? 'none' : 'block';
        document.getElementById('m_active_shift').value = s;
        
        // Reset values of the other shift to 0
        if(isS1) { 
            document.getElementById('s2_reg').value = 0; document.getElementById('s2_ot').value = 0; 
        } else { 
            document.getElementById('s1_reg').value = 0; document.getElementById('s1_ot').value = 0; 
        }
        calculateLiveHours();
    }

    function calculateLiveHours() {
        const cap = parseFloat(document.getElementById('input_cap').value) || 1;
        const dandory = parseFloat(document.getElementById('input_dandory').value) || 0;
        const s1 = (parseFloat(document.getElementById('s1_reg').value) || 0) + (parseFloat(document.getElementById('s1_ot').value) || 0);
        const s2 = (parseFloat(document.getElementById('s2_reg').value) || 0) + (parseFloat(document.getElementById('s2_ot').value) || 0);
        const totalQty = s1 + s2;
        
        let hours = totalQty > 0 ? (totalQty / cap) + (dandory / 60) : 0;
        
        const label = document.getElementById('live_load_label');
        label.innerText = hours.toFixed(1) + "H";
        label.parentElement.className = hours > 8 ? "alert alert-danger border-0 rounded-lg text-center mt-3 py-3 shadow-sm" : "alert alert-info border-0 rounded-lg text-center mt-3 py-3 shadow-sm";
    }

    document.querySelectorAll('.calc-trigger, #input_cap, #input_dandory').forEach(i => i.addEventListener('input', calculateLiveHours));

    document.getElementById('select_customer').addEventListener('change', function() {
        fetch(`/get-parts-and-specs/${this.value}`).then(r => r.json()).then(data => {
            let html = '<option value="">-- SELECT PART --</option>';
            data.parts.forEach(p => html += `<option value="${p.part_no}">${p.part_no} - ${p.part_name}</option>`);
            document.getElementById('select_part').innerHTML = html;
        });
    });
</script>
@endsection