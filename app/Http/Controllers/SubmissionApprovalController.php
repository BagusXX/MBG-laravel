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
        abort_if(!$submission->isChild(), 403, 'Hanya child submission');
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

    public function updateHarga(Request $request, Submission $submission)
    {
        $this->ensureEditable($submission);

        $request->validate([
            'details' => 'required|array',
            'details.*.id' => 'required|exists:submission_details,id',
            'details.*.qty_digunakan' => 'required|numeric|min:0.0001',
            'details.*.harga_dapur' => 'required|numeric|min:0',
            'details.*.harga_mitra' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $submission) {
            foreach ($request->details as $row) {
                $detail = SubmissionDetails::where('id', $row['id'])
                    ->where('submission_id', $submission->id)
                    ->firstOrFail();

                $detail->update([
                    'qty_digunakan' => $row['qty_digunakan'],
                    'harga_dapur' => $row['harga_dapur'],
                    'harga_mitra' => $row['harga_mitra'],
                ]);
            }

            $this->recalculateTotal($submission);
        });

        return response()->json(['success' => true]);
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

        return response()->json([
            'id' => $submission->id,
            'kode' => $submission->kode,
            'tanggal' => $submission->tanggal,
            'kitchen' => $submission->kitchen->nama,
            'menu' => $submission->menu->nama,
            'porsi' => $submission->porsi,
            'status' => $submission->status,
            'history' => $history // <--- Kirim data riwayat ke JS
        ]);
    }

    public function splitToSupplier(Request $request, Submission $parent)
    {


        // Validasi input checkbox
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'selected_details' => 'required|array', // Array ID dari checkbox
            'selected_details.*' => 'exists:submission_details,id',
        ]);

        if ($parent->status === 'diajukan') {
            $parent->update(['status' => 'diproses']);
        }

        abort_if(in_array($parent->status, ['selesai', 'ditolak']), 403, 'Pengajuan sudah ditutup');

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'selected_details' => 'required|array',
            'selected_details.*' => 'exists:submission_details,id',
        ]);
        
        DB::transaction(function () use ($parent, $request) {
            // 1. Buat Child Submission
            $child = Submission::create([
                'kode' => $parent->kode . '-' . (Submission::where('parent_id', $parent->id)->count() + 1),
                'tanggal' => now(),
                'kitchen_id' => $parent->kitchen_id,
                'menu_id' => $parent->menu_id,
                'porsi' => $parent->porsi,
                'total_harga' => 0,
                'tipe' => 'disetujui',
                'status' => 'diproses', // Atau 'disetujui' sesuai flow Anda
                'parent_id' => $parent->id,
                'supplier_id' => $request->supplier_id,
            ]);

            $total = 0;

            // 2. Ambil detail yang DICENTANG saja
            $detailsToMove = SubmissionDetails::whereIn('id', $request->selected_details)->get();

            foreach ($detailsToMove as $detail) {
                $hargaMitra = $detail->harga_mitra ?? $detail->harga_dapur;
                $subtotal = $hargaMitra * $detail->qty_digunakan;

                // Copy ke Child
                SubmissionDetails::create([
                    'submission_id' => $child->id,
                    'recipe_bahan_baku_id' => $detail->recipe_bahan_baku_id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'qty_digunakan' => $detail->qty_digunakan,
                    'harga_satuan' => $detail->harga_satuan,
                    'harga_dapur' => null,
                    'harga_mitra' => $hargaMitra,
                    'subtotal_harga' => $subtotal,
                ]);

                $total += $subtotal;

                // OPSIONAL: Apakah item di Parent dihapus setelah di-split?
                // Jika YA, uncomment baris bawah:
                // $detail->delete(); 
            }

            $child->update(['total_harga' => $total]);

            // Update total parent jika item dihapus (jika pakai opsi hapus)
            // $this->recalculateTotal($parent);
        });

        return response()->json(['success' => true, 'message' => 'Order berhasil dipisah ke supplier']);
    }

}
