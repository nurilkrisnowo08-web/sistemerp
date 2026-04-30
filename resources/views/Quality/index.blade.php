@extends('layout.admin')

@section('content')
<!-- Core Assets -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root { 
        --ind-navy: #0f172a; --ind-blue: #4361ee; --ind-amber: #f59e0b; 
        --ind-emerald: #10b981; --ind-rose: #ef4444; 
    }
    body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; }
    
    .heading-cyber { font-family: 'Orbitron'; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
    
    /* Modern Industrial Cards */
    .qc-card { 
        background: #fff; border-radius: 30px; border: 1px solid #eef2f6; 
        box-shadow: 0 20px 50px rgba(0,0,0,0.03); overflow: hidden; 
        position: relative; margin-bottom: 2.5rem; transition: 0.3s;
    }
    .qc-card:hover { transform: translateY(-5px); box-shadow: 0 25px 60px rgba(67, 97, 238, 0.1); }
    
    .stamping-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 8px; background: var(--ind-blue); }
    .welding-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 8px; background: var(--ind-amber); }
    
    .qty-badge { font-family: 'Orbitron'; font-size: 2.5rem; font-weight: 900; line-height: 1; }
    
    /* Input Styling */
    .input-ind { 
        background: #f8fafc; border: 2px solid #edf2f7; border-radius: 16px; 
        font-weight: 700; padding: 12px 18px; transition: 0.3s;
    }
    .input-ind:focus { border-color: var(--ind-blue); background: #fff; outline: none; }
    
    .input-qty-hero { 
        font-size: 28px; height: 70px; text-align: center; 
        font-family: 'Orbitron'; font-weight: 900; border-radius: 20px;
    }

    /* NG List Section */
    .ng-breakdown-box { background: #fff1f2; border-radius: 24px; padding: 20px; border: 1px solid #fee2e2; }
    .ng-item { 
        background: white; border-radius: 14px; padding: 12px 16px; 
        margin-bottom: 8px; border: 1px solid #f1f5f9; display: flex; 
        align-items: center; justify-content: space-between; box-shadow: 0 4px 6px rgba(0,0,0,0.01);
    }
    .ng-input-sm { 
        width: 85px; text-align: center; font-weight: 800; 
        border-radius: 10px; border: 2px solid #e2e8f0; font-family: 'JetBrains Mono';
    }

    .btn-action {
        font-family: 'Orbitron'; font-size: 0.9rem; padding: 18px; 
        border-radius: 20px; text-transform: uppercase; font-weight: 800; 
        border: none; transition: 0.4s;
    }
</style>

<div class="container-fluid py-4">
    <!-- Notifications -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-lg rounded-2xl animate__animated animate__fadeInDown px-4 py-3">
            <i class="fas fa-check-circle mr-2"></i> <b>SYSTEM_SUCCESS:</b> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-lg rounded-2xl animate__animated animate__shakeX px-4 py-3">
            <i class="fas fa-exclamation-triangle mr-2"></i> <b>SYSTEM_ERROR:</b> {{ session('error') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-5 bg-white p-4 rounded-3xl border shadow-sm animate__animated animate__fadeIn">
        <div>
            <h2 class="heading-cyber m-0 text-primary">QC_TERMINAL <span class="text-dark">v6.0</span></h2>
            <p class="text-muted small font-weight-bold mb-0 uppercase tracking-widest">Precision Quality Verification</p>
        </div>
        <a href="{{ route('quality.history') }}" class="btn btn-dark rounded-pill px-4 font-weight-bold shadow-sm">
            <i class="fas fa-history mr-2"></i> AUDIT_LOG
        </a>
    </div>

    <div class="row">
        <!-- 🚛 STAMPING SECTION -->
        <div class="col-lg-6">
            <h5 class="font-weight-black mb-4 uppercase tracking-tighter ml-2 animate__animated animate__fadeInLeft">
                <i class="fas fa-microchip mr-2 text-primary"></i> Stamping Incoming
            </h5>
            @forelse($produksiQueue as $p)
            <div class="qc-card animate__animated animate__fadeInLeft" style="animation-delay: 0.1s;">
                <div class="stamping-strip"></div>
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <div class="small font-weight-bold text-muted uppercase">Batch Identity</div>
                        <div class="h5 font-weight-black text-primary mb-0" style="font-family: 'JetBrains Mono';">{{ $p->no_produksi }}</div>
                        <span class="badge badge-primary px-3 mt-1">{{ $p->material_code }}</span>
                    </div>
                    <div class="text-right">
                        <div class="qty-badge text-primary" id="incoming-{{ $p->id }}">{{ $p->qty_hasil_ok }}</div>
                        <label class="small font-weight-black text-muted uppercase mb-0">Units Inbound</label>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <form action="{{ route('quality.approve', ['type' => 'stamping', 'id' => $p->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-5">
                            <div class="col-6">
                                <label class="small font-weight-black text-success uppercase mb-2 ml-1">Verified OK</label>
                                <input type="number" name="qty_ok_final" id="ok-{{ $p->id }}" class="form-control input-qty-hero text-success bg-white border-success shadow-sm" value="{{ $p->qty_hasil_ok }}" readonly>
                            </div>
                            <div class="col-6">
                                <label class="small font-weight-black text-danger uppercase mb-2 ml-1">Verified NG</label>
                                <input type="number" name="qty_ng_final" id="total-ng-{{ $p->id }}" class="form-control input-qty-hero text-danger bg-white border-danger shadow-sm" value="0" readonly>
                            </div>
                        </div>

                        <div class="ng-breakdown-box mb-5">
                            <label class="font-weight-black text-danger small uppercase mb-3 d-block ml-1">
                                <i class="fas fa-search mr-2"></i>Defect Category Verification:
                            </label>
                            <div style="max-height: 250px; overflow-y: auto; padding-right: 8px;">
                                @foreach($ngStamping as $ng)
                                <div class="ng-item shadow-sm">
                                    <span class="small font-weight-bold text-dark">{{ strtoupper($ng->ng_name) }}</span>
                                    <input type="number" name="ng_details[{{ $ng->ng_name }}]" 
                                           class="ng-input-sm ng-val-{{ $p->id }}" 
                                           value="0" min="0" oninput="calculateNG('{{ $p->id }}')">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group mb-5">
                            <label class="small font-weight-black text-muted uppercase mb-2 ml-1">Assigned Inspector (Manual Write)</label>
                            <!-- Inspector Manual Input -->
                            <input type="text" name="inspector_name" class="form-control input-ind" placeholder="WRITE YOUR NAME HERE..." required>
                        </div>

                        <button type="submit" class="btn btn-dark btn-block btn-action shadow-lg">
                            APPROVE & COMMIT BATCH <i class="fas fa-check-circle ml-2"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center p-5 text-muted bg-white rounded-3xl border animate__animated animate__pulse">STAMPING_QUEUE_CLEAR</div>
            @endforelse
        </div>

        <!-- 👨‍🏭 WELDING SECTION -->
        <div class="col-lg-6">
            <h5 class="font-weight-black mb-4 uppercase text-warning tracking-tighter ml-2 animate__animated animate__fadeInRight">
                <i class="fas fa-fire mr-2"></i> Welding Incoming
            </h5>
            @forelse($weldingQueue as $w)
            <div class="qc-card animate__animated animate__fadeInRight" style="animation-delay: 0.1s;">
                <div class="welding-strip"></div>
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <div class="small font-weight-bold text-muted uppercase">Job ID</div>
                        <div class="h5 font-weight-black text-warning mb-0" style="font-family: 'JetBrains Mono';">{{ $w->no_produksi_stamping }}</div>
                        <span class="badge badge-warning text-dark px-3 mt-1">{{ $w->part_no }}</span>
                    </div>
                    <div class="text-right">
                        <div class="qty-badge text-warning" id="incoming-w-{{ $w->id }}">{{ $w->qty_ok }}</div>
                        <label class="small font-weight-black text-muted uppercase mb-0">Units Inbound</label>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <form action="{{ route('quality.approve', ['type' => 'welding', 'id' => $w->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-5">
                            <div class="col-6">
                                <label class="small font-weight-black text-success uppercase mb-2 ml-1">Verified OK</label>
                                <input type="number" name="qty_ok_final" id="ok-w-{{ $w->id }}" class="form-control input-qty-hero text-success bg-white border-success shadow-sm" value="{{ $w->qty_ok }}" readonly>
                            </div>
                            <div class="col-6">
                                <label class="small font-weight-black text-danger uppercase mb-2 ml-1">Verified NG</label>
                                <input type="number" name="qty_ng_final" id="total-ng-w-{{ $w->id }}" class="form-control input-qty-hero text-danger bg-white border-danger shadow-sm" value="0" readonly>
                            </div>
                        </div>

                        <div class="ng-breakdown-box mb-5" style="background: #fffbeb; border-color: #fef3c7;">
                            <label class="small font-weight-black text-warning uppercase mb-3 d-block ml-1">
                                <i class="fas fa-tools mr-2"></i>Welding Defect Verification:
                            </label>
                            <div style="max-height: 250px; overflow-y: auto; padding-right: 8px;">
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

                        <div class="form-group mb-5">
                            <label class="small font-weight-black text-muted uppercase mb-2 ml-1">Assigned Inspector (Manual Write)</label>
                            <!-- Inspector Manual Input -->
                            <input type="text" name="inspector_name" class="form-control input-ind" placeholder="WRITE YOUR NAME HERE..." required>
                        </div>

                        <button type="submit" class="btn btn-warning btn-block btn-action shadow-lg text-dark">
                            VERIFY & STORE FG <i class="fas fa-database ml-2"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center p-5 text-muted bg-white rounded-3xl border animate__animated animate__pulse">WELDING_QUEUE_CLEAR</div>
            @endforelse
        </div>
    </div>
</div>

<script>
    function calculateNG(id) {
        let incoming = parseInt(document.getElementById('incoming-' + id).innerText);
        let totalNG = 0;
        document.querySelectorAll('.ng-val-' + id).forEach(input => {
            totalNG += (parseInt(input.value) || 0);
        });

        if (totalNG > incoming) {
            alert("🚨 GAGAL: NG Melebihi Barang Datang!");
            event.target.value = 0; 
            return calculateNG(id);
        }

        document.getElementById('total-ng-' + id).value = totalNG;
        document.getElementById('ok-' + id).value = incoming - totalNG;
    }

    function calculateNGWelding(id) {
        let incoming = parseInt(document.getElementById('incoming-w-' + id).innerText);
        let totalNG = 0;
        document.querySelectorAll('.ng-val-w-' + id).forEach(input => {
            totalNG += (parseInt(input.value) || 0);
        });

        if (totalNG > incoming) {
            alert("🚨 GAGAL: NG Melebihi Barang Datang!");
            event.target.value = 0;
            return calculateNGWelding(id);
        }

        document.getElementById('total-ng-w-' + id).value = totalNG;
        document.getElementById('ok-w-' + id).value = incoming - totalNG;
    }
</script>
@endsection