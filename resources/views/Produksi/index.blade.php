@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --ios-bg: #f4f6fa;
        --ios-card: #ffffff;
        --ios-border: #e2e8f0;
        --ios-blue: #007aff;
        --ios-blue-soft: rgba(0, 122, 255, 0.08);
        --ios-green: #34c759;
        --ios-green-soft: rgba(52, 199, 89, 0.12);
        --ios-red: #ff3b30;
        --ios-red-soft: rgba(255, 59, 48, 0.12);
        --ios-amber: #f59e0b;
        --ios-amber-soft: rgba(245, 158, 11, 0.12);
        --ios-purple: #6366f1;
        --ios-purple-soft: rgba(99, 102, 241, 0.12);
        --ios-dark: #0f172a;
        --ios-muted: #64748b;
        --ios-spring: cubic-bezier(0.16, 1, 0.3, 1);
    }

    body {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        background-color: var(--ios-bg);
        color: #1e293b;
    }

    .main-terminal { min-height: 100vh; padding-bottom: 60px; }

    .btn-spring {
        border-radius: 9999px;
        font-weight: 700;
        font-size: 11.5px;
        letter-spacing: 0.3px;
        padding: 9px 20px;
        border: none;
        transition: transform 0.2s var(--ios-spring), box-shadow 0.2s ease, background-color 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .btn-spring:active { transform: scale(0.95) !important; }

    .command-header {
        background: #ffffff;
        padding: 22px 35px;
        border-bottom: 1px solid var(--ios-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
    }
    .hud-title {
        font-weight: 900;
        font-size: 1.45rem;
        letter-spacing: -0.5px;
        margin: 0;
        color: var(--ios-dark);
    }

    .nav-rail {
        padding: 12px 35px;
        display: flex;
        gap: 8px;
        overflow-x: auto;
        background: #ffffff;
        border-bottom: 1px solid var(--ios-border);
    }
    .nav-rail::-webkit-scrollbar { display: none; }
    .tab-btn {
        background: var(--ios-bg);
        border: 1px solid transparent;
        padding: 8px 18px;
        border-radius: 9999px;
        color: var(--ios-muted);
        font-weight: 700;
        font-size: 11px;
        transition: all 0.2s var(--ios-spring);
        text-decoration: none !important;
        white-space: nowrap;
    }
    .tab-btn:hover { color: var(--ios-dark); background: #e5e9f0; }
    .tab-btn.active {
        background: var(--ios-blue);
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(0, 122, 255, 0.28);
    }

    .table-container {
        background: #ffffff;
        margin: 0 35px;
        border-radius: 22px;
        border: 1px solid var(--ios-border);
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.03);
    }
    .table-industrial {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }
    .table-industrial thead th {
        background: #f8fafc;
        color: #64748b;
        padding: 14px 22px;
        font-size: 10px;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.8px;
        border-top: none;
        border-bottom: 1px solid var(--ios-border);
    }
    .table-industrial tbody td {
        padding: 16px 22px;
        border-top: 1px solid #f1f5f9;
        font-size: 12.5px;
        vertical-align: middle;
    }
    .table-industrial tbody tr:hover { background-color: #fbfcfe; }

    .id-tag {
        font-family: 'JetBrains Mono', monospace;
        color: var(--ios-blue);
        font-size: 12px;
        font-weight: 800;
        background: var(--ios-blue-soft);
        padding: 4px 10px;
        border-radius: 8px;
        display: inline-block;
    }
    .badge-pill-ios {
        font-family: 'JetBrains Mono', monospace;
        font-size: 9.5px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .badge-area-big { background: var(--ios-blue-soft); color: var(--ios-blue); border: 1px solid rgba(0, 122, 255, 0.2); }
    .badge-area-small { background: var(--ios-amber-soft); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.25); }
    .badge-shift-s1 { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
    .badge-shift-s2 { background: var(--ios-purple-soft); color: #4338ca; border: 1px solid #c7d2fe; }

    .badge-fg { background: var(--ios-green-soft); color: #059669; }
    .badge-welding { background: #fef3c7; color: #b45309; }
    .badge-proses { background: #e0f2fe; color: #0369a1; }
    .badge-problem { background: var(--ios-red-soft); color: var(--ios-red); }

    .modal-ios .modal-content {
        border-radius: 24px;
        border: none;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.2);
    }
    .modal-ios .modal-header {
        padding: 20px 28px;
        border-bottom: 1px solid var(--ios-border);
        background: #ffffff;
    }
    .modal-ios label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--ios-muted);
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .input-tactical {
        background: #f8fafc;
        border: 1.5px solid var(--ios-border);
        border-radius: 12px;
        padding: 10px 14px;
        font-weight: 700;
        font-size: 12.5px;
        width: 100%;
        color: #0f172a;
        transition: all 0.2s ease;
    }
    .input-tactical:focus {
        background: #ffffff;
        border-color: var(--ios-blue);
        box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.15);
        outline: none;
    }

    .progress-lite {
        height: 10px;
        border-radius: 9999px;
        background: #e2e8f0;
        overflow: hidden;
        margin: 12px 0;
    }
    .progress-bar-fill {
        height: 100%;
        background: var(--ios-green);
        transition: width 0.4s var(--ios-spring);
    }
    
    /* MODAL HEADER KHUSUS ID WOS */
    .wos-giant-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 25px 20px;
        text-align: center;
        border-bottom: 1px solid var(--ios-border);
    }
    .wos-giant-badge {
        font-family: 'JetBrains Mono', monospace;
        background: #0f172a;
        color: #fff;
        padding: 6px 16px;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 800;
        letter-spacing: 1px;
        display: inline-block;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
</style>

<div class="container-fluid p-0 main-terminal animate__animated animate__fadeIn">

    <div class="px-5 pt-3">
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" style="border-radius: 14px; background: #e8f9ee; color: #166534;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle mr-3 fa-lg" style="color: var(--ios-green);"></i>
                    <div>
                        <strong class="text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Success</strong><br>
                        <span style="font-size: 12px;">{{ session('success') }}</span>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show" style="border-radius: 14px; background: #fef2f2; color: #991b1b;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle mr-3 fa-lg" style="color: var(--ios-red);"></i>
                    <div>
                        <strong class="text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Alert</strong><br>
                        <span style="font-size: 12px;">{{ session('error') }}</span>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
    </div>

    <div class="command-header">
        <div>
            <h1 class="hud-title">Terminal <span style="color: var(--ios-blue);">Line Control</span></h1>
            <small class="text-muted font-weight-bold">Monitoring Terpadu Big Press & Small Press // Shift 1 & Shift 2</small>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ Route::has('produksi.history') ? route('produksi.history') : url('/produksi/history') }}" class="btn btn-light btn-spring border mr-2 shadow-sm">
                <i class="fas fa-archive text-muted"></i> ARCHIVE
            </a>
            <button class="btn btn-primary btn-spring shadow-sm" style="background: var(--ios-blue);" data-toggle="modal" data-target="#modalAmbilMaterial">
                <i class="fas fa-bolt"></i> START NEW BATCH
            </button>
        </div>
    </div>

    <div class="nav-rail d-flex align-items-center justify-content-between">
        <div class="d-flex gap-2">
            <a href="{{ route('produksi.index', ['area' => 'all', 'shift' => $selectedShift, 'customer' => $customerFilter, 'date' => $date]) }}" class="tab-btn {{ $area === 'all' ? 'active' : '' }}">
                🌐 SEMUA LINE
            </a>
            <a href="{{ route('produksi.index', ['area' => 'big', 'shift' => $selectedShift, 'customer' => $customerFilter, 'date' => $date]) }}" class="tab-btn {{ $area === 'big' ? 'active' : '' }}">
                🏭 BIG PRESS
            </a>
            <a href="{{ route('produksi.index', ['area' => 'small', 'shift' => $selectedShift, 'customer' => $customerFilter, 'date' => $date]) }}" class="tab-btn {{ $area === 'small' ? 'active' : '' }}">
                ⚙️ SMALL PRESS
            </a>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <div class="btn-group bg-white border p-1" style="border-radius: 9999px;">
                <a href="{{ route('produksi.index', ['area' => $area, 'shift' => 'ALL', 'customer' => $customerFilter, 'date' => $date]) }}" class="btn btn-sm btn-spring py-1 px-3 {{ $selectedShift === 'ALL' ? 'btn-dark' : 'text-muted' }}">ALL SHIFT</a>
                <a href="{{ route('produksi.index', ['area' => $area, 'shift' => 'S1', 'customer' => $customerFilter, 'date' => $date]) }}" class="btn btn-sm btn-spring py-1 px-3 {{ $selectedShift === 'S1' ? 'btn-warning text-dark' : 'text-muted' }}">SHIFT 1</a>
                <a href="{{ route('produksi.index', ['area' => $area, 'shift' => 'S2', 'customer' => $customerFilter, 'date' => $date]) }}" class="btn btn-sm btn-spring py-1 px-3 {{ $selectedShift === 'S2' ? 'btn-primary' : 'text-muted' }}">SHIFT 2</a>
            </div>
            <form action="{{ route('produksi.index') }}" method="GET" class="m-0">
                <input type="hidden" name="area" value="{{ $area }}">
                <input type="hidden" name="shift" value="{{ $selectedShift }}">
                <input type="hidden" name="customer" value="{{ $customerFilter }}">
                <input type="date" name="date" value="{{ $date ?? date('Y-m-d') }}" class="form-control font-weight-bold border" onchange="this.form.submit()" style="border-radius: 9999px; font-size: 12px; padding: 6px 14px; height: auto;">
            </form>
        </div>
    </div>

    <div class="nav-rail mt-2 border-top-0">
        <a href="{{ route('produksi.index', ['area' => $area, 'shift' => $selectedShift, 'customer' => 'ALL', 'date' => $date]) }}" 
           class="tab-btn {{ (!$customerFilter || $customerFilter === 'ALL') ? 'active' : '' }}" style="{{ (!$customerFilter || $customerFilter === 'ALL') ? 'background:#10b981;box-shadow: 0 4px 12px rgba(16, 185, 129, 0.28);' : '' }}">
            ALL CUSTOMER
        </a>
        @foreach($customers as $c)
            <a href="{{ route('produksi.index', ['area' => $area, 'shift' => $selectedShift, 'customer' => trim($c->code), 'date' => $date]) }}" 
                class="tab-btn {{ request('customer') == trim($c->code) ? 'active' : '' }}" style="{{ request('customer') == trim($c->code) ? 'background:#10b981;box-shadow: 0 4px 12px rgba(16, 185, 129, 0.28);' : '' }}">
                {{ strtoupper($c->code) }}
            </a>
        @endforeach
    </div>

    {{-- ✨ LOGIKA GROUPING BERDASARKAN TANGGAL & MESIN ✨ --}}
    @php
        $groupedByDate = $activeProductions->groupBy('plan_date');
    @endphp

    @forelse($groupedByDate as $tanggal => $dailyBatches)
        <div class="px-5 mt-4 mb-3">
            <h4 class="font-weight-bold text-dark m-0" style="font-size: 16px;">
                <i class="far fa-calendar-alt text-primary mr-2"></i> JADWAL TANGGAL: <span class="text-primary">{{ date('d F Y', strtotime($tanggal)) }}</span>
            </h4>
        </div>

        @php
            $bigPressBatches = $dailyBatches->filter(function($b) {
                return $b->area_type === 'BIG';
            });
            $smallPressBatches = $dailyBatches->filter(function($b) {
                return $b->area_type === 'SMALL';
            });
        @endphp

        <!-- 🏭 TABEL BIG PRESS -->
        @if($bigPressBatches->count() > 0)
        <div class="table-container mb-4 mt-0">
            <div class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                <h6 class="font-weight-bold m-0" style="color: #0f172a;"><i class="fas fa-industry text-primary mr-2"></i> BIG PRESS AREA</h6>
            </div>
            <table class="table-industrial">
                <thead>
                    <tr>
                        <th width="4%" class="text-center">No.</th>
                        <th width="14%">ID WOS</th>
                        <th width="24%">Part Identification</th>
                        <th width="15%">Workstation & Shift</th>
                        <th width="12%">Destination</th>
                        <th width="11%" class="text-right">Total Jatah</th>
                        <th width="9%" class="text-center">Status</th>
                        <th width="11%" class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bigPressBatches as $idx => $p)
                        <!-- ✨ BARIS ASLI TIDAK ADA YANG DIUBAH ✨ -->
                        <tr>
                            <td class="text-center font-weight-bold text-muted">{{ $loop->iteration }}</td>
                            <td>
                                @if($p->qty_return > 0)
                                    <span class="badge mb-1 animate__animated animate__pulse animate__infinite" style="background: #6366f1; color: #fff; font-size: 8px; padding: 3px 6px; border-radius: 4px;">
                                        <i class="fas fa-redo-alt mr-1"></i> REWORK QC
                                    </span><br>
                                @endif
                                <span class="id-tag">{{ $p->no_produksi }}</span>
                            </td>
                            <td>
                                @php 
                                    $isMainPair = isset($p->is_separating) && $p->is_separating;
                                    $pairedPartCode = $isMainPair ? $p->paired_part : null;
                                    $childParts = DB::table('produksi_batch_parts')->where('batch_id', $p->batch_id)->pluck('part_no')->toArray();
                                    
                                    if ($isMainPair) { $displayParts = $p->material_code . ' & ' . $pairedPartCode; } 
                                    elseif (count($childParts) > 0) { $displayParts = implode(' & ', $childParts); } 
                                    else { $displayParts = $p->material_code; }
                                @endphp
                                <div class="font-weight-bold text-dark font-family-jetbrains" style="font-size: 13px;">{{ $displayParts }}</div>
                                <small class="text-muted font-weight-bold">
                                    {{ $p->coil_id ?? 'COIL' }} 
                                    <span class="badge badge-light border ml-1 px-2 py-0" style="border-radius: 4px;">Cav: {{ $p->cavity ?? 1 }}</span>
                                    <span class="text-muted ml-1">{{ $p->customer ?? 'CLIENT' }}</span>
                                </small>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div><span class="badge-pill-ios badge-area-big">🏭 {{ $p->area_label }} ({{ $p->line_names ?? $p->kode_Line }})</span></div>
                                    <div><span class="badge-pill-ios {{ $p->shift_code === 'S1' ? 'badge-shift-s1' : 'badge-shift-s2' }}">{{ $p->shift_code === 'S1' ? '☀️' : '🌙' }} {{ $p->shift_label }}</span></div>
                                </div>
                            </td>
                            <td>
                                @php $route = DB::table('parts')->where('part_no', $p->material_code)->value('next_process'); @endphp
                                @if(strtoupper($route) == 'WELDING') 
                                    <span class="badge-pill-ios badge-welding">WELDING</span> 
                                @else 
                                    <span class="badge-pill-ios badge-fg">FINISHED GOOD</span> 
                                @endif
                            </td>
                            <td class="text-right font-family-jetbrains font-weight-bold">
                                @if($p->qty_return > 0)
                                    <span class="text-primary" style="font-size: 14px;">{{ number_format($p->qty_return) }}</span>
                                    <div style="font-size: 8px; color: var(--ios-muted);">PCS REWORK</div>
                                @else
                                    <span class="text-dark" style="font-size: 13px;">{{ number_format($p->total_qty_batch) }} <small class="text-muted">pcs</small></span><br>
                                    <small class="text-muted">({{ number_format($p->qty_ambil_pcs) }} lbr)</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($p->status == 'PROBLEM')
                                    <span class="badge-pill-ios badge-problem animate__animated animate__flash animate__infinite"><i class="fas fa-exclamation-triangle"></i> PROBLEM</span>
                                @else
                                    <span class="badge-pill-ios badge-proses">{{ $p->status }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="d-flex justify-content-end align-items-center">
                                    <button class="btn btn-outline-danger btn-sm p-0 mr-2 shadow-sm rounded-circle" style="width: 32px; height: 32px;" data-toggle="modal" data-target="#modalProblem{{ $p->batch_id }}" title="REPORT PROBLEM">
                                        <i class="fas fa-radiation-alt" style="font-size: 11px;"></i>
                                    </button>
                                    <button class="btn btn-success btn-spring px-3 font-weight-bold shadow-sm" style="background: var(--ios-green);" data-toggle="modal" data-target="#modalInputHasil{{ $p->batch_id }}">
                                        INPUT RESULT
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- ⚙️ TABEL SMALL PRESS -->
        @if($smallPressBatches->count() > 0)
        <div class="table-container mb-4 mt-0">
            <div class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                <h6 class="font-weight-bold m-0" style="color: #b45309;"><i class="fas fa-cogs text-warning mr-2"></i> SMALL PRESS AREA</h6>
            </div>
            <table class="table-industrial">
                <thead style="background: #fffbeb;">
                    <tr>
                        <th width="4%" class="text-center">No.</th>
                        <th width="14%">ID WOS</th>
                        <th width="24%">Part Identification</th>
                        <th width="15%">Workstation & Shift</th>
                        <th width="12%">Destination</th>
                        <th width="11%" class="text-right">Total Jatah</th>
                        <th width="9%" class="text-center">Status</th>
                        <th width="11%" class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($smallPressBatches as $idx => $p)
                        <!-- ✨ BARIS ASLI TIDAK ADA YANG DIUBAH ✨ -->
                        <tr>
                            <td class="text-center font-weight-bold text-muted">{{ $loop->iteration }}</td>
                            <td>
                                @if($p->qty_return > 0)
                                    <span class="badge mb-1 animate__animated animate__pulse animate__infinite" style="background: #6366f1; color: #fff; font-size: 8px; padding: 3px 6px; border-radius: 4px;">
                                        <i class="fas fa-redo-alt mr-1"></i> REWORK QC
                                    </span><br>
                                @endif
                                <span class="id-tag">{{ $p->no_produksi }}</span>
                            </td>
                            <td>
                                @php 
                                    $isMainPair = isset($p->is_separating) && $p->is_separating;
                                    $pairedPartCode = $isMainPair ? $p->paired_part : null;
                                    $childParts = DB::table('produksi_batch_parts')->where('batch_id', $p->batch_id)->pluck('part_no')->toArray();
                                    
                                    if ($isMainPair) { $displayParts = $p->material_code . ' & ' . $pairedPartCode; } 
                                    elseif (count($childParts) > 0) { $displayParts = implode(' & ', $childParts); } 
                                    else { $displayParts = $p->material_code; }
                                @endphp
                                <div class="font-weight-bold text-dark font-family-jetbrains" style="font-size: 13px;">{{ $displayParts }}</div>
                                <small class="text-muted font-weight-bold">
                                    {{ $p->coil_id ?? 'COIL' }} 
                                    <span class="badge badge-light border ml-1 px-2 py-0" style="border-radius: 4px;">Cav: {{ $p->cavity ?? 1 }}</span>
                                    <span class="text-muted ml-1">{{ $p->customer ?? 'CLIENT' }}</span>
                                </small>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div><span class="badge-pill-ios badge-area-small">⚙️ {{ $p->area_label }} ({{ $p->line_names ?? $p->kode_Line }})</span></div>
                                    <div><span class="badge-pill-ios {{ $p->shift_code === 'S1' ? 'badge-shift-s1' : 'badge-shift-s2' }}">{{ $p->shift_code === 'S1' ? '☀️' : '🌙' }} {{ $p->shift_label }}</span></div>
                                </div>
                            </td>
                            <td>
                                @php $route = DB::table('parts')->where('part_no', $p->material_code)->value('next_process'); @endphp
                                @if(strtoupper($route) == 'WELDING') 
                                    <span class="badge-pill-ios badge-welding">WELDING</span> 
                                @else 
                                    <span class="badge-pill-ios badge-fg">FINISHED GOOD</span> 
                                @endif
                            </td>
                            <td class="text-right font-family-jetbrains font-weight-bold">
                                @if($p->qty_return > 0)
                                    <span class="text-primary" style="font-size: 14px;">{{ number_format($p->qty_return) }}</span>
                                    <div style="font-size: 8px; color: var(--ios-muted);">PCS REWORK</div>
                                @else
                                    <span class="text-dark" style="font-size: 13px;">{{ number_format($p->total_qty_batch) }} <small class="text-muted">pcs</small></span><br>
                                    <small class="text-muted">({{ number_format($p->qty_ambil_pcs) }} lbr)</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($p->status == 'PROBLEM')
                                    <span class="badge-pill-ios badge-problem animate__animated animate__flash animate__infinite"><i class="fas fa-exclamation-triangle"></i> PROBLEM</span>
                                @else
                                    <span class="badge-pill-ios badge-proses">{{ $p->status }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="d-flex justify-content-end align-items-center">
                                    <button class="btn btn-outline-danger btn-sm p-0 mr-2 shadow-sm rounded-circle" style="width: 32px; height: 32px;" data-toggle="modal" data-target="#modalProblem{{ $p->batch_id }}" title="REPORT PROBLEM">
                                        <i class="fas fa-radiation-alt" style="font-size: 11px;"></i>
                                    </button>
                                    <button class="btn btn-success btn-spring px-3 font-weight-bold shadow-sm" style="background: var(--ios-green);" data-toggle="modal" data-target="#modalInputHasil{{ $p->batch_id }}">
                                        INPUT RESULT
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    @empty
        <div class="table-container">
            <div class="py-5 text-center text-muted">
                <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>
                <span class="font-weight-bold">-- SISTEM IDLE: TIDAK ADA BATCH AKTIF BERJALAN --</span>
            </div>
        </div>
    @endforelse

</div>

{{-- MODAL & SCRIPT ASLI TIDAK ADA YANG DIHAPUS --}}
@foreach($activeProductions as $p)
@php 
    $currentTarget = ($p->qty_return > 0) ? ($p->qty_return * ($p->cavity ?? 1)) : $p->total_qty_batch; 
    $isMainPair = isset($p->is_separating) && $p->is_separating;
    $pairedPartCode = $isMainPair ? $p->paired_part : null;
    $actionUrl = Route::has('produksi.update_result') ? route('produksi.update_result', $p->batch_id) : (Route::has('produksi.updateResult') ? route('produksi.updateResult', $p->batch_id) : url('/produksi/update-result/' . $p->batch_id));
    $problemUrl = Route::has('produksi.report_problem') ? route('produksi.report_problem', $p->batch_id) : (Route::has('produksi.reportProblem') ? route('produksi.reportProblem', $p->batch_id) : url('/produksi/report-problem/' . $p->batch_id));
@endphp

{{-- 1. MODAL INPUT RESULT --}}
<div class="modal fade modal-ios" id="modalInputHasil{{ $p->batch_id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="wos-giant-header">
                <div class="text-muted font-weight-bold small text-uppercase mb-1">INPUT HASIL PRODUKSI UNTUK:</div>
                <div class="wos-giant-badge mb-2"><i class="fas fa-qrcode mr-2"></i> {{ $p->no_produksi }}</div>
                <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ $p->display_part }} • {{ $p->area_label }} ({{ $p->shift_code }})</div>
            </div>
            
            <form action="{{ $actionUrl }}" method="POST">
                @csrf 
                @method('PUT')
                <input type="hidden" name="status" value="COMPLETED">
                <input type="hidden" id="cavity_{{ $p->batch_id }}" value="{{ $p->cavity ?? 1 }}">
                
                <div class="modal-body p-4 bg-white">
                    <div id="police_msg_{{ $p->batch_id }}" class="alert alert-warning border-0 font-weight-bold text-center py-2 mb-4" style="border-radius: 12px; font-size: 12px;">
                        👮 STATUS: STANDBY FOR SYNC...
                    </div>
                    
                    {{-- DUAL INPUT (SEPARATING PART) --}}
                    @if($isMainPair)
                        <div class="row mb-3">
                            <div class="col-md-6 border-right">
                                <label class="font-weight-bold text-dark d-block border-bottom pb-2 mb-3">
                                    <span class="badge badge-primary mr-1" style="border-radius:4px;">UTAMA</span> {{ $p->material_code }}
                                </label>
                                <div class="form-group">
                                    <label class="text-success font-weight-bold">Hasil OK (Pcs)</label>
                                    <input type="number" name="qty_hasil_ok" id="ok_main_{{ $p->batch_id }}" data-id="{{ $p->batch_id }}" class="input-tactical border-success calc-input-dual" placeholder="0" required min="0">
                                </div>
                                <div class="form-group">
                                    <label class="text-danger font-weight-bold">Reject NG (Pcs)</label>
                                    <input type="number" name="qty_hasil_ng" id="ng_main_{{ $p->batch_id }}" data-id="{{ $p->batch_id }}" class="input-tactical border-danger calc-input-dual" placeholder="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="font-weight-bold text-dark d-block border-bottom pb-2 mb-3">
                                    <span class="badge badge-info mr-1" style="border-radius:4px;">PASANGAN</span> {{ $pairedPartCode }}
                                </label>
                                <input type="hidden" name="paired_batch_id" value="{{ $p->paired_batch_id }}">
                                <div class="form-group">
                                    <label class="text-success font-weight-bold">Hasil OK (Pcs)</label>
                                    <input type="number" name="paired_qty_ok" id="ok_sub_{{ $p->batch_id }}" data-id="{{ $p->batch_id }}" class="input-tactical border-success calc-input-dual" placeholder="0" required min="0">
                                </div>
                                <div class="form-group">
                                    <label class="text-danger font-weight-bold">Reject NG (Pcs)</label>
                                    <input type="number" name="paired_qty_ng" id="ng_sub_{{ $p->batch_id }}" data-id="{{ $p->batch_id }}" class="input-tactical border-danger calc-input-dual" placeholder="0" min="0">
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- LOGIKA BIASA / MULTI-PART --}}
                        <div class="row">
                            <div class="col-md-8">
                                <label class="font-weight-bold text-dark mb-2">RINCIAN HASIL PER PART</label>
                                @php $childs = DB::table('produksi_batch_parts')->where('batch_id',$p->batch_id)->get(); @endphp
                                
                                @if($childs->count() > 0)
                                    @foreach($childs as $idx =>$cp)
                                        <div class="row mb-3 p-3 bg-light border mx-0" style="border-radius: 14px;">
                                            <div class="col-12 mb-2"><strong class="text-primary font-weight-bold font-family-jetbrains"><i class="fas fa-cog mr-1"></i> {{ $cp->part_no }}</strong></div>
                                            <div class="col-6">
                                                <label class="small text-success font-weight-bold">QTY OK</label>
                                                <input type="number" name="qty_hasil_ok_parts[{{ $cp->id }}]" data-id="{{ $p->batch_id }}" class="input-tactical calc-input" required value="0">
                                            </div>
                                            <div class="col-6">
                                                <label class="small text-danger font-weight-bold">QTY NG</label>
                                                <input type="number" name="qty_hasil_ng_parts[{{ $cp->id }}]" data-id="{{ $p->batch_id }}" class="input-tactical calc-input" required value="0">
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="form-group">
                                        <label class="text-success font-weight-bold">QTY HASIL OK (PCS)</label>
                                        <input type="number" name="qty_hasil_ok" id="ok_{{ $p->batch_id }}" data-id="{{ $p->batch_id }}" class="input-tactical calc-input mb-3" required value="0">
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label class="font-weight-bold text-danger">Return Sheet (Lbr)</label>
                                <input type="number" name="qty_return_warehouse" id="return_{{ $p->batch_id }}" data-id="{{ $p->batch_id }}" class="input-tactical calc-input mb-2" value="0">
                                
                                <label class="font-weight-bold text-danger mt-3">NG Biasa (Pcs)</label>
                                <input type="number" name="qty_hasil_ng" id="ng_{{ $p->batch_id }}" data-id="{{ $p->batch_id }}" class="input-tactical calc-input mb-2" value="0">
                                <small class="text-muted d-block mt-2" style="font-size:10px; line-height: 1.3;">Return dikalikan {{ $p->cavity ?? 1 }} Pcs saat sinkronisasi Gap</small>
                            </div>
                        </div>
                    @endif

                    @if($isMainPair)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="font-weight-bold text-danger">Return Sheet Utama ke Gudang (Lbr)</label>
                            <input type="number" name="qty_return_warehouse" id="return_dual_{{ $p->batch_id }}" data-id="{{ $p->batch_id }}" class="input-tactical calc-input-dual mb-1" value="0">
                            <small class="text-muted d-block" style="font-size:10px;">Return dihitung dari Plat Utama (Dikalikan Cavity: {{ $p->cavity ?? 1 }})</small>
                        </div>
                    </div>
                    @endif

                    <div class="mt-2 border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="font-weight-bold text-danger m-0"><i class="fas fa-exclamation-triangle mr-1"></i> Rincian Reject (NG Spesifik)</label>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill font-weight-bold px-3 py-1" onclick="addNgRow({{ $p->batch_id }})">
                                <i class="fas fa-plus mr-1"></i> TAMBAH NG
                            </button>
                        </div>
                        <div id="ng_container_{{ $p->batch_id }}"></div>
                    </div>

                    <div class="form-group mt-3 mb-3">
                        <label>Keterangan / Remark Tambahan</label>
                        <textarea name="keterangan" class="input-tactical" rows="2" placeholder="Catatan tambahan operator..."></textarea>
                    </div>

                    <div class="p-3 bg-light rounded-xl border text-center" style="border-radius: 16px;">
                        <small class="text-muted font-weight-bold uppercase" style="font-size: 10px;">Gap Status (Target Material x Cavity):</small>
                        <h4 class="mb-0 font-weight-bold text-danger font-family-jetbrains" id="gap_{{ $p->batch_id }}">{{ number_format($currentTarget) }}</h4>
                        <div class="progress-lite"><div class="progress-bar-fill" id="bar_{{ $p->batch_id }}" style="width: 0%"></div></div>
                        <input type="hidden" class="target-val" data-id="{{ $p->batch_id }}" value="{{ $currentTarget }}">
                    </div>
                </div>
                
                <div class="modal-footer border-0 px-4 py-3 bg-light">
                    <button type="button" class="btn btn-secondary btn-spring px-4" data-dismiss="modal">BATAL</button>
                    <button type="submit" id="btn_{{ $p->batch_id }}" class="btn btn-primary btn-spring px-5" style="background: var(--ios-green);" disabled>COMMIT & TRANSMIT DIRECT TO FG</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. MODAL REPORT PROBLEM --}}
<div class="modal fade modal-ios" id="modalProblem{{ $p->batch_id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h6 class="modal-title font-weight-bold text-danger m-0"><i class="fas fa-radiation-alt mr-2"></i> EMERGENCY REPORT</h6>
                    <span class="small font-weight-bold text-muted font-family-jetbrains">Batch: {{ $p->no_produksi }}</span>
                </div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ $problemUrl }}" method="POST">
                @csrf 
                @method('PUT')
                <div class="modal-body p-4 bg-white">
                    <label>Detail Kendala (Dies Rusak, Pin Patah, dll)</label>
                    <textarea name="problem_note" class="input-tactical" rows="4" required placeholder="Jelaskan kondisi dies/kendala saat ini secara detail..."></textarea>
                </div>
                <div class="modal-footer border-0 px-4 py-3 bg-light">
                    <button type="button" class="btn btn-secondary btn-spring px-4" data-dismiss="modal">BATAL</button>
                    <button type="submit" class="btn btn-danger btn-spring px-4" style="background: var(--ios-red);">KIRIM LAPORAN</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- 3. MODAL START NEW BATCH --}}
<div class="modal fade modal-ios" id="modalAmbilMaterial" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h6 class="modal-title font-weight-bold text-dark m-0"><i class="fas fa-bolt text-primary mr-2"></i> Initialize New Batch</h6>
                    <span class="small font-weight-bold text-muted">Ambil Material & Mulai Antrean Produksi Stamping</span>
                </div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ Route::has('produksi.store') ? route('produksi.store') : url('/produksi/store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-white">
                    <div class="form-group">
                        <label>BATCH ID</label>
                        <input type="text" name="no_produksi" class="input-tactical font-family-jetbrains font-weight-bold text-primary" value="PROD-{{ date('Ymd-His') }}" readonly>
                    </div>

                    <div class="row">
                        <div class="col-6 form-group">
                            <label>02. SHIFT</label>
                            <select name="shift" class="input-tactical font-weight-bold" required>
                                <option value="" disabled selected>-- SELECT --</option>
                                <option value="Pagi">PAGI (S1)</option>
                                <option value="Malam">MALAM (S2)</option>
                            </select>
                        </div>
                        <div class="col-6 form-group">
                            <label>03. LINE MESIN</label>
                            <select name="mesin_id" class="input-tactical font-weight-bold" required>
                                <option value="" disabled selected>-- SELECT --</option>
                                @foreach($lines as $l) 
                                    <option value="{{ $l->id }}">{{ $l->kode_Line }} - {{$l->nama_Line }}</option> 
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>04. CUSTOMER ENTITY</label>
                        <select id="sel_customer" class="input-tactical font-weight-bold" required>
                            <option value="" disabled selected>-- SELECT CUSTOMER --</option>
                            @foreach($customers as $c) 
                                <option value="{{ trim($c->code) }}">{{ strtoupper($c->name) }} ({{ strtoupper($c->code) }})</option> 
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 form-group">
                            <label>05. MATERIAL SPEC</label>
                            <select id="sel_spec" class="input-tactical font-weight-bold" disabled required></select>
                        </div>
                        <div class="col-6 form-group">
                            <label>06. PART NUMBER <small class="text-primary">(Tahan CTRL untuk pilih >1)</small></label>
                            <select id="sel_part" name="part_no[]" class="input-tactical font-family-jetbrains mb-1" multiple="multiple" style="min-height: 80px;" disabled required></select>
                            
                            {{-- Input Manual Part --}}
                            <div class="input-group mt-1">
                                <input type="text" id="manual_part_input" class="form-control form-control-sm font-weight-bold" placeholder="Ketik LH & Tambah.." style="border-radius: 8px 0 0 8px; border: 1.5px solid var(--ios-border);">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-sm btn-success font-weight-bold" id="btn_add_manual_part" style="border-radius: 0 8px 8px 0; background: var(--ios-green); border: none;">+ TAMBAH</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>07. PHYSICAL COIL / BUNDLE</label>
                        <select id="sel_bandel" name="rm_stock_id" class="input-tactical font-weight-bold" disabled required></select>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 form-group">
                            <label>08. QTY DIAMBIL (SHEET)</label>
                            <input type="number" id="qty_ambil_pcs" name="qty_ambil_pcs" class="input-tactical text-center font-family-jetbrains font-weight-bold" required placeholder="0" min="1">
                        </div>
                        <div class="col-6 form-group">
                            <label>09. CAVITY</label>
                            <input type="number" id="cavity_input" name="cavity" class="input-tactical text-center font-family-jetbrains font-weight-bold" style="background:#e9ecef;" readonly value="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 py-3 bg-light">
                    <button type="button" class="btn btn-secondary btn-spring px-4" data-dismiss="modal">BATAL</button>
                    <button type="submit" id="btn_submit_ambil" class="btn btn-primary btn-spring px-5" style="background: var(--ios-blue);" disabled>DEPLOY BATCH</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const listPenyakit = ["Burry", "Dented", "Deform", "Oil Maru", "Spring Back", "Nobi", "Crack", "Scratch", "Pull Up", "Pull Down", "NG Thickness", "Wrinkle", "Missing Hole"];

    function addNgRow(batchId) {
        const id = Date.now();
        let options = listPenyakit.map(p => `<option value="${p}">${p}</option>`).join('');
        const html = `
            <div class="row no-gutters mb-2 animate__animated animate__fadeInDown" id="row-${id}">
                <div class="col-7 pr-1">
                    <select name="ng_detail_type[]" class="form-control form-control-sm font-weight-bold" style="border-radius:8px;">${options}</select>
                </div>
                <div class="col-3 pr-1">
                    <input type="number" name="ng_detail_qty[]" class="form-control form-control-sm ng-qty-input text-center font-weight-bold" 
                    placeholder="Qty" min="1" required data-id="${batchId}" style="border-radius:8px;">
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-danger btn-sm btn-block rounded" onclick="removeNgRow(${id}, ${batchId})"><i class="fas fa-times"></i></button>
                </div>
            </div>`;
        $(`#ng_container_${batchId}`).append(html);
        triggerCalc(batchId);
    }

    function removeNgRow(id, batchId) {
        $(`#row-${id}`).remove();
        triggerCalc(batchId);
    }

    function triggerCalc(batchId) {
        $(`input[data-id="${batchId}"].calc-input, input[data-id="${batchId}"].calc-input-dual`).first().trigger('input');
    }

    $(document).ready(function() {
        // ✨ PERHITUNGAN GAP & SINKRONISASI HASIL BERSIH ✨
        $(document).on('input', '.calc-input, .calc-input-dual, .ng-qty-input', function() {
            let id = $(this).data('id');
            let target = parseInt($(`.target-val[data-id="${id}"]`).val()) || 0;
            let cavity = parseInt($(`#cavity_${id}`).val()) || 1;
            
            let isDual = $(`#ok_main_${id}`).length > 0;
            
            let retVal = 0;
            let maxPartUsage = 0;
            
            if (isDual) {
                // Perhitungan form Kanan-Kiri (Separating Part)
                retVal = parseInt($(`#return_dual_${id}`).val()) || 0;
                let okMain = parseInt($(`#ok_main_${id}`).val()) || 0;
                let ngMain = parseInt($(`#ng_main_${id}`).val()) || 0;
                let okSub = parseInt($(`#ok_sub_${id}`).val()) || 0;
                let ngSub = parseInt($(`#ng_sub_${id}`).val()) || 0;
                
                let totalMain = okMain + ngMain;
                let totalSub = okSub + ngSub;
                
                maxPartUsage = Math.max(totalMain, totalSub);
            } else {
                // Perhitungan form Biasa
                retVal = parseInt($(`#return_${id}`).val()) || 0;
                
                let hasParts = false;
                $(`input[name^="qty_hasil_ok_parts"][data-id="${id}"]`).each(function() {
                    hasParts = true;
                    let currentOk = parseInt($(this).val()) || 0;
                    let partIdMatch = $(this).attr('name').match(/\[(\d+)\]/);
                    if(partIdMatch) {
                        let partId = partIdMatch[1];
                        let currentNg = parseInt($(`input[name="qty_hasil_ng_parts[${partId}]"]`).val()) || 0;
                        let currentTotal = currentOk + currentNg;
                        if (currentTotal > maxPartUsage) {
                            maxPartUsage = currentTotal;
                        }
                    }
                });

                if (!hasParts) {
                     let oldOk = parseInt($(`#ok_${id}`).val()) || 0;
                     let oldNg = parseInt($(`#ng_${id}`).val()) || 0;
                     maxPartUsage = oldOk + oldNg;
                }
            }

            // Return lembar dikalikan cavity menjadi pcs
            let retValInPcs = retVal * cavity; 
            
            let dynamicNgSum = 0;
            $(`#ng_container_${id} .ng-qty-input`).each(function() {
                dynamicNgSum += parseInt($(this).val()) || 0;
            });

            let accounted = maxPartUsage + retValInPcs + dynamicNgSum;
            let gap = target - accounted;
            
            $(`#gap_${id}`).text(gap.toLocaleString());
            let progress = target > 0 ? (accounted / target) * 100 : 0;
            $(`#bar_${id}`).css('width', Math.min(progress, 100) + '%');
            
            let btn = $(`#btn_${id}`), msg = $(`#police_msg_${id}`);
            if (gap === 0) { 
                msg.removeClass('alert-warning alert-danger').addClass('alert-success').html('👮 DATA SYNC! Ready to commit.'); 
                btn.prop('disabled', false); 
            } else if (gap < 0) {
                msg.removeClass('alert-warning alert-success').addClass('alert-danger').html('🚨 OVER LIMIT! Cek hitungan Part (Lebih: ' + Math.abs(gap) + ').'); 
                btn.prop('disabled', true); 
            } else { 
                msg.removeClass('alert-success alert-danger').addClass('alert-warning').html('👮 WAITING SYNC... Gap: ' + gap.toLocaleString() + ' Pcs'); 
                btn.prop('disabled', true); 
            }
        });

        // AJAX Cascading Dropdown Modal Ambil Material
        $('#sel_customer').change(function() {
            $.get('/produksi/get-specs/' + $(this).val(), function(data) {
                let h = '<option value="" disabled selected>-- SELECT SPEC --</option>';
                data.forEach(i => { h += `<option value="${i.spec}" data-size="${i.size}">${i.spec} [${i.size}]</option>`; });
                $('#sel_spec').prop('disabled', false).html(h);
                $('#sel_part').prop('disabled', true).html('<option value="" disabled selected>-- SELECT SPEC FIRST --</option>');
                $('#sel_bandel').prop('disabled', true).html('<option value="" disabled selected>-- SELECT PART FIRST --</option>');
            });
        });

        $('#sel_spec').change(function() {
            let s = $(this).find(':selected').data('size');
            $.get('/produksi/get-parts-by-spec', {customer: $('#sel_customer').val(), spec: $(this).val(), size: s}, function(data) {
                let h = '';
                data.forEach(i => { h += `<option value="${i.material_code}">${i.material_code}</option>`; });
                $('#sel_part').prop('disabled', false).html(h);
                $('#sel_bandel').prop('disabled', true).html('<option value="" disabled selected>-- SELECT PART FIRST --</option>');
            });
        });

        $('#btn_add_manual_part').click(function() {
            let newPart = $('#manual_part_input').val().trim();
            if (newPart !== '') {
                let opt = new Option(newPart, newPart, true, true);
                $('#sel_part').append(opt).trigger('change');
                $('#manual_part_input').val('');
            }
        });

        $('#sel_part').change(function() {
            let selectedParts = $(this).val(); 
            let partVal = (selectedParts && selectedParts.length > 0) ? selectedParts[0] : null;

            if (!partVal) {
                $('#sel_bandel').prop('disabled', true).html('<option value="" disabled selected>-- SELECT PART FIRST --</option>');
                return;
            }

            let custVal = $('#sel_customer').val();
            let specVal = $('#sel_spec').val();
            let sizeVal = $('#sel_spec').find(':selected').data('size');

            $.get('/produksi/get-bundles', {
                material_code: partVal,
                customer: custVal,
                spec: specVal,
                size: sizeVal
            }, function(data) {
                let h = '<option value="" disabled selected>-- SELECT COIL --</option>';
                if(data && data.length > 0) {
                    data.forEach(i => { 
                        h += `<option value="${i.id}" data-qty="${i.stock_pcs}" data-cavity="${i.cavity || 1}">${i.coil_id} (Avail: ${i.stock_pcs})</option>`; 
                    });
                } else {
                    h = '<option value="" disabled selected>-- NO COIL AVAILABLE --</option>';
                }
                $('#sel_bandel').prop('disabled', false).html(h);
            });
        });

        $('#sel_bandel').change(function() {
            let selectedCavity = $(this).find('option:selected').data('cavity') || 1;
            $('#cavity_input').val(selectedCavity);
        });

        $('#sel_bandel, #qty_ambil_pcs, select[name="mesin_id"]').on('change input', function() {
            let maxStok = parseInt($('#sel_bandel option:selected').data('qty')) || 0;
            let inputQty = parseInt($('#qty_ambil_pcs').val()) || 0;
            let mesinSelected = $('select[name="mesin_id"]').val();
            $('#btn_submit_ambil').prop('disabled', !(inputQty > 0 && inputQty <= maxStok && mesinSelected));
        });
    });
</script>
@endsection