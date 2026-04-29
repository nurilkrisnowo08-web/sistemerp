@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">

<style>
    :root { --primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444; --dark: #0f172a; --slate-bg: #f8fafc; }
    body { background-color: var(--slate-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark); }
    .card-modern { border: none; border-radius: 24px; background: #ffffff; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02); margin-bottom: 1.5rem; border: 1px solid #eef2f6; }
    
    /* ✨ LEDGER TABLE UPGRADE */
    .table-ledger th { background-color: #f8fafc; text-transform: uppercase; font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 1px; padding: 20px 15px; border: none !important; }
    .rm-row-header { cursor: pointer; transition: 0.3s; border-left: 6px solid transparent; }
    .rm-row-header:hover { background-color: #f0f3ff !important; border-left-color: var(--primary); }
    
    .col-live { background: rgba(67, 97, 238, 0.05); color: var(--primary); font-family: 'Orbitron'; font-weight: 900 !important; font-size: 18px; border-radius: 12px; }
    
    .badge-coil { background: var(--dark); color: #fff; padding: 6px 14px; border-radius: 10px; font-family: 'JetBrains Mono'; font-weight: 700; font-size: 12px; transition: 0.3s; cursor: pointer; border: none; }
    .badge-coil:hover { background: var(--primary); transform: translateY(-3px); box-shadow: 0 10px 20px rgba(67, 97, 238, 0.2); }

    /* Input Style Industrial */
    .form-control-tech { border-radius: 14px; border: 2px solid #f1f5f9; padding: 12px 18px; font-weight: 700; transition: 0.3s; background: #f8fafc; }
    .form-control-tech:focus { border-color: var(--primary); background: #fff; outline: none; box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1); }
</style>

<div class="container-fluid mt-3 animate__animated animate__fadeIn">
    {{-- TOP BAR --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="font-weight-black m-0" style="letter-spacing: -1.5px; font-family: 'Orbitron';">RAW_MATERIAL <span class="text-primary">HUB</span></h2>
            <p class="text-muted small font-weight-bold mb-0 text-uppercase"><i class="fas fa-microchip mr-2 text-primary"></i> PT ASALTA MANDIRI AGUNG - INVENTORY SYSTEM</p>
        </div>
        <div class="d-flex">
            <button class="btn btn-primary shadow-lg px-4 py-2 rounded-pill font-weight-bold" data-toggle="modal" data-target="#modalTambahRM">
                <i class="fas fa-plus-circle mr-2"></i>REGISTER_COIL
            </button>
        </div>
    </div>

    {{-- MAIN TABLE --}}
    <div class="card-modern border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Material identification</th>
                        <th>Opening</th>
                        <th class="text-success">In (Supplier)</th>
                        <th style="color: #6366f1;">In (Return)</th>
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
                            <div class="font-weight-black text-primary" style="font-size: 15px; font-family: 'JetBrains Mono';">{{ $group->alias_code ?? $group->group_key }}</div>
                            <small class="text-muted font-weight-bold uppercase">{{ $group->spec }} | {{ $group->size }}</small>
                        </td>
                        <td class="text-muted font-weight-bold">{{ number_format($group->total_init) }}</td>
                        <td class="text-success font-weight-black">+{{ number_format($group->total_in_s) }}</td>
                        <td style="color: #6366f1;" class="font-weight-black">+{{ number_format($group->total_in_r) }}</td>
                        <td class="text-danger font-weight-black">-{{ number_format($group->total_out) }}</td>
                        <td class="col-live">{{ number_format($group->total_live) }}</td>
                        <td><i class="fas fa-chevron-down text-muted"></i></td>
                    </tr>
                    
                    {{-- DETAIL AREA --}}
                    <tr id="det-{{ $slug }}" class="collapse bg-light">
                        <td colspan="7" class="p-4">
                            <div class="row">
                                <div class="col-md-7">
                                    @foreach($group->details as $p)
                                    @php $subParts = DB::table('rm_stocks')->where('coil_id', $p->coil_id)->where('customer', $p->customer)->get(); @endphp
                                    <div class="card shadow-sm border-0 mb-3 rounded-xl overflow-hidden">
                                        <div class="card-body p-4 bg-white">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <span class="badge-coil">{{ $p->coil_id }}</span>
                                                    <small class="d-block text-muted mt-2 font-weight-bold">UNIT SALDO: <b class="text-primary">{{ number_format($p->stock_pcs) }} PCS</b></small>
                                                </div>
                                                <div class="btn-group">
                                                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3 mr-2" onclick="openAssignPart('{{ $p->id }}', '{{ $p->customer }}')">ADD_PART</button>
                                                    <button class="btn btn-outline-warning btn-sm rounded-pill px-3 mr-2" onclick="openEditUnit('{{ $p->id }}', '{{ $p->coil_id }}', '{{ $p->stock_pcs }}')">ADJUST</button>
                                                    <form action="{{ route('rm.destroy', $p->id) }}" method="POST" onsubmit="return confirm('ERASE UNIT?')">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger btn-sm rounded-circle"><i class="fas fa-trash"></i></button></form>
                                                </div>
                                            </div>
                                            <div class="bg-light p-3 rounded-lg border">
                                                <small class="font-weight-black text-muted d-block mb-2">MAPPED PRODUCTION PARTS:</small>
                                                @foreach($subParts as $sp)
                                                <div class="d-flex justify-content-between align-items-center mb-1 bg-white p-2 rounded border">
                                                    <span class="font-weight-bold text-dark" style="font-size: 12px;">{{ $sp->material_code }} - <span class="text-muted">{{ $sp->material_name }}</span></span>
                                                    <form action="{{ route('rm.remove_part_from_unit', $sp->id) }}" method="POST">@csrf @method('DELETE')<button type="submit" class="border-0 bg-transparent text-danger"><i class="fas fa-unlink"></i></button></form>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="col-md-5">
                                    <div class="card border-0 shadow-sm rounded-xl p-3 bg-white h-100">
                                        <h6 class="font-weight-black text-dark mb-3 uppercase"><i class="fas fa-history mr-2 text-primary"></i>Mutation_Feed</h6>
                                        <div class="log-container" style="max-height: 400px; overflow-y:auto;">
                                            @foreach($group->combined_logs as $log)
                                            <div class="d-flex justify-content-between align-items-center p-3 border-bottom font-weight-bold" style="font-size: 11px; font-family: 'JetBrains Mono';">
                                                <span>{{ isset($log->pcs_used) ? 'OUT' : 'IN' }} > {{ $log->no_produksi ?? $log->po_identitas }}</span>
                                                <span class="{{ isset($log->pcs_used) ? 'text-danger' : 'text-success' }}">{{ isset($log->pcs_used) ? '-' : '+' }}{{ number_format($log->pcs_used ?? $log->pcs_in) }}</span>
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

{{-- 🛠️ MODAL ADJUST UNIT --}}
<div class="modal fade" id="modalEditUnit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 25px;">
            <div class="modal-header bg-warning border-0">
                <h6 class="modal-title font-weight-black uppercase">Adjust_Unit_Saldo</h6>
            </div>
            <form action="{{ route('rm.update_unit_pcs') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="edit_id">
                    <label class="small font-weight-bold">UNIT_COIL_ID</label>
                    <input type="text" id="edit_coil" class="form-control mb-3 form-control-tech" readonly>
                    <label class="small font-weight-bold">ADJUST_NEW_QTY (PCS)</label>
                    <input type="number" name="new_qty" id="edit_qty" class="form-control form-control-tech" required>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-warning btn-block py-3 font-weight-bold rounded-pill shadow">AUTHORIZE_ADJUSTMENT</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 🛠️ MODAL ASSIGN PART --}}
<div class="modal fade" id="modalAssignPart" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 25px;">
            <div class="modal-header bg-primary text-white border-0">
                <h6 class="modal-title font-weight-black uppercase">Assign_Component_to_Unit</h6>
            </div>
            <form action="{{ route('rm.assign_part_to_unit') }}" method="POST">
                @csrf
                <div class="modal-body p-4 text-center">
                    <input type="hidden" name="ref_id" id="assign_ref_id">
                    <p class="small text-muted font-weight-bold">Select parts that can be produced from this coil unit.</p>
                    <select name="part_nos[]" id="assign_select_part" class="form-control form-control-tech" multiple style="height: 150px;" required></select>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold rounded-pill shadow">UPDATE_MAPPING</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 🛠️ SCRIPTS (FUNGSI YANG HILANG SAYA TAMBAHKAN DI SINI) --}}
<script>
    function openAssignPart(id, customer) {
        $('#assign_ref_id').val(id);
        $.ajax({
            url: "/get-parts-and-specs/" + encodeURIComponent(customer),
            type: "GET",
            success: function(res) {
                let options = '';
                $.each(res.parts, function(k, v) {
                    options += `<option value="${v.part_no}">${v.part_no} - ${v.part_name}</option>`;
                });
                $('#assign_select_part').html(options);
                $('#modalAssignPart').modal('show');
            }
        });
    }

    function openEditUnit(id, coil, qty) {
        $('#edit_id').val(id);
        $('#edit_coil').val(coil);
        $('#edit_qty').val(qty);
        $('#modalEditUnit').modal('show');
    }

    $(document).ready(function() {
        // Auto-Filter Dropdown
        $('select[name="customer"]').on('change', function() {
            $('#autoFilterForm').submit();
        });
    });
</script>
@endsection