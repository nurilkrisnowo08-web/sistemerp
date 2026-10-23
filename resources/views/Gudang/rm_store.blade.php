@extends('layout.admin')

@section('content')
<!-- Font Profesional & Monospace untuk Angka -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">

<style>
    /* 🔥 KUSTOMISASI UI ERP MODERN 🔥 */
    .rm-wrapper { font-family: 'Plus Jakarta Sans', sans-serif; color: #0f172a; }
    
    /* Toolbar Filter */
    .filter-card { border-radius: 12px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 20px; background: #fff; }
    .filter-header { background: #1e293b; color: #fff; padding: 15px 20px; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center; }
    .filter-body { padding: 20px; background: #f8fafc; border-radius: 0 0 12px 12px; border: 1px solid #e2e8f0; border-top: none; }
    
    .form-control, .form-select { border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.85rem; font-weight: 600; padding: 0.6rem 1rem; color: #334155; }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .btn-filter { background: #3b82f6; color: white; border-radius: 8px; font-weight: 700; padding: 0.6rem 1.5rem; transition: all 0.2s; border: none; }
    .btn-filter:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }

    /* Group Card (Material) */
    .material-card { border-radius: 10px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); margin-bottom: 15px; background: #fff; overflow: hidden; transition: all 0.2s; }
    .material-card:hover { border-color: #94a3b8; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
    
    .material-header { padding: 15px 20px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; background: #fff; border-bottom: 1px solid transparent; }
    .material-header[aria-expanded="true"] { background: #f8fafc; border-bottom-color: #e2e8f0; }
    
    .spec-title { font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: 0.5px; }
    .spec-subtitle { font-size: 0.8rem; font-weight: 600; color: #64748b; margin: 0; }
    
    .stock-badge { background: #dcfce7; color: #166534; border: 1px solid #86efac; padding: 6px 15px; border-radius: 20px; font-weight: 800; font-size: 0.95rem; font-family: 'JetBrains Mono', monospace; }
    .stock-badge.empty { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }

    /* Tabel Detail Internal */
    .inner-body { padding: 20px; background: #fff; }
    .section-title { font-size: 0.85rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; }
    
    .table-custom { width: 100%; font-size: 0.8rem; }
    .table-custom th { background: #f1f5f9; color: #475569; font-weight: 800; text-transform: uppercase; padding: 10px; border-bottom: 2px solid #cbd5e1; }
    .table-custom td { padding: 10px; border-bottom: 1px solid #e2e8f0; font-weight: 600; vertical-align: middle; }
    
    .mono { font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; font-weight: 700; }
    .badge-coil { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; padding: 3px 8px; border-radius: 6px; font-family: 'JetBrains Mono', monospace; font-weight: 800; }
    
    .log-in { color: #10b981; font-weight: 800; }
    .log-out { color: #ef4444; font-weight: 800; }
    .badge-tipe { padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
    .bg-in { background: #dcfce7; color: #15803d; }
    .bg-out { background: #fee2e2; color: #b91c1c; }

    /* Scrollable Container untuk Log */
    .scrollable-logs { max-height: 300px; overflow-y: auto; padding-right: 5px; }
    .scrollable-logs::-webkit-scrollbar { width: 6px; }
    .scrollable-logs::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    .scrollable-logs::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .scrollable-logs::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<div class="container-fluid rm-wrapper mb-5">
    
    <!-- 1. TOOLBAR FILTER -->
    <div class="card filter-card">
        <div class="filter-header">
            <div>
                <h5 class="m-0 font-weight-bold" style="letter-spacing: 1px;"><i class="fas fa-boxes mr-2"></i> RAW MATERIAL STORE</h5>
                <p class="m-0" style="font-size: 0.8rem; color: #94a3b8;">Dashboard Stok Fisik & Sinkronisasi Mutasi Logistik</p>
            </div>
            <div>
                <!-- Tombol Pintasan ke Halaman Print Rekap -->
                <a href="{{ url('/rm-log-print') }}" class="btn btn-light btn-sm font-weight-bold shadow-sm" style="border-radius: 8px;">
                    <i class="fas fa-print mr-1"></i> CETAK REKAPITULASI BPRM
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
                        <label class="font-weight-bold small text-muted">Pencarian Part / Alias</label>
                        <div class="input-group">
                            <input type="text" name="alias" class="form-control" placeholder="Ketik nama part..." value="{{ request('alias') }}">
                            <div class="input-group-append">
                                <button class="btn btn-filter" type="submit"><i class="fas fa-search"></i> FILTER</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. DAFTAR MATERIAL (ACCORDION) -->
    <div class="accordion" id="rmAccordion">
        @forelse($groupedMaterials as $index => $group)
            <div class="material-card">
                <!-- Card Header (Bisa di-klik) -->
                <div class="material-header" id="heading{{ $index }}" data-toggle="collapse" data-target="#collapse{{ $index }}" aria-expanded="false" aria-controls="collapse{{ $index }}">
                    <div class="d-flex align-items-center">
                        <div class="mr-3 text-primary">
                            <i class="fas fa-chevron-circle-down fa-lg"></i>
                        </div>
                        <div>
                            <h4 class="spec-title">[{{ $group->alias_code ?? $group->customer }}] {{ $group->spec }}</h4>
                            <p class="spec-subtitle">Dimensi: {{ $group->size }} &nbsp;|&nbsp; Master Customer: <span class="text-dark">{{ $group->customer }}</span></p>
                        </div>
                    </div>
                    <div>
                        <div class="text-right mb-1" style="font-size: 0.7rem; font-weight: 700; color: #64748b;">TOTAL FISIK GUDANG:</div>
                        <div class="stock-badge {{ $group->total_live <= 0 ? 'empty' : '' }}">
                            {{ number_format($group->total_live) }} PCS
                        </div>
                    </div>
                </div>

                <!-- Card Body (Rincian) -->
                <div id="collapse{{ $index }}" class="collapse" aria-labelledby="heading{{ $index }}" data-parent="#rmAccordion">
                    <div class="inner-body border-top">
                        <div class="row">
                            
                            <!-- KOLOM KIRI: FISIK COIL SAAT INI -->
                            <div class="col-md-5 mb-4">
                                <div class="section-title"><i class="fas fa-pallet mr-2 text-primary"></i>Rincian Coil Tersedia (Live)</div>
                                @if($group->details->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table-custom">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Identitas Coil</th>
                                                    <th class="text-right">Stok Aktual</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $noCoil = 1; @endphp
                                                @foreach($group->details as $coil)
                                                    <tr>
                                                        <td class="text-center" width="10%">{{ $noCoil++ }}</td>
                                                        <td><span class="badge-coil"><i class="fas fa-tag mr-1"></i>{{ $coil->coil_id }}</span></td>
                                                        <td class="text-right mono text-success" style="font-size: 0.95rem;">{{ number_format($coil->stock_pcs) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-light text-center border mt-2 py-4">
                                        <i class="fas fa-box-open fa-2x text-muted mb-2"></i><br>
                                        <b class="text-muted">Stok fisik kosong (0 Pcs)</b>
                                    </div>
                                @endif
                            </div>

                            <!-- KOLOM KANAN: LOG MUTASI -->
                            <div class="col-md-7 mb-4">
                                <div class="section-title d-flex justify-content-between">
                                    <span><i class="fas fa-history mr-2 text-info"></i>Riwayat Transaksi</span>
                                    <span class="text-muted text-lowercase" style="font-size: 0.7rem; font-weight:600;">(Periode: {{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }})</span>
                                </div>
                                
                                @if($group->combined_logs->count() > 0)
                                    <div class="scrollable-logs">
                                        <table class="table-custom">
                                            <thead style="position: sticky; top: 0; z-index: 1;">
                                                <tr>
                                                    <th>Waktu</th>
                                                    <th>Tipe / No. Dokumen</th>
                                                    <th>Rincian Item</th>
                                                    <th class="text-right">Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($group->combined_logs as $log)
                                                    <tr>
                                                        <td class="text-muted" style="font-size: 0.75rem;">
                                                            {{ date('d/m/Y', strtotime($log->created_at)) }}<br>
                                                            <b class="text-dark">{{ date('H:i', strtotime($log->created_at)) }}</b>
                                                        </td>
                                                        
                                                        @if($log->type == 'INBOUND_GROUP')
                                                            <td>
                                                                <span class="badge-tipe bg-in"><i class="fas fa-arrow-down mr-1"></i>MASUK</span><br>
                                                                <span class="text-dark" style="font-size:0.75rem; font-weight:800;">SJ: {{ $log->no_sj }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="text-muted" style="font-size:0.75rem;">Total Menerima:</span><br>
                                                                <b>{{ $log->bundle_count }} Bundle/Coil</b>
                                                            </td>
                                                            <td class="text-right mono log-in">+{{ number_format($log->total_qty) }}</td>
                                                        @else
                                                            <td>
                                                                <span class="badge-tipe bg-out"><i class="fas fa-arrow-up mr-1"></i>KELUAR</span><br>
                                                                <span class="text-dark" style="font-size:0.75rem; font-weight:800;">WOS: {{ $log->no_sj }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="text-muted" style="font-size:0.75rem;">Coil Terpakai:</span><br>
                                                                <span class="badge-coil" style="font-size:0.7rem;">{{ $log->coil_id ?? 'N/A' }}</span>
                                                            </td>
                                                            <td class="text-right mono log-out">-{{ number_format($log->total_qty) }}</td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-light text-center border mt-2 py-4">
                                        <i class="fas fa-file-invoice fa-2x text-muted mb-2"></i><br>
                                        <b class="text-muted">Tidak ada transaksi pada periode ini.</b>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="fas fa-search-minus fa-4x text-muted mb-3"></i>
                <h5 class="font-weight-bold text-dark">Data Material Tidak Ditemukan</h5>
                <p class="text-muted">Silakan ubah filter pencarian atau tanggal di atas.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Menggunakan jQuery untuk fungsi Collapse/Accordion bawaan Bootstrap -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Animasi icon saat Accordion dibuka/ditutup
        $('.collapse').on('show.bs.collapse', function () {
            $(this).parent().find(".fa-chevron-circle-down").removeClass("fa-chevron-circle-down").addClass("fa-chevron-circle-up").css("color", "#2563eb");
        }).on('hide.bs.collapse', function () {
            $(this).parent().find(".fa-chevron-circle-up").removeClass("fa-chevron-circle-up").addClass("fa-chevron-circle-down").css("color", "#94a3b8");
        });
    });
</script>
@endsection