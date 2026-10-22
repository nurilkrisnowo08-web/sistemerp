<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrder; 
use Carbon\Carbon;

class DeliveryController extends Controller
{
    /**
     * 1. DAFTAR PO AKTIF
     */
    public function index()
    {
        $pos = DB::table('purchase_orders')
            ->where('status', 'READY') 
            ->select('po_number', 'customer_code', 'due_date', 'keterangan') 
            ->groupBy('po_number', 'customer_code', 'due_date', 'keterangan')
            ->orderBy('due_date', 'asc')
            ->get();

        $activePOs = [];

        foreach ($pos as $po) {
            $poIds = DB::table('purchase_orders')
                ->where('po_number', $po->po_number)
                ->where('customer_code', $po->customer_code)
                ->pluck('id');

            $po->total_qty_po = DB::table('purchase_orders')
                ->whereIn('id', $poIds)
                ->sum('quantity');

            $po->total_terkirim = DB::table('deliveries')
                ->whereIn('po_id', $poIds) 
                ->sum('qty_delivery');

            if ($po->total_terkirim < $po->total_qty_po) {
                $activePOs[] = $po;
            }
        }

        $groupedPOs = collect($activePOs)->groupBy('customer_code');

        return view('delivery.index', compact('groupedPOs'));
    }

    /**
     * 2. FORM PENERBITAN SJ
     */
    public function create($po_number)
    {
        $clean_po = urldecode($po_number);

        $items = DB::table('purchase_orders')
            ->where('po_number', $clean_po)
            ->where('status', 'READY') 
            ->get();
        
        $po = $items->first();
        if (!$po) return redirect()->route('delivery.index')->with('error', 'PO Gak Ada atau Sudah Close!');

        foreach ($items as $item) {
            $terkirim = DB::table('deliveries')
                ->where('po_id', $item->id)
                ->where('part_no', $item->part_no)
                ->sum('qty_delivery');
                
            $item->total_sent = $terkirim;
            $item->sisa_pesanan = $item->quantity - $terkirim;
        }

        return view('delivery.create', compact('po', 'items'));
    }

    /**
     * 3. PROSES SIMPAN & AUTO-PRINT (DENGAN AUTO-SYNC AKTUAL WOS)
     */
    public function store(Request $request)
    {
        $items = $request->items; 
        $no_sj = $request->no_sj;
        $po_number = $request->po_header_number;

        DB::beginTransaction();
        try {
            foreach ($items as $part_no => $data) {
                $qty_kirim = (int) $data['qty_kirim'];

                if ($qty_kirim > 0) {
                    $po_item = DB::table('purchase_orders')
                        ->where('po_number', $po_number)
                        ->where('part_no', $part_no)
                        ->first();

                    if (!$po_item) continue;

                    // Cek Stok FG
                    $fg = DB::table('finished_goods')->where('part_no', $part_no)->first();

                    if (!$fg || $fg->actual_stock < $qty_kirim) {
                        throw new \Exception("Gagal! Stok Part $part_no tidak mencukupi (Tersedia: " . ($fg->actual_stock ?? 0) . ")");
                    }

                    // 1. Potong Stok FG
                    DB::table('finished_goods')->where('part_no', $part_no)->decrement('actual_stock', $qty_kirim);

                    // 2. Simpan ke Deliveries
                    DB::table('deliveries')->insert([
                        'po_id' => $po_item->id,
                        'no_sj' => $no_sj,
                        'part_no' => $part_no,
                        'customer_code' => $po_item->customer_code,
                        'qty_delivery' => $qty_kirim,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // 3. Update total_sent PO
                    DB::table('purchase_orders')->where('id', $po_item->id)->increment('total_sent', $qty_kirim);

                    $updated_po = DB::table('purchase_orders')->where('id', $po_item->id)->first();
                    if ($updated_po && $updated_po->total_sent >= $updated_po->quantity) {
                        DB::table('purchase_orders')->where('id', $po_item->id)->update([
                            'status' => 'CLOSED', 
                            'updated_at' => now()
                        ]);
                    }

                    // ✨ 4. LOGIKA AUTO-SYNC AKTUAL PENGIRIMAN KE WOS DELIVERY ✨
                    $today = now()->format('Y-m-d');
                    $current_time = now()->format('H:i');
                    
                    // Jika terbit SJ jam 19:00 ke bawah -> Masuk Shift 1. Di atas 19:00 -> Shift 2.
                    $shift_field = ($current_time <= '19:00') ? 'act_delv_s1' : 'act_delv_s2';

                    DB::table('delivery_plans')
                        ->where('part_no', $part_no)
                        ->whereDate('plan_date', $today)
                        ->increment($shift_field, $qty_kirim);
                }
            }

            DB::commit();
            return redirect()->route('delivery.print', $no_sj)
                ->with('success', 'Surat Jalan ' . $no_sj . ' Berhasil Terbit & Aktual WOS otomatis terupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * 4. HISTORY SURAT JALAN
     */
    public function history(Request $request)
    {
        $query = DB::table('deliveries');

        if ($request->customer_code) {
            $query->where('customer_code', $request->customer_code);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . " 00:00:00", 
                $request->end_date . " 23:59:59"
            ]);
        }

        $deliveries = $query->latest()->get()->groupBy('no_sj');
        $customers = DB::table('customers')->select('code')->get(); 

        return view('delivery.history', compact('deliveries', 'customers'));
    }

    /**
     * 5. PRINT SURAT JALAN
     */
    public function print($no_sj)
    {
        $items = DB::table('deliveries')->where('no_sj', $no_sj)->get();

        if ($items->isEmpty()) {
            return redirect()->route('delivery.index')->with('error', 'Data SJ tidak ditemukan!');
        }

        $sj = $items->first();
        $po = DB::table('purchase_orders')->where('id', $sj->po_id)->first();
        $customer = DB::table('customers')->where('code', $sj->customer_code)->first();

        return view('delivery.print', compact('items', 'sj', 'po', 'no_sj', 'customer'));
    }

    /**
     * 6. PRINT REKAP PO
     */
    public function printRekapPO($po_number)
    {
        $clean_po = urldecode($po_number);
        $poHeader = DB::table('purchase_orders')->where('po_number', $clean_po)->first();
        
        if (!$poHeader) return back()->with('error', 'Data PO tidak ditemukan!');

        $poItems = DB::table('purchase_orders')->where('po_number', $clean_po)->get();

        $allDeliveries = DB::table('deliveries')
            ->whereIn('po_id', $poItems->pluck('id'))
            ->orderBy('created_at', 'asc')
            ->get();

        return view('delivery.print_rekap_po', compact('poHeader', 'poItems', 'allDeliveries', 'po_number'));
    }

    /**
     * ✨ 7. PRINT LABEL (FITUR BARU)
     */
   public function printLabel($no_sj)
   {
       $clean_sj = urldecode($no_sj);
       $items = DB::table('deliveries')->where('no_sj', $clean_sj)->get();

       if ($items->isEmpty()) {
           return redirect()->route('delivery.history')->with('error', 'Data SJ tidak ditemukan!');
       }

       $sj = $items->first();
       $customer = DB::table('customers')->where('code', $sj->customer_code)->first();

       foreach ($items as $item) {
           $p = DB::table('parts')->where('part_no', $item->part_no)->first();
           $item->part_name = $p->part_name ?? 'N/A';

           $m = DB::table('rm_stocks')->where('material_code', $item->part_no)->first();
           if ($m) {
               $item->blank_size = $m->spec . ' [' . $m->size . ']';
           } else {
               $item->blank_size = '-';
           }

           $qrText = "ITEM DETAIL\nPart: " . $item->part_no . "\nName: " . $item->part_name . "\nQty : " . number_format($item->qty_delivery) . " PCS\nLot : " . date('ymd', strtotime($item->created_at)) . "\nSJ  : " . $clean_sj;
           $item->qr_content = rawurlencode($qrText);
       }

       return view('delivery.label', compact('items', 'sj', 'no_sj', 'customer'));
   }

   /**
    * ✨ 8. FITUR REVISI SURAT JALAN / AUTO-SYNC FG & PO
    */
   public function update(Request $request)
   {
       DB::beginTransaction();
       try {
           $no_sj = urldecode($request->no_sj);
           $ada_revisi = false; 
           
           foreach ($request->items as $delivery_id => $new_qty) {
               $new_qty = (int)$new_qty;
               
               $delivery = DB::table('deliveries')->where('id', $delivery_id)->where('no_sj', $no_sj)->first();
               if (!$delivery) continue;

               $old_qty = (int)$delivery->qty_delivery;
               $selisih = $old_qty - $new_qty; 
               
               if ($selisih != 0) {
                   $ada_revisi = true; 

                   if ($selisih > 0) {
                       DB::table('finished_goods')->where('part_no', $delivery->part_no)->increment('actual_stock', $selisih);
                       DB::table('purchase_orders')->where('id', $delivery->po_id)->decrement('total_sent', $selisih);
                   } else {
                       $selisih_kurang = abs($selisih);
                       $fg = DB::table('finished_goods')->where('part_no', $delivery->part_no)->first();
                       
                       if (!$fg || $fg->actual_stock < $selisih_kurang) {
                           throw new \Exception("Gagal Revisi! Sisa Stok FG untuk " . $delivery->part_no . " di rak tidak cukup.");
                       }
                       DB::table('finished_goods')->where('part_no', $delivery->part_no)->decrement('actual_stock', $selisih_kurang);
                       DB::table('purchase_orders')->where('id', $delivery->po_id)->increment('total_sent', $selisih_kurang);
                   }

                   DB::table('deliveries')->where('id', $delivery_id)->update([
                       'qty_delivery' => $new_qty,
                       'updated_at' => now()
                   ]);

                   $po_item = DB::table('purchase_orders')->where('id', $delivery->po_id)->first();
                   if ($po_item) {
                       if ($po_item->total_sent < $po_item->quantity) {
                           DB::table('purchase_orders')->where('id', $po_item->id)->update(['status' => 'READY']);
                       } elseif ($po_item->total_sent >= $po_item->quantity) {
                           DB::table('purchase_orders')->where('id', $po_item->id)->update(['status' => 'CLOSED']);
                       }
                   }
               }
           }

           if ($ada_revisi) {
               DB::table('deliveries')->where('no_sj', $no_sj)->increment('revision_count');
           }

           DB::commit();
           return redirect()->back()->with('success', 'Surat Jalan ' . $no_sj . ' Berhasil Direvisi & Stok Telah Disesuaikan!');
       } catch (\Exception $e) {
           DB::rollBack();
           return redirect()->back()->with('error', $e->getMessage());
       }
   }

    // ==============================================================================
    // ✨✨✨ FUNGSI BARU: WOS DELIVERY LOGISTICS ✨✨✨
    // ==============================================================================

    /**
     * WOS DELIVERY: TAMPILKAN TABEL WOS
     */
    public function wosIndex(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        
        $deliveries = DB::table('delivery_plans')
            ->leftJoin('parts', 'delivery_plans.part_no', '=', 'parts.part_no')
            ->select('delivery_plans.*', 'parts.part_name', 'parts.customer_code as part_cust')
            ->whereDate('plan_date', $date)
            ->orderBy('pulling_time', 'asc')
            ->get();
            
        foreach($deliveries as $d) {
            $d->stock_ready = DB::table('finished_goods')->where('part_no', $d->part_no)->value('actual_stock') ?? 0;
            
            $aktual_total = $d->act_delv_s1 + $d->act_delv_s2;
            
            // Logika Keterangan CLOSE merubah Total menjadi tanda strip "-"
            if (strtoupper($d->keterangan) === 'CLOSE') {
                $d->judge = 'CLOSE';
                $d->total_delv_display = '-';
            } else {
                $d->total_delv_display = number_format($d->total_delv);
                if ($d->total_delv > 0) {
                    $d->judge = ($aktual_total >= $d->total_delv) ? 'OK' : 'SHORT';
                } else {
                    $d->judge = '-';
                }
            }
        }

        // Ambil SEMUA data part langsung dari Master Parts (bukan dari FG), agar part 0 stok tetap muncul di modal
        $parts = DB::table('parts')->select('part_no', 'part_name', 'customer_code')->orderBy('part_no', 'asc')->get();
        $customers = DB::table('customers')->orderBy('code', 'asc')->pluck('code');

        return view('delivery.wos_index', compact('deliveries', 'date', 'parts', 'customers'));
    }

    /**
     * WOS DELIVERY: AJAX CEK STOK FG REAL-TIME DI MODAL
     */
    public function wosCheckStock($part_no)
    {
        $clean_part = trim(explode(' - ', urldecode($part_no))[0]);
        $stock = DB::table('finished_goods')->where('part_no', $clean_part)->value('actual_stock');

        return response()->json([
            'status' => 'success', 
            'stock' => (int) $stock
        ]);
    }

    /**
     * WOS DELIVERY: SIMPAN JADWAL BARU
     */
    /**
     * WOS DELIVERY: SIMPAN JADWAL BARU
     */
    public function wosStore(Request $request)
    {
        $blnc = (int) $request->blnc_delv;
        $plan = (int) $request->plan_delv;
        
        DB::table('delivery_plans')->insert([
            'plan_date'     => $request->plan_date,
            'part_no'       => $request->part_no,
            'customer_code' => $request->customer_code,
            'pulling_time'  => $request->pulling_time,
            'delv_time'     => $request->delv_time,
            'blnc_delv'     => $blnc,
            'plan_delv'     => $plan,
            'total_delv'    => $blnc + $plan,
            'keterangan'    => $request->keterangan, // <-- INI TAMBAHANNYA
            'created_at'    => now(),
            'updated_at'    => now()
        ]);

        return back()->with('success', 'Jadwal WOS Delivery berhasil ditambahkan!');
    }

    /**
     * WOS DELIVERY: UPDATE ACTUAL MANUAL
     */
    public function wosUpdateActual(Request $request, $id)
    {
        $d = DB::table('delivery_plans')->where('id', $id)->first();
        if(!$d) return back()->with('error', 'Data tidak ditemukan.');

        $act_s1 = (int) $request->act_delv_s1;
        $act_s2 = (int) $request->act_delv_s2;
        $total_kirim_baru = $act_s1 + $act_s2;
        $total_kirim_lama = $d->act_delv_s1 + $d->act_delv_s2;

        $selisih_kirim = $total_kirim_baru - $total_kirim_lama;
        if ($selisih_kirim > 0) {
            DB::table('finished_goods')->where('part_no', $d->part_no)->decrement('actual_stock', $selisih_kirim);
        } elseif ($selisih_kirim < 0) {
            DB::table('finished_goods')->where('part_no', $d->part_no)->increment('actual_stock', abs($selisih_kirim));
        }

        DB::table('delivery_plans')->where('id', $id)->update([
            'act_delv_s1' => $act_s1,
            'act_delv_s2' => $act_s2,
            'keterangan'  => $request->keterangan,
            'status'      => 'COMPLETED',
            'updated_at'  => now()
        ]);

        return back()->with('success', 'Aktual pengiriman berhasil diupdate manual!');
    }
}