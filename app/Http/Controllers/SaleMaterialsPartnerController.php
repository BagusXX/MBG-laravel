<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sells;
use App\Models\Kitchen;
use App\Models\BahanBaku;
use App\Models\Menu;
use App\Models\Unit;
use App\Models\Submission;
use App\Models\SubmissionDetails;
use App\Models\Supplier;

class SaleMaterialsPartnerController extends Controller
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
        $suppliers = Supplier::all();
        $bahanBakus = BahanBaku::selectRaw('MIN(id) as id, nama')
            ->groupBy('nama')
            ->get();
        // Ambil submission yang statusnya selesai sebagai data penjualan
        $menus = Menu::selectRaw('MIN(id) as id, nama')
            ->groupBy('nama')
            ->get();
        $query = Submission::with([
            'parentSubmission',
            'kitchen',
            'menu',
            'supplier',
            'details.bahan_baku',
            'details.unit'
        ])
            ->whereNotNull('parent_id')
            ->whereIn('kitchen_id', $kitchensCodes)
            ->where(function ($q) use ($request) {
                $q->where(function ($q2) {
                    $q2->where('status', 'diproses')
                        ->orWhere('tipe', 'disetujui');
                });

                if ($request->filled('from_date') || $request->filled('to_date')) {
                    $q->whereHas('submission.parentSubmission', function ($ps) use ($request) {

                    if ($request->filled('from_date')) {
                        $ps->whereDate('tanggal', '>=', $request->from_date);
                    }

                    if ($request->filled('to_date')) {
                        $ps->whereDate('tanggal', '<=', $request->to_date);
                    }

                    });
                }
                if ($request->filled('kitchen_id')) {
                    $q->where('kitchen_id', $request->kitchen_id);
                }
                if ($request->filled('supplier_id')) {
                    $q->where('supplier_id', $request->supplier_id);
                }
                if ($request->filled('menu_id')) {
                    $selectedMenu = Menu::find($request->menu_id);

                    if ($selectedMenu) {
                        $q->whereHas('menu', function ($mq) use ($selectedMenu) {
                            $mq->where('nama', $selectedMenu->nama);
                        });
                    }
                }
            })
            ->latest('id');

        $submissions = $query->paginate(10)->withQueryString();

        $totalPageSubtotal = $submissions->getCollection()->sum(function ($submission) {
            return $submission->details->sum('subtotal_mitra');
        });

        return view('transaction.sale-materials-partner', compact('submissions', 'kitchens', 'suppliers', 'totalPageSubtotal', 'bahanBakus', 'menus'));
    }

    public function getBahanByKitchen(Kitchen $kitchen)
    {
        $bahanBaku = BahanBaku::where('kitchen_id', $kitchen->id)
            ->select('id', 'nama', 'kitchen_id')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                ];
            });

        return response()->json($bahanBaku);
    }

    

    public function printInvoice($kode)
    {
        $kitchensCodes = $this->userKitchenCodes();
        // Ambil submission berdasarkan kode
        $submission = Submission::with([
            'parentSubmission',
            'kitchen',
            'menu',
            'supplier',
            'details.bahan_baku',
            'details.unit'
        ])
            ->onlyChild()
            ->where('kode', $kode)
            ->whereIn('kitchen_id', $kitchensCodes)
            ->where('status', 'diproses')
            ->first();

        // dd(submission::where('kode', $kode)->value('status'));

        if (!$submission) {
            abort(404, 'Data penjualan tidak ditemukan');
        }


        

        // Hitung total harga dari detail
        $totalHarga = $submission->details->sum('subtotal_mitra');

        $pdf = Pdf::loadView(
            'transaction.invoice-sale-partner',
            compact('submission', 'totalHarga')
        );

        // return view('transaction.invoice-sale-partner', compact('submission', 'totalHarga'));
        return $pdf->download('Invoice-' . $submission->kode . '_' . date('d-m-Y') . '.pdf');
    }

    
}
