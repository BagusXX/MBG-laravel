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
            'details.recipe.bahan_baku.unit',
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
                'recipe.bahan_baku.unit',
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

    public function getBahanBakuByKitchen(Kitchen $kitchen)
    {
        return response()->json(
            BahanBaku::where('kitchen_id', $kitchen->id)
                ->with('unit')
                ->get()
        );
    }

    public function getSubmissionData(Submission $submission)
    {
        $submission->load(['kitchen', 'menu']);

        return response()->json([
            'id' => $submission->id,
            'kode' => $submission->kode,
            'tanggal' => $submission->tanggal,
            'kitchen' => $submission->kitchen?->nama,
            'menu' => $submission->menu?->nama,
            'porsi' => $submission->porsi,
            'status' => $submission->status,
        ]);
    }

    public function splitToSupplier(Request $request, Submission $parent)
    {
        abort_if(!$parent->isParent(), 403, 'Hanya parent submission');
        abort_if($parent->status !== 'diproses', 403, 'Harus diproses dahulu');

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        // Cegah duplikat supplier
        $exists = Submission::where('parent_id', $parent->id)
            ->where('supplier_id', $request->supplier_id)
            ->exists();

        abort_if($exists, 422, 'Supplier sudah pernah dibuat');

        DB::transaction(function () use ($parent, $request) {

            /* ================= CHILD HEADER ================= */

            $child = Submission::create([
                'kode' => $parent->kode . '-S' . str_pad(
                    Submission::where('parent_id', $parent->id)->count() + 1,
                    2,
                    '0',
                    STR_PAD_LEFT
                ),
                'tanggal' => now(),
                'kitchen_id' => $parent->kitchen_id,
                'menu_id' => $parent->menu_id,
                'porsi' => $parent->porsi,
                'total_harga' => 0,
                'tipe' => 'disetujui',
                'status' => 'diproses',
                'parent_id' => $parent->id,
                'supplier_id' => $request->supplier_id,
            ]);

            /* ================= COPY DETAIL ================= */

            $total = 0;

            foreach ($parent->details as $detail) {

                $hargaMitra = $detail->harga_mitra
                    ?? $detail->harga_dapur
                    ?? $detail->harga_satuan;

                $subtotal = $hargaMitra * $detail->qty_digunakan;

                SubmissionDetails::create([
                    'submission_id' => $child->id,
                    'recipe_bahan_baku_id' => $detail->recipe_bahan_baku_id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'qty_digunakan' => $detail->qty_digunakan,
                    'harga_satuan' => $detail->harga_satuan,
                    'harga_dapur' => null,              // child tidak pakai dapur
                    'harga_mitra' => $hargaMitra,
                    'subtotal_harga' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $child->update(['total_harga' => $total]);
        });

        return back()->with('success', 'Child submission berhasil dibuat');
    }





}
