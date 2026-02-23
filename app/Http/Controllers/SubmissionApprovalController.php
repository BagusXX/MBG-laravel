<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\Submission;
use App\Models\SubmissionDetails;
use App\Models\BahanBaku;
use App\Models\Unit;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubmissionApprovalController extends Controller
{
    /* ================= HELPER ================= */

    protected function ensureEditable(Submission $submission)
    {
        //abort_if(!$submission->isParent(), 403, 'Hanya parent submission');
        abort_if($submission->status === 'selesai', 403, 'Submission terkunci');
    }

    protected function userKitchenCodes()
    {
        return auth()->user()->kitchens()->pluck('kode')->toArray();
    }


    // KODE BARU (SOLUSI 1)
    protected function recalculateTotal(Submission $submission)
    {
        // Refresh relasi details
        // $submission->load('details');

        // Sum kolom subtotal_dapur (karena input user = subtotal)
        $total = $submission->details->sum('subtotal_dapur');

        $submission->update([
            'total_harga' => $total ?? 0 // Beri default 0 jika null
        ]);

        $submission->refresh();


    }

    /* ================= INDEX ================= */

    public function index()
    {
        $kitchenCodes = $this->userKitchenCodes();
        $submissions = Submission::with([
            'kitchen',
            'menu',
            'supplier',
            'details.bahan_baku'
        ])
            ->onlyParent()
            ->pengajuan()
            ->whereHas('kitchen', fn($q) => $q->whereIn('kode', $kitchenCodes))
            ->latest()
            ->paginate(10);

        $filteredSuppliers = Supplier::whereHas('kitchens', function ($query) use ($kitchenCodes) {
            $query->whereIn('kode', $kitchenCodes);
        })
            ->orderBy('nama')
            ->get();

        $filteredKitchens = Kitchen::whereIn('kode', $kitchenCodes)
            ->orderBy('nama')
            ->get();

        $filteredUnits = Unit::orderBy('satuan')->get();

        return view('transaction.submissionApproval', [
            'submissions' => $submissions,
            'kitchens' => $filteredKitchens,
            'suppliers' => $filteredSuppliers,
            'units' => $filteredUnits,
        ]);
    }

    /* ================= STATUS ================= */

    public function updateStatus(Request $request, Submission $submission)
    {
        abort_if(!$submission->isParent(), 403, 'Aksi ini hanya untuk Pengajuan Utama (Parent)');
        abort_if(in_array($submission->status, ['selesai', 'ditolak']), 403, 'Pengajuan sudah ditutup');

        $rules = [
            'status' => 'required|in:selesai,ditolak',
        ];

        if ($request->status === 'ditolak') {
            $rules['keterangan'] = 'required|string|min:5';
        }

        $validated = $request->validate($rules);

        $submission->update([
            'status' => $validated['status'],
            'keterangan' => $validated['status'] === 'ditolak'
                ? $validated['keterangan']
                : null,
        ]);

        return back()->with('success', 'Status berhasil diperbarui');
    }


    /* ================= DETAIL ================= */

    public function getDetails(Submission $submission)
    {
        $details = $submission->details()->with(['bahan_baku.unit'])->get();

        $data = $details->map(function ($detail) {

            return [
                'id' => $detail->id,
                'bahan_baku_id' => $detail->bahan_baku_id,
                'bahan_baku_nama' => $detail->bahan_baku->nama ?? 'Item Terhapus',
                'qty_digunakan' => (float) $detail->qty_digunakan,
                'satuan_id' => $detail->satuan_id,
                // Prioritas nama satuan: dari tabel details -> dari master bahan
                'nama_satuan' => $detail->unit->satuan ?? ($detail->bahan_baku->unit->satuan ?? '-'),

                // PERBAIKAN: Kirim Harga Satuan murni untuk Input
                'harga_dapur' => (float) $detail->harga_dapur,
                'harga_mitra' => (float) $detail->harga_mitra,

                // PERBAIKAN: Kirim Subtotal untuk Tampilan Readonly
                'subtotal_dapur' => (float) $detail->subtotal_dapur,
                'subtotal_mitra' => (float) $detail->subtotal_mitra,
            ];
        });

        return response()->json($data);
    }

    public function updateHarga(Request $request, Submission $submission)
    {
        if (in_array($submission->status, ['selesai', 'ditolak'])) {
            return response()->json(['message' => 'Pengajuan sudah terkunci.'], 403);
        }

        $request->validate([
            'details' => 'required|array',
            'details.*.id' => 'required|exists:submission_details,id',
            'details.*.qty_digunakan' => 'required|numeric|min:0',
            'details.*.harga_dapur' => 'nullable|numeric|min:0', // Sekarang kita anggap ini Harga Satuan
            'details.*.harga_mitra' => 'nullable|numeric|min:0', // Sekarang kita anggap ini Harga Satuan
        ]);

        try {
            DB::transaction(function () use ($request, $submission) {
                $existingDetails = $submission->details()->get()->keyBy('id');

                foreach ($request->details as $row) {
                    $detail = $existingDetails->get($row['id']);

                    if ($detail) {
                        $qty = (float) $row['qty_digunakan'];
                        $priceDapur = (float) ($row['harga_dapur'] ?? 0);
                        $priceMitra = (float) ($row['harga_mitra'] ?? 0);

                        // HITUNG SUB TOTAL OTOMATIS
                        $subtotalDapur = $qty * $priceDapur;
                        $subtotalMitra = $qty * $priceMitra;

                        // 1. Update Detail di Parent
                        $detail->update([
                            'qty_digunakan' => $qty,
                            'harga_dapur' => $priceDapur,
                            'subtotal_dapur' => $subtotalDapur,
                            'harga_mitra' => $priceMitra,
                            'subtotal_mitra' => $subtotalMitra,
                            'subtotal_harga' => $subtotalDapur,
                        ]);

                        // 2. Update Otomatis ke Child (Split Order) jika sudah ada
                        SubmissionDetails::whereHas('submission', function ($q) use ($submission) {
                            $q->where('parent_id', $submission->id);
                        })
                            ->where('bahan_baku_id', $detail->bahan_baku_id)
                            ->get()
                            ->each(function ($childDetail) use ($priceDapur, $priceMitra) {
                                // Qty di child bisa saja berbeda, maka hitung ulang subtotal child
                                $newSubDapur = $childDetail->qty_digunakan * $priceDapur;
                                $newSubMitra = $childDetail->qty_digunakan * $priceMitra;

                                $childDetail->update([
                                    'harga_dapur' => $priceDapur,
                                    'subtotal_dapur' => $newSubDapur,
                                    'harga_mitra' => $priceMitra,
                                    'subtotal_mitra' => $newSubMitra,
                                    'subtotal_harga' => $newSubDapur,
                                ]);

                                // Rekalkulasi total header child
                                $this->recalculateTotal($childDetail->submission);
                            });
                    }
                }

                // 3. Hitung ulang total di header parent
                $this->recalculateTotal($submission);
            });

            return response()->json(['success' => true, 'message' => 'Harga dan Subtotal berhasil diperbarui!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function addManualBahan(Request $request, Submission $submission)
    {
        $this->ensureEditable($submission);

        $request->validate([
            'bahan_baku_id' => 'required|exists:bahan_baku,id',
            'qty_digunakan' => 'required|numeric|min:0.0001',
            'satuan_id' => 'required|exists:units,id',
            'harga_total' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($submission, $request) {
                $qty = (float) $request->qty_digunakan;
                $subtotal = (float) ($request->harga_total ?? 0);
                $unitPrice = $qty > 0 ? ($subtotal / $qty) : 0;

                SubmissionDetails::create([
                    'submission_id' => $submission->id,
                    'bahan_baku_id' => $request->bahan_baku_id,
                    'satuan_id' => $request->satuan_id,
                    'qty_digunakan' => $qty,
                    'harga_dapur' => $unitPrice,
                    'subtotal_dapur' => $subtotal,
                    'harga_mitra' => $unitPrice, // Default disamakan dulu
                    'subtotal_mitra' => $subtotal, // Default disamakan dulu
                    'subtotal_harga' => $subtotal,
                ]);

                $this->recalculateTotal($submission);
            });

            return response()->json(['success' => true, 'message' => 'Bahan manual berhasil ditambahkan']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteDetail(Submission $submission, SubmissionDetails $detail)
    {
        try {
            // 1. Cek apakah status masih boleh diedit
            $this->ensureEditable($submission);

            // --- PERBAIKAN DI SINI (Gunakan casting (int) agar aman) ---
            if ((int) $detail->submission_id != (int) $submission->id) {
                return response()->json([
                    'success' => false,
                    // Saya tambahkan info ID di pesan error agar Anda bisa cek jika masih salah
                    'message' => 'ID Mismatch. Detail milik ID: ' . $detail->submission_id . ', Header ID: ' . $submission->id
                ], 403);
            }

            DB::transaction(function () use ($detail, $submission) {
                // 2. Hapus data detail
                $detail->delete();

                // 3. Hitung ulang total_harga di tabel submissions (Header)
                $this->recalculateTotal($submission);
            });

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus dan total diperbarui.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSubmissionData(Submission $submission)
    {
        $submission->load(['kitchen', 'menu', 'children.supplier', 'children.details.unit', 'details.bahan_baku', 'details.unit']);

        // Format History (Child Submissions)
        $history = $submission->children->map(function ($child) {
            return [
                'id' => $child->id,
                'kode' => $child->kode,
                'supplier_nama' => $child->supplier->nama ?? 'Umum',
                'status' => $child->status,
                'total' => $child->total_harga,
                'created_at' => $child->created_at ? \Carbon\Carbon::parse($child->created_at)->locale('id')->translatedFormat('d-m-Y') : '-',
                'item_count' => $child->details->count(),
                'items' => $child->details->map(function ($detail) {
                    return [
                        'nama' => $detail->bahan_baku->nama ?? '-',
                        'qty' => (float) $detail->qty_digunakan,
                        'unit' => $detail->unit->satuan ?? '-',
                        // Tampilkan Subtotal
                        // ini yang dipakai UI
                        'harga_dapur' => (float) $detail->harga_dapur,
                        'harga_mitra' => (float) $detail->harga_mitra,
                        'subtotal_dapur' => (float) $detail->subtotal_dapur,
                        'subtotal_mitra' => (float) $detail->subtotal_mitra,
                    ];
                })->values()
            ];
        });

        $availableSuppliers = $submission->kitchen->suppliers->values();

        return response()->json([
            'id' => $submission->id,
            'kode' => $submission->kode,
            'tanggal' => \Carbon\Carbon::parse($submission->tanggal)->locale('id')->translatedFormat('l, d-m-Y'),
            'tanggal_digunakan' => $submission->tanggal_digunakan ? \Carbon\Carbon::parse($submission->tanggal_digunakan)->locale('id')->translatedFormat('l, d-m-Y') : '-',
            'kitchen' => $submission->kitchen->nama,
            'menu' => $submission->menu->nama,

            // Perbaikan: Porsi Besar & Kecil
            'porsi_besar' => $submission->porsi_besar,
            'porsi_kecil' => $submission->porsi_kecil,

            'status' => $submission->status,
            'history' => $history,
            'suppliers' => $availableSuppliers,

            'details' => $submission->details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'nama_bahan' => $detail->bahan_baku->nama ?? 'Item Terhapus',
                    'qty_digunakan' => (float) $detail->qty_digunakan,
                    'satuan_id' => $detail->satuan_id,
                    // Ambil nama satuan dari relasi unit di detail
                    'nama_satuan' => $detail->unit->satuan ?? ($detail->bahan_baku->unit->satuan ?? '-'),
                    'harga_dapur' => (float) $detail->harga_dapur,
                    'harga_mitra' => (float) $detail->harga_mitra,
                    'subtotal_dapur' => (float) $detail->subtotal_dapur,
                    'subtotal_mitra' => (float) $detail->subtotal_mitra,
                    'recipe_bahan_baku_id' => null, // Placeholder jika tidak ada kolom ini di DB Anda, set null/abaikan logic manual label
                ];
            })->values()
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

            $childSequence = Submission::withTrashed()
                ->where('parent_id', $submission->id)->count() + 1;
            $childKode = $submission->kode . '-' . $childSequence;

            // 2. BUAT CHILD SUBMISSION
            $child = Submission::create([
                'kode' => $childKode,
                'tanggal' => now(),
                'kitchen_id' => $submission->kitchen_id, // Data diambil dari $submission
                'menu_id' => $submission->menu_id,       // Data diambil dari $submission
                'porsi_besar' => $submission->porsi_besar,
                'porsi_kecil' => $submission->porsi_kecil,           // Data diambil dari $submission
                'total_harga' => 0,
                'tipe' => 'disetujui',
                'status' => 'diproses',
                'parent_id' => $submission->id,
                'supplier_id' => $request->supplier_id,
            ]);

            $totalChild = 0;
            $detailsToCopy = SubmissionDetails::whereIn('id', $request->selected_details)->get();

            foreach ($detailsToCopy as $detail) {

                // Ambil Subtotal Mitra (jika ada input), jika tidak 0
                $subtotalMitraFix = $detail->subtotal_mitra > 0 ? $detail->subtotal_mitra : 0;
                $subtotalDapurFix = $detail->subtotal_dapur > 0 ? $detail->subtotal_dapur : 0;

                // Hitung Unit Price Mitra untuk kerapian DB
                $unitPriceMitra = $detail->qty_digunakan > 0 ? ($subtotalMitraFix / $detail->qty_digunakan) : 0;

                SubmissionDetails::create([
                    'submission_id' => $child->id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'satuan_id' => $detail->satuan_id, // Copy Satuan
                    'qty_digunakan' => $detail->qty_digunakan, // Copy Qty Raw

                    'harga_dapur' => $detail->harga_dapur,
                    'subtotal_dapur' => $detail->subtotal_dapur,

                    'harga_mitra' => $unitPriceMitra,
                    'subtotal_mitra' => $subtotalMitraFix,

                    'subtotal_harga' => $subtotalDapurFix,
                ]);

                $totalChild += $subtotalDapurFix;
            }

            // Update total harga child
            $child->update(['total_harga' => $totalChild]);
        });

        return response()->json(['success' => true, 'message' => 'Order berhasil dipisah ke supplier']);
    }
    // app/Http/Controllers/SubmissionApprovalController.php

    public function destroyChild(Submission $submission)
    {

        abort_if(!$submission->isChild(), 403, 'Hanya split order (child) yang bisa dihapus.');

        $parent = $submission->parentSubmission;
        if ($parent && $parent->status === 'selesai') {
            return response()->json(['success' => false, 'message' => 'Pengajuan Utama sudah SELESAI.'], 403);
        }

        if ($submission->status === 'selesai') {
            return response()->json(['success' => false, 'message' => 'PO sudah selesai, tidak bisa dihapus.'], 403);
        }

        DB::transaction(function () use ($submission) {
            $submission->details()->forceDelete();
            $submission->forceDelete();
        });

        return response()->json(['success' => true, 'message' => 'Split order berhasil dihapus.']);
    }

    public function getBahanBakuByKitchen($kitchenId)
    {
        if (!$kitchenId)
            return response()->json([], 400);

        $bahan = BahanBaku::where('kitchen_id', $kitchenId)
            ->whereNull('deleted_at')
            ->with('unit')
            ->orderBy('nama')
            ->get()
            ->values();

        return response()->json($bahan);
    }

    public function printInvoice(Submission $submission)
    {
        $submission->load(['kitchen', 'supplier', 'details.bahan_baku', 'details.unit']);

        foreach ($submission->details as $detail) {
            // Raw Data
            $detail->cetak_qty = (float) $detail->qty_digunakan;
            $detail->cetak_unit = $detail->unit->satuan ?? '-';
            // Cetak Subtotal (Total Harga)
            $detail->cetak_total_harga = $detail->subtotal_mitra > 0 ? $detail->subtotal_mitra : $detail->subtotal_dapur;
        }

        $pdf = Pdf::loadView('transaction.invoice-submission', compact('submission'))
            ->setPaper('a4', 'portrait');
        return $pdf->download($submission->kode . '.pdf');
    }


    public function printParentInvoice(Submission $submission)
    {
        abort_if(!$submission->isParent(), 404);

        $submission->load([
            'kitchen',
            'children.supplier',
            'children.details.bahan_baku.unit',
            'children.details.unit'
        ]);

        foreach ($submission->children as $child) {
            foreach ($child->details as $detail) {
                $detail->cetak_qty = (float) $detail->qty_digunakan;
                $detail->cetak_unit = $detail->unit->satuan ?? '-';
                $detail->cetak_total_harga = $detail->subtotal_mitra > 0 ? $detail->subtotal_mitra : $detail->subtotal_dapur;
            }
        }

        return view('transaction.invoice-submissionParent', compact('submission'));
    }

}
