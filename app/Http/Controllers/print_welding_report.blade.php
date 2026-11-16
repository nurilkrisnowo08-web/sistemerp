<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print - Daily Welding Production Report</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; color: #1e293b; background-color: #f8fafc; }
        .print-container { background: #ffffff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); max-width: 1200px; margin: auto; }
        .action-bar { background: #1e293b; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; border-radius: 8px; margin-bottom: 20px; }
        .action-bar-title { color: #f8fafc; font-weight: 800; font-size: 14px; }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; font-weight: 800; font-size: 12px; cursor: pointer; }
        .btn-out { background: #ef4444; color: white; }
        .btn-print { background: #10b981; color: white; }
        .header-print { border-bottom: 3px solid #1e293b; padding-bottom: 15px; margin-bottom: 20px; }
        .header-table { width: 100%; border: none; }
        .company-title h2 { margin: 0 0 5px 0; font-size: 20px; font-weight: 900; color: #0f172a; text-align: center; }
        .company-title p { margin: 0; font-size: 11px; font-weight: 700; color: #64748b; text-align: center; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th, table.data-table td { border: 1px solid #cbd5e1; padding: 8px; text-align: center; vertical-align: middle; }
        table.data-table th { background-color: #f1f5f9; font-weight: 900; text-transform: uppercase; font-size: 10px; color: #334155; }
        .text-left { text-align: left !important; }
        .text-num { font-family: 'Courier New', Courier, monospace; font-size: 13px; font-weight: bold; }
        .row-summary { background-color: #e2e8f0; font-weight: 900; }
        .status-ok { color: #059669; }
        .status-ng { color: #dc2626; }
        .sign-table { width: 100%; margin-top: 40px; text-align: center; font-size: 11px; }
        .sign-table td { width: 33%; padding-bottom: 60px; font-weight: bold; }
        @media print {
            @page { size: landscape; margin: 12mm; }
            body { padding: 0; background: #fff; }
            .print-container { box-shadow: none; padding: 0; max-width: 100%; }
            .no-print { display: none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>
    <div class="action-bar no-print">
        <div class="action-bar-title">📑 PREVIEW WELDING DAILY REPORT</div>
        <div style="display:flex; gap:10px;">
            <button class="btn btn-out" onclick="window.close()">❌ KELUAR</button>
            <button class="btn btn-print" onclick="window.print()">🖨️ PRINT REPORT</button>
        </div>
    </div>
    <div class="print-container">
        <div class="header-print">
            <table class="header-table">
                <tr>
                    <td width="15%"><img src="{{ asset('assets/img/logo.png') }}" alt="LOGO" style="max-height: 50px;"></td>
                    <td width="70%" class="company-title">
                        <h2>DAILY WELDING PRODUCTION REPORT</h2>
                        <p>PT ASALTA MANDIRI AGUNG // REKAPITULASI HASIL LAS HARIAN</p>
                    </td>
                    <td width="15%" style="text-align: right; font-size:10px; font-weight:bold;">
                        Tanggal Produksi:<br>
                        <span style="font-size:14px; color:#4318FF;">{{ date('d F Y', strtotime($date)) }}</span>
                    </td>
                </tr>
            </table>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th width="3%">NO</th>
                    <th width="10%">STATION / MESIN</th>
                    <th width="5%">SHIFT</th>
                    <th class="text-left" width="22%">PART NAME & NUMBER</th>
                    <th width="5%">CUST</th>
                    <th width="15%">NO. WOS (BATCH)</th>
                    <th>TARGET (IN)</th>
                    <th>ACTUAL (OK)</th>
                    <th>DEFECT (NG)</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $idx => $b)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td style="font-weight: 800;">{{ $b->kode_line }}</td>
                    <td>{{ $b->shift }}</td>
                    <td class="text-left">
                        <b style="font-size:12px;">{{ $b->part_name ?? 'Unknown Part' }}</b><br>
                        <span style="font-size:9px; color:#64748b;">{{ $b->part_no }}</span>
                    </td>
                    <td style="font-weight:bold;">{{ $b->customer_code ?? 'SAI' }}</td>
                    <td style="font-size:10px;">{{ $b->no_produksi }}</td>
                    <td class="text-num" style="color:#d97706;">{{ number_format($b->qty_masuk) }}</td>
                    <td class="text-num status-ok">{{ number_format($b->qty_ok ?? 0) }}</td>
                    <td class="text-num status-ng">{{ number_format($b->qty_ng ?? 0) }}</td>
                    <td style="font-weight:bold; font-size:9px;">
                        @if($b->status == 'COMPLETED')
                            <span style="color:#059669;">COMPLETED</span>
                        @else
                            <span style="color:#4318FF;">{{ $b->status }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="padding: 30px; font-style:italic;">Tidak ada data produksi Welding pada tanggal ini.</td>
                </tr>
                @endforelse
                @if($batches->isNotEmpty())
                <tr class="row-summary">
                    <td colspan="6" style="text-align: right; padding-right:15px;">TOTAL AKUMULASI HARIAN :</td>
                    <td class="text-num" style="color:#d97706;">{{ number_format($total_masuk) }}</td>
                    <td class="text-num status-ok">{{ number_format($total_ok) }}</td>
                    <td class="text-num status-ng">{{ number_format($total_ng) }}</td>
                    <td></td>
                </tr>
                @endif
            </tbody>
        </table>
        <table class="sign-table">
            <tr>
                <td>Dibuat Oleh (Leader Welding),<br><br><br><br><br>____________________</td>
                <td>Diperiksa Oleh (Spv. Produksi),<br><br><br><br><br>____________________</td>
                <td>Diketahui Oleh (Manajer),<br><br><br><br><br>____________________</td>
            </tr>
        </table>
    </div>
</body>
</html>