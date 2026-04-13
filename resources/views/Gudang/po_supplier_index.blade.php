@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root { 
        --brand-indigo: #6366f1; 
        --brand-emerald: #10b981; 
        --brand-rose: #f43f5e;
        --brand-slate: #1e293b;
        --ui-bg: #f8fafc; 
    }

    body { background-color: var(--ui-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: #334155; }

    /* Custom Shake Animation */
    @keyframes alertShake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-6px); }
        75% { transform: translateX(6px); }
    }
    .input-error-shake { animation: alertShake 0.15s ease-in-out 0s 2; border: 2px solid var(--brand-rose) !important; box-shadow: 0 0 15px rgba(244, 63, 94, 0.2) !important; }

    .card-registry { 
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

    /* ✨ FIX: Hover hanya untuk kolom yang bisa di-expand */
    .expand-trigger { cursor: pointer; transition: all 0.2s; }
    .expand-trigger:hover { background-color: #f8fafc !important; }

    .badge-premium { padding: 8px 14px; border-radius: 12px; font-weight: 700; font-size: 10px; letter-spacing: 0.5px; display: inline-flex; align-items: center; }
    .bg-pending { background: #eff6ff; color: #1e40af; }
    .bg-partial { background: #fff7ed; color: #9a3412; }
    .bg-completed { background: #ecfdf5; color: #065f46; }

    .id-code { font-family: 'JetBrains Mono'; font-weight: 800; color: var(--brand-slate); font-size: 14px; }
    
    .form-control-premium { 
        border-radius: 16px; padding: 14px 20px; border: 2px solid #f1f5f9; 
        background: #f8fafc; font-size: 14px; transition: all 0.2s; font-weight: 600;
    }
    .form-control-premium:focus { background: #fff; border-color: var(--brand-indigo); box-shadow: 0 0 0 5px rgba(99, 102, 241, 0.1); outline: none; }

    .entity-nav { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 35px; }
    .entity-pill { 
        padding: 14px 28px; border-radius: 18px; background: #fff; border: 1px solid #e2e8f0;
        color: #64748b; font-weight: 700; font-size: 13px; transition: all 0.3s;
        text-decoration: none !important; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    }
    .entity-pill:hover { transform: translateY(-4px); border-color: var(--brand-indigo); color: var(--brand-indigo); box-shadow: 0 15px 25px -5px rgba(99, 102, 241, 0.15); }
    .entity-pill.active { background: var(--brand-slate); border-color: var(--brand-slate); color: #fff; box-shadow: 0 10px 15px -3px rgba(30, 41, 59, 0.25); }
</style>

<div class="container-fluid mt-4 animate__animated animate__fadeIn">
    <div class="row align-items-center mb-5">
        <div class="col-md-7">
            <h1 class="font-weight-bold mb-1" style="letter-spacing: -1.5px; color: var(--brand-slate);">Procurement Hub</h1>
            <p class="text-muted mb-0 font-weight-medium">Orchestrate your inbound material manifest and logistics supply chain.</p>
        </div>
        <div class="col-md-5 text-right">
            <a href="{{ route('rm.po_supplier_history') }}" class="btn btn-light rounded-pill px-4 mr-2 border font-weight-bold shadow-sm">
                <i class="fas fa-archive mr-2 text-muted"></i> View Archive
            </a>
            <button class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-lg" style="background: var(--brand-indigo); border:none; padding: 12px 30px;" data-toggle="modal" data-target="#modalCreatePO">
                <i class="fas fa-plus-circle mr-2"></i> New Procurement
            </button>
        </div>
    </div>

    <div class="entity-nav animate__animated animate__fadeInUp">
        <a href="{{ route('rm.po_supplier_index', ['customer' => 'ALL']) }}" 
           class="entity-pill {{ (!$selectedCustomer || $selectedCustomer == 'ALL') ? 'active' : '' }}">
            <i class="fas fa-layer-group mr-2"></i> GLOBAL ENTITIES
        </a>
        @foreach($clients as $c)
            <a href="{{ route('rm.po_supplier_index', ['customer' => trim($c->code)]) }}" 
               class="entity-pill {{ ($selectedCustomer == trim($c->code)) ? 'active' : '' }}">
                <i class="fas fa-building mr-2"></i> {{ strtoupper($c->name) }}
            </a>
        @endforeach
    </div>

    @if(session('success')) 
        <div class="alert alert-success border-0 shadow-sm rounded-24 py-3 mb-4 animate__animated animate__lightSpeedInRight">
            <div class="d-flex align-items-center"><i class="fas fa-check-circle fa-lg mr-3"></i><span class="font-weight-bold">{{ session('success') }}</span></div>
        </div> 
    @endif

    <div class="card-registry shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="pl-4">IDENTIFIER</th>
                        <th>SUPPLIER ENTITY</th>
                        <th>MATERIAL DETAILS</th>
                        <th class="text-center">LIFECYCLE</th>
                        <th class="text-right pr-4">OPERATIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pos as $po)
                    @php
                        $totalItems = $po->items->count();
                        $receivedItems = $po->items->filter(fn($it) => $it->qty_received >= $it->qty_order)->count();
                        $isFulfilled = ($totalItems > 0 && $totalItems == $receivedItems);
                        $st = ($isFulfilled || $po->status == 'COMPLETED') ? 'COMPLETED' : $po->status;
                    @endphp
                    <tr>
                        {{-- ✨ FIX: Pindahkan collapse trigger ke TD tertentu saja rill! --}}
                        <td class="pl-4 py-4 expand-trigger" data-toggle="collapse" data-target="#po-detail-{{ $po->id }}">
                            <div class="id-code text-indigo">{{ $po->no_po_supplier }}</div>
                            <span class="text-muted small font-weight-bold">{{ date('M d, Y', strtotime($po->created_at)) }}</span>
                        </td>
                        <td class="font-weight-bold expand-trigger" data-toggle="collapse" data-target="#po-detail-{{ $po->id }}">{{ strtoupper($po->supplier_name) }}</td>
                        <td class="expand-trigger" data-toggle="collapse" data-target="#po-detail-{{ $po->id }}">
                            <div class="d-flex flex-column">
                                @foreach($po->items->take(1) as $item)
                                    <span class="font-weight-bold text-dark" style="font-size: 13px;">• {{ $item->alias_real ?? $item->material_code }}</span>
                                @endforeach
                                @if($totalItems > 1) <span class="text-indigo font-weight-extrabold" style="font-size: 10px;">+ {{ $totalItems - 1 }} ADDITIONAL ITEMS</span> @endif
                            </div>
                        </td>
                        <td class="text-center expand-trigger" data-toggle="collapse" data-target="#po-detail-{{ $po->id }}">
                            <span class="badge-premium {{ strtolower($st) == 'completed' ? 'bg-completed' : (strtolower($st) == 'partial' ? 'bg-partial' : 'bg-pending') }}">
                                <i class="fas {{ strtolower($st) == 'completed' ? 'fa-check-double' : 'fa-spinner fa-spin' }} mr-2"></i> {{ $st }}
                            </span>
                        </td>
                        {{-- ✨ FIX: Kolom Operations jangan dikasih collapse rill! --}}
                        <td class="text-right pr-4">
                            <div class="btn-group">
                                <button type="button" class="btn btn-white btn-sm rounded-xl border shadow-sm px-3" onclick="window.open('{{ route('rm.print_po', $po->id) }}', '_blank')">
                                    <i class="fas fa-print text-muted"></i>
                                </button>
                                @if(!$isFulfilled)
                                    {{-- ✨ FIX: Tombol murni buat Modal rill! --}}
                                    <button type="button" 
                                            class="btn btn-dark btn-sm rounded-xl shadow-sm px-4 ml-2 font-weight-extrabold" 
                                            data-toggle="modal" 
                                            data-target="#modalArrival{{ $po->id }}">
                                        RECEIVE
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <tr id="po-detail-{{ $po->id }}" class="collapse bg-light">
                        <td colspan="5" class="p-0">
                            <div class="p-4" style="background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);">
                                <div class="row">
                                    @foreach($po->items as $item)
                                    @php $p = ($item->qty_order > 0) ? ($item->qty_received / $item->qty_order) * 100 : 0; @endphp
                                    <div class="col-md-6 mb-4">
                                        <div class="bg-white border rounded-24 p-4 shadow-sm">
                                            <div class="d-flex justify-content-between mb-3">
                                                <div>
                                                    <h6 class="font-weight-extrabold text-dark mb-1">{{ $item->alias_real ?? $item->material_code }}</h6>
                                                    <div class="text-muted small font-weight-bold">SPEC: {{ $item->spec_real }} | {{ $item->thickness }}X{{ $item->size }}</div>
                                                </div>
                                                <span class="badge {{ $p >= 100 ? 'badge-success' : 'badge-primary' }} rounded-pill px-3 py-2" style="font-size: 10px;">{{ round($p) }}% VERIFIED</span>
                                            </div>
                                            <div class="progress mb-4" style="height: 10px; border-radius: 10px; background: #f1f5f9; border: 1px solid #e2e8f0;"><div class="progress-bar {{ $p >= 100 ? 'bg-emerald-gradient' : 'bg-indigo-gradient' }}" style="width: {{ $p }}%; border-radius:10px;"></div></div>
                                            <div class="row text-center bg-light rounded-xl py-3 mx-0">
                                                <div class="col-6 border-right">
                                                    <div class="x-small text-muted font-weight-bold text-uppercase">Allocated</div>
                                                    <div class="h6 font-weight-bold mb-0">{{ number_format($item->qty_order) }}</div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="x-small text-muted font-weight-bold text-uppercase">Balance</div>
                                                    <div class="h6 font-weight-bold mb-0 text-indigo">{{ number_format($item->qty_order - $item->qty_received) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty <tr><td colspan="5" class="text-center py-5"><p class="text-muted font-weight-bold">No active manifests found.</p></td></tr> @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODALS TETEP LENGKAP RILL --}}
<div class="modal fade" id="modalCreatePO" tabindex="-1" style="z-index: 1055;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:32px;">
            <div class="modal-header bg-dark text-white p-4"><h6>Initialize Procurement</h6></div>
            <form action="{{ route('rm.po_supplier_store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-white">
                    <div class="row mb-4">
                        <div class="col-md-4"><label class="x-small font-weight-bold text-muted text-uppercase ml-2">Manifest ID</label><input type="text" name="po_no" class="form-control-premium w-100" required></div>
                        <div class="col-md-4"><label class="x-small font-weight-bold text-muted text-uppercase ml-2">Supplier Entity</label><input type="text" name="supplier_name" class="form-control-premium w-100" required></div>
                        <div class="col-md-4"><label class="x-small font-weight-bold text-muted text-uppercase ml-2">Client Assign</label>
                            <select id="client_filter" name="customer_code" class="form-control-premium w-100" required>
                                <option value="" disabled selected>Select Destination</option>
                                @foreach($clients as $c) <option value="{{ trim($c->code) }}">{{ $c->name }}</option> @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="po-items-container"><div class="item-row-box p-4 border rounded-24 bg-light mb-3"><div class="row align-items-end"><div class="col-md-8"><label class="x-small font-weight-bold text-muted text-uppercase">Material Alias</label><select name="items[0][spec]" class="form-control-premium w-100 spec-dropdown" required><option value="">Select Client First</option></select></div><div class="col-md-4"><label class="x-small font-weight-bold text-muted text-uppercase">Quantity</label><input type="number" name="items[0][qty]" class="form-control-premium w-100" required></div></div></div></div>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill font-weight-bold px-4" onclick="addPoItemRow()">+ Append Material</button>
                </div>
                <div class="modal-footer bg-light border-0 p-4"><button type="submit" class="btn btn-primary btn-block py-3 font-weight-extrabold rounded-pill shadow-lg">INITIALIZE MANIFEST</button></div>
            </form>
        </div>
    </div>
</div>

@foreach($pos as $po)
<div class="modal fade" id="modalArrival{{ $po->id }}" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius:32px; overflow:hidden;">
            <div class="modal-header bg-success text-white p-4"><h5 class="modal-title font-weight-bold"><i class="fas fa-shield-check mr-2"></i> Inbound Verification</h5></div>
            <form action="{{ route('rm.po_arrival_store', $po->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-white">
                    <div class="form-group mb-4"><label class="small font-weight-bold text-muted text-uppercase ml-2">Target Material</label>
                        <select name="item_id" class="form-control-premium w-100 select-item-arrival" required>
                            <option value="">-- Choose Material --</option>
                            @foreach($po->items as $it) 
                                @php $sisa = $it->qty_order - $it->qty_received; @endphp
                                @if($sisa > 0) <option value="{{ $it->id }}" data-sisa="{{ $sisa }}">{{ $it->alias_real ?? $it->material_code }} (BAL: {{ $sisa }})</option> @endif 
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-4"><label class="small font-weight-bold text-muted text-uppercase ml-2">Coil Identifier</label><input type="text" name="coil_id" class="form-control-premium w-100" required></div>
                    <div class="form-group mb-0"><label class="small font-weight-bold text-muted text-uppercase ml-2">Received Quantity</label>
                        <input type="number" name="qty_arrival" class="form-control-premium w-100 input-qty-secure text-success h4 mb-0" required min="1">
                        <div class="qty-warning mt-3 d-none animate__animated animate__headShake">
                            <span class="badge badge-danger rounded-pill px-3 py-2 font-weight-bold shadow-sm"><i class="fas fa-exclamation-triangle mr-1"></i> EXCEEDS REMAINING BALANCE!</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-4"><button type="submit" class="btn btn-success btn-block py-3 font-weight-extrabold rounded-pill btn-confirm-receive shadow-lg">CONFIRM RECEIPT</button></div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const masterMaterials = @json($masterMaterials);
    function populateSpecs(targetSelect, clientCode) {
        const filtered = masterMaterials.filter(m => m.customer_code.trim() === clientCode.trim());
        let options = '<option value="">-- Select Material --</option>';
        filtered.forEach(m => { options += `<option value="${m.alias_code}">${m.alias_code} (${m.material_type})</option>`; });
        targetSelect.html(options);
    }
    $(document).on('change', '#client_filter', function() {
        const clientCode = $(this).val(); 
        if(clientCode) { $('.spec-dropdown').each(function() { populateSpecs($(this), clientCode); }); }
    });
    let idx = 1;
    function addPoItemRow() {
        const clientCode = $('#client_filter').val(); 
        let options = '<option value="">Select Client First</option>';
        if(clientCode) {
            const filtered = masterMaterials.filter(m => m.customer_code.trim() === clientCode.trim());
            options = '<option value="">-- Select Material --</option>';
            filtered.forEach(m => { options += `<option value="${m.alias_code}">${m.alias_code} (${m.material_type})</option>`; });
        }
        const html = `<div class="item-row-box p-4 border rounded-24 bg-light mb-3 animate__animated animate__fadeInUp"><div class="row align-items-end"><div class="col-md-8"><label class="x-small font-weight-bold text-muted text-uppercase">Material Alias</label><select name="items[${idx}][spec]" class="form-control-premium w-100 spec-dropdown" required>${options}</select></div><div class="col-md-3"><label class="x-small font-weight-bold text-muted text-uppercase">Quantity</label><input type="number" name="items[${idx}][qty]" class="form-control-premium w-100" required></div><div class="col-md-1"><button type="button" class="btn btn-link text-danger mb-2" onclick="$(this).closest('.item-row-box').remove()"><i class="fas fa-trash fa-lg"></i></button></div></div></div>`;
        $('#po-items-container').append(html); idx++;
    }

    $(document).on('input', '.input-qty-secure', function() {
        const form = $(this).closest('form');
        const selectedOpt = form.find('.select-item-arrival option:selected');
        const max = parseInt(selectedOpt.data('sisa')) || 0;
        const val = parseInt($(this).val());
        const warning = form.find('.qty-warning');
        const btn = form.find('.btn-confirm-receive');
        if(val > max) {
            $(this).addClass('input-error-shake').addClass('text-danger');
            warning.removeClass('d-none');
            btn.prop('disabled', true).css('opacity', '0.3').text('OVER LIMIT');
        } else {
            $(this).removeClass('input-error-shake').removeClass('text-danger');
            warning.addClass('d-none');
            btn.prop('disabled', false).css('opacity', '1').text('CONFIRM RECEIPT');
        }
    });
</script>
@endsection