<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRINT WOS DELIVERY - {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</title>
    <!-- Font Profesional Industri -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Setup Kertas A4 Memanjang (Landscape) */
        @page { size: A4 landscape; margin: 8mm 10mm; }
        body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; font-size: 10px; margin: 0; padding: 0; color: #000; background-color: #fff; }
        
        /* Navigasi Print (Sembunyi saat dicetak) */
        .no-print-area { text-align: right; margin-bottom: 15px; padding: 12px 20px; background: #f8fafc; border-bottom: 2px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center;}
        .form-title-web { font-size: 14px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 1px;}
        .btn { padding: 8px 16px; font-size: 11px; font-weight: 800; border-radius: 6px; border: none; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: 0.5px; transition: all 0.2s;}
        .btn-back { background: #e2e8f0; color: #475569; margin-right: 10px; }
        .btn-back:hover { background: #cbd5e1; }
        .btn-print { background: #2563eb; color: white; box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2); }
        .btn-print:hover { background: #1d4ed8; transform: translateY(-1px); }

        /* HEADER LAYOUT ALA BPRM */
        .header-container { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        
        .logo-section { display: flex; align-items: center; width: 33%; }
        .logo-img { height: 40px; margin-right: 12px; }
        .company-info { font-size: 11px; font-weight: 800; line-height: 1.3; }
        .company-info span { font-weight: 500; font-size: 9px; }

        .title-section { width: 34%; text-align: center; }
        .title-section h1 { margin: 0; font-size: 16px; font-weight: 900; letter-spacing: 1px; text-decoration: underline; }
        .title-section h3 { margin: 3px 0 0 0; font-size: 12px; font-weight: 700; }

        .meta-section { width: 33%; text-align: right; }
        .meta-table { float: right; border-collapse: collapse; text-align: left; font-size: 9px; font-weight: 700; }
        .meta-table td { border: 1px solid #000; padding: 2px 6px; }
        .date-info { clear: both; margin-top: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; }

        /* TABEL WOS */
        table.wos-table { width: 100%; border-collapse: collapse; text-align: center; }
        table.wos-table th, table.wos-table td { border: 1px solid #000; padding: 5px 4px; vertical-align: middle; }
        table.wos-table th { background-color: #f8fafc; font-weight: 800; font-size: 8px; text-transform: uppercase; }
        
        .part-no { font-weight: 800; font-size: 10px; }
        .part-name { font-size: 8px; font-weight: 600; color: #334155; }
        
        .empty-row td { height: 22px; } /* Tinggi baris kosong */

        /* FOOTER KETERANGAN & TANDA TANGAN */
        .footer-container { display: flex; justify-content: space-between; margin-top: 10px; }
        .note-box { width: 55%; border: 1px solid #000; padding: 6px; font-size: 9px; min-height: 55px; }
        .signature-box { width: 42%; }
        table.signature-table { width: 100%; border-collapse: collapse; text-align: center; font-size: 9px; }
        table.signature-table th, table.signature-table td { border: 1px solid #000; padding: 4px; }
        table.signature-table th { font-weight: 800; background: #f8fafc; }
        table.signature-table td { height: 45px; vertical-align: bottom; font-weight: 700; }

        @media print { 
            .no-print { display: none !important; } 
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } 
        }
    </style>
</head>
<body>

    <!-- Area Navigasi (Hilang saat diprint) -->
    <div class="no-print no-print-area">
        <div class="form-title-web">FORM CETAK WOS DELIVERY LOGISTICS</div>
        <div>
            <button class="btn btn-back" onclick="window.close();">TUTUP KEMBALI</button>
            <button class="btn btn-print" onclick="window.print()">🖨️ CETAK FORMULIR</button>
        </div>
    </div>

    <!-- Header Dokumen -->
    <div class="header-container">
        <!-- Logo Kiri -->
        <div class="logo-section">
            <img src="{{ asset('admin/img/Logo-asalta.png') }}" class="logo-img" alt="Logo">
            <div class="company-info">
                PT. Asalta Mandiri Agung<br>
                <span>Dept. PPC<br>LOGISTICS & DELIVERY</span>
            </div>
        </div>
        
        <!-- Judul Tengah -->
        <div class="title-section">
            <h1>WORK ORDER SHEET DELIVERY</h1>
            <h3>(WOS LOGISTICS)</h3>
        </div>
        
        <!-- Meta Data Kanan -->
        <div class="meta-section">
            <table class="meta-table">
                <tr>
                    <td style="width: 50px;">WOS NO</td>
                    <td style="width: 100px;">WOS-DLV-{{ date('ymd', strtotime($date)) }}</td>
                </tr>
                <tr>
                    <td>REVISI</td>
                    <td>00</td>
                </tr>
            </table>
            <div class="date-info">
                HARI / TANGGAL : {{ \Carbon\Carbon::parse($date)->translatedFormat('d-m-Y') }}
            </div>
        </div>
    </div>

    <!-- Tabel Utama -->
    <table class="wos-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">NO</th>
                <th rowspan="2" style="width: 50px;">CUST</th>
                <th rowspan="2" style="width: 130px;">PART NUMBER</th>
                <th rowspan="2" style="width: 180px;">PART NAME</th>
                <th colspan="2">TIME SCHEDULE</th>
                <th colspan="3">DELIVERY QUANTITY</th>
                <th rowspan="2" style="width: 55px;">STOCK<br>FG</th>
                <th colspan="2">ACTUAL DELV</th>
                <th rowspan="2" style="width: 55px;">JUDGE</th>
                <th rowspan="2" style="width: 140px;">REMARK / KETERANGAN</th>
            </tr>
            <tr>
                <th style="width: 45px;">PULLING</th>
                <th style="width: 45px;">DELIVERY</th>
                <th style="width: 45px;">BLNC</th>
                <th style="width: 45px;">PLAN</th>
                <th style="width: 45px;">TOTAL</th>
                <th style="width: 45px;">SHIFT I</th>
                <th style="width: 45px;">SHIFT II</th>
            </tr>
        </thead>
        <tbody>
            @php $rowCount = 0; @endphp
            @forelse($deliveries as $index => $d)
                @php $rowCount++; @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="font-weight: 800;">{{ $d->part_cust }}</td>
                    <td style="text-align: left; padding-left: 8px;" class="part-no">{{ $d->part_no }}</td>
                    <td style="text-align: left; padding-left: 8px;" class="part-name">{{ $d->part_name }}</td>
                    
                    <td style="font-weight: 700;">{{ $d->pulling_time ? date('H:i', strtotime($d->pulling_time)) : '-' }}</td>
                    <td style="font-weight: 700;">{{ $d->delv_time ? date('H:i', strtotime($d->delv_time)) : '-' }}</td>
                    
                    <td>{{ $d->judge === 'CLOSE' ? '-' : number_format($d->blnc_delv) }}</td>
                    <td>{{ $d->judge === 'CLOSE' ? '-' : number_format($d->plan_delv) }}</td>
                    <td style="font-weight: 900;">{{ $d->total_delv_display }}</td>
                    
                    <td style="font-weight: 800;">{{ number_format($d->stock_ready) }}</td>
                    
                    <!-- Dikosongkan jika 0 agar bersih saat diprint -->
                    <td style="font-weight: 800;">{{ $d->act_delv_s1 > 0 ? number_format($d->act_delv_s1) : '' }}</td>
                    <td style="font-weight: 800;">{{ $d->act_delv_s2 > 0 ? number_format($d->act_delv_s2) : '' }}</td>
                    
                    <td style="font-weight: 900;">{{ $d->judge }}</td>
                    <td style="text-align: left; padding-left: 6px; font-size: 8px; font-weight:600;">{{ $d->keterangan ?: '' }}</td>
                </tr>
            @empty
                <!-- Baris akan diisi oleh padding jika kosong -->
            @endforelse

            <!-- PADDING BARIS KOSONG (Minimal 12 Baris) -->
            @for($i = $rowCount; $i < 12; $i++)
                <tr class="empty-row">
                    <td>{{ $i + 1 }}</td>
                    <td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Footer Tanda Tangan & Note -->
    <div class="footer-container">
        <!-- Kotak Catatan Kiri -->
        <div class="note-box">
            <b>Note :</b>
        </div>
        
        <!-- Kotak Tanda Tangan Kanan -->
        <div class="signature-box">
            <table class="signature-table">
                <tr>
                    <th>DISETUJUI</th>
                    <th>DIPERIKSA</th>
                    <th>DIBUAT</th>
                    <th>DITERIMA</th>
                </tr>
                <tr>
                    <td>[ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ]</td>
                    <td>[ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ]</td>
                    <td>[ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ]</td>
                    <td>[ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ]</td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>