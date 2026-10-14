<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>WOS WELDING - {{ $line_code }}</title>
    <!-- Menggunakan font profesional untuk cetakan -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Setup Kertas A4 Memanjang (Landscape) */
        @page { 
            size: A4 landscape; 
            margin: 10mm; 
        }
        
        body { 
            font-family: 'Plus Jakarta Sans', Arial, sans-serif; 
            font-size: 10px; 
            margin: 0; 
            padding: 0; 
            color: #0f172a; 
            background-color: #fff;
        }

        /* 🖨️ Area Tombol (Sembunyi saat diprint) */
        .no-print-area { 
            text-align: right; 
            margin-bottom: 15px; 
            padding: 15px; 
            background: #f1f5f9; 
            border-bottom: 2px solid #cbd5e1; 
        }
        .btn {
            padding: 10px 20px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .btn-back { background: #64748b; color: white; margin-right: 10px; }
        .btn-print { background: #2563eb; color: white; }

        /* 🏢 Kop Surat / Header */
        .header-container { 
            display: table; 
            width: 100%; 
            border-bottom: 4px double #0f172a; 
            padding-bottom: 15px; 
            margin-bottom: 15px; 
        }
        .header-container > div { display: table-cell; vertical-align: middle; }
        
        .logo-section { width: 35%; }
        .logo-wrapper { 
            display: inline-block; vertical-align: middle; 
            width: 50px; height: 50px; border: 2px solid #0f172a; 
            border-radius: 50%; text-align: center; line-height: 50px; 
            font-size: 16px; font-weight: 900; margin-right: 12px; 
        }
        .company-info { 
            display: inline-block; vertical-align: middle; 
            line-height: 1.4; font-size: 11px; font-weight: 800; text-transform: uppercase;
        }

        .title-section { width: 30%; text-align: center; }
        .title-section h1 { margin: 0; font-size: 22px; font-weight: 900; letter-spacing: 1px; color: #0f172a;}
        .title-section h3 { margin: 5px 0 0 0; font-size: 14px; font-weight: 700; color: #475569; }

        .meta-section { width: 35%; text-align: right; font-size: 11px; font-weight: 700; line-height: 1.6; }

        /* 📋 Tabel WOS Utama */
        table.wos-table { 
            width: 100%; 
            border-collapse: collapse; 
            text-align: center; 
        }
        table.wos-table th, table.wos-table td { 
            border: 1px solid #1e293b; 
            padding: 6px 4px; 
            vertical-align: middle; 
        }
        table.wos-table th { 
            background-color: #f1f5f9; 
            font-weight: 800; 
            font-size: 9px; 
            text-transform: uppercase; 
            color: #0f172a; 
        }
        
        .part-info { text-align: left; padding-left: 8px !important;}
        .part-no { font-weight: 800; font-size: 11px; display: block; }
        .part-name { font-size: 9px; color: #475569; font-weight: 600; }
        
        .row-summary { 
            background-color: #e2e8f0; 
            font-weight: 800; 
            font-size: 11px; 
            border-top: 2px solid #0f172a; 
            border-bottom: 2px solid #0f172a;
        }

        .target-box { font-weight: 900; font-size: 12px; color: #2563eb;}
        .time-box { font-family: monospace; font-weight: bold; font-size: 11px;}
        
        .empty-row td { height: 25px; } /* Buat ngisi sisa kertas yang kosong */

        @media print { 
            .no-print { display: none !important; } 
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    {{-- Tombol Aksi --}}
    <div class="no-print no-print-area">
        <button class="btn btn-back" onclick="window.close();">TUTUP HALAMAN</button>
        <button class="btn btn-print" onclick="window.print()">🖨️ CETAK WOS (FULL DAY)</button>
    </div>

    @php 
        $grandTarget = 0; 
        $totalHoursS1 = 0; 
        $totalHoursS2 = 0; 
    @endphp

    {{-- Kop Header --}}
    <div class="header-container">
        <div class="logo-section">
            <div class="logo-wrapper">AMA</div>
            <div class="company-info">PT. Asalta Mandiri Agung<br><span style="color:#64748b;">Logistic & Production Dept.</span></div>
        </div>
        <div class="title-section">
            <h1>WORK ORDER SHEET</h1>
            <h3>[ WELDING STATION : {{ strtoupper($line_code) }} ]</h3>
        </div>
        <div class="meta-section">
            TANGGAL : <span style="color:#2563eb;">{{ date('d F Y', strtotime($date)) }}</span><br>
            NO DOKUMEN : WOS-{{ date('ymd', strtotime($date)) }}-{{ strtoupper($line_code) }}
        </div>
    </div>

    {{-- Tabel Utama WOS --}}
    <table class="wos-table">
        <thead>
            <tr>
                <th rowspan="2" width="25">NO</th>
                <th rowspan="2" width="160">IDENTIFIKASI PART</th>
                <th rowspan="2" width="50">CUST</th>
                <th rowspan="2" width="30">MP</th>
                <th rowspan="2" width="45">CAP /<br>HOUR</th>
                <th rowspan="2" width="55">TARGET<br>SHIFT</th>
                <th colspan="2">PLAN WAKTU</th>
                <th rowspan="2" width="60">M/C LINE</th>
                <th colspan="2">ACTUAL WAKTU</th>
                <th colspan="2">HASIL PRODUKSI</th>
                <th rowspan="2" width="130">KETERANGAN / DOWNTIME</th>
            </tr>
            <tr>
                <th width="45">START</th><th width="45">AKHIR</th>
                <th width="45">MULAI</th><th width="45">SELESAI</th>
                <th width="40">OK</th><th width="40">NG</th>
            </tr>
        </thead>
        <tbody>
            
            {{-- ================= SHIFT 1 (DAY OPS) ================= --}}
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
                    
                    // Ambil actual data jika sudah ada pengerjaan di Terminal
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
                    <td></td><td></td> <!-- Dikosongkan untuk tulis tangan operator -->
                    <td style="font-weight: 800; color: #10b981; font-size:12px;">{{ ($batchData && $batchData->qty_ok > 0) ? number_format($batchData->qty_ok) : '' }}</td>
                    <td style="font-weight: 800; color: #ef4444; font-size:12px;">{{ ($batchData && $batchData->qty_ng > 0) ? number_format($batchData->qty_ng) : '' }}</td>
                    <td style="text-align: left; font-size: 8px;">{{ $batchData->keterangan ?? '' }}</td>
                </tr>
                @endforeach
            @endif

            {{-- ================= SHIFT 2 (NIGHT OPS) ================= --}}
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
                    <td></td><td></td> <!-- Dikosongkan untuk tulis tangan operator -->
                    <td style="font-weight: 800; color: #10b981; font-size:12px;">{{ ($batchData && $batchData->qty_ok > 0) ? number_format($batchData->qty_ok) : '' }}</td>
                    <td style="font-weight: 800; color: #ef4444; font-size:12px;">{{ ($batchData && $batchData->qty_ng > 0) ? number_format($batchData->qty_ng) : '' }}</td>
                    <td style="text-align: left; font-size: 8px;">{{ $batchData->keterangan ?? '' }}</td>
                </tr>
                @endforeach
            @endif

            {{-- Menambahkan Baris Kosong (Padding) agar tabel tidak terlalu pendek jika data sedikit --}}
            @php 
                $totalRows = (isset($plansS1) ? count($plansS1) : 0) + (isset($plansS2) ? count($plansS2) : 0); 
            @endphp
            @for($i = $totalRows; $i < 10; $i++)
            <tr class="empty-row">
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            @endfor

            {{-- Baris Grand Total --}}
            <tr style="background-color: #cbd5e1; font-weight: 900; font-size: 13px;">
                <td colspan="5" style="text-align: right; padding: 12px 15px;">TOTAL KESELURUHAN TARGET HARIAN : </td>
                <td style="color: #dc2626;">{{ number_format($grandTarget) }}</td>
                <td colspan="8"></td>
            </tr>
        </tbody>
    </table>
</body>
</html>