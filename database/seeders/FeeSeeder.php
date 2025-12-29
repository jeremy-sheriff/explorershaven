<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeeSeeder extends Seeder
{
    public function run(): void
    {
        $fees = [
            // Term 1 2025 - All grades
            ['grade_id' => 1, 'amount' => 15000, 'term' => 'Term 1 2025', 'due_date' => '2025-02-28', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 2, 'amount' => 16000, 'term' => 'Term 1 2025', 'due_date' => '2025-02-28', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 3, 'amount' => 17000, 'term' => 'Term 1 2025', 'due_date' => '2025-02-28', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 4, 'amount' => 18000, 'term' => 'Term 1 2025', 'due_date' => '2025-02-28', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 5, 'amount' => 19000, 'term' => 'Term 1 2025', 'due_date' => '2025-02-28', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 6, 'amount' => 20000, 'term' => 'Term 1 2025', 'due_date' => '2025-02-28', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 7, 'amount' => 22000, 'term' => 'Term 1 2025', 'due_date' => '2025-02-28', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 8, 'amount' => 25000, 'term' => 'Term 1 2025', 'due_date' => '2025-02-28', 'created_at' => now(), 'updated_at' => now()],

            // Term 2 2025 - All grades
            ['grade_id' => 1, 'amount' => 15000, 'term' => 'Term 2 2025', 'due_date' => '2025-05-31', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 2, 'amount' => 16000, 'term' => 'Term 2 2025', 'due_date' => '2025-05-31', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 3, 'amount' => 17000, 'term' => 'Term 2 2025', 'due_date' => '2025-05-31', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 4, 'amount' => 18000, 'term' => 'Term 2 2025', 'due_date' => '2025-05-31', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 5, 'amount' => 19000, 'term' => 'Term 2 2025', 'due_date' => '2025-05-31', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 6, 'amount' => 20000, 'term' => 'Term 2 2025', 'due_date' => '2025-05-31', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 7, 'amount' => 22000, 'term' => 'Term 2 2025', 'due_date' => '2025-05-31', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 8, 'amount' => 25000, 'term' => 'Term 2 2025', 'due_date' => '2025-05-31', 'created_at' => now(), 'updated_at' => now()],

            // Term 3 2025 - All grades
            ['grade_id' => 1, 'amount' => 15000, 'term' => 'Term 3 2025', 'due_date' => '2025-09-30', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 2, 'amount' => 16000, 'term' => 'Term 3 2025', 'due_date' => '2025-09-30', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 3, 'amount' => 17000, 'term' => 'Term 3 2025', 'due_date' => '2025-09-30', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 4, 'amount' => 18000, 'term' => 'Term 3 2025', 'due_date' => '2025-09-30', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 5, 'amount' => 19000, 'term' => 'Term 3 2025', 'due_date' => '2025-09-30', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 6, 'amount' => 20000, 'term' => 'Term 3 2025', 'due_date' => '2025-09-30', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 7, 'amount' => 22000, 'term' => 'Term 3 2025', 'due_date' => '2025-09-30', 'created_at' => now(), 'updated_at' => now()],
            ['grade_id' => 8, 'amount' => 25000, 'term' => 'Term 3 2025', 'due_date' => '2025-09-30', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('fees')->insert($fees);
    }
}
