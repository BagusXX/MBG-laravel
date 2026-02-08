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
use App\Models\Unit;
use App\Models\Submission;
use App\Models\SubmissionDetails;
use App\Models\Supplier;
use App\Models\Menu;

class ReportSalesProfitController extends Controller
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

        // Mengambil nama unik dengan lebih aman untuk strict mode
        $bahanBakus = BahanBaku::select('nama')->distinct()->orderBy('nama')->get();
        $menus = Menu::select('nama')->distinct()->orderBy('nama')->get();

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
                // Filter Status/Tipe
                $q->where(function ($q2) {
                    $q2->where('status', 'diproses')
                        ->orWhere('tipe', 'disetujui');
                });

                // Filter Tanggal
                if ($request->filled(['from_date', 'to_date'])) {
                    $q->whereHas('parentSubmission', function ($ps) use ($request) {
                        $ps->whereBetween('tanggal', [$request->from_date, $request->to_date]);
                    });
                }

                // Filter Dropdown
                if ($request->filled('kitchen_id')) $q->where('kitchen_id', $request->kitchen_id);
                if ($request->filled('supplier_id')) $q->where('supplier_id', $request->supplier_id);

                if ($request->filled('menu_id')) {
                    // Gunakan join atau whereHas yang lebih efisien jika data besar
                    $q->whereHas('menu', function ($mq) use ($request) {
                        $mq->where('id', $request->menu_id);
                    });
                }
            })
            ->latest('id');

        $submissions = $query->paginate(10)->withQueryString();

        // Pastikan attribute 'selisih' sudah didefinisikan di Model Submission (Accessor)
        $totalPageSubtotal = $submissions->sum('selisih');

        return view('report.sales-profit', compact('submissions', 'kitchens', 'suppliers', 'totalPageSubtotal', 'bahanBakus', 'menus'));
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

    public function getSubmissionDetails(Submission $submission)
    {
        // Pastikan submission status selesai
        if ($submission->status !== 'selesai') {
            abort(403, 'Hanya submission yang sudah selesai yang dapat digunakan');
        }

        $details = $submission->details()->with([
            'bahan_baku',
            'unit'
        ])->get();

        return response()->json([
            'submission' => [
                'id' => $submission->id,
                'kode' => $submission->kode,
                'tanggal' => $submission->tanggal,
                'kitchen_id' => $submission->kitchen_id,
                'kitchen_nama' => $submission->kitchen->nama,
            ],
            'details' => $details->map(function ($detail) {
                $bahanBakuNama = $detail->bahan_baku?->nama ?? '-';
                $satuan = $detail->satuan ?? '-';
                $bahanBakuId = $detail->bahan_baku_id ?? null;
                $qty  = $detail->qty ?? null;


                // $satuanId = $detail->recipe?->bahan_baku?->satuan_id ?? $detail->bahanBaku?->satuan_id ?? null;


                return [
                    'bahan_baku_id' => $bahanBakuId,
                    'bahan_baku_nama' => $bahanBakuNama,
                    'qty_digunakan' => $qty,
                    'satuan_id' => $detail->satuan_id,
                    'satuan' => $satuan,
                    'harga_dapur' => $detail->subtotal_dapur ?? 0,
                ];
            })
        ]);
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

        if (!$submission) {
            abort(404, 'Data penjualan tidak ditemukan');
        }


        $totalHarga = $submission->details->sum('selisih');

        $pdf = Pdf::loadView(
            'report.invoiceReport-sales-profit',
            compact('submission', 'totalHarga')
        );

        // return view('transaction.invoice-sale-kitchen', compact('submission', 'totalHarga'));
        return $pdf->download('Invoice-' . $submission->kode . '.pdf');
    }
}
