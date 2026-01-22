<?php

namespace App\Http\Controllers;

use App\Models\operationals;
use App\Models\submissionOperational;
use App\Models\submissionOperationalDetails;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $kitchens = $user->kitchens()->orderBy('nama')->get();
        $kitchenCodes = $kitchens->pluck('kode'); // Asumsi 'kode' adalah PK/FK

        // 2. Ambil Master Barang (Untuk Dropdown Barang)
        $masterBarang = operationals::select('id', 'nama', 'kitchen_kode', 'harga_default')->get();

        // 3. Ambil Data Submission
        $submissions = submissionOperational::onlyParent()
            ->pengajuan()
            ->with([
                'kitchen',
                'details.operational',
                'children.supplier',
                'children.details.operational'
            ])
            ->whereIn('kitchen_kode', $kitchenCodes)
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $suppliers = Supplier::orderBy('nama')->get();


        return view('transaction.operational-submission', compact('submissions', 'kitchens', 'masterBarang', 'suppliers'));
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
            'items.*.keterangan' => 'nullable|string'
        ]);

        return DB::transaction(function () use ($request) {
            $prefix = 'POPR';

            // Menggunakan lockForUpdate untuk mencegah duplicate nomor urut di traffic tinggi
            $lastSubmission = SubmissionOperational::where('kode', 'like', $prefix . '%')
                ->orderBy('kode', 'desc')
                ->lockForUpdate()
                ->first();

            if ($lastSubmission) {
                // Ambil 4 digit terakhir
                $lastNumber = (int) substr($lastSubmission->kode, 4);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            // Loop untuk memastikan kode benar-benar belum ada (Double check)
            do {
                $newKode = $prefix . sprintf("%04d", $nextNumber);
                $exists = SubmissionOperational::where('kode', $newKode)->exists();
                if ($exists) {
                    $nextNumber++;
                }
            } while ($exists);

            $submission = submissionOperational::create([
                'kode' => $newKode,
                'parent_id' => null,
                'tipe' => 'pengajuan',
                'kitchen_kode' => $request->kitchen_kode,
                'supplier_id' => null,
                'status' => 'diajukan',
                'total_harga' => 0, // Nanti diupdate
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan
            ]);

            // 1. Tarik Data Master Barang Sekaligus (Optimasi Query)
            $itemIds = collect($request->items)->pluck('barang_id');
            // Pastikan nama kolom harga di tabel operationals benar (misal: harga_satuan / harga_default)
            $masterItems = operationals::whereIn('id', $itemIds)->get()->keyBy('id');

            $totalDapur = 0;

            foreach ($request->items as $item) {
                $barangId = $item['barang_id'];
                $qty = $item['qty'];


                $hargaDapur =  0;
                $hargaMitra = 0;

                // 3. HITUNG SUBTOTAL
                $subtotalDapur = $qty * $hargaDapur;
                $subtotalMitra = $qty * $hargaMitra;

                submissionOperationalDetails::create([
                    'operational_submission_id' => $submission->id,
                    'operational_id' => $barangId,
                    'qty' => $qty,
                    'harga_satuan' => $hargaDapur,

                    // Simpan Harga Snapshot
                    'harga_dapur' => $hargaDapur,
                    'harga_mitra' => $hargaMitra,

                    // Simpan Subtotal Spesifik (Hapus kolom 'subtotal' generic)
                    'subtotal_dapur' => $subtotalDapur,
                    'subtotal_mitra' => $subtotalMitra,

                    'keterangan' => $item['keterangan'] ?? null
                ]);

                $totalDapur += $subtotalDapur;
            }

            // Update Total Header (Pakai total dapur)
            $submission->update(['total_harga' => $totalDapur]);

            return redirect()->back()->with('success', "Pengajuan $newKode berhasil dibuat.");
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $kitchens = $user->kitchens()->pluck('kode');

        $submission = submissionOperational::with([
            // Load relasi 'operational' untuk ambil Nama Barang & Satuan
            'details.operational',
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
        // 1. Validasi: Hapus harga_satuan
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'items' => 'required|array',
            'items.*.barang_id' => 'required|exists:operationals,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.keterangan' => 'nullable|string'
        ]);

        return DB::transaction(function () use ($request, $id) {
            $submission = submissionOperational::findOrFail($id);

            if ($submission->status !== 'diajukan') {
                return back()->with('error', 'Pengajuan tidak dapat diubah karena status sudah diproses');
            }

            // Update Header
            $submission->update([
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan
            ]);

            // Hapus detail lama
            submissionOperationalDetails::where('operational_submission_id', $submission->id)->delete();

            // --- LOGIKA BARU INSERT DETAIL ---
            $itemIds = collect($request->items)->pluck('barang_id');
            $masterItems = operationals::whereIn('id', $itemIds)->get()->keyBy('id');

            $totalDapur = 0;

            foreach ($request->items as $item) {
                $barangId = $item['barang_id'];
                $qty = $item['qty'];

               

                $hargaDapur = 0; // Ambil dari master
                $hargaMitra = 0;  // Ambil dari master

                $subtotalDapur = $qty * $hargaDapur;
                $subtotalMitra = $qty * $hargaMitra;

                submissionOperationalDetails::create([
                    'operational_submission_id' => $submission->id,
                    'operational_id' => $barangId,
                    'qty' => $qty,
                    'harga_satuan' => $hargaDapur,
                    'harga_dapur' => $hargaDapur,
                    'harga_mitra' => $hargaMitra,
                    'subtotal_dapur' => $subtotalDapur,
                    'subtotal_mitra' => $subtotalMitra,
                    'keterangan' => $item['keterangan'] ?? null
                ]);

                $totalDapur += $subtotalDapur;
            }

            // Update total harga header
            $submission->update(['total_harga' => $totalDapur]);

            return back()->with('success', 'Pengajuan berhasil diperbarui');
        });
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $submission = submissionOperational::findOrFail($id);

        // ❌ Parent tidak boleh dihapus + sudah punya child
        if ($submission->isParent() && $submission->children_count > 0) {
            return back()->with(
                'error',
                'Pengajuan utama tidak boleh dihapus'
            );
        }

        // ❌ Approval yang sudah approved tidak boleh dihapus
        if ($submission->isChild() && $submission->status === 'disetujui') {
            return back()->with(
                'error',
                'Data yang sudah disetujui tidak bisa dihapus'
            );
        }

        $submission->delete();

        return back()->with('success', 'Data berhasil dihapus');
    }

    public function invoice($id)
    {
        $submission = submissionOperational::with([
            'supplier',
            'kitchen',
            'details.operational'
        ])->findOrFail($id);

        // Proteksi: hanya yang disetujui
        if (!$submission->isChild() || $submission->status !== 'disetujui') {
            abort(403, 'Invoice hanya untuk approval supplier yang disetujui');
        }


        $pdf = Pdf::loadView(
            'transaction.invoice-operational',
            compact('submission')
        )->setPaper('A4', 'portrait');

        return $pdf->download(
            'Invoice-Operasional-' . $submission->kode . '.pdf'
        );
    }
    // Tambahkan method ini di paling bawah class
    public function invoiceParent($id)
    {
        $parent = submissionOperational::with([
            'kitchen',
            'children.details.operational',
            'children.supplier'
        ])
            ->onlyParent()
            ->findOrFail($id);

        // Validasi: Hanya bisa cetak jika status 'selesai'
        if ($parent->status !== 'selesai') {
            abort(403, 'Invoice rekapitulasi hanya tersedia untuk pengajuan yang sudah selesai.');
        }

        // 3. Generate PDF (Ubah dari return view ke Pdf::loadView)
        $pdf = Pdf::loadView(
            'transaction.invoiceOperational-parent', // Pastikan nama file view sesuai
            compact('parent')
        )->setPaper('A4', 'portrait');

        // 4. Download PDF
        return $pdf->download(
            'Invoice-Rekap-' . $parent->kode . '.pdf'
        );
    }
}
