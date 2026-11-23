<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak SJ - {{ $no_sj }}</title> 
    <style>
        /* Pengaturan Kertas Portrait */
        @page { 
            size: 16.5cm 21.5cm; 
            margin: 0 !important; 
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 16.5cm;
            height: 21.5cm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 9pt;
            background-color: white;
        }

        .print-wrapper {
            position: relative;
            width: 16.5cm;
            height: 21.5cm;
            overflow: hidden;
        }

        @media print { .no-print { display: none !important; } }

        /* TANGGAL: LEBIH KE ATAS & LEBAR KE KANAN */
        .date-area { 
            position: absolute; 
            top: 0.2cm;      
            right: 0.2cm;    
            width: 5.5cm;    
            text-align: left; 
            font-weight: bold; 
        }

        /* CUSTOMER AREA: LEBIH KE ATAS & LEBAR KE KANAN */
        .customer-area { 
            position: absolute; 
            top: 1.0cm;      
            right: 0.2cm; 
            width: 8.5cm; 
            line-height: 1.2; 
        }
        .customer-name { font-size: 10pt; font-weight: bold; text-transform: uppercase; }

        /* NOMOR PO & SJ: LEBIH KE ATAS & LEBIH KE KIRI */
        .header-info { 
            position: absolute; 
            top: 1.8cm;      
            left: 3.0cm;     
            font-weight: bold; 
        }
        .header-row { height: 0.45cm; }

        /* DAFTAR BARANG: LEBIH KE ATAS, KIRI RAPAT, KANAN LEBAR */
        .content-area { 
            position: absolute; 
            top: 3.5cm;
            left: 0;
            width: 100%; 
        }
        .item-table { width: 100%; border-collapse: collapse; }
        .item-table td { 
            padding: 3px 0;
            vertical-align: top; 
            font-weight: bold; 
        }
    </style>
</head>
<body onload="window.print()">

    @php
        $partNameMap = \Illuminate\Support\Facades\DB::table('parts')->pluck('part_name', 'part_no');
    @endphp

    <div class="print-wrapper">
        
        <div class="no-print" style="position: fixed; top: 10px; right: 10px; background: #fff; padding: 15px; border: 2px solid #4361ee; border-radius: 12px; z-index: 9999;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #4361ee; color: #fff; border: none; font-weight: 800; cursor: pointer; border-radius: 8px;">🖨️ CETAK SJ!</button>
        </div>

        <div class="date-area">
            {{ date('d F Y', strtotime($sj->created_at)) }}
        </div>

        <div class="customer-area">
            <div class="customer-name">{{ $customer->name ?? $sj->customer_code }}</div>
            <div class="customer-address" style="font-size: 8pt;">{{ $customer->address ?? '-' }}</div>
        </div>

        <div class="header-info">
            <div class="header-row">{{ $po->po_number ?? '-' }}</div>
            <div class="header-row" style="margin-top: 1px;">{{ $no_sj }}</div>
        </div>

        <div class="content-area">
            <table class="item-table">
                <tbody>
                    @foreach($items as $item)
                    @php
                        $pName = $item->part_name ?? ($partNameMap[$item->part_no] ?? '');
                    @endphp
                    <tr>
                        <td style="width: 10%; text-align: center; font-size: 11pt;">{{ number_format($item->qty_delivery) }}</td>
                        <td style="width: 8%; text-align: left;">PCS</td>
                        <td style="width: 62%; text-align: left; padding-left: 0.2cm; line-height: 1.2;">
                            <div>{{ $item->part_no }}</div>
                            @if($pName)
                                <div style="font-size: 8pt; font-weight: normal;">{{ $pName }}</div>
                            @endif
                        </td>
                        <td style="width: 8%;"></td>
                        <td style="width: 12%;"></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    <script>
        window.onafterprint = function() {
            window.location.href = "{{ route('delivery.history') }}";
        };
    </script>
</body>
</html>