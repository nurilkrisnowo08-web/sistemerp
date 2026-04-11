<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder; 
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * 1. TAMPILAN MONITORING PO (TETAP SAKTI - AUTO CLOSE AKTIF)
     */
    public function index() 
    {
        $rawOrders = PurchaseOrder::where('status', 'READY')
            ->orderBy('due_date', 'asc')
            ->get();

        $activeOrders = [];

        foreach ($rawOrders as $po) {
            $total_terkirim = DB::table('deliveries')
                ->where('po_id', $po->id)
                ->sum('qty_delivery');
            
            $po->sisa = $po->quantity - $total_terkirim;

            if ($po->sisa <= 0) {
                PurchaseOrder::where('id', $po->id)->update([
                    'status' => 'CLOSED',
                    'updated_at' => now()
                ]);
                continue; 
            }

            $activeOrders[] = $po;
        }

        $purchaseOrders = collect($activeOrders)->groupBy(['customer_code', 'po_number']);
        $historyOrders = PurchaseOrder::where('status', 'CLOSED')->latest()->take(15)->get();
        $customers = \App\Models\Customer::all();

        return view('po.index', compact('purchaseOrders', 'historyOrders', 'customers'));
    }

    /**
     * 2. SIMPAN PO BARU (FIX: PAKSA SIMPAN KE DUA KOLOM BIAR GAK BALIK REGULER)
     */
    public function store(Request $request) 
    {
        DB::transaction(function () use ($request) {
            foreach ($request->part_no as $key => $part) {
                if (!empty($part)) {
                    // SAKTI: Ambil pilihan Guru, kalau kosong baru default ke REGULER
                    $pilihan = $request->jenis_po ?? $request->keterangan ?? 'REGULER';

                    PurchaseOrder::create([
                        'po_number'     => $request->po_number,
                        'customer_code' => $request->customer_code,
                        'due_date'      => $request->due_date,
                        'part_no'       => $part,
                        'quantity'      => $request->quantity[$key],
                        'status'        => 'READY',
                        // SAKTI: Kita isi dua-duanya biar pilihan TESTING/URGENT nempel permanen!
                        'jenis_po'      => $pilihan, 
                        'keterangan'    => $pilihan 
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'PO Multi-Item Berhasil Disimpan!');
    }

    /**
     * 3. AJAX AMBIL PART PER CUSTOMER (TETAP)
     */
    public function getPartsByCustomer($customer_code)
    {
        $parts = DB::table('parts')->where('customer_code', $customer_code)->get();
        return response()->json($parts);
    }

    /**
     * 4. UPDATE MASSAL HEADER (FIX: NANGKEP EDIT URGENT/TESTING)
     */
    public function update(Request $request)
    {
        $request->validate([
            'original_po_number' => 'required',
            'due_date' => 'required|date',
        ]);

        $pilihan = $request->jenis_po ?? $request->keterangan;

        DB::table('purchase_orders')
            ->where('po_number', $request->original_po_number)
            ->update([
                'due_date' => $request->due_date,
                // SAKTI: Update dua-duanya biar sinkron sama database
                'jenis_po' => $pilihan,
                'keterangan' => $pilihan,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Data PO ' . $request->original_po_number . ' berhasil diperbarui!');
    }

    /**
     * 5. UPDATE QTY PER ITEM (TETAP)
     */
    public function updateQty(Request $request)
    {
        DB::table('purchase_orders')->where('id', $request->id)->update([
            'quantity' => $request->quantity,
            'updated_at' => now()
        ]);
        return redirect()->back()->with('success', 'Quantity Part berhasil diupdate!');
    }

    /**
     * 6. UPDATE HEADER PER PO NUMBER (FIX: SINKRON URGENT/TESTING)
     */
    public function updateHeader(Request $request, $po_number)
    {
        $clean_po = urldecode($po_number);
        $pilihan = $request->jenis_po ?? $request->keterangan;

        DB::table('purchase_orders')->where('po_number', $clean_po)->update([
            'due_date' => $request->due_date,
            // SAKTI: Isi dua-duanya biar gak ada alasan balik ke Reguler lagi!
            'jenis_po' => $pilihan, 
            'keterangan' => $pilihan, 
            'updated_at' => now()
        ]);
        
        return redirect()->back()->with('success', 'Data PO '.$clean_po.' Berhasil Diupdate!');
    }

    /**
     * 7. HISTORY PO CLOSED (FIXED: ANTI-CRASH & ANTI-ZONK)
     */
    public function history(Request $request)
    {
        $customers = \DB::table('customers')->get();
        $customer = $request->customer;

        $purchaseOrders = \App\Models\PurchaseOrder::with('deliveries')
            ->where('status', 'CLOSED')
            ->when($customer, function($q) use ($customer) {
                return $q->where('customer_code', $customer);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('po.history', compact('purchaseOrders', 'customers'));
    }

    /**
     * 8. TERBIT SURAT JALAN (ANTI-DUPLIKAT)
     */
    public function storeSj(Request $request)
    {
        DB::table('deliveries')->updateOrInsert(
            [
                'po_id'         => $request->po_id,
                'part_no'       => $request->part_no,
                'customer_code' => $request->customer_code,
                'no_sj'         => 'SJ-' . date('Ymd') . '-00/' . $request->customer_code 
            ],
            [
                'qty_delivery'  => $request->qty_delivery,
                'status'        => 'SENT',
                'updated_at'    => now()
            ]
        );

        return redirect()->back()->with('success', 'Surat Jalan Berhasil Terbit (Anti-Duplikat)!');
    }

    /**
     * 9. SAKTI: TAMPILAN CETAK SURAT JALAN (FIX 404)
     */
    public function printSj($no_sj, $customer_code)
    {
        $delivery = DB::table('deliveries')
            ->where('no_sj', 'LIKE', $no_sj)
            ->where('customer_code', 'LIKE', $customer_code)
            ->first();

        if (!$delivery) {
            $existingSj = DB::table('deliveries')->limit(5)->pluck('no_sj')->toArray();
            $list = implode(', ', $existingSj);
            return "Guru, No SJ <b>$no_sj</b> tidak ketemu! Di database adanya: <b>$list</b>.";
        }

        $poDetail = DB::table('purchase_orders')->where('id', $delivery->po_id)->first();
        $customer = DB::table('customers')->where('code', $delivery->customer_code)->first();

        return view('po.print_sj', compact('delivery', 'poDetail', 'customer'));
    }
}