<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\Menu;
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

        // Ambil dapur hanya milik user yang sedang login
        $kitchens = $user->kitchens()->get();

        $submissions = Submission::with([
            'kitchen',
            'menu',
            'details.recipe.bahan_baku.unit',
            'details.bahanBaku.unit'
        ])
            // Filter data hanya dari dapur milik user
            ->whereIn('kitchen_id', $kitchens->pluck('id'))
            ->latest()
            ->paginate(10);

        // Generate Kode untuk tampilan form
        $lastKode = Submission::withTrashed()->orderByDesc('id')->value('kode');
        $nextKode = $lastKode
            ? 'PEM' . str_pad(((int) substr($lastKode, -3)) + 1, 3, '0', STR_PAD_LEFT)
            : 'PEM001';

        return view('transaction.submission', compact(
            'submissions',
            'kitchens',
            'nextKode'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Validasi akses dapur
        $allowedKitchenIds = Kitchen::whereIn(
            'kode',
            $user->kitchens()->pluck('kode')
        )->pluck('id');

        $request->validate([
            'tanggal' => 'required|date',
            'kitchen_id' => ['required', Rule::in($allowedKitchenIds)],
            'menu_id' => [
                'required',
                Rule::exists('menus', 'id')->where('kitchen_id', $request->kitchen_id),
            ],
            'porsi' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $lastKode = Submission::withTrashed()
                ->select('kode')
                ->orderByRaw('CAST(SUBSTRING(kode, 4) AS UNSIGNED) DESC')
                ->lockForUpdate()
                ->value('kode');

            $nextNumber = $lastKode ? ((int) substr($lastKode, -3)) + 1 : 1;
            $kode = 'PEM' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $menu = Menu::with('recipes.bahan_baku')
                ->where('id', $request->menu_id)
                ->where('kitchen_id', $request->kitchen_id)
                ->firstOrFail();

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

            $submission->update(['total_harga' => $totalHarga]);
        });

        return redirect()
            ->route('transaction.submission.index')
            ->with('success', 'Submission berhasil dibuat');
    }

    public function update(Request $request, Submission $submission)
    {
        if ($submission->status === 'selesai') {
            abort(403, 'Submission yang sudah selesai tidak dapat diubah');
        }

        $user = auth()->user();
        
        $allowedKitchenIds = Kitchen::whereIn(
            'kode',
            $user->kitchens()->pluck('kode')
        )->pluck('id');

        if (!$allowedKitchenIds->contains($submission->kitchen_id)) {
            abort(403, 'Anda tidak memiliki akses ke dapur ini');
        }

        if ($request->filled(['kitchen_id', 'menu_id', 'porsi'])) {
            $request->validate([
                'kitchen_id' => ['required', Rule::in($allowedKitchenIds->toArray())],
                'menu_id' => [
                    'required',
                    Rule::exists('menus', 'id')->where('kitchen_id', $request->kitchen_id),
                ],
                'porsi' => 'required|integer|min:1',
            ]);

            DB::transaction(function () use ($request, $submission) {
                $menu = Menu::with('recipes.bahan_baku')
                    ->where('id', $request->menu_id)
                    ->where('kitchen_id', $request->kitchen_id)
                    ->firstOrFail();

                $submission->update([
                    'kitchen_id' => $request->kitchen_id,
                    'menu_id' => $menu->id,
                    'porsi' => $request->porsi,
                ]);

                $submission->details()->delete();
                $totalHarga = 0;

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

                $submission->update(['total_harga' => $totalHarga]);
            });

            return back()->with('success', 'Data permintaan berhasil diperbarui');
        }
        
        return back();
    }

    public function destroy(Submission $submission)
    {
        $user = auth()->user();

        if (!$submission->kitchen) {
            abort(404, 'Dapur tidak ditemukan');
        }

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
        if (!auth()->user()->kitchens->pluck('kode')->contains($kitchen->kode)) {
            abort(403, 'Anda tidak memiliki akses ke dapur ini');
        }

        return response()->json(
            $kitchen->menus()->select('id', 'nama')->get()
        );
    }
    
    public function show(Submission $submission)
    {
        $userKitchenKode = auth()->user()->kitchens->pluck('kode');
        if (!$userKitchenKode->contains($submission->kitchen->kode)) {
            abort(403, 'Tidak memiliki akses ke data ini');
        }

        $submission->load(['kitchen', 'menu', 'details.recipe.bahan_baku.unit']);
        return view('transaction.submission_detail', compact('submission'));
    }
}