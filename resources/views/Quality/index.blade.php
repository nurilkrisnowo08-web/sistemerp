@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --ind-navy: #0f172a; --ind-blue: #4361ee; --ind-amber: #f59e0b;
        --ind-emerald: #10b981; --ind-rose: #ef4444; --ind-slate: #f8fafc;
    }
    body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; }
    .heading-cyber { font-family: 'Orbitron'; font-weight: 800; text-transform: uppercase; }
    .qc-card { background: #fff; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); overflow: hidden; transition: 0.3s; border: 1px solid #eef2f6; position: relative; }
    .card-header-ind { padding: 1.5rem; border-bottom: 1px solid #f1f5f9; }
    .stamping-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--ind-blue); }
    .welding-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--ind-amber); }
    .qty-badge { font-family: 'Orbitron'; font-size: 2.2rem; font-weight: 900; }
    
    .ng-breakdown-box { background: #fff1f2; border-radius: 18px; padding: 15px; border: 1px solid #fee2e2; }
    .ng-item { background: white; border-radius: 12px; padding: 10px 15px; margin-bottom: 8px; border: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
    .ng-input-sm { width: 80px; text-align: center; font-weight: 800; border-radius: 10px; border: 2px solid #e2e8f0; transition: 0.3s; }
    .ng-input-sm:focus { border-color: var(--ind-rose); outline: none; background: #fff1f2; }
    
    .input-ind { background: #f8fafc; border: 2px solid #edf2f7; border-radius: 14px; font-weight: 700; padding: 12px 16px; width: 100%; }
    .input-qty-hero { font-size: 24px; height: 60px; text-align: center; font-family: 'Orbitron'; border: 2px solid #e2e8f0; }
    .btn-action { font-family: 'Orbitron'; font-size: 0.85rem; padding: 18px; border-radius: 14px; text-transform: uppercase; font-weight: 800; border: none; transition: 0.3s; }
    .empty-placeholder { text-align: center; padding: 60px; color: #94a3b8; font-family: 'JetBrains Mono'; font-weight: 700; border: 2px dashed #cbd5e1; border-radius: 24px; background: #fff; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    <div class="d-flex align-items-center justify-content-between mb-5 bg-white p-4 rounded-3xl border shadow-sm">
        <div>
            <h2 class="heading-cyber m-0 text-primary">QC_GATE <span class="text-dark">v4.5</span></h2>
            <p class="text-muted small font-weight-bold mb-0 uppercase">Verified Verification Terminal</p>
        </div>
        <a href="{{ route('quality.history') }}" class="btn btn-dark rounded-pill px-4 font-weight-bold shadow-sm">VIEW_HISTORY</a>
    </div>

    <div class="row">
        {{-- 🚛 STAMPING CHAMBER --}}
        <div class="col-lg-6 mb-4">
            <h5 class="font-weight-black mb-4 px-3 uppercase tracking-tighter"><i class="fas fa-microchip mr-2 text-primary"></i> Stamping Queue</h5>
            @forelse($produksiQueue as $p)
            <div class="qc-card mb-4 animate__animated animate__fadeInLeft">
                <div class="stamping-strip"></div>
                <div class="card-header-ind d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small font-weight-bold text-muted uppercase">BATCH: {{ $p->no_produksi }}</div>
                        <div class="h5 font-weight-black text-primary mb-0">{{ $p->material_code }}</div>
                    </div>
                    <div class="text-right">
                        <div class="qty-badge text-primary" id="incoming-{{ $p->id }}">{{ $p->qty_hasil_ok }}</div>
                        <label class="small font-weight-black text-muted uppercase mb-0">Inbound Qty</label>
                    </div>
                </div>

                <div class="card-body p-4">
                    {{-- ✨ FIX: Memastikan route mengirim type 'stamping' dan ID rill --}}
                    <form action="{{ route('quality.approve', ['type' => 'stamping', 'id' => $p->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="label-ind text-emerald small font-weight-black uppercase">Verified OK</label>
                                <input type="number" name="qty_ok_final" id="ok-stamping-{{ $p->id }}" class="form-control input-ind input-qty-hero text-emerald" value="{{ $p->qty_hasil_ok }}" readonly>
                            </div>
                            <div class="col-6">
                                <label class="label-ind text-rose small font-weight-black uppercase">Final NG</label>
                                <input type="number" name="qty_ng_final" id="total-ng-stamping-{{ $p->id }}" class="form-control input-ind input-qty-hero text-rose" value="0" readonly>
                            </div>
                        </div>

                        <div class="ng-breakdown-box mb-4">
                            <label class="label-ind text-danger font-weight-bold mb-2 d-block">Breakdown NG (Auto-Calculate):</label>
                            <div style="max-height: 250px; overflow-y: auto; padding-right: 5px;">
                                @foreach($ngStamping as $ng)
                                <div class="ng-item">
                                    <span class="small font-weight-bold text-dark">{{ strtoupper($ng->ng_name) }}</span>
                                    {{-- ✨ FIX: Name input harus ng_details[NAMA] agar Controller bisa meloop --}}
                                    <input type="number" name="ng_details[{{ $ng->ng_name }}]" 
                                           class="ng-input-sm ng-val-{{ $p->id }}" 
                                           value="0" min="0" oninput="calculateNG('{{ $p->id }}')">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="label-ind small font-weight-black uppercase">Authorized Inspector</label>
                            <input type="text" name="inspector_name" class="form-control input-ind" value="{{ Auth::user()->name }}" readonly>
                        </div>

                        <button type="submit" class="btn btn-block btn-action bg-dark text-white shadow-lg">COMMIT & RELEASE <i class="fas fa-check-circle ml-2"></i></button>
                    </form>
                </div>
            </div>
            @empty
            <div class="empty-placeholder">STAMPING_QUEUE_CLEAR</div>
            @endforelse
        </div>

        {{-- 👨‍🏭 WELDING CHAMBER --}}
        <div class="col-lg-6 mb-4">
            <h5 class="font-weight-black mb-4 px-3 uppercase tracking-tighter text-warning"><i class="fas fa-fire mr-2"></i> Welding Queue</h5>
            @forelse($weldingQueue as $w)
            <div class="qc-card mb-4 animate__animated animate__fadeInRight">
                <div class="welding-strip"></div>
                <div class="card-header-ind d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small font-weight-bold text-muted text-warning uppercase">WLD_ID: {{ $w->no_produksi_stamping }}</div>
                        <div class="h5 font-weight-black text-warning mb-0">{{ $w->part_no }}</div>
                    </div>
                    <div class="text-right">
                        <div class="qty-badge text-warning" id="incoming-w-{{ $w->id }}">{{ $w->qty_ok }}</div>
                        <label class="small font-weight-black text-muted uppercase mb-0">Inbound Qty</label>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('quality.approve', ['type' => 'welding', 'id' => $w->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="label-ind text-emerald small font-weight-black uppercase">Verified OK</label>
                                <input type="number" name="qty_ok_final" id="ok-welding-{{ $w->id }}" class="form-control input-ind input-qty-hero text-emerald" value="{{ $w->qty_ok }}" readonly>
                            </div>
                            <div class="col-6">
                                <label class="label-ind text-rose small font-weight-black uppercase">Final NG</label>
                                <input type="number" name="qty_ng_final" id="total-ng-welding-{{ $w->id }}" class="form-control input-ind input-qty-hero text-rose" value="0" readonly>
                            </div>
                        </div>

                        <div class="ng-breakdown-box mb-4" style="background: #fffbeb; border-color: #fef3c7;">
                            <label class="label-ind text-warning font-weight-bold mb-2 d-block">Breakdown NG (Welding):</label>
                            <div style="max-height: 250px; overflow-y: auto; padding-right: 5px;">
                                @foreach($ngWelding as $ng)
                                <div class="ng-item shadow-sm">
                                    <span class="small font-weight-bold text-dark">{{ strtoupper($ng->ng_name) }}</span>
                                    <input type="number" name="ng_details[{{ $ng->ng_name }}]" 
                                           class="ng-input-sm ng-val-w-{{ $w->id }}" 
                                           value="0" min="0" oninput="calculateNGWelding('{{ $w->id }}')">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="label-ind small font-weight-black uppercase">Authorized Inspector</label>
                            <input type="text" name="inspector_name" class="form-control input-ind" value="{{ Auth::user()->name }}" readonly>
                        </div>

                        <button type="submit" class="btn btn-block btn-action bg-warning text-dark shadow-lg">COMMIT & STORE FG <i class="fas fa-database ml-2"></i></button>
                    </form>
                </div>
            </div>
            @empty
            <div class="empty-placeholder">WELDING_QUEUE_CLEAR</div>
            @endforelse
        </div>
    </div>
</div>

<script>
    function calculateNG(id) {
        let incoming = parseInt(document.getElementById('incoming-' + id).innerText);
        let totalNG = 0;

        // Ambil semua input dalam card ini saja rill
        document.querySelectorAll('.ng-val-' + id).forEach(input => {
            totalNG += (parseInt(input.value) || 0);
        });

        if (totalNG > incoming) {
            alert("🚨 GAGAL: Total NG ("+totalNG+") tidak boleh melebihi Inbound ("+incoming+")!");
            // Cari input yang baru saja diubah dan nol kan rill
            event.target.value = 0;
            return calculateNG(id);
        }

        document.getElementById('total-ng-stamping-' + id).value = totalNG;
        document.getElementById('ok-stamping-' + id).value = incoming - totalNG;
    }

    function calculateNGWelding(id) {
        let incoming = parseInt(document.getElementById('incoming-w-' + id).innerText);
        let totalNG = 0;

        document.querySelectorAll('.ng-val-w-' + id).forEach(input => {
            totalNG += (parseInt(input.value) || 0);
        });

        if (totalNG > incoming) {
            alert("🚨 GAGAL: Total NG ("+totalNG+") tidak boleh melebihi Inbound ("+incoming+")!");
            event.target.value = 0;
            return calculateNGWelding(id);
        }

        document.getElementById('total-ng-welding-' + id).value = totalNG;
        document.getElementById('ok-welding-' + id).value = incoming - totalNG;
    }
</script>
@endsection@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --ind-navy: #0f172a; --ind-blue: #4361ee; --ind-amber: #f59e0b;
        --ind-emerald: #10b981; --ind-rose: #ef4444; --ind-slate: #f8fafc;
    }
    body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; }
    .heading-cyber { font-family: 'Orbitron'; font-weight: 800; text-transform: uppercase; letter-spacing: -1px; }
    
    /* QC Card Enhancements */
    .qc-card { background: #fff; border-radius: 28px; box-shadow: 0 15px 50px rgba(0,0,0,0.05); overflow: hidden; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid #eef2f6; position: relative; }
    .qc-card:hover { transform: translateY(-5px); box-shadow: 0 20px 60px rgba(67, 97, 238, 0.1); }
    
    .card-header-ind { padding: 2rem; border-bottom: 1px solid #f1f5f9; }
    .stamping-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 8px; background: linear-gradient(to bottom, var(--ind-blue), #3a0ca3); }
    .welding-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 8px; background: linear-gradient(to bottom, var(--ind-amber), #cc8b00); }
    
    .qty-badge { font-family: 'Orbitron'; font-size: 2.8rem; font-weight: 900; line-height: 1; }
    
    /* NG Box Styling */
    .ng-breakdown-box { background: #fff1f2; border-radius: 22px; padding: 20px; border: 1px solid #fee2e2; }
    .ng-item { background: white; border-radius: 14px; padding: 12px 18px; margin-bottom: 10px; border: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
    .ng-input-sm { width: 90px; text-align: center; font-weight: 800; border-radius: 12px; border: 2px solid #e2e8f0; padding: 8px; transition: 0.3s; font-family: 'JetBrains Mono'; }
    .ng-input-sm:focus { border-color: var(--ind-rose); outline: none; background: #fff1f2; transform: scale(1.05); }
    
    .input-qty-hero { font-size: 32px; height: 75px; text-align: center; font-family: 'Orbitron'; border: 2px solid #e2e8f0; border-radius: 18px; font-weight: 900; }
    .input-ind { background: #f8fafc; border: 2px solid #edf2f7; border-radius: 16px; font-weight: 700; padding: 15px; }

    /* Button "Anti-Cuki" */
    .btn-commit { font-family: 'Orbitron'; font-size: 0.9rem; padding: 22px; border-radius: 18px; text-transform: uppercase; font-weight: 800; border: none; transition: 0.3s; width: 100%; position: relative; overflow: hidden; }
    .btn-commit:active { transform: scale(0.95); }
    .btn-commit.processing { pointer-events: none; opacity: 0.7; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    {{-- Main Header --}}
    <div class="d-flex align-items-center justify-content-between mb-5 bg-white p-4 rounded-3xl border shadow-sm">
        <div>
            <h2 class="heading-cyber m-0 text-primary">QC_TERMINAL <span class="text-dark">v5.0</span></h2>
            <p class="text-muted small font-weight-bold mb-0 uppercase"><i class="fas fa-shield-alt mr-2 text-success"></i>High-Precision Quality Gate</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('quality.history') }}" class="btn btn-dark rounded-pill px-4 font-weight-bold shadow-sm mr-2">AUDIT_LOG</a>
        </div>
    </div>

    <div class="row">
        {{-- 🚛 STAMPING SECTION --}}
        <div class="col-lg-6 mb-4">
            <div class="px-3 mb-4 d-flex justify-content-between align-items-center">
                <h5 class="font-weight-black uppercase m-0"><i class="fas fa-microchip mr-2 text-primary"></i> Stamping Inbound</h5>
                <span class="badge badge-primary badge-pill px-3">{{ $produksiQueue->count() }} BATCHES</span>
            </div>

            @forelse($produksiQueue as $p)
            <div class="qc-card mb-5 animate__animated animate__fadeInLeft">
                <div class="stamping-strip"></div>
                <div class="card-header-ind d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small font-weight-bold text-muted uppercase tracking-widest">Batch Identifier</div>
                        <div class="h4 font-weight-black text-primary mb-1" style="font-family: 'Orbitron';">{{ $p->no_produksi }}</div>
                        <span class="badge badge-light border font-weight-bold text-dark">{{ $p->material_code }}</span>
                    </div>
                    <div class="text-right">
                        <div class="qty-badge text-primary" id="incoming-{{ $p->id }}">{{ $p->qty_hasil_ok }}</div>
                        <label class="small font-weight-black text-muted uppercase mb-0 tracking-tighter">Production Claim</label>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('quality.approve', ['type' => 'stamping', 'id' => $p->id]) }}" method="POST" onsubmit="return startProcessing(this)">
                        @csrf
                        <div class="row mb-5">
                            <div class="col-6">
                                <label class="small font-weight-black text-success uppercase mb-2 ml-1">Verified OK</label>
                                <input type="number" name="qty_ok_final" id="ok-stamping-{{ $p->id }}" class="form-control input-qty-hero text-emerald" value="{{ $p->qty_hasil_ok }}" readonly>
                            </div>
                            <div class="col-6">
                                <label class="small font-weight-black text-danger uppercase mb-2 ml-1">Verified NG</label>
                                <input type="number" name="qty_ng_final" id="total-ng-stamping-{{ $p->id }}" class="form-control input-qty-hero text-rose" value="0" readonly>
                            </div>
                        </div>

                        <div class="ng-breakdown-box mb-5 shadow-inner">
                            <label class="font-weight-black text-danger small uppercase mb-3 d-block"><i class="fas fa-search mr-2"></i>Defect Analysis Breakdown:</label>
                            <div style="max-height: 280px; overflow-y: auto; padding-right: 10px;">
                                @foreach($ngStamping as $ng)
                                <div class="ng-item">
                                    <span class="font-weight-bold text-dark small">{{ strtoupper($ng->ng_name) }}</span>
                                    <input type="number" name="ng_details[{{ $ng->ng_name }}]" 
                                           class="ng-input-sm ng-val-{{ $p->id }}" 
                                           value="0" min="0" oninput="calculateNG('{{ $p->id }}')">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group mb-5">
                            <label class="small font-weight-black text-muted uppercase mb-2 ml-1">Authorized QC Inspector</label>
                            <input type="text" name="inspector_name" class="form-control input-ind" value="{{ Auth::user()->name }}" readonly>
                        </div>

                        <button type="submit" class="btn-commit bg-dark text-white shadow-lg">
                            <span class="btn-text">AUTHORIZE & COMMIT BATCH <i class="fas fa-check-double ml-2"></i></span>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="empty-placeholder">
                <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                <h5>STAMPING_QUEUE_CLEAR</h5>
                <p class="small mb-0">All batches have been verified rill.</p>
            </div>
            @endforelse
        </div>

        {{-- 👨‍🏭 WELDING SECTION --}}
        <div class="col-lg-6 mb-4">
             <div class="px-3 mb-4 d-flex justify-content-between align-items-center">
                <h5 class="font-weight-black uppercase m-0 text-warning"><i class="fas fa-fire mr-2"></i> Welding Inbound</h5>
                <span class="badge badge-warning badge-pill px-3">{{ $weldingQueue->count() }} BATCHES</span>
            </div>

            @forelse($weldingQueue as $w)
            <div class="qc-card mb-5 animate__animated animate__fadeInRight">
                <div class="welding-strip"></div>
                <div class="card-header-ind d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small font-weight-bold text-muted uppercase tracking-widest text-warning">Welding Job ID</div>
                        <div class="h4 font-weight-black text-warning mb-1" style="font-family: 'Orbitron';">{{ $w->no_produksi_stamping }}</div>
                        <span class="badge badge-dark font-weight-bold">{{ $w->part_no }}</span>
                    </div>
                    <div class="text-right text-warning">
                        <div class="qty-badge text-warning" id="incoming-w-{{ $w->id }}">{{ $w->qty_ok }}</div>
                        <label class="small font-weight-black uppercase mb-0 tracking-tighter">Inbound Qty</label>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('quality.approve', ['type' => 'welding', 'id' => $w->id]) }}" method="POST" onsubmit="return startProcessing(this)">
                        @csrf
                        <div class="row mb-5">
                            <div class="col-6">
                                <label class="small font-weight-black text-success uppercase mb-2 ml-1">Verified OK</label>
                                <input type="number" name="qty_ok_final" id="ok-welding-{{ $w->id }}" class="form-control input-qty-hero text-emerald" value="{{ $w->qty_ok }}" readonly>
                            </div>
                            <div class="col-6">
                                <label class="small font-weight-black text-danger uppercase mb-2 ml-1">Verified NG</label>
                                <input type="number" name="qty_ng_final" id="total-ng-welding-{{ $w->id }}" class="form-control input-qty-hero text-rose" value="0" readonly>
                            </div>
                        </div>

                        <div class="ng-breakdown-box mb-5 shadow-inner" style="background: #fffbeb; border-color: #fef3c7;">
                            <label class="font-weight-black text-warning small uppercase mb-3 d-block"><i class="fas fa-tools mr-2"></i>Welding Defect List:</label>
                            <div style="max-height: 280px; overflow-y: auto; padding-right: 10px;">
                                @foreach($ngWelding as $ng)
                                <div class="ng-item shadow-sm">
                                    <span class="font-weight-bold text-dark small">{{ strtoupper($ng->ng_name) }}</span>
                                    <input type="number" name="ng_details[{{ $ng->ng_name }}]" 
                                           class="ng-input-sm ng-val-w-{{ $w->id }}" 
                                           value="0" min="0" oninput="calculateNGWelding('{{ $w->id }}')">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group mb-5">
                            <label class="small font-weight-black text-muted uppercase mb-2 ml-1">Authorized QC Inspector</label>
                            <input type="text" name="inspector_name" class="form-control input-ind" value="{{ Auth::user()->name }}" readonly>
                        </div>

                        <button type="submit" class="btn-commit bg-warning text-dark shadow-lg">
                            <span class="btn-text">VERIFY & STORE FG <i class="fas fa-database ml-2"></i></span>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="empty-placeholder">
                <i class="fas fa-check-double text-warning fa-3x mb-3"></i>
                <h5>WELDING_QUEUE_CLEAR</h5>
                <p class="small mb-0">No pending welding jobs rill.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    // Visual Feedback saat klik Commit rill
    function startProcessing(form) {
        const btn = form.querySelector('.btn-commit');
        const text = btn.querySelector('.btn-text');
        btn.classList.add('processing');
        text.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> PROCESSING...';
        return true;
    }

    function calculateNG(id) {
        let incoming = parseInt(document.getElementById('incoming-' + id).innerText);
        let totalNG = 0;

        document.querySelectorAll('.ng-val-' + id).forEach(input => {
            totalNG += (parseInt(input.value) || 0);
        });

        if (totalNG > incoming) {
            alert("🚨 ERROR: Total NG ("+totalNG+") tidak boleh melebihi barang masuk ("+incoming+") rill!");
            event.target.value = 0;
            return calculateNG(id);
        }

        document.getElementById('total-ng-stamping-' + id).value = totalNG;
        document.getElementById('ok-stamping-' + id).value = incoming - totalNG;
    }

    function calculateNGWelding(id) {
        let incoming = parseInt(document.getElementById('incoming-w-' + id).innerText);
        let totalNG = 0;

        document.querySelectorAll('.ng-val-w-' + id).forEach(input => {
            totalNG += (parseInt(input.value) || 0);
        });

        if (totalNG > incoming) {
            alert("🚨 ERROR: Total NG ("+totalNG+") tidak boleh melebihi barang masuk ("+incoming+") rill!");
            event.target.value = 0;
            return calculateNGWelding(id);
        }

        document.getElementById('total-ng-welding-' + id).value = totalNG;
        document.getElementById('ok-welding-' + id).value = incoming - totalNG;
    }
</script>
@endsection