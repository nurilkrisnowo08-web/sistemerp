<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PO_{{ strtoupper(str_replace(['/', ' '], '_', $po->no_po_supplier)) }}_{{ date('Ymd', strtotime($po->created_at)) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark: #0f172a;
            --slate: #475569;
            --muted: #64748b;
            --border-thick: #0f172a;
            --border-thin: #cbd5e1;
            --primary: #1d4ed8;
            --stamp-blue: #1e40af;
        }

        * { box-sizing: border-box; }

        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            font-size: 10px;
            color: var(--dark);
            margin: 0;
            padding: 15px;
            background: #ffffff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .po-print-wrapper {
            max-width: 100%;
            margin: 0 auto;
        }

        /* 🔘 SCREEN TOOLBAR (TIDAK DICETAK) */
        .no-print-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            padding: 10px 18px;
            border-radius: 8px;
            margin-bottom: 16px;
            border: 1px solid #cbd5e1;
        }
        .btn-screen {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 11px;
            padding: 8px 18px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-close { background: #64748b; color: #fff; }
        .btn-print { background: #0f172a; color: #fff; }

        /* 🏛️ INDUSTRIAL COMPANY HEADER */
        .doc-header {
            display: table;
            width: 100%;
            border-bottom: 3.5px solid var(--border-thick);
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header-col { display: table-cell; vertical-align: middle; }
        
        .company-identity { width: 45%; }
        .company-identity h2 { margin: 0; font-size: 18px; font-weight: 900; letter-spacing: 0.5px; }
        .company-identity p { margin: 3px 0 0; font-size: 9px; color: var(--muted); font-weight: 600; line-height: 1.3; }

        .report-title-box { width: 30%; text-align: center; }
        .report-title-box h1 { margin: 0; font-size: 17px; font-weight: 900; letter-spacing: 1.5px; text-transform: uppercase; }
        .report-title-box span { font-size: 11px; font-weight: 800; color: var(--primary); font-family: 'JetBrains Mono', monospace; display: block; margin-top: 2px; }

        .doc-meta { width: 25%; text-align: right; font-size: 9px; line-height: 1.45; }
        .doc-meta b { color: var(--dark); font-weight: 800; }

        /* 📦 PO INFO CARDS */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 14px;
        }
        .info-box-left, .info-box-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            border: 1px solid var(--border-thin);
            padding: 10px 12px;
            border-radius: 6px;
            background: #fafafa;
        }
        .info-box-right {
            border-left: none;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
            font-size: 9.5px;
        }
        .info-row:last-child { margin-bottom: 0; }
        .info-label { display: table-cell; width: 34%; font-weight: 800; text-transform: uppercase; color: var(--muted); font-size: 8.5px; }
        .info-colon { display: table-cell; width: 4%; font-weight: 800; color: var(--muted); }
        .info-value { display: table-cell; width: 62%; font-weight: 700; color: var(--dark); }

        /* 📋 TABLE PO ITEMS */
        table.po-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid var(--border-thick);
            margin-bottom: 12px;
        }
        table.po-table th {
            border: 1px solid var(--border-thick);
            padding: 7px 6px;
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            background: #f1f5f9 !important;
            text-align: center;
        }
        table.po-table td {
            border: 1px solid var(--border-thin);
            padding: 7px 8px;
            vertical-align: middle;
            font-size: 10px;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* 📝 SYARAT & KETENTUAN */
        .terms-box {
            border: 1px solid var(--border-thin);
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 14px;
            background: #fcfcfc;
            font-size: 8.5px;
            line-height: 1.45;
        }
        .terms-box b { text-transform: uppercase; color: var(--dark); }
        .terms-box ol { margin: 3px 0 0 16px; padding: 0; }

        /* ✍️ SIGNATURE BOXES & STEMPEL */
        .doc-footer {
            display: table;
            width: 100%;
            page-break-inside: avoid;
            margin-top: 10px;
        }
        .sig-col {
            display: table-cell;
            width: 25%;
            text-align: center;
            vertical-align: top;
            padding: 0 4px;
        }
        .sig-box {
            border: 1px solid var(--border-thin);
            background: #ffffff;
            border-radius: 6px;
            padding: 6px;
            position: relative;
            height: 95px;
        }
        .sig-title { font-size: 8px; font-weight: 800; text-transform: uppercase; color: var(--muted); margin-bottom: 50px; }
        .sig-name { font-size: 8.5px; font-weight: 800; text-transform: uppercase; border-top: 1px solid var(--border-thick); padding-top: 3px; }

        .stamp-wrapper {
            position: absolute;
            top: 15px;
            left: 50%;
            transform: translateX(-50%) rotate(-8deg);
            width: 70px;
            height: 70px;
            border: 2px dashed var(--stamp-blue);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--stamp-blue);
            opacity: 0.82;
            pointer-events: none;
            background: rgba(255, 255, 255, 0.4);
        }
        .stamp-top { font-size: 4.5px; font-weight: 900; text-transform: uppercase; }
        .stamp-mid { font-size: 7px; font-weight: 900; border-top: 1px solid var(--stamp-blue); border-bottom: 1px solid var(--stamp-blue); padding: 1px 0; margin: 1px 0; width: 85%; }
        .stamp-bot { font-size: 5px; font-weight: 800; text-transform: uppercase; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>

    {{-- TOOLBAR ATAS (HANYA MUNCUL DI LAYAR MONITOR) --}}
    <div class="no-print no-print-bar">
        <div>
            <b>PURCHASE ORDER PREVIEW</b> — Dokumen Pemesanan Bahan Baku Supplier
        </div>
        <div>
            <button class="btn-screen btn-close" onclick="window.close()">TUTUP</button>
            <button class="btn-screen btn-print" onclick="window.print()">🖨️ CETAK PO (A4)</button>
        </div>
    </div>

    <div class="po-print-wrapper">
        {{-- KOP SURAT PERUSAHAAN --}}
        <div class="doc-header">
            <div class="header-col company-identity">
                <h2>PT ASALTA MANDIRI AGUNG</h2>
                <p>
                    Plant: Kawasan Industri Mitra Karawang, Jawa Barat<br>
                    Automotive Metal Stamping, Tooling & Sub-Assembly<br>
                    Telp: (0267) 863-XXXX | Email: purchasing@asalta.co.id
                </p>
            </div>
            <div class="header-col report-title-box">
                <h1>PURCHASE ORDER</h1>
                <span class="font-mono">{{ $po->no_po_supplier }}</span>
            </div>
            <div class="header-col doc-meta">
                FORM NO : <b>AMA-PUR-PO-01</b><br>
                PO DATE : <b>{{ date('d F Y', strtotime($po->created_at)) }}</b><br>
                PRINTED : {{ date('d/m/Y H:i') }}
            </div>
        </div>

        {{-- GRID DETAIL ENTITAS SUPPLIER & PROYEK --}}
        <div class="info-grid">
            <div class="info-box-left">
                <div class="info-row">
                    <span class="info-label">Vendor / Supplier</span>
                    <span class="info-colon">:</span>
                    <span class="info-value">{{ strtoupper($po->supplier_name) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Customer Ref</span>
                    <span class="info-colon">:</span>
                    <span class="info-value font-mono font-weight-bold">{{ strtoupper($po->customer_code) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Destination</span>
                    <span class="info-colon">:</span>
                    <span class="info-value">Gudang Raw Material (Plant Karawang)</span>
                </div>
            </div>
            <div class="info-box-right">
                <div class="info-row">
                    <span class="info-label">Status Dokumen</span>
                    <span class="info-colon">:</span>
                    <span class="info-value">
                        <b>{{ strtoupper($po->status) }}</b>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment Terms</span>
                    <span class="info-colon">:</span>
                    <span class="info-value">As Per Purchasing Agreement (30 Days)</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Delivery Note</span>
                    <span class="info-colon">:</span>
                    <span class="info-value font-weight-bold">Wajib Cantumkan No PO pada Surat Jalan</span>
                </div>
            </div>
        </div>

        {{-- TABEL ITEM SPESIFIKASI BAHAN BAKU --}}
        <table class="po-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Material / Alias Code</th>
                    <th width="25%">Spesifikasi Material</th>
                    <th width="20%">Ukuran (Thick x Size)</th>
                    <th width="15%">Qty Pesanan (Lbr)</th>
                    <th width="15%">Qty Diterima (Lbr)</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $totalQuota = 0;
                    $totalRec = 0;
                @endphp

                @forelse($items as $idx => $item)
                @php 
                    $totalQuota += $item->qty_order;
                    $totalRec += ($item->qty_received ?? 0);
                @endphp
                <tr>
                    <td class="text-center font-weight-bold">{{ $idx + 1 }}</td>
                    <td class="font-mono font-weight-bold" style="color: var(--primary);">
                        {{ $item->material_code }}
                    </td>
                    <td>
                        <b>{{ $item->material_type ?? ($item->spec_real ?? '-') }}</b>
                    </td>
                    <td class="font-mono small">
                        {{ $item->thickness ?? '' }} {{ !empty($item->thickness) && !empty($item->size) ? 'X' : '' }} {{ $item->size ?? '-' }}
                    </td>
                    <td class="text-right font-mono font-weight-bold">
                        {{ number_format($item->qty_order) }}
                    </td>
                    <td class="text-right font-mono font-weight-bold" style="color: #047857;">
                        {{ number_format($item->qty_received ?? 0) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i>Tidak ada rincian material yang terdaftar pada Purchase Order ini.</i>
                    </td>
                </tr>
                @endforelse

                @if(count($items) > 0)
                <tr style="background: #f8fafc; font-weight: 800;">
                    <td colspan="4" class="text-right" style="padding-right: 12px; font-size: 9.5px;">TOTAL ALOKASI KUOTA MATERIAL:</td>
                    <td class="text-right font-mono font-weight-bold" style="font-size: 11px;">{{ number_format($totalQuota) }}</td>
                    <td class="text-right font-mono font-weight-bold" style="font-size: 11px; color: #047857;">{{ number_format($totalRec) }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        {{-- CATATAN & SYARAT PENGIRIMAN --}}
        <div class="terms-box">
            <b>Syarat & Ketentuan Penerimaan Material:</b>
            <ol>
                <li>Surat Jalan dari Supplier <b>wajib mencantumkan No. Purchase Order (PO)</b> yang tertera di atas.</li>
                <li>Setiap bundle / coil fisik wajib ditempel label identitas (Coil Tag ID, Spesifikasi, Dimensi, dan Qty Lembar).</li>
                <li>Material yang dikirimkan harus bebas dari cacat fisik seperti karat (rust), gelombang (waviness), dan goresan (scratch).</li>
                <li>Penerimaan barang hanya sah setelah diverifikasi dan ditandatangani oleh staf Warehouse RM & QC Inspector PT Asalta Mandiri Agung.</li>
            </ol>
        </div>

        {{-- LEMBAR TANDA TANGAN & STEMPEL 4 PIHAK --}}
        <div class="doc-footer">
            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Dibuat Oleh (Purchasing)</div>
                    <div class="stamp-wrapper">
                        <span class="stamp-top">PT ASALTA MANDIRI AGUNG</span>
                        <span class="stamp-mid">PURCHASING</span>
                        <span class="stamp-bot">ISSUED</span>
                    </div>
                    <div class="sig-name">Purchasing Staff</div>
                </div>
            </div>

            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Diperiksa (Warehouse RM)</div>
                    <div class="sig-name">Head of Warehouse</div>
                </div>
            </div>

            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Disetujui (Management)</div>
                    <div class="sig-name">Plant / General Manager</div>
                </div>
            </div>

            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Konfirmasi Vendor / Supplier</div>
                    <div class="sig-name">Ttd & Stempel Supplier</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>