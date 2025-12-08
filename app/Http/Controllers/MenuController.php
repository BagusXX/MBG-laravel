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

    // Generate kode menu: MN + KODE_DAPUR + 2 digit unik
// private function generateKodeMenu($kodeDapur)
// {
//     // Cari menu terakhir untuk dapur tertentu
//     $lastMenu = Menu::where('kode', 'LIKE', 'MN' . $kodeDapur . '%')
//                     ->orderBy('kode', 'desc')
//                     ->first();

//     // Jika belum ada menu → mulai dari 01
//     if (!$lastMenu) {
//         return "MN{$kodeDapur}01";
//     }

//     // Ambil 2 digit terakhir
//     $lastNumber = (int) substr($lastMenu->kode, -2);

//     // Increment
//     $nextNumber = $lastNumber + 1;

//     // Format jadi 2 digit
//     $nextNumberFormatted = str_pad($nextNumber, 2, '0', STR_PAD_LEFT);

//     return "MN{$kodeDapur}{$nextNumberFormatted}";
// }

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

        return redirect()->route('master.menu')->with('success', 'Menu berhasil ditambahkan!');
    }

    // Hapus menu
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return redirect()->route('master.menu')->with('success', 'Menu berhasil dihapus!');
    }
}
