<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            ['name' => 'PP1', 'level' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PP2', 'level' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grade 1', 'level' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Grade 2', 'level' => 4, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('grades')->insert($grades);
    }
}
