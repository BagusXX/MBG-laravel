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
    protected function formatQtyWithUnit($qty, $unit)
    {
        if (!$unit) {
            return [
                'qty' => $qty,
                'unit' => '-',
            ];
        }

        $satuan = strtolower($unit->satuan);

        // gram → kg
        if ($satuan === 'gram' && $qty >= 1000) {
            return [
                'qty' => $qty / 1000,
                'unit' => 'kg',
            ];
        }

        // ml → liter
        if ($satuan === 'ml' && $qty >= 1000) {
            return [
                'qty' => $qty / 1000,
                'unit' => 'liter',
            ];
        }

        // default (tidak dikonversi)
        return [
            'qty' => $qty,
            'unit' => $unit->satuan,
        ];
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
                $namaMenu = Menu::find($request->menu_id)->nama ?? 'Terpilih';
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


    // App\Http\Controllers\SubmissionController.php

    public function show(Submission $submission)
    {
        abort_if(!$submission->isParent(), 403);
        // ... validasi kitchen codes ...

        $submission->load([
            'kitchen',
            'menu',
            'details.bahanBaku.unit',
            'children.supplier',
            'children.details.bahanBaku.unit'
        ]);

        // Mapping Detail agar struktur sesuai Blade (nested bahan_baku)
        $mappedDetails = $submission->details->map(function ($detail) {

            // Gunakan Format Qty
            $formatted = $this->formatQtyWithUnit(
                $detail->qty_digunakan,
                $detail->bahanBaku?->unit
            );

            return [
                'id' => $detail->id,
                // Override data mentah dengan data terformat
                'qty_digunakan' => $formatted['qty'],
                'harga_dapur' => $detail->harga_dapur,
                'recipe_bahan_baku_id' => $detail->recipe_bahan_baku_id,

                // Struktur Bersarang (Nested)
                'bahan_baku' => [
                    'nama' => $detail->bahanBaku->nama ?? 'Item Terhapus',
                    'unit' => [
                        'satuan' => $formatted['unit'] // Override satuan (misal: gram jadi kg)
                    ]
                ]
            ];
        });

        return response()->json([
            'id' => $submission->id,
            'kode' => $submission->kode,
            'tanggal' => $submission->tanggal,
            'status' => $submission->status,
            'porsi' => $submission->porsi,
            'kitchen' => $submission->kitchen,
            'menu' => $submission->menu,
            'details' => $mappedDetails, // <-- Gunakan data yg sudah dimapping
            'history' => $submission->children->map(function ($child) {
                // ... logic history sama seperti di atas ...
                return [
                    'id' => $child->id,
                    'kode' => $child->kode,
                    'supplier_nama' => $child->supplier->nama ?? 'Umum',
                    'status' => $child->status,
                    'total' => $child->total_harga,
                    'items' => $child->details->map(function ($d) {
                    $fmt = $this->formatQtyWithUnit($d->qty_digunakan, $d->bahanBaku?->unit);
                    return [
                        'nama' => $d->bahanBaku->nama ?? '-',
                        'qty' => $fmt['qty'],
                        'unit' => $fmt['unit'],
                        'harga' => $d->harga_mitra ?? $d->harga_dapur,
                    ];
                })->values()
                ];
            }),
        ]);
    }
    /* ================= AJAX ================= */

    // Tambahkan/Update method ini di SubmissionApprovalController

    public function getSubmissionData(Submission $submission)
    {
        $submission->load(['kitchen', 'menu', 'children.supplier', 'children.details.bahanBaku']); // Load children & suppliernya

        // Format data children untuk riwayat
        $history = $submission->children->map(function ($child) {
            return [
                'id' => $child->id,
                'kode' => $child->kode,
                'supplier_nama' => $child->supplier->nama ?? 'Umum',
                'status' => $child->status,
                'total' => $child->total_harga,
                'item_count' => $child->details()->count(), // Opsional: jumlah item
                'items' => $child->details->map(function ($detail) {
                    return [
                        'nama' => $detail->bahanBaku->nama ?? '-',
                        'qty' => $detail->qty_digunakan,
                        'harga' => $detail->harga_mitra ?? $detail->harga_satuan,
                    ];
                })->values()
            ];
        });

        $availableSuppliers = $submission->kitchen->suppliers->values();

        return response()->json([
            'id' => $submission->id,
            'kode' => $submission->kode,
            'tanggal' => date('d-m-Y', strtotime($submission->tanggal)),
            'kitchen' => $submission->kitchen->nama,
            'menu' => $submission->menu->nama,
            'porsi' => $submission->porsi,
            'status' => $submission->status,
            'history' => $history,
            'suppliers' => $availableSuppliers // <--- Kirim data riwayat ke JS
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

            // 1. GENERATE KODE CHILD
            // Format: KODE_PARENT-1, KODE_PARENT-2, dst.
            $childSequence = Submission::where('parent_id', $submission->id)->count() + 1;
            $childKode = $submission->kode . '-' . $childSequence;

            // 2. BUAT CHILD SUBMISSION
            $child = Submission::create([
                'kode' => $childKode,
                'tanggal' => now(),
                'kitchen_id' => $submission->kitchen_id, // Data diambil dari $submission
                'menu_id' => $submission->menu_id,       // Data diambil dari $submission
                'porsi' => $submission->porsi,           // Data diambil dari $submission
                'total_harga' => 0,
                'tipe' => 'disetujui',
                'status' => 'diproses',
                'parent_id' => $submission->id,
                'supplier_id' => $request->supplier_id,
            ]);

            $total = 0;

            // 3. PINDAHKAN DETAIL YANG DICENTANG
            $detailsToCopy = SubmissionDetails::whereIn('id', $request->selected_details)->get();

            foreach ($detailsToCopy as $detail) {
                // Gunakan harga mitra jika ada, jika tidak pakai harga dapur
                $hargaMitra = $detail->harga_mitra ?? $detail->harga_dapur;
                $subtotal = $hargaMitra * $detail->qty_digunakan;

                SubmissionDetails::create([
                    'submission_id' => $child->id,
                    'recipe_bahan_baku_id' => $detail->recipe_bahan_baku_id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'qty_digunakan' => $detail->qty_digunakan,
                    'harga_satuan' => $detail->harga_satuan,
                    'harga_dapur' => $detail->harga_dapur, // Child ke supplier tidak butuh harga dapur
                    'harga_mitra' => $hargaMitra,
                    'subtotal_harga' => $subtotal,
                ]);

                $total += $subtotal;
            }

            // Update total harga child
            $child->update(['total_harga' => $total]);
        });

        return response()->json(['success' => true, 'message' => 'Order berhasil dipisah ke supplier']);
    }
}
