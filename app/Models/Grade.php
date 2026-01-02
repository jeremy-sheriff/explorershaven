<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model
{
    protected $fillable = ['name'];

    /**
     * Get all students in this grade
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get all fees for this grade
     */
    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }
}
