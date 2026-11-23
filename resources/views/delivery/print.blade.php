<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak SJ - {{ $no_sj }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0 !important;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 21cm;
            height: 16.5cm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 9.5pt;
            background-color: white;
        }

        .print-wrapper {
            position: relative;
            width: 21cm;
            height: 16.5cm;
            overflow: hidden;
        }

        @media print {
            .no-print { display: none !important; }
        }

        /* 1. TANGGAL: GESER KE KANAN BIAR PAS DI GARIS SETELAH "Bogor," */
        .date-area {
            position: absolute;
            top: 0.9cm;
            left: 15.3cm;
            width: 5.2cm;
            text-align: left;
            font-weight: bold;
        }

        /* 2. CUSTOMER AREA: GESER KE KANAN & TURUN SEDIKIT BIAR PAS DI GARIS TITIK-TITIK */
        .customer-area {
            position: absolute;
            top: 2.3cm;
            left: 13.5cm;
            width: 7.0cm;
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

        /* 3. NOMOR PO & NO. SURAT JALAN: TURUN & GESER KANAN BIAR PAS SETELAH ":" */
        .header-info {
            position: absolute;
            top: 3.45cm;
            left: 4.1cm;
            font-weight: bold;
            font-size: 9.5pt;
        }
        .header-row {
            height: 0.65cm;
            line-height: 0.65cm;
        }

        /* 4. DAFTAR BARANG: PAS DI DALAM KOTAK JUMLAH & NAMA / NO. PART */
        .content-area {
            position: absolute;
            top: 6.2cm;
            left: 0.5cm;
            width: 20cm;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .item-table td {
            padding: 4px 2px;
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
                        {{-- KOLOM JUMLAH (21%) --}}
                        <td style="width: 21%; text-align: center; font-size: 10pt;">
                            {{ number_format($item->qty_delivery) }} PCS
                        </td>

                        {{-- KOLOM NAMA / NO. PART (40%) --}}
                        <td style="width: 40%; text-align: left; padding-left: 0.4cm; line-height: 1.25;">
                            <div>{{ $item->part_no }}</div>
                            @if($pName)
                                <div style="font-size: 8.5pt; font-weight: normal;">{{ $pName }}</div>
                            @endif
                        </td>

                        {{-- KOLOM NO. LOT (16%) --}}
                        <td style="width: 16%; text-align: center;"></td>

                        {{-- KOLOM KETERANGAN (23%) --}}
                        <td style="width: 23%; text-align: left;"></td>
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