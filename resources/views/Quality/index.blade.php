@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

<style>
    :root {
        --ind-navy: #0f172a;
        --ind-blue: #2563eb;
        --ind-amber: #d97706;
        --ind-emerald: #059669;
        --ind-rose: #e11d48;
    }

    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    .industrial-header { font-family: 'Orbitron', sans-serif; letter-spacing: 1px; color: var(--ind-navy); }
    .alert-ind { border-radius: 10px; border: none; font-weight: 600; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .qc-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.3s ease; }
    .qc-card:hover { transform: translateY(-4px); box-shadow: 0 12px 20px -5px rgba(0,0,0,0.1); border-color: var(--ind-blue); }
    .card-header-ind { background: #f8fafc; padding: 1.25rem; border-bottom: 1px solid #e2e8f0; border-radius: 12px 12px 0 0; }
    .tech-code { font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; color: #64748b; font-weight: 700; }
    .part-title { font-weight: 800; font-size: 1.2rem; color: var(--ind-navy); }
    .qty-badge { font-family: 'Orbitron', sans-serif; font-size: 1.6rem; font-weight: 800; }
    .label-ind { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: #475569; margin-bottom: 0.4rem; display: block; }
    .input-ind { border: 2px solid #e2e8f0; border-radius: 8px; font-weight: 700; padding: 0.6rem; transition: 0.2s; }
    .input-ind:focus { border-color: var(--ind-blue); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); outline: none; }
    .btn-execute { font-family: 'Orbitron', sans-serif; font-size: 0.75rem; padding: 12px; border-radius: 8px; text-transform: uppercase; font-weight: 800; border: none; letter-spacing: 1px; color: white; transition: 0.2s; }
    .btn-prod { background: var(--ind-navy); }
    .btn-wld { background: var(--ind-amber); }
    .empty-placeholder { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 3rem; color: #94a3b8; font-weight: 700; text-align: center; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4 bg-white p-4 rounded-xl shadow-sm border">
        <div>
            <h2 class="industrial-header m-0">UNIT_VERIFICATION <span class="text-primary">v4.0 rill</span></h2>
            <small class="text-muted font-weight-bold">SYSTEM_OPERATIONAL // HUB_CONNECTED</small>
        </div>
        <div class="text-right">
            <span class="badge badge-soft-primary border border-primary px-3 py-2 text-primary font-weight-bold">LIVE_DATA</span>
        </div>
    </div>

    {{-- ALERT SYSTEM rill --}}
    @if(session('success'))
        <div class="alert alert-success alert-ind mb-4 border-left-success" style="border-left: 5px solid #10b981 !important;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-ind mb-4 border-left-danger" style="border-left: 5px solid #ef4444 !important;">
            <i class="fas fa-exclamation-triangle mr-2"></i> <b>GAGAL RILL:</b> {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6 mb-4">
            <h5 class="font-weight-bold mb-3 ml-2">PRODUKSI_ANTICIPATED rill</h5>
            <div class="row">
                @forelse($produksiQueue as $p)
                <div class="col-md-12 mb-4">
                    <div class="qc-card">
                        <div class="card-header-ind d-flex justify-content-between align-items-center">
                            <div>
                                <div class="tech-code">BATCH: {{ $p->no_produksi }}</div>
                                <div class="part-title">{{ $p->material_code }}</div>
                            </div>
                            <div class="text-right">
                                <label class="label-ind mb-0">Reported</label>
                                <div class="qty-badge text-primary">{{ number_format($p->qty_hasil_ok) }}</div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('quality.approve', ['type' => 'stamping', 'id' => $p->id]) }}" method="POST">
                                @csrf
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="label-ind text-emerald">Qty_Ok (Final)</label>
                                        <input type="number" name="qty_ok_final" class="form-control input-ind text-emerald" value="{{ $p->qty_hasil_ok }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="label-ind text-rose">Qty_Ng (Verify)</label>
                                        <input type="number" name="qty_ng_final" class="form-control input-ind text-rose" value="0">
                                    </div>
                                </div>

                                {{-- ✨ BARU: Input Inspector rill --}}
                                <div class="form-group mb-3">
                                    <label class="label-ind">Authorized_Inspector rill</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-user-shield text-muted"></i></span>
                                        </div>
                                        <input type="text" name="inspector_name" class="form-control input-ind border-left-0" value="{{ Auth::user()?->name }}" placeholder="Input name..." required>
                                    </div>
                                </div>

                                <textarea name="ng_reason" class="form-control input-ind mb-3" rows="2" placeholder="Describe NG reason if any..."></textarea>
                                <button type="submit" class="btn btn-block btn-execute btn-prod shadow-sm">
                                    Release_to_Inventory <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12"><div class="empty-placeholder">NO_PENDING_PRODUKSI</div></div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <h5 class="font-weight-bold mb-3 ml-2">WELDING_ANTICIPATED rill</h5>
            <div class="row">
                @forelse($weldingQueue as $w)
                <div class="col-md-12 mb-4">
                    <div class="qc-card" style="border-top: 4px solid var(--ind-amber);">
                        <div class="card-header-ind d-flex justify-content-between align-items-center">
                            <div>
                                <div class="tech-code">WLD_BATCH: {{ $w->no_produksi_stamping }}</div>
                                <div class="part-title">{{ $w->part_no }}</div>
                            </div>
                            <div class="text-right">
                                <label class="label-ind mb-0">Weld_Yield</label>
                                <div class="qty-badge text-warning">{{ number_format($w->qty_ok) }}</div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('quality.approve', ['type' => 'welding', 'id' => $w->id]) }}" method="POST">
                                @csrf
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="label-ind text-emerald">Qty_Ok (Final)</label>
                                        <input type="number" name="qty_ok_final" class="form-control input-ind text-emerald" value="{{ $w->qty_ok }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="label-ind text-rose">Qty_Ng (Verify)</label>
                                        <input type="number" name="qty_ng_final" class="form-control input-ind text-rose" value="0">
                                    </div>
                                </div>

                                {{-- ✨ BARU: Input Inspector rill --}}
                                <div class="form-group mb-3">
                                    <label class="label-ind">Authorized_Inspector rill</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-user-shield text-muted"></i></span>
                                        </div>
                                        <input type="text" name="inspector_name" class="form-control input-ind border-left-0" value="{{ Auth::user()?->name }}" placeholder="Input name..." required>
                                    </div>
                                </div>

                                <textarea name="ng_reason" class="form-control input-ind mb-3" rows="2" placeholder="Record defects..."></textarea>
                                <button type="submit" class="btn btn-block btn-execute btn-wld shadow-sm">
                                    Approve_Assembly <i class="fas fa-shield-alt ml-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12"><div class="empty-placeholder">NO_PENDING_WELDING</div></div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection