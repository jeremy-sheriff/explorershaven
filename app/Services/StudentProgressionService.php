<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentProgression;
use App\Models\SystemSetting;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class StudentProgressionService
{
    /**
     * Promote a single student to the next grade
     */
    public function promoteStudent(
        Student $student,
        int $toGradeId,
        string $progressionType = 'promotion',
        ?string $notes = null,
        ?int $processedBy = null
    ): StudentProgression {
        return DB::transaction(function () use ($student, $toGradeId, $progressionType, $notes, $processedBy) {
            $fromGradeId = $student->grade_id;
            $currentYear = SystemSetting::currentAcademicYear();

            // Create progression record
            $progression = StudentProgression::create([
                'student_id' => $student->id,
                'from_grade_id' => $fromGradeId,
                'to_grade_id' => $toGradeId,
                'academic_year' => $currentYear,
                'progression_type' => $progressionType,
                'notes' => $notes,
                'processed_by' => $processedBy ?? auth()->id(),
                'processed_at' => now(),
            ]);

            // Update student's grade
            $student->update([
                'grade_id' => $toGradeId,
            ]);

            // Check if student should be graduated
            if ($this->shouldGraduateStudent($student)) {
                $this->graduateStudent($student);
            }

            return $progression;
        });
    }

    /**
     * Promote all students in a specific grade to the next grade
     */
    public function promoteGrade(
        int $fromGradeId,
        int $toGradeId,
        ?string $notes = null,
        ?int $processedBy = null
    ): Collection {
        $students = Student::active()
            ->currentYear()
            ->where('grade_id', $fromGradeId)
            ->get();

        $progressions = collect();

        foreach ($students as $student) {
            $progressions->push(
                $this->promoteStudent($student, $toGradeId, 'promotion', $notes, $processedBy)
            );
        }

        return $progressions;
    }

    /**
     * Promote all active students to the next grade (bulk promotion)
     */
    public function promoteAllStudents(?string $notes = null, ?int $processedBy = null): array
    {
        $grades = Grade::orderBy('id')->get();
        $results = [
            'success' => 0,
            'graduated' => 0,
            'failed' => 0,
            'details' => [],
        ];

        DB::transaction(function () use ($grades, $notes, $processedBy, &$results) {
            foreach ($grades as $grade) {
                // Find next grade
                $nextGrade = Grade::where('id', '>', $grade->id)->orderBy('id')->first();

                if (!$nextGrade) {
                    // This is the highest grade - students should graduate
                    $students = Student::active()
                        ->currentYear()
                        ->where('grade_id', $grade->id)
                        ->get();

                    foreach ($students as $student) {
                        $this->graduateStudent($student, $notes, $processedBy);
                        $results['graduated']++;
                    }

                    $results['details'][] = [
                        'grade' => $grade->name,
                        'action' => 'graduated',
                        'count' => $students->count(),
                    ];
                } else {
                    // Promote to next grade
                    $progressions = $this->promoteGrade($grade->id, $nextGrade->id, $notes, $processedBy);
                    $results['success'] += $progressions->count();

                    $results['details'][] = [
                        'from_grade' => $grade->name,
                        'to_grade' => $nextGrade->name,
                        'count' => $progressions->count(),
                    ];
                }
            }
        });

        return $results;
    }

    /**
     * Graduate a student
     */
    public function graduateStudent(Student $student, ?string $notes = null, ?int $processedBy = null): void
    {
        $student->update([
            'status' => 'graduated',
            'graduation_date' => now(),
        ]);

        // Optionally create a progression record for graduation
        StudentProgression::create([
            'student_id' => $student->id,
            'from_grade_id' => $student->grade_id,
            'to_grade_id' => $student->grade_id, // Same grade (graduated)
            'academic_year' => SystemSetting::currentAcademicYear(),
            'progression_type' => 'promotion',
            'notes' => $notes ?? 'Student graduated',
            'processed_by' => $processedBy ?? auth()->id(),
            'processed_at' => now(),
        ]);
    }

    /**
     * Check if a student should be graduated
     */
    protected function shouldGraduateStudent(Student $student): bool
    {
        if (!SystemSetting::isAutoGraduateEnabled()) {
            return false;
        }

        return $student->isAtMaxGrade();
    }

    /**
     * Start a new academic year
     */
    public function startNewAcademicYear(string $newYear, bool $promoteStudents = true): array
    {
        $results = ['old_year' => SystemSetting::currentAcademicYear(), 'new_year' => $newYear];

        DB::transaction(function () use ($newYear, $promoteStudents, &$results) {
            // Promote all students if requested
            if ($promoteStudents) {
                $promotionResults = $this->promoteAllStudents("Academic year transition to {$newYear}");
                $results['promotion'] = $promotionResults;
            }

            // Update all active students to new academic year
            Student::active()->update(['academic_year' => $newYear]);

            // Update system setting
            SystemSetting::set('current_academic_year', $newYear);
        });

        return $results;
    }

    /**
     * Repeat a student in the same grade
     */
    public function repeatStudent(Student $student, ?string $notes = null, ?int $processedBy = null): StudentProgression
    {
        return StudentProgression::create([
            'student_id' => $student->id,
            'from_grade_id' => $student->grade_id,
            'to_grade_id' => $student->grade_id,
            'academic_year' => SystemSetting::currentAcademicYear(),
            'progression_type' => 'repetition',
            'notes' => $notes,
            'processed_by' => $processedBy ?? auth()->id(),
            'processed_at' => now(),
        ]);
    }

    /**
     * Get promotion statistics
     */
    public function getPromotionStats(string $academicYear): array
    {
        return [
            'total_promotions' => StudentProgression::forAcademicYear($academicYear)
                ->where('progression_type', 'promotion')
                ->count(),
            'total_repetitions' => StudentProgression::forAcademicYear($academicYear)
                ->where('progression_type', 'repetition')
                ->count(),
            'total_graduated' => Student::graduated()
                ->whereYear('graduation_date', $academicYear)
                ->count(),
            'by_grade' => StudentProgression::forAcademicYear($academicYear)
                ->with(['fromGrade', 'toGrade'])
                ->get()
                ->groupBy('from_grade_id')
                ->map(function ($progressions) {
                    return [
                        'grade' => $progressions->first()->fromGrade->name ?? 'Unknown',
                        'count' => $progressions->count(),
                    ];
                }),
        ];
    }
}
