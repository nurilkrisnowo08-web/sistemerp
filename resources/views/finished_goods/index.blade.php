@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --primary: #2563eb; --primary-light: #eff6ff;
        --success: #10b981; --success-light: #dcfce7;
        --danger: #ef4444; --danger-light: #fee2e2;
        --warning: #f59e0b; --warning-light: #fef3c7;
        --dark: #0f172a; --slate: #475569; --bg: #f8fafc;
        --border: #e2e8f0;
    }

    .main-terminal { background-color: var(--bg); min-height: 100vh; padding: 1.5rem; font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark); }
    
    /* ✨ CONTROL PANEL */
    .control-panel { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid var(--border); padding: 15px 20px; }
    .form-control, .form-select { border-radius: 8px; border: 1px solid var(--border); font-size: 0.85rem; font-weight: 600; color: var(--dark); }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    
    .btn-custom { border-radius: 8px; font-weight: 700; font-size: 0.8rem; padding: 0.5rem 1.2rem; letter-spacing: 0.5px; text-transform: uppercase; transition: all 0.2s; }
    .btn-action { background: var(--primary); color: #fff; border: none; }
    .btn-action:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,0.2); }
    .btn-recap { background: var(--success); color: #fff; border: none; }
    .btn-recap:hover { background: #059669; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,0.2); }

    /* ✨ MODERN ERP TABLE */
    .card-table { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid var(--border); overflow: hidden; }
    .table-erp { width: 100%; border-collapse: collapse; margin: 0; font-size: 0.8rem; }
    .table-erp thead th { background: var(--dark); color: #fff; text-align: center; padding: 12px 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: none; border-right: 1px solid rgba(255,255,255,0.1); }
    .table-erp thead tr:nth-child(2) th { font-size: 0.75rem; letter-spacing: 0px; }
    .table-erp tbody td { padding: 12px 10px; vertical-align: middle; border-bottom: 1px solid var(--border); border-right: 1px solid var(--border); }
    .table-erp tbody tr:hover { background: #f1f5f9; }
    
    /* ✨ MUTATION HIGHLIGHTS */
    .col-awal { background: #f8fafc; font-weight: 700; color: var(--slate); }
    .col-in { background: var(--success-light); color: #15803d; font-weight: 800; }
    .col-out { background: var(--danger-light); color: #b91c1c; font-weight: 800; }
    .col-akhir { background: var(--primary-light); color: #1d4ed8; font-weight: 800; font-size: 0.9rem; }
    
    .mono { font-family: 'JetBrains Mono', monospace; font-weight: 700; }

    /* ✨ BADGES */
    .badge-status { padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.5px; }
    .badge-crit { background: var(--danger); color: white; animation: pulse-red 2s infinite; }
    .badge-over { background: var(--warning); color: white; }
    .badge-ok { background: var(--success); color: white; }
    
    @keyframes pulse-red { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); } 70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

    /* ✨ SIDE-BY-SIDE LOGS */
    .log-card { background: #fff; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 4px 15px rgba(0,0,0,0.03); overflow: hidden; height: 100%; }
    .log-header { padding: 12px 20px; font-weight: 800; text-transform: uppercase; font-size: 0.8rem; display: flex; justify-content: space-between; align-items: center; }
    .log-table th { background: #f8fafc; color: var(--slate); font-size: 0.7rem; padding: 10px; border-bottom: 2px solid var(--border); }
    .log-table td { padding: 10px; border-bottom: 1px solid var(--border); font-size: 0.8rem; }

    @keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    .animasi-konten { animation: fadeInUp 0.6s ease-out forwards; }

    @media print { .no-print { display: none !important; } .table-erp { font-size: 10px; } }
</style>

<div class="main-terminal text-left animasi-konten">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show font-weight-bold shadow-sm no-print border-0 mb-4" role="alert" style="border-left: 5px solid var(--success) !important; background: var(--success-light); color: #15803d;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" style="color: #15803d;"><span>&times;</span></button>
        </div>
    @endif

    {{-- 🎛️ 1. CONTROL PANEL (FILTER) --}}
    <div class="control-panel mb-4 no-print border-left-primary" style="border-left: 5px solid var(--primary);">
        <form action="{{ route('fg.index') }}" method="GET" class="row align-items-end">
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="small font-weight-bold mb-2 text-muted text-uppercase"><i class="fas fa-industry mr-1 text-primary"></i> Customer Terminal</label>
                <select name="customer" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">-- PILIH CUSTOMER --</option>
                    @foreach($availableCustomers as $c)
                        <option value="{{ $c->code }}" {{ request('customer') == $c->code ? 'selected' : '' }}>{{ $c->code }} - {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label class="small font-weight-bold mb-2 text-muted text-uppercase"><i class="fas fa-calendar-alt mr-1 text-primary"></i> Data Period</label>
                <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-7 text-md-right mt-3 mt-md-0">
                <a href="{{ route('fg.recap') }}" class="btn btn-custom btn-recap mr-2"><i class="fas fa-file-excel mr-1"></i> Rekap Bulanan</a>
                <a href="{{ route('fg.create') }}" class="btn btn-custom btn-action"><i class="fas fa-plus-circle mr-1"></i> Tambah Part</a>
            </div>
        </form>
    </div>

    @if(request('customer') && $allFG->count() > 0)
        
        {{-- 📈 2. VISUAL ANALYTICS (MIXED CHART) --}}
        <div class="row no-print mb-4">
            <div class="col-12">
                <div class="card-table p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h6 class="font-weight-bold text-dark text-uppercase m-0" style="font-size: 0.9rem;">
                            <i class="fas fa-chart-line mr-2 text-primary"></i> Stock Analytics: Actual vs Min Limit
                        </h6>
                    </div>
                    <div style="height: 250px;"><canvas id="stockChart"></canvas></div>
                </div>
            </div>
        </div>

        {{-- 📊 3. MAIN MONITORING TERMINAL --}}
        <div class="card-table mb-4">
            <div class="table-responsive">
                <table class="table-erp text-center mb-0">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 12%;">Part No</th>
                            <th rowspan="2" style="width: 20%; text-align: left; padding-left: 15px;">Part Name</th>
                            <th rowspan="2" style="width: 7%;">Need/Day</th>
                            <th rowspan="2" style="width: 6%;">Min</th>
                            <th rowspan="2" style="width: 6%;">Max</th>
                            <th colspan="4" style="background: #1e293b; border-bottom: 2px solid #334155;">Inventory Mutation Ledger</th>
                            <th rowspan="2" style="width: 6%;">Days</th>
                            <th rowspan="2" style="width: 8%;">Status</th>
                            <th rowspan="2" style="width: 8%;" class="no-print">Action</th>
                        </tr>
                        <tr style="background: #334155;">
                            <th style="width: 8%;">Awal</th>
                            <th style="width: 8%; color: #4ade80;">IN (+)</th>
                            <th style="width: 8%; color: #f87171;">OUT (-)</th>
                            <th style="width: 9%; color: #60a5fa;">Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allFG as $fg)
                        <tr>
                            <td class="font-weight-bold text-primary mono">{{ $fg->part_no }}</td>
                            <td class="text-left font-weight-bold text-dark" style="padding-left: 15px;">{{ $fg->part_name }}</td>
                            <td class="mono text-muted"><span class="roll-number" data-target="{{ $fg->needs_per_day }}">0</span></td>
                            <td class="mono font-weight-bold" style="color: var(--danger);"><span class="roll-number" data-target="{{ $fg->min_stock_pcs }}">0</span></td>
                            <td class="mono font-weight-bold" style="color: var(--warning);"><span class="roll-number" data-target="{{ $fg->max_stock_pcs }}">0</span></td>
                            
                            <td class="col-awal mono text-center"><span class="roll-number" data-target="{{ $fg->stock_awal }}">0</span></td>
                            <td class="col-in mono text-center">+<span class="roll-number" data-target="{{ $fg->in_stp }}">0</span></td>
                            <td class="col-out mono text-center">-<span class="roll-number" data-target="{{ $fg->out_delv }}">0</span></td>
                            <td class="col-akhir mono text-center"><span class="roll-number" data-target="{{ $fg->stock_akhir }}">0</span></td>

                            <td class="mono font-weight-bold text-dark"><span class="roll-number" data-target="{{ $fg->stock_day }}" data-decimal="1">0</span></td>
                            <td>
                                @php
                                    $statusClass = $fg->stock_akhir < $fg->min_stock_pcs ? 'badge-crit' : ($fg->stock_akhir > $fg->max_stock_pcs ? 'badge-over' : 'badge-ok');
                                    $statusText = $fg->stock_akhir < $fg->min_stock_pcs ? 'CRIT' : ($fg->stock_akhir > $fg->max_stock_pcs ? 'OVER' : 'OK');
                                @endphp
                                <span class="badge-status {{ $statusClass }} shadow-sm">{{ $statusText }}</span>
                            </td>
                            <td class="text-center no-print">
                                <a href="{{ route('fg.edit', $fg->id) }}" class="btn btn-sm btn-light border shadow-sm text-warning py-1 px-2"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('fg.destroy', $fg->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin mau hapus part ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border shadow-sm text-danger py-1 px-2"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 📝 4. LOG ACTIVITY SIDE-BY-SIDE --}}
        <div class="row no-print">
            
            <!-- LOG MASUK -->
            <div class="col-md-6 mb-4">
                <div class="log-card" style="border-top: 4px solid var(--success);">
                    <div class="log-header text-success bg-white">
                        <span><i class="fas fa-arrow-down mr-2"></i> Log Masuk (STP Production)</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table-erp log-table text-center mb-0 border-0">
                            <thead><tr><th style="border-left: none;">Time</th><th>Part No</th><th style="border-right: none;">Qty In</th></tr></thead>
                            <tbody>
                                @forelse($stockIn as $in)
                                    <tr>
                                        <td style="border-left: none;"><span class="badge bg-light text-dark border mono">{{ date('H:i', strtotime($in->created_at)) }}</span></td>
                                        <td class="font-weight-bold text-dark mono">{{ $in->part_no }}</td>
                                        <td class="col-in text-center" style="border-right: none;">+{{ number_format($in->qty) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-4 text-muted italic border-0">Awaiting production data...</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- LOG KELUAR -->
            <div class="col-md-6 mb-4">
                <div class="log-card" style="border-top: 4px solid var(--danger);">
                    <div class="log-header text-danger bg-white">
                        <span><i class="fas fa-truck-loading mr-2"></i> Log Keluar (Delivery Order)</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table-erp log-table text-center mb-0 border-0">
                            <thead><tr><th style="border-left: none;">No SJ</th><th>Part No</th><th style="border-right: none;">Qty Out</th></tr></thead>
                            <tbody>
                                @forelse($stockOut as $out)
                                    <tr>
                                        <td class="font-weight-bold text-primary mono" style="font-size: 0.75rem; border-left: none;">{{ $out->no_sj }}</td>
                                        <td class="font-weight-bold text-dark mono">{{ $out->part_no }}</td>
                                        <td class="col-out text-center" style="border-right: none;">-{{ number_format($out->qty_delivery) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-4 text-muted italic border-0">Awaiting delivery data...</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </div>
    @else
        <!-- EMPTY STATE -->
        <div class="text-center py-5 card-table border-0 mt-3 animasi-konten" style="background: #fff;">
            <i class="fas fa-project-diagram fa-4x mb-4" style="color: #cbd5e1;"></i>
            <h4 class="font-weight-bold text-uppercase" style="color: var(--slate);">System Ready: Please Select Customer</h4>
            <p class="text-muted small">Terminal Finish Good Monitoring v.2.0</p>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // ✨ ANIMASI SEMUA ANGKA BERGULIR
        const rollNumbers = document.querySelectorAll('.roll-number');
        rollNumbers.forEach(el => {
            let target = parseFloat(el.getAttribute('data-target'));
            let decimals = el.getAttribute('data-decimal') ? parseInt(el.getAttribute('data-decimal')) : 0;
            
            if (target > 0) {
                let count = 0;
                let speed = target / 40; // Kecepatan disesuaikan
                let timer = setInterval(() => {
                    count += speed;
                    if (count >= target) {
                        el.innerText = target.toLocaleString(undefined, {minimumFractionDigits: decimals, maximumFractionDigits: decimals});
                        clearInterval(timer);
                    } else {
                        el.innerText = count.toLocaleString(undefined, {minimumFractionDigits: decimals, maximumFractionDigits: decimals});
                    }
                }, 30);
            } else { el.innerText = "0"; }
        });

        // ✨ MIXED CHART: BAR (Actual) & LINE (Min Limit)
        @if(isset($labels) && count($labels) > 0)
        const ctx = document.getElementById('stockChart').getContext('2d');
        new Chart(ctx, {
            data: {
                labels: {!! json_encode($labels) !!},
                datasets: [
                    { 
                        type: 'bar',
                        label: 'Actual Stock', 
                        data: {!! json_encode($actStockData) !!}, 
                        backgroundColor: '#3b82f6', 
                        borderRadius: 4,
                        order: 2
                    },
                    { 
                        type: 'line',
                        label: 'Min Limit Threshold', 
                        data: {!! json_encode($minStockData) !!}, 
                        borderColor: '#ef4444', 
                        backgroundColor: '#ef4444',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.3,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        order: 1
                    }
                ]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                animation: {
                    duration: 3000, 
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: { display: true, position: 'top', labels: { font: { family: "'Plus Jakarta Sans', sans-serif", weight: 'bold' } } },
                    tooltip: { mode: 'index', intersect: false, titleFont: { family: "'JetBrains Mono', monospace" } }
                },
                scales: { 
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { family: "'JetBrains Mono', monospace" } } },
                    x: { grid: { display: false }, ticks: { font: { family: "'JetBrains Mono', monospace", size: 10 } } }
                } 
            }
        });
        @endif
    });
</script>
@endsection