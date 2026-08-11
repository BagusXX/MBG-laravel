<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kitchen;
use App\Models\SubmissionDetails;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BahanBaku;

class ReportSalesPartnerController extends Controller
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

        $query = SubmissionDetails::with([
            'submission.kitchen',
            'submission.parentSubmission',
            'bahan_baku',
            'submission.supplier',
        ]);

        $query->whereHas('submission', function ($q) use ($kitchensCodes) {
            $q->whereNotNull('parent_id')
                ->whereIn('kitchen_id', $kitchensCodes);
        });

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

        if ($request->filled('kitchen_id')) {
            $query->whereHas(
                'submission',
                fn($q) =>
                $q->where('kitchen_id', $request->kitchen_id)
            );
        }
        if ($request->filled('supplier_id')) {
            $query->whereHas(
                'submission',
                fn($q) =>
                $q->where('supplier_id', $request->supplier_id)
            );
        }
        if ($request->filled('bahan_baku_id')) {
            $selectedBahan = \App\Models\BahanBaku::find($request->bahan_baku_id);

            if ($selectedBahan) {
                $namaBahan = $selectedBahan->nama;

                $query->where(function ($q) use ($namaBahan) {
                    // Filter 1: Lewat relasi langsung bahanBaku
                    $q->whereHas('bahan_baku', function ($qb) use ($namaBahan) {
                        $qb->where('nama', $namaBahan);
                    })
                        // Filter 2: Lewat relasi resep (Gunakan bahan_baku sesuai modelmu)
                        // Nested relationship: recipeBahanBaku -> bahan_baku
                        ->orWhereHas('bahan_baku', function ($qr) use ($namaBahan) {
                            $qr->where('nama', $namaBahan);
                        });
                });
            }
        }

        $query->orderByDesc(\App\Models\Submission::select('tanggal')
            ->whereIn('id', function ($subQuery) {
                $subQuery->select('parent_id')
                    ->from('submissions')
                    ->whereColumn('id', 'submission_details.submission_id');
            })
            ->limit(1));

        $reports = $query->latest('id')->paginate((int) $request->get('per_page', 10))->withQueryString();

        $totalPageSubtotal = $reports->sum('subtotal');

        return view('report.sales-partner', compact('kitchens', 'reports', 'suppliers', 'totalPageSubtotal', 'bahanBakus'));
    }

    public function invoice(Request $request)
    {
        ini_set('memory_limit', '512M');
        $kitchenCodes = $this->userKitchenCodes();
        $query = SubmissionDetails::with(['submission.kitchen', 'bahan_baku.unit', 'submission.supplier', 'bahan_baku.unit']);

        $query->whereHas('submission', function ($q) {
            $q->whereNotNull('parent_id');
        });

        if ($request->from_date && $request->to_date) {
            $query->whereHas('submission', function ($q) use ($kitchenCodes, $request) {
                $q->whereBetween('tanggal', [$request->from_date, $request->to_date])
                    ->whereIn('kitchen_id', $kitchenCodes);

            });
        }

        if ($request->kitchen_id) {
            $query->whereHas('submission', function ($q) use ($request) {
                $q->where('kitchen_id', $request->kitchen_id);
            });
        }

        if ($request->supplier_id) {
            $query->whereHas('submission', function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });
        }


        $reports = $query->get();

        $reports = $reports->sortByDesc(function ($item) {
            return $item->submission->tanggal;
        });

        $today = date('d-m-Y');

        $submission = $reports->first()->submission ?? null;

        $totalPageSubtotal = $reports->sum('subtotal');

        $pdf = PDF::loadView('report.invoiceReport-sales-partner', compact('submission', 'reports', 'totalPageSubtotal'));

        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('laporan penjualan mitra_' . $today . '.pdf');
    }

    public function excel(Request $request)
    {
        $kitchenCodes = $this->userKitchenCodes();
        $query = SubmissionDetails::with(['submission.kitchen', 'bahan_baku.unit', 'submission.supplier', 'unit']);

        $query->whereHas('submission', function ($q) {
            $q->whereNotNull('parent_id');
        });

        if ($request->from_date && $request->to_date) {
            $query->whereHas('submission', function ($q) use ($kitchenCodes, $request) {
                $q->whereBetween('tanggal', [$request->from_date, $request->to_date])
                    ->whereIn('kitchen_id', $kitchenCodes);
            });
        }

        if ($request->kitchen_id) {
            $query->whereHas('submission', function ($q) use ($request) {
                $q->where('kitchen_id', $request->kitchen_id);
            });
        }

        if ($request->supplier_id) {
            $query->whereHas('submission', function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });
        }

        if ($request->bahan_baku_id) {
            $query->where('bahan_baku_id', $request->bahan_baku_id);
        }

        $reports = $query->get();

        $reports = $reports->sortByDesc(function ($item) {
            return $item->submission->tanggal;
        });

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Pembelian Mitra');

        // ── Baris 1: Judul Utama ──────────────────────────────────────────
        $lastCol = 'K';
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'LAPORAN PEMBELIAN MITRA');
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
        $headers = ['No', 'Tanggal', 'Dapur', 'Supplier', 'Bahan Baku', 'Qty', 'Satuan', 'PM (besar)', 'PM (kecil)', 'Harga Satuan', 'Subtotal'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];

        foreach ($headers as $k => $h) {
            $sheet->setCellValue($cols[$k] . '4', $h);
        }
        $sheet->getStyle('A4:K4')->applyFromArray([
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
            $sheet->setCellValue('F' . $row, $item->qty_digunakan ?? 0);
            $sheet->setCellValue('G' . $row, $item->unit->satuan ?? '-');
            $sheet->setCellValue('H' . $row, $item->submission->porsi_besar ?? 0);
            $sheet->setCellValue('I' . $row, $item->submission->porsi_kecil ?? 0);
            $sheet->setCellValue('J' . $row, $item->harga_mitra ?? 0);
            $sheet->setCellValue('K' . $row, "=F{$row}*J{$row}");

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');
            $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');

            $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
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
        $sheet->mergeCells("A{$row}:J{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL');
        $sheet->setCellValue("K{$row}", "=SUM(K5:K" . ($row - 1) . ")");
        $sheet->getStyle("K{$row}")->getNumberFormat()->setFormatCode('Rp#,##0');
        $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
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
            'H' => 10,
            'I' => 10,
            'J' => 14,
            'K' => 16
        ];
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
        $filename = 'laporan_penjualan_mitra_' . date('Ymd_His') . '.xlsx';

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
