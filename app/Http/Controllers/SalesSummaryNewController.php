<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kitchen;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;


class SalesSummaryNewController extends Controller
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
        
        // if ($request->filled('from_date')) {
        //     $query->where(function ($q) use ($request) {
        //         $q->whereDate('tanggal', '>=', $request->from_date)
        //           ->orWhereDate('tanggal_digunakan', '>=', $request->from_date);
        //     });
        // }
        
        // if ($request->filled('to_date')) {
        //     $query->where(function ($q) use ($request) {
        //         $q->whereDate('tanggal', '<=', $request->to_date)
        //           ->orWhereDate('tanggal_digunakan', '<=', $request->to_date);
        //     });
        // }

        $query->whereDate('tanggal', '>=', '2026-04-01');
        
        if ($request->filled('from_date')) {
            $query->whereDate('tanggal_digunakan', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('tanggal_digunakan', '<=', $request->to_date);
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
            // $totalMitra = 0;

            foreach ($parent->children as $child) {
                $totalDapur += $child->details->sum('subtotal_dapur');
                // $totalMitra += $child->details->sum('subtotal_mitra');
            }

            $parent->total_dapur = $totalDapur;
            // $parent->total_mitra = $totalMitra;
            // $parent->selisih = $totalDapur - $totalMitra;
            $parent->persen_98 = $parent->total_dapur * 0.98;
            $parent->persen_2 = $parent->total_dapur * 0.02;

            return $parent;
        });

        // TOTAL FOOTER (HALAMAN AKTIF)
        $collection = $parents->getCollection();

        // $totalSelisih = $collection->sum('selisih');
        $totalPersen98 = $collection->sum('persen_98');
        $totalPersen2 = $collection->sum('persen_2');

        return view('report.sales-summary-new', compact(
            'kitchens',
            'parents',
            // 'totalSelisih',
            'totalPersen98',
            'totalPersen2'
        ));
    }
}
