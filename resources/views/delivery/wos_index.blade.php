@extends('layout.admin')

@section('content')
<style>
    /* UI KUSTOM UNTUK WOS DELIVERY */
    .wos-card { border-radius: 12px; border: none; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05); overflow: hidden; }
    .wos-header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; padding: 1.5rem; }
    
    .table-wos th { background-color: #f8fafc; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #cbd5e1 !important; vertical-align: middle; }
    .table-wos td { vertical-align: middle; color: #334155; font-weight: 600; font-size: 0.85rem; border-bottom: 1px solid #e2e8f0; padding: 1rem 0.75rem; }
    
    .btn-wos { background: #3b82f6; border: none; border-radius: 8px; font-weight: 600; padding: 0.6rem 1.25rem; color: white; transition: all 0.2s ease; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2); }
    .btn-wos:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(59, 130, 246, 0.3); }
    
    .btn-print { background: #475569; border: none; border-radius: 8px; font-weight: 600; padding: 0.6rem 1.25rem; color: white; transition: all 0.2s ease; box-shadow: 0 4px 6px rgba(71, 85, 105, 0.2); }
    .btn-print:hover { background: #334155; color: white; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(71, 85, 105, 0.3); text-decoration: none; }
    
    .btn-outline-revisi { border: 1px solid #f59e0b; color: #f59e0b; border-radius: 6px; font-weight: 700; padding: 0.4rem 0.8rem; font-size: 0.75rem; background: transparent; transition: all 0.2s ease; }
    .btn-outline-revisi:hover { background: #f59e0b; color: white; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3); }
    
    .badge-short { background-color: #ef4444; color: white; border-radius: 6px; padding: 0.4rem 0.7rem; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px; }
    .badge-ok { background-color: #10b981; color: white; border-radius: 6px; padding: 0.4rem 0.7rem; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px; }
    .badge-close { background-color: #64748b; color: white; border-radius: 6px; padding: 0.4rem 0.7rem; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px; }
    .badge-cust { background-color: #e2e8f0; color: #475569; border-radius: 6px; padding: 0.3rem 0.6rem; font-size: 0.75rem; font-weight: 700; border: 1px solid #cbd5e1; }
    
    .form-control { border-radius: 8px; border: 1px solid #cbd5e1; padding: 0.6rem 1rem; font-size: 0.9rem; transition: border-color 0.2s; }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .info-panel { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 0 8px 8px 0; }
    
    /* Animasi Baris Tabel saat CLOSE */
    .row-closed { background-color: #f8fafc; opacity: 0.65; }
    .row-closed:hover { opacity: 0.9; transition: opacity 0.3s; }
</style>

<div class="container-fluid mb-5">
    <div class="card wos-card">
        <!-- HEADER WOS -->
        <div class="wos-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center">
                <div class="bg-white p-2 rounded shadow-sm mr-3">
                    <i class="fas fa-boxes fa-2x" style="color: #3b82f6;"></i>
                </div>
                <div>
                    <h4 class="m-0 font-weight-bold" style="letter-spacing: 1px;">WOS DELIVERY LOGISTICS</h4>
                    <p class="m-0" style="font-size: 0.85rem; color: #94a3b8;">Manajemen Jadwal Pengiriman & Aktualisasi Bebas Hambatan</p>
                </div>
            </div>
            <div class="d-flex align-items-center mt-3 mt-md-0">
                <form action="{{ url('/wos-delivery') }}" method="GET" class="d-flex mr-3 shadow-sm rounded">
                    <input type="date" name="date" class="form-control font-weight-bold text-primary" value="{{ $date }}" onchange="this.form.submit()" style="border: none; background: #f8fafc; cursor: pointer;">
                </form>
                
                <!-- TOMBOL PRINT BARU -->
                <a href="{{ route('delivery.wos.print', ['date' => $date]) }}" target="_blank" class="btn btn-print mr-2">
                    <i class="fas fa-print mr-1"></i> CETAK WOS
                </a>
                
                <button type="button" class="btn-wos" data-toggle="modal" data-target="#modalTambahJadwal">
                    <i class="fas fa-plus-circle mr-1"></i> BUAT JADWAL
                </button>
            </div>
        </div>

        <!-- TABEL WOS -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-wos table-hover mb-0 text-center">
                    <thead>
                        <tr>
                            <th rowspan="2">NO</th>
                            <th rowspan="2" class="text-left">PART NAME / NUMBER</th>
                            <th rowspan="2">CUST</th>
                            <th colspan="2">TIME SCHEDULE</th>
                            <th colspan="3" style="background: #f8fafc;">DELIVERY QUANTITY</th>
                            <th rowspan="2" style="background: #eff6ff; color:#1e40af;">STOCK READY<br><span style="font-size: 0.65rem; font-weight: 500;">(FG RAK)</span></th>
                            <th colspan="2">ACTUAL DELIVERY</th>
                            <th rowspan="2">JUDGE</th>
                            <th rowspan="2">KETERANGAN</th>
                            <th rowspan="2"><i class="fas fa-cog"></i></th>
                        </tr>
                        <tr>
                            <th>PULLING</th>
                            <th>DELIVERY</th>
                            <th style="background: #f8fafc;">OUTSTANDING</th>
                            <th style="background: #f8fafc;">PLAN</th>
                            <th style="background: #f8fafc;">TOTAL</th>
                            <th>SHIFT 1</th>
                            <th>SHIFT 2</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $index =>$d)
                            <tr class="{{ $d->judge === 'CLOSE' ? 'row-closed' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="text-left">
                                    <div class="font-weight-bold text-dark" style="font-size: 0.9rem;">{{ $d->part_name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $d->part_no }}</div>
                                </td>
                                <td><span class="badge-cust">{{ $d->part_cust }}</span></td>
                                <td class="font-weight-bold">{{ $d->pulling_time ? date('H:i', strtotime($d->pulling_time)) : '-' }}</td>
                                <td class="font-weight-bold text-primary">{{ $d->delv_time ? date('H:i', strtotime($d->delv_time)) : '-' }}</td>
                                
                                <td>{{ $d->judge === 'CLOSE' ? '-' : number_format($d->blnc_delv) }}</td>
                                <td>{{ $d->judge === 'CLOSE' ? '-' : number_format($d->plan_delv) }}</td>
                                <td class="font-weight-bold text-dark" style="font-size: 1rem;">{{ $d->total_delv_display }}</td>
                                
                                <td style="background: #eff6ff;" class="font-weight-bold {{ $d->stock_ready < $d->total_delv &&$d->judge !== 'CLOSE' ? 'text-danger' : 'text-primary' }}">
                                    {{ number_format($d->stock_ready) }}
                                </td>
                                
                                <td class="font-weight-bold text-dark" style="font-size: 0.9rem;">{{ number_format($d->act_delv_s1) }}</td>
                                <td class="font-weight-bold text-dark" style="font-size: 0.9rem;">{{ number_format($d->act_delv_s2) }}</td>
                                
                                <td>
                                    @if($d->judge == 'OK') <span class="badge-ok"><i class="fas fa-check"></i> OK</span>
                                    @elseif($d->judge == 'SHORT') <span class="badge-short"><i class="fas fa-exclamation-triangle"></i> SHORT</span>
                                    @elseif($d->judge == 'CLOSE') <span class="badge-close"><i class="fas fa-ban"></i> CLOSE</span>
                                    @else - @endif
                                </td>
                                <td class="small">{{ $d->keterangan ?: '-' }}</td>
                                <td>
                                    <button class="btn-outline-revisi" data-toggle="modal" data-target="#modalUpdate{{ $d->id }}">
                                        <i class="fas fa-tools"></i> REVISI
                                    </button>
                                </td>
                            </tr>

                            <!-- MODAL REVISI ACTUAL & GANTI PART -->
                            <div class="modal fade text-left" id="modalUpdate{{ $d->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content" style="border-radius:16px; border:none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-tools text-warning mr-2"></i> Revisi WOS Logistik</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <form action="{{ route('delivery.wos.updateActual', $d->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="info-panel small mb-4">
                                                    <i class="fas fa-info-circle mr-1"></i> Form ini digunakan untuk koreksi manual atau merubah status menjadi <b>CLOSE</b>. Ubah part / aktual akan otomatis menyesuaikan stok di gudang FG.
                                                </div>
                                                <div class="row">
                                                    <!-- UBAH CUSTOMER & PART -->
                                                    <div class="col-md-12 form-group">
                                                        <label class="font-weight-bold small text-muted">Customer</label>
                                                        <select name="customer_code" class="form-control edit-cust-select" data-target="#editPart{{ $d->id }}">
                                                            @foreach($customers as $cust)
                                                                <option value="{{ $cust }}" {{ $d->part_cust == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12 form-group">
                                                        <label class="font-weight-bold small text-muted">Part Number</label>
                                                        <select name="part_no" id="editPart{{ $d->id }}" class="form-control">
                                                            @foreach($parts as $p)
                                                                @if($p->customer_code ==$d->part_cust)
                                                                    <option value="{{ $p->part_no }}" {{ $d->part_no ==$p->part_no ? 'selected' : '' }}>
                                                                        {{ $p->part_no }} - {{$p->part_name }}
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>

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
                                                        <option value="" {{ $d->keterangan == '' ? 'selected' : '' }}>- Kosong / Normal -</option>
                                                        <option value="CLOSE" {{ strtoupper($d->keterangan) == 'CLOSE' ? 'selected' : '' }}>CLOSE (Tutup Jadwal)</option>
                                                        <option value="Terkirim Sebagian" {{ $d->keterangan == 'Terkirim Sebagian' ? 'selected' : '' }}>Terkirim Sebagian</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 pt-0">
                                                <button type="submit" class="btn btn-warning w-100 font-weight-bold text-dark" style="border-radius: 8px; padding: 0.8rem; font-size: 1rem;">
                                                    <i class="fas fa-save mr-1"></i> SIMPAN REVISI
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="14" class="py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3" style="color: #cbd5e1;"></i><br>
                                    <h6 class="font-weight-bold">Belum ada Jadwal Pengiriman.</h6>
                                    <p class="small">Klik 'Buat Jadwal Kirim' untuk mulai merencanakan pengiriman hari ini.</p>
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
<div class="modal fade text-left" id="modalTambahJadwal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-calendar-plus mr-2 text-primary"></i> Form Jadwal Delivery</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('delivery.wos.store') }}" method="POST">
                @csrf
                <div class="modal-body pt-3">
                    <div class="row">
                        <!-- TANGGAL & CUSTOMER DROPDOWN -->
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted">Tanggal Rencana (Plan Date)</label>
                            <input type="date" name="plan_date" class="form-control" value="{{ $date }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted">Pilih Customer</label>
                            <select id="filterCustomer" name="customer_code" class="form-control" required>
                                <option value="" disabled selected>-- Pilih Customer --</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust }}">{{ $cust }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- PART SELECTION (FILTERED) -->
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold small text-muted">Pilih Part Number</label>
                            <select name="part_no" id="partSelect" class="form-control" disabled required style="border: 2px solid #e2e8f0;">
                                <option value="" disabled selected>-- Pilih Customer Terlebih Dahulu --</option>
                                <!-- Data part akan di-clone ke sini oleh JavaScript -->
                            </select>
                        </div>
                        
                        <!-- SIMPAN SEMUA OPTION PART SEBAGAI DATA TERSEMBUNYI -->
                        <div id="hiddenPartOptions" style="display: none;">
                            @foreach($parts as $p)
                                <option value="{{ $p->part_no }}" class="part-option" data-cust="{{ $p->customer_code }}">
                                    {{ $p->part_no }} - {{$p->part_name }}
                                </option>
                            @endforeach
                        </div>
                        
                        <!-- INFO STOK PINTAR -->
                        <div class="col-md-12 mb-3">
                            <div id="fgStockMsg" class="info-panel small text-dark">
                                <i class="fas fa-info-circle text-primary mr-1"></i> Pilih part untuk melihat info stok Finish Goods (FG) saat ini.
                            </div>
                        </div>

                        <!-- WAKTU -->
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted"><i class="far fa-clock text-secondary mr-1"></i> Pulling Time</label>
                            <input type="time" name="pulling_time" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-muted"><i class="fas fa-truck text-secondary mr-1"></i> Delivery Time</label>
                            <input type="time" name="delv_time" class="form-control">
                        </div>

                        <!-- QTY (BEBAS KETIK) -->
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-danger">Balance Delv (Hutang Pengiriman)</label>
                            <input type="number" name="blnc_delv" class="form-control" placeholder="0" value="0">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold small text-success">Plan Delv (Rencana Pengiriman)</label>
                            <input type="number" name="plan_delv" class="form-control" placeholder="0" value="0">
                        </div>

                        <!-- STATUS AWAL JADWAL -->
                        <div class="col-md-12 form-group">
                            <label class="font-weight-bold small text-muted">Status / Keterangan (Opsional)</label>
                            <select name="keterangan" class="form-control">
                                <option value="">- Kosong / Normal -</option>
                                <option value="CLOSE">CLOSE (Jadwal Langsung Ditutup)</option>
                                <option value="Terkirim Sebagian">Terkirim Sebagian</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="submit" class="btn btn-primary w-100 font-weight-bold text-white shadow-sm" style="border-radius: 8px; padding: 0.8rem; font-size: 1rem;">
                        <i class="fas fa-paper-plane mr-1"></i> SIMPAN JADWAL
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- LOAD JQUERY -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    
    // --- 1. LOGIKA DROPDOWN BERTINGKAT (MODAL TAMBAH JADWAL) ---
    // Clone semua opsi part dari div tersembunyi ke dalam memori
    let allParts = $('#hiddenPartOptions option.part-option').clone();

    $('#filterCustomer').change(function() {
        let selectedCust = $(this).val();
        let partDropdown = $('#partSelect');
        
        // Aktifkan dropdown part & kosongkan isinya
        partDropdown.prop('disabled', false).css('border-color', '#3b82f6');
        partDropdown.empty().append('<option value="" disabled selected>-- Pilih Part Number --</option>');
        
        // Panggil kembali part yang cocok dengan customer
        allParts.filter(`[data-cust="${selectedCust}"]`).appendTo(partDropdown);
        
        // Reset info stok
        $('#fgStockMsg').html('<i class="fas fa-info-circle text-primary mr-1"></i> Pilih part untuk melihat info stok FG saat ini.');
    });

    // --- 2. AJAX CEK STOK (MODAL TAMBAH JADWAL) ---
    $('#partSelect').change(function() {
        let partNo = $(this).val();$('#fgStockMsg').html('<span class="font-weight-bold text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Sedang memindai rak gudang...</span>');

        $.get('/wos-delivery/check-stock/' + encodeURIComponent(partNo), function(res) {
            if(res.status === 'success') {
                let stock = parseInt(res.stock) || 0;
                let textColor = stock > 0 ? 'text-success' : 'text-danger';
                $('#fgStockMsg').html(`<i class="fas fa-box text-primary mr-2"></i> Stok Finish Goods saat ini: <b class="${textColor}" style="font-size:1.1rem;">${stock.toLocaleString('id-ID')} Pcs</b>.`);
            }
        }).fail(function() {
            $('#fgStockMsg').html('<i class="fas fa-exclamation-triangle text-warning mr-1"></i> Gagal mengecek stok gudang.');
        });
    });

    // --- 3. BERSIHKAN NAMA PART SAAT SUBMIT FORM TAMBAH JADWAL ---
    $('#modalTambahJadwal form').submit(function() {
        let partVal = $('#partSelect').val();
        if(partVal) {
            $('#partSelect').val(partVal.split(' - ')[0].trim());
        }
    });

    // --- 4. LOGIKA DROPDOWN BERTINGKAT (MODAL REVISI) ---
    $('.edit-cust-select').change(function() {
        let selectedCust = $(this).val();
        let targetPartSelect = $($(this).data('target'));
        
        targetPartSelect.empty();
        allParts.filter(`[data-cust="${selectedCust}"]`).appendTo(targetPartSelect);
    });
    
    // --- 5. BERSIHKAN NAMA PART SAAT SUBMIT FORM REVISI ---
    $('[id^="modalUpdate"] form').submit(function() {
        let selectElement = $(this).find('select[name="part_no"]');
        let partVal = selectElement.val();
        if(partVal) {
            selectElement.val(partVal.split(' - ')[0].trim());
        }
    });
});
</script>
@endsection