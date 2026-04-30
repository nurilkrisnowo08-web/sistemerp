@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root { --ind-blue: #4361ee; --ind-amber: #f59e0b; --ind-rose: #ef4444; }
    body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; }
    .heading-cyber { font-family: 'Orbitron'; font-weight: 800; text-transform: uppercase; }
    .qc-card { background: #fff; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #eef2f6; position: relative; margin-bottom: 2rem; }
    .stamping-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--ind-blue); }
    .welding-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--ind-amber); }
    .qty-badge { font-family: 'Orbitron'; font-size: 2.2rem; font-weight: 900; }
    .ng-breakdown-box { background: #fff1f2; border-radius: 18px; padding: 15px; border: 1px solid #fee2e2; margin-bottom: 1.5rem; }
    .ng-item { background: white; border-radius: 12px; padding: 8px 12px; margin-bottom: 6px; border: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
    .ng-input-sm { width: 80px; text-align: center; font-weight: 800; border-radius: 8px; border: 2px solid #e2e8f0; }
    .input-qty-hero { font-size: 24px; height: 60px; text-align: center; font-family: 'Orbitron'; font-weight: 900; }
</style>

<div class="container-fluid py-4">
    {{-- ✨ BLOK NOTIFIKASI ERROR/SUCCESS (Wajib Ada rill!) ✨ --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-xl animate__animated animate__shakeX">
            <b>SUKSES:</b> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-xl animate__animated animate__headShake">
            <b>GAGAL SISTEM:</b> {{ session('error') }}
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-5 bg-white p-4 rounded-3xl border shadow-sm">
        <h2 class="heading-cyber m-0 text-primary">QC_TERMINAL <span class="text-dark">v5.5</span></h2>
        <a href="{{ route('quality.history') }}" class="btn btn-dark rounded-pill px-4">VIEW_HISTORY</a>
    </div>

    <div class="row">
        {{-- SECTION STAMPING --}}
        <div class="col-lg-6">
            <h5 class="font-weight-black mb-4 uppercase"><i class="fas fa-microchip mr-2 text-primary"></i> Stamping Queue</h5>
            @forelse($produksiQueue as $p)
            <div class="qc-card">
                <div class="stamping-strip"></div>
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small font-weight-bold text-muted">BATCH: {{ $p->no_produksi }}</div>
                        <div class="h5 font-weight-black text-primary mb-0">{{ $p->material_code }}</div>
                    </div>
                    <div class="text-right">
                        <div class="qty-badge text-primary" id="incoming-{{ $p->id }}">{{ $p->qty_hasil_ok }}</div>
                    </div>
                </div>

                <div class="p-4">
                    <form action="{{ route('quality.approve', ['type' => 'stamping', 'id' => $p->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="small font-weight-bold text-success uppercase">Verified OK</label>
                                <input type="number" name="qty_ok_final" id="ok-{{ $p->id }}" class="form-control input-qty-hero text-success" value="{{ $p->qty_hasil_ok }}" readonly>
                            </div>
                            <div class="col-6">
                                <label class="small font-weight-bold text-danger uppercase">Verified NG</label>
                                <input type="number" name="qty_ng_final" id="total-ng-{{ $p->id }}" class="form-control input-qty-hero text-danger" value="0" readonly>
                            </div>
                        </div>

                        <div class="ng-breakdown-box">
                            <label class="small font-weight-black text-danger uppercase mb-2 d-block">NG Breakdown:</label>
                            <div style="max-height: 200px; overflow-y: auto;">
                                @foreach($ngStamping as $ng)
                                <div class="ng-item">
                                    <span class="small font-weight-bold">{{ strtoupper($ng->ng_name) }}</span>
                                    <input type="number" name="ng_details[{{ $ng->ng_name }}]" 
                                           class="ng-input-sm ng-val-{{ $p->id }}" 
                                           value="0" min="0" oninput="calculateNG('{{ $p->id }}')">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="small font-weight-black">Inspector Name</label>
                            <input type="text" name="inspector_name" class="form-control" value="{{ Auth::user()->name }}" readonly>
                        </div>

                        <button type="submit" class="btn btn-dark btn-block py-3 font-weight-bold rounded-xl shadow-lg">
                            AUTHORIZE & COMMIT BATCH
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center p-5 text-muted bg-white rounded-3xl border">STAMPING_QUEUE_CLEAR</div>
            @endforelse
        </div>

        {{-- SECTION WELDING --}}
        <div class="col-lg-6">
            <h5 class="font-weight-black mb-4 uppercase text-warning"><i class="fas fa-fire mr-2"></i> Welding Queue</h5>
            @forelse($weldingQueue as $w)
            <div class="qc-card">
                <div class="welding-strip"></div>
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small font-weight-bold text-muted">WLD_ID: {{ $w->no_produksi_stamping }}</div>
                        <div class="h5 font-weight-black text-warning mb-0">{{ $w->part_no }}</div>
                    </div>
                    <div class="text-right">
                        <div class="qty-badge text-warning" id="incoming-w-{{ $w->id }}">{{ $w->qty_ok }}</div>
                    </div>
                </div>

                <div class="p-4">
                    <form action="{{ route('quality.approve', ['type' => 'welding', 'id' => $w->id]) }}" method="POST">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="small font-weight-bold text-success uppercase">Verified OK</label>
                                <input type="number" name="qty_ok_final" id="ok-w-{{ $w->id }}" class="form-control input-qty-hero text-success" value="{{ $w->qty_ok }}" readonly>
                            </div>
                            <div class="col-6">
                                <label class="small font-weight-bold text-danger uppercase">Verified NG</label>
                                <input type="number" name="qty_ng_final" id="total-ng-w-{{ $w->id }}" class="form-control input-qty-hero text-danger" value="0" readonly>
                            </div>
                        </div>

                        <div class="ng-breakdown-box" style="background: #fffbeb;">
                            <label class="small font-weight-black text-warning uppercase mb-2 d-block">NG Breakdown:</label>
                            @foreach($ngWelding as $ng)
                            <div class="ng-item">
                                <span class="small font-weight-bold">{{ strtoupper($ng->ng_name) }}</span>
                                <input type="number" name="ng_details[{{ $ng->ng_name }}]" 
                                       class="ng-input-sm ng-val-w-{{ $w->id }}" 
                                       value="0" min="0" oninput="calculateNGWelding('{{ $w->id }}')">
                            </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-warning btn-block py-3 font-weight-bold rounded-xl shadow-lg">
                            VERIFY & STORE FG
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center p-5 text-muted bg-white rounded-3xl border">WELDING_QUEUE_CLEAR</div>
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
            // Reset input yang bikin berlebih ke 0 rill
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