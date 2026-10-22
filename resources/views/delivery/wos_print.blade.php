<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print WOS Delivery - {{ $date }}</title>
    <style>
        @page { size: landscape; margin: 10mm; }
        body { font-family: 'Arial', sans-serif; font-size: 11px; color: #000; margin: 0; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .company-info h2, .company-info h3 { margin: 0; padding: 0; }
        .company-info h2 { font-size: 14px; text-transform: uppercase; }
        .company-info h3 { font-size: 12px; font-weight: normal; }
        .title-doc h1 { margin: 0; font-size: 20px; text-transform: uppercase; text-align: right; letter-spacing: 2px; }
        .title-doc p { margin: 5px 0 0 0; text-align: right; font-size: 12px; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; vertical-align: middle; }
        th { background-color: #f0f0f0; font-size: 10px; font-weight: bold; }
        td.text-left { text-align: left; }
        .part-name { font-weight: bold; font-size: 11px; }
        .part-no { font-size: 10px; }
        
        .signature-box { width: 100%; margin-top: 20px; display: flex; justify-content: flex-end; }
        .signature-table { width: 40%; border-collapse: collapse; text-align: center; }
        .signature-table th, .signature-table td { border: 1px solid #000; padding: 5px; }
        .signature-table th { background-color: #f0f0f0; font-size: 10px; }
        .signature-table td { height: 60px; vertical-align: bottom; font-size: 11px; }
        
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<!-- Header -->
    <div class="header">
        <!-- Tambahan Logo & Info Perusahaan -->
        <div style="display: flex; align-items: center;">
            <img src="{{ asset('public/admin/img/Logo-asalta.png') }}" alt="Logo Asalta" style="height: 45px; margin-right: 15px;">
            <div class="company-info">
                <h2>PT. Asalta Mandiri Agung</h2>
                <h3>Dept. PPC<br>Logistic & Delivery</h3>
            </div>
        </div>
        
        <div class="title-doc">
            <h1>WORK ORDER SHEET<br>DELIVERY</h1>
            <p>TGL: {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</p>
        </div>
    </div>
    <!-- Tabel Data -->
    <table>
        <thead>
            <tr>
                <th rowspan="2">NO</th>
                <th rowspan="2" class="text-left">PART NAME</th>
                <th rowspan="2">PART NUMBER</th>
                <th rowspan="2">CUST</th>
                <th colspan="2">TIME SCHEDULE</th>
                <th colspan="3">DELIVERY QUANTITY</th>
                <th rowspan="2">STOCK<br>READY</th>
                <th colspan="2">ACT DELV</th>
                <th rowspan="2">JUDGE</th>
                <th rowspan="2">KETERANGAN</th>
            </tr>
            <tr>
                <th>PULLING</th>
                <th>DELV</th>
                <th>OUTSTANDING</th>
                <th>PLAN</th>
                <th>TOTAL</th>
                <th>SHIFT 1</th>
                <th>SHIFT 2</th>
            </tr>
        </thead>
        <tbody>
            @forelse($deliveries as $index => $d)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left part-name">{{ $d->part_name }}</td>
                    <td class="part-no">{{ $d->part_no }}</td>
                    <td>{{ $d->part_cust }}</td>
                    <td>{{ $d->pulling_time ? date('H:i', strtotime($d->pulling_time)) : '-' }}</td>
                    <td>{{ $d->delv_time ? date('H:i', strtotime($d->delv_time)) : '-' }}</td>
                    
                    <td>{{ $d->judge === 'CLOSE' ? '-' : number_format($d->blnc_delv) }}</td>
                    <td>{{ $d->judge === 'CLOSE' ? '-' : number_format($d->plan_delv) }}</td>
                    <td style="font-weight: bold;">{{ $d->total_delv_display }}</td>
                    
                    <td>{{ number_format($d->stock_ready) }}</td>
                    
                    <td>{{ $d->act_delv_s1 > 0 ? number_format($d->act_delv_s1) : '-' }}</td>
                    <td>{{ $d->act_delv_s2 > 0 ? number_format($d->act_delv_s2) : '-' }}</td>
                    
                    <td style="font-weight: bold;">{{ $d->judge }}</td>
                    <td class="text-left">{{ $d->keterangan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" style="padding: 20px;">Jadwal pengiriman kosong untuk tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="signature-box">
        <table class="signature-table">
            <thead>
                <tr>
                    <th>DIKETAHUI</th>
                    <th>DIPERIKSA</th>
                    <th>DIBUAT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>BUDI. P</td>
                    <td>MUSAWAHAR</td>
                    <td>DENY. H</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Tombol Print Manual jika popup gagal -->
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #3b82f6; color: white; border: none; cursor: pointer; font-size: 14px; border-radius: 5px;">Print Dokumen Ini</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #64748b; color: white; border: none; cursor: pointer; font-size: 14px; border-radius: 5px; margin-left: 10px;">Tutup</button>
    </div>
</body>
</html>