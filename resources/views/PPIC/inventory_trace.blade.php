@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    /* ✨ THEME WARNA & ANIMASI ✨ */
    .b-panel { 
        background: #ffffff; 
        border-radius: 16px; 
        padding: 25px; 
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }
    
    .page-title h1 { font-weight: 900; font-size: 26px; margin: 0; color: #1e293b; letter-spacing: -0.5px; }
    .page-title p { font-size: 12px; font-weight: 700; color: #64748b; margin: 4px 0 0 0; }

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

    /* ✨ BARIS HISTORI ✨ */
    .history-row { display: none; }
    .history-box { padding: 16px; border-radius: 12px; border: 1px dashed #e2e8f0; background: #ffffff; height: 100%; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
    
    /* SCROLLABLE TABLE AREA UNTUK MAKSIMAL 15 DATA */
    .history-scroll {
        max-height: 220px;
        overflow-y: auto;
        padding-right: 5px;
    }
    .history-scroll::-webkit-scrollbar { width: 5px; }
    .history-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .history-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    .history-table { width: 100%; font-size: 11px; }
    .history-table th { color: #64748b; padding: 8px 4px; border-bottom: 1px solid #e2e8f0; font-weight: 800; position: sticky; top: 0; background: #fff; z-index: 1;}
    .history-table td { color: #1e293b; padding: 8px 4px; border-bottom: 1px solid #f1f5f9; }
    
    .btn-toggle-history { border-radius: 50rem; padding: 6px 18px; font-size: 11px; font-weight: 800; transition: all 0.3s ease; }
    
    .badge-in { background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; }
    .badge-out { background: #fee2e2; color: #b91c1c; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; }
    .badge-ret { background: #e0e7ff; color: #3730a3; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="page-title">
            <h1>MATERIAL & INVENTORY TRACEABILITY</h1>
            <p>Ruang Pelacakan Stok Aktual (FG, WIP, RM) & Timeline Aktivitas Material per Part Number.</p>
        </div>
        
        <form action="{{ route('ppic.inventory.trace') }}" method="GET" class="d-flex flex-wrap gap-2">
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
                        
                        <td><span class="stock-pill pill-rm">{{ number_format($p->rm_stock) }} Lbr</span></td>
                        <td><span class="stock-pill pill-wip">{{ number_format($p->wip_stock ?? 0) }} Pcs</span></td>
                        <td><span class="stock-pill pill-fg">{{ number_format($p->fg_stock ?? 0) }} Pcs</span></td>
                        
                        <td>
                            <button class="btn btn-outline-primary btn-toggle-history" onclick="toggleHistory('{{ $idx }}')">
                                <i class="fas fa-eye mr-1"></i> Rincian
                            </button>
                        </td>
                    </tr>

                    <tr class="history-row" id="history-{{ $idx }}">
                        <td colspan="7" class="p-0">
                            <div class="p-4" style="background: #f8fafc; border-top: 1px dashed #cbd5e1; border-bottom: 2px solid #2563eb;">
                                <div class="row">
                                    
                                    {{-- 1. RINCIAN RAW MATERIAL (RM) TIMELINE --}}
                                    <div class="col-xl-4 mb-3">
                                        <div class="history-box">
                                            <h6 style="color:#d97706; font-weight:900; font-size:13px; margin-bottom:15px;"><i class="fas fa-layer-group mr-2"></i>TIMELINE RM (Masuk & Keluar)</h6>
                                            
                                            <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                                                <span class="badge-in"><i class="fas fa-arrow-down mr-1"></i>In: {{ number_format($p->rm_in) }}</span>
                                                <span class="badge-out"><i class="fas fa-arrow-up mr-1"></i>Out: {{ number_format($p->rm_out) }}</span>
                                                <span class="badge-ret"><i class="fas fa-undo mr-1"></i>Ret: {{ number_format($p->rm_ret) }}</span>
                                            </div>

                                            <div class="history-scroll">
                                                <table class="history-table">
                                                    <tr><th class="text-left">Tanggal</th><th>Keterangan</th><th class="text-right">Qty</th></tr>
                                                    
                                                    @forelse($p->history_rm as $rm)
                                                        <tr>
                                                            <td class="text-left font-weight-bold">{{ date('d M, H:i', strtotime($rm->created_at)) }}</td>
                                                            @if($rm->type_log == 'IN')
                                                                @if($rm->source == 'supplier')
                                                                    <td class="text-primary font-weight-bold" style="font-size:10px;">IN SUP ({{ $rm->stock_coil_id ?? $rm->stock_mat_code }})</td>
                                                                @else
                                                                    <td class="text-info font-weight-bold" style="font-size:10px;">IN RETUR (Part: {{ $rm->material_code }})</td>
                                                                @endif
                                                                <td class="text-right text-success">+{{ number_format($rm->pcs_in) }}</td>
                                                            @else
                                                                <td class="text-danger font-weight-bold" style="font-size:10px;">OUT (Part: {{ $rm->material_code }})</td>
                                                                <td class="text-right text-danger">-{{ number_format($rm->pcs_used) }}</td>
                                                            @endif
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="3" class="text-center text-muted font-italic py-3">Belum ada riwayat RM.</td></tr>
                                                    @endforelse
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 2. RINCIAN WIP (WELDING) TIMELINE ✨ --}}
                                    <div class="col-xl-4 mb-3">
                                        <div class="history-box">
                                            <h6 style="color:#dc2626; font-weight:900; font-size:13px; margin-bottom:15px;"><i class="fas fa-fire mr-2"></i>TIMELINE WIP (WELDING)</h6>
                                            
                                            <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                                                <span class="badge-in"><i class="fas fa-arrow-down mr-1"></i>In: {{ number_format($p->wip_in) }}</span>
                                                <span class="badge-out"><i class="fas fa-arrow-up mr-1"></i>Out: {{ number_format($p->wip_out) }}</span>
                                            </div>
                                            
                                            @if(count($p->history_wip) > 0)
                                            <div class="history-scroll">
                                                <table class="history-table">
                                                    <tr><th class="text-left">Tanggal</th><th>Keterangan</th><th class="text-right">Qty</th></tr>
                                                    
                                                    @foreach($p->history_wip as $w)
                                                        <tr>
                                                            <td class="text-left font-weight-bold">{{ date('d M, H:i', strtotime($w->created_at)) }}</td>
                                                            @if($w->log_type == 'IN')
                                                                <td class="text-primary font-weight-bold" style="font-size:10px;">IN (Stamping)</td>
                                                                <td class="text-right text-success">+{{ number_format($w->qty_ok) }}</td>
                                                            @else
                                                                <td class="text-danger font-weight-bold" style="font-size:10px;">OUT (Di-Las)</td>
                                                                <td class="text-right text-danger">-{{ number_format($w->qty_masuk) }}</td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </div>
                                            @else
                                            <div class="text-center mt-4">
                                                <i class="fas fa-exchange-alt text-muted mb-3 mt-2" style="font-size:30px; opacity:0.2;"></i>
                                                <p class="text-muted" style="font-size:10px; font-weight:700; line-height:1.6;">
                                                    * <b>WIP In</b> berasal dari Produksi Stamping.<br>
                                                    * <b>WIP Out</b> adalah part yang ditarik untuk diproses Las (Welding).
                                                </p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- 3. RINCIAN FINISH GOOD (FG) TIMELINE --}}
                                    <div class="col-xl-4 mb-3">
                                        <div class="history-box">
                                            <h6 style="color:#059669; font-weight:900; font-size:13px; margin-bottom:15px;"><i class="fas fa-box mr-2"></i>TIMELINE FINISH GOOD (FG)</h6>
                                            
                                            <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                                                <span class="badge-in"><i class="fas fa-arrow-down mr-1"></i>In: {{ number_format($p->fg_in) }}</span>
                                                <span class="badge-out"><i class="fas fa-truck mr-1"></i>Delv: {{ number_format($p->fg_out) }}</span>
                                            </div>

                                            <div class="history-scroll">
                                                <table class="history-table">
                                                    <tr><th class="text-left">Tanggal</th><th>Tipe / Sumber</th><th class="text-right">Qty</th></tr>
                                                    
                                                    {{-- Kita gabung IN (Produksi) dan OUT (Delivery) untuk FG di array sementara --}}
                                                    @php
                                                        $myFgIn = collect($p->history_fg_in)->map(function($i) { $i->log_type = 'IN'; return $i; });
                                                        $myDelv = collect($p->history_delv)->map(function($i) { $i->log_type = 'OUT'; return $i; });
                                                        $fgTimeline = $myFgIn->concat($myDelv)->sortByDesc('created_at')->take(15)->values();
                                                    @endphp

                                                    @forelse($fgTimeline as $f)
                                                        <tr>
                                                            <td class="text-left font-weight-bold">{{ date('d M, H:i', strtotime($f->created_at)) }}</td>
                                                            @if($f->log_type == 'IN')
                                                                <td class="text-primary font-weight-bold" style="font-size:10px;">IN (Prod)</td>
                                                                <td class="text-right text-success">+{{ number_format($f->qty_ok) }}</td>
                                                            @else
                                                                <td class="text-danger font-weight-bold" style="font-size:10px;">OUT (SJ)</td>
                                                                <td class="text-right text-danger">-{{ number_format($f->qty_delivery) }}</td>
                                                            @endif
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="3" class="text-center text-muted font-italic py-3">Belum ada riwayat FG.</td></tr>
                                                    @endforelse
                                                </table>
                                            </div>
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
    function toggleHistory(id) {
        var row = document.getElementById('history-' + id);
        if (row.style.display === 'table-row') {
            row.classList.remove('animate__fadeIn');
            row.style.display = 'none';
        } else {
            var allRows = document.getElementsByClassName('history-row');
            for(var i = 0; i < allRows.length; i++){
                allRows[i].style.display = 'none';
            }
            row.style.display = 'table-row';
            row.classList.add('animate__fadeIn');
        }
    }
</script>
@endsection