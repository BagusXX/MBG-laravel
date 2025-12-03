<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    public function index()
    {
        $items = BahanBaku::all();
        // $kodeBaru = $this->generateKode();
        
        return view('dashboard.master.bahan-baku.index', compact('items'));
    }

    // private function generateKode()
    // {
    //     $lastKitchen = Kitchen::orderBy('id', 'desc')->first();

    //     if (!$lastKitchen || !$lastKitchen->kode) {
    //         // Jika belum ada data → langsung DPR11
    //         return 'DPR11';
    //     }

    //     // Ambil angka setelah "DPR", misal "DPR15" → 15
    //     $lastNumber = (int) substr($lastKitchen->kode, 3);

    //     // Jika angka terlalu kecil (misal DPR1), paksa kembali ke 11
    //     if ($lastNumber < 11) {
    //         $nextNumber = 11;
    //     } else {
    //         // Normal increment
    //         $nextNumber = $lastNumber + 1;
    //     }

    //     // Batas maksimum 99
    //     if ($nextNumber > 99) {
    //         $nextNumber = 99;
    //     }

    //     return 'DPR' . $nextNumber;
    // }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'stok' => 'required|numeric',
            'satuan' => 'required|string|max:50',
        ]);

        BahanBaku::create([
            // 'kode' => $this->generateKode(), // auto-generate dari backend

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
