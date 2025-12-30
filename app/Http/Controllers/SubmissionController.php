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
        // ❌ kunci jika sudah diterima
        if ($submission->status === 'diterima') {
            abort(403, 'Submission yang sudah diterima tidak dapat diubah');
        }

        // ✅ CEK AKSES DI LUAR TRANSACTION
        $userKitchenIds = Kitchen::whereIn(
            'kode',
            auth()->user()->kitchens()->pluck('kode')
        )->pluck('id');

        if (!$userKitchenIds->contains($submission->kitchen_id)) {
            abort(403, 'Anda tidak memiliki akses ke dapur ini');
        }

        $request->validate([
            'status' => 'required|in:diajukan,diproses,diterima,ditolak',
        ]);

        DB::transaction(function () use ($request, $submission) {

            $submission->update([
                'status' => $request->status,
            ]);

            // hapus detail lama
            $submission->details()->delete();

            $totalHarga = 0;

            $recipes = RecipeBahanBaku::with('bahan_baku')
                ->where('menu_id', $submission->menu_id)
                ->get();

            foreach ($recipes as $recipe) {

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

            $submission->update([
                'total_harga' => $totalHarga
            ]);
        });

        return back()->with('success', 'Submission berhasil diperbarui');
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



}
