<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print - Inventory Traceability Report</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 11px; margin: 0; padding: 20px; color: #000; }
        
        .header-print { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header-print h2 { margin: 0 0 5px 0; font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .header-print p { margin: 0; font-size: 11px; color: #333; }
        
        .info-table { width: 100%; margin-bottom: 15px; font-size: 11px; }
        .info-table td { padding: 3px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px; text-align: center; vertical-align: middle; }
        
        table.data-table th { background-color: #f0f0f0; font-weight: bold; text-transform: uppercase; font-size: 10px;}
        table.data-table td.text-left { text-align: left; }
        
        /* Grup Warna Kolom Biar Gampang Dibaca Pas Dicetak */
        .col-rm { background-color: rgba(245, 158, 11, 0.05); }
        .col-wip { background-color: rgba(239, 68, 68, 0.05); }
        .col-fg { background-color: rgba(16, 185, 129, 0.05); }

        .fw-bold { font-weight: bold; }
        
        @media print {
            @page { size: landscape; margin: 15mm; }
            body { padding: 0; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header-print">
        <h2>MATERIAL & INVENTORY TRACEABILITY REPORT</h2>
        <p>PT ASALTA MANDIRI AGUNG // INDUSTRIAL ECOSYSTEM ENGINE</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>Filter Customer</strong></td>
            <td width="2%">:</td>
            <td width="33%">{{ $selectedCustomer ? strtoupper($selectedCustomer) : 'ALL CUSTOMER' }}</td>
            <td width="15%"><strong>Tanggal Cetak</strong></td>
            <td width="2%">:</td>
            <td width="33%">{{ date('d M Y, H:i') }} WIB</td>
        </tr>
        <tr>
            <td><strong>Pencarian</strong></td>
            <td>:</td>
            <td>{{ $search ? $search : '-' }}</td>
            <td><strong>Dicetak Oleh</strong></td>
            <td>:</td>
            <td>{{ Auth::user()->name ?? 'Administrator PPIC' }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" width="3%">NO</th>
                <th rowspan="2" class="text-left" width="18%">PART NAME & NUMBER</th>
                <th rowspan="2" width="7%">CUST</th>
                <th colspan="4" class="col-rm">RAW MATERIAL (RM) Lbr</th>
                <th colspan="3" class="col-wip">WIP (WELDING) Pcs</th>
                <th colspan="3" class="col-fg">FINISH GOOD (FG) Pcs</th>
            </tr>
            <tr>
                <!-- RM Sub -->
                <th class="col-rm">IN Sup</th>
                <th class="col-rm">IN Ret</th>
                <th class="col-rm">OUT Prod</th>
                <th class="col-rm">SALDO</th>
                
                <!-- WIP Sub -->
                <th class="col-wip">IN (Prod)</th>
                <th class="col-wip">OUT (Las)</th>
                <th class="col-wip">SALDO</th>
                
                <!-- FG Sub -->
                <th class="col-fg">IN (Prod)</th>
                <th class="col-fg">OUT (SJ)</th>
                <th class="col-fg">SALDO</th>
            </tr>
        </thead>
        <tbody>
            @forelse($parts as $idx => $p)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td class="text-left">
                    <span class="fw-bold">{{ $p->part_name }}</span><br>
                    <span style="font-size:9px; color:#555;">{{ $p->part_no }}</span>
                </td>
                <td>{{ $p->customer_code }}</td>
                
                <!-- RM Data -->
                <td class="col-rm">{{ number_format($p->rm_in) }}</td>
                <td class="col-rm">{{ number_format($p->rm_ret) }}</td>
                <td class="col-rm">{{ number_format($p->rm_out) }}</td>
                <td class="col-rm fw-bold">{{ number_format($p->rm_stock) }}</td>

                <!-- WIP Data -->
                <td class="col-wip">{{ number_format($p->wip_in) }}</td>
                <td class="col-wip">{{ number_format($p->wip_out) }}</td>
                <td class="col-wip fw-bold">{{ number_format($p->wip_stock ?? 0) }}</td>

                <!-- FG Data -->
                <td class="col-fg">{{ number_format($p->fg_in) }}</td>
                <td class="col-fg">{{ number_format($p->fg_out) }}</td>
                <td class="col-fg fw-bold">{{ number_format($p->fg_stock ?? 0) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="13" style="padding: 30px;">Data part tidak ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right; font-size: 10px; font-style: italic;">
        *Dokumen dicetak dari Asalta MRP System. Data real-time sesuai kondisi saat tombol Print ditekan.
    </div>

</body>
</html>