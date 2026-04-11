<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController, DashboardController, PurchaseOrderController,
    DeliveryController, FgController, WeldingStockController,
    StampingController, ProductionController, CustomerController,
    PartController, DailyFgController, ProduksiController,
    LineController, PPICController, ReportController, RmController
};

/*
|--------------------------------------------------------------------------
| 1. GUEST
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
| 2. AUTH GLOBAL
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| 3. MONITORING (ALL ROLE)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:produksi,staff_ppic,kepala_ppic'])->group(function () {

    Route::get('/po-monitoring', [PurchaseOrderController::class, 'index'])->name('po.index');
    Route::get('/inventory-welding', [WeldingStockController::class, 'index'])->name('welding.index');
    Route::get('/stamping-production', [StampingController::class, 'index'])->name('stamping.index');
    Route::get('/stock-fg', [FgController::class, 'index'])->name('fg.index');
});

/*
|--------------------------------------------------------------------------
| 4. OPERATOR (PPIC)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:staff_ppic,kepala_ppic'])->group(function () {

    // FG
    Route::get('/stock-fg/create', [FgController::class, 'create'])->name('fg.create');
    Route::post('/stock-fg/store', [FgController::class, 'store'])->name('fg.store');
    Route::get('/fg/recap', [FgController::class, 'monthlyRecap'])->name('fg.recap');
    Route::get('/fg/print', [FgController::class, 'printRecap'])->name('fg.print');

    // DELIVERY
    Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
    Route::get('/delivery/history', [DeliveryController::class, 'history'])->name('delivery.history');
    Route::post('/delivery/store', [DeliveryController::class, 'store'])->name('delivery.store');
    Route::get('/delivery/create/{po_number}', [DeliveryController::class, 'create'])->name('delivery.create')->where('po_number', '.*');
    Route::get('/delivery/print/{no_sj}', [DeliveryController::class, 'print'])->name('delivery.print');

    // PRODUCTION SIMPLE
    Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
    Route::post('/production/store', [ProductionController::class, 'store'])->name('production.store');
});

/*
|--------------------------------------------------------------------------
| 5. ADMIN (KEPALA PPIC)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:kepala_ppic'])->group(function () {

    Route::resource('customers', CustomerController::class);
    Route::resource('parts', PartController::class);
    Route::resource('fg-daily', DailyFgController::class);

    Route::get('/po/edit/{id}', [PurchaseOrderController::class, 'edit'])->name('po.edit');
    Route::put('/po/update-header/{po_number}', [PurchaseOrderController::class, 'updateHeader'])->name('po.update');
    Route::delete('/po/delete/{id}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');

    Route::get('/stock-fg/{id}/edit', [FgController::class, 'edit'])->name('fg.edit');
    Route::put('/stock-fg/{id}', [FgController::class, 'update'])->name('fg.update');
});

/*
|--------------------------------------------------------------------------
| 6. PRODUKSI (FIXED - NO DUPLICATE)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('produksi')->group(function () {

    Route::get('/index', [ProduksiController::class, 'index'])->name('produksi.index');
    Route::post('/store', [ProduksiController::class, 'store'])->name('produksi.store');
    Route::put('/update-result/{id}', [ProduksiController::class, 'updateResult'])->name('produksi.update_result');
    Route::get('/history', [ProduksiController::class, 'history'])->name('produksi.history');

    Route::get('/get-specs/{customer}', [ProduksiController::class, 'getSpecsByCustomer']);
    Route::get('/get-parts-by-spec', [ProduksiController::class, 'getPartsBySpec']);
});

/*
|--------------------------------------------------------------------------
| 7. RAW MATERIAL (RAPIH)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('rm')->group(function () {

    Route::get('/inventory', [RmController::class, 'storeIndex'])->name('rm.store');
    Route::get('/monitoring', [RmController::class, 'storeIndex'])->name('rm.index');

    Route::post('/incoming', [RmController::class, 'incomingStore'])->name('rm.incoming');
    Route::post('/production-out', [RmController::class, 'productionStore'])->name('rm.production_out');

    Route::post('/store', [RmController::class, 'store'])->name('rm.store_save');
    Route::put('/update/{id}', [RmController::class, 'update'])->name('rm.update');
    Route::delete('/delete/{id}', [RmController::class, 'destroy'])->name('rm.destroy');

    Route::post('/master-spec', [RmController::class, 'storeMasterSpec'])->name('rm.store_master');

    Route::get('/recap', [RmController::class, 'recapIndex'])->name('rm.recap');
    Route::get('/recap-print', [RmController::class, 'rmRecapPrint'])->name('rm.recap_print');
});

/*
|--------------------------------------------------------------------------
| 8. AJAX
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/get-parts/{customer}', [PurchaseOrderController::class, 'getParts'])->name('po.get-parts');
    Route::get('/production/get-parts/{customer}', [ProductionController::class, 'getPartsByCustomer']);
});

/*
|--------------------------------------------------------------------------
| 9. LAINNYA
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/line', [LineController::class, 'index'])->name('line.index');
    Route::post('/line', [LineController::class, 'store'])->name('line.store');

    Route::get('/ppic-planning', [PPICController::class, 'index'])->name('ppic.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});