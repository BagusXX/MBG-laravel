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

        return view('setup.user', compact('users', 'kitchens'));
    }

    // simpan user baru
    public function store(Request $request)
{
    $request->validate([
        'nama' => 'required',
        'email' => 'required|email|unique:users,email',
        'password' => 'required',
        'kitchen_id' => 'required',
        'role' => 'required',
    ], [
        'email.unique' => 'Email sudah digunakan! Mohon gunakan nama lain.',
    ]);

    User::create([
        'nama' => $request->nama,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'kitchen_id' => $request->kitchen_id,
        'role' => $request->role,
    ]);

    return redirect()->back()->with('success', 'User berhasil ditambahkan.');
}




public function update(Request $request, $id)
{
    $user = User::findOrFail($id);

    $request->validate([
        'nama' => 'required|string',
        'email' => 'required|email|unique:users,email,' . $id,
        'password' => 'nullable|string',
        'kitchen_id' => 'required|exists:kitchens,id',
        'role' => 'required|in:admin,superadmin',
    ]);

    $data = [
        'nama' => $request->nama,
        'email' => $request->email,
        'kitchen_id' => $request->kitchen_id,
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
