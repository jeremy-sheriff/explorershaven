<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Grade;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Fee::with('grade');

        // Filter by grade (ignore if 'all' or empty)
        if ($request->filled('grade') && $request->grade !== 'all') {
            $query->where('grade_id', $request->grade);
        }

        // Filter by term (ignore if 'all' or empty)
        if ($request->filled('term') && $request->term !== 'all') {
            $query->where('term', $request->term);
        }

        // Sort by latest first
        $query->latest();

        $fees = $query->get();
        $grades = Grade::all();

        // Get unique terms
        $terms = Fee::distinct()->pluck('term');

        return Inertia::render('Fees/Index', [
            'fees' => $fees,
            'grades' => $grades,
            'terms' => $terms,
            'filters' => $request->only(['grade', 'term']),
        ]);
    }
}
