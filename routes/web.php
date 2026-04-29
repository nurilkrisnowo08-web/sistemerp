<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RmController;
use App\Http\Controllers\{
    AuthController, DashboardController, PurchaseOrderController, 
    DeliveryController, FgController, FgControlController, 
    WeldingStockController, StampingController, ProductionController, 
    CustomerController, PartController, DailyFgController, ProduksiController, 
    LineController, PPICController, ReportController, QualityGateController,WeldingMasterController
};

/*
|--------------------------------------------------------------------------
| 1. TOOLS & CACHE CLEANER
|--------------------------------------------------------------------------
*/
Route::get('/bersihkan-sistem', function() {
    \Artisan::call('route:clear');
    \Artisan::call('view:clear');
    \Artisan::call('config:clear');
    return "Sistem Berhasil Disinkronkan! Silakan kembali ke Dashboard.";
});

/*
|--------------------------------------------------------------------------
| 2. GUEST AREA (BELUM LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect('/login'));
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::get('/forgot', [AuthController::class, 'showForgot'])->name('forgot');
    Route::post('/forgot', [AuthController::class, 'resetPassword'])->name('forgot.post');
});

/*
|--------------------------------------------------------------------------
| 3. JALUR AJAX & SINKRONISASI (SEMUA ROLE - AGAR DROPDOWN TIDAK MACET)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');

    // JALUR SINKRONISASI DATA (FIX DROPDOWN SAI/TMMIN)
    Route::get('/get-parts/{customer_code}', [PurchaseOrderController::class, 'getParts'])->name('po.get-parts');
    Route::get('/po/get-parts/{customer_code}', [PurchaseOrderController::class, 'getPartsByCustomer']);
    Route::get('/get-parts-and-specs/{customer}', [RmController::class, 'getPartsAndSpecs'])->name('rm.get_parts_specs');
    Route::get('/get-rm-data/{customer}', [RmController::class, 'getPartsAndSpecs']);
    Route::get('/produksi/get-specs/{customer}', [ProduksiController::class, 'getSpecsByCustomer']);
    Route::get('/produksi/get-parts-by-spec', [ProduksiController::class, 'getPartsBySpec']);
    Route::get('/produksi/get-part-detail/{code}', [ProduksiController::class, 'getPartDetail']);
    Route::get('/produksi/get-bundles/{code}', [ProduksiController::class, 'getBundlesByPart']);
    Route::get('/ppic-api-data', [PPICController::class, 'apiData'])->name('ppic.api');
});

/*
|--------------------------------------------------------------------------
| 4. KAMAR OPERASIONAL PRODUKSI (PRODUKSI, STAFF, KEPALA)
|--------------------------------------------------------------------------
| Role Produksi bebas Input & Proses di areanya sendiri (Lantai Produksi).
*/
Route::middleware(['auth', 'role:produksi,staff_ppic,kepala_ppic'])->group(function () {
    // 🏗️ AREA WELDING WIP
    Route::get('/inventory-welding', [WeldingStockController::class, 'index'])->name('welding.index');
    Route::post('/welding/deploy', [WeldingStockController::class, 'deployWelding'])->name('welding.deploy');
    Route::put('/inventory-welding/start/{id}', [WeldingStockController::class, 'startWelding'])->name('welding.start');
    Route::put('/inventory-welding/finish/{id}', [WeldingStockController::class, 'finishWelding'])->name('welding.finish');
    Route::get('/welding-vault', [WeldingStockController::class, 'history'])->name('welding.history');
    Route::get('/welding-history-prod', [WeldingStockController::class, 'historyWelding'])->name('welding.history.weldig');
    Route::get('/inventory-welding/daily-recap', [WeldingStockController::class, 'dailyRecap'])->name('welding.daily_recap');
    Route::get('/inventory-welding/monthly-recap', [WeldingStockController::class, 'recap'])->name('welding.recap');

    // 🏭 AREA LIVE MONITORING & STAMPING
    Route::get('/monitoring-produksi', [ProduksiController::class, 'index'])->name('produksi.index');
    Route::post('/monitoring-produksi/store', [ProduksiController::class, 'store'])->name('produksi.store');
    Route::put('/monitoring-produksi/update-result/{id}', [ProduksiController::class, 'updateResult'])->name('produksi.update_result');
    Route::get('/stamping-production', [StampingController::class, 'index'])->name('stamping.index');
    Route::post('/stamping-production/store', [StampingController::class, 'store'])->name('stamping.store');
    Route::get('/produksi/history', [ProduksiController::class, 'history'])->name('produksi.history');
    Route::get('/ng-report', [ProduksiController::class, 'report'])->name('produksi.report');
    // Tambahkan baris ini di web.php
    Route::put('/produksi/report-problem/{id}', [App\Http\Controllers\ProduksiController::class, 'reportProblem'])->name('produksi.report_problem');
    // PPIC menghidupkan kembali produksi (Jika perbaikan < 2 jam)
    Route::put('/ppic/resume-batch/{id}', [App\Http\Controllers\PPICController::class, 'resumeBatch'])->name('ppic.resume_batch');

    // PPIC menutup produksi (Jika perbaikan lama / Dies rusak parah)
    Route::put('/ppic/close-batch/{id}', [App\Http\Controllers\PPICController::class, 'closeBatch'])->name('ppic.close_batch');

    // 🛡️ AREA QUALITY GATE
    Route::get('/quality-control-room', [QualityGateController::class, 'index'])->name('quality.index');
    Route::post('/quality-control-approve/{type}/{id}', [QualityGateController::class, 'approve'])->name('quality.approve');
    Route::get('/quality/history', [QualityGateController::class, 'history'])->name('quality.history');

    // 📦 MONITORING GUDANG (HANYA LIHAT)
    Route::get('/stock-fg', [FgController::class, 'index'])->name('fg.index');
    Route::get('/rm-inventory', [RmController::class, 'storeIndex'])->name('rm.store');
    Route::get('/rm-monitoring', [RmController::class, 'storeIndex'])->name('rm.index');
    Route::get('/rm/mutation', [RmController::class, 'rmMutation'])->name('rm.mutation');
});

/*
|--------------------------------------------------------------------------
| 5. KAMAR ADMINISTRASI & LOGISTIK (STAFF PPIC & KEPALA PPIC)
|--------------------------------------------------------------------------
| Area ini tertutup untuk Produksi (PO, Delivery, Laporan Master).
*/
Route::middleware(['auth', 'role:staff_ppic,kepala_ppic'])->group(function () {
    // --- PURCHASE ORDER & DELIVERY ---
    Route::get('/po-customer', [PurchaseOrderController::class, 'index'])->name('po-customer.index');
    Route::post('/po-customer/store', [PurchaseOrderController::class, 'store'])->name('po.store');
    Route::get('/po/history', [PurchaseOrderController::class, 'history'])->name('po.history');
    
    Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
    Route::get('/delivery/history', [DeliveryController::class, 'history'])->name('delivery.history');
    Route::post('/delivery/store', [DeliveryController::class, 'store'])->name('delivery.store');
    Route::get('/delivery/create/{po_number}', [DeliveryController::class, 'create'])->name('delivery.create')->where('po_number', '.*');
    Route::get('/delivery/print/{no_sj}', [DeliveryController::class, 'print'])->name('delivery.print')->where('no_sj', '.*');
    Route::get('/delivery/print-rekap-po/{po_number}', [DeliveryController::class, 'printRekapPO'])->name('delivery.print-rekap-po')->where('po_number', '.*');

    // --- RAW MATERIAL & SUPPLIER ---
    Route::get('/rm/po-supplier', [RmController::class, 'poSupplierIndex'])->name('rm.po_supplier_index');
    Route::get('/rm/po-supplier-node', [RmController::class, 'poSupplierIndex'])->name('rm.po_supplier');
    Route::get('/rm/po-history', [RmController::class, 'poSupplierHistory'])->name('rm.po_supplier_history');
    Route::post('/rm/po-supplier/store', [RmController::class, 'poSupplierStore'])->name('rm.po_supplier_store');
    Route::post('/rm/po-arrival/{id}', [RmController::class, 'poArrivalStore'])->name('rm.po_arrival_store');
    Route::get('/rm/po-print/{id}', [RmController::class, 'printPO'])->name('rm.print_po');
    
    Route::post('/rm/store-batch', [RmController::class, 'storeBatch'])->name('rm.store_batch');
    Route::post('/rm-incoming', [RmController::class, 'incomingStore'])->name('rm.incoming');
    Route::post('/rm-production-out', [RmController::class, 'productionStore'])->name('rm.production_out');

    // --- FINISH GOOD & REPORTS ---
    Route::get('/stock-fg/create', [FgController::class, 'create'])->name('fg.create');
    Route::post('/stock-fg/store', [FgController::class, 'store'])->name('fg.store');
    Route::post('/fg/send-to-po', [FgController::class, 'sendToPo'])->name('fg.send_to_po');
    Route::get('/finished-goods/recap', [FgController::class, 'monthlyRecap'])->name('fg.recap');
    Route::get('/finished-goods/print', [FgController::class, 'printRecap'])->name('fg.print');
    
    Route::get('/rm-log-print', [RmController::class, 'recapLogPrint'])->name('rm.log_print');
    Route::get('/rm-recap-print', [RmController::class, 'recapPrint'])->name('rm.recap_print');
    Route::get('/ppic-planning', [PPICController::class, 'index'])->name('ppic.index');
    });

/*
|--------------------------------------------------------------------------
| 6. KAMAR KEPALA PPIC (ADMIN / FULL ACCESS)
|--------------------------------------------------------------------------
| Akses: EDIT & HAPUS data.
*/
Route::middleware(['auth', 'role:kepala_ppic'])->group(function () {
    // PO Adjustments
    Route::put('/po/update-qty', [PurchaseOrderController::class, 'updateQty'])->name('po.update_qty');
    Route::put('/po/update-header/{po_number}', [PurchaseOrderController::class, 'updateHeader'])->name('po.update')->where('po_number', '.*');
    Route::get('/po/edit/{id}', [PurchaseOrderController::class, 'edit'])->name('po.edit');
    Route::delete('/po/delete/{id}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');

    // RM Adjustments
    Route::post('/rm/store-master', [RmController::class, 'storeMasterSpec'])->name('rm.store_master');
    Route::put('/rm/store/update/{id}', [RmController::class, 'update'])->name('rm.update');
    Route::put('/rm/unit-update/{id}', [RmController::class, 'updateUnit'])->name('rm.unit_update');
    Route::post('/rm/update-alias', [RmController::class, 'updateAlias'])->name('rm.update_alias');
    Route::post('/rm/assign-part', [RmController::class, 'assignPart'])->name('rm.assign_part');
    Route::delete('/rm/delete/{id}', [RmController::class, 'destroy'])->name('rm.destroy');
    Route::delete('/rm/remove-part/{id}', [RmController::class, 'removePartFromUnit'])->name('rm.remove_part_from_unit');
     // Rute untuk RM Hub
    Route::get('/get-parts-and-specs/{customer}', [RmController::class, 'getPartsAndSpecs'])->name('rm.get_parts');
    Route::post('/rm/update-unit-pcs', [RmController::class, 'updateUnitPcs'])->name('rm.update_unit_pcs');
    Route::post('/rm/assign-part', [RmController::class, 'assignPart'])->name('rm.assign_part_to_unit');
    // Rute pendaftaran Spek Master (Yang bikin error merah tadi)
    Route::post('/rm/store-master-spec', [RmController::class, 'storeMasterSpec'])->name('rm.store_master_spec');

    // Rute pendaftaran Coil Baru
    Route::post('/rm/store-batch', [RmController::class, 'storeBatch'])->name('rm.store_batch');

    // Rute AJAX untuk ambil data Spek & Part otomatis
    Route::get('/get-parts-and-specs/{customer}', [RmController::class, 'getPartsAndSpecs'])->name('rm.get_parts');

    // FG Adjustments
    Route::get('/stock-fg/{id}/edit', [FgController::class, 'edit'])->name('fg.edit');
    Route::put('/stock-fg/{id}', [FgController::class, 'update'])->name('fg.update');
    Route::delete('/fg/delete/{id}', [FgController::class, 'destroy'])->name('fg.destroy');

    // System Resources
    Route::resource('customers', CustomerController::class);
    Route::resource('parts', PartController::class);
    Route::resource('line', LineController::class);
    Route::resource('fg-daily', DailyFgController::class);
    
    // Advanced Ops
    Route::delete('/quality-control-delete/{type}/{id}', [QualityGateController::class, 'destroy'])->name('quality.destroy');
    Route::delete('/production/{id}', [ProductionController::class, 'destroy'])->name('production.destroy');
    Route::post('/produksi/resolve/{id}', [ProduksiController::class, 'resolveInterruption'])->name('produksi.resolve_interruption');
    Route::post('/produksi/return/{id}', [ProduksiController::class, 'returnToRM'])->name('produksi.return');
    Route::put('/inventory-welding/update-master', [WeldingStockController::class, 'updateMaster'])->name('welding.update_master');
    //planning
    Route::prefix('planning')->group(function () {
    Route::get('/mps', [PPICController::class, 'mpsIndex'])->name('ppic.mps.index');
    Route::post('/mps/store', [PPICController::class, 'mpsStore'])->name('ppic.mps.store');
    Route::get('/ppic/monthly-matrix', [PPICController::class, 'monthlyMatrix'])->name('ppic.monthly.matrix');
    Route::post('/ppic/monthly-matrix/save', [PPICController::class, 'saveMatrixAjax'])->name('ppic.monthly.ajax_save');

    // Halaman Dashboard Utama (Intelligence Hub) dengan Grafik OK vs NG
Route::get('/ppic-planning', [App\Http\Controllers\PPICController::class, 'index'])->name('ppic.index');

// Halaman Detail Quality (OK vs NG Breakdown)
Route::get('/ppic/quality-hub', [App\Http\Controllers\PPICController::class, 'qualityHub'])->name('ppic.quality.hub');
 // --- KHUSUS MANAGEMENT LINE WELDING ---
    Route::prefix('welding-master')->group(function () {
    // Tampilan Daftar Line Welding
    Route::get('/lines', [WeldingMasterController::class, 'lineIndex'])->name('welding.master.lines');
    
    // Proses Tambah Line Baru
    Route::post('/lines/store', [WeldingMasterController::class, 'lineStore'])->name('welding.master.line_store');
    
    // Proses Hapus Line
    Route::delete('/lines/destroy/{id}', [WeldingMasterController::class, 'lineDestroy'])->name('welding.master.line_destroy');
    Route::get('/ng', [WeldingMasterController::class, 'ngIndex'])->name('welding.master.ng');
    Route::post('/ng/store', [WeldingMasterController::class, 'ngStore'])->name('welding.master.ng_store');
    // --- PPIC WELDING COMMAND CENTER ---
    Route::prefix('ppic-welding')->group(function () {
        // 1. Dashboard khusus Welding (Target vs Actual Las)
    Route::get('/intelligence', [PPICController::class, 'weldingIndex'])->name('ppic.welding.index');
        
        // 2. Master Schedule khusus Welding (Jadwal Robot/Spot Las)
    Route::get('/mps', [PPICController::class, 'weldingMps'])->name('ppic.welding.mps');
        
        // 3. Quality Hub khusus Welding (Pantauan Blowhole, Undercut, dll)
    Route::get('/quality', [PPICController::class, 'weldingQualityHub'])->name('ppic.welding.quality');
    Route::prefix('ppic-welding')->group(function () {
    
    // 1. Dashboard Utama Welding (Target vs Actual)
    Route::get('/intelligence', [PPICController::class, 'weldingIndex'])->name('ppic.welding.index');
    
    // 2. Master Schedule khusus Welding (MPS)
    Route::get('/mps', [PPICController::class, 'weldingMps'])->name('ppic.welding.mps');
    Route::post('/mps/store', [PPICController::class, 'weldingMpsStore'])->name('ppic.welding.mps_store');
    
    // 3. Quality Hub khusus Welding (Pantauan NG Las)
    Route::get('/quality', [PPICController::class, 'weldingQualityHub'])->name('ppic.welding.quality');
});

// --- RE-SYNC TERMINAL OPERATOR WELDING ---
// Pastikan rute finish welding sudah benar untuk menampung rincian NG
Route::prefix('welding')->group(function () {
    Route::get('/', [WeldingStockController::class, 'index'])->name('welding.index');
    Route::post('/deploy', [WeldingStockController::class, 'deployWelding'])->name('welding.deploy');
    Route::put('/start/{id}', [WeldingStockController::class, 'startWelding'])->name('welding.start');
    
    // Rute Finish (Inilah yang mengirim data ke welding_actuals & production_ng_logs)
    Route::put('/finish/{id}', [WeldingStockController::class, 'finishWelding'])->name('welding.finish');
    
    Route::get('/history', [WeldingStockController::class, 'history'])->name('welding.history');
    Route::get('/history-audit', [WeldingStockController::class, 'historyWelding'])->name('welding.history.weldig');
});

// --- MASTER REGISTRY WELDING (MESIN & NG) ---
Route::prefix('welding-master')->group(function () {
    Route::get('/lines', [WeldingMasterController::class, 'lineIndex'])->name('welding.master.lines');
    Route::post('/lines/store', [WeldingMasterController::class, 'lineStore'])->name('welding.master.line_store');
    Route::delete('/lines/destroy/{id}', [WeldingMasterController::class, 'lineDestroy'])->name('welding.master.line_destroy');
    
    // --- Master NG (Cacat Produksi) ---
    // 1. Tampilkan Halaman Master NG
    Route::get('/ng', [WeldingMasterController::class, 'ngIndex'])->name('welding.master.ng_index');
    
    // 2. Simpan Master NG Baru (Action dari Modal)
    Route::post('/ng/store', [WeldingMasterController::class, 'ngStore'])->name('welding.master.ng_store');
    
    // 3. Hapus Master NG
    Route::delete('/ng/destroy/{id}', [WeldingMasterController::class, 'ngDestroy'])->name('welding.master.ng.destroy');
   // Tambahkan di routes/web.php
Route::get('/ppic/get-batch-ng-details/{no_produksi}', [App\Http\Controllers\PPICController::class, 'getBatchNGDetails']);
});
});
});
});
});