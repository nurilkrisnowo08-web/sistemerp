<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RmController;
use App\Http\Controllers\{
    AuthController, DashboardController, PurchaseOrderController, 
    DeliveryController, FgController, FgControlController, 
    WeldingStockController, StampingController, ProductionController, 
    CustomerController, PartController, DailyFgController, ProduksiController, 
    LineController, PPICController, ReportController, QualityGateController
};

/*
|--------------------------------------------------------------------------
| 1. TOOLS & CACHE (Semua Role)
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
});

/*
|--------------------------------------------------------------------------
| 3. JALUR DATA AJAX (SEMUA ROLE) - SOLUSI DROPDOWN "-- SYNCING --"
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Jalur Sinkronisasi Part & Spec (Wajib kebuka agar dropdown jalan)
    Route::get('/get-parts/{customer_code}', [PurchaseOrderController::class, 'getParts'])->name('po.get-parts');
    Route::get('/get-parts-and-specs/{customer}', [RmController::class, 'getPartsAndSpecs'])->name('rm.get_parts_specs');
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
| Produksi hanya bisa LIHAT. Tidak ada akses POST/PUT/DELETE.
*/
Route::middleware(['auth', 'role:produksi,staff_ppic,kepala_ppic'])->group(function () {
    // Inventory & Monitoring View
    Route::get('/stock-fg', [FgController::class, 'index'])->name('fg.index');
    Route::get('/inventory-welding', [WeldingStockController::class, 'index'])->name('welding.index');
    Route::get('/rm-inventory', [RmController::class, 'storeIndex'])->name('rm.store');
    Route::get('/monitoring-produksi', [ProduksiController::class, 'index'])->name('produksi.index');
    Route::get('/stamping-production', [StampingController::class, 'index'])->name('stamping.index');
    Route::get('/quality-control-room', [QualityGateController::class, 'index'])->name('quality.index');
    Route::get('/line-registry', [LineController::class, 'index'])->name('line.index');

    // History & Logs
    Route::get('/po/history', [PurchaseOrderController::class, 'history'])->name('po.history');
    Route::get('/rm/mutation', [RmController::class, 'rmMutation'])->name('rm.mutation');
    Route::get('/rm/po-history', [RmController::class, 'poSupplierHistory'])->name('rm.po_supplier_history');
    Route::get('/quality/history', [QualityGateController::class, 'history'])->name('quality.history');
    Route::get('/welding-vault', [WeldingStockController::class, 'history'])->name('welding.history');
    Route::get('/welding-history-prod', [WeldingStockController::class, 'historyWelding'])->name('welding.history.weldig');
    Route::get('/produksi/history', [ProduksiController::class, 'history'])->name('produksi.history');
    Route::get('/ng-report', [ProduksiController::class, 'report'])->name('produksi.report');
});

/*
|--------------------------------------------------------------------------
| 5. LEVEL OPERATORS (STAFF PPIC & KEPALA PPIC)
|--------------------------------------------------------------------------
| Bisa Create, Store, dan Print. Dilarang Edit/Hapus data yang sudah ada.
*/
Route::middleware(['auth', 'role:staff_ppic,kepala_ppic'])->group(function () {
    // FIX ERROR: [fg.create], [fg.recap], [delivery.history]
    Route::get('/stock-fg/create', [FgController::class, 'create'])->name('fg.create');
    Route::get('/finished-goods/recap', [FgController::class, 'monthlyRecap'])->name('fg.recap');
    Route::get('/finished-goods/print', [FgController::class, 'printRecap'])->name('fg.print');
    Route::get('/delivery/history', [DeliveryController::class, 'history'])->name('delivery.history');

    // FIX ERROR: [rm.po_supplier_index], [rm.log_print], [rm.print_po], [rm.store_master]
    Route::get('/rm/po-supplier', [RmController::class, 'poSupplierIndex'])->name('rm.po_supplier_index');
    Route::get('/rm/po-supplier-node', [RmController::class, 'poSupplierIndex'])->name('rm.po_supplier');
    Route::get('/rm/po-print/{id}', [RmController::class, 'printPO'])->name('rm.print_po');
    Route::get('/rm-log-print', [RmController::class, 'recapLogPrint'])->name('rm.log_print');
    Route::get('/rm-recap-print', [RmController::class, 'recapPrint'])->name('rm.recap_print');
    Route::post('/rm/store-master', [RmController::class, 'storeMasterSpec'])->name('rm.store_master');

    // Logistics & Dispatch (FIX Rekap Delivery 404)
    Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
    Route::get('/delivery/create/{po_number}', [DeliveryController::class, 'create'])->name('delivery.create')->where('po_number', '.*');
    Route::post('/delivery/store', [DeliveryController::class, 'store'])->name('delivery.store');
    Route::get('/delivery/print/{no_sj}', [DeliveryController::class, 'print'])->name('delivery.print')->where('no_sj', '.*');
    Route::get('/delivery/print-rekap-po/{po_number}', [DeliveryController::class, 'printRekapPO'])->name('delivery.print-rekap-po')->where('po_number', '.*');

    // Order Entry
    Route::get('/po-customer-index', [PurchaseOrderController::class, 'index'])->name('po-customer.index');
    Route::post('/po-customer/store', [PurchaseOrderController::class, 'store'])->name('po.store');
    Route::post('/rm/po-supplier/store', [RmController::class, 'poSupplierStore'])->name('rm.po_supplier_store');
    Route::post('/rm/po-arrival/{id}', [RmController::class, 'poArrivalStore'])->name('rm.po_arrival_store');

    // Production Actions
    Route::post('/stock-fg/store', [FgController::class, 'store'])->name('fg.store');
    Route::post('/rm-incoming', [RmController::class, 'incomingStore'])->name('rm.incoming');
    Route::post('/rm-production-out', [RmController::class, 'productionStore'])->name('rm.production_out');
    Route::post('/monitoring-produksi/store', [ProduksiController::class, 'store'])->name('produksi.store');
    Route::put('/monitoring-produksi/update-result/{id}', [ProduksiController::class, 'updateResult'])->name('produksi.update_result');
    Route::post('/welding/deploy', [WeldingStockController::class, 'deployWelding'])->name('welding.deploy');
    Route::put('/welding/finish/{id}', [WeldingStockController::class, 'finishWelding'])->name('welding.finish');
    Route::post('/quality-control-approve/{type}/{id}', [QualityGateController::class, 'approve'])->name('quality.approve');
    
    Route::get('/ppic-planning', [PPICController::class, 'index'])->name('ppic.index');
});

/*
|--------------------------------------------------------------------------
| 6. LEVEL ADMINISTRATOR (HANYA KEPALA PPIC)
|--------------------------------------------------------------------------
| Akses MUTLAK: Edit (PUT), Hapus (DELETE), dan Update Master.
*/
Route::middleware(['auth', 'role:kepala_ppic'])->group(function () {
    // FIX ERROR: [po.update_qty], [rm.remove_part_from_unit], [fg.edit]
    Route::put('/po/update-qty', [PurchaseOrderController::class, 'updateQty'])->name('po.update_qty');
    Route::delete('/rm/remove-part/{id}', [RmController::class, 'removePartFromUnit'])->name('rm.remove_part_from_unit');
    Route::get('/stock-fg/{id}/edit', [FgController::class, 'edit'])->name('fg.edit');
    Route::put('/stock-fg/{id}', [FgController::class, 'update'])->name('fg.update');

    // Master Resources
    Route::resource('customers', CustomerController::class);
    Route::resource('parts', PartController::class);
    Route::resource('line-master', LineController::class)->names('line');
    Route::resource('fg-daily', DailyFgController::class);

    // Editing PO
    Route::get('/po/edit/{id}', [PurchaseOrderController::class, 'edit'])->name('po.edit');
    Route::put('/po/update-header/{po_number}', [PurchaseOrderController::class, 'updateHeader'])->name('po.update')->where('po_number', '.*');
    
    // Master RM Update
    Route::put('/rm/unit-update/{id}', [RmController::class, 'updateUnit'])->name('rm.unit_update');
    Route::put('/rm/store/update/{id}', [RmController::class, 'update'])->name('rm.update');
    Route::post('/rm/update-alias', [RmController::class, 'updateAlias'])->name('rm.update_alias');
    Route::post('/rm/assign-part', [RmController::class, 'assignPart'])->name('rm.assign_part');

    // Delete Buttons
    Route::delete('/fg/delete/{id}', [FgController::class, 'destroy'])->name('fg.destroy');
    Route::delete('/rm/delete/{id}', [RmController::class, 'destroy'])->name('rm.destroy');
    Route::delete('/po/delete/{id}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
    Route::delete('/quality-control-delete/{type}/{id}', [QualityGateController::class, 'destroy'])->name('quality.destroy');
    Route::delete('/line-delete/{id}', [LineController::class, 'destroy'])->name('line.destroy');

    // Advanced Operations
    Route::post('/produksi/resolve/{id}', [ProduksiController::class, 'resolveInterruption'])->name('produksi.resolve_interruption');
});