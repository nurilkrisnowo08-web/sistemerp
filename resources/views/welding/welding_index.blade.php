@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-return: #6366f1; --dark-surface: #0f172a; --bg-main: #f1f5f9;
        --ind-border: #e2e8f0;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #1e293b; }
    
    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(135deg, var(--brand-primary), #7209b7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    /* 📈 LEDGER TABLE */
    .ledger-container { background: #fff; border-radius: 30px; border: 1px solid var(--ind-border); overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.04); margin-bottom: 35px; }
    .table-ledger thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 22px; border: none; }
    .table-ledger td { padding: 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; transition: 0.3s; }
    
    /* 🏷️ PT NAVIGATION */
    .nav-section { background: #fff; padding: 8px; border-radius: 22px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 30px; border: 1px solid #e2e8f0; display: inline-flex; }
    .nav-pills .nav-link { border-radius: 16px; padding: 12px 28px; font-weight: 800; font-size: 0.75rem; transition: 0.3s; color: #64748b !important; position: relative; }
    .nav-pills .nav-link.active { background: var(--dark-surface) !important; color: #fff !important; box-shadow: 0 10px 20px rgba(15, 23, 42, 0.2); }
    .count-badge { position: absolute; top: -5px; right: -5px; background: var(--brand-danger); color: white; font-size: 9px; padding: 2px 6px; border-radius: 20px; border: 2px solid #fff; }

    /* 🛠️ WORK CARDS */
    .work-card { background: #fff; border-radius: 28px; border: 1px solid #eef2f6; padding: 28px; margin-bottom: 20px; transition: 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); display: flex; align-items: center; position: relative; overflow: hidden; }
    .work-card:hover { transform: scale(1.015); box-shadow: 0 25px 60px rgba(67, 97, 238, 0.12); border-color: var(--brand-primary); }
    
    .station-tag { background: #f1f5f9; color: var(--dark-surface); font-family: 'JetBrains Mono'; font-size: 10px; padding: 5px 12px; border-radius: 10px; font-weight: 800; margin-bottom: 10px; display: inline-block; border: 1px solid #e2e8f0; }
    .qty-display { font-family: 'Orbitron'; font-weight: 900; font-size: 42px; color: var(--dark-surface); line-height: 1; }

    /* FORM & INPUTS */
    .tech-input-lg { border-radius: 20px; border: 2.5px solid #eef2f6; font-weight: 800; transition: 0.3s; height: 80px; background: #f8fafc; text-align: center; font-family: 'Orbitron'; font-size: 32px; }
    .tech-input-lg:focus { border-color: var(--brand-primary); outline: none; background: #fff; box-shadow: 0 0 0 8px rgba(67, 97, 238, 0.1); }

    /* SECURITY GATE */
    .security-status { border-radius: 20px; padding: 18px; font-weight: 800; font-size: 12px; text-align: center; transition: 0.3s; margin-top: 25px; border-left: 8px solid transparent; }
    .status-match { background: #dcfce7; color: #166534; border-color: #bbf7d0; border-left-color: var(--brand-success); }
    .status-error { background: #fee2e2; color: #991b1b; border-color: #fecaca; border-left-color: var(--brand-danger); }

    .return-box { background: rgba(99, 102, 241, 0.05); border: 2px dashed var(--brand-return); border-radius: 26px; padding: 24px; transition: 0.3s; }

    /* 📜 VAULT STYLE FOR ARCHIVE */
    .vault-container { background: #fff; border-radius: 20px; border: 1px solid var(--ind-border); overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
    .table-vault thead th { background: #fff; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 15px 22px; border-bottom: 1px solid #f1f5f9; border-top: none; }
    .badge-sync { background: #ede9fe; color: #6366f1; font-weight: 800; font-size: 10px; padding: 5px 15px; border-radius: 20px; border: 1px solid #ddd6fe; text-transform: uppercase; }

    /* ✨ TOMBOL KOTAK STYLE (Sesuai gambar DEPLOY) ✨ */
    .btn-action-square {
        width: 45px; height: 45px; border-radius: 12px; border: none;
        background: #ebf0ff; color: var(--brand-primary); display: flex; align-items: center;
        justify-content: center; transition: 0.3s; 
    }
    .btn-action-square:hover {
        background: var(--brand-primary); color: #fff;
        transform: scale(1.1); box-shadow: 0 10px 20px rgba(67, 97, 238, 0.2);
    }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    {{-- 🛰️ HEADER HUB --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding Terminal <span style="-webkit-text-fill-color: var(--dark-surface);">v3.2</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0"><i class="fas fa-satellite-dish text-primary mr-2"></i> Quality Assurance Command Center</p>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('welding.history') }}" class="btn btn-white rounded-pill px-4 font-weight-extrabold border mr-2 shadow-sm">VAULT</a>
            
            {{-- ✨ TOMBOL FUNGSI KOTAK (JUMP TO ARCHIVE) ✨ --}}
            <button onclick="scrollToArchive()" class="btn-action-square mr-2" title="View Recent Archive">
                <i class="fas fa-history"></i>
            </button>

            <button class="btn btn-primary rounded-pill px-4 font-weight-extrabold shadow-lg" data-toggle="modal" data-target="#modalDeployWelding"><i class="fas fa-plus-circle mr-1"></i> DEPLOY</button>
        </div>
    </div>

    {{-- 📊 LEDGER TABLE --}}
    <div class="ledger-container">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Part Identification</th>
                        <th>Opening</th>
                        <th class="text-success">In (Stamp)</th>
                        <th style="color: var(--brand-return);">In (Return)</th>
                        <th class="text-danger">Out (Weld)</th>
                        <th class="bg-light text-primary">Live Stock</th>
                        <th class="text-right pr-5">Control</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventoryWelding as $inv)
                    <tr>
                        <td class="text-left pl-5">
                            <div class="font-weight-black text-dark" style="font-size: 15px;">{{ $inv->part_no }}</div>
                            <small class="text-muted font-weight-bold uppercase" style="font-size: 9px;">{{ $inv->part_name }}</small>
                        </td>
                        <td class="text-muted font-mono">{{ number_format($inv->init) }}</td>
                        <td class="text-success">+{{ number_format($inv->in_s) }}</td>
                        <td style="color: var(--brand-return);">+{{ number_format($inv->in_r ?? 0) }}</td>
                        <td class="text-danger">-{{ number_format($inv->out) }}</td>
                        <td class="bg-light text-primary font-weight-black" style="font-size: 22px;">{{ number_format($inv->live_stock) }}</td>
                        <td class="text-right pr-5">
                            <button class="btn btn-dark btn-sm rounded-pill px-4 font-weight-black" onclick="quickTake('{{ trim($inv->part_no) }}')">TAKE</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 📑 PT NAVIGATION & ACTIVE BATCHES --}}
    <div class="nav-section animate__animated animate__fadeIn">
        <ul class="nav nav-pills" id="ptTab">
            @foreach($availableCustomers as $index => $customer)
            @php $slugPT = Str::slug($customer); $count = $activeWelding->where('customer', $customer)->count(); @endphp
            <li class="nav-item">
                <a class="nav-link {{ $index == 0 ? 'active' : '' }}" data-toggle="pill" href="#pt-{{ $slugPT }}">
                    {{ strtoupper($customer) }} @if($count > 0) <span class="count-badge animate__animated animate__pulse animate__infinite">{{ $count }}</span> @endif
                </a>
            </li>
            @endforeach
        </ul>
    </div>

    <div class="tab-content" id="ptTabContent">
        @foreach($availableCustomers as $index => $customer)
        @php $slugPT = Str::slug($customer); @endphp
        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="pt-{{ $slugPT }}">
            @foreach($activeWelding->where('customer', $customer) as $aw)
            <div class="work-card shadow-sm animate__animated animate__fadeInUp">
                <div class="col-md-3">
                    <span class="station-tag">{{ $aw->kode_line ?? 'STANDBY' }}</span>
                    <div class="font-weight-black text-primary font-mono">{{ $aw->no_produksi_stamping }}</div>
                </div>
                <div class="col-md-4 border-left pl-4">
                    <div class="font-weight-black h5 mb-0">{{ $aw->part_no }}</div>
                    <small class="text-muted font-weight-bold uppercase">{{ $aw->part_name }}</small>
                </div>
                <div class="col-md-2 text-center">
                    <div class="qty-display">{{ number_format($aw->qty_masuk) }}</div>
                    <small class="text-muted font-weight-black uppercase" style="font-size: 9px;">Target</small>
                </div>
                <div class="col-md-3 text-right">
                    @if($aw->batch_status == 'PENDING')
                        <form action="{{ route('welding.start', $aw->id) }}" method="POST">
                            @csrf @method('PUT')
                            <button class="btn btn-primary rounded-pill py-3 px-5 font-weight-black shadow-lg">START OPERATION</button>
                        </form>
                    @else
                        <button class="btn btn-success rounded-pill py-3 px-5 font-weight-black shadow-lg" data-toggle="modal" data-target="#modalFinish{{ $aw->id }}">FINISH BATCH</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>

    {{-- ✨ PRODUCTION_ARCHIVE SECTION ✨ --}}
    <div id="productionArchiveSection" class="mt-5 animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center mb-3 px-2">
            <h5 class="heading-hub m-0" style="font-size: 1.1rem; -webkit-text-fill-color: var(--dark-surface);">PRODUCTION_ARCHIVE <span style="color: #64748b; font-family: 'Plus Jakarta Sans'">(RECENT)</span></h5>
            <span class="badge-sync">LIVE_SYNC_ACTIVE</span>
        </div>

        <div class="ledger-container p-4">
            {{-- Manggil file history Bapak rill --}}
            @include('welding.welding_history_weldig')
        </div>
    </div>
</div>

{{-- SCRIPT TETAP DI SINI --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // ✨ FUNGSI JUMP OTOMATIS RILL ✨
    function scrollToArchive() {
        const element = document.getElementById('productionArchiveSection');
        const offset = 40; // Jarak aman agar header tidak tertutup rill
        const bodyRect = document.body.getBoundingClientRect().top;
        const elementRect = element.getBoundingClientRect().top;
        const elementPosition = elementRect - bodyRect;
        const offsetPosition = elementPosition - offset;

        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
    }

    // Fungsi-fungsi lain Bapak (calculateTotal, addNgRow, dll) jangan dihapus rill!
</script>
@endsection