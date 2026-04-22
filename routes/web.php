<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RmController;
use App\Http\Controllers\{
    AuthController, DashboardController, PurchaseOrderController, 
    DeliveryController, FgController, WeldingStockController, 
    StampingController, ProductionController, CustomerController, 
    PartController, DailyFgController, ProduksiController, 
    LineController, PPICController, ReportController, QualityGateController
};

/*
|--------------------------------------------------------------------------
| 1. TOOLS & CACHE (WAJIB JALANKAN INI SETELAH UPDATE KODE)
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
| 2. GUEST (BELUM LOGIN)
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
| 3. AUTH & AJAX (SEMUA ROLE)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // JALUR DATA AJAX (Biar Dropdown Lancar)
    Route::get('/get-parts/{customer_code}', [PurchaseOrderController::class, 'getParts'])->name('po.get-parts');
    Route::get('/get-rm-data/{customer}', [RmController::class, 'getPartsAndSpecs']);
    Route::get('/produksi/get-specs/{customer}', [ProduksiController::class, 'getSpecsByCustomer']);
    Route::get('/produksi/get-parts-by-spec', [ProduksiController::class, 'getPartsBySpec']);
    Route::get('/produksi/get-part-detail/{code}', [ProduksiController::class, 'getPartDetail']);
    Route::get('/produksi/get-bundles/{code}', [ProduksiController::class, 'getBundlesByPart']);
});

/*
|--------------------------------------------------------------------------
| 4. LEVEL VIEWERS (PRODUKSI, STAFF PPIC, KEPALA PPIC)
|--------------------------------------------------------------------------
| Hanya akses halaman Index, History, dan Monitoring.
*/
Route::middleware(['auth', 'role:produksi,staff_ppic,kepala_ppic'])->group(function () {
    // Inventory Monitoring
    Route::get('/stock-fg', [FgController::class, 'index'])->name('fg.index');
    Route::get('/inventory-welding', [WeldingStockController::class, 'index'])->name('welding.index');
    Route::get('/rm-inventory', [RmController::class, 'storeIndex'])->name('rm.store');
    
    // Rute Monitoring & History (MENYEMBUHKAN ERROR po.history, rm.mutation, dll)
    Route::get('/po/history', [PurchaseOrderController::class, 'history'])->name('po.history');
    Route::get('/rm/mutation', [RmController::class, 'rmMutation'])->name('rm.mutation');
    Route::get('/rm/po-history', [RmController::class, 'poSupplierHistory'])->name('rm.po_supplier_history');
    Route::get('/welding-vault', [WeldingStockController::class, 'history'])->name('welding.history');
    Route::get('/welding-history-prod', [WeldingStockController::class, 'historyWelding'])->name('welding.history.weldig');
    
    // Monitoring Produksi
    Route::get('/monitoring-produksi', [ProduksiController::class, 'index'])->name('produksi.index');
    Route::get('/stamping-production', [StampingController::class, 'index'])->name('stamping.index');
    Route::get('/produksi/history', [ProduksiController::class, 'history'])->name('produksi.history');
    Route::get('/ng-report', [ProduksiController::class, 'report'])->name('produksi.report');

    // Quality Room
    Route::get('/quality-control-room', [QualityGateController::class, 'index'])->name('quality.index');
    Route::get('/quality/history', [QualityGateController::class, 'history'])->name('quality.history');
});

/*
|--------------------------------------------------------------------------
| 5. LEVEL OPERATORS (STAFF PPIC & KEPALA PPIC)
|--------------------------------------------------------------------------
| Bisa Create, Store, dan Print. Tidak Bisa Hapus/Edit Master.
*/
Route::middleware(['auth', 'role:staff_ppic,kepala_ppic'])->group(function () {
    // Menghilangkan Error [fg.create], [fg.recap]
    Route::get('/stock-fg/create', [FgController::class, 'create'])->name('fg.create');
    Route::get('/finished-goods/recap', [FgController::class, 'monthlyRecap'])->name('fg.recap');
    Route::get('/finished-goods/print', [FgController::class, 'printRecap'])->name('fg.print');
    
    // Menghilangkan Error [rm.po_supplier_index], [rm.log_print]
    Route::get('/rm/po-supplier', [RmController::class, 'poSupplierIndex'])->name('rm.po_supplier_index');
    Route::get('/rm/po-supplier-main', [RmController::class, 'poSupplierIndex'])->name('rm.po_supplier');
    Route::get('/rm-log-print', [RmController::class, 'recapLogPrint'])->name('rm.log_print');
    Route::get('/rm-recap-print', [RmController::class, 'recapPrint'])->name('rm.recap_print');

    // Logistics & PO Store
    Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
    Route::get('/delivery/create/{po_number}', [DeliveryController::class, 'create'])->name('delivery.create')->where('po_number', '.*');
    Route::post('/delivery/store', [DeliveryController::class, 'store'])->name('delivery.store');
    Route::get('/delivery/print/{no_sj}', [DeliveryController::class, 'print'])->name('delivery.print')->where('no_sj', '.*');
    
    Route::get('/po-customer', [PurchaseOrderController::class, 'index'])->name('po-customer.index');
    Route::post('/po-customer/store', [PurchaseOrderController::class, 'store'])->name('po.store');
    
    // Input/Simpan Data Lainnya
    Route::post('/stock-fg/store', [FgController::class, 'store'])->name('fg.store');
    Route::post('/rm-incoming', [RmController::class, 'incomingStore'])->name('rm.incoming');
    Route::post('/rm-production-out', [RmController::class, 'productionStore'])->name('rm.production_out');
    Route::post('/rm/po-supplier/store', [RmController::class, 'poSupplierStore'])->name('rm.po_supplier_store');
    Route::post('/rm/po-arrival/{id}', [RmController::class, 'poArrivalStore'])->name('rm.po_arrival_store');
    Route::post('/monitoring-produksi/store', [ProduksiController::class, 'store'])->name('produksi.store');
    Route::put('/monitoring-produksi/update-result/{id}', [ProduksiController::class, 'updateResult'])->name('produksi.update_result');
    Route::post('/quality-control-approve/{type}/{id}', [QualityGateController::class, 'approve'])->name('quality.approve');
    
    Route::get('/ppic-planning', [PPICController::class, 'index'])->name('ppic.index');
});

/*
|--------------------------------------------------------------------------
| 6. LEVEL ADMINISTRATOR (HANYA KEPALA PPIC)
|--------------------------------------------------------------------------
| Akses Full: Delete, Edit, dan Master Data.
*/
Route::middleware(['auth', 'role:kepala_ppic'])->group(function () {
    // Menghilangkan Error [po.update_qty]
    Route::put('/po/update-qty', [PurchaseOrderController::class, 'updateQty'])->name('po.update_qty');

    // Master Resources
    Route::resource('customers', CustomerController::class);
    Route::resource('parts', PartController::class);
    Route::resource('line', LineController::class);
    Route::resource('fg-daily', DailyFgController::class);

    // Edit PO Access
    Route::get('/po/edit/{id}', [PurchaseOrderController::class, 'edit'])->name('po.edit');
    Route::put('/po/update-header/{po_number}', [PurchaseOrderController::class, 'updateHeader'])->name('po.update')->where('po_number', '.*');

    // Hapus Data (Kill Switches)
    Route::delete('/fg/delete/{id}', [FgController::class, 'destroy'])->name('fg.destroy');
    Route::delete('/rm/delete/{id}', [RmController::class, 'destroy'])->name('rm.destroy');
    Route::delete('/po/delete/{id}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
    Route::delete('/quality-control-delete/{type}/{id}', [QualityGateController::class, 'destroy'])->name('quality.destroy');
});