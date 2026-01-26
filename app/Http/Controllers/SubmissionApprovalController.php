<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\Submission;
use App\Models\SubmissionDetails;
use App\Models\BahanBaku;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubmissionApprovalController extends Controller
{
    /* ================= HELPER ================= */

    protected function ensureEditable(Submission $submission)
    {
        abort_if(!$submission->isParent(), 403, 'Hanya parent submission');
        abort_if($submission->status === 'selesai', 403, 'Submission terkunci');
    }

    private function applyConversion($item)
    {
        // 1. Ambil Nama Satuan
        $unitNama = '-';
        if ($item->recipeBahanBaku && $item->recipeBahanBaku->bahan_baku) {
            $unitNama = optional($item->recipeBahanBaku->bahan_baku->unit)->satuan;
        } elseif ($item->bahan_baku) {
            $unitNama = optional($item->bahan_baku->unit)->satuan;
        }

        $unitLower = strtolower($unitNama);
        $qty = (float)$item->qty_digunakan;

        $item->display_qty = $qty;
        $item->display_unit = $unitNama;

        // 2. Logika Konversi ke Kg / L
        if ($unitLower == 'gram') {
            $item->display_unit = 'Kg';
            $item->display_qty = $qty / 1000;
        } elseif ($unitLower == 'ml') {
            $item->display_unit = 'L';
            $item->display_qty = $qty / 1000;
        } else {
            $item->display_unit = $unitNama;
            $item->display_qty = $qty;
        }

        // 3. Format Angka (Gunakan koma untuk desimal, hilangkan desimal jika bulat)
        $item->formatted_qty = number_format(
            $item->display_qty,
            ($item->display_qty == floor($item->display_qty) ? 0 : 2),
            ',',
            '.'
        );

        return $item;
    }

    protected function convertQtyForCalculation(SubmissionDetails $detail): float
    {
        $qty = (float) $detail->qty_digunakan;

    // Ambil bahan baku dari mana pun sumbernya
        $bahanBaku = $detail->bahan_baku
            ?? $detail->recipeBahanBaku?->bahan_baku;

        if (!$bahanBaku || !$bahanBaku->unit) {
            throw new \Exception("Satuan bahan baku tidak ditemukan (Detail ID: {$detail->id})");
        }

        $unit = strtolower($bahanBaku->unit->satuan);

        return match ($unit) {
            'gram' => $qty / 1000,
            'ml'   => $qty / 1000,
            default => $qty,
        };  

    }

    // KODE BARU (SOLUSI 1)
    protected function recalculateTotal(Submission $submission)
    {
        $submission->load(['details.bahan_baku.unit', 'details.recipeBahanBaku.bahan_baku.unit']);

        $total = 0;

        foreach ($submission->details as $detail) {

            // Pilih harga: mitra > dapur
            $harga = $detail->harga_mitra ?? $detail->harga_dapur;

            if ($harga === null) {
                continue;
            }

            // KONVERSI QTY (gram -> kg, ml -> liter)
            $qtyKonversi = $this->convertQtyForCalculation($detail);

            $subtotal = $harga * $qtyKonversi;

            // Optional: simpan subtotal agar konsisten
            $detail->update([
                'subtotal_harga' => $subtotal,
            ]);

            $total += $subtotal;
        }

        // 3. Update hasil perhitungan ke tabel parent
        $submission->update(['total_harga' => $total]);
    }

    /* ================= INDEX ================= */

    public function index()
    {
        $submissions = Submission::with([
            'kitchen',
            'menu',
            'supplier',
            'details',
            'details.recipeBahanBaku.bahan_baku.unit',
            'details.bahan_baku.unit'
        ])
            ->onlyParent()
            ->pengajuan()
            ->latest()
            ->paginate(10);

        return view('transaction.submissionApproval', [
            'submissions' => $submissions,
            'kitchens' => Kitchen::orderBy('nama')->get(),
            'suppliers' => Supplier::orderBy('nama')->get(),
        ]);
    }

    /* ================= STATUS ================= */

    public function updateStatus(Request $request, Submission $submission)
    {
        abort_if(!$submission->isParent(), 403, 'Aksi ini hanya untuk Pengajuan Utama (Parent)');
        abort_if(in_array($submission->status, ['selesai', 'ditolak']), 403, 'Pengajuan sudah ditutup');

        $rules = [
            'status' => 'required|in:selesai,ditolak',
        ];

        if ($request->status === 'ditolak') {
            $rules['keterangan'] = 'required|string|min:5';
        }

        $validated = $request->validate($rules);

        $submission->update([
            'status' => $validated['status'],
            'keterangan' => $validated['status'] === 'ditolak'
                ? $validated['keterangan']
                : null,
        ]);

        return back()->with('success', 'Status berhasil diperbarui');
    }


    /* ================= DETAIL ================= */

    public function getDetails(Submission $submission)
    {
        $details = $submission->details()->with(['bahan_baku.unit'])->get();

        $data = $details->map(function ($detail) {

            // 1. AKTIFKAN KONVERSI (Gram -> Kg)
            $formatted = $this->formatQtyWithUnit(
                $detail->qty_digunakan,
                $detail->bahan_baku?->unit
            );

            return [
                'id' => $detail->id,
                // Kirim angka cantik (1.6)
                'qty_digunakan' => $formatted['qty'],
                'harga_dapur' => $detail->harga_dapur,
                'harga_mitra' => $detail->harga_mitra,
                'recipe_bahan_baku_id' => $detail->recipe_bahan_baku_id,

                'bahan_baku' => [
                    'nama' => $detail->bahan_baku->nama ?? 'Item Terhapus',
                    'unit' => [
                        'satuan' => $formatted['unit'] // Kirim satuan cantik (kg)
                    ]
                ]
            ];
        });

        return response()->json($data);
    }

    public function getSubmissionData(Submission $submission)
    {
        // Load relasi yang diperlukan, termasuk unit untuk konversi
        $submission->load(['kitchen', 'menu', 'children.supplier', 'children.details.bahan_baku.unit']);

        // --- BAGIAN RIWAYAT (HISTORY) ---
        // Karena ini hanya untuk dilihat (bukan diedit), kita KONVERSI satuannya (Kg/Liter)
        $history = $submission->children->map(function ($child) {
            return [
                'id' => $child->id,
                'kode' => $child->kode,
                'supplier_nama' => $child->supplier->nama ?? 'Umum',
                'status' => $child->status,
                'total' => $child->total_harga,
                'item_count' => $child->details->count(),

                'items' => $child->details->map(function ($detail) {

                    // GUNAKAN HELPER KONVERSI DISINI
                    // Agar di riwayat tertulis "1.6 kg", bukan "1600 gram"
                    $formatted = $this->formatQtyWithUnit(
                        $detail->qty_digunakan,
                        $detail->bahan_baku?->unit
                    );

                    return [
                        'nama' => $detail->bahan_baku->nama ?? '-',

                        // Kita kirim nilai yang sudah dikonversi (1.6)
                        'qty' => $formatted['qty'],

                        // Kita kirim satuan yang sudah dikonversi (kg)
                        'unit' => $formatted['unit'],

                        'harga' => $detail->harga_mitra ?? $detail->harga_satuan,
                    ];
                })->values()
            ];
        });

        $availableSuppliers = $submission->kitchen->suppliers->values();

        return response()->json([
            'id' => $submission->id,
            'kode' => $submission->kode,
            'tanggal' => date('d-m-Y', strtotime($submission->tanggal)),
            'kitchen' => $submission->kitchen->nama,
            'menu' => $submission->menu->nama,
            'porsi' => $submission->porsi,
            'status' => $submission->status,
            'history' => $history, // <--- Data history sudah cantik (Kg/Liter)
            'suppliers' => $availableSuppliers
        ]);
    }

    // app/Http/Controllers/SubmissionApprovalController.php

    public function updateHarga(Request $request, Submission $submission)
    {
        // 1. Cek Status agar aman
        if (in_array($submission->status, ['selesai', 'ditolak'])) {
            return response()->json(['message' => 'Pengajuan sudah terkunci.'], 403);
        }

        // 2. Validasi Input
        $request->validate([
            'details' => 'required|array',
            'details.*.id' => 'required|exists:submission_details,id',
            // 'details.*.qty_digunakan' => 'required|numeric|min:0',
            'details.*.harga_dapur' => 'nullable|numeric|min:0',
            'details.*.harga_mitra' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $submission) {
            foreach ($request->details as $row) {
                $detail = $submission->details()->find($row['id']);

                if ($detail) {

                    $detail->setRelation('submission', $submission);

                    $hargaDapur = $row['harga_dapur'] ?? 0;
                    $hargaMitra = $row['harga_mitra'] ?? 0;

                    $detail->update([
                        // 'qty_digunakan' => $row['qty_digunakan'],
                        'harga_dapur' => $hargaDapur,
                        'harga_mitra' => $hargaMitra,
                    ]);
                }
            }

            // Hitung ulang total
            $this->recalculateTotal($submission);
        });

        return response()->json(['success' => true, 'message' => 'Data berhasil disimpan!']);
    }

    public function addManualBahan(Request $request, Submission $submission)
    {
        $this->ensureEditable($submission);

        $request->validate([
            'bahan_baku_id' => 'required|exists:bahan_baku,id',
            'qty_digunakan' => 'required|numeric|min:0.0001',
        ]);

        $bahan = BahanBaku::where('id', $request->bahan_baku_id)
            ->where('kitchen_id', $submission->kitchen_id)
            ->firstOrFail();

        DB::transaction(function () use ($submission, $bahan, $request) {

            SubmissionDetails::create([
                'submission_id' => $submission->id,
                'recipe_bahan_baku_id' => null,
                'bahan_baku_id' => $bahan->id,
                'qty_digunakan' => $request->qty_digunakan,
                'harga_satuan' => $bahan->harga,
                'harga_dapur' => $bahan->harga,
                'harga_mitra' => $bahan->harga,
                'subtotal_harga' => $bahan->harga * $request->qty_digunakan,
            ]);

            $this->recalculateTotal($submission);
        });

        return response()->json(['success' => true]);
    }

    public function deleteDetail(Submission $submission, SubmissionDetails $detail)
    {
        $this->ensureEditable($submission);

        abort_if($detail->submission_id !== $submission->id, 403);

        DB::transaction(function () use ($detail, $submission) {
            $detail->delete();
            $this->recalculateTotal($submission);
        });

        return response()->json(['success' => true]);
    }

    /* ================= AJAX ================= */


    // Tambahkan/Update method ini di SubmissionApprovalController



    public function splitToSupplier(Request $request, Submission $submission)
    {
        // Cek apakah data benar-benar ada (Debugging - Hapus nanti jika sudah fix)
        // dd($submission->toArray()); 

        // Logic auto-update status jika masih diajukan
        if ($submission->status === 'diajukan') {
            $submission->update(['status' => 'diproses']);
        }

        // Validasi Status
        abort_if(in_array($submission->status, ['selesai', 'ditolak']), 403, 'Pengajuan sudah ditutup');

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'selected_details' => 'required|array',
            'selected_details.*' => 'exists:submission_details,id',
        ]);

        DB::transaction(function () use ($submission, $request) {

            // 1. GENERATE KODE CHILD
            // Format: KODE_PARENT-1, KODE_PARENT-2, dst.
            $childSequence = Submission::withTrashed()
                ->where('parent_id', $submission->id)->count() + 1;
            $childKode = $submission->kode . '-' . $childSequence;

            // 2. BUAT CHILD SUBMISSION
            $child = Submission::create([
                'kode' => $childKode,
                'tanggal' => now(),
                'kitchen_id' => $submission->kitchen_id, // Data diambil dari $submission
                'menu_id' => $submission->menu_id,       // Data diambil dari $submission
                'porsi' => $submission->porsi,           // Data diambil dari $submission
                'total_harga' => 0,
                'tipe' => 'disetujui',
                'status' => 'diproses',
                'parent_id' => $submission->id,
                'supplier_id' => $request->supplier_id,
            ]);

            $total = 0;

            // 3. PINDAHKAN DETAIL YANG DICENTANG
            $detailsToCopy = SubmissionDetails::with([
                'bahan_baku.unit',
                'recipeBahanBaku.bahan_baku.unit'
            ])->whereIn('id', $request->selected_details)->get();

            foreach ($detailsToCopy as $detail) {
                $harga = $detail->harga_mitra ?? $detail->harga_dapur;

                if ($harga === null) {
                    continue;
                }

                // KONVERSI QTY (PAKAI LOGIC YANG SAMA)
                $qtyKonversi = $this->convertQtyForCalculation($detail);

                $subtotal = $harga * $qtyKonversi;

                SubmissionDetails::create([
                    'submission_id' => $child->id,
                    'recipe_bahan_baku_id' => $detail->recipe_bahan_baku_id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'qty_digunakan' => $detail->qty_digunakan,
                    'harga_satuan' => $detail->harga_satuan,
                    'harga_dapur' => $detail->harga_dapur, // Child ke supplier tidak butuh harga dapur
                    'harga_mitra' => $detail->harga_mitra,
                    'subtotal_harga' => $subtotal,
                ]);

                $total += $subtotal;
            }

            // Update total harga child
            $child->update(['total_harga' => $total]);
        });

        return response()->json(['success' => true, 'message' => 'Order berhasil dipisah ke supplier']);
    }
    // app/Http/Controllers/SubmissionApprovalController.php

    public function destroyChild(Submission $submission)
    {
        // 1. Pastikan yang dihapus adalah Child (PO Supplier)
        abort_if(!$submission->isChild(), 403, 'Hanya split order (child) yang bisa dihapus di sini.');

        // 2. Cek Status (Opsional: Jangan izinkan hapus jika sudah selesai/diterima supplier)
        if ($submission->status === 'selesai') {
            return response()->json(['message' => 'PO ini sudah selesai, tidak bisa dihapus.'], 403);
        }

        DB::transaction(function () use ($submission) {
            // Hapus detailnya dulu (karena cascade kadang perlu trigger manual tergantung db engine)
            $submission->details()->delete();

            // Hapus headernya
            $submission->delete(); // SoftDelete jika aktif, atau Force Delete
        });

        return response()->json(['success' => true, 'message' => 'Split order berhasil dihapus.']);
    }

    public function getBahanBakuByKitchen($kitchenId)
    {
        // Pastikan Kitchen ID valid/ada
        if (!$kitchenId) {
            return response()->json([], 400);
        }

        // Ambil bahan baku berdasarkan kitchen_id
        // Menggunakan 'values()' agar array index di-reset (antisipasi JS object vs array)
        $bahan = BahanBaku::where('kitchen_id', $kitchenId)
            ->whereNull('deleted_at') // Pastikan tidak terhapus (jika pakai soft deletes)
            ->with('unit')            // Load relasi satuan agar tampil di dropdown (kg, gram, dll)
            ->orderBy('nama')
            ->get()
            ->values();

        return response()->json($bahan);
    }

    public function printInvoice(Submission $submission)
    {
        // Pastikan memuat relasi yang dibutuhkan untuk invoice
        $submission->load(['kitchen', 'supplier', 'details.bahan_baku.unit', 'details.recipeBahanBaku']);

        // Generate PDF
        // 'setPaper' bisa disesuaikan ('a4', 'letter', 'f4', dll)
        $pdf = Pdf::loadView('transaction.invoice-submission', compact('submission'))
            ->setPaper('a4', 'portrait');

        // OPSI A: Langsung Download (Browser tidak buka tab baru, file langsung terunduh)
        return $pdf->download($submission->kode . '.pdf');

        // OPSI B: Stream (Buka PDF di tab browser yang sama - jika ingin print manual dari PDF reader)
        // return $pdf->stream('PO-' . $submission->kode . '.pdf');
    }

    // app/Http/Controllers/SubmissionApprovalController.php

    public function printParentInvoice(Submission $submission)
    {
        // 1. Pastikan ini adalah Parent dan Status Selesai
        abort_if(!$submission->isParent(), 404);

        // 2. Load semua relasi: Children (Split Order), Supplier, dan Detail Barang
        $submission->load([
            'kitchen',
            'children.supplier',
            'children.details.bahan_baku.unit'
        ]);

        // 3. Kirim ke view invoice parent
        return view('transaction.invoice-submissionParent', compact('submission'));
    }

    protected function formatQtyWithUnit($qty, $unit)
    {
        if (!$unit) {
            return [
                'qty' => $qty,
                'unit' => '-',
            ];
        }

        $satuan = strtolower($unit->satuan);

        // gram → kg
        if ($satuan === 'gram' && $qty >= 1000) {
            return [
                'qty' => $qty / 1000,
                'unit' => 'kg',
            ];
        }

        // ml → liter
        if ($satuan === 'ml' && $qty >= 1000) {
            return [
                'qty' => $qty / 1000,
                'unit' => 'liter',
            ];
        }

        // default (tidak dikonversi)
        return [
            'qty' => $qty,
            'unit' => $unit->satuan,
        ];
    }
}
