<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Mutasi FG - {{ $customer ?? 'ALL' }}</title>
    
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@600;800&display=swap" rel="stylesheet">
    
    <style>
        /* RESET & DASAR */
        body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; color: #000; background: #fff; margin: 0; padding: 0; font-size: 11px; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

        /* PENGATURAN KERTAS PRINT (A4 LANDSCAPE) */
        @page { size: A4 landscape; margin: 12mm; }
        .print-container { width: 100%; max-width: 100%; }

        /* KOP SURAT (HEADER) */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .header-table td { vertical-align: middle; }
        .logo-area img { height: 50px; margin-right: 15px; }
        .company-name { font-size: 16px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }
        .dept-name { font-size: 11px; font-weight: 700; color: #333; margin-top: 2px; }
        .doc-title { text-align: center; }
        .doc-title h1 { font-size: 18px; font-weight: 800; text-decoration: underline; margin: 0; letter-spacing: 1px; }
        .doc-title h2 { font-size: 12px; font-weight: 600; margin: 4px 0 0 0; }
        .meta-area { text-align: right; font-size: 10px; font-weight: 700; }
        .meta-table { float: right; border-collapse: collapse; }
        .meta-table td { padding: 2px 5px; }
        .meta-label { text-align: right; }
        .meta-value { text-align: left; font-family: 'JetBrains Mono', monospace; }

        .divider { border-bottom: 3px double #000; margin: 10px 0 15px 0; }
        .filter-info { margin-bottom: 15px; font-size: 11px; font-weight: 700; display: flex; justify-content: space-between; }
        .filter-badge { padding: 4px 8px; border: 1px solid #000; background: #f8fafc; border-radius: 4px; display: inline-block; margin-right: 10px; }

        /* TABEL DATA */
        .section-title { font-size: 13px; font-weight: 800; text-transform: uppercase; margin: 0 0 8px 0; background: #e2e8f0; padding: 6px 10px; border: 1px solid #000; border-bottom: none; display: inline-block;}
        .part-header { font-size: 11px; font-weight: 800; background: #f1f5f9; padding: 4px 8px; border: 1px solid #000; border-bottom: none; margin-top: 15px; display: inline-block; font-family: 'JetBrains Mono', monospace; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; border: 1px solid #000; }
        .data-table th { background: #cbd5e1; color: #000; padding: 8px 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; text-align: center; border: 1px solid #000; }
        .data-table td { padding: 6px; font-size: 10px; border: 1px solid #000; text-align: center; vertical-align: middle; }
        .data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        
        .text-left { text-align: left !important; }
        .mono { font-family: 'JetBrains Mono', monospace; font-weight: 700; }
        .text-in { color: #15803d; font-weight: 800; }
        .text-out { color: #b91c1c; font-weight: 800; }
        .text-akhir { color: #1d4ed8; font-weight: 800; font-size: 11px; }

        /* BLOK TANDA TANGAN */
        .signature-area { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
        .signature-area th { border: 1px solid #000; padding: 6px; font-size: 10px; background: #e2e8f0; width: 33.33%; text-align: center; }
        .signature-area td { border: 1px solid #000; height: 70px; vertical-align: bottom; text-align: center; padding: 5px; font-size: 10px; font-weight: 700; }

        .print-btn-wrapper { text-align: center; margin: 20px 0; }
        .btn-print { background: #2563eb; color: #fff; border: none; padding: 10px 20px; font-size: 14px; font-weight: 700; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-family: inherit; }
        .btn-back { background: #475569; color: #fff; border: none; padding: 10px 20px; font-size: 14px; font-weight: 700; border-radius: 6px; cursor: pointer; text-decoration: none; margin-right: 10px; font-family: inherit; }
        @media print { .print-btn-wrapper { display: none !important; } }
    </style>
</head>
<body>
    <div class="print-btn-wrapper">
        <button type="button" onclick="window.close()" class="btn-back">⬅ Tutup Halaman</button>
        <button type="button" onclick="window.print()" class="btn-print">🖨️ Cetak Dokumen Ini</button>
    </div>

    <div class="print-container">
        
        <!-- KOP SURAT -->
        <table class="header-table">
            <tr>
                <td style="width: 30%;">
                    <div style="display: flex; align-items: center;">
                        <div class="logo-area">
                            <img src="{{ asset('public/admin/img/Logo-asalta.png') }}" alt="Logo Asalta" onerror="this.style.display='none'">
                        </div>
                        <div>
                            <h2 class="company-name">PT. Asalta Mandiri Agung</h2>
                            <div class="dept-name">DEPARTEMEN PPIC - FINISHED GOODS</div>
                        </div>
                    </div>
                </td>
                <td style="width: 40%;" class="doc-title">
                    <h1>FINISHED GOODS MONTHLY RECAP</h1>
                    <h2>OFFICIAL MUTATION LEDGER</h2>
                </td>
                <td style="width: 30%;" class="meta-area">
                    <table class="meta-table">
                        <tr><td class="meta-label">PRINT DATE :</td><td class="meta-value">{{ date('d M Y') }}</td></tr>
                        <tr><td class="meta-label">TIME :</td><td class="meta-value">{{ date('H:i:s') }}</td></tr>
                        <tr><td class="meta-label">PAGE :</td><td class="meta-value">1 of 1</td></tr>
                    </table>
                </td>
            </tr>
        </table>
        
        <div class="divider"></div>

        @php $monthName = date('F', mktime(0, 0, 0, $month, 10)); @endphp
        <div class="filter-info">
            <div>
                <span class="filter-badge">CUSTOMER: <strong>{{ $customer === 'ALL' ? 'SEMUA CUSTOMER (ALL)' : $customer }}</strong></span>
                <span class="filter-badge">PART NO: <strong>{{ $partFilter ?? 'SEMUA PART' }}</strong></span>
            </div>
            <div>
                <span class="filter-badge">PERIOD: <strong>{{ strtoupper($monthName) }} {{ $year }}</strong></span>
            </div>
        </div>

        <!-- 1. TABEL REKAP BULANAN -->
        <div class="section-title">1. REKAPITULASI STOK BULANAN</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 15%;">PART NO</th>
                    <th rowspan="2" style="width: 37%;">PART NAME</th>
                    <th colspan="4">ALUR MUTASI STOK (PCS)</th>
                </tr>
                <tr>
                    <th style="width: 12%;">SALDO AWAL</th>
                    <th style="width: 12%;">TOTAL IN</th>
                    <th style="width: 12%;">TOTAL OUT</th>
                    <th style="width: 12%;">SALDO AKHIR</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recap as $fg)
                    <tr>
                        <td class="mono">{{ $fg->part_no }}</td>
                        <td class="text-left"><strong>{{ $fg->part_name }}</strong></td>
                        <td class="mono">{{ number_format($fg->stock_awal) }}</td>
                        <td class="mono text-in">+{{ number_format($fg->total_in) }}</td>
                        <td class="mono text-out">-{{ number_format($fg->total_out) }}</td>
                        <td class="mono text-akhir">{{ number_format($fg->stock_akhir) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Tidak ada data rekap pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- 2. TABEL RINCIAN MUTASI HARIAN (GROUP BY PART) -->
        <div class="section-title" style="margin-top: 10px;">2. AUDIT TRAIL / RINCIAN MUTASI (01 - {{ date('t', strtotime("$year-$month-01")) }} {{ strtoupper($monthName) }})</div>

        @php 
            $groupedDetails = $dailyDetails->groupBy('part_no'); 
        @endphp

        @forelse($groupedDetails as $partNo => $details)
            @php
                // Ambil nama part dari koleksi rekap
                $partName = $recap->where('part_no', $partNo)->first()->part_name ?? 'Unknown Part';
            @endphp
            <div class="part-header">PART: {{ $partNo }} - {{ $partName }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 12%;">TANGGAL</th>
                        <th style="width: 10%;">JAM</th>
                        <th style="width: 42%;">NO. REFERENSI (WOS / SURAT JALAN)</th>
                        <th style="width: 18%;">IN (PRODUKSI)</th>
                        <th style="width: 18%;">OUT (DELIVERY)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details as $detail)
                        @php
                            $ref_doc = !empty($detail->no_dokumen) && $detail->no_dokumen !== '-' && $detail->no_dokumen !== 'NULL' ? $detail->no_dokumen : ($detail->type == 'IN' ? 'WOS-SYSTEM' : 'SJ-SYSTEM');
                        @endphp
                        <tr>
                            <td class="mono">{{ date('d/m/Y', strtotime($detail->tgl)) }}</td>
                            <td class="mono">{{ date('H:i', strtotime($detail->jam)) }}</td>
                            <td class="mono text-left">
                                @if($detail->type == 'IN')
                                    ⚙️ {{ strtoupper($ref_doc) }}
                                @else
                                    🚚 {{ strtoupper($ref_doc) }}
                                @endif
                            </td>
                            <td class="mono {{ $detail->type == 'IN' ? 'text-in' : '' }}">
                                {{ $detail->type == 'IN' ? '+'.number_format($detail->in_qty) : '-' }}
                            </td>
                            <td class="mono {{ $detail->type == 'OUT' ? 'text-out' : '' }}">
                                {{ $detail->type == 'OUT' ? '-'.number_format($detail->out_qty) : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @empty
            <table class="data-table">
                <tbody><tr><td>Tidak ada mutasi harian pada periode ini.</td></tr></tbody>
            </table>
        @endforelse

        <!-- BLOK TANDA TANGAN -->
        <table class="signature-area">
            <thead>
                <tr>
                    <th>DIBUAT OLEH (PREPARED)</th>
                    <th>DIPERIKSA OLEH (CHECKED)</th>
                    <th>DISETUJUI OLEH (APPROVED)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>( ADMIN FG )</td>
                    <td>( SUPERVISOR PPIC )</td>
                    <td>( MANAGER PPIC )</td>
                </tr>
            </tbody>
        </table>

    </div>

    <!-- Script otomatis buka dialog print -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>