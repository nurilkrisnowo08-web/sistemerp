@extends('layout.admin')
@section('content')
<style>
    /* 1. UI WEB MODERN (DASHBOARD VIEW) */
    .container-fluid { background-color: #f8fafc; color: #334155; }
    .card-report { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; }
    .table-web { width: 100%; border-collapse: collapse; font-size: 13px; font-weight: 700; text-align: center; }
    .table-web thead th { background-color: #1e293b !important; color: white !important; padding: 12px; border: 1px solid #334155; }
    .table-web td { padding: 10px; border: 1px solid #e2e8f0; }

    /* WARNA ALUR MUTASI */
    .bg-awal { background-color: #f8f9fc; color: #64748b !important; }
    .bg-in { background-color: #ecfdf5; color: #10b981 !important; }
    .bg-out { background-color: #fef2f2; color: #ef4444 !important; }
    .bg-akhir { background-color: #eff6ff; color: #3b82f6 !important; border-left: 3px solid #3b82f6 !important; }

    /* CSS PAS PRINT (BACKUP JIKA CTRL+P) */
    @media print {
        @page { size: A4 landscape; margin: 0; }
        .no-print { display: none !important; }
        body { margin: 1.5cm; background: #fff !important; }
    }
</style>

<div class="container-fluid py-4">
    {{-- FILTER AREA (NO PRINT) --}}
    <div class="card card-report mb-4 no-print border-left-primary shadow-sm">
        <div class="card-body py-3">
            <form action="{{ route('fg.recap') }}" method="GET" class="row align-items-end">
                <div class="col-md-3"><label class="small font-weight-bold text-primary">CUSTOMER</label>
                    <select name="customer" class="form-control form-control-sm font-weight-bold" required>
                        <option value="">-- PILIH --</option>
                        @foreach($customers as $c) 
                            <option value="{{ $c->code }}" {{ request('customer') == $c->code ? 'selected' : '' }}>{{ $c->code }}</option> 
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><label class="small font-weight-bold text-primary text-uppercase">Bulan</label>
                    <select name="month" class="form-control form-control-sm">
                        @for($m=1; $m<=12; $m++) <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option> @endfor
                    </select>
                </div>
                <div class="col-md-2"><label class="small font-weight-bold text-primary text-uppercase">Tahun</label>
                    <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}">
                </div>
                <div class="col-md-5 text-right">
                    {{-- TOMBOL KEMBALI --}}
                    <a href="{{ route('fg.index') }}" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow mr-1">
                        <i class="fas fa-arrow-left mr-1"></i> KEMBALI
                    </a>
                    
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-4 shadow">TAMPILKAN DATA</button>
                    
                    {{-- TOMBOL CETAK --}}
                    @if(request('customer'))
                        <a href="{{ route('fg.print', array_merge(request()->all(), ['type' => 'monthly'])) }}" target="_blank" class="btn btn-dark btn-sm font-weight-bold px-3 shadow ml-1">
                            <i class="fas fa-print mr-1"></i> CETAK REKAP
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if(request('customer'))
        {{-- 1. TABEL REKAPITULASI BULANAN --}}
        <div class="card card-report mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary small uppercase"><i class="fas fa-layer-group mr-2"></i>1. REKAP MUTASI BULANAN: {{ request('customer') }}</h6>
                <span class="badge badge-primary px-3 py-2">{{ date('F Y', mktime(0,0,0,$month,1,$year)) }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table-web">
                        <thead>
                            <tr>
                                <th rowspan="2">PART NO</th><th rowspan="2">PART NAME</th><th colspan="4" style="background-color: #334155 !important;">ALUR MUTASI STOK (PCS)</th>
                            </tr>
                            <tr>
                                <th class="bg-awal" style="color:#000;">AWAL</th>
                                <th class="bg-in" style="color:#000;">TOTAL IN</th>
                                <th class="bg-out" style="color:#000;">TOTAL OUT</th>
                                <th class="bg-akhir" style="color:#000;">AKHIR</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recap as $r)
                            <tr>
                                <td class="text-primary font-weight-bold">{{ $r->part_no }}</td>
                                <td class="text-left pl-3 text-uppercase small">{{ $r->part_name }}</td>
                                {{-- Fix undefined stock_awal --}}
                                <td class="bg-awal">{{ number_format($r->stock_awal) }}</td>
                                <td class="bg-in">+{{ number_format($r->total_in) }}</td>
                                <td class="bg-out">-{{ number_format($r->total_out) }}</td>
                                <td class="bg-akhir font-weight-bold">{{ number_format($r->stock_akhir) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 2. TABEL RINCIAN HARIAN --}}
        <div class="card card-report">
            <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold small uppercase"><i class="fas fa-history mr-2"></i>2. RINCIAN MUTASI HARIAN</h6>
                <div class="d-flex align-items-center no-print">
                    <form action="{{ route('fg.recap') }}" method="GET" class="form-inline mr-3">
                        <input type="hidden" name="customer" value="{{ request('customer') }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <label class="mr-2 small font-weight-bold text-white">TANGGAL:</label>
                        <input type="date" name="daily_date" class="form-control form-control-sm" value="{{ $daily_date }}" onchange="this.form.submit()">
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table-web">
                        <thead class="bg-secondary text-white">
                            <tr><th>TANGGAL</th><th>JAM</th><th>PART NUMBER</th><th>IN (FG)</th><th>OUT (DELV)</th></tr>
                        </thead>
                        <tbody>
                            @forelse($dailyDetails as $d)
                            <tr>
                                <td>{{ date('d/m/Y', strtotime($d->tgl)) }}</td>
                                <td class="small font-weight-normal">{{ date('H:i', strtotime($d->jam)) }}</td>
                                <td class="text-primary font-weight-bold">{{ $d->part_no }}</td>
                                <td class="bg-in">+{{ number_format($d->in_qty) }}</td>
                                <td class="bg-out">-{{ number_format($d->out_qty) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="py-4 text-muted italic font-weight-normal">Tidak ada mutasi di tanggal {{ date('d/m/Y', strtotime($daily_date)) }}.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection