<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Pastikan menggunakan paginate(), bukan get() atau all()
        // karena di View Anda memanggil $banks->links() dan $banks->firstItem()
        $banks = \App\Models\BankAccount::paginate(10);

        // 2. Definisi permission check
        // Jika Anda menggunakan Spatie Permission:
        $canManage = auth()->user()->can('master.bank.create');
        // ATAU set manual true untuk testing:
        // $canManage = true;

        // 3. Kirim kedua variabel ke view
        return view('master.bank', compact('banks', 'canManage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
