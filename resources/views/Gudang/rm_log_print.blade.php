@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">

<style>
    :root { 
        --primary: #4361ee; 
        --dark: #0f172a; 
        --slate: #64748b;
        --border: #e2e8f0;
        --stamp-ink: #1e40af;
    }
    
    body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark); }

    /* Navigation UI rill */
    .btn-back-pill { 
        background: #ffffff; color: var(--dark) !important; border-radius: 50px; padding: 10px 24px; 
        font-weight: 700; border: 1.5px solid var(--border); transition: 0.3s; display: inline-flex; 
        align-items: center; font-size: 13px; text-decoration: none !important; box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .btn-back-pill:hover { transform: translateX(-5px); border-color: var(--primary); color: var(--primary) !important; }

    /* Report Container rill */
    .print-container { 
        background: white; padding: 60px; border-radius: 0; box-shadow: 0 20px 50px rgba(0,0,0,0.05); 
        max-width: 1300px; margin: 20px auto; position: relative; min-height: 297mm; /* A4 Ratio */
    }
    
    .header-doc { border-bottom: 5px solid var(--dark); padding-bottom: 25px; margin-bottom: 40px; }
    
    /* ✨ PREMIUM LEDGER TABLE RILL ✨ */
    .table-ledger { width: 100%; border-collapse: collapse; margin-bottom: 50px; }
    .table-ledger thead th { vertical-align: middle; border: 1px solid #cbd5e1; }
    
    .header-mutation-label { 
        background: #0f172a; color: #ffffff; font-size: 10px; text-transform: uppercase; 
        letter-spacing: 3px; font-weight: 800; padding: 12px !important; 
    }

    .table-ledger th { 
        background-color: #f8fafc; text-transform: uppercase; font-size: 11px; 
        font-weight: 800; color: #475569; letter-spacing: 1px; padding: 18px; 
    }
    
    .table-ledger td { 
        padding: 20px 15px; border: 1px solid #e2e8f0; font-size: 14px; 
        font-weight: 700; color: #1e293b; vertical-align: middle;
    }

    /* Column Theming rill */
    .col-init { background: rgba(241, 245, 249, 0.5); color: #64748b; font-family: 'JetBrains Mono'; }
    .col-in { background: rgba(16, 185, 129, 0.04); color: #10b981; font-family: 'JetBrains Mono'; }
    .col-out { background: rgba(239, 68, 68, 0.04); color: #ef4444; font-family: 'JetBrains Mono'; }
    .col-final { 
        background: rgba(67, 97, 238, 0.08); color: var(--primary); 
        font-family: 'Orbitron'; font-weight: 900 !important; font-size: 18px; 
    }

    /* Detailed Logs Section rill */
    .section-title { 
        background: #f1f5f9; padding: 12px 25px; border-radius: 8px; 
        font-size: 12px; font-weight: 800; color: #334155; 
        margin-bottom: 25px; display: inline-block; letter-spacing: 1px;
        border-left: 5px solid var(--primary);
    }

    .table-mini { width: 100%; }
    .table-mini th { 
        border-bottom: 2px solid var(--dark); padding: 12px 10px; 
        color: #64748b; font-size: 10px; text-transform: uppercase; text-align: left;
    }
    .table-mini td { 
        padding: 14px 10px; border-bottom: 1px solid #f1f5f9; 
        text-align: left; font-family: 'JetBrains Mono'; font-size: 11px; 
    }

    /* Logistics Stamp rill */
    .logistics-stamp {
        width: 150px; height: 150px; border: 5px double var(--stamp-ink); border-radius: 50%;
        color: var(--stamp-ink); display: flex; flex-direction: column; align-items: center;
        justify-content: center; text-align: center; font-weight: 900; text-transform: uppercase;
        line-height: 1.1; transform: rotate(-12deg); opacity: 0.8; margin: 0 auto;
    }

    .badge-pill-custom {
        padding: 6px 16px; border-radius: 50px; font-size: 10px; font-weight: 800;
    }

    @media print {
        .no-print, .btn-back-pill, .card-filter, .sidebar, .navbar { display: none !important; }
        body { background: white !important; margin: 0 !important; padding: 0 !important; }
        .print-container { box-shadow: none !important; margin: 0 !important; width: 100% !important; padding: 0 !important; }
        .header-mutation-label { background: #000 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
        .table-ledger th { background: #f1f5f9 !important; -webkit-print-color-adjust: exact; }
    }
</style>

<div class="container-fluid mt-3">
    
    {{-- NAVIGATION --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <a href="{{ route('rm.store') }}" class="btn-back-pill">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke RM_HUB
        </a>
        <div class="d-flex align-items-center">
             <div class="bg-white px-4 py-2 rounded-pill border shadow-sm mr-3">
                <small class="text-muted font-weight-bold uppercase" style="font-size: 10px;">Operational Shift: </small>
                <small class="font-weight-extrabold text-primary">{{ date('d F Y') }}</small>
            </div>
            <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 font-weight-bold shadow-sm">
                <i class="fas fa-print mr-2"></i> PRINT_REPORT
            </button>
        </div>
    </div>

    {{-- FILTER PANEL --}}
    <div class="card border-0 shadow-sm no-print mb-4 card-filter" style="border-radius: 20px;">
        <div class="card-body p-4">
            <form action="{{ route('rm.log_print') }}" method="GET" id="autoFilterForm" class="row align-items-end">
                <div class="col-md-3">
                    <label class="small font-weight-bold text-primary mb-2 ml-1 uppercase">Client_Entity</label>
                    <select name="customer" class="form-control border-0 bg-light font-weight-bold rounded-lg" style="height: 45px;">
                        <option value="">-- ALL CLIENTS --</option>
                        @foreach($availableCustomers as $c)
                            <option value="{{ trim($c->code) }}" {{ $customer == trim($c->code) ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold text-primary mb-2 ml-1 uppercase">Material_Spec</label>
                    <select name="spec" class="form-control border-0 bg-light font-weight-bold rounded-lg" style="height: 45px;">
                        <option value="">-- ALL SPECS --</option>
                        @foreach($availableSpecs as $s)
                            <option value="{{ $s }}" {{ $specFilter == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold text-muted mb-2 ml-1 uppercase">Start_Date</label>
                    <input type="date" name="start_date" class="form-control border-0 bg-light rounded-lg" style="height: 45px;" value="{{ $startDate }}">
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold text-muted mb-2 ml-1 uppercase">End_Date</label>
                    <input type="date" name="end_date" class="form-control border-0 bg-light rounded-lg" style="height: 45px;" value="{{ $endDate }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow-sm rounded-lg" style="height: 45px;">
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
                <h2 class="font-weight-extrabold m-0" style="letter-spacing:-2px; font-size: 34px; color: var(--dark);">RM HISTORICAL LEDGER</h2>
                <p class="text-muted small m-0 font-weight-bold uppercase" style="letter-spacing: 2px;">Industrial Logistics Archive | PT. Asalta Mandiri Agung</p>
            </div>
            <div class="text-right">
                <div class="small font-weight-bold text-uppercase text-muted mb-1" style="letter-spacing: 1px;">Registry Period</div>
                <span class="badge badge-dark px-4 py-2" style="font-size: 12px; font-family: 'JetBrains Mono'; border-radius: 8px;">
                    {{ date('d/m/Y', strtotime($startDate)) }} — {{ date('d/m/Y', strtotime($endDate)) }}
                </span>
            </div>
        </div>

        {{-- 🏆 MAIN MUTATION TABLE rill 🏆 --}}
        <table class="table-ledger text-center">
            <thead>
                <tr>
                    <th rowspan="2" class="text-left pl-4" style="width: 35%;">Material Identification & Spec</th>
                    <th colspan="4" class="header-mutation-label">Inventory Mutation Ledger (PCS)</th>
                    <th rowspan="2" style="width: 15%;">Status</th>
                </tr>
                <tr>
                    <th class="col-init">Stok Awal</th>
                    <th class="col-in">IN</th>
                    <th class="col-out">OUT</th>
                    <th class="col-final">Final</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historyData as $data)
                <tr>
                    <td class="text-left pl-4">
                        <div class="text-primary font-weight-extrabold" style="font-family: 'JetBrains Mono'; font-size: 15px;">{{ $data->alias ?? 'N/A' }}</div>
                        <div class="text-muted font-weight-bold uppercase" style="font-size: 10px; margin-top: 4px;">{{ $data->spec }} | {{ $data->size }}</div>
                    </td>
                    <td class="col-init">{{ number_format($data->initial) }}</td>
                    <td class="col-in">+{{ number_format($data->in_qty) }}</td>
                    <td class="col-out">-{{ number_format($data->out_qty) }}</td>
                    <td class="col-final">{{ number_format($data->final) }}</td>
                    <td>
                        @if($data->final <= 0) 
                            <span class="badge-pill-custom bg-light text-muted border">DEPLETED</span>
                        @elseif($data->final <= 500) 
                            <span class="badge-pill-custom bg-warning text-dark">LOW_STOCK</span>
                        @else 
                            <span class="badge-pill-custom bg-success text-white">AVAILABLE</span> 
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-5 text-muted font-weight-bold italic">-- No mutation history data found in this period --</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- 📝 TRANSACTION LIST (SECTION DETAIL) rill --}}
        <div class="section-title uppercase">Section III: Detailed Transaction Audit Trail</div>
        <table class="table-mini">
            <thead>
                <tr>
                    <th width="150">Timestamp</th>
                    <th width="120">Alias</th>
                    <th width="120">Type</th>
                    <th>Reference ID / Batch</th>
                    <th width="120" class="text-right">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($historyData as $group)
                    @foreach($group->logs as $log)
                    <tr>
                        <td class="text-muted">{{ date('d/m/Y H:i', strtotime($log->created_at)) }}</td>
                        <td class="font-weight-bold text-dark">{{ $group->alias }}</td>
                        <td>
                            @if(isset($log->pcs_used)) 
                                <span class="text-danger font-weight-bold">USAGE_OUT</span>
                            @elseif($log->source == 'return') 
                                <span class="text-info font-weight-bold">RETURN_IN</span>
                            @else 
                                <span class="text-success font-weight-bold">SUPPLY_IN</span> 
                            @endif
                        </td>
                        <td class="text-uppercase">{{ $log->no_produksi ?? ($log->po_identitas ?? 'MANUAL_ENTRY') }}</td>
                        <td class="font-weight-bold text-right {{ isset($log->pcs_used) ? 'text-danger' : 'text-primary' }}" style="font-size: 13px;">
                            {{ isset($log->pcs_used) ? '-' : '+' }}{{ number_format($log->pcs_used ?? $log->pcs_in) }}
                        </td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        {{-- SIGNATURE AREA --}}
        <div class="row mt-5 pt-5 text-center">
            <div class="col-4">
                <p class="mb-0 small font-weight-bold text-uppercase" style="letter-spacing: 1px;">Report Prepared By,</p>
                <div style="height: 90px; border-bottom: 2px solid var(--dark); width: 200px; margin: 0 auto 10px;"></div>
                <p class="font-weight-bold mb-0 text-dark">Logistics Officer</p>
                <small class="text-muted uppercase" style="font-size: 9px;">Warehouse Admin</small>
            </div>
            <div class="col-4 d-flex align-items-center justify-content-center">
                <div class="logistics-stamp animate__animated animate__zoomIn">
                    <div style="font-size: 9px;">PT ASALTA MANDIRI AGUNG</div>
                    <div style="font-size: 14px; border-top: 2px solid var(--stamp-ink); border-bottom: 2px solid var(--stamp-ink); padding: 5px 0; width: 100%; margin: 5px 0;">LOGISTICS<br>DEPARTMENT</div>
                    <div style="font-size: 8px;">KARAWANG PLANT</div>
                </div>
            </div>
            <div class="col-4">
                <p class="mb-0 small font-weight-bold text-uppercase" style="letter-spacing: 1px;">Authorized By,</p>
                <div style="height: 90px; border-bottom: 2px solid var(--dark); width: 200px; margin: 0 auto 10px;"></div>
                <p class="font-weight-bold mb-0 text-dark">Plant Manager</p>
                <small class="text-muted uppercase" style="font-size: 9px;">Official Validation</small>
            </div>
        </div>

        <div class="mt-5 text-center no-print">
            <p class="text-muted small">Generated via MRP System - Industrial Intelligence rill</p>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('#autoFilterForm select, #autoFilterForm input').forEach(el => {
        el.addEventListener('change', () => { 
            // Tambahkan loading sederhana rill
            document.body.style.opacity = '0.6';
            document.getElementById('autoFilterForm').submit(); 
        });
    });
</script>
@endsection