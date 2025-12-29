<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            ['name' => 'Grade 1', 'level' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grade 2', 'level' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grade 3', 'level' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grade 4', 'level' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grade 5', 'level' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grade 6', 'level' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grade 7', 'level' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grade 8', 'level' => 8, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('grades')->insert($grades);
    }
}
