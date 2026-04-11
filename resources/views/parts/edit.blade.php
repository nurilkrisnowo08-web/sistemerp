@extends('layout.admin')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary">
            <h6 class="m-0 font-weight-bold text-white">Edit Part: {{ $part->part_no }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('parts.update', $part->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Part No</label>
                        <input type="text" name="part_no" class="form-control" value="{{ $part->part_no }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold">Part Name</label>
                        <input type="text" name="part_name" class="form-control" value="{{ $part->part_name }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold">Customer</label>
                        <select name="customer_code" class="form-control" required>
                            @foreach($customers as $c)
                                <option value="{{ $c->code }}" {{ $part->customer_code == $c->code ? 'selected' : '' }}>
                                    {{ $c->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <hr>
                <button type="submit" class="btn btn-success font-weight-bold">UPDATE PART</button>
                <a href="{{ route('parts.index') }}" class="btn btn-secondary">BATAL</a>
            </form>
        </div>
    </div>
</div>
@endsection