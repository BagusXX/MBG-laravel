<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\region;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
{
    /**
     * Helper untuk cek akses
     * Hanya 'superadmin' dan 'operator koperasi' yang boleh return true
     */
    private function canManage()
    {
        $user = auth()->user();
        // Sesuaikan 'role' dengan nama kolom di database user Anda
        // atau gunakan $user->hasRole(...) jika pakai Spatie
        return $user->hasAnyRole(['superadmin', 'operatorKoperasi']);
    }

    public function index()
    {
        $user = auth()->user();

        $userKitchenKode = $user->kitchens()->pluck('kode');


        $suppliers = Supplier::with('kitchens')
            ->whereHas('kitchens', function ($q) use ($userKitchenKode) {
                $q->whereIn('kitchens.kode', $userKitchenKode);
            })
            ->orderBy('suppliers.id')
            ->paginate(10);

        $kitchens = Kitchen::whereIn('kode', $userKitchenKode)->get();
        $kodeBaru = $this->generateKode();

        $canManage = $this->canManage();

        return view('master.supplier', compact('suppliers', 'kitchens', 'kodeBaru', 'canManage'));
    }


    public function store(Request $request)
    {
        // 1. Cek Role (Hanya Operator Koperasi & Superadmin)
        abort_if(!$this->canManage(), 403, 'Anda tidak memiliki akses untuk menambah data.');

        $user = auth()->user();
        $userKitchenKode = $user->kitchens()->pluck('kode')->toArray();
        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'kontak' => 'required|string|max:255',
            'nomor' => 'required|string|max:20',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'kitchens' => ['required', 'array'],
            'kitchens.*' => [Rule::in($userKitchenKode)],
        ]);

        $pathGambar = null;

        $supplier = Supplier::create([
            'kode' => self::generateKode(),
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'kontak' => $request->kontak,
            'nomor' => $request->nomor,
            'gambar' => $pathGambar,
        ]);

        if ($request->hasFile('gambar')) {

            // hapus gambar lama
            if ($supplier->gambar && Storage::disk('public')->exists($supplier->gambar)) {
                Storage::disk('public')->delete($supplier->gambar);
            }

            $pathGambar = $request->file('gambar')
                ->store('uploads/suppliers', 'public');

            $supplier->update(['gambar' => $pathGambar]);
        }

        $supplier->save();
        // attach dapur
        $supplier->kitchens()->sync($request->kitchens);

        return redirect()->route('master.supplier.index')->with('success', 'Supplier berhasil ditambahkan.');
    }


    public function edit(Supplier $supplier)
    {
        // 1. Cek Role Terlebih Dahulu
        abort_if(!$this->canManage(), 403, 'Anda tidak memiliki akses untuk mengedit data.');

        $user = auth()->user();
        $userKitchenKode = $user->kitchens()->pluck('kode')->toArray();

        // Cek akses: User hanya boleh edit jika punya akses ke salah satu kitchen supplier tsb
        $hasAccess = $supplier->kitchens()
            ->whereIn('kitchens.kode', $userKitchenKode)
            ->exists();

        abort_if(!$hasAccess, 403, 'Anda tidak memiliki akses ke supplier ini.');

        // FIX: Hanya ambil kitchen milik user untuk pilihan dropdown
        $kitchens = $user->kitchens;

        // Ambil kitchen yang sudah terhubung untuk auto-select di view
        // Hanya pluck ID atau Kode, tergantung value checkbox di view
        $selectedKitchens = $supplier->kitchens->pluck('kode')->toArray();

        // FIX: Tambahkan 'kitchens' dan 'selectedKitchens' ke compact
        return view('supplier.edit', compact('supplier', 'kitchens', 'selectedKitchens'));
    }


    public function update(Request $request, Supplier $supplier)
    {
        // 1. Cek Role
        abort_if(!$this->canManage(), 403, 'Anda tidak memiliki akses untuk mengubah data.');

        $user = auth()->user();
        $userKitchenKode = $user->kitchens()->pluck('kode')->toArray();

        // 🔐 authorization manual
        $hasAccess = $supplier->kitchens()
            ->whereIn('kitchens.kode', $userKitchenKode)
            ->exists();

        abort_if(!$hasAccess, 403, 'Anda tidak berhak mengubah supplier ini');
        // Validasi input
        $request->validate([
            'kode' => 'required|unique:suppliers,kode,' . $supplier->id,
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'kontak' => 'required|string|max:255',
            'nomor' => 'required|string|max:20',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'kitchens' => ['required', 'array'],
            'kitchens.*' => [Rule::in($userKitchenKode)],
        ]);

        $supplier->update([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'kontak' => $request->kontak,
            'nomor' => $request->nomor,
        ]);

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($supplier->gambar && Storage::disk('public')->exists($supplier->gambar)) {
                Storage::disk('public')->delete($supplier->gambar);
            }

            // Upload gambar baru
            $pathGambar = $request->file('gambar')->store('uploads/suppliers', 'public');
            $supplier->update(['gambar' => $pathGambar]);
        }

        // sync hanya kitchen milik user
        $supplier->kitchens()->sync($request->kitchens);

        return redirect()->route('master.supplier.index')->with('success', 'Supplier berhasil diupdate.');
    }


    public function destroy(Supplier $supplier)
    {
        // 1. Cek Role
        abort_if(!$this->canManage(), 403, 'Anda tidak memiliki akses untuk menghapus data.');

        $user = auth()->user();
        $userKitchenKode = $user->kitchens()->pluck('kode')->toArray();

        $supplierKitchen = $supplier->kitchens()->pluck('kitchens.kode')->toArray();

        // jika ada kitchen supplier di luar milik user → block
        $unauthorized = array_diff($supplierKitchen, $userKitchenKode);

        abort_if(!empty($unauthorized), 403, 'Supplier ini terhubung dengan dapur lain');

        if ($supplier->gambar && Storage::disk('public')->exists($supplier->gambar)) {
            Storage::disk('public')->delete($supplier->gambar);
        }

        $supplier->kitchens()->detach();
        $supplier->delete();

        return redirect()
            ->route('master.supplier.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }



    public static function generateKode()
    {
        // 1. Ambil supplier dengan angka urut paling besar
        // Kita pakai SUBSTRING & CAST agar sortingnya benar secara angka (bukan string)
        // Contoh: Agar SPR10 dianggap lebih besar dari SPR2
        $lastSupplier = Supplier::select('kode')
            ->orderByRaw('CAST(SUBSTRING(kode, 4) AS UNSIGNED) DESC')
            ->first();

        // 2. Ambil angka terakhir
        if ($lastSupplier) {
            // Hapus 'SPR' (3 karakter pertama) dan ambil sisanya sebagai integer
            $lastNumber = (int) substr($lastSupplier->kode, 3);
            $nextNumber = $lastNumber + 1;
        } else {
            // Jika belum ada data sama sekali
            $nextNumber = 1;
        }

        // 3. Format kode dengan str_pad
        // Parameter '3' artinya minimal 3 digit.
        // Jika angka 1    -> SPR001
        // Jika angka 99   -> SPR099
        // Jika angka 100  -> SPR100 (Otomatis melebar)
        // Jika angka 1000 -> SPR1000
        return 'SPR' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
