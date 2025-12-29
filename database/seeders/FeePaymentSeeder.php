<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeePaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Get all fees for Term 1 2025
        $term1Fees = DB::table('fees')->where('term', 'Term 1 2025')->get();

        $payments = [];

        // Student 1 (Grade 3) - Full payment
        $fee = $term1Fees->where('grade_id', 3)->first();
        $payments[] = [
            'student_id' => 1,
            'fee_id' => $fee->id,
            'amount_paid' => $fee->amount,
            'payment_date' => '2025-01-15',
            'status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Student 2 (Grade 1) - Partial payment
        $fee = $term1Fees->where('grade_id', 1)->first();
        $payments[] = [
            'student_id' => 2,
            'fee_id' => $fee->id,
            'amount_paid' => 10000,
            'payment_date' => '2025-01-20',
            'status' => 'partial',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Student 3 (Grade 5) - Full payment
        $fee = $term1Fees->where('grade_id', 5)->first();
        $payments[] = [
            'student_id' => 3,
            'fee_id' => $fee->id,
            'amount_paid' => $fee->amount,
            'payment_date' => '2025-01-10',
            'status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Student 4 (Grade 4) - Partial payment
        $fee = $term1Fees->where('grade_id', 4)->first();
        $payments[] = [
            'student_id' => 4,
            'fee_id' => $fee->id,
            'amount_paid' => 12000,
            'payment_date' => '2025-01-25',
            'status' => 'partial',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Student 5 (Grade 7) - Full payment
        $fee = $term1Fees->where('grade_id', 7)->first();
        $payments[] = [
            'student_id' => 5,
            'fee_id' => $fee->id,
            'amount_paid' => $fee->amount,
            'payment_date' => '2025-01-12',
            'status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Student 6 (Grade 2) - Pending (no payment)
        $fee = $term1Fees->where('grade_id', 2)->first();
        $payments[] = [
            'student_id' => 6,
            'fee_id' => $fee->id,
            'amount_paid' => 0,
            'payment_date' => null,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Student 7 (Grade 6) - Partial payment
        $fee = $term1Fees->where('grade_id', 6)->first();
        $payments[] = [
            'student_id' => 7,
            'fee_id' => $fee->id,
            'amount_paid' => 15000,
            'payment_date' => '2025-02-01',
            'status' => 'partial',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Student 8 (Grade 8) - Full payment
        $fee = $term1Fees->where('grade_id', 8)->first();
        $payments[] = [
            'student_id' => 8,
            'fee_id' => $fee->id,
            'amount_paid' => $fee->amount,
            'payment_date' => '2025-01-18',
            'status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('fee_payments')->insert($payments);
    }
}
