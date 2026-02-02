<?php

namespace App\Http\Controllers;

use App\Models\region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegionController extends Controller
{
    public function index()
    {

        $canManage = $this->canManage();

        $regions = Region::paginate(10);

        $lastRegion = Region::orderBy('kode_region', 'desc')->first();

        if (!$lastRegion) {
            $nextKode = 'RGN01';
        } else {
            $lastNumber = (int) substr($lastRegion->kode_region, -2);
            $nextKode = 'RGN' . str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
        }

        return view('master.region', compact('regions', 'nextKode', 'canManage'));
    }

    public function store(Request $request)
    {
        if (!$this->canManage()) {
            abort(403, 'Anda tidak memiliki akses untuk menambah data.');
        }

        $request->validate([
            'nama_region' => 'required',
            'penanggung_jawab' => 'required',
        ]);

        // ambil kode terakhir
        $lastRegion = Region::withTrashed()
            ->orderBy('kode_region', 'desc')
            ->first();

        if (!$lastRegion) {
            $kode = 'RGN01';
        } else {
            $lastNumber = (int) substr($lastRegion->kode_region, -2);
            $kode = 'RGN' . str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
        }

        Region::create([
            'kode_region' => $kode,
            'nama_region' => $request->nama_region,
            'penanggung_jawab' => $request->penanggung_jawab,
        ]);

        return redirect()
            ->route('master.region.index')
            ->with('success', 'Region berhasil ditambahkan');
    }

    public function destroy($id)
    {
        if (!$this->canManage()) {
            abort(403, 'Anda tidak memiliki akses untuk menambah data.');
        }

        Region::findOrFail($id)->delete();

        return redirect()
            ->route('master.region.index')
            ->with('success', 'Region berhasil dihapus');
    }
    public function update(Request $request, $id)
    {
        if (!$this->canManage()) {
            abort(403, 'Anda tidak memiliki akses untuk menambah data.');
        }

        $request->validate([
            'nama_region' => 'required',
            'penanggung_jawab' => 'required',
        ]);

        $region = Region::findOrFail($id);
        $region->update([
            'kode_region' => $request->kode_region,
            'nama_region' => $request->nama_region,
            'penanggung_jawab' => $request->penanggung_jawab,
        ]);

        return redirect()
            ->route('master.region.index')
            ->with('success', 'Region berhasil diperbarui');
    }

    private function canManage()
    {
        $user = Auth::user();
        // Pastikan user memiliki salah satu dari role ini
        return $user->hasAnyRole(['superadmin']);
    }
}
