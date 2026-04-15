@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">

<style>
    :root { 
        --primary: #4361ee; 
        --dark: #0f172a; 
        --border: #e2e8f0;
        --stamp-ink: #1e40af;
    }
    
    body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark); }

    /* UI Navigation rill */
    .btn-back-pill { 
        background: #ffffff; color: var(--dark) !important; border-radius: 50px; padding: 10px 24px; 
        font-weight: 700; border: 1.5px solid var(--border); transition: 0.3s; display: inline-flex; 
        align-items: center; font-size: 13px; text-decoration: none !important; box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .btn-back-pill:hover { transform: translateX(-5px); border-color: var(--primary); }

    /* Report Container rill */
    .print-container { 
        background: white; padding: 50px; border-radius: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.03); 
        max-width: 1250px; margin: 20px auto; position: relative;
    }
    
    .header-doc { border-bottom: 4px solid var(--dark); padding-bottom: 25px; margin-bottom: 30px; }
    
    /* ✨ LEDGER TABLE STYLE RILL ✨ */
    .table-ledger { width: 100%; border-collapse: collapse; margin-bottom: 40px; text-align: center; }
    .table-ledger thead th { vertical-align: middle; border: 1px solid var(--border); }
    .header-mutation-label { background: #1e293b; color: #f8fafc; font-size: 9px; text-transform: uppercase; letter-spacing: 2px; font-weight: 800; padding: 10px !important; }
    .table-ledger th { background-color: #fdfdfd; text-transform: uppercase; font-size: 10px; font-weight: 800; color: #64748b; letter-spacing: 1px; padding: 15px; border-bottom: 2px solid var(--border) !important; }
    
    /* Column Colors rill */
    .col-init { background: rgba(148, 163, 184, 0.05); color: #64748b; font-family: 'JetBrains Mono'; font-weight: 700; }
    .col-in { background: rgba(16, 185, 129, 0.05); color: #10b981; font-family: 'JetBrains Mono'; font-weight: 700; }
    .col-out { background: rgba(239, 68, 68, 0.05); color: #ef4444; font-family: 'JetBrains Mono'; font-weight: 700; }
    .col-final { background: rgba(67, 97, 238, 0.07); color: var(--primary); font-family: 'Orbitron'; font-weight: 900 !important; font-size: 15px; }

    .table-ledger td { padding: 16px; border: 1px solid var(--border); font-size: 13px; font-weight: 700; }

    /* Detailed Logs rill */
    .detailed-logs-title { background: #f1f5f9; padding: 10px 20px; border-radius: 10px; font-size: 11px; font-weight: 800; color: #475569; margin-top: 50px; margin-bottom: 20px; display: inline-block; letter-spacing: 1px; }
    .table-mini { width: 100%; font-size: 10px; }
    .table-mini th { border-bottom: 2px solid var(--border); padding: 8px; color: #94a3b8; text-align: left; }
    .table-mini td { padding: 8px; border-bottom: 1px solid #f1f5f9; text-align: left; font-family: 'JetBrains Mono'; }

    /* Logistics Stamp Design rill */
    .logistics-stamp {
        width: 140px; height: 140px; border: 4px double var(--stamp-ink); border-radius: 50%;
        color: var(--stamp-ink); display: flex; flex-direction: column; align-items: center;
        justify-content: center; text-align: center; font-weight: 900; text-transform: uppercase;
        line-height: 1.1; transform: rotate(-12deg); opacity: 0.7; margin: 0 auto;
    }

    @media print {
        .no-print, .btn-back-pill, .card-filter { display: none !important; }
        body { background: white !important; margin: 0 !important; }
        .print-container { box-shadow: none !important; margin: 0 !important; width: 100% !important; padding: 10px !important; }
        .header-mutation-label { background: #000 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
    }
</style>

<div class="container-fluid mt-3">
    
    {{-- NAVIGATION --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <a href="{{ route('rm.store') }}" class="btn-back-pill">
            <i class="fas fa-arrow-left mr-2"></i> Operational Hub
        </a>
        <div class="bg-white px-4 py-2 rounded-pill border shadow-sm d-flex align-items-center">
            <small class="font-weight-bold text-dark">L-TIME: {{ date('H.i.s') }}</small>
        </div>
    </div>

    {{-- FILTER PANEL --}}
    <div class="card border-0 shadow-sm no-print mb-4 card-filter" style="border-radius: 20px;">
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

    {{-- ✨ REPORT DOCUMENT ✨ --}}
    <div class="print-container animate__animated animate__fadeIn">
        <div class="header-doc d-flex justify-content-between align-items-end">
            <div>
                <h2 class="font-weight-extrabold m-0" style="letter-spacing:-2px; font-size: 30px;">RM HISTORICAL LEDGER</h2>
                <p class="text-muted small m-0 font-weight-bold" style="letter-spacing: 1px;">INDUSTRIAL LOGISTICS ARCHIVE | PT. ASALTA MANDIRI AGUNG</p>
            </div>
            <div class="text-right">
                <div class="small font-weight-bold text-uppercase text-muted mb-1">Registry Period</div>
                <span class="badge badge-dark px-4 py-2" style="font-size: 11px; font-family: 'JetBrains Mono'; border-radius: 8px;">
                    {{ date('d/m/Y', strtotime($startDate)) }} — {{ date('d/m/Y', strtotime($endDate)) }}
                </span>
            </div>
        </div>

        {{-- 🏆 MAIN MUTATION TABLE rill 🏆 --}}
        <table class="table-ledger">
            <thead>
                <tr>
                    <th rowspan="2" class="text-left pl-4" style="width: 30%;">Material Identification</th>
                    <th colspan="4" class="header-mutation-label">Inventory Mutation Ledger (PCS)</th>
                    <th rowspan="2">Status</th>
                </tr>
                <tr>
                    <th class="col-init">Initial</th>
                    <th class="col-in">In</th>
                    <th class="col-out">Out</th>
                    <th class="col-final">Final</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historyData as $data)
                <tr>
                    <td class="text-left pl-4">
                        <div class="font-weight-extrabold text-primary" style="font-family: 'JetBrains Mono';">{{ $data->alias ?? 'N/A' }}</div>
                        <div class="text-muted" style="font-size: 10px;">{{ $data->spec }} | {{ $data->size }}</div>
                    </td>
                    <td class="col-init">{{ number_format($data->initial) }}</td>
                    <td class="col-in">+{{ number_format($data->in_qty) }}</td>
                    <td class="col-out">-{{ number_format($data->out_qty) }}</td>
                    <td class="col-final">{{ number_format($data->final) }}</td>
                    <td>
                        @if($data->final <= 0) <span class="badge badge-secondary p-1 px-3 rounded-pill">EMPTY</span>
                        @elseif($data->final <= 500) <span class="badge badge-warning p-1 px-3 rounded-pill">LOW</span>
                        @else <span class="badge badge-success p-1 px-3 rounded-pill">OK</span> @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-5 text-muted">-- No data history in this period --</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- 📝 TRANSACTION LIST (SECTION DETAIL) rill --}}
        <div class="detailed-logs-title uppercase">Section III: Detailed Transaction History</div>
        <table class="table-mini">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Alias</th>
                    <th>Type</th>
                    <th>Reference ID</th>
                    <th>Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($historyData as $group)
                    @foreach($group->logs as $log)
                    <tr>
                        <td class="text-muted">{{ date('d/m/Y H:i', strtotime($log->created_at)) }}</td>
                        <td class="font-weight-bold text-dark">{{ $group->alias }}</td>
                        <td>
                            @if(isset($log->pcs_used)) <span class="text-danger font-weight-bold">PROD_OUT</span>
                            @elseif($log->source == 'return') <span class="text-info font-weight-bold">RETURN_IN</span>
                            @else <span class="text-success font-weight-bold">SUPP_IN</span> @endif
                        </td>
                        <td>{{ $log->no_produksi ?? ($log->po_identitas ?? 'MANUAL') }}</td>
                        <td class="font-weight-bold {{ isset($log->pcs_used) ? 'text-danger' : 'text-primary' }}">
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
                <p class="mb-0 small font-weight-bold text-uppercase">Prepared by,</p>
                <div style="height: 80px; border-bottom: 2px solid #000; width: 180px; margin: 0 auto 10px;"></div>
                <p class="font-weight-bold mb-0 text-dark">Logistics Admin</p>
            </div>
            <div class="col-4 d-flex align-items-center justify-content-center">
                <div class="logistics-stamp">
                    <div style="font-size: 8px;">PT ASALTA MANDIRI AGUNG</div>
                    <div style="font-size: 13px; border-top: 2px solid var(--stamp-ink); border-bottom: 2px solid var(--stamp-ink); padding: 5px 0; width: 100%; margin: 5px 0;">LOGISTICS<br>DEPARTMENT</div>
                    <div style="font-size: 8px;">VALIDATED</div>
                </div>
            </div>
            <div class="col-4">
                <p class="mb-0 small font-weight-bold text-uppercase">Authorized by,</p>
                <div style="height: 80px; border-bottom: 2px solid #000; width: 180px; margin: 0 auto 10px;"></div>
                <p class="font-weight-bold mb-0 text-dark">Plant Manager</p>
            </div>
        </div>
    </div>

    {{-- Floating Print Button --}}
    <button onclick="window.print()" class="btn btn-dark no-print" style="position: fixed; bottom: 35px; right: 35px; border-radius: 50%; width: 70px; height: 70px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); z-index: 9999; border: 3px solid #fff;">
        <i class="fas fa-print fa-lg"></i>
    </button>
</div>

<script>
    document.querySelectorAll('#autoFilterForm select, #autoFilterForm input').forEach(el => {
        el.addEventListener('change', () => { document.getElementById('autoFilterForm').submit(); });
    });
</script>
@endsection