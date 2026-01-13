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
    public function index()
    {
        // Approval bisa melihat semua dapur
        $kitchens = Kitchen::all();

        $submissions = Submission::with([
            'kitchen',
            'menu',
            'supplier',
            'details.recipe.bahan_baku.unit',
            'details.bahanBaku.unit'
        ])
        ->latest()
        ->paginate(10);

        // Hanya visual saja jika diperlukan di view
        $lastKode = Submission::withTrashed()->orderByDesc('id')->value('kode');
        $nextKode = $lastKode ? $lastKode : 'PEM001';

        // Suppliers dibutuhkan untuk dropdown approval
        $suppliers = Supplier::orderBy('nama')->get();

        return view('transaction.submission', compact(
            'submissions',
            'kitchens',
            'nextKode',
            'suppliers'
        ));
    }

    public function update(Request $request, Submission $submission)
    {
        if ($submission->status === 'selesai') {
            abort(403, 'Submission yang sudah selesai tidak dapat diubah');
        }

        if ($request->has('status')) {
            $request->validate([
                'status' => 'required|in:diajukan,diproses,selesai,ditolak',
            ]);

            $submission->update(['status' => $request->status]);
            return back()->with('success', 'Status berhasil diperbarui');
        }
        
        return back();
    }

    public function updateToProcess(Submission $submission)
    {
        if ($submission->status === 'selesai') abort(403, 'Sudah selesai');
        
        // Opsional: Cek akses dapur jika approver juga dibatasi per dapur
        $userKitchenIds = Kitchen::whereIn('kode', auth()->user()->kitchens()->pluck('kode'))->pluck('id');
        // if (!$userKitchenIds->contains($submission->kitchen_id)) { abort(403, 'Akses ditolak'); }

        $submission->update(['status' => 'diproses']);
        return back()->with('success', 'Status berubah menjadi diproses');
    }

    public function updateToComplete(Request $request, Submission $submission)
    {
        if ($submission->status !== 'diproses') abort(403, 'Hanya status diproses yang bisa diselesaikan');

        // Validasi supplier wajib diisi saat approval selesai
        $request->validate(['supplier_id' => 'required|exists:suppliers,id']);

        $submission->update([
            'status' => 'selesai',
            'supplier_id' => $request->supplier_id
        ]);

        return back()->with('success', 'Status berubah menjadi selesai');
    }

    // --- DETAIL & AJAX OPERATIONS ---

    public function getSubmissionDetails(Submission $submission)
    {
        $details = $submission->details()->with([
            'recipe.bahan_baku.unit',
            'bahanBaku.unit'
        ])->get();

        return response()->json($details->map(function ($detail) {
            $hargaDapur = $detail->harga_dapur ?? $detail->harga_satuan_saat_itu ?? 0;
            $hargaMitra = $detail->harga_mitra ?? $detail->harga_satuan_saat_itu ?? 0;
            
            $bahanBakuNama = $detail->recipe?->bahan_baku?->nama ?? $detail->bahanBaku?->nama ?? '-';
            $bahanBakuId = $detail->recipe?->bahan_baku_id ?? $detail->bahan_baku_id ?? null;
            $satuan = $detail->recipe?->bahan_baku?->unit?->satuan ?? $detail->bahanBaku?->unit?->satuan ?? '-';
            
            return [
                'id' => $detail->id,
                'bahan_baku_id' => $bahanBakuId,
                'bahan_baku_nama' => $bahanBakuNama,
                'qty_digunakan' => $detail->qty_digunakan,
                'satuan' => $satuan,
                'harga_dapur' => $hargaDapur,
                'harga_mitra' => $hargaMitra,
                'subtotal_dapur' => $hargaDapur * $detail->qty_digunakan,
                'subtotal_mitra' => $hargaMitra * $detail->qty_digunakan,
            ];
        }));
    }

    public function updateHarga(Request $request, Submission $submission)
    {
        if ($submission->status === 'selesai') abort(403, 'Terkunci');

        $request->validate([
            'details' => 'required|array',
            'details.*.id' => 'required|exists:submission_details,id',
            'details.*.qty_digunakan' => 'required|numeric|min:0.0001',
            'details.*.harga_dapur' => 'required|numeric|min:0',
            'details.*.harga_mitra' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $submission) {
            $totalHarga = 0;
            foreach ($request->details as $detailData) {
                $detail = SubmissionDetails::findOrFail($detailData['id']);
                if ($detail->submission_id !== $submission->id) continue;

                $bahanBakuId = $detailData['bahan_baku_id'] ?? null;
                $updateData = [
                    'qty_digunakan' => $detailData['qty_digunakan'],
                    'harga_dapur' => $detailData['harga_dapur'],
                    'harga_mitra' => $detailData['harga_mitra'],
                ];

                if ($bahanBakuId !== null) {
                    $bahanBaku = BahanBaku::findOrFail($bahanBakuId);
                    if ($bahanBaku->kitchen_id === $submission->kitchen_id) {
                        $updateData['bahan_baku_id'] = $bahanBakuId;
                        $updateData['recipe_bahan_baku_id'] = null;
                    }
                }

                $detail->update($updateData);
                $totalHarga += ($updateData['harga_dapur'] * $updateData['qty_digunakan']);
            }
            $submission->update(['total_harga' => $totalHarga]);
        });

        if ($request->ajax()) {
            $submission->refresh();
            return response()->json(['success' => true, 'submission' => $submission]);
        }
        return back()->with('success', 'Detail diperbarui');
    }

    public function addBahanBakuManual(Request $request, Submission $submission)
    {
        if ($submission->status === 'selesai') abort(403, 'Terkunci');

        $request->validate([
            'bahan_baku_id' => 'required|exists:bahan_baku,id',
            'qty_digunakan' => 'required|numeric|min:0.0001',
        ]);

        $bahanBaku = BahanBaku::findOrFail($request->bahan_baku_id);
        if ($bahanBaku->kitchen_id !== $submission->kitchen_id) abort(403, 'Bahan baku salah dapur');

        $exists = $submission->details()->whereHas('recipe', fn($q) => $q->where('bahan_baku_id', $bahanBaku->id))->exists();
        if ($exists) return response()->json(['success' => false, 'message' => 'Sudah ada (dari resep)'], 422);

        DB::transaction(function () use ($request, $submission, $bahanBaku) {
            $harga = $bahanBaku->harga;
            $qty = $request->qty_digunakan;
            
            SubmissionDetails::create([
                'submission_id' => $submission->id,
                'recipe_bahan_baku_id' => null,
                'bahan_baku_id' => $bahanBaku->id,
                'qty_digunakan' => $qty,
                'harga_satuan_saat_itu' => $harga,
                'harga_dapur' => $harga,
                'harga_mitra' => $harga,
                'subtotal_harga' => $harga * $qty,
            ]);

            $total = $submission->details()->sum(DB::raw('harga_dapur * qty_digunakan'));
            $submission->update(['total_harga' => $total]);
        });

        return response()->json(['success' => true]);
    }

    public function deleteDetail(Request $request, Submission $submission, $detailId)
    {
        if ($submission->status === 'selesai') abort(403, 'Terkunci');
        
        $detail = SubmissionDetails::findOrFail($detailId);
        if ($detail->submission_id !== $submission->id) abort(403, 'Mismatch');

        DB::transaction(function () use ($detail, $submission) {
            $detail->delete();
            $total = $submission->details()->sum(DB::raw('harga_dapur * qty_digunakan'));
            $submission->update(['total_harga' => $total]);
        });

        return response()->json(['success' => true]);
    }

    public function getBahanBakuByKitchen(Kitchen $kitchen)
    {
        $bahanBaku = BahanBaku::where('kitchen_id', $kitchen->id)
            ->with('unit')
            ->select('id', 'nama', 'harga', 'satuan_id', 'kitchen_id')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'harga' => $item->harga,
                    'satuan' => $item->unit ? $item->unit->satuan : null,
                ];
            });

        return response()->json($bahanBaku);
    }
    
    public function getSubmissionData(Submission $submission)
    {
        $submission->load(['kitchen', 'menu']);
        return response()->json([
            'id' => $submission->id,
            'kode' => $submission->kode,
            'tanggal' => $submission->tanggal,
            'kitchen_id' => $submission->kitchen_id,
            'kitchen_nama' => $submission->kitchen->nama ?? '-',
            'menu_id' => $submission->menu_id,
            'menu_nama' => $submission->menu->nama ?? '-',
            'porsi' => $submission->porsi,
            'status' => $submission->status,
        ]);
    }
}