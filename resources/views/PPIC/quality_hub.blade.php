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
    .modal-content { border-radius: 35px; border: none; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
    .chart-box { background: #f8fafc; border-radius: 25px; padding: 20px; border: 1px solid #e2e8f0; min-height: 350px; display: flex; flex-direction: column; justify-content: center; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    {{-- 🛰️ HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="heading-tech mb-1">Stamping <span class="text-primary">Quality_Hub</span></h1>
            <p class="text-muted small font-weight-bold uppercase mb-0">PT ASALTA MANDIRI AGUNG // UNIT_AUDIT_LOG</p>
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
                <small class="text-info font-weight-bold uppercase d-block mb-1">Yield Achievement</small>
                @php $totalS = ($sumStamping->total_ok ?? 0) + ($sumStamping->total_ng ?? 0); @endphp
                <div class="yield-value text-warning h2 mb-0">{{ $totalS > 0 ? round(($sumStamping->total_ok / $totalS) * 100, 1) : 0 }}%</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 25px;">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 text-center">
                        <thead class="bg-light">
                            <tr class="small text-muted font-weight-black uppercase">
                                <th class="text-left pl-4">Part Identification</th>
                                <th>Station</th>
                                <th>Shift</th>
                                <th>OK Items</th>
                                <th>NG Items</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detailStamping as $ds)
                            {{-- SAAT DIKLIK, MEMANGGIL showDrilldown --}}
                            <tr class="row-clickable" onclick="showDrilldown('{{ $ds->part_no }}', {{ json_encode($ds->batches ?? []) }})">
                                <td class="text-left pl-4">
                                    <div class="text-primary font-weight-black">{{ $ds->part_no }}</div>
                                    <small class="text-muted">Click to view audit trace</small>
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

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 25px;">
                <h6 class="font-weight-black text-muted uppercase small mb-4">Defect Ranking (Top NG)</h6>
                @forelse($ngStamping as $index => $ns)
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded-xl border">
                    <span class="font-weight-bold text-dark"><span class="text-primary mr-2">#{{$index+1}}</span> {{ strtoupper($ns->ng_type) }}</span>
                    <span class="ng-badge">{{ $ns->total }} PCS</span>
                </div>
                @empty
                <div class="text-center py-5 opacity-50">
                    <p class="small font-weight-bold">NO DEFECTS RECORDED</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- 🤖 MODAL DRILLDOWN DENGAN GRAFIK --}}
<div class="modal fade" id="modalDrilldown" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-warning text-dark p-4">
                <h5 class="modal-title font-weight-black uppercase" id="drilldownTitle" style="font-family: 'Orbitron';">AUDIT_TRACE</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="row">
                    {{-- SEKTOR KIRI: GRAFIK DONUT --}}
                    <div class="col-lg-5 mb-4">
                        <div class="chart-box">
                            <h6 class="text-center font-weight-bold text-muted small mb-4">BATCH PERFORMANCE COMPOSITION</h6>
                            <div id="drilldownDonut"></div>
                            <div class="row mt-4 text-center">
                                <div class="col-4 border-right">
                                    <small class="text-muted font-weight-bold">AMBIL</small>
                                    <h5 id="lblTotalAmbil" class="font-weight-black mb-0">0</h5>
                                </div>
                                <div class="col-4 border-right text-success">
                                    <small class="font-weight-bold">OK</small>
                                    <h5 id="lblTotalOk" class="font-weight-black mb-0">0</h5>
                                </div>
                                <div class="col-4 text-danger">
                                    <small class="font-weight-bold">NG</small>
                                    <h5 id="lblTotalNg" class="font-weight-black mb-0">0</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- SEKTOR KANAN: TABEL BATCH --}}
                    <div class="col-lg-7">
                        <div class="table-responsive border rounded-xl overflow-hidden shadow-sm">
                            <table class="table table-hover mb-0 text-center">
                                <thead class="bg-light small font-weight-black">
                                    <tr>
                                        <th class="text-left pl-4">Production ID</th>
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
            <div class="modal-footer bg-light p-3">
                <button class="btn btn-dark btn-block font-weight-bold py-3 rounded-xl shadow-sm" data-dismiss="modal">CLOSE AUDIT LOG</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Inisialisasi variabel chart secara global
    let auditChart = null;

    function showDrilldown(partNo, batches) {
        document.getElementById('drilldownTitle').innerText = "AUDIT TRACE: " + partNo;
        const body = document.getElementById('drilldownBody');
        body.innerHTML = '';

        let totalIn = 0;
        let totalOk = 0;
        let totalNg = 0;

        if (!batches || batches.length === 0) {
            body.innerHTML = '<tr><td colspan="4" class="py-5 text-muted">NO DATA AVAILABLE</td></tr>';
        } else {
            batches.forEach(b => {
                let qIn = parseInt(b.qty_ambil_pcs || b.qty_masuk) || 0;
                let qOk = parseInt(b.qty_hasil_ok || b.qty_ok) || 0;
                let qNg = parseInt(b.qty_hasil_ng || b.qty_ng) || 0;

                totalIn += qIn;
                totalOk += qOk;
                totalNg += qNg;

                body.innerHTML += `
                    <tr>
                        <td class="text-left pl-4 font-weight-bold small">
                            <span class="text-primary">${b.no_produksi || b.no_produksi_stamping || b.no_produksi_welding}</span>
                        </td>
                        <td class="font-weight-black text-muted">${qIn.toLocaleString()}</td>
                        <td class="text-success font-weight-black">${qOk.toLocaleString()}</td>
                        <td class="text-danger font-weight-black">${qNg.toLocaleString()}</td>
                    </tr>
                `;
            });
        }

        // Update Label Summary
        document.getElementById('lblTotalAmbil').innerText = totalIn.toLocaleString();
        document.getElementById('lblTotalOk').innerText = totalOk.toLocaleString();
        document.getElementById('lblTotalNg').innerText = totalNg.toLocaleString();

        // Render atau Update Grafik
        renderAuditDonut(totalOk, totalNg);

        // Tampilkan Modal
        $('#modalDrilldown').modal('show');
    }

    function renderAuditDonut(ok, ng) {
        const options = {
            series: [ok, ng],
            chart: { type: 'donut', height: 320, animations: { enabled: true, speed: 800 } },
            labels: ['PASS GOOD (OK)', 'REJECT (NG)'],
            colors: ['#10b981', '#ef4444'],
            legend: { position: 'bottom', fontWeight: 700 },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '80%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '12px', fontWeight: 700, color: '#64748b' },
                            value: { 
                                show: true, fontSize: '24px', fontWeight: 900, color: '#0f172a',
                                formatter: (val) => val.toLocaleString()
                            },
                            total: {
                                show: true, label: 'TOTAL OK RATE', fontSize: '10px', fontWeight: 900, color: '#4361ee',
                                formatter: function (w) {
                                    const sum = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    if (sum === 0) return '0%';
                                    return ((ok / sum) * 100).toFixed(1) + '%';
                                }
                            }
                        }
                    }
                }
            }
        };

        if (auditChart) {
            auditChart.updateOptions(options);
        } else {
            auditChart = new ApexCharts(document.querySelector("#drilldownDonut"), options);
            auditChart.render();
        }
    }
</script>
@endsection