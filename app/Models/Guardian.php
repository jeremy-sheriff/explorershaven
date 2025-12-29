<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guardian extends Model
{
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'phone_number',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
