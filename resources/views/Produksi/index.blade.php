@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Orbitron:wght@400;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --ind-bg: #f8fafc; --ind-white: #ffffff; --ind-blue: #4361ee;
        --ind-success: #10b981; --ind-danger: #ef4444; --ind-warning: #f59e0b;
        --ind-dark: #0f172a; --ind-border: #e2e8f0; --ind-text: #334155;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--ind-bg); color: var(--ind-text); }
    .main-terminal { min-height: 100vh; position: relative; padding-bottom: 50px; }
    .command-header { background: var(--ind-white); padding: 25px 40px; border-bottom: 1px solid var(--ind-border); display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
    .hud-title { font-weight: 800; font-size: 1.5rem; letter-spacing: -1px; margin: 0; font-family: 'Orbitron', sans-serif; color: var(--ind-dark); }
    .nav-rail { padding: 15px 40px; display: flex; gap: 12px; overflow-x: auto; background: #fff; border-bottom: 1px solid var(--ind-border); }
    .tab-btn { background: var(--ind-bg); border: 1px solid var(--ind-border); padding: 10px 22px; border-radius: 12px; color: #64748b; font-weight: 700; font-size: 11px; transition: 0.3s; text-decoration: none !important; white-space: nowrap; }
    .tab-btn.active { background: var(--ind-blue); color: #fff; border-color: var(--ind-blue); box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3); }
    .table-container { background: #fff; margin: 25px 40px; border-radius: 16px; border: 1px solid var(--ind-border); overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
    .table-industrial { width: 100%; border-collapse: collapse; }
    .table-industrial thead th { background: #f1f5f9; color: #64748b; padding: 15px 25px; text-align: left; font-size: 10px; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; }
    .table-industrial td { padding: 20px 25px; border-bottom: 1px solid var(--ind-border); font-size: 13px; font-weight: 600; }
    .id-tag { font-family: 'JetBrains Mono'; color: var(--ind-blue); font-size: 12px; font-weight: 800; background: #f0f3ff; padding: 4px 8px; border-radius: 6px; }
    .badge-coil { background: #fff; color: var(--ind-dark); border: 2px solid var(--ind-border); padding: 6px 12px; border-radius: 8px; font-family: 'JetBrains Mono'; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; }
    .route-fg { background: rgba(16, 185, 129, 0.1); color: var(--ind-success); border: 1.5px solid var(--ind-success); padding: 5px 12px; border-radius: 8px; font-size: 10px; font-weight: 800; display: inline-flex; align-items: center; }
    .route-wld { background: rgba(245, 158, 11, 0.1); color: var(--ind-warning); border: 1.5px solid var(--ind-warning); padding: 5px 12px; border-radius: 8px; font-size: 10px; font-weight: 800; display: inline-flex; align-items: center; }
    .input-tactical { background: #f8fafc; border: 2px solid var(--ind-border); border-radius: 12px; padding: 12px; font-weight: 700; font-family: 'JetBrains Mono'; width: 100%; transition: 0.3s; }
    .btn-blueprint { border-radius: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; padding: 12px 25px; border: none; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; }
    .progress-lite { height: 12px; border-radius: 20px; background: #f1f5f9; overflow: hidden; margin: 15px 0; border: 1px solid var(--ind-border); }
    .progress-bar-fill { height: 100%; background: var(--ind-success); transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
    .label-box { background: #fff; border: 2px solid #1e293b; padding: 0; color: #0f172a; border-radius: 4px; overflow: hidden; font-family: 'JetBrains Mono', monospace; }
    .label-header { background: #1e293b; color: #fff; padding: 12px; text-align: center; font-weight: 800; text-transform: uppercase; font-size: 14px; letter-spacing: 2px; }
    .label-row { display: flex; border-bottom: 1px solid #1e293b; }
    .label-cell { padding: 10px; border-right: 1px solid #1e293b; flex: 1; }
    .label-title { font-size: 8px; font-weight: 800; display: block; color: #64748b; margin-bottom: 2px; }
    .label-text { font-size: 13px; font-weight: 700; color: #0f172a; }
</style>

<div class="container-fluid main-terminal anim-fade-up">

    <div class="px-4 pt-3">
        @if(session('success'))
            <div class="alert alert-success animate__animated animate__backInDown alert-dismissible fade show border-0 shadow-sm" style="border-radius:15px; border-left: 6px solid var(--ind-success) !important;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle mr-3 fa-lg"></i>
                    <div><strong class="text-uppercase">Success</strong><br><small>{{ session('success') }}</small></div>
                </div>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger animate__animated animate__shakeX border-0 shadow-sm" style="border-radius:15px; border-left: 6px solid var(--ind-danger) !important;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle mr-3 fa-lg"></i>
                    <div><strong class="text-uppercase">Error</strong><br><small>{{ session('error') }}</small></div>
                </div>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
    </div>

    <div class="command-header">
        <div><h1 class="hud-title">Terminal <span style="color: var(--ind-blue)">Line Control</span></h1></div>
        <div class="d-flex align-items-center">
            <a href="{{ route('produksi.history') }}" class="btn btn-light rounded-pill px-4 font-weight-bold btn-sm border mr-3 shadow-sm"><i class="fas fa-archive mr-2"></i> ARCHIVE</a>
            <button class="btn btn-blueprint shadow-sm" style="background: var(--ind-blue); color: #fff;" data-toggle="modal" data-target="#modalAmbilMaterial">
                <i class="fas fa-bolt mr-2"></i> START NEW BATCH
            </button>
        </div>
    </div>

    <div class="nav-rail">
        <a href="{{ route('produksi.index') }}" class="tab-btn {{ !request('customer') ? 'active' : '' }}">ALL OPERATIONS</a>
        @foreach($customers as $cust)
            <a href="{{ route('produksi.index', ['customer' => trim($cust->code)]) }}" class="tab-btn {{ request('customer') == trim($cust->code) ? 'active' : '' }}">{{ strtoupper($cust->code) }}</a>
        @endforeach
    </div>

    <div class="table-container">
        <table class="table-industrial">
            <thead>
                <tr>
                    <th>Batch ID</th>
                    <th>Identification</th>
                    <th>Destination</th>
                    <th>Production Line</th>
                    <th class="text-center">Total Jatah</th>
                    <th class="text-center">Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeProductions as $p)
                <tr>
                    <td><span class="id-tag">{{ $p->no_produksi }}</span></td>
                    <td>
                        <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ $p->material_code }}</div>
                        <div class="mt-1">
                            <span class="badge-coil btn-sm" onclick="viewDigitalLabel({
                                coil: '{{ $p->coil_id }}', spec: '{{ $p->spec }}', size: '{{ $p->size }}',
                                qty: '{{ number_format($p->total_qty_batch) }}', date: '{{ date('d/m/Y H:i', strtotime($p->created_at)) }}',
                                part: '{{ $p->material_code }}', part_name: '{{ $p->material_name }}'
                            })">
                                <i class="fas fa-fingerprint mr-1 text-primary"></i> {{ $p->coil_id ?? 'N/A' }}
                            </span>
                        </div>
                    </td>
                    <td>
                        @php $route = DB::table('parts')->where('part_no', $p->material_code)->value('next_process'); @endphp
                        @if($route == 'WELDING')
                            <span class="route-wld"><i class="fas fa-fire mr-1 text-sm"></i> WELDING</span>
                        @else
                            <span class="route-fg"><i class="fas fa-box-open mr-1 text-sm"></i> FINISHED GOOD</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="badge badge-dark p-2 mr-2" style="font-size: 9px; border-radius: 6px;">{{ strtoupper($p->shift) }}</span>
                            <span class="font-weight-bold text-primary">{{ $p->line_names }}</span>
                        </div>
                    </td>
                    <td class="text-center font-weight-bold" style="font-size: 15px;">{{ number_format($p->total_qty_batch) }} <small class="text-muted">PCS</small></td>
                    <td class="text-center">
                        <span class="badge badge-pill px-3 py-2" style="background: rgba(245, 158, 11, 0.1); color: var(--ind-warning); font-size: 10px; font-weight: 800;">
                            <i class="fas fa-spinner fa-spin mr-1"></i> {{ $p->status }}
                        </span>
                    </td>
                    <td class="text-right">
                        <button class="btn btn-blueprint btn-sm px-4 animate__animated animate__pulse animate__infinite" 
                                style="background: var(--ind-success); color: #fff; border-radius: 10px;" 
                                data-toggle="modal" data-target="#modalInputHasil{{ $p->batch_id }}">INPUT RESULT</button>
                    </td>
                </tr>
                @empty 
                <tr><td colspan="7" class="py-5 text-center text-muted italic">-- SYSTEM IDLE --</td></tr> 
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL LABEL DIGITAL --}}
<div class="modal fade" id="modalLabelDigital" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-body p-0">
                <div class="label-box shadow-2xl animate__animated animate__zoomIn">
                    <div class="label-header">COIL IDENTIFICATION TAG</div>
                    <div class="label-row">
                        <div class="label-cell"><span class="label-title">COIL_ID:</span><div class="label-text" id="v_coil">--</div></div>
                        <div class="label-cell"><span class="label-title">SPECIFICATION:</span><div class="label-text" id="v_spec">--</div></div>
                    </div>
                    <div class="label-row">
                        <div class="label-cell"><span class="label-title">DIMENSION (SIZE):</span><div class="label-text" id="v_size">--</div></div>
                        <div class="label-cell"><span class="label-title">BATCH_QUANTITY:</span><div class="label-text"><span id="v_qty">0</span> PCS</div></div>
                    </div>
                    <div class="p-4 bg-white">
                        <span class="label-title">ACTIVE_PART_PRODUCTION:</span>
                        <div class="mt-2"><b class="text-primary h5" id="v_part_no">--</b><br><span class="text-muted small" id="v_part_name">--</span></div>
                    </div>
                    <div class="p-3 bg-light text-right border-top small font-weight-bold">INITIATED: <span id="v_date">--</span></div>
                </div>
                <button class="btn btn-dark btn-block mt-4 py-3 font-weight-bold rounded-pill" data-dismiss="modal">TERMINATE VIEW</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL INPUT HASIL (START FROM ZERO) --}}
@foreach($activeProductions as $p)
<div class="modal fade" id="modalInputHasil{{ $p->batch_id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:25px; overflow: hidden;">
            <div class="scanner-line"></div>
            <div class="modal-header bg-success text-white py-4 border-0">
                <h6 class="modal-title font-weight-bold" style="font-family: 'Orbitron';">
                    <i class="fas fa-microchip mr-2"></i> FINISH BATCH: [{{ $p->no_produksi }}]
                </h6>
            </div>
            <form action="{{ route('produksi.update_result', $p->batch_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-5">
                    @php $routeTarget = DB::table('parts')->where('part_no', $p->material_code)->value('next_process'); @endphp
                    <div class="route-card" style="background: {{ $routeTarget == 'WELDING' ? '#fffbeb' : '#ecfdf5' }}; color: {{ $routeTarget == 'WELDING' ? '#92400e' : '#065f46' }}; border: 2px solid {{ $routeTarget == 'WELDING' ? '#f59e0b' : '#10b981' }}; border-radius: 15px; padding: 15px; display: flex; align-items: center; margin-bottom: 20px;">
                        <div class="mr-3"><i class="fas {{ $routeTarget == 'WELDING' ? 'fa-fire-alt' : 'fa-check-double' }} fa-2x"></i></div>
                        <div>
                            <small class="font-weight-bold uppercase opacity-75">Target Location:</small>
                            <h5 class="mb-0 font-weight-bold">{{ $routeTarget == 'WELDING' ? 'WELDING AREA (WIP)' : 'FINISHED GOODS (STOCK)' }}</h5>
                        </div>
                    </div>

                    <div id="police_msg_{{ $p->batch_id }}" class="alert alert-warning border-0 font-weight-bold text-center py-3 mb-4" style="border-radius: 12px; font-size: 11px;">👮 STATUS: STANDBY FOR SYNC...</div>

                    <div class="p-4 mb-4 rounded-xl bg-light shadow-inner border">
                        <div class="row text-center">
                            <div class="col-4 border-right"><small class="text-muted font-weight-bold">TOTAL TARGET</small><h3 class="mb-0 font-weight-bold target-val" data-id="{{ $p->batch_id }}">{{ $p->total_qty_batch }}</h3></div>
                            <div class="col-4 border-right"><small class="text-muted font-weight-bold">ACCOUNTED</small><h3 class="mb-0 text-primary font-weight-bold" id="current_{{ $p->batch_id }}">0</h3></div>
                            <div class="col-4"><small class="text-muted font-weight-bold">GAP</small><h3 class="mb-0 text-danger font-weight-bold" id="gap_{{ $p->batch_id }}">{{ $p->total_qty_batch }}</h3></div>
                        </div>
                        <div class="progress-lite mt-4"><div class="progress-bar-fill" id="bar_{{ $p->batch_id }}" style="width: 0%"></div></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 pr-md-4 border-right">
                            <div class="form-group">
                                <label class="small font-weight-bold text-success">TOTAL OK QUANTITY</label>
                                {{-- ✨ PAK RIL: SAYA SET VALUE 0 BIAR ORANG PRODUKSI ISI SENDIRI ✨ --}}
                                <input type="number" name="qty_hasil_ok" id="ok_{{ $p->batch_id }}" data-id="{{ $p->batch_id }}" class="input-tactical calc-input mb-4" required value="0">
                            </div>
                            <div class="form-group"><label class="small font-weight-bold text-danger">RETURN (SISA MATERIAL)</label><input type="number" name="qty_return_warehouse" id="return_{{ $p->batch_id }}" data-id="{{ $p->batch_id }}" class="input-tactical calc-input border-danger" value="0"></div>
                        </div>
                        <div class="col-md-6 pl-md-4">
                            <div class="form-group"><label class="small font-weight-bold text-warning">NG MATERIAL</label><input type="number" name="qty_ng_material" id="ng_mat_{{ $p->batch_id }}" data-id="{{ $p->batch_id }}" class="input-tactical calc-input mb-4" value="0"></div>
                            <div class="form-group"><label class="small font-weight-bold text-warning">NG PROCESS</label><input type="number" name="qty_ng_process" id="ng_proc_{{ $p->batch_id }}" data-id="{{ $p->batch_id }}" class="input-tactical calc-input" value="0"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 bg-light">
                    <button type="submit" id="btn_{{ $p->batch_id }}" class="btn btn-blueprint btn-block py-3 shadow-lg" style="background: var(--ind-success); color: #fff;" disabled>COMMIT & TRANSMIT DATA</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- MODAL INITIALIZE BATCH --}}
<div class="modal fade" id="modalAmbilMaterial" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius:24px;">
            <div class="modal-header bg-primary text-white py-4"><h6 class="modal-title font-weight-bold uppercase" style="font-family: 'Orbitron';">Initialize Batch</h6></div>
            <form action="{{ route('produksi.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <label class="small font-weight-bold mb-1">01. BATCH ID</label>
                    <input type="text" name="no_produksi" class="input-tactical bg-light mb-3" value="PROD-{{ date('Ymd-His') }}" readonly>
                    
                    <div class="row">
                        <div class="col-6"><label class="small font-weight-bold">02. SHIFT</label><select name="shift" class="input-tactical mb-3" required><option value="" disabled selected>-- SELECT --</option><option value="Pagi">PAGI (S1)</option><option value="Malam">MALAM (S2)</option></select></div>
                        <div class="col-6"><label class="small font-weight-bold text-primary">03. SELECT LINES</label><select name="line_ids[]" class="input-tactical mb-3 border-primary" style="height: 100px;" multiple required>@foreach($lines as $l) <option value="{{ $l->id }}">{{ $l->kode_Line }}</option> @endforeach</select></div>
                    </div>

                    <label class="small font-weight-bold">04. CUSTOMER</label><select id="sel_customer" name="customer_code" class="input-tactical mb-3" required><option value="" disabled selected>-- SELECT CUSTOMER --</option>@foreach($customers as $c) <option value="{{ trim($c->code) }}">{{ strtoupper($c->code) }}</option> @endforeach</select>

                    <div class="row">
                        <div class="col-6"><label class="small font-weight-bold">05. SPEC</label><select id="sel_spec" name="spec" class="input-tactical mb-3" disabled required><option value="">-- WAIT --</option></select></div>
                        <div class="col-6"><label class="small font-weight-bold">06. PART NO</label><select id="sel_part" name="material_code" class="input-tactical mb-3" disabled required><option value="">-- WAIT --</option></select></div>
                    </div>

                    <label class="small font-weight-bold text-primary">07. PHYSICAL COIL</label><select id="sel_bandel" name="rm_stock_id" class="input-tactical mb-3 border-primary" disabled required><option value="">-- SELECT PART --</option></select>
                    
                    <div id="box_info_rm" class="d-none mb-3">
                        <div class="p-3 bg-primary text-white rounded-lg shadow-lg mb-2"><div class="d-flex justify-content-between text-center"><div class="flex-1"><small class="opacity-75">Available Stock</small><h4 class="mb-0 font-weight-bold" id="txt_stok_available">0</h4></div><div class="flex-1 border-left"><small class="opacity-75">Cycles</small><h4 class="mb-0 font-weight-bold" id="txt_sisa_batch">0</h4></div></div></div>
                        <div id="route_notif" class="alert border-0 font-weight-bold text-center py-2 animate__animated animate__fadeInUp" style="border-radius: 10px; font-size: 11px; display: none;"><i class="fas fa-directions mr-2"></i> TARGET: <span id="txt_route_target">...</span></div>
                    </div>
                    
                    <label class="small font-weight-bold text-primary">08. TOTAL QUANTITY (SINGLE BATCH)</label>
                    <input type="number" id="qty_ambil_pcs" name="qty_ambil_pcs" class="input-tactical text-center border-primary shadow-sm" placeholder="Input total pcs..." required>
                    <small id="multi_line_warning" class="text-primary font-weight-bold d-block mt-1" style="display:none; font-size: 11px;"></small>
                </div>
                <div class="modal-footer border-0 p-4"><button type="submit" id="btn_submit_ambil" class="btn btn-blueprint btn-block py-3 shadow-lg" style="background: var(--ind-blue); color: #fff;" disabled>DEPLOY BATCH</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    window.viewDigitalLabel = function(data) {
        $('#v_coil').text(data.coil); $('#v_spec').text(data.spec); $('#v_size').text(data.size);
        $('#v_qty').text(data.qty); $('#v_date').text(data.date);
        $('#v_part_no').text(data.part); $('#v_part_name').text(data.part_name);
        $('#modalLabelDigital').modal('show');
    };

    $(document).on('input', '.calc-input', function() {
        let id = $(this).data('id');
        let target = parseInt($(`.target-val[data-id="${id}"]`).first().text()) || 0;
        let ok = parseInt($(`#ok_${id}`).val()) || 0;
        let sisa = parseInt($(`#return_${id}`).val()) || 0;
        let ng_m = parseInt($(`#ng_mat_${id}`).val()) || 0;
        let ng_proc = parseInt($(`#ng_proc_${id}`).val()) || 0;
        let accounted = ok + sisa + ng_m + ng_proc;
        let gap = target - accounted;
        let progress = (accounted / target) * 100;
        
        $(`#current_${id}`).text(accounted.toLocaleString());
        $(`#gap_${id}`).text(gap.toLocaleString());
        $(`#bar_${id}`).css('width', progress + '%');
        
        let alertBox = $(`#police_msg_${id}`);
        let btn = $(`#btn_${id}`);
        
        if (accounted > target) { 
            alertBox.removeClass('alert-success alert-warning').addClass('alert-danger').html(`👮 SYSTEM POLICE: OVER LIMIT! (${accounted}/${target})`); btn.prop('disabled', true);
        } else if (accounted < target) { 
            alertBox.removeClass('alert-success alert-danger').addClass('alert-warning').html(`👮 SYSTEM POLICE: WAITING SYNC... Gap: ${gap}`); btn.prop('disabled', true);
        } else { 
            alertBox.removeClass('alert-danger alert-warning').addClass('alert-success').html(`👮 SYSTEM POLICE: DATA SYNCHRONIZED!`); btn.prop('disabled', false); 
        }
    });

    // ✨ Langsung hitung gap saat modal dibuka
    $('.modal').on('shown.bs.modal', function () {
        $(this).find('.calc-input').first().trigger('input');
    });

    $('#sel_customer').change(function() {
        let customer = $(this).val();
        if(customer) {
            $.get('/produksi/get-specs/' + encodeURIComponent(customer), function(data) {
                let html = '<option value="" disabled selected>-- CHOOSE SPEC --</option>';
                let uniqueSpecs = [];
                data.forEach(item => {
                    let key = (item.spec + item.size).replace(/\s+/g, '').toUpperCase();
                    if (!uniqueSpecs.includes(key)) { uniqueSpecs.push(key); html += `<option value="${item.spec}" data-size="${item.size}">${item.spec} [${item.size}]</option>`; }
                });
                $('#sel_spec').prop('disabled', false).html(html);
            });
        }
    });

    $('#sel_spec').change(function() {
        let spec = $(this).val(), customer = $('#sel_customer').val(), size = $(this).find(':selected').data('size'); 
        if(spec) {
            $.get('/produksi/get-parts-by-spec', {customer: customer, spec: spec, size: size}, function(data) {
                let html = '<option value="" disabled selected>-- CHOOSE PART --</option>';
                data.forEach(item => { html += `<option value="${item.material_code}" data-route="${item.next_process || 'FG'}">${item.material_code} - ${item.material_name}</option>`; });
                $('#sel_part').prop('disabled', false).html(html);
            });
        }
    });

    $('#sel_part').change(function() {
        let code = $(this).val(), route = $(this).find(':selected').data('route'), notif = $('#route_notif');
        if(route == 'WELDING') { notif.removeClass('alert-success').addClass('alert-warning').show(); $('#txt_route_target').text('WELDING AREA'); } 
        else { notif.removeClass('alert-warning').addClass('alert-success').show(); $('#txt_route_target').text('FINISHED GOODS'); }
        if(code) {
            $.get('/produksi/get-bundles/' + encodeURIComponent(code), function(data) {
                let html = '<option value="" disabled selected>-- SELECT COIL --</option>';
                data.forEach(item => { html += `<option value="${item.id}" data-qty="${item.stock_pcs}">${item.coil_id} | Avail: ${item.stock_pcs}</option>`; });
                $('#sel_bandel').prop('disabled', false).html(html);
            });
        }
    });

    let limitBandel = 0;
    $('#sel_bandel').change(function() {
        let selected = $(this).find(':selected'), stockId = $(this).val();
        limitBandel = parseInt(selected.data('qty')) || 0;
        $('#txt_stok_available').text(limitBandel.toLocaleString() + ' PCS');
        $('#box_info_rm').removeClass('d-none').hide().fadeIn();
        if(stockId) { $.get('/produksi/get-part-detail/' + encodeURIComponent(stockId), function(data) { $('#txt_sisa_batch').text(data.sisa_jalan + ' CYCLES'); }); }
    });

    $('select[name="line_ids[]"], #qty_ambil_pcs').on('input change', function() {
        let linesCount = $('select[name="line_ids[]"] :selected').length;
        let totalQty = parseInt($('#qty_ambil_pcs').val()) || 0;
        if (linesCount > 1 && totalQty > 0) {
            $('#multi_line_warning').show().html(`ℹ️ Total <b>${totalQty} Pcs</b> will be deployed as a single batch.`);
        } else { $('#multi_line_warning').hide(); }
        $('#btn_submit_ambil').prop('disabled', !(totalQty > 0 && totalQty <= limitBandel && linesCount > 0));
    });
});
</script>
@endsection