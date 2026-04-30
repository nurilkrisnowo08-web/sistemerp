@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root { 
        --ind-steel: #4361ee; --ind-success: #10b981; 
        --ind-danger: #ef4444; --ind-warning: #f59e0b; --ind-info: #3a86ff;
        --ind-dark: #0f172a;
    }
    
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: var(--ind-dark); }
    .heading-cyber { font-family: 'Orbitron'; font-weight: 800; letter-spacing: -1px; text-transform: uppercase; }

    /* 📊 SCREEN UI: STATS CARDS */
    .stat-card { background: #fff; border-radius: 20px; padding: 22px; border: 1px solid rgba(0,0,0,0.05); transition: 0.3s; box-shadow: 0 10px 30px rgba(0,0,0,0.02); height: 100%; position: relative; overflow: hidden; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(67, 97, 238, 0.1); }
    .stat-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }
    .stat-value { font-family: 'Orbitron'; font-size: 24px; font-weight: 900; }
    .card-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 6px; }

    /* 📋 TABLE HUD */
    .terminal-card { background: #fff; border-radius: 25px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); border: 1px solid #eef2f6; overflow: hidden; }
    .table-hud thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px; border: none; font-weight: 800; }
    .row-clickable { cursor: pointer; transition: 0.3s; }
    .row-clickable:hover { background-color: rgba(67, 97, 238, 0.04) !important; }
    .ng-mini-pill { background: #fff1f2; color: var(--ind-danger); font-size: 9px; padding: 2px 8px; border-radius: 6px; border: 1px solid #fecdd3; font-family: 'JetBrains Mono'; font-weight: 700; display: inline-block; margin-top: 4px; margin-right: 3px; }

    /* 🖨️ SAKTI PRINT ENGINE v5.0 - ANTI-SIDEBAR */
    .print-only { display: none; }

    @media print {
        @page { size: A4 landscape; margin: 10mm; }
        
        /* 1. Paksa Sidebar, Navbar, dan Footer Admin Hilang */
        .main-sidebar, .main-header, .main-footer, .no-print, .btn, .filter-bar, .modal, nav, footer { 
            display: none !important; 
        }

        /* 2. Geser konten ke pojok kiri mentok agar tabel tidak terpotong */
        .content-wrapper, .content, .container-fluid { 
            margin: 0 !important; 
            padding: 0 !important; 
            width: 100% !important; 
            max-width: 100% !important; 
            background: white !important;
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
        }

        /* 3. Tampilkan Kop Surat Industrial */
        .print-only { display: block !important; }
        .print-header-brand { border-bottom: 5px double #000; padding-bottom: 10px; margin-bottom: 25px; }

        /* 4. Perbaiki Visual Tabel & Grafik untuk Print */
        .stat-card { border: 1px solid #ddd !important; box-shadow: none !important; margin-bottom: 10px !important; }
        .terminal-card { border: 1px solid #000 !important; border-radius: 0 !important; box-shadow: none !important; }
        .table-hud { width: 100% !important; border-collapse: collapse !important; }
        .table-hud th, .table-hud td { border: 1px solid #000 !important; padding: 8px !important; color: #000 !important; font-size: 11px !important; }
        
        #trendChart { width: 100% !important; display: block !important; height: 250px !important; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        
        .print-sig-container { display: flex !important; justify-content: space-between; margin-top: 40px; text-align: center; }
        .sig-item { width: 30%; }
    }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">

    {{-- 🏛️ KOP SURAT RESMI (Hanya muncul pas di-print) --}}
    <div class="print-only">
        <div class="print-header-brand">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 80px;">
                        <div style="width: 70px; height: 70px; background: #000; color: #fff; text-align: center; line-height: 70px; font-weight: 900; border-radius: 12px; font-size: 24px;">AMA</div>
                    </td>
                    <td>
                        <h1 style="margin: 0; font-family: 'Orbitron'; font-weight: 900; font-size: 26px;">PT ASALTA MANDIRI AGUNG</h1>
                        <p style="margin: 0; font-size: 14px; font-weight: 700;">Kawasan Industri Mitrakarawang, Karawang, Jawa Barat - Indonesia</p>
                        <p style="margin: 0; font-size: 11px; text-transform: uppercase; color: #444;">Department: Production & Quality Assurance // History Audit v5.0</p>
                    </td>
                    <td style="text-align: right; vertical-align: middle;">
                        <div style="border: 2px solid #000; padding: 10px; display: inline-block; text-align: left;">
                            <div style="font-size: 9px; font-weight: 800;">PERIOD:</div>
                            <div style="font-size: 12px; font-weight: 900;">{{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    {{-- 🛰️ SCREEN HEADER (No-Print) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 no-print">
        <div>
            <h2 class="heading-cyber m-0 text-dark">PRODUCTION_AUDIT <span class="text-primary">v4.5</span></h2>
            <p class="text-muted small font-weight-bold mb-0 uppercase"><i class="fas fa-history text-primary mr-2"></i> PT ASALTA MANDIRI AGUNG KARAWANG</p>
        </div>
        
        <form action="{{ route('produksi.history') }}" method="GET" class="filter-bar d-flex align-items-center shadow-sm p-3 bg-white rounded-xl border">
            <i class="fas fa-calendar-alt text-primary mr-3"></i>
            <input type="date" name="start_date" value="{{ $startDate }}" class="input-date-custom mr-2">
            <input type="date" name="end_date" value="{{ $endDate }}" class="input-date-custom">
            <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold ml-3 shadow-sm">SYNC</button>
        </form>
    </div>

    {{-- 🛸 2. STATS OVERVIEW --}}
    @php
        $totalTake = $history->sum('qty_ambil_pcs');
        $totalOk = $history->sum('qty_hasil_ok');
        $totalNg = $history->sum('qty_hasil_ng');
        $totalRet = $history->sum('qty_return_warehouse');
        $performance = ($totalTake - $totalRet) > 0 ? ($totalOk / ($totalTake - $totalRet)) * 100 : 0;
    @endphp
    <div class="row mb-5">
        <div class="col-md-3 col-6 mb-3">
            <div class="stat-card">
                <div class="card-strip bg-primary"></div>
                <div class="stat-label">Material Take</div>
                <div class="stat-value text-primary">{{ number_format($totalTake) }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="stat-card"><div class="stat-label">Verified OK</div><div class="stat-value text-success">{{ number_format($totalOk) }}</div></div>
        </div>
        <div class="col-md-2 col-4 mb-3">
            <div class="stat-card"><div class="stat-label">Total NG</div><div class="stat-value text-danger">{{ number_format($totalNg) }}</div></div>
        </div>
        <div class="col-md-2 col-4 mb-3">
            <div class="stat-card"><div class="stat-label">Return RM</div><div class="stat-value text-info">{{ number_format($totalRet) }}</div></div>
        </div>
        <div class="col-md-3 col-4 mb-3">
            <div class="stat-card bg-dark text-white"><div class="stat-label text-white-50">Performance</div><div class="stat-value text-white">{{ number_format($performance, 1) }}%</div></div>
        </div>
    </div>

    {{-- 📈 3. CHART --}}
    <div class="terminal-card p-4 mb-5">
        <h6 class="font-weight-black text-muted small uppercase mb-4 tracking-widest no-print"><i class="fas fa-chart-area mr-2"></i> Quality Stability Trends</h6>
        <div id="trendChart"></div>
    </div>

    {{-- 📋 4. TABLE LOG --}}
    <div class="terminal-card shadow-lg mb-5">
        <div class="table-responsive">
            <table class="table table-hud mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-5">Prod Timestamp</th>
                        <th>Batch_No</th>
                        <th class="text-left">Part Identification</th>
                        <th class="bg-light">Take</th>
                        <th class="text-success">OK</th>
                        <th class="text-danger">NG</th>
                        <th class="text-info">RET</th>
                        <th>Yield</th>
                        <th class="text-left">NG Breakdown</th>
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
                            <div class="small text-primary font-weight-bold">{{ date('H:i', strtotime($h->created_at)) }}</div>
                        </td>
                        <td class="small font-weight-bold text-muted">{{ $h->no_produksi }}</td>
                        <td class="text-left font-weight-black text-dark pl-4">> {{ $h->material_code }}</td>
                        <td class="bg-light font-weight-black">{{ number_format($h->qty_ambil_pcs) }}</td>
                        <td class="text-success font-weight-black">{{ number_format($batchOk) }}</td>
                        <td class="text-danger font-weight-black">{{ number_format($batchNg) }}</td>
                        <td class="text-info font-weight-black">{{ number_format($h->qty_return_warehouse) }}</td>
                        <td style="color: {{ $color }}; font-weight: 800;">{{ number_format($yield, 1) }}%</td>
                        <td class="text-left">
                            @foreach($rincian as $r)
                                <span class="ng-mini-pill">{{ strtoupper($r->ng_type) }}({{ $r->qty }})</span>
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 🖋️ 5. SIGNATURE BOX (Hanya Print) --}}
    <div class="print-only">
        <div class="print-sig-container">
            <div class="sig-item"><p>Prepared by,</p><div style="height: 60px;"></div><p>____________________</p><p><b>( Production )</b></p></div>
            <div class="sig-item"><p>Checked by,</p><div style="height: 60px;"></div><p>____________________</p><p><b>( Quality Control )</b></p></div>
            <div class="sig-item"><p>Approved by,</p><div style="height: 60px;"></div><p>____________________</p><p><b>( Supervisor )</b></p></div>
        </div>
        <p style="text-align: right; font-size: 10px; margin-top: 30px;">Report Generated: {{ now() }} // System User: {{ Auth::user()->name }}</p>
    </div>

    {{-- BUTTON PRINT (No-Print) --}}
    <div class="text-center no-print mt-4 mb-5">
        <button onclick="window.print()" class="btn btn-dark btn-lg px-5 rounded-pill shadow-lg font-weight-black">
            <i class="fas fa-print mr-2"></i> PRINT OFFICIAL AUDIT REPORT
        </button>
    </div>
</div>

{{-- 🛡️ MODAL DETAIL SAKTI (Full Deep-Dive) --}}
<div class="modal fade animate__animated animate__zoomIn" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-2xl border-0" style="border-radius: 35px;">
            <div class="modal-header bg-dark text-white border-0 py-4 px-5">
                <h6 class="modal-title font-weight-black uppercase tracking-widest"><i class="fas fa-microchip mr-2 text-primary"></i>Audit_Batch_Deep_Dive</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-5 bg-white text-left">
                <div class="row align-items-center mb-5">
                    <div class="col-md-4 text-center border-right">
                        <div id="modal-donut-yield"></div>
                        <h5 class="font-weight-black mt-2 mb-0" id="det-yield-val">0%</h5>
                        <small class="stat-label">Yield Accuracy</small>
                    </div>
                    <div class="col-md-8 pl-md-5">
                        <small class="stat-label d-block">Batch Identifier</small>
                        <h4 class="font-weight-black text-primary mb-3" id="det-batch" style="font-family: 'Orbitron';"></h4>
                        <div class="row">
                            <div class="col-6"><small class="stat-label">Part Identification</small><div class="font-weight-bold" id="det-part">-</div></div>
                            <div class="col-6"><small class="stat-label">Creation Timestamp</small><div class="font-weight-bold small text-muted" id="det-time">-</div></div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-3"><div class="p-3 bg-light rounded-xl text-center border"><small class="stat-label">Ambil</small><div class="h5 font-weight-black mb-0" id="det-take">0</div></div></div>
                    <div class="col-3"><div class="p-3 bg-success-soft rounded-xl text-center border"><small class="stat-label text-success font-weight-black">OK</small><div class="h5 font-weight-black text-success mb-0" id="det-ok">0</div></div></div>
                    <div class="col-3"><div class="p-3 bg-danger-soft rounded-xl text-center border"><small class="stat-label text-danger font-weight-black">NG</small><div class="h5 font-weight-black text-danger mb-0" id="det-ng">0</div></div></div>
                    <div class="col-3"><div class="p-3 bg-info-soft rounded-xl text-center border"><small class="stat-label text-info font-weight-black">RET</small><div class="h5 font-weight-black text-info mb-0" id="det-ret">0</div></div></div>
                </div>

                <div class="mb-4">
                    <h6 class="font-weight-black text-danger small mb-3 uppercase border-bottom pb-2">Reject Breakdown:</h6>
                    <div id="det-ng-list" class="bg-light p-3 rounded-2xl border-dashed"></div>
                </div>

                <div class="p-4 bg-light rounded-3xl border">
                    <small class="text-uppercase font-weight-black text-muted d-block mb-2" style="font-size: 9px;">Operator Remark :</small>
                    <p class="mb-0 font-weight-bold text-dark" id="det-remark" style="font-size: 13px;">-</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 📊 TREND CHART
    const chartData = @json($history->take(15)->reverse()->values());
    const options = {
        series: [{
            name: 'Yield %',
            data: chartData.map(h => {
                const total = (parseFloat(h.qty_hasil_ok) || 0) + (parseFloat(h.qty_hasil_ng) || 0);
                return total > 0 ? ((h.qty_hasil_ok / total) * 100).toFixed(1) : 0;
            })
        }, {
            name: 'Return Qty',
            data: chartData.map(h => h.qty_return_warehouse || 0)
        }],
        chart: { type: 'area', height: 300, toolbar: { show: false }, animations: { enabled: false } },
        colors: ['#4361ee', '#3a86ff'],
        stroke: { curve: 'smooth', width: 4 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
        xaxis: { categories: chartData.map(h => h.no_produksi.substr(-6)), labels: { style: { fontSize: '10px', fontWeight: 700 } } },
        yaxis: { labels: { formatter: (val) => val.toFixed(0) } }
    };
    new ApexCharts(document.querySelector("#trendChart"), options).render();

    // 🛡️ MODAL DETAIL Deep-Dive
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
        document.getElementById('det-remark').innerText = h.keterangan || 'NO_SPECIFIC_REMARK';

        const listDiv = document.getElementById('det-ng-list');
        listDiv.innerHTML = '';
        if (rincian && rincian.length > 0) {
            rincian.forEach(item => {
                listDiv.innerHTML += `
                    <div class="d-flex justify-content-between align-items-center mb-2 bg-white p-3 rounded-xl shadow-sm border">
                        <span class="font-weight-black text-danger small uppercase">• ${item.ng_type}</span>
                        <span class="badge badge-danger rounded-pill px-3 font-weight-bold">${item.qty} PCS</span>
                    </div>`;
            });
        } else {
            listDiv.innerHTML = '<div class="text-center text-muted small py-2 font-weight-bold">ZERO DEFECTS RECORDED</div>';
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