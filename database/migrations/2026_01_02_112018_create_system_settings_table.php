<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('system_settings')->insert([
            [
                'key' => 'current_academic_year',
                'value' => '2025',
                'type' => 'string',
                'description' => 'Current academic year',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_grade_level',
                'value' => '12',
                'type' => 'integer',
                'description' => 'Maximum grade level in the school (students at this level will graduate)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'auto_graduate_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Automatically graduate students at max grade level',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
