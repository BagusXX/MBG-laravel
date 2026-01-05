<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sells;
use App\Models\Kitchen;
use App\Models\BahanBaku;
use App\Models\Unit;
use App\Models\Submission;

class SaleMaterialsPartnerController extends Controller
{
    public function index()
    {
        // Ambil submission yang statusnya selesai sebagai data penjualan
        $submissions = Submission::with(['kitchen', 'menu', 'details.recipe.bahan_baku.unit'])
            ->where('status', 'selesai')
            ->latest()
            ->get();

        // Group by kode untuk menghindari duplikasi di tabel
        $sales = $submissions->groupBy('kode')->map(function ($group) {
            return $group->first();
        })->values();

        return view('transaction.sale-materials-partner', compact('sales', 'submissions'));
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

    public function printInvoice($kode)
    {
        $sales = Sells::with(['user', 'kitchen', 'bahanBaku.unit', 'satuan'])
            ->where('tipe', 'mitra')
            ->where('kode', $kode)
            ->get();

        if ($sales->isEmpty()) {
            abort(404, 'Data penjualan tidak ditemukan');
        }

        $totalHarga = $sales->sum(function ($sale) {
            return $sale->harga * $sale->bobot_jumlah;
        });

        return view('transaction.invoice-sale-partner', compact('sales', 'totalHarga'));
    }

    public function downloadInvoice($kode)
    {
        $sales = Sells::with(['user', 'kitchen', 'bahanBaku.unit', 'satuan'])
            ->where('tipe', 'mitra')
            ->where('kode', $kode)
            ->get();

        if ($sales->isEmpty()) {
            abort(404, 'Data penjualan tidak ditemukan');
        }

        $totalHarga = $sales->sum(function ($sale) {
            return $sale->harga * $sale->bobot_jumlah;
        });

        $pdf = Pdf::loadView('transaction.invoice-sale-partner', compact('sales', 'totalHarga'));
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'Invoice_' . $kode . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
}
