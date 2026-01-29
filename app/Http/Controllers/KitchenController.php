<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\region;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class KitchenController extends Controller
{
    // Tampilkan halaman dapur
    public function index()
    {
        $canCreateDelete = Auth::user()->hasRole('superadmin');

        $kitchens = Kitchen::with('region')
            ->paginate(10);
        $kodeBaru = $this->generateKode();
        $regions = region::all();

        return view('master.kitchen', compact('kitchens', 'kodeBaru', 'regions', 'canCreateDelete'));
    }

    private function generateKode()
    {
        $lastKode = Kitchen::withTrashed()->max('kode');

        if (!$lastKode) {
            return 'DPR11';
        }

        $lastNumber = (int) substr($lastKode, 3);
        return 'DPR' . ($lastNumber + 1);
    }


    // Simpan data dapur baru
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('superadmin')) {
            abort(403, 'Akses ditolak. Hanya Superadmin yang boleh menambah data.');
        }

        $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'kepala_dapur' => 'required',
            'nomor_kepala_dapur' => 'required',
            'region_id' => 'required|exists:regions,id',
            'kota' => 'required',
        ]);

        // 1. Buat dapur
        $kitchen = Kitchen::create([
            'kode' => $this->generateKode(),
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'kepala_dapur' => $request->kepala_dapur,
            'nomor_kepala_dapur' => $request->nomor_kepala_dapur,
            'region_id' => $request->region_id,
            'kota' => $request->kota,
        ]);

        // 2. Ambil semua superadmin
        $superadmins = User::role('superadmin')->get();

        // 3. Attach dapur baru ke semua superadmin
        foreach ($superadmins as $admin) {
            $admin->kitchens()->syncWithoutDetaching([$kitchen->kode]);
        }

        return redirect()->route('master.kitchen.index')
            ->with('success', 'Data dapur berhasil ditambahkan & otomatis terhubung ke Superadmin.');
    }


    // Update data dapur
    public function update(Request $request, $id)
    {
        if (!Auth::user()->hasAnyRole(['superadmin', 'operatorkoperasi'])) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit data.');
        }

        $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'kepala_dapur' => 'required',
            'nomor_kepala_dapur' => 'required',
            'region_id' => 'required|exists:regions,id',
            'kota' => 'required'
        ]);

        $kitchen = Kitchen::findOrFail($id);

        $kitchen->update([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'kepala_dapur' => $request->kepala_dapur,
            'nomor_kepala_dapur' => $request->nomor_kepala_dapur,
            'region_id' => $request->region_id,
            'kota' => $request->kota,
        ]);

        return redirect()->route('master.kitchen.index')
            ->with('success', 'Data dapur berhasil diperbarui.');
    }

    // Hapus data dapur
    public function destroy($id)
    {

        if (!Auth::user()->hasRole('superadmin')) {
            abort(403, 'Akses ditolak. Hanya Superadmin yang boleh menghapus data.');
        }

        $kitchen = Kitchen::findOrFail($id);
        $kitchen->delete();

        return redirect()->route('master.kitchen.index')
            ->with('success', 'Data dapur berhasil dihapus.');
    }
}
