@extends('layout.admin')

@section('content')
<style>
    /* 🏛️ CLEAN INDUSTRIAL WOS THEME */
    .wos-container {
        font-family: 'Plus Jakarta Sans', Arial, sans-serif;
    }
    
    .wos-title-box h3 {
        font-family: 'JetBrains Mono', 'Plus Jakarta Sans', monospace;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: #0f172a;
        margin: 0;
    }

    /* 📊 KPI TOP CARDS */
    .metric-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 18px 20px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
    }
    .metric-card .card-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .metric-card .card-value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 26px;
        font-weight: 800;
        line-height: 1.1;
    }
    .metric-card-scope {
        background: #334155;
        color: #ffffff;
        border-color: #1e293b;
    }

    /* 📋 LINE CONTAINER & INDUSTRIAL TABLE */
    .line-block {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .line-header {
        background: #ffffff;
        padding: 14px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .line-header-title {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-wos {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .table-wos thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 10.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 14px;
        border-top: none;
        border-bottom: 2px solid #cbd5e1;
        vertical-align: middle;
    }
    .table-wos tbody td {
        padding: 12px 14px;
        font-size: 12px;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
        color: #1e293b;
    }
    .table-wos tbody tr:hover {
        background-color: #f8fafc;
    }

    /* 🏷️ BADGES & HIGHLIGHTS */
    .shift-header-row td {
        background: #f1f5f9;
        font-weight: 800;
        font-size: 10.5px;
        text-transform: uppercase;
        color: #475569;
        padding: 8px 14px;
        letter-spacing: 0.5px;
    }
    .part-title {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 800;
        font-size: 13px;
        color: #1d4ed8;
        display: block;
    }
    .customer-sub {
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
    }
    .pill-badge {
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .pill-green {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #86efac;
    }
    .pill-red {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
    }

    /* 🏁 TABLE SUMMARY FOOTER */
    .line-footer-summary {
        background: #059669;
        color: #ffffff;
        padding: 12px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12.5px;
        font-weight: 800;
    }
    .line-footer-summary span b {
        font-family: 'JetBrains Mono', monospace;
        font-size: 14px;
    }
</style>

<div class="wos-container mb-5">

    {{-- 🔘 HEADER ATAS & KAMAR SWITCHER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 mr-2" style="font-size: 10px;">SMALL PRESS AREA</span>
                <h3 class="m-0 font-weight-bold">WOS_TERMINAL <span class="text-primary">Smart MRP</span></h3>
            </div>
            <div class="text-muted small font-weight-bold">
                <i class="far fa-calendar-alt text-primary mr-1"></i> Operational Date: <b>{{ date('d F Y', strtotime($date)) }}</b>
            </div>
        </div>

        <div class="d-flex align-items-center flex-wrap gap-2 mt-3 mt-md-0">
            {{-- Navigasi Switcher Kamar (Big vs Small Press) --}}
            <div class="btn-group mr-3 border bg-white rounded p-1">
                <a href="{{ route('ppic.mps.index') }}" class="btn btn-sm btn-light font-weight-bold text-muted px-3">
                    <i class="fas fa-industry mr-1"></i> BIG PRESS
                </a>
                <a href="{{ url('/planning/ppic/mps-small') }}" class="btn btn-sm btn-warning font-weight-bold text-dark px-3 shadow-sm">
                    <i class="fas fa-cogs mr-1"></i> SMALL PRESS
                </a>
            </div>

            {{-- Date Picker --}}
            <form action="{{ url('/planning/ppic/mps-small') }}" method="GET" class="d-flex align-items-center mr-2">
                <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm font-weight-bold border" onchange="this.form.submit()" style="width: 140px;">
            </form>

            <a href="{{ url('/planning/ppic/print-wos-bundle/' . $date) }}" target="_blank" class="btn btn-dark btn-sm font-weight-bold px-3 mr-2">
                <i class="fas fa-print mr-1"></i> PRINT ALL WOS
            </a>

            <button class="btn btn-primary btn-sm font-weight-bold px-3" data-toggle="modal" data-target="#registerPlanSmallModal">
                <i class="fas fa-plus-circle mr-1"></i> REGISTER PLAN
            </button>
        </div>
    </div>

    {{-- 📊 4 KARTU KPI METRIC ATAS (PERSIS BIG PRESS) --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="metric-card" style="border-top: 4px solid #2563eb;">
                <span class="card-label">Total Target (Qty)</span>
                <span class="card-value text-primary">{{ number_format($totalPlanQty) }}</span>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="metric-card" style="border-top: 4px solid #06b6d4;">
                <span class="card-label">Workload (Hours)</span>
                <span class="card-value" style="color: #0284c7;">
                    S1: {{ number_format($totalWorkingHoursS1, 1) }}h <span style="color:#cbd5e1; font-size:16px;">|</span> S2: {{ number_format($totalWorkingHoursS2, 1) }}h
                </span>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="metric-card" style="border-top: 4px solid #d97706;">
                <span class="card-label">Switch Loss (Dandory)</span>
                <span class="card-value text-warning">{{ number_format($totalDandory) }}m</span>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="metric-card metric-card-scope">
                <span class="card-label" style="color:#94a3b8;">Scope Data</span>
                <span class="card-value" style="font-size: 20px;">FULL DAY (S1+S2)</span>
            </div>
        </div>
    </div>

    {{-- 📋 DAFTAR JADWAL PER LINE MESIN SMALL PRESS --}}
    @forelse($groupedPlans as $lineCode => $plans)
    <div class="line-block">
        <div class="line-header">
            <h6 class="line-header-title">
                <i class="fas fa-industry text-primary"></i> M/C LINE : <b>{{ $lineCode }}</b>
            </h6>
            <div>
                <a href="{{ url('/planning/ppic/print-wos/' . $date . '/ALL/' . $lineCode) }}" target="_blank" class="btn btn-outline-primary btn-sm font-weight-bold px-3 mr-1">
                    <i class="fas fa-file-alt mr-1"></i> WOS
                </a>
                <a href="{{ url('/planning/ppic/print-serah-terima/' . $date . '/ALL/' . $lineCode) }}" target="_blank" class="btn btn-outline-success btn-sm font-weight-bold px-3">
                    <i class="fas fa-pallet mr-1"></i> MATERIAL (BPRM)
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-wos">
                <thead>
                    <tr>
                        <th width="4%" class="text-center">#</th>
                        <th width="20%">Identification</th>
                        <th width="6%" class="text-center">MP</th>
                        <th width="6%" class="text-center">Proc</th>
                        <th width="10%" class="text-right">Target</th>
                        <th width="10%" class="text-right">Actual</th>
                        <th width="10%" class="text-center">Balance</th>
                        <th width="8%" class="text-center">Start</th>
                        <th width="8%" class="text-center">Finish</th>
                        <th width="8%" class="text-center">Dandory</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $plansS1 = $plans->where('display_shift', 'S1'); 
                        $plansS2 = $plans->where('display_shift', 'S2'); 
                    @endphp

                    {{-- SHIFT 1 --}}
                    @if($plansS1->count() > 0)
                        <tr class="shift-header-row">
                            <td colspan="11">
                                <i class="fas fa-sun text-warning mr-1"></i> SHIFT 1 (DAY OPS)
                            </td>
                        </tr>
                        @foreach($plansS1 as $idx => $p)
                        <tr>
                            <td class="text-center font-weight-bold text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <span class="part-title">{{ $p->part_no }}</span>
                                <span class="customer-sub">{{ $p->customer_code ?? 'CUSTOMER' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light border font-weight-bold">{{ $p->manpower ?? 2 }}P</span>
                            </td>
                            <td class="text-center font-weight-bold">{{ $p->process_qty ?? 1 }}</td>
                            <td class="text-right font-weight-bold font-family-jetbrains">{{ number_format($p->total_target) }}</td>
                            <td class="text-right font-weight-bold font-family-jetbrains text-primary">{{ number_format($p->total_actual) }}</td>
                            <td class="text-center">
                                @if($p->balance <= 0)
                                    <span class="pill-badge pill-green"><i class="fas fa-check-circle"></i> {{ abs($p->balance) }}</span>
                                @else
                                    <span class="pill-badge pill-red"><i class="fas fa-exclamation-triangle"></i> {{ $p->balance }}</span>
                                @endif
                            </td>
                            <td class="text-center font-family-jetbrains small font-weight-bold text-warning">{{ $p->start_time ?? '-' }}</td>
                            <td class="text-center font-family-jetbrains small font-weight-bold">{{ $p->ahir_time ?? '-' }}</td>
                            <td class="text-center font-family-jetbrains small text-muted">{{ $p->dandory_time ?? 15 }}m</td>
                            <td class="text-center">
                                <button class="btn btn-outline-secondary btn-sm px-2" data-toggle="modal" data-target="#editModal{{ $p->id }}" title="Revisi Plan">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @endif

                    {{-- SHIFT 2 --}}
                    @if($plansS2->count() > 0)
                        <tr class="shift-header-row">
                            <td colspan="11">
                                <i class="fas fa-moon text-primary mr-1"></i> SHIFT 2 (NIGHT OPS)
                            </td>
                        </tr>
                        @foreach($plansS2 as $idx => $p)
                        <tr>
                            <td class="text-center font-weight-bold text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <span class="part-title">{{ $p->part_no }}</span>
                                <span class="customer-sub">{{ $p->customer_code ?? 'CUSTOMER' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light border font-weight-bold">{{ $p->manpower ?? 2 }}P</span>
                            </td>
                            <td class="text-center font-weight-bold">{{ $p->process_qty ?? 1 }}</td>
                            <td class="text-right font-weight-bold font-family-jetbrains">{{ number_format($p->total_target) }}</td>
                            <td class="text-right font-weight-bold font-family-jetbrains text-primary">{{ number_format($p->total_actual) }}</td>
                            <td class="text-center">
                                @if($p->balance <= 0)
                                    <span class="pill-badge pill-green"><i class="fas fa-check-circle"></i> {{ abs($p->balance) }}</span>
                                @else
                                    <span class="pill-badge pill-red"><i class="fas fa-exclamation-triangle"></i> {{ $p->balance }}</span>
                                @endif
                            </td>
                            <td class="text-center font-family-jetbrains small font-weight-bold text-warning">{{ $p->start_time ?? '-' }}</td>
                            <td class="text-center font-family-jetbrains small font-weight-bold">{{ $p->ahir_time ?? '-' }}</td>
                            <td class="text-center font-family-jetbrains small text-muted">{{ $p->dandory_time ?? 15 }}m</td>
                            <td class="text-center">
                                <button class="btn btn-outline-secondary btn-sm px-2" data-toggle="modal" data-target="#editModal{{ $p->id }}" title="Revisi Plan">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Footer Ringkasan Beban Line --}}
        @php
            $lineTargetTotal = $plans->sum('total_target');
        @endphp
        <div class="line-footer-summary">
            <span>Daily Summary Total : <b>{{ number_format($lineTargetTotal) }}</b></span>
            <span>Est. Operations : <b>Active Queue</b></span>
        </div>
    </div>

    {{-- MODAL REVISI / EDIT PLAN --}}
    @foreach($plans as $p)
    <div class="modal fade" id="editModal{{ $p->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white">
                    <h6 class="modal-title font-weight-bold">Revisi Jadwal [{{ $p->part_no }}]</h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('ppic.wos.update', $p->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold">Target Shift 1 (Reguler + OT)</label>
                            <input type="number" name="s1_plan_reg" class="form-control" value="{{ $p->s1_plan_reg }}">
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold">Target Shift 2 (Reguler + OT)</label>
                            <input type="number" name="s2_plan_reg" class="form-control" value="{{ $p->s2_plan_reg }}">
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Kapasitas Mesin / Jam (PCS)</label>
                            <input type="number" name="cap_per_hour" class="form-control" value="{{ $p->cap_per_hour }}">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    @empty
    <div class="card border p-5 text-center bg-white shadow-sm" style="border-radius: 12px;">
        <i class="fas fa-calendar-times text-muted fa-3x mb-3"></i>
        <h6 class="font-weight-bold text-dark">Belum Ada Jadwal Small Press</h6>
        <p class="text-muted small">Klik tombol <b>Register Plan</b> di pojok kanan atas untuk menambahkan antrean Small Press pada tanggal {{ date('d F Y', strtotime($date)) }}.</p>
    </div>
    @endforelse

</div>

{{-- 🛡️ MODAL REGISTER PLAN (KHUSUS SMALL PRESS) --}}
<div class="modal fade" id="registerPlanSmallModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title font-weight-bold">REGISTER PRODUCTION PLAN // SMALL PRESS</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('ppic.mps.store') }}" method="POST">
                @csrf
                <input type="hidden" name="plan_date" value="{{ $date }}">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Line Mesin (Small Press Only)</label>
                            <select name="line_code" class="form-control" required>
                                @foreach($availableLines as $l)
                                    <option value="{{ $l->kode_Line }}">{{ $l->kode_Line }} - {{ $l->nama_Line }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Customer Entity</label>
                            <select name="customer_code" class="form-control" required>
                                @foreach($availableCustomers as $c)
                                    <option value="{{ $c->code }}">{{ $c->name }} ({{ $c->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Part Number Utama</label>
                            <input type="text" name="part_no" class="form-control text-uppercase" placeholder="Contoh: 61635-BZ050" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Separating Part (Opsional)</label>
                            <input type="text" name="part_no_separator" class="form-control text-uppercase" placeholder="Contoh: 61636-BZ050">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Target Shift 1 (Reguler + OT)</label>
                            <input type="number" name="s1_plan_reg" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Target Shift 2 (Reguler + OT)</label>
                            <input type="number" name="s2_plan_reg" class="form-control" value="0" min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold">Manpower</label>
                            <input type="number" name="manpower" class="form-control" value="2">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold">Process Qty</label>
                            <input type="number" name="process_qty" class="form-control" value="1">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold">Cap / Hour</label>
                            <input type="number" name="cap_per_hour" class="form-control" value="300">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold">Dandory (Mnt)</label>
                            <input type="number" name="dandory_time" class="form-control" value="15">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Deploy Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection