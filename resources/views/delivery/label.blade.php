<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Label Delivery - {{ $no_sj }}</title>
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        /* ✨ Grid Layout: 2 Kolom per baris rill */
        .label-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .label-box {
            border: 1.5px solid #000;
            padding: 0;
            width: 100%;
            height: auto;
            position: relative;
        }
        .header {
            border-bottom: 1.5px solid #000;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 11pt;
        }
        .header img {
            width: 20px;
            margin-right: 10px;
        }
        .table-label {
            width: 100%;
            border-collapse: collapse;
        }
        .table-label td {
            border-bottom: 1px solid #000;
            padding: 4px 8px;
            font-size: 9pt;
            height: 25px;
        }
        .label-title {
            width: 30%;
            font-weight: bold;
            border-right: 1px solid #000;
        }
        .label-value {
            width: 70%;
            text-transform: uppercase;
            font-weight: bold;
        }
        .bottom-section {
            display: flex;
            width: 100%;
            height: 80px;
        }
        .ok-box {
            width: 50%;
            border-right: 1px solid #000;
            display: flex;
            flex-direction: column;
        }
        .remark-box {
            width: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .ok-text {
            font-size: 28pt;
            font-weight: 900;
            margin: 0;
        }
        .small-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }
        .small-table td {
            font-size: 7pt;
            border: none;
            border-bottom: 0.5px solid #000;
            padding: 2px 5px;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="label-grid">
        @foreach($items as $item)
        {{-- Loop ini akan membuat kotak label sebanyak item yang dikirim rill --}}
        <div class="label-box">
            <div class="header">
                {{-- Tambahkan logo kalau ada rill --}}
                PT ASALTA MANDIRI AGUNG
            </div>
            <table class="table-label">
                <tr>
                    <td class="label-title">CUSTOMER</td>
                    <td class="label-value">{{ $customer->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label-title">PART NUMBER</td>
                    <td class="label-value" style="font-size: 11pt;">{{ $item->part_no }}</td>
                </tr>
                <tr>
                    <td class="label-title">PART NAME</td>
                    <td class="label-value">{{ $item->part_name }}</td>
                </tr>
                <tr>
                    <td class="label-title">BLANK SIZE</td>
                    <td class="label-value">{{ $item->blank_size ?? '-' }}</td>
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
                            <td width="30%">QTY</td>
                            <td style="font-size: 12pt; font-weight: 900;">{{ number_format($item->qty_delivery) }} PCS</td>
                        </tr>
                        <tr>
                            <td>SHIFT</td>
                            <td>{{ $item->shift ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Q1 DATE</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>PPC DATE</td>
                            <td></td>
                        </tr>
                    </table>
                </div>
                <div class="remark-box">
                    <span style="font-size: 8pt; font-weight: bold;">REMARK</span>
                    <h1 class="ok-text">OK</h1>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</body>
</html>