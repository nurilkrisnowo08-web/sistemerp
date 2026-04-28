@extends('layout.admin')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root { --ind-blue: #4361ee; --ind-navy: #0f172a; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
    .heading-tech { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; color: var(--ind-navy); }
    .row-clickable { cursor: pointer; transition: 0.2s; }
    .row-clickable:hover { background-color: #f0f7ff !important; }
    
    /* Style ID Produksi agar terlihat bisa diklik */
    .id-link { color: var(--ind-blue); text-decoration: underline; cursor: pointer; font-weight: 800; font-family: 'JetBrains Mono'; }
    .id-link:hover { color: #000; background: #fff3cd; }

    .modal-content { border-radius: 30px; border: none; }
    .chart-box { background: #f8fafc; border-radius: 20px; padding: 20px; border: 1px solid #e2e8f0; }
</style>

<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="heading-tech">Stamping <span class="text-primary">Audit_System</span></h1>
    </div>

    {{-- Tabel Utama --}}
    <div class="card border-0 shadow-sm" style="border-radius: 25px;">
        <div class="table-responsive">
            <table class="table table-hover mb-0 text-center">
                <thead class="bg-light">
                    <tr class="small text-muted font-weight-black uppercase">
                        <th class="text-left pl-4">Part Identification</th>
                        <th>Actual OK</th>
                        <th>Actual NG</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailStamping as $ds)
                    <tr>
                        <td class="text-left pl-4">
                            <div class="font-weight-black">{{ $ds->part_no }}</div>
                        </td>
                        <td class="text-success font-weight-bold">{{ number_format($ds->qty_ok) }}</td>
                        <td class="text-danger font-weight-bold">{{ number_format($ds->qty_ng) }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-warning rounded-pill px-4 font-weight-bold" 
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
<div class="modal fade" id="modalDrilldown" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark p-4">
                <h5 class="modal-title font-weight-black uppercase" id="drilldownTitle">AUDIT_TRACE</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-hover mb-0 text-center">
                    <thead class="bg-light small font-weight-black">
                        <tr>
                            <th class="text-left pl-4">Production ID (Click for Chart)</th>
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

{{-- 📊 MODAL 2: BATCH NG ANALYSIS (CHART DONUT) --}}
<div class="modal fade" id="modalBatchAnalysis" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-2xl">
            <div class="modal-header bg-dark text-white p-4">
                <h6 class="modal-title font-weight-bold uppercase" id="batchAnalysisTitle">BATCH_REJECT_BREAKDOWN</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="chart-box">
                    <div id="batchDonutChart"></div>
                </div>
                <div id="ngDetailList" class="mt-4 text-left">
                    {{-- Rincian Text NG Muncul di Sini --}}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let batchChart = null;

    // Fungsi Munculkan Modal 1 (Daftar Batch)
    function showDrilldown(partNo, batches) {
        document.getElementById('drilldownTitle').innerText = "AUDIT TRACE: " + partNo;
        const body = document.getElementById('drilldownBody');
        body.innerHTML = '';

        batches.forEach(b => {
            let qIn = parseInt(b.qty_ambil_pcs || b.qty_masuk) || 0;
            let qOk = parseInt(b.qty_hasil_ok || b.qty_ok) || 0;
            let qNg = parseInt(b.qty_hasil_ng || b.qty_ng) || 0;
            let noProd = b.no_produksi || b.no_produksi_stamping || b.no_produksi_welding;

            body.innerHTML += `
                <tr>
                    <td class="text-left pl-4">
                        <span class="id-link" onclick="loadBatchAnalysis('${noProd}', ${qOk}, ${qNg})">
                            ${noProd}
                        </span>
                    </td>
                    <td class="font-weight-bold">${qIn}</td>
                    <td class="text-success font-weight-bold">${qOk}</td>
                    <td class="text-danger font-weight-bold">${qNg}</td>
                </tr>
            `;
        });
        $('#modalDrilldown').modal('show');
    }

    // Fungsi Munculkan Modal 2 (Grafik Donut Per Batch)
    function loadBatchAnalysis(noProd, qtyOk, qtyNg) {
        document.getElementById('batchAnalysisTitle').innerText = "ANALYSIS: " + noProd;
        
        // Ambil data rincian NG lewat AJAX
        fetch(`/ppic/get-batch-ng-details/${noProd}`)
            .then(response => response.json())
            .then(data => {
                const labels = ['GOOD_OK'];
                const values = [qtyOk];
                let detailHtml = '<p class="small font-weight-black text-muted uppercase mb-2">Reject Breakdown:</p>';

                if(data.length > 0) {
                    data.forEach(item => {
                        labels.push(item.ng_type.toUpperCase());
                        values.push(parseInt(item.qty));
                        detailHtml += `
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span class="small font-weight-bold text-danger">${item.ng_type.toUpperCase()}</span>
                                <span class="font-weight-black">${item.qty} PCS</span>
                            </div>`;
                    });
                } else {
                    detailHtml += '<div class="text-center text-success py-2 font-weight-bold">NO REJECTS RECORDED</div>';
                }

                document.getElementById('ngDetailList').innerHTML = detailHtml;
                renderBatchChart(labels, values);
                $('#modalBatchAnalysis').modal('show');
            });
    }

    function renderBatchChart(labels, values) {
        const options = {
            series: values,
            chart: { type: 'donut', height: 350 },
            labels: labels,
            colors: ['#10b981', '#ef4444', '#f59e0b', '#3b82f6', '#8b5cf6', '#ec4899'],
            legend: { position: 'bottom', fontWeight: 700 },
            plotOptions: {
                pie: {
                    donut: {
                        size: '75%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'TOTAL IN',
                                formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                            }
                        }
                    }
                }
            }
        };

        if (batchChart) {
            batchChart.updateOptions(options);
        } else {
            batchChart = new ApexCharts(document.querySelector("#batchDonutChart"), options);
            batchChart.render();
        }
    }
</script>
@endsection