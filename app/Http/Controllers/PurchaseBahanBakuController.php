<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseBahanBaku;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PurchaseBahanBakuController extends Controller
{


    private function generateKode()
    {
        $last = PurchaseBahanBaku::orderBy('id', 'desc')->first();
        if (!$last) {
            return 'PRCBB001';
        }

        // Ambil angka dari kode terakhir
        $lastNumber = intval(substr($last->kode, -3));
        $newNumber = $lastNumber + 1;

        return 'PRCBB' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    public function index()
    {
        // $purchases = PurchaseBahanBaku::with('supplier')->get();
        // $users = User::all();
        // $suppliers = Supplier::all();
        // $bahanBaku = BahanBaku::with('unit')->get();
        // $satuan = Unit::all();

        return view('transaction.purchase-materials', [
            'purchases' => PurchaseBahanBaku::with(['supplier', 'items'])->latest()->get(),
            'users'     => User::all(),
            'suppliers' => Supplier::all(),
            'bahanBaku' => BahanBaku::with('unit')->get(),
            'kode'      => $this->generateKode(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'supplier' => 'required',
            'bahan' => 'required|array',
        ]);
        DB::transaction(function () use ($request) {

            // 1. Simpan HEADER
            $purchase = PurchaseBahanBaku::create([
                'kode'        => $this->generateKode(),
                'supplier_id' => $request->supplier,
                'user_id'    => Auth::id(),
                'total' => 0,
                'tanggal' => $request->tanggal,
            ]);

            $grandTotal = 0;

            // 2. Simpan DETAIL
            foreach ($request->bahan as $index => $bahanId) {
                $bahan = BahanBaku::findOrFail($bahanId);

                $jumlah = $request->jumlah[$index];
                $harga = $request->harga[$index];

                $subtotal = $harga * $jumlah;
                $grandTotal += $subtotal;

                PurchaseItem::create([
                    'purchase_bahan_bakus_id'   => $purchase->id,
                    'bahan_baku_id' => $bahanId,
                    'jumlah'        => $jumlah,
                    'units_id'       => $bahan->satuan_id,
                    'harga'         => $harga,
                    'subtotal' => $subtotal,
                ]);
            }

            $purchase->update([
                'total' => $grandTotal
            ]);
        });
        return redirect()->back()->with('success', 'Pembelian berhasil disimpan');
    }

    public function show($id)
    {
        $purchase = PurchaseBahanBaku::with([
            'supplier',
            'items.bahanBaku.unit'
        ])->findOrFail($id);

        $purchase->total = $purchase->items->sum('subtotal');
        return response()->json($purchase);
    }

    public function printInvoice($id)
    {
        $purchase = PurchaseBahanBaku::with([
            'supplier',
            'items.bahanBaku.unit',
            'user'
        ])->findOrFail($id);
        $pdf = Pdf::loadView('transaction.invoice-purchase-material', compact('purchase'));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('Invoice-pembelian-bahan baku-' . $purchase->kode . '.pdf');
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {

            $purchase = PurchaseBahanBaku::findOrFail($id);

            // hapus detail dulu
            PurchaseItem::where('purchase_bahan_bakus_id', $purchase->id)->delete();

            // hapus header
            $purchase->delete();
        });

        return redirect()
            ->back()
            ->with('success', 'Data pembelian berhasil dihapus');
    }
}
