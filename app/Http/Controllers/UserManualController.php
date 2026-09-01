<?php

namespace App\Http\Controllers;

use App\Models\UserManual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserManualController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userRoleName = $user->getRoleNames()->first();

        // Superadmin & superadminDapur bisa lihat panduan role lain via dropdown
        $isSuperRole = $user->hasAnyRole(['superadmin', 'superadminDapur']);

        $allRoles = [];
        if ($isSuperRole) {
            $allRoles = Role::orderBy('name')->pluck('name')->toArray();
        }

        // Tentukan role yang panduannya akan ditampilkan
        $selectedRole = $request->get('role', $userRoleName);

        // Non-super role hanya boleh lihat panduan sendiri
        if (!$isSuperRole) {
            $selectedRole = $userRoleName;
        }

        $manual = UserManual::where('role_name', $selectedRole)->first();

        return view('user_manual.index', compact(
            'manual',
            'selectedRole',
            'allRoles',
            'isSuperRole',
            'userRoleName'
        ));
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasRole('superadmin'), 403, 'Akses ditolak.');

        $request->validate([
            'role_name' => 'required|string|max:255',
            'file_pdf'  => 'required|mimes:pdf|max:10240',
        ]);

        $roleName = $request->role_name;

        // Cek apakah sudah ada panduan untuk role ini
        $existing = UserManual::where('role_name', $roleName)->first();

        // Hapus file lama jika ada
        if ($existing && $existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
            Storage::disk('public')->delete($existing->file_path);
        }

        // Upload file baru
        $file = $request->file('file_pdf');
        $namaFile = $file->getClientOriginalName();
        // Path storage juga diganti ke user-manuals
        $filePath = $file->store('uploads/user-manuals', 'public');

        // Update atau create
        UserManual::updateOrCreate(
            ['role_name' => $roleName],
            [
                'nama_file' => $namaFile,
                'file_path' => $filePath,
            ]
        );

        return redirect()
            ->route('user-manual.index', ['role' => $roleName])
            ->with('success', 'User manual berhasil diupload untuk role: ' . $roleName);
    }

    public function download($id)
    {
        $manual = UserManual::findOrFail($id);

        $fullPath = Storage::disk('public')->path($manual->file_path);

        if (!file_exists($fullPath)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download($fullPath, $manual->nama_file);
    }

    public function destroy($id)
    {
        abort_if(!auth()->user()->hasRole('superadmin'), 403, 'Akses ditolak.');

        $manual = UserManual::findOrFail($id);

        if ($manual->file_path && Storage::disk('public')->exists($manual->file_path)) {
            Storage::disk('public')->delete($manual->file_path);
        }

        $manual->delete();

        return redirect()
            ->route('user-manual.index')
            ->with('success', 'User manual berhasil dihapus.');
    }
}
