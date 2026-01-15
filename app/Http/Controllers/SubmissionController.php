<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\RecipeBahanBaku;
use App\Models\Submission;
use App\Models\SubmissionDetails;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SubmissionController extends Controller
{
    /* ================= HELPER ================= */

    protected function userKitchenCodes()
    {
        return auth()->user()->kitchens()->pluck('kode');
    }

    protected function generateKode(): string
    {
        $last = Submission::withTrashed()
            ->orderByRaw('CAST(SUBSTRING(kode, 4) AS UNSIGNED) DESC')
            ->lockForUpdate()
            ->value('kode');

        $next = $last ? ((int) substr($last, -3)) + 1 : 1;
        return 'PEM' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    // Hapus "Menu" dari type hint parameter kedua
    protected function syncDetails(Submission $submission, $recipes)
    {
        $submission->details()->delete();

        $total = 0;

        // $recipes di sini sudah berupa kumpulan baris dari tabel recipe_bahan_baku
        // Jadi kita langsung loop saja
        foreach ($recipes as $recipe) {

            $qty = $recipe->jumlah * $submission->porsi;

            // Pastikan relasi bahan_baku ter-load atau gunakan optional chaining
            $harga = $recipe->bahan_baku->harga ?? 0;
            $subtotal = $qty * $harga;

            $submission->details()->create([
                'recipe_bahan_baku_id' => $recipe->id,
                'bahan_baku_id' => $recipe->bahan_baku_id,
                'qty_digunakan' => $qty,
                'harga_satuan' => $harga,
                'harga_dapur' => $harga,
                'harga_mitra' => $harga,
                'subtotal_harga' => $subtotal,
            ]);

            $total += $subtotal;
        }

        $submission->update(['total_harga' => $total]);
    }

    /* ================= INDEX ================= */

    public function index()
    {
        $kitchenCodes = $this->userKitchenCodes();

        $submissions = Submission::with([
            'kitchen',
            'menu',
            'details.recipeBahanBaku.bahan_baku.unit',
            'details.bahanBaku.unit'
        ])
            ->onlyParent()
            ->pengajuan()
            ->whereHas('kitchen', fn($q) => $q->whereIn('kode', $kitchenCodes))
            ->latest()
            ->paginate(10);

        return view('transaction.submission', [
            'submissions' => $submissions,
            'kitchens' => auth()->user()->kitchens,
            'nextKode' => $this->generateKode(),
        ]);
    }

    /* ================= STORE ================= */

    public function store(Request $request)
    {
        $kitchenCodes = $this->userKitchenCodes();

        $request->validate([
            'tanggal' => 'required|date',
            'kitchen_id' => [
                'required',
                Rule::exists('kitchens', 'id')->where(
                    fn($q) => $q->whereIn('kode', $kitchenCodes)
                ),
            ],
            'menu_id' => [
                'required',
                Rule::exists('menus', 'id')->where('kitchen_id', $request->kitchen_id)
            ],
            'porsi' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {

            // --- UBAHAN UTAMA DI SINI ---
            // Kita cari daftar resep langsung dari model RecipeBahanBaku
            $recipes = RecipeBahanBaku::with('bahan_baku')
                ->where('menu_id', $request->menu_id)
                ->where('kitchen_id', $request->kitchen_id)
                ->get();

            // Validasi manual: Jika tidak ada resep ditemukan
            if ($recipes->isEmpty()) {
                // Opsional: Ambil nama menu untuk pesan error yg lebih bagus
                $namaMenu = \App\Models\Menu::find($request->menu_id)->nama ?? 'Terpilih';
                throw new \Exception("Menu '$namaMenu' tidak memiliki resep/bahan baku di dapur ini.");
            }

            $submission = Submission::create([
                'kode' => $this->generateKode(),
                'tanggal' => $request->tanggal,
                'kitchen_id' => $request->kitchen_id,
                'menu_id' => $request->menu_id, // Menu ID langsung dari request
                'porsi' => $request->porsi,
                'tipe' => 'pengajuan',
                'status' => 'diajukan',
            ]);

            // Kirim variable $recipes (Collection) ke fungsi sync
            $this->syncDetails($submission, $recipes);
        });

        return back()->with('success', 'Pengajuan berhasil dibuat');
    }

    /* ================= UPDATE ================= */

    public function update(Request $request, Submission $submission)
    {
        abort_if(!$submission->isParent(), 403);
        abort_if($submission->status !== 'diajukan', 403);

        $kitchenCodes = $this->userKitchenCodes();
        abort_if(!in_array($submission->kitchen->kode, $kitchenCodes->toArray()), 403);

        $request->validate([
            'menu_id' => [
                'required',
                Rule::exists('menus', 'id')->where('kitchen_id', $request->kitchen_id)
            ],
            'porsi' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $submission) {

            // --- UBAHAN UTAMA DI SINI ---
            // Ambil resep langsung dari RecipeBahanBaku
            $recipes = RecipeBahanBaku::with('bahan_baku')
                ->where('menu_id', $request->menu_id)
                ->where('kitchen_id', $submission->kitchen_id)
                ->get();

            if ($recipes->isEmpty()) {
                $namaMenu = \App\Models\Menu::find($request->menu_id)->nama ?? 'Terpilih';
                throw new \Exception("Menu '$namaMenu' tidak memiliki resep/bahan baku.");
            }

            $submission->update([
                'menu_id' => $request->menu_id,
                'porsi' => $request->porsi,
            ]);

            $this->syncDetails($submission, $recipes);
        });

        return back()->with('success', 'Pengajuan berhasil diperbarui');
    }

    /* ================= DESTROY ================= */

    public function destroy(Submission $submission)
    {
        abort_if(!$submission->isParent(), 403);
        abort_if(!in_array($submission->status, ['diajukan', 'ditolak']), 403);

        $kitchenCodes = $this->userKitchenCodes();
        abort_if(!in_array($submission->kitchen->kode, $kitchenCodes->toArray()), 403);

        $submission->delete();

        return back()->with('success', 'Pengajuan berhasil dihapus');
    }
    // Tambahkan di dalam SubmissionController

    public function getMenuByKitchen($kitchenId)
    {
        $kitchenCodes = $this->userKitchenCodes();

        // Query dimulai dari RecipeBahanBaku
        $menus = RecipeBahanBaku::query()
            ->where('kitchen_id', $kitchenId)
            // Filter keamanan: pastikan dapurnya milik user
            ->whereHas('kitchen', fn($q) => $q->whereIn('kode', $kitchenCodes))
            // Filter menu: pastikan menunya aktif (tidak soft delete)
            ->whereHas('menu', fn($q) => $q->whereNull('deleted_at'))
            // Ambil Menu ID unik saja
            ->select('menu_id')
            ->distinct()
            ->with('menu:id,nama') // Load nama menunya
            ->get()
            ->map(function ($item) {
                // Format ulang output agar bersih (id & nama saja)
                return [
                    'id' => $item->menu_id,
                    'nama' => $item->menu->nama ?? 'Unknown Menu'
                ];
            });

        return response()->json($menus);
    }


    public function show(Submission $submission)
    {
        abort_if(!$submission->isParent(), 403);

        $kitchenCodes = $this->userKitchenCodes();
        abort_if(!in_array($submission->kitchen->kode, $kitchenCodes->toArray()), 403);

        $submission->load([
            'kitchen',
            'menu',
            // 'details.recipeBahanBaku.bahan_baku.unit',
            'details.bahanBaku.unit'
        ]);

        // return view('transaction.submission-detail', compact('submission'));
        return response()->json($submission);
    }
}
