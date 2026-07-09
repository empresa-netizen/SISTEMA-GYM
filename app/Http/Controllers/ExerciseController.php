<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index(Request $request)
    {
        $query = Exercise::query()->orderBy('name');

        if ($search = $request->get('q')) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $exercises = $query->paginate(24)->withQueryString();

        return view('exercises.index', compact('exercises', 'search'));
    }
}
