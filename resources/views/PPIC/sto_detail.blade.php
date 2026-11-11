@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    .b-panel { background: #ffffff; border-radius: 16px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
    .page-title h1 { font-weight: 900; font-size: 26px; margin: 0; color: #1e293b; letter-spacing: -0.5px; }
    .page-title p { font-size: 12px; font-weight: 700; color: #64748b; margin: 4px 0 0 0; }

    .info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px 20px; display: flex; gap: 40px; margin-bottom: 25px; }
    .info-item h6 { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; margin: 0 0 5px 0; }
    .info-item h4 { font-size: 14px; font-weight: 900; color: #0f172a; margin: 0; }

    .trace-table-wrapper { overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0; }
    .trace-table { width: 100%; min-width: 1200px; border-collapse: collapse; text-align: center; }
    .trace-table th { background: rgba(37, 99, 235, 0.05); padding: 12px; font-size: 10px; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .trace-table td { padding: 12px; font-size: 12px; font-weight: 700; border-bottom: 1px solid #e2e8f0; color: #1e293b; vertical-align: middle; }
    
    .col-sys { background-color: #f8fafc; }
    .col-act { background-color: #f0fdf4; border-left: 2px solid #bbf7d0; border-right: 2px solid #bbf7d0; }
    
    .text-num { font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: bold; }
    
    .selisih-badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; display: inline-block; min-width: 40px; text-align: center; }
    .diff-ok { background: #e2e8f0; color: #475569; }
    .diff-plus { background: #dcfce7; color: #166534; }
    .diff-minus { background: #fee2e2; color: #b91c1c; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
   <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="page-title">
            <h1><i class="fas fa-file-alt text-primary mr-2"></i>DETAIL REKAPAN STO</h1>
            <p>Melihat kembali data fisik aktual dan selisih (diff) pada riwayat opname.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('ppic.sto.index') }}" class="btn btn-secondary shadow-sm" style="border-radius: 50rem; font-weight: 800; padding: 8px 20px;">
                <i class="fas fa-arrow-left mr-1"></i> KEMBALI
            </a>
            
            {{-- ✨ INI TOMBOL PRINT REKAPANNYA ✨ --}}
            <a href="{{ route('ppic.sto.print_detail', $sto->id) }}" target="_blank" class="btn btn-primary shadow-sm" style="border-radius: 50rem; font-weight: 800; padding: 8px 20px;">
                <i class="fas fa-print mr-1"></i> PRINT REKAPAN INI
            </a>
        </div>
    </div>

    <div class="b-panel">
        
        {{-- IDENTITAS DOKUMEN STO --}}
        <div class="info-card">
            <div class="info-item">
                <h6>No. Dokumen STO</h6>
                <h4 style="color: #4318FF;">{{ $sto->doc_no }}</h4>
            </div>
            <div class="info-item">
                <h6>Periode STO</h6>
                <h4>{{ $sto->period }}</h4>
            </div>
            <div class="info-item">
                <h6>Tanggal Eksekusi</h6>
                <h4>{{ date('d F Y, H:i', strtotime($sto->created_at)) }} WIB</h4>
            </div>
            <div class="info-item">
                <h6>Dicatat Oleh</h6>
                <h4>{{ $sto->created_by }}</h4>
            </div>
            <div class="info-item">
                <h6>Status Dokumen</h6>
                <h4 style="color: #059669;"><i class="fas fa-check-circle mr-1"></i>{{ $sto->status }}</h4>
            </div>
        </div>

        {{-- TABEL ANGKA REKAPAN --}}
        <div class="trace-table-wrapper">
            <table class="trace-table">
                <thead>
                    <tr>
                        <th rowspan="2" width="3%">NO</th>
                        <th rowspan="2" class="text-left">PART NAME & NUMBER</th>
                        <th rowspan="2">CUST</th>
                        <th colspan="3" class="col-sys text-primary" style="border-bottom: 2px solid #93c5fd;">STOK SISTEM (SAAT ITU)</th>
                        <th colspan="3" class="col-act text-success" style="border-bottom: 2px solid #86efac;">STOK FISIK (AKTUAL)</th>
                        <th colspan="3" class="text-danger" style="border-bottom: 2px solid #fca5a5;">SELISIH (+/-)</th>
                    </tr>
                    <tr>
                        <th class="col-sys">RM</th>
                        <th class="col-sys">WIP</th>
                        <th class="col-sys">FG</th>
                        
                        <th class="col-act">FISIK RM</th>
                        <th class="col-act">FISIK WIP</th>
                        <th class="col-act">FISIK FG</th>
                        
                        <th>RM Diff</th>
                        <th>WIP Diff</th>
                        <th>FG Diff</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($details as $idx => $d)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="text-left">
                            <b style="font-size:13px; color:#1e293b;">{{ $d->part_name ?? 'Unknown Part' }}</b><br>
                            <span style="font-size:9px; color:#64748b;">{{ $d->part_no }}</span>
                        </td>
                        <td><span class="badge badge-secondary px-2 py-1">{{ $d->customer_code ?? '-' }}</span></td>
                        
                        {{-- ANGKA SISTEM SAAT DISIMPAN --}}
                        <td class="col-sys text-num" style="color:#64748b;">{{ number_format($d->rm_sys) }}</td>
                        <td class="col-sys text-num" style="color:#64748b;">{{ number_format($d->wip_sys) }}</td>
                        <td class="col-sys text-num" style="color:#64748b;">{{ number_format($d->fg_sys) }}</td>
                        
                        {{-- ANGKA FISIK YANG DIKETIK USER --}}
                        <td class="col-act text-num" style="color:#166534;">{{ number_format($d->rm_act) }}</td>
                        <td class="col-act text-num" style="color:#166534;">{{ number_format($d->wip_act) }}</td>
                        <td class="col-act text-num" style="color:#166534;">{{ number_format($d->fg_act) }}</td>
                        
                        {{-- SELISIH RM --}}
                        <td>
                            @if($d->rm_diff > 0) <span class="selisih-badge diff-plus">+{{ number_format($d->rm_diff) }}</span>
                            @elseif($d->rm_diff < 0) <span class="selisih-badge diff-minus">{{ number_format($d->rm_diff) }}</span>
                            @else <span class="selisih-badge diff-ok">0</span> @endif
                        </td>

                        {{-- SELISIH WIP --}}
                        <td>
                            @if($d->wip_diff > 0) <span class="selisih-badge diff-plus">+{{ number_format($d->wip_diff) }}</span>
                            @elseif($d->wip_diff < 0) <span class="selisih-badge diff-minus">{{ number_format($d->wip_diff) }}</span>
                            @else <span class="selisih-badge diff-ok">0</span> @endif
                        </td>

                        {{-- SELISIH FG --}}
                        <td>
                            @if($d->fg_diff > 0) <span class="selisih-badge diff-plus">+{{ number_format($d->fg_diff) }}</span>
                            @elseif($d->fg_diff < 0) <span class="selisih-badge diff-minus">{{ number_format($d->fg_diff) }}</span>
                            @else <span class="selisih-badge diff-ok">0</span> @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-4">Data rincian tidak ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection