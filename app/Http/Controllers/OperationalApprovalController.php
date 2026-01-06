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
        $submissions = submissionOperational::with(['details.barang', 'kitchen'])
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
            'details.barang',
            'kitchen'
        ])->findOrFail($id);

        return view('approval.show', compact('submission'));
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
            'status' => 'required|in:diterima,ditolak'
        ]);

        $submission = submissionOperational::findOrFail($id);

        if ($submission->status === 'diterima') {
            return back()->with('error', 'Status tidak bisa diubah');
        }

        $submission->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Status diperbarui');
    }
}
