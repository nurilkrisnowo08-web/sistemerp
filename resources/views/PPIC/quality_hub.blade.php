@extends('layout.admin')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root { --ind-blue: #4361ee; --ind-navy: #0f172a; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
    
    .heading-tech { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; color: var(--ind-navy); }
    .stat-card { background: #fff; border-radius: 25px; padding: 25px; border: none; transition: 0.3s; box-shadow: 0 10px 25px rgba(0,0,0,0.02); }
    .yield-value { font-family: 'Orbitron'; font-weight: 900; line-height: 1; }
    
    .table-tech td { font-size: 13px; font-weight: 700; vertical-align: middle; padding: 18px !important; }
    .row-clickable { cursor: pointer; transition: 0.2s; }
    .row-clickable:hover { background-color: #f0f7ff !important; box-shadow: inset 4px 0 0 var(--ind-blue); }

    .batch-pill { font-family: 'JetBrains Mono'; font-size: 10px; background: #f1f5f9; color: #475569; padding: 3px 10px; border-radius: 6px; font-weight: 700; border: 1px solid #e2e8f0; }
    .ng-badge { background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 8px; font-size: 11px; font-weight: 800; border: 1px solid #fecaca; }

    /* Modal Styling */
    .modal-content { border-radius: 35px; border: none; overflow: hidden; }
    .chart-container { background: #f8fafc; border-radius: 25px; padding: 20px; border: 1px solid #e2e8f0; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    
    {{-- 🛰️ HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="heading-tech mb-1">Stamping <span class="text-primary">Quality_Hub</span></h1>
            <p class="text-muted small font-weight-bold uppercase mb-0">PT ASALTA MANDIRI AGUNG // BATCH_ANALYSIS_MODE</p>
        </div>
        <div class="d-flex align-items-center">
            <form action="" method="GET" class="bg-white p-2 rounded-pill shadow-sm border d-flex align-items-center mr-3">
                <i class="fas fa-calendar-alt mx-3 text-primary"></i>
                <input type="date" name="date" class="border-0 font-weight-bold mr-2" value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <a href="{{ route('ppic.mps.index') }}" class="btn btn-dark rounded-pill px-4 font-weight-bold shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> BACK_TO_MPS
            </a>
        </div>
    </div>

    {{-- 📊 STATS CARDS --}}
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="stat-card border-left border-success" style="border-left-width: 8px !important;">
                <small class="text-muted font-weight-bold uppercase d-block mb-1">Passed Good (OK)</small>
                <div class="yield-value text-success h2 mb-0">{{ number_format($sumStamping->total_ok ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card border-left border-danger" style="border-left-width: 8px !important;">
                <small class="text-muted font-weight-bold uppercase d-block mb-1">Total Rejected (NG)</small>
                <div class="yield-value text-danger h2 mb-0">{{ number_format($sumStamping->total_ng ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card bg-dark text-white">
                <small class="text-info font-weight-bold uppercase d-block mb-1">Overall Yield Rate</small>
                @php $totalS = ($sumStamping->total_ok ?? 0) + ($sumStamping->total_ng ?? 0); @endphp
                <div class="yield-value text-warning h2 mb-0">{{ $totalS > 0 ? round(($sumStamping->total_ok / $totalS) * 100, 1) : 0 }}%</div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- 📋 MAIN LOG TABLE --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 25px;">
                <div class="bg-light p-4 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-black mb-0 uppercase"><i class="fas fa-stream mr-2 text-primary"></i> Stamping_Production_Log</h6>
                    <span class="badge badge-dark rounded-pill px-3">{{ count($detailStamping) }} Entries</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 text-center">
                        <thead class="bg-white">
                            <tr class="small text-muted font-weight-black uppercase">
                                <th class="text-left pl-4">Part Identification</th>
                                <th>Station</th>
                                <th>Shift</th>
                                <th>OK</th>
                                <th>NG</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detailStamping as $ds)
                            <tr class="row-clickable" onclick="showDrilldown({{ json_encode($ds->part_no) }}, {{ json_encode($ds->batches ?? []) }})">
                                <td class="text-left pl-4">
                                    <div class="text-primary font-weight-black">{{ $ds->part_no }}</div>
                                    <small class="text-muted">Click to drill down batches</small>
                                </td>
                                <td><span class="badge badge-outline-dark font-weight-bold">{{ $ds->line_code }}</span></td>
                                <td><span class="badge {{ $ds->shift == 'Pagi' ? 'badge-warning' : 'badge-dark' }} px-3">{{ strtoupper($ds->shift) }}</span></td>
                                <td class="text-success font-weight-black" style="font-size: 16px;">{{ number_format($ds->qty_ok) }}</td>
                                <td class="text-danger font-weight-black" style="font-size: 16px;">{{ number_format($ds->qty_ng) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 📉 NG BREAKDOWN --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 25px;">
                <h6 class="font-weight-black text-muted uppercase small mb-4"><i class="fas fa-bug mr-2 text-danger"></i> Defect Analysis</h6>
                @forelse($ngStamping as $index => $ns)
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded-xl border">
                    <span class="font-weight-bold text-dark"><span class="text-primary mr-2">#{{$index+1}}</span> {{ strtoupper($ns->ng_type) }}</span>
                    <span class="ng-badge">{{ $ns->total }} PCS</span>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success opacity-25 mb-3"></i>
                    <p class="small font-weight-bold text-muted">ZERO DEFECTS REPORTED</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- 🤖 MODAL DRILLDOWN (DENGAN GRAFIK) --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalDrilldown" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 35px;">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title font-weight-black uppercase" style="font-family: 'Orbitron';" id="drilldownTitle">BATCH_DRILLDOWN</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    {{-- Grafik Donut --}}
                    <div class="col-lg-5 mb-4">
                        <div class="chart-container text-center h-100">
                            <h6 class="font-weight-black text-muted small uppercase mb-4">Performance Composition</h6>
                            <div id="batchDonutChart"></div>
                            <div class="row mt-3">
                                <div class="col-4 border-right"><small class="d-block text-muted font-weight-bold">TOTAL IN</small><h5 id="chartLabelIn" class="font-weight-black mb-0">0</h5></div>
                                <div class="col-4 border-right"><small class="d-block text-success font-weight-bold">GOOD OK</small><h5 id="chartLabelOk" class="text-success font-weight-black mb-0">0</h5></div>
                                <div class="col-4"><small class="d-block text-danger font-weight-bold">REJECT NG</small><h5 id="chartLabelNg" class="text-danger font-weight-black mb-0">0</h5></div>
                            </div>
                        </div>
                    </div>
                    {{-- Tabel Detail --}}
                    <div class="col-lg-7">
                        <div class="table-responsive border rounded-xl overflow-hidden shadow-sm">
                            <table class="table table-hover mb-0 text-center">
                                <thead class="bg-light small font-weight-black uppercase">
                                    <tr>
                                        <th class="text-left pl-4">No Produksi</th>
                                        <th>In</th>
                                        <th>OK</th>
                                        <th>NG</th>
                                    </tr>
                                </thead>
                                <tbody id="drilldownBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 p-3">
                <button class="btn btn-dark btn-block font-weight-bold py-3 rounded-2xl" data-dismiss="modal">CLOSE AUDIT DATA</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Inisialisasi Chart Global agar bisa diupdate
    let donutChart = null;

    function showDrilldown(partNo, batches) {
        document.getElementById('drilldownTitle').innerText = "AUDIT TRACE: " + partNo;
        const body = document.getElementById('drilldownBody');
        body.innerHTML = '';

        let totalIn = 0;
        let totalOk = 0;
        let totalNg = 0;

        if (batches.length === 0) {
            body.innerHTML = '<tr><td colspan="4" class="py-5 text-muted">-- NO ACTIVE BATCHES FOUND --</td></tr>';
        } else {
            batches.forEach(b => {
                let qIn = parseInt(b.qty_ambil_pcs) || 0;
                let qOk = parseInt(b.qty_hasil_ok) || 0;
                let qNg = parseInt(b.qty_hasil_ng) || 0;

                totalIn += qIn;
                totalOk += qOk;
                totalNg += qNg;

                body.innerHTML += `
                    <tr>
                        <td class="text-left pl-4 font-weight-bold">
                            <span class="batch-pill">${b.no_produksi}</span>
                        </td>
                        <td class="font-weight-black text-muted">${qIn.toLocaleString()}</td>
                        <td class="text-success font-weight-black">${qOk.toLocaleString()}</td>
                        <td class="text-danger font-weight-black">${qNg.toLocaleString()}</td>
                    </tr>
                `;
            });
        }

        // Update Label HUD di Modal
        document.getElementById('chartLabelIn').innerText = totalIn.toLocaleString();
        document.getElementById('chartLabelOk').innerText = totalOk.toLocaleString();
        document.getElementById('chartLabelNg').innerText = totalNg.toLocaleString();

        // Render atau Update Grafik Donut
        renderDonut(totalOk, totalNg);

        $('#modalDrilldown').modal('show');
    }

    function renderDonut(ok, ng) {
        const options = {
            series: [ok, ng],
            chart: { type: 'donut', height: 280, animations: { enabled: true, easing: 'easeinout', speed: 800 } },
            labels: ['OK Goods', 'NG Reject'],
            colors: ['#10b981', '#ef4444'],
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '80%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '12px', fontWeight: 800, color: '#64748b' },
                            value: { show: true, fontSize: '24px', fontWeight: 900, color: '#0f172a', formatter: (val) => val.toLocaleString() },
                            total: {
                                show: true, label: 'TOTAL YIELD', fontSize: '10px', fontWeight: 900, color: '#4361ee',
                                formatter: function (w) {
                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    if(total === 0) return '0%';
                                    return ((ok / total) * 100).toFixed(1) + '%';
                                }
                            }
                        }
                    }
                }
            },
            legend: { position: 'bottom', fontWeight: 700 }
        };

        if (donutChart) {
            donutChart.updateOptions(options);
        } else {
            donutChart = new ApexCharts(document.querySelector("#batchDonutChart"), options);
            donutChart.render();
        }
    }
</script>
@endsection