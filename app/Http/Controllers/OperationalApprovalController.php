<?php

namespace App\Http\Controllers;

use App\Models\submissionOperational;
use Illuminate\Http\Request;

class OperationalApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $submissions = submissionOperational::with(['details.operational', 'kitchen'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('transaction.operational-approval', compact('submissions'));
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
        //
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
