@extends('layout.admin')

@section('content')
<style>
    /* UI KHUSUS REKAP HARIAN */
    .card { border-radius: 15px; border: none; }
    .table-recap { font-size: 12px; color: #000; border-collapse: collapse; width: 100%; }
    .table-recap thead th { background-color: #2c3e50 !important; color: #fff !important; padding: 12px; border: 1px solid #444; text-transform: uppercase; }
    .table-recap td { border: 1px solid #d1d3e2; padding: 10px; vertical-align: middle; font-weight: bold; }
    .bg-header-mutasi { background-color: #4e73df !important; color: white !important; }
    .text-in { color: #1cc88a !important; }
    .text-out { color: #e74a3b !important; }
    @media print { .no-print { display: none; } .card { shadow: none; border: none; } }
</style>

<div class="container-fluid text-dark">
    {{-- 1. FILTER HEADER --}}
    <div class="card shadow-sm mb-4 bg-light no-print">
        <div class="card-body py-3">
            <form action="{{ route('welding.daily_recap') }}" method="GET" class="row align-items-end">
                <div class="col-md-3">
                    <label class="small font-weight-bold text-primary uppercase">Pilih Tanggal</label>
                    <input type="date" name="date" class="form-control form-control-sm border-primary font-weight-bold" value="{{ $date }}">
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold text-primary uppercase">Customer</label>
                    <select name="customer" class="form-control form-control-sm border-primary font-weight-bold">
                        <option value="">-- SEMUA CUSTOMER --</option>
                        @foreach($availableCustomers as $c)
                            <option value="{{ $c }}" {{ $selectedCustomer == $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 text-right">
                    <a href="{{ route('welding.index') }}" class="btn btn-secondary btn-sm font-weight-bold mr-1">
                        <i class="fas fa-arrow-left mr-1"></i> KEMBALI
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold mr-1">
                        TAMPILKAN
                    </button>
                    <button type="button" onclick="window.print()" class="btn btn-dark btn-sm font-weight-bold">
                        <i class="fas fa-print mr-1"></i> PRINT LAPORAN
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. JUDUL LAPORAN --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="m-0 font-weight-bold text-primary uppercase">
            <i class="fas fa-clipboard-list mr-2"></i>REKAP MUTASI HARIAN WELDING
        </h6>
        <span class="badge badge-dark px-3 py-2 font-weight-bold">
            TANGGAL: {{ \Carbon\Carbon::parse($date)->format('d F Y') }}
        </span>
    </div>

    {{-- 3. TABEL MUTASI HARIAN --}}
    <div class="card shadow border-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table-recap text-center mb-0">
                <thead>
                    <tr>
                        <th rowspan="2" width="150">PART NO</th>
                        <th rowspan="2">PART NAME</th>
                        <th colspan="3" class="bg-header-mutasi">MUTASI STOK (PCS)</th>
                        <th rowspan="2" width="120" style="background:#2c3e50; border-left: 3px solid #4e73df;">AKHIR</th>
                    </tr>
                    <tr>
                        <th width="100" style="background:#5a5c69 !important;">AWAL</th>
                        <th width="100" style="background:#f8f9fc !important; color:#000 !important;">IN (STP)</th>
                        <th width="100" style="background:#f8f9fc !important; color:#000 !important;">OUT (FG)</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($dailyData as $r)
                    <tr>
                        <td class="text-primary text-uppercase">{{ $r->part_no }}</td>
                        <td class="text-left pl-3 small text-uppercase">{{ $r->part_name }}</td>
                        <td class="bg-light">{{ number_format($r->stok_awal) }}</td>
                        <td class="text-in">+{{ number_format($r->total_in) }}</td>
                        <td class="text-out">-{{ number_format($r->total_out) }}</td>
                        <td class="text-primary font-weight-bolder" style="font-size: 14px; border-left: 2px solid #4e73df;">
                            {{ number_format($r->stok_akhir) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-5 text-muted italic">Tidak ada data transaksi pada tanggal ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection