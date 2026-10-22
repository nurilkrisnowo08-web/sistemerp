@extends('layout.admin')

@section('content')
<style>
    .wos-card { border-radius: 12px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.03); overflow: hidden; }
    .wos-header { background: #1e293b; color: white; padding: 1.25rem 1.5rem; }
    .table-wos th { background-color: #f8fafc; color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0 !important; vertical-align: middle; }
    .table-wos td { vertical-align: middle; color: #334155; font-weight: 600; font-size: 0.8rem; border-bottom: 1px solid #f1f5f9; padding: 0.85rem 0.75rem; }
    .btn-wos { background: #3b82f6; border: none; border-radius: 6px; font-weight: 600; padding: 0.5rem 1rem; color: white; transition: all 0.2s; }
    .btn-wos:hover { background: #2563eb; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
    .btn-outline-aktual { border: 1px solid #3b82f6; color: #3b82f6; border-radius: 6px; font-weight: 700; padding: 0.35rem 0.75rem; font-size: 0.75rem; background: transparent; transition: all 0.2s; }
    .btn-outline-aktual:hover { background: #3b82f6; color: white; }
    .badge-short { background-color: #ef4444; color: white; border-radius: 4px; padding: 0.35rem 0.6rem; font-size: 0.65rem; }
    .badge-ok { background-color: #10b981; color: white; border-radius: 4px; padding: 0.35rem 0.6rem; font-size: 0.65rem; }
    .badge-close { background-color: #64748b; color: white; border-radius: 4px; padding: 0.35rem 0.6rem; font-size: 0.65rem; }
    .badge-cust { background-color: #94a3b8; color: white; border-radius: 4px; padding: 0.25rem 0.5rem; font-size: 0.7rem; }
    .form-control { border-radius: 6px; }
</style>

<div class="container-fluid mb-5">
    <div class="card wos-card">
        <!-- HEADER -->
        <div class="wos-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center">
                <i class="fas fa-boxes fa-2x mr-3" style="color: #60a5fa;"></i>
                <div>
                    <h5 class="m-0 font-weight-bold" style="letter-spacing: 1px;">WOS DELIVERY LOGISTICS</h5>
                    <p class="m-0" style="font-size: 0.75rem; color: #cbd5e1;">Manajemen Jadwal Pengiriman & Aktualisasi Bebas Hambatan</p>
                </div>
            </div>
            <div class="d-flex align-items-center mt-3 mt-md-0">
                <form action="{{ url('/wos-delivery') }}" method="GET" class="d-flex mr-3">
                    <input type="date" name="date" class="form-control font-weight-bold text-primary" value="{{ $date }}" onchange="this.form.submit()" style="border: none; padding: 0.4rem 1rem;">
                </form>
                <button type="button" class="btn-wos shadow-sm" data-toggle="modal" data-target="#modalTambahJadwal">
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
                            <th rowspan="2">NO</th>
                            <th rowspan="2" class="text-left">PART NAME / NUMBER</th>
                            <th rowspan="2">CUST</th>
                            <th colspan="2">TIME SCHEDULE</th>
                            <th colspan="3">DELIVERY QUANTITY</th>
                            <th rowspan="2" style="background:#f1f5f9;">STOCK READY<br><span style="font-size: 0.6rem;">(FG)</span></th>
                            <th colspan="2">ACTUAL DELIVERY</th>
                            <th rowspan="2">JUDGE</th>
                            <th rowspan="2">KETERANGAN</th>
                            <th rowspan="2">AKSI</th>
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
                            <tr style="{{ $d->judge === 'CLOSE' ? 'background-color: #f8fafc; opacity: 0.6;' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="text-left">
                                    <div class="font-weight-bold text-dark" style="font-size: 0.85rem;">{{ $d->part_name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $d->part_no }}</div>
                                </td>
                                <td><span class="badge-cust">{{ $d->part_cust }}</span></td>
                                <td class="font-weight-bold text-dark">{{ $d->pulling_time ? date('H:i', strtotime($d->pulling_time)) : '-' }}</td>
                                <td class="font-weight-bold text-primary">{{ $d->delv_time ? date('H:i', strtotime($d->delv_time)) : '-' }}</td>
                                
                                <td>{{ $d->judge === 'CLOSE' ? '-' : number_format($d->blnc_delv) }}</td>
                                <td>{{ $d->judge === 'CLOSE' ? '-' : number_format($d->plan_delv) }}</td>
                                <td class="font-weight-bold text-dark" style="font-size: 0.9rem;">{{ $d->total_delv_display }}</td>
                                
                                <td style="background:#f8fafc;" class="font-weight-bold {{ $d->stock_ready < $d->total_delv &&$d->judge !== 'CLOSE' ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($d->stock_ready) }}
                                </td>
                                
                                <td class="font-weight-bold">{{ number_format($d->act_delv_s1) }}</td>
                                <td class="font-weight-bold">{{ number_format($d->act_delv_s2) }}</td>
                                
                                <td>
                                    @if($d->judge == 'OK') <span class="badge-ok"><i class="fas fa-check"></i> OK</span>
                                    @elseif($d->judge == 'SHORT') <span class="badge-short"><i class="fas fa-exclamation-triangle"></i> SHORT</span>
                                    @elseif($d->judge == 'CLOSE') <span class="badge-close"><i class="fas fa-ban"></i> CLOSE</span>
                                    @else - @endif
                                </td>
                                <td>{{ $d->keterangan ?: '-' }}</td>
                                <td>
                                    <button class="btn-outline-aktual" data-toggle="modal" data-target="#modalUpdate{{ $d->id }}">
                                        <i class="fas fa-edit"></i> AKTUAL
                                    </button>
                                </td>
                            </tr>

                            <!-- MODAL UPDATE ACTUAL -->
                            <div class="modal fade" id="modalUpdate{{ $d->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content" style="border-radius:12px; border:none;">
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h6 class="modal-title font-weight-bold text-dark">Update Aktual: {{ $d->part_no }}</h6>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <form action="{{ route('delivery.wos.updateActual', $d->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
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
                                                    <label class="font-weight-bold small text-muted">Status / Keterangan</label>
                                                    <select name="keterangan" class="form-control">
                                                        <option value="" {{ $d->keterangan == '' ? 'selected' : '' }}>- Kosong -</option>
                                                        <option value="CLOSE" {{ strtoupper($d->keterangan) == 'CLOSE' ? 'selected' : '' }}>CLOSE (Tutup Jadwal)</option>
                                                        <option value="Terkirim Sebagian" {{ $d->keterangan == 'Terkirim Sebagian' ? 'selected' : '' }}>Terkirim Sebagian</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 pt-0">
                                                <button type="submit" class="btn-wos w-100">SIMPAN AKTUAL</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="14" class="py-5 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-3 opacity-50"></i><br>
                                    Belum ada Jadwal Pengiriman.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH JADWAL BERTINGKAT -->
<div class="modal fade" id="modalTambahJadwal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px; border:none;">
            <div class="modal-header border-bottom-0 pb-0">
                <h6 class="modal-title font-weight-bold text-dark"><i class="fas fa-calendar-plus mr-2 text-primary"></i> Buat Jadwal Delivery</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('delivery.wos.store') }}" method="POST">
                @csrf
                <div class="modal-body pt-3">
                    <div class="row">
                        <!-- TANGGAL & CUSTOMER DROPDOWN -->
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted">Tanggal Rencana</label>
                            <input type="date" name="plan_date" class="form-control" value="{{ $date }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted">Pilih Customer</label>
                            <select id="filterCustomer" name="customer_code" class="form-control" required>
                                <option value="" disabled selected>-- Pilih Customer --</option>
                                @foreach($customers as$cust)
                                    <option value="{{ $cust }}">{{ $cust }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- PART SELECTION (FILTERED) -->
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold small text-muted">Pilih Part Number</label>
                            <select name="part_no" id="partSelect" class="form-control" disabled required>
                                <option value="" disabled selected>-- Pilih Customer Terlebih Dahulu --</option>
                                @foreach($parts as$p)
                                    <option value="{{ $p->part_no }}" class="part-option" data-cust="{{ $p->customer_code }}">
                                        {{ $p->part_no }} - {{$p->part_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- INFO STOK -->
                        <div class="col-md-12 mb-3">
                            <div id="fgStockMsg" class="small text-muted p-2" style="background: #f8fafc; border-radius: 6px; border-left: 3px solid #3b82f6;">
                                <i class="fas fa-info-circle text-primary"></i> Pilih part untuk melihat info stok FG saat ini.
                            </div>
                        </div>

                        <!-- WAKTU -->
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted">Pulling Time</label>
                            <input type="time" name="pulling_time" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted">Delivery Time</label>
                            <input type="time" name="delv_time" class="form-control">
                        </div>

                        <!-- QTY -->
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted text-danger">Balance Delv (Hutang)</label>
                            <input type="number" name="blnc_delv" id="blncInput" class="form-control calc-fg" placeholder="0" value="0">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted text-success">Plan Delv</label>
                            <input type="number" name="plan_delv" id="planInput" class="form-control calc-fg" placeholder="0" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="submit" class="btn-wos w-100">SIMPAN JADWAL</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {  // 1. Logika Dropdown Bertingkat (Customer -> Part)$('#filterCustomer').change(function() {
        let selectedCust = $(this).val();
        let partDropdown = $('#partSelect');
        
        // Buka kunci dropdown part
        partDropdown.prop('disabled', false);
        partDropdown.val('');
        
        // Sembunyikan semua part, lalu tampilkan yang sesuai customer
        partDropdown.find('.part-option').hide();
        partDropdown.find(`.part-option[data-cust="${selectedCust}"]`).show();
        
        partDropdown.find('option:first').text('-- Pilih Part Number --').prop('selected', true);
        $('#fgStockMsg').html('<i class="fas fa-info-circle text-primary"></i> Pilih part untuk melihat info stok FG saat ini.');
    });

    // 2. Cek Stok FG secara Realtime (Hanya info, tidak memblokir simpan)
    $('#partSelect').change(function() {
        let partNo = $(this).val();$('#fgStockMsg').html('<span class="font-weight-bold text-primary"><i class="fas fa-spinner fa-spin"></i> Sedang mengecek gudang...</span>');

        $.get('/wos-delivery/check-stock/' + encodeURIComponent(partNo), function(res) {
            if(res.status === 'success') {
                let stock = parseInt(res.stock) || 0;
                $('#fgStockMsg').html(`<i class="fas fa-box text-primary mr-1"></i> Stok Gudang FG saat ini: <b class="text-dark">${stock.toLocaleString('id-ID')} Pcs</b>.`);
            }
        }).fail(function() {
            $('#fgStockMsg').html('<i class="fas fa-exclamation-triangle text-warning"></i> Gagal mengecek stok.');
        });
    });
});
</script>
@endsection