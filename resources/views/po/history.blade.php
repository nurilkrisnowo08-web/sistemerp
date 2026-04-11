@extends('layout.admin')

@section('content')
<div class="container-fluid">
    {{-- 1. HEADER & FILTER --}}
    <div class="card shadow mb-4 no-print border-left-success">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-6 text-left">
                    <h1 class="h4 mb-0 text-success font-weight-bold">
                        <i class="fas fa-archive mr-2"></i> ARSIP PO SELESAI
                    </h1>
                </div>
                <div class="col-md-6 text-right">
                    <form action="{{ route('po.history') }}" method="GET" class="d-flex justify-content-end align-items-center">
                        <label class="font-weight-bold text-success mr-2 mb-0 small">FILTER CUSTOMER :</label>
                        <select name="customer" class="form-control form-control-sm font-weight-bold border-success shadow-sm" 
                                style="width: 200px;" onchange="this.form.submit()">
                            <option value="">-- Tampilkan Semua --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->code }}" {{ request('customer') == $c->code ? 'selected' : '' }}>
                                    {{ $c->code }} - {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        <a href="{{ url('/po-customer') }}" class="btn btn-primary btn-sm ml-2 font-weight-bold shadow-sm">
                             <i class="fas fa-arrow-left mr-1"></i> KEMBALI
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. TAMPILAN DATA PER CUSTOMER --}}
    @forelse($purchaseOrders->groupBy('customer_code') as $customerCode => $allOrdersByCustomer)
        <div class="card shadow mb-4 border-left-success">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h6 class="m-0 font-weight-bold text-success text-uppercase small">
                    <i class="fas fa-building mr-2"></i> {{ $customerCode }} 
                </h6>
                <span class="badge badge-success px-3 shadow-sm">
                    {{ $allOrdersByCustomer->groupBy('po_number')->count() }} Nomor PO Selesai
                </span>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 text-dark" style="font-size: 13px;">
                        <thead class="bg-light text-center font-weight-bold text-uppercase small">
                            <tr>
                                <th width="40"></th>
                                <th>NOMOR PO</th>
                                <th>JUMLAH ITEM</th>
                                <th>CLOSED DATE</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allOrdersByCustomer->groupBy('po_number') as $poNumber => $items)
                            <tr class="font-weight-bold text-center" style="cursor: pointer;" data-toggle="collapse" data-target="#detail-{{ Str::slug($customerCode.'-'.$poNumber) }}">
                                <td class="text-success"><i class="fas fa-check-circle"></i></td>
                                <td class="text-left">{{ $poNumber }}</td>
                                <td>{{ $items->count() }} Part</td>
                                <td class="text-muted font-italic">
                                    {{-- SAKTI 1: Tambahkan ->first() biar gak error Property [updated_at] lagi! --}}
                                    {{ \Carbon\Carbon::parse($items->first()->updated_at)->format('d/m/Y') }}
                                </td>
                                <td>
                                    <span class="badge badge-outline-success border px-2 py-1 shadow-sm uppercase">ARSIP <i class="fas fa-chevron-down ml-1 small"></i></span>
                                </td>
                            </tr>

                            <tr id="detail-{{ Str::slug($customerCode.'-'.$poNumber) }}" class="collapse bg-white no-print">
                                <td colspan="5" class="p-0">
                                    <div class="px-5 py-3 border-left-success border-bottom shadow-inner bg-light text-left">
                                        <table class="table table-sm table-bordered mb-0 bg-white shadow-sm" style="font-size: 11px;">
                                            <thead class="bg-dark text-white text-center">
                                                <tr>
                                                    <th>PART NUMBER</th>
                                                    <th>QTY PESANAN</th>
                                                    <th>TOTAL TERKIRIM</th>
                                                    <th>BALANCE</th>
                                                    <th>KETERANGAN</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($items as $po)
                                                @php 
                                                    /** * SAKTI 2: Logika Anti-Zonk untuk Item Bawah
                                                     * Karena di DB lo semua log pake po_id=1, kita harus cari
                                                     * berdasarkan ID pertama di grup ini dan filter spesifik per PART NUMBER.
                                                     */
                                                    $firstIdInGroup = $items->first()->id; 
                                                    $realSent = DB::table('deliveries')
                                                                ->where('po_id', $firstIdInGroup)
                                                                ->where('part_no', trim($po->part_no))
                                                                ->sum('qty_delivery'); 
                                                    
                                                    $balance = $po->quantity - $realSent;
                                                    
                                                    // SAKTI 3: Mapping Badge Warna agar sinkron sama monitoring
                                                    $ket = $po->jenis_po ?? $po->keterangan ?? 'REGULER';
                                                    $badgeStyle = 'badge-light border';
                                                    if($ket == 'URGENT / TAMBAHAN') $badgeStyle = 'badge-danger';
                                                    if($ket == 'TESTING') $badgeStyle = 'badge-info';
                                                @endphp
                                                <tr class="text-center">
                                                    <td class="text-left font-weight-bold pl-3 text-primary text-uppercase">{{ $po->part_no }}</td>
                                                    <td class="font-weight-bold text-dark">{{ number_format($po->quantity) }}</td>
                                                    <td class="text-success font-weight-bold">
                                                        {{ number_format($realSent) }}
                                                    </td>
                                                    <td class="{{ $balance > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                                        {{ number_format($balance) }}
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $badgeStyle }} text-uppercase shadow-sm">{{ $ket }}</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5 card shadow border-0">
            <div class="card-body">
                <i class="fas fa-search fa-3x text-gray-200 mb-3"></i>
                <h5 class="text-gray-400 font-weight-bold">Tidak ada riwayat PO selesai, Guru.</h5>
            </div>
        </div>
    @endforelse
</div>
@endsection