@extends('layout.admin')

@section('content')
<!-- Core Assets -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --ind-navy: #0f172a; --ind-blue: #4361ee; --ind-amber: #f59e0b;
        --ind-emerald: #10b981; --ind-rose: #ef4444; --bg-main: #f1f5f9;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #1e293b; }
    .industrial-header { font-family: 'Orbitron', sans-serif; letter-spacing: 1px; }
    
    /* Progress Bar Custom */
    .progress-custom { height: 8px; border-radius: 10px; background: #e2e8f0; overflow: hidden; margin-top: 5px; }
    .progress-bar-fill { height: 100%; transition: 0.6s cubic-bezier(0.4, 0, 0.2, 1); }

    /* Grouping Card */
    .batch-group-card { background: white; border-radius: 30px; margin-bottom: 2rem; border: 1px solid #eef2f6; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
    .batch-header { background: #f8fafc; padding: 20px 30px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
    
    .table-history td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f8fafc; font-weight: 700; font-size: 13px; }
    .phase-badge { background: var(--ind-navy); color: white; padding: 4px 12px; border-radius: 8px; font-family: 'JetBrains Mono'; font-size: 10px; }
    
    .ng-pill { background: #fee2e2; color: var(--ind-rose); font-size: 9px; padding: 2px 8px; border-radius: 6px; border: 1px solid #fecdd3; font-family: 'JetBrains Mono'; font-weight: 700; margin: 2px; display: inline-block; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    {{-- 1. HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-5 bg-white p-4 rounded-3xl border shadow-sm">
        <div>
            <h2 class="industrial-header m-0 text-primary">Quality_Audit <span class="text-dark">Timeline</span></h2>
            <p class="text-muted small font-weight-bold mb-0 uppercase">Batch History & Partial Inspection Tracking</p>
        </div>
        <a href="{{ route('quality.index') }}" class="btn btn-dark rounded-pill px-4 font-weight-black shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> QC TERMINAL
        </a>
    </div>

    {{-- 2. STATS (Global) --}}
    @php
        $totalOk = $historyData->sum('qty_ok');
        $totalNg = $historyData->sum('qty_ng');
        $grandTotal = $totalOk + $totalNg;
        $avgYield = $grandTotal > 0 ? ($totalOk / $grandTotal) * 100 : 0;
        
        // Grouping data by Batch Number
        $groupedHistory = $historyData->groupBy('batch_no');
    @endphp

    <div class="row mb-5">
        <div class="col-md-3"><div class="stat-card p-4 bg-white rounded-3xl border shadow-sm"><small class="stat-label d-block text-muted mb-1">Total Verified OK</small><h3 class="font-weight-black text-emerald m-0" style="font-family:'Orbitron'">{{ number_format($totalOk) }}</h3></div></div>
        <div class="col-md-3"><div class="stat-card p-4 bg-white rounded-3xl border shadow-sm"><small class="stat-label d-block text-muted mb-1">Total Verified NG</small><h3 class="font-weight-black text-rose m-0" style="font-family:'Orbitron'">{{ number_format($totalNg) }}</h3></div></div>
        <div class="col-md-3"><div class="stat-card p-4 bg-white rounded-3xl border shadow-sm"><small class="stat-label d-block text-muted mb-1">Global Yield</small><h3 class="font-weight-black text-primary m-0" style="font-family:'Orbitron'">{{ number_format($avgYield, 1) }}%</h3></div></div>
        <div class="col-md-3"><div class="stat-card p-4 bg-white rounded-3xl border shadow-sm"><small class="stat-label d-block text-muted mb-1">Active Batches</small><h3 class="font-weight-black text-dark m-0" style="font-family:'Orbitron'">{{ $groupedHistory->count() }}</h3></div></div>
    </div>

    {{-- 3. GROUPED TIMELINE --}}
    @forelse($groupedHistory as $batchNo => $records)
        @php
            $batchPart = $records->first()->part_no;
            $batchOrigin = $records->first()->origin;
            $batchTarget = $records->first()->qty_from_prod; // Target awal 200
            $batchChecked = $records->sum('total_checked');
            $percent = ($batchChecked / $batchTarget) * 100;
        @endphp

        <div class="batch-group-card animate__animated animate__fadeInUp">
            <div class="batch-header">
                <div class="d-flex align-items-center">
                    <div class="mr-4 text-center">
                        <small class="stat-label">Origin</small>
                        <div class="badge {{ $batchOrigin == 'WELDING' ? 'bg-warning text-dark' : 'bg-primary text-white' }} d-block px-3 rounded-pill" style="font-size:10px">{{ $batchOrigin }}</div>
                    </div>
                    <div>
                        <h4 class="m-0 font-weight-black text-dark" style="font-family: 'JetBrains Mono';">{{ $batchNo }}</h4>
                        <small class="text-muted font-weight-bold uppercase">{{ $batchPart }}</small>
                    </div>
                </div>

                <div style="width: 300px">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="font-weight-black small uppercase">Inspection Progress</small>
                        <small class="font-weight-black text-primary">{{ number_format($batchChecked) }} / {{ number_format($batchTarget) }} PCS</small>
                    </div>
                    <div class="progress-custom">
                        <div class="progress-bar-fill {{ $percent >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-history mb-0 text-center">
                    <thead class="bg-white">
                        <tr>
                            <th style="width: 150px">Phase</th>
                            <th class="text-left">Inspector</th>
                            <th>Verified OK</th>
                            <th>Verified NG</th>
                            <th>Return</th>
                            <th>Timestamp</th>
                            <th class="text-left">NG Summary</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records->reverse() as $index => $h) {{-- reverse supaya urutan 1, 2, 3 rill --}}
                        <tr onclick="showDetail({{ json_encode($h) }})">
                            <td><span class="phase-badge">CHECK #{{ $records->count() - $index }}</span></td>
                            <td class="text-left">
                                <div class="font-weight-black text-uppercase">{{ $h->inspector }}</div>
                            </td>
                            <td class="text-success font-weight-black">+{{ number_format($h->qty_ok) }}</td>
                            <td class="text-danger font-weight-black">+{{ number_format($h->qty_ng) }}</td>
                            <td class="text-primary font-weight-black">+{{ number_format($h->qty_ret ?? 0) }}</td>
                            <td class="text-muted small">{{ date('d/m/Y H:i', strtotime($h->created_at)) }}</td>
                            <td class="text-left">
                                @if($h->ng_reason && $h->ng_reason != 'OK GOODS')
                                    @foreach(explode(', ', $h->ng_reason) as $pill)
                                        <span class="ng-pill">{{ $pill }}</span>
                                    @endforeach
                                @else
                                    <small class="text-muted italic">ZERO_DEFECTS</small>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="text-center py-5 bg-white rounded-3xl border shadow-sm">
            <h5 class="text-muted">No inspection history available.</h5>
        </div>
    @endforelse
</div>

{{-- MODAL DETAIL (Tetap sama kodenya) --}}
@include('Quality.partials.modal_detail') {{-- Atau paste modal detail Bapak di sini --}}

@endsection