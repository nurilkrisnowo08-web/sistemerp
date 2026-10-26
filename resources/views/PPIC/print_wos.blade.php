<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print WOS Produksi Stamping - Line {{ $line_code }}</title>
    <style>
        /* RESET & DASAR */
        body { font-family: Arial, sans-serif; color: #000; background: #fff; margin: 0; padding: 10px; font-size: 9px; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

        @page { size: A4 landscape; margin: 5mm; }

        /* KONTROL TOMBOL */
        .print-controls { background: #1e293b; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: flex-end; gap: 10px; }
        .btn { padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; border: none; font-size: 12px; text-decoration: none; display: inline-block; }
        .btn-back { background: #475569; color: #fff; }
        .btn-print { background: #f8fafc; color: #0f172a; border: 1px solid #cbd5e1; }
        @media print { .print-controls { display: none !important; } }

        /* HEADER WOS */
        .header-wos { display: flex; align-items: center; margin-bottom: 10px; }
        .logo { width: 45px; margin-right: 15px; }
        .title-area h2 { margin: 0; font-size: 15px; font-weight: bold; text-transform: uppercase; }
        .title-area p { margin: 2px 0; font-size: 10px; font-weight: bold; }

        /* MAIN TABLE */
        .wos-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .wos-table th, .wos-table td { border: 1px solid #000; padding: 3px; text-align: center; vertical-align: middle; }
        .wos-table th { background: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 8px; }
        .wos-table td { font-size: 9px; height: 18px; }
        
        /* SPESIFIK KOLOM */
        .text-left { text-align: left !important; padding-left: 5px !important; }
        .text-bold { font-weight: bold; }
        .text-blue { color: #2563eb !important; }
        .text-green { color: #16a34a !important; font-weight: bold; }
        .text-red { color: #dc2626 !important; font-weight: bold; }
        
        .shift-header { background: #f8fafc; text-align: left !important; padding: 4px 8px !important; font-size: 9px; font-weight: bold; text-transform: uppercase; border: 1px solid #000; border-bottom: 2px solid #000; }
        .shift-header span { color: #dc2626; }

        /* FOOTER BLOCKS */
        .footer-grid { display: flex; gap: 15px; margin-top: 5px; }
        
        .problem-table { width: 45%; border-collapse: collapse; font-size: 9px; font-weight: bold; }
        .problem-table th, .problem-table td { border: 1px solid #000; padding: 5px; text-align: left; }
        .problem-table th { width: 30%; background: #f8fafc; }
        
        .sig-table { width: 55%; border-collapse: collapse; font-size: 9px; font-weight: bold; text-align: center; }
        .sig-table th, .sig-table td { border: 1px solid #000; padding: 5px; width: 25%; }
        .sig-table th { background: #f8fafc; }
        .sig-table td { height: 50px; vertical-align: bottom; padding-bottom: 5px; }
    </style>
</head>
<body>

    <!-- KONTROL TOMBOL -->
    <div class="print-controls">
        <button onclick="window.close()" class="btn btn-back">⬅ KEMBALI / TUTUP</button>
        <button onclick="window.print()" class="btn btn-print">🖨️ PRINT WOS (FULL DAY)</button>
    </div>

    <!-- HEADER KOP SURAT -->
    <div class="header-wos">
        <img src="{{ asset('public/admin/img/Logo-asalta.png') }}" class="logo" alt="Logo" onerror="this.style.display='none'">
        <div class="title-area">
            <h2>PT. Asalta Mandiri Agung</h2>
            <p>Dept. PPC<br>LOGISTICS & MATERIAL</p>
        </div>
    </div>

    <!-- TABEL UTAMA WOS -->
    <table class="wos-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 2%;">NO</th>
                <th rowspan="2" style="width: 14%;">PART NUMBER / IDENTIFICATION</th>
                <th rowspan="2" style="width: 4%;">CUST</th>
                <th rowspan="2" style="width: 3%;">MP</th>
                <th rowspan="2" style="width: 3%;">PROC</th>
                <th rowspan="2" style="width: 4%;">QTY LOT</th>
                <th rowspan="2" style="width: 4%;">CAP / HOUR</th>
                <th rowspan="2" style="width: 4%;">TOTAL TARGET</th>
                <th rowspan="2" style="width: 4%;">TOTAL STROKE</th>
                <th rowspan="2" style="width: 4%;">DANDORY (MIN)</th>
                <th colspan="2">PLAN HOURS</th>
                <th colspan="2">PLAN PRODUKSI</th>
                <th rowspan="2" style="width: 4%;">TOTAL PLAN</th>
                <th rowspan="2" style="width: 6%;">M/C LINE</th>
                <th rowspan="2" style="width: 4%;">JAM</th>
                <th colspan="3">ACTUAL PRODUKSI</th>
                <th rowspan="2" style="width: 15%;">KETERANGAN</th>
            </tr>
            <tr>
                <th style="width: 4%;">START</th>
                <th style="width: 4%;">AKHIR</th>
                <th style="width: 4%;">REG</th>
                <th style="width: 4%;">OT</th>
                <th style="width: 4%;">OK</th>
                <th style="width: 4%;">NG</th>
                <th style="width: 4%;">WH</th>
            </tr>
        </thead>
        <tbody>
            <!-- ===================== SHIFT 1 (PAGI) ===================== -->
            @php 
                $totalTargetS1 = 0; 
                $totalWorkingS1 = 0;
            @endphp
            @foreach($plansS1 as $p)
                @php 
                    $target = $p->s1_plan_reg + $p->s1_plan_ot;
                    $totalTargetS1 += $target;
                    $dur = ($p->cap_per_hour > 0) ? ($target / $p->cap_per_hour) + (($p->dandory_time ?? 15) / 60) : 0;
                    $totalWorkingS1 += $dur;
                @endphp
            @endforeach

            <tr>
                <td colspan="21" class="shift-header">
                    TOTAL WORKING HOURS / SHIFT 1 (DAY OPS) : <span>{{ number_format($totalWorkingS1, 1) }} Jam</span> | TANGGAL: {{ date('d/m/Y', strtotime($date)) }}
                </td>
            </tr>

            @php $no = 1; $startTime = "07:30"; @endphp
            @forelse($plansS1 as $p)
                @php
                    $target = $p->s1_plan_reg + $p->s1_plan_ot;
                    $durMin = ($p->cap_per_hour > 0) ? round(($target / $p->cap_per_hour) * 60) + ($p->dandory_time ?? 15) : 0;
                    $endTime = date('H:i', strtotime($startTime . " + $durMin minutes"));
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td class="text-left text-bold">
                        {{ $p->part_no }}<br>
                        <!-- 🔥 INI DIA NOMOR WOS YANG DIMINTA! BERCETAK MERAH TEBAL 🔥 -->
                        <span style="color: #dc2626; font-family: monospace; font-size: 10px;">{{ $p->no_produksi }}</span>
                    </td>
                    <td class="text-bold">{{ $p->customer_code ?? 'AMA' }}</td>
                    <td>{{ $p->manpower ?? 1 }}</td>
                    <td>{{ $p->process_qty ?? 1 }}</td>
                    <td>{{ $p->qty_lot ?? 200 }}</td>
                    <td>{{ $p->cap_per_hour }}</td>
                    <td class="text-bold">{{ number_format($target) }}</td>
                    <td class="text-bold text-blue">{{ number_format($target) }}</td>
                    <td>{{ $p->dandory_time ?? 15 }}</td>
                    
                    <td class="text-bold">{{ $startTime }}</td>
                    <td class="text-bold">{{ $endTime }}</td>
                    
                    <td>{{ number_format($p->s1_plan_reg) }}</td>
                    <td>{{ number_format($p->s1_plan_ot) }}</td>
                    <td class="text-bold">{{ number_format($target) }}</td>
                    <td class="text-bold">{{ $p->line_code }}</td>
                    <td></td>
                    
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @php $startTime = $endTime; @endphp
            @empty
                <tr><td colspan="21" style="padding: 10px; font-style: italic;">Tidak ada plan Shift 1.</td></tr>
            @endforelse

            <!-- ===================== SHIFT 2 (MALAM) ===================== -->
            @php 
                $totalTargetS2 = 0; 
                $totalWorkingS2 = 0;
            @endphp
            @foreach($plansS2 as $p)
                @php 
                    $target = $p->s2_plan_reg + $p->s2_plan_ot;
                    $totalTargetS2 += $target;
                    $dur = ($p->cap_per_hour > 0) ? ($target / $p->cap_per_hour) + (($p->dandory_time ?? 15) / 60) : 0;
                    $totalWorkingS2 += $dur;
                @endphp
            @endforeach

            @if($plansS2->count() > 0)
            <tr>
                <td colspan="21" class="shift-header" style="margin-top: 5px;">
                    TOTAL WORKING HOURS / SHIFT 2 (NIGHT OPS) : <span>{{ number_format($totalWorkingS2, 1) }} Jam</span> | TANGGAL: {{ date('d/m/Y', strtotime($date)) }}
                </td>
            </tr>

            @php $startTime = "19:30"; @endphp
            @foreach($plansS2 as $p)
                @php
                    $target = $p->s2_plan_reg + $p->s2_plan_ot;
                    $durMin = ($p->cap_per_hour > 0) ? round(($target / $p->cap_per_hour) * 60) + ($p->dandory_time ?? 15) : 0;
                    $endTime = date('H:i', strtotime($startTime . " + $durMin minutes"));
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td class="text-left text-bold">
                        {{ $p->part_no }}<br>
                        <span style="color: #dc2626; font-family: monospace; font-size: 10px;">{{ $p->no_produksi }}</span>
                    </td>
                    <td class="text-bold">{{ $p->customer_code ?? 'AMA' }}</td>
                    <td>{{ $p->manpower ?? 1 }}</td>
                    <td>{{ $p->process_qty ?? 1 }}</td>
                    <td>{{ $p->qty_lot ?? 200 }}</td>
                    <td>{{ $p->cap_per_hour }}</td>
                    <td class="text-bold">{{ number_format($target) }}</td>
                    <td class="text-bold text-blue">{{ number_format($target) }}</td>
                    <td>{{ $p->dandory_time ?? 15 }}</td>
                    
                    <td class="text-bold">{{ $startTime }}</td>
                    <td class="text-bold">{{ $endTime }}</td>
                    
                    <td>{{ number_format($p->s2_plan_reg) }}</td>
                    <td>{{ number_format($p->s2_plan_ot) }}</td>
                    <td class="text-bold">{{ number_format($target) }}</td>
                    <td class="text-bold">{{ $p->line_code }}</td>
                    <td></td>
                    
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @php $startTime = $endTime; @endphp
            @endforeach
            @endif

            <!-- BARIS KOSONG UNTUK ISIAN MANUAL OPERATOR -->
            @for ($i = 0; $i < 6; $i++)
                <tr>
                    <td>-</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            @endfor

            <!-- TOTAL BAWAH -->
            <tr>
                <td colspan="7" class="text-left text-bold" style="background:#f1f5f9; padding-left:10px;">NO PLANING : {{ date('dmy', strtotime($date)) }}-{{ strtoupper($line_code) }}</td>
                <td class="text-bold">{{ number_format($totalTargetS1 + $totalTargetS2) }}</td>
                <td class="text-bold text-blue">{{ number_format($totalTargetS1 + $totalTargetS2) }}</td>
                <td colspan="3" style="background:#f1f5f9;"></td>
                <td class="text-bold">{{ number_format($plansS1->sum('s1_plan_reg') + $plansS2->sum('s2_plan_reg')) }}</td>
                <td class="text-bold">{{ number_format($plansS1->sum('s1_plan_ot') + $plansS2->sum('s2_plan_ot')) }}</td>
                <td class="text-bold">{{ number_format($totalTargetS1 + $totalTargetS2) }}</td>
                <td colspan="6" style="background:#f1f5f9;"></td>
            </tr>
        </tbody>
    </table>

    <!-- AREA BAWAH: PROBLEM & TANDA TANGAN -->
    <div class="footer-grid">
        <table class="problem-table">
            <tr><th>PROBLEM</th><td></td></tr>
            <tr><th>TEMPORARY ACTION</th><td></td></tr>
            <tr><th>COUNTER MEASURE</th><td></td></tr>
        </table>

        <table class="sig-table">
            <tr>
                <th>DIBUAT</th>
                <th>DIPERIKSA</th>
                <th>DISETUJUI</th>
                <th>DITERIMA</th>
            </tr>
            <tr>
                <td>PPIC</td>
                <td>LEADER PPIC</td>
                <td>MANAGER</td>
                <td>PRODUKSI</td>
            </tr>
        </table>
    </div>

    <script>
        window.onload = function() { setTimeout(function() { window.print(); }, 500); }
    </script>
</body>
</html>