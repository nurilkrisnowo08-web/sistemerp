@extends('layout.admin')

@section('content')
@php
    // Setup Tanggal dari Controller
    $startD = $startDate ?? date('Y-m-d', strtotime('-6 days'));
    $endD = $endDate ?? date('Y-m-d');
    
    if ($startD == $endD) {
        $periodString = date('d F Y', strtotime($startD));
    } else {
        $periodString = date('d M Y', strtotime($startD)) . ' - ' . date('d M Y', strtotime($endD));
    }

    // Kalkulasi Data untuk KPI Box
    $totalTake = 0; 
    $totalOk = 0; 
    $totalNg = 0; 
    $totalRet = 0;
    
    if(isset($histories) && count($histories) > 0) {
        foreach($histories as $h) {
            $totalTake += $h->qty_ambil_pcs;
            $totalRet += $h->qty_return_warehouse;
            $totalOk += $h->qty_hasil_ok;
            $totalNg += $h->qty_hasil_ng;

            if ($h->is_separating) {
                $totalOk += ($h->paired_ok ?? 0);
                $totalNg += ($h->paired_ng ?? 0);
            }
        }
    }

    $penyebut = $totalTake - $totalRet;
    $performance = $penyebut > 0 ? ($totalOk / $penyebut) * 100 : 0;
@endphp

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root { 
        --audit-blue: #2563eb; --audit-green: #059669; 
        --audit-red: #dc2626; --audit-gold: #d97706; --audit-slate: #475569;
        --audit-dark: #0f172a; --audit-bg: #f8fafc;
    }
    
    body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #f1f5f9; color: #1e293b; }
    .heading-audit { font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; letter-spacing: -0.5px; text-transform: uppercase; }

    /* 📊 STAT CARDS */
    .stat-card { background: #fff; border-radius: 20px; padding: 20px; border: 1px solid #e2e8f0; transition: 0.3s; position: relative; overflow: hidden; height: 100%; }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
    .stat-label { font-size: 10.5px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-value { font-family: 'JetBrains Mono', monospace; font-size: 26px; font-weight: 800; line-height: 1.2; margin-top: 4px; }
    .card-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 4px; }

    /* 🏛️ HUD TABLE */
    .terminal-card { background: #fff; border-radius: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04); border: 1px solid #e2e8f0; overflow: hidden; }
    .table-hud thead th { background: #f8fafc; color: #475569; font-size: 9.5px; text-transform: uppercase; letter-spacing: 1px; padding: 16px 12px; border: none; font-weight: 800; vertical-align: middle; }
    .table-hud tbody td { padding: 12px; vertical-align: top; border-top: 1px solid #f1f5f9; font-size: 11px; }
    .row-clickable:hover { background: #f8fafc !important; cursor: pointer; }

    /* 🏷️ BADGES & CHIPS */
    .coil-badge { font-family: 'JetBrains Mono', monospace; font-size: 8.5px; font-weight: 700; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 4px; padding: 2px 6px; display: inline-block; margin: 1px 0; white-space: nowrap; }
    .ng-chip { font-family: 'JetBrains Mono', monospace; font-size: 8.5px; font-weight: 700; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 4px; padding: 2px 6px; display: inline-block; margin: 1px; white-space: nowrap; }
    .pair-badge { font-size: 8px; font-weight: 800; background: #fdf4ff; color: #a21caf; border: 1px solid #f5d0fe; border-radius: 4px; padding: 1px 5px; text-transform: uppercase; }
    .cavity-pill { font-size: 8px; font-weight: 800; background: #e2e8f0; color: #334155; border-radius: 3px; padding: 1px 4px; }

    /* 🛡️ MODAL */
    .modal-audit { border-radius: 28px !important; border: none !important; }
    .kamar-ng { background: #f8fafc; border-radius: 16px; padding: 14px; border: 1px solid #e2e8f0; }

    /* 🖨️ PROFESSIONAL PRINT TEMPLATE */
    .print-template { display: none; }
    @media print {
        @page { size: A4 landscape; margin: 8mm; }
        .screen-only, .no-print, .main-sidebar, .main-header, .btn, .filter-bar, .modal, #sidebarToggle, .navbar { display: none !important; }
        body { background: white !important; margin: 0; padding: 0; font-size: 8.5px; }
        .print-template { display: block !important; width: 100% !important; color: #000 !important; }
        .print-header { border-bottom: 2.5px solid #000; margin-bottom: 12px; padding-bottom: 8px; }
        .print-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .print-table th { background: #f1f5f9 !important; border: 1px solid #000; padding: 6px 4px; font-size: 8px; text-transform: uppercase; font-weight: 800; }
        .print-table td { border: 1px solid #000; padding: 5px 4px; font-size: 8.5px; vertical-align: top; }
        .summary-box { border: 1px solid #000; padding: 6px 10px; text-align: center; width: 18%; font-weight: 800; font-size: 11px; font-family: 'JetBrains Mono', monospace; }
    }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">

    {{-- 🖨️ LEMBAR CETAK PROFESIONAL (A4 LANDSCAPE) --}}
    <div class="print-template">
        <div class="print-header">
            <table style="width: 100%;">
                <tr>
                    <td>
                        <h2 style="margin: 0; font-weight: 900; font-size: 20px;">PT ASALTA MANDIRI AGUNG</h2>
                        <p style="margin: 2px 0 0; font-size: 11px; font-weight: 700; color: #475569;">Stamping Production Audit & Traceability Report</p>
                    </td>
                    <td style="text-align: right; vertical-align: bottom;">
                        <p style="margin: 0; font-size: 11px;"><b>PERIOD:</b> {{ $periodString }}</p>
                        <p style="margin: 2px 0 0; font-size: 8px;">Printed on: {{ date('d/m/Y H:i') }}</p>
                    </td>
                </tr>
            </table>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 14px;">
            <div class="summary-box">TOTAL TAKE: {{ number_format($totalTake) }}</div>
            <div class="summary-box">TOTAL OK: {{ number_format($totalOk) }}</div>
            <div class="summary-box">TOTAL REJECT: {{ number_format($totalNg) }}</div>
            <div class="summary-box">RETURN RM: {{ number_format($totalRet) }}</div>
            <div class="summary-box">PERFORMANCE: {{ number_format($performance, 1) }}%</div>
        </div>

        <table class="print-table">
            <thead>
                <tr>
                    <th width="11%">Timestamp / Shift</th>
                    <th width="14%">Batch ID / Machine</th>
                    <th width="18%">Part Information</th>
                    <th width="18%">Coil / Material Terpakai</th>
                    <th width="6%">Take</th>
                    <th width="6%">OK</th>
                    <th width="6%">NG</th>
                    <th width="21%">Reject Detail & Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories as $h)
                <tr>
                    <td>
                        <b>{{ date('d/m/y H:i', strtotime($h->updated_at)) }}</b><br>
                        <span style="font-size: 7.5px;">Shift: {{ $h->shift }}</span>
                    </td>
                    <td>
                        <b style="font-family:'JetBrains Mono';">{{ $h->no_produksi }}</b><br>
                        <span style="font-size: 7.5px;">Line: {{ $h->kode_Line ?? 'LINE A' }}</span>
                    </td>
                    <td>
                        <b>{{ $h->material_code }}</b> 
                        <span class="cavity-pill">Cav:{{ $h->cavity ?? 1 }}</span><br>
                        <span style="font-size: 7.5px; color: #475569;">{{ $h->part_name ?? '-' }}</span>
                        @if($h->is_separating && $h->paired_part)
                            <div style="margin-top: 2px; border-top: 1px dashed #cbd5e1; padding-top: 2px;">
                                <span class="pair-badge">PAIR</span> <b>{{ $h->paired_part }}</b><br>
                                <span style="font-size: 7px; color: #475569;">{{ $h->paired_part_name ?? '-' }}</span>
                            </div>
                        @endif
                    </td>
                    <td>
                        @if(isset($h->coils_used) && count($h->coils_used) > 0)
                            @foreach($h->coils_used as $c)
                                <div style="font-family:'JetBrains Mono'; font-size:7.5px;">
                                    🏷️ {{ $c->coil_id }}: <b>{{ number_format($c->pcs_used) }} lbr</b>
                                </div>
                            @endforeach
                        @else
                            <span style="font-family:'JetBrains Mono'; font-size:7.5px;">{{ $h->coils_summary ?? '-' }}</span>
                        @endif
                    </td>
                    <td style="text-align: right; font-family:'JetBrains Mono'; font-weight:700;">
                        {{ number_format($h->qty_ambil_pcs) }}
                    </td>
                    <td style="text-align: right; font-family:'JetBrains Mono'; font-weight:700; color: #15803d;">
                        {{ number_format($h->qty_hasil_ok) }}
                        @if($h->is_separating)
                            <br><span style="color: #6b21a8;">+{{ number_format($h->paired_ok) }}</span>
                        @endif
                    </td>
                    <td style="text-align: right; font-family:'JetBrains Mono'; font-weight:700; color: #b91c1c;">
                        {{ number_format($h->qty_hasil_ng) }}
                        @if($h->is_separating)
                            <br><span style="color: #6b21a8;">+{{ number_format($h->paired_ng) }}</span>
                        @endif
                    </td>
                    <td>
                        @if(isset($h->ng_details) && count($h->ng_details) > 0)
                            <div style="font-family:'JetBrains Mono'; font-size:7.5px; color: #b91c1c; margin-bottom: 2px;">
                                <b>Defect:</b> {{ $h->ng_remark }}
                            </div>
                        @endif
                        @if($h->is_separating && !empty($h->paired_ng_remark) && $h->paired_ng_remark !== '-')
                            <div style="font-family:'JetBrains Mono'; font-size:7px; color: #6b21a8;">
                                <b>Pair Defect:</b> {{ $h->paired_ng_remark }}
                            </div>
                        @endif
                        @if(!empty($h->keterangan))
                            <div style="font-size: 7.5px; color: #1e293b; font-style: italic;">
                                📝 Note: {{ $h->keterangan }}
                            </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">Belum ada riwayat produksi pada periode ini</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div style="margin-top: 35px; display: flex; justify-content: space-around; text-align: center;">
            <div style="width: 180px; border-top: 1px solid #000; padding-top: 4px; font-weight: 800;">Production Supervisor</div>
            <div style="width: 180px; border-top: 1px solid #000; padding-top: 4px; font-weight: 800;">Quality Control</div>
            <div style="width: 180px; border-top: 1px solid #000; padding-top: 4px; font-weight: 800;">PPIC Department</div>
        </div>
    </div>

    {{-- 🖥️ TAMPILAN MONITOR (SCREEN VIEW) --}}
    <div class="screen-only">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="heading-audit m-0">QUALITY_AUDIT <span class="text-primary">SYSTEM</span></h2>
                <p class="text-muted small font-weight-bold mb-0 text-uppercase tracking-widest">Production Traceability Matrix // Period: {{ $periodString }}</p>
            </div>
            <div class="d-flex mt-3 mt-md-0">
                <form action="{{ route('produksi.history') }}" method="GET" class="d-flex align-items-center shadow-sm p-1 px-2 bg-white rounded-pill border mr-2">
                    <input type="date" name="start_date" value="{{ $startD }}" class="form-control form-control-sm border-0 bg-transparent shadow-none font-weight-bold" style="width: 135px;">
                    <span class="mx-1 text-muted font-weight-bold">to</span>
                    <input type="date" name="end_date" value="{{ $endD }}" class="form-control form-control-sm border-0 bg-transparent shadow-none font-weight-bold" style="width: 135px;">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 ml-2"><i class="fas fa-sync-alt"></i></button>
                </form>
                <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 shadow-sm font-weight-bold"><i class="fas fa-print mr-2"></i> PRINT REPORT</button>
            </div>
        </div>

        {{-- KPI STAT BOXES --}}
        <div class="row mb-4">
            <div class="col-md-3 mb-3"><div class="stat-card"><div class="card-strip bg-primary"></div><div class="stat-label">Total Material Take</div><div class="stat-value text-primary">{{ number_format($totalTake) }} <span style="font-size:12px;">lbr</span></div></div></div>
            <div class="col-md-2 mb-3"><div class="stat-card"><div class="card-strip bg-success"></div><div class="stat-label">Verified OK</div><div class="stat-value text-success">{{ number_format($totalOk) }}</div></div></div>
            <div class="col-md-2 mb-3"><div class="stat-card"><div class="card-strip bg-danger"></div><div class="stat-label">Total Reject</div><div class="stat-value text-danger">{{ number_format($totalNg) }}</div></div></div>
            <div class="col-md-2 mb-3"><div class="stat-card"><div class="card-strip bg-warning"></div><div class="stat-label">Returned RM</div><div class="stat-value text-warning">{{ number_format($totalRet) }}</div></div></div>
            <div class="col-md-3 mb-3"><div class="stat-card bg-dark text-white"><div class="stat-label text-white-50">Global Performance</div><div class="stat-value text-white">{{ number_format($performance, 1) }}%</div></div></div>
        </div>

        {{-- 📉 GRAFIK TREND PRODUKSI HARIAN --}}
        <div class="terminal-card p-4 mb-4 shadow-sm">
            <h6 class="font-weight-bold text-muted small text-uppercase mb-3 tracking-widest"><i class="fas fa-chart-line mr-2 text-primary"></i> Daily Production Trend Analysis</h6>
            <div id="trendChart"></div>
        </div>

        {{-- TABEL UTAMA SCREEN MONITOR --}}
        <div class="terminal-card shadow-sm mb-5">
            <div class="table-responsive">
                <table class="table table-hud mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Time / Machine</th>
                            <th>Batch ID</th>
                            <th>Part Details</th>
                            <th>Coil / Material Terpakai</th>
                            <th class="text-right">Take</th>
                            <th class="text-right text-success">OK</th>
                            <th class="text-right text-danger">NG</th>
                            <th>Defect Breakdown & Remark</th>
                            <th class="text-center">Yield</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($histories as $h)
                        @php 
                            $batchOk = (float)$h->qty_hasil_ok + ($h->is_separating ? ($h->paired_ok ?? 0) : 0);
                            $batchNg = (float)$h->qty_hasil_ng + ($h->is_separating ? ($h->paired_ng ?? 0) : 0);
                            $yld = ($batchOk + $batchNg) > 0 ? ($batchOk / ($batchOk +$batchNg)) * 100 : 0;
                        @endphp
                        <tr class="row-clickable" onclick='showDeepDive(@json($h))'>
                            <td class="pl-4">
                                <div class="font-weight-bold text-dark">{{ date('d/m/y H:i', strtotime($h->updated_at)) }}</div>
                                <div class="small text-muted font-weight-bold"><i class="fas fa-industry text-primary mr-1"></i> {{ $h->kode_Line ?? 'LINE' }} ({{ $h->shift }})</div>
                            </td>
                            <td>
                                <span class="font-family-jetbrains font-weight-bold text-primary">{{ $h->no_produksi }}</span>
                            </td>
                            <td>
                                <div class="font-weight-bold text-dark">
                                    {{ $h->material_code }} 
                                    <span class="cavity-pill ml-1">Cav:{{ $h->cavity ?? 1 }}</span>
                                </div>
                                <div class="small text-muted">{{ $h->part_name ?? '-' }}</div>

                                {{-- Jika Ada Separating Part (Ganda) --}}
                                @if($h->is_separating &&$h->paired_part)
                                    <div class="mt-1 pt-1 border-top">
                                        <span class="pair-badge">PAIR</span> 
                                        <span class="font-weight-bold text-dark">{{ $h->paired_part }}</span>
                                        <div class="small text-muted">{{ $h->paired_part_name ?? '-' }}</div>
                                    </div>
                                @endif
                            </td>
                            <td>
                                {{-- Rincian Coil Fisik yang Dipakai --}}
                                @if(isset($h->coils_used) && count($h->coils_used) > 0)
                                    <div class="d-flex flex-column">
                                        @foreach($h->coils_used as $c)
                                            <span class="coil-badge">
                                                🏷️ {{ $c->coil_id }}: <b>{{ number_format($c->pcs_used) }} lbr</b>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="coil-badge">🏷️ {{ $h->coils_summary ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold font-family-jetbrains">
                                {{ number_format($h->qty_ambil_pcs) }}
                            </td>
                            <td class="text-right font-weight-bold font-family-jetbrains text-success">
                                <div>{{ number_format($h->qty_hasil_ok) }}</div>
                                @if($h->is_separating)
                                    <div class="small text-purple" style="color: #9333ea;">+{{ number_format($h->paired_ok) }}</div>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold font-family-jetbrains text-danger">
                                <div>{{ number_format($h->qty_hasil_ng) }}</div>
                                @if($h->is_separating)
                                    <div class="small text-purple" style="color: #9333ea;">+{{ number_format($h->paired_ng) }}</div>
                                @endif
                            </td>
                            <td>
                                {{-- Rincian Jenis NG --}}
                                @if(isset($h->ng_details) && count($h->ng_details) > 0)
                                    <div class="d-flex flex-wrap mb-1">
                                        @foreach($h->ng_details as $ng)
                                            <span class="ng-chip">⚠️ {{ $ng->ng_type }}: {{$ng->qty }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Rincian Jenis NG Part Pasangan --}}
                                @if($h->is_separating && !empty($h->paired_ng_remark) &&$h->paired_ng_remark !== '-')
                                    <div class="small font-weight-bold" style="color: #9333ea;">
                                        <i class="fas fa-link mr-1"></i> Pair Defect: {{ $h->paired_ng_remark }}
                                    </div>
                                @endif

                                {{-- Tulisan Remark / Catatan Form --}}
                                @if(!empty($h->keterangan))
                                    <div class="small text-muted font-italic mt-1">
                                        <i class="far fa-comment-dots mr-1"></i> {{ $h->keterangan }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light border px-2 py-1 font-family-jetbrains font-weight-bold {{ $yld >= 90 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($yld, 1) }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="py-5 text-center text-muted italic">Belum ada riwayat produksi yang tercatat pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(isset($histories) && method_exists($histories, 'links'))
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $histories->appends(request()->input())->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- 🛡️ MODAL DETAIL DEEP-DIVE --}}
<div class="modal fade" id="deepDiveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-audit">
            <div class="modal-header bg-dark text-white border-0 py-3 px-4">
                <h5 class="modal-title font-weight-bold">Batch_Trace_Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="row align-items-center mb-4">
                    <div class="col-md-5 text-center border-right">
                        <div id="modal-donut-yield" style="min-height: 180px;"></div>
                        <h2 class="font-weight-bold m-0" id="det-yield-val">0%</h2>
                        <small class="stat-label">Batch Accuracy (Yield)</small>
                    </div>
                    <div class="col-md-7 pl-md-4">
                        <small class="stat-label">Batch Identifier</small>
                        <h5 class="font-weight-bold text-primary mb-3" id="det-batch"></h5>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <small class="stat-label">Primary Part</small>
                                <div class="font-weight-bold text-dark" id="det-part">-</div>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="stat-label">Machine / Line</small>
                                <div class="font-weight-bold text-dark" id="det-line">-</div>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="stat-label">Cavity</small>
                                <div class="font-weight-bold text-dark" id="det-cavity">1</div>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="stat-label">Finish Time</small>
                                <div class="font-weight-bold small text-muted" id="det-time">-</div>
                            </div>
                        </div>

                        {{-- Rincian Separating di Modal --}}
                        <div id="det-separating-box" class="p-2 bg-light rounded border mt-2" style="display: none;">
                            <small class="stat-label text-primary d-block font-weight-bold">🔗 Separating Pair Connected</small>
                            <span class="font-weight-bold small" id="det-pair-part">-</span>
                            <div class="d-flex gap-2 mt-1 small font-weight-bold">
                                <span class="text-success mr-3">OK: <span id="det-pair-ok">0</span></span>
                                <span class="text-danger">NG: <span id="det-pair-ng">0</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Baris KPI Mini --}}
                <div class="row mb-3">
                    <div class="col-3"><div class="kamar-ng text-center p-2"><small class="stat-label">Take</small><div class="h5 font-weight-bold m-0" id="det-take">0</div></div></div>
                    <div class="col-3"><div class="kamar-ng text-center p-2"><small class="stat-label text-success">OK Total</small><div class="h5 font-weight-bold text-success m-0" id="det-ok">0</div></div></div>
                    <div class="col-3"><div class="kamar-ng text-center p-2"><small class="stat-label text-danger">NG Total</small><div class="h5 font-weight-bold text-danger m-0" id="det-ng">0</div></div></div>
                    <div class="col-3"><div class="kamar-ng text-center p-2"><small class="stat-label text-info">Return</small><div class="h5 font-weight-bold text-info m-0" id="det-ret">0</div></div></div>
                </div>

                {{-- Rincian Coil / Bundle Fisik --}}
                <div class="p-3 bg-light rounded border mb-3">
                    <small class="stat-label d-block mb-2"><i class="fas fa-boxes mr-1 text-primary"></i> Material Coil/Bundle Breakdown</small>
                    <div id="det-coil-list" class="d-flex flex-wrap gap-2"></div>
                </div>

                {{-- Rincian Reject NG --}}
                <div class="p-3 bg-light rounded border mb-3">
                    <small class="stat-label d-block mb-2 text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Specific NG (Reject Types)</small>
                    <div id="det-ng-list" class="row"></div>
                </div>

                {{-- Remark / Keterangan Teks --}}
                <div class="p-3 bg-white rounded border">
                    <small class="stat-label d-block text-muted mb-1"><i class="far fa-sticky-note mr-1 text-warning"></i> Production Remarks & Notes</small>
                    <p class="mb-0 font-italic text-dark small" id="det-remark">-</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 📊 SCRIPT GRAFIK TREND PRODUKSI HARIAN
    const chartLabels = @json($chartLabels ?? []);
    const chartOk = @json($chartOk ?? []);
    const chartNg = @json($chartNg ?? []);
    const chartPerf = @json($chartPerf ?? []);

    if(chartLabels.length > 0) {
        new ApexCharts(document.querySelector("#trendChart"), {
            series: [
                { name: 'Performance %', type: 'line', data: chartPerf },
                { name: 'OK Units', type: 'area', data: chartOk },
                { name: 'NG Units', type: 'area', data: chartNg }
            ],
            chart: { height: 320, type: 'line', toolbar: { show: false } },
            stroke: { width: [3.5, 2, 2], curve: 'smooth' },
            colors: ['#2563eb', '#10b981', '#ef4444'],
            xaxis: { 
                categories: chartLabels, 
                tooltip: { enabled: false },
                labels: { style: { fontWeight: 700 } }
            },
            yaxis: [
                { title: { text: "Performance %", style: { fontWeight: 800 } }, min: 0, max: 100, labels: { formatter: (val) => val.toFixed(1) + "%" } }, 
                { opposite: true, title: { text: "Units (Pcs)", style: { fontWeight: 800 } }, min: 0, labels: { formatter: (val) => Math.round(val) } }
            ],
            legend: { position: 'top', fontWeight: 700 },
            fill: { opacity: [1, 0.15, 0.15] },
            dataLabels: { enabled: false }
        }).render();
    }

    // 🔍 SCRIPT MODAL DEEP-DIVE
    let donut = null;
    let tempDonutData = { ok: 0, ng: 0 };

    function showDeepDive(h) {
        const ok = (parseInt(h.qty_hasil_ok) || 0) + (h.is_separating ? (parseInt(h.paired_ok) || 0) : 0);
        const ng = (parseInt(h.qty_hasil_ng) || 0) + (h.is_separating ? (parseInt(h.paired_ng) || 0) : 0);
        const total = ok + ng;
        const yld = total > 0 ? Math.round((ok / total) * 100) : 0;
        
        tempDonutData = { ok: ok, ng: ng };

        document.getElementById('det-batch').innerText = h.no_produksi;
        document.getElementById('det-part').innerText = h.material_code + (h.part_name ? ' (' + h.part_name + ')' : '');
        document.getElementById('det-line').innerText = (h.kode_Line || 'LINE A') + ' - Shift ' + (h.shift || '1');
        document.getElementById('det-cavity').innerText = h.cavity || 1;
        document.getElementById('det-time').innerText = h.updated_at;
        document.getElementById('det-take').innerText = Number(h.qty_ambil_pcs).toLocaleString();
        document.getElementById('det-ok').innerText = Number(ok).toLocaleString();
        document.getElementById('det-ng').innerText = Number(ng).toLocaleString();
        document.getElementById('det-ret').innerText = Number(h.qty_return_warehouse || 0).toLocaleString();
        document.getElementById('det-yield-val').innerText = yld + "%";
        document.getElementById('det-yield-val').style.color = yld >= 90 ? '#10b981' : '#ef4444';
        
        // Catatan Remark
        document.getElementById('det-remark').innerText = h.keterangan ? h.keterangan : 'Tidak ada catatan remark khusus.';

        // Separating Pair
        const pairBox = document.getElementById('det-separating-box');
        if (h.is_separating && h.paired_part) {
            pairBox.style.display = 'block';
            document.getElementById('det-pair-part').innerText = h.paired_part + (h.paired_part_name ? ' - ' + h.paired_part_name : '');
            document.getElementById('det-pair-ok').innerText = Number(h.paired_ok || 0).toLocaleString();
            document.getElementById('det-pair-ng').innerText = Number(h.paired_ng || 0).toLocaleString();
        } else {
            pairBox.style.display = 'none';
        }

        // Coil List
        let coilHtml = '';
        if (h.coils_used && h.coils_used.length > 0) {
            h.coils_used.forEach(c => {
                coilHtml += `<span class="coil-badge mr-1 mb-1">
                                🏷️ ${c.coil_id}: <b>${Number(c.pcs_used).toLocaleString()} lbr</b>
                             </span>`;
            });
        } else {
            coilHtml = `<span class="coil-badge">🏷️ ${h.coils_summary || '-'}</span>`;
        }
        document.getElementById('det-coil-list').innerHTML = coilHtml;

        // Reject NG List
        let ngHtml = '';
        if(h.ng_details && h.ng_details.length > 0) {
            h.ng_details.forEach(item => {
                ngHtml += `<div class="col-6 col-md-4 mb-2">
                            <div class="p-2 border rounded font-weight-bold" style="font-size: 11px; background:#fff; color:#b91c1c;">
                                ⚠️ ${item.ng_type}: ${item.qty} Pcs
                            </div>
                           </div>`;
            });
        } else {
            ngHtml = '<div class="col-12"><p class="mb-0 small text-muted font-italic">Tidak ada reject spesifik yang tercatat (Good Batch).</p></div>';
        }
        document.getElementById('det-ng-list').innerHTML = ngHtml;

        $('#deepDiveModal').modal('show');
    }

    $('#deepDiveModal').on('shown.bs.modal', function () {
        if(donut) donut.destroy();
        donut = new ApexCharts(document.querySelector("#modal-donut-yield"), {
            series: [tempDonutData.ok, tempDonutData.ng],
            chart: { type: 'donut', width: 180 },
            colors: ['#10b981', '#ef4444'],
            labels: ['Good OK', 'Reject NG'],
            legend: { show: false },
            plotOptions: { pie: { donut: { size: '72%' } } },
            dataLabels: { enabled: false }
        });
        donut.render();
    });
</script>
@endsection