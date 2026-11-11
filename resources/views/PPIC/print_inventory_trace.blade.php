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
        
        .col-rm { background-color: rgba(245, 158, 11, 0.05); }
        .col-wip { background-color: rgba(239, 68, 68, 0.05); }
        .col-fg { background-color: rgba(16, 185, 129, 0.05); }

        .fw-bold { font-weight: bold; }

        /* ✨ TABEL DETAIL (RINCIAN TIMELINE) ✨ */
        .detail-wrapper { background: #fafafa; padding: 10px; }
        .detail-grid { width: 100%; font-size: 9px; border: none !important; }
        .detail-grid td { border: none !important; vertical-align: top; text-align: left; padding: 0 10px; }
        .detail-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .detail-table th { background: #e2e8f0; font-size: 8px; padding: 4px; border: 1px solid #cbd5e1; }
        .detail-table td { padding: 4px; border: 1px solid #cbd5e1; font-size: 9px; }
        
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
            <td><strong>Filter Part Number</strong></td>
            <td>:</td>
            <td>{{ $selectedPart ? $selectedPart : 'ALL PARTS' }}</td>
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
                <th class="col-rm">IN Sup</th>
                <th class="col-rm">IN Ret</th>
                <th class="col-rm">OUT Prod</th>
                <th class="col-rm">SALDO</th>
                
                <th class="col-wip">IN (Prod)</th>
                <th class="col-wip">OUT (Las)</th>
                <th class="col-wip">SALDO</th>
                
                <th class="col-fg">IN (Prod)</th>
                <th class="col-fg">OUT (SJ)</th>
                <th class="col-fg">SALDO</th>
            </tr>
        </thead>
        <tbody>
            @forelse($parts as $idx => $p)
            <!-- BARIS SUMMARY -->
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td class="text-left">
                    <span class="fw-bold">{{ $p->part_name }}</span><br>
                    <span style="font-size:9px; color:#555;">{{ $p->part_no }}</span>
                </td>
                <td>{{ $p->customer_code }}</td>
                
                <td class="col-rm">{{ number_format($p->rm_in) }}</td>
                <td class="col-rm">{{ number_format($p->rm_ret) }}</td>
                <td class="col-rm">{{ number_format($p->rm_out) }}</td>
                <td class="col-rm fw-bold">{{ number_format($p->rm_stock) }}</td>

                <td class="col-wip">{{ number_format($p->wip_in) }}</td>
                <td class="col-wip">{{ number_format($p->wip_out) }}</td>
                <td class="col-wip fw-bold">{{ number_format($p->wip_stock ?? 0) }}</td>

                <td class="col-fg">{{ number_format($p->fg_in) }}</td>
                <td class="col-fg">{{ number_format($p->fg_out) }}</td>
                <td class="col-fg fw-bold">{{ number_format($p->fg_stock ?? 0) }}</td>
            </tr>
            
            <!-- ✨ BARIS DETAIL (RINCIAN TIMELINE) ✨ -->
            <tr>
                <td colspan="13" class="detail-wrapper">
                    <table class="detail-grid">
                        <tr>
                            <!-- 1. TIMELINE RM -->
                            <td width="33%">
                                <strong>[+] TIMELINE RAW MATERIAL</strong>
                                @if(count($p->history_rm) > 0)
                                <table class="detail-table">
                                    <tr><th>TANGGAL</th><th>KETERANGAN</th><th align="right">QTY</th></tr>
                                    @foreach($p->history_rm as $rm)
                                    <tr>
                                        <td>{{ date('d/m/y H:i', strtotime($rm->created_at)) }}</td>
                                        @if($rm->type_log == 'IN')
                                            <td>IN {{ $rm->source == 'supplier' ? 'SUP' : 'RETUR' }} ({{ $rm->material_code }})</td>
                                            <td align="right" style="color:green;">+{{ number_format($rm->pcs_in) }}</td>
                                        @else
                                            <td>OUT Prod ({{ $rm->material_code }})</td>
                                            <td align="right" style="color:red;">-{{ number_format($rm->pcs_used) }}</td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </table>
                                @else
                                <div style="color:#777; margin-top:3px;"><i>Tidak ada aktivitas RM.</i></div>
                                @endif
                            </td>
                            
                            <!-- 2. TIMELINE WIP -->
                            <td width="33%" style="border-left: 1px dashed #ccc !important;">
                                <strong>[+] TIMELINE WIP (WELDING)</strong>
                                @if(count($p->history_wip) > 0)
                                <table class="detail-table">
                                    <tr><th>TANGGAL</th><th>KETERANGAN</th><th align="right">QTY</th></tr>
                                    @foreach($p->history_wip as $w)
                                    <tr>
                                        <td>{{ date('d/m/y H:i', strtotime($w->created_at)) }}</td>
                                        @if($w->type_log == 'IN')
                                            <td>IN (Dari Stamping)</td>
                                            <td align="right" style="color:green;">+{{ number_format($w->qty_ok) }}</td>
                                        @else
                                            <td>OUT (Di-Spot/Robot)</td>
                                            <td align="right" style="color:red;">-{{ number_format($w->qty_masuk) }}</td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </table>
                                @else
                                <div style="color:#777; margin-top:3px;"><i>Tidak ada aktivitas WIP.</i></div>
                                @endif
                            </td>
                            
                            <!-- 3. TIMELINE FG -->
                            <td width="33%" style="border-left: 1px dashed #ccc !important;">
                                <strong>[+] TIMELINE FINISH GOOD</strong>
                                @if(count($p->history_fg) > 0)
                                <table class="detail-table">
                                    <tr><th>TANGGAL</th><th>KETERANGAN</th><th align="right">QTY</th></tr>
                                    @foreach($p->history_fg as $f)
                                    <tr>
                                        <td>{{ date('d/m/y H:i', strtotime($f->created_at)) }}</td>
                                        @if($f->type_log == 'IN')
                                            <td>IN (Dari Produksi)</td>
                                            <td align="right" style="color:green;">+{{ number_format($f->qty_ok) }}</td>
                                        @else
                                            <td>OUT (Delivery SJ)</td>
                                            <td align="right" style="color:red;">-{{ number_format($f->qty_delivery) }}</td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </table>
                                @else
                                <div style="color:#777; margin-top:3px;"><i>Tidak ada aktivitas FG.</i></div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
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