<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            // Guardian 1's children
            [
                'adm_no' => 'STU001',
                'first_name' => 'James',
                'middle_name' => 'Kariuki',
                'last_name' => 'Mwangi',
                'guardian_id' => 1,
                'grade_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'adm_no' => 'STU002',
                'first_name' => 'Faith',
                'middle_name' => 'Wanjiku',
                'last_name' => 'Mwangi',
                'guardian_id' => 1,
                'grade_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Guardian 2's children
            [
                'adm_no' => 'STU003',
                'first_name' => 'Daniel',
                'middle_name' => 'Kimani',
                'last_name' => 'Njeri',
                'guardian_id' => 2,
                'grade_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Guardian 3's children
            [
                'adm_no' => 'STU004',
                'first_name' => 'Sharon',
                'middle_name' => 'Auma',
                'last_name' => 'Odhiambo',
                'guardian_id' => 3,
                'grade_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'adm_no' => 'STU005',
                'first_name' => 'Brian',
                'middle_name' => 'Owino',
                'last_name' => 'Odhiambo',
                'guardian_id' => 3,
                'grade_id' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Guardian 4's children
            [
                'adm_no' => 'STU006',
                'first_name' => 'Nancy',
                'middle_name' => 'Atieno',
                'last_name' => 'Adhiambo',
                'guardian_id' => 4,
                'grade_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Guardian 5's children
            [
                'adm_no' => 'STU007',
                'first_name' => 'Victor',
                'middle_name' => 'Kiprono',
                'last_name' => 'Kiptoo',
                'guardian_id' => 5,
                'grade_id' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'adm_no' => 'STU008',
                'first_name' => 'Ruth',
                'middle_name' => 'Jepkoech',
                'last_name' => 'Kiptoo',
                'guardian_id' => 5,
                'grade_id' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('students')->insert($students);
    }
}
