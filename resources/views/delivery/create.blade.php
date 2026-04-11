@extends('layout.admin')

@section('content')
<style>
    /* STYLE EXCEL TEBAL & KAKU */
    .table-excel { border: 2px solid #4e73df !important; font-size: 12px; color: #000; width: 100%; border-collapse: collapse; }
    .table-excel thead th { background-color: #4e73df !important; color: #fff !important; text-align: center; padding: 10px; border: 1px solid #fff !important; text-transform: uppercase; font-weight: 900; }
    .table-excel td { border: 1px solid #d1d3e2 !important; padding: 8px; vertical-align: middle; font-weight: bold; }
    
    .col-part { color: #4e73df; }
    .col-sisa { color: #e74a3b; }
    .col-stok { background-color: #fff3cd; } /* Warna kuning untuk area pantauan stok */
</style>

<div class="container-fluid text-dark">
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-header bg-primary py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-white text-uppercase">
                <i class="fas fa-truck mr-2"></i> PENERBITAN SURAT JALAN: {{ $po->po_number }}
            </h6>
            <a href="{{ route('delivery.index') }}" class="btn btn-sm btn-light font-weight-bold shadow-sm text-uppercase">KEMBALI</a>
        </div>
        <div class="card-body">
            {{-- NOTIFIKASI ERROR STOK KURANG --}}
            @if(session('error'))
                <div class="alert alert-danger font-weight-bold shadow-sm border-left-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('delivery.store') }}" method="POST">
                @csrf
                <input type="hidden" name="po_id" value="{{ $po->id }}">
                <input type="hidden" name="po_header_number" value="{{ $po->po_number }}">

                <div class="row mb-4">
                    <div class="col-md-6 form-group">
                        <label class="small font-weight-bold text-primary text-uppercase">Nomor Surat Jalan (SJ)</label>
                        <input type="text" name="no_sj" class="form-control font-weight-bold border-primary shadow-sm" 
                               placeholder="SJ-{{ date('Ymd') }}-..." required autofocus>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table-excel text-center mb-0">
                        <thead>
                            <tr>
                                <th>Part Number & Riwayat</th>
                                <th>Qty PO</th>
                                <th class="text-success">Terkirim</th>
                                <th class="text-danger">Outstanding PO</th> {{-- --}}
                                <th class="bg-warning text-dark">Stok FG (Rak)</th> {{-- POLISI STOK --}}
                                <th width="200">Qty Kirim Sekarang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                            @php
                                // AMBIL STOK FISIK DARI GUDANG FINISH GOOD
                                $stok_fg = DB::table('finished_goods')->where('part_no', $item->part_no)->value('actual_stock') ?? 0;
                                // Logika Max Kirim: Gak boleh lebih dari Sisa PO atau Stok FG yang ada
                                $max_boleh_kirim = min($item->sisa_pesanan, $stok_fg); 
                            @endphp
                            <tr>
                                <td class="text-left pl-3">
                                    <span class="col-part text-uppercase">{{ $item->part_no }}</span>
                                    {{-- BREAKDOWN TAHAP PENGIRIMAN SEBELUMNYA --}}
                                    <div class="mt-1 small text-muted font-weight-normal" style="font-size: 10px;">
                                        @php
                                            $histories = DB::table('deliveries')->where('po_id', $item->id)->get();
                                        @endphp
                                        @foreach($histories as $index => $history)
                                            <div class="border-top mt-1 pt-1">
                                                Tahap {{ $index + 1 }}: <strong>{{ number_format($history->qty_delivery) }}</strong> ({{ $history->no_sj }})
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>{{ number_format($item->quantity) }}</td>
                                <td class="text-success">{{ number_format($item->total_sent) }}</td>
                                <td class="text-danger">{{ number_format($item->sisa_pesanan) }}</td>
                                
                                {{-- KOLOM PANTAUAN STOK FG --}}
                                <td class="col-stok {{ $stok_fg <= 0 ? 'text-danger' : '' }}">
                                    {{ number_format($stok_fg) }} PCS
                                    @if($stok_fg <= 0) <br><small class="badge badge-danger">STOK KOSONG!</small> @endif
                                </td>

                                <td class="bg-white">
                                    {{-- VALIDASI INPUT: JIKA STOK 0 MAKA READONLY --}}
                                    <input type="number" name="items[{{ $item->part_no }}][qty_kirim]" 
                                           class="form-control form-control-sm text-center font-weight-bold border-primary shadow-sm" 
                                           min="0" max="{{ $max_boleh_kirim }}" value="0"
                                           {{ $stok_fg <= 0 ? 'readonly' : '' }}
                                           oninput="if(this.value > {{ $max_boleh_kirim }}) this.value = {{ $max_boleh_kirim }};">
                                    <small class="text-muted d-block mt-1 italic">Maks: {{ number_format($max_boleh_kirim) }} PCS</small>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="py-5 text-muted font-italic">Semua item dalam PO ini sudah lunas atau data tidak ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary px-5 font-weight-bold shadow-sm" 
                            {{ $items->isEmpty() ? 'disabled' : '' }} style="border-radius: 50px;">
                        <i class="fas fa-print mr-2"></i> TERBITKAN SURAT JALAN {{-- ICON PRINTER --}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection