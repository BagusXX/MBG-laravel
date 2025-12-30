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

        $user = auth()->user();

        // 1️⃣ Untuk dropdown (kode => nama)
        $kitchens = $user->kitchens()->pluck('nama', 'kode');

        // 2️⃣ Ambil hanya KODENYA saja untuk filter
        $kitchenKode = $kitchens->keys();

        $operationals = operationals::with('kitchen')
            ->whereIn('kitchen_kode', $kitchenKode)
            ->paginate(10);

        $lastOperational = operationals::orderBy('kode', 'desc')->first();

        if (!$lastOperational) {
            $nextKode = 'BOP001';
        } else {
            $lastNumber = (int) substr($lastOperational->kode, -3);
            $nextKode = 'BOP' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return view('master.operational', compact('operationals', 'nextKode', 'kitchens'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'nama' => 'required',
            'harga' => 'required',
            'tempat_beli' => 'required',
            'kitchen_kode' => 'required|exists:kitchens,kode',
        ]);

        if (!$user->kitchens()->where('kode', $request->kitchen_kode)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke dapur ini');
        }


        // ambil kode terakhir
        $lastOperational = operationals::orderBy('kode', 'desc')->first();

        if (!$lastOperational) {
            $kode = 'BOP001';
        } else {
            $lastNumber = (int) substr($lastOperational->kode, -3);
            $kode = 'BOP' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        operationals::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'harga' => $request->harga,
            'tempat_beli' => $request->tempat_beli,
            'kitchen_kode' => $request->kitchen_kode,
        ]);

        return redirect()
            ->route('master.operational.index')
            ->with('success', 'Biaya Operasional berhasil ditambahkan');
    }



    public function update(Request $request, $id)
    {
        $operational = operationals::findOrFail($id);
        $user = auth()->user();

        if (! $user->kitchens()->where('kode', $operational->kitchen_kode)->exists()) {
            abort(403);
        }


        $request->validate([
            'nama' => 'required',
            'harga' => 'required',
            'tempat_beli' => 'required',
            'kitchen_kode' => 'required|exists:kitchens,kode',
        ]);

        $operational->update([
            'nama' => $request->nama,
            'harga' => $request->harga,
            'tempat_beli' => $request->tempat_beli,
            'kitchen_kode' => $request->kitchen_kode,
        ]);

        return redirect()
            ->route('master.operational.index')
            ->with('success', 'Biaya Operasional berhasil diperbarui');
    }

    public function destroy($id)
    {
        $operational = operationals::findOrFail($id);

        if (!auth()->user()->kitchens()->where('kode', $operational->kode_kitchen)->exists()) {
            abort(403);
        }

        // baru hapus operational
        $operational->delete();

        return redirect()
            ->route('master.operational.index')
            ->with('success', 'Biaya Operasional berhasil dihapus');
    }
}
