<?php

use Illuminate\Support\Facades\Route;
// Tambahkan ini di barisan paling atas web.php lo!
use App\Http\Controllers\RmController;
use App\Http\Controllers\{
    AuthController, DashboardController, PurchaseOrderController, 
    DeliveryController, FgController, FgControlController, 
    WeldingStockController, StampingController, ProductionController, 
    CustomerController, PartController, DailyFgController,ProduksiController,LineController,PPICController,ReportController
};

// =============================================================
// 1. WILAYAH TAMU (GUEST) - Belum Login
// =============================================================
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect('/login'));
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Register & Forgot Password
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::get('/forgot', [AuthController::class, 'showForgot'])->name('forgot');
    Route::post('/forgot', [AuthController::class, 'resetPassword'])->name('forgot.post');
});

// =============================================================
// 2. WILAYAH UMUM (AUTH) - Sudah Login (Semua Role Bisa Masuk)
// =============================================================
Route::middleware(['auth', 'role:produksi,staff_ppic,kepala_ppic'])->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Monitoring Dasar
    Route::get('/po-monitoring', [PurchaseOrderController::class, 'index'])->name('po.index');
    Route::get('/po-customer', [PurchaseOrderController::class, 'index'])->name('po-customer.index');
    Route::get('/inventory-welding', [WeldingStockController::class, 'index'])->name('welding.index');
    Route::get('/stamping-production', [StampingController::class, 'index'])->name('stamping.index');
    Route::get('/stock-fg', [FgController::class, 'index'])->name('fg.index');
    
    // Profile
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
});

// =============================================================
// 3. WILAYAH OPERATOR - (Staff PPIC & Kepala PPIC)
// =============================================================
Route::middleware(['auth', 'role:staff_ppic,kepala_ppic'])->group(function () {
    
    // --- FINISH GOOD (FG) ---
    Route::post('/fg/send-to-po', [FgController::class, 'sendToPo'])->name('fg.send_to_po');
    Route::get('/stock-fg/create', [FgController::class, 'create'])->name('fg.create');
    Route::post('/stock-fg/store', [FgController::class, 'store'])->name('fg.store');
    Route::get('/finished-goods/recap', [FgController::class, 'monthlyRecap'])->name('fg.recap');
    Route::get('/finished-goods/print', [FgController::class, 'printRecap'])->name('fg.print');

    // --- DELIVERY (SURAT JALAN) ---
    Route::get('/delivery', [DeliveryController::class, 'index'])->name('delivery.index');
    Route::get('/delivery/history', [DeliveryController::class, 'history'])->name('delivery.history');
    Route::post('/delivery/store', [DeliveryController::class, 'store'])->name('delivery.store');
    Route::get('/delivery/create/{po_number}', [DeliveryController::class, 'create'])->name('delivery.create')->where('po_number', '.*');
    Route::get('/delivery/print/{no_sj}', [DeliveryController::class, 'print'])->name('delivery.print');
    Route::get('/delivery/print-rekap-po/{po_number}', [DeliveryController::class, 'printRekapPO'])->name('delivery.print-rekap-po')->where('po_number', '.*');

    // --- PRODUCTION & WELDING ---
    Route::post('/production/store', [ProductionController::class, 'store'])->name('production.store');
    Route::post('/welding-store-part', [WeldingStockController::class, 'storePart'])->name('welding.store_part');
    Route::post('/inventory-welding/transfer', [WeldingStockController::class, 'transfer'])->name('welding.transfer');
    
    // --- STAMPING ---
    Route::post('/stamping-production/store', [StampingController::class, 'store'])->name('stamping.store');

    // --- PO STORE ---
    Route::post('/po-customer/store', [PurchaseOrderController::class, 'store'])->name('po.store');

   Route::get('/po/history', [PurchaseOrderController::class, 'history'])->name('po.history');
});

// =============================================================
// 4. WILAYAH KEKUASAAN - (Hanya Kepala PPIC / MUSA WAHAB)
// =============================================================
Route::middleware(['auth', 'role:kepala_ppic'])->group(function () {
    
    // Master Data (Kasta Tertinggi)
    Route::resource('customers', CustomerController::class);
    Route::resource('parts', PartController::class);
    Route::resource('fg-daily', DailyFgController::class);

    // Edit & Hapus PO (Hanya Bos yang Boleh)
    Route::get('/po/edit/{id}', [PurchaseOrderController::class, 'edit'])->name('po.edit');
    Route::put('/po/update-header/{po_number}', [PurchaseOrderController::class, 'updateHeader'])->name('po.update')->where('po_number', '.*');
    Route::put('/po/update-qty', [PurchaseOrderController::class, 'updateQty'])->name('po.update_qty');
    Route::delete('/po/delete/{id}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
    

    // Edit FG & Welding
    Route::get('/stock-fg/{id}/edit', [FgController::class, 'edit'])->name('fg.edit');
    Route::put('/stock-fg/{id}', [FgController::class, 'update'])->name('fg.update');
    Route::put('/inventory-welding/update-master', [WeldingStockController::class, 'updateMaster'])->name('welding.update_master');
    Route::delete('/production/{id}', [ProductionController::class, 'destroy'])->name('production.destroy');
});

// =============================================================
// 5. JALUR AJAX & TOOLS (BIAR DROPDOWN GAK MACET)
// =============================================================
Route::middleware('auth')->group(function () {
    // SAKTI: Jalur utama buat ambil part di modal Input PO
    Route::get('/get-parts/{customer_code}', [PurchaseOrderController::class, 'getParts'])->name('po.get-parts');
    
    // Jalur pendukung lainnya
    Route::get('/po/get-parts/{customer_code}', [PurchaseOrderController::class, 'getPartsByCustomer']);
    Route::get('/welding-get-parts/{customer}', [WeldingStockController::class, 'getPartsByCustomer']);
    Route::get('/stamping-get-parts/{customer}', [StampingController::class, 'getParts']);
    Route::get('/production/get-parts/{customer}', [ProductionController::class, 'getPartsByCustomer']);
});
// ... rute lainnya tetap sama ...

// =============================================================
// 3. WILAYAH OPERATOR - (Staff PPIC & Kepala PPIC)
// =============================================================
Route::middleware(['auth', 'role:staff_ppic,kepala_ppic'])->group(function () {
    
    // --- PRODUCTION ---
    // SAKTI: Ini rute yang hilang tadi biar gak eror Route Not Defined
    Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
    Route::post('/production/store', [ProductionController::class, 'store'])->name('production.store');
    Route::get('/production/get-parts/{customer}', [ProductionController::class, 'getPartsByCustomer']);
    // KELOMPOK RUTE INVENTORY WELDING
Route::prefix('inventory-welding')->group(function () {
    Route::get('/', [WeldingStockController::class, 'index'])->name('welding.index');
    Route::get('/recap', [WeldingStockController::class, 'recap'])->name('welding.recap'); // SAKTI: Tambahkan ini
    Route::get('/daily-recap', [WeldingStockController::class, 'dailyRecap'])->name('welding.daily_recap'); // FIX image_260762.png
    
    // Rute AJAX & Transfer (Taruh di sini biar Deny.H bisa akses)
    Route::get('/get-parts/{customer}', [WeldingStockController::class, 'getPartsByCustomer']);
    Route::post('/transfer', [WeldingStockController::class, 'transfer'])->name('welding.transfer');
});
// Pastikan pakai Route::post, bukan Route::get!
Route::post('/inventory-welding/transfer', [WeldingStockController::class, 'transfer'])->name('welding.transfer');



// --- 1. HALAMAN UTAMA (GET) ---
// Untuk melihat Dashboard Monitoring
Route::get('/rm-store', [RmController::class, 'storeIndex'])->name('rm.store');
// Untuk halaman input barang datang (jika ada halamannya sendiri)
Route::get('/rm-in', [RmController::class, 'inIndex'])->name('rm.in');

// --- 2. PROSES SIMPAN DATA (POST) ---
// Pintu khusus buat SIMPAN Master Material Baru
Route::post('/rm-store/save', [RmController::class, 'store'])->name('rm.store_new');
// Rute untuk mendaftarkan Master Spec Baru dari Excel
Route::post('/rm-master-spec', [RmController::class, 'storeMasterSpec'])->name('rm.store_master');
// Rute untuk input berat material masuk (Incoming)
Route::post('/rm-incoming', [RmController::class, 'incomingStore'])->name('rm.incoming');

// --- 3. PROSES EDIT & HAPUS (PUT/DELETE) ---
// Rute untuk update data (Edit Spec, Size, atau Lot)
Route::put('/rm-store/update/{id}', [RmController::class, 'update'])->name('rm.update');
// Rute untuk menghapus material dari monitoring
Route::delete('/rm-store/delete/{id}', [RmController::class, 'destroy'])->name('rm.destroy');

// --- 4. FITUR TAMBAHAN (GET) ---
// Untuk memperbaiki data customer yang tidak sinkron
Route::get('/rm-sync-customer', [RmController::class, 'syncCustomer'])->name('rm.sync');


// Route untuk AJAX ambil part number
Route::get('/production/get-parts/{customer}', [App\Http\Controllers\FgController::class, 'getParts']);
Route::delete('/fg/delete/{id}', [App\Http\Controllers\FgController::class, 'destroy'])->name('fg.destroy');




// Pastikan nama controller-nya sesuai, misal: DeliveryController atau PurchaseOrderController
Route::post('/delivery/store', [App\Http\Controllers\PurchaseOrderController::class, 'storeSj'])->name('sj.store');
// SAKTI: Hubungkan View ke Fungsi Store di DeliveryController
Route::post('/delivery/store', [App\Http\Controllers\DeliveryController::class, 'store'])->name('delivery.store');
// SAKTI: Jalur cetak Surat Jalan
Route::get('/delivery/print/{no_sj}/{customer_code}', [App\Http\Controllers\PurchaseOrderController::class, 'printSj'])->name('delivery.print');

// ======================= RAW MATERIAL MANAGEMENT ======================= //

// =============================================================
// 🛰️ 1. MONITORING RM & TRANSAKSI (LOG IN / LOG OUT)
// =============================================================
Route::get('/rm-store', [RmController::class, 'storeIndex'])->name('rm.store');
Route::get('/rm-monitoring', [RmController::class, 'storeIndex'])->name('rm.index');

// ✨ FIX: Nama route disamakan dengan tombol di Blade lo (image_27ad14)
Route::post('/rm-incoming', [RmController::class, 'incomingStore'])->name('rm.incoming_store');
Route::post('/rm-production-out', [RmController::class, 'productionStore'])->name('rm.production_out');

// MASTER DATA RM (CRUD)
Route::post('/rm-store/save', [RmController::class, 'store'])->name('rm.store_save');
Route::put('/rm-store/update/{id}', [RmController::class, 'update'])->name('rm.update');
Route::delete('/rm-store/delete/{id}', [RmController::class, 'destroy'])->name('rm.destroy');

// KAMUS SPEC & REKAP
Route::post('/rm-master-spec', [RmController::class, 'storeMasterSpec'])->name('rm.store_master');
Route::get('/rm-recap', [RmController::class, 'recapIndex'])->name('rm.recap');

// AJAX UNTUK MODAL TAMBAH RM
Route::get('/get-rm-data/{customer}', [RmController::class, 'getPartsAndSpecs']);


// =============================================================
// 🏭 2. LANTAI PRODUKSI (Prefix Produksi)
// =============================================================
Route::prefix('produksi')->group(function () {
    // Tampilan & Eksekusi Produksi
    Route::get('/index', [ProduksiController::class, 'index'])->name('produksi.index');
    Route::post('/store', [ProduksiController::class, 'store'])->name('produksi.store');
    Route::put('/update-result/{id}', [ProduksiController::class, 'updateResult'])->name('produksi.update_result');
    Route::get('/history', [ProduksiController::class, 'history'])->name('produksi.history');

    // 🔗 KURIR AJAX (Pemicu Dropdown Spec & Part No - image_7c41d2)
    Route::get('/get-specs/{customer}', [ProduksiController::class, 'getSpecsByCustomer']);
    Route::get('/get-parts-by-spec', [ProduksiController::class, 'getPartsBySpec']);
    Route::get('/get-part-detail/{code}', [ProduksiController::class, 'getPartDetail']);
});


// =============================================================
// 📦 3. RUANGAN RM IN & AUTO-PO
// =============================================================
Route::get('/rm-incoming-list', [RmController::class, 'incomingIndex'])->name('rm.incoming_index');
Route::post('/rm-auto-po', [RmController::class, 'generateAutoPO'])->name('rm.auto_po');
Route::post('/rm-receive-item', [RmController::class, 'processIncoming'])->name('rm.receive');
//=================================================================================================================//
//========================================PRODUKSI================================================================//
//===============================================================================================================//

// Rute Utama Monitoring Produksi
Route::get('/monitoring-produksi', [ProduksiController::class, 'index'])->name('produksi.index');

// Rute Ambil Material (Store)
Route::post('/monitoring-produksi/store', [ProduksiController::class, 'store'])->name('produksi.store');

/** * 🚀 SAKTI: Rute Input Hasil (OK vs NG)
 * Menggunakan PUT karena kita mengupdate data batch yang sudah ada
 */
Route::put('/monitoring-produksi/update-result/{id}', [ProduksiController::class, 'updateResult'])->name('produksi.update_result');
// Ruangan Monitoring (Khusus yang masih PROSES)
Route::get('/monitoring-produksi', [ProduksiController::class, 'index'])->name('produksi.index');

// Ruangan NG Report (Khusus yang sudah SELESAI/HISTORY)
Route::get('/ng-report', [ProduksiController::class, 'report'])->name('produksi.report');
Route::get('/produksi/get-specs/{customer}', [ProduksiController::class, 'getSpecsByCustomer']);
Route::get('/produksi/get-parts-by-spec', [ProduksiController::class, 'getPartsBySpec']);
Route::get('/produksi/get-part-detail/{code}', [ProduksiController::class, 'getPartDetail']);

Route::get('/produksi/history', [ProduksiController::class, 'history'])->name('produksi.history');

// Tambahkan di routes/web.php
Route::get('/produksi/get-specs/{customer}', [App\Http\Controllers\ProduksiController::class, 'getSpecsByCustomer']);
Route::get('/produksi/get-parts-by-spec', [App\Http\Controllers\ProduksiController::class, 'getPartsBySpec']);
Route::get('/produksi/get-part-detail/{code}', [App\Http\Controllers\ProduksiController::class, 'getPartDetail']);
// Pastikan pakai Route::post
Route::post('/produksi/return/{id}', [App\Http\Controllers\ProduksiController::class, 'returnToRM'])->name('produksi.return');

Route::get('/rm-reports', [ReportController::class, 'index'])->name('reports.index');
// Route khusus cetak rekap Raw Material (RM)
Route::get('/rm-recap-print', [RmController::class, 'rmRecapPrint'])->name('rm.recap_print');
// Jalur cetak history harian
Route::get('/rm-log-print', [App\Http\Controllers\RmController::class, 'recapLogPrint'])->name('rm.log_print');

Route::get('/rm/po-supplier', [App\Http\Controllers\RmController::class, 'poIndex'])->name('rm.po_supplier');
Route::post('/rm/po-supplier/store', [App\Http\Controllers\RmController::class, 'poStore'])->name('rm.po_store');
Route::post('/rm/po-supplier/receive/{id}', [App\Http\Controllers\RmController::class, 'poReceive'])->name('rm.po_receive');

// 🌊 JALUR AJAX UNTUK DROPDOWN (Spec, Part, & Bandel)
Route::get('/produksi/get-specs/{customer}', [App\Http\Controllers\ProduksiController::class, 'getSpecsByCustomer']);
Route::get('/produksi/get-parts-by-spec', [App\Http\Controllers\ProduksiController::class, 'getPartsBySpec']);
Route::get('/produksi/get-bundles/{code}', [App\Http\Controllers\ProduksiController::class, 'getBundlesByPart']);

// Route Inbound rill
Route::get('/gudang/inbound', [App\Http\Controllers\RmController::class, 'inboundIndex'])->name('rm.inbound');
Route::post('/gudang/inbound/store', [App\Http\Controllers\RmController::class, 'inboundStore'])->name('rm.inbound_store');

// Kamar PO Supplier
Route::get('/rm/po-supplier', [App\Http\Controllers\RmController::class, 'poSupplierIndex'])->name('rm.po_supplier');
// Inbound (Pintu Masuk Barang)
Route::get('/gudang/inbound', [App\Http\Controllers\RmController::class, 'inboundIndex'])->name('rm.inbound');

Route::post('/rm/po-supplier/store', [App\Http\Controllers\RmController::class, 'poSupplierStore'])->name('rm.po_supplier_store');

// Pastikan route resolve_interruption juga sudah ada:
Route::post('/produksi/resolve/{id}', [App\Http\Controllers\ProduksiController::class, 'resolveInterruption'])->name('produksi.resolve_interruption');

// Route buat Print Ledger (Tabel Utama)
Route::get('/rm/recap-print', [App\Http\Controllers\RmController::class, 'rmRecapPrint'])->name('rm.recap_print');

// Route buat Print Log (Incoming & Feeding) rill!
Route::get('/rm/recap-log-print', [App\Http\Controllers\RmController::class, 'recapLogPrint'])->name('rm.recap_log_print');
//=================================================================================================================//
//========================================Mesin================================================================//
//===============================================================================================================//
Route::get('/line', [LineController::class, 'index'])->name('line.index');
Route::post('/line', [LineController::class, 'store'])->name('line.store');
Route::put('/line/{id}', [LineController::class, 'update'])->name('line.update');
Route::delete('/line/{id}', [LineController::class, 'destroy'])->name('line.destroy');
// Daftarkan route untuk handle Emergency Stop
Route::post('/produksi/resolve/{id}', [App\Http\Controllers\ProduksiController::class, 'resolveInterruption'])->name('produksi.resolve_interruption');
//=================================================================================================================//
//========================================Planing ppic================================================================//
//===============================================================================================================//
Route::get('/ppic-planning', [PPICController::class, 'index'])->name('ppic.index');
Route::get('/ppic-api-data', [PPICController::class, 'apiData'])->name('ppic.api');
// Tambahkan ini
Route::post('/rm/store-batch', [RmController::class, 'storeBatch'])->name('rm.store_batch');
    // --- FINISH GOOD (FG) ---
    // ... rute FG lainnya ...

    // --- DELIVERY (SURAT JALAN) ---
    // ... rute Delivery lainnya ...
});
//=================================================================================================================//
//======================================Purcahase Order ================================================================//
//===============================================================================================================//

// --- 1. Jalur AJAX (Luar Group) ---
Route::get('/get-parts-and-specs/{customer}', [App\Http\Controllers\RmController::class, 'getPartsAndSpecs']);

// --- 2. Group Utama Raw Material (RM) rill ---
Route::prefix('rm')->group(function () {
    
    // Dashboard Inventory rill
    Route::get('/inventory', [App\Http\Controllers\RmController::class, 'storeIndex'])->name('rm.store');
    
    // ✨ JEMBATAN DUA NAMA RILL! (Pake 2 Baris terpisah biar gak error) ✨
    // Ini buat Filter lo:
    Route::get('/po-supplier', [App\Http\Controllers\RmController::class, 'poSupplierIndex'])->name('rm.po_supplier_index');
    // Ini buat Sidebar lo:
    Route::get('/po-supplier-node', [App\Http\Controllers\RmController::class, 'poSupplierIndex'])->name('rm.po_supplier');
    
    // Jalur Simpan & Kedatangan rill
    Route::post('/po-supplier/store', [App\Http\Controllers\RmController::class, 'poSupplierStore'])->name('rm.po_supplier_store');
    Route::post('/po-arrival/{id}', [App\Http\Controllers\RmController::class, 'poArrivalStore'])->name('rm.po_arrival_store');
    
    // Jalur Cetak PO & Management
    Route::get('/po-print/{id}', [App\Http\Controllers\RmController::class, 'printPO'])->name('rm.print_po');
    Route::post('/store-batch', [App\Http\Controllers\RmController::class, 'storeBatch'])->name('rm.store_batch');
    Route::post('/master-spec', [App\Http\Controllers\RmController::class, 'storeMasterSpec'])->name('rm.store_master');
    Route::put('/update/{id}', [App\Http\Controllers\RmController::class, 'update'])->name('rm.update');
    Route::delete('/destroy/{id}', [App\Http\Controllers\RmController::class, 'destroy'])->name('rm.destroy');
});

Route::post('/rm/update-alias', [RmController::class, 'updateAlias'])->name('rm.update_alias');
// ... sisanya tetap sama ...

// Tambahkan baris ini rill!
Route::get('/rm/po-history', [RmController::class, 'poSupplierHistory'])->name('rm.po_supplier_history');

Route::put('/rm/unit-update/{id}', [RmController::class, 'updateUnit'])->name('rm.unit_update');
Route::post('/rm/assign-part', [RmController::class, 'assignPart'])->name('rm.assign_part');

Route::delete('/rm/remove-part/{id}', [RmController::class, 'removePartFromUnit'])->name('rm.remove_part_from_unit');

Route::get('/rm-log-print', [RmController::class, 'recapLogPrint'])->name('rm.log_print');
Route::get('/rm-recap-print', [RmController::class, 'recapPrint'])->name('rm.recap_print');


// Route buat Terminal Welding lu rill
Route::get('/inventory-welding', [WeldingStockController::class, 'index'])->name('welding.index');

// ✨ INI YANG KURANG RILL! ✨
Route::put('/inventory-welding/start/{id}', [WeldingStockController::class, 'startWelding'])->name('welding.start');
Route::put('/inventory-welding/finish/{id}', [WeldingStockController::class, 'finishWelding'])->name('welding.finish');

// Route buat update master & rekap rill
Route::put('/inventory-welding/update-master', [WeldingStockController::class, 'updateMaster'])->name('welding.update_master');
Route::get('/inventory-welding/daily-recap', [WeldingStockController::class, 'dailyRecap'])->name('welding.daily_recap');
Route::get('/inventory-welding/monthly-recap', [WeldingStockController::class, 'recap'])->name('welding.recap');
// Pastiin lokasinya bareng sama route welding lainnya
Route::post('/welding/deploy', [App\Http\Controllers\WeldingStockController::class, 'deployWelding'])->name('welding.deploy');

Route::prefix('welding')->name('welding.')->group(function () {
    // Halaman Utama Terminal Welding
    Route::get('/index', [WeldingStockController::class, 'index'])->name('index');
    
    // Halaman History / Archive Vault (INI YANG BARU RILL!)
    Route::get('/history', [WeldingStockController::class, 'history'])->name('history');
    
    // Prosedur Kerja
    Route::post('/deploy', [WeldingStockController::class, 'deployWelding'])->name('deploy');
    Route::put('/start/{id}', [WeldingStockController::class, 'startWelding'])->name('start');
    Route::put('/finish/{id}', [WeldingStockController::class, 'finishWelding'])->name('finish');
});