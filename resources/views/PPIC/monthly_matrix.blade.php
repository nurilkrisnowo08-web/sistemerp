@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root {
        --primary: #4361ee; --dark: #0f172a; --slate: #64748b;
        --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
        --weekend: #fff1f2; --today: #eff6ff;
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: var(--dark); }

    /* Matrix Container - Perbaikan Agar Bisa Digeser Licin */
    .matrix-viewport { 
        background: white; 
        border-radius: 20px; 
        border: 1px solid #e2e8f0; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .matrix-wrapper { 
        overflow-x: auto; 
        overflow-y: auto; 
        max-height: 75vh; 
        position: relative;
        -webkit-overflow-scrolling: touch; /* Support Smooth Scroll Mobile */
    }

    /* Table Styling */
    .table-matrix { border-collapse: separate; border-spacing: 0; width: max-content; min-width: 100%; table-layout: fixed; }
    
    .table-matrix th, .table-matrix td { 
        border-right: 1px solid #f1f5f9; 
        border-bottom: 1px solid #f1f5f9; 
        padding: 0; 
        width: 60px; /* Ukuran kolom tanggal */
    }

    /* Sticky Part Number */
    .sticky-col { 
        position: sticky; left: 0; background: white !important; z-index: 40; 
        width: 220px !important; text-align: left !important; font-weight: 800;
        box-shadow: 8px 0 15px -5px rgba(0,0,0,0.08);
        padding: 12px !important;
    }
    
    /* Sticky Headers */
    thead tr:nth-child(1) th { position: sticky; top: 0; z-index: 50; background: var(--dark); color: white; height: 40px; }
    thead tr:nth-child(2) th { position: sticky; top: 40px; z-index: 50; background: #f8fafc; color: var(--slate); font-size: 10px; height: 30px; }

    /* Cell Design (Plan vs Actual) */
    .cell-container { display: flex; flex-direction: column; height: 65px; justify-content: space-between; }
    
    .plan-box { 
        height: 50%; background: transparent; display: flex; align-items: center; 
        border-bottom: 1px dashed #e2e8f0;
    }
    
    .actual-box { 
        height: 50%; background: #fcfdfd; display: flex; align-items: center; 
        justify-content: center; font-family: 'JetBrains Mono'; font-weight: 800;
        font-size: 10px; color: var(--success);
    }

    .input-grid { 
        width: 100%; border: none; background: transparent; text-align: center; 
        font-weight: 700; font-family: 'JetBrains Mono'; font-size: 11px; color: var(--primary);
    }
    .input-grid:focus { background: #fff; outline: 2px solid var(--primary); z-index: 10; }

    /* Helper Styles */
    .bg-weekend { background-color: var(--weekend) !important; }
    .bg-today { background-color: var(--today) !important; }
    .shortage { color: var(--danger) !important; } /* Warna merah jika actual < plan */
    
    .summary-col { background: #f8fafc !important; width: 100px !important; font-family: 'Orbitron'; font-weight: 800; text-align: center !important; }
</style>

<div class="container-fluid mt-4 mb-5">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="font-weight-black m-0" style="letter-spacing: -1px; font-family: 'Orbitron';">MONTHLY_MASTER <span class="text-primary">v4.5</span></h2>
            <div class="d-flex gap-3 mt-2">
                <small class="badge badge-light border text-primary px-3"><i class="fas fa-edit mr-1"></i> BLUE = PLAN (INPUT)</small>
                <small class="badge badge-light border text-success px-3"><i class="fas fa-check-circle mr-1"></i> GREEN = ACTUAL (LIVE)</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="shift-master-pill mr-3">
                <button type="button" id="btn-s1" class="shift-btn active-s1" onclick="switchShift('s1')">S1</button>
                <button type="button" id="btn-s2" class="shift-btn" onclick="switchShift('s2')">S2</button>
            </div>

            <form action="" method="GET" class="d-flex mr-2">
                <select name="month" class="form-control form-control-sm rounded-lg border-dark font-weight-bold px-3 mr-2" onchange="this.form.submit()">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('ppic.mps.index') }}" class="btn btn-primary shadow-sm font-weight-bold rounded-lg px-4">
                <i class="fas fa-bolt mr-2"></i> DAILY_MPS
            </a>
        </div>
    </div>

    <div id="status-notif" class="toast-sync animate__animated animate__fadeInUp" style="position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: var(--dark); color: #fff; padding: 12px 30px; border-radius: 50px; z-index: 1000; display: none;">
        <i class="fas fa-sync fa-spin mr-2 text-primary"></i> UPDATING_DATA_STREAM...
    </div>

    <div class="matrix-viewport">
        <div class="matrix-wrapper">
            <table class="table-matrix">
                <thead>
                    <tr>
                        <th rowspan="2" class="sticky-col">PART_NAME_SERIAL</th>
                        <th rowspan="2" style="width: 50px;">CUST</th>
                        <th rowspan="2" style="width: 60px;">CAP/H</th>
                        @for($d=1; $d<=$daysInMonth; $d++)
                            @php 
                                $dateStr = "$year-$month-$d";
                                $isWknd = (date('N', strtotime($dateStr)) >= 6);
                                $isToday = (date('Y-m-d', strtotime($dateStr)) == date('Y-m-d'));
                            @endphp
                            <th class="header-day {{ $isWknd ? 'text-danger' : '' }} {{ $isToday ? 'bg-primary' : '' }}">
                                {{ str_pad($d, 2, '0', STR_PAD_LEFT) }}
                            </th>
                        @endfor
                        <th rowspan="2" class="summary-col bg-warning text-dark">ACHV_%</th>
                    </tr>
                    <tr>
                        @for($d=1; $d<=$daysInMonth; $d++)
                            <th class="day-name">{{ date('D', strtotime("$year-$month-$d")) }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($parts as $part)
                    @php $rowPlanTotal = 0; $rowActTotal = 0; @endphp
                    <tr>
                        <td class="sticky-col">
                            <div class="text-primary font-weight-bold" style="font-size: 12px;"># {{ $part->part_no }}</div>
                            <small class="text-muted">{{ $part->part_name }}</small>
                        </td>
                        <td class="text-center font-weight-bold text-muted">{{ $part->customer_code }}</td>
                        <td class="text-center font-weight-bold bg-light">{{ $part->cap_per_hour ?? 320 }}</td>
                        
                        @for($d=1; $d<=$daysInMonth; $d++)
                            @php
                                $dStr = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($d, 2, '0', STR_PAD_LEFT);
                                $plan = $planData->get($part->part_no)?->firstWhere('plan_date', $dStr);
                                $s1_val = $plan ? $plan->s1_plan_reg : 0;
                                $s2_val = $plan ? $plan->s2_plan_reg : 0;
                                
                                // ✨ Data Actual Live
                                $actQty = $actualData->get($part->part_no)?->firstWhere('day', $d)->total_ok ?? 0;
                                
                                $rowPlanTotal += ($s1_val + $s2_val);
                                $rowActTotal += $actQty;
                            @endphp
                            <td class="{{ (date('N', strtotime($dStr)) >= 6) ? 'bg-weekend' : '' }} {{ ($dStr == date('Y-m-d')) ? 'bg-today' : '' }}">
                                <div class="cell-container">
                                    <div class="plan-box">
                                        <input type="number" class="input-grid qty-input" 
                                               value="{{ $activeShift == 's1' ? ($s1_val ?: '') : ($s2_val ?: '') }}" 
                                               data-s1="{{ $s1_val }}" data-s2="{{ $s2_val }}"
                                               data-part="{{ $part->part_no }}" data-day="{{ $d }}"
                                               data-cap="{{ $part->cap_per_hour ?? 320 }}" 
                                               data-cust="{{ $part->customer_code ?? 'AMA' }}"
                                               onchange="autoSave(this)">
                                    </div>
                                    <div class="actual-box {{ ($actQty < ($s1_val + $s2_val) && $actQty > 0) ? 'shortage' : '' }}">
                                        {{ $actQty > 0 ? number_format($actQty) : '-' }}
                                    </div>
                                </div>
                            </td>
                        @endfor
                        <td class="summary-col">
                            <div class="text-primary" style="font-size: 9px;">P: {{ number_format($rowPlanTotal) }}</div>
                            <div class="{{ $rowActTotal < $rowPlanTotal ? 'text-danger' : 'text-success' }}">
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
                customer_code: input.dataset.cust,
                day: input.dataset.day,
                month: '{{ $month }}',
                year: '{{ $year }}',
                qty: input.value,
                cap_per_hour: input.dataset.cap
            })
        }).then(() => {
            setTimeout(() => { statusBox.style.display = "none"; }, 800);
            location.reload(); // Refresh untuk update totalan actual jika perlu
        });
    }
</script>
@endsection