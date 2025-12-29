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
        $kitchens = Kitchen::all();
        $menus = collect();

        // Ambil kode terakhir (hanya untuk tampilan)
        $lastKode = Submission::withTrashed()
            ->orderByDesc('id')
            ->value('kode');

        $nextKode = $lastKode
            ? 'PEM' . str_pad(((int) substr($lastKode, -3)) + 1, 3, '0', STR_PAD_LEFT)
            : 'PEM001';

        $submissions = Submission::with([
            'kitchen',
            'menu',
            'details.recipe.bahan_baku'
        ])->latest()->paginate(10);

        /**
         * 🔑 DETEKSI MODE DARI ROUTE NAME
         */
        $mode = request()->routeIs('transaction.submission.index')
            ? 'pengajuan'
            : 'permintaan';

        return view('transaction.submission', compact(
            'submissions',
            'kitchens',
            'menus',
            'nextKode',
            'mode' // ✅ WAJIB DIKIRIM
        ));
    }


    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kitchen_id' => 'required|exists:kitchens,id',
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
        $submission->load([
            'kitchen',
            'menu',
            'details.recipe.bahan_baku'
        ]);

        return view('transaction.submission_detail', compact('submission'));
    }

    public function update(Request $request, Submission $submission)
    {
        // ❌ Kunci HANYA jika sudah diterima
        if ($submission->status === 'diterima') {
            abort(403, 'Submission yang sudah diterima tidak dapat diubah');
        }

        $request->validate([
            'porsi' => 'required|integer|min:1',
            'status' => 'required|in:diajukan,diproses,diterima,ditolak',
        ]);

        DB::transaction(function () use ($request, $submission) {

            /**
             * 🔄 Update header submission
             */
            $submission->update([
                'porsi' => $request->porsi,
                'status' => $request->status,
            ]);

            /**
             * 🔥 Hapus detail lama
             */
            $submission->details()->delete();

            $totalHarga = 0;

            /**
             * 📦 Ambil recipe bahan baku
             */
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

            /**
             * 💰 Update total harga
             */
            $submission->update([
                'total_harga' => $totalHarga
            ]);
        });

        return back()->with('success', 'Submission berhasil diperbarui');
    }


    public function destroy(Submission $submission)
    {
        if ($submission->status === 'diproses') {
            abort(403, 'Submission yang sedang diproses tidak dapat dihapus');
        }

        $submission->delete();

        return back()->with('success', 'Submission berhasil dihapus');

    }



    public function getMenuByKitchen(Kitchen $kitchen)
    {
        return response()->json(
            $kitchen->menus()->select('id', 'nama')->get()
        );
    }



}
