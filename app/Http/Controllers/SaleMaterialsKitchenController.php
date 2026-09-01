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

class SaleMaterialsKitchenController extends Controller
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
        $menus = Menu::selectRaw('MIN(id) as id, nama')
            ->groupBy('nama')
            ->get();

        $query = Submission::with([
            'parentSubmission',
            'kitchen',
            'menu',
            'supplier',
            'details.unit',
            'details.bahan_baku'
        ])
            ->whereNotNull('parent_id')
            ->whereIn('kitchen_id', $kitchensCodes)
            ->where(function ($q) use ($request) {
                $q->where(function ($q2) {
                    $q2->where('status', 'diproses')
                        ->orWhere('tipe', 'disetujui');
                });

                $q->whereHas('parentSubmission', function ($ps) use ($request) {

                    if ($request->filled('from_date')) {
                        $ps->whereDate('tanggal', '>=', $request->from_date);
                    }

                    if ($request->filled('to_date')) {
                        $ps->whereDate('tanggal', '<=', $request->to_date);
                    }
                });

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

        $submissions = $query->paginate((int) $request->get('per_page', 10))->withQueryString();

        $totalPageSubtotal = $submissions->getCollection()->sum(function ($submission) {
            return $submission->details->sum('subtotal_dapur');
        });

        return view('transaction.sale-materials-kitchen', compact('submissions', 'kitchens', 'suppliers', 'totalPageSubtotal', 'bahanBakus', 'menus'));
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
        // Load relasi unit dan bahan baku
        $submission->load(['details.unit', 'details.bahan_baku', 'kitchen']);

        return response()->json([
            'submission' => [
                'id' => $submission->id,
                'kode' => $submission->kode,
                'tanggal' => $submission->tanggal,
                'kitchen_id' => $submission->kitchen_id,
                'kitchen_nama' => $submission->kitchen->nama,
            ],
            'details' => $submission->details->map(function ($detail) {
                return [
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'bahan_baku_nama' => $detail->bahan_baku->nama ?? '-',
                    'qty_digunakan' => $detail->qty_digunakan,
                    'satuan' => $detail->unit->satuan ?? '-', // Mengambil dari relasi unit di model
                    'harga_dapur' => $detail->harga_dapur ?? 0,
                    'subtotal' => $detail->subtotal_dapur ?? 0,
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
            'details.unit',
            'details.bahan_baku'
        ])
            ->onlyChild()
            ->where('kode', $kode)
            ->whereIn('kitchen_id', $kitchensCodes)
            ->where('status', 'diproses')
            ->first();

        if (!$submission) {
            abort(404, 'Data penjualan tidak ditemukan');
        }

        // Hitung total harga dari detail
        $totalHarga = $submission->details()->get()->sum(function ($detail) {
            $hargaDapur = $detail->subtotal_dapur ?? 0;
            return $hargaDapur;
        });

        $pdf = Pdf::loadView(
            'transaction.invoice-sale-kitchen',
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
            'details.unit',
            'details.bahan_baku'
        ])
            ->whereNotNull('parent_id')
            ->whereIn('kitchen_id', $kitchensCodes)
            ->where(function ($q) use ($request) {
                $q->where(function ($q2) {
                    $q2->where('status', 'diproses')
                        ->orWhere('tipe', 'disetujui');
                });

                $q->whereHas('parentSubmission', function ($ps) use ($request) {
                    if ($request->filled('from_date')) {
                        $ps->whereDate('tanggal', '>=', $request->from_date);
                    }
                    if ($request->filled('to_date')) {
                        $ps->whereDate('tanggal', '<=', $request->to_date);
                    }
                });

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

        $submissions = $query->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Invoice Dapur');

        // Header row
        $headers = ['No', 'Kode', 'Tanggal Pengajuan', 'Dapur', 'Menu', 'PM (besar)', 'PM (kecil)', 'Supplier', 'Total Dapur'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}1", $h);
        }
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF17375E']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        // Data rows
        foreach ($submissions as $idx => $s) {
            $row = $idx + 2;
            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $s->kode);
            $sheet->setCellValue("C{$row}", $s->parentSubmission ? \Carbon\Carbon::parse($s->parentSubmission->tanggal)->format('d/m/Y') : \Carbon\Carbon::parse($s->tanggal)->format('d/m/Y'));
            $sheet->setCellValue("D{$row}", optional($s->kitchen)->nama);
            $sheet->setCellValue("E{$row}", optional($s->menu)->nama);
            $sheet->setCellValue("F{$row}", $s->porsi_besar);
            $sheet->setCellValue("G{$row}", $s->porsi_kecil);
            $sheet->setCellValue("H{$row}", optional($s->supplier)->nama);
            $sheet->setCellValue("I{$row}", $s->details->sum('subtotal_dapur'));

            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            ]);
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'laporan-invoice-dapur-' . now()->format('Ymd-His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        return response()->streamDownload(fn() => $writer->save('php://output'), $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function pdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        $kitchensCodes = $this->userKitchenCodes();
        $query = Submission::with([
            'parentSubmission',
            'kitchen',
            'menu',
            'supplier',
            'details.unit',
            'details.bahan_baku'
        ])
            ->whereNotNull('parent_id')
            ->whereIn('kitchen_id', $kitchensCodes)
            ->where(function ($q) use ($request) {
                $q->where(function ($q2) {
                    $q2->where('status', 'diproses')
                        ->orWhere('tipe', 'disetujui');
                });

                $q->whereHas('parentSubmission', function ($ps) use ($request) {
                    if ($request->filled('from_date')) {
                        $ps->whereDate('tanggal', '>=', $request->from_date);
                    }
                    if ($request->filled('to_date')) {
                        $ps->whereDate('tanggal', '<=', $request->to_date);
                    }
                });

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

        $submissions = $query->get();
        $totalPageSubtotal = $submissions->sum(function ($s) {
            return $s->details->sum('subtotal_dapur');
        });

        $today = date('d-m-Y');

        $pdf = Pdf::loadView('transaction.pdf-sale-materials-kitchen', compact('submissions', 'totalPageSubtotal'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('laporan_invoice_dapur_' . $today . '.pdf');
    }
}
