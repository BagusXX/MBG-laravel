<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Kitchen;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    // Tampilkan halaman bahan baku
    public function index()
    {
        // Ambil semua bahan beserta relasi dapur
        $items = BahanBaku::with('kitchen')->get();
        $kitchens = Kitchen::all(); // Untuk dropdown pilih dapur saat tambah

        return view('dashboard.master.bahan-baku.index', compact('items', 'kitchens'));
    }

    // Generate kode bahan baku: 2 digit + kode dapur
    private function generateKode($kodeDapur)
    {
        // Cari kode terakhir untuk dapur tertentu
        $lastItem = BahanBaku::where('kode', 'LIKE', "%{$kodeDapur}")
                              ->orderBy('kode', 'desc')
                              ->first();

        if (!$lastItem) {
            return '01'.$kodeDapur; // mulai dari 01
        }

        // Ambil 2 digit pertama dari kode terakhir
        $lastNumber = (int) substr($lastItem->kode, 0, 2);
        $nextNumber = $lastNumber + 1;

        if ($nextNumber > 99) $nextNumber = 99; // batas maksimum 99

        return str_pad($nextNumber, 2, '0', STR_PAD_LEFT) . $kodeDapur;
    }

    // Simpan bahan baku baru
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            // 'stok' => 'required|numeric',
            'satuan' => 'required|string|max:50',
            'kitchen_id' => 'required|exists:kitchens,id',
        ]);

        $kitchen = Kitchen::findOrFail($request->kitchen_id);

        BahanBaku::create([
            'kode' => $this->generateKode($kitchen->kode), // kode otomatis
            'nama' => $request->nama,
            // 'stok' => $request->stok,
            'satuan' => $request->satuan,
            'kitchen_id' => $request->kitchen_id,
        ]);

        return redirect()->route('master.materials')
                         ->with('success', 'Bahan baku berhasil ditambahkan');
    }

    // Hapus bahan baku
    public function destroy($id)
    {
        $item = BahanBaku::findOrFail($id);
        $item->delete();

        return redirect()->route('master.materials')
                         ->with('success', 'Bahan baku berhasil dihapus');
    }
}
