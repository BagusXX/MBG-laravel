<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // FORM TAMBAH ADMIN
    public function create()
    {
        return view('admin.create');
    }

    // PROSES TAMBAH ADMIN
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin', // Wajib: Superadmin hanya membuat admin
        ]);

        return redirect()->route('admin.create')->with('success', 'Admin berhasil dibuat!');
    }
}
