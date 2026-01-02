<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_progressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_grade_id')->nullable()->constrained('grades')->nullOnDelete();
            $table->foreignId('to_grade_id')->constrained('grades')->cascadeOnDelete();
            $table->string('academic_year');
            $table->enum('progression_type', ['promotion', 'repetition', 'demotion'])->default('promotion');
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at');
            $table->timestamps();

            // Index for faster queries
            $table->index(['student_id', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_progressions');
    }
};
