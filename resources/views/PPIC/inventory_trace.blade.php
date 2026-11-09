@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    /* ✨ THEME WARNA & ANIMASI ✨ */
    .b-panel { 
        background: var(--bg-surface); 
        border-radius: 16px; 
        padding: 25px; 
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }
    
    .page-title h1 { font-weight: 900; font-size: 26px; margin: 0; color: var(--text-main); letter-spacing: -0.5px; }
    .page-title p { font-size: 12px; font-weight: 700; color: var(--text-muted); margin: 4px 0 0 0; }

    .search-box {
        background: var(--bg-body);
        border-radius: 50rem;
        padding: 8px 16px;
        display: flex; align-items: center; gap: 10px;
        border: 1px solid var(--border-color);
        width: 100%; max-width: 350px;
    }
    .search-box input { border: none; outline: none; background: transparent; color: var(--text-main); font-weight: 600; width: 100%; font-size: 13px;}

    /* ✨ TABEL UTAMA ✨ */
    .trace-table-wrapper { overflow-x: auto; border-radius: 12px; border: 1px solid var(--border-color); }
    .trace-table { width: 100%; min-width: 900px; border-collapse: collapse; }
    .trace-table th { background: rgba(37, 99, 235, 0.05); padding: 16px; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid var(--border-color); }
    .trace-table td { padding: 16px; font-size: 13px; font-weight: 700; border-bottom: 1px solid var(--border-color); color: var(--text-main); vertical-align: middle; }
    
    body.dark-mode .trace-table th { background: rgba(0,0,0,0.3); color: #94a3b8; }
    body.dark-mode .trace-table td { border-color: rgba(255,255,255,0.05); }

    /* Pill/Badges untuk angka stok Aktual */
    .stock-pill { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 900; display: inline-block; font-family: 'JetBrains Mono', monospace;}
    .pill-rm { background: rgba(245, 158, 11, 0.15); color: #d97706; }
    .pill-wip { background: rgba(239, 68, 68, 0.15); color: #dc2626; }
    .pill-fg { background: rgba(16, 185, 129, 0.15); color: #059669; }
    
    body.dark-mode .pill-rm { color: #fcd34d; }
    body.dark-mode .pill-wip { color: #fca5a5; }
    body.dark-mode .pill-fg { color: #6ee7b7; }

    /* ✨ BARIS HISTORI (YANG TERSEMBUNYI) ✨ */
    .history-row { display: none; }
    .history-box { padding: 16px; border-radius: 12px; border: 1px dashed var(--border-color); background: var(--bg-surface); height: 100%; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
    
    .history-table { width: 100%; font-size: 11px; }
    .history-table th { color: var(--text-muted); padding: 8px 4px; border-bottom: 1px solid var(--border-color); font-weight: 800; }
    .history-table td { color: var(--text-main); padding: 8px 4px; border-bottom: 1px solid var(--border-color); }
    
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
        
        <form action="{{ route('ppic.inventory.trace') }}" method="GET" class="search-box shadow-sm">
            <i class="fas fa-search text-muted"></i>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari Part Number atau Nama Part..." onchange="this.form.submit()">
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
                            <b style="font-size:14px;">{{ $p->part_name }}</b><br>
                            <span style="font-size:10px; color: var(--text-muted);">{{ $p->part_no }}</span>
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
                            <div class="p-4" style="background: var(--bg-body); border-top: 1px dashed var(--border-color);">
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
                                                <tr><th class="text-left">Tanggal</th><th>Tipe / Sumber</th><th class="text-right">Qty</th></tr>
                                                @foreach($p->history_rm_in as $in)
                                                <tr>
                                                    <td class="text-left">{{ date('d M, H:i', strtotime($in->created_at)) }}</td>
                                                    <td>IN ({{ $in->source }})</td>
                                                    <td class="text-right text-success">+{{ $in->pcs_in }}</td>
                                                </tr>
                                                @endforeach
                                                @foreach($p->history_rm_out as $out)
                                                <tr>
                                                    <td class="text-left">{{ date('d M, H:i', strtotime($out->created_at)) }}</td>
                                                    <td>OUT (Prod)</td>
                                                    <td class="text-right text-danger">-{{ $out->pcs_used }}</td>
                                                </tr>
                                                @endforeach
                                                @if(count($p->history_rm_in) == 0 && count($p->history_rm_out) == 0)
                                                    <tr><td colspan="3" class="text-center text-muted font-italic py-2">Belum ada riwayat aktivitas.</td></tr>
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
                                                <i class="fas fa-exchange-alt text-muted mb-2" style="font-size:24px; opacity:0.3;"></i>
                                                <p class="text-muted" style="font-size:10px; font-weight:600; line-height:1.5;">
                                                    *WIP In berasal dari Output Produksi Stamping.<br>
                                                    *WIP Out adalah part yang ditarik untuk diproses Las (Welding).
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
                                                <tr><th class="text-left">Tanggal</th><th>Tipe / Sumber</th><th class="text-right">Qty</th></tr>
                                                @foreach($p->history_fg_in as $fgin)
                                                <tr>
                                                    <td class="text-left">{{ date('d M, H:i', strtotime($fgin->created_at)) }}</td>
                                                    <td>IN (Prod)</td>
                                                    <td class="text-right text-success">+{{ $fgin->qty_ok }}</td>
                                                </tr>
                                                @endforeach
                                                @foreach($p->history_delv as $delv)
                                                <tr>
                                                    <td class="text-left">{{ date('d M, H:i', strtotime($delv->created_at)) }}</td>
                                                    <td>OUT (SJ)</td>
                                                    <td class="text-right text-danger">-{{ $delv->qty_delivery }}</td>
                                                </tr>
                                                @endforeach
                                                @if(count($p->history_fg_in) == 0 && count($p->history_delv) == 0)
                                                    <tr><td colspan="3" class="text-center text-muted font-italic py-2">Belum ada riwayat aktivitas.</td></tr>
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
                            <i class="fas fa-box-open text-muted" style="font-size: 30px; opacity:0.5;"></i><br>
                            <span class="text-muted font-weight-bold mt-2 d-block">Data Part tidak ditemukan.</span>
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