<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Kitchen;

class MenuController extends Controller
{
    // Tampilkan daftar menu
    public function index()
    {
        $menus = Menu::with('kitchen')->get();
        $kitchens = Kitchen::all();

        $generatedCodes = [];
        foreach ($kitchens as $k) {
            $generatedCodes[$k->id] = $this->generateKode($k->kode);
        }

        return view('master.menu', compact('menus', 'kitchens', 'generatedCodes'));
    }


    private function generateKode($kodeDapur)
    {
        // Cari kode terakhir khusus dapur tertentu
        $lastItem = Menu::where('kode', 'LIKE', "MN{$kodeDapur}%")
            ->orderBy('kode', 'desc')
            ->first();

        // Jika belum ada data, mulai dari 111
        if (!$lastItem) {
            return 'MN' . $kodeDapur . '111';
        }

        // Ambil 3 digit angka terakhir
        // Contoh kode: BNDPR11555 → ambil '555'
        $lastNumber = (int) substr($lastItem->kode, -3);
        $nextNumber = $lastNumber + 1;

        // Batas maksimum 999
        if ($nextNumber > 999) $nextNumber = 999;

        // Format angka menjadi tiga digit
        $num = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return 'MN' . $kodeDapur . $num;
    }


    // Simpan menu baru
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kitchen_id' => 'required|exists:kitchens,id' // dapur wajib dipilih
        ]);

        // Ambil kode dapur dari tabel dapur
        $kitchen = Kitchen::findOrFail($request->kitchen_id);

        // Generate kode menu
        $kodeMenu = $this->generateKode($kitchen->kode);

        Menu::create([
            'kode' => $kodeMenu,
            'nama' => $request->nama,
            'kitchen_id' => $request->kitchen_id,
        ]);

        return redirect()->route('master.menu.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kitchen_id' => 'required|exists:kitchens,id',
        ]);

        $menu = Menu::findOrFail($id);

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
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return redirect()->route('master.menu.index')->with('success', 'Menu berhasil dihapus.');
    }
}
