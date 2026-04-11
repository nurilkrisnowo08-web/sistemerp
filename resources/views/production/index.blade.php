@extends('layout.admin')
@section('content')
<div class="container-fluid">
    {{-- NOTIFIKASI SUKSES/ERROR --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-left-success" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- BAGIAN 1: FORM INPUT PRODUKSI (STP & WELD) --}}
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary text-uppercase">
                <i class="fas fa-hammer mr-2"></i> Input Hasil Produksi (STP & WELD)
            </h6>
            <span class="badge badge-primary px-3 py-2 shadow-sm">
                Waktu Sistem: {{ date('H:i') }} WIB
            </span>
        </div>
        <div class="card-body">
            <form action="{{ route('production.store') }}" method="POST">
                @csrf
                <div class="row align-items-end">
                    {{-- 1. Pilih Customer --}}
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-primary text-uppercase">1. Pilih Customer</label>
                        <select id="customer_select" class="form-control border-primary shadow-sm font-weight-bold" required>
                            <option value="">-- PILIH CUSTOMER --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->customer }}">{{ $c->customer }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. Pilih Part Number (Dinamis via AJAX) --}}
                    <div class="col-md-5">
                        <label class="small font-weight-bold text-primary text-uppercase">2. Pilih Part Number</label>
                        <select name="part_no" id="part_select" class="form-control border-primary shadow-sm font-weight-bold" required disabled>
                            <option value="">-- PILIH CUSTOMER DULU --</option>
                        </select>
                    </div>

                    {{-- 3. Input Qty --}}
                    <div class="col-md-2">
                        <label class="small font-weight-bold text-primary text-uppercase">3. Qty (PCS)</label>
                        <input type="number" name="qty" class="form-control border-primary shadow-sm font-weight-bold text-center" placeholder="0" min="1" required>
                    </div>

                    {{-- Tombol Simpan --}}
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow-sm">
                            <i class="fas fa-save mr-1"></i> SIMPAN
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- BAGIAN 2: FILTER RIWAYAT PER CUSTOMER --}}
    <div class="card shadow mb-3 no-print" style="background: #f8f9fc; border-left: 5px solid #1cc88a;">
        <div class="card-body py-2">
            <form action="{{ route('production.index') }}" method="GET" class="row align-items-center">
                <div class="col-md-4">
                    <label class="small font-weight-bold mb-1 text-success text-uppercase">Filter Riwayat Per Customer :</label>
                    <select name="customer_filter" class="form-control form-control-sm font-weight-bold border-success shadow-sm" onchange="this.form.submit()">
                        <option value="">-- Tampilkan Semua Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->customer }}" {{ request('customer_filter') == $c->customer ? 'selected' : '' }}>
                                {{ $c->customer }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8 text-right pt-4">
                    <h6 class="font-weight-bold text-success m-0">
                        <i class="fas fa-history mr-2"></i> RIWAYAT PRODUKSI HARI INI
                    </h6>
                </div>
            </form>
        </div>
    </div>

    {{-- BAGIAN 3: TABEL RIWAYAT PRODUKSI --}}
    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 text-dark" style="font-size: 13px;">
                    <thead class="bg-success text-white text-center font-weight-bold text-uppercase small">
                        <tr>
                            <th width="100">Waktu</th>
                            <th>Customer</th>
                            <th>Part Number</th>
                            <th>Part Name</th>
                            <th>Qty OK</th>
                            <th width="50">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyLogs as $log)
                        <tr class="text-center">
                            {{-- Force Timezone Jakarta (WIB) --}}
                            <td class="text-muted font-italic">
                                {{ \Carbon\Carbon::parse($log->created_at, 'UTC')->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                            </td>
                            <td class="text-uppercase">{{ $log->customer }}</td>
                            <td class="font-weight-bold text-primary">{{ $log->part_no }}</td>
                            <td class="text-left small">{{ $log->part_name }}</td>
                            <td class="font-weight-bold text-success">+{{ number_format($log->qty) }} PCS</td>
                            <td>
                                {{-- Tombol Hapus & Koreksi Stok --}}
                                <form action="{{ route('production.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat ini? Stok di Monitoring FG otomatis berkurang.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm text-danger p-0">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted small">
                                <i class="fas fa-clipboard-list fa-3x mb-3 opacity-25"></i><br>
                                Belum ada riwayat masuk untuk filter ini hari ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // FUNGSI UTAMA: Mengaktifkan Dropdown Part Number
    $('#customer_select').on('change', function() {
        var customer = $(this).val();
        var partSelect = $('#part_select');

        if(customer) {
            // 1. Lepas status disabled & tampilkan status loading
            partSelect.prop('disabled', false).html('<option value="">-- SEDANG MEMUAT... --</option>');
            
            // 2. AJAX untuk tarik data part berdasarkan customer
            $.ajax({
                url: '/production/get-parts/' + customer, 
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    partSelect.html('<option value="">-- PILIH PART NUMBER --</option>');
                    $.each(data, function(key, value) {
                        partSelect.append('<option value="'+ value.part_no +'">'+ value.part_no + ' - ' + value.part_name +'</option>');
                    });
                },
                error: function() {
                    alert('Gagal mengambil data part. Periksa rute /production/get-parts/');
                }
            });
        } else {
            // Jika customer tidak dipilih, kunci lagi dropdown part-nya
            partSelect.prop('disabled', true).html('<option value="">-- PILIH CUSTOMER DULU --</option>');
        }
    });
});
</script>
@endsection