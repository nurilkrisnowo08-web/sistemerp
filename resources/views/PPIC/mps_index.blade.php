@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --c-blue: #3b82f6; --c-green: #10b981; --c-yellow: #f59e0b; 
        --c-red: #ef4444; --c-dark: #0f172a; --bg-main: #f1f5f9;
        --border-color: #e2e8f0;
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-main); color: #1e293b; }
    
    .sys-title { font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 32px; color: var(--c-dark); letter-spacing: -1px; text-transform: uppercase; }
    .sys-title span { color: var(--c-blue); }
    .sys-subtitle { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px; }

    /* Top Cards */
    .stat-card { background: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border: 1px solid var(--border-color); border-bottom: 5px solid; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; height: 100%; transition: 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    .stat-card h5 { font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; margin-bottom: 10px; letter-spacing: 1px; }
    .stat-card h2 { font-family: 'Orbitron'; font-size: 38px; font-weight: 900; margin: 0; }
    
    .card-target { border-bottom-color: var(--c-blue); } .card-target h2 { color: var(--c-blue); }
    .card-actual { border-bottom-color: var(--c-green); } .card-actual h2 { color: var(--c-green); }
    .card-dandory { border-bottom-color: var(--c-yellow); } .card-dandory h2 { color: var(--c-yellow); }
    .card-scope { border-bottom-color: var(--c-dark); background: var(--c-dark); border: none; }
    .card-scope h5 { color: #94a3b8; } .card-scope h2 { color: #fff; font-size: 26px; margin-top: 10px; }

    /* Line Group Box */
    .line-box { background: #fff; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); border: 1px solid var(--border-color); margin-bottom: 30px; overflow: hidden; }
    .line-header { background: #f8fafc; padding: 20px 25px; border-bottom: 2px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
    .line-header h3 { font-family: 'Orbitron'; font-weight: 900; font-size: 20px; color: var(--c-dark); margin: 0; letter-spacing: 1px; }
    
    /* Table Styling */
    .mps-table { width: 100%; font-size: 11px; border-collapse: collapse; }
    .mps-table th { background: #f1f5f9; color: #475569; font-weight: 800; text-transform: uppercase; padding: 15px 12px; text-align: center; border-bottom: 1px solid var(--border-color); border-right: 1px solid #e2e8f0; }
    .mps-table td { padding: 15px 12px; font-weight: 600; text-align: center; vertical-align: middle; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; }
    .mps-table .text-left { text-align: left; }
    
    .shift-row { background: #f8fafc; text-align: left !important; font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 10px 15px !important; color: #475569; border-bottom: 2px solid var(--border-color); }
    .shift-row span { color: var(--c-blue); }
    .shift-row.s2 span { color: var(--c-dark); }
    
    .t-mono { font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 12px; }
    .rev-badge { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; padding: 2px 8px; border-radius: 6px; font-size: 9px; font-weight: 900; margin-left: 8px; }

    .btn-cyber { border-radius: 12px; font-weight: 800; padding: 10px 20px; font-size: 12px; letter-spacing: 0.5px; transition: 0.3s; }
    .btn-cyber:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    
    .input-date-custom { background: #f8fafc; border: 2px solid #cbd5e1; font-weight: 800; font-family: 'JetBrains Mono'; color: var(--c-dark); padding: 8px 15px; border-radius: 12px; outline: none; transition: 0.3s; }
    .input-date-custom:focus { border-color: var(--c-blue); background: #fff; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    
    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <div class="sys-title">WOS_TERMINAL <span>Smart MRP</span></div>
            <div class="sys-subtitle"><i class="far fa-calendar-alt mr-1"></i> Operational Date: {{ date('d F Y', strtotime($date)) }}</div>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0 align-items-center">
            <form action="" method="GET" class="mr-3">
                <input type="date" name="date" class="input-date-custom" value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <a href="{{ route('ppic.printWosBundle', ['date' => $date]) }}" target="_blank" class="btn btn-dark btn-cyber shadow-sm mr-2">
                <i class="fas fa-print mr-2"></i> PRINT ALL WOS
            </a>
            <button class="btn btn-primary btn-cyber shadow-sm" data-toggle="modal" data-target="#modalRegister">
                <i class="fas fa-plus-circle mr-2"></i> REGISTER PLAN
            </button>
        </div>
    </div>

    {{-- 4 KARTU INDIKATOR (TOP CARDS) --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="stat-card card-target">
                <h5>Total Target (Qty)</h5>
                <h2>{{ number_format($totalPlanQty) }}</h2>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="stat-card card-actual">
                <h5>Total Actual (Qty)</h5>
                <h2>{{ number_format($groupedPlans->flatten()->sum('total_actual')) }}</h2>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="stat-card card-dandory">
                <h5>Switch Loss (Dandory)</h5>
                <h2>{{ number_format($totalDandory) }}<span style="font-size: 20px;">m</span></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card card-scope">
                <h5>Scope Data</h5>
                <h2>FULL DAY (S1+S2)</h2>
            </div>
        </div>
    </div>

    {{-- DAFTAR MPS PER MESIN --}}
    @forelse($groupedPlans as $lineCode => $plans)
        <div class="line-box animate__animated animate__fadeInUp">
            <div class="line-header">
                <h3><i class="fas fa-industry text-primary mr-2"></i> M/C LINE : {{ strtoupper($line_code ?? $lineCode) }}</h3>
                <div>
                    <a href="{{ route('ppic.printWos', ['date' => $date, 'shift' => 'ALL', 'line_code' => $lineCode]) }}" target="_blank" class="btn btn-outline-primary btn-sm btn-cyber bg-white">
                        <i class="fas fa-print mr-1"></i> WOS
                    </a>
                    <a href="{{ route('ppic.printSerahTerima', ['date' => $date, 'shift' => 'ALL', 'line_code' => $lineCode]) }}" target="_blank" class="btn btn-success btn-sm btn-cyber ml-2">
                        <i class="fas fa-box-open mr-1"></i> MATERIAL
                    </a>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="mps-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 3%;">#</th>
                            <th rowspan="2" class="text-left" style="width: 25%;">IDENTIFICATION</th>
                            <th rowspan="2" style="width: 5%;">MP</th>
                            <th rowspan="2" style="width: 5%;">PROC</th>
                            <th rowspan="2" style="width: 6%;">CAP/HOUR</th>
                            <th rowspan="2" style="width: 8%;">TARGET</th>
                            <th colspan="2">PLAN WAKTU</th>
                            <th rowspan="2" style="width: 8%;">DANDORY</th>
                            <th rowspan="2" style="width: 8%;">AKSI</th>
                        </tr>
                        <tr>
                            <th style="width: 8%;">START</th>
                            <th style="width: 8%;">FINISH</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- SHIFT 1 --}}
                        @php $s1Plans = $plans->where('display_shift', 'S1'); $no = 1; @endphp
                        @if($s1Plans->count() > 0)
                            <tr>
                                <td colspan="10" class="shift-row"><i class="fas fa-sun text-warning mr-1"></i> SHIFT 1 (DAY OPS)</td>
                            </tr>
                            @foreach($s1Plans as $p)
                                @php
                                    // Deteksi Status Revisi
                                    $revLabel = '';
                                    if (isset($p->keterangan) && preg_match('/\(R(\d+)\)/', $p->keterangan, $matches)) {
                                        $revLabel = "<span class='rev-badge'>REV-{$matches[1]}</span>";
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td class="text-left">
                                        <div class="text-primary font-weight-bold" style="font-size: 13px;">
                                            {{ $p->part_no }} {!! $revLabel !!}
                                        </div>
                                        <div style="font-size: 10px; color: #64748b; margin-top: 2px;">{{ $p->customer_code }}</div>
                                    </td>
                                    <td>{{ $p->manpower ?? 1 }}P</td>
                                    <td>{{ $p->process_qty ?? 1 }}</td>
                                    <td class="t-mono text-muted">{{ $p->cap_per_hour }}</td>
                                    <td class="t-mono" style="color: var(--c-blue); font-size: 14px;">{{ number_format($p->total_target) }}</td>
                                    
                                    <td class="t-mono bg-light" style="color: #475569;">{{ $p->start_time }}</td>
                                    <td class="t-mono bg-light" style="color: #475569;">{{ $p->ahir_time }}</td>
                                    
                                    <td class="t-mono text-muted">{{ $p->dandory_time ?? 15 }}m</td>
                                    <td>
                                        <button class="btn btn-warning btn-sm shadow-sm" style="border-radius: 8px;" data-toggle="modal" data-target="#modalRevise{{ $p->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                        {{-- SHIFT 2 --}}
                        @php $s2Plans = $plans->where('display_shift', 'S2'); $no = 1; @endphp
                        @if($s2Plans->count() > 0)
                            <tr>
                                <td colspan="10" class="shift-row s2"><i class="fas fa-moon text-secondary mr-1"></i> SHIFT 2 (NIGHT OPS)</td>
                            </tr>
                            @foreach($s2Plans as $p)
                                @php
                                    $revLabel = '';
                                    if (isset($p->keterangan) && preg_match('/\(R(\d+)\)/', $p->keterangan, $matches)) {
                                        $revLabel = "<span class='rev-badge'>REV-{$matches[1]}</span>";
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td class="text-left">
                                        <div class="text-primary font-weight-bold" style="font-size: 13px;">
                                            {{ $p->part_no }} {!! $revLabel !!}
                                        </div>
                                        <div style="font-size: 10px; color: #64748b; margin-top: 2px;">{{ $p->customer_code }}</div>
                                    </td>
                                    <td>{{ $p->manpower ?? 1 }}P</td>
                                    <td>{{ $p->process_qty ?? 1 }}</td>
                                    <td class="t-mono text-muted">{{ $p->cap_per_hour }}</td>
                                    <td class="t-mono" style="color: var(--c-blue); font-size: 14px;">{{ number_format($p->total_target) }}</td>
                                    
                                    <td class="t-mono bg-light" style="color: #475569;">{{ $p->start_time }}</td>
                                    <td class="t-mono bg-light" style="color: #475569;">{{ $p->ahir_time }}</td>
                                    
                                    <td class="t-mono text-muted">{{ $p->dandory_time ?? 15 }}m</td>
                                    <td>
                                        <button class="btn btn-warning btn-sm shadow-sm" style="border-radius: 8px;" data-toggle="modal" data-target="#modalRevise{{ $p->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        
                        {{-- FOOTER SUMMARY PER MESIN --}}
                        <tr style="background: #0f172a; color: #fff;">
                            <td colspan="10" class="text-center font-weight-bold" style="padding: 12px; font-size: 11px; letter-spacing: 1px;">
                                DAILY SUMMARY TARGET LINE {{ $lineCode }} = <span style="color: #38bdf8;">{{ number_format($plans->sum('total_target')) }} PCS</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- MODAL REVISI PER PLAN --}}
        @foreach($plans->unique('id') as $p)
        <div class="modal fade" id="modalRevise{{ $p->id }}" tabindex="-1" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                    <div class="modal-header bg-warning p-4 border-0">
                        <h5 class="modal-title font-weight-black text-dark"><i class="fas fa-tools mr-2"></i> Revisi WOS & Sinkronisasi Material</h5>
                        <button type="button" class="close text-dark" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <form action="{{ route('mps.updateWos', $p->id) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-body p-4 p-md-5">
                            <div class="alert alert-warning border-0" style="background: #fef3c7; color: #b45309; border-radius: 12px; font-size: 11px; font-weight: 700;">
                                <i class="fas fa-exclamation-circle mr-1"></i> Ubah target menjadi 0 jika ingin membatalkan WOS. Selisih material akan dikalkulasi otomatis ke gudang.
                            </div>
                            
                            <div class="border rounded-lg p-4 mb-4">
                                <h6 class="text-primary font-weight-bold mb-3">{{ $p->part_no }} — {{ $p->line_code }}</h6>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="small font-weight-bold text-dark">S1 Reguler</label>
                                        <input type="number" name="s1_plan_reg" class="form-control text-center font-weight-bold" style="border-radius: 10px;" value="{{ $p->s1_plan_reg }}">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="small font-weight-bold text-warning">S1 OT</label>
                                        <input type="number" name="s1_plan_ot" class="form-control text-center font-weight-bold" style="border-radius: 10px;" value="{{ $p->s1_plan_ot }}">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="small font-weight-bold text-dark">S2 Reguler</label>
                                        <input type="number" name="s2_plan_reg" class="form-control text-center font-weight-bold" style="border-radius: 10px;" value="{{ $p->s2_plan_reg }}">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="small font-weight-bold text-warning">S2 OT</label>
                                        <input type="number" name="s2_plan_ot" class="form-control text-center font-weight-bold" style="border-radius: 10px;" value="{{ $p->s2_plan_ot }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="small font-weight-bold text-dark">Cap/Hour</label>
                                    <input type="number" name="cap_per_hour" class="form-control text-center" style="border-radius: 10px;" value="{{ $p->cap_per_hour }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="small font-weight-bold text-dark">Dandory (m)</label>
                                    <input type="number" name="dandory_time" class="form-control text-center" style="border-radius: 10px;" value="{{ $p->dandory_time }}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 bg-light p-4 justify-content-center">
                            <button type="button" class="btn btn-outline-secondary btn-cyber bg-white" data-dismiss="modal">BATAL</button>
                            <button type="submit" class="btn btn-warning btn-cyber"><i class="fas fa-save mr-2"></i> UPDATE & SYNC MATERIAL</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

    @empty
        <div class="text-center py-5 bg-white rounded-2xl border border-dashed text-muted mt-4 shadow-sm">
            <i class="fas fa-calendar-times fa-3x mb-3" style="color: #cbd5e1;"></i>
            <h5 class="font-weight-bold text-dark">MPS Kosong</h5>
            <p class="small">Tidak ada jadwal produksi Big Press yang terdaftar untuk tanggal ini.</p>
        </div>
    @endforelse

</div>

{{-- MODAL REGISTER PLAN (Menggunakan struktur yang sama dengan sebelumnya) --}}
@include('PPIC.modals.register_mps_modal') 

@endsection