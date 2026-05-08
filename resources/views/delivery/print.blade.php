<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak SJ - {{ $no_sj }}</title> 
    <style>
        @page { 
            size: 21.5cm 16.5cm; /* 1/2 F4 Landscape rill */
            margin: 0; 
        }

        body { 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 11pt; margin: 0; padding: 0; color: #000; 
        }

        /* Navigasi layar biar gak ikut ke-print rill */
        @media print { .no-print { display: none !important; } }

        /* ✨ PERBAIKAN POSISI TANGGAL rill */
        .date-area { 
            position: absolute; 
            top: 3.2cm;  /* Disamakan dengan top customer-area biar sejajar PT ICHII */
            left: 1.3cm; /* Digeser ke kiri supaya sejajar dengan kolom Qty (50 PCS) */
            font-weight: bold; 
        }

        /* POSISI CUSTOMER */
        .customer-area { position: absolute; top: 3.2cm; right: 1.2cm; width: 9.5cm; line-height: 1.2; }
        .customer-name { font-size: 12pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }

        /* POSISI NOMOR PO & SJ */
        .header-info { 
            position: absolute; 
            top: 4.1cm; 
            left: 4.5cm; 
            font-weight: bold; 
        }
        .header-row { height: 0.7cm; }

        /* POSISI DAFTAR BARANG rill */
        .content-area { position: absolute; top: 7.7cm; width: 100%; }
        .item-table { width: 100%; border-collapse: collapse; }
        .item-table td { height: 0.94cm; vertical-align: middle; font-weight: bold; }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="position: fixed; top: 10px; right: 10px; background: #fff; padding: 15px; border: 2px solid #4361ee; border-radius: 12px; box-shadow: 0 10px 20px rgba(0,0,0,0.2); z-index: 9999;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #4361ee; color: #fff; border: none; font-weight: 800; cursor: pointer; border-radius: 8px;">🖨️ CETAK SEKARANG rill!</button>
        <a href="{{ route('delivery.index') }}" style="display: block; text-align: center; color: #64748b; margin-top: 10px; font-weight: bold; text-decoration: none;">← KEMBALI</a>
    </div>

    <div class="date-area">
        Bogor, {{ date('d F Y', strtotime($sj->created_at)) }}
    </div>

    <div class="customer-area">
        <div class="customer-name">{{ $customer->name ?? $sj->customer_code }}</div>
        <div class="customer-address">{{ $customer->address ?? '-' }}</div>
    </div>

    <div class="header-info">
        <div class="header-row">: {{ $po->po_number ?? '-' }}</div>
        <div class="header-row" style="margin-top: 1px;">: {{ $no_sj }}</div>
    </div>

    <div class="content-area">
        <table class="item-table">
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td style="width: 14%; text-align: center; font-size: 13pt;">{{ number_format($item->qty_delivery) }}</td>
                    <td style="width: 10%; text-align: center;">PCS</td>
                    <td style="width: 45%; text-align: left; padding-left: 1.5cm;">{{ $item->part_no }}</td>
                    <td style="width: 13%;"></td>
                    <td style="width: 18%;"></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        window.onafterprint = function() {
            window.location.href = "{{ route('delivery.index') }}";
        };
    </script>
</body>
</html>