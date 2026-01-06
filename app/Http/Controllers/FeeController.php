<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Grade;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeeController extends Controller
{
    /**
     * Allowed terms for validation
     */
    private const ALLOWED_TERMS = [
        'TERM ONE 2026',
        'TERM TWO 2026',
        'TERM THREE 2026',
    ];

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
        $validated = $request->validate($this->validationRules());

        Fee::create($validated);

        return redirect()->route('fees.index')
            ->with('success', 'Fee created successfully.');
    }

    public function update(Request $request, Fee $fee)
    {
        $validated = $request->validate($this->validationRules());

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

    /**
     * Get validation rules for fee
     *
     * @return array
     */
    private function validationRules(): array
    {
        return [
            'grade_id' => 'required|exists:grades,id',
            'amount' => 'required|numeric|min:0',
            'term' => [
                'required',
                'string',
                'max:255',
                'regex:/^TERM (ONE|TWO|THREE) \d{4}$/',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, self::ALLOWED_TERMS)) {
                        $fail('The term must be exactly: ' . implode(', ', self::ALLOWED_TERMS) . ' (uppercase required).');
                    }
                },
            ],
            'due_date' => 'required|date',
        ];
    }
}
