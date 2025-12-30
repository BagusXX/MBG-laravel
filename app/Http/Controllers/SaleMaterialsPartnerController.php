<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Sells;
use App\Models\Kitchen;
use App\Models\BahanBaku;
use App\Models\Unit;

class SaleMaterialsPartnerController extends Controller
{
    public function index()
    {
        $sales = Sells::with(['user', 'kitchen', 'bahanBaku.unit', 'satuan'])
            ->where('tipe', 'mitra')
            ->latest()
            ->get();

        $kitchens = Kitchen::all();
        $units = Unit::all();

        // Generate kode untuk form
        $lastKode = Sells::withTrashed()
            ->where('tipe', 'mitra')
            ->orderByRaw('CAST(SUBSTRING(kode, 3) AS UNSIGNED) DESC')
            ->value('kode');

        $nextKode = $lastKode ? 'SM' . str_pad(((int) substr($lastKode, 2)) + 1, 6, '0', STR_PAD_LEFT) : 'SM000001';

        return view('transaction.sale-materials-partner', compact('sales', 'kitchens', 'units', 'nextKode'));
    }

    public function getBahanByKitchen(Kitchen $kitchen)
    {
        $bahanBaku = BahanBaku::where('kitchen_id', $kitchen->id)
            ->with('unit')
            ->select('id', 'nama', 'harga', 'satuan_id', 'kitchen_id')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'harga' => $item->harga,
                    'satuan_id' => $item->satuan_id,
                    'satuan' => $item->unit ? $item->unit->satuan : null,
                ];
            });

        return response()->json($bahanBaku);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kitchen_id' => 'required|exists:kitchens,id',
            'bahan_id' => 'required|array',
            'bahan_id.*' => 'required|exists:bahan_baku,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'required|numeric|min:1',
            'satuan_id' => 'required|array',
            'satuan_id.*' => 'required|exists:units,id',
            'harga' => 'required|array',
            'harga.*' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Generate kode
            $lastKode = Sells::withTrashed()
                ->where('tipe', 'mitra')
                ->orderByRaw('CAST(SUBSTRING(kode, 3) AS UNSIGNED) DESC')
                ->lockForUpdate()
                ->value('kode');

            $nextNumber = $lastKode ? ((int) substr($lastKode, 2)) + 1 : 1;
            $kode = 'SM' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            // Simpan setiap bahan baku
            foreach ($request->bahan_id as $index => $bahanId) {
                $bahanBaku = BahanBaku::findOrFail($bahanId);
                
                Sells::create([
                    'kode' => $kode,
                    'tanggal' => $request->tanggal,
                    'tipe' => 'mitra',
                    'kitchen_id' => $request->kitchen_id,
                    'bahan_baku_id' => $bahanId,
                    'satuan_id' => $request->satuan_id[$index],
                    'bobot_jumlah' => $request->jumlah[$index],
                    'harga' => $request->harga[$index],
                    'user_id' => Auth::id(),
                ]);
            }
        });

        return redirect()->route('transaction.sale-materials-partner.index')
            ->with('success', 'Penjualan bahan baku mitra berhasil disimpan');
    }
}
