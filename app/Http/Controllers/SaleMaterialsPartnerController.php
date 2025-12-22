<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SaleMaterialsPartnerController extends Controller
{
    public function index()
    {
        return view('transaction.sale-materials-partner');
    }
}
