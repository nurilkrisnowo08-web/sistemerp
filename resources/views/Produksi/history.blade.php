@extends('layout.admin')

@section('content')
@php
    // Setup Tanggal
    $startD = $startDate ?? date('Y-m-d', strtotime('-6 days'));
    $endD = $endDate ?? date('Y-m-d');
    
    if ($startD == $endD) {
        $periodString = date('d F Y', strtotime($startD));
    } else {
        $periodString = date('d M Y', strtotime($startD)) . ' - ' . date('d M Y', strtotime($endD));
    }

    // AMBIL DATA UNTUK DROPDOWN FILTER
    $filterCustomers = \DB::table('customers')->select('code', 'name')->orderBy('code', 'asc')->get();
    $filterParts = \DB::table('parts')->select('part_no', 'customer_code')->orderBy('part_no', 'asc')->get();
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
    .heading-audit { font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 900; letter-spacing: -0.5px; text-transform: uppercase; }

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
    .coil-badge { font-family: 'JetBrains Mono', monospace; font-size: 8.5px; font-weight: 700; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 4px; padding: 3px 6px; display: inline-block; margin: 2px 0; white-space: nowrap; }
    .ng-chip { font-family: 'JetBrains Mono', monospace; font-size: 8.5px; font-weight: 700; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 4px; padding: 3px 6px; display: inline-block; margin: 2px; white-space: nowrap; }
    .pair-badge { font-size: 8px; font-weight: 800; background: #fdf4ff; color: #a21caf; border: 1px solid #f5d0fe; border-radius: 4px; padding: 2px 5px; text-transform: uppercase; }
    .cavity-pill { font-size: 8px; font-weight: 800; background: #e2e8f0; color: #334155; border-radius: 4px; padding: 2px 5px; }

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
        
        .print-header-container { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 15px; }
        .print-logo-section { display: flex; align-items: center; gap: 15px; }
        .print-logo-section img { height: 42px; }
        .print-company-info h2 { margin: 0; font-size: 16px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; }
        .print-company-info p { margin: 2px 0 0; font-size: 10px; font-weight: 700; color: #475569; }
        
        .print-meta { text-align: right; line-height: 1.4; }
        
        .print-recap-grid { display: flex; justify-content: space-between; margin-bottom: 15px; gap: 10px; }
        .summary-box { flex: 1; border: 1px solid #000; border-radius: 4px; overflow: hidden; text-align: center; }
        .summary-title { background: #f1f5f9; padding: 4px; font-size: 8px; font-weight: 800; border-bottom: 1px solid #000; text-transform: uppercase; }
        .summary-value { padding: 6px; font-size: 12px; font-weight: 900; font-family: 'JetBrains Mono', monospace; }
        
        .print-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .print-table th { background: #f8fafc !important; border: 1px solid #000; padding: 6px 4px; font-size: 8px; text-transform: uppercase; font-weight: 800; }
        .print-table td { border: 1px solid #000; padding: 5px 4px; font-size: 8.5px; vertical-align: top; }
        
        .split-line { border-top: 1px dashed #cbd5e1; display: block; padding-top: 2px; margin-top: 2px; }
    }
    
    .split-line { border-top: 1px dashed #cbd5e1; display: block; padding-top: 4px; margin-top: 4px; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">

    {{-- 🖨️ LEMBAR CETAK PROFESIONAL (A4 LANDSCAPE) --}}
    <div class="print-template">
        <div class="print-header-container">
            <div class="print-logo-section">
                <!-- Gunakan Asset Logo Perusahaan -->
                <img src="{{ asset('public/admin/img/Logo-asalta.png') }}" alt="Logo Asalta" onerror="this.style.display='none'">
                <div class="print-company-info">
                    <h2>PT ASALTA MANDIRI AGUNG</h2>
                    <p>STAMPING PRODUCTION AUDIT & TRACEABILITY REPORT</p>
                </div>
            </div>
            <div class="print-meta">
                <p style="margin: 0; font-size: 11px;"><b>PERIOD DATE:</b> {{ strtoupper($periodString) }}</p>
                <p style="margin: 2px 0 0; font-size: 8px;">Printed on: {{ date('d M Y, H:i') }}</p>
            </div>
        </div>

        <!-- ✨ DATA MENGGUNAKAN VARIABEL GLOBAL DARI CONTROLLER ✨ -->
        <div class="print-recap-grid">
            <div class="summary-box"><div class="summary-title">Total Material Take</div><div class="summary-value">{{ number_format($globalTakePcs ?? 0) }} Pcs</div></div>
            <div class="summary-box"><div class="summary-title">Verified OK</div><div class="summary-value">{{ number_format($globalOk ?? 0) }}</div></div>
            <div class="summary-box"><div class="summary-title">Total Reject NG</div><div class="summary-value">{{ number_format($globalNg ?? 0) }}</div></div>
            <div class="summary-box"><div class="summary-title">Returned RM</div><div class="summary-value">{{ number_format($globalRet ?? 0) }} Pcs</div></div>
            <div class="summary-box"><div class="summary-title">Global Performance</div><div class="summary-value">{{ number_format($globalPerf ?? 0, 1) }}%</div></div>
        </div>

        <table class="print-table">
            <thead>
                <tr>
                    <th width="11%">Plan Date / Shift</th>
                    <th width="13%">Batch ID / Machine</th>
                    <th width="17%">Part Information</th>
                    <th width="17%">Coil / Material Terpakai</th>
                    <th width="5%">Take</th>
                    <th width="5%">OK</th>
                    <th width="5%">NG</th>
                    <th width="5%">RET</th>
                    <th width="22%">Reject Detail & Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories as $h)
                @php 
                    $isFiltered = !empty($partFilter);
                    $isFilteredToMain = $isFiltered && ($partFilter == $h->material_code);
                    $isFilteredToSub = $isFiltered && ($partFilter == $h->paired_part);
                    
                    $batchTake = $h->qty_ambil_pcs;
                    $batchRet = $h->qty_return_warehouse ?? 0;
                    $batchEffTake = max(0, $batchTake - $batchRet);

                    if ($isFilteredToMain) {
                        $okToShow = $h->qty_hasil_ok;
                        $ngToShow = $h->qty_hasil_ng;
                        $takeToShow = $batchEffTake;
                    } elseif ($isFilteredToSub) {
                        $okToShow = $h->paired_ok ?? 0;
                        $ngToShow = $h->paired_ng ?? 0;
                        $takeToShow = $batchEffTake;
                    } else {
                        $okToShow = clone($h); // Flag untuk tampilan normal ganda
                    }
                    
                    if ($h->is_separating) {
                        $p1Split = explode('-', $h->material_code);
                        $p1Label = substr($p1Split[0], -2);
                        
                        $p2Split = explode('-', $h->paired_part);
                        $p2Label = substr($p2Split[0], -2);
                    }
                @endphp
                <tr>
                    <td>
                        <b>{{ date('d/m/Y', strtotime($h->plan_date ?? $h->created_at)) }}</b><br>
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
                            <div class="split-line">
                                <span class="pair-badge">PAIR</span> <b>{{ $h->paired_part }}</b><br>
                                <span style="font-size: 7px; color: #475569;">{{ $h->paired_part_name ?? '-' }}</span>
                            </div>
                        @endif
                    </td>
                    <td>
                        @if(isset($h->coils_used) && count($h->coils_used) > 0)
                            @foreach($h->coils_used as $c)
                                <div style="font-family:'JetBrains Mono'; font-size:7.5px; margin-bottom: 2px;">
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
                        @if($isFilteredToMain)
                            {{ number_format($okToShow) }}<br><span style="font-size: 6px;">(Filtered: {{ $p1Label }})</span>
                        @elseif($isFilteredToSub)
                            {{ number_format($okToShow) }}<br><span style="font-size: 6px;">(Filtered: {{ $p2Label }})</span>
                        @elseif($h->is_separating)
                            <b>{{ $p1Label }}:</b> {{ number_format($h->qty_hasil_ok) }}
                            <div class="split-line text-purple" style="color: #9333ea;">
                                <b>{{ $p2Label }}:</b> {{ number_format($h->paired_ok) }}
                            </div>
                        @else
                            {{ number_format($h->qty_hasil_ok) }}
                        @endif
                    </td>
                    <td style="text-align: right; font-family:'JetBrains Mono'; font-weight:700; color: #b91c1c;">
                        @if($isFilteredToMain)
                            {{ number_format($ngToShow) }}<br><span style="font-size: 6px;">(Filtered: {{ $p1Label }})</span>
                        @elseif($isFilteredToSub)
                            {{ number_format($ngToShow) }}<br><span style="font-size: 6px;">(Filtered: {{ $p2Label }})</span>
                        @elseif($h->is_separating)
                            <b>{{ $p1Label }}:</b> {{ number_format($h->qty_hasil_ng) }}
                            <div class="split-line text-purple" style="color: #9333ea;">
                                <b>{{ $p2Label }}:</b> {{ number_format($h->paired_ng) }}
                            </div>
                        @else
                            {{ number_format($h->qty_hasil_ng) }}
                        @endif
                    </td>
                    <td style="text-align: right; font-family:'JetBrains Mono'; font-weight:700; color: #d97706;">
                        {{ number_format($h->qty_return_warehouse) }}
                    </td>
                    <td>
                        @if(isset($h->ng_details) && count($h->ng_details) > 0)
                            <div style="font-family:'JetBrains Mono'; font-size:7.5px; color: #b91c1c; margin-bottom: 2px;">
                                <b>Defect:</b> {{ $h->ng_remark }}
                            </div>
                        @endif
                        @if($h->is_separating && !empty($h->paired_ng_remark) && $h->paired_ng_remark !== '-')
                            <div style="font-family:'JetBrains Mono'; font-size:7px; color: #6b21a8; margin-bottom: 2px;">
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
                    <td colspan="9" style="text-align: center; padding: 20px;">Belum ada riwayat produksi pada periode ini</td>
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
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            
            <div class="mb-3 mb-md-0 d-flex align-items-center">
                <a href="{{ route('produksi.index') }}" class="btn btn-light rounded-circle shadow-sm border mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="Kembali ke Terminal">
                    <i class="fas fa-arrow-left text-primary"></i>
                </a>
                <div>
                    <h2 class="heading-audit m-0">QUALITY_AUDIT <span class="text-primary">SYSTEM</span></h2>
                    <p class="text-muted small font-weight-bold mb-0 text-uppercase tracking-widest">Production Traceability Matrix // Period: {{ $periodString }}</p>
                </div>
            </div>
            
            <div class="d-flex flex-column gap-2">
                {{-- Form Filter Periode, Customer, dan Part --}}
                <form action="{{ route('produksi.history') }}" method="GET" class="d-flex flex-wrap align-items-center bg-white shadow-sm border p-1" style="border-radius: 12px; gap: 4px;">
                    <input type="date" name="start_date" value="{{ $startD }}" class="form-control form-control-sm border-0 bg-transparent font-weight-bold" style="width: 125px; outline: none; box-shadow: none;">
                    <span class="text-muted font-weight-bold small">to</span>
                    <input type="date" name="end_date" value="{{ $endD }}" class="form-control form-control-sm border-0 bg-transparent font-weight-bold" style="width: 125px; outline: none; box-shadow: none;">
                    
                    <div class="border-left pl-2 d-flex gap-1">
                        {{-- DROPDOWN CUSTOMER --}}
                        <select name="customer" id="filter_customer" class="form-control form-control-sm font-weight-bold text-dark" style="width: 110px; background: var(--ios-bg); border-radius: 6px; border: 1px solid var(--ios-border);">
                            <option value="">All Cust</option>
                            @foreach($filterCustomers as $c)
                                <option value="{{ trim($c->code) }}" {{ request('customer') == trim($c->code) ? 'selected' : '' }}>
                                    {{ strtoupper($c->code) }}
                                </option>
                            @endforeach
                        </select>

                        {{-- DROPDOWN PART NO (Akan difilter oleh JS) --}}
                        <select name="part_no" id="filter_part" class="form-control form-control-sm font-weight-bold text-dark" style="width: 160px; background: var(--ios-bg); border-radius: 6px; border: 1px solid var(--ios-border);">
                            <option value="">All Part</option>
                            @foreach($filterParts as $p)
                                <option value="{{ $p->part_no }}" data-customer="{{ trim($p->customer_code) }}" {{ request('part_no') == $p->part_no ? 'selected' : '' }}>
                                    {{ $p->part_no }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm px-3 ml-1 font-weight-bold" style="border-radius: 8px;"><i class="fas fa-filter mr-1"></i> Filter</button>
                    <button type="button" onclick="window.print()" class="btn btn-dark btn-sm px-3 ml-1 font-weight-bold" style="border-radius: 8px;"><i class="fas fa-print mr-1"></i> Print</button>
                </form>
            </div>
        </div>

        {{-- KPI STAT BOXES --}}
        <div class="row mb-4">
            <div class="col-md-3 mb-3"><div class="stat-card"><div class="card-strip bg-primary"></div><div class="stat-label">Total Material Take</div><div class="stat-value text-primary">{{ number_format($globalTakePcs ?? 0) }} <span style="font-size:12px;">pcs</span></div></div></div>
            <div class="col-md-2 mb-3"><div class="stat-card"><div class="card-strip bg-success"></div><div class="stat-label">Verified OK</div><div class="stat-value text-success">{{ number_format($globalOk ?? 0) }}</div></div></div>
            <div class="col-md-2 mb-3"><div class="stat-card"><div class="card-strip bg-danger"></div><div class="stat-label">Total Reject</div><div class="stat-value text-danger">{{ number_format($globalNg ?? 0) }}</div></div></div>
            <div class="col-md-2 mb-3"><div class="stat-card"><div class="card-strip bg-warning"></div><div class="stat-label">Returned RM</div><div class="stat-value text-warning">{{ number_format($globalRet ?? 0) }} <span style="font-size:12px;">pcs</span></div></div></div>
            <div class="col-md-3 mb-3"><div class="stat-card bg-dark text-white"><div class="stat-label text-white-50">Global Performance</div><div class="stat-value text-white">{{ number_format($globalPerf ?? 0, 1) }}%</div></div></div>
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
                            <th class="pl-4">Plan Date / Time</th>
                            <th>Batch ID</th>
                            <th>Part Details</th>
                            <th>Coil / Material Terpakai</th>
                            <th class="text-right">Take</th>
                            <th class="text-right text-success">OK</th>
                            <th class="text-right text-danger">NG</th>
                            <th class="text-right text-warning">Return</th>
                            <th>Defect Breakdown & Remark</th>
                            <th class="text-center">Yield</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($histories as $h)
                        @php 
                            // ✨ YIELD DI TABEL MURNI MENGGUNAKAN FILTER ✨
                            $isFiltered = !empty($partFilter);
                            $isFilteredToMain = $isFiltered && ($partFilter == $h->material_code);
                            $isFilteredToSub = $isFiltered && ($partFilter == $h->paired_part);
                            
                            $batchTake = $h->qty_ambil_pcs;
                            $batchRet = $h->qty_return_warehouse ?? 0;
                            $batchEffTake = max(0, $batchTake - $batchRet);

                            // Jika filter pada Part tertentu, hanya baca variabel tersebut
                            if ($isFilteredToMain) {
                                $okToShow = $h->qty_hasil_ok;
                                $ngToShow = $h->qty_hasil_ng;
                                $takeToShow = $batchEffTake;
                            } elseif ($isFilteredToSub) {
                                $okToShow = $h->paired_ok ?? 0;
                                $ngToShow = $h->paired_ng ?? 0;
                                $takeToShow = $batchEffTake;
                            } else {
                                $okToShow = clone($h); // Placeholder objek biar bisa dicek array-nya di bawah
                                $takeToShow = $batchEffTake * ($h->is_separating ? 2 : 1);
                            }
                            
                            // Hitung Yield Baris
                            if ($isFilteredToMain || $isFilteredToSub) {
                                $totalOutput = $okToShow + $ngToShow;
                                $yld = $totalOutput > 0 ? ($okToShow / $totalOutput) * 100 : 0;
                                if ($yld > 99.9 && $ngToShow > 0) $yld = 99.9;
                            } else {
                                $totalBatchOk = (float)$h->qty_hasil_ok + ($h->is_separating ? ($h->paired_ok ?? 0) : 0);
                                $totalBatchNg = (float)$h->qty_hasil_ng + ($h->is_separating ? ($h->paired_ng ?? 0) : 0);
                                $totalOutput = $totalBatchOk + $totalBatchNg;
                                $yld = $totalOutput > 0 ? ($totalBatchOk / $totalOutput) * 100 : 0;
                                if ($yld > 99.9 && $totalBatchNg > 0) $yld = 99.9;
                            }

                            if ($h->is_separating) {
                                $p1Split = explode('-', $h->material_code);
                                $p1Label = substr($p1Split[0], -2);
                                
                                $p2Split = explode('-', $h->paired_part);
                                $p2Label = substr($p2Split[0], -2);
                            }
                        @endphp
                        <tr class="row-clickable" onclick='showDeepDive(@json($h), {{ $isFilteredToMain || $isFilteredToSub ? $okToShow : ($h->qty_hasil_ok + ($h->paired_ok ?? 0)) }}, {{ $isFilteredToMain || $isFilteredToSub ? $ngToShow : ($h->qty_hasil_ng + ($h->paired_ng ?? 0)) }}, {{ $yld }}, {{ $takeToShow }}, "{{ $isFilteredToMain || $isFilteredToSub ? ($isFilteredToMain ? $p1Label : $p2Label) : "ALL" }}")'>
                            <td class="pl-4">
                                <div class="font-weight-bold text-dark"><i class="far fa-calendar-check text-success mr-1"></i> {{ date('d M Y', strtotime($h->plan_date ?? $h->created_at)) }}</div>
                                <div class="small text-muted font-weight-bold"><i class="fas fa-clock text-primary mr-1"></i> Input: {{ date('H:i', strtotime($h->updated_at)) }}</div>
                                <div class="small text-muted font-weight-bold mt-1"><i class="fas fa-industry text-secondary mr-1"></i> {{ $h->kode_Line ?? 'LINE' }} ({{ $h->shift }})</div>
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

                                @if($h->is_separating &&$h->paired_part)
                                    <div class="mt-1 pt-1 border-top">
                                        <span class="pair-badge">PAIR</span> 
                                        <span class="font-weight-bold text-dark">{{ $h->paired_part }}</span>
                                        <div class="small text-muted">{{ $h->paired_part_name ?? '-' }}</div>
                                    </div>
                                @endif
                            </td>
                            <td>
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
                                {{-- Menampilkan OK Sesuai Filter atau Default Gabungan (Kiri-Kanan) --}}
                                @if($isFilteredToMain)
                                    <span style="font-size: 14px;">{{ number_format($okToShow) }}</span>
                                    <div class="small text-muted font-weight-normal" style="font-size: 9px;">(Filtered: {{ $p1Label }})</div>
                                @elseif($isFilteredToSub)
                                    <span style="font-size: 14px;">{{ number_format($okToShow) }}</span>
                                    <div class="small text-muted font-weight-normal" style="font-size: 9px;">(Filtered: {{ $p2Label }})</div>
                                @elseif($h->is_separating)
                                    <div style="font-size: 13px;"><b>{{ $p1Label }}:</b> {{ number_format($h->qty_hasil_ok) }}</div>
                                    <div class="split-line text-purple" style="font-size: 13px; color: #9333ea;">
                                        <b>{{ $p2Label }}:</b> {{ number_format($h->paired_ok) }}
                                    </div>
                                @else
                                    <span style="font-size: 14px;">{{ number_format($h->qty_hasil_ok) }}</span>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold font-family-jetbrains text-danger">
                                {{-- Menampilkan NG Sesuai Filter atau Default Gabungan (Kiri-Kanan) --}}
                                @if($isFilteredToMain)
                                    <span style="font-size: 14px;">{{ number_format($ngToShow) }}</span>
                                    <div class="small text-muted font-weight-normal" style="font-size: 9px;">(Filtered: {{ $p1Label }})</div>
                                @elseif($isFilteredToSub)
                                    <span style="font-size: 14px;">{{ number_format($ngToShow) }}</span>
                                    <div class="small text-muted font-weight-normal" style="font-size: 9px;">(Filtered: {{ $p2Label }})</div>
                                @elseif($h->is_separating)
                                    <div style="font-size: 13px;"><b>{{ $p1Label }}:</b> {{ number_format($h->qty_hasil_ng) }}</div>
                                    <div class="split-line text-purple" style="font-size: 13px; color: #9333ea;">
                                        <b>{{ $p2Label }}:</b> {{ number_format($h->paired_ng) }}
                                    </div>
                                @else
                                    <span style="font-size: 14px;">{{ number_format($h->qty_hasil_ng) }}</span>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold font-family-jetbrains text-warning">
                                {{ number_format($h->qty_return_warehouse) }}
                            </td>
                            <td>
                                @if(isset($h->ng_details) && count($h->ng_details) > 0)
                                    <div class="d-flex flex-wrap mb-1">
                                        @foreach($h->ng_details as $ng)
                                            <span class="ng-chip">⚠️ {{ $ng->ng_type }}: {{$ng->qty }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                @if($h->is_separating && !empty($h->paired_ng_remark) &&$h->paired_ng_remark !== '-')
                                    <div class="small font-weight-bold" style="color: #9333ea;">
                                        <i class="fas fa-link mr-1"></i> Pair Defect: {{ $h->paired_ng_remark }}
                                    </div>
                                @endif

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
                            <td colspan="10" class="py-5 text-center text-muted font-italic">Belum ada riwayat produksi yang tercatat pada periode ini.</td>
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
                                <small class="stat-label">Primary Part <span id="det-filter-tag" style="display:none;color:#d97706;font-size:8px;border:1px solid #d97706;border-radius:4px;padding:0 3px;">FILTERED</span></small>
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
                                <small class="stat-label">Finish / Input Time</small>
                                <div class="font-weight-bold small text-muted" id="det-time">-</div>
                            </div>
                        </div>

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

                <div class="row mb-3">
                    <div class="col-3"><div class="kamar-ng text-center p-2"><small class="stat-label">Take Pcs</small><div class="h5 font-weight-bold m-0" id="det-take">0</div></div></div>
                    <div class="col-3"><div class="kamar-ng text-center p-2"><small class="stat-label text-success">OK Total</small><div class="h5 font-weight-bold text-success m-0" id="det-ok">0</div></div></div>
                    <div class="col-3"><div class="kamar-ng text-center p-2"><small class="stat-label text-danger">NG Total</small><div class="h5 font-weight-bold text-danger m-0" id="det-ng">0</div></div></div>
                    <div class="col-3"><div class="kamar-ng text-center p-2"><small class="stat-label text-info">Return</small><div class="h5 font-weight-bold text-info m-0" id="det-ret">0</div></div></div>
                </div>

                <div class="p-3 bg-light rounded border mb-3">
                    <small class="stat-label d-block mb-2"><i class="fas fa-boxes mr-1 text-primary"></i> Material Coil/Bundle Breakdown</small>
                    <div id="det-coil-list" class="d-flex flex-wrap gap-2"></div>
                </div>

                <div class="p-3 bg-light rounded border mb-3">
                    <small class="stat-label d-block mb-2 text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Specific NG (Reject Types)</small>
                    <div id="det-ng-list" class="row"></div>
                </div>

                <div class="p-3 bg-white rounded border">
                    <small class="stat-label d-block text-muted mb-1"><i class="far fa-sticky-note mr-1 text-warning"></i> Production Remarks & Notes</small>
                    <p class="mb-0 font-italic text-dark small" id="det-remark">-</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // ✨ JS SAKTI: CASCADING DROPDOWN FILTER CUSTOMER -> PART NO ✨
    $(document).ready(function() {
        const $custSelect = $('#filter_customer');
        const $partSelect = $('#filter_part');
        
        const allParts = $partSelect.find('option').clone();

        function updatePartDropdown() {
            const selectedCust = $custSelect.val().trim();
            const currentPart = $partSelect.val();
            
            $partSelect.empty();
            
            allParts.each(function() {
                const partCust = $(this).data('customer') ? $(this).data('customer').trim() : "";
                const isAllOption = $(this).val() === "";
                
                if (isAllOption || !selectedCust || partCust === selectedCust) {
                    $partSelect.append($(this).clone());
                }
            });
            
            if ($partSelect.find(`option[value="${currentPart}"]`).length > 0) {
                $partSelect.val(currentPart);
            } else {
                $partSelect.val("");
            }
        }

        $custSelect.on('change', updatePartDropdown);
        updatePartDropdown();
        
        let reqPart = "{{ request('part_no') }}";
        if (reqPart) {
            $partSelect.val(reqPart);
        }
    });

    // 📊 SCRIPT GRAFIK TREND PRODUKSI HARIAN
    const chartLabels = @json($chartLabels ?? []);
    const chartOk = @json($chartOk ?? []);
    const chartNg = @json($chartNg ?? []);
    const chartPerf = @json($chartPerf ?? []);

    if(chartLabels.length > 0) {
        new ApexCharts(document.querySelector("#trendChart"), {
            series: [
                { name: 'Performance % (Yield)', type: 'line', data: chartPerf },
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
                { title: { text: "Performance % (Yield)", style: { fontWeight: 800 } }, min: 0, max: 100, labels: { formatter: (val) => val.toFixed(1) + "%" } }, 
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

    function showDeepDive(h, trueOk, trueNg, yieldPercent, trueTakePcs, filteredLabel) {
        tempDonutData = { ok: trueOk, ng: trueNg };

        document.getElementById('det-batch').innerText = h.no_produksi;
        
        let partInfo = h.material_code + (h.part_name ? ' (' + h.part_name + ')' : '');
        const tagFilter = document.getElementById('det-filter-tag');
        
        // Membaca Kondisi Jika Filter Sedang Aktif
        if (filteredLabel !== "ALL") {
            tagFilter.style.display = 'inline-block';
            tagFilter.innerText = "FILTERED: " + filteredLabel;
            if (h.is_separating && !partInfo.includes(filteredLabel)) {
                partInfo = h.paired_part + (h.paired_part_name ? ' (' + h.paired_part_name + ')' : '');
            }
        } else {
            tagFilter.style.display = 'none';
        }
        
        document.getElementById('det-part').innerText = partInfo;
        
        document.getElementById('det-line').innerText = (h.kode_Line || 'LINE A') + ' - Shift ' + (h.shift || '1');
        document.getElementById('det-cavity').innerText = h.cavity || 1;
        document.getElementById('det-time').innerText = h.updated_at;
        
        // Data sudah murni berdasarkan filter Blade
        document.getElementById('det-take').innerText = Number(trueTakePcs).toLocaleString();
        document.getElementById('det-ok').innerText = Number(trueOk).toLocaleString();
        document.getElementById('det-ng').innerText = Number(trueNg).toLocaleString();
        document.getElementById('det-ret').innerText = Number(h.qty_return_warehouse || 0).toLocaleString();
        
        document.getElementById('det-yield-val').innerText = yieldPercent.toFixed(1) + "%";
        document.getElementById('det-yield-val').style.color = yieldPercent >= 90 ? '#10b981' : '#ef4444';
        
        document.getElementById('det-remark').innerText = h.keterangan ? h.keterangan : 'Tidak ada catatan remark khusus.';

        const pairBox = document.getElementById('det-separating-box');
        if (h.is_separating && h.paired_part && filteredLabel === "ALL") {
            pairBox.style.display = 'block';
            document.getElementById('det-pair-part').innerText = h.paired_part + (h.paired_part_name ? ' - ' + h.paired_part_name : '');
            document.getElementById('det-pair-ok').innerText = Number(h.paired_ok || 0).toLocaleString();
            document.getElementById('det-pair-ng').innerText = Number(h.paired_ng || 0).toLocaleString();
        } else {
            pairBox.style.display = 'none';
        }

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