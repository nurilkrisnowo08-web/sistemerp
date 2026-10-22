@extends('layout.admin')

@section('content')
<style>
    .wos-card { border-radius: 16px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .wos-header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 16px 16px 0 0; color: white; padding: 1.5rem; }
    .table-wos th { background-color: #f8fafc; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0 !important; }
    .table-wos td { vertical-align: middle; color: #1e293b; font-weight: 500; font-size: 0.85rem; border-bottom: 1px solid #f1f5f9; }
    .btn-wos { border-radius: 8px; font-weight: 600; padding: 0.5rem 1.25rem; transition: all 0.2s; }
    .btn-wos:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(29, 78, 216, 0.3); }
    .info-box { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 0 8px 8px 0; }
    .form-control { border-radius: 8px; border: 1px solid #cbd5e1; padding: 0.6rem 1rem; }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
</style>

<div class="container-fluid mb-5">
    <div class="card wos-card">
        <!-- HEADER -->
        <div class="wos-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="m-0 font-weight-bold"><i class="fas fa-boxes text-primary mr-2"></i> WOS DELIVERY LOGISTICS</h4>
                <p class="m-0 mt-1 text-gray-400 small">Manajemen Jadwal Pengiriman & Aktualisasi Bebas Hambatan</p>
            </div>
            <div class="d-flex align-items-center mt-3 mt-md-0">
                <form action="{{ url('/wos-delivery') }}" method="GET" class="d-flex mr-3">
                    <input type="date" name="date" class="form-control mr-2 font-weight-bold text-primary" value="{{ $date }}" onchange="this.form.submit()" style="max-width: 180px;">
                </form>
                <button type="button" class="btn btn-primary btn-wos" data-toggle="modal" data-target="#modalTambahJadwal">
                    <i class="fas fa-plus mr-1"></i> BUAT JADWAL KIRIM
                </button>
            </div>
        </div>

        <!-- TABLE DATA -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-wos table-hover mb-0 text-center">
                    <thead>
                        <tr>
                            <th rowspan="2" class="align-middle">NO</th>
                            <th rowspan="2" class="align-middle text-left">PART NAME / NUMBER</th>
                            <th rowspan="2" class="align-middle">CUST</th>
                            <th colspan="2">TIME SCHEDULE</th>
                            <th colspan="3">DELIVERY QUANTITY</th>
                            <th rowspan="2" class="align-middle" style="background:#f1f5f9;">STOCK READY<br><span class="text-muted">(FG)</span></th>
                            <th colspan="2">ACTUAL DELIVERY</th>
                            <th rowspan="2" class="align-middle">JUDGE</th>
                            <th rowspan="2" class="align-middle">KETERANGAN</th>
                            <th rowspan="2" class="align-middle">AKSI</th>
                        </tr>
                        <tr>
                            <th>PULLING</th>
                            <th>DELV</th>
                            <th>BLNC</th>
                            <th>PLAN</th>
                            <th>TOTAL</th>
                            <th>SHIFT 1</th>
                            <th>SHIFT 2</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $index =>$d)
                            @php 
                                $aktual_total = $d->act_delv_s1 +$d->act_delv_s2; 
                                $isCompleted =$d->status === 'COMPLETED';
                            @endphp
                            <tr style="{{ $isCompleted ? 'background-color: #f8fafc; opacity: 0.8;' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="text-left">
                                    <div class="font-weight-bold text-dark">{{ $d->part_name }}</div>
                                    <div class="small text-muted">{{ $d->part_no }}</div>
                                </td>
                                <td><span class="badge badge-secondary px-2 py-1">{{ $d->part_cust }}</span></td>
                                <td class="font-weight-bold">{{ $d->pulling_time ? date('H:i', strtotime($d->pulling_time)) : '-' }}</td>
                                <td class="font-weight-bold text-primary">{{ $d->delv_time ? date('H:i', strtotime($d->delv_time)) : '-' }}</td>
                                <td>{{ number_format($d->blnc_delv) }}</td>
                                <td>{{ number_format($d->plan_delv) }}</td>
                                <td class="font-weight-bold text-dark" style="font-size:1rem;">{{ number_format($d->total_delv) }}</td>
                                
                                <td style="background:#f8fafc;" class="font-weight-bold {{ $d->stock_ready <$d->total_delv ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($d->stock_ready) }}
                                </td>
                                
                                <td>{{ number_format($d->act_delv_s1) }}</td>
                                <td>{{ number_format($d->act_delv_s2) }}</td>
                                
                                <td>
                                    @if($d->judge == 'OK') <span class="badge badge-success px-2 py-1"><i class="fas fa-check"></i> OK</span>
                                    @elseif($d->judge == 'SHORTAGE') <span class="badge badge-danger px-2 py-1"><i class="fas fa-exclamation-triangle"></i> SHORT</span>
                                    @else - @endif
                                </td>
                                <td>{{ $d->keterangan ?: '-' }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius:6px;" data-toggle="modal" data-target="#modalUpdate{{ $d->id }}">
                                        <i class="fas fa-edit"></i> AKTUAL
                                    </button>
                                </td>
                            </tr>

                            <!-- MODAL UPDATE ACTUAL -->
                            <div class="modal fade" id="modalUpdate{{ $d->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content" style="border-radius:16px; border:none;">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title font-weight-bold text-primary"><i class="fas fa-clipboard-check mr-2"></i> Update Aktual Pengiriman</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <form action="{{ route('delivery.wos.updateActual', $d->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="info-box mb-4">
                                                    <div class="small text-muted">Target Pengiriman (Total Delv)</div>
                                                    <div class="font-weight-bold text-dark" style="font-size:1.2rem;">{{ number_format($d->total_delv) }} Pcs</div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 form-group">
                                                        <label class="font-weight-bold small text-muted">Aktual Shift 1</label>
                                                        <input type="number" name="act_delv_s1" class="form-control" value="{{ $d->act_delv_s1 }}">
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        <label class="font-weight-bold small text-muted">Aktual Shift 2</label>
                                                        <input type="number" name="act_delv_s2" class="form-control" value="{{ $d->act_delv_s2 }}">
                                                    </div>
                                                </div>
                                                <div class="form-group mt-2">
                                                    <label class="font-weight-bold small text-muted">Keterangan (Opsional)</label>
                                                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Tulis alasan jika shortage...">{{ $d->keterangan }}</textarea>
                                                </div>
                                                <div class="alert alert-warning small p-2 m-0 text-center" style="border-radius:8px;">
                                                    <i class="fas fa-info-circle"></i> Stok FG akan dipotong otomatis sesuai jumlah aktual yang disimpan.
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="submit" class="btn btn-primary w-100 btn-wos">SIMPAN AKTUAL</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="14" class="py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                    <h5>Belum ada Jadwal Pengiriman untuk tanggal ini.</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH JADWAL (INPUT BEBAS) -->
<div class="modal fade" id="modalTambahJadwal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold text-primary"><i class="fas fa-calendar-plus mr-2"></i> Buat Jadwal Delivery Baru</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('delivery.wos.store') }}" method="POST">
                @csrf
                <div class="modal-body pt-3">
                    <div class="row">
                        <!-- TANGGAL & CUSTOMER -->
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted">Tanggal Rencana (Plan Date)</label>
                            <input type="date" name="plan_date" class="form-control" value="{{ $date }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted">Customer Code</label>
                            <input type="text" name="customer_code" id="custCode" class="form-control" placeholder="Ketik Customer Code (Misal: SAI)" required>
                        </div>

                        <!-- PART SELECTION -->
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold small text-muted">Pilih / Ketik Part Number</label>
                            <select name="part_no" id="partSelect" class="form-control" required>
                                <option value="" disabled selected>-- Cari Part --</option>
                                @foreach($parts as $p)
                                    <option value="{{ $p->part_no }}" data-cust="{{ $p->customer_code }}">
                                        {{ $p->part_no }} - {{$p->part_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- INFO STOK FG (CUMA TAMPIL, GAK NGEBLOK) -->
                        <div class="col-md-12 mb-3">
                            <div id="fgStockMsg" class="info-box text-dark small">
                                <i class="fas fa-info-circle text-primary"></i> Pilih part untuk melihat ketersediaan barang di Gudang FG.
                            </div>
                        </div>

                        <!-- JADWAL WAKTU -->
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted">Pulling Time</label>
                            <input type="time" name="pulling_time" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted">Delivery Time</label>
                            <input type="time" name="delv_time" class="form-control">
                        </div>

                        <!-- QUANTITY BEBAS KETIK -->
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted text-danger">Balance Delv (Hutang Kirim)</label>
                            <input type="number" name="blnc_delv" id="blncInput" class="form-control calc-fg" placeholder="0" value="0">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted text-success">Plan Delv (Rencana Kirim)</label>
                            <input type="number" name="plan_delv" id="planInput" class="form-control calc-fg" placeholder="0" value="0">
                        </div>
                        
                        <!-- TOTAL DISPLAY -->
                        <div class="col-md-12 mt-2">
                            <div class="text-center p-3" style="background:#f8fafc; border-radius:12px; border:1px dashed #cbd5e1;">
                                <div class="small text-muted font-weight-bold mb-1">TOTAL DELIVERY (PCS)</div>
                                <h2 class="m-0 font-weight-bold text-primary" id="totalDelvDisplay">0</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary w-100 btn-wos" style="font-size:1.1rem; padding: 0.8rem;">SIMPAN JADWAL KIRIM</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    
    // Fungsi Hitung Total (Sekarang tombol simpan selalu aktif berapapun totalnya)
    function calcTotal() {
        let blnc = parseInt($('#blncInput').val()) || 0;
        let plan = parseInt($('#planInput').val()) || 0;
        let total = blnc + plan;
        $('#totalDelvDisplay').text(total.toLocaleString('id-ID'));
    }

    // Eksekusi setiap ngetik angka
    $('.calc-fg').on('input', function() {
        calcTotal();
    });

    // Panggil Stok FG Cuma Buat Nampilin Info Saja
    $('#partSelect').change(function() {
        let rawVal = $(this).val();
        let partNo = rawVal.split(' - ')[0].trim();
        
        let cust = $(this).find(':selected').data('cust');$('#custCode').val(cust);

        $('#fgStockMsg').html('<span class="font-weight-bold text-primary"><i class="fas fa-spinner fa-spin"></i> Sedang mengecek gudang...</span>');

        $.get('/wos-delivery/check-stock/' + encodeURIComponent(partNo), function(res) {
            if(res.status === 'success') {
                let stock = parseInt(res.stock) || 0;
                $('#fgStockMsg').html(`<i class="fas fa-box text-primary mr-1"></i> Informasi Gudang: Saat ini tersedia <b>${stock.toLocaleString('id-ID')} Pcs</b> untuk Part <b>${partNo}</b>.`);
            }
        }).fail(function() {
            $('#fgStockMsg').html('<i class="fas fa-exclamation-triangle text-warning"></i> Gagal mengecek stok, tapi Anda tetap bisa melanjutkan.');
        });
    });
});
</script>
@endsection