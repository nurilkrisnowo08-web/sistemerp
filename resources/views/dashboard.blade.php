@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --ind-navy: #0f172a; 
        --ind-blue: #4361ee; 
        --ind-danger: #ef4444; 
        --ind-success: #10b981; 
        --ind-warning: #f59e0b;
        --ind-bg: #f8fafc;
        --ind-border: #e2e8f0;
    }
    
    body { background-color: var(--ind-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--ind-navy); overflow-x: hidden; }

    @media (max-width: 768px) {
        .stat-value { font-size: 24px !important; }
        .flow-path { flex-direction: column !important; padding: 20px !important; gap: 15px; }
        .flow-pulse { display: none; }
        .hide-mobile { display: none !important; }
    }

    .tactical-card { 
        background: #ffffff; 
        border: 1px solid var(--ind-border); 
        border-radius: 20px; 
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); 
        transition: transform 0.2s ease, box-shadow 0.2s ease; 
        position: relative; 
        height: 100%;
    }
    .tactical-card:hover { 
        transform: translateY(-4px); 
        border-color: #cbd5e1; 
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06); 
    }
    
    .stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.8px; }
    .stat-value { font-size: 32px; font-weight: 800; letter-spacing: -0.5px; }

    .flow-path { background: var(--ind-navy); border-radius: 20px; padding: 25px 30px; display: flex; align-items: center; justify-content: space-between; position: relative; }
    .flow-step { flex: 1; text-align: center; z-index: 2; transition: 0.2s; color: #fff !important; text-decoration: none !important; }
    .flow-step:hover { transform: scale(1.05); }
    .flow-pulse { width: 30px; height: 2px; background: #334155; opacity: 0.8; }

    .ticker-wrap { background: #fff; border-radius: 14px; border: 1px solid #fee2e2; padding: 10px 0; overflow: hidden; }
    .ticker-move { display: flex; width: max-content; animation: ticker 45s linear infinite; }
    .ticker-item { padding: 0 40px; font-weight: 700; font-size: 12px; color: var(--ind-danger); font-family: 'JetBrains Mono'; }
    @keyframes ticker { from { transform: translateX(0); } to { transform: translateX(-50%); } }

    .table-modern thead th { background: #f8fafc; border: none; font-size: 11px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; padding: 14px 18px; }
    .table-modern td { padding: 14px 18px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 13px; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
    {{-- 1. HEADER --}}
    <div class="tactical-card p-4 mb-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h4 mb-1 font-weight-extrabold" style="letter-spacing: -0.5px;">
                    Dashboard <span class="text-primary">Monitoring Operasional</span>
                </h1>
                <p class="text-muted small font-weight-bold mb-0">
                    <i class="fas fa-clock mr-1 text-primary"></i> 
                    Pembaruan Sistem: <span id="real-clock">{{ date('H:i') }}</span> WIB
                </p>
            </div>
            <div class="dropdown">
                <button class="btn btn-light rounded-pill px-4 font-weight-bold border dropdown-toggle shadow-sm" data-toggle="dropdown">
                    <i class="fas fa-sliders-h mr-1 text-muted"></i> Pilihan Tampilan
                </button>
                <div class="dropdown-menu dropdown-menu-right border-0 shadow-lg p-2" style="border-radius: 14px;">
                    <a class="dropdown-item py-2 font-weight-bold" href="{{ route('dashboard', ['mode' => 'summary']) }}">Ringkasan Stok</a>
                    <a class="dropdown-item py-2 font-weight-bold" href="{{ route('dashboard', ['mode' => 'delivery']) }}">Monitoring Pengiriman</a>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. PERINGATAN STOK MINIMUM (TICKER) --}}
    @if(isset($shortageParts) && count($shortageParts) > 0)
    <div class="ticker-wrap mb-4 shadow-sm">
        <div class="ticker-move">
            @foreach($shortageParts->merge($shortageParts) as $p)
            <div class="ticker-item">
                <i class="fas fa-exclamation-triangle mr-1"></i> PERINGATAN: Part [{{ $p->part_no }}] berada di bawah stok minimum (Stok Saat Ini: {{ number_format($p->actual_stock) }} pcs)
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 3. KARTU STATISTIK UTAMA --}}
    <div class="row mb-4 g-3">
        <div class="col-6 col-md-3 mb-3">
            <div class="tactical-card p-4 text-center" style="border-bottom: 4px solid var(--ind-blue);">
                <div class="stat-label">Total Part Terdaftar</div>
                <div class="stat-value text-dark roll-number" data-target="{{ $totalParts ?? 0 }}">0</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="tactical-card p-4 text-center" style="border-bottom: 4px solid var(--ind-danger);">
                <div class="stat-label text-danger">Part Stok Kritis</div>
                <div class="stat-value text-danger roll-number" data-target="{{ $critCount ?? 0 }}">0</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="tactical-card p-4 text-center" style="border-bottom: 4px solid var(--ind-success);">
                <div class="stat-label text-success">Produksi Hari Ini</div>
                <div class="stat-value text-success">+<span class="roll-number" data-target="{{ $todayProd ?? 0 }}">0</span></div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="tactical-card p-4 text-center" style="border-bottom: 4px solid var(--ind-warning);">
                <div class="stat-label text-warning">Pengiriman Hari Ini</div>
                <div class="stat-value text-warning">-<span class="roll-number" data-target="{{ $todayDelv ?? 0 }}">0</span></div>
            </div>
        </div>
    </div>

    {{-- 4. ALUR LOGISTIK & PRODUKSI --}}
    <div class="tactical-card p-4 mb-4 bg-dark">
        <div class="flow-path">
            <a href="{{ route('po-customer.index') }}" class="flow-step">
                <i class="fas fa-file-invoice fa-2x mb-2 text-primary"></i>
                <div class="small font-weight-bold opacity-75">Customer PO</div>
                <div class="h5 font-weight-bold mb-0">{{ $totalPO ?? 0 }} PO</div>
            </a>
            <div class="flow-pulse"></div>
            <a href="{{ route('produksi.index') }}" class="flow-step">
                <i class="fas fa-cogs fa-2x mb-2 text-warning"></i>
                <div class="small font-weight-bold opacity-75">Proses Produksi</div>
                <div class="h5 font-weight-bold mb-0">MONITOR</div>
            </a>
            <div class="flow-pulse"></div>
            <a href="{{ route('fg.index') }}" class="flow-step">
                <i class="fas fa-boxes fa-2x mb-2 text-success"></i>
                <div class="small font-weight-bold opacity-75">Gudang FG</div>
                <div class="h5 font-weight-bold mb-0">READY</div>
            </a>
            <div class="flow-pulse"></div>
            <a href="{{ route('delivery.index') }}" class="flow-step">
                <i class="fas fa-shipping-fast fa-2x mb-2 text-danger"></i>
                <div class="small font-weight-bold opacity-75">Antrean Surat Jalan</div>
                <div class="h5 font-weight-bold mb-0">{{ $pendingDelvCount ?? 0 }} SJ</div>
            </a>
        </div>
    </div>

    {{-- 5. TABEL STOK KRITIS & GRAFIK STOK --}}
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="tactical-card overflow-hidden">
                <div class="p-3 bg-danger text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold small text-uppercase"><i class="fas fa-exclamation-circle mr-2"></i> Daftar Stok Kurang (< Min)</h6>
                    <span class="badge bg-white text-danger font-weight-bold px-3 py-1" style="border-radius: 8px;">{{ $critCount }} PART</span>
                </div>
                <div class="table-responsive" style="max-height: 400px;">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>Part Identity</th>
                                <th class="text-right">Stok Aktual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shortageParts as $p)
                            <tr>
                                <td>
                                    <div class="font-weight-bold" style="font-family: 'JetBrains Mono';">{{ $p->part_no }}</div>
                                    <small class="text-muted text-uppercase">{{ $p->customer ?? $p->customer_code ?? '-' }}</small>
                                </td>
                                <td class="text-right">
                                    <div class="text-danger font-weight-bold h6 mb-0">{{ number_format($p->actual_stock) }}</div>
                                    <div class="text-muted" style="font-size: 10px;">MIN: {{ number_format($p->min_stock_pcs) }} pcs</div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="py-5 text-center text-muted">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                    <div class="small font-weight-bold">Semua stok part berada di atas batas aman</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="tactical-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="font-weight-bold m-0 text-primary uppercase small">Grafik Monitoring Stok Part</h6>
                    <form action="{{ route('dashboard') }}" method="GET" class="hide-mobile">
                        <select name="customer" class="btn btn-light btn-sm rounded-pill border px-3 font-weight-bold" onchange="this.form.submit()">
                            <option value="">-- Semua Customer --</option>
                            @if(isset($customersList))
                                @foreach($customersList as $cust)
                                    <option value="{{ $cust }}" {{ ($selectedCustomer ?? '') == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                                @endforeach
                            @endif
                        </select>
                    </form>
                </div>
                <div style="height: 330px;"><canvas id="mainDashboardChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Clock
        setInterval(() => {
            document.getElementById('real-clock').innerText = new Date().toLocaleTimeString('id-ID', { hour12: false });
        }, 1000);

        // Counter Numbers
        const rollNumbers = document.querySelectorAll('.roll-number');
        rollNumbers.forEach(el => {
            let target = parseFloat(el.getAttribute('data-target'));
            if(target === 0) return;
            let count = 0;
            let timer = setInterval(() => {
                count += target / 30;
                if (count >= target) { el.innerText = Math.floor(target).toLocaleString(); clearInterval(timer); } 
                else { el.innerText = Math.floor(count).toLocaleString(); }
            }, 30);
        });

        // Chart.js
        const ctx = document.getElementById('mainDashboardChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($labels ?? []) !!},
                datasets: [
                    { label: 'Stok Aktual', data: {!! json_encode($actStockData ?? []) !!}, backgroundColor: '#4361ee', borderRadius: 6 },
                    { label: 'Batas Minimum', data: {!! json_encode($minStockData ?? []) !!}, borderColor: '#ef4444', borderWidth: 2, type: 'line', pointRadius: 0 }
                ]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                scales: { 
                    x: { ticks: { font: { size: 10, family: "'Plus Jakarta Sans', sans-serif" } } },
                    y: { ticks: { font: { size: 10, family: "'Plus Jakarta Sans', sans-serif" } } }
                }
            }
        });
    });
</script>
@endsection