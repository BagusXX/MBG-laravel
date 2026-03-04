<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use App\Models\Supplier;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $month = $request->query('month');
        $selectedKitchen = $request->get('kitchen', 'all');
        // ==========================
        // 1️⃣ Filter Kitchen
        // ==========================
        $kitchenIds = $user->hasRole('superadmin')
            ? null
            : $this->userKitchenCodes();

        $allKitchens = DB::table('kitchens')
            ->when($kitchenIds, function ($query) use ($kitchenIds) {
                return $query->whereIn('id', $kitchenIds);
            })
            ->select('id', 'nama')
            ->get();
        // ==========================
        // 2️⃣ Summary
        // ==========================
        $summary = $this->getSummary($kitchenIds);

        // ==========================
        // 3️⃣ Chart
        // ==========================
        $monthlyChart = $this->getMonthlySubmission($kitchenIds);
        $statusChart = $this->getStatusChart($kitchenIds);

        // ==========================
        // 4️⃣ Recent Data
        // ==========================
        $recentActivity = $this->getRecentActivity($user);
        $topKitchen = $this->getTopKitchenByNominal($kitchenIds, $month);

        // if ($user->hasRole('superadmin')) {
        //     $topKitchen = $this->getTopKitchenByNominal($kitchenIds);
        // }

        $topKitchen = $this->getTopKitchenByNominal($kitchenIds, $month);
        $topBahanBaku = $this->getTopBahanBaku($kitchenIds, $month, $selectedKitchen);

        return view('dashboard', compact(
            'summary',
            'monthlyChart',
            'statusChart',
            'allKitchens',
            'recentActivity',
            'topKitchen',
            'topBahanBaku'
        ));
    }

    /* ===================================================== */
    /* ================= FILTER FUNCTION =================== */
    /* ===================================================== */

    protected function userKitchenCodes()
    {
        $allowedCodes = auth()->user()->kitchens()->pluck('kode');
        return Kitchen::whereIn('kode', $allowedCodes)
            ->pluck('id')
            ->toArray();
    }

    protected function filterKitchen($query, $kitchenIds)
    {
        if ($kitchenIds === null) {
            return $query;
        }

        return $query->whereIn('kitchen_id', $kitchenIds);
    }

    /* ===================================================== */
    /* ================= SUMMARY =========================== */
    /* ===================================================== */

    protected function getSummary($kitchenIds)
    {
        return Cache::remember('dashboard_summary_' . auth()->id(), 30, function () use ($kitchenIds) {

            $submissionBase = $this->filterKitchen(Submission::query()->whereNull('parent_id'), $kitchenIds);
            return [
                'total_kitchen' => $kitchenIds === null
                    ? Kitchen::count()
                    : count($kitchenIds),

                'total_supplier' => $kitchenIds === null
                    ? Supplier::count()
                    : Supplier::whereHas('kitchens', function ($q) use ($kitchenIds) {
                        $q->whereIn('kitchens.id', $kitchenIds);
                    })->count(),

                'total_submission' => (clone $submissionBase)->count(),

                'diajukan' => (clone $submissionBase)
                    ->where('status', 'diajukan')
                    ->count(),

                'selesai' => (clone $submissionBase)
                    ->where('status', 'selesai')
                    ->count(),

                'diproses' => (clone $submissionBase)
                    ->where('status', 'diproses')
                    ->count(),
            ];
        });
    }

    /* ===================================================== */
    /* ================= MONTHLY CHART ===================== */
    /* ===================================================== */

    protected function getMonthlySubmission($kitchenIds)
    {
        $query = Submission::selectRaw("
                MONTH(created_at) as month,
                COUNT(*) as total
            ")
            ->whereNull('parent_id')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month');

        $query = $this->filterKitchen($query, $kitchenIds);

        $data = $query->pluck('total', 'month')->toArray();

        // Supaya 12 bulan selalu ada
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = $data[$i] ?? 0;
        }

        return $months;
    }

    /* ===================================================== */
    /* ================= STATUS CHART ====================== */
    /* ===================================================== */

    protected function getStatusChart($kitchenIds)
    {
        $query = Submission::selectRaw("
                status,
                COUNT(*) as total_count,
                SUM(total_harga) as total_rupiah
            ")
            ->whereNull('parent_id')
            // Opsional: Jika kamu hanya ingin membatasi 3 status itu saja yang muncul
            ->whereIn('status', ['diajukan', 'diproses', 'selesai'])
            ->groupBy('status');

        $query = $this->filterKitchen($query, $kitchenIds);

        return $query->get();
    }

    /* ===================================================== */
    /* ================= RECENT ACTIVITY =================== */
    /* ===================================================== */

    protected function getRecentActivity($user)
    {
        $query = Activity::with(['causer', 'subject']) // Load causer (user) dan subject (model yg diubah)
            ->latest()
            ->limit(10);


        if ($user->hasRole('superadmin')) {
            return Activity::with(['causer', 'subject'])
                ->latest()
                ->limit(10)
                ->get();
        }
        return $query->where('causer_id', $user->id)
            ->get();
    }

    protected function getTopKitchenByNominal($kitchenIds, $month = null)
    {
        // Hapus Cache::remember sementara untuk testing
        $query = Submission::query()
            ->selectRaw("
                kitchen_id,
                SUM(total_harga) as total_nominal,
                COUNT(id) as total_submission
            ")
            ->where('status', 'selesai') // Fokus ke status selesai saja dulu
            ->groupBy('kitchen_id')
            ->orderByDesc('total_nominal')
            ->with('kitchen');

        if ($month && $month !== 'all') {
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', now()->year);
        }

        if ($kitchenIds !== null) {
            $query->whereIn('kitchen_id', $kitchenIds);
        }

        return $query->get();
    }

    protected function getTopBahanBaku($kitchenIds, $month = 'all', $selectedKitchen = 'all')
    {
        $cacheKey = 'dashboard_top_bahan_' . auth()->id() . '_' . ($month ?? 'all') . '_' . ($selectedKitchen ?? 'all');

        return Cache::remember(
            $cacheKey,
            60,
            function () use ($kitchenIds, $month, $selectedKitchen) {

                $query = DB::table('submission_details as sd')
                    ->join('submissions as s', 'sd.submission_id', '=', 's.id')
                    ->join('submissions as parent', 's.parent_id', '=', 'parent.id')
                    ->join('bahan_baku as b', 'sd.bahan_baku_id', '=', 'b.id')
                    ->join('kitchens as k', 's.kitchen_id', '=', 'k.id')
                    ->selectRaw('
                    b.id,
                    b.nama as nama_bahan,
                    SUM(sd.qty_digunakan) as total_qty,
                    COUNT(sd.id) as total_penggunaan,
                    GROUP_CONCAT(DISTINCT k.nama SEPARATOR ", ") as daftar_dapur
                ')
                    ->whereNotNull('s.parent_id')       // child
                    ->where('parent.status', 'selesai') // parent selesai
                    ->groupBy('b.id', 'b.nama')
                    ->orderByDesc('total_qty')
                    ->limit(10);

                // ✅ Filter bulan (child)
                if ($month && $month !== 'all') {
                    $query->whereMonth('s.created_at', $month)
                        ->whereYear('s.created_at', now()->year);
                }

                // ✅ Filter role kitchen (batas akses user)
                if ($kitchenIds !== null) {
                    $query->whereIn('s.kitchen_id', $kitchenIds);
                }

                // ✅ Filter dapur tertentu (opsional)
                if ($selectedKitchen && $selectedKitchen !== 'all') {
                    $query->where('s.kitchen_id', $selectedKitchen);
                }

                return $query->get();
            }
        );
    }
}
