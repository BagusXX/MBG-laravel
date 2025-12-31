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
        $user = auth()->user();
        $kitchens = $user->kitchens()->pluck('kode');

        $menus = Menu::with([
            'recipes' => function ($q) {
                $q->with(['bahan_baku.unit', 'kitchen']);
            }
        ])->get();
        $kitchens = Kitchen::all();
        $bahanBaku = BahanBaku::with('unit')->get();
        $units = Unit::all();

        // Pastikan nama view ini benar ada di folder resources/views/setup/createmenu.blade.php
        return view('setup.createmenu', compact('menus', 'kitchens', 'bahanBaku', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'kitchen_id' => 'required|exists:kitchens,id',
            'bahan_baku_id' => 'required|array|min:1',
            'bahan_baku_id.*' => 'exists:bahan_baku,id',
            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'numeric|min:0.0001',
        ]);

        foreach ($request->bahan_baku_id as $bahanId) {
            $exists = RecipeBahanBaku::where([
                'menu_id' => $request->menu_id,
                'kitchen_id' => $request->kitchen_id,
                'bahan_baku_id' => $bahanId,
            ])->exists();

            if ($exists) {
                return back()->withErrors('Bahan yang sama tidak boleh dobel dalam satu resep');
            }
        }

        foreach ($request->bahan_baku_id as $i => $bahanId) {
            RecipeBahanBaku::create([
                'menu_id' => $request->menu_id,
                'kitchen_id' => $request->kitchen_id,
                'bahan_baku_id' => $bahanId,
                'jumlah' => $request->jumlah[$i],
            ]);
        }

        return redirect()
            ->route('recipe.index')
            ->with('success', 'Resep berhasil disimpan');
    }

    public function update(Request $request, $menuId, $kitchenId)
    {
        $request->validate([
            'bahan_baku_id' => 'required|array|min:1',
            'jumlah' => 'required|array|min:1',
        ]);

        $existingIds = [];

        foreach ($request->bahan_baku_id as $i => $bahanId) {

            $rowId = $request->row_id[$i] ?? null;

            if ($rowId) {
                RecipeBahanBaku::where('id', $rowId)->update([
                    'bahan_baku_id' => $bahanId,
                    'jumlah' => $request->jumlah[$i],
                ]);

                $existingIds[] = $rowId;
            } else {
                $new = RecipeBahanBaku::create([
                    'menu_id' => $menuId,
                    'kitchen_id' => $kitchenId,
                    'bahan_baku_id' => $bahanId,
                    'jumlah' => $request->jumlah[$i],
                ]);

                $existingIds[] = $new->id;
            }
        }

        RecipeBahanBaku::where('menu_id', $menuId)
            ->where('kitchen_id', $kitchenId)
            ->whereNotIn('id', $existingIds)
            ->delete();

        return redirect()
            ->route('recipe.index')
            ->with('success', 'Resep berhasil diperbarui');
    }



    public function destroy($menuId, $kitchenId)
    {
        $used = RecipeBahanBaku::where('menu_id', $menuId)
            ->where('kitchen_id', $kitchenId)
            ->whereHas('submissionDetails')
            ->exists();

        if ($used) {
            return back()->withErrors('Resep sudah digunakan di submission');
        }

        RecipeBahanBaku::where('menu_id', $menuId)
            ->where('kitchen_id', $kitchenId)
            ->delete();

        return back()->with('success', 'Resep berhasil dihapus');
    }




    public function show($menuId, $kitchenId)
    {
        $recipes = RecipeBahanBaku::with(['bahan_baku.unit'])
            ->where('menu_id', $menuId)
            ->where('kitchen_id', $kitchenId)
            ->get();

        abort_if($recipes->isEmpty(), 404);

        return view('recipe.show', compact('recipes'));
    }



    public function getRecipeDetail($menuId, $kitchenId)
    {
        return RecipeBahanBaku::with('bahan_baku.unit')
            ->where('menu_id', $menuId)
            ->where('kitchen_id', $kitchenId)
            ->get();
    }


    public function getMenusByKitchen(Kitchen $kitchen)
    {
        return response()->json(
            $kitchen->menus()->select('id', 'nama')->get()
        );
    }

    public function getBahanDetail($id)
    {
        $bahan = BahanBaku::with('unit')->findOrFail($id);

        return response()->json([
            'id' => $bahan->id,
            'nama' => $bahan->nama,
            'harga' => $bahan->harga,
            'satuan_id' => $bahan->satuan_id,
            'satuan' => $bahan->unit?->satuan,
        ]);
    }
}
