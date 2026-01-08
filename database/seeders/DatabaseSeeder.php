<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        /*User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);*/

        User::query()->create([
            'name'=>'James Monachi',
            'password'=>bcrypt('5xM0I73Em5gN'),
            'email'=>'jamesmonachi@gmail.com'
            ]);
//        Grade::query()->truncatdre();
        $this->call([
//            GradeSeeder::class,
//            GuardianSeeder::class,
//            StudentSeeder::class,
//            FeeSeeder::class,
//            FeePaymentSeeder::class,
        ]);
    }
}
