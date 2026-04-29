@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root { 
        --ind-steel: #4361ee; --ind-success: #10b981; 
        --ind-danger: #ef4444; --ind-warning: #f59e0b; --ind-info: #3a86ff;
    }
    
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: #0f172a; }
    .heading-cyber { font-family: 'Orbitron'; font-weight: 800; letter-spacing: -1px; text-transform: uppercase; }

    /* UI CARDS & TABLE */
    .stat-card { background: #fff; border-radius: 20px; padding: 22px; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 10px 30px rgba(0,0,0,0.02); height: 100%; position: relative; overflow: hidden; }
    .stat-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }
    .stat-value { font-family: 'Orbitron'; font-size: 24px; font-weight: 900; }
    .terminal-card { background: #fff; border-radius: 25px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); border: 1px solid #eef2f6; overflow: hidden; }
    .table-hud thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px; border: none; font-weight: 800; }
    .yield-pill { padding: 6px 12px; border-radius: 10px; font-family: 'JetBrains Mono'; font-weight: 800; font-size: 13px; }
    .ng-mini-pill { background: #fff1f2; color: var(--ind-danger); font-size: 9px; padding: 2px 8px; border-radius: 6px; border: 1px solid #fecdd3; font-family: 'JetBrains Mono'; font-weight: 700; display: inline-block; margin-top: 4px; }

    /* 🖨️ SAKTI PRINT ENGINE v2.0 */
    .print-only { display: none; }

    @media print {
        /* 1. Paksa Landscape & Hilangkan Margin Default */
        @page { size: A4 landscape; margin: 0.5cm; }
        
        /* 2. Hilangkan SEMUA elemen admin (Sidebar, Header, Footer, Button) */
        nav, .main-sidebar, .main-header, .main-footer, .no-print, .btn, .filter-bar, .modal { 
            display: none !important; 
        }

        /* 3. Paksa Content Luar agar melebar 100% */
        .content-wrapper, .content, .container-fluid { 
            margin: 0 !important; 
            padding: 0 !important; 
            width: 100% !important; 
            max-width: 100% !important; 
            background: white !important;
            left: 0 !important;
            position: absolute !important;
            top: 0 !important;
        }

        /* 4. Tampilkan Header Khusus Print */
        .print-only { display: block !important; }
        .print-header { border-bottom: 5px double #000; padding-bottom: 10px; margin-bottom: 20px; }

        /* 5. Styling Elemen Table & Card saat Print */
        .stat-card { border: 1px solid #ddd !important; box-shadow: none !important; margin-bottom: 10px; }
        .terminal-card { border: 1px solid #000 !important; border-radius: 0 !important; box-shadow: none !important; }
        .table-hud { width: 100% !important; border-collapse: collapse !important; }
        .table-hud th, .table-hud td { border: 1px solid #000 !important; padding: 10px !important; color: #000 !important; font-size: 11px !important; }
        
        /* 6. Pastikan Chart Muncul */
        #trendChart { display: block !important; height: 300px !important; width: 100% !important; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        
        .print-signature-box { display: flex !important; justify-content: space-between; margin-top: 30px; text-align: center; }
        .sig-box { width: 200px; }
    }
</style>

<div class="container-fluid py-4">

    {{-- 🏛️ KOP SURAT (Hanya saat Print) --}}
    <div class="print-only">
        <div class="print-header">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 80px;">
                        <div style="width: 70px; height: 70px; background: #000; color: #fff; text-align: center; line-height: 70px; font-weight: 900; border-radius: 12px; font-size: 20px;">AMA</div>
                    </td>
                    <td>
                        <h1 style="margin: 0; font-family: 'Orbitron'; font-weight: 900; font-size: 24px;">PT ASALTA MANDIRI AGUNG</h1>
                        <p style="margin: 0; font-size: 13px; font-weight: 700;">Kawasan Industri Mitrakarawang, Karawang, Jawa Barat</p>
                        <p style="margin: 0; font-size: 11px; color: #555;">PRODUCTION_LOG // QUALITY_VERIFICATION_REPORT</p>
                    </td>
                    <td style="text-align: right; vertical-align: middle;">
                        <div style="border: 1px solid #000; padding: 10px; display: inline-block; text-align: left;">
                            <div style="font-size: 10px; font-weight: 800;">PERIODE LAPORAN:</div>
                            <div style="font-size: 12px; font-weight: 700;">{{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    {{-- 🛰️ HEADER SCREEN (No-Print) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 no-print">
        <div>
            <h2 class="heading-cyber m-0 text-dark">PRODUCTION_AUDIT <span class="text-primary">v4.5</span></h2>
            <p class="text-muted small font-weight-bold mb-0 uppercase"><i class="fas fa-history text-primary mr-2"></i> PT ASALTA MANDIRI AGUNG KARAWANG</p>
        </div>
        
        <form action="{{ route('produksi.history') }}" method="GET" class="filter-bar d-flex align-items-center mt-3 mt-md-0">
            <div class="d-flex align-items-center">
                <i class="fas fa-calendar-alt text-primary mr-3"></i>
                <input type="date" name="start_date" value="{{ $startDate }}" class="input-date-custom">
                <span class="mx-3 text-muted font-weight-bold">TO</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="input-date-custom">
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-4 font-weight-bold ml-4 shadow-sm">SYNC</button>
        </form>
    </div>

    {{-- 🛸 2. STATS OVERVIEW --}}
    @php
        $totalAmbil = $history->sum('qty_ambil_pcs');
        $totalOk = $history->sum('qty_hasil_ok');
        $totalNg = $history->sum('qty_hasil_ng');
        $totalRet = $history->sum('qty_return_warehouse');
        $grandTotal = $totalOk + $totalNg;
        $avgYield = $grandTotal > 0 ? ($totalOk / $grandTotal) * 100 : 0;
    @endphp
    <div class="row mb-5">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="stat-label">Material Take-In</div>
                <div class="stat-value text-primary">{{ number_format($totalAmbil) }}</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 mb-3">
            <div class="stat-card">
                <div class="stat-label">Passed Good</div>
                <div class="stat-value text-success">{{ number_format($totalOk) }}</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 mb-3">
            <div class="stat-card">
                <div class="stat-label">Reject Items</div>
                <div class="stat-value text-danger">{{ number_format($totalNg) }}</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 mb-3">
            <div class="stat-card">
                <div class="stat-label">Return to RM</div>
                <div class="stat-value text-info">{{ number_format($totalRet) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-12 mb-3">
            <div class="stat-card bg-dark">
                <div class="stat-label text-white-50">Yield Accuracy</div>
                <div class="stat-value text-white">{{ number_format($avgYield, 1) }}%</div>
            </div>
        </div>
    </div>

    {{-- 📈 3. CHART & ACTION (Action No-Print) --}}
    <div class="row mb-5">
        <div class="col-md-9">
            <div class="terminal-card p-4">
                <h6 class="font-weight-black text-muted small uppercase mb-4 tracking-widest"><i class="fas fa-chart-area mr-2"></i> Quality Stability Analysis</h6>
                <div id="trendChart"></div>
            </div>
        </div>
        <div class="col-md-3 no-print">
            <div class="stat-card d-flex flex-column justify-content-center text-center p-4">
                <button onclick="window.print()" class="btn btn-dark btn-block font-weight-black py-3 rounded-xl mb-3 shadow-lg">PRINT REPORT</button>
                <a href="{{ route('produksi.index') }}" class="btn btn-outline-primary btn-block font-weight-bold py-3 rounded-xl">TERMINAL</a>
            </div>
        </div>
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
                        <th class="text-left">Defect Breakdown</th>
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
                    <tr>
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
        <div class="print-signature-box">
            <div class="sig-box">
                <p>Prepared by,</p>
                <div style="height: 80px;"></div>
                <p><b>( Production )</b></p>
            </div>
            <div class="sig-box">
                <p>Checked by,</p>
                <div style="height: 80px;"></div>
                <p><b>( Quality Control )</b></p>
            </div>
            <div class="sig-box">
                <p>Approved by,</p>
                <div style="height: 80px;"></div>
                <p><b>( Supervisor )</b></p>
            </div>
        </div>
        <p style="text-align: right; font-size: 10px; margin-top: 30px;">Generated: {{ now() }} // System User: {{ Auth::user()->name }}</p>
    </div>
</div>

{{-- MODAL DETAIL (Disesuaikan) --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 20px;">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h6 class="modal-title font-weight-bold">Batch Report</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4 text-center">
                <h4 id="det-batch" class="font-weight-black text-primary"></h4>
                <p id="det-remark" class="text-muted"></p>
            </div>
        </div>
    </div>
</div>

<script>
    // 📊 CHART LOGIC
    const chartData = @json($history->take(15)->reverse()->values());
    const options = {
        series: [{
            name: 'Yield %',
            data: chartData.map(h => {
                const ok = parseFloat(h.qty_hasil_ok) || 0;
                const ng = parseFloat(h.qty_hasil_ng) || 0;
                return (ok + ng) > 0 ? ((ok / (ok + ng)) * 100).toFixed(1) : 0;
            })
        }],
        chart: { type: 'area', height: 280, toolbar: { show: false }, zoom: { enabled: false } },
        colors: ['#4361ee'],
        stroke: { curve: 'smooth', width: 4 },
        xaxis: { categories: chartData.map(h => h.no_produksi.substr(-6)) },
        yaxis: { max: 100, min: 0 }
    };
    new ApexCharts(document.querySelector("#trendChart"), options).render();

    function showDetail(h) {
        document.getElementById('det-batch').innerText = h.no_produksi;
        document.getElementById('det-remark').innerText = h.keterangan || '-';
        $('#detailModal').modal('show');
    }
</script>
@endsection