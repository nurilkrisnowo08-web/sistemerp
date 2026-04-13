@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root { 
        --brand-indigo: #6366f1; 
        --brand-emerald: #10b981; 
        --brand-rose: #f43f5e;
        --brand-slate: #1e293b;
        --ui-bg: #f8fafc; 
    }
    body { background-color: var(--ui-bg); font-family: 'Plus Jakarta Sans', sans-serif; }

    .history-filter-bar { 
        background: var(--brand-slate); border-radius: 24px; padding: 30px; 
        margin-bottom: 2.5rem; color: white; box-shadow: 0 15px 30px -10px rgba(15, 23, 42, 0.3);
    }
    .form-control-history { 
        border-radius: 12px; padding: 12px 15px; border: none; 
        background: rgba(255, 255, 255, 0.1); color: white; font-weight: 600; font-size: 13px;
    }
    .form-control-history:focus { background: rgba(255, 255, 255, 0.2); box-shadow: none; color: white; }

    .card-history { border: none; border-radius: 28px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.03); background: #fff; overflow: hidden; }
    .table thead th { background: #fdfdfd; color: #94a3b8; font-weight: 800; text-transform: uppercase; font-size: 10px; letter-spacing: 1.5px; padding: 22px 15px; }
    
    .id-code { font-family: 'JetBrains Mono'; font-weight: 800; color: var(--brand-indigo); font-size: 14px; }
    .qty-badge { font-family: 'Orbitron'; font-weight: 700; font-size: 14px; }
</style>

<div class="container-fluid mt-4 animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="font-weight-bold mb-1" style="letter-spacing: -1.5px; color: var(--brand-slate);">Welding Archive</h1>
            <p class="text-muted mb-0 font-weight-medium">Historical records of completed welding batches & quality verification.</p>
        </div>
        <a href="{{ route('welding.index') }}" class="btn btn-white rounded-pill px-4 border font-weight-bold shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Live Terminal
        </a>
    </div>

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
            <div class="col-md-3 text-right">
                <button type="submit" class="btn btn-primary btn-block rounded-pill font-weight-extrabold shadow-lg" style="height: 48px; background: var(--brand-indigo); border:none;">
                    <i class="fas fa-search mr-2"></i> EXECUTE AUDIT
                </button>
            </div>
        </form>
    </div>

    <div class="card-history shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Batch ID</th>
                        <th class="text-left">Part Identification</th>
                        <th>Target</th>
                        <th class="text-success">OK</th>
                        <th class="text-danger">NG</th>
                        <th>Completed At</th>
                        <th class="text-right pr-4">Log</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $h)
                    <tr>
                        <td class="text-left pl-4">
                            <div class="id-code">{{ $h->no_produksi_stamping }}</div>
                            <small class="badge badge-light text-muted font-weight-bold" style="font-size: 9px;">{{ $h->customer }}</small>
                        </td>
                        <td class="text-left py-4">
                            <div class="font-weight-extrabold text-dark" style="font-size: 14px;">{{ $h->part_no }}</div>
                            <small class="text-muted font-weight-bold uppercase">{{ $h->part_name }}</small>
                        </td>
                        <td class="qty-badge text-muted">{{ number_format($h->qty_masuk) }}</td>
                        <td class="qty-badge text-success">+{{ number_format($h->qty_ok) }}</td>
                        <td class="qty-badge text-danger">-{{ number_format($h->qty_ng) }}</td>
                        <td>
                            <div class="font-weight-bold text-dark" style="font-size: 12px;">{{ date('d/m/Y', strtotime($h->updated_at)) }}</div>
                            <small class="text-muted font-weight-bold">{{ date('H:i', strtotime($h->updated_at)) }} WIB</small>
                        </td>
                        <td class="text-right pr-4">
                            <button class="btn btn-light btn-sm rounded-xl border font-weight-bold px-3">
                                <i class="fas fa-file-alt mr-1 text-muted"></i> DETAIL
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-5 text-muted font-weight-bold italic">-- NO ARCHIVED DATA FOUND IN THIS PERIOD --</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection