@extends('layout.admin')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root { --p-blue: #4361ee; --p-navy: #0f172a; --p-bg: #f8fafc; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--p-bg); }

    .heading-tech { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; color: var(--p-navy); }
    
    /* Stat Cards Modern */
    .glass-card { background: #fff; border-radius: 30px; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 10px 30px rgba(0,0,0,0.02); transition: 0.3s; padding: 25px; }
    
    /* ID Link Button */
    .batch-id-btn { 
        font-family: 'JetBrains Mono'; border: 1px solid #dbeafe; background: #eff6ff; color: var(--p-blue); 
        padding: 5px 12px; border-radius: 10px; font-weight: 800; font-size: 11px; transition: 0.3s; cursor: pointer;
    }
    .batch-id-btn:hover { background: var(--p-blue); color: #fff; box-shadow: 0 5px 15px rgba(67,97,238,0.3); transform: scale(1.05); }
    .batch-id-btn.active { background: var(--p-navy); color: #fff; border-color: var(--p-navy); }

    /* Modal Styling */
    .modal-content { border-radius: 40px; border: none; overflow: hidden; background: #fff; }
    .intel-panel { background: #f8fafc; border-radius: 30px; padding: 25px; height: 100%; border: 1px solid #e2e8f0; }
    
    .table-modern thead th { background: #f1f5f9; color: #64748b; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; border: none; }
    .table-modern td { vertical-align: middle; font-weight: 700; color: var(--p-navy); border-bottom: 1px solid #f1f5f9; }

    /* NG Highlight List */
    .ng-item-box { background: white; border-radius: 15px; padding: 12px 15px; border-left: 5px solid #ef4444; margin-bottom: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
    .batch-summary-box { background: var(--p-navy); color: white; border-radius: 15px; padding: 15px; margin-bottom: 20px; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="heading-tech mb-1">Quality <span class="text-primary">Intelligence</span></h1>
            <p class="text-muted small font-weight-bold mb-0">PT ASALTA MANDIRI AGUNG // UNIT_AUDIT_SYSTEM</p>
        </div>
        <input type="date" class="form-control border-0 shadow-sm rounded-pill px-4 font-weight-bold" style="width: 200px;" value="{{ $date }}">
    </div>

    {{-- Dashboard Summary --}}
    <div class="row mb-5 text-center">
        <div class="col-md-4">
            <div class="glass-card border-left border-success" style="border-left-width: 8px !important;">
                <small class="text-muted font-weight-bold uppercase">Passed Good (OK)</small>
                <h2 class="font-weight-black text-success mt-2">{{ number_format($sumStamping->total_ok ?? 0) }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card border-left border-danger" style="border-left-width: 8px !important;">
                <small class="text-muted font-weight-bold uppercase">Rejected (NG)</small>
                <h2 class="font-weight-black text-danger mt-2">{{ number_format($sumStamping->total_ng ?? 0) }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card bg-dark text-white">
                <small class="text-info font-weight-bold uppercase">Overall Yield</small>
                @php $totalS = ($sumStamping->total_ok ?? 0) + ($sumStamping->total_ng ?? 0); @endphp
                <h2 class="font-weight-black text-warning mt-2">{{ $totalS > 0 ? round(($sumStamping->total_ok / $totalS) * 100, 1) : 0 }}%</h2>
            </div>
        </div>
    </div>

    <div class="glass-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0 text-center table-modern">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Part Identification</th>
                        <th>Station</th>
                        <th class="text-success">Actual OK</th>
                        <th class="text-danger">Actual NG</th>
                        <th>Audit Trace</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailStamping as $ds)
                    <tr style="height: 80px;">
                        <td class="text-left pl-5">
                            <div class="font-weight-black text-dark" style="font-size: 15px;">{{ $ds->part_no }}</div>
                            <small class="text-muted font-weight-bold">UNIT_READY_FOR_AUDIT</small>
                        </td>
                        <td><span class="badge badge-light border px-3 py-2 rounded-lg font-weight-bold">{{ $ds->line_code }}</span></td>
                        <td class="text-success font-weight-black h5">{{ number_format($ds->qty_ok) }}</td>
                        <td class="text-danger font-weight-black h5">{{ number_format($ds->qty_ng) }}</td>
                        <td>
                            <button class="btn btn-primary rounded-pill px-4 font-weight-bold btn-sm shadow-sm" onclick="openAudit('{{ $ds->part_no }}', {{ json_encode($ds->batches) }})">
                                ANALYZE
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🤖 MODAL UTAMA --}}
<div class="modal fade animate__animated animate__fadeIn" id="modalAudit" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow-2xl">
            <div class="modal-body p-5">
                <div class="row">
                    <div class="col-lg-6 pr-lg-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="font-weight-black mb-0" id="modalTitle">--</h4>
                            <span class="badge badge-primary rounded-pill px-3">BATCH_LIST</span>
                        </div>
                        <div class="table-responsive" style="max-height: 450px;">
                            <table class="table table-borderless table-modern">
                                <thead class="small text-muted font-weight-bold">
                                    <tr>
                                        <th>Production ID</th>
                                        <th>In</th>
                                        <th class="text-success">OK</th>
                                        <th class="text-danger">NG</th>
                                    </tr>
                                </thead>
                                <tbody id="auditBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="intel-panel shadow-sm text-center">
                            <h6 class="font-weight-black text-muted uppercase small mb-4">Batch Performance Intelligence</h6>
                            
                            <div id="intelChartContainer" style="min-height: 300px;">
                                <div id="intelChart"></div>
                            </div>

                            <div id="ngDetailContent" class="mt-4 text-left">
                                <div class="text-center py-5 text-muted opacity-50">
                                    <i class="fas fa-mouse-pointer d-block mb-2 fa-2x"></i>
                                    <p class="small font-weight-bold">CLICK A BATCH ID ON THE LEFT<br>TO VIEW DETAILED ANALYSIS</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-top">
                    <button class="btn btn-light btn-block rounded-pill font-weight-bold py-3" data-dismiss="modal">CLOSE AUDIT SESSION</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let chartObj = null;

    function openAudit(partNo, batches) {
        document.getElementById('modalTitle').innerText = partNo;
        const body = document.getElementById('auditBody');
        body.innerHTML = '';
        
        document.getElementById('ngDetailContent').innerHTML = `
            <div class="text-center py-5 text-muted opacity-50">
                <i class="fas fa-mouse-pointer d-block mb-2 fa-2x"></i>
                <p class="small font-weight-bold">CLICK A BATCH ID ON THE LEFT<br>TO VIEW DETAILED ANALYSIS</p>
            </div>`;
        if(chartObj) chartObj.destroy(); chartObj = null;

        batches.forEach(b => {
            let qIn = b.qty_ambil_pcs || b.qty_masuk || 0;
            let qOk = b.qty_hasil_ok || b.qty_ok || 0;
            let qNg = b.qty_hasil_ng || b.qty_ng || 0;
            let id = b.no_produksi || b.no_produksi_stamping || b.no_produksi_welding;

            body.innerHTML += `
                <tr>
                    <td><button class="batch-id-btn" onclick="analyzeBatch(this, '${id}', ${qIn}, ${qOk}, ${qNg})">${id}</button></td>
                    <td class="font-weight-bold">${qIn}</td>
                    <td class="text-success font-weight-black">${qOk}</td>
                    <td class="text-danger font-weight-black">${qNg}</td>
                </tr>`;
        });
        $('#modalAudit').modal('show');
    }

    function analyzeBatch(btn, id, totalIn, ok, ng) {
        document.querySelectorAll('.batch-id-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        fetch(`/ppic/get-batch-ng-details/${id}`)
            .then(res => res.json())
            .then(data => {
                const labels = ['GOOD OK'];
                const values = [ok];
                
                // ✨ HUD Summary: Tampilkan info Ambil, OK, NG di atas daftar rincian
                let ngHtml = `
                    <div class="batch-summary-box shadow-sm">
                        <div class="row text-center">
                            <div class="col-4 border-right border-secondary"><small class="d-block opacity-75 uppercase">Taken (In)</small><b class="h5">${totalIn}</b></div>
                            <div class="col-4 border-right border-secondary"><small class="d-block opacity-75 uppercase">Good (OK)</small><b class="h5 text-success">${ok}</b></div>
                            <div class="col-4"><small class="d-block opacity-75 uppercase">Reject (NG)</small><b class="h5 text-danger">${ng}</b></div>
                        </div>
                    </div>
                    <p class="small font-weight-black text-muted uppercase mb-3">Defect Details Breakdown:</p>`;

                if(data.length > 0) {
                    data.forEach(item => {
                        labels.push(item.ng_type.toUpperCase());
                        values.push(parseInt(item.qty));
                        ngHtml += `
                            <div class="ng-item-box d-flex justify-content-between align-items-center animate__animated animate__fadeInLeft">
                                <span class="small font-weight-bold text-dark">${item.ng_type.toUpperCase()}</span>
                                <span class="badge badge-danger rounded-pill px-3 py-2 font-weight-black">${item.qty} PCS</span>
                            </div>`;
                    });
                } else if (ng > 0) {
                    labels.push('UNSPECIFIED NG');
                    values.push(ng);
                    ngHtml += `<div class="alert alert-warning rounded-xl text-center small font-weight-bold">NG DETECTED, BUT SPECIFIC LOG NOT FOUND</div>`;
                } else {
                    ngHtml += `<div class="alert alert-success rounded-xl text-center small font-weight-bold"><i class="fas fa-check-circle mr-1"></i> 100% QUALITY PERFECT</div>`;
                }

                document.getElementById('ngDetailContent').innerHTML = ngHtml;
                renderIntelligenceChart(labels, values, totalIn);
            });
    }

    function renderIntelligenceChart(labels, values, totalIn) {
        const options = {
            series: values,
            chart: { type: 'donut', height: 350, animations: { enabled: true, speed: 600 } },
            labels: labels,
            colors: ['#10b981', '#ef4444', '#f59e0b', '#3b82f6', '#8b5cf6', '#ec4899'],
            legend: { position: 'bottom', fontWeight: 700, fontSize: '12px' },
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
                                fontSize: '12px',
                                fontWeight: 900,
                                color: '#64748b',
                                formatter: function (w) {
                                    const okValue = w.globals.series[0];
                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    return total > 0 ? ((okValue / total) * 100).toFixed(1) + '%' : '0%';
                                }
                            },
                            value: { fontSize: '22px', fontWeight: 900, color: '#0f172a', show: true }
                        }
                    }
                }
            }
        };

        if (chartObj) { chartObj.updateOptions(options); } 
        else { chartObj = new ApexCharts(document.querySelector("#intelChart"), options); chartObj.render(); }
    }
</script>
@endsection