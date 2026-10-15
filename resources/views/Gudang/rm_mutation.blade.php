@extends('layout.admin')

@section('content')
@php
    // Kelompokkan data mutasi berdasarkan Nomor Surat Jalan
    $groupedBySj = collect($rmData ?? [])->groupBy(function($item) {
        if (!empty($item->no_surat_jalan)) return $item->no_surat_jalan;
        if (!empty($item->ref_sj)) return $item->ref_sj;
        return 'NON-SJ / DIRECT COIL REGISTRATION';
    });

    // Akumulasi KPI
    $totalBundles = count($rmData ?? []);
    $totalSjCount = $groupedBySj->filter(fn($items, $key) => !str_contains($key, 'NON-SJ'))->count();
    $sumInit = collect($rmData ?? [])->sum('initial_stock');
    $sumIn = collect($rmData ?? [])->sum('in_qty');
    $sumOut = collect($rmData ?? [])->sum('out_qty');
    $sumFinal = collect($rmData ?? [])->sum('final_stock');
@endphp

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=JetBrains+Mono:wght@600;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root { 
        --brand-dark: #0f172a;
        --brand-slate: #334155;
        --brand-muted: #64748b;
        --brand-primary: #4361ee;
        --brand-emerald: #059669;
        --brand-rose: #dc2626;
        --ui-surface: #ffffff;
        --ui-border: #e2e8f0;
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; color: var(--brand-dark); background-color: #f8fafc; }
    .mono { font-family: 'JetBrains Mono', monospace; }
    .cyber { font-family: 'Orbitron', sans-serif; }

    /* 📊 KPI METRIC CARDS */
    .kpi-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 15px; margin-bottom: 25px; }
    .kpi-card { 
        background: var(--ui-surface); border: 1px solid var(--ui-border); 
        border-radius: 20px; padding: 18px 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        position: relative; overflow: hidden;
    }
    .kpi-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 4.5px; }
    .kpi-title { font-size: 9.5px; font-weight: 800; text-transform: uppercase; color: var(--brand-muted); letter-spacing: 0.8px; }
    .kpi-val { font-size: 22px; font-weight: 800; margin-top: 4px; font-family: 'JetBrains Mono', monospace; }

    /* 📦 SURAT JALAN GROUP CONTAINER */
    .sj-group-card {
        background: #ffffff;
        border: 1px solid var(--ui-border);
        border-radius: 22px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        margin-bottom: 25px;
        overflow: hidden;
    }

    .sj-group-header {
        background: linear-gradient(90deg, #f8fafc 0%, #ffffff 100%);
        border-bottom: 2px solid var(--ui-border);
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .sj-badge {
        background: #eff6ff; color: #1e40af; border: 1.5px solid #bfdbfe;
        font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 13px;
        padding: 6px 14px; border-radius: 10px; display: inline-flex; align-items: center;
    }

    /* 📋 INDUSTRIAL TABLE INSIDE GROUP */
    .table-bundles { width: 100%; border-collapse: collapse; margin: 0; }
    .table-bundles thead th { 
        background: #fdfdfd; color: var(--brand-muted); font-size: 9.5px; 
        text-transform: uppercase; letter-spacing: 1px; padding: 14px 18px; 
        border-bottom: 1px solid var(--ui-border); font-weight: 800;
    }
    .table-bundles tbody td { 
        padding: 12px 18px; vertical-align: middle; 
        border-bottom: 1px solid #f1f5f9; font-size: 12px;
    }
    .table-bundles tbody tr:last-child td { border-bottom: none; }
    .table-bundles tbody tr:hover { background: #fbfcfe; }

    /* 🔢 NUMERIC COLUMNS (RATA KANAN + MONO) */
    .num-col { text-align: right !important; font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 12.5px; }
    .col-init { color: var(--brand-muted); }
    .col-in { color: var(--brand-emerald); }
    .col-out { color: var(--brand-rose); }
    .col-final { 
        color: #1e3a8a; background: #f8fafc; 
        font-weight: 800; font-size: 13px; border-left: 1px dashed var(--ui-border);
    }

    .badge-coil-tag {
        background: var(--brand-dark); color: #fff; font-family: 'JetBrains Mono';
        font-weight: 700; font-size: 11px; padding: 4px 10px; border-radius: 8px;
    }

    .badge-alias {
        background: #f1f5f9; color: var(--brand-slate); font-family: 'JetBrains Mono';
        font-weight: 700; font-size: 10px; padding: 3px 8px; border-radius: 6px; border: 1px solid #e2e8f0;
    }

    /* 🚦 STATUS PILLS */
    .status-pill { font-size: 9px; font-weight: 800; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; }
    .pill-ok { background: #dcfce7; color: #15803d; }
    .pill-low { background: #fef3c7; color: #b45309; }
    .pill-empty { background: #fee2e2; color: #b91c1c; }

    .tech-input { border-radius: 50px; border: 2px solid #e2e8f0; padding: 8px 18px; font-weight: 700; background: #fff; font-size: 13px; }
    .tech-input:focus { border-color: var(--brand-primary); outline: none; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
    {{-- 🛰️ Top Title Bar & Filter --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h3 class="font-weight-extrabold mb-1" style="letter-spacing: -1px;">
                Raw Material <span class="text-primary">Mutation Ledger</span>
            </h3>
            <p class="text-muted small font-weight-bold mb-0 text-uppercase">
                <i class="fas fa-boxes mr-1 text-primary"></i> Manifest & Surat Jalan Grouping Feed
            </p>
        </div>

        <form action="{{ route('rm.mutation') }}" method="GET" class="d-flex flex-wrap gap-2 align-items-center mt-3 mt-md-0 bg-white p-2 rounded-pill shadow-sm border">
            <select name="customer" class="tech-input border-0" style="width: 160px;">
                <option value="">-- ALL CLIENTS --</option>
                @foreach($availableCustomers as $c)
                    <option value="{{ trim($c->code) }}" {{ $customer == trim($c->code) ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
            <span class="text-muted">|</span>
            <input type="date" name="date" class="tech-input border-0" value="{{ $date }}" style="width: 145px;">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold shadow-sm py-2">
                <i class="fas fa-filter mr-1"></i> FILTER
            </button>
        </form>
    </div>

    {{-- 📊 KPI Metric Ribbon --}}
    <div class="kpi-container">
        <div class="kpi-card">
            <div class="kpi-strip bg-primary"></div>
            <div class="kpi-title">Active Manifests (SJ)</div>
            <div class="kpi-val text-primary">{{ number_format($totalSjCount) }} <span class="small font-weight-bold text-muted" style="font-size: 10px;">SURAT JALAN</span></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-strip bg-dark"></div>
            <div class="kpi-title">Total Physical Bundles</div>
            <div class="kpi-val text-dark">{{ number_format($totalBundles) }} <span class="small font-weight-bold text-muted" style="font-size: 10px;">COILS</span></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-strip bg-secondary"></div>
            <div class="kpi-title">Beginning Balance</div>
            <div class="kpi-val text-muted">{{ number_format($sumInit) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-strip bg-success"></div>
            <div class="kpi-title">Total Received (In)</div>
            <div class="kpi-val text-success">+{{ number_format($sumIn) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-strip bg-danger"></div>
            <div class="kpi-title">Total Issued (Out)</div>
            <div class="kpi-val text-danger">-{{ number_format($sumOut) }}</div>
        </div>
        <div class="kpi-card" style="background: #f1f5f9;">
            <div class="kpi-strip bg-primary"></div>
            <div class="kpi-title">Vault Ending Stock</div>
            <div class="kpi-val text-primary">{{ number_format($sumFinal) }} <span class="small font-weight-bold text-muted" style="font-size: 10px;">PCS</span></div>
        </div>
    </div>

    {{-- 📦 DAFTAR MUTASI: GROUPED PER SURAT JALAN --}}
    @forelse($groupedBySj as $sjNo => $bundles)
        @php
            $groupIn = $bundles->sum('in_qty');
            $groupOut = $bundles->sum('out_qty');
            $groupFinal = $bundles->sum('final_stock');
            $isDirect = str_contains($sjNo, 'NON-SJ');
        @endphp

        <div class="sj-group-card animate__animated animate__fadeInUp">
            {{-- Header Grup Surat Jalan --}}
            <div class="sj-group-header">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="sj-badge">
                        <i class="fas {{ $isDirect ? 'fa-dolly-flatbed' : 'fa-file-invoice' }} mr-2 text-primary"></i>
                        {{ $isDirect ? $sjNo : 'SURAT JALAN: ' . $sjNo }}
                    </span>
                    <span class="badge badge-light border rounded-pill px-3 py-2 font-weight-bold text-muted" style="font-size: 10.5px;">
                        📦 {{ $bundles->count() }} BUNDLE / COIL TAGS
                    </span>
                    @if(!empty($bundles->first()->customer))
                        <span class="badge badge-dark rounded-pill px-3 py-2 font-weight-bold uppercase" style="font-size: 10px;">
                            {{ $bundles->first()->customer }}
                        </span>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-4 text-right">
                    <div>
                        <small class="text-muted font-weight-bold d-block uppercase" style="font-size: 8.5px;">Total Masuk (SJ)</small>
                        <span class="mono font-weight-bold text-success h6 mb-0">+{{ number_format($groupIn) }} PCS</span>
                    </div>
                    <div>
                        <small class="text-muted font-weight-bold d-block uppercase" style="font-size: 8.5px;">Total Terpakai (WOS)</small>
                        <span class="mono font-weight-bold text-danger h6 mb-0">-{{ number_format($groupOut) }} PCS</span>
                    </div>
                    <div>
                        <small class="text-muted font-weight-bold d-block uppercase" style="font-size: 8.5px;">Sisa Stok Bundle</small>
                        <span class="mono font-weight-bold text-primary h6 mb-0">{{ number_format($groupFinal) }} PCS</span>
                    </div>
                </div>
            </div>

            {{-- Tabel Rincian Bundle Fisik di Dalam Surat Jalan Ini --}}
            <div class="table-responsive">
                <table class="table-bundles">
                    <thead>
                        <tr>
                            <th width="28%">Coil / Bundle Identifier</th>
                            <th width="24%">Specification & Dimension</th>
                            <th width="11%" class="num-col">Stok Awal</th>
                            <th width="11%" class="num-col">In (Pcs)</th>
                            <th width="11%" class="num-col">Out (Pcs)</th>
                            <th width="12%" class="num-col">Sisa Saldo</th>
                            <th width="13%" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bundles as $b)
                        <tr>
                            {{-- Coil Tag & Alias --}}
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge-coil-tag mr-2">{{ $b->coil_id }}</span>
                                    <span class="badge-alias">{{ $b->alias_code ?? 'NO_ALIAS' }}</span>
                                </div>
                            </td>

                            {{-- Material Spec & Size --}}
                            <td>
                                <div class="font-weight-bold text-dark small">{{ $b->spec }}</div>
                                <div class="text-muted mono" style="font-size: 10px;">{{ $b->size }}</div>
                            </td>

                            {{-- Angka Mutasi (Rata Kanan) --}}
                            <td class="num-col col-init">{{ number_format($b->initial_stock) }}</td>
                            <td class="num-col col-in">{{ $b->in_qty > 0 ? '+'.number_format($b->in_qty) : '-' }}</td>
                            <td class="num-col col-out">{{ $b->out_qty > 0 ? '-'.number_format($b->out_qty) : '-' }}</td>
                            <td class="num-col col-final">{{ number_format($b->final_stock) }}</td>

                            {{-- Status --}}
                            <td class="text-center">
                                @if($b->final_stock <= 0)
                                    <span class="status-pill pill-empty"><i class="fas fa-times-circle mr-1"></i> HABIS</span>
                                @elseif($b->final_stock < 300)
                                    <span class="status-pill pill-low"><i class="fas fa-exclamation-triangle mr-1"></i> MENIPIS</span>
                                @else
                                    <span class="status-pill pill-ok"><i class="fas fa-check-circle mr-1"></i> TERSEDIA</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="card border-0 rounded-24 p-5 text-center bg-white shadow-sm">
            <i class="fas fa-box-open fa-3x text-muted opacity-50 mb-3"></i>
            <h6 class="text-muted font-weight-bold mb-0">Tidak ada mutasi raw material pada periode tanggal ini.</h6>
        </div>
    @endforelse

</div>
@endsection