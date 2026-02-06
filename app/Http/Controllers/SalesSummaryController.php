<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalesSummaryController extends Controller
{
    public function index()
    {
        return view('report.sales-summary');
    }
}
