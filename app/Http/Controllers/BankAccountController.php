<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BankAccountController extends Controller
{
    private function checkAccess(Supplier $supplier)
    {
        $userKitchenKode = auth()->user()
            ->kitchens()
            ->pluck('kode')
            ->toArray();

        return $supplier->kitchens()
            ->whereIn('kitchens.kode', $userKitchenKode)
            ->exists();
    }

    private function canManage()
    {
        $user = auth()->user();
        return $user->hasAnyRole(['superadmin', 'operatorkoperasi','superadminDapur']);
    }

    public function index()
    {
        $user = auth()->user();
        $userKitchenKode = $user->kitchens()->pluck('kode');

        $bankAccounts = BankAccount::whereHas('suppliers.kitchens', function ($q) use ($userKitchenKode) {
            $q->whereIn('kitchens.kode', $userKitchenKode);
        })
            ->with('suppliers')
            ->latest()
            ->paginate(10);

        $suppliers = Supplier::whereHas('kitchens', function ($q) use ($userKitchenKode) {
            $q->whereIn('kitchens.kode', $userKitchenKode);
        })
            ->orderBy('nama', 'asc')
            ->get();

        $canManage = $this->canManage();

        return view('master.bank', compact('bankAccounts', 'suppliers', 'canManage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'suppliers_id' => 'required|exists:suppliers,id',
            'bank_name' => 'required|string|max:100',
            'account_holder_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50|unique:bank_accounts,account_number',
        ], [
            'account_number.unique' => 'Nomor rekening ini sudah terdaftar di sistem! Silakan cek kembali.',
        ]);

        $supplier = Supplier::findOrFail($request->suppliers_id);

        if (!$this->checkAccess($supplier)) {
            abort(403, 'Anda tidak memiliki akses ke supplier ini.');
        }

        BankAccount::create([
            'suppliers_id' => $supplier->id,
            'bank_name' => $request->bank_name,
            'account_holder_name' => $request->account_holder_name,
            'account_number' => $request->account_number,
        ]);

        // Menggunakan back() aman, atau bisa redirect()->route('master.bank.index')
        return back()->with('success', 'Rekening bank berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        $supplier = $bankAccount->suppliers;

        if (!$this->checkAccess($supplier)) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah rekening ini.');
        }

        $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_holder_name' => 'required|string|max:100',
            'account_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('bank_accounts')->ignore($bankAccount->id)
            ],
            [
                'account_number.unique' => 'Nomor rekening ini sudah terdaftar di sistem! Silakan cek kembali.',
            ]
        ]);

        $bankAccount->update([
            'bank_name' => $request->bank_name,
            'account_holder_name' => $request->account_holder_name,
            'account_number' => $request->account_number,
        ]);

        return back()->with('success', 'Rekening bank berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        $supplier = $bankAccount->suppliers;

        if (!$this->checkAccess($supplier)) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus rekening ini.');
        }

        $bankAccount->delete();

        return back()->with('success', 'Rekening bank berhasil dihapus.');
    }

    public function checkAccountNumber(Request $request)
    {
        try {
            $exists = BankAccount::where('account_number', $request->account_number)
                ->when($request->id, function ($q) use ($request) {
                    return $q->where('id', '!=', $request->id);
                })
                ->exists();

            return response()->json(['exists' => $exists]);
        } catch (\Exception $e) {
            // Ini akan membantu Anda melihat error di log jika masih 500
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
