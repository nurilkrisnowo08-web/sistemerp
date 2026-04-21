@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">

<style>
    :root {
        --ind-navy: #0f172a;
        --ind-blue: #2563eb;
        --ind-amber: #d97706;
        --ind-emerald: #059669;
        --ind-rose: #e11d48;
        --ind-slate: #f1f5f9;
        --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
    }

    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
    .industrial-header { font-family: 'Orbitron', sans-serif; letter-spacing: 1px; color: var(--ind-navy); }
    
    /* Alert & Notification */
    .alert-custom { border-radius: 12px; border: none; font-weight: 500; padding: 1rem 1.5rem; }
    
    /* Card System */
    .qc-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--card-shadow);
    }
    .qc-card:hover { transform: translateY(-5px); border-color: var(--ind-blue); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
    
    .card-header-ind {
        background: #fcfcfd;
        padding: 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        border-radius: 16px 16px 0 0;
    }

    /* Technical Typography */
    .tech-code { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: #64748b; font-weight: 700; letter-spacing: -0.2px; }
    .part-title { font-weight: 800; font-size: 1.25rem; color: var(--ind-navy); margin-top: 2px; }
    .qty-badge { font-family: 'Orbitron', sans-serif; font-size: 1.75rem; font-weight: 800; line-height: 1; }

    /* Form Fields */
    .label-ind { font-family: 'Inter', sans-serif; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.5rem; display: block; }
    .input-ind { 
        background: #ffffff; 
        border: 2px solid #e2e8f0; 
        border-radius: 10px; 
        font-weight: 700; 
        padding: 0.75rem; 
        transition: 0.2s;
    }
    .input-ind:focus { border-color: var(--ind-blue); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); outline: none; }

    /* Buttons */
    .btn-action {
        font-family: 'Orbitron', sans-serif;
        font-size: 0.8rem;
        padding: 14px;
        border-radius: 10px;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.5px;
        transition: all 0.2s;
    }
    .btn-release { background: var(--ind-navy); color: #fff; border: none; }
    .btn-release:hover { background: #000; box-shadow: 0 5px 15px rgba(15, 23, 42, 0.3); }
    
    .btn-reject { 
        background: transparent; 
        color: var(--ind-rose); 
        border: 2px solid #fee2e2; 
        font-family: 'Inter', sans-serif;
        font-size: 0.7rem;
        padding: 8px;
    }
    .btn-reject:hover { background: #fee2e2; border-color: #fecaca; }

    .empty-placeholder { 
        border: 2px dashed #cbd5e1; 
        border-radius: 16px; 
        padding: 4rem 2rem; 
        color: #94a3b8; 
        font-weight: 600; 
        text-align: center;
        background: #fcfcfd;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-5 bg-white p-4 rounded-xl border shadow-sm">
        <div>
            <h2 class="industrial-header m-0">UNIT_VERIFICATION <span class="text-primary">v4.0</span></h2>
            <div class="d-flex align-items-center mt-2">
                <span class="badge badge-success badge-pill mr-2" style="width: 8px; height: 8px; padding: 0;"> </span>
                <small class="text-muted font-weight-bold uppercase tracking-wider">Operational Mode: Active Inspection</small>
            </div>
        </div>
        <div class="text-right">
            <span class="badge badge-outline-primary border-primary px-3 py-2 text-primary font-weight-bold" style="border-width: 2px; border-radius: 8px;">
                <i class="fas fa-sync-alt fa-spin mr-2"></i> DATA FEED ACTIVE
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-custom mb-4 shadow-sm border-left-success" style="border-left: 6px solid #10b981 !important;">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-lg mr-3"></i>
                <div>
                    <strong class="d-block">Transaction Successful</strong>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-custom mb-4 shadow-sm border-left-danger" style="border-left: 6px solid #ef4444 !important;">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-lg mr-3"></i>
                <div>
                    <strong class="d-block">System Exception</strong>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="d-flex align-items-center mb-4 px-2">
                <div style="width: 5px; height: 25px; background: var(--ind-blue); border-radius: 10px;" class="mr-3"></div>
                <h5 class="font-weight-bold m-0 text-uppercase tracking-tight">Production Queue</h5>
            </div>

            <div class="row">
                @forelse($produksiQueue as $p)
                <div class="col-md-12 mb-4">
                    <div class="qc-card">
                        <div class="card-header-ind d-flex justify-content-between align-items-start">
                            <div>
                                <div class="tech-code">BATCH_SERIAL: {{ $p->no_produksi }}</div>
                                <div class="part-title">{{ $p->material_code }}</div>
                            </div>
                            <div class="text-right">
                                <label class="label-ind mb-1">Production Qty</label>
                                <div class="qty-badge text-primary">{{ number_format($p->qty_hasil_ok) }}</div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('quality.approve', ['type' => 'stamping', 'id' => $p->id]) }}" method="POST" class="mb-3">
                                @csrf
                                <div class="row mb-4">
                                    <div class="col-6">
                                        <label class="label-ind text-emerald"><i class="fas fa-check-circle mr-1"></i> Qty OK (Final)</label>
                                        <input type="number" name="qty_ok_final" class="form-control input-ind text-emerald" value="{{ $p->qty_hasil_ok }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="label-ind text-rose"><i class="fas fa-times-circle mr-1"></i> Qty NG (Verify)</label>
                                        <input type="number" name="qty_ng_final" class="form-control input-ind text-rose" value="0" required>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="label-ind">Authorized Inspector</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light border-right-0" style="border: 2px solid #e2e8f0; border-radius: 10px 0 0 10px;"><i class="fas fa-user-check text-muted"></i></span>
                                        </div>
                                        <input type="text" name="inspector_name" class="form-control input-ind border-left-0" value="{{ Auth::user()?->name }}" placeholder="Entry Name" required>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="label-ind">Anomaly / Reject Reason</label>
                                    <textarea name="ng_reason" class="form-control input-ind" rows="2" placeholder="Describe defects if any..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-block btn-action btn-release shadow-sm">
                                    Commit Release to Inventory <i class="fas fa-file-export ml-2"></i>
                                </button>
                            </form>
                            
                            <form action="{{ route('quality.destroy', ['type' => 'stamping', 'id' => $p->id]) }}" method="POST" onsubmit="return confirm('Attention: Permanent deletion of production batch. Proceed?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-block btn-reject font-weight-bold">
                                    <i class="fas fa-trash-alt mr-2"></i> Reject & Purge Batch Data
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12"><div class="empty-placeholder">NO_PENDING_PRODUCTION_DATA</div></div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="d-flex align-items-center mb-4 px-2 text-warning">
                <div style="width: 5px; height: 25px; background: var(--ind-amber); border-radius: 10px;" class="mr-3"></div>
                <h5 class="font-weight-bold m-0 text-uppercase tracking-tight">Welding Queue</h5>
            </div>

            <div class="row">
                @forelse($weldingQueue as $w)
                <div class="col-md-12 mb-4">
                    <div class="qc-card" style="border-top: 5px solid var(--ind-amber);">
                        <div class="card-header-ind d-flex justify-content-between align-items-start">
                            <div>
                                <div class="tech-code">WLD_BATCH: {{ $w->no_produksi_stamping }}</div>
                                <div class="part-title">{{ $w->part_no }}</div>
                            </div>
                            <div class="text-right">
                                <label class="label-ind mb-1">Welded Qty</label>
                                <div class="qty-badge text-warning">{{ number_format($w->qty_ok) }}</div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('quality.approve', ['type' => 'welding', 'id' => $w->id]) }}" method="POST" class="mb-3">
                                @csrf
                                <div class="row mb-4">
                                    <div class="col-6">
                                        <label class="label-ind text-emerald">Qty OK (Final)</label>
                                        <input type="number" name="qty_ok_final" class="form-control input-ind text-emerald" value="{{ $w->qty_ok }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="label-ind text-rose">Qty NG (Verify)</label>
                                        <input type="number" name="qty_ng_final" class="form-control input-ind text-rose" value="0" required>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="label-ind">Authorized Inspector</label>
                                    <input type="text" name="inspector_name" class="form-control input-ind" value="{{ Auth::user()?->name }}" required>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="label-ind">Weld Defect Analysis</label>
                                    <textarea name="ng_reason" class="form-control input-ind" rows="2" placeholder="Record welding anomalies..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-block btn-action shadow-sm" style="background: var(--ind-amber); color: #fff; border: none;">
                                    Verify & Store to FG <i class="fas fa-shield-alt ml-2"></i>
                                </button>
                            </form>

                            <form action="{{ route('quality.destroy', ['type' => 'welding', 'id' => $w->id]) }}" method="POST" onsubmit="return confirm('Attention: Permanent deletion of welding batch. Proceed?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-block btn-reject font-weight-bold">
                                    <i class="fas fa-trash-alt mr-2"></i> Purge Welding Batch
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12"><div class="empty-placeholder">NO_PENDING_WELDING_DATA</div></div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection