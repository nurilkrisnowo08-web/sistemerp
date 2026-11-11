<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print - Rekapan STO ({{ $sto->doc_no }})</title>
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
        
        .diff-plus { color: #166534; }
        .diff-minus { color: #b91c1c; }

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

    <div class="action-bar no-print">
        <div class="action-bar-title">📑 PREVIEW CETAK REKAPAN STO</div>
        <div class="action-btns">
            <button class="btn btn-out" onclick="window.close()">❌ KELUAR (OUT)</button>
            <button class="btn btn-print" onclick="window.print()">🖨️ PRINT REKAPAN</button>
        </div>
    </div>

    <div class="print-container">
        <!-- HEADER -->
        <div class="header-print">
            <table class="header-table">
                <tr>
                    <td width="15%" class="logo-container text-left">
                        <img src="{{ asset('assets/img/logo.png') }}" alt="ASALTA LOGO" onerror="this.outerHTML='<h1 style=\'margin:0;color:#1e293b;font-weight:900;\'>ASALTA</h1>'">
                    </td>
                    <td width="70%" class="company-title" style="text-align: center;">
                        <h2>LAPORAN REKONSILIASI STOCK OPNAME (STO)</h2>
                        <p>PT ASALTA MANDIRI AGUNG // BUKTI LAPORAN FISIK GUDANG</p>
                    </td>
                    <td width="15%" style="text-align: right;">
                        <div style="border: 1px solid #1e293b; padding: 5px; display:inline-block; font-size:9px; font-weight:bold; text-align:left;">
                            Doc No : {{ $sto->doc_no }}<br>
                            Rev : 01<br>
                            Status : {{ $sto->status }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <table class="info-table">
            <tr>
                <td class="info-label">Periode STO</td>
                <td class="info-sep">:</td>
                <td class="info-val">{{ $sto->period }}</td>
                
                <td class="info-label">Tanggal Eksekusi</td>
                <td class="info-sep">:</td>
                <td class="info-val">{{ date('d F Y, H:i', strtotime($sto->created_at)) }} WIB</td>
            </tr>
            <tr>
                <td class="info-label">Status Dokumen</td>
                <td class="info-sep">:</td>
                <td class="info-val">{{ $sto->status }}</td>
                
                <td class="info-label">Dicatat Oleh (PIC)</td>
                <td class="info-sep">:</td>
                <td class="info-val">{{ $sto->created_by }}</td>
            </tr>
        </table>

        <!-- TABEL DATA REKAPAN STO -->
        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" width="3%">NO</th>
                    <th rowspan="2" class="text-left" width="22%">PART NAME & NUMBER</th>
                    <th rowspan="2" width="5%">CUST</th>
                    <th colspan="3" class="col-sys">STOK SISTEM (SAAT ITU)</th>
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
                @forelse($details as $idx => $d)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="text-left">
                        <span class="fw-bold">{{ $d->part_name ?? 'Unknown Part' }}</span><br>
                        <span style="font-size:10px; color:#64748b;">{{ $d->part_no }}</span>
                    </td>
                    <td class="fw-bold">{{ $d->customer_code ?? '-' }}</td>
                    
                    <!-- Stok Sistem Waktu Itu -->
                    <td class="col-sys text-num" style="color:#64748b;">{{ number_format($d->rm_sys) }}</td>
                    <td class="col-sys text-num" style="color:#64748b;">{{ number_format($d->wip_sys) }}</td>
                    <td class="col-sys text-num" style="color:#64748b;">{{ number_format($d->fg_sys) }}</td>

                    <!-- Stok Fisik yang diisi user -->
                    <td class="col-act fw-bold text-num">{{ number_format($d->rm_act) }}</td>
                    <td class="col-act fw-bold text-num">{{ number_format($d->wip_act) }}</td>
                    <td class="col-act fw-bold text-num">{{ number_format($d->fg_act) }}</td>
                    
                    <!-- Selisih -->
                    <td class="fw-bold text-num {{ $d->rm_diff > 0 ? 'diff-plus' : ($d->rm_diff < 0 ? 'diff-minus' : '') }}">
                        {{ $d->rm_diff > 0 ? '+'.number_format($d->rm_diff) : number_format($d->rm_diff) }}
                    </td>
                    <td class="fw-bold text-num {{ $d->wip_diff > 0 ? 'diff-plus' : ($d->wip_diff < 0 ? 'diff-minus' : '') }}">
                        {{ $d->wip_diff > 0 ? '+'.number_format($d->wip_diff) : number_format($d->wip_diff) }}
                    </td>
                    <td class="fw-bold text-num {{ $d->fg_diff > 0 ? 'diff-plus' : ($d->fg_diff < 0 ? 'diff-minus' : '') }}">
                        {{ $d->fg_diff > 0 ? '+'.number_format($d->fg_diff) : number_format($d->fg_diff) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" style="padding: 30px; color:#64748b; font-style:italic;">Data rincian tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <table class="sign-table">
            <tr>
                <td>Dilaporkan Oleh (PIC Gudang),<br><br><br><br><br>{{ $sto->created_by }}</td>
                <td>Diperiksa Oleh (PPIC),<br><br><br><br><br>____________________</td>
                <td>Disetujui Oleh (Manajemen),<br><br><br><br><br>____________________</td>
            </tr>
        </table>
    </div>

</body>
</html>