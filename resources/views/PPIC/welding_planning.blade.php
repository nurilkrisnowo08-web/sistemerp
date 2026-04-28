@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    :root { --ind-blue: #4361ee; --ind-navy: #0f172a; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    .heading-hub { font-family: 'Orbitron'; font-weight: 900; letter-spacing: -1px; text-transform: uppercase; color: var(--ind-navy); }
    .stat-card { background: #fff; border-radius: 24px; padding: 25px; border: 1px solid #e2e8f0; transition: 0.3s; height: 100%; }
    .stat-value { font-family: 'Orbitron'; font-size: 28px; font-weight: 800; color: var(--ind-navy); }
</style>

<div class="container-fluid py-4 px-4">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="heading-hub mb-1">Welding_Intelligence <span class="text-primary">Hub</span></h1>
            <p class="text-muted font-weight-bold small uppercase mb-0">Dedicated Monitoring for Robot & Spot Welding Stations</p>
        </div>
        <div class="d-flex align-items-center">
            <form action="{{ route('ppic.welding.index') }}" method="GET" class="mr-3">
                <input type="date" name="date" class="form-control rounded-pill border-0 shadow-sm px-4" value="{{ $date }}" onchange="this.form.submit()">
            </form>
            <a href="{{ route('ppic.welding.mps') }}" class="btn btn-primary rounded-pill px-4 font-weight-bold shadow-lg">WELDING_MPS</a>
        </div>
    </div>

    {{-- ALERTS (IF ANY PROBLEM) --}}
    @if($alerts->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            @foreach($alerts as $alert)
            <div class="alert alert-danger border-0 shadow-sm rounded-2xl p-4 animate__animated animate__headShake d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge badge-danger px-3 py-2 mb-2">🚨 PROBLEM DETECTED</span>
                    <h5 class="font-weight-bold mb-1">{{ $alert->kode_line }} // {{ $alert->part_no }}</h5>
                    <p class="mb-0 small font-weight-bold uppercase">{{ $alert->keterangan }}</p>
                </div>
                <div class="text-right">
                    <small class="d-block font-weight-bold opacity-50 mb-2">REPORTED AT: {{ date('H:i', strtotime($alert->jam_lapor)) }}</small>
                    <button class="btn btn-dark btn-sm rounded-pill px-4">INTERVENE</button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- STATS --}}
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="stat-card border-left-primary">
                <small class="text-muted font-weight-bold uppercase tracking-widest d-block mb-2">Achievement Rate</small>
                <div class="d-flex align-items-end justify-content-between">
                    <div class="stat-value text-primary">{{ $achievementRate }}%</div>
                    <div class="text-right small font-weight-bold text-muted">vs Target</div>
                </div>
                <div class="progress mt-3" style="height: 8px; border-radius: 10px;">
                    <div class="progress-bar bg-primary" style="width: {{ $achievementRate }}%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <small class="text-muted font-weight-bold uppercase tracking-widest d-block mb-2">Total Good Output</small>
                <div class="stat-value">{{ number_format($totalActual) }} <small style="font-size: 10px;">PCS</small></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <small class="text-muted font-weight-bold uppercase tracking-widest d-block mb-2">Remaining Target</small>
                <div class="stat-value text-danger">{{ number_format($totalPlan - $totalActual) }}</div>
            </div>
        </div>
    </div>

    {{-- CHART & TABLE --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 24px;">
                <h6 class="font-weight-bold mb-4 uppercase small text-muted">Welding Production Load Per Part</h6>
                <div id="weldingChart"></div>
            </div>
        </div>
    </div>
</div>

<script>
    var options = {
        series: [{ name: 'Target', data: @json($chartTargets) }, { name: 'Actual', data: @json($chartActuals) }],
        chart: { type: 'bar', height: 350, toolbar: { show: false } },
        colors: ['#e2e8f0', '#4361ee'],
        plotOptions: { bar: { borderRadius: 10, columnWidth: '40%' } },
        xaxis: { categories: @json($chartLabels) },
    };
    new ApexCharts(document.querySelector("#weldingChart"), options).render();
</script>
@endsection