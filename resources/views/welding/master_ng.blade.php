@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root { --brand-danger: #ef4444; --brand-primary: #4361ee; --dark-navy: #0f172a; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    
    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; color: var(--dark-navy); }
    .card-master { border: none; border-radius: 25px; background: #ffffff; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02); overflow: hidden; }
    
    /* Table Styling */
    .table-ledger thead th { 
        background: #f1f5f9; color: #64748b; font-size: 10px; font-weight: 800; 
        text-transform: uppercase; letter-spacing: 1.5px; border: none; padding: 20px;
    }
    .table-ledger td { padding: 18px 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; font-size: 13px; }

    /* Badge Custom */
    .badge-welding { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .badge-stamping { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    
    /* Modal Input */
    .tech-input { border-radius: 12px; border: 2px solid #f1f5f9; padding: 12px; font-weight: 600; transition: 0.3s; }
    .tech-input:focus { border-color: var(--brand-danger); outline: none; box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1); }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    
    {{-- 🛰️ HEADER AREA --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="heading-hub mb-1">Master <span class="text-danger">Defect_Registry</span></h2>
            <p class="text-muted small font-weight-bold mb-0 text-uppercase">Central Library for Quality Standard Control</p>
        </div>
        <button class="btn btn-danger shadow-lg rounded-pill px-4 font-weight-black" data-toggle="modal" data-target="#modalAddNG">
            <i class="fas fa-plus-circle mr-2"></i> REGISTER_NEW_DEFECT
        </button>
    </div>

    {{-- NOTIFIKASI --}}
    @if(session('success')) <div class="alert alert-success border-0 shadow-sm mb-4 animate__animated animate__fadeInDown" style="border-radius:15px;"><b>✅ SUCCESS:</b> {{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger border-0 shadow-sm mb-4 animate__animated animate__shakeX" style="border-radius:15px;"><b>⚠️ ERROR:</b> {{ session('error') }}</div> @endif

    {{-- 📋 MAIN DATA TABLE --}}
    <div class="card-master shadow-sm">
        <div class="table-responsive">
            <table class="table table-ledger mb-0">
                <thead>
                    <tr>
                        <th class="pl-4">NG Type Identification</th>
                        <th>Classification</th>
                        <th>Registry Date</th>
                        <th class="text-right pr-4">Control</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listNG as $ng)
                    <tr class="animate__animated animate__fadeIn">
                        <td class="pl-4">
                            <div class="font-weight-black text-dark" style="font-family: 'JetBrains Mono'; font-size: 15px;">{{ strtoupper($ng->ng_name) }}</div>
                            <small class="text-muted">UID: #NG-{{ $ng->id }}</small>
                        </td>
                        <td>
                            @if($ng->category == 'WELDING')
                                <span class="badge badge-welding px-3 py-2 rounded-lg font-weight-bold">WELDING AREA</span>
                            @elseif($ng->category == 'STAMPING')
                                <span class="badge badge-stamping px-3 py-2 rounded-lg font-weight-bold">STAMPING AREA</span>
                            @else
                                <span class="badge badge-light border px-3 py-2 rounded-lg font-weight-bold">GENERAL</span>
                            @endif
                        </td>
                        <td class="text-muted font-weight-bold">{{ date('d M Y', strtotime($ng->created_at)) }}</td>
                        <td class="text-right pr-4">
                            <form action="{{ route('welding.master.ng.destroy', $ng->id) }}" method="POST" onsubmit="return confirm('Delete this defect master?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-link text-danger p-0" title="Delete Master Data">
                                    <i class="fas fa-trash-alt fa-lg"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted font-weight-bold italic">
                            <i class="fas fa-database fa-2x mb-3 opacity-25"></i><br>
                            NO DEFECT RECORDS FOUND IN REGISTRY
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🛡️ MODAL: ADD NEW DEFECT --}}
<div class="modal fade animate__animated animate__zoomIn" id="modalAddNG" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-2xl border-0" style="border-radius: 30px;">
            <div class="modal-header bg-danger text-white p-4 border-0">
                <h5 class="modal-title heading-hub" style="color: white; font-size: 1.1rem;">
                    <i class="fas fa-shield-virus mr-2"></i> Deploy_New_Defect
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('welding.master.ng_store') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="form-group mb-4">
                        <label class="small font-weight-black text-muted uppercase mb-2 d-block">01. Defect_Type_Name</label>
                        <input type="text" name="ng_name" class="form-control tech-input font-weight-bold" placeholder="e.g. BLOWHOLE, BURRY, SCRATCH" required>
                    </div>
                    
                    <div class="form-group mb-0">
                        <label class="small font-weight-black text-muted uppercase mb-2 d-block">02. Station_Classification</label>
                        <select name="category" class="form-control tech-input font-weight-bold" required>
                            <option value="WELDING">WELDING AREA</option>
                            <option value="STAMPING">STAMPING AREA</option>
                            <option value="GENERAL">GENERAL / GLOBAL</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-danger btn-block py-3 font-weight-black rounded-2xl shadow-xl">
                        AUTHORIZE & SAVE TO MASTER
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection