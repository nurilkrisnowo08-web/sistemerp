<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak SJ - {{ $no_sj }}</title>
    <style>
        /* Pengaturan Kertas Landscape (21.5cm x 16.5cm) */
        @page {
            size: 21.5cm 16.5cm;
            margin: 0 !important;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 21.5cm;
            height: 16.5cm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 9.5pt;
            background-color: white;
        }

        .print-wrapper {
            position: relative;
            width: 21.5cm;
            height: 16.5cm;
            overflow: hidden;
        }

        @media print {
            .no-print { display: none !important; }
        }

        /* 1. TANGGAL (SEJAJAR TULISAN "Bogor, .......") */
        .date-area {
            position: absolute;
            top: 0.9cm;
            left: 14.2cm;
            width: 6.5cm;
            text-align: left;
            font-weight: bold;
        }

        /* 2. CUSTOMER AREA (DI BAWAH "Kepada Yth,") */
        .customer-area {
            position: absolute;
            top: 2.0cm;
            left: 13.0cm;
            width: 7.8cm;
            line-height: 1.25;
        }
        .customer-name {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .customer-address {
            font-size: 8pt;
            margin-top: 2px;
        }

        /* 3. NOMOR PO & NO. SURAT JALAN (KIRI BAWAH KOP) */
        .header-info {
            position: absolute;
            top: 2.65cm;
            left: 3.9cm;
            font-weight: bold;
            font-size: 9.5pt;
        }
        .header-row {
            height: 0.55cm;
            line-height: 0.55cm;
        }

        /* 4. AREA TABEL BARANG (DI BAWAH GARIS HEADER TABEL) */
        .content-area {
            position: absolute;
            top: 4.8cm;
            left: 0.5cm;
            width: 20.5cm;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
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
        // Otomatis ambil part_name dari tabel parts jika belum ikut di-join dari controller
        $partNameMap = \Illuminate\Support\Facades\DB::table('parts')->pluck('part_name', 'part_no');
    @endphp

    <div class="print-wrapper">

        <div class="no-print" style="position: fixed; top: 10px; right: 10px; background: #fff; padding: 12px 15px; border: 2px solid #4361ee; border-radius: 12px; z-index: 9999;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #4361ee; color: #fff; border: none; font-weight: 800; cursor: pointer; border-radius: 8px;">🖨️ CETAK SJ!</button>
        </div>

        {{-- TANGGAL --}}
        <div class="date-area">
            {{ date('d F Y', strtotime($sj->created_at)) }}
        </div>

        {{-- CUSTOMER --}}
        <div class="customer-area">
            <div class="customer-name">{{ $customer->name ?? $sj->customer_code }}</div>
            <div class="customer-address">{{ $customer->address ?? '-' }}</div>
        </div>

        {{-- NO. PO & NO. SJ --}}
        <div class="header-info">
            <div class="header-row">{{ $po->po_number ?? '-' }}</div>
            <div class="header-row">{{ $no_sj }}</div>
        </div>

        {{-- TABEL ITEM --}}
        <div class="content-area">
            <table class="item-table">
                <tbody>
                    @foreach($items as $item)
                    @php
                        $pName = $item->part_name ?? ($partNameMap[$item->part_no] ?? '');
                    @endphp
                    <tr>
                        {{-- KOLOM JUMLAH (QTY + PCS) --}}
                        <td style="width: 11%; text-align: right; padding-right: 4px; font-size: 10.5pt;">{{ number_format($item->qty_delivery) }}</td>
                        <td style="width: 7%; text-align: left; font-size: 9pt;">PCS</td>

                        {{-- KOLOM NAMA / NO. PART --}}
                        <td style="width: 44%; text-align: left; padding-left: 0.4cm; line-height: 1.2;">
                            <div>{{ $item->part_no }}</div>
                            @if($pName)
                                <div style="font-size: 8.5pt; font-weight: normal;">{{ $pName }}</div>
                            @endif
                        </td>

                        {{-- KOLOM NO. LOT --}}
                        <td style="width: 16%; text-align: center;"></td>

                        {{-- KOLOM KETERANGAN --}}
                        <td style="width: 22%; text-align: left;"></td>
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