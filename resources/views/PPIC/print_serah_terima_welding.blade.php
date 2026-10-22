<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CETAK BPRM WELDING - {{ strtoupper($line_code) }}</title>
    <!-- Font Profesional Industri -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Setup Kertas A4 Memanjang (Landscape) */
        @page { size: A4 landscape; margin: 8mm 10mm; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 8.5px; margin: 0; padding: 0; color: #000; background-color: #fff; }
        
        /* Navigasi Print (Sembunyi saat dicetak) */
        .no-print-area { text-align: right; margin-bottom: 15px; padding: 12px 20px; background: #f8fafc; border-bottom: 2px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center;}
        .form-title-web { font-size: 13px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 1px;}
        .btn { padding: 8px 16px; font-size: 11px; font-weight: 800; border-radius: 6px; border: none; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif; transition: all 0.2s;}
        .btn-back { background: #e2e8f0; color: #475569; margin-right: 10px; }
        .btn-print { background: #2563eb; color: white; box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2); }

        /* HEADER BPRM */
        .header-wrap { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px; }
        .logo-wrap { display: flex; align-items: center; width: 33%; }
        .logo-img { height: 35px; margin-right: 10px; }
        .comp-info { font-size: 10px; font-weight: 800; line-height: 1.2; }
        .comp-dept { font-size: 8px; font-weight: 600; }

        .title-wrap { width: 34%; text-align: center; margin-top: 5px;}
        .title-main { font-size: 14px; font-weight: 900; text-decoration: underline; margin: 0; letter-spacing: 0.5px;}
        .title-sub { font-size: 11px; font-weight: 800; margin-top: 2px; }

        .meta-wrap { width: 33%; display: flex; justify-content: flex-end; }
        .meta-table { border-collapse: collapse; font-size: 8px; font-weight: 800; text-align: left; }
        .meta-table td { border: 1px solid #000; padding: 2px 5px; }

        .sub-header { display: flex; justify-content: space-between; font-weight: 800; font-size: 9.5px; margin-bottom: 3px; text-transform: uppercase;}

        /* TABEL UTAMA */
        table.bprm-table { width: 100%; border-collapse: collapse; text-align: center; }
        table.bprm-table th, table.bprm-table td { border: 1px solid #000; padding: 4px 3px; vertical-align: middle; }
        table.bprm-table th { background-color: #f8fafc; font-weight: 800; font-size: 7.5px; text-transform: uppercase; }
        
        .row-shift td { background-color: #e2e8f0; font-weight: 800; text-align: left; padding-left: 8px; font-size: 9px; }
        .text-left { text-align: left; padding-left: 5px !important; }
        .font-bold { font-weight: 800; }
        .part-no { font-size: 9px; font-weight: 800; }
        
        .empty-row td { height: 18px; }

        /* FOOTER TANDA TANGAN */
        .footer-wrap { display: flex; justify-content: space-between; margin-top: 8px; }
        .note-box { width: 45%; border: 1px solid #000; padding: 4px 6px; font-size: 8px; font-weight: 700; min-height: 40px;}
        .sign-box { width: 50%; }
        table.sign-table { width: 100%; border-collapse: collapse; text-align: center; font-size: 8px; }
        table.sign-table th, table.sign-table td { border: 1px solid #000; padding: 3px; }
        table.sign-table th { font-weight: 800; background: #f8fafc; }
        table.sign-table td { height: 35px; vertical-align: bottom; font-weight: 800; }

        @media print { 
            .no-print { display: none !important; } 
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } 
        }
    </style>
</head>
<body>

    <div class="no-print no-print-area">
        <div class="form-title-web">FORM CETAK BPRM WELDING - LINE: {{ strtoupper($line_code) }}</div>
        <div>
            <button class="btn btn-back" onclick="window.close();">TUTUP KEMBALI</button>
            <button class="btn btn-print" onclick="window.print()">🖨️ CETAK FORMULIR</button>
        </div>
    </div>

     <div style="display: flex; align-items: center;">
            <img src="{{ asset('public/admin/img/Logo-asalta.png') }}" alt="Logo Asalta" style="height: 45px; margin-right: 15px;">
            <div class="company-info">
                <h2>PT. Asalta Mandiri Agung</h2>
                <h3>Dept. PPC<br>LOGISTICS & MATERIAL</h3>
            </div>
        </div>
        
        <div class="title-wrap">
            <h1 class="title-main">BUKTI PENYERAHAN MATERIAL (WIP)</h1>
            <h3 class="title-sub">(BPRM - WELDING)</h3>
        </div>
        
        <div class="meta-wrap">
            <table class="meta-table">
                <tr>
                    <td style="width: 45px;">WOS NO.</td>
                    <td style="width: 80px;">WOS-WLD-{{ date('ymd', strtotime($date)) }}</td>
                </tr>
                <tr>
                    <td>BPRM NO.</td>
                    <td></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="sub-header">
        <div>MESIN / LINE : {{ strtoupper($line_code) }}</div>
        <div>HARI / TANGGAL : {{ \Carbon\Carbon::parse($date)->translatedFormat('d-m-Y') }}</div>
    </div>

    <table class="bprm-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">NO</th>
                <th rowspan="2" style="width: 40px;">CUSTOMER</th>
                <th rowspan="2" style="width: 100px;">PART NUMBER</th>
                <th rowspan="2" style="width: 150px;">PART NAME</th>
                <th rowspan="2" style="width: 70px;">SUMBER STOK</th>
                <th rowspan="2" style="width: 80px;">NO. COIL</th>
                <th rowspan="2" style="width: 60px;">SPEC</th>
                <th rowspan="2" style="width: 80px;">SIZE</th>
                <th colspan="2">QUANTITY</th>
                <th colspan="5">ACTUAL PRODUCTION</th>
                <th rowspan="2" style="width: 100px;">REMARK</th>
            </tr>
            <tr>
                <th style="width: 35px;">SHEET</th>
                <th style="width: 35px;">PCS</th>
                <th style="width: 25px;">OK</th>
                <th style="width: 25px;">WIP</th>
                <th style="width: 25px;">NG</th>
                <th style="width: 35px;">REPAIR</th>
                <th style="width: 35px;">RETURN</th>
            </tr>
        </thead>
        <tbody>
            
            <!-- ================= SHIFT 1 ================= -->
            <tr class="row-shift">
                <td colspan="16">SHIFT I</td>
            </tr>
            @php $rowCountS1 = 0; @endphp
            @if(isset($batchesS1) && count($batchesS1) > 0)
                @foreach($batchesS1 as $index => $b)
                    @php $rowCountS1++; @endphp
                    <tr>
                        <td>{{ $rowCountS1 }}</td>
                        <td class="font-bold">{{ $b->customer_code }}</td>
                        <td class="text-left part-no">{{ $b->display_part_no }}</td>
                        <td class="text-left" style="font-size: 7.5px; font-weight:600;">{{ $b->display_part_name }}</td>
                        
                        <!-- Welding menggunakan WIP dari Stamping -->
                        <td class="font-bold text-primary" style="font-size: 8px;">{{ $b->no_surat_jalan }}</td>
                        <td class="font-bold">{{ $b->coil_id }}</td>
                        <td style="font-size: 7.5px; font-weight:600;">{{ $b->spec }}</td>
                        <td style="font-size: 7.5px; font-weight:600;">{{ $b->size }}</td>
                        
                        <!-- QUANTITY -->
                        <td></td> <!-- Sheet kosong untuk Welding -->
                        <td class="font-bold">{{ number_format($b->qty_ambil_pcs) }}</td>
                        
                        <!-- ACTUAL PRODUCTION -->
                        <td style="font-weight: 800; color: #10b981;">{{ $b->qty_hasil_ok > 0 ? number_format($b->qty_hasil_ok) : '' }}</td>
                        <td></td>
                        <td style="font-weight: 800; color: #ef4444;">{{ $b->qty_hasil_ng > 0 ? number_format($b->qty_hasil_ng) : '' }}</td>
                        <td></td>
                        <td style="font-weight: 800; color: #0284c7;">{{ $b->qty_return_warehouse > 0 ? number_format($b->qty_return_warehouse) : '' }}</td>
                        
                        <!-- REMARK -->
                        <td class="text-left" style="font-size: 7px;">{{ $b->keterangan }}</td>
                    </tr>
                @endforeach
            @endif
            @for($i = $rowCountS1; $i < 5; $i++)
                <tr class="empty-row">
                    <td>{{ $i + 1 }}</td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            @endfor

            <!-- ================= SHIFT 2 ================= -->
            <tr class="row-shift">
                <td colspan="16">SHIFT II</td>
            </tr>
            @php $rowCountS2 = 0; @endphp
            @if(isset($batchesS2) && count($batchesS2) > 0)
                @foreach($batchesS2 as $index => $b)
                    @php $rowCountS2++; @endphp
                    <tr>
                        <td>{{ $rowCountS2 }}</td>
                        <td class="font-bold">{{ $b->customer_code }}</td>
                        <td class="text-left part-no">{{ $b->display_part_no }}</td>
                        <td class="text-left" style="font-size: 7.5px; font-weight:600;">{{ $b->display_part_name }}</td>
                        
                        <td class="font-bold text-primary" style="font-size: 8px;">{{ $b->no_surat_jalan }}</td>
                        <td class="font-bold">{{ $b->coil_id }}</td>
                        <td style="font-size: 7.5px; font-weight:600;">{{ $b->spec }}</td>
                        <td style="font-size: 7.5px; font-weight:600;">{{ $b->size }}</td>
                        
                        <!-- QUANTITY -->
                        <td></td>
                        <td class="font-bold">{{ number_format($b->qty_ambil_pcs) }}</td>
                        
                        <!-- ACTUAL PRODUCTION -->
                        <td style="font-weight: 800; color: #10b981;">{{ $b->qty_hasil_ok > 0 ? number_format($b->qty_hasil_ok) : '' }}</td>
                        <td></td>
                        <td style="font-weight: 800; color: #ef4444;">{{ $b->qty_hasil_ng > 0 ? number_format($b->qty_hasil_ng) : '' }}</td>
                        <td></td>
                        <td style="font-weight: 800; color: #0284c7;">{{ $b->qty_return_warehouse > 0 ? number_format($b->qty_return_warehouse) : '' }}</td>
                        
                        <!-- REMARK -->
                        <td class="text-left" style="font-size: 7px;">{{ $b->keterangan }}</td>
                    </tr>
                @endforeach
            @endif
            @for($i = $rowCountS2; $i < 5; $i++)
                <tr class="empty-row">
                    <td>{{ $i + 1 }}</td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
            @endfor

        </tbody>
    </table>

    <div class="footer-wrap">
        <div class="note-box">
            Note :
        </div>
        <div class="sign-box">
            <table class="sign-table">
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