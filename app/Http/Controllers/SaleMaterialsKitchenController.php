<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SaleMaterialsKitchenController extends Controller
{
    public function index()
    {
        return view('transaction.sale-materials-kitchen');
    }
}
