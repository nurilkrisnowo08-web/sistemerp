@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="mb-5">
        <h1 class="font-weight-bold uppercase" style="font-family: 'Orbitron';">Welding_Quality <small class="text-danger">Audit</small></h1>
        <p class="text-muted font-weight-bold small uppercase">Defect Tracking & Reject Analysis for Welding Operations</p>
    </div>

    <div class="row">
        {{-- RANKING NG --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 24px;">
                <h6 class="font-weight-bold text-dark uppercase small mb-4">Top Defect Ranking (NG)</h6>
                @foreach($ngRanking as $index => $ng)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="badge badge-danger mr-2">#{{ $index+1 }}</span>
                        <span class="font-weight-bold uppercase">{{ $ng->ng_type }}</span>
                    </div>
                    <div class="font-weight-black text-danger h5 mb-0">{{ $ng->total }} <small>PCS</small></div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- DETAIL PER MESIN --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 24px;">
                <table class="table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="pl-4">STATION</th>
                            <th>PART NO</th>
                            <th class="text-center text-success">OK</th>
                            <th class="text-center text-danger">NG</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($details as $d)
                        <tr>
                            <td class="pl-4 font-weight-bold">{{ $d->line_code }}</td>
                            <td class="font-weight-bold text-primary">{{ $d->part_no }}</td>
                            <td class="text-center font-weight-black">{{ number_format($d->qty_ok) }}</td>
                            <td class="text-center font-weight-black text-danger">{{ number_format($d->qty_ng) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection