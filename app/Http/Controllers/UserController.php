<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kitchen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // tampilkan halaman user
    public function index()
    {
        $users = User::with('kitchen')->get();
        $kitchens = Kitchen::all();

        return view('master.user', compact('users', 'kitchens'));
    }

    // simpan user baru
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string',
            'dapur_id' => 'required|exists:kitchens,id',
            'role' => 'required|in:admin,superadmin',
        ]);

        User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'dapur_id' => $request->dapur_id,
            'role' => $request->role,
        ]);

        return back()->with('success', 'User berhasil ditambahkan!');
    }

    // update user
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama' => 'required|string',
            'username' => 'required|string|unique:users,username,' . $id,
            'password' => 'nullable|string',
            'dapur_id' => 'required|exists:kitchens,id',
            'role' => 'required|in:admin,superadmin',
        ]);

        $data = [
            'nama' => $request->nama,
            'username' => $request->username,
            'dapur_id' => $request->dapur_id,
            'role' => $request->role,
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'User berhasil diperbarui!');
    }

    // hapus user
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'User berhasil dihapus!');
    }
}
