@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

@php
    // 🔥 MENGAMBIL DATA RINCIAN TRANSAKSI (WOS) LANGSUNG DARI DATABASE 🔥
    $start = $startDate ?? date('Y-m-d');
    $end = $endDate ?? date('Y-m-d');

    // 1. Ambil WOS dari Stamping (IN)
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

    // 2. Ambil WOS dari Return QC (IN)
    $inReturn = DB::table('quality_inspections')
        ->where('origin', 'WELDING')
        ->whereBetween(DB::raw('DATE(created_at)'), [$start, $end])
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
                'color' => 'indigo' // custom color
            ];
        });

    // 3. Ambil WOS dari Welding (OUT)
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

    // Gabungkan semua mutasi dan urutkan berdasarkan waktu (terbaru di atas)
    $allMutations = $inStamping->concat($inReturn)->concat($outWelding)->sortByDesc('jam')->groupBy('part_no');
@endphp

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-return: #6366f1; --dark-surface: #0f172a; --bg-main: #f8fafc;
        --glass-border: rgba(226, 232, 240, 0.8);
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #1e293b; }
    
    .heading-vault { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(90deg, var(--dark-surface) 0%, var(--brand-primary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    /* Part Card Grouping */
    .part-group-card { background: #fff; border-radius: 20px; border: 1px solid var(--glass-border); margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); overflow: hidden; }
    .part-header { background: #f8fafc; padding: 20px 25px; border-bottom: 2px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .part-title { font-size: 18px; font-weight: 900; color: var(--dark-surface); font-family: 'JetBrains Mono', monospace; }
    .part-desc { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px; }
    
    /* Summary inside Part Card */
    .summary-pills { display: flex; gap: 15px; }
    .s-pill { padding: 8px 15px; border-radius: 12px; font-size: 11px; font-weight: 800; border: 1px solid #e2e8f0; display: flex; align-items: center; background: #fff; }
    .s-pill.open { border-left: 4px solid #94a3b8; }
    .s-pill.close { border-left: 4px solid var(--brand-primary); }
    .s-val { font-family: 'Orbitron', sans-serif; font-size: 16px; font-weight: 900; margin-left: 10px; }

    /* Detail Table */
    .detail-table { width: 100%; font-size: 12px; }
    .detail-table th { background: #fff; color: #64748b; font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 15px 20px; border-bottom: 1px solid #e2e8f0; text-align: center; }
    .detail-table td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; text-align: center; font-weight: 600; }
    
    .wos-badge { padding: 6px 12px; border-radius: 8px; font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 800; background: #f1f5f9; border: 1px solid #cbd5e1; display: inline-block; }
    
    .type-badge { font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    .type-success { background: #dcfce7; color: #16a34a; }
    .type-indigo { background: #e0e7ff; color: #4f46e5; }
    .type-danger { background: #fee2e2; color: #dc2626; }

    .qty-in { color: var(--brand-success); font-weight: 900; font-family: 'JetBrains Mono'; font-size: 14px; }
    .qty-ret { color: var(--brand-return); font-weight: 900; font-family: 'JetBrains Mono'; font-size: 14px; }
    .qty-out { color: var(--brand-danger); font-weight: 900; font-family: 'JetBrains Mono'; font-size: 14px; }

    /* Print Styling */
    @media print {
        body { background: #fff; }
        .no-print { display: none !important; }
        .part-group-card { border: 1px solid #000; margin-bottom: 20px; page-break-inside: avoid; }
        .part-header { background: #f2f2f2 !important; -webkit-print-color-adjust: exact; border-bottom: 1px solid #000; }
        .detail-table th, .detail-table td { border: 1px solid #000; padding: 8px !important; }
        .s-pill { border: 1px solid #000; }
        .wos-badge, .type-badge { border: 1px solid #000; background: transparent !important; color: #000 !important; }
    }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    
    {{-- 1. HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 no-print">
        <div>
            <h1 class="heading-vault mb-1">WELDING_VAULT <span style="-webkit-text-fill-color: var(--dark-surface);">LEDGER</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0"><i class="fas fa-database text-primary mr-2"></i> Rincian Mutasi WOS Material</p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0 flex-wrap gap-2">
             <form action="" method="GET" class="d-flex mr-2 bg-white p-1 rounded-lg shadow-sm border align-items-center" style="border-radius: 12px;">
                <input type="date" name="start_date" class="form-control form-control-sm border-0 bg-transparent font-weight-bold" value="{{ $startDate }}">
                <span class="px-2 text-muted"><i class="fas fa-arrow-right"></i></span>
                <input type="date" name="end_date" class="form-control form-control-sm border-0 bg-transparent font-weight-bold" value="{{ $endDate }}">
                <button type="submit" class="btn btn-primary btn-sm rounded-lg ml-1 px-3"><i class="fas fa-sync-alt"></i></button>
             </form>
             <button onclick="window.print()" class="btn btn-dark rounded-lg mr-2 shadow-sm font-weight-bold px-4">
                <i class="fas fa-print mr-2"></i> PRINT TRACEABILITY
             </button>
            <a href="{{ route('welding.index') }}" class="btn btn-outline-secondary rounded-lg font-weight-bold px-4 bg-white">
                <i class="fas fa-arrow-left mr-2"></i> KEMBALI
            </a>
        </div>
    </div>

    {{-- 2. DAFTAR MUTASI DIKELOMPOKKAN PER PART NUMBER --}}
    @forelse($historyData as $h)
        @php
            $cleanPart = str_replace([' ', '-'], '', trim($h->part_no));
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
                            <th class="text-left" style="width: 15%;">TANGGAL & JAM</th>
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
                                    <td class="text-left">
                                        <div class="font-weight-bold text-dark">{{ date('d/m/Y', strtotime($mut->jam)) }}</div>
                                        <div class="text-muted font-weight-bold" style="font-size: 10px;">{{ date('H:i:s', strtotime($mut->jam)) }}</div>
                                    </td>
                                    <td>
                                        <span class="type-badge type-{{ $mut->color }}">
                                            <i class="fas {{ $mut->icon }} mr-1"></i> {{ $mut->label }}
                                        </span>
                                    </td>
                                    <td class="text-left">
                                        <span class="wos-badge"><i class="fas fa-barcode mr-2 text-muted"></i>{{ strtoupper($mut->no_dokumen) }}</span>
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
                                <td colspan="3" class="text-right font-weight-bold text-muted uppercase" style="font-size: 10px;">TOTAL PERGERAKAN PERIODE INI:</td>
                                <td class="qty-in" style="font-size: 12px;">+{{ number_format($h->in_s + $h->in_r) }}</td>
                                <td class="qty-out" style="font-size: 12px;">-{{ number_format($h->total_out) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="5" class="py-4 text-muted font-weight-bold" style="font-style: italic; font-size: 11px;">
                                    <i class="fas fa-info-circle mr-1"></i> Belum ada aktivitas WOS / pergerakan stok untuk material ini pada periode yang dipilih.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="text-center py-5 bg-white rounded-lg border border-dashed text-muted mt-4">
            <i class="fas fa-box-open fa-3x mb-3" style="color: #cbd5e1;"></i>
            <h5 class="font-weight-bold">Tidak Ada Data Material</h5>
            <p class="small">Silakan sesuaikan filter tanggal di pojok kanan atas.</p>
        </div>
    @endforelse

</div>
@endsection