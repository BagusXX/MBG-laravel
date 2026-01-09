<?php

namespace App\Http\Controllers;

use App\Models\submissionOperational;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationalApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $submissions = submissionOperational::parent()
            ->pengajuan()
            ->with(['details.operational', 'kitchen', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->get();

        $suppliers = Supplier::orderBy('nama')->get();

        return view('transaction.operational-approval', compact('submissions', 'suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'parent_id' => 'required|exists:submission_operationals,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*' => 'exists:submission_operational_details,id',
        ]);

        $parent = submissionOperational::parent()->findOrFail($request->parent_id);

        DB::transaction(function () use ($parent, $request) {
            // hitung child ke-n
            $childCount = $parent->children()->count() + 1;

            $childCode = $parent->kode . '-' . $childCount;

            $child = submissionOperational::create([
                'kode' => $childCode,
                'parent_id' => $parent->id,
                'tipe' => 'disetujui',
                'kitchen_kode' => $parent->kitchen_kode,
                'supplier_id' => $request->supplier_id,
                'status' => 'disetujui',
                'tanggal' => now(),
            ]);

            $total = 0;

            foreach ($request->items as $detailId) {
                $detail = $parent->details()->findOrFail($detailId);

                $subtotal = $detail->qty * $detail->harga_satuan;

                $child->details()->create([
                    'operational_id' => $detail->operational_id,
                    'qty' => $detail->qty,
                    'harga_satuan' => $detail->harga_satuan,
                    'subtotal' => $subtotal,
                    'keterangan' => $detail->keterangan,
                ]);

                $total += $subtotal;
            }

            $child->update(['total_harga' => $total]);
            $parent->update(['status' => 'diproses']);
        });

        return back()
            ->with('success', 'Approval supplier berhasil dibuat')
            ->with('reopen_modal', $parent->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $submission = submissionOperational::with([
            'details.operational',
            'kitchen'
        ])->findOrFail($id);

        return view('transaction.operational-approval', compact('submission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $submission = submissionOperational::findOrFail($id);

        // ❗ Proteksi status
        if ($submission->status !== 'diajukan') {
            return back()->with('error', 'Data tidak dapat diubah karena status bukan diajukan');
        }

        // =====================
        // VALIDATION (FLEKSIBEL)
        // =====================
        $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'keterangan' => 'nullable|string',
            'tanggal' => 'nullable|date',
        ]);

        // =====================
        // UPDATE DATA
        // =====================
        $submission->update([
            'supplier_id' => $request->supplier_id ?? $submission->supplier_id,
            'keterangan' => $request->keterangan ?? $submission->keterangan,
            'tanggal' => $request->tanggal ?? $submission->tanggal,
        ]);

        return back()->with('success', 'Data pengajuan berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $submission = SubmissionOperational::findOrFail($id);

        if ($submission->isParent()) {
            return back()->with('error', 'Pengajuan utama tidak boleh dihapus');
        }

        if ($submission->status === 'approved') {
            return back()->with('error', 'Permintaan sudah disetujui');
        }

        $submission->delete();

        return back()->with('success', 'Permintaan berhasil dihapus');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,ditolak',
            'keterangan' => 'required_if:status,ditolak'
        ]);

        $submission = SubmissionOperational::child()->findOrFail($id);

        $submission->update([
            'status' => $request->status,
            'keterangan' => $request->keterangan
        ]);

        return back()->with('success', 'Status pengajuan berhasil diperbarui');
    }
}
