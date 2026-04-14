@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;800&family=JetBrains+Mono:wght@700&family=Orbitron:wght@700&display=swap" rel="stylesheet">

<style>
    .ledger-header { background: #1e293b; color: white; border-radius: 15px 15px 0 0; padding: 20px; }
    .table-mutation thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; padding: 15px; border-bottom: 2px solid #e2e8f0; }
    .table-mutation td { vertical-align: middle; font-weight: 700; color: #334155; font-size: 13px; }
    .col-init { background: rgba(148, 163, 184, 0.05); color: #64748b; }
    .col-in { background: rgba(16, 185, 129, 0.05); color: #10b981; }
    .col-out { background: rgba(239, 68, 68, 0.05); color: #ef4444; }
    .col-final { background: rgba(67, 97, 238, 0.05); color: #4361ee; font-weight: 800; }
    .badge-alias { font-family: 'JetBrains Mono'; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 11px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h3 class="font-weight-bold mb-0">RM Mutation <span class="text-primary">Ledger</span></h3>
            <p class="text-muted small mb-0 font-weight-bold">RAW MATERIAL INVENTORY FLOW CONTROL rill</p>
        </div>
        
        <form action="{{ route('rm.mutation') }}" method="GET" class="d-flex gap-2">
            <select name="customer" class="form-control rounded-pill border-0 shadow-sm px-4">
                <option value="">-- ALL CLIENTS --</option>
                @foreach($availableCustomers as $c)
                    <option value="{{ trim($c->code) }}" {{ $customer == trim($c->code) ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date" class="form-control rounded-pill border-0 shadow-sm px-4" value="{{ $date }}">
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow"><i class="fas fa-sync-alt"></i></button>
        </form>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px;">
        <div class="table-responsive">
            <table class="table table-mutation mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Coil ID / Alias</th>
                        <th>Material Specification</th>
                        <th class="col-init">Initial</th>
                        <th class="col-in">In (Receive)</th>
                        <th class="col-out">Out (Prod)</th>
                        <th class="col-final">Final Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rmData as $rm)
                    <tr>
                        <td class="text-left pl-4">
                            <div class="font-weight-extrabold text-dark">{{ $rm->coil_id }}</div>
                            <span class="badge-alias text-primary">{{ $rm->alias_code ?? 'NO_ALIAS' }}</span>
                        </td>
                        <td>
                            <div class="small font-weight-bold">{{ $rm->spec }}</div>
                            <div class="text-muted" style="font-size: 10px;">{{ $rm->size }}</div>
                        </td>
                        <td class="col-init">{{ number_format($rm->initial_stock) }}</td>
                        <td class="col-in font-weight-bold">+{{ number_format($rm->in_qty) }}</td>
                        <td class="col-out font-weight-bold">-{{ number_format($rm->out_qty) }}</td>
                        <td class="col-final" style="font-family: 'Orbitron'; font-size: 15px;">{{ number_format($rm->final_stock) }}</td>
                        <td>
                            @if($rm->final_stock <= 0)
                                <span class="badge badge-danger rounded-pill px-3">EMPTY</span>
                            @else
                                <span class="badge badge-success rounded-pill px-3">OK</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-5 text-muted italic">-- NO DATA FOUND IN THIS PERIOD --</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection