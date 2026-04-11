@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root { --brand-primary: #4f46e5; --brand-success: #10b981; --ui-bg: #f8fafc; }
    body { background-color: var(--ui-bg); font-family: 'Inter', sans-serif; color: #1e293b; }
    
    .card-registry { border: none; border-radius: 16px; box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05); background: #fff; margin-bottom: 1.5rem; }
    .table thead th { background: #f1f5f9; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; padding: 18px 15px; border: none; }
    
    .clickable-col { cursor: pointer; transition: all 0.2s ease; }
    .clickable-col:hover { background-color: #f1f5f9 !important; }
    
    .progress-track { height: 8px; border-radius: 12px; background: #e2e8f0; overflow: hidden; margin-top: 6px; }
    .progress-fill { height: 100%; transition: width 0.8s ease; }
    .bg-fulfillment { background: var(--brand-primary); }
    .bg-complete { background: var(--brand-success); }

    .part-mapping-badge { background: #fff; border: 1px solid #e2e8f0; color: #475569; font-size: 10px; padding: 4px 10px; border-radius: 6px; font-weight: 700; display: block; margin-top: 4px; }
    .detail-panel { background: #f8fafc; border-radius: 14px; padding: 25px; border: 1px solid #e2e8f0; }
    .id-code { font-family: 'JetBrains Mono'; font-weight: 700; color: var(--brand-primary); }

    /* Entity Navigation Pills */
    .entity-nav { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 25px; }
    .entity-pill { 
        padding: 10px 20px; border-radius: 12px; background: #fff; border: 1.5px solid #e2e8f0;
        color: #64748b; font-weight: 700; font-size: 12px; transition: all 0.2s; text-decoration: none !important;
    }
    .entity-pill:hover { border-color: var(--brand-primary); color: var(--brand-primary); }
    .entity-pill.active { background: var(--brand-primary); border-color: var(--brand-primary); color: #fff; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }
</style>

<div class="container-fluid mt-4 animate__animated animate__fadeIn">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="font-weight-bold mb-1">Procurement Operations Hub</h4>
            <p class="text-muted small mb-0">Active Purchase Orders & Inbound Logistics Registry</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('rm.po_supplier_history') }}" class="btn btn-dark rounded-pill px-4 font-weight-bold shadow-sm mr-2">
                <i class="fas fa-history mr-2"></i> Historical Registry
            </a>
            <button class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalCreatePO">
                <i class="fas fa-plus-circle mr-2"></i> New Procurement
            </button>
        </div>
    </div>

    {{-- ✨ ENTITY NAVIGATION PILLS (GANTI DROPDOWN) ✨ --}}
    <div class="entity-nav animate__animated animate__fadeInUp">
        <a href="{{ route('rm.po_supplier_index', ['customer' => 'ALL']) }}" 
           class="entity-pill {{ (!$selectedCustomer || $selectedCustomer == 'ALL') ? 'active' : '' }}">
            <i class="fas fa-layer-group mr-2"></i> ALL CLIENT ENTITIES
        </a>
        @foreach($clients as $c)
            <a href="{{ route('rm.po_supplier_index', ['customer' => trim($c->code)]) }}" 
               class="entity-pill {{ ($selectedCustomer == trim($c->code)) ? 'active' : '' }}">
                <i class="fas fa-building mr-2"></i> {{ strtoupper($c->name) }}
            </a>
        @endforeach
    </div>

    @if(session('success')) <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}</div> @endif

    <div class="card-registry overflow-hidden shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="pl-4">PO_Identifier</th>
                        <th>Supplier_Entity</th>
                        <th>Material_Specification</th>
                        <th class="text-center">Fulfillment_Status</th>
                        <th class="text-right pr-4">Operational_Interface</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pos as $po)
                    @php
                        $totalItems = $po->items->count();
                        $receivedItems = $po->items->filter(fn($it) => $it->qty_received >= $it->qty_order)->count();
                        $isFulfilled = ($totalItems > 0 && $totalItems == $receivedItems);
                    @endphp
                    <tr>
                        <td class="pl-4 align-middle clickable-col" data-toggle="collapse" data-target="#po-detail-{{ $po->id }}">
                            <span class="id-code">{{ $po->no_po_supplier }}</span><br>
                            <small class="text-muted font-weight-bold">{{ date('d M Y', strtotime($po->created_at)) }}</small>
                        </td>
                        <td class="font-weight-bold align-middle clickable-col" data-toggle="collapse" data-target="#po-detail-{{ $po->id }}">
                            {{ strtoupper($po->supplier_name) }}
                        </td>
                        <td class="align-middle clickable-col" data-toggle="collapse" data-target="#po-detail-{{ $po->id }}">
                            @foreach($po->items as $item)
                                <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ $item->alias_real ?? $item->material_code }}</div>
                                <div class="text-muted" style="font-size: 10px; font-weight: 600;">SPEC: {{ $item->spec_real }}</div>
                            @endforeach
                            <div class="mt-2"><small class="text-primary font-weight-bold"><i class="fas fa-search-plus mr-1"></i> Expand component details</small></div>
                        </td>
                        <td class="text-center align-middle clickable-col" data-toggle="collapse" data-target="#po-detail-{{ $po->id }}">
                            @if($isFulfilled || $po->status == 'COMPLETED')
                                <span class="badge badge-pill badge-success px-3 py-2 font-weight-bold shadow-sm">COMPLETED</span>
                            @else
                                <span class="badge badge-pill badge-light border px-3 py-2 font-weight-bold text-muted">{{ $po->status }}</span>
                            @endif
                        </td>
                        <td class="text-right pr-4 align-middle">
                            <div class="btn-group">
                                <button class="btn btn-light border btn-sm shadow-sm" onclick="window.open('{{ route('rm.print_po', $po->id) }}', '_blank')"><i class="fas fa-print"></i></button>
                                @if(!$isFulfilled)
                                    <button class="btn btn-success btn-sm font-weight-bold px-3 shadow-sm ml-1" data-toggle="modal" data-target="#modalArrival{{ $po->id }}">Receive</button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <tr id="po-detail-{{ $po->id }}" class="collapse">
                        <td colspan="5" class="p-4 bg-light">
                            <div class="detail-panel shadow-sm">
                                <div class="row">
                                    @foreach($po->items as $item)
                                    <div class="col-md-6 mb-3">
                                        <div class="p-3 bg-white border rounded shadow-sm">
                                            <div class="d-flex justify-content-between mb-2">
                                                <div class="font-weight-bold text-dark">{{ $item->alias_real ?? $item->material_code }}</div>
                                                @php $p = ($item->qty_order > 0) ? ($item->qty_received / $item->qty_order) * 100 : 0; @endphp
                                                <span class="badge {{ $p >= 100 ? 'badge-success' : 'badge-primary' }} font-weight-bold">{{ round($p) }}%</span>
                                            </div>
                                            <div class="small text-muted font-weight-bold mb-3">{{ $item->spec_real }} | {{ $item->thickness }} X {{ $item->size }}</div>
                                            <label class="small font-weight-bold text-muted text-uppercase">Assigned Target Components:</label>
                                            <div class="d-flex flex-wrap gap-1 mb-3">
                                                @isset($item->target_parts)
                                                    @foreach($item->target_parts as $tp)
                                                        <div class="part-mapping-badge">• {{ $tp->part_no }} <span>({{ $tp->part_name }})</span></div>
                                                    @endforeach
                                                @endisset
                                            </div>
                                            <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                                <span class="text-muted">Verification Status:</span>
                                                <span>{{ number_format($item->qty_received) }} / {{ number_format($item->qty_order) }} PCS</span>
                                            </div>
                                            <div class="progress-track"><div class="progress-fill {{ $p >= 100 ? 'bg-complete' : 'bg-fulfillment' }}" style="width: {{ $p }}%"></div></div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty <tr><td colspan="5" class="text-center py-5 text-muted small font-weight-bold">No active procurement records found for the selected entity.</td></tr> @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODALS TETEP LENGKAP RILL --}}
<div class="modal fade" id="modalCreatePO" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content" style="border-radius:15px;"><div class="modal-header bg-dark text-white border-0 py-3"><h6>Initialize New Purchase Order</h6></div><form action="{{ route('rm.po_supplier_store') }}" method="POST">@csrf<div class="modal-body p-4"><div class="row mb-3"><div class="col-md-4"><label class="small font-weight-bold text-muted">DOCUMENT NO</label><input type="text" name="po_no" class="form-control" required placeholder="PO-XXXX"></div><div class="col-md-4"><label class="small font-weight-bold text-muted">SUPPLIER ENTITY</label><input type="text" name="supplier_name" class="form-control" required></div><div class="col-md-4"><label class="small font-weight-bold text-muted">CLIENT ASSIGNMENT</label><select id="client_filter" class="form-control">@foreach($clients as $c) <option value="{{ $c->code }}">{{ $c->name }}</option> @endforeach</select></div></div><div id="po-items-container"><div class="item-row-box shadow-sm border rounded p-3 mb-2 bg-light"><div class="row align-items-end"><div class="col-md-8"><label class="small font-weight-bold text-muted">MATERIAL ALIAS</label><select name="items[0][spec]" class="form-control spec-dropdown" required disabled><option>Select Client First</option></select></div><div class="col-md-4"><label class="small font-weight-bold text-muted">ORDER QUANTITY</label><input type="number" name="items[0][qty]" class="form-control" required placeholder="0"></div></div></div></div><button type="button" class="btn btn-outline-primary btn-sm mt-2 font-weight-bold" onclick="addPoItemRow()"><i class="fas fa-plus mr-1"></i> Add Material Line Item</button></div><div class="modal-footer border-0 p-4 pt-0"><button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold rounded-pill shadow">Confirm & Process Purchase Order</button></div></form></div></div></div>

@foreach($pos as $po)
<div class="modal fade" id="modalArrival{{ $po->id }}" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius:15px;"><div class="modal-header bg-success text-white border-0 py-3"><h6>Material Verification</h6></div><form action="{{ route('rm.po_arrival_store', $po->id) }}" method="POST">@csrf<div class="modal-body p-4"><div class="form-group mb-3"><label class="small font-weight-bold text-muted">SELECTED MATERIAL</label><select name="item_id" class="form-control" required><option value="">-- Choose --</option>@foreach($po->items as $it) @if($it->qty_received < $it->qty_order) <option value="{{ $it->id }}">{{ $it->alias_real ?? $it->material_code }}</option> @endif @endforeach</select></div><div class="form-group mb-3"><label class="small font-weight-bold text-muted">COIL_ID / BATCH_IDENTIFIER</label><input type="text" name="coil_id" class="form-control" required></div><div class="form-group mb-0"><label class="small font-weight-bold text-muted">VERIFIED QUANTITY RECEIVED</label><input type="number" name="qty_arrival" class="form-control" required></div></div><div class="modal-footer border-0 p-4 pt-0"><button type="submit" class="btn btn-success btn-block py-3 font-weight-bold rounded-pill text-white">Confirm Receipt</button></div></form></div></div></div>
@endforeach

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const masterMaterials = @json($masterMaterials);
    $(document).on('change', '#client_filter', function() {
        const sel = $(this).val(); const drop = $('.spec-dropdown');
        if(sel) {
            const filt = masterMaterials.filter(m => m.customer_code.trim() === sel.trim());
            let opt = '<option value="">-- Select Specification --</option>';
            filt.forEach(m => { opt += `<option value="${m.alias_code}">${m.alias_code} (${m.material_type})</option>`; });
            drop.html(opt).prop('disabled', false);
        }
    });
    let idx = 1;
    function addPoItemRow() {
        const sel = $('#client_filter').val(); 
        let opt = '<option>Select Client First</option>';
        if(sel) {
            const filt = masterMaterials.filter(m => m.customer_code.trim() === sel.trim());
            opt = '<option value="">-- Select Specification --</option>';
            filt.forEach(m => { opt += `<option value="${m.alias_code}">${m.alias_code} (${m.material_type})</option>`; });
        }
        $('#po-items-container').append(`<div class="item-row-box mt-3 p-3 border rounded bg-light animate__animated animate__fadeIn"><div class="row align-items-end"><div class="col-md-8"><label class="small font-weight-bold text-muted">MATERIAL ALIAS</label><select name="items[${idx}][spec]" class="form-control spec-dropdown" required>${opt}</select></div><div class="col-md-3"><label class="small font-weight-bold text-muted">ORDER QTY</label><input type="number" name="items[${idx}][qty]" class="form-control" required></div><div class="col-md-1"><button type="button" class="btn btn-link text-danger p-0 mb-1" onclick="$(this).closest('.item-row-box').remove()"><i class="fas fa-trash-alt fa-lg"></i></button></div></div></div>`); idx++;
    }
</script>
@endsection