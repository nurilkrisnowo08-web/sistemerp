@extends('layout.admin')

@section('content')
<!-- Font Profesional & Monospace -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">

<style>
    /* 🔥 UI/UX ERP MUTATION LOG 🔥 */
    .mutation-wrapper { font-family: 'Plus Jakarta Sans', sans-serif; color: #0f172a; }
    
    /* Toolbar Filter */
    .filter-card { border-radius: 12px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 25px; background: #fff; }
    .filter-header { background: #1e293b; color: #fff; padding: 15px 20px; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center; }
    .filter-body { padding: 20px; background: #f8fafc; border-radius: 0 0 12px 12px; border: 1px solid #e2e8f0; border-top: none; }
    
    .form-control, .form-select { border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.85rem; font-weight: 600; padding: 0.6rem 1rem; color: #334155; }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .btn-filter { background: #3b82f6; color: white; border-radius: 8px; font-weight: 700; padding: 0.6rem 1.5rem; transition: all 0.2s; border: none; }
    .btn-filter:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }

    /* Ledger Card (Per Material) */
    .ledger-card { border-radius: 10px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); margin-bottom: 25px; background: #fff; overflow: hidden; }
    
    /* Ledger Header (Identitas Part & Saldo) */
    .ledger-header { background: #fff; padding: 0; display: flex; flex-wrap: wrap; border-bottom: 3px solid #1e293b; }
    .ledger-title-box { padding: 15px 20px; flex: 1; min-width: 300px; background: #f8fafc; border-right: 1px solid #e2e8f0; }
    .ledger-title-box h4 { margin: 0; font-size: 1.1rem; font-weight: 800; color: #0f172a; }
    .ledger-title-box p { margin: 2px 0 0 0; font-size: 0.8rem; font-weight: 600; color: #64748b; }
    
    .ledger-summary-box { display: flex; flex: 2; min-width: 400px; }
    .sum-item { flex: 1; padding: 15px; text-align: center; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: center; }
    .sum-item:last-child { border-right: none; background: #f8fafc; }
    .sum-label { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: #64748b; margin-bottom: 4px; letter-spacing: 0.5px; }
    .sum-value { font-family: 'JetBrains Mono', monospace; font-size: 1.25rem; font-weight: 800; }
    
    .text-in { color: #10b981; }
    .text-out { color: #ef4444; }
    .text-balance { color: #2563eb; }

    /* Tabel History Intern */
    .table-ledger { width: 100%; margin: 0; font-size: 0.85rem; }
    .table-ledger th { background: #0f172a; color: #fff; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; padding: 10px 15px; letter-spacing: 0.5px; border: none; }
    .table-ledger td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
    .table-ledger tbody tr:hover { background: #f8fafc; }
    .table-ledger tbody tr:last-child td { border-bottom: none; }

    /* Elemen Internal Tabel */
    .mono { font-family: 'JetBrains Mono', monospace; font-weight: 700; }
    .badge-tipe { padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; display: inline-block; letter-spacing: 0.5px; }
    .bg-in { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
    .bg-out { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
    
    .coil-list { list-style: none; padding: 0; margin: 0; }
    .coil-list li { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 4px 8px; border-radius: 4px; display: inline-block; margin: 2px 4px 2px 0; font-size: 0.75rem; font-weight: 700; font-family: 'JetBrains Mono', monospace; color: #475569; }
    .coil-qty { color: #2563eb; font-weight: 900; margin-left: 4px; }
</style>

<div class="container-fluid mutation-wrapper mb-5">

    <!-- 1. TOOLBAR FILTER -->
    <div class="card filter-card">
        <div class="filter-header">
            <div>
                <h5 class="m-0 font-weight-bold" style="letter-spacing: 1px;"><i class="fas fa-route mr-2"></i> AUDIT TRAIL & MUTATION LOG</h5>
                <p class="m-0" style="font-size: 0.8rem; color: #94a3b8;">Lacak Riwayat Masuk Keluar Material per Surat Jalan & WOS</p>
            </div>
            <div>
                <a href="{{ url('/rm-store') }}" class="btn btn-light btn-sm font-weight-bold shadow-sm" style="border-radius: 8px;">
                    <i class="fas fa-arrow-left mr-1"></i> KEMBALI KE GUDANG
                </a>
            </div>
        </div>
        <div class="filter-body">
            <form action="{{ url()->current() }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold small text-muted">Tanggal Awal</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold small text-muted">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold small text-muted">Filter Customer</label>
                        <select name="customer" class="form-control">
                            <option value="">-- Semua Customer --</option>
                            @foreach($availableCustomers as $cust)
                                <option value="{{ $cust->code }}" {{ $customer == $cust->code ? 'selected' : '' }}>{{ $cust->code }} - {{ $cust->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold small text-muted">Filter Spesifikasi / Type</label>
                        <div class="input-group">
                            <select name="spec" class="form-control">
                                <option value="">-- Semua Spesifikasi --</option>
                                @foreach($availableSpecs as $spec)
                                    <option value="{{ $spec }}" {{ $specFilter == $spec ? 'selected' : '' }}>{{ $spec }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-filter" type="submit"><i class="fas fa-filter"></i> FILTER</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. LEDGER PER MATERIAL -->
    @forelse($historyData as $data)
        <div class="ledger-card">
            
            <!-- HEADER SALDO -->
            <div class="ledger-header">
                <div class="ledger-title-box">
                    <h4>[{{ $data->alias ?? 'NA' }}] {{ $data->spec }}</h4>
                    <p>Dimensi: {{ $data->size }} &nbsp;|&nbsp; Cust: <b class="text-dark">{{ $data->customer ?? 'ALL' }}</b></p>
                </div>
                <div class="ledger-summary-box">
                    <div class="sum-item">
                        <div class="sum-label">Beginning (Awal)</div>
                        <div class="sum-value text-dark">{{ number_format($data->initial) }}</div>
                    </div>
                    <div class="sum-item">
                        <div class="sum-label">Inbound (Masuk)</div>
                        <div class="sum-value text-in">+{{ number_format($data->in_qty) }}</div>
                    </div>
                    <div class="sum-item">
                        <div class="sum-label">Issued (Keluar)</div>
                        <div class="sum-value text-out">{{ number_format($data->out_qty) }}</div>
                    </div>
                    <div class="sum-item">
                        <div class="sum-label text-primary">Ending (Akhir)</div>
                        <div class="sum-value text-balance">{{ number_format($data->final) }}</div>
                    </div>
                </div>
            </div>

            <!-- TABEL HISTORI (AUDIT TRAIL) -->
            <div class="table-responsive">
                <table class="table-ledger">
                    <thead>
                        <tr>
                            <th width="15%">Waktu Transaksi</th>
                            <th width="15%">Tipe Mutasi</th>
                            <th width="20%">No. Ref (SJ/WOS)</th>
                            <th width="40%">Rincian Fisik Coil / Bundle</th>
                            <th width="10%" class="text-right">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data->logs as $log)
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: #334155;">{{ date('d M Y', strtotime($log->created_at)) }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ date('H:i:s', strtotime($log->created_at)) }}</div>
                                </td>
                                <td>
                                    @if(strpos($log->type, 'IN') !== false)
                                        <span class="badge-tipe bg-in"><i class="fas fa-arrow-down mr-1"></i> MASUK (IN)</span>
                                    @else
                                        <span class="badge-tipe bg-out"><i class="fas fa-arrow-up mr-1"></i> KELUAR (OUT)</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="mono" style="color: #2563eb; font-size: 0.9rem;">{{ $log->ref_id }}</span>
                                </td>
                                <td>
                                    <ul class="coil-list">
                                        @foreach($log->bundles as $b)
                                            <li>🏷️ {{ $b->coil_id }} <span class="coil-qty">({{ number_format($b->qty) }})</span></li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="text-right mono" style="font-size: 1rem;">
                                    @if($log->qty > 0)
                                        <span class="text-in">+{{ number_format($log->qty) }}</span>
                                    @else
                                        <span class="text-out">{{ number_format($log->qty) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted font-weight-bold" style="background: #f8fafc;">
                                    <i class="fas fa-minus-circle mr-1"></i> Tidak ada mutasi pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    @empty
        <!-- STATE JIKA KOSONG -->
        <div class="text-center py-5">
            <i class="fas fa-folder-open fa-4x text-muted mb-3" style="opacity: 0.5;"></i>
            <h5 class="font-weight-bold text-dark">Data Mutasi Tidak Ditemukan</h5>
            <p class="text-muted">Silakan sesuaikan rentang tanggal atau filter spesifikasi di atas.</p>
        </div>
    @endforelse

</div>

@endsection