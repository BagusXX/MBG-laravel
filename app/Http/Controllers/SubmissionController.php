<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Kitchen;
use App\Models\Menu;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index()
    {
        $submission = Submission::with(['kitchen', 'menu'])->get();
        $kitchens = Kitchen::all();
        $menus = Menu::all();

        return view(
            'transaction.submission',
            compact('submission', 'kitchens', 'menus')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required',
            'tanggal' => 'required|date',
            'kitchen_id' => 'required|exists:kitchens,id',
            'menu_id' => 'required|exists:menus,id',
            'porsi' => 'required|numeric|min:1',
        ]);

        Submission::create([
            'kode' => $request->kode,
            'tanggal' => $request->tanggal,
            'kitchen_id' => $request->kitchen_id,
            'menu_id' => $request->menu_id,
            'porsi' => $request->porsi,
        ]);

        return redirect()->back()
            ->with('success', 'Pengajuan menu berhasil ditambahkan');
    }

    public function destroy($id)
    {
        Submission::findOrFail($id)->delete();

        return redirect()->back()
            ->with('success', 'Pengajuan menu berhasil dihapus');
    }
}
