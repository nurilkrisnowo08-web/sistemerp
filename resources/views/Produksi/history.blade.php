@extends('layout.admin')

@section('content')
{{-- ✨ HITUNGAN UTAMA (WAJIB DI ATAS) ✨ --}}
@php
    $totalTake = $history->sum('qty_ambil_pcs');
    $totalOk = $history->sum('qty_hasil_ok');
    $totalNg = $history->sum('qty_hasil_ng');
    $totalRet = $history->sum('qty_return_warehouse');
    $performance = ($totalTake - $totalRet) > 0 ? ($totalOk / ($totalTake - $totalRet)) * 100 : 0;
@endphp

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;900&family=JetBrains+Mono:wght@500;800&family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root { 
        --dragon-blue: #4361ee; --dragon-green: #10b981; 
        --dragon-red: #ef4444; --dragon-gold: #f59e0b; --dragon-sky: #3a86ff;
        --dragon-dark: #0f172a;
    }
    
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
    .heading-cyber { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; }

    /* 📊 SCREEN UI */
    .stat-card { background: #fff; border-radius: 20px; padding: 22px; border: 1px solid rgba(0,0,0,0.05); transition: 0.3s; box-shadow: 0 10px 30px rgba(0,0,0,0.02); height: 100%; position: relative; overflow: hidden; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(67, 97, 238, 0.1); }
    .stat-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }
    .stat-value { font-family: 'Orbitron'; font-size: 26px; font-weight: 900; }
    .card-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; }

    .terminal-card { background: #fff; border-radius: 25px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); border: 1px solid #eef2f6; overflow: hidden; }
    .table-hud thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px; border: none; font-weight: 800; }
    
    /* 🔴 NG BREAKDOWN STYLE */
    .ng-mini-pill { background: #fee2e2; color: var(--dragon-red); font-size: 9px; padding: 3px 8px; border-radius: 6px; border: 1px solid #fecdd3; font-family: 'JetBrains Mono'; font-weight: 800; display: inline-block; margin-top: 4px; margin-right: 3px; }

    /* 🖨️ SAKTI PRINT ENGINE v7.0 (ULTIMATE SIDEBAR KILLER) */
    .print-template { display: none; }

    @media print {
        /* 1. Paksa Sidebar dan UI Dashboard Hilang Total */
        .main-sidebar, .main-header, .main-footer, .no-print, .btn, .filter-bar, .modal, nav, footer, aside, .content-header, .sidebar-mini.sidebar-collapse .content-wrapper { 
            display: none !important; 
            width: 0 !important;
        }

        /* 2. Geser content ke paling kiri mentok (Fix image_29d4b1) */
        .content-wrapper, .content, .container-fluid { 
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            position: relative !important;
            left: 0 !important;
            top: 0 !important;
            display: block !important;
            background: white !important;
        }

        @page { size: A4 landscape; margin: 10mm; }

        .print-template { display: block !important; width: 100% !important; }
        .screen-only { display: none !important; }

        /* 3. Desain Laporan Resmi */
        .print-header { border-bottom: 5px double #000; padding-bottom: 10px; margin-bottom: 25px; }
        .print-table-final { width: 100% !important; border-collapse: collapse !important; margin-top: 20px; }
        .print-table-final th, .print-table-final td { border: 1px solid #000 !important; padding: 8px !important; color: black !important; font-size: 11px !important; }
        
        #trendChart { width: 100% !important; height: 350px !important; display: block !important; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">

    {{-- 🏛️ PRINT VERSION --}}
    <div class="print-template">
        <div class="print-header">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 80px;"><div style="width: 70px; height: 70px; background: #000; color: #fff; text-align: center; line-height: 70px; font-weight: 900; border-radius: 12px; font-size: 24px;">AMA</div></td>
                    <td>
                        <h1 style="margin: 0; font-family: 'Orbitron'; font-weight: 900; font-size: 26px;">PT ASALTA MANDIRI AGUNG</h1>
                        <p style="margin: 0; font-size: 14px; font-weight: 800;">Kawasan Industri Mitrakarawang, Karawang, Jawa Barat - Indonesia</p>
                        <p style="margin: 0; font-size: 11px; text-transform: uppercase;">Production Audit Report // Dragon Engine Traceability v7.0</p>
                    </td>
                    <td style="text-align: right;">
                        <div style="border: 2px solid #000; padding: 10px; display: inline-block;">
                            <div style="font-size: 9px; font-weight: 800;">REPORT DATE:</div>
                            <div style="font-size: 12px; font-weight: 900;">{{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 25px;">
            <div style="width: 19%; border: 1px solid #000; padding: 15px; text-align: center;"><b>TAKE</b><br>{{ number_format($totalTake) }}</div>
            <div style="width: 19%; border: 1px solid #000; padding: 15px; text-align: center;"><b>PASSED</b><br>{{ number_format($totalOk) }}</div>
            <div style="width: 19%; border: 1px solid #000; padding: 15px; text-align: center;"><b>REJECT</b><br>{{ number_format($totalNg) }}</div>
            <div style="width: 19%; border: 1px solid #000; padding: 15px; text-align: center;"><b>RETURN</b><br>{{ number_format($totalRet) }}</div>
            <div style="width: 19%; border: 1px solid #000; padding: 15px; text-align: center;"><b>PERF.</b><br>{{ number_format($performance, 1) }}%</div>
        </div>
    </div>

    {{-- 🛰️ SCREEN UI --}}
    <div class="screen-only">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 no-print">
            <div>
                <h2 class="heading-cyber m-0">PRODUCTION_AUDIT <span class="text-primary">v7.0</span></h2>
                <p class="text-muted small font-weight-bold mb-0 uppercase"><i class="fas fa-dragon text-primary mr-2"></i> Dragon Engine Activated // PT AMA</p>
            </div>
            <form action="{{ route('produksi.history') }}" method="GET" class="filter-bar d-flex align-items-center shadow-sm p-3 bg-white rounded-xl border">
                <i class="fas fa-calendar-alt text-primary mr-3"></i>
                <input type="date" name="start_date" value="{{ $startDate }}" class="input-date-custom mr-3">
                <input type="date" name="end_date" value="{{ $endDate }}" class="input-date-custom">
                <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold ml-4">SYNC</button>
            </form>
        </div>

        <div class="row mb-5">
            <div class="col-md-3 col-6 mb-3"><div class="stat-card"><div class="card-strip bg-primary"></div><div class="stat-label">Material Take</div><div class="stat-value text-primary">{{ number_format($totalTake) }}</div></div></div>
            <div class="col-md-2 col-4 mb-3"><div class="stat-card"><div class="card-strip bg-success"></div><div class="stat-label">Verified OK</div><div class="stat-value text-success">{{ number_format($totalOk) }}</div></div></div>
            <div class="col-md-2 col-4 mb-3"><div class="stat-card"><div class="card-strip bg-danger"></div><div class="stat-label">Total NG</div><div class="stat-value text-danger">{{ number_format($totalNg) }}</div></div></div>
            <div class="col-md-2 col-4 mb-3"><div class="stat-card"><div class="card-strip bg-info"></div><div class="stat-label">Return RM</div><div class="stat-value text-info">{{ number_format($totalRet) }}</div></div></div>
            <div class="col-md-3 col-12 mb-3"><div class="stat-card bg-dark text-white"><div class="stat-label text-white-50">Performance</div><div class="stat-value text-white">{{ number_format($performance, 1) }}%</div></div></div>
        </div>
    </div>

    {{-- 📈 THE ULTIMATE DRAGON CHART --}}
    <div class="terminal-card p-4 mb-5 shadow-xl animate__animated animate__zoomIn">
        <h6 class="font-weight-black text-muted small uppercase mb-4 tracking-widest no-print"><i class="fas fa-wave-square mr-2"></i> Dragon Spline Flow (Performance Matrix)</h6>
        <div id="trendChart"></div>
    </div>

    {{-- 📋 TABLE LOG --}}
    <div class="terminal-card shadow-lg mb-5 animate__animated animate__fadeInUp">
        <div class="table-responsive">
            <table class="table table-hud mb-0 text-center print-table-final">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Prod Timestamp</th>
                        <th>Batch_No</th>
                        <th class="text-left">Part Identification</th>
                        <th>Take</th>
                        <th class="text-success">OK</th>
                        <th class="text-danger">NG</th>
                        <th class="text-info">RET</th>
                        <th>Yield</th>
                        <th class="text-left">NG Breakdown (Red Focus)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $h)
                    @php 
                        $rincian = DB::table('production_ng_logs')->where('no_produksi', $h->no_produksi)->get();
                        $batchOk = (float)$h->qty_hasil_ok;
                        $batchNg = (float)$h->qty_hasil_ng;
                        $yield = ($batchOk + $batchNg) > 0 ? ($batchOk / ($batchOk + $batchNg)) * 100 : 0;
                        $color = ($yield >= 95) ? '#10b981' : (($yield >= 85) ? '#f59e0b' : '#ef4444');
                    @endphp
                    <tr class="row-clickable" onclick="showDetail({{ json_encode($h) }}, {{ json_encode($rincian) }})">
                        <td class="text-left pl-5">
                            <div class="font-weight-black text-dark" style="font-size: 12px;">{{ date('d/m/y', strtotime($h->created_at)) }}</div>
                            <div class="small text-primary font-weight-bold" style="font-family: 'JetBrains Mono';">{{ date('H:i', strtotime($h->created_at)) }}</div>
                        </td>
                        <td class="small font-weight-bold text-muted">{{ $h->no_produksi }}</td>
                        <td class="text-left font-weight-black text-dark pl-4">> {{ $h->material_code }}</td>
                        <td class="bg-light font-weight-black">{{ number_format($h->qty_ambil_pcs) }}</td>
                        <td class="text-success font-weight-black">{{ number_format($batchOk) }}</td>
                        <td class="text-danger font-weight-black">{{ number_format($batchNg) }}</td>
                        <td class="text-info font-weight-black">{{ number_format($h->qty_return_warehouse) }}</td>
                        <td><b style="color: {{ $color }}; font-family: 'JetBrains Mono'; font-size: 13px;">{{ number_format($yield, 1) }}%</b></td>
                        <td class="text-left">
                            {{-- ✨ NG BERWARNA MERAH SESUAI PERMINTAAN ✨ --}}
                            @foreach($rincian as $r)
                                <span class="ng-mini-pill animate__animated animate__pulse animate__infinite">{{ strtoupper($r->ng_type) }}({{ $r->qty }})</span>
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 🖋️ PRINT SIGNATURE --}}
    <div class="print-template">
        <div style="display: flex; justify-content: space-between; text-align: center; margin-top: 60px; font-weight: 800;">
            <div style="width: 250px;"><p>Prepared by,</p><div style="height: 70px; border-bottom: 1px solid #000;"></div><p>( Production Operator )</p></div>
            <div style="width: 250px;"><p>Checked by,</p><div style="height: 70px; border-bottom: 1px solid #000;"></div><p>( Quality Inspector )</p></div>
            <div style="width: 250px;"><p>Approved by,</p><div style="height: 70px; border-bottom: 1px solid #000;"></div><p>( Production Supervisor )</p></div>
        </div>
        <p style="text-align: right; font-size: 10px; margin-top: 40px; color: #555;">Document ID: PTAMA-AUDIT-{{ time() }} // System User: {{ Auth::user()->name }}</p>
    </div>

    <div class="text-center no-print mt-4 mb-5">
        <button onclick="window.print()" class="btn btn-dark btn-lg px-5 rounded-pill shadow-lg font-weight-black">
            <i class="fas fa-print mr-2 text-dragon-gold"></i> GENERATE FINAL AUDIT REPORT
        </button>
    </div>
</div>

{{-- 🛡️ MODAL DETAIL (Deep-Dive) --}}
<div class="modal fade animate__animated animate__zoomIn" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-2xl border-0" style="border-radius: 35px;">
            <div class="modal-header bg-dark text-white border-0 py-4 px-5">
                <h6 class="modal-title font-weight-black uppercase tracking-widest"><i class="fas fa-microchip mr-2 text-primary"></i>Audit_Batch_Deep_Dive</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-5 bg-white text-left">
                <div class="row align-items-center mb-5">
                    <div class="col-md-4 text-center border-right"><div id="modal-donut-yield"></div><h5 class="font-weight-black mt-2 mb-0" id="det-yield-val">0%</h5><small class="stat-label">Yield Accuracy</small></div>
                    <div class="col-md-8 pl-md-5">
                        <h4 class="font-weight-black text-primary mb-3" id="det-batch" style="font-family: 'Orbitron';"></h4>
                        <div class="row"><div class="col-6"><small class="stat-label">Part No</small><div class="font-weight-bold" id="det-part">-</div></div><div class="col-6"><small class="stat-label">Prod Timestamp</small><div class="font-weight-bold small" id="det-time">-</div></div></div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-3"><div class="p-3 bg-light rounded-xl text-center border"><small class="stat-label">Material Take</small><div class="h5 font-weight-black mb-0" id="det-take">0</div></div></div>
                    <div class="col-3"><div class="p-3 bg-success-soft rounded-xl text-center border"><small class="stat-label text-success">Passed Good</small><div class="h5 font-weight-black text-success mb-0" id="det-ok">0</div></div></div>
                    <div class="col-3"><div class="p-3 bg-danger-soft rounded-xl text-center border"><small class="stat-label text-danger">NG Reject</small><div class="h5 font-weight-black text-danger mb-0" id="det-ng">0</div></div></div>
                    <div class="col-3"><div class="p-3 bg-info-soft rounded-xl text-center border"><small class="stat-label text-info">Returned RM</small><div class="h5 font-weight-black text-info mb-0" id="det-ret">0</div></div></div>
                </div>
                <div id="det-ng-list" class="bg-light p-3 rounded-2xl border-dashed mb-3"></div>
                <div class="p-4 bg-light rounded-3xl border"><small class="stat-label d-block">Operator Note</small><p class="mb-0 font-weight-bold text-dark" id="det-remark" style="font-size: 13px;">-</p></div>
            </div>
        </div>
    </div>
</div>

<script>
    // 📊 DRAGON SPLINE MULTI-AXIS CHART
    const chartData = @json($history->take(15)->reverse()->values());
    const options = {
        series: [
            { name: 'Yield %', type: 'line', data: chartData.map(h => {
                const total = (parseFloat(h.qty_hasil_ok) || 0) + (parseFloat(h.qty_hasil_ng) || 0);
                return total > 0 ? ((h.qty_hasil_ok / total) * 100).toFixed(1) : 0;
            })},
            { name: 'Material Take', type: 'area', data: chartData.map(h => h.qty_ambil_pcs || 0) },
            { name: 'Passed OK', type: 'area', data: chartData.map(h => h.qty_hasil_ok || 0) },
            { name: 'NG Items', type: 'area', data: chartData.map(h => h.qty_hasil_ng || 0) },
            { name: 'Return RM', type: 'area', data: chartData.map(h => h.qty_return_warehouse || 0) }
        ],
        chart: { height: 350, type: 'line', toolbar: { show: false }, stacked: false, animations: { enabled: true, easing: 'easeinout', speed: 1000 } },
        stroke: { width: [5, 2, 2, 2, 2], curve: 'smooth', dashArray: [0, 0, 0, 0, 5] },
        colors: ['#4361ee', '#e2e8f0', '#10b981', '#ef4444', '#f59e0b'],
        fill: { type: 'gradient', gradient: { opacityFrom: [1, 0.4, 0.4, 0.4, 0.4], opacityTo: [1, 0.1, 0.1, 0.1, 0.1] } },
        xaxis: { categories: chartData.map(h => h.no_produksi.substr(-6)), labels: { style: { fontWeight: 800 } } },
        yaxis: [
            { title: { text: "Yield %", style: { color: '#4361ee' } }, labels: { style: { fontWeight: 800 } }, min: 0, max: 100 },
            { opposite: true, title: { text: "Quantity (PCS)" }, labels: { style: { fontWeight: 800 } } }
        ],
        markers: { size: 5 },
        tooltip: { shared: true, intersect: false, theme: 'dark' },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 }
    };
    new ApexCharts(document.querySelector("#trendChart"), options).render();

    let donut = null;
    function showDetail(h, rincian) {
        const ok = parseInt(h.qty_hasil_ok) || 0;
        const ng = parseInt(h.qty_hasil_ng) || 0;
        const total = ok + ng;
        const yieldVal = total > 0 ? Math.round((ok / total) * 100) : 0;
        const color = yieldVal >= 95 ? '#10b981' : (yieldVal >= 85 ? '#f59e0b' : '#ef4444');

        document.getElementById('det-batch').innerText = h.no_produksi;
        document.getElementById('det-part').innerText = h.material_code;
        document.getElementById('det-time').innerText = h.created_at;
        document.getElementById('det-take').innerText = h.qty_ambil_pcs;
        document.getElementById('det-ok').innerText = ok;
        document.getElementById('det-ng').innerText = ng;
        document.getElementById('det-ret').innerText = h.qty_return_warehouse;
        document.getElementById('det-yield-val').innerText = yieldVal + "%";
        document.getElementById('det-yield-val').style.color = color;
        document.getElementById('det-remark').innerText = h.keterangan || 'AUTOMATED_SYSTEM_LOG';

        const listDiv = document.getElementById('det-ng-list');
        listDiv.innerHTML = '';
        if (rincian && rincian.length > 0) {
            rincian.forEach(item => {
                listDiv.innerHTML += `
                    <div class="d-flex justify-content-between align-items-center mb-2 bg-white p-3 rounded-xl border">
                        <span class="font-weight-black text-danger small uppercase">• ${item.ng_type}</span>
                        <span class="badge badge-danger rounded-pill px-3 font-weight-bold">${item.qty} PCS</span>
                    </div>`;
            });
        } else {
            listDiv.innerHTML = '<div class="text-center text-muted small py-2 font-weight-bold">ZERO_DEFECTS_DETECTED</div>';
        }

        if(donut) donut.destroy();
        donut = new ApexCharts(document.querySelector("#modal-donut-yield"), {
            series: [ok, ng],
            chart: { type: 'donut', width: 160 },
            colors: ['#10b981', '#ef4444'],
            labels: ['Good', 'NG'],
            legend: { show: false },
            plotOptions: { pie: { donut: { size: '75%' } } }
        });
        donut.render();
        $('#detailModal').modal('show');
    }
</script>
@endsection