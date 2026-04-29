@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --ind-navy: #0f172a; --ind-blue: #4361ee; --ind-amber: #f59e0b;
        --ind-emerald: #10b981; --ind-rose: #ef4444; --ind-slate: #f8fafc;
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: var(--ind-navy); }
    
    /* 🛸 INDUSTRIAL UI ELEMENTS */
    .heading-cyber { font-family: 'Orbitron', sans-serif; font-weight: 800; letter-spacing: -1px; text-transform: uppercase; }
    
    .qc-card { 
        background: #ffffff; border: none; border-radius: 24px; 
        box-shadow: 0 10px 40px rgba(0,0,0,0.04); transition: 0.3s; 
        overflow: hidden; border: 1px solid rgba(255,255,255,0.8);
    }
    .qc-card:hover { transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,0.08); }

    .card-header-ind { padding: 1.5rem; border-bottom: 1px solid #f1f5f9; position: relative; }
    .stamping-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--ind-blue); }
    .welding-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--ind-amber); }

    .tech-code { font-family: 'JetBrains Mono'; font-size: 0.7rem; color: #94a3b8; font-weight: 700; letter-spacing: 1px; }
    .part-title { font-weight: 900; font-size: 1.3rem; letter-spacing: -0.5px; }
    .qty-badge { font-family: 'Orbitron'; font-size: 2.2rem; font-weight: 900; line-height: 1; }

    /* ⌨️ INPUT STYLING */
    .label-ind { font-size: 10px; font-weight: 800; text-transform: uppercase; color: #64748b; margin-bottom: 8px; display: block; letter-spacing: 1px; }
    .input-ind { 
        background: #f8fafc; border: 2px solid #edf2f7; border-radius: 14px; 
        font-weight: 700; padding: 12px 16px; transition: 0.3s; width: 100%;
    }
    .input-ind:focus { border-color: var(--ind-blue); background: #fff; outline: none; box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1); }
    
    .input-qty-hero { font-size: 24px; height: 70px; text-align: center; font-family: 'Orbitron'; }

    .btn-action { 
        font-family: 'Orbitron'; font-size: 0.85rem; padding: 18px; border-radius: 14px; 
        text-transform: uppercase; font-weight: 800; border: none; letter-spacing: 1.5px;
        transition: 0.3s;
    }
    .btn-stamping { background: var(--ind-navy); color: white; }
    .btn-welding { background: var(--ind-amber); color: var(--ind-navy); }
    .btn-action:hover { filter: brightness(1.2); transform: scale(1.02); }

    .empty-placeholder { 
        border: 3px dashed #cbd5e1; border-radius: 24px; padding: 5rem; 
        color: #94a3b8; font-weight: 800; text-align: center; text-transform: uppercase;
        font-family: 'Orbitron'; letter-spacing: 2px;
    }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    {{-- 🛰️ TOP COMMAND BAR --}}
    <div class="d-flex align-items-center justify-content-between mb-5 bg-white p-4 rounded-3xl border shadow-sm">
        <div>
            <h2 class="heading-cyber m-0">GATE_VERIFICATION <span class="text-primary">v4.0</span></h2>
            <p class="text-muted small font-weight-bold mb-0 text-uppercase"><i class="fas fa-shield-alt text-primary mr-2"></i> Quality Assurance Operational Mode</p>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('quality.history') }}" class="btn btn-dark rounded-pill px-4 font-weight-bold mr-3 shadow-sm">
                <i class="fas fa-file-invoice mr-2"></i> AUDIT_LOG
            </a>
            <span class="badge bg-primary-soft text-primary px-3 py-2 font-weight-black animate__animated animate__pulse animate__infinite" style="border-radius: 12px; font-size: 11px;">
                <i class="fas fa-satellite-dish mr-2"></i> LIVE_FEED
            </span>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-lg mb-4 animate__animated animate__fadeInDown" style="border-radius: 16px; border-left: 8px solid var(--ind-emerald) !important;">
            <b class="uppercase">SYSTEM_OK:</b> {{ session('success') }}
        </div>
    @endif

    <div class="row">
        {{-- 🚛 CHAMBER 01: STAMPING --}}
        <div class="col-lg-6 mb-4">
            <div class="d-flex align-items-center mb-4 px-3">
                <i class="fas fa-industry text-primary mr-3 fa-lg"></i>
                <h5 class="font-weight-black m-0 text-uppercase tracking-tighter">Stamping Queue</h5>
            </div>

            @forelse($produksiQueue as $p)
            <div class="qc-card mb-4 animate__animated animate__fadeInLeft">
                <div class="stamping-strip"></div>
                <div class="card-header-ind d-flex justify-content-between">
                    <div>
                        <div class="tech-code">ID: {{ $p->no_produksi }}</div>
                        <div class="part-title text-primary">{{ $p->material_code }}</div>
                    </div>
                    <div class="text-right">
                        <label class="label-ind">Incoming</label>
                        <div class="qty-badge text-primary">{{ number_format($p->qty_hasil_ok) }}</div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('quality.approve', ['type' => 'stamping', 'id' => $p->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="label-ind text-emerald">Verify OK</label>
                                <input type="number" name="qty_ok_final" class="form-control input-ind input-qty-hero text-emerald" value="{{ $p->qty_hasil_ok }}" required>
                            </div>
                            <div class="col-6">
                                <label class="label-ind text-rose">Found NG</label>
                                <input type="number" name="qty_ng_final" class="form-control input-ind input-qty-hero text-rose" value="0" required>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="label-ind">Defect Category (Stamping Room)</label>
                            <select name="ng_reason" class="form-control input-ind" style="height: 55px;">
                                <option value="">-- NO DEFECT DETECTED --</option>
                                @foreach($ngStamping as $ng)
                                    <option value="{{ $ng->ng_name }}">{{ strtoupper($ng->ng_name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label class="label-ind">Authorized Inspector</label>
                            <input type="text" name="inspector_name" class="form-control input-ind" placeholder="Sign ID..." required>
                        </div>

                        <button type="submit" class="btn btn-block btn-action btn-stamping shadow-lg mb-3">
                            AUTHORIZE RELEASE <i class="fas fa-check-double ml-2"></i>
                        </button>
                    </form>
                    
                    <form action="{{ route('quality.destroy', ['type' => 'stamping', 'id' => $p->id]) }}" method="POST" onsubmit="return confirm('WARNING: PURGE DATA?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-block text-muted font-weight-black border-0 bg-transparent" style="font-size: 10px; letter-spacing: 1px;">TERMINATE_BATCH_DATA</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="empty-placeholder">STAMPING_CLEAR</div>
            @endforelse
        </div>

        {{-- 👨‍🏭 CHAMBER 02: WELDING --}}
        <div class="col-lg-6 mb-4">
            <div class="d-flex align-items-center mb-4 px-3">
                <i class="fas fa-fire text-warning mr-3 fa-lg"></i>
                <h5 class="font-weight-black m-0 text-uppercase tracking-tighter">Welding Queue</h5>
            </div>

            @forelse($weldingQueue as $w)
            <div class="qc-card mb-4 animate__animated animate__fadeInRight">
                <div class="welding-strip"></div>
                <div class="card-header-ind d-flex justify-content-between">
                    <div>
                        <div class="tech-code">BATCH: {{ $w->no_produksi_stamping }}</div>
                        <div class="part-title text-warning">{{ $w->part_no }}</div>
                    </div>
                    <div class="text-right">
                        <label class="label-ind">Incoming</label>
                        <div class="qty-badge text-warning" id="total-wip-{{ $w->id }}">{{ $w->qty_ok }}</div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('quality.approve', ['type' => 'welding', 'id' => $w->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="label-ind text-emerald">Verify OK</label>
                                <input type="number" id="ok-{{ $w->id }}" name="qty_ok_final" class="form-control input-ind input-qty-hero text-emerald" value="{{ $w->qty_ok }}" oninput="syncWeldingQty('{{ $w->id }}', 'ok')" required>
                            </div>
                            <div class="col-6">
                                <label class="label-ind text-rose">Found NG</label>
                                <input type="number" id="ng-{{ $w->id }}" name="qty_ng_final" class="form-control input-ind input-qty-hero text-rose" value="0" oninput="syncWeldingQty('{{ $w->id }}', 'ng')" required>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="label-ind text-warning">Defect Category (Welding Room)</label>
                            <select name="ng_reason" class="form-control input-ind" style="height: 55px;">
                                <option value="">-- NO DEFECT DETECTED --</option>
                                @foreach($ngWelding as $ng)
                                    <option value="{{ $ng->ng_name }}">{{ strtoupper($ng->ng_name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label class="label-ind">Authorized Inspector</label>
                            <input type="text" name="inspector_name" class="form-control input-ind" placeholder="Sign ID..." required>
                        </div>

                        <button type="submit" id="btn-{{ $w->id }}" class="btn btn-block btn-action btn-welding shadow-lg mb-3">
                            VERIFY & STORE <i class="fas fa-database ml-2"></i>
                        </button>
                    </form>
                    <form action="{{ route('quality.destroy', ['type' => 'welding', 'id' => $w->id]) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-block text-muted font-weight-black border-0 bg-transparent" style="font-size: 10px; letter-spacing: 1px;">TERMINATE_BATCH_DATA</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="empty-placeholder">WELDING_CLEAR</div>
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
            if (okVal > totalWip) { okInput.value = totalWip; ngInput.value = 0; } 
            else { ngInput.value = totalWip - okVal; }
        } else {
            if (ngVal > totalWip) { ngInput.value = totalWip; okInput.value = 0; } 
            else { okInput.value = totalWip - ngVal; }
        }

        if (parseInt(okInput.value) + parseInt(ngInput.value) > totalWip) {
            btn.disabled = true; btn.innerText = "OVERFLOW!";
        } else {
            btn.disabled = false; btn.innerHTML = 'VERIFY & STORE <i class="fas fa-database ml-2"></i>';
        }
    }
</script>
@endsection