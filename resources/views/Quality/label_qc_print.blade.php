<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>QC Label Partial - {{ $part_no }}</title>
    <style>
        @page { size: A4; margin: 0.5cm; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #fff; }
        .label-box { border: 3px solid #000; width: 14.5cm; height: auto; box-sizing: border-box; }
        
        /* HEADER COIL IDENTIFICATION */
        .top-header { display: flex; border-bottom: 3px solid #000; }
        .ok-side { width: 100px; border-right: 3px solid #000; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #10b981; color: #fff; padding: 10px 0; }
        .ok-side .big-ok { font-size: 32pt; font-weight: 900; margin: 0; line-height: 1; }
        .title-side { flex-grow: 1; text-align: center; padding: 10px; display: flex; flex-direction: column; justify-content: center; }
        .title-side .main-title { font-size: 16pt; font-weight: 900; letter-spacing: 1px; margin: 0; }
        
        /* STRIP VALUE */
        .receiving-bar { border-bottom: 3px solid #000; text-align: center; font-weight: 900; font-size: 12pt; padding: 6px 0; background: #0f172a; color: #fff; text-transform: uppercase; letter-spacing: 1px; }
        
        /* MIDDLE CONFIGURATION */
        .middle-section { display: flex; border-bottom: 3px solid #000; min-height: 170px; }
        .qty-side { width: 42%; border-right: 3px solid #000; padding: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f8fafc; }
        .qty-header { font-weight: 900; font-size: 15pt; border-bottom: 3px double #000; width: 100%; text-align: center; padding-bottom: 6px; margin-bottom: 12px; color: #000; }
        .qr-container { width: 115px; height: 115px; border: 1px solid #000; display: flex; align-items: center; justify-content: center; background: #fff; padding: 3px; }
        .qr-container img { width: 100%; height: 100%; }
        
        /* DATA RECAP SIDE */
        .qc-side { width: 58%; display: flex; flex-direction: column; }
        .qc-header-row { display: flex; border-bottom: 2px solid #000; height: 55px; background: #fff; }
        .pallet-no { width: 50%; border-right: 2px solid #000; padding: 6px; font-size: 9pt; font-weight: bold; text-align: center; display: flex; flex-direction: column; justify-content: center; }
        .qc-check-title { width: 50%; padding: 6px; font-size: 10pt; font-weight: 900; text-align: center; background: #e2e8f0; display: flex; flex-direction: column; justify-content: center; border-left: 1px solid #000; }
        
        .qc-table { width: 100%; border-collapse: collapse; flex-grow: 1; }
        .qc-table th { border: 1px solid #000; font-size: 8pt; padding: 5px; background: #f1f5f9; text-align: center; font-weight: 900; text-transform: uppercase; }
        .qc-table td { border: 1px solid #000; padding: 6px; text-align: center; font-weight: 800; font-size: 11pt; color: #000; }
        
        /* FOOTER BRAND DATA */
        .footer-data { display: flex; flex-direction: column; }
        .data-row { border-bottom: 2px solid #000; padding: 8px 6px; font-size: 13pt; font-weight: 900; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; }
        .data-row.part-hero { background: #f0f4ff; color: #4361ee; font-size: 16pt; font-family: 'Courier New', Courier, monospace; }
        
        .meta-info-container { background: #f8fafc; padding: 6px 12px; }
        .meta-info { display: flex; justify-content: space-between; font-size: 8pt; font-weight: bold; color: #475569; padding: 2px 0; }
    </style>
</head>
<body>
    <div class="label-box">
        <div class="top-header">
            <div class="ok-side">
                <span style="font-size: 9px; font-weight: 900; letter-spacing: 1px; margin-bottom: 2px;">PASSED</span>
                <span class="big-ok">OK</span>
            </div>
            <div class="title-side">
                <span class="main-title">PRODUCT IDENTIFICATION LABEL</span>
                <span style="font-size: 10pt; font-weight: 800; margin-top: 4px; color: #475569;">PT ASALTA MANDIRI AGUNG</span>
            </div>
        </div>
        
        <div class="receiving-bar">
            PROCESS ORIGIN: {{ isset($origin) ? strtoupper($origin) : 'STAMPING SECTOR' }}
        </div>
        
        <div class="middle-section">
            <div class="qty-side">
                <div class="qty-header">QTY: {{ number_format($qty) }} PCS</div>
                <div class="qr-container">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ $qr_content }}" alt="QR BARCODE">
                </div>
            </div>
            
            <div class="qc-side">
                <div class="qc-header-row">
                    <div class="pallet-no">
                        <span style="color: #64748b; font-size: 8px; uppercase font-weight:800;">INSPECTION TYPE</span>
                        <span style="font-size: 12pt; font-weight: 900; color: #10b981;">PARTIAL</span>
                    </div>
                    <div class="qc-check-title">
                        <span style="font-size: 8px; color: #475569; font-weight:800;">VERIFIED OFFICER</span>
                        <span style="font-size: 10pt; font-weight: 900; text-transform: uppercase;">{{ request('inspector_name') ?? 'QC GATE SIGN' }}</span>
                    </div>
                </div>
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th>LOT PRODUCTION</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-family: monospace; font-size: 12pt;">{{ date('ymd') }}</td>
                            <td style="color: #10b981; font-weight: 900; font-size: 14pt;">RELEASED</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="footer-data">
            <div class="data-row part-hero">{{ $part_no }}</div>
            <div class="data-row" style="font-size: 11pt; color: #334155;">{{ $part->part_name ?? 'COMPONENT PIECE PART' }}</div>
            <div class="data-row" style="font-size: 10pt; background: #fff; border-bottom: none;">
                BATCH NUMBER: <span style="font-family: monospace; font-weight: 900;">{{ $no_sj }}</span>
            </div>
            
            <div class="meta-info-container" style="border-top: 2px solid #000;">
                <div class="meta-info">
                    <span>SYSTEM IDENTIFICATION: GATE_CONTROL_v6.0</span>
                    <span>INSPECTED: {{ date('d-m-Y H:i') }}</span>
                </div>
                <div class="meta-info" style="border-top: 1px dashed #cbd5e1; margin-top: 2px; padding-top: 2px;">
                    <span>PRINTED LOG: SERVER_STACK</span>
                    <span>STATUS RECORD: OK SUCCESS</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            window.print();
            
            // Mengunci fungsi penutupan tab rill, tab hanya menutup setelah dialog print selesai direspon
            window.onafterprint = function() {
                window.close();
            };
        });
    </script>
</body>
</html>