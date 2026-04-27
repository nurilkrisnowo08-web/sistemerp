@extends('layout.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="font-weight-bold text-dark mb-0">WELDING_LINE_REGISTRY</h3>
            <p class="text-muted small font-weight-bold uppercase">Master Data Station & Robotic Welding</p>
        </div>
        <button class="btn btn-primary shadow-sm rounded-pill px-4" data-toggle="modal" data-target="#addModal">
            <i class="fas fa-plus mr-2"></i> REGISTER NEW LINE
        </button>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="pl-4">KODE LINE</th>
                        <th>NAMA STATION / AREA</th>
                        <th>REGISTERED_AT</th>
                        <th class="text-right pr-4">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lines as $l)
                    <tr>
                        <td class="pl-4"><span class="badge badge-dark px-3 py-2" style="font-family: 'JetBrains Mono';">{{ $l->kode_line }}</span></td>
                        <td class="font-weight-bold">{{ $l->nama_line }}</td>
                        <td class="text-muted small">{{ date('d M Y', strtotime($l->created_at)) }}</td>
                        <td class="text-right pr-4">
                            <form action="{{ route('welding.master.line_destroy', $l->id) }}" method="POST" onsubmit="return confirm('Hapus Line ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-link text-danger p-0"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">No welding lines registered yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-header bg-primary text-white border-0">
                <h6 class="modal-title font-weight-bold">NEW_LINE_DEPLOYMENT</h6>
            </div>
            <form action="{{ route('welding.master.line_store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <label class="small font-weight-bold">LINE CODE (e.g: W-ROBOT-01)</label>
                    <input type="text" name="kode_line" class="form-control mb-3" required placeholder="W-XXXX-XX">
                    
                    <label class="small font-weight-bold">STATION NAME</label>
                    <input type="text" name="nama_line" class="form-control" required placeholder="Welding Robotic Area A">
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary btn-block py-2 font-weight-bold">AUTHORIZE & SAVE</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection