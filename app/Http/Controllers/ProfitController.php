<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kitchen;
use App\Models\SubmissionDetails;
use App\Models\Supplier;
use App\Models\Submission;
use Barryvdh\DomPDF\Facade\Pdf;

class ProfitController extends Controller
{
    // Fungsi pembantu agar logic filter tidak diulang-ulang
    private function getReportQuery(Request $request, $kitchenCodes)
    {
        $query = SubmissionDetails::with([
            'submission.kitchen',
            'submission.parentSubmission',
            'bahan_baku',
            'submission.supplier',
            'unit'
        ])
            ->whereHas('submission', function ($q) {
                $q->whereNotNull('parent_id');
            });

        // 🔒 Filter kitchen berdasarkan kitchen user login
        $query->whereHas('submission.kitchen', function ($q) use ($kitchenCodes) {
            $q->whereIn('kode', $kitchenCodes);
        });

        // Filter Tanggal
        if ($request->filled('from_date') || $request->filled('to_date')) {
            $query->whereHas('submission.parentSubmission', function ($q) use ($request) {
                if ($request->filled('from_date')) {
                    $q->whereDate('tanggal', '>=', $request->from_date);
                }
                if ($request->filled('to_date')) {
                    $q->whereDate('tanggal', '<=', $request->to_date);
                }
            });
        }

        // Filter Kitchen (dropdown)
        if ($request->filled('kitchen_id')) {
            $query->whereRelation('submission', 'kitchen_id', $request->kitchen_id);
        }

        // Filter Supplier
        if ($request->filled('supplier_id')) {
            $query->whereRelation('submission', 'supplier_id', $request->supplier_id);
        }

        // Sorting di tingkat Database
        return $query->orderByDesc(
            Submission::select('tanggal')
                ->whereColumn('submissions.id', 'submission_details.submission_id')
                ->limit(1)
        )
            ->orderBy('submission_details.submission_id')
            ->orderBy('submission_details.id');
    }

    protected function userKitchenCodes()
    {
        return auth()->user()->kitchens()->pluck('kode');
    }

    public function index(Request $request)
    {
        $kitchenCodes = $this->userKitchenCodes();

        $kitchens = Kitchen::whereIn('kode', $kitchenCodes)->get();
        $suppliers = Supplier::all();

        $reports = $this->getReportQuery($request, $kitchenCodes)
            ->paginate(10)
            ->withQueryString();

        $reports->getCollection()->transform(function ($item) {
            $item->selisih_total = ($item->subtotal_dapur ?? 0) - ($item->subtotal_mitra ?? 0);
            return $item;
        });

        $totalPageSubtotal = $reports->getCollection()->sum('selisih_total');

        return view('report.profit', compact('kitchens', 'reports', 'suppliers', 'totalPageSubtotal'));

    }

    public function invoice(Request $request)
    {
        $kitchenCodes = $this->userKitchenCodes();

        $reports = $this->getReportQuery($request, $kitchenCodes)->get();

        $reports->transform(function ($item) {
            $item->selisih_total = ($item->subtotal_dapur ?? 0) - ($item->subtotal_mitra ?? 0);
            return $item;
        });

        $submission = $reports->first()->submission ?? null;
        $totalPageSubtotal = $reports->sum('selisih_total');
        $today = now()->format('d-m-Y');

        $pdf = PDF::loadView('report.invoiceReport-profit', compact('submission', 'reports', 'totalPageSubtotal'));
        return $pdf->download("laporan_selisih_penjualan_{$today}.pdf");
    }
}