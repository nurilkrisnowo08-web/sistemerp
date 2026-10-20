@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">

<style>
    :root { 
        --oneui-primary: #0381fe;
        --oneui-bg: #f4f6fa; 
        --oneui-card: #ffffff;
        --oneui-border: #e2e8f0;
        --oneui-dark: #0f172a;
        --oneui-spring: cubic-bezier(0.16, 1, 0.3, 1);
    }

    body { background-color: var(--oneui-bg); font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }

    /* 🏛️ ONE UI SQUIRCLE CARD */
    .card-oneui-vault { 
        border-radius: 22px; 
        background: #ffffff; 
        border: 1px solid var(--oneui-border);
        box-shadow: 0 4px 20px -4px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    /* 🎛️ FILTER BAR */
    .oneui-filter-panel { 
        background: #ffffff;
        border: 1px solid var(--oneui-border);
        border-radius: 20px; 
        padding: 22px 26px; 
        margin-bottom: 24px; 
        box-shadow: 0 4px 20px -4px rgba(0, 0, 0, 0.03);
    }
    .oneui-input { 
        border-radius: 12px; 
        padding: 10px 14px; 
        border: 1.5px solid var(--oneui-border); 
        background: #f8fafc;
        color: #0f172a; 
        font-weight: 700; 
        font-size: 12.5px;
        transition: all 0.2s ease;
        height: auto;
    }
    .oneui-input:focus { 
        background: #ffffff;
        border-color: var(--oneui-primary);
        box-shadow: 0 0 0 3px rgba(3, 129, 254, 0.12);
        color: #0f172a;
    }

    /* 📋 TABLE STYLING */
    .table-vault thead th { 
        background: #f8fafc; 
        color: #64748b; 
        font-weight: 800; 
        text-transform: uppercase; 
        font-size: 10px; 
        letter-spacing: 1px; 
        padding: 16px 20px; 
        border-top: none;
        border-bottom: 1px solid var(--oneui-border);
    }
    .table-vault tbody td {
        padding: 16px 20px;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
        font-size: 13px;
    }

    .expand-row-btn { 
        cursor: pointer; 
        transition: background-color 0.15s ease;
    }
    .expand-row-btn:hover { 
        background-color: #f8fafc !important; 
    }

    .manifest-code { 
        font-family: 'JetBrains Mono', monospace; 
        font-weight: 800; 
        color: var(--oneui-primary); 
        font-size: 14px; 
    }

    /* 🏷️ ONE UI BADGES */
    .badge-pill-oneui {
        font-size: 10px;
        font-weight: 800;
        padding: 5px 12px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-family: 'JetBrains Mono', monospace;
    }
    .badge-completed { background: #e6f9f1; color: #00965e; }
    .badge-process { background: #fef3c7; color: #b45309; }

    /* 🔘 SPRING BUTTONS */
    .btn-spring {
        border-radius: 9999px;
        font-weight: 700;
        font-size: 11.5px;
        padding: 8px 18px;
        transition: transform 0.2s var(--oneui-spring);
    }
    .btn-spring:active {
        transform: scale(0.95);
    }

    /* 📦 DETAIL SPEC CARD */
    .item-audit-card {
        background: #ffffff;
        border: 1px solid var(--oneui-border);
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        height: 100%;
    }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">

    {{-- HEADER ATAS --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge badge-dark px-3 py-1 mr-2" style="border-radius: 9999px; font-size: 9px; letter-spacing: 0.5px;">AUDIT LOGS</span>
                <h3 class="font-weight-extrabold m-0 text-dark" style="letter-spacing: -0.5px;">PO Supplier <span style="color: var(--oneui-primary);">Archive Vault</span></h3>
            </div>
            <p class="text-muted small mb-0 font-weight-bold">Arsip historis pengadaan bahan baku dan riwayat manifest penerimaan material.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('rm.po_supplier_index') }}" class="btn btn-outline-secondary btn-spring px-4 bg-white shadow-sm font-weight-bold">
                <i class="fas fa-arrow-left mr-2"></i> Operasional Aktif
            </a>
        </div>
    </div>

    {{-- 🎛️ FILTER PANEL (CLEAN ONE UI) --}}
    <div class="oneui-filter-panel">
        <form action="{{ route('rm.po_supplier_history') }}" method="GET" class="row align-items-end">
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="small font-weight-bold text-muted text-uppercase mb-2 ml-1" style="font-size: 11px;">Customer / Supplier</label>
                <select name="customer" class="form-control oneui-input w-100">
                    <option value="ALL">-- SEMUA ENTITAS --</option>
                    @foreach($clients as $c)
                        <option value="{{ trim($c->code) }}" {{ ($selectedCustomer == trim($c->code)) ? 'selected' : '' }}>
                            {{ strtoupper($c->name) }} ({{ trim($c->code) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="small font-weight-bold text-muted text-uppercase mb-2 ml-1" style="font-size: 11px;">Tanggal Awal</label>
                <input type="date" name="start_date" class="form-control oneui-input w-100" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="small font-weight-bold text-muted text-uppercase mb-2 ml-1" style="font-size: 11px;">Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control oneui-input w-100" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-block btn-spring py-2 shadow-sm font-weight-bold" style="background: var(--oneui-primary); border: none;">
                    <i class="fas fa-search mr-2"></i> Filter Data
                </button>
            </div>
        </form>
    </div>

    {{-- 📋 TABEL HISTORIS PO --}}
    <div class="card-oneui-vault">
        <div class="table-responsive">
            <table class="table table-vault mb-0">
                <thead>
                    <tr>
                        <th class="pl-4">Manifest PO</th>
                        <th>Supplier / Customer</th>
                        <th class="text-center">Status</th>
                        <th>Riwayat Timestamp</th>
                        <th class="text-right pr-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pos as $po)
                    <tr>
                        <td class="pl-4 expand-row-btn" data-toggle="collapse" data-target="#archive-{{ $po->id }}">
                            <div class="manifest-code">{{ $po->no_po_supplier }}</div>
                            <small class="text-muted font-weight-bold" style="font-size: 10px;">
                                <i class="fas fa-layer-group text-primary mr-1"></i> Klik untuk rincian item
                            </small>
                        </td>
                        <td class="font-weight-bold text-dark expand-row-btn" data-toggle="collapse" data-target="#archive-{{ $po->id }}">
                            {{ strtoupper($po->supplier_name) }}
                        </td>
                        <td class="text-center expand-row-btn" data-toggle="collapse" data-target="#archive-{{ $po->id }}">
                            @if($po->status == 'COMPLETED')
                                <span class="badge-pill-oneui badge-completed">
                                    <i class="fas fa-check-circle"></i> COMPLETED
                                </span>
                            @else
                                <span class="badge-pill-oneui badge-process">
                                    <i class="fas fa-clock"></i> {{ $po->status }}
                                </span>
                            @endif
                        </td>
                        <td class="expand-row-btn" data-toggle="collapse" data-target="#archive-{{ $po->id }}">
                            <div class="d-flex gap-4">
                                <div>
                                    <div class="text-muted text-uppercase font-weight-bold" style="font-size: 9px;">Dibuat:</div>
                                    <div class="font-family-jetbrains font-weight-bold text-dark" style="font-size: 11px;">
                                        {{ date('d/m/Y H:i', strtotime($po->created_at)) }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-muted text-uppercase font-weight-bold" style="font-size: 9px;">Update Terakhir:</div>
                                    <div class="font-family-jetbrains font-weight-bold text-success" style="font-size: 11px;">
                                        {{ date('d/m/Y H:i', strtotime($po->updated_at)) }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="text-right pr-4">
                            <button class="btn btn-light btn-sm btn-spring border px-3 font-weight-bold" onclick="window.open('{{ route('rm.print_po', $po->id) }}', '_blank')">
                                <i class="fas fa-print mr-1 text-muted"></i> Cetak PO
                            </button>
                        </td>
                    </tr>

                    {{-- DETAIL ITEMS COLLAPSE --}}
                    <tr id="archive-{{ $po->id }}" class="collapse bg-light">
                        <td colspan="5" class="p-0">
                            <div class="p-4" style="background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-boxes text-primary mr-2"></i>
                                    <h6 class="font-weight-bold text-dark m-0" style="font-size: 13px;">Spesifikasi Material Terverifikasi</h6>
                                </div>
                                
                                <div class="row">
                                    @if(isset($po->items) && count($po->items) > 0)
                                        @foreach($po->items as $item)
                                        <div class="col-md-6 mb-3">
                                            <div class="item-audit-card">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="font-weight-extrabold text-primary mb-0 font-family-jetbrains">{{ $item->material_code }}</h6>
                                                    <span class="badge badge-pill-oneui {{ ($item->qty_received ?? 0) >= ($item->qty_order ?? 0) ? 'badge-completed' : 'badge-process' }}">
                                                        {{ ($item->qty_received ?? 0) >= ($item->qty_order ?? 0) ? 'FULL VERIFIED' : 'PARTIAL' }}
                                                    </span>
                                                </div>
                                                <div class="text-muted small font-weight-bold mb-3">
                                                    {{ $item->material_type ?? '-' }} | {{ $item->thickness ?? '' }} x {{ $item->size ?? '' }}
                                                </div>
                                                
                                                <div class="row text-center bg-light rounded-lg py-2 mx-0 border">
                                                    <div class="col-6 border-right">
                                                        <div class="text-muted font-weight-bold text-uppercase" style="font-size: 9px;">Diterima (Received)</div>
                                                        <div class="font-weight-bold text-success font-family-jetbrains h6 mb-0">{{ number_format($item->qty_received ?? 0) }}</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="text-muted font-weight-bold text-uppercase" style="font-size: 9px;">Target Quota</div>
                                                        <div class="font-weight-bold text-dark font-family-jetbrains h6 mb-0">{{ number_format($item->qty_order ?? 0) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="col-12">
                                            <p class="text-muted small italic mb-0">Tidak ada rincian item material yang tercatat.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty 
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="py-4">
                                <i class="fas fa-folder-open fa-3x text-muted opacity-50 mb-3"></i>
                                <p class="text-muted font-weight-bold mb-0">Tidak ada riwayat PO Supplier yang sesuai dengan kriteria filter.</p>
                            </div>
                        </td>
                    </tr> 
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if(isset($pos) && method_exists($pos, 'links'))
            <div class="p-3 border-top d-flex justify-content-center bg-white">
                {{ $pos->appends(request()->input())->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection