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
            'purchases' => PurchaseBahanBaku::with('supplier')->get(),
            'users'     => User::all(),
            'suppliers' => Supplier::all(),
            'bahanBaku' => BahanBaku::with('unit')->get(),
            'kode'      => $this->generateKode(),
        ]);
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            // 1. Simpan HEADER
            $purchase = PurchaseBahanBaku::create([
                'kode'        => $this->generateKode(),
                'supplier_id' => $request->supplier,
                'user_id'    => Auth::id(),
            ]);

            // 2. Simpan DETAIL
            foreach ($request->bahan as $index => $bahanId) {
                $bahan = BahanBaku::findOrFail($bahanId);

                PurchaseItem::create([
                    'purchase_bahan_bakus_id'   => $purchase->id,
                    'bahan_baku_id' => $bahanId,
                    'jumlah'        => $request->jumlah[$index],
                    'units_id'       => $bahan->satuan_id,
                    'harga'         => $request->harga[$index],
                ]);
            }
        });
        return redirect()->back()->with('success', 'Pembelian berhasil disimpan');
    }

    public function show($id)
    {
        $purchase = PurchaseBahanBaku::with([
            'supplier',
            'items.bahanBaku.unit'
        ])->findOrFail($id);

        return response()->json($purchase);
    }
}
