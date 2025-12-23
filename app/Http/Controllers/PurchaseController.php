<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with('supplier')->get();
        $users = User::all();
        return view('transaction.purchase-materials', compact('purchases', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required',
            'tanggal' => 'required | date',
            'supplier' => 'required',
        ]);

        Purchase::create([
            'kode' => $request->kode,
            'tanggal' => $request->created_at,
            'supplier_id' => $request->supplier,
            'user_id' => $request->user_id,
        ]);
    }
}
