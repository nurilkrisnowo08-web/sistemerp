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
        $customers = Customer::all();

        return view('po.index', compact('purchaseOrders', 'historyOrders', 'customers'));
    }

    /**
     * 2. SIMPAN PO BARU (VALIDASI ARRAY & SINKRON STATUS DUA KOLOM)
     */
    public function store(Request $request) 
    {
        if (!$request->has('part_no') || !is_array($request->part_no)) {
            return redirect()->back()->with('error', 'Item part tidak ditemukan!');
        }

        DB::transaction(function () use ($request) {
            foreach ($request->part_no as $key => $part) {
                if (!empty($part)) {
                    $pilihan = $request->jenis_po ?? $request->keterangan ?? 'REGULER';

                    PurchaseOrder::create([
                        'po_number'     => $request->po_number,
                        'customer_code' => $request->customer_code,
                        'due_date'      => $request->due_date,
                        'part_no'       => $part,
                        'quantity'      => $request->quantity[$key] ?? 0,
                        'status'        => 'READY',
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
     * 4. UPDATE MASSAL HEADER (CEK PILIHAN AGAR TIDAK KETIMPA NULL)
     */
    public function update(Request $request)
    {
        $request->validate([
            'original_po_number' => 'required',
            'due_date' => 'required|date',
        ]);

        $updateData = [
            'due_date'   => $request->due_date,
            'updated_at' => now(),
        ];

        $pilihan = $request->jenis_po ?? $request->keterangan;
        if (!empty($pilihan)) {
            $updateData['jenis_po'] = $pilihan;
            $updateData['keterangan'] = $pilihan;
        }

        DB::table('purchase_orders')
            ->where('po_number', $request->original_po_number)
            ->update($updateData);

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
     * 6. UPDATE HEADER PER PO NUMBER (CEK PILIHAN AGAR TIDAK KETIMPA NULL)
     */
    public function updateHeader(Request $request, $po_number)
    {
        $clean_po = urldecode($po_number);
        
        $updateData = [
            'due_date'   => $request->due_date,
            'updated_at' => now(),
        ];

        $pilihan = $request->jenis_po ?? $request->keterangan;
        if (!empty($pilihan)) {
            $updateData['jenis_po'] = $pilihan;
            $updateData['keterangan'] = $pilihan;
        }

        DB::table('purchase_orders')
            ->where('po_number', $clean_po)
            ->update($updateData);
        
        return redirect()->back()->with('success', 'Data PO ' . $clean_po . ' Berhasil Diupdate!');
    }

    /**
     * 7. HISTORY PO CLOSED (TETAP)
     */
    public function history(Request $request)
    {
        $customers = Customer::all();
        $customer = $request->customer;

        $purchaseOrders = PurchaseOrder::with('deliveries')
            ->where('status', 'CLOSED')
            ->when($customer, function($q) use ($customer) {
                return $q->where('customer_code', $customer);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('po.history', compact('purchaseOrders', 'customers'));
    }

    /**
     * 8. TERBIT SURAT JALAN (NOMOR OTOMATIS URUT, TANPA SLASH BIAR TIDAK 404)
     */
    public function storeSj(Request $request)
    {
        $today = date('Ymd');
        $countToday = DB::table('deliveries')
            ->where('customer_code', $request->customer_code)
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        $sequence = str_pad($countToday, 2, '0', STR_PAD_LEFT);
        $no_sj = 'SJ-' . $today . '-' . $sequence . '-' . $request->customer_code;

        DB::table('deliveries')->insert([
            'po_id'         => $request->po_id,
            'part_no'       => $request->part_no,
            'customer_code' => $request->customer_code,
            'no_sj'         => $no_sj,
            'qty_delivery'  => $request->qty_delivery,
            'status'        => 'SENT',
            'created_at'    => now(),
            'updated_at'    => now()
        ]);

        return redirect()->back()->with('success', 'Surat Jalan Berhasil Terbit dengan No: ' . $no_sj);
    }

    /**
     * 9. TAMPILAN CETAK SURAT JALAN
     */
    public function printSj($no_sj, $customer_code)
    {
        $clean_sj = urldecode($no_sj);
        $clean_customer = urldecode($customer_code);

        $delivery = DB::table('deliveries')
            ->where('no_sj', $clean_sj)
            ->where('customer_code', $clean_customer)
            ->first();

        if (!$delivery) {
            $existingSj = DB::table('deliveries')->limit(5)->pluck('no_sj')->toArray();
            $list = implode(', ', $existingSj);
            return "No SJ <b>$clean_sj</b> tidak ditemukan. Data terakhir yang ada: <b>$list</b>.";
        }

        $poDetail = DB::table('purchase_orders')->where('id', $delivery->po_id)->first();
        $customer = DB::table('customers')->where('code', $delivery->customer_code)->first();

        return view('po.print_sj', compact('delivery', 'poDetail', 'customer'));
    }
}