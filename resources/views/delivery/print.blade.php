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

        /* 1. TANGGAL (SUDAH PAS - TIDAK DIUBAH) */
        .date-area {
            position: absolute;
            top: 0.9cm;
            left: 14.2cm;
            width: 6.2cm;
            text-align: left;
            font-weight: bold;
        }

        /* 2. CUSTOMER AREA (NAMA PT TETAP, ALAMAT DIRAPIKAN SESUAI GARIS TITIK-TITIK) */
        .customer-area {
            position: absolute;
            top: 2.0cm;
            left: 12.8cm;
            width: 7.4cm;
        }
        .customer-name {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 0.55cm;
        }
        .customer-address {
            font-size: 8pt;
            margin-top: 0.12cm;
            line-height: 0.55cm;
            word-wrap: break-word;
        }

        /* 3. NOMOR PO & NO. SURAT JALAN (DITURUNKAN SEDIKIT BIAR PAS TITIK DUA) */
        .header-info {
            position: absolute;
            top: 3.1cm;
            left: 3.8cm;
            font-weight: bold;
            font-size: 9.5pt;
        }
        .header-row {
            height: 0.65cm;
            line-height: 0.65cm;
        }

        /* 4. DAFTAR BARANG (DITURUNKAN MASUK KE KOTAK BESAR DI BAWAH HEADER TABEL) */
        .content-area {
            position: absolute;
            top: 5.7cm;
            left: 0.5cm;
            width: 20cm;
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
                        <td style="width: 10%; text-align: right; padding-right: 4px; font-size: 10.5pt;">{{ number_format($item->qty_delivery) }}</td>
                        <td style="width: 7%; text-align: left; font-size: 9pt;">PCS</td>
                        <td style="width: 45%; text-align: left; padding-left: 0.4cm; line-height: 1.25;">
                            <div>{{ $item->part_no }}</div>
                            @if($pName)
                                <div style="font-size: 8.5pt; font-weight: normal;">{{ $pName }}</div>
                            @endif
                        </td>
                        <td style="width: 16%;"></td>
                        <td style="width: 22%;"></td>
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