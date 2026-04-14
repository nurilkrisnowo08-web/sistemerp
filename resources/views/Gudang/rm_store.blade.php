@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">

<style>
    :root { 
        --primary: #4361ee; 
        --primary-soft: #f0f3ff; 
        --dark-slate: #0f172a; 
        --slate-bg: #f8fafc; 
        --brand-emerald: #10b981;
        --brand-rose: #ef4444;
    }

    body { background-color: var(--slate-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark-slate); }

    /* Premium Header & Branding */
    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(135deg, var(--primary), #7209b7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    /* Card & Table Styling */
    .card-premium { border: none; border-radius: 24px; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.02); overflow: hidden; border: 1px solid rgba(226, 232, 240, 0.8); }
    
    .table thead th { 
        background: #fdfdfd; color: #94a3b8; font-weight: 800; 
        text-transform: uppercase; font-size: 10px; letter-spacing: 1.5px; 
        padding: 20px 15px; border-bottom: 2px solid #f1f5f9;
    }

    .rm-row-header { cursor: pointer; transition: all 0.2s; }
    .rm-row-header:hover { background-color: var(--primary-soft) !important; transform: scale(1.002); }

    /* Components Styling */
    .badge-coil { 
        background: #fff; color: var(--dark-slate); border: 2px solid #e2e8f0; 
        padding: 6px 14px; border-radius: 12px; font-family: 'JetBrains Mono'; 
        font-weight: 800; font-size: 12px; transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
    .badge-coil:hover { border-color: var(--primary); color: var(--primary); transform: translateY(-2px); box-shadow: 0 8px 15px rgba(67, 97, 238, 0.15); }

    .qty-display { font-family: 'Orbitron'; font-weight: 800; font-size: 16px; color: var(--primary); }

    /* Activity Feed Logs */
    .log-container { background: #fcfcfd; border-radius: 18px; padding: 15px; border: 1px solid #f1f5f9; }
    .log-item { 
        display: flex; justify-content: space-between; align-items: center; 
        padding: 10px 15px; border-radius: 12px; margin-bottom: 8px; 
        font-size: 11px; font-weight: 700; font-family: 'JetBrains Mono';
        background: #fff; border: 1px solid #f1f5f9; box-shadow: 0 2px 5px rgba(0,0,0,0.01);
    }
    .log-in { border-left: 5px solid var(--brand-emerald); color: #065f46; }
    .log-out { border-left: 5px solid var(--brand-rose); color: #991b1b; }
    .log-ret { border-left: 5px solid #0ea5e9; color: #075985; }

    /* Interaction Elements */
    .btn-action-hub { 
        width: 36px; height: 36px; border-radius: 10px; display: inline-flex; 
        align-items: center; justify-content: center; border: 1.5px solid #f1f5f9; 
        background: #fff; transition: 0.2s; color: #64748b;
    }
    .btn-action-hub:hover { transform: scale(1.1); border-color: var(--primary); color: var(--primary); }
</style>

<div class="container-fluid mt-3 px-4 animate__animated animate__fadeIn">
    
    {{-- System Notifications --}}
    @if(session('success')) <div class="alert alert-success border-0 shadow-lg mb-4 animate__animated animate__backInRight" style="border-radius:16px; background: var(--brand-emerald); color: white;"><b><i class="fas fa-check-circle mr-2"></i> SYSTEM SUCCESS:</b> {{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger border-0 shadow-lg mb-4 animate__animated animate__shakeX" style="border-radius:16px; background: var(--brand-rose); color: white;"><b><i class="fas fa-exclamation-triangle mr-2"></i> SYSTEM ERROR:</b> {{ session('error') }}</div> @endif

    <div class="row align-items-center mb-5">
        <div class="col-md-7">
            <h1 class="heading-hub mb-1">RM_HUB <span style="-webkit-text-fill-color: var(--dark-slate);">v2.0</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0"><i class="fas fa-microchip text-primary mr-2"></i> Raw Material Lifecycle Management rill</p>
        </div>
        <div class="col-md-5 text-right no-print">
            <a href="{{ route('rm.log_print') }}" class="btn btn-light rounded-pill px-4 border font-weight-extrabold mr-2 shadow-sm">
                <i class="fas fa-history mr-2 text-muted"></i> Registry Archive
            </a>
            <button class="btn btn-primary rounded-pill px-4 font-weight-extrabold shadow-lg" style="background: var(--primary); border:none;" data-toggle="modal" data-target="#modalTambahRM">
                <i class="fas fa-plus-circle mr-2"></i> NEW BATCH
            </button>
        </div>
    </div>

    <div class="card-premium no-print p-4 mb-4 border-0">
        <form action="{{ route('rm.store') }}" method="GET" id="autoFilterForm" class="row align-items-end">
            <div class="col-md-3">
                <label class="x-small font-weight-extrabold text-muted text-uppercase mb-2 ml-1">Client Entity</label>
                <select name="customer" class="form-control border-0 bg-light rounded-xl font-weight-bold" style="height: 45px;">
                    <option value="">-- ALL CLIENTS --</option>
                    @foreach($availableCustomers as $c) <option value="{{ trim($c->code) }}" {{ $customer == trim($c->code) ? 'selected' : '' }}>{{ strtoupper($c->name) }}</option> @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="x-small font-weight-extrabold text-muted text-uppercase mb-2 ml-1">Alias Search</label>
                <input type="text" name="alias" id="searchAlias" class="form-control border-0 bg-light rounded-xl font-weight-bold" style="height: 45px;" placeholder="Type alias..." value="{{ request('alias') }}">
            </div>
            <div class="col-md-2">
                <label class="x-small font-weight-extrabold text-muted text-uppercase mb-2 ml-1">Date Start</label>
                <input type="date" name="start_date" class="form-control border-0 bg-light rounded-xl font-weight-bold" style="height: 45px;" value="{{ $startDate }}">
            </div>
            <div class="col-md-2">
                <label class="x-small font-weight-extrabold text-muted text-uppercase mb-2 ml-1">Date End</label>
                <input type="date" name="end_date" class="form-control border-0 bg-light rounded-xl font-weight-bold" style="height: 45px;" value="{{ $endDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dark btn-block rounded-xl font-weight-extrabold shadow-sm" style="height: 45px;"><i class="fas fa-sync mr-2"></i> REFRESH</button>
            </div>
        </form>
    </div>

    <div class="card-premium border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr class="text-center">
                        <th class="text-left pl-4">Part Identification (Alias & Spec)</th>
                        <th>Initial</th><th>In(S)</th><th>In(R)</th><th>Out</th><th>Live Stock</th><th>Run Rate</th><th class="no-print">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedMaterials as $group)
                    @php $slug = Str::slug($group->group_key); @endphp
                    <tr class="rm-row-header" data-toggle="collapse" data-target="#det-{{ $slug }}">
                        <td class="pl-4 py-4">
                            <div class="font-weight-extrabold text-primary" style="font-size: 14px;">{{ $group->alias_code ?? $group->group_key }}</div>
                            <small class="text-muted font-weight-bold uppercase" style="font-size: 10px;">SPEC: {{ $group->spec }} | SIZE: {{ $group->size }}</small>
                        </td>
                        <td class="text-center font-weight-bold text-muted">{{ number_format($group->total_init) }}</td>
                        <td class="text-center text-success font-weight-bold">+{{ number_format($group->total_in_s) }}</td>
                        <td class="text-center text-info font-weight-bold">+{{ number_format($group->total_in_r) }}</td>
                        <td class="text-center text-danger font-weight-bold">-{{ number_format($group->total_out) }}</td>
                        <td class="text-center"><span class="h6 font-weight-extrabold text-dark" style="font-family: 'Orbitron';">{{ number_format($group->total_live) }}</span></td>
                        <td class="text-center"><span class="badge badge-light border rounded-pill px-3 font-weight-bold">{{ number_format($group->total_live / ($group->std_qty_batch ?? 300), 1) }}x</span></td>
                        <td class="text-center no-print"><i class="fas fa-chevron-down text-muted small"></i></td>
                    </tr>
                    
                    <tr id="det-{{ $slug }}" class="collapse bg-light">
                        <td colspan="8" class="p-4">
                            <div class="row">
                                <div class="col-md-7">
                                    <h6 class="font-weight-extrabold mb-3 text-uppercase" style="font-size: 11px; letter-spacing: 1px;"><i class="fas fa-layer-group mr-2 text-primary"></i>Physical Coil Distribution:</h6>
                                    @foreach($group->details as $p)
                                    @php 
                                        $partsForThisCoil = DB::table('rm_stocks')->where('coil_id', $p->coil_id)->where('customer', $p->customer)->get();
                                        $jsonParts = $partsForThisCoil->map(fn($it) => ['no' => $it->material_code, 'name' => $it->material_name]);
                                    @endphp
                                    <div class="card shadow-sm border-0 mb-3 rounded-24 overflow-hidden">
                                        <div class="card-body p-4 bg-white">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <span class="badge-coil" onclick="showUnitProfile({
                                                        coil: '{{ $p->coil_id }}', spec: '{{ $p->spec }}', 
                                                        size: '{{ $p->size }}', qty: '{{ number_format($p->stock_pcs) }}', 
                                                        target_batch: '{{ $group->std_qty_batch ?? 300 }}',
                                                        date: '{{ date('d/m/Y', strtotime($p->created_at)) }}', 
                                                        parts: {!! htmlspecialchars($jsonParts->toJson(), ENT_QUOTES, 'UTF-8') !!} 
                                                    })">{{ $p->coil_id }}</span>
                                                    <small class="d-block text-muted mt-2 font-weight-bold" style="font-size: 10px;">REGISTERED: {{ date('d M Y', strtotime($p->created_at)) }}</small>
                                                </div>
                                                <div class="text-right">
                                                    <div class="small font-weight-extrabold text-muted text-uppercase mb-1">Live Inventory</div>
                                                    <div class="qty-display" style="font-size: 22px;">{{ number_format($p->stock_pcs) }} PCS</div>
                                                </div>
                                            </div>

                                            <div class="bg-light p-3 rounded-20 border-0 mb-3">
                                                <small class="font-weight-extrabold text-muted d-block mb-2 text-uppercase" style="font-size: 9px;">Mapping Structure:</small>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($partsForThisCoil as $it)
                                                        <div class="comp-row mb-1 p-2 bg-white rounded-xl border-0 shadow-sm w-100">
                                                            <div class="font-weight-bold text-dark flex-grow-1" style="font-size: 11px;"><i class="fas fa-link mr-2 text-primary opacity-50"></i>{{ $it->material_code }} 
                                                                <span class="text-muted small" style="font-weight: 500;">({{ $it->material_name }})</span>
                                                            </div>
                                                            <form action="{{ route('rm.remove_part_from_unit', $it->id) }}" method="POST" onsubmit="return confirm('Remove mapping?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="border-0 bg-transparent p-0 ml-3"><i class="fas fa-times-circle text-danger"></i></button>
                                                            </form>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-end gap-2">
                                                <button class="btn-action-hub" title="Assign Part" onclick="openAssignPart('{{ $p->id }}', '{{ $p->customer }}')"><i class="fas fa-plus"></i></button>
                                                <button class="btn-action-hub" title="Edit Unit" onclick="openEditUnit('{{ $p->id }}', '{{ $p->coil_id }}', '{{ $p->stock_pcs }}')"><i class="fas fa-pen-nib"></i></button>
                                                <form action="{{ route('rm.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Delete Unit?')">@csrf @method('DELETE')<button type="submit" class="btn-action-hub text-danger"><i class="fas fa-trash-alt"></i></button></form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="col-md-5">
                                    <div class="card border-0 shadow-sm rounded-24 h-100">
                                        <div class="card-body p-4">
                                            <h6 class="font-weight-extrabold text-dark mb-4 text-uppercase" style="font-size: 11px; letter-spacing: 1px;"><i class="fas fa-bolt mr-2 text-warning"></i>Real-time Activity:</h6>
                                            <div class="log-container">
                                                @forelse($group->combined_logs as $log)
                                                    @php 
                                                        $isOut = isset($log->pcs_used); 
                                                        $isRet = !$isOut && ($log->source == 'return');
                                                    @endphp
                                                    <div class="log-item {{ $isOut ? 'log-out' : ($isRet ? 'log-ret' : 'log-in') }}">
                                                        <div style="flex: 1;">
                                                            <div class="d-flex justify-content-between">
                                                                <span>{{ $isOut ? 'DISPATCH' : ($isRet ? 'RETURN' : 'RECEIVE') }}: <b class="text-dark">{{ $log->no_produksi ?? ($log->po_identitas ?? 'MANUAL') }}</b></span>
                                                                <small class="opacity-75">{{ date('d/m H:i', strtotime($log->created_at)) }}</small>
                                                            </div>
                                                        </div>
                                                        <span class="ml-4 font-weight-extrabold" style="font-size: 13px;">{{ $isOut ? '-' : '+' }}{{ number_format($isOut ? $log->pcs_used : $log->pcs_in) }}</span>
                                                    </div>
                                                @empty
                                                    <div class="text-center py-5 text-muted small italic font-weight-bold">No telemetry recorded for this group rill.</div>
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

{{-- MODAL UNIT PROFILE --}}
<div class="modal fade" id="modalUnitProfile" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 32px; background: #f3f4f6;">
            <div class="modal-body p-5">
                <div class="bg-white p-4 rounded-24 shadow-sm border-top" style="border-top: 8px solid var(--primary) !important;">
                    <div class="text-center font-weight-extrabold mb-4 text-muted small">PT. ASALTA MANDIRI AGUNG</div>
                    <div class="row text-center">
                        <div class="col-6 mb-4"><small class="text-muted font-weight-bold d-block uppercase mb-1">COIL_ID</small><b id="v_coil" class="text-primary h5 font-weight-bold">--</b></div>
                        <div class="col-6 mb-4"><small class="text-muted font-weight-bold d-block uppercase mb-1">SPEC</small><b id="v_spec" class="text-dark h6">--</b></div>
                        <div class="col-6 mb-4"><small class="text-muted font-weight-bold d-block uppercase mb-1">DIMENSION</small><b id="v_size" class="text-dark">--</b></div>
                        <div class="col-6 mb-4"><small class="text-muted font-weight-bold d-block uppercase mb-1">TARGET_CYC</small><b id="v_target" class="text-dark">--</b></div>
                    </div>
                    <div class="bg-light p-4 rounded-24 mt-2">
                        <small class="font-weight-extrabold d-block mb-3 text-uppercase text-primary" style="font-size: 10px; letter-spacing: 1px;">Components Mapping:</small>
                        <div id="v_parts_list" class="d-flex flex-column gap-2"></div>
                    </div>
                    <div class="mt-4 text-center x-small font-weight-bold text-muted">REGISTRY_DATE: <span id="v_date">--</span></div>
                </div>
                <button class="btn btn-dark btn-block mt-4 py-3 font-weight-extrabold rounded-pill shadow-lg" data-dismiss="modal">CLOSE IDENTITY</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDIT UNIT: FIXED FOR updateUnit rill! --}}
<div class="modal fade" id="modalEditUnit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius:32px;">
            <div class="modal-header bg-warning py-4 px-5">
                <h5 class="modal-title font-weight-extrabold uppercase text-dark"><i class="fas fa-edit mr-3"></i> Unit Override</h5>
            </div>
            <form id="editUnitForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-5">
                    <div class="form-group mb-4">
                        <label class="small font-weight-extrabold text-muted text-uppercase ml-1">Coil Identifier</label>
                        <input type="text" name="coil_id" id="ed_coil" class="form-control border-0 bg-light rounded-xl font-weight-bold h5" style="height: 60px;" required>
                    </div>
                    <div class="form-group mb-0 text-center">
                        <label class="small font-weight-extrabold text-muted text-uppercase mb-2">Inventory Adjust (PCS)</label>
                        <input type="number" name="stock_pcs" id="ed_qty" class="form-control text-center font-weight-extrabold text-warning border-0 bg-light rounded-24" style="font-size: 32px; height: 100px;" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-warning btn-block py-3 font-weight-extrabold rounded-pill shadow-lg">COMMIT_CHANGES rill!</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- REGISTER NEW BATCH MODAL --}}
<div class="modal fade" id="modalTambahRM" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius:32px;">
            <div class="modal-header bg-primary text-white py-4 px-5">
                <h5 class="modal-title font-weight-extrabold uppercase"><i class="fas fa-plus-circle mr-3"></i> Batch Initialization</h5>
            </div>
            <form action="{{ route('rm.store_batch') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="row">
                        <div class="col-md-6 border-right pr-4">
                            <div class="form-group mb-4">
                                <label class="small font-weight-extrabold text-muted text-uppercase">Client Entity</label>
                                <select name="customer_code" id="modalFilterCustomer" class="form-control border-0 bg-light rounded-xl font-weight-bold" required style="height: 50px;">
                                    <option value="">-- CHOOSE CLIENT --</option>
                                    @foreach($availableCustomers as $c) <option value="{{ trim($c->code) }}">{{ strtoupper($c->name) }}</option> @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-4">
                                <label class="small font-weight-extrabold text-muted text-uppercase">Spec Registry</label>
                                <select id="selectMasterSpec" class="form-control border-0 bg-light rounded-xl font-weight-bold" required disabled style="height: 50px;">
                                    <option>-- SELECT CLIENT FIRST --</option>
                                </select>
                                <input type="hidden" name="spec" id="autoSpec">
                                <input type="hidden" name="size" id="autoSize">
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-extrabold text-muted text-uppercase">Unit Coil ID</label>
                                <input type="text" name="coil_id" class="form-control border-0 bg-light rounded-xl font-weight-bold text-primary" style="height: 50px;" placeholder="SAI_XXX" required>
                            </div>
                        </div>
                        <div class="col-md-6 pl-4">
                            <div class="form-group mb-4">
                                <label class="small font-weight-extrabold text-muted text-uppercase">Total Quantity (PCS)</label>
                                <input type="number" name="stock_pcs" class="form-control text-center font-weight-extrabold text-success border-0 bg-light rounded-xl" style="font-size: 24px; height: 80px;" placeholder="0" required>
                            </div>
                            <div class="form-group mb-4">
                                <label class="small font-weight-extrabold text-primary text-uppercase">Std Batch Cycle</label>
                                <input type="number" name="std_qty_batch" class="form-control border-primary bg-light rounded-xl font-weight-bold" style="height: 45px;" value="300" required>
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-extrabold text-muted text-uppercase">Map Components</label>
                                <select name="part_nos[]" id="selectPart" class="form-control border-0 bg-light rounded-xl font-weight-bold" multiple style="height:115px;" required disabled></select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-extrabold rounded-pill shadow-lg">INITIALIZE UNIT rill!</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL ASSIGN PART --}}
<div class="modal fade" id="modalAssignPart" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius:24px;">
            <div class="modal-header bg-primary text-white py-4 px-5"><h6>Map Component to Coil</h6></div>
            <form action="{{ route('rm.assign_part') }}" method="POST">
                @csrf
                <input type="hidden" name="rm_stock_id" id="ap_rm_id">
                <div class="modal-body p-5">
                    <div class="form-group mb-0 text-center">
                        <label class="small font-weight-extrabold text-muted text-uppercase mb-3 d-block">Select Part to Link</label>
                        <select name="part_no" id="ap_select_part" class="form-control border-0 bg-light rounded-xl font-weight-bold" style="height: 55px;" required></select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-extrabold rounded-pill shadow-lg">MAP COMPONENT rill!</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL MASTER SPEC --}}
<div class="modal fade" id="modalMasterSpec" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius:24px;">
            <div class="modal-header bg-dark text-white py-4 px-5"><h6>Registry Manager</h6></div>
            <form action="{{ route('rm.store_master') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="form-group mb-3"><label class="small font-weight-extrabold">CLIENT</label><select name="customer_code" class="form-control border-0 bg-light rounded-xl font-weight-bold" style="height: 45px;">@foreach($availableCustomers as $c) <option value="{{ trim($c->code) }}">{{ $c->name }}</option> @endforeach</select></div>
                    <div class="form-group mb-3"><label class="small font-weight-extrabold">ALIAS_CODE</label><input type="text" name="alias_code" class="form-control border-0 bg-light rounded-xl font-weight-bold" style="height: 45px;" required></div>
                    <div class="form-group mb-3"><label class="small font-weight-extrabold">REAL_SPEC</label><input type="text" name="material_type" class="form-control border-0 bg-light rounded-xl font-weight-bold" style="height: 45px;" required></div>
                    <div class="row"><div class="col-6"><label class="small font-weight-extrabold">THICK</label><input type="text" name="thickness" class="form-control border-0 bg-light rounded-xl font-weight-bold" style="height: 45px;"></div><div class="col-6"><label class="small font-weight-extrabold">SIZE</label><input type="text" name="size" class="form-control border-0 bg-light rounded-xl font-weight-bold" style="height: 45px;"></div></div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0"><button type="submit" class="btn btn-dark btn-block py-3 font-weight-extrabold rounded-pill shadow-lg">REGISTER SPEC rill!</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Logic for Unit Profile Modal rill
    function showUnitProfile(data) {
        $('#v_coil').text(data.coil); $('#v_spec').text(data.spec); $('#v_size').text(data.size); 
        $('#v_target').text(data.target_batch + ' PCS'); $('#v_date').text(data.date);
        let pList = ''; 
        data.parts.forEach(p => { 
            pList += `<div class="p-2 bg-white rounded-lg border-0 shadow-sm" style="font-size: 11px; font-weight: 800; font-family: 'JetBrains Mono'; color: #4361ee;">
                        ${p.no} <span class="text-muted ml-2" style="font-size: 9px; font-weight: 500;">(${p.name})</span>
                      </div>`; 
        });
        $('#v_parts_list').html(pList || '<small class="text-muted italic">No parts mapped</small>'); 
        $('#modalUnitProfile').modal('show');
    }
    
    // Open Assign Part AJAX rill
    function openAssignPart(rmId, customer) {
        $('#ap_rm_id').val(rmId); 
        $.ajax({ 
            url: "/get-parts-and-specs/" + encodeURIComponent(customer), 
            type: "GET", 
            success: function(res) {
                let opt = ''; $.each(res.parts, function(k, v) { 
                    opt += `<option value="${v.part_no}">${v.part_no} - ${v.part_name}</option>`; 
                });
                $('#ap_select_part').html(opt); 
                $('#modalAssignPart').modal('show');
            }
        });
    }
    
    // ✨ FIX: Matching URL for updateUnit rill
    function openEditUnit(id, coil, qty) { 
        $('#ed_coil').val(coil); 
        $('#ed_qty').val(qty.replace(/,/g, '')); // Clean commas from number
        $('#editUnitForm').attr('action', '/rm/unit-update/' + id); 
        $('#modalEditUnit').modal('show'); 
    }
    
    $(document).ready(function() {
        // Auto Submit Filter rill
        $('#autoFilterForm select, #autoFilterForm input[type="date"]').on('change', function() { $(this).closest('form').submit(); });
        let t; $('#searchAlias').on('keyup', function () { clearTimeout(t); t = setTimeout(() => { $(this).closest('form').submit(); }, 700); });
        
        // Cascading Dropdown for New Batch rill
        $('#modalFilterCustomer').on('change', function() { 
            var c = $(this).val(); 
            var sD = $('#selectMasterSpec'); var pD = $('#selectPart');
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
                                        [${v.alias_code}] - ${v.material_type} (${v.material_name})
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

        // Set Hidden Value for Spec rill
        $('#selectMasterSpec').on('change', function() { 
            var o = $(this).find(':selected'); 
            $('#autoSpec').val(o.data('spec') || ''); 
            $('#autoSize').val(o.data('size') || ''); 
        });
    });
</script>
@endsection