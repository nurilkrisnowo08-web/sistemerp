@extends('layout.admin')

@section('content')
@php
    // Hitung ringkasan KPI dari data yang tampil
    $totalCoils = count($rmData ?? []);
    $sumInit = 0;
    $sumIn = 0;
    $sumOut = 0;
    $sumFinal = 0;

    if(isset($rmData) && count($rmData) > 0) {
        foreach($rmData as $row) {
            $sumInit += ($row->initial_stock ?? 0);
            $sumIn += ($row->in_qty ?? 0);
            $sumOut += ($row->out_qty ?? 0);
            $sumFinal += ($row->final_stock ?? 0);
        }
    }
@endphp

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root { 
        --brand-dark: #0f172a;
        --brand-slate: #334155;
        --brand-muted: #64748b;
        --brand-indigo: #4361ee;
        --brand-emerald: #059669;
        --brand-rose: #dc2626;
        --ui-surface: #ffffff;
        --ui-border: #e2e8f0;
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; color: var(--brand-dark); }
    .mono { font-family: 'JetBrains Mono', monospace; }

    /* 🏷️ KPI METRICS RIBBON */
    .kpi-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
    .kpi-card { 
        background: var(--ui-surface); border: 1px solid var(--ui-border); 
        border-radius: 18px; padding: 16px 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        position: relative; overflow: hidden;
    }
    .kpi-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 4px; }
    .kpi-title { font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--brand-muted); letter-spacing: 0.8px; }
    .kpi-val { font-size: 22px; font-weight: 800; margin-top: 4px; font-family: 'JetBrains Mono', monospace; }

    /* 📋 LEDGER HUD TABLE */
    .ledger-card { 
        background: var(--ui-surface); border: 1px solid var(--ui-border); 
        border-radius: 22px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.03); 
        overflow: hidden; 
    }
    .table-ledger thead th { 
        background: #f8fafc; color: var(--brand-slate); font-weight: 800; 
        font-size: 10px; text-transform: uppercase; letter-spacing: 1.2px; 
        padding: 18px 16px; border-bottom: 2px solid var(--ui-border); 
    }
    .table-ledger tbody td { 
        padding: 14px 16px; vertical-align: middle; 
        border-bottom: 1px solid #f1f5f9; font-size: 12px;
    }
    .table-ledger tbody tr:hover { background: #f8fafc; }

    /* 🏷️ BADGES & LABELS */
    .tag-coil { font-size: 13px; font-weight: 800; color: var(--brand-dark); }
    .badge-sj { 
        font-size: 10px; font-weight: 700; background: #eff6ff; color: #1d4ed8; 
        border: 1px solid #dbeafe; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center;
    }
    .badge-alias { 
        font-size: 10px; font-weight: 700; background: #f1f5f9; color: var(--brand-slate); 
        border: 1px solid #e2e8f0; padding: 3px 8px; border-radius: 6px;
    }
    .badge-spec { font-size: 11px; font-weight: 700; color: var(--brand-slate); }
    .dim-spec { font-size: 10px; color: var(--brand-muted); }

    /* 🔢 NUMERIC COLUMNS (RATA KANAN + MONO) */
    .num-col { text-align: right !important; font-family: 'JetBrains Mono', monospace; font-size: 12.5px; font-weight: 700; }
    .col-init { color: var(--brand-muted); }
    .col-in { color: var(--brand-emerald); background: rgba(5, 150, 105, 0.02); }
    .col-out { color: var(--brand-rose); background: rgba(220, 38, 38, 0.02); }
    .col-balance { 
        color: #1e3a8a; background: #f1f5f9; 
        font-weight: 800; font-size: 13px; border-left: 1px dashed #cbd5e1;
    }

    /* 🚦 INVENTORY HEALTH STATUS */
    .status-pill { 
        font-size: 9.5px; font-weight: 800; padding: 4px 10px; 
        border-radius: 20px; letter-spacing: 0.5px; text-transform: uppercase; display: inline-block;
    }
    .pill-ok { background: #dcfce7; color: #15803d; }
    .pill-low { background: #fef3c7; color: #b45309; }
    .pill-empty { background: #fee2e2; color: #b91c1c; }

    .grand-total-row {
        background: #f8fafc !important; font-weight: 900; 
        border-top: 2px solid var(--brand-dark); border-bottom: 2px solid var(--brand-dark);
    }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
    {{-- Header Page & Filter Bar --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h3 class="font-weight-extrabold mb-1" style="letter-spacing: -1px;">
                Raw Material <span class="text-primary">Mutation Ledger</span>
            </h3>
            <p class="text-muted small font-weight-bold mb-0 text-uppercase tracking-wider">
                <i class="fas fa-barcode mr-1 text-primary"></i> Real-time Coil Traceability & Inventory Manifest
            </p>
        </div>
        
        <form action="{{ route('rm.mutation') }}" method="GET" class="d-flex flex-wrap gap-2 align-items-center mt-3 mt-md-0 shadow-sm p-2 bg-white rounded-pill border">
            <select name="customer" class="form-control form-control-sm border-0 bg-transparent shadow-none font-weight-bold" style="width: 160px;">
                <option value="">-- ALL CLIENTS --</option>
                @foreach($availableCustomers as $c)
                    <option value="{{ trim($c->code) }}" {{ $customer == trim($c->code) ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
            <span class="text-muted">|</span>
            <input type="date" name="date" class="form-control form-control-sm border-0 bg-transparent shadow-none font-weight-bold" value="{{ $date }}" style="width: 140px;">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold shadow-sm">
                <i class="fas fa-filter mr-1"></i> APPLY
            </button>
        </form>
    </div>

    {{-- 📊 KPI Summary Ribbon --}}
    <div class="kpi-container">
        <div class="kpi-card">
            <div class="kpi-strip bg-primary"></div>
            <div class="kpi-title">Active Batches Tracked</div>
            <div class="kpi-val text-primary">{{ number_format($totalCoils) }} <span class="small font-weight-bold text-muted">TAGS</span></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-strip bg-secondary"></div>
            <div class="kpi-title">Beginning Inventory</div>
            <div class="kpi-val text-muted">{{ number_format($sumInit) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-strip bg-success"></div>
            <div class="kpi-title">Total Inbound (Received)</div>
            <div class="kpi-val text-success">+{{ number_format($sumIn) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-strip bg-danger"></div>
            <div class="kpi-title">Total Outbound (Issued)</div>
            <div class="kpi-val text-danger">-{{ number_format($sumOut) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-strip bg-dark"></div>
            <div class="kpi-title">Current Vault Balance</div>
            <div class="kpi-val text-dark">{{ number_format($sumFinal) }} <span class="small font-weight-bold text-muted">PCS</span></div>
        </div>
    </div>

    {{-- 🏛️ Main Mutation Ledger Table --}}
    <div class="ledger-card">
        <div class="table-responsive">
            <table class="table table-ledger mb-0">
                <thead>
                    <tr>
                        <th width="28%">Coil Identifier & Manifest Ref</th>
                        <th width="24%">Material Specification</th>
                        <th width="11%" class="num-col">Initial (Pcs)</th>
                        <th width="11%" class="num-col">In (+)</th>
                        <th width="11%" class="num-col">Out (-)</th>
                        <th width="12%" class="num-col">Vault Stock</th>
                        <th width="13%" class="text-center">Health Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rmData as $rm)
                    @php
                        // Cek data nomor Surat Jalan (bisa dari kolom no_surat_jalan atau ref_sj)
                        $sjNo = $rm->no_surat_jalan ?? $rm->ref_sj ?? null;
                    @endphp
                    <tr>
                        {{-- Tag Bundle & Surat Jalan --}}
                        <td>
                            <div class="d-flex align-items-center mb-1">
                                <span class="tag-coil mono mr-2">{{ $rm->coil_id }}</span>
                                <span class="badge-alias mono">{{ $rm->alias_code ?? 'NO_ALIAS' }}</span>
                            </div>
                            <div class="d-flex align-items-center mt-1">
                                @if(!empty($sjNo))
                                    <span class="badge-sj mono">
                                        <i class="fas fa-file-invoice mr-1 text-primary"></i> SJ: {{ $sjNo }}
                                    </span>
                                @else
                                    <span class="text-muted small mono" style="font-size: 10px;">
                                        <i class="fas fa-receipt mr-1"></i> SJ: -
                                    </span>
                                @endif
                                @if(!empty($rm->customer))
                                    <span class="badge badge-light border ml-1 font-weight-bold text-uppercase" style="font-size: 9px;">
                                        {{ $rm->customer }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Spesifikasi Material --}}
                        <td>
                            <div class="badge-spec">{{ $rm->spec }}</div>
                            <div class="dim-spec mono">{{ $rm->size }}</div>
                        </td>

                        {{-- Angka Stok Rata Kanan --}}
                        <td class="num-col col-init">{{ number_format($rm->initial_stock) }}</td>
                        <td class="num-col col-in">{{ $rm->in_qty > 0 ? '+'.number_format($rm->in_qty) : '-' }}</td>
                        <td class="num-col col-out">{{ $rm->out_qty > 0 ? '-'.number_format($rm->out_qty) : '-' }}</td>
                        <td class="num-col col-balance">{{ number_format($rm->final_stock) }}</td>

                        {{-- Status Inventori --}}
                        <td class="text-center">
                            @if($rm->final_stock <= 0)
                                <span class="status-pill pill-empty"><i class="fas fa-times-circle mr-1"></i> DEPLETED</span>
                            @elseif($rm->final_stock < 300)
                                <span class="status-pill pill-low"><i class="fas fa-exclamation-triangle mr-1"></i> LOW STOCK</span>
                            @else
                                <span class="status-pill pill-ok"><i class="fas fa-check-circle mr-1"></i> IN VAULT</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-5 text-center text-muted font-weight-bold">
                            <i class="fas fa-box-open fa-2x mb-2 d-block text-muted opacity-50"></i>
                            TIDAK ADA REKAMAN MUTASI RAW MATERIAL PADA PERIODE INI
                        </td>
                    </tr>
                    @endforelse

                    {{-- Baris Grand Total --}}
                    @if(count($rmData ?? []) > 0)
                    <tr class="grand-total-row">
                        <td colspan="2" class="text-uppercase font-weight-extrabold" style="letter-spacing: 0.5px;">
                            TOTAL ACCUMULATION :
                        </td>
                        <td class="num-col col-init">{{ number_format($sumInit) }}</td>
                        <td class="num-col col-in">+{{ number_format($sumIn) }}</td>
                        <td class="num-col col-out">-{{ number_format($sumOut) }}</td>
                        <td class="num-col col-balance" style="font-size: 14px;">{{ number_format($sumFinal) }}</td>
                        <td class="text-center">
                            <span class="badge badge-dark rounded-pill px-3 py-1 font-weight-bold" style="font-size: 9px;">BALANCED</span>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection