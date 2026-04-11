@extends('layout.admin')

@section('content')
<style>
    .table-matrix { font-size: 10px; border-collapse: separate; border-spacing: 0; width: 100%; color: #000; }
    .table-matrix th, .table-matrix td { border: 1px solid #000; padding: 3px; text-align: center; }
    .sticky-part { position: sticky; left: 0; background: #fff; z-index: 10; min-width: 140px; border-right: 2px solid #000 !important; }
    .sticky-cat { position: sticky; left: 140px; background: #f8f9fc; z-index: 9; min-width: 110px; border-right: 2px solid #000 !important; }
    .row-order-prod { background-color: #ccffcc; } /* Hijau */
    .row-incoming { background-color: #e0f2ff; }   /* Biru */
    .row-stock-fg { background-color: #d1ffd1; font-weight: bold; } /* Hijau Terang */
    .sunday { background: #ff4d4d !important; color: #fff; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3 border-bottom border-dark pb-2">
        <h5 class="font-weight-bold">MONITORING MATERIAL FINISH GOOD</h5>
        <button type="button" class="btn btn-primary btn-sm font-weight-bold" data-toggle="modal" data-target="#modalInput">+ INPUT DATA</button>
    </div>

    <div class="table-responsive shadow-sm" style="max-height: 75vh;">
        <table class="table-matrix">
            <thead class="bg-primary text-white">
                <tr>
                    <th rowspan="2" class="sticky-part">PART NO</th>
                    <th rowspan="2" class="sticky-cat">KATEGORI</th>
                    @for($d=1; $d<=$daysInMonth; $d++)
                        <th class="{{ \Carbon\Carbon::create($year,$month,$d)->isSunday() ? 'sunday' : '' }}">{{ $d }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($parts as $part)
                @php 
                    $categories = [
                        'plan_delv' => 'PLAN DELV', 'act_dn' => 'ACT DN', 'act_delv' => 'ACT DELV',
                        'order_produksi' => 'ORDER PRODUKSI', 'incoming_part' => 'INCOMING PART', 'stock_fg' => 'STOCK FG'
                    ];
                @endphp
                @foreach($categories as $key => $label)
                <tr class="{{ in_array($key, ['order_produksi']) ? 'row-order-prod' : ($key == 'incoming_part' ? 'row-incoming' : ($key == 'stock_fg' ? 'row-stock-fg' : '')) }}">
                    @if($loop->first) <td rowspan="6" class="sticky-part text-left"><b>{{ $part->part_no }}</b></td> @endif
                    <td class="sticky-cat text-left"><b>{{ $label }}</b></td>
                    @for($d=1; $d<=$daysInMonth; $d++)
                        @php 
                            $val = $stockData[$part->part_no][$d][0]->$key ?? 0;
                            $dateStr = "$year-$month-" . sprintf('%02d', $d);
                        @endphp
                        {{-- Klik sel mana saja untuk input/edit manual --}}
                        <td onclick="inputManual('{{ $part->part_no }}', '{{ $dateStr }}')" style="cursor: pointer;">
                            {{ $val ?: '-' }}
                        </td>
                    @endfor
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Area Tanda Tangan --}}
    <div class="mt-4 d-flex justify-content-end">
        <table border="1" style="width: 400px; text-align: center; font-size: 10px; border-collapse: collapse;">
            <tr class="font-weight-bold"><th>Diketahui</th><th>Disetujui</th><th>Dicek</th><th>Dibuat</th></tr>
            <tr style="height: 50px;"><td></td><td></td><td></td><td></td></tr>
            <tr class="font-weight-bold"><td>Santoso C Y</td><td>Maman S</td><td>Musa Wahab</td><td>{{ auth()->user()->name ?? 'Rado U' }}</td></tr>
        </table>
    </div>
</div>

{{-- MODAL INPUT --}}
<div class="modal fade" id="modalInput" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('kontrol.fg.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white"><h6 class="modal-title font-weight-bold">INPUT/EDIT DATA</h6></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="small font-weight-bold">PART NO</label>
                            <select name="part_no" class="form-control form-control-sm" id="modal_part_no" required>
                                @foreach($parts as $p) <option value="{{ $p->part_no }}">{{ $p->part_no }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small font-weight-bold">TANGGAL</label>
                            <input type="date" name="control_date" class="form-control form-control-sm" id="modal_date" required>
                        </div>
                        <div class="col-md-4 mb-2"><label class="small">PLAN DELV</label><input type="number" name="plan_delv" class="form-control form-control-sm"></div>
                        <div class="col-md-4 mb-2"><label class="small">ACT DELV</label><input type="number" name="act_delv" class="form-control form-control-sm"></div>
                        <div class="col-md-4 mb-2"><label class="small">INCOMING</label><input type="number" name="incoming_part" class="form-control form-control-sm"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary btn-block">SIMPAN DATA</button></div>
            </form>
        </div>
    </div>
</div>

<script>
    function inputManual(partNo, date) {
        $('#modal_part_no').val(partNo);
        $('#modal_date').val(date);
        $('#modalInput').modal('show');
    }
</script>
@endsection