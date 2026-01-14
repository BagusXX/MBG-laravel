<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\submissionOperational;
use App\Models\submissionOperationalDetails;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportPurchaseOperationalController extends Controller
{
    public function index(Request $request)
    {
        $kitchens = Kitchen::orderBy('nama')->get();
        $suppliers = Supplier::orderBy('nama')->get();

        // 1. UBAH QUERY: Ambil details, tapi filter submission-nya harus yang CHILD (Approval)
        $query = submissionOperationalDetails::with([
            'submission.kitchen',
            'submission.supplier', // Karena ini Child, relasi ini sekarang AKAN ADA ISINYA
            'operational',
            'submission.parent' // Opsional: jika butuh info dari parent aslinya
        ]);

        // 2. FILTER PENTING:
        // Kita hanya mau mengambil detail dari submission yang MERUPAKAN CHILD (parent_id tidak null)
        // Dan statusnya 'disetujui' (artinya ini adalah PO yang valid)
        $query->whereHas('submission', function ($q) {
            $q->whereNotNull('parent_id') // Pastikan ini baris Child
                ->where('tipe', 'disetujui'); // Pastikan tipenya approval

            // Opsional: Jika Anda hanya ingin laporan muncul kalau Parent-nya sudah status 'selesai'
            // $q->whereHas('parent', fn($p) => $p->where('status', 'selesai'));
        });

        // --- FILTERING INPUT USER ---

        // Filter Tanggal (Gunakan tanggal approval/child)
        if ($request->filled('from_date')) {
            $query->whereHas(
                'submission',
                fn($q) =>
                $q->whereDate('tanggal', '>=', $request->from_date)
            );
        }

        if ($request->filled('to_date')) {
            $query->whereHas(
                'submission',
                fn($q) =>
                $q->whereDate('tanggal', '<=', $request->to_date)
            );
        }

        // Filter Dapur
        if ($request->filled('kitchen_kode')) {
            $query->whereHas(
                'submission',
                fn($q) =>
                $q->where('kitchen_kode', $request->kitchen_kode)
            );
        }

        // Filter Supplier
        if ($request->filled('supplier_id')) {
            $query->whereHas(
                'submission',
                fn($q) =>
                $q->where('supplier_id', $request->supplier_id)
            );
        }

        // Urutkan
        $query->orderByDesc(
            submissionOperational::select('tanggal')
                ->whereColumn('id', 'submission_operational_details.operational_submission_id')
        );

        $reports = $query->get();

        return view('report.purchase-operational', compact('kitchens', 'reports', 'suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function invoice(Request $request)
    {
        // ============================================================
        // 1. QUERY HARUS SAMA PERSIS DENGAN FUNCTION INDEX
        // ============================================================

        $query = submissionOperationalDetails::with([
            'submission.kitchen',
            'submission.supplier',
            'submission.parent.supplier', // Load supplier milik parent juga
            'operational',
        ]);

        // FILTER STATUS: Ambil status yang valid (Draft/Dihapus jangan ikut)
        $query->whereHas('submission', function ($q) {
            // Logic ini disamakan dengan index agar data Parent juga tampil
            $q->whereIn('status', ['diajukan', 'diproses', 'disetujui', 'selesai']);
        });

        // 🔑 PENTING: HANYA SUBMISSION AKTIF
        $query->whereHas('submission', function ($q) {
            $q->whereNotNull('parent_id'); // ⬅ ini kuncinya
        });

        // --- FILTERING ---
        if ($request->filled('from_date')) {
            $query->whereHas('submission', fn($q) => $q->whereDate('tanggal', '>=', $request->from_date));
        }
        if ($request->filled('to_date')) {
            $query->whereHas('submission', fn($q) => $q->whereDate('tanggal', '<=', $request->to_date));
        }
        if ($request->filled('kitchen_kode')) {
            $query->whereHas('submission', fn($q) => $q->where('kitchen_kode', $request->kitchen_kode));
        }
        if ($request->filled('supplier_id')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas(
                    'submission',
                    fn($s) =>
                    $s->where('supplier_id', $request->supplier_id)
                )
                    ->orWhereHas(
                        'submission.parent',
                        fn($p) =>
                        $p->where('supplier_id', $request->supplier_id)
                    );
            });
        }

        $query->orderByDesc(
            submissionOperational::select('tanggal')
                ->whereColumn('id', 'submission_operational_details.operational_submission_id')
        );

        $reports = $query->get();

        // ============================================================
        // 2. GENERATE PDF
        // ============================================================
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('report.invoiceReport-purchaseOperational', [
            'reports' => $reports,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
        ]);

        $pdf->setPaper('a4', 'landscape'); // Landscape agar muat banyak kolom

        return $pdf->stream('Laporan_Pembelian_Operasional.pdf');
    }
}
