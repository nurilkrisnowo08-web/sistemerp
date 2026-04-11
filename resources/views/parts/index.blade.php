@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Roboto+Mono:wght@500&display=swap" rel="stylesheet">

<style>
    :root {
        --ind-steel: #4e73df; --ind-dark: #1e293b; --ind-success: #1cc88a;
        --ind-bg: #f8f9fc; --ind-slate: #5a5c69;
    }

    .main-terminal { font-family: 'Inter', sans-serif; background-color: var(--ind-bg); min-height: 100vh; padding: 1.5rem; }

    /* ✨ ANIMASI ENTRANCE */
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .anim-fade-up { animation: fadeInUp 0.8s ease-out both; }

    /* ✨ INDUSTRIAL CARD MODERN */
    .industrial-card { 
        background: #fff; border: none; border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem;
    }
    .accent-border { border-left: 6px solid var(--ind-steel); }

    /* ✨ FILTER AREA */
    .filter-label { font-weight: 800; color: var(--ind-steel); letter-spacing: 0.5px; font-size: 0.75rem; text-transform: uppercase; }
    .select-industrial { 
        border: 2px solid #e3e6f0; border-radius: 10px; font-weight: 700; color: var(--ind-dark); 
        transition: all 0.3s; padding: 10px 15px; height: auto !important;
    }
    .select-industrial:focus { border-color: var(--ind-steel); box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.1); }

    /* ✨ MODERN TABLE SYSTEM */
    .table-modern { border-collapse: separate; border-spacing: 0 8px; width: 100% !important; }
    .table-modern thead th { 
        background: var(--ind-steel); color: #fff; border: none; 
        padding: 15px; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 800;
    }
    .table-modern tbody tr { 
        background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.02); 
        transition: transform 0.2s, box-shadow 0.2s; 
    }
    .table-modern tbody tr:hover { transform: scale(1.005); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .table-modern td { padding: 15px; vertical-align: middle; border-top: 1px solid #f1f3f9; border-bottom: 1px solid #f1f3f9; font-size: 13px; color: #334155; }
    .table-modern td:first-child { border-left: 1px solid #f1f3f9; border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
    .table-modern td:last-child { border-right: 1px solid #f1f3f9; border-top-right-radius: 10px; border-bottom-right-radius: 10px; }

    .part-no-text { font-family: 'Roboto Mono', monospace; font-weight: 700; color: var(--ind-steel); }

    /* ✨ BUTTONS */
    .btn-ind { border-radius: 50px; font-weight: 700; padding: 8px 20px; transition: all 0.3s; }
    .btn-ind:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

    /* DataTables Overwrite */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: var(--ind-steel) !important; color: white !important; border-radius: 50px; border: none; }
    
    #partContainer { display: none; } /* Tetap default sembunyi */
</style>

<div class="main-terminal text-left">
    {{-- 🛰️ HEADER --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4 anim-fade-up">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-extrabold uppercase" style="letter-spacing: -0.5px;">
                <i class="fas fa-layer-group text-primary mr-2"></i>Master Data <span class="text-primary">Part Library</span>
            </h1>
            <p class="text-muted small font-weight-bold mb-0 mt-1">CORE SYSTEM AMK // STATUS: <span class="text-success">ONLINE</span></p>
        </div>
        <a href="{{ route('parts.create') }}" class="btn btn-primary btn-ind shadow">
            <i class="fas fa-plus-circle mr-2"></i> Register New Part
        </a>
    </div>

    {{-- 🛸 FILTER AREA --}}
    <div class="industrial-card accent-border anim-fade-up" style="animation-delay: 0.2s;">
        <div class="card-body p-4">
            <div class="row align-items-end">
                <div class="col-md-5">
                    <label class="filter-label"><i class="fas fa-microchip mr-1"></i> Selection Parameter</label>
                    <select id="customerFilter" class="form-control select-industrial">
                        <option value="">-- SELECT CUSTOMER CORE --</option>
                        @foreach($parts->unique('customer_code')->sortBy('customer_code') as $p)
                            <option value="{{ $p->customer_code }}">CUSTOMER: {{ $p->customer_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-7 d-none d-md-block">
                    <div class="p-2 px-3 bg-light rounded-pill border" style="border-style: dashed !important;">
                        <span class="small text-muted font-italic"><i class="fas fa-info-circle mr-1"></i> Database will initialize upon customer identification.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 📊 DATA CONTAINER --}}
    <div id="partContainer" class="industrial-card anim-fade-up" style="animation-delay: 0.4s;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
            <h6 class="m-0 font-weight-bold text-dark small uppercase tracking-widest" id="textInfo">Inventory Registry</h6>
            <i class="fas fa-database text-gray-300"></i>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-modern text-center" id="pencarianPart">
                    <thead>
                        <tr>
                            <th>Part Identity No</th>
                            <th class="text-left">Nomenclature (Name)</th>
                            <th>Owner Code</th>
                            <th class="no-print">Command</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parts as $p)
                        <tr>
                            <td class="part-no-text">{{ $p->part_no }}</td>
                            <td class="text-left font-weight-bold">{{ $p->part_name }}</td>
                            <td><span class="badge badge-light border font-weight-bold text-dark px-3 py-2">{{ $p->customer_code }}</span></td>
                            <td class="no-print">
                                <a href="{{ route('parts.edit', $p->id) }}" class="btn btn-warning btn-circle btn-sm shadow-sm">
                                    <i class="fas fa-pencil-alt text-white"></i>
                                </a>
                                <form action="{{ route('parts.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus part ini dari registry?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-circle btn-sm shadow-sm ml-1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        // 1. Inisialisasi DataTables (Logic Utuh)
        var table = $('#pencarianPart').DataTable({
            "paging": true,
            "info": true,
            "searching": true,
            "ordering": true,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "language": {
                "zeroRecords": "Data not found in system storage.",
                "search": "_INPUT_",
                "searchPlaceholder": "Search Part Details..."
            }
        });

        // 2. Logika Trigger Dropdown (Logic Utuh)
        $('#customerFilter').on('change', function() {
            var selectedCustomer = $(this).val();
            
            if (selectedCustomer !== "") {
                $('#partContainer').fadeIn(500); // Kasih efek fade in smooth
                table.column(2).search('^' + selectedCustomer + '$', true, false).draw();
                $('#textInfo').html('<i class="fas fa-check-circle text-success mr-1"></i> Data Identified: ' + selectedCustomer);
            } else {
                $('#partContainer').hide();
                table.search('').columns().search('').draw();
            }
        });

        // Styling search box agar seragam
        $('.dataTables_filter input').addClass('form-control form-control-sm select-industrial border-primary shadow-sm mb-3');
    });
</script>
@endsection