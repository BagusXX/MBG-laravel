<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\Submission;
use App\Models\BahanBaku;
use App\Models\SubmissionDetails;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Support\Facades\Auth;

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
        // Hapus detail lama agar tidak duplikat saat update
        $submission->details()->delete();

        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 0);

            // Ambil Harga Satuan dari input (bukan subtotal)
            $hargaDapur = (float) ($item['harga_dapur'] ?? 0);
            $hargaMitra = (float) ($item['harga_mitra'] ?? 0);

            // HITUNG SUB TOTAL (Harga * Qty)
            $subtotalDapur = $hargaDapur * $qty;
            $subtotalMitra = $hargaMitra * $qty;

            SubmissionDetails::create([
                'submission_id' => $submission->id,
                'bahan_baku_id' => $item['bahan_baku_id'],
                'satuan_id' => $item['satuan_id'],
                'qty_digunakan' => $qty,

                // Simpan Harga Satuan
                'harga_dapur' => $hargaDapur,
                'harga_mitra' => $hargaMitra,

                // Simpan Hasil Perkalian ke Subtotal
                'subtotal_dapur' => $subtotalDapur,
                'subtotal_mitra' => $subtotalMitra,

                // Total global baris (biasanya mengikuti harga dapur/mitra sesuai kebutuhan laporan)
                'subtotal_harga' => $subtotalDapur,
            ]);
        }

        // Update Grand Total di tabel Submissions
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
            ->paginate(perPage: 10);

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

        if ($request->has('items')) {
            $items = $request->items;
            foreach ($items as $key => $val) {
                // List kolom yang butuh desimal
                $fields = ['qty', 'harga_dapur', 'harga_mitra'];
                
                foreach ($fields as $field) {
                    if (isset($val[$field])) {
                        //    Tapi untuk format Indonesia (Ribuan=Titik, Desimal=Koma), ini WAJIB ada.
                        $clean = str_replace('.', '', $val[$field]);
                        
                        // 2. Ganti koma jadi titik (agar terbaca sebagai desimal oleh PHP/MySQL)
                        $clean = str_replace(',', '.', $clean);

                        $items[$key][$field] = $clean;
                    }
                }
            }
            // Masukkan kembali data yang sudah bersih ke request
            $request->merge(['items' => $items]);
        }

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

        // --- TAMBAHAN: SANITASI INPUT ANGKA (Sama seperti Store) ---
        if ($request->has('items')) {
            $items = $request->items;
            foreach ($items as $key => $val) {
                $fields = ['qty', 'harga_dapur', 'harga_mitra'];
                foreach ($fields as $field) {
                    if (isset($val[$field])) {
                        $clean = str_replace('.', '', $val[$field]); // Hapus ribuan
                        $clean = str_replace(',', '.', $clean);      // Ubah desimal
                        $items[$key][$field] = $clean;
                    }
                }
            }
            $request->merge(['items' => $items]);
        }

        $request->validate([
            'tanggal_digunakan' => 'nullable|date',

            'nama_menu' => 'required_without:menu_id|string|nullable',
            'menu_id' => 'required_without:nama_menu|nullable',

            'porsi_besar' => 'nullable|integer|min:0',
            'porsi_kecil' => 'nullable|integer|min:0',

            'items' => 'required|array|min:1',
            'items.*.bahan_baku_id' => 'required|exists:bahan_baku,id',
            'items.*.qty' => 'required|numeric|min:0',
            'items.*.satuan_id' => 'required|exists:units,id',
            'items.*.harga_dapur' => 'nullable|numeric|min:0',
            'items.*.harga_mitra' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $submission) {
            $menuId = $this->handleMenuInput(
                $submission->kitchen_id,
                $request->nama_menu,
                $request->menu_id
            );

            // Update field sama seperti store (kecuali status, tipe, kode)
            $submission->update([
                'tanggal_digunakan' => $request->tanggal_digunakan,
                'menu_id' => $menuId,
                'porsi_besar' => $request->porsi_besar ?? 0,
                'porsi_kecil' => $request->porsi_kecil ?? 0,
            ]);

            // Replace detail
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
            'kitchen_id' => $submission->kitchen_id,
            'tanggal_digunakan_raw' => $submission->tanggal_digunakan ? date('Y-m-d', strtotime($submission->tanggal_digunakan)) : null,
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

        abort_if(in_array($submission->status, ['selesai', 'ditolak']), 403, 'Pengajuan sudah ditutup');

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'selected_details' => 'required|array',
            'selected_details.*' => 'exists:submission_details,id',
        ]);

        DB::transaction(function () use ($submission, $request) {
            $childSequence = Submission::where('parent_id', $submission->id)->count() + 1;
            $childKode = $submission->kode . '-' . $childSequence;

            $child = Submission::create([
                'kode' => $childKode,
                'tanggal' => now(),
                'kitchen_id' => $submission->kitchen_id,
                'menu_id' => $submission->menu_id,
                'porsi_besar' => $submission->porsi_besar,
                'porsi_kecil' => $submission->porsi_kecil,
                'total_harga' => 0,
                'tipe' => 'disetujui',
                'status' => 'diproses',
                'parent_id' => $submission->id,
                'supplier_id' => $request->supplier_id,
            ]);

            $totalHeader = 0;
            $detailsToCopy = SubmissionDetails::whereIn('id', $request->selected_details)->get();

            foreach ($detailsToCopy as $detail) {
                // Pastikan mengambil nilai dari parent. Jika subtotal_dapur kosong, 
                // sistem akan mencoba mengambil dari harga_dapur (satuan) dikali qty.
                $qty = (float) $detail->qty_digunakan;
                $subtotalParent = (float) ($detail->subtotal_dapur > 0 ? $detail->subtotal_dapur : ($detail->harga_dapur * $qty));

                // Jika masih 0, mungkin user belum klik 'Simpan Harga' di UI sebelum Split
                $hargaSatuan = $qty > 0 ? ($subtotalParent / $qty) : 0;

                SubmissionDetails::create([
                    'submission_id' => $child->id,
                    'bahan_baku_id' => $detail->bahan_baku_id,
                    'satuan_id' => $detail->satuan_id,
                    'qty_digunakan' => $qty,

                    // Simpan ke kolom dapur agar muncul di Riwayat (JS)
                    'harga_dapur' => $hargaSatuan,
                    'subtotal_dapur' => $subtotalParent,

                    // Simpan juga ke kolom mitra untuk kebutuhan invoice supplier
                    'harga_mitra' => $hargaSatuan,
                    'subtotal_mitra' => $subtotalParent,
                ]);

                $totalHeader += $subtotalParent;
            }

            $child->update(['total_harga' => $totalHeader]);
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
                'status' => 'disetujui',
                'total' => $child->total_harga,
                'item_count' => $child->details()->count(),
                'items' => $child->details->map(function ($detail) {
                    return [
                        'nama' => $detail->bahan_baku->nama ?? '-',
                        'qty' => $detail->qty_digunakan,
                        'unit' => $detail->unit->satuan ?? '-',
                        // PENTING: Jika ingin menampilkan harga per baris, gunakan subtotal_dapur
                        'harga_tampil' => (float) ($detail->subtotal_dapur > 0 ? $detail->subtotal_dapur : $detail->subtotal_mitra),
                        // Jika ingin menampilkan harga satuan (unit price)
                        'harga_satuan' => (float) ($detail->harga_dapur > 0 ? $detail->harga_dapur : $detail->harga_mitra),
                    ];
                })->values()
            ];
        });

        $availableSuppliers = $submission->kitchen->suppliers->values();

        return response()->json([
            'id' => $submission->id,
            'kode' => $submission->kode,
            'kitchen_id' => $submission->kitchen_id,
            'tanggal' => \Carbon\Carbon::parse($submission->tanggal)
                ->locale('id')
                ->translatedFormat('l, d-m-Y'),
            'tanggal_raw' => date('Y-m-d', strtotime($submission->tanggal)),
            'tanggal_digunakan_raw' => $submission->tanggal_digunakan ? date('Y-m-d', strtotime($submission->tanggal_digunakan)) : null,
            'tanggal_digunakan' => $submission->tanggal_digunakan
                ? \Carbon\Carbon::parse($submission->tanggal_digunakan)
                    ->locale('id')
                    ->translatedFormat('l, d-m-Y')
                : '-',
            'kitchen' => $submission->kitchen->nama,
            'menu_id' => $submission->menu_id,       // Pastikan menu_id dikirim
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
                    'bahan_baku_id' => $detail->bahan_baku_id, // Penting buat edit
                    'satuan_id' => $detail->satuan_id,         // Penting buat edit
                    'nama_bahan' => $detail->bahan_baku->nama ?? '-',
                    'qty' => (float) $detail->qty_digunakan,
                    'nama_satuan' => $detail->unit->satuan ?? '-',
                    'harga_dapur' => (float) $detail->harga_dapur,
                    'harga_mitra' => (float) $detail->harga_mitra,
                    'subtotal_dapur' => (float) $detail->subtotal_dapur,
                    'subtotal_mitra' => (float) $detail->subtotal_mitra,
                ];
            })->values()
        ]);
    }
    public function getBahanByKitchen($kitchenId)
    {
        return response()->json(
            BahanBaku::where('kitchen_id', $kitchenId)
                ->select('id', 'nama')
                ->orderBy('nama')
                ->get()
        );
    }



}
