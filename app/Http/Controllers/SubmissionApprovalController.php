<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\Submission;
use App\Models\SubmissionDetails;
use App\Models\BahanBaku;
use App\Models\Supplier;
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

    protected function recalculateTotal(Submission $submission)
    {
        $total = $submission->details()
            ->sum(DB::raw('harga_dapur * qty_digunakan'));

        $submission->update(['total_harga' => $total]);
    }

    /* ================= INDEX ================= */

    public function index()
    {
        $submissions = Submission::with([
            'kitchen',
            'menu',
            'supplier',
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
        abort_if($submission->status === 'selesai', 403, 'Sudah selesai');

        $request->validate([
            'status' => 'required|in:selesai,ditolak',
        ]);

        $submission->update(['status' => $request->status]);

        return back()->with('success', 'Status child diperbarui');
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
        // 1. Cek Status
        if (in_array($submission->status, ['selesai', 'ditolak'])) {
            return response()->json(['message' => 'Pengajuan sudah terkunci dan tidak bisa diedit.'], 403);
        }

        // 2. Validasi (Gunakan 'nullable' agar input kosong tidak error)
        $request->validate([
            'details' => 'required|array',
            'details.*.id' => 'required|exists:submission_details,id',
            'details.*.qty_digunakan' => 'required|numeric|min:0', // Qty wajib angka
            'details.*.harga_dapur' => 'nullable|numeric|min:0', // Boleh kosong/0
            'details.*.harga_mitra' => 'nullable|numeric|min:0', // Boleh kosong/0
        ]);

        DB::transaction(function () use ($request, $submission) {
            foreach ($request->details as $row) {
                $detail = SubmissionDetails::find($row['id']);

                // Pastikan detail milik submission ini (Security check)
                if ($detail && $detail->submission_id === $submission->id) {

                    // Jika input kosong/null, anggap 0
                    $hargaDapur = $row['harga_dapur'] ?? 0;
                    $hargaMitra = $row['harga_mitra'] ?? 0;

                    // Update Data
                    $detail->update([
                        'qty_digunakan' => $row['qty_digunakan'],
                        'harga_dapur' => $hargaDapur,
                        'harga_mitra' => $hargaMitra,
                        // Subtotal akan dihitung otomatis oleh Model (booted/saving)
                        // Tapi jika ingin memaksa update timestamp agar trigger observer:
                        'updated_at' => now(),
                    ]);
                }
            }

            // Hitung ulang total parent
            $this->recalculateTotal($submission);
        });

        return response()->json(['success' => true, 'message' => 'Perubahan berhasil disimpan']);
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
        $submission->load(['kitchen', 'menu', 'children.supplier']); // Load children & suppliernya

        // Format data children untuk riwayat
        $history = $submission->children->map(function ($child) {
            return [
                'id' => $child->id,
                'kode' => $child->kode,
                'supplier_nama' => $child->supplier->nama ?? 'Umum',
                'status' => $child->status,
                'total' => $child->total_harga,
                'item_count' => $child->details()->count() // Opsional: jumlah item
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
            $childSequence = Submission::where('parent_id', $submission->id)->count() + 1;
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
                    'harga_dapur' => null, // Child ke supplier tidak butuh harga dapur
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

        return view('transaction.invoice-submission', compact('submission'));
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
