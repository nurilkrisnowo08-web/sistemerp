@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

<style>
    :root {
        --ind-steel: #4e73df; --ind-success: #1cc88a; --ind-danger: #e74a3b;
        --ind-warning: #fd7e14; --ind-dark: #2d3436; --ind-bg: #f8f9fc;
        --ind-slate: #5a5c69;
    }

    /* ✨ MODERN INDUSTRIAL WRAPPER */
    .main-terminal { background-color: var(--ind-bg); min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }
    
    /* ✨ TABLE EXCEL MODERN VERSION */
    .table-excel { 
        border: 2px solid var(--ind-steel) !important; 
        font-size: 11px; color: #000; width: 100%; border-collapse: collapse;
        background: white; border-radius: 8px; overflow: hidden;
    }
    .table-excel thead th { 
        background: linear-gradient(180deg, #4e73df 0%, #224abe 100%) !important; 
        color: #fff !important; text-align: center; padding: 12px 8px; 
        border: 1px solid rgba(255,255,255,0.2) !important; text-transform: uppercase;
        letter-spacing: 1px; font-weight: 800;
    }
    .table-excel td { border: 1px solid #d1d3e2 !important; padding: 10px 6px; vertical-align: middle; }
    .table-excel tbody tr:hover { background-color: rgba(78, 115, 223, 0.05); transition: 0.3s; }

    /* ✨ COLUMN ACCENTS */
    .col-in { background-color: #f0f9ff !important; color: #0d47a1 !important; font-weight: 800; border-left: 2px solid #0d47a1 !important; }
    .col-out { background-color: #fff5f5 !important; color: #b71c1c !important; font-weight: 800; border-left: 2px solid #b71c1c !important; }
    .col-akhir { background-color: #f0fff4 !important; color: #1b5e20 !important; font-weight: 800; font-size: 13px; box-shadow: inset 0 0 5px rgba(0,0,0,0.05); }

    /* ✨ DASHBOARD CARDS */
    .terminal-card { 
        border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
        background: white; overflow: hidden;
    }
    .control-panel { border-left: 5px solid var(--ind-steel); }

    /* ✨ BADGES */
    .badge-crit { background: var(--ind-danger); color: white; animation: pulse-red 2s infinite; }
    .badge-over { background: var(--ind-warning); color: white; }
    .badge-ok { background: var(--ind-success); color: white; }
    
    @keyframes pulse-red { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }

    /* ✨ ANIMASI FADE IN */
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animasi-konten { animation: fadeInUp 0.8s ease-out forwards; }

    /* PRINT FIX */
    @media print { .no-print { display: none !important; } .table-excel { font-size: 10px; } }
</style>

<div class="main-terminal text-dark text-left animasi-konten">
    {{-- ALERT PEMBERITAHUAN --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show font-weight-bold shadow-sm no-print border-0" role="alert" style="border-left: 5px solid #155724 !important;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- 🛰️ 1. CONTROL PANEL (FILTER) --}}
    <div class="terminal-card control-panel mb-4 no-print">
        <div class="card-body py-3">
            <form action="{{ route('fg.index') }}" method="GET" class="row align-items-center">
                <div class="col-md-3">
                    <label class="small font-weight-bold mb-1 text-primary uppercase"><i class="fas fa-industry mr-1"></i> Customer Terminal</label>
                    <select name="customer" class="form-control form-control-sm font-weight-bold border-primary shadow-sm" onchange="this.form.submit()" style="border-radius: 8px;">
                        <option value="">-- PILIH CUSTOMER --</option>
                        @foreach($availableCustomers as $c)
                            <option value="{{ $c->code }}" {{ request('customer') == $c->code ? 'selected' : '' }}>{{ $c->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold mb-1 text-primary uppercase"><i class="fas fa-calendar-alt mr-1"></i> Data Period</label>
                    <input type="date" name="date" class="form-control form-control-sm font-weight-bold border-primary shadow-sm" value="{{ $date }}" onchange="this.form.submit()" style="border-radius: 8px;">
                </div>
                <div class="col-md-7 text-right pt-3">
                    <a href="{{ route('fg.recap') }}" class="btn btn-success btn-sm font-weight-bold shadow-sm px-3 mr-1" style="border-radius: 20px;"><i class="fas fa-file-excel mr-1"></i> REKAP BULANAN</a>
                    <a href="{{ route('fg.create') }}" class="btn btn-primary btn-sm font-weight-bold shadow-sm px-3 mr-1" style="border-radius: 20px;"><i class="fas fa-plus-circle mr-1"></i> TAMBAH PART</a>
                </div>
            </form>
        </div>
    </div>

    @if(request('customer') && $allFG->count() > 0)
        {{-- 📈 2. VISUAL ANALYTICS --}}
        <div class="row no-print mb-4">
            <div class="col-12">
                <div class="terminal-card shadow-sm p-3">
                    <h6 class="font-weight-bold text-slate small uppercase mb-3"><i class="fas fa-chart-bar mr-2 text-primary"></i> Stock Analytics: Actual vs Min Limit</h6>
                    <div style="height: 220px;"><canvas id="stockChart"></canvas></div>
                </div>
            </div>
        </div>

        {{-- 📊 3. MAIN MONITORING TERMINAL --}}
        <div class="terminal-card shadow-lg mb-4 border-0">
            <div class="table-responsive">
                <table class="table-excel text-center mb-0">
                    <thead>
                        <tr>
                            <th rowspan="2">Part No</th>
                            <th rowspan="2">Part Name</th>
                            <th rowspan="2">Needs/Day</th>
                            <th rowspan="2">Min</th>
                            <th rowspan="2">Max</th>
                            <th colspan="4" style="background:#2d3436 !important;">Inventory Mutation Ledger</th>
                            <th rowspan="2">Days</th>
                            <th rowspan="2">Status</th>
                            <th rowspan="2" class="no-print">Command</th>
                        </tr>
                        <tr>
                            <th style="background:#858796 !important;">Initial</th>
                            <th style="background:#36b9cc !important;">IN </th>
                            <th style="background:#e74a3b !important;">OUT (DELV)</th>
                            <th style="background:#1cc88a !important;">FINAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allFG as $fg)
                        <tr>
                            <td class="font-weight-bold text-primary" style="font-family: 'Roboto Mono';">{{ $fg->part_no }}</td>
                            <td class="text-left small font-weight-bold" style="padding-left: 15px;">{{ $fg->part_name }}</td>
                            <td class="font-weight-bold"><span class="roll-number" data-target="{{ $fg->needs_per_day }}">0</span></td>
                            <td class="text-primary font-weight-bold"><span class="roll-number" data-target="{{ $fg->min_stock_pcs }}">0</span></td>
                            <td class="text-danger font-weight-bold"><span class="roll-number" data-target="{{ $fg->max_stock_pcs }}">0</span></td>
                            
                            <td class="bg-light font-weight-bold text-muted"><span class="roll-number" data-target="{{ $fg->stock_awal }}">0</span></td>
                            <td class="col-in">+<span class="roll-number" data-target="{{ $fg->in_stp }}">0</span></td>
                            <td class="col-out">-<span class="roll-number" data-target="{{ $fg->out_delv }}">0</span></td>
                            <td class="col-akhir"><span class="roll-number" data-target="{{ $fg->stock_akhir }}">0</span></td>

                            <td class="font-weight-bold text-dark"><span class="roll-number" data-target="{{ $fg->stock_day }}" data-decimal="1">0</span></td>
                            <td>
                                @php
                                    $statusClass = $fg->stock_akhir < $fg->min_stock_pcs ? 'badge-crit' : ($fg->stock_akhir > $fg->max_stock_pcs ? 'badge-over' : 'badge-ok');
                                    $statusText = $fg->stock_akhir < $fg->min_stock_pcs ? 'CRIT' : ($fg->stock_akhir > $fg->max_stock_pcs ? 'OVER' : 'OK');
                                @endphp
                                <span class="badge {{ $statusClass }} px-3 py-1 shadow-sm" style="font-size: 9px; border-radius: 4px;">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="text-center no-print">
                                <div class="btn-group">
                                    <a href="{{ route('fg.edit', $fg->id) }}" class="btn btn-sm btn-outline-warning border-0"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('fg.destroy', $fg->id) }}" method="POST" onsubmit="return confirm('Yakin mau hapus part ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 ml-1"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 📝 4. LOG ACTIVITY SIDE-BY-SIDE --}}
        <div class="row no-print">
            <div class="col-md-6 mb-4">
                <div class="terminal-card border-top-success h-100" style="border-top: 4px solid var(--ind-success);">
                    <div class="card-header bg-white py-3 font-weight-bold text-success small text-uppercase d-flex justify-content-between">
                        <span>Log Masuk (STP Production)</span>
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="table-responsive">
                        <table class="table-excel text-center small mb-0" style="border:none !important;">
                            <thead class="bg-success text-white"><tr><th>TIME</th><th>PART NO</th><th>QTY</th></tr></thead>
                            <tbody>
                                @forelse($stockIn as $in)
                                    <tr>
                                        <td><span class="badge badge-light border">{{ date('H:i', strtotime($in->created_at)) }}</span></td>
                                        <td class="font-weight-bold text-primary">{{ $in->part_no }}</td>
                                        <td class="text-success font-weight-bold">+{{ number_format($in->qty) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-4 text-muted italic">Awaiting production data...</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="terminal-card border-top-danger h-100" style="border-top: 4px solid var(--ind-danger);">
                    <div class="card-header bg-white py-3 font-weight-bold text-danger small text-uppercase d-flex justify-content-between">
                        <span>Log Keluar (Delivery Order)</span>
                        <i class="fas fa-truck-loading"></i>
                    </div>
                    <div class="table-responsive">
                        <table class="table-excel text-center small mb-0" style="border:none !important;">
                            <thead class="bg-danger text-white"><tr><th>NO SJ</th><th>PART NO</th><th>QTY</th></tr></thead>
                            <tbody>
                                @forelse($stockOut as $out)
                                    <tr>
                                        <td class="font-weight-bold text-primary" style="font-size: 9px;">{{ $out->no_sj }}</td>
                                        <td class="font-weight-bold">{{ $out->part_no }}</td>
                                        <td class="text-danger font-weight-bold">-{{ number_format($out->qty_delivery) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-4 text-muted italic">Awaiting delivery data...</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5 card terminal-card border-0 bg-white mt-3 animasi-konten">
            <i class="fas fa-project-diagram fa-4x text-gray-200 mb-4"></i>
            <h4 class="text-gray-400 font-weight-bold uppercase">System Ready: Please Select Customer</h4>
            <p class="text-muted small">Terminal Finish Good Monitoring v.2.0</p>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // ✨ SAKTI 1: ANIMASI SEMUA ANGKA BERGULIR
        const rollNumbers = document.querySelectorAll('.roll-number');
        rollNumbers.forEach(el => {
            let target = parseFloat(el.getAttribute('data-target'));
            let decimals = el.getAttribute('data-decimal') ? parseInt(el.getAttribute('data-decimal')) : 0;
            
            if (target > 0) {
                let count = 0;
                let speed = target / 50; 
                let timer = setInterval(() => {
                    count += speed;
                    if (count >= target) {
                        el.innerText = target.toLocaleString(undefined, {minimumFractionDigits: decimals, maximumFractionDigits: decimals});
                        clearInterval(timer);
                    } else {
                        el.innerText = count.toLocaleString(undefined, {minimumFractionDigits: decimals, maximumFractionDigits: decimals});
                    }
                }, 40);
            } else { el.innerText = "0"; }
        });

        // ✨ SAKTI 2: ANIMASI GRAFIK TUMBUH PELAN (SLOW MOTION)
        @if(isset($labels) && count($labels) > 0)
        const ctx = document.getElementById('stockChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($labels) !!},
                datasets: [
                    { 
                        label: 'Actual Stock', 
                        data: {!! json_encode($actStockData) !!}, 
                        backgroundColor: '#4e73df', 
                        borderRadius: 5,
                        borderWidth: 0
                    },
                    { 
                        label: 'Min Limit', 
                        data: {!! json_encode($minStockData) !!}, 
                        backgroundColor: '#e74a3b', 
                        barThickness: 8,
                        borderRadius: 20
                    }
                ]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                animation: {
                    duration: 4000, 
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: { display: true, position: 'top', labels: { font: { weight: 'bold' } } }
                },
                scales: { 
                    y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                    x: { grid: { display: false } }
                } 
            }
        });
        @endif
    });
</script>
@endsection