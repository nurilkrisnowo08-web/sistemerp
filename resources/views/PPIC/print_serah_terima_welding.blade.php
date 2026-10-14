<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BAST Material WIP Welding - {{ $line_code }}</title>
    <!-- Font Google untuk kesan modern & profesional -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Konfigurasi Kertas A4 Portrait */
        @page { 
            size: A4 portrait; 
            margin: 15mm; 
        }
        
        body { 
            font-family: 'Plus Jakarta Sans', Arial, sans-serif; 
            font-size: 11px; 
            margin: 0; 
            padding: 0; 
            color: #1e293b; 
            background-color: #fff;
        }

        /* 🖨️ Tombol Aksi (Akan hilang saat diprint) */
        .action-bar {
            text-align: right;
            padding: 15px;
            background-color: #f1f5f9;
            border-bottom: 2px solid #cbd5e1;
            margin-bottom: 25px;
        }
        
        .btn {
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-family: 'Plus Jakarta Sans', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-back { background: #64748b; color: white; margin-right: 10px; }
        .btn-print { background: #2563eb; color: white; }

        /* 🏢 Header Dokumen */
        .doc-header {
            display: table;
            width: 100%;
            border-bottom: 3px solid #0f172a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 60%;
        }

        .header-right {
            display: table-cell;
            vertical-align: bottom;
            text-align: right;
            width: 40%;
        }

        .company-name {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 4px 0;
            letter-spacing: -0.5px;
            color: #0f172a;
        }

        .doc-title {
            font-size: 14px;
            font-weight: 600;
            margin: 0;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .meta-box {
            display: inline-block;
            text-align: left;
            border: 1px solid #cbd5e1;
            padding: 8px 15px;
            border-radius: 6px;
            background-color: #f8fafc;
        }

        .meta-box p {
            margin: 3px 0;
            font-size: 11px;
        }

        .meta-box strong { color: #0f172a; }

        /* 📋 Tabel Data */
        table.data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px; 
        }
        
        table.data-table th, table.data-table td { 
            border: 1px solid #94a3b8; 
            padding: 10px 8px; 
            vertical-align: middle;
        }
        
        table.data-table th { 
            background-color: #f1f5f9; 
            font-weight: 800; 
            font-size: 10px; 
            text-transform: uppercase; 
            color: #334155;
            text-align: center;
        }

        table.data-table td.center { text-align: center; }
        
        .part-no {
            font-weight: 800;
            font-size: 12px;
            color: #0f172a;
            display: block;
        }

        .part-name {
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
            margin-top: 3px;
        }

        .qty-box {
            font-size: 14px;
            font-weight: 800;
            color: #0369a1;
            background: #f0f9ff;
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #bae6fd;
            display: inline-block;
        }

        .row-total {
            background-color: #f8fafc;
        }
        
        .row-total td {
            font-weight: 800;
            font-size: 12px;
            padding: 12px 8px;
        }

        /* ✍️ Area Tanda Tangan */
        .signature-area {
            width: 100%;
            margin-top: 40px;
            display: table;
            table-layout: fixed;
        }

        .sig-box {
            display: table-cell;
            text-align: center;
            vertical-align: top;
        }

        .sig-title {
            font-weight: 700;
            font-size: 11px;
            color: #475569;
            margin-bottom: 70px;
            text-transform: uppercase;
        }

        .sig-line {
            width: 70%;
            margin: 0 auto;
            border-bottom: 1px solid #1e293b;
            margin-bottom: 5px;
        }

        .sig-name {
            font-weight: 800;
            font-size: 11px;
            color: #0f172a;
        }

        /* Aturan khusus saat dokumen diprint */
        @media print { 
            .no-print { display: none !important; } 
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .header-container { margin-top: 0; }
        }
    </style>
</head>
<body>

    {{-- 🖨️ Tombol Print (Sembunyi saat diprint) --}}
    <div class="no-print action-bar">
        <button class="btn btn-back" onclick="window.close();">TUTUP HALAMAN</button>
        <button class="btn btn-print" onclick="window.print()">Cetak Dokumen BAST</button>
    </div>

    {{-- 🏢 Header Dokumen --}}
    <div class="doc-header">
        <div class="header-left">
            <h2 class="company-name">PT. ASALTA MANDIRI AGUNG</h2>
            <p class="doc-title">BERITA ACARA SERAH TERIMA (BAST) - WIP WELDING</p>
        </div>
        <div class="header-right">
            <div class="meta-box">
                <p><strong>STATION MESIN :</strong> <span style="color:#2563eb; font-weight:800; font-size:13px;">{{ strtoupper($line_code) }}</span></p>
                <p><strong>TANGGAL DEPLOY :</strong> {{ date('d F Y', strtotime($date)) }}</p>
                <p><strong>DOKUMEN REF :</strong> BAST-WLD/{{ date('ymd', strtotime($date)) }}/{{ rand(100,999) }}</p>
            </div>
        </div>
    </div>

    {{-- 📋 Tabel Material WIP --}}
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="12%">SHIFT</th>
                <th width="45%" style="text-align: left; padding-left: 15px;">IDENTIFIKASI PART (WIP)</th>
                <th width="20%">BATCH ID</th>
                <th width="18%">QTY DIAMBIL</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            
            @forelse($batches as $index => $b)
                @php $grandTotal += $b->qty_masuk; @endphp
                <tr>
                    <td class="center font-weight-bold">{{ $index + 1 }}</td>
                    <td class="center" style="font-weight: 800; color: #475569;">{{ strtoupper($b->shift) }}</td>
                    <td style="padding-left: 15px;">
                        <span class="part-no">{{ $b->part_no }}</span>
                        <div class="part-name">{{ $b->part_name ?? 'N/A' }}</div>
                    </td>
                    <td class="center" style="font-family: monospace; font-size:10px; color:#64748b;">
                        {{ $b->no_produksi_stamping }}
                    </td>
                    <td class="center">
                        <div class="qty-box">{{ number_format($b->qty_masuk) }} PCS</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="center" style="padding: 30px; color: #94a3b8; font-style: italic;">
                        Tidak ada record pengambilan barang setengah jadi (WIP) untuk jadwal ini.
                    </td>
                </tr>
            @endforelse
            
            {{-- Baris Total --}}
            <tr class="row-total">
                <td colspan="4" style="text-align: right; padding-right: 20px; color: #475569;">TOTAL KESELURUHAN DEPLOYMENT :</td>
                <td class="center" style="color: #dc2626; font-size: 15px;">{{ number_format($grandTotal) }} PCS</td>
            </tr>
        </tbody>
    </table>

    {{-- ✍️ Area Tanda Tangan --}}
    <div class="signature-area">
        <div class="sig-box">
            <div class="sig-title">Diserahkan Oleh,</div>
            <div class="sig-line"></div>
            <div class="sig-name">GUDANG WIP / FG</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Diketahui Oleh,</div>
            <div class="sig-line"></div>
            <div class="sig-name">PPIC DEPARTMENT</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Diterima Oleh,</div>
            <div class="sig-line"></div>
            <div class="sig-name">PRODUKSI WELDING</div>
        </div>
    </div>

</body>
</html>