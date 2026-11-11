<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print - Inventory Traceability Summary</title>
    <style>
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 11px; 
            margin: 0; 
            padding: 20px; 
            color: #1e293b;
            background-color: #f8fafc;
        }
        
        .print-container { background: #ffffff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); max-width: 1000px; margin: auto; }

        .action-bar { background: #1e293b; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .action-bar-title { color: #f8fafc; font-weight: 800; letter-spacing: 0.5px; font-size: 14px; }
        .action-btns { display: flex; gap: 10px; }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; font-weight: 800; font-size: 12px; cursor: pointer; transition: all 0.2s; }
        .btn-out { background: #ef4444; color: white; }
        .btn-print { background: #10b981; color: white; }

        .header-print { border-bottom: 3px solid #1e293b; padding-bottom: 15px; margin-bottom: 20px; }
        .header-table { width: 100%; border: none; }
        .header-table td { vertical-align: middle; border: none; }
        
        .logo-container img { max-height: 60px; object-fit: contain; }
        .company-title h2 { margin: 0 0 5px 0; font-size: 20px; font-weight: 900; letter-spacing: 1px; color: #0f172a; }
        .company-title p { margin: 0; font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 0.5px; }

        .info-table { width: 100%; margin-bottom: 15px; font-size: 11px; border-collapse: collapse; }
        .info-table td { padding: 4px 0; }
        .info-label { font-weight: 800; color: #475569; width: 15%; }
        .info-sep { width: 2%; font-weight: bold; }
        .info-val { font-weight: 700; color: #0f172a; width: 33%; }

        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #cbd5e1; padding: 8px; text-align: center; vertical-align: middle; }
        
        table.data-table th { background-color: #f1f5f9; font-weight: 900; text-transform: uppercase; font-size: 10px; color: #334155; }
        table.data-table td.text-left { text-align: left; }
        
        .col-rm { background-color: rgba(245, 158, 11, 0.05); }
        .col-wip { background-color: rgba(239, 68, 68, 0.05); }
        .col-fg { background-color: rgba(16, 185, 129, 0.05); }

        .fw-bold { font-weight: 900; }
        .text-num { font-family: 'Courier New', Courier, monospace; font-size: 13px; font-weight: bold; }

        @media print {
            @page { size: portrait; margin: 12mm; }
            body { padding: 0; margin: 0; background: #fff; }
            .print-container { box-shadow: none; padding: 0; max-width: 100%; border-radius: 0; }
            .no-print { display: none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <div class="action-bar-title">📑 PREVIEW SUMMARY REPORT</div>
        <div class="action-btns">
            <button class="btn btn-out" onclick="window.close()">❌ KELUAR</button>
            <button class="btn btn-print" onclick="window.print()">🖨️ PRINT SUMMARY</button>
        </div>
    </div>

    <div class="print-container">
        <div class="header-print">
            <table class="header-table">
                <tr>
                    <td width="15%" class="logo-container text-left">
                        <img src="{{ asset('assets/img/logo.png') }}" alt="ASALTA LOGO" onerror="this.outerHTML='<h1 style=\'margin:0;color:#1e293b;font-weight:900;\'>ASALTA</h1>'">
                    </td>
                    <td width="70%" class="company-title" style="text-align: center;">
                        <h2>INVENTORY TRACEABILITY SUMMARY</h2>
                        <p>PT ASALTA MANDIRI AGUNG // STOCK BALANCE REPORT</p>
                    </td>
                    <td width="15%" style="text-align: right;">
                        <div style="border: 1px solid #1e293b; padding: 5px; display:inline-block; font-size:9px; font-weight:bold; text-align:left;">
                            Doc No : FRM-PPIC-020<br>
                            Rev : 01<br>
                            Date : {{ date('d/m/Y') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <table class="info-table">
            <tr>
                <td class="info-label">Filter Customer</td>
                <td class="info-sep">:</td>
                <td class="info-val">{{ $selectedCustomer ? strtoupper($selectedCustomer) : 'ALL CUSTOMER' }}</td>
                
                <td class="info-label">Tanggal Cetak</td>
                <td class="info-sep">:</td>
                <td class="info-val">{{ date('d F Y, H:i') }} WIB</td>
            </tr>
            <tr>
                <td class="info-label">Filter Part Number</td>
                <td class="info-sep">:</td>
                <td class="info-val">{{ $selectedPart ? $selectedPart : 'ALL PARTS' }}</td>
                
                <td class="info-label">Dicetak Oleh</td>
                <td class="info-sep">:</td>
                <td class="info-val">{{ Auth::user()->name ?? 'Administrator PPIC' }}</td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">NO</th>
                    <th class="text-left" width="30%">PART NAME & NUMBER</th>
                    <th width="10%">CUST</th>
                    <th class="col-rm" width="15%">SISA RM (LBR)</th>
                    <th class="col-wip" width="20%">SISA WIP (PCS)</th>
                    <th class="col-fg" width="20%">SISA FG (PCS)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parts as $idx => $p)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="text-left">
                        <span class="fw-bold" style="font-size:12px;">{{ $p->part_name }}</span><br>
                        <span style="font-size:10px; color:#64748b;">{{ $p->part_no }}</span>
                    </td>
                    <td class="fw-bold">{{ $p->customer_code }}</td>
                    
                    <td class="col-rm fw-bold text-num" style="color:#d97706; font-size:14px;">{{ number_format($p->rm_stock) }}</td>
                    <td class="col-wip fw-bold text-num" style="color:#dc2626; font-size:14px;">{{ number_format($p->wip_stock ?? 0) }}</td>
                    <td class="col-fg fw-bold text-num" style="color:#059669; font-size:14px;">{{ number_format($p->fg_stock ?? 0) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 40px; font-size: 14px; color: #64748b;">
                        <em>⚠️ Tidak ada data part yang ditemukan.</em>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 40px; text-align: right; font-size: 10px; font-style: italic; color: #64748b;">
            *Summary Report dicetak dari Asalta Industrial Ecosystem Engine.<br>
            <strong>&copy; {{ date('Y') }} PT Asalta Mandiri Agung</strong>
        </div>
    </div>

</body>
</html>