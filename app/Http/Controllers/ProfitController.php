<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kitchen;
use App\Models\SubmissionDetails;
use App\Models\Supplier;
use App\Models\Submission;
use Barryvdh\DomPDF\Facade\Pdf;

class ProfitController extends Controller
{
    // Fungsi pembantu agar logic filter tidak diulang-ulang
    private function getReportQuery(Request $request, $kitchenCodes)
    {
        $query = SubmissionDetails::with([
            'submission.kitchen',
            'submission.parentSubmission',
            'bahan_baku',
            'submission.supplier',
            'unit'
        ])
            ->whereHas('submission', function ($q) {
                $q->whereNotNull('parent_id');
            });

        // 🔒 Filter kitchen berdasarkan kitchen user login
        $query->whereHas('submission.kitchen', function ($q) use ($kitchenCodes) {
            $q->whereIn('kode', $kitchenCodes);
        });

        // Filter Tanggal
        if ($request->filled('from_date') || $request->filled('to_date')) {
            $query->whereHas('submission.parentSubmission', function ($q) use ($request) {
                if ($request->filled('from_date')) {
                    $q->whereDate('tanggal', '>=', $request->from_date);
                }
                if ($request->filled('to_date')) {
                    $q->whereDate('tanggal', '<=', $request->to_date);
                }
            });
        }

        // Filter Kitchen (dropdown)
        if ($request->filled('kitchen_id')) {
            $query->whereRelation('submission', 'kitchen_id', $request->kitchen_id);
        }

        // Filter Supplier
        if ($request->filled('supplier_id')) {
            $query->whereRelation('submission', 'supplier_id', $request->supplier_id);
        }

        // Sorting di tingkat Database
        return $query->orderByDesc(
            Submission::select('tanggal')
                ->whereColumn('submissions.id', 'submission_details.submission_id')
                ->limit(1)
        )
            ->orderBy('submission_details.submission_id')
            ->orderBy('submission_details.id');
    }

    protected function userKitchenCodes()
    {
        return auth()->user()->kitchens()->pluck('kode');
    }

    public function index(Request $request)
    {
        $kitchenCodes = $this->userKitchenCodes();

        $kitchens = Kitchen::whereIn('kode', $kitchenCodes)->get();
        $suppliers = Supplier::all();

        $reports = $this->getReportQuery($request, $kitchenCodes)
            ->paginate((int) $request->get('per_page', 10))
            ->withQueryString();

        $reports->getCollection()->transform(function ($item) {
            $item->selisih_total = ($item->subtotal_dapur ?? 0) - ($item->subtotal_mitra ?? 0);
            return $item;
        });

        $totalPageSubtotal = $reports->getCollection()->sum('selisih_total');

        return view('report.profit', compact('kitchens', 'reports', 'suppliers', 'totalPageSubtotal'));

    }

    public function invoice(Request $request)
    {
        ini_set('memory_limit', '512M');
        $kitchenCodes = $this->userKitchenCodes();

        $reports = $this->getReportQuery($request, $kitchenCodes)->get();

        $reports->transform(function ($item) {
            $item->selisih_total = ($item->subtotal_dapur ?? 0) - ($item->subtotal_mitra ?? 0);
            return $item;
        });

        $submission = $reports->first()->submission ?? null;
        $totalPageSubtotal = $reports->sum('selisih_total');
        $today = now()->format('d-m-Y');

        $pdf = PDF::loadView('report.invoiceReport-profit', compact('submission', 'reports', 'totalPageSubtotal'));
        return $pdf->stream("laporan_selisih_penjualan_{$today}.pdf");
    }

    public function excel(Request $request)
    {
        $kitchenCodes = $this->userKitchenCodes();

        $reports = $this->getReportQuery($request, $kitchenCodes)->get();

        $reports->transform(function ($item) {
            $item->selisih_total = ($item->subtotal_dapur ?? 0) - ($item->subtotal_mitra ?? 0);
            return $item;
        });

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Selisih Bahan Baku');

        // ── Baris 1: Judul Utama ──────────────────────────────────────────
        $lastCol = 'I';
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'LAPORAN SELISIH BAHAN BAKU DAPUR');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F3864']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Baris 2: Tanggal Cetak ────────────────────────────────────────
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Tanggal Cetak: ' . now()->locale('id')->isoFormat('D MMMM YYYY'));
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF4A4A4A']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Baris 3: Spacer ───────────────────────────────────────────────
        $sheet->getRowDimension(3)->setRowHeight(8);

        // ── Baris 4: Header Tabel ─────────────────────────────────────────
        $headers = ['No', 'Tanggal Pengajuan', 'Dapur', 'Supplier', 'Bahan Baku', 'Satuan', 'Harga Dapur', 'Harga Mitra', 'Selisih'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

        foreach ($headers as $k => $h) {
            $sheet->setCellValue($cols[$k] . '4', $h);
        }
        $sheet->getStyle('A4:I4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF2E75B6']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000']
                ]
            ],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);

        // ── Baris 5+: Data ────────────────────────────────────────────────
        $row = 5;
        foreach ($reports as $index => $item) {
            $fillColor = ($index % 2 === 0) ? 'FFDCE6F1' : 'FFFFFFFF';

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, \Carbon\Carbon::parse($item->submission->tanggal)->locale('id')->isoFormat('DD MMM YYYY'));
            $sheet->setCellValue('C' . $row, $item->submission->kitchen->nama ?? '-');
            $sheet->setCellValue('D' . $row, $item->submission->supplier->nama ?? '-');
            $sheet->setCellValue('E' . $row, $item->bahan_baku->nama ?? '-');
            $sheet->setCellValue('F' . $row, $item->unit?->satuan ?? '-');
            $sheet->setCellValue('G' . $row, $item->subtotal_dapur ?? 0);
            $sheet->setCellValue('H' . $row, $item->subtotal_mitra ?? 0);
            $sheet->setCellValue('I' . $row, "=G{$row}-H{$row}");

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');

            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => $fillColor]
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'FFB8CCE4']
                    ]
                ],
            ]);
            $row++;
        }

        // ── Baris Total ───────────────────────────────────────────────────
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL SELISIH');
        $sheet->setCellValue("I{$row}", "=SUM(I5:I" . ($row - 1) . ")");
        $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('Rp#,##0');
        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F3864']
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000']
                ]
            ],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(20);

        // ── Lebar Kolom ───────────────────────────────────────────────────
        $minWidths = ['A' => 5, 'B' => 18, 'C' => 20, 'D' => 22, 'E' => 24, 'F' => 10, 'G' => 14, 'H' => 14, 'I' => 14];
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
        $filename = 'laporan_selisih_profit_' . date('Ymd_His') . '.xlsx';

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