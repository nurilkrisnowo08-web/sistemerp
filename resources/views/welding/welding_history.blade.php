@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root { 
        --brand-indigo: #6366f1; 
        --brand-slate: #1e293b;
        --ui-bg: #f1f5f9; 
    }
    body { background-color: var(--ui-bg); font-family: 'Plus Jakarta Sans', sans-serif; }

    .history-filter-bar { 
        background: var(--brand-slate); border-radius: 24px; padding: 30px; 
        margin-bottom: 2rem; color: white; box-shadow: 0 15px 30px -10px rgba(15, 23, 42, 0.3);
    }
    .form-control-history { 
        border-radius: 12px; padding: 12px 15px; border: none; 
        background: rgba(255, 255, 255, 0.1); color: white; font-weight: 600; font-size: 13px;
    }

    .card-history { border: none; border-radius: 28px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.03); background: #fff; overflow: hidden; }
    
    /* ✨ LEDGER HEADER RILL ✨ */
    .table-ledger thead th { vertical-align: middle; border: 1px solid #e2e8f0; }
    .header-mutation-label { background: #0f172a; color: #ffffff; font-size: 9px; text-transform: uppercase; letter-spacing: 3px; font-weight: 800; padding: 10px !important; }
    .table-ledger th { background-color: #f8fafc; color: #94a3b8; font-weight: 800; text-transform: uppercase; font-size: 10px; letter-spacing: 1px; padding: 15px; }
    
    /* Column Colors */
    .col-init { background: rgba(148, 163, 184, 0.05); color: #64748b; font-family: 'JetBrains Mono'; }
    .col-in { background: rgba(16, 185, 129, 0.05); color: #10b981; font-family: 'JetBrains Mono'; }
    .col-out { background: rgba(239, 68, 68, 0.05); color: #ef4444; font-family: 'JetBrains Mono'; }
    .col-final { background: rgba(99, 102, 241, 0.08); color: var(--brand-indigo); font-family: 'Orbitron'; font-weight: 900 !important; }

    .id-code { font-family: 'JetBrains Mono'; font-weight: 800; color: var(--brand-indigo); font-size: 14px; }
</style>

<div class="container-fluid mt-4 animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="font-weight-bold mb-1" style="letter-spacing: -1.5px; color: var(--brand-slate);">Welding Mutation <span class="text-primary">Ledger rill</span></h1>
            <p class="text-muted mb-0 font-weight-medium">Audit trail of WIP material flow within Welding Department.</p>
        </div>
        <a href="{{ route('welding.index') }}" class="btn btn-white rounded-pill px-4 border font-weight-bold shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Live Terminal
        </a>
    </div>

    {{-- FILTER PANEL --}}
    <div class="history-filter-bar animate__animated animate__fadeInDown">
        <form action="{{ route('welding.history') }}" method="GET" class="row align-items-end">
            <div class="col-md-3">
                <label class="small font-weight-bold mb-2 ml-1 text-uppercase opacity-50">Client Entity</label>
                <select name="customer" class="form-control-history w-100">
                    <option value="ALL">-- ALL ENTITIES --</option>
                    @foreach($clients as $c)
                        <option value="{{ trim($c->code) }}" {{ ($customerFilter == trim($c->code)) ? 'selected' : '' }}>{{ strtoupper($c->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold mb-2 ml-1 text-uppercase opacity-50">Range Start</label>
                <input type="date" name="start_date" class="form-control-history w-100" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold mb-2 ml-1 text-uppercase opacity-50">Range End</label>
                <input type="date" name="end_date" class="form-control-history w-100" value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-block rounded-pill font-weight-extrabold shadow-lg" style="height: 48px; background: var(--brand-indigo); border:none;">
                    <i class="fas fa-sync-alt mr-2"></i> EXECUTE AUDIT rill!
                </button>
            </div>
        </form>
    </div>

    {{-- LEDGER TABLE --}}
    <div class="card-history shadow-sm">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-left pl-4" style="width: 30%;">Part Identification</th>
                        <th colspan="4" class="header-mutation-label">WIP Mutation Ledger (PCS)</th>
                        <th rowspan="2" style="width: 15%;">Status</th>
                    </tr>
                    <tr>
                        <th class="col-init">Stok Awal</th>
                        <th class="col-in">IN (Stp)</th>
                        <th class="col-out">OUT (Wld)</th>
                        <th class="col-final">Final Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $h)
                    <tr>
                        <td class="text-left pl-4 py-4">
                            <div class="id-code">{{ $h->part_no }}</div>
                            <small class="text-muted font-weight-bold uppercase">{{ $h->part_name }}</small>
                            <div class="badge badge-light border text-muted px-2 py-0" style="font-size: 8px;">{{ $h->customer }}</div>
                        </td>
                        <td class="col-init">{{ number_format($h->stock_awal) }}</td>
                        <td class="col-in">+{{ number_format($h->total_in) }}</td>
                        <td class="col-out">-{{ number_format($h->total_out) }}</td>
                        <td class="col-final">{{ number_format($h->stock_akhir) }}</td>
                        <td>
                            @if($h->stock_akhir <= 0)
                                <span class="badge badge-secondary rounded-pill px-3">DEPLETED</span>
                            @else
                                <span class="badge badge-success rounded-pill px-3">AVAILABLE</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-5 text-muted font-weight-bold italic">-- NO AUDIT DATA FOUND IN THIS PERIOD --</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection