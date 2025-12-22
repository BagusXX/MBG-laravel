<?php

namespace App\Http\Controllers;

use App\Models\region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::all();

        $lastRegion = Region::orderBy('kode_region', 'desc')->first();

        if (!$lastRegion) {
            $nextKode = 'RGN01';
        } else {
            $lastNumber = (int) substr($lastRegion->kode_region, -2);
            $nextKode = 'RGN' . str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
        }

        return view('master.region', compact('regions', 'nextKode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_region' => 'required',
            'penanggung_jawab' => 'required',
        ]);

        // ambil kode terakhir
        $lastRegion = Region::orderBy('kode_region', 'desc')->first();

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
        Region::findOrFail($id)->delete();

        return redirect()
            ->route('master.region.index')
            ->with('success', 'Region berhasil dihapus');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_region' => 'required',
            'penanggung_jawab' => 'required',
        ]);

        $region = Region::findOrFail($id);
        $region->update([
            'nama_region' => $request->nama_region,
            'penanggung_jawab' => $request->penanggung_jawab,
        ]);

        return redirect()
            ->route('master.region.index')
            ->with('success', 'Region berhasil diperbarui');
    }
}
