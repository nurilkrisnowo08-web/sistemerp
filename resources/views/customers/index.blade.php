@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Roboto+Mono:wght@500&display=swap" rel="stylesheet">

<style>
    :root {
        --ind-steel: #4e73df; --ind-dark: #1e293b; --ind-success: #1cc88a;
        --ind-bg: #f8f9fc; --ind-slate: #5a5c69;
    }

    .main-terminal { font-family: 'Inter', sans-serif; background-color: var(--ind-bg); min-height: 100vh; }

    /* ✨ ANIMASI ENTRANCE */
    @keyframes fadeInUp { 
        from { opacity: 0; transform: translateY(20px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
    .anim-fade-up { animation: fadeInUp 0.8s ease-out both; }

    /* ✨ INDUSTRIAL CARD SYSTEM */
    .terminal-card { 
        background: #fff; border: none; border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem;
    }
    .accent-header { border-left: 6px solid var(--ind-steel); padding: 20px; background: #fff; }

    /* ✨ MODERN TABLE SYSTEM */
    .table-modern { border-collapse: separate; border-spacing: 0 10px; width: 100% !important; margin-top: -10px; }
    .table-modern thead th { 
        background: transparent; color: var(--ind-steel); border: none; 
        padding: 15px; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 800;
    }
    .table-modern tbody tr { 
        background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.03); 
        transition: transform 0.2s, box-shadow 0.2s; 
    }
    .table-modern tbody tr:hover { transform: scale(1.005); box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
    .table-modern td { padding: 18px 15px; vertical-align: middle; border: none; font-size: 13px; color: #334155; }
    
    /* Rounded corner untuk row */
    .table-modern td:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
    .table-modern td:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; }

    /* ✨ BADGE CUSTOMER CODE */
    .cust-code-badge { 
        font-family: 'Roboto Mono', monospace; font-weight: 800; color: var(--ind-steel);
        background: rgba(78, 115, 223, 0.1); padding: 6px 12px; border-radius: 6px; font-size: 12px;
    }

    /* ✨ BUTTONS MODERN */
    .btn-ind { border-radius: 50px; font-weight: 700; padding: 8px 22px; transition: all 0.3s; border: none; }
    .btn-ind:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    
    .btn-action {
        width: 36px; height: 36px; border-radius: 50%; display: inline-flex;
        align-items: center; justify-content: center; transition: all 0.2s; border: none;
    }
    .btn-action:hover { transform: scale(1.15); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }

    /* Sticky Sidebar Fix */
    #accordionSidebar { height: 100vh; position: sticky; top: 0; z-index: 1000; overflow-y: auto; }

    @media print { .no-print { display: none !important; } }
</style>

<div class="container-fluid main-terminal text-left anim-fade-up">
    
    {{-- 🛸 HEADER SECTION --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-extrabold uppercase" style="letter-spacing: -0.5px;">
                <i class="fas fa-address-book text-primary mr-2"></i> Master Data <span class="text-primary">Customers</span>
            </h1>
            <p class="text-muted small font-weight-bold mb-0 mt-1">
                <i class="fas fa-warehouse mr-1"></i> AMK CORE REGISTRY // STATUS: <span class="text-success">CONNECTED</span>
            </p>
        </div>
        <a href="{{ route('customers.create') }}" class="btn btn-primary btn-ind shadow-sm">
            <i class="fas fa-plus-circle mr-2"></i> Tambah Customer Baru
        </a>
    </div>

    {{-- 📊 MAIN DATA GRID --}}
    <div class="terminal-card">
        <div class="accent-header border-bottom d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-dark uppercase small tracking-widest">Database Relasi Bisnis</h6>
            <span class="badge badge-light border px-3 py-2 font-weight-bold text-muted" style="font-size: 10px;">TOTAL: {{ $customers->count() }} ENTITY</span>
        </div>
        <div class="card-body bg-light">
            <div class="table-responsive">
                <table class="table-modern text-left" width="100%" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>Identity Code</th>
                            <th>Partner Nomenclature</th>
                            <th>Operating Address</th>
                            <th>PIC</th>
                            <th>Contact Line</th>
                            <th class="no-print">Command</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $c)
                        <tr>
                            <td class="text-center">
                                <span class="cust-code-badge">{{ $c->code }}</span>
                            </td>
                            <td>
                                <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ $c->name }}</div>
                            </td>
                            <td>
                                <div class="small text-muted" style="max-width: 300px; line-height: 1.4;">{{ $c->address }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light border font-weight-bold px-3 py-1">{{ $c->pic ?? '-' }}</span>
                            </td>
                            <td class="text-center font-weight-bold text-primary">
                                <i class="fas fa-phone-alt mr-1 small"></i>{{ $c->phone }}
                            </td>
                            <td class="no-print">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('customers.edit', $c->id) }}" 
                                       class="btn btn-warning btn-action mr-2" 
                                       title="Update Record">
                                        <i class="fas fa-pencil-alt text-white fa-sm"></i>
                                    </a>

                                    <form action="{{ route('customers.destroy', $c->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Hapus record {{ $c->name }} dari sistem?')">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-action" title="Purge Data">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </div>
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