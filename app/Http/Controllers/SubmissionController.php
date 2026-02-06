<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\RecipeBahanBaku;
use App\Models\Submission;
use App\Models\BahanBaku;
use App\Models\SubmissionDetails;
use App\Models\Unit;
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

    public function handleMenuInput($kitchenId, $menuName, $menuId = null)
    {
        if ($menuId) {
            return $menuId;
        }

        // Cek dulu apakah menu dengan nama tsb sudah ada di dapur ini
        $existingMenu = Menu::where('kitchen_id', $kitchenId)
            ->where('nama', $menuName)
            ->first();

        if ($existingMenu) {
            return $existingMenu->id;
        }

        // Jika belum, buat baru + generate kode
        $kitchen = Kitchen::find($kitchenId);
        $kodeDapur = $kitchen ? $kitchen->kode : 'XXX';

        // Panggil fungsi static di Model Menu (sesuai kode model Anda sebelumnya)
        $kodeMenu = Menu::generateUniqueKode($kodeDapur);

        $menu = Menu::create([
            'kitchen_id' => $kitchenId,
            'nama' => $menuName,
            'kode' => $kodeMenu
        ]);

        return $menu->id;
    }

    protected function saveManualDetails(Submission $submission, array $items)
    {
        // Hapus detail lama (untuk case update)
        $submission->details()->delete();

        foreach ($items as $item) {
            $qty = (float) $item['qty'];

            // 1. Ambil Subtotal dari Input
            $inputSubtotalDapur = isset($item['harga_dapur']) ? (float) $item['harga_dapur'] : 0;
            $inputSubtotalMitra = isset($item['harga_mitra']) ? (float) $item['harga_mitra'] : 0;

            // 2. Hitung Harga Satuan (Untuk database agar rapi)
            // Rumus: Harga Satuan = Subtotal / Qty
            $hargaSatuanDapur = ($qty > 0) ? ($inputSubtotalDapur / $qty) : 0;
            $hargaSatuanMitra = ($qty > 0) ? ($inputSubtotalMitra / $qty) : 0;

            SubmissionDetails::create([
                'submission_id' => $submission->id,
                'bahan_baku_id' => $item['bahan_baku_id'],
                'satuan_id' => $item['satuan_id'],
                'qty_digunakan' => $qty,

                // Simpan Harga Satuan (Hasil Hitungan)
                'harga_dapur' => $hargaSatuanDapur,
                // Simpan Subtotal (Inputan User)
                'subtotal_dapur' => $inputSubtotalDapur,

                // Simpan Harga Satuan Mitra (Hasil Hitungan)
                'harga_mitra' => $hargaSatuanMitra,
                // Simpan Subtotal Mitra (Inputan User)
                'subtotal_mitra' => $inputSubtotalMitra,

                // Total global baris ini (default pakai harga dapur)
                'subtotal_harga' => $inputSubtotalDapur,
            ]);
        }

        // Update Total Harga di Parent Submission
        $grandTotal = $submission->details()->sum('subtotal_dapur');
        $submission->update(['total_harga' => $grandTotal]);
    }


    /* ================= INDEX ================= */

    public function index()
    {
        $kitchenCodes = $this->userKitchenCodes();

        $submissions = Submission::with([
            'kitchen',
            'menu',
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
            'bahanBakus' => BahanBaku::select('id', 'nama')->orderBy('nama')->get(),
            'units' => Unit::all(),
        ]);
    }

    /* ================= STORE ================= */

    public function store(Request $request)
    {
        $kitchenCodes = $this->userKitchenCodes();

        $request->validate([
            'tanggal' => 'required|date',
            'tanggal_digunakan' => 'required|date',
            'kitchen_id' => [
                'required',
                Rule::exists('kitchens', 'id')->where(
                    fn($q) => $q->whereIn('kode', $kitchenCodes)
                ),
            ],
            'nama_menu' => 'required_without:menu_id|string|nullable',
            'menu_id' => 'required_without:nama_menu|nullable',

            'porsi_besar' => 'nullable|integer|min:0',
            'porsi_kecil' => 'nullable|integer|min:0',

            // Validasi Array Items
            'items' => 'required|array|min:1',
            'items.*.bahan_baku_id' => 'required|exists:bahan_baku,id',
            'items.*.qty' => 'required|numeric|min:0',
            'items.*.satuan_id' => 'required|exists:units,id',
            'items.*.harga_dapur' => 'nullable|numeric|min:0',
            'items.*.harga_mitra' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $menuId = $this->handleMenuInput(
                $request->kitchen_id,
                $request->nama_menu,
                $request->menu_id
            );

            $submission = Submission::create([
                'kode' => $this->generateKode(),
                'tanggal' => $request->tanggal,
                'tanggal_digunakan' => $request->tanggal_digunakan,
                'kitchen_id' => $request->kitchen_id,
                'menu_id' => $menuId, // Menu ID langsung dari request
                'porsi_besar' => $request->porsi_besar ?? 0,
                'porsi_kecil' => $request->porsi_kecil ?? 0,
                'tipe' => 'pengajuan',
                'status' => 'diajukan',
            ]);

            // Kirim variable $recipes (Collection) ke fungsi sync
            $this->saveManualDetails($submission, $request->items);
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
            'tanggal_digunakan' => 'required|date',
            // Logic validasi menu sama dengan store
            'nama_menu' => 'required_without:menu_id|string|nullable',
            'menu_id' => 'required_without:nama_menu|nullable',

            'porsi_besar' => 'nullable|integer|min:0',
            'porsi_kecil' => 'nullable|integer|min:0',

            'items' => 'required|array|min:1',
            'items.*.bahan_baku_id' => 'required|exists:bahan_baku,id',
            'items.*.qty' => 'required|numeric|min:0',
            'items.*.satuan_id' => 'required|exists:units,id',
        ]);

        DB::transaction(function () use ($request, $submission) {
            $menuId = $this->handleMenuInput(
                $submission->kitchen_id,
                $request->nama_menu,
                $request->menu_id
            );

            // 2. Update Header
            $submission->update([
                'tanggal_digunakan' => $request->tanggal_digunakan,
                'menu_id' => $menuId,
                'porsi_besar' => $request->porsi_besar ?? 0,
                'porsi_kecil' => $request->porsi_kecil ?? 0,
            ]);

            // 3. Re-create Details (Hapus lama, buat baru dari input form)
            $this->saveManualDetails($submission, $request->items);
        });

        return back()->with('success', 'Pengajuan berhasil diperbarui');
    }


    public function show(Submission $submission)
    {
        abort_if(!$submission->isParent(), 403);

        $submission->load([
            'kitchen',
            'menu',
            'details.bahan_baku',
            'details.unit',
            'children.unit',
            'children.supplier',
        ]);

        return response()->json([
            'id' => $submission->id,
            'kode' => $submission->kode,
            'tanggal_digunakan' => $submission->tanggal_digunakan,
            'menu_id' => $submission->menu_id,
            'nama_menu' => $submission->menu->nama ?? '-',
            'porsi_besar' => $submission->porsi_besar,
            'porsi_kecil' => $submission->porsi_kecil,
            'keterangan' => $submission->keterangan,
            'kitchen' => $submission->kitchen,

            // Mapping Items agar mudah dibaca Frontend
            'details' => $submission->details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'nama_bahan' => $detail->bahan_baku->nama ?? 'Item Terhapus',
                    'qty' => (float) $detail->qty_digunakan,
                    'satuan_id' => $detail->satuan_id,
                    'nama_satuan' => $detail->unit->satuan ?? '-',
                    'harga_dapur' => (float) $detail->harga_dapur,
                    'harga_mitra' => (float) $detail->harga_mitra,
                    'subtotal' => (float) $detail->subtotal_dapur,
                ];
            }),

            'history' => $submission->children->map(function ($child) {
                return [
                    'kode' => $child->kode,
                    'supplier' => $child->supplier->nama ?? 'Umum',
                    'status' => $child->status,
                    'total' => $child->total_harga
                ];
            })
        ]);
    }

    public function destroy(Submission $submission)
    {
        abort_if(!$submission->isParent(), 403);
        abort_if(!in_array($submission->status, ['diajukan', 'ditolak']), 403);

        $kitchenCodes = $this->userKitchenCodes();
        abort_if(!in_array($submission->kitchen->kode, $kitchenCodes->toArray()), 403);

        $submission->delete();

        return back()->with('success', 'Pengajuan berhasil dihapus');
    }

    public function splitToSupplier(Request $request, Submission $submission)
    {
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
            $childSequence = Submission::where('parent_id', $submission->id)->count() + 1;
            $childKode = $submission->kode . '-' . $childSequence;

            // 2. BUAT CHILD SUBMISSION
            $child = Submission::create([
                'kode' => $childKode,
                'tanggal' => now(),
                'kitchen_id' => $submission->kitchen_id, // Data diambil dari $submission
                'menu_id' => $submission->menu_id,       // Data diambil dari $submission
                'porsi_besar' => $submission->porsi_besar,
                'porsi_kecil' => $submission->porsi_kecil,      // Data diambil dari $submission
                'total_harga' => 0,
                'tipe' => 'disetujui',
                'status' => 'diproses',
                'parent_id' => $submission->id,
                'supplier_id' => $request->supplier_id,
            ]);

            $totalMitra = 0;

            // 3. PINDAHKAN DETAIL YANG DICENTANG
            $detailsToCopy = SubmissionDetails::whereIn('id', $request->selected_details)->get();

            foreach ($detailsToCopy as $detail) {
                // Gunakan harga mitra jika ada, jika tidak pakai harga dapur
                $hargaSatuanFix = $detail->harga_dapur > 0 ? $detail->harga_dapur : ($detail->harga_mitra > 0 ? $detail->harga_mitra : 0);                $subtotalFix = $detail->qty_digunakan * $hargaSatuanFix;

                SubmissionDetails::create([
                    'submission_id' => $child->id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'satuan_id' => $detail->satuan_id,     // Ikut satuan parent
                    'qty_digunakan' => $detail->qty_digunakan,

                    'harga_satuan' => $hargaSatuanFix, // Kolom legacy jika masih dipakai
                    'harga_dapur' => 0, // Child ke supplier tidak perlu harga dapur
                    'subtotal_dapur' => 0,

                    'harga_mitra' => $hargaSatuanFix,
                    'subtotal_mitra' => $subtotalFix,

                    'subtotal_harga' => $subtotalFix,
                ]);

                $totalMitra += $subtotalFix;
            }

            // Update total harga child
            $child->update(['total_harga' => $totalMitra]);
        });

        return response()->json(['success' => true, 'message' => 'Order berhasil dipisah ke supplier']);
    }



    public function getMenuByKitchen($kitchenId)
    {
        $kitchenCodes = $this->userKitchenCodes();

        $menus = Menu::where('kitchen_id', $kitchenId)
            ->whereHas('kitchen', fn($q) => $q->whereIn('kode', $kitchenCodes))
            ->select('id', 'nama')
            ->orderBy('nama')
            ->get();

        return response()->json($menus);
    }


    // App\Http\Controllers\SubmissionController.php


    /* ================= AJAX ================= */

    // Tambahkan/Update method ini di SubmissionApprovalController

    public function getSubmissionData(Submission $submission)
    {
        $submission->load([
            'kitchen',
            'menu',
            'children.supplier',
            'children.details.bahan_baku',
            'details.bahan_baku',
            'details.unit'
        ]);

        // Format data children untuk riwayat
        $history = $submission->children->map(function ($child) {
            return [
                'id' => $child->id,
                'kode' => $child->kode,
                'supplier_nama' => $child->supplier->nama ?? 'Umum',
                'status' => $child->status,
                'total' => $child->total_harga,
                'item_count' => $child->details()->count(),
                'items' => $child->details->map(function ($detail) {
                    return [
                        'nama' => $detail->bahan_baku->nama ?? '-',
                        'qty' => $detail->qty_digunakan,
                        'harga' => $detail->harga_dapur ?? $detail->harga_mitra,
                    ];
                })->values()
            ];
        });

        $availableSuppliers = $submission->kitchen->suppliers->values();

        return response()->json([
            'id' => $submission->id,
            'kode' => $submission->kode,
            'tanggal' => \Carbon\Carbon::parse($submission->tanggal)
                ->locale('id')
                ->translatedFormat('l, d-m-Y'),
            'tanggal_digunakan' => $submission->tanggal_digunakan
                ? \Carbon\Carbon::parse($submission->tanggal_digunakan)
                    ->locale('id')
                    ->translatedFormat('l, d-m-Y')
                : '-',
            'kitchen' => $submission->kitchen->nama,
            'menu' => $submission->menu->nama,
            'porsi_besar' => $submission->porsi_besar,
            'porsi_kecil' => $submission->porsi_kecil,
            'status' => $submission->status,
            'history' => $history,
            'suppliers' => $availableSuppliers,
            // TAMBAHKAN INI: Return details langsung
            'details' => $submission->details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'nama_bahan' => $detail->bahan_baku->nama ?? '-',
                    'qty' => (float) $detail->qty_digunakan,
                    'nama_satuan' => $detail->unit->satuan ?? '-',
                    'harga_dapur' => (float) $detail->harga_dapur,
                    'subtotal_dapur' => (float) $detail->subtotal_dapur,
                ];
            })->values()
        ]);
    }
    public function getBahanByKitchen($kitchenId)
    {
        $bahanBakus = BahanBaku::whereHas('suppliers.kitchens', function ($q) use ($kitchenId) {
            $q->where('kitchens.id', $kitchenId);
        })
            ->select('id', 'nama')
            ->distinct()
            ->orderBy('nama')
            ->get();

        return response()->json($bahanBakus);
    }



}
