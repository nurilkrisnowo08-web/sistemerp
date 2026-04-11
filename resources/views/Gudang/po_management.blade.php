@extends('layout.admin')

@section('content')
<div class="container-fluid text-dark small">
    {{-- 1. HEADER & AUTO-PO BUTTON --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="font-weight-bold text-primary mb-0 uppercase">Raw Material Incoming (Ruang Penerimaan)</h5>
            <p class="text-muted small">Kelola PO Supplier & Penerimaan Material berdasarkan Shortage</p>
        </div>
        {{-- Tombol Sakti untuk generate PO Otomatis berdasarkan Shortage --}}
        <form action="{{ route('rm.auto_po') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-dark shadow-sm font-weight-bold">
                <i class="fas fa-magic mr-2"></i>GENERATE AUTO-PO (FROM SHORTAGE)
            </button>
        </form>
    </div>

    {{-- 2. DAFTAR PO SUPPLIER (OPEN) --}}
    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-gradient-success py-3 d-flex justify-content-between align-items-center border-0 text-white font-weight-bold">
            <h6 class="m-0 uppercase small">Outstanding PO Supplier (Pending Shipment)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 text-center" style="font-size: 11px;">
                    <thead class="bg-light text-success font-weight-bold">
                        <tr>
                            <th>TGL PO</th>
                            <th>NO PO SUPPLIER</th>
                            <th class="text-dark">FOR CUSTOMER</th> {{-- ✨ Penyesuaian: Kolom Identitas Customer --}}
                            <th>SUPPLIER</th>
                            <th>TOTAL ITEM</th>
                            <th>STATUS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="font-weight-bold text-dark">
                        @forelse($supplierPos as $po)
                        <tr>
                            <td class="align-middle">{{ date('d/m/Y', strtotime($po->created_at)) }}</td>
                            <td class="align-middle text-primary">{{ $po->no_po_supplier }}</td>
                            {{-- ✨ Penyesuaian: Menampilkan Kode Customer agar PDF bapak nanti jelas tujuannya --}}
                            <td class="align-middle"><span class="badge badge-dark px-2">{{ $po->customer_code ?? 'N/A' }}</span></td>
                            <td class="align-middle">{{ $po->supplier_name }}</td>
                            <td class="align-middle">{{ $po->items_count }} Items</td>
                            <td class="align-middle">
                                <span class="badge badge-warning px-3 shadow-sm">{{ $po->status }}</span>
                            </td>
                            <td class="align-middle">
                                <button class="btn btn-success btn-sm font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalReceive{{ $po->id }}">
                                    <i class="fas fa-truck-loading mr-2"></i>TERIMA BARANG
                                </button>
                            </td>
                        </tr>

                        {{-- MODAL TERIMA BARANG PER PO (TETAP UTUH!) --}}
                        <div class="modal fade" id="modalReceive{{ $po->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg text-left">
                                    <div class="modal-header bg-success text-white border-0">
                                        <h6 class="modal-title font-weight-bold text-uppercase small">Penerimaan Barang (Customer: {{ $po->customer_code ?? 'N/A' }})</h6>
                                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                    </div>
                                    <form action="{{ route('rm.receive') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="supplier_po_id" value="{{ $po->id }}">
                                        <div class="modal-body bg-light">
                                            <table class="table table-bordered bg-white small">
                                                <thead class="bg-gray-100 text-center">
                                                    <tr>
                                                        <th>PART CODE</th>
                                                        <th>QTY ORDER</th>
                                                        <th>SUDAH DATANG</th>
                                                        <th class="bg-success text-white">DATANG HARI INI (PCS)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($po->items as $item)
                                                    <tr>
                                                        <td class="align-middle font-weight-bold text-primary">{{ $item->material_code }}</td>
                                                        <td class="align-middle text-center">{{ number_format($item->qty_order) }}</td>
                                                        <td class="align-middle text-center">{{ number_format($item->qty_received) }}</td>
                                                        <td class="align-middle p-1">
                                                            <input type="number" name="items[{{ $item->id }}][qty_in]" 
                                                                   class="form-control form-control-sm text-center font-weight-bold border-success" 
                                                                   max="{{ $item->qty_order - $item->qty_received }}"
                                                                   placeholder="0">
                                                            <input type="hidden" name="items[{{ $item->id }}][material_code]" value="{{ $item->material_code }}">
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="modal-footer border-0 bg-white">
                                            <button type="submit" class="btn btn-success btn-block font-weight-bold shadow">KONFIRMASI TERIMA & UPDATE STOK RM</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="7" class="py-5 text-muted italic">Tidak ada Outstanding PO. Klik "Generate Auto-PO" jika ada shortage.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection