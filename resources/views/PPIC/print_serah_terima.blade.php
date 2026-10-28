<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BPRM_{{ $line_code }}_{{ date('Ymd', strtotime($date)) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --border-thick: #000000;
            --border-thin: #334155;
            --dark: #000000;
        }

        * { box-sizing: border-box; }

        @page {
            /* WAJIB LANDSCAPE KARENA KOLOMNYA BANYAK (16 Kolom) */
            size: A4 landscape; 
            margin: 6mm 8mm;
        }

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

        .bprm-wrapper {
            max-width: 100%;
            margin: 0 auto;
        }

        /* 🔘 SCREEN TOOLBAR */
        .no-print-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f1f5f9;
            padding: 8px 15px;
            border-radius: 6px;
            margin-bottom: 12px;
            border: 1px solid #cbd5e1;
        }
        .btn-screen {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 10px;
            padding: 6px 14px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .btn-back { background: #64748b; color: #fff; text-decoration: none; display: inline-block; }
        .btn-print { background: #2563eb; color: #fff; }

        /* 🏢 HEADER FORM BPRM */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 2px 4px;
        }
        .logo-area { width: 25%; font-size: 9px; line-height: 1.2; font-weight: 800; }
        .logo-area h3 { margin: 0; font-size: 13px; font-weight: 900; }
        .title-area { width: 50%; text-align: center; }
        .title-area h1 { margin: 0; font-size: 15px; font-weight: 900; letter-spacing: 0.5px; text-decoration: underline; }
        .title-area h2 { margin: 2px 0 0; font-size: 12px; font-weight: 800; }
        .meta-box-area { width: 25%; text-align: right; }
        .meta-box-area table { border-collapse: collapse; float: right; font-size: 8px; font-weight: 800; }
        .meta-box-area td { border: 1px solid var(--border-thick); padding: 3px 6px; }

        .info-bar {
            display: flex;
            justify-content: space-between;
            font-weight: 800;
            font-size: 9px;
            margin: 6px 0 4px 0;
            text-transform: uppercase;
        }

        /* 📋 TABEL UTAMA BPRM */
        table.bprm-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid var(--border-thick);
            margin-bottom: 8px;
        }
        table.bprm-table th {
            border: 1px solid var(--border-thick);
            padding: 5px 3px;
            font-size: 7.5px;
            font-weight: 900;
            text-transform: uppercase;
            text-align: center;
            background: #f8fafc !important;
        }
        table.bprm-table td {
            border: 1px solid var(--border-thick);
            padding: 4px 4px;
            vertical-align: middle;
            font-size: 8px;
            font-weight: 700;
        }

        .mono { font-family: 'JetBrains Mono', monospace; }
        .text-center { text-align: center !important; }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }

        .shift-badge {
            background: #e2e8f0;
            font-weight: 900;
            font-size: 8.5px;
            padding: 3px 6px;
            text-transform: uppercase;
            border-bottom: 1.5px solid var(--border-thick);
        }

        /* ✍️ FOOTER: CATATAN & 4 KOTAK PENGESAHAN */
        .footer-grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 6px;
            page-break-inside: avoid;
        }
        .note-box {
            width: 48%;
            border: 1px solid var(--border-thick);
            padding: 6px;
            min-height: 70px;
            font-weight: 700;
            font-size: 8.5px;
        }
        .sign-table-wrapper {
            width: 48%;
        }<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BPRM Small Press - Line {{ $line_code }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #000; background: #fff; margin: 0; padding: 10px; font-size: 9px; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        @page { size: A4 landscape; margin: 5mm; }

        .print-controls { background: #1e293b; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .btn { padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; border: none; font-size: 12px; text-decoration: none; display: inline-block; }
        .btn-back { background: #475569; color: #fff; }
        .btn-print { background: #3b82f6; color: #fff; }
        @media print { .print-controls { display: none !important; } }

        .header-container { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .logo-section { display: flex; align-items: center; width: 30%; }
        .logo-section img { height: 40px; margin-right: 10px; }
        .company-info h2 { margin: 0; font-size: 13px; font-weight: bold; }
        .company-info p { margin: 2px 0 0 0; font-size: 9px; }

        .title-section { text-align: center; width: 40%; }
        .title-section h1 { margin: 0; font-size: 18px; font-weight: bold; text-decoration: underline; }
        .title-section p { margin: 4px 0 0 0; font-size: 11px; font-weight: bold; }

        .meta-table { border-collapse: collapse; font-size: 9px; font-weight: bold; text-align: left; }
        .meta-table th, .meta-table td { border: 1px solid #000; padding: 4px 8px; }
        .meta-table th { background: #f1f5f9; }

        .info-row { display: flex; justify-content: space-between; font-weight: bold; font-size: 10px; margin-bottom: 5px; text-transform: uppercase; }

        .bprm-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; text-align: center; }
        .bprm-table th, .bprm-table td { border: 1px solid #000; padding: 4px; vertical-align: middle; }
        .bprm-table th { background: #f8fafc; font-size: 8px; text-transform: uppercase; font-weight: bold; }
        .bprm-table td { font-size: 9px; height: 22px; }
        
        .text-left { text-align: left !important; padding-left: 5px !important; }
        .text-bold { font-weight: bold; }
        
        .shift-header { background: #e2e8f0; text-align: left !important; padding: 4px 8px !important; font-size: 9px; font-weight: bold; text-transform: uppercase; }

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
        <span style="color: #fff; font-size: 14px; font-weight: bold;">FORM CETAK BPRM — Line: {{ strtoupper($line_code) }}</span>
        <div>
            <button onclick="window.close()" class="btn btn-back">TUTUP / KEMBALI</button>
            <button onclick="window.print()" class="btn btn-print">🖨️ CETAK FORMULIR</button>
        </div>
    </div>

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
            <p>(BPRM – SMALL PRESS)</p>
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
                    $revLabel = '';
                    if (isset($b->keterangan) && preg_match('/\(R(\d+)\)/', $b->keterangan, $matches)) {
                        $revLabel = "DIREVISI PPIC (R{$matches[1]})";
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
                    
                    <!-- Kolom Isian Manual Aktual -->
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
        table.sign-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--border-thick);
            text-align: center;
        }
        table.sign-table th {
            border: 1px solid var(--border-thick);
            padding: 4px;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            background: #f8fafc;
            width: 25%;
        }
        table.sign-table td {
            border: 1px solid var(--border-thick);
            height: 52px;
            vertical-align: bottom;
            padding-bottom: 2px;
            font-size: 7.5px;
            font-weight: 700;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>

    {{-- Screen Toolbar --}}
    <div class="no-print no-print-bar">
        <div><b>FORM CETAK BPRM </b> — Line: <b>{{ strtoupper($line_code) }}</b></div>
        <div>
            <button class="btn-screen btn-close" onclick="window.length ? window.history.back() : window.close()">TUTUP / KEMBALI</button>
            <button class="btn-screen btn-print" onclick="window.print()">🖨️ CETAK FORMULIR</button>
        </div>
    </div>

    <div class="bprm-wrapper">
        
        {{-- 🔥 HEADER FORM DIPERBAIKI (TIDAK ADA DIV NYASAR) 🔥 --}}
        <table class="header-table">
            <tr>
                <td class="logo-area" style="width: 30%;">
                    <div style="display: flex; align-items: center;">
                        <img src="{{ asset('public/admin/img/Logo-asalta.png') }}" alt="Logo Asalta" style="height: 40px; margin-right: 10px;" onerror="this.style.display='none'">
                        <div>
                            <h3 style="font-size: 12px; margin: 0; padding: 0;">PT. Asalta Mandiri Agung</h3>
                            <div style="font-size: 8px; font-weight: 700; margin-top: 2px;">Dept. PPC<br>LOGISTICS & MATERIAL</div>
                        </div>
                    </div>
                </td>
                
                <td class="title-area" style="width: 40%;">
                    <h1>BUKTI PENYERAHAN MATERIAL</h1>
                    <h2>(BPRM - BIG PRESS)</h2>
                </td>
                
                <td class="meta-box-area" style="width: 30%;">
                    <table style="float: right;">
                        <tr>
                            <td>WOS NO :</td>
                            <td class="mono" style="min-width: 60px; text-align: left;">{{ $batches->first()->no_produksi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>BPRM NO :</td>
                            <td class="mono" style="font-weight: 900; font-size: 10px; text-align: left;">{{ date('d/H', strtotime($date)) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Baris Info Hari/Tanggal & Mesin --}}
        <div class="info-bar">
            <div>MESIN / LINE : <b>{{ strtoupper($line_code) }}</b></div>
            <div>HARI / TANGGAL : <b>{{ \Carbon\Carbon::parse($date)->translatedFormat('d-m-Y') }}</b></div>
        </div>

        {{-- TABEL BPRM GABUNGAN SHIFT I & SHIFT II --}}
        <table class="bprm-table">
            <thead>
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2" width="55">Customer</th>
                    <th rowspan="2" width="90">Part Number</th>
                    <th rowspan="2" width="130">Part Name</th>
                    <th rowspan="2" width="85">No. SJ</th>
                    <th rowspan="2" width="75">No. Coil</th>
                    <th rowspan="2" width="80">Spec</th>
                    <th rowspan="2" width="75">Size</th>
                    <th colspan="2" width="75">Quantity</th>
                    <th colspan="5" width="160">ACTUAL PRODUCTION</th>
                    <th rowspan="2" width="60">REMARK</th>
                </tr>
                <tr>
                    <th width="37">Sheet</th>
                    <th width="38">Pcs</th>
                    <th width="32">OK</th>
                    <th width="32">WIP</th>
                    <th width="32">NG</th>
                    <th width="32">REPAIR</th>
                    <th width="32">RETURN</th>
                </tr>
            </thead>
            <tbody>
                {{-- ==================== BAGIAN SHIFT I ==================== --}}
                <tr>
                    <td colspan="16" class="shift-badge">SHIFT I</td>
                </tr>
                @php $no1 = 1; @endphp
                @forelse($batchesS1 as $b)
                @php
                    $cavity = $b->cavity > 0 ? $b->cavity : 1;
                    $pcsTarget = $b->qty_ambil_pcs * $cavity;
                @endphp
                <tr>
                    <td class="text-center">{{ $no1++ }}</td>
                    <td class="text-center">{{ $b->customer_code ?? 'SAI' }}</td>
                    <td class="mono text-left" style="font-size: 8px;">{{ $b->material_code ?? $b->part_no ?? '-' }}</td>
                    <td class="text-left" style="font-size: 7.5px;">{{ $b->part_name ?? $b->material_name ?? '-' }}</td>
                    <td class="mono text-center" style="font-size: 8px;">{{ $b->no_surat_jalan ?? '-' }}</td>
                    <td class="mono text-center">{{ $b->coil_id ?? '-' }}</td>
                    <td class="text-center" style="font-size: 7.5px;">{{ $b->spec ?? '-' }}</td>
                    <td class="mono text-center" style="font-size: 7.5px;">{{ $b->size ?? '-' }}</td>
                    <td class="mono text-center">{{ number_format($b->qty_ambil_pcs) }}</td>
                    <td class="mono text-center">{{ number_format($pcsTarget) }}</td>
                    <td class="mono text-center font-weight-bold">{{ $b->qty_hasil_ok > 0 ? number_format($b->qty_hasil_ok) : '' }}</td>
                    <td class="mono text-center"></td>
                    <td class="mono text-center font-weight-bold" style="color: #b91c1c;">{{ $b->qty_hasil_ng > 0 ? number_format($b->qty_hasil_ng) : '' }}</td>
                    <td class="mono text-center"></td>
                    <td class="mono text-center">{{ $b->qty_return_warehouse > 0 ? number_format($b->qty_return_warehouse) : '' }}</td>
                    <td class="text-center" style="font-size: 7px;">{{ $b->keterangan ?? '' }}</td>
                </tr>
                @empty
                @for($i = 0; $i < 2; $i++)
                <tr>
                    <td class="text-center">{{ $no1++ }}</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
                @endfor
                @endforelse

                {{-- Menjaga Baris Tetap Rapi Minimal 4 Baris --}}
                @for($i = $no1; $i <= 4; $i++)
                <tr>
                    <td class="text-center">{{ $i }}</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
                @endfor

                {{-- ==================== BAGIAN SHIFT II ==================== --}}
                <tr>
                    <td colspan="16" class="shift-badge">SHIFT II</td>
                </tr>
                @php $no2 = 1; @endphp
                @forelse($batchesS2 as $b)
                @php
                    $cavity = $b->cavity > 0 ? $b->cavity : 1;
                    $pcsTarget = $b->qty_ambil_pcs * $cavity;
                @endphp
                <tr>
                    <td class="text-center">{{ $no2++ }}</td>
                    <td class="text-center">{{ $b->customer_code ?? 'SAI' }}</td>
                    <td class="mono text-left" style="font-size: 8px;">{{ $b->material_code ?? $b->part_no ?? '-' }}</td>
                    <td class="text-left" style="font-size: 7.5px;">{{ $b->part_name ?? $b->material_name ?? '-' }}</td>
                    <td class="mono text-center" style="font-size: 8px;">{{ $b->no_surat_jalan ?? '-' }}</td>
                    <td class="mono text-center">{{ $b->coil_id ?? '-' }}</td>
                    <td class="text-center" style="font-size: 7.5px;">{{ $b->spec ?? '-' }}</td>
                    <td class="mono text-center" style="font-size: 7.5px;">{{ $b->size ?? '-' }}</td>
                    <td class="mono text-center">{{ number_format($b->qty_ambil_pcs) }}</td>
                    <td class="mono text-center">{{ number_format($pcsTarget) }}</td>
                    <td class="mono text-center font-weight-bold">{{ $b->qty_hasil_ok > 0 ? number_format($b->qty_hasil_ok) : '' }}</td>
                    <td class="mono text-center"></td>
                    <td class="mono text-center font-weight-bold" style="color: #b91c1c;">{{ $b->qty_hasil_ng > 0 ? number_format($b->qty_hasil_ng) : '' }}</td>
                    <td class="mono text-center"></td>
                    <td class="mono text-center">{{ $b->qty_return_warehouse > 0 ? number_format($b->qty_return_warehouse) : '' }}</td>
                    <td class="text-center" style="font-size: 7px;">{{ $b->keterangan ?? '' }}</td>
                </tr>
                @empty
                @for($i = 0; $i < 2; $i++)
                <tr>
                    <td class="text-center">{{ $no2++ }}</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
                @endfor
                @endforelse

                @for($i = $no2; $i <= 4; $i++)
                <tr>
                    <td class="text-center">{{ $i }}</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
                @endfor
            </tbody>
        </table>

        {{-- Footer: Catatan & 4 Kotak Tanda Tangan --}}
        <div class="footer-grid">
            <div class="note-box">
                <b>Note :</b>
            </div>

            <div class="sign-table-wrapper">
                <table class="sign-table">
                    <thead>
                        <tr>
                            <th>Disetujui</th>
                            <th>Diperiksa</th>
                            <th>Dibuat</th>
                            <th>Diterima</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</td>
                            <td>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</td>
                            <td>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</td>
                            <td>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>