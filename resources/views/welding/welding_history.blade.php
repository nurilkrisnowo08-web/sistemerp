@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

@php
    // 🔥 FILTER LOGIC & QUERY WOS OTOMATIS 🔥
    $start = $startDate ?? date('Y-m-d');
    $end = $endDate ?? date('Y-m-d');
    $custFilter = request('customer') ?? null;
    $partFilter = request('part_no') ?? null;
    $wosFilter = request('wos') ?? null; // ✨ Menangkap inputan pencarian WOS

    // 1. Dropdown Part Berdasarkan Customer
    $availablePartsQuery = DB::table('finished_goods')->select('part_no', 'part_name')->orderBy('part_no', 'asc');
    if ($custFilter && $custFilter != 'ALL') {
        $availablePartsQuery->where('customer', trim($custFilter));
    }
    $availableParts = $availablePartsQuery->get();

    // 2. Saring Data History Berdasarkan Part Number
    if ($partFilter) {
        $historyData = $historyData->filter(function($item) use ($partFilter) {
            return $item->part_no == $partFilter;
        });
    }

    // 3. Ambil WOS dari Stamping (IN)
    $inStamping = DB::table('production_logs')
        ->where('process_type', 'WELDING')
        ->whereBetween(DB::raw('DATE(created_at)'), [$start, $end])
        ->get()->map(function($item) {
            return (object)[
                'jam' => $item->created_at,
                'part_no' => str_replace([' ', '-'], '', trim($item->part_no)),
                'no_dokumen' => $item->batch_id ?? $item->no_wos ?? $item->no_produksi ?? 'WOS-STAMP-SYSTEM',
                'in_qty' => $item->qty,
                'out_qty' => 0,
                'type' => 'IN_STAMPING',
                'label' => 'IN (STAMPING)',
                'icon' => 'fa-arrow-down',
                'color' => 'success'
            ];
        });

    // 4. Ambil WOS dari Return QC (IN)
    $inReturn = DB::table('quality_inspections')
        ->where('origin', 'WELDING')
        ->whereBetween(DB::raw('DATE(created_at)'), [$start, $end])
        ->where('qty_ret', '>', 0) 
        ->get()->map(function($item) {
            return (object)[
                'jam' => $item->created_at,
                'part_no' => str_replace([' ', '-'], '', trim($item->part_no)),
                'no_dokumen' => $item->batch_no ?? 'RET-QC-SYSTEM',
                'in_qty' => $item->qty_ret,
                'out_qty' => 0,
                'type' => 'IN_RETURN',
                'label' => 'IN (RETURN QC)',
                'icon' => 'fa-undo',
                'color' => 'indigo'
            ];
        });

    // 5. Ambil WOS dari Welding (OUT)
    $outWelding = DB::table('welding_batches')
        ->whereBetween(DB::raw('DATE(created_at)'), [$start, $end])
        ->get()->map(function($item) {
            return (object)[
                'jam' => $item->created_at,
                'part_no' => str_replace([' ', '-'], '', trim($item->part_no)),
                'no_dokumen' => $item->no_produksi_stamping ?? 'WLD-DEPLOY-SYS',
                'in_qty' => 0,
                'out_qty' => $item->qty_masuk,
                'type' => 'OUT_WELDING',
                'label' => 'OUT (WELDING)',
                'icon' => 'fa-fire',
                'color' => 'danger'
            ];
        });

    // 6. Gabungkan semua mutasi
    $allMutations = $inStamping->concat($inReturn)->concat($outWelding)->sortByDesc('jam');

    // ✨ 7. LOGIKA PENCARIAN WOS ✨
    if ($wosFilter) {
        // Hanya ambil mutasi yang nomor dokumennya mengandung kata kunci WOS
        $allMutations = $allMutations->filter(function($item) use ($wosFilter) {
            return stripos($item->no_dokumen, $wosFilter) !== false;
        });

        // Saring parent data (Part Card) agar hanya menampilkan barang yang terkait dengan WOS tersebut
        $partsWithWos = $allMutations->pluck('part_no')->unique()->toArray();
        $historyData = $historyData->filter(function($item) use ($partsWithWos) {
            return in_array(str_replace([' ', '-'], '', trim($item->part_no)), $partsWithWos);
        });
    }

    // Kelompokkan hasil akhir berdasarkan Part Number
    $allMutations = $allMutations->groupBy('part_no');
@endphp

<style>
    :root {
        --brand-primary: #3b82f6; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-return: #8b5cf6; --dark-surface: #0f172a; --bg-main: #f8fafc;
        --glass-border: rgba(226, 232, 240, 0.8);
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #1e293b; }
    
    .sys-title { font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 28px; color: #1e293b; letter-spacing: -0.5px; }
    .sys-title span { color: var(--brand-primary); }
    .sys-subtitle { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }

    /* Filter Bar Modern */
    .filter-bar { background: #fff; border-radius: 18px; border: 1px solid var(--glass-border); padding: 15px 20px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; flex-wrap: wrap; gap: 15px; align-items: center; justify-content: space-between; }
    .filter-inputs { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
    
    .search-wos { background: #eff6ff; border: 1px solid #bfdbfe; font-family: 'JetBrains Mono'; font-weight: bold; padding: 8px 15px; border-radius: 10px; width: 200px; outline: none; font-size: 12px; color: #1d4ed8; transition: 0.3s; }
    .search-wos:focus { border-color: var(--brand-primary); background: #fff; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .search-wos::placeholder { color: #93c5fd; font-family: 'Plus Jakarta Sans'; font-weight: 600; }

    .f-select { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 15px; font-weight: 700; font-size: 12px; color: #475569; outline: none; transition: 0.3s; }
    .f-select:focus { border-color: var(--brand-primary); background: #fff; }

    /* Part Card Grouping */
    .part-group-card { background: #fff; border-radius: 20px; border: 1px solid var(--glass-border); margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); overflow: hidden; transition: 0.3s; }
    .part-group-card:hover { border-color: #cbd5e1; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .part-header { background: #f8fafc; padding: 20px 25px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .part-title { font-size: 18px; font-weight: 900; color: var(--dark-surface); font-family: 'JetBrains Mono', monospace; }
    .part-desc { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px; }
    
    /* Summary inside Part Card */
    .summary-pills { display: flex; gap: 15px; }
    .s-pill { padding: 8px 15px; border-radius: 12px; font-size: 11px; font-weight: 800; border: 1px solid #e2e8f0; display: flex; align-items: center; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
    .s-pill.open { border-left: 4px solid #94a3b8; }
    .s-pill.close { border-left: 4px solid var(--brand-primary); }
    .s-val { font-family: 'Orbitron', sans-serif; font-size: 16px; font-weight: 900; margin-left: 10px; }

    /* Detail Table */
    .detail-table { width: 100%; font-size: 12px; }
    .detail-table th { background: #fff; color: #64748b; font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 15px 25px; border-bottom: 1px solid #e2e8f0; text-align: center; }
    .detail-table td { padding: 15px 25px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; text-align: center; font-weight: 600; }
    .detail-table tbody tr:hover { background-color: #f8fafc; }
    
    .wos-badge { padding: 6px 12px; border-radius: 8px; font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 800; background: #eff6ff; color: var(--brand-primary); border: 1px solid #bfdbfe; display: inline-block; }
    
    .type-badge { font-size: 10px; font-weight: 800; padding: 5px 12px; border-radius: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
    .type-success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
    .type-indigo { background: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
    .type-danger { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

    .qty-in { color: var(--brand-success); font-weight: 900; font-family: 'JetBrains Mono'; font-size: 14px; }
    .qty-ret { color: var(--brand-return); font-weight: 900; font-family: 'JetBrains Mono'; font-size: 14px; }
    .qty-out { color: var(--brand-danger); font-weight: 900; font-family: 'JetBrains Mono'; font-size: 14px; }

    .btn-cyber { border-radius: 12px; font-weight: 800; padding: 8px 20px; letter-spacing: 0.5px; transition: 0.3s; }

    /* Print Styling */
    @media print {
        body { background: #fff; }
        .no-print { display: none !important; }
        .part-group-card { border: 1px solid #000; margin-bottom: 20px; page-break-inside: avoid; box-shadow: none; border-radius: 0; }
        .part-header { background: #f2f2f2 !important; -webkit-print-color-adjust: exact; border-bottom: 1px solid #000; }
        .detail-table th, .detail-table td { border: 1px solid #000; padding: 8px !important; }
        .s-pill { border: 1px solid #000; border-radius: 0; }
        .wos-badge, .type-badge { border: 1px solid #000; background: transparent !important; color: #000 !important; border-radius: 0; }
        #printArea { display: block !important; position: absolute; left: 0; top: 0; width: 100%; background: white; color: black; font-family: Arial, sans-serif; }
    }
</style>

{{-- AREA CETAK (Hanya Muncul Saat Print) --}}
<div id="printArea" style="display: none;">
    <div style="border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 15px; text-align: center;">
        <h1 style="margin:0; font-size: 20px; font-weight: bold;">PT. ASALTA MANDIRI AGUNG</h1>
        <h3 style="margin:5px 0; font-size: 14px; letter-spacing: 1px;">DEPARTEMEN PPIC - WELDING VAULT LEDGER (TRACEABILITY)</h3>
        <p style="margin:0; font-size: 11px;">
            Customer: <strong>{{ request('customer') ?: 'ALL' }}</strong> | Part: <strong>{{ request('part_no') ?: 'ALL' }}</strong>
            @if($wosFilter) | Filter WOS: <strong>{{ $wosFilter }}</strong> @endif
            <br>
            Periode Laporan: <strong>{{ date('d/m/Y', strtotime($start)) }}</strong> s/d <strong>{{ date('d/m/Y', strtotime($end)) }}</strong>
        </p>
    </div>
</div>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn no-print">
    
    {{-- HEADER JUDUL --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <div class="sys-title">WELDING_VAULT <span>LEDGER</span></div>
            <div class="sys-subtitle mt-1">MUTASI MATERIAL & TRACEABILITY WOS</div>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <button onclick="window.print()" class="btn btn-dark btn-cyber shadow-sm">
                <i class="fas fa-print mr-2"></i> PRINT TRACEABILITY
             </button>
            <a href="{{ route('welding.index') }}" class="btn btn-outline-secondary btn-cyber bg-white">
                <i class="fas fa-arrow-left mr-2"></i> KEMBALI
            </a>
        </div>
    </div>

    {{-- FILTER BAR MODERN --}}
    <div class="filter-bar">
        <form action="" method="GET" class="d-flex w-100 justify-content-between flex-wrap" style="gap: 15px;">
            <div class="filter-inputs">
                <!-- ✨ INPUT PENCARIAN WOS ✨ -->
                <div style="position: relative;">
                    <i class="fas fa-barcode text-primary" style="position: absolute; left: 12px; top: 10px; font-size: 12px;"></i>
                    <input type="text" name="wos" class="search-wos pl-5" placeholder="Scan / Cari No WOS..." value="{{ $wosFilter }}">
                </div>

                <select name="customer" class="f-select" onchange="this.form.submit()" style="width: 140px;">
                    <option value="">-- CUSTOMER --</option>
                    <option value="ALL" {{ $custFilter == 'ALL' ? 'selected' : '' }}>SEMUA (ALL)</option>
                    @if(isset($clients))
                        @foreach($clients as $c)
                            <option value="{{ $c->code }}" {{ $custFilter == $c->code ? 'selected' : '' }}>{{ $c->code }} - {{ $c->name }}</option>
                        @endforeach
                    @endif
                </select>

                <select name="part_no" class="f-select" onchange="this.form.submit()" style="width: 160px;">
                    <option value="">-- SEMUA PART --</option>
                    @foreach($availableParts as $p)
                        <option value="{{ $p->part_no }}" {{ $partFilter == $p->part_no ? 'selected' : '' }}>{{ $p->part_no }}</option>
                    @endforeach
                </select>

                <div class="d-flex align-items-center bg-light px-2 rounded-lg border" style="height: 35px; border-radius: 10px;">
                    <input type="date" name="start_date" class="border-0 bg-transparent font-weight-bold text-secondary" style="font-size: 12px; outline:none;" value="{{ $start }}">
                    <span class="mx-2 text-muted" style="font-size: 10px;"><i class="fas fa-arrow-right"></i></span>
                    <input type="date" name="end_date" class="border-0 bg-transparent font-weight-bold text-secondary" style="font-size: 12px; outline:none;" value="{{ $end }}">
                </div>
            </div>
            <div>
                <button type="submit" class="btn btn-primary btn-cyber"><i class="fas fa-filter mr-2"></i> TERAPKAN FILTER</button>
            </div>
        </form>
    </div>

    {{-- DAFTAR MUTASI DIKELOMPOKKAN PER PART NUMBER --}}
    @forelse($historyData as $h)
        @php
            $cleanPart = str_replace([' ', '-'], '', trim($h->part_no));
            // Ambil mutasi yang sudah disaring (termasuk filter WOS)
            $mutations = $allMutations->get($cleanPart) ?? collect();
        @endphp

        <div class="part-group-card animate__animated animate__fadeInUp">
            {{-- Header Part & Rekap Saldo --}}
            <div class="part-header flex-column flex-md-row">
                <div class="mb-3 mb-md-0">
                    <div class="part-title"><i class="fas fa-cube mr-2 text-primary"></i> {{ $h->part_no }}</div>
                    <div class="part-desc">{{ $h->part_name }}</div>
                </div>
                <div class="summary-pills">
                    <div class="s-pill open">
                        <span class="text-muted">OPENING SALDO</span>
                        <span class="s-val text-dark">{{ number_format($h->stock_awal) }}</span>
                    </div>
                    <div class="s-pill close">
                        <span class="text-primary">LIVE BALANCE</span>
                        <span class="s-val text-primary">{{ number_format($h->stock_akhir) }}</span>
                    </div>
                </div>
            </div>

            {{-- Tabel Rincian WOS --}}
            <div class="table-responsive">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th class="text-left pl-4" style="width: 15%;">TANGGAL & JAM</th>
                            <th style="width: 15%;">JENIS TRANSAKSI</th>
                            <th class="text-left" style="width: 40%;">WOS / DOKUMEN REFERENSI</th>
                            <th style="width: 15%;">MATERIAL MASUK (IN)</th>
                            <th style="width: 15%;">MATERIAL KELUAR (OUT)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($mutations->count() > 0)
                            @foreach($mutations as $mut)
                                <tr>
                                    <td class="text-left pl-4">
                                        <div class="font-weight-bold text-dark">{{ date('d/m/Y', strtotime($mut->jam)) }}</div>
                                        <div class="text-muted font-weight-bold" style="font-size: 10px;">{{ date('H:i:s', strtotime($mut->jam)) }}</div>
                                    </td>
                                    <td>
                                        <span class="type-badge type-{{ $mut->color }}">
                                            <i class="fas {{ $mut->icon }} mr-1"></i> {{ $mut->label }}
                                        </span>
                                    </td>
                                    <td class="text-left">
                                        <span class="wos-badge"><i class="fas fa-qrcode mr-2 opacity-50"></i>{{ strtoupper($mut->no_dokumen) }}</span>
                                    </td>
                                    <td>
                                        @if($mut->in_qty > 0)
                                            <span class="{{ $mut->type == 'IN_RETURN' ? 'qty-ret' : 'qty-in' }}">+{{ number_format($mut->in_qty) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($mut->out_qty > 0)
                                            <span class="qty-out">-{{ number_format($mut->out_qty) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            
                            {{-- Baris Total Bawah --}}
                            <tr style="background: #f8fafc; border-top: 2px solid #e2e8f0;">
                                <td colspan="3" class="text-right font-weight-bold text-muted uppercase pr-4" style="font-size: 10px;">TOTAL PERGERAKAN PERIODE INI:</td>
                                <td class="qty-in" style="font-size: 12px;">+{{ number_format($h->in_s + $h->in_r) }}</td>
                                <td class="qty-out" style="font-size: 12px;">-{{ number_format($h->total_out) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="5" class="py-4 text-muted font-weight-bold" style="font-style: italic; font-size: 11px;">
                                    <i class="fas fa-info-circle mr-1"></i> Belum ada aktivitas WOS / pergerakan stok untuk material ini pada kriteria pencarian yang dipilih.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="text-center py-5 bg-white rounded-2xl border border-dashed text-muted mt-4 shadow-sm">
            <i class="fas fa-box-open fa-3x mb-3" style="color: #cbd5e1;"></i>
            <h5 class="font-weight-bold text-dark">Tidak Ada Data Material</h5>
            <p class="small">Data tidak ditemukan. Silakan sesuaikan filter WOS, Part Number, atau Tanggal di atas.</p>
        </div>
    @endforelse

</div>
@endsection