@php
    /** 
     * ✨ STANDALONE DATA LOADING 
     * Mengambil data langsung dari DB agar tidak error "Undefined variable"
     */
    $historyData = DB::table('welding_batches')
        ->leftJoin('line_welding', 'welding_batches.line_id', '=', 'line_welding.id')
        ->where('welding_batches.status', 'COMPLETED')
        ->select('welding_batches.*', 'line_welding.kode_line', 'line_welding.nama_line')
        ->orderBy('welding_batches.updated_at', 'desc')
        ->limit(15) // Kita ambil 15 data terbaru untuk audit
        ->get();

    // Hitung total ringkasan
    $totalAmbil = $historyData->sum('qty_masuk');
    $totalOk = $historyData->sum('qty_ok');
    $totalNg = $historyData->sum('qty_ng');
    $totalRet = $historyData->sum('qty_return');
    $yieldGlobal = $totalAmbil > 0 ? ($totalOk / $totalAmbil) * 100 : 0;
@endphp

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=JetBrains+Mono:wght@500;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root {
        --brand-primary: #4361ee; --brand-success: #10b981; --brand-danger: #ef4444;
        --brand-warning: #f59e0b; --dark-surface: #0f172a; --bg-main: #f8fafc;
    }

    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; background: linear-gradient(135deg, var(--brand-primary), #7209b7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    /* 📊 CARDS UI */
    .stat-card { background: #fff; border-radius: 20px; padding: 18px; border: 1px solid #e2e8f0; transition: 0.4s; position: relative; overflow: hidden; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(67, 97, 238, 0.1); }
    .stat-label { font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
    .stat-value { font-family: 'Orbitron'; font-size: 22px; font-weight: 900; }
    
    /* 📋 TABLE STYLE */
    .ledger-container { background: #fff; border-radius: 25px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.03); }
    .table-ledger thead th { background: #f8fafc; color: #64748b; font-size: 10px; text-transform: uppercase; padding: 18px; border: none; font-weight: 800; }
    .table-ledger td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 13px; }
    .row-clickable:hover { background-color: #f8faff !important; cursor: pointer; }
    .station-badge { background: var(--dark-surface); color: var(--brand-warning); font-family: 'JetBrains Mono'; font-size: 10px; padding: 3px 10px; border-radius: 6px; }

    /* 🖨️ PRINT LOGIC */
    .print-only { display: none; }
    @media print {
        .no-print, .btn, .main-header, .main-sidebar { display: none !important; }
        .print-only { display: block !important; }
        .ledger-container { border: 1px solid #000 !important; border-radius: 0; }
        @page { size: A4 landscape; margin: 10mm; }
    }
</style>

<div class="container-fluid py-3 animate__animated animate__fadeIn">
    
    {{-- 🏛️ HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h2 class="heading-hub m-0" style="font-size: 1.5rem;">Welding_Archive <span style="-webkit-text-fill-color: var(--dark-surface);">History</span></h2>
            <p class="text-muted small font-weight-bold mb-0 uppercase"><i class="fas fa-database text-primary mr-2"></i> Production Integrity Log</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 shadow-lg"><i class="fas fa-print mr-2"></i> PRINT_AUDIT</button>
        </div>
    </div>

    {{-- 📊 SUMMARY STATS --}}
    <div class="row mb-4">
        <div class="col-md-2 mb-3">
            <div class="stat-card border-bottom border-primary" style="border-bottom-width: 4px !important;">
                <div class="stat-label">Material In</div>
                <div class="stat-value text-primary">{{ number_format($totalAmbil) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="stat-card border-bottom border-success" style="border-bottom-width: 4px !important;">
                <div class="stat-label">Total OK</div>
                <div class="stat-value text-success">{{ number_format($totalOk) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="stat-card border-bottom border-danger" style="border-bottom-width: 4px !important;">
                <div class="stat-label">Total NG</div>
                <div class="stat-value text-danger">{{ number_format($totalNg) }}</div>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <div class="stat-card border-bottom border-warning" style="border-bottom-width: 4px !important;">
                <div class="stat-label">Return WIP</div>
                <div class="stat-value text-warning">{{ number_format($totalRet) }}</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card bg-dark text-white shadow-lg">
                <div class="stat-label text-white-50">Global Efficiency</div>
                <div class="stat-value text-white">{{ number_format($yieldGlobal, 1) }}%</div>
            </div>
        </div>
    </div>

    {{-- 📉 CHART --}}
    <div class="card border-0 shadow-sm mb-4 p-4 no-print" style="border-radius: 25px;">
        <h6 class="stat-label mb-3"><i class="fas fa-chart-line mr-2 text-primary"></i> Last 15 Batches Performance</h6>
        <div id="yieldChartArchive"></div>
    </div>

    {{-- 📊 TABLE --}}
    <div class="ledger-container">
        <div class="table-responsive">
            <table class="table table-ledger mb-0 text-center">
                <thead>
                    <tr>
                        <th class="text-left pl-4">Timestamp</th>
                        <th>Station</th>
                        <th class="text-left">Batch / Part Identification</th>
                        <th class="text-success">OK</th>
                        <th class="text-danger">NG</th>
                        <th>Yield</th>
                        <th class="no-print text-right pr-4">Log</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historyData as $h)
                    @php
                        $batchTotal = (float)$h->qty_ok + (float)$h->qty_ng;
                        $batchYield = $batchTotal > 0 ? ($h->qty_ok / $batchTotal) * 100 : 0;
                        $ngDetails = DB::table('production_ng_logs')->where('no_produksi', $h->no_produksi_stamping)->get();
                        $h->ng_details = $ngDetails;
                    @endphp
                    <tr class="row-clickable" onclick="showHistoryDetail({{ json_encode($h) }})">
                        <td class="text-left pl-4">
                            <div class="text-dark">{{ date('d/m/y', strtotime($h->updated_at)) }}</div>
                            <div class="small text-primary font-mono">{{ date('H:i', strtotime($h->updated_at)) }}</div>
                        </td>
                        <td><span class="station-badge">{{ $h->kode_line ?? 'W-01' }}</span></td>
                        <td class="text-left">
                            <div class="font-weight-black text-primary font-mono" style="font-size: 12px;">{{ $h->no_produksi_stamping }}</div>
                            <div class="small text-dark font-weight-bold uppercase">{{ $h->part_no }}</div>
                        </td>
                        <td class="text-success font-weight-black">{{ number_format($h->qty_ok) }}</td>
                        <td class="text-danger font-weight-black">{{ number_format($h->qty_ng) }}</td>
                        <td>
                            <div style="width: 70px; margin: 0 auto; border: 1.5px solid {{ $batchYield >= 98 ? 'var(--brand-success)' : 'var(--brand-warning)' }}; border-radius: 8px; padding: 3px; font-family: 'JetBrains Mono'; font-weight: 800; font-size: 11px;">
                                {{ number_format($batchYield, 1) }}%
                            </div>
                        </td>
                        <td class="no-print text-right pr-4 text-muted"><i class="fas fa-external-link-alt"></i></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-5 text-muted font-weight-bold">NO_PRODUCTION_RECORDS_FOUND</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🛡️ SIGNATURE FOR PRINT --}}
<div class="print-only mt-5">
    <div class="row text-center">
        <div class="col-4"><p>Prepared by,</p><br><br><br><p>_________________</p><p>(Operator)</p></div>
        <div class="col-4"><p>Verified by QC,</p><br><br><br><p>_________________</p><p>(Inspector)</p></div>
        <div class="col-4"><p>Approved by,</p><br><br><br><p>_________________</p><p>(Supervisor)</p></div>
    </div>
</div>

<script>
    // 📊 CHART SCRIPT
    document.addEventListener("DOMContentLoaded", function() {
        const hist = @json($historyData->take(15)->reverse()->values());
        new ApexCharts(document.querySelector("#yieldChartArchive"), {
            series: [{
                name: 'Efficiency %',
                data: hist.map(h => {
                    const t = parseFloat(h.qty_ok) + parseFloat(h.qty_ng);
                    return t > 0 ? ((h.qty_ok / t) * 100).toFixed(1) : 0;
                })
            }],
            chart: { type: 'area', height: 280, toolbar: { show: false } },
            colors: ['#4361ee'],
            stroke: { curve: 'smooth', width: 3 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0 } },
            xaxis: { categories: hist.map(h => h.no_produksi_stamping.substr(-6)), labels: { style: { fontWeight: 800, fontSize: '9px' } } },
            yaxis: { max: 100, min: 50, labels: { style: { fontWeight: 800 } } }
        }).render();
    });

    // 🛡️ MODAL BRIDGE
    function showHistoryDetail(h) {
        // Jika fungsi showDetail ada di file utama, panggil rill
        if (typeof showDetail === "function") {
            showDetail(h);
        } else {
            console.error("Fungsi showDetail() tidak ditemukan di parent view.");
            alert("Detail lengkap hanya bisa dibuka dari Dashboard Terminal rill!");
        }
    }
</script>