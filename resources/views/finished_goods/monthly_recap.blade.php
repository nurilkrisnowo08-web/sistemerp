@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --primary: #2563eb; --primary-light: #eff6ff;
        --success: #10b981; --success-light: #dcfce7;
        --danger: #ef4444; --danger-light: #fee2e2;
        --dark: #0f172a; --slate: #475569; --bg: #f8fafc;
        --border: #e2e8f0;
    }

    .recap-wrapper { font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark); background-color: var(--bg); min-height: 100vh; padding: 1.5rem; }
    
    /* 🎛️ FILTER PANEL */
    .filter-panel { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid var(--border); padding: 20px; border-left: 5px solid var(--primary); margin-bottom: 25px; }
    .form-control, .form-select { border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem; font-weight: 700; color: var(--dark); }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    
    .btn-action { border-radius: 6px; font-weight: 800; font-size: 0.75rem; padding: 0.6rem 1.2rem; letter-spacing: 0.5px; text-transform: uppercase; transition: all 0.2s; border: none; color: #fff; text-decoration: none; display: inline-block; }
    .btn-back { background: var(--slate); }
    .btn-back:hover { background: #334155; transform: translateY(-1px); color: #fff; text-decoration: none; }
    .btn-print { background: var(--dark); }
    .btn-print:hover { background: #000; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); color: #fff; text-decoration: none; }

    /* 📊 CARD TABLE ERP */
    .card-erp { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid var(--border); overflow: hidden; margin-bottom: 25px; }
    .card-header-erp { background: #fff; padding: 15px 20px; border-bottom: 2px solid var(--dark); display: flex; justify-content: space-between; align-items: center; }
    .card-title-erp { font-size: 0.9rem; font-weight: 800; color: var(--primary); text-transform: uppercase; margin: 0; letter-spacing: 0.5px; }
    
    .table-erp { width: 100%; border-collapse: collapse; margin: 0; font-size: 0.8rem; }
    .table-erp thead th { background: var(--dark); color: #fff; text-align: center; padding: 12px 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; border-right: 1px solid rgba(255,255,255,0.1); }
    .table-erp tbody td { padding: 12px 10px; vertical-align: middle; border-bottom: 1px solid var(--border); border-right: 1px solid var(--border); text-align: center; }
    .table-erp tbody tr:hover { background: #f1f5f9; }

    /* HIGHLIGHTS */
    .mono { font-family: 'JetBrains Mono', monospace; font-weight: 800; }
    .text-in { color: #15803d; font-weight: 900; }
    .text-out { color: #b91c1c; font-weight: 900; }
    .bg-in { background: var(--success-light); }
    .bg-out { background: var(--danger-light); }
    
    .doc-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 900; letter-spacing: 0.5px; font-family: 'JetBrains Mono', monospace; border: 1px solid; display: inline-block;}
    .doc-wos { background: #eff6ff; color: #1d4ed8; border-color: #93c5fd; }
    .doc-sj { background: #fef2f2; color: #b91c1c; border-color: #fca5a5; }
</style>

<div class="recap-wrapper">
    
    <!-- 🎛️ FILTER PANEL -->
    <div class="filter-panel">
        <form action="{{ route('fg.recap') }}" method="GET" id="filterForm">
            <div class="row align-items-end">
                <div class="col-md-3 mb-3">
                    <label class="small font-weight-bold text-primary text-uppercase mb-2">Customer</label>
                    <select name="customer" class="form-control" onchange="document.getElementById('partFilter').value=''; this.form.submit()">
                        <option value="">-- PILIH CUSTOMER --</option>
                        <option value="ALL" {{ request('customer') == 'ALL' ? 'selected' : '' }}>-- SEMUA CUSTOMER (ALL) --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->code }}" {{ request('customer') == $c->code ? 'selected' : '' }}>{{ $c->code }} - {{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="small font-weight-bold text-primary text-uppercase mb-2">Filter Part Number</label>
                    <select name="part_no" id="partFilter" class="form-control" onchange="this.form.submit()">
                        <option value="">-- SEMUA PART --</option>
                        @if(isset($availableParts))
                            @foreach($availableParts as $p)
                                <option value="{{ $p->part_no }}" {{ request('part_no') == $p->part_no ? 'selected' : '' }}>{{ $p->part_no }} - {{ $p->part_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="small font-weight-bold text-primary text-uppercase mb-2">Bulan</label>
                    <select name="month" class="form-control" onchange="this.form.submit()">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-1 mb-3">
                    <label class="small font-weight-bold text-primary text-uppercase mb-2">Tahun</label>
                    <input type="number" name="year" class="form-control" value="{{ $year }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-3 mb-3 text-right">
                    <a href="{{ route('fg.index') }}" class="btn btn-action btn-back mr-1"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                    
                    <!-- 🔥 FIX: BUKA TAB BARU KE HALAMAN PRINT KHUSUS 🔥 -->
                    @if($customer)
                        <a href="{{ request()->fullUrlWithQuery(['action' => 'print']) }}" target="_blank" class="btn btn-action btn-print">
                            <i class="fas fa-print mr-1"></i> Cetak Dokumen
                        </a>
                    @else
                        <button type="button" class="btn btn-action btn-print" disabled><i class="fas fa-print mr-1"></i> Cetak Dokumen</button>
                    @endif
                    
                </div>
            </div>
        </form>
    </div>

    @if($customer)
        <!-- 📦 1. REKAP BULANAN -->
        <div class="card-erp">
            <div class="card-header-erp">
                <h3 class="card-title-erp"><i class="fas fa-boxes mr-2"></i> 1. REKAP MUTASI BULANAN: {{ $customer }}</h3>
                <span class="badge badge-primary px-3 py-2 font-weight-bold shadow-sm" style="font-size: 0.75rem;">{{ date('F Y', strtotime("$year-$month-01")) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table-erp">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 15%;">Part No</th>
                            <th rowspan="2" style="width: 25%;">Part Name</th>
                            <th colspan="4" style="background: #1e293b; border-bottom: 2px solid #334155;">Alur Mutasi Stok (Pcs)</th>
                        </tr>
                        <tr style="background: #334155;">
                            <th style="width: 15%;">Awal</th>
                            <th style="width: 15%; color: #4ade80;">Total IN</th>
                            <th style="width: 15%; color: #f87171;">Total OUT</th>
                            <th style="width: 15%; color: #60a5fa;">Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recap as $fg)
                            <tr>
                                <td class="mono text-primary">{{ $fg->part_no }}</td>
                                <td class="text-left font-weight-bold">{{ $fg->part_name }}</td>
                                <td class="mono font-weight-bold text-slate">{{ number_format($fg->stock_awal) }}</td>
                                <td class="mono text-in bg-in">+{{ number_format($fg->total_in) }}</td>
                                <td class="mono text-out bg-out">-{{ number_format($fg->total_out) }}</td>
                                <td class="mono font-weight-bold" style="color: #1d4ed8; font-size: 0.9rem;">{{ number_format($fg->stock_akhir) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 font-weight-bold text-muted">Data kosong pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 📅 2. RINCIAN MUTASI (FULL BULAN) -->
        <div class="card-erp">
            <div class="card-header-erp" style="background: #f8fafc;">
                <h3 class="card-title-erp text-dark"><i class="fas fa-list-alt mr-2"></i> 2. RINCIAN MUTASI (FULL BULAN)</h3>
                <span class="badge bg-white border px-3 py-1 font-weight-bold text-muted shadow-sm" style="font-size: 0.7rem;"><i class="fas fa-calendar mr-1"></i> 01 - {{ date('t', strtotime("$year-$month-01")) }} {{ date('M Y', strtotime("$year-$month-01")) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table-erp">
                    <thead>
                        <tr>
                            <th style="width: 10%;">Tanggal</th>
                            <th style="width: 10%;">Jam</th>
                            <th style="width: 15%;">Part Number</th>
                            <th style="width: 25%;">No. Dokumen (WOS / Surat Jalan)</th>
                            <th style="width: 20%; color: #4ade80;">IN (Produksi)</th>
                            <th style="width: 20%; color: #f87171;">OUT (Delivery)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyDetails as $detail)
                            @php
                                $ref_doc = !empty($detail->no_dokumen) && $detail->no_dokumen !== '-' && $detail->no_dokumen !== 'NULL' ? $detail->no_dokumen : ($detail->type == 'IN' ? 'WOS-SYSTEM' : 'SJ-SYSTEM');
                                $doc_class = $detail->type == 'IN' ? 'doc-wos' : 'doc-sj';
                            @endphp
                            <tr>
                                <td class="font-weight-bold text-dark">{{ date('d/m/Y', strtotime($detail->tgl)) }}</td>
                                <td class="text-muted mono">{{ date('H:i', strtotime($detail->jam)) }}</td>
                                <td class="text-primary font-weight-bold mono">{{ $detail->part_no }}</td>
                                <td>
                                    <span class="doc-badge {{ $doc_class }}">
                                        {!! $detail->type == 'IN' ? '<i class="fas fa-cog mr-1"></i>' : '<i class="fas fa-truck mr-1"></i>' !!} 
                                        {{ strtoupper($ref_doc) }}
                                    </span>
                                </td>
                                <td class="mono {{ $detail->type == 'IN' ? 'text-in bg-in' : 'text-muted' }}">
                                    {{ $detail->type == 'IN' ? '+'.number_format($detail->in_qty) : '-' }}
                                </td>
                                <td class="mono {{ $detail->type == 'OUT' ? 'text-out bg-out' : 'text-muted' }}">
                                    {{ $detail->type == 'OUT' ? '-'.number_format($detail->out_qty) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 font-weight-bold text-muted">Tidak ada mutasi pada bulan ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- EMPTY STATE -->
        <div class="text-center py-5 card-erp border-0 mt-3" style="background: #fff; box-shadow:none;">
            <i class="fas fa-network-wired fa-4x mb-4" style="color: #cbd5e1;"></i>
            <h4 class="font-weight-bold text-uppercase" style="color: var(--slate);">System Ready: Please Select Customer</h4>
            <p class="text-muted small">Terminal Finish Good Monitoring v.2.0</p>
        </div>
    @endif
</div>
@endsection