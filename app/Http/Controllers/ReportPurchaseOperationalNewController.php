<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Kitchen;
use App\Models\submissionOperational;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

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
            ->where('status', 'selesai')
            ->whereIn('kitchen_kode', $kitchenCodes)
            ->with(['kitchen', 'children.supplier']);

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
            ->paginate((int) $request->get('per_page', 10))
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

    public function excel(Request $request)
    {
        $kitchenCodes = $this->userKitchenCodes();
        $query = submissionOperational::onlyParent()
            ->has('children')
            ->where('status', 'selesai')
            ->whereIn('kitchen_kode', $kitchenCodes)
            ->with(['kitchen', 'children.supplier']);

        if ($request->filled('from_date')) {
            $query->whereDate('tanggal', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('tanggal', '<=', $request->to_date);
        }
        if ($request->filled('kitchen_kode')) {
            $query->where('kitchen_kode', $request->kitchen_kode);
        }

        $reports = $query->orderByDesc('tanggal')->get();

        $reports->transform(function ($report) {
            $totalChild = $report->children->sum('total_harga');
            $report->total_dapur = $totalChild;
            $report->persen_98 = $totalChild * 0.98;
            $report->persen_2 = $totalChild * 0.02;
            return $report;
        });

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Total Operasional');

        // ── Baris 1: Judul Utama ──────────────────────────────────────────
        $lastCol = 'G';
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI TOTAL OPERASIONAL');
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
        $headers = ['Kode', 'Dapur', 'Tanggal', 'Keterangan', 'Total Pengajuan', '98%', '2%'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        foreach ($headers as $k => $h) {
            $sheet->setCellValue($cols[$k] . '4', $h);
        }
        $sheet->getStyle('A4:G4')->applyFromArray([
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

            $sheet->setCellValue('A' . $row, $item->kode);
            $sheet->setCellValue('B' . $row, $item->kitchen->nama ?? '-');
            $sheet->setCellValue('C' . $row, \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('DD MMM YYYY'));
            $sheet->setCellValue('D' . $row, $item->keterangan ?? '-');
            $sheet->setCellValue('E' . $row, $item->total_dapur ?? 0);
            $sheet->setCellValue('F' . $row, $item->persen_98 ?? 0);
            $sheet->setCellValue('G' . $row, $item->persen_2 ?? 0);

            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');

            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
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
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL');
        $sheet->setCellValue("E{$row}", "=SUM(E5:E" . ($row - 1) . ")");
        $sheet->setCellValue("F{$row}", "=SUM(F5:F" . ($row - 1) . ")");
        $sheet->setCellValue("G{$row}", "=SUM(G5:G" . ($row - 1) . ")");
        $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('Rp#,##0');
        $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('Rp#,##0');
        $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('Rp#,##0');
        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
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
        $minWidths = ['A' => 18, 'B' => 20, 'C' => 14, 'D' => 24, 'E' => 16, 'F' => 16, 'G' => 14];
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
        $filename = 'laporan_total_operasional_' . date('Ymd_His') . '.xlsx';

        $response = response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
        $response->headers->setCookie(cookie('download_excel_completed', '1', 0, '/', null, false, false));
        return $response;
    }

    public function pdf(Request $request)
    {
        $kitchenCodes = $this->userKitchenCodes();
        $query = submissionOperational::onlyParent()
            ->has('children')
            ->where('status', 'selesai')
            ->whereIn('kitchen_kode', $kitchenCodes)
            ->with(['kitchen', 'children.supplier']);

        if ($request->filled('from_date')) {
            $query->whereDate('tanggal', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('tanggal', '<=', $request->to_date);
        }
        if ($request->filled('kitchen_kode')) {
            $query->where('kitchen_kode', $request->kitchen_kode);
        }

        $reports = $query->orderByDesc('tanggal')->get();

        $reports->transform(function ($report) {
            $totalChild = $report->children->sum('total_harga');
            $report->total_dapur = $totalChild;
            $report->persen_98 = $totalChild * 0.98;
            $report->persen_2 = $totalChild * 0.02;
            return $report;
        });

        $totalPersen98 = $reports->sum('persen_98');
        $totalPersen2 = $reports->sum('persen_2');
        $totalGrand = $reports->sum('total_dapur');

        $today = date('d-m-Y');

        $pdf = Pdf::loadView('report.invoiceReport-purchaseOperational-new', compact('reports', 'totalPersen98', 'totalPersen2', 'totalGrand'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('laporan_total_operasional_' . $today . '.pdf');
    }
}
