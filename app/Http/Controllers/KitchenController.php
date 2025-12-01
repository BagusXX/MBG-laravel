<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    // Tampilkan halaman dapur
    public function index()
    {
        $kitchens = Kitchen::all();
        return view('master.kitchen', compact('kitchens'));
    }

    // Simpan data dapur baru
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'kepala_dapur' => 'required',
            'nomor_kepala_dapur' => 'required',
        ]);

        Kitchen::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'kepala_dapur' => $request->kepala_dapur,
            'nomor_kepala_dapur' => $request->nomor_kepala_dapur,
        ]);

        return redirect()->route('master.kitchen')
                         ->with('success', 'Data dapur berhasil ditambahkan!');
    }

    // Update data dapur
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'kepala_dapur' => 'required',
            'nomor_kepala_dapur' => 'required',
        ]);

        $kitchen = Kitchen::findOrFail($id);

        $kitchen->update([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'kepala_dapur' => $request->kepala_dapur,
            'nomor_kepala_dapur' => $request->nomor_kepala_dapur,
        ]);

        return redirect()->route('master.kitchen')
                         ->with('success', 'Data dapur berhasil diperbarui!');
    }

    // Hapus data dapur
    public function destroy($id)
    {
        $kitchen = Kitchen::findOrFail($id);
        $kitchen->delete();

        return redirect()->route('master.kitchen')
                         ->with('success', 'Data dapur berhasil dihapus!');
    }
}
