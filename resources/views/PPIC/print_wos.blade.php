<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order Sheet - {{ $line_code }} - {{ date('d M Y', strtotime($date)) }}</title>
    <style>
        @page {
            size: landscape;
            margin: 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            color: #000;
        }
        /* KOP SURAT */
        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .logo-company {
            display: flex;
            align-items: center;
        }
        .logo-box {
            width: 50px;
            height: 50px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            font-size: 20px;
            margin-right: 15px;
        }
        .company-name {
            font-weight: bold;
            font-size: 12px;
            line-height: 1.2;
        }
        .doc-title {
            text-align: center;
            flex-grow: 1;
        }
        .doc-title h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .doc-title h3 {
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: normal;
        }
        .doc-meta {
            text-align: right;
            font-size: 11px;
            font-weight: bold;
        }

        /* TABEL UTAMA */
        table.table-wos {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            margin-bottom: 20px;
        }
        .table-wos th, .table-wos td {
            border: 1px solid #000;
            padding: 5px 3px;
            vertical-align: middle;
        }
        .table-wos th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        
        /* Baris Khusus untuk Section/Pembatas */
        .row-section {
            background-color: #e6e6e6;
            font-weight: bold;
            text-align: left;
            padding-left: 10px !important;
            font-size: 11px;
        }

        .text-left { text-align: left; padding-left: 5px !important; }
        .text-bold { font-weight: bold; }
        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
        }

        /* BAGIAN PROBLEM / KETERANGAN BAWAH */
        .bottom-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            border: 1px solid #000;
            padding: 10px;
        }
        .problem-box {
            width: 60%;
        }
        .problem-table {
            width: 100%;
            border-collapse: collapse;
        }
        .problem-table td, .problem-table th {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .problem-table th { background: #f2f2f2; width: 150px;}

        /* TANDA TANGAN */
        .signature-box {
            width: 35%;
            display: flex;
            justify-content: space-around;
        }
        .sign-col {
            text-align: center;
            width: 30%;
        }
        .sign-col .title {
            border: 1px solid #000;
            background: #f2f2f2;
            padding: 5px;
            font-weight: bold;
            font-size: 10px;
        }
        .sign-col .space {
            border: 1px solid #000;
            border-top: none;
            height: 60px;
        }
        .sign-col .name {
            border: 1px solid #000;
            border-top: none;
            padding: 5px;
            font-size: 10px;
        }

        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; font-weight: bold; cursor: pointer;">🖨️ PRINT WOS</button>
    </div>

    {{-- KOP SURAT --}}
    <div class="kop-surat">
        <div class="logo-company">
            <div class="logo-box">AMA</div>
            <div class="company-name">
                PT. Asalta Mandiri Agung<br>
                <span style="font-weight: normal; font-size: 10px;">Logistic & Delivery Dept.</span>
            </div>
        </div>
        <div class="doc-title">
            <h1>WORK ORDER SHEET (WOS)</h1>
            <h3>STAMPING {{ str_contains(strtoupper($line_code), 'BIG') ? 'BIG PRESS' : 'SMALL PRESS' }}</h3>
        </div>
        <div class="doc-meta">
            TGL: {{ date('d F Y', strtotime($date)) }}<br>
            REVISI: 0
        </div>
    </div>

    {{-- TABEL WOS --}}
    <table class="table-wos">
        <thead>
            <tr>
                <th rowspan="2" width="20">NO</th>
                <th rowspan="2" width="150">PART NUMBER / IDENTIFICATION</th>
                <th rowspan="2" width="50">CUST</th>
                <th rowspan="2" width="30">MP</th>
                <th rowspan="2" width="30">PROC</th>
                <th rowspan="2" width="40">QTY<br>LOT</th>
                <th rowspan="2" width="40">CAP /<br>HOUR</th>
                <th rowspan="2" width="50">TOTAL<br>TARGET</th>
                <th rowspan="2" width="40">DANDORY<br>(MIN)</th>
                <th colspan="2">PLAN HOURS</th>
                <th colspan="2">PLAN PRODUKSI</th>
                <th rowspan="2" width="50">TOTAL<br>PLAN</th>
                <th rowspan="2" width="60">M/C LINE</th>
                <th colspan="3">ACTUAL PRODUKSI (SHIFT {{ str_replace('S', '', $shift) }})</th>
                <th rowspan="2" width="80">MATERIAL REQ<br>(SERAH TERIMA)</th>
            </tr>
            <tr>
                <th width="40">START</th>
                <th width="40">AKHIR</th>
                <th width="40">REG</th>
                <th width="40">OT</th>
                <th width="40">OK</th>
                <th width="40">NG</th>
                <th width="40">WH</th>
            </tr>
        </thead>
        <tbody>
            <tr class="row-section">
                <td colspan="20">TOTAL WORKING HOURS / SHIFT {{ str_replace('S', '', $shift) }} : {{$plans->sum('total_target') > 0 ? number_format(($plans->sum('total_target') / 320) + ($plans->sum('dandory_time') / 60), 1) : 0 }} Jam</td>
            </tr>

            @php 
                $defaultStart = ($shift == 'S1') ? "07:30" : "19:30";
                $lastFinish =$defaultStart;
            @endphp

            @foreach($plans as $index =>$plan)
            @php
                // Hitung Jam
                $target = ($shift == 'S1') ? ($plan->s1_plan_reg +$plan->s1_plan_ot) : ($plan->s2_plan_reg +$plan->s2_plan_ot);
                $durationHours = ($plan->cap_per_hour > 0 && $target > 0) ? ($target / $plan->cap_per_hour) + (($plan->dandory_time ?? 15) / 60) : 0;
                $start =$lastFinish;
                $finish = date('H:i', strtotime($start . " + " . round($durationHours * 60) . " minutes"));
                $lastFinish =$finish;

                // Cari data Batch buat info Material
                $batchData = $batches->where('plan_id',$plan->id)->first();
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left text-bold">{{ $plan->part_no }}</td>
                <td>{{ $plan->customer_code }}</td>
                <td>{{ $plan->manpower }}</td>
                <td>{{ $plan->process_qty }}</td>
                <td>{{ $plan->qty_lot }}</td>
                <td>{{ $plan->cap_per_hour }}</td>
                <td class="text-bold">{{ number_format($target) }}</td>
                <td>{{ $plan->dandory_time }}</td>
                <td>{{ $start }}</td>
                <td>{{ $finish }}</td>
                <td>{{ ($shift == 'S1') ? $plan->s1_plan_reg :$plan->s2_plan_reg }}</td>
                <td>{{ ($shift == 'S1') ? $plan->s1_plan_ot :$plan->s2_plan_ot }}</td>
                <td class="text-bold">{{ number_format($target) }}</td>
                <td class="text-bold">{{ $plan->line_code }}</td>
                
                {{-- Kolom Kosong untuk diisi Actual oleh Operator --}}
                <td></td>
                <td></td>
                <td></td>

                {{-- INFO MATERIAL (SERAH TERIMA) --}}
                <td class="text-left" style="font-size: 8px;">
                    @if($batchData)
                        <strong>{{ $batchData->material_code }}</strong><br>
                        {{ $batchData->qty_ambil_pcs }} Sheet/Lembar<br>
                        <span style="color: #666;">(Coil: {{ $batchData->coil_id }})</span>
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach

            {{-- Baris Kosong Tambahan Biar Kertas Kelihatan Penuh (Optional) --}}
            @for($i = count($plans);$i < max(count($plans), 8);$i++)
            <tr>
                <td>{{ $i + 1 }}</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            @endfor

            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="7" class="text-left">NO PLANING : {{ date('ymd') }}-{{ $line_code }}</td>
                <td>{{ number_format($plans->sum('total_target')) }}</td>
                <td></td><td></td><td></td>
                <td>{{ number_format($plans->sum('s1_plan_reg') +$plans->sum('s2_plan_reg')) }}</td>
                <td>{{ number_format($plans->sum('s1_plan_ot') +$plans->sum('s2_plan_ot')) }}</td>
                <td>{{ number_format($plans->sum('total_target')) }}</td>
                <td colspan="5"></td>
            </tr>
        </tbody>
    </table>

    {{-- BAGIAN BAWAH: PROBLEM & TANDA TANGAN --}}
    <div class="bottom-section">
        {{-- Tabel Problem / Remark --}}
        <div class="problem-box">
            <table class="problem-table">
                <tr>
                    <th>PROBLEM</th>
                    <td><br><br></td>
                </tr>
                <tr>        