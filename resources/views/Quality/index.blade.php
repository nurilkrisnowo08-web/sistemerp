@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root {
        --ind-navy: #1e293b;
        --ind-blue: #3b82f6;
        --ind-amber: #f59e0b;
        --ind-emerald: #10b981;
        --ind-rose: #f43f5e;
        --ind-border: #e2e8f0;
    }

    .industrial-title { font-family: 'Orbitron', sans-serif; color: var(--ind-navy); letter-spacing: 1px; text-transform: uppercase; }
    .qc-card { background: #ffffff; border: 1px solid var(--ind-border); border-radius: 12px; transition: 0.2s; }
    .qc-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1); }
    .card-header-ind { padding: 15px 20px; border-bottom: 1px solid var(--ind-border); background: #f8fafc; border-radius: 12px 12px 0 0; }
    .batch-no { font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; color: #64748b; }
    .part-no { font-weight: 800; font-size: 1.1rem; color: var(--ind-navy); }
    .qty-display { font-family: 'Orbitron', sans-serif; font-size: 1.8rem; font-weight: 700; }
    .label-ind { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; display: block; }
    .input-ind { border: 2px solid #f1f5f9; border-radius: 8px; font-weight: 600; padding: 10px; }
    .btn-release { font-family: 'Orbitron', sans-serif; font-size: 0.75rem; padding: 12px; border-radius: 8px; text-transform: uppercase; font-weight: 700; border: none; }
    .btn-produksi { background: var(--ind-navy); color: #fff; }
    .btn-welding { background: var(--ind-amber); color: #fff; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-5 bg-white p-4 rounded-lg shadow-sm border">
        <div>
            <h2 class="industrial-title m-0">Quality_Gate <span class="text-primary">System rill</span></h2>
            <p class="text-muted small mb-0 font-weight-bold">UNIT_VERIFICATION_MODULE_V4.0 // PRODUKSI_CORE</p>
        </div>
        <span class="badge badge-soft-primary p-2 px-3 border border-primary text-primary font-weight-bold">LIVE_DATA</span>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <h5 class="font-weight-bold mb-4">PRODUKSI_QUEUE</h5>
            @forelse($produksiQueue as $p)
            <div class="qc-card mb-4">
                <div class="card-header-ind d-flex justify-content-between">
                    <div>
                        <div class="batch-no">BATCH: {{ $p->no_produksi }}</div>
                        <div class="part-no">{{ $p->material_code }}</div>
                    </div>
                    <div class="qty-display text-primary">{{ number_format($p->qty_hasil_ok) }}</div>
                </div>
                <div class="card-body p-4">
                    {{-- FIX: Route disesuaikan rill! --}}
                    <form action="{{ route('quality.approve', ['type' => 'stamping', 'id' => $p->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="label-ind text-success">Qty_Ok (Final)</label>
                                <input type="number" name="qty_ok_final" class="form-control input-ind" value="{{ $p->qty_hasil_ok }}" required>
                            </div>
                            <div class="col-6">
                                <label class="label-ind text-danger">Qty_Ng (Verify)</label>
                                <input type="number" name="qty_ng_final" class="form-control input-ind" value="0">
                            </div>
                        </div>
                        <textarea name="ng_reason" class="form-control input-ind mb-3" placeholder="Alasan jika ada NG..."></textarea>
                        <button type="submit" class="btn btn-block btn-release btn-produksi">Release_to_Finished_Goods rill</button>
                    </form>
                </div>
            </div>
            @empty
            <p class="text-muted">PRODUKSI_BAY_IS_CLEAR rill</p>
            @endforelse
        </div>

        <div class="col-lg-6 mb-4">
            <h5 class="font-weight-bold mb-4">WELDING_QUEUE</h5>
            @forelse($weldingQueue as $w)
            <div class="qc-card mb-4" style="border-left: 5px solid var(--ind-amber);">
                <div class="card-header-ind d-flex justify-content-between">
                    <div>
                        <div class="batch-no">BATCH: {{ $w->no_produksi_stamping }}</div>
                        <div class="part-no">{{ $w->part_no }}</div>
                    </div>
                    <div class="qty-display text-warning">{{ number_format($w->qty_ok) }}</div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('quality.approve', ['type' => 'welding', 'id' => $w->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-6"><input type="number" name="qty_ok_final" class="form-control input-ind" value="{{ $w->qty_ok }}" required></div>
                            <div class="col-6"><input type="number" name="qty_ng_final" class="form-control input-ind" value="0"></div>
                        </div>
                        <textarea name="ng_reason" class="form-control input-ind mb-3" placeholder="Describe defects..."></textarea>
                        <button type="submit" class="btn btn-block btn-release btn-welding">Verify_&_Store_FG rill</button>
                    </form>
                </div>
            </div>
            @empty
            <p class="text-muted">WELDING_BAY_IS_CLEAR rill</p>
            @endforelse
        </div>
    </div>
</div>
@endsection