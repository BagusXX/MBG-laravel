<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kitchen;
use App\Models\SubmissionDetails;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BahanBaku;

class ReportSalesPartnerController extends Controller
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
            'bahan_baku',
            'submission.supplier',
            'recipeBahanBaku.bahan_baku'
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

        $totalPageSubtotal = $reports->sum(function ($item) {
            return ($item->submission->porsi ?? 0) * ($item->harga_mitra ?? 0);
        });

        return view('report.sales-partner', compact('kitchens', 'reports', 'suppliers', 'totalPageSubtotal', 'bahanBakus'));
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
        return ($item->submission->porsi ?? 0) * ($item->harga_mitra ?? 0);
    });

    $pdf = PDF::loadView('report.invoiceReport-sales-partner', compact('submission','reports', 'totalPageSubtotal'));

    return $pdf->download('laporan penjualan mitra_' .$today. '.pdf');
    }
}
