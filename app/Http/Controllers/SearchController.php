<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Member;
use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $parentId = parentId();

        $members = collect();
        $workouts = collect();
        $invoices = collect();

        if (strlen($q) >= 2) {
            $members = Member::where('parent_id', $parentId)
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                })
                ->limit(8)
                ->get();

            $workouts = Workout::where('parent_id', $parentId)
                ->where('name', 'like', "%{$q}%")
                ->with('member')
                ->limit(8)
                ->get();

            $invoices = Invoice::where('parent_id', $parentId)
                ->where(function ($query) use ($q) {
                    $query->where('invoice_number', 'like', "%{$q}%")
                        ->orWhereHas('member', fn ($m) => $m->where('name', 'like', "%{$q}%"));
                })
                ->with('member')
                ->limit(8)
                ->get();
        }

        return view('prime.search', compact('q', 'members', 'workouts', 'invoices'));
    }
}
