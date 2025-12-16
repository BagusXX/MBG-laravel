<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Kitchen;
use App\Models\Unit;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    // Tampilkan halaman bahan baku
    public function index()
    {
        $items = BahanBaku::with('kitchen')->get();
        $kitchens = Kitchen::all();
        $units = Unit::all();

        // Pre-generate kode untuk semua dapur
        $generatedCodes = [];
        foreach ($kitchens as $k) {
            $generatedCodes[$k->id] = $this->generateKode($k->kode);
        }

        return view('dashboard.master.bahan-baku.index', compact('items', 'kitchens', 'units', 'generatedCodes'));
    }

    // Generate kode bahan baku: 2 digit + kode dapur
    private function generateKode($kodeDapur)
    {
        // Cari kode terakhir khusus dapur tertentu
        $lastItem = BahanBaku::where('kode', 'LIKE', "BN{$kodeDapur}%")
                            ->orderBy('kode', 'desc')
                            ->first();

        // Jika belum ada data, mulai dari 111
        if (!$lastItem) {
            return 'BN' . $kodeDapur . '111';
        }

        // Ambil 3 digit angka terakhir
        // Contoh kode: BNDPR11555 → ambil '555'
        $lastNumber = (int) substr($lastItem->kode, -3);
        $nextNumber = $lastNumber + 1;

        // Batas maksimum 999
        if ($nextNumber > 999) $nextNumber = 999;

        // Format angka menjadi tiga digit
        $num = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return 'BN' . $kodeDapur . $num;
    }

    // Simpan bahan baku baru
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'kitchen_id' => 'required|exists:kitchens,id',
        ]);

        $kitchen = Kitchen::findOrFail($request->kitchen_id);

        BahanBaku::create([
            'kode' => $this->generateKode($kitchen->kode),
            'nama' => $request->nama,
            'satuan' => $request->satuan,
            'harga' => $request->harga,
            'kitchen_id' => $request->kitchen_id,
        ]);

        return redirect()->route('dashboard.master.bahan-baku.index')
                        ->with('success', 'Bahan baku berhasil ditambahkan.');
    }


    // Hapus bahan baku
    public function destroy($id)
    {
        $item = BahanBaku::findOrFail($id);
        $item->delete();

        return redirect()->route('dashboard.master.bahan-baku.index')
                         ->with('success', 'Bahan baku berhasil dihapus.');
    }
}
