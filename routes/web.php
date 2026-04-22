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
    Route::get('/forgot', [AuthController::class, 'showForgot'])->name('forgot');
    Route::post('/forgot', [AuthController::class, 'resetPassword'])->name('forgot.post');
});

/*
|--------------------------------------------------------------------------
| 2. WILAYAH UMUM (AUTH) - Semua yang sudah Login
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');

    // --- JALUR AJAX & DROPDOWN (Penting agar UI tidak macet) ---
    Route::get('/get-parts/{customer_code}', [PurchaseOrderController::class, 'getParts'])->name('po.get-parts');
    Route::get('/get-rm-data/{customer}', [RmController::class, 'getPartsAndSpecs']);
    Route::get('/produksi/get-specs/{customer}', [ProduksiController::class, 'getSpecsByCustomer']);
    Route::get('/produksi/get-parts-by-spec', [ProduksiController::class, 'getPartsBySpec']);
    Route::get('/produksi/get-part-detail/{code}', [ProduksiController::class, 'getPartDetail']);
    Route::get('/produksi/get-bundles/{code}', [ProduksiController::class, 'getBundlesByPart']);
});

/*
|--------------------------------------------------------------------------
| 3. LEVEL 1: VIEWERS (Produksi, Staff PPIC, Kepala PPIC)
|--------------------------------------------------------------------------
| Role ini hanya bisa melihat monitoring dan riwayat.
*/
Route::middleware(['auth', 'role:produksi,staff_ppic,kepala_ppic'])->group(function () {
    // Inventory View
    Route::get('/stock-fg', [FgController::class, 'index'])->name('fg.index');
    Route::get('/inventory-welding', [WeldingStockController::class, 'index'])->name('welding.index');
    Route::get('/rm-inventory', [RmController::class, 'storeIndex'])->name('rm.store');
    Route::get('/rm-mutation', [RmController::class, 'rmMutation'])->name('rm.mutation');

    // Manufacturing View
    Route::get('/monitoring-produksi', [ProduksiController::class, 'index'])->name('produksi.index');
    Route::get('/stamping-production', [StampingController::class, 'index'])->name('stamping.index');
    Route::get('/line-registry', [LineController::class, 'index'])->name('line.index');
    
    // Quality & History View
    Route::get('/quality-control-room', [QualityGateController::class, 'index'])->name('quality.index');
    Route::get('/quality/history', [QualityGateController::class, 'history'])->name('quality.history');
    Route::get('/welding-vault', [WeldingStockController::class, 'history'])->name('welding.history');
    Route::get('/welding-history-prod', [WeldingStockController::class, 'historyWelding'])->name('welding.history.weldig');
    Route::get('/produksi/history', [ProduksiController::class, 'history'])->name('produksi.history');
});

/*
|--------------------------------------------------------------------------
| 4. LEVEL 2: INPUTTERS (Staff PPIC & Kepala PPIC)
|--------------------------------------------------------------------------
| Role ini bisa Input/Tambah data, tapi TIDAK BISA Edit atau Hapus.
*/
Route::middleware(['auth', 'role:staff_ppic,kepala_ppic'])->group(function () {
    // Commerce & Delivery
    Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
    Route::get('/delivery/create/{po_number}', [DeliveryController::class, 'create'])->name('delivery.create')->where('po_number', '.*');
    Route::post('/delivery/store', [DeliveryController::class, 'store'])->name('delivery.store');
    Route::get('/delivery/history', [DeliveryController::class, 'history'])->name('delivery.history');
    Route::get('/delivery/print/{no_sj}', [DeliveryController::class, 'print'])->name('delivery.print');
    Route::get('/delivery/print-rekap-po/{po_number}', [DeliveryController::class, 'printRekapPO'])->name('delivery.print-rekap-po');

    // Order Center
    Route::get('/po-customer', [PurchaseOrderController::class, 'index'])->name('po-customer.index');
    Route::post('/po-customer/store', [PurchaseOrderController::class, 'store'])->name('po.store');
    Route::get('/rm/po-supplier', [RmController::class, 'poSupplierIndex'])->name('rm.po_supplier');
    Route::post('/rm/po-supplier/store', [RmController::class, 'poSupplierStore'])->name('rm.po_supplier_store');

    // Inventory & Production Input
    Route::post('/stock-fg/store', [FgController::class, 'store'])->name('fg.store');
    Route::post('/rm-incoming', [RmController::class, 'incomingStore'])->name('rm.incoming');
    Route::post('/rm-production-out', [RmController::class, 'productionStore'])->name('rm.production_out');
    Route::post('/welding/deploy', [WeldingStockController::class, 'deployWelding'])->name('welding.deploy');
    Route::put('/welding/finish/{id}', [WeldingStockController::class, 'finishWelding'])->name('welding.finish');
    Route::post('/monitoring-produksi/store', [ProduksiController::class, 'store'])->name('produksi.store');
    Route::put('/monitoring-produksi/update-result/{id}', [ProduksiController::class, 'updateResult'])->name('produksi.update_result');

    // Quality Action
    Route::post('/quality-control-approve/{type}/{id}', [QualityGateController::class, 'approve'])->name('quality.approve');

    // PPIC Planning
    Route::get('/ppic-planning', [PPICController::class, 'index'])->name('ppic.index');
});

/*
|--------------------------------------------------------------------------
| 5. LEVEL 3: ADMIN / POWER USER (Hanya Kepala PPIC)
|--------------------------------------------------------------------------
| Role ini memiliki hak akses penuh: Edit, Update, dan Delete.
*/
Route::middleware(['auth', 'role:kepala_ppic'])->group(function () {
    // Master Data Resources
    Route::resource('customers', CustomerController::class);
    Route::resource('parts', PartController::class);
    Route::resource('line', LineController::class);
    Route::resource('fg-daily', DailyFgController::class);

    // Edit & Delete Access
    Route::delete('/fg/delete/{id}', [FgController::class, 'destroy'])->name('fg.destroy');
    Route::delete('/rm/delete/{id}', [RmController::class, 'destroy'])->name('rm.destroy');
    Route::delete('/quality-control-delete/{type}/{id}', [QualityGateController::class, 'destroy'])->name('quality.destroy');
    Route::delete('/po/delete/{id}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
    Route::get('/po/edit/{id}', [PurchaseOrderController::class, 'edit'])->name('po.edit');
    Route::put('/po/update-header/{po_number}', [PurchaseOrderController::class, 'updateHeader'])->name('po.update');

    // System Tools
    Route::post('/rm/update-alias', [RmController::class, 'updateAlias'])->name('rm.update_alias');
    Route::post('/produksi/resolve/{id}', [ProduksiController::class, 'resolveInterruption'])->name('produksi.resolve_interruption');
});