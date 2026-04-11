@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root { --brand-blue: #4361ee; --brand-dark: #0f172a; --bg-main: #f8fafc; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #334155; }
    .card-master { background: #fff; border-radius: 20px; border: 1px solid #eef2f6; box-shadow: 0 4px 20px rgba(0,0,0,0.02); overflow: hidden; }
    .table-ind { width: 100%; border-collapse: separate; border-spacing: 0; }
    .table-ind th { background: #fbfcfe; color: #64748b; padding: 18px 20px; text-transform: uppercase; font-size: 11px; letter-spacing: 1px; border-bottom: 2px solid #f1f5f9; font-weight: 800; }
    .table-ind td { padding: 18px 20px; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #1e293b; vertical-align: middle; }
    .line-badge { font-family: 'JetBrains Mono'; background: #f0f3ff; color: var(--brand-blue); padding: 6px 14px; border-radius: 10px; font-size: 12px; font-weight: 800; border: 1px solid #e0e7ff; }
    .input-ind { background: #f8fafc; border: 2px solid #eef2f6; border-radius: 12px; padding: 12px 15px; width: 100%; font-weight: 600; transition: 0.3s; }
    .input-ind:focus { border-color: var(--brand-blue); background: #fff; outline: none; box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.08); }
    .btn-action { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; border: none; font-size: 14px; transition: 0.2s; }
    .btn-edit { background: #eff6ff; color: #3b82f6; }
    .btn-delete { background: #fef2f2; color: #ef4444; }
</style>

<div class="px-2">
    <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
        <div>
            <h1 class="h3 font-weight-bold mb-1">Line <span class="text-primary">Registry</span></h1>
            <p class="text-muted small font-weight-bold uppercase mb-0">Operational Production Facility Control</p>
        </div>
        <button class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm" data-toggle="modal" data-target="#addLine" style="border-radius: 12px;">
            <i class="fas fa-plus-circle mr-2"></i> NEW LINE
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm p-3 mb-4 animate__animated animate__slideInDown" style="border-radius: 15px;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card-master animate__animated animate__fadeInUp">
        <table class="table-ind text-center">
            <thead>
                <tr>
                    <th width="25%">Identification</th>
                    <th width="35%">Operational Name</th>
                    <th width="20%">Reg. Date</th>
                    <th width="20%">Control</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $l)
                <tr>
                    <td><span class="line-badge">{{ $l->kode_Line }}</span></td>
                    <td class="text-dark font-weight-bold">{{ $l->nama_Line ?? 'NOT SET' }}</td>
                    <td class="text-muted small font-weight-bold">
                        {{ $l->created_at ? $l->created_at->format('d M Y') : '10 Apr 2026' }}
                    </td>
                    <td>
                        <div class="d-flex justify-content-center">
                            {{-- Tombol Edit --}}
                            <button class="btn-action btn-edit mr-2" data-toggle="modal" data-target="#editLine{{ $l->id }}">
                                <i class="fas fa-pen"></i>
                            </button>
                            {{-- Tombol Delete --}}
                            <form action="{{ route('line.destroy', $l->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-action btn-delete" onclick="return confirm('Hapus Line ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ✨ PINDAHKAN MODAL KE LUAR TABEL BIAR TIDAK GLITCH ✨ --}}

<div class="modal fade" id="addLine" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:24px;">
            <form action="{{ route('line.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <h5 class="font-weight-bold text-dark text-center mb-4">Register New Line</h5>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-muted uppercase">Line Code</label>
                        <input type="text" name="kode_Line" class="input-ind" placeholder="e.g. LINE-A" required>
                    </div>
                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-muted uppercase">Operational Name</label>
                        <input type="text" name="nama_Line" class="input-ind" placeholder="e.g. Stamping Line 1" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold" style="border-radius: 12px;">REGISTER TO SYSTEM</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($lines as $l)
<div class="modal fade" id="editLine{{ $l->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:24px;">
            <form action="{{ route('line.update', $l->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <h5 class="font-weight-bold text-dark text-center mb-4">Update Line Info (ID: {{ $l->id }})</h5>
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-muted uppercase">Line Code</label>
                        <input type="text" name="kode_Line" class="input-ind" value="{{ $l->kode_Line }}" required>
                    </div>
                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-muted uppercase">Line Description</label>
                        <input type="text" name="nama_Line" class="input-ind" value="{{ $l->nama_Line }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block py-3 font-weight-bold" style="border-radius: 12px;">SAVE CHANGES</button>
                    <button type="button" class="btn btn-link btn-block text-muted small mt-2" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection