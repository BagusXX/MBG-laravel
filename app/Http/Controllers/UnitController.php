<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    private function canManage()
    {
        $user = auth()->user();
        return $user->hasAnyRole(['superadmin', 'operatorKoperasi','operatorDapur']);
    }

    public function index()
    {
        $units = Unit::orderBy('id', 'desc')->paginate(10);

        $canManage = $this->canManage();

        return view('master.unit', compact('units', 'canManage'));
    }

    public function store(Request $request)
    {
        abort_if(!$this->canManage(), 403, 'Anda tidak memiliki akses untuk menambah data.');

        $request->validate([
            'satuan' => 'required|string|max:20',
            'keterangan' => 'nullable|string|max:20',
        ]);

        Unit::create([
            'satuan' => $request->satuan,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('master.unit.index')
            ->with('success', 'Data satuan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        abort_if(!$this->canManage(), 403, 'Anda tidak memiliki akses untuk menambah data.');

        $request->validate([
            'satuan' => 'required|string|max:17',
            'keterangan' => 'nullable|string|max:20',
        ]);

        $unit = Unit::findOrFail($id);

        $unit->update([
            'satuan' => $request->satuan,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('master.unit.index')
            ->with('success', 'Data satuan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        abort_if(!$this->canManage(), 403, 'Anda tidak memiliki akses untuk menambah data.');

        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->back()->with('success', 'Data satuan berhasil dihapus.');
    }
}
