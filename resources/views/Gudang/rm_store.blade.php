@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root { --primary: #4361ee; --primary-soft: #f0f3ff; --dark: #0f172a; --slate-bg: #f8fafc; }
    body { background-color: var(--slate-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark); }
    .card-modern { border: none; border-radius: 16px; background: #ffffff; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); margin-bottom: 1.5rem; }
    
    .btn-historical { background: #4b4d5a; color: #ffffff !important; border-radius: 50px; padding: 8px 25px; font-weight: 700; border: none; transition: 0.3s; display: inline-flex; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); font-size: 13px; text-decoration: none !important; }
    .btn-historical:hover { background: #343a40; transform: translateY(-2px); }

    .table thead th { background-color: #f1f5f9; text-transform: uppercase; font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 0.8px; padding: 15px; border: none; }
    .rm-row-header { cursor: pointer; transition: background 0.2s; border-bottom: 1px solid #f1f5f9 !important; }
    .rm-row-header:hover { background-color: var(--primary-soft) !important; }
    
    .badge-coil { background: #fff; color: var(--dark); border: 2.5px solid #e2e8f0; padding: 5px 12px; border-radius: 8px; font-family: 'JetBrains Mono'; font-weight: 800; font-size: 11px; cursor: pointer; transition: 0.2s; display: inline-block; }
    .badge-coil:hover { border-color: var(--primary); color: var(--primary); transform: translateY(-2px); }
    
    .btn-act { padding: 6px 10px; border-radius: 8px; font-size: 10px; font-weight: 700; transition: 0.2s; background: #fff; border: 1.5px solid #e2e8f0; color: #64748b; }
    .btn-act:hover { border-color: var(--primary); color: var(--primary); transform: scale(1.1); }
    
    .log-container { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; max-height: 280px; overflow-y: auto; }
    .log-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; border-radius: 8px; margin-bottom: 6px; font-size: 10px; font-weight: 700; font-family: 'JetBrains Mono', monospace; border-left: 4px solid #cbd5e1; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .log-in { background: #ecfdf5; color: #065f46; border-left-color: #10b981; }
    .log-out { background: #fef2f2; color: #991b1b; border-left-color: #ef4444; }
    .log-ret { background: #f0f9ff; color: #075985; border-left-color: #0ea5e9; }

    .comp-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; padding: 6px 10px; border-radius: 8px; background: #fff; border: 1px solid #e2e8f0; transition: 0.2s; font-size: 10px; }
</style>

<div class="container-fluid mt-3">
    {{-- NOTIFIKASI SYSTEM rill --}}
    @if(session('success')) <div class="alert alert-success border-0 shadow-sm mb-4 animate__animated animate__fadeInDown" style="border-radius:12px;"><b>✅ SUCCESS:</b> {{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger border-0 shadow-sm mb-4 animate__animated animate__shakeX" style="border-radius:12px;"><b>⚠️ ERROR:</b> {{ session('error') }}</div> @endif

    {{-- TOP BAR --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-calendar-check text-primary mr-3 fa-lg"></i>
                <h6 class="mb-0 font-weight-bold text-uppercase" style="letter-spacing: 1px;">
                    SHIFT OPERATIONAL: <span class="text-primary">{{ strtoupper(date('l, d F Y')) }}</span>
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
            <h4 class="font-weight-extrabold m-0">RM_HUB <span class="text-primary text-sm">Industrial Portal v2.0 rill</span></h4>
            <p class="text-muted small font-weight-bold mb-0">Raw Material Tracking System</p>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('rm.log_print') }}" class="btn-historical mr-3">
                <i class="fas fa-history mr-2"></i> Historical Registry
            </a>
            <button class="btn btn-primary shadow-sm px-4 font-weight-bold rounded-pill mr-2" data-toggle="modal" data-target="#modalTambahRM">REGISTER_UNIT</button>
            <button class="btn btn-dark shadow-sm px-4 font-weight-bold rounded-pill" data-toggle="modal" data-target="#modalMasterSpec">SPEC_REGISTRY</button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card-modern no-print p-3 mb-4 shadow-sm border">
        <form action="{{ route('rm.store') }}" method="GET" id="autoFilterForm" class="row align-items-end">
            <div class="col-md-3"><label class="small font-weight-bold text-primary mb-1">ENTITY_ID</label>
                <select name="customer" class="form-control">
                    <option value="">-- ALL CLIENTS --</option>
                    @foreach($availableCustomers as $c) <option value="{{ trim($c->code) }}" {{ $customer == trim($c->code) ? 'selected' : '' }}>{{ $c->name }}</option> @endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="small font-weight-bold text-primary mb-1">SEARCH_ALIAS</label><input type="text" name="alias" id="searchAlias" class="form-control" placeholder="Search Alias..." value="{{ request('alias') }}"></div>
            <div class="col-md-2"><label class="small font-weight-bold text-primary mb-1">START_DATE</label><input type="date" name="start_date" class="form-control" value="{{ $startDate }}"></div>
            <div class="col-md-2"><label class="small font-weight-bold text-primary mb-1">END_DATE</label><input type="date" name="end_date" class="form-control" value="{{ $endDate }}"></div>
            <div class="col-md-2 d-flex">
                <button type="submit" class="btn btn-primary btn-block shadow-sm" style="height: 40px; border-radius: 10px;"><i class="fas fa-sync-alt"></i> REFRESH</button>
            </div>
        </form>
    </div>

    {{-- Main Table Section --}}
    <div class="card-modern overflow-hidden shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr class="text-center">
                        <th class="text-left pl-4">Identification (Alias & Spec)</th>
                        <th>Init</th><th>In(S)</th><th>In(R)</th><th>Out</th><th>Live</th><th>Run</th><th class="no-print">ACT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedMaterials as $group)
                    @php $slug = Str::slug($group->group_key); @endphp
                    <tr class="rm-row-header" data-toggle="collapse" data-target="#det-{{ $slug }}">
                        <td class="pl-4">
                            <div class="font-weight-bold text-primary" style="font-size: 13px;">{{ $group->alias_code ?? $group->group_key }}</div>
                            <div class="small text-muted font-weight-bold" style="font-size: 9px;">SPEC: {{ $group->spec }} | DIM: {{ $group->size }}</div>
                        </td>
                        <td class="text-center font-weight-bold text-muted">{{ number_format($group->total_init) }}</td>
                        <td class="text-center text-success font-weight-bold">+{{ number_format($group->total_in_s) }}</td>
                        <td class="text-center text-info font-weight-bold">+{{ number_format($group->total_in_r) }}</td>
                        <td class="text-center text-danger font-weight-bold">-{{ number_format($group->total_out) }}</td>
                        <td class="text-center"><span class="h6 font-weight-bold text-dark">{{ number_format($group->total_live) }}</span></td>
                        <td class="text-center"><span class="badge badge-light border">{{ number_format($group->total_live / ($group->std_qty_batch ?? 300), 1) }}x</span></td>
                        <td class="text-center no-print"><i class="fas fa-chevron-down text-muted small"></i></td>
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
                                                    <div class="h6 font-weight-bold text-primary mb-0">{{ number_format($p->stock_pcs) }}</div>
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

{{-- MODAL UNIT PROFILE rill --}}
<div class="modal fade" id="modalUnitProfile" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 12px; overflow:hidden; background: #f3f4f6;">
            <div class="modal-body p-4">
                <div class="label-container shadow-lg" style="background:#fff; padding:20px; border-radius:10px; border-top: 5px solid var(--primary);">
                    <div class="text-center font-weight-bold mb-3">PT. ASALTA MANDIRI AGUNG</div>
                    <div class="row">
                        <div class="col-6 mb-2"><small class="text-muted d-block">COIL_NO:</small><b id="v_coil" class="text-primary">--</b></div>
                        <div class="col-6 mb-2"><small class="text-muted d-block">SPEC:</small><b id="v_spec">--</b></div>
                        <div class="col-6 mb-2"><small class="text-muted d-block">SIZE:</small><b id="v_size">--</b></div>
                        <div class="col-6 mb-2"><small class="text-muted d-block">TARGET_BATCH:</small><b id="v_target">--</b></div>
                    </div>
                    <div class="bg-light p-3 rounded mt-2">
                        <small class="font-weight-bold d-block mb-1">PRODUCING_COMPONENTS:</small>
                        <div id="v_parts_list"></div>
                    </div>
                    <div class="mt-3 text-center small text-muted">STARTED: <span id="v_date">--</span></div>
                </div>
                <button class="btn btn-secondary btn-block mt-4 py-3 font-weight-bold rounded-pill shadow" data-dismiss="modal">CLOSE DETAILS</button>
            </div>
        </div>
    </div>
</div>

{{-- ✨ UPDATED: REGISTER NEW BATCH MODAL rill ✨ --}}
<div class="modal fade" id="modalTambahRM" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:15px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="mb-0 font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>REGISTER_NEW_BATCH</h6>
            </div>
            <form action="{{ route('rm.store_batch') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 border-right">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold">CLIENT</label>
                                <select name="customer_code" id="modalFilterCustomer" class="form-control" required>
                                    <option value="">-- CHOOSE --</option>
                                    @foreach($availableCustomers as $c) <option value="{{ trim($c->code) }}">{{ $c->name }}</option> @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold">SPECIFICATION</label>
                                <select id="selectMasterSpec" class="form-control" required disabled>
                                    <option>-- SELECT CLIENT --</option>
                                </select>
                                <input type="hidden" name="spec" id="autoSpec">
                                <input type="hidden" name="size" id="autoSize">
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold">UNIT_COIL_ID</label>
                                <input type="text" name="coil_id" class="form-control font-weight-bold text-primary" placeholder="SAI_XXX" required>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label class="small font-weight-bold">MIN_STOK (PCS)</label>
                                    <input type="number" name="min_stock" class="form-control" value="500" required>
                                </div>
                                <div class="col-6">
                                    <label class="small font-weight-bold">MAX_STOK (PCS)</label>
                                    <input type="number" name="max_stock" class="form-control" value="1000" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 pl-4">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold">TOTAL QTY (PCS)</label>
                                <input type="number" name="stock_pcs" class="form-control font-weight-bold h5 text-success" placeholder="0" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-primary">STD_QTY_BATCH (CYCLES)</label>
                                <input type="number" name="std_qty_batch" class="form-control border-primary" value="300" required>
                                <small class="text-muted" style="font-size: 8px;">*Standard target per production run</small>
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold">MAPPED_PARTS (CHOOSE MULTIPLE)</label>
                                <select name="part_nos[]" id="selectPart" class="form-control" multiple style="height:120px;" required disabled></select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold rounded-pill shadow-lg">CONFIRM_DEPLOYMENT rill!</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL LAINNYA --}}
<div class="modal fade" id="modalAssignPart" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius:15px;"><div class="modal-header bg-primary text-white py-3"><h6>ASSIGN_COMPONENT</h6></div><form action="{{ route('rm.assign_part') }}" method="POST">@csrf<input type="hidden" name="rm_stock_id" id="ap_rm_id"><div class="modal-body p-4"><div class="form-group mb-0"><label class="small font-weight-bold text-muted">SELECT PART TO ADD</label><select name="part_no" id="ap_select_part" class="form-control" required></select></div></div><div class="modal-footer border-0 p-4 pt-0"><button type="submit" class="btn btn-primary btn-block py-2 font-weight-bold rounded-pill">MAP_COMPONENT</button></div></form></div></div></div>
<div class="modal fade" id="modalEditUnit" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius:15px;"><div class="modal-header bg-warning py-3"><h6>EDIT_UNIT_PROFILE</h6></div><form id="editUnitForm" method="POST">@csrf @method('PUT')<div class="modal-body p-4"><div class="form-group mb-3"><label class="small font-weight-bold">UNIT_COIL_ID</label><input type="text" name="coil_id" id="ed_coil" class="form-control" required></div><div class="form-group mb-0"><label class="small font-weight-bold">STOCK_QTY (PCS)</label><input type="number" name="stock_pcs" id="ed_qty" class="form-control" required></div></div><div class="modal-footer border-0 p-4 pt-0"><button type="submit" class="btn btn-warning btn-block py-2 font-weight-bold rounded-pill">COMMIT_CHANGES</button></div></form></div></div></div>
<div class="modal fade" id="modalMasterSpec" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius:15px;"><div class="modal-header bg-dark text-white py-3"><h6>SPEC_REGISTRY_MANAGER</h6></div><form action="{{ route('rm.store_master') }}" method="POST">@csrf<div class="modal-body p-4">
    <div class="form-group mb-2"><label class="small font-weight-bold">CLIENT</label><select name="customer_code" class="form-control">@foreach($availableCustomers as $c) <option value="{{ trim($c->code) }}">{{ $c->name }}</option> @endforeach</select></div>
    <div class="form-group mb-2"><label class="small font-weight-bold">ALIAS_CODE</label><input type="text" name="alias_code" class="form-control" required></div>
    <div class="form-group mb-2"><label class="small font-weight-bold">REAL_SPEC</label><input type="text" name="material_type" class="form-control" required></div>
    <div class="row"><div class="col-6"><label class="small font-weight-bold">THICK</label><input type="text" name="thickness" class="form-control"></div><div class="col-6"><label class="small font-weight-bold">SIZE</label><input type="text" name="size" class="form-control"></div></div>
</div><div class="modal-footer border-0 p-4 pt-0"><button type="submit" class="btn btn-dark btn-block py-3 font-weight-bold rounded-pill">COMMIT_TO_MASTER rill!</button></div></form></div></div></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function showUnitProfile(data) {
        $('#v_coil').text(data.coil); $('#v_spec').text(data.spec); $('#v_size').text(data.size); 
        $('#v_target').text(data.target_batch + ' PCS'); $('#v_date').text(data.date);
        let pList = ''; 
        data.parts.forEach(p => { 
            // ✨ FIX: Menampilkan nama di modal profile rill
            pList += `<div style="font-size: 11px; font-weight: 800; color: #4361ee; font-family: 'JetBrains Mono';">${p.no}</div>
                      <div style="font-size: 10px; color: #64748b; margin-bottom: 8px; text-transform: uppercase;">${p.name}</div>`; 
        });
        $('#v_parts_list').html(pList || '<small class="text-muted italic">No parts mapped</small>'); 
        $('#modalUnitProfile').modal('show');
    }
    
    function openAssignPart(rmId, customer) {
        $('#ap_rm_id').val(rmId); $.ajax({ url: "/get-parts-and-specs/" + encodeURIComponent(customer), type: "GET", success: function(res) {
            let opt = ''; $.each(res.parts, function(k, v) { 
                // ✨ FIX: Menampilkan nama di modal assign part rill
                opt += `<option value="${v.part_no}">${v.part_no} - ${v.part_name}</option>`; 
            });
            $('#ap_select_part').html(opt); $('#modalAssignPart').modal('show');
        }});
    }
    
    function openEditUnit(id, coil, qty) { $('#ed_coil').val(coil); $('#ed_qty').val(qty); $('#editUnitForm').attr('action', '/rm/unit-update/' + id); $('#modalEditUnit').modal('show'); }
    
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
                        // ✨ FIX: Menampilkan nama di dropdown Spec rill
                        var s = '<option value="">-- SELECT SPEC --</option>'; 
                        let uniqueSpecs = [];
                        $.each(res.specs, function(k, v) { 
                            let key = (v.material_type + v.thickness + v.size).replace(/\s+/g, '').toUpperCase();
                            if(!uniqueSpecs.includes(key)){
                                uniqueSpecs.push(key);
                                s += `<option value="${v.material_type}" data-spec="${v.material_type}" data-size="${v.thickness} X ${v.size}">
                                        [${v.alias_code}] - ${v.material_type} (${v.material_name})
                                      </option>`;
                            }
                        }); 
                        sD.html(s).prop('disabled', false); 
                        
                        // ✨ FIX: Menampilkan nama di dropdown Part rill
                        var p = ''; 
                        $.each(res.parts, function(k, v) { 
                            p += `<option value="${v.part_no}">${v.part_no} - ${v.part_name}</option>`; 
                        }); 
                        pD.html(p).prop('disabled', false); 
                    } 
                }); 
            } 
        });
        $('#selectMasterSpec').on('change', function() { var o = $(this).find(':selected'); $('#autoSpec').val(o.data('spec') || ''); $('#autoSize').val(o.data('size') || ''); });
    });
</script>
@endsection