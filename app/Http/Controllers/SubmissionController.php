<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\RecipeBahanBaku;
use App\Models\Submission;
use App\Models\SubmissionDetails;
use App\Models\BahanBaku;
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
            'details.recipe.bahan_baku.unit',
            'details.bahanBaku.unit'
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
        
        // Tentukan mode dari request atau referer URL
        $referer = request()->header('referer', '');
        $isPermintaanMode = $request->input('_mode') === 'permintaan' ||
                           str_contains($referer, 'daftar-pemesanan') || 
                           str_contains($referer, 'request-materials');
        
        // Untuk mode permintaan, bisa edit semua dapur
        // Untuk mode pengajuan, hanya dapur milik user
        if ($isPermintaanMode) {
            // Mode permintaan: bisa edit ke semua dapur
            $allowedKitchenIds = Kitchen::all()->pluck('id');
        } else {
            // Mode pengajuan: hanya dapur milik user
            $allowedKitchenIds = Kitchen::whereIn(
                'kode',
                $user->kitchens()->pluck('kode')
            )->pluck('id');
            
            // ✅ CEK AKSES: Pastikan user memiliki akses ke submission yang akan diubah
            if (!$allowedKitchenIds->contains($submission->kitchen_id)) {
                abort(403, 'Anda tidak memiliki akses ke dapur ini');
            }
        }

        // Validasi: jika ada kitchen_id, menu_id, porsi berarti update data lengkap
        // Jika hanya ada status, berarti update status saja
        // Untuk mode permintaan, jangan update dapur/menu/porsi, hanya bahan baku
        if ($request->filled(['kitchen_id', 'menu_id', 'porsi']) && !$isPermintaanMode) {
            // Update data lengkap (dari modal detail) - hanya untuk mode pengajuan
            $kitchenValidation = ['required', Rule::in($allowedKitchenIds->toArray())];
            
            $request->validate([
                // 'tanggal' => 'required|date',
                'kitchen_id' => $kitchenValidation,
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
                    // 'tanggal' => $request->tanggal,
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
            'recipe.bahan_baku.unit',
            'bahanBaku.unit'
        ])->get();

        return response()->json($details->map(function ($detail) {
            $hargaDapur = $detail->harga_dapur ?? $detail->harga_satuan_saat_itu ?? 0;
            $hargaMitra = $detail->harga_mitra ?? $detail->harga_satuan_saat_itu ?? 0;
            
            // Ambil nama bahan baku dari recipe (jika ada) atau dari bahan_baku_id (untuk manual)
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

    public function getSubmissionData(Submission $submission)
    {
        // Load submission dengan relasi
        $submission->load(['kitchen', 'menu']);

        // Akses control sudah ditangani oleh middleware permission
        // Data dikembalikan sesuai dengan yang ada di database
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
            'details.*.bahan_baku_id' => 'nullable|exists:bahan_baku,id',
            'details.*.qty_digunakan' => 'required|numeric|min:0.0001',
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

                $bahanBakuId = $detailData['bahan_baku_id'] ?? null;
                $qtyDigunakan = $detailData['qty_digunakan'];
                $hargaDapur = $detailData['harga_dapur'];
                $hargaMitra = $detailData['harga_mitra'];
                $subtotalDapur = $hargaDapur * $qtyDigunakan;
                $subtotalMitra = $hargaMitra * $qtyDigunakan;

                // Update detail
                $updateData = [
                    'qty_digunakan' => $qtyDigunakan,
                    'harga_dapur' => $hargaDapur,
                    'harga_mitra' => $hargaMitra,
                ];

                // Jika bahan baku diubah, update bahan_baku_id dan reset recipe_bahan_baku_id
                if ($bahanBakuId !== null) {
                    // Validasi bahan baku milik dapur yang sama
                    $bahanBaku = BahanBaku::findOrFail($bahanBakuId);
                    if ($bahanBaku->kitchen_id !== $submission->kitchen_id) {
                        continue; // Skip jika bahan baku tidak sesuai dapur
                    }

                    $updateData['bahan_baku_id'] = $bahanBakuId;
                    $updateData['recipe_bahan_baku_id'] = null; // Reset recipe karena manual edit
                }
                // Jika bahan_baku_id null, tetap gunakan yang sudah ada (tidak diubah)

                $detail->update($updateData);

                // Gunakan harga dapur untuk total (atau bisa disesuaikan)
                $totalHarga += $subtotalDapur;
            }

            // Update total harga submission
            $submission->update([
                'total_harga' => $totalHarga
            ]);
        });

        // Refresh submission dengan data terbaru dari database
        $submission->refresh();
        $submission->load('details');

        // Return JSON untuk AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Detail berhasil diperbarui',
                'submission' => $submission
            ]);
        }

        return back()->with('success', 'Detail berhasil diperbarui');
    }

    public function deleteDetail(Request $request, Submission $submission, $detailId)
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

        // Ambil detail
        $detail = SubmissionDetails::findOrFail($detailId);

        // Pastikan detail milik submission ini
        if ($detail->submission_id !== $submission->id) {
            abort(403, 'Detail tidak sesuai dengan submission');
        }

        DB::transaction(function () use ($detail, $submission) {
            // Hapus detail
            $detail->delete();

            // Recalculate total harga
            $totalHarga = $submission->details()->sum(DB::raw('harga_dapur * qty_digunakan'));
            $submission->update(['total_harga' => $totalHarga]);
        });

        return response()->json(['success' => true, 'message' => 'Bahan baku berhasil dihapus']);
    }

    public function addBahanBakuManual(Request $request, Submission $submission)
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
            'bahan_baku_id' => 'required|exists:bahan_baku,id',
            'qty_digunakan' => 'required|numeric|min:0.0001',
        ]);

        // Pastikan bahan baku dari dapur yang sama
        $bahanBaku = BahanBaku::findOrFail($request->bahan_baku_id);
        if ($bahanBaku->kitchen_id !== $submission->kitchen_id) {
            abort(403, 'Bahan baku harus dari dapur yang sama');
        }

        // Cek apakah bahan baku sudah ada (dari recipe atau manual)
        // Untuk bahan baku dari recipe
        $existingDetailFromRecipe = $submission->details()
            ->whereHas('recipe', function ($q) use ($bahanBaku) {
                $q->where('bahan_baku_id', $bahanBaku->id);
            })
            ->first();

        // Untuk bahan baku manual, kita perlu cek melalui recipe yang null
        // Tapi karena tidak ada kolom bahan_baku_id langsung, kita skip pengecekan duplikasi manual
        // atau bisa ditambahkan kolom bahan_baku_id di migration jika diperlukan
        
        if ($existingDetailFromRecipe) {
            return response()->json([
                'success' => false,
                'message' => 'Bahan baku ini sudah ada dalam daftar (dari recipe)'
            ], 422);
        }

        DB::transaction(function () use ($request, $submission, $bahanBaku) {
            $harga = $bahanBaku->harga;
            $qty = $request->qty_digunakan;
            $subtotal = $harga * $qty;

            // Buat detail baru tanpa recipe (manual)
            SubmissionDetails::create([
                'submission_id' => $submission->id,
                'recipe_bahan_baku_id' => null, // null karena manual
                'bahan_baku_id' => $bahanBaku->id, // simpan bahan baku ID untuk manual
                'qty_digunakan' => $qty,
                'harga_satuan_saat_itu' => $harga,
                'harga_dapur' => $harga,
                'harga_mitra' => $harga,
                'subtotal_harga' => $subtotal,
            ]);

            // Update total harga
            $totalHarga = $submission->details()->sum(DB::raw('harga_dapur * qty_digunakan'));
            $submission->update(['total_harga' => $totalHarga]);
        });

        return response()->json(['success' => true, 'message' => 'Bahan baku berhasil ditambahkan']);
    }

    public function getBahanBakuByKitchen(Kitchen $kitchen)
    {
        // ✅ CEK AKSES
        $userKitchenIds = Kitchen::whereIn(
            'kode',
            auth()->user()->kitchens()->pluck('kode')
        )->pluck('id');

        if (!$userKitchenIds->contains($kitchen->id)) {
            abort(403, 'Anda tidak memiliki akses ke dapur ini');
        }

        $bahanBaku = BahanBaku::where('kitchen_id', $kitchen->id)
            ->with('unit')
            ->select('id', 'nama', 'harga', 'satuan_id', 'kitchen_id')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'harga' => $item->harga,
                    'satuan_id' => $item->satuan_id,
                    'satuan' => $item->unit ? $item->unit->satuan : null,
                ];
            });

        return response()->json($bahanBaku);
    }

}
