<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeCredit extends Model
{
    protected $fillable = [
        'student_id',
        'from_payment_id',
        'applied_to_fee_id',
        'amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromPayment(): BelongsTo
    {
        return $this->belongsTo(FeePayment::class, 'from_payment_id');
    }

    public function appliedToFee(): BelongsTo
    {
        return $this->belongsTo(Fee::class, 'applied_to_fee_id');
    }
}
