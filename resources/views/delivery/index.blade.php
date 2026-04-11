@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Roboto+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root {
        --sultan-navy: #0f172a; 
        --sultan-blue: #3b82f6; /* Electric Blue */
        --sultan-cyan: #06b6d4; /* Neon Cyan */
        --sultan-silver: #f1f5f9; 
        --sultan-bg: #f8fafc; 
        --sultan-success: #10b981;
    }

    .main-terminal { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--sultan-bg); min-height: 100vh; padding: 1.5rem; }

    /* ✨ ANIMASI ENTRANCE */
    @keyframes sultanFade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .anim-sultan { animation: sultanFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) both; }

    /* 🚚 HIGH-WAY TRUCK ANIMATION (TITANIUM BLUE VERSION) */
    @keyframes driveTruck {
        0% { transform: translateX(-150px); }
        100% { transform: translateX(calc(100vw)); }
    }
    .highway-container {
        width: 100%; height: 60px; background: #1e293b; border-radius: 15px;
        position: relative; overflow: hidden; margin-bottom: 30px;
        border: 4px solid #334155; box-shadow: inset 0 2px 15px rgba(0,0,0,0.6);
        display: flex; align-items: center;
    }
    .road-line {
        position: absolute; width: 100%; height: 2px; border-top: 2px dashed rgba(255,255,255,0.15);
        top: 50%; transform: translateY(-50%);
    }
    .truck-sultan {
        position: absolute; font-size: 32px; color: var(--sultan-cyan);
        animation: driveTruck 12s linear infinite; z-index: 2;
        text-shadow: 0 0 20px rgba(6, 182, 212, 0.8); /* Efek Lampu Neon Biru */
    }

    /* ✨ ELITE CARD SYSTEM (TITANIUM BLUE) */
    .sultan-card { 
        background: #fff; border: none; border-radius: 24px; 
        box-shadow: 0 10px 40px rgba(0,0,0,0.04); overflow: hidden; margin-bottom: 3rem;
    }
    .sultan-card-header { 
        background: var(--sultan-navy); padding: 25px 35px; 
        border-left: 10px solid var(--sultan-blue); display: flex; 
        justify-content: space-between; align-items: center;
    }

    /* ✨ ELITE TABLE DESIGN */
    .table-sultan { width: 100%; border-collapse: separate; border-spacing: 0; }
    .table-sultan thead th { 
        background: #f8fafc; color: #64748b; font-size: 11px; 
        text-transform: uppercase; padding: 20px; border: none; letter-spacing: 2px; font-weight: 800;
    }
    .table-sultan tbody td { 
        padding: 25px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; 
        font-size: 14px; font-weight: 600; color: var(--sultan-navy); 
    }
    .table-sultan tbody tr:hover td { background-color: rgba(59, 130, 246, 0.03); color: var(--sultan-blue); }

    /* ✨ PROGRESS BAR ELITE BLUE */
    .progress-elite { 
        height: 14px; background-color: #f1f5f9; border-radius: 50px; 
        overflow: hidden; border: 1px solid #e2e8f0; position: relative;
    }
    .bar-glow {
        background: linear-gradient(90deg, var(--sultan-blue) 0%, var(--sultan-cyan) 100%);
        box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
    }

    /* ✨ TYPOGRAPHY & BUTTONS */
    .po-id { font-family: 'Roboto Mono', monospace; font-weight: 700; color: var(--sultan-blue); font-size: 16px; }
    
    .btn-sultan-dispatch {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #fff; border: none; border-radius: 15px; padding: 12px 25px;
        font-weight: 800; font-size: 11px; text-transform: uppercase;
        letter-spacing: 1.5px; transition: all 0.4s; 
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.2);
        display: inline-flex; align-items: center; gap: 10px;
    }
    .btn-sultan-dispatch:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 15px 30px rgba(59, 130, 246, 0.4); 
        color: var(--sultan-cyan); 
    }

    .badge-sultan { padding: 8px 20px; border-radius: 12px; font-size: 10px; font-weight: 800; text-transform: uppercase; border: 1.5px solid transparent; }
</style>

<div class="container-fluid main-terminal anim-sultan">
    
    {{-- 🛰️ 1. TOP HEADER --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-5">
        <div>
            <h1 class="h2 mb-0 text-gray-900 font-weight-extrabold uppercase" style="letter-spacing: -1px;">
                Shipment <span style="color: var(--sultan-blue)">Dispatch Terminal</span>
            </h1>
            <p class="text-muted font-weight-bold mb-0">AMK CORE LOGISTICS // BLUE TITANIUM V2.3</p>
        </div>
        <a href="{{ route('delivery.history') }}" class="btn btn-white px-4 py-2 shadow-sm" style="border-radius: 15px; font-weight: 800; border: 1.5px solid #e2e8f0; color: #64748b;">
            <i class="fas fa-history mr-2"></i> ARCHIVE RECORDS
        </a>
    </div>

    {{-- 🚚 TITANIUM BLUE HIGHWAY ANIMATION --}}
    <div class="highway-container shadow-2xl no-print">
        <div class="road-line"></div>
        <i class="fas fa-truck-moving truck-sultan"></i>
    </div>

    {{-- 📦 2. CUSTOMER MANIFEST LOOP --}}
    @forelse($groupedPOs as $customer => $activePOs)
    <div class="sultan-card">
        <div class="sultan-card-header">
            <div>
                <span class="small font-weight-bold text-gray-400 uppercase tracking-widest">Delivery Destination</span>
                <h3 class="m-0 font-weight-extrabold text-white">{{ $customer }}</h3>
            </div>
            <span class="badge px-4 py-2 font-weight-bold shadow-lg" style="border-radius: 12px; background: var(--sultan-blue); color: #fff;">
                {{ count($activePOs) }} ACTIVE MANIFESTS
            </span>
        </div>
        
        <div class="table-responsive">
            <table class="table-sultan text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">PO Identity No</th>
                        <th>Classification</th>
                        <th>Target Departure</th>
                        <th width="350">Shipment Progress</th>
                        <th width="250">Command</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activePOs as $po)
                    <tr>
                        <td class="text-left pl-5 po-id">{{ $po->po_number }}</td>
                        <td>
                            @php
                                $isReguler = strtoupper($po->keterangan) == 'REGULER';
                                $badgeColor = $isReguler ? 'var(--sultan-success)' : 'var(--sultan-blue)';
                                $bgOp = $isReguler ? 'rgba(16, 185, 129, 0.05)' : 'rgba(59, 130, 246, 0.05)';
                            @endphp
                            <span class="badge-sultan" style="background: {{ $bgOp }}; color: {{ $badgeColor }}; border-color: {{ $badgeColor }}22;">
                                {{ $po->keterangan ?? 'URGENT' }}
                            </span>
                        </td>
                        <td>
                            <div class="font-weight-bold" style="color: #64748b;">
                                <i class="far fa-calendar-alt mr-2 text-primary"></i>{{ date('d M Y', strtotime($po->due_date)) }}
                            </div>
                        </td>
                        <td class="px-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small font-weight-bold text-muted uppercase">Cargo Loaded</span>
                                <span class="font-weight-bold" style="font-family: 'Roboto Mono';">{{ number_format($po->total_terkirim) }} <small class="text-gray-400">/ {{ number_format($po->total_qty_po) }}</small></span>
                            </div>
                            @php $persen = ($po->total_qty_po > 0) ? ($po->total_terkirim / $po->total_qty_po) * 100 : 0; @endphp
                            <div class="progress-elite">
                                <div class="progress-bar bar-glow progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: {{ $persen }}%"></div>
                            </div>
                        </td>
                        <td class="pr-5">
                            <a href="{{ route('delivery.create', $po->po_number) }}" class="btn-sultan-dispatch">
                                <i class="fas fa-file-invoice"></i> Issue Surat Jalan
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="text-center py-5 sultan-card anim-sultan">
        <div class="mb-4">
            <i class="fas fa-boxes fa-4x" style="color: #e2e8f0;"></i>
        </div>
        <h4 class="text-gray-400 font-weight-bold uppercase tracking-widest">Logistic Clear: No Pending Deliveries</h4>
        <p class="text-muted small">All manifest systems are currently idle.</p>
    </div>
    @endforelse
</div>
@endsection