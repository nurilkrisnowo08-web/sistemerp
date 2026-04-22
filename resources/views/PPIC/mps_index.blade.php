@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root { --excel-border: #000000; --shift1-bg: #f8fafc; --shift2-bg: #f1f5f9; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    
    /* Excel Style Table Layout */
    .table-mps { border: 2px solid var(--excel-border); background: white; width: 100%; border-collapse: collapse; }
    .table-mps th { 
        border: 1px solid var(--excel-border); padding: 10px; 
        font-size: 11px; font-weight: 800; text-transform: uppercase;
        background-color: #e2e8f0; vertical-align: middle; color: #1e293b;
    }
    .table-mps td { 
        border: 1px solid var(--excel-border); padding: 8px; 
        font-size: 12px; font-weight: 700; vertical-align: middle; color: #334155;
    }

    .bg-shift1 { background-color: var(--shift1-bg); }
    .bg-shift2 { background-color: var(--shift2-bg); }
    .font-mono { font-family: 'JetBrains Mono', monospace; }
    
    .btn-excel-style {
        border-radius: 8px; font-weight: 800; letter-spacing: 0.5px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: 0.3s;
    }
    .badge-mc { background: #0f172a; color: white; border-radius: 4px; padding: 4px 8px; font-size: 10px; }
</style>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-weight-extrabold mb-0" style="letter-spacing: -1px; color: #0f172a;">PRODUCTION PLANNING (MPS)</h2>
            <p class="text-muted small font-weight-bold">Industrial Master Schedule & Real-time Achievement Control</p>
        </div>
        
        <div class="d-flex gap-2">
            <form action="{{ route('ppic.mps.index') }}" method="GET" class="d-flex mr-2">
                <input type="date" name="date" class="form-control rounded-pill border-dark px-4" 
                       value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <button class="btn btn-primary btn-excel-style px-4 shadow-lg" data-toggle="modal" data-target="#modalAddPlan">
                <i class="fas fa-plus-circle mr-2"></i> REGISTER NEW PLAN
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-24 overflow-hidden">
        <div class="table-responsive">
            <table class="table-mps text-center">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 40px;">NO</th>
                        <th rowspan="2" style="min-width: 280px;">PART NAME / IDENTIFICATION</th>
                        <th rowspan="2" style="width: 80px;">M/C</th>
                        <th rowspan="2" style="width: 70px;">CUST</th>
                        <th rowspan="2" style="width: 50px;">M/P</th>
                        <th rowspan="2" style="width: 80px;">CAP/ HOUR</th>
                        <th colspan="4" class="bg-shift1">SHIFT 1 (OPERATIONAL)</th>
                        <th colspan="4" class="bg-shift2">SHIFT 2 (OPERATIONAL)</th>
                        <th rowspan="2">REMARK</th>
                    </tr>
                    <tr>
                        <th class="bg-shift1" style="width: 70px;">PLAN</th>
                        <th class="bg-shift1" style="width: 60px;">HOUR</th>
                        <th class="bg-shift1" style="width: 70px;">ACTUAL</th>
                        <th class="bg-shift1" style="width: 70px;">BALANCE</th>
                        <th class="bg-shift2" style="width: 70px;">PLAN</th>
                        <th class="bg-shift2" style="width: 60px;">HOUR</th>
                        <th class="bg-shift2" style="width: 70px;">ACTUAL</th>
                        <th class="bg-shift2" style="width: 70px;">BALANCE</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $index => $p)
                    <tr>
                        <td class="bg-light">{{ $index + 1 }}</td>
                        <td class="text-left pl-3">{{ $p->part_no }}</td>
                        <td><span class="badge-mc">{{ $p->line_code }}</span></td>
                        <td>{{ $p->customer_code }}</td>
                        <td>{{ $p->manpower }}</td>
                        <td class="bg-light font-mono">{{ $p->cap_per_hour }}</td>

                        {{-- SHIFT 1 --}}
                        <td class="font-mono">{{ $p->s1_total_target }}</td>
                        <td class="text-muted small">{{ $p->s1_hour }}h</td>
                        <td class="text-primary font-weight-extrabold bg-white">{{ $p->s1_actual }}</td>
                        <td class="{{ $p->s1_balance > 0 ? 'text-danger' : 'text-success' }} font-weight-bold">
                            {{ $p->s1_balance }}
                        </td>

                        {{-- SHIFT 2 --}}
                        <td class="font-mono">{{ $p->s2_total_target }}</td>
                        <td class="text-muted small">{{ $p->s2_hour }}h</td>
                        <td class="text-primary font-weight-extrabold bg-white">{{ $p->s2_actual }}</td>
                        <td class="{{ $p->s2_balance > 0 ? 'text-danger' : 'text-success' }} font-weight-bold">
                            {{ $p->s2_balance }}
                        </td>
                        
                        <td class="small text-muted italic">{{ $p->remark ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="15" class="py-5 text-muted bg-white">
                            <i class="fas fa-calendar-times fa-3x mb-3 opacity-25"></i><br>
                            -- No production plan registered for this date --
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL INPUT PLAN --}}
<div class="modal fade" id="modalAddPlan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content rounded-24 shadow-lg border-0">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-calendar-plus mr-2"></i> REGISTER PRODUCTION TARGET</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('ppic.mps.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-uppercase">Plan Date</label>
                            <input type="date" name="plan_date" class="form-control rounded-lg" value="{{ $date }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-uppercase">Machine (Line Registry)</label>
                            <select name="line_code" class="form-control rounded-lg" required>
                                <option value="">-- SELECT LINE --</option>
                                @foreach($availableLines as $l)
                                    {{-- FIX: Menggunakan kode_Line & nama_Line sesuai database --}}
                                    <option value="{{ $l->kode_Line }}">{{ $l->nama_Line }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-uppercase">Client Entity</label>
                            <select name="customer_code" id="select_customer" class="form-control rounded-lg" required>
                                <option value="">-- SELECT CUSTOMER --</option>
                                @foreach($availableCustomers as $c)
                                    <option value="{{ $c->code }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-uppercase">Part Number Selection</label>
                            <select name="part_no" id="select_part" class="form-control rounded-lg" required>
                                <option value="">-- SELECT CLIENT FIRST --</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row text-center mb-4">
                        <div class="col-md-6">
                            <label class="font-weight-bold small text-uppercase">Capacity / Hour (Target Speed)</label>
                            <input type="number" name="cap_per_hour" class="form-control text-center font-weight-bold border-primary" placeholder="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="font-weight-bold small text-uppercase">Total Manpower (M/P)</label>
                            <input type="number" name="manpower" class="form-control text-center" value="1" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light p-3 border-0 rounded-lg">
                                <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-sun mr-2"></i> SHIFT 1 TARGET</h6>
                                <div class="form-group mb-2">
                                    <small class="font-weight-bold text-muted">Reguler Plan (Pcs)</small>
                                    <input type="number" name="s1_plan_reg" class="form-control" placeholder="0">
                                </div>
                                <div class="form-group">
                                    <small class="font-weight-bold text-muted">Overtime Plan (Pcs)</small>
                                    <input type="number" name="s1_plan_ot" class="form-control" placeholder="0">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light p-3 border-0 rounded-lg">
                                <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-moon mr-2"></i> SHIFT 2 TARGET</h6>
                                <div class="form-group mb-2">
                                    <small class="font-weight-bold text-muted">Reguler Plan (Pcs)</small>
                                    <input type="number" name="s2_plan_reg" class="form-control" placeholder="0">
                                </div>
                                <div class="form-group">
                                    <small class="font-weight-bold text-muted">Overtime Plan (Pcs)</small>
                                    <input type="number" name="s2_plan_ot" class="form-control" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="font-weight-bold small text-uppercase">Plan Remarks (Optional)</label>
                        <textarea name="remark" class="form-control rounded-lg" rows="2" placeholder="Ex: Raw material delay, machine maintenance, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer p-4 bg-light border-0">
                    <button type="submit" class="btn btn-primary btn-block rounded-pill font-weight-bold shadow-lg py-3">
                        <i class="fas fa-save mr-2"></i> AUTHORIZE & SAVE PRODUCTION PLAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    /** * AJAX SINKRONISASI PART BERDASARKAN CUSTOMER 
     */
    document.getElementById('select_customer').addEventListener('change', function() {
        const customer = this.value;
        const partSelect = document.getElementById('select_part');
        
        partSelect.innerHTML = '<option>-- SYNCING DATA... --</option>';

        // Menarik data parts library dari rute yang sudah kita sinkronkan tadi
        fetch(`/get-parts-and-specs/${customer}`)
            .then(response => response.json())
            .then(data => {
                partSelect.innerHTML = '<option value="">-- SELECT PART --</option>';
                data.parts.forEach(part => {
                    partSelect.innerHTML += `<option value="${part.part_no}">${part.part_no} - ${part.part_name}</option>`;
                });
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                partSelect.innerHTML = '<option>-- ERROR FETCHING DATA --</option>';
            });
    });
</script>
@endsection