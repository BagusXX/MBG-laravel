<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\region;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    // Tampilkan halaman dapur
    public function index()
    {
        $kitchens = Kitchen::with('region')->get();
        $kodeBaru = $this->generateKode();
        $regions = region::all();

        return view('master.kitchen', compact('kitchens', 'kodeBaru', 'regions'));
    }

    private function generateKode()
    {
        $lastKitchen = Kitchen::orderBy('id', 'desc')->first();

        if (!$lastKitchen || !$lastKitchen->kode) {
            // Jika belum ada data → langsung DPR11
            return 'DPR11';
        }

        // Ambil angka setelah "DPR", misal "DPR15" → 15
        $lastNumber = (int) substr($lastKitchen->kode, 3);

        // Jika angka terlalu kecil (misal DPR1), paksa kembali ke 11
        if ($lastNumber < 11) {
            $nextNumber = 11;
        } else {
            // Normal increment
            $nextNumber = $lastNumber + 1;
        }

        // Batas maksimum 99
        if ($nextNumber > 99) {
            $nextNumber = 99;
        }

        return 'DPR' . $nextNumber;
    }


    // Simpan data dapur baru
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'kepala_dapur' => 'required',
            'nomor_kepala_dapur' => 'required',
            'region_id' => 'required|exists:regions,id',
        ]);

        Kitchen::create([
            'kode' => $this->generateKode(), // auto-generate dari backend
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'kepala_dapur' => $request->kepala_dapur,
            'nomor_kepala_dapur' => $request->nomor_kepala_dapur,
            'region_id' => $request->region_id,
        ]);

        return redirect()->route('master.kitchen')
                         ->with('success', 'Data dapur berhasil ditambahkan.');
    }

    // Update data dapur
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'kepala_dapur' => 'required',
            'nomor_kepala_dapur' => 'required',
            'region_id' => 'required|exists:regions,id',
        ]);

        $kitchen = Kitchen::findOrFail($id);

        $kitchen->update([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'kepala_dapur' => $request->kepala_dapur,
            'nomor_kepala_dapur' => $request->nomor_kepala_dapur,
            'region_id' => $request->region_id,
        ]);

        return redirect()->route('master.kitchen')
                         ->with('success', 'Data dapur berhasil diperbarui.');
    }

    // Hapus data dapur
    public function destroy($id)
    {
        $kitchen = Kitchen::findOrFail($id);
        $kitchen->delete();

        return redirect()->route('master.kitchen')
                         ->with('success', 'Data dapur berhasil dihapus.');
    }
}
