<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak SJ - {{ $no_sj }}</title> 
    <style>
        /* 1. Kunci Ukuran Kertas rill */
        @page { 
            size: 16.5cm 21.5cm; /* Portrait murni rill */
            margin: 0 !important; /* Paksa margin nol biar gak lari ke tengah */
        }

        /* 2. Reset Body agar nempel ke pojok kiri atas rill */
        html, body {
            margin: 0;
            padding: 0;
            width: 16.5cm;
            height: 21.5cm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 9pt;
            background-color: white;
        }

        /* 3. Container Utama untuk mengunci posisi rill */
        .print-wrapper {
            position: relative;
            width: 16.5cm;
            height: 21.5cm;
            overflow: hidden; /* Biar gak nambah halaman kosong rill */
        }

        @media print { 
            .no-print { display: none !important; } 
            body { -webkit-print-color-adjust: exact; }
        }

        /* ✨ POSISI TANGGAL: ME PET KE ATAS & KANAN rill */
        .date-area { 
            position: absolute; 
            top: 0.4cm;      
            right: 1.0cm;    
            width: 7.0cm;    
            font-weight: bold; 
        }

        /* ✨ POSISI CUSTOMER rill */
        .customer-area { 
            position: absolute; 
            top: 1.4cm;      
            right: 1.0cm; 
            width: 7.0cm; 
            line-height: 1.1; 
        }
        .customer-name { font-size: 10pt; font-weight: bold; text-transform: uppercase; }

        /* ✨ POSISI NO PO & SJ rill */
        .header-info { 
            position: absolute; 
            top: 1.4cm;      
            left: 0.5cm;    
            font-weight: bold; 
        }
        .header-row { height: 0.45cm; }

        /* ✨ POSISI DAFTAR BARANG rill */
        .content-area { 
            position: absolute; 
            top: 5.5cm;      
            width: 100%; 
        }
        .item-table { width: 100%; border-collapse: collapse; }
        .item-table td { 
            height: 0.65cm;   
            vertical-align: middle; 
            font-weight: bold; 
        }
    </style>
</head>
<body onload="window.print()">

    <div class="print-wrapper"> {{-- Container Pengunci rill --}}
        
        <div class="no-print" style="position: fixed; top: 10px; right: 10px; background: #fff; padding: 15px; border: 2px solid #4361ee; border-radius: 12px; z-index: 9999;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #4361ee; color: #fff; border: none; font-weight: 800; cursor: pointer; border-radius: 8px;">🖨️ CETAK SJ rill!</button>
        </div>

        <div class="date-area">
            {{ date('d F Y', strtotime($sj->created_at)) }}
        </div>

        <div class="customer-area">
            <div class="customer-name">{{ $customer->name ?? $sj->customer_code }}</div>
            <div class="customer-address" style="font-size: 8pt;">{{ $customer->address ?? '-' }}</div>
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
                        <td style="width: 12%; text-align: center; font-size: 11pt;">{{ number_format($item->qty_delivery) }}</td>
                        <td style="width: 10%; text-align: center;">PCS</td>
                        <td style="width: 48%; text-align: left; padding-left: 0.3cm;">{{ $item->part_no }}</td>
                        <td style="width: 10%;"></td>
                        <td style="width: 20%;"></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div> {{-- End print-wrapper rill --}}

    <script>
        window.onafterprint = function() {
            window.location.href = "{{ route('delivery.index') }}";
        };
    </script>
</body>
</html>