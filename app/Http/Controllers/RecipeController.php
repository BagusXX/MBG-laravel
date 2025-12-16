<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Menu;
use App\Models\Kitchen;
use App\Models\BahanBaku;
use App\Models\Unit; 
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index()
    {
        $recipes = Recipe::with(['menu', 'kitchen', 'bahanBaku'])->get();
        $menus = Menu::all();
        $kitchens = Kitchen::all();
        $bahanBaku = BahanBaku::all();
        $units = Unit::all();

        return view('setup.createmenu', compact('recipes', 'menus', 'kitchens', 'bahanBaku', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kitchen_id' => 'required|exists:kitchens,id',
            'menu_id' => 'required|exists:menus,id',
            'bahan' => 'required|array',
            'jumlah' => 'required|array',
            'satuan' => 'required|array',
            ''
        ]);

        $recipe = Recipe::create([
            'kitchen_id' => $request->kitchen_id,
            'menu_id' => $request->menu_id,
            'porsi' => $request->porsi,
        ]);

        foreach($request->bahan as $index => $bahan_id) {
            $recipe->bahanBaku()->attach($bahan_id, [
                'jumlah' => $request->jumlah[$index],
                'satuan' => $request->satuan[$index],
                'porsi' => $request->porsi ?? 1,
            ]);
        }

        return redirect()->route('setup.createmenu')->with('success', 'Menu berhasil diracik.');
    }
}