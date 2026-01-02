<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProgression extends Model
{
    protected $fillable = [
        'student_id',
        'from_grade_id',
        'to_grade_id',
        'academic_year',
        'progression_type',
        'notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    /**
     * Get the student that owns the progression
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the grade the student progressed from
     */
    public function fromGrade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'from_grade_id');
    }

    /**
     * Get the grade the student progressed to
     */
    public function toGrade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'to_grade_id');
    }

    /**
     * Get the user who processed this progression
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope to get promotions only
     */
    public function scopePromotions($query)
    {
        return $query->where('progression_type', 'promotion');
    }

    /**
     * Scope to get repetitions only
     */
    public function scopeRepetitions($query)
    {
        return $query->where('progression_type', 'repetition');
    }

    /**
     * Scope to filter by academic year
     */
    public function scopeForAcademicYear($query, string $year)
    {
        return $query->where('academic_year', $year);
    }
}
