<?php

namespace App\Http\Controllers;

use App\Models\operationals;
use App\Models\Recipe;
use App\Models\RecipeBahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OperationalController extends Controller
{
    public function index(Request $request)
    {

        $user = auth()->user();

        $canManage = $this->canManage();

        // 1️⃣ Untuk dropdown (kode => nama)
        $kitchens = $user->kitchens()->get();
        // 2️⃣ Ambil hanya KODENYA saja untuk filter
        $kitchenKode = $kitchens->pluck('kode');

        if ($request->filled('kitchen_kode')) {
        $selectedKitchen = $kitchens->where('kode', $request->kitchen_kode)->first();
        
            if ($selectedKitchen) {
                // Jika user memfilter dapur yang valid, timpa array dengan satu kode saja
                $kitchenKode = collect([$selectedKitchen->kode]);
            } else {
                // Jika tidak valid, kosongkan
                $kitchenKode = collect([]);
            }
        }

        $operationals = operationals::with('kitchen')
            ->whereIn('kitchen_kode', $kitchenKode);

        $lastOperational = operationals::orderBy('kode', 'desc')->first();

        if (!$lastOperational) {
            $nextKode = 'BOP001';
        } else {
            $lastNumber = (int) substr($lastOperational->kode, -3);
            $nextKode = 'BOP' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $operationals->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                    ->orWhere('kode', 'LIKE', "%{$search}%");
            });
        }

        $items = $operationals->paginate(10)->withQueryString();

        return view('master.operational', compact('items', 'nextKode', 'kitchens', 'canManage'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$this->canManage()) {
            abort(403, 'Anda tidak memiliki akses untuk menambah data.');
        }

        $request->validate([
            'nama' => 'required',
            'kitchen_kode' => 'required|exists:kitchens,kode',
            'harga_default' => 'nullable|numeric|min:0',
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
            'kitchen_kode' => $request->kitchen_kode,
            'harga_default' => $request->input('harga_defult', 0),
        ]);

        return redirect()
            ->route('master.operational.index')
            ->with('success', 'Biaya Operasional berhasil ditambahkan');
    }



    public function update(Request $request, $id)
    {
        $operational = operationals::findOrFail($id);
        $user = auth()->user();

        if (!$this->canManage()) {
            abort(403, 'Anda tidak memiliki akses untuk menambah data.');
        }

        if (! $user->kitchens()->where('kode', $operational->kitchen_kode)->exists()) {
            abort(403);
        }


        $request->validate([
            'nama' => 'required',
            'kitchen_kode' => 'required|exists:kitchens,kode',
            'harga_default' => 'nullable|numeric|min:0',
        ]);

        $operational->update([
            'nama' => $request->nama,
            'kitchen_kode' => $request->kitchen_kode,
            'harga_default' => $request->input('harga_default', 0),
        ]);

        return redirect()
            ->route('master.operational.index')
            ->with('success', 'Biaya Operasional berhasil diperbarui');
    }

    public function destroy($id)
    {
        $operational = operationals::findOrFail($id);

        if (!auth()->user()->kitchens()->where('kode', $operational->kitchen_kode)->exists()) {
            abort(403);
        }

        if (!$this->canManage()) {
            abort(403, 'Anda tidak memiliki akses untuk menambah data.');
        }

        // baru hapus operational
        $operational->delete();

        return redirect()
            ->route('master.operational.index')
            ->with('success', 'Biaya Operasional berhasil dihapus');
    }

    private function canManage()
    {
        $user = Auth::user();
        // Pastikan user memiliki salah satu dari role ini
        return $user->hasAnyRole(['superadmin', 'operatorDapur']);
    }
}
