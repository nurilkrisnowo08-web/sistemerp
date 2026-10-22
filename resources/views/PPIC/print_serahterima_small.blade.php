<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>WOS WELDING - {{ strtoupper($line_code) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --border-thick: #000000;
            --dark: #000000;
        }

        * { box-sizing: border-box; }

        @page { size: A4 landscape; margin: 6mm 8mm; }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            font-size: 8.5px;
            color: var(--dark);
            margin: 0;
            padding: 10px;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* 🔘 SCREEN TOOLBAR */
        .no-print-bar {
            display: flex; justify-content: space-between; align-items: center;
            background: #f1f5f9; padding: 8px 15px; border-radius: 6px;
            margin-bottom: 12px; border: 1px solid #cbd5e1;
        }
        .btn-screen {
            font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700;
            font-size: 10px; padding: 6px 14px; border-radius: 4px;
            border: none; cursor: pointer;
        }
        .btn-close { background: #64748b; color: #fff; }
        .btn-print { background: #2563eb; color: #fff; }

        /* 🏢 HEADER FORM STAMPING STYLE */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header-table td { vertical-align: middle; padding: 2px 4px; }
        .logo-area { width: 30%; font-size: 9px; line-height: 1.2; font-weight: 800; }
        .logo-area h3 { margin: 0; font-size: 13px; font-weight: 900; }
        .title-area { width: 40%; text-align: center; }
        .title-area h1 { margin: 0; font-size: 16px; font-weight: 900; letter-spacing: 0.5px; text-decoration: underline; }
        .title-area h2 { margin: 2px 0 0; font-size: 11px; font-weight: 800; }
        .meta-box-area { width: 30%; text-align: right; }
        .meta-box-area table { border-collapse: collapse; float: right; font-size: 9px; font-weight: 800; }
        .meta-box-area td { border: 1px solid var(--border-thick); padding: 3px 6px; }

        /* 📋 TABEL UTAMA WOS */
        table.wos-table { width: 100%; border-collapse: collapse; text-align: center; table-layout: fixed; }
        table.wos-table th, table.wos-table td { border: 1px solid var(--border-thick); padding: 4px 2px; vertical-align: middle; word-wrap: break-word; }
        table.wos-table th { background-color: #f8fafc; font-weight: 800; font-size: 8px; text-transform: uppercase; }
        
        .part-info { text-align: left; padding-left: 6px !important; }
        .part-no { font-weight: 900; font-size: 9.5px; display: block; }
        .part-name { font-size: 7.5px; color: #475569; font-weight: 700; }
        
        .row-summary td { background-color: #e2e8f0; font-weight: 800; font-size: 10px; border-top: 2px solid var(--border-thick); border-bottom: 2px solid var(--border-thick);}
        .target-box { font-weight: 900; font-size: 11px; }
        .time-box { font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 9px;}
        
        .empty-row td { height: 20px; }

        @media print { .no-print { display: none !important; } body { padding: 0; margin: 0; } }
    </style>
</head>
<body>

    <div class="no-print no-print-bar">
        <div><b>FORM CETAK WOS WELDING</b> — Line: <b>{{ strtoupper($line_code) }}</b></div>
        <div>
            <button class="btn-screen btn-close" onclick="window.length ? window.history.back() : window.close()">TUTUP / KEMBALI</button>
            <button class="btn-screen btn-print" onclick="window.print()">🖨️ CETAK WOS WELDING</button>
        </div>
    </div>

    @php 
        $grandTarget = 0; 
        $totalHoursS1 = 0; 
        $totalHoursS2 = 0; 
    @endphp

    {{-- 🔥 HEADER DISAMAKAN DENGAN STAMPING BPRM 🔥 --}}
    <table class="header-table">
        <tr>
            <td class="logo-area">
                <div style="display: flex; align-items: center;">
                    <img src="{{ asset('public/admin/img/Logo-asalta.png') }}" alt="Logo Asalta" style="height: 40px; margin-right: 10px;" onerror="this.style.display='none'">
                    <div>
                        <h3 style="font-size: 12px; margin: 0; padding: 0;">PT. Asalta Mandiri Agung</h3>
                        <div style="font-size: 8px; font-weight: 700; margin-top: 2px;">Dept. PPC<br>LOGISTICS & PRODUCTION</div>
                    </div>
                </div>
            </td>
            
            <td class="title-area">
                <h1>WORK ORDER SHEET</h1>
                <h2>[ WELDING STATION : {{ strtoupper($line_code) }} ]</h2>
            </td>
            
            <td class="meta-box-area">
                <table>
                    <tr>
                        <td>TANGGAL :</td>
                        <td style="min-width: 60px; text-align: left; color:#2563eb;">{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td>NO DOKUMEN :</td>
                        <td style="font-weight: 900; font-size: 9px; text-align: left;">WOS-{{ date('ymd', strtotime($date)) }}-{{ strtoupper(str_replace(' ', '', $line_code)) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

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
                    <td colspan="15" style="text-align: left; padding-left: 10px;">
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
                        <td style="font-weight: 900; font-size:10px; color: #10b981;">{{ ($batchData && $batchData->qty_ok > 0) ? number_format($batchData->qty_ok) : '' }}</td>
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
                    <td colspan="15" style="text-align: left; padding-left: 10px;">
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
                        <td style="font-weight: 900; font-size:10px; color: #10b981;">{{ ($batchData && $batchData->qty_ok > 0) ? number_format($batchData->qty_ok) : '' }}</td>
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
                <td colspan="9"></td>
            </tr>
        </tbody>
    </table>

</body>
</html>