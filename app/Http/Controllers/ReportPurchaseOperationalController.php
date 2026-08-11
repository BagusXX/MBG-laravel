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
    protected function userKitchenCodes()
    {
        return auth()->user()->kitchens()->pluck('kode')->toArray();
    }

    public function index(Request $request)
    {
        $kitchens = Kitchen::orderBy('nama')->get();
        $suppliers = Supplier::orderBy('nama')->get();

        $kitchenCodes = $this->userKitchenCodes();

        // 1. UBAH QUERY: Ambil details, tapi filter submission-nya harus yang CHILD (Approval)
        $query = submissionOperationalDetails::with([
            'submission.kitchen',
            'submission.supplier', // Karena ini Child, relasi ini sekarang AKAN ADA ISINYA
            'operational',
            'submission.parentSubmission' // Opsional: jika butuh info dari parent aslinya
        ]);

        $query->whereHas('submission', function ($q) use ($kitchenCodes, $request) {
            $q->whereNotNull('parent_id') // Pastikan ini baris Child
                ->where('tipe', 'disetujui');
            $q->whereIn('kitchen_kode', $kitchenCodes); // Pastikan tipenya approval
        });

        // --- FILTERING INPUT USER ---

        // Filter Tanggal (Gunakan tanggal approval/child)
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

        $reports = $query->paginate((int) $request->get('per_page', 10))->withQueryString();

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
        ini_set('memory_limit', '512M');
        // ============================================================
        // 1. QUERY HARUS SAMA PERSIS DENGAN FUNCTION INDEX
        // ============================================================
        $kitchenCodes = $this->userKitchenCodes();

        $query = submissionOperationalDetails::with([
            'submission.kitchen',
            'submission.supplier',
            'submission.parentSubmission.supplier', // Load supplier milik parent juga
            'operational',
        ]);

        // FILTER STATUS: Ambil status yang valid (Draft/Dihapus jangan ikut)
        $query->whereHas('submission', function ($q) use ($kitchenCodes) {
            // Logic ini disamakan dengan index agar data Parent juga tampil
            $q->whereIn('status', ['diajukan', 'diproses', 'disetujui', 'selesai'])
                ->whereIn('kitchen_kode', $kitchenCodes);
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

        $today = date('d-m-Y');

        $submission = $reports->first()->submission ?? null;

        // ============================================================
        // 2. GENERATE PDF
        // ============================================================
        $pdf = Pdf::loadView('report.invoiceReport-purchaseOperational', [
            'reports' => $reports,
            'submission' => $submission,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
            'today' => $today,
        ]);

        $pdf->setPaper('a4', 'landscape'); // Landscape agar muat banyak kolom

        return $pdf->stream('Laporan_Pembelian_Operasional.pdf');
    }

    public function excel(Request $request)
    {
        $kitchenCodes = $this->userKitchenCodes();

        $query = submissionOperationalDetails::with([
            'submission.kitchen',
            'submission.supplier',
            'submission.parentSubmission.supplier',
            'operational',
        ]);

        $query->whereHas('submission', function ($q) use ($kitchenCodes) {
            $q->whereIn('status', ['diajukan', 'diproses', 'disetujui', 'selesai'])
                ->whereIn('kitchen_kode', $kitchenCodes);
        });

        $query->whereHas('submission', function ($q) {
            $q->whereNotNull('parent_id');
        });

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
                    fn($s) => $s->where('supplier_id', $request->supplier_id)
                )->orWhereHas(
                    'submission.parent',
                    fn($p) => $p->where('supplier_id', $request->supplier_id)
                );
            });
        }

        $query->orderByDesc(
            submissionOperational::select('tanggal')
                ->whereColumn('id', 'submission_operational_details.operational_submission_id')
        );

        $reports = $query->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Pembelian Operasional');

        // ── Baris 1: Judul Utama ──────────────────────────────────────────
        $lastCol = 'I';
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'LAPORAN PEMBELIAN OPERASIONAL DAPUR');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF1F3864']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Baris 2: Tanggal Cetak ────────────────────────────────────────
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Tanggal Cetak: ' . now()->locale('id')->isoFormat('D MMMM YYYY'));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF4A4A4A']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Baris 3: Spacer ───────────────────────────────────────────────
        $sheet->getRowDimension(3)->setRowHeight(8);

        // ── Baris 4: Header Tabel ─────────────────────────────────────────
        $headers = ['No', 'Tanggal', 'Dapur', 'Supplier', 'Barang', 'Keterangan', 'Jumlah', 'Harga', 'Subtotal'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

        foreach ($headers as $k => $h) {
            $sheet->setCellValue($cols[$k] . '4', $h);
        }
        $sheet->getStyle('A4:I4')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF2E75B6']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                             'color' => ['argb' => 'FF000000']]],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);

        // ── Baris 5+: Data ────────────────────────────────────────────────
        $row = 5;
        foreach ($reports as $index => $item) {
            $fillColor = ($index % 2 === 0) ? 'FFDCE6F1' : 'FFFFFFFF';

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, \Carbon\Carbon::parse($item->submission->tanggal)->locale('id')->isoFormat('DD MMM YYYY'));
            $sheet->setCellValue('C' . $row, $item->submission->kitchen->nama ?? '-');
            $sheet->setCellValue('D' . $row, $item->submission->supplier->nama ?? $item->submission->parent->supplier->nama ?? '-');
            $sheet->setCellValue('E' . $row, $item->operational->nama ?? '-');
            $sheet->setCellValue('F' . $row, $item->keterangan ?? $item->submission->keterangan ?? '-');
            $sheet->setCellValue('G' . $row, $item->qty ?? 0);
            $sheet->setCellValue('H' . $row, $item->harga_dapur ?? 0);
            $sheet->setCellValue('I' . $row, "=G{$row}*H{$row}");

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');

            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                'fill'    => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                              'startColor' => ['argb' => $fillColor]],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                               'color' => ['argb' => 'FFB8CCE4']]],
            ]);
            $row++;
        }

        // ── Baris Total ───────────────────────────────────────────────────
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL');
        $sheet->setCellValue("I{$row}", "=SUM(I5:I" . ($row - 1) . ")");
        $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('Rp#,##0');
        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF1F3864']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
            'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                             'color' => ['argb' => 'FF000000']]],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(20);

        // ── Lebar Kolom ───────────────────────────────────────────────────
        $minWidths = ['A' => 5, 'B' => 14, 'C' => 20, 'D' => 22, 'E' => 20, 'F' => 22, 'G' => 10, 'H' => 14, 'I' => 16];
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        foreach ($minWidths as $col => $width) {
            if ($sheet->getColumnDimension($col)->getWidth() < $width) {
                $sheet->getColumnDimension($col)->setAutoSize(false)->setWidth($width);
            }
        }

        // ── Output ────────────────────────────────────────────────────────
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'laporan_pembelian_operasional_' . date('Ymd_His') . '.xlsx';

        $response = response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
        $response->headers->setCookie(cookie('download_excel_completed', '1', 0, '/', null, false, false));
        return $response;
    }
}
