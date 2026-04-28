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

    /* ID Produksi Clickable Style */
    .id-link { 
        font-family: 'JetBrains Mono'; font-size: 11px; background: #eff6ff; color: var(--ind-blue); 
        padding: 5px 12px; border-radius: 8px; font-weight: 800; border: 1px solid #dbeafe;
        cursor: pointer; transition: 0.2s; display: inline-block;
    }
    .id-link:hover { background: var(--ind-blue); color: #fff; transform: scale(1.05); text-decoration: none; }

    .ng-badge { background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 8px; font-size: 11px; font-weight: 800; border: 1px solid #fecaca; }

    /* Modal Styling */
    .modal-content { border-radius: 35px; border: none; overflow: hidden; }
    .chart-container { background: #f8fafc; border-radius: 25px; padding: 20px; border: 1px solid #e2e8f0; }

    /* Z-Index Fix for Double Modal */
    #modalBatchAnalysis { z-index: 1060 !important; }
    .modal-backdrop.show:nth-of-type(even) { z-index: 1059 !important; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    
    {{-- 🛰️ HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="heading-tech mb-1">Quality <span class="text-primary">Intelligence</span></h1>
            <p class="text-muted small font-weight-bold uppercase mb-0">PT ASALTA MANDIRI AGUNG // AUDIT_TRACE_SYSTEM</p>
        </div>
        <div class="d-flex align-items-center">
            <form action="" method="GET" class="bg-white p-2 rounded-pill shadow-sm border d-flex align-items-center mr-3">
                <i class="fas fa-calendar-alt mx-3 text-primary"></i>
                <input type="date" name="date" class="border-0 font-weight-bold mr-2" value="{{ $date }}" onchange="this.form.submit()">
            </form>
        </div>
    </div>

    {{-- 📊 STATS CARDS --}}
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="stat-card border-left border-success" style="border-left-width: 8px !important;">
                <small class="text-muted font-weight-bold uppercase d-block mb-1">Passed (OK)</small>
                <div class="yield-value text-success h2 mb-0">{{ number_format($sumStamping->total_ok ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card border-left border-danger" style="border-left-width: 8px !important;">
                <small class="text-muted font-weight-bold uppercase d-block mb-1">Rejected (NG)</small>
                <div class="yield-value text-danger h2 mb-0">{{ number_format($sumStamping->total_ng ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card bg-dark text-white text-center">
                <small class="text-info font-weight-bold uppercase d-block mb-1">Overall Yield Rate</small>
                @php $totalS = ($sumStamping->total_ok ?? 0) + ($sumStamping->total_ng ?? 0); @endphp
                <div class="yield-value text-warning h2 mb-0">{{ $totalS > 0 ? round(($sumStamping->total_ok / $totalS) * 100, 1) : 0 }}%</div>
            </div>
        </div>
    </div>

    {{-- MAIN TABLE --}}
    <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 25px;">
        <div class="table-responsive">
            <table class="table table-hover mb-0 text-center">
                <thead class="bg-light">
                    <tr class="small text-muted font-weight-black uppercase">
                        <th class="text-left pl-5">Part Identification</th>
                        <th>Station</th>
                        <th class="text-success">OK</th>
                        <th class="text-danger">NG</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailStamping as $ds)
                    <tr>
                        <td class="text-left pl-5">
                            <div class="text-dark font-weight-black" style="font-size: 15px;">{{ $ds->part_no }}</div>
                        </td>
                        <td><span class="badge badge-outline-dark font-weight-bold">{{ $ds->line_code }}</span></td>
                        <td class="text-success font-weight-black h5 mb-0">{{ number_format($ds->qty_ok) }}</td>
                        <td class="text-danger font-weight-black h5 mb-0">{{ number_format($ds->qty_ng) }}</td>
                        <td>
                            <button class="btn btn-primary rounded-pill px-4 font-weight-bold btn-sm shadow-sm" 
                                    onclick="showDrilldown('{{ $ds->part_no }}', {{ json_encode($ds->batches ?? []) }})">
                                VIEW_DETAIL
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🤖 MODAL 1: AUDIT TRACE (DAFTAR BATCH) --}}
<div class="modal fade animate__animated animate__fadeInDown" id="modalDrilldown" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title font-weight-black uppercase" style="font-family: 'Orbitron';" id="drilldownTitle">BATCH_DRILLDOWN</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-lg-5 mb-4">
                        <div class="chart-container text-center h-100">
                            <h6 class="font-weight-black text-muted small uppercase mb-4">Summary Performance</h6>
                            <div id="partSummaryDonut"></div>
                            <div class="row mt-3">
                                <div class="col-6 border-right text-success"><small class="font-weight-bold">TOTAL OK</small><h4 id="lblTotalOk" class="font-weight-black mb-0">0</h4></div>
                                <div class="col-6 text-danger"><small class="font-weight-bold">TOTAL NG</small><h4 id="lblTotalNg" class="font-weight-black mb-0">0</h4></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="table-responsive border rounded-xl overflow-hidden">
                            <table class="table table-hover mb-0 text-center">
                                <thead class="bg-light small font-weight-black uppercase">
                                    <tr>
                                        <th class="text-left pl-4">No Produksi (Click for NG Detail)</th>
                                        <th>In</th>
                                        <th class="text-success">OK</th>
                                        <th class="text-danger">NG</th>
                                    </tr>
                                </thead>
                                <tbody id="drilldownBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 📊 MODAL 2: BATCH NG ANALYSIS (CHART DONUT SPESIFIK) --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalBatchAnalysis" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-2xl border-0">
            <div class="modal-header bg-dark text-white p-4">
                <h6 class="modal-title font-weight-bold uppercase" id="batchAnalysisTitle">BATCH_REJECT_BREAKDOWN</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="chart-container">
                    <div id="batchDonutChart"></div>
                </div>
                <div id="ngDetailList" class="mt-4 text-left">
                    {{-- Text Detail NG muncul di sini via JS --}}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let summaryChart = null;
    let detailChart = null;

    // FUNGSI MODAL 1: Daftar Batch
    function showDrilldown(partNo, batches) {
        document.getElementById('drilldownTitle').innerText = "AUDIT TRACE: " + partNo;
        const body = document.getElementById('drilldownBody');
        body.innerHTML = '';

        let tOk = 0, tNg = 0;

        batches.forEach(b => {
            let qIn = parseInt(b.qty_ambil_pcs || b.qty_masuk) || 0;
            let qOk = parseInt(b.qty_hasil_ok || b.qty_ok) || 0;
            let qNg = parseInt(b.qty_hasil_ng || b.qty_ng) || 0;
            let id = b.no_produksi || b.no_produksi_stamping || b.no_produksi_welding;

            tOk += qOk; tNg += qNg;

            body.innerHTML += `
                <tr>
                    <td class="text-left pl-4">
                        <span class="id-link" onclick="loadBatchAnalysis('${id}', ${qOk}, ${qNg})">
                            ${id}
                        </span>
                    </td>
                    <td class="font-weight-bold text-muted">${qIn}</td>
                    <td class="text-success font-weight-black">${qOk}</td>
                    <td class="text-danger font-weight-black">${qNg}</td>
                </tr>
            `;
        });

        document.getElementById('lblTotalOk').innerText = tOk.toLocaleString();
        document.getElementById('lblTotalNg').innerText = tNg.toLocaleString();
        
        renderSummaryDonut(tOk, tNg);
        $('#modalDrilldown').modal('show');
    }

    // FUNGSI MODAL 2: Rincian NG Per Batch
    function loadBatchAnalysis(noProd, ok, ng) {
        document.getElementById('batchAnalysisTitle').innerText = "ANALYSIS: " + noProd;
        
        // Ambil data rincian NG lewat AJAX
        fetch(`/ppic/get-batch-ng-details/${noProd}`)
            .then(res => res.json())
            .then(data => {
                const labels = ['GOOD OK'];
                const values = [ok];
                let detailHtml = '<p class="small font-weight-black text-muted uppercase mb-2">Defect Distribution:</p>';

                if(data.length > 0) {
                    data.forEach(item => {
                        labels.push(item.ng_type.toUpperCase());
                        values.push(parseInt(item.qty));
                        detailHtml += `
                            <div class="d-flex justify-content-between border-bottom py-2 small font-weight-bold">
                                <span class="text-danger"><i class="fas fa-caret-right mr-2"></i>${item.ng_type.toUpperCase()}</span>
                                <span class="font-weight-black">${item.qty} PCS</span>
                            </div>`;
                    });
                } else if(ng > 0) {
                    labels.push('UNSPECIFIED NG');
                    values.push(ng);
                    detailHtml += `<div class="text-center text-muted py-3">DETAIL LOG NOT FOUND</div>`;
                } else {
                    detailHtml += `<div class="text-center text-success font-weight-bold py-3">ZERO DEFECT BATCH</div>`;
                }

                document.getElementById('ngDetailList').innerHTML = detailHtml;
                
                // Tampilkan modal 2
                $('#modalBatchAnalysis').modal('show');
                
                // Render Chart dengan delay agar ukuran pas
                setTimeout(() => renderDetailDonut(labels, values), 300);
            });
    }

    // Render Chart Modal 1 (Ringkasan Part)
    function renderSummaryDonut(ok, ng) {
        const options = {
            series: [ok, ng],
            chart: { type: 'donut', height: 280 },
            labels: ['Good OK', 'Total NG'],
            colors: ['#10b981', '#ef4444'],
            plotOptions: { pie: { donut: { size: '75%', labels: { show: true, total: { show: true, label: 'YIELD', formatter: () => ((ok/(ok+ng))*100).toFixed(1) + '%' } } } } },
            legend: { position: 'bottom' }
        };
        if (summaryChart) summaryChart.updateOptions(options);
        else { summaryChart = new ApexCharts(document.querySelector("#partSummaryDonut"), options); summaryChart.render(); }
    }

    // Render Chart Modal 2 (Detail Batch)
    function renderDetailDonut(labels, values) {
        const options = {
            series: values,
            chart: { type: 'donut', height: 320 },
            labels: labels,
            colors: ['#10b981', '#ef4444', '#f59e0b', '#3b82f6', '#8b5cf6', '#ec4899'],
            stroke: { width: 0 },
            plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'TOTAL IN' } } } } },
            legend: { position: 'bottom', fontWeight: 700 }
        };
        if (detailChart) detailChart.updateOptions(options);
        else { detailChart = new ApexCharts(document.querySelector("#batchDonutChart"), options); detailChart.render(); }
    }
</script>
@endsection