@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root { 
        --primary: #4361ee; 
        --dark: #0f172a; 
        --border: #e2e8f0;
        --stamp-ink: #1e40af; /* Warna Biru Tinta Stempel rill */
    }
    
    body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark); }

    /* UI Navigation rill */
    .btn-back-pill { 
        background: #ffffff; 
        color: var(--dark) !important; 
        border-radius: 50px; 
        padding: 10px 24px; 
        font-weight: 700; 
        border: 1.5px solid var(--border); 
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        display: inline-flex; 
        align-items: center; 
        font-size: 13px; 
        text-decoration: none !important;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .btn-back-pill:hover { transform: translateX(-5px); border-color: var(--primary); background: #fff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }

    /* Report Container rill */
    .print-container { 
        background: white; 
        padding: 60px; 
        border-radius: 30px; 
        box-shadow: 0 20px 50px rgba(0,0,0,0.03); 
        max-width: 1200px; 
        margin: 20px auto; 
        position: relative;
    }
    
    .header-doc { border-bottom: 4px solid var(--dark); padding-bottom: 25px; margin-bottom: 40px; }
    
    /* Industrial Table Styling rill */
    .table-clean { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
    .table-clean th { 
        background: #f1f5f9; 
        color: #64748b; 
        padding: 16px; 
        font-size: 10px; 
        text-transform: uppercase; 
        letter-spacing: 1.5px;
        border: 1px solid var(--border); 
        text-align: center; 
        font-weight: 800;
    }
    .table-clean td { 
        padding: 16px; 
        border: 1px solid var(--border); 
        font-size: 12px; 
        font-family: 'JetBrains Mono', monospace; 
        text-align: center; 
    }

    .badge-status { padding: 5px 12px; border-radius: 8px; font-size: 9px; font-weight: 800; text-transform: uppercase; display: inline-block; letter-spacing: 0.5px; }
    .bg-supplier { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .bg-return { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

    /* ✨ LOGISTICS STAMP DESIGN rill ✨ */
    .logistics-stamp {
        width: 150px;
        height: 150px;
        border: 4px double var(--stamp-ink);
        border-radius: 50%;
        color: var(--stamp-ink);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-weight: 900;
        text-transform: uppercase;
        line-height: 1;
        transform: rotate(-12deg); /* Miring Biar Real rill */
        opacity: 0.8;
        user-select: none;
        margin: 0 auto;
        padding: 10px;
        background: transparent;
    }
    .stamp-top { font-size: 9px; margin-bottom: 4px; }
    .stamp-center { 
        font-size: 14px; 
        border-top: 2px solid var(--stamp-ink); 
        border-bottom: 2px solid var(--stamp-ink); 
        padding: 6px 0; 
        width: 100%;
        margin: 4px 0;
    }
    .stamp-bottom { font-size: 8px; margin-top: 4px; }

    @media print {
        .no-print, .sidebar, .navbar, .main-footer, .card-filter, .btn-back-pill { display: none !important; }
        body { background: white !important; margin: 0 !important; padding: 0 !important; }
        .print-container { box-shadow: none !important; margin: 0 !important; width: 100% !important; max-width: 100% !important; padding: 20px !important; }
        .table-clean th { background: #000 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
    }
</style>

<div class="container-fluid mt-3">
    
    {{-- ✨ NAVIGATION BAR rill ✨ --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <a href="{{ route('rm.store') }}" class="btn-back-pill">
            <i class="fas fa-arrow-left mr-2"></i> Active Operations
        </a>
        <div class="d-flex align-items-center bg-white px-4 py-2 rounded-pill border shadow-sm">
            <div class="mr-3 text-right">
                <small class="text-muted d-block font-weight-bold" style="font-size: 9px;">SYSTEM_STATUS</small>
                <small class="text-success font-weight-extrabold">CORE_ONLINE</small>
            </div>
            <div class="bg-light px-3 py-1 rounded-lg border">
                <small class="font-weight-bold text-dark">L-TIME: {{ date('H.i.s') }}</small>
            </div>
        </div>
    </div>

    {{-- ✨ FILTER PANEL rill ✨ --}}
    <div class="card border-0 shadow-sm no-print mb-5 card-filter" style="border-radius: 24px;">
        <div class="card-body p-4">
            <form action="{{ route('rm.log_print') }}" method="GET" id="autoFilterForm" class="row align-items-end">
                <div class="col-md-3">
                    <label class="small font-weight-bold text-primary mb-2 ml-1">CLIENT_ENTITY</label>
                    <select name="customer" class="form-control border-0 bg-light font-weight-bold" style="border-radius: 12px; height: 45px;">
                        <option value="">-- ALL CLIENTS --</option>
                        @foreach($availableCustomers as $c)
                            <option value="{{ trim($c->code) }}" {{ $customer == trim($c->code) ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold text-primary mb-2 ml-1">MATERIAL_SPEC</label>
                    <select name="spec" class="form-control border-0 bg-light font-weight-bold" style="border-radius: 12px; height: 45px;">
                        <option value="">-- ALL SPECS --</option>
                        @foreach($availableSpecs as $s)
                            <option value="{{ $s }}" {{ $specFilter == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold text-muted mb-2 ml-1">START_DATE</label>
                    <input type="date" name="start_date" class="form-control border-0 bg-light" style="border-radius: 12px; height: 45px;" value="{{ $startDate }}">
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold text-muted mb-2 ml-1">END_DATE</label>
                    <input type="date" name="end_date" class="form-control border-0 bg-light" style="border-radius: 12px; height: 45px;" value="{{ $endDate }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow" style="border-radius: 12px; height: 45px;">
                        <i class="fas fa-sync-alt mr-2"></i> SYNC_REPORT
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ✨ REPORT DOCUMENT rill ✨ --}}
    <div class="print-container animate__animated animate__fadeIn">
        <div class="header-doc d-flex justify-content-between align-items-end">
            <div>
                <h2 class="font-weight-extrabold m-0" style="letter-spacing:-2px; font-size: 32px; color: var(--dark);">RM TRACEABILITY REPORT</h2>
                <p class="text-muted small m-0 font-weight-bold" style="letter-spacing: 1px;">INDUSTRIAL LOGISTICS ARCHIVE | PT. ASALTA MANDIRI AGUNG</p>
            </div>
            <div class="text-right">
                <div class="small font-weight-bold text-uppercase text-muted mb-1">Registry Period</div>
                <span class="badge badge-dark px-4 py-2" style="font-size: 12px; font-family: 'JetBrains Mono'; border-radius: 10px;">
                    {{ date('d/m/Y', strtotime($startDate)) }} — {{ date('d/m/Y', strtotime($endDate)) }}
                </span>
            </div>
        </div>

        {{-- SECTION I rill --}}
        <div class="d-flex align-items-center mb-4">
            <div style="width: 6px; height: 22px; background: var(--primary); margin-right: 12px; border-radius: 10px;"></div>
            <h5 class="font-weight-bold text-dark m-0 text-uppercase" style="letter-spacing: 1px;">Section I: Incoming Material Registry</h5>
        </div>
        
        <table class="table-clean">
            <thead>
                <tr>
                    <th width="160">TIMESTAMP</th>
                    <th width="200">MATERIAL SPEC</th>
                    <th class="text-left">TRACEABILITY (PO / COIL ID)</th>
                    <th width="140">QTY (PCS)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logIn as $li)
                <tr>
                    <td class="text-muted">{{ date('d/m/Y H:i', strtotime($li->created_at)) }}</td>
                    <td class="font-weight-bold text-dark">{{ $li->spec }}</td>
                    <td class="text-left px-4">
                        @if($li->source == 'return')
                            <span class="badge-status bg-return">RETURN</span> 
                            <span class="ml-2">Prod ID: <b class="text-dark">{{ $li->no_produksi ?? 'N/A' }}</b></span>
                        @else
                            <span class="badge-status bg-supplier">SUPPLIER</span> 
                            <span class="ml-3">PO: <b>{{ $li->po_identitas ?? 'PO_MANUAL' }}</b> / Coil: <span class="text-primary">{{ $li->no_produksi ?? 'N/A' }}</span></span>
                        @endif
                    </td>
                    <td class="font-weight-bold text-primary" style="font-size: 14px;">+{{ number_format($li->pcs_in) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-5 text-muted italic">-- No Incoming Data Found in This Period --</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- SECTION II rill --}}
        <div class="d-flex align-items-center mb-4 mt-5">
            <div style="width: 6px; height: 22px; background: #ef4444; margin-right: 12px; border-radius: 10px;"></div>
            <h5 class="font-weight-bold text-dark m-0 text-uppercase" style="letter-spacing: 1px;">Section II: Factory Feeding (Production)</h5>
        </div>

        <table class="table-clean">
            <thead>
                <tr>
                    <th width="160">TIMESTAMP</th>
                    <th width="200">MATERIAL SPEC</th>
                    <th class="text-left">PRODUCTION ID / BATCH IDENTIFIER</th>
                    <th width="140">QTY (PCS)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logOut as $lo)
                <tr>
                    <td class="text-muted">{{ date('d/m/Y H:i', strtotime($lo->created_at)) }}</td>
                    <td class="font-weight-bold text-dark">{{ $lo->spec }}</td>
                    <td class="text-left px-4">
                        <span class="text-muted">USAGE_ID:</span> <b class="text-dark">{{ $lo->no_produksi }}</b>
                    </td>
                    <td class="text-danger font-weight-bold" style="font-size: 14px;">-{{ number_format($lo->pcs_used) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-5 text-muted italic">-- No Feeding Data Found in This Period --</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- ✨ SIGNATURE AREA & STEMPEL rill ✨ --}}
        <div class="row mt-5 pt-5 text-center">
            <div class="col-4">
                <p class="mb-0 small font-weight-bold text-uppercase" style="letter-spacing: 1px;">Prepared by (Warehouse),</p>
                <div style="height: 90px; border-bottom: 2px solid #000; width: 200px; margin: 0 auto 10px;"></div>
                <p class="font-weight-bold mb-0 text-dark">Logistics Admin</p>
                <small class="text-muted">MRP_SYSTEM_GENERATED</small>
            </div>

            {{-- 🏆 STEMPEL LOGISTIK RILL --}}
            <div class="col-4 d-flex align-items-center justify-content-center">
                <div class="logistics-stamp animate__animated animate__zoomIn animate__delay-1s">
                    <div class="stamp-top">PT ASALTA MANDIRI AGUNG</div>
                    <div class="stamp-center">LOGISTICS<br>DEPARTMENT</div>
                    <div class="stamp-bottom">KARAWANG PLANT</div>
                </div>
            </div>

            <div class="col-4">
                <p class="mb-0 small font-weight-bold text-uppercase" style="letter-spacing: 1px;">Authorized by (Manager),</p>
                <div style="height: 90px; border-bottom: 2px solid #000; width: 200px; margin: 0 auto 10px;"></div>
                <p class="font-weight-bold mb-0 text-dark">Plant Manager</p>
                <small class="text-muted">Validation Stamp</small>
            </div>
        </div>
        
        <div class="mt-5 text-center no-print">
            <p class="text-muted small">End of Traceability Document - PT. Asalta Mandiri Agung rill</p>
        </div>
    </div>

    {{-- Floating Print Button rill --}}
    <button onclick="window.print()" class="btn btn-dark no-print" style="position: fixed; bottom: 35px; right: 35px; border-radius: 50%; width: 70px; height: 70px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); z-index: 9999; border: 3px solid #fff;">
        <i class="fas fa-print fa-lg"></i>
    </button>
</div>

<script>
    // UX: Auto-sync pas ganti filter rill
    document.querySelectorAll('#autoFilterForm select, #autoFilterForm input').forEach(el => {
        el.addEventListener('change', () => {
            document.getElementById('autoFilterForm').submit();
        });
    });
</script>
@endsection