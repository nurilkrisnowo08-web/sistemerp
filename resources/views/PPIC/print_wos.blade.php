<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WOS - {{ $line_code }} - {{ date('d M Y', strtotime($date)) }}</title>
    <style>
        @page { size: landscape; margin: 10mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; margin: 0; padding: 0; color: #111; }
        .no-print-area { text-align: right; margin-bottom: 15px; }
        .btn-print { background: #0f172a; color: white; border: none; padding: 10px 25px; font-size: 14px; font-weight: bold; border-radius: 8px; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header-container { display: table; width: 100%; border-bottom: 4px double #222; padding-bottom: 15px; margin-bottom: 15px; }
        .header-container > div { display: table-cell; vertical-align: middle; }
        .logo-section { width: 30%; }
        .logo-wrapper { display: inline-block; vertical-align: middle; width: 55px; height: 55px; border: 2px solid #111; border-radius: 50%; text-align: center; line-height: 55px; font-size: 18px; font-weight: 900; margin-right: 12px; letter-spacing: 1px;}
        .company-info { display: inline-block; vertical-align: middle; line-height: 1.4; font-size: 12px; font-weight: 800; }
        .company-info span { font-size: 10px; font-weight: 500; color: #444; }
        .title-section { width: 40%; text-align: center; }
        .title-section h1 { margin: 0; font-size: 26px; font-weight: 900; letter-spacing: 1.5px; text-transform: uppercase; }
        .title-section h3 { margin: 5px 0 0 0; font-size: 15px; font-weight: 600; color: #333; letter-spacing: 1px; }
        .meta-section { width: 30%; text-align: right; font-size: 11px; font-weight: bold; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; text-align: center; }
        th, td { border: 1px solid #000; padding: 6px 3px; vertical-align: middle; }
        th { background-color: #e2e8f0; font-weight: 800; font-size: 9px; text-transform: uppercase; color: #111; }
        .text-left { text-align: left !important; padding-left: 8px !important; }
        .text-bold { font-weight: 800; }
        .row-summary { background-color: #f1f5f9; font-weight: 800; font-size: 11px; border-top: 3px solid #111; }
        .row-footer { background-color: #e2e8f0; font-weight: 800; font-size: 11px; }
        .bottom-wrapper { width: 100%; margin-top: 20px; border-collapse: collapse; border: none; }
        .bottom-wrapper > tbody > tr > td { border: none; padding: 0; vertical-align: top; }
        .box-problem { width: 100%; border-collapse: collapse; }
        .box-problem th { width: 150px; text-align: left; padding: 10px; background-color: #f8fafc; font-size: 10px; border: 1px solid #000; }
        .box-problem td { border: 1px solid #000; height: 30px; }
        .box-signature { width: 100%; border-collapse: collapse; margin-left: 20px; }
        .box-signature th { background-color: #e2e8f0; padding: 8px; font-size: 10px; border: 1px solid #000; }
        .box-signature td { height: 75px; vertical-align: bottom; padding-bottom: 8px; font-size: 10px; font-weight: bold; border: 1px solid #000; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="no-print no-print-area">
        <button class="btn-print" onclick="window.print()">🖨️ PRINT WOS (FULL DAY)</button>
    </div>

    @php
        $lineUpper = strtoupper($line_code);
        $pressType = (str_contains($lineUpper, 'C') || str_contains($lineUpper, 'SMALL')) ? 'SMALL PRESS' : 'BIG PRESS';
        
        $grandTarget = 0; $grandStroke = 0; $grandReg = 0; $grandOt = 0;
        $totalHoursS1 = 0; $totalHoursS2 = 0;
    @endphp

    <div class="header-container">
        <div class="logo-section">
            <div class="logo-wrapper">AMA</div>
            <div class="company-info">PT. Asalta Mandiri Agung<br><span>Logistic & Delivery Dept.</span></div>
        </div>
        <div class="title-section">
            <h1>WORK ORDER SHEET (WOS)</h1>
            <h3>STAMPING {{ $pressType }}</h3>
        </div>
        <div class="meta-section">TGL: {{ date('d F Y', strtotime($date)) }}<br>REVISI: 0</div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" width="20">NO</th>
                <th rowspan="2" width="140">PART NUMBER / IDENTIFICATION</th>
                <th rowspan="2" width="45">CUST</th>
                <th rowspan="2" width="25">MP</th>
                <th rowspan="2" width="30">PROC</th>
                <th rowspan="2" width="35">QTY<br>LOT</th>
                <th rowspan="2" width="35">CAP /<br>HOUR</th>
                <th rowspan="2" width="45">TOTAL<br>TARGET</th>
                <th rowspan="2" width="45">TOTAL<br>STROKE</th>
                <th rowspan="2" width="45">DANDORY<br>(MIN)</th>
                <th colspan="2">PLAN HOURS</th>
                <th colspan="2">PLAN PRODUKSI</th>
                <th rowspan="2" width="45">TOTAL<br>PLAN</th>
                <th rowspan="2" width="50">M/C LINE</th>
                <th rowspan="2" width="35">JAM</th>
                <th colspan="3">ACTUAL PRODUKSI</th>
                <th rowspan="2" width="90">MATERIAL REQ<br>(SERAH TERIMA)</th>
            </tr>
            <tr>
                <th width="40">START</th><th width="40">AKHIR</th><th width="40">REG</th><th width="40">OT</th>
                <th width="35">OK</th><th width="35">NG</th><th width="35">WH</th>
            </tr>
        </thead>
        <tbody>
            
            {{-- ================= SHIFT 1 ================= --}}
            @if(isset($plansS1) && count($plansS1) > 0)
                @php
                    foreach($plansS1 as $p) {
                        $t = $p->s1_plan_reg + $p->s1_plan_ot;
                        $dur = ($p->cap_per_hour > 0 && $t > 0) ? ($t / $p->cap_per_hour) + (($p->dandory_time ?? 15) / 60) : 0;
                        $totalHoursS1 += $dur;
                    }
                @endphp
                <tr class="row-summary">
                    <td colspan="21" class="text-left" style="background-color: #f1f5f9;">
                        TOTAL WORKING HOURS / SHIFT 1 (DAY OPS) : <span style="color:#e63946;">{{ number_format($totalHoursS1, 1) }} Jam</span>
                    </td>
                </tr>
                @php $lastFinish = "07:30"; $no = 1; @endphp
                @foreach($plansS1 as $plan)
                @php
                    $target = $plan->s1_plan_reg + $plan->s1_plan_ot;
                    $stroke = $target * $plan->process_qty; 
                    $grandTarget += $target; $grandStroke += $stroke; $grandReg += $plan->s1_plan_reg; $grandOt += $plan->s1_plan_ot;
                    
                    $dur = ($plan->cap_per_hour > 0 && $target > 0) ? ($target / $plan->cap_per_hour) + (($plan->dandory_time ?? 15) / 60) : 0;
                    $start = $lastFinish; 
                    $finish = date('H:i', strtotime($start . " + " . round($dur * 60) . " minutes")); 
                    $lastFinish = $finish;
                    
                    $batchData = null;
                    if(isset($batches)) {
                        $batchData = $batches->where('plan_id', $plan->id)->where('shift', 'Pagi')->first();
                    }
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td class="text-left text-bold">{{ $plan->part_no }}</td>
                    <td class="text-bold">{{ $plan->customer_code }}</td>
                    <td>{{ $plan->manpower }}</td><td>{{ $plan->process_qty }}</td><td>{{ $plan->qty_lot }}</td>
                    <td class="text-bold">{{ $plan->cap_per_hour }}</td>
                    <td class="text-bold" style="font-size: 11px;">{{ number_format($target) }}</td>
                    <td class="text-bold" style="font-size: 11px; color: #1e40af;">{{ number_format($stroke) }}</td>
                    <td>{{ $plan->dandory_time }}</td><td class="text-bold">{{ $start }}</td><td class="text-bold">{{ $finish }}</td>
                    <td>{{ $plan->s1_plan_reg }}</td><td>{{ $plan->s1_plan_ot }}</td>
                    <td class="text-bold" style="font-size: 11px;">{{ number_format($target) }}</td>
                    <td class="text-bold">{{ $plan->line_code }}</td>
                    
                    {{-- ✨ AUTO FILL ACTUAL ✨ --}}
                    <td></td> 
                    <td class="text-bold" style="color: #059669;">{{ ($batchData && $batchData->qty_hasil_ok > 0) ? number_format($batchData->qty_hasil_ok) : '' }}</td>
                    <td class="text-bold" style="color: #dc2626;">{{ ($batchData && $batchData->qty_hasil_ng > 0) ? number_format($batchData->qty_hasil_ng) : '' }}</td>
                    <td class="text-bold" style="color: #d97706;">{{ ($batchData && $batchData->qty_return_warehouse > 0) ? number_format($batchData->qty_return_warehouse) : '' }}</td>
                    
                    <td class="text-left" style="font-size: 8px; line-height: 1.2;">
                        @if($batchData)
                            <strong>{{ $batchData->material_code }}</strong><br>
                            Ambil: <b>{{ $batchData->qty_ambil_pcs }}</b> Sht<br>
                            @if(($batchData->qty_return_warehouse ?? 0) > 0)
                                <span style="color: #dc2626; font-weight: bold;">Rtn: {{ $batchData->qty_return_warehouse }} Sht</span><br>
                                Pakai: <b>{{ $batchData->qty_ambil_pcs - $batchData->qty_return_warehouse }}</b> Sht<br>
                            @endif
                            <small style="color: #555;">Coil: {{ $batchData->coil_id }}</small>
                        @else - @endif
                    </td>
                </tr>
                @endforeach
            @endif

            {{-- ================= SHIFT 2 ================= --}}
            @if(isset($plansS2) && count($plansS2) > 0)
                @php
                    foreach($plansS2 as $p) {
                        $t = $p->s2_plan_reg + $p->s2_plan_ot;
                        $dur = ($p->cap_per_hour > 0 && $t > 0) ? ($t / $p->cap_per_hour) + (($p->dandory_time ?? 15) / 60) : 0;
                        $totalHoursS2 += $dur;
                    }
                @endphp
                <tr class="row-summary">
                    <td colspan="21" class="text-left" style="background-color: #cbd5e1;">
                        TOTAL WORKING HOURS / SHIFT 2 (NIGHT OPS) : <span style="color:#e63946;">{{ number_format($totalHoursS2, 1) }} Jam</span>
                    </td>
                </tr>
                @php $lastFinish = "19:30"; $no = 1; @endphp
                @foreach($plansS2 as $plan)
                @php
                    $target = $plan->s2_plan_reg + $plan->s2_plan_ot;
                    $stroke = $target * $plan->process_qty; 
                    $grandTarget += $target; $grandStroke += $stroke; $grandReg += $plan->s2_plan_reg; $grandOt += $plan->s2_plan_ot;
                    
                    $dur = ($plan->cap_per_hour > 0 && $target > 0) ? ($target / $plan->cap_per_hour) + (($plan->dandory_time ?? 15) / 60) : 0;
                    $start = $lastFinish; 
                    $finish = date('H:i', strtotime($start . " + " . round($dur * 60) . " minutes")); 
                    $lastFinish = $finish;
                    
                    $batchData = null;
                    if(isset($batches)) {
                        $batchData = $batches->where('plan_id', $plan->id)->where('shift', 'Malam')->first();
                    }
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td class="text-left text-bold">{{ $plan->part_no }}</td>
                    <td class="text-bold">{{ $plan->customer_code }}</td>
                    <td>{{ $plan->manpower }}</td><td>{{ $plan->process_qty }}</td><td>{{ $plan->qty_lot }}</td>
                    <td class="text-bold">{{ $plan->cap_per_hour }}</td>
                    <td class="text-bold" style="font-size: 11px;">{{ number_format($target) }}</td>
                    <td class="text-bold" style="font-size: 11px; color: #1e40af;">{{ number_format($stroke) }}</td>
                    <td>{{ $plan->dandory_time }}</td><td class="text-bold">{{ $start }}</td><td class="text-bold">{{ $finish }}</td>
                    <td>{{ $plan->s2_plan_reg }}</td><td>{{ $plan->s2_plan_ot }}</td>
                    <td class="text-bold" style="font-size: 11px;">{{ number_format($target) }}</td>
                    <td class="text-bold">{{ $plan->line_code }}</td>
                    
                    {{-- ✨ AUTO FILL ACTUAL ✨ --}}
                    <td></td>
                    <td class="text-bold" style="color: #059669;">{{ ($batchData && $batchData->qty_hasil_ok > 0) ? number_format($batchData->qty_hasil_ok) : '' }}</td>
                    <td class="text-bold" style="color: #dc2626;">{{ ($batchData && $batchData->qty_hasil_ng > 0) ? number_format($batchData->qty_hasil_ng) : '' }}</td>
                    <td class="text-bold" style="color: #d97706;">{{ ($batchData && $batchData->qty_return_warehouse > 0) ? number_format($batchData->qty_return_warehouse) : '' }}</td>

                    <td class="text-left" style="font-size: 8px; line-height: 1.2;">
                        @if($batchData)
                            <strong>{{ $batchData->material_code }}</strong><br>
                            Ambil: <b>{{ $batchData->qty_ambil_pcs }}</b> Sht<br>
                            @if(($batchData->qty_return_warehouse ?? 0) > 0)
                                <span style="color: #dc2626; font-weight: bold;">Rtn: {{ $batchData->qty_return_warehouse }} Sht</span><br>
                                Pakai: <b>{{ $batchData->qty_ambil_pcs - $batchData->qty_return_warehouse }}</b> Sht<br>
                            @endif
                            <small style="color: #555;">Coil: {{ $batchData->coil_id }}</small>
                        @else - @endif
                    </td>
                </tr>
                @endforeach
            @endif

            {{-- PADDING KOSONG BIAR KERTAS PENUH & RAPI --}}
            @php $totalRows = (isset($plansS1) ? count($plansS1) : 0) + (isset($plansS2) ? count($plansS2) : 0); @endphp
            @for($i = $totalRows; $i < 12; $i++)
            <tr>
                <td>-</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            @endfor

            {{-- BARIS REKAPITULASI BAWAH --}}
            <tr class="row-footer">
                <td colspan="7" class="text-left" style="padding: 10px;">NO PLANING : {{ date('ymd') }}-{{ $line_code }}</td>
                <td style="font-size: 12px;">{{ number_format($grandTarget) }}</td>
                <td style="font-size: 12px; color: #1e40af;">{{ number_format($grandStroke) }}</td>
                <td></td><td></td><td></td>
                <td style="font-size: 11px;">{{ number_format($grandReg) }}</td>
                <td style="font-size: 11px;">{{ number_format($grandOt) }}</td>
                <td style="font-size: 12px;">{{ number_format($grandTarget) }}</td>
                <td colspan="6"></td>
            </tr>
        </tbody>
    </table>

    <table class="bottom-wrapper">
        <tr>
            <td style="width: 50%; padding-right: 20px;">
                <table class="box-problem">
                    <tr><th>PROBLEM</th><td></td></tr>
                    <tr><th>TEMPORARY ACTION</th><td></td></tr>
                    <tr><th>COUNTER MEASURE</th><td></td></tr>
                </table>
            </td>
            <td style="width: 50%;">
                <table class="box-signature">
                    <tr>
                        <th width="25%">DIBUAT</th>
                        <th width="25%">DIPERIKSA</th>
                        <th width="25%">DISETUJUI</th>
                        <th width="25%">DITERIMA</th>
                    </tr>
                    <tr>
                        <td>PPIC</td>
                        <td>LEADER PPIC</td>
                        <td>MANAGER</td>
                        <td>PRODUKSI</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>