<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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

        $query = SubmissionDetails::with([
            'submission.kitchen',
            'bahanBaku',
            'submission.supplier'
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

        $reports = $query->paginate(10)->withQueryString();

        $totalPageSubtotal = $reports->getCollection()->sum(function ($item) {
            return ($item->submission->porsi ?? 0) * ($item->harga_dapur ?? 0);
        });

        return view('report.sales-kitchen', compact('kitchens', 'reports', 'suppliers', 'totalPageSubtotal'));
    }

    public function invoice(Request $request)
    {
        $query = SubmissionDetails::with(['submission.kitchen', 'bahanBaku', 'submission.supplier']); 

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

    return $pdf->download('laporan penjualan dapur_' .$today. '.pdf');
    }
}
