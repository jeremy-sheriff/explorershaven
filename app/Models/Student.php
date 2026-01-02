<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'adm_no',
        'first_name',
        'middle_name',
        'last_name',
        'guardian_id',
        'grade_id',
        'academic_year',
        'status',
        'enrollment_date',
        'graduation_date',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'graduation_date' => 'date',
    ];

    /**
     * Get the guardian of the student
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    /**
     * Get the grade that the student belongs to
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Get all fee payments for the student
     */
    public function feePayments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    /**
     * Get all fee credits for the student
     */
    public function feeCredits(): HasMany
    {
        return $this->hasMany(FeeCredit::class);
    }

    /**
     * Get all progressions for the student
     */
    public function progressions(): HasMany
    {
        return $this->hasMany(StudentProgression::class);
    }

    /**
     * Scope to get only active students
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get students in a specific academic year
     */
    public function scopeInAcademicYear($query, string $year)
    {
        return $query->where('academic_year', $year);
    }

    /**
     * Scope to get students in the current academic year
     */
    public function scopeCurrentYear($query)
    {
        $currentYear = SystemSetting::currentAcademicYear();
        return $query->where('academic_year', $currentYear);
    }

    /**
     * Scope to get graduated students
     */
    public function scopeGraduated($query)
    {
        return $query->where('status', 'graduated');
    }

    /**
     * Scope to get students by grade
     */
    public function scopeInGrade($query, int $gradeId)
    {
        return $query->where('grade_id', $gradeId);
    }

    /**
     * Get the student's full name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    /**
     * Check if student can be promoted
     */
    public function canBePromoted(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if student is at maximum grade level
     */
    public function isAtMaxGrade(): bool
    {
        $maxGradeLevel = SystemSetting::maxGradeLevel();
        return $this->grade && $this->grade->id >= $maxGradeLevel;
    }
}
