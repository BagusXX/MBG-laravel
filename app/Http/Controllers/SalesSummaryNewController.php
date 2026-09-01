<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kitchen;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Barryvdh\DomPDF\Facade\Pdf;


class SalesSummaryNewController extends Controller
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
        $query = Submission::query()
            ->whereNull('parent_id')
            ->has('children')
            ->whereIn('kitchen_id', $kitchensCodes)
            ->with([
                'kitchen',
                'supplier',
                'children.details'
            ]);

        // if ($request->filled('from_date')) {
        //     $query->where(function ($q) use ($request) {
        //         $q->whereDate('tanggal', '>=', $request->from_date)
        //           ->orWhereDate('tanggal_digunakan', '>=', $request->from_date);
        //     });
        // }

        // if ($request->filled('to_date')) {
        //     $query->where(function ($q) use ($request) {
        //         $q->whereDate('tanggal', '<=', $request->to_date)
        //           ->orWhereDate('tanggal_digunakan', '<=', $request->to_date);
        //     });
        // }

        $query->whereDate('tanggal', '>=', '2026-04-01');

        if ($request->filled('from_date')) {
            $query->whereDate('tanggal_digunakan', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('tanggal_digunakan', '<=', $request->to_date);
        }


        if ($request->filled('kitchen_id')) {
            $query->where('kitchen_id', $request->kitchen_id);
        }

        $parents = $query
            ->orderByDesc('tanggal')
            ->paginate((int) $request->get('per_page', 10))
            ->withQueryString();

        // HITUNG TOTAL DARI CHILD
        $parents->getCollection()->transform(function ($parent) {

            $totalDapur = 0;
            // $totalMitra = 0;

            foreach ($parent->children as $child) {
                $totalDapur += $child->details->sum('subtotal_dapur');
                // $totalMitra += $child->details->sum('subtotal_mitra');
            }

            $parent->total_dapur = $totalDapur;
            // $parent->total_mitra = $totalMitra;
            // $parent->selisih = $totalDapur - $totalMitra;
            $parent->persen_98 = $parent->total_dapur * 0.98;
            $parent->persen_2 = $parent->total_dapur * 0.02;

            return $parent;
        });

        // TOTAL FOOTER (HALAMAN AKTIF)
        $collection = $parents->getCollection();
        
        $totalInvoiceDapur = $collection->sum('total_dapur');

        // $totalSelisih = $collection->sum('selisih');
        $totalPersen98 = $collection->sum('persen_98');
        $totalPersen2 = $collection->sum('persen_2');

        return view('report.sales-summary-new', compact(
            'kitchens',
            'parents',
            // 'totalSelisih',
            'totalInvoiceDapur',
            'totalPersen98',
            'totalPersen2'
        ));
    }

    public function excel(Request $request)
    {
        $kitchensCodes = $this->userKitchenCodes();
        $query = Submission::query()
            ->whereNull('parent_id')
            ->has('children')
            ->whereIn('kitchen_id', $kitchensCodes)
            ->with(['kitchen', 'supplier', 'children.details']);

        $query->whereDate('tanggal', '>=', '2026-04-01');

        if ($request->filled('from_date')) {
            $query->whereDate('tanggal_digunakan', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('tanggal_digunakan', '<=', $request->to_date);
        }
        if ($request->filled('kitchen_id')) {
            $query->where('kitchen_id', $request->kitchen_id);
        }

        $parents = $query->orderByDesc('tanggal')->get();

        $parents->transform(function ($parent) {
            $totalDapur = 0;
            foreach ($parent->children as $child) {
                $totalDapur += $child->details->sum('subtotal_dapur');
            }
            $parent->total_dapur = $totalDapur;
            $parent->persen_98 = $totalDapur * 0.98;
            $parent->persen_2 = $totalDapur * 0.02;
            return $parent;
        });

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Total Penjualan');

        // Header row
        $headers = [
            'No',
            'Kode',
            'Dapur',
            'Tanggal Pengajuan',
            'Tanggal Digunakan',
            'Total Invoice Dapur',
            '98%',
            '2%'
        ];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}1", $h);
        }
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF17375E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Data rows
        foreach ($parents as $idx => $p) {
            $row = $idx + 2;
            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $p->kode);
            $sheet->setCellValue("C{$row}", optional($p->kitchen)->nama);
            $sheet->setCellValue("D{$row}", $p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') : '-');
            $sheet->setCellValue("E{$row}", $p->tanggal_digunakan ? \Carbon\Carbon::parse($p->tanggal_digunakan)->format('d/m/Y') : '-');
            $sheet->setCellValue("F{$row}", $p->total_dapur);
            $sheet->setCellValue("G{$row}", $p->persen_98);
            $sheet->setCellValue("H{$row}", $p->persen_2);

            foreach (['F', 'G', 'H'] as $c) {
                $sheet->getStyle("{$c}{$row}")->getNumberFormat()->setFormatCode('#,##0');
            }
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'laporan-total-penjualan-' . now()->format('Ymd-His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(fn() => $writer->save('php://output'), $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function pdf(Request $request)
    {
        $kitchensCodes = $this->userKitchenCodes();
        $query = Submission::query()
            ->whereNull('parent_id')
            ->has('children')
            ->whereIn('kitchen_id', $kitchensCodes)
            ->with(['kitchen', 'supplier', 'children.details']);

        $query->whereDate('tanggal', '>=', '2026-04-01');

        if ($request->filled('from_date')) {
            $query->whereDate('tanggal_digunakan', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('tanggal_digunakan', '<=', $request->to_date);
        }
        if ($request->filled('kitchen_id')) {
            $query->where('kitchen_id', $request->kitchen_id);
        }

        $parents = $query->orderByDesc('tanggal')->get();

        $parents->transform(function ($parent) {
            $totalDapur = 0;
            foreach ($parent->children as $child) {
                $totalDapur += $child->details->sum('subtotal_dapur');
            }
            $parent->total_dapur = $totalDapur;
            $parent->persen_98 = $totalDapur * 0.98;
            $parent->persen_2 = $totalDapur * 0.02;
            return $parent;
        });

        $totalPersen98 = $parents->sum('persen_98');
        $totalPersen2 = $parents->sum('persen_2');
        $totalGrand = $parents->sum('total_dapur');

        $today = date('d-m-Y');

        $pdf = Pdf::loadView('report.invoiceReport-sales-summary-new', compact('parents', 'totalPersen98', 'totalPersen2', 'totalGrand'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('laporan_total_penjualan_' . $today . '.pdf');
    }
}
