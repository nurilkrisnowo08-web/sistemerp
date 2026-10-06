<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WOS - {{ $line_code }} - {{ date('d M Y', strtotime($date)) }}</title>
    <style>
        @page {
            size: landscape;
            margin: 10mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            color: #111;
        }

        /* TOMBOL PRINT */
        .no-print-area { text-align: right; margin-bottom: 15px; }
        .btn-print { background: #0f172a; color: white; border: none; padding: 10px 25px; font-size: 14px; font-weight: bold; border-radius: 8px; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        
        /* KOP SURAT (HEADER) */
        .header-container {
            display: table;
            width: 100%;
            border-bottom: 4px double #222;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .header-container > div { display: table-cell; vertical-align: middle; }
        
        .logo-section { width: 30%; }
        .logo-wrapper { display: inline-block; vertical-align: middle; width: 55px; height: 55px; border: 2px solid #111; border-radius: 50%; text-align: center; line-height: 55px; font-size: 18px; font-weight: 900; margin-right: 12px; letter-spacing: 1px;}
        .company-info { display: inline-block; vertical-align: middle; line-height: 1.4; font-size: 12px; font-weight: 800; }
        .company-info span { font-size: 10px; font-weight: 500; color: #444; }

        .title-section { width: 40%; text-align: center; }
        .title-section h1 { margin: 0; font-size: 26px; font-weight: 900; letter-spacing: 1.5px; text-transform: uppercase; }
        .title-section h3 { margin: 5px 0 0 0; font-size: 15px; font-weight: 600; color: #333; letter-spacing: 1px; }

        .meta-section { width: 30%; text-align: right; font-size: 11px; font-weight: bold; line-height: 1.6; }

        /* TABEL UTAMA */
        table { width: 100%; border-collapse: collapse; text-align: center; }
        th, td { border: 1px solid #000; padding: 7px 4px; vertical-align: middle; }
        th { background-color: #e2e8f0; font-weight: 800; font-size: 9px; text-transform: uppercase; color: #111; }
        
        .text-left { text-align: left !important; padding-left: 8px !important; }
        .text-bold { font-weight: 800; }
        
        .row-summary { background-color: #f1f5f9; font-weight: 800; font-size: 11px; }
        .row-footer { background-color: #e2e8f0; font-weight: 800; font-size: 11px; }

        /* TABEL PROBLEM & TANDA TANGAN */
        .bottom-wrapper { width: 100%; margin-top: 20px; border-collapse: collapse; border: none; }
        .bottom-wrapper > tbody > tr > td { border: none; padding: 0; vertical-align: top; }
        
        .box-problem { width: 100%; border-collapse: collapse; }
        .box-problem th { width: 160px; text-align: left; padding: 10px; background-color: #f8fafc; font-size: 10px; border: 1px solid #000; }
        .box-problem td { border: 1px solid #000; height: 30px; }

        .box-signature { width: 100%; border-collapse: collapse; margin-left: 20px; }
        .box-signature th { background-color: #e2e8f0; padding: 8px; font-size: 10px; border: 1px solid #000; }
        .box-signature td { height: 75px; vertical-align: bottom; padding-bottom: 8px; font-size: 10px; font-weight: bold; border: 1px solid #000; }

        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

    <div class="no-print no-print-area">
        <button class="btn-print" onclick="window.print()">🖨️ PRINT WOS (A4 Landscape)</button>
    </div>

    @php
        // 1. LOGIKA JENIS MESIN (BIG / SMALL)
        $lineUpper = strtoupper($line_code);
        if (str_contains($lineUpper, 'C') || str_contains($lineUpper, 'SMALL')) {
            $pressType = 'SMALL PRESS';
        } else {
            $pressType = 'BIG PRESS'; // Default ke Big Press untuk Line A / B
        }

        // 2. KALKULASI MANUAL TOTAL TARGET & JAM KERJA
        $shiftNum = str_replace('S', '', $shift);
        $grandTarget = 0;
        $grandReg = 0;
        $grandOt = 0;
        $totalHours = 0;

        foreach($plans as $p) {
            $t = ($shift == 'S1') ? ($p->s1_plan_reg + $p->s1_plan_ot) : ($p->s2_plan_reg + $p->s2_plan_ot);
            $grandTarget += $t;
            
            $reg = ($shift == 'S1') ? $p->s1_plan_reg : $p->s2_plan_reg;
            $ot = ($shift == 'S1') ? $p->s1_plan_ot : $p->s2_plan_ot;
            $grandReg += $reg;
            $grandOt += $ot;

            $dandory = $p->dandory_time ?? 15;
            $dur = ($p->cap_per_hour > 0 && $t > 0) ? ($t / $p->cap_per_hour) + ($dandory / 60) : 0;
            $totalHours += $dur;
        }
    @endphp

    {{-- KOP SURAT --}}
    <div class="header-container">
        <div class="logo-section">
            <div class="logo-wrapper">AMA</div>
            <div class="company-info">
                PT. Asalta Mandiri Agung<br>
                <span>Logistic & Delivery Dept.</span>
            </div>
        </div>
        <div class="title-section">
            <h1>WORK ORDER SHEET (WOS)</h1>
            <h3>STAMPING {{ $pressType }}</h3>
        </div>
        <div class="meta-section">
            TGL: {{ date('d F Y', strtotime($date)) }}<br>
            REVISI: 0
        </div>
    </div>

    {{-- TABEL WOS --}}
    <table>
        <thead>
            <tr>
                <th rowspan="2" width="25">NO</th>
                <th rowspan="2" width="160">PART NUMBER / IDENTIFICATION</th>
                <th rowspan="2" width="50">CUST</th>
                <th rowspan="2" width="30">MP</th>
                <th rowspan="2" width="30">PROC</th>
                <th rowspan="2" width="40">QTY<br>LOT</th>
                <th rowspan="2" width="40">CAP /<br>HOUR</th>
                <th rowspan="2" width="50">TOTAL<br>TARGET</th>
                <th rowspan="2" width="45">DANDORY<br>(MIN)</th>
                <th colspan="2">PLAN HOURS</th>
                <th colspan="2">PLAN PRODUKSI</th>
                <th rowspan="2" width="50">TOTAL<br>PLAN</th>
                <th rowspan="2" width="60">M/C LINE</th>
                <th colspan="3">ACTUAL PRODUKSI (SHIFT {{ $shiftNum }})</th>
                <th rowspan="2" width="100">MATERIAL REQ<br>(SERAH TERIMA)</th>
            </tr>
            <tr>
                <th width="45">START</th>
                <th width="45">AKHIR</th>
                <th width="45">REG</th>
                <th width="45">OT</th>
                <th width="35">OK</th>
                <th width="35">NG</th>
                <th width="35">WH</th>
            </tr>
        </thead>
        <tbody>
            {{-- BARIS INFO TOTAL JAM KERJA --}}
            <tr class="row-summary">
                <td colspan="19" class="text-left">
                    TOTAL WORKING HOURS / SHIFT {{ $shiftNum }} : <span style="color:#e63946;">{{ number_format($totalHours, 1) }} Jam</span>
                </td>
            </tr>

            @php 
                $defaultStart = ($shift == 'S1') ? "07:30" : "19:30";
                $lastFinish = $defaultStart;
            @endphp

            @foreach($plans as $index => $plan)
            @php
                // Hitung individual untuk baris ini
                $target = ($shift == 'S1') ? ($plan->s1_plan_reg + $plan->s1_plan_ot) : ($plan->s2_plan_reg + $plan->s2_plan_ot);
                $durationHours = ($plan->cap_per_hour > 0 && $target > 0) ? ($target / $plan->cap_per_hour) + (($plan->dandory_time ?? 15) / 60) : 0;
                $start = $lastFinish;
                $finish = date('H:i', strtotime($start . " + " . round($durationHours * 60) . " minutes"));
                $lastFinish = $finish;

                $batchData = $batches->where('plan_id', $plan->id)->first();
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left text-bold">{{ $plan->part_no }}</td>
                <td class="text-bold">{{ $plan->customer_code }}</td>
                <td>{{ $plan->manpower }}</td>
                <td>{{ $plan->process_qty }}</td>
                <td>{{ $plan->qty_lot }}</td>
                <td class="text-bold">{{ $plan->cap_per_hour }}</td>
                <td class="text-bold" style="font-size: 11px;">{{ number_format($target) }}</td>
                <td>{{ $plan->dandory_time }}</td>
                <td class="text-bold">{{ $start }}</td>
                <td class="text-bold">{{ $finish }}</td>
                <td>{{ ($shift == 'S1') ? $plan->s1_plan_reg : $plan->s2_plan_reg }}</td>
                <td>{{ ($shift == 'S1') ? $plan->s1_plan_ot : $plan->s2_plan_ot }}</td>
                <td class="text-bold" style="font-size: 11px;">{{ number_format($target) }}</td>
                <td class="text-bold">{{ $plan->line_code }}</td>
                
                {{-- Kolom Kosong Actual --}}
                <td></td>
                <td></td>
                <td></td>

                {{-- INFO MATERIAL (SERAH TERIMA) --}}
                <td class="text-left" style="font-size: 8px; line-height: 1.2;">
                    @if($batchData)
                        <strong>{{ $batchData->material_code }}</strong><br>
                        <span style="font-size: 9px; font-weight: bold;">{{ $batchData->qty_ambil_pcs }}</span> Sheet/Lembar<br>
                        (Coil: {{ $batchData->coil_id }})
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach

            {{-- Baris Kosong Tambahan Biar Kertas Kelihatan Estetik & Proporsional --}}
            @for($i = count($plans); $i < max(count($plans), 8); $i++)
            <tr>
                <td>{{ $i + 1 }}</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            @endfor

            {{-- BARIS REKAPITULASI (NO PLANING & TOTAL KESELURUHAN) --}}
            <tr class="row-footer">
                <td colspan="7" class="text-left" style="padding: 10px;">NO PLANING : {{ date('ymd') }}-{{ $line_code }}</td>
                <td style="font-size: 12px;">{{ number_format($grandTarget) }}</td>
                <td></td><td></td><td></td>
                <td style="font-size: 11px;">{{ number_format($grandReg) }}</td>
                <td style="font-size: 11px;">{{ number_format($grandOt) }}</td>
                <td style="font-size: 12px;">{{ number_format($grandTarget) }}</td>
                <td colspan="5"></td>
            </tr>
        </tbody>
    </table>

    {{-- BAGIAN BAWAH: PROBLEM & TANDA TANGAN (Menggunakan Super Grid Layout biar Rapi pas di Print) --}}
    <table class="bottom-wrapper">
        <tr>
            <td style="width: 55%; padding-right: 25px;">
                {{-- TABEL KETERANGAN PROBLEM --}}
                <table class="box-problem">
                    <tr>
                        <th>PROBLEM</th>
                        <td></td>
                    </tr>
                    <tr>
                        <th>TEMPORARY ACTION</th>
                        <td></td>
                    </tr>
                    <tr>
                        <th>COUNTER MEASURE</th>
                        <td></td>
                    </tr>
                </table>
            </td>
            <td style="width: 45%;">
                {{-- TABEL TANDA TANGAN --}}
                <table class="box-signature">
                    <tr>
                        <th width="33%">DIBUAT</th>
                        <th width="33%">DIPERIKSA</th>
                        <th width="34%">DISETUJUI</th>
                    </tr>
                    <tr>
                        <td>PPIC / LEADER</td>
                        <td>SPV PRODUKSI</td>
                        <td>MANAGER</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>