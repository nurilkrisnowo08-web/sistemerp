@extends('layout.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 mb-0 text-gray-800 font-weight-bold">Tambah Customer Baru</h1>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Data Pelanggan</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Customer Code</label>
                        <input type="text" name="code" class="form-control" placeholder="Contoh: AMA-P2" required>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="font-weight-bold">Customer Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Nama Perusahaan" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="font-weight-bold">Address (Alamat)</label>
                    <textarea name="address" class="form-control" rows="3" placeholder="Alamat Lengkap Kantor/Plant"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">PIC (Person in Charge)</label>
                        <input type="text" name="pic" class="form-control" placeholder="Nama Kontak Person">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="Nomor Telepon">
                    </div>
                </div>

                <hr>
                <button type="submit" class="btn btn-primary px-4 font-weight-bold">
                    <i class="fas fa-save mr-2"></i> Simpan Data Customer
                </button>
            </form>
        </div>
    </div>
</div>
@endsection