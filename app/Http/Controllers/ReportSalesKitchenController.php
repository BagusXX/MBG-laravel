<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kitchen;
use App\Models\SubmissionDetails;
use App\Models\Supplier;
use SebastianBergmann\CodeCoverage\Report\Xml\Report;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportSalesKitchenController extends Controller
{
    public function index(Request $request)
    {
        $kitchens = Kitchen::all();
        $suppliers = Supplier::all();
        $bahanBakus = BahanBaku::selectRaw('MIN(id) as id, nama')
        ->groupBy('nama') 
        ->get();

        $query = SubmissionDetails::with([
            'submission.kitchen',
            'bahan_baku.unit',
            'submission.supplier',
            'recipeBahanBaku.bahan_baku.unit'
        ]);

        $query->whereHas('submission', function ($q) {
            $q->whereNotNull('parent_id');
        });

        if ($request->filled('from_date')) {
            $query->whereHas('submission', fn ($q) =>
                $q->whereDate('tanggal', '>=', $request->from_date)
            );
        }

        if ($request->filled('to_date')) {
            $query->whereHas('submission', fn ($q) =>
                $q->whereDate('tanggal', '<=', $request->to_date)
            );
        }

        if ($request->filled('kitchen_id')) {
            $query->whereHas('submission', fn ($q) =>
                $q->where('kitchen_id', $request->kitchen_id)
            );
        }
        if ($request->filled('supplier_id')) {
            $query->whereHas('submission', fn ($q) =>
                $q->where('supplier_id', $request->supplier_id)
            );
        }
        if ($request->filled('bahan_baku_id')) {
            $selectedBahan = \App\Models\BahanBaku::find($request->bahan_baku_id);
            
            if ($selectedBahan) {
                $namaBahan = $selectedBahan->nama; 

                $query->where(function ($q) use ($namaBahan) {
                    // Filter 1: Lewat relasi langsung bahanBaku
                    $q->whereHas('bahan_baku', function ($qb) use ($namaBahan) {
                        $qb->where('nama', $namaBahan);
                    })
                    // Filter 2: Lewat relasi resep (Gunakan bahan_baku sesuai modelmu)
                    // Nested relationship: recipeBahanBaku -> bahan_baku
                    ->orWhereHas('recipeBahanBaku.bahan_baku', function ($qr) use ($namaBahan) {
                        $qr->where('nama', $namaBahan);
                    });
                });
            }
        }
        $query->orderByDesc(\App\Models\Submission::select('tanggal')
                ->whereColumn('submissions.id', 'submission_details.submission_id')
                ->limit(1));

        $reports = $query->paginate(10)->withQueryString();

        $reports->getCollection()->transform(function ($item) {
            return $this->applyConversion($item);
        });

        $totalPageSubtotal = $reports->getCollection()->sum(function ($item) {
            return ($item->submission->porsi ?? 0) * ($item->harga_dapur ?? 0);
        });

        return view('report.sales-kitchen', compact('kitchens', 'reports', 'suppliers', 'totalPageSubtotal', 'bahanBakus'));
    }

    public function invoice(Request $request)
    {
        $query = SubmissionDetails::with(['submission.kitchen', 'bahan_baku', 'submission.supplier']); 

        $query->whereHas('submission', function ($q) {
            $q->whereNotNull('parent_id');
        });

    if ($request->from_date && $request->to_date) {
        $query->whereHas('submission', function($q) use ($request) {
            $q->whereBetween('tanggal', [$request->from_date, $request->to_date]);
        });
    }

    if ($request->kitchen_id) {
        $query->whereHas('submission', function($q) use ($request) {
            $q->where('kitchen_id', $request->kitchen_id);
        });
    }

    if ($request->supplier_id) {
        $query->whereHas('submission', function($q) use ($request) {
            $q->where('supplier_id', $request->supplier_id);
        });
    }

    
    $reports = $query->get();

    $today = date('d-m-Y');

    $submission = $reports->first()->submission ?? null;

    $totalPageSubtotal = $reports->sum(function ($item) {
        return ($item->submission->porsi ?? 0) * ($item->harga_dapur ?? 0);
    });

    $pdf = PDF::loadView('report.invoiceReport-sales-kitchen', compact('submission','reports', 'totalPageSubtotal'));

    $pdf->setPaper('a4', 'landscape');

    return $pdf->download('laporan penjualan dapur_' .$today. '.pdf');
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

        return $item;
    }

}
