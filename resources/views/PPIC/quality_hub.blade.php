@extends('layout.admin')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Orbitron:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root { 
        --primary: #4361ee; --success: #10b981; --danger: #ef4444; 
        --warning: #f59e0b; --dark: #0f172a; --bg: #f8fafc; 
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg); color: var(--dark); }

    /* Header Styling */
    .header-section { margin-bottom: 2rem; }
    .heading-tech { font-family: 'Orbitron'; letter-spacing: -1px; text-transform: uppercase; font-weight: 900; font-size: 1.75rem; }
    
    /* Elegant Stat Cards */
    .stat-card { 
        background: #fff; border-radius: 24px; padding: 1.5rem; border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-label { font-size: 0.75rem; font-weight: 800; color: #64748b; letter-spacing: 1px; text-transform: uppercase; }
    .stat-value { font-family: 'Orbitron'; font-size: 1.8rem; font-weight: 900; margin-top: 0.5rem; }

    /* Main Table Styling */
    .main-card { background: #fff; border-radius: 30px; border: none; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05); }
    .table thead th { 
        background: #f1f5f9; text-transform: uppercase; font-size: 0.7rem; font-weight: 800; 
        letter-spacing: 0.5px; color: #475569; border: none; padding: 1.2rem;
    }
    .table td { padding: 1.2rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 0.85rem; }
    
    /* Buttons & Badges */
    .btn-action { 
        border-radius: 12px; font-weight: 800; font-size: 0.7rem; letter-spacing: 0.5px; 
        padding: 0.6rem 1.2rem; transition: 0.3s;
    }
    .id-pill { 
        font-family: 'JetBrains Mono'; background: #eff6ff; color: var(--primary); 
        padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer;
    }
    .id-pill:hover { background: var(--primary); color: #fff; }

    /* Modal Redesign */
    .modal-content { border-radius: 35px; border: none; overflow: hidden; }
    .modal-header { border-bottom: 1px solid #f1f5f9; padding: 1.5rem 2rem; }
    .chart-container { 
        background: #f8fafc; border-radius: 25px; padding: 2rem; 
        border: 1px solid #e2e8f0; min-height: 400px;
    }

    /* Modal Layering Fix */
    #modalBatchAnalysis { z-index: 1070 !important; }
    body.modal-open #modalDrilldown { filter: blur(2px); transition: 0.3s; }
</style>

<div class="container-fluid py-4 px-4">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center header-section">
        <div>
            <h1 class="heading-tech">Quality <span class="text-primary">Intelligence</span></h1>
            <p class="text-muted small font-weight-bold mb-0"><i class="fas fa-microchip mr-2"></i> STAMPING_CONTROL_SYSTEM // V5.0</p>
        </div>
        <div class="d-flex align-items-center">
            <form action="" method="GET" class="mr-3">
                <input type="date" name="date" class="form-control rounded-pill border-0 shadow-sm font-weight-bold px-4" value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <a href="{{ route('ppic.mps.index') }}" class="btn btn-dark rounded-pill px-4 font-weight-bold shadow-sm">
                <i class="fas fa-th-large mr-2"></i> MASTER_PLAN
            </a>
        </div>
    </div>

    {{-- STATS AREA --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Production Passed</div>
                <div class="stat-value text-success">{{ number_format($sumStamping->total_ok ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Total Rejected</div>
                <div class="stat-value text-danger">{{ number_format($sumStamping->total_ng ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-6">
            @php $totalS = ($sumStamping->total_ok ?? 0) + ($sumStamping->total_ng ?? 0); @endphp
            @php $rate = $totalS > 0 ? round(($sumStamping->total_ok / $totalS) * 100, 1) : 0; @endphp
            <div class="stat-card bg-dark text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label text-info">Yield Accuracy</div>
                        <div class="stat-value text-warning">{{ $rate }}%</div>
                    </div>
                    <div class="w-50">
                        <div class="progress rounded-pill" style="height: 10px; background: rgba(255,255,255,0.1);">
                            <div class="progress-bar bg-info" style="width: {{ $rate }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN TABLE --}}
    <div class="main-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Part Identification</th>
                        <th>Station</th>
                        <th class="text-success">Passed OK</th>
                        <th class="text-danger">Failed NG</th>
                        <th>Audit Trace</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailStamping as $ds)
                    <tr>
                        <td class="text-left pl-5">
                            <div class="font-weight-bold text-dark" style="font-size: 1rem;">{{ $ds->part_no }}</div>
                            <small class="text-muted font-weight-bold">UNIT_VERIFICATION</small>
                        </td>
                        <td><span class="badge badge-light border font-weight-bold px-3 py-2 rounded-lg">{{ $ds->line_code }}</span></td>
                        <td class="text-success font-weight-black" style="font-size: 1.1rem;">{{ number_format($ds->qty_ok) }}</td>
                        <td class="text-danger font-weight-black" style="font-size: 1.1rem;">{{ number_format($ds->qty_ng) }}</td>
                        <td>
                            <button class="btn btn-primary btn-action shadow-sm" onclick="openAuditTrace('{{ $ds->part_no }}', {{ json_encode($ds->batches ?? []) }})">
                                <i class="fas fa-search-plus mr-2"></i> ANALYZE
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL 1: BATCH LIST --}}
<div class="modal fade animate__animated animate__fadeIn" id="modalDrilldown" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-2xl">
            <div class="modal-header bg-white">
                <h5 class="modal-title font-weight-black uppercase" id="lblAuditTitle">Audit_Trace</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="small font-weight-black">
                        <tr class="text-center">
                            <th class="text-left pl-4">BATCH_ID</th>
                            <th>INPUT</th>
                            <th class="text-success">OK</th>
                            <th class="text-danger">NG</th>
                        </tr>
                    </thead>
                    <tbody id="auditBody"></tbody>
                </table>
            </div>
            <div class="modal-footer bg-light p-3">
                <button class="btn btn-secondary btn-block font-weight-bold py-3 rounded-xl" data-dismiss="modal">CLOSE_SESSION</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: DONUT ANALYSIS --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalBatchAnalysis" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-2xl">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title font-weight-bold" id="lblAnalysisTitle">BATCH_ANALYSIS</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <div class="chart-container">
                    <div id="donutChartArea"></div>
                    <div id="ngListArea" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let apexDonut = null;

    function openAuditTrace(partNo, batches) {
        document.getElementById('lblAuditTitle').innerHTML = `<i class="fas fa-clipboard-check mr-2 text-primary"></i> ${partNo}`;
        const body = document.getElementById('auditBody');
        body.innerHTML = '';

        batches.forEach(b => {
            let qIn = parseInt(b.qty_ambil_pcs || b.qty_masuk || 0);
            let qOk = parseInt(b.qty_hasil_ok || b.qty_ok || 0);
            let qNg = parseInt(b.qty_hasil_ng || b.qty_ng || 0);
            let id = b.no_produksi || b.no_produksi_stamping || b.no_produksi_welding;

            body.innerHTML += `
                <tr class="text-center">
                    <td class="text-left pl-4">
                        <span class="id-pill" onclick="openBatchAnalysis('${id}', ${qOk}, ${qNg})">
                            <i class="fas fa-fingerprint mr-1"></i> ${id}
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

    function openBatchAnalysis(id, ok, ng) {
        document.getElementById('lblAnalysisTitle').innerText = "UNIT_ANALYSIS: " + id;
        
        fetch(`/ppic/get-batch-ng-details/${id}`)
            .then(res => res.json())
            .then(data => {
                const labels = ['GOOD OK'];
                const values = [ok];
                let listHtml = '<hr><p class="small font-weight-black text-muted mb-2">DEFECT_DISTRIBUTION:</p>';

                if(data.length > 0) {
                    data.forEach(item => {
                        labels.push(item.ng_type.toUpperCase());
                        values.push(parseInt(item.qty));
                        listHtml += `<div class="d-flex justify-content-between mb-1 small font-weight-bold text-danger">
                                        <span><i class="fas fa-caret-right mr-2"></i>${item.ng_type}</span>
                                        <span>${item.qty} PCS</span>
                                     </div>`;
                    });
                } else if(ng > 0) {
                    labels.push('UNSPECIFIED NG');
                    values.push(ng);
                    listHtml += `<div class="text-center text-muted py-2 small">DETAIL LOG NOT FOUND</div>`;
                } else {
                    listHtml += `<div class="text-center text-success font-weight-bold py-2">ZERO DEFECT</div>`;
                }

                document.getElementById('ngListArea').innerHTML = listHtml;
                renderChart(labels, values);
                $('#modalBatchAnalysis').modal('show');
            });
    }

    function renderChart(labels, values) {
        const options = {
            series: values,
            chart: { type: 'donut', height: 350 },
            labels: labels,
            colors: ['#10b981', '#ef4444', '#f59e0b', '#3b82f6', '#8b5cf6'],
            legend: { position: 'bottom', fontWeight: 700 },
            stroke: { width: 0 },
            plotOptions: {
                pie: {
                    donut: {
                        size: '75%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'YIELD RATE',
                                formatter: function (w) {
                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    if(total === 0) return '0%';
                                    return ((values[0] / total) * 100).toFixed(1) + '%';
                                }
                            }
                        }
                    }
                }
            }
        };

        if (apexDonut) { apexDonut.updateOptions(options); } 
        else { apexDonut = new ApexCharts(document.querySelector("#donutChartArea"), options); apexDonut.render(); }
    }
</script>
@endsection