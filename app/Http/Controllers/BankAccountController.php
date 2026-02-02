<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BankAccountController extends Controller
{
    private function checkAccess(Supplier $supplier)
    {
        $userKitchenKode = auth()->user()
            ->kitchens()
            ->pluck('kode') // Mengambil kode kitchen dari user
            ->toArray();

        // Cek apakah supplier ini terhubung dengan salah satu kitchen milik user
        return $supplier->kitchens()
            ->whereIn('kitchens.kode', $userKitchenKode)
            ->exists();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $userKitchenKode = $user->kitchens()->pluck('kode');

        $bankAccounts = BankAccount::whereHas('supplier.kitchens', function ($q) use ($userKitchenKode) {
            $q->whereIn('kitchens.kode', $userKitchenKode);
        })
            ->with('suppliers')
            ->latest()
            ->get();
        return view('bank_accounts.index', compact('bankAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'bank_name' => 'required|string|max:100',
            'account_holder_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50|unique:bank_accounts,account_number',

        ]);

        $supplier = Supplier::findOrFail($request->supplier_id);

        if (!$this->checkAccess($supplier)) {
            abort(403, 'Anda tidak memiliki akses ke supplier ini karena perbedaan dapur/kitchen.');
        }

        BankAccount::create([
            'supplier_id' => $supplier->id,
            'bank_name' => $request->bank_name,
            'account_holder_name' => $request->account_holder_name,
            'account_number' => $request->account_number,
        ]);
        return back()->with('success', 'Rekening bank berhasil ditambahkan.');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        $supplier = $bankAccount->suppliers;

        if (!$this->checkAccess($supplier)) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah rekening ini.');
        }

        $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_holder_name' => 'required|string|max100',
            'account_number ' => ['required', 'string', 'max:50', Rule::unique('bank_accounts')->ignore($bankAccount->id)]

        ]);

        $bankAccount->update([
            'bank_name' => $request->bank_name,
            'account_holder_name' => $request->account_holder_name,
            'account_number' => $request->account_number,
        ]);

        return back()->with('success', 'Rekening bank berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        $supplier = $bankAccount->suppliers;

        // 1. Cek Akses
        if (!$this->checkAccess($supplier)) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus rekening ini.');
        }

        // 2. Hapus
        $bankAccount->delete();

        return back()->with('success', 'Rekening bank berhasil dihapus.');
    }

}
