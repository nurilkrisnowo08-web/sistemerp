@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">

<style>
    :root { 
        --primary: #4361ee; --primary-soft: rgba(67, 97, 238, 0.1); 
        --brand-success: #10b981; --brand-danger: #ef4444; 
        --brand-return: #6366f1; --dark: #0f172a; --slate-bg: #f1f5f9; 
    }
    body { background-color: var(--slate-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark); }
    
    .heading-cyber { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; }
    .card-industrial { border: none; border-radius: 30px; background: #ffffff; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03); border: 1px solid #eef2f6; overflow: hidden; }

    /* 📈 LEDGER TABLE PREMIUM */
    .table-ledger thead th { 
        background: #f8fafc; color: #64748b; font-size: 11px; 
        text-transform: uppercase; letter-spacing: 1.5px; padding: 22px 15px; border: none; 
    }
    .rm-row-header { cursor: pointer; transition: 0.3s; border-left: 6px solid transparent; }
    .rm-row-header:hover { background-color: var(--primary-soft) !important; border-left-color: var(--primary); }
    .col-live { font-family: 'Orbitron'; font-weight: 900; font-size: 18px; color: var(--primary); background: var(--primary-soft); border-radius: 15px; }

    /* 🏷️ UNIT BADGES */
    .badge-coil { 
        background: var(--dark); color: #fff; padding: 8px 16px; border-radius: 12px; 
        font-family: 'JetBrains Mono'; font-weight: 700; font-size: 13px; cursor: pointer; transition: 0.3s;
    }
    .badge-coil:hover { transform: scale(1.05); background: var(--primary); box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3); }

    /* 📋 ACTIVITY FEED */
    .log-container { background: #f8fafc; border-radius: 20px; padding: 20px; border: 1px solid #e2e8f0; }
    .log-entry { 
        display: flex; justify-content: space-between; align-items: center; 
        padding: 12px 15px; background: white; border-radius: 15px; margin-bottom: 10px;
        border-left: 5px solid #cbd5e1; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }
    .log-entry.in { border-left-color: var(--brand-success); }
    .log-entry.out { border-left-color: var(--brand-danger); }
    .log-entry.ret { border-left-color: var(--brand-return); }

    /* FORM TECH */
    .form-control-tech { border-radius: 15px; border: 2.5px solid #f1f5f9; padding: 12px 18px; font-weight: 700; background: #f8fafc; transition: 0.3s; }
    .form-control-tech:focus { border-color: var(--primary); background: #fff; outline: none; box-shadow: 0 0 0 5px rgba(67, 97, 238, 0.1); }
</style>

<div class="container-fluid mt-4 animate__animated animate__fadeIn">
    
    {{-- 🛰️ TOP DASHBOARD BAR --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="heading-cyber m-0">RM_HUB <span class="text-primary">CORE_v2.6</span></h2>
            <p class="text-muted small font-weight-bold mb-0 text-uppercase">
                <i class="fas fa-database text-primary mr-2"></i> PT ASALTA MANDIRI AGUNG - INVENTORY SYNC
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('rm.log_print') }}" class="btn btn-dark rounded-pill px-4 font-weight-bold mr-2"><i class="fas fa-history mr-2"></i>HISTORY</a>
            <button class="btn btn-primary shadow-lg px-4 rounded-pill font-weight-bold" data-toggle="modal" data-target="#modalTambahRM">
                <i class="fas fa-plus-circle mr-2"></i>REGISTER_COIL
            </button>
        </div>
    </div>

    {{-- 🔍 FILTER PANEL --}}
    <div class="card-industrial p-4 mb-4">
        <form action="{{ route('rm.store') }}" method="GET" id="autoFilterForm" class="row align-items-end">
            <div class="col-md-4">
                <label class="small font-weight-black text-primary mb-2 uppercase">Client entity</label>
                <select name="customer" class="form-control form-control-tech" onchange="this.form.submit()">
                    <option value="">-- ALL CLIENTS --</option>
                    @foreach($availableCustomers as $c) 
                        <option value="{{ trim($c->code) }}" {{ $customer == trim($c->code) ? 'selected' : '' }}>{{ $c->name }}</option> 
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="small font-weight-black text-primary mb-2 uppercase">Search material alias</label>
                <input type="text" name="alias" class="form-control form-control-tech" placeholder="Search spec/alias..." value="{{ request('alias') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-dark btn-block font-weight-bold" style="height: 52px; border-radius: 15px;">
                    <i class="fas fa-sync-alt mr-2"></i>SYNC_DATABASE
                </button>
            </div>
        </form>
    </div>

    {{-- 📊 MAIN INVENTORY TABLE --}}
    <div class="card-industrial">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Material Identification</th>
                        <th>Opening</th>
                        <th class="text-success">In (Supplier)</th>
                        <th style="color: var(--brand-return);">In (Return)</th>
                        <th class="text-danger">Out (Prod)</th>
                        <th class="col-live">Live Balance</th>
                        <th>ACT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedMaterials as $group)
                    @php $slug = Str::slug($group->group_key); @endphp
                    <tr class="rm-row-header" data-toggle="collapse" data-target="#det-{{ $slug }}">
                        <td class="pl-5 py-4 text-left">
                            <div class="font-weight-black text-primary" style="font-size: 16px; font-family: 'JetBrains Mono';">{{ $group->alias_code ?? $group->group_key }}</div>
                            <small class="text-muted font-weight-bold uppercase">{{ $group->spec }} | {{ $group->size }}</small>
                        </td>
                        <td class="text-muted font-weight-bold">{{ number_format($group->total_init) }}</td>
                        <td class="text-success font-weight-black">+{{ number_format($group->total_in_s) }}</td>
                        <td style="color: var(--brand-return);" class="font-weight-black">+{{ number_format($group->total_in_r) }}</td>
                        <td class="text-danger font-weight-black">-{{ number_format($group->total_out) }}</td>
                        <td class="col-live">{{ number_format($group->total_live) }}</td>
                        <td><i class="fas fa-chevron-down text-muted"></i></td>
                    </tr>
                    
                    {{-- 🔓 DETAIL COLLAPSE AREA --}}
                    <tr id="det-{{ $slug }}" class="collapse bg-light">
                        <td colspan="7" class="p-4">
                            <div class="row">
                                {{-- LEFT: UNIT LIST --}}
                                <div class="col-md-7">
                                    @foreach($group->details as $p)
                                    @php $subParts = DB::table('rm_stocks')->where('coil_id', $p->coil_id)->where('customer', $p->customer)->get(); @endphp
                                    <div class="card shadow-sm border-0 mb-3 rounded-xl overflow-hidden animate__animated animate__fadeInLeft">
                                        <div class="card-body p-4 bg-white">
                                            <div class="d-flex justify-content-between align-items-start mb-4">
                                                <div>
                                                    <span class="badge-coil">{{ $p->coil_id }}</span>
                                                    <small class="d-block text-muted mt-3 font-weight-black uppercase">Unit Saldo: <b class="text-primary" style="font-size: 16px;">{{ number_format($p->stock_pcs) }} PCS</b></small>
                                                </div>
                                                <div class="btn-group">
                                                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3 mr-2 font-weight-bold" onclick="openAssignPart('{{ $p->id }}', '{{ $p->customer }}')">ADD_PART</button>
                                                    <button class="btn btn-outline-warning btn-sm rounded-pill px-3 mr-2 font-weight-bold" onclick="openEditUnit('{{ $p->id }}', '{{ $p->coil_id }}', '{{ $p->stock_pcs }}')">ADJUST</button>
                                                    <form action="{{ route('rm.destroy', $p->id) }}" method="POST" onsubmit="return confirm('ERASE UNIT?')">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger btn-sm rounded-circle"><i class="fas fa-trash"></i></button></form>
                                                </div>
                                            </div>

                                            <div class="bg-light p-3 rounded-xl border">
                                                <small class="font-weight-black text-muted d-block mb-3 uppercase">Mapped Production Components:</small>
                                                @foreach($subParts as $sp)
                                                <div class="d-flex justify-content-between align-items-center mb-2 bg-white p-3 rounded-lg border shadow-sm">
                                                    <span class="font-weight-bold text-dark" style="font-size: 13px;">
                                                        <i class="fas fa-check-circle text-success mr-2"></i>{{ $sp->material_code }} - <span class="text-muted">{{ $sp->material_name }}</span>
                                                    </span>
                                                    <form action="{{ route('rm.remove_part_from_unit', $sp->id) }}" method="POST">@csrf @method('DELETE')<button type="submit" class="border-0 bg-transparent text-danger hover-lift"><i class="fas fa-unlink"></i></button></form>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                {{-- RIGHT: ACTIVITY FEED --}}
                                <div class="col-md-5">
                                    <div class="card border-0 shadow-sm rounded-xl p-4 bg-white h-100">
                                        <h6 class="font-weight-black text-dark mb-4 uppercase"><i class="fas fa-stream mr-2 text-primary"></i>Mutation_Activity_Feed</h6>
                                        <div class="log-container" style="max-height: 500px; overflow-y:auto;">
                                            @forelse($group->combined_logs as $log)
                                                @php 
                                                    $isOut = isset($log->pcs_used); 
                                                    $isRet = !$isOut && ($log->source == 'return');
                                                @endphp
                                                <div class="log-entry {{ $isOut ? 'out' : ($isRet ? 'ret' : 'in') }}">
                                                    <div style="flex: 1;">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="font-weight-black uppercase" style="font-size: 10px;">{{ $isOut ? 'PROD_OUT' : ($isRet ? 'RETURN_IN' : 'SUPPLIER_IN') }}</span>
                                                            <small class="text-muted font-weight-bold">{{ date('d M, H:i', strtotime($log->created_at)) }}</small>
                                                        </div>
                                                        <div class="text-dark font-weight-bold" style="font-family: 'JetBrains Mono';">{{ $log->no_produksi ?? $log->po_identitas ?? 'MANUAL_ENTRY' }}</div>
                                                    </div>
                                                    <div class="ml-4 h5 font-weight-black mb-0">
                                                        {{ $isOut ? '-' : '+' }}{{ number_format($log->pcs_used ?? $log->pcs_in) }}
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center py-5 text-muted small italic">No logs found for this spec.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🛠️ MODAL: ADJUST UNIT SALDO --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalEditUnit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 35px;">
            <div class="modal-header bg-warning p-4 border-0">
                <h5 class="modal-title font-weight-black uppercase"><i class="fas fa-edit mr-2"></i>Adjust_Unit_Saldo</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('rm.update_unit_pcs') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group mb-4">
                        <label class="small font-weight-black text-muted uppercase mb-2">Unit Coil ID</label>
                        <input type="text" id="edit_coil" class="form-control form-control-tech font-mono" readonly>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-black text-muted uppercase mb-2">Adjust New Quantity (PCS)</label>
                        <input type="number" name="new_qty" id="edit_qty" class="form-control form-control-tech font-weight-black text-primary" style="font-size: 32px; height: 80px;" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-warning btn-block py-3 font-weight-black rounded-pill shadow-lg">AUTHORIZE_ADJUSTMENT</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 🛠️ MODAL: ASSIGN PART TO UNIT --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalAssignPart" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 35px;">
            <div class="modal-header bg-primary text-white p-4 border-0">
                <h5 class="modal-title font-weight-black uppercase"><i class="fas fa-plus-circle mr-2"></i>Assign_Component</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('rm.assign_part_to_unit') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <input type="hidden" name="rm_stock_id" id="assign_ref_id">
                    <p class="text-muted font-weight-bold text-center mb-4">Select components that can be produced from this specific coil unit.</p>
                    <div class="form-group mb-0">
                        <label class="small font-weight-black text-primary uppercase mb-2">Component Selection</label>
                        <select name="part_no" id="assign_select_part" class="form-control form-control-tech font-weight-bold" required>
                            {{-- AJAX Populated --}}
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-black rounded-pill shadow-lg">REGISTER_MAPPING</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 🛡️ MODAL: REGISTER NEW COIL (STAY AS IS) --}}
@include('Gudang.rm_modals') 

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // 1. Fungsi Assign Part (Fix AJAX & Dropdown)
    function openAssignPart(id, customer) {
        $('#assign_ref_id').val(id);
        $.ajax({
            url: "/get-parts-and-specs/" + encodeURIComponent(customer),
            type: "GET",
            success: function(res) {
                let options = '<option value="">-- SELECT PART --</option>';
                $.each(res.parts, function(k, v) {
                    options += `<option value="${v.part_no}">${v.part_no} - ${v.part_name}</option>`;
                });
                $('#assign_select_part').html(options);
                $('#modalAssignPart').modal('show');
            }
        });
    }

    // 2. Fungsi Edit Unit Saldo
    function openEditUnit(id, coil, qty) {
        $('#edit_id').val(id);
        $('#edit_coil').val(coil);
        $('#edit_qty').val(qty);
        $('#modalEditUnit').modal('show');
    }

    $(document).ready(function() {
        // Auto Sync on Customer Change
        $('select[name="customer"]').on('change', function() {
            $(this).closest('form').submit();
        });
    });
</script>
@endsection