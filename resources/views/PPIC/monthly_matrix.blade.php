@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root { --excel-border: #000000; --header-bg: #d1d5db; --footer-qty: #92d050; --footer-load: #fbbf24; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }

    .matrix-wrapper { background: white; border: 2px solid var(--excel-border); position: relative; }
    .table-matrix { border-collapse: collapse; font-size: 10px; width: 100%; table-layout: fixed; }
    .table-matrix th, .table-matrix td { border: 1px solid var(--excel-border); padding: 4px; text-align: center; vertical-align: middle; }
    
    .sticky-col { 
        position: sticky; left: 0; background: #f8fafc !important; z-index: 10; 
        width: 180px; text-align: left !important; font-weight: 800; 
        border-right: 2px solid var(--excel-border) !important; 
    }
    
    .header-day { background: #334155 !important; color: white; width: 40px; position: sticky; top: 0; z-index: 15; }
    .day-name { font-size: 8px; position: sticky; top: 24px; background: var(--header-bg); z-index: 15; }

    .input-grid { width: 100%; border: none; background: transparent; text-align: center; font-weight: 700; font-family: 'JetBrains Mono', monospace; font-size: 11px; }
    .input-grid:focus { background: #fffde7; outline: 1.5px solid #2563eb; }

    .bg-weekend { background-color: #fee2e2 !important; }
    .bg-today { background-color: #dcfce7 !important; outline: 2px solid #22c55e; }
    .tfoot-total { font-weight: 900; color: black; }
    
    /* Tombol Shift Switcher */
    .shift-btn { border-radius: 20px; font-weight: 800; padding: 5px 20px; transition: 0.3s; font-size: 12px; }
    .active-s1 { background: #4361ee !important; color: white; border: none; box-shadow: 0 4px 10px rgba(67, 97, 238, 0.4); }
    .active-s2 { background: #1e293b !important; color: white; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
</style>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="font-weight-black mb-0" style="letter-spacing: -1.5px; color: #0f172a;">PRODUCTION_MONTHLY_MASTER</h2>
            <p class="text-muted small font-weight-bold uppercase mb-0">Period: {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="bg-white p-1 rounded-pill border shadow-sm d-flex">
                <button type="button" id="btn-s1" class="btn btn-sm shift-btn active-s1" onclick="switchShift('s1')">SHIFT 1</button>
                <button type="button" id="btn-s2" class="btn btn-sm shift-btn text-muted" onclick="switchShift('s2')">SHIFT 2</button>
            </div>

            <form action="" method="GET" class="d-flex mr-2">
                <select name="month" class="form-control form-control-sm rounded-pill border-dark px-4 mr-2" onchange="this.form.submit()">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('ppic.mps.index', ['date' => date('Y-m-d')]) }}" class="btn btn-primary shadow font-weight-bold rounded-pill px-4">
                <i class="fas fa-desktop mr-2"></i> OPEN DAILY MPS
            </a>
        </div>
    </div>

    <div id="status-notif" class="alert alert-dark shadow-lg py-2 px-4 rounded-pill text-white" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: none;">
        <i class="fas fa-sync fa-spin mr-2"></i> Syncing to Database...
    </div>

    <div class="matrix-wrapper overflow-auto" style="max-height: 70vh;">
        <table class="table-matrix">
            <thead>
                <tr>
                    <th rowspan="2" class="sticky-col">PART NUMBER / IDENTIFICATION</th>
                    <th rowspan="2" style="width: 50px;">CUST</th>
                    <th rowspan="2" style="width: 40px;">PROC</th>
                    <th rowspan="2" style="width: 50px;">CAP/H</th>
                    @for($d=1; $d<=$daysInMonth; $d++)
                        @php 
                            $isWeekend = (date('N', strtotime("$year-$month-$d")) >= 6);
                        @endphp
                        <th class="header-day {{ $isWeekend ? 'bg-weekend text-danger' : '' }}">{{ $d }}</th>
                    @endfor
                    <th rowspan="2" style="width: 80px; background: #1e293b; color: white;">TOTAL</th>
                </tr>
                <tr>
                    @for($d=1; $d<=$daysInMonth; $d++)
                        <th class="day-name">{{ date('D', strtotime("$year-$month-$d")) }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($parts as $part)
                <tr>
                    <td class="sticky-col pl-2">{{ $part->part_no }}</td>
                    <td class="small">{{ $part->customer_code }}</td>
                    <td>{{ $part->process_qty ?? 1 }}</td>
                    <td class="bg-light font-weight-bold text-primary">{{ $part->cap_per_hour ?? 0 }}</td>
                    
                    @for($d=1; $d<=$daysInMonth; $d++)
                        @php
                            $dStr = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($d, 2, '0', STR_PAD_LEFT);
                            $currentPlan = $planData->get($part->part_no)?->firstWhere('plan_date', $dStr);
                            
                            $s1_val = $currentPlan ? $currentPlan->s1_plan_reg : '';
                            $s2_val = $currentPlan ? $currentPlan->s2_plan_reg : '';
                            
                            $isWeekend = (date('N', strtotime($dStr)) >= 6);
                        @endphp
                        <td class="{{ $isWeekend ? 'bg-weekend' : '' }}">
                            <input type="number" 
                                   class="input-grid qty-input" 
                                   value="{{ $s1_val }}" 
                                   data-s1="{{ $s1_val }}"
                                   data-s2="{{ $s2_val }}"
                                   data-part="{{ $part->part_no }}"
                                   data-day="{{ $d }}"
                                   data-cap="{{ $part->cap_per_hour ?? 0 }}"
                                   data-cust="{{ $part->customer_code }}"
                                   data-line="{{ $part->line_code }}"
                                   onchange="autoSave(this)">
                        </td>
                    @endfor
                    <td class="bg-dark text-warning font-weight-bold row-total" data-part-total="{{ $part->part_no }}">0</td>
                </tr>
                @endforeach
            </tbody>
            
            <tfoot>
                <tr class="tfoot-total" style="background: var(--footer-qty) !important;">
                    <td colspan="4" class="sticky-col text-right pr-3">TOTAL QTY PRODUKSI / DAY</td>
                    @for($d=1; $d<=$daysInMonth; $d++)
                        <td id="day-qty-{{ $d }}">0</td>
                    @endfor
                    <td id="grand-total-qty">0</td>
                </tr>
                <tr class="tfoot-total" style="background: var(--footer-load) !important;">
                    <td colspan="4" class="sticky-col text-right pr-3 text-dark">EST. M/C LOAD (HOURS)</td>
                    @for($d=1; $d<=$daysInMonth; $d++)
                        <td id="day-load-{{ $d }}" class="text-dark">0.0</td>
                    @endfor
                    <td id="grand-total-load" class="text-dark">0.0</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
    let activeShift = 's1';

    document.addEventListener("DOMContentLoaded", function() {
        calculateAllTotals();
    });

    function switchShift(shift) {
        activeShift = shift;
        document.getElementById('btn-s1').className = shift === 's1' ? 'btn btn-sm shift-btn active-s1' : 'btn btn-sm shift-btn text-muted';
        document.getElementById('btn-s2').className = shift === 's2' ? 'btn btn-sm shift-btn active-s2' : 'btn btn-sm shift-btn text-muted';

        document.querySelectorAll('.qty-input').forEach(inp => {
            inp.value = inp.getAttribute(`data-${shift}`);
        });
        calculateAllTotals();
    }

    function autoSave(input) {
        const statusBox = document.getElementById('status-notif');
        statusBox.style.display = "block";
        
        // Update local data attributes
        input.setAttribute(`data-${activeShift}`, input.value);

        const payload = {
            _token: '{{ csrf_token() }}',
            shift: activeShift,
            part_no: input.dataset.part,
            customer_code: input.dataset.cust,
            line_code: input.dataset.line,
            day: input.dataset.day,
            month: '{{ $month }}',
            year: '{{ $year }}',
            qty: input.value
        };

        fetch('{{ route("ppic.monthly.ajax_save") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(() => {
            setTimeout(() => { statusBox.style.display = "none"; }, 500);
            calculateAllTotals();
        });
    }

    function calculateAllTotals() {
        const days = {{ $daysInMonth }};
        let grandQty = 0;
        let grandLoad = 0;

        for (let d = 1; d <= days; d++) {
            let dayQty = 0;
            let dayLoad = 0;
            
            document.querySelectorAll(`.qty-input[data-day="${d}"]`).forEach(inp => {
                const qty = parseInt(inp.value) || 0;
                const cap = parseInt(inp.dataset.cap) || 0;
                dayQty += qty;
                if(qty > 0 && cap > 0) {
                    dayLoad += (qty / cap) + 0.25; // 0.25 = Dandory 15m
                }
            });

            document.getElementById(`day-qty-${d}`).innerText = dayQty.toLocaleString();
            document.getElementById(`day-load-${d}`).innerText = dayLoad.toFixed(1);
            document.getElementById(`day-load-${d}`).style.color = dayLoad > 8 ? 'red' : 'black';

            grandQty += dayQty;
            grandLoad += dayLoad;
        }

        // Row totals
        document.querySelectorAll('tr').forEach(row => {
            let rowSum = 0;
            row.querySelectorAll('.qty-input').forEach(inp => rowSum += parseInt(inp.value) || 0);
            const totalCell = row.querySelector('.row-total');
            if(totalCell) totalCell.innerText = rowSum.toLocaleString();
        });

        document.getElementById('grand-total-qty').innerText = grandQty.toLocaleString();
        document.getElementById('grand-total-load').innerText = grandLoad.toFixed(1);
    }
</script>
@endsection