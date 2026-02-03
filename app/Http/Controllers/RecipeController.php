<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Kitchen;
use App\Models\BahanBaku;
use App\Models\RecipeBahanBaku;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Ambil KODE dapur milik user
        $kitchenKodes = $user->kitchens()->pluck('kode');
        $kitchens = Kitchen::whereIn('kode', $kitchenKodes)->get();

        $menus = Menu::whereHas('recipes.kitchen', function ($q) use ($kitchenKodes) {
            $q->whereIn('kode', $kitchenKodes);
        })
            ->with([
                'recipes' => function ($q) use ($kitchenKodes) {
                    $q->with(['bahan_baku.unit', 'kitchen'])
                        ->withCount('submissionDetails')
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
                $recipe = RecipeBahanBaku::where('id', $rowId)
                    ->where('kitchen_id', $kitchenId)
                    ->firstOrFail();

                if ($recipe->submissionDetails()->exists()) {
                    return back()->withErrors("Bahan '{$recipe->bahan_baku->nama}' tidak bisa diubah karena sudah masuk dalam data Submission.");
                }

                $recipe->update([
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
            ->whereHas('bahan_baku')
            ->join('bahan_baku', 'bahan_baku.id', '=', 'recipe_bahan_baku.bahan_baku_id')
            ->orderBy('bahan_baku.nama', 'asc')
            ->select('recipe_bahan_baku.*')
            ->get();


        abort_if($recipes->isEmpty(), 404);

        return view('recipe.show', compact('recipes'));
    }



    public function getRecipeDetail($menuId, $kitchenId)
    {
        return RecipeBahanBaku::with(['bahan_baku.unit', 'kitchen', 'menu'])
            ->join('bahan_baku', 'bahan_baku.id', '=', 'recipe_bahan_baku.bahan_baku_id')
            ->where('recipe_bahan_baku.menu_id', $menuId)
            ->where('recipe_bahan_baku.kitchen_id', $kitchenId)
            ->orderBy('bahan_baku.nama', 'asc')
            ->select('recipe_bahan_baku.*')
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

    public function duplicate(Request $request)
    {
        $user = auth()->user();
        $kitchenKodes = $user->kitchens()->pluck('kode');

        $request->validate([
            'original_menu_id' => 'required|exists:menus,id',
            'kitchen_id' => 'required|exists:kitchens,id',
            'new_menu_name' => 'required|string|max:255',
        ]);

        $kitchen = Kitchen::where('id', $request->kitchen_id)
            ->whereIn('kode', $kitchenKodes)
            ->firstOrFail();

        // Validasi menu punya resep di dapur tsb
        $sourceMenu = Menu::where('id', $request->original_menu_id)
            ->whereHas('recipes', fn($q) => $q->where('kitchen_id', $kitchen->id))
            ->firstOrFail();

        // Cegah nama dobel
        $exists = Menu::where('nama', $request->new_menu_name)
            ->whereHas('recipes', fn($q) => $q->where('kitchen_id', $kitchen->id))
            ->exists();

        if ($exists) {
            return back()->withErrors('Nama menu sudah digunakan di dapur ini.');
        }

        $sourceRecipes = RecipeBahanBaku::where('menu_id', $sourceMenu->id)
            ->where('kitchen_id', $kitchen->id)
            ->get();

        if ($sourceRecipes->isEmpty()) {
            return back()->withErrors('Menu sumber tidak memiliki resep.');
        }

        DB::transaction(function () use ($sourceMenu, $sourceRecipes, $request, $kitchen) {


            $newKode = Menu::generateUniqueKode($kitchen->kode, true);
            // 1️⃣ Buat MENU BARU di dapur tujuan (MASTER DAPUR)
            $newMenu = Menu::create([
                'kode' => $newKode,
                'nama' => $request->new_menu_name,
                'kitchen_id' => $kitchen->id,
            ]);

            // 2️⃣ Copy semua resep
            foreach ($sourceRecipes as $recipe) {
                RecipeBahanBaku::create([
                    'menu_id' => $newMenu->id,
                    'kitchen_id' => $kitchen->id,
                    'bahan_baku_id' => $recipe->bahan_baku_id,
                    'jumlah' => $recipe->jumlah,
                ]);
            }
        });


        return redirect()
            ->route('recipe.index')
            ->with('success', 'Menu berhasil diduplikasi');
    }

    private function generateMenuKode(): string
    {
        $lastKode = Menu::withTrashed()
            ->orderBy('id', 'desc')
            ->value('kode');

        if (!$lastKode) {
            return 'MNDPR0100001';
        }

        $number = (int) substr($lastKode, -7) + 1;

        return 'MNDPR01' . str_pad($number, 7, '0', STR_PAD_LEFT);
    }


}
