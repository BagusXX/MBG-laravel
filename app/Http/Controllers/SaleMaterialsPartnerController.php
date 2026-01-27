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
        $submissions = Submission::with([
            'parentSubmission',
            'kitchen', 
            'menu',
            'supplier',
            'details.recipeBahanBaku.bahan_baku.unit',
            'details.bahan_baku.unit'
        ])
            ->onlyChild()
            ->where('status', 'diproses')
            ->latest()
            ->paginate(10);

        // Group by kode untuk menghindari duplikasi di tabel
        // $sales = $submissions->groupBy('kode')->map(function ($group) {
        //     return $group->first();
        // })->values();

        return view('transaction.sale-materials-partner', compact('submissions'));
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
        // Ambil submission berdasarkan kode
        $submission = Submission::with([
            'parentSubmission',
            'kitchen',
            'menu',
            'supplier',
            'details.recipeBahanBaku.bahan_baku.unit',
            'details.bahan_baku.unit'
        ])
            ->onlyChild()
            ->where('kode', $kode)
            ->where('status', 'diproses')
            ->first();

        if (!$submission) {
            abort(404, 'Data penjualan tidak ditemukan');
        }

        // Hitung total harga dari detail
        $totalHarga = $submission->details->sum(function ($detail) {
            $hargaMitra = $detail->harga_mitra ?? $detail->harga_satuan_saat_itu ?? 0;
            return $hargaMitra * $detail->qty_digunakan;
        });

        $pdf = Pdf::loadView(
            'transaction.invoice-sale-partner',
            compact('submission', 'totalHarga')
        );

        // return view('transaction.invoice-sale-partner', compact('submission', 'totalHarga'));
        return $pdf->stream('Invoice-' . $submission->kode . '.pdf');
    }

    public function downloadInvoice($kode)
    {
        // Ambil submission berdasarkan kode
        $submission = Submission::with([
            'kitchen',
            'menu',
            'supplier',
            'details.recipeBahanBaku.bahan_baku.unit',
            'details.bahan_baku.unit'
        ])
            ->where('kode', $kode)
            ->where('status', 'selesai')
            ->first();

        if (!$submission) {
            abort(404, 'Data penjualan tidak ditemukan');
        }

        // Hitung total harga dari detail
        $totalHarga = $submission->details->sum(function ($detail) {
            $hargaMitra = $detail->harga_mitra ?? $detail->harga_satuan_saat_itu ?? 0;
            return $hargaMitra * $detail->qty_digunakan;
        });

        $pdf = Pdf::loadView('transaction.invoice-sale-partner', compact('submission', 'totalHarga'));
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'Invoice_' . $kode . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
}
