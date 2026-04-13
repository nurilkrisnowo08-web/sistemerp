@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root { 
        --brand-indigo: #6366f1; 
        --brand-emerald: #10b981; 
        --brand-slate: #1e293b;
        --ui-bg: #f8fafc; 
    }

    body { background-color: var(--ui-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: #334155; }

    /* Elegant UI Components */
    .card-history { 
        border: none; border-radius: 28px; 
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.03); 
        background: #fff; border: 1px solid rgba(226, 232, 240, 0.8);
        overflow: hidden;
    }
    
    .table thead th { 
        background: #fdfdfd; color: #94a3b8; font-weight: 800; 
        text-transform: uppercase; font-size: 10px; letter-spacing: 1.5px; 
        padding: 22px 15px; border-bottom: 2px solid #f1f5f9;
    }

    .expand-trigger { cursor: pointer; transition: all 0.2s; }
    .expand-trigger:hover { background-color: #f8fafc !important; }

    .id-code { font-family: 'JetBrains Mono'; font-weight: 800; color: var(--brand-indigo); font-size: 14px; }
    
    /* Search Bar Styling */
    .history-filter-bar { 
        background: var(--brand-slate); border-radius: 24px; padding: 30px; 
        margin-bottom: 2.5rem; color: white; box-shadow: 0 15px 30px -10px rgba(15, 23, 42, 0.3);
    }
    .form-control-history { 
        border-radius: 12px; padding: 12px 15px; border: none; 
        background: rgba(255, 255, 255, 0.1); color: white; font-weight: 600; font-size: 13px;
    }
    .form-control-history:focus { background: rgba(255, 255, 255, 0.2); box-shadow: none; color: white; }
    .form-control-history option { color: #334155; }

    /* Timestamp Styling */
    .log-box { display: flex; flex-direction: column; line-height: 1.2; }
    .log-label { font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 2px; }
    .log-time { font-family: 'JetBrains Mono'; font-size: 11px; font-weight: 700; color: #475569; }

    .badge-success-premium { background: #ecfdf5; color: #065f46; padding: 8px 15px; border-radius: 10px; font-weight: 800; font-size: 10px; }
</style>

<div class="container-fluid mt-4 animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="font-weight-bold mb-1" style="letter-spacing: -1.5px; color: var(--brand-slate);">Archive Vault</h1>
            <p class="text-muted mb-0 font-weight-medium">Access finalized manifests and historical procurement audits.</p>
        </div>
        <a href="{{ route('rm.po_supplier_index') }}" class="btn btn-white rounded-pill px-4 border font-weight-bold shadow-sm">
            <i class="fas fa-arrow-left mr-2 text-muted"></i> Active Operations
        </a>
    </div>

    <div class="history-filter-bar animate__animated animate__fadeInDown">
        <form action="{{ route('rm.po_supplier_history') }}" method="GET" class="row align-items-end">
            <div class="col-md-3">
                <label class="x-small font-weight-bold mb-2 ml-1 text-uppercase opacity-50">Client Entity</label>
                <select name="customer" class="form-control-history w-100">
                    <option value="ALL">-- ALL ENTITIES --</option>
                    @foreach($clients as $c)
                        <option value="{{ trim($c->code) }}" {{ ($selectedCustomer == trim($c->code)) ? 'selected' : '' }}>
                            {{ strtoupper($c->name) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="x-small font-weight-bold mb-2 ml-1 text-uppercase opacity-50">Range Start</label>
                <input type="date" name="start_date" class="form-control-history w-100" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <label class="x-small font-weight-bold mb-2 ml-1 text-uppercase opacity-50">Range End</label>
                <input type="date" name="end_date" class="form-control-history w-100" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3 text-right">
                <button type="submit" class="btn btn-primary btn-block rounded-pill font-weight-extrabold shadow-lg" style="height: 48px; background: var(--brand-indigo); border:none;">
                    <i class="fas fa-search-archive mr-2"></i> EXECUTE SEARCH
                </button>
            </div>
        </form>
    </div>

    <div class="card-history shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="pl-4">MANIFEST_ID</th>
                        <th>SUPPLIER_ENTITY</th>
                        <th class="text-center">STATUS</th>
                        <th>LIFECYCLE_LOG</th>
                        <th class="text-right pr-4">COMMAND</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pos as $po)
                    <tr>
                        <td class="pl-4 py-4 expand-trigger" data-toggle="collapse" data-target="#archive-{{ $po->id }}">
                            <div class="id-code">{{ $po->no_po_supplier }}</div>
                            <small class="text-indigo font-weight-bold" style="font-size: 10px;">
                                <i class="fas fa-fingerprint mr-1"></i> CLICK FOR AUDIT
                            </small>
                        </td>
                        <td class="font-weight-bold expand-trigger" data-toggle="collapse" data-target="#archive-{{ $po->id }}">
                            {{ strtoupper($po->supplier_name) }}
                        </td>
                        <td class="text-center expand-trigger" data-toggle="collapse" data-target="#archive-{{ $po->id }}">
                            <span class="badge-success-premium">
                                <i class="fas fa-check-circle mr-1"></i> COMPLETED
                            </span>
                        </td>
                        <td class="expand-trigger" data-toggle="collapse" data-target="#archive-{{ $po->id }}">
                            <div class="d-flex gap-4">
                                <div class="log-box">
                                    <span class="log-label">Initialized:</span>
                                    <span class="log-time">{{ date('d/m/Y H:i', strtotime($po->created_at)) }}</span>
                                </div>
                                <div class="log-box ml-4">
                                    <span class="log-label">Archived:</span>
                                    <span class="log-time text-emerald-600">{{ date('d/m/Y H:i', strtotime($po->updated_at)) }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-right pr-4">
                            <button class="btn btn-white btn-sm rounded-xl border shadow-sm px-4 font-weight-bold" onclick="window.open('{{ route('rm.print_po', $po->id) }}', '_blank')">
                                <i class="fas fa-print mr-2 text-muted"></i> REPRINT
                            </button>
                        </td>
                    </tr>

                    <tr id="archive-{{ $po->id }}" class="collapse bg-light">
                        <td colspan="5" class="p-0">
                            <div class="p-4" style="background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%); border-top: 1px solid #f1f5f9;">
                                <h6 class="font-weight-bold mb-4 ml-2 text-indigo"><i class="fas fa-boxes mr-2"></i>Material Audit Specification</h6>
                                <div class="row">
                                    @foreach($po->items as $item)
                                    <div class="col-md-6 mb-3">
                                        <div class="bg-white border rounded-24 p-4 shadow-sm">
                                            <div class="d-flex justify-content-between mb-2">
                                                <h6 class="font-weight-extrabold text-dark mb-0">{{ $item->alias_real ?? $item->material_code }}</h6>
                                                <span class="badge badge-light border rounded-pill px-3 py-1 font-weight-bold" style="font-size: 10px;">FULL VERIFIED</span>
                                            </div>
                                            <div class="text-muted small font-weight-bold mb-3">
                                                {{ $item->spec_real }} | {{ $item->thickness }} X {{ $item->size }}
                                            </div>
                                            
                                            <div class="row text-center bg-light rounded-xl py-3 mx-0">
                                                <div class="col-6 border-right">
                                                    <div class="x-small text-muted font-weight-bold text-uppercase">Final Qty</div>
                                                    <div class="h6 font-weight-bold mb-0 text-success">{{ number_format($item->qty_received) }}</div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="x-small text-muted font-weight-bold text-uppercase">Quota</div>
                                                    <div class="h6 font-weight-bold mb-0">{{ number_format($item->qty_order) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty 
                    <tr><td colspan="5" class="text-center py-5"><div class="py-5"><i class="fas fa-folder-open fa-3x text-light mb-3"></i><p class="text-muted font-weight-bold">No historical data matches your search criteria.</p></div></td></tr> 
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection