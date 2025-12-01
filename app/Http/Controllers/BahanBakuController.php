<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    public function index()
    {
        $items = BahanBaku::all();
        return view('dashboard.master.bahan-baku.index', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'stok' => 'required|numeric',
            'satuan' => 'required|string|max:50',
        ]);

        BahanBaku::create([
            'nama' => $request->nama,
            'stok' => $request->stok,
            'satuan' => $request->satuan,
        ]);

        return redirect()->route('master.materials')->with('success', 'Bahan baku berhasil ditambahkan');
    }

    public function destroy($id)
    {
        $item = BahanBaku::findOrFail($id);
        $item->delete();

        return redirect()->route('master.materials')->with('success', 'Bahan baku berhasil dihapus');
    }
}
