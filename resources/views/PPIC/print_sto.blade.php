<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print - Form Stock Opname (STO)</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; color: #1e293b; background-color: #f8fafc; }
        
        .print-container { background: #ffffff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); max-width: 1400px; margin: auto; }

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
        .company-title p { margin: 0; font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 0.5px; text-transform: uppercase; }

        .info-table { width: 100%; margin-bottom: 15px; font-size: 11px; border-collapse: collapse; }
        .info-table td { padding: 4px 0; }
        .info-label { font-weight: 800; color: #475569; width: 12%; }
        .info-sep { width: 2%; font-weight: bold; }
        .info-val { font-weight: 700; color: #0f172a; width: 36%; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.data-table th, table.data-table td { border: 1px solid #cbd5e1; padding: 7px; text-align: center; vertical-align: middle; }
        
        table.data-table th { background-color: #f1f5f9; font-weight: 900; text-transform: uppercase; font-size: 10px; color: #334155; border-bottom: 2px solid #94a3b8; }
        table.data-table td.text-left { text-align: left; }
        
        .col-sys { background-color: #f8fafc; }
        .col-act { background-color: #f0fdf4; }

        .fw-bold { font-weight: 900; }
        .text-num { font-family: 'Courier New', Courier, monospace; font-size: 12px; font-weight: bold; }
        .blank-box { border: 1px dashed #94a3b8; width: 80%; height: 20px; display: inline-block; margin: auto; }

        .history-section { border-top: 2px solid #e2e8f0; padding-top: 20px; }
        .history-title { font-weight: 900; font-size: 13px; color: #0f172a; margin-bottom: 10px; text-transform: uppercase; }
        
        .sign-table { width: 100%; margin-top: 40px; text-align: center; font-size: 11px; }
        .sign-table td { width: 33%; padding-bottom: 60px; font-weight: bold; }

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
        <div class="action-bar-title">📑 PREVIEW BLANKO STOCK OPNAME (STO)</div>
        <div class="action-btns">
            <button class="btn btn-out" onclick="window.close()">❌ KELUAR (OUT)</button>
            <button class="btn btn-print" onclick="window.print()">🖨️ PRINT BLANKO</button>
        </div>
    </div>

    <div class="print-container">
        <!-- ✨ KOP SURAT / HEADER ✨ -->
        <div class="header-print">
            <table class="header-table">
                <tr>
                    <td width="15%" class="logo-container text-left">
                        <img src="{{ asset('public/admin/img/Logo-asalta.png') }}" alt="ASALTA LOGO" onerror="this.outerHTML='<h1 style=\'margin:0;color:#1e293b;font-weight:900;\'>ASALTA MANDIRI</h1>'">
                    </td>
                    <td width="70%" class="company-title" style="text-align: center;">
                        <h2>MATERIAL & INVENTORY TRACEABILITY REPORT</h2>
                        <p>PT ASALTA MANDIRI AGUNG // INDUSTRIAL</p>
                    </td>
                    <td width="15%" style="text-align: right;">
                        <div style="border: 1px solid #1e293b; padding: 5px; display:inline-block; font-size:9px; font-weight:bold; text-align:left;">
                            Doc No : FRM-PPIC-STO-001<br>
                            Rev : 0<br>
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
                <td class="info-label">Pencarian Part</td>
                <td class="info-sep">:</td>
                <td class="info-val">{{ $search ? $search : 'ALL PARTS' }}</td>
                
                <td class="info-label">Dicetak Oleh</td>
                <td class="info-sep">:</td>
                <td class="info-val">{{ Auth::user()->name ?? 'Administrator PPIC' }}</td>
            </tr>
        </table>

        <!-- ✨ TABEL BLANKO STO ✨ -->
        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" width="3%">NO</th>
                    <th rowspan="2" class="text-left" width="22%">PART NAME & NUMBER</th>
                    <th rowspan="2" width="5%">CUST</th>
                    <th colspan="3" class="col-sys">STOK SISTEM (DATABASE)</th>
                    <th colspan="3" class="col-act">STOK FISIK (AKTUAL LAPANGAN)</th>
                    <th colspan="3">SELISIH (+/-)</th>
                </tr>
                <tr>
                    <th class="col-sys">RM (Lbr)</th>
                    <th class="col-sys">WIP (Pcs)</th>
                    <th class="col-sys">FG (Pcs)</th>
                    
                    <th class="col-act">RM Aktual</th>
                    <th class="col-act">WIP Aktual</th>
                    <th class="col-act">FG Aktual</th>
                    
                    <th>RM Diff</th>
                    <th>WIP Diff</th>
                    <th>FG Diff</th>
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
                    
                    <!-- Stok Sistem (Pre-populated) -->
                    <td class="col-sys text-num" style="color:#d97706;">{{ number_format($p->rm_stock) }}</td>
                    <td class="col-sys text-num" style="color:#dc2626;">{{ number_format($p->wip_stock ?? 0) }}</td>
                    <td class="col-sys text-num" style="color:#059669;">{{ number_format($p->fg_stock ?? 0) }}</td>

                    <!-- Kolom Kosong untuk ditulis Manual saat Opname -->
                    <td class="col-act"><span class="blank-box"></span></td>
                    <td class="col-act"><span class="blank-box"></span></td>
                    <td class="col-act"><span class="blank-box"></span></td>
                    
                    <!-- Kolom Kosong untuk Selisih -->
                    <td><span class="blank-box"></span></td>
                    <td><span class="blank-box"></span></td>
                    <td><span class="blank-box"></span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" style="padding: 30px; color:#64748b; font-style:italic;">Data part tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- ✨ HISTORY FORM STO PER BULAN ✨ -->
        <div class="history-section">
            <div class="history-title">DAFTAR RIWAYAT PELAKSANAAN STO (BULAN {{ strtoupper(date('F Y')) }})</div>
            <table class="data-table" style="width: 60%; margin-top: 0;">
                <thead>
                    <tr>
                        <th width="20%">TANGGAL STO</th>
                        <th width="30%">NO. DOKUMEN</th>
                        <th width="30%">PETUGAS / PIC</th>
                        <th width="20%">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historySto ?? [] as $h)
                    <tr>
                        <td class="text-num">{{ date('d M Y', strtotime($h['tanggal'])) }}</td>
                        <td class="fw-bold">{{ $h['doc_no'] }}</td>
                        <td>{{ $h['petugas'] }}</td>
                        <td style="color: #059669; font-weight: bold;">{{ $h['status'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="color:#64748b; font-style:italic;">Belum ada pelaksanaan STO di bulan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <table class="sign-table">
            <tr>
                <td>Dibuat Oleh (PIC Gudang),<br><br><br><br><br>____________________</td>
                <td>Diperiksa Oleh (PPIC),<br><br><br><br><br>____________________</td>
                <td>Disetujui Oleh (Manajemen),<br><br><br><br><br>____________________</td>
            </tr>
        </table>
    </div>

</body>
</html>