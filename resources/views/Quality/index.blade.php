@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root { 
        --ind-navy: #0f172a; --ind-blue: #4361ee; --ind-amber: #f59e0b; 
        --ind-emerald: #10b981; --ind-rose: #ef4444; 
    }
    body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; color: var(--ind-text); }
    
    .heading-cyber { font-family: 'Orbitron'; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
    
    .qc-card { 
        background: #fff; border-radius: 30px; border: 1px solid #eef2f6; 
        box-shadow: 0 20px 50px rgba(0,0,0,0.03); overflow: hidden; 
        position: relative; margin-bottom: 2.5rem; transition: 0.3s;
    }
    .qc-card:hover { transform: translateY(-5px); box-shadow: 0 25px 60px rgba(67, 97, 238, 0.1); }
    
    .stamping-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 8px; background: var(--ind-blue); }
    .welding-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 8px; background: var(--ind-amber); }
    
    .qty-badge { font-family: 'Orbitron'; font-size: 2.5rem; font-weight: 900; line-height: 1; }
    
    .input-ind { 
        background: #f8fafc; border: 2px solid #edf2f7; border-radius: 16px; 
        font-weight: 700; padding: 12px 18px; transition: 0.3s;
    }
    .input-ind:focus { border-color: var(--ind-blue); background: #fff; outline: none; }
    
    .input-qty-hero { 
        font-size: 28px; height: 70px; text-align: center; 
        font-family: 'Orbitron'; font-weight: 900; border-radius: 20px;
    }

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
        border: none; cursor: pointer; transition: 0.4s;
    }

    .security-status { border-radius: 15px; padding: 10px; font-weight: 800; font-size: 11px; text-align: center; margin-bottom: 15px; }
</style>

<div class="container-fluid py-4">
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
            <p class="text-muted small font-weight-bold mb-0 uppercase tracking-widest">Precision Quality Verification (Partial Support)</p>
        </div>
        <a href="{{ route('quality.history') }}" class="btn btn-dark rounded-pill px-4 font-weight-bold shadow-sm">
            <i class="fas fa-history mr-2"></i> AUDIT_LOG
        </a>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <h5 class="font-weight-black mb-4 uppercase tracking-tighter ml-2">
                <i class="fas fa-microchip mr-2 text-primary"></i> Stamping Incoming
            </h5>
            @forelse($produksiQueue as $p)
            @php $remaining = $p->qty_hasil_ok - ($p->total_checked_so_far ?? 0); @endphp
            <div class="qc-card animate__animated animate__fadeInLeft">
                <div class="stamping-strip"></div>
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <div class="h5 font-weight-black text-primary mb-0">{{ $p->no_produksi }}</div>
                        <span class="badge badge-primary px-3 mt-1">{{ $p->material_code }}</span>
                    </div>
                    <div class="text-right">
                        <div class="qty-badge text-primary" id="remaining-{{ $p->id }}">{{ $remaining }}</div>
                        <label class="small font-weight-black text-muted uppercase mb-0">Units Remaining</label>
                    </div>
                </div>

                <div class="p-4">
                    <form action="{{ route('quality.approve', ['type' => 'stamping', 'id' => $p->id]) }}" method="POST" class="ajax-qc-form">
                        @csrf
                        
                        <div id="msg-{{ $p->id }}" class="security-status bg-light text-muted">READY_FOR_PARTIAL_CHECK</div>

                        <div class="row mb-4">
                            <div class="col-4">
                                <label class="small font-weight-black text-success uppercase mb-2 ml-1">Verified OK</label>
                                <input type="number" name="qty_ok_final" id="ok-{{ $p->id }}" class="form-control input-qty-hero text-success border-success" value="0" oninput="validatePartial('{{ $p->id }}', {{ $remaining }})">
                            </div>
                            <div class="col-4">
                                <label class="small font-weight-black text-danger uppercase mb-2 ml-1">Verified NG</label>
                                <input type="number" name="qty_ng_final" id="total-ng-{{ $p->id }}" class="form-control input-qty-hero text-danger border-danger" value="0" readonly>
                            </div>
                            <div class="col-4">
                                <label class="small font-weight-black text-primary uppercase mb-2 ml-1">Return</label>
                                <input type="number" name="qty_return_final" id="ret-{{ $p->id }}" class="form-control input-qty-hero text-primary border-primary" value="0" oninput="validatePartial('{{ $p->id }}', {{ $remaining }})">
                            </div>
                        </div>

                        <div class="ng-breakdown-box mb-4">
                            <label class="font-weight-black text-danger small uppercase mb-3 d-block">Defect Category Verification:</label>
                            <div style="max-height: 200px; overflow-y: auto;">
                                @foreach($ngStamping as $ng)
                                <div class="ng-item shadow-sm">
                                    {{-- ✨ FIX: Karena mengambil data dari master_materials, kita gunakan alias_code atau material_type rill --}}
                                    <span class="small font-weight-bold text-dark">[{{ $ng->alias_code }}] {{ $ng->material_type }}</span>
                                    <input type="number" name="ng_details[{{ $ng->alias_code }}]" class="ng-input-sm ng-val-{{ $p->id }}" value="0" oninput="validatePartial('{{ $p->id }}', {{ $remaining }})">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <input type="text" name="inspector_name" class="form-control input-ind mb-4" placeholder="INSPECTOR NAME..." required>

                        <button type="submit" id="btn-{{ $p->id }}" class="btn btn-dark btn-block btn-action shadow-lg" disabled>
                            SUBMIT PARTIAL CHECK <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center p-5 text-muted bg-white rounded-3xl border">STAMPING_QUEUE_CLEAR</div>
            @endforelse
        </div>

        <div class="col-lg-6">
            <h5 class="font-weight-black mb-4 uppercase text-warning text-tracking-tighter ml-2">
                <i class="fas fa-fire mr-2"></i> Welding Incoming
            </h5>
            @forelse($weldingQueue as $w)
            @php $remainingW = $w->qty_ok - ($w->total_checked ?? 0); @endphp
            <div class="qc-card animate__animated animate__fadeInRight">
                <div class="welding-strip"></div>
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <div class="h5 font-weight-black text-warning mb-0">{{ $w->no_produksi_stamping }}</div>
                        <span class="badge badge-warning text-dark px-3 mt-1">{{ $w->part_no }}</span>
                    </div>
                    <div class="text-right">
                        <div class="qty-badge text-warning" id="remaining-w-{{ $w->id }}">{{ $remainingW }}</div>
                        <label class="small font-weight-black text-muted uppercase mb-0">Units Remaining</label>
                    </div>
                </div>

                <div class="p-4">
                    <form action="{{ route('quality.approve', ['type' => 'welding', 'id' => $w->id]) }}" method="POST" class="ajax-qc-form">
                        @csrf
                        <div id="msg-w-{{ $w->id }}" class="security-status bg-light text-muted">READY_FOR_PARTIAL_CHECK</div>

                        <div class="row mb-4">
                            <div class="col-4">
                                <label class="small font-weight-black text-success uppercase mb-2 ml-1">Verified OK</label>
                                <input type="number" name="qty_ok_final" id="ok-w-{{ $w->id }}" class="form-control input-qty-hero text-success border-success" value="0" oninput="validatePartialWelding('{{ $w->id }}', {{ $remainingW }})">
                            </div>
                            <div class="col-4">
                                <label class="small font-weight-black text-danger uppercase mb-2 ml-1">Verified NG</label>
                                <input type="number" name="qty_ng_final" id="total-ng-w-{{ $w->id }}" class="form-control input-qty-hero text-danger border-danger" value="0" readonly>
                            </div>
                            <div class="col-4">
                                <label class="small font-weight-black text-primary uppercase mb-2 ml-1">Return</label>
                                <input type="number" name="qty_return_final" id="ret-w-{{ $w->id }}" class="form-control input-qty-hero text-primary border-primary" value="0" oninput="validatePartialWelding('{{ $w->id }}', {{ $remainingW }})">
                            </div>
                        </div>

                        <div class="ng-breakdown-box mb-4" style="background: #fffbeb; border-color: #fef3c7;">
                            <label class="small font-weight-black text-warning uppercase mb-3 d-block ml-1">Welding Defect Verification:</label>
                            <div style="max-height: 200px; overflow-y: auto;">
                                @foreach($ngWelding as $ng)
                                <div class="ng-item shadow-sm">
                                    {{-- ✨ FIX: Sinkronisasi pemanggilan kolom master_materials rill --}}
                                    <span class="small font-weight-bold text-dark">[{{ $ng->alias_code }}] {{ $ng->material_type }}</span>
                                    <input type="number" name="ng_details[{{ $ng->alias_code }}]" class="ng-input-sm ng-val-w-{{ $w->id }}" value="0" oninput="validatePartialWelding('{{ $w->id }}', {{ $remainingW }})">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <input type="text" name="inspector_name" class="form-control input-ind mb-4" placeholder="INSPECTOR NAME..." required>

                        <button type="submit" id="btn-w-{{ $w->id }}" class="btn btn-warning btn-block btn-action shadow-lg text-dark" disabled>
                            SUBMIT PARTIAL CHECK <i class="fas fa-database ml-2"></i>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // ✨ INTERCEPTOR AJAX UNTUK AUTO-PRINT LABEL PARTIAL OTOMATIS RILL
    $(document).on('submit', '.ajax-qc-form', function(e) {
        e.preventDefault();
        
        let form = $(this);
        let submitBtn = form.find('button[type="submit"]');
        let initialText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> TRANSMITTING TO DIGITAL STACK...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(res) {
                if(res.success) {
                    let okInput = form.find('input[name="qty_ok_final"]').val() || 0;
                    if(parseInt(okInput) > 0 && res.print_url) {
                        window.open(res.print_url, '_blank');
                    }
                    window.location.reload();
                } else {
                    alert('Gagal Memproses Data: ' + res.message);
                    submitBtn.prop('disabled', false).html(initialText);
                }
            },
            error: function(err) {
                alert('System Error! Gagal melakukan komunikasi data Quality Gate.');
                submitBtn.prop('disabled', false).html(initialText);
            }
        });
    });

    // Fungsi Validasi Partial (Stamping)
    function validatePartial(id, remaining) {
        let ok = parseInt(document.getElementById('ok-' + id).value) || 0;
        let ret = parseInt(document.getElementById('ret-' + id).value) || 0;
        let totalNG = 0;
        
        document.querySelectorAll('.ng-val-' + id).forEach(input => {
            totalNG += (parseInt(input.value) || 0);
        });

        document.getElementById('total-ng-' + id).value = totalNG;
        let grandTotal = ok + totalNG + ret;
        let msg = document.getElementById('msg-' + id);
        let btn = document.getElementById('btn-' + id);

        if (grandTotal > remaining) {
            msg.className = "security-status bg-danger text-white animate__animated animate__shakeX";
            msg.innerHTML = "🚨 OVERFLOW: Total (" + grandTotal + ") Melebihi Sisa (" + remaining + ")";
            btn.disabled = true;
        } else if (grandTotal <= 0) {
            msg.className = "security-status bg-light text-muted";
            msg.innerHTML = "READY_FOR_PARTIAL_CHECK";
            btn.disabled = true;
        } else {
            msg.className = "security-status bg-success text-white animate__animated animate__pulse";
            msg.innerHTML = "VALID: Melaporkan " + grandTotal + " PCS dari " + remaining + " PCS";
            btn.disabled = false;
        }
    }

    // Fungsi Validasi Partial (Welding)
    function validatePartialWelding(id, remaining) {
        let ok = parseInt(document.getElementById('ok-w-' + id).value) || 0;
        let ret = parseInt(document.getElementById('ret-w-' + id).value) || 0;
        let totalNG = 0;
        
        document.querySelectorAll('.ng-val-w-' + id).forEach(input => {
            totalNG += (parseInt(input.value) || 0);
        });

        document.getElementById('total-ng-w-' + id).value = totalNG;
        let grandTotal = ok + totalNG + ret;
        let msg = document.getElementById('msg-w-' + id);
        let btn = document.getElementById('btn-w-' + id);

        if (grandTotal > remaining) {
            msg.className = "security-status bg-danger text-white animate__animated animate__shakeX";
            msg.innerHTML = "🚨 OVERFLOW: Total (" + grandTotal + ") Melebihi Sisa (" + remaining + ")";
            btn.disabled = true;
        } else if (grandTotal <= 0) {
            msg.className = "security-status bg-light text-muted";
            msg.innerHTML = "READY_FOR_PARTIAL_CHECK";
            btn.disabled = true;
        } else {
            msg.className = "security-status bg-success text-white animate__animated animate__pulse";
            msg.innerHTML = "VALID: Melaporkan " + grandTotal + " PCS dari " + remaining + " PCS";
            btn.disabled = false;
        }
    }
</script>
@endsection