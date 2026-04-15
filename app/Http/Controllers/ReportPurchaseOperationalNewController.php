<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Kitchen;
use App\Models\submissionOperational;
use Illuminate\Http\Request;

class ReportPurchaseOperationalNewController extends Controller
{
    protected function userKitchenCodes()
    {
        return auth()->user()->kitchens()->pluck("kode")->toArray();
    }

    public function index(Request $request)
    {
        $kitchenCodes = $this->userKitchenCodes();
        $kitchens = Kitchen::whereIn('kode', $kitchenCodes)->orderBy('nama')->get();
        $query = submissionOperational::onlyParent()
            ->has('children')
            ->where('status' , 'selesai')
            ->whereIn('kitchen_kode', $kitchenCodes)
            ->with(['kitchen' , 'children.supplier']);
        
        if ($request->filled('from_date')) {
            $query->whereDate('tanggal', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('tanggal', '<=', $request->to_date);
        }
        if ($request->filled('kitchen_kode')) {
            $query->where('kitchen_kode', $request->kitchen_kode);
        }
        // Urutkan berdasarkan tanggal terbaru
        $reports = $query->orderByDesc('tanggal')
            ->paginate(10)
            ->withQueryString();
        // --- MENGHITUNG PEMBAGIAN 98% dan 2% ---
        $reports->getCollection()->transform(function ($report) {
            $totalChild = $report->children->sum('total_harga');

            $report->total_dapur = $totalChild;
            $report->persen_98 = $totalChild * 0.98;
            $report->persen_2 = $totalChild * 0.02;
            
            return $report;
        });
        // --- MENGHITUNG TOTAL FOOTER PADA HALAMAN AKTIF ---
        $collection = $reports->getCollection();
        $totalPersen98 = $collection->sum('persen_98');
        $totalPersen2 = $collection->sum('persen_2');
        return view('report.purchase-operational-new', compact(
            'kitchens',
            'reports',
            'totalPersen98',
            'totalPersen2'
        ));
    }
}
