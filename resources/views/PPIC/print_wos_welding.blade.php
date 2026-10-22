<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>WOS WELDING - {{ $line_code }}</title>
    <!-- Font Profesional Industri -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Setup Kertas A4 Memanjang (Landscape) */
        @page { size: A4 landscape; margin: 10mm; }
        body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; font-size: 10px; margin: 0; padding: 0; color: #0f172a; background-color: #fff; }
        
        .no-print-area { text-align: right; margin-bottom: 15px; padding: 15px; background: #f1f5f9; border-bottom: 2px solid #cbd5e1; }
        .btn { padding: 10px 20px; font-size: 12px; font-weight: 700; border-radius: 6px; border: none; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif; }
        .btn-back { background: #64748b; color: white; margin-right: 10px; }
        .btn-print { background: #2563eb; color: white; }

        /* 🔥 PERBAIKAN HEADER CSS 🔥 */
        .header-container { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 4px double #0f172a; padding-bottom: 15px; margin-bottom: 15px; }
        
        .logo-section { display: flex; align-items: center; width: 33%; }
        .company-info { display: block; line-height: 1.3; text-transform: uppercase; }
        .company-info h2 { margin: 0; font-size: 14px; font-weight: 900; color: #0f172a; letter-spacing: 0.5px; }
        .company-info h3 { margin: 2px 0 0 0; font-size: 10px; font-weight: 700; color: #475569; }
        
        .title-section { width: 34%; text-align: center; }
        .title-section h1 { margin: 0; font-size: 22px; font-weight: 900; letter-spacing: 1px; color: #0f172a;}
        .title-section h3 { margin: 5px 0 0 0; font-size: 14px; font-weight: 700; color: #475569; }

        .meta-section { width: 33%; text-align: right; font-size: 11px; font-weight: 700; line-height: 1.6; }

        /* 📋 Tabel WOS */
        table.wos-table { width: 100%; border-collapse: collapse; text-align: center; }
        table.wos-table th, table.wos-table td { border: 1px solid #1e293b; padding: 6px 4px; vertical-align: middle; }
        table.wos-table th { background-color: #f1f5f9; font-weight: 800; font-size: 9px; text-transform: uppercase; color: #0f172a; }
        
        .part-info { text-align: left; padding-left: 8px !important;}
        .part-no { font-weight: 800; font-size: 11px; display: block; }
        .part-name { font-size: 9px; color: #475569; font-weight: 600; }
        
        .row-summary { background-color: #e2e8f0; font-weight: 800; font-size: 11px; border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a;}
        .target-box { font-weight: 900; font-size: 12px; color: #2563eb;}
        .time-box { font-family: monospace; font-weight: bold; font-size: 11px;}
        
        .empty-row td { height: 25px; }

        @media print { .no-print { display: none !important; } body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>

    <div class="no-print no-print-area">
        <button class="btn btn-back" onclick="window.close();">TUTUP HALAMAN</button>
        <button class="btn btn-print" onclick="window.print()">🖨️ CETAK WOS (FULL DAY)</button>
    </div>

    @php $grandTarget = 0; $totalHoursS1 = 0; $totalHoursS2 = 0; @endphp

    <!-- 🔥 HEADER DIPERBAIKI (TIDAK ADA LAGI DIV BERANTAKAN) 🔥 -->
    <div class="header-container">
        <!-- Logo & Nama PT -->
        <div class="logo-section">
            <img src="{{ asset('admin/img/Logo-asalta.png') }}" alt="Logo Asalta" style="height: 45px; margin-right: 15px;" onerror="this.style.display='none'">
            <div class="company-info">
                <h2>PT. Asalta Mandiri Agung</h2>
                <h3>Dept. PPC<br>LOGISTICS & PRODUCTION</h3>
            </div>
        </div>
        
        <!-- Judul Tengah -->
        <div class="title-section">
            <h1>WORK ORDER SHEET</h1>
            <h3>[ WELDING STATION : {{ strtoupper($line_code) }} ]</h3>
        </div>
        
        <!-- Tanggal Kanan -->
        <div class="meta-section">
            TANGGAL : <span style="color:#2563eb;">{{ date('d F Y', strtotime($date)) }}</span><br>
            NO DOKUMEN : WOS-{{ date('ymd', strtotime($date)) }}-{{ strtoupper(str_replace(' ', '', $line_code)) }}
        </div>
    </div>

    <!-- TABEL DI BAWAH INI SAMA PERSIS 100% TIDAK DIUBAH -->
    <table class="wos-table">
        <thead>
            <tr>
                <th rowspan="2" width="20">NO</th>
                <th rowspan="2" width="130">IDENTIFIKASI PART</th>
                <th rowspan="2" width="45">CUST</th>
                <th rowspan="2" width="25">MP</th>
                <th rowspan="2" width="35">CAP /<br>HOUR</th>
                <th rowspan="2" width="45">TARGET<br>SHIFT</th>
                <th colspan="2">PLAN WAKTU</th>
                <th rowspan="2" width="55">M/C LINE</th>
                <th colspan="2">ACTUAL WAKTU</th>
                <th colspan="3">HASIL PRODUKSI</th>
                <th rowspan="2" width="100">KETERANGAN / DOWNTIME</th>
            </tr>
            <tr>
                <th width="35">START</th><th width="35">AKHIR</th>
                <th width="35">MULAI</th><th width="35">SELESAI</th>
                <th width="25">OK</th><th width="25">NG</th><th width="25">RET</th>
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
                    <td colspan="15" style="text-align: left; padding-left: 10px; background-color: #f1f5f9;">
                        SHIFT 1 (DAY OPS) | TOTAL ESTIMASI RUN-TIME : <span style="color:#dc2626;">{{ number_format($totalHoursS1, 1) }} JAM</span>
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
                        <span class="part-name">{{ $plan->part_name ?? 'N/A' }}</span>
                    </td>
                    <td style="font-weight: bold;">{{ $plan->customer_code }}</td>
                    <td>{{ $plan->manpower }}</td>
                    <td style="font-weight: bold;">{{ $plan->cap_per_hour }}</td>
                    <td class="target-box">{{ number_format($target) }}</td>
                    <td class="time-box">{{ $start }}</td>
                    <td class="time-box">{{ $finish }}</td>
                    <td style="font-weight: 800; font-size:10px;">{{ $plan->line_code }}</td>
                    <td></td><td></td>
                    <td style="font-weight: 800; color: #10b981; font-size:11px;">{{ ($batchData && $batchData->qty_ok > 0) ? number_format($batchData->qty_ok) : '' }}</td>
                    <td style="font-weight: 800; color: #ef4444; font-size:11px;">{{ ($batchData && $batchData->qty_ng > 0) ? number_format($batchData->qty_ng) : '' }}</td>
                    <td style="font-weight: 800; color: #0284c7; font-size:11px;">{{ ($batchData && $batchData->qty_return > 0) ? number_format($batchData->qty_return) : '' }}</td>
                    <td style="text-align: left; font-size: 8px;">{{ $batchData->keterangan ?? '' }}</td>
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
                        SHIFT 2 (NIGHT OPS) | TOTAL ESTIMASI RUN-TIME : <span style="color:#dc2626;">{{ number_format($totalHoursS2, 1) }} JAM</span>
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
                        <span class="part-name">{{ $plan->part_name ?? 'N/A' }}</span>
                    </td>
                    <td style="font-weight: bold;">{{ $plan->customer_code }}</td>
                    <td>{{ $plan->manpower }}</td>
                    <td style="font-weight: bold;">{{ $plan->cap_per_hour }}</td>
                    <td class="target-box">{{ number_format($target) }}</td>
                    <td class="time-box">{{ $start }}</td>
                    <td class="time-box">{{ $finish }}</td>
                    <td style="font-weight: 800; font-size:10px;">{{ $plan->line_code }}</td>
                    <td></td><td></td>
                    <td style="font-weight: 800; color: #10b981; font-size:11px;">{{ ($batchData && $batchData->qty_ok > 0) ? number_format($batchData->qty_ok) : '' }}</td>
                    <td style="font-weight: 800; color: #ef4444; font-size:11px;">{{ ($batchData && $batchData->qty_ng > 0) ? number_format($batchData->qty_ng) : '' }}</td>
                    <td style="font-weight: 800; color: #0284c7; font-size:11px;">{{ ($batchData && $batchData->qty_return > 0) ? number_format($batchData->qty_return) : '' }}</td>
                    <td style="text-align: left; font-size: 8px;">{{ $batchData->keterangan ?? '' }}</td>
                </tr>
                @endforeach
            @endif

            {{-- Padding Baris Kosong --}}
            @php $totalRows = (isset($plansS1) ? count($plansS1) : 0) + (isset($plansS2) ? count($plansS2) : 0); @endphp
            @for($i = $totalRows; $i < 10; $i++)
            <tr class="empty-row">
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            @endfor

            <tr style="background-color: #cbd5e1; font-weight: 900; font-size: 13px;">
                <td colspan="5" style="text-align: right; padding: 12px 15px;">TOTAL KESELURUHAN TARGET HARIAN : </td>
                <td style="color: #dc2626;">{{ number_format($grandTarget) }}</td>
                <td colspan="9"></td>
            </tr>
        </tbody>
    </table>
</body>
</html>