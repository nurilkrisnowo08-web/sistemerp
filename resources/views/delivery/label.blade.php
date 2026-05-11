<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Label Delivery - {{ $no_sj }}</title>
    <style>
        @page {
            size: A4;
            margin: 0.5cm; /* Margin diperkecil agar muat lebih banyak rill */
        }
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 10px;
            background-color: #fff;
        }
        /* ✨ Grid Layout: 2 Kolom per baris rill */
        .label-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .label-box {
            border: 2px solid #000; /* Border lebih tebal agar jelas pas dipotong */
            padding: 0;
            width: 100%;
            height: 380px; /* Kunci tinggi agar kotak seragam rill */
            position: relative;
            margin-bottom: 5px;
            overflow: hidden;
        }
        .header {
            border-bottom: 2px solid #000;
            padding: 8px;
            text-align: center;
            font-weight: 900;
            font-size: 13pt;
            letter-spacing: 1px;
        }
        .table-label {
            width: 100%;
            border-collapse: collapse;
        }
        .table-label td {
            border-bottom: 1.5px solid #000;
            padding: 6px 10px;
            font-size: 10pt;
            height: 32px;
            vertical-align: middle;
        }
        .label-title {
            width: 35%;
            font-weight: bold;
            border-right: 2px solid #000;
            background-color: #f9f9f9;
        }
        .label-value {
            width: 65%;
            text-transform: uppercase;
            font-weight: 800;
        }
        .bottom-section {
            display: flex;
            width: 100%;
            height: 120px; /* Tinggi area bawah rill */
        }
        .ok-box {
            width: 55%;
            border-right: 2px solid #000;
            display: flex;
            flex-direction: column;
        }
        .remark-box {
            width: 45%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .ok-text {
            font-size: 45pt; /* Ukuran OK diperbesar sesuai foto rill */
            font-weight: 900;
            margin: 0;
            line-height: 1;
        }
        .small-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }
        .small-table td {
            font-size: 8.5pt;
            border: none;
            border-bottom: 1px solid #000;
            padding: 3px 8px;
            font-weight: bold;
        }
        .small-table tr:last-child td {
            border-bottom: none;
        }
        @media print {
            .no-print { display: none; }
            .label-box { page-break-inside: avoid; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="label-grid">
        @foreach($items as $item)
        <div class="label-box">
            <div class="header">
                PT ASALTA MANDIRI AGUNG
            </div>
            <table class="table-label">
                <tr>
                    <td class="label-title">CUSTOMER</td>
                    <td class="label-value">{{ $customer->name ?? $sj->customer_code }}</td>
                </tr>
                <tr>
                    <td class="label-title">PART NUMBER</td>
                    <td class="label-value" style="font-size: 12pt;">{{ $item->part_no }}</td>
                </tr>
                <tr>
                    <td class="label-title">PART NAME</td>
                    <td class="label-value" style="font-size: 9pt;">{{ $item->part_name }}</td>
                </tr>
                <tr>
                    <td class="label-title">BLANK SIZE</td>
                    {{-- Ini otomatis mengambil dari Kamus RM rill --}}
                    <td class="label-value" style="font-size: 8.5pt; color: #000;">{{ $item->blank_size }}</td>
                </tr>
                <tr>
                    <td class="label-title">PROD DATE</td>
                    <td class="label-value">{{ date('d-m-Y', strtotime($item->created_at)) }}</td>
                </tr>
            </table>

            <div class="bottom-section">
                <div class="ok-box">
                    <table class="small-table">
                        <tr>
                            <td width="35%">QTY</td>
                            <td style="font-size: 15pt; font-weight: 900;">{{ number_format($item->qty_delivery) }} PCS</td>
                        </tr>
                        <tr>
                            <td>SHIFT</td>
                            <td>{{ $item->shift ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Q1 DATE</td>
                            <td style="border-bottom: 1px solid #000;"></td>
                        </tr>
                        <tr>
                            <td>PPC DATE</td>
                            <td></td>
                        </tr>
                    </table>
                </div>
                <div class="remark-box">
                    <span style="font-size: 9pt; font-weight: bold; margin-bottom: 5px;">REMARK</span>
                    <h1 class="ok-text">OK</h1>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</body>
</html>