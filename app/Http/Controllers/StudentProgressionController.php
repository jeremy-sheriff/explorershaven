<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentProgression;
use App\Models\SystemSetting;
use App\Services\StudentProgressionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentProgressionController extends Controller
{
    protected StudentProgressionService $progressionService;

    public function __construct(StudentProgressionService $progressionService)
    {
        $this->progressionService = $progressionService;
    }

    /**
     * Display the student progression management page
     */
    public function index()
    {
        $currentYear = SystemSetting::currentAcademicYear();
        $grades = Grade::withCount(['students' => function ($query) {
            $query->active()->currentYear();
        }])->orderBy('id')->get();

        $stats = $this->progressionService->getPromotionStats($currentYear);

        return Inertia::render('StudentProgression/Index', [
            'grades' => $grades,
            'currentYear' => $currentYear,
            'stats' => $stats,
            'maxGradeLevel' => SystemSetting::maxGradeLevel(),
            'autoGraduateEnabled' => SystemSetting::isAutoGraduateEnabled(),
        ]);
    }

    /**
     * Promote a single student
     */
    public function promoteStudent(Request $request, Student $student)
    {
        $validated = $request->validate([
            'to_grade_id' => 'required|exists:grades,id',
            'progression_type' => 'required|in:promotion,repetition,demotion',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->progressionService->promoteStudent(
                $student,
                $validated['to_grade_id'],
                $validated['progression_type'],
                $validated['notes'] ?? null
            );

            return redirect()->back()->with('success', 'Student promoted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to promote student: ' . $e->getMessage());
        }
    }

    /**
     * Promote all students in a specific grade
     */
    public function promoteGrade(Request $request)
    {
        $validated = $request->validate([
            'from_grade_id' => 'required|exists:grades,id',
            'to_grade_id' => 'required|exists:grades,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $progressions = $this->progressionService->promoteGrade(
                $validated['from_grade_id'],
                $validated['to_grade_id'],
                $validated['notes'] ?? null
            );

            return redirect()->back()->with('success', "Successfully promoted {$progressions->count()} students.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to promote grade: ' . $e->getMessage());
        }
    }

    /**
     * Promote all students (bulk promotion for new academic year)
     */
    public function promoteAll(Request $request)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $results = $this->progressionService->promoteAllStudents($validated['notes'] ?? null);

            return redirect()->back()->with('success',
                "Promotion complete! {$results['success']} students promoted, {$results['graduated']} students graduated."
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to promote students: ' . $e->getMessage());
        }
    }

    /**
     * Start a new academic year
     */
    public function startNewYear(Request $request)
    {
        $validated = $request->validate([
            'new_year' => 'required|string|max:4',
            'promote_students' => 'boolean',
        ]);

        try {
            $results = $this->progressionService->startNewAcademicYear(
                $validated['new_year'],
                $validated['promote_students'] ?? true
            );

            return redirect()->back()->with('success',
                "New academic year {$validated['new_year']} started successfully!"
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to start new year: ' . $e->getMessage());
        }
    }

    /**
     * Graduate a student manually
     */
    public function graduateStudent(Request $request, Student $student)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->progressionService->graduateStudent($student, $validated['notes'] ?? null);

            return redirect()->back()->with('success', 'Student graduated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to graduate student: ' . $e->getMessage());
        }
    }

    /**
     * View progression history
     */
    public function history(Request $request)
    {
        $query = StudentProgression::with(['student', 'fromGrade', 'toGrade', 'processedBy'])
            ->latest('processed_at');

        // Filter by academic year
        if ($request->filled('year')) {
            $query->where('academic_year', $request->year);
        }

        // Filter by progression type
        if ($request->filled('type')) {
            $query->where('progression_type', $request->type);
        }

        $progressions = $query->paginate(50);
        $years = StudentProgression::distinct()->pluck('academic_year');

        return Inertia::render('StudentProgression/History', [
            'progressions' => $progressions,
            'years' => $years,
            'filters' => $request->only(['year', 'type']),
        ]);
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'max_grade_level' => 'required|integer|min:1|max:20',
            'auto_graduate_enabled' => 'required|boolean',
        ]);

        try {
            SystemSetting::set('max_grade_level', $validated['max_grade_level'], 'integer');
            SystemSetting::set('auto_graduate_enabled', $validated['auto_graduate_enabled'] ? 'true' : 'false', 'boolean');

            return redirect()->back()->with('success', 'Settings updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}
