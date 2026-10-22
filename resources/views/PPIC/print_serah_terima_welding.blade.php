<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WOS WELDING - {{ strtoupper($line_code) }}</title>
    <!-- Font Profesional Industri -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Setup Kertas A4 Memanjang (Landscape) */
        @page { size: A4 landscape; margin: 8mm 10mm; }
        body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; font-size: 9px; margin: 0; padding: 0; color: #000; background-color: #fff; }
        
        /* Navigasi Print (Sembunyi saat dicetak) */
        .no-print-area { text-align: right; margin-bottom: 15px; padding: 12px 20px; background: #f8fafc; border-bottom: 2px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center;}
        .form-title-web { font-size: 13px; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 1px;}
        .btn { padding: 8px 16px; font-size: 11px; font-weight: 800; border-radius: 6px; border: none; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif; transition: all 0.2s;}
        .btn-back { background: #e2e8f0; color: #475569; margin-right: 10px; }
        .btn-print { background: #2563eb; color: white; box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2); }

        /* HEADER DOKUMEN */
        .header-wrap { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; border-bottom: 3px double #000; padding-bottom: 10px;}
        
        .logo-wrap { display: flex; align-items: center; width: 33%; }
        .logo-img { height: 38px; margin-right: 10px; }
        .comp-info { font-size: 10px; font-weight: 900; line-height: 1.2; text-transform: uppercase;}
        .comp-dept { font-size: 8px; font-weight: 700; color: #334155; }

        .title-wrap { width: 34%; text-align: center; }
        .title-main { font-size: 16px; font-weight: 900; margin: 0; letter-spacing: 1px;}
        .title-sub { font-size: 12px; font-weight: 800; margin-top: 2px; color: #1e293b; }

        .meta-wrap { width: 33%; text-align: right; font-size: 9.5px; font-weight: 800; line-height: 1.5;}
        .meta-highlight { color: #2563eb; font-size: 10px;}

        /* TABEL WOS (DIFIXKAN 100% LEBARNYA) */
        table.wos-table { width: 100%; border-collapse: collapse; text-align: center; table-layout: fixed; }
        table.wos-table th, table.wos-table td { border: 1px solid #000; padding: 4px 2px; vertical-align: middle; word-wrap: break-word; }
        table.wos-table th { background-color: #f8fafc; font-weight: 800; font-size: 8px; text-transform: uppercase; }
        
        .part-info { text-align: left; padding-left: 6px !important; }
        .part-no { font-weight: 900; font-size: 9.5px; display: block; }
        .part-name { font-size: 7.5px; color: #475569; font-weight: 700; }
        
        .row-summary td { background-color: #e2e8f0; font-weight: 800; font-size: 10px; border-top: 2px solid #000; border-bottom: 2px solid #000;}
        .target-box { font-weight: 900; font-size: 11px; }
        .time-box { font-family: monospace; font-weight: 800; font-size: 9.5px;}
        
        .empty-row td { height: 20px; }

        @media print { 
            .no-print { display: none !important; } 
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } 
        }
    </style>
</head>
<body>

    <div class="no-print no-print-area">
        <div class="form-title-web">FORM CETAK WOS WELDING - {{ strtoupper($line_code) }}</div>
        <div>
            <button class="btn btn-back" onclick="window.close();">TUTUP KEMBALI</button>
            <button class="btn btn-print" onclick="window.print()">🖨️ CETAK WOS WELDING</button>
        </div>
    </div>

    @php 
        $grandTarget = 0; 
        $totalHoursS1 = 0; 
        $totalHoursS2 = 0; 
    @endphp

    <div class="header-wrap">
        <div class="logo-wrap">
            <img src="{{ asset('admin/img/Logo-asalta.png') }}" class="logo-img" alt="Logo" onerror="this.style.display='none'">
            <div class="comp-info">
                PT. Asalta Mandiri Agung<br>
                <span class="comp-dept">Logistic & Production Dept.</span>
            </div>
        </div>
        <div class="title-wrap">
            <h1 class="title-main">WORK ORDER SHEET</h1>
            <h3 class="title-sub">[ WELDING STATION : {{ strtoupper($line_code) }} ]</h3>
        </div>
        <div class="meta-wrap">
            TANGGAL : <span class="meta-highlight">{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</span><br>
            NO DOKUMEN : WOS-{{ date('ymd', strtotime($date)) }}-{{ strtoupper(str_replace(' ', '', $line_code)) }}
        </div>
    </div>

    <table class="wos-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">NO</th>
                <th rowspan="2" style="width: 17%;">IDENTIFIKASI PART</th>
                <th rowspan="2" style="width: 4%;">CUST</th>
                <th rowspan="2" style="width: 3%;">MP</th>
                <th rowspan="2" style="width: 4%;">CAP/<br>HR</th>
                <th rowspan="2" style="width: 5%;">TARGET<br>SHIFT</th>
                <th colspan="2" style="width: 8%;">PLAN WAKTU</th>
                <th rowspan="2" style="width: 5%;">M/C LINE</th>
                <th colspan="2" style="width: 8%;">ACTUAL WAKTU</th>
                <th colspan="3" style="width: 12%;">HASIL PRODUKSI</th>
                <th rowspan="2" style="width: 31%;">KETERANGAN / DOWNTIME</th>
            </tr>
            <tr>
                <th>START</th><th>AKHIR</th>
                <th>MULAI</th><th>SELESAI</th>
                <th>OK</th><th>NG</th><th>RET</th>
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
                    <!-- Colspan harus 15 sesuai jumlah kolom <th> -->
                    <td colspan="15" style="text-align: left; padding-left: 10px; background-color: #f1f5f9;">
                        SHIFT I (DAY OPS) &nbsp;&nbsp;|&nbsp;&nbsp; TOTAL ESTIMASI RUN-TIME : <span style="color:#dc2626;">{{ number_format($totalHoursS1, 1) }} JAM</span>
                    </td>
                </tr>
                
                @php $lastFinish = "07:30"; $no = 1; @endphp
                @foreach($plansS1 as $plan)
                    @php
                        $target = $plan->s1_plan_reg + $plan->s1_plan_ot;
                        $dur = ($plan->cap_per_hour > 0 && $target > 0) ? ($target / $plan->cap_per_hour) + (($plan->dandory_time ?? 15) / 60) : 0;
                        $start = $lastFinish; 
                        $finish = date('H:i', strtotime($start . " + " . round($dur * 60) . " minutes")); 
                        
                        $batchData = isset($batches) ? $batches->where('part_no', $plan->part_no)->where('shift', 'Pagi')->first() : null;
                        $grandTarget += $target; $lastFinish = $finish;
                    @endphp
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td class="part-info">
                            <span class="part-no">{{ $plan->part_no }}</span>
                            <span class="part-name">{{ $plan->part_name ?? '-' }}</span>
                        </td>
                        <td style="font-weight: 800;">{{ $plan->customer_code }}</td>
                        <td style="font-weight: 700;">{{ $plan->manpower }}</td>
                        <td style="font-weight: 800;">{{ $plan->cap_per_hour }}</td>
                        <td class="target-box text-primary">{{ number_format($target) }}</td>
                        <td class="time-box">{{ $start }}</td>
                        <td class="time-box">{{ $finish }}</td>
                        <td style="font-weight: 800; font-size:8.5px;">{{ $plan->line_code }}</td>
                        <td></td><td></td>
                        <td style="font-weight: 900; font-size:10px;">{{ ($batchData && $batchData->qty_ok > 0) ? number_format($batchData->qty_ok) : '' }}</td>
                        <td style="font-weight: 900; color: #ef4444; font-size:10px;">{{ ($batchData && $batchData->qty_ng > 0) ? number_format($batchData->qty_ng) : '' }}</td>
                        <td style="font-weight: 900; color: #0284c7; font-size:10px;">{{ ($batchData && $batchData->qty_return > 0) ? number_format($batchData->qty_return) : '' }}</td>
                        <td style="text-align: left; padding-left: 4px; font-size: 7.5px; font-weight: 600;">{{ $batchData->keterangan ?? '' }}</td>
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
                    <td colspan="15" style="text-align: left; padding-left: 10px; background-color: #e2e8f0;">
                        SHIFT II (NIGHT OPS) &nbsp;&nbsp;|&nbsp;&nbsp; TOTAL ESTIMASI RUN-TIME : <span style="color:#dc2626;">{{ number_format($totalHoursS2, 1) }} JAM</span>
                    </td>
                </tr>
                
                @php $lastFinish = "19:30"; $no = 1; @endphp
                @foreach($plansS2 as $plan)
                    @php
                        $target = $plan->s2_plan_reg + $plan->s2_plan_ot;
                        $dur = ($plan->cap_per_hour > 0 && $target > 0) ? ($target / $plan->cap_per_hour) + (($plan->dandory_time ?? 15) / 60) : 0;
                        $start = $lastFinish; 
                        $finish = date('H:i', strtotime($start . " + " . round($dur * 60) . " minutes")); 
                        
                        $batchData = isset($batches) ? $batches->where('part_no', $plan->part_no)->where('shift', 'Malam')->first() : null;
                        $grandTarget += $target; $lastFinish = $finish;
                    @endphp
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td class="part-info">
                            <span class="part-no">{{ $plan->part_no }}</span>
                            <span class="part-name">{{ $plan->part_name ?? '-' }}</span>
                        </td>
                        <td style="font-weight: 800;">{{ $plan->customer_code }}</td>
                        <td style="font-weight: 700;">{{ $plan->manpower }}</td>
                        <td style="font-weight: 800;">{{ $plan->cap_per_hour }}</td>
                        <td class="target-box text-primary">{{ number_format($target) }}</td>
                        <td class="time-box">{{ $start }}</td>
                        <td class="time-box">{{ $finish }}</td>
                        <td style="font-weight: 800; font-size:8.5px;">{{ $plan->line_code }}</td>
                        <td></td><td></td>
                        <td style="font-weight: 900; font-size:10px;">{{ ($batchData && $batchData->qty_ok > 0) ? number_format($batchData->qty_ok) : '' }}</td>
                        <td style="font-weight: 900; color: #ef4444; font-size:10px;">{{ ($batchData && $batchData->qty_ng > 0) ? number_format($batchData->qty_ng) : '' }}</td>
                        <td style="font-weight: 900; color: #0284c7; font-size:10px;">{{ ($batchData && $batchData->qty_return > 0) ? number_format($batchData->qty_return) : '' }}</td>
                        <td style="text-align: left; padding-left: 4px; font-size: 7.5px; font-weight: 600;">{{ $batchData->keterangan ?? '' }}</td>
                    </tr>
                @endforeach
            @endif

            {{-- PADDING BARIS KOSONG --}}
            @php $totalRows = (isset($plansS1) ? count($plansS1) : 0) + (isset($plansS2) ? count($plansS2) : 0); @endphp
            @for($i = $totalRows; $i < 12; $i++)
            <tr class="empty-row">
                <td></td><td></td><td></td><td></td><td></td>
                <td></td><td></td><td></td><td></td><td></td>
                <td></td><td></td><td></td><td></td><td></td>
            </tr>
            @endfor

            <!-- TOTAL SUMMARY -->
            <tr style="background-color: #cbd5e1; font-weight: 900; font-size: 11px;">
                <td colspan="5" style="text-align: right; padding: 6px 12px; border-right: none;">TOTAL KESELURUHAN TARGET HARIAN : </td>
                <td style="color: #dc2626; border-left: none;">{{ number_format($grandTarget) }}</td>
                <!-- Sisa colspan harus 9 agar genap 15 -->
                <td colspan="9"></td>
            </tr>
        </tbody>
    </table>

</body>
</html>