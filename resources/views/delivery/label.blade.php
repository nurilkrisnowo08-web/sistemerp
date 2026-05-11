<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Product Identification - {{ $no_sj }}</title>
    <style>
        @page {
            size: A4;
            margin: 0.5cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 10px;
            background-color: #fff;
        }
        .label-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .label-box {
            border: 2px solid #000;
            width: 100%;
            height: auto;
            position: relative;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        /* Atas: OK & Judul */
        .top-header {
            display: flex;
            border-bottom: 2px solid #000;
        }
        .ok-side {
            width: 80px;
            border-right: 2px solid #000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 5px;
        }
        .ok-side .small-code { font-size: 8pt; font-weight: bold; margin-bottom: 5px; }
        .ok-side .big-ok { font-size: 28pt; font-weight: 900; margin: 0; }
        
        .title-side {
            flex-grow: 1;
            text-align: center;
            padding: 5px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .title-side .main-title { font-size: 14pt; font-weight: 900; letter-spacing: 1px; margin-bottom: 5px; }
        .title-side .sub-info { font-size: 9pt; font-weight: bold; margin-bottom: 3px; }

        /* Baris Receiving Area */
        .receiving-bar {
            background-color: #fff;
            border-bottom: 2px solid #000;
            text-align: center;
            font-weight: 900;
            font-size: 10pt;
            padding: 2px 0;
        }

        /* Area Tengah: Qty & QC Check */
        .middle-section {
            display: flex;
            border-bottom: 2px solid #000;
            min-height: 160px;
        }
        .qty-side {
            width: 40%;
            border-right: 2px solid #000;
            padding: 5px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .qty-header { width: 100%; font-weight: 900; font-size: 11pt; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 10px; }
        
        /* ✨ QR CODE STYLING */
        .qr-container { 
            width: 100px; 
            height: 100px; 
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 5px;
        }
        .qr-container img { width: 90px; height: 90px; }
        .qr-label { font-size: 7pt; font-weight: bold; }

        .qc-side {
            width: 60%;
            display: flex;
            flex-direction: column;
        }
        .qc-header-row { display: flex; border-bottom: 1px solid #000; height: 50px; }
        .pallet-no { width: 50%; border-right: 1px solid #000; padding: 3px; font-size: 9pt; font-weight: bold; text-align: center; }
        .qc-check-title { width: 50%; padding: 3px; font-size: 9pt; font-weight: bold; text-align: center; }
        
        .qc-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .qc-table th { border: 1px solid #000; font-size: 8pt; padding: 2px; background: #eee; }
        .qc-table td { border: 1px solid #000; height: 35px; }

        /* Area Bawah: Data Part */
        .footer-data {
            display: flex;
            flex-direction: column;
        }
        .data-row {
            border-bottom: 1px solid #000;
            padding: 4px 10px;
            font-size: 11pt;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
        }
        .data-row:last-child { border-bottom: none; }
        .meta-info {
            display: flex;
            justify-content: space-between;
            font-size: 7.5pt;
            padding: 4px 10px;
            font-weight: bold;
        }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="label-grid">
        @foreach($items as $item)
        <div class="label-box">
            <div class="top-header">
                <div class="ok-side">
                    <span class="small-code">10440</span>
                    <span class="big-ok">OK</span>
                </div>
                <div class="title-side">
                    <span class="main-title">PRODUCT IDENTIFICATION</span>
                    <span class="sub-info">Del. Date : {{ date('d-m-Y', strtotime($item->created_at)) }}</span>
                    <span class="sub-info" style="font-size: 8.5pt;">{{ $no_sj }}</span>
                </div>
            </div>

            <div class="receiving-bar">Receiving Area</div>

            <div class="middle-section">
                <div class="qty-side">
                    <div class="qty-header">Qty: {{ number_format($item->qty_delivery) }} PCS</div>
                    
                    <div class="qr-container">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $item->part_no }}" alt="QR Part">
                    </div>
                    <div class="qr-label">{{ $item->part_no }}</div>
                </div>

                <div class="qc-side">
                    <div class="qc-header-row">
                        <div class="pallet-no">Pallet No.<br><span style="font-size: 12pt;">1 / 1</span></div>
                        <div class="qc-check-title">QC Check</div>
                    </div>
                    <table class="qc-table">
                        <thead>
                            <tr>
                                <th>PCS</th>
                                <th>QC</th>
                                <th>WH1</th>
                                <th>WH2</th>
                                <th>WH3</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td></td><td></td><td></td><td></td><td></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="footer-data">
                <div class="data-row" style="background-color: #f0f0f0;">{{ $item->part_no }}</div>
                <div class="data-row">{{ $item->part_name }}</div>
                <div class="data-row" style="font-size: 10pt;">LOT : {{ date('ymd', strtotime($item->created_at)) }}</div>
                
                <div class="meta-info">
                    <span>Created : ASALTA / {{ date('d-m-Y') }}</span>
                </div>
                <div class="meta-info" style="border-top: 0.5px solid #000; padding-top: 2px;">
                    <span>Printed : {{ date('d-m-Y H:i:s') }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</body>
</html>