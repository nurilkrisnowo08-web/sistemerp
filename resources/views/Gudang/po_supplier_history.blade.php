@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root { --brand-primary: #4f46e5; --ui-bg: #f8fafc; --brand-success: #10b981; }
    body { background-color: var(--ui-bg); font-family: 'Inter', sans-serif; color: #1e293b; }
    
    .card-history { border: none; border-radius: 16px; box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05); background: #fff; }
    .table thead th { background: #f1f5f9; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 11px; padding: 18px 15px; border: none; }
    
    .clickable-id { cursor: pointer; transition: all 0.2s ease; text-decoration: none !important; }
    .clickable-id:hover { opacity: 0.7; }
    
    .id-code { font-family: 'JetBrains Mono'; font-weight: 700; color: var(--brand-primary); font-size: 13px; }
    .history-filter-bar { background: #0f172a; border-radius: 16px; padding: 25px; margin-bottom: 2rem; color: white; }
    
    /* Timestamp Styling */
    .log-time-box { display: flex; flex-direction: column; gap: 2px; }
    .log-label { font-size: 8px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
    .log-value { font-size: 11px; font-weight: 700; color: #475569; font-family: 'JetBrains Mono'; }
    .log-value.success { color: var(--brand-success); }

    /* Detail Panel */
    .detail-panel-archive { background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; }
    .part-archive-badge { background: #fff; border: 1px solid #e2e8f0; color: #64748b; font-size: 10px; padding: 3px 8px; border-radius: 5px; font-weight: 700; display: inline-block; margin-top: 2px; }
</style>

<div class="container-fluid mt-4 animate__animated animate__fadeIn">
    {{-- History Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="font-weight-bold mb-1">Historical Procurement Registry</h4>
            <p class="text-muted small mb-0">Centralized Archive for Purchase Order Verification & Audit</p>
        </div>
        <a href="{{ route('rm.po_supplier_index') }}" class="btn btn-light border rounded-pill px-4 font-weight-bold shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Active Operations
        </a>
    </div>

    {{-- Search Engine --}}
    <div class="history-filter-bar shadow-lg">
        <form action="{{ route('rm.po_supplier_history') }}" method="GET" class="row align-items-end">
            <div class="col-md-3">
                <label class="small font-weight-bold mb-1">ENTITY_FILTER</label>
                <select name="customer" class="form-control border-0" style="border-radius: 8px; height: 40px; font-size: 12px;">
                    <option value="">-- ALL CLIENTS --</option>
                    @foreach($clients as $c)
                        <option value="{{ trim($c->code) }}" {{ (isset($customerFilter) && $customerFilter == trim($c->code)) ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold mb-1">DATE_START</label>
                <input type="date" name="start_date" class="form-control border-0" style="border-radius: 8px; height: 40px;" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold mb-1">DATE_END</label>
                <input type="date" name="end_date" class="form-control border-0" style="border-radius: 8px; height: 40px;" value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-block font-weight-bold" style="border-radius: 8px; height: 40px;">
                    <i class="fas fa-search-location mr-2"></i> EXECUTE_ARCHIVE_SEARCH
                </button>
            </div>
        </form>
    </div>

    <div class="card-history overflow-hidden shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="pl-4">Archive_ID (Click)</th>
                        <th>Supplier_Entity</th>
                        <th>Fulfillment_Status</th>
                        <th>Log_Timestamps</th>
                        <th class="text-right pr-4">Interface</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pos as $po)
                    <tr>
                        {{-- ✨ Klik ID buat buka detail rill --}}
                        <td class="pl-4 align-middle clickable-id" data-toggle="collapse" data-target="#archive-{{ $po->id }}">
                            <span class="id-code">{{ $po->no_po_supplier }}</span><br>
                            <small class="text-primary font-weight-bold" style="font-size: 9px;"><i class="fas fa-eye mr-1"></i> View Details</small>
                        </td>
                        <td class="font-weight-bold align-middle">{{ strtoupper($po->supplier_name) }}</td>
                        <td class="align-middle">
                            <span class="badge badge-pill {{ $po->status == 'COMPLETED' ? 'badge-success' : 'badge-light border' }} px-3 py-2 font-weight-bold">
                                {{ $po->status }}
                            </span>
                        </td>
                        <td class="align-middle">
                            <div class="log-time-box">
                                <span class="log-label">Created At:</span>
                                <span class="log-value">{{ date('d/m/Y H:i', strtotime($po->created_at)) }}</span>
                                
                                @if($po->status == 'COMPLETED')
                                    <span class="log-label mt-1">Completed At:</span>
                                    <span class="log-value success">{{ date('d/m/Y H:i', strtotime($po->updated_at)) }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-right pr-4 align-middle">
                            <button class="btn btn-light border btn-sm shadow-sm" onclick="window.open('{{ route('rm.print_po', $po->id) }}', '_blank')">
                                <i class="fas fa-print mr-1"></i> Reprint
                            </button>
                        </td>
                    </tr>

                    {{-- ✨ DETAIL BARIS HISTORY ✨ --}}
                    <tr id="archive-{{ $po->id }}" class="collapse bg-light">
                        <td colspan="5" class="p-4">
                            <div class="detail-panel-archive shadow-sm">
                                <h6 class="font-weight-bold mb-3 text-primary"><i class="fas fa-file-invoice mr-2"></i>Archive Material Specification</h6>
                                <div class="row">
                                    @foreach($po->items as $item)
                                    <div class="col-md-6 mb-2">
                                        <div class="bg-white p-3 border rounded">
                                            <div class="font-weight-bold text-dark mb-1">{{ $item->alias_real ?? $item->material_code }}</div>
                                            <div class="small text-muted font-weight-bold mb-2">{{ $item->spec_real }} | {{ $item->thickness }} X {{ $item->size }}</div>
                                            
                                            <div class="small font-weight-bold text-muted text-uppercase mb-1" style="font-size: 9px;">Assigned Components:</div>
                                            <div class="d-flex flex-wrap gap-1">
                                                @isset($item->target_parts)
                                                    @foreach($item->target_parts as $tp)
                                                        <span class="part-archive-badge">• {{ $tp->part_no }}</span>
                                                    @endforeach
                                                @endisset
                                            </div>
                                            
                                            <div class="mt-3 pt-2 border-top">
                                                <small class="font-weight-bold">Fulfillment: {{ number_format($item->qty_received) }} / {{ number_format($item->qty_order) }} PCS</small>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted font-weight-bold">Registry is empty. Define period to search historical data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endsection