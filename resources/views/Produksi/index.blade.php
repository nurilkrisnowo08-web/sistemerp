@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">

<style>
    /* 📱 SAMSUNG ONE UI THEME ENGINE */
    :root {
        --oneui-primary: #0381fe;
        --oneui-primary-soft: rgba(3, 129, 254, 0.08);
        --oneui-bg: #f4f6fa;
        --oneui-card: #ffffff;
        --oneui-border: #e2e8f0;
        --oneui-dark: #0f172a;
        --oneui-text-sub: #64748b;
        --oneui-success: #00c073;
        --oneui-warning: #ff8a00;
        --oneui-danger: #ff3b30;
        --oneui-spring: cubic-bezier(0.16, 1, 0.3, 1);
    }

    body {
        background-color: var(--oneui-bg);
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #1e293b;
    }

    .terminal-container {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* 🎛️ SEGMENTED ONE UI SWITCHER */
    .oneui-segmented-wrap {
        background: #e2e8f0;
        padding: 4px;
        border-radius: 9999px;
        display: inline-flex;
        gap: 4px;
    }
    .oneui-segmented-btn {
        padding: 7px 20px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 800;
        color: var(--oneui-text-sub);
        text-decoration: none !important;
        transition: all 0.25s var(--oneui-spring);
    }
    .oneui-segmented-btn:active {
        transform: scale(0.96);
    }
    .oneui-segmented-btn.active {
        background: #ffffff;
        color: #0f172a;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    }

    /* 🔘 SPRING BUTTONS */
    .btn-spring {
        border-radius: 9999px;
        font-weight: 800;
        font-size: 11.5px;
        padding: 8px 18px;
        border: none;
        transition: transform 0.2s var(--oneui-spring), box-shadow 0.2s ease, background 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-spring:active {
        transform: scale(0.95) !important;
    }

    /* 📋 LINE CONTAINER CARDS */
    .terminal-line-card {
        background: var(--oneui-card);
        border: 1px solid var(--oneui-border);
        border-radius: 22px;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px -4px rgba(0, 0, 0, 0.04);
    }
    .terminal-line-header {
        padding: 16px 24px;
        background: #ffffff;
        border-bottom: 1px solid var(--oneui-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-terminal {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .table-terminal thead th {
        background: #f8fafc;
        color: var(--oneui-text-sub);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 13px 18px;
        border-top: none;
        border-bottom: 1px solid var(--oneui-border);
    }
    .table-terminal tbody td {
        padding: 14px 18px;
        vertical-align: middle;
        font-size: 12.5px;
        border-top: 1px solid #f1f5f9;
    }
    .table-terminal tbody tr:hover {
        background-color: #f8fafc;
    }

    /* 🏷️ SHIFT SEPARATION HEADERS */
    .shift-header-divider td {
        background: #f8fafc;
        font-weight: 800;
        font-size: 10.5px;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 9px 18px;
        border-top: 1.5px solid var(--oneui-border);
    }

    .batch-tag {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 800;
        font-size: 12px;
        color: var(--oneui-primary);
        background: var(--oneui-primary-soft);
        padding: 4px 10px;
        border-radius: 8px;
        display: inline-block;
    }

    .badge-pill-status {
        font-family: 'JetBrains Mono', monospace;
        font-size: 9.5px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .badge-pill-proses { background: #e0f2fe; color: #0369a1; }
    .badge-pill-problem { background: #fee2e2; color: #b91c1c; }

    /* 🛡️ MODAL ONE UI */
    .modal-oneui .modal-content {
        border-radius: 24px;
        border: none;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    .modal-oneui .modal-header {
        background: #ffffff;
        padding: 20px 26px;
        border-bottom: 1px solid var(--oneui-border);
    }
    .modal-oneui label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--oneui-text-sub);
        letter-spacing: 0.4px;
        margin-bottom: 6px;
    }
    .modal-oneui .form-control {
        border-radius: 12px;
        border: 1.5px solid var(--oneui-border);
        background: #f8fafc;
        font-size: 12.5px;
        padding: 10px 14px;
        height: auto;
        color: #0f172a;
        transition: all 0.2s ease;
    }
    .modal-oneui .form-control:focus {
        background: #ffffff;
        border-color: var(--oneui-primary);
        box-shadow: 0 0 0 4px rgba(3, 129, 254, 0.12);
    }
</style>

<div class="terminal-container mb-5">

    {{-- HEADER ATAS & WORKSTATION SWITCHER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge {{ $area === 'small' ? 'badge-warning text-dark' : 'badge-primary' }} font-weight-bold px-3 py-1 mr-2" style="border-radius: 9999px; font-size: 9px; letter-spacing: 0.5px;">
                    {{ $area === 'small' ? 'SMALL PRESS WORKSTATION' : 'BIG PRESS WORKSTATION' }}
                </span>
                <h3 class="m-0 font-weight-bold text-dark" style="font-size: 22px;">Terminal <span class="text-primary">Line Control</span></h3>
            </div>
            <div class="text-muted small font-weight-bold">
                <i class="far fa-calendar-alt text-primary mr-1"></i> Tanggal Operasional: <b>{{ date('d F Y', strtotime($date ?? date('Y-m-d'))) }}</b>
            </div>
        </div>

        <div class="d-flex align-items-center flex-wrap gap-2 mt-3 mt-md-0">
            {{-- Room Switcher: Big Press vs Small Press --}}
            <div class="oneui-segmented-wrap mr-3">
                <a href="{{ route('produksi.index', ['area' => 'big', 'shift' => $selectedShift, 'customer' => $customerFilter, 'date' => $date]) }}" class="oneui-segmented-btn {{ $area === 'big' ? 'active' : '' }}">
                    🏭 Big Press
                </a>
                <a href="{{ route('produksi.index', ['area' => 'small', 'shift' => $selectedShift, 'customer' => $customerFilter, 'date' => $date]) }}" class="oneui-segmented-btn {{ $area === 'small' ? 'active' : '' }}">
                    ⚙️ Small Press
                </a>
            </div>

            {{-- Filter Shift --}}
            <div class="btn-group mr-2 bg-white border p-1" style="border-radius: 9999px;">
                <a href="{{ route('produksi.index', ['area' => $area, 'shift' => 'ALL', 'customer' => $customerFilter, 'date' => $date]) }}" class="btn btn-sm btn-spring py-1 px-3 {{ $selectedShift === 'ALL' ? 'btn-dark' : 'text-muted' }}">ALL</a>
                <a href="{{ route('produksi.index', ['area' => $area, 'shift' => 'S1', 'customer' => $customerFilter, 'date' => $date]) }}" class="btn btn-sm btn-spring py-1 px-3 {{ $selectedShift === 'S1' ? 'btn-warning text-dark' : 'text-muted' }}">SHIFT 1</a>
                <a href="{{ route('produksi.index', ['area' => $area, 'shift' => 'S2', 'customer' => $customerFilter, 'date' => $date]) }}" class="btn btn-sm btn-spring py-1 px-3 {{ $selectedShift === 'S2' ? 'btn-primary' : 'text-muted' }}">SHIFT 2</a>
            </div>

            {{-- Date Selector --}}
            <form action="{{ route('produksi.index') }}" method="GET" class="d-flex align-items-center mr-2">
                <input type="hidden" name="area" value="{{ $area }}">
                <input type="hidden" name="shift" value="{{ $selectedShift }}">
                <input type="hidden" name="customer" value="{{ $customerFilter }}">
                <input type="date" name="date" value="{{ $date ?? date('Y-m-d') }}" class="form-control font-weight-bold border" onchange="this.form.submit()" style="border-radius: 9999px; font-size: 12px; padding: 6px 14px; height: auto;">
            </form>

            {{-- Tombol Riwayat & Mulai Batch --}}
            <a href="{{ route('produksi.history') }}" class="btn btn-light btn-sm btn-spring border px-3 mr-1 shadow-sm font-weight-bold">
                <i class="fas fa-history text-muted"></i> Archive
            </a>
            <button class="btn btn-primary btn-sm btn-spring px-4 shadow-sm font-weight-bold" data-toggle="modal" data-target="#startBatchModal">
                <i class="fas fa-bolt"></i> Start New Batch
            </button>
        </div>
    </div>

    {{-- FILTER CUSTOMER ENTITY TABS --}}
    <div class="d-flex align-items-center flex-wrap gap-2 mb-4">
        <a href="{{ route('produksi.index', ['area' => $area, 'shift' => $selectedShift, 'customer' => 'ALL', 'date' => $date]) }}" 
           class="btn btn-sm btn-spring px-3 border shadow-sm {{ (!$customerFilter || $customerFilter === 'ALL') ? 'btn-primary text-white' : 'btn-white text-muted' }}">
            ALL OPERATIONS
        </a>
        @foreach($customers as $c)
        <a href="{{ route('produksi.index', ['area' => $area, 'shift' => $selectedShift, 'customer' => $c->code, 'date' => $date]) }}" 
           class="btn btn-sm btn-spring px-3 border shadow-sm {{ ($customerFilter === $c->code) ? 'btn-primary text-white' : 'btn-white text-muted' }}">
            {{ $c->code }}
        </a>
        @endforeach
    </div>

    {{-- 📋 DAFTAR BATCH PER LINE MESIN --}}
    @forelse($groupedLines as $lineCode => $batchesInLine)
    <div class="terminal-line-card">
        <div class="terminal-line-header">
            <h6 class="font-weight-bold text-dark m-0 d-flex align-items-center">
                <i class="fas fa-industry text-primary mr-2"></i> M/C LINE: <b>{{ $lineCode }}</b>
            </h6>
            <span class="badge badge-light border px-3 py-1 font-weight-bold" style="border-radius: 9999px; font-size: 11px;">
                Antrean Aktif: {{ $batchesInLine->count() }} Batch
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-terminal">
                <thead>
                    <tr>
                        <th width="15%">BATCH ID</th>
                        <th width="25%">PART IDENTIFICATION</th>
                        <th width="16%">MATERIAL SPEC / COIL</th>
                        <th width="12%" class="text-right">JATAH (LBR/PCS)</th>
                        <th width="12%" class="text-right">HASIL (OK/NG)</th>
                        <th width="8%" class="text-center">STATUS</th>
                        <th width="12%" class="text-center">COMMAND</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $batchesS1 = $batchesInLine->filter(fn($b) => $b->shift_group === 'S1');
                        $batchesS2 = $batchesInLine->filter(fn($b) => $b->shift_group === 'S2');
                    @endphp

                    {{-- SHIFT 1 --}}
                    @if($batchesS1->count() > 0 && ($selectedShift === 'ALL' || $selectedShift === 'S1'))
                        <tr class="shift-header-divider">
                            <td colspan="7">
                                <i class="fas fa-sun text-warning mr-1"></i> SHIFT 1 (DAY OPS)
                            </td>
                        </tr>
                        @foreach($batchesS1 as $b)
                        <tr>
                            <td>
                                <span class="batch-tag">{{ $b->no_produksi }}</span>
                            </td>
                            <td>
                                <b class="text-dark font-family-jetbrains d-block" style="font-size: 13px;">{{ $b->display_part }}</b>
                                <small class="text-muted font-weight-bold">
                                    {{ $b->customer ?? ($b->plan_customer ?? 'CUSTOMER') }} // Cavity: {{ $b->cavity }}
                                </small>
                            </td>
                            <td>
                                <div class="font-family-jetbrains font-weight-bold text-dark small">🏷️ {{ $b->coil_id ?? '-' }}</div>
                                <small class="text-muted font-family-jetbrains">{{ $b->spec ?? '-' }} {{ $b->size ? '| '.$b->size : '' }}</small>
                            </td>
                            <td class="text-right font-family-jetbrains font-weight-bold">
                                <span class="text-dark">{{ number_format($b->qty_ambil_pcs) }} <small class="text-muted">lbr</small></span><br>
                                <small class="text-primary font-weight-bold">({{ number_format($b->qty_ambil_pcs * $b->cavity) }} pcs)</small>
                            </td>
                            <td class="text-right font-family-jetbrains font-weight-bold">
                                <span class="text-success">{{ number_format($b->qty_hasil_ok) }} OK</span><br>
                                <span class="text-danger small">{{ number_format($b->qty_hasil_ng) }} NG</span>
                            </td>
                            <td class="text-center">
                                @if($b->status === 'PROBLEM')
                                    <span class="badge-pill-status badge-pill-problem"><i class="fas fa-exclamation-triangle"></i> PROBLEM</span>
                                @else
                                    <span class="badge-pill-status badge-pill-proses"><i class="fas fa-spinner fa-spin"></i> PROSES</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    {{-- Lapor Problem --}}
                                    <button class="btn btn-outline-danger btn-sm rounded-circle p-0 mr-1 shadow-sm" style="width: 32px; height: 32px;" title="Lapor Problem Dies/Mesin" data-toggle="modal" data-target="#problemModal{{ $b->batch_id }}">
                                        <i class="fas fa-exclamation" style="font-size: 11px;"></i>
                                    </button>
                                    {{-- Input Result --}}
                                    <button class="btn btn-success btn-sm btn-spring px-3 font-weight-bold shadow-sm" data-toggle="modal" data-target="#inputResultModal{{ $b->batch_id }}">
                                        INPUT RESULT
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endif

                    {{-- SHIFT 2 --}}
                    @if($batchesS2->count() > 0 && ($selectedShift === 'ALL' || $selectedShift === 'S2'))
                        <tr class="shift-header-divider">
                            <td colspan="7">
                                <i class="fas fa-moon text-primary mr-1"></i> SHIFT 2 (NIGHT OPS)
                            </td>
                        </tr>
                        @foreach($batchesS2 as $b)
                        <tr>
                            <td>
                                <span class="batch-tag">{{ $b->no_produksi }}</span>
                            </td>
                            <td>
                                <b class="text-dark font-family-jetbrains d-block" style="font-size: 13px;">{{ $b->display_part }}</b>
                                <small class="text-muted font-weight-bold">
                                    {{ $b->customer ?? ($b->plan_customer ?? 'CUSTOMER') }} // Cavity: {{ $b->cavity }}
                                </small>
                            </td>
                            <td>
                                <div class="font-family-jetbrains font-weight-bold text-dark small">🏷️ {{ $b->coil_id ?? '-' }}</div>
                                <small class="text-muted font-family-jetbrains">{{ $b->spec ?? '-' }} {{ $b->size ? '| '.$b->size : '' }}</small>
                            </td>
                            <td class="text-right font-family-jetbrains font-weight-bold">
                                <span class="text-dark">{{ number_format($b->qty_ambil_pcs) }} <small class="text-muted">lbr</small></span><br>
                                <small class="text-primary font-weight-bold">({{ number_format($b->qty_ambil_pcs * $b->cavity) }} pcs)</small>
                            </td>
                            <td class="text-right font-family-jetbrains font-weight-bold">
                                <span class="text-success">{{ number_format($b->qty_hasil_ok) }} OK</span><br>
                                <span class="text-danger small">{{ number_format($b->qty_hasil_ng) }} NG</span>
                            </td>
                            <td class="text-center">
                                @if($b->status === 'PROBLEM')
                                    <span class="badge-pill-status badge-pill-problem"><i class="fas fa-exclamation-triangle"></i> PROBLEM</span>
                                @else
                                    <span class="badge-pill-status badge-pill-proses"><i class="fas fa-spinner fa-spin"></i> PROSES</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <button class="btn btn-outline-danger btn-sm rounded-circle p-0 mr-1 shadow-sm" style="width: 32px; height: 32px;" title="Lapor Problem Dies/Mesin" data-toggle="modal" data-target="#problemModal{{ $b->batch_id }}">
                                        <i class="fas fa-exclamation" style="font-size: 11px;"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm btn-spring px-3 font-weight-bold shadow-sm" data-toggle="modal" data-target="#inputResultModal{{ $b->batch_id }}">
                                        INPUT RESULT
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL INPUT RESULT --}}
    @foreach($batchesInLine as $b)
    <div class="modal fade modal-oneui" id="inputResultModal{{ $b->batch_id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h6 class="modal-title font-weight-bold">Input Laporan Hasil Produksi</h6>
                        <span class="small font-weight-bold text-primary font-family-jetbrains">Batch: {{ $b->no_produksi }} // {{ $b->display_part }}</span>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('produksi.updateResult', $b->batch_id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4 bg-white">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label><i class="fas fa-check-circle text-success mr-1"></i> Qty OK Utama (Pcs)</label>
                                <input type="number" name="qty_hasil_ok" class="form-control font-weight-bold font-family-jetbrains" placeholder="Jumlah produk bagus" min="0" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label><i class="fas fa-times-circle text-danger mr-1"></i> Qty NG Utama (Pcs)</label>
                                <input type="number" name="qty_hasil_ng" class="form-control font-weight-bold font-family-jetbrains" placeholder="Total cacat (opsional)" min="0" value="0">
                            </div>
                        </div>

                        {{-- Input Pasangan jika Separating Part --}}
                        @if($b->is_separating)
                        <div class="p-3 mb-3 border rounded-lg bg-light">
                            <h6 class="font-weight-bold text-dark small mb-2"><i class="fas fa-link text-primary mr-1"></i> Hasil Part Pasangan (Separating: {{ $b->paired_part }})</h6>
                            <input type="hidden" name="paired_batch_id" value="{{ $b->paired_batch_id }}">
                            <div class="row">
                                <div class="col-md-6 form-group mb-0">
                                    <label>Qty OK Pasangan (Pcs)</label>
                                    <input type="number" name="paired_qty_ok" class="form-control font-weight-bold font-family-jetbrains" placeholder="Jumlah OK pasangan" min="0">
                                </div>
                                <div class="col-md-6 form-group mb-0">
                                    <label>Qty NG Pasangan (Pcs)</label>
                                    <input type="number" name="paired_qty_ng" class="form-control font-weight-bold font-family-jetbrains" value="0" min="0">
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label><i class="fas fa-undo text-warning mr-1"></i> Return Lembar Sheet ke Gudang RM</label>
                                <input type="number" name="qty_return_warehouse" class="form-control font-weight-bold font-family-jetbrains" value="0" min="0">
                                <small class="text-muted font-weight-bold">Isi jika ada sisa lembaran utuh yang dikembalikan ke rak.</small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Status Batch</label>
                                <select name="status" class="form-control font-weight-bold">
                                    <option value="COMPLETED">COMPLETED (Selesai & Tutup Batch)</option>
                                    <option value="PROSES">PROSES (Masih Berjalan di Line)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label>Catatan Operator / Remark</label>
                            <input type="text" name="keterangan" class="form-control" placeholder="Keterangan tambahan (opsional)">
                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-3">
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3 font-weight-bold" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm btn-spring px-4 font-weight-bold">Simpan Hasil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL LAPOR PROBLEM --}}
    <div class="modal fade modal-oneui" id="problemModal{{ $b->batch_id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title font-weight-bold text-danger"><i class="fas fa-biohazard mr-2"></i> Lapor Problem Mesin / Dies</h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('produksi.reportProblem', $b->batch_id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4 bg-white">
                        <p class="small text-muted mb-3">Laporan ini akan mengubah status batch menjadi <b>PROBLEM</b> dan memberi notifikasi ke PPIC & Maintenance Hub.</p>
                        <div class="form-group mb-0">
                            <label>Keterangan Masalah</label>
                            <textarea name="problem_note" class="form-control font-weight-bold" rows="3" placeholder="Contoh: Pin Dies Patah / Sensor Ejector Error..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-3">
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3 font-weight-bold" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-danger btn-sm btn-spring px-4 font-weight-bold">Kirim Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    @empty
    <div class="card p-5 text-center bg-white shadow-sm" style="border-radius: 22px; border: 1px solid var(--oneui-border);">
        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
        <h5 class="font-weight-bold text-dark">Tidak Ada Antrean Batch Berjalan</h5>
        <p class="text-muted small mb-0">Semua batch produksi untuk area <b>{{ strtoupper($area) }} PRESS</b> pada tanggal ini sudah selesai atau belum di-deploy dari PPIC Master Schedule.</p>
    </div>
    @endforelse

</div>

{{-- 🛡️ MODAL START NEW BATCH (MANUAL INPUT JIKA DIBUTUHKAN) --}}
<div class="modal fade modal-oneui" id="startBatchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h6 class="modal-title font-weight-bold">Start Batch Produksi Manual</h6>
                    <span class="small font-weight-bold text-muted">Ambil bahan langsung dari Gudang RM</span>
                </div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('produksi.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-white">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Pilih Mesin Stamping</label>
                            <select name="mesin_id" class="form-control font-weight-bold" required>
                                @foreach($lines as $l)
                                    <option value="{{ $l->id }}">{{ $l->kode_Line }} - {{ $l->nama_Line }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Shift Kerja</label>
                            <select name="shift" class="form-control font-weight-bold" required>
                                <option value="Pagi">Shift 1 (Pagi)</option>
                                <option value="Malam">Shift 2 (Malam)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Material Coil / Bundle Fisik</label>
                            <select name="rm_stock_id" class="form-control font-weight-bold" required>
                                @foreach($materials as $m)
                                    <option value="{{ $m->id }}">{{ $m->coil_id }} | {{ $m->spec }} (Sisa: {{ number_format($m->stock_pcs) }} lbr)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Part Number</label>
                            <input type="text" name="material_code" class="form-control font-weight-bold text-uppercase" placeholder="Contoh: 61635-BZ050" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Qty Ambil Lembar (Sheet)</label>
                            <input type="number" name="qty_ambil_pcs" class="form-control font-weight-bold font-family-jetbrains" min="1" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Cavity</label>
                            <input type="number" name="cavity" class="form-control font-weight-bold font-family-jetbrains" value="1" min="1" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm btn-spring px-4 font-weight-bold">Start Batch</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection