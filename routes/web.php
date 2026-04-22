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
| 1. TOOLS & CACHE CLEANER
|--------------------------------------------------------------------------
*/
Route::get('/bersihkan-sistem', function() {
    \Artisan::call('route:clear');
    \Artisan::call('view:clear');
    \Artisan::call('config:clear');
    return "Sistem Sinkron! Silakan kembali ke Dashboard.";
});

/*
|--------------------------------------------------------------------------
| 2. GUEST & AUTH DASAR
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect('/login'));
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| 3. JALUR SINKRONISASI DATA (PENTING: SEMUA ROLE BISA AKSES)
|--------------------------------------------------------------------------
| Jalur ini saya bebaskan agar Dropdown "SAI" dll tidak mentok di SYNCING.
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // --- JALUR DATA PO CUSTOMER (FIX DROPDOWN MACET) ---
    Route::get('/get-parts/{customer_code}', [PurchaseOrderController::class, 'getParts'])->name('po.get-parts');
    Route::get('/po/get-parts/{customer_code}', [PurchaseOrderController::class, 'getPartsByCustomer']);
    
    // --- JALUR DATA RM & PRODUKSI ---
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
| 4. KAMAR PRODUKSI (HANYA LIHAT)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:produksi,staff_ppic,kepala_ppic'])->group(function () {
    Route::get('/stock-fg', [FgController::class, 'index'])->name('fg.index');
    Route::get('/inventory-welding', [WeldingStockController::class, 'index'])->name('welding.index');
    Route::get('/rm-inventory', [RmController::class, 'storeIndex'])->name('rm.store');
    Route::get('/monitoring-produksi', [ProduksiController::class, 'index'])->name('produksi.index');
    Route::get('/quality-control-room', [QualityGateController::class, 'index'])->name('quality.index');
    
    // History (Hanya Lihat)
    Route::get('/po/history', [PurchaseOrderController::class, 'history'])->name('po.history');
    Route::get('/rm/mutation', [RmController::class, 'rmMutation'])->name('rm.mutation');
    Route::get('/rm/po-history', [RmController::class, 'poSupplierHistory'])->name('rm.po_supplier_history');
    Route::get('/quality/history', [QualityGateController::class, 'history'])->name('quality.history');
    Route::get('/welding-vault', [WeldingStockController::class, 'history'])->name('welding.history');
    Route::get('/produksi/history', [ProduksiController::class, 'history'])->name('produksi.history');
});

/*
|--------------------------------------------------------------------------
| 5. KAMAR STAFF PPIC (LIHAT + INPUT)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:staff_ppic,kepala_ppic'])->group(function () {
    // PO Customer & Delivery
    Route::get('/po-customer', [PurchaseOrderController::class, 'index'])->name('po-customer.index');
    Route::post('/po-customer/store', [PurchaseOrderController::class, 'store'])->name('po.store');
    Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
    Route::get('/delivery/history', [DeliveryController::class, 'history'])->name('delivery.history');
    Route::post('/delivery/store', [DeliveryController::class, 'store'])->name('delivery.store');
    Route::get('/delivery/create/{po_number}', [DeliveryController::class, 'create'])->name('delivery.create')->where('po_number', '.*');
    Route::get('/delivery/print/{no_sj}', [DeliveryController::class, 'print'])->name('delivery.print')->where('no_sj', '.*');
    Route::get('/delivery/print-rekap-po/{po_number}', [DeliveryController::class, 'printRekapPO'])->name('delivery.print-rekap-po')->where('po_number', '.*');

    // RM & PO Supplier
    Route::get('/rm/po-supplier', [RmController::class, 'poSupplierIndex'])->name('rm.po_supplier_index');
    Route::get('/rm/po-supplier-sidebar', [RmController::class, 'poSupplierIndex'])->name('rm.po_supplier');
    Route::post('/rm/store-batch', [RmController::class, 'storeBatch'])->name('rm.store_batch');
    Route::post('/rm/po-supplier/store', [RmController::class, 'poSupplierStore'])->name('rm.po_supplier_store');
    Route::post('/rm/po-arrival/{id}', [RmController::class, 'poArrivalStore'])->name('rm.po_arrival_store');
    Route::get('/rm/po-print/{id}', [RmController::class, 'printPO'])->name('rm.print_po');
    
    // FG & Produksi Input
    Route::get('/stock-fg/create', [FgController::class, 'create'])->name('fg.create');
    Route::post('/stock-fg/store', [FgController::class, 'store'])->name('fg.store');
    Route::post('/rm-incoming', [RmController::class, 'incomingStore'])->name('rm.incoming');
    Route::post('/monitoring-produksi/store', [ProduksiController::class, 'store'])->name('produksi.store');
    Route::put('/monitoring-produksi/update-result/{id}', [ProduksiController::class, 'updateResult'])->name('produksi.update_result');
    Route::post('/welding/deploy', [WeldingStockController::class, 'deployWelding'])->name('welding.deploy');
    Route::put('/welding/finish/{id}', [WeldingStockController::class, 'finishWelding'])->name('welding.finish');
    Route::post('/quality-control-approve/{type}/{id}', [QualityGateController::class, 'approve'])->name('quality.approve');
    
    Route::get('/rm-log-print', [RmController::class, 'recapLogPrint'])->name('rm.log_print');
    Route::get('/rm-recap-print', [RmController::class, 'recapPrint'])->name('rm.recap_print');
});

/*
|--------------------------------------------------------------------------
| 6. KAMAR KEPALA PPIC (ADMIN / FULL CRUD)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:kepala_ppic'])->group(function () {
    Route::put('/po/update-qty', [PurchaseOrderController::class, 'updateQty'])->name('po.update_qty');
    Route::delete('/rm/remove-part/{id}', [RmController::class, 'removePartFromUnit'])->name('rm.remove_part_from_unit');
    Route::post('/rm/store-master', [RmController::class, 'storeMasterSpec'])->name('rm.store_master');
    
    Route::resource('customers', CustomerController::class);
    Route::resource('parts', PartController::class);
    Route::resource('line', LineController::class);

    // Edit Master
    Route::get('/po/edit/{id}', [PurchaseOrderController::class, 'edit'])->name('po.edit');
    Route::put('/po/update-header/{po_number}', [PurchaseOrderController::class, 'updateHeader'])->name('po.update')->where('po_number', '.*');
    Route::get('/stock-fg/{id}/edit', [FgController::class, 'edit'])->name('fg.edit');
    Route::put('/stock-fg/{id}', [FgController::class, 'update'])->name('fg.update');

    // Hapus Data
    Route::delete('/fg/delete/{id}', [FgController::class, 'destroy'])->name('fg.destroy');
    Route::delete('/rm/delete/{id}', [RmController::class, 'destroy'])->name('rm.destroy');
    Route::delete('/po/delete/{id}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
    Route::delete('/quality-control-delete/{type}/{id}', [QualityGateController::class, 'destroy'])->name('quality.destroy');
});