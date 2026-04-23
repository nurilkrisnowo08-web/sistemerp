@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root { --excel-border: #000000; --header-bg: #d1d5db; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }

    /* Container Matrix */
    .matrix-wrapper { background: white; border: 2px solid var(--excel-border); border-radius: 4px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    
    /* Excel Style Table */
    .table-matrix { border-collapse: collapse; font-size: 10px; width: 100%; table-layout: fixed; }
    .table-matrix th, .table-matrix td { border: 1px solid var(--excel-border); padding: 4px; text-align: center; vertical-align: middle; }
    
    /* Sticky Header & Column */
    .sticky-col { position: sticky; left: 0; background: #f3f4f6 !important; z-index: 10; width: 180px; text-align: left !important; font-weight: 800; border-right: 2px solid var(--excel-border) !important; }
    .header-master { background: var(--header-bg); font-weight: 800; text-transform: uppercase; }
    .header-day { background: #334155 !important; color: white; width: 35px; cursor: default; }
    
    /* Input Grid Style */
    .input-grid { width: 100%; border: none; background: transparent; text-align: center; font-weight: 700; font-family: 'JetBrains Mono', monospace; font-size: 11px; padding: 2px 0; }
    .input-grid:focus { background: #fffde7; outline: 1px solid #2563eb; }
    .input-grid::-webkit-inner-spin-button { -webkit-appearance: none; }

    /* Weekend Highlighting */
    .bg-weekend { background-color: #fee2e2 !important; }
    .bg-today { background-color: #dcfce7 !important; border: 2px solid #22c55e !important; }

    /* Status Indicator */
    #status-notif { position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: none; }
    .total-row { background: #92d050; font-weight: 900; }
</style>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="font-weight-black mb-0" style="letter-spacing: -1.5px; color: #0f172a;">PRODUCTION_MONTHLY_MASTER</h2>
            <p class="text-muted small font-weight-bold uppercase mb-0">Master Scheduler Matrix - {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <form action="" method="GET" class="d-flex mr-3">
                <select name="month" class="form-control form-control-sm rounded-pill border-dark px-4 mr-2" onchange="this.form.submit()">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endforeach
                </select>
                <select name="year" class="form-control form-control-sm rounded-pill border-dark px-4" onchange="this.form.submit()">
                    <option value="2026" selected>2026</option>
                </select>
            </form>
            <a href="{{ route('ppic.mps.index') }}" class="btn btn-dark shadow font-weight-bold rounded-pill px-4">
                <i class="fas fa-eye mr-2"></i> GO TO DAILY MPS
            </a>
        </div>
    </div>

    <div id="status-notif" class="alert alert-success shadow-lg py-2 px-4 rounded-pill">
        <i class="fas fa-check-circle mr-2"></i> <span id="status-text">Data Synchronized</span>
    </div>

    <div class="matrix-wrapper overflow-auto" style="max-height: 75vh;">
        <table class="table-matrix">
            <thead>
                <tr class="header-master">
                    <th rowspan="2" class="sticky-col" style="top: 0; z-index: 20;">PART NUMBER / IDENTIFICATION</th>
                    <th rowspan="2" style="width: 50px; top: 0; position: sticky; background: var(--header-bg); z-index: 15;">CUST</th>
                    <th rowspan="2" style="width: 50px; top: 0; position: sticky; background: var(--header-bg); z-index: 15;">PROCESS</th>
                    <th rowspan="2" style="width: 50px; top: 0; position: sticky; background: var(--header-bg); z-index: 15;">CAP/H</th>
                    @for($d=1; $d<=$daysInMonth; $d++)
                        @php 
                            $dateStr = "$year-$month-$d";
                            $isWeekend = (date('N', strtotime($dateStr)) >= 6);
                        @endphp
                        <th class="header-day {{ $isWeekend ? 'bg-weekend text-danger' : '' }} {{ date('Y-m-j') == $year.'-'.$month.'-'.$d ? 'bg-today' : '' }}" 
                            style="top: 0; position: sticky; z-index: 15;">
                            {{ $d }}
                        </th>
                    @endfor
                    <th rowspan="2" style="width: 80px; top: 0; position: sticky; background: #1e293b; color: white; z-index: 15;">TOTAL</th>
                </tr>
                <tr class="header-master">
                    @for($d=1; $d<=$daysInMonth; $d++)
                        @php 
                            $dayName = date('D', strtotime("$year-$month-$d"));
                        @endphp
                        <th class="small py-0 {{ $dayName == 'Sun' || $dayName == 'Sat' ? 'text-danger' : '' }}" style="top: 25px; position: sticky; background: var(--header-bg); z-index: 15; font-size: 8px;">
                            {{ $dayName }}
                        </th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($parts as $part)
                <tr>
                    <td class="sticky-col pl-2 font-weight-bold">{{ $part->part_no }}</td>
                    <td class="small">{{ $part->customer_code }}</td>
                    <td>{{ $part->process_qty ?? 1 }}</td>
                    <td class="bg-light font-weight-bold text-primary">{{ $part->cap_per_hour ?? 0 }}</td>
                    
                    @php $rowTotal = 0; @endphp
                    @for($d=1; $d<=$daysInMonth; $d++)
                        @php
                            $dStr = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($d, 2, '0', STR_PAD_LEFT);
                            $currentPlan = $planData->get($part->part_no)?->firstWhere('plan_date', $dStr);
                            $qtyValue = $currentPlan ? ($currentPlan->s1_plan_reg + $currentPlan->s1_plan_ot) : '';
                            if($qtyValue) $rowTotal += $qtyValue;

                            $dayName = date('N', strtotime($dStr));
                        @endphp
                        <td class="{{ $dayName >= 6 ? 'bg-weekend' : '' }}">
                            <input type="number" 
                                   class="input-grid qty-input" 
                                   value="{{ $qtyValue }}" 
                                   data-part="{{ $part->part_no }}"
                                   data-cust="{{ $part->customer_code }}"
                                   data-line="{{ $part->line_code }}"
                                   data-day="{{ $d }}"
                                   onchange="autoSave(this)">
                        </td>
                    @endfor
                    <td class="bg-dark text-warning font-weight-bold" id="total-{{ Str::slug($part->part_no) }}">
                        {{ number_format($rowTotal) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-3 bg-white border border-dark rounded">
        <h6 class="font-weight-bold small uppercase"><i class="fas fa-info-circle mr-2"></i> How to use:</h6>
        <ul class="mb-0 small font-weight-bold">
            <li>Type any number in the date columns to set production target.</li>
            <li>System saves automatically when you move to the next cell.</li>
            <li>Dates marked in <span class="text-danger">Red</span> are weekends.</li>
            <li>These targets will automatically appear in the <strong>Daily MPS</strong> on the respective date.</li>
        </ul>
    </div>
</div>

<script>
    /**
     * ✨ AUTO SAVE LOGIC (AJAX)
     * Langsung simpan ke Database pas Bapak selesai ngetik
     */
    function autoSave(input) {
        const statusBox = document.getElementById('status-notif');
        const statusText = document.getElementById('status-text');
        
        // Visual feedback saat mulai save
        input.style.backgroundColor = "#e0f2fe";
        
        const payload = {
            _token: '{{ csrf_token() }}',
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
        .then(response => response.json())
        .then(data => {
            // Sukses
            input.style.backgroundColor = "transparent";
            input.style.color = "#059669"; // Hijau kalau sukses
            
            // Munculkan notif sebentar
            statusBox.style.display = "block";
            statusText.innerText = "Target Updated: " + input.dataset.part;
            
            setTimeout(() => { 
                statusBox.style.fadeOut; 
                statusBox.style.display = "none";
                input.style.color = "";
            }, 2000);

            recalculateTotal(input.dataset.part);
        })
        .catch(error => {
            console.error('Error:', error);
            input.style.backgroundColor = "#fee2e2"; // Merah kalau error
            alert('Fails to sync database. Please check connection!');
        });
    }

    /**
     * Update Kolom Total (Samping) Tanpa Refresh
     */
    function recalculateTotal(partNo) {
        let total = 0;
        document.querySelectorAll(`.qty-input[data-part="${partNo}"]`).forEach(input => {
            total += parseInt(input.value) || 0;
        });
        
        // Update di kolom total
        // Slug digunakan agar ID valid jika part_no mengandung spasi/karakter aneh
        const safeId = "total-" + partNo.replace(/[^a-z0-9]/gi, '-').toLowerCase();
        const totalCell = document.getElementById(safeId);
        if(totalCell) totalCell.innerText = total.toLocaleString();
    }
</script>
@endsection