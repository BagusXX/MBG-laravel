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
use App\Models\Menu;
use App\Models\Unit;
use App\Models\Submission;
use App\Models\SubmissionDetails;
use App\Models\Supplier;

class SaleMaterialsPartnerController extends Controller
{
    protected function convertQtyForCalculation(SubmissionDetails $detail): float
    {
        $qty = (float) $detail->qty_digunakan;

        // Ambil bahan baku dari mana pun sumbernya
        $bahanBaku = $detail->bahan_baku
            ?? $detail->recipeBahanBaku?->bahan_baku;

        if (!$bahanBaku || !$bahanBaku->unit) {
            return $qty; // Default jika unit tidak ditemukan
        }

        $unit = strtolower($bahanBaku->unit->satuan);

        return match ($unit) {
            'gram' => $qty / 1000,
            'ml' => $qty / 1000,
            default => $qty,
        };
    }

    protected function formatQtyWithUnit($qty, $unit)
    {
        if (!$unit) {
            return [
                'qty' => $qty,
                'unit' => '-',
            ];
        }

        $satuan = strtolower($unit->satuan);

        // gram → kg
        if ($satuan === 'gram') {
            return [
                'qty' => $qty / 1000,
                'unit' => 'kg',
            ];
        }

        // ml → liter
        if ($satuan === 'ml') {
            return [
                'qty' => $qty / 1000,
                'unit' => 'liter',
            ];
        }

        // default (tidak dikonversi)
        return [
            'qty' => $qty,
            'unit' => $unit->satuan,
        ];
    }

    public function index(Request $request)
    {
        $kitchens = Kitchen::all();
        $suppliers = Supplier::all();
        $bahanBakus = BahanBaku::selectRaw('MIN(id) as id, nama')
            ->groupBy('nama')
            ->get();
        // Ambil submission yang statusnya selesai sebagai data penjualan
        $menus = Menu::selectRaw('MIN(id) as id, nama')
            ->groupBy('nama')
            ->get();
        $query = Submission::with([
            'parentSubmission',
            'kitchen',
            'menu',
            'supplier',
            'details.recipeBahanBaku.bahan_baku.unit',
            'details.bahan_baku.unit'
        ])
            ->whereNotNull('parent_id')
            ->where(function ($q) {
                $q->where('status', 'diproses')
                    ->orWhere('tipe', 'disetujui');
            })
            ->orderByDesc('tanggal');

        if ($request->filled('from_date')) {
            $query->whereDate('tanggal', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('tanggal', '<=', $request->to_date);
        }

        if ($request->filled('kitchen_id')) {
            $query->where('kitchen_id', $request->kitchen_id);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('menu_id')) {
            $selectedMenu = Menu::find($request->menu_id);

            if ($selectedMenu) {
                $query->whereHas('menu', function ($q) use ($selectedMenu) {
                    $q->where('nama', $selectedMenu->nama);
                });
            }
        }

        $submissions = $query->paginate(10)->withQueryString();

        $submissions->getCollection()->each(function ($submission) {
            $submission->details->each(function ($detail) {

                $bahanBaku = $detail->bahan_baku
                    ?? $detail->recipeBahanBaku?->bahan_baku;

                $unit = $bahanBaku?->unit?->satuan;

                // ===== Konversi Qty =====
                $qty = (float) $detail->qty_digunakan;

                if (in_array(strtolower($unit), ['gram', 'ml'])) {
                    $displayQty  = $qty / 1000;
                    $displayUnit = $unit === 'gram' ? 'kg' : 'liter';
                } else {
                    $displayQty  = $qty;
                    $displayUnit = $unit ?? '-';
                }

                // ===== Subtotal =====
                $subtotal = $displayQty * ($detail->harga_mitra ?? 0);

                // ===== Inject ke object =====
                $detail->display_qty     = $displayQty;
                $detail->display_unit    = $displayUnit;
                $detail->subtotal_mitra  = $subtotal;
            });
        });

        $totalPageSubtotal = $submissions->getCollection()->sum('subtotal_mitra');

        return view('transaction.sale-materials-partner', compact('submissions', 'kitchens', 'suppliers', 'totalPageSubtotal', 'bahanBakus', 'menus'));
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

        // dd(submission::where('kode', $kode)->value('status'));

        if (!$submission) {
            abort(404, 'Data penjualan tidak ditemukan');
        }


        $submission->details->each(function ($detail) {

            $bahanBaku = $detail->bahan_baku
                ?? $detail->recipeBahanBaku?->bahan_baku;

            $unit = $bahanBaku?->unit?->satuan;

            // ===== Konversi Qty =====
            $qty = (float)(
                $detail->qty_digunakan
                ?? $detail->qty
                ?? 0
            );

            if (in_array(strtolower($unit), ['gram', 'ml'])) {
                $displayQty  = $qty / 1000;
                $displayUnit = $unit === 'gram' ? 'kg' : 'liter';
            } else {
                $displayQty  = $qty;
                $displayUnit = $unit ?? '-';
            }

            // ===== Subtotal =====
            $subtotal = $displayQty * ($detail->harga_mitra ?? 0);

            // ===== Inject ke object =====
            $detail->display_qty     = $displayQty;
            $detail->display_unit    = $displayUnit;
            $detail->subtotal_mitra  = $subtotal;
        });


        // Hitung total harga dari detail
        $totalHarga = $submission->details->sum('subtotal_mitra');

        $pdf = Pdf::loadView(
            'transaction.invoice-sale-partner',
            compact('submission', 'totalHarga')
        );

        // return view('transaction.invoice-sale-partner', compact('submission', 'totalHarga'));
        return $pdf->download('Invoice-' . $submission->kode . '_' . date('d-m-Y') .'.pdf');
    }

    // public function downloadInvoice($kode)
    // {
    //     // Ambil submission berdasarkan kode
    //     $submission = Submission::with([
    //         'kitchen',
    //         'menu',
    //         'supplier',
    //         'details.recipeBahanBaku.bahan_baku.unit',
    //         'details.bahan_baku.unit'
    //     ])
    //         ->where('kode', $kode)
    //         ->where('status', 'selesai')
    //         ->first();

    //     if (!$submission) {
    //         abort(404, 'Data penjualan tidak ditemukan');
    //     }

    //     // Hitung total harga dari detail
    //     $totalHarga = $submission->details()->get()->sum(function ($detail) {
    //         $hargaMitra = $detail->harga_mitra ?? $detail->harga_satuan_saat_itu ?? 0;
    //         return $hargaMitra * $detail->qty_digunakan;
    //     });

    //     $pdf = Pdf::loadView('transaction.invoice-sale-partner', compact('submission', 'totalHarga'));
    //     $pdf->setPaper('a4', 'portrait');

    //     $filename = 'Invoice_' . $kode . '_' . date('Y-m-d') . '.pdf';

    //     return $pdf->download($filename);
    // }
}
