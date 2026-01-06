<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\SystemSetting;
use Inertia\Inertia;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['grade', 'guardian']);

        // Filter by grade
        if ($request->filled('grade') && $request->grade !== 'all') {
            $query->where('grade_id', $request->grade);
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('adm_no', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->get();
        $grades = Grade::all();

        return Inertia::render('Students/Index', [
            'students' => $students,
            'grades' => $grades,
            'filters' => $request->only(['grade', 'status', 'search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'adm_no' => 'required|unique:students',
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'grade_id' => 'required|exists:grades,id',
            'guardian_first_name' => 'required|string',
            'guardian_last_name' => 'required|string',
            'guardian_phone' => 'required|string',
        ]);

        // Create or find guardian
        $guardian = Guardian::firstOrCreate([
            'phone_number' => $validated['guardian_phone']
        ], [
            'first_name' => $validated['guardian_first_name'],
            'middle_name' => '',
            'last_name' => $validated['guardian_last_name'],
        ]);

        // Create student
        Student::create([
            'adm_no' => $validated['adm_no'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'grade_id' => $validated['grade_id'],
            'guardian_id' => $guardian->id,
        ]);

        return redirect()->route('students.index'); // Changed this line
    }


    public function show(Student $student)
    {
        $student->load([
            'guardian',
            'grade',
            'feePayments.fee',
            'feeCredits.fromPayment',
            'feeCredits.appliedToFee'
        ]);

        // Calculate total paid
        $totalPaid = $student->feePayments->sum('amount_paid');

        // Calculate total balance
        $totalBalance = $student->feePayments->sum('balance');

        // Get available credits
        $availableCredits = $student->feeCredits()->where('status', 'available')->sum('amount');

        // Get applied credits
        $appliedCredits = $student->feeCredits()->where('status', 'applied')->sum('amount');

        $academicYear = SystemSetting::currentAcademicYear();
        // Get current term fees
        $currentMonth = now()->month;
        $currentYear = 2026; // Adjust as needed
        $currentTerm = match(true) {
            $currentMonth >= 1 && $currentMonth <= 4 => "Term 1 {$currentYear}",
            $currentMonth >= 5 && $currentMonth <= 8 => "Term 2 {$currentYear}",
            $currentMonth >= 9 && $currentMonth <= 12 => "Term 3 {$currentYear}",
            default => "Term 1 {$currentYear}"
        };

        $currentTermFee = Fee::query()->where('grade_id', $student->grade_id)
            ->where('term', $currentTerm)
            ->first();

        $currentTermBalance = 0;
        if ($currentTermFee) {
            $paidForCurrentTerm = $student->feePayments()
                ->where('fee_id', $currentTermFee->id)
                ->sum('amount_paid');
            $currentTermBalance = max(0, $currentTermFee->amount - $paidForCurrentTerm - $availableCredits);
        }

        return response()->json([
            'student' => $student,
            'summary' => [
                'total_paid' => $totalPaid,
                'total_balance' => $totalBalance,
                'available_credits' => $availableCredits,
                'applied_credits' => $appliedCredits,
                'current_term_balance' => $currentTermBalance,
            ],
            'payments' => $student->feePayments()->with('fee')->latest()->get(),
            'credits' => $student->feeCredits()->with(['fromPayment', 'appliedToFee'])->latest()->get(),
        ]);
    }


    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'adm_no' => 'required|unique:students,adm_no,' . $student->id,
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'grade_id' => 'required|exists:grades,id',
            'guardian_first_name' => 'required|string',
            'guardian_last_name' => 'required|string',
            'guardian_phone' => 'required|string',
        ]);

        // Update guardian
        $student->guardian->update([
            'first_name' => $validated['guardian_first_name'],
            'last_name' => $validated['guardian_last_name'],
            'phone_number' => $validated['guardian_phone'],
        ]);

        // Update student
        $student->update([
            'adm_no' => $validated['adm_no'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'grade_id' => $validated['grade_id'],
        ]);

        return redirect()->route('students.index'); // Changed this line
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->back()->with('success', 'Student deleted successfully');
    }
}
