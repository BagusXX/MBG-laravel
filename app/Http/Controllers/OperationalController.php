<?php

namespace App\Http\Controllers;

use App\Models\operationals;
use App\Models\Recipe;
use App\Models\RecipeBahanBaku;
use Illuminate\Http\Request;

class OperationalController extends Controller
{
    public function index()
    {
        $operationals = operationals::all();

        $lastOperational = operationals::orderBy('kode', 'desc')->first();

        if (!$lastOperational) {
            $nextKode = 'BOP001';
        } else {
            $lastNumber = (int) substr($lastOperational->kode, -3);
            $nextKode = 'BOP' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return view('master.operational', compact('operationals', 'nextKode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'harga' => 'required',
            'tempat_beli' => 'required',
        ]);

        // ambil kode terakhir
        $lastOperational = operationals::orderBy('kode', 'desc')->first();

        if (!$lastOperational) {
            $kode = 'BOP001';
        } else {
            $lastNumber = (int) substr($lastOperational->kode, -3);
            $kode = 'BOP' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        operationals::create([
            'kode' => $kode,
            'nama' => $request->nama,
            'harga' => $request->harga,
            'tempat_beli' => $request->tempat_beli,
        ]);

        return redirect()
            ->route('master.operational.index')
            ->with('success', 'Biaya Operasional berhasil ditambahkan');
    }

    public function destroy($id)
    {
        $recipe = RecipeBahanBaku::findOrFail($id);

        // hapus relasi pivot dulu
        $recipe->bahanBaku()->detach();

        // baru hapus recipe
        $recipe->delete();

        return redirect()
            ->route('master.recipe.index')
            ->with('success', 'Recipe berhasil dihapus');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required',
            'harga' => 'required',
            'tempat_beli' => 'required',
        ]);

        $region = operationals::findOrFail($id);
        $region->update([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'harga' => $request->harga,
            'tempat_beli' => $request->tempat_beli,
        ]);

        return redirect()
            ->route('master.operationals.index')
            ->with('success', 'Biaya Operasional berhasil diperbarui');
    }
}
