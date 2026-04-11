<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RM_RECAP_{{ date('Ymd') }} rill</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary: #4361ee; 
            --dark: #0f172a; 
            --border-thick: #000000;
            --border-thin: #cbd5e1;
            --stamp-blue: #1e40af;
        }
        
        * { box-sizing: border-box; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            font-size: 10px; 
            color: var(--dark); 
            margin: 0; 
            padding: 40px; 
            background: #fff; 
        }

        .report-wrapper { max-width: 1300px; margin: 0 auto; position: relative; }

        /* ✨ DOCUMENT HEADER ✨ */
        .doc-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-end; 
            border-bottom: 5px solid var(--border-thick); 
            padding-bottom: 20px; 
            margin-bottom: 30px; 
        }
        .doc-header h2 { margin: 0; font-weight: 800; font-size: 26px; letter-spacing: -1.5px; text-transform: uppercase; }
        .doc-header h3 { margin: 5px 0 0 0; font-weight: 700; color: var(--primary); font-size: 15px; letter-spacing: 1px; }
        .meta-info { text-align: right; }
        .meta-info p { margin: 2px 0; font-weight: 700; font-size: 10px; text-transform: uppercase; color: #64748b; }
        .meta-info b { color: var(--dark); }

        /* ✨ INDUSTRIAL TABLE ✨ */
        table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 40px; border: 2px solid var(--border-thick); }
        th { 
            border: 1.5px solid var(--border-thick); 
            padding: 12px 5px; 
            text-transform: uppercase; 
            font-size: 9px; 
            font-weight: 800; 
            background: #f8fafc !important;
            -webkit-print-color-adjust: exact;
        }
        td { 
            border: 1px solid var(--border-thin); 
            padding: 10px 8px; 
            text-align: center; 
            font-family: 'JetBrains Mono', monospace; 
            font-size: 10px;
        }

        .text-left { text-align: left; padding-left: 15px; }
        .item-alias { font-weight: 800; font-size: 12.5px; color: var(--primary); display: block; margin-bottom: 2px; }
        .item-spec { font-size: 9px; color: #1e293b; font-weight: 700; display: block; }
        .item-dim { font-size: 8px; color: #64748b; display: block; margin-top: 1px; }

        /* Section Styling rill */
        .bg-monthly { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; }
        .bg-balance { background-color: #e2e8f0 !important; font-weight: 800; font-size: 13px; border-left: 2px solid var(--border-thick) !important; -webkit-print-color-adjust: exact; }
        
        .val-in { color: #059669; font-weight: 700; }
        .val-out { color: #dc2626; font-weight: 700; }
        .val-init { color: #475569; font-weight: 600; }

        /* ✨ SIGNATURE & STAMP ✨ */
        .doc-footer { display: flex; justify-content: space-between; margin-top: 60px; page-break-inside: avoid; }
        .sig-box { text-align: center; width: 230px; position: relative; }
        .sig-line { height: 90px; border-bottom: 2px solid var(--border-thick); margin-bottom: 12px; }
        .sig-box p { margin: 0; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        .sig-box small { color: #64748b; font-weight: 600; }

        /* 🏆 THE LOGISTICS STAMP rill */
        .official-stamp {
            width: 140px;
            height: 140px;
            border: 4px double var(--stamp-blue);
            border-radius: 50%;
            color: var(--stamp-blue);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1.1;
            transform: rotate(-15deg);
            opacity: 0.75;
            position: absolute;
            top: -25px;
            left: 50%;
            margin-left: -70px;
            pointer-events: none;
            background: transparent;
        }
        .stamp-inner-t { font-size: 8px; margin-bottom: 3px; }
        .stamp-inner-c { border-top: 2px solid var(--stamp-blue); border-bottom: 2px solid var(--stamp-blue); padding: 4px 0; width: 100%; font-size: 12px; margin: 2px 0; }
        .stamp-inner-b { font-size: 7.5px; margin-top: 3px; }

        @media print {
            @page { size: landscape; margin: 0.5cm; }
            body { padding: 20px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="report-wrapper">
        <div class="doc-header">
            <div>
                <h2>PT ASALTA MANDIRI AGUNG</h2>
                <h3>{{ $title }}</h3>
            </div>
            <div class="meta-info">
                <p>SCOPE: <b>{{ $customer ?? 'ALL CLIENT ENTITIES' }}</b></p>
                <p>DATE: <b>{{ date('d F Y', strtotime($targetDate)) }}</b></p>
                <p>REF_ID: <b>RM/RCP/{{ date('YmdHi') }} rill</b></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th rowspan="2" width="280">Material Identification</th>
                    <th colspan="4">Daily Movement (PCS)</th>
                    <th colspan="3" class="bg-monthly">Monthly Accumulation</th>
                    <th rowspan="2" class="bg-balance" width="120">Balance Stock</th>
                </tr>
                <tr>
                    <th width="90">Initial</th>
                    <th width="75">In (S)</th>
                    <th width="75">In (R)</th>
                    <th width="75">Out</th>
                    <th class="bg-monthly" width="90">Total In (S)</th>
                    <th class="bg-monthly" width="90">Total In (R)</th>
                    <th class="bg-monthly" width="90">Total Out</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $rm)
                <tr>
                    <td class="text-left">
                        <span class="item-alias">{{ $rm->alias_code ?? 'UNREGISTERED' }}</span>
                        <span class="item-spec">{{ $rm->spec }}</span>
                        <span class="item-dim">DIMENSION: {{ $rm->size }}</span>
                    </td>
                    
                    {{-- Daily Column rill --}}
                    <td class="val-init">{{ number_format($rm->stok_awal) }}</td>
                    <td class="val-in">+{{ number_format($rm->daily_in_s) }}</td>
                    <td class="val-in" style="color:#0ea5e9">+{{ number_format($rm->daily_in_r) }}</td>
                    <td class="val-out">-{{ number_format($rm->daily_out) }}</td>
                    
                    {{-- Monthly Column rill --}}
                    <td class="bg-monthly">{{ number_format($rm->monthly_in_s) }}</td>
                    <td class="bg-monthly">{{ number_format($rm->monthly_in_r) }}</td>
                    <td class="bg-monthly" style="color:#991b1b">{{ number_format($rm->monthly_out) }}</td>
                    
                    {{-- Final Result rill --}}
                    <td class="bg-balance">{{ number_format($rm->stok_akhir_hari_ini) }}</td>
                </tr>
                @empty
                <tr><td colspan="9" class="py-5 text-muted italic">-- No precision records found for this period --</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="doc-footer">
            <div class="sig-box">
                <p>Prepared by (Warehouse),</p>
                <div class="sig-line"></div>
                <p>Logistics Administrator</p>
                <small>MRP_SYSTEM_GENERATED</small>
            </div>
            
            <div class="sig-box">
                <p>Validated by (PPIC),</p>
                <div class="sig-line">
                    <div class="official-stamp">
                        <div class="stamp-inner-t">PT ASALTA MANDIRI AGUNG</div>
                        <div class="stamp-inner-c">LOGISTICS<br>DEPARTMENT</div>
                        <div class="stamp-inner-b">KARAWANG PLANT</div>
                    </div>
                </div>
                <p>PPIC Department</p>
                <small>Official Documentation</small>
            </div>

            <div class="sig-box">
                <p>Authorized by (Plant),</p>
                <div class="sig-line"></div>
                <p>Plant Manager</p>
                <small>Management Approval</small>
            </div>
        </div>

        <div style="margin-top: 50px; text-align: center;" class="no-print">
            <p style="color: #64748b; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                End of Inventory Recap Registry - PT. Asalta Mandiri Agung rill.
            </p>
        </div>
    </div>

</body>
</html>