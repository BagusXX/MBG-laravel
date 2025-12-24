<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseBahanBaku;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = PurchaseBahanBaku::with('supplier')->get();
        $users = User::all();
        $suppliers = Supplier::all();
        return view('transaction.purchase-materials', compact('purchases', 'users', 'suppliers'));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            // 1. Simpan HEADER
            $purchases = PurchaseBahanBaku::create([
                'kode'        => PurchaseBahanBaku::generateKode(),
                'supplier_id' => $request->supplier,
                'user_id'    => Auth::id(),
            ]);

            // 2. Simpan DETAIL
            foreach ($request->bahan as $index => $bahanId) {
                PurchaseItem::create([
                    'purchase_id'   => $purchases->id,
                    'bahan_baku_id' => $bahanId,
                    'jumlah'        => $request->jumlah[$index],
                    'harga'         => $request->harga[$index],
                ]);
            }
        });
        return redirect()->back()->with('success', 'Pembelian berhasil disimpan');
    }
}
