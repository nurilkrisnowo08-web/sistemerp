@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-warning: #f59e0b; --dark-surface: #0f172a; --bg-main: #f8fafc;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #334155; }
    
    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(135deg, var(--brand-primary), #7209b7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    /* 📈 LEDGER TABLE */
    .ledger-container { background: #fff; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); margin-bottom: 30px; }
    .table-ledger thead th { background: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; padding: 15px; border-bottom: 2px solid #edf2f7; }
    .table-ledger td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; }
    
    /* 🏷️ PT NAVIGATION */
    .nav-section { background: #fff; padding: 18px; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 25px; border: 1px solid #e2e8f0; }
    .nav-pills .nav-link { 
        border-radius: 12px; padding: 12px 24px; font-weight: 800; 
        transition: 0.3s; margin-right: 12px;
        background: #f1f5f9; color: #475569 !important; border: 2px solid #e2e8f0;
        display: flex; align-items: center;
    }
    .nav-pills .nav-link.active { 
        background: var(--dark-surface) !important; color: #fff !important; 
        border-color: var(--brand-primary); box-shadow: 0 8px 20px rgba(15, 23, 42, 0.3);
    }

    /* 🔴 NOTIF BADGE */
    .count-badge {
        background: var(--brand-danger); color: white; border-radius: 8px;
        padding: 2px 8px; font-size: 10px; margin-left: 10px; font-family: 'JetBrains Mono';
    }

    /* 🛠️ WORK CARDS */
    .work-card { 
        background: #fff; border-radius: 24px; border: 1px solid #eef2f6; 
        padding: 24px; margin-bottom: 16px; transition: 0.3s; 
        display: flex; align-items: center; position: relative;
    }
    .work-card:hover { transform: scale(1.005); box-shadow: 0 15px 30px rgba(0,0,0,0.08); border-color: var(--brand-primary); }
    .qty-display { font-family: 'Orbitron'; font-weight: 800; font-size: 32px; color: var(--dark-surface); line-height: 1; }

    .btn-action-custom { border-radius: 15px; font-weight: 900; letter-spacing: 0.5px; transition: 0.3s; padding: 12px 25px; border: none; }
    .sultan-input { border-radius: 15px; border: 2px solid #f1f5f9; font-weight: 700; transition: 0.3s; }
    .sultan-input:focus { border-color: var(--brand-primary); outline: none; background: #f8faff; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    {{-- 🛸 HEADER HUB --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding Terminal <span style="-webkit-text-fill-color: var(--dark-surface);">v3.0</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0">
                <i class="fas fa-microchip text-primary mr-2"></i> WIP Control & Batch Management System
            </p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
            <a href="{{ route('welding.history') }}" class="btn btn-white rounded-pill px-4 font-weight-extrabold border mr-2 shadow-sm">
                <i class="fas fa-archive mr-2"></i> VAULT
            </a>

            <a href="{{ route('welding.history.weldig') }}" class="btn btn-white rounded-pill px-4 font-weight-extrabold border mr-2 shadow-sm text-primary">
                <i class="fas fa-clipboard-list mr-2"></i> HISTORY
            </a>

            <button class="btn btn-primary rounded-pill px-4 font-weight-extrabold shadow-lg mr-2" style="background: var(--brand-primary); border:none;" data-toggle="modal" data-target="#modalDeployWelding">
                <i class="fas fa-plus-circle mr-1"></i> DEPLOY
            </button>
            <div class="bg-white px-4 py-2 rounded-xl shadow-sm border border-primary text-center">
                <small class="text-muted font-weight-bold d-block uppercase" style="font-size: 8px;">Shift Date</small>
                <span class="font-weight-bold text-primary" style="font-family: 'JetBrains Mono'; font-size: 14px;">{{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}</span>
            </div>
        </div>
    </div>

    {{-- 📊 LEDGER TABLE --}}
    <div class="ledger-container animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Identification</th>
                        <th>START</th>
                        <th class="text-success">IN</th>
                        <th class="text-danger">OUT</th>
                        <th class="text-primary">LIVE STOCK</th>
                        <th>RUN</th>
                        <th class="text-right pr-4">COMMAND</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventoryWelding as $inv)
                    <tr>
                        <td class="text-left pl-4">
                            <div class="font-weight-extrabold text-dark" style="font-size: 14px;">{{ $inv->part_no }}</div>
                            <small class="text-muted font-weight-bold text-uppercase" style="font-size: 9px;">{{ $inv->part_name }}</small>
                        </td>
                        <td style="color: #94a3b8; font-family: 'JetBrains Mono';">{{ number_format($inv->init) }}</td>
                        <td class="text-success font-weight-bold">+{{ number_format($inv->in_s) }}</td>
                        <td class="text-danger font-weight-bold">-{{ number_format($inv->out) }}</td>
                        <td class="text-primary font-weight-extrabold" style="font-size: 16px;">{{ number_format($inv->live_stock) }}</td>
                        <td><span class="badge badge-light border px-2 py-1 font-weight-bold">{{ $inv->run }}x</span></td>
                        <td class="text-right pr-4">
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-4 font-weight-bold" onclick="quickTake('{{ trim($inv->part_no) }}')">TAKE</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sisa kode PT Navigation, Work Cards, dan Modals tetap sama seperti sebelumnya --}}
    {{-- Pastikan pada modalFinish sudah ada textarea 'keterangan' seperti instruksi sebelumnya --}}

    {{-- ... (lanjutan kode Anda) ... --}}

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function quickTake(partNo) {
        document.getElementById('part_select').value = partNo;
        $('#modalDeployWelding').modal('show');
    }
</script>
@endsection