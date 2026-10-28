<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BPRM Big Press - Line {{ $line_code }}</title>
    <style>
        /* RESET & DASAR */
        body { font-family: Arial, sans-serif; color: #000; background: #fff; margin: 0; padding: 10px; font-size: 9px; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        @page { size: A4 landscape; margin: 5mm; }

        /* KONTROL TOMBOL */
        .print-controls { background: #1e293b; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .btn { padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; border: none; font-size: 12px; text-decoration: none; display: inline-block; }
        .btn-back { background: #475569; color: #fff; }
        .btn-print { background: #3b82f6; color: #fff; }
        @media print { .print-controls, .no-print-area { display: none !important; } }

        /* HEADER CONTAINER */
        .header-container { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .logo-section { display: flex; align-items: center; width: 30%; }
        .logo-section img { height: 40px; margin-right: 10px; }
        .company-info h2 { margin: 0; font-size: 13px; font-weight: bold; }
        .company-info p { margin: 2px 0 0 0; font-size: 9px; }

        .title-section { text-align: center; width: 40%; }
        .title-section h1 { margin: 0; font-size: 18px; font-weight: bold; text-decoration: underline; }
        .title-section p { margin: 4px 0 0 0; font-size: 11px; font-weight: bold; }

        /* TABEL META INFO */
        .meta-table { border-collapse: collapse; font-size: 9px; font-weight: bold; text-align: left; }
        .meta-table th, .meta-table td { border: 1px solid #000; padding: 4px 8px; }
        .meta-table th { background: #f1f5f9; }

        .info-row { display: flex; justify-content: space-between; font-weight: bold; font-size: 10px; margin-bottom: 5px; text-transform: uppercase; }

        /* MAIN BPRM TABLE */
        .bprm-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; text-align: center; }
        .bprm-table th, .bprm-table td { border: 1px solid #000; padding: 4px; vertical-align: middle; }
        .bprm-table th { background: #f8fafc; font-size: 8px; text-transform: uppercase; font-weight: bold; }
        .bprm-table td { font-size: 9px; height: 22px; }
        
        .text-left { text-align: left !important; padding-left: 5px !important; }
        .text-bold { font-weight: bold; }
        
        .shift-header { background: #e2e8f0; text-align: left !important; padding: 4px 8px !important; font-size: 9px; font-weight: bold; text-transform: uppercase; }

        /* FOOTER & TANDA TANGAN */
        .footer-grid { display: flex; justify-content: space-between; margin-top: 15px; }
        .note-box { width: 45%; border: 1px solid #000; padding: 5px; font-size: 9px; font-weight: bold; min-height: 50px; }
        .sig-table { width: 50%; border-collapse: collapse; font-size: 9px; font-weight: bold; text-align: center; }
        .sig-table th, .sig-table td { border: 1px solid #000; padding: 4px; width: 25%; }
        .sig-table th { background: #f8fafc; }
        .sig-table td { height: 45px; vertical-align: bottom; padding-bottom: 5px; }
    </style>
</head>
<body>

    @php
        // Kumpulkan Nomor WOS untuk Header
        $allWos = collect();
        foreach($batchesS1 as $b) { if($b->no_produksi != '-') $allWos->push(preg_replace('/-REV-.*/', '', $b->no_produksi)); }
        foreach($batchesS2 as $b) { if($b->no_produksi != '-') $allWos->push(preg_replace('/-REV-.*/', '', $b->no_produksi)); }
        $uniqueWos = $allWos->unique()->values();
        
        $displayWos = $uniqueWos->count() > 0 ? strtoupper($uniqueWos->first()) : '-';
        if($uniqueWos->count() > 1) {
            $displayWos .= ' (+'.($uniqueWos->count() - 1).')';
        }
    @endphp

    <div class="print-controls no-print-area">
        <span style="color: #fff; font-size: 14px; font-weight: bold;">FORM CETAK BPRM (BIG PRESS) — Line: {{ strtoupper($line_code) }}</span>
        <div>
            <button onclick="window.close()" class="btn btn-back">TUTUP / KEMBALI</button>
            <button onclick="window.print()" class="btn btn-print">🖨️ CETAK FORMULIR</button>
        </div>
    </div>

    <!-- HEADER LOGO & INFO -->
    <div class="header-container">
        <div class="logo-section">
            <img src="{{ asset('public/admin/img/Logo-asalta.png') }}" alt="Logo Asalta" onerror="this.style.display='none'">
            <div class="company-info">
                <h2>PT. Asalta Mandiri Agung</h2>
                <p>Dept. PPC<br>LOGISTICS & MATERIAL</p>
            </div>
        </div>
        
        <div class="title-section">
            <h1>BUKTI PENYERAHAN MATERIAL</h1>
            <p>(BPRM – BIG PRESS)</p>
        </div>

        <div>
            <table class="meta-table">
                <tr>
                    <th>WOS NO:</th>
                    <td style="font-family: monospace;">{{ $displayWos }}</td>
                </tr>
                <tr>
                    <th>BPRM NO:</th>
                    <td>{{ date('d/m', strtotime($date)) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="info-row">
        <div>MESIN / LINE : {{ strtoupper($line_code) }}</div>
        <div>HARI / TANGGAL : {{ date('d-m-Y', strtotime($date)) }}</div>
    </div>

    <!-- TABEL DATA BPRM -->
    <table class="bprm-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 2%;">NO</th>
                <th rowspan="2" style="width: 5%;">CUSTOMER</th>
                <th rowspan="2" style="width: 15%;">PART NUMBER</th>
                <th rowspan="2" style="width: 18%;">PART NAME</th>
                <th rowspan="2" style="width: 8%;">NO. SJ</th>
                <th rowspan="2" style="width: 8%;">NO. COIL</th>
                <th rowspan="2" style="width: 6%;">SPEC</th>
                <th rowspan="2" style="width: 10%;">SIZE</th>
                <th colspan="2">QUANTITY</th>
                <th colspan="5">ACTUAL PRODUCTION</th>
                <th rowspan="2" style="width: 10%;">REMARK</th>
            </tr>
            <tr>
                <th style="width: 4%;">SHEET</th>
                <th style="width: 4%;">PCS</th>
                <th style="width: 4%;">OK</th>
                <th style="width: 4%;">WIP</th>
                <th style="width: 4%;">NG</th>
                <th style="width: 4%;">REPAIR</th>
                <th style="width: 4%;">RETURN</th>
            </tr>
        </thead>
        <tbody>
            <!-- ================= SHIFT 1 ================= -->
            <tr>
                <td colspan="19" class="shift-header">SHIFT I</td>
            </tr>
            @php $no = 1; @endphp
            @forelse($batchesS1 as $b)
                @php
                    // Cek Status Revisi
                    $revLabel = '';
                    if (isset($b->keterangan) && preg_match('/\(R(\d+)\)/', $b->keterangan, $matches)) {
                        $revLabel = "DIREVISI PPIC (R{$matches[1]})";
                    } elseif (strpos($b->keterangan, 'PAIR_MAIN:') === 0 || str_ends_with($b->no_produksi, '-M')) {
                        // Jika tidak direvisi tapi ini part gabungan
                        $revLabel = "AUTO-DEPLOY (MERGED)";
                    } else {
                        $revLabel = "AUTO-DEPLOY FROM PPIC";
                    }
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td class="text-bold">{{ $b->customer_code }}</td>
                    <td class="text-left text-bold" style="font-family: monospace;">{{ strtoupper($b->display_part_no) }}</td>
                    <td class="text-left" style="font-size: 8px;">{{ strtoupper($b->display_part_name) }}</td>
                    <td class="text-bold" style="font-size: 8px;">{{ strtoupper($b->no_surat_jalan) }}</td>
                    <td class="text-bold">{{ strtoupper($b->coil_id) }}</td>
                    <td>{{ $b->spec }}</td>
                    <td>{{ $b->size }}</td>
                    <td class="text-bold">{{ number_format($b->qty_ambil_pcs) }}</td>
                    <td class="text-bold">{{ number_format($b->qty_ambil_pcs) }}</td>
                    
                    <!-- Area Isian Manual Aktual Tulis Tangan -->
                    <td></td><td></td><td></td><td></td><td></td>
                    
                    <td class="text-left" style="font-size: 7px;">{{ $revLabel }}</td>
                </tr>
            @empty
                <tr><td colspan="19" style="padding: 10px; font-style: italic;">Tidak ada request material untuk Shift 1.</td></tr>
            @endforelse
            
            <!-- Baris Kosong Cadangan S1 -->
            @for ($i = 0; $i < 2; $i++)
                <tr>
                    <td>{{ $no++ }}</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            @endfor

            <!-- ================= SHIFT 2 ================= -->
            <tr>
                <td colspan="19" class="shift-header">SHIFT II</td>
            </tr>
            @php $no = 1; @endphp
            @foreach($batchesS2 as $b)
                @php
                    $revLabel = '';
                    if (isset($b->keterangan) && preg_match('/\(R(\d+)\)/', $b->keterangan, $matches)) {
                        $revLabel = "DIREVISI PPIC (R{$matches[1]})";
                    } elseif (strpos($b->keterangan, 'PAIR_MAIN:') === 0 || str_ends_with($b->no_produksi, '-M')) {
                        $revLabel = "AUTO-DEPLOY (MERGED)";
                    } else {
                        $revLabel = "AUTO-DEPLOY FROM PPIC";
                    }
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td class="text-bold">{{ $b->customer_code }}</td>
                    <td class="text-left text-bold" style="font-family: monospace;">{{ strtoupper($b->display_part_no) }}</td>
                    <td class="text-left" style="font-size: 8px;">{{ strtoupper($b->display_part_name) }}</td>
                    <td class="text-bold" style="font-size: 8px;">{{ strtoupper($b->no_surat_jalan) }}</td>
                    <td class="text-bold">{{ strtoupper($b->coil_id) }}</td>
                    <td>{{ $b->spec }}</td>
                    <td>{{ $b->size }}</td>
                    <td class="text-bold">{{ number_format($b->qty_ambil_pcs) }}</td>
                    <td class="text-bold">{{ number_format($b->qty_ambil_pcs) }}</td>
                    
                    <!-- Area Isian Manual Aktual Tulis Tangan -->
                    <td></td><td></td><td></td><td></td><td></td>
                    
                    <td class="text-left" style="font-size: 7px;">{{ $revLabel }}</td>
                </tr>
            @endforeach
            
            <!-- Baris Kosong Cadangan S2 -->
            @for ($i = 0; $i < 3; $i++)
                <tr>
                    <td>{{ $no++ }}</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="footer-grid">
        <div class="note-box">
            Note :
        </div>

        <table class="sig-table">
            <tr>
                <th>DISETUJUI</th>
                <th>DIPERIKSA</th>
                <th>DIBUAT</th>
                <th>DITERIMA</th>
            </tr>
            <tr>
                <td>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</td>
                <td>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</td>
                <td>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</td>
                <td>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</td>
            </tr>
        </table>
    </div>

    <script>
        window.onload = function() { setTimeout(function() { window.print(); }, 500); }
    </script>
</body>
</html>