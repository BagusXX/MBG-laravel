<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::orderBy('id', 'desc')->paginate(10);

        return view('master.unit', compact('units'));
    }

    public function store(Request $request)
    {
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
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->back()->with('success', 'Data satuan berhasil dihapus.');
    }
}
