@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    :root { 
        --ind-red: #ef4444; --ind-navy: #0f172a; --ind-amber: #f59e0b;
        --ind-blue: #3b82f6; --ind-slate: #64748b;
    }

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; }

    /* Header Cyber Style */
    .heading-cyber { 
        font-family: 'Orbitron', sans-serif; font-weight: 900; letter-spacing: -1px; text-transform: uppercase;
        background: linear-gradient(90deg, var(--ind-navy) 0%, var(--ind-red) 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    /* Interactive Stat Cards */
    .filter-card {
        background: #fff; border-radius: 24px; padding: 20px; border: 2px solid transparent;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); cursor: pointer;
        position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    }
    .filter-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); }
    .filter-card.active { border-color: var(--ind-red); background: var(--ind-navy); }
    .filter-card.active h5, .filter-card.active small { color: #fff !important; }
    
    .card-icon { 
        width: 50px; height: 50px; border-radius: 15px; display: flex; 
        align-items: center; justify-content: center; margin-bottom: 15px;
    }

    /* Industrial Table */
    .card-industrial { border: none; border-radius: 30px; background: #fff; box-shadow: 0 15px 50px rgba(0,0,0,0.05); overflow: hidden; }
    .table-tech thead th { 
        background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 800; 
        text-transform: uppercase; letter-spacing: 2px; border: none; padding: 20px;
    }
    .table-tech td { padding: 20px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; transition: 0.3s; }
    
    /* Animation for filtering */
    .ng-row.hidden { display: none; }
    .ng-row.show { animation: fadeInUp 0.5s; }

    /* Custom Badge Tech */
    .badge-tech { 
        font-family: 'JetBrains Mono'; font-weight: 800; font-size: 10px; 
        padding: 6px 14px; border-radius: 8px; text-transform: uppercase; 
    }
</style>

<div class="container-fluid py-4 px-4 animate__animated animate__fadeIn">
    
    {{-- 🛰️ HEADER & ACTIONS --}}
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="heading-cyber mb-1">Master <span style="font-weight: 400;">Defect_Hub</span></h1>
            <p class="text-muted font-weight-bold mb-0 text-uppercase" style="font-size: 11px; letter-spacing: 1px;">
                <i class="fas fa-microchip text-danger mr-2"></i> Quality Assurance Control System v5.2
            </p>
        </div>
        <button class="btn btn-dark rounded-pill px-4 font-weight-black shadow-lg" data-toggle="modal" data-target="#modalAddNG" style="height: 50px;">
            <i class="fas fa-plus-circle mr-2 text-danger"></i> REGISTER_NEW_DEFECT
        </button>
    </div>

    {{-- 📊 INTERACTIVE FILTER CARDS --}}
    <div class="row mb-5">
        <div class="col-md-3">
            <div class="filter-card active" onclick="filterData('ALL', this)">
                <div class="card-icon bg-light text-dark"><i class="fas fa-layer-group"></i></div>
                <small class="text-muted font-weight-bold uppercase">Total_Registry</small>
                <h5 class="font-weight-black mb-0 font-mono">{{ count($listNG) }} Records</h5>
            </div>
        </div>
        <div class="col-md-3">
            <div class="filter-card" onclick="filterData('WELDING', this)">
                <div class="card-icon bg-warning-soft text-warning" style="background: #fffbeb;"><i class="fas fa-fire"></i></div>
                <small class="text-muted font-weight-bold uppercase">Welding_Sector</small>
                <h5 class="font-weight-black mb-0 font-mono">{{ $listNG->where('category', 'WELDING')->count() }} Types</h5>
            </div>
        </div>
        <div class="col-md-3">
            <div class="filter-card" onclick="filterData('STAMPING', this)">
                <div class="card-icon bg-primary-soft text-primary" style="background: #eff6ff;"><i class="fas fa-bolt"></i></div>
                <small class="text-muted font-weight-bold uppercase">Stamping_Sector</small>
                <h5 class="font-weight-black mb-0 font-mono">{{ $listNG->where('category', 'STAMPING')->count() }} Types</h5>
            </div>
        </div>
    </div>

    {{-- 📋 INDUSTRIAL DATA TABLE --}}
    <div class="card-industrial">
        <div class="table-responsive">
            <table class="table table-tech mb-0" id="ngTable">
                <thead>
                    <tr>
                        <th class="pl-5">Defect_Identification</th>
                        <th>Classification</th>
                        <th>Registration_Date</th>
                        <th class="text-right pr-5">Management</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listNG as $ng)
                    <tr class="ng-row show" data-category="{{ $ng->category }}">
                        <td class="pl-5">
                            <div class="d-flex align-items-center">
                                <div class="mr-3 p-3 bg-light rounded-lg text-muted"><i class="fas fa-barcode"></i></div>
                                <div>
                                    <div class="font-weight-black text-dark text-uppercase" style="font-size: 15px; font-family: 'JetBrains Mono';">{{ $ng->ng_name }}</div>
                                    <small class="text-muted font-weight-bold">UID: <span class="text-danger">#NG-0{{ $ng->id }}</span></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($ng->category == 'WELDING')
                                <span class="badge-tech bg-warning text-white"><i class="fas fa-fire mr-1"></i> Welding</span>
                            @elseif($ng->category == 'STAMPING')
                                <span class="badge-tech bg-primary text-white"><i class="fas fa-bolt mr-1"></i> Stamping</span>
                            @else
                                <span class="badge-tech bg-dark text-white"><i class="fas fa-globe mr-1"></i> General</span>
                            @endif
                        </td>
                        <td class="text-muted font-weight-bold">{{ date('d M Y', strtotime($ng->created_at ?? now())) }}</td>
                        <td class="text-right pr-5">
                            <form action="{{ route('welding.master.ng.destroy', $ng->id) }}" method="POST" onsubmit="return confirm('Erase from Master Database?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-link text-muted hover-danger"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-5 text-center text-muted italic font-weight-bold">No master records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🛡️ MODAL: DEPLOY NEW DEFECT (SAMA SEPERTI SEBELUMNYA) --}}
<div class="modal fade" id="modalAddNG" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-2xl border-0" style="border-radius: 35px;">
            <div class="modal-header bg-dark text-white p-4 border-0">
                <h5 class="modal-title heading-cyber" style="background:none; -webkit-text-fill-color:white; font-size: 1.1rem;">
                    <i class="fas fa-shield-virus mr-2 text-danger"></i> Register_Defect
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('welding.master.ng_store') }}" method="POST">
                @csrf
                <div class="modal-body p-5">
                    <div class="form-group mb-4">
                        <label class="small font-weight-black text-muted uppercase mb-2 d-block">NG Type Name</label>
                        <input type="text" name="ng_name" class="form-control tech-input" placeholder="e.g. BLOWHOLE" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-black text-muted uppercase mb-2 d-block">Classification</label>
                        <select name="category" class="form-control tech-input" required>
                            <option value="WELDING">WELDING_AREA</option>
                            <option value="STAMPING">STAMPING_AREA</option>
                            <option value="GENERAL">GENERAL_GLOBAL</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-5 pt-0">
                    <button type="submit" class="btn btn-danger btn-block py-3 font-weight-black rounded-2xl shadow-xl uppercase">
                        Confirm & Save to Master
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function filterData(dept, element) {
        // 1. Manage Active Class
        document.querySelectorAll('.filter-card').forEach(card => card.classList.remove('active'));
        element.classList.add('active');

        // 2. Filter Table Rows
        const rows = document.querySelectorAll('.ng-row');
        
        rows.forEach(row => {
            const rowCat = row.getAttribute('data-category');
            
            row.classList.remove('animate__animated', 'animate__fadeInUp');

            if (dept === 'ALL') {
                row.style.display = '';
                row.classList.add('animate__animated', 'animate__fadeInUp');
            } else if (rowCat === dept || rowCat === 'GENERAL') {
                // Tampilkan jika kategori sama ATAU kategori adalah GENERAL
                row.style.display = '';
                row.classList.add('animate__animated', 'animate__fadeInUp');
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endsection