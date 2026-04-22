@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-warning: #f59e0b; --dark-surface: #0f172a; --bg-main: #f8fafc;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #334155; }
    .heading-vault { font-family: 'Orbitron'; font-weight: 900; color: var(--dark-surface); letter-spacing: -1px; text-transform: uppercase; }
    
    .stat-card { background: #fff; border-radius: 20px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); transition: 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
    .stat-label { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
    .stat-value { font-family: 'Orbitron'; font-size: 22px; font-weight: 800; color: var(--dark-surface); }

    .ledger-container { background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    .table-history thead th { background: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; padding: 18px 15px; border-bottom: 2px solid #edf2f7; font-weight: 800; }
    .table-history td { padding: 16px 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 13px; }
    
    .chart-box { background: #fff; border-radius: 24px; padding: 25px; border: 1px solid #e2e8f0; margin-bottom: 30px; }
</style>

<div class="container-fluid py-4 px-4">
    {{-- 1. HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="heading-vault mb-1">WELDING_VAULT <span class="text-primary">LEDGER</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0">Rangkuman Mutasi Stok Area Welding</p>
        </div>
        <div class="d-flex">
             <form action="" method="GET" class="d-flex mr-3">
                <input type="date" name="start_date" class="form-control form-control-sm rounded-pill px-3 mr-2" value="{{ $startDate }}">
                <input type="date" name="end_date" class="form-control form-control-sm rounded-pill px-3 mr-2" value="{{ $endDate }}">
                <button type="submit" class="btn btn-primary btn-sm rounded-circle"><i class="fas fa-search"></i></button>
             </form>
            <a href="{{ route('welding.index') }}" class="btn btn-outline-primary rounded-pill px-4 font-weight-extrabold shadow-sm">
                <i class="fas fa-desktop mr-2"></i> MONITORING
            </a>
        </div>
    </div>

    {{-- 2. STATS (Mutasi) --}}
    @php
        $totalIn = $historyData->sum('total_in');
        $totalOut = $historyData->sum('total_out');
        $totalAkhir = $historyData->sum('stock_akhir');
    @endphp
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card border-left-success" style="border-left: 5px solid var(--brand-success) !important;">
                <div class="stat-label">Total Barang Masuk (IN)</div>
                <div class="stat-value text-success">+{{ number_format($totalIn) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card border-left-danger" style="border-left: 5px solid var(--brand-danger) !important;">
                <div class="stat-label">Total Selesai Las (OUT)</div>
                <div class="stat-value text-danger">-{{ number_format($totalOut) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card border-left-primary" style="border-left: 5px solid var(--brand-primary) !important;">
                <div class="stat-label">Live WIP Stock</div>
                <div class="stat-value text-primary">{{ number_format($totalAkhir) }}</div>
            </div>
        </div>
    </div>

    {{-- 3. CHART --}}
    <div class="chart-box shadow-sm">
        <h6 class="font-weight-bold mb-4 text-uppercase small tracking-widest text-muted">Stock Movement Distribution</h6>
        <div id="movementChart"></div>
    </div>

    {{-- 4. TABLE MUTASI --}}
    <div class="ledger-container shadow-sm">
        <div class="table-responsive">
            <table class="table table-history mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Material Part Identification</th>
                        <th>Stok Awal</th>
                        <th class="text-success">Masuk (+)</th>
                        <th class="text-danger">Keluar (-)</th>
                        <th class="text-primary border-left bg-light">Stok Akhir</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($historyData as $h)
                    <tr>
                        <td class="text-left pl-4">
                            <div class="font-weight-bold text-dark">{{ $h->part_no }}</div>
                            <small class="text-muted text-uppercase" style="font-size: 10px;">{{ $h->part_name }}</small>
                        </td>
                        <td class="font-mono text-muted">{{ number_format($h->stock_awal) }}</td>
                        <td class="text-success font-weight-bold">+{{ number_format($h->total_in) }}</td>
                        <td class="text-danger font-weight-bold">-{{ number_format($h->total_out) }}</td>
                        <td class="text-primary font-weight-extrabold border-left bg-light" style="font-size: 16px;">
                            {{ number_format($h->stock_akhir) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-5 text-muted">Data mutasi tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Grafik Mutasi (Ambil 10 data teratas berdasarkan stok terbanyak)
    const rawData = @json($historyData->sortByDesc('stock_akhir')->take(10)->values());
    
    const options = {
        series: [
            { name: 'Masuk', data: rawData.map(i => i.total_in) },
            { name: 'Keluar', data: rawData.map(i => i.total_out) }
        ],
        chart: { type: 'bar', height: 300, stacked: false, toolbar: {show:false} },
        colors: ['#10b981', '#ef4444'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
        xaxis: { categories: rawData.map(i => i.part_no) },
        legend: { position: 'top' }
    };

    new ApexCharts(document.querySelector("#movementChart"), options).render();
</script>
@endsection