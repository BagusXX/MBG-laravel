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

    return view('master.menu', compact('menus', 'kitchens'));
}




    // Generate kode menu: MNDPR11 + 001
    private function generateKodeMenu($kodeDapur)
    {
        // Cari menu terakhir untuk dapur tertentu
        $lastMenu = Menu::where('kode', 'LIKE', 'MN' . $kodeDapur . '%')
                        ->orderBy('kode', 'desc')
                        ->first();

        // Jika belum ada → mulai dari 001
        if (!$lastMenu) {
            return "MN{$kodeDapur}001";
        }

        // Ambil nomor terakhir 3 digit (ambil 3 digit paling belakang)
        $lastNumber = (int) substr($lastMenu->kode, -3);

        // Increment
        $nextNumber = $lastNumber + 1;

        // Format jadi 3 digit
        $nextNumberFormatted = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return "MN{$kodeDapur}{$nextNumberFormatted}";
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
        $kodeMenu = $this->generateKodeMenu($kitchen->kode);

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
