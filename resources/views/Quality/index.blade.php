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
    .qc-card { background: #fff; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); overflow: hidden; transition: 0.3s; }
    .card-header-ind { padding: 1.5rem; border-bottom: 1px solid #f1f5f9; position: relative; }
    .stamping-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--ind-blue); }
    .welding-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--ind-amber); }
    .qty-badge { font-family: 'Orbitron'; font-size: 2.2rem; font-weight: 900; }
    
    /* NG List Style */
    .ng-breakdown-box { background: #fff1f2; border-radius: 18px; padding: 15px; border: 1px solid #fee2e2; }
    .ng-item { background: white; border-radius: 12px; padding: 8px 12px; margin-bottom: 6px; border: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
    .ng-input-sm { width: 70px; text-align: center; font-weight: 800; border-radius: 8px; border: 2px solid #e2e8f0; }
    .ng-input-sm:focus { border-color: var(--ind-rose); outline: none; }
    
    .input-ind { background: #f8fafc; border: 2px solid #edf2f7; border-radius: 14px; font-weight: 700; padding: 12px 16px; width: 100%; }
    .input-qty-hero { font-size: 24px; height: 60px; text-align: center; font-family: 'Orbitron'; }
    .btn-action { font-family: 'Orbitron'; font-size: 0.85rem; padding: 18px; border-radius: 14px; text-transform: uppercase; font-weight: 800; border: none; transition: 0.3s; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    {{-- Header Section Tetap Sama --}}
    <div class="d-flex align-items-center justify-content-between mb-5 bg-white p-4 rounded-3xl border shadow-sm">
        <div>
            <h2 class="heading-cyber m-0 text-primary">QC_GATE <span class="text-dark">v4.5</span></h2>
            <p class="text-muted small font-weight-bold mb-0">MULTIPLE_DEFECT_ANALYSIS_MODE</p>
        </div>
        <a href="{{ route('quality.history') }}" class="btn btn-dark rounded-pill px-4 font-weight-bold shadow-sm">AUDIT_LOG</a>
    </div>

    <div class="row">
        {{-- 🚛 STAMPING CHAMBER --}}
        <div class="col-lg-6 mb-4">
            <h5 class="font-weight-black mb-4 px-3 uppercase tracking-tighter"><i class="fas fa-microchip mr-2 text-primary"></i> Stamping Incoming</h5>
            @forelse($produksiQueue as $p)
            <div class="qc-card mb-4 animate__animated animate__fadeInLeft">
                <div class="stamping-strip"></div>
                <div class="card-header-ind d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small font-weight-bold text-muted">BATCH: {{ $p->no_produksi }}</div>
                        <div class="h5 font-weight-black text-primary mb-0">{{ $p->material_code }}</div>
                    </div>
                    <div class="text-right">
                        <div class="qty-badge text-primary" id="incoming-{{ $p->id }}">{{ $p->qty_hasil_ok }}</div>
                        <label class="small font-weight-black text-muted uppercase mb-0">Total Incoming</label>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('quality.approve', ['type' => 'stamping', 'id' => $p->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="label-ind text-emerald small font-weight-black uppercase">Verified OK</label>
                                <input type="number" name="qty_ok_final" id="ok-stamping-{{ $p->id }}" class="form-control input-ind input-qty-hero text-emerald" value="{{ $p->qty_hasil_ok }}" readonly>
                            </div>
                            <div class="col-6">
                                <label class="label-ind text-rose small font-weight-black uppercase">Total NG Found</label>
                                <input type="number" name="qty_ng_final" id="total-ng-stamping-{{ $p->id }}" class="form-control input-ind input-qty-hero text-rose" value="0" readonly>
                            </div>
                        </div>

                        {{-- ✨ NG BREAKDOWN LIST --}}
                        <div class="ng-breakdown-box mb-4">
                            <label class="label-ind text-danger">Defect Breakdown List:</label>
                            <div style="max-height: 200px; overflow-y: auto;">
                                @foreach($ngStamping as $ng)
                                <div class="ng-item">
                                    <span class="small font-weight-bold text-dark">{{ strtoupper($ng->ng_name) }}</span>
                                    <input type="number" name="ng_details[{{ $ng->ng_name }}]" 
                                           class="ng-input-sm ng-val-{{ $p->id }}" 
                                           data-parent="{{ $p->id }}" data-type="stamping"
                                           value="0" min="0" oninput="calculateNG('{{ $p->id }}')">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="label-ind small font-weight-black">Authorized Inspector</label>
                            <input type="text" name="inspector_name" class="form-control input-ind" placeholder="QC Sign..." required>
                        </div>

                        <button type="submit" class="btn btn-block btn-action bg-dark text-white shadow-lg">AUTHORIZE & RELEASE <i class="fas fa-check-double ml-2"></i></button>
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
                        <div class="small font-weight-bold text-muted text-warning">WLD_ID: {{ $w->no_produksi_stamping }}</div>
                        <div class="h5 font-weight-black text-warning mb-0">{{ $w->part_no }}</div>
                    </div>
                    <div class="text-right">
                        <div class="qty-badge text-warning" id="incoming-w-{{ $w->id }}">{{ $w->qty_ok }}</div>
                        <label class="small font-weight-black text-muted uppercase mb-0">Total Incoming</label>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('quality.approve', ['type' => 'welding', 'id' => $w->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="label-ind text-emerald small font-weight-black uppercase">Final OK</label>
                                <input type="number" name="qty_ok_final" id="ok-welding-{{ $w->id }}" class="form-control input-ind input-qty-hero text-emerald" value="{{ $w->qty_ok }}" readonly>
                            </div>
                            <div class="col-6">
                                <label class="label-ind text-rose small font-weight-black uppercase">Verify NG</label>
                                <input type="number" name="qty_ng_final" id="total-ng-welding-{{ $w->id }}" class="form-control input-ind input-qty-hero text-rose" value="0" readonly>
                            </div>
                        </div>

                        <div class="ng-breakdown-box mb-4" style="background: #fffbeb; border-color: #fef3c7;">
                            <label class="label-ind text-warning">Welding Defect List:</label>
                            <div style="max-height: 200px; overflow-y: auto;">
                                @foreach($ngWelding as $ng)
                                <div class="ng-item">
                                    <span class="small font-weight-bold text-dark">{{ strtoupper($ng->ng_name) }}</span>
                                    <input type="number" name="ng_details[{{ $ng->ng_name }}]" 
                                           class="ng-input-sm ng-val-w-{{ $w->id }}" 
                                           data-parent="{{ $w->id }}" data-type="welding"
                                           value="0" min="0" oninput="calculateNGWelding('{{ $w->id }}')">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="label-ind small font-weight-black">Authorized Inspector</label>
                            <input type="text" name="inspector_name" class="form-control input-ind" placeholder="QC Sign..." required>
                        </div>

                        <button type="submit" class="btn btn-block btn-action btn-welding shadow-lg">VERIFY & STORE <i class="fas fa-database ml-2"></i></button>
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
    // Perhitungan Otomatis STAMPING
    function calculateNG(id) {
        let incoming = parseInt(document.getElementById('incoming-' + id).innerText);
        let totalNG = 0;
        document.querySelectorAll('.ng-val-' + id).forEach(input => {
            totalNG += (parseInt(input.value) || 0);
        });

        if (totalNG > incoming) {
            alert("NG melebihi total barang datang rill!");
            event.target.value = 0;
            return calculateNG(id);
        }

        document.getElementById('total-ng-stamping-' + id).value = totalNG;
        document.getElementById('ok-stamping-' + id).value = incoming - totalNG;
    }

    // Perhitungan Otomatis WELDING
    function calculateNGWelding(id) {
        let incoming = parseInt(document.getElementById('incoming-w-' + id).innerText);
        let totalNG = 0;
        document.querySelectorAll('.ng-val-w-' + id).forEach(input => {
            totalNG += (parseInt(input.value) || 0);
        });

        if (totalNG > incoming) {
            alert("NG melebihi total barang datang rill!");
            event.target.value = 0;
            return calculateNGWelding(id);
        }

        document.getElementById('total-ng-welding-' + id).value = totalNG;
        document.getElementById('ok-welding-' + id).value = incoming - totalNG;
    }
</script>
@endsection