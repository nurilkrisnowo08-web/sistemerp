@extends('layout.admin')

@section('content')

{{-- 1. HEADER & FILTER (CUSTOMER + RENTANG TANGGAL) --}}
<div class="card shadow mb-4 border-left-success no-print">
    <div class="card-body py-3">
        <form action="{{ route('delivery.history') }}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="small font-weight-bold text-success text-uppercase">Pilih Customer :</label>
                    <select name="customer_code" class="form-control form-control-sm font-weight-bold border-success shadow-sm">
                        <option value="">-- Semua Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->code }}" {{ request('customer_code') == $c->code ? 'selected' : '' }}>
                                {{ $c->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold text-success text-uppercase">Dari Tanggal :</label>
                    <input type="date" name="start_date" class="form-control form-control-sm border-success shadow-sm" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold text-success text-uppercase">Sampai Tanggal :</label>
                    <input type="date" name="end_date" class="form-control form-control-sm border-success shadow-sm" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3 d-flex">
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold shadow-sm mr-2 flex-grow-1">
                        <i class="fas fa-search mr-1"></i> CARI DATA
                    </button>
                    <a href="{{ route('delivery.index') }}" class="btn btn-sm btn-light border font-weight-bold shadow-sm text-primary">
                        KEMBALI
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ALERT SUCCESS & ERROR --}}
<div class="px-2">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-lg" style="border-left: 5px solid #10b981 !important;">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-lg" style="border-left: 5px solid #ef4444 !important;">
            <strong>Error!</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
</div>

{{-- 2. TABEL HISTORY UTAMA --}}
@if(request('customer_code') || (request('start_date') && request('end_date')))
    <div class="card shadow mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h6 class="m-0 font-weight-bold text-success text-uppercase small">
                <i class="fas fa-history mr-2"></i> History Surat Jalan: {{ request('customer_code') ?: 'Semua' }}
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-dark mb-0" style="font-size: 13px;">
                    <thead class="bg-light text-center font-weight-bold text-uppercase small">
                        <tr>
                            <th width="40"></th> 
                            <th>No Surat Jalan</th>
                            <th>Customer</th>
                            <th>Jumlah Item</th>
                            <th>Tanggal Terbit</th>
                            <th width="300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $no_sj => $items)
                        @php
                            $poNumber = DB::table('purchase_orders')->where('id', $items->first()->po_id)->value('po_number');
                        @endphp
                        
                        <tr class="text-center" style="cursor: pointer;" data-toggle="collapse" data-target="#details-{{ Str::slug($no_sj) }}">
                            <td class="text-success"><i class="fas fa-plus-circle"></i></td>
                            <td class="font-weight-bold text-primary">
                                {{ $no_sj }}
                                {{-- ✨ LABEL REVISI MUNCUL DI BAWAH NOMOR SJ KALAU ADA --}}
                                @if($items->first()->revision_count > 0)
                                    <br><span class="badge badge-danger shadow-sm mt-1" style="font-size: 10px;"><i class="fas fa-exclamation-triangle mr-1"></i> Direvisi {{ $items->first()->revision_count }}x</span>
                                @endif
                            </td>
                            <td class="text-uppercase">{{ $items->first()->customer_code }}</td>
                            <td><span class="badge badge-pill badge-info px-3">{{ $items->count() }} Item</span></td>
                            
                            {{-- FIX TANGGAL: Menggunakan format standar agar tidak melesat ke besok --}}
                            <td>{{ \Carbon\Carbon::parse($items->first()->created_at)->format('d/m/Y') }}</td>
                            
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('delivery.print', urlencode($no_sj)) }}" class="btn btn-info btn-sm font-weight-bold shadow-sm" target="_blank">
                                        <i class="fas fa-print mr-1"></i> SJ
                                    </a>
                                    <a href="{{ route('delivery.label', urlencode($no_sj)) }}" class="btn btn-warning btn-sm font-weight-bold shadow-sm text-dark" target="_blank">
                                        <i class="fas fa-tag mr-1"></i> LABEL
                                    </a>
                                    @if($poNumber)
                                        <a href="{{ route('delivery.print-rekap-po', urlencode($poNumber)) }}" class="btn btn-primary btn-sm font-weight-bold shadow-sm" target="_blank">
                                            <i class="fas fa-file-invoice mr-1"></i> REKAP
                                        </a>
                                    @else
                                        <button class="btn btn-secondary btn-sm font-weight-bold shadow-sm" disabled title="Nomor PO tidak ditemukan">
                                            <i class="fas fa-exclamation-circle mr-1"></i> NO PO
                                        </button>
                                    @endif
                                    
                                    {{-- ✨ TOMBOL REVISI --}}
                                    <button class="btn btn-danger btn-sm font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalRevisi-{{ Str::slug($no_sj) }}">
                                        <i class="fas fa-edit mr-1"></i> REVISI
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr id="details-{{ Str::slug($no_sj) }}" class="collapse bg-light">
                            <td colspan="6" class="p-3">
                                <div class="card border-0 shadow-sm overflow-hidden">
                                    <table class="table table-sm table-bordered mb-0" style="font-size: 12px;">
                                        <thead class="bg-gray-200 text-uppercase small font-weight-bold text-center">
                                            <tr>
                                                <th>Part Number</th>
                                                <th>Qty Terkirim</th>
                                                <th>Waktu Input (Jam)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $item)
                                            <tr>
                                                <td class="pl-4 font-weight-bold text-primary">{{ $item->part_no }}</td>
                                                <td class="text-center font-weight-bold">{{ number_format($item->qty_delivery) }} PCS</td>
                                                
                                                {{-- FIX JAM: Menampilkan jam asli dari database --}}
                                                <td class="text-center text-muted">
                                                    <i class="far fa-clock mr-1"></i> 
                                                    {{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }} WIB
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>

                        {{-- ✨ MODAL REVISI SURAT JALAN --}}
                        <div class="modal fade" id="modalRevisi-{{ Str::slug($no_sj) }}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content border-0 shadow-lg rounded-lg">
                                    <div class="modal-header bg-danger text-white border-0 py-3">
                                        <h6 class="modal-title font-weight-bold text-uppercase">
                                            <i class="fas fa-tools mr-2"></i> REVISI QTY PENGIRIMAN
                                            {{-- ✨ TAMBAHAN INFO REVISI KE-BERAPA DI JUDUL MODAL --}}
                                            @if($items->first()->revision_count > 0)
                                                <span class="badge badge-light text-danger ml-2" style="font-size: 11px;">Rev ke-{{ $items->first()->revision_count + 1 }}</span>
                                            @endif
                                        </h6>
                                    </div>
                                    <form action="{{ route('delivery.update') }}" method="POST">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="no_sj" value="{{ urlencode($no_sj) }}">
                                        
                                        <div class="modal-body p-4 bg-light">
                                            <div class="alert alert-warning border-0 shadow-sm small font-weight-bold mb-4">
                                                <i class="fas fa-info-circle mr-1"></i> 
                                                Sistem akan memotong/mengembalikan stok FG dan me-refresh sisa PO secara otomatis.
                                            </div>

                                            <h6 class="font-weight-bold text-dark mb-3">{{ $no_sj }}</h6>
                                            
                                            @foreach($items as $item)
                                            <div class="form-group mb-3 bg-white p-3 rounded shadow-sm border">
                                                <label class="font-weight-bold text-primary mb-1">{{ $item->part_no }}</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-light font-weight-bold">QTY</span>
                                                    </div>
                                                    {{-- Array input name menggunakan id delivery untuk patokan spesifik --}}
                                                    <input type="number" name="items[{{ $item->id }}]" class="form-control font-weight-bold text-center" value="{{ $item->qty_delivery }}" min="0" required>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-light">PCS</span>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        <div class="modal-footer border-0 p-3 bg-white">
                                            <button type="button" class="btn btn-light border font-weight-bold" data-dismiss="modal">BATAL</button>
                                            <button type="submit" class="btn btn-danger font-weight-bold shadow-sm">
                                                <i class="fas fa-save mr-1"></i> SIMPAN REVISI
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted small">
                                <i class="fas fa-search fa-3x mb-3 text-gray-200"></i><br>
                                Data tidak ditemukan untuk filter ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    <div class="card shadow mb-4 py-5 border-left-success text-center">
        <div class="card-body py-5 text-gray-400">
            <i class="fas fa-filter fa-4x mb-4 opacity-25"></i>
            <h4 class="font-weight-bold">Dashboard History Kosong</h4>
            <p class="small">Silakan gunakan filter di atas untuk melihat riwayat Surat Jalan per Customer atau Tanggal.</p>
        </div>
    </div>
@endif

@endsection