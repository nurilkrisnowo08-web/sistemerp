@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="font-weight-bold text-dark uppercase" style="font-family: 'Orbitron';">Welding_MPS <small class="text-primary">v2</small></h2>
        <button class="btn btn-dark rounded-pill px-4" data-toggle="modal" data-target="#modalAddPlan">+ ADD WELDING SCHEDULE</button>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px;">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="pl-4">STATION</th>
                    <th>PART NO</th>
                    <th class="text-center">TARGET (S1+S2)</th>
                    <th class="text-center">ACTUAL</th>
                    <th class="text-center">BALANCE</th>
                    <th class="text-right pr-4">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plans as $p)
                <tr>
                    <td class="pl-4"><span class="badge badge-dark px-3 py-2" style="font-family: 'JetBrains Mono';">{{ $p->line_code }}</span></td>
                    <td class="font-weight-bold">{{ $p->part_no }}</td>
                    <td class="text-center font-weight-bold">{{ number_format($p->total_target) }}</td>
                    <td class="text-center text-success">{{ number_format($p->total_actual) }}</td>
                    <td class="text-center {{ $p->balance > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($p->balance) }}</td>
                    <td class="text-right pr-4">
                        @if($p->balance <= 0)
                            <span class="badge badge-success px-3 rounded-pill">COMPLETED</span>
                        @else
                            <span class="badge badge-warning px-3 rounded-pill">IN PROGRESS</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL ADD --}}
<div class="modal fade" id="modalAddPlan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white border-0">
                <h6 class="modal-title font-weight-bold">REGISTER_WELDING_PLAN</h6>
            </div>
            <form action="{{ route('ppic.welding.mps_store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="small font-weight-bold">PLAN DATE</label>
                            <input type="date" name="plan_date" class="form-control" value="{{ $date }}" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="small font-weight-bold">WELDING STATION</label>
                            <select name="line_code" class="form-control" required>
                                @foreach($availableLines as $line)
                                    <option value="{{ $line->kode_line }}">{{ $line->kode_line }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="small font-weight-bold">PART IDENTIFICATION</label>
                            <input type="text" name="part_no" class="form-control" required placeholder="MN530002X">
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold">SHIFT 1 TARGET</label>
                            <input type="number" name="s1_plan_reg" class="form-control" value="0">
                        </div>
                        <div class="col-6">
                            <label class="small font-weight-bold">SHIFT 2 TARGET</label>
                            <input type="number" name="s2_plan_reg" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary btn-block font-weight-bold py-3 shadow-lg">AUTHORIZE SCHEDULE</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection