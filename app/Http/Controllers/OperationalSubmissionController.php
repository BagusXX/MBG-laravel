<?php

namespace App\Http\Controllers;

use App\Models\operationals;
use App\Models\submissionOperational;
use App\Models\submissionOperationalDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OperationalSubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Ambil List Kitchen User (Untuk Dropdown & Query)
        // Pastikan relasi di model User bernama 'kitchens'
        $kitchens = $user->kitchens;
        $kitchenCodes = $kitchens->pluck('kode'); // Asumsi 'kode' adalah PK/FK

        // 2. Ambil Master Barang (Untuk Dropdown Barang)
        $masterBarang = operationals::select('id', 'nama', 'kitchen_kode', 'harga_default')->get();

        // 3. Ambil Data Submission
        $submissions = submissionOperational::with(['kitchen', 'details.barang'])
            ->whereIn('kitchen_kode', $kitchenCodes)
            ->orderBy('tanggal','desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('transaction.operational-submission', compact('submissions', 'kitchens', 'masterBarang'));
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
        $request->validate([
            'kitchen_kode' => 'required|exists:kitchens,kode',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'items' => 'required|array',
            'items.*.barang_id' => 'required|exists:operationals,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.harga_satuan' => 'required|numeric',
            'items.*.keterangan'   => 'nullable|string'
        ]);

        return DB::transaction(function () use ($request) {
            $prefix = 'POPR';

            // Menggunakan lockForUpdate untuk mencegah duplicate nomor urut di traffic tinggi
            $lastSubmission = SubmissionOperational::where('kode', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $nextNumber = $lastSubmission ? ((int) substr($lastSubmission->kode, 4)) + 1 : 1;
            $newKode = $prefix . sprintf("%04d", $nextNumber);

            $submission = SubmissionOperational::create([
                'kode' => $newKode,
                'kitchen_kode' => $request->kitchen_kode,
                'status' => 'diajukan',
                'total_harga' => 0,
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan
            ]);

            $total = 0;
            foreach ($request->items as $item) {
                $subtotal = $item['qty'] * $item['harga_satuan'];

                submissionOperationalDetails::create([
                    'operational_submission_id' => $submission->id,
                    'operational_id' => $item['barang_id'], // Di migrasi ada dua field ini
                    'qty' => $item['qty'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $subtotal,
                    'keterangan'   => $item['keterangan'] ?? null
                ]);
                $total += $subtotal;
            }

            $submission->update(['total_harga' => $total]);

            return redirect()->back()->with('success', "Pengajuan $newKode berhasil dibuat");
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //

        $user = auth()->user();
        $kitchens = $user->kitchens()->pluck('kode');

        $submission = submissionOperational::with([
            'details.barang',
            'kitchen'
        ])
            ->whereIn('kitchen_kode', $kitchens)
            ->findOrFail($id);

        return view('submission.show', compact('submission'));
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
        $submission = submissionOperational::findOrFail($id);

        if ($submission->status === 'diterima') {
            return back()->with('error', 'Pengajuan sudah diterima');
        }

        $submission->details()->delete();
        $submission->delete();

        return back()->with('success', 'Pengajuan dihapus');
    }
}
