<?php

namespace App\Http\Controllers;

use App\Models\FeeCredit;
use App\Models\Student;
use App\Models\Fee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeeCreditController extends Controller
{
    public function index(Request $request)
    {
        $query = FeeCredit::with(['student.grade', 'fromPayment', 'appliedToFee']);

        // Filter by student (ignore if 'all' or empty)
        if ($request->filled('student') && $request->student !== 'all') {
            $query->where('student_id', $request->student);
        }

        // Filter by status (ignore if 'all' or empty)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by grade (ignore if 'all' or empty)
        if ($request->filled('grade') && $request->grade !== 'all') {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('grade_id', $request->grade);
            });
        }

        // Sort by latest first
        $query->latest();

        $credits = $query->get();
        $students = Student::with('grade')->get();
        $fees = Fee::with('grade')->get();

        // Get unique grades
        $grades = \App\Models\Grade::all();

        // Calculate totals
        $totalAvailable = FeeCredit::where('status', 'available')->sum('amount');
        $totalApplied = FeeCredit::where('status', 'applied')->sum('amount');

        return Inertia::render('FeeCredits/Index', [
            'credits' => $credits,
            'students' => $students,
            'fees' => $fees,
            'grades' => $grades,
            'totalAvailable' => $totalAvailable,
            'totalApplied' => $totalApplied,
            'filters' => $request->only(['student', 'status', 'grade']),
        ]);
    }
}
