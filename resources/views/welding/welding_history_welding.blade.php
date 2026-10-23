@extends('layout.admin')

@section('content')
@php
    /** 
     * ✨ SAFETY FALLBACK
     * Jika Controller tidak mengirim data, dia akan otomatis ambil sendiri.
     * Ini yang bikin Anti-Error rill!
     */
    $dataHist = isset($historyData) ? $historyData : DB::table('welding_batches')
        ->leftJoin('line_welding', 'welding_batches.line_id', '=', 'line_welding.id')
        ->where('welding_batches.status', 'COMPLETED')
        ->select('welding_batches.*', 'line_welding.nama_line')
        ->orderBy('welding_batches.updated_at', 'desc')
        ->get();
@endphp

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">

<style>
    body { background-color: #f4f7f6; font-family: 'Plus Jakarta Sans', sans-serif; }
    
    /* 📝 TEXT & TYPOGRAPHY */
    .vault-header-title { font-family: 'Orbitron'; font-weight: 900; color: #4361ee; font-size: 1.4rem; letter-spacing: -0.5px; text-transform: uppercase; }
    .vault-header-title span { color: #64748b; font-family: 'Plus Jakarta Sans'; font-weight: 800; font-size: 1.2rem; }
    
    /* 🏷️ BADGES */
    .badge-sync { background: #ede9fe; color: #6366f1; font-weight: 800; font-size: 11px; padding: 6px 16px; border-radius: 20px; border: 1px solid #ddd6fe; text-transform: uppercase; letter-spacing: 1px; }
    .badge-completed { background: #10b981; color: #fff; font-size: 10px; font-weight: 800; padding: 6px 16px; border-radius: 20px; letter-spacing: 1px; }
    
    /* 📋 TABLE VAULT (Meniru desain gambar Bapak) */
    .vault-container { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); overflow: hidden; }
    .table-vault { margin-bottom: 0; }
    .table-vault thead th { background: #fff; color: #64748b; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px; border-bottom: 1px solid #e2e8f0; border-top: none; }
    .table-vault td { padding: 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 14px; color: #334155; }
    
    /* 🎨 COLOR CODING */
    .val-ok { color: #10b981 !important; }
    .val-ng { color: #ef4444 !important; }
    .val-ret { color: #4361ee !important; }
    .val-eff { color: #f59e0b !important; }
    .batch-text { color: #4361ee; font-weight: 700; }
    
    /* 🔍 FILTER SECTION */
    .vault-filter { background: #fff; padding: 15px 25px; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
</style>

<div class="container-fluid py-4 px-4">
    
    {{-- 🛰️ FILTER SECTION (Standard Vault Style) --}}
    <div class="vault-filter">
        <div>
            <h4 class="m-0 font-weight-bold" style="color: #0f172a;">Production Vault</h4>
            <small class="text-muted font-weight-bold">Master record of all completed welding batches.</small>
        </div>
        <form action="" method="GET" class="d-flex align-items-center bg-light p-2 rounded-pill border">
            <input type="date" name="start_date" value="{{ $startDate ?? date('Y-m-d') }}" class="form-control form-control-sm border-0 bg-transparent px-3 font-weight-bold">
            <span class="mx-2 text-muted">-</span>
            <input type="date" name="end_date" value="{{ $endDate ?? date('Y-m-d') }}" class="form-control form-control-sm border-0 bg-transparent px-3 font-weight-bold">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 ml-2 font-weight-bold">SYNC</button>
        </form>
    </div>

    {{-- 📋 VAULT TABLE (100% Mengikuti Gambar Bapak) --}}
    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
        <h5 class="vault-header-title m-0">PRODUCTION_ARCHIVE <span>(RECENT)</span></h5>
        <span class="badge-sync">LIVE_SYNC_ACTIVE</span>
    </div>

    <div class="vault-container">
        <div class="table-responsive">
            <table class="table table-vault text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">TIMESTAMP</th>
                        <th>BATCH ID</th>
                        <th class="text-left">PART IDENTIFICATION</th>
                        <th class="val-ok">OK</th>
                        <th class="val-ng">NG</th>
                        <th class="val-ret">RET</th>
                        <th class="val-eff">EFFICIENCY</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataHist as $h)
                    @php
                        // Logic Effisiensi
                        $total = (float)$h->qty_masuk - (float)$h->qty_return;
                        $eff = $total > 0 ? ($h->qty_ok / $total) * 100 : 0;
                    @endphp
                    <tr>
                        <td class="text-left pl-5">
                            <div class="text-muted" style="font-size: 11px;">{{ date('H:i', strtotime($h->updated_at)) }}</div>
                            <div class="text-muted" style="font-size: 12px;">{{ date('d M Y', strtotime($h->updated_at)) }}</div>
                        </td>
                        <td>
                            <span class="batch-text">{{ $h->no_produksi_stamping }}</span>
                        </td>
                        <td class="text-left">
                            <div class="text-dark font-weight-bold">> {{ $h->part_no }}</div>
                            <div class="text-muted" style="font-size: 10px; text-transform: uppercase;">{{ $h->nama_line ?? 'WELDING SPOT' }}</div>
                        </td>
                        <td class="val-ok">{{ number_format($h->qty_ok) }}</td>
                        <td class="val-ng">{{ number_format($h->qty_ng) }}</td>
                        <td class="val-ret">{{ number_format($h->qty_return) }}</td>
                        <td class="val-eff">{{ number_format($eff, 1) }}%</td>
                        <td><span class="badge-completed">COMPLETED</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-5 text-muted font-weight-bold text-center">NO DATA FOUND</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection