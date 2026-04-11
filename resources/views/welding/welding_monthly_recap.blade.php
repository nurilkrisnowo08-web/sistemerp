@extends('layout.admin')

@section('content')
<style>
    /* UI MATCHING RECAP STYLE */
    .card { border-radius: 15px; border: none; }
    .filter-box { background-color: #f8f9fc; border: 1px solid #e3e6f0; border-radius: 10px; }
    .table-recap { font-size: 12px; color: #000; border-collapse: collapse; width: 100%; }
    .table-recap thead th { background-color: #2c3e50 !important; color: #fff !important; padding: 12px; border: 1px solid #444; text-transform: uppercase; }
    .table-recap td { border: 1px solid #d1d3e2; padding: 10px; vertical-align: middle; font-weight: bold; }
    .bg-header-mutasi { background-color: #4e73df !important; color: white !important; }
    .text-in { color: #1cc88a !important; }
    .text-out { color: #e74a3b !important; }
    .col-akhir { border-left: 3px solid #4e73df !important; }
</style>

<div class="container-fluid text-dark">
    {{-- 1. FILTER HEADER --}}
    <div class="card shadow-sm mb-4 filter-box">
        <div class="card-body py-3">
            <form action="{{ route('welding.recap') }}" method="GET" class="row align-items-end">
                <div class="col-md-3">
                    <label class="small font-weight-bold text-primary uppercase">Customer</label>
                    <select name="customer" class="form-control form-control-sm border-primary font-weight-bold">
                        <option value="">-- PILIH --</option>
                        @foreach($availableCustomers as $c)
                            <option value="{{ $c }}" {{ $selectedCustomer == $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold text-primary uppercase">Bulan</label>
                    <select name="month" class="form-control form-control-sm border-primary font-weight-bold">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold text-primary uppercase">Tahun</label>
                    <input type="number" name="year" class="form-control form-control-sm border-primary font-weight-bold text-center" value="{{ $year }}">
                </div>
                <div class="col-md-5 text-right">
                    <a href="{{ route('welding.index') }}" class="btn btn-secondary btn-sm font-weight-bold shadow-sm mr-1">
                        <i class="fas fa-arrow-left mr-1"></i> KEMBALI
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold shadow-sm mr-1">
                        TAMPILKAN DATA
                    </button>
                    <button type="button" onclick="window.print()" class="btn btn-dark btn-sm font-weight-bold shadow-sm">
                        <i class="fas fa-print mr-1"></i> CETAK REKAP
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. TITLE SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-layer-group mr-2"></i>1. REKAP MUTASI BULANAN: {{ $selectedCustomer ?? 'SEMUA CUSTOMER' }}
        </h6>
        <span class="badge badge-primary px-3 py-2 shadow-sm uppercase font-weight-bold" style="font-size: 12px;">
            {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
        </span>
    </div>

    {{-- 3. TABEL REKAP UTAMA --}}
    <div class="card shadow border-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table-recap text-center mb-0">
                <thead>
                    <tr>
                        <th rowspan="2" width="150">PART NO</th>
                        <th rowspan="2">PART NAME</th>
                        <th colspan="3" class="bg-header-mutasi">ALUR MUTASI STOK (PCS)</th>
                        <th rowspan="2" width="120" class="col-akhir">AKHIR</th>
                    </tr>
                    <tr>
                        <th width="100" style="background:#5a5c69 !important;">AWAL</th>
                        <th width="100" style="background:#f8f9fc !important; color:#000 !important;">TOTAL IN</th>
                        <th width="100" style="background:#f8f9fc !important; color:#000 !important;">TOTAL OUT</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($recapData as $r)
                    <tr>
                        <td class="text-primary text-uppercase">{{ $r->part_no }}</td>
                        <td class="text-left pl-3 small text-uppercase">{{ $r->part_name }}</td>
                        <td class="bg-light">{{ number_format($r->stok_awal) }}</td>
                        <td class="text-in">+{{ number_format($r->total_in) }}</td>
                        <td class="text-out">-{{ number_format($r->total_out) }}</td>
                        <td class="col-akhir text-primary font-weight-bolder" style="font-size: 14px;">
                            {{ number_format($r->stok_akhir) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-5 text-muted italic">Tidak ada data mutasi untuk periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection