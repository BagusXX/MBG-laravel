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


        // 🔑 Generate KODE
        $lastSubmission = Submission::orderBy('kode', 'desc')->first();

        if (!$lastSubmission) {
            $kode = 'PEM001';
        } else {
            $lastNumber = (int) substr($lastSubmission->kode, -3);
            $kode = 'PEM' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        $submissions = Submission::with([
            'kitchen',
            'menu',
            'details.recipe.bahan_baku'
        ])->latest()->get();


        return view('transaction.submission', compact(
            'submissions',
            'kitchens',
            'menus',
            'kode'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|unique:submissions,kode',
            'tanggal' => 'required|date',
            'kitchen_id' => 'required|exists:kitchens,id',
            'menu_id' => 'required|exists:menus,id',
            'porsi' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {

            $lastSubmission = Submission::lockForUpdate()
                ->orderBy('kode', 'desc')
                ->first();

            if (!$lastSubmission) {
                $kode = 'PEM001';
            } else {
                $lastNumber = (int) substr($lastSubmission->kode, -3);
                $kode = 'PEM' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }


            /**
             * Ambil menu + resep + bahan baku
             */
            $menu = Menu::with('recipes.bahan_baku')
                ->where('id', $request->menu_id)
                ->where('kitchen_id', $request->kitchen_id)
                ->firstOrFail();

            /**
             * Buat SUBMISSION (HEADER)
             */
            $submission = Submission::create([
                'kode' => $kode,
                'tanggal' => $request->tanggal,
                'kitchen_id' => $request->kitchen_id,
                'menu_id' => $menu->id,
                'porsi' => $request->porsi,
                'total_harga' => 0, // diupdate setelah loop
                'status' => 'diajukan',
            ]);

            $totalHarga = 0;

            /**
             * Buat SUBMISSION DETAILS
             */
            foreach ($menu->recipes as $recipe) {

                $qtyDigunakan = $recipe->jumlah * $request->porsi;
                $hargaSatuan = $recipe->bahan_baku->harga;
                $subtotal = $qtyDigunakan * $hargaSatuan;

                SubmissionDetails::create([
                    'submission_id' => $submission->id,
                    'recipe_bahan_baku_id' => $recipe->id,
                    'qty_digunakan' => $qtyDigunakan,
                    'harga_satuan_saat_itu' => $hargaSatuan,
                    'subtotal_harga' => $subtotal,
                ]);

                $totalHarga += $subtotal;

                // OPTIONAL: potong stok bahan baku
                // $recipe->bahanBaku->decrement('stok', $qtyDigunakan);
            }

            /**
             * Update total harga
             */
            $submission->update([
                'total_harga' => $totalHarga
            ]);
        });

        return back()->with('success', 'Submission berhasil dibuat');

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
        // ❌ Lock submission jika sudah final
        if (in_array($submission->status, ['diproses', 'diterima'])) {
            abort(403, 'Submission sudah tidak dapat diubah');
        }

        $request->validate([
            'porsi' => 'required|integer|min:1',
            'status' => 'required|in:diajukan,diproses,diterima,ditolak',
        ]);

        DB::transaction(function () use ($request, $submission) {

            // 🔄 Update data utama submission
            $submission->update([
                'porsi' => $request->porsi,
                'status' => $request->status,
            ]);

            // 🔥 Hapus detail lama
            $submission->submission_detail()->delete();

            $total = 0;

            // Ambil recipe bahan baku berdasarkan menu
            $recipes = RecipeBahanBaku::where('menu_id', $submission->menu_id)
                ->with('bahan_baku')
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

                $total += $subtotal;
            }

            // 💰 Update total harga
            $submission->update([
                'total_harga' => $total
            ]);
        });

        return redirect()->back()->with('success', 'Submission berhasil diperbarui');


    }

    public function destroy(Submission $submission)
    {
        if (!in_array($submission->status, ['ditolak'])) {
            abort(403, 'Submission tidak bisa dihapus');
        }

        $submission->delete();

        return back()->with('success', 'Pengajuan menu berhasil dihapus.');
    }



    public function getMenuByKitchen(Kitchen $kitchen)
    {
        return response()->json(
            $kitchen->menus()->select('id', 'nama')->get()
        );
    }


}
