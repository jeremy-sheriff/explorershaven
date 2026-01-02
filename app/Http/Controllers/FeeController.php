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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'amount' => 'required|numeric|min:0',
            'term' => 'required|string|max:255',
            'due_date' => 'required|date',
        ]);

        Fee::create($validated);

        return redirect()->route('fees.index')
            ->with('success', 'Fee created successfully.');
    }

    public function update(Request $request, Fee $fee)
    {
        $validated = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'amount' => 'required|numeric|min:0',
            'term' => 'required|string|max:255',
            'due_date' => 'required|date',
        ]);

        $fee->update($validated);

        return redirect()->route('fees.index')
            ->with('success', 'Fee updated successfully.');
    }

    public function destroy(Fee $fee)
    {
        $fee->delete();

        return redirect()->route('fees.index')
            ->with('success', 'Fee deleted successfully.');
    }
}
