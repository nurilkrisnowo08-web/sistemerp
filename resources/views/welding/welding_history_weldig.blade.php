@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=JetBrains+Mono:wght@500;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-warning: #f59e0b; --dark-surface: #0f172a; --bg-main: #f8fafc;
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #334155; }
    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(135deg, var(--brand-primary), #7209b7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    /* 📈 LEDGER & CARDS */
    .ledger-container { background: #fff; border-radius: 28px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.04); }
    .stat-card { background: #fff; border-radius: 24px; padding: 22px; border: 1px solid #e2e8f0; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; overflow: hidden; }
    .stat-card:hover { transform: translateY(-8px); box-shadow: 0 20px 30px rgba(67, 97, 238, 0.1); }
    .stat-value { font-family: 'Orbitron'; font-size: 26px; font-weight: 900; line-height: 1; }
    
    /* 📋 TABLE HUD */
    .table-ledger thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; padding: 20px; border: none; }
    .table-ledger td { padding: 18px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 13px; }
    .row-clickable:hover { background-color: #f8faff !important; cursor: pointer; }

    /* 🛡️ MODAL ENHANCEMENT */
    .modal-content { border-radius: 40px !important; border: none; overflow: hidden; box-shadow: 0 25px 70px rgba(0,0,0,0.3); }
    .kamar-ng { background: #fff5f5; border-radius: 20px; padding: 20px; border: 1px solid #fed7d7; }
    .ng-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #feb2b2; font-family: 'JetBrains Mono'; font-weight: 800; }

    /* 🖨️ ULTIMATE PRINT ENGINE v10.0 */
    .print-only { display: none; }
    @media print {
        .no-print, .main-sidebar, .main-header, .btn, .filter-bar, nav, footer { display: none !important; }
        .content-wrapper, .content, .container-fluid { margin: 0 !important; padding: 0 !important; width: 100% !important; background: white !important; position: absolute; left: 0; top: 0; }
        .ledger-container { border: 1px solid #000 !important; box-shadow: none !important; border-radius: 0 !important; }
        .stat-card { border: 1px solid #000 !important; border-radius: 10px !important; }
        .print-only { display: block !important; }
        .print-header { border-bottom: 4px double #000; padding-bottom: 10px; margin-bottom: 25px; }
        .yield-badge { border: 1px solid #000 !important; color: #000 !important; background: transparent !important; }
        @page { size: A4 landscape; margin: 10mm; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    
    {{-- 🏛️ HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 no-print">
        <div>
            <h1 class="heading-hub mb-1">Welding_History <span style="-webkit-text-fill-color: var(--dark-surface);">Audit</span></h1>
            <p class="text-muted font-weight-bold small mb-0 uppercase tracking-widest"><i class="fas fa-shield-check text-primary mr-2"></i> Quality Assurance Traceability Log</p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
            <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 font-weight-extrabold shadow-lg mr-2">
                <i class="fas fa-print mr-2"></i> GENERATE_REPORT
            </button>
            <a href="{{ route('welding.index') }}" class="btn btn-outline-primary rounded-pill px-4 font-weight-extrabold border-2">
                <i class="fas fa-desktop mr-2"></i> TERMINAL
            </a>
        </div>
    </div>

    {{-- 📋 PRINT HEADER (HANYA MUNCUL SAAT PRINT) --}}
    <div class="print-only">
        <div class="print-header text-center">
            <h1 style="font-family: 'Orbitron'; font-weight: 900; margin: 0;">PT ASALTA MANDIRI AGUNG</h1>
            <p style="font-weight: 800; text-transform: uppercase; margin: 5px 0;">Production Audit Report: Welding Division</p>
            <p style="font-size: 12px;">Generated Date: {{ date('d/m/Y H:i') }} | Auditor: {{ Auth::user()->name }}</p>
        </div>
    </div>

    {{-- 📊 SUMMARY STATS --}}
    @php
        $totalAmbil = $historyData->sum('qty_masuk');
        $totalOk = $historyData->sum('qty_ok');
        $totalNg = $historyData->sum('qty_ng');
        $totalRet = $historyData->sum('qty_return');
        $yield = $totalAmbil > 0 ? ($totalOk / $totalAmbil) * 100 : 0;
    @endphp
    <div class="row mb-5">
        <div class="col-md-2 mb-3">
            <div class="stat-card border-bottom border-primary" style="border-bottom-width: 5px !important;">
                <div class="stat-label">Material Take</div>
                <div class="stat-value text-primary">{{ number_format($totalAmbil) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="stat-card border-bottom border-success" style="border-bottom-width: 5px !important;">
                <div class="stat-label">Passed OK</div>
                <div class="stat-value text-success">{{ number_format($totalOk) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="stat-card border-bottom border-danger" style="border-bottom-width: 5px !important;">
                <div class="stat-label">Defect (NG)</div>
                <div class="stat-value text-danger">{{ number_format($totalNg) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="stat-card border-bottom border-warning" style="border-bottom-width: 5px !important;">
                <div class="stat-label">Return RM</div>
                <div class="stat-value text-warning">{{ number_format($totalRet) }}</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card bg-dark text-white shadow-2xl">
                <div class="stat-label text-white-50">Global Performance Accuracy</div>
                <div class="stat-value text-white">{{ number_format($yield, 1) }}%</div>
            </div>
        </div>
    </div>

    {{-- 📉 TREND CHART (NO PRINT) --}}
    <div class="card border-0 shadow-sm mb-5 p-4 no-print" style="border-radius: 30px;">
        <h6 class="stat-label mb-4 ml-2"><i class="fas fa-wave-square mr-2 text-primary"></i> Performance Trend (Last 15 Sessions)</h6>
        <div id="yieldChart" style="min-height: 320px;"></div>
    </div>

    {{-- 📊 HISTORY TABLE --}}
    <div class="ledger-container">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Timestamp</th>
                        <th>Station</th>
                        <th class="text-left">Batch_No / Part_No</th>
                        <th>IN</th>
                        <th class="text-success">OK</th>
                        <th class="text-danger">NG</th>
                        <th>YIELD</th>
                        <th class="no-print">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($historyData as $h)
                    @php
                        $batchTotal = (float)$h->qty_ok + (float)$h->qty_ng;
                        $batchYield = $batchTotal > 0 ? ($h->qty_ok / $batchTotal) * 100 : 0;
                        
                        // Ambil Detail NG
                        $ngDetails = DB::table('production_ng_logs')
                            ->where('no_produksi', $h->no_produksi_stamping)
                            ->get();
                        $h->ng_details = $ngDetails;
                    @endphp
                    <tr class="row-clickable" onclick="showDetail({{ json_encode($h) }})">
                        <td class="text-left pl-5">
                            <div class="text-dark">{{ date('d/m/y', strtotime($h->updated_at)) }}</div>
                            <div class="small text-primary font-mono">{{ date('H:i', strtotime($h->updated_at)) }}</div>
                        </td>
                        <td><span class="station-badge">{{ $h->kode_line ?? 'W-AREA' }}</span></td>
                        <td class="text-left">
                            <div class="font-weight-black text-dark" style="font-family: 'JetBrains Mono';">{{ $h->no_produksi_stamping }}</div>
                            <div class="small text-muted font-weight-bold uppercase">{{ $h->part_no }}</div>
                        </td>
                        <td class="font-weight-black text-muted">{{ number_format($h->qty_masuk) }}</td>
                        <td class="text-success font-weight-black">{{ number_format($h->qty_ok) }}</td>
                        <td class="text-danger font-weight-black">{{ number_format($h->qty_ng) }}</td>
                        <td>
                            <div style="width: 70px; margin: 0 auto; border: 1.5px solid {{ $batchYield >= 98 ? 'var(--brand-success)' : 'var(--brand-warning)' }}; border-radius: 8px; padding: 3px; font-family: 'JetBrains Mono'; font-weight: 800; font-size: 11px;">
                                {{ number_format($batchYield, 1) }}%
                            </div>
                        </td>
                        <td class="no-print"><i class="fas fa-fingerprint text-muted opacity-50"></i></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 🖋️ SIGNATURE SECTION (ONLY PRINT) --}}
    <div class="print-only" style="margin-top: 50px;">
        <div class="row text-center">
            <div class="col-4">
                <p>Prepared by,</p>
                <div style="height: 80px; border-bottom: 1px solid #000; width: 150px; margin: 0 auto;"></div>
                <p class="small">( Production Operator )</p>
            </div>
            <div class="col-4">
                <p>Checked by,</p>
                <div style="height: 80px; border-bottom: 1px solid #000; width: 150px; margin: 0 auto;"></div>
                <p class="small">( Quality Inspector )</p>
            </div>
            <div class="col-4">
                <p>Approved by,</p>
                <div style="height: 80px; border-bottom: 1px solid #000; width: 150px; margin: 0 auto;"></div>
                <p class="small">( Production Supervisor )</p>
            </div>
        </div>
    </div>
</div>

{{-- 🛡️ MODAL DETAIL --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content animate__animated animate__zoomIn">
            <div class="modal-header bg-dark text-white p-4 border-0">
                <h6 class="modal-title font-weight-bold uppercase" style="font-family: 'Orbitron';">Batch_Deep_Dive_Analysis</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-5">
                <div class="row align-items-center mb-5">
                    <div class="col-md-5 text-center border-right">
                        <div id="modal-donut-yield"></div>
                        <h3 class="font-weight-black text-dark mb-0 mt-2" id="det-yield-val">0%</h3>
                        <small class="stat-label">Batch Accuracy</small>
                    </div>
                    <div class="col-md-7 pl-md-5 text-left">
                        <h4 class="font-weight-black text-primary mb-1" id="det-part"></h4>
                        <div class="badge badge-light border font-mono px-3 py-2" id="det-batch"></div>
                        <div class="row mt-4">
                            <div class="col-6"><small class="stat-label d-block">Operator</small><b id="det-qc">-</b></div>
                            <div class="col-6"><small class="stat-label d-block">Finish Time</small><b id="det-time">-</b></div>
                        </div>
                    </div>
                </div>

                <div class="row text-center mb-5">
                    <div class="col-4"><div class="p-3 bg-light rounded-xl"><small class="stat-label">In</small><div class="h5 font-weight-black m-0" id="det-masuk">0</div></div></div>
                    <div class="col-4"><div class="p-3 bg-success-soft rounded-xl"><small class="stat-label text-success">OK</small><div class="h5 font-weight-black text-success m-0" id="det-ok">0</div></div></div>
                    <div class="col-4"><div class="p-3 bg-danger-soft rounded-xl"><small class="stat-label text-danger">NG</small><div class="h5 font-weight-black text-danger m-0" id="det-ng">0</div></div></div>
                </div>

                <div class="mb-5 text-left">
                    <h6 class="stat-label text-danger font-weight-black border-bottom pb-2 mb-3"><i class="fas fa-microscope mr-2"></i>Defect Breakdown Report</h6>
                    <div id="ng-list-container" class="kamar-ng"></div>
                </div>

                <div class="p-4 bg-light rounded-3xl text-left border">
                    <small class="stat-label d-block mb-1">Process Abnormalities / Notes</small>
                    <p class="mb-0 font-weight-bold text-dark italic" id="det-remark"></p>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button class="btn btn-dark btn-block py-3 font-weight-black rounded-3xl shadow-xl" data-dismiss="modal">CLOSE REPORT</button>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. TREND CHART (Last 15 Batches)
    const historyData = @json($historyData->take(15)->reverse()->values());
    new ApexCharts(document.querySelector("#yieldChart"), {
        series: [{
            name: 'Yield %',
            data: historyData.map(h => {
                const total = parseFloat(h.qty_ok) + parseFloat(h.qty_ng);
                return total > 0 ? ((h.qty_ok / total) * 100).toFixed(1) : 0;
            })
        }],
        chart: { type: 'area', height: 320, toolbar: { show: false }, animations: { enabled: true, easing: 'easeinout', speed: 1000 } },
        colors: ['#4361ee'],
        stroke: { curve: 'smooth', width: 4 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
        xaxis: { categories: historyData.map(h => h.no_produksi_stamping.substr(-6)), labels: { style: { fontWeight: 800 } } },
        yaxis: { max: 100, min: 50, labels: { style: { fontWeight: 800 } } },
        markers: { size: 5, colors: ['#4361ee'], strokeWidth: 3, hover: { size: 8 } }
    }).render();

    // 2. MODAL DETAIL & DONUT CHART
    let donutChart = null;

    function showDetail(h) {
        document.getElementById('det-part').innerText = h.part_no;
        document.getElementById('det-batch').innerText = h.no_produksi_stamping;
        document.getElementById('det-masuk').innerText = h.qty_masuk.toLocaleString();
        document.getElementById('det-ok').innerText = h.qty_ok.toLocaleString();
        document.getElementById('det-ng').innerText = h.qty_ng.toLocaleString();
        document.getElementById('det-qc').innerText = h.qc_by || 'OP_NAME_NA';
        document.getElementById('det-time').innerText = h.updated_at;
        document.getElementById('det-remark').innerText = h.keterangan || 'Log: Normal processing, integrity verified.';

        const totalBatch = h.qty_ok + h.qty_ng;
        const yieldVal = totalBatch > 0 ? Math.round((h.qty_ok / totalBatch) * 100) : 0;
        document.getElementById('det-yield-val').innerText = yieldVal + "%";

        // NG List
        const container = document.getElementById('ng-list-container');
        container.innerHTML = '';
        if (h.ng_details && h.ng_details.length > 0) {
            h.ng_details.forEach(item => {
                container.innerHTML += `<div class="ng-item"><span class="text-danger">» ${item.ng_type.toUpperCase()}</span><span>${item.qty} PCS</span></div>`;
            });
        } else {
            container.innerHTML = '<div class="text-center text-muted font-weight-bold py-3 uppercase tracking-widest">Zero Defect Detected</div>';
        }

        // Donut Chart logic
        if (donutChart) donutChart.destroy();
        donutChart = new ApexCharts(document.querySelector("#modal-donut-yield"), {
            series: [h.qty_ok, h.qty_ng],
            chart: { type: 'donut', width: 220 },
            colors: ['#10b981', '#ef4444'],
            labels: ['OK', 'NG'],
            legend: { show: false },
            plotOptions: { pie: { donut: { size: '75%' } } }
        });
        donutChart.render();

        $('#detailModal').modal('show');
    }
</script>
@endsection