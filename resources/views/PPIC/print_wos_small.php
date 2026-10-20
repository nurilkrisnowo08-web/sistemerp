<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>WOS_SMALL_{{ strtoupper(str_replace(' ', '_', $line_code)) }}_{{ date('Ymd', strtotime($date)) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark: #0f172a;
            --slate: #475569;
            --muted: #64748b;
            --border-thick: #0f172a;
            --border-thin: #94a3b8;
            --stamp-blue: #1d4ed8;
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

        .wos-print-wrapper {
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
        .btn-print { background: #0f172a; color: #fff; }

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
        .report-title-box h1 { margin: 0; font-size: 16px; font-weight: 900; letter-spacing: 1.5px; text-transform: uppercase; }
        .report-title-box span { font-size: 10.5px; font-weight: 800; color: #d97706; font-family: 'JetBrains Mono', monospace; display: block; margin-top: 2px; }

        .doc-meta { width: 25%; text-align: right; font-size: 8.5px; line-height: 1.4; font-weight: 600; }
        .doc-meta b { color: var(--dark); font-weight: 800; }

        table.wos-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid var(--border-thick);
            margin-bottom: 12px;
        }
        table.wos-table th {
            border: 1px solid var(--border-thick);
            padding: 6px 4px;
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            background: #f1f5f9 !important;
            text-align: center;
        }
        table.wos-table td {
            border: 1px solid var(--border-thin);
            padding: 5px 6px;
            vertical-align: middle;
            font-size: 9.5px;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        .shift-header-divider td {
            background: #0f172a !important;
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
            <b>WOS SMALL PRESS DOCUMENT PREVIEW</b> — Lembar Kerja Khusus Area Small Press
        </div>
        <div>
            <button class="btn-screen btn-close" onclick="window.close()">TUTUP</button>
            <button class="btn-screen btn-print ml-2" onclick="window.print()">🖨️ CETAK DOKUMEN (A4)</button>
        </div>
    </div>

    <div class="wos-print-wrapper">
        <div class="doc-header">
            <div class="header-col company-identity">
                <h2>PT ASALTA MANDIRI AGUNG</h2>
                <p>Plant Karawang // Small Press Production Division</p>
            </div>
            <div class="header-col report-title-box">
                <h1>WORKING ORDER SHEET (WOS)</h1>
                <span>LINE: {{ strtoupper($line_code) }} (SMALL PRESS) // DATE: {{ date('d F Y', strtotime($date)) }}</span>
            </div>
            <div class="header-col doc-meta">
                FORM NO : <b>AMA-PRD-WOS-SP01</b><br>
                AREA WORKSTATION : <b>SMALL PRESS</b><br>
                PRINTED AT : {{ date('d/m/Y H:i') }}
            </div>
        </div>

        <table class="wos-table">
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="14%">WOS Number</th>
                    <th width="16%">Part Number</th>
                    <th width="16%">Part Name</th>
                    <th width="8%">Customer</th>
                    <th width="8%">Target Qty</th>
                    <th width="10%">Mat. Spec / Size</th>
                    <th width="10%">Coil / Tag ID</th>
                    <th width="7%">Start</th>
                    <th width="7%">Finish</th>
                </tr>
            </thead>
            <tbody>
                {{-- SHIFT 1 --}}
                @if(isset($plansS1) && count($plansS1) > 0)
                    <tr class="shift-header-divider">
                        <td colspan="10">OPERASIONAL SHIFT 1 (PAGI)</td>
                    </tr>
                    @foreach($plansS1 as $idx => $p)
                    @php
                        $batchInfo = $batches->where('plan_id', $p->id)->where('shift', 'Pagi')->first();
                        $target = $p->s1_plan_reg + $p->s1_plan_ot;
                    @endphp
                    <tr>
                        <td class="text-center font-weight-bold">{{ $idx + 1 }}</td>
                        <td class="font-mono font-weight-bold text-center" style="color:#d97706;">
                            {{ $batchInfo->no_produksi ?? ('WOS-S1-' . $p->id) }}
                        </td>
                        <td class="font-mono font-weight-bold">{{ $p->part_no }}</td>
                        <td>{{ $batchInfo->material_name ?? '-' }}</td>
                        <td class="text-center font-weight-bold">{{ $p->customer_code ?? '-' }}</td>
                        <td class="text-right font-mono font-weight-bold">{{ number_format($target) }}</td>
                        <td class="small font-mono text-center">{{ $batchInfo->spec ?? '-' }}<br>{{ $batchInfo->size ?? '' }}</td>
                        <td class="font-mono text-center font-weight-bold">{{ $batchInfo->coil_id ?? 'SESUAI BPRM' }}</td>
                        <td class="text-center font-mono font-weight-bold">{{ $p->start_time ?? '07:30' }}</td>
                        <td class="text-center font-mono font-weight-bold">{{ $p->ahir_time ?? '-' }}</td>
                    </tr>
                    @endforeach
                @endif

                {{-- SHIFT 2 --}}
                @if(isset($plansS2) && count($plansS2) > 0)
                    <tr class="shift-header-divider">
                        <td colspan="10">OPERASIONAL SHIFT 2 (MALAM)</td>
                    </tr>
                    @foreach($plansS2 as $idx => $p)
                    @php
                        $batchInfo = $batches->where('plan_id', $p->id)->where('shift', 'Malam')->first();
                        $target = $p->s2_plan_reg + $p->s2_plan_ot;
                    @endphp
                    <tr>
                        <td class="text-center font-weight-bold">{{ $idx + 1 }}</td>
                        <td class="font-mono font-weight-bold text-center" style="color:#d97706;">
                            {{ $batchInfo->no_produksi ?? ('WOS-S2-' . $p->id) }}
                        </td>
                        <td class="font-mono font-weight-bold">{{ $p->part_no }}</td>
                        <td>{{ $batchInfo->material_name ?? '-' }}</td>
                        <td class="text-center font-weight-bold">{{ $p->customer_code ?? '-' }}</td>
                        <td class="text-right font-mono font-weight-bold">{{ number_format($target) }}</td>
                        <td class="small font-mono text-center">{{ $batchInfo->spec ?? '-' }}<br>{{ $batchInfo->size ?? '' }}</td>
                        <td class="font-mono text-center font-weight-bold">{{ $batchInfo->coil_id ?? 'SESUAI BPRM' }}</td>
                        <td class="text-center font-mono font-weight-bold">{{ $p->start_time ?? '19:30' }}</td>
                        <td class="text-center font-mono font-weight-bold">{{ $p->ahir_time ?? '-' }}</td>
                    </tr>
                    @endforeach
                @endif

                @if((!isset($plansS1) || count($plansS1) == 0) && (!isset($plansS2) || count($plansS2) == 0))
                    <tr>
                        <td colspan="10" class="text-center py-4" style="color:#64748b; font-style:italic;">
                            -- Tidak ada jadwal produksi pada mesin Small Press ini untuk tanggal {{ date('d F Y', strtotime($date)) }} --
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="doc-footer">
            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Diterbitkan Oleh (PPIC)</div>
                    <div class="stamp-wrapper">
                        <span class="stamp-top">PT ASALTA MANDIRI AGUNG</span>
                        <span class="stamp-mid">PPIC DEPT</span>
                        <span class="stamp-bot">RELEASED</span>
                    </div>
                    <div class="sig-name">PPIC Staff</div>
                </div>
            </div>

            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Diterima Oleh (Small Press)</div>
                    <div class="sig-name">Foreman / Leader Small Press</div>
                </div>
            </div>

            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Diverifikasi Oleh (QC)</div>
                    <div class="sig-name">QC Inspector</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>