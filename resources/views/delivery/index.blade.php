@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --sultan-navy: #0f172a; 
        --sultan-blue: #4361ee; 
        --sultan-cyan: #4cc9f0; 
        --sultan-bg: #f1f5f9; 
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--sultan-bg); color: var(--sultan-navy); }

    /* ✨ UI ELEMENTS RILL */
    .sultan-card { 
        background: #fff; border: none; border-radius: 28px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.03); overflow: hidden; margin-bottom: 2.5rem;
        border: 1px solid #e2e8f0;
    }
    .sultan-card-header { 
        background: var(--sultan-navy); padding: 25px 35px; 
        border-left: 10px solid var(--sultan-blue); display: flex; 
        justify-content: space-between; align-items: center;
    }

    /* 🚚 HIGH-WAY TRUCK ANIMATION rill */
    @keyframes driveTruck { 0% { transform: translateX(-150px); } 100% { transform: translateX(calc(100vw)); } }
    .highway-container {
        width: 100%; height: 50px; background: #1e293b; border-radius: 50px;
        position: relative; overflow: hidden; margin-bottom: 30px;
        border: 3px solid #334155; box-shadow: inset 0 2px 10px rgba(0,0,0,0.5);
        display: flex; align-items: center;
    }
    .road-line { position: absolute; width: 100%; height: 2px; border-top: 2px dashed rgba(255,255,255,0.1); top: 50%; transform: translateY(-50%); }
    .truck-sultan { position: absolute; font-size: 24px; color: var(--sultan-cyan); animation: driveTruck 15s linear infinite; text-shadow: 0 0 15px var(--sultan-blue); }

    /* 📊 TABLE ELITE rill */
    .table-sultan { width: 100%; border-collapse: separate; border-spacing: 0; }
    .table-sultan thead th { 
        background: #f8fafc; color: #94a3b8; font-size: 10px; 
        text-transform: uppercase; padding: 20px; border-bottom: 2px solid #f1f5f9; letter-spacing: 1.5px; font-weight: 800;
    }
    .table-sultan tbody td { padding: 25px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; }
    .po-id { font-family: 'JetBrains Mono'; color: var(--sultan-blue); font-size: 15px; }

    /* 📈 PROGRESS BAR rill */
    .progress-elite { height: 12px; background: #f1f5f9; border-radius: 50px; overflow: hidden; border: 1px solid #e2e8f0; }
    .bar-glow { background: linear-gradient(90deg, var(--sultan-blue), var(--sultan-cyan)); box-shadow: 0 0 10px rgba(67, 97, 238, 0.4); }

    .btn-sultan-dispatch {
        background: var(--sultan-navy); color: #fff !important; border: none; border-radius: 12px; 
        padding: 10px 20px; font-weight: 800; font-size: 11px; text-transform: uppercase;
        letter-spacing: 1px; transition: 0.3s; font-family: 'Orbitron';
    }
    .btn-sultan-dispatch:hover { transform: translateY(-3px); background: var(--sultan-blue); box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3); }

    .badge-sultan { padding: 6px 15px; border-radius: 8px; font-size: 9px; font-weight: 800; text-transform: uppercase; }
</style>

<div class="container-fluid main-terminal animate__animated animate__fadeIn">
    
    {{-- 🛰️ 1. TOP HEADER CENTER rill --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-5">
        <div>
            <h1 class="h2 mb-0 font-weight-extrabold uppercase" style="letter-spacing: -2px;">
                SHIPMENT <span class="text-primary">DISPATCH HUB</span>
            </h1>
            <p class="text-muted font-weight-bold mb-0 uppercase small tracking-widest">
                <i class="fas fa-satellite mr-2 text-primary"></i> Core Logistics System // Titanium v2.3 rill
            </p>
        </div>
        <a href="{{ route('delivery.history') }}" class="btn btn-white rounded-pill px-4 border font-weight-bold shadow-sm">
            <i class="fas fa-history mr-2"></i> ARCHIVE_VAULT
        </a>
    </div>

    {{-- 🚚 HIGHWAY ANIMATION rill --}}
    <div class="highway-container shadow-sm no-print">
        <div class="road-line"></div>
        <i class="fas fa-truck-moving truck-sultan"></i>
    </div>

    {{-- 📦 2. CUSTOMER MANIFEST LOOP --}}
    @forelse($groupedPOs as $customer => $activePOs)
    <div class="sultan-card">
        <div class="sultan-card-header">
            <div>
                <span class="label-tech text-muted small uppercase tracking-widest" style="color: #94a3b8 !important;">Destination Entity</span>
                <h3 class="m-0 font-weight-extrabold text-white" style="letter-spacing: -1px;">{{ $customer }}</h3>
            </div>
            <span class="badge badge-primary px-4 py-2 font-weight-bold shadow-lg" style="border-radius: 12px; font-family: 'Orbitron'; font-size: 10px;">
                {{ count($activePOs) }} ACTIVE_MANIFESTS
            </span>
        </div>
        
        <div class="table-responsive">
            <table class="table-sultan text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">PO_Identity_No</th>
                        <th>Classification</th>
                        <th>Target_Departure</th>
                        <th width="320">Dispatch_Progress</th>
                        <th>Command</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activePOs as $po)
                    <tr>
                        <td class="text-left pl-5 po-id">{{ $po->po_number }}</td>
                        <td>
                            @php
                                $isReguler = strtoupper($po->keterangan) == 'REGULER';
                                $badgeColor = $isReguler ? 'bg-success' : 'bg-primary';
                            @endphp
                            <span class="badge {{ $badgeColor }} text-white badge-sultan shadow-sm">
                                {{ $po->keterangan ?? 'URGENT' }} rill
                            </span>
                        </td>
                        <td>
                            <div class="font-weight-bold text-dark">
                                <i class="far fa-calendar-alt mr-2 text-primary"></i>{{ date('d/m/Y', strtotime($po->due_date)) }}
                            </div>
                        </td>
                        <td class="px-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small font-weight-bold text-muted uppercase">LOADED</span>
                                <span class="font-weight-bold" style="font-family: 'JetBrains Mono';">{{ number_format($po->total_terkirim) }} / {{ number_format($po->total_qty_po) }}</span>
                            </div>
                            @php $persen = ($po->total_qty_po > 0) ? ($po->total_terkirim / $po->total_qty_po) * 100 : 0; @endphp
                            <div class="progress-elite">
                                <div class="progress-bar bar-glow progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: {{ $persen }}%"></div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('delivery.create', $po->po_number) }}" class="btn-sultan-dispatch">
                                <i class="fas fa-file-signature mr-1"></i> Issue Surat Jalan
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="sultan-card p-5 text-center">
        <i class="fas fa-boxes fa-4x mb-4 text-light"></i>
        <h4 class="font-weight-bold text-muted uppercase tracking-widest">Logistic Clear: No Pending Manifests rill</h4>
    </div>
    @endforelse
</div>
@endsection