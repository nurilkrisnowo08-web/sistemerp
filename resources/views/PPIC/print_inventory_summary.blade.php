<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print - Inventory Traceability Summary</title>
    <style>
        /* ✨ FONT & BASIC SETUP ✨ */
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 11px; 
            margin: 0; 
            padding: 20px; 
            color: #1e293b;
            background-color: #f8fafc;
        }
        
        .print-container {
            background: #ffffff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            max-width: 1400px;
            margin: auto;
        }

        /* ✨ FLOATING ACTION BAR (NO-PRINT) ✨ */
        .action-bar {
            background: #1e293b;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .action-bar-title { color: #f8fafc; font-weight: 800; letter-spacing: 0.5px; font-size: 14px; }
        .action-btns { display: flex; gap: 10px; }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; font-weight: 800; font-size: 12px; cursor: pointer; transition: all 0.2s; }
        .btn-out { background: #ef4444; color: white; }
        .btn-out:hover { background: #dc2626; }
        .btn-print { background: #10b981; color: white; }
        .btn-print:hover { background: #059669; }

        /* ✨ HEADER & LOGO ✨ */
        .header-print {
            border-bottom: 3px solid #1e293b;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-table { width: 100%; border: none; }
        .header-table td { vertical-align: middle; border: none; }
        
        .logo-container img { max-height: 60px; object-fit: contain; }
        .company-title h2 { margin: 0 0 5px 0; font-size: 20px; font-weight: 900; letter-spacing: 1px; color: #0f172a; }
        .company-title p { margin: 0; font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 0.5px; text-transform: uppercase; }

        /* ✨ INFO FILTER ✨ */
        .info-table { width: 100%; margin-bottom: 15px; font-size: 11px; border-collapse: collapse; }
        .info-table td { padding: 4px 0; }
        .info-label { font-weight: 800; color: #475569; width: 12%; }
        .info-sep { width: 2%; font-weight: bold; }
        .info-val { font-weight: 700; color: #0f172a; width: 36%; }

        /* ✨ MAIN TABLE ✨ */
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #cbd5e1; padding: 7px; text-align: center; vertical-align: middle; }
        
        table.data-table th { background-color: #f1f5f9; font-weight: 900; text-transform: uppercase; font-size: 10px; color: #334155; border-bottom: 2px solid #94a3b8; }
        table.data-table td.text-left { text-align: left; }
        
        .col-rm { background-color: rgba(245, 158, 11, 0.05); }
        .col-wip { background-color: rgba(239, 68, 68, 0.05); }
        .col-fg { background-color: rgba(16, 185, 129, 0.05); }

        .fw-bold { font-weight: 900; }
        .text-num { font-family: 'Courier New', Courier, monospace; font-size: 12px; font-weight: bold; }

        .text-rm-stock { color: #d97706; }
        .text-wip-stock { color: #dc2626; }
        .text-fg-stock { color: #059669; }

        /* ✨ PRINT MEDIA QUERIES ✨ */
        @media print {
            @page { size: landscape; margin: 12mm; }
            body { padding: 0; margin: 0; background: #fff; }
            .print-container { box-shadow: none; padding: 0; max-width: 100%; border-radius: 0; }
            .no-print { display: none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

    <!-- ✨ ACTION BAR (NO-PRINT) ✨ -->
    <div class="action-bar no-print">
        <div class="action-bar-title">📑 PREVIEW SUMMARY REPORT</div>
        <div class="action-btns">
            <!-- Tombol Keluar / Out -->
            <button class="btn btn-out" onclick="window.close()">❌ KELUAR (OUT)</button>
            <!-- Tombol Print -->
            <button class="btn btn-print" onclick="window.print()">🖨️ PRINT SUMMARY</button>
        </div>
    </div>

    <div class="print-container">
        <!-- ✨ KOP SURAT / HEADER ✨ -->
        <div class="header-print">
            <table class="header-table">
                <tr>
                    <td width="15%" class="logo-container text-left">
                        <img src="{{ asset('assets/img/logo.png') }}" alt="ASALTA LOGO" onerror="this.outerHTML='<h1 style=\'margin:0;color:#1e293b;font-weight:900;\'>ASALTA</h1>'">
                    </td>
                    <td width="70%" class="company-title" style="text-align: center;">
                        <h2>MATERIAL & INVENTORY TRACEABILITY REPORT</h2>
                        <p>PT ASALTA MANDIRI AGUNG // INDUSTRIAL</p>
                    </td>
                    <td width="15%" style="text-align: right;">
                        <div style="border: 1px solid #1e293b; padding: 5px; display:inline-block; font-size:9px; font-weight:bold; text-align:left;">
                            Doc No : FRM-PPIC-0     <br>
                            Rev : <br>
                            Date : {{ date('d/m/Y') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- ✨ INFO FILTER ✨ -->
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
                <td class="info-val">{{ Auth::user()->name ?? 'PT.ASALTA MANDIRI AGUNG' }}</td>
            </tr>
        </table>

        <!-- ✨ TABEL SUMMARY UTAMA ✨ -->
        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" width="3%">NO</th>
                    <th rowspan="2" class="text-left" width="18%">PART NAME & NUMBER</th>
                    <th rowspan="2" width="6%">CUST</th>
                    <th colspan="4" class="col-rm">RAW MATERIAL (RM) <span style="font-weight:normal;font-size:8px;">[LBR]</span></th>
                    <th colspan="3" class="col-wip">WIP (WELDING) <span style="font-weight:normal;font-size:8px;">[PCS]</span></th>
                    <th colspan="3" class="col-fg">FINISH GOOD (FG) <span style="font-weight:normal;font-size:8px;">[PCS]</span></th>
                </tr>
                <tr>
                    <!-- RM Sub -->
                    <th class="col-rm">IN SUP</th>
                    <th class="col-rm">IN RET</th>
                    <th class="col-rm">OUT PROD</th>
                    <th class="col-rm">STOCK</th>
                    
                    <!-- WIP Sub -->
                    <th class="col-wip">IN (PROD)</th>
                    <th class="col-wip">OUT (LAS)</th>
                    <th class="col-wip">STOCK</th>
                    
                    <!-- FG Sub -->
                    <th class="col-fg">IN (PROD)</th>
                    <th class="col-fg">OUT (SJ)</th>
                    <th class="col-fg">STOCK</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parts as $idx => $p)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="text-left">
                        <span class="fw-bold">{{ $p->part_name }}</span><br>
                        <span style="font-size:10px; color:#64748b;">{{ $p->part_no }}</span>
                    </td>
                    <td class="fw-bold">{{ $p->customer_code }}</td>
                    
                    <!-- RM Data -->
                    <td class="col-rm text-num">{{ number_format($p->rm_in) }}</td>
                    <td class="col-rm text-num">{{ number_format($p->rm_ret) }}</td>
                    <td class="col-rm text-num">{{ number_format($p->rm_out) }}</td>
                    <td class="col-rm fw-bold text-num text-rm-stock">{{ number_format($p->rm_stock) }}</td>

                    <!-- WIP Data -->
                    <td class="col-wip text-num">{{ number_format($p->wip_in) }}</td>
                    <td class="col-wip text-num">{{ number_format($p->wip_out) }}</td>
                    <td class="col-wip fw-bold text-num text-wip-stock">{{ number_format($p->wip_stock ?? 0) }}</td>

                    <!-- FG Data -->
                    <td class="col-fg text-num">{{ number_format($p->fg_in) }}</td>
                    <td class="col-fg text-num">{{ number_format($p->fg_out) }}</td>
                    <td class="col-fg fw-bold text-num text-fg-stock">{{ number_format($p->fg_stock ?? 0) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" style="padding: 40px; font-size: 14px; color: #64748b;">
                        <em>⚠️ Tidak ada data part yang ditemukan untuk kriteria filter ini.</em>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 40px; text-align: right; font-size: 10px; font-style: italic; color: #64748b;">
            *Dokumen dicetak dari Asalta MRP System. Data real-time sesuai kondisi saat tombol Print ditekan.<br>
        </div>
    </div>

</body>
</html>