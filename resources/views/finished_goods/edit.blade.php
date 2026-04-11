@extends('layout.admin')

@section('content')
<div class="container-fluid text-dark">
    <div class="card shadow mb-4 border-left-warning">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h6 class="m-0 font-weight-bold text-warning uppercase">
                <i class="fas fa-edit mr-2"></i>Edit Master Part: {{ $fg->part_no }}
            </h6>
            {{-- Sesuaikan redirect agar kembali ke customer yang sedang dikerjakan --}}
            <a href="{{ route('fg.index', ['customer' => $fg->customer]) }}" class="btn btn-sm btn-light border font-weight-bold shadow-sm">
                KEMBALI
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('fg.update', $fg->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold">PART NO</label>
                        {{-- TAMBAHKAN name="part_no" AGAR TIDAK ERROR NULL --}}
                        <input type="text" name="part_no" class="form-control bg-light" value="{{ $fg->part_no }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold">PART NAME</label>
                        <input type="text" name="part_name" class="form-control" value="{{ $fg->part_name }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="small font-weight-bold text-primary">MIN STOCK (PCS)</label>
                        <input type="number" name="min_stock_pcs" class="form-control border-primary" value="{{ $fg->min_stock_pcs }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="small font-weight-bold text-success">MAX STOCK (PCS)</label>
                        <input type="number" name="max_stock_pcs" class="form-control border-success" value="{{ $fg->max_stock_pcs }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="small font-weight-bold text-danger">ACTUAL STOCK (GUDANG FG)</label>
                        <input type="number" name="actual_stock" class="form-control border-danger font-weight-bold" value="{{ $fg->actual_stock }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="small font-weight-bold text-info">Delivery / DAY (DOP)</label>
                        <input type="number" step="0.01" name="needs_per_day" class="form-control border-info" value="{{ $fg->needs_per_day }}" required>
                    </div>
                    {{-- Input Hidden untuk Customer agar redirect balik ke filter yang benar --}}
                    <input type="hidden" name="customer" value="{{ $fg->customer }}">
                </div>

                <hr>
                <div class="text-right">
                    <button type="submit" class="btn btn-warning px-5 font-weight-bold shadow">
                        SIMPAN PERUBAHAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection