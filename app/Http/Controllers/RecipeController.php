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
        $recipes = RecipeBahanBaku::with(['menu', 'kitchen', 'bahan_baku'])->get();
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
            'bahan_baku_id' => 'required|array',
            'bahan_baku_id.*' => 'exists:bahan_baku,id',
            'jumlah'     => 'required|array',
        ]); // Hapus tanda '' yang tadi ada di sini

        // $recipe = RecipeBahanBaku::create([
        //     'kitchen_id' => $request->kitchen_id,
        //     'menu_id'    => $request->menu_id,
        // ]);

        foreach ($request->bahan_baku_id as $index => $bahan_id) {
            RecipeBahanBaku::create([
                'kitchen_id'    => $request->kitchen_id,
                'menu_id'       => $request->menu_id,
                'bahan_baku_id' => $bahan_id,
                'jumlah'        => $request->jumlah[$index],

            ]);
        }

        // PERBAIKAN PENTING:
        // Ganti 'setup.createmenu' menjadi 'recipe.index' agar sesuai dengan web.php
        return redirect()->route('recipe.index')->with('success', 'Menu berhasil diracik.');
    }

    public function update(Request $request, $menuId)
    {
        $request->validate([
            'kitchen_id' => 'required',
            'menu_id' => 'required',
            'bahan_baku_id' => 'required|array',
            'jumlah' => 'required|array',
        ]);

        $existingIds = [];

        foreach ($request->bahan_baku_id as $i => $bahanId) {

            $rowId = $request->row_id[$i] ?? null;

            if ($rowId) {
                // UPDATE baris lama
                RecipeBahanBaku::where('id', $rowId)->update([
                    'bahan_baku_id' => $bahanId,
                    'jumlah' => $request->jumlah[$i],
                ]);

                $existingIds[] = $rowId;
            } else {
                // TAMBAH baris baru
                $new = RecipeBahanBaku::create([
                    'menu_id' => $menuId,
                    'kitchen_id' => $request->kitchen_id,
                    'bahan_baku_id' => $bahanId,
                    'jumlah' => $request->jumlah[$i],
                ]);

                $existingIds[] = $new->id;
            }
        }

        // HAPUS yang dihapus user
        RecipeBahanBaku::where('menu_id', $menuId)
            ->where('kitchen_id', $request->kitchen_id)
            ->whereNotIn('id', $existingIds)
            ->delete();

        return redirect()
            ->route('recipe.index')
            ->with('success', 'Racikan berhasil diperbarui');
    }


    public function getRecipeDetail($menuId, $kitchenId)
    {
        return RecipeBahanBaku::with('bahan_baku.unit')
            ->where('menu_id', $menuId)
            ->where('kitchen_id', $kitchenId)
            ->get();
    }

    public function destroy(RecipeBahanBaku $recipe)
    {
        $recipe->bahanBaku()->detach(); // penting
        $recipe->delete();

        return redirect()
            ->route('recipe.index')
            ->with('success', 'Racik menu berhasil dihapus');
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
