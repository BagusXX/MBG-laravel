<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kitchen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule; // Tambahkan ini untuk validasi update unique

class UserController extends Controller
{
    // 1. TAMPILKAN HALAMAN USER
    public function index()
    {
        $users = User::with(['kitchens.region','roles'])->get();
        $kitchens = Kitchen::with('region')->get();
        $roles = Role::all();

        return view('setup.user', compact('users','kitchens','roles'));
    }

    // 2. SIMPAN USER BARU
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            // Validasi apakah KODE dapur ada di tabel kitchens kolom kode
            'kitchen_kode' => 'required|array',
            'kitchen_kode.*' => 'required|exists:kitchens,kode', 
            // Validasi apakah role ada di tabel roles
            'role' => 'required|exists:roles,name', 
        ]);

        $user = User::create([
            'name' => $request->name, // PERBAIKAN: Gunakan 'name' bukan 'nama'
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign Role (Spatie)
        $user->assignRole($request->role);

        // Attach Kitchen (Simpan ke pivot kitchen_user menggunakan kode)
        $user->kitchens()->attach($request->kitchen_kode);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    // 3. UPDATE USER
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            // Validasi unique email kecuali punya user ini sendiri
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            // PERBAIKAN: Validasi ke kolom 'kode', bukan 'id'
            'kitchen_kode' => 'required|array',
            'kitchen_kode.*' => 'required|exists:kitchens,kode', 
            'role' => 'required|exists:roles,name',
        ]);

        $userData = [
            'name' => $request->name, // PERBAIKAN: Gunakan 'name' bukan 'nama'
            'email' => $request->email,
        ];

        // Hanya update password jika diisi
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        // Update Role (Ganti role lama dengan yang baru)
        $user->syncRoles([$request->role]);

        // Update Kitchen (Ganti dapur lama dengan yang baru)
        // Kita bungkus dalam array [] agar aman

        $user->kitchens()->detach();
        $user->kitchens()->attach(array_unique($request->kitchen_kode));
        
        return back()->with('success', 'User berhasil diperbarui!');
    }

    // 4. HAPUS USER
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'User berhasil dihapus!');
    }
}