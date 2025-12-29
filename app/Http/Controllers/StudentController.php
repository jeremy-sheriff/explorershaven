<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Grade;
use App\Models\Guardian;
use Inertia\Inertia;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with(['guardian', 'grade'])->get();
        $grades = Grade::all();

        return Inertia::render('Students/Index', [
            'students' => $students,
            'grades' => $grades
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
