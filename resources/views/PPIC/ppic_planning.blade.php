@extends('layout.admin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">

<style>
    :root { --ppic-steel: #4e73df; --ppic-bg: #f8fafc; --ppic-dark: #0f172a; }
    .main-terminal { background-color: var(--ppic-bg); min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }
    .card-kpi { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.01); transition: 0.3s; }
    
    /* Audit Box Dynamic */
    .audit-box { padding: 15px 25px; border-radius: 15px; display: flex; align-items: center; gap: 15px; font-weight: 800; text-transform: uppercase; }
    .audit-good { background: rgba(28, 200, 138, 0.1); color: #1cc88a; border: 2px solid #1cc88a; }
    .audit-bad { background: rgba(231, 74, 59, 0.1); color: #e74a3b; border: 2px solid #e74a3b; }
    .audit-none { background: #f1f5f9; color: #94a3b8; border: 2px solid #cbd5e1; }

    .btn-filter { border-radius: 10px; font-weight: 700; background: white; border: 2px solid var(--ppic-steel); color: var(--ppic-steel); }
</style>

<div class="main-terminal">
    <div class="d-md-flex justify-content-between align-items-center mb-5">
        <div>
            <h3 class="font-weight-extrabold text-dark mb-1" style="letter-spacing: -1.5px;">PRODUCTION_CORE_MONITOR</h3>
            <form action="{{ route('ppic.index') }}" method="GET" class="d-flex align-items-center">
                <input type="date" name="date" class="form-control form-control-sm mr-2 shadow-sm" value="{{ $date }}" onchange="this.form.submit()" style="width: 200px; border-radius: 8px;">
                <span class="badge badge-dark py-1 px-3 rounded-pill">SYNC_ACTIVE</span>
            </form>
        </div>

        {{-- ✨ DAILY AUDIT TER-OPTIMASI ✨ --}}
        @php
            $auditClass = 'audit-none';
            $auditText = 'RESULT: NO PLAN';
            $auditIcon = 'fa-info-circle';
            
            if($totalPlan > 0) {
                if($achievementRate >= 85) {
                    $auditClass = 'audit-good';
                    $auditText = 'RESULT: BAGUS';
                    $auditIcon = 'fa-check-circle';
                } else {
                    $auditClass = 'audit-bad';
                    $auditText = 'RESULT: JELEK';
                    $auditIcon = 'fa-exclamation-triangle';
                }
            }
        @endphp

        <div class="audit-box {{ $auditClass }} mt-3 mt-md-0">
            <i class="fas {{ $auditIcon }} fa-2x"></i>
            <div>
                <div style="font-size: 10px; opacity: 0.8;">DAILY PERFORMANCE AUDIT ({{ $date }})</div>
                <div style="font-size: 18px;">{{ $auditText }}</div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Kiri: Target vs Actual --}}
        <div class="col-lg-8 mb-4 text-center">
            <div class="card-kpi p-4">
                <h6 class="text-left font-weight-bold mb-4">PRODUCTION VOLUME ACHIEVEMENT</h6>
                @if($totalPlan > 0)
                    <div style="height: 300px;"><canvas id="compareBar"></canvas></div>
                @else
                    <div class="py-5">
                        <i class="fas fa-folder-open fa-4x text-light mb-3"></i>
                        <h5 class="text-muted">No Data Plan for this date</h5>
                    </div>
                @endif
            </div>
        </div>

        {{-- Kanan: Donut --}}
        <div class="col-lg-4 mb-4">
            <div class="card-kpi p-4">
                <h6 class="text-left font-weight-bold mb-4">JOB STATUS</h6>
                <div style="height: 220px;"><canvas id="statusDonut"></canvas></div>
                <hr>
                <div class="row text-center font-weight-bold">
                    <div class="col-4"><small>WAIT</small><br>{{ $statusCount['waiting'] }}</div>
                    <div class="col-4 text-primary"><small>RUN</small><br>{{ $statusCount['running'] }}</div>
                    <div class="col-4 text-success"><small>DONE</small><br>{{ $statusCount['completed'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card-kpi p-4">
                <h6 class="font-weight-bold"><i class="fas fa-chart-line mr-2"></i> PRODUCTION STABILITY TREND (ACTUAL OUTPUT)</h6>
                <div style="height: 280px;"><canvas id="stabilityLineChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    Chart.defaults.font.family = "'Roboto Mono', monospace";
    
    // 1. Donut
    new Chart(document.getElementById('statusDonut'), {
        type: 'doughnut',
        data: {
            labels: ['Waiting', 'Running', 'Completed'],
            datasets: [{
                data: [{{ $statusCount['waiting'] }}, {{ $statusCount['running'] }}, {{ $statusCount['completed'] }}],
                backgroundColor: ['#e2e8f0', '#4e73df', '#1cc88a'],
                borderWidth: 0
            }]
        },
        options: { cutout: '80%', maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 2. Bar Compare
    new Chart(document.getElementById('compareBar'), {
        type: 'bar',
        data: {
            labels: ['DAILY TARGET', 'DAILY ACTUAL'],
            datasets: [{
                data: [{{ $totalPlan }}, {{ $plans->sum('actual_qty') }}],
                backgroundColor: ['#f1f5f9', '#4e73df'],
                borderRadius: 10
            }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 3. ✨ REAL HISTORY TREND CHART ✨
    new Chart(document.getElementById('stabilityLineChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyData['labels']) !!},
            datasets: [{
                label: 'Actual Pcs',
                data: {!! json_encode($monthlyData['actual']) !!},
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 4, fill: true, tension: 0.3,
                pointRadius: 5, pointBackgroundColor: '#fff'
            }]
        },
        options: { 
            maintainAspectRatio: false, 
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } } }
        }
    });
});
</script>
@endsection
@endsection