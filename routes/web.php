<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController, DashboardController, PurchaseOrderController, 
    DeliveryController, FgController, WeldingStockController, 
    StampingController, ProductionController, CustomerController, 
    PartController, DailyFgController, ProduksiController, 
    LineController, PPICController, ReportController, QualityGateController, RmController
};

/*
|--------------------------------------------------------------------------
| TOOLS & CACHE CLEANER
|--------------------------------------------------------------------------
*/
Route::get('/bersihkan-sistem', function() {
    \Artisan::call('route:clear');
    \Artisan::call('view:clear');
    \Artisan::call('config:clear');
    return "Sistem Bersih Total! Silakan kembali ke Dashboard.";
});

/*
|--------------------------------------------------------------------------
| 1. WILAYAH TAMU (GUEST)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect('/login'));
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

/*
|--------------------------------------------------------------------------
| 2. WILAYAH UMUM & AJAX (AUTH)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // --- JALUR DATA (Dropdown Ajax) ---
    Route::get('/get-parts/{customer_code}', [PurchaseOrderController::class, 'getParts'])->name('po.get-parts');
    Route::get('/get-rm-data/{customer}', [RmController::class, 'getPartsAndSpecs']);
    Route::get('/produksi/get-specs/{customer}', [ProduksiController::class, 'getSpecsByCustomer']);
    Route::get('/produksi/get-parts-by-spec', [ProduksiController::class, 'getPartsBySpec']);
    Route::get('/produksi/get-part-detail/{code}', [ProduksiController::class, 'getPartDetail']);
});

/*
|--------------------------------------------------------------------------
| 3. KAMAR PRODUKSI (Bisa Lihat: FG, Welding, RM, Monitor, Quality)
|--------------------------------------------------------------------------
| Akses: Read-Only (Hanya Lihat Laporan & Monitoring)
*/
Route::middleware(['auth', 'role:produksi,staff_ppic,kepala_ppic'])->group(function () {
    // Inventory Monitoring
    Route::get('/stock-fg', [FgController::class, 'index'])->name('fg.index');
    Route::get('/inventory-welding', [WeldingStockController::class, 'index'])->name('welding.index');
    Route::get('/rm-inventory', [RmController::class, 'storeIndex'])->name('rm.store');
    
    // Laporan & History (Menghilangkan Error Route Not Found)
    Route::get('/finished-goods/recap', [FgController::class, 'monthlyRecap'])->name('fg.recap');
    Route::get('/finished-goods/print', [FgController::class, 'printRecap'])->name('fg.print');
    Route::get('/rm-log-print', [RmController::class, 'recapLogPrint'])->name('rm.log_print');
    Route::get('/rm-recap-print', [RmController::class, 'recapPrint'])->name('rm.recap_print');
    Route::get('/rm/mutation', [RmController::class, 'rmMutation'])->name('rm.mutation');
    Route::get('/po/history', [PurchaseOrderController::class, 'history'])->name('po.history');
    Route::get('/rm/po-history', [RmController::class, 'poSupplierHistory'])->name('rm.po_supplier_history');

    // Manufacturing & Quality View
    Route::get('/monitoring-produksi', [ProduksiController::class, 'index'])->name('produksi.index');
    Route::get('/produksi/history', [ProduksiController::class, 'history'])->name('produksi.history');
    Route::get('/quality-control-room', [QualityGateController::class, 'index'])->name('quality.index');
    Route::get('/quality/history', [QualityGateController::class, 'history'])->name('quality.history');
    Route::get('/welding-vault', [WeldingStockController::class, 'history'])->name('welding.history');
    Route::get('/welding-history-prod', [WeldingStockController::class, 'historyWelding'])->name('welding.history.weldig');
});

/*
|--------------------------------------------------------------------------
| 4. KAMAR STAFF PPIC (Semua Level 3 + Bisa Input/Tambah)
|--------------------------------------------------------------------------
| Akses: Create & Read (Bisa Input & Terbitkan SJ, Tapi Tidak Bisa Hapus/Edit)
*/
Route::middleware(['auth', 'role:staff_ppic,kepala_ppic'])->group(function () {
    // Logistics (Delivery)
    Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
    Route::get('/delivery/create/{po_number}', [DeliveryController::class, 'create'])->name('delivery.create')->where('po_number', '.*');
    Route::post('/delivery/store', [DeliveryController::class, 'store'])->name('delivery.store');
    Route::get('/delivery/history', [DeliveryController::class, 'history'])->name('delivery.history');
    Route::get('/delivery/print/{no_sj}', [DeliveryController::class, 'print'])->name('delivery.print')->where('no_sj', '.*');
    Route::get('/delivery/print-rekap-po/{po_number}', [DeliveryController::class, 'printRekapPO'])->name('delivery.print-rekap-po')->where('po_number', '.*');

    // Order Center
    Route::get('/po-customer-index', [PurchaseOrderController::class, 'index'])->name('po-customer.index');
    Route::post('/po-customer/store', [PurchaseOrderController::class, 'store'])->name('po.store');
    Route::get('/rm/po-supplier', [RmController::class, 'poSupplierIndex'])->name('rm.po_supplier');
    Route::post('/rm/po-supplier/store', [RmController::class, 'poSupplierStore'])->name('rm.po_supplier_store');
    Route::post('/rm/po-arrival/{id}', [RmController::class, 'poArrivalStore'])->name('rm.po_arrival_store');

    // Planning & Input
    Route::get('/ppic-planning', [PPICController::class, 'index'])->name('ppic.index');
    Route::post('/stock-fg/store', [FgController::class, 'store'])->name('fg.store');
    Route::post('/rm-incoming', [RmController::class, 'incomingStore'])->name('rm.incoming');
    Route::post('/rm-production-out', [RmController::class, 'productionStore'])->name('rm.production_out');
    Route::post('/welding/deploy', [WeldingStockController::class, 'deployWelding'])->name('welding.deploy');
    Route::put('/welding/finish/{id}', [WeldingStockController::class, 'finishWelding'])->name('welding.finish');
    Route::post('/monitoring-produksi/store', [ProduksiController::class, 'store'])->name('produksi.store');
    Route::put('/monitoring-produksi/update-result/{id}', [ProduksiController::class, 'updateResult'])->name('produksi.update_result');
    Route::post('/quality-control-approve/{type}/{id}', [QualityGateController::class, 'approve'])->name('quality.approve');
});

/*
|--------------------------------------------------------------------------
| 5. KAMAR KEPALA PPIC (ADMIN / FULL ACCESS)
|--------------------------------------------------------------------------
| Akses: CRUD (Bisa Tambah, Edit, Hapus, dan Kelola Master Data)
*/
Route::middleware(['auth', 'role:kepala_ppic'])->group(function () {
    // Master Data Resource
    Route::resource('customers', CustomerController::class);
    Route::resource('parts', PartController::class);
    Route::resource('fg-daily', DailyFgController::class);
    Route::get('/line-registry-manage', [LineController::class, 'index'])->name('line.index');
    Route::post('/line-store', [LineController::class, 'store'])->name('line.store');

    // Edit & Update PO
    Route::get('/po/edit/{id}', [PurchaseOrderController::class, 'edit'])->name('po.edit');
    Route::put('/po/update-header/{po_number}', [PurchaseOrderController::class, 'updateHeader'])->name('po.update')->where('po_number', '.*');

    // Delete Access
    Route::delete('/fg/delete/{id}', [FgController::class, 'destroy'])->name('fg.destroy');
    Route::delete('/rm/delete/{id}', [RmController::class, 'destroy'])->name('rm.destroy');
    Route::delete('/quality-control-delete/{type}/{id}', [QualityGateController::class, 'destroy'])->name('quality.destroy');
    Route::delete('/po/delete/{id}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
    Route::delete('/line-delete/{id}', [LineController::class, 'destroy'])->name('line.destroy');

    // System Advanced
    Route::post('/produksi/resolve/{id}', [ProduksiController::class, 'resolveInterruption'])->name('produksi.resolve_interruption');
});