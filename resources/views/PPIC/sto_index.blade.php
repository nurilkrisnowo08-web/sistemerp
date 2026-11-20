@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-lg-4">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
    @endif

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h4 class="font-weight-bold mb-1">STOCK OPNAME (STO) HUB</h4>
            <small class="text-muted">Pencatatan fisik RM, WIP, dan Finished Goods (hanya rekap riwayat, tidak mengubah stok live).</small>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ url('/ppic/sto/print?search=' . urlencode($search ?? '') . '&customer=' . urlencode($selectedCustomer ?? '')) }}" target="_blank" class="btn btn-outline-dark btn-sm rounded-pill px-3 font-weight-bold">
                <i class="fas fa-print mr-1"></i> PRINT FORM STO
            </a>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body">
            <form method="GET" action="{{ route('ppic.sto.index') }}" class="row align-items-end">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="small font-weight-bold">Cari Part No / Nama</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ $search ?? '' }}" placeholder="Ketik Part No atau Part Name...">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="small font-weight-bold">Customer</label>
                    <select name="customer" class="form-control form-control-sm">
                        <option value="">-- Semua Customer --</option>
                        @foreach($customersList as $cust)
                            <option value="{{ $cust }}" {{ ($selectedCustomer ?? '') == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold">Filter</button>
                    <a href="{{ route('ppic.sto.index') }}" class="btn btn-light btn-sm px-3">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- FORM INPUT STO MASAL --}}
    <form method="POST" action="{{ url('/ppic/sto/store') }}">
        @csrf
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; overflow: hidden;">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">DAFTAR STOK SISTEM VS AKTUAL</span>
                <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 font-weight-bold" onclick="return confirm('Simpan hasil Stock Opname ini ke riwayat?')">
                    <i class="fas fa-save mr-1"></i> SIMPAN LAPORAN STO
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0 text-center align-middle" style="font-size: 12px;">
                    <thead class="bg-light">
                        <tr>
                            <th rowspan="2" class="align-middle">No</th>
                            <th rowspan="2" class="align-middle text-left">Part No & Name</th>
                            <th rowspan="2" class="align-middle">Cust</th>
                            <th colspan="2" class="bg-warning text-dark">RAW MATERIAL (RM)</th>
                            <th colspan="2" class="bg-info text-white">WIP WELDING</th>
                            <th colspan="2" class="bg-success text-white">FINISHED GOODS (FG)</th>
                        </tr>
                        <tr>
                            <th>Sistem</th>
                            <th>Aktual</th>
                            <th>Sistem</th>
                            <th>Aktual</th>
                            <th>Sistem</th>
                            <th>Aktual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parts as $i => $p)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-left">
                                <b>{{ $p->part_no }}</b><br>
                                <small class="text-muted">{{ $p->part_name }}</small>
                                <input type="hidden" name="part_no[]" value="{{ $p->part_no }}">
                            </td>
                            <td><span class="badge badge-light border">{{ $p->customer_code }}</span></td>

                            <td>
                                {{ number_format($p->rm_stock ?? 0) }}
                                <input type="hidden" name="rm_sys[]" value="{{ $p->rm_stock ?? 0 }}">
                            </td>
                            <td style="width: 95px;">
                                <input type="number" name="rm_act[]" class="form-control form-control-sm text-center" value="{{ $p->rm_stock ?? 0 }}">
                            </td>

                            <td>
                                {{ number_format($p->wip_stock ?? 0) }}
                                <input type="hidden" name="wip_sys[]" value="{{ $p->wip_stock ?? 0 }}">
                            </td>
                            <td style="width: 95px;">
                                <input type="number" name="wip_act[]" class="form-control form-control-sm text-center" value="{{ $p->wip_stock ?? 0 }}">
                            </td>

                            <td>
                                {{ number_format($p->fg_stock ?? 0) }}
                                <input type="hidden" name="fg_sys[]" value="{{ $p->fg_stock ?? 0 }}">
                            </td>
                            <td style="width: 95px;">
                                <input type="number" name="fg_act[]" class="form-control form-control-sm text-center" value="{{ $p->fg_stock ?? 0 }}">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="py-4 text-muted">Tidak ada data part ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    {{-- RIWAYAT STO --}}
    <div class="card border-0 shadow-sm" style="border-radius: 14px; overflow: hidden;">
        <div class="card-header bg-white font-weight-bold">RIWAYAT DOKUMEN STO</div>
        <div class="table-responsive">
            <table class="table table-sm mb-0 text-center" style="font-size: 12px;">
                <thead class="bg-light">
                    <tr>
                        <th>No Dokumen</th>
                        <th>Periode</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historySto as $h)
                    <tr>
                        <td class="font-weight-bold">{{ $h->doc_no }}</td>
                        <td>{{ $h->period }}</td>
                        <td>{{ $h->created_by }}</td>
                        <td>{{ $h->created_at }}</td>
                        <td><span class="badge badge-success">{{ $h->status }}</span></td>
                        <td>
                            <a href="{{ url('/ppic/sto/detail/' . $h->id) }}" class="btn btn-sm btn-primary rounded-pill px-3">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-3 text-muted">Belum ada riwayat STO tersimpan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection