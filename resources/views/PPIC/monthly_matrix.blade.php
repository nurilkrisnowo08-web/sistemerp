@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root {
        --primary: #4361ee;
        --dark: #0f172a;
        --slate: #64748b;
        --success: #10b981;
        --danger: #ef4444;
        --weekend: #fff1f2;
        --today: #eff6ff;
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: var(--dark); }

    /* Matrix Container */
    .matrix-card { 
        background: white; 
        border-radius: 20px; 
        border: 1px solid #e2e8f0; 
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); 
        overflow: hidden; 
    }

    .matrix-wrapper { overflow: auto; max-height: 72vh; position: relative; }

    /* Table Styling */
    .table-matrix { border-collapse: separate; border-spacing: 0; width: 100%; table-layout: fixed; }
    
    .table-matrix th, .table-matrix td { 
        border-right: 1px solid #f1f5f9; 
        border-bottom: 1px solid #f1f5f9; 
        padding: 8px; 
        font-size: 11px;
    }

    /* Sticky Headers */
    .sticky-col { 
        position: sticky; left: 0; background: white !important; z-index: 20; 
        width: 190px; text-align: left !important; font-weight: 700;
        box-shadow: 5px 0 10px -5px rgba(0,0,0,0.1);
    }
    
    thead tr:nth-child(1) th { position: sticky; top: 0; z-index: 30; background: var(--dark); color: white; border: none; }
    thead tr:nth-child(2) th { position: sticky; top: 35px; z-index: 30; background: #f1f5f9; color: var(--slate); font-size: 9px; }

    /* Day Header */
    .header-day { width: 45px; text-align: center; font-family: 'Orbitron'; }
    .day-name { font-weight: 800; text-transform: uppercase; }

    /* Input Grid */
    .input-grid { 
        width: 100%; border: none; background: transparent; text-align: center; 
        font-weight: 700; font-family: 'JetBrains Mono'; font-size: 12px; 
        color: var(--dark); transition: 0.2s;
    }
    .input-grid:focus { background: white; border-radius: 4px; box-shadow: 0 0 0 2px var(--primary); outline: none; }
    .input-grid::-webkit-inner-spin-button { display: none; }

    /* Statuses */
    .bg-weekend { background-color: var(--weekend) !important; color: var(--danger); }
    .bg-today { background-color: var(--today) !important; box-shadow: inset 0 0 0 1px var(--primary); }
    
    /* Footer Styling */
    tfoot tr td { font-family: 'Orbitron'; font-weight: 800; font-size: 11px; }
    .row-total { background: var(--dark) !important; color: #fbbf24 !important; font-family: 'JetBrains Mono'; }

    /* Controls */
    .shift-master-pill { background: #f1f5f9; padding: 4px; border-radius: 12px; display: inline-flex; }
    .shift-btn { 
        border: none; padding: 8px 24px; border-radius: 10px; font-weight: 800; 
        font-size: 11px; transition: 0.3s; cursor: pointer; color: var(--slate);
        background: transparent;
    }
    .active-s1 { background: var(--primary) !important; color: white !important; box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3); }
    .active-s2 { background: var(--dark) !important; color: white !important; }

    /* Custom Scrollbar */
    .matrix-wrapper::-webkit-scrollbar { width: 8px; height: 8px; }
    .matrix-wrapper::-webkit-scrollbar-track { background: #f1f5f9; }
    .matrix-wrapper::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    .toast-sync {
        position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%);
        background: var(--dark); color: white; padding: 12px 30px; border-radius: 50px;
        z-index: 1000; font-weight: 700; font-size: 12px; display: none;
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
</style>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-2">
                    <li class="breadcrumb-item small font-weight-bold"><a href="#">PPIC_CORE</a></li>
                    <li class="breadcrumb-item small font-weight-bold active">MONTHLY_MASTER</li>
                </ol>
            </nav>
            <h2 class="font-weight-black m-0" style="letter-spacing: -1px; color: var(--dark); font-family: 'Orbitron';">MASTER_MATRIX <span class="text-primary">v4.0</span></h2>
            <p class="text-muted small font-weight-bold mb-0">OPERATIONAL_PERIOD: {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="shift-master-pill mr-3">
                <button type="button" id="btn-s1" class="shift-btn active-s1" onclick="switchShift('s1')">SHIFT_01</button>
                <button type="button" id="btn-s2" class="shift-btn" onclick="switchShift('s2')">SHIFT_02</button>
            </div>

            <form action="" method="GET" class="d-flex mr-2">
                <select name="month" class="form-control form-control-sm rounded-lg border-secondary font-weight-bold px-3 mr-2" onchange="this.form.submit()">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('ppic.mps.index') }}" class="btn btn-primary shadow-sm font-weight-bold rounded-lg px-4">
                <i class="fas fa-external-link-alt mr-2"></i> OPEN DAILY MPS
            </a>
        </div>
    </div>

    <div id="status-notif" class="toast-sync animate__animated animate__fadeInUp">
        <i class="fas fa-sync fa-spin mr-2 text-primary"></i> SYSTEM_SYNC_ACTIVE...
    </div>

    <div class="matrix-card">
        <div class="matrix-wrapper">
            <table class="table-matrix">
                <thead>
                    <tr>
                        <th rowspan="2" class="sticky-col">PART_IDENTIFICATION</th>
                        <th rowspan="2" style="width: 60px;">CUST</th>
                        <th rowspan="2" style="width: 45px;">PROC</th>
                        <th rowspan="2" style="width: 60px;">CAP/H</th>
                        @for($d=1; $d<=$daysInMonth; $d++)
                            @php 
                                $dateStr = "$year-$month-$d";
                                $isWknd = (date('N', strtotime($dateStr)) >= 6);
                                $isToday = (date('Y-m-d', strtotime($dateStr)) == date('Y-m-d'));
                            @endphp
                            <th class="header-day {{ $isWknd ? 'bg-weekend' : '' }} {{ $isToday ? 'bg-today' : '' }}">
                                {{ str_pad($d, 2, '0', STR_PAD_LEFT) }}
                            </th>
                        @endfor
                        <th rowspan="2" style="width: 100px; background: #fbbf24; color: black;">TOTAL_QTY</th>
                    </tr>
                    <tr>
                        @for($d=1; $d<=$daysInMonth; $d++)
                            @php $isWknd = (date('N', strtotime("$year-$month-$d")) >= 6); @endphp
                            <th class="day-name {{ $isWknd ? 'text-danger' : '' }}">{{ date('D', strtotime("$year-$month-$d")) }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($parts as $part)
                    <tr>
                        <td class="sticky-col pl-3">
                            <span class="text-primary">#</span> {{ $part->part_no }}
                        </td>
                        <td class="text-center font-weight-bold text-muted">{{ $part->customer_code }}</td>
                        <td class="text-center">{{ $part->process_qty ?? 4 }}</td>
                        <td class="text-center bg-light font-weight-bold">{{ $part->cap_per_hour ?? 320 }}</td>
                        
                        @for($d=1; $d<=$daysInMonth; $d++)
                            @php
                                $dStr = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($d, 2, '0', STR_PAD_LEFT);
                                $plan = $planData->get($part->part_no)?->firstWhere('plan_date', $dStr);
                                $s1_val = $plan ? $plan->s1_plan_reg : '';
                                $s2_val = $plan ? $plan->s2_plan_reg : '';
                                $isWknd = (date('N', strtotime($dStr)) >= 6);
                                $isToday = ($dStr == date('Y-m-d'));
                            @endphp
                            <td class="{{ $isWknd ? 'bg-weekend' : '' }} {{ $isToday ? 'bg-today' : '' }}">
                                <input type="number" class="input-grid qty-input" 
                                       value="{{ $s1_val }}" 
                                       data-s1="{{ $s1_val }}" data-s2="{{ $s2_val }}"
                                       data-part="{{ $part->part_no }}" data-day="{{ $d }}"
                                       data-cap="{{ $part->cap_per_hour ?? 320 }}" 
                                       data-cust="{{ $part->customer_code ?? 'AMA' }}"
                                       data-line="{{ $part->line_code ?? 'LINE A' }}"
                                       onchange="autoSave(this)">
                            </td>
                        @endfor
                        <td class="row-total text-center font-weight-bold">0</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #f8fafc;">
                        <td colspan="4" class="sticky-col text-right pr-3 font-weight-bold">TOTAL_DAILY_OUTPUT</td>
                        @for($d=1; $d<=$daysInMonth; $d++) 
                            <td id="day-qty-{{ $d }}" class="text-center font-weight-bold text-primary">0</td> 
                        @endfor
                        <td id="grand-total-qty" class="text-center font-weight-bold bg-primary text-white">0</td>
                    </tr>
                    <tr style="background: #f8fafc;">
                        <td colspan="4" class="sticky-col text-right pr-3 font-weight-bold">MACHINE_LOAD (HRS)</td>
                        @for($d=1; $d<=$daysInMonth; $d++) 
                            <td id="day-load-{{ $d }}" class="text-center font-weight-bold">0.0</td> 
                        @endfor
                        <td id="grand-total-load" class="text-center font-weight-bold bg-warning text-dark">0.0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
    let activeShift = 's1';

    document.addEventListener("DOMContentLoaded", () => {
        calculateAllTotals();
        // Auto-scroll ke tanggal hari ini
        const todayCol = document.querySelector('.bg-today');
        if (todayCol) {
            todayCol.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    });

    function switchShift(shift) {
        activeShift = shift;
        document.getElementById('btn-s1').classList.toggle('active-s1', shift === 's1');
        document.getElementById('btn-s2').classList.toggle('active-s2', shift === 's2');
        
        document.querySelectorAll('.qty-input').forEach(inp => {
            inp.value = inp.getAttribute(`data-${shift}`);
            // Animasi kecil saat ganti shift
            inp.style.opacity = "0";
            setTimeout(() => {
                inp.style.opacity = "1";
            }, 100);
        });
        calculateAllTotals();
    }

    function autoSave(input) {
        const statusBox = document.getElementById('status-notif');
        statusBox.style.display = "block";
        input.setAttribute(`data-${activeShift}`, input.value);

        fetch('{{ route("ppic.monthly.ajax_save") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                shift: activeShift,
                part_no: input.dataset.part,
                customer_code: input.dataset.cust,
                line_code: input.dataset.line,
                day: input.dataset.day,
                month: '{{ $month }}',
                year: '{{ $year }}',
                qty: input.value,
                cap_per_hour: input.dataset.cap
            })
        }).then(() => {
            setTimeout(() => { statusBox.style.display = "none"; }, 800);
            calculateAllTotals();
        }).catch(err => {
            alert("Connection Lost! Data not saved.");
            statusBox.style.display = "none";
        });
    }

    function calculateAllTotals() {
        const days = {{ $daysInMonth }};
        let grandQty = 0; 
        let grandLoad = 0;

        for (let d = 1; d <= days; d++) {
            let dQty = 0; 
            let dLoad = 0;
            document.querySelectorAll(`.qty-input[data-day="${d}"]`).forEach(inp => {
                let v = parseInt(inp.value) || 0;
                dQty += v;
                if(v > 0) {
                    let cap = parseInt(inp.dataset.cap) || 320;
                    dLoad += (v / cap) + 0.25; // 0.25 adalah asumsi dandory per part
                }
            });

            document.getElementById(`day-qty-${d}`).innerText = dQty.toLocaleString();
            document.getElementById(`day-load-${d}`).innerText = dLoad.toFixed(1);
            
            // Warning if overcapacity (8 hours)
            document.getElementById(`day-load-${d}`).style.color = dLoad > 8 ? 'var(--danger)' : 'var(--slate)';
            
            grandQty += dQty; 
            grandLoad += dLoad;
        }

        // Row totals
        document.querySelectorAll('tbody tr').forEach(row => {
            let rSum = 0;
            row.querySelectorAll('.qty-input').forEach(inp => rSum += (parseInt(inp.value) || 0));
            row.querySelector('.row-total').innerText = rSum.toLocaleString();
        });

        document.getElementById('grand-total-qty').innerText = grandQty.toLocaleString();
        document.getElementById('grand-total-load').innerText = grandLoad.toFixed(1);
    }
</script>
@endsection