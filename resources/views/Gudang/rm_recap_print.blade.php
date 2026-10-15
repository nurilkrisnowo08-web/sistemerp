<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RM_RECAP_{{ date('Ymd', strtotime($targetDate ?? now())) }}</title>
    <!-- Font Standar Industri Manufaktur -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --dark: #0f172a; 
            --slate: #475569;
            --muted: #64748b;
            --border-thick: #0f172a;
            --border-thin: #cbd5e1;
            --stamp-blue: #1d4ed8;
            --success-green: #047857;
            --danger-red: #b91c1c;
            --return-indigo: #4338ca;
        }
        
        * { box-sizing: border-box; }
        
        @page { 
            size: A4 landscape; 
            margin: 8mm 10mm; 
        }

        body { 
            font-family: 'Plus Jakarta Sans', Arial, sans-serif; 
            font-size: 9.5px; 
            color: var(--dark); 
            margin: 0; 
            padding: 12px; 
            background: #fff; 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact; 
        }

        .report-wrapper { max-width: 100%; margin: 0 auto; position: relative; }

        /* 🔘 SCREEN ACTION BAR (Hanya di layar monitor) */
        .no-print-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            border: 1px solid #cbd5e1;
        }
        .btn-screen {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 11px;
            padding: 8px 18px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }
        .btn-close { background: #64748b; color: #fff; }
        .btn-print { background: #2563eb; color: #fff; }

        /* 🏛️ INDUSTRIAL DOCUMENT HEADER */
        .doc-header { 
            display: table; 
            width: 100%; 
            border-bottom: 3.5px solid var(--border-thick); 
            padding-bottom: 10px; 
            margin-bottom: 12px; 
        }
        .header-col { display: table-cell; vertical-align: middle; }
        
        .company-identity { width: 35%; }
        .company-identity h2 { margin: 0; font-size: 18px; font-weight: 900; letter-spacing: 0.5px; }
        .company-identity p { margin: 2px 0 0; font-size: 8.5px; color: var(--muted); font-weight: 700; text-transform: uppercase; }

        .report-title-box { width: 40%; text-align: center; }
        .report-title-box h1 { margin: 0; font-size: 15px; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; }
        .report-title-box span { font-size: 9.5px; font-weight: 700; color: #2563eb; font-family: 'JetBrains Mono', monospace; }

        .doc-meta { width: 25%; text-align: right; font-size: 8.5px; line-height: 1.4; font-weight: 600; }
        .doc-meta b { color: var(--dark); font-weight: 800; }

        /* 📊 KPI METRIC STRIP */
        .kpi-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 12px;
        }
        .kpi-card {
            flex: 1;
            border: 1px solid var(--border-thick);
            padding: 6px 12px;
            background: #f8fafc;
            border-radius: 4px;
        }
        .kpi-title { font-size: 7.5px; text-transform: uppercase; font-weight: 800; color: var(--muted); }
        .kpi-value { font-family: 'JetBrains Mono', monospace; font-size: 13.5px; font-weight: 800; color: var(--dark); margin-top: 1px; }

        /* 📋 INDUSTRIAL TABLE */
        table.recap-table { 
            width: 100%; 
            border-collapse: collapse; 
            border: 1.5px solid var(--border-thick); 
            margin-bottom: 15px;
        }
        table.recap-table th { 
            border: 1px solid var(--border-thick); 
            padding: 7px 4px; 
            font-size: 8.5px; 
            font-weight: 800; 
            text-transform: uppercase; 
            background: #f1f5f9 !important; 
            text-align: center;
            letter-spacing: 0.5px;
        }
        table.recap-table td { 
            border: 1px solid #94a3b8; 
            padding: 5px 6px; 
            vertical-align: middle; 
            font-size: 9.5px;
        }

        .text-left { text-align: left !important; }
        .text-center { text-align: center !important; }
        .num-val { text-align: right !important; font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 10px; }

        .item-alias { font-weight: 800; font-size: 11px; color: #1e40af; display: block; font-family: 'JetBrains Mono', monospace; }
        .item-spec { font-size: 8.5px; font-weight: 700; color: var(--dark); }
        .item-dim { font-size: 7.5px; color: var(--muted); display: block; font-family: 'JetBrains Mono', monospace; margin-top: 1px; }

        .col-final-stock { 
            background: #e2e8f0 !important; 
            font-weight: 800; 
            font-size: 11px; 
            color: #0f172a; 
            border-left: 2px solid var(--border-thick) !important; 
        }

        .val-in-s { color: var(--success-green); }
        .val-in-r { color: var(--return-indigo); }
        .val-out { color: var(--danger-red); }

        tr.grand-total {
            background: #e2e8f0 !important;
            font-weight: 900;
            border-top: 2px solid var(--border-thick);
            border-bottom: 2px solid var(--border-thick);
        }

        /* ✍️ SIGNATURE SECTION & STEMPEL */
        .doc-footer { 
            display: table; 
            width: 100%; 
            margin-top: 15px; 
            page-break-inside: avoid; 
        }
        .sig-col { 
            display: table-cell; 
            width: 33.33%; 
            text-align: center; 
            vertical-align: top; 
            padding: 0 25px; 
        }
        .sig-box { 
            border: 1px solid #cbd5e1; 
            background: #fff; 
            border-radius: 6px; 
            padding: 8px; 
            position: relative; 
            height: 95px;
        }
        .sig-title { font-size: 8.5px; font-weight: 800; text-transform: uppercase; color: var(--muted); margin-bottom: 45px; }
        .sig-name { font-size: 9.5px; font-weight: 800; text-transform: uppercase; border-top: 1px solid var(--border-thick); padding-top: 3px; }

        /* 🏆 THE LOGISTICS REAL STAMP */
        .stamp-wrapper {
            position: absolute;
            top: 12px;
            left: 50%;
            transform: translateX(-50%) rotate(-12deg);
            width: 82px;
            height: 82px;
            border: 2.5px dashed var(--stamp-blue);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--stamp-blue);
            opacity: 0.85;
            pointer-events: none;
            background: rgba(255, 255, 255, 0.45);
            text-align: center;
        }
        .stamp-top { font-size: 5.5px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; }
        .stamp-mid { font-size: 8px; font-weight: 900; border-top: 1px solid var(--stamp-blue); border-bottom: 1px solid var(--stamp-blue); padding: 1.5px 0; margin: 2px 0; width: 85%; }
        .stamp-bot { font-size: 5.5px; font-weight: 800; text-transform: uppercase; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>

    {{-- Screen Action Toolbar --}}
    <div class="no-print no-print-bar">
        <div>
            <b>INVENTORY AUDIT PREVIEW</b> — Lembar Rekapitulasi Fisik Raw Material
        </div>
        <div>
            <button class="btn-screen btn-close" onclick="window.close()">TUTUP</button>
            <button class="btn-screen btn-print" onclick="window.print()">🖨️ CETAK DOKUMEN (A4)</button>
        </div>
    </div>

    @php
        // Akumulasi Total Keseluruhan
        $sumAwal = 0; $sumInS = 0; $sumInR = 0; $sumOut = 0; $sumAkhir = 0;
        foreach($data as $d) {
            $sumAwal += ($d->stok_awal ?? 0);
            $sumInS += ($d->daily_in_s ?? 0);
            $sumInR += ($d->daily_in_r ?? 0);
            $sumOut += ($d->daily_out ?? 0);
            $sumAkhir += ($d->stok_akhir_hari_ini ?? 0);
        }
    @endphp

    <div class="report-wrapper">
        {{-- Header Dokumen Resmi --}}
        <div class="doc-header">
            <div class="header-col company-identity">
                <h2>PT ASALTA MANDIRI AGUNG</h2>
                <p>Plant Karawang // Warehouse & Logistics Department</p>
            </div>
            <div class="header-col report-title-box">
                <h1>RAW MATERIAL RECAP REGISTRY</h1>
                <span>DATE: {{ date('d F Y', strtotime($targetDate ?? now())) }}</span>
            </div>
            <div class="header-col doc-meta">
                FORM NO : <b>AMA-LOG-REC-01</b><br>
                CLIENT SCOPE : <b>{{ strtoupper($customer ?? 'ALL CLIENT ENTITIES') }}</b><br>
                PRINTED AT : {{ date('d/m/Y H:i') }}
            </div>
        </div>

        {{-- KPI Ribbon --}}
        <div class="kpi-bar">
            <div class="kpi-card">
                <div class="kpi-title">Beginning Balance (Awal)</div>
                <div class="kpi-value">{{ number_format($sumAwal) }} <span style="font-size:8px;">PCS</span></div>
            </div>
            <div class="kpi-card" style="border-left: 3px solid var(--success-green);">
                <div class="kpi-title" style="color: var(--success-green);">Total Inbound (Supplier)</div>
                <div class="kpi-value text-success">+{{ number_format($sumInS) }}</div>
            </div>
            <div class="kpi-card" style="border-left: 3px solid var(--return-indigo);">
                <div class="kpi-title" style="color: var(--return-indigo);">Total Return (Shop Floor)</div>
                <div class="kpi-value" style="color: var(--return-indigo);">+{{ number_format($sumInR) }}</div>
            </div>
            <div class="kpi-card" style="border-left: 3px solid var(--danger-red);">
                <div class="kpi-title" style="color: var(--danger-red);">Total Issued (Production)</div>
                <div class="kpi-value" style="color: var(--danger-red);">-{{ number_format($sumOut) }}</div>
            </div>
            <div class="kpi-card" style="border-left: 4px solid var(--border-thick); background:#e2e8f0;">
                <div class="kpi-title" style="color: var(--dark);">Ending Stock Balance</div>
                <div class="kpi-value">{{ number_format($sumAkhir) }} <span style="font-size:8px;">PCS</span></div>
            </div>
        </div>

        {{-- Tabel Rekapitulasi Utama --}}
        <table class="recap-table">
            <thead>
                <tr>
                    <th rowspan="2" width="28%">Material Identification & Spec</th>
                    <th colspan="4" width="38%">Daily Movement Ledger (PCS)</th>
                    <th colspan="3" width="22%" style="background: #e2e8f0 !important;">Monthly Accumulation</th>
                    <th rowspan="2" width="12%" class="col-final-stock">Ending Balance</th>
                </tr>
                <tr>
                    <th width="9.5%">Stok Awal</th>
                    <th width="9.5%">In (Supplier)</th>
                    <th width="9.5%">In (Return)</th>
                    <th width="9.5%">Out (Prod)</th>
                    <th width="7.3%" style="background: #e2e8f0 !important;">In (S)</th>
                    <th width="7.3%" style="background: #e2e8f0 !important;">In (R)</th>
                    <th width="7.4%" style="background: #e2e8f0 !important;">Out</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $rm)
                <tr>
                    <td class="text-left">
                        <span class="item-alias">{{ $rm->alias_code ?? 'UNREGISTERED' }}</span>
                        <span class="item-spec">{{ $rm->spec }}</span>
                        <span class="item-dim">DIM: {{ $rm->size }}</span>
                    </td>
                    
                    {{-- Nilai Mutasi Harian (Rata Kanan) --}}
                    <td class="num-val" style="color: var(--slate);">{{ number_format($rm->stok_awal) }}</td>
                    <td class="num-val val-in-s">{{ $rm->daily_in_s > 0 ? '+'.number_format($rm->daily_in_s) : '-' }}</td>
                    <td class="num-val val-in-r">{{ $rm->daily_in_r > 0 ? '+'.number_format($rm->daily_in_r) : '-' }}</td>
                    <td class="num-val val-out">{{ $rm->daily_out > 0 ? '-'.number_format($rm->daily_out) : '-' }}</td>
                    
                    {{-- Akumulasi Bulanan --}}
                    <td class="num-val" style="background: #f8fafc;">{{ number_format($rm->monthly_in_s ?? 0) }}</td>
                    <td class="num-val" style="background: #f8fafc;">{{ number_format($rm->monthly_in_r ?? 0) }}</td>
                    <td class="num-val" style="background: #f8fafc; color:#991b1b;">{{ number_format($rm->monthly_out ?? 0) }}</td>
                    
                    {{-- Saldo Akhir --}}
                    <td class="num-val col-final-stock">{{ number_format($rm->stok_akhir_hari_ini) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted italic">-- Tidak ada transaksi mutasi raw material pada periode ini --</td>
                </tr>
                @endforelse

                {{-- Baris Grand Total --}}
                @if(count($data) > 0)
                <tr class="grand-total">
                    <td class="text-left" style="padding-left: 10px; font-weight: 900; font-size: 10px;">GRAND TOTAL BALANCE :</td>
                    <td class="num-val">{{ number_format($sumAwal) }}</td>
                    <td class="num-val val-in-s">+{{ number_format($sumInS) }}</td>
                    <td class="num-val val-in-r">+{{ number_format($sumInR) }}</td>
                    <td class="num-val val-out">-{{ number_format($sumOut) }}</td>
                    <td colspan="3" style="background: #cbd5e1; text-align: center; font-size: 8px; font-weight: 800;">VERIFIED ACCUMULATION</td>
                    <td class="num-val col-final-stock" style="font-size: 12px; color: #1e3a8a;">{{ number_format($sumAkhir) }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        {{-- Lembar Otorisasi & Stempel --}}
        <div class="doc-footer">
            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Disiapkan Oleh (Warehouse)</div>
                    <div class="sig-name">Admin Gudang RM</div>
                </div>
            </div>

            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Divalidasi Oleh (PPIC)</div>
                    {{-- Stempel Validasi PPIC Department --}}
                    <div class="stamp-wrapper">
                        <span class="stamp-top">PT ASALTA MANDIRI AGUNG</span>
                        <span class="stamp-mid">LOGISTICS DEPT</span>
                        <span class="stamp-bot">KARAWANG PLANT</span>
                    </div>
                    <div class="sig-name">PPIC Supervisor</div>
                </div>
            </div>

            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Disetujui Oleh (Plant)</div>
                    <div class="sig-name">Plant Manager</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>