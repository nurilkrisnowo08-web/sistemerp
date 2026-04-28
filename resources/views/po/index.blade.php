@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&family=Roboto+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root {
        --sultan-navy: #0f172a; --sultan-gold: #d4af37; --sultan-blue: #3b82f6;
        --sultan-silver: #e2e8f0; --sultan-bg: #f1f5f9; --sultan-success: #10b981;
    }

    .main-terminal { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--sultan-bg); min-height: 100vh; color: var(--sultan-navy); }

    /* ✨ ANIMASI PREMIUM */
    @keyframes sultanEntrance { from { opacity: 0; transform: scale(0.98) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    .anim-sultan { animation: sultanEntrance 0.7s cubic-bezier(0.16, 1, 0.3, 1) both; }

    /* ✨ SULTAN CARD (GLASSMORPHISM) */
    .sultan-card { 
        background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 24px; 
        box-shadow: 0 10px 40px rgba(0,0,0,0.04); overflow: hidden; margin-bottom: 2.5rem;
    }
    .customer-banner { 
        background: var(--sultan-navy); padding: 25px 35px; border-left: 8px solid var(--sultan-gold);
        display: flex; justify-content: space-between; align-items: center;
    }

    /* ✨ ELITE TABLE DESIGN */
    .table-elite { width: 100%; border-collapse: separate; border-spacing: 0; }
    .table-elite thead th { 
        background: transparent; color: #64748b; font-size: 10px; 
        text-transform: uppercase; padding: 20px; border-bottom: 1px solid #f1f5f9;
        letter-spacing: 2px; font-weight: 800;
    }
    .table-elite tbody td { 
        padding: 22px 20px; vertical-align: middle; border-bottom: 1px solid #f8fafc; 
        font-size: 13px; font-weight: 600; transition: all 0.3s;
    }
    .table-elite tbody tr:hover td { background: rgba(59, 130, 246, 0.03); color: var(--sultan-blue); }

    /* ✨ TACTICAL COMMAND CENTER */
    .cmd-group { display: flex; align-items: center; justify-content: center; gap: 12px; }
    
    .btn-sultan-inspect {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; border: none;
        padding: 8px 24px; border-radius: 12px; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1.5px; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.2);
    }
    .btn-sultan-inspect:hover { transform: translateY(-3px) scale(1.05); box-shadow: 0 8px 25px rgba(15, 23, 42, 0.3); color: var(--sultan-gold); }
    
    .btn-sultan-edit {
        background: #fff; color: var(--sultan-gold); border: 1.5px solid #e2e8f0;
        width: 38px; height: 38px; border-radius: 12px; display: flex;
        align-items: center; justify-content: center; transition: all 0.3s;
    }
    .btn-sultan-edit:hover { background: var(--sultan-gold); color: #fff; border-color: var(--sultan-gold); transform: rotate(15deg); }

    /* ✨ STATUS PILLS */
    .sultan-pill { padding: 8px 18px; border-radius: 12px; font-size: 10px; font-weight: 800; border: 1.5px solid transparent; }
    .badge-urgent { background: rgba(239, 68, 68, 0.05); color: #ef4444; border-color: rgba(239, 68, 68, 0.1); }
    .badge-testing { background: rgba(59, 130, 246, 0.05); color: #3b82f6; border-color: rgba(59, 130, 246, 0.1); }
    .badge-reguler { background: rgba(16, 185, 129, 0.05); color: #10b981; border-color: rgba(16, 185, 129, 0.1); }

    /* ✨ MONOSPACE FONT FOR IDS */
    .po-id { font-family: 'Roboto Mono', monospace; font-weight: 700; font-size: 15px; color: var(--sultan-navy); letter-spacing: -0.5px; }

    /* Modal Styling */
    .modal-content { border-radius: 30px; border: none; overflow: hidden; }
</style>

<div class="container-fluid main-terminal anim-sultan">
    
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-lg font-weight-bold py-3 mb-4" style="border-radius: 15px; background: #fff; border-left: 6px solid var(--sultan-success) !important;">
            <i class="fas fa-check-double mr-2 text-success"></i> {{ session('success') }}
        </div>
    @endif

    {{-- 🛰️ ELITE HEADER --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-5">
        <div>
            <h1 class="h2 mb-0 text-gray-900 font-weight-extrabold uppercase" style="letter-spacing: -1px;">
                Purchase Order <span style="color: var(--sultan-gold)">Terminal</span>
            </h1>
            <p class="text-muted font-weight-bold mb-0">MRP CORE // ARCHITECTURE VERSION 2.1</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('po.history') }}" class="btn btn-white px-4 py-2 mr-3" style="border-radius: 15px; font-weight: 800; border: 1.5px solid #e2e8f0; color: #64748b;">
                <i class="fas fa-archive mr-2"></i> LOG ARCHIVE
            </a>
            <button class="btn btn-primary px-4 py-2 shadow-lg" style="border-radius: 15px; font-weight: 800; background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); border: none;" data-toggle="modal" data-target="#modalTambahPO">
                <i class="fas fa-plus-circle mr-2"></i> REGISTER NEW PO
            </button>
        </div>
    </div>

    @forelse($purchaseOrders as $customerCode => $poGroups)
    <div class="sultan-card">
        <div class="customer-banner">
            <div>
                <span class="small font-weight-bold text-gray-400 uppercase tracking-widest">Entity Identification</span>
                <h4 class="m-0 font-weight-extrabold text-white">{{ $customerCode }}</h4>
            </div>
            <div class="text-right">
                <span class="badge badge-warning px-4 py-2 font-weight-bold shadow-sm" style="border-radius: 10px; background: var(--sultan-gold); color: #000;">
                    <i class="fas fa-database mr-2"></i>{{ $poGroups->count() }} ACTIVE RECORDS
                </span>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table-elite text-center">
                <thead>
                    <tr>
                        <th width="80">INDICATOR</th>
                        <th class="text-left">PO IDENTITY NUMBER</th>
                        <th>CLASSIFICATION</th>
                        <th>PART COMPOSITION</th>
                        <th>DUE DATE</th>
                        <th width="220">COMMAND CENTER</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($poGroups as $poNumber => $items)
                    @php 
                        $firstItem = $items->first(); 
                        $ket = $firstItem->jenis_po ?? $firstItem->keterangan ?? 'REGULER';
                        $badgeClass = ($ket == 'URGENT / TAMBAHAN') ? 'badge-urgent' : (($ket == 'TESTING') ? 'badge-testing' : 'badge-reguler');
                    @endphp
                    <tr>
                        <td>
                            <button class="btn btn-light" style="border-radius: 10px; width: 35px; height: 35px;" data-toggle="collapse" data-target="#detail-{{ Str::slug($poNumber) }}">
                                <i class="fas fa-chevron-down text-primary"></i>
                            </button>
                        </td>
                        <td class="po-id text-left">{{ $poNumber }}</td>
                        <td><span class="sultan-pill {{ $badgeClass }}">{{ $ket }}</span></td>
                        <td class="font-weight-bold" style="color: #64748b;">{{ $items->count() }} ITEMS DETECTED</td>
                        <td class="font-weight-bold">{{ $firstItem->due_date ? date('d M Y', strtotime($firstItem->due_date)) : '-' }}</td>
                        <td>
                            <div class="cmd-group">
                                <button class="btn-sultan-edit btn-edit-header" 
                                        data-toggle="modal" data-target="#modalEditPO"
                                        data-ponumber="{{ $poNumber }}"
                                        data-duedate="{{ $firstItem->due_date }}"
                                        data-keterangan="{{ $ket }}">
                                    <i class="fas fa-cog"></i>
                                </button>
                                
                                <button class="btn-sultan-inspect" data-toggle="collapse" data-target="#detail-{{ Str::slug($poNumber) }}">
                                    <i class="fas fa-expand-arrows-alt mr-2"></i> INSPECT
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- 🕵️ NESTED ANALYTICS VIEW --}}
                    <tr id="detail-{{ Str::slug($poNumber) }}" class="collapse">
                        <td colspan="6" class="px-5 py-4" style="background: #fafbfc;">
                            <div style="border: 1.5px solid #e2e8f0; border-radius: 20px; background: #fff; overflow: hidden; box-shadow: inset 0 2px 10px rgba(0,0,0,0.02);">
                                <table class="table-elite mb-0">
                                    <thead style="background: #f8fafc;">
                                        <tr class="text-center">
                                            <th class="py-3">PART IDENTITY</th>
                                            <th>ORDER VOLUME</th>
                                            <th>OUTSTANDING</th>
                                            <th>SYSTEM STATUS</th>
                                            <th width="150">ADJUSTMENT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                        <tr class="text-center">
                                            <td class="text-left pl-5 po-id uppercase">{{ $item->part_no }}</td>
                                            <td class="font-weight-extrabold" style="font-size: 14px;">{{ number_format($item->quantity) }}</td>
                                            <td class="text-danger font-weight-extrabold" style="font-size: 14px;">{{ number_format($item->sisa) }}</td>
                                            <td>
                                                <span class="badge {{ $item->status == 'READY' ? 'bg-primary' : 'bg-dark' }} text-white px-3 py-1" style="font-size: 9px; border-radius: 6px; letter-spacing: 1px;">
                                                    {{ $item->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-outline-primary btn-sm btn-edit-qty font-weight-bold w-100" 
                                                        data-toggle="modal" data-target="#modalEditQty"
                                                        data-id="{{ $item->id }}"
                                                        data-part="{{ $item->part_no }}"
                                                        data-qty="{{ $item->quantity }}"
                                                        style="font-size: 10px; border-radius: 10px; border-width: 2px;">
                                                    EDIT QUANTITY
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="text-center py-5 sultan-card">
        <i class="fas fa-layer-group fa-4x mb-4" style="color: #cbd5e1;"></i>
        <h4 class="text-gray-400 font-weight-bold">DATABASE EMPTY: NO ACTIVE PO</h4>
    </div>
    @endforelse
</div>

{{-- 🛡️ MODALS: TETAP KONSISTEN DENGAN FUNGSI ASLI --}}

<div class="modal fade" id="modalTambahPO" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-2xl">
            <div class="modal-header bg-primary text-white py-4 px-5">
                <h5 class="modal-title font-weight-extrabold uppercase">
                    <i class="fas fa-file-signature mr-3"></i> PO REGISTRATION TERMINAL
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('po.store') }}" method="POST">
                @csrf
                <div class="modal-body p-5 bg-light">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="small font-weight-extrabold text-muted uppercase tracking-wider">Client Identity</label>
                            <select name="customer_code" id="cust_select_ajax" class="form-control border-0 shadow-sm font-weight-bold" style="height: 50px; border-radius: 12px;" required>
                                <option value="">-- SELECT --</option>
                                @foreach($customers as $c) <option value="{{ $c->code }}">{{ $c->code }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-extrabold text-muted uppercase tracking-wider">PO Number</label>
                            <input type="text" name="po_number" class="form-control border-0 shadow-sm text-uppercase font-weight-bold" style="height: 50px; border-radius: 12px;" required>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-extrabold text-muted uppercase tracking-wider">Final Deadline</label>
                            <input type="date" name="due_date" class="form-control border-0 shadow-sm font-weight-bold" style="height: 50px; border-radius: 12px;" required>
                        </div>
                    </div>
                    <hr class="my-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="font-weight-extrabold text-dark mb-0 uppercase tracking-widest">Part Infrastructure</h6>
                        <button type="button" class="btn btn-success font-weight-bold px-4" style="border-radius: 12px;" onclick="addPartRow()">+ ADD BARIS</button>
                    </div>
                    <div id="part-container">
                        <div class="row mb-3 part-row">
                            <div class="col-md-8"><select name="part_no[]" class="form-control border-0 shadow-sm part-list-select font-weight-bold" style="height: 45px; border-radius: 10px;" required disabled></select></div>
                            <div class="col-md-4"><input type="number" name="quantity[]" class="form-control border-0 shadow-sm text-center font-weight-bold" style="height: 45px; border-radius: 10px;" placeholder="QTY" required></div>
                        </div>
                    </div>
                    <div class="mt-5 p-4 bg-white rounded-2xl border" style="border-radius: 20px;">
                        <label class="small font-weight-extrabold text-primary uppercase mb-3 d-block">PO Classification</label>
                        <select name="jenis_po" class="form-control border-0 bg-light font-weight-bold" style="height: 45px; border-radius: 12px;" required>
                            <option value="REGULER">REGULER STOCK</option>
                            <option value="URGENT / TAMBAHAN">URGENT PRIORITY</option>
                            <option value="TESTING">RESEARCH / TESTING</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5"><button type="submit" class="btn btn-primary btn-block py-4 font-weight-extrabold uppercase shadow-2xl" style="border-radius: 15px; font-size: 16px;">COMMIT ALL RECORDS TO CORE</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditPO" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--sultan-gold);">
                <h6 class="modal-title font-weight-extrabold uppercase text-white small">PO HEADER OPTIMIZATION</h6>
            </div>
            <form id="formEditHeader" action="" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-5">
                    <div class="form-group mb-4">
                        <label class="small font-weight-extrabold text-muted uppercase">Target PO Number</label>
                        <input type="text" id="header_po_display" class="form-control bg-light font-weight-bold border-0" style="height: 50px; border-radius: 12px;" disabled>
                    </div>
                    <div class="form-group mb-4">
                        <label class="small font-weight-extrabold text-muted uppercase">New Due Date</label>
                        <input type="date" name="due_date" id="header_due_date" class="form-control border-warning shadow-sm font-weight-bold" style="height: 50px; border-radius: 12px;" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-extrabold text-muted uppercase">Modify Category</label>
                        <select name="jenis_po" id="header_keterangan" class="form-control border-warning shadow-sm font-weight-bold" style="height: 50px; border-radius: 12px;" required>
                            <option value="REGULER">REGULER</option>
                            <option value="URGENT / TAMBAHAN">URGENT / TAMBAHAN</option>
                            <option value="TESTING">TESTING</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5"><button type="submit" class="btn btn-warning btn-block font-weight-extrabold py-3" style="border-radius: 15px; color: #fff;">EXECUTE UPDATE</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditQty" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info py-3">
                <h6 class="modal-title font-weight-bold text-white small uppercase">LOGIC ADJUSTMENT</h6>
            </div>
            <form action="{{ route('po.update_qty') }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="item_id">
                    <div class="form-group"><label class="small font-weight-bold text-muted uppercase">Part Identify</label><input type="text" id="part_no_display" class="form-control bg-light font-weight-bold border-0" disabled></div>
                    <div class="form-group"><label class="small font-weight-bold text-primary uppercase">Revised Volume</label><input type="number" name="quantity" id="edit_qty" class="form-control border-primary shadow-sm font-weight-bold text-center" style="height: 45px; border-radius: 10px;" required></div>
                </div>
                <div class="modal-footer border-0 p-4"><button type="submit" class="btn btn-info btn-block font-weight-extrabold py-2" style="border-radius: 12px;">UPDATE LOGIC</button></div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#cust_select_ajax').on('change', function() {
        var customerCode = $(this).val();
        loadPartsToDropdown(customerCode, $('.part-list-select'));
    });

    $('.btn-edit-header').on('click', function() {
        var po = $(this).data('ponumber');
        var encodedPo = encodeURIComponent(po);
        $('#formEditHeader').attr('action', '/po/update-header/' + encodedPo); 
        $('#header_po_display').val(po);
        $('#header_due_date').val($(this).data('duedate'));
        $('#header_keterangan').val($(this).data('keterangan'));
    });

    $('.btn-edit-qty').on('click', function() {
        $('#item_id').val($(this).data('id'));
        $('#part_no_display').val($(this).data('part'));
        $('#edit_qty').val($(this).data('qty'));
    });
});

function loadPartsToDropdown(customerCode, element) {
    if(customerCode) {
        element.prop('disabled', false).html('<option value="">-- SYNCING --</option>');
        $.ajax({
            url: '/po/get-parts/' + customerCode, type: 'GET',
            success: function(data) {
                element.html('<option value="">-- SELECT PART --</option>');
                $.each(data, function(key, value) {
                    element.append('<option value="'+ value.part_no +'">'+ value.part_no + ' - ' + value.part_name +'</option>');
                });
            }
        });
    }
}

function addPartRow() {
    var customerCode = $('#cust_select_ajax').val();
    if(!customerCode) { alert("Core Identity Required: Please select Customer Code."); return; }
    var newRow = `
        <div class="row mb-3 part-row anim-sultan">
            <div class="col-md-8"><select name="part_no[]" class="form-control border-0 shadow-sm part-list-select font-weight-bold" style="height:45px; border-radius:10px;" required><option value="">-- RE-SYNCING --</option></select></div>
            <div class="col-md-3"><input type="number" name="quantity[]" class="form-control border-0 shadow-sm text-center font-weight-bold" style="height:45px; border-radius:10px;" placeholder="QTY" required></div>
            <div class="col-md-1"><button type="button" class="btn btn-danger btn-circle-sm shadow-sm" style="border-radius:10px; width:45px; height:45px;" onclick="$(this).closest('.row').remove()"><i class="fas fa-times"></i></button></div>
        </div>`;
    $('#part-container').append(newRow);
    loadPartsToDropdown(customerCode, $('#part-container .part-row:last .part-list-select'));
}
</script>
@endsection