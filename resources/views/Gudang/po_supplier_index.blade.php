@extends('layout.admin')

@section('content')
@php
    // Amankan data: jika Controller tidak mengirim $masterMaterials, ambil langsung dari tabel master_materials
    if (!isset($masterMaterials) || empty($masterMaterials) || (is_countable($masterMaterials) && count($masterMaterials) === 0)) {$masterMaterials = \Illuminate\Support\Facades\DB::table('master_materials')->get();
    }
@endphp

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
           class="entity-pill {{ (!$selectedCustomer ||$selectedCustomer == 'ALL') ? 'active' : '' }}">
            <i class="fas fa-layer-group mr-2"></i> GLOBAL ENTITIES
        </a>
        @foreach($clients as$c)
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

    @if(session('error')) 
        <div class="alert alert-danger border-0 shadow-sm rounded-24 py-3 mb-4 animate__animated animate__shakeX">
            <div class="d-flex align-items-center"><i class="fas fa-exclamation-triangle fa-lg mr-3"></i><span class="font-weight-bold">{{ session('error') }}</span></div>
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
                    @forelse($pos as$po)
                    @php
                        $totalItems = $po->items->count();$receivedItems = $po->items->filter(fn($it) => $it->qty_received >=$it->qty_order)->count();
                        $isFulfilled = ($totalItems > 0 && $totalItems ==$receivedItems);
                        $st = ($isFulfilled || $po->status == 'COMPLETED') ? 'COMPLETED' :$po->status;
                    @endphp
                    <tr>
                        <td class="pl-4 py-4 expand-trigger" data-toggle="collapse" data-target="#po-detail-{{ $po->id }}">
                            <div class="id-code text-indigo">{{ $po->no_po_supplier }}</div>
                            <span class="text-muted small font-weight-bold">{{ date('M d, Y', strtotime($po->created_at)) }}</span>
                        </td>
                        <td class="font-weight-bold expand-trigger" data-toggle="collapse" data-target="#po-detail-{{ $po->id }}">{{ strtoupper($po->supplier_name) }}</td>
                        <td class="expand-trigger" data-toggle="collapse" data-target="#po-detail-{{ $po->id }}">
                            <div class="d-flex flex-column">
                                @foreach($po->items->take(1) as$item)
                                    <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $item->alias_real ?? $item->material_code }}</span>
                                @endforeach
                                @if($totalItems > 1) <span class="text-indigo font-weight-extrabold" style="font-size: 10px;">+ {{ $totalItems - 1 }} ADDITIONAL ITEMS</span> @endif
                            </div>
                        </td>
                        <td class="text-center expand-trigger" data-toggle="collapse" data-target="#po-detail-{{ $po->id }}">
                            <span class="badge-premium {{ strtolower($st) == 'completed' ? 'bg-completed' : (strtolower($st) == 'partial' ? 'bg-partial' : 'bg-pending') }}">
                                <i class="fas {{ strtolower($st) == 'completed' ? 'fa-check-double' : 'fa-spinner fa-spin' }} mr-2"></i> {{ $st }}
                            </span>
                        </td>
                        <td class="text-right pr-4">
                            <div class="btn-group">
                                <button type="button" class="btn btn-white btn-sm rounded-xl border shadow-sm px-3" onclick="window.open('{{ route('rm.print_po', $po->id) }}', '_blank')">
                                    <i class="fas fa-print text-muted"></i>
                                </button>
                                @if(!$isFulfilled)
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
                                    @foreach($po->items as$item)
                                    @php $p = ($item->qty_order > 0) ? ($item->qty_received / $item->qty_order) * 100 : 0; @endphp
                                    <div class="col-md-6 mb-4">
                                        <div class="bg-white border rounded-24 p-4 shadow-sm">
                                            <div class="d-flex justify-content-between mb-3">
                                                <div>
                                                    <h6 class="font-weight-extrabold text-dark mb-1">{{ $item->alias_real ?? $item->material_code }}</h6>
                                                    <div class="text-muted small font-weight-bold">SPEC: {{ $item->spec_real }} | {{ $item->thickness }}X{{$item->size }}</div>
                                                </div>
                                                <span class="badge {{ $p >= 100 ? 'badge-success' : 'badge-primary' }} rounded-pill px-3 py-2" style="font-size: 10px;">{{ round($p) }}% VERIFIED</span>
                                            </div>
                                            <div class="progress mb-4" style="height: 10px; border-radius: 10px; background: #f1f5f9; border: 1px solid #e2e8f0;">
                                                <div class="progress-bar {{ $p >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ min($p, 100) }}%; border-radius:10px;"></div>
                                            </div>
                                            <div class="row text-center bg-light rounded-xl py-3 mx-0">
                                                <div class="col-6 border-right">
                                                    <div class="x-small text-muted font-weight-bold text-uppercase">Allocated</div>
                                                    <div class="h6 font-weight-bold mb-0">{{ number_format($item->qty_order) }}</div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="x-small text-muted font-weight-bold text-uppercase">Balance</div>
                                                    <div class="h6 font-weight-bold mb-0 text-indigo">{{ number_format($item->qty_order -$item->qty_received) }}</div>
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

{{-- MODAL CREATE PO --}}
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
                                @foreach($clients as$c) <option value="{{ trim($c->code) }}">{{ $c->name }}</option> @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="po-items-container">
                        <div class="item-row-box p-4 border rounded-24 bg-light mb-3">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <label class="x-small font-weight-bold text-muted text-uppercase">Material Alias</label>
                                    <select name="items[0][spec]" class="form-control-premium w-100 spec-dropdown" required><option value="">Select Client First</option></select>
                                </div>
                                <div class="col-md-4">
                                    <label class="x-small font-weight-bold text-muted text-uppercase">Quantity</label>
                                    <input type="number" name="items[0][qty]" class="form-control-premium w-100" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill font-weight-bold px-4" onclick="addPoItemRow()">+ Append Material</button>
                </div>
                <div class="modal-footer bg-light border-0 p-4"><button type="submit" class="btn btn-primary btn-block py-3 font-weight-extrabold rounded-pill shadow-lg">INITIALIZE MANIFEST</button></div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL RECEIVE PO (DENGAN INPUT NO SURAT JALAN & MULTI-BUNDLE BUILDER) --}}
@foreach($pos as$po)
<div class="modal fade" id="modalArrival{{ $po->id }}" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius:32px; overflow:hidden;">
            <div class="modal-header bg-dark text-white p-4 d-flex justify-content-between align-items-center">
                <h5 class="modal-title font-weight-bold m-0"><i class="fas fa-truck-loading mr-2 text-primary"></i> RECEIVE MATERIAL MANIFEST</h5>
                <span class="badge badge-light px-3 py-2 rounded-pill font-weight-bold">PO: {{ $po->no_po_supplier }}</span>
            </div>
            
            <form action="{{ route('rm.po_arrival_store', $po->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-white">
                    {{-- 📄 Input No Surat Jalan Supplier --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="small font-weight-bold text-muted text-uppercase ml-2">NOMOR SURAT JALAN (SUPPLIER) <span class="text-danger">*</span></label>
                            <input type="text" name="no_surat_jalan" class="form-control-premium w-100 font-weight-bold text-uppercase" placeholder="Contoh: SJ-SAI-2026/10/001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small font-weight-bold text-muted text-uppercase ml-2">SUPPLIER ENTITY</label>
                            <input type="text" class="form-control-premium w-100 bg-light text-muted" value="{{ strtoupper($po->supplier_name) }} ({{$po->customer_code }})" disabled>
                        </div>
                    </div>

                    {{-- 📦 Tabel Multi-Bundle Builder --}}
                    <div class="card border rounded-24 overflow-hidden shadow-none mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3 px-4 border-0">
                            <div>
                                <h6 class="font-weight-extrabold text-dark mb-0"><i class="fas fa-boxes mr-2 text-primary"></i> BUNDLE & COIL RECEIPT DETAILS</h6>
                                <small class="text-muted">Masukkan semua bundle fisik yang tertera pada lembar Surat Jalan.</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 font-weight-bold shadow-sm btn-add-bundle" data-po-id="{{ $po->id }}">
                                <i class="fas fa-plus mr-1"></i> TAMBAH BUNDLE
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0 text-center align-middle">
                                <thead class="bg-white">
                                    <tr class="small text-muted font-weight-bold">
                                        <th width="35%">TARGET MATERIAL (PO ITEM)</th>
                                        <th width="35%">COIL / BUNDLE IDENTIFIER</th>
                                        <th width="20%">RECEIVED QTY (PCS)</th>
                                        <th width="10%">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody class="bundle-tbody">
                                    <tr class="bundle-row">
                                        <td>
                                            <select name="bundles[0][item_id]" class="form-control-premium py-2 px-3 select-bundle-item" style="font-size:13px;" required>
                                                <option value="">-- Pilih Material --</option>
                                                @foreach($po->items as$it) 
                                                    @php $sisa = $it->qty_order -$it->qty_received; @endphp
                                                    @if($sisa > 0) 
                                                        <option value="{{ $it->id }}" data-sisa="{{ $sisa }}">
                                                            {{ $it->alias_real ?? $it->material_code }} (BAL: {{ number_format($sisa) }})
                                                        </option> 
                                                    @endif 
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="bundles[0][coil_id]" class="form-control-premium py-2 px-3 text-uppercase font-weight-bold text-dark" style="font-size:13px;" placeholder="Contoh: TP265C006101" required>
                                        </td>
                                        <td>
                                            <input type="number" name="bundles[0][qty_arrival]" class="form-control-premium py-2 px-3 text-center font-weight-bold text-success bundle-qty" style="font-size:14px;" placeholder="0" min="1" required>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-link text-danger p-0" onclick="removeBundleRow(this)" title="Hapus Baris">
                                                <i class="fas fa-trash fa-lg"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ⚠️ Peringatan Over-Limit --}}
                    <div class="qty-warning mt-2 d-none animate__animated animate__headShake">
                        <span class="badge badge-danger rounded-pill px-4 py-2 font-weight-bold shadow-sm" style="font-size:12px;">
                            <i class="fas fa-exclamation-triangle mr-2"></i> TOTAL QUANTITY MEMENUHI ATAU MELEBIHI SALDO SISA PO!
                        </span>
                    </div>
                </div>

                <div class="modal-footer bg-light p-4 border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 font-weight-bold border" data-dismiss="modal">BATAL</button>
                    <button type="submit" class="btn btn-success px-5 py-3 font-weight-extrabold rounded-pill btn-confirm-receive shadow-lg">
                        <i class="fas fa-check-circle mr-2"></i> CONFIRM INBOUND MANIFEST
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const masterMaterials = @json($masterMaterials);

    function filterMaterialsByClient(clientCode) {
        if (!clientCode) return [];
        const cleanClient = String(clientCode).trim().toUpperCase();
        
        return masterMaterials.filter(m => {
            if (!m.customer_code) return false;
            const itemCode = String(m.customer_code).trim().toUpperCase();
            
            return itemCode === cleanClient || 
                   itemCode.startsWith(cleanClient) || 
                   cleanClient.startsWith(itemCode) ||
                   itemCode.includes(cleanClient) ||
                   cleanClient.includes(itemCode);
        });
    }

    function buildOptionsHtml(materials) {
        if (!materials || materials.length === 0) {
            return '<option value="">-- No Material Available --</option>';
        }
        let options = '<option value="">-- Select Material --</option>';
        materials.forEach(m => { 
            const alias = m.alias_code || m.material_type;
            const type = m.material_type ? ` (${m.material_type})` : '';
            options += `<option value="${alias}">${alias}${type}</option>`; 
        });
        return options;
    }

    function populateSpecs(targetSelect, clientCode) {
        const filtered = filterMaterialsByClient(clientCode);
        targetSelect.html(buildOptionsHtml(filtered));
    }

    $(document).on('change', '#client_filter', function() {
        const clientCode = $(this).val(); 
        if(clientCode) { 
            $('.spec-dropdown').each(function() { 
                populateSpecs($(this), clientCode); 
            }); 
        }
    });

    let idx = 1;
    function addPoItemRow() {
        const clientCode = $('#client_filter').val(); 
        let options = '<option value="">Select Client First</option>';
        if(clientCode) {
            const filtered = filterMaterialsByClient(clientCode);
            options = buildOptionsHtml(filtered);
        }
        const html = `<div class="item-row-box p-4 border rounded-24 bg-light mb-3 animate__animated animate__fadeInUp"><div class="row align-items-end"><div class="col-md-8"><label class="x-small font-weight-bold text-muted text-uppercase">Material Alias</label><select name="items[${idx}][spec]" class="form-control-premium w-100 spec-dropdown" required>${options}</select></div><div class="col-md-3"><label class="x-small font-weight-bold text-muted text-uppercase">Quantity</label><input type="number" name="items[${idx}][qty]" class="form-control-premium w-100" required></div><div class="col-md-1"><button type="button" class="btn btn-link text-danger mb-2" onclick="$(this).closest('.item-row-box').remove()"><i class="fas fa-trash fa-lg"></i></button></div></div></div>`;
        $('#po-items-container').append(html); 
        idx++;
    }

    // ✨ TAMBAH BARIS BUNDLE PADA MODAL RECEIVE SECARA DINAMIS ✨
    $(document).on('click', '.btn-add-bundle', function() {
        const modal = $(this).closest('.modal');
        const tbody = modal.find('.bundle-tbody');
        const firstRow = tbody.find('tr').first();
        const selectOptions = firstRow.find('select').html();
        const newIdx = tbody.find('tr').length;
        
        const newRow = `
            <tr class="bundle-row animate__animated animate__fadeIn">
                <td>
                    <select name="bundles[${newIdx}][item_id]" class="form-control-premium py-2 px-3 select-bundle-item" style="font-size:13px;" required>
                        ${selectOptions}
                    </select>
                </td>
                <td>
                    <input type="text" name="bundles[${newIdx}][coil_id]" class="form-control-premium py-2 px-3 text-uppercase font-weight-bold text-dark" style="font-size:13px;" placeholder="Contoh: TP265C00610${newIdx + 1}" required>
                </td>
                <td>
                    <input type="number" name="bundles[${newIdx}][qty_arrival]" class="form-control-premium py-2 px-3 text-center font-weight-bold text-success bundle-qty" style="font-size:14px;" placeholder="0" min="1" required>
                </td>
                <td>
                    <button type="button" class="btn btn-link text-danger p-0" onclick="removeBundleRow(this)" title="Hapus Baris">
                        <i class="fas fa-trash fa-lg"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(newRow);
    });

    function removeBundleRow(btn) {
        const tbody = $(btn).closest('.bundle-tbody');
        if(tbody.find('tr').length > 1) {
            $(btn).closest('tr').remove();
            checkModalLimits(tbody.closest('form'));
        } else {
            alert('Minimal harus ada 1 bundle yang diterima!');
        }
    }

    // ✨ CEK LIMIT REAL-TIME (AGGREGASI MULTI-BUNDLE DENGAN BALANCE PO) ✨
    $(document).on('input change', '.bundle-qty, .select-bundle-item', function() {
        const form = $(this).closest('form');
        checkModalLimits(form);
    });

    function checkModalLimits(form) {
        const warning = form.find('.qty-warning');
        const btn = form.find('.btn-confirm-receive');
        
        let isOver = false;
        const itemTotals = {};
        const itemMax = {};
        
        form.find('.bundle-row').each(function() {
            const select = $(this).find('.select-bundle-item option:selected');
            const itemId = select.val();
            const max = parseInt(select.data('sisa')) || 0;
            const qty = parseInt($(this).find('.bundle-qty').val()) || 0;
            
            if (itemId) {
                itemTotals[itemId] = (itemTotals[itemId] || 0) + qty;
                itemMax[itemId] = max;
            }
        });
        
        for (let id in itemTotals) {
            if (itemTotals[id] > itemMax[id]) {
                isOver = true;
                break;
            }
        }
        
        if (isOver) {
            warning.removeClass('d-none');
            btn.prop('disabled', true).css('opacity', '0.4').text('OVER LIMIT');
        } else {
            warning.addClass('d-none');
            btn.prop('disabled', false).css('opacity', '1').html('<i class="fas fa-check-circle mr-2"></i> CONFIRM INBOUND MANIFEST');
        }
    }
</script>
@endsection