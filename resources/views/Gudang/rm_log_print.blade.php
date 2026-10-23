@extends('layout.admin')

@section('content')
<!-- Font Profesional & Monospace -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">

<style>
    .mutation-wrapper { font-family: 'Plus Jakarta Sans', sans-serif; color: #0f172a; }
    
    /* 🎛️ Toolbar Filter (Hanya tampil di layar) */
    .filter-card { border-radius: 10px; border: 1px solid #cbd5e1; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 25px; background: #fff; }
    .filter-header { background: #0f172a; color: #fff; padding: 12px 20px; border-radius: 10px 10px 0 0; display: flex; justify-content: space-between; align-items: center; }
    .filter-body { padding: 15px 20px; background: #f8fafc; border-radius: 0 0 10px 10px; border-top: none; }
    
    .form-control, .form-select { border-radius: 6px; border: 1px solid #94a3b8; font-size: 0.85rem; font-weight: 700; padding: 0.5rem 0.8rem; color: #1e293b; }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .btn-filter { background: #3b82f6; color: white; border-radius: 6px; font-weight: 800; padding: 0.5rem 1.2rem; transition: all 0.2s; border: none; }
    .btn-filter:hover { background: #2563eb; transform: translateY(-1px); }

    /* 📊 Ledger Card (Garis Dipertebal) */
    .ledger-card { border-radius: 8px; border: 2px solid #475569; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 30px; background: #fff; overflow: hidden; }
    
    /* 🏢 Ledger Header (Garis Bawah Dipertebal) */
    .ledger-header { border-bottom: 3px solid #0f172a; background: #fff; }
    
    /* 📋 Tabel Histori (Border Lebih Jelas) */
    .table-ledger { width: 100%; margin: 0; font-size: 0.85rem; border-collapse: collapse; }
    .table-ledger th { background: #0f172a; color: #fff; font-weight: 800; text-transform: uppercase; font-size: 0.75rem; padding: 10px 15px; letter-spacing: 0.5px; border: 2px solid #0f172a; }
    .table-ledger td { padding: 12px 15px; border: 1.5px solid #94a3b8; vertical-align: middle; }
    .table-ledger tbody tr:hover { background: #f1f5f9; }
    
    .mono { font-family: 'JetBrains Mono', monospace; font-weight: 800; }
    .badge-tipe { padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; display: inline-block; letter-spacing: 0.5px; }
    .bg-in { background: #dcfce7; color: #15803d; border: 1px solid #4ade80; }
    .bg-out { background: #fee2e2; color: #b91c1c; border: 1px solid #f87171; }
    
    .coil-list { list-style: none; padding: 0; margin: 0; }
    .coil-list li { background: #fef9c3; border: 1px solid #eab308; padding: 4px 10px; border-radius: 6px; display: inline-block; margin: 2px 4px 2px 0; font-size: 0.75rem; font-weight: 800; font-family: 'JetBrains Mono', monospace; color: #854d0e; }
    .coil-qty { color: #2563eb; margin-left: 5px; }

    /* 🔥 CSS KHUSUS PRINT (GARIS HITAM PEKAT & MUNCULKAN HEADER LOGO) 🔥 */
    @media print {
        @page { size: A4 landscape; margin: 10mm; }
        body { padding: 0 !important; margin: 0 !important; background: #fff !important; color: #000 !important; }
        .filter-card, .sidebar, .navbar, footer { display: none !important; }
        .mutation-wrapper { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
        
        /* Memunculkan Header Khusus Dokumen Print */
        .print-header-area { display: block !important; margin-bottom: 25px; page-break-after: avoid; }
        
        /* Mempertebal Semua Border Menjadi Hitam Pekat */
        .ledger-card { border: 2px solid #000 !important; box-shadow: none !important; margin-bottom: 25px !important; page-break-inside: avoid; border-radius: 0 !important; }
        .ledger-header { border-bottom: 3px solid #000 !important; }
        .table-ledger th { background: #e2e8f0 !important; color: #000 !important; border: 2px solid #000 !important; }
        .table-ledger td { border: 2px solid #000 !important; color: #000 !important; }
        
        /* Reset Warna Teks Supaya Hitam Jelas */
        .text-muted, .text-dark { color: #000 !important; }
        .stat-box { border-left: 2px dashed #000 !important; }
        
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>

<div class="container-fluid mutation-wrapper mb-5">

    <!-- 🔥 HEADER KHUSUS PRINT (Tampil saat dicetak) 🔥 -->
    <div class="print-header-area" style="display: none;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 35%; vertical-align: middle;">
                    <div style="display: flex; align-items: center;">
                        <img src="{{ asset('public/admin/img/Logo-asalta.png') }}" alt="Logo Asalta" style="height: 55px; margin-right: 15px;" onerror="this.style.display='none'">
                        <div>
                            <h3 style="font-size: 15px; margin: 0; font-weight: 900; color: #000; text-transform: uppercase; letter-spacing: 0.5px;">PT. Asalta Mandiri Agung</h3>
                            <div style="font-size: 10px; font-weight: 800; color: #000; margin-top: 2px;">Dept. PPC<br>LOGISTICS & MATERIAL</div>
                        </div>
                    </div>
                </td>
                <td style="width: 40%; text-align: center; vertical-align: middle;">
                    <h1 style="margin: 0; font-size: 18px; font-weight: 900; text-decoration: underline; color: #000; letter-spacing: 1px;">AUDIT TRAIL & MUTATION LOG</h1>
                    <h2 style="margin: 6px 0 0 0; font-size: 11px; font-weight: 800; color: #000;">OFFICIAL PRINT RECORD</h2>
                </td>
                <td style="width: 25%; text-align: right; vertical-align: middle;">
                    <table style="float: right; font-size: 10px; font-weight: 800; color: #000; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 2px 5px; text-align: right;">PRINT DATE :</td>
                            <td style="padding: 2px 5px; text-align: left; font-family: 'JetBrains Mono', monospace;">{{ date('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 5px; text-align: right;">TIME :</td>
                            <td style="padding: 2px 5px; text-align: left; font-family: 'JetBrains Mono', monospace;">{{ date('H:i:s') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div style="border-bottom: 4px double #000; margin-top: 15px; margin-bottom: 10px;"></div>
    </div>

    <!-- 1. TOOLBAR FILTER (Hanya tampil di layar) -->
    <div class="card filter-card">
        <div class="filter-header">
            <div>
                <h5 class="m-0 font-weight-bold" style="letter-spacing: 1px; font-size: 1rem;"><i class="fas fa-route mr-2"></i> AUDIT TRAIL & MUTATION LOG</h5>
                <p class="m-0" style="font-size: 0.75rem; color: #cbd5e1;">Lacak Riwayat Masuk Keluar Material per Surat Jalan & WOS</p>
            </div>
            <div>
                <button type="button" onclick="window.history.back()" class="btn btn-light btn-sm font-weight-bold shadow-sm mr-2" style="border-radius: 6px; color:#0f172a; border:1px solid #cbd5e1;">
                    <i class="fas fa-arrow-left mr-1"></i> KEMBALI
                </button>
                <button type="button" onclick="window.print()" class="btn btn-primary btn-sm font-weight-bold shadow-sm" style="border-radius: 6px; background-color: #2563eb; border:none;">
                    <i class="fas fa-print mr-1"></i> CETAK LOG
                </button>
            </div>
        </div>
        <div class="filter-body">
            <form action="{{ url()->current() }}" method="GET" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-2 mb-3">
                        <label class="font-weight-bold small text-muted">Tanggal Awal</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="font-weight-bold small text-muted">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold small text-muted">Filter Customer</label>
                        <select name="customer" id="customerSelect" class="form-control">
                            <option value="">-- Semua Customer --</option>
                            @foreach($availableCustomers as $cust)
                                <option value="{{ $cust->code }}" {{ $customer == $cust->code ? 'selected' : '' }}>{{ $cust->code }} - {{ $cust->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @php
                        $smartSpecs = DB::table('rm_stocks')
                            ->leftJoin('master_materials as mm', DB::raw('TRIM(rm_stocks.spec)'), '=', DB::raw('TRIM(mm.material_type)'))
                            ->select('rm_stocks.spec', 'mm.alias_code')
                            ->when($customer, function($q) use ($customer) {
                                return $q->where('rm_stocks.customer', $customer);
                            })
                            ->groupBy('rm_stocks.spec', 'mm.alias_code')
                            ->get();
                    @endphp

                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold small text-muted">Filter Spesifikasi / Type Part</label>
                        <select name="spec" id="specSelect" class="form-control" style="font-family: 'JetBrains Mono', monospace; font-size: 0.8rem;">
                            <option value="">-- Semua Spesifikasi --</option>
                            @foreach($smartSpecs as $s)
                                @php $displayName = ($s->alias_code ? '['.$s->alias_code.'] ' : '') . $s->spec; @endphp
                                <option value="{{ $s->spec }}" {{ $specFilter == $s->spec ? 'selected' : '' }}>
                                    {{ $displayName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 mb-3">
                        <button class="btn btn-filter w-100" type="submit"><i class="fas fa-filter"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. LEDGER PER MATERIAL -->
    @forelse($historyData as $data)
        <div class="ledger-card">
            
            <!-- HEADER SALDO (Rapi & Sejajar) -->
            <div class="ledger-header d-flex flex-wrap align-items-center justify-content-between p-3">
                <div class="part-info mr-auto" style="min-width: 250px;">
                    <h5 class="font-weight-bold mb-1" style="color: #0f172a; font-size:1.15rem; letter-spacing: 0.5px;">[{{ $data->alias ?? 'NA' }}] {{ $data->spec }}</h5>
                    <div class="text-muted" style="font-size: 0.85rem; font-weight: 700;">Dimensi: {{ $data->size }} &nbsp;|&nbsp; Cust: <span class="text-dark">{{ $data->customer ?? 'ALL' }}</span></div>
                </div>
                
                <div class="part-stats d-flex align-items-center text-center mt-3 mt-md-0" style="gap: 2.5rem; padding-right: 15px;">
                    <div class="stat-box">
                        <div class="text-muted text-uppercase mb-1" style="font-size: 0.65rem; font-weight: 900; letter-spacing: 0.5px;">Beginning (Awal)</div>
                        <div class="font-weight-bold" style="font-size: 1.4rem; color: #475569; font-family: 'JetBrains Mono', monospace;">{{ number_format($data->initial) }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="text-muted text-uppercase mb-1" style="font-size: 0.65rem; font-weight: 900; letter-spacing: 0.5px;">Inbound (Masuk)</div>
                        <div class="font-weight-bold text-success" style="font-size: 1.4rem; font-family: 'JetBrains Mono', monospace;">+{{ number_format($data->in_qty) }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="text-muted text-uppercase mb-1" style="font-size: 0.65rem; font-weight: 900; letter-spacing: 0.5px;">Issued (Keluar)</div>
                        <div class="font-weight-bold text-danger" style="font-size: 1.4rem; font-family: 'JetBrains Mono', monospace;">-{{ number_format($data->out_qty) }}</div>
                    </div>
                    <!-- Garis pembatas putus-putus dipertegas -->
                    <div class="stat-box pl-4" style="border-left: 2px dashed #94a3b8;">
                        <div class="text-primary text-uppercase mb-1" style="font-size: 0.65rem; font-weight: 900; letter-spacing: 0.5px;">Ending (Akhir)</div>
                        <div class="font-weight-bold text-primary" style="font-size: 1.5rem; font-family: 'JetBrains Mono', monospace;">{{ number_format($data->final) }}</div>
                    </div>
                </div>
            </div>

            <!-- TABEL HISTORI (Tebal & Jelas) -->
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
                                    <div style="font-weight: 800; color: #0f172a; font-size: 0.85rem;">{{ date('d M Y', strtotime($log->created_at)) }}</div>
                                    <div class="text-muted mono" style="font-size: 0.75rem;">{{ date('H:i:s', strtotime($log->created_at)) }}</div>
                                </td>
                                <td>
                                    @if(strpos($log->type, 'IN') !== false)
                                        <span class="badge-tipe bg-in"><i class="fas fa-arrow-down mr-1"></i> MASUK (IN)</span>
                                    @else
                                        <span class="badge-tipe bg-out"><i class="fas fa-arrow-up mr-1"></i> KELUAR (OUT)</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="mono" style="color: #2563eb; font-size: 0.85rem;">{{ $log->ref_id }}</span>
                                </td>
                                <td>
                                    <ul class="coil-list">
                                        @foreach($log->bundles as $b)
                                            <li>🏷️ {{ $b->coil_id }} <span class="coil-qty">({{ number_format($b->qty) }})</span></li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="text-right mono" style="font-size: 1.15rem;">
                                    @if($log->qty > 0)
                                        <span class="text-success">+{{ number_format($log->qty) }}</span>
                                    @else
                                        <span class="text-danger">{{ number_format($log->qty) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted font-weight-bold" style="background: #f8fafc;">
                                    <i class="fas fa-minus-circle mr-2"></i> Tidak ada pergerakan material pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    @empty
        <!-- STATE JIKA DATA KOSONG -->
        <div class="text-center py-5">
            <i class="fas fa-folder-open fa-4x text-muted mb-3" style="opacity: 0.5;"></i>
            <h5 class="font-weight-bold text-dark">Data Mutasi Tidak Ditemukan</h5>
            <p class="text-muted">Silakan sesuaikan rentang tanggal atau pilih filter di atas.</p>
        </div>
    @endforelse

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#customerSelect').on('change', function() {
            $('#specSelect').val(''); 
            $('#filterForm').submit();
        });
    });
</script>
@endsection