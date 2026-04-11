@extends('layout.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 mb-0 text-gray-800 font-weight-bold">Edit Data Customer</h1>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Customer Code</label>
                        <input type="text" name="code" class="form-control" value="{{ $customer->code }}" required>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="font-weight-bold">Customer Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="font-weight-bold">Address</label>
                    <textarea name="address" class="form-control" rows="3">{{ $customer->address }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">PIC</label>
                        <input type="text" name="pic" class="form-control" value="{{ $customer->pic }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="{{ $customer->phone }}">
                    </div>
                </div>

                <hr>
                <button type="submit" class="btn btn-warning px-4 font-weight-bold text-dark">
                    <i class="fas fa-edit mr-2"></i> Perbarui Data
                </button>
            </form>
        </div>
    </div>
</div>
@endsection