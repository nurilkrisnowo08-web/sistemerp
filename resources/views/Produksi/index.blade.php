@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

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
    .input-tactical { background: #f8fafc; border: 2px solid var(--ind-border); border-radius: 12px; padding: 12px; font-weight: 700; font-family: 'JetBrains Mono'; width: 100%; transition: 0.3s; }
    .btn-blueprint { border-radius: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; padding: 12px 25px; border: none; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; }
    .progress-lite { height: 12px; border-radius: 20px; background: #f1f5f9; overflow: hidden; margin: 15px 0; border: 1px solid var(--ind-border); }
    .progress-bar-fill { height: 100%; background: var(--ind-success); transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
</style>

<div class="container-fluid main-terminal anim-fade-up">
    <div class="px-4 pt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" style="border-radius:15px; border-left: 6px solid var(--ind-success) !important;">
                <div class="d-flex align-items-center"><i class="fas fa-check-circle mr-3 fa-lg"></i><div><strong class="text-uppercase">Success </strong><br><small>{{ session('success') }}</small></div></div>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius:15px; border-left: 6px solid var(--ind-danger) !important;">
                <div class="d-flex align-items-center"><i class="fas fa-exclamation-triangle mr-3 fa-lg"></i><div><strong class="text-uppercase">Alert </strong><br><small>{{ session('error') }}</small></div></div>
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
                    <td>
                        @if($p->qty_return > 0)
                            <span class="badge mb-1 animate__animated animate__pulse animate__infinite" style="background: #6366f1; color: #fff; font-size: 9px; padding: 4px 8px; border-radius: 4px;">
                                <i class="fas fa-redo-alt mr-1"></i> REWORK QC
                            </span><br>
                        @endif
                        <span class="id-tag">{{ $p->no_produksi }}</span>
                    </td>
                    <td><div class="font-weight-bold text-dark">{{ $p->material_code }}</div><small class="text-muted">{{ $p->coil_id }}</small></td>
                    <td>
                        @php $route = DB::table('parts')->where('part_no', $p->material_code)->value('next_process'); @endphp
                        @if(strtoupper($route) == 'WELDING') <span class="badge badge-warning">WELDING</span> @else <span class="badge badge-success">FG / QC</span> @endif
                    </td>
                    <td><span class="font-weight-bold text-primary">{{ $p->line_names }}</span></td>
                    
                    <td class="text-center font-weight-black">
                        @if($p->qty_return > 0)
                            <span class="text-primary" style="font-size: 16px;">{{ number_format($p->qty_return) }}</span>
                            <div style="font-size: 8px; color: #64748b;">PCS REWORK</div>
                        @else
                            {{ number_format($p->total_qty_batch) }}
                        @endif
                    </td>

                    <td class="text-center">
                        @if($p->status == 'PROBLEM')
                            <span class="badge badge-danger px-3 animate__animated animate__flash animate__infinite"><i class="fas fa-exclamation-triangle mr-1"></i> {{ $p->status }}</span>
                        @else
                            <span class="badge badge-primary px-3">{{ $p->status }}</span>
                        @endif
                    </td>
                    <td class="text-right d-flex justify-content-end align-items-center">
                        <button class="btn btn-danger btn-sm px-3 mr-2" data-toggle="modal" data-target="#modalProblem{{ $p->batch_id }}" style="border-radius: 10px;" title="REPORT PROBLEM">
                            <i class="fas fa-radiation-alt"></i>
                        </button>
                        <button class="btn btn-blueprint btn-sm px-4" style="background: var(--ind-success); color: #fff; border-radius: 10px;" data-toggle="modal" data-target="#modalInputHasil{{ $p->batch_id }}">INPUT RESULT</button>
                    </td>
                </tr>
                @empty 
                <tr><td colspan="7" class="py-5 text-center text-muted italic">-- SYSTEM IDLE --</td></tr> 
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($activeProductions as $p)
@php $currentTarget = ($p->qty_return > 0) ? $p->qty_return : $p->total_qty_batch; @endphp
{{-- 🛡️ MODAL INPUT HASIL --}}
<div class="modal fade" id="modalInputHasil{{ $p->batch_id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:25px; overflow: hidden;">
            <div class="modal-header bg-success text-white py-4 border-0">
                <h6 class="modal-title font-weight-bold">
                    <i class="fas fa-microchip mr-2"></i> 
                    {{ $p->qty_return > 0 ? 'REWORK PROCESS' : 'FINISH BATCH' }}: [{{ $p->no_produksi }}]
                </h6>
            </div>
            <form action="{{ route('produksi.update_result', $p->batch_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-5">
                    <div id="police_msg_{{ $p->batch_id }}" class="alert alert-warning border-0 font-weight-bold text-center py-3 mb-4">👮 STATUS: STANDBY FOR SYNC...</div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="small font-weight-bold text-success uppercase">Total OK Quantity</label>
                            {{-- ✨ FIX: Nilai default dimulai dari 0 rill --}}
                            <input type="number" name="qty_hasil_ok" id="ok_{{ $p->batch_id }}" data-id="{{ $p->batch_id }}" class="input-tactical calc-input mb-4" required value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="small font-weight-bold text-danger uppercase">Return (Sisa Material)</label>
                            <input type="number" name="qty_return_warehouse" id="return_{{ $p->batch_id }}" data-id="{{ $p->batch_id }}" class="input-tactical calc-input mb-4" value="0">
                        </div>
                    </div>

                    <div class="mt-2 border-top pt-3">
                        <label class="small font-weight-bold text-danger uppercase"><i class="fas fa-exclamation-triangle mr-1"></i> Rincian Reject (NG Spesifik)</label>
                        <div id="ng_container_{{ $p->batch_id }}"></div>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-block mt-2" onclick="addNgRow({{ $p->batch_id }})">
                            <i class="fas fa-plus mr-1"></i> TAMBAH JENIS NG
                        </button>
                    </div>

                    <div class="form-group mt-4">
                        <label class="small font-weight-bold text-muted uppercase">Keterangan Produksi</label>
                        <textarea name="keterangan" class="form-control" rows="2" style="border-radius: 12px; border: 2px solid var(--ind-border); font-weight: 600;" placeholder="Catatan tambahan..."></textarea>
                    </div>

                    <div class="p-3 bg-light mt-4 rounded-xl border text-center">
                        <small class="text-muted font-weight-bold uppercase">Gap Status (Must be 0):</small>
                        <h4 class="mb-0 font-weight-bold text-danger" id="gap_{{ $p->batch_id }}">{{ number_format($currentTarget) }}</h4>
                        <div class="progress-lite mt-2"><div class="progress-bar-fill" id="bar_{{ $p->batch_id }}" style="width: 0%"></div></div>
                        <input type="hidden" class="target-val" data-id="{{ $p->batch_id }}" value="{{ $currentTarget }}">
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 bg-light">
                    <button type="submit" id="btn_{{ $p->batch_id }}" class="btn btn-blueprint btn-block py-3 shadow-lg" style="background: var(--ind-success); color: #fff;" disabled>COMMIT & TRANSMIT DATA</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 🚨 MODAL LAPOR MASALAH --}}
<div class="modal fade" id="modalProblem{{ $p->batch_id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:25px; overflow: hidden;">
            <div class="modal-header bg-danger text-white py-3 border-0">
                <h6 class="modal-title font-weight-bold"><i class="fas fa-radiation-alt mr-2"></i> EMERGENCY REPORT: {{ $p->no_produksi }}</h6>
            </div>
            <form action="{{ route('produksi.report_problem', $p->batch_id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <label class="small font-weight-bold uppercase">Detail Kendala (Dies Rusak, Pin Patah, dll)</label>
                    <textarea name="problem_note" class="form-control" rows="4" style="border-radius: 12px; border: 2px solid var(--ind-border); font-weight: 600;" required placeholder="Jelaskan kondisi dies/kendala saat ini secara detail..."></textarea>
                </div>
                <div class="modal-footer border-0 p-4 bg-light text-right">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">CANCEL</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 font-weight-bold shadow">SEND EMERGENCY SIGNAL</button>
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
                    <label class="small font-weight-bold">01. BATCH ID</label>
                    <input type="text" name="no_produksi" class="input-tactical bg-light mb-3" value="PROD-{{ date('Ymd-His') }}" readonly>
                    <div class="row">
                        <div class="col-6">
                            <label class="small font-weight-bold">02. SHIFT</label>
                            <select name="shift" class="input-tactical mb-3" required>
                                <option value="" disabled selected>-- SELECT --</option>
                                <option value="Pagi">PAGI (S1)</option>
                                <option value="Malam">MALAM (S2)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold text-primary">03. LINE</label>
                            <select name="mesin_id" class="input-tactical mb-3 border-primary" required>
                                <option value="" disabled selected>-- SELECT --</option>
                                @foreach($lines as $l) <option value="{{ $l->id }}">{{ $l->kode_Line }}</option> @endforeach
                            </select>
                        </div>
                    </div>
                    <label class="small font-weight-bold">04. CUSTOMER</label>
                    <select id="sel_customer" class="input-tactical mb-3" required>
                        <option value="" disabled selected>-- SELECT --</option>
                        @foreach($customers as $c) <option value="{{ trim($c->code) }}">{{ strtoupper($c->code) }}</option> @endforeach
                    </select>
                    <div class="row">
                        <div class="col-6"><label class="small font-weight-bold">05. SPEC</label><select id="sel_spec" class="input-tactical mb-3" disabled required></select></div>
                        <div class="col-6"><label class="small font-weight-bold">06. PART NO</label><select id="sel_part" name="material_code" class="input-tactical mb-3" disabled required></select></div>
                    </div>
                    <label class="small font-weight-bold text-primary">07. PHYSICAL COIL</label>
                    <select id="sel_bandel" name="rm_stock_id" class="input-tactical mb-3 border-primary" disabled required></select>
                    <label class="small font-weight-bold text-primary">08. TOTAL QUANTITY</label>
                    <input type="number" id="qty_ambil_pcs" name="qty_ambil_pcs" class="input-tactical text-center border-primary shadow-sm" required placeholder="0">
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="submit" id="btn_submit_ambil" class="btn btn-blueprint btn-block py-3 shadow-lg" style="background: var(--ind-blue); color: #fff;" disabled>DEPLOY BATCH</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const listPenyakit = ["Burry", "Dented", "Deform", "Oil Maru", "Spring Back", "Nobi", "Crack", "Scratch", "Pull Up", "Pull Down", "NG Thickness", "Wrinkle", "Missing Hole"];

    function addNgRow(batchId) {
        const id = Date.now();
        let options = listPenyakit.map(p => `<option value="${p}">${p}</option>`).join('');
        const html = `
            <div class="row no-gutters mb-2 animate__animated animate__fadeInDown" id="row-${id}">
                <div class="col-7 pr-1">
                    <select name="ng_detail_type[]" class="form-control form-control-sm shadow-sm font-weight-bold">${options}</select>
                </div>
                <div class="col-3 pr-1">
                    <input type="number" name="ng_detail_qty[]" class="form-control form-control-sm shadow-sm ng-qty-input text-center font-weight-bold" 
                    placeholder="Qty" min="1" required data-id="${batchId}">
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-danger btn-sm btn-block" onclick="removeNgRow(${id}, ${batchId})"><i class="fas fa-times"></i></button>
                </div>
            </div>`;
        $(`#ng_container_${batchId}`).append(html);
        triggerCalc(batchId);
    }

    function removeNgRow(id, batchId) {
        $(`#row-${id}`).remove();
        triggerCalc(batchId);
    }

    function triggerCalc(batchId) {
        $(`#ok_${batchId}`).trigger('input');
    }

    $(document).ready(function() {
        $(document).on('input', '.calc-input, .ng-qty-input', function() {
            let id = $(this).data('id');
            let target = parseInt($(`.target-val[data-id="${id}"]`).val()) || 0;
            
            let dynamicNgSum = 0;
            $(`#ng_container_${id} .ng-qty-input`).each(function() {
                dynamicNgSum += parseInt($(this).val()) || 0;
            });

            let okVal = parseInt($(`#ok_${id}`).val()) || 0;
            let retVal = parseInt($(`#return_${id}`).val()) || 0;
            let accounted = okVal + retVal + dynamicNgSum;

            let gap = target - accounted;
            $(`#gap_${id}`).text(gap.toLocaleString());
            
            let progress = (accounted / target) * 100;
            $(`#bar_${id}`).css('width', progress + '%');
            
            let btn = $(`#btn_${id}`), msg = $(`#police_msg_${id}`);
            
            if (gap === 0) { 
                msg.removeClass('alert-warning alert-danger').addClass('alert-success').html('👮 DATA SYNC! Ready to commit.'); 
                btn.prop('disabled', false); 
            } else if (gap < 0) {
                msg.removeClass('alert-warning alert-success').addClass('alert-danger').html('🚨 OVER LIMIT! Check your numbers.'); 
                btn.prop('disabled', true); 
            } else { 
                msg.removeClass('alert-success alert-danger').addClass('alert-warning').html('👮 WAITING SYNC... Gap: ' + gap); 
                btn.prop('disabled', true); 
            }
        });

        $('#sel_customer').change(function() {
            $.get('/produksi/get-specs/' + $(this).val(), function(data) {
                let h = '<option value="" disabled selected>-- SELECT SPEC --</option>';
                data.forEach(i => { h += `<option value="${i.spec}" data-size="${i.size}">${i.spec} [${i.size}]</option>`; });
                $('#sel_spec').prop('disabled', false).html(h);
            });
        });

        $('#sel_spec').change(function() {
            let s = $(this).find(':selected').data('size');
            $.get('/produksi/get-parts-by-spec', {customer: $('#sel_customer').val(), spec: $(this).val(), size: s}, function(data) {
                let h = '<option value="" disabled selected>-- SELECT PART --</option>';
                data.forEach(i => { h += `<option value="${i.material_code}">${i.material_code}</option>`; });
                $('#sel_part').prop('disabled', false).html(h);
            });
        });

        $('#sel_part').change(function() {
            $.get('/produksi/get-bundles/' + $(this).val(), function(data) {
                let h = '<option value="" disabled selected>-- SELECT COIL --</option>';
                data.forEach(i => { 
                    // ✨ Menggunakan i.id yang merupakan MAX(id) dari Controller rill
                    h += `<option value="${i.id}" data-qty="${i.stock_pcs}">${i.coil_id} (Avail: ${i.stock_pcs})</option>`; 
                });
                $('#sel_bandel').prop('disabled', false).html(h);
            });
        });

        $('#sel_bandel, #qty_ambil_pcs, select[name="mesin_id"]').on('change input', function() {
            let maxStok = parseInt($('#sel_bandel option:selected').data('qty')) || 0;
            let inputQty = parseInt($('#qty_ambil_pcs').val()) || 0;
            let mesinSelected = $('select[name="mesin_id"]').val();
            $('#btn_submit_ambil').prop('disabled', !(inputQty > 0 && inputQty <= maxStok && mesinSelected));
        });
    });
</script>
@endsection