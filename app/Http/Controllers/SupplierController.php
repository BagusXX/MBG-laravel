<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{

    public function index()
    {
        $suppliers = Supplier::orderBy('id', 'asc')->get();

        // Generate kode SPR11-SPR99
        $generatedCodes = [];
        for ($i = 11; $i <= 99; $i++) {
            $generatedCodes[$i] = 'SPR' . $i;
        }

        return view('master.supplier', compact('suppliers', 'generatedCodes'));
    }

   
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'kode' => 'required|unique:suppliers,kode',
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'kontak_person' => 'required|string|max:255',
            'nomor' => 'required|string|max:20',
        ]);

        Supplier::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'kontak_person' => $request->kontak_person,
            'nomor' => $request->nomor,
        ]);

        return redirect()->route('master.supplier')->with('success', 'Supplier berhasil ditambahkan.');
    }

    
    public function edit(Supplier $supplier)
    {
        $generatedCodes = [];
        for ($i = 11; $i <= 99; $i++) {
            $generatedCodes[$i] = 'SPR' . $i;
        }

        return view('supplier.edit', compact('supplier', 'generatedCodes'));
    }

    
    public function update(Request $request, Supplier $supplier)
    {
        // Validasi input
        $request->validate([
            'kode' => 'required|unique:suppliers,kode,' . $supplier->id,
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'kontak_person' => 'required|string|max:255',
            'nomor' => 'required|string|max:20',
        ]);

        $supplier->update([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'kontak_person' => $request->kontak_person,
            'nomor' => $request->nomor,
        ]);

        return redirect()->route('master.supplier')->with('success', 'Supplier berhasil diupdate.');
    }

   
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('master.supplier')->with('success', 'Supplier berhasil dihapus.');
    }

    public static function generateKode()
    {
        $lastSupplier = Supplier::orderBy('id', 'desc')->first();
        $lastNumber = $lastSupplier ? intval(substr($lastSupplier->kode, 3)) : 10; // SPR11 awal
        $nextNumber = $lastNumber + 1;

        if ($nextNumber > 99) {
            $nextNumber = 11; // reset jika sudah SPR99
        }

        return 'SPR' . $nextNumber;
    }
}
