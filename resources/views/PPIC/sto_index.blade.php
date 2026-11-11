@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    /* ✨ THEME WARNA & ANIMASI ✨ */
    .b-panel { 
        background: #ffffff; 
        border-radius: 16px; 
        padding: 25px; 
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }
    
    .page-title h1 { font-weight: 900; font-size: 26px; margin: 0; color: #1e293b; letter-spacing: -0.5px; }
    .page-title p { font-size: 12px; font-weight: 700; color: #64748b; margin: 4px 0 0 0; }

    .search-box {
        background: #f4f7fe;
        border-radius: 50rem;
        padding: 8px 16px;
        display: flex; align-items: center; gap: 10px;
        border: 1px solid #e2e8f0;
    }
    .search-box input, .search-box select { 
        border: none; outline: none; background: transparent; 
        color: #1e293b; font-weight: 700; width: 100%; font-size: 13px;
        cursor: pointer;
    }

    /* ✨ TABEL UTAMA STO ✨ */
    .trace-table-wrapper { overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0; }
    .trace-table { width: 100%; min-width: 1200px; border-collapse: collapse; }
    .trace-table th { background: rgba(37, 99, 235, 0.05); padding: 12px; font-size: 10px; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .trace-table td { padding: 12px; font-size: 12px; font-weight: 700; border-bottom: 1px solid #e2e8f0; color: #1e293b; vertical-align: middle; }
    
    /* Warna Kolom Identitas */
    .col-sys { background-color: #f8fafc; }
    .col-act { background-color: #f0fdf4; border-left: 2px solid #bbf7d0; border-right: 2px solid #bbf7d0; }
    
    .stock-sys { font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: bold; color: #475569; }
    .input-fisik { width: 80px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 8px; font-family: 'JetBrains Mono', monospace; font-weight: bold; font-size: 13px; text-align: center; color: #166534; outline: none; transition: 0.2s;}
    .input-fisik:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2); }
    
    .selisih-badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; display: inline-block; min-width: 40px; text-align: center; }
    .diff-ok { background: #e2e8f0; color: #475569; }
    .diff-plus { background: #dcfce7; color: #166534; }
    .diff-minus { background: #fee2e2; color: #b91c1c; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="page-title">
            <h1><i class="fas fa-clipboard-check text-success mr-2"></i>STOCK OPNAME (STO) HUB</h1>
            <p>Form Rekonsiliasi & Pencatatan Laporan Stok Fisik Bulanan.</p>
        </div>
        
        {{-- FORM FILTER SEARCH & TOMBOL ACTION --}}
        <form action="{{ route('ppic.sto.index') }}" method="GET" class="d-flex flex-wrap gap-2 align-items-center">
            <div class="search-box shadow-sm mr-2" style="min-width: 150px;">
                <i class="fas fa-users text-primary"></i>
                <select name="customer" onchange="this.form.submit()">
                    <option value="">ALL CUSTOMER</option>
                    @if(isset($customersList))
                        @foreach($customersList as $cust)
                            <option value="{{ $cust }}" {{ (($selectedCustomer ?? '') == $cust) ? 'selected' : '' }}>{{ strtoupper($cust) }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            
            <div class="search-box shadow-sm" style="min-width: 250px;">
                <i class="fas fa-search text-muted"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari Part Number..." onchange="this.form.submit()">
            </div>
            
            <div class="d-flex ml-2 gap-2">
                <button type="button" class="btn btn-success shadow-sm" style="border-radius: 50rem; font-weight: 800; padding: 8px 16px;" onclick="simpanMasal()">
                    <i class="fas fa-save mr-1"></i> SIMPAN STO MASAL
                </button>
                
                {{-- TOMBOL PRINT STO --}}
                <a href="{{ route('ppic.sto.print', ['customer' => $selectedCustomer, 'search' => $search]) }}" target="_blank" class="btn btn-primary shadow-sm" style="border-radius: 50rem; font-weight: 800; padding: 8px 16px;">
                    <i class="fas fa-print mr-1"></i> PRINT BLANKO STO
                </a>
            </div>
        </form>
    </div>

    <div class="b-panel">
        <div class="trace-table-wrapper">
            {{-- ✨ FORM POST: UNTUK SIMPAN DATA KE CONTROLLER ✨ --}}
            <form id="formSto" action="{{ route('ppic.sto.store') }}" method="POST">
                @csrf
                <table class="trace-table text-center">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center" width="3%">NO</th>
                            <th rowspan="2" class="text-left">PART NAME & NUMBER</th>
                            <th rowspan="2">CUST</th>
                            <th colspan="3" class="col-sys text-primary" style="border-bottom: 2px solid #93c5fd;">STOK SISTEM (DATABASE)</th>
                            <th colspan="3" class="col-act text-success" style="border-bottom: 2px solid #86efac;">STOK FISIK (AKTUAL LAPANGAN)</th>
                            <th colspan="3" class="text-danger" style="border-bottom: 2px solid #fca5a5;">SELISIH (+/-)</th>
                        </tr>
                        <tr>
                            <th class="col-sys">RM</th>
                            <th class="col-sys">WIP</th>
                            <th class="col-sys">FG</th>
                            
                            <th class="col-act">FISIK RM</th>
                            <th class="col-act">FISIK WIP</th>
                            <th class="col-act">FISIK FG</th>
                            
                            <th>RM</th>
                            <th>WIP</th>
                            <th>FG</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parts as $idx => $p)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="text-left">
                                <b style="font-size:13px; color:#1e293b;">{{ $p->part_name }}</b><br>
                                <span style="font-size:9px; color:#64748b;">{{ $p->part_no }}</span>
                                {{-- Hidden input agar ID Part kekirim ke Controller --}}
                                <input type="hidden" name="part_no[]" value="{{ $p->part_no }}">
                            </td>
                            <td><span class="badge badge-secondary px-2 py-1">{{ $p->customer_code }}</span></td>
                            
                            {{-- ANGKA STOK SISTEM & HIDDEN INPUT --}}
                            <td class="col-sys stock-sys" id="sys-rm-{{ $idx }}">{{ $p->rm_stock }}</td>
                            <input type="hidden" name="rm_sys[]" value="{{ $p->rm_stock }}">
                            
                            <td class="col-sys stock-sys" id="sys-wip-{{ $idx }}">{{ $p->wip_stock ?? 0 }}</td>
                            <input type="hidden" name="wip_sys[]" value="{{ $p->wip_stock ?? 0 }}">
                            
                            <td class="col-sys stock-sys" id="sys-fg-{{ $idx }}">{{ $p->fg_stock ?? 0 }}</td>
                            <input type="hidden" name="fg_sys[]" value="{{ $p->fg_stock ?? 0 }}">
                            
                            {{-- INPUT STOK FISIK AKTUAL (YANG DI-POST) --}}
                            <td class="col-act">
                                <input type="number" name="rm_act[]" class="input-fisik calc-sto" data-idx="{{ $idx }}" data-type="rm" value="{{ $p->rm_stock }}">
                            </td>
                            <td class="col-act">
                                <input type="number" name="wip_act[]" class="input-fisik calc-sto" data-idx="{{ $idx }}" data-type="wip" value="{{ $p->wip_stock ?? 0 }}">
                            </td>
                            <td class="col-act">
                                <input type="number" name="fg_act[]" class="input-fisik calc-sto" data-idx="{{ $idx }}" data-type="fg" value="{{ $p->fg_stock ?? 0 }}">
                            </td>
                            
                            {{-- INDIKATOR SELISIH --}}
                            <td><span class="selisih-badge diff-ok" id="diff-rm-{{ $idx }}">0</span></td>
                            <td><span class="selisih-badge diff-ok" id="diff-wip-{{ $idx }}">0</span></td>
                            <td><span class="selisih-badge diff-ok" id="diff-fg-{{ $idx }}">0</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <i class="fas fa-box-open text-muted" style="font-size: 40px; opacity:0.3; margin-bottom:10px;"></i><br>
                                <span class="text-muted font-weight-bold d-block">Data Part tidak ditemukan.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>
        <div class="mt-3 text-right">
            <span style="font-size:10px; color:#64748b; font-style:italic;">*Data akan disimpan secara terpisah ke dalam tabel riwayat sto_logs.</span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // FUNGSI KALKULASI SELISIH STO SECARA REAL-TIME
    document.querySelectorAll('.calc-sto').forEach(input => {
        input.addEventListener('input', function() {
            let idx = this.getAttribute('data-idx');
            let type = this.getAttribute('data-type');
            
            let systemVal = parseInt(document.getElementById(`sys-${type}-${idx}`).innerText.replace(/,/g, '')) || 0;
            let actualVal = parseInt(this.value) || 0;
            
            let diff = actualVal - systemVal;
            let badge = document.getElementById(`diff-${type}-${idx}`);
            
            if(diff > 0) {
                badge.innerText = '+' + diff;
                badge.className = 'selisih-badge diff-plus';
            } else if (diff < 0) {
                badge.innerText = diff; 
                badge.className = 'selisih-badge diff-minus';
            } else {
                badge.innerText = '0';
                badge.className = 'selisih-badge diff-ok';
            }
        });
    });

    // FUNGSI SIMPAN FORM MENGGUNAKAN SWEETALERT BARU
    function simpanMasal() {
        Swal.fire({
            title: 'Simpan Laporan STO Bulanan?',
            text: "Data ini HANYA disimpan sebagai riwayat STO per bulan. Data stok sistem saat ini TIDAK akan diubah / ditimpa.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#05CD99',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan Riwayat!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formSto').submit();
            }
        })
    }
</script>
@endsection@extends('layout.admin')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    /* ✨ THEME WARNA & ANIMASI ✨ */
    .b-panel { 
        background: #ffffff; 
        border-radius: 16px; 
        padding: 25px; 
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }
    
    .page-title h1 { font-weight: 900; font-size: 26px; margin: 0; color: #1e293b; letter-spacing: -0.5px; }
    .page-title p { font-size: 12px; font-weight: 700; color: #64748b; margin: 4px 0 0 0; }

    .search-box {
        background: #f4f7fe;
        border-radius: 50rem;
        padding: 8px 16px;
        display: flex; align-items: center; gap: 10px;
        border: 1px solid #e2e8f0;
    }
    .search-box input, .search-box select { 
        border: none; outline: none; background: transparent; 
        color: #1e293b; font-weight: 700; width: 100%; font-size: 13px;
        cursor: pointer;
    }

    /* ✨ TABEL UTAMA STO ✨ */
    .trace-table-wrapper { overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0; }
    .trace-table { width: 100%; min-width: 1200px; border-collapse: collapse; }
    .trace-table th { background: rgba(37, 99, 235, 0.05); padding: 12px; font-size: 10px; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .trace-table td { padding: 12px; font-size: 12px; font-weight: 700; border-bottom: 1px solid #e2e8f0; color: #1e293b; vertical-align: middle; }
    
    /* Warna Kolom Identitas */
    .col-sys { background-color: #f8fafc; }
    .col-act { background-color: #f0fdf4; border-left: 2px solid #bbf7d0; border-right: 2px solid #bbf7d0; }
    
    .stock-sys { font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: bold; color: #475569; }
    .input-fisik { width: 80px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 8px; font-family: 'JetBrains Mono', monospace; font-weight: bold; font-size: 13px; text-align: center; color: #166534; outline: none; transition: 0.2s;}
    .input-fisik:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2); }
    
    .selisih-badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; display: inline-block; min-width: 40px; text-align: center; }
    .diff-ok { background: #e2e8f0; color: #475569; }
    .diff-plus { background: #dcfce7; color: #166534; }
    .diff-minus { background: #fee2e2; color: #b91c1c; }
</style>

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="page-title">
            <h1><i class="fas fa-clipboard-check text-success mr-2"></i>STOCK OPNAME (STO) HUB</h1>
            <p>Form Rekonsiliasi & Pencatatan Laporan Stok Fisik Bulanan.</p>
        </div>
        
        {{-- FORM FILTER SEARCH & TOMBOL ACTION --}}
        <form action="{{ route('ppic.sto.index') }}" method="GET" class="d-flex flex-wrap gap-2 align-items-center">
            <div class="search-box shadow-sm mr-2" style="min-width: 150px;">
                <i class="fas fa-users text-primary"></i>
                <select name="customer" onchange="this.form.submit()">
                    <option value="">ALL CUSTOMER</option>
                    @if(isset($customersList))
                        @foreach($customersList as $cust)
                            <option value="{{ $cust }}" {{ (($selectedCustomer ?? '') == $cust) ? 'selected' : '' }}>{{ strtoupper($cust) }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            
            <div class="search-box shadow-sm" style="min-width: 250px;">
                <i class="fas fa-search text-muted"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari Part Number..." onchange="this.form.submit()">
            </div>
            
            <div class="d-flex ml-2 gap-2">
                <button type="button" class="btn btn-success shadow-sm" style="border-radius: 50rem; font-weight: 800; padding: 8px 16px;" onclick="simpanMasal()">
                    <i class="fas fa-save mr-1"></i> SIMPAN STO MASAL
                </button>
                
                {{-- TOMBOL PRINT STO --}}
                <a href="{{ route('ppic.sto.print', ['customer' => $selectedCustomer, 'search' => $search]) }}" target="_blank" class="btn btn-primary shadow-sm" style="border-radius: 50rem; font-weight: 800; padding: 8px 16px;">
                    <i class="fas fa-print mr-1"></i> PRINT BLANKO STO
                </a>
            </div>
        </form>
    </div>

    <div class="b-panel">
        <div class="trace-table-wrapper">
            {{-- ✨ FORM POST: UNTUK SIMPAN DATA KE CONTROLLER ✨ --}}
            <form id="formSto" action="{{ route('ppic.sto.store') }}" method="POST">
                @csrf
                <table class="trace-table text-center">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center" width="3%">NO</th>
                            <th rowspan="2" class="text-left">PART NAME & NUMBER</th>
                            <th rowspan="2">CUST</th>
                            <th colspan="3" class="col-sys text-primary" style="border-bottom: 2px solid #93c5fd;">STOK SISTEM (DATABASE)</th>
                            <th colspan="3" class="col-act text-success" style="border-bottom: 2px solid #86efac;">STOK FISIK (AKTUAL LAPANGAN)</th>
                            <th colspan="3" class="text-danger" style="border-bottom: 2px solid #fca5a5;">SELISIH (+/-)</th>
                        </tr>
                        <tr>
                            <th class="col-sys">RM</th>
                            <th class="col-sys">WIP</th>
                            <th class="col-sys">FG</th>
                            
                            <th class="col-act">FISIK RM</th>
                            <th class="col-act">FISIK WIP</th>
                            <th class="col-act">FISIK FG</th>
                            
                            <th>RM</th>
                            <th>WIP</th>
                            <th>FG</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parts as $idx => $p)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="text-left">
                                <b style="font-size:13px; color:#1e293b;">{{ $p->part_name }}</b><br>
                                <span style="font-size:9px; color:#64748b;">{{ $p->part_no }}</span>
                                {{-- Hidden input agar ID Part kekirim ke Controller --}}
                                <input type="hidden" name="part_no[]" value="{{ $p->part_no }}">
                            </td>
                            <td><span class="badge badge-secondary px-2 py-1">{{ $p->customer_code }}</span></td>
                            
                            {{-- ANGKA STOK SISTEM & HIDDEN INPUT --}}
                            <td class="col-sys stock-sys" id="sys-rm-{{ $idx }}">{{ $p->rm_stock }}</td>
                            <input type="hidden" name="rm_sys[]" value="{{ $p->rm_stock }}">
                            
                            <td class="col-sys stock-sys" id="sys-wip-{{ $idx }}">{{ $p->wip_stock ?? 0 }}</td>
                            <input type="hidden" name="wip_sys[]" value="{{ $p->wip_stock ?? 0 }}">
                            
                            <td class="col-sys stock-sys" id="sys-fg-{{ $idx }}">{{ $p->fg_stock ?? 0 }}</td>
                            <input type="hidden" name="fg_sys[]" value="{{ $p->fg_stock ?? 0 }}">
                            
                            {{-- INPUT STOK FISIK AKTUAL (YANG DI-POST) --}}
                            <td class="col-act">
                                <input type="number" name="rm_act[]" class="input-fisik calc-sto" data-idx="{{ $idx }}" data-type="rm" value="{{ $p->rm_stock }}">
                            </td>
                            <td class="col-act">
                                <input type="number" name="wip_act[]" class="input-fisik calc-sto" data-idx="{{ $idx }}" data-type="wip" value="{{ $p->wip_stock ?? 0 }}">
                            </td>
                            <td class="col-act">
                                <input type="number" name="fg_act[]" class="input-fisik calc-sto" data-idx="{{ $idx }}" data-type="fg" value="{{ $p->fg_stock ?? 0 }}">
                            </td>
                            
                            {{-- INDIKATOR SELISIH --}}
                            <td><span class="selisih-badge diff-ok" id="diff-rm-{{ $idx }}">0</span></td>
                            <td><span class="selisih-badge diff-ok" id="diff-wip-{{ $idx }}">0</span></td>
                            <td><span class="selisih-badge diff-ok" id="diff-fg-{{ $idx }}">0</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <i class="fas fa-box-open text-muted" style="font-size: 40px; opacity:0.3; margin-bottom:10px;"></i><br>
                                <span class="text-muted font-weight-bold d-block">Data Part tidak ditemukan.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>
        <div class="mt-3 text-right">
            <span style="font-size:10px; color:#64748b; font-style:italic;">*Data akan disimpan secara terpisah ke dalam tabel riwayat sto_logs.</span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // FUNGSI KALKULASI SELISIH STO SECARA REAL-TIME
    document.querySelectorAll('.calc-sto').forEach(input => {
        input.addEventListener('input', function() {
            let idx = this.getAttribute('data-idx');
            let type = this.getAttribute('data-type');
            
            let systemVal = parseInt(document.getElementById(`sys-${type}-${idx}`).innerText.replace(/,/g, '')) || 0;
            let actualVal = parseInt(this.value) || 0;
            
            let diff = actualVal - systemVal;
            let badge = document.getElementById(`diff-${type}-${idx}`);
            
            if(diff > 0) {
                badge.innerText = '+' + diff;
                badge.className = 'selisih-badge diff-plus';
            } else if (diff < 0) {
                badge.innerText = diff; 
                badge.className = 'selisih-badge diff-minus';
            } else {
                badge.innerText = '0';
                badge.className = 'selisih-badge diff-ok';
            }
        });
    });

    // FUNGSI SIMPAN FORM MENGGUNAKAN SWEETALERT BARU
    function simpanMasal() {
        Swal.fire({
            title: 'Simpan Laporan STO Bulanan?',
            text: "Data ini HANYA disimpan sebagai riwayat STO per bulan. Data stok sistem saat ini TIDAK akan diubah / ditimpa.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#05CD99',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan Riwayat!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formSto').submit();
            }
        })
    }
</script>
@endsection