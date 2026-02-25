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

        // Data untuk Filter
        $kitchens = Kitchen::whereIn('id', $kitchensCodes)->orderBy('nama')->get();
        $suppliers = Supplier::all();
        $bahanBakus = BahanBaku::selectRaw('MIN(id) as id, nama')->groupBy('nama')->get();
        $menus = Menu::selectRaw('MIN(id) as id, nama')->groupBy('nama')->get();

        $query = SubmissionDetails::with([
            'submission.parentSubmission',
            'submission.kitchen',
            'submission.menu',
            'submission.supplier',
            'bahan_baku',
            'unit'
        ]);

        $query->whereHas('submission', function ($sub) use ($kitchensCodes, $request) {
            // Filter status dan tipe harus di dalam sini
            $sub->whereNotNull('parent_id')
                ->whereIn('kitchen_id', $kitchensCodes)
                ->where(function ($q) {
                    $q->where('status', 'selesai')
                        ->orWhere('tipe', 'disetujui');
                });
            // FILTER DINAMIS: Hanya jalan jika user memilih sesuatu di dropdown
            if ($request->filled('kitchen_id')) {
                $sub->where('kitchen_id', $request->kitchen_id);
            }

            if ($request->filled('supplier_id')) {
                $sub->where('supplier_id', $request->supplier_id);
            }
        });

        // Filter Tanggal
        if ($request->filled('from_date') || $request->filled('to_date')) {
            $query->whereHas('submission.parentSubmission', function ($ps) use ($request) {
                if ($request->filled('from_date'))
                    $ps->whereDate('tanggal', '>=', $request->from_date);
                if ($request->filled('to_date'))
                    $ps->whereDate('tanggal', '<=', $request->to_date);
            });
        }


        if ($request->filled('menu_id')) {
            $selectedMenu = Menu::find($request->menu_id);
            if ($selectedMenu) {
                $query->whereHas('submission.menu', function ($mq) use ($selectedMenu) {
                    $mq->where('nama', $selectedMenu->nama);
                });
            }
        }

        // FILTER BAHAN BAKU (Kolom ini ada langsung di tabel detail, jadi tidak perlu whereHas)
        if ($request->filled('bahan_baku_id')) {
            $query->where('bahan_baku_id', $request->bahan_baku_id);
        }

        $submissions = $query->latest('id')->paginate(10)->withQueryString();

        // Kalkulasi Total per Halaman menggunakan kolom subtotal_harga dari DB
        $totalPageSubtotal = $submissions->sum('subtotal_dapur');

        return view('report.sales-kitchen', compact('submissions', 'kitchens', 'suppliers', 'totalPageSubtotal', 'bahanBakus', 'menus'));
    }

    public function invoice(Request $request)
    {
        $kitchenCodes = $this->userKitchenCodes();

        $query = SubmissionDetails::with([
            'submission.kitchen',
            'bahan_baku',
            'submission.supplier',
            'submission.menu',
            'unit'
        ])->whereHas('submission', function ($q) use ($kitchenCodes) {
            $q->whereNotNull('parent_id')
                ->whereIn('kitchen_id', $kitchenCodes);
        });

        // Filter Tambahan
        if ($request->from_date && $request->to_date) {
            $query->whereHas('submission', function ($q) use ($request) {
                $q->whereBetween('tanggal', [$request->from_date, $request->to_date]);
            });
        }
        if ($request->kitchen_id)
            $query->whereHas('submission', function ($q) use ($request) {
                $q->where('kitchen_id', $request->kitchen_id);
            });
        if ($request->supplier_id)
            $query->whereHas('submission', function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });

        $reports = $query->get()->sortByDesc('submission.tanggal');
        $totalPageSubtotal = $reports->sum('subtotal_dapur');
        $submission = $reports->first()->submission ?? null;
        $today = date('d-m-Y');

        $pdf = PDF::loadView('report.invoiceReport-sales-kitchen', compact('submission', 'reports', 'totalPageSubtotal'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('laporan_penjualan_dapur_' . $today . '.pdf');
    }
}
