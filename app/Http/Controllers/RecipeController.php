<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Kitchen;
use App\Models\BahanBaku;
use App\Models\RecipeBahanBaku;
use App\Models\Unit;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index()
    {
        $recipes = RecipeBahanBaku::with(['menu', 'kitchen', 'bahanBaku'])->get();
        $menus = Menu::all();
        $kitchens = Kitchen::all();
        $bahanBaku = BahanBaku::all();
        $units = Unit::all();

        // Pastikan nama view ini benar ada di folder resources/views/setup/createmenu.blade.php
        return view('setup.createmenu', compact('recipes', 'menus', 'kitchens', 'bahanBaku', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kitchen_id' => 'required|exists:kitchens,id',
            'menu_id'    => 'required|exists:menus,id',
            'bahan'      => 'required|array',
            'jumlah'     => 'required|array',
            'satuan'     => 'required|array',
        ]); // Hapus tanda '' yang tadi ada di sini

        $recipe = RecipeBahanBaku::create([
            'kitchen_id' => $request->kitchen_id,
            'menu_id'    => $request->menu_id,
        ]);

        foreach ($request->bahan as $index => $bahan_id) {
            $recipe->bahanBaku()->attach($bahan_id, [
                'jumlah' => $request->jumlah[$index],
                'satuan' => $request->satuan[$index],
                'harga' => $request->harga[$index],
            ]);
        }

        // PERBAIKAN PENTING:
        // Ganti 'setup.createmenu' menjadi 'recipe.index' agar sesuai dengan web.php
        return redirect()->route('recipe.index')->with('success', 'Menu berhasil diracik.');
    }

    public function destroy(RecipeBahanBaku $recipe)
    {
        $recipe->bahanBaku()->detach(); // penting
        $recipe->delete();

        return redirect()
            ->route('recipe.index')
            ->with('success', 'Racik menu berhasil dihapus');
    }
}
