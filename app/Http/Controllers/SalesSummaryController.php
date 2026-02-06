<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kitchen;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;


class SalesSummaryController extends Controller
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
        $query = Submission::query()
            ->whereNull('parent_id') // PARENT TRANSAKSI
            ->whereIn('kitchen_id', $kitchensCodes)
            ->with('kitchen');

        if ($request->filled('from_date') || $request->filled('to_date')) {
            $query->whereHas('submission.parentSubmission', function ($ps) use ($request) {

            if ($request->filled('from_date')) {
                $ps->whereDate('tanggal', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $ps->whereDate('tanggal', '<=', $request->to_date);
            }

            });
        }

        if ($request->filled('kitchen_id')) {
            $query->where('kitchen_id', $request->kitchen_id);
        }

        $reports = $query
            ->select([
                'submissions.id',
                'submissions.kode',
                'submissions.tanggal',
                'submissions.kitchen_id',

                DB::raw('
                    SUM(submission_details.qty_digunakan * submission_details.harga_dapur)
                    as total_dapur
                '),

                DB::raw('
                    SUM(submission_details.qty_digunakan * submission_details.harga_mitra)
                    as total_mitra
                ')
            ])
            ->join('submission_details', 'submission_details.submission_id', '=', 'submissions.id')
            ->groupBy(
                'submissions.id',
                'submissions.kode',
                'submissions.tanggal',
                'submissions.kitchen_id'
            )
            ->orderByDesc('submissions.id')
            ->paginate(10)
            ->withQueryString();

        $reports->getCollection()->transform(function ($item) {
            $item->selisih = ($item->total_dapur ?? 0) - ($item->total_mitra ?? 0);
            $item->persen_85 = $item->selisih * 0.85;
            $item->persen_15 = $item->selisih * 0.15;
            return $item;
        });

        $collection =$reports->getCollection();

        $totalSelisih = $collection->sum('selisih');
        $totalPersen85 = $collection->sum('persen_85');
        $totalPersen15 = $collection->sum('persen_15');

        return view('report.sales-summary', compact('kitchens','reports', 'totalSelisih', 'totalPersen85', 'totalPersen15'));
    }
}
