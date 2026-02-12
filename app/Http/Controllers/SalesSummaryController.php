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
            ->whereNull('parent_id')
            ->has('children')
            ->whereIn('kitchen_id', $kitchensCodes)
            ->with([
                'kitchen',
                'supplier',
                'children.details'
            ]);
        
        if ($request->filled('from_date')) {
            $query->whereDate('tanggal', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('tanggal', '<=', $request->to_date);
        }


        if ($request->filled('kitchen_id')) {
            $query->where('kitchen_id', $request->kitchen_id);
        }

         $parents = $query
            ->orderByDesc('tanggal')
            ->paginate(10)
            ->withQueryString();

        // HITUNG TOTAL DARI CHILD
        $parents->getCollection()->transform(function ($parent) {

            $totalDapur = 0;
            $totalMitra = 0;

            foreach ($parent->children as $child) {
                $totalDapur += $child->details->sum('subtotal_dapur');
                $totalMitra += $child->details->sum('subtotal_mitra');
            }

            $parent->total_dapur = $totalDapur;
            $parent->total_mitra = $totalMitra;
            $parent->selisih = $totalDapur - $totalMitra;
            $parent->persen_85 = $parent->selisih * 0.85;
            $parent->persen_15 = $parent->selisih * 0.15;

            return $parent;
        });

        // TOTAL FOOTER (HALAMAN AKTIF)
        $collection = $parents->getCollection();

        $totalSelisih = $collection->sum('selisih');
        $totalPersen85 = $collection->sum('persen_85');
        $totalPersen15 = $collection->sum('persen_15');

        return view('report.sales-summary', compact(
            'kitchens',
            'parents',
            'totalSelisih',
            'totalPersen85',
            'totalPersen15'
        ));
    }
}
