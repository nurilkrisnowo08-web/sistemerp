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

    /* ✨ BARIS SUMMARY HIJAU (Sesuai image_c50334.png) */
    .tfoot-total { background-color: #92d050 !important; color: black !important; font-weight: 900; }
    .tfoot-total td { border: 1px solid var(--excel-border); }

    .input-industrial { border: 2px solid #cbd5e1; border-radius: 8px; font-weight: 700; height: 40px; }
    .bg-yellow-light { background-color: #fff9c4 !important; }
    .bg-blue-plan { background-color: #4472c4 !important; color: white !important; }
    .bg-warning-plan { background-color: #ffc000 !important; color: black !important; }
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
                        <th rowspan="2">QTY/LOT PROD</th>
                        <th rowspan="2">CAP/ HOUR</th>
                        <th rowspan="2">DANDORY</th>
                        <th colspan="2" class="bg-warning-plan">PLAN HOURS</th>
                        <th colspan="3" class="bg-blue-plan">PRODUCTION TARGETS</th>
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
                    @forelse($plans as $index => $p)
                    <tr>
                        <td class="bg-light small">{{ $index + 1 }}</td>
                        <td class="text-left pl-3 font-weight-bold">{{ $p->part_no }}</td>
                        <td>{{ $p->customer_code }}</td>
                        <td>{{ $p->manpower }}</td>
                        <td>{{ $p->process_qty ?? '-' }}</td>
                        <td>{{ $p->qty_lot ?? '-' }}</td>
                        <td class="bg-light font-mono">{{ $p->cap_per_hour }}</td>
                        <td>{{ $p->dandory_time }}m</td>

                        {{-- PLAN HOURS (Otomatis dari Controller) --}}
                        <td class="bg-yellow-light">{{ $p->start_time }}</td>
                        <td class="bg-yellow-light">{{ $p->ahir_time }}</td>

                        {{-- TARGETS (Total S1 + S2) --}}
                        <td class="bg-light font-weight-bold">{{ number_format($p->total_target) }}</td>
                        <td class="text-primary font-weight-bold">{{ number_format($p->total_actual) }}</td>
                        <td class="{{ ($p->total_target - $p->total_actual) > 0 ? 'text-danger' : 'text-success' }} font-weight-bold">
                            {{ number_format($p->total_target - $p->total_actual) }}
                        </td>

                        <td class="small text-muted">{{ $p->remark ?? '-' }}</td>
                    </tr>
                    @empty
                        <tr><td colspan="15" class="py-5 text-muted bg-white font-weight-bold uppercase text-center">No data found. Please set targets in Monthly Master Matrix.</td></tr>
                    @endforelse
                </tbody>
                
                {{-- ✨ FOOTER SUMMARY HIJAU (Summary Real-Time) ✨ --}}
                @if(count($plans) > 0)
                <tfoot>
                    <tr class="tfoot-total">
                        <td colspan="7" class="text-right px-4 uppercase">TOTAL WORKING HOURS / DAILY SUMMARY</td>
                        <td>{{ $totalDandory }}m</td>
                        <td colspan="2" class="text-center">{{ round($totalWorkingHours, 1) }} HOURS</td>
                        <td>{{ number_format($totalPlanQty) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- MODAL REGISTER --}}
<div class="modal fade" id="modalAddPlan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content rounded-24 shadow-lg border-0">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i> MASTER SCHEDULE REGISTRY</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('ppic.mps.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="small font-weight-bold uppercase">Line Selection</label>
                            <select name="line_code" class="form-control input-industrial" required>
                                @foreach($availableLines as $l)
                                    <option value="{{ $l->kode_Line }}">{{ $l->kode_Line }} - {{ $l->nama_Line }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-primary uppercase">Select Operational Shift</label>
                            <select id="shift_selector" class="form-control input-industrial bg-primary text-white font-weight-bold">
                                <option value="1">SHIFT 1 (DAY)</option>
                                <option value="2">SHIFT 2 (NIGHT)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold uppercase">Customer</label>
                            <select name="customer_code" id="select_customer" class="form-control input-industrial" required>
                                <option value="">-- SELECT --</option>
                                @foreach($availableCustomers as $c)
                                    <option value="{{ $c->code }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold uppercase">Part Number Identification</label>
                            <select name="part_no" id="select_part" class="form-control input-industrial" required>
                                <option value="">-- SELECT CUSTOMER FIRST --</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-2">
                            <label class="small font-weight-bold uppercase">Menpower</label>
                            <input type="number" name="manpower" class="form-control input-industrial text-center" value="8">
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold uppercase">Pro cess</label>
                            <input type="number" name="process_qty" class="form-control input-industrial text-center" value="4">
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold uppercase">Qty/Lot Prod</label>
                            <input type="number" name="qty_lot" class="form-control input-industrial text-center" value="200">
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold uppercase">Cap/ Hour</label>
                            <input type="number" name="cap_per_hour" id="input_cap" class="form-control input-industrial text-center" value="320" required>
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold uppercase">Dandory (Min)</label>
                            <input type="number" name="dandory_time" id="input_dandory" class="form-control input-industrial text-center" value="15">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="badge badge-info w-100 py-2 font-weight-bold" id="live_load_label">LOAD: 0.0H</div>
                        </div>
                    </div>

                    <div class="card p-4 border-primary bg-light" style="border-width: 2px; border-radius: 15px;">
                        <h6 class="font-weight-bold text-primary mb-3 uppercase">Plan Produksi (Qty)</h6>
                        <div class="row">
                            <div class="col-md-6" id="box_shift_1">
                                <small class="font-weight-bold">SHIFT 1 - REGULER</small>
                                <input type="number" name="s1_plan_reg" id="s1_reg" class="form-control input-industrial calc-trigger" value="0">
                                <small class="font-weight-bold mt-2 d-block">SHIFT 1 - OVERTIME</small>
                                <input type="number" name="s1_plan_ot" id="s1_ot" class="form-control input-industrial calc-trigger" value="0">
                            </div>
                            <div class="col-md-6" id="box_shift_2" style="opacity: 0.5;">
                                <small class="font-weight-bold">SHIFT 2 - REGULER</small>
                                <input type="number" name="s2_plan_reg" id="s2_reg" class="form-control input-industrial calc-trigger" value="0" disabled>
                                <small class="font-weight-bold mt-2 d-block">SHIFT 2 - OVERTIME</small>
                                <input type="number" name="s2_plan_ot" id="s2_ot" class="form-control input-industrial calc-trigger" value="0" disabled>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <input type="hidden" name="plan_date" value="{{ $date }}">
                        <textarea name="remark" class="form-control input-industrial" placeholder="Optional planning note..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold shadow uppercase">Authorize Production Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // FUNGSI GANTI SHIFT DI MODAL
    document.getElementById('shift_selector').addEventListener('change', function() {
        const isShift1 = this.value === "1";
        document.getElementById('box_shift_1').style.opacity = isShift1 ? "1" : "0.5";
        document.getElementById('box_shift_2').style.opacity = isShift1 ? "0.5" : "1";
        
        document.getElementById('s1_reg').disabled = !isShift1;
        document.getElementById('s1_ot').disabled = !isShift1;
        document.getElementById('s2_reg').disabled = isShift1;
        document.getElementById('s2_ot').disabled = isShift1;
        
        if(isShift1) { document.getElementById('s2_reg').value = 0; document.getElementById('s2_ot').value = 0; }
        else { document.getElementById('s1_reg').value = 0; document.getElementById('s1_ot').value = 0; }
        calculateLiveHours();
    });

    function calculateLiveHours() {
        const cap = parseFloat(document.getElementById('input_cap').value) || 0;
        const dandory = parseFloat(document.getElementById('input_dandory').value) || 0;
        const s1 = (parseFloat(document.getElementById('s1_reg').value) || 0) + (parseFloat(document.getElementById('s1_ot').value) || 0);
        const s2 = (parseFloat(document.getElementById('s2_reg').value) || 0) + (parseFloat(document.getElementById('s2_ot').value) || 0);
        
        const totalQty = s1 + s2;
        let hours = cap > 0 ? (totalQty / cap) + (dandory / 60) : 0;
        if(totalQty === 0) hours = 0;
        
        document.getElementById('live_load_label').innerText = "LOAD: " + hours.toFixed(1) + "H";
        document.getElementById('live_load_label').className = hours > 8 ? "badge badge-danger w-100 py-2" : "badge badge-info w-100 py-2";
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