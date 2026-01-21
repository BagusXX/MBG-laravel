<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Kitchen;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BahanBakuController extends Controller
{
    // Tampilkan halaman bahan baku
    public function index(Request $request)
    {
        $user = Auth::user();
        $kitchens = $user->kitchens;
        $kitchenCodes = $kitchens->pluck('kode');
        $kitchenIds = $kitchens->pluck('id');


        $query = BahanBaku::with(['kitchen', 'unit'])->whereIn('kitchen_id', $kitchenIds);

        if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('nama', 'LIKE', "%{$search}%")
              ->orWhere('kode', 'LIKE', "%{$search}%");
        });
    }

        $items = $query->paginate(10)->withQueryString();
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
        if ($nextNumber > 999)
            $nextNumber = 999;

        // Format angka menjadi tiga digit
        $num = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return 'BN' . $kodeDapur . $num;
    }

    // Simpan bahan baku baru
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nama' => 'required|string|max:255',
            'harga' => 'nullable|numeric|min:0',
            'satuan_id' => 'required|exists:units,id',
            'kitchen_id' => 'required|exists:kitchens,id',
        ]);

        $kitchen = Kitchen::findOrFail($request->kitchen_id);

        // ❗ AUTH CHECK PAKAI KODE
        if (!$user->kitchens()->where('kode', $kitchen->kode)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke kitchen ini');
        }

        BahanBaku::create([
            'kode' => $this->generateKode($kitchen->kode),
            'nama' => $request->nama,
            'harga' => $request->input('harga',0),
            'satuan_id' => $request->satuan_id,
            'kitchen_id' => $request->kitchen_id,
        ]);

        return redirect()->route('dashboard.master.bahan-baku.index')
            ->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {

        $user = Auth::user();

        $item = BahanBaku::with('kitchen')->findOrFail($id);

        if (!$user->kitchens()->where('kode', $item->kitchen->kode)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke data ini');
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'satuan_id' => 'required|exists:units,id',
            'kitchen_id' => 'required|exists:kitchens,id',
        ]);

        $data = [
            'nama' => $request->nama,
            'harga' => $request->harga,
            'satuan_id' => $request->satuan_id,
            'kitchen_id' => $request->kitchen_id,
        ];

        if ($item->kitchen_id != $request->kitchen_id) {
            $kitchen = Kitchen::findOrFail($request->kitchen_id);
            $data['kode'] = $this->generateKode($kitchen->kode);
        }

        $item->update($data);


        return redirect()
            ->route('dashboard.master.bahan-baku.index')
            ->with('success', 'Bahan baku berhasil diperbarui.');
    }


    // Hapus bahan baku
    public function destroy($id)
    {
        $user = Auth::user();

        $item = BahanBaku::with('kitchen')->findOrFail($id);

        if (!$user->kitchens()->where('kode', $item->kitchen->kode)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke data ini');
        }


        $item->delete();

        return redirect()->route('dashboard.master.bahan-baku.index')
            ->with('success', 'Bahan baku berhasil dihapus.');
    }
}
