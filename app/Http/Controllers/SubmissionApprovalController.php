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

    // KODE BARU (SOLUSI 1)
    protected function recalculateTotal(Submission $submission)
    {
        // 1. Ambil data detailnya (Load dari database ke PHP)
        // Kita gunakan get() agar menjadi Collection
        $details = $submission->details()->get();

        // 2. Hitung total menggunakan Collection method 'sum' milik Laravel
        // Logic ini berjalan di RAM server (PHP), bukan di Database
        $total = $details->sum(function ($detail) {

            // Pastikan nilai dikonversi jadi angka (float).
            // Tanda '?? 0' artinya: jika null, anggap 0.
            $harga = (float) ($detail->harga_dapur ?? 0);
            $qty = (float) ($detail->qty_digunakan ?? 0);

            return $harga * $qty;
        });

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
            'details.bahanBaku.unit'
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
        return response()->json(
            $submission->details()->with([
                'recipeBahanBaku.bahan_baku.unit',
                'bahanBaku.unit'
            ])->get()
        );
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
            'details.*.qty_digunakan' => 'required|numeric|min:0',
            'details.*.harga_dapur' => 'nullable|numeric|min:0',
            'details.*.harga_mitra' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $submission) {
            foreach ($request->details as $row) {

                // --- PERBAIKAN UTAMA ADA DISINI ---

                // 1. Cari detail MENGGUNAKAN $submission->details()
                // Supaya kita yakin detail ini milik pengajuan yang sedang dibuka
                $detail = $submission->details()->find($row['id']);

                if ($detail) {
                    // 2. SAMBUNGKAN RELASI SECARA MANUAL
                    // Ini yang memperbaiki error! Kita kasih tahu si detail: 
                    // "Hei, ini lho Parent Submission kamu ($submission)"
                    // Jadi saat disimpan, dia tidak bingung/error.
                    $detail->setRelation('submission', $submission);

                    $hargaDapur = $row['harga_dapur'] ?? 0;
                    $hargaMitra = $row['harga_mitra'] ?? 0;

                    $detail->update([
                        'qty_digunakan' => $row['qty_digunakan'],
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

    public function getSubmissionData(Submission $submission)
    {
        $submission->load([
            'kitchen',
            'menu',
            'children' => function ($q) {
                $q->withTrashed()->with([
                    'supplier',
                    'details.bahanBaku'
                ]);
            }
        ]);

        // Format data children untuk riwayat
        $history = $submission->children->map(function ($child) {
            return [
                'id' => $child->id,
                'kode' => $child->kode,
                'supplier_nama' => $child->supplier->nama ?? 'Umum',
                'status' => $child->status,
                'total' => $child->total_harga,
                'item_count' => $child->details()->count(), // Opsional: jumlah item
                'items' => $child->details->map(function ($detail) {
                    return [
                        'nama' => $detail->bahanBaku->nama ?? '-',
                        'qty' => $detail->qty_digunakan,
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
            'history' => $history,
            'suppliers' => $availableSuppliers // <--- Kirim data riwayat ke JS
        ]);
    }


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
            $detailsToCopy = SubmissionDetails::whereIn('id', $request->selected_details)->get();

            foreach ($detailsToCopy as $detail) {
                // Gunakan harga mitra jika ada, jika tidak pakai harga dapur
                $hargaMitra = $detail->harga_mitra ?? $detail->harga_dapur;
                $subtotal = $hargaMitra * $detail->qty_digunakan;

                SubmissionDetails::create([
                    'submission_id' => $child->id,
                    'recipe_bahan_baku_id' => $detail->recipe_bahan_baku_id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'qty_digunakan' => $detail->qty_digunakan,
                    'harga_satuan' => $detail->harga_satuan,
                    'harga_dapur' => $detail->harga_dapur, // Child ke supplier tidak butuh harga dapur
                    'harga_mitra' => $hargaMitra,
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
        $submission->load(['kitchen', 'supplier', 'details.bahanBaku.unit', 'details.recipeBahanBaku']);

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
            'children.details.bahanBaku.unit'
        ]);

        // 3. Kirim ke view invoice parent
        return view('transaction.invoice-submissionParent', compact('submission'));
    }
}
