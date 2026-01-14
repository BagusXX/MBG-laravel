<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kitchen;
use App\Models\SubmissionDetails;
use App\Models\Supplier;

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

        $reports = $query->get();

        return view('report.sales-kitchen', compact('kitchens', 'reports', 'suppliers'));
    }
}
