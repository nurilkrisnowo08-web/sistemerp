<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrder; 
use Carbon\Carbon;

class DeliveryController extends Controller
{
    /**
     * 1. DAFTAR PO AKTIF - FIX: PAKAI 'keterangan' KARENA 'jenis_po' SUDAH DI-REMOVE
     */
    public function index()
    {
        // SAKTI: Mengambil 'keterangan' sebagai pengganti 'jenis_po' agar tidak error 1054
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
                ->where('status', 'READY')
                ->pluck('id');

            $po->total_qty_po = DB::table('purchase_orders')
                ->where('po_number', $po->po_number)
                ->where('customer_code', $po->customer_code)
                ->where('status', 'READY')
                ->sum('quantity');

            $po->total_terkirim = DB::table('deliveries')
                ->whereIn('po_id', $poIds) 
                ->sum('qty_delivery');

            // Hanya tampilkan yang belum lunas (Outstanding > 0)
            if ($po->total_terkirim < $po->total_qty_po) {
                $activePOs[] = $po;
            }
        }

        // SAKTI: Mengelompokkan berdasarkan customer agar tabel di View terpisah
        $groupedPOs = collect($activePOs)->groupBy('customer_code');

        return view('delivery.index', compact('groupedPOs'));
    }

    /**
     * 2. FORM PENERBITAN SJ (TETAP)
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
            $terkirim = DB::table('deliveries')->where('po_id', $item->id)->sum('qty_delivery');
            $item->total_sent = $terkirim;
            $item->sisa_pesanan = $item->quantity - $terkirim;
        }

        return view('delivery.create', compact('po', 'items'));
    }

    /**
     * 3. PROSES SIMPAN & AUTO-PRINT
     */
    public function store(Request $request)
    {
        $items = $request->items; 
        $no_sj = $request->no_sj;
        $po_id = $request->po_id;

        $po_data = DB::table('purchase_orders')->where('id', $po_id)->first();
        
        if (!$po_data) {
            return redirect()->back()->with('error', 'Data Purchase Order tidak ditemukan!');
        }

        $customer_code = $po_data->customer_code;

        DB::beginTransaction();
        try {
            foreach ($items as $part_no => $data) {
                $qty_kirim = $data['qty_kirim'];

                if ($qty_kirim > 0) {
                    $fg = DB::table('finished_goods')->where('part_no', $part_no)->first();

                    if (!$fg || $fg->actual_stock < $qty_kirim) {
                        return redirect()->back()->with('error', "Gagal! Stok Part $part_no tidak mencukupi (Tersedia: $fg->actual_stock)");
                    }

                    DB::table('finished_goods')->where('part_no', $part_no)->decrement('actual_stock', $qty_kirim);

                    DB::table('deliveries')->insert([
                        'po_id' => $po_id,
                        'no_sj' => $no_sj,
                        'part_no' => $part_no,
                        'customer_code' => $customer_code,
                        'qty_delivery' => $qty_kirim,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    DB::table('purchase_orders')->where('po_number', $request->po_header_number)
                        ->where('part_no', $part_no)
                        ->increment('total_sent', $qty_kirim);

                    $status_check = DB::table('purchase_orders')
                        ->where('po_number', $request->po_header_number)
                        ->where('part_no', $part_no)
                        ->first();

                    if ($status_check && $status_check->total_sent >= $status_check->quantity) {
                        DB::table('purchase_orders')->where('id', $status_check->id)->update([
                            'status' => 'CLOSED', 
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            DB::commit();
            
            // SAKTI: Setelah simpan, langsung lempar ke halaman PRINT biar otomatis cetak
            return redirect()->route('delivery.print', $no_sj)
                ->with('success', 'Surat Jalan ' . $no_sj . ' Berhasil Terbit! Siap Dicetak.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * 4. HISTORY SURAT JALAN (TETAP)
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
     * 5. PRINT SURAT JALAN (MENYAMBUNGKAN DATA CUSTOMER)
     */
    public function print($no_sj)
    {
        $items = DB::table('deliveries')->where('no_sj', $no_sj)->get();

        if ($items->isEmpty()) {
            return redirect()->route('delivery.index')->with('error', 'Data SJ tidak ditemukan!');
        }

        $sj = $items->first();
        $po = DB::table('purchase_orders')->where('id', $sj->po_id)->first();

        // SAKTI: Mengambil data Nama PT dan Alamat lengkap dari tabel customers
        $customer = DB::table('customers')->where('code', $sj->customer_code)->first();

        return view('delivery.print', compact('items', 'sj', 'po', 'no_sj', 'customer'));
    }

    /**
     * 6. PRINT REKAP PO (TETAP)
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
}