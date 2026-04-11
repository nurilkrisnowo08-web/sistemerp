<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OFFICIAL_PURCHASE_ORDER_{{ $po->no_po_supplier }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap');

        :root {
            --corporate-blue: #001a41;
            --border-color: #1e293b;
            --soft-bg: #f8fafc;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            color: #000; 
            margin: 0; 
            padding: 0; 
            background: #f1f5f9; 
            -webkit-print-color-adjust: exact;
        }
        
        .document-page { 
            width: 210mm; 
            min-height: 297mm; 
            margin: 20px auto; 
            padding: 20mm; 
            background: #fff;
            position: relative; 
            box-sizing: border-box; 
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        /* Watermark Modern */
        .document-page::before {
            content: "ORIGINAL PROCUREMENT"; 
            position: absolute; 
            top: 50%; 
            left: 50%; 
            font-size: 60px;
            font-weight: 800;
            color: rgba(0,0,0,0.02);
            transform: translate(-50%, -50%) rotate(-45deg);
            z-index: 0;
            pointer-events: none;
        }

        /* Header Construction */
        .header { 
            display: flex; 
            justify-content: space-between;
            align-items: center; 
            border-bottom: 3.5px solid var(--corporate-blue); 
            padding-bottom: 20px; 
            margin-bottom: 25px; 
        }

        .logo-section { display: flex; align-items: center; }
        .logo-section img { 
            height: 65px; 
            width: auto;
            margin-right: 20px;
        }
        
        .company-identity h1 { margin: 0; font-size: 20px; font-weight: 800; color: var(--corporate-blue); letter-spacing: -0.5px; }
        .company-identity p { margin: 2px 0; font-size: 10px; color: #475569; line-height: 1.4; font-weight: 600; }

        .document-title-box { text-align: right; }
        .document-title-box h2 { margin: 0; font-size: 24px; font-weight: 800; color: #cbd5e1; text-transform: uppercase; line-height: 1; }
        .po-number-badge { 
            font-family: 'JetBrains Mono', monospace; 
            font-size: 12px; 
            font-weight: 700; 
            background: var(--corporate-blue); 
            color: #fff; 
            padding: 6px 14px; 
            margin-top: 10px; 
            display: inline-block;
            border-radius: 4px;
        }

        /* Registry Info Section */
        .registry-container { 
            display: flex; 
            margin-bottom: 30px; 
            gap: 20px;
        }
        
        .registry-card { 
            flex: 1;
            border: 1.5px solid var(--border-color); 
            padding: 15px; 
            border-radius: 6px;
        }

        .data-label { font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 5px; display: block; }
        .data-value { font-size: 13px; font-weight: 700; color: #000; }
        .data-value.project { color: #e11d48; font-size: 14px; }

        .opening-statement { 
            font-size: 12px; 
            line-height: 1.6; 
            margin-bottom: 20px; 
            color: #1e293b;
        }

        /* Industrial Table */
        .procurement-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; z-index: 1; position: relative; }
        .procurement-table th { 
            background: #f8fafc; 
            border: 1.5px solid var(--border-color); 
            padding: 12px 10px; 
            font-size: 10px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            text-align: center;
        }
        .procurement-table td { 
            border: 1.5px solid var(--border-color); 
            padding: 15px 12px; 
            font-size: 12px; 
            vertical-align: top; 
        }

        .item-code { font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 13px; color: var(--corporate-blue); }
        .item-spec { font-size: 10px; color: #64748b; margin-top: 5px; font-weight: 600; line-height: 1.4; }
        
        /* ✨ Component Details Section ✨ */
        .parts-registry { 
            margin-top: 12px; 
            background: var(--soft-bg); 
            border: 1px solid #e2e8f0; 
            padding: 10px; 
            border-radius: 6px; 
        }
        .parts-registry-title { 
            font-size: 9px; 
            font-weight: 800; 
            color: #475569; 
            text-transform: uppercase; 
            display: block; 
            margin-bottom: 6px; 
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
        }
        .part-entry { font-size: 10px; font-weight: 700; color: #1e293b; margin-bottom: 3px; display: block; }
        .part-entry span { font-weight: 500; color: #64748b; font-family: 'Inter'; }

        .qty-column { font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 16px; text-align: center; vertical-align: middle !important; }

        /* Legal Notice */
        .legal-notice { 
            border: 1px solid #e2e8f0; 
            padding: 15px; 
            background: #f8fafc; 
            font-size: 10px; 
            margin-bottom: 40px; 
            border-radius: 8px; 
        }
        .legal-notice b { color: var(--corporate-blue); display: block; margin-bottom: 6px; text-transform: uppercase; }
        .legal-notice ol { margin: 0; padding-left: 18px; color: #475569; }

        /* Authorization Section */
        .auth-section { display: flex; justify-content: space-between; margin-top: 30px; }
        .auth-box { width: 220px; text-align: center; }
        .auth-signature-space { height: 75px; border-bottom: 1.5px solid var(--border-color); margin-bottom: 10px; }
        .auth-name { font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 2px; }
        .auth-role { font-size: 10px; font-weight: 600; color: #64748b; text-transform: uppercase; }

        .document-footer { 
            position: absolute; 
            bottom: 10mm; 
            left: 0; 
            width: 100%; 
            text-align: center; 
            font-size: 9px; 
            color: #94a3b8; 
            font-weight: 600;
        }

        @media print {
            body { background: #fff; }
            .document-page { margin: 0; border: none; box-shadow: none; width: 100%; padding: 10mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="document-page">
        <div class="header">
            <div class="logo-section">
                <img src="{{ asset('admin/img/Logo-asalta.png') }}" alt="PT ASALTA LOGO">
                <div class="company-identity">
                    <h1>PT. ASALTA MANDIRI AGUNG</h1>
                    <p>Industrial Excellence & Supply Chain Management</p>
                    <p>Kawasan Industri KIIC, Lot C-7, Karawang, Jawa Barat 41361</p>
                    <p>P: (0267) 889-1234 | E: procurement.registry@asalta.co.id</p>
                </div>
            </div>
            <div class="document-title-box">
                <h2>Purchase Order</h2>
                <div class="po-number-badge">{{ $po->no_po_supplier }}</div>
            </div>
        </div>

        <div class="registry-container">
            <div class="registry-card">
                <span class="data-label">Vendor / Supply Partner:</span>
                <div class="data-value">{{ strtoupper($po->supplier_name) }}</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 4px; font-weight: 600;">Industrial Supply Chain Verified Entity</div>
            </div>
            <div class="registry-card" style="max-width: 250px;">
                <span class="data-label">Project Identification:</span>
                <div class="data-value project">{{ $po->items->first()->project_name ?? 'GENERAL PROCUREMENT' }}</div>
                
                <span class="data-label" style="margin-top: 12px;">Emission Date:</span>
                <div class="data-value">{{ date('d M Y', strtotime($po->created_at)) }}</div>
            </div>
        </div>

        <div class="opening-statement">
            Berdasarkan instruksi kebutuhan material produksi PT. ASALTA MANDIRI AGUNG, dokumen ini diterbitkan sebagai perintah kerja pengadaan material. Seluruh rincian teknis dan komponen assignment yang tercantum bersifat mengikat:
        </div>

        <table class="procurement-table">
            <thead>
                <tr>
                    <th width="40">Pos</th>
                    <th>Nomenclature & Technical Specification</th>
                    <th width="120">Net Quantity</th>
                    <th width="80">UoM</th>
                </tr>
            </thead>
            <tbody>
                @foreach($po->items as $index => $item)
                <tr>
                    <td align="center" style="font-weight: 700; color: #64748b;">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div class="item-code">{{ $item->alias_real ?? $item->material_code }}</div>
                        <div class="item-spec">
                            Material Type: {{ $item->material_type ?? 'RAW MATERIAL' }} <br>
                            Dimension Geometry: <span style="color: #e11d48; font-weight: 800;">{{ $item->thickness ?? '0.0' }} X {{ $item->size ?? '0.0' }}</span>
                        </div>

                        @if(isset($item->target_parts) && $item->target_parts->count() > 0)
                        <div class="parts-registry">
                            <span class="parts-registry-title">Technical Assignment / Component Details:</span>
                            @foreach($item->target_parts as $tp)
                                <div class="part-entry">• {{ $tp->material_code }} <span>({{ $tp->material_name }})</span></div>
                            @endforeach
                        </div>
                        @endif
                    </td>
                    <td class="qty-column">{{ number_format($item->qty_order) }}</td>
                    <td align="center" style="font-weight: 700; color: #475569; vertical-align: middle;">PCS</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="legal-notice">
            <b>Standard Procurement Terms & Conditions:</b>
            <ol>
                <li>Dokumen pengiriman (Surat Jalan) asli wajib mencantumkan Nomor PO resmi untuk verifikasi sistem.</li>
                <li>Setiap unit material wajib melalui tahap inspeksi Quality Control pada Gate Integrated KIIC.</li>
                <li>Operasional penerimaan logistik terjadwal pada pukul 08:00 - 16:00 WIB (Senin - Jumat).</li>
                <li>Ketidaksesuaian spesifikasi atau dimensi akan mengakibatkan penolakan otomatis oleh sistem.</li>
            </ol>
        </div>

        <div class="auth-section">
            <div class="auth-box">
                <span class="data-label">Ordered & Verified By,</span>
                <div class="auth-signature-space"></div>
                <div class="auth-name">Procurement Division</div>
                <div class="auth-role">Purchasing Officer</div>
            </div>
            <div class="auth-box">
                <span class="data-label">Validated & Approved By,</span>
                <div class="auth-signature-space"></div>
                <div class="auth-name">Operations Management</div>
                <div class="auth-role">Director of Supply Chain</div>
            </div>
        </div>

        <div class="document-footer">
            PT. ASALTA MANDIRI AGUNG - INTEGRATED MANUFACTURING SYSTEM V2.0
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin: 30px 0;">
        <button onclick="window.print()" style="padding: 16px 60px; background: var(--corporate-blue); color: #fff; border: none; border-radius: 50px; font-weight: 800; cursor: pointer; font-size: 14px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);">
            <i class="fas fa-print"></i> AUTHORIZE & PRINT DOCUMENT
        </button>
    </div>

</body>
</html>