@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">

<style>
    :root { 
        --primary: #4361ee; --primary-soft: rgba(67, 97, 238, 0.08); 
        --brand-success: #10b981; --brand-danger: #ef4444; 
        --brand-return: #6366f1; --dark-surface: #0f172a; --bg-main: #f1f5f9; 
    }

    body { background-color: var(--bg-main); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark-surface); }

    /* 🛸 DASHBOARD ELEMENTS */
    .heading-cyber { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(90deg, var(--dark-surface), var(--primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    
    .card-industrial { border: none; border-radius: 30px; background: #ffffff; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03); border: 1px solid rgba(255,255,255,0.8); overflow: hidden; }

    /* 📈 LEDGER TABLE DESIGN */
    .table-ledger thead th { 
        background: #f8fafc; color: #64748b; font-size: 10px; 
        text-transform: uppercase; letter-spacing: 1.5px; padding: 22px 15px; border: none; font-weight: 800;
    }
    .rm-row-header { cursor: pointer; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-left: 6px solid transparent; }
    .rm-row-header:hover { background-color: var(--primary-soft) !important; border-left-color: var(--primary); transform: scale(1.002); }
    
    .col-live { font-family: 'Orbitron'; font-weight: 900; font-size: 18px; color: var(--primary); background: rgba(67, 97, 238, 0.05); }

    /* 🏷️ UNIT & COMPONENT TAGS */
    .unit-tag-card { background: #fff; border-radius: 20px; border: 1px solid #eef2f6; transition: 0.3s; position: relative; overflow: hidden; }
    .unit-tag-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.05); border-color: var(--primary); }
    
    .badge-coil { background: var(--dark-surface); color: #fff; padding: 8px 16px; border-radius: 12px; font-family: 'JetBrains Mono'; font-weight: 700; font-size: 13px; }

    /* 📋 MUTATION FEED STYLE */
    .log-container { background: #f8fafc; border-radius: 24px; padding: 20px; border: 1px solid #e2e8f0; }
    .log-entry { 
        display: flex; justify-content: space-between; align-items: center; 
        padding: 15px; background: white; border-radius: 18px; margin-bottom: 12px;
        border-left: 6px solid #cbd5e1; box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        transition: 0.3s;
    }
    .log-entry:hover { transform: translateX(5px); }
    .log-entry.in { border-left-color: var(--brand-success); }
    .log-entry.out { border-left-color: var(--brand-danger); }
    .log-entry.ret { border-left-color: var(--brand-return); }

    /* FORM & INPUTS */
    .tech-input { border-radius: 16px; border: 2.5px solid #f1f5f9; padding: 12px 18px; font-weight: 700; background: #f8fafc; transition: 0.3s; }
    .tech-input:focus { border-color: var(--primary); background: #fff; outline: none; box-shadow: 0 0 0 5px rgba(67, 97, 238, 0.1); }

    /* ANIMATIONS */
    .hover-lift { transition: 0.3s; }
    .hover-lift:hover { transform: translateY(-3px); }
</style>

<div class="container-fluid mt-4 animate__animated animate__fadeIn">
    
    {{-- 🛰️ TOP COMMAND BAR --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-cyber m-0">RM_HUB <span class="text-primary">CORE_v2.6</span></h1>
            <p class="text-muted small font-weight-bold mb-0 text-uppercase">
                <i class="fas fa-microchip text-primary mr-2"></i> PT ASALTA MANDIRI AGUNG - INVENTORY CONTROL
            </p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
            <a href="{{ route('rm.log_print') }}" class="btn btn-white rounded-pill px-4 font-weight-bold border mr-2 shadow-sm hover-lift"><i class="fas fa-history mr-2"></i>RECAP</a>
            <button class="btn btn-primary shadow-lg px-4 py-2 rounded-pill font-weight-bold hover-lift" data-toggle="modal" data-target="#modalTambahRM">
                <i class="fas fa-plus-circle mr-2"></i>REGISTER_UNIT
            </button>
        </div>
    </div>

    {{-- 🔍 ANALYTICS FILTER --}}
    <div class="card-industrial p-4 mb-4 animate__animated animate__fadeInDown">
        <form action="{{ route('rm.store') }}" method="GET" id="autoFilterForm" class="row align-items-end">
            <div class="col-md-4">
                <label class="small font-weight-black text-primary mb-2 uppercase tracking-wider">Target Client Entity</label>
                <select name="customer" class="form-control tech-input w-100" onchange="this.form.submit()">
                    <option value="">-- ALL CLIENTS --</option>
                    @foreach($availableCustomers as $c) 
                        <option value="{{ trim($c->code) }}" {{ $customer == trim($c->code) ? 'selected' : '' }}>{{ $c->name }}</option> 
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="small font-weight-black text-primary mb-2 uppercase tracking-wider">Quick Spec Search</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text bg-transparent border-0"><i class="fas fa-search"></i></span></div>
                    <input type="text" name="alias" class="form-control tech-input" placeholder="Enter spec or alias code..." value="{{ request('alias') }}">
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-dark btn-block font-weight-bold" style="height: 52px; border-radius: 16px;">
                    <i class="fas fa-sync-alt mr-2"></i>SYNC_DASHBOARD
                </button>
            </div>
        </form>
    </div>

    {{-- 📊 MASTER LEDGER TABLE --}}
    <div class="card-industrial animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Spec & Material Identification</th>
                        <th>Opening</th>
                        <th class="text-success">In (Supplier)</th>
                        <th style="color: var(--brand-return);">In (Return)</th>
                        <th class="text-danger">Out (Prod)</th>
                        <th class="col-live">Live Balance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedMaterials as $group)
                    @php $slug = Str::slug($group->group_key); @endphp
                    <tr class="rm-row-header" data-toggle="collapse" data-target="#det-{{ $slug }}">
                        <td class="pl-5 py-4 text-left">
                            <div class="font-weight-black text-primary" style="font-size: 16px; font-family: 'JetBrains Mono';">{{ $group->alias_code ?? $group->group_key }}</div>
                            <small class="text-muted font-weight-bold uppercase" style="letter-spacing: 0.5px;">{{ $group->spec }} | {{ $group->size }}</small>
                        </td>
                        <td class="text-muted font-weight-bold">{{ number_format($group->total_init) }}</td>
                        <td class="text-success font-weight-black">+{{ number_format($group->total_in_s) }}</td>
                        <td style="color: var(--brand-return);" class="font-weight-black">+{{ number_format($group->total_in_r) }}</td>
                        <td class="text-danger font-weight-black">-{{ number_format($group->total_out) }}</td>
                        <td class="col-live">{{ number_format($group->total_live) }}</td>
                        <td><i class="fas fa-chevron-right text-muted animate__animated animate__pulse animate__infinite"></i></td>
                    </tr>
                    
                    {{-- 🔓 DRILL-DOWN DETAIL AREA --}}
                    <tr id="det-{{ $slug }}" class="collapse bg-light">
                        <td colspan="7" class="p-4">
                            <div class="row">
                                {{-- UNITS SECTION --}}
                                <div class="col-md-7">
                                    @foreach($group->details as $p)
                                    @php $subParts = DB::table('rm_stocks')->where('coil_id', $p->coil_id)->where('customer', $p->customer)->get(); @endphp
                                    <div class="unit-tag-card p-4 bg-white mb-3 animate__animated animate__fadeInLeft">
                                        <div class="d-flex justify-content-between align-items-start mb-4">
                                            <div>
                                                <span class="badge-coil shadow-sm">{{ $p->coil_id }}</span>
                                                <div class="mt-3">
                                                    <small class="text-muted font-weight-black uppercase d-block">Available Unit Balance</small>
                                                    <span class="h4 font-weight-black text-primary" style="font-family: 'Orbitron';">{{ number_format($p->stock_pcs) }} <small class="h6">PCS</small></span>
                                                </div>
                                            </div>
                                            <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                                                <button class="btn btn-light btn-sm px-3 font-weight-bold border-right" onclick="openAssignPart('{{ $p->id }}', '{{ $p->customer }}')"><i class="fas fa-plus mr-1 text-primary"></i> PART</button>
                                                <button class="btn btn-light btn-sm px-3 font-weight-bold border-right" onclick="openEditUnit('{{ $p->id }}', '{{ $p->coil_id }}', '{{ $p->stock_pcs }}')"><i class="fas fa-edit mr-1 text-warning"></i> ADJUST</button>
                                                <form action="{{ route('rm.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Erase this unit from system?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-light btn-sm px-3"><i class="fas fa-trash text-danger"></i></button>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="bg-light p-3 rounded-2xl border" style="border-style: dashed !important;">
                                            <small class="font-weight-black text-muted d-block mb-3 uppercase tracking-tighter">Mapped Production Parts</small>
                                            <div class="row">
                                                @foreach($subParts as $sp)
                                                <div class="col-md-6 mb-2">
                                                    <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-xl border shadow-sm hover-lift">
                                                        <div class="truncate pr-2">
                                                            <div class="font-weight-black text-dark" style="font-size: 12px;">{{ $sp->material_code }}</div>
                                                            <small class="text-muted font-weight-bold uppercase" style="font-size: 9px;">{{ $sp->material_name }}</small>
                                                        </div>
                                                        <form action="{{ route('rm.remove_part_from_unit', $sp->id) }}" method="POST">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="border-0 bg-transparent p-0"><i class="fas fa-unlink text-danger opacity-50 hover-opacity-100"></i></button>
                                                        </form>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                {{-- ACTIVITY FEED SECTION --}}
                                <div class="col-md-5">
                                    <div class="card border-0 shadow-sm rounded-3xl p-4 bg-white h-100 animate__animated animate__fadeInRight">
                                        <h6 class="font-weight-black text-dark mb-4 uppercase tracking-widest"><i class="fas fa-stream mr-2 text-primary"></i>Mutation_Analytics_Feed</h6>
                                        <div class="log-container" style="max-height: 550px; overflow-y:auto;">
                                            @forelse($group->combined_logs as $log)
                                                @php 
                                                    $isOut = isset($log->pcs_used); 
                                                    $isRet = !$isOut && ($log->source == 'return');
                                                @endphp
                                                <div class="log-entry {{ $isOut ? 'out' : ($isRet ? 'ret' : 'in') }}">
                                                    <div style="flex: 1;">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="font-weight-black uppercase" style="font-size: 9px; letter-spacing: 1px;">
                                                                {{ $isOut ? 'Production_Exit' : ($isRet ? 'Warehouse_Return' : 'Supplier_Inbound') }}
                                                            </span>
                                                            <small class="text-muted font-weight-bold">{{ date('d M, H:i', strtotime($log->created_at)) }}</small>
                                                        </div>
                                                        <div class="text-dark font-weight-bold" style="font-family: 'JetBrains Mono'; font-size: 12px;">
                                                            {{ $log->no_produksi ?? $log->po_identitas ?? 'SYSTEM_ADJUSTMENT' }}
                                                        </div>
                                                    </div>
                                                    <div class="ml-4 h5 font-weight-black mb-0" style="font-family: 'Orbitron';">
                                                        {{ $isOut ? '-' : '+' }}{{ number_format($log->pcs_used ?? $log->pcs_in) }}
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center py-5">
                                                    <i class="fas fa-ghost fa-3x text-light mb-3"></i>
                                                    <p class="text-muted small font-weight-bold">Zero Mutations Found</p>
                                                </div>
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

{{-- 🛠️ MODAL: ADJUST UNIT SALDO (GLASS UI) --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalEditUnit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 40px; overflow: hidden;">
            <div class="modal-header bg-warning p-4 border-0">
                <h5 class="modal-title font-weight-black uppercase text-dark"><i class="fas fa-tools mr-2"></i>Inventory_Correction</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('rm.update_unit_pcs') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group mb-4 text-center">
                        <small class="font-weight-black text-muted uppercase d-block mb-2">Targeting Unit ID</small>
                        <input type="text" id="edit_coil" class="form-control tech-input text-center font-weight-black" style="font-size: 20px; color: var(--primary);" readonly>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-black text-muted uppercase mb-2 d-block">Input Corrected Balance (PCS)</label>
                        <input type="number" name="new_qty" id="edit_qty" class="form-control tech-input font-weight-black text-primary text-center" style="font-size: 42px; height: 100px;" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-warning btn-block py-4 font-weight-black rounded-pill shadow-lg uppercase" style="letter-spacing: 1px;">Confirm_Correction</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 🛠️ MODAL: ASSIGN COMPONENT (GLASS UI) --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalAssignPart" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 40px; overflow: hidden;">
            <div class="modal-header bg-primary text-white p-4 border-0">
                <h5 class="modal-title font-weight-black uppercase"><i class="fas fa-link mr-2"></i>Map_New_Component</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('rm.assign_part_to_unit') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <input type="hidden" name="rm_stock_id" id="assign_ref_id">
                    <div class="bg-primary-soft p-4 rounded-2xl mb-4 border border-primary text-center">
                        <i class="fas fa-info-circle text-primary mb-2"></i>
                        <p class="small text-dark font-weight-bold mb-0">Binding a component will allow this unit to be used as raw material for the selected part.</p>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-black text-primary uppercase mb-2 d-block">Choose Target Component</label>
                        <select name="part_no" id="assign_select_part" class="form-control tech-input font-weight-bold" style="height: 60px;" required>
                            {{-- AJAX Populated --}}
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-primary btn-block py-4 font-weight-black rounded-pill shadow-lg uppercase" style="letter-spacing: 1px;">Register_Mapping</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('Gudang.rm_modals') 

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // ⚡ AJAX PART MAPPING
    function openAssignPart(id, customer) {
        $('#assign_ref_id').val(id);
        $('#assign_select_part').html('<option>Loading database...</option>');
        $.ajax({
            url: "/get-parts-and-specs/" + encodeURIComponent(customer),
            type: "GET",
            success: function(res) {
                let options = '<option value="">-- SELECT COMPONENT --</option>';
                $.each(res.parts, function(k, v) {
                    options += `<option value="${v.part_no}">${v.part_no} - ${v.part_name}</option>`;
                });
                $('#assign_select_part').html(options);
                $('#modalAssignPart').modal('show');
            }
        });
    }

    // ⚡ UNIT ADJUSTMENT
    function openEditUnit(id, coil, qty) {
        $('#edit_id').val(id);
        $('#edit_coil').val(coil);
        $('#edit_qty').val(qty);
        $('#modalEditUnit').modal('show');
    }

    $(document).ready(function() {
        // Toggle animation for chevron
        $('.rm-row-header').click(function() {
            $(this).find('.fa-chevron-right').toggleClass('fa-rotate-90');
        });

        // Auto-refresh on customer entity select
        $('select[name="customer"]').on('change', function() {
            $(this).closest('form').submit();
        });
    });
</script>
@endsection