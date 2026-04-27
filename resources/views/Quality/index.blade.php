@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">

<style>
    :root {
        --ind-navy: #0f172a; --ind-blue: #2563eb; --ind-amber: #d97706;
        --ind-emerald: #059669; --ind-rose: #e11d48; --ind-slate: #f1f5f9;
        --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
    }

    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
    .industrial-header { font-family: 'Orbitron', sans-serif; letter-spacing: 1px; color: var(--ind-navy); }
    .qc-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; transition: 0.3s; box-shadow: var(--card-shadow); }
    .qc-card:hover { border-color: var(--ind-blue); }
    .card-header-ind { background: #fcfcfd; padding: 1.25rem; border-bottom: 1px solid #f1f5f9; border-radius: 16px 16px 0 0; }
    .tech-code { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: #64748b; font-weight: 700; }
    .part-title { font-weight: 800; font-size: 1.25rem; color: var(--ind-navy); }
    .qty-badge { font-family: 'Orbitron', sans-serif; font-size: 1.75rem; font-weight: 800; line-height: 1; }
    .label-ind { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 0.5rem; display: block; }
    .input-ind { background: #ffffff; border: 2px solid #e2e8f0; border-radius: 10px; font-weight: 700; padding: 0.75rem; transition: 0.2s; }
    .input-ind:focus { border-color: var(--ind-blue); outline: none; }
    .btn-action { font-family: 'Orbitron', sans-serif; font-size: 0.8rem; padding: 14px; border-radius: 10px; text-transform: uppercase; font-weight: 800; border: none; }
    .empty-placeholder { border: 2px dashed #cbd5e1; border-radius: 16px; padding: 4rem; color: #94a3b8; font-weight: 600; text-align: center; }
</style>

<div class="container-fluid py-4">
    {{-- 🛸 Header Section --}}
    <div class="d-flex align-items-center justify-content-between mb-5 bg-white p-4 rounded-xl border shadow-sm">
        <div>
            <h2 class="industrial-header m-0">UNIT_VERIFICATION <span class="text-primary">v4.0</span></h2>
            <small class="text-muted font-weight-bold uppercase">Operational Mode: Final Inspection Gate</small>
        </div>
        <div class="text-right d-flex align-items-center">
            <a href="{{ route('quality.history') }}" class="btn btn-outline-dark rounded-pill px-4 font-weight-bold mr-3 shadow-sm">
                <i class="fas fa-history mr-2"></i> QC_HISTORY_AUDIT
            </a>
            <span class="badge badge-outline-primary border-primary px-3 py-2 text-primary font-weight-bold" style="border: 2px solid; border-radius: 12px;">
                <i class="fas fa-sync-alt fa-spin mr-2"></i> DATA FEED ACTIVE
            </span>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4" style="border-left: 6px solid #10b981 !important;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-left: 6px solid #ef4444 !important;">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row">
        {{-- 🚛 STAMPING/PRODUCTION QUEUE --}}
        <div class="col-lg-6 mb-4">
            <div class="d-flex align-items-center mb-4 px-2">
                <div style="width: 5px; height: 25px; background: var(--ind-blue); border-radius: 10px;" class="mr-3"></div>
                <h5 class="font-weight-bold m-0 text-uppercase">Stamping Incoming (OK Goods)</h5>
            </div>

            @forelse($produksiQueue as $p)
            <div class="qc-card mb-4">
                <div class="card-header-ind d-flex justify-content-between align-items-start">
                    <div>
                        <div class="tech-code">BATCH_NO: {{ $p->no_produksi }}</div>
                        <div class="part-title text-primary">{{ $p->material_code }}</div>
                    </div>
                    <div class="text-right">
                        <label class="label-ind">Incoming OK</label>
                        {{-- Ini menampilkan TOTAL OK dari semua line di batch ini --}}
                        <div class="qty-badge text-emerald">{{ number_format($p->qty_hasil_ok) }}</div>
                    </div>
                </div>
                <div class="card-body p-4">
                    {{-- ❌ BOX REJECT BREAKDOWN SUDAH DIHAPUS AGAR QC FOKUS BARANG OK ❌ --}}

                    <form action="{{ route('quality.approve', ['type' => 'stamping', 'id' => $p->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="label-ind text-emerald">Verified OK (To FG)</label>
                                <input type="number" name="qty_ok_final" class="form-control input-ind text-emerald" value="{{ $p->qty_hasil_ok }}" required>
                            </div>
                            <div class="col-6">
                                <label class="label-ind text-rose">New NG found by QC</label>
                                <input type="number" name="qty_ng_final" class="form-control input-ind text-rose" value="0" required>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="label-ind">Authorized Inspector</label>
                            <input type="text" name="inspector_name" class="form-control input-ind" placeholder="Input QC Name..." required>
                        </div>

                        <div class="form-group mb-4">
                            <label class="label-ind">Inspector Remark / Defect Note</label>
                            <textarea name="ng_reason" class="form-control input-ind" rows="2" placeholder="Describe defects if any...">{{ $p->keterangan }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-block btn-action bg-dark text-white shadow-sm mb-3">COMMIT RELEASE TO FG</button>
                    </form>
                    
                    <form action="{{ route('quality.destroy', ['type' => 'stamping', 'id' => $p->id]) }}" method="POST" onsubmit="return confirm('Attention: Permanent deletion of batch. Proceed?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-block bg-white text-muted font-weight-bold border-0" style="font-size: 11px;">PURGE BATCH DATA</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="empty-placeholder">NO_PENDING_STAMPING_DATA</div>
            @endforelse
        </div>

        {{-- 👨‍🏭 WELDING VERIFICATION --}}
        <div class="col-lg-6 mb-4">
            <div class="d-flex align-items-center mb-4 px-2 text-warning">
                <div style="width: 5px; height: 25px; background: var(--ind-amber); border-radius: 10px;" class="mr-3"></div>
                <h5 class="font-weight-bold m-0 text-uppercase">Welding Verification (OK Goods)</h5>
            </div>

            @forelse($weldingQueue as $w)
            <div class="qc-card mb-4" style="border-top: 5px solid var(--ind-amber);">
                <div class="card-header-ind d-flex justify-content-between align-items-start">
                    <div>
                        <div class="tech-code">WLD_BATCH: {{ $w->no_produksi_stamping }}</div>
                        <div class="part-title">{{ $w->part_no }}</div>
                    </div>
                    <div class="text-right">
                        <label class="label-ind">Incoming OK</label>
                        <div class="qty-badge text-warning" id="total-wip-{{ $w->id }}">{{ $w->qty_ok }}</div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('quality.approve', ['type' => 'welding', 'id' => $w->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="label-ind text-emerald">Actual OK (Final)</label>
                                <input type="number" id="ok-{{ $w->id }}" name="qty_ok_final" class="form-control input-ind text-emerald" value="{{ $w->qty_ok }}" oninput="syncWeldingQty('{{ $w->id }}', 'ok')" required>
                            </div>
                            <div class="col-6">
                                <label class="label-ind text-rose">Actual NG (Verify)</label>
                                <input type="number" id="ng-{{ $w->id }}" name="qty_ng_final" class="form-control input-ind text-rose" value="0" oninput="syncWeldingQty('{{ $w->id }}', 'ng')" required>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="label-ind">Authorized Inspector</label>
                            <input type="text" name="inspector_name" class="form-control input-ind" placeholder="Type Name..." required>
                        </div>

                        <div class="form-group mb-4">
                            <label class="label-ind">Analysis Notes</label>
                            <textarea name="ng_reason" class="form-control input-ind" rows="2" placeholder="Record analysis...">{{ $w->keterangan }}</textarea>
                        </div>

                        <button type="submit" id="btn-{{ $w->id }}" class="btn btn-block btn-action shadow-sm" style="background: var(--ind-amber); color: #fff;">
                            VERIFY & STORE TO FG <i class="fas fa-shield-alt ml-2"></i>
                        </button>
                    </form>
                    <form action="{{ route('quality.destroy', ['type' => 'welding', 'id' => $w->id]) }}" method="POST" class="mt-3">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-block bg-white text-muted font-weight-bold border-0" style="font-size: 11px;">PURGE WELDING BATCH</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="empty-placeholder">WELDING_VERIFICATION_CLEAR</div>
            @endforelse
        </div>
    </div>
</div>

<script>
    function syncWeldingQty(id, type) {
        const totalWip = parseInt(document.getElementById('total-wip-' + id).innerText);
        const okInput = document.getElementById('ok-' + id);
        const ngInput = document.getElementById('ng-' + id);
        const btn = document.getElementById('btn-' + id);

        let okVal = parseInt(okInput.value) || 0;
        let ngVal = parseInt(ngInput.value) || 0;

        if (type === 'ok') {
            if (okVal > totalWip) { 
                okInput.value = totalWip; 
                ngInput.value = 0; 
            } else { 
                ngInput.value = totalWip - okVal; 
            }
        } else {
            if (ngVal > totalWip) { 
                ngInput.value = totalWip; 
                okInput.value = 0; 
            } else { 
                okInput.value = totalWip - ngVal; 
            }
        }

        if (parseInt(okInput.value) + parseInt(ngInput.value) > totalWip) {
            btn.disabled = true; btn.innerText = "OVER QUANTITY!";
        } else {
            btn.disabled = false; btn.innerHTML = 'VERIFY & STORE TO FG <i class="fas fa-shield-alt ml-2"></i>';
        }
    }
</script>
@endsection