@extends('layout.admin')

@section('content')
<div class="container-fluid text-dark font-weight-bold">
    @if(session('success')) <div class="alert alert-success shadow-sm border-left-success font-weight-bold">{{ session('success') }}</div> @endif

    {{-- Form Input Produksi --}}
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-header py-3 bg-white font-weight-bold text-primary uppercase small">
            <i class="fas fa-hammer mr-2"></i> Input Hasil Stamping (WIP Area)
        </div>
        <div class="card-body">
            <form action="{{ route('stamping.store') }}" method="POST">
                @csrf
                <div class="row align-items-end text-primary">
                    <div class="col-md-4">
                        <label class="small uppercase">1. Pilih Customer :</label>
                        <select id="sel_cust" class="form-control border-primary" required>
                            <option value="">-- SEMUA CUSTOMER --</option>
                            @foreach($availableCustomers as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="small uppercase">2. Pilih Part Number :</label>
                        <select name="part_no" id="sel_part" class="form-control border-primary" required disabled>
                            <option value="">-- Pilih Customer Dulu --</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small uppercase text-center d-block">3. Qty OK</label>
                        <input type="number" name="qty" class="form-control border-primary text-center font-weight-bold" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block shadow font-weight-bold uppercase"><i class="fas fa-save mr-1"></i> SIMPAN</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL RIWAYAT (YANG TADI ILANG) --}}
    <div class="card shadow mb-4 border-left-success">
        <div class="card-header py-2 bg-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-success uppercase small"><i class="fas fa-history mr-2"></i> Riwayat Stamping Hari Ini</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover text-center mb-0" style="font-size: 11px;">
                <thead class="bg-success text-white">
                    <tr><th>JAM</th><th>CUSTOMER</th><th>PART NO</th><th>PART NAME</th><th>QTY OK</th></tr>
                </thead>
                <tbody>
                    @forelse($history as $h)
                    <tr>
                        <td>{{ date('H:i', strtotime($h->created_at)) }} WIB</td>
                        <td>{{ $h->customer }}</td>
                        <td class="font-weight-bold">{{ $h->part_no }}</td>
                        <td class="text-left pl-2 text-uppercase">{{ $h->part_name }}</td>
                        <td class="font-weight-bold">{{ number_format($h->qty) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-4 text-muted italic">Belum ada hasil stamping yang diinput hari ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $('#sel_cust').on('change', function() {
        let cust = $(this).val();
        let partDrop = $('#sel_part');
        if(cust) {
            partDrop.prop('disabled', false).html('<option>Memuat Part...</option>');
            $.get('/stamping-get-parts/' + cust, function(res) {
                partDrop.html('<option value="">-- PILIH PART NUMBER --</option>');
                $.each(res, function(i, v) {
                    partDrop.append(`<option value="${v.part_no}">${v.part_no} - ${v.part_name}</option>`);
                });
            });
        }
    });
</script>
@endsection