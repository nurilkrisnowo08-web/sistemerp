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

    /* ✨ LEDGER TABLE STYLE */
    .table-ledger thead th { vertical-align: middle; border: none; }
    .header-mutation-label { background: #1e293b; color: #f8fafc; font-size: 9px; text-transform: uppercase; letter-spacing: 2px; font-weight: 800; padding: 10px !important; }
    .table-ledger th { background-color: #fdfdfd; text-transform: uppercase; font-size: 10px; font-weight: 800; color: #94a3b8; letter-spacing: 0.8px; padding: 15px; border-bottom: 2px solid #f1f5f9 !important; }
    
    .rm-row-header { cursor: pointer; transition: 0.2s; border-left: 5px solid transparent; }
    .rm-row-header:hover { background-color: var(--primary-soft) !important; border-left-color: var(--primary); }
    
    .col-init { background: rgba(148, 163, 184, 0.08); color: #475569; font-family: 'JetBrains Mono'; font-weight: 700; }
    .col-in-s { background: rgba(16, 185, 129, 0.08); color: #10b981; font-family: 'JetBrains Mono'; font-weight: 800; }
    .col-in-r { background: rgba(6, 182, 212, 0.08); color: #0891b2; font-family: 'JetBrains Mono'; font-weight: 800; }
    .col-out { background: rgba(239, 68, 68, 0.08); color: #ef4444; font-family: 'JetBrains Mono'; font-weight: 800; }
    .col-live { background: rgba(67, 97, 238, 0.08); color: var(--primary); font-family: 'Orbitron'; font-weight: 900 !important; font-size: 16px; }

    .badge-coil { background: #fff; color: var(--dark); border: 2.5px solid #e2e8f0; padding: 5px 12px; border-radius: 8px; font-family: 'JetBrains Mono'; font-weight: 800; font-size: 11px; cursor: pointer; transition: 0.2s; display: inline-block; }
    .badge-coil:hover { border-color: var(--primary); color: var(--primary); transform: translateY(-2px); }
    
    .log-container { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; max-height: 280px; overflow-y: auto; }
    .log-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-radius: 10px; margin-bottom: 8px; font-size: 11px; font-weight: 700; font-family: 'JetBrains Mono'; background: #fff; border-left: 5px solid #cbd5e1; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .log-in { border-left-color: #10b981; color: #065f46; }
    .log-out { border-left-color: #ef4444; color: #991b1b; }
    .log-ret { border-left-color: #0ea5e9; color: #075985; }

    .comp-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px; padding: 8px 12px; border-radius: 10px; background: #fff; border: 1px solid #eef2f6; font-size: 11px; font-weight: 700; }
</style>

<div class="container-fluid mt-3 animate__animated animate__fadeIn">
    @if(session('success')) <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius:15px;"><b>✅ SUCCESS:</b> {{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius:15px;"><b>⚠️ ERROR:</b> {{ session('error') }}</div> @endif

    {{-- TOP BAR --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h3 class="font-weight-extrabold m-0">RM_HUB <span class="text-primary" style="font-family: 'Orbitron'; font-size: 14px;">v2.0</span></h3>
            <p class="text-muted small font-weight-bold mb-0">Raw Material Mutation Ledger System</p>
        </div>
        <div class="d-flex">
            <a href="{{ route('rm.log_print') }}" class="btn-historical mr-3"><i class="fas fa-history mr-2"></i> RECAP_HISTORY</a>
            <button class="btn btn-primary shadow-sm px-4 font-weight-bold rounded-pill mr-2" data-toggle="modal" data-target="#modalTambahRM">REGISTER_COIL</button>
            <button class="btn btn-dark shadow-sm px-4 font-weight-bold rounded-pill" data-toggle="modal" data-target="#modalMasterSpec">SPEC_DB</button>
        </div>
    </div>

    {{-- FILTER PANEL --}}
    <div class="card-modern no-print p-4 mb-4 border-0 shadow-sm">
        <form action="{{ route('rm.store') }}" method="GET" id="autoFilterForm" class="row align-items-end">
            <div class="col-md-3"><label class="small font-weight-bold text-primary mb-1">CLIENT ENTITY</label>
                <select name="customer" class="form-control rounded-lg font-weight-bold">
                    <option value="">-- ALL CLIENTS --</option>
                    @foreach($availableCustomers as $c) <option value="{{ trim($c->code) }}" {{ $customer == trim($c->code) ? 'selected' : '' }}>{{ $c->name }}</option> @endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="small font-weight-bold text-primary mb-1">ALIAS SEARCH</label><input type="text" name="alias" class="form-control rounded-lg" value="{{ request('alias') }}"></div>
            <div class="col-md-2"><label class="small font-weight-bold text-primary mb-1">START PERIOD</label><input type="date" name="start_date" class="form-control rounded-lg" value="{{ $startDate }}"></div>
            <div class="col-md-2"><label class="small font-weight-bold text-primary mb-1">END PERIOD</label><input type="date" name="end_date" class="form-control rounded-lg" value="{{ $endDate }}"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary btn-block shadow-sm font-weight-bold" style="height: 45px; border-radius: 12px;">REFRESH</button></div>
        </form>
    </div>

    {{-- MAIN TABLE --}}
    <div class="card-modern border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-left pl-5">Material Identification</th>
                        <th colspan="4" class="header-mutation-label">Inventory Ledger (PCS)</th>
                        <th rowspan="2">Live Stock</th>
                        <th rowspan="2">Status</th>
                        <th rowspan="2" class="no-print">ACT</th>
                    </tr>
                    <tr>
                        <th class="col-init">STOK AWAL</th>
                        <th class="col-in-s">IN (SUP)</th>
                        <th class="col-in-r">IN (RET)</th>
                        <th class="col-out">OUT (PROD)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedMaterials as $group)
                    @php $slug = Str::slug($group->group_key); @endphp
                    <tr class="rm-row-header" data-toggle="collapse" data-target="#det-{{ $slug }}">
                        <td class="pl-5 py-4 text-left">
                            <div class="font-weight-black text-primary" style="font-size: 14px;">{{ $group->alias_code ?? $group->group_key }}</div>
                            <small class="text-muted font-weight-bold" style="font-size: 9px;">{{ $group->spec }} | {{ $group->size }}</small>
                        </td>
                        <td class="col-init">{{ number_format($group->total_init) }}</td>
                        <td class="col-in-s">+{{ number_format($group->total_in_s) }}</td>
                        <td class="col-in-r">+{{ number_format($group->total_in_r) }}</td>
                        <td class="col-out">-{{ number_format($group->total_out) }}</td>
                        <td class="col-live">{{ number_format($group->total_live) }}</td>
                        <td>
                            @php $run = $group->total_live / ($group->std_qty_batch ?? 300); @endphp
                            <span class="badge {{ $run < 1 ? 'badge-danger' : 'badge-light border' }} px-3 py-2 font-weight-bold">{{ number_format($run, 1) }}x RUN</span>
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
                                    <div class="card shadow-sm border-0 mb-3 rounded-xl">
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
                                                </div>
                                                <div class="text-right">
                                                    <small class="font-weight-black text-muted uppercase">Unit Saldo</small>
                                                    <h4 class="font-weight-black text-primary mb-0">{{ number_format($p->stock_pcs) }}</h4>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end">
                                                <button class="btn btn-outline-primary btn-sm rounded-pill px-3 mr-2" onclick="openAssignPart('{{ $p->id }}', '{{ $p->customer }}')">Add Part</button>
                                                <button class="btn btn-outline-warning btn-sm rounded-pill px-3 mr-2" onclick="openEditUnit('{{ $p->id }}', '{{ $p->coil_id }}', '{{ $p->stock_pcs }}')">Adjust</button>
                                                <form action="{{ route('rm.destroy', $p->id) }}" method="POST">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">Delete</button></form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="col-md-5">
                                    <div class="log-container">
                                        @foreach($group->combined_logs as $log)
                                            <div class="log-item {{ isset($log->pcs_used) ? 'log-out' : ($log->source == 'return' ? 'log-ret' : 'log-in') }}">
                                                <span><b>{{ isset($log->pcs_used) ? 'OUT' : 'IN' }}</b>: {{ $log->no_produksi ?? 'MANUAL' }}</span>
                                                <span>{{ isset($log->pcs_used) ? '-' . $log->pcs_used : '+' . $log->pcs_in }}</span>
                                            </div>
                                        @endforeach
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

{{-- --- SEMUA MODAL SAYA SATUKAN DI SINI AGAR TIDAK ERROR NOT FOUND --- --}}

<div class="modal fade" id="modalUnitProfile" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-2xl border-0" style="border-radius: 30px;">
            <div class="modal-body p-4 text-center">
                <h5 class="font-weight-black text-primary mb-4">UNIT_PROFILE</h5>
                <div class="row text-left mb-4">
                    <div class="col-6 mb-2 small font-weight-bold">COIL_ID: <span id="v_coil" class="text-dark">--</span></div>
                    <div class="col-6 mb-2 small font-weight-bold">SPEC: <span id="v_spec" class="text-dark">--</span></div>
                    <div class="col-6 mb-2 small font-weight-bold">SIZE: <span id="v_size" class="text-dark">--</span></div>
                    <div class="col-6 mb-2 small font-weight-bold">DATE: <span id="v_date" class="text-dark">--</span></div>
                </div>
                <div id="v_parts_list" class="text-left bg-light p-3 rounded-lg border"></div>
                <button class="btn btn-dark btn-block mt-4 rounded-pill py-2" data-dismiss="modal">CLOSE</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahRM" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:20px;">
            <div class="modal-header bg-primary text-white"><h6 class="mb-0 font-weight-bold">REGISTER_NEW_COIL</h6></div>
            <form action="{{ route('rm.store_batch') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="small font-weight-bold">CLIENT</label>
                            <select name="customer_code" id="modalFilterCustomer" class="form-control mb-3" required>
                                <option value="">-- SELECT CLIENT --</option>
                                @foreach($availableCustomers as $c) <option value="{{ trim($c->code) }}">{{ $c->name }}</option> @endforeach
                            </select>
                            <label class="small font-weight-bold">COIL_ID</label><input type="text" name="coil_id" class="form-control mb-3" required>
                            <label class="small font-weight-bold">SPEC (MASTER)</label>
                            <select id="selectMasterSpec" class="form-control" disabled required></select>
                            <input type="hidden" name="spec" id="autoSpec"><input type="hidden" name="size" id="autoSize">
                        </div>
                        <div class="col-md-6">
                            <label class="small font-weight-bold">QTY (PCS)</label><input type="number" name="stock_pcs" class="form-control mb-3" required>
                            <label class="small font-weight-bold">MIN STOK</label><input type="number" name="min_stock" class="form-control mb-3" value="500">
                            <label class="small font-weight-bold">MAX STOK</label><input type="number" name="max_stock" class="form-control mb-3" value="1000">
                            <label class="small font-weight-bold">MAPPED PARTS</label>
                            <select name="part_nos[]" id="selectPart" class="form-control" multiple style="height:100px;" disabled required></select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0"><button type="submit" class="btn btn-primary btn-block rounded-pill">CONFIRM REGISTRATION</button></div>
            </form>
        </div>
    </div>
</div>

{{-- SCRIPT TETAP DI BAWAH --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function showUnitProfile(data) {
        $('#v_coil').text(data.coil); $('#v_spec').text(data.spec); $('#v_size').text(data.size); $('#v_date').text(data.date);
        let pList = '<small class="font-weight-black d-block mb-2 text-muted uppercase">Mapped Production Parts:</small>'; 
        data.parts.forEach(p => { pList += `<div class="font-weight-bold text-primary">• ${p.no} <small class="text-muted">(${p.name})</small></div>`; });
        $('#v_parts_list').html(pList); 
        $('#modalUnitProfile').modal('show');
    }

    $(document).ready(function() {
        $('#modalFilterCustomer').on('change', function() { 
            var c = $(this).val(); 
            if(c) { 
                $.ajax({ url: "/get-parts-and-specs/" + encodeURIComponent(c), type: "GET", success: function(res) { 
                    var s = '<option value="">-- SELECT SPEC --</option>'; 
                    $.each(res.specs, function(k, v) { s += `<option value="${v.material_type}" data-spec="${v.material_type}" data-size="${v.thickness} X ${v.size}">[${v.alias_code}] ${v.material_type}</option>`; }); 
                    $('#selectMasterSpec').html(s).prop('disabled', false); 
                    var p = ''; $.each(res.parts, function(k, v) { p += `<option value="${v.part_no}">${v.part_no}</option>`; }); 
                    $('#selectPart').html(p).prop('disabled', false); 
                }}); 
            } 
        });
        $('#selectMasterSpec').on('change', function() { var o = $(this).find(':selected'); $('#autoSpec').val(o.data('spec')); $('#autoSize').val(o.data('size')); });
    });
</script>
@endsection