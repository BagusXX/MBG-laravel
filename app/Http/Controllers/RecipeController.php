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

        // Ambil KODE dapur milik user
        $kitchenKodes = $user->kitchens()->pluck('kode');
        $kitchens = Kitchen::whereIn('kode', $kitchenKodes)->get();

        $menus = Menu::with([
            'recipes' => function ($q) use ($kitchenKodes) {
                $q->with(['bahan_baku.unit', 'kitchen'])
                    ->whereHas('kitchen', function ($k) use ($kitchenKodes) {
                        $k->whereIn('kode', $kitchenKodes);
                    });
            }
        ])->paginate(10);

        // HANYA dapur user

        $bahanBaku = BahanBaku::with('unit')
            ->whereHas('kitchen', function ($q) use ($kitchenKodes) {
                $q->whereIn('kode', $kitchenKodes);
            })
            ->get();

        $units = Unit::all();

        // Pastikan nama view ini benar ada di folder resources/views/setup/createmenu.blade.php
        return view('setup.createmenu', compact(
            'menus',
            'kitchens',
            'bahanBaku',
            'units'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $kitchenKodes = $user->kitchens()->pluck('kode');

        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'kitchen_id' => 'required|exists:kitchens,id',
            'bahan_baku_id' => 'required|array|min:1',
            'bahan_baku_id.*' => 'exists:bahan_baku,id',
            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'numeric|min:0.0001',
        ]);

        // 🔒 pastikan dapur milik user (VALID UNTUK CENTRAL & CABANG)
        $kitchen = Kitchen::where('id', $request->kitchen_id)
            ->whereIn('kode', $kitchenKodes)
            ->firstOrFail();

        foreach ($request->bahan_baku_id as $bahanId) {
            $exists = RecipeBahanBaku::where([
                'menu_id' => $request->menu_id,
                'kitchen_id' => $kitchen->id,
                'bahan_baku_id' => $bahanId,
            ])->exists();

            if ($exists) {
                return back()->withErrors('Bahan yang sama tidak boleh dobel dalam satu resep');
            }
        }

        foreach ($request->bahan_baku_id as $i => $bahanId) {
            RecipeBahanBaku::create([
                'menu_id' => $request->menu_id,
                'kitchen_id' => $kitchen->id,
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
        $user = auth()->user();
        $kitchenKodes = $user->kitchens()->pluck('kode');

        // 🔒 validasi dapur milik user
        Kitchen::where('id', $kitchenId)
            ->whereIn('kode', $kitchenKodes)
            ->firstOrFail();

        $request->validate([
            'bahan_baku_id' => 'required|array|min:1',
            'jumlah' => 'required|array|min:1',
        ]);

        $existingIds = [];

        foreach ($request->bahan_baku_id as $i => $bahanId) {
            $rowId = $request->row_id[$i] ?? null;

            if ($rowId) {
                RecipeBahanBaku::where('id', $rowId)
                    ->where('kitchen_id', $kitchenId)
                    ->update([
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
        $user = auth()->user();
        $kitchenKodes = $user->kitchens()->pluck('kode');

        // 🔒 validasi dapur
        Kitchen::where('id', $kitchenId)
            ->whereIn('kode', $kitchenKodes)
            ->firstOrFail();

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
        $user = auth()->user();
        $kitchenKode = $user->kitchens()->pluck('kode');

        $recipes = RecipeBahanBaku::with(['bahan_baku.unit'])
            ->where('menu_id', $menuId)
            ->where('kitchen_id', $kitchenId)
            ->get();

        abort_if($recipes->isEmpty(), 404);

        return view('recipe.show', compact('recipes'));
    }



    public function getRecipeDetail($menuId, $kitchenId)
    {
        return RecipeBahanBaku::with('bahan_baku.unit', 'kitchen', 'menu')
            ->where('menu_id', $menuId)
            ->where('kitchen_id', $kitchenId)
            ->get();
    }


    public function getMenusByKitchen($kitchenId)
    {
        $user = auth()->user();

        // pastikan dapur milik user
        $kitchen = Kitchen::where('id', $kitchenId)
            ->whereIn('kode', $user->kitchens()->pluck('kode'))
            ->firstOrFail();

        return response()->json(
            Menu::where('kitchen_id', $kitchen->id)
                ->select('id', 'nama')
                ->orderBy('nama', 'asc')
                ->get()
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

    public function getBahanByKitchen($kitchenId)
    {
        $user = auth()->user();

        // pastikan dapur milik user
        $kitchen = Kitchen::where('id', $kitchenId)
            ->whereIn('kode', $user->kitchens()->pluck('kode'))
            ->firstOrFail();

        $bahanBaku = BahanBaku::where('kitchen_id', $kitchen->id)
            ->with('unit')
            ->select('id', 'nama', 'harga', 'satuan_id', 'kitchen_id')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'harga' => $item->harga,
                    'satuan_id' => $item->satuan_id,
                    'satuan' => $item->unit ? $item->unit->satuan : null,
                ];
            });

        return response()->json($bahanBaku);
    }
}
