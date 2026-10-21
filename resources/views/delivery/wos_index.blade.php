@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --ios-bg: #f4f6fa;
        --ios-border: #e2e8f0;
        --ios-blue: #007aff;
        --ios-green: #34c759;
        --ios-dark: #0f172a;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--ios-bg); color: #1e293b; }
    
    .table-container { background: #ffffff; margin: 20px; border-radius: 20px; border: 1px solid var(--ios-border); overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.03); }
    .table-industrial { width: 100%; border-collapse: collapse; }
    .table-industrial thead th { background: #f8fafc; color: #64748b; padding: 14px 15px; font-size: 10px; text-transform: uppercase; font-weight: 800; border-bottom: 1px solid var(--ios-border); }
    .table-industrial tbody td { padding: 14px 15px; border-bottom: 1px solid #f1f5f9; font-size: 12px; vertical-align: middle; }
    
    .input-tactical { background: #f8fafc; border: 1.5px solid var(--ios-border); border-radius: 10px; padding: 10px 14px; font-weight: 700; font-size: 13px; width: 100%; transition: 0.2s; }
    .input-tactical:focus { background: #ffffff; border-color: var(--ios-blue); outline: none; }
    .num-font { font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: 800; }
</style>

<div class="container-fluid p-0 animate__animated animate__fadeIn">
    
    <div class="bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h4 class="font-weight-bold m-0" style="color: var(--ios-dark);">📦 WOS DELIVERY LOGISTICS</h4>
            <small class="text-muted font-weight-bold">Integrasi FG Stock Real-Time</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('delivery.wos.index') }}" method="GET" class="m-0 mr-3">
                <input type="date" name="date" value="{{ $date }}" class="form-control font-weight-bold" style="border-radius: 10px;" onchange="this.form.submit()">
            </form>
            <button class="btn btn-primary font-weight-bold rounded-pill px-4" data-toggle="modal" data-target="#modalAddPlan">
                + BUAT JADWAL KIRIM
            </button>
        </div>
    </div>

    {{-- ALERT NOTIFIKASI --}}
    <div class="px-4 mt-3">
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" style="border-radius: 10px;">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
    </div>

    <div class="table-container">
        <table class="table-industrial">
            <thead>
                <tr>
                    <th rowspan="2" class="text-center border-right">No</th>
                    <th rowspan="2">Part Name / Number</th>
                    <th rowspan="2" class="text-center">Customer</th>
                    <th colspan="2" class="text-center border-left border-right bg-light">Time Schedule</th>
                    <th colspan="3" class="text-center border-right">Delivery Quantity</th>
                    <th rowspan="2" class="text-center border-right bg-light">Stock Ready<br>(FG)</th>
                    <th colspan="2" class="text-center border-right bg-light">Actual Delivery</th>
                    <th rowspan="2" class="text-center">Judge</th>
                    <th rowspan="2">Keterangan</th>
                    <th rowspan="2" class="text-center">Aksi</th>
                </tr>
                <tr>
                    <th class="text-center border-left">Pulling</th>
                    <th class="text-center border-right">Delv</th>
                    <th class="text-right">Blnc</th>
                    <th class="text-right">Plan</th>
                    <th class="text-right border-right text-primary">Total</th>
                    <th class="text-right border-left">Shift 1</th>
                    <th class="text-right border-right">Shift 2</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveries as $idx =>$d)
                <tr>
                    <td class="text-center text-muted font-weight-bold border-right">{{ $idx + 1 }}</td>
                    <td>
                        <div class="font-weight-bold text-dark">{{ $d->part_name ?? 'UNKNOWN PART' }}</div>
                        <small class="text-primary font-weight-bold">{{ $d->part_no }}</small>
                    </td>
                    <td class="text-center font-weight-bold">{{ $d->customer_code ?? $d->part_cust }}</td>
                    
                    <td class="text-center border-left num-font">{{ $d->pulling_time ?: '-' }}</td>
                    <td class="text-center border-right num-font">{{ $d->delv_time ?: '-' }}</td>
                    
                    <td class="text-right num-font text-danger">{{ $d->blnc_delv > 0 ? number_format($d->blnc_delv) : '-' }}</td>
                    <td class="text-right num-font text-dark">{{ $d->plan_delv > 0 ? number_format($d->plan_delv) : '-' }}</td>
                    <td class="text-right num-font text-primary border-right" style="font-size: 14px;">{{ number_format($d->total_delv) }}</td>
                    
                    <td class="text-center border-right">
                        @if($d->stock_ready >=$d->total_delv)
                            <span class="badge badge-success px-2 py-1 num-font">{{ number_format($d->stock_ready) }}</span>
                        @else
                            <span class="badge badge-danger px-2 py-1 num-font animate__animated animate__flash">{{ number_format($d->stock_ready) }}</span>
                        @endif
                    </td>
                    
                    <td class="text-right num-font">{{ $d->act_delv_s1 > 0 ? number_format($d->act_delv_s1) : '-' }}</td>
                    <td class="text-right border-right num-font">{{ $d->act_delv_s2 > 0 ? number_format($d->act_delv_s2) : '-' }}</td>
                    
                    <td class="text-center font-weight-bold">
                        @if($d->judge == 'OK') <span class="text-success"><i class="fas fa-check-circle"></i> OK</span>
                        @elseif($d->judge == 'SHORTAGE') <span class="text-danger"><i class="fas fa-times-circle"></i> SHRT</span>
                        @else - @endif
                    </td>
                    <td><small class="font-weight-bold text-muted">{{ $d->keterangan ?? '-' }}</small></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-success rounded-pill font-weight-bold px-3" data-toggle="modal" data-target="#modalAct{{ $d->id }}">ISI AKTUAL</button>
                    </td>
                </tr>
                
                {{-- MODAL INPUT ACTUAL --}}
                <div class="modal fade" id="modalAct{{ $d->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="border-radius:20px;">
                            <div class="modal-header bg-light border-0">
                                <h6 class="modal-title font-weight-bold">INPUT ACTUAL PENGIRIMAN</h6>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <form action="{{ route('delivery.wos.updateActual', $d->id) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="modal-body p-4">
                                    <div class="alert alert-info text-center" style="border-radius:12px;">
                                        Target Pengiriman: <b style="font-size:16px;">{{ number_format($d->total_delv) }}</b> Pcs
                                    </div>
                                    <div class="row">
                                        <div class="col-6 form-group">
                                            <label class="small font-weight-bold text-muted">SHIFT 1 (Act)</label>
                                            <input type="number" name="act_delv_s1" class="input-tactical num-font" value="{{ $d->act_delv_s1 }}" min="0">
                                        </div>
                                        <div class="col-6 form-group">
                                            <label class="small font-weight-bold text-muted">SHIFT 2 (Act)</label>
                                            <input type="number" name="act_delv_s2" class="input-tactical num-font" value="{{ $d->act_delv_s2 }}" min="0">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="small font-weight-bold text-muted">Keterangan / Remarks</label>
                                        <textarea name="keterangan" class="input-tactical" rows="2">{{ $d->keterangan }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="submit" class="btn btn-primary btn-block rounded-pill font-weight-bold">SIMPAN & POTONG FG</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <tr><td colspan="14" class="text-center py-5 text-muted font-weight-bold">Belum ada WOS Delivery untuk tanggal ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL TAMBAH JADWAL --}}
<div class="modal fade" id="modalAddPlan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:24px;">
            <div class="modal-header bg-primary text-white border-0">
                <h6 class="modal-title font-weight-bold">TAMBAH JADWAL DELIVERY</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('delivery.wos.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <input type="hidden" name="plan_date" value="{{ $date }}">
                    
                    <div class="form-group">
                        <label class="small font-weight-bold">PART NUMBER</label>
                        <select name="part_no" id="partSelect" class="input-tactical" required>
                            <option value="" disabled selected>-- PILIH BARANG FG --</option>
                            @foreach($parts as$p)
                                <option value="{{ $p->part_no }}" data-cust="{{ $p->customer_code }}">{{ $p->part_no }} - {{$p->part_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <input type="hidden" name="customer_code" id="custCode">

                    {{-- BOX NOTIFIKASI STOCK FG --}}
                    <div id="fgStockMsg" class="w-100 text-center mb-3"></div>

                    <div class="row">
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold">PULLING TIME</label>
                            <input type="time" name="pulling_time" class="input-tactical">
                        </div>
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold">DELIVERY TIME</label>
                            <input type="time" name="delv_time" class="input-tactical">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold text-danger">BALANCE DELV (Hutang Kirim)</label>
                            <input type="number" name="blnc_delv" id="blncInput" class="input-tactical num-font calc-fg" placeholder="0" min="0">
                        </div>
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold text-success">PLAN DELV (Rencana Kirim)</label>
                            <input type="number" name="plan_delv" id="planInput" class="input-tactical num-font calc-fg" placeholder="0" min="0" required>
                        </div>
                    </div>
                    
                    <div class="p-3 bg-white border text-center" style="border-radius: 12px;">
                        <span class="small font-weight-bold text-muted">TOTAL DELV (Pcs)</span>
                        <h3 class="m-0 font-weight-bold text-primary num-font" id="totalDelvDisplay">0</h3>
                    </div>

                </div>
                <div class="modal-footer border-0">
                    <button type="submit" id="btnSubmitDelv" class="btn btn-primary btn-block rounded-pill font-weight-bold py-2" disabled>SIMPAN JADWAL</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let currentFgStock = 0;

    function calcTotal() {
        let blnc = parseInt($('#blncInput').val()) || 0;
        let plan = parseInt($('#planInput').val()) || 0;
        let total = blnc + plan;
        $('#totalDelvDisplay').text(total.toLocaleString());
        
        let msgBox = $('#fgStockMsg');
        let btn = $('#btnSubmitDelv');

        if(total === 0) {
            msgBox.html('');
            btn.prop('disabled', true);
            return;
        }

        if(total > currentFgStock) {
            msgBox.html(`<div class="alert alert-danger p-2 m-0 shadow-sm border-0" style="border-radius:10px;"><b>❌ STOK FG TIDAK CUKUP!</b> Butuh: <b>${total}</b> Pcs | Sisa Gudang: <b>${currentFgStock}</b> Pcs</div>`);
            btn.prop('disabled', true);
        } else {
            msgBox.html(`<div class="alert alert-success p-2 m-0 shadow-sm border-0" style="border-radius:10px;"><b>✅ STOK FG AMAN!</b> Butuh: <b>${total}</b> Pcs | Sisa Gudang: <b>${currentFgStock}</b> Pcs</div>`);
            btn.prop('disabled', false);
        }
    }

    $('#partSelect').change(function() {
        let partNo = $(this).val();
        let cust = $(this).find(':selected').data('cust');$('#custCode').val(cust);

        $('#fgStockMsg').html('<span class="text-warning font-weight-bold"><i class="fas fa-spinner fa-spin"></i> Mengecek stok FG...</span>');
        $('#btnSubmitDelv').prop('disabled', true);

        $.get('/delivery/wos/check-stock/' + encodeURIComponent(partNo), function(res) {
            if(res.status === 'success') {
                currentFgStock = res.stock;
                calcTotal();
            }
        });
    });

    $('.calc-fg').on('input', function() {
        calcTotal();
    });
});
</script>
@endsection