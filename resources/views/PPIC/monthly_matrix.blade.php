@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root {
        --p-primary: #4361ee; --p-dark: #0f172a; --p-slate: #64748b;
        --p-emerald: #10b981; --p-rose: #ef4444; --p-amber: #f59e0b;
        --p-bg: #f8fafc;
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--p-bg); }

    /* 🌊 Viewport Licin & Responsif */
    .matrix-container { 
        background: white; border-radius: 24px; border: 1px solid #e2e8f0; 
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.05); overflow: hidden; 
    }
    
    .matrix-scroll-wrapper { 
        overflow: auto; max-height: 75vh; 
        -webkit-overflow-scrolling: touch; 
    }

    /* 📊 Tabel Modern */
    .table-matrix { border-collapse: separate; border-spacing: 0; width: max-content; min-width: 100%; }

    /* 📌 Sticky Column (Part No) */
    .sticky-col { 
        position: sticky; left: 0; background: white !important; z-index: 45; 
        width: 200px; padding: 12px 20px !important; border-right: 3px solid #f1f5f9 !important;
        box-shadow: 10px 0 20px -10px rgba(0,0,0,0.1);
    }
    
    /* 📌 Sticky Header */
    thead tr:nth-child(1) th { position: sticky; top: 0; z-index: 50; background: var(--p-dark); color: white; height: 45px; }
    thead tr:nth-child(2) th { position: sticky; top: 45px; z-index: 50; background: #f8fafc; color: var(--p-slate); font-size: 10px; height: 35px; }

    /* 📦 Cell Box (Plan vs Actual) */
    .cell-box { display: flex; flex-direction: column; height: 60px; min-width: 55px; justify-content: space-between; padding: 4px 0; }
    
    /* Atas: Input Plan */
    .input-grid { 
        width: 100%; border: none; background: transparent; text-align: center; 
        font-weight: 700; font-family: 'JetBrains Mono'; font-size: 12px; color: var(--p-primary);
    }
    .input-grid:focus { background: white; box-shadow: 0 0 0 2px var(--p-primary); border-radius: 6px; outline: none; }

    /* Bawah: Actual Display */
    .act-display { 
        font-family: 'JetBrains Mono'; font-size: 10px; font-weight: 800; text-align: center;
        background: rgba(16, 185, 129, 0.05); color: var(--p-emerald);
        border-top: 1px dashed #e2e8f0; padding-top: 2px;
    }
    .act-shortage { color: var(--p-rose); background: rgba(239, 68, 68, 0.05); } /* Merah jika < Plan */

    /* Row Summary */
    .row-summary { background: #fcfcfd; font-family: 'Orbitron'; font-weight: 800; font-size: 10px; }
    .col-total { width: 100px; background: #f8fafc !important; text-align: center !important; }

    /* Highlights */
    .bg-today { background-color: #eff6ff !important; box-shadow: inset 0 0 0 1px var(--p-primary); }
    .bg-weekend { background-color: #fff1f2 !important; }

    /* 🍬 Custom Scrollbar */
    .matrix-scroll-wrapper::-webkit-scrollbar { height: 12px; width: 8px; }
    .matrix-scroll-wrapper::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; border: 3px solid white; }
</style>

<div class="container-fluid mt-4 mb-5">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-end mb-4 px-2">
        <div>
            <h2 class="font-weight-black m-0" style="letter-spacing: -1px; font-family: 'Orbitron';">MASTER_MATRIX <span class="text-primary">v4.5</span></h2>
            <div class="d-flex gap-3 mt-2">
                <small class="badge badge-outline-primary border px-3 py-1"><i class="fas fa-edit mr-1"></i> TOP: PLAN (BLUE)</small>
                <small class="badge badge-outline-success border px-3 py-1"><i class="fas fa-check-circle mr-1"></i> BTM: ACTUAL (GREEN)</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="shift-master-pill mr-3" style="background: #f1f5f9; padding: 4px; border-radius: 12px;">
                <button type="button" id="btn-s1" class="shift-btn active-s1" onclick="switchShift('s1')">SHIFT_1</button>
                <button type="button" id="btn-s2" class="shift-btn" onclick="switchShift('s2')">SHIFT_2</button>
            </div>

            <form action="" method="GET" class="d-flex">
                <select name="month" class="form-control form-control-sm rounded-lg border-dark font-weight-bold px-3 mr-2" onchange="this.form.submit()">
                    @foreach(range(1, 12) as $m) <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option> @endforeach
                </select>
            </form>
            <a href="{{ route('ppic.mps.index') }}" class="btn btn-primary shadow-sm font-weight-bold rounded-lg px-4">OPEN_DAILY_MPS</a>
        </div>
    </div>

    <div id="status-notif" class="toast-sync" style="position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: var(--p-dark); color: white; padding: 12px 30px; border-radius: 50px; z-index: 1000; display: none; font-weight: 700;">
        <i class="fas fa-sync fa-spin mr-2 text-primary"></i> STREAMING_DATA...
    </div>

    <div class="matrix-container">
        <div class="matrix-scroll-wrapper">
            <table class="table-matrix">
                <thead>
                    <tr>
                        <th rowspan="2" class="sticky-col">PART_NAME_SPEC</th>
                        <th rowspan="2" style="width: 60px;">CUST</th>
                        <th rowspan="2" style="width: 70px;">CAP/H</th>
                        @for($d=1; $d<=$daysInMonth; $d++)
                            @php 
                                $dateStr = "$year-$month-$d";
                                $isToday = (date('Y-m-d', strtotime($dateStr)) == date('Y-m-d'));
                                $isWknd = (date('N', strtotime($dateStr)) >= 6);
                            @endphp
                            <th class="header-day {{ $isToday ? 'bg-primary' : '' }} {{ $isWknd ? 'text-danger' : '' }}">
                                {{ str_pad($d, 2, '0', STR_PAD_LEFT) }}
                            </th>
                        @endfor
                        <th rowspan="2" class="col-total bg-warning text-dark">ACHV_%</th>
                    </tr>
                    <tr>
                        @for($d=1; $d<=$daysInMonth; $d++)
                            <th class="sub-header-day text-center">{{ date('D', strtotime("$year-$month-$d")) }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($parts as $part)
                    @php $rowPlanTotal = 0; $rowActTotal = 0; @endphp
                    <tr>
                        <td class="sticky-col">
                            <div class="text-primary font-weight-bold"># {{ $part->part_no }}</div>
                            <div class="text-muted" style="font-size: 8px;">{{ $part->part_name }}</div>
                        </td>
                        <td class="text-center font-weight-bold text-muted">{{ $part->customer_code }}</td>
                        <td class="text-center bg-light font-weight-bold">{{ $part->cap_per_hour ?? 320 }}</td>
                        
                        @for($d=1; $d<=$daysInMonth; $d++)
                            @php
                                $dStr = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($d, 2, '0', STR_PAD_LEFT);
                                $plan = $planData->get($part->part_no)?->firstWhere('plan_date', $dStr);
                                $s1_val = $plan ? $plan->s1_plan_reg : 0;
                                $s2_val = $plan ? $plan->s2_plan_reg : 0;
                                $target = ($activeShift == 's1') ? $s1_val : $s2_val;
                                
                                // ✨ Data Actual Live dari Controller
                                $actQty = $actualData->get($part->part_no)?->firstWhere('day', $d)->total_ok ?? 0;
                                
                                $rowPlanTotal += ($s1_val + $s2_val);
                                $rowActTotal += $actQty;
                                
                                $isToday = ($dStr == date('Y-m-d'));
                                $isWknd = (date('N', strtotime($dStr)) >= 6);
                            @endphp
                            <td class="{{ $isToday ? 'bg-today' : '' }} {{ $isWknd ? 'bg-weekend' : '' }}">
                                <div class="cell-box">
                                    <input type="number" class="input-grid qty-input" 
                                           value="{{ $target ?: '' }}" 
                                           data-s1="{{ $s1_val }}" data-s2="{{ $s2_val }}"
                                           data-part="{{ $part->part_no }}" data-day="{{ $d }}"
                                           data-cap="{{ $part->cap_per_hour ?? 320 }}"
                                           onchange="autoSave(this)">
                                    
                                    <div class="act-display {{ ($actQty < $target && $actQty > 0) ? 'act-shortage' : '' }}">
                                        {{ $actQty > 0 ? number_format($actQty) : '-' }}
                                    </div>
                                </div>
                            </td>
                        @endfor
                        <td class="col-total">
                            <div class="text-primary" style="font-size: 9px;">P: {{ number_format($rowPlanTotal) }}</div>
                            <div class="{{ $rowActTotal < $rowPlanTotal ? 'text-danger' : 'text-success' }} font-weight-bold">
                                {{ $rowPlanTotal > 0 ? round(($rowActTotal / $rowPlanTotal) * 100) : 0 }}%
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let activeShift = 's1';

    // ✨ Biar licin, kita auto-scroll ke hari ini pas buka halaman
    document.addEventListener("DOMContentLoaded", () => {
        const todayCol = document.querySelector('.bg-today');
        if (todayCol) {
            todayCol.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    });

    function switchShift(shift) {
        activeShift = shift;
        document.getElementById('btn-s1').className = shift === 's1' ? 'shift-btn active-s1' : 'shift-btn';
        document.getElementById('btn-s2').className = shift === 's2' ? 'shift-btn active-s2' : 'shift-btn';
        document.querySelectorAll('.qty-input').forEach(inp => {
            inp.value = inp.getAttribute(`data-${shift}`) || '';
        });
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
                day: input.dataset.day,
                month: '{{ $month }}',
                year: '{{ $year }}',
                qty: input.value,
                cap_per_hour: input.dataset.cap
            })
        }).then(() => {
            setTimeout(() => { statusBox.style.display = "none"; }, 800);
        });
    }
</script>
@endsection