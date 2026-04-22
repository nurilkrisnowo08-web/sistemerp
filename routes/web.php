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
| 3. JALUR AJAX & TOOLS (SEMUA ROLE - AGAR DROPDOWN TIDAK MACET)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Dropdown Kurir
    Route::get('/get-parts/{customer_code}', [PurchaseOrderController::class, 'getParts'])->name('po.get-parts');
    Route::get('/get-parts-and-specs/{customer}', [RmController::class, 'getPartsAndSpecs']);
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
| Produksi hanya bisa lihat monitoring & history. Dilarang Simpan/Edit/Hapus.
*/
Route::middleware(['auth', 'role:produksi,staff_ppic,kepala_ppic'])->group(function () {
    // Inventory Monitoring
    Route::get('/stock-fg', [FgController::class, 'index'])->name('fg.index');
    Route::get('/inventory-welding', [WeldingStockController::class, 'index'])->name('welding.index');
    Route::get('/rm-inventory', [RmController::class, 'storeIndex'])->name('rm.store');
    
    // Manufacturing & Quality
    Route::get('/monitoring-produksi', [ProduksiController::class, 'index'])->name('produksi.index');
    Route::get('/stamping-production', [StampingController::class, 'index'])->name('stamping.index');
    Route::get('/quality-control-room', [QualityGateController::class, 'index'])->name('quality.index');
    Route::get('/line-registry', [LineController::class, 'index'])->name('line.index');

    // History & Recap (Fix Error: po.history, rm.mutation, dll)
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
| Bisa Input/Tambah Data & Terbitkan SJ. Dilarang Edit/Hapus Master.
*/
Route::middleware(['auth', 'role:staff_ppic,kepala_ppic'])->group(function () {
    // Menghilangkan Error [fg.create], [fg.recap], [rm.po_supplier_index]
    Route::get('/stock-fg/create', [FgController::class, 'create'])->name('fg.create');
    Route::get('/finished-goods/recap', [FgController::class, 'monthlyRecap'])->name('fg.recap');
    Route::get('/finished-goods/print', [FgController::class, 'printRecap'])->name('fg.print');
    Route::get('/rm/po-supplier', [RmController::class, 'poSupplierIndex'])->name('rm.po_supplier_index');
    Route::get('/rm/po-supplier-node', [RmController::class, 'poSupplierIndex'])->name('rm.po_supplier');
    Route::get('/rm-log-print', [RmController::class, 'recapLogPrint'])->name('rm.log_print');
    Route::get('/rm-recap-print', [RmController::class, 'recapPrint'])->name('rm.recap_print');

    // PO & Delivery Store
    Route::get('/po-customer-index', [PurchaseOrderController::class, 'index'])->name('po-customer.index');
    Route::post('/po-customer/store', [PurchaseOrderController::class, 'store'])->name('po.store');
    Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
    Route::post('/delivery/store', [DeliveryController::class, 'store'])->name('delivery.store');
    Route::get('/delivery/create/{po_number}', [DeliveryController::class, 'create'])->name('delivery.create')->where('po_number', '.*');
    Route::get('/delivery/print/{no_sj}', [DeliveryController::class, 'print'])->name('delivery.print')->where('no_sj', '.*');
    Route::get('/delivery/print-rekap-po/{po_number}', [DeliveryController::class, 'printRekapPO'])->name('delivery.print-rekap-po');

    // Production & Inventory Actions
    Route::post('/stock-fg/store', [FgController::class, 'store'])->name('fg.store');
    Route::post('/rm-incoming', [RmController::class, 'incomingStore'])->name('rm.incoming');
    Route::post('/rm/po-supplier/store', [RmController::class, 'poSupplierStore'])->name('rm.po_supplier_store');
    Route::post('/rm/po-arrival/{id}', [RmController::class, 'poArrivalStore'])->name('rm.po_arrival_store');
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
| Bisa Edit/Update & Hapus (The Boss Access).
*/
Route::middleware(['auth', 'role:kepala_ppic'])->group(function () {
    // Menghilangkan Error [po.update_qty]
    Route::put('/po/update-qty', [PurchaseOrderController::class, 'updateQty'])->name('po.update_qty');

    // Master Data Resource
    Route::resource('customers', CustomerController::class);
    Route::resource('parts', PartController::class);
    Route::resource('line-master', LineController::class)->names('line');
    Route::resource('fg-daily', DailyFgController::class);

    // Edit PO
    Route::get('/po/edit/{id}', [PurchaseOrderController::class, 'edit'])->name('po.edit');
    Route::put('/po/update-header/{po_number}', [PurchaseOrderController::class, 'updateHeader'])->name('po.update')->where('po_number', '.*');

    // Delete Keys
    Route::delete('/fg/delete/{id}', [FgController::class, 'destroy'])->name('fg.destroy');
    Route::delete('/rm/delete/{id}', [RmController::class, 'destroy'])->name('rm.destroy');
    Route::delete('/po/delete/{id}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
    Route::delete('/quality-control-delete/{type}/{id}', [QualityGateController::class, 'destroy'])->name('quality.destroy');
    Route::delete('/line-delete/{id}', [LineController::class, 'destroy'])->name('line.destroy');

    // Maintenance
    Route::post('/rm/update-alias', [RmController::class, 'updateAlias'])->name('rm.update_alias');
    Route::post('/produksi/resolve/{id}', [ProduksiController::class, 'resolveInterruption'])->name('produksi.resolve_interruption');
});