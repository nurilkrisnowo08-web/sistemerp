<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PPICController extends Controller
{
    /**
     * 1. DASHBOARD UTAMA (STAMPING)
     */
    public function index(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $today = date('Y-m-d');
        
        $alerts = DB::table('produksi_batches')
            ->leftJoin('line', 'produksi_batches.mesin_id', '=', 'line.id')
            ->where('produksi_batches.status', 'PROBLEM')
            ->select('produksi_batches.id', 'produksi_batches.no_produksi', 'produksi_batches.material_code', 'line.kode_Line', 'produksi_batches.keterangan', 'produksi_batches.updated_at')
            ->get();

        $plans = DB::table('production_plans')->where('plan_date', $date)->get();
        $statusCount = ['waiting' => 0, 'running' => 0, 'completed' => 0, 'shortage' => 0];
        $chartLabels = []; $chartTargets = []; $chartActuals = [];

        foreach($plans as $p) {
            $targetPerPart = ($p->s1_plan_reg + $p->s1_plan_ot + $p->s2_plan_reg + $p->s2_plan_ot);
            $actualPerPart = DB::table('production_actuals')
                ->where('part_no', $p->part_no)
                ->whereDate('created_at', $date)
                ->where('line_code', 'NOT LIKE', 'W-%')
                ->where('line_code', '!=', 'WELDING AREA')
                ->sum('qty_ok');
            
            $p->actual_qty = (int)$actualPerPart;
            $p->plan_qty = (int)$targetPerPart;
            $chartLabels[] = $p->part_no;
            $chartTargets[] = (int)$targetPerPart;
            $chartActuals[] = (int)$actualPerPart;

            if($targetPerPart > 0 && $actualPerPart >= $targetPerPart) { $statusCount['completed']++; }
            elseif ($date < $today && $actualPerPart < $targetPerPart) { $statusCount['shortage']++; }
            elseif ($actualPerPart > 0) { $statusCount['running']++; }
            else { $statusCount['waiting']++; }
        }

        $totalPlan = $plans->sum('plan_qty') ?: 0;
        $totalActual = $plans->sum('actual_qty') ?: 0;
        $achievementRate = $totalPlan > 0 ? round(($totalActual / $totalPlan) * 100, 1) : 0;

        $dailyLabels = []; $dailyOk = []; $dailyNg = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $dailyLabels[] = date('d M', strtotime($d));
            $dailyOk[] = DB::table('production_actuals')->whereDate('created_at', $d)->where('line_code', 'NOT LIKE', 'W-%')->sum('qty_ok');
            $dailyNg[] = DB::table('production_actuals')->whereDate('created_at', $d)->where('line_code', 'NOT LIKE', 'W-%')->sum('qty_ng');
        }

        return view('PPIC.ppic_planning', compact('plans', 'statusCount', 'achievementRate', 'date', 'totalPlan', 'totalActual', 'chartLabels', 'chartTargets', 'chartActuals', 'dailyLabels', 'dailyOk', 'dailyNg', 'alerts'));
    }

    /**
     * 2. DAILY MPS (STAMPING)
     */
    public function mpsIndex(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $shiftParam = $request->shift ?? 'S1'; 
        $query = DB::table('production_plans')->where('plan_date', $date);
        if ($shiftParam == 'S1') { $query->where(DB::raw('s1_plan_reg + s1_plan_ot'), '>', 0); } 
        else { $query->where(DB::raw('s2_plan_reg + s2_plan_ot'), '>', 0); }

        $plans = $query->orderBy('id', 'asc')->get();
        $lineFinishTime = []; 
        $defaultStart = ($shiftParam == 'S1') ? "07:30" : "19:30";

        foreach($plans as $plan) {
            $dbShiftName = ($shiftParam == 'S1') ? 'Pagi' : 'Malam';
            $actualData = DB::table('production_actuals')->where('part_no', $plan->part_no)->where('shift', $dbShiftName)->whereDate('created_at', $date)->sum('qty_ok');
            $plan->total_actual = (int)$actualData;
            $plan->total_target = ($shiftParam == 'S1') ? ($plan->s1_plan_reg + $plan->s1_plan_ot) : ($plan->s2_plan_reg + $plan->s2_plan_ot);
            
            $dandoryH = ($plan->dandory_time ?? 0) / 60;
            $duration = ($plan->cap_per_hour > 0 && $plan->total_target > 0) ? ($plan->total_target / $plan->cap_per_hour) + $dandoryH : 0;
            $startTime = $lineFinishTime[$plan->line_code] ?? $defaultStart;
            $plan->start_time = $startTime;
            $plan->ahir_time = date('H:i', strtotime($startTime . " + " . round($duration * 60) . " minutes"));
            $lineFinishTime[$plan->line_code] = $plan->ahir_time; 
            $plan->balance = $plan->total_target - $plan->total_actual;
        }

        $availableLines = DB::table('line')->get();
        $availableCustomers = DB::table('customers')->get();
        return view('PPIC.mps_index', compact('plans', 'date', 'availableLines', 'availableCustomers'))->with('shift', $shiftParam);
    }

    public function mpsStore(Request $request)
    {
        DB::table('production_plans')->updateOrInsert(
            ['plan_date' => $request->plan_date, 'part_no' => $request->part_no],
            ['customer_code' => $request->customer_code, 'line_code' => $request->line_code, 'manpower' => $request->manpower ?? 8, 'process_qty' => $request->process_qty ?? 4, 'qty_lot' => $request->qty_lot ?? 200, 'cap_per_hour' => $request->cap_per_hour ?? 320, 's1_plan_reg' => $request->s1_plan_reg ?? 0, 's1_plan_ot' => $request->s1_plan_ot ?? 0, 's2_plan_reg' => $request->s2_plan_reg ?? 0, 's2_plan_ot' => $request->s2_plan_ot ?? 0, 'dandory_time' => $request->dandory_time ?? 15, 'updated_at' => now()]
        );
        return redirect()->back()->with('success', 'Master Schedule Updated!');
    }

    /**
     * ✨ 3. QUALITY HUB KHUSUS STAMPING (PISAH TOTAL)
     */
    public function qualityHub(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');

        // Sum khusus Stamping
        $sumStamping = DB::table('production_actuals')
            ->whereDate('created_at', $date)
            ->where('line_code', 'NOT LIKE', 'W-%')
            ->where('line_code', '!=', 'WELDING AREA')
            ->select(DB::raw('SUM(qty_ok) as total_ok'), DB::raw('SUM(qty_ng) as total_ng'))->first();

        // NG khusus Stamping (Hanya ambil NG dari tabel production_ng_logs)
        $ngStamping = DB::table('production_ng_logs')
            ->select('ng_type', DB::raw('SUM(qty) as total'))
            ->whereDate('created_at', $date)
            ->whereNotIn('ng_type', function($q) { $q->select('ng_name')->from('master_ngs')->where('category', 'WELDING'); })
            ->groupBy('ng_type')->orderBy('total', 'DESC')->get();

        // Detail list Stamping
        $detailStamping = DB::table('production_actuals')
            ->whereDate('created_at', $date)
            ->where('line_code', 'NOT LIKE', 'W-%')
            ->where('line_code', '!=', 'WELDING AREA')
            ->get();

        foreach($detailStamping as $d) {
            $d->batches = DB::table('produksi_batches')
                ->leftJoin('line', 'produksi_batches.mesin_id', '=', 'line.id')
                ->where('material_code', $d->part_no)
                ->where('shift', $d->shift)
                ->whereDate('produksi_batches.created_at', $date)
                ->select('no_produksi', 'qty_ambil_pcs', 'qty_hasil_ok', 'qty_hasil_ng', 'kode_Line')
                ->get();
        }

        return view('PPIC.quality_hub', compact('date', 'sumStamping', 'ngStamping', 'detailStamping'));
    }

    /**
     * 4. WELDING INTELLIGENCE DASHBOARD
     */
    public function weldingIndex(Request $request)
    {
        $start_date = $request->start_date ?? date('Y-m-d');
        $end_date = $request->end_date ?? date('Y-m-d');

        $alerts = DB::table('welding_batches')
            ->leftJoin('line_welding', 'welding_batches.line_id', '=', 'line_welding.id')
            ->where('welding_batches.status', 'PROBLEM')
            ->select('welding_batches.*', 'line_welding.kode_line', 'welding_batches.updated_at as jam_lapor')
            ->get();

        $plans = DB::table('welding_plans')->whereBetween('plan_date', [$start_date, $end_date])->get();
        $chartLabels = []; $chartTargets = []; $chartActuals = [];
        
        foreach($plans as $p) {
            $actual = DB::table('welding_actuals')->where('part_no', $p->part_no)->whereBetween(DB::raw('DATE(created_at)'), [$start_date, $end_date])->sum('qty_ok');
            $p->actual_qty = (int)$actual;
            $p->plan_qty = (int)($p->s1_plan_reg + $p->s1_plan_ot + $p->s2_plan_reg + $p->s2_plan_ot);
            $chartLabels[] = $p->part_no;
            $chartTargets[] = $p->plan_qty;
            $chartActuals[] = $p->actual_qty;
        }

        $totalPlan = array_sum($chartTargets);
        $totalActual = array_sum($chartActuals);
        $totalNg = DB::table('welding_actuals')->whereBetween(DB::raw('DATE(created_at)'), [$start_date, $end_date])->sum('qty_ng');
        $achievementRate = $totalPlan > 0 ? round(($totalActual / $totalPlan) * 100, 1) : 0;

        $dailyLabels = []; $dailyOk = []; $dailyNg = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("$end_date -$i days"));
            $dailyLabels[] = date('d M', strtotime($d));
            $dailyOk[] = DB::table('welding_actuals')->whereDate('created_at', $d)->sum('qty_ok');
            $dailyNg[] = DB::table('welding_actuals')->whereDate('created_at', $d)->sum('qty_ng');
        }

        return view('PPIC.welding_planning', compact('plans', 'achievementRate', 'start_date', 'end_date', 'totalPlan', 'totalActual', 'totalNg', 'alerts', 'chartLabels', 'chartTargets', 'chartActuals', 'dailyLabels', 'dailyOk', 'dailyNg'));
    }

    /**
     * 5. WELDING MPS (WITH PART NAMES & CAPACITY)
     */
   public function weldingMps(Request $request)
{
    $date = $request->date ?? date('Y-m-d');
    $shiftParam = $request->shift ?? 'S1'; 

    // Ambil data Plan + Join Parts (Otomatis ngebaca part name)
    $plans = DB::table('welding_plans')
        ->leftJoin('parts', 'welding_plans.part_no', '=', 'parts.part_no')
        ->where('welding_plans.plan_date', $date)
        ->select('welding_plans.*', 'parts.part_name')
        ->orderBy('welding_plans.id', 'asc')
        ->get();

    foreach($plans as $plan) {
        // ✨ AUTO-READ: Mengambil data Actual dari welding_actuals secara real-time
        $actual = DB::table('welding_actuals')
            ->where('part_no', $plan->part_no)
            ->whereDate('created_at', $date)
            ->sum('qty_ok');
            
        $plan->total_actual = (int)$actual;
        
        // Pilih target berdasarkan shift yang difilter
        $plan->total_target = ($shiftParam == 'S1') ? ($plan->s1_plan_reg + $plan->s1_plan_ot) : ($plan->s2_plan_reg + $plan->s2_plan_ot);
        
        // Hitung selisih
        $plan->balance = $plan->total_target - $plan->total_actual;
    }

    $availableLines = DB::table('line_welding')->get();
    $availableParts = DB::table('parts')->where('next_process', 'WELDING')->get();

    return view('PPIC.welding_mps', compact('plans', 'date', 'availableLines', 'availableParts'))->with('shift', $shiftParam);
}

    public function weldingMpsStore(Request $request)
    {
        $partData = DB::table('parts')->where('part_no', $request->part_no)->first();
        DB::table('welding_plans')->updateOrInsert(
            ['plan_date' => $request->plan_date, 'part_no' => $request->part_no],
            ['customer_code' => $partData->customer_code ?? 'UNK', 'line_code' => $request->line_code, 'manpower' => $request->manpower ?? 1, 'cap_per_hour' => $request->cap_per_hour ?? 0, 's1_plan_reg' => $request->s1_plan_reg ?? 0, 's1_plan_ot' => $request->s1_plan_ot ?? 0, 's2_plan_reg' => $request->s2_plan_reg ?? 0, 's2_plan_ot' => $request->s2_plan_ot ?? 0, 'dandory_time' => 15, 'process_qty' => 1, 'qty_lot' => 1, 'updated_at' => now()]
        );
        return redirect()->back()->with('success', 'Welding Plan Authorized!');
    }

    /**
     * ✨ 6. QUALITY HUB KHUSUS WELDING (TOTAL PISAH)
     */
    public function weldingQualityHub(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');

        // Sum khusus Welding (Baca tabel welding_actuals)
        $sumWelding = DB::table('welding_actuals')
            ->whereDate('created_at', $date)
            ->select(DB::raw('SUM(qty_ok) as total_ok'), DB::raw('SUM(qty_ng) as total_ng'))->first();

        // NG khusus Welding (Baca tabel welding_ng_logs)
        $ngWelding = DB::table('welding_ng_logs')
            ->select('ng_type', DB::raw('SUM(qty) as total'))
            ->whereDate('created_at', $date)
            ->groupBy('ng_type')->orderBy('total', 'DESC')->get();

        // Detail list Welding
        $detailWelding = DB::table('welding_actuals')->whereDate('created_at', $date)->get();

        foreach($detailWelding as $d) {
            $d->batches = DB::table('welding_batches')
                ->leftJoin('line_welding', 'welding_batches.line_id', '=', 'line_welding.id')
                ->where('part_no', $d->part_no)
                ->whereDate('welding_batches.created_at', $date)
                ->select('no_produksi_stamping as no_produksi', 'qty_masuk', 'qty_ok', 'qty_ng', 'kode_line')
                ->get();
        }

        return view('PPIC.welding_quality_hub', compact('date', 'sumWelding', 'ngWelding', 'detailWelding'));
    }

    // --- RECOVERY FUNCTIONS (JANGAN DIUBAH) ---
    public function resumeBatch($id) { DB::table('produksi_batches')->where('id', $id)->update(['status' => 'PROSES', 'updated_at' => now()]); return redirect()->back()->with('success', 'Batch resumed.'); }
    public function closeBatch($id) { 
        DB::beginTransaction(); 
        try { 
            $batch = DB::table('produksi_batches')->where('id', $id)->first(); 
            $sisa = (int)$batch->qty_ambil_pcs - ((int)$batch->qty_hasil_ok + (int)$batch->qty_hasil_ng); 
            if ($sisa > 0) { DB::table('rm_stocks')->where('id', $batch->rm_stock_id)->increment('stock_pcs', $sisa); } 
            DB::table('produksi_batches')->where('id', $id)->update(['status' => 'COMPLETED', 'qty_return_warehouse' => $sisa, 'updated_at' => now()]); 
            $this->syncToActual($id); 
            DB::commit(); return redirect()->back()->with('success', "Batch closed."); 
        } catch (\Exception $e) { DB::rollBack(); return redirect()->back()->with('error', $e->getMessage()); } 
    }
    public function syncToActual($batchId) {
        $batch = DB::table('produksi_batches')->where('id', $batchId)->first(); if (!$batch) return;
        $lineCode = DB::table('line')->where('id', $batch->mesin_id)->value('kode_Line') ?? 'UNKNOWN';
        $actual = DB::table('production_actuals')->where('part_no', $batch->material_code)->where('shift', $batch->shift)->whereDate('created_at', date('Y-m-d', strtotime($batch->created_at)))->first();
        if ($actual) { DB::table('production_actuals')->where('id', $actual->id)->update(['qty_ok' => $actual->qty_ok + $batch->qty_hasil_ok, 'qty_ng' => $actual->qty_ng + $batch->qty_hasil_ng, 'updated_at' => now()]); } 
        else { DB::table('production_actuals')->insert(['part_no' => $batch->material_code, 'line_code' => $lineCode, 'shift' => $batch->shift, 'qty_ok' => $batch->qty_hasil_ok, 'qty_ng' => $batch->qty_hasil_ng, 'created_at' => $batch->created_at, 'updated_at' => now()]); }
    }
}