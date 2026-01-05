<?php

namespace App\Http\Controllers;

use App\Models\submissionOperational;
use App\Models\submissionOperationalDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OperationalSubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userKitchens = Auth::user()->kitchens()->pluck('nama','kode');

        $submissions = submissionOperational::with(['kitchen','details.barang'])
            ->whereIn('kitchen_id', $userKitchens)
            ->orderBy('created_at','desc')
            ->get();
        
        
        return view('transaction.operational-submission',compact('submissions'));
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
        $request->validate([
            'kitchen_id' => 'required',
            'items' => 'required|array'
        ]);

        DB::transaction(function () use ($request){
           $prefix = 'POPR';

           $lastSubmission = submissionOperational::where('kode', 'like', $prefix.'%')
                ->orderBy('id','desc')
                ->lockForUpdate()
                ->first();

            if ($lastSubmission) {
                $lastNumber = (int) substr($lastSubmission->kode, strlen($prefix));
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber =  1;
            }

            $newKode = $prefix . sprintf("%04d", $nextNumber);

            $submission = submissionOperational::create([
                'kode' => $newKode,
                'kitchen_id' => $request->kitchen_id,
                'status' => 'diajukan',
                'total_harga' => 0
            ]);

            $total = 0;

            foreach ($request->items as $item){
                $subtotal = $item['qty'] * $item['harga_satuan'];

                submissionOperationalDetails::create([
                    'operational_submission_id' => $submission->id,
                    'barang_id' => $item['barang_id'],
                    'qty' => $item['qty'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $subtotal
                ]);

                $total += $subtotal;
            }

            $submission->update(['total_harga' => $total]);
            
        });
        return redirect()->back()->with('success', 'Pengajuan berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //

         $user = auth()->user();
    $kitchens = $user->kitchens()->pluck('kode');

    $submission = submissionOperational::with([
            'details.barang',
            'kitchen'
        ])
        ->whereIn('kitchen_id', $kitchens)
        ->findOrFail($id);

    return view('submission.show', compact('submission'));
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
        $submission = submissionOperational::findOrFail($id);

    if ($submission->status === 'diterima') {
        return back()->with('error', 'Pengajuan sudah diterima');
    }

    $submission->details()->delete();
    $submission->delete();

    return back()->with('success', 'Pengajuan dihapus');
    }
}
