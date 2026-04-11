<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Pengiriman PO - {{ $po_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11pt; padding: 30px; color: #333; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .info-table { width: 100%; margin-bottom: 20px; border: none; }
        .info-table td { padding: 3px 0; }
        .main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .main-table th { background: #f2f2f2; border: 1px solid #000; padding: 10px; font-size: 10pt; }
        .main-table td { border: 1px solid #000; padding: 8px; text-align: center; }
        .text-left { text-align: left; }
        .text-bold { font-weight: bold; }
        
        /* CSS UNTUK TOMBOL NAVIGASI MELAYANG */
        .floating-nav {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fff;
            padding: 15px;
            border: 2px solid #4e73df;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 9999;
            text-align: center;
        }
        .btn-nav {
            display: block;
            width: 150px;
            padding: 8px 15px;
            margin-bottom: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 9pt;
            border-radius: 5px;
            cursor: pointer;
            border: none;
        }
        .btn-print { background: #4e73df; color: white; }
        .btn-back { background: #f8f9fc; color: #4e73df; border: 1px solid #4e73df !important; }

        @media print { 
            .no-print { display: none !important; } 
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    {{-- 1. TOMBOL NAVIGASI (HANYA MUNCUL DI LAYAR) --}}
    <div class="floating-nav no-print">
        <button onclick="window.print()" class="btn-nav btn-print">
            <i class="fas fa-print"></i> 🖨️ PRINT REKAP
        </button>
        <a href="{{ route('delivery.history') }}" class="btn-nav btn-back">
            ← KEMBALI
        </a>
    </div>

    {{-- 2. KOP LAPORAN --}}
    <div class="header">
        <h2 style="margin:0;">LAPORAN REKAPITULASI PENGIRIMAN PO</h2>
        <h3 style="margin:5px 0;">PT. ASALTA MANDIRI AGUNG</h3>
    </div>

    {{-- 3. INFORMASI PO --}}
    <table class="info-table">
        <tr>
            <td width="150">NOMOR PO</td>
            <td width="300">: <strong>{{ $po_number }}</strong></td>
            <td width="150">CUSTOMER</td>
            <td>: <span class="text-bold">{{ $poHeader->customer_code }}</span></td>
        </tr>
        <tr>
            <td>TANGGAL CETAK</td>
            <td>: {{ date('d/m/Y H:i') }} WIB</td>
            <td>STATUS PO</td>
            <td>: <span class="text-bold text-uppercase">{{ $poHeader->status }}</span></td>
        </tr>
    </table>

    {{-- 4. TABEL RINGKASAN OUTSTANDING --}}
    <h4 style="margin-bottom: 10px; border-left: 5px solid #000; padding-left: 10px;">RINGKASAN ITEM PO</h4>
    <table class="main-table">
        <thead>
            <tr>
                <th>PART NUMBER</th>
                <th>QTY ORDER (PO)</th>
                <th>TOTAL TERKIRIM</th>
                <th>SISA (OUTSTANDING)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($poItems as $item)
            @php
                $terkirim = $allDeliveries->where('po_id', $item->id)->sum('qty_delivery');
                $sisa = $item->quantity - $terkirim;
            @endphp
            <tr>
                <td class="text-left text-bold">{{ $item->part_no }}</td>
                <td>{{ number_format($item->quantity) }} PCS</td>
                <td style="color: #28a745; font-weight: bold;">{{ number_format($terkirim) }}</td>
                <td style="color: #e74a3b; font-weight: bold;">{{ number_format($sisa) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 5. RINCIAN PER SURAT JALAN (DENGAN FIX JAM WIB) --}}
    <h4 style="margin-top: 40px; margin-bottom: 10px; border-left: 5px solid #000; padding-left: 10px;">
        RINCIAN SURAT JALAN (STAGE DELIVERY)
    </h4>
    <table class="main-table" style="font-size: 10pt;">
        <thead>
            <tr>
                <th>TANGGAL KIRIM</th>
                <th>NO SURAT JALAN</th>
                <th>PART NUMBER</th>
                <th>QTY KIRIM</th>
            </tr>
        </thead>
        <tbody>
            @forelse($allDeliveries as $delivery)
            <tr>
                {{-- FIX JAM: Paksa konversi dari UTC ke Asia/Jakarta agar tidak selisih 7 jam --}}
                <td>
                    {{ \Carbon\Carbon::parse($delivery->created_at, 'UTC')->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB
                </td>
                <td class="text-bold" style="color: #4e73df;">{{ $delivery->no_sj }}</td>
                <td>{{ $delivery->part_no }}</td>
                <td>{{ number_format($delivery->qty_delivery) }} PCS</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-muted" style="padding: 20px;">Belum ada riwayat pengiriman untuk PO ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- 6. TANDA TANGAN --}}
    <div style="margin-top: 60px; float: right; text-align: center; width: 250px;">
        <p>Hormat Kami,</p>
        <br><br><br><br>
        <p><strong>( WAHAB )</strong></p>
        <div style="border-top: 1px solid #000; margin-top: 5px; font-size: 9pt;">Logistic & Warehouse Dept.</div>
    </div>

    <div style="clear: both;"></div>

</body>
</html>