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

    /* 🛸 HEADER & CARDS */
    .heading-cyber { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(90deg, var(--dark-surface), var(--primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .card-industrial { border: none; border-radius: 30px; background: #ffffff; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03); border: 1px solid rgba(255,255,255,0.8); overflow: hidden; }

    /* 📈 TABLE STYLE */
    .table-ledger thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 22px 15px; border: none; font-weight: 800; }
    .rm-row-header { cursor: pointer; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-left: 6px solid transparent; }
    .rm-row-header:hover { background-color: var(--primary-soft) !important; border-left-color: var(--primary); }
    .col-live { font-family: 'Orbitron'; font-weight: 900; font-size: 18px; color: var(--primary); background: rgba(67, 97, 238, 0.05); }

    /* 📋 ACTIVITY FEED */
    .log-container { background: #f8fafc; border-radius: 24px; padding: 20px; border: 1px solid #e2e8f0; }
    .log-entry { display: flex; justify-content: space-between; align-items: center; padding: 15px; background: white; border-radius: 18px; margin-bottom: 12px; border-left: 6px solid #cbd5e1; box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: 0.3s; }
    .log-entry.in { border-left-color: var(--brand-success); }
    .log-entry.out { border-left-color: var(--brand-danger); }
    .log-entry.ret { border-left-color: var(--brand-return); }

    /* ⌨️ FORM CONTROLS */
    .tech-input { border-radius: 16px; border: 2.5px solid #f1f5f9; padding: 12px 18px; font-weight: 700; background: #f8fafc; transition: 0.3s; }
    .tech-input:focus { border-color: var(--primary); background: #fff; outline: none; box-shadow: 0 0 0 5px rgba(67, 97, 238, 0.1); }
</style>

<div class="container-fluid mt-4 animate__animated animate__fadeIn">
    
    {{-- 🛰️ HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-cyber m-0">RM_HUB <span class="text-primary">CORE_v2.6</span></h1>
            <p class="text-muted small font-weight-bold mb-0 text-uppercase"><i class="fas fa-satellite-dish text-primary mr-2"></i> PT ASALTA MANDIRI AGUNG - INVENTORY CONTROL</p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
            <a href="{{ route('rm.log_print') }}" class="btn btn-white rounded-pill px-4 font-weight-bold border mr-2 shadow-sm"><i class="fas fa-history mr-2"></i>RECAP</a>
            <button class="btn btn-primary shadow-lg px-4 py-2 rounded-pill font-weight-bold" data-toggle="modal" data-target="#modalTambahRM">
                <i class="fas fa-plus-circle mr-2"></i>REGISTER_UNIT
            </button>
        </div>
    </div>

    {{-- 🔍 FILTER --}}
    <div class="card-industrial p-4 mb-4 animate__animated animate__fadeInDown">
        <form action="{{ route('rm.store') }}" method="GET" id="autoFilterForm" class="row align-items-end">
            <div class="col-md-4">
                <label class="small font-weight-black text-primary mb-2 uppercase">Target Client Entity</label>
                <select name="customer" class="form-control tech-input w-100" onchange="this.form.submit()">
                    <option value="">-- ALL CLIENTS --</option>
                    @foreach($availableCustomers as $c) 
                        <option value="{{ trim($c->code) }}" {{ $customer == trim($c->code) ? 'selected' : '' }}>{{ $c->name }}</option> 
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="small font-weight-black text-primary mb-2 uppercase">Material Search</label>
                <input type="text" name="alias" class="form-control tech-input" placeholder="Enter spec or alias code..." value="{{ request('alias') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-dark btn-block font-weight-bold" style="height: 52px; border-radius: 16px;">
                    <i class="fas fa-sync-alt mr-2"></i>SYNC_DASHBOARD
                </button>
            </div>
        </form>
    </div>

    {{-- 📊 LEDGER TABLE --}}
    <div class="card-industrial">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Identification</th>
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
                            <small class="text-muted font-weight-bold uppercase">{{ $group->spec }} | {{ $group->size }}</small>
                        </td>
                        <td class="text-muted font-weight-bold">{{ number_format($group->total_init) }}</td>
                        <td class="text-success font-weight-black">+{{ number_format($group->total_in_s) }}</td>
                        <td style="color: var(--brand-return);" class="font-weight-black">+{{ number_format($group->total_in_r) }}</td>
                        <td class="text-danger font-weight-black">-{{ number_format($group->total_out) }}</td>
                        <td class="col-live">{{ number_format($group->total_live) }}</td>
                        <td><i class="fas fa-chevron-right text-muted"></i></td>
                    </tr>
                    
                    <tr id="det-{{ $slug }}" class="collapse bg-light">
                        <td colspan="7" class="p-4">
                            <div class="row">
                                <div class="col-md-7">
                                    @foreach($group->details as $p)
                                    @php $subParts = DB::table('rm_stocks')->where('coil_id', $p->coil_id)->where('customer', $p->customer)->get(); @endphp
                                    <div class="card shadow-sm border-0 mb-3 rounded-xl p-4 bg-white">
                                        <div class="d-flex justify-content-between mb-3">
                                            <div>
                                                <span class="badge-coil">{{ $p->coil_id }}</span>
                                                <h4 class="font-weight-black text-primary mt-3">{{ number_format($p->stock_pcs) }} <small class="h6">PCS</small></h4>
                                            </div>
                                            <div class="btn-group h-50">
                                                <button class="btn btn-outline-primary btn-sm px-3" onclick="openAssignPart('{{ $p->id }}', '{{ $p->customer }}')">ADD_PART</button>
                                                <button class="btn btn-outline-warning btn-sm px-3" onclick="openEditUnit('{{ $p->id }}', '{{ $p->coil_id }}', '{{ $p->stock_pcs }}')">ADJUST</button>
                                            </div>
                                        </div>
                                        <div class="bg-light p-3 rounded-xl border">
                                            <small class="font-weight-black text-muted d-block mb-2 uppercase">Mapped Components:</small>
                                            @foreach($subParts as $sp)
                                                <div class="d-flex justify-content-between mb-1 bg-white p-2 rounded border">
                                                    <span class="font-weight-bold small">{{ $sp->material_code }} - {{ $sp->material_name }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="col-md-5">
                                    <div class="card border-0 shadow-sm rounded-3xl p-4 bg-white h-100">
                                        <h6 class="font-weight-black text-dark mb-4 uppercase"><i class="fas fa-stream mr-2 text-primary"></i>Recent_Mutations</h6>
                                        <div class="log-container" style="max-height: 500px; overflow-y:auto;">
                                            @foreach($group->combined_logs as $log)
                                                @php $isOut = isset($log->pcs_used); @endphp
                                                <div class="log-entry {{ $isOut ? 'out' : 'in' }}">
                                                    <div>
                                                        <small class="text-muted font-weight-bold">{{ date('d M, H:i', strtotime($log->created_at)) }}</small>
                                                        <div class="font-weight-bold">{{ $log->no_produksi ?? $log->po_identitas }}</div>
                                                    </div>
                                                    <div class="h5 font-weight-black">{{ $isOut ? '-' : '+' }}{{ number_format($log->pcs_used ?? $log->pcs_in) }}</div>
                                                </div>
                                            @endforeach
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

{{-- MODALS --}}
@include('Gudang.rm_modals') 

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // ⚡ LOGIKA OTOMATIS: AMBIL SPEC & PART SAAT PILIH CLIENT
    $(document).on('change', '#modalFilterCustomer', function() {
        let customerCode = $(this).val();
        
        if (customerCode) {
            // Tampilkan status loading
            $('#selectMasterSpec').html('<option>Loading database...</option>').prop('disabled', true);
            $('#selectPart').html('<option>Loading database...</option>').prop('disabled', true);

            $.ajax({
                url: "/get-parts-and-specs/" + encodeURIComponent(customerCode),
                type: "GET",
                success: function(res) {
                    // 1. Render Dropdown Specification
                    let specHtml = '<option value="" disabled selected>-- SELECT SPEC --</option>';
                    $.each(res.specs, function(k, v) {
                        specHtml += `<option value="${v.material_type}" 
                                        data-spec="${v.material_type}" 
                                        data-size="${v.thickness} X ${v.size}">
                                        [${v.alias_code}] ${v.material_type} (${v.thickness}x${v.size})
                                     </option>`;
                    });
                    $('#selectMasterSpec').html(specHtml).prop('disabled', false);

                    // 2. Render List Mapped Parts
                    let partHtml = '';
                    $.each(res.parts, function(k, v) {
                        partHtml += `<option value="${v.part_no}">${v.part_no} - ${v.part_name}</option>`;
                    });
                    $('#selectPart').html(partHtml).prop('disabled', false);
                },
                error: function() {
                    alert('Gagal mengambil data. Pastikan Route sudah terdaftar!');
                }
            });
        }
    });

    // ⚡ Logika simpan data spec ke input hidden
    $(document).on('change', '#selectMasterSpec', function() {
        let selected = $(this).find(':selected');
        $('#autoSpec').val(selected.data('spec'));
        $('#autoSize').val(selected.data('size'));
    });

    // Fungsi Adjust & Assign Part
    function openEditUnit(id, coil, qty) { $('#edit_id').val(id); $('#edit_coil').val(coil); $('#edit_qty').val(qty); $('#modalEditUnit').modal('show'); }
    function openAssignPart(id, customer) {
        $('#assign_ref_id').val(id);
        $.ajax({
            url: "/get-parts-and-specs/" + encodeURIComponent(customer),
            type: "GET",
            success: function(res) {
                let options = '<option value="">-- SELECT PART --</option>';
                $.each(res.parts, function(k, v) { options += `<option value="${v.part_no}">${v.part_no} - ${v.part_name}</option>`; });
                $('#assign_select_part').html(options);
                $('#modalAssignPart').modal('show');
            }
        });
    }

    $(document).ready(function() {
        $('.rm-row-header').click(function() { $(this).find('.fa-chevron-right').toggleClass('fa-rotate-90'); });
    });
</script>
@endsection