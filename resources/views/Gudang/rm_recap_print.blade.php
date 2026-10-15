<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>REKAP_RM_{{ date('Ymd', strtotime($targetDate ?? now())) }}</title>
    <!-- Font Standar Industri -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --dark: #0f172a; 
            --slate: #475569;
            --border-dark: #0f172a;
            --border-light: #cbd5e1;
            --stamp-blue: #1d4ed8;
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
            padding: 10px; 
            background: #fff; 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact; 
        }

        /* 🔘 ACTION BAR (Hanya di layar) */
        .no-print-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f1f5f9;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #cbd5e1;
        }
        .btn {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 11px;
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }
        .btn-back { background: #64748b; color: #fff; }
        .btn-print { background: #2563eb; color: #fff; }

        /* 🏢 HEADER FORM PABRIK */
        .doc-header { 
            display: table; 
            width: 100%; 
            border-bottom: 3px double var(--border-dark); 
            padding-bottom: 10px; 
            margin-bottom: 12px; 
        }
        .header-cell { display: table-cell; vertical-align: middle; }
        
        .company-title { width: 35%; }
        .company-title h2 { margin: 0; font-size: 18px; font-weight: 900; letter-spacing: 0.5px; }
        .company-title p { margin: 2px 0 0; font-size: 8.5px; color: var(--slate); font-weight: 600; text-transform: uppercase; }

        .report-title { width: 40%; text-align: center; }
        .report-title h1 { margin: 0; font-size: 15px; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; }
        .report-title span { font-size: 10px; font-weight: 700; color: #2563eb; }

        .meta-box { width: 25%; text-align: right; font-size: 8.5px; line-height: 1.4; font-weight: 600; }
        .meta-box b { color: var(--dark); font-weight: 800; }

        /* 📊 KPI SUMMARY BAR */
        .summary-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 12px;
        }
        .kpi-card {
            flex: 1;
            border: 1px solid var(--border-dark);
            padding: 6px 10px;
            background: #f8fafc;
            border-radius: 4px;
        }
        .kpi-label { font-size: 7.5px; text-transform: uppercase; font-weight: 800; color: var(--slate); }
        .kpi-value { font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: 800; color: var(--dark); margin-top: 2px; }

        /* 📋 INDUSTRIAL TABLE */
        table.recap-table { 
            width: 100%; 
            border-collapse: collapse; 
            border: 1.5px solid var(--border-dark); 
            margin-bottom: 15px;
        }
        table.recap-table th { 
            border: 1px solid var(--border-dark); 
            padding: 6px 4px; 
            font-size: 8px; 
            font-weight: 800; 
            text-transform: uppercase; 
            background: #f1f5f9 !important; 
            text-align: center;
        }
        table.recap-table td { 
            border: 1px solid #94a3b8; 
            padding: 5px 6px; 
            vertical-align: middle; 
            font-size: 9px;
        }

        .text-left { text-align: left !important; }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; font-family: 'JetBrains Mono', monospace; font-weight: 700; }

        .item-alias { font-weight: 800; font-size: 11px; color: #1e40af; display: block; }
        .item-spec { font-size: 8.5px; font-weight: 700; color: var(--dark); }
        .item-dim { font-size: 7.5px; color: var(--slate); display: block; font-family: 'JetBrains Mono', monospace; }

        .col-balance { 
            background: #e2e8f0 !important; 
            font-weight: 800; 
            font-size: 10.5px; 
            color: #0f172a; 
            border-left: 2px solid var(--border-dark) !important; 
        }

        .val-in-s { color: #047857; }
        .val-in-r { color: #0284c7; }
        .val-out { color: #b91c1c; }

        tr.grand-total {
            background: #e2e8f0 !important;
            font-weight: 900;
            border-top: 2px solid var(--border-dark);
            border-bottom: 2px solid var(--border-dark);
        }

        /* ✍️ TANDA TANGAN & STEMPEL */
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
            padding: 0 20px; 
        }
        .sig-box { 
            border: 1px solid #cbd5e1; 
            background: #fff; 
            border-radius: 6px; 
            padding: 8px; 
            position: relative; 
            height: 95px;
        }
        .sig-title { font-size: 8.5px; font-weight: 800; text-transform: uppercase; color: var(--slate); margin-bottom: 45px; }
        .sig-name { font-size: 9.5px; font-weight: 800; text-transform: uppercase; border-top: 1px solid var(--border-dark); padding-top: 3px; }

        /* 🏆 REALISTIC STAMP LOGISTICS */
        .stamp-wrapper {
            position: absolute;
            top: 15px;
            left: 50%;
            transform: translateX(-50%) rotate(-12deg);
            width: 80px;
            height: 80px;
            border: 2.5px dashed var(--stamp-blue);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--stamp-blue);
            opacity: 0.85;
            pointer-events: none;
            background: rgba(255, 255, 255, 0.4);
        }
        .stamp-top { font-size: 5.5px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; }
        .stamp-mid { font-size: 8px; font-weight: 900; border-top: 1px solid var(--stamp-blue); border-bottom: 1px solid var(--stamp-blue); padding: 1px 0; margin: 2px 0; width: 85%; }
        .stamp-bot { font-size: 5.5px; font-weight: 800; text-transform: uppercase; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>

    {{-- Top Action Bar --}}
    <div class="no-print no-print-bar">
        <div>
            <b>INVENTORY AUDIT PREVIEW</b> — Lembar Rekapitulasi Fisik Raw Material
        </div>
        <div>
            <button class="btn btn-back" onclick="window.close()">TUTUP</button>
            <button class="btn btn-print" onclick="window.print()">🖨️ CETAK DOKUMEN (A4)</button>
        </div>
    </div>

    @php
        // Perhitungan Total Akumulasi
        $sumAwal = 0; $sumInS = 0; $sumInR = 0; $sumOut = 0; $sumAkhir = 0;
        foreach($data as $d) {
            $sumAwal += $d->stok_awal;
            $sumInS += $d->daily_in_s;
            $sumInR += $d->daily_in_r;
            $sumOut += $d->daily_out;
            $sumAkhir += $d->stok_akhir_hari_ini;
        }
    @endphp

    {{-- Header Dokumen --}}
    <div class="doc-header">
        <div class="header-cell company-title">
            <h2>PT ASALTA MANDIRI AGUNG</h2>
            <p>Plant Karawang // Warehouse & Logistics Department</p>
        </div>
        <div class="header-cell report-title">
            <h1>RAW MATERIAL RECAP REGISTRY</h1>
            <span>PERIODE : {{ date('d F Y', strtotime($targetDate ?? now())) }}</span>
        </div>
        <div class="header-cell meta-box">
            DOKUMEN : <b>AMA-LOG-REC-01</b><br>
            CLIENT ENTITY : <b>{{ strtoupper($customer ?? 'ALL CUSTOMERS') }}</b><br>
            PRINT TIMESTAMP : {{ date('d/m/Y H:i:s') }}
        </div>
    </div>

    {{-- Summary Cards Ribbon --}}
    <div class="summary-bar">
        <div class="kpi-card">
            <div class="kpi-label">Beginning Balance (Awal)</div>
            <div class="kpi-value">{{ number_format($sumAwal) }} <span style="font-size:8px;">PCS</span></div>
        </div>
        <div class="kpi-card" style="border-left: 3px solid #047857;">
            <div class="kpi-label" style="color: #047857;">Total Inbound (Supplier)</div>
            <div class="kpi-value text-success">+{{ number_format($sumInS) }}</div>
        </div>
        <div class="kpi-card" style="border-left: 3px solid #0284c7;">
            <div class="kpi-label" style="color: #0284c7;">Total Return (Shop Floor)</div>
            <div class="kpi-value" style="color: #0284c7;">+{{ number_format($sumInR) }}</div>
        </div>
        <div class="kpi-card" style="border-left: 3px solid #b91c1c;">
            <div class="kpi-label" style="color: #b91c1c;">Total Issued (Production)</div>
            <div class="kpi-value text-danger">-{{ number_format($sumOut) }}</div>
        </div>
        <div class="kpi-card" style="border-left: 4px solid var(--border-dark); background:#e2e8f0;">
            <div class="kpi-label" style="color: var(--dark);">Total Ending Balance</div>
            <div class="kpi-value">{{ number_format($sumAkhir) }} <span style="font-size:8px;">PCS</span></div>
        </div>
    </div>

    {{-- Tabel Rekapitulasi Utama --}}
    <table class="recap-table">
        <thead>
            <tr>
                <th rowspan="2" width="260">Material Identification & Spec</th>
                <th colspan="4" width="280">Daily Movement Ledger (PCS)</th>
                <th colspan="3" width="240" style="background: #e2e8f0 !important;">Monthly Accumulation</th>
                <th rowspan="2" width="95" class="col-balance">Ending Balance</th>
            </tr>
            <tr>
                <th width="70">Stok Awal</th>
                <th width="70">In (Supplier)</th>
                <th width="70">In (Return)</th>
                <th width="70">Out (Prod)</th>
                <th width="80" style="background: #e2e8f0 !important;">Total In (S)</th>
                <th width="80" style="background: #e2e8f0 !important;">Total In (R)</th>
                <th width="80" style="background: #e2e8f0 !important;">Total Out</th>
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
                
                {{-- Daily Values (Rata Kanan) --}}
                <td class="text-right" style="color: var(--slate);">{{ number_format($rm->stok_awal) }}</td>
                <td class="text-right val-in-s">{{ $rm->daily_in_s > 0 ? '+'.number_format($rm->daily_in_s) : '-' }}</td>
                <td class="text-right val-in-r">{{ $rm->daily_in_r > 0 ? '+'.number_format($rm->daily_in_r) : '-' }}</td>
                <td class="text-right val-out">{{ $rm->daily_out > 0 ? '-'.number_format($rm->daily_out) : '-' }}</td>
                
                {{-- Monthly Values --}}
                <td class="text-right" style="background: #f8fafc;">{{ number_format($rm->monthly_in_s ?? 0) }}</td>
                <td class="text-right" style="background: #f8fafc;">{{ number_format($rm->monthly_in_r ?? 0) }}</td>
                <td class="text-right" style="background: #f8fafc; color:#991b1b;">{{ number_format($rm->monthly_out ?? 0) }}</td>
                
                {{-- Final Balance --}}
                <td class="text-right col-balance">{{ number_format($rm->stok_akhir_hari_ini) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-4 text-muted italic">-- Tidak ada mutasi raw material pada periode ini --</td>
            </tr>
            @endforelse

            {{-- Baris Grand Total --}}
            @if(count($data) > 0)
            <tr class="grand-total">
                <td class="text-left" style="padding-left: 10px; font-size: 10px;">GRAND TOTAL BALANCE :</td>
                <td class="text-right">{{ number_format($sumAwal) }}</td>
                <td class="text-right val-in-s">+{{ number_format($sumInS) }}</td>
                <td class="text-right val-in-r">+{{ number_format($sumInR) }}</td>
                <td class="text-right val-out">-{{ number_format($sumOut) }}</td>
                <td colspan="3" style="background: #cbd5e1; text-align: center; font-size: 8px;">VERIFIED SYSTEM</td>
                <td class="text-right col-balance" style="font-size: 12px; color: #1e3a8a;">{{ number_format($sumAkhir) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Lembar Pengesahan Tanda Tangan --}}
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
                {{-- Stempel Pas Nempel di Tanda Tangan PPIC --}}
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
                <div class="sig-title">Disetujui Oleh (Factory)</div>
                <div class="sig-name">Plant Manager</div>
            </div>
        </div>
    </div>

</body>
</html>