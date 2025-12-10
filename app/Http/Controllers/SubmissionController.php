<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Submission;
use App\Models\Kitchen;

class SubmissionController extends Controller
{
    public function index()
{
    $submission = Submission::with(['kitchen', 'menu'])->get();
    $kitchens = Kitchen::all();
    $menus = Menu::all();

    return view('transaction.submission', compact('submission', 'kitchens', 'menus'));
}

}
