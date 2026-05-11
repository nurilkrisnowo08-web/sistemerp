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
            // Ambil semua ID yang berhubungan dengan nomor PO ini
            $poIds = DB::table('purchase_orders')
                ->where('po_number', $po->po_number)
                ->where('customer_code', $po->customer_code)
                ->pluck('id');

            $po->total_qty_po = DB::table('purchase_orders')
                ->whereIn('id', $poIds)
                ->sum('quantity');

            // Hitung total terkirim berdasarkan kumpulan ID PO tersebut
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
            // ✨ FIX: Tambahkan filter part_no agar tidak menjumlahkan part lain dalam PO yang sama
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
     * 3. PROSES SIMPAN & AUTO-PRINT
     */
    public function store(Request $request)
    {
        $items = $request->items; 
        $no_sj = $request->no_sj;
        $po_number = $request->po_header_number;

        DB::beginTransaction();
        try {
            foreach ($items as $part_no => $data) {
                $qty_kirim = $data['qty_kirim'];

                if ($qty_kirim > 0) {
                    // Cari ID spesifik untuk part ini di tabel PO
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

                    // 2. Simpan ke Deliveries menggunakan ID PO yang spesifik per Part
                    DB::table('deliveries')->insert([
                        'po_id' => $po_item->id,
                        'no_sj' => $no_sj,
                        'part_no' => $part_no,
                        'customer_code' => $po_item->customer_code,
                        'qty_delivery' => $qty_kirim,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // 3. Update total_sent di tabel purchase_orders untuk baris part tersebut
                    DB::table('purchase_orders')->where('id', $po_item->id)->increment('total_sent', $qty_kirim);

                    // 4. Cek status lunas per Part
                    $updated_po = DB::table('purchase_orders')->where('id', $po_item->id)->first();
                    if ($updated_po && $updated_po->total_sent >= $updated_po->quantity) {
                        DB::table('purchase_orders')->where('id', $po_item->id)->update([
                            'status' => 'CLOSED', 
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('delivery.print', $no_sj)
                ->with('success', 'Surat Jalan ' . $no_sj . ' Berhasil Terbit!');

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

        // Cari data PO berdasarkan po_id yang tersimpan di baris delivery
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
        // Ambil item dari surat jalan ini
        $items = DB::table('deliveries')->where('no_sj', $no_sj)->get();

        if ($items->isEmpty()) {
            return redirect()->route('delivery.history')->with('error', 'Data SJ tidak ditemukan!');
        }

        $sj = $items->first();

        // Ambil data customer
        $customer = DB::table('customers')->where('code', $sj->customer_code)->first();

        // Ambil detail part_name dan blank_size dari Master Part untuk setiap item
        foreach ($items as $item) {
            $p = DB::table('parts')->where('part_no', $item->part_no)->first();
            $item->part_name = $p->part_name ?? 'N/A';
            $item->blank_size = $p->blank_size ?? '-'; // Sesuaikan kolom blank_size di tabel parts Anda
        }

        return view('delivery.label', compact('items', 'sj', 'no_sj', 'customer'));
    }
}