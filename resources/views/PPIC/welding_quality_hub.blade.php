@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root {
        --welding-gold: #f59e0b; --welding-danger: #ef4444; --welding-success: #10b981;
        --industrial-navy: #0f172a; --bg-soft: #f1f5f9;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-soft); color: #334155; }
    
    .heading-tech { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; color: var(--industrial-navy); }
    .yield-value { font-family: 'Orbitron'; font-weight: 900; }
    
    /* Stats Card */
    .stat-card { background: #fff; border-radius: 28px; padding: 25px; border: 1px solid #e2e8f0; transition: 0.3s; position: relative; overflow: hidden; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(245, 158, 11, 0.1); border-color: var(--welding-gold); }
    
    /* Industrial Table */
    .ledger-container { background: #fff; border-radius: 28px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
    .table-tech thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px; border-bottom: 2px solid #edf2f7; }
    .table-tech td { padding: 18px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 13px; }
    .row-clickable { cursor: pointer; transition: 0.2s; }
    .row-clickable:hover { background-color: #fff9eb !important; box-shadow: inset 4px 0 0 var(--welding-gold); }

    /* NG Ranking HUD */
    .ng-item { background: #fff1f2; border: 1px solid #fecaca; border-radius: 16px; padding: 15px; margin-bottom: 12px; transition: 0.3s; }
    .ng-item:hover { background: #ffe4e6; transform: scale(1.02); }

    /* Modal Styling */
    .modal-content { border-radius: 35px; border: none; overflow: hidden; }
    .batch-id { font-family: 'JetBrains Mono'; font-size: 11px; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; color: #475569; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    
    {{-- 🛰️ HEADER & FILTER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
        <div>
            <h1 class="heading-tech mb-1">Welding_Quality <span class="text-warning">Gate</span></h1>
            <p class="text-muted small font-weight-bold uppercase mb-0"><i class="fas fa-shield-halved text-warning mr-2"></i> Final Verification Analysis // Batch Mode</p>
        </div>
        <form action="" method="GET" class="bg-white p-2 rounded-pill shadow-sm border d-flex align-items-center mt-3 mt-md-0">
            <i class="fas fa-calendar-alt mx-3 text-warning"></i>
            <input type="date" name="date" class="border-0 font-weight-bold text-dark mr-3" value="{{ $date }}" onchange="this.form.submit()">
            <button type="submit" class="btn btn-warning rounded-pill px-4 font-weight-black text-white">SYNC_LIVE</button>
        </form>
    </div>

    {{-- 📊 SUMMARY STATS & CHART --}}
    <div class="row mb-5">
        <div class="col-lg-7">
            <div class="stat-card h-100">
                <h6 class="font-weight-black text-muted small uppercase mb-4">Welding Performance Trend</h6>
                <div id="performanceChart" style="min-height: 250px;"></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="row h-100">
                <div class="col-12 mb-4">
                    <div class="stat-card border-left-success" style="border-left: 8px solid var(--welding-success) !important;">
                        <small class="text-muted font-weight-bold uppercase d-block mb-1">Passed (OK Goods)</small>
                        <div class="yield-value text-success h2 mb-0">{{ number_format($summary->total_ok ?? 0) }} <small class="h6">PCS</small></div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="stat-card bg-dark text-white border-0">
                        <small class="text-warning font-weight-bold uppercase d-block mb-1">Welding Yield Rate</small>
                        @php 
                            $totalInput = ($summary->total_ok ?? 0) + ($summary->total_ng ?? 0);
                            $yield = $totalInput > 0 ? round(($summary->total_ok / $totalInput) * 100, 1) : 0;
                        @endphp
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
            <div class="card border-0 shadow-sm p-4" style="border-radius: 28px;">
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
                    <p class="font-weight-bold text-muted small uppercase">No Defects Logged Today</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- 📋 LIVE MONITOR TABLE --}}
        <div class="col-lg-8">
            <h6 class="font-weight-black text-muted small uppercase mb-3"><i class="fas fa-microchip mr-2 text-warning"></i> Station Verification Registry</h6>
            <div class="ledger-container">
                <div class="table-responsive">
                    <table class="table table-tech text-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-left pl-4">Part Identification</th>
                                <th>Station</th>
                                <th>Result OK</th>
                                <th>Result NG</th>
                                <th class="text-right pr-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $d)
                            <tr class="row-clickable" onclick="showDrilldown({{ json_encode($d->part_no) }}, {{ json_encode($d->batches ?? []) }})">
                                <td class="text-left pl-4">
                                    <div class="text-dark font-weight-black">{{ $d->part_no }}</div>
                                    <small class="text-muted font-weight-bold">Click for batch audit</small>
                                </td>
                                <td><span class="badge badge-dark font-weight-bold">{{ $d->line_code }}</span></td>
                                <td class="text-success font-weight-black" style="font-size: 16px;">{{ number_format($d->qty_ok) }}</td>
                                <td class="text-danger font-weight-black" style="font-size: 16px;">{{ number_format($d->qty_ng) }}</td>
                                <td class="text-right pr-4">
                                    <button class="btn btn-outline-warning btn-sm rounded-pill px-3 font-weight-bold">DRILLDOWN</button>
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

{{-- 🤖 MODAL: HOLOGRAPHIC BATCH DRILLDOWN --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalDrilldown" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl">
            <div class="modal-header bg-warning text-dark p-4 border-0">
                <h5 class="modal-title font-weight-black uppercase" style="font-family: 'Orbitron';" id="drillTitle">Batch_Audit_Trace</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-hover mb-0 text-center">
                    <thead class="bg-light small font-weight-black uppercase">
                        <tr>
                            <th class="text-left pl-4">Production ID</th>
                            <th>Station</th>
                            <th>Taken</th>
                            <th>OK Released</th>
                            <th>NG Reject</th>
                        </tr>
                    </thead>
                    <tbody id="drillBody">
                        {{-- JS Content --}}
                    </tbody>
                </table>
            </div>
            <div class="modal-footer bg-light border-0 p-3">
                <button class="btn btn-dark btn-block font-weight-bold py-3 rounded-2xl" data-dismiss="modal">CLOSE AUDIT DATA</button>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Performance Chart (ApexCharts)
    var options = {
        series: [{ name: 'Yield Rate %', data: [98.5, 99.2, {{ $yield }}, 97.8, 99.5] }],
        chart: { type: 'area', height: 250, toolbar: { show: false }, animations: { enabled: true } },
        colors: ['#f59e0b'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0 } },
        dataLabels: { enabled: false },
        xaxis: { categories: ['D-4', 'D-3', 'D-2', 'D-1', 'Today'], labels: { style: { fontWeight: 700 } } }
    };
    new ApexCharts(document.querySelector("#performanceChart"), options).render();

    // 2. Drilldown Function
    function showDrilldown(partNo, batches) {
        document.getElementById('drillTitle').innerText = "AUDIT TRACE: " + partNo;
        const body = document.getElementById('drillBody');
        body.innerHTML = '';

        if (batches.length === 0) {
            body.innerHTML = '<tr><td colspan="5" class="py-5 text-muted">-- NO LIVE BATCH DATA FOUND --</td></tr>';
        } else {
            batches.forEach(b => {
                body.innerHTML += `
                    <tr>
                        <td class="text-left pl-4">
                            <span class="batch-id">${b.no_produksi}</span>
                        </td>
                        <td><span class="badge badge-dark font-weight-bold">${b.kode_line || 'W-GEN'}</span></td>
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