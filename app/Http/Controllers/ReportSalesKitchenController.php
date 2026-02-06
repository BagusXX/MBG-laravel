<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kitchen;
use App\Models\SubmissionDetails;
use App\Models\Submission;
use App\Models\Supplier;
use App\Models\Menu;
use SebastianBergmann\CodeCoverage\Report\Xml\Report;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportSalesKitchenController extends Controller
{
    protected function userKitchenCodes()
    {
        $allowedCodes = auth()->user()->kitchens()->pluck('kode');
        return Kitchen::whereIn('kode', $allowedCodes)->pluck('id')->toArray();

    }
    public function index(Request $request)
    {
        $kitchensCodes = $this->userKitchenCodes();

        $kitchens = Kitchen::whereIn('id', $kitchensCodes)->orderBy('nama')->get();
        $suppliers = Supplier::all();
        $bahanBakus = BahanBaku::selectRaw('MIN(id) as id, nama')
            ->groupBy('nama')
            ->get();
        $menus = Menu::selectRaw('MIN(id) as id, nama')
            ->groupBy('nama')
            ->get();

        $query = SubmissionDetails::with([
            'submission.parentSubmission',
            'submission.kitchen',
            'submission.menu',
            'submission.supplier',
            'bahan_baku.unit'
        ])
        // ->whereNotNull('parent_id')
        ->whereIn('kitchen_id', $kitchensCodes)
        ->where(function ($q) use ($request) {
            $q->whereHas('submission', function($sub) {
                $sub->whereNotNull('parent_id');
            });
            $q->where(function ($q2) {
                $q2->where('status', 'selesai')
                    ->orWhere('tipe', 'disetujui');
            });

                if ($request->filled('from_date') || $request->filled('to_date')) {
                    $q->whereHas('submission.parentSubmission', function ($ps) use ($request) {

                    if ($request->filled('from_date')) {
                        $ps->whereDate('tanggal', '>=', $request->from_date);
                    }

                    if ($request->filled('to_date')) {
                        $ps->whereDate('tanggal', '<=', $request->to_date);
                    }

                    });
                }

                if ($request->filled('kitchen_id')) {
                    $q->where('kitchen_id', $request->kitchen_id);
                }
                if ($request->filled('supplier_id')) {
                    $q->where('supplier_id', $request->supplier_id);
                }
                if ($request->filled('menu_id')) {
                    $selectedMenu = Menu::find($request->menu_id);

                    if ($selectedMenu) {
                        $q->whereHas('menu', function ($mq) use ($selectedMenu) {
                            $mq->where('nama', $selectedMenu->nama);
                        });
                    }
                }
            })
            ->latest('id');

        $submissions = $query->paginate(10)->withQueryString();

        $submissions->getCollection()->each(function ($submission) {
            $submission->details->each(function ($detail) {

                $bahanBaku = $detail->bahan_baku
                    ?? $detail->recipeBahanBaku?->bahan_baku;

                $unit = $bahanBaku?->unit?->satuan;

                // ===== Konversi Qty =====
                $qty = (float) $detail->qty_digunakan;

                if (in_array(strtolower($unit), ['gram', 'ml'])) {
                    $displayQty = $qty / 1000;
                    $displayUnit = $unit === 'gram' ? 'kg' : 'liter';
                } else {
                    $displayQty = $qty;
                    $displayUnit = $unit ?? '-';
                }

                // ===== Subtotal =====
                $subtotal = $displayQty * ($detail->harga_dapur ?? 0);

                // ===== Inject ke object =====
                $detail->display_qty = $displayQty;
                $detail->display_unit = $displayUnit;
                $detail->subtotal_dapur = $subtotal;
            });
        });

        $totalPageSubtotal = $submissions->getCollection()->sum('subtotal_dapur');

        return view('report.sales-kitchen', compact('submissions', 'kitchens', 'suppliers', 'totalPageSubtotal', 'bahanBakus', 'menus'));
    }

    public function invoice(Request $request)
    {
        $kitchenCodes = $this->userKitchenCodes();

        $query = SubmissionDetails::with(['submission.kitchen', 'bahan_baku.unit', 'submission.supplier', 'recipeBahanBaku.bahan_baku.unit']);

        $query->whereHas('submission', function ($q) use ($kitchenCodes) {
            $q->whereNotNull('parent_id')
                ->whereIn('kitchen_id', $kitchenCodes);

        });

        if ($request->from_date && $request->to_date) {
            $query->whereHas('submission', function ($q) use ($request) {
                $q->whereBetween('tanggal', [$request->from_date, $request->to_date]);
            });
        }

        if ($request->kitchen_id) {
            $query->whereHas('submission', function ($q) use ($request) {
                $q->where('kitchen_id', $request->kitchen_id);
            });
        }

        if ($request->supplier_id) {
            $query->whereHas('submission', function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });
        }


        $reports = $query->get();

        $reports->transform(function ($item) {
            return $this->applyConversion($item);
        });

        $reports = $reports->sortByDesc(function ($item) {
            return $item->submission->tanggal;
        });

        $today = date('d-m-Y');

        $submission = $reports->first()->submission ?? null;

        $totalPageSubtotal = $reports->sum('subtotal');

        $pdf = PDF::loadView('report.invoiceReport-sales-kitchen', compact('submission', 'reports', 'totalPageSubtotal'));

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('laporan penjualan dapur_' . $today . '.pdf');
    }

    private function applyConversion($item)
    {
        // 1. Ambil Nama Satuan
        $unitNama = '-';
        if ($item->recipeBahanBaku && $item->recipeBahanBaku->bahan_baku) {
            $unitNama = optional($item->recipeBahanBaku->bahan_baku->unit)->satuan;
        } elseif ($item->bahan_baku) {
            $unitNama = optional($item->bahan_baku->unit)->satuan;
        }

        $unitLower = strtolower($unitNama);
        $qty = $item->qty_digunakan;

        // 2. Logika Konversi ke Kg / L
        if ($unitLower == 'gram') {
            $item->display_unit = 'Kg';
            $item->display_qty = $qty / 1000;
        } elseif ($unitLower == 'ml') {
            $item->display_unit = 'L';
            $item->display_qty = $qty / 1000;
        } else {
            $item->display_unit = $unitNama;
            $item->display_qty = $qty;
        }

        // 3. Format Angka (Gunakan koma untuk desimal, hilangkan desimal jika bulat)
        $item->formatted_qty = number_format(
            $item->display_qty,
            ($item->display_qty == floor($item->display_qty) ? 0 : 2),
            ',',
            '.'
        );

        $item->subtotal = ($item->display_qty ?? 0) * ($item->harga_dapur ?? 0);

        return $item;
    }
}
