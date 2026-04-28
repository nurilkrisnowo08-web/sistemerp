@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Orbitron:wght@700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root {
        --stamping-blue: #4361ee; --welding-gold: #f59e0b; --industrial-navy: #0f172a;
        --glass-white: rgba(255, 255, 255, 0.9);
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }
    
    /* Typography Industrial */
    .heading-tech { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; color: var(--industrial-navy); }
    .yield-value { font-family: 'Orbitron'; font-weight: 900; line-height: 1; }
    .font-mono { font-family: 'JetBrains Mono', monospace; }

    /* Card Layouts */
    .dept-card { background: #fff; border-radius: 30px; border: 1px solid #e2e8f0; overflow: hidden; height: 100%; transition: 0.3s; }
    .dept-card:hover { border-color: var(--stamping-blue); box-shadow: 0 20px 40px rgba(0,0,0,0.05); }
    
    .dept-header-stamping { background: linear-gradient(90deg, #4361ee, #4cc9f0); color: white; padding: 20px; }
    .dept-header-welding { background: linear-gradient(90deg, #f59e0b, #fbbf24); color: #000; padding: 20px; }

    .stat-pill { background: #f8fafc; border-radius: 20px; padding: 15px; border: 1px solid #edf2f7; text-align: center; }
    
    /* Table Styling */
    .table-tech td { font-size: 13px; font-weight: 700; vertical-align: middle; padding: 15px !important; }
    .row-clickable { cursor: pointer; transition: 0.2s; }
    .row-clickable:hover { background-color: #f0f7ff !important; transform: scale(1.01); }

    /* NG Tags */
    .ng-badge { background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 8px; font-size: 11px; font-weight: 800; border: 1px solid #fecaca; }
    
    /* Fixed Button Date */
    .btn-sync { background: var(--industrial-navy); color: white; border-radius: 12px; border: none; padding: 8px 20px; font-weight: 700; transition: 0.3s; }
    .btn-sync:hover { background: var(--stamping-blue); }
</style>

<div class="container-fluid py-4 px-4">
    
    {{-- 🛰️ UNIFIED HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div class="animate__animated animate__fadeInLeft">
            <h1 class="heading-tech mb-1">Quality_Hub <span class="text-primary">Intelligence</span></h1>
            <p class="text-muted small font-weight-bold uppercase mb-0">PT ASALTA MANDIRI AGUNG // Unified Quality Control Center</p>
        </div>
        <form action="" method="GET" class="bg-white p-2 rounded-pill shadow-sm border d-flex align-items-center animate__animated animate__fadeInRight">
            <i class="fas fa-calendar-alt mx-3 text-primary"></i>
            <input type="date" name="date" class="border-0 font-weight-bold mr-2" value="{{ $date }}" onchange="this.form.submit()">
            <button type="submit" class="btn-sync">SYNC_DATA</button>
        </form>
    </div>

    <div class="row">
        
        {{-- --- 🛠️ ZONA 1: STAMPING DEPARTMENT --- --}}
        <div class="col-lg-6 mb-4 animate__animated animate__fadeInUp">
            <div class="dept-card shadow-sm">
                <div class="dept-header-stamping d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="font-weight-black mb-0 uppercase" style="font-family: 'Orbitron';">Stamping_Control</h5>
                        <small class="font-weight-bold opacity-75">Production Logs & Yield</small>
                    </div>
                    <i class="fas fa-microchip fa-2x opacity-50"></i>
                </div>
                
                <div class="p-4">
                    {{-- Stats --}}
                    <div class="row mb-4">
                        <div class="col-4">
                            <div class="stat-pill">
                                <small class="text-muted font-weight-bold uppercase d-block mb-1">OK Goods</small>
                                <div class="yield-value text-success h4 mb-0">{{ number_format($sumStamping->total_ok ?? 0) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-pill">
                                <small class="text-muted font-weight-bold uppercase d-block mb-1">NG Goods</small>
                                <div class="yield-value text-danger h4 mb-0">{{ number_format($sumStamping->total_ng ?? 0) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-pill bg-dark text-white border-0">
                                <small class="text-info font-weight-bold uppercase d-block mb-1">Yield Rate</small>
                                @php $totalS = ($sumStamping->total_ok ?? 0) + ($sumStamping->total_ng ?? 0); @endphp
                                <div class="yield-value text-warning h4 mb-0">{{ $totalS > 0 ? round(($sumStamping->total_ok / $totalS) * 100, 1) : 0 }}%</div>
                            </div>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-tech text-center">
                            <thead class="bg-light">
                                <tr class="small text-muted font-weight-black uppercase">
                                    <th class="text-left pl-4">Part ID</th>
                                    <th>Station</th>
                                    <th>OK</th>
                                    <th>NG</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailStamping as $ds)
                                <tr class="row-clickable" onclick="showDrilldown('STAMPING', {{ json_encode($ds->part_no) }}, {{ json_encode($ds->batches ?? []) }})">
                                    <td class="text-left pl-4">
                                        <div class="text-primary font-weight-black">{{ $ds->part_no }}</div>
                                        <small class="text-muted font-mono">STAMPING_MODE</small>
                                    </td>
                                    <td><span class="badge badge-dark font-mono">{{ $ds->line_code }}</span></td>
                                    <td class="text-success font-weight-black">{{ number_format($ds->qty_ok) }}</td>
                                    <td class="text-danger font-weight-black">{{ number_format($ds->qty_ng) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- --- ⚡ ZONA 2: WELDING DEPARTMENT --- --}}
        <div class="col-lg-6 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
            <div class="dept-card shadow-sm border-warning">
                <div class="dept-header-welding d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="font-weight-black mb-0 uppercase" style="font-family: 'Orbitron';">Welding_Control</h5>
                        <small class="font-weight-bold opacity-75">Verification Logs & Yield</small>
                    </div>
                    <i class="fas fa-bolt-lightning fa-2x opacity-50"></i>
                </div>
                
                <div class="p-4">
                    {{-- Stats --}}
                    <div class="row mb-4">
                        <div class="col-4">
                            <div class="stat-pill">
                                <small class="text-muted font-weight-bold uppercase d-block mb-1">OK Output</small>
                                <div class="yield-value text-success h4 mb-0">{{ number_format($sumWelding->total_ok ?? 0) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-pill">
                                <small class="text-muted font-weight-bold uppercase d-block mb-1">NG Output</small>
                                <div class="yield-value text-danger h4 mb-0">{{ number_format($sumWelding->total_ng ?? 0) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-pill bg-dark text-white border-0">
                                <small class="text-info font-weight-bold uppercase d-block mb-1">Yield Rate</small>
                                @php $totalW = ($sumWelding->total_ok ?? 0) + ($sumWelding->total_ng ?? 0); @endphp
                                <div class="yield-value text-warning h4 mb-0">{{ $totalW > 0 ? round(($sumWelding->total_ok / $totalW) * 100, 1) : 0 }}%</div>
                            </div>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-tech text-center">
                            <thead class="bg-light">
                                <tr class="small text-muted font-weight-black uppercase">
                                    <th class="text-left pl-4">Part ID</th>
                                    <th>Station</th>
                                    <th>OK</th>
                                    <th>NG</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailWelding as $dw)
                                <tr class="row-clickable" onclick="showDrilldown('WELDING', {{ json_encode($dw->part_no) }}, {{ json_encode($dw->batches ?? []) }})">
                                    <td class="text-left pl-4">
                                        <div class="text-warning font-weight-black">{{ $dw->part_no }}</div>
                                        <small class="text-muted font-mono">WELDING_MODE</small>
                                    </td>
                                    <td><span class="badge badge-warning text-dark font-mono">{{ $dw->line_code }}</span></td>
                                    <td class="text-success font-weight-black">{{ number_format($dw->qty_ok) }}</td>
                                    <td class="text-danger font-weight-black">{{ number_format($dw->qty_ng) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- --- 📊 NG RANKING SECTION (BOTTOM) --- --}}
    <div class="row mt-5">
        <div class="col-md-6 animate__animated animate__fadeInUp">
            <h6 class="font-weight-black text-muted uppercase small mb-3"><i class="fas fa-bug mr-2 text-primary"></i> Stamping Defect Breakdown</h6>
            <div class="bg-white p-4 rounded-3xl shadow-sm border">
                @foreach($ngStamping as $index => $ns)
                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                    <span class="font-weight-bold text-dark small"><span class="text-primary mr-2">#{{$index+1}}</span> {{ strtoupper($ns->ng_type) }}</span>
                    <span class="ng-badge">{{ $ns->total }} PCS</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="col-md-6 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
            <h6 class="font-weight-black text-muted uppercase small mb-3"><i class="fas fa-fire mr-2 text-warning"></i> Welding Defect Breakdown</h6>
            <div class="bg-white p-4 rounded-3xl shadow-sm border">
                @foreach($ngWelding as $index => $nw)
                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                    <span class="font-weight-bold text-dark small"><span class="text-warning mr-2">#{{$index+1}}</span> {{ strtoupper($nw->ng_type) }}</span>
                    <span class="ng-badge" style="background:#fef3c7; color:#92400e; border-color:#fde68a;">{{ $nw->total }} PCS</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- --- 🤖 MODAL DRILLDOWN BATCH --- --}}
<div class="modal fade" id="modalDrilldown" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: 30px;">
            <div id="modalHeader" class="modal-header text-white p-4" style="border-radius: 30px 30px 0 0;">
                <h5 class="modal-title font-weight-black uppercase" id="drilldownTitle">BATCH_DRILLDOWN</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-hover mb-0 text-center">
                    <thead class="bg-light small font-weight-black uppercase">
                        <tr>
                            <th class="text-left pl-4">No Produksi</th>
                            <th>Line</th>
                            <th>In</th>
                            <th>OK</th>
                            <th>NG</th>
                        </tr>
                    </thead>
                    <tbody id="drilldownBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function showDrilldown(type, partNo, batches) {
        const title = document.getElementById('drilldownTitle');
        const header = document.getElementById('modalHeader');
        const body = document.getElementById('drilldownBody');
        
        title.innerText = `${type}_BATCH_LOG: ${partNo}`;
        header.className = (type === 'STAMPING') ? 'modal-header bg-primary text-white p-4' : 'modal-header bg-warning text-dark p-4';
        
        body.innerHTML = '';
        if (batches.length === 0) {
            body.innerHTML = '<tr><td colspan="5" class="py-5 text-muted">-- NO ACTIVE BATCHES FOUND --</td></tr>';
        } else {
            batches.forEach(b => {
                body.innerHTML += `
                    <tr>
                        <td class="text-left pl-4 font-weight-bold font-mono" style="font-size:11px;">
                            <span class="badge badge-light border">${b.no_produksi}</span>
                        </td>
                        <td><span class="badge badge-outline-dark">${b.kode_Line || 'GENERAL'}</span></td>
                        <td class="font-weight-black">${parseInt(b.qty_ambil_pcs || b.qty_masuk).toLocaleString()}</td>
                        <td class="text-success font-weight-black">${parseInt(b.qty_hasil_ok || b.qty_ok || 0).toLocaleString()}</td>
                        <td class="text-danger font-weight-black">${parseInt(b.qty_hasil_ng || b.qty_ng || 0).toLocaleString()}</td>
                    </tr>
                `;
            });
        }
        $('#modalDrilldown').modal('show');
    }
</script>
@endsection