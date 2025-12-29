<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GuardianSeeder extends Seeder
{
    public function run(): void
    {
        $guardians = [
            [
                'first_name' => 'John',
                'middle_name' => 'Kamau',
                'last_name' => 'Mwangi',
                'phone_number' => '0712345678',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Mary',
                'middle_name' => 'Wanjiru',
                'last_name' => 'Njeri',
                'phone_number' => '0723456789',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Peter',
                'middle_name' => 'Otieno',
                'last_name' => 'Odhiambo',
                'phone_number' => '0734567890',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Grace',
                'middle_name' => 'Akinyi',
                'last_name' => 'Adhiambo',
                'phone_number' => '0745678901',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'David',
                'middle_name' => 'Kipchoge',
                'last_name' => 'Kiptoo',
                'phone_number' => '0756789012',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('guardians')->insert($guardians);
    }
}
