<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BPRM_SMALL_{{ strtoupper(str_replace(' ', '_', $line_code)) }}_{{ date('Ymd', strtotime($date)) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark: #0f172a;
            --slate: #475569;
            --muted: #64748b;
            --border-thick: #0f172a;
            --border-thin: #94a3b8;
            --stamp-blue: #1d4ed8;
            --success-green: #047857;
        }

        * { box-sizing: border-box; }

        @page {
            size: A4 landscape;
            margin: 7mm 9mm;
        }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            font-size: 9.5px;
            color: var(--dark);
            margin: 0;
            padding: 10px;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .bprm-print-wrapper {
            max-width: 100%;
            margin: 0 auto;
        }

        .no-print-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 14px;
            border: 1px solid #cbd5e1;
        }
        .btn-screen {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 11px;
            padding: 7px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none !important;
        }
        .btn-close { background: #64748b; color: #fff; }
        .btn-print { background: #047857; color: #fff; }

        .doc-header {
            display: table;
            width: 100%;
            border-bottom: 3.5px solid var(--border-thick);
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header-col { display: table-cell; vertical-align: middle; }
        
        .company-identity { width: 35%; }
        .company-identity h2 { margin: 0; font-size: 17px; font-weight: 900; letter-spacing: 0.5px; }
        .company-identity p { margin: 2px 0 0; font-size: 8.5px; color: var(--muted); font-weight: 700; text-transform: uppercase; }

        .report-title-box { width: 40%; text-align: center; }
        .report-title-box h1 { margin: 0; font-size: 15px; font-weight: 900; letter-spacing: 1.2px; text-transform: uppercase; }
        .report-title-box span { font-size: 10.5px; font-weight: 800; color: #047857; font-family: 'JetBrains Mono', monospace; display: block; margin-top: 2px; }

        .doc-meta { width: 25%; text-align: right; font-size: 8.5px; line-height: 1.4; font-weight: 600; }
        .doc-meta b { color: var(--dark); font-weight: 800; }

        table.bprm-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid var(--border-thick);
            margin-bottom: 12px;
        }
        table.bprm-table th {
            border: 1px solid var(--border-thick);
            padding: 6px 4px;
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            background: #f1f5f9 !important;
            text-align: center;
        }
        table.bprm-table td {
            border: 1px solid var(--border-thin);
            padding: 5px 6px;
            vertical-align: middle;
            font-size: 9.5px;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        .shift-header-divider td {
            background: #047857 !important;
            color: #ffffff !important;
            font-weight: 800;
            font-size: 9.5px;
            padding: 5px 10px;
            letter-spacing: 0.5px;
        }

        .doc-footer {
            display: table;
            width: 100%;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .sig-col {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
        }
        .sig-box {
            border: 1px solid #cbd5e1;
            background: #fff;
            border-radius: 6px;
            padding: 6px;
            position: relative;
            height: 85px;
        }
        .sig-title { font-size: 8px; font-weight: 800; text-transform: uppercase; color: var(--muted); margin-bottom: 40px; }
        .sig-name { font-size: 9px; font-weight: 800; text-transform: uppercase; border-top: 1px solid var(--border-thick); padding-top: 3px; }

        .stamp-wrapper {
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%) rotate(-10deg);
            width: 75px;
            height: 75px;
            border: 2px dashed var(--stamp-blue);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--stamp-blue);
            opacity: 0.85;
            pointer-events: none;
            background: rgba(255, 255, 255, 0.45);
        }
        .stamp-top { font-size: 5px; font-weight: 900; text-transform: uppercase; }
        .stamp-mid { font-size: 7.5px; font-weight: 900; border-top: 1px solid var(--stamp-blue); border-bottom: 1px solid var(--stamp-blue); padding: 1px 0; margin: 1px 0; width: 85%; }
        .stamp-bot { font-size: 5px; font-weight: 800; text-transform: uppercase; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print no-print-bar">
        <div>
            <b>BPRM SMALL PRESS PREVIEW</b> — Bukti Penyerahan Raw Material Mesin Small Press
        </div>
        <div>
            <button class="btn-screen btn-close" onclick="window.close()">TUTUP</button>
            <button class="btn-screen btn-print ml-2" onclick="window.print()">🖨️ CETAK DOKUMEN (A4)</button>
        </div>
    </div>

    <div class="bprm-print-wrapper">
        <div class="doc-header">
            <div class="header-col company-identity">
                <h2>PT ASALTA MANDIRI AGUNG</h2>
                <p>Warehouse & Raw Material Logistics Department</p>
            </div>
            <div class="header-col report-title-box">
                <h1>BUKTI PENYERAHAN RAW MATERIAL (BPRM)</h1>
                <span>LINE: {{ strtoupper($line_code) }} (SMALL PRESS) // DATE: {{ date('d F Y', strtotime($date)) }}</span>
            </div>
            <div class="header-col doc-meta">
                FORM NO : <b>AMA-LOG-BPRM-SP01</b><br>
                DESTINATION : <b>M/C {{ strtoupper($line_code) }}</b><br>
                PRINTED AT : {{ date('d/m/Y H:i') }}
            </div>
        </div>

        <table class="bprm-table">
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="14%">WOS Reference</th>
                    <th width="16%">Part Destination</th>
                    <th width="8%">Client</th>
                    <th width="13%">Tag / Coil Number</th>
                    <th width="15%">Material Spec & Size</th>
                    <th width="12%">Surat Jalan Asal</th>
                    <th width="8%">Take (Lbr)</th>
                    <th width="10%">Paraf Terima</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $totalLbrS1 = 0;
                    $totalLbrS2 = 0;
                @endphp

                {{-- SHIFT 1 --}}
                @if(isset($batchesS1) && count($batchesS1) > 0)
                    <tr class="shift-header-divider">
                        <td colspan="9">PENYERAHAN MATERIAL SHIFT 1 (PAGI)</td>
                    </tr>
                    @foreach($batchesS1 as $idx => $b)
                    @php $totalLbrS1 += $b->qty_ambil_pcs; @endphp
                    <tr>
                        <td class="text-center font-weight-bold">{{ $idx + 1 }}</td>
                        <td class="font-mono font-weight-bold text-center" style="color:#047857;">
                            {{ $b->no_produksi }}
                        </td>
                        <td>
                            <b class="font-mono">{{ $b->display_part_no ?? $b->material_code }}</b><br>
                            <span style="font-size:8px; color:#475569;">{{ $b->display_part_name ?? $b->material_name }}</span>
                        </td>
                        <td class="text-center font-weight-bold">{{ $b->customer_code ?? 'SAI' }}</td>
                        <td class="font-mono font-weight-bold text-center">
                            🏷️ {{ $b->coil_id ?? '-' }}
                        </td>
                        <td class="font-mono small">
                            <b>{{ $b->spec ?? '-' }}</b><br>
                            <span>{{ $b->size ?? '-' }}</span>
                        </td>
                        <td class="font-mono text-center small text-muted">
                            {{ $b->no_surat_jalan ?? '-' }}
                        </td>
                        <td class="text-right font-mono font-weight-bold" style="font-size:11px;">
                            {{ number_format($b->qty_ambil_pcs) }}
                        </td>
                        <td class="text-center" style="border: 1px dashed #94a3b8;"></td>
                    </tr>
                    @endforeach
                @endif

                {{-- SHIFT 2 --}}
                @if(isset($batchesS2) && count($batchesS2) > 0)
                    <tr class="shift-header-divider">
                        <td colspan="9">PENYERAHAN MATERIAL SHIFT 2 (MALAM)</td>
                    </tr>
                    @foreach($batchesS2 as $idx => $b)
                    @php $totalLbrS2 += $b->qty_ambil_pcs; @endphp
                    <tr>
                        <td class="text-center font-weight-bold">{{ $idx + 1 }}</td>
                        <td class="font-mono font-weight-bold text-center" style="color:#047857;">
                            {{ $b->no_produksi }}
                        </td>
                        <td>
                            <b class="font-mono">{{ $b->display_part_no ?? $b->material_code }}</b><br>
                            <span style="font-size:8px; color:#475569;">{{ $b->display_part_name ?? $b->material_name }}</span>
                        </td>
                        <td class="text-center font-weight-bold">{{ $b->customer_code ?? 'SAI' }}</td>
                        <td class="font-mono font-weight-bold text-center">
                            🏷️ {{ $b->coil_id ?? '-' }}
                        </td>
                        <td class="font-mono small">
                            <b>{{ $b->spec ?? '-' }}</b><br>
                            <span>{{ $b->size ?? '-' }}</span>
                        </td>
                        <td class="font-mono text-center small text-muted">
                            {{ $b->no_surat_jalan ?? '-' }}
                        </td>
                        <td class="text-right font-mono font-weight-bold" style="font-size:11px;">
                            {{ number_format($b->qty_ambil_pcs) }}
                        </td>
                        <td class="text-center" style="border: 1px dashed #94a3b8;"></td>
                    </tr>
                    @endforeach
                @endif

                @if((!isset($batchesS1) || count($batchesS1) == 0) && (!isset($batchesS2) || count($batchesS2) == 0))
                    <tr>
                        <td colspan="9" class="text-center py-4" style="color:#64748b; font-style:italic;">
                            -- Tidak ada alokasi material untuk mesin Small Press ini pada tanggal {{ date('d F Y', strtotime($date)) }} --
                        </td>
                    </tr>
                @else
                    <tr style="background:#e2e8f0; font-weight:900;">
                        <td colspan="7" class="text-right" style="padding-right:12px;">TOTAL LEMBAR MATERIAL DITRANSFER :</td>
                        <td class="text-right font-mono" style="font-size:11.5px; color:#047857;">{{ number_format($totalLbrS1 + $totalLbrS2) }}</td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="doc-footer">
            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Diserahkan Oleh (Warehouse RM)</div>
                    <div class="stamp-wrapper">
                        <span class="stamp-top">PT ASALTA MANDIRI AGUNG</span>
                        <span class="stamp-mid">WAREHOUSE RM</span>
                        <span class="stamp-bot">TRANSFERRED</span>
                    </div>
                    <div class="sig-name">Admin Gudang RM</div>
                </div>
            </div>

            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Diterima Oleh (Small Press)</div>
                    <div class="sig-name">Operator / Leader Press</div>
                </div>
            </div>

            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Divalidasi Oleh (PPIC)</div>
                    <div class="sig-name">PPIC Supervisor</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>