<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RM MUTATION LOG - {{ $periodTitle }}</title>
    <!-- Font Profesional -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark: #0f172a;
            --gray: #475569;
            --light-gray: #f1f5f9;
            --border: #cbd5e1;
            --blue: #1e3a8a;
            --green: #15803d;
            --red: #b91c1c;
        }

        * { box-sizing: border-box; }

        @page { size: A4 landscape; margin: 10mm; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 9px;
            color: var(--dark);
            margin: 0;
            padding: 10px;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* 🔘 SCREEN TOOLBAR (NO PRINT) */
        .no-print-area {
            display: flex; justify-content: space-between; align-items: center;
            background: #f8fafc; padding: 12px 15px; border-radius: 8px;
            margin-bottom: 15px; border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .filter-group { display: flex; gap: 10px; align-items: center; }
        .filter-group select, .filter-group input {
            padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border);
            font-family: 'Plus Jakarta Sans', sans-serif; font-size: 10px; font-weight: 600;
        }
        .btn { padding: 8px 16px; font-size: 10px; font-weight: 800; border-radius: 6px; border: none; cursor: pointer; transition: all 0.2s; text-transform: uppercase; letter-spacing: 0.5px;}
        .btn-filter { background: #0f172a; color: #fff; }
        .btn-back { background: #64748b; color: #fff; }
        .btn-print { background: #2563eb; color: #fff; box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2); }

        /* 🏢 HEADER DOKUMEN */
        .header-container { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid var(--dark); padding-bottom: 8px; margin-bottom: 12px; }
        .comp-info h1 { margin: 0; font-size: 14px; font-weight: 900; letter-spacing: 1px; text-transform: uppercase;}
        .comp-info p { margin: 2px 0 0 0; font-size: 8.5px; font-weight: 600; color: var(--gray); text-transform: uppercase;}
        .title-doc h2 { margin: 0; font-size: 14px; font-weight: 900; text-align: center; letter-spacing: 0.5px;}
        .title-doc p { margin: 2px 0 0 0; font-size: 10px; font-weight: 800; text-align: center; color: var(--blue); text-transform: uppercase;}
        .meta-doc { text-align: right; font-size: 8px; font-weight: 700; line-height: 1.4; text-transform: uppercase;}

        /* 📊 SUMMARY CARDS */
        .summary-grid { display: flex; gap: 10px; margin-bottom: 15px; }
        .card { flex: 1; border: 1.5px solid var(--dark); padding: 8px 12px; border-radius: 4px; }
        .card-title { font-size: 7.5px; font-weight: 800; text-transform: uppercase; margin-bottom: 4px; color: var(--gray); }
        .card-value { font-size: 16px; font-weight: 900; font-family: 'JetBrains Mono', monospace; }
        .text-green { color: var(--green); }
        .text-red { color: var(--red); }
        .card-ending { border-width: 2.5px; }

        /* 📋 TABEL MUTASI */
        table.mutation-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.mutation-table th, table.mutation-table td { border: 1px solid var(--dark); padding: 5px 6px; vertical-align: middle; }
        table.mutation-table th { background: #f8fafc; font-size: 7.5px; font-weight: 900; text-transform: uppercase; text-align: center; }
        
        .row-part-header td { background: #0f172a; color: #fff; font-size: 9px; font-weight: 800; padding: 4px 8px; }
        .part-stats { float: right; color: #93c5fd; font-family: 'JetBrains Mono', monospace; font-size: 8.5px;}
        .part-stats span { color: #fff; margin-left: 8px; }
        .part-stats span.in { color: #4ade80; }
        .part-stats span.out { color: #f87171; }
        .part-stats span.akhir { color: #facc15; }

        .row-empty td { background: #f8fafc; text-align: center; font-size: 8px; font-weight: 700; font-style: italic; color: var(--gray); }
        .row-data td { font-size: 8px; font-weight: 700; text-align: center; }
        .row-total td { background: #f1f5f9; font-weight: 900; font-size: 8.5px; text-transform: uppercase; }

        .mono { font-family: 'JetBrains Mono', monospace; }
        .badge-tipe { padding: 2px 6px; border-radius: 4px; font-size: 7px; font-weight: 900; text-transform: uppercase; border: 1px solid;}
        .badge-in { background: #dcfce7; color: var(--green); border-color: #86efac; }
        .badge-out { background: #fee2e2; color: var(--red); border-color: #fca5a5; }
        .badge-coil { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; padding: 2px 6px; border-radius: 4px; font-family: 'JetBrains Mono', monospace; font-weight: 800;}

        /* ✍️ FOOTER PENGESAHAN */
        .signature-grid { display: flex; justify-content: space-between; margin-top: 10px; gap: 15px;}
        .sign-box { flex: 1; border: 1px solid var(--border); padding: 6px; text-align: center; border-radius: 4px;}
        .sign-title { font-size: 7.5px; font-weight: 800; color: var(--gray); text-transform: uppercase; margin-bottom: 40px; }
        .sign-line { border-top: 1px solid var(--dark); margin: 0 10px; padding-top: 4px; font-size: 8px; font-weight: 900; text-transform: uppercase; }

        @media print { .no-print { display: none !important; } body { padding: 0; margin: 0; } }
    </style>
</head>
<body>

    @php 
        $grandAwal = $data->sum('stok_awal');
        $grandIn = $data->sum('daily_in_s') + $data->sum('daily_in_r');
        $grandOut = $data->sum('daily_out');
        $grandAkhir = $data->sum('stok_akhir_hari_ini');
    @endphp

    <!-- TOOLBAR FILTER -->
    <div class="no-print no-print-area">
        <form action="{{ url()->current() }}" method="GET" class="filter-group">
            <span style="font-weight: 800; font-size: 10px; text-transform:uppercase;">Mode Filter:</span>
            
            <select name="filter_type" id="filterType" onchange="toggleDateInput()">
                <option value="daily" {{ $filterType == 'daily' ? 'selected' : '' }}>Per Tanggal (Harian)</option>
                <option value="monthly" {{ $filterType == 'monthly' ? 'selected' : '' }}>Per Bulan (Bulanan)</option>
            </select>
            
            <input type="date" name="date" id="inputDate" value="{{ $targetDate }}" style="{{ $filterType == 'monthly' ? 'display:none;' : '' }}">
            <input type="month" name="month" id="inputMonth" value="{{ $selectedMonth }}" style="{{ $filterType == 'daily' ? 'display:none;' : '' }}">

            <select name="customer">
                <option value="ALL">-- SEMUA CLIENT / CUSTOMER --</option>
                @foreach($availableCustomers as $c)
                    <option value="{{ $c->code }}" {{ $customer == $c->code ? 'selected' : '' }}>{{ $c->code }} - {{ $c->name }}</option>
                @endforeach
            </select>

            <select name="part_size">
                <option value="ALL">-- SEMUA PART / SPESIFIKASI --</option>
                @if(isset($availableParts))
                    @foreach($availableParts as $p)
                        <option value="{{ $p->spec }}" {{ (isset($partSize) && $partSize == $p->spec) ? 'selected' : '' }}>
                            {{ $p->part_name }}
                        </option>
                    @endforeach
                @endif
            </select>

            <button type="submit" class="btn btn-filter">🔍 TERAPKAN FILTER</button>
        </form>
        <div>
            <button class="btn btn-back" onclick="window.length ? window.history.back() : window.close()">KEMBALI</button>
            <button class="btn btn-print" onclick="window.print()">🖨️ CETAK DOKUMEN (A4)</button>
        </div>
    </div>

    <!-- HEADER DOKUMEN -->
    <div class="header-container">
        <div class="comp-info">
            <h1>PT ASALTA MANDIRI AGUNG</h1>
            <p>PLANT KARAWANG // WAREHOUSE & LOGISTICS DEPARTMENT</p>
        </div>
        <div class="title-doc">
            <h2>RAW MATERIAL AUDIT & MUTATION LOG</h2>
            <p>{{ $periodTitle }}</p>
        </div>
        <div class="meta-doc">
            FORM NO : AMA-LOG-MUT-01<br>
            CLIENT SCOPE : {{ $customer && $customer !== 'ALL' ? $customer : 'ALL CLIENT ENTITIES' }}<br>
            PRINTED AT : {{ date('d/m/Y H:i') }}
        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="summary-grid">
        <div class="card">
            <div class="card-title">BEGINNING BALANCE (STOK AWAL)</div>
            <div class="card-value">{{ number_format($grandAwal) }} <span style="font-size:8px;">PCS</span></div>
        </div>
        <div class="card" style="border-color: var(--green);">
            <div class="card-title text-green">TOTAL INBOUND (SUPPLIER)</div>
            <div class="card-value text-green">+{{ number_format($grandIn) }}</div>
        </div>
        <div class="card" style="border-color: var(--red);">
            <div class="card-title text-red">TOTAL ISSUED (PRODUKSI)</div>
            <div class="card-value text-red">-{{ number_format($grandOut) }}</div>
        </div>
        <div class="card card-ending">
            <div class="card-title">ENDING STOCK BALANCE (STOK AKHIR)</div>
            <div class="card-value">{{ number_format($grandAkhir) }} <span style="font-size:8px;">PCS</span></div>
        </div>
    </div>

    <!-- TABEL DATA -->
    <table class="mutation-table">
        <thead>
            <tr>
                <th width="15%">WAKTU / PERIODE</th>
                <th width="20%">NO. DOKUMEN (SJ / WOS)</th>
                <th width="12%">TIPE MUTASI</th>
                <th width="33%">RINCIAN COIL / BUNDLE FISIK TERLIBAT</th>
                <th width="10%">MASUK (+)</th>
                <th width="10%">KELUAR (-)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
                @php 
                    $totalIn = $item->daily_in_s + $item->daily_in_r;
                    $totalOut = $item->daily_out;
                @endphp
                
                <!-- Header Part -->
                <tr class="row-part-header">
                    <td colspan="6">
                        <span style="color:#60a5fa;">[{{ $item->alias_code ?? $item->customer }}]</span> 
                        {{ $item->spec }} (DIM: {{ str_replace(' ', '', $item->size) }})
                        <div class="part-stats">
                            AWAL: {{ number_format($item->stok_awal) }}
                            <span class="in">IN: +{{ number_format($totalIn) }}</span>
                            <span class="out">OUT: -{{ number_format($totalOut) }}</span>
                            <span class="akhir">AKHIR: {{ number_format($item->stok_akhir_hari_ini) }} PCS</span>
                        </div>
                    </td>
                </tr>

                @if($totalIn == 0 && $totalOut == 0)
                    <tr class="row-empty">
                        <td colspan="6">-- Tidak ada mutasi fisik coil pada periode ini (Stok Tetap) --</td>
                    </tr>
                @else
                    <!-- Inbound Logs -->
                    @if(isset($item->coils_in) && count($item->coils_in) > 0)
                        @foreach($item->coils_in as $in)
                        <tr class="row-data">
                            <td class="mono" style="color: var(--gray);">{{ $periodTitle }}</td>
                            <td class="mono text-blue">{{ $in->no_dokumen ?: '-' }}</td>
                            <td><span class="badge-tipe badge-in">MASUK (SJ)</span></td>
                            <td><span class="badge-coil">🏷️ {{ $in->coil_id }} : {{ number_format($in->qty) }}</span></td>
                            <td class="mono text-green font-weight-bold">+{{ number_format($in->qty) }}</td>
                            <td class="mono text-red">-</td>
                        </tr>
                        @endforeach
                    @endif

                    <!-- Outbound Logs -->
                    @if(isset($item->coils_out) && count($item->coils_out) > 0)
                        @foreach($item->coils_out as $out)
                        <tr class="row-data">
                            <td class="mono" style="color: var(--gray);">{{ $periodTitle }}</td>
                            <td class="mono text-blue">{{ $out->no_dokumen ?: '-' }}</td>
                            <td><span class="badge-tipe badge-out">KELUAR (WOS)</span></td>
                            <td><span class="badge-coil">🏷️ {{ $out->coil_id }} : {{ number_format($out->qty) }}</span></td>
                            <td class="mono text-green">-</td>
                            <td class="mono text-red font-weight-bold">-{{ number_format($out->qty) }}</td>
                        </tr>
                        @endforeach
                    @endif
                @endif
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; font-weight: 800; color: var(--gray);">TIDAK ADA DATA MATERIAL YANG DITEMUKAN PADA FILTER INI.</td>
                </tr>
            @endforelse
            
            <!-- TOTAL GRAND ROW -->
            <tr class="row-total">
                <td colspan="4" style="text-align: right; padding-right: 15px;">TOTAL MUTASI KESELURUHAN (PERIODE INI) :</td>
                <td class="mono text-green">+{{ number_format($grandIn) }}</td>
                <td class="mono text-red">-{{ number_format($grandOut) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- FOOTER SIGNATURES -->
    <div class="signature-grid">
        <div class="sign-box">
            <div class="sign-title">DISIAPKAN OLEH (WAREHOUSE)</div>
            <div class="sign-line">ADMIN GUDANG RM</div>
        </div>
        <div class="sign-box">
            <div class="sign-title">DIVALIDASI OLEH (PPIC)</div>
            <div class="sign-line">PPIC SUPERVISOR</div>
        </div>
        <div class="sign-box">
            <div class="sign-title">DISETUJUI OLEH (PLANT)</div>
            <div class="sign-line">PLANT MANAGER</div>
        </div>
    </div>

    <script>
        function toggleDateInput() {
            var filterType = document.getElementById('filterType').value;
            if (filterType === 'monthly') {
                document.getElementById('inputDate').style.display = 'none';
                document.getElementById('inputMonth').style.display = 'inline-block';
            } else {
                document.getElementById('inputDate').style.display = 'inline-block';
                document.getElementById('inputMonth').style.display = 'none';
            }
        }
    </script>
</body>
</html>