@extends('layout.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 mb-0 text-gray-800 font-weight-bold">Tambah Part Baru</h1>
        <a href="{{ route('parts.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('parts.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Part No</label>
                        <input type="text" name="part_no" class="form-control" placeholder="Contoh: 61737-BZ060-H" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Part Name</label>
                        <input type="text" name="part_name" class="form-control" placeholder="Nama Barang" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Customer</label>
                        <select name="customer_code" class="form-control" required>
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->code }}">{{ $c->code }} - {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr>
                <button type="submit" class="btn btn-primary px-4 font-weight-bold">
                    <i class="fas fa-save mr-2"></i> Simpan Part
                </button>
            </form>
        </div>
    </div>
</div>
@endsection