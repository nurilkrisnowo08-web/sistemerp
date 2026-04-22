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
| 1. TOOLS & CACHE CLEANER (Wajib Ada)
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
| Ini adalah kunci agar dropdown PT (SAI, TMMIN, dll) tidak mentok di SYNCING.
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // --- Jalur Utama Sinkronisasi (PO & RM) ---
    Route::get('/get-parts/{customer_code}', [PurchaseOrderController::class, 'getParts'])->name('po.get-parts');
    Route::get('/po/get-parts/{customer_code}', [PurchaseOrderController::class, 'getPartsByCustomer']);
    Route::get('/get-parts-and-specs/{customer}', [RmController::class, 'getPartsAndSpecs'])->name('rm.get_parts_specs');
    Route::get('/get-rm-data/{customer}', [RmController::class, 'getPartsAndSpecs']);
    
    // --- Jalur Sinkronisasi Produksi ---
    Route::get('/produksi/get-specs/{customer}', [ProduksiController::class, 'getSpecsByCustomer']);
    Route::get('/produksi/get-parts-by-spec', [ProduksiController::class, 'getPartsBySpec']);
    Route::get('/produksi/get-part-detail/{code}', [ProduksiController::class, 'getPartDetail']);
    Route::get('/produksi/get-bundles/{code}', [ProduksiController::class, 'getBundlesByPart']);
    Route::get('/ppic-api-data', [PPICController::class, 'apiData'])->name('ppic.api');
});

/*
|--------------------------------------------------------------------------
| 4. KAMAR VIEWERS (PRODUKSI, STAFF PPIC, KEPALA PPIC)
|--------------------------------------------------------------------------
| Produksi hanya boleh LIHAT. Dilarang Simpan (POST), Edit (PUT), atau Hapus.
*/
Route::middleware(['auth', 'role:produksi,staff_ppic,kepala_ppic'])->group(function () {
    // Inventory Monitoring
    Route::get('/stock-fg', [FgController::class, 'index'])->name('fg.index');
    Route::get('/inventory-welding', [WeldingStockController::class, 'index'])->name('welding.index');
    Route::get('/rm-inventory', [RmController::class, 'storeIndex'])->name('rm.store');
    Route::get('/rm-monitoring', [RmController::class, 'storeIndex'])->name('rm.index');
    
    // Manufacturing & Quality View
    Route::get('/monitoring-produksi', [ProduksiController::class, 'index'])->name('produksi.index');
    Route::get('/stamping-production', [StampingController::class, 'index'])->name('stamping.index');
    Route::get('/quality-control-room', [QualityGateController::class, 'index'])->name('quality.index');
    Route::get('/line-registry', [LineController::class, 'index'])->name('line.index');

    // History & Logs (MENYEMBUHKAN SEMUA ERROR PO.HISTORY, RM.MUTATION, DLL)
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
| 5. KAMAR OPERATORS (STAFF PPIC & KEPALA PPIC)
|--------------------------------------------------------------------------
| Boleh LIHAT + INPUT/SIMPAN (POST). Dilarang Edit/Hapus Master data.
*/
Route::middleware(['auth', 'role:staff_ppic,kepala_ppic'])->group(function () {
    // Fitur Input PO Customer & Dispatch (Surat Jalan)
    Route::get('/po-customer-index', [PurchaseOrderController::class, 'index'])->name('po-customer.index');
    Route::get('/po-customer', [PurchaseOrderController::class, 'index'])->name('po.index'); // Alias agar tombol tidak error
    Route::post('/po-customer/store', [PurchaseOrderController::class, 'store'])->name('po.store');
    Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
    Route::get('/delivery/history', [DeliveryController::class, 'history'])->name('delivery.history');
    Route::post('/delivery/store', [DeliveryController::class, 'store'])->name('delivery.store');
    Route::get('/delivery/create/{po_number}', [DeliveryController::class, 'create'])->name('delivery.create')->where('po_number', '.*');
    Route::get('/delivery/print/{no_sj}', [DeliveryController::class, 'print'])->name('delivery.print')->where('no_sj', '.*');
    Route::get('/delivery/print-rekap-po/{po_number}', [DeliveryController::class, 'printRekapPO'])->name('delivery.print-rekap-po')->where('po_number', '.*');

    // Fitur Input Gudang RM & PO Supplier (FIX ERROR rm.store_batch, rm.po_supplier_index)
    Route::get('/rm/po-supplier', [RmController::class, 'poSupplierIndex'])->name('rm.po_supplier_index');
    Route::get('/rm/po-supplier-node', [RmController::class, 'poSupplierIndex'])->name('rm.po_supplier');
    Route::post('/rm/store-batch', [RmController::class, 'storeBatch'])->name('rm.store_batch');
    Route::post('/rm/po-supplier/store', [RmController::class, 'poSupplierStore'])->name('rm.po_supplier_store');
    Route::post('/rm/po-arrival/{id}', [RmController::class, 'poArrivalStore'])->name('rm.po_arrival_store');
    Route::get('/rm/po-print/{id}', [RmController::class, 'printPO'])->name('rm.print_po');
    Route::post('/rm-incoming', [RmController::class, 'incomingStore'])->name('rm.incoming');
    Route::post('/rm-production-out', [RmController::class, 'productionStore'])->name('rm.production_out');

    // Fitur Input FG & Produksi
    Route::get('/stock-fg/create', [FgController::class, 'create'])->name('fg.create');
    Route::post('/stock-fg/store', [FgController::class, 'store'])->name('fg.store');
    Route::get('/finished-goods/recap', [FgController::class, 'monthlyRecap'])->name('fg.recap');
    Route::get('/finished-goods/print', [FgController::class, 'printRecap'])->name('fg.print');
    Route::post('/monitoring-produksi/store', [ProduksiController::class, 'store'])->name('produksi.store');
    Route::put('/monitoring-produksi/update-result/{id}', [ProduksiController::class, 'updateResult'])->name('produksi.update_result');
    Route::post('/welding/deploy', [WeldingStockController::class, 'deployWelding'])->name('welding.deploy');
    Route::put('/welding/finish/{id}', [WeldingStockController::class, 'finishWelding'])->name('welding.finish');
    Route::post('/quality-control-approve/{type}/{id}', [QualityGateController::class, 'approve'])->name('quality.approve');
    
    // Reports
    Route::get('/rm-log-print', [RmController::class, 'recapLogPrint'])->name('rm.log_print');
    Route::get('/rm-recap-print', [RmController::class, 'recapPrint'])->name('rm.recap_print');
    Route::get('/ppic-planning', [PPICController::class, 'index'])->name('ppic.index');
});

/*
|--------------------------------------------------------------------------
| 6. KAMAR KEPALA PPIC (ADMIN / FULL ACCESS)
|--------------------------------------------------------------------------
| Akses MUTLAK: Edit Master, Update Qty, Hapus Data.
*/
Route::middleware(['auth', 'role:kepala_ppic'])->group(function () {
    // FIX SEMUA ERROR EDIT & DELETE
    Route::put('/po/update-qty', [PurchaseOrderController::class, 'updateQty'])->name('po.update_qty');
    Route::delete('/rm/remove-part/{id}', [RmController::class, 'removePartFromUnit'])->name('rm.remove_part_from_unit');
    Route::post('/rm/store-master', [RmController::class, 'storeMasterSpec'])->name('rm.store_master');

    // Master Resources
    Route::resource('customers', CustomerController::class);
    Route::resource('parts', PartController::class);
    Route::resource('line-master', LineController::class)->names('line');
    Route::resource('fg-daily', DailyFgController::class);

    // Edit Master PO & Stock
    Route::get('/po/edit/{id}', [PurchaseOrderController::class, 'edit'])->name('po.edit');
    Route::put('/po/update-header/{po_number}', [PurchaseOrderController::class, 'updateHeader'])->name('po.update')->where('po_number', '.*');
    Route::get('/stock-fg/{id}/edit', [FgController::class, 'edit'])->name('fg.edit');
    Route::put('/stock-fg/{id}', [FgController::class, 'update'])->name('fg.update');
    Route::put('/rm/unit-update/{id}', [RmController::class, 'updateUnit'])->name('rm.unit_update');
    Route::put('/rm/store/update/{id}', [RmController::class, 'update'])->name('rm.update');
    Route::post('/rm/update-alias', [RmController::class, 'updateAlias'])->name('rm.update_alias');
    Route::post('/rm/assign-part', [RmController::class, 'assignPart'])->name('rm.assign_part');

    // Kill Switches (Delete Buttons)
    Route::delete('/fg/delete/{id}', [FgController::class, 'destroy'])->name('fg.destroy');
    Route::delete('/rm/delete/{id}', [RmController::class, 'destroy'])->name('rm.destroy');
    Route::delete('/po/delete/{id}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
    Route::delete('/quality-control-delete/{type}/{id}', [QualityGateController::class, 'destroy'])->name('quality.destroy');
    Route::delete('/line-delete/{id}', [LineController::class, 'destroy'])->name('line.destroy');

    // Advanced Operations
    Route::post('/produksi/resolve/{id}', [ProduksiController::class, 'resolveInterruption'])->name('produksi.resolve_interruption');
});