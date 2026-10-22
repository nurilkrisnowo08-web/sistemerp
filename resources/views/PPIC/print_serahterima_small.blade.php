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
            size: A4 portrait;
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
        }
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
        <div><b>FORM CETAK BPRM (BUKTI PENYERAHAN RAW MATERIAL)</b> — Line: <b>{{ $line_code }}</b></div>
        <div>
           <button class="btn-screen btn-close" onclick="window.length ? window.history.back() : window.close()">TUTUP / KEMBALI</button>
            <button class="btn-screen btn-print" onclick="window.print()">🖨️ CETAK FORMULIR</button>
        </div>
    </div>

    <div class="bprm-wrapper">
        
        {{-- Header Form --}}
        <table class="header-table">
            <div class="header">
        <!-- Tambahan Logo & Info Perusahaan -->
        <div style="display: flex; align-items: center;">
            <img src="{{ asset('public/admin/img/Logo-asalta.png') }}" alt="Logo Asalta" style="height: 45px; margin-right: 15px;">
            <div class="company-info">
                <h2>PT. Asalta Mandiri Agung</h2>
                <h3>Dept. PPC<br>LOGISTICS & MATERIAL</h3>
            </div>
        </div>
                <td class="title-area">
                    <h1>BUKTI PENYERAHAN RAW MATERIAL</h1>
                    <h2>(BPRM)</h2>
                </td>
                <td class="meta-box-area">
                    <table>
                        <tr>
                            <td>WOS NO :</td>
                            <td class="mono" style="min-width: 60px;">{{ $batches->first()->no_produksi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>BPRM NO :</td>
                            <td class="mono" style="font-weight: 900; font-size: 10px;">{{ date('d/H', strtotime($date)) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Baris Info Hari/Tanggal & Mesin --}}
        <div class="info-bar">
            <div>MESIN / LINE : <b>{{ $line_code }}</b></div>
            <div>HARI / TANGGAL : <b>{{ date('d-m-Y', strtotime($date)) }}</b></div>
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
                    <td class="mono text-left" style="font-size: 8px;">{{ $b->material_code }}</td>
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
                    <td class="mono text-left" style="font-size: 8px;">{{ $b->material_code }}</td>
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