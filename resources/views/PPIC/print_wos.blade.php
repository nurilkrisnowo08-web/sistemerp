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
        @media print { .print-controls, .no-print-area { display: none !important; } }

        /* HEADER WOS */
        .header-container { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; border-bottom: 3px double #000; padding-bottom: 10px; }
        
        .logo-section { display: flex; align-items: center; width: 30%; }
        .logo-section img { height: 45px; margin-right: 15px; }
        .company-info h2 { margin: 0; font-size: 14px; font-weight: 900; text-transform: uppercase; }
        .company-info h3 { margin: 2px 0 0 0; font-size: 10px; font-weight: bold; font-weight: normal; }
        
        .title-section { text-align: center; width: 40%; }
        .title-section h1 { margin: 0; font-size: 20px; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; }
        .title-section h3 { margin: 5px 0 0 0; font-size: 12px; font-weight: bold; color: #1e293b; }

        .meta-section { text-align: right; width: 30%; font-size: 10px; font-weight: bold; line-height: 1.6; }
        .meta-section span.val { color: #dc2626; font-family: monospace; font-size: 11px; }

        /* MAIN TABLE */
        .wos-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .wos-table th, .wos-table td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; }
        .wos-table th { background: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 8px; }
        .wos-table td { font-size: 9px; height: 25px; }
        
        .text-left { text-align: left !important; padding-left: 5px !important; }
        .text-bold { font-weight: bold; }
        .text-blue { color: #2563eb !important; font-weight: bold; }
        .text-green { color: #16a34a !important; font-weight: bold; }
        .text-danger { color: #dc2626 !important; font-weight: bold; }
        
        .shift-header { background: #f8fafc; text-align: left !important; padding: 5px 8px !important; font-size: 9px; font-weight: bold; text-transform: uppercase; border: 1px solid #000; border-bottom: 2px solid #000; }
        .shift-header span { color: #dc2626; }
        
        /* FOOTER BLOCKS */
        .footer-grid { display: flex; gap: 15px; margin-top: 5px; }
        .problem-table { width: 45%; border-collapse: collapse; font-size: 9px; font-weight: bold; }
        .problem-table th, .problem-table td { border: 1px solid #000; padding: 6px; text-align: left; }
        .problem-table th { width: 30%; background: #f8fafc; }
        .sig-table { width: 55%; border-collapse: collapse; font-size: 9px; font-weight: bold; text-align: center; }
        .sig-table th, .sig-table td { border: 1px solid #000; padding: 6px; width: 25%; }
        .sig-table th { background: #f8fafc; }
        .sig-table td { height: 50px; vertical-align: bottom; padding-bottom: 5px; }
    </style>
</head>
<body>

    @php
        // ✨ FUNGSI SAKTI: MENGGABUNGKAN PART KIRI & KANAN JADI SATU BARIS ✨
        function mergeWosPlans($plans) {
            $merged = collect();
            foreach($plans as $p) {
                // Hilangkan akhiran -M atau -S agar terdeteksi sebagai WOS yang sama
                $baseProd = preg_replace('/-[MS]$/', '', $p->no_produksi);
                
                // Jika belum diregister (masih '-'), pastikan tidak digabung ngawur dengan antrean idle lainnya
                if (empty($baseProd) || $baseProd === '-') {
                    $baseProd = 'UNREG-' . $p->id; 
                }
                
                if (!$merged->has($baseProd)) {
                    $item = clone $p;
                    $item->display_part_no = $item->part_no;
                    $item->display_part_name = $item->part_name ?? 'WOS PART';
                    $item->display_no_produksi = preg_replace('/-[MS]$/', '', $p->no_produksi); // Tampilkan tanpa -M / -S
                    $merged->put($baseProd, $item);
                } else {
                    $existing = $merged->get($baseProd);
                    
                    // Gabungkan Part Number
                    if (!str_contains($existing->display_part_no, $p->part_no)) {
                        $existing->display_part_no .= ' / ' . $p->part_no;
                    }
                    
                    // Gabungkan Part Name
                    $pName = $p->part_name ?? 'WOS PART';
                    if (!str_contains($existing->display_part_name, $pName)) {
                        $existing->display_part_name .= ' / ' . $pName;
                    }
                    
                    // Ambil angka Aktual (OK/NG) terbesar di antara keduanya
                    $existing->actual_ok = max($existing->actual_ok ?? 0, $p->actual_ok ?? 0);
                    $existing->actual_ng = max($existing->actual_ng ?? 0, $p->actual_ng ?? 0);
                    
                    $merged->put($baseProd, $existing);
                }
            }
            return $merged->values();
        }

        // Terapkan fungsi merge
        $plansS1 = mergeWosPlans($plansS1);
        $plansS2 = mergeWosPlans($plansS2);

        // Kumpulkan Nomor WOS untuk Header Pojok Kanan Atas
        $allWos = collect();
        foreach($plansS1 as $p) { if($p->display_no_produksi != '-') $allWos->push(preg_replace('/-REV-.*/', '', $p->display_no_produksi)); }
        foreach($plansS2 as $p) { if($p->display_no_produksi != '-') $allWos->push(preg_replace('/-REV-.*/', '', $p->display_no_produksi)); }
        $uniqueWos = $allWos->unique()->values();
        
        $displayWos = $uniqueWos->count() > 0 ? strtoupper($uniqueWos->first()) : 'AUTO-DEPLOY';
        if($uniqueWos->count() > 1) {
            $displayWos .= ' (+'.($uniqueWos->count() - 1).' WOS)';
        }
    @endphp

    <!-- KONTROL TOMBOL -->
    <div class="print-controls no-print-area">
        <button onclick="window.close()" class="btn btn-back">⬅ TUTUP HALAMAN</button>
        <button onclick="window.print()" class="btn btn-print">🖨️ CETAK WOS (FULL DAY)</button>
    </div>

    <div class="header-container">
        <!-- Logo & Nama PT -->
        <div class="logo-section">
            <img src="{{ asset('public/admin/img/Logo-asalta.png') }}" alt="Logo Asalta" onerror="this.style.display='none'">
            <div class="company-info">
                <h2>PT. Asalta Mandiri Agung</h2>
                <h3>Dept. PPC<br>LOGISTICS & PRODUCTION</h3>
            </div>
        </div>
        
        <!-- Judul Tengah -->
        <div class="title-section">
            <h1>WORK ORDER SHEET</h1>
            <h3>[ STAMPING STATION : {{ strtoupper($line_code) }} ]</h3>
        </div>

        <!-- Tanggal Kanan & Nomor WOS Dinamis -->
        <div class="meta-section">
            TANGGAL : <span style="color:#2563eb;">{{ date('d F Y', strtotime($date)) }}</span><br>
            NO DOKUMEN : <span class="val">{{ $displayWos }}</span>
        </div>
    </div>

    <!-- TABEL UTAMA WOS -->
    <table class="wos-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 2%;">NO</th>
                <th rowspan="2" style="width: 20%;">IDENTIFIKASI PART</th>
                <th rowspan="2" style="width: 4%;">CUST</th>
                <th rowspan="2" style="width: 3%;">MP</th>
                <th rowspan="2" style="width: 3%;">PROC</th>
                <th rowspan="2" style="width: 4%;">QTY LOT</th>
                <th rowspan="2" style="width: 5%;">CAP / HOUR</th>
                <th rowspan="2" style="width: 5%;">TOTAL TARGET</th>
                <th rowspan="2" style="width: 5%;">TOTAL STROKE</th>
                <th rowspan="2" style="width: 4%;">DANDORY (MIN)</th>
                <th colspan="2">PLAN WAKTU</th>
                <th rowspan="2" style="width: 8%;">M/C LINE</th>
                <th colspan="2">ACTUAL WAKTU</th>
                <th colspan="3">HASIL PRODUKSI</th>
                <th rowspan="2" style="width: 15%;">KETERANGAN / DOWNTIME</th>
            </tr>
            <tr>
                <th style="width: 4%;">START</th>
                <th style="width: 4%;">AKHIR</th>
                <th style="width: 4%;">MULAI</th>
                <th style="width: 4%;">SELESAI</th>
                <th style="width: 4%;">OK</th>
                <th style="width: 4%;">NG</th>
                <th style="width: 4%;">WH</th>
            </tr>
        </thead>
        <tbody>
            <!-- ===================== SHIFT 1 (PAGI) ===================== -->
            @php 
                $totalTargetS1 = 0; $totalWorkingS1 = 0;
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
                <td colspan="19" class="shift-header">
                    SHIFT 1 (DAY OPS) | TOTAL ESTIMASI RUN-TIME : <span>{{ number_format($totalWorkingS1, 1) }} Jam</span>
                </td>
            </tr>

            @php $no = 1; $startTime = "07:30"; @endphp
            @forelse($plansS1 as $p)
                @php
                    $target = $p->s1_plan_reg + $p->s1_plan_ot;
                    $durMin = ($p->cap_per_hour > 0) ? round(($target / $p->cap_per_hour) * 60) + ($p->dandory_time ?? 15) : 0;
                    $endTime = date('H:i', strtotime($startTime . " + $durMin minutes"));

                    // ✨ Indikator Revisi ✨
                    $revLabel = '';
                    if (isset($p->keterangan) && preg_match('/\(R(\d+)\)/', $p->keterangan, $matches)) {
                        $revLabel = " <span style='color: #2563eb; font-weight: bold;'>[REV-{$matches[1]}]</span>";
                    }
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td class="text-left text-bold">
                        {{ $p->display_part_no }}<br>
                        <span style="font-size: 8px; font-weight: normal; color: #475569;">{{ Str::limit($p->display_part_name, 50) }}</span><br>
                        <span style="color: #dc2626; font-family: monospace; font-size: 10px;">{{ strtoupper($p->display_no_produksi) }}{!! $revLabel !!}</span>
                    </td>
                    <td class="text-bold">{{ $p->customer_code ?? 'AMA' }}</td>
                    <td>{{ $p->manpower ?? 1 }}</td>
                    <td>{{ $p->process_qty ?? 1 }}</td>
                    <td>{{ $p->qty_lot ?? 200 }}</td>
                    <td>{{ $p->cap_per_hour }}</td>
                    <td class="text-blue">{{ number_format($target) }}</td>
                    <td class="text-blue">{{ number_format($target) }}</td>
                    <td>{{ $p->dandory_time ?? 15 }}</td>
                    
                    <td class="text-bold">{{ $startTime }}</td>
                    <td class="text-bold">{{ $endTime }}</td>
                    
                    <td class="text-bold">{{ $p->line_code }}</td>
                    
                    <td class="text-bold" style="color: #2563eb;">{{ $p->actual_start ?: '' }}</td>
                    <td class="text-bold" style="color: #2563eb;">{{ $p->actual_end ?: '' }}</td>
                    
                    <td class="text-bold text-green" style="font-size: 11px;">{{ $p->actual_ok > 0 ? number_format($p->actual_ok) : '' }}</td>
                    <td class="text-bold text-danger" style="font-size: 11px;">{{ $p->actual_ng > 0 ? number_format($p->actual_ng) : '' }}</td>
                    <td></td>
                    
                    <td class="text-left" style="font-size: 8px;">
                        @if(strpos($p->keterangan, 'PAIR_MAIN:') === 0)
                            AUTO-DEPLOY (MERGED)
                        @else
                            {{ Str::limit($p->keterangan ?? 'AUTO-DEPLOY', 40) }}
                        @endif
                    </td>
                </tr>
                @php $startTime = $endTime; @endphp
            @empty
                <tr><td colspan="19" style="padding: 10px; font-style: italic;">Tidak ada plan Shift 1 untuk line ini.</td></tr>
            @endforelse

            <!-- ===================== SHIFT 2 (MALAM) ===================== -->
            @php 
                $totalTargetS2 = 0; $totalWorkingS2 = 0;
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
                <td colspan="19" class="shift-header" style="margin-top: 5px;">
                    SHIFT 2 (NIGHT OPS) | TOTAL ESTIMASI RUN-TIME : <span>{{ number_format($totalWorkingS2, 1) }} Jam</span>
                </td>
            </tr>

            @php $startTime = "19:30"; @endphp
            @foreach($plansS2 as $p)
                @php
                    $target = $p->s2_plan_reg + $p->s2_plan_ot;
                    $durMin = ($p->cap_per_hour > 0) ? round(($target / $p->cap_per_hour) * 60) + ($p->dandory_time ?? 15) : 0;
                    $endTime = date('H:i', strtotime($startTime . " + $durMin minutes"));

                    // ✨ Indikator Revisi ✨
                    $revLabel = '';
                    if (isset($p->keterangan) && preg_match('/\(R(\d+)\)/', $p->keterangan, $matches)) {
                        $revLabel = " <span style='color: #2563eb; font-weight: bold;'>[REV-{$matches[1]}]</span>";
                    }
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td class="text-left text-bold">
                        {{ $p->display_part_no }}<br>
                        <span style="font-size: 8px; font-weight: normal; color: #475569;">{{ Str::limit($p->display_part_name, 50) }}</span><br>
                        <span style="color: #dc2626; font-family: monospace; font-size: 10px;">{{ strtoupper($p->display_no_produksi) }}{!! $revLabel !!}</span>
                    </td>
                    <td class="text-bold">{{ $p->customer_code ?? 'AMA' }}</td>
                    <td>{{ $p->manpower ?? 1 }}</td>
                    <td>{{ $p->process_qty ?? 1 }}</td>
                    <td>{{ $p->qty_lot ?? 200 }}</td>
                    <td>{{ $p->cap_per_hour }}</td>
                    <td class="text-blue">{{ number_format($target) }}</td>
                    <td class="text-blue">{{ number_format($target) }}</td>
                    <td>{{ $p->dandory_time ?? 15 }}</td>
                    
                    <td class="text-bold">{{ $startTime }}</td>
                    <td class="text-bold">{{ $endTime }}</td>
                    
                    <td class="text-bold">{{ $p->line_code }}</td>
                    
                    <td class="text-bold" style="color: #2563eb;">{{ $p->actual_start ?: '' }}</td>
                    <td class="text-bold" style="color: #2563eb;">{{ $p->actual_end ?: '' }}</td>
                    
                    <td class="text-bold text-green" style="font-size: 11px;">{{ $p->actual_ok > 0 ? number_format($p->actual_ok) : '' }}</td>
                    <td class="text-bold text-danger" style="font-size: 11px;">{{ $p->actual_ng > 0 ? number_format($p->actual_ng) : '' }}</td>
                    <td></td>
                    
                    <td class="text-left" style="font-size: 8px;">
                        @if(strpos($p->keterangan, 'PAIR_MAIN:') === 0)
                            AUTO-DEPLOY (MERGED)
                        @else
                            {{ Str::limit($p->keterangan ?? 'AUTO-DEPLOY', 40) }}
                        @endif
                    </td>
                </tr>
                @php $startTime = $endTime; @endphp
            @endforeach
            @endif

            <!-- BARIS KOSONG UNTUK ISIAN MANUAL OPERATOR -->
            @for ($i = 0; $i < 4; $i++)
                <tr>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            @endfor

            <!-- TOTAL BAWAH -->
            <tr>
                <td colspan="7" class="text-bold" style="background:#e2e8f0; text-align: right; padding-right: 15px;">TOTAL KESELURUHAN TARGET HARIAN :</td>
                <td class="text-bold" style="background:#f1f5f9; color:#dc2626;">{{ number_format($totalTargetS1 + $totalTargetS2) }}</td>
                <td colspan="11" style="background:#e2e8f0;"></td>
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