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

{{-- MODAL TAMBAH BOM --}}
<div class="modal fade" id="modalAddBom" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title font-weight-bold">TAMBAH RESEP KOMPONEN BOM</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('ppic.bom.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="small font-weight-bold">Part Jadi (Parent Welding)</label>
                        <select name="parent_part_no" class="form-control" required>
                            <option value="">-- Pilih Part Jadi --</option>
                            @foreach($parentParts as $p)
                                <option value="{{ $p->part_no }}">[{{ $p->customer_code }}] {{ $p->part_no }} - {{ $p->part_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Sumber Stok Komponen</label>
                        <select name="source_type" id="bom_source_type" class="form-control" required onchange="toggleCompList()">
                            <option value="WIP">WIP (Hasil Stamping / finished_goods)</option>
                            <option value="RM">RM (Nut / Plat / rm_stocks)</option>
                        </select>
                    </div>
                    <div class="form-group" id="box_comp_wip">
                        <label class="small font-weight-bold">Pilih Komponen WIP</label>
                        <select id="sel_wip" class="form-control" onchange="document.getElementById('final_comp_code').value = this.value">
                            <option value="">-- Pilih Part WIP --</option>
                            @foreach($wipParts as $wp)
                                <option value="{{ $wp->part_no }}">{{ $wp->part_no }} - {{ $wp->part_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" id="box_comp_rm" style="display:none;">
                        <label class="small font-weight-bold">Pilih Material RM / Nut</label>
                        <select id="sel_rm" class="form-control" onchange="document.getElementById('final_comp_code').value = this.value">
                            <option value="">-- Pilih Material RM --</option>
                            @foreach($rmMaterials as $rm)
                                <option value="{{ $rm->material_code }}">{{ $rm->material_code }} - {{ $rm->material_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="component_code" id="final_comp_code" required>
                    <div class="form-group">
                        <label class="small font-weight-bold">Kebutuhan per 1 Pcs Part Jadi</label>
                        <input type="number" name="qty_per_unit" class="form-control" value="1" min="1" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold">Simpan BOM</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleCompList() {
        let src = document.getElementById('bom_source_type').value;
        document.getElementById('box_comp_wip').style.display = (src === 'WIP') ? 'block' : 'none';
        document.getElementById('box_comp_rm').style.display  = (src === 'RM')  ? 'block' : 'none';
        document.getElementById('final_comp_code').value = '';
    }
</script>
@endsection