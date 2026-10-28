@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Orbitron:wght@600;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --oneui-primary: #0381fe; --oneui-primary-soft: rgba(3, 129, 254, 0.08); --oneui-bg: #f4f6fa;
        --oneui-card: #ffffff; --oneui-border: #e9ecef; --oneui-text-title: #11142d;
        --oneui-text-sub: #6c757d; --oneui-success: #00c073; --oneui-warning: #ff8a00;
        --oneui-danger: #ff3b30; --oneui-spring: cubic-bezier(0.16, 1, 0.3, 1);
    }
    .oneui-container { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif; color: var(--oneui-text-title); }

    /* 🔘 TACTILE ONE UI BUTTONS */
    .btn-oneui { border-radius: 9999px; font-weight: 700; font-size: 11.5px; padding: 8px 18px; border: none; transition: transform 0.2s var(--oneui-spring), box-shadow 0.2s ease, background-color 0.2s ease; display: inline-flex; align-items: center; gap: 6px; }
    .btn-oneui:active { transform: scale(0.95) !important; }
    .btn-oneui-primary { background: var(--oneui-primary); color: #fff !important; box-shadow: 0 4px 14px rgba(3, 129, 254, 0.25); }
    .btn-oneui-primary:hover { background: #026cd5; color: #fff !important; }
    .btn-oneui-dark { background: #1e293b; color: #fff !important; }
    .btn-oneui-dark:hover { background: #0f172a; color: #fff !important; }

    /* 🎛️ SEGMENTED ROOM SWITCHER */
    .oneui-segmented-wrap { background: #e9ecef; padding: 4px; border-radius: 9999px; display: inline-flex; gap: 4px; }
    .oneui-segmented-btn { padding: 6px 16px; border-radius: 9999px; font-size: 11.5px; font-weight: 700; color: #64748b; text-decoration: none !important; transition: all 0.25s var(--oneui-spring); }
    .oneui-segmented-btn:active { transform: scale(0.96); }
    .oneui-segmented-btn.active { background: #ffffff; color: #0f172a; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); }

    /* 📊 ONE UI METRIC SQUIRCLES */
    .oneui-metric-card { background: var(--oneui-card); border: 1px solid var(--oneui-border); border-radius: 20px; padding: 18px 22px; height: 100%; box-shadow: 0 4px 20px -4px rgba(0, 0, 0, 0.03); transition: transform 0.25s var(--oneui-spring); }
    .oneui-metric-card:hover { transform: translateY(-3px); }
    .oneui-metric-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--oneui-text-sub); }
    .oneui-metric-value { font-family: 'JetBrains Mono', monospace; font-size: 26px; font-weight: 800; line-height: 1.1; margin-top: 4px; display: block; }

    /* 📋 ONE UI LINE CONTAINER & TABLE */
    .oneui-line-card { background: var(--oneui-card); border: 1px solid var(--oneui-border); border-radius: 22px; overflow: hidden; margin-bottom: 24px; box-shadow: 0 4px 20px -4px rgba(0, 0, 0, 0.04); }
    .oneui-line-header { padding: 16px 24px; background: #ffffff; border-bottom: 1px solid var(--oneui-border); display: flex; justify-content: space-between; align-items: center; }
    .oneui-line-title { font-size: 14px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 10px; }

    .table-oneui { width: 100%; margin-bottom: 0; border-collapse: collapse; text-align: center; }
    .table-oneui thead th { background: #f8fafc; color: #64748b; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; padding: 12px 16px; border-top: none; border-bottom: 1px solid var(--oneui-border); vertical-align: middle; text-align: center; }
    .table-oneui tbody td { padding: 13px 16px; font-size: 12.5px; vertical-align: middle; border-top: 1px solid #f1f3f5; }
    .table-oneui tbody tr:hover { background-color: #f8fafc; }

    .shift-divider-row td { background: #f8fafc; font-weight: 800; font-size: 10.5px; color: #475569; text-transform: uppercase; letter-spacing: 0.8px; padding: 8px 16px; text-align: left; }
    .part-code-text { font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 13px; color: var(--oneui-primary); display: block; }
    .customer-sub { font-size: 10px; color: #64748b; font-weight: 700; margin-top: 2px; display: block; }
    
    .badge-pill-status { font-family: 'JetBrains Mono', monospace; font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 4px; }
    .badge-pill-success { background: #e6f9f1; color: #00965e; }
    .badge-pill-danger { background: #ffebeb; color: #e02424; }

    .font-family-jetbrains { font-family: 'JetBrains Mono', monospace; }
    .rev-badge { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; padding: 3px 8px; border-radius: 6px; font-size: 10px; font-weight: 900; margin-left: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }

    /* 🏁 ONE UI TABLE FOOTER */
    .oneui-line-footer { background: #0f172a; color: #ffffff; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 800; }
    .oneui-line-footer b { font-family: 'JetBrains Mono', monospace; font-size: 14px; color: #38bdf8; }

    /* 🛡️ SAMSUNG ONE UI MODAL ANIMATION & STYLING */
    .modal.fade .modal-dialog { transform: scale(0.93) translateY(24px); opacity: 0; transition: transform 0.3s var(--oneui-spring), opacity 0.25s ease; }
    .modal.show .modal-dialog { transform: scale(1) translateY(0); opacity: 1; }
    .modal-oneui .modal-content { border-radius: 24px; border: none; overflow: hidden; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15); }
    .modal-oneui .modal-header { background: #ffffff; padding: 20px 26px; border-bottom: 1px solid var(--oneui-border); }
    .modal-oneui .modal-title { font-size: 15px; font-weight: 800; color: var(--oneui-text-title); margin: 0; }
    .modal-oneui label { font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.4px; margin-bottom: 6px; }
    .modal-oneui .form-control { border-radius: 12px; border: 1.5px solid var(--oneui-border); background: #f8fafc; font-size: 12.5px; padding: 10px 14px; height: auto; color: #0f172a; transition: all 0.2s ease; }
    .modal-oneui .form-control:focus { background: #ffffff; border-color: var(--oneui-primary); box-shadow: 0 0 0 4px rgba(3, 129, 254, 0.12); }
    
    .oneui-time-banner { background: linear-gradient(135deg, #f0f7ff 0%, #e1effe 100%); border: 1px solid #bfdbfe; border-radius: 16px; padding: 14px 18px; }
</style>

<div class="oneui-container mb-5">

    {{-- HEADER ATAS & SEGMENTED SWITCHER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge badge-warning text-dark font-weight-bold px-3 py-1 mr-2" style="border-radius: 9999px; font-size: 9px; letter-spacing: 0.5px;">SMALL PRESS</span>
                <h3 class="m-0 font-weight-bold" style="font-size: 20px;">WOS Terminal <span style="color: var(--oneui-primary);">Smart MRP</span></h3>
            </div>
            <div class="text-muted small font-weight-bold">
                <i class="far fa-calendar-check mr-1" style="color: var(--oneui-primary);"></i> Tanggal Operasional: <b>{{ date('d F Y', strtotime($date)) }}</b>
            </div>
        </div>

        <div class="d-flex align-items-center flex-wrap gap-2 mt-3 mt-md-0">
            <div class="oneui-segmented-wrap mr-3">
                <a href="{{ route('ppic.mps.index') }}" class="oneui-segmented-btn">
                    <i class="fas fa-industry mr-1"></i> Big Press
                </a>
                <a href="{{ route('ppic.mps.small') }}" class="oneui-segmented-btn active">
                    <i class="fas fa-cogs mr-1"></i> Small Press
                </a>
            </div>

            <form action="{{ route('ppic.mps.small') }}" method="GET" class="d-flex align-items-center mr-2">
                <input type="date" name="date" value="{{ $date }}" class="form-control font-weight-bold border" onchange="this.form.submit()" style="border-radius: 9999px; font-size: 12px; padding: 6px 14px; height: auto;">
            </form>

            <a href="{{ route('ppic.wos.print_bundle', ['date' => $date]) }}" target="_blank" class="btn-oneui btn-oneui-dark mr-2">
                <i class="fas fa-print"></i> Cetak Semua WOS
            </a>

            <button class="btn-oneui btn-oneui-primary" data-toggle="modal" data-target="#registerPlanSmallModal">
                <i class="fas fa-plus-circle"></i> Tambah Jadwal
            </button>
        </div>
    </div>

    {{-- 4 KARTU KPI METRIC --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="oneui-metric-card" style="border-left: 4px solid var(--oneui-primary);">
                <span class="oneui-metric-label">Total Target (Qty)</span>
                <span class="oneui-metric-value" style="color: var(--oneui-primary);">{{ number_format($totalPlanQty) }} <span style="font-size: 13px;">PCS</span></span>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="oneui-metric-card" style="border-left: 4px solid #0284c7;">
                <span class="oneui-metric-label">Beban Kerja (Jam)</span>
                <span class="oneui-metric-value" style="color: #0284c7; font-size: 22px;">
                    S1: {{ number_format($totalWorkingHoursS1, 1) }}h <span style="color:#cbd5e1; font-size:16px;">|</span> S2: {{ number_format($totalWorkingHoursS2, 1) }}h
                </span>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="oneui-metric-card" style="border-left: 4px solid var(--oneui-warning);">
                <span class="oneui-metric-label">Dandory Loss</span>
                <span class="oneui-metric-value" style="color: var(--oneui-warning);">{{ number_format($totalDandory) }} <span style="font-size: 13px;">MNT</span></span>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="oneui-metric-card" style="border-left: 4px solid #334155; background: #f8fafc;">
                <span class="oneui-metric-label">Cakupan Data</span>
                <span class="oneui-metric-value" style="font-size: 20px; color: #1e293b;">FULL DAY (S1+S2)</span>
            </div>
        </div>
    </div>

    {{-- DAFTAR JADWAL MESIN SMALL PRESS --}}
    @forelse($groupedPlans as $lineCode => $plans)
    <div class="oneui-line-card">
        <div class="oneui-line-header">
            <h6 class="oneui-line-title">
                <i class="fas fa-microchip text-primary"></i> LINE MESIN: <b style="font-family: 'Orbitron';">{{ strtoupper($lineCode) }}</b>
            </h6>
            <div>
                <a href="{{ route('ppic.wos.print_small', ['date' => $date, 'shift' => 'ALL', 'line_code' => $lineCode]) }}" target="_blank" class="btn-oneui btn-outline-primary py-1 px-3 mr-1" style="border: 1px solid #bfdbfe; background: #eff6ff;">
                    <i class="fas fa-file-alt"></i> WOS
                </a>
                <a href="{{ route('ppic.serahterima.print_small', ['date' => $date, 'shift' => 'ALL', 'line_code' => $lineCode]) }}" target="_blank" class="btn-oneui btn-outline-success py-1 px-3" style="border: 1px solid #bbf7d0; background: #f0fdf4;">
                    <i class="fas fa-pallet"></i> BPRM
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-oneui">
                <thead>
                    <tr>
                        <th width="4%" class="text-center">#</th>
                        <th width="22%" class="text-left pl-4">Part Identification</th>
                        <th width="6%">MP</th>
                        <th width="6%">Proc</th>
                        <th width="10%" class="text-right">Target</th>
                        <th width="10%" class="text-right text-primary">Actual</th>
                        <th width="10%">Balance</th>
                        <th width="8%">Mulai</th>
                        <th width="8%">Selesai</th>
                        <th width="8%">Dandory</th>
                        <th width="8%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $plansS1 = $plans->where('display_shift', 'S1'); 
                        $plansS2 = $plans->where('display_shift', 'S2'); 
                    @endphp

                    {{-- SHIFT 1 --}}
                    @if($plansS1->count() > 0)
                        <tr class="shift-divider-row">
                            <td colspan="11" class="pl-4"><i class="fas fa-sun text-warning mr-1" style="font-size: 14px;"></i> SHIFT 1 (OPERASIONAL PAGI)</td>
                        </tr>
                        @foreach($plansS1 as $idx => $p)
                        @php
                            // Status Badge
                            if ($p->balance < 0) { $balanceStatus = 'badge-pill-success'; $balanceIcon = 'fa-plus-circle'; $balanceText = '+' . number_format(abs($p->balance)); } 
                            elseif ($p->balance > 0) { $balanceStatus = 'badge-pill-danger'; $balanceIcon = 'fa-clock'; $balanceText = 'Sisa ' . number_format($p->balance); } 
                            else { $balanceStatus = 'badge-pill-success'; $balanceIcon = 'fa-check-circle'; $balanceText = 'Selesai'; }

                            // Revisi Label
                            $revLabel = '';
                            if (isset($p->keterangan) && preg_match('/\(R(\d+)\)/', $p->keterangan, $matches)) {
                                $revLabel = "<span class='rev-badge shadow-sm'><i class='fas fa-info-circle mr-1'></i>REV-{$matches[1]}</span>";
                            }
                        @endphp
                        <tr>
                            <td class="text-center font-weight-bold text-muted">{{ $loop->iteration }}</td>
                            <td class="text-left pl-4 part-cell">
                                <span class="part-code-text">{{ $p->part_no }} {!! $revLabel !!}</span>
                                <span class="customer-sub">{{ $p->customer_code ?? 'CUSTOMER' }}</span>
                            </td>
                            <td class="text-center"><span class="badge badge-light border font-weight-bold px-2 py-1" style="border-radius:6px;">{{ $p->manpower ?? 2 }}P</span></td>
                            <td class="text-center font-weight-bold">{{ $p->process_qty ?? 1 }}</td>
                            <td class="text-right font-weight-bold font-family-jetbrains">{{ number_format($p->total_target) }}</td>
                            <td class="text-right font-weight-bold font-family-jetbrains text-primary">{{ number_format($p->total_actual) }}</td>
                            <td class="text-center">
                                <span class="badge-pill-status {{ $balanceStatus }}"><i class="fas {{ $balanceIcon }}"></i> {{ $balanceText }}</span>
                            </td>
                            <td class="text-center font-family-jetbrains small font-weight-bold text-muted">{{ $p->start_time ?? '-' }}</td>
                            <td class="text-center font-family-jetbrains small font-weight-bold text-dark">{{ $p->ahir_time ?? '-' }}</td>
                            <td class="text-center font-family-jetbrains small text-muted">{{ $p->dandory_time ?? 15 }}m</td>
                            <td class="text-center">
                                @if($p->total_actual > 0)
                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm border" disabled title="Terkunci: Produksi Sudah Mulai" style="width: 32px; height: 32px;">
                                        <i class="fas fa-lock text-muted" style="font-size: 11px;"></i>
                                    </button>
                                @else
                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm border" data-toggle="modal" data-target="#editModal{{ $p->id }}" title="Revisi Jadwal" style="width: 32px; height: 32px;">
                                        <i class="fas fa-pen text-primary" style="font-size: 11px;"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    @endif

                    {{-- SHIFT 2 --}}
                    @if($plansS2->count() > 0)
                        <tr class="shift-divider-row">
                            <td colspan="11" class="pl-4"><i class="fas fa-moon text-primary mr-1" style="font-size: 14px;"></i> SHIFT 2 (OPERASIONAL MALAM)</td>
                        </tr>
                        @foreach($plansS2 as $idx => $p)
                        @php
                            if ($p->balance < 0) { $balanceStatus = 'badge-pill-success'; $balanceIcon = 'fa-plus-circle'; $balanceText = '+' . number_format(abs($p->balance)); } 
                            elseif ($p->balance > 0) { $balanceStatus = 'badge-pill-danger'; $balanceIcon = 'fa-clock'; $balanceText = 'Sisa ' . number_format($p->balance); } 
                            else { $balanceStatus = 'badge-pill-success'; $balanceIcon = 'fa-check-circle'; $balanceText = 'Selesai'; }

                            $revLabel = '';
                            if (isset($p->keterangan) && preg_match('/\(R(\d+)\)/', $p->keterangan, $matches)) {
                                $revLabel = "<span class='rev-badge shadow-sm'><i class='fas fa-info-circle mr-1'></i>REV-{$matches[1]}</span>";
                            }
                        @endphp
                        <tr>
                            <td class="text-center font-weight-bold text-muted">{{ $loop->iteration }}</td>
                            <td class="text-left pl-4 part-cell">
                                <span class="part-code-text">{{ $p->part_no }} {!! $revLabel !!}</span>
                                <span class="customer-sub">{{ $p->customer_code ?? 'CUSTOMER' }}</span>
                            </td>
                            <td class="text-center"><span class="badge badge-light border font-weight-bold px-2 py-1" style="border-radius:6px;">{{ $p->manpower ?? 2 }}P</span></td>
                            <td class="text-center font-weight-bold">{{ $p->process_qty ?? 1 }}</td>
                            <td class="text-right font-weight-bold font-family-jetbrains">{{ number_format($p->total_target) }}</td>
                            <td class="text-right font-weight-bold font-family-jetbrains text-primary">{{ number_format($p->total_actual) }}</td>
                            <td class="text-center">
                                <span class="badge-pill-status {{ $balanceStatus }}"><i class="fas {{ $balanceIcon }}"></i> {{ $balanceText }}</span>
                            </td>
                            <td class="text-center font-family-jetbrains small font-weight-bold text-muted">{{ $p->start_time ?? '-' }}</td>
                            <td class="text-center font-family-jetbrains small font-weight-bold text-dark">{{ $p->ahir_time ?? '-' }}</td>
                            <td class="text-center font-family-jetbrains small text-muted">{{ $p->dandory_time ?? 15 }}m</td>
                            <td class="text-center">
                                @if($p->total_actual > 0)
                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm border" disabled title="Terkunci: Produksi Sudah Mulai" style="width: 32px; height: 32px;">
                                        <i class="fas fa-lock text-muted" style="font-size: 11px;"></i>
                                    </button>
                                @else
                                    <button class="btn btn-light btn-sm rounded-circle shadow-sm border" data-toggle="modal" data-target="#editModal{{ $p->id }}" title="Revisi Jadwal" style="width: 32px; height: 32px;">
                                        <i class="fas fa-pen text-primary" style="font-size: 11px;"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        
        @php $lineTargetTotal = $plans->sum('total_target'); @endphp
        <div class="oneui-line-footer">
            <span>Total Target Harian Mesin: <b>{{ number_format($lineTargetTotal) }} PCS</b></span>
            <span>Status: <b style="color: #4ade80;"><i class="fas fa-check-circle mr-1"></i> Antrean Aktif</b></span>
        </div>
    </div>
    @empty
    <div class="card p-5 text-center bg-white shadow-sm" style="border-radius: 20px; border: 1px solid var(--oneui-border);">
        <i class="fas fa-calendar-times text-muted fa-3x mb-3"></i>
        <h6 class="font-weight-bold text-dark">Belum Ada Jadwal Small Press</h6>
        <p class="text-muted small">Klik tombol <b>Tambah Jadwal</b> di pojok kanan atas untuk menambahkan antrean Small Press pada tanggal {{ date('d F Y', strtotime($date)) }}.</p>
    </div>
    @endforelse

</div>

{{-- =========================================================================
     MODAL SECTION (DIKELUARKAN DARI TABEL AGAR TIDAK TERPOTONG)
     ========================================================================= --}}

{{-- 🛡️ MODAL REGISTRASI PLAN ONE UI --}}
<div class="modal fade modal-oneui" id="registerPlanSmallModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h6 class="modal-title font-weight-bold">Registrasi Jadwal Baru</h6>
                    <span class="small font-weight-bold text-muted">Area Mesin Small Press // Tanggal: {{ date('d M Y', strtotime($date)) }}</span>
                </div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('ppic.mps.store') }}" method="POST">
                @csrf
                <input type="hidden" name="plan_date" value="{{ $date }}">
                <div class="modal-body p-4 bg-white">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label><i class="fas fa-cogs mr-1 text-primary"></i> Line Mesin Small Press</label>
                            <select name="line_code" class="form-control font-weight-bold" required>
                                @foreach($availableLines as $l)
                                    <option value="{{ $l->kode_Line }}">{{ $l->kode_Line }} - {{ $l->nama_Line }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label><i class="fas fa-building mr-1 text-primary"></i> Customer Entity</label>
                            <select name="customer_code" id="customer_select" class="form-control font-weight-bold" required onchange="filterPartsByCustomer(this.value)">
                                <option value="">-- PILIH CUSTOMER --</option>
                                @foreach($availableCustomers as $c)
                                    <option value="{{ $c->code }}">{{ $c->name }} ({{ $c->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="m-0"><i class="fas fa-cube mr-1 text-primary"></i> Part Number Utama</label>
                                <a href="javascript:void(0)" onclick="toggleManualInput('part_no')" class="text-primary font-weight-bold" style="font-size: 10px; text-decoration: none;">
                                    <i class="fas fa-keyboard mr-1"></i> Ketik Manual
                                </a>
                            </div>
                            <div id="part_no_select_wrap">
                                <select name="part_no" id="part_no_select" class="form-control font-weight-bold font-family-jetbrains" required>
                                    <option value="">-- PILIH CUSTOMER DULU --</option>
                                </select>
                            </div>
                            <div id="part_no_manual_wrap" style="display: none;">
                                <input type="text" id="part_no_manual" class="form-control text-uppercase font-weight-bold font-family-jetbrains" placeholder="Ketik Part No manual...">
                            </div>
                        </div>

                        <div class="col-md-6 form-group">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="m-0"><i class="fas fa-link mr-1 text-primary"></i> Separating Part (Opsional)</label>
                                <a href="javascript:void(0)" onclick="toggleManualInput('part_separator')" class="text-primary font-weight-bold" style="font-size: 10px; text-decoration: none;">
                                    <i class="fas fa-keyboard mr-1"></i> Ketik Manual
                                </a>
                            </div>
                            <div id="part_separator_select_wrap">
                                <select name="part_no_separator" id="part_separator_select" class="form-control font-weight-bold font-family-jetbrains">
                                    <option value="">-- TIDAK ADA / OPSIONAL --</option>
                                </select>
                            </div>
                            <div id="part_separator_manual_wrap" style="display: none;">
                                <input type="text" id="part_separator_manual" class="form-control text-uppercase font-weight-bold font-family-jetbrains" placeholder="Ketik Part Separator manual...">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-sun text-warning mr-1"></i> Target Shift 1 (Pcs)</label>
                            <input type="number" name="s1_plan_reg" id="in_target_s1" class="form-control font-weight-bold font-family-jetbrains" value="0" min="0" oninput="calculateLiveTime()">
                        </div>
                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-moon text-primary mr-1"></i> Target Shift 2 (Pcs)</label>
                            <input type="number" name="s2_plan_reg" id="in_target_s2" class="form-control font-weight-bold font-family-jetbrains" value="0" min="0" oninput="calculateLiveTime()">
                        </div>
                        <div class="col-md-4 form-group">
                            <label><i class="fas fa-clock text-danger mr-1"></i> Jam Mulai (Start Time)</label>
                            <input type="time" name="start_time" id="in_start_time" class="form-control font-weight-bold font-family-jetbrains" value="07:30" oninput="calculateLiveTime()">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Manpower (MP)</label>
                            <input type="number" name="manpower" class="form-control font-weight-bold text-center" value="2">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Process Qty</label>
                            <input type="number" name="process_qty" class="form-control font-weight-bold text-center" value="1">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Kapasitas / Jam</label>
                            <input type="number" name="cap_per_hour" id="in_cap_hour" class="form-control font-weight-bold font-family-jetbrains text-center" value="300" oninput="calculateLiveTime()">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Dandory (Menit)</label>
                            <input type="number" name="dandory_time" id="in_dandory" class="form-control font-weight-bold font-family-jetbrains text-center" value="15" oninput="calculateLiveTime()">
                        </div>
                    </div>

                    <div class="oneui-time-banner mt-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted font-weight-bold text-uppercase d-block" style="font-size: 10px;">Estimasi Durasi Pengerjaan:</small>
                                <span class="font-weight-bold font-family-jetbrains text-primary h6 m-0" id="live_duration">0.0 Jam</span>
                            </div>
                            <div class="text-right">
                                <small class="text-muted font-weight-bold text-uppercase d-block" style="font-size: 10px;">Estimasi Jam Selesai:</small>
                                <span class="font-weight-bold font-family-jetbrains text-success h5 m-0" id="live_finish_time">--:--</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3 border-top-0 justify-content-between">
                    <button type="button" class="btn-oneui text-muted bg-white border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-oneui btn-oneui-primary px-4"><i class="fas fa-rocket mr-1"></i> Deploy Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 🛠️ KUMPULAN MODAL EDIT (REVISI) --}}
@foreach($groupedPlans as $lineCode => $plans)
    @foreach($plans as $p)
        @if($p->total_actual == 0)
        <div class="modal fade modal-oneui" id="editModal{{ $p->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-white">
                        <div>
                            <h6 class="modal-title font-weight-bold">Revisi Target Produksi</h6>
                            <span class="small font-weight-bold text-primary font-family-jetbrains">{{ $p->part_no }} // Line {{ $p->line_code }}</span>
                        </div>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('ppic.wos.update', $p->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body p-4 bg-white">
                            <div class="p-3 mb-3 border rounded-lg" style="background: #fffbeb; border-color: #fde68a !important;">
                                <div class="d-flex align-items-center text-dark small font-weight-bold" style="color: #b45309 !important;">
                                    <i class="fas fa-exclamation-circle text-warning mr-2" style="font-size: 14px;"></i>
                                    <span>Mengisi target menjadi <b>0</b> akan otomatis membatalkan jadwal dan mengembalikan lembar bahan ke Gudang RM.</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Target Shift 1 (Pcs)</label>
                                    <input type="number" name="s1_plan_reg" class="form-control font-weight-bold font-family-jetbrains text-center" value="{{ $p->s1_plan_reg }}" min="0">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Target Shift 2 (Pcs)</label>
                                    <input type="number" name="s2_plan_reg" class="form-control font-weight-bold font-family-jetbrains text-center" value="{{ $p->s2_plan_reg }}" min="0">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Kapasitas Mesin / Jam</label>
                                    <input type="number" name="cap_per_hour" class="form-control font-weight-bold font-family-jetbrains text-center" value="{{ $p->cap_per_hour }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Dandory (Menit)</label>
                                    <input type="number" name="dandory_time" class="form-control font-weight-bold font-family-jetbrains text-center" value="{{ $p->dandory_time ?? 15 }}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light px-4 py-3 justify-content-between border-top-0">
                            <button type="button" class="btn-oneui bg-white text-muted border" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn-oneui" style="background: var(--oneui-warning); color: #fff;"><i class="fas fa-sync-alt"></i> Simpan Revisi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endforeach
@endforeach

{{-- SCRIPT --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const allParts = @json($availableParts ?? []);

    function filterPartsByCustomer(customerCode) {
        const partSelect = document.getElementById('part_no_select');
        const partSeparatorSelect = document.getElementById('part_separator_select');

        partSelect.innerHTML = '';
        partSeparatorSelect.innerHTML = '<option value="">-- TIDAK ADA / OPSIONAL --</option>';

        if (!customerCode) {
            partSelect.innerHTML = '<option value="">-- PILIH CUSTOMER TERLEBIH DAHULU --</option>';
            return;
        }

        const cleanCust = customerCode.trim().toUpperCase();
        const filteredParts = allParts.filter(p => (p.customer_code || '').trim().toUpperCase() === cleanCust);

        if (filteredParts.length === 0) {
            partSelect.innerHTML = '<option value="">-- TIDAK ADA PART UNTUK CUSTOMER INI --</option>';
        } else {
            partSelect.innerHTML = '<option value="">-- PILIH PART NUMBER UTAMA --</option>';
            filteredParts.forEach(p => {
                const label = p.part_no + (p.part_name ? ' - ' + p.part_name : '');
                
                const opt1 = document.createElement('option');
                opt1.value = p.part_no;
                opt1.textContent = label;
                partSelect.appendChild(opt1);

                const opt2 = document.createElement('option');
                opt2.value = p.part_no;
                opt2.textContent = label;
                partSeparatorSelect.appendChild(opt2);
            });
        }
    }

    function toggleManualInput(field) {
        const selectWrap = document.getElementById(field + '_select_wrap');
        const manualWrap = document.getElementById(field + '_manual_wrap');
        const selectEl = document.getElementById(field + '_select');
        const manualEl = document.getElementById(field + '_manual');

        if (manualWrap.style.display === 'none') {
            manualWrap.style.display = 'block';
            selectWrap.style.display = 'none';
            selectEl.removeAttribute('name');
            manualEl.setAttribute('name', field === 'part_separator' ? 'part_no_separator' : 'part_no');
            manualEl.focus();
        } else {
            manualWrap.style.display = 'none';
            selectWrap.style.display = 'block';
            manualEl.removeAttribute('name');
            selectEl.setAttribute('name', field === 'part_separator' ? 'part_no_separator' : 'part_no');
        }
    }

    function calculateLiveTime() {
        const s1 = parseInt(document.getElementById('in_target_s1').value) || 0;
        const s2 = parseInt(document.getElementById('in_target_s2').value) || 0;
        const cap = parseInt(document.getElementById('in_cap_hour').value) || 0;
        const dandory = parseInt(document.getElementById('in_dandory').value) || 0;
        const startTimeStr = document.getElementById('in_start_time').value || "07:30";

        const totalQty = s1 + s2;
        if (totalQty <= 0 || cap <= 0) {
            document.getElementById('live_duration').innerText = "0.0 Jam";
            document.getElementById('live_finish_time').innerText = "--:--";
            return;
        }

        const totalHours = (totalQty / cap) + (dandory / 60);
        const totalMinutes = Math.round(totalHours * 60);

        document.getElementById('live_duration').innerText = totalHours.toFixed(1) + " Jam (" + totalMinutes + " Mnt)";

        const parts = startTimeStr.split(':');
        let startMinutes = parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
        let finishMinutes = startMinutes + totalMinutes;

        let finishHour = Math.floor(finishMinutes / 60) % 24;
        let finishMin = finishMinutes % 60;

        let formattedTime = String(finishHour).padStart(2, '0') + ":" + String(finishMin).padStart(2, '0');
        document.getElementById('live_finish_time').innerText = formattedTime;
    }

    $(document).ready(function() {
        let submitBtn = $('button:contains("Deploy Jadwal")');
        if(submitBtn.length === 0) submitBtn = $('#registerPlanSmallModal').find('button[type="submit"]');
        
        submitBtn.parent().prepend('<div id="rmValidationMsg" class="w-100 text-center mb-3" style="font-size:12px; transition: 0.3s;"></div>');
        
        function validateRmStock() {
            let s1_reg = parseInt($('input[name="s1_plan_reg"]').val()) || 0;
            let s2_reg = parseInt($('input[name="s2_plan_reg"]').val()) || 0;
            
            let modal = $('#registerPlanSmallModal');
            let currentRmStock = modal.data('rm-stock') || 0;
            let currentCavity = modal.data('cavity') || 1;
            
            let totalPcs = s1_reg + s2_reg;
            let requiredSheet = Math.ceil(totalPcs / currentCavity);
            let msgBox = $('#rmValidationMsg');
            
            if (totalPcs === 0) {
                msgBox.html('<span class="text-muted font-weight-bold"><i class="fas fa-info-circle"></i> Input target qty untuk cek ketersediaan material.</span>');
                submitBtn.prop('disabled', true);
                return;
            }
            
            if (requiredSheet > currentRmStock) {
                msgBox.html(`
                    <div class="alert alert-danger p-2 m-0 border-0 shadow-sm animate__animated animate__headShake" style="border-radius:10px;">
                        <b>❌ STOK RM TIDAK CUKUP!</b><br>
                        Butuh: <b style="font-size:14px;">${requiredSheet}</b> Lembar | Tersedia: <b>${currentRmStock}</b> Lembar (Cav: ${currentCavity})
                    </div>
                `);
                submitBtn.prop('disabled', true).css('opacity', '0.6');
            } else {
                msgBox.html(`
                    <div class="alert alert-success p-2 m-0 border-0 shadow-sm" style="border-radius:10px; background-color: #d1fae5; color: #065f46;">
                        <b>✅ STOK RM AMAN!</b><br>
                        Butuh: <b>${requiredSheet}</b> Lembar | Tersedia: <b>${currentRmStock}</b> Lembar (Cav: ${currentCavity})
                    </div>
                `);
                submitBtn.prop('disabled', false).css('opacity', '1');
            }
        }
        
        $('select[name="part_no"]').on('change', function() {
            let partNo = $(this).val();
            if (!partNo) return;
            
            $('#rmValidationMsg').html('<span class="text-warning font-weight-bold"><i class="fas fa-spinner fa-spin"></i> Sedang mengecek stok di Gudang RM...</span>');
            submitBtn.prop('disabled', true);
            
            $.get('/planning/ppic/check-rm-stock/' + encodeURIComponent(partNo), function(res) {
                if (res.status === 'success') {
                    $('#registerPlanSmallModal').data('rm-stock', res.total_stock);
                    $('#registerPlanSmallModal').data('cavity', res.cavity);
                    validateRmStock();
                }
            }).fail(function() {
                $('#rmValidationMsg').html('<span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle"></i> Gagal terhubung ke database gudang.</span>');
            });
        });
        
        $('#in_target_s1, #in_target_s2').on('input', function() {
            validateRmStock();
        });
    });
</script>
@endsection