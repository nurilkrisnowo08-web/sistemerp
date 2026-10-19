<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RM_MUTATION_LEDGER_{{ strtoupper($filterType ?? 'DAILY') }}_{{ date('Ymd', strtotime($targetDate ?? $startDate ?? now())) }}</title>
    <!-- Font Standar Industri Manufaktur -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --dark: #0f172a; 
            --slate: #475569;
            --muted: #64748b;
            --border-thick: #0f172a;
            --border-thin: #cbd5e1;
            --stamp-blue: #1d4ed8;
            --success-green: #047857;
            --danger-red: #b91c1c;
            --return-indigo: #4338ca;
        }
        
        * { box-sizing: border-box; }
        
        @page { 
            size: A4 landscape; 
            margin: 8mm 10mm; 
        }

        body { 
            font-family: 'Plus Jakarta Sans', Arial, sans-serif; 
            font-size: 9px; 
            color: var(--dark); 
            margin: 0; 
            padding: 12px; 
            background: #fff; 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact; 
        }

        .report-wrapper { max-width: 100%; margin: 0 auto; position: relative; }

        /* 🔘 SCREEN ACTION & FILTER BAR */
        .no-print-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            border: 1px solid #cbd5e1;
            flex-wrap: wrap;
            gap: 10px;
        }
        .filter-form {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .filter-select, .filter-input {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 10px;
            font-weight: 700;
            padding: 6px 10px;
            border: 1.5px solid #94a3b8;
            border-radius: 6px;
            background: #ffffff;
            color: var(--dark);
            outline: none;
        }
        .filter-select:focus, .filter-input:focus {
            border-color: #2563eb;
        }
        .btn-screen {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 11px;
            padding: 7px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
        }
        .btn-apply { background: #0f172a; color: #fff; }
        .btn-apply:hover { background: #1e293b; }
        .btn-close { background: #64748b; color: #fff; }
        .btn-print { background: #2563eb; color: #fff; }

        /* 🏛️ INDUSTRIAL DOCUMENT HEADER */
        .doc-header { 
            display: table; 
            width: 100%; 
            border-bottom: 3.5px solid var(--border-thick); 
            padding-bottom: 10px; 
            margin-bottom: 12px; 
        }
        .header-col { display: table-cell; vertical-align: middle; }
        
        .company-identity { width: 35%; }
        .company-identity h2 { margin: 0; font-size: 18px; font-weight: 900; letter-spacing: 0.5px; }
        .company-identity p { margin: 2px 0 0; font-size: 8.5px; color: var(--muted); font-weight: 700; text-transform: uppercase; }

        .report-title-box { width: 40%; text-align: center; }
        .report-title-box h1 { margin: 0; font-size: 15px; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; }
        .report-title-box span { font-size: 10px; font-weight: 800; color: #2563eb; font-family: 'JetBrains Mono', monospace; display: block; margin-top: 2px; }

        .doc-meta { width: 25%; text-align: right; font-size: 8.5px; line-height: 1.4; font-weight: 600; }
        .doc-meta b { color: var(--dark); font-weight: 800; }

        /* 📊 KPI METRIC STRIP */
        .kpi-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 12px;
        }
        .kpi-card {
            flex: 1;
            border: 1px solid var(--border-thick);
            padding: 6px 12px;
            background: #f8fafc;
            border-radius: 4px;
        }
        .kpi-title { font-size: 7.5px; text-transform: uppercase; font-weight: 800; color: var(--muted); }
        .kpi-value { font-family: 'JetBrains Mono', monospace; font-size: 13.5px; font-weight: 800; color: var(--dark); margin-top: 1px; }

        /* 📋 INDUSTRIAL TABLE */
        table.recap-table { 
            width: 100%; 
            border-collapse: collapse; 
            border: 1.5px solid var(--border-thick); 
            margin-bottom: 15px;
        }
        table.recap-table th { 
            border: 1px solid var(--border-thick); 
            padding: 6px 4px; 
            font-size: 8px; 
            font-weight: 800; 
            text-transform: uppercase; 
            background: #f1f5f9 !important; 
            text-align: center;
            letter-spacing: 0.5px;
        }
        table.recap-table td { 
            border: 1px solid #cbd5e1; 
            padding: 5px 6px; 
            vertical-align: middle; 
            font-size: 8.5px;
        }

        .text-left { text-align: left !important; }
        .text-center { text-align: center !important; }
        .num-val { text-align: right !important; font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 9.5px; }

        /* 🏷️ MATERIAL GROUP HEADER BAR */
        tr.mat-group-row {
            background: #0f172a !important;
            color: #ffffff !important;
            font-weight: 800;
        }
        tr.mat-group-row td {
            border: 1px solid var(--border-thick);
            padding: 6px 10px;
        }
        .mat-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9.5px;
        }
        .mat-stat-pills {
            display: flex;
            gap: 12px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 9px;
        }
        .pill-awal { color: #93c5fd; }
        .pill-in { color: #86efac; }
        .pill-out { color: #fca5a5; }
        .pill-final { color: #fde047; font-weight: 900; }

        /* 🏷️ BADGE MUTASI */
        .badge-type {
            font-family: 'JetBrains Mono', monospace;
            font-size: 7.5px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            display: inline-block;
        }
        .type-in { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .type-return { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }
        .type-out { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        /* 🏷️ BREAKDOWN COIL CHIP */
        .coil-tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        .coil-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 7.5px;
            font-weight: 700;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 2px 6px;
            border-radius: 3px;
            color: #1e293b;
            white-space: nowrap;
        }

        .val-in-s { color: var(--success-green); }
        .val-out { color: var(--danger-red); }

        tr.grand-total {
            background: #e2e8f0 !important;
            font-weight: 900;
            border-top: 2px solid var(--border-thick);
            border-bottom: 2px solid var(--border-thick);
        }

        /* ✍️ SIGNATURE SECTION & STEMPEL */
        .doc-footer { 
            display: table; 
            width: 100%; 
            margin-top: 15px; 
            page-break-inside: avoid; 
        }
        .sig-col { 
            display: table-cell; 
            width: 33.33%; 
            text-align: center; 
            vertical-align: top; 
            padding: 0 25px; 
        }
        .sig-box { 
            border: 1px solid #cbd5e1; 
            background: #fff; 
            border-radius: 6px; 
            padding: 8px; 
            position: relative; 
            height: 95px;
        }
        .sig-title { font-size: 8.5px; font-weight: 800; text-transform: uppercase; color: var(--muted); margin-bottom: 45px; }
        .sig-name { font-size: 9.5px; font-weight: 800; text-transform: uppercase; border-top: 1px solid var(--border-thick); padding-top: 3px; }

        .stamp-wrapper {
            position: absolute;
            top: 12px;
            left: 50%;
            transform: translateX(-50%) rotate(-12deg);
            width: 82px;
            height: 82px;
            border: 2.5px dashed var(--stamp-blue);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--stamp-blue);
            opacity: 0.85;
            pointer-events: none;
            background: rgba(255, 255, 255, 0.45);
            text-align: center;
        }
        .stamp-top { font-size: 5.5px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; }
        .stamp-mid { font-size: 8px; font-weight: 900; border-top: 1px solid var(--stamp-blue); border-bottom: 1px solid var(--stamp-blue); padding: 1.5px 0; margin: 2px 0; width: 85%; }
        .stamp-bot { font-size: 5.5px; font-weight: 800; text-transform: uppercase; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>

    {{-- 🔘 SCREEN ACTION & FILTER BAR --}}
    <div class="no-print no-print-bar">
        <form id="filterForm" action="{{ url()->current() }}" method="GET" class="filter-form">
            <input type="hidden" name="start_date" id="hidden_start_date" value="{{ $startDate ?? date('Y-m-d') }}">
            <input type="hidden" name="end_date" id="hidden_end_date" value="{{ $endDate ?? date('Y-m-d') }}">

            <span style="font-weight: 800;">MODE FILTER:</span>
            
            <select id="mode_selector" class="filter-select" onchange="toggleFilterMode(this.value)">
                <option value="daily" {{ ($filterType ?? 'daily') === 'daily' ? 'selected' : '' }}>📅 Per Tanggal (Harian)</option>
                <option value="monthly" {{ ($filterType ?? '') === 'monthly' ? 'selected' : '' }}>🗓️ Per Bulan (Bulanan)</option>
            </select>

            <span id="date-input-wrap" style="{{ ($filterType ?? 'daily') === 'monthly' ? 'display:none;' : '' }}">
                <input type="date" id="date_picker" class="filter-input" value="{{ $startDate ?? date('Y-m-d') }}" onchange="syncDates()">
            </span>

            <span id="month-input-wrap" style="{{ ($filterType ?? 'daily') === 'monthly' ? '' : 'display:none;' }}">
                <input type="month" id="month_picker" class="filter-input" value="{{ $selectedMonth ?? date('Y-m', strtotime($startDate ?? now())) }}" onchange="syncDates()">
            </span>

            <select name="customer" class="filter-select">
                <option value="">-- SEMUA CLIENT / CUSTOMER --</option>
                @foreach($availableCustomers ?? [] as $c)
                    <option value="{{ $c->code }}" {{ ($customer ?? '') === $c->code ? 'selected' : '' }}>{{ $c->name }} ({{$c->code }})</option>
                @endforeach
            </select>

            <button type="submit" class="btn-screen btn-apply" onclick="prepareSubmit()">🔍 TERAPKAN FILTER</button>
        </form>

        <div>
            <button class="btn-screen btn-close" onclick="window.history.back()">KEMBALI</button>
            <button class="btn-screen btn-print ml-2" onclick="window.print()">🖨️ CETAK DOKUMEN (A4)</button>
        </div>
    </div>

    @php
        $mutationData = $historyData ?? $data ?? collect();
        $sumAwal = 0; $sumIn = 0; $sumOut = 0; $sumAkhir = 0;

        foreach($mutationData as $mat) {$sumAwal += ($mat->initial ?? $mat->stok_awal ?? 0);
            $sumIn += ($mat->in_qty ?? ($mat->daily_in_s ?? 0) + ($mat->daily_in_r ?? 0));
            $sumOut += ($mat->out_qty ?? ($mat->daily_out ?? 0));
            $sumAkhir += ($mat->final ?? $mat->stok_akhir_hari_ini ?? 0);
        }

        $formattedPeriod = ($startDate ===$endDate)
            ? date('d F Y', strtotime($startDate))
            : date('d M Y', strtotime($startDate)) . ' s/d ' . date('d M Y', strtotime($endDate));
    @endphp

    <div class="report-wrapper">
        {{-- Header Dokumen Resmi --}}
        <div class="doc-header">
            <div class="header-col company-identity">
                <h2>PT ASALTA MANDIRI AGUNG</h2>
                <p>Plant Karawang // Warehouse & Logistics Department</p>
            </div>
            <div class="header-col report-title-box">
                <h1>RAW MATERIAL MUTATION LEDGER</h1>
                <span>PERIODE: {{ $formattedPeriod }}</span>
            </div>
            <div class="header-col doc-meta">
                FORM NO : <b>AMA-LOG-MUT-02</b><br>
                CLIENT SCOPE : <b>{{ !empty($customer) ? strtoupper($customer) : 'ALL CLIENT ENTITIES' }}</b><br>
                PRINTED AT : {{ date('d/m/Y H:i') }}
            </div>
        </div>

        {{-- KPI Ribbon --}}
        <div class="kpi-bar">
            <div class="kpi-card">
                <div class="kpi-title">Beginning Balance (Stok Awal)</div>
                <div class="kpi-value">{{ number_format($sumAwal) }} <span style="font-size:8px;">PCS</span></div>
            </div>
            <div class="kpi-card" style="border-left: 3px solid var(--success-green);">
                <div class="kpi-title" style="color: var(--success-green);">Total Inbound (Supplier)</div>
                <div class="kpi-value" style="color: var(--success-green);">+{{ number_format($sumIn) }}</div>
            </div>
            <div class="kpi-card" style="border-left: 3px solid var(--danger-red);">
                <div class="kpi-title" style="color: var(--danger-red);">Total Issued (Produksi)</div>
                <div class="kpi-value" style="color: var(--danger-red);">-{{ number_format($sumOut) }}</div>
            </div>
            <div class="kpi-card" style="border-left: 4px solid var(--border-thick); background:#e2e8f0;">
                <div class="kpi-title" style="color: var(--dark);">Ending Stock Balance (Stok Akhir)</div>
                <div class="kpi-value">{{ number_format($sumAkhir) }} <span style="font-size:8px;">PCS</span></div>
            </div>
        </div>

        {{-- Tabel Mutasi Utama Terjabar --}}
        <table class="recap-table">
            <thead>
                <tr>
                    <th width="12%">Waktu / Tanggal</th>
                    <th width="20%">No. Dokumen (SJ / WOS)</th>
                    <th width="13%">Tipe Mutasi</th>
                    <th width="35%">Rincian Coil / Bundle Fisik Terlibat</th>
                    <th width="10%">Masuk (+)</th>
                    <th width="10%">Keluar (-)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mutationData as$mat)
                    @php
                        $initVal = $mat->initial ?? $mat->stok_awal ?? 0;
                        $inVal =$mat->in_qty ?? (($mat->daily_in_s ?? 0) + ($mat->daily_in_r ?? 0));
                        $outVal = $mat->out_qty ?? ($mat->daily_out ?? 0);
                        $finalVal = $mat->final ?? $mat->stok_akhir_hari_ini ?? 0;
                        $logs = $mat->logs ?? $mat->combined_logs ?? collect();
                    @endphp

                    {{-- Baris Header Material Group --}}
                    <tr class="mat-group-row">
                        <td colspan="6">
                            <div class="mat-title">
                                <div>
                                    <span style="color:#60a5fa;">[{{ $mat->alias ?? $mat->alias_code ?? 'UNREGISTERED' }}]</span> 
                                    <span>{{ $mat->spec }}</span> 
                                    <span style="color:#94a3b8; font-size:8px;">(DIM: {{ $mat->size }})</span>
                                    @if(isset($mat->customer))
                                        <span style="color:#cbd5e1; font-size:8px;">// {{ $mat->customer }}</span>
                                    @endif
                                </div>
                                <div class="mat-stat-pills">
                                    <span class="pill-awal">AWAL: {{ number_format($initVal) }}</span>
                                    <span class="pill-in">IN: +{{ number_format($inVal) }}</span>
                                    <span class="pill-out">OUT: -{{ number_format($outVal) }}</span>
                                    <span class="pill-final">AKHIR: {{ number_format($finalVal) }} PCS</span>
                                </div>
                            </div>
                        </td>
                    </tr>

                    {{-- Baris Rincian Log Mutasi --}}
                    @forelse($logs as$log)
                        @php
                            $logType =$log->type ?? 'MUTATION';
                            $refId = $log->ref_id ?? $log->no_sj ?? '-';
                            $qty = $log->qty ?? $log->total_qty ?? 0;
                            $createdAt =$log->created_at ?? now();
                            $bundles =$log->bundles ?? [];
                        @endphp
                        <tr>
                            <td class="text-center font-weight-bold" style="font-family:'JetBrains Mono'; font-size:8px;">
                                {{ date('d/m/Y H:i', strtotime($createdAt)) }}
                            </td>
                            <td class="text-left font-weight-bold" style="font-family:'JetBrains Mono'; color:#1e40af;">
                                {{ $refId }}
                            </td>
                            <td class="text-center">
                                @if(str_contains(strtoupper($logType), 'RETURN') || (isset($log->source) &&$log->source === 'return'))
                                    <span class="badge-type type-return">RETURN (PROD)</span>
                                @elseif(str_contains(strtoupper($logType), 'IN'))
                                    <span class="badge-type type-in">MASUK (SJ)</span>
                                @else
                                    <span class="badge-type type-out">KELUAR (WOS)</span>
                                @endif
                            </td>
                            <td class="text-left">
                                <div class="coil-tag-list">
                                    @if(count($bundles) > 0)
                                        @foreach($bundles as$b)
                                            @php
                                                $cId = is_object($b) ? ($b->coil_id ?? 'COIL') : ($b['coil_id'] ?? 'COIL');
                                                $bQty = is_object($b) ? ($b->qty ?? $b->pcs_in ?? $b->pcs_used ?? 0) : ($b['qty'] ?? 0);
                                            @endphp
                                            <span class="coil-tag">
                                                🏷️ {{ $cId }}: <b>{{ number_format(abs($bQty)) }}</b>
                                            </span>
                                        @endforeach
                                    @elseif(isset($log->coil_id))
                                        <span class="coil-tag">
                                            🏷️ {{ $log->coil_id }}: <b>{{ number_format(abs($qty)) }}</b>
                                        </span>
                                    @else
                                        <span class="text-muted italic" style="font-size:7.5px;">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="num-val val-in-s">
                                {{ (str_contains(strtoupper($logType), 'IN') || $qty > 0) ? '+'.number_format(abs($qty)) : '-' }}
                            </td>
                            <td class="num-val val-out">
                                {{ (!str_contains(strtoupper($logType), 'IN') &&$qty < 0 || str_contains(strtoupper($logType), 'EXIT')) ? '-'.number_format(abs($qty)) : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-2 text-muted italic" style="background:#fafafa;">
                                -- Tidak ada mutasi fisik coil pada periode ini (Stok Tetap) --
                            </td>
                        </tr>
                    @endforelse

                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted italic">
                            -- Tidak ada data mutasi raw material yang ditemukan pada periode ini --
                        </td>
                    </tr>
                @endforelse

                {{-- Baris Grand Total --}}
                @if(count($mutationData) > 0)
                <tr class="grand-total">
                    <td colspan="4" class="text-left" style="padding-left: 12px; font-weight: 900; font-size: 10px;">
                        TOTAL MUTASI KESELURUHAN (PERIODE INI) :
                    </td>
                    <td class="num-val val-in-s" style="font-size: 11px;">+{{ number_format($sumIn) }}</td>
                    <td class="num-val val-out" style="font-size: 11px;">-{{ number_format($sumOut) }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        {{-- Lembar Otorisasi & Stempel Resmi --}}
        <div class="doc-footer">
            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Disiapkan Oleh (Warehouse)</div>
                    <div class="sig-name">Admin Gudang RM</div>
                </div>
            </div>

            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Divalidasi Oleh (PPIC)</div>
                    <div class="stamp-wrapper">
                        <span class="stamp-top">PT ASALTA MANDIRI AGUNG</span>
                        <span class="stamp-mid">LOGISTICS DEPT</span>
                        <span class="stamp-bot">KARAWANG PLANT</span>
                    </div>
                    <div class="sig-name">PPIC Supervisor</div>
                </div>
            </div>

            <div class="sig-col">
                <div class="sig-box">
                    <div class="sig-title">Disetujui Oleh (Plant)</div>
                    <div class="sig-name">Plant Manager</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleFilterMode(mode) {
            const dateWrap = document.getElementById('date-input-wrap');
            const monthWrap = document.getElementById('month-input-wrap');
            if (mode === 'monthly') {
                dateWrap.style.display = 'none';
                monthWrap.style.display = 'inline-block';
            } else {
                dateWrap.style.display = 'inline-block';
                monthWrap.style.display = 'none';
            }
            syncDates();
        }

        function syncDates() {
            const mode = document.getElementById('mode_selector').value;
            const startInput = document.getElementById('hidden_start_date');
            const endInput = document.getElementById('hidden_end_date');

            if (mode === 'monthly') {
                const monthVal = document.getElementById('month_picker').value;
                if (monthVal) {
                    const parts = monthVal.split('-');
                    const year = parseInt(parts[0], 10);
                    const month = parseInt(parts[1], 10);
                    const lastDay = new Date(year, month, 0).getDate();
                    
                    const padMonth = String(month).padStart(2, '0');
                    startInput.value = `${year}-${padMonth}-01`;
                    endInput.value = `${year}-${padMonth}-${lastDay}`;
                }
            } else {
                const dateVal = document.getElementById('date_picker').value;
                if (dateVal) {
                    startInput.value = dateVal;
                    endInput.value = dateVal;
                }
            }
        }

        function prepareSubmit() {
            syncDates();
        }

        window.addEventListener('DOMContentLoaded', () => {
            syncDates();
        });
    </script>

</body>
</html>