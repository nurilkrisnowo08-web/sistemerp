@extends('layout.admin')

@section('content')
<style>
    :root { --ind-steel: #4e73df; --ind-success: #1cc88a; --ind-danger: #e74a3b; --ind-bg: #f8f9fc; }
    .report-card { background: #fff; border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
    .stat-box { padding: 25px; border-radius: 15px; color: white; margin-bottom: 20px; }
    .bg-blue { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); }
    .bg-green { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); }
    .bg-red { background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%); }
    .filter-bar { background: #fff; padding: 20px; border-radius: 50px; box-shadow: 0 2px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
    @media print { .no-print { display: none; } .main-terminal { background: #fff; } }
</style>

<div class="main-terminal text-left p-4">
    <h3 class="font-weight-bold text-dark mb-4 uppercase">Industrial Report Center</h3>

    {{-- FILTER --}}
    <div class="filter-bar no-print">
        <form action="{{ route('reports.index') }}" method="GET" class="row align-items-center">
            <div class="col-md-3">
                <label class="small font-weight-bold text-muted">CEK TANGGAL (DAILY)</label>
                <input type="date" name="day" class="form-control form-control-sm border-0 bg-light" value="{{ $day }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold text-muted">BULAN (MONTHLY)</label>
                <select name="month" class="form-control form-control-sm border-0 bg-light" onchange="this.form.submit()">
                    @for($m=1; $m<=12; $m++) <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option> @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold text-muted">TAHUN</label>
                <select name="year" class="form-control form-control-sm border-0 bg-light" onchange="this.form.submit()">
                    <option value="2026" selected>2026</option>
                    <option value="2025">2025</option>
                </select>
            </div>
            <div class="col-md-5 text-right mt-3">
                <button type="button" onclick="window.print()" class="btn btn-dark btn-sm rounded-pill px-4"><i class="fas fa-print mr-2"></i> CETAK LAPORAN</button>
            </div>
        </form>
    </div>

    {{-- HARIAN --}}
    <div class="row">
        <div class="col-md-4">
            <div class="stat-box bg-blue">
                <h6 class="small uppercase opacity-75">Material In ({{ date('d M', strtotime($day)) }})</h6>
                <h2 class="font-weight-bold">{{ number_format($dailyIn->sum('total')) }} <span class="small">Pcs</span></h2>
                <div class="small">S: {{ number_format($dailyIn->where('source', 'supplier')->first()->total ?? 0) }} | R: {{ number_format($dailyIn->where('source', 'return')->first()->total ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box bg-green">
                <h6 class="small uppercase opacity-75">Hasil Produksi OK</h6>
                <h2 class="font-weight-bold">{{ number_format($dailyProd->total_ok ?? 0) }} <span class="small">Pcs</span></h2>
                <div class="small">Yield: {{ $dailyProd->total_ambil > 0 ? round(($dailyProd->total_ok / $dailyProd->total_ambil) * 100, 1) : 0 }}%</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box bg-red">
                <h6 class="small uppercase opacity-75">Total NG/Reject</h6>
                <h2 class="font-weight-bold">{{ number_format($dailyProd->total_ng ?? 0) }} <span class="small">Pcs</span></h2>
                <div class="small">Process & Material Errors</div>
            </div>
        </div>
    </div>

    {{-- BULANAN --}}
    <div class="report-card mt-4">
        <div class="card-header bg-white border-0 py-4"><h6>Monthly Material Balance ({{ date('F Y', mktime(0,0,0,$month,1,$year)) }})</h6></div>
        <div class="table-responsive p-3">
            <table class="table table-hover text-center" style="font-size: 13px;">
                <thead class="bg-light text-primary">
                    <tr>
                        <th>MATERIAL_CODE</th>
                        <th>TOTAL IN (S)</th>
                        <th>TOTAL IN (R)</th>
                        <th>TOTAL USED</th>
                        <th>NET_CHANGE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyUsage as $mu)
                    @php 
                        $inS = $monthlyIncoming->where('material_code', $mu->material_code)->where('source', 'supplier')->first()->total_in ?? 0;
                        $inR = $monthlyIncoming->where('material_code', $mu->material_code)->where('source', 'return')->first()->total_in ?? 0;
                        $net = ($inS + $inR) - $mu->total_used;
                    @endphp
                    <tr>
                        <td class="font-weight-bold">{{ $mu->material_code }}</td>
                        <td>+{{ number_format($inS) }}</td>
                        <td>+{{ number_format($inR) }}</td>
                        <td class="text-danger">-{{ number_format($mu->total_used) }}</td>
                        <td class="font-weight-bold {{ $net >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $net >= 0 ? '+' : '' }}{{ number_format($net) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection