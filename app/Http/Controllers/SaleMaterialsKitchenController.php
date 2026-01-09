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

class SaleMaterialsKitchenController extends Controller
{
    public function index()
    {
        // Ambil submission yang statusnya selesai sebagai data penjualan
        $submissions = Submission::with([
            'kitchen', 
            'menu', 
            'details.recipe.bahan_baku.unit',
            'details.bahanBaku.unit'
        ])
            ->where('status', 'selesai')
            ->latest()
            ->paginate(10);

        // Group by kode untuk menghindari duplikasi di tabel
        $sales = $submissions->groupBy('kode')->map(function ($group) {
            return $group->first();
        })->values();

        return view('transaction.sale-materials-kitchen', compact('sales', 'submissions'));
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

    public function getSubmissionDetails(Submission $submission)
    {
        // Pastikan submission status selesai
        if ($submission->status !== 'selesai') {
            abort(403, 'Hanya submission yang sudah selesai yang dapat digunakan');
        }

        $details = $submission->details()->with([
            'recipe.bahan_baku.unit',
            'bahanBaku.unit'
        ])->get();

        return response()->json([
            'submission' => [
                'id' => $submission->id,
                'kode' => $submission->kode,
                'tanggal' => $submission->tanggal,
                'kitchen_id' => $submission->kitchen_id,
                'kitchen_nama' => $submission->kitchen->nama,
            ],
            'details' => $details->map(function ($detail) {
                $bahanBakuNama = $detail->recipe?->bahan_baku?->nama ?? $detail->bahanBaku?->nama ?? '-';
                $satuan = $detail->recipe?->bahan_baku?->unit?->satuan ?? $detail->bahanBaku?->unit?->satuan ?? '-';
                $bahanBakuId = $detail->recipe?->bahan_baku_id ?? $detail->bahan_baku_id ?? null;
                $satuanId = $detail->recipe?->bahan_baku?->satuan_id ?? $detail->bahanBaku?->satuan_id ?? null;
                
                return [
                    'bahan_baku_id' => $bahanBakuId,
                    'bahan_baku_nama' => $bahanBakuNama,
                    'qty_digunakan' => $detail->qty_digunakan,
                    'satuan_id' => $satuanId,
                    'satuan' => $satuan,
                    'harga_dapur' => $detail->harga_dapur ?? $detail->harga_satuan_saat_itu ?? 0,
                ];
            })
        ]);
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
                ->where('tipe', 'dapur')
                ->orderByRaw('CAST(SUBSTRING(kode, 3) AS UNSIGNED) DESC')
                ->lockForUpdate()
                ->value('kode');

            $nextNumber = $lastKode ? ((int) substr($lastKode, 2)) + 1 : 1;
            $kode = 'SJ' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            // Simpan setiap bahan baku
            foreach ($request->bahan_id as $index => $bahanId) {
                $bahanBaku = BahanBaku::findOrFail($bahanId);
                
                Sells::create([
                    'kode' => $kode,
                    'tanggal' => $request->tanggal,
                    'tipe' => 'dapur',
                    'kitchen_id' => $request->kitchen_id,
                    'bahan_baku_id' => $bahanId,
                    'satuan_id' => $request->satuan_id[$index],
                    'bobot_jumlah' => $request->jumlah[$index],
                    'harga' => $request->harga[$index],
                    'user_id' => Auth::id(),
                ]);
            }
        });

        return redirect()->route('transaction.sale-materials-kitchen.index')
            ->with('success', 'Penjualan bahan baku dapur berhasil disimpan');
    }

    public function printInvoice($kode)
    {
        // Ambil submission berdasarkan kode
        $submission = Submission::with([
            'kitchen',
            'menu',
            'details.recipe.bahan_baku.unit',
            'details.bahanBaku.unit'
        ])
            ->where('kode', $kode)
            ->where('status', 'selesai')
            ->first();

        if (!$submission) {
            abort(404, 'Data penjualan tidak ditemukan');
        }

        // Hitung total harga dari detail
        $totalHarga = $submission->details->sum(function ($detail) {
            $hargaDapur = $detail->harga_dapur ?? $detail->harga_satuan_saat_itu ?? 0;
            return $hargaDapur * $detail->qty_digunakan;
        });

        return view('transaction.invoice-sale-kitchen', compact('submission', 'totalHarga'));
    }

    public function downloadInvoice($kode)
    {
        // Ambil submission berdasarkan kode
        $submission = Submission::with([
            'kitchen',
            'menu',
            'details.recipe.bahan_baku.unit',
            'details.bahanBaku.unit'
        ])
            ->where('kode', $kode)
            ->where('status', 'selesai')
            ->first();

        if (!$submission) {
            abort(404, 'Data penjualan tidak ditemukan');
        }

        // Hitung total harga dari detail
        $totalHarga = $submission->details->sum(function ($detail) {
            $hargaDapur = $detail->harga_dapur ?? $detail->harga_satuan_saat_itu ?? 0;
            return $hargaDapur * $detail->qty_digunakan;
        });

        $pdf = Pdf::loadView('transaction.invoice-sale-kitchen', compact('submission', 'totalHarga'));
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'Invoice_' . $kode . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
}
