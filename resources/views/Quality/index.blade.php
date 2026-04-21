@extends('layout.admin')

@section('content')
<style>
    :root {
        --glow-blue: rgba(67, 97, 238, 0.3);
        --glow-success: rgba(46, 213, 115, 0.3);
        --industrial-navy: #0f172a;
    }

    .industrial-header {
        font-family: 'Orbitron', sans-serif;
        letter-spacing: 2px;
        text-transform: uppercase;
        text-shadow: 0 0 10px var(--glow-blue);
    }

    .qc-card {
        background: #ffffff;
        border: none;
        border-radius: 20px;
        transition: all 0.3s ease;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .qc-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    .batch-badge {
        font-family: 'JetBrains Mono', monospace;
        background: var(--industrial-navy);
        color: #fff;
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
    }

    .input-industrial {
        border-radius: 10px;
        border: 2px solid #f1f5f9;
        font-weight: 700;
        transition: 0.3s;
    }

    .input-industrial:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 4px var(--glow-blue);
    }

    .btn-release {
        border-radius: 12px;
        padding: 12px;
        font-family: 'Orbitron', sans-serif;
        font-size: 0.8rem;
        letter-spacing: 1px;
        transition: 0.3s;
        border: none;
    }

    .btn-release-stamping {
        background: linear-gradient(45deg, #4361ee, #4895ef);
        box-shadow: 0 4px 15px var(--glow-blue);
    }

    .btn-release-welding {
        background: linear-gradient(45deg, #f59e0b, #fbbf24);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    .section-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        color: var(--industrial-navy);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .empty-state {
        border: 2px dashed #cbd5e1;
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        color: #94a3b8;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-5">
        <div>
            <h2 class="industrial-header font-weight-black mb-0">Quality <span class="text-primary">Gate rill</span></h2>
            <p class="text-muted small font-weight-bold">INDUSTRIAL_CORE // VERIFICATION_UNIT_v4.0</p>
        </div>
        <div class="text-right">
            <span class="badge badge-soft-primary px-3 py-2 border border-primary text-primary">
                <i class="fas fa-sync fa-spin mr-2"></i> LIVE_MONITORING rill
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="section-title mb-4">
                <div style="width: 12px; height: 30px; background: #4361ee; border-radius: 4px;"></div>
                <h4 class="mb-0">STAMPING <span class="opacity-50">LINE</span></h4>
            </div>

            <div class="row">
                @forelse($stampingQueue as $s)
                <div class="col-md-12">
                    <div class="qc-card mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <span class="batch-badge mb-2 d-inline-block">{{ $s->no_produksi }}</span>
                                    <h5 class="font-weight-bold text-dark mb-0 tracking-tight">{{ $s->material_code }}</h5>
                                </div>
                                <div class="text-right">
                                    <small class="text-muted d-block font-weight-bold">PROD_REPORT</small>
                                    <h4 class="font-weight-black text-primary mb-0">{{ number_format($s->qty_hasil_ok) }}</h4>
                                </div>
                            </div>

                            <form action="{{ route('quality.approve', ['type' => 'stamping', 'id' => $s->id]) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="small font-weight-extrabold text-success">QTY OK (FINAL) rill</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text bg-white border-right-0"><i class="fas fa-check-circle text-success"></i></span></div>
                                            <input type="number" name="qty_ok_final" class="form-control input-industrial border-left-0" value="{{ $s->qty_hasil_ok }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="small font-weight-extrabold text-danger">QTY NG (TEMUAN QC)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text bg-white border-right-0"><i class="fas fa-times-circle text-danger"></i></span></div>
                                            <input type="number" name="qty_ng_final" class="form-control input-industrial border-left-0" value="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-4">
                                    <textarea name="ng_reason" class="form-control input-industrial" rows="2" placeholder="Tulis alasan jika ada barang NG rill..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block btn-release btn-release-stamping text-white font-weight-bold">
                                    RELEASE TO FG rill <i class="fas fa-paper-plane ml-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="empty-state">
                        <i class="fas fa-box-open fa-3x mb-3 opacity-20"></i>
                        <p class="font-weight-bold mb-0">ANTREAN STAMPING BERSIH RILL</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="section-title mb-4 text-warning">
                <div style="width: 12px; height: 30px; background: #f59e0b; border-radius: 4px;"></div>
                <h4 class="mb-0">WELDING <span class="opacity-50 text-dark">LINE</span></h4>
            </div>

            <div class="row">
                @forelse($weldingQueue as $w)
                <div class="col-md-12">
                    <div class="qc-card mb-4" style="border-top: 5px solid #f59e0b;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <span class="batch-badge mb-2 d-inline-block bg-warning">{{ $w->no_produksi_stamping }}</span>
                                    <h5 class="font-weight-bold text-dark mb-0 tracking-tight">{{ $w->part_no }}</h5>
                                </div>
                                <div class="text-right">
                                    <small class="text-muted d-block font-weight-bold">WELDING_OUT</small>
                                    <h4 class="font-weight-black text-warning mb-0">{{ number_format($w->qty_ok) }}</h4>
                                </div>
                            </div>

                            <form action="{{ route('quality.approve', ['type' => 'welding', 'id' => $w->id]) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="small font-weight-extrabold text-success">QTY OK (FINAL) rill</label>
                                        <input type="number" name="qty_ok_final" class="form-control input-industrial" value="{{ $w->qty_ok }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="small font-weight-extrabold text-danger">QTY NG (TEMUAN QC)</label>
                                        <input type="number" name="qty_ng_final" class="form-control input-industrial" value="0">
                                    </div>
                                </div>
                                <textarea name="ng_reason" class="form-control input-industrial mb-4" rows="2" placeholder="Alasan NG Welding rill..."></textarea>
                                <button type="submit" class="btn btn-warning btn-block btn-release btn-release-welding text-white font-weight-bold">
                                    APPROVE TO FG rill <i class="fas fa-shield-alt ml-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="empty-state">
                        <i class="fas fa-fire-extinguisher fa-3x mb-3 opacity-20"></i>
                        <p class="font-weight-bold mb-0">MEJA QC WELDING KOSONG RILL</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection