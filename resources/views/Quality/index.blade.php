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

    .industrial-title {
        font-family: 'Orbitron', sans-serif;
        color: var(--ind-navy);
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .qc-card {
        background: #ffffff;
        border: 1px solid var(--ind-border);
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .qc-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .card-header-ind {
        padding: 15px 20px;
        border-bottom: 1px solid var(--ind-border);
        background: #f8fafc;
        border-radius: 12px 12px 0 0;
    }

    .batch-no { font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; color: #64748b; }
    .part-no { font-weight: 800; font-size: 1.1rem; color: var(--ind-navy); letter-spacing: -0.5px; }
    .qty-display { font-family: 'Orbitron', sans-serif; font-size: 1.8rem; font-weight: 700; }
    .label-ind { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; display: block; }

    .input-ind {
        border: 2px solid #f1f5f9;
        border-radius: 8px;
        font-weight: 600;
        padding: 10px;
        transition: 0.3s;
    }

    .input-ind:focus { border-color: var(--ind-blue); box-shadow: none; background: #fff; }

    .btn-release {
        font-family: 'Orbitron', sans-serif;
        font-size: 0.75rem;
        padding: 12px;
        border-radius: 8px;
        text-transform: uppercase;
        font-weight: 700;
        transition: 0.3s;
        border: none;
    }

    .btn-produksi { background: var(--ind-navy); color: #fff; }
    .btn-produksi:hover { background: #0f172a; opacity: 0.9; }
    .btn-welding { background: var(--ind-amber); color: #fff; }
    .btn-welding:hover { background: #d97706; }

    .empty-state { border: 2px dashed #cbd5e1; border-radius: 15px; padding: 40px; color: #94a3b8; font-weight: 600; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-5 bg-white p-4 rounded-lg shadow-sm border">
        <div>
            <h2 class="industrial-title m-0">Quality_Gate <span class="text-primary">System</span></h2>
            <p class="text-muted small mb-0 font-weight-bold">UNIT_VERIFICATION_MODULE_V4.0 // PRODUKSI_CORE rill</p>
        </div>
        <div>
            <span class="badge badge-soft-primary p-2 px-3 border border-primary text-primary font-weight-bold">
                <i class="fas fa-satellite-dish mr-2"></i> LIVE_STREAMING_DATA
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-primary rounded-circle p-2 mr-3" style="width: 10px; height: 10px;"></div>
                <h5 class="font-weight-bold m-0" style="letter-spacing: 1px;">PRODUKSI_QUEUE rill</h5>
            </div>

            <div class="row">
                {{-- FIX: Sekarang pake $produksiQueue sesuai Controller rill! --}}
                @forelse($produksiQueue as $p)
                <div class="col-md-12 mb-4">
                    <div class="qc-card">
                        <div class="card-header-ind d-flex justify-content-between align-items-center">
                            <div>
                                <div class="batch-no">BATCH: {{ $p->no_produksi }}</div>
                                <div class="part-no">{{ $p->material_code }}</div>
                            </div>
                            <div class="text-right">
                                <small class="label-ind">Reported_Qty</small>
                                <div class="qty-display text-primary">{{ number_format($p->qty_hasil_ok) }}</div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('quality.approve', ['type' => 'stamping', 'id' => $p->id]) }}" method="POST">
                                @csrf
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="label-ind text-emerald">Qty_Ok (Final)</label>
                                        <input type="number" name="qty_ok_final" class="form-control input-ind" value="{{ $p->qty_hasil_ok }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="label-ind text-rose">Qty_Ng (Verify)</label>
                                        <input type="number" name="qty_ng_final" class="form-control input-ind" value="0">
                                    </div>
                                </div>
                                <div class="form-group mb-4">
                                    <label class="label-ind">Rejection_Reason_Log</label>
                                    <textarea name="ng_reason" class="form-control input-ind" rows="2" placeholder="Input reason if NG detected rill..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-block btn-release btn-produksi shadow-sm">
                                    Release_to_Finished_Goods <i class="fas fa-check-double ml-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center empty-state">
                    <i class="fas fa-clipboard-check fa-2x mb-3"></i>
                    <p class="m-0">PRODUKSI_BAY_IS_CLEAR_RILL</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-warning rounded-circle p-2 mr-3" style="width: 10px; height: 10px;"></div>
                <h5 class="font-weight-bold m-0" style="letter-spacing: 1px;">WELDING_QUEUE rill</h5>
            </div>

            <div class="row">
                @forelse($weldingQueue as $w)
                <div class="col-md-12 mb-4">
                    <div class="qc-card" style="border-left: 5px solid var(--ind-amber);">
                        <div class="card-header-ind d-flex justify-content-between align-items-center">
                            <div>
                                <div class="batch-no">BATCH: {{ $w->no_produksi_stamping }}</div>
                                <div class="part-no">{{ $w->part_no }}</div>
                            </div>
                            <div class="text-right">
                                <small class="label-ind">Welded_Qty</small>
                                <div class="qty-display text-warning">{{ number_format($w->qty_ok) }}</div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('quality.approve', ['type' => 'welding', 'id' => $w->id]) }}" method="POST">
                                @csrf
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="label-ind text-emerald">Qty_Ok (Final)</label>
                                        <input type="number" name="qty_ok_final" class="form-control input-ind" value="{{ $w->qty_ok }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="label-ind text-rose">Qty_Ng (Verify)</label>
                                        <input type="number" name="qty_ng_final" class="form-control input-ind" value="0">
                                    </div>
                                </div>
                                <div class="form-group mb-4">
                                    <label class="label-ind">Defect_Analysis_Log</label>
                                    <textarea name="ng_reason" class="form-control input-ind" rows="2" placeholder="Describe welding defects if any rill..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-block btn-release btn-welding shadow-sm">
                                    Verify_&_Store_FG <i class="fas fa-shield-alt ml-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center empty-state">
                    <i class="fas fa-fire-extinguisher fa-2x mb-3"></i>
                    <p class="m-0">WELDING_BAY_IS_CLEAR_RILL</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection