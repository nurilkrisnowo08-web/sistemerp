<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>QC Label Partial - {{ $part_no }}</title>
    <style>
        @page { size: A4; margin: 0.5cm; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #fff; }
        .label-box { border: 3px solid #000; width: 14.5cm; height: auto; box-sizing: border-box; }
        .top-header { display: flex; border-bottom: 3px solid #000; }
        .ok-side { width: 90px; border-right: 3px solid #000; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #10b981; color: #fff; }
        .ok-side .big-ok { font-size: 32pt; font-weight: 900; margin: 0; }
        .title-side { flex-grow: 1; text-align: center; padding: 10px; display: flex; flex-direction: column; justify-content: center; }
        .title-side .main-title { font-size: 16pt; font-weight: 900; letter-spacing: 1px; }
        .receiving-bar { border-bottom: 3px solid #000; text-align: center; font-weight: 900; font-size: 12pt; padding: 4px 0; background: #0f172a; color: #fff; }
        .middle-section { display: flex; border-bottom: 3px solid #000; min-height: 160px; }
        .qty-side { width: 40%; border-right: 3px solid #000; padding: 10px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .qty-header { font-weight: 900; font-size: 14pt; border-bottom: 2px solid #000; width: 100%; text-align: center; padding-bottom: 5px; margin-bottom: 10px; }
        .qr-container { width: 110px; height: 110px; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; }
        .qr-container img { width: 100px; height: 100px; }
        .qc-side { width: 60%; display: flex; flex-direction: column; }
        .qc-header-row { display: flex; border-bottom: 2px solid #000; height: 50px; }
        .pallet-no { width: 50%; border-right: 2px solid #000; padding: 5px; font-size: 10pt; font-weight: bold; text-align: center; }
        .qc-check-title { width: 50%; padding: 5px; font-size: 10pt; font-weight: bold; text-align: center; line-height: 40px; background: #e2e8f0; }
        .qc-table { width: 100%; border-collapse: collapse; }
        .qc-table th { border: 1px solid #000; font-size: 9pt; padding: 4px; background: #eee; text-align: center; }
        .qc-table td { border: 1px solid #000; height: 40px; text-align: center; font-weight: bold; font-size: 14px; }
        .footer-data { display: flex; flex-direction: column; }
        .data-row { border-bottom: 2px solid #000; padding: 6px; font-size: 12pt; font-weight: 900; text-align: center; text-transform: uppercase; }
        .data-row:last-child { border-bottom: none; }
        .meta-info { display: flex; justify-content: space-between; font-size: 8pt; padding: 4px 10px; font-weight: bold; }
    </style>
</head>
<body onload="window.print(); window.close();">
    <div class="label-box">
        <div class="top-header">
            <div class="ok-side"><span style="font-size:8pt;font-weight:bold;">PASSED</span><span class="big-ok">QC</span></div>
            <div class="title-side">
                <span class="main-title">QUALITY INSPECTION LABEL</span>
                <span style="font-size:10pt; font-weight:bold; margin-top:5px;">Inspected Date : {{ date('d-m-Y') }}</span>
            </div>
        </div>
        <div class="receiving-bar">APPROVED STACK (FINISHED GOODS)</div>
        <div class="middle-section">
            <div class="qty-side">
                <div class="qty-header">Qty: {{ number_format($qty) }} PCS</div>
                <div class="qr-container">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ $qr_content }}" alt="QR">
                </div>
            </div>
            <div class="qc-side">
                <div class="qc-header-row">
                    <div class="pallet-no">Status Box<br><span style="font-size:12pt;font-weight:900;color:#10b981;">PARTIAL</span></div>
                    <div class="qc-check-title">QC GATE SIGN</div>
                </div>
                <table class="qc-table">
                    <thead>
                        <tr><th>PCS</th><th>INSPECTOR</th><th>STATUS</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ number_format($qty) }}</td>
                            <td style="font-size:10px;">RELEASED</td>
                            <td style="color:#10b981;">OK</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="footer-data">
            <div class="data-row" style="background: #f8fafc; color: #4361ee;">{{ $part_no }}</div>
            <div class="data-row">{{ $part->part_name ?? 'N/A' }}</div>
            <div class="data-row" style="font-size:10pt;">BATCH REF : {{ $no_sj }}</div>
            <div class="meta-info"><span>Verified By : SYSTEM_GATE</span><span>Date : {{ date('d-m-Y H:i:s') }}</span></div>
        </div>
    </div>
</body>
</html>