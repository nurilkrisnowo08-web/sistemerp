<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak SJ - {{ $no_sj }}</title> 
    <style>
        @page { 
            size: 16.5cm 21.5cm; /* Portrait rill */
            margin: 0; 
        }

        body { 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 10pt; margin: 0; padding: 0; color: #000; 
        }

        @media print { .no-print { display: none !important; } }

        /* TANGGAL: TETAP DI ATAS PT ICHII (KANAN) */
        .date-area { 
            position: absolute; 
            top: 2.0cm;      
            right: 0.8cm;    
            width: 7.5cm;    
            text-align: left; 
            font-weight: bold; 
        }

        /* POSISI CUSTOMER (PT ICHII) */
        .customer-area { 
            position: absolute; 
            top: 2.8cm; 
            right: 0.8cm; 
            width: 7.5cm; 
            line-height: 1.2; 
        }
        .customer-name { font-size: 11pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }

        /* ✨ PERBAIKAN: KEKIRIIN & KEBAWAHAN rill */
        .header-info { 
            position: absolute; 
            top: 4.8cm;     /* Diturunkan dari 4.5cm ke 4.8cm */
            left: 0.5cm;    /* Digeser ke kiri dari 1.0cm ke 0.5cm */
            font-weight: bold; 
        }
        .header-row { height: 0.6cm; }

        /* DAFTAR BARANG: IKUT TURUN SEDIKIT rill */
        .content-area { position: absolute; top: 8.8cm; width: 100%; }
        .item-table { width: 100%; border-collapse: collapse; }
        .item-table td { height: 0.8cm; vertical-align: middle; font-weight: bold; }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="position: fixed; top: 10px; right: 10px; background: #fff; padding: 15px; border: 2px solid #4361ee; border-radius: 12px; box-shadow: 0 10px 20px rgba(0,0,0,0.2); z-index: 9999;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #4361ee; color: #fff; border: none; font-weight: 800; cursor: pointer; border-radius: 8px;">🖨️ CETAK PORTRAIT rill!</button>
        <a href="{{ route('delivery.index') }}" style="display: block; text-align: center; color: #64748b; margin-top: 10px; font-weight: bold; text-decoration: none;">← KEMBALI</a>
    </div>

    <div class="date-area">
        {{ date('d F Y', strtotime($sj->created_at)) }}
    </div>

    <div class="customer-area">
        <div class="customer-name">{{ $customer->name ?? $sj->customer_code }}</div>
        <div class="customer-address" style="font-size: 9pt;">{{ $customer->address ?? '-' }}</div>
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
                    <td style="width: 12%; text-align: center; font-size: 12pt;">{{ number_format($item->qty_delivery) }}</td>
                    <td style="width: 10%; text-align: center;">PCS</td>
                    <td style="width: 48%; text-align: left; padding-left: 0.3cm;">{{ $item->part_no }}</td>
                    <td style="width: 10%;"></td>
                    <td style="width: 20%;"></td>
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