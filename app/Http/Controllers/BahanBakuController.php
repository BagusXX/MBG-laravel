<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Kitchen;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Tambahkan ini untuk query manual ke tabel pivot

class BahanBakuController extends Controller
{
    // Tampilkan halaman bahan baku
    public function index(Request $request)
    {
        $user = Auth::user();

        $canManage = $this->canManage();

        // -----------------------------------------------------------
        // 1. AMBIL KITCHEN MILIK USER (LOGIC BARU)
        // -----------------------------------------------------------
        // Kita ambil daftar kode kitchen dari tabel pivot 'kitchen_user'
        $myKitchenCodes = DB::table('kitchen_user')
            ->where('user_id', $user->id)
            ->pluck('kitchen_kode')
            ->toArray();

        // Isi variabel $kitchens berdasarkan kode yang didapat di atas
        // Ini akan memfilter dropdown agar hanya muncul dapur milik user
        $kitchens = Kitchen::whereIn('kode', $myKitchenCodes)->get();

        // Ambil ID untuk filtering data bahan baku
        $kitchenIds = $kitchens->pluck('id');

        // -----------------------------------------------------------
        // 2. QUERY DATA BAHAN BAKU
        // -----------------------------------------------------------
        $query = BahanBaku::with(['kitchen']);

        // Filter: Hanya tampilkan bahan baku yang kitchen_id nya ada di list dapur user
        if ($kitchenIds->isNotEmpty()) {
            $query->whereIn('kitchen_id', $kitchenIds);
        } else {
            // Jika user tidak punya dapur, jangan tampilkan data apa pun
            $query->where('id', -1);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                    ->orWhere('kode', 'LIKE', "%{$search}%");
            });
        }

        $items = $query->paginate(10)->withQueryString();


        // Pre-generate kode untuk semua dapur milik user
        $generatedCodes = [];
        foreach ($kitchens as $k) {
            $generatedCodes[$k->id] = $this->generateKode($k->kode);
        }

        // Variabel tetap $kitchens sesuai permintaan
        return view('dashboard.master.bahan-baku.index', compact('items', 'kitchens', 'generatedCodes', 'canManage'));
    }

    // Generate kode bahan baku: 2 digit + kode dapur
    private function generateKode($kodeDapur)
    {
        // Gunakan withTrashed() agar data yang sudah di-soft delete tetap terbaca.
        // Jika tidak, kode bisa bentrok (Duplicate Entry) dengan data sampah.
        $lastItem = BahanBaku::withTrashed()
            ->where('kode', 'LIKE', "BN{$kodeDapur}%")
            ->orderBy('kode', 'desc')
            ->first();

        if (!$lastItem) {
            return 'BN' . $kodeDapur . '111';
        }

        $lastNumber = (int) substr($lastItem->kode, -3);
        $nextNumber = $lastNumber + 1;

        if ($nextNumber > 999)
            $nextNumber = 999;

        $num = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return 'BN' . $kodeDapur . $num;
    }

    // Simpan bahan baku baru
    public function store(Request $request)
    {

        if (!$this->canManage()) {
            abort(403, 'Anda tidak memiliki akses untuk menambah data.');
        }

        $user = Auth::user();

        $request->validate([
            'nama' => 'required|string|max:255',
            'harga' => 'nullable|numeric|min:0',
            'kitchen_id' => 'required|exists:kitchens,id',
        ]);

        $kitchen = Kitchen::findOrFail($request->kitchen_id);

        // -----------------------------------------------------------
        // VALIDASI AKSES (PERBAIKAN)
        // -----------------------------------------------------------
        // Cek manual ke tabel pivot kitchen_user menggunakan KODE
        $hasAccess = DB::table('kitchen_user')
            ->where('user_id', $user->id)
            ->where('kitchen_kode', $kitchen->kode)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke kitchen ini');
        }

        BahanBaku::create([
            'kode' => $this->generateKode($kitchen->kode),
            'nama' => $request->nama,
            'harga' => $request->input('harga', 0),
            'kitchen_id' => $request->kitchen_id,
        ]);

        return redirect()->route('dashboard.master.bahan-baku.index')
            ->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {

        if (!$this->canManage()) {
            abort(403, 'Anda tidak memiliki akses untuk menambah data.');
        }

        $user = Auth::user();

        $item = BahanBaku::with('kitchen')->findOrFail($id);

        // -----------------------------------------------------------
        // VALIDASI AKSES DATA LAMA (PERBAIKAN)
        // -----------------------------------------------------------
        $hasAccessOrigin = DB::table('kitchen_user')
            ->where('user_id', $user->id)
            ->where('kitchen_kode', $item->kitchen->kode)
            ->exists();

        if (!$hasAccessOrigin) {
            abort(403, 'Anda tidak memiliki akses ke data ini');
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'harga' => 'nullable|numeric|min:0',
            'kitchen_id' => 'required|exists:kitchens,id',
        ]);

        $data = [
            'nama' => $request->nama,
            'harga' => $request->input('harga', 0),
            'kitchen_id' => $request->kitchen_id,
        ];

        // Jika pindah dapur, cek akses dapur tujuan & generate kode baru
        if ($item->kitchen_id != $request->kitchen_id) {
            $kitchen = Kitchen::findOrFail($request->kitchen_id);

            // Cek akses ke dapur TUJUAN
            $hasAccessDest = DB::table('kitchen_user')
                ->where('user_id', $user->id)
                ->where('kitchen_kode', $kitchen->kode)
                ->exists();

            if (!$hasAccessDest) {
                abort(403, 'Anda tidak memiliki akses ke kitchen tujuan');
            }

            $data['kode'] = $this->generateKode($kitchen->kode);
        }

        $item->update($data);

        return redirect()
            ->route('dashboard.master.bahan-baku.index')
            ->with('success', 'Bahan baku berhasil diperbarui.');
    }

    public function destroy($id)
    {
        if (!$this->canManage()) {
            abort(403, 'Anda tidak memiliki akses untuk menambah data.');
        }

        $user = Auth::user();
        $item = BahanBaku::with('kitchen')->findOrFail($id);

        // Cek Validasi Hapus
        $hasAccess = DB::table('kitchen_user')
            ->where('user_id', $user->id)
            ->where('kitchen_kode', $item->kitchen->kode)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke data ini');
        }

        $item->delete();

        return redirect()->route('dashboard.master.bahan-baku.index')
            ->with('success', 'Bahan baku berhasil dihapus.');
    }
    // Fungsi bantuan untuk cek role
    private function canManage()
    {
        $user = Auth::user();
        // Pastikan user memiliki salah satu dari role ini
        return $user->hasAnyRole(['superadmin', 'operatorDapur','superadminDapur']);
    }
}
