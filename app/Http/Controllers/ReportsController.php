<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ReportsController extends Controller
{
    public function index(): View
    {
        return view('mgteam.reports.index');
    }

    public function profile(): View
    {
        $user = auth()->user();

        return view('mgteam.reports.profile', compact('user'));
    }
}
