@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root { 
        --ind-red: #ef4444; 
        --ind-navy: #0f172a; 
        --ind-blue: #3b82f6;
        --ind-slate: #f8fafc;
        --ind-border: #e2e8f0;
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: var(--ind-navy); }

    /* Glassmorphism Card Industrial */
    .card-industrial { 
        border: none; 
        border-radius: 30px; 
        background: #ffffff; 
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04); 
        border: 1px solid rgba(255,255,255,0.7);
        overflow: hidden;
    }

    .heading-cyber { 
        font-family: 'Orbitron', sans-serif; 
        font-weight: 900; 
        letter-spacing: -1px; 
        text-transform: uppercase;
        background: linear-gradient(90deg, #0f172a 0%, #ef4444 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Tech Table Styling */
    .table-tech thead th { 
        background: #f8fafc; 
        color: #64748b; 
        font-size: 11px; 
        font-weight: 800; 
        text-transform: uppercase; 
        letter-spacing: 2px; 
        border: none; 
        padding: 20px;
    }
    .table-tech tbody tr { transition: 0.3s; }
    .table-tech tbody tr:hover { background-color: #fcfcfc; transform: scale(1.002); }
    .table-tech td { padding: 22px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; }

    /* Department Badges - Industrial High Vis */
    .badge-ind { padding: 8px 16px; border-radius: 12px; font-weight: 800; font-size: 10px; letter-spacing: 1px; border: 1.5px solid transparent; }
    .badge-welding { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .badge-stamping { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .badge-general { background: #f8fafc; color: #475569; border-color: #e2e8f0; }

    /* Cyber Input */
    .tech-input { 
        border-radius: 15px; 
        border: 2px solid #eef2f6; 
        padding: 14px 20px; 
        font-weight: 700; 
        background: #f8fafc; 
        transition: 0.3s;
        color: var(--ind-navy);
    }
    .tech-input:focus { border-color: var(--ind-red); background: #fff; box-shadow: 0 0 0 5px rgba(239, 68, 68, 0.1); outline: none; }

    .btn-cyber-red { 
        background: var(--ind-red); 
        color: white; 
        border-radius: 18px; 
        font-weight: 800; 
        padding: 12px 25px; 
        border: none; 
        box-shadow: 0 10px 20px rgba(239, 68, 68, 0.2);
        transition: 0.3s;
    }
    .btn-cyber-red:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(239, 68, 68, 0.3); color: white; }

    .btn-trash { 
        width: 40px; height: 40px; 
        display: inline-flex; align-items: center; justify-content: center; 
        border-radius: 12px; color: #cbd5e1; transition: 0.2s;
    }
    .btn-trash:hover { background: #fee2e2; color: #ef4444; }

    /* Counter Cards */
    .stat-pill { background: #fff; padding: 15px 25px; border-radius: 20px; border: 1px solid var(--ind-border); display: flex; align-items: center; }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    
    {{-- 🛰️ HEADER SECTION --}}
    <div class="row align-items-center mb-5">
        <div class="col-md-7">
            <h1 class="heading-cyber mb-1">Master <span style="font-weight: 400;">Defect_Registry</span></h1>
            <p class="text-muted font-weight-bold mb-0">
                <i class="fas fa-microchip text-danger mr-2"></i> Quality Assurance Standard Library v5.0
            </p>
        </div>
        <div class="col-md-5 text-md-right mt-3 mt-md-0">
            <button class="btn btn-cyber-red px-4 text-uppercase" data-toggle="modal" data-target="#modalAddNG">
                <i class="fas fa-plus-circle mr-2"></i> Deploy_New_NG
            </button>
        </div>
    </div>

    {{-- 📊 MINI STATS --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-pill shadow-sm">
                <div class="mr-3 text-danger"><i class="fas fa-shield-virus fa-2x"></i></div>
                <div>
                    <small class="text-muted font-weight-bold d-block text-uppercase">Total_NG_Types</small>
                    <span class="h5 font-weight-black mb-0">{{ count($listNG) }} Records</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-pill shadow-sm border-left-warning" style="border-left: 5px solid orange;">
                <div class="mr-3 text-warning"><i class="fas fa-fire fa-2x"></i></div>
                <div>
                    <small class="text-muted font-weight-bold d-block text-uppercase">Welding_Area</small>
                    <span class="h5 font-weight-black mb-0">{{ $listNG->where('category', 'WELDING')->count() }} Defect</span>
                </div>
            </div>
        </div>
    </div>

    {{-- NOTIFIKASI --}}
    @if(session('success')) 
        <div class="alert alert-success border-0 shadow-sm mb-4 animate__animated animate__fadeInDown" style="border-radius:18px;">
            <i class="fas fa-check-circle mr-2"></i> <b>SYSTEM_SUCCESS:</b> {{ session('success') }}
        </div> 
    @endif

    {{-- 📋 MAIN DATA TABLE --}}
    <div class="card-industrial shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-tech mb-0">
                <thead>
                    <tr>
                        <th class="pl-5">Defect_Classification</th>
                        <th>Status / Category</th>
                        <th>Registration_Timestamp</th>
                        <th class="text-right pr-5">System_Control</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listNG as $ng)
                    <tr>
                        <td class="pl-5">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-lg mr-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; color: #94a3b8;">
                                    <i class="fas fa-fingerprint"></i>
                                </div>
                                <div>
                                    <div class="font-weight-black text-dark text-uppercase" style="font-family: 'JetBrains Mono'; font-size: 15px; letter-spacing: -0.5px;">
                                        {{ $ng->ng_name }}
                                    </div>
                                    <small class="text-muted font-weight-bold">UID-NG: <span class="text-danger">00{{ $ng->id }}</span></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($ng->category == 'WELDING')
                                <span class="badge-ind badge-welding text-uppercase">WELDING STATION</span>
                            @elseif($ng->category == 'STAMPING')
                                <span class="badge-ind badge-stamping text-uppercase">STAMPING STATION</span>
                            @else
                                <span class="badge-ind badge-general text-uppercase">GENERAL_CORE</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center text-muted">
                                <i class="far fa-clock mr-2 small"></i>
                                <span class="font-weight-bold" style="font-size: 13px;">{{ date('d M Y', strtotime($ng->created_at ?? now())) }}</span>
                            </div>
                        </td>
                        <td class="text-right pr-5">
                            <form action="{{ route('welding.master.ng.destroy', $ng->id) }}" method="POST" onsubmit="return confirm('ERASE DATA: Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-trash" title="Delete Records">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" style="width: 80px; opacity: 0.2; filter: grayscale(1);">
                            <div class="mt-3 text-muted font-weight-bold uppercase italic">No Defect Records Found in Database</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🛡️ MODAL: DEPLOY NEW DEFECT --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalAddNG" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-2xl border-0" style="border-radius: 35px;">
            <div class="modal-header bg-ind-navy text-white p-4 border-0" style="background: var(--ind-navy);">
                <div class="d-flex align-items-center">
                    <div class="bg-danger rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-plus text-white"></i>
                    </div>
                    <h5 class="modal-title heading-cyber" style="background: none; -webkit-text-fill-color: #fff; font-size: 1.1rem; margin: 0;">
                        Defect_Deployment
                    </h5>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('welding.master.ng_store') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="form-group mb-4">
                        <label class="small font-weight-black text-muted uppercase mb-3 d-flex align-items-center">
                            <i class="fas fa-tag mr-2"></i> 01. NG_Type_Identification
                        </label>
                        <input type="text" name="ng_name" class="form-control tech-input" placeholder="e.g. BLOWHOLE, UNDERCUT, BURRY" required>
                    </div>
                    
                    <div class="form-group mb-0">
                        <label class="small font-weight-black text-muted uppercase mb-3 d-flex align-items-center">
                            <i class="fas fa-layer-group mr-2"></i> 02. Station_Allocation
                        </label>
                        <select name="category" class="form-control tech-input" required>
                            <option value="WELDING">WELDING_AREA (MAINTENANCE)</option>
                            <option value="STAMPING">STAMPING_AREA (PRESS)</option>
                            <option value="GENERAL">GENERAL / GLOBAL_ACCESS</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-cyber-red btn-block py-3 text-uppercase" style="font-size: 14px;">
                        Authorize & Register to Master
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection