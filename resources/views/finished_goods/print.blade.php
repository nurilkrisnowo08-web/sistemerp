<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Mutasi - {{ $customer }}</title>
    <style>
        /* 1. SETTING KERTAS A4 LANDSCAPE & BERSIHIN SAMPAH BROWSER */
        @page { 
            size: A4 landscape; 
            margin: 0; /* Menghilangkan URL, Tanggal, dan Judul Browser di pojok kertas */
        }
        body { 
            margin: 1.5cm; 
            font-family: Arial, sans-serif; 
            background: #fff; 
            color: #000;
        }

        /* 2. KOP SURAT RESMI PT. AMA */
        .kop-surat { 
            position: relative; 
            width: 100%; 
            border-bottom: 4px solid #000; 
            padding-bottom: 10px; 
            margin-bottom: 2px;
            display: flex;
            align-items: center;
        }
        .logo-area { width: 95px; }
        .logo-img { width: 100%; height: auto; }
        .kop-text { 
            flex-grow: 1; 
            text-align: center; 
            padding-right: 95px; /* Offset logo biar teks bener-bener di tengah */
        }
        .kop-text h1 { font-size: 26px; font-weight: 900; margin: 0; text-transform: uppercase; font-family: "Arial Black", sans-serif; }
        .kop-text p { font-size: 11px; margin: 0; font-weight: bold; line-height: 1.3; }
        .garis-bawah { border-bottom: 1.5px solid #000; margin-top: 3px; margin-bottom: 20px; }

        /* 3. JUDUL LAPORAN */
        .title-report { text-align: center; margin-bottom: 25px; }
        .title-report h2 { font-size: 18px; text-decoration: underline; margin-bottom: 5px; text-transform: uppercase; }
        .title-report p { font-size: 13px; font-weight: bold; margin: 0; }

        /* 4. TABEL MUTASI (TEBAL & BERSIH) */
        .table-print { width: 100%; border-collapse: collapse; margin-top: 10px; border: 2px solid #000; }
        .table-print th { 
            background-color: #f2f2f2 !important; 
            border: 1.5px solid #000; 
            padding: 10px; 
            font-size: 12px; 
            text-transform: uppercase;
        }
        .table-print td { 
            border: 1px solid #000; 
            padding: 8px; 
            font-size: 12px; 
            font-weight: bold; 
            text-align: center;
        }
        .text-left { text-align: left !important; padding-left: 10px !important; }

        /* 5. TANDA TANGAN */
        .signature-area { margin-top: 40px; width: 100%; }
        .signature-box { float: right; width: 250px; text-align: center; font-weight: bold; font-size: 14px; }

        /* SEMBUNYIKAN TOMBOL SAAT DIPRINT */
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body onload="window.print()"> {{-- Otomatis munculin dialog print pas dibuka --}}

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">KLIK UNTUK PRINT</button>
        <a href="{{ url()->previous() }}" style="margin-left: 10px; text-decoration: none; color: #666;">KEMBALI</a>
    </div>

    <div class="kop-surat">
        <div class="logo-area">    
        </div>
        <div class="kop-text">
            <h1>PT. Asalta Mandiri Agung</h1>
            <p>JL. RAYA JAKARTA-BOGOR KM. 49 (JLN. RODA PEMBANGUNAN) BOGOR</p>
            <p>TELP : (0251) 8652769, 8662656 | (021) 8753727, 8751390, 8751382</p>
        </div>
    </div>
    <div class="garis-bawah"></div>

    <div class="title-report">
        <h2>LAPORAN MUTASI BULANAN FINISH GOOD</h2>
        <p>CUSTOMER: {{ $customer }} | PERIODE: {{ $month_name }} {{ $year }}</p>
    </div>
    <table class="table-print">
        <thead>
            <tr>
                <th rowspan="2">PART NO</th>
                <th rowspan="2">PART NAME</th>
                <th colspan="4">ALUR MUTASI STOK (PCS)</th>
            </tr>
            <tr>
                <th>AWAL</th>
                <th>IN</th>
                <th>OUT</th>
                <th>AKHIR</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recap as $r)
            <tr>
                <td>{{ $r->part_no }}</td>
                <td class="text-left uppercase">{{ $r->part_name }}</td>
                <td>{{ number_format($r->stock_awal) }}</td>
                <td>+{{ number_format($r->total_in) }}</td>
                <td>-{{ number_format($r->total_out) }}</td>
                <td style="background-color: #f9f9f9;">{{ number_format($r->stock_akhir) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="signature-area">
        <div class="signature-box">
            Bogor, {{ date('d F Y') }}<br>
            Dibuat Oleh,<br><br><br><br><br>
            ( ............................. )<br>
            Admin Gudang
        </div>
    </div>

</body>
</html>