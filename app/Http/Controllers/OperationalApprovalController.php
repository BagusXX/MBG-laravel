<?php

namespace App\Http\Controllers;

use App\Models\submissionOperational;
use App\Models\Supplier;
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
        //
        $request->validate([
            'parent_id' => 'required|exists:submission_operationals,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*' => 'exists:submission_operational_details,id',
        ]);

        $parent = submissionOperational::onlyParent()->findOrFail($request->parent_id);

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
        if (! $child->isChild()) {
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

        if (! $parent->children()->exists()) {
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

        return view(
            'transaction.invoiceOperational-parent',
            compact('parent')
        );
    }
}
