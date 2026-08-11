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

        // Data Dropdown
        $kitchens = Kitchen::whereIn('id', $kitchensCodes)->orderBy('nama')->get();
        $suppliers = Supplier::all();
        $bahanBakus = BahanBaku::select('nama')->distinct()->orderBy('nama')->get();
        $menuQuery = Menu::whereIn('kitchen_id', $kitchensCodes);

        if ($request->filled('kitchen_id')) {
            $menuQuery->where('kitchen_id', $request->kitchen_id);
        }

        $menus = $menuQuery->orderBy('nama')->get();

        // 1. Query Dasar & Relasi
        $query = Submission::with([
            'parentSubmission',
            'kitchen',
            'menu',
            'supplier',
            'details.bahan_baku',
            'details.unit'
        ])
            ->whereNotNull('parent_id')
            ->whereIn('kitchen_id', $kitchensCodes);

        // 2. Filter Status & Tipe (Wajib ada)
        $query->where(function ($q) {
            $q->where('status', 'diproses') // atau 'selesai' sesuai kebutuhan Anda
                ->orWhere('tipe', 'disetujui');
        });

        // 3. Filter Tanggal (Dibuat fleksibel: bisa salah satu atau keduanya)
        if ($request->filled('from_date') || $request->filled('to_date')) {
            $query->whereHas('parentSubmission', function ($ps) use ($request) {
                if ($request->filled('from_date')) {
                    $ps->whereDate('tanggal', '>=', $request->from_date);
                }
                if ($request->filled('to_date')) {
                    $ps->whereDate('tanggal', '<=', $request->to_date);
                }
            });
        }

        // 4. Filter Dropdown (Langsung ke kolom di tabel submissions)
        if ($request->filled('kitchen_id')) {
            $query->where('kitchen_id', $request->kitchen_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // 5. Filter Menu
        if ($request->filled('menu_id')) {
            $query->where('menu_id', $request->menu_id);
        }

        $submissions = $query->latest('id')->paginate((int) $request->get('per_page', 10))->withQueryString();

        // Kalkulasi total
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
                $qty = $detail->qty ?? null;


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
        ini_set('memory_limit', '512M');
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
        return $pdf->stream('Invoice-' . $submission->kode . '.pdf');
    }

    public function excel(Request $request)
    {
        $kitchensCodes = $this->userKitchenCodes();

        $query = Submission::with([
            'parentSubmission',
            'kitchen',
            'menu',
            'supplier',
            'details.bahan_baku',
            'details.unit'
        ])
            ->whereNotNull('parent_id')
            ->whereIn('kitchen_id', $kitchensCodes);

        $query->where(function ($q) {
            $q->where('status', 'diproses')
                ->orWhere('tipe', 'disetujui');
        });

        if ($request->filled('from_date') || $request->filled('to_date')) {
            $query->whereHas('parentSubmission', function ($ps) use ($request) {
                if ($request->filled('from_date')) {
                    $ps->whereDate('tanggal', '>=', $request->from_date);
                }
                if ($request->filled('to_date')) {
                    $ps->whereDate('tanggal', '<=', $request->to_date);
                }
            });
        }

        if ($request->filled('kitchen_id')) {
            $query->where('kitchen_id', $request->kitchen_id);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('menu_id')) {
            $query->where('menu_id', $request->menu_id);
        }

        $submissions = $query->latest('id')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Selisih Penjualan');

        // ── Baris 1: Judul Utama ──────────────────────────────────────────
        $lastCol = 'H';
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI SELISIH PENJUALAN');
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
        $headers = ['Kode', 'Tanggal Pengajuan', 'Dapur', 'Menu', 'PM (besar)', 'PM (kecil)', 'Supplier', 'Total Selisih'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        foreach ($headers as $k => $h) {
            $sheet->setCellValue($cols[$k] . '4', $h);
        }
        $sheet->getStyle('A4:H4')->applyFromArray([
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
        foreach ($submissions as $index => $item) {
            $fillColor = ($index % 2 === 0) ? 'FFDCE6F1' : 'FFFFFFFF';

            $sheet->setCellValue('A' . $row, $item->kode);
            $sheet->setCellValue('B' . $row, \Carbon\Carbon::parse($item->parentSubmission ? $item->parentSubmission->tanggal : $item->tanggal)->locale('id')->isoFormat('DD MMM YYYY'));
            $sheet->setCellValue('C' . $row, $item->kitchen->nama ?? '-');
            $sheet->setCellValue('D' . $row, $item->menu->nama ?? '-');
            $sheet->setCellValue('E' . $row, $item->porsi_besar ?? 0);
            $sheet->setCellValue('F' . $row, $item->porsi_kecil ?? 0);
            $sheet->setCellValue('G' . $row, $item->supplier->nama ?? '-');
            $sheet->setCellValue('H' . $row, $item->details->sum('selisih') ?? 0);

            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
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
        $sheet->mergeCells("A{$row}:G{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL');
        $sheet->setCellValue("H{$row}", "=SUM(H5:H" . ($row - 1) . ")");
        $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('Rp#,##0');
        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
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
        $minWidths = ['A' => 18, 'B' => 18, 'C' => 20, 'D' => 20, 'E' => 10, 'F' => 10, 'G' => 22, 'H' => 16];
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
        $filename = 'laporan_rekap_selisih_profit_' . date('Ymd_His') . '.xlsx';

        $response = response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
        $response->headers->setCookie(cookie('download_excel_completed', '1', 0, '/', null, false, false));
        return $response;
    }

    public function printInvoiceExcel($kode)
    {
        $kitchensCodes = $this->userKitchenCodes();
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

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Invoice Header Details
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'INVOICE SELISIH PENJUALAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'Dapur:');
        $sheet->setCellValue('B3', $submission->kitchen->nama ?? '-');
        $sheet->setCellValue('A4', 'Supplier:');
        $sheet->setCellValue('B4', $submission->supplier->nama ?? '-');
        $sheet->setCellValue('A5', 'No. Invoice:');
        $sheet->setCellValue('B5', $submission->kode);
        $sheet->setCellValue('A6', 'Tanggal Cetak:');
        $sheet->setCellValue('B6', now()->locale('id')->isoFormat('dddd, D MMMM YYYY'));

        $sheet->getStyle('A3:A6')->getFont()->setBold(true);

        // Header Table
        $headers = ['No', 'Bahan Baku', 'Qty', 'Satuan', 'Total Dapur', 'Total Mitra', 'Selisih'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        foreach ($headers as $k => $h) {
            $sheet->setCellValue($cols[$k] . '8', $h);
            $sheet->getStyle($cols[$k] . '8')->getFont()->setBold(true);
            $sheet->getStyle($cols[$k] . '8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Body
        $row = 9;
        foreach ($submission->details as $index => $detail) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $detail->recipeBahanBaku?->bahan_baku?->nama ?? $detail->bahan_baku?->nama ?? '-');
            $sheet->setCellValue('C' . $row, $detail->qty_digunakan ?? 0);
            $sheet->setCellValue('D' . $row, $detail->unit?->satuan ?? '-');
            $sheet->setCellValue('E' . $row, $detail->subtotal_dapur ?? 0);
            $sheet->setCellValue('F' . $row, $detail->subtotal_mitra ?? 0);

            // Selisih formula
            $sheet->setCellValue('G' . $row, "=E{$row}-F{$row}");

            // formatting
            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('Rp#,##0');

            $row++;
        }

        // Total Row
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'TOTAL SELISIH');
        $sheet->setCellValue("G{$row}", "=SUM(G9:G" . ($row - 1) . ")");
        $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('Rp#,##0');

        // Style borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A8:G' . $row)->applyFromArray($styleArray);

        // Auto size columns
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Invoice_Selisih_' . $submission->kode . '.xlsx';

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
