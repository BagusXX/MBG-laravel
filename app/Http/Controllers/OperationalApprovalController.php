<?php

namespace App\Http\Controllers;

use App\Models\submissionOperational;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OperationalApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // ==================================================================
        // 1. FILTER DAPUR BERDASARKAN ROLE (LOGIK SAMA DENGAN SUBMISSION)
        // ==================================================================

        // Cek permission/role untuk melihat semua dapur
        // Sesuaikan 'Super Admin' dengan nama role di DB Anda
        // Atau gunakan permission: if ($user->can('view_all_kitchens'))
        $kitchens = $user->kitchens()->orderBy('nama')->get();
        $kitchenCodes = $kitchens->pluck('kode'); // A

        $submissions = submissionOperational::onlyParent()
            ->pengajuan()
            ->with(['details.operational', 'kitchen', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->get();

        $suppliers = Supplier::with('kitchens')->orderBy('nama')->get();

        return view('transaction.operational-approval', compact('submissions', 'suppliers', 'kitchens'));
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
            'parent_id' => 'required|exists:submission_operationals,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
        ]);

        $parent = submissionOperational::onlyParent()->findOrFail($request->parent_id);

        if (in_array($parent->status, ['ditolak', 'selesai'])) {
            return back()->with('error', 'Gagal: Pengajuan yang sudah Ditolak atau Selesai tidak dapat diedit.');
        }

        DB::transaction(function () use ($parent, $request) {
            // A. Buat Header PO (Child)
            $childCount = $parent->children()->count() + 1;
            $child = submissionOperational::create([
                'kode' => $parent->kode . '-' . $childCount,
                'parent_id' => $parent->id,
                'tipe' => 'disetujui',
                'kitchen_kode' => $parent->kitchen_kode,
                'supplier_id' => $request->supplier_id,
                'status' => 'disetujui',
                'tanggal' => now(),
                'total_harga' => 0
            ]);

            $totalDapur = 0;

            // B. Pindahkan Item ke Child (Copy Data dari Parent)
            foreach ($request->items as $detailId) {
                // Ambil data TERBARU dari parent (yang mungkin barusan diedit user)
                $parentDetail = $parent->details()->findOrFail($detailId);

                $inputHarga = $request->input("harga.$detailId");
                $finalHargaDapur = is_numeric($inputHarga) ? $inputHarga : $parentDetail->harga_dapur;

                $subtotalDapur = $parentDetail->qty * $finalHargaDapur;

                $hargaMitra = $parentDetail->harga_mitra;
                $subtotalMitra = $parentDetail->qty * $hargaMitra;

                // Create Detail Child (Snapshot)
                $child->details()->create([
                    'operational_id' => $parentDetail->operational_id,
                    'qty' => $parentDetail->qty,
                    'harga_satuan' => $finalHargaDapur,
                    'harga_dapur' => $finalHargaDapur,
                    'harga_mitra' => $hargaMitra,
                    'subtotal_dapur' => $subtotalDapur,
                    'subtotal_mitra' => $subtotalMitra,
                    'keterangan' => $parentDetail->keterangan,
                ]);

                $totalDapur += $subtotalDapur;
            }

            // C. Update Total & Status
            $child->update(['total_harga' => $totalDapur]);
            $parent->update(['status' => 'diproses']);
        });

        return back()
            ->with('success', 'Item berhasil di-split ke supplier.')
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
        $submission = submissionOperational::with('details')->findOrFail($id);

        if (in_array($submission->status, ['selesai', 'ditolak'])) {
            return back()->with('error', 'Data tidak dapat diubah karena status sudah final (Selesai/Ditolak).');
        }

        // =====================
        // VALIDATION (FLEKSIBEL)
        // =====================
        $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'keterangan' => 'nullable|string',
            'tanggal' => 'nullable|date',
            // Validasi Array Items untuk Update Harga/Qty
            'items' => 'nullable|array',
            // Pastikan ID detail valid dan ada
            'items.*.id' => 'sometimes | required|exists:submission_operational_details,id',
            'items.*.harga_dapur' => 'required|numeric|min:0',
            'items.*.harga_mitra' => 'required|numeric|min:0',
            'items.*.qty' => 'required|numeric|min:1',

        ]);

        DB::transaction(function () use ($submission, $request) {
            // A. Update Header (Data Umum)
            $submission->update([
                'supplier_id' => $request->supplier_id ?? $submission->supplier_id,
                'keterangan' => $request->keterangan ?? $submission->keterangan,
                'tanggal' => $request->tanggal ?? $submission->tanggal,
            ]);

            // B. Update Detail Items (Harga & Hitung Ulang Total)
            if ($request->has('items')) {
                $totalBaru = 0;

                foreach ($request->items as $itemData) {
                    // Cari detail berdasarkan ID yang dikirim form
                    // Gunakan $submission->details() untuk memastikan detail memang milik parent ini (security)
                    $detail = $submission->details()->find($itemData['id']);

                    if ($detail) {
                        $hargaDapurBaru = $itemData['harga_dapur'];
                        $hargaMitraBaru = $itemData['harga_mitra'];
                        $qtyBaru = $itemData['qty'];
                        $subtotalDapur = $hargaDapurBaru * $qtyBaru;
                        $subtotalMitra = $hargaMitraBaru * $qtyBaru;

                        // Update baris detail
                        $detail->update([
                            'harga_dapur' => $hargaDapurBaru,
                            'harga_mitra' => $hargaMitraBaru,
                            'qty' => $qtyBaru,
                            'subtotal_dapur' => $subtotalDapur,
                            'subtotal_mitra' => $subtotalMitra
                        ]);

                        $totalDapurBaru += $subtotalDapur;
                        $totalMitraBaru += $subtotalMitra;
                    }
                }

                // C. Update Total Harga di Header Submission
                $submission->update(['total_harga' => $totalDapurBaru]);
            }
        });

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

        if ($submission->status === 'disetujui') {
            return back()->with('error', 'Permintaan sudah disetujui');
        }

        $submission->delete();

        return back()->with('success', 'Permintaan berhasil dihapus');
    }

    public function updateStatus(Request $request, $id)
    {
        $submission = submissionOperational::onlyParent()->findOrFail($id);
        $status = $request->status; // <-- INI PENTING

        // =====================
        // STATUS: DITOLAK
        // =====================
        if ($status === 'ditolak') {

            $request->validate([
                'keterangan' => 'required|string'
            ]);

            // ❌ Tidak boleh ditolak jika sudah punya child
            if ($submission->children()->exists()) {
                return back()->with('error', 'Pengajuan tidak bisa ditolak karena sudah diproses');
            }

            // ❌ Status harus masih diajukan
            if ($submission->status !== 'diajukan') {
                return back()->with('error', 'Status pengajuan tidak valid untuk ditolak');
            }

            $submission->update([
                'status' => 'ditolak',
                'keterangan' => $request->keterangan
            ]);
        }

        // =====================
        // STATUS: SELESAI
        // =====================
        elseif ($status === 'selesai') {

            // ❌ Harus sudah diproses
            if ($submission->status !== 'diproses') {
                return back()->with('error', 'Pengajuan belum diproses');
            }

            $submission->update([
                'status' => 'selesai',
                'tanggal_selesai' => now()
            ]);
        }

        return back()->with('success', 'Status pengajuan berhasil diperbarui');
    }


    public function destroyChild($id)
    {
        $child = submissionOperational::with('parentSubmission')->findOrFail($id);
        $parent = $child->parentSubmission;


        // ❌ Pastikan ini child
        if (!$child->isChild()) {
            return back()->with('error', 'Data tidak valid');
        }

        // ❌ Parent harus diproses
        if ($parent->status !== 'diproses') {
            return back()->with(
                'error',
                'Approval tidak bisa dihapus karena status pengajuan sudah berubah'
            );
        }

        // ❌ Child harus disetujui
        if ($child->status !== 'disetujui') {
            return back()->with(
                'error',
                'Hanya approval yang disetujui yang dapat dihapus'
            );
        }

        $child->delete();

        if (!$parent->children()->exists()) {
            $parent->update(['status' => 'diajukan']);
        }

        return back()
            ->with('success', 'Approval supplier berhasil dihapus')
            ->with('reopen_modal', $parent->id);
    }
    public function selesai($id)
    {
        $submission = submissionOperational::findOrFail($id);

        // Validasi status
        if ($submission->status !== 'Diproses') {
            return back()->with('error', 'Pengajuan belum diproses');
        }

        $submission->status = 'Selesai';
        $submission->tanggal_selesai = now(); // jika ada kolom
        $submission->save();

        return back()->with('success', 'Pengajuan berhasil diselesaikan');
    }

    public function invoiceParent($id)
    {
        $parent = submissionOperational::with([
            'kitchen',
            'children.details.operational',
            'children.supplier'
        ])
            ->onlyParent()
            ->findOrFail($id);

        // ❌ hanya boleh jika selesai
        if ($parent->status !== 'selesai') {
            abort(403, 'Invoice hanya tersedia untuk pengajuan selesai');
        }

        // 3. Generate PDF (Ubah dari return view ke Pdf::loadView)
        $pdf = Pdf::loadView(
            'transaction.invoiceOperational-parent', // Pastikan nama file view sesuai
            compact('parent')
        )->setPaper('A4', 'portrait');

        // 4. Download PDF
        return $pdf->stream(
            'Invoice-Rekap-' . $parent->kode . '.pdf'
        );
    }

    // App\Http\Controllers\OperationalApprovalController.php

    public function updatePrices(Request $request, $id)
    {
        $parent = submissionOperational::findOrFail($id);

        if (in_array($parent->status, ['ditolak', 'selesai'])) {
            return back()->with('error', 'Gagal: Tidak bisa melakukan Split Order pada pengajuan Ditolak/Selesai.');
        }

        // Validasi input array
        $request->validate([
            'details' => 'required|array',
            'details.*.id' => 'required|exists:submission_operational_details,id',
            'details.*.qty' => 'required|numeric|min:0',
            'details.*.harga_dapur' => 'required|numeric|min:0',
            'details.*.harga_mitra' => 'nullable|numeric|min:0', // Opsional jika mau edit harga mitra juga
        ]);

        DB::transaction(function () use ($parent, $request) {
            $totalParent = 0;

            foreach ($request->details as $item) {
                $detail = $parent->details()->find($item['id']);

                if ($detail) {
                    $qty = $item['qty'];
                    $hDapur = $item['harga_dapur'];
                    $hMitra = $item['harga_mitra'] ?? $detail->harga_mitra; // Pakai lama jika tidak dikirim

                    // Update Parent Detail
                    $detail->update([
                        'qty' => $qty,
                        'harga_satuan' => $hDapur,
                        'harga_dapur' => $hDapur,
                        'harga_mitra' => $hMitra,
                        'subtotal_dapur' => $qty * $hDapur,
                        'subtotal_mitra' => $qty * $hMitra
                    ]);

                    $totalParent += ($qty * $hDapur);

                    // --- SINKRONISASI (OPSIONAL) ---
                    // Jika item ini SUDAH pernah di-split ke PO anak, update juga anaknya
                    // agar data tidak "belang".
                    foreach ($parent->children as $child) {
                        $childDetail = $child->details()
                            ->where('operational_id', $detail->operational_id)
                            ->first();

                        // Update anak hanya jika statusnya masih 'diproses' (belum selesai/dikirim)
                        if ($childDetail && in_array($child->status, ['diproses', 'disetujui'])) {
                            $childDetail->update([
                                'qty' => $qty,
                                'harga_satuan' => $hDapur,
                                'harga_dapur' => $hDapur,
                                'harga_mitra' => $hMitra,
                                'subtotal_dapur' => $qty * $hDapur,
                                'subtotal_mitra' => $qty * $hMitra
                            ]);
                            // Update total header anak
                            $child->update(['total_harga' => $child->details->sum('subtotal_dapur')]);
                        }
                    }
                }
            }

            // Update Total Parent
            $parent->update(['total_harga' => $totalParent]);
        });

        return back()
            ->with('success', 'Perubahan Harga & Qty berhasil disimpan.')
            ->with('reopen_modal', $parent->id);
    }
}
