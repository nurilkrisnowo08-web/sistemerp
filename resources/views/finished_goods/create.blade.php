@extends('layout.admin')
@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 mb-0 text-gray-800 font-weight-bold text-uppercase">
            <i class="fas fa-plus-circle mr-2"></i> Tambah Master Monitoring
        </h1>
    </div>

    <div class="card shadow mb-4 border-left-primary">
        <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary text-uppercase">
                <i class="fas fa-edit mr-2"></i> Form Input Part Baru
            </h6>
            <a href="{{ route('fg.index') }}" class="btn btn-secondary btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> KEMBALI
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('fg.store') }}" method="POST">
                @csrf
                <div class="row">
                    {{-- 1. PILIH CUSTOMER --}}
                    <div class="col-md-6 form-group">
                        <label class="small font-weight-bold text-primary text-uppercase">Pilih Customer</label>
                        <select name="customer" id="customer_select_fg" class="form-control border-primary shadow-sm font-weight-bold" required>
                            <option value="">-- PILIH CUSTOMER --</option>
                            @foreach($customers as $c)
                                {{-- SAKTI: Gunakan Code untuk filter AJAX agar sinkron --}}
                                <option value="{{ $c->code }}">{{ $c->code }} - {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. PILIH PART NO (Dinamis via AJAX) --}}
                    <div class="col-md-6 form-group">
                        <label class="small font-weight-bold text-primary text-uppercase">Part No</label>
                        <select name="part_no" id="part_no_select_fg" class="form-control border-primary shadow-sm font-weight-bold" required disabled>
                            <option value="">-- Pilih Customer Terlebih Dahulu --</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    {{-- 3. NAMA PART (Otomatis Terisi) --}}
                    <div class="col-md-12 form-group">
                        <label class="small font-weight-bold text-primary text-uppercase">Part Name</label>
                        <input type="text" name="part_name" id="part_name_fg" class="form-control bg-light font-weight-bold" placeholder="Nama part akan terisi otomatis..." readonly required>
                        <small class="text-info font-italic">*Nama part akan terisi otomatis berdasarkan Part No yang dipilih.</small>
                    </div>
                </div>

                <hr class="my-4">

                {{-- BARIS BARU: STOK, TARGET, & LIMIT --}}
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="small font-weight-bold text-primary text-uppercase">Actual Stock Awal</label>
                        <input type="number" name="actual_stock" class="form-control border-primary text-center font-weight-bold shadow-sm" value="0" min="0" required>
                    </div>

                    <div class="col-md-4 form-group">
                        <label class="small font-weight-bold text-primary text-uppercase">Delivery / Day</label>
                        <input type="number" name="needs_per_day" class="form-control border-primary text-center font-weight-bold shadow-sm" placeholder="Target per Hari" min="1" required>
                    </div>

                    {{-- FIX SQL ERROR: Tambahkan Qty / Pallet agar tidak Error 1364 lagi! --}}
                    <div class="col-md-4 form-group">
                        <label class="small font-weight-bold text-dark text-uppercase">Qty / Pallet</label>
                        <input type="number" name="qty_per_pallet" class="form-control border-dark text-center font-weight-bold shadow-sm" value="50" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="small font-weight-bold text-success text-uppercase">Min Stock (PCS)</label>
                        <input type="number" name="min_stock_pcs" class="form-control border-success text-center font-weight-bold shadow-sm" placeholder="200" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="small font-weight-bold text-danger text-uppercase">Max Stock (PCS)</label>
                        <input type="number" name="max_stock_pcs" class="form-control border-danger text-center font-weight-bold shadow-sm" placeholder="500" required>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow py-3">
                        <i class="fas fa-save mr-2"></i> SIMPAN KE MASTER MONITORING
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // 1. AJAX: Munculkan List Part saat Customer dipilih
    $('#customer_select_fg').on('change', function() {
        var customer = $(this).val();
        var partSelect = $('#part_no_select_fg');
        var nameInput = $('#part_name_fg');

        if(customer) {
            partSelect.prop('disabled', false).html('<option value="">-- MEMUAT DATA... --</option>');
            nameInput.val('');

            $.ajax({
                url: '/production/get-parts/' + customer, 
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    partSelect.html('<option value="">-- PILIH PART NUMBER --</option>');
                    if(data.length > 0) {
                        $.each(data, function(key, value) {
                            partSelect.append('<option value="'+ value.part_no +'" data-name="'+ value.part_name +'">'+ value.part_no + ' - ' + value.part_name +'</option>');
                        });
                    } else {
                        partSelect.html('<option value="">-- TIDAK ADA DATA PART UNTUK CUSTOMER INI --</option>');
                    }
                },
                error: function() {
                    // SAKTI: Jika masih "Memuat", biasanya Route atau Fungsi di Controller Belum Dibuat!
                    partSelect.html('<option value="">-- EROR: ROUTE TIDAK DITEMUKAN --</option>');
                    alert('Guru, pastikan Route /production/get-parts sudah ada di web.php!');
                }
            });
        } else {
            partSelect.prop('disabled', true).html('<option value="">-- Pilih Customer Terlebih Dahulu --</option>');
            nameInput.val('');
        }
    });

    // 2. Pas Part No dipilih, otomatis isi Nama Part-nya
    $('#part_no_select_fg').on('change', function() {
        var selectedName = $(this).find(':selected').data('name');
        $('#part_name_fg').val(selectedName || '');
    });
});
</script>
@endsection