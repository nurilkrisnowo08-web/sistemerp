@extends('layout.admin')

@section('content')
<div class="container-fluid">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="font-weight-bold text-dark mb-0">MASTER_NG_REGISTRY</h3>
            <p class="text-muted small font-weight-bold uppercase">Defect Library for Stamping & Welding Stations</p>
        </div>
        <button class="btn btn-danger shadow-sm rounded-pill px-4" data-toggle="modal" data-target="#modalAddNG">
            <i class="fas fa-plus mr-2"></i> REGISTER NEW DEFECT
        </button>
    </div>

    {{-- TABLE --}}
    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="pl-4">DEFECT NAME (NG TYPE)</th>
                        <th>CATEGORY</th>
                        <th>REGISTERED_DATE</th>
                        <th class="text-right pr-4">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listNG as $ng)
                    <tr>
                        <td class="pl-4">
                            <span class="font-weight-bold text-uppercase" style="font-family: 'JetBrains Mono';">{{ $ng->ng_name }}</span>
                        </td>
                        <td>
                            @if($ng->category == 'WELDING')
                                <span class="badge badge-warning px-3">WELDING</span>
                            @elseif($ng->category == 'STAMPING')
                                <span class="badge badge-primary px-3">STAMPING</span>
                            @else
                                <span class="badge badge-secondary px-3">GENERAL</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ date('d M Y', strtotime($ng->created_at)) }}</td>
                        <td class="text-right pr-4">
                            {{-- Tambahkan fungsi hapus jika perlu --}}
                            <button class="btn btn-link text-danger p-0"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">No NG types registered in database.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL ADD NEW NG --}}
<div class="modal fade" id="modalAddNG" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-danger text-white border-0">
                <h6 class="modal-title font-weight-bold">NEW_DEFECT_REGISTRY</h6>
            </div>
            <form action="{{ route('welding.master.ng_store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold uppercase">Defect Name / NG Type</label>
                        <input type="text" name="ng_name" class="form-control" required placeholder="e.g: blowhole, undercut...">
                    </div>
                    
                    <div class="form-group">
                        <label class="small font-weight-bold uppercase">Department Category</label>
                        <select name="category" class="form-control" required>
                            <option value="WELDING">WELDING AREA</option>
                            <option value="STAMPING">STAMPING AREA</option>
                            <option value="GENERAL">GENERAL / GLOBAL</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-danger btn-block py-2 font-weight-bold shadow">AUTHORIZE & SAVE TO MASTER</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection