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

        $submissions = $query->latest('id')->paginate((int) $request->get('per_page', 10))->withQueryString();

        // Kalkulasi Total per Halaman menggunakan kolom subtotal_harga dari DB
        $totalPageSubtotal = $submissions->sum('subtotal_dapur');

        return view('report.sales-kitchen', compact('submissions', 'kitchens', 'suppliers', 'totalPageSubtotal', 'bahanBakus', 'menus'));
    }

    public function invoice(Request $request)
    {
        ini_set('memory_limit', '512M');
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

        return $pdf->stream('laporan_penjualan_dapur_' . $today . '.pdf');
    }

    public function excel(Request $request)
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
        if ($request->bahan_baku_id) {
            $query->where('bahan_baku_id', $request->bahan_baku_id);
        }

        $reports = $query->get()->sortByDesc('submission.tanggal');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Pembelian Dapur');

        // ── Baris 1: Judul Utama ──────────────────────────────────────────
        $lastCol = 'J';
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'LAPORAN PEMBELIAN DAPUR');
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

        // ── Baris 3: Kosong (spacer) ──────────────────────────────────────
        $sheet->getRowDimension(3)->setRowHeight(8);

        // ── Baris 4: Header Tabel ─────────────────────────────────────────
        $headers = ['No', 'Tanggal', 'Dapur', 'Supplier', 'Bahan Baku', 'Qty', 'Satuan', 'Porsi', 'Harga', 'Subtotal'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

        foreach ($headers as $k => $h) {
            $sheet->setCellValue($cols[$k] . '4', $h);
        }
        $sheet->getStyle('A4:J4')->applyFromArray([
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
            $isEven = ($index % 2 === 0);
            $fillColor = $isEven ? 'FFDCE6F1' : 'FFFFFFFF';

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, \Carbon\Carbon::parse($item->submission->tanggal)->locale('id')->isoFormat('DD MMM YYYY'));
            $sheet->setCellValue('C' . $row, $item->submission->kitchen->nama ?? '-');
            $sheet->setCellValue('D' . $row, $item->submission->supplier->nama ?? '-');
            $sheet->setCellValue('E' . $row, $item->bahan_baku->nama ?? '-');
            $sheet->setCellValue('F' . $row, $item->display_qty ?? $item->qty_digunakan ?? 0);
            $sheet->setCellValue('G' . $row, $item->display_unit ?? ($item->unit->satuan ?? '-'));
            $sheet->setCellValue('H' . $row, $item->submission->porsi ?? 0);
            $sheet->setCellValue('I' . $row, $item->harga_dapur ?? 0);
            $sheet->setCellValue('J' . $row, "=F{$row}*I{$row}");

            // Format angka
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');

            // Background selang-seling & border
            $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
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
        $sheet->mergeCells("A{$row}:I{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL');
        $sheet->setCellValue("J{$row}", "=SUM(J5:J" . ($row - 1) . ")");
        $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('Rp#,##0');
        $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
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
        $minWidths = [
            'A' => 5,
            'B' => 14,
            'C' => 20,
            'D' => 22,
            'E' => 24,
            'F' => 10,
            'G' => 10,
            'H' => 8,
            'I' => 14,
            'J' => 16
        ];
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // Set minimum width setelah autosize
        foreach ($minWidths as $col => $width) {
            if ($sheet->getColumnDimension($col)->getWidth() < $width) {
                $sheet->getColumnDimension($col)->setAutoSize(false)->setWidth($width);
            }
        }

        // ── Output ────────────────────────────────────────────────────────
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'laporan_penjualan_dapur_' . date('Ymd_His') . '.xlsx';

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
