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
        padding: 6px 16px;
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

    /* Pill/Badges untuk angka */
    .stock-pill { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 900; display: inline-block; font-family: 'JetBrains Mono', monospace;}
    .pill-rm { background: rgba(245, 158, 11, 0.15); color: #d97706; }
    .pill-wip { background: rgba(239, 68, 68, 0.15); color: #dc2626; }
    .pill-fg { background: rgba(16, 185, 129, 0.15); color: #059669; }
    
    body.dark-mode .pill-rm { color: #fcd34d; }
    body.dark-mode .pill-wip { color: #fca5a5; }
    body.dark-mode .pill-fg { color: #6ee7b7; }

    /* ✨ BARIS HISTORI (YANG TERSEMBUNYI) ✨ */
    .history-row { background: var(--bg-body); display: none; }
    .history-box { padding: 20px; border-radius: 12px; border: 1px dashed var(--border-color); background: var(--bg-surface); margin-bottom: 10px;}
    
    .history-table { width: 100%; font-size: 11px; }
    .history-table th { color: var(--text-muted); padding: 8px; border-bottom: 1px solid var(--border-color); font-weight: 800; }
    .history-table td { color: var(--text-main); padding: 8px; border-bottom: 1px solid var(--border-color); }
    
    .btn-toggle-history { border-radius: 50rem; padding: 4px 14px; font-size: 11px; font-weight: 800; transition: all 0.3s ease; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="page-title">
            <h1>MATERIAL & INVENTORY TRACEABILITY</h1>
            <p>Ruang Pelacakan Stok Aktual (FG, WIP, RM) & Rincian Riwayat Bulan/Tanggal Masuk Barang.</p>
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
                        <th>Rincian Masuk</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parts as $idx => $p)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-left">
                            <b style="font-size:14px; color: var(--text-main);">{{ $p->part_name }}</b><br>
                            <span style="font-size:10px; color: var(--text-muted);">{{ $p->part_no }}</span>
                        </td>
                        <td><span class="badge badge-secondary px-2 py-1">{{ $p->customer_code }}</span></td>
                        
                        {{-- ANGKA STOK --}}
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
                    <tr class="history-row animate__animated animate__fadeIn" id="history-{{ $idx }}">
                        <td colspan="7" class="p-4" style="background: rgba(0,0,0,0.02);">
                            <div class="row">
                                {{-- TABEL RIWAYAT RM (MATERIAL MASUK) --}}
                                <div class="col-md-6">
                                    <div class="history-box">
                                        <h6 style="font-weight:900; color:#d97706; font-size:12px; margin-bottom:12px;"><i class="fas fa-dolly mr-2"></i>5 RIWAYAT TERBARU KEDATANGAN RM</h6>
                                        <table class="history-table">
                                            <thead>
                                                <tr>
                                                    <th class="text-left">Bulan / Tanggal</th>
                                                    <th>Sumber / No PO</th>
                                                    <th class="text-right">Qty Masuk</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($p->history_rm as $rm)
                                                <tr>
                                                    <td class="text-left font-weight-bold">{{ date('d M Y, H:i', strtotime($rm->created_at)) }}</td>
                                                    <td>{{ $rm->source == 'supplier' ? 'Supplier' : 'Gudang/Return' }}</td>
                                                    <td class="text-right text-success">+{{ number_format($rm->pcs_in) }}</td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="3" class="text-center font-italic text-muted">Belum ada riwayat kedatangan RM.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- TABEL RIWAYAT PRODUKSI (WIP / FG MASUK) --}}
                                <div class="col-md-6">
                                    <div class="history-box">
                                        <h6 style="font-weight:900; color:#059669; font-size:12px; margin-bottom:12px;"><i class="fas fa-industry mr-2"></i>5 RIWAYAT TERBARU HASIL PRODUKSI (WIP/FG)</h6>
                                        <table class="history-table">
                                            <thead>
                                                <tr>
                                                    <th class="text-left">Bulan / Tanggal</th>
                                                    <th>Mesin / Line</th>
                                                    <th class="text-right">Qty Dihasilkan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($p->history_prod as $prod)
                                                <tr>
                                                    <td class="text-left font-weight-bold">{{ date('d M Y, H:i', strtotime($prod->created_at)) }}</td>
                                                    <td>{{ $prod->line_code }} ({{ $prod->shift }})</td>
                                                    <td class="text-right text-success">+{{ number_format($prod->qty_ok) }}</td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="3" class="text-center font-italic text-muted">Belum ada riwayat hasil produksi.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
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
    // FUNGSI UNTUK BUKA-TUTUP TABEL RINCIAN
    function toggleHistory(id) {
        var row = document.getElementById('history-' + id);
        if (row.style.display === 'table-row') {
            row.style.display = 'none';
        } else {
            // Tutup semua yang lain dulu (Opsional, agar rapi)
            var allRows = document.getElementsByClassName('history-row');
            for(var i = 0; i < allRows.length; i++){
                allRows[i].style.display = 'none';
            }
            // Buka yang di-klik
            row.style.display = 'table-row';
        }
    }
</script>
@endsection