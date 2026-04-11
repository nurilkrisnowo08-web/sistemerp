@extends('layout.admin')

@section('content')
<style>
    /* --- UI INDUSTRIAL PRO (WEB VIEW) --- */
    body { background-color: #f8f9fc; color: #333; }
    .table-pro { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
    .table-pro thead th { background: #4e73df; color: #ffffff; font-size: 11px; text-transform: uppercase; padding: 12px; border: none; }
    .table-pro tbody tr { background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: 0.2s; }
    .table-pro tbody tr:hover { transform: scale(1.005); box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
    .table-pro td { padding: 12px; vertical-align: middle; font-size: 13px; border-top: 1px solid #f1f1f1; border-bottom: 1px solid #f1f1f1; }
    .table-pro td:first-child { border-left: 1px solid #f1f1f1; border-radius: 8px 0 0 8px; }
    .table-pro td:last-child { border-right: 1px solid #f1f1f1; border-radius: 0 8px 8px 0; }

    .val-in { color: #1cc88a; background: #f0fff4; padding: 4px 8px; border-radius: 4px; font-weight: 700; } /* */
    .val-out { color: #e74a3b; background: #fff5f5; padding: 4px 8px; border-radius: 4px; font-weight: 700; } /* */
    .val-stock { font-weight: 800; color: #4e73df; background: #f0f7ff; } /* */
    .print-only { display: none; }

    /* --- LOGIKA CETAK A4 LANDSCAPE --- */
    @media print {
        @page { size: A4 landscape; margin: 1cm; }
        .no-print, .sidebar, .navbar, footer, .btn, .modal, .breadcrumb { display: none !important; }
        #content-wrapper { background: white !important; margin: 0 !important; padding: 0 !important; }
        .container-fluid { padding: 0 !important; }
        .print-only { display: block !important; width: 100% !important; }
        .table-pro { border-spacing: 0; width: 100% !important; }
        .table-pro td, .table-pro th { border: 1px solid #000 !important; border-radius: 0 !important; color: #000 !important; font-size: 10px !important; }
        .val-in, .val-out, .val-stock { background: none !important; color: #000 !important; padding: 0 !important; }
    }
</style>

<div class="container-fluid">
    <div class="print-only">
        <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px;">
            <div style="display: flex; align-items: center;">
                <img src="{{ asset('admin/img/Logo-asalta.png') }}" style="width: 50px;" class="mr-3">
                <div>
                    <h6 class="m-0 font-weight-bold">PT. ASALTA MANDIRI AGUNG</h6>
                    <p class="m-0 small">PLANT Karawang | Dept. PPC Logistic & Delivery</p>
                    <h5 class="mt-2 mb-0 font-weight-bold text-uppercase">Daily Report Finish Good</h5>
                </div>
            </div>
            <div class="text-right">
                <p class="m-0 small font-weight-bold">TANGGAL LAPORAN:</p>
                <h5 class="m-0 font-weight-bold text-primary">{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</h5>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h1 class="h4 m-0 font-weight-bold text-gray-800">Daily Stock Movement</h1>
            <p class="mb-0 small text-muted">Total Record: <span class="badge badge-primary">{{ $reports->flatten()->count() }} items</span></p>
        </div>
        <div class="d-flex align-items-center">
            <form action="{{ route('fg-daily.index') }}" method="GET" class="form-inline mr-2">
                <input type="date" name="date" class="form-control form-control-sm border-0 shadow-sm" value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <input type="text" id="liveSearch" class="form-control form-control-sm border-0 shadow-sm mr-2" style="width: 180px;" placeholder="Cari Part...">
            <button class="btn btn-primary btn-sm font-weight-bold shadow-sm px-3 mr-1" data-toggle="modal" data-target="#modalCreate"><i class="fas fa-plus mr-1"></i> TAMBAH DATA</button>
            <button onclick="window.print()" class="btn btn-dark btn-sm shadow-sm px-2"><i class="fas fa-print"></i></button>
        </div>
    </div>

    <div class="report-container">
        @forelse($reports as $customer => $items)
            <div class="mb-1 mt-4 no-print">
                <span class="badge badge-light border text-primary px-3 py-2 text-uppercase font-weight-bold shadow-sm">
                    <i class="fas fa-building mr-1"></i> {{ $customer ?? 'Gudang' }}
                </span>
            </div>
            <div class="print-only font-weight-bold small mb-1">CUSTOMER: {{ $customer ?? 'Gudang' }}</div>
            
            <div class="table-responsive">
                <table class="table-pro text-center">
                    <thead>
                        <tr>
                            <th width="40">No</th>
                            <th width="100">Tanggal</th>
                            <th width="140">Part No</th>
                            <th>Part Name</th>
                            <th width="80">Awal</th>
                            <th width="80">IN</th>
                            <th width="80">OUT</th>
                            <th width="80">Akhir</th>
                            <th>Keterangan</th>
                            <th width="90" class="no-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="report-body">
                        @foreach($items as $index => $r)
                        <tr class="row-data">
                            <td>{{ $index + 1 }}</td>
                            <td class="small font-weight-bold text-primary">{{ \Carbon\Carbon::parse($r->report_date)->format('d/m/Y') }}</td>
                            <td class="font-weight-bold text-dark">{{ $r->part_no }}</td>
                            <td class="text-left small">{{ $r->part_name }}</td>
                            <td>{{ number_format($r->stock_awal) }}</td>
                            <td><span class="val-in">+{{ number_format($r->in) }}</span></td>
                            <td><span class="val-out">-{{ number_format($r->out) }}</span></td>
                            <td class="val-stock">{{ number_format($r->stock_akhir) }}</td>
                            <td class="text-left small text-muted italic">{{ $r->keterangan ?? '-' }}</td>
                            <td class="no-print text-nowrap">
                                <button class="btn btn-link text-warning p-0 btn-edit-trigger" 
                                    data-id="{{ $r->id }}" data-date="{{ $r->report_date }}" 
                                    data-part="{{ $r->part_no }}" data-awal="{{ $r->stock_awal }}" 
                                    data-in="{{ $r->in }}" data-out="{{ $r->out }}" 
                                    data-ket="{{ $r->keterangan }}"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('fg-daily.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger p-0 ml-2"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div class="text-center py-5 text-muted italic">Belum ada aktivitas stok untuk tanggal ini.</div>
        @endforelse

        <div class="print-only mt-5">
            <div class="d-flex justify-content-end">
                <table border="1" style="width: 450px; text-align: center; font-size: 10px; border-collapse: collapse;">
                    <tr><th width="25%">Diketahui</th><th width="25%">Disetujui</th><th width="25%">Dicek</th><th width="25%">Dibuat</th></tr>
                    <tr style="height: 50px;"><td></td><td></td><td></td><td></td></tr>
                    <tr class="font-weight-bold"><td>Santoso C Y</td><td>Maman S</td><td>Musa Wahab</td><td>{{ Auth::user()->name ?? 'Wahab' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade no-print" id="modalCreate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white"><h6 class="modal-title font-weight-bold">INPUT DATA HARIAN</h6></div>
            <form action="{{ route('fg-daily.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3"><label class="small font-weight-bold">TANGGAL LAPORAN</label><input type="date" name="report_date" class="form-control" value="{{ $date }}" required></div>
                    
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-primary">1. PILIH CUSTOMER</label>
                        <select id="customer_filter" class="form-control form-control-sm border-primary">
                            <option value="">-- Pilih Customer --</option>
                            @foreach($parts->unique('customer_code') as $p)
                                <option value="{{ $p->customer_code }}">{{ $p->customer_code }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-primary">2. PILIH PART NO</label>
                        <select name="part_no" id="part_select" class="form-control form-control-sm" required disabled>
                            <option value="">-- Pilih Customer Dulu --</option>
                        </select>
                    </div>

                    <input type="hidden" name="part_name" id="p_name">
                    <input type="hidden" name="customer_code" id="p_cust">

                    <div class="row no-gutters bg-light p-3 rounded shadow-sm text-center">
                        <div class="col-4 border-right"><label class="small font-weight-bold">AWAL</label><input type="number" name="stock_awal" class="form-control form-control-sm border-0 text-center" value="0"></div>
                        <div class="col-4 border-right"><label class="small font-weight-bold text-success">IN (+)</label><input type="number" name="in" class="form-control form-control-sm border-0 text-center text-success font-weight-bold" value="0"></div>
                        <div class="col-4"><label class="small font-weight-bold text-danger">OUT (-)</label><input type="number" name="out" class="form-control form-control-sm border-0 text-center text-danger font-weight-bold" value="0"></div>
                    </div>
                    <div class="form-group mt-3"><label class="small font-weight-bold">KETERANGAN</label><textarea name="keterangan" class="form-control form-control-sm" rows="2"></textarea></div>
                </div>
                <div class="modal-footer border-0"><button type="submit" class="btn btn-primary btn-block font-weight-bold">SIMPAN DATA</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade no-print" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark"><h6 class="modal-title font-weight-bold">EDIT GERAK STOK</h6></div>
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-2"><label class="small font-weight-bold text-muted text-uppercase">Tanggal Laporan</label><input type="date" name="report_date" id="edit_date" class="form-control form-control-sm" required></div>
                    <div class="form-group mb-3"><label class="small font-weight-bold">PART NO</label><input type="text" id="edit_part" class="form-control form-control-sm bg-white" readonly></div>
                    <div class="row no-gutters bg-white p-3 rounded shadow-sm text-center">
                        <div class="col-4 border-right"><label class="small">AWAL</label><input type="number" name="stock_awal" id="edit_awal" class="form-control form-control-sm border-0 text-center"></div>
                        <div class="col-4 border-right"><label class="small text-success font-weight-bold">IN</label><input type="number" name="in" id="edit_in" class="form-control form-control-sm border-0 text-center text-success font-weight-bold"></div>
                        <div class="col-4"><label class="small text-danger font-weight-bold">OUT</label><input type="number" name="out" id="edit_out" class="form-control form-control-sm border-0 text-center text-danger font-weight-bold"></div>
                    </div>
                    <div class="form-group mt-3"><label class="small font-weight-bold">KETERANGAN</label><textarea name="keterangan" id="edit_ket" class="form-control form-control-sm" rows="2"></textarea></div>
                </div>
                <div class="modal-footer border-0"><button type="submit" class="btn btn-warning btn-block font-weight-bold">UPDATE DATA</button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Data parts dari Laravel dijadikan JSON
        var allParts = @json($parts);

        // LOGIKA CHAINED DROPDOWN
        $('#customer_filter').on('change', function() {
            var customerCode = $(this).val();
            var partSelect = $('#part_select');

            partSelect.empty().append('<option value="">-- Pilih Part --</option>');
            
            if (customerCode) {
                // Filter data
                var filtered = allParts.filter(function(part) {
                    return part.customer_code == customerCode;
                });

                filtered.forEach(function(part) {
                    partSelect.append('<option value="'+ part.part_no +'" data-name="'+ part.part_name +'" data-cust="'+ part.customer_code +'">'+ part.part_no +' | '+ part.part_name +'</option>');
                });

                partSelect.prop('disabled', false);
            } else {
                partSelect.prop('disabled', true);
            }
        });

        // Auto-fill hidden fields
        $('#part_select').on('change', function() {
            var opt = $(this).find(':selected');
            $('#p_name').val(opt.data('name'));
            $('#p_cust').val(opt.data('cust'));
        });

        // Live Search
        $("#liveSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $(".row-data").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // Edit Modal Trigger
        $('.btn-edit-trigger').on('click', function() {
            $('#formEdit').attr('action', '/fg-daily/' + $(this).data('id'));
            $('#edit_date').val($(this).data('date'));
            $('#edit_part').val($(this).data('part'));
            $('#edit_awal').val($(this).data('awal'));
            $('#edit_in').val($(this).data('in'));
            $('#edit_out').val($(this).data('out'));
            $('#edit_ket').val($(this).data('ket'));
            $('#modalEdit').modal('show');
        });
    });
</script>
@endsection