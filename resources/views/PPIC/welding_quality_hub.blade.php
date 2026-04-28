@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root {
        --welding-gold: #f59e0b; --welding-success: #10b981; --welding-danger: #ef4444;
        --industrial-navy: #0f172a; --bg-soft: #f8fafc;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-soft); color: #334155; }
    
    .heading-tech { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; color: var(--industrial-navy); }
    .stat-card { background: #fff; border-radius: 28px; padding: 25px; border: 1px solid #e2e8f0; transition: 0.3s; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
    .stat-card:hover { transform: translateY(-5px); border-color: var(--welding-gold); box-shadow: 0 15px 35px rgba(245, 158, 11, 0.1); }
    
    /* Industrial Table Ledger */
    .ledger-container { background: #fff; border-radius: 28px; border: 1px solid #e2e8f0; overflow: hidden; }
    .table-tech thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px; border-bottom: 2px solid #edf2f7; }
    .table-tech td { padding: 18px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 13px; }
    .row-clickable { cursor: pointer; transition: 0.2s; }
    .row-clickable:hover { background-color: #fff9eb !important; box-shadow: inset 4px 0 0 var(--welding-gold); }

    /* Performance HUD */
    .yield-value { font-family: 'Orbitron'; font-weight: 900; line-height: 1; }
    .ng-item { background: #fff1f2; border: 1px solid #fecaca; border-radius: 16px; padding: 15px; margin-bottom: 12px; transition: 0.3s; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    
    {{-- 🛰️ HEADER & CONTROL --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-tech mb-1">Welding_Quality <span class="text-warning">Hub</span></h1>
            <p class="text-muted small font-weight-bold uppercase mb-0"><i class="fas fa-shield-halved text-warning mr-2"></i> Quality Gate Verification // Audit Registry</p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
            <form action="" method="GET" class="bg-white p-2 rounded-pill shadow-sm border d-flex align-items-center mr-3">
                <i class="fas fa-calendar-alt mx-3 text-warning"></i>
                <input type="date" name="date" class="border-0 font-weight-bold text-dark" value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <button onclick="window.location.reload()" class="btn btn-warning rounded-pill px-4 font-weight-black text-white shadow-lg">SYNC_LIVE</button>
        </div>
    </div>

    {{-- 📊 SUMMARY WIDGETS --}}
    @php 
        $totalInput = ($summary->total_ok ?? 0) + ($summary->total_ng ?? 0);
        $yield = $totalInput > 0 ? round(($summary->total_ok / $totalInput) * 100, 1) : 0;
    @endphp
    <div class="row mb-5">
        <div class="col-lg-7 mb-4">
            <div class="stat-card h-100">
                <h6 class="font-weight-black text-muted small uppercase mb-4"><i class="fas fa-chart-line mr-2"></i> Quality Stability Trend</h6>
                <div id="performanceChart"></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="stat-card border-left-success" style="border-left: 8px solid var(--welding-success) !important;">
                        <small class="text-muted font-weight-bold uppercase d-block mb-1">Good Output Today</small>
                        <div class="yield-value text-success h2 mb-0">{{ number_format($summary->total_ok ?? 0) }} <small class="h6">PCS</small></div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="stat-card bg-dark text-white border-0 shadow-xl">
                        <small class="text-warning font-weight-bold uppercase d-block mb-1">WELDING YIELD RATE</small>
                        <div class="yield-value text-warning h1 mb-0">{{ $yield }}%</div>
                        <div class="progress mt-3" style="height: 6px; background: rgba(255,255,255,0.1); border-radius: 10px;">
                            <div class="progress-bar bg-warning" style="width: {{ $yield }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- 📉 NG RANKING --}}
        <div class="col-lg-4 mb-4">
            <h6 class="font-weight-black text-muted small uppercase mb-3"><i class="fas fa-fire mr-2 text-danger"></i> Defect Breakdown Ranking</h6>
            <div class="stat-card">
                @forelse($ngRanking as $index => $ng)
                <div class="ng-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge badge-danger rounded-circle mr-2" style="width:25px; height:25px; padding-top:6px;">{{ $index+1 }}</span>
                        <span class="font-weight-black text-dark small">{{ strtoupper($ng->ng_type) }}</span>
                    </div>
                    <div class="font-weight-black text-danger" style="font-family: 'JetBrains Mono'; font-size: 16px;">{{ $ng->total }} <small>PCS</small></div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success opacity-25 mb-3"></i>
                    <p class="font-weight-bold text-muted small uppercase">Zero Defects Detected Today</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- 📋 LIVE MONITOR --}}
        <div class="col-lg-8">
            <h6 class="font-weight-black text-muted small uppercase mb-3"><i class="fas fa-microchip mr-2 text-warning"></i> Station Verification Registry</h6>
            <div class="ledger-container shadow-sm">
                <div class="table-responsive">
                    <table class="table table-tech text-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-left pl-4">Part Identification</th>
                                <th>Station</th>
                                <th>Actual OK</th>
                                <th>Actual NG</th>
                                <th class="text-right pr-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $d)
                            <tr class="row-clickable" onclick="showDrilldown({{ json_encode($d->part_no) }}, {{ json_encode($d->batches ?? []) }})">
                                <td class="text-left pl-4">
                                    <div class="text-dark font-weight-black">{{ $d->part_no }}</div>
                                    <small class="text-muted font-weight-bold">Batch Audit Required</small>
                                </td>
                                <td><span class="badge badge-dark font-weight-bold" style="font-family:'JetBrains Mono';">{{ $d->line_code }}</span></td>
                                <td class="text-success font-weight-black" style="font-size: 16px;">{{ number_format($d->qty_ok) }}</td>
                                <td class="text-danger font-weight-black" style="font-size: 16px;">{{ number_format($d->qty_ng) }}</td>
                                <td class="text-right pr-4">
                                    <button class="btn btn-outline-warning btn-sm rounded-pill px-3 font-weight-bold">VIEW_DETAIL</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 🤖 MODAL: HOLOGRAPHIC DRILLDOWN --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalDrilldown" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-2xl">
            <div class="modal-header bg-warning text-dark p-4 border-0">
                <h5 class="modal-title font-weight-black uppercase" style="font-family: 'Orbitron';" id="drillTitle">Audit_Trace</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-hover mb-0 text-center">
                    <thead class="bg-light small font-weight-black uppercase">
                        <tr>
                            <th class="text-left pl-4">Production ID</th>
                            <th>Station</th>
                            <th>In</th>
                            <th>OK</th>
                            <th>NG</th>
                        </tr>
                    </thead>
                    <tbody id="drillBody"></tbody>
                </table>
            </div>
            <div class="modal-footer bg-light border-0">
                <button class="btn btn-dark btn-block font-weight-bold py-3 rounded-2xl" data-dismiss="modal">CLOSE AUDIT LOG</button>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Performance Trend Chart
    new ApexCharts(document.querySelector("#performanceChart"), {
        series: [{ name: 'Yield %', data: [98.2, 99.1, {{ $yield }}, 97.5, 99.8] }],
        chart: { type: 'area', height: 200, toolbar: { show: false }, animations: { enabled: true } },
        colors: ['#f59e0b'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0 } },
        dataLabels: { enabled: false },
        xaxis: { categories: ['D-4', 'D-3', 'Today', 'D+1', 'D+2'] }
    }).render();

    // 2. Drilldown Function
    function showDrilldown(partNo, batches) {
        document.getElementById('drillTitle').innerText = "AUDIT TRACE: " + partNo;
        const body = document.getElementById('drillBody');
        body.innerHTML = '';

        if (batches.length === 0) {
            body.innerHTML = '<tr><td colspan="5" class="py-5 text-muted italic">-- No live data for this part --</td></tr>';
        } else {
            batches.forEach(b => {
                body.innerHTML += `
                    <tr>
                        <td class="text-left pl-4"><span class="font-weight-bold text-primary" style="font-family:'JetBrains Mono'; font-size:11px;">${b.no_produksi}</span></td>
                        <td><span class="badge badge-outline-dark">${b.kode_line || 'W-GEN'}</span></td>
                        <td class="font-weight-black text-muted">${parseInt(b.qty_masuk).toLocaleString()}</td>
                        <td class="text-success font-weight-black">${parseInt(b.qty_ok).toLocaleString()}</td>
                        <td class="text-danger font-weight-black">${parseInt(b.qty_ng).toLocaleString()}</td>
                    </tr>
                `;
            });
        }
        $('#modalDrilldown').modal('show');
    }
</script>
@endsection