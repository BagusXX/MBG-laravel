<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Kitchen;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    // Tampilkan daftar menu
    public function index(Request $request)
    {
        $user = auth()->user();
        $canManage = $this->canManage();

        $kitchens = $user->kitchens()->get();

        // Default: Ambil semua ID dapur milik user
        $kitchenIds = $kitchens->pluck('id');

        if ($request->filled('kitchen_kode')) {
        $selectedKitchen = $kitchens->where('kode', $request->kitchen_kode)->first();
        
            if ($selectedKitchen) {
                // Jika valid, filter hanya untuk ID dapur yang dipilih
                $kitchenIds = collect([$selectedKitchen->id]);
            } else {
                // Jika tidak valid (misal dimanipulasi di URL), kosongkan ID
                $kitchenIds = collect([]);
            }
        }

        $query = Menu::with('kitchen')
            ->withCount('recipes')
            ->whereIn('kitchen_id', $kitchenIds);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                    ->orWhere('kode', 'LIKE', "%{$search}%");
            });
        }

        $items = $query->paginate(10)->withQueryString();

        $generatedCodes = [];
        foreach ($kitchens as $k) {
            $generatedCodes[$k->id] = $this->generateKode($k->kode);
        }

        return view('master.menu', compact('kitchens', 'generatedCodes', 'items', 'canManage'));
    }


    private function generateKode($kodeDapur)
    {
        // 1. Cari angka awal dari item terakhir
        // Kita gunakan ->withTrashed() (jika ada) atau query biasa untuk safety
        $query = Menu::query();

        // Cek jika model menggunakan SoftDeletes, sertakan data sampah
        if (method_exists($query->getModel(), 'bootSoftDeletes')) {
            $query->withTrashed();
        }

        $lastItem = $query->where('kode', 'LIKE', "MN{$kodeDapur}%")
            ->orderBy('kode', 'desc')
            ->first();

        $number = 111; // Default mulai

        if ($lastItem) {
            // Ambil 3 digit terakhir
            $lastNumber = (int) substr($lastItem->kode, -3);
            $number = $lastNumber + 1;
        }

        // 2. LAKUKAN LOOPING PENGECEKAN (SOLUSI ANTI DUPLICATE)
        // Terus mencari sampai menemukan kode yang belum dipakai
        while (true) {
            // Batas 999
            if ($number > 999)
                $number = 999;

            // Format kode
            $tryCode = 'MN' . $kodeDapur . str_pad($number, 3, '0', STR_PAD_LEFT);

            // Cek apakah kode ini ada di database (termasuk yang di-soft delete)
            $checkQuery = Menu::where('kode', $tryCode);

            if (method_exists($checkQuery->getModel(), 'bootSoftDeletes')) {
                $checkQuery->withTrashed();
            }

            $exists = $checkQuery->exists();

            if (!$exists) {
                // Jika TIDAK ADA, berarti kode aman digunakan
                return $tryCode;
            }

            // Jika SUDAH ADA, naikkan angka dan coba lagi loop berikutnya
            $number++;
        }
    }


    // Simpan menu baru
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$this->canManage()) {
            abort(403, 'Anda tidak memiliki akses untuk menambah data.');
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'kitchen_id' => 'required|exists:kitchens,id' // dapur wajib dipilih
        ]);

        if (!$user->kitchens()->where('kitchens.id', $request->kitchen_id)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke dapur ini');
        }

        // Ambil kode dapur dari tabel dapur
        $kitchen = Kitchen::findOrFail($request->kitchen_id);

        // Generate kode menu
        $kodeMenu = $this->generateKode($kitchen->kode);

        Menu::create([
            'kode' => $kodeMenu,
            'nama' => $request->nama,
            'kitchen_id' => $request->kitchen_id,
        ]);

        return redirect()
            ->route('master.menu.index')
            ->with('success', 'Menu berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if (!$this->canManage()) {
            abort(403, 'Anda tidak memiliki akses untuk menambah data.');
        }

        if (!$user->kitchens()->where('kitchens.id', $request->kitchen_id)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke dapur ini');
        }



        $menu = Menu::findOrFail($id);

        if ($menu->recipes()->exists()) {
            return back()->withErrors('Menu tidak bisa diubah karena sudah memiliki resep.');
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'kitchen_id' => 'required|exists:kitchens,id',
        ]);


        // jika dapur berubah → generate ulang kode
        if ($menu->kitchen_id != $request->kitchen_id) {
            $kitchen = Kitchen::findOrFail($request->kitchen_id);
            $menu->kode = $this->generateKode($kitchen->kode);
        }

        $menu->update([
            'nama' => $request->nama,
            'kitchen_id' => $request->kitchen_id,
        ]);

        return redirect()
            ->route('master.menu.index')
            ->with('success', 'Menu berhasil diperbarui.');
    }


    // Hapus menu
    public function destroy($id)
    {
        if (!$this->canManage()) {
            abort(403, 'Anda tidak memiliki akses untuk menambah data.');
        }

        $menu = Menu::findOrFail($id);

        if ($menu->recipes()->exists()) {
            return back()->withErrors('Menu tidak bisa dihapus karena sudah terdaftar dalam resep.');
        }

        if (!auth()->user()->kitchens()->where('kitchens.id', $menu->kitchen_id)->exists()) {
            abort(403);
        }

        $menu->delete();

        return redirect()->route('master.menu.index')->with('success', 'Menu berhasil dihapus.');
    }

    private function canManage()
    {
        $user = Auth::user();
        // Pastikan user memiliki salah satu dari role ini
        return $user->hasAnyRole(['superadmin', 'operatorDapur']);
    }
}
