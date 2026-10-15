<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RM_HISTORICAL_LEDGER_{{ date('Ymd') }}</title>
    <!-- Font Standar Industri Manufaktur Tier-1 -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
    
    <style>
        :root { 
            --dark: #0f172a; 
            --slate: #475569;
            --muted: #64748b;
            --border-thick: #0f172a;
            --border-thin: #cbd5e1;
            --primary-blue: #1e40af;
            --stamp-blue: #1d4ed8;
            --success-green: #047857;
            --danger-red: #b91c1c;
        }
        
        * { box-sizing: border-box; }
        
        /* 📄 PENGATURAN KERTAS A4 LANDSCAPE */
        @page { 
            size: A4 landscape; 
            margin: 8mm 10mm; 
        }

        body { 
            font-family: 'Plus Jakarta Sans', Arial, sans-serif; 
            font-size: 9px; 
            color: var(--dark); 
            margin: 0; 
            padding: 20px 0; 
            background: #cbd5e1; /* Background abu-abu agar lembar kertas terlihat tegas di tengah layar */
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact; 
        }

        /* 📑 KERTAS DOKUMEN CETAK (TERPUSAT, TIDAK MELAR KOSONG DI MONITOR LEBAR) */
        .paper-sheet { 
            max-width: 1220px; 
            margin: 0 auto; 
            background: #ffffff; 
            padding: 24px 28px; 
            border-radius: 8px; 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12); 
            position: relative;
        }

        /* 🔘 SCREEN ACTION BAR */
        .no-print-bar {
            max-width: 1220px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.03);
            border: 1px solid #cbd5e1;
        }
        .btn-screen {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 11px;
            padding: 8px 18px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
        }
        .btn-back { background: #475569; color: #fff; }
        .btn-back:hover { background: #334155; color: #fff; }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-print:hover { background: #1d4ed8; color: #fff; }

        /* 🏛️ INDUSTRIAL DOCUMENT HEADER */
        .doc-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            border-bottom: 3.5px solid var(--border-thick); 
            padding-bottom: 10px; 
            margin-bottom: 14px; 
        }
        .header-left h1 { 
            margin: 0; 
            font-size: 19px; 
            font-weight: 900; 
            letter-spacing: -0.5px; 
            text-transform: uppercase; 
            color: var(--dark);
        }
        .header-left p { 
            margin: 2px 0 0; 
            font-size: 9px; 
            color: var(--muted); 
            font-weight: 700; 
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .header-right { text-align: right; font-size: 8.5px; line-height: 1.4; }
        .period-pill {
            background: var(--dark);
            color: #fff;
            padding: 4px 10px;
            border-radius: 5px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 800;
            font-size: 9.5px;
            display: inline-block;
            margin-bottom: 3px;
        }
        .header-right span { font-weight: 600; color: var(--muted); text-transform: uppercase; }
        .header-right b { color: var(--dark); }

        /* 📋 SECTION I: INVENTORY MUTATION LEDGER */
        table.ledger-table { 
            width: 100%; 
            border-collapse: collapse; 
            border: 1.5px solid var(--border-thick); 
            margin-bottom: 20px;
        }
        table.ledger-table th { 
            border: 1px solid var(--border-thick); 
            padding: 7px 5px; 
            font-size: 8px; 
            font-weight: 800; 
            text-transform: uppercase; 
            background: #f8fafc !important; 
            letter-spacing: 0.5px;
            text-align: center;
        }
        table.ledger-table td { 
            border: 1px solid var(--border-thin); 
            padding: 6px 8px; 
            vertical-align: middle; 
            font-size: 9.5px;
        }

        .text-left { text-align: left !important; }
        .text-center { text-align: center !important; }
        .num-cell { text-align: right !important; font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 10.5px; }

        .mat-alias { font-weight: 800; font-size: 11px; color: var(--primary-blue); font-family: 'JetBrains Mono', monospace; display: block; }
        .mat-spec { font-size: 8.5px; font-weight: 700; color: var(--dark); }
        .mat-dim { font-size: 7.5px; color: var(--muted); font-family: 'JetBrains Mono', monospace; display: block; }

        .cell-final { background: #f1f5f9 !important; font-weight: 800; color: #1e3a8a; border-left: 1.5px solid var(--border-thick) !important; font-size: 11px; }
        .val-in { color: var(--success-green); }
        .val-out { color: var(--danger-red); }

        .status-badge { 
            font-size: 8px; font-weight: 800; padding: 3px 8px; 
            border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px; 
            display: inline-block; border: 1px solid transparent;
        }
        .status-available { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
        .status-low { background: #fef3c7; color: #b45309; border-color: #fde68a; }
        .status-depleted { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }

        /* 📑 SECTION III: DETAILED TRANSACTION AUDIT TRAIL */
        .section-header { 
            display: flex; 
            align-items: center; 
            margin-bottom: 8px; 
            border-left: 4px solid var(--primary-blue); 
            padding-left: 10px;
        }
        .section-header h3 { 
            margin: 0; 
            font-size: 10.5px; 
            font-weight: 900; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            color: var(--dark);
        }
        .section-header span { font-size: 8.5px; color: var(--muted); margin-left: 10px; font-weight: 600; }

        table.audit-table { width: 100%; border-collapse: collapse; border: 1.5px solid var(--border-thick); margin-bottom: 25px; }
        table.audit-table th { 
            border: 1px solid var(--border-thick); 
            padding: 6px 5px; 
            font-size: 8px; 
            font-weight: 800; 
            text-transform: uppercase; 
            background: #f8fafc !important; 
        }
        table.audit-table td { 
            border: 1px solid var(--border-thin); 
            padding: 6px 8px; 
            vertical-align: middle; 
            font-size: 9px;
        }

        .type-badge { font-weight: 800; font-size: 7.5px; text-transform: uppercase; padding: 2px 6px; border-radius: 4px; }
        .type-in { background: #dcfce7; color: #047857; }
        .type-out { background: #fee2e2; color: #b91c1c; }

        .sj-title { font-family: 'JetBrains Mono', monospace; font-weight: 800; color: #1e3a8a; font-size: 10px; }
        .badge-count { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 8px; font-weight: 800; padding: 1px 6px; border-radius: 4px; margin-left: 6px; }

        /* ✨ KOTAK RINCIAN TAG BUNDLE FISIK ✨ */
        .breakdown-wrapper {
            margin-top: 5px;
            padding: 5px 8px;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px dashed #cbd5e1;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 5px;
        }
        .breakdown-label { font-size: 7.5px; font-weight: 800; color: var(--muted); text-transform: uppercase; margin-right: 4px; }
        .bundle-chip {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 2px 7px;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 8.5px;
            font-weight: 700;
            color: var(--dark);
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        .bundle-chip b { color: #1e40af; }
        .bundle-chip .chip-qty { color: var(--success-green); font-weight: 800; }

        /* ✍️ SIGNATURE SECTION & STEMPEL */
        .doc-footer { 
            display: flex; 
            justify-content: space-between; 
            margin-top: 20px; 
            page-break-inside: avoid; 
        }
        .sig-box { 
            text-align: center; 
            width: 210px; 
            position: relative; 
        }
        .sig-line { 
            height: 55px; 
            border-bottom: 1.5px solid var(--border-thick); 
            margin-bottom: 6px; 
            position: relative;
        }
        .sig-box p { margin: 0; font-weight: 800; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        .sig-box small { color: var(--muted); font-size: 7.5px; font-weight: 600; text-transform: uppercase; }

        /* 🏆 LOGISTICS STAMP */
        .stamp-box {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%) rotate(-12deg);
            width: 78px;
            height: 78px;
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
        .stamp-t { font-size: 5px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; }
        .stamp-c { font-size: 7.5px; font-weight: 900; border-top: 1px solid var(--stamp-blue); border-bottom: 1px solid var(--stamp-blue); padding: 1px 0; margin: 1.5px 0; width: 85%; }
        .stamp-b { font-size: 5px; font-weight: 800; text-transform: uppercase; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; background: #fff; }
            .paper-sheet { 
                max-width: 100% !important; 
                margin: 0 !important; 
                padding: 0 !important; 
                box-shadow: none !important; 
                border-radius: 0 !important;
            }
        }
    </style>
</head>
<body>

    {{-- Screen Toolbar: Tombol KEMBALI Tidak Akan Menutup Web --}}
    <div class="no-print no-print-bar">
        <div>
            <b>LOGISTICS AUDIT TRAIL PREVIEW</b> — Arsip Mutasi Raw Material PT Asalta Mandiri Agung
        </div>
        <div>
            <a href="{{ route('rm.store') }}" class="btn-screen btn-back" onclick="if(window.history.length > 1){ window.history.back(); return false; }">
                <i class="fas fa-arrow-left mr-2"></i> KEMBALI KE RM HUB
            </a>
            <button class="btn-screen btn-print ml-2" onclick="window.print()">
                <i class="fas fa-print mr-2"></i> CETAK DOKUMEN (A4)
            </button>
        </div>
    </div>

    @php
        // 1. Ambil Semua Referensi No Surat Jalan yang muncul di log transaksi
        $allSjNumbers = collect();
        foreach($historyData as $mat) {
            if(isset($mat->logs)) {
                foreach($mat->logs as $l) {
                    $ref = $l->ref_id ?? $l->no_produksi ?? null;
                    if($ref) $allSjNumbers->push(trim($ref));
                }
            }
        }

        // 2. ✨ PENGAMBILAN DATA BUNDLE ASLI LANGSUNG DARI RM_INCOMING_LOGS JOIN RM_STOCKS ✨
        // Logika ini memastikan tag bundle fisik (seperti SAI7898246719, dll) selalu muncul presisi per No SJ
        $inboundBundleMap = \Illuminate\Support\Facades\DB::table('rm_incoming_logs')
            ->leftJoin('rm_stocks', 'rm_incoming_logs.rm_stock_id', '=', 'rm_stocks.id')
            ->whereIn('rm_incoming_logs.no_produksi', $allSjNumbers->unique())
            ->select(
                'rm_incoming_logs.no_produksi',
                'rm_incoming_logs.pcs_in',
                'rm_incoming_logs.material_code',
                'rm_stocks.coil_id'
            )
            ->get()
            ->groupBy('no_produksi');

        // 3. Grand Total Section I
        $totInit = 0; $totIn = 0; $totOut = 0; $totFinal = 0;
        foreach($historyData as $item) {
            $totInit += $item->initial;
            $totIn += $item->in_qty;
            $totOut += $item->out_qty;
            $totFinal += $item->final;
        }

        // 4. Susun Daftar Transaksi Audit Trail yang Ter-grouping
        $allTransactions = collect();

        foreach($historyData as $mat) {
            $rawLogs = collect($mat->logs ?? []);

            $inboundLogs = $rawLogs->filter(function($l) {
                $type = strtoupper($l->type ?? '');
                return str_contains($type, 'IN') || isset($l->pcs_in) || (isset($l->source) && $l->source != 'return');
            });

            $outboundLogs = $rawLogs->filter(function($l) {
                $type = strtoupper($l->type ?? '');
                return str_contains($type, 'OUT') || str_contains($type, 'EXIT') || isset($l->pcs_used);
            });

            // Grouping log Inbound per Nomor Surat Jalan
            $groupedInbound = $inboundLogs->groupBy(function($l) {
                return trim($l->ref_id ?? $l->no_produksi ?? 'INBOUND-DIRECT');
            })->map(function($bundleGroup, $sjNo) use ($mat, $inboundBundleMap) {
                // Ambil daftar bundle fisik langsung dari database map
                $realBundles = $inboundBundleMap->get($sjNo, collect());
                
                $totalQty = $realBundles->isNotEmpty() 
                    ? $realBundles->sum('pcs_in') 
                    : $bundleGroup->sum(fn($b) => $b->qty ?? $b->pcs_in ?? 0);

                return (object)[
                    'created_at'   => $bundleGroup->first()->created_at ?? now(),
                    'alias'        => $mat->alias ?? $mat->spec ?? '-',
                    'type'         => 'SUPPLY_IN (SJ)',
                    'ref_id'       => $sjNo,
                    'bundle_count' => $realBundles->count() > 0 ? $realBundles->count() : count($bundleGroup),
                    'bundles'      => $realBundles,
                    'qty'          => $totalQty
                ];
            });

            // Format Outbound Produksi (WOS)
            $formattedOutbound = $outboundLogs->map(function($l) use ($mat) {
                $qty = isset($l->qty) ? $l->qty : -(int)($l->pcs_used ?? 0);
                if ($qty > 0) $qty = -$qty;

                return (object)[
                    'created_at'   => $l->created_at ?? now(),
                    'alias'        => $mat->alias ?? $mat->spec ?? '-',
                    'type'         => 'USAGE_OUT',
                    'ref_id'       => $l->ref_id ?? $l->no_produksi ?? 'WOS-PRODUCTION',
                    'bundle_count' => 1,
                    'bundles'      => collect(),
                    'qty'          => $qty
                ];
            });

            $allTransactions = $allTransactions->concat($groupedInbound)->concat($formattedOutbound);
        }

        // Urutkan transaksi secara kronologis terbaru
        $auditTrailList = $allTransactions->sortByDesc('created_at')->values();
    @endphp

    <div class="paper-sheet">
        
        {{-- Header Dokumen Resmi --}}
        <div class="doc-header">
            <div class="header-left">
                <h1>RM HISTORICAL LEDGER</h1>
                <p>Industrial Logistics Archive & Traceability Audit // PT. Asalta Mandiri Agung</p>
            </div>
            <div class="header-right">
                <div class="period-pill">
                    {{ date('d/m/Y', strtotime($startDate ?? now())) }} — {{ date('d/m/Y', strtotime($endDate ?? now())) }}
                </div>
                <div><span>CLIENT:</span> <b>{{ strtoupper($customer ?? 'ALL CLIENT ENTITIES') }}</b></div>
                <div><span>DOC NUMBER:</span> <b>AMA-LOG-LEDGER-{{ date('Ymd') }}</b></div>
            </div>
        </div>

        {{-- SECTION I: INVENTORY MUTATION LEDGER --}}
        <table class="ledger-table">
            <thead>
                <tr>
                    <th width="35%" class="text-left">MATERIAL IDENTIFICATION & SPEC</th>
                    <th width="13%">STOK AWAL</th>
                    <th width="13%">IN (+)</th>
                    <th width="13%">OUT (-)</th>
                    <th width="14%" style="background:#f1f5f9 !important;">FINAL STOCK</th>
                    <th width="12%">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historyData as $mat)
                <tr>
                    <td class="text-left">
                        <span class="mat-alias">{{ $mat->alias ?? 'UNREGISTERED' }}</span>
                        <span class="mat-spec">{{ $mat->spec }}</span>
                        <span class="mat-dim">DIM: {{ $mat->size }}</span>
                    </td>
                    <td class="num-cell" style="color: var(--slate);">{{ number_format($mat->initial) }}</td>
                    <td class="num-cell val-in">{{ $mat->in_qty > 0 ? '+'.number_format($mat->in_qty) : '0' }}</td>
                    <td class="num-cell val-out">{{ $mat->out_qty > 0 ? '-'.number_format($mat->out_qty) : '0' }}</td>
                    <td class="num-cell cell-final">{{ number_format($mat->final) }}</td>
                    <td class="text-center">
                        @if($mat->final <= 0)
                            <span class="status-badge status-depleted">DEPLETED</span>
                        @elseif($mat->final < 500)
                            <span class="status-badge status-low">LOW_STOCK</span>
                        @else
                            <span class="status-badge status-available">AVAILABLE</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted italic">-- Tidak ada data mutasi inventori pada periode ini --</td>
                </tr>
                @endforelse

                {{-- Baris Grand Total --}}
                @if(count($historyData) > 0)
                <tr style="background:#e2e8f0; font-weight:900; border-top:2px solid var(--border-thick);">
                    <td class="text-left" style="font-weight:900; padding-left:10px;">TOTAL INVENTORY MUTATION :</td>
                    <td class="num-cell">{{ number_format($totInit) }}</td>
                    <td class="num-cell val-in">+{{ number_format($totIn) }}</td>
                    <td class="num-cell val-out">-{{ number_format($totOut) }}</td>
                    <td class="num-cell cell-final" style="font-size:11.5px;">{{ number_format($totFinal) }}</td>
                    <td class="text-center">
                        <span class="status-badge status-available">BALANCED</span>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>

        {{-- SECTION III: DETAILED TRANSACTION AUDIT TRAIL --}}
        <div class="section-header">
            <h3>Section III: Detailed Transaction Audit Trail</h3>
            <span>(Tergrup Berdasarkan Nomor Surat Jalan & Work Order Stamping)</span>
        </div>

        <table class="audit-table">
            <thead>
                <tr>
                    <th width="14%" class="text-center">TIMESTAMP</th>
                    <th width="14%" class="text-center">ALIAS / MATERIAL</th>
                    <th width="16%" class="text-center">TYPE</th>
                    <th width="42%" class="text-left" style="padding-left:12px;">REFERENCE ID / MANIFEST DETAILS</th>
                    <th width="14%" class="text-center">QUANTITY</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditTrailList as $tx)
                <tr>
                    <td class="text-center" style="font-family:'JetBrains Mono', monospace; font-size:8.5px; color:var(--slate);">
                        {{ date('d/m/Y H:i', strtotime($tx->created_at)) }}
                    </td>
                    <td class="text-center font-weight-bold" style="font-family:'JetBrains Mono', monospace;">
                        {{ $tx->alias }}
                    </td>
                    <td class="text-center">
                        @if(str_contains($tx->type, 'IN'))
                            <span class="type-badge type-in"><i class="fas fa-arrow-down mr-1"></i> SUPPLY_IN (SJ)</span>
                        @else
                            <span class="type-badge type-out"><i class="fas fa-arrow-up mr-1"></i> USAGE_OUT</span>
                        @endif
                    </td>
                    <td class="text-left" style="padding-left:12px;">
                        <div class="d-flex align-items-center">
                            <span class="sj-title">{{ $tx->ref_id }}</span>
                            @if(isset($tx->bundle_count) && $tx->bundle_count > 1)
                                <span class="badge-count">
                                    <i class="fas fa-boxes mr-1"></i> {{ $tx->bundle_count }} BUNDLES
                                </span>
                            @endif
                        </div>

                        {{-- 📦 KOTAK RINCIAN TAG BUNDLE FISIK ASLI --}}
                        @if(isset($tx->bundles) && count($tx->bundles) > 0)
                            <div class="breakdown-wrapper">
                                <span class="breakdown-label"><i class="fas fa-tags mr-1"></i> RINCIAN COIL:</span>
                                @foreach($tx->bundles as $b)
                                    <span class="bundle-chip">
                                        🏷️ <b>{{ $b->coil_id ?? $b->material_code ?? 'TAG' }}</b>: <span class="chip-qty">+{{ number_format($b->pcs_in ?? $b->qty ?? 0) }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="num-cell" style="{{ $tx->qty > 0 ? 'color:var(--success-green);' : 'color:var(--danger-red);' }}">
                        {{ $tx->qty > 0 ? '+'.number_format($tx->qty) : number_format($tx->qty) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted italic">-- Tidak ada transaksi mutasi yang tercatat --</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Lembar Otorisasi & Stempel --}}
        <div class="doc-footer">
            <div class="sig-box">
                <p>REPORT PREPARED BY,</p>
                <div class="sig-line"></div>
                <p>Logistics Administrator</p>
                <small>WAREHOUSE & STORAGE DEPT</small>
            </div>

            <div class="sig-box">
                <p>VALIDATED BY,</p>
                <div class="sig-line">
                    <div class="stamp-box">
                        <span class="stamp-t">PT ASALTA MANDIRI AGUNG</span>
                        <span class="stamp-c">LOGISTICS<br>DEPARTMENT</span>
                        <span class="stamp-b">KARAWANG PLANT</span>
                    </div>
                </div>
                <p>PPIC Supervisor</p>
                <small>PRODUCTION PLANNING & CONTROL</small>
            </div>

            <div class="sig-box">
                <p>AUTHORIZED BY,</p>
                <div class="sig-line"></div>
                <p>Plant Manager</p>
                <small>OPERATIONAL MANAGEMENT</small>
            </div>
        </div>

    </div>

</body>
</html>