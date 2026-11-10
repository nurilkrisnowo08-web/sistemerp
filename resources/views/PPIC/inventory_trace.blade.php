@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    /* ✨ THEME WARNA & ANIMASI (PURE LIGHT MODE) ✨ */
    .b-panel { 
        background: #ffffff; 
        border-radius: 16px; 
        padding: 25px; 
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }
    
    .page-title h1 { font-weight: 900; font-size: 26px; margin: 0; color: #1e293b; letter-spacing: -0.5px; }
    .page-title p { font-size: 12px; font-weight: 700; color: #64748b; margin: 4px 0 0 0; }

    /* ✨ FILTER BOX ✨ */
    .search-box {
        background: #f4f7fe;
        border-radius: 50rem;
        padding: 8px 16px;
        display: flex; align-items: center; gap: 10px;
        border: 1px solid #e2e8f0;
    }
    .search-box input, .search-box select { 
        border: none; outline: none; background: transparent; 
        color: #1e293b; font-weight: 700; width: 100%; font-size: 13px;
        cursor: pointer;
    }

    /* ✨ TABEL UTAMA ✨ */
    .trace-table-wrapper { overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0; }
    .trace-table { width: 100%; min-width: 1000px; border-collapse: collapse; }
    .trace-table th { background: rgba(37, 99, 235, 0.05); padding: 16px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .trace-table td { padding: 16px; font-size: 13px; font-weight: 700; border-bottom: 1px solid #e2e8f0; color: #1e293b; vertical-align: middle; }
    
    /* Pill/Badges untuk angka stok Aktual */
    .stock-pill { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 900; display: inline-block; font-family: 'JetBrains Mono', monospace;}
    .pill-rm { background: rgba(245, 158, 11, 0.15); color: #d97706; }
    .pill-wip { background: rgba(239, 68, 68, 0.15); color: #dc2626; }
    .pill-fg { background: rgba(16, 185, 129, 0.15); color: #059669; }

    /* ✨ BARIS HISTORI (YANG TERSEMBUNYI) ✨ */
    .history-row { display: none; }
    .history-box { padding: 16px; border-radius: 12px; border: 1px dashed #e2e8f0; background: #ffffff; height: 100%; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
    
    .history-table { width: 100%; font-size: 11px; }
    .history-table th { color: #64748b; padding: 8px 4px; border-bottom: 1px solid #e2e8f0; font-weight: 800; }
    .history-table td { color: #1e293b; padding: 8px 4px; border-bottom: 1px solid #e2e8f0; }
    
    .btn-toggle-history { border-radius: 50rem; padding: 6px 18px; font-size: 11px; font-weight: 800; transition: all 0.3s ease; }
    
    .badge-in { background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; }
    .badge-out { background: #fee2e2; color: #b91c1c; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; }
    .badge-ret { background: #e0e7ff; color: #3730a3; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="page-title">
            <h1>MATERIAL & INVENTORY TRACEABILITY</h1>
            <p>Ruang Pelacakan Stok Aktual (FG, WIP, RM) & Rincian Log Masuk-Keluar Barang per Part Number.</p>
        </div>
        
        <form action="{{ route('ppic.inventory.trace') }}" method="GET" class="d-flex flex-wrap gap-2">
            {{-- ✨ FILTER CUSTOMER ✨ --}}
            <div class="search-box shadow-sm mr-2" style="min-width: 200px;">
                <i class="fas fa-users text-primary"></i>
                <select name="customer" onchange="this.form.submit()">
                    <option value="">ALL CUSTOMER</option>
                    @if(isset($customersList))
                        @foreach($customersList as $cust)
                            <option value="{{ $cust }}" {{ (($selectedCustomer ?? '') == $cust) ? 'selected' : '' }}>{{ strtoupper($cust) }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            
            {{-- FILTER TEKS --}}
            <div class="search-box shadow-sm" style="min-width: 280px;">
                <i class="fas fa-search text-muted"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari Part Number atau Nama Part..." onchange="this.form.submit()">
            </div>
        </form>
    </div>

    <div class="b-panel">
        <div class="trace-table-wrapper">
            <table class="trace-table text-center">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-left">Part Name & Number</th>
                        <th>Customer</th>
                        <th>Raw Material (RM)</th>
                        <th>WIP (Welding)</th>
                        <th>Finish Good (FG)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parts as $idx => $p)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-left">
                            <b style="font-size:14px; color:#1e293b;">{{ $p->part_name }}</b><br>
                            <span style="font-size:10px; color:#64748b;">{{ $p->part_no }}</span>
                        </td>
                        <td><span class="badge badge-secondary px-2 py-1">{{ $p->customer_code }}</span></td>
                        
                        {{-- ANGKA STOK AKTUAL --}}
                        <td><span class="stock-pill pill-rm">{{ number_format($p->rm_stock) }} Lbr</span></td>
                        <td><span class="stock-pill pill-wip">{{ number_format($p->wip_stock ?? 0) }} Pcs</span></td>
                        <td><span class="stock-pill pill-fg">{{ number_format($p->fg_stock ?? 0) }} Pcs</span></td>
                        
                        {{-- TOMBOL BUKA TUTUP RINCIAN --}}
                        <td>
                            <button class="btn btn-outline-primary btn-toggle-history" onclick="toggleHistory('{{ $idx }}')">
                                <i class="fas fa-eye mr-1"></i> Rincian
                            </button>
                        </td>
                    </tr>

                    {{-- ✨ BARIS RINCIAN (DISEMBUNYIKAN DEFAULT) ✨ --}}
                    <tr class="history-row" id="history-{{ $idx }}">
                        <td colspan="7" class="p-0">
                            <div class="p-4" style="background: #f8fafc; border-top: 1px dashed #cbd5e1; border-bottom: 2px solid #2563eb;">
                                <div class="row">
                                    
                                    {{-- 1. RINCIAN RAW MATERIAL (RM) --}}
                                    <div class="col-xl-4 mb-3">
                                        <div class="history-box">
                                            <h6 style="color:#d97706; font-weight:900; font-size:13px; margin-bottom:15px;"><i class="fas fa-layer-group mr-2"></i>RAW MATERIAL (RM)</h6>
                                            
                                            <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                                                <span class="badge-in" title="Total RM Masuk dari Supplier"><i class="fas fa-arrow-down mr-1"></i>In: {{ number_format($p->rm_in) }}</span>
                                                <span class="badge-out" title="Total RM Dipakai ke Mesin"><i class="fas fa-arrow-up mr-1"></i>Out: {{ number_format($p->rm_out) }}</span>
                                                <span class="badge-ret" title="Total RM Dikembalikan (Return)"><i class="fas fa-undo mr-1"></i>Ret: {{ number_format($p->rm_ret) }}</span>
                                            </div>

                                            <table class="history-table">
                                                <tr><th class="text-left">Tanggal</th><th>Material In/Out</th><th class="text-right">Qty</th></tr>
                                                
                                                {{-- ✨ FITUR BARU: Menampilkan nama material/coil_id aslinya ✨ --}}
                                                @foreach($p->history_rm_in as $in)
                                                <tr>
                                                    <td class="text-left font-weight-bold">{{ date('d M, H:i', strtotime($in->created_at)) }}</td>
                                                    <td class="text-primary font-weight-bold" style="font-size:10px;">IN: {{ $in->stock_coil_id ?? $in->stock_mat_code ?? '-' }}</td>
                                                    <td class="text-right text-success">+{{ number_format($in->pcs_in) }}</td>
                                                </tr>
                                                @endforeach
                                                
                                                @foreach($p->history_rm_out as $out)
                                                <tr>
                                                    <td class="text-left font-weight-bold">{{ date('d M, H:i', strtotime($out->created_at)) }}</td>
                                                    <td class="text-danger font-weight-bold" style="font-size:10px;">OUT: Prod ({{ $p->part_no }})</td>
                                                    <td class="text-right text-danger">-{{ number_format($out->pcs_used) }}</td>
                                                </tr>
                                                @endforeach
                                                
                                                @if(count($p->history_rm_in) == 0 && count($p->history_rm_out) == 0)
                                                    <tr><td colspan="3" class="text-center text-muted font-italic py-3">Belum ada riwayat masuk/keluar RM.</td></tr>
                                                @endif
                                            </table>
                                        </div>
                                    </div>

                                    {{-- 2. RINCIAN WIP (WELDING) --}}
                                    <div class="col-xl-4 mb-3">
                                        <div class="history-box">
                                            <h6 style="color:#dc2626; font-weight:900; font-size:13px; margin-bottom:15px;"><i class="fas fa-fire mr-2"></i>WIP (WELDING)</h6>
                                            
                                            <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                                                <span class="badge-in" title="Hasil output dari mesin Stamping"><i class="fas fa-arrow-down mr-1"></i>In: {{ number_format($p->wip_in) }}</span>
                                                <span class="badge-out" title="Ditarik masuk ke mesin Welding"><i class="fas fa-arrow-up mr-1"></i>Out: {{ number_format($p->wip_out) }}</span>
                                            </div>
                                            
                                            <div class="text-center mt-4">
                                                <i class="fas fa-exchange-alt text-muted mb-3 mt-2" style="font-size:30px; opacity:0.2;"></i>
                                                <p class="text-muted" style="font-size:10px; font-weight:700; line-height:1.6;">
                                                    * <b>WIP In</b> berasal dari Produksi Stamping.<br>
                                                    * <b>WIP Out</b> adalah part yang telah ditarik<br>untuk diproses Las (Welding).
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 3. RINCIAN FINISH GOOD (FG) --}}
                                    <div class="col-xl-4 mb-3">
                                        <div class="history-box">
                                            <h6 style="color:#059669; font-weight:900; font-size:13px; margin-bottom:15px;"><i class="fas fa-box mr-2"></i>FINISH GOOD (FG)</h6>
                                            
                                            <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                                                <span class="badge-in" title="Total Barang Jadi Masuk ke RAK"><i class="fas fa-arrow-down mr-1"></i>In: {{ number_format($p->fg_in) }}</span>
                                                <span class="badge-out" title="Total Barang Keluar via Surat Jalan"><i class="fas fa-truck mr-1"></i>Delv: {{ number_format($p->fg_out) }}</span>
                                            </div>

                                            <table class="history-table">
                                                <tr><th class="text-left">Tanggal</th><th>Sumber / Tipe</th><th class="text-right">Qty</th></tr>
                                                @foreach($p->history_fg_in as $fgin)
                                                <tr>
                                                    <td class="text-left font-weight-bold">{{ date('d M, H:i', strtotime($fgin->created_at)) }}</td>
                                                    <td>IN (Prod)</td>
                                                    <td class="text-right text-success">+{{ number_format($fgin->qty_ok) }}</td>
                                                </tr>
                                                @endforeach
                                                @foreach($p->history_delv as $delv)
                                                <tr>
                                                    <td class="text-left font-weight-bold">{{ date('d M, H:i', strtotime($delv->created_at)) }}</td>
                                                    <td>OUT (SJ)</td>
                                                    <td class="text-right text-danger">-{{ number_format($delv->qty_delivery) }}</td>
                                                </tr>
                                                @endforeach
                                                @if(count($p->history_fg_in) == 0 && count($p->history_delv) == 0)
                                                    <tr><td colspan="3" class="text-center text-muted font-italic py-3">Belum ada riwayat masuk/keluar FG.</td></tr>
                                                @endif
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-box-open text-muted" style="font-size: 40px; opacity:0.3; margin-bottom:10px;"></i><br>
                            <span class="text-muted font-weight-bold d-block">Data Part tidak ditemukan atau pilih Customer yang tepat.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // FUNGSI UNTUK BUKA-TUTUP TABEL RINCIAN 3 KOLOM
    function toggleHistory(id) {
        var row = document.getElementById('history-' + id);
        if (row.style.display === 'table-row') {
            row.classList.remove('animate__fadeIn');
            row.style.display = 'none';
        } else {
            // Tutup rincian part lain biar layar nggak kepanjangan
            var allRows = document.getElementsByClassName('history-row');
            for(var i = 0; i < allRows.length; i++){
                allRows[i].style.display = 'none';
            }
            // Buka yang di-klik dengan animasi mulus
            row.style.display = 'table-row';
            row.classList.add('animate__fadeIn');
        }
    }
</script>
@endsection