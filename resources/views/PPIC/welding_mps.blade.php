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

    /* 📈 LEDGER TABLE */
    .ledger-container { background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.04); }
    .table-ledger thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px; border-bottom: 2px solid #edf2f7; }
    .table-ledger td { padding: 18px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 13px; }
    
    /* 🏷️ MODAL INDUSTRIAL STYLE */
    .modal-content { border-radius: 35px; border: none; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
    .tech-input { border-radius: 14px; border: 2px solid #f1f5f9; font-weight: 700; transition: 0.3s; background: #f8fafc; height: 50px; }
    .tech-input:focus { border-color: var(--brand-primary); outline: none; background: #fff; box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1); }

    /* Capacity Control Box (Industrial Dark) */
    .cap-control-box { background: var(--dark-surface); border-radius: 24px; padding: 25px; color: white; margin-bottom: 25px; border: 1px solid rgba(255,255,255,0.1); }
    .hour-badge { background: rgba(67, 97, 238, 0.2); border: 1.5px solid var(--brand-primary); color: #fff; padding: 10px 20px; border-radius: 15px; display: inline-block; }
    .hour-value { font-family: 'Orbitron'; font-size: 28px; color: var(--brand-warning); font-weight: 900; }
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

    {{-- 📋 MPS TABLE --}}
    <div class="ledger-container animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Station</th>
                        <th class="text-left">Part Identification</th>
                        <th>MP</th>
                        <th>Cap/H</th>
                        <th>Target Qty</th>
                        <th>Actual</th>
                        <th>Balance</th>
                        <th class="text-right pr-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $p)
                    @php 
                        $isComplete = $p->total_actual >= $p->total_target && $p->total_target > 0;
                    @endphp
                    <tr>
                        <td class="text-left pl-4">
                            <span class="badge badge-dark px-3 py-2" style="font-family: 'JetBrains Mono'; border-radius: 8px;">{{ $p->line_code }}</span>
                        </td>
                        <td class="text-left">
                            <div class="font-weight-black text-dark" style="font-size: 14px;">{{ $p->part_no }}</div>
                            <small class="text-muted font-weight-bold uppercase d-block" style="font-size: 10px;">{{ $p->part_name }}</small>
                            <span class="badge badge-light border text-primary mt-1" style="font-size: 9px;">{{ $p->customer_code }}</span>
                        </td>
                        <td class="font-weight-black text-muted">{{ $p->manpower }}</td>
                        <td class="font-weight-black text-muted">{{ $p->cap_per_hour }}</td>
                        <td class="font-weight-black text-dark" style="font-family: 'JetBrains Mono';">{{ number_format($p->total_target) }}</td>
                        <td class="text-success font-weight-black">{{ number_format($p->total_actual) }}</td>
                        <td class="{{ $p->balance > 0 ? 'text-danger' : 'text-muted' }} font-weight-black">{{ number_format($p->balance) }}</td>
                        <td class="text-right pr-4">
                            @if($isComplete)
                                <span class="badge badge-success px-3 py-2 rounded-pill font-weight-bold">COMPLETED</span>
                            @else
                                <span class="badge badge-warning px-3 py-2 rounded-pill font-weight-bold animate__animated animate__pulse animate__infinite">IN PROCESS</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="py-5 text-center text-muted">No schedule found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🛡️ MODAL: REGISTER PRODUCTION PLAN (INDUSTRIAL VERSION) --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalAddPlan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white p-4 border-0">
                <h5 class="modal-title font-weight-black uppercase" style="font-family: 'Orbitron'; letter-spacing: 1px;">
                    <i class="fas fa-microchip mr-2 text-primary"></i> Register_Welding_Plan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            
            <form action="{{ route('ppic.welding.mps_store') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    
                    {{-- 🏭 SECTION 1: CAPACITY & TIME CALCULATOR --}}
                    <div class="cap-control-box shadow-lg animate__animated animate__fadeInDown">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <label class="small font-weight-black text-white-50 uppercase mb-2">Manual Manpower</label>
                                <input type="number" name="manpower" id="inp_mp" class="form-control bg-transparent border-0 text-white font-weight-black p-0" style="font-size: 32px; outline:none;" value="1" required oninput="autoCalc()">
                            </div>
                            <div class="col-md-3 border-left border-secondary">
                                <label class="small font-weight-black text-white-50 uppercase mb-2">Capacity / Hour</label>
                                <input type="number" name="cap_per_hour" id="inp_cap" class="form-control bg-transparent border-0 text-white font-weight-black p-0" style="font-size: 32px; outline:none;" value="100" required oninput="autoCalc()">
                            </div>
                            <div class="col-md-6 text-right border-left border-secondary">
                                <div class="hour-badge">
                                    <label class="small font-weight-black text-white-50 uppercase d-block mb-1">Estimated Production Time</label>
                                    <div class="hour-value" id="display_hours">0.0 <small style="font-size: 14px;">HRS</small></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 📋 SECTION 2: DEPLOYMENT DETAILS --}}
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label class="small font-weight-black text-muted uppercase mb-2">Deployment Date</label>
                            <input type="date" name="plan_date" class="form-control tech-input" value="{{ $date }}" required>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="small font-weight-black text-muted uppercase mb-2">Authorized Station</label>
                            <select name="line_code" class="form-control tech-input" required>
                                <option value="" disabled selected>-- SELECT STATION --</option>
                                @foreach($availableLines as $line)
                                    <option value="{{ $line->kode_line }}">{{ $line->kode_line }} - {{ $line->nama_line }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="small font-weight-black text-muted uppercase mb-2">Target Part Identification</label>
                            <select name="part_no" class="form-control tech-input" required>
                                <option value="" disabled selected>-- CHOOSE PART --</option>
                                @foreach($availableParts as $part)
                                    <option value="{{ $part->part_no }}">
                                        [{{ $part->customer_code }}] {{ $part->part_no }} - {{ $part->part_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 🎯 SECTION 3: SHIFT TARGETS --}}
                        <div class="col-md-6">
                            <div class="p-4 rounded-3xl animate__animated animate__fadeInLeft" style="background: #f0f4ff; border: 1px solid #d0dfff;">
                                <label class="small font-weight-bold text-primary uppercase mb-2 d-block">Shift 1 Production Target</label>
                                <input type="number" name="s1_plan_reg" id="inp_s1" class="form-control border-0 bg-transparent font-weight-black text-primary" style="font-size: 42px; outline: none;" value="0" oninput="autoCalc()">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 rounded-3xl animate__animated animate__fadeInRight" style="background: #fdf2f2; border: 1px solid #fee2e2;">
                                <label class="small font-weight-bold text-danger uppercase mb-2 d-block">Shift 2 Production Target</label>
                                <input type="number" name="s2_plan_reg" id="inp_s2" class="form-control border-0 bg-transparent font-weight-black text-danger" style="font-size: 42px; outline: none;" value="0" oninput="autoCalc()">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-primary btn-block py-4 font-weight-black rounded-2xl shadow-xl animate__animated animate__pulse animate__infinite" style="background: var(--brand-primary); font-size: 1.2rem; letter-spacing: 2px;">
                        AUTHORIZE PRODUCTION LOAD
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function autoCalc() {
        let mp = parseFloat(document.getElementById('inp_mp').value) || 0;
        let cap = parseFloat(document.getElementById('inp_cap').value) || 0;
        let s1 = parseFloat(document.getElementById('inp_s1').value) || 0;
        let s2 = parseFloat(document.getElementById('inp_s2').value) || 0;
        
        let totalQty = s1 + s2;
        let totalHours = 0;

        // Rumus: Total Target / Kapasitas Per Jam
        if (cap > 0 && totalQty > 0) {
            totalHours = totalQty / cap;
        }

        // Tampilkan hasil di box hitam (Industrial Hour Badge)
        document.getElementById('display_hours').innerHTML = totalHours.toFixed(1) + ' <small style="font-size: 14px;">HRS</small>';
    }

    // Hitung ulang saat modal dibuka
    $('#modalAddPlan').on('shown.bs.modal', function () {
        autoCalc();
    });
</script>
@endsection