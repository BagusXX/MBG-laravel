<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Kitchen;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubmissionController extends Controller
{
    public function index()
    {
        return view('transaction.submission', [
            'submissions' => Submission::with(['kitchen', 'menu'])->get(),
            'kitchens'    => Kitchen::all(),
            'menus'       => Menu::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string',
            'tanggal' => 'required|date',
            'kitchen_id' => 'required|exists:kitchens,id',
            'menu_id' => [
                'required',
                Rule::exists('menus', 'id')
                    ->where('kitchen_id', $request->kitchen_id),
            ],
            'porsi' => 'required|numeric|min:1',
        ]);

        Submission::create($request->only([
            'kode',
            'tanggal',
            'kitchen_id',
            'menu_id',
            'porsi',
        ]));

        return back()->with('success', 'Pengajuan menu berhasil ditambahkan.');
    }

    public function destroy(Submission $submission)
    {
        $submission->delete();

        return back()->with('success', 'Pengajuan menu berhasil dihapus.');
    }

    public function getMenuByKitchen(Kitchen $kitchen)
    {
        return response()->json(
            $kitchen->menus()->select('id', 'nama')->get()
        );
    }
}
