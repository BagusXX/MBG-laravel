<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu; // Pastikan ada model Menu

class MenuController extends Controller
{
    // Tampilkan daftar menu
    public function index()
    {
        $menus = Menu::all(); // Ambil semua menu
        return view('master.menu', compact('menus'));
    }

    // Simpan menu baru
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        Menu::create([
            'nama' => $request->nama,
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
