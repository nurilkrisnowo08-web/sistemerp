<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak SJ - {{ $no_sj }}</title>
    <style>
        /* Letter Portrait (21.59cm x 27.94cm) */
        @page {
            size: 21.59cm 27.94cm;
            margin: 0 !important;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 21.59cm;
            height: 27.94cm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 9pt;
            background-color: white;
        }

        .print-wrapper {
            position: relative;
            width: 21.59cm;
            height: 27.94cm;
            overflow: hidden;
        }

        @media print {
            .no-print { display: none !important; }
        }

        /* 1. TANGGAL: TURUN & GESER KANAN BIAR PAS DI GARIS SETELAH "Bogor," */
        .date-area {
            position: absolute;
            top: 1.15cm;
            left: 15.5cm;
            width: 5.5cm;
            text-align: left;
            font-weight: bold;
        }

        /* 2. CUSTOMER AREA: TURUN & GESER KANAN BIAR TIDAK NABRAK "Kepada Yth," */
        .customer-area {
            position: absolute;
            top: 2.55cm;
            left: 14.5cm;
            width: 6.4cm;
        }
        .customer-name {
            font-size: 9.5pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 0.52cm;
        }
        .customer-address {
            font-size: 8pt;
            margin-top: 0.1cm;
            line-height: 0.52cm;
            word-wrap: break-word;
        }

        /* 3. NO. PO & NO. SURAT JALAN: TURUN & GESER KANAN SEDIKIT */
        .header-info {
            position: absolute;
            top: 3.6cm;
            left: 5.9cm;
            font-weight: bold;
            font-size: 9pt;
        }
        .header-row {
            height: 0.6cm;
            line-height: 0.6cm;
        }

        /* 4. DAFTAR BARANG: TURUN MASUK KOTAK & GESER KANAN LEPAS GARIS VERTIKAL */
        .content-area {
            position: absolute;
            top: 6.1cm;
            left: 1.8cm;
            width: 18.8cm;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .item-table td {
            padding: 3px 2px;
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

        <div class="no-print" style="position: fixed; top: 10px; right: 10px; background: #fff; padding: 12px 15px; border: 2px solid #4361ee; border-radius: 12px; z-index: 9999;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #4361ee; color: #fff; border: none; font-weight: 800; cursor: pointer; border-radius: 8px;">🖨️ CETAK SJ!</button>
        </div>

        <div class="date-area">
            {{ date('d F Y', strtotime($sj->created_at)) }}
        </div>

        <div class="customer-area">
            <div class="customer-name">{{ $customer->name ?? $sj->customer_code }}</div>
            <div class="customer-address">{{ $customer->address ?? '-' }}</div>
        </div>

        <div class="header-info">
            <div class="header-row">{{ $po->po_number ?? '-' }}</div>
            <div class="header-row">{{ $no_sj }}</div>
        </div>

        <div class="content-area">
            <table class="item-table">
                <tbody>
                    @foreach($items as $item)
                    @php
                        $pName = $item->part_name ?? ($partNameMap[$item->part_no] ?? '');
                    @endphp
                    <tr>
                        {{-- QTY --}}
                        <td style="width: 10%; text-align: right; padding-right: 6px; font-size: 9.5pt;">
                            {{ number_format($item->qty_delivery) }}
                        </td>

                        {{-- SATUAN PCS --}}
                        <td style="width: 8%; text-align: center; font-size: 8.5pt;">
                            PCS
                        </td>

                        {{-- PART NO & PART NAME --}}
                        <td style="width: 52%; text-align: left; padding-left: 1.1cm; line-height: 1.25;">
                            <div>{{ $item->part_no }}</div>
                            @if($pName)
                                <div style="font-size: 8pt; font-weight: normal;">{{ $pName }}</div>
                            @endif
                        </td>

                        {{-- NO. LOT & KETERANGAN --}}
                        <td style="width: 15%;"></td>
                        <td style="width: 15%;"></td>
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