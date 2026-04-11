@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&family=Inter:wght@400;700;900&display=swap" rel="stylesheet">

<style>
    /* ============================================================
       🏗️ UI THEME: MODERN INDUSTRIAL PRO (WHITE)
       ============================================================ */
    :root { --ind-steel: #4e73df; --ind-success: #1cc88a; --ind-danger: #e74a3b; --ind-warning: #f6c23e; }
    
    .main-terminal { background-color: #f8f9fc; min-height: 100vh; padding: 2rem; color: #2d3436; }
    .animasi-masuk { animation: fadeInUp 0.5s ease-out both; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .terminal-card { background: #fff; border: 1px solid #e3e6f0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; }
    .table-hud thead th { background: #f8f9fc; color: var(--ind-steel); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; padding: 18px; border-bottom: 2px solid #eaecf4; }
    .row-clickable { cursor: pointer; transition: all 0.2s; border-bottom: 1px solid #f1f2f6; }
    .row-clickable:hover { background-color: #f1f4ff !important; transform: scale(1.002); }

    /* 🍩 DONUT CHART KPI ANIMATED */
    .donut-container {
        width: 130px; height: 130px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: conic-gradient(var(--c) var(--p), #eaecf4 0);
        margin: 0 auto; position: relative;
        animation: donutRotate 1.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }
    @keyframes donutRotate { from { transform: scale(0.5) rotate(-90deg); opacity: 0; } to { transform: scale(1) rotate(0deg); opacity: 1; } }
    .donut-container::after { content: ""; position: absolute; width: 100px; height: 100px; background: #fff; border-radius: 50%; }
    .donut-text { position: relative; z-index: 2; font-weight: 800; font-size: 18px; color: var(--c); font-family: 'Roboto Mono'; }

    /* ============================================================
       🖨️ PRINT RULES (A4 PORTRAIT ISO STANDARD)
       ============================================================ */
    #print-area { display: none; }
    @media print {
        @page { size: A4 portrait; margin: 15mm; }
        .no-print, .sidebar, .navbar, .modal, .btn { display: none !important; }
        #print-area { display: block !important; padding: 0; color: #000; font-family: 'Arial', sans-serif; }
        .kop-surat { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .table-print { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table-print th, .table-print td { border: 1pt solid black; padding: 8px; font-size: 9pt; text-align: center; color: #000 !important; }
        .table-print th { background-color: #f2f2f2 !important; font-weight: bold; }
        .font-bold { font-weight: bold; }
        .ttd-box { float: right; width: 220px; text-align: center; margin-top: 40px; font-size: 10pt; }
    }
</style>

<div class="main-terminal text-left animasi-masuk no-print">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h3 class="font-weight-bold" style="letter-spacing: -1.5px; color: var(--ind-steel); font-size: 26px;">PRODUCTION_HISTORY</h3>
            <p class="text-muted small font-weight-bold mb-0">PT. ASALTA MANDIRI AGUNG // SHEET_LEVEL_AUDIT</p>
        </div>
        <div class="hud-actions">
            <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 font-weight-bold shadow-sm mr-2">
                <i class="fas fa-print mr-2"></i> GENERATE_PDF
            </button>
            <a href="{{ route('produksi.index') }}" class="btn btn-outline-primary rounded-pill px-4 font-weight-bold shadow-sm border-2">
                <i class="fas fa-desktop mr-2 text-primary"></i> MONITORING
            </a>
        </div>
    </div>

    <div class="terminal-card">
        <div class="table-responsive">
            <table class="table table-hud mb-0 text-center">
                <thead>
                    <tr>
                        <th>WAKTU</th>
                        <th>BATCH_NO</th>
                        <th>PART_IDENTIFIER</th>
                        <th>OK (PCS)</th>
                        <th>NG (PCS)</th>
                        <th>YIELD</th>
                    </tr>
                </thead>
                <tbody id="historyLogBody">
                    @foreach($history as $h)
                    @php 
                        $yield = ($h->qty_ambil_pcs > 0) ? ($h->qty_hasil_ok / $h->qty_ambil_pcs) * 100 : 0;
                        $color = ($yield >= 95) ? 'var(--ind-success)' : (($yield >= 85) ? 'var(--ind-warning)' : 'var(--ind-danger)');
                    @endphp
                    <tr class="row-clickable" data-toggle="modal" data-target="#modalDetail{{ $h->id }}">
                        <td class="text-muted small">{{ date('d/m/y H:i', strtotime($h->updated_at)) }}</td>
                        <td class="font-weight-bold text-primary">{{ $h->no_production ?? $h->no_produksi }}</td>
                        <td class="text-left font-weight-bold pl-4">> {{ $h->material_code }}</td>
                        <td class="text-success font-weight-bold">{{ number_format($h->qty_hasil_ok) }}</td>
                        <td class="text-danger font-weight-bold">{{ number_format($h->qty_ng_material + $h->qty_ng_process) }}</td>
                        <td><b style="color: {{ $color }}; font-size: 15px;">{{ number_format($yield, 1) }}%</b></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🛡️ DETAIL MODAL (ANIMASI DONUT & BREAKDOWN NG) --}}
@foreach($history as $h)
@php 
    $yield = ($h->qty_ambil_pcs > 0) ? ($h->qty_hasil_ok / $h->qty_ambil_pcs) * 100 : 0;
    $chartColor = ($yield >= 95) ? '#1cc88a' : (($yield >= 85) ? '#f6c23e' : '#e74a3b');
@endphp
<div class="modal fade" id="modalDetail{{ $h->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content shadow-lg border-0" style="border-radius: 24px; overflow: hidden;">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h6 class="modal-title font-weight-bold uppercase tracking-widest">Audit Batch // ID: {{ $h->id }}</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4 text-left bg-white">
                <div class="text-center mb-4">
                    <div class="donut-container" style="--p: {{ $yield }}%; --c: {{ $chartColor }};">
                        <div class="donut-text">{{ number_format($yield, 0) }}%</div>
                    </div>
                    <small class="font-weight-bold text-muted d-block mt-3 uppercase">Production Accuracy</small>
                </div>

                <div class="row mb-3 text-center small font-weight-bold">
                    <div class="col-6 pr-1"><div class="p-3 bg-light rounded border-bottom border-primary"><small class="text-muted d-block uppercase" style="font-size: 8px;">Target Actual</small><b class="text-primary">{{ number_format($h->qty_ambil_pcs) }}</b></div></div>
                    <div class="col-6 pl-1"><div class="p-3 bg-light rounded border-bottom border-success"><small class="text-muted d-block uppercase" style="font-size: 8px;">Passed Good</small><b class="text-success">{{ number_format($h->qty_hasil_ok) }}</b></div></div>
                </div>

                {{-- NG Breakdown Detail --}}
                <div class="p-4 border rounded bg-white shadow-sm">
                    <h6 class="font-weight-bold text-danger small mb-3 uppercase border-bottom pb-2">Reject Breakdown Details</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small font-weight-bold text-muted">1. NG Material (Defect Bahan)</span>
                        <b class="text-dark font-family-mono">{{ $h->qty_ng_material }} Pcs</b>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="small font-weight-bold text-muted">2. NG Process (Kesalahan Mesin)</span>
                        <b class="text-dark font-family-mono">{{ $h->qty_ng_process }} Pcs</b>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- 🖨️ PRINT AREA (A4 PORTRAIT) --}}
<div id="print-area">
    <div class="kop-surat">
        <h2 style="font-family: Arial Black, sans-serif; letter-spacing: -1px;">PT. ASALTA MANDIRI AGUNG</h2>
        <p>JL. RAYA JAKARTA-BOGOR KM. 49 (JLN. RODA PEMBANGUNAN) BOGOR</p>
        <p>LAPORAN DETAIL HISTORY PRODUKSI BATCH // PERIODE: {{ date('F Y') }}</p>
    </div>

    <table class="table-print">
        <thead>
            <tr>
                <th rowspan="2" style="width: 15%">BATCH NO</th>
                <th rowspan="2" style="width: 25%">PART NUMBER</th>
                <th colspan="3">MUTASI PRODUKSI (PCS)</th>
                <th rowspan="2">YIELD</th>
                <th colspan="2">DETIL REJECT/NG</th>
            </tr>
            <tr>
                <th>AMBIL</th>
                <th>GOOD OK</th>
                <th>NG TOT</th>
                <th>MATERIAL</th>
                <th>PROCESS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history as $h)
            <tr>
                <td class="font-bold">{{ $h->no_production ?? $h->no_produksi }}</td>
                <td class="text-left font-bold">{{ $h->material_code }}</td>
                <td>{{ number_format($h->qty_ambil_pcs) }}</td>
                <td class="font-bold text-success">+{{ number_format($h->qty_hasil_ok) }}</td>
                <td class="font-bold text-danger">-{{ number_format($h->qty_ng_material + $h->qty_ng_process) }}</td>
                <td class="font-bold">{{ number_format(($h->qty_ambil_pcs > 0 ? ($h->qty_hasil_ok/$h->qty_ambil_pcs)*100 : 0), 1) }}%</td>
                {{-- Penjelasan NG detail --}}
                <td>{{ $h->qty_ng_material }}</td>
                <td>{{ $h->qty_ng_process }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="ttd-box">
        <p>Bogor, {{ date('d F Y') }}</p>
        <p style="margin-top: 60px;">( ................................. )</p>
        <p>PIC PRODUKSI</p>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function(){
        $("#searchLog").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#historyLogBody tr").filter(function() { $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1) });
        });
    });
</script>
@endsection