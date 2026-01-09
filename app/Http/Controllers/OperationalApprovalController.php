<?php

namespace App\Http\Controllers;

use App\Models\submissionOperational;
use App\Models\Supplier;
use Illuminate\Http\Request;

class OperationalApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $submissions = submissionOperational::with(['details.operational', 'kitchen', 'supplier'])
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
            'keterangan'  => 'nullable|string',
            'tanggal'     => 'nullable|date',
        ]);

        // =====================
        // UPDATE DATA
        // =====================
        $submission->update([
            'supplier_id' => $request->supplier_id ?? $submission->supplier_id,
            'keterangan'  => $request->keterangan ?? $submission->keterangan,
            'tanggal'     => $request->tanggal ?? $submission->tanggal,
        ]);

        return back()->with('success', 'Data pengajuan berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:diterima,ditolak',
            'keterangan' => 'required_if:status,ditolak'
        ]);

        $submission = SubmissionOperational::findOrFail($id);

        // Perbaikan: Jangan gunakan konstanta jika belum didefinisikan di model
        if ($submission->status === 'diterima') {
            return back()->with('error', 'Pengajuan sudah diterima dan tidak bisa diubah');
        }

        $submission->update([
            'status' => $request->status,
            'keterangan' => $request->status === 'ditolak' ? $request->keterangan : $submission->keterangan
        ]);

        return back()->with('success', 'Status pengajuan berhasil diperbarui');
    }
}
