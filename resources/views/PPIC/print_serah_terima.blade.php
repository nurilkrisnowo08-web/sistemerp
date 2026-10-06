<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Serah Terima Material - {{ $line_code }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; color: #000; font-size: 12px; }
        .header { border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .header-left h2 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .header-left p { margin: 5px 0 0 0; font-weight: bold; }
        .header-right { text-align: right; }
        
        .info-table { margin-bottom: 20px; font-weight: bold; }
        .info-table td { padding: 3px 10px 3px 0; }

        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 30px; text-align: center; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 8px; }
        .table-data th { background-color: #f2f2f2; }
        .text-left { text-align: left; }

        .signature { display: flex; justify-content: space-between; margin-top: 40px; text-align: center; }
        .sign-box { width: 30%; }
        .sign-space { height: 80px; }
        .sign-name { font-weight: bold; text-decoration: underline; }

        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; font-weight: bold; cursor: pointer;">🖨️ PRINT DOKUMEN MATERIAL</button>
    </div>

    <div class="header">
        <div class="header-left">
            <h2>SURAT SERAH TERIMA MATERIAL</h2>
            <p>PT. ASALTA MANDIRI AGUNG (GUDANG RM ➔ PRODUKSI)</p>
        </div>
        <div class="header-right">
            <h3>No: STM-{{ date('Ymd') }}-{{ $line_code }}</h3>
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td>TANGGAL PRODUKSI</td>
            <td>: {{ date('d F Y', strtotime($date)) }}</td>
        </tr>
        <tr>
            <td>SHIFT KERJA</td>
            <td>: FULL DAY (SHIFT 1 & SHIFT 2)</td>
        </tr>
        <tr>
            <td>TUJUAN MESIN</td>
            <td>: {{ $line_code }}</td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th width="30">NO</th>
                <th>NO PRODUKSI (WOS)</th>
                <th>SHIFT</th>
                <th>PART NUMBER</th>
                <th>SPESIFIKASI MATERIAL</th>
                <th>NO COIL</th>
                <th width="100">QTY DIAMBIL<br>(SHEET)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batches as $index => $b)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="font-family: monospace; font-weight: bold;">{{ $b->no_produksi }}</td>
                <td style="font-weight: bold; color: {{ $b->shift == 'Pagi' ? '#d97706' : '#0f172a' }};">
                    {{ strtoupper($b->shift) }}
                </td>
                <td class="text-left font-weight-bold">{{ $b->material_code }}<br><small>{{ $b->part_name }}</small></td>
                <td class="text-left">
                    {{ $b->material_name }}<br>
                    <small>Spec: {{ $b->spec }} | Size: {{ $b->size }}</small>
                </td>
                <td style="font-weight: bold;">{{ $b->coil_id }}</td>
                <td style="font-size: 16px; font-weight: bold;">{{ $b->qty_ambil_pcs }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding: 20px; font-weight: bold; color: red;">-- BELUM ADA DATA MATERIAL --</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature">
        <div class="sign-box">
            <div>Diserahkan Oleh,</div>
            <div class="sign-space"></div>
            <div class="sign-name">GUDANG RM</div>
        </div>
        <div class="sign-box">
            <div>Diterima Oleh,</div>
            <div class="sign-space"></div>
            <div class="sign-name">LEADER PRODUKSI</div>
        </div>
        <div class="sign-box">
            <div>Diketahui Oleh,</div>
            <div class="sign-space"></div>
            <div class="sign-name">PPIC DEPT</div>
        </div>
    </div>

</body>
</html>