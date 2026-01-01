<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['adm_no', 'first_name', 'middle_name', 'last_name', 'guardian_id', 'grade_id'];

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function feePayments()
    {
        return $this->hasMany(FeePayment::class);
    }

    public function feeCredits()
    {
        return $this->hasMany(FeeCredit::class);
    }
}

