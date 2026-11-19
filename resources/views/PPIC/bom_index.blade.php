@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-lg-5">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="font-weight-bold mb-0">MASTER BOM (BILL OF MATERIALS) WELDING</h4>
            <small class="text-muted">Atur resep komponen WIP & RM (Nut/Material) untuk setiap part assembly.</small>
        </div>
        <button class="btn btn-primary rounded-pill px-4 font-weight-bold" data-toggle="modal" data-target="#modalAddBom">
            <i class="fas fa-plus mr-1"></i> TAMBAH KOMPONEN BOM
        </button>
    </div>

    @forelse($boms as $parentNo => $items)
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 16px; overflow: hidden;">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span class="font-weight-bold">
                [{{ $items->first()->customer_code ?? '-' }}] {{ $parentNo }} - {{ $items->first()->parent_name ?? '' }}
            </span>
            <span class="badge badge-info">{{ $items->count() }} Komponen</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0 text-center">
                <thead class="bg-light">
                    <tr>
                        <th width="50">No</th>
                        <th class="text-left">Kode Komponen / Material</th>
                        <th>Sumber Stok</th>
                        <th>Kebutuhan / 1 Pcs</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="text-left font-weight-bold">{{ $item->component_code }}</td>
                        <td>
                            @if($item->source_type == 'WIP')
                                <span class="badge badge-primary px-3">WIP (finished_goods)</span>
                            @else
                                <span class="badge badge-warning px-3">RM (rm_stocks)</span>
                            @endif
                        </td>
                        <td class="font-weight-bold">{{ $item->qty_per_unit }} Pcs</td>
                        <td>
                            <form action="{{ route('ppic.bom.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus komponen ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="alert alert-light border text-center py-5">Belum ada data BOM. Klik tombol <b>+ TAMBAH KOMPONEN BOM</b> untuk mulai membuat resep.</div>
    @endforelse
</div>

{{-- MODAL TAMBAH BOM (MULTI-KOMPONEN) --}}
<div class="modal fade" id="modalAddBom" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title font-weight-bold">TAMBAH RESEP KOMPONEN BOM</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('ppic.bom.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-4">
                        <label class="small font-weight-bold">Part Jadi (Parent Welding)</label>
                        <select name="parent_part_no" class="form-control font-weight-bold" required>
                            <option value="">-- Pilih Part Jadi --</option>
                            @foreach($parentParts as $p)
                                <option value="{{ $p->part_no }}">[{{ $p->customer_code }}] {{ $p->part_no }} - {{ $p->part_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="small font-weight-bold mb-0 text-primary">DAFTAR KOMPONEN PENYUSUN (WIP / RM)</label>
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 font-weight-bold" onclick="addBomRow()">
                            <i class="fas fa-plus mr-1"></i> Tambah Baris
                        </button>
                    </div>

                    <div id="bomRowsContainer">
                        {{-- Baris komponen pertama otomatis muncul di sini via JS --}}
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold">Simpan Semua BOM</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- TEMPLATE OPSI DROPDOWN (DISIMPAN DI HIDDEN TEMPLATE BIAR RAPI) --}}
<template id="tplOptionsWip">
    <option value="">-- Pilih Part WIP --</option>
    @foreach($wipParts as $wp)
        <option value="{{ $wp->part_no }}">{{ $wp->part_no }} - {{ $wp->part_name }}</option>
    @endforeach
</template>

<template id="tplOptionsRm">
    <option value="">-- Pilih Material RM / Nut --</option>
    @foreach($rmMaterials as $rm)
        <option value="{{ $rm->material_code }}">{{ $rm->material_code }} - {{ $rm->material_name }}</option>
    @endforeach
</template>

<script>
    function addBomRow() {
        const container = document.getElementById('bomRowsContainer');
        const wipOptions = document.getElementById('tplOptionsWip').innerHTML;

        const row = document.createElement('div');
        row.className = 'bom-row row align-items-center bg-light p-2 mb-2 rounded border';
        row.innerHTML = `
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="small font-weight-bold mb-1">Sumber</label>
                <select name="source_type[]" class="form-control form-control-sm font-weight-bold" onchange="switchRowOptions(this)" required>
                    <option value="WIP">WIP (Stamping)</option>
                    <option value="RM">RM (Nut / Plat)</option>
                </select>
            </div>
            <div class="col-md-6 mb-2 mb-md-0">
                <label class="small font-weight-bold mb-1">Komponen / Material</label>
                <select name="component_code[]" class="form-control form-control-sm comp-select" required>
                    ${wipOptions}
                </select>
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label class="small font-weight-bold mb-1">Qty / Pcs</label>
                <input type="number" name="qty_per_unit[]" class="form-control form-control-sm text-center font-weight-bold" value="1" min="1" required>
            </div>
            <div class="col-md-1 text-right pt-md-3">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeBomRow(this)" title="Hapus Baris">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
    }

    function switchRowOptions(selectEl) {
        const row = selectEl.closest('.bom-row');
        const compSelect = row.querySelector('.comp-select');
        if (selectEl.value === 'WIP') {
            compSelect.innerHTML = document.getElementById('tplOptionsWip').innerHTML;
        } else {
            compSelect.innerHTML = document.getElementById('tplOptionsRm').innerHTML;
        }
    }

    function removeBomRow(btn) {
        const rows = document.querySelectorAll('#bomRowsContainer .bom-row');
        if (rows.length > 1) {
            btn.closest('.bom-row').remove();
        } else {
            alert('Minimal harus ada 1 baris komponen.');
        }
    }

    // Otomatis buat 1 baris awal saat halaman siap
    document.addEventListener('DOMContentLoaded', function() {
        addBomRow();
    });
</script>
@endsection