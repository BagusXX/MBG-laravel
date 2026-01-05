<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\RecipeBahanBaku;
use App\Models\Submission;
use App\Models\SubmissionDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SubmissionController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        /**
         * 🔑 MODE DARI ROUTE
         */
        $mode = request()->routeIs('transaction.submission.index')
            ? 'pengajuan'
            : 'permintaan';

        /**
         * 🏠 DAPUR
         * - pengajuan  → hanya dapur user
         * - permintaan → semua dapur
         */
        $kitchens = $mode === 'pengajuan'
            ? $user->kitchens()->get()
            : Kitchen::all();

        /**
         * 📋 SUBMISSION
         * - pengajuan  → hanya dapur user
         * - permintaan → semua
         */
        $submissions = Submission::with([
            'kitchen',
            'menu',
            'details.recipe.bahan_baku'
        ])
            ->when($mode === 'pengajuan', function ($q) use ($kitchens) {
                $q->whereIn(
                    'kitchen_id',
                    Kitchen::whereIn('kode', $kitchens->pluck('kode'))->pluck('id')
                );
            })
            ->latest()
            ->paginate(10);

        /**
         * 🔢 KODE BARU (TAMPILAN)
         */
        $lastKode = Submission::withTrashed()->orderByDesc('id')->value('kode');
        $nextKode = $lastKode
            ? 'PEM' . str_pad(((int) substr($lastKode, -3)) + 1, 3, '0', STR_PAD_LEFT)
            : 'PEM001';

        return view('transaction.submission', compact(
            'submissions',
            'kitchens',
            'nextKode',
            'mode'
        ));
    }


    public function store(Request $request)
    {
        $user = auth()->user();

        $allowedKitchenIds = Kitchen::whereIn(
            'kode',
            $user->kitchens()->pluck('kode')
        )->pluck('id');



        $request->validate([
            'tanggal' => 'required|date',
            'kitchen_id' => [
                'required',
                Rule::in($allowedKitchenIds),
            ],
            'menu_id' => [
                'required',
                Rule::exists('menus', 'id')->where('kitchen_id', $request->kitchen_id),
            ],
            'porsi' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {

            /**
             * 🔒 Generate kode submission (AMAN DARI DUPLIKASI)
             */
            $lastKode = Submission::withTrashed()
                ->select('kode')
                ->orderByRaw('CAST(SUBSTRING(kode, 4) AS UNSIGNED) DESC')
                ->lockForUpdate()
                ->value('kode');

            $nextNumber = $lastKode ? ((int) substr($lastKode, -3)) + 1 : 1;
            $kode = 'PEM' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            /**
             * 📦 Ambil menu + resep + bahan baku
             */
            $menu = Menu::with('recipes.bahan_baku')
                ->where('id', $request->menu_id)
                ->where('kitchen_id', $request->kitchen_id)
                ->firstOrFail();

            /**
             * 🧾 Buat submission (header)
             */
            $submission = Submission::create([
                'kode' => $kode,
                'tanggal' => $request->tanggal,
                'kitchen_id' => $request->kitchen_id,
                'menu_id' => $menu->id,
                'porsi' => $request->porsi,
                'total_harga' => 0,
                'status' => 'diajukan',
            ]);

            $totalHarga = 0;

            /**
             * 📑 Buat submission detail
             */
            foreach ($menu->recipes as $recipe) {

                $qty = $recipe->jumlah * $request->porsi;
                $harga = $recipe->bahan_baku->harga;
                $subtotal = $qty * $harga;

                SubmissionDetails::create([
                    'submission_id' => $submission->id,
                    'recipe_bahan_baku_id' => $recipe->id,
                    'qty_digunakan' => $qty,
                    'harga_satuan_saat_itu' => $harga,
                    'harga_dapur' => $harga,
                    'harga_mitra' => $harga,
                    'subtotal_harga' => $subtotal,
                ]);

                $totalHarga += $subtotal;
            }

            /**
             * 💰 Update total harga
             */
            $submission->update([
                'total_harga' => $totalHarga
            ]);
        });

        return redirect()
            ->route('transaction.submission.index')
            ->with('success', 'Submission berhasil dibuat');
    }


    public function show(Submission $submission)
    {
        // 🔐 keamanan: hanya boleh lihat dapur miliknya saat pengajuan
        if (request()->routeIs('transaction.submission.*')) {
            $userKitchenKode = auth()->user()->kitchens->pluck('kode');

            if (!$userKitchenKode->contains($submission->kitchen->kode)) {
                abort(403, 'Tidak memiliki akses ke data ini');
            }
        }

        $submission->load([
            'kitchen',
            'menu',
            'details.recipe.bahan_baku.unit'
        ]);

        return view('transaction.submission_detail', compact('submission'));
    }

    public function update(Request $request, Submission $submission)
    {
        // ❌ kunci jika sudah selesai
        if ($submission->status === 'selesai') {
            abort(403, 'Submission yang sudah selesai tidak dapat diubah');
        }

        $user = auth()->user();
        $allowedKitchenIds = Kitchen::whereIn(
            'kode',
            $user->kitchens()->pluck('kode')
        )->pluck('id');

        // ✅ CEK AKSES
        if (!$allowedKitchenIds->contains($submission->kitchen_id)) {
            abort(403, 'Anda tidak memiliki akses ke dapur ini');
        }

        // Validasi: jika ada kitchen_id, menu_id, porsi berarti update data lengkap
        // Jika hanya ada status, berarti update status saja
        if ($request->has('kitchen_id') && $request->has('menu_id') && $request->has('porsi')) {
            // Update data lengkap (dari modal detail)
            $request->validate([
                'tanggal' => 'required|date',
                'kitchen_id' => [
                    'required',
                    Rule::in($allowedKitchenIds),
                ],
                'menu_id' => [
                    'required',
                    Rule::exists('menus', 'id')->where('kitchen_id', $request->kitchen_id),
                ],
                'porsi' => 'required|integer|min:1',
            ]);

            DB::transaction(function () use ($request, $submission) {
                // Ambil menu baru
                $menu = Menu::with('recipes.bahan_baku')
                    ->where('id', $request->menu_id)
                    ->where('kitchen_id', $request->kitchen_id)
                    ->firstOrFail();

                // Update submission header
                $submission->update([
                    'tanggal' => $request->tanggal,
                    'kitchen_id' => $request->kitchen_id,
                    'menu_id' => $menu->id,
                    'porsi' => $request->porsi,
                ]);

                // Hapus detail lama
                $submission->details()->delete();

                $totalHarga = 0;

                // Buat detail baru
                foreach ($menu->recipes as $recipe) {
                    $qty = $recipe->jumlah * $request->porsi;
                    $harga = $recipe->bahan_baku->harga;
                    $subtotal = $qty * $harga;

                    SubmissionDetails::create([
                        'submission_id' => $submission->id,
                        'recipe_bahan_baku_id' => $recipe->id,
                        'qty_digunakan' => $qty,
                        'harga_satuan_saat_itu' => $harga,
                        'subtotal_harga' => $subtotal,
                    ]);

                    $totalHarga += $subtotal;
                }

                // Update total harga
                $submission->update([
                    'total_harga' => $totalHarga
                ]);
            });

            return back()->with('success', 'Data permintaan berhasil diperbarui');
        } else {
            // Update status saja (untuk kompatibilitas dengan kode lama)
            $request->validate([
                'status' => 'required|in:diajukan,diproses,selesai,ditolak',
            ]);

            $submission->update([
                'status' => $request->status,
            ]);

            return back()->with('success', 'Status berhasil diperbarui');
        }
    }



    public function destroy(Submission $submission)
{
    $user = auth()->user();

    // ⛔ cegah relasi null
    if (!$submission->kitchen) {
        abort(404, 'Dapur tidak ditemukan');
    }

    // ✅ cek akses dapur user
    if (!$user->kitchens()->where('kitchens.id', $submission->kitchen_id)->exists()) {
        abort(403, 'Anda tidak memiliki akses ke dapur ini');
    }

    if ($submission->status === 'diproses') {
        abort(403, 'Submission yang sedang diproses tidak dapat dihapus');
    }

    $submission->delete();

    return back()->with('success', 'Submission berhasil dihapus');
}





    public function updateToProcess(Submission $submission)
    {
        // ❌ kunci jika sudah selesai
        if ($submission->status === 'selesai') {
            abort(403, 'Submission yang sudah selesai tidak dapat diubah');
        }

        // ✅ CEK AKSES
        $userKitchenIds = Kitchen::whereIn(
            'kode',
            auth()->user()->kitchens()->pluck('kode')
        )->pluck('id');

        if (!$userKitchenIds->contains($submission->kitchen_id)) {
            abort(403, 'Anda tidak memiliki akses ke dapur ini');
        }

        // Update status ke diproses
        $submission->update([
            'status' => 'diproses',
        ]);

        return back()->with('success', 'Status berhasil diubah menjadi diproses');
    }

    public function updateToComplete(Submission $submission)
    {
        // ❌ hanya bisa jika status = diproses
        if ($submission->status !== 'diproses') {
            abort(403, 'Hanya submission yang sedang diproses yang dapat diselesaikan');
        }

        // ✅ CEK AKSES
        $userKitchenIds = Kitchen::whereIn(
            'kode',
            auth()->user()->kitchens()->pluck('kode')
        )->pluck('id');

        if (!$userKitchenIds->contains($submission->kitchen_id)) {
            abort(403, 'Anda tidak memiliki akses ke dapur ini');
        }

        // Update status ke selesai
        $submission->update([
            'status' => 'selesai',
        ]);

        return back()->with('success', 'Status berhasil diubah menjadi selesai');
    }

    public function getMenuByKitchen(Kitchen $kitchen)
    {
        if (request()->routeIs('transaction.submission.index')) {
            if (!auth()->user()->kitchens->pluck('kode')->contains($kitchen->kode)) {
                abort(403, 'Anda tidak memiliki akses ke dapur ini');
            }
        }

        return response()->json(
            $kitchen->menus()->select('id', 'nama')->get()
        );
    }

    public function getSubmissionDetails(Submission $submission)
    {
        // ✅ CEK AKSES
        $userKitchenIds = Kitchen::whereIn(
            'kode',
            auth()->user()->kitchens()->pluck('kode')
        )->pluck('id');

        if (!$userKitchenIds->contains($submission->kitchen_id)) {
            abort(403, 'Anda tidak memiliki akses ke data ini');
        }

        $details = $submission->details()->with([
            'recipe.bahan_baku.unit'
        ])->get();

        return response()->json($details->map(function ($detail) {
            $hargaDapur = $detail->harga_dapur ?? $detail->harga_satuan_saat_itu ?? 0;
            $hargaMitra = $detail->harga_mitra ?? $detail->harga_satuan_saat_itu ?? 0;
            
            return [
                'id' => $detail->id,
                'bahan_baku_nama' => $detail->recipe?->bahan_baku?->nama ?? '-',
                'qty_digunakan' => $detail->qty_digunakan,
                'satuan' => $detail->recipe?->bahan_baku?->unit?->satuan ?? '-',
                'harga_dapur' => $hargaDapur,
                'harga_mitra' => $hargaMitra,
                'subtotal_dapur' => $hargaDapur * $detail->qty_digunakan,
                'subtotal_mitra' => $hargaMitra * $detail->qty_digunakan,
            ];
        }));
    }

    public function updateHarga(Request $request, Submission $submission)
    {
        // ❌ kunci jika sudah selesai
        if ($submission->status === 'selesai') {
            abort(403, 'Submission yang sudah selesai tidak dapat diubah');
        }

        // ✅ CEK AKSES
        $userKitchenIds = Kitchen::whereIn(
            'kode',
            auth()->user()->kitchens()->pluck('kode')
        )->pluck('id');

        if (!$userKitchenIds->contains($submission->kitchen_id)) {
            abort(403, 'Anda tidak memiliki akses ke data ini');
        }

        $request->validate([
            'details' => 'required|array',
            'details.*.id' => 'required|exists:submission_details,id',
            'details.*.harga_dapur' => 'required|numeric|min:0',
            'details.*.harga_mitra' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $submission) {
            $totalHarga = 0;

            foreach ($request->details as $detailData) {
                $detail = SubmissionDetails::findOrFail($detailData['id']);
                
                // Pastikan detail milik submission ini
                if ($detail->submission_id !== $submission->id) {
                    continue;
                }

                $hargaDapur = $detailData['harga_dapur'];
                $hargaMitra = $detailData['harga_mitra'];
                $subtotalDapur = $hargaDapur * $detail->qty_digunakan;
                $subtotalMitra = $hargaMitra * $detail->qty_digunakan;

                $detail->update([
                    'harga_dapur' => $hargaDapur,
                    'harga_mitra' => $hargaMitra,
                ]);

                // Gunakan harga dapur untuk total (atau bisa disesuaikan)
                $totalHarga += $subtotalDapur;
            }

            // Update total harga submission
            $submission->update([
                'total_harga' => $totalHarga
            ]);
        });

        return back()->with('success', 'Harga berhasil diperbarui');
    }



}
