<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak SJ - {{ $no_sj }}</title> 
    <style>
        /* 1. SETTING KERTAS 1/2 F4 LANDSCAPE (21.5 x 16.5 cm) */
        @page { 
            size: 21.5cm 16.5cm; 
            margin: 0; 
        }

        body { 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 11pt; 
            margin: 0; 
            padding: 0; 
            color: #000; 
            -webkit-print-color-adjust: exact;
        }

        /* 2. POSISI TANGGAL (BOGOR, ...) */
        .date-area {
            position: absolute;
            top: 0.8cm; 
            right: 1.5cm;
            font-weight: bold;
        }

        /* 3. POSISI CUSTOMER (DI BAWAH "KEPADA YTH") */
        .customer-area {
            position: absolute;
            top: 3.0cm; /* GESER KE BAWAH: Agar pas di bawah teks Kepada Yth */
            right: 1.2cm;
            width: 9.5cm;
            line-height: 1.3;
        }
        .customer-name { font-size: 11.5pt; font-weight: bold; text-transform: uppercase; }
        .customer-address { font-size: 9pt; margin-top: 4px; font-weight: bold; }

        /* 4. POSISI NOMOR PO & SJ (SISI KIRI) */
        .header-info {
            position: absolute;
            top: 3.0cm; 
            left: 5.2cm;
            font-weight: bold;
        }
        .header-row { height: 0.7cm; vertical-align: middle; }

        /* 5. POSISI DAFTAR BARANG (PAS DI GARIS TABEL) */
        .content-area {
            position: absolute;
            top: 7.7cm; 
            width: 100%;
        }
        .item-table { width: 100%; border-collapse: collapse; }
        .item-table td {
            height: 0.94cm; /* Tinggi baris menyesuaikan jarak garis fisik */
            vertical-align: middle;
            border: none !important;
            font-weight: bold;
        }

        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body onload="window.print()">

    {{-- NAVIGASI LAYAR --}}
    <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 9999; background: #fff; padding: 12px; border: 2px solid #4e73df; border-radius: 8px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #4e73df; color: #fff; border: none; font-weight: bold; cursor: pointer; border-radius: 5px;">🖨️ CETAK SEKARANG</button>
        <a href="{{ route('delivery.index') }}" style="display: block; text-align: center; text-decoration: none; color: #333; margin-top: 8px; font-weight: bold; font-size: 9pt;">← KEMBALI</a>
    </div>

    {{-- DATA TANGGAL --}}
    <div class="date-area">
        {{ date('d F Y', strtotime($sj->created_at)) }}
    </div>

    {{-- DATA CUSTOMER (DI BAWAH KEPADA YTH) --}}
    <div class="customer-area">
        <div class="customer-name">{{ $customer->name ?? $sj->customer_code }}</div>
        <div class="customer-address">
            {{ $customer->address ?? 'ALAMAT TIDAK DITEMUKAN' }}
        </div>
    </div>

    {{-- DATA PO & SJ --}}
    <div class="header-info">
        <div class="header-row">: {{ $po->po_number ?? '-' }}</div>
        <div class="header-row" style="margin-top: 2px;">: {{ $no_sj }}</div>
    </div>

    {{-- DATA BARANG --}}
    <div class="content-area">
        <table class="item-table">
            <tbody>
                @foreach($items as $item)
                <tr>
                    {{-- QTY --}}
                    <td style="width: 14%; text-align: center; font-size: 13pt;">
                        {{ number_format($item->qty_delivery) }}
                    </td>
                    {{-- UNIT --}}
                    <td style="width: 10%; text-align: center;">PCS</td>
                    {{-- PART NUMBER --}}
                    <td style="width: 45%; text-align: left; padding-left: 1.5cm;">
                        {{ $item->part_no }}
                    </td>
                    <td style="width: 13%;"></td>
                    <td style="width: 18%;"></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- AUTO-BACK SETELAH PRINT --}}
    <script>
        window.onafterprint = function() {
            window.location.href = "{{ route('delivery.index') }}";
        };
    </script>
</body>
</html>