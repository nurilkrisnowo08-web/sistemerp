@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">

<style>
    :root { --primary: #4361ee; --primary-soft: #f0f3ff; --dark: #0f172a; --slate-bg: #f8fafc; }
    body { background-color: var(--slate-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark); }
    .card-modern { border: none; border-radius: 20px; background: #ffffff; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02); margin-bottom: 1.5rem; overflow: hidden; border: 1px solid #eef2f6; }
    
    .btn-historical { background: #4b4d5a; color: #ffffff !important; border-radius: 50px; padding: 8px 25px; font-weight: 700; border: none; transition: 0.3s; display: inline-flex; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); font-size: 13px; text-decoration: none !important; }
    .btn-historical:hover { background: #343a40; transform: translateY(-2px); }

    /* ✨ LEDGER TABLE STYLE rill */
    .table-ledger thead th { vertical-align: middle; border: none; }
    .header-mutation { background: #1e293b; color: #f8fafc; font-size: 9px; text-transform: uppercase; letter-spacing: 2px; font-weight: 800; padding: 10px !important; }
    .table-ledger th { background-color: #fdfdfd; text-transform: uppercase; font-size: 10px; font-weight: 800; color: #94a3b8; letter-spacing: 0.8px; padding: 15px; border-bottom: 2px solid #f1f5f9 !important; }
    
    .rm-row-header { cursor: pointer; transition: background 0.2s; }
    .rm-row-header:hover { background-color: var(--primary-soft) !important; }
    
    .col-init { background: rgba(148, 163, 184, 0.05); color: #64748b; font-family: 'JetBrains Mono'; }
    .col-in-s { background: rgba(16, 185, 129, 0.05); color: #10b981; font-family: 'JetBrains Mono'; }
    .col-in-r { background: rgba(6, 182, 212, 0.05); color: #0891b2; font-family: 'JetBrains Mono'; }
    .col-out { background: rgba(239, 68, 68, 0.05); color: #ef4444; font-family: 'JetBrains Mono'; }
    .col-live { background: rgba(67, 97, 238, 0.05); color: var(--primary); font-family: 'Orbitron'; font-weight: 800 !important; }

    .badge-coil { background: #fff; color: var(--dark); border: 2.5px solid #e2e8f0; padding: 5px 12px; border-radius: 8px; font-family: 'JetBrains Mono'; font-weight: 800; font-size: 11px; cursor: pointer; transition: 0.2s; display: inline-block; }
    .badge-coil:hover { border-color: var(--primary); color: var(--primary); transform: translateY(-2px); }
    
    .btn-act { padding: 6px 10px; border-radius: 8px; font-size: 10px; font-weight: 700; transition: 0.2s; background: #fff; border: 1.5px solid #e2e8f0; color: #64748b; }
    .btn-act:hover { border-color: var(--primary); color: var(--primary); transform: scale(1.1); }
    
    .log-container { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; max-height: 280px; overflow-y: auto; }
    .log-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; border-radius: 8px; margin-bottom: 6px; font-size: 10px; font-weight: 700; font-family: 'JetBrains Mono', monospace; border-left: 4px solid #cbd5e1; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .log-in { background: #ecfdf5; color: #065f46; border-left-color: #10b981; }
    .log-out { background: #fef2f2; color: #991b1b; border-left-color: #ef4444; }
    .log-ret { background: #f0f9ff; color: #075985; border-left-color: #0ea5e9; }
</style>

<div class="container-fluid mt-3 animate__animated animate__fadeIn">
    {{-- TOP BAR --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px; overflow: hidden;">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-calendar-check text-primary mr-3 fa-lg"></i>
                <h6 class="mb-0 font-weight-bold text-uppercase" style="letter-spacing: 1px;">
                    SHIFT OPERATIONAL: <span class="text-primary">{{ \Carbon\Carbon::parse($startDate)->format('l, d F Y') }}</span>
                </h6>
            </div>
            <div class="bg-light px-3 py-1 rounded border">
                <small class="font-weight-bold text-muted">L-TIME: {{ date('H.i') }}</small>
            </div>
        </div>
    </div>

    {{-- Title & Actions --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h4 class="font-weight-extrabold m-0" style="letter-spacing: -1px;">RM_HUB <span class="text-primary" style="font-family: 'Orbitron'; font-size: 14px;">v2.0 rill</span></h4>
            <p class="text-muted small font-weight-bold mb-0 uppercase">Raw Material Inventory Flow Ledger</p>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('rm.log_print') }}" class="btn-historical mr-3">
                <i class="fas fa-history mr-2"></i> Historical Registry
            </a>
            <button class="btn btn-primary shadow-lg px-4 font-weight-bold rounded-pill mr-2" data-toggle="modal" data-target="#modalTambahRM" style="height: 45px;">REGISTER_UNIT</button>
            <button class="btn btn-dark shadow px-4 font-weight-bold rounded-pill" data-toggle="modal" data-target="#modalMasterSpec" style="height: 45px;">SPEC_REGISTRY</button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card-modern no-print p-3 mb-4 shadow-sm">
        <form action="{{ route('rm.store') }}" method="GET" id="autoFilterForm" class="row align-items-end">
            <div class="col-md-3"><label class="small font-weight-bold text-primary mb-2">ENTITY_ID</label>
                <select name="customer" class="form-control rounded-xl border-0 bg-light font-weight-bold">
                    <option value="">-- ALL CLIENTS --</option>
                    @foreach($availableCustomers as $c) <option value="{{ trim($c->code) }}" {{ $customer == trim($c->code) ? 'selected' : '' }}>{{ $c->name }}</option> @endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="small font-weight-bold text-primary mb-2">SEARCH_ALIAS</label><input type="text" name="alias" id="searchAlias" class="form-control rounded-xl border-0 bg-light font-weight-bold" placeholder="Search..." value="{{ request('alias') }}"></div>
            <div class="col-md-2"><label class="small font-weight-bold text-primary mb-2">START_DATE</label><input type="date" name="start_date" class="form-control rounded-xl border-0 bg-light font-weight-bold" value="{{ $startDate }}"></div>
            <div class="col-md-2"><label class="small font-weight-bold text-primary mb-2">END_DATE</label><input type="date" name="end_date" class="form-control rounded-xl border-0 bg-light font-weight-bold" value="{{ $endDate }}"></div>
            <div class="col-md-2 d-flex">
                <button type="submit" class="btn btn-primary btn-block shadow font-weight-bold rounded-xl" style="height: 42px;"><i class="fas fa-sync-alt mr-2"></i> SYNC</button>
            </div>
        </form>
    </div>

    {{-- Main Ledger Table rill --}}
    <div class="card-modern shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-left pl-4" style="width: 25%;">Identification (Alias & Spec)</th>
                        <th colspan="4" class="header-mutation">Inventory Mutation Ledger (PCS)</th>
                        <th rowspan="2" style="width: 12%;">Live Stock</th>
                        <th rowspan="2" style="width: 8%;">Run</th>
                        <th rowspan="2" class="no-print" style="width: 5%;">ACT</th>
                    </tr>
                    <tr>
                        <th class="col-init" style="width: 10%;">Initial</th>
                        <th class="col-in-s" style="width: 10%;">In (S)</th>
                        <th class="col-in-r" style="width: 10%;">In (R)</th>
                        <th class="col-out" style="width: 10%;">Out (Prod)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedMaterials as $group)
                    @php $slug = Str::slug($group->group_key); @endphp
                    <tr class="rm-row-header" data-toggle="collapse" data-target="#det-{{ $slug }}">
                        <td class="pl-4 py-4 text-left">
                            <div class="font-weight-extrabold text-primary" style="font-size: 13px;">{{ $group->alias_code ?? $group->group_key }}</div>
                            <div class="small text-muted font-weight-bold" style="font-size: 9px;">SPEC: {{ $group->spec }} | DIM: {{ $group->size }}</div>
                        </td>
                        <td class="col-init">{{ number_format($group->total_init) }}</td>
                        <td class="col-in-s">+{{ number_format($group->total_in_s) }}</td>
                        <td class="col-in-r">+{{ number_format($group->total_in_r) }}</td>
                        <td class="col-out">-{{ number_format($group->total_out) }}</td>
                        <td class="col-live text-primary">{{ number_format($group->total_live) }}</td>
                        <td>
                            <span class="badge badge-light border font-weight-bold" style="border-radius: 8px;">
                                {{ number_format($group->total_live / ($group->std_qty_batch ?? 300), 1) }}x
                            </span>
                        </td>
                        <td class="no-print"><i class="fas fa-chevron-down text-muted small"></i></td>
                    </tr>
                    
                    <tr id="det-{{ $slug }}" class="collapse bg-light">
                        <td colspan="8" class="p-4">
                            <div class="row">
                                <div class="col-md-7">
                                    @foreach($group->details as $p)
                                    @php 
                                        $partsForThisCoil = DB::table('rm_stocks')->where('coil_id', $p->coil_id)->where('customer', $p->customer)->get();
                                        $jsonParts = $partsForThisCoil->map(fn($it) => ['no' => $it->material_code, 'name' => $it->material_name]);
                                    @endphp
                                    <div class="card shadow-sm border-0 mb-3 rounded-lg overflow-hidden">
                                        <div class="card-body p-3 bg-white">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <span class="badge-coil" onclick="showUnitProfile({
                                                        coil: '{{ $p->coil_id }}', spec: '{{ $p->spec }}', 
                                                        size: '{{ $p->size }}', qty: '{{ number_format($p->stock_pcs) }}', 
                                                        target_batch: '{{ $group->std_qty_batch ?? 300 }}',
                                                        date: '{{ date('d/m/Y', strtotime($p->created_at)) }}', 
                                                        parts: {!! htmlspecialchars($jsonParts->toJson(), ENT_QUOTES, 'UTF-8') !!} 
                                                    })">{{ $p->coil_id }}</span>
                                                    <small class="d-block text-muted mt-1 font-weight-bold" style="font-size: 9px;">REG_DATE: {{ date('d/m/Y', strtotime($p->created_at)) }}</small>
                                                </div>
                                                <div class="text-right">
                                                    <div class="small font-weight-bold text-muted">LIVE_STOCK</div>
                                                    <div class="h6 font-weight-bold text-primary mb-0" style="font-family: 'Orbitron';">{{ number_format($p->stock_pcs) }}</div>
                                                </div>
                                            </div>

                                            <div class="bg-light p-2 rounded border mb-2">
                                                <small class="font-weight-bold text-muted d-block mb-1" style="font-size: 8px;">MAPPED COMPONENTS (PARTS):</small>
                                                <div style="max-height: 120px; overflow-y: auto;">
                                                    @foreach($partsForThisCoil as $it)
                                                        <div class="comp-row mb-1 p-1 bg-white rounded border">
                                                            <div class="font-weight-bold text-dark flex-grow-1">• {{ $it->material_code }} 
                                                                <span class="text-muted small" style="font-weight: 500;">({{ $it->material_name }})</span>
                                                            </div>
                                                            <form action="{{ route('rm.remove_part_from_unit', $it->id) }}" method="POST" onsubmit="return confirm('Remove mapping?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="border-0 bg-transparent p-0"><i class="fas fa-times-circle text-danger"></i></button>
                                                            </form>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="btn-group">
                                                    <button class="btn btn-act btn-sm mr-1" onclick="openAssignPart('{{ $p->id }}', '{{ $p->customer }}')"><i class="fas fa-plus text-primary"></i></button>
                                                    <button class="btn btn-act btn-sm mr-1" onclick="openEditUnit('{{ $p->id }}', '{{ $p->coil_id }}', '{{ $p->stock_pcs }}')"><i class="fas fa-edit text-warning"></i></button>
                                                    <form action="{{ route('rm.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Delete Unit?')">@csrf @method('DELETE')<button type="submit" class="btn btn-act btn-sm"><i class="fas fa-trash"></i></button></form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="col-md-5">
                                    <div class="card border-0 shadow-sm rounded-lg h-100">
                                        <div class="card-body p-3">
                                            <h6 class="font-weight-bold text-dark mb-3" style="font-size: 11px;"><i class="fas fa-stream mr-2 text-primary"></i>ACTIVITY FEED:</h6>
                                            <div class="log-container">
                                                @forelse($group->combined_logs as $log)
                                                    @php 
                                                        $isOut = isset($log->pcs_used); 
                                                        $isRet = !$isOut && ($log->source == 'return');
                                                    @endphp
                                                    <div class="log-item {{ $isOut ? 'log-out' : ($isRet ? 'log-ret' : 'log-in') }}">
                                                        <div style="flex: 1;">
                                                            <div class="d-flex justify-content-between">
                                                                <span>{{ $isOut ? 'OUT' : ($isRet ? 'RET' : 'SUP') }}: <b class="text-dark">{{ $log->no_produksi ?? ($log->po_identitas ?? 'MANUAL') }}</b></span>
                                                                <small>{{ date('d/m H:i', strtotime($log->created_at)) }}</small>
                                                            </div>
                                                        </div>
                                                        <span class="ml-3 font-weight-extrabold">{{ $isOut ? '-' : '+' }}{{ number_format($isOut ? $log->pcs_used : $log->pcs_in) }}</span>
                                                    </div>
                                                @empty
                                                    <div class="text-center py-5 text-muted small italic">No activity recorded.</div>
                                                @endforelse
                                            </div>
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

{{-- MODALS & SCRIPTS (TETAP UTUH TANPA PERUBAHAN RILL) --}}
{{-- MODAL UNIT PROFILE --}}
<div class="modal fade" id="modalUnitProfile" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 12px; overflow:hidden; background: #f3f4f6;">
            <div class="modal-body p-4">
                <div class="label-container shadow-lg" style="background:#fff; padding:20px; border-radius:10px; border-top: 5px solid var(--primary);">
                    <div class="text-center font-weight-bold mb-3 text-muted small">PT. ASALTA MANDIRI AGUNG</div>
                    <div class="row text-center">
                        <div class="col-6 mb-2"><small class="text-muted d-block font-weight-bold uppercase">COIL_NO:</small><b id="v_coil" class="text-primary h6">--</b></div>
                        <div class="col-6 mb-2"><small class="text-muted d-block font-weight-bold uppercase">SPEC:</small><b id="v_spec" class="h6">--</b></div>
                        <div class="col-6 mb-2"><small class="text-muted d-block font-weight-bold uppercase">SIZE:</small><b id="v_size" class="h6">--</b></div>
                        <div class="col-6 mb-2"><small class="text-muted d-block font-weight-bold uppercase">TARGET_CYC:</small><b id="v_target" class="h6">--</b></div>
                    </div>
                    <div class="bg-light p-3 rounded mt-2">
                        <small class="font-weight-extrabold d-block mb-1 uppercase" style="font-size: 10px;">Mapped Components:</small>
                        <div id="v_parts_list" class="d-flex flex-column gap-1"></div>
                    </div>
                    <div class="mt-3 text-center small text-muted font-weight-bold">REGISTRY_DATE: <span id="v_date">--</span></div>
                </div>
                <button class="btn btn-dark btn-block mt-4 py-3 font-weight-bold rounded-pill shadow" data-dismiss="modal">CLOSE IDENTITY</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH RM --}}
<div class="modal fade" id="modalTambahRM" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:24px;">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="mb-0 font-weight-bold uppercase"><i class="fas fa-plus-circle mr-2"></i>Register_New_Batch</h6>
            </div>
            <form action="{{ route('rm.store_batch') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 border-right">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold uppercase">Client</label>
                                <select name="customer_code" id="modalFilterCustomer" class="form-control rounded-xl bg-light border-0 font-weight-bold" required>
                                    <option value="">-- CHOOSE --</option>
                                    @foreach($availableCustomers as $c) <option value="{{ trim($c->code) }}">{{ $c->name }}</option> @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold uppercase">Specification</label>
                                <select id="selectMasterSpec" class="form-control rounded-xl bg-light border-0 font-weight-bold" required disabled>
                                    <option>-- SELECT CLIENT --</option>
                                </select>
                                <input type="hidden" name="spec" id="autoSpec">
                                <input type="hidden" name="size" id="autoSize">
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold uppercase">Unit_Coil_Id</label>
                                <input type="text" name="coil_id" class="form-control rounded-xl bg-light border-0 font-weight-bold text-primary" placeholder="SAI_XXX" required>
                            </div>
                            <div class="row">
                                <div class="col-6"><label class="small font-weight-bold uppercase">Min_Stok</label><input type="number" name="min_stock" class="form-control rounded-xl bg-light border-0" value="500" required></div>
                                <div class="col-6"><label class="small font-weight-bold uppercase">Max_Stok</label><input type="number" name="max_stock" class="form-control rounded-xl bg-light border-0" value="1000" required></div>
                            </div>
                        </div>
                        <div class="col-md-6 pl-4">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold uppercase">Total Qty (PCS)</label>
                                <input type="number" name="stock_pcs" class="form-control rounded-xl bg-light border-0 font-weight-bold h5 text-success" style="height: 60px; font-size: 24px;" placeholder="0" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-primary uppercase">Std_Qty_Batch</label>
                                <input type="number" name="std_qty_batch" class="form-control border-primary rounded-xl font-weight-bold" value="300" required>
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold uppercase">Mapped_Parts</label>
                                <select name="part_nos[]" id="selectPart" class="form-control rounded-xl bg-light border-0 font-weight-bold" multiple style="height:115px;" required disabled></select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-extrabold rounded-pill shadow-lg">CONFIRM_DEPLOYMENT rill!</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT UNIT rill --}}
<div class="modal fade" id="modalEditUnit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:24px;">
            <div class="modal-header bg-warning py-3 px-4">
                <h6 class="mb-0 font-weight-bold uppercase">Edit_Unit_Profile</h6>
            </div>
            <form id="editUnitForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold uppercase ml-1">Unit_Coil_Id</label>
                        <input type="text" name="coil_id" id="ed_coil" class="form-control rounded-xl bg-light border-0 font-weight-bold text-primary" style="height: 50px;" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold uppercase ml-1">Stock_Qty (PCS)</label>
                        <input type="number" name="stock_pcs" id="ed_qty" class="form-control rounded-xl bg-light border-0 font-weight-extrabold text-dark h4 text-center" style="height: 80px;" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-warning btn-block py-3 font-weight-extrabold rounded-pill shadow">COMMIT_CHANGES rill!</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL ASSIGN PART --}}
<div class="modal fade" id="modalAssignPart" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius:20px; border:none; shadow-lg;"><div class="modal-header bg-primary text-white py-3"><h6>ASSIGN_COMPONENT</h6></div><form action="{{ route('rm.assign_part') }}" method="POST">@csrf<input type="hidden" name="rm_stock_id" id="ap_rm_id"><div class="modal-body p-4"><div class="form-group mb-0"><label class="small font-weight-bold text-muted">SELECT PART TO ADD</label><select name="part_no" id="ap_select_part" class="form-control rounded-xl font-weight-bold" style="height:50px;" required></select></div></div><div class="modal-footer border-0 p-4 pt-0"><button type="submit" class="btn btn-primary btn-block py-2 font-weight-bold rounded-pill">MAP_COMPONENT</button></div></form></div></div></div>

{{-- MODAL MASTER SPEC --}}
<div class="modal fade" id="modalMasterSpec" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius:20px; border:none;"><div class="modal-header bg-dark text-white py-3"><h6>SPEC_REGISTRY_MANAGER</h6></div><form action="{{ route('rm.store_master') }}" method="POST">@csrf<div class="modal-body p-4">
    <div class="form-group mb-3"><label class="small font-weight-bold">CLIENT</label><select name="customer_code" class="form-control rounded-xl">@foreach($availableCustomers as $c) <option value="{{ trim($c->code) }}">{{ $c->name }}</option> @endforeach</select></div>
    <div class="form-group mb-3"><label class="small font-weight-bold">ALIAS_CODE</label><input type="text" name="alias_code" class="form-control rounded-xl" required></div>
    <div class="form-group mb-3"><label class="small font-weight-bold">REAL_SPEC</label><input type="text" name="material_type" class="form-control rounded-xl" required></div>
    <div class="row"><div class="col-6"><label class="small font-weight-bold">THICK</label><input type="text" name="thickness" class="form-control rounded-xl"></div><div class="col-6"><label class="small font-weight-bold">SIZE</label><input type="text" name="size" class="form-control rounded-xl"></div></div>
</div><div class="modal-footer border-0 p-4 pt-0"><button type="submit" class="btn btn-dark btn-block py-3 font-weight-bold rounded-pill">COMMIT_TO_MASTER rill!</button></div></form></div></div></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function showUnitProfile(data) {
        $('#v_coil').text(data.coil); $('#v_spec').text(data.spec); $('#v_size').text(data.size); 
        $('#v_target').text(data.target_batch + ' PCS'); $('#v_date').text(data.date);
        let pList = ''; 
        data.parts.forEach(p => { 
            pList += `<div class="p-2 bg-white rounded-lg border shadow-sm" style="font-size: 11px; font-weight: 800; color: #4361ee; font-family: 'JetBrains Mono';">
                        ${p.no} <span class="text-muted ml-1" style="font-size: 9px; font-weight: 600;">(${p.name})</span>
                      </div>`; 
        });
        $('#v_parts_list').html(pList || '<small class="text-muted italic">No parts mapped</small>'); 
        $('#modalUnitProfile').modal('show');
    }
    
    function openAssignPart(rmId, customer) {
        $('#ap_rm_id').val(rmId); $.ajax({ url: "/get-parts-and-specs/" + encodeURIComponent(customer), type: "GET", success: function(res) {
            let opt = ''; $.each(res.parts, function(k, v) { 
                opt += `<option value="${v.part_no}">${v.part_no} - ${v.part_name}</option>`; 
            });
            $('#ap_select_part').html(opt); $('#modalAssignPart').modal('show');
        }});
    }
    
    function openEditUnit(id, coil, qty) { 
        $('#ed_coil').val(coil); 
        $('#ed_qty').val(qty.replace(/,/g, '')); 
        $('#editUnitForm').attr('action', '/rm/unit-update/' + id); 
        $('#modalEditUnit').modal('show'); 
    }
    
    $(document).ready(function() {
        $('#autoFilterForm select, #autoFilterForm input[type="date"]').on('change', function() { $(this).closest('form').submit(); });
        let t; $('#searchAlias').on('keyup', function () { clearTimeout(t); t = setTimeout(() => { $(this).closest('form').submit(); }, 700); });
        
        $('#modalFilterCustomer').on('change', function() { 
            var c = $(this).val(); 
            var sD = $('#selectMasterSpec'); 
            var pD = $('#selectPart');
            if(c) { 
                $.ajax({ 
                    url: "/get-parts-and-specs/" + encodeURIComponent(c), 
                    type: "GET", 
                    success: function(res) { 
                        var s = '<option value="">-- SELECT SPEC --</option>'; 
                        let uniqueSpecs = [];
                        $.each(res.specs, function(k, v) { 
                            let key = (v.material_type + v.thickness + v.size).replace(/\s+/g, '').toUpperCase();
                            if(!uniqueSpecs.includes(key)){
                                uniqueSpecs.push(key);
                                s += `<option value="${v.material_type}" data-spec="${v.material_type}" data-size="${v.thickness} X ${v.size}">
                                        [${v.alias_code}] - ${v.material_type} (${v.thickness}x${v.size})
                                      </option>`;
                            }
                        }); 
                        sD.html(s).prop('disabled', false); 
                        var p = ''; $.each(res.parts, function(k, v) { p += `<option value="${v.part_no}">${v.part_no} - ${v.part_name}</option>`; }); 
                        pD.html(p).prop('disabled', false); 
                    } 
                }); 
            } 
        });
        $('#selectMasterSpec').on('change', function() { var o = $(this).find(':selected'); $('#autoSpec').val(o.data('spec') || ''); $('#autoSize').val(o.data('size') || ''); });
    });
</script>
@endsection